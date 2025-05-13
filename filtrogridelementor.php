<?php
/**
 * Plugin Name: Filtros Grid Elementor (ACF)
 * Plugin URI: https://github.com/GuilhermeGosse/FilterGridElementor
 * Description: Adiciona filtros baseados em campos ACF com integração do GRID Post do Elementor.
 * Version: 1.0.0
 * Author: Canal Solar
 * Author URI: https://github.com/GuilhermeGosse
 * Update URI: https://github.com/GuilhermeGosse/FilterGridElementor
 * Text Domain: filtrogridelementor
 * License: GPL2
 * Requires at least: 5.6
 * Requires PHP: 7.4
 */

defined('ABSPATH') || exit;

// Defina constantes do plugin
define('FGE_PLUGIN_PATH', plugin_dir_path(__FILE__));
define('FGE_PLUGIN_URL', plugin_dir_url(__FILE__));
define('FGE_PLUGIN_VERSION', '1.0.0');

class FiltroGridElementor {
    
    private static $instance = null;
    
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        // Verifica e carrega dependências
        if (!$this->load_dependencies()) {
            return;
        }
        
        // Inicializa componentes
        $this->init_components();
        
        // Registra hooks
        $this->register_hooks();
    }
    
    private function load_dependencies() {
        // Verifica dependências críticas
        if (!function_exists('get_field')) {
            add_action('admin_notices', [$this, 'show_acf_missing_notice']);
            return false;
        }
        
        // Arquivos necessários
        $required_files = [
            'includes/class-updater.php',
            'post-types/fabricante/class-fabricantes.php',
            'post-types/distribuidora/class-distribuidoras.php',
            'includes/helpers.php'
        ];
        
        foreach ($required_files as $file) {
            $file_path = FGE_PLUGIN_PATH . $file;
            if (file_exists($file_path)) {
                require_once $file_path;
            } else {
                error_log("FGE Error: Arquivo não encontrado - {$file}");
                add_action('admin_notices', function() use ($file) {
                    echo '<div class="notice notice-error"><p>Filtro Grid Elementor: Arquivo essencial faltando - ' . esc_html($file) . '</p></div>';
                });
                return false;
            }
        }
        
        return true;
    }
    
    private function init_components() {
        // Inicializa os módulos principais
        new FGE_Fabricantes();
        new FGE_Distribuidoras();
        
        // Sistema de atualização
        if (class_exists('FGE_Updater')) {
            new FGE_Updater(__FILE__);
        }
    }
    
    private function register_hooks() {
        // Assets
        add_action('wp_enqueue_scripts', [$this, 'enqueue_assets']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_admin_assets']);
        
        // Internacionalização
        add_action('plugins_loaded', [$this, 'load_textdomain']);
    }
    
    public function show_acf_missing_notice() {
        echo '<div class="notice notice-error">';
        echo '<p>' . esc_html__('O plugin Filtros Grid Elementor requer o Advanced Custom Fields (ACF) instalado e ativado.', 'filtrogridelementor') . '</p>';
        echo '</div>';
    }
    
    public function enqueue_assets() {
        // CSS
        wp_enqueue_style(
            'fge-frontend-css',
            FGE_PLUGIN_URL . 'assets/css/frontend.css',
            [],
            filemtime(FGE_PLUGIN_PATH . 'assets/css/frontend.css')
        );
        
        // JS
        wp_enqueue_script(
            'fge-frontend-js',
            FGE_PLUGIN_URL . 'assets/js/frontend.js',
            ['jquery'],
            filemtime(FGE_PLUGIN_PATH . 'assets/js/frontend.js'),
            true
        );
        
        // Localiza script
        wp_localize_script('fge-frontend-js', 'fge_ajax', [
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('fge_nonce')
        ]);
    }
    
    public function enqueue_admin_assets($hook) {
        if ('post.php' === $hook || 'post-new.php' === $hook) {
            wp_enqueue_style(
                'fge-admin-css',
                FGE_PLUGIN_URL . 'assets/css/admin.css',
                [],
                filemtime(FGE_PLUGIN_PATH . 'assets/css/admin.css')
            );
        }
    }
    
    public function load_textdomain() {
        load_plugin_textdomain(
            'filtrogridelementor',
            false,
            dirname(plugin_basename(__FILE__)) . '/languages/'
        );
    }
}

// Inicialização segura do plugin
add_action('plugins_loaded', function() {
    // Verifica requisitos mínimos antes de iniciar
    if (version_compare(PHP_VERSION, '7.4', '<') {
        add_action('admin_notices', function() {
            echo '<div class="notice notice-error"><p>';
            echo esc_html__('Filtros Grid Elementor requer PHP 7.4 ou superior. Seu servidor está usando PHP ' . PHP_VERSION . '.', 'filtrogridelementor');
            echo '</p></div>';
        });
        return;
    }
    
    FiltroGridElementor::get_instance();
});

// Ativação do plugin
register_activation_hook(__FILE__, function() {
    // Cria tabelas ou opções necessárias
    if (!get_option('fge_flush_rewrite_rules')) {
        add_option('fge_flush_rewrite_rules', true);
    }
});

// Desativação do plugin
register_deactivation_hook(__FILE__, function() {
    // Limpeza ao desativar
    delete_option('fge_flush_rewrite_rules');
});