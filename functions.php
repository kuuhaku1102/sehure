<?php
/**
 * Sefure Keijiban Theme
 * - 47都道府県別セフレ掲示板サイト
 */

if (!defined('ABSPATH')) { exit; }

/**
 * 47都道府県リスト
 */
function get_prefectures() {
    return array(
        'hokkaido' => '北海道',
        'aomori' => '青森県',
        'iwate' => '岩手県',
        'miyagi' => '宮城県',
        'akita' => '秋田県',
        'yamagata' => '山形県',
        'fukushima' => '福島県',
        'ibaraki' => '茨城県',
        'tochigi' => '栃木県',
        'gunma' => '群馬県',
        'saitama' => '埼玉県',
        'chiba' => '千葉県',
        'tokyo' => '東京都',
        'kanagawa' => '神奈川県',
        'niigata' => '新潟県',
        'toyama' => '富山県',
        'ishikawa' => '石川県',
        'fukui' => '福井県',
        'yamanashi' => '山梨県',
        'nagano' => '長野県',
        'gifu' => '岐阜県',
        'shizuoka' => '静岡県',
        'aichi' => '愛知県',
        'mie' => '三重県',
        'shiga' => '滋賀県',
        'kyoto' => '京都府',
        'osaka' => '大阪府',
        'hyogo' => '兵庫県',
        'nara' => '奈良県',
        'wakayama' => '和歌山県',
        'tottori' => '鳥取県',
        'shimane' => '島根県',
        'okayama' => '岡山県',
        'hiroshima' => '広島県',
        'yamaguchi' => '山口県',
        'tokushima' => '徳島県',
        'kagawa' => '香川県',
        'ehime' => '愛媛県',
        'kochi' => '高知県',
        'fukuoka' => '福岡県',
        'saga' => '佐賀県',
        'nagasaki' => '長崎県',
        'kumamoto' => '熊本県',
        'oita' => '大分県',
        'miyazaki' => '宮崎県',
        'kagoshima' => '鹿児島県',
        'okinawa' => '沖縄県'
    );
}

/**
 * Get rows from wp_kami_import (全てランダムに取得)
 */
function kami_import_get_items($limit = 60) {
    global $wpdb;
    $table = $wpdb->prefix . 'kami_import';

    $limit = (int)$limit;
    if ($limit <= 0) { $limit = 60; }

    // 全国のデータをランダムに取得
    $sql = $wpdb->prepare(
        "SELECT * FROM {$table} WHERE post_status = %s ORDER BY RAND() LIMIT %d",
        'publish',
        $limit
    );

    return $wpdb->get_results($sql);
}

/**
 * Normalize thumbnail URL
 */
function kami_import_normalize_url($value) {
    $value = trim((string)$value);
    if ($value === '') return '';

    if (preg_match('#^https?://#i', $value)) return esc_url($value);

    if (strpos($value, '//') === 0) return esc_url('https:' . $value);

    if (strpos($value, '/') === 0) return esc_url(site_url($value));

    return esc_url(site_url('/' . $value));
}

/**
 * カスタムリライトルールを追加
 */
function sefure_add_rewrite_rules() {
    $prefectures = get_prefectures();
    
    foreach ($prefectures as $slug => $name) {
        add_rewrite_rule(
            '^' . $slug . '/?$',
            'index.php?prefecture=' . $slug,
            'top'
        );
    }
}
add_action('init', 'sefure_add_rewrite_rules');

/**
 * カスタムクエリ変数を追加
 */
function sefure_query_vars($vars) {
    $vars[] = 'prefecture';
    return $vars;
}
add_filter('query_vars', 'sefure_query_vars');

/**
 * テンプレートリダイレクト
 */
function sefure_template_redirect() {
    $prefecture = get_query_var('prefecture');
    
    if ($prefecture) {
        $prefectures = get_prefectures();
        
        if (isset($prefectures[$prefecture])) {
            $template_file = get_template_directory() . '/' . $prefecture . '.php';
            
            if (file_exists($template_file)) {
                include($template_file);
                exit;
            }
        }
    }
}
add_action('template_redirect', 'sefure_template_redirect');

/**
 * パーマリンク設定を更新した時にリライトルールをフラッシュ
 */
function sefure_flush_rewrite_rules() {
    sefure_add_rewrite_rules();
    flush_rewrite_rules();
}
register_activation_hook(__FILE__, 'sefure_flush_rewrite_rules');

/**
 * Basic theme setup.
 */
add_action('after_setup_theme', function() {
    add_theme_support('title-tag');
});

/**
 * SEO用のメタタグを出力
 */
function sefure_keijiban_meta_tags() {
    $prefecture = get_query_var('prefecture');
    
    if (is_front_page()) {
        echo '<meta name="description" content="全国47都道府県のセフレ掲示板。地域別にセフレを探せる安全な出会いの場を提供します。">' . "\n";
        echo '<meta name="keywords" content="セフレ,掲示板,出会い,全国">' . "\n";
    } elseif ($prefecture) {
        $prefectures = get_prefectures();
        
        if (isset($prefectures[$prefecture])) {
            $pref_name = $prefectures[$prefecture];
            echo '<meta name="description" content="' . esc_attr($pref_name) . 'のセフレ掲示板。' . esc_attr($pref_name) . 'で安全にセフレを探せる出会いの場を提供します。">' . "\n";
            echo '<meta name="keywords" content="セフレ,掲示板,' . esc_attr($pref_name) . ',出会い">' . "\n";
        }
    }
}
add_action('wp_head', 'sefure_keijiban_meta_tags');

/**
 * 構造化データ（パンくずリスト）を出力
 */
function sefure_keijiban_breadcrumb_schema() {
    $prefecture = get_query_var('prefecture');
    
    if (!$prefecture) return;
    
    $prefectures = get_prefectures();
    
    if (isset($prefectures[$prefecture])) {
        $schema = array(
            '@context' => 'https://schema.org',
            '@type' => 'BreadcrumbList',
            'itemListElement' => array(
                array(
                    '@type' => 'ListItem',
                    'position' => 1,
                    'name' => 'ホーム',
                    'item' => home_url('/')
                ),
                array(
                    '@type' => 'ListItem',
                    'position' => 2,
                    'name' => $prefectures[$prefecture] . 'のセフレ掲示板',
                    'item' => home_url('/' . $prefecture . '/')
                )
            )
        );
        
        echo '<script type="application/ld+json">' . json_encode($schema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . '</script>' . "\n";
    }
}
add_action('wp_head', 'sefure_keijiban_breadcrumb_schema');
