<?php
if (!defined('ABSPATH')) exit;

function fge_fabricantes_shortcode($atts) {
    $atts = shortcode_atts([
        'campo' => 'fabricacao', // Campo ACF padrão
        'parametro' => 'fabricacao' // Parâmetro da URL padrão
    ], $atts);
    
    return fge_generate_acf_filter(
        $atts['campo'], 
        'fabricante', 
        $atts['parametro']
    );
}