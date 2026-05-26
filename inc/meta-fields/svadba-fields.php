<?php

/**
 * Meta fields for svadba CPT: Pricing, Details, Characteristics, Context Image
 */
if (!defined('ABSPATH')) {
    exit;
}

// --- Pricing & Details ---
function svadba_add_price_fields_metabox()
{
    add_meta_box(
        'svadba_price_fields',
        'Pricing & Details',
        'svadba_price_fields_callback',
        'svadba',
        'normal',
        'high'
    );
}
add_action('add_meta_boxes', 'svadba_add_price_fields_metabox');

function svadba_price_fields_callback($post)
{
    wp_nonce_field('svadba_price_fields_nonce', 'svadba_price_fields_nonce');
    $fromold = get_post_meta($post->ID, 'fromold', true);
    $fromnew = get_post_meta($post->ID, 'fromnew', true);
    $capacity = get_post_meta($post->ID, 'capacity', true);
    $distance = get_post_meta($post->ID, 'distance', true);
    $cer_time = get_post_meta($post->ID, 'cer-time', true);
    $google_map_url = get_post_meta($post->ID, 'google_map_url', true);
?>
    <style>
        .svadba-fields-row {
            display: flex;
            gap: 15px;
            margin-bottom: 15px;
        }

        .svadba-field {
            flex: 1;
        }

        .svadba-field label {
            display: block;
            font-weight: 600;
            margin-bottom: 5px;
        }

        .svadba-field input {
            width: 100%;
        }
    </style>
    <div class="svadba-fields-row">
        <div class="svadba-field">
            <label for="fromold">Old price</label>
            <input type="number" id="fromold" name="fromold" value="<?php echo esc_attr($fromold); ?>" step="0.01" />
        </div>
        <div class="svadba-field">
            <label for="fromnew">New price (from)</label>
            <input type="number" id="fromnew" name="fromnew" value="<?php echo esc_attr($fromnew); ?>" step="0.01" />
        </div>
        <div class="svadba-field">
            <label for="capacity">Capacity</label>
            <input type="number" id="capacity" name="capacity" value="<?php echo esc_attr($capacity); ?>" />
        </div>
        <div class="svadba-field">
            <label for="distance">Base car time</label>
            <select id="distance" name="distance">
                <?php $distance_options = range(2, 8);
                $current_distance = (int)$distance;
                if ($current_distance < 2) {
                    $current_distance = 2;
                }
                foreach ($distance_options as $opt) {
                    printf('<option value="%d" %s>%d</option>', $opt, selected($current_distance, $opt, false), $opt);
                } ?>
            </select>
        </div>
        <div class="svadba-field">
            <label for="cer-time">Ceremony time</label>
            <input type="text" id="cer-time" name="cer-time" value="<?php echo esc_attr($cer_time); ?>" />
        </div>
    </div>
    <div class="svadba-fields-row">
        <div class="svadba-field">
            <label for="google_map_url">Google Map link (embed or regular URL)</label>
            <input type="url" id="google_map_url" name="google_map_url" value="<?php echo esc_attr($google_map_url); ?>" placeholder="https://www.google.com/maps/..." />
        </div>
    </div>
<?php
}

function svadba_save_price_fields($post_id)
{
    if (!isset($_POST['svadba_price_fields_nonce']) || !wp_verify_nonce($_POST['svadba_price_fields_nonce'], 'svadba_price_fields_nonce')) return;
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
    if (!current_user_can('edit_post', $post_id)) return;
    $fields = array('fromold', 'fromnew', 'capacity', 'distance', 'cer-time');
    foreach ($fields as $field) {
        if (isset($_POST[$field])) {
            update_post_meta($post_id, $field, sanitize_text_field($_POST[$field]));
        } else {
            delete_post_meta($post_id, $field);
        }
    }

    if (isset($_POST['google_map_url'])) {
        $google_map_url = esc_url_raw(wp_unslash($_POST['google_map_url']));
        if (!empty($google_map_url)) {
            update_post_meta($post_id, 'google_map_url', $google_map_url);
        } else {
            delete_post_meta($post_id, 'google_map_url');
        }
    } else {
        delete_post_meta($post_id, 'google_map_url');
    }
}
add_action('save_post_svadba', 'svadba_save_price_fields');

