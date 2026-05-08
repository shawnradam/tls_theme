<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="profile" href="https://gmpg.org/xfn/11">
    <?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<?php
// Get contact settings
$whatsapp = get_theme_mod('whatsapp_number', '601126661706');
$facebook = get_theme_mod('facebook_url', 'https://facebook.com/tanahlotsabah');
$instagram = get_theme_mod('instagram_url', 'https://instagram.com/tanahlotsabah');
$tiktok = get_theme_mod('tiktok_url', 'https://tiktok.com/@tanahlotsabah');

// Format display number
$whatsapp_display = '+' . substr($whatsapp, 0, 2) . ' ' . substr($whatsapp, 2, 3) . '-' . substr($whatsapp, 5, 3) . ' ' . substr($whatsapp, 8);
?>

<!--
============================================
HEADER LAYOUT - LOCKED STRUCTURE
============================================
DO NOT MODIFY THIS LAYOUT!

2-Column Structure:
- Left: Logo
- Right: User Icon + Menu Toggle (grouped)

User icon is permanently positioned on the right.
Do not move it back to center or anywhere else.
============================================
-->
<header class="site-header">
    <div class="container">
        <div class="header-flex">
            <!-- Left: Logo -->
            <div class="logo-left">
                <a href="<?php echo esc_url(home_url('/')); ?>">
                    <img src="<?php echo get_template_directory_uri(); ?>/headerbgpnglogo.webp" alt="tanahlotsabah">
                </a>
            </div>

            <!-- Right: User Icon + Menu Toggle (LOCKED - DO NOT MOVE) -->
            <div class="header-right-group">
                <!-- User Icon -->
                <div class="header-login">
                    <?php if (is_user_logged_in()): ?>
                        <?php $current_user = wp_get_current_user(); ?>
                        <div class="login-user" onclick="toggleLoginMenu()" title="<?php echo esc_attr($current_user->display_name); ?>">
                            <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                            </svg>
                        </div>
                        <div class="login-dropdown" id="loginDropdown">
                            <div class="dropdown-header"><?php echo esc_html($current_user->display_name); ?></div>
                            <a href="<?php echo home_url('/dashboard/'); ?>">
                                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                                Dashboard
                            </a>
                            <a href="<?php echo home_url('/logout/'); ?>" class="logout-link">
                                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                                Logout
                            </a>
                        </div>
                    <?php else: ?>
                        <a href="<?php echo home_url('/login/'); ?>" class="login-btn" title="Login">
                            <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/>
                            </svg>
                        </a>
                    <?php endif; ?>
                </div>

                <!-- Menu Toggle -->
                <button class="menu-toggle" id="menuToggle" aria-label="Toggle Menu">
                    <span class="menu-text">MENU</span>
                    <div class="menu-lines">
                        <span></span>
                        <span></span>
                    </div>
                </button>
            </div>
            
            <nav class="primary" id="primaryNav">
                <button class="menu-close" id="menuClose" aria-label="Close Menu">
                    <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="18" y1="6" x2="6" y2="18"></line>
                        <line x1="6" y1="6" x2="18" y2="18"></line>
                    </svg>
                </button>
                <div class="menu-container">

                    <!-- Top: Brand Name (Plain Text) -->
                    <div class="menu-top">

                        <div class="menu-brand-text">Tanah Lot Sabah</div>

                    </div>

                    <!-- Middle: Large Bold Links -->
                    <div class="menu-middle">
                        <?php
                        wp_nav_menu([
                            'theme_location' => 'primary',
                            'container' => false,
                            'menu_class' => 'large-menu-list',
                            'fallback_cb' => function() {
                                echo '<ul class="large-menu-list"><li><a href="' . esc_url(home_url('/')) . '">Home</a></li><li><a href="' . esc_url(home_url('/tanah/')) . '">Beli Tanah</a></li><li><a href="' . esc_url(home_url('/news/')) . '">Panduan</a></li></ul>';
                            }
                        ]);
                        ?>
                    </div>

                    <!-- Bottom: Social Media & WhatsApp -->
                    <div class="menu-bottom">
                        <div class="menu-socials">
                            <?php if ($facebook): ?>
                            <a href="<?php echo esc_url($facebook); ?>" class="social-icon" target="_blank" aria-label="Facebook">
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
                            </a>
                            <?php endif; ?>
                            
                            <?php if ($instagram): ?>
                            <a href="<?php echo esc_url($instagram); ?>" class="social-icon" target="_blank" aria-label="Instagram">
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="2" width="20" height="20" rx="5" ry="5"></rect><path d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37z"></path><line x1="17.5" y1="6.5" x2="17.51" y2="6.5"></line></svg>
                            </a>
                            <?php endif; ?>

                            <?php if ($tiktok): ?>
                            <a href="<?php echo esc_url($tiktok); ?>" class="social-icon" target="_blank" aria-label="TikTok">
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor"><path d="M12.525.02c1.31-.02 2.61-.01 3.91-.02.08 1.53.63 3.09 1.75 4.17 1.12 1.11 2.7 1.62 4.24 1.79v4.03c-1.44-.05-2.89-.35-4.2-.97-.57-.26-1.1-.59-1.62-.93-.01 2.92.01 5.84-.02 8.75-.08 1.4-.54 2.79-1.35 3.94-1.31 1.92-3.58 3.17-5.91 3.21-1.43.08-2.86-.31-4.08-1.03-2.02-1.19-3.44-3.37-3.65-5.71-.02-.5-.03-1-.01-1.49.18-1.9 1.12-3.72 2.58-4.96 1.66-1.44 3.98-2.13 6.15-1.72.02 1.48-.04 2.96-.04 4.44-.99-.32-2.15-.23-3.02.37-.63.41-1.11 1.04-1.36 1.75-.21.51-.15 1.07-.14 1.61.24 1.64 1.82 3.02 3.5 2.87 1.12-.01 2.19-.66 2.77-1.61.19-.33.4-.67.41-1.06.1-1.79.06-3.57.07-5.36.01-4.03-.01-8.05.02-12.07z"/></svg>
                            </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </nav>

        </div>
    </div>
