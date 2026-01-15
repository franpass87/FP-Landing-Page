<?php
/**
 * Gestione disattivazione plugin
 *
 * @package FPLandingPage
 */

namespace FPLandingPage;

defined('ABSPATH') || exit;

/**
 * Classe per gestire la disattivazione del plugin
 */
class Deactivation {
    
    /**
     * Esegue operazioni durante la disattivazione
     */
    public static function deactivate() {
        // Cancella cron jobs
        self::clear_scheduled_events();
        
        // Pulisci cache
        self::clear_transients();
        
        // Flush rewrite rules
        flush_rewrite_rules();
        
        // Log disattivazione
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log('FP Landing Page: Plugin disattivato');
        }
        
        do_action('fp_landing_page_deactivated');
    }
    
    /**
     * Cancella eventi schedulati
     */
    private static function clear_scheduled_events() {
        // Qui verranno aggiunti gli hook cron se necessari
    }
    
    /**
     * Pulisci transients
     */
    private static function clear_transients() {
        global $wpdb;
        
        $wpdb->query(
            $wpdb->prepare(
                "DELETE FROM {$wpdb->options} WHERE option_name LIKE %s",
                '_transient_fp_landing_page_%'
            )
        );
        
        $wpdb->query(
            $wpdb->prepare(
                "DELETE FROM {$wpdb->options} WHERE option_name LIKE %s",
                '_transient_timeout_fp_landing_page_%'
            )
        );
    }
}
