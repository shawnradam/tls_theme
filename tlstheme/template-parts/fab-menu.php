<?php
/**
 * FAB Menu Template
 * Renders Floating Action Button based on settings
 */

if (!defined('ABSPATH')) exit;

// Get FAB settings
global $wpdb;
$table_name = $wpdb->prefix . 'tls_fab_settings';
$settings = $wpdb->get_row("SELECT * FROM $table_name ORDER BY id DESC LIMIT 1", ARRAY_A);

// If no settings exist or FAB is disabled, return
if (!$settings || !$settings['enabled']) {
    return;
}

// Get contact info from settings or customizer
$whatsapp = $settings['whatsapp_number'] ?: get_theme_mod('whatsapp_number', '60123456789');
$phone = $settings['phone_number'] ?: get_theme_mod('phone_number', $whatsapp);
$button_color = $settings['button_color'] ?: '#667eea';
$position = $settings['button_position'] ?: 'bottom-right';

// Position classes
$position_class = $position === 'bottom-left' ? 'fab-left' : 'fab-right';

// Custom CSS
if (!empty($settings['custom_css'])) {
    echo '<style>' . wp_kses_post($settings['custom_css']) . '</style>';
}

// Render based on FAB type
$fab_type = $settings['fab_type'];
?>

<!-- FAB System Styles -->
<style>
.tls-fab-button {
    pointer-events: auto !important;
    cursor: pointer !important;
    z-index: 1001 !important;
}
</style>

