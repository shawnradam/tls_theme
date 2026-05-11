<?php
/**
 * WP CLI command to seed news and tanah data
 * Usage: wp eval-file inc/seed-wpcli.php
 */

if (!defined('ABSPATH')) {
    $cmd = array_shift($argv);
    foreach ($argv as $arg) {
        if (file_exists($arg)) {
            require_once $arg;
            break;
        }
    }
}

if (!function_exists('tls_run_news_seed')) {
    function tls_run_news_seed() {
        if (get_option('tls_news_seeded')) {
            echo "News already seeded. Skipping.\n";
            return;
        }
        
        $posts_data = [
            [
                'title' => 'Native Customary Rights: A Complete Guide for Land Investors in Sabah',
                'content' => file_get_contents(__DIR__ . '/news-templates/ncr-guide.php'),
                'excerpt' => 'Understand the 7 conditions for NCR claims under Sabah Land Ordinance Section 15 and how native land ownership affects your investment strategy.',
                'year' => 2020,
                'categories' => ['NT Land', 'Investor Guide']
            ],
            [
                'title' => 'Sabah Land Title Distribution 2021: NT vs CL Statistics',
                'content' => file_get_contents(__DIR__ . '/news-templates/nt-cl-distribution.php'),
                'excerpt' => 'Native Title (NT) and Country Lease (CL) ownership statistics reveal the balance of land rights in Sabah. 54% NT vs 46% CL.',
                'year' => 2021,
                'categories' => ['Land Titles', 'Statistics']
            ],
            [
                'title' => 'PANTAS Programme: Accelerating Native Land Ownership in Sabah',
                'content' => file_get_contents(__DIR__ . '/news-templates/pantas-program.php'),
                'excerpt' => 'The Sabah Native Land Services Programme (PANTAS) has issued 27,572 NT grants covering 42,429 hectares since 2012.',
                'year' => 2022,
                'categories' => ['NT Land', 'Government']
            ],
            [
                'title' => 'Sabah Residential Market Rebounds: RM2.78 Billion in Transactions',
                'content' => file_get_contents(__DIR__ . '/news-templates/residential-2022.php'),
                'excerpt' => 'The Sabah property market saw a strong rebound with 5,792 residential transactions worth RM2.78 billion in 2022.',
                'year' => 2022,
                'categories' => ['Market Update', 'Residential']
            ],
            [
                'title' => 'KKIP: RM8.32 Billion Investment Hub Transforms Sabah\'s Economy',
                'content' => file_get_contents(__DIR__ . '/news-templates/kkip-investment.php'),
                'excerpt' => 'Kota Kinabalu Industrial Park has attracted RM8.32 billion in investments and created 5,882 jobs from 2017-2024.',
                'year' => 2023,
                'categories' => ['Industrial', 'Investment']
            ],
            [
                'title' => 'Sabah Property Market Update: RM2.37 Billion in Residential Sales 2023',
                'content' => file_get_contents(__DIR__ . '/news-templates/residential-2023.php'),
                'excerpt' => 'Despite a slight dip in volume, Sabah residential transactions value increased to RM2.37 billion in 2023.',
                'year' => 2023,
                'categories' => ['Market Update', 'Statistics']
            ],
            [
                'title' => '16,760 Native Titles Granted Since 2020: Sabah\'s Land Reform Progress',
                'content' => file_get_contents(__DIR__ . '/news-templates/nt-reform-2024.php'),
                'excerpt' => 'The Sabah government has granted 16,760 NT titles across 223 villages since 2020, with 4,834 hectares distributed.',
                'year' => 2024,
                'categories' => ['NT Land', 'Government']
            ],
            [
                'title' => 'Pan Borneo Highway: Connecting Sabah\'s Future',
                'content' => file_get_contents(__DIR__ . '/news-templates/pan-borneo.php'),
                'excerpt' => 'Phase 1A of the Pan Borneo Highway Sabah is 77.91% complete, with 706km of new roads transforming connectivity.',
                'year' => 2024,
                'categories' => ['Infrastructure', 'Development']
            ],
            [
                'title' => 'NT vs CL Land: Investment Profit Comparison 2024',
                'content' => file_get_contents(__DIR__ . '/news-templates/nt-cl-roi.php'),
                'excerpt' => 'Compare investment returns: Country Lease (CL) offers 15-18% ROI with broader market access, while Native Title (NT) provides 8-10% with native-only trading.',
                'year' => 2024,
                'categories' => ['Investor Guide', 'ROI']
            ],
            [
                'title' => 'Sabah\'s Property Uptrend: RM5.17 Trillion Transaction Value',
                'content' => file_get_contents(__DIR__ . '/news-templates/market-uptrend-2025.php'),
                'excerpt' => 'Sabah property market shows significant uptrend with RM5.17 trillion in MOT transaction value as of November 2024.',
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
                        $term_id = is_array($term) ? $term['term_id'] : $term->term_id;
                        wp_set_object_terms($post_id, $term_id, 'category', true);
                    }
                }
                echo "Created post: " . $data['title'] . " (ID: $post_id)\n";
            }
        }

        update_option('tls_news_seeded', true);
        echo "\nNews seeding complete!\n";
    }
}

echo "=== TLS News Seeder ===\n";
tls_run_news_seed();
echo "\nDone!\n";