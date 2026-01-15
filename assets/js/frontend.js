/**
 * FP Landing Page - JavaScript Frontend
 */
(function($) {
    'use strict';
    
    $(document).ready(function() {
        // Fix per il lazy loading del tema Salient sui video embeddati
        // Il tema converte src in data-src per il lazy loading
        $('iframe').each(function() {
            var $iframe = $(this);
            
            // Fix per attributo malformato (allowfullscreendata-src)
            var malformedAttr = $iframe.attr('allowfullscreendata-src');
            if (malformedAttr && !$iframe.attr('src')) {
                $iframe.attr('src', malformedAttr);
                $iframe.removeAttr('allowfullscreendata-src');
                $iframe.attr('allowfullscreen', '');
                return;
            }
            
            // Fix per data-src separato
            var dataSrc = $iframe.attr('data-src');
            if (dataSrc && !$iframe.attr('src')) {
                $iframe.attr('src', dataSrc);
            }
        });
        
        // FAQ Accordion
        $('.fp-lp-faq-question').on('click', function() {
            const $question = $(this);
            const $faqItem = $question.closest('.fp-lp-faq-item');
            const $answer = $faqItem.find('.fp-lp-faq-answer');
            const isActive = $question.hasClass('active');
            
            // Chiudi tutte le altre FAQ nella stessa sezione
            $question.closest('.fp-lp-faq-list').find('.fp-lp-faq-item').not($faqItem).each(function() {
                $(this).find('.fp-lp-faq-question').removeClass('active');
                $(this).find('.fp-lp-faq-answer').removeClass('active').slideUp(300);
            });
            
            // Toggle FAQ corrente
            if (isActive) {
                $question.removeClass('active');
                $answer.removeClass('active').slideUp(300);
            } else {
                $question.addClass('active');
                $answer.addClass('active').slideDown(300);
            }
        });
        
        // Tabs
        $('.fp-lp-tab-button').on('click', function() {
            const $button = $(this);
            const $tabsWrapper = $button.closest('.fp-lp-tabs-wrapper');
            const tabId = $button.data('tab');
            
            // Rimuovi active da tutti i tab
            $tabsWrapper.find('.fp-lp-tab-button').removeClass('active');
            $tabsWrapper.find('.fp-lp-tab-panel').removeClass('active');
            
            // Aggiungi active al tab cliccato
            $button.addClass('active');
            $tabsWrapper.find('#' + tabId).addClass('active');
        });
        
        // Applica stili responsive
        function applyResponsiveStyles() {
            const width = window.innerWidth;
            let breakpoint = 'desktop';
            
            if (width < 768) {
                breakpoint = 'mobile';
            } else if (width < 1024) {
                breakpoint = 'tablet';
            }
            
            // Applica padding e margin responsive alle sezioni
            $('.fp-lp-section').each(function() {
                const $section = $(this);
                let padding = '', margin = '';
                
                if (breakpoint === 'mobile' && $section.data('padding-mobile')) {
                    padding = $section.data('padding-mobile');
                } else if (breakpoint === 'tablet' && $section.data('padding-tablet')) {
                    padding = $section.data('padding-tablet');
                } else if (breakpoint === 'desktop' && $section.data('padding-desktop')) {
                    padding = $section.data('padding-desktop');
                }
                
                if (breakpoint === 'mobile' && $section.data('margin-mobile')) {
                    margin = $section.data('margin-mobile');
                } else if (breakpoint === 'tablet' && $section.data('margin-tablet')) {
                    margin = $section.data('margin-tablet');
                } else if (breakpoint === 'desktop' && $section.data('margin-desktop')) {
                    margin = $section.data('margin-desktop');
                }
                
                if (padding) {
                    $section.css('padding', padding);
                }
                if (margin) {
                    $section.css('margin', margin);
                }
            });
            
            // Applica font-size responsive ai titoli
            $('.fp-lp-title').each(function() {
                const $title = $(this);
                let fontSize = '';
                
                if (breakpoint === 'mobile' && $title.data('font-size-mobile')) {
                    fontSize = $title.data('font-size-mobile') + 'px';
                } else if (breakpoint === 'tablet' && $title.data('font-size-tablet')) {
                    fontSize = $title.data('font-size-tablet') + 'px';
                } else if (breakpoint === 'desktop' && $title.data('font-size-desktop')) {
                    fontSize = $title.data('font-size-desktop') + 'px';
                }
                
                if (fontSize) {
                    $title.css('font-size', fontSize);
                }
            });
            
            // Applica allineamento responsive
            $('.fp-lp-title-section, .fp-lp-text-section, .fp-lp-image-section, .fp-lp-cta-section').each(function() {
                const $section = $(this);
                let align = '';
                
                if (breakpoint === 'mobile' && $section.data('align-mobile')) {
                    align = $section.data('align-mobile');
                } else if (breakpoint === 'tablet' && $section.data('align-tablet')) {
                    align = $section.data('align-tablet');
                } else if (breakpoint === 'desktop' && $section.data('align-desktop')) {
                    align = $section.data('align-desktop');
                }
                
                if (align) {
                    $section.css('text-align', align);
                }
            });
            
            // Applica max-width responsive alle immagini
            $('.fp-lp-image').each(function() {
                const $image = $(this);
                let maxWidth = '';
                
                if (breakpoint === 'mobile' && $image.data('max-width-mobile')) {
                    maxWidth = $image.data('max-width-mobile');
                } else if (breakpoint === 'tablet' && $image.data('max-width-tablet')) {
                    maxWidth = $image.data('max-width-tablet');
                } else if (breakpoint === 'desktop' && $image.data('max-width-desktop')) {
                    maxWidth = $image.data('max-width-desktop');
                }
                
                if (maxWidth) {
                    $image.css('max-width', maxWidth);
                }
            });
        }
        
        // Applica al caricamento
        applyResponsiveStyles();
        
        // Applica al ridimensionamento (con debounce)
        let resizeTimer;
        $(window).on('resize', function() {
            clearTimeout(resizeTimer);
            resizeTimer = setTimeout(applyResponsiveStyles, 100);
        });
    });
    
})(jQuery);