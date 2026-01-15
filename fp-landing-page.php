<?php
/**
 * Plugin Name: FP Landing Page
 * Plugin URI: https://francescopasseri.com/plugins/fp-landing-page
 * Description: Plugin per la gestione e creazione di landing page personalizzate
 * Version: 1.0.0
 * Author: Francesco Passeri
 * Author URI: https://francescopasseri.com
 * Text Domain: fp-landing-page
 * Domain Path: /languages
 * Requires at least: 6.0
 * Requires PHP: 7.4
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 */

namespace FPLandingPage;

defined('ABSPATH') || exit;

// Costanti del plugin
if (!defined('FP_LANDING_PAGE_VERSION')) {
    define('FP_LANDING_PAGE_VERSION', '1.0.0');
}
if (!defined('FP_LANDING_PAGE_FILE')) {
    define('FP_LANDING_PAGE_FILE', __FILE__);
}
if (!defined('FP_LANDING_PAGE_DIR')) {
    define('FP_LANDING_PAGE_DIR', plugin_dir_path(__FILE__));
}
if (!defined('FP_LANDING_PAGE_URL')) {
    define('FP_LANDING_PAGE_URL', plugin_dir_url(__FILE__));
}
if (!defined('FP_LANDING_PAGE_BASENAME')) {
    define('FP_LANDING_PAGE_BASENAME', plugin_basename(__FILE__));
}

/**
 * Funzioni helper per Composer
 */
if (!function_exists('FPLandingPage\find_composer_binary')) {
    function find_composer_binary() {
        $paths = ['composer', 'composer.phar'];
        
        // Su Windows
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            $paths = array_merge($paths, ['composer.bat']);
        }
        
        // Percorsi comuni
        $home = getenv('HOME') ?: getenv('USERPROFILE');
        $common_paths = [
            '/usr/local/bin/composer',
            '/usr/bin/composer',
        ];
        
        if ($home) {
            $common_paths[] = $home . '/.composer/vendor/bin/composer';
            $common_paths[] = $home . '/.config/composer/vendor/bin/composer';
        }
        
        $paths = array_merge($paths, $common_paths);
        
        foreach ($paths as $path) {
            if (empty($path)) {
                continue;
            }
            $output = [];
            $return_var = 0;
            @exec(escapeshellarg($path) . ' --version 2>&1', $output, $return_var);
            if ($return_var === 0) {
                return $path;
            }
        }
        
        return false;
    }
}

if (!function_exists('FPLandingPage\try_composer_install')) {
    function try_composer_install($plugin_dir) {
        $composer_path = \FPLandingPage\find_composer_binary();
        
        if (!$composer_path) {
            return false;
        }
        
        $command = escapeshellarg($composer_path) . ' install --no-dev --optimize-autoloader --no-interaction --working-dir=' . escapeshellarg($plugin_dir) . ' 2>&1';
        
        $output = [];
        $return_var = 0;
        @exec($command, $output, $return_var);
        
        return $return_var === 0;
    }
}

// Autoload PSR-4 via Composer
$autoload_path = FP_LANDING_PAGE_DIR . 'vendor/autoload.php';
if (!file_exists($autoload_path)) {
    // Tenta di eseguire composer install automaticamente
    $composer_installed = false;
    
    // Solo in admin per evitare overhead su frontend
    if (is_admin() && file_exists(FP_LANDING_PAGE_DIR . 'composer.json')) {
        $composer_installed = \FPLandingPage\try_composer_install(FP_LANDING_PAGE_DIR);
        
        // Se installato con successo, ricarica il file
        if ($composer_installed && file_exists($autoload_path)) {
            require_once $autoload_path;
        }
    }
    
    // Se ancora non esiste, mostra avviso
    if (!file_exists($autoload_path)) {
        add_action('admin_notices', function() use ($composer_installed) {
            if (!current_user_can('activate_plugins')) {
                return;
            }
            $plugin_dir = esc_html(FP_LANDING_PAGE_DIR);
            echo '<div class="notice notice-error"><p>';
            echo '<strong>' . esc_html__('FP Landing Page:', 'fp-landing-page') . '</strong> ';
            if ($composer_installed === false) {
                echo esc_html__('Impossibile eseguire automaticamente composer install.', 'fp-landing-page') . ' ';
            }
            echo esc_html__('Esegui manualmente:', 'fp-landing-page') . ' <code>composer install</code> ';
            echo esc_html__('nella cartella del plugin:', 'fp-landing-page') . ' <code>' . $plugin_dir . '</code>';
            echo '</p></div>';
        });
        return;
    }
}

if (file_exists($autoload_path)) {
    require_once $autoload_path;
}

// Inizializza il plugin
add_action('plugins_loaded', function() {
    // Carica traduzioni
    load_plugin_textdomain('fp-landing-page', false, dirname(FP_LANDING_PAGE_BASENAME) . '/languages');
    
    // Inizializza il plugin principale
    Plugin::get_instance();
}, 10);

// Hook di attivazione
register_activation_hook(__FILE__, function() {
    if (class_exists('FPLandingPage\Activation')) {
        Activation::activate();
    }
});

// Hook di disattivazione
register_deactivation_hook(__FILE__, function() {
    if (class_exists('FPLandingPage\Deactivation')) {
        Deactivation::deactivate();
    }
});
