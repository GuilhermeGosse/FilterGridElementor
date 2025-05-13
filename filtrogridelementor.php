<?php
/*
Plugin Name: Filtros Grid Elementor (ACF)
Plugin URI: https://github.com/GuilhermeGosse/FilterGridElementor
Description: Adiciona filtros baseados em campos ACF com integração do GRID Post do Elementor.
Version: 1.0.0
Author: Canal Solar
Author URI: https://github.com/GuilhermeGosse
Update URI: https://github.com/GuilhermeGosse/FilterGridElementor
Text Domain: filtrogridelementor
License: GPL2
*/

defined('ABSPATH') || exit;

// Defina constantes do plugin
define('FGE_PLUGIN_PATH', plugin_dir_path(__FILE__));
define('FGE_PLUGIN_URL', plugin_dir_url(__FILE__));

// Carrega os arquivos necessários
require_once FGE_PLUGIN_PATH . 'includes/class-updater.php';
require_once FGE_PLUGIN_PATH . 'post-types/fabricante/class-fabricantes.php';
require_once FGE_PLUGIN_PATH . 'post-types/distribuidora/class-distribuidoras.php';

class FiltroGridElementor {
    
    public function __construct() {
        // Verifica dependências
        $this->check_dependencies();
        
        // Inicializa os módulos
        new FGE_Fabricantes();
        new FGE_Distribuidoras();
        new FGE_Updater();
        
        // Carrega assets
        add_action('wp_enqueue_scripts', [$this, 'load_assets']);
    }
    
    private function check_dependencies() {
        if (!function_exists('get_field')) {
            add_action('admin_notices', function() {
                echo '<div class="notice notice-error"><p>O plugin <strong>Filtros Grid Elementor</strong> requer o plugin Advanced Custom Fields (ACF) para funcionar corretamente.</p></div>';
            });
            return false;
        }
        return true;
    }
    
    public function load_assets() {
        wp_enqueue_style(
            'filtrogridelementor-css',
            FGE_PLUGIN_URL . 'assets/css/styles.css',
            [],
            filemtime(FGE_PLUGIN_PATH . 'assets/css/styles.css')
        );
        
        wp_enqueue_script(
            'filtrogridelementor-js',
            FGE_PLUGIN_URL . 'assets/js/scripts.js',
            ['jquery'],
            filemtime(FGE_PLUGIN_PATH . 'assets/js/scripts.js'),
            true
        );
    }
}

// Inicializa o plugin
add_action('plugins_loaded', function() {
    new FiltroGridElementor();
});