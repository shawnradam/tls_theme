</main>

<!-- Calculator Modal -->
<div class="calc-modal" id="calcModal">
    <div class="calc-modal-overlay" id="calcModalOverlay"></div>
    <div class="calc-modal-content">
        <div class="calc-modal-header">
            <h2>Kalkulator Kos Pembangunan</h2>
            <button class="calc-modal-close" id="closeCalcBtn">&times;</button>
        </div>
        <div class="calc-modal-body">
            <?php echo do_shortcode('[land_dev_calculator]'); ?>
        </div>
    </div>
</div>

<!-- Advanced Search Modal -->
<div class="search-modal" id="searchModal">
    <div class="search-modal-overlay" id="searchModalOverlay"></div>
    <div class="search-modal-content">
        <div class="search-modal-header">
            <h2>Advanced Search</h2>
            <button class="search-modal-close" id="closeSearchBtn">&times;</button>
        </div>
        <form method="get" action="<?php echo esc_url(home_url('/')); ?>" class="search-modal-form advanced-search-form">
            <input type="hidden" name="post_type" value="tanah">

            <!-- Search Keywords -->
            <div class="search-section">
                <label class="search-label">Keywords</label>
                <input type="text" name="s" placeholder="Search by location, title, or description..." class="search-input" value="<?php echo esc_attr(get_query_var('s')); ?>">
            </div>

            <!-- Property Type & District -->
            <div class="search-row">
                <div class="search-col">
                    <label class="search-label">Property Type</label>
                    <select name="land_type" class="search-select">
                        <option value="">All Types</option>
                        <?php
                        $land_types = get_terms(['taxonomy' => 'land_type', 'hide_empty' => false]);
                        $selected_type = get_query_var('land_type');
                        foreach ($land_types as $type):
                        ?>
                            <option value="<?php echo esc_attr($type->slug); ?>" <?php selected($selected_type, $type->slug); ?>>
                                <?php echo esc_html($type->name); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="search-col">
                    <label class="search-label">Daerah / District</label>
                    <select name="daerah" class="search-select">
                        <option value="">All Districts</option>
                        <?php
                        $parent_districts = get_terms([
                            'taxonomy' => 'daerah',
                            'hide_empty' => false,
                            'parent' => 0
                        ]);
                        $selected_daerah = get_query_var('daerah');

                        foreach ($parent_districts as $parent):
                            $towns = get_terms([
                                'taxonomy' => 'daerah',
                                'hide_empty' => false,
                                'parent' => $parent->term_id
                            ]);

                            if (!empty($towns)):
                                ?>
                                <optgroup label="<?php echo esc_attr($parent->name); ?>">
                                    <?php foreach ($towns as $town): ?>
                                        <option value="<?php echo esc_attr($town->slug); ?>" <?php selected($selected_daerah, $town->slug); ?>>
                                            <?php echo esc_html($town->name); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </optgroup>
                            <?php else: ?>
                                <option value="<?php echo esc_attr($parent->slug); ?>" <?php selected($selected_daerah, $parent->slug); ?>>
                                    <?php echo esc_html($parent->name); ?>
                                </option>
                            <?php endif;
                        endforeach;
                        ?>
                    </select>
                </div>
            </div>

            <!-- Grant Type & Zoning -->
            <div class="search-row">
                <div class="search-col">
                    <label class="search-label">Jenis Geran / Grant Type</label>
                    <select name="geran" class="search-select">
                        <option value="">All Grant Types</option>
                        <option value="CL" <?php selected(get_query_var('geran'), 'CL'); ?>>CL - Country Lease</option>
                        <option value="NT" <?php selected(get_query_var('geran'), 'NT'); ?>>NT - Native Title</option>
                        <option value="P" <?php selected(get_query_var('geran'), 'P'); ?>>Pajakan</option>
                        <option value="Hakmilik" <?php selected(get_query_var('geran'), 'Hakmilik'); ?>>Freehold</option>
                    </select>
                </div>
                <div class="search-col">
                    <label class="search-label">Zoning</label>
                    <select name="zoning" class="search-select">
                        <option value="">All Zoning</option>
                        <option value="Kediaman" <?php selected(get_query_var('zoning'), 'Kediaman'); ?>>Kediaman (Residential)</option>
                        <option value="Komersial" <?php selected(get_query_var('zoning'), 'Komersial'); ?>>Komersial (Commercial)</option>
                        <option value="Pertanian" <?php selected(get_query_var('zoning'), 'Pertanian'); ?>>Pertanian (Agricultural)</option>
                        <option value="Perindustrian" <?php selected(get_query_var('zoning'), 'Perindustrian'); ?>>Perindustrian (Industrial)</option>
                        <option value="Campuran" <?php selected(get_query_var('zoning'), 'Campuran'); ?>>Campuran (Mixed)</option>
                    </select>
                </div>
            </div>

            <!-- Price Range -->
            <div class="search-row">
                <div class="search-col">
                    <label class="search-label">Min Price</label>
                    <select name="min_price" class="search-select">
                        <option value="">No Minimum</option>
                        <option value="50000" <?php selected(get_query_var('min_price'), '50000'); ?>>RM 50,000</option>
                        <option value="100000" <?php selected(get_query_var('min_price'), '100000'); ?>>RM 100,000</option>
                        <option value="200000" <?php selected(get_query_var('min_price'), '200000'); ?>>RM 200,000</option>
                        <option value="300000" <?php selected(get_query_var('min_price'), '300000'); ?>>RM 300,000</option>
                        <option value="500000" <?php selected(get_query_var('min_price'), '500000'); ?>>RM 50,0000</option>
                        <option value="1000000" <?php selected(get_query_var('min_price'), '1000000'); ?>>RM 1,000,000</option>
                    </select>
                </div>
                <div class="search-col">
                    <label class="search-label">Max Price</label>
                    <select name="max_price" class="search-select">
                        <option value="">No Maximum</option>
                        <option value="100000" <?php selected(get_query_var('max_price'), '100000'); ?>>RM 100,000</option>
                        <option value="200000" <?php selected(get_query_var('max_price'), '200000'); ?>>RM 200,000</option>
                        <option value="300000" <?php selected(get_query_var('max_price'), '300000'); ?>>RM 300,000</option>
                        <option value="500000" <?php selected(get_query_var('max_price'), '500000'); ?>>RM 500,000</option>
                        <option value="1000000" <?php selected(get_query_var('max_price'), '1000000'); ?>>RM 1,000,000</option>
                        <option value="2000000" <?php selected(get_query_var('max_price'), '2000000'); ?>>RM 2,000,000</option>
                        <option value="5000000" <?php selected(get_query_var('max_price'), '5000000'); ?>>RM 5,000,000</option>
                    </select>
                </div>
            </div>

            <!-- Size Range -->
            <div class="search-row">
                <div class="search-col">
                    <label class="search-label">Min Size (Ekar)</label>
                    <select name="min_size" class="search-select">
                        <option value="">No Minimum</option>
                        <option value="0.5" <?php selected(get_query_var('min_size'), '0.5'); ?>>0.5 ekar</option>
                        <option value="1" <?php selected(get_query_var('min_size'), '1'); ?>>1 ekar</option>
                        <option value="2" <?php selected(get_query_var('min_size'), '2'); ?>>2 ekar</option>
                        <option value="5" <?php selected(get_query_var('min_size'), '5'); ?>>5 ekar</option>
                        <option value="10" <?php selected(get_query_var('min_size'), '10'); ?>>10 ekar</option>
                        <option value="20" <?php selected(get_query_var('min_size'), '20'); ?>>20 ekar</option>
                    </select>
                </div>
                <div class="search-col">
                    <label class="search-label">Max Size (Ekar)</label>
                    <select name="max_size" class="search-select">
                        <option value="">No Maximum</option>
                        <option value="1" <?php selected(get_query_var('max_size'), '1'); ?>>1 ekar</option>
                        <option value="2" <?php selected(get_query_var('max_size'), '2'); ?>>2 ekar</option>
                        <option value="5" <?php selected(get_query_var('max_size'), '5'); ?>>5 ekar</option>
                        <option value="10" <?php selected(get_query_var('max_size'), '10'); ?>>10 ekar</option>
                        <option value="20" <?php selected(get_query_var('max_size'), '20'); ?>>20 ekar</option>
                        <option value="50" <?php selected(get_query_var('max_size'), '50'); ?>>50 ekar</option>
                        <option value="100" <?php selected(get_query_var('max_size'), '100'); ?>>100 ekar</option>
                    </select>
                </div>
            </div>

            <!-- Additional Filters -->
            <div class="search-section">
                <label class="search-checkbox">
                    <input type="checkbox" name="verified_only" value="1" <?php checked(get_query_var('verified_only'), '1'); ?>>
                    <span>Verified Properties Only</span>
                </label>
            </div>

            <!-- Sort Options -->
            <div class="search-section">
                <label class="search-label">Sort By</label>
                <select name="orderby" class="search-select">
                    <option value="date" <?php selected(get_query_var('orderby'), 'date'); ?>>Newest First</option>
                    <option value="price_low" <?php selected(get_query_var('orderby'), 'price_low'); ?>>Price: Low to High</option>
                    <option value="price_high" <?php selected(get_query_var('orderby'), 'price_high'); ?>>Price: High to Low</option>
                    <option value="size_low" <?php selected(get_query_var('orderby'), 'size_low'); ?>>Size: Smallest First</option>
                    <option value="size_high" <?php selected(get_query_var('orderby'), 'size_high'); ?>>Size: Largest First</option>
                </select>
            </div>

            <!-- Action Buttons -->
            <div class="search-actions">
                <button type="button" class="search-reset-btn" onclick="this.form.reset();">Reset Filters</button>
                <button type="submit" class="search-submit-btn">Search Properties</button>
            </div>
        </form>
    </div>
