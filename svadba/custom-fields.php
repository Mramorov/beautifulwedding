<?php
function beautifulwedding_add_meta_boxes()
{
    add_meta_box(
        'svadba_price_fields',
        'Pricing & Details',
        'svadba_price_fields_callback',
        'svadba',
        'normal',
        'high'
    );
    
    add_meta_box(
        'svadba_contextpic',
        'Изображение рядом с текстом',
        'svadba_contextpic_callback',
        'svadba',
        'normal',
        'high'
    );
    
    add_meta_box(
        'svadba_gallery',
        'Wedding Gallery',
        'svadba_gallery_callback',
        'svadba',
        'normal',
        'high'
    );
}
add_action('add_meta_boxes', 'beautifulwedding_add_meta_boxes');

function svadba_price_fields_callback($post)
{
    wp_nonce_field('svadba_price_fields_nonce', 'svadba_price_fields_nonce');
    
    $fromold = get_post_meta($post->ID, 'fromold', true);
    $fromnew = get_post_meta($post->ID, 'fromnew', true);
    $capacity = get_post_meta($post->ID, 'capacity', true);
    $distance = get_post_meta($post->ID, 'distance', true);
    $cer_time = get_post_meta($post->ID, 'cer-time', true);
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
    </div>
    
    <div class="svadba-fields-row">
        <div class="svadba-field">
            <label for="distance">Base car time</label>
            <input type="number" id="distance" name="distance" value="<?php echo esc_attr($distance); ?>" />
        </div>
        <div class="svadba-field">
            <label for="cer-time">Ceremony time</label>
            <input type="text" id="cer-time" name="cer-time" value="<?php echo esc_attr($cer_time); ?>" />
        </div>
    </div>
    <?php
}

function svadba_contextpic_callback($post)
{
    wp_nonce_field('svadba_contextpic_nonce', 'svadba_contextpic_nonce');
    $contextpic_id = get_post_meta($post->ID, 'contextpic', true);
    $contextpic_url = $contextpic_id ? wp_get_attachment_image_url($contextpic_id, 'medium') : '';
    ?>
    <div id="svadba_contextpic_container">
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
                    button: { text: 'Использовать это изображение' },
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

function svadba_enqueue_admin_scripts($hook)
{
    global $post;
    if (($hook === 'post-new.php' || $hook === 'post.php') && isset($post->post_type) && $post->post_type === 'svadba') {
        wp_enqueue_media();
        wp_enqueue_script('jquery-ui-sortable');
    }
}
add_action('admin_enqueue_scripts', 'svadba_enqueue_admin_scripts');

function svadba_gallery_callback($post)
{
    wp_nonce_field('svadba_gallery_nonce', 'svadba_gallery_nonce');
    $gallery_images = (array) get_post_meta($post->ID, 'svadba_gallery', true);
?>
    <div id="svadba_gallery_container">
        <input type="hidden" id="svadba_gallery_images" name="svadba_gallery" value="<?php echo esc_attr(implode(',', $gallery_images)); ?>" />
        <div id="svadba_gallery_preview">
            <?php foreach ($gallery_images as $image_id):
                $thumb = wp_get_attachment_image_src($image_id, 'thumbnail');
                if ($thumb): ?>
                    <div class="gallery-image">
                        <img data-id="<?php echo esc_attr($image_id); ?>" src="<?php echo esc_url($thumb[0]); ?>" />
                        <button type="button" class="remove-image">×</button>
                    </div>
            <?php endif;
            endforeach; ?>
        </div>
        <button type="button" class="button" id="svadba_add_gallery_images">Add Gallery Images</button>
    </div>

    <style>
        #svadba_gallery_preview {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin: 10px 0;
        }

        .gallery-image {
            position: relative;
            max-width: 150px;
            cursor: move;
        }

        .gallery-image img {
            display: block;
            width: 100%;
            height: auto;
            border-radius: 3px;
        }

        .remove-image {
            position: absolute;
            top: 5px;
            right: 5px;
            background: #dc3545;
            color: #fff;
            border: none;
            border-radius: 50%;
            padding: 0 6px;
            cursor: pointer;
        }
    </style>

    <script>
        jQuery(document).ready(function($) {
            let frame;

            $('#svadba_add_gallery_images').on('click', function(e) {
                e.preventDefault();
                if (frame) frame.open();
                frame = wp.media({
                    title: 'Select Gallery Images',
                    button: {
                        text: 'Add to gallery'
                    },
                    multiple: true
                });
                frame.on('select', function() {
                    const attachments = frame.state().get('selection').toJSON();
                    let ids = $('#svadba_gallery_images').val() ? $('#svadba_gallery_images').val().split(',') : [];
                    attachments.forEach(a => {
                        if (!ids.includes(a.id.toString())) {
                            ids.push(a.id);
                            $('#svadba_gallery_preview').append(
                                `<div class="gallery-image"><img data-id="${a.id}" src="${a.sizes.thumbnail.url}" /><button type="button" class="remove-image">×</button></div>`
                            );
                        }
                    });
                    $('#svadba_gallery_images').val(ids.join(','));
                });
                frame.open();
            });

            $('#svadba_gallery_preview').on('click', '.remove-image', function() {
                const imgId = $(this).siblings('img').attr('data-id');
                let ids = $('#svadba_gallery_images').val().split(',').filter(id => id !== imgId);
                $('#svadba_gallery_images').val(ids.join(','));
                $(this).parent().remove();
            });

            $('#svadba_gallery_preview').sortable({
                update: function() {
                    const ids = $('#svadba_gallery_preview img').map(function() {
                        return $(this).attr('data-id');
                    }).get();
                    $('#svadba_gallery_images').val(ids.join(','));
                }
            });
        });
    </script>
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
}
add_action('save_post_svadba', 'svadba_save_price_fields');

function svadba_save_contextpic($post_id)
{
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
add_action('save_post_svadba', 'svadba_save_contextpic');

function svadba_save_gallery_meta($post_id)
{
    if (!isset($_POST['svadba_gallery_nonce']) || !wp_verify_nonce($_POST['svadba_gallery_nonce'], 'svadba_gallery_nonce')) return;
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
    if (!current_user_can('edit_post', $post_id)) return;

    if (isset($_POST['svadba_gallery'])) {
        $ids = array_filter(array_map('intval', explode(',', $_POST['svadba_gallery'])));
        update_post_meta($post_id, 'svadba_gallery', $ids);
    } else {
        delete_post_meta($post_id, 'svadba_gallery');
    }
}
add_action('save_post_svadba', 'svadba_save_gallery_meta');
