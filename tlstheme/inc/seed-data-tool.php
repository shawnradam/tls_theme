<?php
/**
 * Seed Data Admin Tool
 * Run from WP Admin > TLS System > Seed Data
 */

if (!defined('ABSPATH')) exit;

// Add admin menu
add_action('admin_menu', function() {
    add_submenu_page(
        'tls-dashboard',
        'Seed Data',
        'Seed Data',
        'manage_options',
        'tls-seed-data',
        'tls_seed_data_page'
    );
});

function tls_seed_data_page() {
    $message = '';
    $status = '';
    
    // Handle seed action
    if (isset($_POST['tls_seed_nonce']) && wp_verify_nonce($_POST['tls_seed_nonce'], 'tls_seed_action')) {
        if (isset($_POST['seed_news'])) {
            tls_run_news_seed();
            $message = 'News posts seeded successfully!';
            $status = 'success';
        }
        if (isset($_POST['seed_tanah'])) {
            tls_run_tanah_seed();
            $message = 'Tanah properties seeded successfully!';
            $status = 'success';
        }
        if (isset($_POST['seed_all'])) {
            tls_run_news_seed();
            tls_run_tanah_seed();
            $message = 'All data seeded successfully!';
            $status = 'success';
        }
        if (isset($_POST['reset_seeds'])) {
            delete_option('tls_news_seeded');
            delete_option('tls_tanah_seeded');
            $message = 'Seed flags reset. You can re-seed by clicking the buttons below.';
            $status = 'success';
        }
    }
    
    $news_count = wp_count_posts('post')->publish;
    $tanah_count = wp_count_posts('tanah')->publish;
    ?>
    <div class="wrap tls-seed-wrap">
        <h1>
            <span class="dashicons dashicons-database-import"></span>
            Seed Data Manager
        </h1>
        <p class="description">Create sample placeholder data for development. Safe to run multiple times - won't create duplicates.</p>
        
        <?php if ($message): ?>
            <div class="notice notice-<?php echo $status; ?> is-dismissible">
                <p><?php echo esc_html($message); ?></p>
            </div>
        <?php endif; ?>
        
        <style>
            .tls-seed-wrap { max-width: 800px; }
            .tls-seed-wrap h1 { display: flex; align-items: center; gap: 10px; }
            .tls-seed-card {
                background: #fff;
                border: 1px solid #ddd;
                border-radius: 8px;
                padding: 24px;
                margin: 20px 0;
            }
            .tls-seed-card h2 { margin-top: 0; }
            .tls-seed-stats {
                display: flex;
                gap: 30px;
                margin: 20px 0;
            }
            .tls-stat-box {
                background: #f8f9fa;
                padding: 16px 24px;
                border-radius: 8px;
                text-align: center;
            }
            .tls-stat-box .count {
                font-size: 2rem;
                font-weight: 700;
                color: var(--primary);
            }
            .tls-stat-box .label {
                font-size: 0.85rem;
                color: #666;
            }
            .tls-seed-actions {
                display: flex;
                flex-wrap: wrap;
                gap: 12px;
                margin-top: 20px;
            }
            .tls-seed-btn {
                padding: 12px 24px;
                border: none;
                border-radius: 6px;
                font-size: 14px;
                font-weight: 600;
                cursor: pointer;
                text-decoration: none;
                display: inline-flex;
                align-items: center;
                gap: 8px;
            }
            .tls-seed-btn-primary {
                background: #16a34a;
                color: #fff;
            }
            .tls-seed-btn-primary:hover { background: #15803d; }
            .tls-seed-btn-secondary {
                background: #0369a1;
                color: #fff;
            }
            .tls-seed-btn-secondary:hover { background: #0284c7; }
            .tls-seed-btn-warning {
                background: #d97706;
                color: #fff;
            }
            .tls-seed-btn-warning:hover { background: #b45309; }
        </style>
        
        <div class="tls-seed-card">
            <h2>Current Status</h2>
            <div class="tls-seed-stats">
                <div class="tls-stat-box">
                    <div class="count"><?php echo $news_count; ?></div>
                    <div class="label">News Posts</div>
                </div>
                <div class="tls-stat-box">
                    <div class="count"><?php echo $tanah_count; ?></div>
                    <div class="label">Tanah Properties</div>
                </div>
                <div class="tls-stat-box">
                    <div class="count"><?php echo get_option('tls_news_seeded') ? 'Yes' : 'No'; ?></div>
                    <div class="label">News Seeded</div>
                </div>
                <div class="tls-stat-box">
                    <div class="count"><?php echo get_option('tls_tanah_seeded') ? 'Yes' : 'No'; ?></div>
                    <div class="label">Tanah Seeded</div>
                </div>
            </div>
        </div>
        
        <div class="tls-seed-card">
            <h2>Seed Actions</h2>
            <p>Click to create sample data. Safe to run multiple times.</p>
            
            <form method="post">
                <?php wp_nonce_field('tls_seed_action', 'tls_seed_nonce'); ?>
                <div class="tls-seed-actions">
                    <button type="submit" name="seed_all" class="tls-seed-btn tls-seed-btn-primary">
                        <span class="dashicons dashicons-database-import"></span>
                        Seed All Data
                    </button>
                    <button type="submit" name="seed_news" class="tls-seed-btn tls-seed-btn-secondary">
                        <span class="dashicons dashicons-admin-post"></span>
                        Seed News Only
                    </button>
                    <button type="submit" name="seed_tanah" class="tls-seed-btn tls-seed-btn-secondary">
                        <span class="dashicons dashicons-location"></span>
                        Seed Properties Only
                    </button>
                    <button type="submit" name="reset_seeds" class="tls-seed-btn tls-seed-btn-warning">
                        <span class="dashicons dashicons-backup"></span>
                        Reset Seed Flags
                    </button>
                </div>
            </form>
        </div>
        
        <div class="tls-seed-card">
            <h2>News Posts Preview</h2>
            <ul>
                <li>2020: Native Customary Rights Guide</li>
                <li>2021: NT vs CL Land Distribution</li>
                <li>2022: PANTAS Programme Progress</li>
                <li>2022: Sabah Residential Market Rebounds</li>
                <li>2023: KKIP RM8.32B Investment</li>
                <li>2023: Sabah Property Market Update</li>
                <li>2024: 16,760 NT Titles Since 2020</li>
                <li>2024: Pan Borneo Highway Progress</li>
                <li>2024: NT vs CL Investment ROI</li>
                <li>2025: Sabah Property Uptrend</li>
            </ul>
            <p><em>Each post includes animated Chart.js statistics with real Sabah land data.</em></p>
        </div>
        
        <div class="tls-seed-card">
            <h2>Tanah Properties Preview</h2>
            <p>10 sample properties with varied development statuses:</p>
            <ul>
                <li>Jalan Sulaman, Shangri La (Completed, NT)</li>
                <li>Kg. Tambalugu (In Progress, CL)</li>
                <li>Telupid Development Zone (Planned, NT)</li>
                <li>Inanam Industrial Plot (Completed, CL)</li>
                <li>Penampang Town Centre (Completed, CL)</li>
                <li>Lahad Datu Palm Oil Zone (In Progress, NT)</li>
                <li>Putatan Mixed Development (Planned, CL)</li>
                <li>Sandakan Harbour View (Completed, CL)</li>
                <li>Tuaran Rice Bowl Area (Raw Land, NT)</li>
                <li>Kinarut Township Expansion (In Progress, CL)</li>
            </ul>
        </div>
    </div>
    <?php
}

// News seed function
function tls_run_news_seed() {
    if (get_option('tls_news_seeded')) return;
    
    $posts_data = [
        [
            'title' => 'Native Customary Rights: A Complete Guide for Land Investors in Sabah',
            'content' => file_get_contents(__DIR__ . '/news-templates/ncr-guide.php'),
            'excerpt' => 'Understand the 7 conditions for NCR claims under Sabah Land Ordinance Section 15.',
            'year' => 2020,
            'categories' => ['NT Land', 'Investor Guide']
        ],
        [
            'title' => 'Sabah Land Title Distribution 2021: NT vs CL Statistics',
            'content' => file_get_contents(__DIR__ . '/news-templates/nt-cl-distribution.php'),
            'excerpt' => 'Native Title (NT) and Country Lease (CL) ownership statistics reveal the balance of land rights.',
            'year' => 2021,
            'categories' => ['Land Titles', 'Statistics']
        ],
        [
            'title' => 'PANTAS Programme: Accelerating Native Land Ownership in Sabah',
            'content' => file_get_contents(__DIR__ . '/news-templates/pantas-program.php'),
            'excerpt' => 'The Sabah Native Land Services Programme (PANTAS) has issued 27,572 NT grants covering 42,429 hectares.',
            'year' => 2022,
            'categories' => ['NT Land', 'Government']
        ],
        [
            'title' => 'Sabah Residential Market Rebounds: RM2.78 Billion in Transactions',
            'content' => file_get_contents(__DIR__ . '/news-templates/residential-2022.php'),
            'excerpt' => 'The Sabah property market saw a strong rebound with 5,792 residential transactions worth RM2.78 billion.',
            'year' => 2022,
            'categories' => ['Market Update', 'Residential']
        ],
        [
            'title' => 'KKIP: RM8.32 Billion Investment Hub Transforms Sabah\'s Economy',
            'content' => file_get_contents(__DIR__ . '/news-templates/kkip-investment.php'),
            'excerpt' => 'Kota Kinabalu Industrial Park has attracted RM8.32 billion in investments and created 5,882 jobs.',
            'year' => 2023,
            'categories' => ['Industrial', 'Investment']
        ],
        [
            'title' => 'Sabah Property Market Update: RM2.37 Billion in Residential Sales 2023',
            'content' => file_get_contents(__DIR__ . '/news-templates/residential-2023.php'),
            'excerpt' => 'Despite a slight dip in volume, Sabah residential transactions value increased to RM2.37 billion.',
            'year' => 2023,
            'categories' => ['Market Update', 'Statistics']
        ],
        [
            'title' => '16,760 Native Titles Granted Since 2020: Sabah\'s Land Reform Progress',
            'content' => file_get_contents(__DIR__ . '/news-templates/nt-reform-2024.php'),
            'excerpt' => 'The Sabah government has granted 16,760 NT titles across 223 villages since 2020.',
            'year' => 2024,
            'categories' => ['NT Land', 'Government']
        ],
        [
            'title' => 'Pan Borneo Highway: Connecting Sabah\'s Future',
            'content' => file_get_contents(__DIR__ . '/news-templates/pan-borneo.php'),
            'excerpt' => 'Phase 1A of the Pan Borneo Highway Sabah is 77.91% complete, with 706km of new roads.',
            'year' => 2024,
            'categories' => ['Infrastructure', 'Development']
        ],
        [
            'title' => 'NT vs CL Land: Investment Profit Comparison 2024',
            'content' => file_get_contents(__DIR__ . '/news-templates/nt-cl-roi.php'),
            'excerpt' => 'Compare investment returns: CL offers 15-18% ROI with broader market access, NT provides 8-10%.',
            'year' => 2024,
            'categories' => ['Investor Guide', 'ROI']
        ],
        [
            'title' => 'Sabah\'s Property Uptrend: RM5.17 Trillion Transaction Value',
            'content' => file_get_contents(__DIR__ . '/news-templates/market-uptrend-2025.php'),
            'excerpt' => 'Sabah property market shows significant uptrend with RM5.17 trillion in MOT transaction value.',
            'year' => 2025,
            'categories' => ['Market Update', 'Statistics']
        ]
    ];

    foreach ($posts_data as $data) {
        $post_id = wp_insert_post([
            'post_title' => $data['title'],
            'post_content' => $data['content'],
            'post_excerpt' => $data['excerpt'],
            'post_status' => 'publish',
            'post_type' => 'post',
            'post_date' => $data['year'] . '-06-15 10:00:00',
        ]);

        if (!is_wp_error($post_id)) {
            foreach ($data['categories'] as $cat) {
                $term = get_term_by('name', $cat, 'category');
                if (!$term) {
                    $term = wp_insert_term($cat, 'category');
                }
                if (!is_wp_error($term)) {
                    wp_set_object_terms($post_id, is_array($term) ? $term['term_id'] : $term->term_id, 'category', true);
                }
            }
        }
    }

    update_option('tls_news_seeded', true);
}

// Tanah seed function
function tls_run_tanah_seed() {
    if (get_option('tls_tanah_seeded')) return;
    
    $properties = [
        ['title' => 'Jalan Sulaman, Shangri La', 'price' => 2500000, 'size' => '1.64', 'geran' => 'NT', 'status' => 'available', 'dev_status' => 'completed', 'lat' => 6.0279, 'lng' => 116.2474, 'town' => 'Kota Kinabalu'],
        ['title' => 'Kg. Tambalugu', 'price' => 1500000, 'size' => '0.5', 'geran' => 'CL', 'status' => 'available', 'dev_status' => 'in_progress', 'lat' => 6.1448, 'lng' => 116.2293, 'town' => 'Kota Kinabalu'],
        ['title' => 'Telupid Development Zone', 'price' => 890000, 'size' => '5.2', 'geran' => 'NT', 'status' => 'available', 'dev_status' => 'planned', 'lat' => 5.9567, 'lng' => 117.1328, 'town' => 'Telupid'],
        ['title' => 'Inanam Industrial Plot', 'price' => 3200000, 'size' => '3.8', 'geran' => 'CL', 'status' => 'available', 'dev_status' => 'completed', 'lat' => 5.9921, 'lng' => 116.1875, 'town' => 'Inanam'],
        ['title' => 'Penampang Town Centre', 'price' => 1800000, 'size' => '0.75', 'geran' => 'CL', 'status' => 'available', 'dev_status' => 'completed', 'lat' => 5.9356, 'lng' => 116.1033, 'town' => 'Penampang'],
        ['title' => 'Lahad Datu Palm Oil Zone', 'price' => 650000, 'size' => '10.5', 'geran' => 'NT', 'status' => 'available', 'dev_status' => 'in_progress', 'lat' => 5.0216, 'lng' => 118.3281, 'town' => 'Lahad Datu'],
        ['title' => 'Putatan Mixed Development', 'price' => 2100000, 'size' => '2.3', 'geran' => 'CL', 'status' => 'reserved', 'dev_status' => 'planned', 'lat' => 5.9203, 'lng' => 116.0583, 'town' => 'Putatan'],
        ['title' => 'Sandakan Harbour View', 'price' => 1350000, 'size' => '1.2', 'geran' => 'CL', 'status' => 'available', 'dev_status' => 'completed', 'lat' => 5.8386, 'lng' => 118.1167, 'town' => 'Sandakan'],
        ['title' => 'Tuaran Rice Bowl Area', 'price' => 480000, 'size' => '8.4', 'geran' => 'NT', 'status' => 'available', 'dev_status' => 'raw_land', 'lat' => 6.1778, 'lng' => 116.2311, 'town' => 'Tuaran'],
        ['title' => 'Kinarut Township Expansion', 'price' => 980000, 'size' => '1.8', 'geran' => 'CL', 'status' => 'available', 'dev_status' => 'in_progress', 'lat' => 5.8522, 'lng' => 116.0747, 'town' => 'Kinarut'],
    ];

    foreach ($properties as $prop) {
        $post_id = wp_insert_post([
            'post_title' => $prop['title'],
            'post_content' => 'Sample property - ' . $prop['title'],
            'post_status' => 'publish',
            'post_type' => 'tanah'
        ]);

        if (!is_wp_error($post_id)) {
            update_post_meta($post_id, '_tanah_harga', $prop['price']);
            update_post_meta($post_id, '_tanah_keluasan', $prop['size']);
            update_post_meta($post_id, '_tanah_jenis_geran', $prop['geran']);
            update_post_meta($post_id, '_tanah_status', $prop['status']);
            update_post_meta($post_id, '_tanah_development_status', $prop['dev_status']);
            update_post_meta($post_id, '_tanah_latitude', $prop['lat']);
            update_post_meta($post_id, '_tanah_longitude', $prop['lng']);
            update_post_meta($post_id, '_tanah_town', $prop['town']);
        }
    }

    update_option('tls_tanah_seeded', true);
}