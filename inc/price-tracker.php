<?php
/**
 * 相場トラッカー
 * - Googleスプレッドシート（CSV公開）から価格データを取り込み
 * - WP独自テーブルへ日々スナップショットを蓄積（履歴）
 * - 移動平均・傾向・線形回帰から「予想相場」を算出
 * - 管理画面の設定・手動取込ボタン、WP-Cronによる自動取込
 *
 * @package Torekamafia_Yamaguchi
 */

if (!defined('ABSPATH')) { exit; }

define('TMF_DB_VERSION', '1.0.1');

/* ============================================================
 * テーブル作成
 * ========================================================== */
function tmf_db_tables() {
    global $wpdb;
    return array(
        'cards'   => $wpdb->prefix . 'tmf_cards',
        'history' => $wpdb->prefix . 'tmf_history',
    );
}

function tmf_create_tables() {
    global $wpdb;
    $t = tmf_db_tables();
    $charset = $wpdb->get_charset_collate();
    require_once ABSPATH . 'wp-admin/includes/upgrade.php';

    $sql1 = "CREATE TABLE {$t['cards']} (
        id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
        code VARCHAR(48) NOT NULL DEFAULT '',
        name VARCHAR(191) NOT NULL DEFAULT '',
        grade VARCHAR(48) NOT NULL DEFAULT '',
        trend VARCHAR(24) NOT NULL DEFAULT '',
        price BIGINT NOT NULL DEFAULT 0,
        price_date DATE NULL,
        d1 FLOAT NOT NULL DEFAULT 0,
        d3 FLOAT NOT NULL DEFAULT 0,
        d7 FLOAT NOT NULL DEFAULT 0,
        ma5 FLOAT NOT NULL DEFAULT 0,
        ma20 FLOAT NOT NULL DEFAULT 0,
        vol FLOAT NOT NULL DEFAULT 0,
        image TEXT NULL,
        series TEXT NULL,
        forecast BIGINT NOT NULL DEFAULT 0,
        forecast_dir VARCHAR(8) NOT NULL DEFAULT '',
        forecast_pct FLOAT NOT NULL DEFAULT 0,
        updated DATETIME NULL,
        PRIMARY KEY  (id),
        UNIQUE KEY code (code),
        KEY price (price),
        KEY trend (trend)
    ) $charset;";

    $sql2 = "CREATE TABLE {$t['history']} (
        id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
        code VARCHAR(48) NOT NULL DEFAULT '',
        price BIGINT NOT NULL DEFAULT 0,
        captured_on DATE NOT NULL,
        PRIMARY KEY  (id),
        UNIQUE KEY code_day (code, captured_on),
        KEY code (code)
    ) $charset;";

    dbDelta($sql1);
    dbDelta($sql2);
    update_option('tmf_db_version', TMF_DB_VERSION);
}

// バージョン差異があれば作成/更新
function tmf_maybe_upgrade_db() {
    if (get_option('tmf_db_version') !== TMF_DB_VERSION) {
        tmf_create_tables();
    }
}
add_action('admin_init', 'tmf_maybe_upgrade_db');
add_action('after_switch_theme', 'tmf_create_tables');

/* ============================================================
 * ユーティリティ
 * ========================================================== */

/** 文字列から数値のみを抽出して整数化 */
function tmf_to_int($v) {
    $v = preg_replace('/[^0-9\-]/', '', (string)$v);
    return $v === '' || $v === '-' ? 0 : (int)$v;
}
function tmf_to_float($v) {
    $v = preg_replace('/[^0-9\.\-]/', '', (string)$v);
    return $v === '' || $v === '-' || $v === '.' ? 0.0 : (float)$v;
}

