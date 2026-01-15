<?php
/**
 * Meta Boxes per Landing Pages
 *
 * @package FPLandingPage
 */

namespace FPLandingPage\Admin;

defined('ABSPATH') || exit;

/**
 * Classe per gestire i meta box delle landing page
 */
class MetaBoxes {
    
    /**
     * Costruttore
     */
    public function __construct() {
        add_action('add_meta_boxes', [$this, 'add_meta_boxes'], 5);
        add_action('save_post', [$this, 'save_meta_boxes'], 10, 2);
    }
    
    /**
     * Aggiunge meta boxes
     */
    public function add_meta_boxes() {
        add_meta_box(
            'fp_landing_page_settings',
            __('Impostazioni Landing Page', 'fp-landing-page'),
            [$this, 'render_settings_metabox'],
            'fp_landing_page',
            'normal',
            'core' // Priorità massima per apparire prima di SEO
        );
        
        add_meta_box(
            'fp_landing_page_shortcodes',
            __('Shortcodes Disponibili', 'fp-landing-page'),
            [$this, 'render_shortcodes_metabox'],
            'fp_landing_page',
            'side',
            'default'
        );
    }
    
    /**
     * Render meta box impostazioni
     */
    public function render_settings_metabox($post) {
        wp_nonce_field('fp_landing_page_meta_box', 'fp_landing_page_meta_box_nonce');
        
        $bg_color = get_post_meta($post->ID, '_fp_landing_bg_color', true) ?: '#ffffff';
        $text_color = get_post_meta($post->ID, '_fp_landing_text_color', true) ?: '#333333';
        $header_style = get_post_meta($post->ID, '_fp_landing_header_style', true) ?: 'default';
        $footer_text = get_post_meta($post->ID, '_fp_landing_footer_text', true) ?: '';
        
        ?>
        <table class="form-table">
            <tr>
                <th><label for="fp_landing_bg_color"><?php _e('Colore Sfondo', 'fp-landing-page'); ?></label></th>
                <td>
                    <input type="color" id="fp_landing_bg_color" name="fp_landing_bg_color" value="<?php echo esc_attr($bg_color); ?>" />
                </td>
            </tr>
            <tr>
                <th><label for="fp_landing_text_color"><?php _e('Colore Testo', 'fp-landing-page'); ?></label></th>
                <td>
                    <input type="color" id="fp_landing_text_color" name="fp_landing_text_color" value="<?php echo esc_attr($text_color); ?>" />
                </td>
            </tr>
            <tr>
                <th><label for="fp_landing_header_style"><?php _e('Stile Header', 'fp-landing-page'); ?></label></th>
                <td>
                    <select id="fp_landing_header_style" name="fp_landing_header_style">
                        <option value="default" <?php selected($header_style, 'default'); ?>><?php _e('Default', 'fp-landing-page'); ?></option>
                        <option value="transparent" <?php selected($header_style, 'transparent'); ?>><?php _e('Trasparente', 'fp-landing-page'); ?></option>
                        <option value="hidden" <?php selected($header_style, 'hidden'); ?>><?php _e('Nascosto', 'fp-landing-page'); ?></option>
                    </select>
                </td>
            </tr>
            <tr>
                <th><label for="fp_landing_footer_text"><?php _e('Testo Footer', 'fp-landing-page'); ?></label></th>
                <td>
                    <textarea id="fp_landing_footer_text" name="fp_landing_footer_text" rows="3" style="width: 100%;"><?php echo esc_textarea($footer_text); ?></textarea>
                    <p class="description"><?php _e('Testo da mostrare nel footer della landing page', 'fp-landing-page'); ?></p>
                </td>
            </tr>
        </table>
        <?php
    }
    
    /**
     * Render meta box shortcodes
     */
    public function render_shortcodes_metabox($post) {
        ?>
        <div class="fp-landing-shortcodes-help">
            <p><strong><?php _e('Come usare questa landing page:', 'fp-landing-page'); ?></strong></p>
            <p><?php _e('Crea il contenuto di questa landing page usando gli shortcode delle sezioni qui sotto, poi inserisci questo shortcode in qualsiasi pagina:', 'fp-landing-page'); ?></p>
            <div style="background: #f0f0f1; padding: 10px; border-radius: 4px; margin: 10px 0;">
                <code style="font-size: 14px;">[fp_landing_page id="<?php echo esc_attr($post->ID); ?>"]</code>
            </div>
            
            <p style="margin-top: 20px;"><strong><?php _e('Shortcodes per sezioni:', 'fp-landing-page'); ?></strong></p>
            <ul style="list-style: disc; margin-left: 20px;">
                <li><code>[fp_lp_hero]</code> - Sezione Hero</li>
                <li><code>[fp_lp_features]</code> - Sezione Features</li>
                <li><code>[fp_lp_cta]</code> - Call to Action</li>
                <li><code>[fp_lp_testimonials]</code> - Testimonial</li>
                <li><code>[fp_lp_pricing]</code> - Tabelle Prezzi</li>
                <li><code>[fp_lp_form]</code> - Form Contatto</li>
            </ul>
        </div>
        <?php
    }
    
    /**
     * Salva meta boxes
     */
    public function save_meta_boxes($post_id, $post) {
        // Verifica nonce
        if (!isset($_POST['fp_landing_page_meta_box_nonce']) || 
            !wp_verify_nonce($_POST['fp_landing_page_meta_box_nonce'], 'fp_landing_page_meta_box')) {
            return;
        }
        
        // Verifica autosave
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }
        
        // Verifica permessi
        if (!current_user_can('edit_post', $post_id)) {
            return;
        }
        
        // Verifica post type
        if ($post->post_type !== 'fp_landing_page') {
            return;
        }
        
        // Salva meta fields
        $fields = [
            'fp_landing_bg_color',
            'fp_landing_text_color',
            'fp_landing_header_style',
            'fp_landing_footer_text',
        ];
        
        foreach ($fields as $field) {
            if (isset($_POST[$field])) {
                update_post_meta($post_id, '_' . $field, sanitize_text_field($_POST[$field]));
            }
        }
    }
}
