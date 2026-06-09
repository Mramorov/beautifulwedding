<?php

/**
 * Template Name: Прайс-лист услуг
 * Template Post Type: page
 */

// Enqueue assets
function enqueue_price_page_assets()
{
    // Табличные стили (общие для шорткода и страницы)
    $tables_css = get_template_directory() . '/assets/css/price-tables.css';
    $tables_ver = file_exists($tables_css) ? filemtime($tables_css) : wp_get_theme()->get('Version');
    wp_enqueue_style('bw-price-tables', get_template_directory_uri() . '/assets/css/price-tables.css', array(), $tables_ver);

    // Стили специфичные для страницы (табы, сетка мест)
    $css_file = get_template_directory() . '/assets/css/price-page.css';
    $css_version = file_exists($css_file) ? filemtime($css_file) : wp_get_theme()->get('Version');
    wp_enqueue_style('price-page-styles', get_template_directory_uri() . '/assets/css/price-page.css', array('bw-price-tables'), $css_version);

    $js_file = get_template_directory() . '/assets/js/price-page.js';
    $js_version = file_exists($js_file) ? filemtime($js_file) : wp_get_theme()->get('Version');
    wp_enqueue_script('price-page-script', get_template_directory_uri() . '/assets/js/price-page.js', array(), $js_version, true);
}
add_action('wp_enqueue_scripts', 'enqueue_price_page_assets');

// Локальная конфигурация вкладок и секций для прайс-листа
$tabs_config = [
    'weddings' => [
        'tab' => 'Свадьбы в Чехии',
        'sections' => [], // отдельная логика генерируется в шаблоне
    ],
     'auto' => [
        'tab' => 'Авто',
        'sections' => [
            ['title' => 'Трансфер аэропорт-отель и трансферы по городу (в одну сторону)', 'keys' => 'trans'],
            ['title' => 'Транспорт в день свадьбы (минимально 2 часа, цена указана за 1 час)', 'keys' => 'auto'],
        ],
    ],   
    'photo-video' => [
        'tab' => 'Фото, видео',
        'sections' => [
            ['title' => 'Фотосъёмка свадебной церемонии, прогулки по Праге', 'keys' => 'photo'],
            ['title' => 'Видеосъёмка в день свадьбы', 'keys' => 'video'],
            ['title' => 'Дополнительные услуги', 'keys' => 'phvid'],
        ],
    ],

    'hair-makeup' => [
        'tab' => 'Причёски, макияж',
        'sections' => [
            ['title' => '', 'keys' => 'hair'],
        ],
    ],
    'flowers' => [
        'tab' => 'Цветы, торты',
        'sections' => [
            ['title' => 'Цветы', 'keys' => 'bqt'],
            ['title' => 'Торты', 'keys' => 'cake']
        ],
    ],
    'arches' => [
        'tab' => 'Арки',
        'sections' => [
            ['title' => '', 'keys' => 'arch'],
        ],
    ],
    'dresses' => [
        'tab' => 'Платья',
        'sections' => [
            ['title' => '', 'keys' => 'dress'],
        ],
    ],
    'other' => [
        'tab' => 'Прочие услуги',
        'sections' => [
            ['title' => 'Разные услуги', 'keys' => 'other'],
            ['title' => 'Почтовые услуги', 'keys' => 'post'],
        ],
    ],
];

// Функция получения данных из БД
function get_prices_by_keys($keys)
{
    global $wpdb;
    $keys_placeholders = implode(',', array_fill(0, count($keys), '%s'));
    $query = $wpdb->prepare(
        "SELECT sname, sdetail, sprice, pr_key FROM {$wpdb->prefix}svadba_prices WHERE pr_key IN ($keys_placeholders) ORDER BY id",
        $keys
    );
    return $wpdb->get_results($query);
}

// Функция форматирования цены
function format_price($price)
{
    if ($price == -999) {
        return 'договорная';
    }
    return number_format($price, 0, ',', ' ') . ' €';
}

