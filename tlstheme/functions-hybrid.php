<?php
/**
 * TanahLotSabah Theme - Functions
 * Includes Full Land Development Calculator with Lead Tracking
 */

if (!defined('ABSPATH')) exit;

// TLS REST API User Endpoints - Now handled by tls-app-auth plugin
// require_once dirname(__FILE__) . '/inc/tls-rest-api-user.php';

// Hide Admin Bar based on user preference
add_filter('show_admin_bar', function($show) {
    if (is_user_logged_in()) {
        $hide = get_user_meta(get_current_user_id(), 'tls_hide_admin_bar', true);
        if ($hide) return false;
    }
    return $show;
});

// Custom logout AJAX
add_action('wp_ajax_tls_logout', 'tls_custom_logout');
add_action('wp_ajax_nopriv_tls_logout', 'tls_custom_logout');

function tls_custom_logout() {
    wp_logout();
    wp_send_json_success(['redirect' => home_url('/')]);
}

// Redirect wp-login.php to homepage - Controlled by Login Security Settings
add_action('login_init', function() {
    // Check if wp-login.php blocking is enabled
    $disable_wp_login = get_option('tls_disable_wp_login', 0);

    if ($disable_wp_login) {
        wp_redirect(home_url());
        exit;
    }
});

// AJAX Login
add_action('wp_ajax_tls_ajax_login', 'tls_ajax_login');
add_action('wp_ajax_nopriv_tls_ajax_login', 'tls_ajax_login');

function tls_ajax_login() {
    check_ajax_referer('tls_login_nonce', 'nonce');
    
    $creds = [
        'user_login' => sanitize_text_field($_POST['username']),
        'user_password' => $_POST['password'],
        'remember' => isset($_POST['remember'])
    ];
    
    $user = wp_signon($creds, false);
    
    if (is_wp_error($user)) {
        wp_send_json_error(['message' => $user->get_error_message()]);
    }
    
    wp_send_json_success(['redirect' => home_url('/dashboard/'), 'message' => 'Login successful']);
}

// Theme version
define('TLS_VERSION', '4.0');
define('TLS_LDC_PREFIX', 'tls_ldc_');

// ============================================
// LICENSE SYSTEM
// ============================================
require_once get_template_directory() . '/inc/license-system.php';

// ============================================
// FAB (FLOATING ACTION BUTTON) SYSTEM
// ============================================
require_once get_template_directory() . '/inc/fab-system.php';
require_once get_template_directory() . '/inc/agent-tools/agent-tools.php';
global $tls_fab_system;
$tls_fab_system = new TLS_FAB_System(); // Initialize FAB System

// ============================================
// ADMIN ACCESS RESTRICTION
// Only administrators can access the admin dashboard
// Note: Users can access profile.php and admin-ajax.php, but not other admin pages
// ============================================
add_action('admin_init', function() {
    // Skip on AJAX requests
    if (defined('DOING_AJAX') && DOING_AJAX) {
        return;
    }

    // Allow profile page access for all logged-in users
    global $pagenow;
    if ($pagenow === 'profile.php' || $pagenow === 'user-edit.php') {
        return;
    }

    // Only restrict non-admin users from accessing other admin pages
    if (!current_user_can('manage_options')) {
        wp_redirect(home_url());
        exit;
    }
});

// ============================================
// THEME SETUP
// ============================================
add_action('after_setup_theme', function() {
    add_theme_support('title-tag');
    add_theme_support('post-thumbnails');
    add_theme_support('html5', ['search-form', 'comment-form']);
    register_nav_menus([
        'primary' => 'Menu Utama',
        'footer' => 'Menu Footer'
    ]);
    add_image_size('listing-thumb', 600, 400, true);
    add_image_size('hero-bg', 1920, 1080, true);
});

// Add login page rewrite
add_action('init', function() {
    add_rewrite_rule('^login/?', 'index.php?pagename=login', 'top');
    add_rewrite_rule('^dashboard/?', 'index.php?pagename=dashboard', 'top');
}, 1);

// Flush rewrite rules on theme activation
add_action('after_switch_theme', function() {
    flush_rewrite_rules();
    TLS_LDC_Database::create_tables();
    TLS_FAB_System::create_table(); // Create FAB settings table

    // Create default land types
    tls_create_land_types();
});

// Also flush on admin init as fallback
add_action('admin_init', function() {
    global $pagenow;
    if ($pagenow === 'admin.php' && isset($_GET['page']) && $_GET['page'] === 'tls-splash') {
        flush_rewrite_rules();
    }
});

function tls_create_land_types() {
    $land_types = [
        ['name' => 'Land Lot', 'slug' => 'land-lot'],
        ['name' => 'Commercial', 'slug' => 'commercial'],
        ['name' => 'Manufacturing', 'slug' => 'manufacturing'],
        ['name' => 'Palm Oil', 'slug' => 'palm-oil'],
    ];
    
    foreach ($land_types as $type) {
        if (!term_exists($type['slug'], 'land_type')) {
            wp_insert_term($type['name'], 'land_type', [
                'slug' => $type['slug']
            ]);
        }
    }
}

// Create terms on init if they don't exist
add_action('init', function() {
    tls_create_land_types();
}, 1);

// ============================================
// SPLASH SCREEN SETTINGS
// ============================================
add_action('admin_menu', function() {
    add_theme_page('Splash', 'Splash Screen', 'manage_options', 'tls-splash', 'tls_splash_admin');
});

function tls_splash_admin() {
    wp_enqueue_media();
    if (isset($_POST['save']) && check_admin_referer('tls_splash_save')) {
        update_option('tls_splash_enabled', isset($_POST['enabled']) ? 1 : 0);
        update_option('tls_splash_img', intval($_POST['img_id']));
        echo '<div class="notice notice-success"><p>Saved!</p></div>';
    }
    
    $enabled = get_option('tls_splash_enabled', 1);
    $img_id = get_option('tls_splash_img', 0);
    $img_url = $img_id ? wp_get_attachment_url($img_id) : '';
    ?>
    <div class="wrap">
        <h1>Splash Screen</h1>
        <form method="post">
            <?php wp_nonce_field('tls_splash_save'); ?>
            <table class="form-table">
                <tr>
                    <th>Enable</th>
                    <td><label><input type="checkbox" name="enabled" value="1" <?php checked($enabled, 1); ?>> Show splash on homepage</label></td>
                </tr>
                <tr>
                    <th>Image</th>
                    <td>
                        <input type="hidden" name="img_id" id="img_id" value="<?php echo $img_id; ?>">
                        <div id="preview" style="margin-bottom:10px;"><?php if($img_url): ?><img src="<?php echo $img_url; ?>" style="max-width:400px;height:auto;border-radius:8px;"><?php endif; ?></div>
                        <button type="button" class="button" onclick="openMedia()"><?php echo $img_id ? 'Change' : 'Upload'; ?></button>
                    </td>
                </tr>
            </table>
            <p class="submit"><button type="submit" class="button button-primary" name="save">Save</button></p>
        </form>
        <script>
        function openMedia() {
            var frame = wp.media({title:'Select Image',multiple:false});
            frame.on('select',function(){var a=frame.state().get('selection').first().toJSON();document.getElementById('img_id').value=a.id;document.getElementById('preview').innerHTML='<img src="'+a.url+'" style="max-width:400px;height:auto;border-radius:8px;">';});
            frame.open();
        }
        </script>
    </div>
    <?php
}

// Set default land type for existing posts
function tls_assign_default_land_type() {
    $already_done = get_option('tls_land_types_assigned', 0);
    if ($already_done) return;
    
    $posts = get_posts([
        'post_type' => 'tanah',
        'post_status' => 'any',
        'posts_per_page' => -1,
    ]);
    
    $land_lot = get_term_by('slug', 'land-lot', 'land_type');
    
    foreach ($posts as $post) {
        $has_type = wp_get_object_terms($post->ID, 'land_type');
        if (empty($has_type) && $land_lot) {
            wp_set_object_terms($post->ID, $land_lot->term_id, 'land_type');
        }
    }
    
    update_option('tls_land_types_assigned', 1);
}

add_action('admin_init', 'tls_assign_default_land_type');

// ============================================
// ADVANCED SEARCH QUERY HANDLER
// ============================================
add_action('pre_get_posts', 'tls_advanced_search_query');

function tls_advanced_search_query($query) {
    // Only modify main query on search and archive pages
    if (!$query->is_main_query() || is_admin()) {
        return;
    }

    // Only apply to tanah post type searches
    if (!$query->is_search() && !$query->is_post_type_archive('tanah') && !$query->is_tax()) {
        return;
    }

    // Set post type to tanah
    if (isset($_GET['post_type']) && $_GET['post_type'] === 'tanah') {
        $query->set('post_type', 'tanah');
    }

    // Tax queries array
    $tax_query = [];

    // Land Type Filter
    if (!empty($_GET['land_type'])) {
        $tax_query[] = [
            'taxonomy' => 'land_type',
            'field' => 'slug',
            'terms' => sanitize_text_field($_GET['land_type'])
        ];
    }

    // Daerah Filter
    if (!empty($_GET['daerah'])) {
        $tax_query[] = [
            'taxonomy' => 'daerah',
            'field' => 'slug',
            'terms' => sanitize_text_field($_GET['daerah'])
        ];
    }

    if (!empty($tax_query)) {
        $tax_query['relation'] = 'AND';
        $query->set('tax_query', $tax_query);
    }

    // Meta queries array for custom fields
    $meta_query = [];

    // Geran Filter
    if (!empty($_GET['geran'])) {
        $meta_query[] = [
            'key' => '_tanah_jenis_geran',
            'value' => sanitize_text_field($_GET['geran']),
            'compare' => '='
        ];
    }

    // Zoning Filter
    if (!empty($_GET['zoning'])) {
        $meta_query[] = [
            'key' => '_tanah_zoning',
            'value' => sanitize_text_field($_GET['zoning']),
            'compare' => '='
        ];
    }

    // Price Range Filter
    if (!empty($_GET['min_price']) || !empty($_GET['max_price'])) {
        $price_query = [
            'key' => '_tanah_harga',
            'type' => 'NUMERIC'
        ];

        if (!empty($_GET['min_price']) && !empty($_GET['max_price'])) {
            $price_query['value'] = [
                (int)$_GET['min_price'],
                (int)$_GET['max_price']
            ];
            $price_query['compare'] = 'BETWEEN';
        } elseif (!empty($_GET['min_price'])) {
            $price_query['value'] = (int)$_GET['min_price'];
            $price_query['compare'] = '>=';
        } elseif (!empty($_GET['max_price'])) {
            $price_query['value'] = (int)$_GET['max_price'];
            $price_query['compare'] = '<=';
        }

        $meta_query[] = $price_query;
    }

    // Size Range Filter (Ekar)
    if (!empty($_GET['min_size']) || !empty($_GET['max_size'])) {
        $size_query = [
            'key' => '_tanah_keluasan',
            'type' => 'DECIMAL(10,2)'
        ];

        if (!empty($_GET['min_size']) && !empty($_GET['max_size'])) {
            $size_query['value'] = [
                (float)$_GET['min_size'],
                (float)$_GET['max_size']
            ];
            $size_query['compare'] = 'BETWEEN';
        } elseif (!empty($_GET['min_size'])) {
            $size_query['value'] = (float)$_GET['min_size'];
            $size_query['compare'] = '>=';
        } elseif (!empty($_GET['max_size'])) {
            $size_query['value'] = (float)$_GET['max_size'];
            $size_query['compare'] = '<=';
        }

        $meta_query[] = $size_query;
    }

    // Verified Only Filter
    if (!empty($_GET['verified_only']) && $_GET['verified_only'] == '1') {
        $meta_query[] = [
            'key' => '_tanah_verified',
            'value' => '1',
            'compare' => '='
        ];
    }

    if (!empty($meta_query)) {
        $meta_query['relation'] = 'AND';
        $query->set('meta_query', $meta_query);
    }

    // Sorting / Order By
    $orderby = !empty($_GET['orderby']) ? sanitize_text_field($_GET['orderby']) : 'date';

    switch ($orderby) {
        case 'price_low':
            $query->set('meta_key', '_tanah_harga');
            $query->set('orderby', 'meta_value_num');
            $query->set('order', 'ASC');
            break;

        case 'price_high':
            $query->set('meta_key', '_tanah_harga');
            $query->set('orderby', 'meta_value_num');
            $query->set('order', 'DESC');
            break;

        case 'size_low':
            $query->set('meta_key', '_tanah_keluasan');
            $query->set('orderby', 'meta_value_num');
            $query->set('order', 'ASC');
            break;

        case 'size_high':
            $query->set('meta_key', '_tanah_keluasan');
            $query->set('orderby', 'meta_value_num');
            $query->set('order', 'DESC');
            break;

        case 'date':
        default:
            $query->set('orderby', 'date');
            $query->set('order', 'DESC');
            break;
    }

    // Increase posts per page for search results
    if ($query->is_search() || $query->is_post_type_archive('tanah')) {
        $query->set('posts_per_page', 12);
    }
}

// Register query vars for custom search parameters
add_filter('query_vars', 'tls_register_search_query_vars');

function tls_register_search_query_vars($vars) {
    $vars[] = 'land_type';
    $vars[] = 'daerah';
    $vars[] = 'geran';
    $vars[] = 'zoning';
    $vars[] = 'min_price';
    $vars[] = 'max_price';
    $vars[] = 'min_size';
    $vars[] = 'max_size';
    $vars[] = 'verified_only';
    $vars[] = 'orderby';
    return $vars;
}

