<?php
/**
 * Shortcodes per landing page
 *
 * @package FPLandingPage
 */

namespace FPLandingPage\Shortcodes;

defined('ABSPATH') || exit;

/**
 * Classe per gestire gli shortcodes delle landing page
 */
class Landing {
    
    /**
     * Registra gli shortcodes
     */
    public static function register() {
        // Shortcode principale - mostra l'intera landing page
        add_shortcode('fp_landing_page', [__CLASS__, 'landing_page']);
        
        // Gli asset vengono caricati dalla classe Template quando necessario
        // add_action('wp_enqueue_scripts', [__CLASS__, 'maybe_enqueue_assets'], 20);
    }
    
    /**
     * Carica gli asset frontend se necessario
     */
    public static function maybe_enqueue_assets() {
        global $post;
        
        // Verifica che le costanti siano definite
        if (!defined('FP_LANDING_PAGE_URL') || !defined('FP_LANDING_PAGE_VERSION')) {
            return;
        }
        
        // Carica se siamo su una landing page o se il contenuto contiene lo shortcode
        if ($post && (
            is_singular('fp_landing_page') || 
            (isset($post->post_content) && has_shortcode($post->post_content, 'fp_landing_page'))
        )) {
            // CSS frontend
            if (!wp_style_is('fp-landing-page-frontend', 'enqueued')) {
                wp_enqueue_style(
                    'fp-landing-page-frontend',
                    FP_LANDING_PAGE_URL . 'assets/css/fp-landing-page.css',
                    [],
                    FP_LANDING_PAGE_VERSION
                );
            }
            
            // JS frontend - solo se il file esiste
            if (defined('FP_LANDING_PAGE_DIR')) {
                $js_path = FP_LANDING_PAGE_DIR . 'assets/js/frontend.js';
                if (file_exists($js_path) && !wp_script_is('fp-landing-page-frontend', 'enqueued')) {
                    wp_enqueue_script(
                        'fp-landing-page-frontend',
                        FP_LANDING_PAGE_URL . 'assets/js/frontend.js',
                        ['jquery'],
                        FP_LANDING_PAGE_VERSION,
                        true
                    );
                }
            }
        }
    }
    
    /**
     * Shortcode principale: Mostra una landing page completa
     * [fp_landing_page id="123"]
     */
    public static function landing_page($atts) {
        $atts = shortcode_atts([
            'id' => 0,
        ], $atts);
        
        $landing_id = absint($atts['id']);
        
        if (!$landing_id) {
            return '<p class="fp-lp-error">' . esc_html__('ID landing page non valido', 'fp-landing-page') . '</p>';
        }
        
        $landing = get_post($landing_id);
        
        if (!$landing || $landing->post_type !== 'fp_landing_page') {
            return '<p class="fp-lp-error">' . esc_html__('Landing page non trovata', 'fp-landing-page') . '</p>';
        }
        
        if ($landing->post_status !== 'publish') {
            return '<p class="fp-lp-error">' . esc_html__('Landing page non pubblicata', 'fp-landing-page') . '</p>';
        }
        
        // Ottieni i meta per stili
        $bg_color = get_post_meta($landing_id, '_fp_landing_bg_color', true);
        $text_color = get_post_meta($landing_id, '_fp_landing_text_color', true);
        
        // Leggi sezioni dal builder
        $sections = get_post_meta($landing_id, '_fp_landing_page_sections', true);
        
        ob_start();
        ?>
        <div class="fp-landing-page-container" 
             id="fp-landing-page-<?php echo esc_attr($landing_id); ?>"
             style="<?php echo $bg_color ? 'background-color: ' . esc_attr($bg_color) . ';' : ''; ?> <?php echo $text_color ? 'color: ' . esc_attr($text_color) . ';' : ''; ?>">
            <?php
            // Se ci sono sezioni dal builder, usale
            if (is_array($sections) && !empty($sections)) {
                foreach ($sections as $section) {
                    self::render_section_from_builder($section);
                }
            } else {
                // Fallback: usa il contenuto (compatibilità con vecchie landing page)
                echo apply_filters('the_content', $landing->post_content);
            }
            
            // Footer personalizzato se presente
            $footer_text = get_post_meta($landing_id, '_fp_landing_footer_text', true);
            if ($footer_text) {
                ?>
                <div class="fp-landing-page-footer">
                    <?php echo wp_kses_post($footer_text); ?>
                </div>
                <?php
            }
            ?>
        </div>
        <?php
        return ob_get_clean();
    }
    
    /**
     * Renderizza le sezioni di una landing page (metodo pubblico per template)
     */
    public static function render_sections($landing_id) {
        $sections = get_post_meta($landing_id, '_fp_landing_page_sections', true);
        
        if (is_array($sections) && !empty($sections)) {
            foreach ($sections as $section) {
                self::render_section_from_builder($section);
            }
        }
    }
    
