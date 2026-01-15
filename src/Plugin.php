<?php
/**
 * Classe principale plugin FP Landing Page
 *
 * @package FPLandingPage
 */

namespace FPLandingPage;

defined('ABSPATH') || exit;

/**
 * Classe singleton per gestire l'inizializzazione del plugin
 */
class Plugin {
    
    /**
     * Istanza singleton
     *
     * @var Plugin|null
     */
    private static $instance = null;
    
    /**
     * Ottiene l'istanza singleton
     *
     * @return Plugin
     */
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Costruttore privato (singleton)
     */
    private function __construct() {
        $this->init_hooks();
    }
    
    /**
     * Previene clonazione
     */
    private function __clone() {
        _doing_it_wrong(
            __FUNCTION__,
            __('Clonazione non permessa.', 'fp-landing-page'),
            FP_LANDING_PAGE_VERSION
        );
    }
    
    /**
     * Previene unserialize
     */
    public function __wakeup() {
        _doing_it_wrong(
            __FUNCTION__,
            __('Unserialize non permesso.', 'fp-landing-page'),
            FP_LANDING_PAGE_VERSION
        );
    }
    
    /**
     * Inizializza gli hook del plugin
     */
    private function init_hooks() {
        // Hook per inizializzazione
        add_action('init', [$this, 'init'], 0);
        
        // Flush rewrite rules dopo aggiornamento plugin
        add_action('upgrader_process_complete', [$this, 'maybe_flush_rewrite_rules'], 10, 2);
        
        // Verifica versione e flush se necessario
        add_action('admin_init', [$this, 'check_version_and_flush_rules']);
    }
    
    /**
     * Inizializza il plugin
     */
    public function init() {
        // Inizializzazione componenti
        $this->init_components();
    }
    
    /**
     * Inizializza i componenti del plugin
     */
    private function init_components() {
        // Custom Post Type
        if (class_exists('\FPLandingPage\PostTypes\LandingPage')) {
            \FPLandingPage\PostTypes\LandingPage::register();
        }
        
        // Admin components
        if (is_admin()) {
            if (class_exists('\FPLandingPage\Admin\MetaBoxes')) {
                new \FPLandingPage\Admin\MetaBoxes();
            }
            
            if (class_exists('\FPLandingPage\Admin\LandingPageBuilder')) {
                \FPLandingPage\Admin\LandingPageBuilder::get_instance();
            }
            
            // Integrazione con FP SEO Manager
            if (class_exists('\FPLandingPage\Admin\SeoIntegration')) {
                new \FPLandingPage\Admin\SeoIntegration();
            }
            
            // Import Manager per importazione landing page da JSON
            if (class_exists('\FPLandingPage\Admin\ImportManager')) {
                new \FPLandingPage\Admin\ImportManager();
            }
            
            // Pagina istruzioni ChatGPT
            if (class_exists('\FPLandingPage\Admin\InstructionsPage')) {
                new \FPLandingPage\Admin\InstructionsPage();
            }
        }
        
        // REST API
        if (class_exists('\FPLandingPage\REST\Controller')) {
            new \FPLandingPage\REST\Controller();
        }
        
        // Shortcodes
        if (class_exists('\FPLandingPage\Shortcodes\Landing')) {
            \FPLandingPage\Shortcodes\Landing::register();
        }
        
        // Template override
        if (class_exists('\FPLandingPage\Template')) {
            new \FPLandingPage\Template();
        }
    }
    
    /**
     * Flush rewrite rules dopo aggiornamento plugin
     */
    public function maybe_flush_rewrite_rules($upgrader_object, $options) {
        if ($options['action'] === 'update' && $options['type'] === 'plugin') {
            $plugin_file = plugin_basename(FP_LANDING_PAGE_FILE);
            
            // Verifica se questo plugin è stato aggiornato
            if (isset($options['plugins']) && in_array($plugin_file, $options['plugins'])) {
                // Flush rewrite rules dopo aggiornamento
                flush_rewrite_rules(false);
                
                // Aggiorna versione salvata
                update_option('fp_landing_page_version', FP_LANDING_PAGE_VERSION);
            }
        }
    }
    
    /**
     * Verifica versione e flush rewrite rules se necessario
     */
    public function check_version_and_flush_rules() {
        $saved_version = get_option('fp_landing_page_version', '0');
        
        // Se la versione è cambiata, flush rewrite rules
        if (version_compare($saved_version, FP_LANDING_PAGE_VERSION, '<')) {
            flush_rewrite_rules(false);
            update_option('fp_landing_page_version', FP_LANDING_PAGE_VERSION);
        }
    }
}
