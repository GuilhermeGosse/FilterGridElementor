<?php
if (!defined('ABSPATH')) exit;

if (!class_exists('FGE_Updater')) {
    class FGE_Updater {
        
        private $github_user = 'GuilhermeGosse';
        private $github_repo = 'FilterGridElementor';
        private $plugin_file;
        
        public function __construct($plugin_file) {
            if (!function_exists('get_plugin_data')) {
                require_once ABSPATH . 'wp-admin/includes/plugin.php';
            }
            
            $this->plugin_file = $plugin_file;
            add_filter('pre_set_site_transient_update_plugins', [$this, 'check_updates']);
        }
        
        public function check_updates($transient) {
            if (empty($transient->checked)) {
                return $transient;
            }

            $remote_data = $this->get_remote_data();
            
            if (!$remote_data) return $transient;

            $current_version = $this->get_local_version();

            if (version_compare($current_version, $remote_data->version, '<')) {
                $transient->response[$this->plugin_file] = (object) [
                    'slug' => 'filtrogridelementor',
                    'plugin' => plugin_basename($this->plugin_file),
                    'new_version' => $remote_data->version,
                    'package' => $remote_data->download_url,
                    'tested' => get_bloginfo('version')
                ];
            }

            return $transient;
        }

        private function get_remote_data() {
            $response = wp_remote_get(
                "https://api.github.com/repos/{$this->github_user}/{$this->github_repo}/releases/latest",
                [
                    'headers' => ['Accept' => 'application/vnd.github.v3+json'],
                    'timeout' => 15
                ]
            );

            if (is_wp_error($response)) {
                error_log('FGE Updater Error: ' . $response->get_error_message());
                return false;
            }

            $code = wp_remote_retrieve_response_code($response);
            if ($code !== 200) {
                error_log("FGE Updater: GitHub API returned status {$code}");
                return false;
            }

            $body = json_decode(wp_remote_retrieve_body($response));
            
            if (empty($body->tag_name)) {
                error_log('FGE Updater: No tag found in GitHub response');
                return false;
            }

            return (object) [
                'version' => ltrim($body->tag_name, 'v'),
                'download_url' => $body->zipball_url
            ];
        }

        private function get_local_version() {
            $plugin_data = get_plugin_data($this->plugin_file);
            return $plugin_data['Version'] ?? '0.0.0';
        }
    }
}