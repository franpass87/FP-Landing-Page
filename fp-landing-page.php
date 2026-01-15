<?php
/**
 * Plugin Name: FP Landing Page
 * Plugin URI: https://francescopasseri.com/plugins/fp-landing-page
 * Description: Plugin per la gestione e creazione di landing page personalizzate
 * Version: 1.0.4
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

// Costanti del plugin
if (!defined('FP_LANDING_PAGE_VERSION')) {
    define('FP_LANDING_PAGE_VERSION', '1.0.4');
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

// Autoload PSR-4 via Composer
$autoload_path = FP_LANDING_PAGE_DIR . 'vendor/autoload.php';
if (file_exists($autoload_path)) {
    require_once $autoload_path;
} else {
    add_action('admin_notices', function() {
        if (!current_user_can('activate_plugins')) {
            return;
        }
        echo '<div class="notice notice-error"><p>';
        echo '<strong>' . esc_html__('FP Landing Page:', 'fp-landing-page') . '</strong> ';
        echo esc_html__('Esegui', 'fp-landing-page') . ' <code>composer install</code> ';
        echo esc_html__('nella cartella del plugin.', 'fp-landing-page');
        echo '</p></div>';
    });
    return;
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