</div>

<!-- Contact Modal -->
<div class="contact-modal" id="contactModal">
    <div class="contact-modal-overlay" id="contactModalOverlay"></div>
    <div class="contact-modal-content">
        <div class="contact-modal-header">
            <h2>Hantar Emel</h2>
            <button class="contact-modal-close" id="closeContactBtn">&times;</button>
        </div>
        <div class="contact-modal-body">
            <form id="contactForm" method="post">
                <input type="hidden" name="action" value="tls_send_contact_email">
                <input type="hidden" name="property_id" id="contact_property_id" value="">
                <?php wp_nonce_field('tls_contact_nonce', 'nonce'); ?>
                <div class="form-group">
                    <label for="contact_name">Nama Penuh</label>
                    <input type="text" id="contact_name" name="name" required placeholder="Masukkan nama anda">
                </div>
                <div class="form-group">
                    <label for="contact_email">Emel</label>
                    <input type="email" id="contact_email" name="email" required placeholder="email@example.com">
                </div>
                <div class="form-group">
                    <label for="contact_phone">No. Telefon</label>
                    <input type="tel" id="contact_phone" name="phone" placeholder="01xxxxxxxxx">
                </div>
                <div class="form-group">
                    <label for="contact_subject">Subjek</label>
                    <select id="contact_subject" name="subject" required>
                        <option value="">Pilih subjek</option>
                        <option value="Pertanyaan Harta Tanah">Pertanyaan Harta Tanah</option>
                        <option value="Tempahan Lawatan">Tempahan Lawatan</option>
                        <option value="Maklumat Geran">Maklumat Geran</option>
                        <option value="Harga dan Tawar Menawar">Harga dan Tawar Menawar</option>
                        <option value="Lain-lain">Lain-lain</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="contact_message">Mesej</label>
                    <textarea id="contact_message" name="message" required rows="5" placeholder="Tulis mesej anda di sini..."></textarea>
                </div>
                <button type="submit" class="btn-submit">
                    <span class="btn-text">Hantar Emel</span>
                    <span class="btn-loading" style="display:none;">Menghantar...</span>
                </button>
            </form>
            <div id="contactFormSuccess" style="display:none;" class="form-success">
                <svg width="48" height="48" fill="none" stroke="#16a34a" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <p>Emel berjaya dihantar!</p>
                <p>Kami akan menghubungi anda tidak lama lagi.</p>
            </div>
        </div>
    </div>
