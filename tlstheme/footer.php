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
                        <option value="500000" <?php selected(get_query_var('min_price'), '500000'); ?>>RM 500,000</option>
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

<!-- FAB Menu will be rendered by TLS_FAB_System via wp_footer hook -->

<footer class="site-footer">
    <div class="container">
        <div class="footer-grid">
            <div class="footer-col">
                <h3 class="footer-brand"><?php echo esc_html(get_theme_mod('company_name', 'tanahlotsabah')); ?></h3>
                <p>Platform jual beli tanah pertanian, kediaman & komersial di Sabah. Geran individu, harga telus.</p>
            </div>
            <div class="footer-col">
                <h4 class="footer-heading">Hubungi Kami</h4>
                <div class="contact-icons">
                    <a href="https://wa.me/<?php echo esc_attr(get_theme_mod('whatsapp_number', '60123456789')); ?>" target="_blank" class="contact-icon-btn wa-icon" title="WhatsApp">
                        <svg width="24" height="24" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z"/></svg>
                        <span>WhatsApp</span>
                    </a>
                    <a href="tel:+<?php echo esc_attr(preg_replace('/\D/', '', get_theme_mod('phone_number', '60123456789'))); ?>" class="contact-icon-btn phone-icon" title="Telefon">
                        <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                        <span>Telefon</span>
                    </a>
                    <button type="button" class="contact-icon-btn email-icon" id="openContactModal" title="Emel">
                        <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                        <span>Emel</span>
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
            <?php 
            $app_settings = function_exists('tls_get_app_settings') ? tls_get_app_settings() : ['enabled' => 0, 'playstore_url' => ''];
            if ($app_settings['enabled'] && !empty($app_settings['playstore_url'])): 
            ?>
            <div class="footer-col footer-app-download">
                <h4 class="footer-heading">Muat Turun App</h4>
                <a href="<?php echo esc_url($app_settings['playstore_url']); ?>" target="_blank" class="playstore-btn">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M3.609 1.814L13.792 12 3.61 22.186a.996.996 0 01-.61-.92V2.734a1 1 0 01.609-.92zm10.89 10.893L2.168 3.402l1.114 1.125 11.128 11.13 1.089-1.085-11.89-11.86zm3.496-3.464l1.27 1.296 1.122-1.103-1.27-1.296-1.122 1.103zM5.793 19.574l1.125-1.125 9.287-9.288 1.125 1.125-9.287 9.288-1.125-1.125zm11.855-2.215l1.122-1.125 2.303-2.303-1.125-1.125-2.303 2.303-1.117 1.12 1.12 1.125z"/>
                    </svg>
                    <span>Get it on<br><strong>Google Play</strong></span>
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
document.addEventListener('DOMContentLoaded', function() {
    // 2. Modal Logic - Calculator & Search
    var calcModal = document.getElementById('calcModal');
    var calcModalOverlay = document.getElementById('calcModalOverlay');
    var closeCalcBtn = document.getElementById('closeCalcBtn');
    
    if (closeCalcBtn && calcModal) {
        closeCalcBtn.addEventListener('click', function() {
            calcModal.classList.remove('active');
        });
    }
    
    if (calcModalOverlay && calcModal) {
        calcModalOverlay.addEventListener('click', function() {
            calcModal.classList.remove('active');
        });
    }
    
    var searchModal = document.getElementById('searchModal');
    var searchModalOverlay = document.getElementById('searchModalOverlay');
    var closeSearchBtn = document.getElementById('closeSearchBtn');
    
    if (closeSearchBtn && searchModal) {
        closeSearchBtn.addEventListener('click', function() {
            searchModal.classList.remove('active');
        });
    }
    
    if (searchModalOverlay && searchModal) {
        searchModalOverlay.addEventListener('click', function() {
            searchModal.classList.remove('active');
        });
    }

    // 3. Contact Modal Logic
    var contactModal = document.getElementById('contactModal');
    var contactModalOverlay = document.getElementById('contactModalOverlay');
    var closeContactBtn = document.getElementById('closeContactBtn');
    var openContactBtn = document.getElementById('openContactModal');
    
    if (openContactBtn && contactModal) {
        openContactBtn.addEventListener('click', function() {
            contactModal.classList.add('active');
        });
    }
    
    if (closeContactBtn && contactModal) {
        closeContactBtn.addEventListener('click', function() {
            contactModal.classList.remove('active');
        });
    }
    
    if (contactModalOverlay && contactModal) {
        contactModalOverlay.addEventListener('click', function() {
            contactModal.classList.remove('active');
        });
    }
    
    // 4. Contact Form Submission (AJAX)
    var contactForm = document.getElementById('contactForm');
    if (contactForm) {
        contactForm.addEventListener('submit', function(e) {
            e.preventDefault();
            var formData = new FormData(contactForm);
            var submitBtn = contactForm.querySelector('.btn-submit');
            var btnText = submitBtn.querySelector('.btn-text');
            var btnLoading = submitBtn.querySelector('.btn-loading');
            
            btnText.style.display = 'none';
            btnLoading.style.display = 'inline';
            submitBtn.disabled = true;
            
            fetch(ldcAjax.ajaxurl, {
                method: 'POST',
                body: formData
            })
            .then(function(response) { return response.json(); })
            .then(function(data) {
                if (data.success) {
                    contactForm.style.display = 'none';
                    document.getElementById('contactFormSuccess').style.display = 'block';
                } else {
                    alert(data.data || 'Ralat berlaku. Sila cuba lagi.');
                    btnText.style.display = 'inline';
                    btnLoading.style.display = 'none';
                    submitBtn.disabled = false;
                }
            })
            .catch(function() {
                alert('Ralat berlaku. Sila cuba lagi.');
                btnText.style.display = 'inline';
                btnLoading.style.display = 'none';
                submitBtn.disabled = false;
            });
        });
    }
    
    // 5. ESC key closes all modals
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            if (calcModal) calcModal.classList.remove('active');
            if (searchModal) searchModal.classList.remove('active');
            if (contactModal) contactModal.classList.remove('active');
        }
    });

    // 2. Footer Fullscreen Logic - Works on all devices when body has enable-fullscreen-footer class
    var footer = document.querySelector('footer.site-footer');
    if (footer && document.body.classList.contains('enable-fullscreen-footer')) {
        window.addEventListener('scroll', function() {
            if ((window.innerHeight + window.scrollY) >= document.body.offsetHeight - 50) {
                footer.classList.add('is-at-bottom');
            } else {
                footer.classList.remove('is-at-bottom');
            }
        });
    }
});
</script>

