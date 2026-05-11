<?php 
get_header();

// Add body class to disable fullscreen footer (normal scroll behavior)
add_filter('body_class', function($classes) {
    $classes[] = 'disable-fullscreen-footer';
    return $classes;
});

// Include WordPress plugin functions for is_plugin_active()
include_once(ABSPATH . 'wp-admin/includes/plugin.php');
?>

<?php while (have_posts()) : the_post();
    $harga = get_post_meta(get_the_ID(), '_tanah_harga', true) ?: 0;
    $ekar = get_post_meta(get_the_ID(), '_tanah_keluasan', true) ?: 0;
    $geran = get_post_meta(get_the_ID(), '_tanah_jenis_geran', true) ?: 'CL';
    $zoning = get_post_meta(get_the_ID(), '_tanah_zoning', true) ?: 'Kediaman';
    $sqft = $ekar * 43560;
    $psf = $sqft > 0 ? $harga / $sqft : 0;

    $property_id = get_post_meta(get_the_ID(), '_tanah_property_id', true) ?: '';
    $building_size = get_post_meta(get_the_ID(), '_tanah_building_size', true) ?: 0;
    $building_unit = get_post_meta(get_the_ID(), '_tanah_building_unit', true) ?: 'sqft';
    $verified = get_post_meta(get_the_ID(), '_tanah_verified', true) ?: 0;
    $video_url = get_post_meta(get_the_ID(), '_tanah_video_url', true) ?: '';
    $virtual_tour_url = get_post_meta(get_the_ID(), '_tanah_virtual_tour_url', true) ?: '';

    // Location data for map
    $lat = get_post_meta(get_the_ID(), '_tanah_latitude', true);
    $lng = get_post_meta(get_the_ID(), '_tanah_longitude', true);
    $boundary = get_post_meta(get_the_ID(), '_tanah_boundary', true);

    $daerah_terms = get_the_terms(get_the_ID(), 'daerah');
    $daerah = $daerah_terms && !is_wp_error($daerah_terms) ? $daerah_terms[0]->name : '';
    $town = get_post_meta(get_the_ID(), '_tanah_town', true) ?: '';
    $location = $town ?: ($daerah ?: 'Sabah');

    $wa = get_theme_mod('whatsapp_number', '60123456789');
    $phone = get_theme_mod('phone_number', $wa);
    $msg = urlencode("Saya berminat dengan " . get_the_title() . " RM" . number_format((int)$harga) . " - " . get_permalink());

    $geran_labels = ['CL' => 'CL', 'NT' => 'NT', 'Development' => 'Development Phase', 'Hakmilik' => 'Freehold'];
    $geran_display = $geran_labels[$geran] ?? $geran;
    
    $geran_class = 'badge-' . strtolower($geran);
    if ($geran === 'Hakmilik') $geran_class = 'badge-fh';
    if ($geran === 'Development') $geran_class = 'badge-development';
?>

