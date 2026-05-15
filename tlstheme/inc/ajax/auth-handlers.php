<?php
/**
 * Authentication AJAX Handlers
 *
 * @package TanahLotSabah
 * @since 6.0
 */

if (!defined('ABSPATH')) exit;

// ============================================
// AJAX LOGOUT
// ============================================

add_action('wp_ajax_tls_logout', 'tls_custom_logout');
add_action('wp_ajax_nopriv_tls_logout', 'tls_custom_logout');

function tls_custom_logout() {
    wp_logout();
    wp_send_json_success(['redirect' => home_url('/')]);
}

// ============================================
// AJAX LOGIN
// ============================================

add_action('wp_ajax_tls_ajax_login', 'tls_ajax_login');
add_action('wp_ajax_nopriv_tls_ajax_login', 'tls_ajax_login');

function tls_ajax_login() {
    check_ajax_referer('tls_login_nonce', 'nonce');

    $creds = [
        'user_login' => sanitize_text_field($_POST['username']),
        'user_password' => $_POST['password'],
        'remember' => isset($_POST['remember'])
    ];

    $secure_cookie = is_ssl();

    $user = wp_signon($creds, $secure_cookie);

    if (is_wp_error($user)) {
        wp_send_json_error(['message' => $user->get_error_message()]);
    }

    wp_set_current_user($user->ID);

    wp_send_json_success(['redirect' => home_url('/dashboard/'), 'message' => 'Login successful']);
}
