<?php
/*
Template Name: Hubungi Kami (Modal Redirect)
Description: Redirects to home page with contact modal open
*/

$property_id = isset($_GET['property']) ? sanitize_text_field($_GET['property']) : '';

// Redirect to home with modal parameter
$redirect_url = get_home_url();
if ($property_id) {
    $redirect_url = add_query_arg('contact', 'modal', $redirect_url);
    $redirect_url = add_query_arg('property', $property_id, $redirect_url);
} else {
    $redirect_url = add_query_arg('contact', 'modal', $redirect_url);
}

wp_redirect($redirect_url);
exit;