<section class="section">
    <div class="container">
        <div class="single-tanah-layout">
            <!-- Main Content -->
            <div class="single-tanah-main">
                <!-- Image Gallery -->
                <?php
                $gallery = get_post_meta(get_the_ID(), '_tanah_gallery', true);
                $gallery_ids = $gallery ? explode(',', $gallery) : [];

                // If no gallery, use featured image
                if (empty($gallery_ids)) {
                    if (has_post_thumbnail()) {
                        $gallery_ids = [get_post_thumbnail_id()];
                    }
                }
                ?>

                <div class="single-tanah-gallery-wrapper">
                    <?php if (!empty($gallery_ids)): ?>
                    <div class="tls-gallery-slider">
                        <div class="tls-gallery-main">
                            <?php foreach ($gallery_ids as $img_id): ?>
                                <?php if ($img_id): $img_url = wp_get_attachment_image_url($img_id, 'large'); ?>
                                <div class="tls-gallery-slide">
                                    <img src="<?php echo esc_url($img_url); ?>" alt="<?php the_title_attribute(); ?>">
                                </div>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </div>

                        <?php if (count($gallery_ids) > 1): ?>
                        <!-- Navigation -->
                        <button class="tls-gallery-nav tls-gallery-prev" aria-label="Previous">‹</button>
                        <button class="tls-gallery-nav tls-gallery-next" aria-label="Next">›</button>

                        <!-- Counter -->
                        <div class="tls-gallery-counter">
                            <span class="current">1</span> / <span class="total"><?php echo count($gallery_ids); ?></span>
                        </div>

                        <!-- Thumbnails -->
                        <div class="tls-gallery-thumbs">
                            <?php foreach ($gallery_ids as $index => $img_id): ?>
                                <?php if ($img_id): $thumb_url = wp_get_attachment_image_url($img_id, 'thumbnail'); ?>
                                <div class="tls-gallery-thumb <?php echo $index === 0 ? 'active' : ''; ?>" data-index="<?php echo $index; ?>">
                                    <img src="<?php echo esc_url($thumb_url); ?>" alt="Thumbnail <?php echo $index + 1; ?>">
                                </div>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </div>
                        <?php endif; ?>
                    </div>
                    <?php else: ?>
                    <!-- Fallback placeholder -->
                    <div class="single-tanah-image">
                        <img src="<?php echo get_template_directory_uri(); ?>/assets/images/placeholder.jpeg" alt="<?php the_title_attribute(); ?>">
                    </div>
                    <?php endif; ?>

                    <div class="single-tanah-badges">
                        <?php if ($verified): ?>
                        <span class="badge badge-verified">✓ Verified</span>
                        <?php endif; ?>
                        <span class="badge <?php echo $geran_class; ?>"><?php echo esc_html($geran); ?></span>
                        <span class="badge badge-zoning"><?php echo esc_html($zoning); ?></span>
                    </div>

                    <!-- Image Action Buttons -->
                    <div class="image-action-buttons">
                        <?php if (!empty($lat) && !empty($lng) || !empty($boundary)): ?>
                        <button class="peta-lot-button" onclick="scrollToMap()" style="background:#16a34a; color:white;">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                            Peta Tersedia
                        </button>
                        <?php else: ?>
                        <button class="peta-lot-button" disabled style="opacity: 0.5; cursor: not-allowed; background:#dc3545; color:white;">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                            Peta Tidak Tersedia
                        </button>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Content -->
                <div class="single-tanah-content">
                    <?php if ($property_id): ?>
                    <div class="single-property-id">Property ID: <?php echo esc_html($property_id); ?></div>
                    <?php endif; ?>
                    
                    <h1 class="single-tanah-title"><?php the_title(); ?></h1>
                    <?php if (function_exists('tls_get_property_views')) echo tls_get_property_views(); ?>
                    
                    <div class="single-tanah-meta">
                        <span><?php echo esc_html($location); ?></span>
                        <span class="separator">|</span>
                        <span>Geran <?php echo esc_html($geran); ?></span>
                        <span class="separator">|</span>
                        <span>Zoning <?php echo esc_html($zoning); ?></span>
                    </div>
                    
                    <?php if ($video_url): ?>
                    <div class="single-tanah-video">
                        <h3>Video</h3>
                        <div class="video-container">
                            <iframe src="<?php echo esc_attr($video_url); ?>" frameborder="0" allowfullscreen></iframe>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <?php if ($virtual_tour_url): ?>
                    <div class="single-tanah-vtour">
                        <h3>360° Virtual Tour</h3>
                        <div class="vtour-container">
                            <iframe src="<?php echo esc_attr($virtual_tour_url); ?>" frameborder="0" allowfullscreen></iframe>
                        </div>
                    </div>
                    <?php endif; ?>

                    <?php
                    $floor_plans_json = get_post_meta(get_the_ID(), '_tanah_floor_plans', true);
                    $floor_plans = $floor_plans_json ? json_decode($floor_plans_json, true) : [];
                    if (!empty($floor_plans)):
                    ?>
                    <div class="single-tanah-floor-plans">
                        <h3>Floor Plans / Layout</h3>
                        <div class="floor-plans-grid">
                            <?php foreach ($floor_plans as $plan): ?>
                                <?php if (!empty($plan['title'])): ?>
                                <div class="floor-plan-card">
                                    <div class="floor-plan-header">
                                        <h4><?php echo esc_html($plan['title']); ?></h4>
                                        <?php if (!empty($plan['size'])): ?>
                                        <span class="floor-plan-size"><?php echo esc_html(number_format($plan['size'])); ?> sqft</span>
                                        <?php endif; ?>
                                    </div>
                                    <?php if (!empty($plan['description'])): ?>
                                    <p class="floor-plan-desc"><?php echo esc_html($plan['description']); ?></p>
                                    <?php endif; ?>
                                </div>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php endif; ?>

                    <div class="single-tanah-desc">
                        <?php the_content(); ?>
                    </div>

                    <!-- Document Preview Section -->
                    <?php
                    $geran_available = get_post_meta(get_the_ID(), '_tanah_doc_geran_available', true);
                    $geran_file = get_post_meta(get_the_ID(), '_tanah_doc_geran_file', true);

                    $pelan_available = get_post_meta(get_the_ID(), '_tanah_doc_pelan_available', true);
                    $pelan_file = get_post_meta(get_the_ID(), '_tanah_doc_pelan_file', true);

                    $search_available = get_post_meta(get_the_ID(), '_tanah_doc_search_available', true);
                    $search_file = get_post_meta(get_the_ID(), '_tanah_doc_search_file', true);

                    $has_any_doc = ($geran_available == '1') || ($pelan_available == '1') || ($search_available == '1');

                    if ($has_any_doc):
                    ?>
                    <div class="dokumen-section">
                        <h3>Dokumen Tersedia</h3>
                        <div class="dokumen-grid">
                            <?php if ($geran_available == '1'): ?>
                            <div class="dokumen-card">
                                <div class="dokumen-icon">📄</div>
                                <div class="dokumen-name">Geran (Title)</div>
                                <div class="dokumen-status">✓ Available</div>
                                <?php if ($geran_file): ?>
                                    <a href="<?php echo esc_url($geran_file); ?>" target="_blank" class="doc-view-link">View PDF</a>
                                <?php else: ?>
                                    <button type="button" class="doc-request-btn" onclick="requestDocument('geran', <?php echo get_the_ID(); ?>)">Request Document</button>
                                <?php endif; ?>
                            </div>
                            <?php endif; ?>

                            <?php if ($pelan_available == '1'): ?>
                            <div class="dokumen-card">
                                <div class="dokumen-icon">🗺️</div>
                                <div class="dokumen-name">Pelan Tapak</div>
                                <div class="dokumen-status">✓ Available</div>
                                <?php if ($pelan_file): ?>
                                    <a href="<?php echo esc_url($pelan_file); ?>" target="_blank" class="doc-view-link">View PDF</a>
                                <?php else: ?>
                                    <button type="button" class="doc-request-btn" onclick="requestDocument('pelan', <?php echo get_the_ID(); ?>)">Request Document</button>
                                <?php endif; ?>
                            </div>
                            <?php endif; ?>

                            <?php if ($search_available == '1'): ?>
                            <div class="dokumen-card">
                                <div class="dokumen-icon">◈</div>
                                <div class="dokumen-name">Official Search</div>
                                <div class="dokumen-status">✓ Available</div>
                                <?php if ($search_file): ?>
                                    <a href="<?php echo esc_url($search_file); ?>" target="_blank" class="doc-view-link">View PDF</a>
                                <?php else: ?>
                                    <button type="button" class="doc-request-btn" onclick="requestDocument('search', <?php echo get_the_ID(); ?>)">Request Document</button>
                                <?php endif; ?>
                            </div>
                            <?php endif; ?>
                        </div>

                        <?php if (!$geran_file || !$pelan_file || !$search_file): ?>
                        <center>
                            <?php 
                            $wa_doc = get_theme_mod('whatsapp_number', '60123456789');
                            $wa_doc_text = "Saya ingin melihat dokumen untuk Property ID: " . $property_id;
                            $wa_doc_full = $wa_doc . "?text=" . urlencode($wa_doc_text);
                            $wa_doc_enc = base64_encode($wa_doc_full);
                            ?>
                            <a href="javascript:void(0)" onclick="tlsRevealContact(this, 'wa_full', '<?php echo $wa_doc_enc; ?>')" class="btn-dokumen">
                                <svg width="20" height="20" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z"/>
                                </svg>
                                Minta Dokumen (WhatsApp)
                            </a>
                        </center>
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Sidebar -->
            <aside class="single-tanah-sidebar">
                <div class="single-tanah-card">
                    <?php if ($harga > 0): ?>
                        <div class="single-tanah-price">RM <?php echo number_format((int)$harga); ?></div>
                        <div class="single-tanah-psf">RM <?php echo number_format($psf, 2); ?>/sqft</div>
                    <?php endif; ?>

                    <!-- Check Eligibility for NT Properties -->
                    <?php if ($geran === 'NT'): ?>
                    <div class="check-eligibility-box">
                        <h4>Semak Kelayakan</h4>
                        <div class="eligibility-buttons">
                            <button class="eligibility-btn" onclick="checkEligibility('yes')">
                                Saya Bumiputera Sabah
                            </button>
                            <button class="eligibility-btn" onclick="checkEligibility('no')">
                                Bukan Bumiputera
                            </button>
                        </div>
                        <div class="eligibility-message" id="eligibilityMessage">
                            Tanah NT hanya untuk Bumiputera Sabah. <a href="<?php echo esc_url(home_url('/tanah/?geran=CL')); ?>">Lihat senarai Tanah CL kami di sini</a>.
                        </div>
                    </div>
                    <?php endif; ?>

                    <div class="single-tanah-specs">
                        <?php if ($property_id): ?>
                        <div class="spec-item">
                            <span class="spec-label">Property ID</span>
                            <span class="spec-value"><?php echo esc_html($property_id); ?></span>
                        </div>
                        <?php endif; ?>
                        <div class="spec-item">
                            <span class="spec-label">Keluasan Tanah</span>
                            <span class="spec-value"><?php echo esc_html($ekar); ?> ekar (<?php echo number_format($sqft, 0); ?> sqft)</span>
                        </div>
                        <?php if ($building_size > 0): ?>
                        <div class="spec-item">
                            <span class="spec-label">Building Size</span>
                            <span class="spec-value"><?php echo number_format($building_size, 0); ?> <?php echo esc_html($building_unit); ?></span>
                        </div>
                        <?php endif; ?>
                        <div class="spec-item">
                            <span class="spec-label">Jenis Geran</span>
                            <span class="spec-value"><?php echo esc_html($geran); ?></span>
                        </div>
                        <div class="spec-item">
                            <span class="spec-label">Daerah/Bandar</span>
                            <span class="spec-value"><?php echo esc_html($location); ?></span>
                        </div>
                        <div class="spec-item">
                            <span class="spec-label">Zoning</span>
                            <span class="spec-value"><?php echo esc_html($zoning); ?></span>
                        </div>
                        <?php if ($verified): ?>
                        <div class="spec-item">
                            <span class="spec-label">Status</span>
                            <span class="spec-value verified-badge">Verified ?</span>
                        </div>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Advertisement Slot: Local Partner -->
                    <div class="ad-partner-slot">
                        <div class="ad-label">Partner Tempatan</div>
                        <div class="ad-content">
                            <p>Mau bersihkan tanah atau semak sempadan? <a href="#">Hubungi Juruukur Berdaftar</a></p>
                        </div>
                    </div>
                    
                    <!-- DUAL CALCULATOR SYSTEM -->
                    <div class="calculator-box" id="calculatorBox">
                        <div class="calculator-header" onclick="toggleCalculator()">
                            <span>▪ Kalkulator Kos</span>
                            <span class="calculator-toggle">▼</span>
                        </div>
                        <div class="calculator-content">
                            <!-- Calculator Tabs -->
                            <div class="calc-tabs">
                                <button class="calc-tab active" onclick="switchCalcTab('purchase')">
                                    ◆ Kos Pembelian
                                </button>
                                <button class="calc-tab" onclick="switchCalcTab('development')">
                                    ▣ Kos Pembangunan
                                </button>
                            </div>

                            <!-- TAB 1: Purchase Costs (MOT/Stamp Duty) -->
                            <div class="calc-tab-content" id="purchaseCalc">
                                <div class="calc-input-group">
                                    <label>Harga Tanah (RM)</label>
                                    <input type="number" class="calc-input" id="calcPrice" value="<?php echo (int)$harga; ?>" oninput="calculateCosts()">
                                </div>
                                <div class="calc-results">
                                    <div class="calc-row">
                                        <span class="calc-label">Stamp Duty (Duti Setem):</span>
                                        <span class="calc-value" id="stampDuty">RM 0</span>
                                    </div>
                                    <div class="calc-row">
                                        <span class="calc-label">Legal Fees (Yuran Guaman):</span>
                                        <span class="calc-value" id="legalFees">RM 0</span>
                                    </div>
                                    <div class="calc-row">
                                        <span class="calc-label">MOT & Others (Anggaran):</span>
                                        <span class="calc-value" id="motFees">RM 0</span>
                                    </div>
                                    <div class="calc-row">
                                        <span class="calc-label">JUMLAH ANGGARAN KOS:</span>
                                        <span class="calc-value" id="totalCost">RM 0</span>
                                    </div>
                                </div>
                            </div>

                            <!-- TAB 2: Development Costs (Construction Calculator) -->
                            <div class="calc-tab-content" id="developmentCalc" style="display: none;">
                                <p style="margin-bottom: 16px; color: #666; font-size: 0.9rem;">
                                    Kira anggaran kos untuk membangunkan tanah ini.
                                </p>
                                <a href="<?php echo esc_url(home_url('/calculator')); ?>" class="btn-full-calculator" target="_blank">
                                    ▣ Buka Kalkulator Pembangunan Penuh
                                </a>
                                <p style="margin-top: 12px; color: #999; font-size: 0.8rem; text-align: center;">
                                    Kalkulator lengkap dengan harga terkini untuk kerja pagar, penyediaan tapak, binaan & lebih lagi
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Back Link -->
                <a href="<?php echo esc_url(home_url('/')); ?>" class="back-link">
                    &larr; Kembali ke Senarai
                </a>
            </aside>
        </div>
    </div>
