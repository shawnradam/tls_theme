<?php
/**
 * TLS REST API - Authentication Endpoints
 * 
 * Provides login and registration endpoints for Flutter app
 */

if (!defined('ABSPATH')) {
    exit;
}

add_action('rest_api_init', function() {
    // Login endpoint
    register_rest_route('sles/v1', '/auth/login', array(
        'methods' => 'POST',
        'callback' => 'tls_api_handle_login',
        'permission_callback' => '__return_true',
    ));

    // Register endpoint
    register_rest_route('sles/v1', '/auth/register', array(
        'methods' => 'POST',
        'callback' => 'tls_api_handle_register',
        'permission_callback' => '__return_true',
    ));
});

/**
 * Handle user login
 */
function tls_api_handle_login($request) {
    $params = $request->get_json_params();
    if (empty($params)) {
        $params = $request->get_body_params();
    }

    $email = isset($params['email']) ? $params['email'] : '';
    $password = isset($params['password']) ? $params['password'] : '';

    if (empty($email) || empty($password)) {
        return new WP_Error('missing_params', 'Email and password are required', array('status' => 400));
    }

    // Try to get user by email or username
    $user = get_user_by('email', $email);
    if (!$user) {
        $user = get_user_by('login', $email);
    }

    if (!$user || !wp_check_password($password, $user->data->user_pass, $user->ID)) {
        return new WP_Error('invalid_credentials', 'Invalid email or password', array('status' => 401));
    }

    // Generate token using the helper from tls-rest-api-user.php
    if (!function_exists('tls_api_generate_token')) {
        require_once dirname(__DIR__) . '/tls-rest-api-user.php';
    }
    
    $token = tls_api_generate_token($user->ID);
    $expires_at = date('Y-m-d H:i:s', strtotime('+30 days'));

    // Get user metadata
    $phone = get_user_meta($user->ID, 'phone', true);
    $ic_number = get_user_meta($user->ID, 'ic_number', true);
    $is_bumiputera = get_user_meta($user->ID, 'is_bumiputera', true);

    return array(
        'success' => true,
        'message' => 'Login successful',
        'token' => $token,
        'expires_at' => $expires_at,
        'user' => array(
            'id' => $user->ID,
            'email' => $user->user_email,
            'name' => $user->display_name,
            'username' => $user->user_login,
            'phone' => $phone ?: '',
            'ic_number' => $ic_number ?: '',
            'is_bumiputera' => (bool) ($is_bumiputera ?: false),
            'created_at' => $user->user_registered
        )
    );
}

/**
 * Handle user registration
 */
function tls_api_handle_register($request) {
    $params = $request->get_json_params();
    if (empty($params)) {
        $params = $request->get_body_params();
    }

    $name = isset($params['name']) ? sanitize_text_field($params['name']) : '';
    $email = isset($params['email']) ? sanitize_email($params['email']) : '';
    $password = isset($params['password']) ? $params['password'] : '';
    $phone = isset($params['phone']) ? sanitize_text_field($params['phone']) : '';
    $ic_number = isset($params['ic_number']) ? sanitize_text_field($params['ic_number']) : '';
    $is_bumiputera = isset($params['is_bumiputera']) ? (bool) $params['is_bumiputera'] : false;

    if (empty($name) || empty($email) || empty($password)) {
        return new WP_Error('missing_params', 'Name, email and password are required', array('status' => 400));
    }

    if (email_exists($email)) {
        return new WP_Error('email_exists', 'An account with this email already exists', array('status' => 400));
    }

    // Create username from email
    $username = sanitize_user(current(explode('@', $email)));
    if (username_exists($username)) {
        $username = $username . '_' . wp_generate_password(4, false, false);
    }

    $user_id = wp_create_user($username, $password, $email);

    if (is_wp_error($user_id)) {
        return $user_id;
    }

    // Update display name
    wp_update_user(array(
        'ID' => $user_id,
        'display_name' => $name
    ));

    // Save additional meta
    update_user_meta($user_id, 'phone', $phone);
    update_user_meta($user_id, 'ic_number', $ic_number);
    update_user_meta($user_id, 'is_bumiputera', $is_bumiputera);

    return array(
        'success' => true,
        'message' => 'Registration successful. You can now login.',
        'user_id' => $user_id
    );
}
