<?php
/**
 * Tanah (Land) Custom Post Type
 *
 * @package TanahLotSabah
 * @since 6.0
 */

if (!defined('ABSPATH')) exit;

add_action('init', 'tls_register_tanah_cpt', 0);
function tls_register_tanah_cpt() {
    register_post_type('tanah', [
        'labels' => [
            'name' => 'Senarai Tanah',
            'singular_name' => 'Tanah',
            'add_new_item' => 'Tambah Tanah Baru',
            'edit_item' => 'Edit Tanah',
            'all_items' => 'Semua Tanah',
        ],
        'public' => true,
        'has_archive' => true,
        'show_in_menu' => false,
        'show_in_rest' => true,
        'rest_base' => 'tanah',
        'supports' => ['title', 'editor', 'thumbnail', 'custom-fields'],
        'rewrite' => ['slug' => 'tanah']
    ]);
}

add_action('add_meta_boxes', 'tls_tanah_add_meta_boxes');
function tls_tanah_add_meta_boxes() {
    add_meta_box(
        'tls_tanah_development_status',
        'Development Status',
        'tls_tanah_development_status_callback',
        'tanah',
        'side',
        'default'
    );
}

function tls_tanah_development_status_callback($post) {
    wp_nonce_field('tls_tanah_dev_status_nonce', 'tls_dev_status_nonce_field');
    $value = get_post_meta($post->ID, '_tanah_development_status', true);
    ?>
    <p>
        <label for="tls_development_status">Select Development Status:</label>
    </p>
    <p>
        <select name="tls_development_status" id="tls_development_status" style="width:100%; padding:8px;">
            <option value="">-- Select --</option>
            <option value="planned" <?php selected($value, 'planned'); ?>>Planned</option>
            <option value="in_progress" <?php selected($value, 'in_progress'); ?>>In Progress</option>
            <option value="completed" <?php selected($value, 'completed'); ?>>Completed</option>
            <option value="raw_land" <?php selected($value, 'raw_land'); ?>>Raw Land</option>
        </select>
    </p>
    <p style="margin-top:12px;">
        <label for="tls_development_notes">Development Notes (optional):</label>
    </p>
    <p>
        <textarea name="tls_development_notes" id="tls_development_notes" rows="3" style="width:100%;"><?php echo esc_textarea(get_post_meta($post->ID, '_tanah_development_notes', true)); ?></textarea>
    </p>
    <?php
}

add_action('save_post_tanah', 'tls_tanah_save_development_status');
function tls_tanah_save_development_status($post_id) {
    if (!isset($_POST['tls_dev_status_nonce_field']) || 
        !wp_verify_nonce($_POST['tls_dev_status_nonce_field'], 'tls_tanah_dev_status_nonce')) {
        return;
    }
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
    if (!current_user_can('edit_post', $post_id)) return;

    if (isset($_POST['tls_development_status'])) {
        update_post_meta($post_id, '_tanah_development_status', sanitize_text_field($_POST['tls_development_status']));
    }
    if (isset($_POST['tls_development_notes'])) {
        update_post_meta($post_id, '_tanah_development_notes', sanitize_textarea_html($_POST['tls_development_notes']));
    }
}
