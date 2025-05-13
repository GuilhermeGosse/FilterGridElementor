<?php
if (!defined('ABSPATH')) exit;

class FGE_Fabricantes {
    
    public function __construct() {
        // Carrega as dependências
        require_once __DIR__ . '/queries-fabricantes.php';
        require_once __DIR__ . '/shortcodes-fabricantes.php';
        
        // Configura as funcionalidades
        $this->setup_hooks();
    }
    
    private function setup_hooks() {
        // Integração com Elementor
        add_action('elementor/query/fabricantes_query', 'fge_fabricantes_query');
        
        // Shortcode principal
        add_shortcode('filtro_fabricantes', 'fge_fabricantes_shortcode');
        
        // Garante suporte para thumbnail (caso não tenha)
        add_action('init', function() {
            if (post_type_exists('fabricante') && !post_type_supports('fabricante', 'thumbnail')) {
                add_post_type_support('fabricante', 'thumbnail');
            }
        }, 20);
    }
}