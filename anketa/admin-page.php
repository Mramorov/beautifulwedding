<?php
if (!defined('ABSPATH')) exit;

require_once get_template_directory() . '/anketa/common.php';

// =============================================================================
// Список органов (мест регистрации). Редактировать здесь.
// =============================================================================
define('ANKETA_ORGANS', [
    'P1', 'P2', 'P4', 'P6', 'P7', 'P8', 'P19', 'P22',
    'Jinocany', 'Clam-Gal', 'Pruhonice', 'Brandys', 'Sychrov',
    'Dobrys', 'Konopiste', 'Hluboka', 'Mnichovice', 'Orlik',
    'Blatna', 'Liberec', 'Lysa',
]);
// =============================================================================

// Миграции
add_action('admin_init', function() {
    global $wpdb;
    $table = $wpdb->prefix . 'anketa';

    $exists = $wpdb->get_var("SHOW COLUMNS FROM `$table` LIKE 'svadba_date_time'");
    if (!$exists) {
        $wpdb->query("ALTER TABLE `$table` ADD COLUMN `svadba_date_time` DATETIME NULL DEFAULT NULL");
    }

    $exists = $wpdb->get_var("SHOW COLUMNS FROM `$table` LIKE 'organ'");
    if (!$exists) {
        $wpdb->query("ALTER TABLE `$table` ADD COLUMN `organ` VARCHAR(64) NULL DEFAULT NULL");
    }
});

add_action('admin_menu', function() {
    add_menu_page(
        'Анкеты',
        'Анкеты',
        'manage_options',
        'anketa-list',
        'anketa_admin_render_list',
        'dashicons-clipboard',
        30
    );
});

function anketa_time_options(): string {
    $out = '';
    for ($h = 9; $h <= 15; $h++) {
        foreach (['00', '30'] as $m) {
            $val  = sprintf('%02d:%s', $h, $m);
            $out .= '<option value="' . $val . '">';
        }
    }
    return $out;
}

// ---------------------------------------------------------------------------
// Формирование имени папки OneDrive из записи анкеты
// ---------------------------------------------------------------------------
function anketa_onedrive_folder_name(array $row): string {
    return date('d.m', strtotime($row['svadba_date_time']))
         . '-'
         . date('H.i', strtotime($row['svadba_date_time']))
         . '-'
         . $row['organ'];
}