/** "ピカチュウ [323/S-P]" → code=323/S-P, name=ピカチュウ */
function tmf_extract_code($name, $fallback = '') {
    $code = '';
    if (preg_match('/[\[［]\s*([^\]］]+?)\s*[\]］]/u', $name, $m)) {
        $code = trim($m[1]);
    }
    if ($code === '' && $fallback) {
        // card_id "323-s-p" → "323/S-P" 風に整形（表示用フォールバック）
        $code = strtoupper(str_replace('-', '/', $fallback));
    }
    return $code;
}
function tmf_strip_code_from_name($name) {
    return trim(preg_replace('/[\[［]\s*[^\]］]+?\s*[\]］]/u', '', $name));
}

/** "109375→99450→..." を数値配列に */
function tmf_parse_series($s) {
    $s = (string)$s;
    if ($s === '') return array();
    $parts = preg_split('/[→>\x{2192}]+/u', $s);
    $out = array();
    foreach ($parts as $p) {
        $n = tmf_to_int($p);
        if ($n > 0) $out[] = $n;
    }
    return $out;
}

/* ============================================================
 * CSV 取り込み
 * ========================================================== */

/** CSVテキストを連想配列（ヘッダーキー）に変換 */
function tmf_parse_csv($text) {
    $text = str_replace(array("\r\n", "\r"), "\n", $text);
    $lines = explode("\n", $text);
    $rows = array();
    $buffer = '';
    // 単純パーサ：ダブルクオート内改行に対応
    $joined = array();
    foreach ($lines as $line) {
        $buffer .= ($buffer === '' ? '' : "\n") . $line;
        if (substr_count($buffer, '"') % 2 === 0) {
            $joined[] = $buffer;
            $buffer = '';
        }
    }
    if ($buffer !== '') $joined[] = $buffer;

    $header = null;
    foreach ($joined as $line) {
        if (trim($line) === '') continue;
        $cols = str_getcsv($line);
        if ($header === null) {
            $header = array_map(function ($h) { return trim($h); }, $cols);
            continue;
        }
        $row = array();
        foreach ($header as $i => $key) {
            if ($key === '') continue;
            $row[$key] = isset($cols[$i]) ? $cols[$i] : '';
        }
        $rows[] = $row;
    }
    return $rows;
}

/** ヘッダー名を柔軟に取得 */
function tmf_col($row, $candidates, $default = '') {
    foreach ((array)$candidates as $c) {
        foreach ($row as $k => $v) {
            if ($k === $c) return $v;
        }
    }
    // 部分一致
    foreach ((array)$candidates as $c) {
        foreach ($row as $k => $v) {
            if (mb_strpos($k, $c) !== false) return $v;
        }
    }
    return $default;
}

/**
 * メイン取込
 * @return array 結果サマリ
 */
