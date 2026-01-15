<?php
/**
 * Plugin Name: FP Landing Page
 * Plugin URI: https://francescopasseri.com/plugins/fp-landing-page
 * Description: Plugin per la gestione e creazione di landing page personalizzate
 * Version: 1.0.2
 * Author: Francesco Passeri
 * Author URI: https://francescopasseri.com
 * Text Domain: fp-landing-page
 * Domain Path: /languages
 * Requires at least: 6.0
 * Requires PHP: 5.6
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 */

namespace FPLandingPage;

defined('ABSPATH') || exit;

// Gestione errori per prevenire fatal errors
error_reporting(E_ALL);
ini_set('display_errors', 0);

// Verifica versione PHP minima
if (version_compare(PHP_VERSION, '5.6', '<')) {
    add_action('admin_notices', function() {
        if (!current_user_can('activate_plugins')) {
            return;
        }
        echo '<div class="notice notice-error"><p>';
        echo '<strong>' . esc_html__('FP Landing Page:', 'fp-landing-page') . '</strong> ';
        echo sprintf(
            esc_html__('Il plugin richiede PHP 5.6 o superiore. La versione attuale è %s. Aggiorna PHP per utilizzare questo plugin.', 'fp-landing-page'),
            PHP_VERSION
        );
        echo '</p></div>';
    });
    
    // Disattiva il plugin se possibile
    if (function_exists('deactivate_plugins')) {
        add_action('admin_init', function() {
            deactivate_plugins(plugin_basename(__FILE__));
        });
    }
    
    return;
}

// Costanti del plugin
if (!defined('FP_LANDING_PAGE_VERSION')) {
    define('FP_LANDING_PAGE_VERSION', '1.0.1');
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

// CONTROLLO CRITICO: Se vendor/autoload.php non esiste, NON continuare
// Questo deve essere il PRIMO controllo dopo le costanti
$autoload_path = FP_LANDING_PAGE_DIR . 'vendor/autoload.php';
if (!file_exists($autoload_path)) {
    // Mostra notice e interrompi - NON registrare hook che potrebbero fallire
    if (function_exists('add_action')) {
        add_action('admin_notices', function() {
            if (function_exists('current_user_can') && current_user_can('activate_plugins')) {
                $plugin_dir = defined('ABSPATH') ? str_replace(ABSPATH, '', FP_LANDING_PAGE_DIR) : 'wp-content/plugins/FP-Landing-Page/';
                $plugin_dir_safe = function_exists('esc_html') ? esc_html($plugin_dir) : htmlspecialchars($plugin_dir, ENT_QUOTES, 'UTF-8');
                echo '<div class="notice notice-error"><p>';
                echo '<strong>FP Landing Page:</strong> ';
                echo 'Le dipendenze Composer non sono installate. Esegui <code>composer install</code> ';
                echo 'nella cartella del plugin: <code>' . $plugin_dir_safe . '</code>';
                echo '</p></div>';
            }
        });
    }
    // RETURN IMMEDIATO - non eseguire altro codice
    return;
}

/**
 * Funzioni helper per Composer
 */
if (!function_exists('\\FPLandingPage\\find_composer_binary')) {
    function find_composer_binary() {
        try {
            $paths = array('composer', 'composer.phar');
            
            // Su Windows
            if (function_exists('strtoupper') && function_exists('substr') && defined('PHP_OS')) {
                if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
                    $paths = array_merge($paths, array('composer.bat'));
                }
            }
            
            // Percorsi comuni
            $home = '';
            if (function_exists('getenv')) {
                $home = getenv('HOME');
                if (empty($home)) {
                    $home = getenv('USERPROFILE');
                }
            }
            $common_paths = array(
                '/usr/local/bin/composer',
                '/usr/bin/composer',
            );
            
            if (!empty($home)) {
                $common_paths[] = $home . '/.composer/vendor/bin/composer';
                $common_paths[] = $home . '/.config/composer/vendor/bin/composer';
            }
            
            $paths = array_merge($paths, $common_paths);
            
            foreach ($paths as $path) {
                if (empty($path)) {
                    continue;
                }
                if (function_exists('escapeshellarg') && function_exists('exec')) {
                    $output = array();
                    $return_var = 0;
                    @exec(escapeshellarg($path) . ' --version 2>&1', $output, $return_var);
                    if ($return_var === 0) {
                        return $path;
                    }
                }
            }
        } catch (\Exception $e) {
            // Ignora errori nella ricerca di composer
        }
        
        return false;
    }
}

