<?php
/**
 * TLS Property Data Validator
 * Validates that all properties have required data (coordinates, boundary, images, links)
 * 
 * Usage: http://localhost/maps/wp-admin/admin.php?page=tls-property-validator
 */

if (!defined('ABSPATH')) {
    require_once('../../../wp-load.php');
}

// Check admin permissions
if (!current_user_can('manage_options')) {
    wp_die('Access denied');
}

// Handle validation request
$results = [];
$issues = [];

if (isset($_POST['run_validation'])) {
    $properties = get_posts([
        'post_type' => 'tanah',
        'post_status' => ['publish', 'draft'],
        'posts_per_page' => -1,
    ]);
    
    foreach ($properties as $prop) {
        $id = $prop->ID;
        $title = get_the_title($id);
        $link = get_permalink($id);
        $lat = get_post_meta($id, '_tanah_latitude', true);
        $lng = get_post_meta($id, '_tanah_longitude', true);
        $boundary = get_post_meta($id, '_tanah_boundary', true);
        $price = get_post_meta($id, '_tanah_harga', true);
        $area = get_post_meta($id, '_tanah_keluasan', true);
        $status = get_post_meta($id, '_tanah_status', true);
        $image = get_the_post_thumbnail_url($id, 'full');
        
        $property_issues = [];
        
        // Check required fields
        if (empty($lat) || empty($lng)) {
            $property_issues[] = 'Missing coordinates (lat/lng)';
        }
        if (empty($boundary)) {
            $property_issues[] = 'Missing boundary data';
        } else {
            // Validate GeoJSON
            $geo = json_decode($boundary, true);
            if (!$geo || !isset($geo['type']) || $geo['type'] !== 'Feature') {
                $property_issues[] = 'Invalid boundary GeoJSON';
            }
        }
        if (empty($price)) {
            $property_issues[] = 'Missing price';
        }
        if (empty($area)) {
            $property_issues[] = 'Missing area size';
        }
        if (empty($status)) {
            $property_issues[] = 'Missing status';
        }
        if ($link === false) {
            $property_issues[] = 'Invalid permalink (link is false)';
        }
        if (empty($image)) {
            $property_issues[] = 'Missing featured image';
        }
        
        $results[] = [
            'id' => $id,
            'title' => $title,
            'link' => $link,
            'lat' => $lat,
            'lng' => $lng,
            'has_boundary' => !empty($boundary),
            'issues' => $property_issues
        ];
        
        if (!empty($property_issues)) {
            $issues[] = [
                'id' => $id,
                'title' => $title,
                'issues' => $property_issues
            ];
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Property Data Validator - TLS Theme</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; padding: 20px; background: #f1f5f9; }
        .wrap { max-width: 1200px; margin: 0 auto; }
        .card { background: white; padding: 24px; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); margin-bottom: 20px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #e2e8f0; }
        th { background: #f8fafc; font-weight: 600; }
        .issue { color: #dc2626; font-size: 12px; }
        .good { color: #16a34a; }
        .button { padding: 12px 24px; background: #16a34a; color: white; border: none; border-radius: 6px; cursor: pointer; font-size: 14px; }
        .button:hover { background: #15803d; }
        .stats { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px; margin: 20px 0; }
        .stat-box { background: white; padding: 20px; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); }
        .stat-number { font-size: 32px; font-weight: bold; color: #16a34a; }
        .stat-label { font-size: 14px; color: #64748b; }
    </style>
</head>
<body>
    <div class="wrap">
        <h1>🔍 Property Data Validator</h1>
        <p>Validate that all properties have required data for map display and features.</p>
        
        <div class="card">
            <form method="post">
                <button type="submit" name="run_validation" class="button">Run Validation</button>
            </form>
            
            <?php if (!empty($results)): ?>
                <div class="stats">
                    <div class="stat-box">
                        <div class="stat-number"><?php echo count($results); ?></div>
                        <div class="stat-label">Total Properties</div>
                    </div>
                    <div class="stat-box">
                        <div class="stat-number" style="color: #dc2626;"><?php echo count($issues); ?></div>
                        <div class="stat-label">Properties with Issues</div>
                    </div>
                    <div class="stat-box">
                        <div class="stat-number" style="color: #16a34a;"><?php echo count($results) - count($issues); ?></div>
                        <div class="stat-label">Healthy Properties</div>
                    </div>
                </div>
                
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Title</th>
                            <th>Link</th>
                            <th>Coordinates</th>
                            <th>Boundary</th>
                            <th>Issues</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($results as $r): ?>
                            <tr>
                                <td><?php echo $r['id']; ?></td>
                                <td>
                                    <a href="<?php echo admin_url('post.php?post=' . $r['id'] . '&action=edit'); ?>">
                                        <?php echo esc_html($r['title']); ?>
                                    </a>
                                </td>
                                <td>
                                    <?php if ($r['link'] && $r['link'] !== false): ?>
                                        <a href="<?php echo esc_url($r['link']); ?>" target="_blank">View</a>
                                    <?php else: ?>
                                        <span class="issue">Invalid link</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($r['lat'] && $r['lng']): ?>
                                        <span class="good">✓</span> <?php echo $r['lat'] . ', ' . $r['lng']; ?>
                                    <?php else: ?>
                                        <span class="issue">Missing</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($r['has_boundary']): ?>
                                        <span class="good">✓</span>
                                    <?php else: ?>
                                        <span class="issue">Missing</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if (!empty($r['issues'])): ?>
                                        <ul style="margin: 0; padding-left: 20px;">
                                            <?php foreach ($r['issues'] as $issue): ?>
                                                <li class="issue"><?php echo esc_html($issue); ?></li>
                                            <?php endforeach; ?>
                                        </ul>
                                    <?php else: ?>
                                        <span class="good">✓ All good</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
