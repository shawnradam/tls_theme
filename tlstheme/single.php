<?php get_header(); ?>

<?php while (have_posts()) : the_post(); ?>
<section class="section single-post">
    <div class="container">
        <div class="single-tanah-layout">
            <!-- Main Content -->
            <div class="single-tanah-main">
                <div class="single-tanah-image">
                    <?php if (has_post_thumbnail()): ?>
                        <?php the_post_thumbnail('large'); ?>
                    <?php else: ?>
                        <img src="<?php echo get_template_directory_uri(); ?>/assets/images/placeholder.jpeg" alt="<?php the_title_attribute(); ?>">
                    <?php endif; ?>
                </div>
                
                <div class="single-tanah-content">
                    <div class="blog-meta" style="margin-bottom: 16px;">
                        <span class="blog-date"><?php echo get_the_date('d M Y'); ?></span>
                        <?php 
                        $categories = get_the_category();
                        if ($categories) {
                            echo ' <span class="blog-category">' . esc_html($categories[0]->name) . '</span>';
                        }
                        ?>
                    </div>
                    
                    <h1 class="single-tanah-title"><?php the_title(); ?></h1>
                    
                    <div class="entry-content">
                        <?php the_content(); ?>
                    </div>
                    
                    <?php 
                    $tags = get_the_tags();
                    if ($tags): ?>
                    <div class="post-tags" style="margin-top: 30px;">
                        <strong>Tags:</strong>
                        <?php foreach ($tags as $tag): ?>
                            <a href="<?php echo get_tag_link($tag->term_id); ?>" style="background:#f1f5f9;padding:4px 10px;border-radius:4px;margin-right:8px;font-size:12px;text-decoration:none;color:#64748b;"><?php echo $tag->name; ?></a>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Sidebar -->
            <aside class="single-tanah-sidebar">
                <div class="single-tanah-card">
                    <h3 style="margin:0 0 16px;font-size:18px;">Share</h3>
                    <div class="share-buttons" style="display:flex;flex-direction:column;gap:10px;">
                        <a href="https://wa.me/?text=<?php the_title(); ?> - <?php the_permalink(); ?>" class="btn btn-block" style="background:#25d366;color:#fff;text-align:center;padding:10px;border-radius:8px;text-decoration:none;" target="_blank">
                            WhatsApp
                        </a>
                        <a href="https://www.facebook.com/sharer/sharer.php?u=<?php the_permalink(); ?>" class="btn btn-block" style="background:#1877f2;color:#fff;text-align:center;padding:10px;border-radius:8px;text-decoration:none;" target="_blank">
                            Facebook
                        </a>
                    </div>
                </div>
                
                <?php 
                $related = new WP_Query([
                    'post_type' => 'post',
                    'posts_per_page' => 3,
                    'post_status' => 'publish',
                    'post__not_in' => [get_the_ID()],
                ]);
                if ($related->have_posts()): ?>
                <div class="single-tanah-card" style="margin-top:16px;">
                    <h3 style="margin:0 0 16px;font-size:18px;">Related Posts</h3>
                    <div class="related-posts">
                        <?php while ($related->have_posts()): $related->the_post(); ?>
                        <a href="<?php the_permalink(); ?>" class="related-post-link" style="display:block;padding:10px 0;border-bottom:1px solid #eee;text-decoration:none;">
                            <div style="font-weight:600;color:var(--text);font-size:14px;"><?php the_title(); ?></div>
                            <div style="font-size:12px;color:var(--muted);"><?php echo get_the_date('d M Y'); ?></div>
                        </a>
                        <?php endwhile; wp_reset_postdata(); ?>
                    </div>
                </div>
                <?php endif; ?>
                
                <!-- Back Link -->
                <a href="<?php echo esc_url(home_url('/blog')); ?>" class="back-link" style="display:block;margin-top:16px;text-align:center;">
                    &larr; Back to Blog
                </a>
            </aside>
        </div>
    </div>
</section>

<?php endwhile; ?>

<?php get_footer(); ?>