<?php 
// Logout page - auto logs out and redirects
if (is_user_logged_in()) {
    wp_logout();
    wp_redirect(home_url('/'));
    exit;
}
wp_redirect(home_url('/login/'));
exit;
