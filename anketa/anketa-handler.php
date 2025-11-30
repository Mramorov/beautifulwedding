<?php
if (!defined('ABSPATH')) exit;

// Ensure labels helper is available
require_once get_template_directory() . '/anketa/common.php';

/**
 * Построить тело письма на основе сохранённых данных анкеты.
 * Выводит только непустые поля.
 */
if (!function_exists('anketa_build_email_body')) {
    function anketa_build_email_body(array $data, array $labels, $edit_url, $is_update, $hash) {
        $lines = [];
        $lines[] = $is_update ? 'Анкета ОБНОВЛЕНА' : 'Создана НОВАЯ анкета';
        $lines[] = 'Hash: ' . $hash;
        $lines[] = 'Ссылка для редактирования: ' . $edit_url;
        $lines[] = str_repeat('-', 60);
        foreach ($labels as $key => $label) {
            if (!empty($data[$key])) {
                $lines[] = $label . ': ' . $data[$key];
            }
        }
        $lines[] = str_repeat('-', 60);
        $lines[] = 'Отправлено: ' . wp_date('Y-m-d H:i:s');
        return implode("\n", $lines);
    }
}

// HTML версия тела письма для администратора (таблица 2 колонки)
if (!function_exists('anketa_build_email_body_html')) {
    function anketa_build_email_body_html(array $data, array $labels, $edit_url, $is_update, $hash) {
        $title = $is_update ? 'Анкета ОБНОВЛЕНА' : 'Создана НОВАЯ анкета';
        $out  = '<html><body style="font-family:Arial,sans-serif; font-size:14px; color:#333;">';
        $out .= '<h2 style="margin:0 0 12px;">' . esc_html($title) . '</h2>';
        $out .= '<p style="margin:0 0 12px;">Hash: <strong>' . esc_html($hash) . '</strong><br>';
        $out .= 'Ссылка для редактирования: <a href="' . esc_url($edit_url) . '">' . esc_html($edit_url) . '</a></p>';
        $out .= '<table cellpadding="6" cellspacing="0" style="border-collapse:collapse; width:100%; max-width:900px; background:#fff;">';
        $out .= '<thead><tr style="background:#f5f5f5; border-bottom:1px solid #ddd;">'
              . '<th align="left" style="text-align:left; font-weight:600; border:1px solid #ddd;">Поле</th>'
              . '<th align="left" style="text-align:left; font-weight:600; border:1px solid #ddd;">Значение</th></tr></thead><tbody>';
        foreach ($labels as $key => $label) {
            $val = isset($data[$key]) ? trim($data[$key]) : '';
            $is_empty = ($val === '');
            $row_style = $is_empty ? 'background:#fff6f6;' : '';
            $value_html = $is_empty
                ? '<span style="color:#b30000;">Не заполнено!</span>'
                : nl2br(esc_html($val));
            $out .= '<tr style="' . $row_style . '">' .
                '<td style="vertical-align:top; border:1px solid #eee; width:40%; background:#fafafa;">' . esc_html($label) . '</td>' .
                '<td style="vertical-align:top; border:1px solid #eee;">' . $value_html . '</td>' .
            '</tr>';
        }
        $out .= '</tbody></table>';
        $out .= '<p style="margin-top:16px; font-size:12px; color:#666;">Отправлено: ' . esc_html(wp_date('Y-m-d H:i:s')) . '</p>';
        $out .= '</body></html>';
        return $out;
    }
}

/**
 * Получить URL страницы анкеты с добавленным hash.
 * Ищет страницу по назначенному шаблону 'anketa/page-form.php'.
 * Если не найдена – возвращает home_url() с параметром.
 */
if (!function_exists('anketa_get_edit_url')) {
    function anketa_get_edit_url($hash) {
        $edit_base = '';
        // Поиск страницы с шаблоном
        $pages = get_pages(array(
            'meta_key' => '_wp_page_template',
            'meta_value' => 'anketa/page-form.php',
            'number' => 1,
        ));
        if (!empty($pages)) {
            $edit_base = get_permalink($pages[0]->ID);
        } else {
            // fallback: попробуем найти по слагу (если автор решит назвать страницу 'anketa')
            $maybe = get_page_by_path('anketa');
            if ($maybe) {
                $edit_base = get_permalink($maybe->ID);
            }
        }
        if (empty($edit_base)) {
            $edit_base = home_url('/');
        }
        return add_query_arg('hash', $hash, $edit_base);
    }
}

