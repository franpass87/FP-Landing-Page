<?php
/**
 * Custom Post Type: Landing Page
 *
 * @package FPLandingPage
 */

namespace FPLandingPage\PostTypes;

defined('ABSPATH') || exit;

/**
 * Gestisce il custom post type per le landing page
 */
class LandingPage {
    
    const POST_TYPE = 'fp_landing_page';
    
    /**
     * Registra il post type
     */
    public static function register() {
        add_action('init', [__CLASS__, 'register_post_type']);
        add_action('init', [__CLASS__, 'register_taxonomies']);
    }
    
    /**
     * Registra il custom post type
     */
    public static function register_post_type() {
        $labels = [
            'name'                  => _x('Landing Pages', 'Post type general name', 'fp-landing-page'),
            'singular_name'         => _x('Landing Page', 'Post type singular name', 'fp-landing-page'),
            'menu_name'             => _x('Landing Pages', 'Admin Menu text', 'fp-landing-page'),
            'add_new'               => __('Aggiungi Nuova', 'fp-landing-page'),
            'add_new_item'          => __('Aggiungi Nuova Landing Page', 'fp-landing-page'),
            'edit_item'             => __('Modifica Landing Page', 'fp-landing-page'),
            'view_item'             => __('Visualizza Landing Page', 'fp-landing-page'),
            'all_items'             => __('Tutte le Landing Pages', 'fp-landing-page'),
            'search_items'          => __('Cerca Landing Pages', 'fp-landing-page'),
            'not_found'             => __('Nessuna landing page trovata.', 'fp-landing-page'),
        ];
        
        $args = [
            'labels'             => $labels,
            'public'             => true,
            'publicly_queryable' => true,
            'show_ui'            => true,
            'show_in_menu'       => true,
            'query_var'          => true,
            'rewrite'            => ['slug' => 'landing-page'],
            'capability_type'    => 'post',
            'has_archive'        => false,
            'hierarchical'       => false,
            'menu_position'      => 20,
            'menu_icon'          => 'dashicons-welcome-view-site',
            'supports'           => ['title', 'author', 'thumbnail', 'editor'],
            'show_in_rest'       => false,
        ];
        
        register_post_type(self::POST_TYPE, $args);
    }
    
    /**
     * Registra tassonomie
     */
    public static function register_taxonomies() {
        // Categoria Landing Page
        register_taxonomy('fp_landing_category', [self::POST_TYPE], [
            'hierarchical'      => true,
            'labels'            => [
                'name'          => _x('Categorie', 'taxonomy general name', 'fp-landing-page'),
                'singular_name' => _x('Categoria', 'taxonomy singular name', 'fp-landing-page'),
                'search_items'  => __('Cerca Categorie', 'fp-landing-page'),
                'all_items'     => __('Tutte le Categorie', 'fp-landing-page'),
            ],
            'show_ui'           => true,
            'show_admin_column' => true,
            'query_var'         => true,
            'rewrite'           => ['slug' => 'categoria-landing'],
            'show_in_rest'      => false,
        ]);
    }
}
