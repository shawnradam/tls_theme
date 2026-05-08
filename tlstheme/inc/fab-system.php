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
        <div class="wrap tls-fab-settings">
            <h1>🎯 FAB Menu Settings</h1>
            <p class="description">Manage your Floating Action Button and sticky footer settings. Choose from 4 different FAB types and customize appearance.</p>

            <form id="tls-fab-form" method="post">
                <?php wp_nonce_field('tls_fab_nonce', 'tls_fab_nonce_field'); ?>

                <!-- FAB Type Selection -->
                <div class="tls-card" style="margin-top: 30px;">
                    <h2>Choose FAB Type</h2>
                    <p class="description">Select the layout style for your floating action button</p>

                    <div class="fab-types-grid">
                        <!-- Type 1 -->
                        <label class="fab-type-card <?php echo $settings['fab_type'] === 'type1' ? 'active' : ''; ?>">
                            <input type="radio" name="fab_type" value="type1" <?php checked($settings['fab_type'], 'type1'); ?>>
                            <div class="fab-type-preview">
                                <div class="preview-screen">
                                    <div class="preview-fab">+</div>
                                    <div class="preview-menu">
                                        <div class="preview-item">◈</div>
                                        <div class="preview-item"><i class="material-icons">add</i></div>
                                        <div class="preview-item">◉</div>
                                        <div class="preview-item"><i class="material-icons">circle</i></div>
                                    </div>
                                </div>
                            </div>
                            <h3>Type 1: Full FAB</h3>
                            <p>All buttons in FAB menu. Clean and organized.</p>
                            <ul>
                                <li><i class="material-icons">check</i> All 4 buttons in FAB</li>
                                <li><i class="material-icons">check</i> No sticky footer</li>
                                <li><i class="material-icons">check</i> Desktop + Mobile</li>
                            </ul>
                        </label>

                        <!-- Type 2 -->
                        <label class="fab-type-card <?php echo $settings['fab_type'] === 'type2' ? 'active' : ''; ?>">
                            <input type="radio" name="fab_type" value="type2" <?php checked($settings['fab_type'], 'type2'); ?>>
                            <div class="fab-type-preview">
                                <div class="preview-screen">
                                    <div class="preview-fab">+</div>
                                    <div class="preview-menu">
                                        <div class="preview-item">◈</div>
                                        <div class="preview-item"><i class="material-icons">add</i></div>
                                    </div>
                                    <div class="preview-footer">
                                        <div class="preview-footer-btn">◉</div>
                                        <div class="preview-footer-btn"><i class="material-icons">circle</i></div>
                                    </div>
                                </div>
                            </div>
                            <h3>Type 2: FAB + Sticky Footer</h3>
                            <p>Tools in FAB, Communication in footer. <span class="badge-recommended">RECOMMENDED</span></p>
                            <ul>
                                <li><i class="material-icons">check</i> Search + Calculator in FAB</li>
                                <li><i class="material-icons">check</i> WhatsApp + Call in footer</li>
                                <li><i class="material-icons">check</i> Mobile optimized</li>
                            </ul>
                        </label>

                        <!-- Type 3 -->
                        <label class="fab-type-card <?php echo $settings['fab_type'] === 'type3' ? 'active' : ''; ?>">
                            <input type="radio" name="fab_type" value="type3" <?php checked($settings['fab_type'], 'type3'); ?>>
                            <div class="fab-type-preview">
                                <div class="preview-screen">
                                    <div class="preview-fab">+</div>
                                    <div class="preview-menu">
                                        <div class="preview-item">◈</div>
                                        <div class="preview-item"><i class="material-icons">add</i></div>
                                    </div>
                                </div>
                            </div>
                            <h3>Type 3: Minimal FAB</h3>
                            <p>Only essential tools. Perfect for minimal design.</p>
                            <ul>
                                <li><i class="material-icons">check</i> Search + Calculator only</li>
                                <li><i class="material-icons">check</i> No footer</li>
                                <li><i class="material-icons">check</i> Ultra clean</li>
                            </ul>
                        </label>

                        <!-- Type 4 -->
                        <label class="fab-type-card <?php echo $settings['fab_type'] === 'type4' ? 'active' : ''; ?>">
                            <input type="radio" name="fab_type" value="type4" <?php checked($settings['fab_type'], 'type4'); ?>>
                            <div class="fab-type-preview">
                                <div class="preview-screen">
                                    <div class="preview-footer">
                                        <div class="preview-footer-btn">◉</div>
                                        <div class="preview-footer-btn"><i class="material-icons">circle</i></div>
                                        <div class="preview-footer-btn">◈</div>
                                        <div class="preview-footer-btn"><i class="material-icons">add</i></div>
                                    </div>
                                </div>
                            </div>
                            <h3>Type 4: Sticky Footer Only</h3>
                            <p>All buttons in footer bar. Traditional approach.</p>
                            <ul>
                                <li><i class="material-icons">check</i> All buttons in footer</li>
                                <li><i class="material-icons">check</i> No FAB</li>
                                <li><i class="material-icons">check</i> Simple and direct</li>
                            </ul>
                        </label>
                    </div>
                </div>

                <!-- Enable/Disable -->
                <div class="tls-card">
                    <h2>General Settings</h2>
                    <table class="form-table">
                        <tr>
                            <th><label for="enabled">Enable FAB Menu</label></th>
                            <td>
                                <label class="tls-toggle">
                                    <input type="checkbox" name="enabled" id="enabled" <?php checked($settings['enabled'], 1); ?>>
                                    <span class="tls-toggle-slider"></span>
                                </label>
                                <p class="description">Turn on/off the entire FAB system</p>
                            </td>
                        </tr>
                    </table>
                </div>

                <!-- Button Visibility -->
                <div class="tls-card">
                    <h2>Button Visibility</h2>
                    <p class="description">Choose which buttons to show in FAB menu</p>
                    <table class="form-table">
                        <tr>
                            <th><label for="show_search">◈ Search Button</label></th>
                            <td>
                                <label class="tls-toggle">
                                    <input type="checkbox" name="show_search" id="show_search" <?php checked($settings['show_search'], 1); ?>>
                                    <span class="tls-toggle-slider"></span>
                                </label>
                            </td>
                        </tr>
                        <tr>
                            <th><label for="show_calculator"><i class="material-icons">add</i> Calculator Button</label></th>
                            <td>
                                <label class="tls-toggle">
                                    <input type="checkbox" name="show_calculator" id="show_calculator" <?php checked($settings['show_calculator'], 1); ?>>
                                    <span class="tls-toggle-slider"></span>
                                </label>
                            </td>
                        </tr>
                        <tr>
                            <th><label for="show_whatsapp">◉ WhatsApp Button</label></th>
                            <td>
                                <label class="tls-toggle">
                                    <input type="checkbox" name="show_whatsapp" id="show_whatsapp" <?php checked($settings['show_whatsapp'], 1); ?>>
                                    <span class="tls-toggle-slider"></span>
                                </label>
                            </td>
                        </tr>
                        <tr>
                            <th><label for="show_call"><i class="material-icons">circle</i> Call Button</label></th>
                            <td>
                                <label class="tls-toggle">
                                    <input type="checkbox" name="show_call" id="show_call" <?php checked($settings['show_call'], 1); ?>>
                                    <span class="tls-toggle-slider"></span>
                                </label>
                            </td>
                        </tr>
                    </table>
                </div>

                <!-- Contact Information -->
                <div class="tls-card">
                    <h2>Contact Information</h2>
                    <table class="form-table">
                        <tr>
                            <th><label for="whatsapp_number">WhatsApp Number</label></th>
                            <td>
                                <input type="text" name="whatsapp_number" id="whatsapp_number" class="regular-text" value="<?php echo esc_attr($settings['whatsapp_number'] ?: get_theme_mod('whatsapp_number', '')); ?>" placeholder="60123456789">
                                <p class="description">Format: 60123456789 (without +)</p>
                            </td>
                        </tr>
                        <tr>
                            <th><label for="phone_number">Phone Number</label></th>
                            <td>
                                <input type="text" name="phone_number" id="phone_number" class="regular-text" value="<?php echo esc_attr($settings['phone_number'] ?: get_theme_mod('phone_number', '')); ?>" placeholder="60123456789">
                                <p class="description">Format: 60123456789 (without +)</p>
                            </td>
                        </tr>
                    </table>
                </div>

                <!-- Appearance -->
                <div class="tls-card">
                    <h2>Appearance</h2>
                    <table class="form-table">
                        <tr>
                            <th><label for="button_color">Button Color</label></th>
                            <td>
                                <input type="color" name="button_color" id="button_color" value="<?php echo esc_attr($settings['button_color']); ?>">
                                <input type="text" class="regular-text" value="<?php echo esc_attr($settings['button_color']); ?>" readonly>
                                <p class="description">Main color for FAB button</p>
                            </td>
                        </tr>
                        <tr>
                            <th><label for="button_position">Button Position</label></th>
                            <td>
                                <select name="button_position" id="button_position">
                                    <option value="bottom-right" <?php selected($settings['button_position'], 'bottom-right'); ?>>Bottom Right</option>
                                    <option value="bottom-left" <?php selected($settings['button_position'], 'bottom-left'); ?>>Bottom Left</option>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <th><label for="show_sticky_footer">Show Sticky Footer</label></th>
                            <td>
                                <label class="tls-toggle">
                                    <input type="checkbox" name="show_sticky_footer" id="show_sticky_footer" <?php checked($settings['show_sticky_footer'], 1); ?>>
                                    <span class="tls-toggle-slider"></span>
                                </label>
                                <p class="description">Show WhatsApp/Call buttons in sticky footer (Type 2 & 4)</p>
                            </td>
                        </tr>
                    </table>
                </div>

                <!-- Custom CSS -->
                <div class="tls-card">
                    <h2>Custom CSS</h2>
                    <p class="description">Add custom CSS to override FAB styles (advanced users only)</p>
                    <textarea name="custom_css" id="custom_css" rows="8" class="large-text code"><?php echo esc_textarea($settings['custom_css']); ?></textarea>
                </div>

                <!-- Save Buttons -->
                <p class="submit">
                    <button type="submit" class="button button-primary button-large" id="save-fab-settings">
                        💾 Save Settings
                    </button>
                    <button type="button" class="button button-secondary" id="reset-fab-settings">
                        🔄 Reset to Defaults
                    </button>
                    <span class="spinner" style="float: none; margin: 0 10px;"></span>
                </p>

                <div id="fab-message"></div>
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
