<?php
/**
 * Template System per Landing Pages
 *
 * @package FPLandingPage
 */

namespace FPLandingPage;

defined('ABSPATH') || exit;

/**
 * Classe per gestire i template delle landing page
 */
class Template {
    
    /**
     * Costruttore
     */
    public function __construct() {
        // Modifica il post content direttamente prima che venga mostrato
        add_filter('the_posts', [$this, 'inject_landing_page_content'], 10, 2);
        // Fallback: usa the_content
        add_filter('the_content', [$this, 'render_landing_page_content'], 5);
        add_action('wp_enqueue_scripts', [$this, 'enqueue_frontend_assets']);
        add_action('wp_head', [$this, 'add_landing_page_styles']);
        add_filter('body_class', [$this, 'add_landing_page_body_class']);
        // Fallback: carica script inline nel footer
        add_action('wp_footer', [$this, 'load_frontend_script_inline'], 999);
    }
    
    /**
     * Inietta il contenuto della landing page direttamente nel post_content
     * Questo funziona anche se il tema non usa the_content()
     */
    public function inject_landing_page_content($posts, $query) {
        // Solo nella query principale per pagine singole
        if (!$query->is_main_query() || !$query->is_singular) {
            return $posts;
        }
        
        foreach ($posts as &$post) {
            if ($post->post_type !== 'fp_landing_page') {
                continue;
            }
            
            // Ottieni le sezioni dal builder
            $sections = get_post_meta($post->ID, '_fp_landing_page_sections', true);
            
            if (!is_array($sections) || empty($sections)) {
                continue;
            }
            
            // Genera il contenuto renderizzato
            ob_start();
            echo '<div class="fp-landing-page-container" id="fp-landing-page-' . esc_attr($post->ID) . '">';
            \FPLandingPage\Shortcodes\Landing::render_sections($post->ID);
            
            // Footer personalizzato se presente
            $footer_text = get_post_meta($post->ID, '_fp_landing_footer_text', true);
            if ($footer_text) {
                echo '<div class="fp-landing-page-footer">' . wp_kses_post($footer_text) . '</div>';
            }
            echo '</div>';
            $rendered_content = ob_get_clean();
            
            // Sostituisci il post_content con il contenuto renderizzato
            $post->post_content = $rendered_content;
        }
        
        return $posts;
    }
    
    /**
     * Carica CSS e JS frontend
     */
    public function enqueue_frontend_assets() {
        global $post;
        
        // Carica solo su landing page singole
        $is_landing = is_singular('fp_landing_page') || 
                      ($post && $post->post_type === 'fp_landing_page');
        
        if (!$is_landing) {
            return;
        }
        
        // Verifica che le costanti siano definite
        if (!defined('FP_LANDING_PAGE_URL') || !defined('FP_LANDING_PAGE_VERSION')) {
            return;
        }
        
        // CSS frontend
        wp_enqueue_style(
            'fp-landing-page-frontend',
            FP_LANDING_PAGE_URL . 'assets/css/fp-landing-page.css',
            [],
            FP_LANDING_PAGE_VERSION
        );
        
        // JS frontend
        wp_enqueue_script(
            'fp-landing-page-frontend',
            FP_LANDING_PAGE_URL . 'assets/js/frontend.js',
            ['jquery'],
            FP_LANDING_PAGE_VERSION,
            true
        );
    }
    
    /**
     * Renderizza il contenuto della landing page tramite the_content filter
     */
    public function render_landing_page_content($content) {
        global $post;
        
        // Verifica se siamo su una landing page
        if (!$post || $post->post_type !== 'fp_landing_page') {
            return $content;
        }
        
        // Solo nella query principale per evitare loop infiniti
        if (!is_singular('fp_landing_page')) {
            return $content;
        }
        
        // Ottieni le sezioni dal builder
        $sections = get_post_meta($post->ID, '_fp_landing_page_sections', true);
        
        // Se non ci sono sezioni, restituisci il contenuto originale
        if (!is_array($sections) || empty($sections)) {
            return $content;
        }
        
        // Renderizza le sezioni
        ob_start();
        echo '<div class="fp-landing-page-container" id="fp-landing-page-' . esc_attr($post->ID) . '">';
        \FPLandingPage\Shortcodes\Landing::render_sections($post->ID);
        
        // Footer personalizzato se presente
        $footer_text = get_post_meta($post->ID, '_fp_landing_footer_text', true);
        if ($footer_text) {
            echo '<div class="fp-landing-page-footer">' . wp_kses_post($footer_text) . '</div>';
        }
        
        echo '</div>';
        $landing_content = ob_get_clean();
        
        // Sostituisci completamente il contenuto
        return $landing_content;
    }
    