</div>

<!-- App Announcement Modal -->
<?php $app_settings = tls_get_app_settings(); ?>
<div class="contact-modal" id="appModal">
    <div class="contact-modal-overlay" id="appModalOverlay"></div>
    <div class="contact-modal-content">
        <div class="contact-modal-header">
            <h2>Pengumuman Aplikasi</h2>
            <button class="contact-modal-close" id="closeAppBtn">&times;</button>
        </div>
        <div class="contact-modal-body">
            <div style="text-align: center; margin-bottom: 20px;">
                <div style="width: 60px; height: 60px; background: #f0fdf4; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 15px;">
                    <span class="dashicons dashicons-smartphone" style="font-size: 30px; width: 30px; height: 30px; color: #16a34a;"></span>
                </div>
                <h3 style="color: #0F766E; margin-bottom: 10px;">Tanah Lot Sabah Mobile App</h3>
                <p style="color: #475569; line-height: 1.6; font-size: 0.95rem;">
                    <?php echo nl2br(esc_html($app_settings['announcement'])); ?>
                </p>
            </div>

            <?php if ($app_settings['status'] === 'available'): ?>
                <div style="text-align: center; padding-top: 10px; border-top: 1px solid #e5e7eb;">
                    <p style="margin-bottom: 15px; font-weight: 600; color: #1e293b;">Tersedia di Google Play Store:</p>
                    <a href="<?php echo esc_url($app_settings['playstore_url']); ?>" target="_blank" class="playstore-badge-link" style="margin-top: 0;">
                        <img src="https://upload.wikimedia.org/wikipedia/commons/7/78/Google_Play_Store_badge_EN.svg" alt="Get it on Google Play" style="height: 50px;">
                    </a>
                </div>
            <?php else: ?>
                <div style="background: #f8fafc; padding: 20px; border-radius: 12px; border: 1px solid #e2e8f0;">
                    <h4 style="margin-top: 0; color: #1e293b; font-size: 0.95rem; margin-bottom: 12px; text-align: center;">Daftar untuk Makluman Pelancaran</h4>
                    <form id="appSubscribeForm">
                        <div class="form-group">
                            <label for="subscribe_email">Alamat Emel</label>
                            <input type="email" name="email" id="subscribe_email" placeholder="nama@anda.com" required>
                        </div>
                        <button type="submit" class="btn-submit">
                            <span class="btn-text">Langgan Makluman</span>
                            <span class="btn-loading" style="display:none;">Menghantar...</span>
                        </button>
                    </form>
                    <div id="subscribeSuccess" style="display:none; text-align: center; color: #16a34a; font-weight: 600; margin-top: 10px;">
                        ✓ Berjaya dilanggan! Kami akan maklumkan anda nanti.
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<footer class="site-footer">
    <div class="container">
        <div class="footer-grid">
            <div class="footer-col">
                <h3 class="footer-brand"><?php echo esc_html(get_theme_mod('company_name', 'tanahlotsabah')); ?></h3>
                <p>Platform jual beli tanah pertanian, kediaman & komersial di Sabah. Geran individu, harga telus.</p>
            </div>
            <div class="footer-col">
                <h4 class="footer-heading">Hubungi Kami</h4>
                <div class="contact-icons-round">
                    <?php 
                    $wa_footer = get_theme_mod('whatsapp_number', '60123456789');
                    $phone_footer = get_theme_mod('phone_number', '60123456789');
                    $wa_footer_enc = base64_encode($wa_footer);
                    $phone_footer_enc = base64_encode($phone_footer);
                    ?>
                    <a href="javascript:void(0)" onclick="tlsRevealContact(this, 'wa', '<?php echo $wa_footer_enc; ?>')" class="contact-circle wa-circle" title="WhatsApp">
                        <svg width="22" height="22" viewBox="0 0 24 24" fill="currentColor"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
                    </a>
                    <a href="javascript:void(0)" onclick="tlsRevealContact(this, 'tel', '<?php echo $phone_footer_enc; ?>')" class="contact-circle phone-circle" title="Telefon">
                        <span class="material-icons">call</span>
                    </a>
                    <button type="button" class="contact-circle email-circle" id="openContactModal" title="Emel">
                        <span class="material-icons">mail</span>
                    </button>
                </div>
            </div>
            <div class="footer-col">
                <h4 class="footer-heading">Pautan</h4>
                <p><a href="<?php echo esc_url(home_url('/news')); ?>">News & Updates</a></p>
                <p><a href="<?php echo esc_url(home_url('/calculator')); ?>">Kalkulator Kos</a></p>
            </div>
            <div class="footer-col">
                <h4 class="footer-heading">Lesen Ejen</h4>
                <?php if (get_theme_mod('license_number')): ?>
                <p>No. Lesen: <?php echo esc_html(get_theme_mod('license_number')); ?></p>
                <?php endif; ?>
                <p>Berdaftar dengan LPPEH<br>
                Ejen berlesen dan profesional<br>
                Urusan sah dan selamat</p>
            </div>
            <?php if ($app_settings['enabled']): ?>
            <div class="footer-col footer-app-download">
                <h4 class="footer-heading">Muat Turun App</h4>
                <a href="javascript:void(0)" id="openAppModal" class="playstore-badge-link">
                    <img src="https://upload.wikimedia.org/wikipedia/commons/7/78/Google_Play_Store_badge_EN.svg" alt="Get it on Google Play" class="playstore-badge-img">
                </a>
            </div>
            <?php endif; ?>
        </div>
        <div class="footer-bottom">
            <p>&copy; <?php echo date('Y'); ?> <?php echo esc_html(get_theme_mod('company_name', 'tanahlotsabah')); ?>. Hak Cipta Terpelihara.</p>
        </div>
    </div>