// Функция форматирования названия услуги
function format_service_name($item)
{
    $name = $item->sname;
    $detail = $item->sdetail;

    // Специальная логика для фото и видео
    if ($item->pr_key === 'photo') {
        $formatted = 'Фотосъёмка – ' . $name;
        if ($detail) {
            $formatted .= ' ' . $detail;
        }
        return $formatted;
    }

    if ($item->pr_key === 'video') {
        $formatted = 'Видеосъёмка – ' . $name;
        if ($detail) {
            $formatted .= ' ' . $detail;
        }
        return $formatted;
    }

    // Для остальных категорий
    $formatted = $name;
    if ($detail) {
        $formatted .= ' ' . $detail;
    }
    return $formatted;
}

// Дубликат render_wedding_places_table:
// - вывод через HTML table
// - одна общая таблица, внутри которой локации разделены строками-заголовками
function render_wedding_places_table_by_location()
{
    global $wpdb;

    require_once get_template_directory() . '/inc/utils/svadba-common.php';

    $packets = svadba_get_packets();
    $table = $wpdb->prefix . 'svadba_prices';

    $base_auto_price_row = $wpdb->get_row(
        "SELECT MIN(sprice) as min_price FROM {$table} WHERE pr_key = 'auto'",
        ARRAY_A
    );
    $base_auto_price = $base_auto_price_row ? (float)$base_auto_price_row['min_price'] : 0;

    $auto_prices = array();
    $other_prices = array();
    $rows_with_packets = $wpdb->get_results(
        "SELECT sprice, packet, pr_key FROM {$table} WHERE packet IS NOT NULL AND packet <> ''",
        ARRAY_A
    );
    $packet_has_photovideo = array();
    foreach ($rows_with_packets as $row) {
        $row_price = (float)$row['sprice'];
        $packet_indices = array_filter(array_map('trim', explode(',', (string)$row['packet'])), 'strlen');
        foreach ($packet_indices as $idx) {
            if ($row['pr_key'] === 'auto') {
                if (!isset($auto_prices[$idx])) {
                    $auto_prices[$idx] = 0;
                }
                $auto_prices[$idx] += $row_price;
            } else {
                if (!isset($other_prices[$idx])) {
                    $other_prices[$idx] = 0;
                }
                $other_prices[$idx] += $row_price;

                if (!isset($packet_has_photovideo[$idx])) {
                    $packet_has_photovideo[$idx] = false;
                }
                if ($row['pr_key'] === 'photo' || $row['pr_key'] === 'video') {
                    $packet_has_photovideo[$idx] = true;
                }
            }
        }
    }

    $args = array(
        'post_type' => 'svadba',
        'post_status' => 'publish',
        'posts_per_page' => -1,
        'orderby' => 'title',
        'order' => 'ASC'
    );
    $places = get_posts($args);

    if (empty($places)) {
        return '<div class="price-placeholder"><p><em>Нет доступных мест для свадеб</em></p></div>';
    }

    $grouped_places = array();
    $taxonomy = 'location';

    foreach ($places as $place) {
        $terms = taxonomy_exists($taxonomy) ? get_the_terms($place->ID, $taxonomy) : false;

        if (is_wp_error($terms) || empty($terms)) {
            if (!isset($grouped_places['__no_location'])) {
                $grouped_places['__no_location'] = array(
                    'label' => 'Без локации',
                    'url' => '',
                    'places' => array(),
                );
            }
            $grouped_places['__no_location']['places'][] = $place;
            continue;
        }

        foreach ($terms as $term) {
            if (!isset($grouped_places[$term->term_id])) {
                $term_link = get_term_link($term);
                $grouped_places[$term->term_id] = array(
                    'label' => $term->name,
                    'url' => !is_wp_error($term_link) ? $term_link : '',
                    'places' => array(),
                );
            }
            $grouped_places[$term->term_id]['places'][] = $place;
        }
    }

    uasort($grouped_places, function ($a, $b) {
        return strcasecmp((string)$a['label'], (string)$b['label']);
    });

    $colspan = count($packets) + 2;
    $output = '<div class="wedding-places-table-wrap">';
    $output .= '<table class="wedding-places-table">';
    $output .= '<thead><tr>';
    $output .= '<th>Место свадьбы</th>';
    $output .= '<th>Базовая цена</th>';
    foreach ($packets as $packet_data) {
        $output .= '<th>' . esc_html($packet_data['name']) . '</th>';
    }
    $output .= '</tr></thead>';
    $output .= '<tbody>';

    foreach ($grouped_places as $group) {
        if (empty($group['places'])) {
            continue;
        }

        $output .= '<tr class="location-group-row">';
        if (!empty($group['url'])) {
            $output .= '<th colspan="' . esc_attr($colspan) . '"><a href="' . esc_url($group['url']) . '">' . esc_html($group['label']) . '</a></th>';
        } else {
            $output .= '<th colspan="' . esc_attr($colspan) . '">' . esc_html($group['label']) . '</th>';
        }
        $output .= '</tr>';

        foreach ($group['places'] as $place) {
            $distance = max(BW_MIN_DISTANCE, (int)get_post_meta($place->ID, 'distance', true));
            $base_place_price = (float)get_post_meta($place->ID, 'fromnew', true);

            $base_auto_minus = round(($base_auto_price * $distance * BW_AUTO_DEDUCTION_COEF) / BW_ROUND_STEP) * BW_ROUND_STEP;

            $output .= '<tr>';
            $output .= '<td class="place-name"><a href="' . esc_url(get_permalink($place->ID)) . '">' . esc_html($place->post_title) . '</a></td>';
            $output .= '<td class="place-price">' . number_format($base_place_price, 0, ',', ' ') . ' €</td>';

            foreach ($packets as $packet_idx => $packet_data) {
                if ($distance == 2) {
                    $sv_hours = $distance + $packet_idx + 1;
                } else {
                    $sv_hours = $distance;
                }

                $auto_price = isset($auto_prices[$packet_idx]) ? $auto_prices[$packet_idx] : 0;
                $other_price = isset($other_prices[$packet_idx]) ? $other_prices[$packet_idx] : 0;

                $pack_price = ($auto_price * $sv_hours - $base_auto_minus) + $other_price;
                if ($distance > BW_MIN_DISTANCE && !empty($packet_has_photovideo[$packet_idx])) {
                    $pack_price += ($distance - BW_MIN_DISTANCE) * BW_TRAVEL_RATE_PHOTO_VIDEO;
                }

                $total_price = $base_place_price + round(($pack_price * BW_PACKET_DISCOUNT_COEF) / BW_ROUND_STEP) * BW_ROUND_STEP;
                $output .= '<td class="packet-price">' . number_format($total_price, 0, ',', ' ') . ' €</td>';
            }

            $output .= '</tr>';
        }
    }

    $output .= '</tbody></table></div>';

    return $output;
}

