<?php
if (!defined('ABSPATH')) exit;

require_once get_template_directory() . '/anketa/common.php';

if (!function_exists('anketa_build_email_body_html')) {
    function anketa_build_email_body_html(array $data, array $labels, $edit_url, $is_update, $hash, array $existing_docs = []) {
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
            $val      = isset($data[$key]) ? trim($data[$key]) : '';
            $is_empty = ($val === '');
            $row_style  = $is_empty ? 'background:#fff6f6;' : '';
            $value_html = $is_empty
                ? '<span style="color:#b30000;">Не заполнено!</span>'
                : nl2br(esc_html($val));
            $out .= '<tr style="' . $row_style . '">'
                 . '<td style="vertical-align:top; border:1px solid #eee; width:40%; background:#fafafa;">' . esc_html($label) . '</td>'
                 . '<td style="vertical-align:top; border:1px solid #eee;">' . $value_html . '</td>'
                 . '</tr>';
        }
        $out .= '</tbody></table>';

        // Документы
        $file_fields = anketa_get_file_fields();
        $out .= '<h3 style="margin-top:24px;">Документы</h3>';
        $out .= '<table cellpadding="6" cellspacing="0" style="border-collapse:collapse; width:100%; max-width:900px; background:#fff;">';
        $out .= '<thead><tr style="background:#f5f5f5; border-bottom:1px solid #ddd;">'
              . '<th align="left" style="text-align:left; font-weight:600; border:1px solid #ddd;">Документ</th>'
              . '<th align="left" style="text-align:left; font-weight:600; border:1px solid #ddd;">Статус</th></tr></thead><tbody>';
        foreach ($file_fields as $field => $label) {
            $uploaded = isset($existing_docs[$field]);
            $val_html = $uploaded
                ? '<span style="color:#007700;">Загружен (' . esc_html($field . '.' . $existing_docs[$field]) . ')</span>'
                : '<span style="color:#b30000;">Не предоставлен</span>';
            $row_style = $uploaded ? '' : 'background:#fff6f6;';
            $out .= '<tr style="' . $row_style . '">'
                 . '<td style="vertical-align:top; border:1px solid #eee; width:40%; background:#fafafa;">' . esc_html($label) . '</td>'
                 . '<td style="vertical-align:top; border:1px solid #eee;">' . $val_html . '</td>'
                 . '</tr>';
        }
        $out .= '</tbody></table>';
        $out .= '<p style="margin-top:16px; font-size:12px; color:#666;">Отправлено: ' . esc_html(wp_date('Y-m-d H:i:s')) . '</p>';
        $out .= '</body></html>';
        return $out;
    }
}

if (!function_exists('anketa_get_edit_url')) {
    function anketa_get_edit_url($hash) {
        $pages = get_pages([
            'meta_key'   => '_wp_page_template',
            'meta_value' => 'anketa/page-form.php',
            'number'     => 1,
        ]);
        if (!empty($pages)) {
            return add_query_arg('hash', $hash, get_permalink($pages[0]->ID));
        }
        return new WP_Error('anketa_page_not_found', 'Страница анкеты не найдена', ['status' => 404]);
    }
}

add_action('rest_api_init', function() {
    register_rest_route('anketa/v1', '/submit', [
        'methods'             => 'POST',
        'callback'            => 'anketa_handle_form_submit',
        'permission_callback' => '__return_true',
    ]);

    register_rest_route('anketa/v1', '/get', [
        'methods'             => 'GET',
        'callback'            => 'anketa_get_by_hash',
        'permission_callback' => '__return_true',
        'args'                => [
            'hash' => [
                'required'          => true,
                'type'              => 'string',
                'sanitize_callback' => 'sanitize_text_field',
            ],
        ],
    ]);
});

