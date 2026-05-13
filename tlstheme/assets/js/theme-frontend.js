/**
 * Tanah Lot Sabah - Frontend Interactive Scripts
 * This file contains logic for:
 * 1. Homepage Map Toggles & Navigation
 * 2. AJAX News 'Load More' system
 * 3. Property Gallery Slider
 * 4. Property ID Document Requests
 * 5. Eligibility Checks & Calculators
 */

(function($) {
    'use strict';

    // ============================================
    // 1. HOMEPAGE GLOBAL FUNCTIONS
    // ============================================

    window.scrollToMap = function(event) {
        if (event) {
            event.preventDefault();
        }
        var mapSection = document.getElementById('map-portal');
        if (!mapSection) mapSection = document.getElementById('propertyMapSection');
        
        if (mapSection) {
            mapSection.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
    };

    window.toggleMobilePortalView = function() {
        var sidebar = document.querySelector('.map-portal-sidebar');
        var btn = document.getElementById('portal-view-btn');
        if (!sidebar || !btn) return;
        
        var isShowing = sidebar.classList.contains('show');
        
        if (isShowing) {
            sidebar.classList.remove('show');
            btn.innerHTML = '<i class="fas fa-list"></i> Senarai';
            btn.classList.remove('map-hidden');
            var mapSection = document.getElementById('map-portal');
            if (mapSection) {
                mapSection.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
            setTimeout(function() {
                if (window.tlsMap) {
                    window.tlsMap.invalidateSize();
                }
            }, 400);
        } else {
            sidebar.classList.add('show');
            btn.innerHTML = '<i class="fas fa-chevron-left"></i> Peta';
            btn.classList.add('map-hidden');
            var sidebarEl = document.querySelector('.map-portal-sidebar');
            if (sidebarEl) {
                sidebarEl.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        }
    };

    // ============================================
    // 2. AJAX NEWS LOADER
    // ============================================
    
    function initNewsAjax() {
        var showMoreBtn = document.getElementById('showMoreNews');
        var newsGrid = document.getElementById('newsGrid');
        
        if (showMoreBtn && newsGrid) {
            showMoreBtn.addEventListener('click', function() {
                var currentPage = parseInt(newsGrid.getAttribute('data-page'));
                var nextPage = currentPage + 1;
                var btnText = showMoreBtn.querySelector('.btn-text');
                var btnLoading = showMoreBtn.querySelector('.btn-loading');
                var btnIcon = showMoreBtn.querySelector('.material-icons');

                showMoreBtn.disabled = true;
                if (btnText) btnText.style.display = 'none';
                if (btnIcon) btnIcon.style.display = 'none';
                if (btnLoading) btnLoading.style.display = 'inline';

                var formData = new FormData();
                formData.append('action', 'tls_load_more_news');
                formData.append('page', nextPage);

                // Use the global ajaxurl provided by WordPress
                fetch(ldcAjax.ajaxurl, {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        var tempDiv = document.createElement('div');
                        tempDiv.innerHTML = data.data.html;
                        
                        while (tempDiv.firstChild) {
                            newsGrid.appendChild(tempDiv.firstChild);
                        }

                        newsGrid.setAttribute('data-page', nextPage);

                        if (!data.data.more) {
                            showMoreBtn.style.display = 'none';
                        }
                    } else {
                        showMoreBtn.style.display = 'none';
                    }
                })
                .catch(error => console.error('AJAX Error:', error))
                .finally(() => {
                    showMoreBtn.disabled = false;
                    if (btnText) btnText.style.display = 'inline';
                    if (btnIcon) btnIcon.style.display = 'inline';
                    if (btnLoading) btnLoading.style.display = 'none';
                });
            });
        }
    }

    // ============================================
    // 3. PROPERTY GALLERY SLIDER
    // ============================================
    
    function initGallerySlider() {
        const slider = document.querySelector('.tls-gallery-slider');
        if (!slider) return;

        const slides = slider.querySelectorAll('.tls-gallery-slide');
        const prevBtn = slider.querySelector('.tls-gallery-prev');
        const nextBtn = slider.querySelector('.tls-gallery-next');
        const counter = slider.querySelector('.tls-gallery-counter .current');
        const thumbs = slider.querySelectorAll('.tls-gallery-thumb');

        if (slides.length === 0) return;

        let currentIndex = 0;
        slides[0].classList.add('active');

        function goToSlide(index) {
            slides.forEach(s => s.classList.remove('active'));
            thumbs.forEach(t => t.classList.remove('active'));
            slides[index].classList.add('active');
            if (thumbs[index]) thumbs[index].classList.add('active');
            if (counter) counter.textContent = index + 1;
            currentIndex = index;
        }

        if (prevBtn) {
            prevBtn.addEventListener('click', () => {
                const newIndex = currentIndex === 0 ? slides.length - 1 : currentIndex - 1;
                goToSlide(newIndex);
            });
        }

        if (nextBtn) {
            nextBtn.addEventListener('click', () => {
                const newIndex = currentIndex === slides.length - 1 ? 0 : currentIndex + 1;
                goToSlide(newIndex);
            });
        }

        thumbs.forEach((thumb, index) => {
            thumb.addEventListener('click', () => goToSlide(index));
        });

        // Touch swipe support
        let touchStartX = 0;
        let touchEndX = 0;
        slider.addEventListener('touchstart', e => touchStartX = e.changedTouches[0].screenX);
        slider.addEventListener('touchend', e => {
            touchEndX = e.changedTouches[0].screenX;
            if (touchEndX < touchStartX - 50 && nextBtn) nextBtn.click();
            if (touchEndX > touchStartX + 50 && prevBtn) prevBtn.click();
        });
    }

    // ============================================
    // 4. ELIGIBILITY & DOCUMENT REQUESTS
    // ============================================
    
    window.checkEligibility = function(choice) {
        const message = document.getElementById('eligibilityMessage');
        const buttons = document.querySelectorAll('.eligibility-btn');
        if (!message) return;

        buttons.forEach(btn => btn.classList.remove('active'));

        if (choice === 'yes') {
            message.classList.remove('show');
            if (buttons[0]) buttons[0].classList.add('active');
        } else {
            message.classList.add('show');
            if (buttons[1]) buttons[1].classList.add('active');
        }
    };

    window.requestDocument = function(docType, postId) {
        var button = event.target;
        var originalText = button.textContent;
        button.disabled = true;
        button.textContent = 'Sending...';
        button.style.opacity = '0.6';

        var formData = new FormData();
        formData.append('action', 'request_document');
        formData.append('post_id', postId);
        formData.append('doc_type', docType);

        fetch(ldcAjax.ajaxurl, {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                button.textContent = '✓ Request Sent!';
                button.style.backgroundColor = '#10b981';
                button.style.color = '#fff';

                setTimeout(function() {
                    button.textContent = originalText;
                    button.disabled = false;
                    button.style.opacity = '1';
                    button.style.backgroundColor = '';
                    button.style.color = '';
                }, 3000);

                alert('Your document request has been sent! The agent will contact you shortly.');
            } else {
                button.textContent = originalText;
                button.disabled = false;
                button.style.opacity = '1';
                alert('Error sending request. Please try WhatsApp instead.');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            button.textContent = originalText;
            button.disabled = false;
            button.style.opacity = '1';
            alert('Error sending request. Please try WhatsApp instead.');
        });
    };

    // ============================================
    // 5. CALCULATOR SYSTEM
    // ============================================

    window.toggleCalculator = function() {
        const box = document.getElementById('calculatorBox');
        if (!box) return;
        box.classList.toggle('active');
        if (box.classList.contains('active')) {
            window.calculateCosts();
        }
    };

    window.switchCalcTab = function(tabName) {
        document.querySelectorAll('.calc-tab-content').forEach(function(content) {
            content.style.display = 'none';
        });
        document.querySelectorAll('.calc-tab').forEach(function(tab) {
            tab.classList.remove('active');
        });

        if (tabName === 'purchase') {
            document.getElementById('purchaseCalc').style.display = 'block';
            document.querySelectorAll('.calc-tab')[0].classList.add('active');
        } else {
            document.getElementById('developmentCalc').style.display = 'block';
            document.querySelectorAll('.calc-tab')[1].classList.add('active');
        }
    };

    window.calculateCosts = function() {
        const priceInput = document.getElementById('calcPrice');
        if (!priceInput || typeof CALCULATOR_SETTINGS === 'undefined') return;
        
        const price = parseFloat(priceInput.value) || 0;

        // STAMP DUTY
        let stampDuty = 0;
        const sd = CALCULATOR_SETTINGS.stampDuty;
        if (price <= sd.tier1.threshold) {
            stampDuty = price * sd.tier1.rate;
        } else if (price <= sd.tier2.threshold) {
            stampDuty = (sd.tier1.threshold * sd.tier1.rate) + ((price - sd.tier1.threshold) * sd.tier2.rate);
        } else if (price <= sd.tier3.threshold) {
            stampDuty = (sd.tier1.threshold * sd.tier1.rate) + ((sd.tier2.threshold - sd.tier1.threshold) * sd.tier2.rate) + ((price - sd.tier2.threshold) * sd.tier3.rate);
        } else {
            stampDuty = (sd.tier1.threshold * sd.tier1.rate) + ((sd.tier2.threshold - sd.tier1.threshold) * sd.tier2.rate) + ((sd.tier3.threshold - sd.tier2.threshold) * sd.tier3.rate) + ((price - sd.tier3.threshold) * sd.tier4.rate);
        }

        // LEGAL FEES
        let legalFees = 0;
        const lf = CALCULATOR_SETTINGS.legalFees;
        if (price <= lf.tier1.threshold) {
            legalFees = price * lf.tier1.rate;
        } else if (price <= lf.tier2.threshold) {
            legalFees = (lf.tier1.threshold * lf.tier1.rate) + ((price - lf.tier1.threshold) * lf.tier2.rate);
        } else {
            legalFees = (lf.tier1.threshold * lf.tier1.rate) + ((lf.tier2.threshold - lf.tier1.threshold) * lf.tier2.rate) + ((price - lf.tier2.threshold) * lf.tier3.rate);
        }

        const mf = CALCULATOR_SETTINGS.motFees;
        const motFees = Math.max(mf.minimum, price * mf.rate);
        const total = stampDuty + legalFees + motFees;

        const format = num => Math.round(num).toLocaleString('en-MY');
        document.getElementById('stampDuty').textContent = 'RM ' + format(stampDuty);
        document.getElementById('legalFees').textContent = 'RM ' + format(legalFees);
        document.getElementById('motFees').textContent = 'RM ' + format(motFees);
        document.getElementById('totalCost').textContent = 'RM ' + format(total);
    };

    // ============================================
    // 6. MAP DESIGN UPGRADE & TERRITORY LOCK
    // ============================================
    
    function upgradeMapDesign() {
        // Wait for the map object to be initialized by the plugin
        var checkInterval = setInterval(function() {
            if (window.tlsMap) {
                clearInterval(checkInterval);
                
                // 1. Identify and remove existing tile layers (Standard OSM/Esri)
                window.tlsMap.eachLayer(function(layer) {
                    if (layer instanceof L.TileLayer) {
                        window.tlsMap.removeLayer(layer);
                    }
                });

                // 2. Add the Premium Carto Positron Layer
                L.tileLayer('https://{s}.basemaps.cartocdn.com/light_all/{z}/{x}/{y}{r}.png', {
                    attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors &copy; <a href="https://carto.com/attributions">CARTO</a>',
                    subdomains: 'abcd',
                    maxZoom: 20
                }).addTo(window.tlsMap);

                // 3. Lock Map to Sabah Territory
                // Approximate bounding box for Sabah: SW [3.7, 115.1], NE [7.5, 119.5]
                var sabahBounds = L.latLngBounds([
                    [3.7, 115.1], // Southwest corner
                    [7.5, 119.5]  // Northeast corner
                ]);
                
                window.tlsMap.setMaxBounds(sabahBounds);
                window.tlsMap.options.minZoom = 8; // Prevent zooming out too far
                
                // Ensure the map snaps back if the user tries to pan outside Sabah
                window.tlsMap.on('drag', function() {
                    window.tlsMap.panInsideBounds(sabahBounds, { animate: false });
                });

                console.log('TLS: Map upgraded to Carto Positron and locked to Sabah.');
            }
        }, 100);

        // Safety timeout (stop checking after 5 seconds)
        setTimeout(function() {
            clearInterval(checkInterval);
        }, 5000);
    }

    // ============================================
    // 7. SMART SCROLL INTERACTIONS
    // ============================================

    function initScrollInteractions() {
        const header = document.querySelector('header.site-header');
        const footer = document.querySelector('footer.site-footer');
        const mobileBar = document.querySelector('.mobile-sticky-bar');
        const fabContainer = document.querySelector('.tls-fab-container');
        const fabStickyFooter = document.querySelector('.tls-sticky-footer');

        window.addEventListener('scroll', function() {
            const scrollTop = window.scrollY || document.documentElement.scrollTop;

            // A. Header Shadow on Scroll
            if (header) {
                if (scrollTop > 20) {
                    header.classList.add('header-scrolled');
                } else {
                    header.classList.remove('header-scrolled');
                }
            }

            // B. Auto-Hide Fixed Buttons at Footer
            if (footer) {
                const footerRect = footer.getBoundingClientRect();
                const isFooterVisible = footerRect.top < window.innerHeight;

                if (isFooterVisible) {
                    if (mobileBar) mobileBar.classList.add('hidden');
                    if (fabContainer) fabContainer.classList.add('hidden');
                    if (fabStickyFooter) fabStickyFooter.classList.add('hidden');
                } else {
                    if (mobileBar) mobileBar.classList.remove('hidden');
                    if (fabContainer) fabContainer.classList.remove('hidden');
                    if (fabStickyFooter) fabStickyFooter.classList.remove('hidden');
                }
            }
        }, { passive: true });
    }

    // ============================================
    // 8. PREMIUM LAND CARDS (3D FLIP)
    // ============================================
    
    function initLandCards() {
        // 1. Unbind previous clicks to prevent the "double fire" bug
        $(document).off('click', '.framer-card-container');

        // 2. Attach the clean click event
        $(document).on('click', '.framer-card-container', function(e) {
            
            // 3. If the link is clicked, exit immediately and let the browser navigate
            if ($(e.target).closest('a, button, .back-cta').length > 0) {
                return; 
            }
            
            const currentInner = $(this).find('.framer-card-inner');
            const isAlreadyOpen = currentInner.hasClass('flipped');
            
            // 4. Close ALL cards safely
            $('.framer-card-inner').removeClass('flipped');
            
            // 5. Open ONLY the one we clicked (if it wasn't already open)
            if (!isAlreadyOpen) {
                currentInner.addClass('flipped');
            }
        });
    }

    // ============================================
    // INITIALIZATION
    // ============================================

    document.addEventListener('DOMContentLoaded', function() {
        initNewsAjax();
        initGallerySlider();
        upgradeMapDesign();
        initScrollInteractions();
        initLandCards();
        if (document.getElementById('calcPrice')) {
            window.calculateCosts();
        }
    });

})(jQuery);