<?php
if (!defined('ABSPATH')) exit;

class FGE_Loader {

    protected $post_types_dir;

    public function __construct() {
        $this->post_types_dir = trailingslashit(plugin_dir_path(__FILE__) . '../post-types');
        $this->load_filters();
    }

    private function load_filters() {
        $post_types = scandir($this->post_types_dir);

        foreach ($post_types as $pt) {
            if ($pt === '.' || $pt === '..') continue;

            $pt_path = realpath($this->post_types_dir . $pt);
            if (!$pt_path || !is_dir($pt_path)) continue;

            $config_file = $pt_path . '/config.php';
            if (file_exists($config_file)) {
                require_once $config_file;
            }

            $trait_file = $pt_path . '/trait-helpers.php';
            if (file_exists($trait_file)) {
                require_once $trait_file;
            }

            $shortcode_file = $pt_path . '/class-shortcode.php';
            $query_file     = $pt_path . '/class-query.php';

            if (file_exists($shortcode_file)) require_once $shortcode_file;
            if (file_exists($query_file)) require_once $query_file;

            $shortcode_class = 'FGE_' . ucfirst($pt) . '_Shortcode';
            $query_class     = 'FGE_' . ucfirst($pt) . '_Query';

            if (class_exists($shortcode_class)) {
                new $shortcode_class();
            }

            if (class_exists($query_class)) {
                new $query_class();
            }
        }
    }
}

new FGE_Loader();
