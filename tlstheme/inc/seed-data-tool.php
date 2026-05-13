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
            $count = tls_run_news_seed(true); // Force seed
            $message = "Success! $count News posts processed.";
            $status = 'success';
        }
        if (isset($_POST['seed_tanah'])) {
            $count = tls_run_tanah_seed(true); // Force seed
            $message = "Success! $count Tanah properties processed.";
            $status = 'success';
        }
        if (isset($_POST['seed_all'])) {
            $c1 = tls_run_news_seed(true);
            $c2 = tls_run_tanah_seed(true);
            $message = "All data processed! ($c1 News, $c2 Properties)";
            $status = 'success';
        }
        if (isset($_POST['reset_seeds'])) {
            delete_option('tls_news_seeded');
            delete_option('tls_tanah_seeded');
            $message = 'Seed flags reset. You can now re-seed if needed.';
            $status = 'info';
        }
    }
    
    $news_count = wp_count_posts('post')->publish;
    $tanah_count = wp_count_posts('tanah')->publish;
    ?>
    <div class="wrap tls-seed-modern">
        <div class="tls-admin-header">
            <h1>
                <span class="dashicons dashicons-database-import"></span>
                Seed Data Manager
            </h1>
            <p>Populate your database with professional Sabah land data and statistics.</p>
        </div>

        <?php if ($message): ?>
            <div class="notice notice-<?php echo $status; ?> is-dismissible" style="margin: 20px 0; border-radius: 8px;">
                <p><strong><?php echo esc_html($message); ?></strong></p>
            </div>
        <?php endif; ?>

        <div class="tls-grid-layout">
            <!-- Left Column: Status & Stats -->
            <div class="tls-grid-col main-col">
                <div class="tls-card status-card">
                    <div class="card-header">
                        <span class="dashicons dashicons-chart-bar"></span>
                        <h2>Current Database Status</h2>
                    </div>
                    <div class="status-grid">
                        <div class="status-item">
                            <div class="val"><?php echo $news_count; ?></div>
                            <div class="lab">Published Posts</div>
                        </div>
                        <div class="status-item">
                            <div class="val"><?php echo $tanah_count; ?></div>
                            <div class="lab">Tanah Listings</div>
                        </div>
                        <div class="status-item <?php echo get_option('tls_news_seeded') ? 'done' : ''; ?>">
                            <div class="val"><?php echo get_option('tls_news_seeded') ? '✓' : '—'; ?></div>
                            <div class="lab">News Seeded</div>
                        </div>
                        <div class="status-item <?php echo get_option('tls_tanah_seeded') ? 'done' : ''; ?>">
                            <div class="val"><?php echo get_option('tls_tanah_seeded') ? '✓' : '—'; ?></div>
                            <div class="lab">Tanah Seeded</div>
                        </div>
                    </div>
                </div>

                <div class="tls-card actions-card">
                    <div class="card-header">
                        <span class="dashicons dashicons-admin-generic"></span>
                        <h2>Available Actions</h2>
                    </div>
                    <form method="post" class="seed-form">
                        <?php wp_nonce_field('tls_seed_action', 'tls_seed_nonce'); ?>
                        <div class="action-buttons">
                            <button type="submit" name="seed_all" class="btn btn-primary">
                                <span class="dashicons dashicons-cloud-upload"></span>
                                Seed Everything
                            </button>
                            <button type="submit" name="seed_news" class="btn btn-secondary">
                                <span class="dashicons dashicons-admin-post"></span>
                                Seed News Only
                            </button>
                            <button type="submit" name="seed_tanah" class="btn btn-secondary">
                                <span class="dashicons dashicons-location"></span>
                                Seed Properties
                            </button>
                            <button type="submit" name="reset_seeds" class="btn btn-warning">
                                <span class="dashicons dashicons-undo"></span>
                                Reset Lock Flags
                            </button>
                        </div>
                        <p class="hint">Note: System will automatically skip items that already exist based on the title.</p>
                    </form>
                </div>
            </div>

            <!-- Right Column: Previews -->
            <div class="tls-grid-col side-col">
                <div class="tls-card preview-card">
                    <div class="card-header">
                        <span class="dashicons dashicons-visibility"></span>
                        <h2>Data Previews</h2>
                    </div>
                    <div class="preview-section">
                        <h4>Professional News Articles</h4>
                        <div class="preview-list">
                            <span>NT vs CL ROI Comparison</span>
                            <span>PANTAS Programme Updates</span>
                            <span>Sabah Residential Trends</span>
                        </div>
                        <p class="small">Each post includes Chart.js animated statistics.</p>
                    </div>
                    <hr>
                    <div class="preview-section">
                        <h4>Geographic Sample Data</h4>
                        <div class="preview-list">
                            <span>Jalan Sulaman, Shangri La</span>
                            <span>Kg. Tambalugu Properties</span>
                            <span>Inanam Industrial Plots</span>
                        </div>
                        <p class="small">Includes coordinates for map rendering.</p>
                    </div>
                </div>
            </div>
        </div>

        <style>
            .tls-seed-modern { margin-top: 20px; font-family: -apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,Oxygen-Sans,Ubuntu,Cantarell,"Helvetica Neue",sans-serif; }
            .tls-admin-header h1 { font-size: 24px; font-weight: 700; margin-bottom: 5px; color: #1e293b; display: flex; align-items: center; gap: 10px; }
            .tls-admin-header p { color: #64748b; margin-top: 0; font-size: 14px; }
            
            .tls-grid-layout { display: grid; grid-template-columns: 1fr 300px; gap: 20px; margin-top: 24px; }
            .tls-card { background: #fff; border-radius: 12px; border: 1px solid #e2e8f0; padding: 24px; box-shadow: 0 1px 3px rgba(0,0,0,0.05); margin-bottom: 20px; }
            .card-header { display: flex; align-items: center; gap: 10px; margin-bottom: 20px; padding-bottom: 15px; border-bottom: 1px solid #f1f5f9; }
            .card-header h2 { font-size: 16px; font-weight: 600; margin: 0; color: #334155; }
            .card-header .dashicons { color: #16a34a; }

            .status-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 15px; }
            .status-item { background: #f8fafc; border: 1px solid #f1f5f9; padding: 15px; border-radius: 10px; text-align: center; }
            .status-item.done { border-color: #bbf7d0; background: #f0fdf4; }
            .status-item .val { font-size: 24px; font-weight: 800; color: #0f172a; margin-bottom: 5px; }
            .status-item .lab { font-size: 11px; text-transform: uppercase; letter-spacing: 0.5px; color: #64748b; font-weight: 600; }
            .status-item.done .val { color: #16a34a; }

            .action-buttons { display: flex; flex-direction: column; gap: 12px; max-width: 300px; }
            .btn { display: inline-flex; align-items: center; justify-content: center; gap: 8px; padding: 12px 20px; border-radius: 8px; font-weight: 600; font-size: 13px; cursor: pointer; border: none; transition: all 0.2s; text-decoration: none; }
            .btn-primary { background: #16a34a; color: white; }
            .btn-primary:hover { background: #15803d; transform: translateY(-1px); }
            .btn-secondary { background: #f1f5f9; color: #334155; border: 1px solid #e2e8f0; }
            .btn-secondary:hover { background: #e2e8f0; }
            .btn-warning { background: #fff7ed; color: #b45309; border: 1px solid #ffedd5; }
            .btn-warning:hover { background: #ffedd5; }
            
            .hint { font-size: 12px; color: #94a3b8; margin-top: 15px; font-style: italic; }
            
            .preview-section h4 { font-size: 13px; margin: 0 0 10px 0; color: #1e293b; }
            .preview-list { display: flex; flex-direction: column; gap: 5px; margin-bottom: 10px; }
            .preview-list span { font-size: 12px; color: #64748b; background: #f8fafc; padding: 4px 8px; border-radius: 4px; }
            .small { font-size: 11px; color: #94a3b8; }
            hr { border: 0; border-top: 1px solid #f1f5f9; margin: 15px 0; }

            @media (max-width: 900px) { .tls-grid-layout { grid-template-columns: 1fr; } }
        </style>
    </div>
    <?php
}

/**
 * Optimized News Seed Function
 * Prevents duplicates by checking title
 */
function tls_run_news_seed($force = false) {
    if (!$force && get_option('tls_news_seeded')) return 0;
    
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

    $count = 0;
    foreach ($posts_data as $data) {
        // Prevent duplicates
        if (get_page_by_title($data['title'], OBJECT, 'post')) continue;

        $post_id = wp_insert_post([
            'post_title' => $data['title'],
            'post_content' => $data['content'],
            'post_excerpt' => $data['excerpt'],
            'post_status' => 'publish',
            'post_type' => 'post',
            'post_date' => $data['year'] . '-06-15 10:00:00',
        ]);

        if (!is_wp_error($post_id)) {
            $count++;
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
    return $count;
}

/**
 * Optimized Tanah Seed Function
 * Prevents duplicates by checking title
 */
function tls_run_tanah_seed($force = false) {
    if (!$force && get_option('tls_tanah_seeded')) return 0;
    
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

    $count = 0;
    foreach ($properties as $prop) {
        // Prevent duplicates
        if (get_page_by_title($prop['title'], OBJECT, 'tanah')) continue;

        $post_id = wp_insert_post([
            'post_title' => $prop['title'],
            'post_content' => 'Sample property - ' . $prop['title'],
            'post_status' => 'publish',
            'post_type' => 'tanah'
        ]);

        if (!is_wp_error($post_id)) {
            $count++;
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
    return $count;
}