// ============================================
// ENQUEUE SCRIPTS & STYLES
// ============================================
add_action('wp_enqueue_scripts', function() {
    wp_enqueue_style('tls-style', get_stylesheet_uri(), [], TLS_VERSION);
    wp_enqueue_style('tls-calculator', get_template_directory_uri() . '/assets/css/calculator.css', [], TLS_VERSION);

    // Leaflet CSS
    wp_enqueue_style('leaflet', 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.css', [], '1.9.4');
    wp_enqueue_style('leaflet-markercluster', 'https://unpkg.com/leaflet.markercluster@1.5.3/dist/MarkerCluster.css', ['leaflet'], '1.5.3');
    wp_enqueue_style('leaflet-markercluster-default', 'https://unpkg.com/leaflet.markercluster@1.5.3/dist/MarkerCluster.Default.css', ['leaflet-markercluster'], '1.5.3');

    wp_enqueue_script('tls-calculator', get_template_directory_uri() . '/assets/js/calculator.js', ['jquery'], TLS_VERSION, true);
    wp_localize_script('tls-calculator', 'ldcAjax', [
        'ajaxurl' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('tls_ldc_nonce'),
        'currency' => 'RM',
        'messages' => [
            'success_save' => 'Estimate saved successfully!',
            'error_general' => 'An error occurred. Please try again.',
            'generating_pdf' => 'Generating your PDF report...',
        ]
    ]);
});

add_action('admin_enqueue_scripts', function($hook) {
    // Splash Page
    if ($hook === 'appearance_page_tls-splash') {
        wp_enqueue_media();
    }

    // Nearby Places Page
    if (strpos($hook, 'tls-nearby-places') !== false) {
        wp_enqueue_media();
    }

    global $post_type;
    if ($hook === 'post.php' || $hook === 'post-new.php') {
        if (in_array($post_type, ['hero_video', 'tanah', 'tls_agent'])) {
            wp_enqueue_media();

            // Enqueue Leaflet for tanah post type (map drawing)
            if ($post_type === 'tanah') {
                wp_enqueue_style('leaflet', 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.css', [], '1.9.4');
                wp_enqueue_style('leaflet-draw', 'https://unpkg.com/leaflet-draw@1.0.4/dist/leaflet.draw.css', ['leaflet'], '1.0.4');

                wp_enqueue_script('leaflet', 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.js', [], '1.9.4', true);
                wp_enqueue_script('leaflet-draw', 'https://unpkg.com/leaflet-draw@1.0.4/dist/leaflet.draw.js', ['leaflet'], '1.0.4', true);
            }
        }
    }
});

// ============================================
// REGISTER CPT & TAXONOMIES
// ============================================
add_action('init', function() {
    register_post_type('tanah', [
        'labels' => [
            'name' => 'Senarai Tanah',
            'singular_name' => 'Tanah',
            'add_new_item' => 'Tambah Tanah Baru',
            'edit_item' => 'Edit Tanah',
            'all_items' => 'Semua Tanah',
        ],
        'public' => true,
        'has_archive' => true,
        'show_in_menu' => false, // Managed via TLS System dashboard
        'show_in_rest' => true, // Enable REST API
        'rest_base' => 'tanah', // REST API base
        'supports' => ['title', 'editor', 'thumbnail', 'custom-fields'],
        'rewrite' => ['slug' => 'tanah']
    ]);
    
    register_taxonomy('daerah', 'tanah', [
        'labels' => ['name' => 'Daerah'],
        'hierarchical' => true,
        'show_in_rest' => true, // Enable REST API
        'rewrite' => ['slug' => 'daerah']
    ]);
    
    register_taxonomy('jenis_geran', 'tanah', [
        'labels' => ['name' => 'Jenis Geran'],
        'hierarchical' => true,
        'show_in_rest' => true, // Enable REST API
    ]);
    
    register_taxonomy('land_type', 'tanah', [
        'labels' => [
            'name' => 'Land Type',
            'add_new_item' => 'Add Land Type',
            'edit_item' => 'Edit Land Type',
        ],
        'hierarchical' => true,
        'show_admin_column' => true,
    ]);

    register_post_type('hero_video', [
        'labels' => [
            'name' => 'Hero Videos',
            'singular_name' => 'Hero Video',
            'add_new_item' => 'Tambah Hero Video',
            'edit_item' => 'Edit Hero Video',
            'all_items' => 'Semua Hero Video',
        ],
        'public' => false,
        'show_ui' => true,
        'show_in_menu' => false, // Managed via TLS System dashboard
        'supports' => ['title', 'thumbnail'],
    ]);

    // Register POI System
    register_post_type('tls_poi', [
        'labels' => [
            'name' => 'Nearby Places',
            'singular_name' => 'Place',
            'all_items' => 'All Places',
            'add_new_item' => 'Add New Place',
        ],
        'public' => false,
        'show_ui' => true,
        'show_in_menu' => false, // Managed via TLS System dashboard
        'supports' => ['title', 'thumbnail'],
    ]);
}, 0);

// ============================================
// HERO VIDEO METABOXES
// ============================================
add_action('add_meta_boxes', function() {
    add_meta_box('hero_video_meta', 'Hero Video Settings', 'render_hero_video_meta', 'hero_video', 'normal', 'high');
});

function render_hero_video_meta($post) {
    $video_url = get_post_meta($post->ID, 'hero_video_url', true);
    $video_type = get_post_meta($post->ID, 'hero_video_type', true) ?: 'youtube';
    $display_order = get_post_meta($post->ID, 'hero_display_order', true) ?: 0;
    $is_active = get_post_meta($post->ID, 'hero_is_active', true) ?: 1;
    $video_disabled = get_post_meta($post->ID, 'hero_video_disabled', true) ?: 0;
    $hero_image_id = get_post_thumbnail_id($post->ID);
    $hero_image_url = $hero_image_id ? wp_get_attachment_url($hero_image_id) : '';
    $hero_image_width = get_post_meta($post->ID, 'hero_image_width', true) ?: 1920;
    $hero_image_height = get_post_meta($post->ID, 'hero_image_height', true) ?: 1080;
    $hero_image_fit = get_post_meta($post->ID, 'hero_image_fit', true) ?: 'cover';
    ?>
    <table class="form-table">
        <tr>
            <th>Hero Image</th>
            <td>
                <div id="hero-image-preview" style="margin-bottom:10px;">
                    <?php if ($hero_image_url): ?>
                    <img src="<?php echo esc_attr($hero_image_url); ?>" style="max-width:300px;height:auto;border-radius:8px;">
                    <?php endif; ?>
                </div>
                <input type="hidden" name="hero_image_id" id="hero_image_id" value="<?php echo esc_attr($hero_image_id); ?>">
                <button type="button" class="button" id="hero-upload-image-btn">Upload Image</button>
                <?php if ($hero_image_id): ?>
                <button type="button" class="button" id="hero-remove-image-btn" style="margin-left:5px;">Remove</button>
                <?php endif; ?>
                <p class="description">Recommended size: 1920x1080px</p>
            </td>
        </tr>
        <tr>
            <th>Image Size</th>
            <td>
                <div style="display:flex;gap:10px;align-items:center;">
                    <input type="number" name="hero_image_width" id="hero_image_width" value="<?php echo esc_attr($hero_image_width); ?>" style="width:80px;" placeholder="Width">
                    <span>x</span>
                    <input type="number" name="hero_image_height" id="hero_image_height" value="<?php echo esc_attr($hero_image_height); ?>" style="width:80px;" placeholder="Height">
                    <span>px</span>
                </div>
            </td>
        </tr>
        <tr>
            <th>Image Fit</th>
            <td>
                <select name="hero_image_fit" id="hero_image_fit">
                    <option value="cover" <?php selected($hero_image_fit, 'cover'); ?>>Cover (Fill)</option>
                    <option value="contain" <?php selected($hero_image_fit, 'contain'); ?>>Contain (Fit)</option>
                    <option value="auto" <?php selected($hero_image_fit, 'auto'); ?>>Auto (Original)</option>
                </select>
            </td>
        </tr>
        <tr>
            <th><label for="hero_video_type">Media Type</label></th>
            <td>
                <select name="hero_video_type" id="hero_video_type">
                    <option value="youtube" <?php selected($video_type, 'youtube'); ?>>YouTube Video</option>
                    <option value="image" <?php selected($video_type, 'image'); ?>>Static Image</option>
                </select>
            </td>
        </tr>
        <tr>
            <th><label for="hero_video_disabled">Video Option</label></th>
            <td>
                <label>
                    <input type="checkbox" name="hero_video_disabled" id="hero_video_disabled" value="1" <?php checked($video_disabled, 1); ?>>
                    Disable video - show image only
                </label>
                <p class="description">Check this to disable video and show only the featured image</p>
            </td>
        </tr>
        <tr>
            <th><label for="hero_video_url">Video URL / Image URL</label></th>
            <td>
                <input type="url" name="hero_video_url" id="hero_video_url" value="<?php echo esc_attr($video_url); ?>" class="regular-text" placeholder="YouTube URL or Image URL">
                <p class="description">For YouTube: paste embed URL (e.g., https://www.youtube.com/embed/VIDEO_ID). For image: paste image URL.</p>
            </td>
        </tr>
        <tr>
            <th><label for="hero_display_order">Display Order</label></th>
            <td>
                <input type="number" name="hero_display_order" id="hero_display_order" value="<?php echo esc_attr($display_order); ?>" min="0">
                <p class="description">Lower number shows first. 0 = highest priority.</p>
            </td>
        </tr>
        <tr>
            <th><label for="hero_is_active">Active</label></th>
            <td>
                <label>
                    <input type="checkbox" name="hero_is_active" id="hero_is_active" value="1" <?php checked($is_active, 1); ?>>
                    Show in hero section
                </label>
            </td>
        </tr>
    </table>
    <script>
    jQuery(document).ready(function($) {
        var frame;
        $('#hero-upload-image-btn').on('click', function(e) {
            e.preventDefault();
            if (frame) {
                frame.open();
                return;
            }
            frame = wp.media({
                title: 'Select or Upload Hero Image',
                button: { text: 'Use as Hero Image' },
                multiple: false
            });
            frame.on('select', function() {
                var attachment = frame.state().get('selection').first().toJSON();
                $('#hero_image_id').val(attachment.id);
                $('#hero-image-preview').html('<img src="' + attachment.url + '" style="max-width:300px;height:auto;border-radius:8px;">');
                $('#hero-upload-image-btn').text('Replace Image');
                if ($('#hero-remove-image-btn').length === 0) {
                    $('#hero-upload-image-btn').after('<button type="button" id="hero-remove-image-btn" class="button" style="margin-left:5px;">Remove</button>');
                }
            });
            frame.open();
        });
        $('#hero-remove-image-btn').on('click', function(e) {
            e.preventDefault();
            $('#hero_image_id').val('');
            $('#hero-image-preview').html('');
            $('#hero-upload-image-btn').text('Upload Image');
            $(this).remove();
        });
    });
    </script>
    <?php
}

add_action('save_post_hero_video', function($post_id) {
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
    if (!current_user_can('edit_post', $post_id)) return;
    
    if (isset($_POST['hero_video_url'])) {
        update_post_meta($post_id, 'hero_video_url', sanitize_text_field($_POST['hero_video_url']));
    }
    if (isset($_POST['hero_video_type'])) {
        update_post_meta($post_id, 'hero_video_type', sanitize_text_field($_POST['hero_video_type']));
    }
    if (isset($_POST['hero_display_order'])) {
        update_post_meta($post_id, 'hero_display_order', intval($_POST['hero_display_order']));
    }
    $is_active = isset($_POST['hero_is_active']) ? 1 : 0;
    update_post_meta($post_id, 'hero_is_active', $is_active);
    
    if (isset($_POST['hero_image_width'])) {
        update_post_meta($post_id, 'hero_image_width', intval($_POST['hero_image_width']));
    }
    if (isset($_POST['hero_image_height'])) {
        update_post_meta($post_id, 'hero_image_height', intval($_POST['hero_image_height']));
    }
    if (isset($_POST['hero_image_fit'])) {
        update_post_meta($post_id, 'hero_image_fit', sanitize_text_field($_POST['hero_image_fit']));
    }
    update_post_meta($post_id, 'hero_video_disabled', isset($_POST['hero_video_disabled']) ? 1 : 0);
    
    if (isset($_POST['hero_image_id'])) {
        $image_id = intval($_POST['hero_image_id']);
        if ($image_id) {
            set_post_thumbnail($post_id, $image_id);
        } else {
            delete_post_thumbnail($post_id);
        }
    }
});

// ============================================
// CUSTOMIZER SETTINGS
// ============================================
add_action('customize_register', function($wp_customize) {
    $wp_customize->add_section('tls_contact', [
        'title' => 'Maklumat Hubungi',
        'priority' => 30
    ]);

    // WhatsApp Number
    $wp_customize->add_setting('whatsapp_number', [
        'default' => '60123456789',
        'sanitize_callback' => 'sanitize_text_field'
    ]);

    $wp_customize->add_control('whatsapp_number', [
        'label' => 'Nombor WhatsApp',
        'section' => 'tls_contact',
        'type' => 'text',
    ]);

    // Phone Number
    $wp_customize->add_setting('phone_number', [
        'default' => '+60 12-345 6789',
        'sanitize_callback' => 'sanitize_text_field'
    ]);

    $wp_customize->add_control('phone_number', [
        'label' => 'Phone Number',
        'section' => 'tls_contact',
        'type' => 'text',
    ]);

    // Email Address
    $wp_customize->add_setting('email_address', [
        'default' => 'info@tanahlotsabah.com',
        'sanitize_callback' => 'sanitize_email'
    ]);

    $wp_customize->add_control('email_address', [
        'label' => 'Email Address',
        'section' => 'tls_contact',
        'type' => 'email',
    ]);

    // Company Name
    $wp_customize->add_setting('company_name', [
        'default' => 'Tanah Lot Sabah',
        'sanitize_callback' => 'sanitize_text_field'
    ]);

    $wp_customize->add_control('company_name', [
        'label' => 'Company Name',
        'section' => 'tls_contact',
        'type' => 'text',
    ]);

    // License Number
    $wp_customize->add_setting('license_number', [
        'default' => 'E(1)1234/5',
        'sanitize_callback' => 'sanitize_text_field'
    ]);

    $wp_customize->add_control('license_number', [
        'label' => 'Agent License Number',
        'section' => 'tls_contact',
        'type' => 'text',
    ]);
});

// ============================================
// SEARCH & FILTER FUNCTIONALITY
// ============================================
add_action('pre_get_posts', function($query) {
    // Only modify main query on frontend for tanah post type
    if (is_admin() || !$query->is_main_query()) {
        return;
    }

    // Only apply to front page and tanah archives
    if (!is_front_page() && !is_post_type_archive('tanah') && !is_tax(['daerah', 'jenis_geran', 'land_type'])) {
        return;
    }

    // Set post type to tanah
    $query->set('post_type', 'tanah');

    // Build meta query array
    $meta_query = [];

    // Filter by price range
    if (!empty($_GET['max_price'])) {
        $max_price = intval($_GET['max_price']);
        if ($max_price > 0) {
            $meta_query[] = [
                'key' => '_tanah_harga',
                'value' => $max_price,
                'type' => 'NUMERIC',
                'compare' => '<='
            ];
        }
    }

    if (!empty($_GET['min_price'])) {
        $max_price = intval($_GET['min_price']);
        if ($max_price > 0) {
            $meta_query[] = [
                'key' => '_tanah_harga',
                'value' => $max_price,
                'type' => 'NUMERIC',
                'compare' => '>='
            ];
        }
    }

    // Filter by grant type (jenis_geran)
    if (!empty($_GET['geran'])) {
        $geran = sanitize_text_field($_GET['geran']);
        $meta_query[] = [
            'key' => '_tanah_jenis_geran',
            'value' => $geran,
            'compare' => '='
        ];
    }

    // Filter by zoning
    if (!empty($_GET['zoning'])) {
        $zoning = sanitize_text_field($_GET['zoning']);
        $meta_query[] = [
            'key' => '_tanah_zoning',
            'value' => $zoning,
            'compare' => '='
        ];
    }

    // Filter by size (keluasan)
    if (!empty($_GET['min_size'])) {
        $min_size = floatval($_GET['min_size']);
        if ($min_size > 0) {
            $meta_query[] = [
                'key' => '_tanah_keluasan',
                'value' => $min_size,
                'type' => 'DECIMAL',
                'compare' => '>='
            ];
        }
    }

    if (!empty($_GET['max_size'])) {
        $max_size = floatval($_GET['max_size']);
        if ($max_size > 0) {
            $meta_query[] = [
                'key' => '_tanah_keluasan',
                'value' => $max_size,
                'type' => 'DECIMAL',
                'compare' => '<='
            ];
        }
    }

    // Apply meta query if we have filters
    if (!empty($meta_query)) {
        $meta_query['relation'] = 'AND';
        $query->set('meta_query', $meta_query);
    }

    // Build tax query array
    $tax_query = [];

    // Filter by daerah (district)
    if (!empty($_GET['daerah'])) {
        $daerah = sanitize_text_field($_GET['daerah']);
        $tax_query[] = [
            'taxonomy' => 'daerah',
            'field' => 'slug',
            'terms' => $daerah
        ];
    }

    // Apply tax query if we have filters
    if (!empty($tax_query)) {
        $tax_query['relation'] = 'AND';
        $query->set('tax_query', $tax_query);
    }

    // Sorting
    if (!empty($_GET['orderby'])) {
        $orderby = sanitize_text_field($_GET['orderby']);

        switch ($orderby) {
            case 'price_low':
                $query->set('meta_key', '_tanah_harga');
                $query->set('orderby', 'meta_value_num');
                $query->set('order', 'ASC');
                break;
            case 'price_high':
                $query->set('meta_key', '_tanah_harga');
                $query->set('orderby', 'meta_value_num');
                $query->set('order', 'DESC');
                break;
            case 'size_low':
                $query->set('meta_key', '_tanah_keluasan');
                $query->set('orderby', 'meta_value_num');
                $query->set('order', 'ASC');
                break;
            case 'size_high':
                $query->set('meta_key', '_tanah_keluasan');
                $query->set('orderby', 'meta_value_num');
                $query->set('order', 'DESC');
                break;
            case 'date_new':
                $query->set('orderby', 'date');
                $query->set('order', 'DESC');
                break;
            case 'date_old':
                $query->set('orderby', 'date');
                $query->set('order', 'ASC');
                break;
            default:
                // Default: newest first
                $query->set('orderby', 'date');
                $query->set('order', 'DESC');
        }
    }

    // Set posts per page
    $query->set('posts_per_page', 12);
});

// ============================================
// HELPER FUNCTIONS
// ============================================
function tls_get_meta($key, $default = '') {
    $post_id = get_the_ID();
    if (!$post_id) return $default;
    $val = get_post_meta($post_id, $key, true);
    return $val ? $val : $default;
}

function tls_get_tanah_data($post_id = 0) {
    if (!$post_id) $post_id = get_the_ID();
    if (!$post_id) return [];
    
    $harga = get_post_meta($post_id, '_tanah_harga', true) ?: 0;
    $ekar = get_post_meta($post_id, '_tanah_keluasan', true) ?: 0;
    $sqft = $ekar * 43560;
    $psf = $sqft > 0 ? $harga / $sqft : 0;
    
    return [
        'id' => $post_id,
        'property_id' => get_post_meta($post_id, '_tanah_property_id', true) ?: '',
        'title' => get_the_title($post_id),
        'slug' => get_post_field('post_name', $post_id),
        'permalink' => get_permalink($post_id),
        'price' => (float)$harga,
        'price_formatted' => 'RM ' . number_format($harga),
        'land_size' => (float)$ekar,
        'land_unit' => 'Ekar',
        'land_size_sqft' => $sqft,
        'building_size' => get_post_meta($post_id, '_tanah_building_size', true) ?: 0,
        'building_unit' => get_post_meta($post_id, '_tanah_building_unit', true) ?: 'sqft',
        'psf' => $psf,
        'psf_formatted' => 'RM ' . number_format($psf, 2),
        'geran' => get_post_meta($post_id, '_tanah_jenis_geran', true) ?: 'CL',
        'zoning' => get_post_meta($post_id, '_tanah_zoning', true) ?: 'Kediaman',
        'verified' => get_post_meta($post_id, '_tanah_verified', true) ?: 0,
        'video_url' => get_post_meta($post_id, '_tanah_video_url', true) ?: '',
        'virtual_tour_url' => get_post_meta($post_id, '_tanah_virtual_tour_url', true) ?: '',
        'gallery' => get_post_meta($post_id, '_tanah_gallery', true) ?: '',
        'content' => get_post_field('post_content', $post_id),
        'lat' => get_post_meta($post_id, '_tanah_latitude', true) ?: '',
        'lng' => get_post_meta($post_id, '_tanah_longitude', true) ?: '',
        'featured_image' => get_the_post_thumbnail_url($post_id, 'medium_large') ?: '',
        'images' => [],
    ];
}

function tls_price_per_sqft($harga, $ekar) {
    if ($ekar <= 0) return 0;
    $sqft = floatval($ekar) * 43560;
    return $sqft > 0 ? floatval($harga) / $sqft : 0;
}

function tls_get_hero_videos() {
    $videos = new WP_Query([
        'post_type' => 'hero_video',
        'posts_per_page' => -1,
        'meta_query' => [
            'key' => 'hero_is_active',
            'value' => 1,
            'compare' => '='
        ],
        'orderby' => 'meta_value_num',
        'meta_key' => 'hero_display_order',
        'order' => 'ASC'
    ]);
    return $videos->posts;
}

// ============================================
// LEAD TRACKING SYSTEM (Tanah Clicks)
// ============================================

function tls_create_leads_table() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'tls_leads';
    $charset_collate = $wpdb->get_charset_collate();
    
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    
    $sql = "CREATE TABLE IF NOT EXISTS $table_name (
        id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        tanah_id BIGINT UNSIGNED NOT NULL,
        tanah_title VARCHAR(255),
        tanah_price DECIMAL(12,2),
        source VARCHAR(50) DEFAULT 'whatsapp',
        client_ip VARCHAR(45),
        user_agent TEXT,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_tanah (tanah_id),
        INDEX idx_created (created_at)
    ) $charset_collate;";
    
    dbDelta($sql);
}

add_action('after_switch_theme', function() {
    tls_create_leads_table();
    flush_rewrite_rules();
}, 1);

add_action('wp_ajax_tls_track_lead', 'tls_track_lead');
add_action('wp_ajax_nopriv_tls_track_lead', 'tls_track_lead');

function tls_track_lead() {
    check_ajax_referer('tls_theme_nonce', 'nonce');
    
    $tanah_id = isset($_POST['tanah_id']) ? intval($_POST['tanah_id']) : 0;
    
    if (!$tanah_id) {
        wp_send_json_error(['message' => 'Invalid tanah ID']);
        return;
    }
    
    global $wpdb;
    $table = $wpdb->prefix . 'tls_leads';
    
    $tanah = get_post($tanah_id);
    $price = get_post_meta($tanah_id, 'harga', true);
    
    $result = $wpdb->insert($table, [
        'tanah_id' => $tanah_id,
        'tanah_title' => $tanah ? sanitize_text_field($tanah->post_title) : '',
        'tanah_price' => floatval($price),
        'source' => 'whatsapp',
        'client_ip' => $_SERVER['REMOTE_ADDR'] ?? '',
        'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? ''
    ], ['%d', '%s', '%f', '%s', '%s', '%s']);
    
    if ($result) {
        wp_send_json_success(['lead_id' => $wpdb->insert_id]);
    } else {
        wp_send_json_error(['message' => 'Failed to track lead']);
    }
}

add_action('wp_enqueue_scripts', function() {
    wp_localize_script('tls-calculator', 'tls_ajax', [
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('tls_theme_nonce')
    ]);
}, 25);

function tls_leads_admin_page() {
    global $wpdb;
    $table = $wpdb->prefix . 'tls_leads';
    
    $leads = $wpdb->get_results("SELECT * FROM $table ORDER BY created_at DESC LIMIT 100", ARRAY_A);
    $total = $wpdb->get_var("SELECT COUNT(*) FROM $table");
    $today = $wpdb->get_var("SELECT COUNT(*) FROM $table WHERE DATE(created_at) = CURDATE()");
    $total_value = $wpdb->get_var("SELECT SUM(tanah_price) FROM $table");
    ?>
    <div class="wrap">
        <h1>Tanah Lead Tracking</h1>
        
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin: 30px 0;">
            <div class="stat-box">
                <h3><?php echo $total; ?></h3>
                <p>Total Leads</p>
            </div>
            <div class="stat-box">
                <h3><?php echo $today; ?></h3>
                <p>Today</p>
            </div>
            <div class="stat-box">
                <h3>RM <?php echo number_format($total_value ?: 0, 2); ?></h3>
                <p>Total Interest Value</p>
            </div>
        </div>
        
        <style>
            .stat-box { background: #fff; padding: 20px; border-radius: 8px; border-left: 4px solid #198754; }
            .stat-box h3 { margin: 0; font-size: 28px; color: #198754; }
            .stat-box p { margin: 5px 0 0; color: #666; }
            .leads-table { background: #fff; border-radius: 8px; overflow: hidden; margin-top: 20px; }
            .leads-table table { width: 100%; border-collapse: collapse; }
            .leads-table th { background: #f8f9fa; padding: 12px; text-align: left; font-weight: 600; }
            .leads-table td { padding: 12px; border-bottom: 1px solid #eee; }
        </style>
        
        <div class="leads-table">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Tanah</th>
                        <th>Harga</th>
                        <th>Source</th>
                        <th>Tarikh</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($leads as $lead): ?>
                    <tr>
                        <td><strong>#<?php echo $lead['id']; ?></strong></td>
                        <td>
                            <?php if ($lead['tanah_id']): ?>
                            <a href="<?php echo get_edit_post_link($lead['tanah_id']); ?>" target="_blank">
                                <?php echo esc_html($lead['tanah_title']); ?>
                            </a>
                            <?php else: ?>
                            <?php echo esc_html($lead['tanah_title']); ?>
                            <?php endif; ?>
                        </td>
                        <td>RM <?php echo number_format($lead['tanah_price'], 2); ?></td>
                        <td><span style="background:#25d366;color:#fff;padding:3px 8px;border-radius:4px;font-size:12px;">WhatsApp</span></td>
                        <td><?php echo date('d/m/Y H:i', strtotime($lead['created_at'])); ?></td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (empty($leads)): ?>
                    <tr><td colspan="5" style="text-align:center; padding:30px;">Tiada lead lagi.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php
}

add_action('wp_dashboard_setup', function() {
    wp_add_dashboard_widget('tls_tanah_leads_widget', '<span class="dashicons dashicons-chart-bar"></span> Tanah Leads', 'tls_tanah_leads_dashboard_widget');
});

function tls_tanah_leads_dashboard_widget() {
    global $wpdb;
    $table = $wpdb->prefix . 'tls_leads';
    
    $total = $wpdb->get_var("SELECT COUNT(*) FROM $table");
    $today = $wpdb->get_var("SELECT COUNT(*) FROM $table WHERE DATE(created_at) = CURDATE()");
    ?>
    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px;">
        <div style="text-align:center; padding:10px; background:#f0f0f0; border-radius:6px;">
            <h4 style="margin:0; color:#198754;"><?php echo $total; ?></h4>
            <small>Total Leads</small>
        </div>
        <div style="text-align:center; padding:10px; background:#f0f0f0; border-radius:6px;">
            <h4 style="margin:0; color:#198754;"><?php echo $today; ?></h4>
            <small>Today</small>
        </div>
    </div>
    <p style="margin-top:10px; font-size:12px;">
        <a href="<?php echo admin_url('admin.php?page=tls-leads'); ?>">View all leads →</a>
    </p>
    <?php
}

// ============================================
// LAND DEVELOPMENT CALCULATOR - FULL SYSTEM
// ============================================

class TLS_LDC_Database {
    
    public static function create_tables() {
        global $wpdb;
        $charset_collate = $wpdb->get_charset_collate();
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        
        // 1. AGENTS TABLE
        $agents_table = $wpdb->prefix . TLS_LDC_PREFIX . 'agents';
        $sql_agents = "CREATE TABLE IF NOT EXISTS $agents_table (
            id VARCHAR(20) PRIMARY KEY,
            name VARCHAR(100) NOT NULL,
            email VARCHAR(100) NOT NULL UNIQUE,
            phone VARCHAR(20) NOT NULL,
            password_hash VARCHAR(255) NOT NULL,
            is_admin TINYINT(1) DEFAULT 0,
            is_active TINYINT(1) DEFAULT 1,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            last_login DATETIME NULL,
            total_estimates INT DEFAULT 0
        ) $charset_collate;";
        dbDelta($sql_agents);
        
        // 2. USERS TABLE (for clients)
        $users_table = $wpdb->prefix . TLS_LDC_PREFIX . 'users';
        $sql_users = "CREATE TABLE IF NOT EXISTS $users_table (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(100) NOT NULL,
            email VARCHAR(100) NOT NULL UNIQUE,
            phone VARCHAR(20) NOT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        ) $charset_collate;";
        dbDelta($sql_users);
        
        // 3. SESSIONS TABLE
        $sessions_table = $wpdb->prefix . TLS_LDC_PREFIX . 'sessions';
        $sql_sessions = "CREATE TABLE IF NOT EXISTS $sessions_table (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            agent_id VARCHAR(20) NOT NULL,
            password_hash VARCHAR(255) NOT NULL,
            expires_at DATETIME NOT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        ) $charset_collate;";
        dbDelta($sql_sessions);
        
        // 4. ESTIMATES TABLE (Leads)
        $estimates_table = $wpdb->prefix . TLS_LDC_PREFIX . 'estimates';
        $sql_estimates = "CREATE TABLE IF NOT EXISTS $estimates_table (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            agent_id VARCHAR(20) NOT NULL,
            client_name VARCHAR(100) NOT NULL,
            client_email VARCHAR(100) NOT NULL,
            client_phone VARCHAR(20) NOT NULL,
            land_size DECIMAL(10,2) NOT NULL,
            land_unit VARCHAR(20) NOT NULL DEFAULT 'sq ft',
            location VARCHAR(100) NOT NULL,
            location_multiplier DECIMAL(4,2) DEFAULT 1.00,
            items JSON NOT NULL,
            total_cost DECIMAL(12,2) NOT NULL,
            pdf_url TEXT NULL,
            lead_type VARCHAR(20) DEFAULT 'development',
            source VARCHAR(10) DEFAULT 'web',
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_agent (agent_id),
            INDEX idx_client_email (client_email),
            INDEX idx_created (created_at)
        ) $charset_collate;";
        dbDelta($sql_estimates);
        
        // 5. PRICING TABLE
        $pricing_table = $wpdb->prefix . TLS_LDC_PREFIX . 'pricing';
        $sql_pricing = "CREATE TABLE IF NOT EXISTS $pricing_table (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            category VARCHAR(100) NOT NULL,
            category_icon VARCHAR(10) DEFAULT '📦',
            category_description TEXT,
            name VARCHAR(200) NOT NULL,
            unit VARCHAR(50) NOT NULL,
            unit_price DECIMAL(10,2) NOT NULL,
            is_active TINYINT(1) DEFAULT 1,
            sort_order INT DEFAULT 0
        ) $charset_collate;";
        dbDelta($sql_pricing);

        // 6. CONSTRUCTION TEMPLATES TABLE
        $templates_table = $wpdb->prefix . TLS_LDC_PREFIX . 'templates';
        $sql_templates = "CREATE TABLE IF NOT EXISTS $templates_table (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(200) NOT NULL,
            description TEXT,
            template_type VARCHAR(50) DEFAULT 'construction',
            icon VARCHAR(10) DEFAULT '<i class="material-icons">construction</i>',
            is_active TINYINT(1) DEFAULT 1,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) $charset_collate;";
        dbDelta($sql_templates);

        // 7. TEMPLATE ITEMS TABLE
        $template_items_table = $wpdb->prefix . TLS_LDC_PREFIX . 'template_items';
        $sql_template_items = "CREATE TABLE IF NOT EXISTS $template_items_table (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            template_id BIGINT UNSIGNED NOT NULL,
            pricing_id BIGINT UNSIGNED NOT NULL,
            default_quantity DECIMAL(10,2) NOT NULL DEFAULT 1.00,
            notes TEXT,
            INDEX idx_template (template_id)
        ) $charset_collate;";
        dbDelta($sql_template_items);

        // 8. LOCATIONS TABLE
        $locations_table = $wpdb->prefix . TLS_LDC_PREFIX . 'locations';
        $sql_locations = "CREATE TABLE IF NOT EXISTS $locations_table (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(100) NOT NULL,
            area VARCHAR(100) NOT NULL,
            district VARCHAR(100) NOT NULL,
            multiplier DECIMAL(4,2) NOT NULL DEFAULT 1.00,
            description TEXT,
            is_active TINYINT(1) DEFAULT 1
        ) $charset_collate;";
        dbDelta($sql_locations);

        // 9. DEMO COMPLETIONS TABLE
        $demo_table = $wpdb->prefix . TLS_LDC_PREFIX . 'demo_completions';
        $sql_demo = "CREATE TABLE IF NOT EXISTS $demo_table (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            agent_id VARCHAR(20) NOT NULL UNIQUE,
            completed_at DATETIME DEFAULT CURRENT_TIMESTAMP
        ) $charset_collate;";
        dbDelta($sql_demo);
        
        // Insert default data
        self::insert_default_pricing();
        self::insert_default_agents();
        self::insert_default_locations();
    }
    
    public static function insert_default_pricing() {
        global $wpdb;
        $table = $wpdb->prefix . TLS_LDC_PREFIX . 'pricing';
        
        $count = $wpdb->get_var("SELECT COUNT(*) FROM $table");
        if ($count > 0) return;
        
        $items = [
            ['Land Preparation', '', 'Clearing, leveling, and preparing your land', 'Jungle/Vegetation Clearing', 'per sq ft', 0.50, 1],
            ['Land Preparation', '', 'Clearing, leveling, and preparing your land', 'Land Leveling/Grading', 'per sq ft', 0.80, 2],
            ['Land Preparation', '', 'Clearing, leveling, and preparing your land', 'Tree Removal (Small)', 'per tree', 200.00, 3],
            ['Fencing', '', 'Secure your property with various fencing options', 'Chain Link Fence', 'per meter', 50.00, 1],
            ['Fencing', '', 'Secure your property with various fencing options', 'Concrete Wall Fence', 'per meter', 200.00, 2],
            ['Fencing', '', 'Secure your property with various fencing options', 'Wooden Fence', 'per meter', 80.00, 3],
            ['Fencing', '', 'Secure your property with various fencing options', 'Main Gate (Single)', 'per unit', 3500.00, 4],
            ['Utilities', '', 'Essential utilities - electricity, water, and sewage', 'TNB Electricity Connection', 'per connection', 12000.00, 1],
            ['Utilities', '', 'Essential utilities - electricity, water, and sewage', 'Water Pipe Connection', 'per meter', 40.00, 2],
            ['Utilities', '', 'Essential utilities - electricity, water, and sewage', 'Septic Tank Installation', 'per unit', 6500.00, 3],
            ['Utilities', '', 'Essential utilities - electricity, water, and sewage', 'Water Tank (1000L)', 'per unit', 1500.00, 4],
            ['Road Access', '', 'Build access roads and driveways', 'Gravel Road (Single Lane)', 'per meter', 80.00, 1],
            ['Road Access', '', 'Build access roads and driveways', 'Concrete Road (Single Lane)', 'per meter', 200.00, 2],
            ['Road Access', '', 'Build access roads and driveways', 'Drainage System', 'per meter', 50.00, 3],
            ['Building', '', 'Construction of houses and structures', 'Basic House Construction', 'per sq ft', 150.00, 1],
            ['Building', '', 'Construction of houses and structures', 'Premium House Construction', 'per sq ft', 300.00, 2],
            ['Building', '', 'Construction of houses and structures', 'Guard House', 'per unit', 15000.00, 3],
            ['Permits & Legal', '', 'Legal requirements and government permits', 'Building Permit', 'per application', 3000.00, 1],
            ['Permits & Legal', '', 'Legal requirements and government permits', 'Land Survey Fee', 'per survey', 4500.00, 2],
            ['Permits & Legal', '', 'Legal requirements and government permits', 'Planning Approval', 'per application', 2500.00, 3],
            ['Miscellaneous', '', 'Additional features and improvements', 'Security Camera System (4 cameras)', 'per system', 5000.00, 1],
            ['Miscellaneous', '', 'Additional features and improvements', 'Retaining Wall', 'per meter', 250.00, 2],
            ['Miscellaneous', '', 'Additional features and improvements', 'Street Light Installation', 'per unit', 1200.00, 3],
        ];
        
        foreach ($items as $item) {
            $wpdb->insert($table, [
                'category' => $item[0],
                'category_icon' => $item[1],
                'category_description' => $item[2],
                'name' => $item[3],
                'unit' => $item[4],
                'unit_price' => $item[5],
                'sort_order' => $item[6],
                'is_active' => 1
            ]);
        }
    }
    
    public static function insert_default_agents() {
        global $wpdb;
        $table = $wpdb->prefix . TLS_LDC_PREFIX . 'agents';
        
        $count = $wpdb->get_var("SELECT COUNT(*) FROM $table");
        if ($count > 0) return;
        
        $default_password = password_hash('demo123', PASSWORD_BCRYPT);
        
        $agents = [
            ['TLS001', 'Super Admin', 'admin@tanahlotsabah.com', '60123456789', 1],
            ['TLS002', 'Admin Sarah Lee', 'sarah@tanahlotsabah.com', '60129876543', 1],
            ['TLS003', 'Admin John Tan', 'john@tanahlotsabah.com', '60125551234', 1],
            ['TLS004', 'Agent Michael Wong', 'michael@tanahlotsabah.com', '60123334444', 0],
            ['TLS005', 'Agent Lisa Chen', 'lisa@tanahlotsabah.com', '60125555666', 0],
        ];
        
        foreach ($agents as $agent) {
            $wpdb->insert($table, [
                'id' => $agent[0],
                'name' => $agent[1],
                'email' => $agent[2],
                'phone' => $agent[3],
                'password_hash' => $default_password,
                'is_admin' => $agent[4],
                'is_active' => 1
            ]);
        }
    }
    
    public static function insert_default_locations() {
        global $wpdb;
        $table = $wpdb->prefix . TLS_LDC_PREFIX . 'locations';
        
        $count = $wpdb->get_var("SELECT COUNT(*) FROM $table");
        if ($count > 0) return;
        
        $locations = [
            ['Kota Kinabalu City Center', 'Kota Kinabalu', 'Kota Kinabalu', 1.5],
            ['Likas, KK', 'Kota Kinabalu', 'Kota Kinabalu', 1.35],
            ['Luyang, KK', 'Kota Kinabalu', 'Kota Kinabalu', 1.3],
            ['Penampang Town', 'Penampang', 'Penampang', 1.2],
            ['Donggongon, Penampang', 'Penampang', 'Penampang', 1.15],
            ['Putatan', 'Putatan', 'Penampang', 1.1],
            ['Papar Town', 'Papar', 'Papar', 0.95],
            ['Tuaran Town', 'Tuaran', 'Tuaran', 1.0],
            ['Tamparuli', 'Tamparuli', 'Tuaran', 0.95],
            ['Inanam', 'Inanam', 'Kota Kinabalu', 1.15],
            ['Ranau Town', 'Ranau', 'Ranau', 0.85],
            ['Kundasang', 'Kundasang', 'Ranau', 0.9],
            ['Sandakan Town', 'Sandakan', 'Sandakan', 1.15],
            ['Tawau Town', 'Tawau', 'Tawau', 1.1],
            ['Lahad Datu', 'Lahad Datu', 'Lahad Datu', 0.95],
            ['Semporna', 'Semporna', 'Semporna', 0.9],
            ['Keningau', 'Keningau', 'Keningau', 0.85],
            ['Beaufort', 'Beaufort', 'Beaufort', 0.85],
            ['Kudat', 'Kudat', 'Kudat', 0.9],
            ['Kota Belud', 'Kota Belud', 'Kota Belud', 0.9],
        ];
        
        foreach ($locations as $loc) {
            $wpdb->insert($table, [
                'name' => $loc[0],
                'area' => $loc[1],
                'district' => $loc[2],
                'multiplier' => $loc[3],
                'is_active' => 1
            ]);
        }
    }
    
    // PRICING FUNCTIONS
    public static function get_pricing() {
        global $wpdb;
        $table = $wpdb->prefix . TLS_LDC_PREFIX . 'pricing';
        return $wpdb->get_results("SELECT * FROM $table WHERE is_active = 1 ORDER BY category, sort_order, name", ARRAY_A);
    }
    
    public static function get_pricing_by_category() {
        $items = self::get_pricing();
        $grouped = [];
        foreach ($items as $item) {
            $cat = $item['category'];
            if (!isset($grouped[$cat])) {
                $grouped[$cat] = [
                    'icon' => $item['category_icon'],
                    'description' => $item['category_description'],
                    'items' => []
                ];
            }
            $grouped[$cat]['items'][] = $item;
        }
        return $grouped;
    }
    
    // AGENT FUNCTIONS
    public static function get_agent($agent_id) {
        global $wpdb;
        $table = $wpdb->prefix . TLS_LDC_PREFIX . 'agents';
        return $wpdb->get_row($wpdb->prepare("SELECT * FROM $table WHERE id = %s", $agent_id), ARRAY_A);
    }
    
    public static function get_all_agents() {
        global $wpdb;
        $table = $wpdb->prefix . TLS_LDC_PREFIX . 'agents';
        return $wpdb->get_results("SELECT * FROM $table ORDER BY is_admin DESC, id ASC", ARRAY_A);
    }
    
    public static function validate_agent_login($agent_id, $password) {
        $agent = self::get_agent($agent_id);
        if (!$agent) return false;
        if (!$agent['is_active']) return false;
        return password_verify($password, $agent['password_hash']);
    }
    
    // USER FUNCTIONS
    public static function create_user($name, $email, $phone) {
        global $wpdb;
        $table = $wpdb->prefix . TLS_LDC_PREFIX . 'users';
        
        $existing = $wpdb->get_row($wpdb->prepare("SELECT id FROM $table WHERE email = %s", $email));
        if ($existing) {
            return $existing->id;
        }
        
        $wpdb->insert($table, [
            'name' => sanitize_text_field($name),
            'email' => sanitize_email($email),
            'phone' => sanitize_text_field($phone)
        ]);
        
        return $wpdb->insert_id;
    }
    
    public static function get_user_by_email($email) {
        global $wpdb;
        $table = $wpdb->prefix . TLS_LDC_PREFIX . 'users';
        return $wpdb->get_row($wpdb->prepare("SELECT * FROM $table WHERE email = %s", $email), ARRAY_A);
    }
    
    // ESTIMATE/LEAD FUNCTIONS
    public static function save_estimate($data) {
        global $wpdb;
        $table = $wpdb->prefix . TLS_LDC_PREFIX . 'estimates';
        
        $agent_id = isset($data['agent_id']) && !empty($data['agent_id']) 
            ? sanitize_text_field($data['agent_id']) 
            : 'TLS001';
        
        $wpdb->insert($table, [
            'agent_id' => $agent_id,
            'client_name' => sanitize_text_field($data['client_name']),
            'client_email' => sanitize_email($data['client_email']),
            'client_phone' => sanitize_text_field($data['client_phone']),
            'land_size' => floatval($data['land_size']),
            'land_unit' => sanitize_text_field($data['land_unit']),
            'location' => sanitize_text_field($data['location']),
            'location_multiplier' => isset($data['location_multiplier']) ? floatval($data['location_multiplier']) : 1.0,
            'items' => json_encode($data['items']),
            'total_cost' => floatval($data['total_cost']),
            'lead_type' => isset($data['lead_type']) ? sanitize_text_field($data['lead_type']) : 'development',
            'source' => isset($data['source']) ? sanitize_text_field($data['source']) : 'web'
        ]);
        
        return $wpdb->insert_id;
    }
    
    public static function get_all_estimates($limit = 100) {
        global $wpdb;
        $table = $wpdb->prefix . TLS_LDC_PREFIX . 'estimates';
        return $wpdb->get_results("SELECT * FROM $table ORDER BY created_at DESC LIMIT $limit", ARRAY_A);
    }
    
    public static function get_estimate($id) {
        global $wpdb;
        $table = $wpdb->prefix . TLS_LDC_PREFIX . 'estimates';
        return $wpdb->get_row($wpdb->prepare("SELECT * FROM $table WHERE id = %d", $id), ARRAY_A);
    }
    
    public static function get_agent_estimates($agent_id) {
        global $wpdb;
        $table = $wpdb->prefix . TLS_LDC_PREFIX . 'estimates';
        return $wpdb->get_results($wpdb->prepare("SELECT * FROM $table WHERE agent_id = %s ORDER BY created_at DESC", $agent_id), ARRAY_A);
    }
    
    public static function get_estimate_stats() {
        global $wpdb;
        $table = $wpdb->prefix . TLS_LDC_PREFIX . 'estimates';
        
        $total = $wpdb->get_var("SELECT COUNT(*) FROM $table");
        $total_value = $wpdb->get_var("SELECT SUM(total_cost) FROM $table");
        $today = $wpdb->get_var("SELECT COUNT(*) FROM $table WHERE DATE(created_at) = CURDATE()");
        $week = $wpdb->get_var("SELECT COUNT(*) FROM $table WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)");
        
        return [
            'total' => (int)$total,
            'total_value' => floatval($total_value ?: 0),
            'today' => (int)$today,
            'week' => (int)$week
        ];
    }
    
    // LOCATION FUNCTIONS
    public static function search_locations($query) {
        global $wpdb;
        $table = $wpdb->prefix . TLS_LDC_PREFIX . 'locations';
        $search = '%' . $wpdb->esc_like($query) . '%';
        
        return $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM $table WHERE is_active = 1 AND (name LIKE %s OR area LIKE %s OR district LIKE %s) ORDER BY name LIMIT 10",
            $search, $search, $search
        ), ARRAY_A);
    }
    
    public static function get_all_locations() {
        global $wpdb;
        $table = $wpdb->prefix . TLS_LDC_PREFIX . 'locations';
        return $wpdb->get_results("SELECT * FROM $table WHERE is_active = 1 ORDER BY name", ARRAY_A);
    }
}

