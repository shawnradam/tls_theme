<?php
/**
 * Template Name: Land Development Calculator
 */

get_header();
?>

<section class="section calculator-page-section">
    <div class="container">
        <h1 class="section-title" style="text-align:center; margin-bottom: 10px;">Kalkulator Kos Pembangunan Tanah</h1>
        <p class="section-subtitle" style="text-align:center; margin-bottom: 40px; color: #666;">
            Kira anggaran kos untuk membangunkan tanah anda di Sabah
        </p>
        
        <?php echo do_shortcode('[land_dev_calculator]'); ?>
    </div>
</section>

<?php get_footer(); ?>
