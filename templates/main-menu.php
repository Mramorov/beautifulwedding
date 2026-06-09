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
 */

$MENU_ITEMS = [
    [
        'title'   => 'Главная',
        'url'     => home_url('/'),
        'classes' => [],
        'type'    => 'link',
    ],
    [
        'title'   => 'Свадьба в Праге',
        'classes' => ['has-mega-menu'],
        'type'    => 'svadba_places',
        'location_slug' => 'svadba-v-prage',
        'columns' => 3,
    ],
    [
        'title'   => 'Свадьба в замке',
        'classes' => ['has-mega-menu'],
        'type'    => 'svadba_places',
        'location_slug' => 'svadba-v-zamke-chehii',
        'columns' => 3,
    ],
    [
        'title'   => 'Свадьба на корабле',
        'classes' => ['has-mega-menu', 'left-banner'],
        'type'    => 'svadba_places',
        'location_slug' => 'svadba-na-korable',
        'columns' => 1,
    ],
    [
        'title'   => 'Услуги',
        'classes' => ['has-mega-menu', 'left-banner'],
        'type'    => 'service',
        'columns' => 1,
    ],
    [
        'title'   => 'Цены',
        'url'     => home_url('/prajs-svadebnyh-uslug/'),
        'classes' => [],
        'type'    => 'link',
    ],
    [
        'title'   => 'Контакты',
        'url'     => home_url('/contacts/'),
        'classes' => [],
        'type'    => 'link',
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
        $classes = implode(' ', $item['classes'] ?? []);
        $location_slug = sanitize_title($item['location_slug'] ?? '');
        $columns = max(1, intval($item['columns'] ?? 2));

        $url = '#';
        if ($location_slug !== '') {
            $term = get_term_by('slug', $location_slug, 'location');
            if ($term instanceof WP_Term) {
                $term_link = get_term_link($term);
                if (!is_wp_error($term_link)) {
                    $url = $term_link;
                }
            }
        }

        echo '<li class="' . esc_attr($classes) . '">';
        echo '<a href="' . esc_url($url) . '">' . esc_html($title) . '</a>';
        echo '<div class="mega-menu"><div class="mega-menu-inner">';
        echo render_svadba_places_dropdown([
            'location_slug' => $location_slug,
            'columns' => $columns,
        ]);
        echo '</div></div>';
        echo '</li>';
    },

    // Выпадающее меню со списком услуг
    'service' => function (array $item) {
        $title   = $item['title'] ?? '';
        $classes = implode(' ', $item['classes'] ?? []);
        $columns = max(1, intval($item['columns'] ?? 1));

        $url = get_post_type_archive_link('service') ?: home_url('/service/');

        echo '<li class="' . esc_attr($classes) . '">';
        echo '<a href="' . esc_url($url) . '">' . esc_html($title) . '</a>';
        echo '<div class="mega-menu"><div class="mega-menu-inner">';
        echo render_service_dropdown([
            'columns' => $columns,
        ]);
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
    $fallback    = '';

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
                    $data['wedding_days'] = $wedding_days;
                }

                // JSON для data-info
                $data_attr = esc_attr(wp_json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));

                // Изображение для баннера
                $thumbnail_url = get_the_post_thumbnail_url($post_id, 'medium');
                if ($fallback === '' && !empty($thumbnail_url)) {
                    $fallback = $thumbnail_url;
                }
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

    <div class="right-mega-banner" <?php echo $fallback ? ' style="background-image:url(' . esc_url($fallback) . ')"' : ''; ?>>
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
    $fallback = '';

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
                if ($fallback === '' && !empty($thumbnail_url)) {
                    $fallback = $thumbnail_url;
                }
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

    <div class="right-mega-banner" <?php echo $fallback ? ' style="background-image:url(' . esc_url($fallback) . ')"' : ''; ?>>
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