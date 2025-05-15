<?php
if (!defined('ABSPATH')) exit;

class FGE_Fabricante_Query {

    protected $config;
    protected $post_type = 'distribuidora';

    public function __construct() {
        $this->config = require __DIR__ . '/config.php';
        add_action('elementor/query/distribuidoras_query', [$this, 'apply']);
    }

    public function apply($query) {
        if ($query->get('post_type') !== $this->post_type) return;

        $meta_query = [];

        foreach ($this->config as $param => $campo) {
            if (!empty($_GET[$param])) {
                $value = sanitize_text_field($_GET[$param]);

                $meta_query[] = [
                    'key'     => $campo['acf_key'],
                    'value'   => $campo['type'] === 'select' ? '"' . $value . '"' : $value,
                    'compare' => $campo['type'] === 'select' ? 'LIKE' : '=',
                ];
            }
        }

        if (!empty($meta_query)) {
            $query->set('meta_query', $meta_query);
        }
    }
}
