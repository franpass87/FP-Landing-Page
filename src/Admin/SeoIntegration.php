<?php
/**
 * Integrazione con FP SEO Manager
 *
 * @package FPLandingPage
 */

namespace FPLandingPage\Admin;

defined('ABSPATH') || exit;

/**
 * Gestisce l'integrazione con FP SEO Manager
 */
class SeoIntegration {
    
    /**
     * Costruttore
     */
    public function __construct() {
        // Aggiungi fp_landing_page ai post types supportati da FP SEO Manager
        add_filter('fp_seo_metabox_post_types', [$this, 'add_landing_page_to_seo']);
        
        // Riordina con JavaScript sicuro
        add_action('admin_footer', [$this, 'reorder_metaboxes_js']);
        
        // Nascondi l'editor nell'interfaccia admin
        add_action('admin_head', [$this, 'hide_editor_ui']);
    }
    
    /**
     * JavaScript per riordinare i metabox
     */
    public function reorder_metaboxes_js() {
        $screen = get_current_screen();
        if (!$screen || $screen->post_type !== 'fp_landing_page') {
            return;
        }
        ?>
        <script>
        jQuery(function($) {
            var container = $('#normal-sortables');
            if (!container.length) return;
            
            var builder = $('#fp_landing_page_builder');
            var settings = $('#fp_landing_page_settings');
            var seo = $('#fp-seo-performance-metabox');
            
            // Sposta builder in cima, poi settings, poi SEO
            if (builder.length) container.prepend(builder);
            if (settings.length) builder.after(settings);
            if (seo.length && builder.length) {
                // Metti SEO dopo settings (o dopo builder se settings non c'è)
                if (settings.length) {
                    settings.after(seo);
                } else {
                    builder.after(seo);
                }
            }
        });
        </script>
        <?php
    }
    
    /**
     * Aggiunge fp_landing_page ai post types supportati da FP SEO Manager
     *
     * @param array $post_types Post types supportati
     * @return array
     */
    public function add_landing_page_to_seo($post_types) {
        if (!in_array('fp_landing_page', $post_types, true)) {
            $post_types[] = 'fp_landing_page';
        }
        return $post_types;
    }
    
    /**
     * Nascondi l'editor nell'interfaccia admin
     *
     * @return void
     */
    public function hide_editor_ui() {
        global $post_type;
        if ($post_type !== 'fp_landing_page') {
            return;
        }
        
        ?>
        <style type="text/css">
            #postdivrich,
            #post-body-content #postdivrich,
            .wp-editor-wrap,
            #wp-content-wrap,
            #content,
            #post-body-content #content,
            #wp-content-editor-tools,
            #wp-content-editor-container,
            #content-html,
            #content-tmce {
                display: none !important;
            }
        </style>
        <?php
    }
}