function anketa_handle_form_submit(WP_REST_Request $request) {
    global $wpdb;
    $table = $wpdb->prefix . 'anketa';

    // Поддержка JSON и multipart/form-data
    $data = $request->get_json_params();
    if (empty($data)) {
        $data = $request->get_body_params();
    }

    $labels = anketa_get_labels();
    $fields = array_keys($labels);

    $contact_email = isset($data['contact_email']) ? trim(sanitize_email($data['contact_email'])) : '';
    $contact_tel   = isset($data['contact_tel'])   ? trim(sanitize_text_field($data['contact_tel'])) : '';

    if (empty($contact_email) && empty($contact_tel)) {
        return new WP_REST_Response([
            'success' => false,
            'message' => 'Укажите хотя бы один способ связи: email или телефон',
        ], 400);
    }

    $hash      = isset($data['hash']) ? sanitize_text_field($data['hash']) : '';
    $is_update = false;
    $row_id    = 0;

    if ($hash) {
        $row = $wpdb->get_row($wpdb->prepare("SELECT id FROM $table WHERE hash = %s", $hash));
        if ($row) {
            $is_update = true;
            $row_id    = $row->id;
        }
    }

    $save = [];
    foreach ($fields as $field) {
        $save[$field] = ($field === 'contact_email')
            ? (isset($data[$field]) ? sanitize_email($data[$field]) : '')
            : (isset($data[$field]) ? sanitize_text_field($data[$field]) : '');
    }

    if ($is_update) {
        $wpdb->update($table, $save, ['id' => $row_id]);
        $result_hash = $hash;
    } else {
        do {
            $result_hash = wp_generate_password(32, false, false);
            $exists = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM $table WHERE hash = %s", $result_hash));
        } while ($exists);
        $save['hash'] = $result_hash;
        $wpdb->insert($table, $save);
        $row_id = $wpdb->insert_id;
    }

    // =============================
    // Обработка файлов
    // =============================
    $uploaded_files = $request->get_file_params();
    $delete_files   = (isset($data['delete_files']) && is_array($data['delete_files'])) ? $data['delete_files'] : [];
    $file_fields    = anketa_get_file_fields();
    $docs_dir       = anketa_docs_dir($result_hash);
    $allowed_mime   = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'application/pdf' => 'pdf'];
    $finfo          = new finfo(FILEINFO_MIME_TYPE);

    foreach (array_keys($file_fields) as $field_name) {
        $upload_key  = 'file_' . $field_name;
        $has_new     = !empty($uploaded_files[$upload_key])
                    && $uploaded_files[$upload_key]['error'] === UPLOAD_ERR_OK;

        if ($has_new) {
            $tmp  = $uploaded_files[$upload_key]['tmp_name'];
            $mime = $finfo->file($tmp);

            if (!isset($allowed_mime[$mime])) continue;

            if (!is_dir($docs_dir)) {
                wp_mkdir_p($docs_dir);
            }

            $old = glob($docs_dir . '/' . $field_name . '.*');
            if ($old) array_map('unlink', $old);

            move_uploaded_file($tmp, $docs_dir . '/' . $field_name . '.' . $allowed_mime[$mime]);

        } elseif (isset($delete_files[$field_name]) && is_dir($docs_dir)) {
            $old = glob($docs_dir . '/' . $field_name . '.*');
            if ($old) array_map('unlink', $old);
        }
    }

    $existing_docs = anketa_get_existing_files($result_hash);

    // =============================
    // Письма
    // =============================
    $edit_url = anketa_get_edit_url($result_hash);
    $admin_email  = get_option('admin_email');
    $subject_admin = ($is_update ? '[Анкета обновлена]' : '[Новая анкета]') . ' hash=' . $result_hash;
    $body_admin    = anketa_build_email_body_html($save, $labels, $edit_url, $is_update, $result_hash, $existing_docs);
    $mail_admin_sent = false;
    if ($admin_email) {
        $mail_admin_sent = wp_mail($admin_email, $subject_admin, $body_admin, ['Content-Type: text/html; charset=UTF-8']);
    }

    $mail_client_sent = false;
    $client_email     = trim($save['contact_email'] ?? '');
    if (!empty($client_email)) {
        $subject_client = $is_update ? 'Анкета обновлена — ссылка для редактирования' : 'Анкета сохранена — ссылка для редактирования';
        $lines = [];
        $lines[] = $is_update ? 'Ваши данные обновлены.' : 'Ваша анкета сохранена.';
        $lines[] = 'Ссылка для редактирования: ' . $edit_url;
        $lines[] = '';
        $lines[] = 'Сохраните эту ссылку. Не передавайте её третьим лицам.';

        // Незаполненные текстовые поля
        $missing_by_section = [];
        foreach (anketa_get_sections() as $section => $fields) {
            if ($section === 'Контактные данные') continue;
            $missing = [];
            foreach ($fields as $key => $label) {
                if (empty($save[$key])) $missing[] = $label;
            }
            if ($missing) $missing_by_section[$section] = $missing;
        }
        if ($missing_by_section) {
            $lines[] = '';
            $lines[] = 'Памятка: для завершения анкеты подготовьте данные по следующим пунктам:';
            foreach ($missing_by_section as $section => $items) {
                $lines[] = $section . ':';
                foreach ($items as $lbl) $lines[] = '— ' . $lbl;
            }
        }

        // Статус документов
        $lines[] = '';
        $lines[] = 'Документы:';
        foreach ($file_fields as $field => $lbl) {
            $status   = isset($existing_docs[$field]) ? '✓ загружен' : '✗ не предоставлен';
            $lines[] = '— ' . $lbl . ': ' . $status;
        }

        $mail_client_sent = wp_mail($client_email, $subject_client, implode("\n", $lines), ['Content-Type: text/plain; charset=UTF-8']);
    }

    return new WP_REST_Response([
        'success'          => true,
        'editUrl'          => $edit_url,
        'hash'             => $result_hash,
        'is_update'        => $is_update,
        'id'               => $row_id,
        'mail_admin_sent'  => (bool) $mail_admin_sent,
        'mail_client_sent' => (bool) $mail_client_sent,
    ], 200);
}

function anketa_get_by_hash(WP_REST_Request $request) {
    global $wpdb;
    $table = $wpdb->prefix . 'anketa';
    $hash  = $request->get_param('hash');

    $row = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table WHERE hash = %s", $hash), ARRAY_A);

    if (!$row) {
        return new WP_REST_Response(['success' => false, 'message' => 'Анкета не найдена'], 404);
    }

    unset($row['id'], $row['created_at'], $row['updated_at']);

    return new WP_REST_Response(['success' => true, 'data' => $row], 200);
}
