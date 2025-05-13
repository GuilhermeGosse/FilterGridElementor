<?php
/*
Plugin Name: Filtros Grid Elementor (ACF)
Version: 1.0.2
Update URI: https://github.com/GuilhermeGosse/FilterGridElementor
*/

defined('ABSPATH') || exit;

class FiltroGridElementor_Updater {
    
    private $plugin_slug;
    private $plugin_file;
    private $github_username = 'GuilhermeGosse';
    private $github_repo = 'FilterGridElementor';
    
    public function __construct() {
        $this->plugin_file = plugin_basename(__FILE__);
        $this->plugin_slug = basename(__FILE__, '.php');
        
        add_filter('site_transient_update_plugins', [$this, 'force_update_check'], 20, 1);
        add_filter('plugins_api', [$this, 'plugin_info'], 20, 3);
    }
    
    public function force_update_check($transient) {
        // Remove o filtro temporariamente para evitar loops
        remove_filter('site_transient_update_plugins', [$this, 'force_update_check'], 20);
        
        // Força a verificação apenas para este plugin
        $update = $this->check_update();
        
        if ($update && version_compare($this->get_local_version(), $update->new_version, '<')) {
            $transient->response[$this->plugin_file] = $update;
        } else {
            unset($transient->response[$this->plugin_file]);
        }
        
        // Restaura o filtro
        add_filter('site_transient_update_plugins', [$this, 'force_update_check'], 20, 1);
        
        return $transient;
    }
    
    private function check_update() {
        $remote_data = $this->get_remote_data();
        
        if (!$remote_data || !isset($remote_data->tag_name)) {
            return false;
        }
        
        $remote_version = ltrim($remote_data->tag_name, 'v');
        $local_version = $this->get_local_version();
        
        if (version_compare($local_version, $remote_version, '>=')) {
            return false;
        }
        
        return (object) [
            'slug' => $this->plugin_slug,
            'plugin' => $this->plugin_file,
            'new_version' => $remote_version,
            'package' => "https://github.com/{$this->github_username}/{$this->github_repo}/archive/refs/tags/{$remote_data->tag_name}.zip",
            'tested' => get_bloginfo('version')
        ];
    }
    
    private function get_remote_data() {
        $response = wp_remote_get(
            "https://api.github.com/repos/{$this->github_username}/{$this->github_repo}/releases/latest",
            [
                'headers' => ['Accept' => 'application/vnd.github.v3+json'],
                'timeout' => 15
            ]
        );
        
        if (is_wp_error($response) || wp_remote_retrieve_response_code($response) !== 200) {
            return false;
        }
        
        return json_decode(wp_remote_retrieve_body($response));
    }
    
    private function get_local_version() {
        static $version = null;
        if (is_null($version)) {
            $plugin_data = get_file_data(__FILE__, ['Version' => 'Version']);
            $version = $plugin_data['Version'];
        }
        return $version;
    }
    
    public function plugin_info($false, $action, $response) {
        if ($response->slug !== $this->plugin_slug) {
            return $false;
        }
        
        $remote_data = $this->get_remote_data();
        
        if (!$remote_data) {
            return $false;
        }
        
        return (object) [
            'name' => 'Filtros Grid Elementor',
            'slug' => $this->plugin_slug,
            'version' => ltrim($remote_data->tag_name, 'v'),
            'download_link' => "https://github.com/{$this->github_username}/{$this->github_repo}/archive/refs/tags/{$remote_data->tag_name}.zip",
            'sections' => [
                'description' => 'Plugin de filtros para Elementor com integração ACF'
            ]
        ];
    }
}

new FiltroGridElementor_Updater();
