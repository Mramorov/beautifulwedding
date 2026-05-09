<?php

/**
 * Shared helpers for Svadba features
 */
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Get available packets configuration
 * @return array Associative array: index => ['name' => 'Название пакета']
 */
if (!function_exists('svadba_get_packets')) {
    function svadba_get_packets() {
        return array(
            1 => array('name' => 'Super Best'),
            2 => array('name' => 'Exclusive')
            // Можно добавить пакет 3 и 4 позже
        );
    }
}

if (!function_exists('svadba_get_labels')) {
    /**
     * Return shared labels used across svadba form and packets.
     *
     * @return array key => human-readable label
     */
    function svadba_get_labels()
    {
        $labels = array(
            'trans' => 'Автомобиль для трансфера из аэропорта в отель и обратно',
            'auto'  => 'Автомобиль в день бракосочетания',
            'photo' => 'Фотосъёмка свадебной церемонии, прогулки по Праге',
            'video' => 'Видеосъёмка свадебной церемонии, прогулки по Праге',
            'bqt'   => 'Букет невесты',
            'cake'  => 'Свадебный торт',
            'post'  => 'Отправка экспресс почтой EMS/DHL/Fedex свидетельства о браке',
            // Дополнено из словаря в svadba.js
            'car_hours' => 'Время автомобиля (час)',
            'additional_services' => 'Другие услуги',
            'services_sum' => 'В том числе дополнительных услуг на сумму',
            // Технические ключи формы
            'packet' => 'Пакет',
            'price'  => 'Цена',
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
    function svadba_get_service_order()
    {
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
