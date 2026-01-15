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
}
