<?php
/**
 * Template per singola Landing Page
 *
 * @package FPLandingPage
 */

defined('ABSPATH') || exit;

get_header();

while (have_posts()) :
    the_post();
    ?>
    <?php
    // Ottieni meta per stili
    $bg_color = get_post_meta(get_the_ID(), '_fp_landing_bg_color', true);
    $text_color = get_post_meta(get_the_ID(), '_fp_landing_text_color', true);
    $footer_text = get_post_meta(get_the_ID(), '_fp_landing_footer_text', true);
    ?>
    <div class="fp-landing-page-container" 
         id="fp-landing-page-<?php the_ID(); ?>"
         style="<?php echo $bg_color ? 'background-color: ' . esc_attr($bg_color) . ';' : ''; ?> <?php echo $text_color ? 'color: ' . esc_attr($text_color) . ';' : ''; ?>">
        <?php
        // Renderizza direttamente le sezioni (evita loop con shortcode)
        \FPLandingPage\Shortcodes\Landing::render_sections(get_the_ID());
        
        // Footer personalizzato se presente
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
endwhile;

get_footer();
