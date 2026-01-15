<?php
/**
 * Builder Visuale per Landing Pages
 *
 * @package FPLandingPage
 */

namespace FPLandingPage\Admin;

defined('ABSPATH') || exit;

/**
 * Classe per gestire il builder visuale delle landing page
 */
class LandingPageBuilder {
    /**
     * Istanza singleton.
     *
     * @var self|null
     */
    private static $instance = null;
    
    /**
     * Costruttore
     */
    public function __construct() {
        add_action('add_meta_boxes', [$this, 'add_builder_metabox']);
        add_action('save_post', [$this, 'save_builder_data'], 10, 2);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_builder_scripts']);
        add_action('wp_ajax_fp_lp_get_image', [$this, 'ajax_get_image']);
        add_action('wp_ajax_fp_lp_get_gallery', [$this, 'ajax_get_gallery']);
    }

    /**
     * Ottiene l'istanza singleton.
     *
     * @return self
     */
    public static function get_instance() {
        if (!self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Aggiunge meta box del builder
     */
    public function add_builder_metabox() {
        add_meta_box(
            'fp_landing_page_builder',
            __('Builder Landing Page', 'fp-landing-page'),
            [$this, 'render_builder'],
            'fp_landing_page',
            'normal',
            'core' // Priorità massima per apparire prima di SEO
        );
    }
    
    /**
     * Renderizza il builder
     */
    public function render_builder($post) {
        wp_nonce_field('fp_landing_page_builder', 'fp_landing_page_builder_nonce');
        
        $sections = get_post_meta($post->ID, '_fp_landing_page_sections', true);
        if (!is_array($sections)) {
            $sections = [];
        }
        
        ?>
        <div id="fp-lp-builder" class="fp-lp-builder-container">
            <div class="fp-lp-builder-toolbar">
                <button type="button" class="button fp-lp-add-section" data-section-type="title">Titolo</button>
                <button type="button" class="button fp-lp-add-section" data-section-type="text">Testo</button>
                <button type="button" class="button fp-lp-add-section" data-section-type="image">Immagine</button>
                <button type="button" class="button fp-lp-add-section" data-section-type="gallery">Galleria</button>
                <button type="button" class="button fp-lp-add-section" data-section-type="cta">CTA</button>
                <button type="button" class="button fp-lp-add-section" data-section-type="video">Video</button>
                <button type="button" class="button fp-lp-add-section" data-section-type="separator">Separatore</button>
                <button type="button" class="button fp-lp-add-section" data-section-type="features">Features</button>
                <button type="button" class="button fp-lp-add-section" data-section-type="counters">Contatori</button>
                <button type="button" class="button fp-lp-add-section" data-section-type="faq">FAQ</button>
                <button type="button" class="button fp-lp-add-section" data-section-type="tabs">Tabs</button>
                <button type="button" class="button fp-lp-add-section" data-section-type="shortcode">Shortcode</button>
                
                <span class="fp-lp-toolbar-separator"></span>
                
                <button type="button" class="button fp-lp-collapse-all" title="<?php _e('Comprimi tutte le sezioni', 'fp-landing-page'); ?>">▲ Comprimi</button>
                <button type="button" class="button fp-lp-expand-all" title="<?php _e('Espandi tutte le sezioni', 'fp-landing-page'); ?>">▼ Espandi</button>
                
                <span class="fp-lp-save-indicator ready">✓ <?php _e('Pronto', 'fp-landing-page'); ?></span>
            </div>
            
            <div id="fp-lp-sections-list" class="fp-lp-sections-list">
                <?php if (empty($sections)): ?>
                    <p class="fp-lp-empty-state"><?php _e('Nessuna sezione aggiunta. Clicca sui pulsanti sopra per aggiungere sezioni.', 'fp-landing-page'); ?></p>
                <?php else: ?>
                    <?php foreach ($sections as $index => $section): ?>
                        <?php $this->render_section_editor($index, $section); ?>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
            
            <input type="hidden" id="fp-lp-sections-data" name="fp_landing_page_sections_data" value="<?php echo esc_attr(json_encode($sections)); ?>">
        </div>
        <?php
    }
    
    /**
     * Renderizza editor per una sezione
     */
    private function render_section_editor($index, $section) {
        $type = isset($section['type']) ? $section['type'] : 'text';
        $data = isset($section['data']) ? $section['data'] : [];
        $icon = $this->get_section_icon($type);
        $item_count = $this->get_item_count($type, $data);
        
        ?>
        <div class="fp-lp-section-item" data-index="<?php echo esc_attr($index); ?>" data-type="<?php echo esc_attr($type); ?>">
            <div class="fp-lp-section-header">
                <span class="fp-lp-drag-handle">⋮⋮</span>
                <span class="fp-lp-section-icon"><?php echo $icon; ?></span>
                <span class="fp-lp-section-title">
                    <?php echo esc_html($this->get_section_title($type)); ?>
                    <?php if ($item_count !== null): ?>
                        <span class="fp-lp-item-count"><?php echo $item_count; ?> <?php echo $item_count === 1 ? 'elemento' : 'elementi'; ?></span>
                    <?php endif; ?>
                </span>
                <div class="fp-lp-section-actions">
                    <button type="button" class="button-link fp-lp-duplicate-section" title="<?php _e('Duplica', 'fp-landing-page'); ?>">📋</button>
                    <button type="button" class="button-link fp-lp-move-up" title="<?php _e('Sposta su', 'fp-landing-page'); ?>">⬆</button>
                    <button type="button" class="button-link fp-lp-move-down" title="<?php _e('Sposta giù', 'fp-landing-page'); ?>">⬇</button>
                    <button type="button" class="button-link fp-lp-toggle-section" title="<?php _e('Espandi/Comprimi', 'fp-landing-page'); ?>">▼</button>
                    <button type="button" class="button-link fp-lp-remove-section" style="color: #b32d2e;" title="<?php _e('Rimuovi', 'fp-landing-page'); ?>">✕</button>
                </div>
            </div>
            <div class="fp-lp-section-content">
                <?php $this->render_section_fields($type, $index, $data); ?>
            </div>
        </div>
        <?php
    }
    
    /**
     * Renderizza i campi per una sezione
     */
    private function render_section_fields($type, $index, $data) {
        switch ($type) {
            case 'title':
                $this->render_title_fields($index, $data);
                break;
            case 'text':
                $this->render_text_fields($index, $data);
                break;
            case 'image':
                $this->render_image_fields($index, $data);
                break;
            case 'gallery':
                $this->render_gallery_fields($index, $data);
                break;
            case 'shortcode':
                $this->render_shortcode_fields($index, $data);
                break;
            case 'cta':
                $this->render_cta_fields($index, $data);
                break;
            case 'video':
                $this->render_video_fields($index, $data);
                break;
            case 'separator':
                $this->render_separator_fields($index, $data);
                break;
            case 'features':
                $this->render_features_fields($index, $data);
                break;
            case 'counters':
                $this->render_counters_fields($index, $data);
                break;
            case 'faq':
                $this->render_faq_fields($index, $data);
                break;
            case 'tabs':
                $this->render_tabs_fields($index, $data);
                break;
        }
        // Aggiungi campi di personalizzazione comuni per tutte le sezioni
        $this->render_section_customization_fields($index, $data);
    }
    
    /**
     * Campi Titolo
     */
    private function render_title_fields($index, $data) {
        ?>
        <table class="form-table">
            <tr>
                <th><label>Testo Titolo</label></th>
                <td>
                    <input type="text" class="fp-lp-field" data-field="text" value="<?php echo esc_attr((isset($data['text']) ? $data['text'] : '')); ?>" style="width: 100%;" placeholder="Inserisci il titolo">
                </td>
            </tr>
            <tr>
                <th><label>Livello (H1, H2, H3)</label></th>
                <td>
                    <select class="fp-lp-field" data-field="level">
                        <option value="h1" <?php selected((isset($data['level']) ? $data['level'] : 'h2'), 'h1'); ?>>H1</option>
                        <option value="h2" <?php selected((isset($data['level']) ? $data['level'] : 'h2'), 'h2'); ?>>H2</option>
                        <option value="h3" <?php selected((isset($data['level']) ? $data['level'] : 'h2'), 'h3'); ?>>H3</option>
                        <option value="h4" <?php selected((isset($data['level']) ? $data['level'] : 'h2'), 'h4'); ?>>H4</option>
                    </select>
                </td>
            </tr>
            <tr>
                <th><label>Allineamento</label></th>
                <td>
                    <select class="fp-lp-field" data-field="align">
                        <option value="left" <?php selected((isset($data['align']) ? $data['align'] : 'left'), 'left'); ?>>Sinistra</option>
                        <option value="center" <?php selected((isset($data['align']) ? $data['align'] : 'left'), 'center'); ?>>Centro</option>
                        <option value="right" <?php selected((isset($data['align']) ? $data['align'] : 'left'), 'right'); ?>>Destra</option>
                    </select>
                </td>
            </tr>
        </table>
        
        <details class="fp-lp-collapsible">
            <summary>Personalizzazioni Avanzate Titolo</summary>
            <div class="fp-lp-collapsible-content">
                <table>
                    <tr>
                        <th><label>Dimensione Font (px)</label></th>
                        <td>
                            <input type="number" class="fp-lp-field" data-field="font_size" value="<?php echo esc_attr(isset($data['font_size']) ? $data['font_size'] : ''); ?>" placeholder="36" min="12" max="120" style="width: 100%;">
                            <p class="description">Dimensione del font in pixel (es: 36 per H1, 24 per H2)</p>
                        </td>
                    </tr>
                    <tr>
                        <th><label>Colore Testo</label></th>
                        <td>
                            <input type="text" class="fp-lp-field" data-field="text_color" value="<?php echo esc_attr(isset($data['text_color']) ? $data['text_color'] : ''); ?>" placeholder="#333333" style="width: 100%;">
                            <p class="description">Colore del testo (es: #333333, #000000)</p>
                        </td>
                    </tr>
                    <tr>
                        <th><label>Peso Font</label></th>
                        <td>
                            <select class="fp-lp-field" data-field="font_weight">
                                <option value="" <?php selected((isset($data['font_weight']) ? $data['font_weight'] : ''), ''); ?>>Default</option>
                                <option value="300" <?php selected(isset($data['font_weight']) ? $data['font_weight'] : '', '300'); ?>>300 (Light)</option>
                                <option value="400" <?php selected(isset($data['font_weight']) ? $data['font_weight'] : '', '400'); ?>>400 (Normal)</option>
                                <option value="600" <?php selected(isset($data['font_weight']) ? $data['font_weight'] : '', '600'); ?>>600 (Semi-Bold)</option>
                                <option value="700" <?php selected(isset($data['font_weight']) ? $data['font_weight'] : '', '700'); ?>>700 (Bold)</option>
                                <option value="800" <?php selected(isset($data['font_weight']) ? $data['font_weight'] : '', '800'); ?>>800 (Extra-Bold)</option>
                            </select>
                        </td>
                    </tr>
                </table>
            </div>
        </details>
        
        <details class="fp-lp-collapsible">
            <summary>Opzioni Responsive Titolo</summary>
            <div class="fp-lp-collapsible-content">
                <table>
                    <tr>
                        <th><label>Font Size Mobile (px)</label></th>
                        <td>
                            <input type="number" class="fp-lp-field" data-field="font_size_mobile" value="<?php echo esc_attr(isset($data['font_size_mobile']) ? $data['font_size_mobile'] : ''); ?>" placeholder="24" min="12" max="120" style="width: 100%;">
                            <p class="description">Dimensione font su mobile (&lt;768px)</p>
                        </td>
                    </tr>
                    <tr>
                        <th><label>Font Size Tablet (px)</label></th>
                        <td>
                            <input type="number" class="fp-lp-field" data-field="font_size_tablet" value="<?php echo esc_attr(isset($data['font_size_tablet']) ? $data['font_size_tablet'] : ''); ?>" placeholder="30" min="12" max="120" style="width: 100%;">
                            <p class="description">Dimensione font su tablet (768px-1024px)</p>
                        </td>
                    </tr>
                    <tr>
                        <th><label>Font Size Desktop (px)</label></th>
                        <td>
                            <input type="number" class="fp-lp-field" data-field="font_size_desktop" value="<?php echo esc_attr(isset($data['font_size_desktop']) ? $data['font_size_desktop'] : ''); ?>" placeholder="36" min="12" max="120" style="width: 100%;">
                            <p class="description">Dimensione font su desktop (&gt;1024px)</p>
                        </td>
                    </tr>
                    <tr>
                        <th><label>Allineamento Mobile</label></th>
                        <td>
                            <select class="fp-lp-field" data-field="align_mobile">
                                <option value="" <?php selected((isset($data['align_mobile']) ? $data['align_mobile'] : ''), ''); ?>>Usa allineamento generale</option>
                                <option value="left" <?php selected(isset($data['align_mobile']) ? $data['align_mobile'] : '', 'left'); ?>>Sinistra</option>
                                <option value="center" <?php selected(isset($data['align_mobile']) ? $data['align_mobile'] : '', 'center'); ?>>Centro</option>
                                <option value="right" <?php selected(isset($data['align_mobile']) ? $data['align_mobile'] : '', 'right'); ?>>Destra</option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <th><label>Allineamento Tablet</label></th>
                        <td>
                            <select class="fp-lp-field" data-field="align_tablet">
                                <option value="" <?php selected((isset($data['align_tablet']) ? $data['align_tablet'] : ''), ''); ?>>Usa allineamento generale</option>
                                <option value="left" <?php selected(isset($data['align_tablet']) ? $data['align_tablet'] : '', 'left'); ?>>Sinistra</option>
                                <option value="center" <?php selected(isset($data['align_tablet']) ? $data['align_tablet'] : '', 'center'); ?>>Centro</option>
                                <option value="right" <?php selected(isset($data['align_tablet']) ? $data['align_tablet'] : '', 'right'); ?>>Destra</option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <th><label>Allineamento Desktop</label></th>
                        <td>
                            <select class="fp-lp-field" data-field="align_desktop">
                                <option value="" <?php selected((isset($data['align_desktop']) ? $data['align_desktop'] : ''), ''); ?>>Usa allineamento generale</option>
                                <option value="left" <?php selected(isset($data['align_desktop']) ? $data['align_desktop'] : '', 'left'); ?>>Sinistra</option>
                                <option value="center" <?php selected(isset($data['align_desktop']) ? $data['align_desktop'] : '', 'center'); ?>>Centro</option>
                                <option value="right" <?php selected(isset($data['align_desktop']) ? $data['align_desktop'] : '', 'right'); ?>>Destra</option>
                            </select>
                        </td>
                    </tr>
                </table>
            </div>
        </details>
        <?php
    }
    
    /**
     * Campi Testo
     */
    private function render_text_fields($index, $data) {
        ?>
        <table class="form-table">
            <tr>
                <th><label>Contenuto</label></th>
                <td>
                    <textarea class="fp-lp-field" data-field="content" rows="8" style="width: 100%;"><?php echo esc_textarea(isset($data['content']) ? $data['content'] : ''); ?></textarea>
                    <p class="description">HTML consentito</p>
                </td>
            </tr>
            <tr>
                <th><label>Allineamento</label></th>
                <td>
                    <select class="fp-lp-field" data-field="align">
                        <option value="left" <?php selected((isset($data['align']) ? $data['align'] : 'left'), 'left'); ?>>Sinistra</option>
                        <option value="center" <?php selected((isset($data['align']) ? $data['align'] : 'left'), 'center'); ?>>Centro</option>
                        <option value="right" <?php selected((isset($data['align']) ? $data['align'] : 'left'), 'right'); ?>>Destra</option>
                        <option value="justify" <?php selected((isset($data['align']) ? $data['align'] : 'left'), 'justify'); ?>>Giustificato</option>
                    </select>
                </td>
            </tr>
        </table>
        
        <details class="fp-lp-collapsible">
            <summary>Opzioni Responsive Testo</summary>
            <div class="fp-lp-collapsible-content">
                <table>
                    <tr>
                        <th><label>Allineamento Mobile</label></th>
                        <td>
                            <select class="fp-lp-field" data-field="align_mobile">
                                <option value="" <?php selected((isset($data['align_mobile']) ? $data['align_mobile'] : ''), ''); ?>>Usa allineamento generale</option>
                                <option value="left" <?php selected(isset($data['align_mobile']) ? $data['align_mobile'] : '', 'left'); ?>>Sinistra</option>
                                <option value="center" <?php selected(isset($data['align_mobile']) ? $data['align_mobile'] : '', 'center'); ?>>Centro</option>
                                <option value="right" <?php selected(isset($data['align_mobile']) ? $data['align_mobile'] : '', 'right'); ?>>Destra</option>
                                <option value="justify" <?php selected(isset($data['align_mobile']) ? $data['align_mobile'] : '', 'justify'); ?>>Giustificato</option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <th><label>Allineamento Tablet</label></th>
                        <td>
                            <select class="fp-lp-field" data-field="align_tablet">
                                <option value="" <?php selected((isset($data['align_tablet']) ? $data['align_tablet'] : ''), ''); ?>>Usa allineamento generale</option>
                                <option value="left" <?php selected(isset($data['align_tablet']) ? $data['align_tablet'] : '', 'left'); ?>>Sinistra</option>
                                <option value="center" <?php selected(isset($data['align_tablet']) ? $data['align_tablet'] : '', 'center'); ?>>Centro</option>
                                <option value="right" <?php selected(isset($data['align_tablet']) ? $data['align_tablet'] : '', 'right'); ?>>Destra</option>
                                <option value="justify" <?php selected(isset($data['align_tablet']) ? $data['align_tablet'] : '', 'justify'); ?>>Giustificato</option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <th><label>Allineamento Desktop</label></th>
                        <td>
                            <select class="fp-lp-field" data-field="align_desktop">
                                <option value="" <?php selected((isset($data['align_desktop']) ? $data['align_desktop'] : ''), ''); ?>>Usa allineamento generale</option>
                                <option value="left" <?php selected(isset($data['align_desktop']) ? $data['align_desktop'] : '', 'left'); ?>>Sinistra</option>
                                <option value="center" <?php selected(isset($data['align_desktop']) ? $data['align_desktop'] : '', 'center'); ?>>Centro</option>
                                <option value="right" <?php selected(isset($data['align_desktop']) ? $data['align_desktop'] : '', 'right'); ?>>Destra</option>
                                <option value="justify" <?php selected(isset($data['align_desktop']) ? $data['align_desktop'] : '', 'justify'); ?>>Giustificato</option>
                            </select>
                        </td>
                    </tr>
                </table>
            </div>
        </details>
        <?php
    }
    
    /**
     * Campi Immagine
     */
    private function render_image_fields($index, $data) {
        $image_id = isset($data['image_id']) ? $data['image_id'] : '';
        $image_url = '';
        if ($image_id) {
            $image_url = wp_get_attachment_image_url($image_id, 'medium');
        }
        ?>
        <table class="form-table">
            <tr>
                <th><label>Immagine</label></th>
                <td>
                    <div class="fp-lp-image-preview" style="margin-bottom: 10px;">
                        <?php if ($image_url): ?>
                            <img src="<?php echo esc_url($image_url); ?>" style="max-width: 200px; height: auto; display: block;">
                        <?php else: ?>
                            <div style="width: 200px; height: 150px; background: #f0f0f0; display: flex; align-items: center; justify-content: center; color: #666;">
                                Nessuna immagine
                            </div>
                        <?php endif; ?>
                    </div>
                    <input type="hidden" class="fp-lp-field" data-field="image_id" value="<?php echo esc_attr($image_id); ?>">
                    <button type="button" class="button fp-lp-select-image" data-index="<?php echo esc_attr($index); ?>">
                        <?php echo $image_id ? __('Cambia Immagine', 'fp-landing-page') : __('Seleziona Immagine', 'fp-landing-page'); ?>
                    </button>
                    <button type="button" class="button fp-lp-remove-image" data-index="<?php echo esc_attr($index); ?>" style="<?php echo $image_id ? '' : 'display:none;'; ?>">
                        <?php _e('Rimuovi', 'fp-landing-page'); ?>
                    </button>
                </td>
            </tr>
            <tr>
                <th><label>Alt Text</label></th>
                <td>
                    <input type="text" class="fp-lp-field" data-field="alt" value="<?php echo esc_attr(isset($data['alt']) ? $data['alt'] : ''); ?>" style="width: 100%;" placeholder="Testo alternativo">
                </td>
            </tr>
            <tr>
                <th><label>Allineamento</label></th>
                <td>
                    <select class="fp-lp-field" data-field="align">
                        <option value="left" <?php selected((isset($data['align']) ? $data['align'] : 'center'), 'left'); ?>>Sinistra</option>
                        <option value="center" <?php selected((isset($data['align']) ? $data['align'] : 'center'), 'center'); ?>>Centro</option>
                        <option value="right" <?php selected((isset($data['align']) ? $data['align'] : 'center'), 'right'); ?>>Destra</option>
                    </select>
                </td>
            </tr>
            <tr>
                <th><label>Link (URL)</label></th>
                <td>
                    <input type="url" class="fp-lp-field" data-field="link" value="<?php echo esc_attr(isset($data['link']) ? $data['link'] : ''); ?>" style="width: 100%;" placeholder="https://">
                </td>
            </tr>
        </table>
        
        <details class="fp-lp-collapsible">
            <summary>Personalizzazioni Avanzate Immagine</summary>
            <div class="fp-lp-collapsible-content">
                <table>
                    <tr>
                        <th><label>Max Width (px o %)</label></th>
                        <td>
                            <input type="text" class="fp-lp-field" data-field="max_width" value="<?php echo esc_attr(isset($data['max_width']) ? $data['max_width'] : ''); ?>" placeholder="800px o 100%" style="width: 100%;">
                            <p class="description">Larghezza massima dell'immagine (es: "800px", "100%", "50vw")</p>
                        </td>
                    </tr>
                    <tr>
                        <th><label>Border Radius (px)</label></th>
                        <td>
                            <input type="number" class="fp-lp-field" data-field="border_radius" value="<?php echo esc_attr(isset($data['border_radius']) ? $data['border_radius'] : ''); ?>" placeholder="0" min="0" max="50" style="width: 100%;">
                            <p class="description">Arrotondamento degli angoli (0-50px)</p>
                        </td>
                    </tr>
                    <tr>
                        <th><label>Box Shadow</label></th>
                        <td>
                            <input type="text" class="fp-lp-field" data-field="box_shadow" value="<?php echo esc_attr(isset($data['box_shadow']) ? $data['box_shadow'] : ''); ?>" placeholder="0 2px 8px rgba(0,0,0,0.1)" style="width: 100%;">
                            <p class="description">Ombra CSS personalizzata (es: "0 2px 8px rgba(0,0,0,0.1)")</p>
                        </td>
                    </tr>
                </table>
            </div>
        </details>
        
        <details class="fp-lp-collapsible">
            <summary>Opzioni Responsive Immagine</summary>
            <div class="fp-lp-collapsible-content">
                <table>
            <tr>
                <th><label>Max Width Mobile (px o %)</label></th>
                <td>
                    <input type="text" class="fp-lp-field" data-field="max_width_mobile" value="<?php echo esc_attr(isset($data['max_width_mobile']) ? $data['max_width_mobile'] : ''); ?>" placeholder="100%" style="width: 100%;">
                    <p class="description">Larghezza massima su mobile (&lt;768px)</p>
                </td>
            </tr>
            <tr>
                <th><label>Max Width Tablet (px o %)</label></th>
                <td>
                    <input type="text" class="fp-lp-field" data-field="max_width_tablet" value="<?php echo esc_attr(isset($data['max_width_tablet']) ? $data['max_width_tablet'] : ''); ?>" placeholder="80%" style="width: 100%;">
                    <p class="description">Larghezza massima su tablet (768px-1024px)</p>
                </td>
            </tr>
            <tr>
                <th><label>Max Width Desktop (px o %)</label></th>
                <td>
                    <input type="text" class="fp-lp-field" data-field="max_width_desktop" value="<?php echo esc_attr(isset($data['max_width_desktop']) ? $data['max_width_desktop'] : ''); ?>" placeholder="800px" style="width: 100%;">
                    <p class="description">Larghezza massima su desktop (&gt;1024px)</p>
                </td>
            </tr>
            <tr>
                <th><label>Allineamento Mobile</label></th>
                <td>
                    <select class="fp-lp-field" data-field="align_mobile">
                        <option value="" <?php selected((isset($data['align_mobile']) ? $data['align_mobile'] : ''), ''); ?>>Usa allineamento generale</option>
                        <option value="left" <?php selected(isset($data['align_mobile']) ? $data['align_mobile'] : '', 'left'); ?>>Sinistra</option>
                        <option value="center" <?php selected(isset($data['align_mobile']) ? $data['align_mobile'] : '', 'center'); ?>>Centro</option>
                        <option value="right" <?php selected(isset($data['align_mobile']) ? $data['align_mobile'] : '', 'right'); ?>>Destra</option>
                    </select>
                </td>
            </tr>
            <tr>
                <th><label>Allineamento Tablet</label></th>
                <td>
                    <select class="fp-lp-field" data-field="align_tablet">
                        <option value="" <?php selected((isset($data['align_tablet']) ? $data['align_tablet'] : ''), ''); ?>>Usa allineamento generale</option>
                        <option value="left" <?php selected(isset($data['align_tablet']) ? $data['align_tablet'] : '', 'left'); ?>>Sinistra</option>
                        <option value="center" <?php selected(isset($data['align_tablet']) ? $data['align_tablet'] : '', 'center'); ?>>Centro</option>
                        <option value="right" <?php selected(isset($data['align_tablet']) ? $data['align_tablet'] : '', 'right'); ?>>Destra</option>
                    </select>
                </td>
            </tr>
                    <tr>
                        <th><label>Allineamento Desktop</label></th>
                        <td>
                            <select class="fp-lp-field" data-field="align_desktop">
                                <option value="" <?php selected((isset($data['align_desktop']) ? $data['align_desktop'] : ''), ''); ?>>Usa allineamento generale</option>
                                <option value="left" <?php selected(isset($data['align_desktop']) ? $data['align_desktop'] : '', 'left'); ?>>Sinistra</option>
                                <option value="center" <?php selected(isset($data['align_desktop']) ? $data['align_desktop'] : '', 'center'); ?>>Centro</option>
                                <option value="right" <?php selected(isset($data['align_desktop']) ? $data['align_desktop'] : '', 'right'); ?>>Destra</option>
                            </select>
                        </td>
                    </tr>
                </table>
            </div>
        </details>
        <?php
    }
    
    /**
     * Campi Galleria
     */
    private function render_gallery_fields($index, $data) {
        $gallery_ids = (isset($data['gallery_ids']) ? $data['gallery_ids'] : '');
        $ids_array = !empty($gallery_ids) ? explode(',', $gallery_ids) : [];
        ?>
        <table class="form-table">
            <tr>
                <th><label>Galleria</label></th>
                <td>
                    <div class="fp-lp-gallery-preview" style="display: flex; flex-wrap: wrap; gap: 10px; margin-bottom: 10px;">
                        <?php if (!empty($ids_array)): ?>
                            <?php foreach ($ids_array as $img_id): ?>
                                <?php $img_url = wp_get_attachment_image_url($img_id, 'thumbnail'); ?>
                                <?php if ($img_url): ?>
                                    <img src="<?php echo esc_url($img_url); ?>" style="width: 80px; height: 80px; object-fit: cover; border: 1px solid #ddd;">
                                <?php endif; ?>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p style="color: #666;">Nessuna immagine nella galleria</p>
                        <?php endif; ?>
                    </div>
                    <input type="hidden" class="fp-lp-field" data-field="gallery_ids" value="<?php echo esc_attr($gallery_ids); ?>">
                    <button type="button" class="button fp-lp-select-gallery" data-index="<?php echo esc_attr($index); ?>">
                        <?php echo !empty($ids_array) ? __('Modifica Galleria', 'fp-landing-page') : __('Crea Galleria', 'fp-landing-page'); ?>
                    </button>
                    <button type="button" class="button fp-lp-remove-gallery" data-index="<?php echo esc_attr($index); ?>" style="<?php echo !empty($ids_array) ? '' : 'display:none;'; ?>">
                        <?php _e('Rimuovi Galleria', 'fp-landing-page'); ?>
                    </button>
                </td>
            </tr>
            <tr>
                <th><label>Colonne</label></th>
                <td>
                    <select class="fp-lp-field" data-field="columns">
                        <option value="2" <?php selected(isset($data['columns']) ? $data['columns'] : '3', '2'); ?>>2</option>
                        <option value="3" <?php selected(isset($data['columns']) ? $data['columns'] : '3', '3'); ?>>3</option>
                        <option value="4" <?php selected(isset($data['columns']) ? $data['columns'] : '3', '4'); ?>>4</option>
                    </select>
                </td>
            </tr>
        </table>
        
        <details class="fp-lp-collapsible">
            <summary>Personalizzazioni Avanzate Galleria</summary>
            <div class="fp-lp-collapsible-content">
                <table>
                    <tr>
                        <th><label>Border Radius Immagini (px)</label></th>
                        <td>
                            <input type="number" class="fp-lp-field" data-field="image_border_radius" value="<?php echo esc_attr(isset($data['image_border_radius']) ? $data['image_border_radius'] : ''); ?>" placeholder="4" min="0" max="50" style="width: 100%;">
                            <p class="description">Arrotondamento degli angoli delle immagini (0-50px)</p>
                        </td>
                    </tr>
                    <tr>
                        <th><label>Gap tra Immagini (px)</label></th>
                        <td>
                            <input type="number" class="fp-lp-field" data-field="gap" value="<?php echo esc_attr(isset($data['gap']) ? $data['gap'] : ''); ?>" placeholder="10" min="0" max="50" style="width: 100%;">
                            <p class="description">Spaziatura tra le immagini della galleria (0-50px)</p>
                        </td>
                    </tr>
                </table>
            </div>
        </details>
        <?php
    }
    
    /**
     * Campi Shortcode
     */
    private function render_shortcode_fields($index, $data) {
        ?>
        <table class="form-table">
            <tr>
                <th><label>Shortcode</label></th>
                <td>
                    <textarea class="fp-lp-field" data-field="shortcode" rows="3" style="width: 100%; font-family: monospace;"><?php echo esc_textarea(isset($data['shortcode']) ? $data['shortcode'] : ''); ?></textarea>
                    <p class="description">Inserisci lo shortcode completo, es: [fp_form id="123"]</p>
                </td>
            </tr>
        </table>
        <?php
    }
    
    /**
     * Campi CTA
     */
    private function render_cta_fields($index, $data) {
        ?>
        <table class="form-table">
            <tr>
                <th><label>Testo Pulsante</label></th>
                <td>
                    <input type="text" class="fp-lp-field" data-field="button_text" value="<?php echo esc_attr(isset($data['button_text']) ? $data['button_text'] : ''); ?>" style="width: 100%;">
                </td>
            </tr>
            <tr>
                <th><label>URL</label></th>
                <td>
                    <input type="url" class="fp-lp-field" data-field="button_url" value="<?php echo esc_attr(isset($data['button_url']) ? $data['button_url'] : '#'); ?>" style="width: 100%;">
                </td>
            </tr>
            <tr>
                <th><label>Stile</label></th>
                <td>
                    <select class="fp-lp-field" data-field="style">
                        <option value="primary" <?php selected(isset($data['style']) ? $data['style'] : 'primary', 'primary'); ?>>Primario</option>
                        <option value="secondary" <?php selected(isset($data['style']) ? $data['style'] : 'primary', 'secondary'); ?>>Secondario</option>
                        <option value="outline" <?php selected(isset($data['style']) ? $data['style'] : 'primary', 'outline'); ?>>Outline</option>
                    </select>
                </td>
            </tr>
            <tr>
                <th><label>Allineamento</label></th>
                <td>
                    <select class="fp-lp-field" data-field="align">
                        <option value="left" <?php selected((isset($data['align']) ? $data['align'] : 'center'), 'left'); ?>>Sinistra</option>
                        <option value="center" <?php selected((isset($data['align']) ? $data['align'] : 'center'), 'center'); ?>>Centro</option>
                        <option value="right" <?php selected((isset($data['align']) ? $data['align'] : 'center'), 'right'); ?>>Destra</option>
                    </select>
                </td>
            </tr>
        </table>
        
        <details class="fp-lp-collapsible">
            <summary>Personalizzazioni Avanzate CTA</summary>
            <div class="fp-lp-collapsible-content">
                <table>
                    <tr>
                        <th><label>Colore Background Pulsante</label></th>
                        <td>
                            <input type="text" class="fp-lp-field" data-field="button_bg_color" value="<?php echo esc_attr(isset($data['button_bg_color']) ? $data['button_bg_color'] : ''); ?>" placeholder="#0073aa" style="width: 100%;">
                            <p class="description">Colore di sfondo del pulsante (es: #0073aa, #333333)</p>
                        </td>
                    </tr>
                    <tr>
                        <th><label>Colore Testo Pulsante</label></th>
                        <td>
                            <input type="text" class="fp-lp-field" data-field="button_text_color" value="<?php echo esc_attr(isset($data['button_text_color']) ? $data['button_text_color'] : ''); ?>" placeholder="#ffffff" style="width: 100%;">
                            <p class="description">Colore del testo del pulsante (es: #ffffff, #000000)</p>
                        </td>
                    </tr>
                    <tr>
                        <th><label>Border Radius (px)</label></th>
                        <td>
                            <input type="number" class="fp-lp-field" data-field="button_border_radius" value="<?php echo esc_attr(isset($data['button_border_radius']) ? $data['button_border_radius'] : ''); ?>" placeholder="4" min="0" max="50" style="width: 100%;">
                            <p class="description">Arrotondamento degli angoli (es: 4 per angoli leggermente arrotondati, 50 per completamente rotondo)</p>
                        </td>
                    </tr>
                </table>
            </div>
        </details>
        
        <details class="fp-lp-collapsible">
            <summary>Opzioni Responsive CTA</summary>
            <div class="fp-lp-collapsible-content">
                <table>
                    <tr>
                        <th><label>Allineamento Mobile</label></th>
                        <td>
                            <select class="fp-lp-field" data-field="align_mobile">
                                <option value="" <?php selected((isset($data['align_mobile']) ? $data['align_mobile'] : ''), ''); ?>>Usa allineamento generale</option>
                                <option value="left" <?php selected(isset($data['align_mobile']) ? $data['align_mobile'] : '', 'left'); ?>>Sinistra</option>
                                <option value="center" <?php selected(isset($data['align_mobile']) ? $data['align_mobile'] : '', 'center'); ?>>Centro</option>
                                <option value="right" <?php selected(isset($data['align_mobile']) ? $data['align_mobile'] : '', 'right'); ?>>Destra</option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <th><label>Allineamento Tablet</label></th>
                        <td>
                            <select class="fp-lp-field" data-field="align_tablet">
                                <option value="" <?php selected((isset($data['align_tablet']) ? $data['align_tablet'] : ''), ''); ?>>Usa allineamento generale</option>
                                <option value="left" <?php selected(isset($data['align_tablet']) ? $data['align_tablet'] : '', 'left'); ?>>Sinistra</option>
                                <option value="center" <?php selected(isset($data['align_tablet']) ? $data['align_tablet'] : '', 'center'); ?>>Centro</option>
                                <option value="right" <?php selected(isset($data['align_tablet']) ? $data['align_tablet'] : '', 'right'); ?>>Destra</option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <th><label>Allineamento Desktop</label></th>
                        <td>
                            <select class="fp-lp-field" data-field="align_desktop">
                                <option value="" <?php selected((isset($data['align_desktop']) ? $data['align_desktop'] : ''), ''); ?>>Usa allineamento generale</option>
                                <option value="left" <?php selected(isset($data['align_desktop']) ? $data['align_desktop'] : '', 'left'); ?>>Sinistra</option>
                                <option value="center" <?php selected(isset($data['align_desktop']) ? $data['align_desktop'] : '', 'center'); ?>>Centro</option>
                                <option value="right" <?php selected(isset($data['align_desktop']) ? $data['align_desktop'] : '', 'right'); ?>>Destra</option>
                            </select>
                        </td>
                    </tr>
                </table>
            </div>
        </details>
        <?php
    }
    
    /**
     * Campi Video
     */
    private function render_video_fields($index, $data) {
        ?>
        <table class="form-table">
            <tr>
                <th><label>URL Video (YouTube/Vimeo)</label></th>
                <td>
                    <input type="url" class="fp-lp-field" data-field="video_url" value="<?php echo esc_attr(isset($data['video_url']) ? $data['video_url'] : ''); ?>" style="width: 100%;" placeholder="https://youtube.com/watch?v=...">
                    <p class="description">Incolla l'URL completo di YouTube o Vimeo</p>
                </td>
            </tr>
            <tr>
                <th><label>Allineamento</label></th>
                <td>
                    <select class="fp-lp-field" data-field="align">
                        <option value="left" <?php selected((isset($data['align']) ? $data['align'] : 'center'), 'left'); ?>>Sinistra</option>
                        <option value="center" <?php selected((isset($data['align']) ? $data['align'] : 'center'), 'center'); ?>>Centro</option>
                        <option value="right" <?php selected((isset($data['align']) ? $data['align'] : 'center'), 'right'); ?>>Destra</option>
                    </select>
                </td>
            </tr>
        </table>
        <?php
    }
    
    /**
     * Campi Separatore
     */
    private function render_separator_fields($index, $data) {
        ?>
        <table class="form-table">
            <tr>
                <th><label>Stile</label></th>
                <td>
                    <select class="fp-lp-field" data-field="style">
                        <option value="solid" <?php selected(isset($data['style']) ? $data['style'] : 'solid', 'solid'); ?>>Linea Solida</option>
                        <option value="dashed" <?php selected(isset($data['style']) ? $data['style'] : 'solid', 'dashed'); ?>>Linea Tratteggiata</option>
                        <option value="dotted" <?php selected(isset($data['style']) ? $data['style'] : 'solid', 'dotted'); ?>>Linea Puntinata</option>
                        <option value="space" <?php selected(isset($data['style']) ? $data['style'] : 'solid', 'space'); ?>>Spazio</option>
                    </select>
                </td>
            </tr>
            <tr>
                <th><label>Altezza (px)</label></th>
                <td>
                    <input type="number" class="fp-lp-field" data-field="height" value="<?php echo esc_attr(isset($data['height']) ? $data['height'] : '40'); ?>" min="10" max="200" step="10">
                </td>
            </tr>
        </table>
        <?php
    }
    
    /**
     * Campi Features
     */
    private function render_features_fields($index, $data) {
        $features = isset($data['features']) ? $data['features'] : [];
        $columns = isset($data['columns']) ? $data['columns'] : '3';
        if (empty($features)) {
            $features = [['icon' => '', 'title' => '', 'text' => '']];
        }
        ?>
        <table class="form-table">
            <tr>
                <th><label>Colonne</label></th>
                <td>
                    <select class="fp-lp-field" data-field="columns">
                        <option value="2" <?php selected($columns, '2'); ?>>2</option>
                        <option value="3" <?php selected($columns, '3'); ?>>3</option>
                        <option value="4" <?php selected($columns, '4'); ?>>4</option>
                    </select>
                </td>
            </tr>
            <tr>
                <th><label>Features</label></th>
                <td>
                    <div class="fp-lp-features-list" data-index="<?php echo esc_attr($index); ?>">
                        <?php foreach ($features as $i => $feature): ?>
                            <div class="fp-lp-feature-item" style="border: 1px solid #ddd; padding: 15px; margin-bottom: 10px; background: #f9f9f9;">
                                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
                                    <strong>Feature #<?php echo ($i + 1); ?></strong>
                                    <button type="button" class="button fp-lp-remove-feature" data-index="<?php echo esc_attr($index); ?>" data-feature-index="<?php echo esc_attr($i); ?>" style="<?php echo count($features) <= 1 ? 'display:none;' : ''; ?>">Rimuovi</button>
                                </div>
                                <table style="width: 100%;">
                                    <tr>
                                        <td style="padding: 5px 0;"><label>Icona (classe CSS, es: fa fa-star)</label></td>
                                        <td style="padding: 5px 0;">
                                            <input type="text" class="fp-lp-feature-field" data-field="icon" data-feature-index="<?php echo esc_attr($i); ?>" value="<?php echo esc_attr((isset($feature['icon']) ? $feature['icon'] : '')); ?>" style="width: 100%;" placeholder="fa fa-star">
                                            <p class="description">Usa classi Font Awesome o altre librerie di icone</p>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td style="padding: 5px 0;"><label>Titolo</label></td>
                                        <td style="padding: 5px 0;">
                                            <input type="text" class="fp-lp-feature-field" data-field="title" data-feature-index="<?php echo esc_attr($i); ?>" value="<?php echo esc_attr(isset($feature['title']) ? $feature['title'] : ''); ?>" style="width: 100%;" placeholder="Titolo feature">
                                        </td>
                                    </tr>
                                    <tr>
                                        <td style="padding: 5px 0;"><label>Testo</label></td>
                                        <td style="padding: 5px 0;">
                                            <textarea class="fp-lp-feature-field" data-field="text" data-feature-index="<?php echo esc_attr($i); ?>" rows="3" style="width: 100%;" placeholder="Descrizione feature"><?php echo esc_textarea(isset($feature['text']) ? $feature['text'] : ''); ?></textarea>
                                        </td>
                                    </tr>
                                </table>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <button type="button" class="button fp-lp-add-feature" data-index="<?php echo esc_attr($index); ?>">+ Aggiungi Feature</button>
                </td>
            </tr>
        </table>
        <?php
    }
    
    /**
     * Campi Contatori
     */
    private function render_counters_fields($index, $data) {
        $counters = isset($data['counters']) ? $data['counters'] : [];
        $columns = (isset($data['columns']) ? $data['columns'] : '4');
        if (empty($counters)) {
            $counters = [['number' => '', 'label' => '', 'prefix' => '', 'suffix' => '', 'icon' => '']];
        }
        ?>
        <table class="form-table">
            <tr>
                <th><label>Colonne</label></th>
                <td>
                    <select class="fp-lp-field" data-field="columns">
                        <option value="2" <?php selected($columns, '2'); ?>>2</option>
                        <option value="3" <?php selected($columns, '3'); ?>>3</option>
                        <option value="4" <?php selected($columns, '4'); ?>>4</option>
                    </select>
                </td>
            </tr>
            <tr>
                <th><label>Contatori</label></th>
                <td>
                    <div class="fp-lp-counters-list" data-index="<?php echo esc_attr($index); ?>">
                        <?php foreach ($counters as $i => $counter): ?>
                            <div class="fp-lp-counter-item" style="border: 1px solid #ddd; padding: 15px; margin-bottom: 10px; background: #f9f9f9;">
                                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
                                    <strong>Contatore #<?php echo ($i + 1); ?></strong>
                                    <button type="button" class="button fp-lp-remove-counter" data-index="<?php echo esc_attr($index); ?>" data-counter-index="<?php echo esc_attr($i); ?>" style="<?php echo count($counters) <= 1 ? 'display:none;' : ''; ?>">Rimuovi</button>
                                </div>
                                <table style="width: 100%;">
                                    <tr>
                                        <td style="padding: 5px 0;"><label>Icona (classe CSS, opzionale)</label></td>
                                        <td style="padding: 5px 0;">
                                            <input type="text" class="fp-lp-counter-field" data-field="icon" data-counter-index="<?php echo esc_attr($i); ?>" value="<?php echo esc_attr(isset($counter['icon']) ? $counter['icon'] : ''); ?>" style="width: 100%;" placeholder="fa fa-users">
                                        </td>
                                    </tr>
                                    <tr>
                                        <td style="padding: 5px 0;"><label>Numero</label></td>
                                        <td style="padding: 5px 0;">
                                            <input type="text" class="fp-lp-counter-field" data-field="number" data-counter-index="<?php echo esc_attr($i); ?>" value="<?php echo esc_attr(isset($counter['number']) ? $counter['number'] : ''); ?>" style="width: 100%;" placeholder="1000">
                                        </td>
                                    </tr>
                                    <tr>
                                        <td style="padding: 5px 0;"><label>Etichetta</label></td>
                                        <td style="padding: 5px 0;">
                                            <input type="text" class="fp-lp-counter-field" data-field="label" data-counter-index="<?php echo esc_attr($i); ?>" value="<?php echo esc_attr(isset($counter['label']) ? $counter['label'] : ''); ?>" style="width: 100%;" placeholder="Clienti Soddisfatti">
                                        </td>
                                    </tr>
                                    <tr>
                                        <td style="padding: 5px 0;"><label>Prefisso (opzionale)</label></td>
                                        <td style="padding: 5px 0;">
                                            <input type="text" class="fp-lp-counter-field" data-field="prefix" data-counter-index="<?php echo esc_attr($i); ?>" value="<?php echo esc_attr(isset($counter['prefix']) ? $counter['prefix'] : ''); ?>" style="width: 100%;" placeholder="€">
                                        </td>
                                    </tr>
                                    <tr>
                                        <td style="padding: 5px 0;"><label>Suffisso (opzionale)</label></td>
                                        <td style="padding: 5px 0;">
                                            <input type="text" class="fp-lp-counter-field" data-field="suffix" data-counter-index="<?php echo esc_attr($i); ?>" value="<?php echo esc_attr(isset($counter['suffix']) ? $counter['suffix'] : ''); ?>" style="width: 100%;" placeholder="+">
                                        </td>
                                    </tr>
                                </table>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <button type="button" class="button fp-lp-add-counter" data-index="<?php echo esc_attr($index); ?>">+ Aggiungi Contatore</button>
                </td>
            </tr>
        </table>
        <?php
    }
    
    /**
     * Campi FAQ
     */
    private function render_faq_fields($index, $data) {
        $faqs = isset($data['faqs']) ? $data['faqs'] : [];
        if (empty($faqs)) {
            $faqs = [['question' => '', 'answer' => '']];
        }
        ?>
        <table class="form-table">
            <tr>
                <th><label>FAQ</label></th>
                <td>
                    <div class="fp-lp-faqs-list" data-index="<?php echo esc_attr($index); ?>">
                        <?php foreach ($faqs as $i => $faq): ?>
                            <div class="fp-lp-faq-item" style="border: 1px solid #ddd; padding: 15px; margin-bottom: 10px; background: #f9f9f9;">
                                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
                                    <strong>FAQ #<?php echo ($i + 1); ?></strong>
                                    <button type="button" class="button fp-lp-remove-faq" data-index="<?php echo esc_attr($index); ?>" data-faq-index="<?php echo esc_attr($i); ?>" style="<?php echo count($faqs) <= 1 ? 'display:none;' : ''; ?>">Rimuovi</button>
                                </div>
                                <table style="width: 100%;">
                                    <tr>
                                        <td style="padding: 5px 0;"><label>Domanda</label></td>
                                        <td style="padding: 5px 0;">
                                            <input type="text" class="fp-lp-faq-field" data-field="question" data-faq-index="<?php echo esc_attr($i); ?>" value="<?php echo esc_attr(isset($faq['question']) ? $faq['question'] : ''); ?>" style="width: 100%;" placeholder="La domanda frequente">
                                        </td>
                                    </tr>
                                    <tr>
                                        <td style="padding: 5px 0;"><label>Risposta</label></td>
                                        <td style="padding: 5px 0;">
                                            <textarea class="fp-lp-faq-field" data-field="answer" data-faq-index="<?php echo esc_attr($i); ?>" rows="4" style="width: 100%;" placeholder="La risposta alla domanda"><?php echo esc_textarea(isset($faq['answer']) ? $faq['answer'] : ''); ?></textarea>
                                        </td>
                                    </tr>
                                </table>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <button type="button" class="button fp-lp-add-faq" data-index="<?php echo esc_attr($index); ?>">+ Aggiungi FAQ</button>
                </td>
            </tr>
        </table>
        <?php
    }
    
    /**
     * Campi Tabs
     */
    private function render_tabs_fields($index, $data) {
        $tabs = isset($data['tabs']) ? $data['tabs'] : [];
        if (empty($tabs)) {
            $tabs = [['title' => '', 'content' => '']];
        }
        ?>
        <table class="form-table">
            <tr>
                <th><label>Tabs</label></th>
                <td>
                    <div class="fp-lp-tabs-list" data-index="<?php echo esc_attr($index); ?>">
                        <?php foreach ($tabs as $i => $tab): ?>
                            <div class="fp-lp-tab-item" style="border: 1px solid #ddd; padding: 15px; margin-bottom: 10px; background: #f9f9f9;">
                                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
                                    <strong>Tab #<?php echo ($i + 1); ?></strong>
                                    <button type="button" class="button fp-lp-remove-tab" data-index="<?php echo esc_attr($index); ?>" data-tab-index="<?php echo esc_attr($i); ?>" style="<?php echo count($tabs) <= 1 ? 'display:none;' : ''; ?>">Rimuovi</button>
                                </div>
                                <table style="width: 100%;">
                                    <tr>
                                        <td style="padding: 5px 0;"><label>Titolo Tab</label></td>
                                        <td style="padding: 5px 0;">
                                            <input type="text" class="fp-lp-tab-field" data-field="title" data-tab-index="<?php echo esc_attr($i); ?>" value="<?php echo esc_attr(isset($tab['title']) ? $tab['title'] : ''); ?>" style="width: 100%;" placeholder="Nome Tab">
                                        </td>
                                    </tr>
                                    <tr>
                                        <td style="padding: 5px 0;"><label>Contenuto</label></td>
                                        <td style="padding: 5px 0;">
                                            <textarea class="fp-lp-tab-field" data-field="content" data-tab-index="<?php echo esc_attr($i); ?>" rows="6" style="width: 100%;" placeholder="Contenuto del tab"><?php echo esc_textarea(isset($tab['content']) ? $tab['content'] : ''); ?></textarea>
                                            <p class="description">HTML consentito</p>
                                        </td>
                                    </tr>
                                </table>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <button type="button" class="button fp-lp-add-tab" data-index="<?php echo esc_attr($index); ?>">+ Aggiungi Tab</button>
                </td>
            </tr>
        </table>
        <?php
    }
    
    /**
     * Renderizza campi di personalizzazione comuni per tutte le sezioni
     */
    private function render_section_customization_fields($index, $data) {
        ?>
        <details class="fp-lp-collapsible">
            <summary>Personalizzazioni CSS</summary>
            <div class="fp-lp-collapsible-content">
                <table>
                    <tr>
                        <th><label>Colore Background</label></th>
                        <td>
                            <input type="text" class="fp-lp-field" data-field="bg_color" value="<?php echo esc_attr(isset($data['bg_color']) ? $data['bg_color'] : ''); ?>" placeholder="#ffffff o trasparente" style="width: 100%;">
                            <p class="description">Colore di sfondo della sezione (es: #ffffff, #f5f5f5, trasparente)</p>
                        </td>
                    </tr>
                    <tr>
                        <th><label>Padding (px)</label></th>
                        <td>
                            <input type="text" class="fp-lp-field" data-field="padding" value="<?php echo esc_attr(isset($data['padding']) ? $data['padding'] : ''); ?>" placeholder="20px" style="width: 100%;">
                            <p class="description">Spaziatura interna (es: "20px" o "20px 10px" per top-bottom left-right)</p>
                        </td>
                    </tr>
                    <tr>
                        <th><label>Margin (px)</label></th>
                        <td>
                            <input type="text" class="fp-lp-field" data-field="margin" value="<?php echo esc_attr(isset($data['margin']) ? $data['margin'] : ''); ?>" placeholder="20px" style="width: 100%;">
                            <p class="description">Spaziatura esterna (es: "20px" o "20px 10px")</p>
                        </td>
                    </tr>
                    <tr>
                        <th><label>Classe CSS Personalizzata</label></th>
                        <td>
                            <input type="text" class="fp-lp-field" data-field="css_class" value="<?php echo esc_attr(isset($data['css_class']) ? $data['css_class'] : ''); ?>" placeholder="mia-classe" style="width: 100%;">
                            <p class="description">Aggiungi una classe CSS personalizzata (senza punto)</p>
                        </td>
                    </tr>
                    <tr>
                        <th><label>ID HTML Personalizzato</label></th>
                        <td>
                            <input type="text" class="fp-lp-field" data-field="css_id" value="<?php echo esc_attr(isset($data['css_id']) ? $data['css_id'] : ''); ?>" placeholder="mio-id" style="width: 100%;">
                            <p class="description">Aggiungi un ID HTML personalizzato (senza #)</p>
                        </td>
                    </tr>
                </table>
            </div>
        </details>
        
        <details class="fp-lp-collapsible">
            <summary>Opzioni Responsive Sezione</summary>
            <div class="fp-lp-collapsible-content">
                <table>
                    <tr>
                        <th><label>Padding Mobile (px)</label></th>
                        <td>
                            <input type="text" class="fp-lp-field" data-field="padding_mobile" value="<?php echo esc_attr(isset($data['padding_mobile']) ? $data['padding_mobile'] : ''); ?>" placeholder="10px" style="width: 100%;">
                            <p class="description">Spaziatura interna su dispositivi mobile (&lt;768px)</p>
                        </td>
                    </tr>
                    <tr>
                        <th><label>Padding Tablet (px)</label></th>
                        <td>
                            <input type="text" class="fp-lp-field" data-field="padding_tablet" value="<?php echo esc_attr(isset($data['padding_tablet']) ? $data['padding_tablet'] : ''); ?>" placeholder="15px" style="width: 100%;">
                            <p class="description">Spaziatura interna su tablet (768px-1024px)</p>
                        </td>
                    </tr>
                    <tr>
                        <th><label>Padding Desktop (px)</label></th>
                        <td>
                            <input type="text" class="fp-lp-field" data-field="padding_desktop" value="<?php echo esc_attr(isset($data['padding_desktop']) ? $data['padding_desktop'] : ''); ?>" placeholder="20px" style="width: 100%;">
                            <p class="description">Spaziatura interna su desktop (&gt;1024px)</p>
                        </td>
                    </tr>
                    <tr>
                        <th><label>Margin Mobile (px)</label></th>
                        <td>
                            <input type="text" class="fp-lp-field" data-field="margin_mobile" value="<?php echo esc_attr(isset($data['margin_mobile']) ? $data['margin_mobile'] : ''); ?>" placeholder="10px" style="width: 100%;">
                            <p class="description">Spaziatura esterna su dispositivi mobile</p>
                        </td>
                    </tr>
                    <tr>
                        <th><label>Margin Tablet (px)</label></th>
                        <td>
                            <input type="text" class="fp-lp-field" data-field="margin_tablet" value="<?php echo esc_attr(isset($data['margin_tablet']) ? $data['margin_tablet'] : ''); ?>" placeholder="15px" style="width: 100%;">
                            <p class="description">Spaziatura esterna su tablet</p>
                        </td>
                    </tr>
                    <tr>
                        <th><label>Margin Desktop (px)</label></th>
                        <td>
                            <input type="text" class="fp-lp-field" data-field="margin_desktop" value="<?php echo esc_attr(isset($data['margin_desktop']) ? $data['margin_desktop'] : ''); ?>" placeholder="20px" style="width: 100%;">
                            <p class="description">Spaziatura esterna su desktop</p>
                        </td>
                    </tr>
                </table>
            </div>
        </details>
        <table class="form-table" style="display:none;">
            <tr>
                <th><label>Visibilità</label></th>
                <td>
                    <label style="margin-right: 15px;">
                        <input type="checkbox" class="fp-lp-field" data-field="hide_mobile" value="1" <?php checked((isset($data['hide_mobile']) ? $data['hide_mobile'] : ''), '1'); ?>>
                        Nascondi su Mobile
                    </label>
                    <label style="margin-right: 15px;">
                        <input type="checkbox" class="fp-lp-field" data-field="hide_tablet" value="1" <?php checked((isset($data['hide_tablet']) ? $data['hide_tablet'] : ''), '1'); ?>>
                        Nascondi su Tablet
                    </label>
                    <label>
                        <input type="checkbox" class="fp-lp-field" data-field="hide_desktop" value="1" <?php checked((isset($data['hide_desktop']) ? $data['hide_desktop'] : ''), '1'); ?>>
                        Nascondi su Desktop
                    </label>
                </td>
            </tr>
        </table>
        <?php
    }
    
    /**
     * Nome sezione
     */
    private function get_section_title($type) {
        $titles = [
            'title' => 'Titolo',
            'text' => 'Testo',
            'image' => 'Immagine',
            'gallery' => 'Galleria',
            'shortcode' => 'Shortcode',
            'cta' => 'Call to Action',
            'video' => 'Video',
            'separator' => 'Separatore',
            'features' => 'Features',
            'counters' => 'Contatori',
            'faq' => 'FAQ',
            'tabs' => 'Tabs',
        ];
        return isset($titles[$type]) ? $titles[$type] : $type;
    }
    
    /**
     * Ottiene l'icona per un tipo di sezione
     */
    private function get_section_icon($type) {
        $icons = [
            'title' => '📝',
            'text' => '📄',
            'image' => '🖼️',
            'gallery' => '🎨',
            'shortcode' => '⚡',
            'cta' => '🔘',
            'video' => '🎬',
            'separator' => '➖',
            'features' => '⭐',
            'counters' => '🔢',
            'faq' => '❓',
            'tabs' => '📑',
        ];
        return isset($icons[$type]) ? $icons[$type] : '📦';
    }
    
    /**
     * Conta gli elementi in sezioni complesse
     */
    private function get_item_count($type, $data) {
        switch ($type) {
            case 'features':
                return isset($data['features']) && is_array($data['features']) ? count($data['features']) : 0;
            case 'counters':
                return isset($data['counters']) && is_array($data['counters']) ? count($data['counters']) : 0;
            case 'faq':
                return isset($data['faqs']) && is_array($data['faqs']) ? count($data['faqs']) : 0;
            case 'tabs':
                return isset($data['tabs']) && is_array($data['tabs']) ? count($data['tabs']) : 0;
            default:
                return null;
        }
    }
    
    /**
     * Salva dati builder
     */
    public function save_builder_data($post_id, $post) {
        // Verifica nonce
        if (!isset($_POST['fp_landing_page_builder_nonce']) || 
            !wp_verify_nonce($_POST['fp_landing_page_builder_nonce'], 'fp_landing_page_builder')) {
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
        
        // Salva sezioni
        if (isset($_POST['fp_landing_page_sections_data'])) {
            $sections = json_decode(stripslashes($_POST['fp_landing_page_sections_data']), true);
            if (is_array($sections)) {
                update_post_meta($post_id, '_fp_landing_page_sections', $sections);
                
                // Inserisci lo shortcode nel post_content per garantire il rendering
                $shortcode = '[fp_landing_page id="' . $post_id . '"]';
                $current_content = $post->post_content;
                
                // Se il contenuto non ha già lo shortcode, aggiungilo
                if (strpos($current_content, '[fp_landing_page') === false) {
                    // Rimuovi il filter per evitare loop infinito
                    remove_action('save_post', [$this, 'save_builder_data'], 10);
                    
                    wp_update_post([
                        'ID' => $post_id,
                        'post_content' => $shortcode,
                    ]);
                    
                    // Riaggiungi il filter
                    add_action('save_post', [$this, 'save_builder_data'], 10, 2);
                }
            }
        }
    }
    
    /**
     * Carica script builder
     */
    public function enqueue_builder_scripts($hook) {
        if ($hook !== 'post.php' && $hook !== 'post-new.php') {
            return;
        }

        $screen = function_exists('get_current_screen') ? get_current_screen() : null;
        if (!$screen || $screen->post_type !== 'fp_landing_page') {
            return;
        }
        
        wp_enqueue_media();
        wp_enqueue_script('jquery-ui-sortable');
        
        wp_enqueue_script(
            'fp-landing-page-builder',
            FP_LANDING_PAGE_URL . 'assets/js/builder.js',
            ['jquery', 'jquery-ui-sortable'],
            FP_LANDING_PAGE_VERSION,
            true
        );
        
        wp_enqueue_style(
            'fp-landing-page-builder',
            FP_LANDING_PAGE_URL . 'assets/css/builder.css',
            [],
            FP_LANDING_PAGE_VERSION
        );
        
        // Stili per sezioni collassabili
        wp_add_inline_style('fp-landing-page-builder', '
            .fp-lp-collapsible {
                margin-top: 15px;
                border-top: 1px solid #ddd;
            }
            .fp-lp-collapsible summary {
                cursor: pointer;
                padding: 10px 0;
                font-weight: 600;
                color: #1d2327;
                list-style: none;
                display: flex;
                align-items: center;
                gap: 8px;
            }
            .fp-lp-collapsible summary::-webkit-details-marker {
                display: none;
            }
            .fp-lp-collapsible summary::before {
                content: "▶";
                font-size: 10px;
                transition: transform 0.2s;
            }
            .fp-lp-collapsible[open] summary::before {
                transform: rotate(90deg);
            }
            .fp-lp-collapsible summary:hover {
                color: #2271b1;
            }
            .fp-lp-collapsible-content {
                padding-bottom: 10px;
            }
            .fp-lp-collapsible-content table {
                width: 100%;
            }
        ');
        
        // Localize script
        wp_localize_script('fp-landing-page-builder', 'fpLandingPageBuilder', [
            'ajaxurl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('fp_landing_page_builder')
        ]);
    }
    
    /**
     * AJAX: Get image URL
     */
    public function ajax_get_image() {
        check_ajax_referer('fp_landing_page_builder', 'nonce');
        
        $image_id = absint(isset($_POST['image_id']) ? $_POST['image_id'] : 0);
        if ($image_id) {
            $url = wp_get_attachment_image_url($image_id, 'medium');
            if ($url) {
                wp_send_json_success(['url' => $url]);
            }
        }
        
        wp_send_json_error();
    }
    
    /**
     * AJAX: Get gallery images
     */
    public function ajax_get_gallery() {
        check_ajax_referer('fp_landing_page_builder', 'nonce');
        
        $gallery_ids = sanitize_text_field(isset($_POST['gallery_ids']) ? $_POST['gallery_ids'] : '');
        if ($gallery_ids) {
            $ids = array_filter(array_map('absint', explode(',', $gallery_ids)));
            $images = [];
            
            foreach ($ids as $id) {
                $url = wp_get_attachment_image_url($id, 'thumbnail');
                if ($url) {
                    $images[] = ['id' => $id, 'url' => $url];
                }
            }
            
            if (!empty($images)) {
                wp_send_json_success(['images' => $images]);
            }
        }
        
        wp_send_json_error();
    }
}