// ============================================
// AJAX HANDLERS
// ============================================

// Generate PDF
add_action('wp_ajax_tls_ldc_generate_pdf', 'tls_ldc_generate_pdf');
add_action('wp_ajax_nopriv_tls_ldc_generate_pdf', 'tls_ldc_generate_pdf');

function tls_ldc_generate_pdf() {
    check_ajax_referer('tls_ldc_nonce', 'nonce');
    
    $data = isset($_POST['estimate_data']) ? $_POST['estimate_data'] : [];
    
    if (empty($data['client_name']) || empty($data['client_email'])) {
        wp_send_json_error(['message' => 'Maklumat klien diperlukan']);
        return;
    }
    
    // Create user record
    $user_id = TLS_LDC_Database::create_user(
        $data['client_name'],
        $data['client_email'],
        isset($data['client_phone']) ? $data['client_phone'] : ''
    );
    
    // Save estimate
    $estimate_id = TLS_LDC_Database::save_estimate($data);
    
    // Generate HTML report
    $upload_dir = wp_upload_dir();
    $pdf_dir = $upload_dir['basedir'] . '/tls-estimates/';
    if (!file_exists($pdf_dir)) {
        wp_mkdir_p($pdf_dir);
    }
    
    $filename = 'estimate_' . $estimate_id . '_' . time() . '.html';
    $file_path = $pdf_dir . $filename;
    $file_url = $upload_dir['baseurl'] . '/tls-estimates/' . $filename;
    
    $html = tls_ldc_generate_html_report($data, $estimate_id);
    file_put_contents($file_path, $html);
    
    wp_send_json_success([
        'pdf_url' => $file_url,
        'estimate_id' => $estimate_id,
        'message' => 'PDF berjaya dijana!'
    ]);
}

// Get Pricing (for AJAX)
add_action('wp_ajax_tls_ldc_get_pricing', 'tls_ldc_get_pricing');
add_action('wp_ajax_nopriv_tls_ldc_get_pricing', 'tls_ldc_get_pricing');

function tls_ldc_get_pricing() {
    $pricing = TLS_LDC_Database::get_pricing_by_category();
    wp_send_json_success(['pricing' => $pricing]);
}

// Search Locations (for AJAX)
add_action('wp_ajax_tls_ldc_search_locations', 'tls_ldc_search_locations');
add_action('wp_ajax_nopriv_tls_ldc_search_locations', 'tls_ldc_search_locations');

function tls_ldc_search_locations() {
    $query = isset($_GET['q']) ? sanitize_text_field($_GET['q']) : '';
    if (strlen($query) < 2) {
        wp_send_json_success(['locations' => []]);
        return;
    }
    $locations = TLS_LDC_Database::search_locations($query);
    wp_send_json_success(['locations' => $locations]);
}

// ============================================
// MORTGAGE CALCULATOR (REALPRESS STYLE)
// ============================================
add_shortcode('mortgage_calculator', 'tls_mortgage_calculator');

