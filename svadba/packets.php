<?php
/**
 * Svadba fixed packets grid
 * Shortcode: [svadba_packets]
 */
if (!defined('ABSPATH')) { exit; }
require_once get_template_directory() . '/svadba/common.php';

function svadba_packets_shortcode_handler($atts = array()) {
    global $wpdb;

    $post_id = get_the_ID();
    if (!$post_id) return '';

    $table = $wpdb->prefix . 'svadba_prices';

    // Find base auto price FIRST (cheapest auto across ALL rows, not filtered by packet)
    $base_auto_price = 0.0;
    $base_auto_row = $wpdb->get_row(
        "SELECT id, sname, sprice FROM {$table} WHERE pr_key = 'auto' ORDER BY sprice ASC, id ASC LIMIT 1",
        ARRAY_A
    );
    if ($base_auto_row) {
        $base_auto_price = (float)$base_auto_row['sprice'];
    }

    // Fetch all rows that are assigned to any packet
    $rows = $wpdb->get_results(
        "SELECT id, pr_key, sname, sprice, packet FROM {$table} WHERE packet IS NOT NULL AND packet <> '' ORDER BY id ASC",
        ARRAY_A
    );

    if (empty($rows)) return '';

    // Labels for first column (shared)
    $labels = svadba_get_labels();

    // Gather packets indices and group items by packet index
    $packets = array(); // index => list of [key, sname, sprice]

    // Build product rows list (first column names)
    $product_rows = array(); // name => ['name'=>..., 'key'=>..., 'order'=>...]
    
    // Get service order priorities
    $service_order = svadba_get_service_order();

    // Determine base values from post meta
    $distance = (int) get_post_meta($post_id, 'distance', true);
    if ($distance <= 0) $distance = 1;
    $base_place_price = (float) get_post_meta($post_id, 'fromnew', true);

    foreach ($rows as $r) {
        $key = $r['pr_key'];
        $sname = $r['sname'];
        $sprice = (float)$r['sprice'];
        $packetField = $r['packet'];
        if (!$packetField) continue;

        $packetIndices = array_map('intval', array_map('trim', explode(',', $packetField)));
        foreach ($packetIndices as $pidx) {
            if (!isset($packets[$pidx])) $packets[$pidx] = array();
            $packets[$pidx][] = array('key' => $key, 'sname' => $sname, 'sprice' => $sprice);
        }

        // Build first-column product row name
        if ($key === 'auto') {
            $name = $labels['auto'] . ': ' . $sname; // include auto model for clarity
        } elseif ($key === 'photo') {
            $name = $labels['photo'];
        } elseif ($key === 'video') {
            $name = $labels['video'];
        } else {
            $name = (isset($labels[$key]) ? $labels[$key] . ': ' : '') . $sname;
        }

        if (!isset($product_rows[$name])) {
            $order = isset($service_order[$key]) ? $service_order[$key] : 999;
            $product_rows[$name] = array(
                'name' => $name,
                'key'  => $key,
                'order' => $order,
            );
        }
    }

    if (empty($packets)) return '';

    ksort($packets); // order by index
    $maxPacket = max(array_keys($packets));

    // Names for packets (can be customized via filter)
    // Explicit mapping for known packets; extend here as needed.
    $default_names = array(
        1 => 'Super Best',
        2 => 'Exclusive',
        // 3 => 'Premium', // example: add more named packets here
    );
    // Backfill the rest with generic names
    for ($i = 1; $i <= $maxPacket; $i++) {
        if (!isset($default_names[$i])) {
            $default_names[$i] = 'Пакет ' . $i;
        }
    }
    $packet_names = apply_filters('svadba_packet_names', $default_names, $post_id);

    // Calculate packet prices
    $pack_prices = array(); // only services sum
    $pack_prices_total = array(); // base + discounted

    // Base auto deduction (align with new 0.7 coefficient)
    $base_auto_deduction = round(($base_auto_price * $distance * 0.7) / 10) * 10;

    foreach ($packets as $idx => $items) {
        $sum = 0.0;
        foreach ($items as $it) {
            switch ($it['key']) {
                case 'auto':
                    // Hours logic: if distance == 2, add per packet index + 1, else keep distance
                    $hours = ($distance == 2) ? ($distance + $idx + 1) : $distance;
                    $sum += max(0, ($it['sprice'] * $hours) - $base_auto_deduction);
                    break;
                case 'photo':
                case 'video':
                    // Try to extract hours from beginning of sname
                    $sentence = $it['sname'];
                    $spacePos = strpos($sentence, ' ');
                    $hours = $spacePos !== false ? (int) substr($sentence, 0, $spacePos) : 0;
                    $sum += $it['sprice'];
                    break;
                default:
                    $sum += $it['sprice'];
            }
        }
        $pack_prices[$idx] = $sum;
        $pack_prices_total[$idx] = (int)$base_place_price + (int)(round(($sum * 0.8) / 10) * 10); // 20% discount
    }

    // Build grid HTML
    ob_start();
    echo '<div class="packets-grid" data-maxpacket="' . esc_attr($maxPacket) . '">';

    // Header row
    echo '<div class="packets-cell header">Услуга</div>';
    foreach ($packets as $idx => $_) {
        $pname = isset($packet_names[$idx]) ? $packet_names[$idx] : ('Пакет ' . $idx);
        $total = isset($pack_prices_total[$idx]) ? $pack_prices_total[$idx] : 0;
        echo '<div class="packets-cell header"><div class="packet-name" id="name-packet-' . esc_attr($idx) . '">' . esc_html($pname) . '</div>'
           . '<div class="packet-price"><span class="price-value packet-price" id="price-packet-' . esc_attr($idx) . '" data-init-price="' . esc_attr($total) . '">' . esc_html($total) . '</span> <span>€</span></div></div>';
    }

    // Sort product rows by order priority
    usort($product_rows, function($a, $b) {
        return $a['order'] <=> $b['order'];
    });

    // Body rows (order by service priority from svadba_get_service_order)
    // Show prices when ?show_packet_prices=1 in URL
    $show_prices = isset($_GET['show_packet_prices']) && $_GET['show_packet_prices'] == '1';
    
    foreach ($product_rows as $prod) {
        $prodName = $prod['name'];
        echo '<div class="packets-cell first-col">' . esc_html($prodName) . '</div>';
        foreach ($packets as $idx => $items) {
            $display = '✗';
            $cellPrice = 0;
            foreach ($items as $it) {
                $key = $it['key'];
                $label = isset($labels[$key]) ? $labels[$key] : '';
                // Match auto rows by full name including model
                if ($key === 'auto' && $prodName === ($labels['auto'] . ': ' . $it['sname'])) {
                    $hours = ($distance == 2) ? ($distance + $idx + 1) : $distance;
                    $display = $hours . ' ч.';
                    $cellPrice = max(0, ($it['sprice'] * $hours) - $base_auto_deduction);
                    break;
                }
                if ($key === 'photo' && $prodName === $labels['photo']) {
                    $sentence = $it['sname'];
                    $spacePos = strpos($sentence, ' ');
                    $hours = $spacePos !== false ? (int) substr($sentence, 0, $spacePos) : 0;
                    $display = ($hours > 0 ? $hours . ' ч.' : '✓');
                    $cellPrice = (float)$it['sprice'];
                    break;
                }
                if ($key === 'video' && $prodName === $labels['video']) {
                    $sentence = $it['sname'];
                    $spacePos = strpos($sentence, ' ');
                    $hours = $spacePos !== false ? (int) substr($sentence, 0, $spacePos) : 0;
                    $display = ($hours > 0 ? $hours . ' ч.' : '✓');
                    $cellPrice = (float)$it['sprice'];
                    break;
                }
                $full = ($key === 'auto') ? ($labels['auto'] . ': ' . $it['sname']) : ((isset($labels[$key]) ? $labels[$key] . ': ' : '') . $it['sname']);
                if ($full === $prodName) { $display = '✓'; $cellPrice = (float)$it['sprice']; break; }
            }
            
            // Add price if requested via URL parameter
            $cellText = $display;
            if ($show_prices && $cellPrice > 0) {
                $cellText .= ' (' . (int)$cellPrice . ' €)';
            }
            
            echo '<div class="packets-cell">' . esc_html($cellText) . '</div>';
        }
    }

    // Footer row with order buttons (non-functional placeholder)
    echo '<div class="packets-cell footer text-accent">Место принятия решения</div>';
    foreach ($packets as $idx => $_) {
        echo '<div class="packets-cell footer"><button type="button" class="packet-order btn" data-formid="packet-' . esc_attr($idx) . '">Заказать</button></div>';
    }

    echo '</div>'; // .packets-grid
    echo '<div id="packet-message-form"></div>';

    return ob_get_clean();
}
add_shortcode('svadba_packets', 'svadba_packets_shortcode_handler');
