<?php
/**
 * TLS Theme - FAB (Floating Action Button) Management System
 * Complete CRUD system for managing floating action buttons
 *
 * @package TLS_Theme
 * @version 1.0.0
 */

if (!defined('ABSPATH')) exit;

class TLS_FAB_System {

    private $table_name;
    private $option_name = 'tls_fab_settings';

    public function __construct() {
        global $wpdb;
        $this->table_name = $wpdb->prefix . 'tls_fab_settings';

        // Hooks
        // Note: admin_menu is now registered in functions.php to ensure proper load order
        // add_action('admin_menu', [$this, 'add_admin_menu']);
        add_action('wp_footer', [$this, 'render_fab']);
        add_action('wp_ajax_tls_save_fab_settings', [$this, 'save_fab_settings']);
        add_action('wp_ajax_tls_reset_fab_settings', [$this, 'reset_fab_settings']);
    }

    /**
     * Create database table for FAB settings
     */
    public static function create_table() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'tls_fab_settings';
        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE IF NOT EXISTS $table_name (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            fab_type varchar(50) NOT NULL DEFAULT 'type1',
            enabled tinyint(1) NOT NULL DEFAULT 1,
            show_search tinyint(1) NOT NULL DEFAULT 1,
            show_calculator tinyint(1) NOT NULL DEFAULT 1,
            show_whatsapp tinyint(1) NOT NULL DEFAULT 1,
            show_call tinyint(1) NOT NULL DEFAULT 1,
            whatsapp_number varchar(20) DEFAULT NULL,
            phone_number varchar(20) DEFAULT NULL,
            button_color varchar(20) DEFAULT '#667eea',
            button_position varchar(20) DEFAULT 'bottom-right',
            show_sticky_footer tinyint(1) NOT NULL DEFAULT 1,
            custom_css text DEFAULT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY  (id)
        ) $charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);

        // Insert default settings if table is empty
        $count = $wpdb->get_var("SELECT COUNT(*) FROM $table_name");
        if ($count == 0) {
            $wpdb->insert($table_name, [
                'fab_type' => 'type2',
                'enabled' => 1,
                'show_search' => 1,
                'show_calculator' => 1,
                'show_whatsapp' => 1,
                'show_call' => 1,
                'button_color' => '#667eea',
                'button_position' => 'bottom-right',
                'show_sticky_footer' => 1
            ]);
        }
    }

    /**
     * Get current FAB settings
     */
    public function get_settings() {
        global $wpdb;
        $settings = $wpdb->get_row("SELECT * FROM {$this->table_name} ORDER BY id DESC LIMIT 1", ARRAY_A);

        if (!$settings) {
            return $this->get_default_settings();
        }

        return $settings;
    }

    /**
     * Get default settings
     */
    private function get_default_settings() {
        return [
            'fab_type' => 'type2',
            'enabled' => 1,
            'show_search' => 1,
            'show_calculator' => 1,
            'show_whatsapp' => 1,
            'show_call' => 1,
            'whatsapp_number' => get_theme_mod('whatsapp_number', ''),
            'phone_number' => get_theme_mod('phone_number', ''),
            'button_color' => '#667eea',
            'button_position' => 'bottom-right',
            'show_sticky_footer' => 1,
            'custom_css' => ''
        ];
    }

    /**
     * Add admin menu
     */
    public function add_admin_menu() {
        add_submenu_page(
            'tls-dashboard',
            'FAB Menu Settings',
            'FAB Menu',
            'manage_options',
            'tls-fab-settings',
            [$this, 'render_admin_page']
        );
    }

    /**
     * Save FAB settings (AJAX)
     */
    public function save_fab_settings() {
        check_ajax_referer('tls_fab_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => 'Insufficient permissions']);
        }

        global $wpdb;

        $data = [
            'fab_type' => sanitize_text_field($_POST['fab_type']),
            'enabled' => isset($_POST['enabled']) ? 1 : 0,
            'show_search' => isset($_POST['show_search']) ? 1 : 0,
            'show_calculator' => isset($_POST['show_calculator']) ? 1 : 0,
            'show_whatsapp' => isset($_POST['show_whatsapp']) ? 1 : 0,
            'show_call' => isset($_POST['show_call']) ? 1 : 0,
            'whatsapp_number' => sanitize_text_field($_POST['whatsapp_number']),
            'phone_number' => sanitize_text_field($_POST['phone_number']),
            'button_color' => sanitize_hex_color($_POST['button_color']),
            'button_position' => sanitize_text_field($_POST['button_position']),
            'show_sticky_footer' => isset($_POST['show_sticky_footer']) ? 1 : 0,
            'custom_css' => sanitize_textarea_field($_POST['custom_css'])
        ];

        // Update or insert
        $existing = $wpdb->get_var("SELECT id FROM {$this->table_name} ORDER BY id DESC LIMIT 1");

        if ($existing) {
            $wpdb->update($this->table_name, $data, ['id' => $existing]);
        } else {
            $wpdb->insert($this->table_name, $data);
        }

        wp_send_json_success(['message' => 'FAB settings saved successfully!']);
    }

    /**
     * Reset FAB settings (AJAX)
     */
    public function reset_fab_settings() {
        check_ajax_referer('tls_fab_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => 'Insufficient permissions']);
        }

        global $wpdb;
        $wpdb->query("TRUNCATE TABLE {$this->table_name}");

        // Re-insert defaults
        $defaults = $this->get_default_settings();
        unset($defaults['whatsapp_number'], $defaults['phone_number'], $defaults['custom_css']);
        $wpdb->insert($this->table_name, $defaults);

        wp_send_json_success(['message' => 'FAB settings reset to defaults!']);
    }

    /**
     * Render admin page
     */
    public function render_admin_page() {
        $settings = $this->get_settings();
        ?>
        <div class="wrap tls-admin-modern">
            <div class="tls-admin-header">
                <h1>🎯 Floating Menu (FAB) & Footer Settings</h1>
                <p>Customize the behavior and appearance of your site's persistent engagement tools.</p>
            </div>

            <form id="tls-fab-form" method="post">
                <?php wp_nonce_field('tls_fab_nonce', 'tls_fab_nonce_field'); ?>

                <div class="tls-grid-layout">
                    <!-- Main Column: Settings -->
                    <div class="tls-grid-col main-col">
                        <!-- FAB Type Selection -->
                        <div class="tls-card">
                            <div class="card-header">
                                <span class="dashicons dashicons-layout"></span>
                                <h2>Interface Strategy</h2>
                            </div>
                            <p class="description" style="margin-bottom: 20px;">Select how users will interact with your engagement tools on mobile and desktop.</p>

                            <div class="fab-types-grid" style="grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));">
                                <!-- Type 1 -->
                                <label class="fab-type-card <?php echo $settings['fab_type'] === 'type1' ? 'active' : ''; ?>">
                                    <input type="radio" name="fab_type" value="type1" <?php checked($settings['fab_type'], 'type1'); ?>>
                                    <div class="fab-type-preview">
                                        <div class="preview-screen">
                                            <div class="preview-fab">+</div>
                                            <div class="preview-menu">
                                                <div class="preview-item">◈</div>
                                                <div class="preview-item">◈</div>
                                            </div>
                                        </div>
                                    </div>
                                    <h3 style="font-size:14px;">Full FAB Menu</h3>
                                    <p style="font-size:12px;">All tools inside a single expanding button.</p>
                                </label>

                                <!-- Type 2 -->
                                <label class="fab-type-card <?php echo $settings['fab_type'] === 'type2' ? 'active' : ''; ?>">
                                    <input type="radio" name="fab_type" value="type2" <?php checked($settings['fab_type'], 'type2'); ?>>
                                    <div class="fab-type-preview">
                                        <div class="preview-screen">
                                            <div class="preview-fab">+</div>
                                            <div class="preview-footer">
                                                <div class="preview-footer-btn">◉</div>
                                                <div class="preview-footer-btn">◉</div>
                                            </div>
                                        </div>
                                    </div>
                                    <h3 style="font-size:14px;">Hybrid Layout</h3>
                                    <p style="font-size:12px;">FAB for tools + Sticky bar for contact. <span class="badge badge-success" style="font-size:9px;">BEST</span></p>
                                </label>

                                <!-- Type 4 -->
                                <label class="fab-type-card <?php echo $settings['fab_type'] === 'type4' ? 'active' : ''; ?>">
                                    <input type="radio" name="fab_type" value="type4" <?php checked($settings['fab_type'], 'type4'); ?>>
                                    <div class="fab-type-preview">
                                        <div class="preview-screen">
                                            <div class="preview-footer">
                                                <div class="preview-footer-btn">◈</div>
                                                <div class="preview-footer-btn">◉</div>
                                                <div class="preview-footer-btn">◉</div>
                                            </div>
                                        </div>
                                    </div>
                                    <h3 style="font-size:14px;">Sticky Bar Only</h3>
                                    <p style="font-size:12px;">Traditional app-like bottom navigation bar.</p>
                                </label>
                            </div>
                        </div>

                        <div class="tls-card">
                            <div class="card-header">
                                <span class="dashicons dashicons-admin-generic"></span>
                                <h2>Configuration</h2>
                            </div>
                            
                            <div class="tls-form-row" style="padding-bottom: 15px; border-bottom: 1px solid #f1f5f9; margin-bottom: 20px;">
                                <label for="enabled" style="display:flex; align-items:center; gap:12px; cursor:pointer;">
                                    <input type="checkbox" name="enabled" id="enabled" <?php checked($settings['enabled'], 1); ?> style="width:20px; height:20px;">
                                    <strong>Enable engagement system site-wide</strong>
                                </label>
                            </div>

                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                                <div class="tls-form-row">
                                    <label>Search Button</label>
                                    <label class="tls-toggle">
                                        <input type="checkbox" name="show_search" id="show_search" <?php checked($settings['show_search'], 1); ?>>
                                        <span class="tls-toggle-slider"></span>
                                    </label>
                                </div>
                                <div class="tls-form-row">
                                    <label>Calculator Button</label>
                                    <label class="tls-toggle">
                                        <input type="checkbox" name="show_calculator" id="show_calculator" <?php checked($settings['show_calculator'], 1); ?>>
                                        <span class="tls-toggle-slider"></span>
                                    </label>
                                </div>
                                <div class="tls-form-row">
                                    <label>WhatsApp Link</label>
                                    <label class="tls-toggle">
                                        <input type="checkbox" name="show_whatsapp" id="show_whatsapp" <?php checked($settings['show_whatsapp'], 1); ?>>
                                        <span class="tls-toggle-slider"></span>
                                    </label>
                                </div>
                                <div class="tls-form-row">
                                    <label>Direct Call Link</label>
                                    <label class="tls-toggle">
                                        <input type="checkbox" name="show_call" id="show_call" <?php checked($settings['show_call'], 1); ?>>
                                        <span class="tls-toggle-slider"></span>
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="tls-card">
                            <div class="card-header">
                                <span class="dashicons dashicons-phone"></span>
                                <h2>Contact Overrides</h2>
                            </div>
                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                                <div class="tls-form-row">
                                    <label for="whatsapp_number">WhatsApp (Override)</label>
                                    <input type="text" name="whatsapp_number" id="whatsapp_number" class="tls-input" value="<?php echo esc_attr($settings['whatsapp_number']); ?>" placeholder="e.g. 60123456789">
                                </div>
                                <div class="tls-form-row">
                                    <label for="phone_number">Phone (Override)</label>
                                    <input type="text" name="phone_number" id="phone_number" class="tls-input" value="<?php echo esc_attr($settings['phone_number']); ?>" placeholder="e.g. 60123456789">
                                </div>
                            </div>
                            <p class="description">Leave blank to use the global settings from TLS Dashboard > Contact Settings.</p>
                        </div>

                        <div style="margin-top: 30px; display: flex; gap: 15px;">
                            <button type="submit" class="btn btn-primary" id="save-fab-settings">
                                <span class="dashicons dashicons-saved"></span> Save All Settings
                            </button>
                            <button type="button" class="btn btn-secondary" id="reset-fab-settings">
                                <span class="dashicons dashicons-undo"></span> Reset to Factory Defaults
                            </button>
                            <span class="spinner" style="float: none; margin: 0 10px;"></span>
                        </div>
                    </div>

                    <!-- Side Column: Status & Preview -->
                    <div class="tls-grid-col side-col">
                        <div class="tls-card">
                            <div class="card-header">
                                <span class="dashicons dashicons-visibility"></span>
                                <h2>Appearance Preview</h2>
                            </div>
                            <div class="preview-section">
                                <h4>Global Status</h4>
                                <div class="preview-list">
                                    <?php if ($settings['enabled']): ?>
                                        <span class="badge badge-success">System Active</span>
                                    <?php else: ?>
                                        <span class="badge" style="background:#f1f5f9; color:#64748b;">Disabled</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <hr style="border:0; border-top: 1px solid #f1f5f9; margin: 20px 0;">
                            <div class="preview-section">
                                <h4>Visual Brand</h4>
                                <div class="tls-form-row">
                                    <label>Action Color</label>
                                    <div style="display:flex; gap:10px;">
                                        <input type="color" name="button_color" id="button_color" value="<?php echo esc_attr($settings['button_color']); ?>" style="height:40px; width:60px; border-radius:8px; border:1px solid #ddd; padding:2px;">
                                        <input type="text" class="tls-input" value="<?php echo esc_attr($settings['button_color']); ?>" style="flex:1;" readonly>
                                    </div>
                                </div>
                                <div class="tls-form-row">
                                    <label>Corner Anchor</label>
                                    <select name="button_position" id="button_position" class="tls-input">
                                        <option value="bottom-right" <?php selected($settings['button_position'], 'bottom-right'); ?>>Bottom Right</option>
                                        <option value="bottom-left" <?php selected($settings['button_position'], 'bottom-left'); ?>>Bottom Left</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="tls-card">
                            <div class="card-header">
                                <span class="dashicons dashicons-editor-code"></span>
                                <h2>Developer CSS</h2>
                            </div>
                            <textarea name="custom_css" id="custom_css" rows="6" class="tls-input" style="font-family:monospace; font-size:12px;" placeholder="/* Custom CSS here */"><?php echo esc_textarea($settings['custom_css']); ?></textarea>
                        </div>
                    </div>
                </div>

                <div id="fab-message" style="margin-top: 20px;"></div>
            </form>
        </div>


        <style>
            .tls-fab-settings .tls-card {
                background: white;
                padding: 25px;
                margin-bottom: 20px;
                border: 1px solid #ccd0d4;
                box-shadow: 0 1px 1px rgba(0,0,0,.04);
                border-radius: 8px;
            }

            .tls-card h2 {
                margin-top: 0;
                padding-bottom: 10px;
                border-bottom: 2px solid #667eea;
                color: #333;
            }

            .fab-types-grid {
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
                gap: 20px;
                margin-top: 20px;
            }

            .fab-type-card {
                border: 2px solid #ddd;
                border-radius: 12px;
                padding: 20px;
                cursor: pointer;
                transition: all 0.3s ease;
                position: relative;
            }

            .fab-type-card:hover {
                border-color: #667eea;
                box-shadow: 0 4px 12px rgba(102, 126, 234, 0.2);
            }

            .fab-type-card.active {
                border-color: #667eea;
                background: #f8f9ff;
                box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
            }

            .fab-type-card input[type="radio"] {
                position: absolute;
                top: 15px;
                right: 15px;
            }

            .fab-type-preview {
                margin-bottom: 15px;
            }

            .preview-screen {
                background: #f5f5f5;
                border-radius: 8px;
                height: 180px;
                position: relative;
                overflow: hidden;
                border: 1px solid #ddd;
            }

            .preview-fab {
                position: absolute;
                bottom: 15px;
                right: 15px;
                width: 45px;
                height: 45px;
                border-radius: 50%;
                background: #667eea;
                color: white;
                display: flex;
                align-items: center;
                justify-content: center;
                font-size: 24px;
                font-weight: 300;
            }

            .preview-menu {
                position: absolute;
                bottom: 70px;
                right: 15px;
                display: flex;
                flex-direction: column;
                gap: 8px;
            }

            .preview-item {
                width: 40px;
                height: 40px;
                border-radius: 50%;
                background: white;
                box-shadow: 0 2px 8px rgba(0,0,0,0.15);
                display: flex;
                align-items: center;
                justify-content: center;
                font-size: 16px;
            }

            .preview-footer {
                position: absolute;
                bottom: 0;
                left: 0;
                right: 0;
                background: white;
                padding: 8px;
                display: flex;
                gap: 8px;
                box-shadow: 0 -2px 8px rgba(0,0,0,0.1);
            }

            .preview-footer-btn {
                flex: 1;
                height: 35px;
                background: #667eea;
                color: white;
                border-radius: 6px;
                display: flex;
                align-items: center;
                justify-content: center;
                font-size: 16px;
            }

            .fab-type-card h3 {
                margin: 10px 0;
                font-size: 16px;
                color: #333;
            }

            .fab-type-card p {
                color: #666;
                font-size: 13px;
                margin-bottom: 10px;
            }

            .fab-type-card ul {
                margin: 0;
                padding-left: 20px;
                font-size: 12px;
                color: #666;
            }

            .fab-type-card ul li {
                margin-bottom: 5px;
            }

            .badge-recommended {
                background: #16a34a;
                color: white;
                padding: 2px 8px;
                border-radius: 4px;
                font-size: 11px;
                font-weight: 600;
            }

            /* Toggle Switch */
            .tls-toggle {
                position: relative;
                display: inline-block;
                width: 50px;
                height: 24px;
            }

            .tls-toggle input {
                opacity: 0;
                width: 0;
                height: 0;
            }

            .tls-toggle-slider {
                position: absolute;
                cursor: pointer;
                top: 0;
                left: 0;
                right: 0;
                bottom: 0;
                background-color: #ccc;
                transition: .4s;
                border-radius: 24px;
            }

            .tls-toggle-slider:before {
                position: absolute;
                content: "";
                height: 18px;
                width: 18px;
                left: 3px;
                bottom: 3px;
                background-color: white;
                transition: .4s;
                border-radius: 50%;
            }

            .tls-toggle input:checked + .tls-toggle-slider {
                background-color: #667eea;
            }

            .tls-toggle input:checked + .tls-toggle-slider:before {
                transform: translateX(26px);
            }

            #fab-message {
                margin-top: 15px;
            }

            #fab-message .notice {
                padding: 12px;
                margin: 0;
            }
        </style>

        <script>
        jQuery(document).ready(function($) {
            // Color picker sync
            $('#button_color').on('input', function() {
                $(this).next('input[type="text"]').val($(this).val());
            });

            // Save settings
            $('#tls-fab-form').on('submit', function(e) {
                e.preventDefault();

                const $form = $(this);
                const $btn = $('#save-fab-settings');
                const $spinner = $btn.siblings('.spinner');
                const $message = $('#fab-message');

                $btn.prop('disabled', true);
                $spinner.addClass('is-active');
                $message.html('');

                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: $form.serialize() + '&action=tls_save_fab_settings&nonce=<?php echo wp_create_nonce('tls_fab_nonce'); ?>',
                    success: function(response) {
                        $btn.prop('disabled', false);
                        $spinner.removeClass('is-active');

                        if (response.success) {
                            $message.html('<div class="notice notice-success is-dismissible"><p>' + response.data.message + '</p></div>');
                        } else {
                            $message.html('<div class="notice notice-error is-dismissible"><p>' + response.data.message + '</p></div>');
                        }

                        $('html, body').animate({ scrollTop: $message.offset().top - 100 }, 500);
                    }
                });
            });

            // Reset settings
            $('#reset-fab-settings').on('click', function() {
                if (!confirm('Are you sure you want to reset all FAB settings to defaults? This cannot be undone.')) {
                    return;
                }

                const $btn = $(this);
                const $spinner = $btn.siblings('.spinner');
                const $message = $('#fab-message');

                $btn.prop('disabled', true);
                $spinner.addClass('is-active');
                $message.html('');

                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'tls_reset_fab_settings',
                        nonce: '<?php echo wp_create_nonce('tls_fab_nonce'); ?>'
                    },
                    success: function(response) {
                        $btn.prop('disabled', false);
                        $spinner.removeClass('is-active');

                        if (response.success) {
                            $message.html('<div class="notice notice-success is-dismissible"><p>' + response.data.message + '</p></div>');
                            setTimeout(() => location.reload(), 1500);
                        }
                    }
                });
            });
        });
        </script>
        <?php
    }

    /**
     * Render FAB on frontend
     */
    public function render_fab() {
        $settings = $this->get_settings();

        // Don't show if disabled or in admin
        if (!$settings['enabled'] || is_admin()) {
            return;
        }

        // Include FAB template based on type
        include locate_template('template-parts/fab-menu.php');
    }
}

// NOTE: FAB System is initialized in functions.php
// Do NOT initialize here to avoid duplicate instances
