<?php
/**
 * TLS REST API - User Authentication Endpoints
 * 
 * Provides user profile, update, and password change endpoints for Flutter app
 * 
 * Endpoints:
 * - GET /sles/v1/user/profile - Get current user profile
 * - POST /sles/v1/user/update-profile - Update user profile
 * - POST /sles/v1/user/change-password - Change password
 */

if (!defined('ABSPATH')) {
    exit;
}

// ============================================
// Register REST API Routes
// ============================================

add_action('rest_api_init', function() {
    // User profile endpoint (GET)
    register_rest_route('sles/v1', '/user/profile', array(
        'methods' => 'GET',
        'callback' => 'tls_api_get_user_profile',
        'permission_callback' => '__return_true',
    ));

    // User update profile endpoint (POST)
    register_rest_route('sles/v1', '/user/update-profile', array(
        'methods' => 'POST',
        'callback' => 'tls_api_update_user_profile',
        'permission_callback' => '__return_true',
    ));

    // Change password endpoint (POST)
    register_rest_route('sles/v1', '/user/change-password', array(
        'methods' => 'POST',
        'callback' => 'tls_api_change_password',
        'permission_callback' => '__return_true',
    ));
});

// ============================================
// Helper Functions
// ============================================

/**
 * Validate authentication token from request header
 */
function tls_api_validate_token() {
    $headers = isset($_SERVER['HTTP_AUTHORIZATION']) ? $_SERVER['HTTP_AUTHORIZATION'] : '';
    
    if (empty($headers)) {
        // Try to get from apache headers
        if (function_exists('apache_request_headers')) {
            $apache_headers = apache_request_headers();
            $headers = isset($apache_headers['Authorization']) ? $apache_headers['Authorization'] : '';
        }
    }
    
    if (empty($headers)) {
        return false;
    }
    
    // Remove 'Bearer ' prefix if present
    $token = str_replace('Bearer ', '', $headers);
    $token = trim($token);
    
    if (empty($token)) {
        return false;
    }
    
    // Validate token against stored tokens in user meta
    return tls_api_validate_auth_token($token);
}

/**
 * Validate auth token and return user ID
 */
function tls_api_validate_auth_token($token) {
    if (empty($token)) {
        return false;
    }
    
    // Get all users and check their tokens
    $users = get_users(array('fields' => 'ID'));
    
    foreach ($users as $user_id) {
        $saved_tokens = get_user_meta($user_id, 'app_auth_tokens', true);
        
        if (empty($saved_tokens)) {
            continue;
        }
        
        if (is_string($saved_tokens)) {
            $saved_tokens = array($saved_tokens);
        }
        
        if (is_array($saved_tokens) && in_array($token, $saved_tokens)) {
            return (int) $user_id;
        }
    }
    
    return false;
}

/**
 * Generate new auth token for user
 */
function tls_api_generate_token($user_id) {
    $token = wp_hash($user_id . '-' . time() . '-' . wp_generate_password(32, false));
    
    // Get existing tokens
    $existing_tokens = get_user_meta($user_id, 'app_auth_tokens', true);
    
    if (empty($existing_tokens)) {
        $existing_tokens = array();
    } elseif (is_string($existing_tokens)) {
        $existing_tokens = array($existing_tokens);
    }
    
    // Add new token
    $existing_tokens[] = $token;
    
    // Keep only last 5 tokens
    if (count($existing_tokens) > 5) {
        $existing_tokens = array_slice($existing_tokens, -5);
    }
    
    update_user_meta($user_id, 'app_auth_tokens', $existing_tokens);
    
    return $token;
}

/**
 * Save token for user (called from login endpoint)
 */
function tls_api_save_token($user_id, $token) {
    $existing_tokens = get_user_meta($user_id, 'app_auth_tokens', true);
    
    if (empty($existing_tokens)) {
        $existing_tokens = array();
    } elseif (is_string($existing_tokens)) {
        $existing_tokens = array($existing_tokens);
    }
    
    $existing_tokens[] = $token;
    
    // Keep only last 5 tokens
    if (count($existing_tokens) > 5) {
        $existing_tokens = array_slice($existing_tokens, -5);
    }
    
    update_user_meta($user_id, 'app_auth_tokens', $existing_tokens);
}

