<article id="post-<?php the_ID(); ?>" class="blog-card <?php if (has_excerpt()) echo 'has-excerpt'; ?>">
    <a href="<?php the_permalink(); ?>" class="blog-image-link">
        <div class="blog-image">
            <?php if (has_post_thumbnail()): ?>
                <img src="<?php the_post_thumbnail_url('medium_large'); ?>" alt="<?php the_title_attribute(); ?>">
            <?php else: ?>
                <img src="https://picsum.photos/seed/<?php the_ID(); ?>/600/400" alt="Sabah Land News">
            <?php endif; ?>
            <span class="blog-year-overlay"><?php echo get_the_date('Y'); ?></span>
        </div>
    </a>
    <div class="blog-content">
        <div class="blog-meta">
            <span class="blog-date"><?php echo get_the_date('d M Y'); ?></span>
            <?php 
            $categories = get_the_category();
            if ($categories) {
                echo '<span class="blog-category">' . esc_html($categories[0]->name) . '</span>';
            }
            ?>
        </div>
        <h2 class="blog-title">
            <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
        </h2>
        <div class="blog-excerpt">
            <?php the_excerpt(); ?>
        </div>
        <a href="<?php the_permalink(); ?>" class="blog-read-more">
            Baca Lagi <span>&rarr;</span>
        </a>
    </div>
</article>