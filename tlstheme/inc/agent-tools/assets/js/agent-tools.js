/**
 * TLS Agent Tools - Frontend Logic
 */
(function($) {
    'use strict';

    $(document).ready(function() {
        const $popup = $('#tls-contact-popup');
        const $openBtn = $('#tls-open-contact');
        const $closeBtn = $('#tls-close-contact');
        const $overlay = $popup.find('.popup-overlay');

        function openPopup() {
            $popup.show();
            // Trigger animation after show
            setTimeout(() => $popup.addClass('active'), 10);
            $('body').css('overflow', 'hidden'); // Prevent scrolling
        }

        function closePopup() {
            $popup.removeClass('active');
            setTimeout(() => $popup.hide(), 300);
            $('body').css('overflow', '');
        }

        $openBtn.on('click', function(e) {
            e.preventDefault();
            openPopup();
        });

        $closeBtn.on('click', closePopup);
        $overlay.on('click', closePopup);

        // Form Submission
        $('#tls-agent-contact-form').on('submit', function(e) {
            e.preventDefault();
            
            const $btn = $(this).find('.submit-btn');
            const originalText = $btn.text();
            
            $btn.text('Menghantar...').prop('disabled', true);
            
            // Simulate success for now
            setTimeout(() => {
                alert('Terima kasih! Pertanyaan anda telah dihantar kepada perunding tanah kami.');
                $btn.text(originalText).prop('disabled', false);
                closePopup();
                this.reset();
            }, 1500);
        });
    });

})(jQuery);
