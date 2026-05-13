<?php get_header(); ?>

<?php while (have_posts()) : the_post(); ?>
<article class="single-post-article">
    <div class="container">
        <!-- Featured Image -->
        <div class="single-post-image">
            <?php if (has_post_thumbnail()): ?>
                <?php the_post_thumbnail('large'); ?>
            <?php else: ?>
                <img src="<?php echo get_template_directory_uri(); ?>/assets/images/placeholder.jpeg" alt="<?php the_title_attribute(); ?>">
            <?php endif; ?>
            <span class="single-post-year"><?php echo get_the_date('Y'); ?></span>
        </div>
        
        <!-- Post Header -->
        <header class="single-post-header">
            <div class="single-post-meta">
                <span class="single-post-date"><?php echo get_the_date('d M Y'); ?></span>
                <?php 
                $categories = get_the_category();
                if ($categories) {
                    echo '<span class="single-post-category">' . esc_html($categories[0]->name) . '</span>';
                }
                ?>
            </div>
            <h1 class="single-post-title"><?php the_title(); ?></h1>
        </header>
        
        <!-- Post Content -->
        <div class="single-post-content">
            <?php the_content(); ?>
        </div>
        
        <!-- Post Footer -->
        <footer class="single-post-footer">
            <?php 
            $tags = get_the_tags();
            if ($tags): ?>
            <div class="single-post-tags">
                <span class="tags-label">Tags:</span>
                <?php foreach ($tags as $tag): ?>
                    <a href="<?php echo get_tag_link($tag->term_id); ?>" class="tag-link"><?php echo esc_html($tag->name); ?></a>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
            
            <!-- Navigation -->
            <nav class="single-post-nav">
                <a href="<?php echo esc_url(home_url('/news/')); ?>" class="back-to-news">
                    &larr; Kembali ke Berita
                </a>
            </nav>
        </footer>
        
        <!-- Related Posts -->
        <?php 
        $related = new WP_Query([
            'post_type' => 'post',
            'posts_per_page' => 3,
            'post_status' => 'publish',
            'post__not_in' => [get_the_ID()],
        ]);
        if ($related->have_posts()): ?>
        <section class="related-posts-section">
            <h2>Artikel Lainnya</h2>
            <div class="related-posts-grid">
                <?php while ($related->have_posts()): $related->the_post(); ?>
                <a href="<?php the_permalink(); ?>" class="related-post-card">
                    <div class="related-post-image">
                        <?php if (has_post_thumbnail()): ?>
                            <img src="<?php the_post_thumbnail_url('medium'); ?>" alt="<?php the_title_attribute(); ?>">
                        <?php else: ?>
                            <img src="<?php echo get_template_directory_uri(); ?>/assets/images/placeholder.jpeg" alt="Article thumbnail">
                        <?php endif; ?>
                    </div>
                    <div class="related-post-info">
                        <span class="related-post-date"><?php echo get_the_date('d M Y'); ?></span>
                        <h3><?php the_title(); ?></h3>
                    </div>
                </a>
                <?php endwhile; wp_reset_postdata(); ?>
            </div>
        </section>
        <?php endif; ?>
    </div>
</article>

<style>
.single-post-article {
    padding: 40px 0 60px;
    background: #fff;
    min-height: 100vh;
}

.single-post-image {
    position: relative;
    width: 100%;
    max-height: 400px;
    overflow: hidden;
    margin-bottom: 30px;
}

.single-post-image img {
    width: 100%;
    height: auto;
    display: block;
}

.single-post-year {
    position: absolute;
    top: 16px;
    right: 16px;
    background: #16a34a;
    color: #fff;
    padding: 6px 14px;
    border-radius: 20px;
    font-size: 13px;
    font-weight: 700;
}

.single-post-header {
    margin-bottom: 30px;
}

.single-post-meta {
    display: flex;
    align-items: center;
    gap: 12px;
    margin-bottom: 16px;
    flex-wrap: wrap;
}

.single-post-date {
    color: #666;
    font-size: 0.9rem;
}

.single-post-category {
    background: #16a34a;
    color: #fff;
    padding: 4px 12px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
}

.single-post-title {
    font-size: 2rem;
    font-weight: 800;
    line-height: 1.3;
    color: #1a1a1a;
    margin: 0;
}

/* Content Styles - This is critical for showing content */
.single-post-content {
    font-size: 1rem;
    line-height: 1.8;
    color: #333;
    margin: 30px 0;
    max-width: 100%;
}

/* Ensure all child elements in content are visible */
.single-post-content > * {
    margin-bottom: 16px;
    display: block !important;
}

.single-post-content h3 {
    font-size: 1.3rem;
    font-weight: 700;
    color: #1a1a1a;
    margin: 24px 0 16px;
}

.single-post-content h4 {
    font-size: 1.1rem;
    font-weight: 600;
    color: #1a1a1a;
    margin: 20px 0 12px;
}

.single-post-content p {
    margin-bottom: 16px;
    line-height: 1.8;
    color: #444;
}

/* Ensure ALL divs in content are visible */
.single-post-content div {
    display: block !important;
    visibility: visible !important;
}

/* News Stats Section - The chart sections */
.news-stats-section {
    margin: 30px 0;
    padding: 24px;
    background: #f8fafc;
    border-radius: 12px;
    border: 1px solid #e2e8f0;
    display: block !important;
    visibility: visible !important;
}

.news-stats-section * {
    display: block !important;
}

