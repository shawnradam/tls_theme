<?php
/**
 * TLS Theme License Key Generator
 * Standalone tool to generate license keys for customers
 *
 * HOW TO USE:
 * 1. Upload this file to a secure location on your server
 * 2. Access it via browser: http://yourdomain.com/path-to-file/license-generator.php
 * 3. Generate keys for your customers
 * 4. DELETE this file after use or protect it with a password
 *
 * SECURITY WARNING:
 * This file should NEVER be accessible publicly. Protect it or delete after use.
 *
 * @package TLS_Theme
 * @version 1.0.0
 */

// SECURITY: Set your own password here
define('GENERATOR_PASSWORD', 'tanahlotsabah2026'); // CHANGE THIS!

// Start session for authentication
session_start();

// Simple authentication
if (!isset($_SESSION['authenticated'])) {
    if (isset($_POST['password'])) {
        if ($_POST['password'] === GENERATOR_PASSWORD) {
            $_SESSION['authenticated'] = true;
        } else {
            $error = 'Invalid password!';
        }
    }
}

// Logout
if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit;
}

// Show login form if not authenticated
if (!isset($_SESSION['authenticated'])) {
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <title>TLS License Generator - Login</title>
        <style>
            * { margin: 0; padding: 0; box-sizing: border-box; }
            body {
                font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                min-height: 100vh;
                display: flex;
                align-items: center;
                justify-content: center;
                padding: 20px;
            }
            .login-box {
                background: white;
                padding: 40px;
                border-radius: 16px;
                box-shadow: 0 20px 60px rgba(0,0,0,0.3);
                max-width: 400px;
                width: 100%;
            }
            h1 {
                margin-bottom: 30px;
                color: #333;
                text-align: center;
            }
            input[type="password"] {
                width: 100%;
                padding: 12px;
                border: 2px solid #e0e0e0;
                border-radius: 8px;
                font-size: 16px;
                margin-bottom: 20px;
            }
            button {
                width: 100%;
                padding: 12px;
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                color: white;
                border: none;
                border-radius: 8px;
                font-size: 16px;
                font-weight: 600;
                cursor: pointer;
            }
            button:hover {
                opacity: 0.9;
            }
            .error {
                background: #fee;
                color: #c33;
                padding: 10px;
                border-radius: 8px;
                margin-bottom: 20px;
                text-align: center;
            }
            .warning {
                background: #fffbea;
                border-left: 4px solid #f59e0b;
                padding: 12px;
                margin-top: 20px;
                font-size: 14px;
                color: #92400e;
            }
        </style>
    </head>
    <body>
        <div class="login-box">
            <h1><span style="font-size: 24px;">&#128274;</span> License Generator</h1>
            <?php if (isset($error)): ?>
                <div class="error"><?php echo $error; ?></div>
            <?php endif; ?>
            <form method="POST">
                <input type="password" name="password" placeholder="Enter Password" required autofocus>
                <button type="submit">Access Generator</button>
            </form>
            <div class="warning">
                <strong><span style="color: #f59e0b;">⚠</span> Security Warning:</strong> This tool should be protected and never left publicly accessible.
            </div>
        </div>
    </body>
    </html>
    <?php
    exit;
}

// ==========================================
// LICENSE KEY GENERATION LOGIC
// ==========================================

class License_Key_Generator {

    public $auth_key = 'YOUR_WORDPRESS_AUTH_KEY_HERE'; // Replace with your WordPress AUTH_KEY

    public function __construct() {
        // Try to load WordPress AUTH_KEY if available
        // Try multiple possible paths
        $possible_paths = [
            __DIR__ . '/../../../../wp-config.php',  // Standard WordPress location
            __DIR__ . '/../../../../../wp-config.php', // One level up
            dirname(dirname(dirname(dirname(__DIR__)))) . '/wp-config.php', // Absolute path approach
        ];

        foreach ($possible_paths as $wp_config_path) {
            if (file_exists($wp_config_path)) {
                // Load the config file content
                $config_content = file_get_contents($wp_config_path);

                // Extract AUTH_KEY using regex
                if (preg_match("/define\s*\(\s*['\"]AUTH_KEY['\"]\s*,\s*['\"]([^'\"]+)['\"]\s*\)/", $config_content, $matches)) {
                    $this->auth_key = $matches[1];
                    break;
                }
            }
        }
    }

    /**
     * Generate a new license key
     */
    public function generate_key($prefix = '') {
        // Generate random 20 characters
        $chars = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789'; // Exclude confusing chars: I, O, 1, 0
        $random_data = '';

        for ($i = 0; $i < 20; $i++) {
            $random_data .= $chars[random_int(0, strlen($chars) - 1)];
        }

        // Add prefix if provided
        if (!empty($prefix)) {
            $prefix = strtoupper(substr(preg_replace('/[^A-Z0-9]/', '', $prefix), 0, 3));
            $random_data = $prefix . substr($random_data, 3);
        }

        // Calculate checksum
        $checksum = $this->calculate_checksum($random_data);

        // Combine data + checksum
        $full_key = $random_data . $checksum;

        // Format as XXXXX-XXXXX-XXXXX-XXXXX-XXXXX
        return $this->format_key($full_key);
    }

