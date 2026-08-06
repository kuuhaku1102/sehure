<?php
/**
 * TOREKAMAFIA 山口店 - 公式テーマ
 *
 * トレカ買取専門店「トレカマフィア山口店」のための
 * 近未来 / サイバー系デザインテーマ。
 *
 * - カスタマイザーで店舗情報・キャッチコピー・SNS等を管理画面から編集可能
 * - カスタム投稿「お知らせ / イベント」「強化買取カード」で更新しやすい構成
 * - LocalBusiness 構造化データ・OGP・メタ出力によるローカルSEO最適化
 *
 * @package Torekamafia_Yamaguchi
 */

if (!defined('ABSPATH')) { exit; }

define('TMF_VERSION', '1.0.0');

/* ============================================================
 * 1. テーマ基本セットアップ
 * ========================================================== */
function tmf_setup() {
    load_theme_textdomain('tmf', get_template_directory() . '/languages');

    add_theme_support('title-tag');
    add_theme_support('post-thumbnails');
    add_theme_support('automatic-feed-links');
    add_theme_support('html5', array('search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'style', 'script'));
    add_theme_support('custom-logo', array(
        'height'      => 80,
        'width'       => 240,
        'flex-height' => true,
        'flex-width'  => true,
    ));
    add_theme_support('customize-selective-refresh-widgets');

    register_nav_menus(array(
        'primary' => __('ヘッダーメニュー', 'tmf'),
        'footer'  => __('フッターメニュー', 'tmf'),
    ));
}
add_action('after_setup_theme', 'tmf_setup');

/* ============================================================
 * 2. CSS / JS 読み込み
 * ========================================================== */
function tmf_assets() {
    // Google Fonts（近未来系見出し + 日本語本文）
    wp_enqueue_style(
        'tmf-fonts',
        'https://fonts.googleapis.com/css2?family=Orbitron:wght@500;700;900&family=Noto+Sans+JP:wght@400;500;700;900&display=swap',
        array(),
        null
    );

    // メインスタイル（style.css）
    wp_enqueue_style('tmf-style', get_stylesheet_uri(), array('tmf-fonts'), TMF_VERSION);

    // メインスクリプト
    wp_enqueue_script('tmf-main', get_template_directory_uri() . '/assets/js/main.js', array(), TMF_VERSION, true);

    if (is_singular() && comments_open() && get_option('thread_comments')) {
        wp_enqueue_script('comment-reply');
    }
}
add_action('wp_enqueue_scripts', 'tmf_assets');

/* ============================================================
 * 3. カスタム投稿タイプ
 *    - news       : お知らせ / イベント
 *    - kaitori     : 強化買取カード（画像＋価格）
 * ========================================================== */
function tmf_register_post_types() {

    register_post_type('news', array(
        'labels' => array(
            'name'               => 'お知らせ・イベント',
            'singular_name'      => 'お知らせ',
            'add_new'            => '新規追加',
            'add_new_item'       => 'お知らせを追加',
            'edit_item'          => 'お知らせを編集',
            'new_item'           => '新しいお知らせ',
            'view_item'          => 'お知らせを見る',
            'search_items'       => 'お知らせを検索',
            'not_found'          => 'お知らせがありません',
            'menu_name'          => 'お知らせ・イベント',
        ),
        'public'        => true,
        'has_archive'   => true,
        'menu_icon'     => 'dashicons-megaphone',
        'menu_position' => 5,
        'rewrite'       => array('slug' => 'news'),
        'supports'      => array('title', 'editor', 'thumbnail', 'excerpt'),
        'show_in_rest'  => true,
    ));

    register_post_type('kaitori', array(
        'labels' => array(
            'name'               => '強化買取カード',
            'singular_name'      => '強化買取カード',
            'add_new'            => '新規追加',
            'add_new_item'       => 'カードを追加',
            'edit_item'          => 'カードを編集',
            'new_item'           => '新しいカード',
            'view_item'          => 'カードを見る',
            'search_items'       => 'カードを検索',
            'not_found'          => 'カードがありません',
            'menu_name'          => '強化買取カード',
        ),
        'public'        => true,
        'has_archive'   => true,
        'menu_icon'     => 'dashicons-tickets-alt',
        'menu_position' => 6,
        'rewrite'       => array('slug' => 'kaitori'),
        'supports'      => array('title', 'thumbnail', 'page-attributes'),
        'show_in_rest'  => true,
    ));

    // 買取カテゴリー（ポケカ / ワンピ / 遊戯王 など）
    register_taxonomy('kaitori_cat', 'kaitori', array(
        'labels' => array(
            'name'          => '買取カテゴリー',
            'singular_name' => '買取カテゴリー',
            'add_new_item'  => 'カテゴリーを追加',
            'menu_name'     => '買取カテゴリー',
        ),
        'public'            => true,
        'hierarchical'      => true,
        'show_admin_column' => true,
        'show_in_rest'      => true,
        'rewrite'           => array('slug' => 'kaitori-cat'),
    ));
}
add_action('init', 'tmf_register_post_types');

/* ============================================================
 * 4. 強化買取カードのメタ（買取価格）入力欄
 * ========================================================== */
function tmf_kaitori_meta_box() {
    add_meta_box('tmf_kaitori_price', '買取情報', 'tmf_kaitori_meta_html', 'kaitori', 'side', 'high');
}
add_action('add_meta_boxes', 'tmf_kaitori_meta_box');

function tmf_kaitori_meta_html($post) {
    wp_nonce_field('tmf_kaitori_meta', 'tmf_kaitori_nonce');
    $price = get_post_meta($post->ID, '_tmf_price', true);
    $note  = get_post_meta($post->ID, '_tmf_note', true);
    $badge = get_post_meta($post->ID, '_tmf_badge', true);
    ?>
    <p>
        <label for="tmf_price"><strong>買取価格（円）</strong></label><br>
        <input type="number" id="tmf_price" name="tmf_price" value="<?php echo esc_attr($price); ?>" style="width:100%" placeholder="例: 120000">
        <small style="color:#666">数字のみ。空欄なら「要査定」と表示されます。</small>
    </p>
    <p>
        <label for="tmf_badge"><strong>バッジ表記</strong></label><br>
        <input type="text" id="tmf_badge" name="tmf_badge" value="<?php echo esc_attr($badge); ?>" style="width:100%" placeholder="例: 買取強化中 / UP">
    </p>
    <p>
        <label for="tmf_note"><strong>補足メモ</strong></label><br>
        <input type="text" id="tmf_note" name="tmf_note" value="<?php echo esc_attr($note); ?>" style="width:100%" placeholder="例: PSA10 / 美品">
    </p>
    <?php
}

function tmf_save_kaitori_meta($post_id) {
    if (!isset($_POST['tmf_kaitori_nonce']) || !wp_verify_nonce($_POST['tmf_kaitori_nonce'], 'tmf_kaitori_meta')) return;
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
    if (!current_user_can('edit_post', $post_id)) return;

    if (isset($_POST['tmf_price'])) {
        update_post_meta($post_id, '_tmf_price', sanitize_text_field($_POST['tmf_price']));
    }
    if (isset($_POST['tmf_badge'])) {
        update_post_meta($post_id, '_tmf_badge', sanitize_text_field($_POST['tmf_badge']));
    }
    if (isset($_POST['tmf_note'])) {
        update_post_meta($post_id, '_tmf_note', sanitize_text_field($_POST['tmf_note']));
    }
}
add_action('save_post_kaitori', 'tmf_save_kaitori_meta');

/* ============================================================
 * 5. カスタマイザー（管理画面から編集できる設定）
 * ========================================================== */
function tmf_customize_register($wp_customize) {

    $wp_customize->add_panel('tmf_panel', array(
        'title'    => '★ サイト設定（トレカマフィア）',
        'priority' => 1,
    ));

    /* --- 店舗基本情報 --- */
    $wp_customize->add_section('tmf_store', array(
        'title' => '店舗基本情報',
        'panel' => 'tmf_panel',
    ));

    $store_fields = array(
        'tmf_shop_name'    => array('label' => '店舗名', 'default' => 'TOREKAMAFIA 山口店'),
        'tmf_shop_name_ja' => array('label' => '店舗名（日本語）', 'default' => 'トレカマフィア 山口店'),
        'tmf_tel'          => array('label' => '電話番号', 'default' => ''),
        'tmf_address'      => array('label' => '住所', 'default' => '山口県周南市大字久米（国道2号沿い・桜木）'),
        'tmf_access'       => array('label' => 'アクセス', 'default' => '国道2号沿い／お車でのご来店に便利'),
        'tmf_hours'        => array('label' => '営業時間', 'default' => '13:00 〜 20:00'),
        'tmf_holiday'      => array('label' => '定休日', 'default' => '水曜日'),
        'tmf_line'         => array('label' => 'LINE 査定URL', 'default' => ''),
        'tmf_x'            => array('label' => 'X（Twitter）URL', 'default' => 'https://x.com/nt6GT1Zbok62018'),
        'tmf_instagram'    => array('label' => 'Instagram URL', 'default' => ''),
        'tmf_tel_link'     => array('label' => '電話発信用番号（数字のみ）', 'default' => ''),
    );
    foreach ($store_fields as $id => $f) {
        $wp_customize->add_setting($id, array('default' => $f['default'], 'sanitize_callback' => 'wp_kses_post', 'transport' => 'refresh'));
        $wp_customize->add_control($id, array('label' => $f['label'], 'section' => 'tmf_store', 'type' => 'text'));
    }

    // Googleマップ 埋め込み iframe src
    $wp_customize->add_setting('tmf_map_src', array('default' => '', 'sanitize_callback' => 'esc_url_raw'));
    $wp_customize->add_control('tmf_map_src', array(
        'label'       => 'Googleマップ 埋め込みURL（iframeのsrc）',
        'description' => 'Googleマップ →「共有」→「地図を埋め込む」の src="..." 部分を貼り付け',
        'section'     => 'tmf_store',
        'type'        => 'url',
    ));

    /* --- ヒーロー（トップ最上部） --- */
    $wp_customize->add_section('tmf_hero', array(
        'title' => 'ヒーロー（トップ最上部）',
        'panel' => 'tmf_panel',
    ));

    $hero_text = array(
        'tmf_hero_kicker'  => array('label' => '上部の小見出し', 'default' => '山口・広島エリア 最高水準の買取', 'type' => 'text'),
        'tmf_hero_title'   => array('label' => 'メインコピー', 'default' => 'その一枚、<br>東京基準で買い取る。', 'type' => 'textarea'),
        'tmf_hero_sub'     => array('label' => 'サブコピー', 'default' => '秋葉原水準の買取表 × 迅速査定 × 即日お支払い。ポケカ・ワンピ・遊戯王、高価買取はトレカマフィア山口店へ。', 'type' => 'textarea'),
        'tmf_cta1_text'    => array('label' => 'CTAボタン1 テキスト', 'default' => 'LINEで無料査定', 'type' => 'text'),
        'tmf_cta1_url'     => array('label' => 'CTAボタン1 URL', 'default' => '', 'type' => 'text'),
        'tmf_cta2_text'    => array('label' => 'CTAボタン2 テキスト', 'default' => '買取の流れを見る', 'type' => 'text'),
        'tmf_cta2_url'     => array('label' => 'CTAボタン2 URL', 'default' => '#flow', 'type' => 'text'),
    );
    foreach ($hero_text as $id => $f) {
        $wp_customize->add_setting($id, array('default' => $f['default'], 'sanitize_callback' => 'wp_kses_post', 'transport' => 'refresh'));
        $wp_customize->add_control($id, array('label' => $f['label'], 'section' => 'tmf_hero', 'type' => $f['type']));
    }

    // ヒーロー背景画像
    $wp_customize->add_setting('tmf_hero_bg', array('default' => '', 'sanitize_callback' => 'esc_url_raw'));
    $wp_customize->add_control(new WP_Customize_Image_Control($wp_customize, 'tmf_hero_bg', array(
        'label'   => 'ヒーロー背景画像（任意）',
        'section' => 'tmf_hero',
    )));

    /* --- 実績カウンター（数字アニメーション） --- */
    $wp_customize->add_section('tmf_stats', array(
        'title' => '実績カウンター',
        'panel' => 'tmf_panel',
    ));
    $stats = array(
        'tmf_stat1_num'  => array('label' => '数値1', 'default' => '95'),
        'tmf_stat1_unit' => array('label' => '単位1', 'default' => '%'),
        'tmf_stat1_lbl'  => array('label' => 'ラベル1', 'default' => '相場連動の買取率'),
        'tmf_stat2_num'  => array('label' => '数値2', 'default' => '10'),
        'tmf_stat2_unit' => array('label' => '単位2', 'default' => '分〜'),
        'tmf_stat2_lbl'  => array('label' => 'ラベル2', 'default' => 'スピード査定'),
        'tmf_stat3_num'  => array('label' => '数値3', 'default' => '0'),
        'tmf_stat3_unit' => array('label' => '単位3', 'default' => '円'),
        'tmf_stat3_lbl'  => array('label' => 'ラベル3', 'default' => '査定・キャンセル手数料'),
    );
    foreach ($stats as $id => $f) {
        $wp_customize->add_setting($id, array('default' => $f['default'], 'sanitize_callback' => 'sanitize_text_field'));
        $wp_customize->add_control($id, array('label' => $f['label'], 'section' => 'tmf_stats', 'type' => 'text'));
    }

    /* --- SEO --- */
    $wp_customize->add_section('tmf_seo', array(
        'title' => 'SEO設定',
        'panel' => 'tmf_panel',
    ));
    $wp_customize->add_setting('tmf_meta_desc', array(
        'default'           => '山口県周南市のトレカ買取専門店「トレカマフィア山口店」。ポケモンカード・ワンピース・遊戯王などを秋葉原水準の買取表で高価買取。迅速査定・即日お支払い、山口・広島エリア最高水準の買取をお約束します。',
        'sanitize_callback' => 'sanitize_textarea_field',
    ));
    $wp_customize->add_control('tmf_meta_desc', array(
        'label'   => 'メタディスクリプション（トップページ）',
        'section' => 'tmf_seo',
        'type'    => 'textarea',
    ));
    $wp_customize->add_setting('tmf_og_image', array('default' => '', 'sanitize_callback' => 'esc_url_raw'));
    $wp_customize->add_control(new WP_Customize_Image_Control($wp_customize, 'tmf_og_image', array(
        'label'       => 'OGP画像（SNSシェア画像）',
        'section'     => 'tmf_seo',
    )));
}
add_action('customize_register', 'tmf_customize_register');

/**
 * 設定値取得のヘルパー
 */
function tmf_opt($key, $default = '') {
    return get_theme_mod($key, $default);
}

/* ============================================================
 * 6. SEO：メタ・OGP・構造化データ出力
 * ========================================================== */
function tmf_seo_meta() {
    $desc = '';
    if (is_front_page()) {
        $desc = tmf_opt('tmf_meta_desc');
    } elseif (is_singular()) {
        $desc = get_the_excerpt();
    } elseif (is_post_type_archive('news')) {
        $desc = 'トレカマフィア山口店のお知らせ・イベント情報一覧。オリパやキャンペーン、買取強化情報を随時更新中。';
    } elseif (is_post_type_archive('kaitori')) {
        $desc = 'トレカマフィア山口店の強化買取カード一覧。ポケカ・ワンピ・遊戯王など、高価買取中のカードをチェック。';
    }
    $desc = trim(wp_strip_all_tags($desc));
    if ($desc === '') { $desc = tmf_opt('tmf_meta_desc'); }
    $desc = mb_substr($desc, 0, 120);

    $title    = wp_get_document_title();
    $url      = home_url(add_query_arg(array(), $GLOBALS['wp']->request ?? ''));
    $sitename = get_bloginfo('name');

    $og_image = '';
    if (is_singular() && has_post_thumbnail()) {
        $og_image = get_the_post_thumbnail_url(get_the_ID(), 'large');
    }
    if (!$og_image) { $og_image = tmf_opt('tmf_og_image'); }

    echo "\n<!-- TMF SEO -->\n";
    echo '<meta name="description" content="' . esc_attr($desc) . "\">\n";
    echo '<meta property="og:type" content="' . (is_singular() ? 'article' : 'website') . "\">\n";
    echo '<meta property="og:title" content="' . esc_attr($title) . "\">\n";
    echo '<meta property="og:description" content="' . esc_attr($desc) . "\">\n";
    echo '<meta property="og:site_name" content="' . esc_attr($sitename) . "\">\n";
    echo '<meta property="og:url" content="' . esc_url($url) . "\">\n";
    if ($og_image) {
        echo '<meta property="og:image" content="' . esc_url($og_image) . "\">\n";
    }
    echo '<meta name="twitter:card" content="summary_large_image">' . "\n";
    $x = tmf_opt('tmf_x');
    if ($x && preg_match('#x\.com/([^/?]+)#', $x, $m)) {
        echo '<meta name="twitter:site" content="@' . esc_attr($m[1]) . "\">\n";
    }
    echo "<!-- /TMF SEO -->\n";
}
add_action('wp_head', 'tmf_seo_meta', 5);

/**
 * LocalBusiness（Store）構造化データ ― ローカルSEOの要
 */
function tmf_local_business_schema() {
    if (!is_front_page()) return;

    $sameas = array_values(array_filter(array(
        tmf_opt('tmf_x'),
        tmf_opt('tmf_instagram'),
        tmf_opt('tmf_line'),
    )));

    $schema = array(
        '@context'    => 'https://schema.org',
        '@type'       => 'Store',
        'name'        => tmf_opt('tmf_shop_name_ja', 'トレカマフィア 山口店'),
        'alternateName' => tmf_opt('tmf_shop_name', 'TOREKAMAFIA 山口店'),
        'description' => tmf_opt('tmf_meta_desc'),
        'url'         => home_url('/'),
        'image'       => tmf_opt('tmf_og_image') ?: (has_custom_logo() ? wp_get_attachment_image_url(get_theme_mod('custom_logo'), 'full') : ''),
        'address'     => array(
            '@type'           => 'PostalAddress',
            'addressRegion'   => '山口県',
            'addressLocality' => '周南市',
            'streetAddress'   => tmf_opt('tmf_address'),
            'addressCountry'  => 'JP',
        ),
        'priceRange'   => '¥¥',
    );

    // 営業時間仕様（定休日を除外して営業日を列挙）
    $hours = preg_replace('/[^0-9:\-]/', '', str_replace('〜', '-', tmf_opt('tmf_hours', '13:00-20:00')));
    if (preg_match('/^(\d{1,2}:\d{2})-(\d{1,2}:\d{2})$/', $hours, $hm)) {
        $day_map = array(
            '月' => 'Monday', '火' => 'Tuesday', '水' => 'Wednesday', '木' => 'Thursday',
            '金' => 'Friday', '土' => 'Saturday', '日' => 'Sunday',
        );
        $holiday = tmf_opt('tmf_holiday', '水曜日');
        $open_days = array();
        foreach ($day_map as $jp => $en) {
            if (mb_strpos($holiday, $jp) === false) { $open_days[] = $en; }
        }
        $schema['openingHoursSpecification'] = array(
            '@type'     => 'OpeningHoursSpecification',
            'dayOfWeek' => $open_days,
            'opens'     => $hm[1],
            'closes'    => $hm[2],
        );
    }
    if (tmf_opt('tmf_tel')) { $schema['telephone'] = tmf_opt('tmf_tel'); }
    if (!empty($sameas)) { $schema['sameAs'] = $sameas; }

    echo '<script type="application/ld+json">' . wp_json_encode($schema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . '</script>' . "\n";
}
add_action('wp_head', 'tmf_local_business_schema');

/**
 * パンくずリスト構造化データ
 */
function tmf_breadcrumb_schema() {
    if (is_front_page()) return;

    $items = array(array('@type' => 'ListItem', 'position' => 1, 'name' => 'ホーム', 'item' => home_url('/')));
    if (is_singular()) {
        $items[] = array('@type' => 'ListItem', 'position' => 2, 'name' => get_the_title(), 'item' => get_permalink());
    } elseif (is_post_type_archive()) {
        $items[] = array('@type' => 'ListItem', 'position' => 2, 'name' => post_type_archive_title('', false), 'item' => get_post_type_archive_link(get_post_type()));
    }
    $schema = array('@context' => 'https://schema.org', '@type' => 'BreadcrumbList', 'itemListElement' => $items);
    echo '<script type="application/ld+json">' . wp_json_encode($schema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . '</script>' . "\n";
}
add_action('wp_head', 'tmf_breadcrumb_schema');

/* ============================================================
 * 7. 抜粋・その他ユーティリティ
 * ========================================================== */
function tmf_excerpt_length($length) { return 60; }
add_filter('excerpt_length', 'tmf_excerpt_length');

function tmf_excerpt_more($more) { return '…'; }
add_filter('excerpt_more', 'tmf_excerpt_more');

/**
 * 価格を整形（例: 120000 → ¥120,000）
 */
function tmf_format_price($price) {
    $price = preg_replace('/[^0-9]/', '', (string)$price);
    if ($price === '') return '要査定';
    return '¥' . number_format((int)$price);
}

/**
 * 有効化時にパーマリンクをフラッシュ（カスタム投稿URL反映）
 */
function tmf_flush_rewrite() {
    tmf_register_post_types();
    flush_rewrite_rules();
}
add_action('after_switch_theme', 'tmf_flush_rewrite');

/**
 * 買取カテゴリーの初期データ（初回有効化時に作成）
 */
function tmf_seed_categories() {
    if (get_option('tmf_seeded_cats')) return;
    $cats = array('ポケモンカード', 'ワンピースカード', '遊戯王', 'デュエルマスターズ', 'ヴァイスシュヴァルツ', 'MTG');
    foreach ($cats as $c) {
        if (!term_exists($c, 'kaitori_cat')) {
            wp_insert_term($c, 'kaitori_cat');
        }
    }
    update_option('tmf_seeded_cats', 1);
}
add_action('init', 'tmf_seed_categories', 20);