</section>

<!-- Property Location Map Section -->
<?php if (!empty($lat) && !empty($lng) || !empty($boundary)): ?>
<section class="property-map-section" id="propertyMapSection">
    <?php if (is_plugin_active('tlsmap/tlsmap.php')): ?>
    <div class="container">
        <h2 class="section-title" style="font-weight:300;">Lokasi Tanah</h2>
        <div style="position: relative; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 20px rgba(0,0,0,0.08);">
            <?php 
                $lat_attr = !empty($lat) ? esc_attr($lat) : '6.1300';
                $lng_attr = !empty($lng) ? esc_attr($lng) : '116.2300';
                echo do_shortcode('[tlsmap height="600px" lat="' . $lat_attr . '" lng="' . $lng_attr . '"]'); 
            ?>
        </div>
    </div>
    <?php endif; ?>
</section>

<style>
.property-map-section {
    padding: 60px 0;
    background: #f8f9fa;
}
.property-map-section .section-title {
    font-size: 1.75rem;
    font-weight: 300;
    color: #1a1a1a;
    margin-bottom: 24px;
    text-align: center;
}
</style>
<?php endif; ?>

<?php endwhile; ?>

<script>
// Property Gallery Slider
document.addEventListener('DOMContentLoaded', function() {
    const slider = document.querySelector('.tls-gallery-slider');
    if (!slider) return;

    const slides = slider.querySelectorAll('.tls-gallery-slide');
    const prevBtn = slider.querySelector('.tls-gallery-prev');
    const nextBtn = slider.querySelector('.tls-gallery-next');
    const counter = slider.querySelector('.tls-gallery-counter .current');
    const thumbs = slider.querySelectorAll('.tls-gallery-thumb');

    if (slides.length === 0) return;

    let currentIndex = 0;

    // Show first slide
    slides[0].classList.add('active');

    function goToSlide(index) {
        // Remove active from all
        slides.forEach(s => s.classList.remove('active'));
        thumbs.forEach(t => t.classList.remove('active'));

        // Add active to current
        slides[index].classList.add('active');
        if (thumbs[index]) {
            thumbs[index].classList.add('active');
        }

        // Update counter
        if (counter) {
            counter.textContent = index + 1;
        }

        currentIndex = index;
    }

    // Next/Prev buttons
    if (prevBtn) {
        prevBtn.addEventListener('click', function() {
            const newIndex = currentIndex === 0 ? slides.length - 1 : currentIndex - 1;
            goToSlide(newIndex);
        });
    }

    if (nextBtn) {
        nextBtn.addEventListener('click', function() {
            const newIndex = currentIndex === slides.length - 1 ? 0 : currentIndex + 1;
            goToSlide(newIndex);
        });
    }

    // Thumbnail clicks
    thumbs.forEach(function(thumb, index) {
        thumb.addEventListener('click', function() {
            goToSlide(index);
        });
    });

    // Keyboard navigation
    document.addEventListener('keydown', function(e) {
        if (e.key === 'ArrowLeft' && prevBtn) {
            prevBtn.click();
        } else if (e.key === 'ArrowRight' && nextBtn) {
            nextBtn.click();
        }
    });

    // Touch swipe support
    let touchStartX = 0;
    let touchEndX = 0;

    slider.addEventListener('touchstart', function(e) {
        touchStartX = e.changedTouches[0].screenX;
    });

    slider.addEventListener('touchend', function(e) {
        touchEndX = e.changedTouches[0].screenX;
        handleSwipe();
    });

    function handleSwipe() {
        if (touchEndX < touchStartX - 50 && nextBtn) {
            nextBtn.click();
        }
        if (touchEndX > touchStartX + 50 && prevBtn) {
            prevBtn.click();
        }
    }
});