<!-- FAB Container -->
<div class="tls-fab-container <?php echo esc_attr($position_class); ?>" data-fab-type="<?php echo esc_attr($fab_type); ?>">

    <?php if ($fab_type === 'type1'): ?>
        <!-- TYPE 1: Full FAB - All 4 buttons in FAB menu -->

        <!-- Main FAB Button -->
        <button class="tls-fab-button" id="tlsFabButton" aria-label="Actions Menu" style="background: <?php echo esc_attr($button_color); ?>;">
            <svg class="fab-icon-plus" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3">
                <line x1="12" y1="5" x2="12" y2="19"></line>
                <line x1="5" y1="12" x2="19" y2="12"></line>
            </svg>
        </button>

        <!-- FAB Menu -->
        <div class="tls-fab-menu" id="tlsFabMenu">
            <?php if ($settings['show_search']): ?>
            <div class="fab-menu-item">
                <span class="fab-menu-label">Search Properties</span>
                <button class="fab-menu-button" id="fabSearchBtn" style="color: <?php echo esc_attr($button_color); ?>;">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="11" cy="11" r="8"></circle>
                        <path d="m21 21-4.35-4.35"></path>
                    </svg>
                </button>
            </div>
            <?php endif; ?>

            <?php if ($settings['show_calculator']): ?>
            <div class="fab-menu-item">
                <span class="fab-menu-label">Calculator</span>
                <button class="fab-menu-button" id="fabCalcBtn" style="color: <?php echo esc_attr($button_color); ?>;">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <rect x="4" y="2" width="16" height="20" rx="2"></rect>
                        <line x1="8" y1="6" x2="16" y2="6"></line>
                        <line x1="8" y1="10" x2="16" y2="10"></line>
                        <line x1="8" y1="14" x2="16" y2="14"></line>
                        <line x1="8" y1="18" x2="16" y2="18"></line>
                    </svg>
                </button>
            </div>
            <?php endif; ?>

            <?php if ($settings['show_whatsapp']): ?>
            <div class="fab-menu-item">
                <span class="fab-menu-label">WhatsApp</span>
                <a href="https://wa.me/<?php echo esc_attr($whatsapp); ?>" class="fab-menu-button" target="_blank" style="color: <?php echo esc_attr($button_color); ?>;">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z"/>
                    </svg>
                </a>
            </div>
            <?php endif; ?>

            <?php if ($settings['show_call']): ?>
            <div class="fab-menu-item">
                <span class="fab-menu-label">Call Us</span>
                <a href="tel:+<?php echo esc_attr($phone); ?>" class="fab-menu-button" style="color: <?php echo esc_attr($button_color); ?>;">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path>
                    </svg>
                </a>
            </div>
            <?php endif; ?>
        </div>

    <?php elseif ($fab_type === 'type2'): ?>
        <!-- TYPE 2: FAB + Sticky Footer - Tools in FAB, Communication in footer -->

        <!-- Main FAB Button -->
        <button class="tls-fab-button" id="tlsFabButton" aria-label="Tools Menu" style="background: <?php echo esc_attr($button_color); ?>;">
            <svg class="fab-icon-plus" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3">
                <line x1="12" y1="5" x2="12" y2="19"></line>
                <line x1="5" y1="12" x2="19" y2="12"></line>
            </svg>
        </button>

        <!-- FAB Menu (Tools Only) -->
        <div class="tls-fab-menu" id="tlsFabMenu">
            <?php if ($settings['show_search']): ?>
            <div class="fab-menu-item">
                <span class="fab-menu-label">Search Properties</span>
                <button class="fab-menu-button" id="fabSearchBtn" style="color: <?php echo esc_attr($button_color); ?>;">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="11" cy="11" r="8"></circle>
                        <path d="m21 21-4.35-4.35"></path>
                    </svg>
                </button>
            </div>
            <?php endif; ?>

            <?php if ($settings['show_calculator']): ?>
            <div class="fab-menu-item">
                <span class="fab-menu-label">Calculator</span>
                <button class="fab-menu-button" id="fabCalcBtn" style="color: <?php echo esc_attr($button_color); ?>;">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <rect x="4" y="2" width="16" height="20" rx="2"></rect>
                        <line x1="8" y1="6" x2="16" y2="6"></line>
                        <line x1="8" y1="10" x2="16" y2="10"></line>
                        <line x1="8" y1="14" x2="16" y2="14"></line>
                        <line x1="8" y1="18" x2="16" y2="18"></line>
                    </svg>
                </button>
            </div>
            <?php endif; ?>
        </div>

        <!-- Sticky Footer (Communication) -->
        <?php if ($settings['show_sticky_footer'] && ($settings['show_whatsapp'] || $settings['show_call'])): ?>
        <div class="tls-sticky-footer">
            <?php if ($settings['show_whatsapp']): ?>
            <a href="https://wa.me/<?php echo esc_attr($whatsapp); ?>" class="sticky-footer-btn btn-whatsapp" target="_blank">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z"/>
                </svg>
                <span>WhatsApp</span>
            </a>
            <?php endif; ?>

            <?php if ($settings['show_call']): ?>
            <a href="tel:+<?php echo esc_attr($phone); ?>" class="sticky-footer-btn btn-call">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path>
                </svg>
                <span>Call</span>
            </a>
            <?php endif; ?>
        </div>
        <?php endif; ?>

    <?php elseif ($fab_type === 'type3'): ?>
        <!-- TYPE 3: Minimal FAB - Only Search + Calculator -->

        <!-- Main FAB Button -->
        <button class="tls-fab-button" id="tlsFabButton" aria-label="Tools Menu" style="background: <?php echo esc_attr($button_color); ?>;">
            <svg class="fab-icon-plus" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3">
                <line x1="12" y1="5" x2="12" y2="19"></line>
                <line x1="5" y1="12" x2="19" y2="12"></line>
            </svg>
        </button>

        <!-- FAB Menu (Tools Only) -->
        <div class="tls-fab-menu" id="tlsFabMenu">
            <?php if ($settings['show_search']): ?>
            <div class="fab-menu-item">
                <span class="fab-menu-label">Search Properties</span>
                <button class="fab-menu-button" id="fabSearchBtn" style="color: <?php echo esc_attr($button_color); ?>;">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="11" cy="11" r="8"></circle>
                        <path d="m21 21-4.35-4.35"></path>
                    </svg>
                </button>
            </div>
            <?php endif; ?>

            <?php if ($settings['show_calculator']): ?>
            <div class="fab-menu-item">
                <span class="fab-menu-label">Calculator</span>
                <button class="fab-menu-button" id="fabCalcBtn" style="color: <?php echo esc_attr($button_color); ?>;">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <rect x="4" y="2" width="16" height="20" rx="2"></rect>
                        <line x1="8" y1="6" x2="16" y2="6"></line>
                        <line x1="8" y1="10" x2="16" y2="10"></line>
                        <line x1="8" y1="14" x2="16" y2="14"></line>
                        <line x1="8" y1="18" x2="16" y2="18"></line>
                    </svg>
                </button>
            </div>
            <?php endif; ?>
        </div>

    <?php elseif ($fab_type === 'type4'): ?>
        <!-- TYPE 4: Sticky Footer Only - All buttons in footer -->

        <div class="tls-sticky-footer tls-footer-full">
            <?php if ($settings['show_search']): ?>
            <button class="sticky-footer-btn btn-search" id="fabSearchBtn" style="background: <?php echo esc_attr($button_color); ?>;">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="11" cy="11" r="8"></circle>
                    <path d="m21 21-4.35-4.35"></path>
                </svg>
                <span>Search</span>
            </button>
            <?php endif; ?>

            <?php if ($settings['show_calculator']): ?>
            <button class="sticky-footer-btn btn-calculator" id="fabCalcBtn" style="background: <?php echo esc_attr($button_color); ?>;">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <rect x="4" y="2" width="16" height="20" rx="2"></rect>
                    <line x1="8" y1="6" x2="16" y2="6"></line>
                    <line x1="8" y1="10" x2="16" y2="10"></line>
                    <line x1="8" y1="14" x2="16" y2="14"></line>
                    <line x1="8" y1="18" x2="16" y2="18"></line>
                </svg>
                <span>Calculator</span>
            </button>
            <?php endif; ?>

            <?php if ($settings['show_whatsapp']): ?>
            <a href="https://wa.me/<?php echo esc_attr($whatsapp); ?>" class="sticky-footer-btn btn-whatsapp" target="_blank">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z"/>
                </svg>
                <span>WhatsApp</span>
            </a>
            <?php endif; ?>

            <?php if ($settings['show_call']): ?>
            <a href="tel:+<?php echo esc_attr($phone); ?>" class="sticky-footer-btn btn-call">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path>
                </svg>
                <span>Call</span>
            </a>
            <?php endif; ?>
        </div>

    <?php endif; ?>