    /**
     * Genera attributi di stile e classe per personalizzazioni comuni
     */
    private static function get_section_attributes($data) {
        $style = '';
        $classes = ['fp-lp-section'];
        $id = '';
        $data_attrs = '';
        
        // Background color
        if (!empty($data['bg_color'])) {
            $style .= 'background-color: ' . esc_attr($data['bg_color']) . ';';
        }
        
        // Padding (base) - solo se specificato, altrimenti usa i valori CSS
        if (!empty($data['padding'])) {
            $style .= 'padding: ' . esc_attr($data['padding']) . ';';
        }
        
        // Margin (base) - solo se specificato, altrimenti usa i valori CSS
        if (!empty($data['margin'])) {
            $style .= 'margin: ' . esc_attr($data['margin']) . ';';
        }
        
        // NOTA: Se padding/margin non sono specificati, gli stili CSS di base verranno applicati
        // Questo permette al CSS di gestire i margini standard
        
        // Responsive padding/margin - salvataggio in data attributes per CSS
        if (!empty($data['padding_mobile'])) {
            $data_attrs .= ' data-padding-mobile="' . esc_attr($data['padding_mobile']) . '"';
        }
        if (!empty($data['padding_tablet'])) {
            $data_attrs .= ' data-padding-tablet="' . esc_attr($data['padding_tablet']) . '"';
        }
        if (!empty($data['padding_desktop'])) {
            $data_attrs .= ' data-padding-desktop="' . esc_attr($data['padding_desktop']) . '"';
        }
        if (!empty($data['margin_mobile'])) {
            $data_attrs .= ' data-margin-mobile="' . esc_attr($data['margin_mobile']) . '"';
        }
        if (!empty($data['margin_tablet'])) {
            $data_attrs .= ' data-margin-tablet="' . esc_attr($data['margin_tablet']) . '"';
        }
        if (!empty($data['margin_desktop'])) {
            $data_attrs .= ' data-margin-desktop="' . esc_attr($data['margin_desktop']) . '"';
        }
        
        // Visibilità responsive - aggiungi le classi hide SOLO se NON sono tutti e 3 settati
        // (se tutti e 3 sono settati è un errore di configurazione e li ignoriamo)
        $hide_mobile = isset($data['hide_mobile']) && ($data['hide_mobile'] === true || $data['hide_mobile'] === 1 || $data['hide_mobile'] === '1' || $data['hide_mobile'] === 'on');
        $hide_tablet = isset($data['hide_tablet']) && ($data['hide_tablet'] === true || $data['hide_tablet'] === 1 || $data['hide_tablet'] === '1' || $data['hide_tablet'] === 'on');
        $hide_desktop = isset($data['hide_desktop']) && ($data['hide_desktop'] === true || $data['hide_desktop'] === 1 || $data['hide_desktop'] === '1' || $data['hide_desktop'] === 'on');
        
        // Se tutti e 3 sono settati, è un errore di configurazione - ignora tutti
        if (!($hide_mobile && $hide_tablet && $hide_desktop)) {
            if ($hide_mobile) {
                $classes[] = 'fp-lp-hide-mobile';
            }
            if ($hide_tablet) {
                $classes[] = 'fp-lp-hide-tablet';
            }
            if ($hide_desktop) {
                $classes[] = 'fp-lp-hide-desktop';
            }
        }
        
        // CSS class
        if (!empty($data['css_class'])) {
            $classes[] = esc_attr($data['css_class']);
        }
        
        // ID
        if (!empty($data['css_id'])) {
            $id = esc_attr($data['css_id']);
        }
        
        return [
            'style' => $style,
            'class' => implode(' ', $classes),
            'id' => $id,
            'data_attrs' => $data_attrs
        ];
    }
    
    /**
     * Renderizza una sezione dai dati del builder
     */
    private static function render_section_from_builder($section) {
        $type = isset($section['type']) ? $section['type'] : '';
        $data = isset($section['data']) ? $section['data'] : [];
        $attrs = self::get_section_attributes($data);
        
        // Wrapper per personalizzazioni comuni
        $wrapper_attrs = '';
        if (!empty($attrs['style'])) {
            $wrapper_attrs .= ' style="' . $attrs['style'] . '"';
        }
        if (!empty($attrs['class'])) {
            $wrapper_attrs .= ' class="' . $attrs['class'] . '"';
        }
        if (!empty($attrs['id'])) {
            $wrapper_attrs .= ' id="' . $attrs['id'] . '"';
        }
        if (!empty($attrs['data_attrs'])) {
            $wrapper_attrs .= $attrs['data_attrs'];
        }
        
        echo '<div' . $wrapper_attrs . '>';
        
        switch ($type) {
            case 'title':
                self::render_title($data);
                break;
            case 'text':
                self::render_text($data);
                break;
            case 'image':
                self::render_image($data);
                break;
            case 'gallery':
                self::render_gallery($data);
                break;
            case 'shortcode':
                self::render_shortcode($data);
                break;
            case 'cta':
                self::render_cta($data);
                break;
            case 'video':
                self::render_video($data);
                break;
            case 'separator':
                self::render_separator($data);
                break;
            case 'features':
                self::render_features($data);
                break;
            case 'counters':
                self::render_counters($data);
                break;
            case 'faq':
                self::render_faq($data);
                break;
            case 'tabs':
                self::render_tabs($data);
                break;
        }
        
        echo '</div>';
    }
    