// ============================================
// PROFESSIONAL FEATURES JAVASCRIPT
// ============================================

// 1. Peta Lot Button - Scroll to Map
function scrollToMap() {
    const mapSection = document.getElementById('propertyMapSection');
    if (mapSection) {
        mapSection.scrollIntoView({ behavior: 'smooth', block: 'start' });
        // Highlight the map briefly
        setTimeout(function() {
            var mapEl = document.getElementById('property-map');
            if (mapEl) {
                mapEl.style.boxShadow = '0 0 0 4px #3b82f6';
                setTimeout(function() {
                    mapEl.style.boxShadow = '0 4px 6px rgba(0,0,0,0.1)';
                }, 1500);
            }
        }, 500);
    }
}

// 2. Check Eligibility NT
function checkEligibility(choice) {
    const message = document.getElementById('eligibilityMessage');
    const buttons = document.querySelectorAll('.eligibility-btn');

    // Remove active class from all buttons
    buttons.forEach(btn => btn.classList.remove('active'));

    if (choice === 'yes') {
        // User is Bumiputera - hide message and mark button
        message.classList.remove('show');
        buttons[0].classList.add('active');
    } else {
        // User is NOT Bumiputera - show message
        message.classList.add('show');
        buttons[1].classList.add('active');
    }
}

