<?php
/**
 * Custom Taxonomies
 *
 * @package TanahLotSabah
 * @since 6.0
 */

if (!defined('ABSPATH')) exit;

add_action('init', 'tls_register_taxonomies', 0);
function tls_register_taxonomies() {
    // Daerah (District) Taxonomy
    register_taxonomy('daerah', 'tanah', [
        'labels' => ['name' => 'Daerah'],
        'hierarchical' => true,
        'show_in_rest' => true, // Enable REST API
        'rewrite' => ['slug' => 'daerah']
    ]);

    // Jenis Geran (Grant Type) Taxonomy
    register_taxonomy('jenis_geran', 'tanah', [
        'labels' => ['name' => 'Jenis Geran'],
        'hierarchical' => true,
        'show_in_rest' => true, // Enable REST API
    ]);

    // Land Type Taxonomy
    register_taxonomy('land_type', 'tanah', [
        'labels' => [
            'name' => 'Land Type',
            'add_new_item' => 'Add Land Type',
            'edit_item' => 'Edit Land Type',
        ],
        'hierarchical' => true,
        'show_admin_column' => true,
    ]);
}