    /**
     * Renderizza Titolo
     */
    private static function render_title($data) {
        $text = isset($data['text']) ? $data['text'] : '';
        $level = isset($data['level']) ? $data['level'] : 'h2';
        $align = isset($data['align']) ? $data['align'] : 'left';
        $font_size = isset($data['font_size']) ? $data['font_size'] : '';
        $text_color = isset($data['text_color']) ? $data['text_color'] : '';
        $font_weight = isset($data['font_weight']) ? $data['font_weight'] : '';
        
        // Responsive
        $font_size_mobile = isset($data['font_size_mobile']) ? $data['font_size_mobile'] : '';
        $font_size_tablet = isset($data['font_size_tablet']) ? $data['font_size_tablet'] : '';
        $font_size_desktop = isset($data['font_size_desktop']) ? $data['font_size_desktop'] : '';
        $align_mobile = isset($data['align_mobile']) ? $data['align_mobile'] : '';
        $align_tablet = isset($data['align_tablet']) ? $data['align_tablet'] : '';
        $align_desktop = isset($data['align_desktop']) ? $data['align_desktop'] : '';
        
        if (empty($text)) {
            return;
        }
        
        $tag = in_array($level, ['h1', 'h2', 'h3', 'h4', 'h5', 'h6']) ? $level : 'h2';
        
        // Usa allineamento responsive se disponibile, altrimenti quello generale
        $final_align = $align;
        if ($align_mobile || $align_tablet || $align_desktop) {
            // Se ci sono allineamenti responsive, usa quello di default come base
            $final_align = $align;
        }
        
        $title_style = 'text-align: ' . esc_attr($final_align) . ';';
        if ($font_size) {
            $title_style .= 'font-size: ' . esc_attr($font_size) . 'px;';
        }
        if ($text_color) {
            $title_style .= 'color: ' . esc_attr($text_color) . ';';
        }
        if ($font_weight) {
            $title_style .= 'font-weight: ' . esc_attr($font_weight) . ';';
        }
        
        // Data attributes per responsive
        $title_data = '';
        // Salva il font-size base se presente
        if ($font_size) {
            $title_data .= ' data-base-font-size="' . esc_attr($font_size) . 'px"';
        }
        if ($font_size_mobile) {
            $title_data .= ' data-font-size-mobile="' . esc_attr($font_size_mobile) . '"';
        }
        if ($font_size_tablet) {
            $title_data .= ' data-font-size-tablet="' . esc_attr($font_size_tablet) . '"';
        }
        if ($font_size_desktop) {
            $title_data .= ' data-font-size-desktop="' . esc_attr($font_size_desktop) . '"';
        }
        if ($align_mobile && $align_mobile !== '') {
            $title_data .= ' data-align-mobile="' . esc_attr($align_mobile) . '"';
        }
        if ($align_tablet && $align_tablet !== '') {
            $title_data .= ' data-align-tablet="' . esc_attr($align_tablet) . '"';
        }
        if ($align_desktop && $align_desktop !== '') {
            $title_data .= ' data-align-desktop="' . esc_attr($align_desktop) . '"';
        }
        
        ?>
        <div class="fp-lp-title-section">
            <<?php echo $tag; ?> class="fp-lp-title" style="<?php echo $title_style; ?>"<?php echo $title_data; ?>><?php echo esc_html($text); ?></<?php echo $tag; ?>>
        </div>
        <?php
    }
    
    /**
     * Renderizza Testo
     */
    private static function render_text($data) {
        $content = isset($data['content']) ? $data['content'] : '';
        $align = isset($data['align']) ? $data['align'] : 'left';
        
        // Responsive alignment
        $align_mobile = isset($data['align_mobile']) ? $data['align_mobile'] : '';
        $align_tablet = isset($data['align_tablet']) ? $data['align_tablet'] : '';
        $align_desktop = isset($data['align_desktop']) ? $data['align_desktop'] : '';
        
        if (empty($content)) {
            return;
        }
        
        $text_data = '';
        if ($align_mobile) {
            $text_data .= ' data-align-mobile="' . esc_attr($align_mobile) . '"';
        }
        if ($align_tablet) {
            $text_data .= ' data-align-tablet="' . esc_attr($align_tablet) . '"';
        }
        if ($align_desktop) {
            $text_data .= ' data-align-desktop="' . esc_attr($align_desktop) . '"';
        }
        
        ?>
        <div class="fp-lp-text-section" style="text-align: <?php echo esc_attr($align); ?>;"<?php echo $text_data; ?>>
            <div class="fp-lp-text-content">
                <?php echo wp_kses_post(wpautop($content)); ?>
            </div>
        </div>
        <?php
    }
    
