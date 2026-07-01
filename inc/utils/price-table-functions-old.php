<?php

// Функция получения данных из БД
function get_prices_by_keys($keys) {
	global $wpdb;
	$keys_placeholders = implode(',', array_fill(0, count($keys), '%s'));
	$query = $wpdb->prepare(
		"SELECT sname, sdetail, sprice, pr_key FROM {$wpdb->prefix}svadba_prices WHERE pr_key IN ($keys_placeholders) ORDER BY id",
		$keys
	);
	return $wpdb->get_results($query);
}

// Функция форматирования цены
function format_price($price) {
	if ($price == -999) {
		return 'договорная';
	}
	return number_format($price, 0, ',', ' ') . ' €';
}

// Функция форматирования названия услуги
function format_service_name($item) {
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

// Функция отрисовки таблицы
function render_price_table($section) {
	if (empty($section['keys'])) {
		return '';
	}
	$items = get_prices_by_keys($section['keys']);
	if (empty($items)) {
		return '';
	}
	$output = '<div class="price-section">';
	if (!empty($section['title'])) {
		$output .= '<h3>' . esc_html($section['title']) . '</h3>';
	}
	$output .= '<div class="price-table">';
	$output .= '<div class="price-table-header">';
	$output .= '<div class="price-cell price-cell-name">Название услуги</div>';
	$output .= '<div class="price-cell price-cell-price">Цена</div>';
	$output .= '</div>';
	foreach ($items as $item) {
		$output .= '<div class="price-table-row">';
		$output .= '<div class="price-cell price-cell-name">' . esc_html(format_service_name($item)) . '</div>';
		$output .= '<div class="price-cell price-cell-price">' . format_price($item->sprice) . '</div>';
		$output .= '</div>';
	}
	$output .= '</div></div>';
	return $output;
}

// Функция расчета цен по пакетам для мест свадеб
function render_wedding_places_table() {
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
	$auto_rows = $wpdb->get_results(
		"SELECT sprice, packet FROM {$table} WHERE pr_key = 'auto' AND packet IS NOT NULL AND packet <> ''",
		ARRAY_A
	);
	foreach ($auto_rows as $row) {
		$packet_indices = array_map('trim', explode(',', $row['packet']));
		foreach ($packet_indices as $idx) {
			if (!isset($auto_prices[$idx])) $auto_prices[$idx] = 0;
			$auto_prices[$idx] += (float)$row['sprice'];
		}
	}
	$other_prices = array();
	$other_keys = array('photo', 'bqt', 'cake', 'phvid', 'other', 'dress', 'hair');
	$keys_placeholders = implode(',', array_fill(0, count($other_keys), '%s'));
	$other_rows = $wpdb->get_results(
		$wpdb->prepare(
			"SELECT sprice, packet, pr_key FROM {$table} WHERE pr_key IN ($keys_placeholders) AND packet IS NOT NULL AND packet <> ''",
			$other_keys
		),
		ARRAY_A
	);
	$packet_has_photovideo = array();
	foreach ($other_rows as $row) {
		$packet_indices = array_map('trim', explode(',', $row['packet']));
		foreach ($packet_indices as $idx) {
			if (!isset($other_prices[$idx])) $other_prices[$idx] = 0;
			$other_prices[$idx] += (float)$row['sprice'];
			if (!isset($packet_has_photovideo[$idx])) $packet_has_photovideo[$idx] = false;
			if ($row['pr_key'] === 'photo' || $row['pr_key'] === 'video') {
				$packet_has_photovideo[$idx] = true;
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
	$output = '<div class="wedding-places-grid">';
	$output .= '<div class="place-cell header">Место свадьбы</div>';
	$output .= '<div class="place-cell header">Базовая цена</div>';
	foreach ($packets as $packet_idx => $packet_data) {
		$output .= '<div class="place-cell header">' . esc_html($packet_data['name']) . '</div>';
	}
	foreach ($places as $place) {
		$distance = max(BW_MIN_DISTANCE, (int)get_post_meta($place->ID, 'distance', true));
		$base_place_price = (float)get_post_meta($place->ID, 'fromnew', true);
		$base_auto_minus = round(($base_auto_price * $distance * BW_AUTO_DEDUCTION_COEF) / BW_ROUND_STEP) * BW_ROUND_STEP;
		$output .= '<div class="place-cell place-name">';
		$output .= '<a href="' . get_permalink($place->ID) . '">' . esc_html($place->post_title) . '</a>';
		$output .= '</div>';
		$output .= '<div class="place-cell place-price">' . number_format($base_place_price, 0, ',', ' ') . ' €</div>';
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
			$output .= '<div class="place-cell packet-price">';
			$output .= number_format($total_price, 0, ',', ' ') . ' €</div>';
		}
	}
	$output .= '</div>';
	return $output;
}