<!-- Mobile Sticky Bar -->
<?php 
$show_mobile_footer = get_theme_mod('show_mobile_footer', true);
if ($show_mobile_footer && !is_singular('tanah')):
    $wa = get_theme_mod('whatsapp_number', '601126661706');
    $phone = get_theme_mod('phone_number', $wa);
?>
<div class="mobile-sticky-bar">
    <a href="https://wa.me/<?php echo esc_attr($wa); ?>" class="action-item wa">
        <svg width="24" height="24" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z"/></svg>
        WhatsApp
    </a>
    <a href="tel:+<?php echo esc_attr($phone); ?>" class="action-item call">
        <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
        Call
    </a>
</div>
<?php endif; ?>
.mobile-sticky-bar {
    position: fixed;
    bottom: 0;
    left: 0;
    right: 0;
    background: #fff;
    box-shadow: 0 -2px 10px rgba(0,0,0,0.1);
    display: flex;
    z-index: 99;
    padding: 8px 16px;
    gap: 10px;
}

.action-item.wa, .action-item.call {
    flex: 1;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    padding: 12px;
    border-radius: 8px;
    text-decoration: none;
    font-weight: 600;
    font-size: 14px;
    color: #fff;
}

.action-item.wa { background: #25d366; }
.action-item.call { background: #007bff; }

/* Contact Modal */
.contact-modal {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    z-index: 10000;
    display: none;
    align-items: center;
    justify-content: center;
}

.contact-modal.active {
    display: flex;
}

.contact-modal-overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0,0,0,0.5);
}

.contact-modal-content {
    position: relative;
    background: #fff;
    border-radius: 16px;
    max-width: 500px;
    width: 90%;
    max-height: 90vh;
    overflow-y: auto;
    box-shadow: 0 10px 40px rgba(0,0,0,0.2);
}

.contact-modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 20px 24px;
    border-bottom: 1px solid #e5e7eb;
}

.contact-modal-header h2 {
    margin: 0;
    font-size: 1.25rem;
    font-weight: 700;
    color: #1a1a1a;
}

.contact-modal-close {
    width: 36px;
    height: 36px;
    border: none;
    background: #f3f4f6;
    border-radius: 50%;
    cursor: pointer;
    font-size: 20px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #6b7280;
    transition: background 0.2s;
}

.contact-modal-close:hover {
    background: #e5e7eb;
}

.contact-modal-body {
    padding: 24px;
}

#contactForm .form-group {
    margin-bottom: 16px;
}

#contactForm label {
    display: block;
    font-size: 0.875rem;
    font-weight: 600;
    color: #374151;
    margin-bottom: 6px;
}

#contactForm input,
#contactForm select,
#contactForm textarea {
    width: 100%;
    padding: 12px;
    border: 1px solid #d1d5db;
    border-radius: 8px;
    font-size: 0.95rem;
    transition: border-color 0.2s;
    box-sizing: border-box;
}

#contactForm input:focus,
#contactForm select:focus,
#contactForm textarea:focus {
    outline: none;
    border-color: #16a34a;
}

.btn-submit {
    width: 100%;
    padding: 14px;
    background: #16a34a;
    color: #fff;
    border: none;
    border-radius: 8px;
    font-size: 1rem;
    font-weight: 600;
    cursor: pointer;
    transition: background 0.2s;
}

.btn-submit:hover:not(:disabled) {
    background: #0d6e61;
}

.btn-submit:disabled {
    opacity: 0.7;
    cursor: not-allowed;
}

.form-success {
    text-align: center;
    padding: 30px 20px;
}

.form-success svg {
    margin-bottom: 16px;
}

.form-success p {
    margin: 8px 0;
    color: #374151;
}

/* Contact Icons in Footer */
.contact-icons {
    display: flex;
    gap: 12px;
    flex-wrap: wrap;
}

.contact-icon-btn {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 10px 16px;
    border-radius: 8px;
    text-decoration: none;
    font-size: 0.9rem;
    font-weight: 600;
    border: none;
    cursor: pointer;
    transition: transform 0.2s, box-shadow 0.2s;
}

.contact-icon-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
}

.contact-icon-btn svg {
    flex-shrink: 0;
}

.wa-icon {
    background: #25d366;
    color: #fff;
}

.phone-icon {
    background: #007bff;
    color: #fff;
}

.email-icon {
    background: #6c757d;
    color: #fff;
}

@media (max-width: 480px) {
    .contact-icons {
        flex-direction: column;
    }
    
    .contact-icon-btn {
        width: 100%;
        justify-content: center;
    }
    
    .contact-modal-content {
        width: 95%;
        border-radius: 12px;
    }
    
    .contact-modal-body {
        padding: 16px;
    }
}
</style>

<!--[CDATA[-->
</body>
</html>
<!--]]-->
