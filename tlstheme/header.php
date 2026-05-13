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

<!-- Mobile Menu Overlay -->
<div class="menu-overlay" id="menuOverlay"></div>

<?php
// Get contact settings
$whatsapp = get_theme_mod('whatsapp_number', '601126661706');
$facebook = get_theme_mod('facebook_url', 'https://facebook.com/tanahlotsabah');
$instagram = get_theme_mod('instagram_url', 'https://instagram.com/tanahlotsabah');
$tiktok = get_theme_mod('tiktok_url', 'https://tiktok.com/@tanahlotsabah');

// Format display number
$whatsapp_display = '+' . substr($whatsapp, 0, 2) . ' ' . substr($whatsapp, 2, 3) . '-' . substr($whatsapp, 5, 3) . ' ' . substr($whatsapp, 8);
$whatsapp_enc = base64_encode($whatsapp);
$phone = get_theme_mod('phone_number', $whatsapp);
$phone_enc = base64_encode($phone);
?>

<header class="site-header">
    <div class="container">
        <div class="header-flex">
            <!-- Left: Logo -->
            <div class="logo-left">
                <a href="<?php echo esc_url(home_url('/')); ?>">
                    <img src="<?php echo get_template_directory_uri(); ?>/headerbgpnglogo.webp" alt="tanahlotsabah">
                </a>
            </div>

            <!-- Center: Desktop Nav -->
            <nav class="desktop-nav">
                <ul class="desktop-nav-list">
                    <li><a href="<?php echo esc_url(home_url('/')); ?>" class="<?php echo is_front_page() ? 'active' : ''; ?>">Home</a></li>
                    <li><a href="<?php echo esc_url(home_url('/tanah/')); ?>" class="<?php echo is_post_type_archive('tanah') ? 'active' : ''; ?>">Beli Tanah</a></li>
                    <li><a href="<?php echo esc_url(home_url('/news/')); ?>" class="<?php echo is_page('news') ? 'active' : ''; ?>">Panduan</a></li>
                    <li><a href="<?php echo esc_url(home_url('/calculator/')); ?>" class="<?php echo is_page('calculator') ? 'active' : ''; ?>">Kalkulator</a></li>
                </ul>
            </nav>

            <!-- Right: User Icon + WhatsApp + Menu Toggle -->
            <div class="header-right-group">
                <a href="javascript:void(0)" class="header-wa" onclick="tlsRevealContact(this, 'wa', '<?php echo $whatsapp_enc; ?>')" aria-label="WhatsApp">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
                </a>

                <!-- User Icon -->
                <div class="header-login">
                    <?php if (is_user_logged_in()): ?>
                        <?php $current_user = wp_get_current_user(); ?>
                        <div class="login-user" onclick="toggleLoginMenu()" title="<?php echo esc_attr($current_user->display_name); ?>">
                            <svg width="22" height="22" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
                            <svg width="22" height="22" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/>
                            </svg>
                        </a>
                    <?php endif; ?>
                </div>

                <!-- Hamburger Toggle (mobile only) -->
                <button class="menu-toggle" id="menuToggle" aria-label="Toggle Menu">
                    <span class="menu-lines">
                        <span></span>
                        <span></span>
                        <span></span>
                    </span>
                </button>
            </div>
        </div>
    </div>
</header>