function anketa_admin_render_list() {
    global $wpdb;
    $table = $wpdb->prefix . 'anketa';

    // ---- Уведомления после OAuth-редиректа ----
    $onedrive_notice = '';
    if (!empty($_GET['od_connected'])) {
        $onedrive_notice = '<div class="notice notice-success is-dismissible"><p>OneDrive подключён ✓</p></div>';
    } elseif (!empty($_GET['od_error'])) {
        $onedrive_notice = '<div class="notice notice-error is-dismissible"><p>OneDrive: ' . esc_html(urldecode($_GET['od_error'])) . '</p></div>';
    }

    // ---- Экспорт в OneDrive ----
    if (
        isset($_POST['anketa_export_onedrive']) &&
        check_admin_referer('anketa_export_onedrive')
    ) {
        $hash = sanitize_text_field($_POST['anketa_hash'] ?? '');
        $row  = $hash
            ? $wpdb->get_row($wpdb->prepare("SELECT * FROM $table WHERE hash = %s", $hash), ARRAY_A)
            : null;

        if (!$row) {
            $onedrive_notice = '<div class="notice notice-error"><p>Анкета не найдена.</p></div>';
        } elseif (empty($row['svadba_date_time']) || empty($row['organ'])) {
            $onedrive_notice = '<div class="notice notice-error"><p>Укажите дату, время и орган перед отправкой в OneDrive.</p></div>';
        } else {
            $folder_name = anketa_onedrive_folder_name($row);
            $export_error = null;

            // Генерируем PDF
            try {
                anketa_generate_pdf($hash);
            } catch (\Exception $e) {
                $export_error = 'PDF: ' . $e->getMessage();
            }

            if (!$export_error) {
                $docs_dir = anketa_docs_dir($hash);
                $files    = glob($docs_dir . '/*');

                foreach ($files as $file_path) {
                    if (!is_file($file_path) || basename($file_path) === 'mpdf_tmp') continue;
                    $remote = ONEDRIVE_ROOT_FOLDER . '/' . $folder_name . '/' . basename($file_path);
                    $result = anketa_onedrive_upload_file($remote, $file_path);
                    if (is_wp_error($result)) {
                        $export_error = $result->get_error_message();
                        break;
                    }
                }
            }

            if ($export_error) {
                $onedrive_notice = '<div class="notice notice-error"><p>Ошибка OneDrive: ' . esc_html($export_error) . '</p></div>';
            } else {
                $onedrive_notice = '<div class="notice notice-success"><p>Файлы отправлены в OneDrive: <strong>' . esc_html(ONEDRIVE_ROOT_FOLDER . '/' . $folder_name) . '</strong></p></div>';
            }
        }
    }

    // ---- Сохранение дат/органов ----
    if (
        isset($_POST['anketa_save_dates']) &&
        check_admin_referer('anketa_save_dates')
    ) {
        $rows_post = isset($_POST['rows']) && is_array($_POST['rows']) ? $_POST['rows'] : [];
        foreach ($rows_post as $id => $fields) {
            $id = (int) $id;
            if (!$id) continue;

            $date  = isset($fields['svadba_date']) ? sanitize_text_field($fields['svadba_date']) : '';
            $time  = isset($fields['svadba_time']) ? sanitize_text_field($fields['svadba_time']) : '';
            $dt    = ($date && $time) ? date('Y-m-d H:i:s', strtotime("$date $time")) : null;
            $organ = isset($fields['organ']) ? sanitize_text_field($fields['organ']) : null;

            $wpdb->update(
                $table,
                ['svadba_date_time' => $dt, 'organ' => $organ],
                ['id' => $id],
                ['%s', '%s'],
                ['%d']
            );
        }
        echo '<div class="notice notice-success is-dismissible"><p>Данные сохранены.</p></div>';
    }

    $rows = $wpdb->get_results(
        "SELECT id, hash, groom_full_name, bride_full_name, contact_email, contact_tel, svadba_date_time, organ, created_at
         FROM $table
         ORDER BY created_at DESC",
        ARRAY_A
    );

    // Статус подключения OneDrive
    $od_configured = defined('ONEDRIVE_CLIENT_ID') && ONEDRIVE_CLIENT_ID !== '';
    $od_tokens     = get_option('anketa_onedrive_tokens');
    $od_connected  = !empty($od_tokens['refresh_token']);
    ?>
    <div class="wrap">
        <h1>Анкеты</h1>

        <?php echo $onedrive_notice; ?>

        <?php if ($od_configured): ?>
        <p>
            <?php if ($od_connected): ?>
                <span style="color:#007700;">&#10003; OneDrive подключён</span>
                &nbsp;
                <a href="<?php echo esc_url(anketa_onedrive_get_auth_url()); ?>" class="button button-small">Переподключить</a>
            <?php else: ?>
                <a href="<?php echo esc_url(anketa_onedrive_get_auth_url()); ?>" class="button button-primary">Подключить OneDrive</a>
            <?php endif; ?>
        </p>
        <?php endif; ?>

        <?php if (empty($rows)): ?>
            <p>Анкет пока нет.</p>
        <?php else: ?>

        <datalist id="svadba-time-options">
            <?php echo anketa_time_options(); ?>
        </datalist>

        <form method="post">
            <?php wp_nonce_field('anketa_save_dates'); ?>

            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th style="width:36px">#</th>
                        <th>Жених</th>
                        <th>Невеста</th>
                        <th>Email</th>
                        <th>Телефон</th>
                        <th style="width:240px">Дата и время свадьбы</th>
                        <th style="width:130px">Орган</th>
                        <th>Документы</th>
                        <th style="width:120px">Действия</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $export_forms = '';
                    foreach ($rows as $row):
                        $id         = (int) $row['id'];
                        $date_val   = $row['svadba_date_time'] ? date('Y-m-d', strtotime($row['svadba_date_time'])) : '';
                        $time_val   = $row['svadba_date_time'] ? date('H:i',   strtotime($row['svadba_date_time'])) : '';
                        $organ_val  = $row['organ'] ?? '';
                        $docs       = array_keys(anketa_get_existing_files($row['hash']));
                        $edit_url   = anketa_get_edit_url($row['hash']);
                        $edit_href  = is_wp_error($edit_url) ? '' : esc_url($edit_url);
                        $can_export = $od_configured && $od_connected && !empty($row['svadba_date_time']) && !empty($row['organ']);
                        $export_form_id = 'export-form-' . $id;

                        // Собираем export-формы отдельно — вне основной <form>
                        if ($can_export) {
                            ob_start(); ?>
                            <form id="<?php echo $export_form_id; ?>" method="post" style="display:none;">
                                <?php wp_nonce_field('anketa_export_onedrive'); ?>
                                <input type="hidden" name="anketa_hash" value="<?php echo esc_attr($row['hash']); ?>">
                                <input type="hidden" name="anketa_export_onedrive" value="1">
                            </form>
                            <?php $export_forms .= ob_get_clean();
                        }
                    ?>
                    <tr>
                        <td><?php echo $id; ?></td>
                        <td><?php echo esc_html($row['groom_full_name'] ?: '—'); ?></td>
                        <td><?php echo esc_html($row['bride_full_name']  ?: '—'); ?></td>
                        <td><?php echo esc_html($row['contact_email']    ?: '—'); ?></td>
                        <td><?php echo esc_html($row['contact_tel']      ?: '—'); ?></td>
                        <td>
                            <div style="display:flex; gap:6px; align-items:center;">
                                <input
                                    type="date"
                                    name="rows[<?php echo $id; ?>][svadba_date]"
                                    value="<?php echo esc_attr($date_val); ?>"
                                    style="flex:1; min-width:0;"
                                >
                                <input
                                    type="time"
                                    name="rows[<?php echo $id; ?>][svadba_time]"
                                    value="<?php echo esc_attr($time_val); ?>"
                                    list="svadba-time-options"
                                    style="width:90px;"
                                >
                            </div>
                        </td>
                        <td>
                            <select name="rows[<?php echo $id; ?>][organ]" style="width:100%">
                                <option value="">—</option>
                                <?php foreach (ANKETA_ORGANS as $o): ?>
                                    <option value="<?php echo esc_attr($o); ?>" <?php selected($organ_val, $o); ?>>
                                        <?php echo esc_html($o); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </td>
                        <td><?php echo esc_html($docs ? implode(', ', $docs) : '—'); ?></td>
                        <td>
                            <div style="display:flex; flex-direction:column; gap:4px;">
                                <?php if ($edit_href): ?>
                                    <a href="<?php echo $edit_href; ?>" target="_blank" class="button button-small">Анкета →</a>
                                <?php endif; ?>
                                <?php if ($can_export): ?>
                                    <button type="button" class="button button-small"
                                            title="<?php echo esc_attr(anketa_onedrive_folder_name($row)); ?>"
                                            onclick="document.getElementById('<?php echo $export_form_id; ?>').submit()">
                                        &#x2192; OneDrive
                                    </button>
                                <?php elseif ($od_configured && $od_connected): ?>
                                    <span style="color:#aaa; font-size:11px;" title="Укажите дату и орган">&#x2192; OneDrive</span>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <p>
                <button type="submit" name="anketa_save_dates" class="button button-primary">
                    Сохранить
                </button>
            </p>
        </form>

        <?php echo $export_forms; ?>

        <?php endif; ?>
    </div>
    <?php
}
