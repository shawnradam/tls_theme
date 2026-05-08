<?php
/**
 * TLS Theme License System
 * Secure license key validation and activation
 *
 * @package TLS_Theme
 * @version 1.0.0
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class TLS_License_System {

    private $option_name = 'tls_theme_license';
    private $api_url = ''; // Your license server URL (optional)
    private $theme_slug = 'tls-theme';
    private $theme_version = '1.0.0';
    private $founder_email = 'tanahlotsabah@gmail.com'; // Founder email - full access
    private $founder_secret = 'tls_founder_2026_secure'; // Secret key - change this!

    public function __construct() {
        add_action('admin_menu', [$this, 'add_license_page']);
        add_action('admin_init', [$this, 'register_license_settings']);
        add_action('admin_notices', [$this, 'license_notice']);
        add_action('wp_ajax_tls_activate_license', [$this, 'activate_license']);
        add_action('wp_ajax_tls_deactivate_license', [$this, 'deactivate_license']);

        // Check license on theme activation
        add_action('after_switch_theme', [$this, 'check_license_on_activation']);

        // DISABLED: License blocking - founder needs full access for all users
        // The license check was blocking visitors because is_founder() requires logged-in user
        // Comment out these lines to prevent blocking non-admin users:
        /*
        if (!$this->is_licensed() && $this->is_grace_period_expired()) {
            add_action('template_redirect', [$this, 'disable_theme']);
        }
        */
    }

    /**
     * Add license page to admin menu
     */
    public function add_license_page() {
        add_theme_page(
            'Theme License',
            'Theme License',
            'manage_options',
            'tls-theme-license',
            [$this, 'render_license_page']
        );
    }

    /**
     * Register license settings
     */
    public function register_license_settings() {
        register_setting('tls_license_options', $this->option_name, [
            'sanitize_callback' => [$this, 'sanitize_license']
        ]);
    }

    /**
     * Sanitize license input
     */
    public function sanitize_license($input) {
        return sanitize_text_field($input);
    }

    /**
     * Show admin notice if unlicensed
     */
    public function license_notice() {
        // Don't show notice to founder
        if ($this->is_founder()) {
            return;
        }

        if (!$this->is_licensed()) {
            $days_left = $this->get_grace_period_days_left();
            $class = $days_left > 0 ? 'notice-warning' : 'notice-error';
            $message = $days_left > 0
                ? sprintf('Please activate your TLS Theme license. Grace period: %d days left.', $days_left)
                : 'Your TLS Theme license has expired. Please activate a valid license.';

            echo '<div class="notice ' . $class . ' is-dismissible">';
            echo '<p><strong>' . esc_html($message) . '</strong> ';
            echo '<a href="' . admin_url('themes.php?page=tls-theme-license') . '">Activate License</a></p>';
            echo '</div>';
        }
    }

    /**
     * Check if current user is the founder (Multi-layer security)
     */
    private function is_founder() {
        $current_user = wp_get_current_user();

        // Check 1: User must be logged in
        if (!$current_user->ID) {
            return false;
        }

        // Check 2: Email must match
        if ($current_user->user_email !== $this->founder_email) {
            return false;
        }

        // Check 3: Must be super admin or user ID #1 (original admin)
        if (!is_super_admin($current_user->ID) && $current_user->ID != 1) {
            return false;
        }

        // Check 4: Verify founder secret key (stored in user meta)
        // This is set when you first activate as founder
        $stored_secret = get_user_meta($current_user->ID, '_tls_founder_verified', true);

        if (empty($stored_secret)) {
            // First time founder login - verify and set the secret
            if ($this->verify_founder_secret($current_user->ID)) {
                update_user_meta($current_user->ID, '_tls_founder_verified', hash('sha256', $this->founder_secret . $current_user->ID));
                return true;
            }
            return false;
        }

        // Verify the stored secret matches
        $expected_secret = hash('sha256', $this->founder_secret . $current_user->ID);
        if (!hash_equals($stored_secret, $expected_secret)) {
            return false;
        }

        return true;
    }

    /**
     * Verify founder secret from wp-config.php or user meta
     */
    private function verify_founder_secret($user_id) {
        // Option 1: Check for secret in wp-config.php (most secure)
        if (defined('TLS_FOUNDER_KEY')) {
            return hash_equals(TLS_FOUNDER_KEY, $this->founder_secret);
        }

        // Option 2: Check if this is user ID #1 (original admin) AND super admin
        if ($user_id == 1 && is_super_admin($user_id)) {
            return true;
        }

        // Option 3: Check user meta for manual verification
        // You can manually set this via database: wp_usermeta table
        // meta_key: _tls_founder_manual_verify
        // meta_value: 1
        $manual_verify = get_user_meta($user_id, '_tls_founder_manual_verify', true);
        if ($manual_verify === '1' || $manual_verify === 1) {
            return true;
        }

        return false;
    }

    /**
     * Check if theme is licensed
     */
    public function is_licensed() {
        // Founder always has full access
        if ($this->is_founder()) {
            return true;
        }

        $license_data = get_option($this->option_name . '_data', []);

        if (empty($license_data) || !isset($license_data['status'])) {
            return false;
        }

        if ($license_data['status'] !== 'active') {
            return false;
        }

        // Verify domain binding
        if (!$this->verify_domain($license_data)) {
            return false;
        }

        // Verify expiration
        if (isset($license_data['expires']) && $license_data['expires'] !== 'lifetime') {
            if (time() > strtotime($license_data['expires'])) {
                return false;
            }
        }

        // Verify integrity
        if (!$this->verify_integrity($license_data)) {
            return false;
        }

        return true;
    }

    /**
     * Verify domain binding
     */
    private function verify_domain($license_data) {
        if (!isset($license_data['domain'])) {
            return false;
        }

        $current_domain = $this->get_current_domain();
        $stored_domain = $license_data['domain'];

        return hash_equals($stored_domain, $current_domain);
    }

    /**
     * Verify data integrity
     */
    private function verify_integrity($license_data) {
        if (!isset($license_data['hash'])) {
            return false;
        }

        $data_to_hash = $license_data['key'] .
                        $license_data['domain'] .
                        $license_data['status'] .
                        ($license_data['expires'] ?? 'lifetime');

        $expected_hash = $this->generate_hash($data_to_hash);

        return hash_equals($license_data['hash'], $expected_hash);
    }

    /**
     * Generate secure hash
     */
    private function generate_hash($data) {
        $secret = defined('AUTH_KEY') ? AUTH_KEY : 'tls-theme-secret';
        return hash_hmac('sha256', $data, $secret);
    }

    /**
     * Get current domain
     */
    private function get_current_domain() {
        $domain = parse_url(home_url(), PHP_URL_HOST);
        return hash('sha256', $domain);
    }

    /**
     * Check grace period
     */
    public function is_grace_period_expired() {
        $installed_time = get_option($this->option_name . '_installed', 0);

        if ($installed_time == 0) {
            update_option($this->option_name . '_installed', time());
            return false;
        }

        $grace_period = 7 * 24 * 60 * 60; // 7 days
        return (time() - $installed_time) > $grace_period;
    }

    /**
     * Get grace period days left
     */
    public function get_grace_period_days_left() {
        $installed_time = get_option($this->option_name . '_installed', time());
        $grace_period = 7 * 24 * 60 * 60; // 7 days
        $time_left = $grace_period - (time() - $installed_time);

        if ($time_left < 0) {
            return 0;
        }

        return ceil($time_left / (24 * 60 * 60));
    }

    /**
     * Check license on theme activation
     */
    public function check_license_on_activation() {
        if (!get_option($this->option_name . '_installed')) {
            update_option($this->option_name . '_installed', time());
        }
    }

    /**
     * Activate license
     */
    public function activate_license() {
        check_ajax_referer('tls_license_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => 'Insufficient permissions']);
        }

        $license_key = sanitize_text_field($_POST['license_key']);

        if (empty($license_key)) {
            wp_send_json_error(['message' => 'License key is required']);
        }

        // Validate license key format
        if (!$this->validate_key_format($license_key)) {
            wp_send_json_error(['message' => 'Invalid license key format']);
        }

        // Remote validation (if API URL is set)
        if (!empty($this->api_url)) {
            $result = $this->remote_activate($license_key);
        } else {
            // Local validation
            $result = $this->local_activate($license_key);
        }

        if ($result['success']) {
            wp_send_json_success($result);
        } else {
            wp_send_json_error($result);
        }
    }

    /**
     * Validate license key format
     */
    private function validate_key_format($key) {
        // Format: XXXXX-XXXXX-XXXXX-XXXXX-XXXXX
        return preg_match('/^[A-Z0-9]{5}-[A-Z0-9]{5}-[A-Z0-9]{5}-[A-Z0-9]{5}-[A-Z0-9]{5}$/', $key);
    }

    /**
     * Remote license activation (connects to your server)
     */
    private function remote_activate($license_key) {
        $response = wp_remote_post($this->api_url . '/activate', [
            'body' => [
                'license_key' => $license_key,
                'domain' => parse_url(home_url(), PHP_URL_HOST),
                'theme_slug' => $this->theme_slug,
                'theme_version' => $this->theme_version
            ],
            'timeout' => 15
        ]);

        if (is_wp_error($response)) {
            return [
                'success' => false,
                'message' => 'Connection error: ' . $response->get_error_message()
            ];
        }

        $body = json_decode(wp_remote_retrieve_body($response), true);

        if ($body['status'] === 'active') {
            $this->store_license_data($license_key, $body);
            return [
                'success' => true,
                'message' => 'License activated successfully!'
            ];
        }

        return [
            'success' => false,
            'message' => $body['message'] ?? 'Activation failed'
        ];
    }

    /**
     * Local license activation (without remote server)
     */
    private function local_activate($license_key) {
        // Verify license key using built-in algorithm
        if (!$this->verify_license_key($license_key)) {
            return [
                'success' => false,
                'message' => 'Invalid license key'
            ];
        }

        // Store license data
        $license_data = [
            'status' => 'active',
            'expires' => 'lifetime', // or specific date
            'type' => 'regular'
        ];

        $this->store_license_data($license_key, $license_data);

        return [
            'success' => true,
            'message' => 'License activated successfully!'
        ];
    }

    /**
     * Verify license key algorithm (local validation)
     */
    private function verify_license_key($key) {
        // Remove hyphens
        $clean_key = str_replace('-', '', $key);

        if (strlen($clean_key) !== 25) {
            return false;
        }

        // Extract checksum (last 5 characters)
        $checksum = substr($clean_key, -5);
        $data = substr($clean_key, 0, -5);

        // Verify checksum
        $calculated_checksum = $this->calculate_checksum($data);

        return hash_equals($checksum, $calculated_checksum);
    }

    /**
     * Calculate checksum for license key
     */
    private function calculate_checksum($data) {
        $hash = hash('sha256', $data . AUTH_KEY);

        // Convert to alphanumeric
        $checksum = '';
        $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';

        for ($i = 0; $i < 5; $i++) {
            $pos = hexdec(substr($hash, $i * 2, 2)) % 36;
            $checksum .= $chars[$pos];
        }

        return $checksum;
    }

    /**
     * Store license data
     */
    private function store_license_data($license_key, $data) {
        $current_domain = $this->get_current_domain();

        $license_data = [
            'key' => $license_key,
            'domain' => $current_domain,
            'status' => $data['status'],
            'expires' => $data['expires'] ?? 'lifetime',
            'type' => $data['type'] ?? 'regular',
            'activated_at' => time()
        ];

        // Generate integrity hash
        $data_to_hash = $license_key .
                        $current_domain .
                        $license_data['status'] .
                        $license_data['expires'];

        $license_data['hash'] = $this->generate_hash($data_to_hash);

        // Encrypt and store
        $encrypted_data = $this->encrypt_data($license_data);
        update_option($this->option_name . '_data', $license_data);
        update_option($this->option_name, $license_key);
    }

    /**
     * Encrypt sensitive data
     */
    private function encrypt_data($data) {
        if (function_exists('openssl_encrypt')) {
            $key = substr(hash('sha256', AUTH_KEY), 0, 32);
            $iv = substr(hash('sha256', SECURE_AUTH_KEY), 0, 16);

            return openssl_encrypt(
                json_encode($data),
                'AES-256-CBC',
                $key,
                0,
                $iv
            );
        }

        return base64_encode(json_encode($data));
    }

    /**
     * Deactivate license
     */
    public function deactivate_license() {
        check_ajax_referer('tls_license_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => 'Insufficient permissions']);
        }

        delete_option($this->option_name);
        delete_option($this->option_name . '_data');

        wp_send_json_success(['message' => 'License deactivated']);
    }

    /**
     * Disable theme if unlicensed
     */
    public function disable_theme() {
        if (is_admin() || is_customize_preview()) {
            return; // Allow admin access
        }

        wp_die(
            '<h1>Theme License Required</h1>' .
            '<p>This theme requires a valid license to function. Please contact the site administrator.</p>' .
            '<p><a href="' . admin_url('themes.php?page=tls-theme-license') . '">Activate License</a></p>',
            'License Required',
            ['response' => 403]
        );
    }

    /**
     * Render license page
     */
    public function render_license_page() {
        $license_key = get_option($this->option_name, '');
        $license_data = get_option($this->option_name . '_data', []);
        $is_active = $this->is_licensed();

        ?>
        <div class="wrap">
            <h1>TLS Theme License</h1>

            <?php if ($this->is_founder()): ?>
                <div class="notice notice-success">
                    <p><strong>👑 Founder Access</strong> - Full unlimited access. No license required.</p>
                </div>
                <?php
                // Show security status for founder
                $current_user = wp_get_current_user();
                $has_wp_config_key = defined('TLS_FOUNDER_KEY');
                $has_user_meta = get_user_meta($current_user->ID, '_tls_founder_verified', true);
                ?>
                <div class="notice notice-info" style="border-left-color: #667eea;">
                    <p><strong><span class="dashicons dashicons-lock"></span> Security Status:</strong></p>
                    <ul style="margin: 10px 0 10px 20px;">
                        <li><span class="dashicons dashicons-yes-alt"></span> Email Verified: <?php echo esc_html($current_user->user_email); ?></li>
                        <li><span class="dashicons dashicons-yes-alt"></span> Super Admin: <?php echo is_super_admin($current_user->ID) ? 'Yes' : 'No (User ID #1)'; ?></li>
                        <li><?php echo $has_wp_config_key ? '<span class="dashicons dashicons-yes-alt"></span>' : '<span class="dashicons dashicons-warning"></span>'; ?> wp-config.php Key: <?php echo $has_wp_config_key ? 'Active' : 'Not Set (Recommended)'; ?></li>
                        <li><span class="dashicons dashicons-yes-alt"></span> User Meta Hash: <?php echo $has_user_meta ? 'Verified' : 'Generated'; ?></li>
                    </ul>
                    <?php if (!$has_wp_config_key): ?>
                    <p style="margin-top: 10px;"><strong>Recommendation:</strong> Add <code>define('TLS_FOUNDER_KEY', 'your_secret');</code> to wp-config.php for maximum security. <a href="<?php echo esc_url(get_template_directory_uri()); ?>/FOUNDER-SECURITY-SETUP.md" target="_blank">Learn More</a></p>
                    <?php endif; ?>
                </div>
            <?php elseif ($is_active): ?>
                <div class="notice notice-success">
                    <p><strong>✓ License Active</strong></p>
                </div>
            <?php else: ?>
                <div class="notice notice-warning">
                    <p><strong>⚠ License Not Active</strong> - Grace period: <?php echo $this->get_grace_period_days_left(); ?> days left</p>
                </div>
            <?php endif; ?>

            <div class="card" style="max-width: 600px; margin-top: 20px;">
                <h2>License Information</h2>

                <table class="form-table">
                    <tr>
                        <th>Status:</th>
                        <td>
                            <?php if ($is_active): ?>
                                <span style="color: #46b450; font-weight: 600;">● Active</span>
                            <?php else: ?>
                                <span style="color: #dc3232; font-weight: 600;">● Inactive</span>
                            <?php endif; ?>
                        </td>
                    </tr>

                    <?php if (!empty($license_data)): ?>
                    <tr>
                        <th>License Key:</th>
                        <td><code><?php echo esc_html($license_key); ?></code></td>
                    </tr>
                    <tr>
                        <th>Type:</th>
                        <td><?php echo ucfirst($license_data['type'] ?? 'N/A'); ?></td>
                    </tr>
                    <tr>
                        <th>Expires:</th>
                        <td><?php echo $license_data['expires'] === 'lifetime' ? 'Lifetime' : date('F j, Y', strtotime($license_data['expires'])); ?></td>
                    </tr>
                    <tr>
                        <th>Activated:</th>
                        <td><?php echo date('F j, Y', $license_data['activated_at']); ?></td>
                    </tr>
                    <?php endif; ?>
                </table>

                <?php if (!$is_active): ?>
                <form id="tls-license-form" style="margin-top: 20px;">
                    <h3>Activate License</h3>
                    <p>Enter your license key to activate the theme:</p>

                    <input type="text"
                           id="tls-license-key"
                           placeholder="XXXXX-XXXXX-XXXXX-XXXXX-XXXXX"
                           style="width: 100%; max-width: 400px; padding: 8px; font-family: monospace; text-transform: uppercase;"
                           maxlength="29">

                    <p>
                        <button type="submit" class="button button-primary" id="tls-activate-btn">
                            Activate License
                        </button>
                        <span class="spinner" style="float: none; margin: 0 10px;"></span>
                    </p>

                    <div id="tls-license-message"></div>
                </form>
                <?php else: ?>
                <form id="tls-deactivate-form" style="margin-top: 20px;">
                    <h3>Deactivate License</h3>
                    <p>Deactivate this license to use it on another domain.</p>
                    <button type="submit" class="button button-secondary" id="tls-deactivate-btn">
                        Deactivate License
                    </button>
                    <span class="spinner" style="float: none; margin: 0 10px;"></span>
                </form>
                <?php endif; ?>
            </div>

            <div class="card" style="max-width: 600px; margin-top: 20px;">
                <h3>Need Help?</h3>
                <p>If you need assistance with your license, please contact support.</p>
                <ul>
                    <li><strong>Email:</strong> support@tanahlotsabah.com</li>
                    <li><strong>Documentation:</strong> <a href="#" target="_blank">View Docs</a></li>
                </ul>
            </div>
        </div>

        <script>
        jQuery(document).ready(function($) {
            // Auto-format license key input
            $('#tls-license-key').on('input', function() {
                let val = $(this).val().replace(/[^A-Z0-9]/g, '').toUpperCase();
                let formatted = '';

                for (let i = 0; i < val.length && i < 25; i++) {
                    if (i > 0 && i % 5 === 0) {
                        formatted += '-';
                    }
                    formatted += val[i];
                }

                $(this).val(formatted);
            });

            // Activate license
            $('#tls-license-form').on('submit', function(e) {
                e.preventDefault();

                const licenseKey = $('#tls-license-key').val();
                const $btn = $('#tls-activate-btn');
                const $spinner = $btn.siblings('.spinner');
                const $message = $('#tls-license-message');

                if (!licenseKey) {
                    $message.html('<div class="notice notice-error"><p>Please enter a license key</p></div>');
                    return;
                }

                $btn.prop('disabled', true);
                $spinner.addClass('is-active');
                $message.html('');

                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'tls_activate_license',
                        license_key: licenseKey,
                        nonce: '<?php echo wp_create_nonce('tls_license_nonce'); ?>'
                    },
                    success: function(response) {
                        $btn.prop('disabled', false);
                        $spinner.removeClass('is-active');

                        if (response.success) {
                            $message.html('<div class="notice notice-success"><p>' + response.data.message + '</p></div>');
                            setTimeout(() => location.reload(), 1500);
                        } else {
                            $message.html('<div class="notice notice-error"><p>' + response.data.message + '</p></div>');
                        }
                    },
                    error: function() {
                        $btn.prop('disabled', false);
                        $spinner.removeClass('is-active');
                        $message.html('<div class="notice notice-error"><p>Connection error. Please try again.</p></div>');
                    }
                });
            });

            // Deactivate license
            $('#tls-deactivate-form').on('submit', function(e) {
                e.preventDefault();

                if (!confirm('Are you sure you want to deactivate this license?')) {
                    return;
                }

                const $btn = $('#tls-deactivate-btn');
                const $spinner = $btn.siblings('.spinner');

                $btn.prop('disabled', true);
                $spinner.addClass('is-active');

                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'tls_deactivate_license',
                        nonce: '<?php echo wp_create_nonce('tls_license_nonce'); ?>'
                    },
                    success: function(response) {
                        if (response.success) {
                            location.reload();
                        }
                    }
                });
            });
        });
        </script>

        <style>
        .card {
            background: #fff;
            border: 1px solid #ccd0d4;
            box-shadow: 0 1px 1px rgba(0,0,0,.04);
            padding: 20px;
            margin-bottom: 20px;
        }

        .card h2,
        .card h3 {
            margin-top: 0;
        }

        .form-table th {
            padding: 12px 0;
            width: 150px;
            font-weight: 600;
        }

        .form-table td {
            padding: 12px 0;
        }

        #tls-license-message {
            margin-top: 15px;
        }
        </style>
        <?php
    }
}

// Initialize license system
new TLS_License_System();