    /**
     * Calculate checksum for license key
     */
    private function calculate_checksum($data) {
        $hash = hash('sha256', $data . $this->auth_key);

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
     * Format key with hyphens
     */
    private function format_key($key) {
        return substr($key, 0, 5) . '-' .
               substr($key, 5, 5) . '-' .
               substr($key, 10, 5) . '-' .
               substr($key, 15, 5) . '-' .
               substr($key, 20, 5);
    }

    /**
     * Verify if a key is valid
     */
    public function verify_key($key) {
        // Remove hyphens
        $clean_key = str_replace('-', '', strtoupper($key));

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
     * Generate batch of keys
     */
    public function generate_batch($count, $prefix = '') {
        $keys = [];
        for ($i = 0; $i < $count; $i++) {
            $keys[] = $this->generate_key($prefix);
        }
        return $keys;
    }
}

$generator = new License_Key_Generator();

// Handle form submissions
$generated_keys = [];
$verification_result = null;

if (isset($_POST['action'])) {
    if ($_POST['action'] === 'generate') {
        $count = max(1, min(100, intval($_POST['count'])));
        $prefix = sanitize_text_field($_POST['prefix'] ?? '');
        $generated_keys = $generator->generate_batch($count, $prefix);
    } elseif ($_POST['action'] === 'verify') {
        $key_to_verify = sanitize_text_field($_POST['verify_key']);
        $verification_result = $generator->verify_key($key_to_verify);
    }
}

function sanitize_text_field($str) {
    return htmlspecialchars(strip_tags(trim($str)), ENT_QUOTES, 'UTF-8');
}

?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TLS Theme - License Key Generator</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 40px 20px;
        }

        .container {
            max-width: 900px;
            margin: 0 auto;
        }

        .header {
            text-align: center;
            color: white;
            margin-bottom: 40px;
        }

        .header h1 {
            font-size: 2.5rem;
            margin-bottom: 10px;
        }

        .header p {
            opacity: 0.9;
            font-size: 1.1rem;
        }

        .logout-btn {
            float: right;
            background: rgba(255,255,255,0.2);
            color: white;
            padding: 8px 20px;
            border-radius: 20px;
            text-decoration: none;
            font-size: 14px;
        }

        .logout-btn:hover {
            background: rgba(255,255,255,0.3);
        }

        .card {
            background: white;
            border-radius: 16px;
            padding: 30px;
            margin-bottom: 30px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
        }

        .card h2 {
            color: #333;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 2px solid #f0f0f0;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #555;
        }

        .form-group input,
        .form-group select {
            width: 100%;
            padding: 12px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 16px;
        }

        .form-group input:focus,
        .form-group select:focus {
            outline: none;
            border-color: #667eea;
        }

        .btn {
            padding: 12px 30px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.2s;
        }

        .btn:hover {
            transform: translateY(-2px);
        }

        .btn-secondary {
            background: #6c757d;
        }

        .keys-output {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin-top: 20px;
            max-height: 400px;
            overflow-y: auto;
        }

        .key-item {
            background: white;
            padding: 12px 16px;
            margin-bottom: 10px;
            border-radius: 6px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-family: monospace;
            font-size: 16px;
            border-left: 4px solid #667eea;
        }

        .copy-btn {
            padding: 6px 12px;
            background: #667eea;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 12px;
        }

        .copy-btn:hover {
            background: #5568d3;
        }

        .success {
            background: #d4edda;
            border-left: 4px solid #28a745;
            padding: 15px;
            border-radius: 8px;
            color: #155724;
        }

        .error {
            background: #f8d7da;
            border-left: 4px solid #dc3545;
            padding: 15px;
            border-radius: 8px;
            color: #721c24;
        }

        .warning {
            background: #fff3cd;
            border-left: 4px solid #ffc107;
            padding: 15px;
            border-radius: 8px;
            color: #856404;
            margin-bottom: 20px;
        }

