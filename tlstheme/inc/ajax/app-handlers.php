<?php
/**
 * App AJAX Handlers
 * Handle app launch subscriptions
 */

if (!defined('ABSPATH')) exit;

add_action('wp_ajax_tls_app_subscribe', 'tls_ajax_app_subscribe');
add_action('wp_ajax_nopriv_tls_app_subscribe', 'tls_ajax_app_subscribe');

function tls_ajax_app_subscribe() {
    $email = sanitize_email($_POST['email']);

    if (!is_email($email)) {
        wp_send_json_error('Alamat emel tidak sah.');
    }

    // In a real app, you would save this to a database table
    // For now, we will send an email to the admin or just return success
    
    $admin_email = get_option('admin_email');
    $subject = 'New App Launch Subscription';
    $message = "A user has subscribed to be notified when the app launches.\n\nEmail: $email";
    
    wp_mail($admin_email, $subject, $message);

    wp_send_json_success('Terima kasih! Kami akan maklumkan kepada anda sebaik sahaja aplikasi dilancarkan.');
}
