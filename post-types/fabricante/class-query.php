<?php
if (!defined('ABSPATH')) exit;

class FGE_Fabricante_Query {

    protected $config;
    protected $post_type = 'fabricante';

    public function __construct() {
        $this->config = require __DIR__ . '/config.php';
        add_action('elementor/query/fabricantes_query', [$this, 'apply']);
    }

public function apply($query) {
    if ($query->get('post_type') !== $this->post_type) return;

    $meta_query = [];

    foreach ($this->config as $param => $campo) {
        if (!empty($_GET[$param])) {
            // Para checkbox vem array
            $values = $_GET[$param];
            if (!is_array($values)) {
                $values = [$values];
            }

            if ($campo['type'] === 'select') {
                // filtro para múltiplos valores, cada valor precisa ser "like" com aspas
                $like_clauses = [];
                foreach ($values as $val) {
                    $val = sanitize_text_field($val);
                    $like_clauses[] = [
                        'key'     => $campo['acf_key'],
                        'value'   => '"' . $val . '"',
                        'compare' => 'LIKE',
                    ];
                }
                if (!empty($like_clauses)) {
                    $meta_query[] = [
                        'relation' => 'OR',
                        ...$like_clauses
                    ];
                }
            } else {
                // Para outros tipos (exemplo text) normal sanitização e comparação
                $value = sanitize_text_field(reset($values));
                $meta_query[] = [
                    'key'     => $campo['acf_key'],
                    'value'   => $value,
                    'compare' => '=',
                ];
            }
        }
    }

    if (!empty($meta_query)) {
        $query->set('meta_query', $meta_query);
    }
}

}
