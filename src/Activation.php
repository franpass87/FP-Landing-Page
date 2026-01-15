<?php
/**
 * Gestione attivazione plugin
 *
 * @package FPLandingPage
 */

namespace FPLandingPage;

defined('ABSPATH') || exit;

/**
 * Classe per gestire l'attivazione del plugin
 */
class Activation {
    
    /**
     * Esegue operazioni durante l'attivazione
     */
    public static function activate() {
        // Verifica requisiti minimi
        self::check_requirements();
        
        // Imposta opzioni predefinite
        self::set_default_options();
        
        // Flush rewrite rules
        flush_rewrite_rules();
        
        // Log attivazione
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log('FP Landing Page: Plugin attivato con successo');
        }
        
        do_action('fp_landing_page_activated');
    }
    
    /**
     * Verifica i requisiti minimi
     */
    private static function check_requirements() {
        global $wp_version;
        
        // Verifica versione WordPress
        if (version_compare($wp_version, '6.0', '<')) {
            deactivate_plugins(plugin_basename(FP_LANDING_PAGE_FILE));
            wp_die(
                __('FP Landing Page richiede WordPress 6.0 o superiore.', 'fp-landing-page'),
                __('Requisiti non soddisfatti', 'fp-landing-page'),
                ['back_link' => true]
            );
        }
        
        // Verifica versione PHP
        if (version_compare(PHP_VERSION, '7.4', '<')) {
            deactivate_plugins(plugin_basename(FP_LANDING_PAGE_FILE));
            wp_die(
                __('FP Landing Page richiede PHP 7.4 o superiore.', 'fp-landing-page'),
                __('Requisiti non soddisfatti', 'fp-landing-page'),
                ['back_link' => true]
            );
        }
    }
    
    /**
     * Imposta opzioni predefinite
     */
    private static function set_default_options() {
        $defaults = [
            'fp_landing_page_version' => FP_LANDING_PAGE_VERSION,
        ];
        
        foreach ($defaults as $option => $value) {
            if (get_option($option) === false) {
                add_option($option, $value);
            }
        }
    }
}
