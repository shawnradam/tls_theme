<?php
/**
 * Other Custom Post Types (Hero Video, POI)
 *
 * @package TanahLotSabah
 * @since 6.0
 */

if (!defined('ABSPATH')) exit;

add_action('init', 'tls_register_other_cpts', 0);
function tls_register_other_cpts() {
    // Hero Video CPT
    register_post_type('hero_video', [
        'labels' => [
            'name' => 'Hero Videos',
            'singular_name' => 'Hero Video',
            'add_new_item' => 'Tambah Hero Video',
            'edit_item' => 'Edit Hero Video',
            'all_items' => 'Semua Hero Video',
        ],
        'public' => false,
        'show_ui' => true,
        'show_in_menu' => false, // Managed via TLS System dashboard
        'supports' => ['title', 'thumbnail'],
    ]);

    // POI (Points of Interest / Nearby Places) CPT
    register_post_type('tls_poi', [
        'labels' => [
            'name' => 'Nearby Places',
            'singular_name' => 'Place',
            'all_items' => 'All Places',
            'add_new_item' => 'Add New Place',
        ],
        'public' => false,
        'show_ui' => true,
        'show_in_menu' => false, // Managed via TLS System dashboard
        'supports' => ['title', 'thumbnail'],
    ]);
}
