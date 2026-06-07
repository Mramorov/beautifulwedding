<?php

/**
 * Svadba form generator
 * Provides shortcode [svadba_form]
 * Pulls data from table wp_svadba_prices where in_form = 1
 */

if (!defined('ABSPATH')) {
    exit;
}

if (!class_exists('SvadbaDataSingleton')) {
    class SvadbaDataSingleton
    {
        private static $instance = null;
        private $wpdb;
        public $items_in_form_1 = array();
        public $items_in_form_2 = array();

        // labels shown above selects (shared)
        public $labels = array();

        private function __construct()
        {
            global $wpdb;
            $this->wpdb = $wpdb;
            $table = $this->wpdb->prefix . 'svadba_prices';

            // load shared labels
            $this->labels = svadba_get_labels();

            // fetch rows where in_form > 0 (includes in_form = 1 and 2)
            $rows = $this->wpdb->get_results($this->wpdb->prepare("SELECT * FROM {$table} WHERE in_form > %d ORDER BY id ASC", 0), ARRAY_A);

            if ($rows) {
                foreach ($rows as $r) {
                    $in_form = isset($r['in_form']) ? (int) $r['in_form'] : 1;

                    if ($in_form === 1) {
                        $key = isset($r['pr_key']) && $r['pr_key'] !== '' ? $r['pr_key'] : 'other';
                        if (!isset($this->items_in_form_1[$key])) $this->items_in_form_1[$key] = array();
                        $this->items_in_form_1[$key][] = $r;
                    } else {
                        $this->items_in_form_2[] = $r;
                    }
                }
            }
        }

        public static function getInstance()
        {
            if (self::$instance === null) {
                self::$instance = new self();
            }
            return self::$instance;
        }
    }
}