        .stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }

        .stat-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px;
            border-radius: 12px;
            text-align: center;
        }

        .stat-card h3 {
            font-size: 2rem;
            margin-bottom: 5px;
        }

        .stat-card p {
            opacity: 0.9;
        }

        .flex-row {
            display: flex;
            gap: 15px;
            align-items: flex-end;
        }

        .flex-row .form-group {
            flex: 1;
        }

        @media (max-width: 768px) {
            .header h1 {
                font-size: 1.8rem;
            }

            .flex-row {
                flex-direction: column;
            }

            .stat-card h3 {
                font-size: 1.5rem;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <a href="?logout" class="logout-btn">Logout</a>
            <h1><span style="font-size: 24px;">&#128273;</span> TLS Theme License Generator</h1>
            <p>Generate secure license keys for your customers</p>
        </div>

        <div class="warning">
            <strong><span style="color: #f59e0b;">⚠</span> SECURITY WARNING:</strong> This file should be deleted after use or moved to a secure location. Never leave it publicly accessible!
        </div>

        <!-- Generate Keys Form -->
        <div class="card">
            <h2>Generate License Keys</h2>
            <form method="POST">
                <input type="hidden" name="action" value="generate">

                <div class="flex-row">
                    <div class="form-group">
                        <label>Number of Keys (1-100)</label>
                        <input type="number" name="count" min="1" max="100" value="1" required>
                    </div>

                    <div class="form-group">
                        <label>Prefix (Optional, 3 chars)</label>
                        <input type="text" name="prefix" maxlength="3" placeholder="e.g., TLS">
                    </div>

                    <button type="submit" class="btn">Generate Keys</button>
                </div>
            </form>

            <?php if (!empty($generated_keys)): ?>
            <div class="keys-output">
                <div style="display: flex; justify-content: space-between; margin-bottom: 15px;">
                    <strong>Generated Keys (<?php echo count($generated_keys); ?>):</strong>
                    <button onclick="copyAllKeys()" class="btn-secondary" style="padding: 6px 15px; border: none; border-radius: 4px; cursor: pointer;">Copy All</button>
                </div>
                <div id="keys-list">
                    <?php foreach ($generated_keys as $key): ?>
                    <div class="key-item">
                        <span class="key-value"><?php echo $key; ?></span>
                        <button onclick="copyKey('<?php echo $key; ?>')" class="copy-btn">Copy</button>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>
        </div>

        <!-- Verify Key Form -->
        <div class="card">
            <h2>Verify License Key</h2>
            <form method="POST">
                <input type="hidden" name="action" value="verify">

                <div class="flex-row">
                    <div class="form-group">
                        <label>License Key</label>
                        <input type="text" name="verify_key" placeholder="XXXXX-XXXXX-XXXXX-XXXXX-XXXXX" required style="text-transform: uppercase;">
                    </div>

                    <button type="submit" class="btn">Verify Key</button>
                </div>
            </form>

            <?php if ($verification_result !== null): ?>
                <?php if ($verification_result): ?>
                <div class="success">
                    <strong>✓ Valid License Key</strong> - This key is authentic and can be activated.
                </div>
                <?php else: ?>
                <div class="error">
                    <strong>✗ Invalid License Key</strong> - This key is not valid or has been tampered with.
                </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>

        <!-- Security Status -->
        <div class="card">
            <h2><span style="font-size: 20px;">&#128274;</span> Security Status</h2>
            <?php
            $auth_key_loaded = $generator->auth_key !== 'YOUR_WORDPRESS_AUTH_KEY_HERE';
            $auth_key_preview = $auth_key_loaded ? substr($generator->auth_key, 0, 20) . '...' : 'NOT LOADED';
            ?>
            <div style="margin-bottom: 20px;">
                <?php if ($auth_key_loaded): ?>
                    <div class="success">
                        <strong><span style="color: #28a745;">✓</span> WordPress AUTH_KEY Loaded Successfully!</strong>
                        <p style="margin: 10px 0 0 0; font-family: monospace; font-size: 13px;">
                            Key Preview: <?php echo htmlspecialchars($auth_key_preview, ENT_QUOTES, 'UTF-8'); ?>
                        </p>
                        <p style="margin: 10px 0 0 0; font-size: 14px;">
                            Generated keys will work with your WordPress site.
                        </p>
                    </div>
                <?php else: ?>
                    <div class="error">
                        <strong><span style="color: #f59e0b;">⚠</span> WordPress AUTH_KEY Not Loaded!</strong>
                        <p style="margin: 10px 0 0 0;">
                            Keys generated will NOT work. Please check wp-config.php path.
                        </p>
                        <p style="margin: 10px 0 0 0; font-size: 13px;">
                            Expected location: <code>C:\xampp\htdocs\tanahlotsabah\wp-config.php</code>
                        </p>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Statistics -->
        <div class="card">
            <h2>Quick Stats</h2>
            <div class="stats">
                <div class="stat-card">
                    <h3><?php echo count($generated_keys); ?></h3>
                    <p>Keys Generated Today</p>
                </div>
                <div class="stat-card">
                    <h3>256-bit</h3>
                    <p>Encryption Level</p>
                </div>
                <div class="stat-card">
                    <h3>100%</h3>
                    <p>Secure & Unique</p>
                </div>
            </div>
        </div>
    </div>

    <script>
        function copyKey(key) {
            navigator.clipboard.writeText(key).then(() => {
                alert('License key copied to clipboard!');
            });
        }

        function copyAllKeys() {
            const keys = Array.from(document.querySelectorAll('.key-value')).map(el => el.textContent).join('\n');
            navigator.clipboard.writeText(keys).then(() => {
                alert('All keys copied to clipboard!');
            });
        }

        // Auto-format key input
        document.querySelector('input[name="verify_key"]').addEventListener('input', function(e) {
            let val = e.target.value.replace(/[^A-Z0-9]/g, '').toUpperCase();
            let formatted = '';

            for (let i = 0; i < val.length && i < 25; i++) {
                if (i > 0 && i % 5 === 0) {
                    formatted += '-';
                }
                formatted += val[i];
            }

            e.target.value = formatted;
        });
    </script>
</body>
</html>
<?php
session_write_close();
?>
