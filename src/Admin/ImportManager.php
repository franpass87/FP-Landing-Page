<?php
/**
 * Import Manager per Landing Pages
 *
 * @package FPLandingPage
 */

namespace FPLandingPage\Admin;

defined('ABSPATH') || exit;

/**
 * Classe per gestire l'import di landing page dalla lista
 */
class ImportManager {
    
    /**
     * Costruttore
     */
    public function __construct() {
        // Pulsante nella lista delle landing page
        add_action('manage_posts_extra_tablenav', [$this, 'add_import_button'], 10, 1);
        
        // Modal HTML
        add_action('admin_footer', [$this, 'render_import_modal']);
        
        // Scripts e stili
        add_action('admin_enqueue_scripts', [$this, 'enqueue_scripts']);
        
        // AJAX handler
        add_action('wp_ajax_fp_lp_import_landing_page', [$this, 'ajax_import_landing_page']);
    }
    
    /**
     * Aggiunge il pulsante Import nella lista delle landing page
     */
    public function add_import_button($which) {
        global $typenow;
        
        if ($typenow !== 'fp_landing_page' || $which !== 'top') {
            return;
        }
        
        ?>
        <div class="alignleft actions">
            <button type="button" id="fp-lp-import-btn" class="button">
                <span class="dashicons dashicons-upload" style="margin-top: 3px;"></span> 
                <?php _e('Importa Landing Page', 'fp-landing-page'); ?>
            </button>
        </div>
        <?php
    }
    
    /**
     * Renderizza il modal per l'import
     */
    public function render_import_modal() {
        global $typenow;
        
        if ($typenow !== 'fp_landing_page') {
            return;
        }
        
        ?>
        <div id="fp-lp-import-modal" style="display: none;">
            <div class="fp-lp-modal-overlay"></div>
            <div class="fp-lp-modal-content">
                <div class="fp-lp-modal-header">
                    <h2><?php _e('Importa Landing Page', 'fp-landing-page'); ?></h2>
                    <button type="button" class="fp-lp-modal-close" aria-label="<?php esc_attr_e('Chiudi', 'fp-landing-page'); ?>">
                        <span class="dashicons dashicons-no-alt"></span>
                    </button>
                </div>
                <div class="fp-lp-modal-body">
                    <p><?php _e('Incolla il codice JSON della landing page prefabbricata qui sotto. Il formato atteso è:', 'fp-landing-page'); ?></p>
                    <pre style="background: #f5f5f5; padding: 10px; border-radius: 4px; overflow-x: auto; font-size: 11px; margin: 10px 0;">{
  "title": "Titolo Landing Page",
  "sections": [
    {
      "type": "title",
      "data": {
        "text": "Titolo",
        "level": "h1",
        "align": "center"
      }
    }
  ],
  "settings": {
    "bg_color": "#ffffff",
    "text_color": "#333333"
  }
}</pre>
                    <label for="fp-lp-import-json" style="display: block; margin: 15px 0 5px; font-weight: 600;">
                        <?php _e('Codice JSON:', 'fp-landing-page'); ?>
                    </label>
                    <textarea 
                        id="fp-lp-import-json" 
                        rows="15" 
                        style="width: 100%; font-family: monospace; font-size: 12px;"
                        placeholder='{"title": "Titolo", "sections": [], "settings": {}}'
                    ></textarea>
                    <div id="fp-lp-import-error" style="display: none; margin-top: 10px; padding: 10px; background: #f8d7da; color: #721c24; border-radius: 4px;"></div>
                    <div id="fp-lp-import-success" style="display: none; margin-top: 10px; padding: 10px; background: #d4edda; color: #155724; border-radius: 4px;"></div>
                </div>
                <div class="fp-lp-modal-footer">
                    <button type="button" class="button" id="fp-lp-import-cancel">
                        <?php _e('Annulla', 'fp-landing-page'); ?>
                    </button>
                    <button type="button" class="button button-primary" id="fp-lp-import-submit">
                        <span class="spinner" style="float: none; margin: 0 5px 0 0;"></span>
                        <?php _e('Importa', 'fp-landing-page'); ?>
                    </button>
                </div>
            </div>
        </div>
        <?php
    }
    
    /**
     * Carica script e stili
     */
    public function enqueue_scripts($hook) {
        global $typenow;
        
        if ($typenow !== 'fp_landing_page' || $hook !== 'edit.php') {
            return;
        }
        
        wp_enqueue_script(
            'fp-landing-page-import',
            FP_LANDING_PAGE_URL . 'assets/js/import.js',
            ['jquery'],
            FP_LANDING_PAGE_VERSION,
            true
        );
        
        wp_enqueue_style(
            'fp-landing-page-import',
            FP_LANDING_PAGE_URL . 'assets/css/import.css',
            [],
            FP_LANDING_PAGE_VERSION
        );
        
        wp_localize_script('fp-landing-page-import', 'fpLandingPageImport', [
            'ajaxurl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('fp_lp_import_nonce'),
            'i18n' => [
                'importing' => __('Importazione in corso...', 'fp-landing-page'),
                'success' => __('Landing page importata con successo!', 'fp-landing-page'),
                'error' => __('Errore durante l\'importazione.', 'fp-landing-page'),
            ]
        ]);
    }
    