</div>

<!-- FAB JavaScript -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    console.log('FAB System: DOMContentLoaded fired');

    // FAB Button Toggle
    const fabButton = document.getElementById('tlsFabButton');
    const fabMenu = document.getElementById('tlsFabMenu');

    console.log('FAB Button:', fabButton);
    console.log('FAB Menu:', fabMenu);

    if (fabButton && fabMenu) {
        console.log('FAB System: Elements found, attaching click handler');

        fabButton.addEventListener('click', function(e) {
            console.log('FAB Button clicked!');
            e.stopPropagation();
            this.classList.toggle('active');
            fabMenu.classList.toggle('active');
        });

        // Close menu when clicking outside
        document.addEventListener('click', function(e) {
            if (!e.target.closest('.tls-fab-container')) {
                fabButton.classList.remove('active');
                fabMenu.classList.remove('active');
            }
        });
    } else {
        console.error('FAB System: Button or menu not found!');
    }

    // Search button - opens search modal
    const searchBtns = document.querySelectorAll('#fabSearchBtn');
    searchBtns.forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.stopPropagation();
            const searchModal = document.getElementById('searchModal');
            if (searchModal) {
                searchModal.classList.add('active');
            }
            // Close FAB menu
            if (fabButton && fabMenu) {
                fabButton.classList.remove('active');
                fabMenu.classList.remove('active');
            }
        });
    });

    // Calculator button - opens calculator modal
    const calcBtns = document.querySelectorAll('#fabCalcBtn');
    calcBtns.forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.stopPropagation();
            const calcModal = document.getElementById('calcModal');
            if (calcModal) {
                calcModal.classList.add('active');
            }
            // Close FAB menu
            if (fabButton && fabMenu) {
                fabButton.classList.remove('active');
                fabMenu.classList.remove('active');
            }
        });
    });
});
</script>