get_header('service');

?>

<main id="post-<?php the_ID(); ?>" <?php post_class('layout'); ?>>

    <section class="price-header boxed">
        <h1><?php the_title(); ?></h1>
        <?php if (have_posts()) : while (have_posts()) : the_post(); ?>
                <div class="entry-content">
                    <?php the_content(); ?>
                </div>
        <?php endwhile;
        endif; ?>
    </section>

    <section class="price-tabs-section shrink-animation grow-animation boxed">
        <div class="svadba-tabs-nav">
            <?php
            $first = true;
            foreach ($tabs_config as $tab_key => $tab_data) :
            ?>
                <button type="button"
                    class="svadba-tab-button<?php echo $first ? ' active' : ''; ?>"
                    data-tab="<?php echo esc_attr($tab_key); ?>-tab">
                    <?php echo esc_html($tab_data['tab']); ?>
                </button>
            <?php
                $first = false;
            endforeach;
            ?>
        </div>

        <div class="svadba-tabs-content">
            <?php
            $first = true;
            foreach ($tabs_config as $tab_key => $tab_data) :
            ?>
                <div id="<?php echo esc_attr($tab_key); ?>-tab"
                    class="svadba-tab-pane<?php echo $first ? ' active' : ''; ?>">

                    <?php if ($tab_key === 'weddings') : ?>
                        <?php echo render_wedding_places_table_by_location(); ?>
                    <?php else : ?>
                        <?php foreach ($tab_data['sections'] as $section) : ?>
                            <?php
                            // Render via shortcode for reuse across pages
                            $keys_attr = esc_attr($section['keys']);
                            $title_attr = esc_attr($section['title']);
                            echo do_shortcode('[bw_services keys="' . $keys_attr . '" title="' . $title_attr . '"]');
                            ?>
                        <?php endforeach; ?>
                    <?php endif; ?>

                </div>
            <?php
                $first = false;
            endforeach;
            ?>
        </div>
    </section>

</main>

<?php get_footer(); ?>