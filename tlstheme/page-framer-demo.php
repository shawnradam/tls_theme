<?php
/**
 * Template Name: Framer Card Demo
 * Description: A demo page for the new 3D Flipping Real Estate Card with Cinematic Hover
 */

get_header(); ?>

<style>
/* New Card Demo Styles */
.framer-demo-section {
    padding: 100px 20px;
    background: #f0f2f5;
    min-height: 100vh;
    display: flex;
    justify-content: center;
    align-items: center;
}

.framer-grid {
    display: grid; 
    grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); 
    gap: 40px; 
    justify-items: center;
    width: 100%;
    max-width: 1200px;
}

.framer-card-container {
    width: 350px;
    height: 480px;
    position: relative;
    cursor: pointer;
    perspective: 1500px; /* Stronger perspective for 3D feel */
}

.framer-card-inner {
    width: 100%;
    height: 100%;
    transition: transform 0.8s cubic-bezier(0.4, 0, 0.2, 1);
    transform-style: preserve-3d;
    position: relative;
}

/* FLIP TRIGGER - REMOVED HOVER, ADDED CLASS */
.framer-card-inner.flipped {
    transform: rotateY(180deg);
}

.framer-card-front, .framer-card-back {
    position: absolute;
    width: 100%;
    height: 100%;
    backface-visibility: hidden;
    border-radius: 24px;
    overflow: hidden;
    background: white;
    box-shadow: 0 15px 35px rgba(0,0,0,0.1);
}

.framer-card-back {
    transform: rotateY(180deg);
    padding: 30px;
    display: flex;
    flex-direction: column;
    background: #ffffff;
}

/* FLIP ICON - TOP RIGHT */
.flip-icon {
    position: absolute;
    top: 20px;
    right: 20px;
    width: 42px;
    height: 42px;
    background: rgba(255,255,255,0.25);
    backdrop-filter: blur(12px);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 10;
    border: 1px solid rgba(255,255,255,0.4);
    color: white;
    transition: all 0.5s cubic-bezier(0.4, 0, 0.2, 1);
}

.framer-card-container:hover .flip-icon {
    transform: rotate(180deg) scale(1.1);
    background: rgba(255,255,255,0.5);
    color: #000;
}

/* FRONT CONTENT & CINEMATIC ZOOM */
.framer-card-image {
    width: 100%;
    height: 100%;
    position: relative;
    overflow: hidden;
}

.framer-card-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 1.5s cubic-bezier(0.2, 0.8, 0.2, 1);
}

.framer-card-container:hover .framer-card-image img {
    transform: scale(1.18);
}

.framer-card-overlay {
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    padding: 40px 25px;
    background: linear-gradient(to top, 
        rgba(0,0,0,0.9) 0%, 
        rgba(0,0,0,0.5) 50%, 
        transparent 100%);
    color: white;
    z-index: 2;
    transition: all 0.6s cubic-bezier(0.4, 0, 0.2, 1);
}

/* REVEAL INFO ON HOVER */
.extra-reveal {
    max-height: 0;
    opacity: 0;
    transform: translateY(10px);
    transition: all 0.5s cubic-bezier(0.4, 0, 0.2, 1);
    font-size: 0.9rem;
    color: rgba(255,255,255,0.7);
    margin-top: 12px;
    display: flex;
    gap: 10px;
}

.framer-card-container:hover .extra-reveal {
    max-height: 60px;
    opacity: 1;
    transform: translateY(0);
}

.framer-card-badges {
    position: absolute;
    top: 20px;
    left: 20px;
    display: flex;
    gap: 10px;
    z-index: 3;
}

.framer-badge {
    padding: 7px 16px;
    border-radius: 50px;
    font-size: 0.75rem;
    font-weight: 600;
    text-transform: uppercase;
    backdrop-filter: blur(10px);
    background: rgba(255,255,255,0.15);
    color: white;
    border: 1px solid rgba(255,255,255,0.25);
}

.framer-badge-verified {
    background: rgba(34, 197, 94, 0.7);
    border-color: rgba(34, 197, 94, 0.9);
}

.framer-card-title {
    font-size: 1.6rem;
    font-weight: 700;
    margin-bottom: 5px;
    line-height: 1.1;
}

.framer-card-price {
    font-size: 1.3rem;
    color: #ffeb3b;
    font-weight: 700;
}

/* BACK CONTENT STYLING */
.back-header {
    border-bottom: 1px solid #f0f0f0;
    padding-bottom: 20px;
    margin-bottom: 20px;
}

.back-title {
    font-size: 1.3rem;
    font-weight: 800;
    color: #1a1a1a;
}

.back-specs {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 24px;
    margin-bottom: 30px;
}

.spec-item {
    display: flex;
    flex-direction: column;
}

.spec-label {
    font-size: 0.75rem;
    color: #999;
    text-transform: uppercase;
    font-weight: 700;
    letter-spacing: 1px;
    margin-bottom: 4px;
}

.spec-value {
    font-size: 1.1rem;
    font-weight: 600;
    color: #333;
}

