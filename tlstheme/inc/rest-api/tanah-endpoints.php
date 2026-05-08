<?php
/**
 * Tanah REST API Endpoints
 *
 * @package TanahLotSabah
 * @since 6.0
 */

if (!defined('ABSPATH')) exit;

// ============================================
// REGISTER CUSTOM ENDPOINTS
// ============================================

add_action('rest_api_init', 'tls_register_tanah_endpoints');
function tls_register_tanah_endpoints() {
    // Register /sles/v1/lands/nt endpoint
    register_rest_route('sles/v1', '/lands/nt', array(
        'methods' => 'GET',
        'callback' => 'tls_api_get_nt_lands',
        'permission_callback' => '__return_true',
    ));

    // Register /sles/v1/lands/cl endpoint
    register_rest_route('sles/v1', '/lands/cl', array(
        'methods' => 'GET',
        'callback' => 'tls_api_get_cl_lands',
        'permission_callback' => '__return_true',
    ));

    // Register /sles/v1/areas endpoint
    register_rest_route('sles/v1', '/areas', array(
        'methods' => 'GET',
        'callback' => 'tls_api_get_areas',
        'permission_callback' => '__return_true',
    ));
}

// ============================================
// ENDPOINT CALLBACKS
// ============================================

/**
 * Get Native Title (NT) lands
 */
function tls_api_get_nt_lands() {
    $posts = get_posts(array(
        'post_type' => 'tanah',
        'post_status' => 'publish',
        'posts_per_page' => 50,
        'tax_query' => array(
            array(
                'taxonomy' => 'jenis_geran',
                'field' => 'slug',
                'terms' => array('nt', 'native-title', 'native')
            )
        )
    ));

    $lands = array();
    foreach ($posts as $post) {
        $lands[] = tls_format_tanah_for_api($post);
    }
    return $lands;
}

/**
 * Get Country Lease (CL) lands
 */
function tls_api_get_cl_lands() {
    $posts = get_posts(array(
        'post_type' => 'tanah',
        'post_status' => 'publish',
        'posts_per_page' => 50,
        'tax_query' => array(
            array(
                'taxonomy' => 'jenis_geran',
                'field' => 'slug',
                'terms' => array('cl', 'country-lease')
            )
        )
    ));

    $lands = array();
    foreach ($posts as $post) {
        $lands[] = tls_format_tanah_for_api($post);
    }
    return $lands;
}

/**
 * Get areas/districts
 */
function tls_api_get_areas() {
    $terms = get_terms(array(
        'taxonomy' => 'daerah',
        'hide_empty' => false,
    ));

    $areas = array();
    foreach ($terms as $term) {
        $areas[] = array(
            'id' => (string)$term->term_id,
            'name' => $term->name,
            'slug' => $term->slug,
        );
    }
    return $areas;
}

// ============================================
// HELPER FUNCTIONS
// ============================================

/**
 * Format tanah post for API response
 */
function tls_format_tanah_for_api($post) {
    $price = get_post_meta($post->ID, '_tanah_harga', true);
    $size = get_post_meta($post->ID, '_tanah_keluasan', true);
    $id_code = get_post_meta($post->ID, '_tanah_property_id', true);
    $lat = get_post_meta($post->ID, '_tanah_latitude', true);
    $lng = get_post_meta($post->ID, '_tanah_longitude', true);

    $geran_terms = get_the_terms($post->ID, 'jenis_geran');
    $geran = !empty($geran_terms) ? reset($geran_terms)->slug : 'cl';
    $land_type = (in_array($geran, array('nt', 'native-title', 'native'))) ? 'native' : 'cl';

    $daerah_terms = get_the_terms($post->ID, 'daerah');
    $location = !empty($daerah_terms) ? reset($daerah_terms)->name : 'Sabah';

    $featured_image = get_the_post_thumbnail_url($post->ID, 'large');
    $gallery = $featured_image ? array($featured_image) : array();

    return array(
        'id' => (string)$post->ID,
        'title' => array('rendered' => $post->post_title),
        'content' => array('rendered' => $post->post_content),
        'sles_data' => array(
            'price' => $price ? floatval($price) : 0.0,
            'size' => $size ? $size : '',
            'id_code' => $id_code ? $id_code : '',
            'land_type' => $land_type,
            'modified' => $post->post_modified,
            'latitude' => $lat ? $lat : null,
            'longitude' => $lng ? $lng : null,
        ),
        'district' => $location,
        'featured_media_url' => $gallery,
    );
}
