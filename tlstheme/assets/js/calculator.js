/**
 * Land Development Calculator - Main JavaScript
 * Includes Lead Tracking for Tanah
 */

(function($) {
    'use strict';

    let selectedItems = {};
    let presets = {};
    let pricingData = {};

    $(document).ready(function() {
        initCalculator();
        initStickyLauncher();
        initLeadTracking();
    });

    // Lead Tracking - Track WhatsApp clicks
    function initLeadTracking() {
        $(document).on('click', '.btn-whatsapp[data-tanah-id]', function(e) {
            const tanahId = $(this).data('tanah-id');
            if (tanahId && typeof tls_ajax !== 'undefined') {
                $.ajax({
                    url: tls_ajax.ajax_url,
                    type: 'POST',
                    data: {
                        action: 'tls_track_lead',
                        nonce: tls_ajax.nonce,
                        tanah_id: tanahId
                    }
                });
            }
        });
    }

    function initStickyLauncher() {
        $('#ldc-sticky-launcher').on('click', function() {
            $('#ldc-global-modal').fadeIn(300);
        });

        $('#ldc-close-global-modal').on('click', function() {
            $('#ldc-global-modal').fadeOut(300);
        });
    }

    function initCalculator() {
        if ($('#ldcCalculator').length === 0) return;

        const presetsEl = document.getElementById('ldc-presets-data');
        const pricingEl = document.getElementById('ldc-pricing-data');

        if (presetsEl) presets = JSON.parse(presetsEl.textContent);
        if (pricingEl) pricingData = JSON.parse(pricingEl.textContent);

        $('.ldc-category-toggle').on('change', function() {
            const $section = $(this).closest('.ldc-section');
            const $body = $section.find('.ldc-section-body');

            if ($(this).is(':checked')) {
                $body.slideDown(300);
            } else {
                $body.slideUp(300);
                $section.find('.ldc-item-checkbox').prop('checked', false).trigger('change');
            }
        });

        $('.ldc-item-checkbox').on('change', function() {
            const $row = $(this).closest('.ldc-item-row');
            const $details = $row.find('.ldc-item-details');

            if ($(this).is(':checked')) {
                $details.slideDown(200);
                addItemToSelection($(this));
            } else {
                $details.slideUp(200);
                removeItemFromSelection($(this));
            }
            updateSummary();
        });

        $(document).on('input', '.ldc-quantity-input', function() {
            const $row = $(this).closest('.ldc-item-row');
            updateItemSubtotal($row);
            updateSummary();
        });

        $('#ldc-review-btn').on('click', function() {
            if (Object.keys(selectedItems).length === 0) {
                alert('Sila pilih sekurang-kurangnya satu item.');
                return;
            }
            openReviewModal();
        });

        $('#ldc-reset-btn').on('click', function() {
            if (confirm('Adakah anda pasti mahu set semula kalkulator?')) {
                resetCalculator();
            }
        });

        $('#ldc-confirm-generate-btn').on('click', function() {
            handlePDFGeneration();
        });
    }

    function openReviewModal() {
        $('#review_client_name').val($('#client_name').val());
        $('#review_client_email').val($('#client_email').val());
        $('#review_client_phone').val($('#client_phone').val());
        populateReviewItems();
        $('#review_total_cost').text('RM ' + formatNumber(calculateTotalCost()));
        $('#ldcConfirmationModal').fadeIn(300);
    }

    function populateReviewItems() {
        const $container = $('#review_items_list');
        $container.empty();

        const itemsByCategory = {};
        $.each(selectedItems, function(itemId, item) {
            if (!itemsByCategory[item.category]) {
                itemsByCategory[item.category] = [];
            }
            itemsByCategory[item.category].push(item);
        });

        $.each(itemsByCategory, function(category, items) {
            const $categoryDiv = $('<div class="review-category"></div>');
            $categoryDiv.append('<h4>' + category + '</h4>');

            items.forEach(function(item) {
                const itemCost = item.price * item.quantity;
                $categoryDiv.append('<div class="review-item">' +
                    '<span>' + item.name + '</span>' +
                    '<span>RM ' + formatNumber(itemCost) + '</span>' +
                '</div>');
            });

            $container.append($categoryDiv);
        });
    }

    function handlePDFGeneration() {
        $('#ldcConfirmationModal').fadeOut(300);
        $('.ldc-loading-overlay').fadeIn(300);

        const estimateData = {
            client_name: $('#review_client_name').val() || $('#client_name').val(),
            client_email: $('#review_client_email').val() || $('#client_email').val(),
            client_phone: $('#review_client_phone').val() || $('#client_phone').val(),
            land_size: parseFloat($('#land_size').val()) || 0,
            land_unit: $('#land_unit').val() || 'sq ft',
            location: $('#land_location').val() || '',
            items: Object.values(selectedItems).map(item => ({
                category: item.category,
                name: item.name,
                quantity: item.quantity,
                unit: item.unit,
                unit_price: item.price
            })),
            total_cost: calculateTotalCost()
        };

        $.ajax({
            url: ldcAjax.ajaxurl,
            type: 'POST',
            data: {
                action: 'tls_ldc_generate_pdf',
                nonce: ldcAjax.nonce,
                estimate_data: estimateData
            },
            success: function(response) {
                $('.ldc-loading-overlay').fadeOut(300);
                if (response.success && response.data.pdf_url) {
                    window.open(response.data.pdf_url, '_blank');
                    alert('PDF berjaya dihasilkan!');
                } else {
                    alert('Ralat: ' + (response.data.message || 'Tidak dapat menjana PDF'));
                }
            },
            error: function() {
                $('.ldc-loading-overlay').fadeOut(300);
                alert('Ralat server. Sila cuba lagi.');
            }
        });
    }

    function addItemToSelection($checkbox) {
        const itemData = {
            id: $checkbox.data('item-id'),
            name: $checkbox.data('item-name'),
            category: $checkbox.data('category'),
            unit: $checkbox.data('unit'),
            price: parseFloat($checkbox.data('price')),
            quantity: 1
        };
        selectedItems[itemData.id] = itemData;
        updateItemSubtotal($checkbox.closest('.ldc-item-row'));
    }

    function removeItemFromSelection($checkbox) {
        delete selectedItems[$checkbox.data('item-id')];
    }

    function updateItemSubtotal($row) {
        const $checkbox = $row.find('.ldc-item-checkbox');
        const quantity = parseFloat($row.find('.ldc-quantity-input').val()) || 0;
        const price = parseFloat($checkbox.data('price')) || 0;
        const subtotal = quantity * price;
        $row.find('.ldc-subtotal-display').text('RM ' + formatNumber(subtotal));
        const itemId = $checkbox.data('item-id');
        if (selectedItems[itemId]) {
            selectedItems[itemId].quantity = quantity;
        }
    }

    function updateSummary() {
        let subtotal = 0;
        Object.values(selectedItems).forEach(item => {
            subtotal += item.quantity * item.price;
        });
        $('.ldc-grand-total-amount').text('RM ' + formatNumber(subtotal));
    }

    function calculateTotalCost() {
        let total = 0;
        Object.values(selectedItems).forEach(item => {
            total += item.quantity * item.price;
        });
        return total;
    }

    function resetCalculator() {
        $('.ldc-category-toggle').prop('checked', false);
        $('.ldc-item-checkbox').prop('checked', false);
        $('.ldc-item-details').hide();
        $('.ldc-quantity-input').val(1);
        selectedItems = {};
        updateSummary();
        $('#land_size').val('');
        $('#land_location').val('');
    }

    function formatNumber(num) {
        return parseFloat(num).toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',');
    }

})(jQuery);