.back-description {
    font-size: 0.95rem;
    color: #666;
    line-height: 1.6;
    margin-bottom: auto;
}

.back-cta {
    margin-top: 20px;
    width: 100%;
    padding: 16px;
    background: #000;
    color: #fff;
    text-align: center;
    border-radius: 14px;
    font-weight: 700;
    text-decoration: none;
    transition: all 0.3s ease;
    border: 2px solid #000;
}

.back-cta:hover {
    background: transparent;
    color: #000;
}

</style>

<div class="framer-demo-section">
    <div class="container" style="max-width: 1200px; margin: 0 auto; width: 100%;">
        <h1 style="text-align: center; margin-bottom: 60px; font-family: sans-serif; font-weight: 800;">Premium Cinematic Land Cards</h1>
        
        <div class="framer-grid">
            
            <?php 
            $demo_cards = [
                [
                    'title' => 'Lot Tanah 5.2 Ekar Tamparuli',
                    'price' => 'RM 250,000',
                    'image' => '/assets/images/linangkit.jpg',
                    'size' => '5.2 Acres',
                    'type' => 'NT (Native)',
                    'psf' => 'RM 1.10',
                    'id' => '#TLS-2024-01',
                    'desc' => 'Tanah rata dengan pemandangan Gunung Kinabalu. Sesuai untuk pertanian atau rumah rehat.',
                    'verified' => true
                ],
                [
                    'title' => 'Kawasan Industri Sepanggar',
                    'price' => 'RM 1,200,000',
                    'image' => '/assets/images/placeholder.jpeg',
                    'size' => '1.5 Acres',
                    'type' => 'CL (99 Years)',
                    'psf' => 'RM 18.30',
                    'id' => '#TLS-2024-02',
                    'desc' => 'Kawasan perindustrian strategik. Berhampiran pelabuhan dan lebuhraya utama.',
                    'verified' => false
                ],
                [
                    'title' => 'Tanah Bangunan Penampang',
                    'price' => 'RM 450,000',
                    'image' => '/assets/images/linangkit.jpg',
                    'size' => '0.25 Acres',
                    'type' => 'Country Lease',
                    'psf' => 'RM 41.30',
                    'id' => '#TLS-2024-03',
                    'desc' => 'Tanah lot banglo di kawasan matang. Berhampiran sekolah dan pusat membeli-belah.',
                    'verified' => true
                ]
            ];

            foreach($demo_cards as $card):
            ?>
            <div class="framer-card-container">
                <div class="framer-card-inner">
                    <!-- Front -->
                    <div class="framer-card-front">
                        <div class="flip-icon">
                            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M21 12a9 9 0 0 0-9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"/><path d="M3 3v5h5"/><path d="M3 12a9 9 0 0 0 9 9 9.75 9.75 0 0 0 6.74-2.74L21 16"/><path d="M21 21v-5h-5"/></svg>
                        </div>
                        <div class="framer-card-badges">
                            <?php if($card['verified']): ?>
                            <span class="framer-badge framer-badge-verified">Verified</span>
                            <?php endif; ?>
                            <span class="framer-badge"><?php echo $card['type']; ?></span>
                        </div>
                        <div class="framer-card-image">
                            <img src="<?php echo get_template_directory_uri() . $card['image']; ?>" alt="Property">
                            <div class="framer-card-overlay">
                                <div class="framer-card-title"><?php echo $card['title']; ?></div>
                                <div class="framer-card-price"><?php echo $card['price']; ?></div>
                                <div class="extra-reveal">
                                    <span><?php echo $card['size']; ?></span>
                                    <span>•</span>
                                    <span>Sabah</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Back -->
                    <div class="framer-card-back">
                        <div class="flip-icon" style="color: #333; background: rgba(0,0,0,0.05); border-color: rgba(0,0,0,0.1);">
                            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M21 12a9 9 0 0 0-9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"/><path d="M3 3v5h5"/><path d="M3 12a9 9 0 0 0 9 9 9.75 9.75 0 0 0 6.74-2.74L21 16"/><path d="M21 21v-5h-5"/></svg>
                        </div>
                        <div class="back-header">
                            <div class="back-title">Spesifikasi Tanah</div>
                        </div>
                        
                        <div class="back-specs">
                            <div class="spec-item">
                                <span class="spec-label">Luas</span>
                                <span class="spec-value"><?php echo $card['size']; ?></span>
                            </div>
                            <div class="spec-item">
                                <span class="spec-label">Jenis Geran</span>
                                <span class="spec-value"><?php echo $card['type']; ?></span>
                            </div>
                        </div>
                        
                        <div class="back-description">
                            <?php echo $card['desc']; ?>
                        </div>
                        
                        <a href="#" class="back-cta">Lihat Details</a>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>

        </div>
    </div>
</div>

<?php get_footer(); ?>

<script>
document.querySelectorAll('.framer-card-container').forEach(card => {
    card.addEventListener('click', function(e) {
        // Don't flip if clicking the CTA button on the back
        if (e.target.classList.contains('back-cta')) return;
        
        const inner = this.querySelector('.framer-card-inner');
        inner.classList.toggle('flipped');
    });
});
</script>
