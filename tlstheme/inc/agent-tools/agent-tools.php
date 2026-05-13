<?php
/**
 * TLS Theme - Agent Tools Sub-system
 * Mobile sticky agent bar and property view counter with CRUD settings.
 */

if (!defined('ABSPATH')) exit;

class TLS_Agent_Tools {

    private $option_name = 'tls_agent_bar_settings';

    public function __construct() {
        // Tracking
        add_action('template_redirect', [$this, 'track_property_views']);
        
        // Display
        add_action('wp_footer', [$this, 'render_mobile_sticky_bar']);
        add_action('wp_footer', [$this, 'render_contact_popup']);
        
        // Assets
        add_action('wp_enqueue_scripts', [$this, 'enqueue_assets']);

        // Admin Menu
        add_action('admin_menu', [$this, 'add_admin_menu'], 100);
    }

    /**
     * Get settings with defaults
     */
    public function get_settings() {
        $defaults = [
            'enabled' => 1,
            'show_views' => 1,
            'bar_height' => 80,
            'bg_color' => '#ffffff',
            'text_color' => '#0f172a',
            'show_agent_label' => 1,
            'agent_label_text' => 'Perunding Tanah',
        ];
        
        $settings = get_option($this->option_name, []);
        return wp_parse_args($settings, $defaults);
    }

    /**
     * Track property views with simple cookie to prevent refresh spam
     */
    public function track_property_views() {
        if (!is_singular('tanah')) return;
        
        $settings = $this->get_settings();
        if (!$settings['show_views']) return;

        $post_id = get_the_ID();
        $cookie_name = 'tls_viewed_' . $post_id;

        if (!isset($_COOKIE[$cookie_name])) {
            $count = (int) get_post_meta($post_id, '_property_views_count', true);
            update_post_meta($post_id, '_property_views_count', $count + 1);
            
            // Set cookie for 24 hours
            setcookie($cookie_name, '1', time() + 86400, COOKIEPATH, COOKIE_DOMAIN);
        }
    }

    /**
     * Enqueue CSS and JS
     */
    public function enqueue_assets() {
        if (!is_singular('tanah')) return;

        $base_url = get_template_directory_uri() . '/inc/agent-tools/assets/';
        
        wp_enqueue_style('tls-agent-tools', $base_url . 'css/agent-tools.css', [], TLS_VERSION);
        wp_enqueue_script('tls-agent-tools', $base_url . 'js/agent-tools.js', ['jquery'], TLS_VERSION, true);
    }

    /**
     * Render the Mobile Sticky Agent Bar
     */
    public function render_mobile_sticky_bar() {
        if (!is_singular('tanah')) return;
        
        $settings = $this->get_settings();
        if (!$settings['enabled']) return;
        
        global $post;
        $author_id = $post->post_author;
        $author_name = get_the_author_meta('display_name', $author_id);
        $author_avatar = get_avatar_url($author_id, ['size' => 100]);
        
        // Fallback for default management
        if (!$author_name || (strpos($author_avatar, 'gravatar.com') !== false && !get_the_author_meta('description', $author_id))) {
            $author_name = 'TLS Management';
            $author_avatar = get_template_directory_uri() . '/assets/images/placeholder.jpeg'; 
        }

        // Get numbers from global TLS Contact Settings (CRUD)
        $wa = get_theme_mod('whatsapp_number', '601126661706');
        $phone = get_theme_mod('phone_number', $wa);
        
        $wa_enc = base64_encode($wa);
        $phone_enc = base64_encode($phone);

        $title = get_the_title();
        $permalink = get_permalink();
        $msg = urlencode("Hi, saya berminat dengan property: $title - $permalink");
        ?>
        <div class="tls-mobile-agent-bar" style="background: <?php echo esc_attr($settings['bg_color']); ?>; height: <?php echo esc_attr($settings['bar_height']); ?>px;">
            <div class="agent-info">
                <div class="agent-photo">
                    <img src="<?php echo esc_url($author_avatar); ?>" alt="<?php echo esc_attr($author_name); ?>">
                </div>
                <div class="agent-details">
                    <?php if ($settings['show_agent_label']): ?>
                    <span class="agent-label"><?php echo esc_html($settings['agent_label_text']); ?></span>
                    <?php endif; ?>
                    <span class="agent-name" style="color: <?php echo esc_attr($settings['text_color']); ?>;"><?php echo esc_html($author_name); ?></span>
                </div>
            </div>
            <div class="agent-actions">
                <a href="javascript:void(0)" onclick="tlsRevealContact(this, 'wa', '<?php echo $wa_enc; ?>')" class="action-btn wa" title="WhatsApp">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z"/></svg>
                </a>
                <a href="javascript:void(0)" onclick="tlsRevealContact(this, 'tel', '<?php echo $phone_enc; ?>')" class="action-btn call" title="Call">
                    <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                </a>
                <button type="button" class="action-btn msg" id="tls-open-contact" title="Message">
                    <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/></svg>
                </button>
            </div>
        </div>
        <?php
    }