<!-- Mobile Slide-In Panel -->
<nav class="slide-panel" id="slidePanel">
    <div class="slide-panel-header">
        <span class="slide-panel-brand">Tanah Lot Sabah</span>
        <button class="slide-panel-close" id="slidePanelClose" aria-label="Close Menu">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <line x1="18" y1="6" x2="6" y2="18"></line>
                <line x1="6" y1="6" x2="18" y2="18"></line>
            </svg>
        </button>
    </div>

    <div class="slide-panel-nav">
        <ul>
            <li><a href="<?php echo esc_url(home_url('/')); ?>">Home</a></li>
            <li><a href="<?php echo esc_url(home_url('/tanah/')); ?>">Beli Tanah</a></li>
            <li><a href="<?php echo esc_url(home_url('/news/')); ?>">Panduan</a></li>
            <li><a href="<?php echo esc_url(home_url('/calculator/')); ?>">Kalkulator</a></li>
        </ul>
    </div>

    <div class="slide-panel-social">
        <div class="slide-panel-social-icons">
            <?php if ($facebook): ?>
            <a href="<?php echo esc_url($facebook); ?>" class="sp-social-icon" target="_blank" aria-label="Facebook">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="currentColor"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
            </a>
            <?php endif; ?>
            <?php if ($instagram): ?>
            <a href="<?php echo esc_url($instagram); ?>" class="sp-social-icon" target="_blank" aria-label="Instagram">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="2" width="20" height="20" rx="5" ry="5"></rect><path d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37z"></path><line x1="17.5" y1="6.5" x2="17.51" y2="6.5"></line></svg>
            </a>
            <?php endif; ?>
            <?php if ($tiktok): ?>
            <a href="<?php echo esc_url($tiktok); ?>" class="sp-social-icon" target="_blank" aria-label="TikTok">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="currentColor"><path d="M12.525.02c1.31-.02 2.61-.01 3.91-.02.08 1.53.63 3.09 1.75 4.17 1.12 1.11 2.7 1.62 4.24 1.79v4.03c-1.44-.05-2.89-.35-4.2-.97-.57-.26-1.1-.59-1.62-.93-.01 2.92.01 5.84-.02 8.75-.08 1.4-.54 2.79-1.35 3.94-1.31 1.92-3.58 3.17-5.91 3.21-1.43.08-2.86-.31-4.08-1.03-2.02-1.19-3.44-3.37-3.65-5.71-.02-.5-.03-1-.01-1.49.18-1.9 1.12-3.72 2.58-4.96 1.66-1.44 3.98-2.13 6.15-1.72.02 1.48-.04 2.96-.04 4.44-.99-.32-2.15-.23-3.02.37-.63.41-1.11 1.04-1.36 1.75-.21.51-.15 1.07-.14 1.61.24 1.64 1.82 3.02 3.5 2.87 1.12-.01 2.19-.66 2.77-1.61.19-.33.4-.67.41-1.06.1-1.79.06-3.57.07-5.36.01-4.03-.01-8.05.02-12.07z"/></svg>
            </a>
            <?php endif; ?>
        </div>
        <a href="javascript:void(0)" class="slide-panel-wa" onclick="tlsRevealContact(this, 'wa', '<?php echo $whatsapp_enc; ?>')">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
            Hubungi Kami
        </a>
    </div>
</nav>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var menuToggle = document.getElementById('menuToggle');
    var slidePanel = document.getElementById('slidePanel');
    var slidePanelClose = document.getElementById('slidePanelClose');
    var menuOverlay = document.getElementById('menuOverlay');

    function togglePanel() {
        slidePanel.classList.toggle('active');
        menuOverlay.classList.toggle('active');
        document.body.classList.toggle('menu-active');
        menuToggle.classList.toggle('active');
    }

    if (menuToggle && slidePanel) {
        menuToggle.addEventListener('click', function(e) {
            e.stopPropagation();
            togglePanel();
        });
    }

    if (slidePanelClose) {
        slidePanelClose.addEventListener('click', togglePanel);
    }

    if (menuOverlay) {
        menuOverlay.addEventListener('click', togglePanel);
    }

    var panelLinks = slidePanel.querySelectorAll('a');
    panelLinks.forEach(function(link) {
        link.addEventListener('click', function() {
            slidePanel.classList.remove('active');
            menuOverlay.classList.remove('active');
            document.body.classList.remove('menu-active');
            menuToggle.classList.remove('active');
        });
    });
});

function toggleLoginMenu() {
    var dropdown = document.getElementById('loginDropdown');
    var formDropdown = document.getElementById('loginFormDropdown');
    if (formDropdown) formDropdown.style.display = 'none';
    dropdown.style.display = dropdown.style.display === 'block' ? 'none' : 'block';
    event.stopPropagation();
}

document.addEventListener('click', function(e) {
    var dropdowns = document.querySelectorAll('.login-form-dropdown, .login-dropdown');
    dropdowns.forEach(function(d) {
        if (!d.contains(e.target) && !d.previousElementSibling?.contains(e.target)) {
            d.style.display = 'none';
        }
    });
});
</script>

<main>