function tmf_import_prices($manual = false) {
    global $wpdb;
    $t = tmf_db_tables();

    $url = trim((string)get_option('tmf_csv_url'));
    if ($url === '') {
        return array('ok' => false, 'msg' => 'CSV URLが未設定です。');
    }

    $res = wp_remote_get($url, array('timeout' => 25, 'redirection' => 5));
    if (is_wp_error($res)) {
        return array('ok' => false, 'msg' => '取得失敗: ' . $res->get_error_message());
    }
    $code_http = wp_remote_retrieve_response_code($res);
    if ($code_http !== 200) {
        return array('ok' => false, 'msg' => 'HTTP ' . $code_http . '（URL/公開設定を確認してください）');
    }
    $body = wp_remote_retrieve_body($res);
    if (stripos($body, '<html') !== false) {
        return array('ok' => false, 'msg' => 'HTMLが返されました。CSV公開URL（output=csv）か、リンク共有設定をご確認ください。');
    }

    $rows = tmf_parse_csv($body);
    if (empty($rows)) {
        return array('ok' => false, 'msg' => '行を取得できませんでした。');
    }

    // 画像CSV（任意）
    $images = tmf_fetch_image_map();

    $today = current_time('Y-m-d');
    $count = 0; $hist = 0;

    foreach ($rows as $row) {
        $name_raw = tmf_col($row, array('名前', '商品名', 'カード名', 'name'));
        $card_id  = tmf_col($row, array('card_id', 'カードID'));
        if ($name_raw === '' && $card_id === '') continue;

        $code = tmf_extract_code($name_raw, $card_id);
        if ($code === '') continue;
        $name = tmf_strip_code_from_name($name_raw);
        if ($name === '') $name = $name_raw;

        $grade = tmf_col($row, array('状態', 'グレード', 'grade'));
        $trend = tmf_col($row, array('傾向', 'trend'));
        $price = tmf_to_int(tmf_col($row, array('最新価格', '買取価格', '価格', 'price')));
        $pdate = tmf_col($row, array('最新日', '取得日時', 'price_date'));
        $pdate = tmf_normalize_date($pdate, $today);

        $series = tmf_parse_series(tmf_col($row, array('直近1週間', '直近', 'series')));
        // 最新価格が空なら系列の末尾で補完
        if ($price <= 0 && !empty($series)) $price = end($series);
        if ($price <= 0) continue;

        $image = '';
        $ck = tmf_norm_code($code);
        if (isset($images[$ck])) $image = $images[$ck];

        $data = array(
            'code'  => $code,
            'name'  => mb_substr($name, 0, 180),
            'grade' => $grade,
            'trend' => $trend,
            'price' => $price,
            'price_date' => $pdate,
            'd1'    => tmf_to_float(tmf_col($row, array('d1%', 'd1'))),
            'd3'    => tmf_to_float(tmf_col($row, array('d3%', 'd3'))),
            'd7'    => tmf_to_float(tmf_col($row, array('d7%', 'd7'))),
            'ma5'   => tmf_to_float(tmf_col($row, array('ma5'))),
            'ma20'  => tmf_to_float(tmf_col($row, array('ma20'))),
            'vol'   => tmf_to_float(tmf_col($row, array('変動%', 'vol'))),
            'image' => $image,
            'series'=> implode(',', $series),
            'updated' => current_time('mysql'),
        );

        // upsert
        $exists = $wpdb->get_var($wpdb->prepare("SELECT id FROM {$t['cards']} WHERE code=%s", $code));
        if ($exists) {
            $wpdb->update($t['cards'], $data, array('id' => $exists));
        } else {
            $wpdb->insert($t['cards'], $data);
        }
        $count++;

        // --- 履歴の蓄積 ---
        // 初回：直近1週間シリーズを日付付きでバックフィル
        $have = (int)$wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM {$t['history']} WHERE code=%s", $code));
        if ($have === 0 && !empty($series)) {
            $anchor = strtotime($pdate);
            $n = count($series);
            foreach ($series as $i => $val) {
                $day = date('Y-m-d', $anchor - (($n - 1 - $i) * DAY_IN_SECONDS));
                $wpdb->query($wpdb->prepare(
                    "INSERT IGNORE INTO {$t['history']} (code, price, captured_on) VALUES (%s, %d, %s)",
                    $code, $val, $day
                ));
                $hist++;
            }
        }
        // 当日スナップショット
        $ins = $wpdb->query($wpdb->prepare(
            "INSERT IGNORE INTO {$t['history']} (code, price, captured_on) VALUES (%s, %d, %s)",
            $code, $price, $pdate
        ));
        if ($ins) $hist++;

        // 予想を更新
        tmf_update_forecast($code);
    }

    update_option('tmf_last_import', current_time('mysql'));
    update_option('tmf_last_import_result', array('ok' => true, 'cards' => $count, 'history' => $hist));

    return array('ok' => true, 'msg' => "取込成功：{$count}件のカード / {$hist}件の履歴を更新", 'cards' => $count, 'history' => $hist);
}

