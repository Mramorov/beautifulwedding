<?php
/**
 * Shortcodes: Service price tables for beautifulwedding
 *
 * Provides [bw_services] for a single table (one section):
 * Usage: [bw_services keys="photo" title="..."]
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
    $name    = (string)$item->sname;
    $detail  = (string)$item->sdetail;
    $prefixes = [
        'photo' => 'Фотосъёмка – ',
        'video' => 'Видеосъёмка – ',
    ];
    $base = (isset($prefixes[$item->pr_key]) ? $prefixes[$item->pr_key] : '') . $name;
    return $detail ? ($base . ' ' . $detail) : $base;
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
    $out .= '<table class="price-table-table">';
    $out .= '<thead>';
    $out .= '<tr class="price-table-header">';
    $out .= '<th scope="col" class="price-cell price-cell-name">Название услуги</th>';
    $out .= '<th scope="col" class="price-cell price-cell-price">Цена</th>';
    $out .= '</tr>';
    $out .= '</thead>';
    $out .= '<tbody>';

    foreach ($items as $item) {
        $out .= '<tr class="price-table-row">';
        $out .= '<td class="price-cell price-cell-name">' . esc_html(bw_format_service_name($item)) . '</td>';
        $out .= '<td class="price-cell price-cell-price">' . esc_html(bw_format_price_value($item->sprice)) . '</td>';
        $out .= '</tr>';
    }

    $out .= '</tbody>';
    $out .= '</table>';
    $out .= '</div></div>';
    return $out;
}

/** Main shortcode: [bw_services] */
function bw_services_shortcode($atts) {
    $a = shortcode_atts([
        'keys'   => '',         
        'title'  => '',        
    ], $atts);

    $key = trim((string)$a['keys']);
    if ($key === '') { return ''; }
    return bw_render_price_section($a['title'], [$key]);
}
add_shortcode('bw_services', 'bw_services_shortcode');