    /**
     * Renderizza Immagine
     */
    private static function render_image($data) {
        $image_id = isset($data['image_id']) ? $data['image_id'] : '';
        $alt = isset($data['alt']) ? $data['alt'] : '';
        $align = isset($data['align']) ? $data['align'] : 'center';
        $link = isset($data['link']) ? $data['link'] : '';
        
        if (empty($image_id)) {
            return;
        }
        
        $image_url = wp_get_attachment_image_url($image_id, 'large');
        if (!$image_url) {
            return;
        }
        
        // Se non c'è alt text, prova a prenderlo dall'attachment
        if (empty($alt)) {
            $alt = get_post_meta($image_id, '_wp_attachment_image_alt', true);
        }
        
        // Personalizzazioni avanzate
        $max_width = isset($data['max_width']) ? $data['max_width'] : '';
        $border_radius = isset($data['border_radius']) ? $data['border_radius'] : '';
        $box_shadow = isset($data['box_shadow']) ? $data['box_shadow'] : '';
        
        // Responsive
        $max_width_mobile = isset($data['max_width_mobile']) ? $data['max_width_mobile'] : '';
        $max_width_tablet = isset($data['max_width_tablet']) ? $data['max_width_tablet'] : '';
        $max_width_desktop = isset($data['max_width_desktop']) ? $data['max_width_desktop'] : '';
        $align_mobile = isset($data['align_mobile']) ? $data['align_mobile'] : '';
        $align_tablet = isset($data['align_tablet']) ? $data['align_tablet'] : '';
        $align_desktop = isset($data['align_desktop']) ? $data['align_desktop'] : '';
        
        $image_style = '';
        if ($max_width) {
            $image_style .= 'max-width: ' . esc_attr($max_width) . ';';
        }
        if ($border_radius) {
            $image_style .= 'border-radius: ' . esc_attr($border_radius) . 'px;';
        }
        if ($box_shadow) {
            $image_style .= 'box-shadow: ' . esc_attr($box_shadow) . ';';
        }
        
        $image_attrs = ['alt' => $alt, 'class' => 'fp-lp-image'];
        if ($image_style) {
            $image_attrs['style'] = $image_style;
        }
        
        // Data attributes per responsive
        if ($max_width_mobile) {
            $image_attrs['data-max-width-mobile'] = esc_attr($max_width_mobile);
        }
        if ($max_width_tablet) {
            $image_attrs['data-max-width-tablet'] = esc_attr($max_width_tablet);
        }
        if ($max_width_desktop) {
            $image_attrs['data-max-width-desktop'] = esc_attr($max_width_desktop);
        }
        
        $image_html = wp_get_attachment_image($image_id, 'large', false, $image_attrs);
        
        $section_data = '';
        if ($align_mobile) {
            $section_data .= ' data-align-mobile="' . esc_attr($align_mobile) . '"';
        }
        if ($align_tablet) {
            $section_data .= ' data-align-tablet="' . esc_attr($align_tablet) . '"';
        }
        if ($align_desktop) {
            $section_data .= ' data-align-desktop="' . esc_attr($align_desktop) . '"';
        }
        
        ?>
        <div class="fp-lp-image-section" style="text-align: <?php echo esc_attr($align); ?>;"<?php echo $section_data; ?>>
            <?php if ($link): ?>
                <a href="<?php echo esc_url($link); ?>">
                    <?php echo $image_html; ?>
                </a>
            <?php else: ?>
                <?php echo $image_html; ?>
            <?php endif; ?>
        </div>
        <?php
    }
    
    /**
     * Renderizza Galleria
     */
    private static function render_gallery($data) {
        $gallery_ids = isset($data['gallery_ids']) ? $data['gallery_ids'] : '';
        $columns_value = isset($data['columns']) ? $data['columns'] : 3;
        $columns = absint($columns_value);
        
        if (empty($gallery_ids)) {
            return;
        }
        
        $ids_array = array_filter(array_map('absint', explode(',', $gallery_ids)));
        if (empty($ids_array)) {
            return;
        }
        
        $columns = max(1, min(4, $columns)); // Tra 1 e 4 colonne
        
        // Personalizzazioni avanzate
        $image_border_radius = isset($data['image_border_radius']) ? $data['image_border_radius'] : '';
        $gap = isset($data['gap']) ? $data['gap'] : '';
        
        $gallery_style = '';
        if ($gap !== '') {
            $gallery_style .= 'gap: ' . esc_attr($gap) . 'px;';
        }
        
        $image_style = '';
        if ($image_border_radius !== '') {
            $image_style .= 'border-radius: ' . esc_attr($image_border_radius) . 'px;';
        }
        
        ?>
        <div class="fp-lp-gallery-section">
            <div class="fp-lp-gallery fp-lp-gallery-columns-<?php echo esc_attr($columns); ?>"<?php echo $gallery_style ? ' style="' . $gallery_style . '"' : ''; ?>>
                <?php foreach ($ids_array as $img_id): ?>
                    <?php 
                    $img_url = wp_get_attachment_image_url($img_id, 'medium_large');
                    $img_alt = get_post_meta($img_id, '_wp_attachment_image_alt', true);
                    if ($img_url):
                    ?>
                        <div class="fp-lp-gallery-item">
                            <img src="<?php echo esc_url($img_url); ?>" alt="<?php echo esc_attr($img_alt); ?>" class="fp-lp-gallery-image"<?php echo $image_style ? ' style="' . $image_style . '"' : ''; ?>>
                        </div>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>
        </div>
        <?php
    }
    
