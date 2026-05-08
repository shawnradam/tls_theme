<?php get_header(); ?>

<!-- HERO SECTION -->
<?php 
$hero_videos = tls_get_hero_videos();
$main_video = !empty($hero_videos) ? $hero_videos[0] : null;

if (!$main_video) {
    $main_video = get_posts(['post_type' => 'hero_video', 'posts_per_page' => 1, 'post_status' => 'any']);
    $main_video = !empty($main_video) ? $main_video[0] : null;
}

$video_url = $main_video ? get_post_meta($main_video->ID, 'hero_video_url', true) : '';
?>
<section class="hero" id="home">
    <div class="hero-media">
        <?php if ($video_url): ?>
            <iframe src="<?php echo esc_url($video_url); ?>?autoplay=1&mute=1&loop=1&playlist=<?php echo str_replace('https://www.youtube.com/embed/', '', $video_url); ?>&controls=0&showinfo=0&rel=0&modestbranding=1" frameborder="0" allow="autoplay; encrypted-media"></iframe>
        <?php else: ?>
            <img src="<?php echo get_template_directory_uri(); ?>/assets/images/placeholder.jpeg" alt="Hero Background" class="hero-bg-image">
        <?php endif; ?>
    </div>
    <div class="hero-overlay"></div>
    <div class="hero-content">
        <div class="hero-badge">
            <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
            Hartanah Pilihan di Sabah
        </div>
        <h1>Miliki Warisan <span class="highlight">Tanah Sabah</span> Kita</h1>
        <p class="hero-subtitle">Terokai lot tanah premium dengan sempadan yang disahkan melalui sistem peta interaktif kami.</p>
        
        <div class="hero-actions">
            <a href="#" onclick="scrollToMap(event)" class="btn-hero-primary">
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

<!-- INTEGRATED LAND MAP PORTAL (THE NEW MAP) -->
<section class="tls-map-portal-section" id="map-portal">
    <div class="map-portal-container">
        <!-- Sidebar - PHP Generated Properties -->
        <div class="map-portal-sidebar">
            <div class="sidebar-header">
                <h3>Hartanah di Kawasan Ini</h3>
                <div class="results-count">
                    <?php
                    $sidebar_query = new WP_Query(['post_type' => 'tanah', 'posts_per_page' => -1, 'post_status' => 'publish']);
                    $total_count = $sidebar_query->found_posts;
                    echo '<span id="map-results-count">' . $total_count . '</span> hartanah dijumpai';
                    ?>
                </div>
            </div>
            <div class="sidebar-filters">
                <div class="search-box-minimal">
                    <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    <input type="text" id="map-sidebar-search" placeholder="Cari nama, geran, daerah..." onkeyup="filterMapProperties()">
                </div>
            </div>
            <div class="sidebar-listings" id="map-sidebar-listings">
                <?php if ($sidebar_query->have_posts()) : ?>
                    <?php while ($sidebar_query->have_posts()) : $sidebar_query->the_post();
                        $price = get_post_meta(get_the_ID(), '_tanah_harga', true);
                        $ekar = get_post_meta(get_the_ID(), '_tanah_keluasan', true);
                        $geran = get_post_meta(get_the_ID(), '_tanah_jenis_geran', true);
                        $thumbnail = get_the_post_thumbnail_url(get_the_ID(), 'medium') ?: get_template_directory_uri() . '/assets/images/placeholder.jpeg';
                        $status = get_post_meta(get_the_ID(), '_tanah_status', true) ?: 'available';
                    ?>
                    <div class="portal-listing-card" data-id="<?php the_ID(); ?>" data-name="<?php echo strtolower(get_the_title()); ?>">
                        <img src="<?php echo esc_url($thumbnail); ?>" class="portal-card-thumb" alt="<?php the_title(); ?>">
                        <div class="portal-card-info">
                            <div class="portal-card-top">
                                <span class="portal-card-status <?php echo $status; ?>"><?php echo ucfirst($status); ?></span>
                            </div>
                            <h4 class="portal-card-title"><?php the_title(); ?></h4>
                            <div class="portal-card-price">RM <?php echo number_format((int)$price); ?></div>
                            <div class="portal-card-meta"><?php echo $ekar; ?> ekar • <?php echo $geran; ?></div>
                            <a href="<?php the_permalink(); ?>" class="btn-detail" style="margin-top: 8px; display: inline-block;">View Details</a>
                        </div>
                    </div>
                    <?php endwhile; wp_reset_postdata(); ?>
                <?php else : ?>
                    <p style="text-align: center; padding: 20px;">Tiada hartanah dijumpai.</p>
                <?php endif; ?>
            </div>
        </div>

        <!-- Map Area -->
        <div class="map-portal-main">
            <div id="map-skeleton" class="skeleton-box" style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; z-index: 10; display: none;"></div>
            <?php echo do_shortcode('[tlsmap height="100%" width="100%"]'); ?>
            
            <!-- Mobile Toggle -->
            <div class="mobile-map-toggle">
                <button onclick="toggleMobilePortalView()" id="portal-view-btn">
                    <i class="fas fa-list"></i> Lihat Senarai
                </button>
            </div>
        </div>
    </div>
