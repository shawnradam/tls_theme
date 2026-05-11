<?php
/**
 * Contact Form Handler
 *
 * @package TanahLotSabah
 */

if (!defined('ABSPATH')) exit;

// ============================================
// AJAX: Send Contact Email
// ============================================

add_action('wp_ajax_tls_send_contact_email', 'tls_handle_contact_email');
add_action('wp_ajax_nopriv_tls_send_contact_email', 'tls_handle_contact_email');

function tls_handle_contact_email() {
    check_ajax_referer('tls_contact_nonce', 'nonce');

    $name = sanitize_text_field($_POST['name'] ?? '');
    $email = sanitize_email($_POST['email'] ?? '');
    $phone = sanitize_text_field($_POST['phone'] ?? '');
    $subject = sanitize_text_field($_POST['subject'] ?? '');
    $message = sanitize_textarea_field($_POST['message'] ?? '');

    if (empty($name) || empty($email) || empty($subject) || empty($message)) {
        wp_send_json_error('Sila isi semua ruangan yang diperlukan.');
    }

    if (!is_email($email)) {
        wp_send_json_error('Alamat emel tidak sah.');
    }

    $to_email = get_theme_mod('email_address', get_option('admin_email'));
    
    $email_subject = '[' . get_bloginfo('name') . '] ' . $subject . ' - dari ' . $name;
    
    $email_body = "Nama: $name\n";
    $email_body .= "Emel: $email\n";
    $email_body .= "Telefon: " . ($phone ?: 'Tidak dinyatakan') . "\n";
    $email_body .= "Subjek: $subject\n\n";
    $email_body .= "Mesej:\n$message\n";
    $email_body .= "\n---\nHantar dari: " . get_bloginfo('url') . "\n";

    $headers = [
        'Content-Type: text/plain; charset=UTF-8',
        'From: ' . $name . ' <' . $email . '>',
        'Reply-To: ' . $email
    ];

    $sent = wp_mail($to_email, $email_subject, $email_body, $headers);

    if ($sent) {
        wp_send_json_success('Emel berjaya dihantar!');
    } else {
        wp_send_json_error('Gagal menghantar emel. Sila cuba lagi.');
    }
}