<?php
/**
 * Blog News Seed Data
 * Creates placeholder news posts with real Sabah land statistics
 *
 * @package TanahLotSabah
 */

if (!defined('ABSPATH')) exit;

add_action('after_setup_theme', 'tls_seed_news_posts');
function tls_seed_news_posts() {
    // Check if re-seeding is requested
    if (isset($_GET['reseed_news']) && current_user_can('manage_options')) {
        // Delete existing news posts
        $existing_news = get_posts([
            'post_type' => 'post',
            'posts_per_page' => -1,
            'post_status' => 'any'
        ]);
        foreach ($existing_news as $post) {
            wp_delete_post($post->ID, true);
        }
        delete_option('tls_news_seeded');
    }

    // Check if already seeded
    $seeded = get_option('tls_news_seeded', false);
    
    if ($seeded) return;

    $posts_data = [
        [
            'title' => 'Native Customary Rights: A Complete Guide for Land Investors in Sabah',
            'content' => file_get_contents(__DIR__ . '/news-templates/ncr-guide.php'),
            'excerpt' => 'Understand the 7 conditions for NCR claims under Sabah Land Ordinance Section 15 and how native land ownership affects your investment strategy.',
            'year' => 2020,
            'chart_type' => 'ncr_claims',
            'categories' => ['NT Land', 'Investor Guide']
        ],
        [
            'title' => 'Sabah Land Title Distribution 2021: NT vs CL Statistics',
            'content' => file_get_contents(__DIR__ . '/news-templates/nt-cl-distribution.php'),
            'excerpt' => 'Native Title (NT) and Country Lease (CL) ownership statistics reveal the balance of land rights in Sabah.',
            'year' => 2021,
            'chart_type' => 'nt_cl_pie',
            'categories' => ['Land Titles', 'Statistics']
        ],
        [
            'title' => 'PANTAS Programme: Accelerating Native Land Ownership in Sabah',
            'content' => file_get_contents(__DIR__ . '/news-templates/pantas-program.php'),
            'excerpt' => 'The Sabah Native Land Services Programme (PANTAS) has issued 27,572 NT grants covering 42,429 hectares since 2012.',
            'year' => 2022,
            'chart_type' => 'pantas_growth',
            'categories' => ['NT Land', 'Government']
        ],
        [
            'title' => 'Sabah Residential Market Rebounds: RM2.78 Billion in Transactions',
            'content' => file_get_contents(__DIR__ . '/news-templates/residential-2022.php'),
            'excerpt' => 'The Sabah property market saw a strong rebound with 5,792 residential transactions worth RM2.78 billion in 2022.',
            'year' => 2022,
            'chart_type' => 'quarterly_transactions',
            'categories' => ['Market Update', 'Residential']
        ],
        [
            'title' => 'KKIP: RM8.32 Billion Investment Hub Transforms Sabah\'s Economy',
            'content' => file_get_contents(__DIR__ . '/news-templates/kkip-investment.php'),
            'excerpt' => 'Kota Kinabalu Industrial Park has attracted RM8.32 billion in investments and created 5,882 jobs from 2017-2024.',
            'year' => 2023,
            'chart_type' => 'investment_doughnut',
            'categories' => ['Industrial', 'Investment']
        ],
        [
            'title' => 'Sabah Property Market Update: RM2.37 Billion in Residential Sales 2023',
            'content' => file_get_contents(__DIR__ . '/news-templates/residential-2023.php'),
            'excerpt' => 'Despite a slight dip in volume, Sabah residential transactions value increased to RM2.37 billion in 2023.',
            'year' => 2023,
            'chart_type' => 'annual_comparison',
            'categories' => ['Market Update', 'Statistics']
        ],
        [
            'title' => '16,760 Native Titles Granted Since 2020: Sabah\'s Land Reform Progress',
            'content' => file_get_contents(__DIR__ . '/news-templates/nt-reform-2024.php'),
            'excerpt' => 'The Sabah government has granted 16,760 NT titles across 223 villages since 2020, with 4,834 hectares distributed.',
            'year' => 2024,
            'chart_type' => 'titles_timeline',
            'categories' => ['NT Land', 'Government']
        ],
        [
            'title' => 'Pan Borneo Highway: Connecting Sabah\'s Future',
            'content' => file_get_contents(__DIR__ . '/news-templates/pan-borneo.php'),
            'excerpt' => 'Phase 1A of the Pan Borneo Highway Sabah is 77.91% complete, with 706km of new roads transforming connectivity.',
            'year' => 2024,
            'chart_type' => 'phase_progress',
            'categories' => ['Infrastructure', 'Development']
        ],
        [
            'title' => 'NT vs CL Land: Investment Profit Comparison 2024',
            'content' => file_get_contents(__DIR__ . '/news-templates/nt-cl-roi.php'),
            'excerpt' => 'Compare investment returns: Country Lease (CL) offers 15-18% ROI with broader market access, while Native Title (NT) provides 8-10% with native-only trading.',
            'year' => 2024,
            'chart_type' => 'roi_comparison',
            'categories' => ['Investor Guide', 'ROI']
        ],
        [
            'title' => 'Sabah\'s Property Uptrend: RM5.17 Trillion Transaction Value',
            'content' => file_get_contents(__DIR__ . '/news-templates/market-uptrend-2025.php'),
            'excerpt' => 'Sabah property market shows significant uptrend with RM5.17 trillion in MOT transaction value as of November 2024.',
            'year' => 2025,
            'chart_type' => 'value_trend',
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
            'post_category' => []
        ]);

        if (!is_wp_error($post_id)) {
            foreach ($data['categories'] as $cat) {
                $term = get_term_by('name', $cat, 'category');
                if (!$term) {
                    $term = wp_insert_term($cat, 'category');
                    if (!is_wp_error($term)) {
                        wp_set_object_terms($post_id, $term['term_id'], 'category', true);
                    }
                } else {
                    wp_set_object_terms($post_id, $term->term_id, 'category', true);
                }
            }
            update_post_meta($post_id, '_tls_chart_type', $data['chart_type']);
        }
    }

    update_option('tls_news_seeded', true);
}