    /**
     * Renderizza Shortcode
     */
    private static function render_shortcode($data) {
        $shortcode = isset($data['shortcode']) ? $data['shortcode'] : '';
        
        if (empty($shortcode)) {
            return;
        }
        
        ?>
        <div class="fp-lp-shortcode-section">
            <?php echo do_shortcode($shortcode); ?>
        </div>
        <?php
    }
    
    /**
     * Renderizza CTA
     */
    private static function render_cta($data) {
        $button_text = isset($data['button_text']) ? $data['button_text'] : '';
        $button_url = isset($data['button_url']) ? $data['button_url'] : '#';
        $style = isset($data['style']) ? $data['style'] : 'primary';
        $align = isset($data['align']) ? $data['align'] : 'center';
        $button_bg_color = isset($data['button_bg_color']) ? $data['button_bg_color'] : '';
        $button_text_color = isset($data['button_text_color']) ? $data['button_text_color'] : '';
        $button_border_radius = isset($data['button_border_radius']) ? $data['button_border_radius'] : '';
        
        // Responsive alignment
        $align_mobile = isset($data['align_mobile']) ? $data['align_mobile'] : '';
        $align_tablet = isset($data['align_tablet']) ? $data['align_tablet'] : '';
        $align_desktop = isset($data['align_desktop']) ? $data['align_desktop'] : '';
        
        if (empty($button_text)) {
            return;
        }
        
        $button_class = 'fp-lp-button';
        switch ($style) {
            case 'primary':
                $button_class .= ' fp-lp-button-primary';
                break;
            case 'secondary':
                $button_class .= ' fp-lp-button-secondary';
                break;
            case 'outline':
                $button_class .= ' fp-lp-button-outline';
                break;
        }
        
        // Forza stili base per sovrascrivere il tema - usa flexbox per centrare il testo
        $button_style = 'display: inline-flex !important; align-items: center !important; justify-content: center !important; height: auto !important; max-height: 60px !important; min-height: 0 !important; padding: 16px 36px !important; line-height: 1.2 !important; box-sizing: border-box !important;';
        if ($button_bg_color) {
            $button_style .= 'background-color: ' . esc_attr($button_bg_color) . ';';
        }
        if ($button_text_color) {
            $button_style .= 'color: ' . esc_attr($button_text_color) . ';';
        }
        if ($button_border_radius !== '') {
            $button_style .= 'border-radius: ' . esc_attr($button_border_radius) . 'px;';
        }
        
        $section_data = '';
        if ($align_mobile) {
            $section_data .= ' data-align-mobile="' . esc_attr($align_mobile) . '"';
        }
        if ($align_tablet) {
            $section_data .= ' data-align-tablet="' . esc_attr($align_tablet) . '"';
        }
        if ($align_desktop) {
            $section_data .= ' data-align-desktop="' . esc_attr($align_desktop) . '"';
        }
        
        ?>
        <div class="fp-lp-cta-section" style="text-align: <?php echo esc_attr($align); ?>;"<?php echo $section_data; ?>>
            <span class="<?php echo esc_attr($button_class); ?>" data-href="<?php echo esc_url($button_url); ?>" onclick="window.location.href=this.dataset.href" role="link" tabindex="0" onkeypress="if(event.key==='Enter')window.location.href=this.dataset.href"<?php echo $button_style ? ' style="' . $button_style . '"' : ''; ?>><?php echo esc_html($button_text); ?></span>
        </div>
        <?php
    }
    