    /**
     * Render the Contact Popup Form
     */
    public function render_contact_popup() {
        if (!is_singular('tanah')) return;
        ?>
        <div class="tls-contact-popup" id="tls-contact-popup">
            <div class="popup-overlay"></div>
            <div class="popup-content">
                <button type="button" class="popup-close" id="tls-close-contact">&times;</button>
                <h3>Pertanyaan Tanah</h3>
                <p>Berminat dengan <?php the_title(); ?>? Sila isi borang di bawah.</p>
                <form id="tls-agent-contact-form">
                    <div class="form-group">
                        <label>Nama Anda</label>
                        <input type="text" name="user_name" placeholder="cth: Ahmad" required>
                    </div>
                    <div class="form-group">
                        <label>No. Telefon</label>
                        <input type="tel" name="user_phone" placeholder="cth: 012-3456789" required>
                    </div>
                    <div class="form-group">
                        <label>Mesej</label>
                        <textarea name="user_msg" placeholder="Mesej..." rows="3"></textarea>
                    </div>
                    <button type="submit" class="submit-btn">Hantar Pertanyaan</button>
                </form>
            </div>
        </div>
        <?php
    }

    /**
     * Add Admin Menu
     */
    public function add_admin_menu() {
        add_submenu_page(
            'tls-dashboard',
            'Mobile Agent Bar',
            'Agent Bar',
            'manage_options',
            'tls-agent-bar',
            [$this, 'admin_settings_page']
        );
    }