.news-stats-section h3 {
    font-size: 1.25rem;
    font-weight: 700;
    color: #1a1a1a;
    margin-bottom: 16px;
}

.news-stats-section p {
    color: #444;
    line-height: 1.7;
}

/* Stats Highlight Grid */
.stats-highlight-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
    gap: 16px;
    margin: 24px 0;
}

.stat-box {
    background: #fff;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    padding: 20px 16px;
    text-align: center;
}

.stat-box.stat-nt {
    border-left: 4px solid #16a34a;
}

.stat-box.stat-cl {
    border-left: 4px solid #0369a1;
}

.stat-number {
    display: block;
    font-size: 1.6rem;
    font-weight: 800;
    color: #1a1a1a;
    line-height: 1.2;
}

.stat-label {
    display: block;
    font-size: 0.85rem;
    color: #666;
    margin-top: 4px;
}

.stat-prev {
    display: block;
    font-size: 0.75rem;
    color: #888;
    margin-top: 4px;
}

/* Tables */
.news-stats-section table,
.single-post-content table {
    width: 100%;
    border-collapse: collapse;
    margin: 20px 0;
    font-size: 0.9rem;
}

.news-stats-section table th,
.news-stats-section table td,
.single-post-content table th,
.single-post-content table td {
    padding: 12px;
    text-align: left;
    border-bottom: 1px solid #e2e8f0;
}

.news-stats-section table th,
.single-post-content table th {
    background: #f1f5f9;
    font-weight: 600;
}

/* Lists */
.news-stats-section ul,
.single-post-content ul {
    margin: 16px 0;
    padding-left: 24px;
}

.news-stats-section li,
.single-post-content li {
    margin-bottom: 10px;
    line-height: 1.7;
    color: #444;
}

/* Chart Container */
.stats-chart-container {
    background: #fff;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    padding: 20px;
    margin: 24px 0;
}

.stats-chart-container canvas {
    max-width: 100%;
    height: auto !important;
}

/* Post Footer */
.single-post-footer {
    margin-top: 40px;
    padding-top: 30px;
    border-top: 1px solid #e2e8f0;
}

.single-post-tags {
    display: flex;
    align-items: center;
    gap: 8px;
    flex-wrap: wrap;
    margin-bottom: 20px;
}

.tags-label {
    font-weight: 600;
    color: #666;
}

.tag-link {
    background: #f1f5f9;
    color: #333;
    padding: 6px 12px;
    border-radius: 20px;
    font-size: 13px;
    text-decoration: none;
    transition: background 0.2s;
}

.tag-link:hover {
    background: #16a34a;
    color: #fff;
}

.single-post-nav {
    margin-top: 20px;
}

.back-to-news {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 12px 24px;
    background: #16a34a;
    color: #fff;
    border-radius: 8px;
    text-decoration: none;
    font-weight: 600;
    transition: background 0.2s;
}

.back-to-news:hover {
    background: #0d6e61;
}

/* Related Posts */
.related-posts-section {
    margin-top: 50px;
    padding-top: 40px;
    border-top: 1px solid #e2e8f0;
}

.related-posts-section h2 {
    font-size: 1.5rem;
    margin-bottom: 24px;
    color: #1a1a1a;
}

.related-posts-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 20px;
}

.related-post-card {
    background: #fff;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    text-decoration: none;
    transition: transform 0.2s, box-shadow 0.2s;
    display: block;
}

.related-post-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
}

.related-post-image {
    aspect-ratio: 16/10;
    overflow: hidden;
}

.related-post-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    display: block;
}

.related-post-info {
    padding: 16px;
}

.related-post-date {
    font-size: 12px;
    color: #666;
}

.related-post-info h3 {
    font-size: 0.95rem;
    font-weight: 600;
    margin: 8px 0 0;
    color: #1a1a1a;
    line-height: 1.4;
}

/* Tablet */
@media (max-width: 1024px) {
    .related-posts-grid {
        grid-template-columns: repeat(2, 1fr);
    }
    
    .stats-highlight-grid {
        grid-template-columns: repeat(2, 1fr);
    }
}

/* Mobile */
@media (max-width: 768px) {
    .single-post-article {
        padding: 20px 0 40px;
    }
    
    .single-post-image {
        max-height: 250px;
    }
    
    .single-post-title {
        font-size: 1.5rem;
    }
    
    .single-post-content {
        font-size: 0.95rem;
        margin: 20px 0;
    }
    
    .news-stats-section {
        padding: 16px;
        margin: 20px 0;
    }
    
    .stats-highlight-grid {
        grid-template-columns: 1fr;
        gap: 12px;
    }
    
    .related-posts-grid {
        grid-template-columns: 1fr;
        gap: 16px;
    }
    
    .related-posts-section h2 {
        font-size: 1.3rem;
    }
    
    .stat-number {
        font-size: 1.4rem;
    }
}

/* Small Mobile */
@media (max-width: 480px) {
    .single-post-image {
        max-height: 200px;
    }
    
    .single-post-year {
        font-size: 11px;
        padding: 4px 10px;
    }
    
    .single-post-meta {
        gap: 8px;
    }
    
    .back-to-news {
        width: 100%;
        justify-content: center;
    }
    
    .news-stats-section table th,
    .news-stats-section table td {
        padding: 8px;
        font-size: 0.85rem;
    }
}
</style>

<?php endwhile; ?>

<?php get_footer(); ?>