    /**
     * Renderizza Video
     */
    private static function render_video($data) {
        $video_url = isset($data['video_url']) ? $data['video_url'] : '';
        $align = isset($data['align']) ? $data['align'] : 'center';
        
        if (empty($video_url)) {
            return;
        }
        
        // Genera direttamente l'iframe per evitare interferenze del tema con lazy loading
        $embed_url = self::get_video_embed_url($video_url);
        
        ?>
        <div class="fp-lp-video-section" style="text-align: <?php echo esc_attr($align); ?>;">
            <?php if ($embed_url): ?>
                <div class="fp-lp-video-wrapper">
                    <iframe 
                        src="<?php echo esc_url($embed_url); ?>" 
                        width="100%" 
                        height="450" 
                        frameborder="0" 
                        allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" 
                        allowfullscreen
                        style="aspect-ratio: 16/9; max-width: 100%;">
                    </iframe>
                </div>
            <?php else: ?>
                <p class="fp-lp-error"><?php _e('URL video non valido', 'fp-landing-page'); ?></p>
            <?php endif; ?>
        </div>
        <?php
    }
    
    /**
     * Converte URL video in URL embed
     */
    private static function get_video_embed_url($url) {
        // YouTube
        if (preg_match('/(?:youtube\.com\/(?:watch\?v=|embed\/|v\/)|youtu\.be\/)([a-zA-Z0-9_-]{11})/', $url, $matches)) {
            return 'https://www.youtube.com/embed/' . $matches[1];
        }
        
        // Vimeo
        if (preg_match('/vimeo\.com\/(?:video\/)?(\d+)/', $url, $matches)) {
            return 'https://player.vimeo.com/video/' . $matches[1];
        }
        
        // URL già embed
        if (strpos($url, 'youtube.com/embed/') !== false || strpos($url, 'player.vimeo.com/') !== false) {
            return $url;
        }
        
        return null;
    }
    
    /**
     * Renderizza Separatore
     */
    private static function render_separator($data) {
        $style = isset($data['style']) ? $data['style'] : 'solid';
        $height_value = isset($data['height']) ? $data['height'] : 40;
        $height = absint($height_value);
        $color = isset($data['color']) ? $data['color'] : '';
        $align = isset($data['align']) ? $data['align'] : 'left';
        
        if ($style === 'space') {
            ?>
            <div class="fp-lp-separator-section">
                <div class="fp-lp-separator fp-lp-separator-space" style="height: <?php echo esc_attr($height); ?>px;"></div>
            </div>
            <?php
        } else {
            $border_style = in_array($style, ['solid', 'dashed', 'dotted']) ? $style : 'solid';
            $separator_style = 'border-top-style: ' . esc_attr($border_style) . '; margin: ' . esc_attr($height / 2) . 'px 0;';
            if ($color) {
                $separator_style .= ' border-top-color: ' . esc_attr($color) . ';';
            }
            
            // Wrapper per allineamento
            $wrapper_style = 'text-align: ' . esc_attr($align) . ';';
            ?>
            <div class="fp-lp-separator-section" style="<?php echo $wrapper_style; ?>">
                <div class="fp-lp-separator fp-lp-separator-<?php echo esc_attr($border_style); ?>" style="<?php echo $separator_style; ?>"></div>
            </div>
            <?php
        }
    }
    
    /**
     * Renderizza Features
     */
    private static function render_features($data) {
        $features = isset($data['features']) ? $data['features'] : [];
        $columns_value = isset($data['columns']) ? $data['columns'] : 3;
        $columns = absint($columns_value);
        
        if (empty($features) || !is_array($features)) {
            return;
        }
        
        $columns = max(1, min(4, $columns)); // Tra 1 e 4 colonne
        $columns_class = 'fp-lp-features-columns-' . $columns;
        
        // Colori personalizzabili per Features
        $icon_color = isset($data['icon_color']) ? $data['icon_color'] : '';
        $title_color = isset($data['title_color']) ? $data['title_color'] : '';
        $text_color = isset($data['text_color']) ? $data['text_color'] : '';
        
        $section_style = '';
        if ($icon_color || $title_color || $text_color) {
            if ($icon_color) {
                $section_style .= '--fp-lp-feature-icon-color: ' . esc_attr($icon_color) . ';';
            }
            if ($title_color) {
                $section_style .= '--fp-lp-feature-title-color: ' . esc_attr($title_color) . ';';
            }
            if ($text_color) {
                $section_style .= '--fp-lp-feature-text-color: ' . esc_attr($text_color) . ';';
            }
        }
        
        ?>
        <div class="fp-lp-features-section"<?php echo $section_style ? ' style="' . $section_style . '"' : ''; ?>>
            <div class="fp-lp-features-grid <?php echo esc_attr($columns_class); ?>">
                <?php foreach ($features as $feature): 
                    $icon = isset($feature['icon']) ? $feature['icon'] : '';
                    $title = isset($feature['title']) ? $feature['title'] : '';
                    $text = isset($feature['text']) ? $feature['text'] : '';
                    $icon_color_item = isset($feature['icon_color']) ? $feature['icon_color'] : '';
                    
                    if (empty($title) && empty($text)) {
                        continue;
                    }
                    
                    // Stile icona individuale (prevalenza sul globale)
                    $icon_style = '';
                    if ($icon_color_item) {
                        $icon_style = ' style="color: ' . esc_attr($icon_color_item) . ';"';
                    }
                ?>
                    <div class="fp-lp-feature-box">
                        <?php if ($icon): ?>
                            <div class="fp-lp-feature-icon"<?php echo $icon_style; ?>>
                                <i class="<?php echo esc_attr($icon); ?>"></i>
                            </div>
                        <?php endif; ?>
                        <?php if ($title): ?>
                            <h3 class="fp-lp-feature-title"><?php echo esc_html($title); ?></h3>
                        <?php endif; ?>
                        <?php if ($text): ?>
                            <div class="fp-lp-feature-text">
                                <?php echo wp_kses_post(wpautop($text)); ?>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php
    }
    