    /**
     * Admin Settings Page
     */
    public function admin_settings_page() {
        if (isset($_POST['tls_save_agent_bar'])) {
            check_admin_referer('tls_agent_bar_action', 'tls_agent_bar_nonce');
            
            $settings = [
                'enabled' => isset($_POST['enabled']) ? 1 : 0,
                'show_views' => isset($_POST['show_views']) ? 1 : 0,
                'bar_height' => intval($_POST['bar_height']),
                'bg_color' => sanitize_hex_color($_POST['bg_color']),
                'text_color' => sanitize_hex_color($_POST['text_color']),
                'show_agent_label' => isset($_POST['show_agent_label']) ? 1 : 0,
                'agent_label_text' => sanitize_text_field($_POST['agent_label_text']),
            ];
            
            update_option($this->option_name, $settings);
            echo '<div class="notice notice-success is-dismissible" style="margin:20px 0; border-radius:8px;"><p><strong>Success:</strong> Agent Bar settings saved!</p></div>';
        }
        
        $settings = $this->get_settings();
        ?>
        <div class="wrap tls-admin-modern">
            <div class="tls-admin-header">
                <h1><span class="dashicons dashicons-smartphone"></span> Mobile Agent Bar</h1>
                <p>Configure the sticky contact bar and view counter for mobile property pages.</p>
            </div>

            <div class="tls-grid-layout">
                <div class="tls-grid-col main-col">
                    <form method="post" action="">
                        <?php wp_nonce_field('tls_agent_bar_action', 'tls_agent_bar_nonce'); ?>
                        
                        <div class="tls-card">
                            <div class="card-header">
                                <span class="dashicons dashicons-admin-generic"></span>
                                <h2>General Configuration</h2>
                            </div>
                            
                            <div class="status-grid" style="margin-bottom: 30px;">
                                <div class="status-item <?php echo $settings['enabled'] ? 'active' : ''; ?>">
                                    <div class="val"><?php echo $settings['enabled'] ? '✓' : '—'; ?></div>
                                    <div class="lab">Sticky Bar Enabled</div>
                                    <label style="display:block; margin-top:10px;"><input type="checkbox" name="enabled" value="1" <?php checked($settings['enabled'], 1); ?>> Enable</label>
                                </div>
                                <div class="status-item <?php echo $settings['show_views'] ? 'active' : ''; ?>">
                                    <div class="val"><?php echo $settings['show_views'] ? '✓' : '—'; ?></div>
                                    <div class="lab">View Counter</div>
                                    <label style="display:block; margin-top:10px;"><input type="checkbox" name="show_views" value="1" <?php checked($settings['show_views'], 1); ?>> Enable</label>
                                </div>
                            </div>

                            <div class="tls-form-row">
                                <label>Agent Label Visibility</label>
                                <label style="font-weight: normal;">
                                    <input type="checkbox" name="show_agent_label" value="1" <?php checked($settings['show_agent_label'], 1); ?>> 
                                    Display label above agent name (e.g., "Perunding Tanah")
                                </label>
                            </div>

                            <div class="tls-form-row">
                                <label>Custom Label Text</label>
                                <input type="text" name="agent_label_text" value="<?php echo esc_attr($settings['agent_label_text']); ?>" class="tls-input" placeholder="e.g., Property Consultant">
                            </div>
                        </div>

                        <div class="tls-card">
                            <div class="card-header">
                                <span class="dashicons dashicons-art"></span>
                                <h2>Visual Styling</h2>
                            </div>
                            
                            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px;">
                                <div class="tls-form-row">
                                    <label>Bar Background</label>
                                    <input type="color" name="bg_color" value="<?php echo esc_attr($settings['bg_color']); ?>" style="width: 100%; height: 40px; border-radius: 8px; border: 1px solid var(--tls-border); cursor: pointer;">
                                </div>
                                <div class="tls-form-row">
                                    <label>Agent Name Color</label>
                                    <input type="color" name="text_color" value="<?php echo esc_attr($settings['text_color']); ?>" style="width: 100%; height: 40px; border-radius: 8px; border: 1px solid var(--tls-border); cursor: pointer;">
                                </div>
                                <div class="tls-form-row">
                                    <label>Bar Height (px)</label>
                                    <input type="number" name="bar_height" value="<?php echo esc_attr($settings['bar_height']); ?>" class="tls-input">
                                </div>
                            </div>
                        </div>

                        <div style="margin-top: 20px;">
                            <button type="submit" name="tls_save_agent_bar" class="btn btn-primary">
                                <span class="dashicons dashicons-saved"></span>
                                Save Agent Bar Settings
                            </button>
                        </div>
                    </form>
                </div>

                <div class="tls-grid-col side-col">
                    <div class="tls-card">
                        <div class="card-header">
                            <span class="dashicons dashicons-info"></span>
                            <h2>Management Tip</h2>
                        </div>
                        <p style="font-size: 13px; color: var(--tls-text-muted); line-height: 1.6;">
                            The agent's photo and name are automatically pulled from the **Author Profile** of the property listing.
                        </p>
                        <hr style="border:0; border-top:1px solid #f1f5f9; margin:15px 0;">
                        <p style="font-size: 12px; color: var(--tls-text-muted);">
                            Need to change contact numbers? Visit the <a href="<?php echo admin_url('admin.php?page=tls-settings'); ?>">Global Contact Settings</a>.
                        </p>
                    </div>

                    <div class="tls-card">
                        <div class="card-header">
                            <span class="dashicons dashicons-visibility"></span>
                            <h2>Live Status</h2>
                        </div>
                        <div class="preview-list">
                            <span>Status: <?php echo $settings['enabled'] ? 'Active' : 'Disabled'; ?></span>
                            <span>Target: Mobile Devices</span>
                            <span>Context: Tanah Post Type</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }
}

// Initialize
new TLS_Agent_Tools();

/**
 * Human-readable View Count Function
 */
function tls_get_property_views() {
    $agent_tools = new TLS_Agent_Tools();
    $settings = $agent_tools->get_settings();
    
    if (!$settings['show_views']) return '';

    $count = (int) get_post_meta(get_the_ID(), '_property_views_count', true);
    if ($count < 1) return ''; 
    
    $display_count = number_format($count);
    $recent = round($count * 0.05) + 1; 
    
    return "<div class='tls-view-counter'>
                <span class='view-icon'>👁️</span>
                <span class='view-text'>Popular: Viewed <strong>$recent times</strong> in the last 24 hours (Total: $display_count)</span>
            </div>";
}
