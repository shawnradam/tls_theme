<?php
/**
 * Template Name: Demo Property Page
 * Description: A demo page for testing property links from map info panel
 */

get_header(); ?>

<section class="demo-property-section" style="padding: 120px 20px 60px; min-height: 100vh; background: var(--bg);">
    <div class="container" style="max-width: 800px; margin: 0 auto;">
        <div style="background: white; border-radius: 16px; padding: 40px; box-shadow: var(--shadow-lg);">
            <span style="display: inline-block; padding: 6px 16px; background: #dcfce7; color: #166534; border-radius: 20px; font-size: 0.85rem; font-weight: 600; margin-bottom: 20px;">Available</span>
            
            <h1 style="font-family: var(--font-display); font-size: 2.5rem; margin-bottom: 16px; line-height: 1.2;">Lot Tanah Demo - Tamparuli</h1>
            
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin: 30px 0; padding: 20px; background: #f8fafc; border-radius: 12px;">
                <div>
                    <div style="font-size: 0.85rem; color: var(--muted); text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 4px;">Size</div>
                    <div style="font-size: 1.2rem; font-weight: 600; color: var(--slate);">5.2 Acres</div>
                </div>
                <div>
                    <div style="font-size: 0.85rem; color: var(--muted); text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 4px;">Price</div>
                    <div style="font-size: 1.2rem; font-weight: 600; color: #1a73e8;">RM 250,000</div>
                </div>
                <div>
                    <div style="font-size: 0.85rem; color: var(--muted); text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 4px;">Geran Type</div>
                    <div style="font-size: 1.2rem; font-weight: 600; color: var(--slate);">Native Title (NT)</div>
                </div>
                <div>
                    <div style="font-size: 0.85rem; color: var(--muted); text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 4px;">Geran No</div>
                    <div style="font-size: 1.2rem; font-weight: 600; color: var(--slate);">NT-2024-001</div>
                </div>
            </div>

            <div style="margin: 30px 0;">
                <h3 style="font-size: 1.2rem; margin-bottom: 12px;">Description</h3>
                <p style="line-height: 1.7; color: #475569;">This is a demo property page for testing the map info panel property link feature. The property is located in Tamparuli, Sabah with verified boundaries and easy access to main road.</p>
            </div>

            <div style="display: flex; gap: 16px; margin-top: 30px;">
                <a href="<?php echo home_url('/#map-portal'); ?>" class="btn-hero-primary" style="text-decoration: none;">
                    <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l5.447 2.724A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/></svg>
                    View on Map
                </a>
                <?php 
                $wa_demo = get_theme_mod('whatsapp_number', '60123456789');
                $wa_demo_enc = base64_encode($wa_demo);
                ?>
                <a href="javascript:void(0)" onclick="tlsRevealContact(this, 'wa', '<?php echo $wa_demo_enc; ?>')" class="btn-hero-secondary" style="text-decoration: none;">
                    <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                    WhatsApp Agent
                </a>
            </div>

            <div style="margin-top: 40px; padding-top: 30px; border-top: 1px solid var(--border);">
                <h3 style="font-size: 1.2rem; margin-bottom: 16px;">Location</h3>
                <p style="line-height: 1.7; color: #475569;">
                    <strong>Coordinates:</strong> 6.1300, 116.2300<br>
                    <strong>Address:</strong> Tamparuli, Tuaran, Sabah<br>
                    <strong>Access:</strong> 5 minutes to main road, 20 minutes to KK city
                </p>
            </div>
        </div>
    </div>
</section>

<?php get_footer(); ?>
