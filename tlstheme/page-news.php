<?php
/**
 * Template Name: News Template
 */

// Disable fullscreen footer
add_filter('body_class', function($classes) {
    $classes[] = 'disable-fullscreen-footer';
    return $classes;
});

get_header();

$news = new WP_Query([
    'post_type' => 'post',
    'posts_per_page' => 9,
    'post_status' => 'publish',
    'orderby' => 'date',
    'order' => 'DESC',
    'paged' => get_query_var('paged') ?: 1,
]);
?>

<div class="news-page-wrapper">
    <div class="container section">
        <div class="blog-header">
            <h1 class="section-title">Panduan & Berita Tanah Sabah</h1>
            <p class="section-subtitle">Maklumat terkini tentang hartanah, pelaburan dan pembangunan tanah di Sabah</p>
        </div>
        
        <div class="blog-grid">
            <?php if ($news->have_posts()) : ?>
                <?php 
                $post_count = 0;
                while ($news->have_posts()) : 
                    $news->the_post();
                    $post_count++;
                    get_template_part('template-parts/content', 'blog');
                endwhile;
                wp_reset_postdata();
                ?>
                
                <?php if ($news->max_num_pages > 1) : ?>
                <div class="pagination-wrapper">
                    <?php
                    $big = 999999999;
                    echo paginate_links([
                        'base' => str_replace($big, '%#%', get_pagenum_link($big)),
                        'format' => '?paged=%#%',
                        'current' => max(1, get_query_var('paged')),
                        'total' => $news->max_num_pages,
                        'prev_text' => __('« Prev'),
                        'next_text' => __('Next »'),
                    ]);
                    ?>
                </div>
                <?php endif; ?>
                
            <?php else : ?>
                <div class="no-results">
                    <div class="no-results-icon">📰</div>
                    <h3>Belum Ada Berita</h3>
                    <p>Artikel pertama akan muncul tidak lama lagi.</p>
                    <a href="<?php echo home_url('/'); ?>" class="back-home-btn">Kembali ke Laman Utama</a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
.news-page-wrapper {
    min-height: 60vh;
    padding-bottom: 60px;
    background: var(--bg);
    width: 100%;
    padding-top: 24px;
}

.news-page-wrapper .container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 16px;
}

.blog-header {
    text-align: center;
    padding: 72px 0 32px;
}

.blog-header .section-title {
    font-size: clamp(1.5rem, 4vw, 2.5rem);
    font-weight: 800;
    margin-bottom: 12px;
    color: var(--text);
    line-height: 1.25;
}

.blog-header .section-subtitle {
    font-size: 1.1rem;
    color: var(--muted);
    max-width: 600px;
    margin: 0 auto;
}

.blog-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 24px;
    width: 100%;
}

.pagination-wrapper {
    grid-column: 1 / -1;
    display: flex;
    justify-content: center;
    gap: 8px;
    margin: 30px 0;
    flex-wrap: wrap;
}

.pagination-wrapper a,
.pagination-wrapper span {
    padding: 8px 14px;
    border-radius: 6px;
    background: var(--white);
    border: 1px solid var(--border);
    color: var(--text);
    text-decoration: none;
    font-size: 0.9rem;
}

.pagination-wrapper a:hover {
    background: var(--primary);
    color: #fff;
    border-color: var(--primary);
}

.pagination-wrapper .current {
    background: var(--primary);
    color: #fff;
    border-color: var(--primary);
}

.no-results {
    grid-column: 1 / -1;
    text-align: center;
    padding: 60px 20px;
}

.no-results-icon {
    font-size: 64px;
    margin-bottom: 16px;
}

.no-results h3 {
    font-size: 1.5rem;
    margin-bottom: 8px;
    color: var(--text);
}

.no-results p {
    color: var(--muted);
    margin-bottom: 20px;
}

.back-home-btn {
    display: inline-block;
    padding: 12px 24px;
    background: var(--primary);
    color: #fff;
    border-radius: 8px;
    text-decoration: none;
    font-weight: 600;
}

.back-home-btn:hover {
    background: #0d6e61;
}

/* Tablet - iPad */
@media (max-width: 1024px) {
    .blog-grid {
        grid-template-columns: repeat(2, 1fr);
        gap: 20px;
    }
}

/* Mobile */
@media (max-width: 768px) {
    .news-page-wrapper {
        padding-bottom: 40px;
        padding-top: 0;
    }
    
    .blog-header {
        padding: 56px 16px 24px;
    }
    
    .blog-header .section-title {
        font-size: 1.6rem;
        line-height: 1.3;
    }
    
    .blog-header .section-subtitle {
        font-size: 0.95rem;
    }
    
    .blog-grid {
        grid-template-columns: 1fr;
        gap: 16px;
    }
}

/* Small Mobile */
@media (max-width: 480px) {
    .news-page-wrapper {
        padding-top: 0;
    }

    .blog-header {
        padding: 48px 12px 20px;
    }

    .blog-header .section-title {
        font-size: 1.4rem;
        line-height: 1.3;
    }
    
    .pagination-wrapper a,
    .pagination-wrapper span {
        padding: 6px 10px;
        font-size: 0.85rem;
    }
}
</style>

<?php get_footer(); ?>