/** 画像CSV（任意）→ code(正規化) => URL */
function tmf_fetch_image_map() {
    $url = trim((string)get_option('tmf_csv_image_url'));
    if ($url === '') return array();
    $res = wp_remote_get($url, array('timeout' => 20));
    if (is_wp_error($res) || wp_remote_retrieve_response_code($res) !== 200) return array();
    $rows = tmf_parse_csv(wp_remote_retrieve_body($res));
    $map = array();
    foreach ($rows as $row) {
        $nm  = tmf_col($row, array('商品名', '名前', 'name'));
        $img = tmf_col($row, array('画像URL', '画像', 'image', 'image_url'));
        if ($nm === '' || $img === '') continue;
        // 商品名内の "294/XY-P" 等を抽出
        if (preg_match('/(\d{1,3}\/[0-9A-Za-z\-]+)/u', $nm, $m)) {
            $map[tmf_norm_code($m[1])] = esc_url_raw($img);
        }
    }
    return $map;
}

/** コード正規化（比較用）："294/XY-P" → "294/xy-p" 全角ハイフン統一 */
function tmf_norm_code($c) {
    $c = str_replace(array('ｰ', '−', '―', '‐'), '-', (string)$c);
    return strtolower(trim($c));
}

/** 日付を Y-m-d に正規化 */
function tmf_normalize_date($v, $default) {
    $v = trim((string)$v);
    if ($v === '') return $default;
    $ts = strtotime($v);
    return $ts ? date('Y-m-d', $ts) : $default;
}

/* ============================================================
 * 予想相場（線形回帰 + 傾向 + 移動平均）
 * ========================================================== */
function tmf_update_forecast($code) {
    global $wpdb;
    $t = tmf_db_tables();

    $card = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$t['cards']} WHERE code=%s", $code));
    if (!$card) return;

    // 履歴（古い順）
    $hist = $wpdb->get_results($wpdb->prepare(
        "SELECT price, captured_on FROM {$t['history']} WHERE code=%s ORDER BY captured_on ASC", $code
    ));
    $points = array();
    foreach ($hist as $h) { $points[] = (float)$h->price; }
    if (count($points) < 3 && $card->series) {
        $points = array_map('floatval', array_filter(explode(',', $card->series)));
    }

    $horizon = (int)get_option('tmf_forecast_days', 30);
    $last = (float)$card->price;
    $forecast = $last;

    $n = count($points);
    if ($n >= 3) {
        // 線形回帰 y = a + b x
        $sx = $sy = $sxx = $sxy = 0;
        foreach ($points as $i => $y) {
            $x = $i;
            $sx += $x; $sy += $y; $sxx += $x * $x; $sxy += $x * $y;
        }
        $denom = ($n * $sxx - $sx * $sx);
        $b = $denom != 0 ? ($n * $sxy - $sx * $sy) / $denom : 0;
        // 直近価格からhorizon日先を外挿（1点=1日相当）
        $forecast = $last + $b * $horizon;
    } else {
        // データ不足：傾向で微調整
        $bias = array('上昇' => 0.05, '下落' => -0.05);
        $forecast = $last * (1 + (isset($bias[$card->trend]) ? $bias[$card->trend] : 0));
    }

    // 移動平均クロスの補正（ma5>ma20で上向き、逆で下向き）
    if ($card->ma5 > 0 && $card->ma20 > 0) {
        $cross = ($card->ma5 - $card->ma20) / max($card->ma20, 1);
        $forecast *= (1 + max(-0.15, min(0.15, $cross * 0.5)));
    }

    // 現実的な範囲にクランプ（±60%）
    $forecast = max($last * 0.4, min($last * 1.6, $forecast));
    $forecast = (int)round($forecast);

    $pct = $last > 0 ? round((($forecast - $last) / $last) * 100, 1) : 0;
    $dir = $pct > 1.5 ? 'up' : ($pct < -1.5 ? 'down' : 'flat');

    $wpdb->update($t['cards'], array(
        'forecast' => $forecast,
        'forecast_dir' => $dir,
        'forecast_pct' => $pct,
    ), array('id' => $card->id));
}

/* ============================================================
 * WP-Cron（毎日自動取込）
 * ========================================================== */
function tmf_schedule_cron() {
    if (!wp_next_scheduled('tmf_daily_import')) {
        wp_schedule_event(time() + 300, 'daily', 'tmf_daily_import');
    }
}
add_action('after_switch_theme', 'tmf_schedule_cron');
add_action('init', 'tmf_schedule_cron');
add_action('tmf_daily_import', function () { tmf_import_prices(false); });

