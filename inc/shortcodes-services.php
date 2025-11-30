<?php
/**
 * Shortcodes: Service price tables for beautifulwedding
 *
 * Provides [bw_services] with two usage modes:
 * 1) Preset pages (multiple tables at once): [bw_services preset="photo-video|auto|hair-makeup|flowers|cakes|dresses|other"]
 * 2) Single table (one section): [bw_services keys="photo" title="..."]
 */

if (!defined('ABSPATH')) { exit; }

/** Fetch items by pr_key array */
function bw_get_prices_by_keys($keys) {
    global $wpdb;
    if (empty($keys)) { return array(); }
    $placeholders = implode(',', array_fill(0, count($keys), '%s'));
    $sql = $wpdb->prepare(
        "SELECT sname, sdetail, sprice, pr_key FROM {$wpdb->prefix}svadba_prices WHERE pr_key IN ($placeholders) ORDER BY id",
        $keys
    );
    return $wpdb->get_results($sql);
}

/** Format price with euro, support -999 as 'договорная' */
function bw_format_price_value($price) {
    if ($price == -999) {
        return 'договорная';
    }
    return number_format((float)$price, 0, ',', ' ') . ' €';
}

/** Format service name with special prefixes for photo/video */
function bw_format_service_name($item) {
    $name = (string)$item->sname;
    $detail = (string)$item->sdetail;
    if ($item->pr_key === 'photo') {
        $base = 'Фотосъёмка – ' . $name;
        return $detail ? $base . ' ' . $detail : $base;
    }
    if ($item->pr_key === 'video') {
        $base = 'Видеосъёмка – ' . $name;
        return $detail ? $base . ' ' . $detail : $base;
    }
    return $detail ? ($name . ' ' . $detail) : $name;
}

/** Render a single section (title + table by keys) */
function bw_render_price_section($title, $keys) {
    $items = bw_get_prices_by_keys($keys);
    if (empty($items)) { return ''; }

    $out  = '<div class="price-section">';
    if (!empty($title)) {
        $out .= '<h3>' . esc_html($title) . '</h3>';
    }
    $out .= '<div class="price-table">';
    $out .= '<div class="price-table-header">';
    $out .= '<div class="price-cell price-cell-name">Название услуги</div>';
    $out .= '<div class="price-cell price-cell-price">Цена</div>';
    $out .= '</div>';

    foreach ($items as $item) {
        $out .= '<div class="price-table-row">';
        $out .= '<div class="price-cell price-cell-name">' . esc_html(bw_format_service_name($item)) . '</div>';
        $out .= '<div class="price-cell price-cell-price">' . esc_html(bw_format_price_value($item->sprice)) . '</div>';
        $out .= '</div>';
    }

    $out .= '</div></div>';
    return $out;
}

/** Main shortcode: [bw_services] */
function bw_services_shortcode($atts) {
    $a = shortcode_atts([
        'preset' => '',         // name of preset from config
        'keys'   => '',         // comma-separated pr_key list for single section
        'title'  => '',         // title for single section
    ], $atts);

    // Preset mode using unified config
    if ($a['preset'] !== '') {
        global $bw_service_presets;
        $preset = trim($a['preset']);
        $sections = isset($bw_service_presets[$preset]) ? $bw_service_presets[$preset] : [];
        if (empty($sections)) { return ''; }
        $output = '';
        foreach ($sections as $section) {
            $output .= bw_render_price_section($section['title'], $section['keys']);
        }
        return $output;
    }

    // Single section mode
    $keys = array_filter(array_map('trim', explode(',', (string)$a['keys'])));
    if (empty($keys)) { return ''; }
    return bw_render_price_section($a['title'], $keys);
}
add_shortcode('bw_services', 'bw_services_shortcode');
