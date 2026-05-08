<?php
/**
 * Custom REST API Fields
 *
 * @package TanahLotSabah
 * @since 6.0
 */

if (!defined('ABSPATH')) exit;

// ============================================
// REGISTER CUSTOM FIELDS FOR REST API
// ============================================

add_action('rest_api_init', 'tls_register_custom_rest_fields');
function tls_register_custom_rest_fields() {
    // Register sles_data field
    register_rest_field('tanah', 'sles_data', array(
        'get_callback' => 'tls_get_sles_data_field',
        'update_callback' => null,
        'schema' => null,
    ));

    // Register class_list field for Flutter app filtering
    register_rest_field('tanah', 'class_list', array(
        'get_callback' => 'tls_get_class_list_field',
        'update_callback' => null,
        'schema' => null,
    ));
}

// ============================================
// FIELD CALLBACKS
// ============================================

/**
 * Get sles_data field for REST API
 */
function tls_get_sles_data_field($post) {
    return array(
        'price' => get_post_meta($post['id'], '_tanah_harga', true),
        'size' => get_post_meta($post['id'], '_tanah_keluasan', true),
        'id_code' => get_post_meta($post['id'], '_tanah_property_id', true),
        'land_type' => get_post_meta($post['id'], '_tanah_jenis_geran', true) ?: 'cl',
        'modified' => get_post($post['id'])->post_modified,
        'latitude' => get_post_meta($post['id'], '_tanah_latitude', true),
        'longitude' => get_post_meta($post['id'], '_tanah_longitude', true),
    );
}

/**
 * Get class_list field for REST API
 * This field helps Flutter app filter properties
 */
function tls_get_class_list_field($post) {
    $classes = [];
    $geran_terms = get_the_terms($post['id'], 'jenis_geran');

    if (!empty($geran_terms)) {
        foreach ($geran_terms as $term) {
            if (in_array($term->slug, ['nt', 'native-title', 'native'])) {
                $classes[] = 'native-title';
            } elseif (in_array($term->slug, ['cl', 'country-lease'])) {
                $classes[] = 'country-lease';
            }
        }
    }

    return $classes;
}
