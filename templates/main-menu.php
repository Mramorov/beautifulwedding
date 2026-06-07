<?php

/**
 * Главное навигационное меню сайта
 * 
 * Конфигурируемое меню с поддержкой кастомных выпадающих блоков.
 * Типы пунктов меню:
 * - 'link'           : простая ссылка первого уровня
 * - 'svadba_places'  : выпадающий блок со списком мест свадеб из таксономии location
 * 
 * @package BeautifulWedding
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
 * ВАЖНО: Соответствие слагов таксономии location
 * При изменении слагов терминов в админке обновить здесь!
 * 
 * Активные термины таксономии location:
 * - praga          : Прага
 * - zamki-chehii   : Замки Чехии
 */

$MENU_ITEMS = [
    [
        'title'   => 'Главная',
        'url'     => home_url('/'),
        'classes' => [],
        'type'    => 'link',
        'params'  => [],
    ],
    [
        'title'   => 'Свадьба в Праге',
        'url'     => home_url('/location/svadba-v-prage/'),
        'classes' => ['has-mega-menu'],
        'type'    => 'svadba_places',
        'params'  => [
            'location_slug' => 'svadba-v-prage',
            'columns' => 3,
            'banner_fallback' => wp_get_attachment_image_url(195, 'medium_large'),
        ],
    ],
    [
        'title'   => 'Свадьба в замке',
        'url'     => home_url('/location/svadba-v-zamke-chehii/'),
        'classes' => ['has-mega-menu'],
        'type'    => 'svadba_places',
        'params'  => [
            'location_slug' => 'svadba-v-zamke-chehii',
            'columns' => 3,
            'banner_fallback' => '',
        ],
    ],
    [
        'title'   => 'Свадьба на корабле',
        'url'     => home_url('/location/svadba-na-korable/'),
        'classes' => ['has-mega-menu'],
        'type'    => 'svadba_places',
        'params'  => [
            'location_slug' => '/svadba-na-korable/',
            'columns' => 1,
            'banner_fallback' => '',
        ],
    ],
    [
        'title'   => 'Услуги',
        'url'     => home_url('/service/'),
        'classes' => ['has-mega-menu'],
        'type'    => 'service',
        'params'  => [
            'location_slug' => 'service',
            'columns' => 1,
            'banner_fallback' => '',
        ],
    ],
    [
        'title'   => 'Цены',
        'url'     => home_url('/prajs-svadebnyh-uslug/'),
        'classes' => [],
        'type'    => 'link',
        'params'  => [],
    ],
    [
        'title'   => 'Контакты',
        'url'     => home_url('/contacts/'),
        'classes' => [],
        'type'    => 'link',
        'params'  => [],
    ],
];

/**
 * Регистр рендереров для разных типов пунктов меню
 */
$RENDERERS = [
    // Простая ссылка
    'link' => function (array $item) {
        $classes = implode(' ', $item['classes'] ?? []);
        printf(
            '<li class="%s"><a href="%s">%s</a></li>',
            esc_attr($classes),
            esc_url($item['url'] ?? '#'),
            esc_html($item['title'] ?? '')
        );
    },

    // Выпадающее меню со списком мест свадеб
    'svadba_places' => function (array $item) {
        $title   = $item['title'] ?? '';
        $url     = $item['url'] ?? '#';
        $classes = implode(' ', $item['classes'] ?? []);
        $params  = $item['params'] ?? [];

        echo '<li class="' . esc_attr($classes) . '">';
        echo '<a href="' . esc_url($url) . '">' . esc_html($title) . '</a>';
        echo '<div class="mega-menu"><div class="mega-menu-inner">';
        echo render_svadba_places_dropdown($params);
        echo '</div></div>';
        echo '</li>';
    },

    // Выпадающее меню со списком услуг
    'service' => function (array $item) {
        $title   = $item['title'] ?? '';
        $url     = $item['url'] ?? '#';
        $classes = implode(' ', $item['classes'] ?? []);
        $params  = $item['params'] ?? [];

        echo '<li class="' . esc_attr($classes) . '">';
        echo '<a href="' . esc_url($url) . '">' . esc_html($title) . '</a>';
        echo '<div class="mega-menu"><div class="mega-menu-inner">';
        echo render_service_dropdown($params);
        echo '</div></div>';
        echo '</li>';
    },
];

/**
 * Рендер выпадающего блока со списком мест свадеб
 * 
 * @param array $args {
 *     @type string $location_slug    Слаг термина таксономии location
 *     @type int    $columns          Количество колонок в grid (по умолчанию 2)
 *     @type string $banner_fallback  URL фоллбек-изображения для баннера
 * }
 * @return string HTML выпадающего блока
 */
