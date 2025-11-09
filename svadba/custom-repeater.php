<?php
/**
 * Custom Repeater Field for Svadba
 */

function beautifulwedding_add_repeater_meta_box() {
    add_meta_box(
        'svadba_repeater',
        'Additional Information',
        'svadba_repeater_callback',
        'svadba',
        'normal',
        'high'
    );
}
add_action('add_meta_boxes', 'beautifulwedding_add_repeater_meta_box');

function svadba_repeater_callback($post) {
    wp_nonce_field('svadba_repeater_nonce', 'svadba_repeater_nonce');
    
    // Get saved values
    $repeater_data = get_post_meta($post->ID, 'svadba_repeater_data', true);
    if (!is_array($repeater_data)) {
        $repeater_data = array();
    }
    ?>
    <div id="svadba_repeater_container">
        <div class="repeater-items">
            <?php
            if (!empty($repeater_data)) {
                foreach ($repeater_data as $index => $item) {
                    svadba_render_repeater_item($index, $item);
                }
            }
            ?>
        </div>
        
        <button type="button" class="button" id="add_repeater_item">Add New Item</button>
    </div>

    <!-- Template for new items -->
    <script type="text/template" id="repeater_template">
        <?php svadba_render_repeater_item('{{index}}'); ?>
    </script>

    <style>
        .repeater-item {
            background: #f9f9f9;
            padding: 15px;
            margin-bottom: 10px;
            border: 1px solid #ddd;
            position: relative;
        }
        .repeater-item .handle {
            background: #e5e5e5;
            padding: 5px;
            margin-bottom: 10px;
            cursor: move;
        }
        .repeater-item .remove-item {
            position: absolute;
            top: 10px;
            right: 10px;
            color: red;
            cursor: pointer;
        }
        .image-preview {
            max-width: 150px;
            margin: 10px 0;
        }
        .image-preview img {
            max-width: 100%;
            height: auto;
        }
        .field-group {
            margin-bottom: 15px;
        }
        .field-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
    </style>

    <script>
    jQuery(document).ready(function($) {
        var container = $('#svadba_repeater_container');
        var template = $('#repeater_template').html();
        var nextIndex = $('.repeater-item').length;

        // Add new item
        $('#add_repeater_item').on('click', function() {
            var newItem = template.replace(/\{\{index\}\}/g, nextIndex);
            $('.repeater-items').append(newItem);
            nextIndex++;
        });

        // Remove item
        container.on('click', '.remove-item', function() {
            $(this).closest('.repeater-item').remove();
        });

        // Image upload
        container.on('click', '.upload-image', function(e) {
            e.preventDefault();
            var button = $(this);
            var imageContainer = button.siblings('.image-preview');
            var imageInput = button.siblings('.image-input');

            var frame = wp.media({
                title: 'Select Image',
                multiple: false
            });

            frame.on('select', function() {
                var attachment = frame.state().get('selection').first().toJSON();
                imageContainer.html('<img src="' + attachment.url + '" />');
                imageInput.val(attachment.id);
            });

            frame.open();
        });

        // Make items sortable
        $('.repeater-items').sortable({
            handle: '.handle',
            items: '.repeater-item'
        });
    });
    </script>
    <?php
}

function svadba_render_repeater_item($index, $item = array()) {
    $text = isset($item['text']) ? $item['text'] : '';
    $image = isset($item['image']) ? $item['image'] : '';
    $number = isset($item['number']) ? $item['number'] : '';
    ?>
    <div class="repeater-item">
        <div class="handle">Drag to Reorder</div>
        <span class="remove-item">×</span>

        <div class="field-group">
            <label>Text</label>
            <input type="text" 
                   name="svadba_repeater[<?php echo $index; ?>][text]" 
                   value="<?php echo esc_attr($text); ?>" 
                   class="widefat" />
        </div>

        <div class="field-group">
            <label>Image</label>
            <input type="hidden" 
                   name="svadba_repeater[<?php echo $index; ?>][image]" 
                   value="<?php echo esc_attr($image); ?>" 
                   class="image-input" />
            <div class="image-preview">
                <?php 
                if ($image) {
                    echo wp_get_attachment_image($image);
                }
                ?>
            </div>
            <button type="button" class="button upload-image">Select Image</button>
        </div>

        <div class="field-group">
            <label>Number</label>
            <input type="number" 
                   name="svadba_repeater[<?php echo $index; ?>][number]" 
                   value="<?php echo esc_attr($number); ?>" 
                   class="widefat" />
        </div>
    </div>
    <?php
}

function svadba_save_repeater_data($post_id) {
    if (!isset($_POST['svadba_repeater_nonce']) || 
        !wp_verify_nonce($_POST['svadba_repeater_nonce'], 'svadba_repeater_nonce')) {
        return;
    }

    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }

    if (!current_user_can('edit_post', $post_id)) {
        return;
    }

    if (isset($_POST['svadba_repeater'])) {
        $repeater_data = array();
        
        foreach ($_POST['svadba_repeater'] as $item) {
            $repeater_data[] = array(
                'text' => sanitize_text_field($item['text']),
                'image' => absint($item['image']),
                'number' => absint($item['number'])
            );
        }
        
        update_post_meta($post_id, 'svadba_repeater_data', $repeater_data);
    } else {
        delete_post_meta($post_id, 'svadba_repeater_data');
    }
}
add_action('save_post_svadba', 'svadba_save_repeater_data');

// Helper function to get repeater data
function get_svadba_repeater_data($post_id = null) {
    if (!$post_id) {
        $post_id = get_the_ID();
    }
    return get_post_meta($post_id, 'svadba_repeater_data', true);
}