// 3. Toggle Calculator
function toggleCalculator() {
    const box = document.getElementById('calculatorBox');
    box.classList.toggle('active');

    // Calculate on first open
    if (box.classList.contains('active')) {
        calculateCosts();
    }
}

// 4. Calculate Costs - Sabah Land Calculator
<?php
// Fetch Purchase Calculator Settings from Database
$stamp_duty_rates = get_option('tls_stamp_duty_rates', [
    'tier1_threshold' => 100000, 'tier1_rate' => 1,
    'tier2_threshold' => 500000, 'tier2_rate' => 2,
    'tier3_threshold' => 1000000, 'tier3_rate' => 3,
    'tier4_rate' => 4
]);

$legal_fees_rates = get_option('tls_legal_fees_rates', [
    'tier1_threshold' => 150000, 'tier1_rate' => 1,
    'tier2_threshold' => 1000000, 'tier2_rate' => 0.7,
    'tier3_rate' => 0.6
]);

$mot_fees_rates = get_option('tls_mot_fees_rates', [
    'rate' => 0.5,
    'minimum' => 500
]);
?>
// Purchase Calculator Rates (from database)
const CALCULATOR_SETTINGS = {
    stampDuty: {
        tier1: { threshold: <?php echo $stamp_duty_rates['tier1_threshold']; ?>, rate: <?php echo $stamp_duty_rates['tier1_rate'] / 100; ?> },
        tier2: { threshold: <?php echo $stamp_duty_rates['tier2_threshold']; ?>, rate: <?php echo $stamp_duty_rates['tier2_rate'] / 100; ?> },
        tier3: { threshold: <?php echo $stamp_duty_rates['tier3_threshold']; ?>, rate: <?php echo $stamp_duty_rates['tier3_rate'] / 100; ?> },
        tier4: { rate: <?php echo $stamp_duty_rates['tier4_rate'] / 100; ?> }
    },
    legalFees: {
        tier1: { threshold: <?php echo $legal_fees_rates['tier1_threshold']; ?>, rate: <?php echo $legal_fees_rates['tier1_rate'] / 100; ?> },
        tier2: { threshold: <?php echo $legal_fees_rates['tier2_threshold']; ?>, rate: <?php echo $legal_fees_rates['tier2_rate'] / 100; ?> },
        tier3: { rate: <?php echo $legal_fees_rates['tier3_rate'] / 100; ?> }
    },
    motFees: {
        rate: <?php echo $mot_fees_rates['rate'] / 100; ?>,
        minimum: <?php echo $mot_fees_rates['minimum']; ?>
    }
};

