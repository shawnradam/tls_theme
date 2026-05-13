<?php
/**
 * Security and Access Control
 *
 * @package TanahLotSabah
 * @since 6.0
 */

if (!defined('ABSPATH')) exit;

// ============================================
// ADMIN BAR CONTROL
// ============================================

add_filter('show_admin_bar', 'tls_maybe_hide_admin_bar');
function tls_maybe_hide_admin_bar($show) {
    if (is_user_logged_in()) {
        $hide = get_user_meta(get_current_user_id(), 'tls_hide_admin_bar', true);
        if ($hide) return false;
    }
    return $show;
}

// ============================================
// WP-LOGIN.PHP REDIRECT
// ============================================

// Disabled to allow standard login access as requested
/*
add_action('login_init', 'tls_block_wp_login');
function tls_block_wp_login() {
    // Check if wp-login.php blocking is enabled
    $disable_wp_login = get_option('tls_disable_wp_login', 0);

    if ($disable_wp_login) {
        wp_redirect(home_url());
        exit;
    }
}
*/

// ============================================
// ADMIN ACCESS RESTRICTION
// ============================================

// Disabled to allow admin dashboard access as requested
/*
add_action('admin_init', 'tls_restrict_admin_access');
function tls_restrict_admin_access() {
    // Skip on AJAX requests
    if (defined('DOING_AJAX') && DOING_AJAX) {
        return;
    }

    // Allow profile page access for all logged-in users
    global $pagenow;
    if ($pagenow === 'profile.php' || $pagenow === 'user-edit.php') {
        return;
    }

    // Only restrict non-admin users from accessing other admin pages
    if (!current_user_can('manage_options')) {
        wp_redirect(home_url());
        exit;
    }
}
*/
