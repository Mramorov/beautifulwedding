<?php
/**
 * Unified services configuration.
 * Defines preset section groupings for service tables and tabs.
 */
if (!defined('ABSPATH')) { exit; }

// Preset → list of sections (each section: title + keys array)
$bw_service_presets = [
  'photo-video' => [
    ['title' => 'Фотосъёмка свадебной церемонии, прогулки по Праге', 'keys' => ['photo']],
    ['title' => 'Видеосъёмка в день свадьбы', 'keys' => ['video']],
    ['title' => 'Дополнительные услуги', 'keys' => ['phvid']],
  ],
  'auto' => [
    ['title' => 'Трансфер аэропорт-отель и трансферы по городу (в одну сторону)', 'keys' => ['trans']],
    ['title' => 'Транспорт в день свадьбы (минимально 2 часа, цена указана за 1 час)', 'keys' => ['auto']],
  ],
  'hair-makeup' => [
    ['title' => '', 'keys' => ['hair']],
  ],
  'flowers' => [
    ['title' => '', 'keys' => ['bqt']],
  ],
  'cakes' => [
    ['title' => '', 'keys' => ['cake']],
  ],
  'dresses' => [
    ['title' => '', 'keys' => ['dress']],
  ],
  'other' => [
    ['title' => 'Название услуги', 'keys' => ['other']],
    ['title' => 'Почтовые услуги', 'keys' => ['post']],
  ],
];

// Tabs configuration (links tab key to title + sections)
$bw_tabs_config = [
  'weddings' => [
    'title' => 'Свадьбы в Чехии',
    'sections' => [], // отдельная логика генерируется в шаблоне
  ],
  'photo-video' => [
    'title' => 'Фото, видео',
    'sections' => $bw_service_presets['photo-video'],
  ],
  'auto' => [
    'title' => 'Автомобили',
    'sections' => $bw_service_presets['auto'],
  ],
  'hair-makeup' => [
    'title' => 'Причёски, макияж',
    'sections' => $bw_service_presets['hair-makeup'],
  ],
  'flowers' => [
    'title' => 'Цветы',
    'sections' => $bw_service_presets['flowers'],
  ],
  'cakes' => [
    'title' => 'Торты',
    'sections' => $bw_service_presets['cakes'],
  ],
  'dresses' => [
    'title' => 'Платья',
    'sections' => $bw_service_presets['dresses'],
  ],
  'other' => [
    'title' => 'Прочие услуги',
    'sections' => $bw_service_presets['other'],
  ],
];

// Allow external modification if ever needed
$bw_service_presets = apply_filters('bw_service_presets', $bw_service_presets);
$bw_tabs_config     = apply_filters('bw_tabs_config', $bw_tabs_config);

// Unified pricing coefficients & constants
if (!defined('BW_AUTO_DEDUCTION_COEF'))      define('BW_AUTO_DEDUCTION_COEF', 0.7); // коэффициент вычета базового авто
if (!defined('BW_PACKET_DISCOUNT_COEF'))     define('BW_PACKET_DISCOUNT_COEF', 0.8); // коэффициент скидки пакета
if (!defined('BW_TRAVEL_RATE_PHOTO_VIDEO'))  define('BW_TRAVEL_RATE_PHOTO_VIDEO', 50); // доплата за дорогу (за единицу свыше базовой дистанции)
if (!defined('BW_MIN_DISTANCE'))             define('BW_MIN_DISTANCE', 2); // минимальная дистанция
if (!defined('BW_ROUND_STEP'))               define('BW_ROUND_STEP', 10); // шаг округления

$bw_pricing = [
  'auto_deduction_coef' => BW_AUTO_DEDUCTION_COEF,
  'packet_discount_coef' => BW_PACKET_DISCOUNT_COEF,
  'travel_rate_photo_video' => BW_TRAVEL_RATE_PHOTO_VIDEO,
  'min_distance' => BW_MIN_DISTANCE,
  'round_step' => BW_ROUND_STEP,
];
