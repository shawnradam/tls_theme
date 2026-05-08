<?php
// Redirect to front page if this is the homepage
if (is_front_page()) {
    wp_redirect(home_url());
    exit;
}

get_header();
?>

<div class="container section">
    <div class="blog-header">
        <h1 class="section-title">News & Updates</h1>
    </div>
    
    <div class="blog-grid">
        <?php 
        $news = new WP_Query([
            'post_type' => 'post',
            'posts_per_page' => 9,
            'post_status' => 'publish',
            'paged' => get_query_var('paged') ?: 1,
        ]);
        
        if ($news->have_posts()) : 
            while ($news->have_posts()) : $news->the_post();
                get_template_part('template-parts/content', 'blog');
            endwhile;
            
            // Pagination
            echo '<div class="pagination" style="grid-column: 1/-1;">';
            $big = 999999999;
            echo paginate_links([
                'base' => str_replace($big, '%#%', get_pagenum_link($big)),
                'format' => '?paged=%#%',
                'current' => max(1, get_query_var('paged')),
                'total' => $news->max_num_pages,
                'prev_text' => '‹',
                'next_text' => '›',
            ]);
            echo '</div>';
            
            wp_reset_postdata();
        else : ?>
            <p class="no-results" style="grid-column:1/-1;text-align:center;padding:40px;">No news yet.</p>
        <?php endif; ?>
    </div>
</div>

<?php get_footer(); ?>