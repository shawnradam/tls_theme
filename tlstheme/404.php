<?php get_header(); ?>

<div class="container section" style="min-height:60vh;text-align:center;">
    <h1 class="section-title">Halaman Tidak Ditemui</h1>
    <div class="error-404">
        <h2 style="font-size:8rem;color:var(--color-border);margin:0;font-weight:700;">404</h2>
        <p style="font-size:1.2rem;margin:1rem 0 2rem;">Maaf, halaman yang anda cari tidak wujud.</p>
        <a href="<?php echo esc_url(home_url('/')); ?>" class="btn">Kembali ke Laman Utama</a>
    </div>
</div>

<?php get_footer(); ?>