</section>

<script>
function filterMapProperties() {
    var input = document.getElementById('map-sidebar-search');
    var filter = input.value.toLowerCase();
    var cards = document.querySelectorAll('.portal-listing-card[data-name]');
    var count = 0;
    
    cards.forEach(function(card) {
        var name = card.getAttribute('data-name');
        if (name.includes(filter)) {
            card.style.display = '';
            count++;
        } else {
            card.style.display = 'none';
        }
    });
    
    document.getElementById('map-results-count').textContent = count;
}
</script>

<!-- RECENT LISTINGS (All Properties Below Map) -->
<section class="listings-section" id="property-listings">
    <div class="container">
        <div class="section-header">
            <h2>Semua <span class="highlight">Hartanah</span></h2>
            <p>Cari dan teroka lot tanah yang tersedia menggunakan carian di bawah.</p>
        </div>

        <!-- Search & Filter Bar -->
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
                    get_template_part('template-parts/card', 'land');
                endwhile;
                wp_reset_postdata();
            else :
                echo '<p style="text-align:center; grid-column: 1/-1;">Tiada hartanah dijumpai.</p>';
            endif;
            ?>
        </div>
    </div>
</section>

<!-- CTA SECTION -->
<section class="cta-section-modern">
    <div class="cta-bg-pattern"></div>
    <div class="container">
        <div class="cta-content-modern">
            <div class="cta-icon">
                <i class="fas fa-comments"></i>
            </div>
            <h2>Bersedia Untuk Melabur?</h2>
            <p>Hubungi ejen kami untuk konsultasi percuma, lawatan tapak, atau maklumat lanjut mengenai tanah yang anda minati di seluruh Sabah.</p>
            <div class="cta-buttons">
                <?php
                $wa = get_theme_mod('whatsapp_number', '601126661706');
                $phone = get_theme_mod('phone_number', $wa);
                ?>
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

<script>
// Global variable to store selected property for mobile map view
let selectedProperty = null;

function toggleMobilePortalView() {
    const sidebar = document.querySelector('.map-portal-sidebar');
    const btn = document.getElementById('portal-view-btn');
    if (!sidebar || !btn) {
        return;
    }
    
    const isShowing = sidebar.classList.toggle('show');
    
    if (isShowing) {
        btn.innerHTML = '<i class="fas fa-map"></i> Lihat Peta';
        const mapSection = document.getElementById('map-portal');
        if (mapSection) {
            mapSection.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
    } else {
        btn.innerHTML = '<i class="fas fa-list"></i> Lihat Senarai';
        setTimeout(function() {
            if (window.tlsMap) {
                window.tlsMap.invalidateSize();
                if (window.selectedProperty && window.selectedProperty.lat && window.selectedProperty.lng) {
                    window.tlsMap.setView([window.selectedProperty.lat, window.selectedProperty.lng], 16);
                } else if (selectedProperty && selectedProperty.lat && selectedProperty.lng) {
                    window.tlsMap.setView([selectedProperty.lat, selectedProperty.lng], 16);
                } else {
                    window.tlsMap.setView([6.13, 116.23], 12);
                }
            }
        }, 400);
    }
}

// Property search and filter for listings below map
(function() {
    var searchInput = document.getElementById('property-search');
    var filterStatus = document.getElementById('filter-status');
    var filterGeran = document.getElementById('filter-geran');
    var listingsGrid = document.getElementById('listings-grid');
    var resultsCount = document.getElementById('listing-results-count');

    if (!searchInput || !listingsGrid || !resultsCount) return;

    var cards = listingsGrid.querySelectorAll('.listing-card');
    var sidebarSearch = document.getElementById('map-sidebar-search');

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
            var geran = (card.dataset.geran || '').toLowerCase();
            var location = (card.dataset.location || '').toLowerCase();
            var daerah = (card.dataset.daerah || '').toLowerCase();
            var town = (card.dataset.town || '').toLowerCase();
            var geranDisplay = (card.dataset.geranDisplay || '').toLowerCase();
            var price = (card.dataset.price || '').toLowerCase();

            var searchText = title + ' ' + location + ' ' + daerah + ' ' + town + ' ' + geranDisplay + ' ' + price;

            var geranMatch = !geranVal || geran === geranVal.toLowerCase();
            var textMatch = !query || searchText.indexOf(query) !== -1;

            if (textMatch && geranMatch) {
                card.style.display = '';
                visibleCount++;
            } else {
                card.style.display = 'none';
            }
        });

        updateResultsCount(visibleCount);

        if (sidebarSearch) {
            sidebarSearch.value = query;
            sidebarSearch.dispatchEvent(new Event('input'));
        }
    }

    searchInput.addEventListener('input', filterCards);
    if (filterStatus) filterStatus.addEventListener('change', filterCards);
    if (filterGeran) filterGeran.addEventListener('change', filterCards);

    updateResultsCount(cards.length);
})();

// Scroll to Map Function for Hero Button
function scrollToMap(event) {
    if (event) event.preventDefault();
    const mapSection = document.getElementById('map-portal');
    if (mapSection) {
        mapSection.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }
}
</script>

<?php get_footer(); ?>
