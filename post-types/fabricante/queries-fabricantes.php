<?php
if (!defined('ABSPATH')) exit;

function fge_fabricantes_query($query) {
    // Verifica se é uma query para o post type fabricante
    if (!isset($query->query_vars['post_type']) || $query->query_vars['post_type'] !== 'fabricante') {
        return;
    }

    // Filtro por campo ACF 'fabricacao'
    if (!empty($_GET['fabricacao'])) {
        $fabricacao = sanitize_text_field($_GET['fabricacao']);
        
        $meta_query = (array) $query->get('meta_query');
        $meta_query[] = [
            'key' => 'fabricacao',
            'value' => '"' . $fabricacao . '"', // Notação serializada do ACF
            'compare' => 'LIKE'
        ];
        
        $query->set('meta_query', $meta_query);
    }
}