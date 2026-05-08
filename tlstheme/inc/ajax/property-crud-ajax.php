<?php
/**
 * Property CRUD AJAX Handlers
 * For frontend property management
 */

if (!defined('ABSPATH')) exit;

// Get single property data for editing
add_action('wp_ajax_tls_get_property', 'tls_ajax_get_property');
function tls_ajax_get_property() {
    // Verify nonce
    if (!wp_verify_nonce($_POST['nonce'], 'tls_get_property')) {
        wp_send_json_error('Invalid nonce');
    }
    
    if (!current_user_can('manage_options')) {
        wp_send_json_error('Permission denied');
    }
    
    $post_id = intval($_POST['id']);
    $post = get_post($post_id);
    
    if (!$post || $post->post_type !== 'tanah') {
        wp_send_json_error('Property not found');
    }
    
    $data = [
        'id' => $post->ID,
        'title' => $post->post_title,
        'content' => $post->post_content,
        'status' => $post->post_status,
        'property_id' => get_post_meta($post_id, '_tanah_property_id', true),
        'price' => get_post_meta($post_id, '_tanah_harga', true),
        'area' => get_post_meta($post_id, '_tanah_keluasan', true),
        'geran' => get_post_meta($post_id, '_tanah_jenis_geran', true),
        'zoning' => get_post_meta($post_id, '_tanah_zoning', true),
        'town' => get_post_meta($post_id, '_tanah_town', true),
        'latitude' => get_post_meta($post_id, '_tanah_latitude', true),
        'longitude' => get_post_meta($post_id, '_tanah_longitude', true),
        'building_size' => get_post_meta($post_id, '_tanah_building_size', true),
        'building_unit' => get_post_meta($post_id, '_tanah_building_unit', true),
        'verified' => get_post_meta($post_id, '_tanah_verified', true),
    ];
    
    // Get daerah
    $daerah_terms = get_the_terms($post_id, 'daerah');
    $data['daerah'] = ($daerah_terms && !is_wp_error($daerah_terms)) ? $daerah_terms[0]->name : '';
    
    wp_send_json_success($data);
}
