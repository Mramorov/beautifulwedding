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

class SvadbaDataSingleton {
    private static $instance = null;
    public $items_by_key = array();

    // keys that should be rendered as selects (in this order)
    // New table uses pr_key values: trans, auto, photo, video, bqt, cake, post
    public $select_keys = array(
        'trans',
        'auto',
        'photo',
        'video',
        'bqt',
        'cake',
        'post'
    );

    // labels shown above selects (matches old project / screenshot)
    public $labels = array(
        'trans' => 'Автомобиль для трансфера из аэропорта в отель и обратно',
        'auto' => 'Автомобиль в день бракосочетания',
        'photo' => 'Фотосъёмка свадебной церемонии, прогулки по Праге (часов)',
        'video' => 'Видеосъёмка свадебной церемонии, прогулки по Праге (часов)',
        'bqt' => 'Букет невесты',
        'cake' => 'Свадебный торт',
        'post' => 'Отправка экспресс почтой EMS/DHL/Fedex свидетельства о браке',
    );

    private function __construct() {
        global $wpdb;
        $table = $wpdb->prefix . 'svadba_prices';

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
    $html .= '<span class="calcresult-text">Сумма набранных услуг</span>';
    $html .= '<div><span class="price-value" id="calcresult">0</span><span class="calc-sum-sign"> € </span></div>';
    $html .= '</div>';
    $html .= '<div class="calcresult-subblock">';
    $html .= '<span class="calcresult-text">Общая сумма индивидуального пакета</span>';
    $html .= '<div><span class="price-value" id="total-calcresult">0</span><span class="calc-sum-sign"> € </span></div>';
    $html .= '</div>';
    $html .= '</div>';

    $html .= '<div> <input type="hidden" id="calcField" name="В_том_числе_дополнительных_услуг_на_сумму" value="-1"></div>';

    // Selects block
    $html .= '<div class="select-block">';

    foreach ($data->select_keys as $sel_key) {
        $options = isset($data->items_by_key[$sel_key]) ? $data->items_by_key[$sel_key] : array();
        $label = isset($data->labels[$sel_key]) ? $data->labels[$sel_key] : ucfirst($sel_key);

        // generate select
        $html .= '<div class="select-row">';
        $html .= '<div class="select-label">' . esc_html($label) . '</div>';
        $html .= '<div class="select-control"><select class="' . esc_attr($sel_key) . '-select select-element" name="' . esc_attr($sel_key) . '" id="' . esc_attr($sel_key) . '">';
        $html .= '<option value="" data-calculate="0">Выберите...</option>';
        foreach ($options as $opt) {
            $sname = isset($opt['sname']) ? $opt['sname'] : '';
            $sprice = isset($opt['sprice']) ? $opt['sprice'] : 0;
            $html .= '<option value="' . esc_attr($sname) . '" data-calculate="' . esc_attr($sprice) . '">' . esc_html($sname) . '</option>';
        }
        $html .= '</select></div>';

        // special case for wedding-auto: keep an extra hours select (hidden by default) similar to old code
        if ($sel_key === 'wedding-auto') {
            // determine distance (if available on post meta)
            $distance = (int) get_post_meta(get_the_ID(), 'distance', true);
            if ($distance <= 0) $distance = 1;
            $html .= '<div class="auto-hours-label" style="display: none;">Время автомобиля (час)</div>';
            $html .= '<div class="auto-hours-wrap" style="display: none;">';
            $html .= '<select class="auto-hours-select select-element" name="Время_автомобиля" id="auto-time_select">';
            for ($h = $distance; $h <= 7; $h++) {
                $html .= '<option value="' . $h . '" data-calculate="' . $h . '">' . $h . ' часа.</option>';
            }
            $html .= '</select></div>';
        }

        $html .= '</div>'; // .select-row
    }

    $html .= '</div>'; // .select-block

    // Checkboxes block: gather all keys that are not select_keys
    $html .= '<div class="check-block">';
    $all_keys = array_keys($data->items_by_key);
    $checkbox_keys = array_diff($all_keys, $data->select_keys);

    foreach ($checkbox_keys as $chk_key) {
        foreach ($data->items_by_key[$chk_key] as $chk_item) {
            $sname = isset($chk_item['sname']) ? $chk_item['sname'] : '';
            $sprice = isset($chk_item['sprice']) ? $chk_item['sprice'] : 0;
            // Only include items that are to be shown in form (we already filtered by in_form)
            $html .= '<label class="chk-lbl">';
            $html .= '<input type="checkbox" class="checkbox-element" name="Другие_услуги[]" value="' . esc_attr($sname) . '" data-calculate="' . esc_attr($sprice) . '" id="' . esc_attr(sanitize_title($sname)) . '">';
            $html .= '<span>' . esc_html($sname) . '</span></label>';
        }
    }

    $html .= '</div>'; // .check-block

    // Submit / order button (no modal/contact form included per instructions)
    $html .= '<div class="send-button-wrap"><button type="button" id="orderButton" data-formid="individ">Заказать</button></div>';

    $html .= '</div></form>';

    // Placeholder for messages (as in old project)
    $html .= '<div id="individ-message-form"></div>';

    return $html;
}

function svadba_form_shortcode_handler($atts = array()) {
    return svadba_generate_form_html();
}
add_shortcode('svadba_form', 'svadba_form_shortcode_handler');
