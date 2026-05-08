<?php
/**
 * TanahLotSabah Theme - Map Data Output
 * 
 * Extracts map data for the front page map system.
 * This file is part of the modular theme structure.
 */

if (!defined('ABSPATH')) exit;

// ============================================
// OUTPUT MAP DATA FOR FRONTPAGE
// ============================================
add_action('wp_footer', function() {
    $log_file = TLS_THEME_DIR . '/tls-theme-error.log';
    $log = function($msg) use ($log_file) {
        $timestamp = date('Y-m-d H:i:s');
        $line = "[{$timestamp}] " . $msg . "\n";
        @file_put_contents($log_file, $line, FILE_APPEND | LOCK_EX);
    };

    $log('=== TLS_LOTS Generation Start ===');
    $log('is_front_page(): ' . (is_front_page() ? 'true' : 'false'));

    if (!is_front_page()) {
        $log('Not front page, skipping');
        return;
    }

    $log('TLS_THEME_DIR: ' . TLS_THEME_DIR);

    $args = [
        'post_type' => 'tanah',
        'posts_per_page' => -1,
        'post_status' => 'publish'
    ];

    $tanah_posts = get_posts($args);
    $lots = [];

    $log('Total tanah posts found: ' . count($tanah_posts));

    if (count($tanah_posts) === 0) {
        $log('WARNING: No tanah posts found! Check if CPT is registered.');
        
        // Debug: check if tanah CPT exists
        global $wp_post_types;
        $log('Registered post types: ' . (isset($wp_post_types['tanah']) ? 'tanah EXISTS' : 'tanah NOT registered'));
        
        // Try without status filter
        $args2 = ['post_type' => 'tanah', 'posts_per_page' => -1, 'post_status' => 'any'];
        $all_tanah = get_posts($args2);
        $log('All tanah posts (any status): ' . count($all_tanah));
        foreach ($all_tanah as $tp) {
            $log('  - "' . $tp->post_title . '" (ID:' . $tp->ID . ') status: ' . $tp->post_status);
        }
    }

    foreach ($tanah_posts as $post) {
        $log('Processing post ID:' . $post->ID . ' title: "' . $post->post_title . '"');

        $price = get_post_meta($post->ID, '_tanah_harga', true);
        if (empty($price)) $price = get_post_meta($post->ID, '_tanah_price', true);

        $ekar = get_post_meta($post->ID, '_tanah_keluasan', true);
        if (empty($ekar)) $ekar = get_post_meta($post->ID, '_tanah_ekar', true);

        $geran = get_post_meta($post->ID, '_tanah_jenis_geran', true);
        if (empty($geran)) $geran = get_post_meta($post->ID, '_tanah_geran', true);

        $lat = get_post_meta($post->ID, '_tanah_latitude', true);
        $lng = get_post_meta($post->ID, '_tanah_longitude', true);
        $boundary = get_post_meta($post->ID, '_tanah_boundary', true);

        $log('  price: ' . $price . ', ekar: ' . $ekar . ', geran: ' . $geran);
        $log('  lat: "' . $lat . '", lng: "' . $lng . '"');
        $log('  boundary: ' . ($boundary ? substr($boundary, 0, 100) : 'none'));

        // Parse boundary safely - check if it's GeoJSON or simple array
        $boundary_array = [];
        if ($boundary && is_string($boundary)) {
            $decoded = json_decode($boundary, true);
            if (is_array($decoded)) {
                // Check if it's GeoJSON format or simple coordinate array
                if (isset($decoded['type'])) {
                    // GeoJSON format - extract coordinates
                    if ($decoded['type'] === 'Polygon' && isset($decoded['coordinates'])) {
                        $boundary_array = $decoded['coordinates'][0];
                        $log('  boundary is GeoJSON Polygon');
                    } elseif ($decoded['type'] === 'Point' && isset($decoded['coordinates'])) {
                        $boundary_array = [$decoded['coordinates']];
                        $log('  boundary is GeoJSON Point');
                    }
                } else {
                    // Simple array format
                    $boundary_array = $decoded;
                    $log('  boundary parsed as simple array, points: ' . count($decoded));
                }
            } else {
                $log('  boundary JSON decode failed');
            }
        }

        $thumbnail = get_the_post_thumbnail_url($post->ID, 'medium');
        if (!$thumbnail) {
            $thumbnail = get_template_directory_uri() . '/assets/images/placeholder.jpeg';
        }
        $log('  thumbnail: ' . ($thumbnail ? 'yes' : 'no'));

        // Build lot data with all fields tlsmap.js expects
        // Note: lat and lng are stored as swapped in hosting database, swap them back
        $lot_data = [
            'dbId' => $post->ID,
            'lat' => floatval($lng ?: 0),  // lat from DB actually contains lng value
            'lng' => floatval($lat ?: 0),  // lng from DB actually contains lat value
            'name' => $post->post_title,
            'title' => $post->post_title,
            'price' => intval($price ?: 0),
            'area' => $ekar ?: '0',
            'ekar' => $ekar ?: '0',
            'geran' => $geran ?: 'N/A',
            'grant' => $geran ?: 'N/A',
            'grant_no' => $geran ?: 'N/A',
            'status' => strtolower(get_post_meta($post->ID, '_tanah_status', true) ?: 'available'),
            'img' => $thumbnail,
            'image' => $thumbnail,
            'permalink' => get_permalink($post->ID),
            'link' => get_permalink($post->ID),
            'boundary' => $boundary_array
        ];

        // If no coords but has boundary, extract from first point
        // Your data stores as [[lat, lng], ...] format
        if ((empty($lat) || empty($lng) || floatval($lat) == 0) && !empty($boundary_array)) {
            $first_point = $boundary_array[0];
            if (is_array($first_point) && count($first_point) >= 2) {
                // Simple format: [lat, lng] - swap for GeoJSON which expects [lng, lat]
                $lot_data['lat'] = floatval($first_point[0]);
                $lot_data['lng'] = floatval($first_point[1]);
            }
            $log('  Using boundary fallback coords: lat=' . $lot_data['lat'] . ', lng=' . $lot_data['lng']);
        }

        // Mark no_coords but still include for sidebar
        if ((empty($lat) || empty($lng) || floatval($lat) == 0) && empty($boundary_array)) {
            $lot_data['no_coords'] = true;
            $log('  No coords, marked for sidebar only');
        }

        $lots[] = $lot_data;
    }

    $log('=== TLS_LOTS Generation End ===');
    ?>
    <script>
    console.log('=== TLS_LOTS PHP Debug ===');
    console.log('Posts found: <?php echo count($tanah_posts); ?>');
    console.log('Lots generated: <?php echo count($lots); ?>');
    </script>
    <?php if (count($tanah_posts) === 0): ?>
    <script>
    console.warn('WARNING: No tanah posts found! Check CPT registration.');
    console.warn('is_front_page: <?php echo is_front_page() ? 'true' : 'false'; ?>');
    </script>
    <?php else: ?>
    <script>
    var TLS_LOTS = <?php echo json_encode($lots, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>;
    console.log('TLS_LOTS created:', TLS_LOTS.length, 'properties');
    console.log('TLS_LOTS JSON sample:', JSON.stringify(TLS_LOTS[0] ?? 'empty').substring(0, 300));
    </script>
    <?php endif;
}, 5);
