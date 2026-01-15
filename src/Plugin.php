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
        $this->init();
    }
    
    /**
     * Previene clonazione
     */
    private function __clone() {}
    
    /**
     * Previene unserialize
     */
    public function __wakeup() {}
    
    /**
     * Inizializza il plugin
     */
    private function init() {
        // Verifica dipendenze Composer (solo se necessario e non già fatto di recente)
        $this->check_composer_dependencies();
        
        // Hook per aggiornamenti plugin (Git Updater, ecc.)
        add_action('upgrader_process_complete', [$this, 'handle_plugin_update'], 10, 2);
        
        // Custom Post Type - registra subito l'hook
        add_action('init', [PostTypes\LandingPage::class, 'register_post_type']);
        add_action('init', [PostTypes\LandingPage::class, 'register_taxonomies']);
        
        // Admin components
        if (is_admin()) {
            new Admin\MetaBoxes();
            Admin\LandingPageBuilder::get_instance();
            new Admin\SeoIntegration();
            new Admin\ImportManager();
            new Admin\InstructionsPage();
        }
        
        // Shortcodes
        add_action('init', [Shortcodes\Landing::class, 'register']);
        
        // Template override
        new Template();
    }
    
    /**
     * Gestisce l'aggiornamento del plugin
     */
    public function handle_plugin_update($upgrader_object, $options) {
        if ($options['action'] === 'update' && $options['type'] === 'plugin') {
            $plugin_file = plugin_basename(FP_LANDING_PAGE_FILE);
            
            // Verifica se questo plugin è stato aggiornato
            if (isset($options['plugins']) && in_array($plugin_file, $options['plugins'])) {
                // Esegui composer install dopo l'aggiornamento
                Activation::ensure_composer_dependencies();
                
                // Cancella il transient per forzare un nuovo controllo
                delete_transient('fp_landing_page_composer_check');
            }
        }
    }
    
    /**
     * Verifica e installa dipendenze Composer se necessario
     */
    private function check_composer_dependencies() {
        // Controlla solo una volta ogni ora per evitare overhead
        $transient_key = 'fp_landing_page_composer_check';
        $last_check = get_transient($transient_key);
        
        if ($last_check !== false) {
            return; // Già controllato di recente
        }
        
        $plugin_dir = dirname(FP_LANDING_PAGE_FILE);
        $vendor_dir = $plugin_dir . '/vendor';
        $composer_json = $plugin_dir . '/composer.json';
        
        // Se vendor non esiste ma composer.json sì, esegui composer install
        if (!is_dir($vendor_dir) && file_exists($composer_json)) {
            // Esegui in background per non bloccare il caricamento
            if (is_admin()) {
                add_action('admin_init', function() use ($plugin_dir) {
                    Activation::ensure_composer_dependencies();
                }, 1);
            }
        }
        
        // Imposta transient per non controllare di nuovo per 1 ora
        set_transient($transient_key, time(), HOUR_IN_SECONDS);
    }
}