    /**
     * AJAX handler per importare la landing page
     */
    public function ajax_import_landing_page() {
        check_ajax_referer('fp_lp_import_nonce', 'nonce');
        
        if (!current_user_can('edit_posts')) {
            wp_send_json_error(['message' => __('Permessi insufficienti.', 'fp-landing-page')]);
        }
        
        $json_data = isset($_POST['json_data']) ? wp_unslash($_POST['json_data']) : '';
        
        if (empty($json_data)) {
            wp_send_json_error(['message' => __('Nessun dato JSON fornito.', 'fp-landing-page')]);
        }
        
        // Decodifica JSON
        $data = json_decode($json_data, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            wp_send_json_error([
                'message' => __('JSON non valido: ', 'fp-landing-page') . json_last_error_msg()
            ]);
        }
        
        // Valida struttura base
        if (!isset($data['sections']) || !is_array($data['sections'])) {
            wp_send_json_error(['message' => __('Struttura JSON non valida: manca "sections".', 'fp-landing-page')]);
        }
        
        // Crea la landing page
        $post_title = isset($data['title']) && !empty($data['title']) 
            ? sanitize_text_field($data['title']) 
            : __('Landing Page Importata', 'fp-landing-page');
        
        $post_data = [
            'post_title' => $post_title,
            'post_content' => '',
            'post_status' => 'draft',
            'post_type' => 'fp_landing_page',
        ];
        
        $post_id = wp_insert_post($post_data);
        
        if (is_wp_error($post_id)) {
            wp_send_json_error([
                'message' => __('Errore nella creazione del post: ', 'fp-landing-page') . $post_id->get_error_message()
            ]);
        }
        
        // Salva le sezioni
        $sections = $this->sanitize_sections($data['sections']);
        update_post_meta($post_id, '_fp_landing_page_sections', $sections);
        
        // Salva le impostazioni se presenti
        if (isset($data['settings']) && is_array($data['settings'])) {
            $settings = $data['settings'];
            
            if (isset($settings['bg_color'])) {
                update_post_meta($post_id, '_fp_landing_bg_color', sanitize_hex_color($settings['bg_color']));
            }
            
            if (isset($settings['text_color'])) {
                update_post_meta($post_id, '_fp_landing_text_color', sanitize_hex_color($settings['text_color']));
            }
            
            if (isset($settings['header_style'])) {
                update_post_meta($post_id, '_fp_landing_header_style', sanitize_text_field($settings['header_style']));
            }
            
            if (isset($settings['footer_text'])) {
                update_post_meta($post_id, '_fp_landing_footer_text', wp_kses_post($settings['footer_text']));
            }
        }
        
        // Inserisci lo shortcode nel contenuto
        $shortcode = '[fp_landing_page id="' . $post_id . '"]';
        wp_update_post([
            'ID' => $post_id,
            'post_content' => $shortcode,
        ]);
        
        wp_send_json_success([
            'message' => __('Landing page importata con successo!', 'fp-landing-page'),
            'edit_url' => get_edit_post_link($post_id, 'raw'),
            'post_id' => $post_id
        ]);
    }
    
    /**
     * Sanitizza le sezioni importate
     */
    private function sanitize_sections($sections) {
        $sanitized = [];
        $allowed_types = ['title', 'text', 'image', 'gallery', 'shortcode', 'cta', 'video', 'separator', 'features', 'counters', 'faq', 'tabs'];
        
        foreach ($sections as $section) {
            if (!isset($section['type']) || !in_array($section['type'], $allowed_types)) {
                continue;
            }
            
            $sanitized_section = [
                'type' => sanitize_text_field($section['type']),
                'data' => isset($section['data']) && is_array($section['data']) 
                    ? $this->sanitize_section_data($section['type'], $section['data']) 
                    : []
            ];
            
            $sanitized[] = $sanitized_section;
        }
        
        return $sanitized;
    }
    
    /**
     * Sanitizza i dati di una sezione
     */
    private function sanitize_section_data($type, $data) {
        $sanitized = [];
        
        foreach ($data as $key => $value) {
            // Campi di testo base
            if (in_array($key, ['text', 'alt', 'button_text', 'button_url', 'video_url', 'shortcode'])) {
                $sanitized[$key] = sanitize_text_field($value);
            }
            // Campi HTML
            elseif ($key === 'content') {
                $sanitized[$key] = wp_kses_post($value);
            }
            // Numeri
            elseif (in_array($key, ['image_id', 'columns', 'height', 'font_size', 'gallery_ids'])) {
                if ($key === 'gallery_ids') {
                    // Per gallery_ids, può essere una stringa con ID separati da virgola
                    $sanitized[$key] = sanitize_text_field($value);
                } else {
                    $sanitized[$key] = absint($value);
                }
            }
            // Colori
            elseif (strpos($key, 'color') !== false || strpos($key, '_color') !== false) {
                $sanitized[$key] = sanitize_hex_color($value);
            }
            // Array complessi (features, counters, faqs, tabs)
            elseif (in_array($key, ['features', 'counters', 'faqs', 'tabs']) && is_array($value)) {
                $sanitized[$key] = $this->sanitize_complex_array($key, $value);
            }
            // Altri campi
            else {
                $sanitized[$key] = sanitize_text_field($value);
            }
        }
        
        return $sanitized;
    }
    
    /**
     * Sanitizza array complessi (features, counters, etc.)
     */
    private function sanitize_complex_array($type, $items) {
        $sanitized = [];
        
        foreach ($items as $item) {
            if (!is_array($item)) {
                continue;
            }
            
            $sanitized_item = [];
            foreach ($item as $key => $value) {
                if ($key === 'text' || $key === 'content' || $key === 'answer') {
                    $sanitized_item[$key] = wp_kses_post($value);
                } elseif (strpos($key, 'color') !== false || strpos($key, '_color') !== false) {
                    // Sanitizza colori hex
                    $sanitized_item[$key] = sanitize_hex_color($value);
                } else {
                    $sanitized_item[$key] = sanitize_text_field($value);
                }
            }
            $sanitized[] = $sanitized_item;
        }
        
        return $sanitized;
    }
}
