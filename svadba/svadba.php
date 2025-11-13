<?php
/**
 * Svadba form generator
 * Provides shortcode [svadba_form]
 * Pulls data from table wp_svadba_prices where in_form = 1
 */

if (!defined('ABSPATH')) {
    exit;
}

global $wpdb;
require_once get_template_directory() . '/svadba/common.php';

class SvadbaDataSingleton {
    private static $instance = null;
    public $items_by_key = array();

    // labels shown above selects (shared)
    public $labels = array();

    private function __construct() {
        global $wpdb;
        $table = $wpdb->prefix . 'svadba_prices';

        // load shared labels
        $this->labels = svadba_get_labels();

    // fetch rows where in_form = 1
        $rows = $wpdb->get_results( $wpdb->prepare("SELECT * FROM {$table} WHERE in_form = %d ORDER BY id ASC", 1), ARRAY_A );

        if ($rows) {
            foreach ($rows as $r) {
                $key = isset($r['pr_key']) && $r['pr_key'] !== '' ? $r['pr_key'] : 'other';
                if (!isset($this->items_by_key[$key])) $this->items_by_key[$key] = array();
                $this->items_by_key[$key][] = $r;
            }
        }
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
}


function svadba_generate_form_html() {
    $data = SvadbaDataSingleton::getInstance();

    // Start building HTML
    $html = '<form id="individForm"><div class="individFormWrap">';

    // Calculator header (visual, values will be filled later by JS)
    $html .= '<div class="calcresult-block">';
    $html .= '<div class="calcresult-subblock">';
    $html .= '<span class="calcresult-text text-accent">Сумма набранных услуг</span>';
    $html .= '<div><span class="price-value heading-lg" id="calcresult">0</span><span class="calc-sum-sign"> € </span></div>';
    $html .= '</div>';
    $html .= '<div class="calcresult-subblock">';
    $html .= '<span class="calcresult-text text-accent">Общая сумма индивидуального пакета</span>';
    $html .= '<div><span class="price-value heading-lg" id="total-calcresult">0</span><span class="calc-sum-sign"> € </span></div>';
    $html .= '</div>';
    $html .= '</div>';

    $html .= '<div> <input type="hidden" id="calcField" name="services_sum" value="-1"></div>';

    // Selects block
    $html .= '<div class="select-block">';

    foreach (array_keys($data->labels) as $sel_key) {
        $options = isset($data->items_by_key[$sel_key]) ? $data->items_by_key[$sel_key] : array();
        $label = isset($data->labels[$sel_key]) ? $data->labels[$sel_key] : ucfirst($sel_key);

        // generate select
        $html .= '<div class="select-row">';
        $html .= '<div class="select-label">' . esc_html($label) . '</div>';
        $html .= '<div class="select-control">';
        
        // For auto select, add data attributes for base price and distance
        $auto_data_attrs = '';
        if ($sel_key === 'auto') {
            $distance = (int) get_post_meta(get_the_ID(), 'distance', true);
            if ($distance <= 0) $distance = 1;
            $base_auto_price = isset($options[0]['sprice']) ? $options[0]['sprice'] : 0;
            $auto_data_attrs = ' data-distance="' . esc_attr($distance) . '" data-base-auto-price="' . esc_attr($base_auto_price) . '"';
        }
        
        $html .= '<select class="' . esc_attr($sel_key) . '-select select-element" name="' . esc_attr($sel_key) . '"' . $auto_data_attrs . '>';
        
        // Special case for 'auto': no "Выберите..." option, first item selected by default
        if ($sel_key !== 'auto') {
            $html .= '<option value="" data-calculate="0">Выберите...</option>';
        }
        
        $first = true;
        foreach ($options as $opt) {
            $sname = isset($opt['sname']) ? $opt['sname'] : '';
            $sprice = isset($opt['sprice']) ? $opt['sprice'] : 0;
            // if sdetail available, store it in data-detail attribute
            $data_detail_attr = !empty($opt['sdetail']) ? ' data-detail="' . esc_attr($opt['sdetail']) . '"' : '';
            $selected = ($sel_key === 'auto' && $first) ? ' selected' : '';
            $html .= '<option value="' . esc_attr($sname) . '" data-calculate="' . esc_attr($sprice) . '"' . $data_detail_attr . $selected . '>' . esc_html($sname) . '</option>';
            $first = false;
        }
        $html .= '</select>';
        // add detail container inside select-control, right after select
        $html .= '<div class="select-detail" data-for="' . esc_attr($sel_key) . '"></div>';
        $html .= '</div>'; // .select-control

    // special case for auto: keep an extra hours select (always visible now)
    if ($sel_key === 'auto') {
            // determine distance (if available on post meta)
            $distance = (int) get_post_meta(get_the_ID(), 'distance', true);
            if ($distance <= 0) $distance = 1;
            $html .= '<div class="auto-hours-label">Время автомобиля (час)</div>';
            $html .= '<div class="auto-hours-wrap">';
            $html .= '<select class="auto-hours-select select-element" name="car_hours">';
            for ($h = $distance; $h <= 7; $h++) {
                $selected = ($h === $distance) ? ' selected' : '';
                $html .= '<option value="' . $h . '" data-calculate="' . $h . '"' . $selected . '>' . $h . ' часа.</option>';
            }
            $html .= '</select></div>';
        }

        $html .= '</div>'; // .select-row
    }

    $html .= '</div>'; // .select-block

    // Checkboxes block: gather all keys that are not select_keys
    $html .= '<div class="check-block">';
    $all_keys = array_keys($data->items_by_key);
    $checkbox_keys = array_diff($all_keys, array_keys($data->labels));

    foreach ($checkbox_keys as $chk_key) {
        foreach ($data->items_by_key[$chk_key] as $chk_item) {
            $sname = isset($chk_item['sname']) ? $chk_item['sname'] : '';
            $sprice = isset($chk_item['sprice']) ? $chk_item['sprice'] : 0;
            // Only include items that are to be shown in form (we already filtered by in_form)
            $html .= '<label class="chk-lbl">';
            $html .= '<input type="checkbox" class="checkbox-element" name="additional_services[]" value="' . esc_attr($sname) . '" data-calculate="' . esc_attr($sprice) . '">';
            $html .= '<div class="chk-text-wrapper"><span>' . esc_html($sname) . '</span></div>';
            $html .= '</label>';
        }
    }

    $html .= '</div>'; // .check-block

    // Submit / order button (no modal/contact form included per instructions)
    $html .= '<div class="send-button-wrap"><button type="button" id="orderButton" class="btn btn-large" data-formid="individ">Заказать</button></div>';

    $html .= '</div></form>';

    // Placeholder for messages (as in old project)
    $html .= '<div id="individ-message-form"></div>';

    return $html;
}

function svadba_form_shortcode_handler($atts = array()) {
    return svadba_generate_form_html();
}
add_shortcode('svadba_form', 'svadba_form_shortcode_handler');
