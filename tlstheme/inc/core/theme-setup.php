<?php
/**
 * Theme Setup and Configuration
 *
 * @package TanahLotSabah
 * @since 6.0
 */

if (!defined('ABSPATH')) exit;

// ============================================
// THEME SETUP
// ============================================

add_action('after_setup_theme', 'tls_theme_setup');
function tls_theme_setup() {
    add_theme_support('title-tag');
    add_theme_support('post-thumbnails');
    add_theme_support('html5', ['search-form', 'comment-form']);

    register_nav_menus([
        'primary' => 'Menu Utama',
        'footer' => 'Menu Footer'
    ]);

    add_image_size('listing-thumb', 600, 400, true);
    add_image_size('hero-bg', 1920, 1080, true);
}

// ============================================
// REWRITE RULES
// ============================================

add_action('init', 'tls_rewrite_rules', 1);
function tls_rewrite_rules() {
    add_rewrite_rule('^login/?', 'index.php?pagename=login', 'top');
    add_rewrite_rule('^dashboard/?', 'index.php?pagename=dashboard', 'top');
}

// ============================================
// THEME ACTIVATION
// ============================================

add_action('after_switch_theme', 'tls_theme_activation');
function tls_theme_activation() {
    flush_rewrite_rules();

    // Create database tables for features
    if (class_exists('TLS_LDC_Database')) {
        TLS_LDC_Database::create_tables();
    }

    if (class_exists('TLS_FAB_System')) {
        TLS_FAB_System::create_table();
    }

    // Create default land types
    tls_create_land_types();
}

// Fallback flush on admin init
add_action('admin_init', 'tls_admin_flush_rules');
function tls_admin_flush_rules() {
    global $pagenow;
    if ($pagenow === 'admin.php' && isset($_GET['page']) && $_GET['page'] === 'tls-splash') {
        flush_rewrite_rules();
    }
}

// ============================================
// LAND TYPES CREATION
// ============================================

add_action('init', 'tls_create_land_types', 1);
function tls_create_land_types() {
    $land_types = [
        ['name' => 'Land Lot', 'slug' => 'land-lot'],
        ['name' => 'Commercial', 'slug' => 'commercial'],
        ['name' => 'Manufacturing', 'slug' => 'manufacturing'],
        ['name' => 'Palm Oil', 'slug' => 'palm-oil'],
    ];

    foreach ($land_types as $type) {
        if (!term_exists($type['slug'], 'land_type')) {
            wp_insert_term($type['name'], 'land_type', [
                'slug' => $type['slug']
            ]);
        }
    }
}
