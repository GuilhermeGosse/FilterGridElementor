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
*/

// Sistema de atualização automática
add_filter('update_plugins_github.com', function($update, $plugin_data, $plugin_file, $locales) {
    // Verifica se é este plugin
    if (plugin_basename(__FILE__) !== $plugin_file) {
        return $update;
    }
    
    // Obter a versão atual do plugin
    $plugin_data = get_file_data(__FILE__, ['Version' => 'Version']);
    $current_version = $plugin_data['Version'];
    
    return [
        'slug' => 'filtrogridelementor',
        'version' => $current_version,
        'url' => 'https://github.com/GuilhermeGosse/FilterGridElementor',
        'package' => 'https://github.com/GuilhermeGosse/FilterGridElementor/archive/refs/tags/v' . $current_version . '.zip',
        'requires' => '5.6',
        'requires_php' => '7.4',
    ];
}, 10, 4);

if (!defined('ABSPATH')) {
    exit; 
}

class FiltroGridElementor {
    
    public function __construct() {
     
        add_shortcode('filtro', array($this, 'filtro_fabricacao_elementor_shortcode'));
        add_action('elementor/query/fabricantes_query', array($this, 'fabricante_query'));
    }
    
    public function filtro_elementor_shortcode() {

        $valores_fabricacao = [];

        $posts = get_posts([
            'post_type'      => 'fabricante',
            'posts_per_page' => -1,
            'post_status'    => 'publish',
            'fields'         => 'ids', 
        ]);

        foreach ($posts as $post_id) {
            $valor = get_field('fabricacao', $post_id);

            if (is_array($valor)) {
                foreach ($valor as $v) {
                    $valores_fabricacao[] = $v;
                }
            } elseif (!empty($valor)) {
                $valores_fabricacao[] = $valor;
            }
        }

        $valores_fabricacao = array_unique($valores_fabricacao);
        sort($valores_fabricacao);

        ob_start();
        ?>
        <form id="form-fabricacao" method="get">
            <label for="fabricacao">Categoria de fabricação:</label>
            <select name="fabricacao" id="fabricacao">
                <option value="" <?php selected($_GET['fabricacao'] ?? '', ''); ?>>Todas</option>
                <?php foreach ($valores_fabricacao as $valor): ?>
                    <option value="<?php echo esc_attr($valor); ?>" <?php selected($_GET['fabricacao'] ?? '', $valor); ?>>
                        <?php echo esc_html($valor); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </form>

        <script>
        document.addEventListener('DOMContentLoaded', function () {
            const select = document.getElementById('fabricacao');
            if (select) {
                select.addEventListener('change', function () {
                    document.getElementById('form-fabricacao').submit();
                });
            }
        });
        </script>
        <?php
        return ob_get_clean();
    }
    
    public function fabricante_query($query) {
        if (!isset($query->query_vars['post_type']) || $query->query_vars['post_type'] !== 'fabricante') {
            return;
        }

        if (!empty($_GET['fabricacao'])) {
            $fabricacao = sanitize_text_field($_GET['fabricacao']);

            $meta_query = $query->get('meta_query');
            if (!is_array($meta_query)) {
                $meta_query = [];
            }

            $meta_query[] = [
                'key'     => 'fabricacao',
                'value'   => '"' . $fabricacao . '"',
                'compare' => 'LIKE'
            ];

            $query->set('meta_query', $meta_query);
        }
    }
}

new FiltroGridElementor();