<?php
/**
 * App Settings Management
 * Manage Play Store link for mobile app
 */

// Add submenu page
add_action('admin_menu', function() {
    add_submenu_page('tls-dashboard', 'App Download', 'App Download', 'manage_options', 'tls-app-download', 'tls_app_download_page');
});

// Render the page
function tls_app_download_page() {
    // Handle form submissions
    $message = '';
    
    if (isset($_POST['tls_app_nonce']) && wp_verify_nonce($_POST['tls_app_nonce'], 'tls_app_save')) {
        $playstore_url = sanitize_text_field($_POST['playstore_url']);
        $app_enabled = isset($_POST['app_enabled']) ? 1 : 0;
        
        update_option('tls_playstore_url', $playstore_url);
        update_option('tls_app_enabled', $app_enabled);
        
        $message = 'Settings saved successfully!';
    }
    
    $playstore_url = get_option('tls_playstore_url', '');
    $app_enabled = get_option('tls_app_enabled', 0);
    ?>
    <div class="wrap tls-app-download-wrap">
        <h1>
            <span class="dashicons dashicons-smartphone"></span> 
            App Download Settings
        </h1>
        <p class="description">Configure the mobile app download link shown in the footer.</p>
        
        <?php if ($message): ?>
            <div class="notice notice-success is-dismissible">
                <p><?php echo esc_html($message); ?></p>
            </div>
        <?php endif; ?>
        
        <style>
            .tls-app-download-wrap { max-width: 800px; }
            .tls-app-download-wrap h1 { display: flex; align-items: center; gap: 10px; }
            .tls-app-card {
                background: #fff;
                border: 1px solid #ccc;
                border-radius: 8px;
                padding: 30px;
                margin-top: 20px;
            }
            .tls-app-card label {
                font-weight: 600;
                display: block;
                margin-bottom: 8px;
            }
            .tls-app-card input[type="url"] {
                width: 100%;
                max-width: 500px;
                padding: 12px 16px;
                border: 1px solid #ddd;
                border-radius: 6px;
                font-size: 16px;
            }
            .tls-app-card input[type="url"]:focus {
                border-color: #2271b1;
                outline: none;
            }
            .tls-app-card p.help-text {
                color: #666;
                font-size: 14px;
                margin-top: 5px;
            }
            .tls-app-card .toggle-row {
                display: flex;
                align-items: center;
                gap: 12px;
                margin-bottom: 25px;
                padding-bottom: 25px;
                border-bottom: 1px solid #eee;
            }
            .tls-app-card .toggle-row input[type="checkbox"] {
                width: 24px;
                height: 24px;
            }
            .tls-app-card .toggle-row label {
                margin-bottom: 0;
            }
            .tls-app-card .submit-row {
                margin-top: 25px;
                padding-top: 25px;
                border-top: 1px solid #eee;
            }
            .tls-app-card .submit-row .button-primary {
                padding: 10px 30px;
                font-size: 16px;
            }
            .tls-app-preview {
                background: #f8f9fa;
                padding: 20px;
                border-radius: 8px;
                margin-top: 30px;
            }
            .tls-app-preview h3 {
                margin-top: 0;
                font-size: 14px;
                color: #666;
                text-transform: uppercase;
            }
            .tls-app-preview .preview-box {
                background: #1e293b;
                color: white;
                padding: 15px 20px;
                border-radius: 8px;
                display: flex;
                align-items: center;
                gap: 15px;
            }
            .tls-app-preview .play-icon {
                font-size: 30px;
            }
        </style>
        
        <div class="tls-app-card">
            <form method="post">
                <?php wp_nonce_field('tls_app_save', 'tls_app_nonce'); ?>
                
                <div class="toggle-row">
                    <input type="checkbox" name="app_enabled" id="app_enabled" value="1" <?php checked($app_enabled, 1); ?>>
                    <label for="app_enabled">Show App Download in Footer</label>
                </div>
                
                <label for="playstore_url">Play Store URL</label>
                <input type="url" name="playstore_url" id="playstore_url" 
                       value="<?php echo esc_attr($playstore_url); ?>" 
                       placeholder="https://play.google.com/store/apps/details?id=com.example.app">
                <p class="help-text">Enter the full Play Store URL for your mobile app.</p>
                
                <div class="submit-row">
                    <button type="submit" class="button button-primary">Save Settings</button>
                </div>
            </form>
            
            <div class="tls-app-preview">
                <h3>Footer Preview</h3>
                <div class="preview-box">
                    <span class="play-icon">▶</span>
                    <span>Download Our App</span>
                </div>
            </div>
        </div>
    </div>
    <?php
}

// Get app settings for frontend
function tls_get_app_settings() {
    return [
        'enabled' => get_option('tls_app_enabled', 0),
        'playstore_url' => get_option('tls_playstore_url', '')
    ];
}