// --- Additional characteristics ---
function svadba_add_characteristics_metabox()
{
    add_meta_box(
        'svadba_characteristics',
        'Additional characteristics',
        'svadba_characteristics_callback',
        'svadba',
        'normal',
        'high'
    );
}
add_action('add_meta_boxes', 'svadba_add_characteristics_metabox');

function svadba_characteristics_callback($post)
{
    wp_nonce_field('svadba_characteristics_nonce', 'svadba_characteristics_nonce');
    $characteristics = get_post_meta($post->ID, 'characteristics', true);
?>
    <style>
        #characteristics {
            width: 100%;
            font-family: monospace;
            font-size: 13px;
            line-height: 1.5;
        }
    </style>
    <textarea id="characteristics" name="characteristics" rows="15"><?php echo esc_textarea($characteristics); ?></textarea>
    <p class="description">Вставьте готовый HTML-код в это поле.</p>
<?php
}

function svadba_save_characteristics($post_id)
{
    if (!isset($_POST['svadba_characteristics_nonce']) || !wp_verify_nonce($_POST['svadba_characteristics_nonce'], 'svadba_characteristics_nonce')) return;
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
    if (!current_user_can('edit_post', $post_id)) return;
    if (isset($_POST['characteristics'])) {
        $characteristics = wp_kses_post($_POST['characteristics']);
        if ($characteristics) {
            update_post_meta($post_id, 'characteristics', $characteristics);
        } else {
            delete_post_meta($post_id, 'characteristics');
        }
    } else {
        delete_post_meta($post_id, 'characteristics');
    }
}
add_action('save_post_svadba', 'svadba_save_characteristics');

// --- Image next to text ---
function svadba_add_contextpic_metabox()
{
    add_meta_box(
        'svadba_contextpic',
        'Image next to text',
        'svadba_contextpic_callback',
        array('svadba', 'service'),
        'normal',
        'high'
    );
}
add_action('add_meta_boxes', 'svadba_add_contextpic_metabox');

function svadba_contextpic_callback($post)
{
    wp_nonce_field('svadba_contextpic_nonce', 'svadba_contextpic_nonce');
    $post_type = get_post_type($post);
    $is_service = ($post_type === 'service');
    $contextpic_id = get_post_meta($post->ID, 'contextpic', true);
    $contextpic_url = $contextpic_id ? wp_get_attachment_image_url($contextpic_id, 'medium') : '';
?>
    <?php if ($is_service): ?>
        <style>
            .service-meta-layout {
                display: grid;
                grid-template-columns: minmax(320px, 380px) minmax(360px, 1fr);
                gap: 24px;
                align-items: start;
            }

            .service-meta-json-wrap textarea {
                font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace;
                min-height: 440px;
            }

            @media (max-width: 980px) {
                .service-meta-layout {
                    grid-template-columns: 1fr;
                }

                .service-meta-json-wrap textarea {
                    min-height: 260px;
                }
            }
        </style>
    <?php endif; ?>

    <div id="svadba_contextpic_container" class="<?php echo $is_service ? 'service-meta-layout' : ''; ?>">
        <div class="service-meta-image-wrap">
        <input type="hidden" id="contextpic" name="contextpic" value="<?php echo esc_attr($contextpic_id); ?>" />
        <div id="contextpic_preview">
            <?php if ($contextpic_url): ?>
                <img src="<?php echo esc_url($contextpic_url); ?>" style="max-width: 300px; display: block; margin-bottom: 10px;" />
            <?php endif; ?>
        </div>
        <button type="button" class="button" id="contextpic_upload_button">
            <?php echo $contextpic_id ? 'Изменить изображение' : 'Выбрать изображение'; ?>
        </button>
        <?php if ($contextpic_id): ?>
            <button type="button" class="button" id="contextpic_remove_button">Удалить изображение</button>
        <?php endif; ?>
        </div>

        <?php if ($is_service): ?>
            <div class="service-meta-json-wrap">
                <?php service_shortcode_settings_callback($post); ?>
            </div>
        <?php endif; ?>
    </div>
    <script>
        jQuery(document).ready(function($) {
            let contextpicFrame;
            $('#contextpic_upload_button').on('click', function(e) {
                e.preventDefault();
                if (contextpicFrame) {
                    contextpicFrame.open();
                    return;
                }
                contextpicFrame = wp.media({
                    title: 'Выберите изображение',
                    button: {
                        text: 'Использовать это изображение'
                    },
                    multiple: false
                });
                contextpicFrame.on('select', function() {
                    const attachment = contextpicFrame.state().get('selection').first().toJSON();
                    $('#contextpic').val(attachment.id);
                    $('#contextpic_preview').html('<img src="' + attachment.url + '" style="max-width: 300px; display: block; margin-bottom: 10px;" />');
                    $('#contextpic_upload_button').text('Изменить изображение');
                    if (!$('#contextpic_remove_button').length) {
                        $('#contextpic_upload_button').after('<button type="button" class="button" id="contextpic_remove_button">Удалить изображение</button>');
                    }
                });
                contextpicFrame.open();
            });
            $(document).on('click', '#contextpic_remove_button', function(e) {
                e.preventDefault();
                $('#contextpic').val('');
                $('#contextpic_preview').empty();
                $('#contextpic_upload_button').text('Выбрать изображение');
                $(this).remove();
            });
        });
    </script>
<?php
}