</footer>

<?php wp_footer(); ?>

<script>
// Theme toggle system
(function() {
  var currentTheme = localStorage.getItem('tls-theme') || 'auto';

  function tlsApplyTheme(mode) {
    var root = document.documentElement;
    if (mode === 'dark') {
      root.setAttribute('data-theme', 'dark');
    } else if (mode === 'auto') {
      if (window.matchMedia('(prefers-color-scheme: dark)').matches) {
        root.setAttribute('data-theme', 'dark');
      } else {
        root.removeAttribute('data-theme');
      }
    } else {
      root.removeAttribute('data-theme');
    }
    localStorage.setItem('tls-theme', mode);
    currentTheme = mode;
    tlsUpdateIcons(mode);
  }

  function tlsCycleTheme() {
    var next = currentTheme === 'light' ? 'dark' : currentTheme === 'dark' ? 'auto' : 'light';
    tlsApplyTheme(next);
  }

  function tlsUpdateIcons(mode) {
    document.querySelectorAll('.theme-toggle-btn').forEach(function(btn) {
      var moon = btn.querySelector('.theme-icon-moon');
      var sun = btn.querySelector('.theme-icon-sun');
      var auto = btn.querySelector('.theme-icon-auto');
      if (moon) moon.style.display = mode === 'light' ? '' : 'none';
      if (sun) sun.style.display = mode === 'dark' ? '' : 'none';
      if (auto) auto.style.display = mode === 'auto' ? '' : 'none';
    });
  }

  function tlsListenSystem() {
    window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', function(e) {
      if (localStorage.getItem('tls-theme') === 'auto') {
        if (e.matches) {
          document.documentElement.setAttribute('data-theme', 'dark');
        } else {
          document.documentElement.removeAttribute('data-theme');
        }
      }
    });
  }

  document.addEventListener('DOMContentLoaded', function() {
    tlsUpdateIcons(currentTheme);
    tlsListenSystem();

    document.querySelectorAll('.theme-toggle-btn').forEach(function(btn) {
      btn.addEventListener('click', tlsCycleTheme);
    });
  });
})();
</script>

