<?php
/**
 * News AJAX Handlers
 */

if (!defined('ABSPATH')) exit;

add_action('wp_ajax_tls_load_more_news', 'tls_ajax_load_more_news');
add_action('wp_ajax_nopriv_tls_load_more_news', 'tls_ajax_load_more_news');

function tls_ajax_load_more_news() {
    $page = isset($_POST['page']) ? intval($_POST['page']) : 1;
    $posts_per_page = 3;
    $offset = ($page - 1) * $posts_per_page;

    $query = new WP_Query([
        'post_type' => 'post',
        'posts_per_page' => $posts_per_page,
        'offset' => $offset,
        'post_status' => 'publish',
        'orderby' => 'date',
        'order' => 'DESC'
    ]);

    if ($query->have_posts()) {
        ob_start();
        while ($query->have_posts()) {
            $query->the_post();
            $year = get_the_date('Y');
            $categories = get_the_category();
            $cat_name = $categories ? $categories[0]->name : 'Panduan';
            ?>
            <article class="news-card">
                <a href="<?php the_permalink(); ?>" class="news-image-link">
                    <div class="news-image">
                        <?php if (has_post_thumbnail()): ?>
                            <img src="<?php the_post_thumbnail_url('medium_large'); ?>" alt="<?php the_title_attribute(); ?>">
                        <?php else: ?>
                            <img src="<?php echo get_template_directory_uri(); ?>/assets/images/placeholder.jpeg" alt="Sabah Land News">
                        <?php endif; ?>
                        <span class="news-year"><?php echo esc_html($year); ?></span>
                    </div>
                </a>
                <div class="news-content">
                    <div class="news-meta">
                        <span class="news-category"><?php echo esc_html($cat_name); ?></span>
                        <span class="news-date"><?php echo get_the_date('d M Y'); ?></span>
                    </div>
                    <h3 class="news-title">
                        <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                    </h3>
                    <p class="news-excerpt"><?php echo wp_trim_words(get_the_excerpt(), 18, '...'); ?></p>
                    <a href="<?php the_permalink(); ?>" class="news-read-more">
                        Baca Lagi <i class="material-icons" style="font-size:16px;">arrow_forward</i>
                    </a>
                </div>
            </article>
            <?php
        }
        wp_reset_postdata();
        $html = ob_get_clean();
        wp_send_json_success([
            'html' => $html,
            'more' => ($query->found_posts > ($offset + $posts_per_page))
        ]);
    } else {
        wp_send_json_error('No more posts');
    }
}