    /**
     * Aggiunge stili inline basati sui meta della landing page
     */
    public function add_landing_page_styles() {
        global $post;
        
        $is_landing = is_singular('fp_landing_page') || 
                      ($post && $post->post_type === 'fp_landing_page');
        
        if (!$is_landing || !$post) {
            return;
        }
        
        $bg_color = get_post_meta($post->ID, '_fp_landing_bg_color', true);
        $text_color = get_post_meta($post->ID, '_fp_landing_text_color', true);
        $header_style = get_post_meta($post->ID, '_fp_landing_header_style', true);
        
        echo '<style id="fp-landing-page-custom-styles">';
        
        if ($bg_color) {
            echo 'body.fp-landing-page-body { background-color: ' . esc_attr($bg_color) . '; }';
        }
        
        if ($text_color) {
            echo 'body.fp-landing-page-body { color: ' . esc_attr($text_color) . '; }';
        }
        
        if ($header_style === 'transparent') {
            echo 'body.fp-landing-page-body .site-header { background: transparent; }';
        } elseif ($header_style === 'hidden') {
            echo 'body.fp-landing-page-body .site-header { display: none; }';
        }
        
        echo '</style>';
    }
    
    /**
     * Aggiunge classe body per landing page
     */
    public function add_landing_page_body_class($classes) {
        if (is_singular('fp_landing_page')) {
            $classes[] = 'fp-landing-page-body';
        }
        return $classes;
    }
    
    /**
     * Carica lo script frontend inline nel footer come fallback
     */
    public function load_frontend_script_inline() {
        global $post;
        
        // Solo su landing page
        if (!is_singular('fp_landing_page') && (!$post || $post->post_type !== 'fp_landing_page')) {
            return;
        }
        
        // Se lo script è già caricato via wp_enqueue_script, non caricarlo inline
        if (wp_script_is('fp-landing-page-frontend', 'done')) {
            return;
        }
        
        ?>
        <script type="text/javascript">
        (function($) {
            'use strict';
            
            $(document).ready(function() {
                // Fix per il lazy loading del tema Salient sui video embeddati
                $('iframe').each(function() {
                    var $iframe = $(this);
                    var malformedAttr = $iframe.attr('allowfullscreendata-src');
                    if (malformedAttr && !$iframe.attr('src')) {
                        $iframe.attr('src', malformedAttr);
                        $iframe.removeAttr('allowfullscreendata-src');
                        $iframe.attr('allowfullscreen', '');
                        return;
                    }
                    var dataSrc = $iframe.attr('data-src');
                    if (dataSrc && !$iframe.attr('src')) {
                        $iframe.attr('src', dataSrc);
                    }
                });
                
                // Fix per sovrascrivere gli stili del tema sui bottoni CTA
                $('.fp-lp-button').each(function() {
                    this.style.setProperty('height', 'auto', 'important');
                    this.style.setProperty('min-height', '0', 'important');
                    this.style.setProperty('padding', '12px 28px', 'important');
                    this.style.setProperty('line-height', '1.4', 'important');
                });
                
                // FAQ Accordion - usa event delegation
                $(document).on('click', '.fp-lp-faq-question', function() {
                    var $question = $(this);
                    var $faqItem = $question.closest('.fp-lp-faq-item');
                    var $answer = $faqItem.find('.fp-lp-faq-answer');
                    var isActive = $question.hasClass('active');
                    
                    $question.closest('.fp-lp-faq-list').find('.fp-lp-faq-item').not($faqItem).each(function() {
                        $(this).find('.fp-lp-faq-question').removeClass('active');
                        $(this).find('.fp-lp-faq-answer').removeClass('active').slideUp(300);
                    });
                    
                    if (isActive) {
                        $question.removeClass('active');
                        $answer.removeClass('active').slideUp(300);
                    } else {
                        $question.addClass('active');
                        $answer.addClass('active').slideDown(300);
                    }
                });
                
                // Tabs - usa event delegation
                $(document).on('click', '.fp-lp-tab-button', function() {
                    var $button = $(this);
                    var $tabsWrapper = $button.closest('.fp-lp-tabs-wrapper');
                    var tabId = $button.data('tab');
                    
                    $tabsWrapper.find('.fp-lp-tab-button').removeClass('active');
                    $tabsWrapper.find('.fp-lp-tab-panel').removeClass('active');
                    
                    $button.addClass('active');
                    $tabsWrapper.find('#' + tabId).addClass('active');
                });
            });
        })(jQuery);
        </script>
        <?php
    }
}