function calculateCosts() {
    const price = parseFloat(document.getElementById('calcPrice').value) || 0;

    // STAMP DUTY CALCULATION (Using Database Settings)
    let stampDuty = 0;
    const sd = CALCULATOR_SETTINGS.stampDuty;

    if (price <= sd.tier1.threshold) {
        stampDuty = price * sd.tier1.rate;
    } else if (price <= sd.tier2.threshold) {
        const tier1Amount = sd.tier1.threshold * sd.tier1.rate;
        stampDuty = tier1Amount + ((price - sd.tier1.threshold) * sd.tier2.rate);
    } else if (price <= sd.tier3.threshold) {
        const tier1Amount = sd.tier1.threshold * sd.tier1.rate;
        const tier2Amount = (sd.tier2.threshold - sd.tier1.threshold) * sd.tier2.rate;
        stampDuty = tier1Amount + tier2Amount + ((price - sd.tier2.threshold) * sd.tier3.rate);
    } else {
        const tier1Amount = sd.tier1.threshold * sd.tier1.rate;
        const tier2Amount = (sd.tier2.threshold - sd.tier1.threshold) * sd.tier2.rate;
        const tier3Amount = (sd.tier3.threshold - sd.tier2.threshold) * sd.tier3.rate;
        stampDuty = tier1Amount + tier2Amount + tier3Amount + ((price - sd.tier3.threshold) * sd.tier4.rate);
    }

    // LEGAL FEES (Using Database Settings)
    let legalFees = 0;
    const lf = CALCULATOR_SETTINGS.legalFees;

    if (price <= lf.tier1.threshold) {
        legalFees = price * lf.tier1.rate;
    } else if (price <= lf.tier2.threshold) {
        const tier1Amount = lf.tier1.threshold * lf.tier1.rate;
        legalFees = tier1Amount + ((price - lf.tier1.threshold) * lf.tier2.rate);
    } else {
        const tier1Amount = lf.tier1.threshold * lf.tier1.rate;
        const tier2Amount = (lf.tier2.threshold - lf.tier1.threshold) * lf.tier2.rate;
        legalFees = tier1Amount + tier2Amount + ((price - lf.tier2.threshold) * lf.tier3.rate);
    }

    // MOT & Other Fees (Using Database Settings)
    const mf = CALCULATOR_SETTINGS.motFees;
    const motFees = Math.max(mf.minimum, price * mf.rate);

    // TOTAL
    const total = stampDuty + legalFees + motFees;

    // Update Display
    document.getElementById('stampDuty').textContent = 'RM ' + formatNumber(stampDuty);
    document.getElementById('legalFees').textContent = 'RM ' + formatNumber(legalFees);
    document.getElementById('motFees').textContent = 'RM ' + formatNumber(motFees);
    document.getElementById('totalCost').textContent = 'RM ' + formatNumber(total);
}