// Регистрация REST API endpoints
add_action('rest_api_init', function() {
    // POST: Сохранение/обновление анкеты
    register_rest_route('anketa/v1', '/submit', array(
        'methods' => 'POST',
        'callback' => 'anketa_handle_form_submit',
        'permission_callback' => '__return_true', // Для теста, потом добавить nonce
    ));
    
    // GET: Получение данных анкеты по хэшу
    register_rest_route('anketa/v1', '/get', array(
        'methods' => 'GET',
        'callback' => 'anketa_get_by_hash',
        'permission_callback' => '__return_true',
        'args' => array(
            'hash' => array(
                'required' => true,
                'type' => 'string',
                'sanitize_callback' => 'sanitize_text_field',
            ),
        ),
    ));
});

function anketa_handle_form_submit(WP_REST_Request $request) {
    global $wpdb;
    $table = $wpdb->prefix . 'anketa';
    $data = $request->get_json_params();

    // Получаем список полей из словаря лейблов, чтобы не дублировать массив
    $fields = array_keys(anketa_get_labels());

    // Валидация контактных данных: хотя бы одно поле должно быть заполнено
    $contact_email = isset($data['contact_email']) ? trim(sanitize_email($data['contact_email'])) : '';
    $contact_tel = isset($data['contact_tel']) ? trim(sanitize_text_field($data['contact_tel'])) : '';
    
    if (empty($contact_email) && empty($contact_tel)) {
        return new WP_REST_Response([
            'success' => false,
            'message' => 'Укажите хотя бы один способ связи: email или телефон'
        ], 400);
    }

    // Проверка хэша
    $hash = isset($data['hash']) ? sanitize_text_field($data['hash']) : '';
    $is_update = false;
    $row_id = 0;

    if ($hash) {
        $row = $wpdb->get_row($wpdb->prepare("SELECT id FROM $table WHERE hash = %s", $hash));
        if ($row) {
            $is_update = true;
            $row_id = $row->id;
        }
    }

    // Подготовка данных
    $save = array();
    foreach ($fields as $field) {
        if ($field === 'contact_email') {
            $save[$field] = isset($data[$field]) ? sanitize_email($data[$field]) : '';
        } else {
            $save[$field] = isset($data[$field]) ? sanitize_text_field($data[$field]) : '';
        }
    }

    if ($is_update) {
        $wpdb->update($table, $save, array('id' => $row_id));
        $result_hash = $hash;
    } else {
        // Генерация уникального хэша
        do {
            $result_hash = wp_generate_password(32, false, false);
            $exists = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM $table WHERE hash = %s", $result_hash));
        } while ($exists);
        $save['hash'] = $result_hash;
        $wpdb->insert($table, $save);
        $row_id = $wpdb->insert_id;
    }

    // Ссылка для редактирования (корректный URL страницы анкеты, не REST маршрут)
    $edit_url = anketa_get_edit_url($result_hash);

    // =============================
    // Отправка писем
    // =============================
    $labels = anketa_get_labels();
    $admin_email = get_option('admin_email');
    $subject_admin = ($is_update ? '[Анкета обновлена]' : '[Новая анкета]') . ' hash=' . $result_hash;
    // HTML таблица для администратора
    $body_admin = anketa_build_email_body_html($save, $labels, $edit_url, $is_update, $result_hash);
    $headers_admin = [ 'Content-Type: text/html; charset=UTF-8' ];
    $mail_admin_sent = false;
    if ($admin_email) {
        $mail_admin_sent = wp_mail($admin_email, $subject_admin, $body_admin, $headers_admin);
    }

    // Письмо клиенту (только если указан email)
    $mail_client_sent = false;
    $client_email = isset($save['contact_email']) ? trim($save['contact_email']) : '';
    if (!empty($client_email)) {
        $subject_client = $is_update ? 'Анкета обновлена — ссылка для редактирования' : 'Анкета сохранена — ссылка для редактирования';
        $body_client_lines = [];
        $body_client_lines[] = $is_update ? 'Ваши данные обновлены.' : 'Ваша анкета сохранена.';
        $body_client_lines[] = 'Ссылка для редактирования: ' . $edit_url;
        $body_client_lines[] = '';
        $body_client_lines[] = 'Сохраните эту ссылку. НЕ передавайте её третьим лицам.';
        $body_client_lines[] = '';
        $body_client_lines[] = 'Если вы не заполняли анкету или получили письмо по ошибке — просто проигнорируйте.';

        // Памятка: какие поля ещё не заполнены
        $labels = anketa_get_labels();
        // Секции: ключ => [ключи]
        $sections = [
            'Жених' => [
                'groom_full_name', 'groom_birth_surname', 'groom_passport', 'groom_birthdate', 'groom_birthplace', 'groom_citizenship', 'groom_marital_status', 'groom_address', 'groom_education'
            ],
            'Родители жениха — отец' => [
                'groom_father_name', 'groom_father_birth_surname', 'groom_father_birthdate', 'groom_father_birthplace'
            ],
            'Родители жениха — мать' => [
                'groom_mother_name', 'groom_mother_birth_surname', 'groom_mother_birthdate', 'groom_mother_birthplace'
            ],
            'Невеста' => [
                'bride_full_name', 'bride_birth_surname', 'bride_passport', 'bride_birthdate', 'bride_birthplace', 'bride_citizenship', 'bride_marital_status', 'bride_address', 'bride_education'
            ],
            'Родители невесты — отец' => [
                'bride_father_name', 'bride_father_birth_surname', 'bride_father_birthdate', 'bride_father_birthplace'
            ],
            'Родители невесты — мать' => [
                'bride_mother_name', 'bride_mother_birth_surname', 'bride_mother_birthdate', 'bride_mother_birthplace'
            ],
            'Договоренности' => [
                'surname_choice', 'wedding_location', 'translation_language', 'certificate_address'
            ]
        ];
        $missing_by_section = [];
        foreach ($sections as $section => $keys) {
            $missing = [];
            foreach ($keys as $key) {
                if (empty($save[$key])) {
                    $missing[] = $labels[$key];
                }
            }
            if (!empty($missing)) {
                $missing_by_section[$section] = $missing;
            }
        }
        if (!empty($missing_by_section)) {
            $body_client_lines[] = '';
            $body_client_lines[] = 'Памятка: для завершения анкеты подготовьте данные по следующим пунктам:';
            foreach ($missing_by_section as $section => $fields) {
                $body_client_lines[] = $section . ':';
                foreach ($fields as $label) {
                    $body_client_lines[] = '— ' . $label;
                }
            }
        }

        $body_client = implode("\n", $body_client_lines);
        $headers_client = [ 'Content-Type: text/plain; charset=UTF-8' ];
        $mail_client_sent = wp_mail($client_email, $subject_client, $body_client, $headers_client);
    }

    return new WP_REST_Response([
        'success' => true,
        'editUrl' => $edit_url,
        'hash' => $result_hash,
        'is_update' => $is_update,
        'id' => $row_id,
        'mail_admin_sent' => (bool) $mail_admin_sent,
        'mail_client_sent' => (bool) $mail_client_sent
    ], 200);
}

/**
 * Получить данные анкеты по хэшу
 */
function anketa_get_by_hash(WP_REST_Request $request) {
    global $wpdb;
    $table = $wpdb->prefix . 'anketa';
    $hash = $request->get_param('hash');
    
    $row = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table WHERE hash = %s", $hash), ARRAY_A);
    
    if (!$row) {
        return new WP_REST_Response([
            'success' => false,
            'message' => 'Анкета не найдена'
        ], 404);
    }
    
    // Удалить служебные поля из ответа
    unset($row['id'], $row['created_at'], $row['updated_at']);
    
    return new WP_REST_Response([
        'success' => true,
        'data' => $row
    ], 200);
}
