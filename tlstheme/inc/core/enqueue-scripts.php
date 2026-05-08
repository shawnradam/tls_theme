<?php
/**
 * Enqueue Scripts and Styles
 *
 * @package TanahLotSabah
 * @since 6.0
 */

if (!defined('ABSPATH')) exit;

// ============================================
// FRONTEND SCRIPTS & STYLES
// ============================================

add_action('wp_enqueue_scripts', 'tls_enqueue_frontend_scripts');
function tls_enqueue_frontend_scripts() {
    // Theme Styles
    wp_enqueue_style('tls-style', get_stylesheet_uri(), [], TLS_VERSION);
    
    // Google Fonts: Plus Jakarta Sans (headings) + Inter (body) - Clean Real Estate Typography
    // Add cache busting to fonts
    $font_version = 'v1.' . time();
    wp_enqueue_style('tls-pro-fonts', 'https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Inter:wght@300;400;500;600;700&display=swap', [], $font_version);
    wp_enqueue_style('google-material-icons', 'https://fonts.googleapis.com/icon?family=Material+Icons', [], null);
    wp_enqueue_style('tls-calculator', get_template_directory_uri() . '/assets/css/calculator.css', [], TLS_VERSION);

    // Map Assets
    wp_enqueue_style('leaflet-css', 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.css', [], '1.9.4');
    wp_enqueue_style('leaflet-fullscreen-css', 'https://api.mapbox.com/mapbox.js/plugins/leaflet-fullscreen/v1.0.1/leaflet.fullscreen.css', ['leaflet-css'], '1.0.1');
    wp_enqueue_style('tlsmap-css', get_template_directory_uri() . '/assets/css/tlsmap.css', ['leaflet-css'], TLS_VERSION);

    wp_enqueue_script('leaflet-js', 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.js', [], '1.9.4', true);
    wp_enqueue_script('esri-leaflet-js', get_template_directory_uri() . '/assets/js/esri-leaflet.js', ['leaflet-js'], '3.0.12', true);
    wp_enqueue_script('leaflet-fullscreen-js', 'https://api.mapbox.com/mapbox.js/plugins/leaflet-fullscreen/v1.0.1/Leaflet.fullscreen.min.js', ['leaflet-js'], '1.0.1', true);
    wp_enqueue_script('leaflet-rotate', 'https://unpkg.com/leaflet-rotate@0.2.8/dist/leaflet-rotate.js', ['leaflet-js'], '0.2.8', true);
    
    // Force cache busting with timestamp
$tlsmap_version = TLS_VERSION . '.' . time();

wp_enqueue_script('tlsmap-js', get_template_directory_uri() . '/assets/js/tlsmap.js', ['leaflet-js', 'esri-leaflet-js'], $tlsmap_version, true);
    wp_localize_script('tlsmap-js', 'tlsmapConfig', [
        'ajaxUrl' => admin_url('admin-ajax.php'),
        'themeUri' => get_template_directory_uri(),
        'canEdit' => false,
        'defaultLat' => get_option('tlsmap_default_lat', '6.1300'),
        'defaultLng' => get_option('tlsmap_default_lng', '116.2300'),
        'defaultZoom' => get_option('tlsmap_default_zoom', '12'),
        'colors' => [
            'available' => get_option('tlsmap_color_available', '#16a34a'),
            'reserved' => get_option('tlsmap_color_reserved', '#f59e0b'),
            'sold' => get_option('tlsmap_color_sold', '#ef4444'),
        ]
    ]);

    // Calculator Scripts
    wp_enqueue_script('tls-calculator', get_template_directory_uri() . '/assets/js/calculator.js', ['jquery'], TLS_VERSION, true);
    wp_localize_script('tls-calculator', 'ldcAjax', [
        'ajaxurl' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('tls_ldc_nonce'),
        'currency' => 'RM',
        'messages' => [
            'success_save' => 'Estimate saved successfully!',
            'error_general' => 'An error occurred. Please try again.',
            'generating_pdf' => 'Generating your PDF report...',
        ]
    ]);
}

// ============================================
// ADMIN SCRIPTS & STYLES
// ============================================

add_action('admin_enqueue_scripts', 'tls_enqueue_admin_scripts');
function tls_enqueue_admin_scripts($hook) {
    // Splash Page
    if ($hook === 'appearance_page_tls-splash') {
        wp_enqueue_media();
    }

    // Nearby Places Page
    if (strpos($hook, 'tls-nearby-places') !== false) {
        wp_enqueue_media();
    }

    // Post Edit Pages
    global $post_type;
    if ($hook === 'post.php' || $hook === 'post-new.php') {
        if (in_array($post_type, ['hero_video', 'tanah', 'tls_agent'])) {
            wp_enqueue_media();

            // Enqueue Leaflet for tanah post type (map drawing)
            if ($post_type === 'tanah') {
                wp_enqueue_style('leaflet', 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.css', [], '1.9.4');
                wp_enqueue_style('leaflet-draw', 'https://unpkg.com/leaflet-draw@1.0.4/dist/leaflet.draw.css', ['leaflet'], '1.0.4');

                wp_enqueue_script('leaflet', 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.js', [], '1.9.4', true);
                wp_enqueue_script('leaflet-draw', 'https://unpkg.com/leaflet-draw@1.0.4/dist/leaflet.draw.js', ['leaflet'], '1.0.4', true);
            }
        }
    }
}