    /**
     * Renderizza Contatori
     */
    private static function render_counters($data) {
        $counters = isset($data['counters']) ? $data['counters'] : [];
        $columns_value = isset($data['columns']) ? $data['columns'] : 4;
        $columns = absint($columns_value);
        
        if (empty($counters) || !is_array($counters)) {
            return;
        }
        
        $columns = max(1, min(4, $columns)); // Tra 1 e 4 colonne
        $columns_class = 'fp-lp-counters-columns-' . $columns;
        
        // Colori personalizzabili per Counters
        $icon_color = isset($data['icon_color']) ? $data['icon_color'] : '';
        $number_color = isset($data['number_color']) ? $data['number_color'] : '';
        $label_color = isset($data['label_color']) ? $data['label_color'] : '';
        
        $section_style = '';
        if ($icon_color || $number_color || $label_color) {
            if ($icon_color) {
                $section_style .= '--fp-lp-counter-icon-color: ' . esc_attr($icon_color) . ';';
            }
            if ($number_color) {
                $section_style .= '--fp-lp-counter-number-color: ' . esc_attr($number_color) . ';';
            }
            if ($label_color) {
                $section_style .= '--fp-lp-counter-label-color: ' . esc_attr($label_color) . ';';
            }
        }
        
        ?>
        <div class="fp-lp-counters-section"<?php echo $section_style ? ' style="' . $section_style . '"' : ''; ?>>
            <div class="fp-lp-counters-grid <?php echo esc_attr($columns_class); ?>">
                <?php foreach ($counters as $counter): 
                    $number = isset($counter['number']) ? $counter['number'] : '';
                    $label = isset($counter['label']) ? $counter['label'] : '';
                    $prefix = isset($counter['prefix']) ? $counter['prefix'] : '';
                    $suffix = isset($counter['suffix']) ? $counter['suffix'] : '';
                    $icon = isset($counter['icon']) ? $counter['icon'] : '';
                    
                    if (empty($number) && empty($label)) {
                        continue;
                    }
                ?>
                    <div class="fp-lp-counter-box">
                        <?php if ($icon): 
                            $icon_color_item = isset($counter['icon_color']) ? $counter['icon_color'] : '';
                            $icon_style = '';
                            if ($icon_color_item) {
                                $icon_style = ' style="color: ' . esc_attr($icon_color_item) . ';"';
                            }
                        ?>
                            <div class="fp-lp-counter-icon"<?php echo $icon_style; ?>>
                                <i class="<?php echo esc_attr($icon); ?>"></i>
                            </div>
                        <?php endif; ?>
                        <div class="fp-lp-counter-number">
                            <?php if ($prefix): ?><span class="fp-lp-counter-prefix"><?php echo esc_html($prefix); ?></span><?php endif; ?>
                            <span class="fp-lp-counter-value"><?php echo esc_html($number); ?></span>
                            <?php if ($suffix): ?><span class="fp-lp-counter-suffix"><?php echo esc_html($suffix); ?></span><?php endif; ?>
                        </div>
                        <?php if ($label): ?>
                            <div class="fp-lp-counter-label"><?php echo esc_html($label); ?></div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php
    }
    
