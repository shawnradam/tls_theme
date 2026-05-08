<?php
// For news/blog posts
if (is_home() || is_archive() || is_search() || (is_single() && get_post_type() === 'post')) {
    get_header();
    ?>
    <div class="container section">
        <div class="blog-header">
            <h1 class="section-title">
                <?php if (is_search()): ?>
                Search: "<?php echo get_search_query(); ?>"
                <?php elseif (is_archive()): ?>
                <?php the_archive_title(); ?>
                <?php elseif (is_single()): ?>
                News
                <?php else: ?>
                News & Updates
                <?php endif; ?>
            </h1>
        </div>
        
        <div class="blog-grid">
            <?php 
            if (have_posts()) : 
                while (have_posts()) : the_post();
                    get_template_part('template-parts/content', 'blog');
                endwhile;
                
                // Pagination
                echo '<div class="pagination" style="grid-column: 1/-1;">';
                echo paginate_links([
                    'base' => str_replace(999999999, '%#%', get_pagenum_link(999999999)),
                    'format' => '?paged=%#%',
                    'current' => max(1, get_query_var('paged')),
                    'total' => $wp_query->max_num_pages,
                    'prev_text' => '‹',
                    'next_text' => '›',
                ]);
                echo '</div>';
            else : ?>
                <p class="no-results" style="grid-column:1/-1;text-align:center;padding:40px;">No news yet.</p>
            <?php endif; ?>
        </div>
    </div>
    <?php
    get_footer();
    return;
}

// Default to tanah listings
get_header();
?>

<div class="container section">
    <h1 class="section-title">Senarai Tanah</h1>
    <div class="listings-grid">
        <?php 
        $tanah = new WP_Query([
            'post_type' => 'tanah',
            'posts_per_page' => 12,
            'post_status' => 'publish'
        ]);
        
        if ($tanah->have_posts()) : 
            while ($tanah->have_posts()) : $tanah->the_post();
                get_template_part('template-parts/card', 'land');
            endwhile;
            wp_reset_postdata();
        else : ?>
            <p class="no-results">Tiada tanah dijumpai.</p>
        <?php endif; ?>
    </div>
</div>

<?php get_footer(); ?>