if (!function_exists('svadba_generate_form_html')) {
    function svadba_generate_form_html()
    {
        $data = SvadbaDataSingleton::getInstance();

        // Start building HTML
        $html = '<form id="individForm"><div class="individFormWrap">';

        // Calculator header (visual, values will be filled later by JS)
        /*     $html .= '<div class="it-accent small">&#9888; Основной пакет уже включает стоимость базового автомобиля и стандартного времени для данной локации. Эти значения выбраны по умолчанию в соответствующих выпадающих списках. Если выбрать другой автомобиль или изменить время, при расчёте дополнительных услуг из их стоимости сначала будет вычтена стоимость стандартного автомобиля с коэффициентом 0,7.</div>'; */

        $html .= '<div class="calcresult-block">';
        $html .= '<div class="calcresult-subblock">';
        $html .= '<span class="calcresult-text">Сумма дополнительных услуг</span>';
        $html .= '<div><span class="price-value" id="calcresult">0</span><span class="calc-sum-sign"> € </span></div>';
        $html .= '</div>';
        $html .= '<div class="calcresult-subblock">';
        $html .= '<span class="calcresult-text">Общая сумма индивидуального пакета</span>';
        $html .= '<div><span class="price-value" id="total-calcresult">0</span><span class="calc-sum-sign"> € </span></div>';
        $html .= '</div>';
        $html .= '</div>';

        $html .= '<div> <input type="hidden" id="calcField" name="services_sum" value="-1"></div>';

        // Selects block (whitelist via service order)
        $html .= '<div class="select-block">';
        $service_order = function_exists('svadba_get_service_order') ? svadba_get_service_order() : array();
        // сортируем по значению (приоритету)
        asort($service_order, SORT_NUMERIC);
        $select_keys = array_keys($service_order);

        foreach ($select_keys as $sel_key) {
            $options = isset($data->items_in_form_1[$sel_key]) ? $data->items_in_form_1[$sel_key] : array();
            $label = isset($data->labels[$sel_key]) ? $data->labels[$sel_key] : ucfirst($sel_key);

            $html .= '<div class="select-row">';
            $html .= '<div class="select-label">' . esc_html($label) . '</div>';
            $html .= '<div class="select-control">';

            $auto_data_attrs = '';
            if ($sel_key === 'auto') {
                $distance = (int) get_post_meta(get_the_ID(), 'distance', true);
                if ($distance <= 0) $distance = 1;
                $base_auto_price = isset($options[0]['sprice']) ? $options[0]['sprice'] : 0;
                $auto_data_attrs = ' data-distance="' . esc_attr($distance) . '" data-base-auto-price="' . esc_attr($base_auto_price) . '"';
            }

            $html .= '<select class="' . esc_attr($sel_key) . '-select select-element" name="' . esc_attr($sel_key) . '"' . $auto_data_attrs . '>';
            if ($sel_key !== 'auto') {
                $html .= '<option value="" data-calculate="0">Выберите...</option>';
            }

            $first = true;
            foreach ($options as $opt) {
                $sname = isset($opt['sname']) ? $opt['sname'] : '';
                $sprice = isset($opt['sprice']) ? $opt['sprice'] : 0;
                $data_detail_attr = !empty($opt['sdetail']) ? ' data-detail="' . esc_attr($opt['sdetail']) . '"' : '';
                $selected = ($sel_key === 'auto' && $first) ? ' selected' : '';
                $html .= '<option value="' . esc_attr($sname) . '" data-calculate="' . esc_attr($sprice) . '"' . $data_detail_attr . $selected . '>' . esc_html($sname) . '</option>';
                $first = false;
            }
            $html .= '</select>';
            $html .= '<div class="select-detail" data-for="' . esc_attr($sel_key) . '"></div>';
            $html .= '</div>';
            $html .= '</div>';

            if ($sel_key === 'auto') {
                $distance = (int) get_post_meta(get_the_ID(), 'distance', true);
                if ($distance <= 0) {
                    $distance = 1;
                }
                $max_hours = $distance + 6;
                $html .= '<div class="select-row">';
                $html .= '<div class="select-label">Часы автомобиля:</div>';
                $html .= '<div class="select-control">';
                $html .= '<select id="auto-hours-select" class="auto-hours-select select-element" name="car_hours">';
                for ($h = $distance; $h <= $max_hours; $h++) {
                    $sel = ($h === $distance) ? ' selected' : '';
                    $html .= '<option value="' . esc_attr($h) . '"' . $sel . '>' . esc_html($h) . '</option>';
                }
                $html .= '</select>';
                $html .= '</div>';
                $html .= '</div>';
            }
        }
        $html .= '</div>';

        $checkbox_items = $data->items_in_form_2;

        $html .= '<div class="check-block">';

        foreach ($checkbox_items as $chk_item) {
            $sname = isset($chk_item['sname']) ? $chk_item['sname'] : '';
            $sprice = isset($chk_item['sprice']) ? $chk_item['sprice'] : 0;
            // Only include items that are to be shown in form (we already filtered by in_form)
            $html .= '<label class="chk-lbl">';
            $html .= '<input type="checkbox" class="checkbox-element" name="additional_services[]" value="' . esc_attr($sname) . '" data-calculate="' . esc_attr($sprice) . '">';
            $html .= '<div class="chk-text-wrapper"><span>' . esc_html($sname) . '</span></div>';
            $html .= '</label>';
        }

        $html .= '</div>'; // .check-block

        // Submit / order button (no modal/contact form included per instructions)
        $html .= '<div class="send-button-wrap"><button type="button" id="orderButton" class="button-main" data-formid="individ">Заказать</button></div>';

        $html .= '</div></form>';

        // Placeholder for messages (as in old project)
        $html .= '<div id="individ-message-form"></div>';

        return $html;
    }
}

if (!function_exists('svadba_form_shortcode_handler')) {
    function svadba_form_shortcode_handler($atts = array())
    {
        return svadba_generate_form_html();
    }
}
add_shortcode('svadba_form', 'svadba_form_shortcode_handler');