// ============================================
// API Endpoint Callbacks
// ============================================

/**
 * GET /sles/v1/user/profile
 * Get current user profile
 */
function tls_api_get_user_profile($request) {
    $user_id = tls_api_validate_token();
    
    if (!$user_id) {
        return array(
            'success' => false,
            'message' => 'Unauthorized. Please login.'
        );
    }
    
    $user = get_userdata($user_id);
    
    if (!$user) {
        return array(
            'success' => false,
            'message' => 'User not found'
        );
    }
    
    // Get user metadata
    $phone = get_user_meta($user_id, 'phone', true);
    $ic_number = get_user_meta($user_id, 'ic_number', true);
    $is_bumiputera = get_user_meta($user_id, 'is_bumiputera', true);
    
    return array(
        'success' => true,
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
 * POST /sles/v1/user/update-profile
 * Update user profile
 */
function tls_api_update_user_profile($request) {
    $user_id = tls_api_validate_token();
    
    if (!$user_id) {
        return array(
            'success' => false,
            'message' => 'Unauthorized. Please login.'
        );
    }
    
    // Get request body
    $body = $request->get_body();
    $params = json_decode($body, true);
    
    if (empty($params)) {
        return array(
            'success' => false,
            'message' => 'No data provided'
        );
    }
    
    // Update display name
    if (!empty($params['name'])) {
        wp_update_user(array(
            'ID' => $user_id,
            'display_name' => sanitize_text_field($params['name'])
        ));
    }
    
    // Update phone
    if (isset($params['phone'])) {
        update_user_meta($user_id, 'phone', sanitize_text_field($params['phone']));
    }
    
    // Update IC number
    if (isset($params['ic_number'])) {
        update_user_meta($user_id, 'ic_number', sanitize_text_field($params['ic_number']));
    }
    
    // Update bumiputera status
    if (isset($params['is_bumiputera'])) {
        update_user_meta($user_id, 'is_bumiputera', (bool) $params['is_bumiputera']);
    }
    
    return array(
        'success' => true,
        'message' => 'Profile updated successfully'
    );
}

/**
 * POST /sles/v1/user/change-password
 * Change password
 */
function tls_api_change_password($request) {
    $user_id = tls_api_validate_token();
    
    if (!$user_id) {
        return array(
            'success' => false,
            'message' => 'Unauthorized. Please login.'
        );
    }
    
    // Get request body
    $body = $request->get_body();
    $params = json_decode($body, true);
    
    if (empty($params)) {
        return array(
            'success' => false,
            'message' => 'No data provided'
        );
    }
    
    $current_password = isset($params['current_password']) ? $params['current_password'] : '';
    $new_password = isset($params['new_password']) ? $params['new_password'] : '';
    
    if (empty($current_password) || empty($new_password)) {
        return array(
            'success' => false,
            'message' => 'Current and new password are required'
        );
    }
    
    // Verify current password
    $user = get_user_by('ID', $user_id);
    
    if (!wp_check_password($current_password, $user->user_pass, $user_id)) {
        return array(
            'success' => false,
            'message' => 'Current password is incorrect'
        );
    }
    
    // Update password
    wp_set_password($new_password, $user_id);
    
    return array(
        'success' => true,
        'message' => 'Password changed successfully'
    );
}

/**
 * POST /sles/v1/user/delete-account
 * Delete user account
 */
function tls_api_delete_account($request) {
    $user_id = tls_api_validate_token();
    
    if (!$user_id) {
        return array(
            'success' => false,
            'message' => 'Unauthorized. Please login.'
        );
    }
    
    // Get request body
    $body = $request->get_body();
    $params = json_decode($body, true);
    
    $password = isset($params['password']) ? $params['password'] : '';
    
    if (empty($password)) {
        return array(
            'success' => false,
            'message' => 'Password required to delete account'
        );
    }
    
    // Verify password
    $user = get_user_by('ID', $user_id);
    
    if (!wp_check_password($password, $user->user_pass, $user_id)) {
        return array(
            'success' => false,
            'message' => 'Password is incorrect'
        );
    }
    
    // Delete user
    require_once(ABSPATH . 'wp-admin/includes/user.php');
    wp_delete_user($user_id);
    
    return array(
        'success' => true,
        'message' => 'Account deleted successfully'
    );
}