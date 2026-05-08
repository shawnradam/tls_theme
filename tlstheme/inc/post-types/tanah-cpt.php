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
        'show_in_menu' => false, // Managed via TLS System dashboard
        'show_in_rest' => true, // Enable REST API
        'rest_base' => 'tanah', // REST API base
        'supports' => ['title', 'editor', 'thumbnail', 'custom-fields'],
        'rewrite' => ['slug' => 'tanah']
    ]);
}