function tls_mortgage_calculator($atts) {
    $atts = shortcode_atts([
        'property_price' => 0,
        'title' => 'Mortgage Calculator'
    ], $atts);
    
    ob_start();
    ?>
    <div class="tls-mortgage-calculator" id="mortgageCalculator">
        <div class="mc-inputs">
            <div class="mc-field">
                <label>Property Price (RM)</label>
                <input type="number" id="mc-property-price" value="<?php echo esc_attr($atts['property_price']); ?>" class="mc-input">
            </div>
            <div class="mc-field">
                <label>Down Payment (%)</label>
                <input type="number" id="mc-down-payment" value="20" class="mc-input" min="0" max="100">
            </div>
            <div class="mc-field">
                <label>Loan Term (Years)</label>
                <select id="mc-loan-term" class="mc-input">
                    <option value="10">10 years</option>
                    <option value="15">15 years</option>
                    <option value="20">20 years</option>
                    <option value="25" selected>25 years</option>
                    <option value="30">30 years</option>
                </select>
            </div>
            <div class="mc-field">
                <label>Interest Rate (% p.a.)</label>
                <input type="number" id="mc-interest-rate" value="4.5" class="mc-input" step="0.1" min="0">
            </div>
            <div class="mc-field">
                <label>Property Tax (RM/year)</label>
                <input type="number" id="mc-property-tax" value="0" class="mc-input">
            </div>
            <div class="mc-field">
                <label>Home Insurance (RM/year)</label>
                <input type="number" id="mc-home-insurance" value="0" class="mc-input">
            </div>
            <button type="button" class="mc-calculate-btn" onclick="calculateMortgage()">Calculate</button>
        </div>
        <div class="mc-results" style="display:none;">
            <div class="mc-monthly">
                <span class="mc-label">Monthly Payment</span>
                <span class="mc-amount" id="mc-monthly-total">RM 0</span>
            </div>
            <div class="mc-breakdown">
                <div class="mc-item">
                    <span>Principal & Interest</span>
                    <span id="mc-pi">RM 0</span>
                </div>
                <div class="mc-item">
                    <span>Property Tax</span>
                    <span id="mc-tax">RM 0</span>
                </div>
                <div class="mc-item">
                    <span>Home Insurance</span>
                    <span id="mc-insurance">RM 0</span>
                </div>
            </div>
            <canvas id="mc-chart" width="200" height="200"></canvas>
        </div>
    </div>
    <script>
    function calculateMortgage() {
        var price = parseFloat(document.getElementById('mc-property-price').value) || 0;
        var downPercent = parseFloat(document.getElementById('mc-down-payment').value) || 20;
        var years = parseInt(document.getElementById('mc-loan-term').value) || 25;
        var rate = parseFloat(document.getElementById('mc-interest-rate').value) || 4.5;
        var tax = parseFloat(document.getElementById('mc-property-tax').value) || 0;
        var insurance = parseFloat(document.getElementById('mc-home-insurance').value) || 0;
        
        var loanAmount = price * (1 - downPercent / 100);
        var monthlyRate = rate / 100 / 12;
        var numPayments = years * 12;
        
        var monthlyPI = 0;
        if (monthlyRate > 0) {
            monthlyPI = loanAmount * (monthlyRate * Math.pow(1 + monthlyRate, numPayments)) / (Math.pow(1 + monthlyRate, numPayments) - 1);
        } else {
            monthlyPI = loanAmount / numPayments;
        }
        
        var monthlyTax = tax / 12;
        var monthlyInsurance = insurance / 12;
        var totalMonthly = monthlyPI + monthlyTax + monthlyInsurance;
        
        document.getElementById('mc-monthly-total').textContent = 'RM ' + totalMonthly.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
        document.getElementById('mc-pi').textContent = 'RM ' + monthlyPI.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
        document.getElementById('mc-tax').textContent = 'RM ' + monthlyTax.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
        document.getElementById('mc-insurance').textContent = 'RM ' + monthlyInsurance.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
        
        document.querySelector('.mc-results').style.display = 'block';
    }
    </script>
    <style>
    .tls-mortgage-calculator { background: #fff; border-radius: 12px; padding: 24px; box-shadow: 0 2px 8px rgba(0,0,0,.08); max-width: 400px; }
    .mc-field { margin-bottom: 16px; }
    .mc-field label { display: block; font-size: 14px; color: #64748b; margin-bottom: 4px; }
    .mc-input { width: 100%; padding: 10px 12px; border: 1px solid #e2e8f0; border-radius: 8px; font-size: 16px; }
    .mc-calculate-btn { width: 100%; padding: 12px; background: #16a34a; color: #fff; border: none; border-radius: 8px; font-size: 16px; font-weight: 600; cursor: pointer; }
    .mc-calculate-btn:hover { background: #15803d; }
    .mc-results { margin-top: 24px; padding-top: 24px; border-top: 1px solid #e2e8f0; }
    .mc-monthly { text-align: center; margin-bottom: 20px; }
    .mc-label { display: block; font-size: 14px; color: #64748b; }
    .mc-amount { display: block; font-size: 28px; font-weight: 700; color: #16a34a; }
    .mc-breakdown { font-size: 14px; }
    .mc-breakdown .mc-item { display: flex; justify-content: space-between; padding: 8px 0; border-bottom: 1px solid #f1f5f9; }
    </style>
    <?php
    return ob_get_clean();
}

// ============================================
// TANAH DETAILS METABOX
// ============================================
add_action('add_meta_boxes', function() {
    add_meta_box('tls_tanah_details', 'Property Details', 'tls_render_tanah_details', 'tanah', 'normal', 'high');
}, 5);

function tls_render_tanah_details($post) {
    wp_nonce_field('tls_tanah_details_nonce', 'tls_tanah_details_nonce');

    $harga = get_post_meta($post->ID, '_tanah_harga', true);
    $keluasan = get_post_meta($post->ID, '_tanah_keluasan', true);
    $jenis_geran = get_post_meta($post->ID, '_tanah_jenis_geran', true);
    $zoning = get_post_meta($post->ID, '_tanah_zoning', true);
    $property_id = get_post_meta($post->ID, '_tanah_property_id', true);
    $latitude = get_post_meta($post->ID, '_tanah_latitude', true);
    $longitude = get_post_meta($post->ID, '_tanah_longitude', true);
    $building_size = get_post_meta($post->ID, '_tanah_building_size', true);
    $building_unit = get_post_meta($post->ID, '_tanah_building_unit', true);
    $video_url = get_post_meta($post->ID, '_tanah_video_url', true);
    $virtual_tour_url = get_post_meta($post->ID, '_tanah_virtual_tour_url', true);
    $verified = get_post_meta($post->ID, '_tanah_verified', true);
    ?>
    <style>
        .tls-meta-table { width: 100%; border-collapse: collapse; }
        .tls-meta-table th { width: 25%; padding: 12px; text-align: left; font-weight: 600; background: #f8f9fa; border-bottom: 1px solid #e2e8f0; }
        .tls-meta-table td { padding: 12px; border-bottom: 1px solid #e2e8f0; }
        .tls-meta-table input[type="text"],
        .tls-meta-table input[type="number"],
        .tls-meta-table select { width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px; }
        .tls-meta-table .description { margin-top: 5px; font-size: 12px; color: #666; }
        .tls-meta-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; }
    </style>
    <table class="tls-meta-table">
        <tr>
            <th><label for="tanah_property_id">Property ID</label></th>
            <td>
                <input type="text" id="tanah_property_id" name="tanah_property_id" value="<?php echo esc_attr($property_id); ?>" placeholder="e.g., TLS-001">
                <p class="description">Unique identifier for this property</p>
            </td>
        </tr>
        <tr>
            <th><label for="tanah_harga">Price (RM)</label></th>
            <td>
                <input type="number" id="tanah_harga" name="tanah_harga" value="<?php echo esc_attr($harga); ?>" placeholder="0" step="1000">
                <p class="description">Total price in Malaysian Ringgit</p>
            </td>
        </tr>
        <tr>
            <th><label for="tanah_keluasan">Land Size (Acres)</label></th>
            <td>
                <input type="text" id="tanah_keluasan" name="tanah_keluasan" value="<?php echo esc_attr($keluasan); ?>" placeholder="0.00">
                <p class="description">Land area in acres/ekar</p>
            </td>
        </tr>
        <tr>
            <th><label for="tanah_jenis_geran">Grant Type</label></th>
            <td>
                <select id="tanah_jenis_geran" name="tanah_jenis_geran">
                    <option value="">Select Grant Type</option>
                    <option value="CL" <?php selected($jenis_geran, 'CL'); ?>>Country Lease (CL)</option>
                    <option value="NT" <?php selected($jenis_geran, 'NT'); ?>>Native Title (NT)</option>
                    <option value="P" <?php selected($jenis_geran, 'P'); ?>>Pajakan (P)</option>
                    <option value="Hakmilik" <?php selected($jenis_geran, 'Hakmilik'); ?>>Freehold (Hakmilik)</option>
                </select>
                <p class="description">Type of land grant/title</p>
            </td>
        </tr>
        <tr>
            <th><label for="tanah_zoning">Zoning</label></th>
            <td>
                <select id="tanah_zoning" name="tanah_zoning">
                    <option value="">Select Zoning</option>
                    <option value="Kediaman" <?php selected($zoning, 'Kediaman'); ?>>Residential (Kediaman)</option>
                    <option value="Komersial" <?php selected($zoning, 'Komersial'); ?>>Commercial (Komersial)</option>
                    <option value="Perindustrian" <?php selected($zoning, 'Perindustrian'); ?>>Industrial (Perindustrian)</option>
                    <option value="Pertanian" <?php selected($zoning, 'Pertanian'); ?>>Agriculture (Pertanian)</option>
                    <option value="Campuran" <?php selected($zoning, 'Campuran'); ?>>Mixed Use (Campuran)</option>
                </select>
                <p class="description">Zoning classification</p>
            </td>
        </tr>
        <tr>
            <th><label>GPS Coordinates</label></th>
            <td>
                <div class="tls-meta-grid">
                    <div>
                        <input type="text" id="tanah_latitude" name="tanah_latitude" value="<?php echo esc_attr($latitude); ?>" placeholder="Latitude (e.g., 5.9804)">
                    </div>
                    <div>
                        <input type="text" id="tanah_longitude" name="tanah_longitude" value="<?php echo esc_attr($longitude); ?>" placeholder="Longitude (e.g., 116.0735)">
                    </div>
                </div>
                <p class="description">Location coordinates for map display</p>
            </td>
        </tr>
        <tr>
            <th><label>Building Size (Optional)</label></th>
            <td>
                <div class="tls-meta-grid">
                    <div>
                        <input type="text" id="tanah_building_size" name="tanah_building_size" value="<?php echo esc_attr($building_size); ?>" placeholder="0">
                    </div>
                    <div>
                        <select id="tanah_building_unit" name="tanah_building_unit">
                            <option value="sqft" <?php selected($building_unit, 'sqft'); ?>>Square Feet</option>
                            <option value="sqm" <?php selected($building_unit, 'sqm'); ?>>Square Meters</option>
                        </select>
                    </div>
                </div>
                <p class="description">Size of buildings on the property (if any)</p>
            </td>
        </tr>
        <tr>
            <th><label for="tanah_video_url">Video URL</label></th>
            <td>
                <input type="text" id="tanah_video_url" name="tanah_video_url" value="<?php echo esc_attr($video_url); ?>" placeholder="https://www.youtube.com/watch?v=...">
                <p class="description">YouTube or Vimeo video URL</p>
            </td>
        </tr>
        <tr>
            <th><label for="tanah_virtual_tour_url">Virtual Tour URL</label></th>
            <td>
                <input type="text" id="tanah_virtual_tour_url" name="tanah_virtual_tour_url" value="<?php echo esc_attr($virtual_tour_url); ?>" placeholder="https://...">
                <p class="description">360° virtual tour or 3D walkthrough URL</p>
            </td>
        </tr>
        <tr>
            <th><label for="tanah_verified">Verified Property</label></th>
            <td>
                <label>
                    <input type="checkbox" id="tanah_verified" name="tanah_verified" value="1" <?php checked($verified, '1'); ?>>
                    Mark this property as verified
                </label>
                <p class="description">Show verified badge on listing</p>
            </td>
        </tr>
    </table>
    <?php
}

add_action('save_post_tanah', function($post_id) {
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
    if (!isset($_POST['tls_tanah_details_nonce']) || !wp_verify_nonce($_POST['tls_tanah_details_nonce'], 'tls_tanah_details_nonce')) return;
    if (!current_user_can('edit_post', $post_id)) return;

    $fields = [
        'tanah_property_id' => '_tanah_property_id',
        'tanah_harga' => '_tanah_harga',
        'tanah_keluasan' => '_tanah_keluasan',
        'tanah_jenis_geran' => '_tanah_jenis_geran',
        'tanah_zoning' => '_tanah_zoning',
        'tanah_latitude' => '_tanah_latitude',
        'tanah_longitude' => '_tanah_longitude',
        'tanah_building_size' => '_tanah_building_size',
        'tanah_building_unit' => '_tanah_building_unit',
        'tanah_video_url' => '_tanah_video_url',
        'tanah_virtual_tour_url' => '_tanah_virtual_tour_url',
    ];

    foreach ($fields as $field => $meta_key) {
        if (isset($_POST[$field])) {
            update_post_meta($post_id, $meta_key, sanitize_text_field($_POST[$field]));
        }
    }

    // Handle checkbox
    $verified = isset($_POST['tanah_verified']) ? '1' : '0';
    update_post_meta($post_id, '_tanah_verified', $verified);
}, 10, 1);

// ============================================
// PROPERTY GALLERY (MULTIPLE IMAGES)
// ============================================
add_action('add_meta_boxes', function() {
    add_meta_box('tls_tanah_gallery', 'Property Gallery', 'tls_render_tanah_gallery', 'tanah', 'normal', 'high');
});

function tls_render_tanah_gallery($post) {
    $gallery = get_post_meta($post->ID, '_tanah_gallery', true);
    $gallery_ids = $gallery ? explode(',', $gallery) : [];
    ?>
    <div class="tls-gallery-wrapper">
        <input type="hidden" name="tls_gallery_ids" id="tls_gallery_ids" value="<?php echo esc_attr($gallery); ?>">
        <div class="tls-gallery-preview" id="tlsGalleryPreview" style="display:flex;flex-wrap:wrap;gap:10px;margin-bottom:10px;">
            <?php foreach ($gallery_ids as $img_id): ?>
                <?php if ($img_id): $img_url = wp_get_attachment_url($img_id); ?>
                <div class="tls-gallery-item" style="position:relative;width:100px;height:100px;overflow:hidden;border-radius:8px;">
                    <img src="<?php echo esc_attr($img_url); ?>" style="width:100%;height:100%;object-fit:cover;">
                    <button type="button" class="tls-remove-gallery" data-id="<?php echo $img_id; ?>" style="position:absolute;top:2px;right:2px;background:#dc3545;color:#fff;border:none;border-radius:50%;width:20px;height:20px;cursor:pointer;">×</button>
                </div>
                <?php endif; ?>
            <?php endforeach; ?>
        </div>
        <button type="button" class="button" id="tlsUploadGallery">Add Images</button>
    </div>
    <p class="description">Upload multiple images for this property. First image will be the main photo.</p>
    <script>
    jQuery(document).ready(function($) {
        var galleryFrame;
        $('#tlsUploadGallery').on('click', function(e) {
            e.preventDefault();
            if (galleryFrame) {
                galleryFrame.open();
                return;
            }
            galleryFrame = wp.media({
                title: 'Select Property Images',
                button: { text: 'Add to Gallery' },
                multiple: true
            });
            galleryFrame.on('select', function() {
                var attachments = galleryFrame.state().get('selection').map(function(att) {
                    return att.id;
                }).join(',');
                var current = $('#tls_gallery_ids').val();
                var newVal = current ? current + ',' + attachments : attachments;
                $('#tls_gallery_ids').val(newVal);
                location.reload();
            });
            galleryFrame.open();
        });
        $('.tls-remove-gallery').on('click', function() {
            var id = $(this).data('id');
            var current = $('#tls_gallery_ids').val().split(',').filter(function(x) { return x != id; }).join(',');
            $('#tls_gallery_ids').val(current);
            $(this).parent().remove();
        });
    });
    </script>
    <?php
}

add_action('save_post_tanah', function($post_id) {
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
    if (isset($_POST['tls_gallery_ids'])) {
        update_post_meta($post_id, '_tanah_gallery', sanitize_text_field($_POST['tls_gallery_ids']));
    }
});

// ============================================
// FLOOR PLANS
// ============================================
add_action('add_meta_boxes', function() {
    add_meta_box('tls_tanah_floor_plans', 'Floor Plans / Layout', 'tls_render_floor_plans', 'tanah', 'normal', 'high');
});

function tls_render_floor_plans($post) {
    $floor_plans = get_post_meta($post->ID, '_tanah_floor_plans', true);
    $plans = $floor_plans ? json_decode($floor_plans, true) : [];
    ?>
    <div class="tls-floor-plans-wrapper">
        <div id="tlsFloorPlansContainer">
            <?php if (!empty($plans)): foreach ($plans as $i => $plan): ?>
            <div class="tls-floor-plan-item" style="background:#f8f9fa;padding:12px;margin-bottom:10px;border-radius:8px;">
                <input type="text" name="fp_title[]" value="<?php echo esc_attr($plan['title'] ?? ''); ?>" placeholder="Plan Name" style="width:100%;margin-bottom:8px;">
                <input type="number" name="fp_size[]" value="<?php echo esc_attr($plan['size'] ?? ''); ?>" placeholder="Size (sqft)" style="width:100%;margin-bottom:8px;">
                <input type="text" name="fp_description[]" value="<?php echo esc_attr($plan['description'] ?? ''); ?>" placeholder="Description" style="width:100%;">
                <button type="button" class="button-link-delete" onclick="$(this).parent().remove()">Remove</button>
            </div>
            <?php endforeach; endif; ?>
        </div>
        <button type="button" class="button" onclick="addFloorPlan()">Add Floor Plan</button>
    </div>
    <input type="hidden" id="tls_floor_plans_json" name="tls_floor_plans_json" value="<?php echo esc_attr($floor_plans); ?>">
    <script>
    function addFloorPlan() {
        var html = '<div class="tls-floor-plan-item" style="background:#f8f9fa;padding:12px;margin-bottom:10px;border-radius:8px;">';
        html += '<input type="text" name="fp_title[]" placeholder="Plan Name" style="width:100%;margin-bottom:8px;">';
        html += '<input type="number" name="fp_size[]" placeholder="Size (sqft)" style="width:100%;margin-bottom:8px;">';
        html += '<input type="text" name="fp_description[]" placeholder="Description" style="width:100%;">';
        html += '<button type="button" class="button-link-delete" onclick="$(this).parent().remove()">Remove</button>';
        html += '</div>';
        jQuery('#tlsFloorPlansContainer').append(html);
    }
    jQuery(document).ready(function() {
        jQuery('form').on('submit', function() {
            var plans = [];
            jQuery('.tls-floor-plan-item').each(function() {
                var title = jQuery(this).find('input[name="fp_title[]"]').val();
                var size = jQuery(this).find('input[name="fp_size[]"]').val();
                var desc = jQuery(this).find('input[name="fp_description[]"]').val();
                if (title) plans.push({title: title, size: size, description: desc});
            });
            jQuery('#tls_floor_plans_json').val(JSON.stringify(plans));
        });
    });
    </script>
    <?php
}

add_action('save_post_tanah', function($post_id) {
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
    if (isset($_POST['tls_floor_plans_json'])) {
        update_post_meta($post_id, '_tanah_floor_plans', sanitize_text_field($_POST['tls_floor_plans_json']));
    }
});

// ============================================
// DOCUMENT MANAGEMENT METABOX
// ============================================
add_action('add_meta_boxes', function() {
    add_meta_box('tls_tanah_documents', 'Dokumen Tanah', 'tls_render_tanah_documents', 'tanah', 'normal', 'high');
});

function tls_render_tanah_documents($post) {
    wp_nonce_field('tls_tanah_documents_nonce', 'tls_tanah_documents_nonce');

    $geran_available = get_post_meta($post->ID, '_tanah_doc_geran_available', true);
    $geran_file = get_post_meta($post->ID, '_tanah_doc_geran_file', true);

    $pelan_available = get_post_meta($post->ID, '_tanah_doc_pelan_available', true);
    $pelan_file = get_post_meta($post->ID, '_tanah_doc_pelan_file', true);

    $search_available = get_post_meta($post->ID, '_tanah_doc_search_available', true);
    $search_file = get_post_meta($post->ID, '_tanah_doc_search_file', true);

    // Get expiry dates
    $geran_expiry = get_post_meta($post->ID, '_tanah_doc_geran_expiry', true);
    $pelan_expiry = get_post_meta($post->ID, '_tanah_doc_pelan_expiry', true);
    $search_expiry = get_post_meta($post->ID, '_tanah_doc_search_expiry', true);
    ?>
    <style>
        .tls-doc-table { width: 100%; border-collapse: collapse; }
        .tls-doc-table th { background: #f8fafc; padding: 12px; text-align: left; border-bottom: 2px solid #e2e8f0; }
        .tls-doc-table td { padding: 12px; border-bottom: 1px solid #e2e8f0; }
        .tls-doc-toggle { width: 50px; height: 26px; background: #cbd5e1; border-radius: 13px; position: relative; cursor: pointer; transition: background 0.3s; }
        .tls-doc-toggle input { display: none; }
        .tls-doc-toggle span { width: 22px; height: 22px; background: white; border-radius: 50%; position: absolute; top: 2px; left: 2px; transition: left 0.3s; }
        .tls-doc-toggle input:checked + .tls-doc-toggle { background: #28a745; }
        .tls-doc-toggle input:checked + .tls-doc-toggle span { left: 26px; }
        .tls-doc-upload-btn { background: #1a1a2e; color: white; padding: 8px 16px; border-radius: 6px; border: none; cursor: pointer; font-size: 13px; }
        .tls-doc-upload-btn:hover { background: #28a745; }
        .tls-doc-file-name { color: #28a745; font-weight: 600; margin-left: 10px; }
        .tls-doc-remove { color: #dc2626; cursor: pointer; margin-left: 10px; text-decoration: underline; }
        .tls-doc-expiry { margin-top: 8px; }
        .tls-doc-expiry input { padding: 4px 8px; border: 1px solid #e2e8f0; border-radius: 4px; }
        .tls-expiry-warning { color: #dc2626; font-weight: 600; font-size: 12px; }
        .tls-expiry-ok { color: #28a745; font-weight: 600; font-size: 12px; }
    </style>

    <table class="tls-doc-table">
        <thead>
            <tr>
                <th>Document Type</th>
                <th>Available</th>
                <th>Upload PDF & Expiry Date</th>
            </tr>
        </thead>
        <tbody>
            <!-- GERAN -->
            <tr>
                <td><strong><i class="material-icons">description</i> Geran (Title Deed)</strong></td>
                <td>
                    <label class="tls-doc-toggle">
                        <input type="checkbox" name="tanah_doc_geran_available" value="1" <?php checked($geran_available, '1'); ?>>
                        <div class="tls-doc-toggle">
                            <span></span>
                        </div>
                    </label>
                </td>
                <td>
                    <input type="hidden" name="tanah_doc_geran_file" id="geran_file_url" value="<?php echo esc_attr($geran_file); ?>">
                    <button type="button" class="tls-doc-upload-btn" onclick="openMediaUploader('geran')">Upload PDF</button>
                    <?php if ($geran_file): ?>
                        <span class="tls-doc-file-name" id="geran_file_name"><?php echo basename($geran_file); ?></span>
                        <span class="tls-doc-remove" onclick="removeDocument('geran')">Remove</span>
                    <?php else: ?>
                        <span class="tls-doc-file-name" id="geran_file_name"></span>
                    <?php endif; ?>
                    <div class="tls-doc-expiry">
                        <label style="font-size: 12px; display: block; margin-bottom: 4px;">Expiry Date:</label>
                        <input type="date" name="tanah_doc_geran_expiry" value="<?php echo esc_attr($geran_expiry); ?>">
                        <?php
                        if ($geran_expiry) {
                            $expiry_date = strtotime($geran_expiry);
                            $today = strtotime(date('Y-m-d'));
                            $days_until_expiry = ($expiry_date - $today) / (60 * 60 * 24);

                            if ($days_until_expiry < 0) {
                                echo '<span class="tls-expiry-warning"> <span class="dashicons dashicons-warning"></span>Expired ' . abs(floor($days_until_expiry)) . ' days ago!</span>';
                            } elseif ($days_until_expiry <= 30) {
                                echo '<span class="tls-expiry-warning"> <span class="dashicons dashicons-warning"></span>Expires in ' . floor($days_until_expiry) . ' days</span>';
                            } else {
                                echo '<span class="tls-expiry-ok"> <i class="material-icons">check</i> Valid for ' . floor($days_until_expiry) . ' days</span>';
                            }
                        }
                        ?>
                    </div>
                </td>
            </tr>

            <!-- PELAN TAPAK -->
            <tr>
                <td><strong><i class="material-icons">map</i> Pelan Tapak (Site Plan)</strong></td>
                <td>
                    <label class="tls-doc-toggle">
                        <input type="checkbox" name="tanah_doc_pelan_available" value="1" <?php checked($pelan_available, '1'); ?>>
                        <div class="tls-doc-toggle">
                            <span></span>
                        </div>
                    </label>
                </td>
                <td>
                    <input type="hidden" name="tanah_doc_pelan_file" id="pelan_file_url" value="<?php echo esc_attr($pelan_file); ?>">
                    <button type="button" class="tls-doc-upload-btn" onclick="openMediaUploader('pelan')">Upload PDF</button>
                    <?php if ($pelan_file): ?>
                        <span class="tls-doc-file-name" id="pelan_file_name"><?php echo basename($pelan_file); ?></span>
                        <span class="tls-doc-remove" onclick="removeDocument('pelan')">Remove</span>
                    <?php else: ?>
                        <span class="tls-doc-file-name" id="pelan_file_name"></span>
                    <?php endif; ?>
                    <div class="tls-doc-expiry">
                        <label style="font-size: 12px; display: block; margin-bottom: 4px;">Expiry Date:</label>
                        <input type="date" name="tanah_doc_pelan_expiry" value="<?php echo esc_attr($pelan_expiry); ?>">
                        <?php
                        if ($pelan_expiry) {
                            $expiry_date = strtotime($pelan_expiry);
                            $today = strtotime(date('Y-m-d'));
                            $days_until_expiry = ($expiry_date - $today) / (60 * 60 * 24);

                            if ($days_until_expiry < 0) {
                                echo '<span class="tls-expiry-warning"> <span class="dashicons dashicons-warning"></span>Expired ' . abs(floor($days_until_expiry)) . ' days ago!</span>';
                            } elseif ($days_until_expiry <= 30) {
                                echo '<span class="tls-expiry-warning"> <span class="dashicons dashicons-warning"></span>Expires in ' . floor($days_until_expiry) . ' days</span>';
                            } else {
                                echo '<span class="tls-expiry-ok"> <i class="material-icons">check</i> Valid for ' . floor($days_until_expiry) . ' days</span>';
                            }
                        }
                        ?>
                    </div>
                </td>
            </tr>

            <!-- OFFICIAL SEARCH -->
            <tr>
                <td><strong><span class="dashicons dashicons-search"></span> Official Search</strong></td>
                <td>
                    <label class="tls-doc-toggle">
                        <input type="checkbox" name="tanah_doc_search_available" value="1" <?php checked($search_available, '1'); ?>>
                        <div class="tls-doc-toggle">
                            <span></span>
                        </div>
                    </label>
                </td>
                <td>
                    <input type="hidden" name="tanah_doc_search_file" id="search_file_url" value="<?php echo esc_attr($search_file); ?>">
                    <button type="button" class="tls-doc-upload-btn" onclick="openMediaUploader('search')">Upload PDF</button>
                    <?php if ($search_file): ?>
                        <span class="tls-doc-file-name" id="search_file_name"><?php echo basename($search_file); ?></span>
                        <span class="tls-doc-remove" onclick="removeDocument('search')">Remove</span>
                    <?php else: ?>
                        <span class="tls-doc-file-name" id="search_file_name"></span>
                    <?php endif; ?>
                    <div class="tls-doc-expiry">
                        <label style="font-size: 12px; display: block; margin-bottom: 4px;">Expiry Date:</label>
                        <input type="date" name="tanah_doc_search_expiry" value="<?php echo esc_attr($search_expiry); ?>">
                        <?php
                        if ($search_expiry) {
                            $expiry_date = strtotime($search_expiry);
                            $today = strtotime(date('Y-m-d'));
                            $days_until_expiry = ($expiry_date - $today) / (60 * 60 * 24);

                            if ($days_until_expiry < 0) {
                                echo '<span class="tls-expiry-warning"> <span class="dashicons dashicons-warning"></span>Expired ' . abs(floor($days_until_expiry)) . ' days ago!</span>';
                            } elseif ($days_until_expiry <= 30) {
                                echo '<span class="tls-expiry-warning"> <span class="dashicons dashicons-warning"></span>Expires in ' . floor($days_until_expiry) . ' days</span>';
                            } else {
                                echo '<span class="tls-expiry-ok"> <i class="material-icons">check</i> Valid for ' . floor($days_until_expiry) . ' days</span>';
                            }
                        }
                        ?>
                    </div>
                </td>
            </tr>
        </tbody>
    </table>

    <p style="margin-top: 16px; color: #666; font-size: 13px;">
        <strong>Note:</strong> Toggle "Available" to show the document on the frontend. Set expiry dates to track document validity. You'll receive warnings when documents are about to expire.
    </p>

    <script>
    function openMediaUploader(docType) {
        var mediaUploader = wp.media({
            title: 'Upload Document',
            button: { text: 'Select PDF' },
            library: { type: 'application/pdf' },
            multiple: false
        });

        mediaUploader.on('select', function() {
            var attachment = mediaUploader.state().get('selection').first().toJSON();
            document.getElementById(docType + '_file_url').value = attachment.url;
            document.getElementById(docType + '_file_name').textContent = attachment.filename;
            document.getElementById(docType + '_file_name').style.display = 'inline';
        });

        mediaUploader.open();
    }

    function removeDocument(docType) {
        if (confirm('Remove this document?')) {
            document.getElementById(docType + '_file_url').value = '';
            document.getElementById(docType + '_file_name').textContent = '';
            document.getElementById(docType + '_file_name').style.display = 'none';
        }
    }
    </script>
    <?php
}

add_action('save_post_tanah', function($post_id) {
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
    if (!isset($_POST['tls_tanah_documents_nonce']) || !wp_verify_nonce($_POST['tls_tanah_documents_nonce'], 'tls_tanah_documents_nonce')) return;

    // Save Geran
    update_post_meta($post_id, '_tanah_doc_geran_available', isset($_POST['tanah_doc_geran_available']) ? '1' : '0');
    if (isset($_POST['tanah_doc_geran_file'])) {
        update_post_meta($post_id, '_tanah_doc_geran_file', esc_url_raw($_POST['tanah_doc_geran_file']));
    }
    if (isset($_POST['tanah_doc_geran_expiry'])) {
        update_post_meta($post_id, '_tanah_doc_geran_expiry', sanitize_text_field($_POST['tanah_doc_geran_expiry']));
    }

    // Save Pelan Tapak
    update_post_meta($post_id, '_tanah_doc_pelan_available', isset($_POST['tanah_doc_pelan_available']) ? '1' : '0');
    if (isset($_POST['tanah_doc_pelan_file'])) {
        update_post_meta($post_id, '_tanah_doc_pelan_file', esc_url_raw($_POST['tanah_doc_pelan_file']));
    }
    if (isset($_POST['tanah_doc_pelan_expiry'])) {
        update_post_meta($post_id, '_tanah_doc_pelan_expiry', sanitize_text_field($_POST['tanah_doc_pelan_expiry']));
    }

    // Save Official Search
    update_post_meta($post_id, '_tanah_doc_search_available', isset($_POST['tanah_doc_search_available']) ? '1' : '0');
    if (isset($_POST['tanah_doc_search_file'])) {
        update_post_meta($post_id, '_tanah_doc_search_file', esc_url_raw($_POST['tanah_doc_search_file']));
    }
    if (isset($_POST['tanah_doc_search_expiry'])) {
        update_post_meta($post_id, '_tanah_doc_search_expiry', sanitize_text_field($_POST['tanah_doc_search_expiry']));
    }
});

// ============================================
// ADVANCED SEARCH SHORTCODE
// ============================================
add_shortcode('tls_search', 'tls_advanced_search');

function tls_advanced_search() {
    $daerahs = get_terms(['taxonomy' => 'daerah', 'hide_empty' => false]);
    ob_start();
    ?>
    <div class="tls-advanced-search">
        <form method="get" action="<?php echo esc_url(home_url('/')); ?>">
            <div class="tls-search-grid">
                <div class="tls-search-field">
                    <input type="text" name="s" placeholder="Search location...">
                </div>
                <div class="tls-search-field">
                    <select name="daerah">
                        <option value="">All Districts</option>
                        <?php foreach ($daerahs as $d): ?>
                        <option value="<?php echo $d->slug; ?>"><?php echo $d->name; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="tls-search-field">
                    <select name="geran">
                        <option value="">All Grant Types</option>
                        <option value="CL">CL (Country Lease)</option>
                        <option value="NT">NT (Native Title)</option>
                        <option value="P">Pajakan</option>
                        <option value="Hakmilik">Freehold</option>
                    </select>
                </div>
                <div class="tls-search-field">
                    <select name="min_price">
                        <option value="">Min Price</option>
                        <option value="50000">RM 50,000</option>
                        <option value="100000">RM 100,000</option>
                        <option value="200000">RM 200,000</option>
                        <option value="300000">RM 300,000</option>
                        <option value="500000">RM 500,000</option>
                        <option value="1000000">RM 1,000,000</option>
                        <option value="2000000">RM 2,000,000</option>
                    </select>
                </div>
                <div class="tls-search-field">
                    <select name="max_price">
                        <option value="">Max Price</option>
                        <option value="100000">RM 100,000</option>
                        <option value="200000">RM 200,000</option>
                        <option value="300000">RM 300,000</option>
                        <option value="500000">RM 500,000</option>
                        <option value="1000000">RM 1,000,000</option>
                        <option value="2000000">RM 2,000,000</option>
                        <option value="5000000">RM 5,000,000</option>
                    </select>
                </div>
                <div class="tls-search-field">
                    <button type="submit" class="tls-search-btn">Search</button>
                </div>
            </div>
        </form>
    </div>
    <style>
    .tls-advanced-search { background: #fff; padding: 20px; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,.08); margin-bottom: 30px; }
    .tls-search-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 12px; }
    .tls-search-field input, .tls-search-field select { width: 100%; padding: 10px 12px; border: 1px solid #e2e8f0; border-radius: 8px; font-size: 14px; }
    .tls-search-btn { background: #16a34a; color: #fff; border: none; padding: 10px 20px; border-radius: 8px; font-weight: 600; cursor: pointer; }
    .tls-search-btn:hover { background: #15803d; }
    </style>
    <?php
    return ob_get_clean();
}

// ============================================
// AGENT SYSTEM (REALPRESS STYLE)
// ============================================
add_action('init', function() {
    register_post_type('tls_agent', [
        'labels' => [
            'name' => 'Agents',
            'singular_name' => 'Agent',
            'add_new_item' => 'Add New Agent',
            'edit_item' => 'Edit Agent',
        ],
        'public' => false,
        'show_ui' => true,
        'show_in_menu' => false, // Managed via TLS System dashboard
        'supports' => ['title', 'thumbnail', 'editor'],
    ]);
}, 0);

add_action('add_meta_boxes', function() {
    add_meta_box('tls_agent_profile', 'Agent Profile', 'tls_render_agent_profile', 'tls_agent', 'normal', 'high');
});

function tls_render_agent_profile($post) {
    $phone = get_post_meta($post->ID, '_tls_agent_phone', true);
    $email = get_post_meta($post->ID, '_tls_agent_email', true);
    $company = get_post_meta($post->ID, '_tls_agent_company', true);
    $license = get_post_meta($post->ID, '_tls_agent_license', true);
    $whatsapp = get_post_meta($post->ID, '_tls_agent_whatsapp', true);
    ?>
    <table class="form-table">
        <tr>
            <th><label>Phone</label></th>
            <td><input type="text" name="tls_agent_phone" value="<?php echo esc_attr($phone); ?>" class="regular-text"></td>
        </tr>
        <tr>
            <th><label>Email</label></th>
            <td><input type="email" name="tls_agent_email" value="<?php echo esc_attr($email); ?>" class="regular-text"></td>
        </tr>
        <tr>
            <th><label>Company</label></th>
            <td><input type="text" name="tls_agent_company" value="<?php echo esc_attr($company); ?>" class="regular-text"></td>
        </tr>
        <tr>
            <th><label>License Number</label></th>
            <td><input type="text" name="tls_agent_license" value="<?php echo esc_attr($license); ?>" class="regular-text"></td>
        </tr>
        <tr>
            <th><label>WhatsApp</label></th>
            <td><input type="text" name="tls_agent_whatsapp" value="<?php echo esc_attr($whatsapp); ?>" class="regular-text"></td>
        </tr>
    </table>
    <?php
}

add_action('save_post_tls_agent', function($post_id) {
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
    if (isset($_POST['tls_agent_phone'])) update_post_meta($post_id, '_tls_agent_phone', sanitize_text_field($_POST['tls_agent_phone']));
    if (isset($_POST['tls_agent_email'])) update_post_meta($post_id, '_tls_agent_email', sanitize_email($_POST['tls_agent_email']));
    if (isset($_POST['tls_agent_company'])) update_post_meta($post_id, '_tls_agent_company', sanitize_text_field($_POST['tls_agent_company']));
    if (isset($_POST['tls_agent_license'])) update_post_meta($post_id, '_tls_agent_license', sanitize_text_field($_POST['tls_agent_license']));
    if (isset($_POST['tls_agent_whatsapp'])) update_post_meta($post_id, '_tls_agent_whatsapp', sanitize_text_field($_POST['tls_agent_whatsapp']));
});

// Agent List Shortcode
add_shortcode('tls_agents', 'tls_agent_list');

function tls_agent_list($atts) {
    $atts = shortcode_atts(['limit' => 10], $atts);
    $agents = new WP_Query([
        'post_type' => 'tls_agent',
        'posts_per_page' => intval($atts['limit']),
        'post_status' => 'publish'
    ]);
    
    ob_start();
    ?>
    <div class="tls-agent-grid" style="display:grid;grid-template-columns:repeat(auto-fill,minmax(250px,1fr));gap:20px;">
        <?php while ($agents->have_posts()): $agents->the_post(); 
            $phone = get_post_meta(get_the_ID(), '_tls_agent_phone', true);
            $email = get_post_meta(get_the_ID(), '_tls_agent_email', true);
            $company = get_post_meta(get_the_ID(), '_tls_agent_company', true);
            $whatsapp = get_post_meta(get_the_ID(), '_tls_agent_whatsapp', true);
        ?>
        <div class="tls-agent-card" style="background:#fff;border-radius:12px;padding:20px;box-shadow:0 2px 8px rgba(0,0,0,.08);text-align:center;">
            <?php if (has_post_thumbnail()): ?>
            <img src="<?php the_post_thumbnail_url('thumbnail'); ?>" alt="<?php the_title(); ?>" style="width:80px;height:80px;border-radius:50%;object-fit:cover;margin-bottom:12px;">
            <?php endif; ?>
            <h3 style="margin:0 0 4px;font-size:18px;"><?php the_title(); ?></h3>
            <?php if ($company): ?>
            <p style="margin:0 0 8px;color:#64748b;font-size:14px;"><?php echo esc_html($company); ?></p>
            <?php endif; ?>
            <div style="margin-top:12px;">
                <?php if ($whatsapp): ?>
                <a href="https://wa.me/<?php echo esc_attr($whatsapp); ?>" class="button" target="_blank" style="background:#25d366;color:#fff;padding:8px 16px;border-radius:8px;text-decoration:none;">WhatsApp</a>
                <?php endif; ?>
                <?php if ($phone): ?>
                <a href="tel:<?php echo esc_attr($phone); ?>" class="button" style="background:#f1f5f9;color:#333;padding:8px 16px;border-radius:8px;text-decoration:none;margin-left:8px;">Call</a>
                <?php endif; ?>
            </div>
        </div>
        <?php endwhile; wp_reset_postdata(); ?>
    </div>
    <?php
    return ob_get_clean();
}

// ============================================
// HTML REPORT GENERATOR
// ============================================

function tls_ldc_generate_html_report($data, $estimate_id) {
    $html = '<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Land Development Cost Estimate - ' . $estimate_id . '</title>
    <style>
        @page { size: A4; margin: 20mm; }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; font-size: 11pt; line-height: 1.6; color: #333; background: white; max-width: 210mm; margin: 0 auto; padding: 20px; }
        .header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 30px; padding-bottom: 20px; border-bottom: 3px solid #2c5f2d; }
        .header-left h1 { color: #2c5f2d; font-size: 24pt; margin-bottom: 5px; }
        .header-left p { color: #666; font-size: 10pt; }
        .header-right { text-align: right; }
        .header-right .estimate-id { font-size: 18pt; font-weight: bold; color: #2c5f2d; }
        .header-right .date { color: #666; font-size: 10pt; }
        .badge { background: #fff9e6; border-left: 4px solid #f5a623; padding: 10px 15px; margin: 20px 0; font-size: 10pt; }
        h2 { color: #2c5f2d; margin: 30px 0 15px; font-size: 14pt; border-bottom: 1px solid #ddd; padding-bottom: 5px; }
        .info-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin: 15px 0; }
        .info-box { background: #f9f9f9; padding: 15px; border-radius: 4px; }
        .info-box strong { color: #2c5f2d; display: block; margin-bottom: 5px; font-size: 10pt; }
        .category-title { font-size: 12pt; color: #666; margin: 25px 0 10px; font-weight: bold; }
        table { width: 100%; border-collapse: collapse; margin: 10px 0; }
        th { background: none; color: #999; font-weight: 600; padding: 10px 5px; text-align: left; border-bottom: 1px solid #ddd; font-size: 10pt; }
        th:nth-child(2), th:nth-child(3), th:nth-child(4) { text-align: right; }
        td { padding: 12px 5px; border-bottom: 1px solid #f0f0f0; }
        td:nth-child(2), td:nth-child(3), td:nth-child(4) { text-align: right; }
        td:nth-child(4) { font-weight: bold; }
        .total-section { text-align: right; margin: 40px 0; }
        .total-section .label { font-size: 12pt; color: #666; margin-bottom: 10px; }
        .total-section .amount { font-size: 24pt; color: #2c5f2d; font-weight: bold; }
        .notes { background: #f9f9f9; padding: 20px; margin: 30px 0; border-radius: 4px; }
        .notes h3 { margin-bottom: 10px; color: #2c5f2d; }
        .notes ul { margin-left: 20px; }
        .notes li { margin-bottom: 5px; font-size: 10pt; }
        .footer { text-align: center; margin-top: 50px; padding-top: 20px; border-top: 1px solid #ddd; font-size: 10pt; color: #666; }
        @media print { body { padding: 0; } }
    </style>
</head>
<body>
    <div class="header">
        <div class="header-left">
            <h1>TANAH LOT SABAH</h1>
            <p>Platform Jual Beli Tanah di Sabah</p>
        </div>
        <div class="header-right">
            <div class="estimate-id">Estimate #' . $estimate_id . '</div>
            <div class="date">Tarikh: ' . date('d/m/Y') . '</div>
        </div>
    </div>
    
    <div class="badge">
        <strong>Nota:</strong> Estimate ini sah for 7 hari. Harga sebenar mungkin berbeza bergantung pada keadaan tapak.
    </div>
    
    <h2>Maklumat Klien</h2>
    <div class="info-grid">
        <div class="info-box"><strong>Nama</strong>' . esc_html($data['client_name']) . '</div>
        <div class="info-box"><strong>Email</strong><a href="mailto:' . esc_attr($data['client_email']) . '">' . esc_html($data['client_email']) . '</a></div>
        <div class="info-box"><strong>Telefon</strong>+' . esc_html($data['client_phone']) . '</div>
        <div class="info-box"><strong>Lokasi Tanah</strong>' . esc_html($data['location']) . '</div>
    </div>
    
    <h2>Maklumat Tanah</h2>
    <div class="info-grid">
        <div class="info-box"><strong>Saiz Tanah</strong>' . number_format($data['land_size'], 2) . ' ' . esc_html($data['land_unit']) . '</div>
    </div>
    
    <h2>Kos Pembangunan</h2>';
    
    // Group items by category
    $items_by_cat = [];
    foreach ($data['items'] as $item) {
        $cat = $item['category'];
        if (!isset($items_by_cat[$cat])) $items_by_cat[$cat] = [];
        $items_by_cat[$cat][] = $item;
    }
    
    foreach ($items_by_cat as $category => $items) {
        $html .= '<div class="category-title">' . esc_html($category) . '</div>';
        $html .= '<table><thead><tr><th>Item</th><th>Kuantiti</th><th>Harga Unit</th><th>Jumlah</th></tr></thead><tbody>';
        
        foreach ($items as $item) {
            $amount = floatval($item['quantity']) * floatval($item['unit_price']);
            $html .= '<tr>
                <td>' . esc_html($item['name']) . '</td>
                <td>' . number_format($item['quantity'], 2) . ' ' . esc_html($item['unit']) . '</td>
                <td>RM ' . number_format($item['unit_price'], 2) . '</td>
                <td>RM ' . number_format($amount, 2) . '</td>
            </tr>';
        }
        
        $html .= '</tbody></table>';
    }
    
    $total = 0;
    foreach ($data['items'] as $item) {
        $total += floatval($item['quantity']) * floatval($item['unit_price']);
    }
    
    $html .= '
    <div class="total-section">
        <div class="label">JUMLAH ANGGARAN KOS PEMBANGUNAN</div>
        <div class="amount">RM ' . number_format($total, 2) . '</div>
    </div>
    
    <div class="notes">
        <h3>Nota Penting</h3>
        <ul>
            <li>Harga adalah anggaran sahaja dan tertakluk kepada perubahan.</li>
            <li>Kos sebenar bergantung pada keadaan tapak dan spesifikasi projek.</li>
            <li>Harga tidak termasuk cukai dan duti setem.</li>
            <li>Sila hubungi kami untuk konsultasi percuma.</li>
        </ul>
    </div>
    
    <div class="footer">
        <p><strong>Tanah Lot Sabah</strong> - Partner Tanah Anda di Sabah</p>
        <p>Website: tanahlotsabah.com | WhatsApp: +60123456789</p>
    </div>
</body>
</html>';
    
    return $html;
}

// ============================================
// SHORTCODES
// ============================================
add_shortcode('land_dev_calculator', function() {
    ob_start();
    tls_ldc_render_calculator();
    return ob_get_clean();
});

// ============================================
// CALCULATOR RENDERER
// ============================================

function tls_ldc_render_calculator() {
    global $wpdb;
    $pricing = TLS_LDC_Database::get_pricing_by_category();
    $locations = TLS_LDC_Database::get_all_locations();

    // Get active construction templates
    $templates_table = $wpdb->prefix . TLS_LDC_PREFIX . 'templates';
    $templates = [];

    // Check if table exists before querying
    $table_exists = $wpdb->get_var("SHOW TABLES LIKE '$templates_table'");
    if ($table_exists) {
        $templates = $wpdb->get_results("SELECT * FROM $templates_table WHERE is_active = 1 ORDER BY name");
    }
    ?>
    <div class="ldc-calculator-wrapper" id="ldcCalculator">
        
        <!-- LAND INFO SECTION -->
        <div class="ldc-section">
            <div class="ldc-section-header">
                <h2>Maklumat Tanah</h2>
            </div>
            <div class="ldc-section-body">
                <div class="ldc-row">
                    <div class="ldc-col-6">
                        <label>Saiz Tanah *</label>
                        <input type="number" id="land_size" class="ldc-input" step="0.01" min="0" placeholder="cth: 5000" required>
                    </div>
                    <div class="ldc-col-6">
                        <label>Unit</label>
                        <select id="land_unit" class="ldc-input">
                            <option value="sq ft">Kaki Persegi (sq ft)</option>
                            <option value="acre">Ekar</option>
                        </select>
                    </div>
                </div>
                <div class="ldc-row">
                    <div class="ldc-col-12">
                        <label>Lokasi *</label>
                        <input type="text" id="land_location" class="ldc-input" placeholder="cth: Kota Kinabalu, Penampang" required list="location_list">
                        <datalist id="location_list">
                            <?php foreach ($locations as $loc): ?>
                            <option value="<?php echo esc_attr($loc['name']); ?>">
                            <?php endforeach; ?>
                        </datalist>
                    </div>
                </div>
            </div>
        </div>

        <?php if (!empty($templates)): ?>
        <!-- CONSTRUCTION TEMPLATES SECTION -->
        <div class="ldc-section" style="background: linear-gradient(135deg, #f0f8f0 0%, #e8f5e9 100%); border: 2px solid #2c5f2d;">
            <div class="ldc-section-header" style="background: #2c5f2d; color: white;">
                <h2><i class="material-icons">construction</i> Pakej Pembangunan (Quick Start)</h2>
            </div>
            <div class="ldc-section-body">
                <p style="margin-bottom: 15px; color: #666; font-size: 14px;">
                    Pilih pakej untuk mengisi kalkulator secara automatik dengan anggaran kos standard.
                </p>
                <div class="ldc-row">
                    <div class="ldc-col-12">
                        <label style="font-weight: 600; margin-bottom: 8px; display: block;">Pilih Pakej:</label>
                        <select id="construction_template" class="ldc-input" style="font-size: 15px; padding: 12px; border: 2px solid #2c5f2d;">
                            <option value="">-- Pilih pakej atau isi manual --</option>
                            <?php foreach ($templates as $template): ?>
                                <option value="<?php echo $template->id; ?>" data-template-id="<?php echo $template->id; ?>">
                                    <?php echo esc_html($template->icon . ' ' . $template->name); ?>
                                    <?php if ($template->description): ?>
                                        - <?php echo esc_html($template->description); ?>
                                    <?php endif; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div id="template_info" style="display: none; margin-top: 15px; padding: 15px; background: white; border-radius: 8px; border-left: 4px solid #2c5f2d;">
                    <p style="margin: 0; color: #2c5f2d; font-weight: 600;">
                        <i class="material-icons">check</i> Pakej dipilih! Item akan diisi ke dalam kalkulator di bawah.
                    </p>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- PRICING CATEGORIES -->
        <?php foreach ($pricing as $category => $data): ?>
        <div class="ldc-section">
            <div class="ldc-section-header">
                <h2><?php echo esc_html($category); ?></h2>
                <label class="ldc-toggle-switch">
                    <input type="checkbox" class="ldc-category-toggle" data-category="<?php echo esc_attr($category); ?>">
                    <span class="ldc-toggle-slider"></span>
                    <span class="ldc-toggle-label">Aktifkan</span>
                </label>
            </div>
            <div class="ldc-section-body ldc-category-body" style="display:none;">
                <?php if (!empty($data['description'])): ?>
                <p class="ldc-category-desc"><?php echo esc_html($data['description']); ?></p>
                <?php endif; ?>
                
                <?php foreach ($data['items'] as $item): ?>
                <div class="ldc-item-row">
                    <div class="ldc-item-select">
                        <label>
                            <input type="checkbox" class="ldc-item-checkbox" 
                                data-item-id="<?php echo $item['id']; ?>"
                                data-category="<?php echo esc_attr($category); ?>"
                                data-item-name="<?php echo esc_attr($item['name']); ?>"
                                data-unit="<?php echo esc_attr($item['unit']); ?>"
                                data-price="<?php echo esc_attr($item['unit_price']); ?>">
                            <span><?php echo esc_html($item['name']); ?></span>
                        </label>
                    </div>
                    <div class="ldc-item-details" style="display:none;">
                        <div class="ldc-item-quantity">
                            <label>Kuantiti</label>
                            <input type="number" class="ldc-quantity-input" step="0.01" min="0" value="1">
                            <span class="ldc-unit"><?php echo esc_html($item['unit']); ?></span>
                        </div>
                        <div class="ldc-item-price">
                            <label>Harga Unit</label>
                            <div>RM <?php echo number_format($item['unit_price'], 2); ?></div>
                        </div>
                        <div class="ldc-item-total">
                            <label>Subtotal</label>
                            <div class="ldc-subtotal-display">RM 0.00</div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endforeach; ?>
        
        <!-- CLIENT INFO SECTION -->
        <div class="ldc-section">
            <div class="ldc-section-header">
                <h2>Maklumat Klien</h2>
            </div>
            <div class="ldc-section-body">
                <div class="ldc-row">
                    <div class="ldc-col-6">
                        <label>Nama Penuh *</label>
                        <input type="text" id="client_name" class="ldc-input" required>
                    </div>
                    <div class="ldc-col-6">
                        <label>Email *</label>
                        <input type="email" id="client_email" class="ldc-input" required>
                    </div>
                </div>
                <div class="ldc-row">
                    <div class="ldc-col-12">
                        <label>No. WhatsApp/Telefon *</label>
                        <input type="tel" id="client_phone" class="ldc-input" placeholder="60123456789" required>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- TOTAL SECTION -->
        <div class="ldc-section">
            <div class="ldc-section-header">
                <h2>Jumlah Kos Anggaran</h2>
            </div>
            <div class="ldc-section-body">
                <div class="ldc-grand-total">
                    <span>JUMLAH:</span>
                    <span class="ldc-grand-total-amount">RM 0.00</span>
                </div>
            </div>
        </div>
        
        <div style="text-align:center; margin: 30px 0;">
            <button type="button" id="ldc-review-btn" class="ldc-btn ldc-btn-primary ldc-btn-large">
                Jana PDF Estimate
            </button>
        </div>
    </div>
    
    <!-- CONFIRMATION MODAL -->
    <div class="ldc-modal" id="ldcConfirmationModal" style="display:none;">
        <div class="ldc-modal-content">
            <div class="ldc-modal-header">
                <h2>Sahkan Estimate</h2>
                <span class="ldc-modal-close" onclick="document.getElementById('ldcConfirmationModal').style.display='none'">&times;</span>
            </div>
            <div class="ldc-modal-body">
                <div class="confirm-section">
                    <h4>Maklumat Klien</h4>
                    <p><strong>Nama:</strong> <span id="confirm_name"></span></p>
                    <p><strong>Email:</strong> <span id="confirm_email"></span></p>
                    <p><strong>Telefon:</strong> <span id="confirm_phone"></span></p>
                </div>
                <div class="confirm-section">
                    <h4>Maklumat Tanah</h4>
                    <p><strong>Saiz:</strong> <span id="confirm_land"></span></p>
                    <p><strong>Lokasi:</strong> <span id="confirm_location"></span></p>
                </div>
                <div class="confirm-total">
                    <strong>Jumlah: RM <span id="confirm_total"></span></strong>
                </div>
                <button id="ldc-confirm-generate-btn" class="ldc-btn ldc-btn-primary" style="width:100%; margin-top:20px;">
                    Jana PDF
                </button>
            </div>
        </div>
    </div>
    
    <!-- LOADING OVERLAY -->
    <div class="ldc-loading-overlay" style="display:none;">
        <div class="ldc-loading-spinner"></div>
        <p>Menjana PDF...</p>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        var templateSelect = document.getElementById('construction_template');
        var templateInfo = document.getElementById('template_info');

        if (templateSelect) {
            templateSelect.addEventListener('change', function() {
                var templateId = this.value;

                if (!templateId) {
                    if (templateInfo) templateInfo.style.display = 'none';
                    return;
                }

                // Show loading state
                if (templateInfo) {
                    templateInfo.style.display = 'block';
                    templateInfo.innerHTML = '<p style="margin: 0; color: #666;">⏳ Loading template items...</p>';
                }

                // Fetch template items via AJAX
                var formData = new FormData();
                formData.append('action', 'get_template_items');
                formData.append('template_id', templateId);

                fetch('<?php echo admin_url('admin-ajax.php'); ?>', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.data.length > 0) {
                        // Apply template items to calculator
                        applyTemplateItems(data.data);

                        // Update info box
                        if (templateInfo) {
                            templateInfo.innerHTML = '<p style="margin: 0; color: #2c5f2d; font-weight: 600;"><i class="material-icons">check</i> ' + data.data.length + ' item(s) loaded from template!</p>';
                        }
                    } else {
                        if (templateInfo) {
                            templateInfo.innerHTML = '<p style="margin: 0; color: #f59e0b;"><span style="font-weight: bold;">!</span> No items found in this template.</p>';
                        }
                    }
                })
                .catch(error => {
                    console.error('Error loading template:', error);
                    if (templateInfo) {
                        templateInfo.innerHTML = '<p style="margin: 0; color: #ef4444;"><span style="font-weight: bold;">✕</span> Error loading template. Please try again.</p>';
                    }
                });
            });
        }

        function applyTemplateItems(items) {
            // Reset all checkboxes first
            document.querySelectorAll('.ldc-item-checkbox').forEach(function(checkbox) {
                checkbox.checked = false;
                var detailsRow = checkbox.closest('.ldc-item-row').querySelector('.ldc-item-details');
                if (detailsRow) detailsRow.style.display = 'none';
            });

            // Apply each template item
            items.forEach(function(item) {
                // Find the checkbox for this pricing item
                var checkbox = document.querySelector('.ldc-item-checkbox[data-item-id="' + item.pricing_id + '"]');

                if (checkbox) {
                    // Check the checkbox
                    checkbox.checked = true;

                    // Show and enable the category if not already
                    var category = checkbox.getAttribute('data-category');
                    var categoryToggle = document.querySelector('.ldc-category-toggle[data-category="' + category + '"]');
                    if (categoryToggle && !categoryToggle.checked) {
                        categoryToggle.checked = true;
                        categoryToggle.dispatchEvent(new Event('change'));
                    }

                    // Show details row and set quantity
                    var itemRow = checkbox.closest('.ldc-item-row');
                    var detailsRow = itemRow.querySelector('.ldc-item-details');
                    if (detailsRow) {
                        detailsRow.style.display = 'flex';
                        var quantityInput = detailsRow.querySelector('.ldc-input-qty');
                        if (quantityInput) {
                            quantityInput.value = item.default_quantity;
                        }
                    }
                }
            });

            // Recalculate totals (if there's a calculate function)
            if (typeof window.ldcCalculateTotal === 'function') {
                window.ldcCalculateTotal();
            }
        }
    });
    </script>
    <?php
}

// ============================================
// ============================================
// ADMIN MENU - TLS System Dashboard
// ============================================
add_action('admin_menu', function() {
    // Main Menu
    add_menu_page(
        'TLS System',
        'TLS System',
        'manage_options',
        'tls-dashboard',
        'tls_dashboard_page',
        'dashicons-admin-site-alt3',
        30
    );

    // Dashboard Home
    add_submenu_page('tls-dashboard', 'Dashboard', 'Dashboard', 'manage_options', 'tls-dashboard', 'tls_dashboard_page');

    // Property Management - Redirect to WordPress post type pages
    add_submenu_page('tls-dashboard', 'All Properties', 'All Properties', 'manage_options', 'edit.php?post_type=tanah');
    add_submenu_page('tls-dashboard', 'Add New Property', 'Add New Property', 'manage_options', 'post-new.php?post_type=tanah');
    add_submenu_page('tls-dashboard', 'Nearby Places', 'Nearby Places', 'manage_options', 'tls-nearby-places', 'tls_nearby_places_page');

    // Leads & Inquiries
    add_submenu_page('tls-dashboard', 'Property Leads', 'Property Leads', 'manage_options', 'tls-leads', 'tls_leads_admin_page');
    add_submenu_page('tls-dashboard', 'Calculator Leads', 'Calculator Leads', 'manage_options', 'tls-calculator', 'tls_ldc_admin_page');

    // Agents
    add_submenu_page('tls-dashboard', 'Property Agents', 'Property Agents', 'manage_options', 'tls-agents', 'tls_agents_admin_page');
    add_submenu_page('tls-dashboard', 'Calculator Agents', 'Calculator Agents', 'manage_options', 'tls-calculator-agents', 'tls_ldc_agents_page');

    // Content
    // add_submenu_page('tls-dashboard', 'Hero Videos', 'Hero Videos', 'manage_options', 'tls-hero-videos', 'tls_hero_videos_page');

    // Settings
    add_submenu_page('tls-dashboard', 'Login Security', 'Login Security', 'manage_options', 'tls-login-security', 'tls_login_security_page');
    add_submenu_page('tls-dashboard', 'Contact Settings', 'Contact Settings', 'manage_options', 'tls-settings', 'tls_settings_page');
    add_submenu_page('tls-dashboard', 'FAB Menu', 'FAB Menu', 'manage_options', 'tls-fab-settings', 'tls_fab_settings_admin_page');
    add_submenu_page('tls-dashboard', 'Calculator Settings', 'Calculator Settings', 'manage_options', 'tls-calculator-settings', 'tls_ldc_settings_page');
    add_submenu_page('tls-dashboard', 'Pricing Management', 'Pricing Management', 'manage_options', 'tls-pricing-management', 'tls_pricing_management_page');
    add_submenu_page('tls-dashboard', 'Construction Templates', 'Construction Templates', 'manage_options', 'tls-construction-templates', 'tls_construction_templates_page');
    add_submenu_page('tls-dashboard', 'Purchase Calculator Settings', 'Purchase Calculator', 'manage_options', 'tls-purchase-calculator', 'tls_purchase_calculator_settings_page');
});

// ============================================
// LOGIN SECURITY SETTINGS PAGE
// ============================================
function tls_login_security_page() {
    // Save settings
    if (isset($_POST['tls_login_security_nonce']) && wp_verify_nonce($_POST['tls_login_security_nonce'], 'tls_login_security_save')) {
        $disable_wp_login = isset($_POST['disable_wp_login']) ? 1 : 0;
        update_option('tls_disable_wp_login', $disable_wp_login);
        echo '<div class="notice notice-success is-dismissible"><p>Login security settings saved!</p></div>';
    }

    $disable_wp_login = get_option('tls_disable_wp_login', 0);
    ?>
    <div class="wrap">
        <h1><span class="dashicons dashicons-lock"></span> Login Security Settings</h1>
        <p class="description">Control access to WordPress default login page (wp-login.php)</p>

        <form method="post" style="margin-top: 30px;">
            <?php wp_nonce_field('tls_login_security_save', 'tls_login_security_nonce'); ?>

            <table class="form-table">
                <tr>
                    <th scope="row">
                        <label for="disable_wp_login">Disable wp-login.php</label>
                    </th>
                    <td>
                        <label for="disable_wp_login" style="display: flex; align-items: center; gap: 10px;">
                            <input type="checkbox" name="disable_wp_login" id="disable_wp_login" value="1" <?php checked($disable_wp_login, 1); ?>>
                            <span>Block access to WordPress default login page</span>
                        </label>
                        <p class="description" style="margin-top: 10px;">
                            <strong>When enabled:</strong> wp-login.php will redirect to homepage<br>
                            <strong>Users must login via:</strong> <code><?php echo home_url('/login/'); ?></code><br>
                            <strong>When disabled:</strong> Both wp-login.php and custom login page work
                        </p>
                    </td>
                </tr>
            </table>

            <div style="margin-top: 20px; padding: 15px; background: #fff3cd; border-left: 4px solid #ffc107;">
                <h3 style="margin-top: 0; color: #856404;"><span class="dashicons dashicons-warning"></span> Important Notes</h3>
                <ul style="margin: 10px 0;">
                    <li><strong>Custom Login Page:</strong> Your theme has a custom login at <code>/login/</code></li>
                    <li><strong>Admin Access:</strong> You can always access admin if already logged in</li>
                    <li><strong>Emergency Access:</strong> If locked out, disable via database:<br>
                        <code>UPDATE wp_options SET option_value='0' WHERE option_name='tls_disable_wp_login';</code>
                    </li>
                </ul>
            </div>

            <?php submit_button('Save Security Settings'); ?>
        </form>

        <div style="margin-top: 30px; padding: 20px; background: #f0f6fc; border-left: 4px solid #0969da;">
            <h3 style="margin-top: 0;"><span class="dashicons dashicons-clipboard"></span> Current Status</h3>
            <table style="width: 100%; max-width: 600px;">
                <tr>
                    <td style="padding: 8px 0;"><strong>wp-login.php Access:</strong></td>
                    <td style="padding: 8px 0;">
                        <?php if ($disable_wp_login): ?>
                            <span style="color: #dc3545;"><span class="dashicons dashicons-no-alt"></span> Blocked</span>
                        <?php else: ?>
                            <span style="color: #28a745;"><span class="dashicons dashicons-yes-alt"></span> Allowed</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <tr>
                    <td style="padding: 8px 0;"><strong>Custom Login Page:</strong></td>
                    <td style="padding: 8px 0;"><span style="color: #28a745;"><span class="dashicons dashicons-yes-alt"></span> Active</span></td>
                </tr>
                <tr>
                    <td style="padding: 8px 0;"><strong>Login URL:</strong></td>
                    <td style="padding: 8px 0;"><a href="<?php echo home_url('/login/'); ?>" target="_blank"><?php echo home_url('/login/'); ?></a></td>
                </tr>
            </table>
        </div>
    </div>
    <?php
}

// ============================================
// FAB SETTINGS ADMIN PAGE
// ============================================
function tls_fab_settings_admin_page() {
    global $tls_fab_system;
    if ($tls_fab_system) {
        $tls_fab_system->render_admin_page();
    }
}

// ============================================
// AGENTS ADMIN PAGE
// ============================================
function tls_agents_admin_page() {
    $action = isset($_GET['action']) ? $_GET['action'] : 'list';
    $post_id = isset($_GET['post_id']) ? intval($_GET['post_id']) : 0;
    
    if ($action === 'delete' && $post_id) {
        wp_delete_post($post_id, true);
        wp_redirect(admin_url('admin.php?page=tls-agents'));
        exit;
    }
    
    $agents = new WP_Query([
        'post_type' => 'tls_agent',
        'posts_per_page' => -1,
        'orderby' => 'title',
        'order' => 'ASC'
    ]);
    
    ?>
    <div class="wrap">
        <h1>Property Agents
            <a href="<?php echo admin_url('post-new.php?post_type=tls_agent'); ?>" class="page-title-action">Add New Agent</a>
        </h1>
        
        <style>
        .agent-list { background: #fff; border-radius: 8px; margin-top: 20px; }
        .agent-list table { width: 100%; border-collapse: collapse; }
        .agent-list th { background: #f8f9fa; padding: 15px; text-align: left; font-weight: 600; }
        .agent-list td { padding: 15px; border-bottom: 1px solid #eee; vertical-align: middle; }
        .agent-avatar { width: 60px; height: 60px; border-radius: 50%; object-fit: cover; background: #eee; }
        </style>
        
        <div class="agent-list">
            <table>
                <thead>
                    <tr>
                        <th>Photo</th>
                        <th>Name</th>
                        <th>Company</th>
                        <th>License</th>
                        <th>Contact</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($agents->have_posts()): ?>
                        <?php while ($agents->have_posts()): $agents->the_post(); 
                            $post_id = get_the_ID();
                            $phone = get_post_meta($post_id, '_tls_agent_phone', true);
                            $email = get_post_meta($post_id, '_tls_agent_email', true);
                            $company = get_post_meta($post_id, '_tls_agent_company', true);
                            $license = get_post_meta($post_id, '_tls_agent_license', true);
                            $whatsapp = get_post_meta($post_id, '_tls_agent_whatsapp', true);
                            $avatar = get_the_post_thumbnail_url($post_id, 'thumbnail');
                        ?>
                        <tr>
                            <td>
                                <?php if ($avatar): ?>
                                <img src="<?php echo esc_attr($avatar); ?>" class="agent-avatar">
                                <?php else: ?>
                                <div class="agent-avatar"></div>
                                <?php endif; ?>
                            </td>
                            <td><strong><?php the_title(); ?></strong></td>
                            <td><?php echo esc_html($company ?: '-'); ?></td>
                            <td><?php echo esc_html($license ?: '-'); ?></td>
                            <td>
                                <?php if ($whatsapp): ?><a href="https://wa.me/<?php echo esc_attr($whatsapp); ?>" target="_blank">WhatsApp</a><?php endif; ?>
                                <?php if ($phone): ?> | <?php echo esc_html($phone); ?><?php endif; ?>
                            </td>
                            <td>
                                <a href="<?php echo admin_url('post.php?post=' . $post_id . '&action=edit'); ?>">Edit</a> |
                                <a href="<?php echo admin_url('admin.php?page=tls-agents&action=delete&post_id=' . $post_id); ?>" onclick="return confirm('Delete this agent?')" style="color:#dc3545;">Delete</a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                        <?php wp_reset_postdata(); ?>
                    <?php else: ?>
                        <tr><td colspan="6" style="text-align:center;padding:40px;color:#666;">
                            No agents yet. <a href="<?php echo admin_url('post-new.php?post_type=tls_agent'); ?>">Add your first agent</a>
                        </td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php
}

// Hero Videos Admin Page
function tls_hero_videos_page() {
    $action = isset($_GET['action']) ? $_GET['action'] : 'list';
    $post_id = isset($_GET['post_id']) ? intval($_GET['post_id']) : 0;
    
    if ($action === 'delete' && $post_id) {
        wp_delete_post($post_id, true);
        wp_redirect(admin_url('admin.php?page=tls-hero-videos'));
        exit;
    }
    
    if ($action === 'toggle' && $post_id) {
        $current = get_post_meta($post_id, 'hero_is_active', true);
        update_post_meta($post_id, 'hero_is_active', $current ? 0 : 1);
        wp_redirect(admin_url('admin.php?page=tls-hero-videos'));
        exit;
    }
    
    $videos = new WP_Query([
        'post_type' => 'hero_video',
        'posts_per_page' => -1,
        'orderby' => 'meta_value_num',
        'meta_key' => 'hero_display_order',
        'order' => 'ASC'
    ]);
    
    $whatsapp = get_theme_mod('whatsapp_number', '60123456789');
    ?>
    <div class="wrap">
        <h1>Hero Videos Management
            <a href="<?php echo admin_url('post-new.php?post_type=hero_video'); ?>" class="page-title-action">Add New</a>
        </h1>
        
        <style>
            .hero-video-list { background: #fff; border-radius: 8px; margin-top: 20px; }
            .hero-video-list table { width: 100%; border-collapse: collapse; }
            .hero-video-list th { background: #f8f9fa; padding: 15px; text-align: left; font-weight: 600; }
            .hero-video-list td { padding: 15px; border-bottom: 1px solid #eee; vertical-align: middle; }
            .hero-thumb { width: 120px; height: 68px; object-fit: cover; border-radius: 4px; background: #eee; }
            .hero-video-list .status-active { color: #198754; font-weight: 600; }
            .hero-video-list .status-inactive { color: #dc3545; }
            .hero-video-list .type-badge { background: #e9ecef; padding: 4px 10px; border-radius: 4px; font-size: 12px; }
            .hero-video-list .type-image { background: #6f42c1; color: #fff; }
            .hero-video-list .type-youtube { background: #ff0000; color: #fff; }
            .row-actions { margin-top: 5px; }
            .row-actions a { margin-right: 10px; font-size: 12px; }
        </style>
        
        <div class="hero-video-list">
            <table>
                <thead>
                    <tr>
                        <th>Thumbnail</th>
                        <th>Title</th>
                        <th>Type</th>
                        <th>URL/Image</th>
                        <th>Order</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($videos->have_posts()): ?>
                        <?php while ($videos->have_posts()): $videos->the_post(); 
                            $post_id = get_the_ID();
                            $video_url = get_post_meta($post_id, 'hero_video_url', true);
                            $video_type = get_post_meta($post_id, 'hero_video_type', true);
                            $display_order = get_post_meta($post_id, 'hero_display_order', true);
                            $is_active = get_post_meta($post_id, 'hero_is_active', true);
                            $video_disabled = get_post_meta($post_id, 'hero_video_disabled', true);
                            $thumb = get_the_post_thumbnail_url($post_id, 'thumbnail');
                            if (!$thumb && $video_type === 'image' && $video_url) {
                                $thumb = $video_url;
                            }
                        ?>
                        <tr>
                            <td>
                                <?php if ($thumb): ?>
                                <img src="<?php echo esc_attr($thumb); ?>" class="hero-thumb" alt="">
                                <?php else: ?>
                                <div class="hero-thumb" style="display:flex;align-items:center;justify-content:center;color:#999;">
                                    <span>No image</span>
                                </div>
                                <?php endif; ?>
                            </td>
                            <td><strong><?php the_title(); ?></strong></td>
                            <td>
                                <span class="type-badge <?php echo $video_type === 'image' ? 'type-image' : 'type-youtube'; ?>">
                                    <?php echo $video_type === 'image' ? 'Image' : 'YouTube'; ?>
                                </span>
                            </td>
                            <td style="max-width:200px;word-break:break-all;font-size:12px;color:#666;">
                                <?php echo esc_html($video_url ?: '-'); ?>
                            </td>
                            <td><?php echo esc_html($display_order); ?></td>
                            <td>
                                <?php if ($video_disabled): ?>
                                <span class="type-badge type-image">Image Only</span>
                                <?php else: ?>
                                <span class="<?php echo $is_active ? 'status-active' : 'status-inactive'; ?>">
                                    <?php echo $is_active ? 'Active' : 'Inactive'; ?>
                                </span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="row-actions">
                                    <a href="<?php echo admin_url('post.php?post=' . $post_id . '&action=edit'); ?>">Edit</a>
                                    <a href="<?php echo admin_url('admin.php?page=tls-hero-videos&action=toggle&post_id=' . $post_id); ?>">
                                        <?php echo $is_active ? 'Deactivate' : 'Activate'; ?>
                                    </a>
                                    <a href="<?php echo admin_url('admin.php?page=tls-hero-videos&action=delete&post_id=' . $post_id); ?>" onclick="return confirm('Delete this video?')" style="color:#dc3545;">Delete</a>
                                </div>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                        <?php wp_reset_postdata(); ?>
                    <?php else: ?>
                        <tr><td colspan="7" style="text-align:center;padding:40px;color:#666;">
                            No hero videos yet. <a href="<?php echo admin_url('post-new.php?post_type=hero_video'); ?>">Add your first one</a>
                        </td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php
}

// Dashboard Page
function tls_dashboard_page() {
    global $wpdb;

    // Get statistics
    $tanah_count = wp_count_posts('tanah')->publish;
    $leads_count = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}tls_leads");
    $calc_leads = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}tls_ldc_estimates");
    $agents_count = wp_count_posts('tls_agent')->publish;
    $videos_count = wp_count_posts('hero_video')->publish;
    $gadai_count = wp_count_posts('gadai_contract')->publish;

    // Get recent activity
    $recent_leads = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}tls_leads ORDER BY created_at DESC LIMIT 5");

    ?>
    <div class="wrap tls-dashboard-wrap">
        <h1 class="tls-dashboard-title">TLS System Dashboard</h1>
        <p class="tls-dashboard-subtitle">Welcome to your property management system</p>

        <!-- Statistics Cards -->
        <div class="tls-stats-grid">
            <div class="tls-stat-card tls-stat-primary">
                <div class="tls-stat-icon">
                    <span class="dashicons dashicons-location-alt"></span>
                </div>
                <div class="tls-stat-content">
                    <div class="tls-stat-number"><?php echo number_format($tanah_count); ?></div>
                    <div class="tls-stat-label">Properties</div>
                </div>
                <a href="<?php echo admin_url('edit.php?post_type=tanah'); ?>" class="tls-stat-link">View All →</a>
            </div>

            <div class="tls-stat-card tls-stat-success">
                <div class="tls-stat-icon">
                    <span class="dashicons dashicons-businessperson"></span>
                </div>
                <div class="tls-stat-content">
                    <div class="tls-stat-number"><?php echo number_format($leads_count); ?></div>
                    <div class="tls-stat-label">Property Leads</div>
                </div>
                <a href="<?php echo admin_url('admin.php?page=tls-leads'); ?>" class="tls-stat-link">Manage →</a>
            </div>

            <div class="tls-stat-card tls-stat-info">
                <div class="tls-stat-icon">
                    <span class="dashicons dashicons-calculator"></span>
                </div>
                <div class="tls-stat-content">
                    <div class="tls-stat-number"><?php echo number_format($calc_leads); ?></div>
                    <div class="tls-stat-label">Calculator Leads</div>
                </div>
                <a href="<?php echo admin_url('admin.php?page=tls-calculator'); ?>" class="tls-stat-link">View →</a>
            </div>

            <div class="tls-stat-card tls-stat-warning">
                <div class="tls-stat-icon">
                    <span class="dashicons dashicons-groups"></span>
                </div>
                <div class="tls-stat-content">
                    <div class="tls-stat-number"><?php echo number_format($agents_count); ?></div>
                    <div class="tls-stat-label">Agents</div>
                </div>
                <a href="<?php echo admin_url('admin.php?page=tls-agents'); ?>" class="tls-stat-link">Manage →</a>
            </div>
        </div>

        <!-- Quick Links -->
        <div class="tls-quick-actions">
            <h2>Quick Actions</h2>
            <div class="tls-action-buttons">
                <a href="<?php echo admin_url('post-new.php?post_type=tanah'); ?>" class="button button-primary button-hero">
                    <span class="dashicons dashicons-plus-alt" style="margin-top:3px;"></span> Add New Property
                </a>
                <a href="<?php echo admin_url('edit.php?post_type=tanah'); ?>" class="button button-secondary button-hero">
                    <span class="dashicons dashicons-edit" style="margin-top:3px;"></span> Draw Boundary
                </a>
                <a href="<?php echo admin_url('admin.php?page=tls-settings'); ?>" class="button button-secondary button-hero">
                    <span class="dashicons dashicons-admin-generic" style="margin-top:3px;"></span> Settings
                </a>
            </div>
        </div>

        <style>
            .tls-dashboard-wrap { max-width: 1400px; }
            .tls-dashboard-title { font-size: 32px; margin-bottom: 8px; color: #1e293b; }
            .tls-dashboard-subtitle { font-size: 16px; color: #64748b; margin-bottom: 30px; }

            .tls-stats-grid {
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
                gap: 20px;
                margin-bottom: 40px;
            }

            .tls-stat-card {
                background: white;
                border-radius: 12px;
                padding: 24px;
                box-shadow: 0 1px 3px rgba(0,0,0,0.1);
                border-left: 4px solid #cbd5e1;
                transition: all 0.3s ease;
                display: flex;
                flex-direction: column;
                gap: 16px;
            }

            .tls-stat-card:hover {
                box-shadow: 0 4px 12px rgba(0,0,0,0.15);
                transform: translateY(-2px);
            }

            .tls-stat-primary { border-left-color: #3b82f6; }
            .tls-stat-success { border-left-color: #10b981; }
            .tls-stat-info { border-left-color: #8b5cf6; }
            .tls-stat-warning { border-left-color: #f59e0b; }

            .tls-stat-icon {
                width: 48px;
                height: 48px;
                border-radius: 10px;
                display: flex;
                align-items: center;
                justify-content: center;
                background: #f1f5f9;
            }

            .tls-stat-primary .tls-stat-icon { background: #eff6ff; color: #3b82f6; }
            .tls-stat-success .tls-stat-icon { background: #f0fdf4; color: #10b981; }
            .tls-stat-info .tls-stat-icon { background: #f5f3ff; color: #8b5cf6; }
            .tls-stat-warning .tls-stat-icon { background: #fffbeb; color: #f59e0b; }

            .tls-stat-icon .dashicons { font-size: 24px; width: 24px; height: 24px; }

            .tls-stat-content { flex: 1; }

            .tls-stat-number {
                font-size: 36px;
                font-weight: 700;
                color: #1e293b;
                line-height: 1;
                margin-bottom: 8px;
            }

            .tls-stat-label {
                font-size: 14px;
                color: #64748b;
                font-weight: 500;
            }

            .tls-stat-link {
                color: #3b82f6;
                text-decoration: none;
                font-size: 14px;
                font-weight: 600;
                transition: color 0.2s;
            }

            .tls-stat-link:hover { color: #2563eb; }

            .tls-quick-actions h2 {
                font-size: 20px;
                color: #1e293b;
                margin-bottom: 16px;
            }

            .tls-action-buttons {
                display: flex;
                gap: 12px;
                flex-wrap: wrap;
            }

            .tls-action-buttons .button-hero {
                display: inline-flex;
                align-items: center;
                gap: 8px;
                padding: 12px 24px;
                font-size: 14px;
            }
        </style>
    </div>
    <?php
}

// Combined Settings Page with Contact Info
function tls_settings_page() {
    $saved = false;
    $error = '';
    
    if (isset($_POST['tls_save_settings']) && wp_verify_nonce($_POST['tls_settings_nonce'], 'tls_save_settings')) {
        $whatsapp = sanitize_text_field($_POST['whatsapp_number']);
        $phone = sanitize_text_field($_POST['phone_number']);
        $email = sanitize_email($_POST['email']);
        $company = sanitize_text_field($_POST['company_name']);
        $license = sanitize_text_field($_POST['license_number']);
        $address = sanitize_textarea_field($_POST['address']);
        $show_sticky = isset($_POST['show_sticky']) ? true : false;
        $show_footer = isset($_POST['show_mobile_footer']) ? true : false;
        $facebook = sanitize_url($_POST['facebook_url'] ?? '');
        $instagram = sanitize_url($_POST['instagram_url'] ?? '');
        $tiktok = sanitize_url($_POST['tiktok_url'] ?? '');

        if (empty($whatsapp)) {
            $error = 'WhatsApp number is required.';
        } else {
            set_theme_mod('whatsapp_number', $whatsapp);
            set_theme_mod('phone_number', $phone);
            set_theme_mod('email_address', $email);
            set_theme_mod('company_name', $company);
            set_theme_mod('license_number', $license);
            set_theme_mod('company_address', $address);
            set_theme_mod('show_sticky_calculator', $show_sticky);
            set_theme_mod('show_mobile_footer', $show_footer);
            set_theme_mod('facebook_url', $facebook);
            set_theme_mod('instagram_url', $instagram);
            set_theme_mod('tiktok_url', $tiktok);
            $saved = true;
        }
    }

    $whatsapp = get_theme_mod('whatsapp_number', '60123456789');
    $phone = get_theme_mod('phone_number', '');
    $email = get_theme_mod('email_address', 'info@tanahlotsabah.com');
    $company = get_theme_mod('company_name', 'TanahLotSabah');
    $license = get_theme_mod('license_number', '');
    $address = get_theme_mod('company_address', '');
    $show_sticky = get_theme_mod('show_sticky_calculator', true);
    $show_footer = get_theme_mod('show_mobile_footer', true);
    $facebook = get_theme_mod('facebook_url', 'https://facebook.com/tanahlotsabah');
    $instagram = get_theme_mod('instagram_url', 'https://instagram.com/tanahlotsabah');
    $tiktok = get_theme_mod('tiktok_url', 'https://tiktok.com/@tanahlotsabah');
    ?>
    <div class="wrap">
        <h1>Contact Settings</h1>
        
        <?php if ($saved): ?>
        <div class="notice notice-success"><p>Settings saved successfully!</p></div>
        <?php endif; ?>
        
        <?php if ($error): ?>
        <div class="notice notice-error"><p><?php echo $error; ?></p></div>
        <?php endif; ?>
        
        <div class="tls-settings-layout">
            <div class="tls-settings-main">
                <form method="post" class="tls-settings-form">
                    <?php wp_nonce_field('tls_save_settings', 'tls_settings_nonce'); ?>
                    
                    <h2>Contact Information</h2>
                    <p class="description">This information will be displayed on your website and used for WhatsApp/Call buttons.</p>
                    
                    <table class="form-table">
                        <tr>
                            <th scope="row"><label for="whatsapp_number">WhatsApp Number *</label></th>
                            <td>
                                <input type="text" name="whatsapp_number" id="whatsapp_number" class="regular-text" value="<?php echo esc_attr($whatsapp); ?>" required>
                                <p class="description">Format: 60123456789 (no + or spaces)</p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="phone_number">Phone Number</label></th>
                            <td>
                                <input type="text" name="phone_number" id="phone_number" class="regular-text" value="<?php echo esc_attr($phone); ?>">
                                <p class="description">Secondary phone number (optional)</p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="email">Email</label></th>
                            <td>
                                <input type="email" name="email" id="email" class="regular-text" value="<?php echo esc_attr($email); ?>">
                            </td>
                        </tr>
                    </table>
                    
                    <h2>Social Media Links</h2>
                    <table class="form-table">
                        <tr>
                            <th scope="row"><label for="facebook_url">Facebook URL</label></th>
                            <td><input type="url" name="facebook_url" id="facebook_url" class="regular-text" value="<?php echo esc_attr($facebook); ?>"></td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="instagram_url">Instagram URL</label></th>
                            <td><input type="url" name="instagram_url" id="instagram_url" class="regular-text" value="<?php echo esc_attr($instagram); ?>"></td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="tiktok_url">TikTok URL</label></th>
                            <td><input type="url" name="tiktok_url" id="tiktok_url" class="regular-text" value="<?php echo esc_attr($tiktok); ?>"></td>
                        </tr>
                    </table>

                    <h2>Company Information</h2>
                    
                    <table class="form-table">
                        <tr>
                            <th scope="row"><label for="company_name">Company Name</label></th>
                            <td>
                                <input type="text" name="company_name" id="company_name" class="regular-text" value="<?php echo esc_attr($company); ?>">
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="license_number">License Number (Ejen)</label></th>
                            <td>
                                <input type="text" name="license_number" id="license_number" class="regular-text" value="<?php echo esc_attr($license); ?>">
                                <p class="description">Your real estate agent license number</p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="address">Company Address</label></th>
                            <td>
                                <textarea name="address" id="address" rows="3" class="widefat"><?php echo esc_textarea($address); ?></textarea>
                            </td>
                        </tr>
                    </table>
                    
                    <h2>Display Settings</h2>
                    
                    <table class="form-table">
                        <tr>
                            <th scope="row">Floating Calculator</th>
                            <td>
                                <label>
                                    <input type="checkbox" name="show_sticky" value="1" <?php checked($show_sticky, true); ?>>
                                    Show floating calculator button on website
                                </label>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">Mobile Sticky Footer</th>
                            <td>
                                <label>
                                    <input type="checkbox" name="show_mobile_footer" value="1" <?php checked($show_footer, true); ?>>
                                    Show WhatsApp/Call sticky bar on mobile
                                </label>
                                <p class="description">Control visibility of the fixed bar at the bottom of mobile screens.</p>
                            </td>
                        </tr>
                    </table>
                    
                    <p class="submit">
                        <input type="submit" name="tls_save_settings" class="button button-primary" value="Save Settings">
                    </p>
                </form>
            </div>
            
            <div class="tls-settings-sidebar">
                <div class="tls-settings-preview">
                    <h3>Preview</h3>
                    <div class="preview-box">
                        <p><strong>WhatsApp:</strong> +<?php echo esc_html($whatsapp); ?></p>
                        <p><strong>Phone:</strong> <?php echo esc_html($phone ?: 'Not set'); ?></p>
                        <p><strong>Email:</strong> <?php echo esc_html($email); ?></p>
                        <p><strong>Company:</strong> <?php echo esc_html($company); ?></p>
                    </div>
                    <a href="<?php echo esc_url(home_url('/')); ?>" class="button" target="_blank">View Website</a>
                </div>
            </div>
        </div>
        
        <style>
            .tls-settings-layout { display: grid; grid-template-columns: 1fr 300px; gap: 30px; margin-top: 20px; }
            .tls-settings-form { background: #fff; padding: 30px; border-radius: 8px; }
            .tls-settings-form h2 { margin-top: 30px; padding-top: 20px; border-top: 1px solid #eee; }
            .tls-settings-form h2:first-child { margin-top: 0; padding-top: 0; border-top: none; }
            .tls-settings-preview { background: #fff; padding: 20px; border-radius: 8px; position: sticky; top: 30px; }
            .tls-settings-preview h3 { margin-top: 0; }
            .preview-box { background: #f8fafc; padding: 15px; border-radius: 6px; margin: 15px 0; }
            .preview-box p { margin: 5px 0; font-size: 13px; }
            @media (max-width: 1024px) { .tls-settings-layout { grid-template-columns: 1fr; } }
        </style>
    </div>
    <?php
}

function tls_ldc_admin_page() {
    $stats = TLS_LDC_Database::get_estimate_stats();
    $estimates = TLS_LDC_Database::get_all_estimates(50);
    ?>
    <div class="wrap">
        <h1>Land Development Calculator - Leads</h1>
        
        <div class="ldc-admin-stats">
            <div class="stat-card">
                <h3><?php echo $stats['total']; ?></h3>
                <p>Total Leads</p>
            </div>
            <div class="stat-card">
                <h3>RM <?php echo number_format($stats['total_value'], 2); ?></h3>
                <p>Total Value</p>
            </div>
            <div class="stat-card">
                <h3><?php echo $stats['today']; ?></h3>
                <p>Today</p>
            </div>
            <div class="stat-card">
                <h3><?php echo $stats['week']; ?></h3>
                <p>This Week</p>
            </div>
        </div>
        
        <style>
            .ldc-admin-stats { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin: 30px 0; }
            .stat-card { background: #fff; padding: 20px; border-radius: 8px; border-left: 4px solid #2c5f2d; }
            .stat-card h3 { margin: 0; font-size: 28px; color: #2c5f2d; }
            .stat-card p { margin: 5px 0 0; color: #666; }
            .ldc-admin-table { background: #fff; border-radius: 8px; overflow: hidden; margin-top: 20px; }
            .ldc-admin-table table { width: 100%; border-collapse: collapse; }
            .ldc-admin-table th { background: #f0f0f0; padding: 12px; text-align: left; font-weight: 600; }
            .ldc-admin-table td { padding: 12px; border-bottom: 1px solid #eee; }
            .ldc-admin-table tr:hover { background: #f9f9f9; }
        </style>
        
        <div class="ldc-admin-table">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Klien</th>
                        <th>Email</th>
                        <th>Telefon</th>
                        <th>Saiz</th>
                        <th>Lokasi</th>
                        <th>Kos</th>
                        <th>Agent</th>
                        <th>Tarikh</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($estimates as $est): 
                        $items = json_decode($est['items'], true);
                    ?>
                    <tr>
                        <td><strong>#<?php echo $est['id']; ?></strong></td>
                        <td><?php echo esc_html($est['client_name']); ?></td>
                        <td><a href="mailto:<?php echo esc_attr($est['client_email']); ?>"><?php echo esc_html($est['client_email']); ?></a></td>
                        <td><a href="https://wa.me/<?php echo esc_attr($est['client_phone']); ?>" target="_blank">+<?php echo esc_html($est['client_phone']); ?></a></td>
                        <td><?php echo number_format($est['land_size'], 2); ?> <?php echo esc_html($est['land_unit']); ?></td>
                        <td><?php echo esc_html($est['location']); ?></td>
                        <td><strong>RM <?php echo number_format($est['total_cost'], 2); ?></strong></td>
                        <td><?php echo esc_html($est['agent_id']); ?></td>
                        <td><?php echo date('d/m/Y H:i', strtotime($est['created_at'])); ?></td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (empty($estimates)): ?>
                    <tr><td colspan="9" style="text-align:center; padding:30px;">Tiada lead lagi.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php
}

function tls_ldc_agents_page() {
    $agents = TLS_LDC_Database::get_all_agents();
    ?>
    <div class="wrap">
        <h1>Agents Management</h1>
        
        <style>
            .agent-card { background: #fff; padding: 20px; border-radius: 8px; margin-bottom: 15px; display: flex; justify-content: space-between; align-items: center; }
            .agent-info h3 { margin: 0 0 5px; }
            .agent-info p { margin: 3px 0; color: #666; font-size: 14px; }
            .agent-badge { padding: 5px 12px; border-radius: 20px; font-size: 12px; font-weight: bold; }
            .agent-badge.admin { background: #e3f2fd; color: #1565c0; }
            .agent-badge.active { background: #e8f5e9; color: #2e7d32; }
            .agent-badge.inactive { background: #ffebee; color: #c62828; }
        </style>
        
        <h2 style="margin-top:30px;">Senarai Ejen</h2>
        
        <?php foreach ($agents as $agent): ?>
        <div class="agent-card">
            <div class="agent-info">
                <h3><?php echo esc_html($agent['name']); ?> (<?php echo esc_html($agent['id']); ?>)</h3>
                <p><?php echo esc_html($agent['email']); ?> | <?php echo esc_html($agent['phone']); ?></p>
                <p>Last Login: <?php echo $agent['last_login'] ? date('d/m/Y H:i', strtotime($agent['last_login'])) : 'Never'; ?></p>
            </div>
            <div>
                <?php if ($agent['is_admin']): ?>
                <span class="agent-badge admin">Admin</span>
                <?php endif; ?>
                <span class="agent-badge <?php echo $agent['is_active'] ? 'active' : 'inactive'; ?>">
                    <?php echo $agent['is_active'] ? 'Active' : 'Inactive'; ?>
                </span>
            </div>
        </div>
        <?php endforeach; ?>
        
        <div style="background: #fff3cd; padding: 20px; border-radius: 8px; margin-top: 30px;">
            <h3>Default Login</h3>
            <p><strong>Agent ID:</strong> TLS001 - TLS005</p>
            <p><strong>Password:</strong> demo123</p>
        </div>
    </div>
    <?php
}

function tls_ldc_settings_page() {
    $show_sticky = get_theme_mod('show_sticky_calculator', true);
    
    if (isset($_POST['save_settings']) && wp_verify_nonce($_POST['settings_nonce'], 'tls_settings')) {
        $show_sticky = isset($_POST['show_sticky']) ? true : false;
        set_theme_mod('show_sticky_calculator', $show_sticky);
        echo '<div class="notice notice-success"><p>Settings saved!</p></div>';
    }
    ?>
    <div class="wrap">
        <h1>Calculator Settings</h1>
        
        <div style="background: #fff; padding: 30px; border-radius: 8px; margin-top: 20px; max-width: 600px;">
            <form method="post">
                <?php wp_nonce_field('tls_settings', 'settings_nonce'); ?>
                
                <h2>Display Settings</h2>
                
                <table class="form-table">
                    <tr>
                        <th scope="row">Floating Calculator Button</th>
                        <td>
                            <label>
                                <input type="checkbox" name="show_sticky" value="1" <?php checked($show_sticky, true); ?>>
                                Show floating calculator button on frontend
                            </label>
                            <p class="description">When enabled, a calculator button will appear at the bottom-right of your website.</p>
                        </td>
                    </tr>
                </table>
                
                <p class="submit">
                    <input type="submit" name="save_settings" class="button button-primary" value="Save Settings">
                </p>
            </form>
            
            <hr style="margin: 30px 0;">
            
            <h2>Theme Integration</h2>
            <p>This calculator is integrated into the <strong>tls-theme</strong>.</p>
            
            <h3 style="margin-top:20px;">Shortcode</h3>
            <code>[land_dev_calculator]</code>
            <p>Use this shortcode to display the calculator on any page.</p>
            
            <h3 style="margin-top:20px;">Calculator Page</h3>
            <p>Create a page with slug <code>calculator</code> and template "Land Development Calculator"</p>
        </div>
    </div>
    <?php
}

// ============================================
// PRICING MANAGEMENT PAGE
// ============================================
function tls_pricing_management_page() {
    global $wpdb;
    $pricing_table = $wpdb->prefix . TLS_LDC_PREFIX . 'pricing';

    // Handle CSV Import
    if (isset($_POST['import_csv']) && wp_verify_nonce($_POST['pricing_nonce'], 'pricing_action')) {
        if (isset($_FILES['csv_file']) && $_FILES['csv_file']['error'] === 0) {
            $file = $_FILES['csv_file']['tmp_name'];
            $handle = fopen($file, 'r');
            $row = 0;
            $imported = 0;
            $errors = [];

            while (($data = fgetcsv($handle, 1000, ',')) !== FALSE) {
                $row++;
                if ($row === 1) continue; // Skip header row

                // Validate data
                if (count($data) < 5) {
                    $errors[] = "Row $row: Not enough columns";
                    continue;
                }

                $wpdb->insert($pricing_table, [
                    'category' => sanitize_text_field($data[0]),
                    'category_icon' => sanitize_text_field($data[1] ?: '📦'),
                    'category_description' => sanitize_textarea_field($data[2] ?: ''),
                    'name' => sanitize_text_field($data[3]),
                    'unit' => sanitize_text_field($data[4]),
                    'unit_price' => floatval($data[5]),
                    'is_active' => isset($data[6]) && $data[6] === '1' ? 1 : 1,
                    'sort_order' => isset($data[7]) ? intval($data[7]) : 0
                ]);
                $imported++;
            }
            fclose($handle);

            echo '<div class="notice notice-success"><p>CSV Import Complete! Imported ' . $imported . ' items.</p></div>';
            if (!empty($errors)) {
                echo '<div class="notice notice-warning"><p>Errors: ' . implode('<br>', $errors) . '</p></div>';
            }
        } else {
            echo '<div class="notice notice-error"><p>File upload error!</p></div>';
        }
    }

    // Handle Add/Edit/Delete
    if (isset($_POST['action'])) {
        if ($_POST['action'] === 'add' && wp_verify_nonce($_POST['pricing_nonce'], 'pricing_action')) {
            $wpdb->insert($pricing_table, [
                'category' => sanitize_text_field($_POST['category']),
                'category_icon' => sanitize_text_field($_POST['category_icon']),
                'category_description' => sanitize_textarea_field($_POST['category_description']),
                'name' => sanitize_text_field($_POST['name']),
                'unit' => sanitize_text_field($_POST['unit']),
                'unit_price' => floatval($_POST['unit_price']),
                'is_active' => isset($_POST['is_active']) ? 1 : 0,
                'sort_order' => intval($_POST['sort_order'])
            ]);
            echo '<div class="notice notice-success"><p>Item added successfully!</p></div>';
        } elseif ($_POST['action'] === 'edit' && wp_verify_nonce($_POST['pricing_nonce'], 'pricing_action')) {
            $wpdb->update($pricing_table, [
                'category' => sanitize_text_field($_POST['category']),
                'category_icon' => sanitize_text_field($_POST['category_icon']),
                'category_description' => sanitize_textarea_field($_POST['category_description']),
                'name' => sanitize_text_field($_POST['name']),
                'unit' => sanitize_text_field($_POST['unit']),
                'unit_price' => floatval($_POST['unit_price']),
                'is_active' => isset($_POST['is_active']) ? 1 : 0,
                'sort_order' => intval($_POST['sort_order'])
            ], ['id' => intval($_POST['item_id'])]);
            echo '<div class="notice notice-success"><p>Item updated successfully!</p></div>';
        } elseif ($_POST['action'] === 'delete' && wp_verify_nonce($_POST['pricing_nonce'], 'pricing_action')) {
            $wpdb->delete($pricing_table, ['id' => intval($_POST['item_id'])]);
            echo '<div class="notice notice-success"><p>Item deleted successfully!</p></div>';
        }
    }

    // Get all pricing items
    $items = $wpdb->get_results("SELECT * FROM $pricing_table ORDER BY category, sort_order");
    $categories = $wpdb->get_col("SELECT DISTINCT category FROM $pricing_table ORDER BY category");

    // Get item for editing
    $edit_item = null;
    if (isset($_GET['edit'])) {
        $edit_item = $wpdb->get_row($wpdb->prepare("SELECT * FROM $pricing_table WHERE id = %d", intval($_GET['edit'])));
    }
    ?>
    <div class="wrap">
        <h1><i class="material-icons">construction</i> Construction Pricing Management</h1>
        <p>Manage pricing for the Land Development Calculator. These prices will be used when users calculate construction costs.</p>

        <!-- CSV IMPORT SECTION -->
        <div style="background: #fff; padding: 20px; border-radius: 8px; margin-top: 20px; border-left: 4px solid #28a745;">
            <h3>📥 Bulk Import via CSV</h3>
            <form method="post" enctype="multipart/form-data" style="display: flex; align-items: center; gap: 15px;">
                <?php wp_nonce_field('pricing_action', 'pricing_nonce'); ?>
                <input type="file" name="csv_file" accept=".csv" required>
                <button type="submit" name="import_csv" class="button button-primary">Import CSV</button>
                <a href="#" onclick="downloadCSVTemplate(); return false;" class="button">Download Template</a>
            </form>
            <p style="margin-top: 10px; color: #666; font-size: 13px;">
                <strong>CSV Format:</strong> category, category_icon, category_description, name, unit, unit_price, is_active, sort_order
            </p>
        </div>

        <script>
        function downloadCSVTemplate() {
            const csvContent = "category,category_icon,category_description,name,unit,unit_price,is_active,sort_order\n" +
                              "Pagar & Gerbang,🚧,Kerja pagar dan gerbang,Pagar Besi 6 Kaki,meter,120.00,1,0\n" +
                              "Pagar & Gerbang,🚧,Kerja pagar dan gerbang,Gerbang Besi 2 Panel,unit,1500.00,1,1\n" +
                              "Penyediaan Tapak,<i class="material-icons">construction</i>,Kerja penyediaan tanah,Pembersihan Tanah,sqft,2.50,1,0\n" +
                              "Binaan Rumah,<i class="material-icons">construction</i>,Kerja pembinaan rumah,Asas Concrete,sqft,15.00,1,0";

            const blob = new Blob([csvContent], { type: 'text/csv' });
            const url = window.URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = 'pricing_template.csv';
            a.click();
            window.URL.revokeObjectURL(url);
        }
        </script>

        <div style="display: grid; grid-template-columns: 400px 1fr; gap: 30px; margin-top: 30px;">
            <!-- ADD/EDIT FORM -->
            <div style="background: #fff; padding: 25px; border-radius: 8px; height: fit-content;">
                <h2><?php echo $edit_item ? 'Edit Item' : 'Add New Item'; ?></h2>
                <form method="post">
                    <?php wp_nonce_field('pricing_action', 'pricing_nonce'); ?>
                    <input type="hidden" name="action" value="<?php echo $edit_item ? 'edit' : 'add'; ?>">
                    <?php if ($edit_item): ?>
                    <input type="hidden" name="item_id" value="<?php echo $edit_item->id; ?>">
                    <?php endif; ?>

                    <table class="form-table">
                        <tr>
                            <th>Category</th>
                            <td>
                                <input type="text" name="category" class="regular-text" value="<?php echo esc_attr($edit_item->category ?? ''); ?>" required list="category_list">
                                <datalist id="category_list">
                                    <?php foreach ($categories as $cat): ?>
                                    <option value="<?php echo esc_attr($cat); ?>">
                                    <?php endforeach; ?>
                                </datalist>
                            </td>
                        </tr>
                        <tr>
                            <th>Category Icon</th>
                            <td><input type="text" name="category_icon" class="regular-text" value="<?php echo esc_attr($edit_item->category_icon ?? '📦'); ?>"></td>
                        </tr>
                        <tr>
                            <th>Category Description</th>
                            <td><textarea name="category_description" class="large-text" rows="2"><?php echo esc_textarea($edit_item->category_description ?? ''); ?></textarea></td>
                        </tr>
                        <tr>
                            <th>Item Name</th>
                            <td><input type="text" name="name" class="regular-text" value="<?php echo esc_attr($edit_item->name ?? ''); ?>" required></td>
                        </tr>
                        <tr>
                            <th>Unit</th>
                            <td>
                                <input type="text" name="unit" class="regular-text" value="<?php echo esc_attr($edit_item->unit ?? 'sqft'); ?>" required>
                                <p class="description">Examples: sqft, meter, unit, lot</p>
                            </td>
                        </tr>
                        <tr>
                            <th>Unit Price (RM)</th>
                            <td><input type="number" step="0.01" name="unit_price" class="regular-text" value="<?php echo esc_attr($edit_item->unit_price ?? ''); ?>" required></td>
                        </tr>
                        <tr>
                            <th>Sort Order</th>
                            <td><input type="number" name="sort_order" class="small-text" value="<?php echo esc_attr($edit_item->sort_order ?? 0); ?>"></td>
                        </tr>
                        <tr>
                            <th>Active</th>
                            <td><label><input type="checkbox" name="is_active" value="1" <?php checked($edit_item->is_active ?? 1, 1); ?>> Show in calculator</label></td>
                        </tr>
                    </table>

                    <p class="submit">
                        <input type="submit" class="button button-primary" value="<?php echo $edit_item ? 'Update Item' : 'Add Item'; ?>">
                        <?php if ($edit_item): ?>
                            <a href="<?php echo admin_url('admin.php?page=tls-pricing-management'); ?>" class="button">Cancel</a>
                        <?php endif; ?>
                    </p>
                </form>
            </div>

            <!-- ITEMS LIST -->
            <div style="background: #fff; padding: 25px; border-radius: 8px;">
                <h2>Pricing Items (<?php echo count($items); ?>)</h2>
                <table class="wp-list-table widefat fixed striped">
                    <thead>
                        <tr>
                            <th>Category</th>
                            <th>Item Name</th>
                            <th>Unit</th>
                            <th>Price (RM)</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $current_category = '';
                        foreach ($items as $item):
                            if ($current_category !== $item->category) {
                                $current_category = $item->category;
                                echo '<tr style="background: #f8f9fa; font-weight: bold;"><td colspan="6">' . esc_html($item->category_icon . ' ' . $item->category) . '</td></tr>';
                            }
                        ?>
                        <tr>
                            <td><?php echo esc_html($item->category); ?></td>
                            <td><?php echo esc_html($item->name); ?></td>
                            <td><?php echo esc_html($item->unit); ?></td>
                            <td><?php echo number_format($item->unit_price, 2); ?></td>
                            <td><?php echo $item->is_active ? '<span style="color: green;">●</span> Active' : '<span style="color: red;">●</span> Inactive'; ?></td>
                            <td>
                                <a href="<?php echo admin_url('admin.php?page=tls-pricing-management&edit=' . $item->id); ?>" class="button button-small">Edit</a>
                                <form method="post" style="display: inline;" onsubmit="return confirm('Delete this item?');">
                                    <?php wp_nonce_field('pricing_action', 'pricing_nonce'); ?>
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="item_id" value="<?php echo $item->id; ?>">
                                    <button type="submit" class="button button-small" style="color: red;">Delete</button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <?php
}

// ============================================
// PURCHASE CALCULATOR SETTINGS PAGE
// ============================================
function tls_purchase_calculator_settings_page() {
    $message = '';

    // Save settings
    if (isset($_POST['save_purchase_calculator']) && wp_verify_nonce($_POST['purchase_calc_nonce'], 'purchase_calc_action')) {
        // Stamp Duty Rates
        update_option('tls_stamp_duty_tier1_rate', floatval($_POST['stamp_tier1_rate']));
        update_option('tls_stamp_duty_tier2_rate', floatval($_POST['stamp_tier2_rate']));
        update_option('tls_stamp_duty_tier3_rate', floatval($_POST['stamp_tier3_rate']));
        update_option('tls_stamp_duty_tier4_rate', floatval($_POST['stamp_tier4_rate']));

        // Legal Fees Rates
        update_option('tls_legal_fees_tier1_rate', floatval($_POST['legal_tier1_rate']));
        update_option('tls_legal_fees_tier2_rate', floatval($_POST['legal_tier2_rate']));
        update_option('tls_legal_fees_tier3_rate', floatval($_POST['legal_tier3_rate']));

        // MOT & Other Fees
        update_option('tls_mot_fees_rate', floatval($_POST['mot_rate']));
        update_option('tls_mot_fees_minimum', floatval($_POST['mot_minimum']));

        $message = '<div class="notice notice-success is-dismissible"><p>Settings saved successfully!</p></div>';
    }

    // Get current settings (with defaults)
    $stamp_tier1 = get_option('tls_stamp_duty_tier1_rate', 1.0); // 1%
    $stamp_tier2 = get_option('tls_stamp_duty_tier2_rate', 2.0); // 2%
    $stamp_tier3 = get_option('tls_stamp_duty_tier3_rate', 3.0); // 3%
    $stamp_tier4 = get_option('tls_stamp_duty_tier4_rate', 4.0); // 4%

    $legal_tier1 = get_option('tls_legal_fees_tier1_rate', 1.0); // 1%
    $legal_tier2 = get_option('tls_legal_fees_tier2_rate', 0.7); // 0.7%
    $legal_tier3 = get_option('tls_legal_fees_tier3_rate', 0.6); // 0.6%

    $mot_rate = get_option('tls_mot_fees_rate', 0.5); // 0.5%
    $mot_minimum = get_option('tls_mot_fees_minimum', 500); // RM500
    ?>

    <div class="wrap">
        <h1><span class="dashicons dashicons-calculator"></span> Purchase Calculator Settings (Kos Pembelian)</h1>
        <p>Adjust the fees and rates used in the Purchase Cost Calculator (MOT, Stamp Duty, Legal Fees).</p>

        <?php echo $message; ?>

        <form method="post" style="max-width: 900px;">
            <?php wp_nonce_field('purchase_calc_action', 'purchase_calc_nonce'); ?>

            <!-- Stamp Duty Section -->
            <div style="background: white; padding: 20px; margin: 20px 0; border: 1px solid #ccc; border-radius: 8px;">
                <h2 style="margin-top: 0; color: #2c5f2d;"><span class="dashicons dashicons-media-document"></span> Stamp Duty Rates</h2>
                <p style="color: #666; font-size: 13px;">Malaysia tiered stamp duty calculation. Enter rates as percentages (e.g., 1 = 1%).</p>

                <table class="form-table">
                    <tr>
                        <th>
                            <label>Tier 1: First RM100,000</label>
                            <p class="description">Rate for property value up to RM100,000</p>
                        </th>
                        <td>
                            <input type="number" step="0.01" name="stamp_tier1_rate" value="<?php echo esc_attr($stamp_tier1); ?>" style="width: 100px;"> %
                            <span class="description" style="margin-left: 10px; color: #2c5f2d;">Default: 1%</span>
                        </td>
                    </tr>
                    <tr>
                        <th>
                            <label>Tier 2: RM100,001 - RM500,000</label>
                            <p class="description">Rate for value above RM100k up to RM500k</p>
                        </th>
                        <td>
                            <input type="number" step="0.01" name="stamp_tier2_rate" value="<?php echo esc_attr($stamp_tier2); ?>" style="width: 100px;"> %
                            <span class="description" style="margin-left: 10px; color: #2c5f2d;">Default: 2%</span>
                        </td>
                    </tr>
                    <tr>
                        <th>
                            <label>Tier 3: RM500,001 - RM1,000,000</label>
                            <p class="description">Rate for value above RM500k up to RM1M</p>
                        </th>
                        <td>
                            <input type="number" step="0.01" name="stamp_tier3_rate" value="<?php echo esc_attr($stamp_tier3); ?>" style="width: 100px;"> %
                            <span class="description" style="margin-left: 10px; color: #2c5f2d;">Default: 3%</span>
                        </td>
                    </tr>
                    <tr>
                        <th>
                            <label>Tier 4: Above RM1,000,000</label>
                            <p class="description">Rate for value above RM1M</p>
                        </th>
                        <td>
                            <input type="number" step="0.01" name="stamp_tier4_rate" value="<?php echo esc_attr($stamp_tier4); ?>" style="width: 100px;"> %
                            <span class="description" style="margin-left: 10px; color: #2c5f2d;">Default: 4%</span>
                        </td>
                    </tr>
                </table>
            </div>

            <!-- Legal Fees Section -->
            <div style="background: white; padding: 20px; margin: 20px 0; border: 1px solid #ccc; border-radius: 8px;">
                <h2 style="margin-top: 0; color: #2c5f2d;"><span class="dashicons dashicons-businessman"></span> Legal Fees Rates</h2>
                <p style="color: #666; font-size: 13px;">Lawyer fees for property purchase. Enter rates as percentages.</p>

                <table class="form-table">
                    <tr>
                        <th>
                            <label>Tier 1: Up to RM150,000</label>
                            <p class="description">Rate for property value up to RM150k</p>
                        </th>
                        <td>
                            <input type="number" step="0.01" name="legal_tier1_rate" value="<?php echo esc_attr($legal_tier1); ?>" style="width: 100px;"> %
                            <span class="description" style="margin-left: 10px; color: #2c5f2d;">Default: 1%</span>
                        </td>
                    </tr>
                    <tr>
                        <th>
                            <label>Tier 2: RM150,001 - RM1,000,000</label>
                            <p class="description">Rate for value above RM150k up to RM1M</p>
                        </th>
                        <td>
                            <input type="number" step="0.01" name="legal_tier2_rate" value="<?php echo esc_attr($legal_tier2); ?>" style="width: 100px;"> %
                            <span class="description" style="margin-left: 10px; color: #2c5f2d;">Default: 0.7%</span>
                        </td>
                    </tr>
                    <tr>
                        <th>
                            <label>Tier 3: Above RM1,000,000</label>
                            <p class="description">Rate for value above RM1M</p>
                        </th>
                        <td>
                            <input type="number" step="0.01" name="legal_tier3_rate" value="<?php echo esc_attr($legal_tier3); ?>" style="width: 100px;"> %
                            <span class="description" style="margin-left: 10px; color: #2c5f2d;">Default: 0.6%</span>
                        </td>
                    </tr>
                </table>
            </div>

            <!-- MOT & Other Fees Section -->
            <div style="background: white; padding: 20px; margin: 20px 0; border: 1px solid #ccc; border-radius: 8px;">
                <h2 style="margin-top: 0; color: #2c5f2d;"><span class="dashicons dashicons-clipboard"></span> MOT & Other Fees</h2>
                <p style="color: #666; font-size: 13px;">Memorandum of Transfer and miscellaneous costs.</p>

                <table class="form-table">
                    <tr>
                        <th>
                            <label>MOT Fee Rate</label>
                            <p class="description">Percentage of property value</p>
                        </th>
                        <td>
                            <input type="number" step="0.01" name="mot_rate" value="<?php echo esc_attr($mot_rate); ?>" style="width: 100px;"> %
                            <span class="description" style="margin-left: 10px; color: #2c5f2d;">Default: 0.5%</span>
                        </td>
                    </tr>
                    <tr>
                        <th>
                            <label>MOT Minimum Fee</label>
                            <p class="description">Minimum charge regardless of property value</p>
                        </th>
                        <td>
                            RM <input type="number" step="1" name="mot_minimum" value="<?php echo esc_attr($mot_minimum); ?>" style="width: 100px;">
                            <span class="description" style="margin-left: 10px; color: #2c5f2d;">Default: RM500</span>
                        </td>
                    </tr>
                </table>
            </div>

            <!-- Save Button -->
            <p class="submit">
                <button type="submit" name="save_purchase_calculator" class="button button-primary button-large">
                    💾 Save Calculator Settings
                </button>
            </p>
        </form>
    </div>
    <?php
}

// ============================================
// CONSTRUCTION TEMPLATES MANAGEMENT PAGE
// ============================================
function tls_construction_templates_page() {
    global $wpdb;
    $templates_table = $wpdb->prefix . TLS_LDC_PREFIX . 'templates';
    $template_items_table = $wpdb->prefix . TLS_LDC_PREFIX . 'template_items';
    $pricing_table = $wpdb->prefix . TLS_LDC_PREFIX . 'pricing';

    $message = '';
    $editing_template = null;

    // Handle template actions (create, edit, delete)
    if (isset($_POST['action']) && wp_verify_nonce($_POST['template_nonce'], 'template_action')) {
        if ($_POST['action'] === 'save_template') {
            $template_data = [
                'name' => sanitize_text_field($_POST['template_name']),
                'description' => sanitize_textarea_field($_POST['template_description']),
                'icon' => sanitize_text_field($_POST['template_icon']),
                'is_active' => isset($_POST['is_active']) ? 1 : 0,
            ];

            if (!empty($_POST['template_id'])) {
                $wpdb->update($templates_table, $template_data, ['id' => intval($_POST['template_id'])]);
                $message = '<div class="notice notice-success"><p>Template updated successfully!</p></div>';
            } else {
                $wpdb->insert($templates_table, $template_data);
                $message = '<div class="notice notice-success"><p>Template created successfully!</p></div>';
            }
        } elseif ($_POST['action'] === 'delete_template') {
            $wpdb->delete($templates_table, ['id' => intval($_POST['template_id'])]);
            $message = '<div class="notice notice-success"><p>Template deleted successfully!</p></div>';
        } elseif ($_POST['action'] === 'add_item') {
            $wpdb->insert($template_items_table, [
                'template_id' => intval($_POST['template_id']),
                'pricing_id' => intval($_POST['pricing_id']),
                'default_quantity' => floatval($_POST['default_quantity']),
                'notes' => sanitize_textarea_field($_POST['item_notes']),
            ]);
            $message = '<div class="notice notice-success"><p>Item added to template!</p></div>';
        } elseif ($_POST['action'] === 'delete_item') {
            $wpdb->delete($template_items_table, ['id' => intval($_POST['item_id'])]);
            $message = '<div class="notice notice-success"><p>Item removed from template!</p></div>';
        }
    }

    // Check if editing
    if (isset($_GET['edit'])) {
        $editing_template = $wpdb->get_row($wpdb->prepare("SELECT * FROM $templates_table WHERE id = %d", intval($_GET['edit'])));
    }

    // Get all templates
    $templates = $wpdb->get_results("SELECT * FROM $templates_table ORDER BY created_at DESC");

    // Get all pricing items for the dropdown
    $pricing_items = $wpdb->get_results("SELECT * FROM $pricing_table WHERE is_active = 1 ORDER BY category, name");
    ?>
    <div class="wrap">
        <h1><i class="material-icons">construction</i> Construction Templates</h1>
        <p>Create preset construction packages like "Basic House", "Luxury Villa", etc. Users can select these templates to auto-populate the calculator.</p>

        <?php echo $message; ?>

        <div style="display: grid; grid-template-columns: 400px 1fr; gap: 30px; margin-top: 30px;">

            <!-- Left: Template Form -->
            <div>
                <div style="background: white; padding: 20px; border: 1px solid #ccc; border-radius: 8px;">
                    <h2><?php echo $editing_template ? 'Edit Template' : 'Create New Template'; ?></h2>
                    <form method="post">
                        <?php wp_nonce_field('template_action', 'template_nonce'); ?>
                        <input type="hidden" name="action" value="save_template">
                        <?php if ($editing_template): ?>
                            <input type="hidden" name="template_id" value="<?php echo $editing_template->id; ?>">
                        <?php endif; ?>

                        <table class="form-table">
                            <tr>
                                <th><label>Template Name</label></th>
                                <td><input type="text" name="template_name" class="regular-text" value="<?php echo $editing_template ? esc_attr($editing_template->name) : ''; ?>" required placeholder="e.g., Basic House"></td>
                            </tr>
                            <tr>
                                <th><label>Description</label></th>
                                <td><textarea name="template_description" class="large-text" rows="3" placeholder="Brief description of this package"><?php echo $editing_template ? esc_textarea($editing_template->description) : ''; ?></textarea></td>
                            </tr>
                            <tr>
                                <th><label>Icon</label></th>
                                <td>
                                    <input type="text" name="template_icon" value="<?php echo $editing_template ? esc_attr($editing_template->icon) : '<i class="material-icons">construction</i>'; ?>" style="width: 60px; text-align: center; font-size: 20px;" required>
                                    <p class="description">Use emoji like <i class="material-icons">construction</i> <i class="material-icons">calculate</i> ◉ <i class="material-icons">construction</i> ●</p>
                                </td>
                            </tr>
                            <tr>
                                <th><label>Status</label></th>
                                <td>
                                    <label>
                                        <input type="checkbox" name="is_active" value="1" <?php echo (!$editing_template || $editing_template->is_active) ? 'checked' : ''; ?>>
                                        Active (visible to users)
                                    </label>
                                </td>
                            </tr>
                        </table>

                        <p class="submit">
                            <button type="submit" class="button button-primary"><?php echo $editing_template ? 'Update Template' : 'Create Template'; ?></button>
                            <?php if ($editing_template): ?>
                                <a href="<?php echo admin_url('admin.php?page=tls-construction-templates'); ?>" class="button">Cancel</a>
                            <?php endif; ?>
                        </p>
                    </form>
                </div>

                <?php if ($editing_template): ?>
                <!-- Add Items to Template -->
                <div style="background: white; padding: 20px; border: 1px solid #ccc; border-radius: 8px; margin-top: 20px;">
                    <h3>Add Items to "<?php echo esc_html($editing_template->name); ?>"</h3>
                    <form method="post">
                        <?php wp_nonce_field('template_action', 'template_nonce'); ?>
                        <input type="hidden" name="action" value="add_item">
                        <input type="hidden" name="template_id" value="<?php echo $editing_template->id; ?>">

                        <table class="form-table">
                            <tr>
                                <th><label>Select Item</label></th>
                                <td>
                                    <select name="pricing_id" class="regular-text" required>
                                        <option value="">-- Select Item --</option>
                                        <?php
                                        $current_category = '';
                                        foreach ($pricing_items as $item):
                                            if ($current_category !== $item->category) {
                                                if ($current_category !== '') echo '</optgroup>';
                                                $current_category = $item->category;
                                                echo '<optgroup label="' . esc_attr($item->category_icon . ' ' . $item->category) . '">';
                                            }
                                            echo '<option value="' . $item->id . '">' . esc_html($item->name) . ' (RM' . number_format($item->unit_price, 2) . '/' . $item->unit . ')</option>';
                                        endforeach;
                                        if ($current_category !== '') echo '</optgroup>';
                                        ?>
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <th><label>Default Quantity</label></th>
                                <td><input type="number" name="default_quantity" step="0.01" min="0.01" value="1.00" required style="width: 100px;"></td>
                            </tr>
                            <tr>
                                <th><label>Notes</label></th>
                                <td><textarea name="item_notes" class="regular-text" rows="2" placeholder="Optional notes about this item"></textarea></td>
                            </tr>
                        </table>

                        <p class="submit">
                            <button type="submit" class="button button-secondary">Add Item to Template</button>
                        </p>
                    </form>
                </div>
                <?php endif; ?>
            </div>

            <!-- Right: Templates List -->
            <div>
                <div style="background: white; padding: 20px; border: 1px solid #ccc; border-radius: 8px;">
                    <h2>All Templates (<?php echo count($templates); ?>)</h2>
                    <?php if (empty($templates)): ?>
                        <p style="color: #666; font-style: italic;">No templates created yet. Create your first template!</p>
                    <?php else: ?>
                        <?php foreach ($templates as $template): ?>
                            <?php
                            // Get items count for this template
                            $items_count = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM $template_items_table WHERE template_id = %d", $template->id));
                            $template_items = $wpdb->get_results($wpdb->prepare("
                                SELECT ti.*, p.name, p.unit, p.unit_price, p.category
                                FROM $template_items_table ti
                                JOIN $pricing_table p ON ti.pricing_id = p.id
                                WHERE ti.template_id = %d
                                ORDER BY p.category, p.name
                            ", $template->id));
                            ?>
                            <div style="border: 2px solid <?php echo $template->is_active ? '#2c5f2d' : '#ccc'; ?>; border-radius: 8px; padding: 15px; margin-bottom: 15px; background: <?php echo $template->is_active ? '#f0f8f0' : '#f9f9f9'; ?>;">
                                <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 10px;">
                                    <div>
                                        <h3 style="margin: 0; font-size: 18px;">
                                            <?php echo esc_html($template->icon . ' ' . $template->name); ?>
                                            <?php if (!$template->is_active): ?>
                                                <span style="color: #999; font-size: 12px; font-weight: normal;">(Inactive)</span>
                                            <?php endif; ?>
                                        </h3>
                                        <?php if ($template->description): ?>
                                            <p style="margin: 5px 0 0; color: #666; font-size: 13px;"><?php echo esc_html($template->description); ?></p>
                                        <?php endif; ?>
                                    </div>
                                    <div style="display: flex; gap: 8px;">
                                        <a href="<?php echo admin_url('admin.php?page=tls-construction-templates&edit=' . $template->id); ?>" class="button button-small">Edit</a>
                                        <form method="post" style="display: inline;" onsubmit="return confirm('Delete this template and all its items?');">
                                            <?php wp_nonce_field('template_action', 'template_nonce'); ?>
                                            <input type="hidden" name="action" value="delete_template">
                                            <input type="hidden" name="template_id" value="<?php echo $template->id; ?>">
                                            <button type="submit" class="button button-small" style="color: red;">Delete</button>
                                        </form>
                                    </div>
                                </div>

                                <div style="background: white; padding: 10px; border-radius: 5px; margin-top: 10px;">
                                    <strong style="font-size: 12px; color: #666;">📦 Items in this template: <?php echo $items_count; ?></strong>
                                    <?php if (!empty($template_items)): ?>
                                        <table style="width: 100%; margin-top: 8px; font-size: 12px; border-collapse: collapse;">
                                            <thead>
                                                <tr style="background: #f5f5f5;">
                                                    <th style="text-align: left; padding: 5px; border: 1px solid #ddd;">Item</th>
                                                    <th style="text-align: center; padding: 5px; border: 1px solid #ddd;">Qty</th>
                                                    <th style="text-align: right; padding: 5px; border: 1px solid #ddd;">Unit Price</th>
                                                    <th style="text-align: right; padding: 5px; border: 1px solid #ddd;">Subtotal</th>
                                                    <th style="text-align: center; padding: 5px; border: 1px solid #ddd;"></th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php
                                                $total_cost = 0;
                                                foreach ($template_items as $item):
                                                    $subtotal = $item->default_quantity * $item->unit_price;
                                                    $total_cost += $subtotal;
                                                ?>
                                                <tr>
                                                    <td style="padding: 5px; border: 1px solid #ddd;"><?php echo esc_html($item->name); ?></td>
                                                    <td style="text-align: center; padding: 5px; border: 1px solid #ddd;"><?php echo number_format($item->default_quantity, 2); ?> <?php echo esc_html($item->unit); ?></td>
                                                    <td style="text-align: right; padding: 5px; border: 1px solid #ddd;">RM<?php echo number_format($item->unit_price, 2); ?></td>
                                                    <td style="text-align: right; padding: 5px; border: 1px solid #ddd; font-weight: bold;">RM<?php echo number_format($subtotal, 2); ?></td>
                                                    <td style="text-align: center; padding: 5px; border: 1px solid #ddd;">
                                                        <form method="post" style="display: inline;" onsubmit="return confirm('Remove this item?');">
                                                            <?php wp_nonce_field('template_action', 'template_nonce'); ?>
                                                            <input type="hidden" name="action" value="delete_item">
                                                            <input type="hidden" name="item_id" value="<?php echo $item->id; ?>">
                                                            <button type="submit" class="button button-small" style="color: red; padding: 2px 6px; font-size: 11px;">×</button>
                                                        </form>
                                                    </td>
                                                </tr>
                                                <?php endforeach; ?>
                                                <tr style="background: #e8f5e9; font-weight: bold;">
                                                    <td colspan="3" style="text-align: right; padding: 8px; border: 1px solid #ddd;">Total Estimated Cost:</td>
                                                    <td style="text-align: right; padding: 8px; border: 1px solid #ddd; color: #2c5f2d; font-size: 14px;">RM<?php echo number_format($total_cost, 2); ?></td>
                                                    <td style="border: 1px solid #ddd;"></td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    <?php else: ?>
                                        <p style="margin: 5px 0 0; color: #999; font-style: italic; font-size: 12px;">No items added yet. Click "Edit" to add items.</p>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    <?php
}

// ============================================
// DASHBOARD WIDGET
// ============================================
add_action('wp_dashboard_setup', function() {
    wp_add_dashboard_widget('tls_ldc_dashboard_widget', '<i class="material-icons">construction</i> TLS Calculator - Quick Stats', 'tls_ldc_dashboard_widget');
});

function tls_ldc_dashboard_widget() {
    $stats = TLS_LDC_Database::get_estimate_stats();
    ?>
    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
        <div style="text-align: center; padding: 15px; background: #f0f0f0; border-radius: 8px;">
            <h3 style="margin: 0; font-size: 24px; color: #2c5f2d;"><?php echo $stats['total']; ?></h3>
            <p style="margin: 5px 0 0; font-size: 12px;">Total Leads</p>
        </div>
        <div style="text-align: center; padding: 15px; background: #f0f0f0; border-radius: 8px;">
            <h3 style="margin: 0; font-size: 24px; color: #2c5f2d;"><?php echo $stats['today']; ?></h3>
            <p style="margin: 5px 0 0; font-size: 12px;">Today</p>
        </div>
    </div>
    <p style="margin-top: 15px; font-size: 12px;">
        <a href="<?php echo admin_url('admin.php?page=tls-calculator'); ?>">View all leads →</a>
    </p>
    <?php
}

// ============================================
// STICKY CALCULATOR BUTTON
// ============================================
add_action('wp_footer', function() {
    if (is_page('calculator')) return;
    if (!get_theme_mod('show_sticky_calculator', true)) return;
    ?>
    <div id="ldc-sticky-launcher" class="ldc-sticky-btn" onclick="window.location.href='<?php echo esc_url(home_url('/calculator')); ?>'">
        Kalkulator
    </div>
    <?php
});

// ============================================
// OUTPUT MAP DATA FOR FRONTPAGE
// ============================================
require_once get_template_directory() . '/inc/inc-map-data.php';

// ============================================
// AJAX: GET TEMPLATE ITEMS
// ============================================
add_action('wp_ajax_get_template_items', 'tls_get_template_items');
add_action('wp_ajax_nopriv_get_template_items', 'tls_get_template_items');

function tls_get_template_items() {
    global $wpdb;
    $template_id = intval($_POST['template_id']);

    if (!$template_id) {
        wp_send_json_error('Invalid template ID');
    }

    $template_items_table = $wpdb->prefix . TLS_LDC_PREFIX . 'template_items';
    $pricing_table = $wpdb->prefix . TLS_LDC_PREFIX . 'pricing';

    $items = $wpdb->get_results($wpdb->prepare("
        SELECT
            ti.*,
            p.id as pricing_id,
            p.name,
            p.unit,
            p.unit_price,
            p.category,
            p.category_icon
        FROM $template_items_table ti
        JOIN $pricing_table p ON ti.pricing_id = p.id
        WHERE ti.template_id = %d
        ORDER BY p.category, p.name
    ", $template_id));

    wp_send_json_success($items);
}

// ============================================
// AJAX: DOCUMENT REQUEST EMAIL NOTIFICATION
// ============================================
add_action('wp_ajax_request_document', 'tls_request_document');
add_action('wp_ajax_nopriv_request_document', 'tls_request_document');

function tls_request_document() {
    $post_id = intval($_POST['post_id']);
    $doc_type = sanitize_text_field($_POST['doc_type']);

    if (!$post_id || !$doc_type) {
        wp_send_json_error('Invalid request');
    }

    // Get property details
    $property = get_post($post_id);
    $property_title = $property->post_title;
    $property_url = get_permalink($post_id);
    $property_price = get_post_meta($post_id, '_tanah_price', true);
    $property_ekar = get_post_meta($post_id, '_tanah_ekar', true);

    // Document type mapping
    $doc_names = [
        'geran' => 'Geran (Title)',
        'pelan' => 'Pelan Tapak (Site Plan)',
        'search' => 'Official Search'
    ];
    $doc_name = isset($doc_names[$doc_type]) ? $doc_names[$doc_type] : $doc_type;

    // Get admin email
    $admin_email = get_theme_mod('email_address', get_option('admin_email'));
    $company_name = get_theme_mod('company_name', 'TanahLotSabah');

    // Email subject
    $subject = '<i class="material-icons">description</i> Document Request: ' . $doc_name . ' - ' . $property_title;

    // Email body
    $message = '<html><body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333;">';
    $message .= '<div style="background: #f8f9fa; padding: 20px; border-radius: 8px;">';
    $message .= '<h2 style="color: #2c5f2d; margin-top: 0;">New Document Request</h2>';
    $message .= '<p>A visitor has requested access to a document for one of your properties.</p>';

    $message .= '<div style="background: white; padding: 15px; border-radius: 5px; margin: 15px 0; border-left: 4px solid #2c5f2d;">';
    $message .= '<h3 style="margin-top: 0; color: #2c5f2d;">Property Details:</h3>';
    $message .= '<p><strong>Property:</strong> ' . esc_html($property_title) . '</p>';
    $message .= '<p><strong>Price:</strong> RM ' . number_format($property_price) . '</p>';
    $message .= '<p><strong>Size:</strong> ' . esc_html($property_ekar) . ' ekar</p>';
    $message .= '<p><strong>URL:</strong> <a href="' . esc_url($property_url) . '">' . esc_url($property_url) . '</a></p>';
    $message .= '</div>';

    $message .= '<div style="background: #fff3cd; padding: 15px; border-radius: 5px; margin: 15px 0; border-left: 4px solid #ffc107;">';
    $message .= '<h3 style="margin-top: 0; color: #856404;">Requested Document:</h3>';
    $message .= '<p style="font-size: 18px; font-weight: bold; color: #856404;">' . esc_html($doc_name) . '</p>';
    $message .= '</div>';

    $message .= '<div style="background: white; padding: 15px; border-radius: 5px; margin: 15px 0;">';
    $message .= '<h3 style="margin-top: 0; color: #2c5f2d;">Next Steps:</h3>';
    $message .= '<ul>';
    $message .= '<li>Upload the requested document in the property editor</li>';
    $message .= '<li>Or contact the visitor via WhatsApp to provide the document</li>';
    $message .= '<li>The visitor expects a response soon</li>';
    $message .= '</ul>';
    $message .= '<p><a href="' . admin_url('post.php?post=' . $post_id . '&action=edit') . '" style="display: inline-block; background: #2c5f2d; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; margin-top: 10px;">Edit Property</a></p>';
    $message .= '</div>';

    $message .= '<p style="color: #666; font-size: 12px; margin-top: 20px;">This is an automated notification from ' . esc_html($company_name) . ' website.</p>';
    $message .= '</div>';
    $message .= '</body></html>';

    // Email headers
    $headers = ['Content-Type: text/html; charset=UTF-8'];

    // Send email
    $sent = wp_mail($admin_email, $subject, $message, $headers);

    if ($sent) {
        // Log the request
        add_post_meta($post_id, '_doc_request_' . $doc_type, current_time('mysql'), false);

        wp_send_json_success('Email sent successfully');
    } else {
        wp_send_json_error('Failed to send email');
    }
}

// ============================================
// EXPOSE TANAH CUSTOM FIELDS IN REST API
// ============================================
add_action('rest_api_init', function() {
    register_rest_field('tanah', 'sles_data', array(
        'get_callback' => function($post) {
            return array(
                'price' => get_post_meta($post->ID, '_tanah_harga', true),
                'size' => get_post_meta($post->ID, '_tanah_keluasan', true),
                'id_code' => get_post_meta($post->ID, '_tanah_property_id', true),
                'land_type' => get_post_meta($post->ID, '_tanah_jenis_geran', true) ?: 'cl',
                'modified' => $post->post_modified,
                'latitude' => get_post_meta($post->ID, '_tanah_latitude', true),
                'longitude' => get_post_meta($post->ID, '_tanah_longitude', true),
            );
        },
        'update_callback' => null,
        'schema' => null,
    ));

    // Register class_list field for Flutter app filtering
    register_rest_field('tanah', 'class_list', array(
        'get_callback' => function($post) {
            $classes = [];
            $geran_terms = get_the_terms($post->ID, 'jenis_geran');
            if (!empty($geran_terms)) {
                foreach ($geran_terms as $term) {
                    if (in_array($term->slug, ['nt', 'native-title', 'native'])) {
                        $classes[] = 'native-title';
                    } elseif (in_array($term->slug, ['cl', 'country-lease'])) {
                        $classes[] = 'country-lease';
                    }
                }
            }
            return $classes;
        },
        'update_callback' => null,
        'schema' => null,
    ));
});

// ============================================
// TLS REST API FOR FLUTTER APP
// ============================================

add_action('rest_api_init', function() {
    // Register lands/nt endpoint
    register_rest_route('sles/v1', '/lands/nt', array(
        'methods' => 'GET',
        'callback' => 'tls_api_get_nt_lands',
        'permission_callback' => '__return_true',
    ));

    // Register lands/cl endpoint
    register_rest_route('sles/v1', '/lands/cl', array(
        'methods' => 'GET',
        'callback' => 'tls_api_get_cl_lands',
        'permission_callback' => '__return_true',
    ));

    // Register areas endpoint
    register_rest_route('sles/v1', '/areas', array(
        'methods' => 'GET',
        'callback' => 'tls_api_get_areas',
        'permission_callback' => '__return_true',
    ));
});

// Get Native Title (NT) lands
function tls_api_get_nt_lands() {
    $posts = get_posts(array(
        'post_type' => 'tanah',
        'post_status' => 'publish',
        'posts_per_page' => 50,
        'tax_query' => array(
            array(
                'taxonomy' => 'jenis_geran',
                'field' => 'slug',
                'terms' => array('nt', 'native-title', 'native')
            )
        )
    ));

    $lands = array();
    foreach ($posts as $post) {
        $lands[] = tls_format_tanah_for_api($post);
    }
    return $lands;
}

// Get Country Lease (CL) lands
function tls_api_get_cl_lands() {
    $posts = get_posts(array(
        'post_type' => 'tanah',
        'post_status' => 'publish',
        'posts_per_page' => 50,
        'tax_query' => array(
            array(
                'taxonomy' => 'jenis_geran',
                'field' => 'slug',
                'terms' => array('cl', 'country-lease')
            )
        )
    ));

    $lands = array();
    foreach ($posts as $post) {
        $lands[] = tls_format_tanah_for_api($post);
    }
    return $lands;
}

// Get areas/districts
function tls_api_get_areas() {
    $terms = get_terms(array(
        'taxonomy' => 'daerah',
        'hide_empty' => false,
    ));

    $areas = array();
    foreach ($terms as $term) {
        $areas[] = array(
            'id' => (string)$term->term_id,
            'name' => $term->name,
            'slug' => $term->slug,
        );
    }
    return $areas;
}

// Format tanah post for API
function tls_format_tanah_for_api($post) {
    $price = get_post_meta($post->ID, '_tanah_harga', true);
    $size = get_post_meta($post->ID, '_tanah_keluasan', true);
    $id_code = get_post_meta($post->ID, '_tanah_property_id', true);
    $lat = get_post_meta($post->ID, '_tanah_latitude', true);
    $lng = get_post_meta($post->ID, '_tanah_longitude', true);

    $geran_terms = get_the_terms($post->ID, 'jenis_geran');
    $geran = !empty($geran_terms) ? reset($geran_terms)->slug : 'cl';
    $land_type = (in_array($geran, array('nt', 'native-title', 'native'))) ? 'native' : 'cl';

    $daerah_terms = get_the_terms($post->ID, 'daerah');
    $location = !empty($daerah_terms) ? reset($daerah_terms)->name : 'Sabah';

    $featured_image = get_the_post_thumbnail_url($post->ID, 'large');
    $gallery = $featured_image ? array($featured_image) : array();

    return array(
        'id' => (string)$post->ID,
        'title' => array('rendered' => $post->post_title),
        'content' => array('rendered' => $post->post_content),
        'sles_data' => array(
            'price' => $price ? floatval($price) : 0.0,
            'size' => $size ? $size : '',
            'id_code' => $id_code ? $id_code : '',
            'land_type' => $land_type,
            'modified' => $post->post_modified,
            'latitude' => $lat ? $lat : null,
            'longitude' => $lng ? $lng : null,
        ),
        'district' => $location,
        'featured_media_url' => $gallery,
    );
}