<script>
// Click to Reveal Logic (global, available immediately)
window.tlsRevealContact = function(el, type, encoded) {
    const num = atob(encoded);
    let link = type === 'wa' ? 'https://wa.me/' + num : 'tel:+' + num;
    if (type === 'wa_full') link = 'https://wa.me/' + num;
    el.href = link;
    window.location.href = link;
    return false;
};

document.addEventListener('DOMContentLoaded', function() {
    // 1. Modal Toggle Helper
    function initModal(modalId, openBtnId, closeBtnId, overlayId) {
        var modal = document.getElementById(modalId);
        var openBtn = document.getElementById(openBtnId);
        var closeBtn = document.getElementById(closeBtnId);
        var overlay = document.getElementById(overlayId);

        if (openBtn && modal) {
            openBtn.addEventListener('click', function() { modal.classList.add('active'); });
        }
        if (closeBtn && modal) {
            closeBtn.addEventListener('click', function() { modal.classList.remove('active'); });
        }
        if (overlay && modal) {
            overlay.addEventListener('click', function() { modal.classList.remove('active'); });
        }
    }

    initModal('calcModal', 'openCalcModal', 'closeCalcBtn', 'calcModalOverlay');
    initModal('searchModal', 'openSearchModal', 'closeSearchBtn', 'searchModalOverlay');
    initModal('contactModal', 'openContactModal', 'closeContactBtn', 'contactModalOverlay');
    initModal('appModal', 'openAppModal', 'closeAppBtn', 'appModalOverlay');

    // Check URL for property parameter and auto-open contact modal
    var urlParams = new URLSearchParams(window.location.search);
    var propertyId = urlParams.get('property');
    var contactModalParam = urlParams.get('contact');
    
    if (propertyId || contactModalParam === 'modal') {
        var contactModal = document.getElementById('contactModal');
        var propertyInput = document.getElementById('contact_property_id');
        var messageTextarea = document.getElementById('contact_message');
        
        if (contactModal) {
            if (propertyId && propertyInput) {
                propertyInput.value = propertyId;
            }
            if (propertyId && messageTextarea) {
                messageTextarea.value = 'Saya berminat dengan hartanah (Property ID: ' + propertyId + '). Sila hubungi saya untuk maklumat lanjut.';
            }
            contactModal.classList.add('active');
            
            // Remove query params from URL without reload
            var newUrl = window.location.pathname;
            window.history.replaceState({}, document.title, newUrl);
        }
    }

    // 2. Contact Form Submission
    var contactForm = document.getElementById('contactForm');
    if (contactForm) {
        contactForm.addEventListener('submit', function(e) {
            e.preventDefault();
            var submitBtn = contactForm.querySelector('.btn-submit');
            var btnText = submitBtn.querySelector('.btn-text');
            var btnLoading = submitBtn.querySelector('.btn-loading');
            
            btnText.style.display = 'none';
            btnLoading.style.display = 'inline';
            submitBtn.disabled = true;
            
            fetch(ldcAjax.ajaxurl, {
                method: 'POST',
                body: new FormData(contactForm)
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    contactForm.style.display = 'none';
                    document.getElementById('contactFormSuccess').style.display = 'block';
                } else {
                    alert(data.data || 'Ralat berlaku.');
                    btnText.style.display = 'inline';
                    btnLoading.style.display = 'none';
                    submitBtn.disabled = false;
                }
            })
            .catch(() => {
                alert('Ralat berlaku.');
                submitBtn.disabled = false;
            });
        });
    }

    // 3. App Subscription Form
    var appForm = document.getElementById('appSubscribeForm');
    if (appForm) {
        appForm.addEventListener('submit', function(e) {
            e.preventDefault();
            var submitBtn = appForm.querySelector('.btn-submit');
            var btnText = submitBtn.querySelector('.btn-text');
            var btnLoading = submitBtn.querySelector('.btn-loading');
            
            btnText.style.display = 'none';
            btnLoading.style.display = 'inline';
            submitBtn.disabled = true;
            
            var formData = new FormData(appForm);
            formData.append('action', 'tls_app_subscribe');

            fetch(ldcAjax.ajaxurl, {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    appForm.style.display = 'none';
                    document.getElementById('subscribeSuccess').style.display = 'block';
                } else {
                    alert(data.data);
                    btnText.style.display = 'inline';
                    btnLoading.style.display = 'none';
                    submitBtn.disabled = false;
                }
            })
            .catch(() => {
                alert('Ralat berlaku.');
                submitBtn.disabled = false;
            });
        });
    }
    
    // 4. ESC key closes all modals
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            document.querySelectorAll('.contact-modal, .calc-modal, .search-modal').forEach(m => m.classList.remove('active'));
        }
    });

    // 5. Sticky Footer & Bar Auto-Hide Logic
    var footer = document.querySelector('footer.site-footer');
    var mobileBar = document.querySelector('.mobile-sticky-bar');
    var fabContainer = document.querySelector('.tls-fab-container');
    var fabStickyFooter = document.querySelector('.tls-sticky-footer');
    
    if (footer) {
        window.addEventListener('scroll', function() {
            var footerRect = footer.getBoundingClientRect();
            // If the top of the footer is visible in the viewport
            if (footerRect.top < window.innerHeight) {
                if (mobileBar) mobileBar.classList.add('hidden');
                if (fabContainer) fabContainer.classList.add('hidden');
                if (fabStickyFooter) fabStickyFooter.classList.add('hidden');
            } else {
                if (mobileBar) mobileBar.classList.remove('hidden');
                if (fabContainer) fabContainer.classList.remove('hidden');
                if (fabStickyFooter) fabStickyFooter.classList.remove('hidden');
            }
        });
    }
});
</script>

<!-- Mobile Sticky Bar -->
<?php 
$show_mobile_footer = get_theme_mod('show_mobile_footer', true);
if ($show_mobile_footer && !is_singular('tanah')):
    $wa_raw = get_theme_mod('whatsapp_number', '601126661706');
    $phone_raw = get_theme_mod('phone_number', $wa_raw);
    $wa_enc = base64_encode($wa_raw);
    $phone_enc = base64_encode($phone_raw);
?>
<div class="mobile-sticky-bar">
    <a href="javascript:void(0)" onclick="tlsRevealContact(this, 'wa', '<?php echo $wa_enc; ?>')" class="action-item wa">
        <svg width="24" height="24" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z"/></svg>
        WhatsApp
    </a>
    <a href="javascript:void(0)" onclick="tlsRevealContact(this, 'tel', '<?php echo $phone_enc; ?>')" class="action-item call">
        <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
        Call
    </a>
</div>
<?php endif; ?>

</body>
</html>