</header>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var menuToggle = document.getElementById('menuToggle');
    var menuClose = document.getElementById('menuClose');
    var primaryNav = document.getElementById('primaryNav');
    var menuOverlay = document.getElementById('menuOverlay');

    if (menuToggle && primaryNav) {
        function toggleMenu() {
            primaryNav.classList.toggle('active');
            menuToggle.classList.toggle('active');
            document.body.classList.toggle('menu-active');
        }

        menuToggle.addEventListener('click', toggleMenu);

        // Close button
        if (menuClose) {
            menuClose.addEventListener('click', toggleMenu);
        }

        if (menuOverlay) {
            menuOverlay.addEventListener('click', toggleMenu);
        }

        // Handle menu link clicks
        var menuLinks = primaryNav.querySelectorAll('a');
        menuLinks.forEach(function(link) {
            link.addEventListener('click', function() {
                primaryNav.classList.remove('active');
                menuToggle.classList.remove('active');
                document.body.classList.remove('menu-active');
            });
        });
    }
});

function toggleLoginMenu() {
    const dropdown = document.getElementById('loginDropdown');
    const formDropdown = document.getElementById('loginFormDropdown');
    if (formDropdown) formDropdown.style.display = 'none';
    dropdown.style.display = dropdown.style.display === 'block' ? 'none' : 'block';
    event.stopPropagation();
}

document.addEventListener('click', function(e) {
    const dropdowns = document.querySelectorAll('.login-form-dropdown, .login-dropdown');
    dropdowns.forEach(function(d) {
        if (!d.contains(e.target) && !d.previousElementSibling?.contains(e.target)) {
            d.style.display = 'none';
        }
    });
});
</script>

<main>