if (!function_exists('\\FPLandingPage\\try_composer_install')) {
    function try_composer_install($plugin_dir) {
        try {
            if (!function_exists('\\FPLandingPage\\find_composer_binary')) {
                return false;
            }
            $composer_path = \FPLandingPage\find_composer_binary();
            
            if (!$composer_path || !function_exists('escapeshellarg') || !function_exists('exec')) {
                return false;
            }
            
            $command = escapeshellarg($composer_path) . ' install --no-dev --optimize-autoloader --no-interaction --working-dir=' . escapeshellarg($plugin_dir) . ' 2>&1';
            
            $output = array();
            $return_var = 0;
            @exec($command, $output, $return_var);
            
            return $return_var === 0;
        } catch (\Exception $e) {
            return false;
        }
    }
}

// Carica autoload - a questo punto sappiamo che esiste

require_once $autoload_path;

// Verifica che le classi siano disponibili prima di inizializzare
if (!class_exists('FPLandingPage\Plugin')) {
    // Se la classe Plugin non esiste, qualcosa è andato storto con l'autoload
    add_action('admin_notices', function() {
        if (function_exists('current_user_can') && current_user_can('activate_plugins')) {
            echo '<div class="notice notice-error"><p>';
            echo '<strong>' . esc_html__('FP Landing Page:', 'fp-landing-page') . '</strong> ';
            echo esc_html__('Errore nel caricamento delle classi. Esegui', 'fp-landing-page') . ' <code>composer install</code> ';
            echo esc_html__('nella cartella del plugin.', 'fp-landing-page');
            echo '</p></div>';
        }
    });
    return;
}

// Inizializza il plugin
add_action('plugins_loaded', function() {
    // Verifica nuovamente che autoload sia caricato
    if (!class_exists('FPLandingPage\Plugin')) {
        return;
    }
    
    // Carica traduzioni
    load_plugin_textdomain('fp-landing-page', false, dirname(FP_LANDING_PAGE_BASENAME) . '/languages');
    
    // Inizializza il plugin principale
    \FPLandingPage\Plugin::get_instance();
}, 10);

// Hook di attivazione - solo se autoload esiste
if (file_exists($autoload_path)) {
    register_activation_hook(__FILE__, function() {
        // Verifica che autoload sia ancora presente
        $autoload_path_check = FP_LANDING_PAGE_DIR . 'vendor/autoload.php';
        if (!file_exists($autoload_path_check)) {
            // Se autoload non esiste durante l'attivazione, disattiva il plugin
            if (function_exists('deactivate_plugins')) {
                deactivate_plugins(FP_LANDING_PAGE_BASENAME);
            }
            wp_die(
                'FP Landing Page: Le dipendenze Composer non sono installate. Esegui "composer install" nella cartella del plugin.',
                'Errore Attivazione Plugin',
                array('back_link' => true)
            );
            return;
        }
        require_once $autoload_path_check;
        
        if (class_exists('FPLandingPage\Activation')) {
            \FPLandingPage\Activation::activate();
        }
    });
}

// Hook di disattivazione - solo se autoload esiste
if (file_exists($autoload_path)) {
    register_deactivation_hook(__FILE__, function() {
        // Verifica che autoload sia ancora presente
        $autoload_path_check = FP_LANDING_PAGE_DIR . 'vendor/autoload.php';
        if (!file_exists($autoload_path_check)) {
            return;
        }
        require_once $autoload_path_check;
        
        if (class_exists('FPLandingPage\Deactivation')) {
            \FPLandingPage\Deactivation::deactivate();
        }
    });
}