function tmf_clear_cron() { wp_clear_scheduled_hook('tmf_daily_import'); }
add_action('switch_theme', 'tmf_clear_cron');

/* ============================================================
 * データ取得（テンプレート用）
 * ========================================================== */
function tmf_get_cards($args = array()) {
    global $wpdb;
    $t = tmf_db_tables();
    $limit = isset($args['limit']) ? (int)$args['limit'] : 2000;
    $sql = "SELECT code,name,grade,trend,price,price_date,d7,ma5,ma20,vol,image,series,forecast,forecast_dir,forecast_pct
            FROM {$t['cards']} ORDER BY price DESC LIMIT %d";
    return $wpdb->get_results($wpdb->prepare($sql, $limit));
}

/** 相場検索ページ（テンプレート使用）のURLを取得。無ければ空文字 */
function tmf_souba_url() {
    $cached = wp_cache_get('tmf_souba_page');
    if ($cached === false) {
        $pages = get_posts(array(
            'post_type'   => 'page',
            'numberposts' => 1,
            'meta_key'    => '_wp_page_template',
            'meta_value'  => 'page-souba.php',
            'fields'      => 'ids',
        ));
        $cached = !empty($pages) ? (int)$pages[0] : 0;
        wp_cache_set('tmf_souba_page', $cached);
    }
    return $cached ? get_permalink($cached) : '';
}

function tmf_get_history($code, $limit = 120) {
    global $wpdb;
    $t = tmf_db_tables();
    return $wpdb->get_results($wpdb->prepare(
        "SELECT price, captured_on FROM {$t['history']} WHERE code=%s ORDER BY captured_on ASC LIMIT %d",
        $code, $limit
    ));
}

/* ============================================================
 * AJAX：カード履歴（グラフ用）
 * ========================================================== */
function tmf_ajax_history() {
    $code = isset($_GET['code']) ? sanitize_text_field(wp_unslash($_GET['code'])) : '';
    if ($code === '') wp_send_json_error();
    $rows = tmf_get_history($code);
    $out = array();
    foreach ($rows as $r) { $out[] = array('d' => $r->captured_on, 'p' => (int)$r->price); }
    wp_send_json_success($out);
}
add_action('wp_ajax_tmf_history', 'tmf_ajax_history');
add_action('wp_ajax_nopriv_tmf_history', 'tmf_ajax_history');

/* ============================================================
 * 管理画面：設定 & 手動取込
 * ========================================================== */
function tmf_admin_menu() {
    add_menu_page('相場データ', '相場データ', 'manage_options', 'tmf-souba', 'tmf_admin_page', 'dashicons-chart-line', 7);
}
add_action('admin_menu', 'tmf_admin_menu');

function tmf_admin_register() {
    register_setting('tmf_souba', 'tmf_csv_url', array('sanitize_callback' => 'esc_url_raw'));
    register_setting('tmf_souba', 'tmf_csv_image_url', array('sanitize_callback' => 'esc_url_raw'));
    register_setting('tmf_souba', 'tmf_forecast_days', array('sanitize_callback' => 'absint'));
}
add_action('admin_init', 'tmf_admin_register');

