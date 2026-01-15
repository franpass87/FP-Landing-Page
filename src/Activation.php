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
        
        // Esegui composer install se necessario
        self::ensure_composer_dependencies();
        
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
     * Verifica e installa le dipendenze Composer se necessario
     */
    public static function ensure_composer_dependencies() {
        $plugin_dir = dirname(FP_LANDING_PAGE_FILE);
        $vendor_dir = $plugin_dir . '/vendor';
        $composer_json = $plugin_dir . '/composer.json';
        
        // Se vendor non esiste ma composer.json sì, esegui composer install
        if (!is_dir($vendor_dir) && file_exists($composer_json)) {
            self::run_composer_install($plugin_dir);
        }
    }
    
    /**
     * Esegue composer install
     */
    private static function run_composer_install($plugin_dir) {
        $composer_path = self::find_composer();
        
        if (!$composer_path) {
            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log('FP Landing Page: Composer non trovato. Esegui manualmente: composer install');
            }
            return false;
        }
        
        $command = escapeshellarg($composer_path) . ' install --no-dev --optimize-autoloader --working-dir=' . escapeshellarg($plugin_dir) . ' 2>&1';
        
        $output = [];
        $return_var = 0;
        exec($command, $output, $return_var);
        
        if ($return_var === 0) {
            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log('FP Landing Page: Composer install eseguito con successo');
            }
            return true;
        } else {
            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log('FP Landing Page: Errore durante composer install: ' . implode("\n", $output));
            }
            return false;
        }
    }
    
    /**
     * Trova il percorso di Composer
     */
    private static function find_composer() {
        // Prova composer globale
        $paths = [
            'composer', // Nel PATH
            '/usr/local/bin/composer',
            '/usr/bin/composer',
            getenv('HOME') . '/.composer/vendor/bin/composer',
            getenv('HOME') . '/.config/composer/vendor/bin/composer',
        ];
        
        // Su Windows
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            $paths = array_merge($paths, [
                'composer.bat',
                'composer.phar',
            ]);
        }
        
        foreach ($paths as $path) {
            $output = [];
            $return_var = 0;
            exec(escapeshellarg($path) . ' --version 2>&1', $output, $return_var);
            if ($return_var === 0) {
                return $path;
            }
        }
        
        return false;
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
        if (version_compare(PHP_VERSION, '7.0', '<')) {
            deactivate_plugins(plugin_basename(FP_LANDING_PAGE_FILE));
            wp_die(
                sprintf(__('FP Landing Page richiede PHP 7.0 o superiore. La versione attuale è %s.', 'fp-landing-page'), PHP_VERSION),
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