// Helper: Format number with commas
function formatNumber(num) {
    return Math.round(num).toLocaleString('en-MY');
}

// Initialize calculator on page load
document.addEventListener('DOMContentLoaded', function() {
    // Auto-calculate if calculator exists
    if (document.getElementById('calcPrice')) {
        calculateCosts();
    }
});

// 5. Switch Calculator Tabs
function switchCalcTab(tabName) {
    // Hide all tab contents
    document.querySelectorAll('.calc-tab-content').forEach(function(content) {
        content.style.display = 'none';
    });

    // Remove active class from all tabs
    document.querySelectorAll('.calc-tab').forEach(function(tab) {
        tab.classList.remove('active');
    });

    // Show selected tab
    if (tabName === 'purchase') {
        document.getElementById('purchaseCalc').style.display = 'block';
        document.querySelectorAll('.calc-tab')[0].classList.add('active');
    } else {
        document.getElementById('developmentCalc').style.display = 'block';
        document.querySelectorAll('.calc-tab')[1].classList.add('active');
    }
}

// Document Request Function
function requestDocument(docType, postId) {
    var button = event.target;
    var originalText = button.textContent;
    button.disabled = true;
    button.textContent = 'Sending...';
    button.style.opacity = '0.6';

    var formData = new FormData();
    formData.append('action', 'request_document');
    formData.append('post_id', postId);
    formData.append('doc_type', docType);

    fetch('<?php echo admin_url('admin-ajax.php'); ?>', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            button.textContent = '✓ Request Sent!';
            button.style.backgroundColor = '#10b981';
            button.style.color = '#fff';

            setTimeout(function() {
                button.textContent = originalText;
                button.disabled = false;
                button.style.opacity = '1';
                button.style.backgroundColor = '';
                button.style.color = '';
            }, 3000);

            alert('Your document request has been sent! The agent will contact you shortly.');
        } else {
            button.textContent = originalText;
            button.disabled = false;
            button.style.opacity = '1';
            alert('Error sending request. Please try WhatsApp instead.');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        button.textContent = originalText;
        button.disabled = false;
        button.style.opacity = '1';
        alert('Error sending request. Please try WhatsApp instead.');
    });
}
</script>

<?php get_footer(); ?>
