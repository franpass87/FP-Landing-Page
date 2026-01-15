<?php
/**
 * Controller REST API
 *
 * @package FPLandingPage
 */

namespace FPLandingPage\REST;

defined('ABSPATH') || exit;

/**
 * Classe per gestire le REST API
 */
class Controller {
    
    /**
     * Namespace REST
     */
    const NAMESPACE = 'fp-landing-page/v1';
    
    /**
     * Costruttore
     */
    public function __construct() {
        add_action('rest_api_init', [$this, 'register_routes']);
    }
    
    /**
     * Registra le routes REST
     */
    public function register_routes() {
        // Qui verranno registrate le routes REST
    }
}
