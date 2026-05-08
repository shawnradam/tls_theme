<?php
/**
 * Unified TLS Map System
 * Merged from TLS Map Plugin
 */

if (!defined('ABSPATH')) exit;

class TLS_Unified_Map
{
    public function __construct()
    {
        // Shortcodes
        add_shortcode('tlsmap', [$this, 'render_shortcode']);
        add_shortcode('tls_map', [$this, 'render_shortcode']);

        // AJAX Handlers
        add_action('wp_ajax_tls_save_lot', [$this, 'ajax_save_lot']);
        add_action('wp_ajax_tls_update_lot', [$this, 'ajax_update_lot']);
        add_action('wp_ajax_tls_get_lots', [$this, 'ajax_get_lots']);
        add_action('wp_ajax_nopriv_tls_get_lots', [$this, 'ajax_get_lots']);
        add_action('wp_ajax_tls_update_lot_meta', [$this, 'ajax_update_lot_meta']);
        add_action('wp_ajax_tls_log_debug', [$this, 'ajax_log_debug']);
        add_action('wp_ajax_nopriv_tls_log_debug', [$this, 'ajax_log_debug']);

        // Hooks
        add_action('save_post_tanah', [$this, 'save_tanah_boundary']);
    }

    public function render_shortcode($atts)
    {
        $atts = shortcode_atts([
            'lat' => get_option('tlsmap_default_lat', '6.1300'),
            'lng' => get_option('tlsmap_default_lng', '116.2300'),
            'zoom' => get_option('tlsmap_default_zoom', '12'),
            'height' => '500px',
            'width' => '100%',
            'post_id' => '',
        ], $atts, 'tlsmap');

        $unique_id = 'tlsmap_' . uniqid();
        
        return sprintf(
            '<div class="tlsmap-container" id="%s" data-lat="%s" data-lng="%s" data-zoom="%s" data-post-id="%s" style="height: %s; width: %s;"></div>',
            esc_attr($unique_id),
            esc_attr($atts['lat']),
            esc_attr($atts['lng']),
            esc_attr($atts['zoom']),
            esc_attr($atts['post_id']),
            esc_attr($atts['height']),
            esc_attr($atts['width'])
        );
    }

    public function ajax_update_lot_meta()
    {
        if (!current_user_can('edit_posts')) wp_send_json_error('Unauthorized');
        $post_id = intval($_POST['post_id']);
        if (!$post_id) wp_send_json_error('Invalid Post ID');
        update_post_meta($post_id, '_tanah_boundary', $_POST['boundary']);
        update_post_meta($post_id, '_tanah_keluasan', sanitize_text_field($_POST['area']));
        update_post_meta($post_id, '_tanah_latitude', sanitize_text_field($_POST['lat']));
        update_post_meta($post_id, '_tanah_longitude', sanitize_text_field($_POST['lng']));
        wp_send_json_success();
    }

    public function save_tanah_boundary($post_id)
    {
        if (isset($_POST['tls_tanah_boundary'])) {
            update_post_meta($post_id, '_tanah_boundary', $_POST['tls_tanah_boundary']);
        }
    }

    public function ajax_save_lot()
    {
        if (!current_user_can('manage_options')) wp_send_json_error('Unauthorized');
        global $wpdb;
        $table_name = $wpdb->prefix . 'tls_land_lots';
        $result = $wpdb->insert($table_name, [
            'time' => current_time('mysql'),
            'lot_name' => sanitize_text_field($_POST['name']),
            'coordinates' => stripslashes($_POST['coordinates']),
            'area_size' => sanitize_text_field($_POST['area']),
            'lot_status' => sanitize_text_field($_POST['status']),
            'lot_image' => esc_url_raw($_POST['image']),
            'lot_price' => sanitize_text_field($_POST['price']),
            'lot_zoning' => sanitize_text_field($_POST['zoning']),
            'lot_grant' => sanitize_text_field($_POST['grant']),
            'lot_desc' => sanitize_textarea_field($_POST['desc']),
            'agent_name' => sanitize_text_field($_POST['agent'])
        ]);
        if ($result === false) {
            wp_send_json_error(['message' => $wpdb->last_error]);
        } else {
            wp_send_json_success(['id' => $wpdb->insert_id]);
        }
    }

    public function ajax_update_lot()
    {
        if (!current_user_can('manage_options')) wp_send_json_error('Unauthorized');
        global $wpdb;
        $table_name = $wpdb->prefix . 'tls_land_lots';
        $id = intval($_POST['id']);
        $wpdb->update($table_name, [
            'lot_name' => sanitize_text_field($_POST['name']),
            'lot_status' => sanitize_text_field($_POST['status']),
            'lot_image' => esc_url_raw($_POST['image']),
            'lot_price' => sanitize_text_field($_POST['price']),
            'lot_zoning' => sanitize_text_field($_POST['zoning']),
            'lot_grant' => sanitize_text_field($_POST['grant']),
            'lot_desc' => sanitize_textarea_field($_POST['desc']),
            'agent_name' => sanitize_text_field($_POST['agent']),
            'coordinates' => stripslashes($_POST['coordinates'])
        ], ['id' => $id]);
        wp_send_json_success();
    }

    public function ajax_get_lots()
    {
        global $wpdb;
        $table_name = $wpdb->prefix . 'tls_land_lots';
        $manual_lots = [];
        if($wpdb->get_var("SHOW TABLES LIKE '$table_name'") == $table_name) {
            $manual_lots = $wpdb->get_results("SELECT * FROM $table_name");
        }
        
        $args = ['post_type' => 'tanah', 'posts_per_page' => -1, 'post_status' => 'publish'];
        $properties = [];
        $query = new WP_Query($args);
        if ($query->have_posts()) {
            while ($query->have_posts()) {
                $query->the_post();
                $id = get_the_ID();
                $lat = get_post_meta($id, '_tanah_latitude', true);
                $lng = get_post_meta($id, '_tanah_longitude', true);
                $boundary = get_post_meta($id, '_tanah_boundary', true);
                
                // Include ALL properties, even those without coordinates
                $properties[] = [
                    'id' => $id,
                    'name' => get_the_title(),
                    'lat' => $lat, 'lng' => $lng, 'boundary' => $boundary,
                    'area' => get_post_meta($id, '_tanah_keluasan', true),
                    'price' => get_post_meta($id, '_tanah_harga', true),
                    'zoning' => get_post_meta($id, '_tanah_zoning', true),
                    'grant' => get_post_meta($id, '_tanah_jenis_geran', true),
                        'image' => get_the_post_thumbnail_url($id, 'full'),
                    'link' => (get_permalink($id) ? get_permalink($id) : ''),
                    'status' => strtolower(get_post_meta($id, '_tanah_status', true) ?: 'available')
                ];
            }
            wp_reset_postdata();
        }
        wp_send_json_success(['manual' => $manual_lots, 'properties' => $properties]);
    }

    public function ajax_log_debug()
    {
        $log_file = TLS_THEME_DIR . '/tls-theme-error.log';
        $data = isset($_POST['data']) ? $_POST['data'] : '';
        $parsed = json_decode($data, true);
        if (is_array($parsed)) {
            $timestamp = date('Y-m-d H:i:s');
            $line = "[{$timestamp}] JS DEBUG:\n";
            foreach ($parsed as $msg) {
                $line .= "  - " . $msg . "\n";
            }
            $line .= "[{$timestamp}] JS DEBUG END\n";
            @file_put_contents($log_file, $line, FILE_APPEND | LOCK_EX);
        }
        wp_send_json_success();
    }
}
new TLS_Unified_Map();
