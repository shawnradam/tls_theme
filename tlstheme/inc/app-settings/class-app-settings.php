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
        $app_status = sanitize_text_field($_POST['app_status']);
        $app_announcement = sanitize_textarea_field($_POST['app_announcement']);
        
        update_option('tls_playstore_url', $playstore_url);
        update_option('tls_app_enabled', $app_enabled);
        update_option('tls_app_status', $app_status);
        update_option('tls_app_announcement', $app_announcement);
        
        $message = 'App settings saved successfully!';
    }
    
    $playstore_url = get_option('tls_playstore_url', '');
    $app_enabled = get_option('tls_app_enabled', 0);
    $app_status = get_option('tls_app_status', 'coming_soon');
    $app_announcement = get_option('tls_app_announcement', 'Aplikasi mudah alih Tanah Lot Sabah sedang dibangunkan untuk memudahkan urusan anda.');
    ?>
    <div class="wrap tls-admin-modern">
        <div class="tls-admin-header">
            <h1>
                <span class="dashicons dashicons-smartphone"></span> 
                App Download Settings
            </h1>
            <p>Manage your mobile application distribution and marketplace visibility.</p>
        </div>
        
        <?php if ($message): ?>
            <div class="notice notice-success is-dismissible" style="border-radius:8px; margin: 20px 0;">
                <p><strong><?php echo esc_html($message); ?></strong></p>
            </div>
        <?php endif; ?>
        
        <form method="post">
            <?php wp_nonce_field('tls_app_save', 'tls_app_nonce'); ?>
            
            <div class="tls-grid-layout">
                <!-- Main Column: Configuration -->
                <div class="tls-grid-col main-col">
                    <div class="tls-card">
                        <div class="card-header">
                            <span class="dashicons dashicons-admin-generic"></span>
                            <h2>App Configuration</h2>
                        </div>

                        <div class="tls-form-row" style="padding-bottom: 20px; border-bottom: 1px solid #f1f5f9; margin-bottom: 25px;">
                            <label for="app_enabled" style="display: flex; align-items: center; gap: 12px; cursor: pointer; font-weight: 700;">
                                <input type="checkbox" name="app_enabled" id="app_enabled" value="1" <?php checked($app_enabled, 1); ?> style="width:20px; height:20px;">
                                <span>Enable App Visibility (Frontend Footer)</span>
                            </label>
                        </div>
                        
                        <div class="tls-form-row">
                            <label for="playstore_url">Play Store URL</label>
                            <input type="url" name="playstore_url" id="playstore_url" class="tls-input"
                                   value="<?php echo esc_attr($playstore_url); ?>" 
                                   placeholder="https://play.google.com/store/apps/details?id=...">
                            <p class="description">Paste the complete store URL where users can download the TLS app.</p>
                        </div>
                        
                        <div class="tls-form-row">
                            <label>App Availability Status</label>
                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-top: 10px;">
                                <label class="status-item <?php echo $app_status === 'available' ? 'active' : ''; ?>" style="cursor:pointer; display:block;">
                                    <input type="radio" name="app_status" value="available" <?php checked($app_status, 'available'); ?> style="margin-bottom:10px;">
                                    <div class="lab" style="font-size:12px;">Live & Available</div>
                                    <div class="val" style="font-size:14px; margin-top:5px;">Market Ready</div>
                                </label>
                                <label class="status-item <?php echo $app_status === 'coming_soon' ? 'active' : ''; ?>" style="cursor:pointer; display:block;">
                                    <input type="radio" name="app_status" value="coming_soon" <?php checked($app_status, 'coming_soon'); ?> style="margin-bottom:10px;">
                                    <div class="lab" style="font-size:12px;">Under Development</div>
                                    <div class="val" style="font-size:14px; margin-top:5px;">Coming Soon</div>
                                </label>
                            </div>
                        </div>

                        <div class="tls-form-row">
                            <label for="app_announcement">Marketplace Announcement</label>
                            <textarea name="app_announcement" id="app_announcement" rows="4" class="tls-input"
                                      placeholder="Explain the benefits of the TLS mobile app..."><?php echo esc_textarea($app_announcement); ?></textarea>
                            <p class="description">This message appears in the app modal to encourage downloads or newsletter signups.</p>
                        </div>

                        <div style="margin-top: 30px;">
                            <button type="submit" class="btn btn-primary">
                                <span class="dashicons dashicons-saved"></span> Save App Settings
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Sidebar: Live Preview -->
                <div class="tls-grid-col side-col">
                    <div class="tls-card">
                        <div class="card-header">
                            <span class="dashicons dashicons-visibility"></span>
                            <h2>Live Preview</h2>
                        </div>
                        <div class="preview-section">
                            <h4>Modal Rendering</h4>
                            <div style="background: #fff; border: 1px solid var(--tls-border); padding: 20px; border-radius: var(--tls-radius); box-shadow: 0 4px 12px rgba(0,0,0,0.05);">
                                <div style="width: 40px; height: 40px; background: var(--tls-primary); border-radius: 10px; display: flex; align-items: center; justify-content: center; color: white; margin-bottom: 15px;">
                                    <span class="dashicons dashicons-smartphone"></span>
                                </div>
                                <h4 style="margin: 0 0 8px 0; color: var(--tls-text-main);">Tanah Lot Sabah App</h4>
                                <p style="margin: 0 0 20px 0; font-size: 12px; line-height: 1.5; color: var(--tls-text-muted);">
                                    <?php echo nl2br(esc_html($app_announcement)); ?>
                                </p>
                                
                                <?php if ($app_status === 'available'): ?>
                                    <a href="#" class="btn btn-primary" style="width: 100%; border-radius: 30px; background: #000;">
                                        <span class="dashicons dashicons-googleplay"></span> Get it on Play
                                    </a>
                                <?php else: ?>
                                    <div style="padding: 12px; background: #f0fdf4; border: 1px dashed var(--tls-primary); border-radius: 8px; text-align: center;">
                                        <span style="font-size: 11px; font-weight: 700; color: var(--tls-primary);">Waitlist Form Active</span>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        <hr style="border:0; border-top: 1px solid #f1f5f9; margin: 20px 0;">
                        <div class="preview-section">
                            <h4>Visibility Status</h4>
                            <div class="preview-list">
                                <?php if ($app_enabled): ?>
                                    <span class="badge badge-success">Displayed Online</span>
                                <?php else: ?>
                                    <span class="badge" style="background:#f1f5f9; color:#64748b;">Hidden</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
    <?php
}

// Get app settings for frontend
function tls_get_app_settings() {
    return [
        'enabled' => get_option('tls_app_enabled', 0),
        'playstore_url' => get_option('tls_playstore_url', ''),
        'status' => get_option('tls_app_status', 'coming_soon'),
        'announcement' => get_option('tls_app_announcement', 'Aplikasi mudah alih Tanah Lot Sabah sedang dibangunkan untuk memudahkan urusan anda.')
    ];
}