function svadba_save_contextpic($post_id)
{
    // Check post type
    $post_type = get_post_type($post_id);
    if (!in_array($post_type, array('svadba', 'service'))) return;
    
    if (!isset($_POST['svadba_contextpic_nonce']) || !wp_verify_nonce($_POST['svadba_contextpic_nonce'], 'svadba_contextpic_nonce')) return;
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
    if (!current_user_can('edit_post', $post_id)) return;
    if (isset($_POST['contextpic'])) {
        $contextpic_id = intval($_POST['contextpic']);
        if ($contextpic_id) {
            update_post_meta($post_id, 'contextpic', $contextpic_id);
        } else {
            delete_post_meta($post_id, 'contextpic');
        }
    } else {
        delete_post_meta($post_id, 'contextpic');
    }
}
add_action('save_post', 'svadba_save_contextpic');

// --- Service shortcode settings ---
function service_shortcode_settings_callback($post)
{
    wp_nonce_field('service_shortcode_settings_nonce', 'service_shortcode_settings_nonce');

    $service_price_tables_json = (string) get_post_meta($post->ID, 'service_price_tables_json', true);
    ?>
    <p>
        <label for="service_price_tables_json"><strong>Service price tables JSON</strong></label>
    </p>
    <textarea
        id="service_price_tables_json"
        name="service_price_tables_json"
        class="widefat"
        rows="12"
        spellcheck="false"
    ><?php echo esc_textarea($service_price_tables_json); ?></textarea>
    <p class="description">Храните данные как JSON-массив секций. Каждая секция: {"title":"...","key":"photo"}.</p>
    <?php
}

function service_save_shortcode_settings($post_id)
{
    if (!isset($_POST['service_shortcode_settings_nonce']) || !wp_verify_nonce($_POST['service_shortcode_settings_nonce'], 'service_shortcode_settings_nonce')) return;
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
    if (!current_user_can('edit_post', $post_id)) return;

    if (!isset($_POST['service_price_tables_json'])) {
        return;
    }

    $raw_json = trim((string) wp_unslash($_POST['service_price_tables_json']));
    if ($raw_json === '') {
        delete_post_meta($post_id, 'service_price_tables_json');
        return;
    }

    $decoded = json_decode($raw_json, true);
    if (json_last_error() !== JSON_ERROR_NONE || !is_array($decoded)) {
        update_post_meta($post_id, 'service_price_tables_json', $raw_json);
        return;
    }

    $normalized = array();
    foreach ($decoded as $row) {
        if (!is_array($row)) {
            continue;
        }

        $title = isset($row['title']) ? trim((string) $row['title']) : '';
        $key = isset($row['key']) ? trim((string) $row['key']) : '';
        if ($key === '' && isset($row['keys'])) {
            $keys_input = $row['keys'];
            if (is_string($keys_input)) {
                $keys_input = explode(',', $keys_input);
            }
            if (is_array($keys_input) && !empty($keys_input)) {
                $key = trim((string) reset($keys_input));
            }
        }

        if ($title === '' && $key === '') {
            continue;
        }

        $normalized[] = array(
            'title' => $title,
            'key' => $key,
        );
    }

    update_post_meta(
        $post_id,
        'service_price_tables_json',
        wp_json_encode($normalized, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT)
    );
}
add_action('save_post_service', 'service_save_shortcode_settings');
