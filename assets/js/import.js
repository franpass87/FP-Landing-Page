/**
 * FP Landing Page Import - JavaScript
 */
(function($) {
    'use strict';
    
    $(document).ready(function() {
        const $modal = $('#fp-lp-import-modal');
        const $btn = $('#fp-lp-import-btn');
        const $close = $('.fp-lp-modal-close, #fp-lp-import-cancel');
        const $submit = $('#fp-lp-import-submit');
        const $jsonTextarea = $('#fp-lp-import-json');
        const $error = $('#fp-lp-import-error');
        const $success = $('#fp-lp-import-success');
        
        // Apri modal
        $btn.on('click', function() {
            $modal.fadeIn(200);
            $jsonTextarea.focus();
        });
        
        // Chiudi modal
        $close.on('click', function() {
            $modal.fadeOut(200);
            resetForm();
        });
        
        // Chiudi con overlay
        $('.fp-lp-modal-overlay').on('click', function() {
            $modal.fadeOut(200);
            resetForm();
        });
        
        // Chiudi con ESC
        $(document).on('keydown', function(e) {
            if (e.key === 'Escape' && $modal.is(':visible')) {
                $modal.fadeOut(200);
                resetForm();
            }
        });
        
        // Submit import
        $submit.on('click', function() {
            const jsonData = $jsonTextarea.val().trim();
            
            if (!jsonData) {
                showError('Inserisci il codice JSON da importare.');
                return;
            }
            
            // Valida JSON base
            try {
                JSON.parse(jsonData);
            } catch (e) {
                showError('JSON non valido: ' + e.message);
                return;
            }
            
            // Disabilita pulsante e mostra spinner
            $submit.prop('disabled', true);
            $submit.find('.spinner').css('visibility', 'visible');
            $error.hide();
            $success.hide();
            
            // Chiamata AJAX
            $.ajax({
                url: fpLandingPageImport.ajaxurl,
                type: 'POST',
                data: {
                    action: 'fp_lp_import_landing_page',
                    nonce: fpLandingPageImport.nonce,
                    json_data: jsonData
                },
                success: function(response) {
                    if (response.success) {
                        showSuccess(response.data.message);
                        
                        // Dopo 2 secondi, reindirizza alla pagina di modifica
                        setTimeout(function() {
                            if (response.data.edit_url) {
                                window.location.href = response.data.edit_url;
                            } else {
                                // Ricarica la pagina
                                window.location.reload();
                            }
                        }, 2000);
                    } else {
                        showError(response.data.message || fpLandingPageImport.i18n.error);
                        enableSubmit();
                    }
                },
                error: function(xhr, status, error) {
                    showError('Errore di comunicazione: ' + error);
                    enableSubmit();
                }
            });
        });
        
        /**
         * Mostra errore
         */
        function showError(message) {
            $error.html('<strong>Errore:</strong> ' + message).fadeIn();
            $success.hide();
        }
        
        /**
         * Mostra successo
         */
        function showSuccess(message) {
            $success.html('<strong>Successo!</strong> ' + message).fadeIn();
            $error.hide();
        }
        
        /**
         * Abilita di nuovo il pulsante submit
         */
        function enableSubmit() {
            $submit.prop('disabled', false);
            $submit.find('.spinner').css('visibility', 'hidden');
        }
        
        /**
         * Reset form
         */
        function resetForm() {
            $jsonTextarea.val('');
            $error.hide();
            $success.hide();
            enableSubmit();
        }
    });
})(jQuery);
