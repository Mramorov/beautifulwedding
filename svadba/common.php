<?php
/**
 * Shared helpers for Svadba features
 */
if (!defined('ABSPATH')) { exit; }

if (!function_exists('svadba_get_labels')) {
    /**
     * Return shared labels used across svadba form and packets.
     *
     * @return array key => human-readable label
     */
    function svadba_get_labels() {
        $labels = array(
            'trans' => 'Автомобиль для трансфера из аэропорта в отель и обратно',
            'auto'  => 'Автомобиль в день бракосочетания',
            'photo' => 'Фотосъёмка свадебной церемонии, прогулки по Праге (часов)',
            'video' => 'Видеосъёмка свадебной церемонии, прогулки по Праге (часов)',
            'bqt'   => 'Букет невесты',
            'cake'  => 'Свадебный торт',
            'post'  => 'Отправка экспресс почтой EMS/DHL/Fedex свидетельства о браке',
        );

        return apply_filters('svadba_labels', $labels);
    }
}

if (!function_exists('svadba_get_service_order')) {
    /**
     * Return sort priority for service keys (lower number = earlier in list).
     * Services not in this list get default priority 999.
     *
     * @return array pr_key => sort priority
     */
    function svadba_get_service_order() {
        return array(
            'auto'  => 10,
            'trans' => 20,
            'photo' => 30,
            'video' => 40,
            'bqt'   => 50,
            'cake'  => 60,
            'post'  => 70,
            // Other services will get default priority 999
        );
    }
}