    /**
     * Renderizza FAQ
     */
    private static function render_faq($data) {
        $faqs = isset($data['faqs']) ? $data['faqs'] : [];
        
        if (empty($faqs) || !is_array($faqs)) {
            return;
        }
        
        // Colori personalizzabili per FAQ
        $faq_icon_color = isset($data['icon_color']) ? $data['icon_color'] : '';
        $faq_question_color = isset($data['question_color']) ? $data['question_color'] : '';
        $faq_answer_color = isset($data['answer_color']) ? $data['answer_color'] : '';
        $faq_bg_color = isset($data['bg_color']) ? $data['bg_color'] : '';
        
        $faq_style = '';
        if ($faq_icon_color || $faq_question_color || $faq_answer_color || $faq_bg_color) {
            $faq_style = ' style="';
            if ($faq_icon_color) {
                $faq_style .= '--fp-lp-faq-icon-color: ' . esc_attr($faq_icon_color) . ';';
            }
            if ($faq_question_color) {
                $faq_style .= '--fp-lp-faq-question-color: ' . esc_attr($faq_question_color) . ';';
            }
            if ($faq_answer_color) {
                $faq_style .= '--fp-lp-faq-answer-color: ' . esc_attr($faq_answer_color) . ';';
            }
            if ($faq_bg_color) {
                $faq_style .= '--fp-lp-faq-bg-color: ' . esc_attr($faq_bg_color) . ';';
            }
            $faq_style .= '"';
        }
        
        ?>
        <div class="fp-lp-faq-section"<?php echo $faq_style; ?>>
            <div class="fp-lp-faq-list">
                <?php foreach ($faqs as $i => $faq): 
                    $question = isset($faq['question']) ? $faq['question'] : '';
                    $answer = isset($faq['answer']) ? $faq['answer'] : '';
                    
                    if (empty($question) && empty($answer)) {
                        continue;
                    }
                    
                    $faq_id = 'fp-lp-faq-' . uniqid();
                ?>
                    <div class="fp-lp-faq-item">
                        <div class="fp-lp-faq-question" data-faq-toggle="<?php echo esc_attr($faq_id); ?>">
                            <?php echo esc_html($question); ?>
                            <span class="fp-lp-faq-icon">+</span>
                        </div>
                        <div class="fp-lp-faq-answer" id="<?php echo esc_attr($faq_id); ?>">
                            <div class="fp-lp-faq-answer-content">
                                <?php echo wp_kses_post(wpautop($answer)); ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php
    }
    
    /**
     * Renderizza Tabs
     */
    private static function render_tabs($data) {
        $tabs = isset($data['tabs']) ? $data['tabs'] : [];
        
        if (empty($tabs) || !is_array($tabs)) {
            return;
        }
        
        // Colori personalizzabili per Tab
        $tab_text_color = isset($data['text_color']) ? $data['text_color'] : '';
        $tab_active_bg_color = isset($data['active_bg_color']) ? $data['active_bg_color'] : '';
        $tab_active_text_color = isset($data['active_text_color']) ? $data['active_text_color'] : '';
        $tab_border_color = isset($data['border_color']) ? $data['border_color'] : '';
        $tab_content_color = isset($data['content_color']) ? $data['content_color'] : '';
        
        $tabs_style = '';
        if ($tab_text_color || $tab_active_bg_color || $tab_active_text_color || $tab_border_color || $tab_content_color) {
            $tabs_style = ' style="';
            if ($tab_text_color) {
                $tabs_style .= '--fp-lp-tab-text-color: ' . esc_attr($tab_text_color) . ';';
            }
            if ($tab_active_bg_color) {
                $tabs_style .= '--fp-lp-tab-active-bg-color: ' . esc_attr($tab_active_bg_color) . ';';
            }
            if ($tab_active_text_color) {
                $tabs_style .= '--fp-lp-tab-active-text-color: ' . esc_attr($tab_active_text_color) . ';';
            }
            if ($tab_border_color) {
                $tabs_style .= '--fp-lp-tab-border-color: ' . esc_attr($tab_border_color) . ';';
            }
            if ($tab_content_color) {
                $tabs_style .= '--fp-lp-tab-content-color: ' . esc_attr($tab_content_color) . ';';
            }
            $tabs_style .= '"';
        }
        
        $tabs_id = 'fp-lp-tabs-' . uniqid();
        $first_active = true;
        ?>
        <div class="fp-lp-tabs-section"<?php echo $tabs_style; ?>>
            <div class="fp-lp-tabs-wrapper" id="<?php echo esc_attr($tabs_id); ?>">
                <div class="fp-lp-tabs-nav">
                    <?php foreach ($tabs as $i => $tab): 
                        $title = isset($tab['title']) ? $tab['title'] : '';
                        if (empty($title)) {
                            continue;
                        }
                        $tab_id = $tabs_id . '-tab-' . $i;
                        $active = $first_active ? 'active' : '';
                        $first_active = false;
                    ?>
                        <button type="button" class="fp-lp-tab-button <?php echo esc_attr($active); ?>" data-tab="<?php echo esc_attr($tab_id); ?>">
                            <?php echo esc_html($title); ?>
                        </button>
                    <?php endforeach; ?>
                </div>
                <div class="fp-lp-tabs-content">
                    <?php 
                    $first_active = true;
                    foreach ($tabs as $i => $tab): 
                        $title = isset($tab['title']) ? $tab['title'] : '';
                        $content = isset($tab['content']) ? $tab['content'] : '';
                        if (empty($title)) {
                            continue;
                        }
                        $tab_id = $tabs_id . '-tab-' . $i;
                        $active = $first_active ? 'active' : '';
                        $first_active = false;
                    ?>
                        <div class="fp-lp-tab-panel <?php echo esc_attr($active); ?>" id="<?php echo esc_attr($tab_id); ?>">
                            <div class="fp-lp-tab-content">
                                <?php echo wp_kses_post(wpautop($content)); ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        <?php
    }
}