function render_svadba_places_dropdown(array $args): string
{
    $location_slug = sanitize_title($args['location_slug'] ?? '');
    $columns     = max(1, intval($args['columns'] ?? 2));
    $fallback    = esc_url($args['banner_fallback'] ?? '');

    if (empty($location_slug)) {
        return '<!-- Не указан слаг таксономии location -->';
    }

    // Запрос мест свадеб по таксономии
    $query = new WP_Query([
        'post_type'      => 'svadba',
        'posts_per_page' => -1,
        'orderby'        => 'date',
        'order'          => 'DESC',
        'tax_query'      => [[
            'taxonomy' => 'location',
            'field'    => 'slug',
            'terms'    => $location_slug,
        ]],
    ]);

    // Маппинг дней недели для сортировки
    $ordered_days = [
        'Понедельник' => 'Пн',
        'Вторник'     => 'Вт',
        'Среда'       => 'Ср',
        'Четверг'     => 'Чт',
        'Пятница'     => 'Пт',
        'Суббота'     => 'Сб',
        'Воскресенье' => 'Вс',
    ];

    ob_start();
?>
    <ul class="mega-menu-list" style="<?php echo esc_attr("--mm-cols: {$columns};"); ?>">
        <?php if ($query->have_posts()): ?>
            <?php while ($query->have_posts()): $query->the_post();
                $post_id = get_the_ID();
                $data = [];

                // Цена
                $fromnew = get_post_meta($post_id, 'fromnew', true);
                if (!empty($fromnew)) {
                    $data['fromnew'] = "от {$fromnew} €";
                }

                // Вместимость
                $capacity = get_post_meta($post_id, 'capacity', true);
                if (!empty($capacity)) {
                    $data['capacity'] = "до {$capacity} человек";
                }

                // Церемонии и дни проведения храним как plain-string мета-поля
                $ceremonies = trim((string) get_post_meta($post_id, 'ceremonies', true));
                if ($ceremonies !== '') {
                    $data['ceremonies'] = $ceremonies;
                }

                $wedding_days = trim((string) get_post_meta($post_id, 'wedding_days', true));
                if ($wedding_days !== '') {
                    $days_parts = array_filter(array_map('trim', explode(',', $wedding_days)));
                    if (!empty($days_parts)) {
                        usort($days_parts, function ($a, $b) use ($ordered_days) {
                            $pos_a = array_search($a, array_keys($ordered_days), true);
                            $pos_b = array_search($b, array_keys($ordered_days), true);
                            return ($pos_a === false ? 999 : $pos_a) <=> ($pos_b === false ? 999 : $pos_b);
                        });
                        $short_days = array_map(function ($day) use ($ordered_days) {
                            return $ordered_days[$day] ?? $day;
                        }, $days_parts);
                        $data['wedding_days'] = implode(', ', $short_days);
                    } else {
                        $data['wedding_days'] = $wedding_days;
                    }
                }

                // Залы/места проведения
                $zaly_mesta = get_post_meta($post_id, 'zaly_mesta', true);
                if (is_array($zaly_mesta) && !empty($zaly_mesta)) {
                    $filtered_zaly = [];
                    foreach ($zaly_mesta as $item) {
                        if (!empty($item['mesto'])) {
                            $filtered_zaly[] = $item['mesto'];
                        }
                    }
                    if (!empty($filtered_zaly)) {
                        $data['mesta'] = $filtered_zaly;
                    }
                }

                // JSON для data-info
                $data_attr = esc_attr(wp_json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));

                // Изображение для баннера
                $thumbnail_url = get_the_post_thumbnail_url($post_id, 'medium');
                $data_bg_attr = $thumbnail_url ? ' data-bg="' . esc_url($thumbnail_url) . '"' : '';
            ?>
                <li data-info="<?php echo $data_attr; ?>" <?php echo $data_bg_attr; ?>>
                    <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                </li>
            <?php endwhile;
            wp_reset_postdata(); ?>
        <?php else: ?>
            <li class="empty-list">
                <span>В этой категории пока нет мест</span>
            </li>
        <?php endif; ?>
    </ul>

    <div class="right-mega-banner" <?php echo $fallback ? ' style="background-image:url(' . $fallback . ')"' : ''; ?>>
        <div class="banner-overlay"></div>
        <div class="banner-textblock">
            <p>Наведите мышку на пункт меню для просмотра краткой информации</p>
        </div>
    </div>
<?php
    return ob_get_clean();
}

/**
 * Рендер упрощенного выпадающего блока со списком услуг
 *
 * @param array $args {
 *     @type int    $columns          Количество колонок в grid (по умолчанию 1)
 *     @type string $banner_fallback  URL фоллбек-изображения для баннера
 * }
 * @return string HTML выпадающего блока
 */
function render_service_dropdown(array $args): string
{
    $columns  = max(1, intval($args['columns'] ?? 1));
    $fallback = esc_url($args['banner_fallback'] ?? '');

    // Услуги выводятся без фильтрации по таксономии.
    $query = new WP_Query([
        'post_type'      => 'service',
        'posts_per_page' => -1,
        'orderby'        => 'date',
        'order'          => 'DESC',
    ]);

    ob_start();
?>
    <ul class="mega-menu-list" style="<?php echo esc_attr("--mm-cols: {$columns};"); ?>">
        <?php if ($query->have_posts()): ?>
            <?php while ($query->have_posts()): $query->the_post();
                $post_id = get_the_ID();
                $thumbnail_url = get_the_post_thumbnail_url($post_id, 'medium');
                $data_bg_attr = $thumbnail_url ? ' data-bg="' . esc_url($thumbnail_url) . '"' : '';
            ?>
                <li <?php echo $data_bg_attr; ?>>
                    <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                </li>
            <?php endwhile;
            wp_reset_postdata(); ?>
        <?php else: ?>
            <li class="empty-list">
                <span>В этой категории пока нет услуг</span>
            </li>
        <?php endif; ?>
    </ul>

    <div class="right-mega-banner" <?php echo $fallback ? ' style="background-image:url(' . $fallback . ')"' : ''; ?>>
        <div class="banner-overlay banner-overlay--service"></div>
    </div>
<?php
    return ob_get_clean();
}

// Рендер меню
?>
<nav class="main-navigation">
    <ul class="menu-list custom-mega-menu">
        <?php
        foreach ($MENU_ITEMS as $item) {
            $type = $item['type'] ?? 'link';
            if (isset($RENDERERS[$type]) && is_callable($RENDERERS[$type])) {
                $RENDERERS[$type]($item);
            }
        }
        ?>
    </ul>
</nav>