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
    wp_enqueue_style('tls-pro-fonts', 'https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Inter:wght@300;400;500;600;700&display=swap', [], '1.0.1');
    wp_enqueue_style('google-material-icons', 'https://fonts.googleapis.com/icon?family=Material+Icons', [], null);
    wp_enqueue_style('tls-calculator', get_template_directory_uri() . '/assets/css/calculator.css', [], TLS_VERSION);

    // Chart.js for news/blog statistics
    wp_enqueue_script('chartjs', 'https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js', [], '4.4.0', true);
    wp_enqueue_script('tls-news-charts', get_template_directory_uri() . '/assets/js/news-charts.js', ['chartjs'], TLS_VERSION, true);

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

    // Theme Frontend Interactive Scripts (Externalized from PHP templates)
    wp_enqueue_script('tls-theme-frontend', get_template_directory_uri() . '/assets/js/theme-frontend.js', ['jquery'], TLS_VERSION, true);
    wp_localize_script('tls-theme-frontend', 'ldcAjax', [
        'ajaxurl' => admin_url('admin-ajax.php'),
        'nonce'   => wp_create_nonce('tls_nonce')
    ]);
}

// ============================================
// ADMIN SCRIPTS & STYLES
// ============================================
add_action('admin_enqueue_scripts', 'tls_enqueue_admin_scripts');
function tls_enqueue_admin_scripts($hook) {
    // Master Admin Modern UI Stack
    wp_enqueue_style('tls-admin-modern', get_template_directory_uri() . '/assets/css/tls-admin-modern.css', [], TLS_VERSION);

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
