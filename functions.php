<?php
/**
 * Kami Import Top Theme
 * - Reads data directly from wp_kami_import table and renders on TOP (front page) via index.php
 */

if (!defined('ABSPATH')) { exit; }

/**
 * Get rows from wp_kami_import.
 * You can change limit via filter: kami_import_limit
 */
function kami_import_get_items() {
    global $wpdb;
    $table = $wpdb->prefix . 'kami_import';

    $limit = apply_filters('kami_import_limit', 60);
    $limit = (int)$limit;
    if ($limit <= 0) { $limit = 60; }

    // show publish only (as seen in your table)
    $sql = $wpdb->prepare(
        "SELECT * FROM {$table} WHERE post_status = %s ORDER BY id DESC LIMIT %d",
        'publish',
        $limit
    );

    return $wpdb->get_results($sql);
}

/**
 * Normalize thumbnail URL:
 * - if starts with http(s): return as-is
 * - if starts with / : prepend site_url()
 * - otherwise: prepend site_url('/') 
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
 * Basic theme setup.
 */
add_action('after_setup_theme', function() {
    add_theme_support('title-tag');
});
