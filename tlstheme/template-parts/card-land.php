<?php 
$geran = get_post_meta(get_the_ID(), '_tanah_jenis_geran', true) ?: 'CL';
$verified = get_post_meta(get_the_ID(), '_tanah_verified', true) ?: 0;
$property_id = get_post_meta(get_the_ID(), '_tanah_property_id', true) ?: '';
$daerah_terms = get_the_terms(get_the_ID(), 'daerah');
$daerah = $daerah_terms && !is_wp_error($daerah_terms) ? $daerah_terms[0]->name : '';
$town = get_post_meta(get_the_ID(), '_tanah_town', true) ?: '';
$location = $town ?: ($daerah ?: 'Sabah');

$geran_labels = ['CL' => 'CL', 'NT' => 'NT', 'Development' => 'Phase', 'Hakmilik' => 'Freehold'];
$geran_display = $geran_labels[$geran] ?? $geran;

$harga = get_post_meta(get_the_ID(), '_tanah_harga', true) ?: 0;
$ekar = get_post_meta(get_the_ID(), '_tanah_keluasan', true) ?: 0;
$sqft = $ekar * 43560;
$psf = $sqft > 0 ? $harga / $sqft : 0;
?>

<div class="framer-card-container listing-card" 
    data-id="<?php echo get_the_ID(); ?>" 
    data-title="<?php echo esc_attr(get_the_title()); ?>"
    data-price="<?php echo esc_attr($harga); ?>"
    data-location="<?php echo esc_attr($location); ?>"
>
    <div class="framer-card-inner">
        <!-- Front -->
        <div class="framer-card-front">
            <div class="flip-icon">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M21 12a9 9 0 0 0-9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"/><path d="M3 3v5h5"/><path d="M3 12a9 9 0 0 0 9 9 9.75 9.75 0 0 0 6.74-2.74L21 16"/><path d="M21 21v-5h-5"/></svg>
            </div>
            
            <div class="framer-card-badges">
                <?php if ($verified): ?>
                <span class="framer-badge framer-badge-verified">Verified</span>
                <?php endif; ?>
                <span class="framer-badge"><?php echo esc_html($geran_display); ?></span>
            </div>

            <div class="framer-card-image">
                <?php if (has_post_thumbnail()) : ?>
                    <?php the_post_thumbnail('listing-thumb', ['alt' => get_the_title()]); ?>
                <?php else : ?>
                    <img src="<?php echo get_template_directory_uri(); ?>/assets/images/placeholder.jpeg" alt="Tanah untuk dijual">
                <?php endif; ?>
                
                <div class="framer-card-overlay">
                    <h3 class="framer-card-title"><?php the_title(); ?></h3>
                    <?php if ($harga > 0): ?>
                        <div class="framer-card-price">RM <?php echo number_format((int)$harga); ?></div>
                    <?php endif; ?>
                    <div class="extra-reveal">
                        <span><?php echo esc_html($ekar); ?> ekar</span>
                        <span>•</span>
                        <span><?php echo esc_html($location); ?></span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Back -->
        <div class="framer-card-back">
            <div class="flip-icon">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M21 12a9 9 0 0 0-9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"/><path d="M3 3v5h5"/><path d="M3 12a9 9 0 0 0 9 9 9.75 9.75 0 0 0 6.74-2.74L21 16"/><path d="M21 21v-5h-5"/></svg>
            </div>
            
            <div class="back-header">
                <div class="back-title">Spesifikasi Tanah</div>
            </div>
            
            <div class="back-specs">
                <div class="spec-item">
                    <span class="spec-label">Luas</span>
                    <span class="spec-value"><?php echo esc_html($ekar); ?> ekar</span>
                </div>
                <div class="spec-item">
                    <span class="spec-label">Jenis Geran</span>
                    <span class="spec-value"><?php echo esc_html($geran_display); ?></span>
                </div>
                <?php if ($psf > 0): ?>
                <div class="spec-item">
                    <span class="spec-label">Harga/sqft</span>
                    <span class="spec-value">RM <?php echo number_format($psf, 2); ?></span>
                </div>
                <?php endif; ?>
                <?php if ($property_id): ?>
                <div class="spec-item">
                    <span class="spec-label">ID</span>
                    <span class="spec-value"><?php echo esc_html($property_id); ?></span>
                </div>
                <?php endif; ?>
            </div>
            
            <div class="back-description">
                <?php echo wp_trim_words(get_the_excerpt(), 25); ?>
            </div>
            
            <a href="<?php the_permalink(); ?>" class="back-cta">Lihat Details</a>
        </div>
    </div>
</div>