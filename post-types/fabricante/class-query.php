<?php

if (!defined('ABSPATH')) exit;

class FGE_Fabricante_Query {

    public function __construct() {
        add_action('elementor/query/fabricantes_query', [$this, 'apply']);
    }

    public function apply($query) {
        if ($query->get('post_type') !== 'fabricante') return;

        if (!empty($_GET['fabricacao'])) {
            $fabricacao = sanitize_text_field($_GET['fabricacao']);

            $meta_query = $query->get('meta_query') ?: [];

            $meta_query[] = [
                'key'     => 'fabricacao',
                'value'   => '"' . $fabricacao . '"',
                'compare' => 'LIKE',
            ];

            $query->set('meta_query', $meta_query);
        }
    }
}
