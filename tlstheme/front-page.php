<?php 
get_header();

// Add body class to disable fullscreen footer (normal scroll behavior)
add_filter('body_class', function($classes) {
    $classes[] = 'disable-fullscreen-footer';
    return $classes;
});

// Include WordPress plugin functions for is_plugin_active()
include_once(ABSPATH . 'wp-admin/includes/plugin.php');

// Add global map functions to footer
add_action('wp_footer', 'tls_frontpage_global_scripts');

function tls_frontpage_global_scripts() {
    ?>
    <script>
    // Global functions - ALWAYS available on front page
    window.scrollToMap = function(event) {
        if (event) {
            event.preventDefault();
        }
        var mapSection = document.getElementById('map-portal');
        if (mapSection) {
            mapSection.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
    };

    window.toggleMobilePortalView = function() {
        var sidebar = document.querySelector('.map-portal-sidebar');
        var btn = document.getElementById('portal-view-btn');
        if (!sidebar || !btn) return;
        
        var isShowing = sidebar.classList.contains('show');
        
        if (isShowing) {
            sidebar.classList.remove('show');
            btn.innerHTML = '<i class="fas fa-list"></i> Senarai';
            btn.classList.remove('map-hidden');
            var mapSection = document.getElementById('map-portal');
            if (mapSection) {
                mapSection.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
            setTimeout(function() {
                if (window.tlsMap) {
                    window.tlsMap.invalidateSize();
                }
            }, 400);
        } else {
            sidebar.classList.add('show');
            btn.innerHTML = '<i class="fas fa-chevron-left"></i> Peta';
            btn.classList.add('map-hidden');
            var sidebarEl = document.querySelector('.map-portal-sidebar');
            if (sidebarEl) {
                sidebarEl.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        }
    };

    document.addEventListener('DOMContentLoaded', function() {
        var btn = document.getElementById('portal-view-btn');
        var sidebar = document.querySelector('.map-portal-sidebar');
        if (btn && sidebar) {
            sidebar.classList.remove('show');
            btn.innerHTML = '<i class="fas fa-list"></i> Senarai';
            btn.classList.remove('map-hidden');
        }

        // Show More News Logic (AJAX)
        var showMoreBtn = document.getElementById('showMoreNews');
        var newsGrid = document.getElementById('newsGrid');
        
        if (showMoreBtn && newsGrid) {
            showMoreBtn.addEventListener('click', function() {
                var currentPage = parseInt(newsGrid.getAttribute('data-page'));
                var nextPage = currentPage + 1;
                var btnText = showMoreBtn.querySelector('.btn-text');
                var btnLoading = showMoreBtn.querySelector('.btn-loading');
                var btnIcon = showMoreBtn.querySelector('.material-icons');

                // Show loading state
                showMoreBtn.disabled = true;
                if (btnText) btnText.style.display = 'none';
                if (btnIcon) btnIcon.style.display = 'none';
                if (btnLoading) btnLoading.style.display = 'inline';

                // AJAX Request
                var formData = new FormData();
                formData.append('action', 'tls_load_more_news');
                formData.append('page', nextPage);

                fetch('<?php echo admin_url('admin-ajax.php'); ?>', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Append new posts
                        var tempDiv = document.createElement('div');
                        tempDiv.innerHTML = data.data.html;
                        
                        while (tempDiv.firstChild) {
                            newsGrid.appendChild(tempDiv.firstChild);
                        }

                        // Update page number
                        newsGrid.setAttribute('data-page', nextPage);

                        // Hide button if no more posts
                        if (!data.data.more) {
                            showMoreBtn.style.display = 'none';
                        }
                    } else {
                        console.error('Error loading news:', data.data);
                        showMoreBtn.style.display = 'none';
                    }
                })
                .catch(error => {
                    console.error('AJAX Error:', error);
                })
                .finally(() => {
                    // Reset loading state
                    showMoreBtn.disabled = false;
                    if (btnText) btnText.style.display = 'inline';
                    if (btnIcon) btnIcon.style.display = 'inline';
                    if (btnLoading) btnLoading.style.display = 'none';
                });
            });
        }
    });
    </script>
    <?php
}
?>


<!-- HERO SECTION -->
<?php 
$hero_videos = tls_get_hero_videos();
$main_video = !empty($hero_videos) ? $hero_videos[0] : null;

if (!$main_video) {
    $main_video = get_posts(['post_type' => 'hero_video', 'posts_per_page' => 1, 'post_status' => 'any']);
    $main_video = !empty($main_video) ? $main_video[0] : null;
}

$video_url = '';
$video_poster = '';
if ($main_video) {
    $video_url = get_post_meta($main_video->ID, '_hero_video_url', true) ?: '';
    $video_poster = get_the_post_thumbnail_url($main_video->ID, 'full') ?: '';
}

$fallback_video = get_template_directory_uri() . '/assets/videos/hero.mp4';
$fallback_poster = get_template_directory_uri() . '/assets/images/hero-poster.jpg';
$final_video = !empty($video_url) ? $video_url : $fallback_video;
$final_poster = !empty($video_poster) ? $video_poster : $fallback_poster;
?>

<section class="hero">
    <div class="hero-overlay"></div>
    <?php if (!empty($final_video)): ?>
    <video class="hero-video" autoplay muted loop playsinline poster="<?php echo esc_url($final_poster); ?>">
        <source src="<?php echo esc_url($final_video); ?>" type="video/mp4">
    </video>
    <?php endif; ?>
    
    <div class="hero-content">
        <h1 class="hero-title">Cari Lot Tanah Impian Anda<br>di <span class="highlight">Tanah Lot Sabah</span></h1>
        <p class="hero-subtitle">Terokai lot tanah premium dengan sempadan yang disahkan melalui sistem peta interaktif kami.</p>
        
        <div class="hero-actions">
            <a href="#" onclick="scrollToMap(event); return false;" class="btn-hero-primary">
                <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l5.447 2.724A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/></svg>
                Teroka Peta
            </a>
            <a href="<?php echo home_url('/tanah/'); ?>" class="btn-hero-secondary">
                <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/></svg>
                Semua Tanah
            </a>
            <a href="<?php echo home_url('/calculator/'); ?>" class="btn-hero-secondary">
                <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                Kira Kos
            </a>
        </div>
    </div>
</section>

<!-- INTEGRATED LAND MAP PORTAL -->
<?php if (is_plugin_active('tlsmap/tlsmap.php')): ?>
<section class="tls-map-portal-section" id="map-portal">
    <div class="map-portal-container">
        <div class="map-portal-sidebar">
            <div class="sidebar-header">
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:10px;">
                    <h3>Hartanah di Kawasan Ini</h3>
                    <button onclick="toggleMobilePortalView()" class="back-to-map-btn" style="background:#f1f5f9; border:1px solid #e2e8f0; border-radius:6px; padding:6px 12px; cursor:pointer; font-size:12px; color:#0f172a; display:flex; align-items:center; gap:4px;">
                        <i class="fas fa-map"></i> Tengok Peta
                    </button>
                </div>
                <div class="results-count"><span id="map-results-count">0</span> hartanah dijumpai</div>
            </div>
            <div class="sidebar-filters">
                <div class="search-box-minimal">
                    <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    <input type="text" id="map-sidebar-search" placeholder="Cari nama, geran, daerah...">
                </div>
                <div class="filter-row">
                    <div class="filter-group">
                        <label>Geran:</label>
                        <div class="filter-buttons" id="geran-filter">
                            <button class="filter-btn active" data-value="">All</button>
                            <button class="filter-btn" data-value="NT">NT</button>
                            <button class="filter-btn" data-value="CL">CL</button>
                        </div>
                    </div>
                    <div class="filter-group">
                        <label>Status:</label>
                        <div class="filter-buttons" id="dev-status-filter">
                            <button class="filter-btn active" data-value="">All</button>
                            <button class="filter-btn" data-value="planned">Planned</button>
                            <button class="filter-btn" data-value="in_progress">In Progress</button>
                            <button class="filter-btn" data-value="completed">Completed</button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="sidebar-listings" id="map-sidebar-listings">
                <div class="sidebar-loading">
                    <div class="spinner"></div>
                    <span>Memuatkan hartanah...</span>
                </div>
            </div>
        </div>

        <div class="map-portal-main">
            <div id="map-skeleton" class="skeleton-box" style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; z-index: 10;"></div>
            <?php echo do_shortcode('[tlsmap height="100%" width="100%"]'); ?>
            
            <div class="mobile-map-toggle">
                <button onclick="toggleMobilePortalView()" id="portal-view-btn">
                    <i class="fas fa-list"></i> Senarai
                </button>
            </div>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- RECENT LISTINGS -->
<?php if (!is_plugin_active('tlsmap/tlsmap.php')): ?>
<section class="listings-section" id="property-listings">
    <div class="container">
        <div class="section-header">
            <h2>Semua <span class="highlight">Hartanah</span></h2>
            <p>Cari dan teroka lot tanah yang tersedia menggunakan carian di bawah.</p>
        </div>

        <div class="property-search-bar">
            <div class="search-input-wrap">
                <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                <input type="text" id="property-search" placeholder="Cari nama hartanah, no. geran, daerah, atau lokasi...">
            </div>
            <div class="search-filters">
                <select id="filter-status">
                    <option value="">Semua Status</option>
                    <option value="available">Available</option>
                    <option value="reserved">Reserved</option>
                    <option value="sold">Sold</option>
                </select>
                <select id="filter-geran">
                    <option value="">Semua Geran</option>
                    <option value="CL">CL</option>
                    <option value="NT">NT</option>
                    <option value="Hakmilik">Hakmilik</option>
                </select>
            </div>
            <div class="search-results-info">
                <span id="listing-results-count">0</span> hartanah dijumpai
            </div>
        </div>

        <div class="listings-grid" id="listings-grid">
            <?php
            $tanah_query = new WP_Query([
                'post_type' => 'tanah',
                'posts_per_page' => -1,
                'post_status' => 'publish'
            ]);
            
            if ($tanah_query->have_posts()) :
                while ($tanah_query->have_posts()) : $tanah_query->the_post();
                    $harga = get_post_meta(get_the_ID(), '_tanah_harga', true) ?: 0;
                    $ekar = get_post_meta(get_the_ID(), '_tanah_keluasan', true) ?: 0;
                    $geran = get_post_meta(get_the_ID(), '_tanah_jenis_geran', true) ?: 'CL';
                    $status = strtolower(get_post_meta(get_the_ID(), '_tanah_status', true) ?: 'available');
                    $thumbnail = get_the_post_thumbnail_url(get_the_ID(), 'medium') ?: 'https://tanahlotsabah.com/wp-content/themes/tlstheme/assets/images/placeholder.jpeg';
            ?>
                <div class="listing-card" data-title="<?php echo esc_attr(strtolower(get_the_title())); ?>" data-status="<?php echo esc_attr($status); ?>" data-geran="<?php echo esc_attr($geran); ?>">
                    <div class="listing-image">
                        <img src="<?php echo esc_url($thumbnail); ?>" alt="<?php echo esc_attr(get_the_title()); ?>">
                        <span class="listing-status <?php echo esc_attr($status); ?>"><?php echo ucfirst($status); ?></span>
                    </div>
                    <div class="listing-content">
                        <h3><?php the_title(); ?></h3>
                        <div class="listing-price">RM <?php echo number_format($harga); ?></div>
                        <div class="listing-meta">
                            <span><i class="fas fa-expand"></i> <?php echo $ekar; ?> ekar</span>
                            <span><i class="fas fa-file-alt"></i> <?php echo $geran; ?></span>
                        </div>
                        <a href="<?php the_permalink(); ?>" class="listing-btn">Lihat Detail</a>
                    </div>
                </div>
            <?php 
                endwhile;
                wp_reset_postdata();
            endif;
            ?>
        </div>
    </div>
</section>

<script>
(function() {
    var searchInput = document.getElementById('property-search');
    var filterStatus = document.getElementById('filter-status');
    var filterGeran = document.getElementById('filter-geran');
    var listingsGrid = document.getElementById('listings-grid');
    var resultsCount = document.getElementById('listing-results-count');

    if (!searchInput || !listingsGrid || !resultsCount) return;

    var cards = listingsGrid.querySelectorAll('.listing-card');

    function updateResultsCount(visibleCount) {
        resultsCount.textContent = visibleCount;
    }

    function filterCards() {
        var query = searchInput.value.toLowerCase().trim();
        var statusVal = filterStatus ? filterStatus.value : '';
        var geranVal = filterGeran ? filterGeran.value : '';
        var visibleCount = 0;

        cards.forEach(function(card) {
            var title = (card.dataset.title || '').toLowerCase();
            var status = (card.dataset.status || '').toLowerCase();
            var geran = (card.dataset.geran || '').toLowerCase();
            
            var matchesSearch = query === '' || title.includes(query);
            var matchesStatus = statusVal === '' || status === statusVal;
            var matchesGeran = geranVal === '' || geran === geranVal;
            
            if (matchesSearch && matchesStatus && matchesGeran) {
                card.style.display = '';
                visibleCount++;
            } else {
                card.style.display = 'none';
            }
        });

        updateResultsCount(visibleCount);
    }

    searchInput.addEventListener('input', filterCards);
    if (filterStatus) filterStatus.addEventListener('change', filterCards);
    if (filterGeran) filterGeran.addEventListener('change', filterCards);

    updateResultsCount(cards.length);
})();
</script>
<?php endif; ?>

<!-- CALL TO ACTION -->
<section class="cta-section">
    <div class="container">
        <div class="cta-content">
            <h2>Berjumpa dengan Pasukan Kami</h2>
            <p>Hubungi kami untuk konsultasi percuma dan panduan lengkap tentang hartanah tanah di Sabah.</p>
            <?php 
            $wa = get_option('tls_wa_number', '60123456789');
            $phone = get_option('tls_contact_phone', '+60123456789');
            ?>
            <div class="cta-buttons">
                <a href="https://wa.me/<?php echo esc_attr($wa); ?>" class="btn-large btn-whatsapp" target="_blank">
                    <i class="fab fa-whatsapp"></i> WhatsApp Sekarang
                </a>
                <a href="tel:+<?php echo esc_attr($phone); ?>" class="btn-large btn-outline-white">
                    <i class="fas fa-phone"></i> Hubungi Kami
                </a>
            </div>
        </div>
    </div>
</section>

<!-- NEWS SECTION -->
<section class="news-section" id="news-section">
    <div class="container">
        <div class="section-header">
            <h2>Panduan & <span class="highlight">Berita</span></h2>
            <p>Maklumat terkini tentang hartanah, pelaburan dan pembangunan tanah di Sabah.</p>
        </div>
        
        <div class="news-grid" id="newsGrid" data-page="1">
            <?php 
            $posts_per_page = 3;
            $news_query = new WP_Query([
                'post_type' => 'post',
                'posts_per_page' => $posts_per_page,
                'post_status' => 'publish',
                'orderby' => 'date',
                'order' => 'DESC'
            ]);
            
            if ($news_query->have_posts()) : 
                while ($news_query->have_posts()) : $news_query->the_post();
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
                                <img src="https://images.unsplash.com/photo-1560518883-ce09059eeffa?w=500&h=300&fit=crop" alt="Sabah Land News">
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
                endwhile;
                wp_reset_postdata();
            else : ?>
                <div class="no-news">
                    <p>Belum ada berita. Berita pertama akan tersedia tidak lama lagi.</p>
                </div>
            <?php endif; ?>
        </div>
        
        <div class="news-cta">
            <?php if ($news_query->found_posts > $posts_per_page): ?>
                <button id="showMoreNews" class="btn-show-more">
                    <i class="material-icons">expand_more</i>
                    <span class="btn-text">Lihat Lebih Banyak</span>
                    <span class="btn-loading" style="display:none;">Loading...</span>
                </button>
            <?php endif; ?>
            <a href="<?php echo home_url('/news/'); ?>" class="btn-outline">
                <i class="material-icons" style="font-size:18px;">library_books</i>
                Semua Berita
            </a>
        </div>
    </div>
</section>

<?php get_footer(); ?>