function tmf_admin_page() {
    if (!current_user_can('manage_options')) return;

    // 手動取込
    $notice = '';
    if (isset($_POST['tmf_import']) && check_admin_referer('tmf_import_now')) {
        $r = tmf_import_prices(true);
        $notice = '<div class="notice ' . ($r['ok'] ? 'notice-success' : 'notice-error') . '"><p>' . esc_html($r['msg']) . '</p></div>';
    }

    global $wpdb;
    $t = tmf_db_tables();
    $card_count = (int)$wpdb->get_var("SELECT COUNT(*) FROM {$t['cards']}");
    $hist_count = (int)$wpdb->get_var("SELECT COUNT(*) FROM {$t['history']}");
    $last = get_option('tmf_last_import', '未取込');
    $days = (int)get_option('tmf_forecast_days', 30);
    ?>
    <div class="wrap">
        <h1>📈 相場データ設定</h1>
        <?php echo $notice; ?>

        <div style="display:flex;gap:20px;flex-wrap:wrap;margin:20px 0">
            <div style="background:#fff;border:1px solid #ccd0d4;border-radius:8px;padding:16px 24px">
                <strong style="font-size:26px"><?php echo number_format($card_count); ?></strong><br>登録カード数
            </div>
            <div style="background:#fff;border:1px solid #ccd0d4;border-radius:8px;padding:16px 24px">
                <strong style="font-size:26px"><?php echo number_format($hist_count); ?></strong><br>蓄積履歴レコード
            </div>
            <div style="background:#fff;border:1px solid #ccd0d4;border-radius:8px;padding:16px 24px">
                <strong style="font-size:16px"><?php echo esc_html($last); ?></strong><br>最終取込日時
            </div>
        </div>

        <form method="post" action="options.php">
            <?php settings_fields('tmf_souba'); ?>
            <table class="form-table" role="presentation">
                <tr>
                    <th scope="row"><label for="tmf_csv_url">価格CSV URL（必須）</label></th>
                    <td>
                        <input type="url" id="tmf_csv_url" name="tmf_csv_url" value="<?php echo esc_attr(get_option('tmf_csv_url')); ?>" class="regular-text" style="width:100%;max-width:680px" placeholder="https://docs.google.com/spreadsheets/d/xxx/gviz/tq?tqx=out:csv&gid=1234">
                        <p class="description">
                            Googleスプレッドシートの「分析タブ」をCSVで取得できるURL。<br>
                            <b>方法A（推奨）</b>：スプレッドシートを「リンクを知る全員が閲覧可」に設定 →
                            <code>https://docs.google.com/spreadsheets/d/<b>スプレッドシートID</b>/gviz/tq?tqx=out:csv&amp;gid=<b>タブのgid</b></code><br>
                            <b>方法B</b>：ファイル→共有→ウェブに公開→対象タブをCSV形式で公開し、そのURLを貼付。<br>
                            ※ 列名（品番/名前/傾向/最新価格/ma5/ma20/直近1週間 等）で自動判別します。
                        </p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="tmf_csv_image_url">画像CSV URL（任意）</label></th>
                    <td>
                        <input type="url" id="tmf_csv_image_url" name="tmf_csv_image_url" value="<?php echo esc_attr(get_option('tmf_csv_image_url')); ?>" class="regular-text" style="width:100%;max-width:680px" placeholder="（買取リストタブなど 商品名＋画像URL を含むCSV）">
                        <p class="description">「商品名」と「画像URL」列を含むタブのCSV。品番で自動マッチしてカード画像を表示します。</p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="tmf_forecast_days">予想の対象日数</label></th>
                    <td>
                        <input type="number" id="tmf_forecast_days" name="tmf_forecast_days" value="<?php echo esc_attr($days); ?>" min="7" max="180" style="width:90px"> 日先を予想
                    </td>
                </tr>
            </table>
            <?php submit_button('設定を保存'); ?>
        </form>

        <hr>
        <h2>手動取込</h2>
        <p>設定保存後、下のボタンで今すぐ取り込めます（自動でも毎日1回取り込みます）。</p>
        <form method="post">
            <?php wp_nonce_field('tmf_import_now'); ?>
            <button type="submit" name="tmf_import" class="button button-primary button-hero">▶ 今すぐ取り込む</button>
        </form>

        <hr>
        <h2>相場ページの作り方</h2>
        <ol>
            <li><b>固定ページ→新規追加</b>でページを作成（例：タイトル「相場検索」）</li>
            <li>右側「ページ属性 → テンプレート」で <b>「相場検索ページ」</b> を選択</li>
            <li>公開（社内用にしたい場合は「表示状態」をパスワード保護/非公開に）</li>
        </ol>
    </div>
    <?php
}
