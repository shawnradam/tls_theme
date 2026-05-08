<article class="listing-card" 
    data-id="<?php echo get_the_ID(); ?>" 
    data-title="<?php echo esc_attr(get_the_title()); ?>"
    data-price="<?php echo esc_attr(get_post_meta(get_the_ID(), '_tanah_harga', true) ?: 0); ?>"
    data-geran="<?php echo esc_attr($geran); ?>"
    data-location="<?php echo esc_attr($location); ?>"
    data-daerah="<?php echo esc_attr($daerah); ?>"
    data-town="<?php echo esc_attr($town); ?>"
    data-geran-display="<?php echo esc_attr($geran_display); ?>"
>
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

$geran_class = 'badge-' . strtolower($geran);
if ($geran === 'Hakmilik') $geran_class = 'badge-fh';
if ($geran === 'Development') $geran_class = 'badge-development';

$harga = get_post_meta(get_the_ID(), '_tanah_harga', true) ?: 0;
$ekar = get_post_meta(get_the_ID(), '_tanah_keluasan', true) ?: 0;
$sqft = $ekar * 43560;
$psf = $sqft > 0 ? $harga / $sqft : 0;
?>
    <a href="<?php the_permalink(); ?>" class="listing-image-link">
        <div class="listing-image">
            <?php if (has_post_thumbnail()) : ?>
                <?php the_post_thumbnail('listing-thumb', ['alt' => get_the_title()]); ?>
            <?php else : ?>
                <img src="<?php echo get_template_directory_uri(); ?>/assets/images/placeholder.jpeg" alt="Tanah untuk dijual">
            <?php endif; ?>
            <div class="listing-badges">
                <?php if ($verified): ?>
                <span class="badge badge-verified" title="Verified Documentation">✓</span>
                <?php endif; ?>
                <span class="badge <?php echo esc_attr($geran_class); ?>"><?php echo esc_html($geran_display); ?></span>
                <span class="badge" style="background: rgba(0,0,0,0.5); color: #fff;"><?php echo esc_html($location); ?></span>
            </div>
        </div>
    </a>
    <div class="listing-body">
        <?php if ($property_id): ?>
        <div class="listing-property-id">ID: <?php echo esc_html($property_id); ?></div>
        <?php endif; ?>
        <h3 class="listing-card-title"><?php the_title(); ?></h3>
        <?php if ($harga > 0): ?>
            <div class="listing-price">RM <?php echo number_format((int)$harga); ?></div>
            <div class="listing-psf">RM <?php echo number_format($psf, 2); ?>/sqft</div>
        <?php endif; ?>
        <div class="listing-meta">
            <span><?php echo esc_html($ekar); ?> ekar</span>
            <span>-</span>
            <span><?php echo esc_html($location); ?></span>
        </div>
        <a href="<?php the_permalink(); ?>" class="btn btn-detail">Lihat Detail</a>
    </div>
</article>