<?php

if (!defined('ABSPATH')) exit;

class FGE_Fabricante_Shortcode {

    public function __construct() {
        add_shortcode('filtro_fabricante', [$this, 'render']);
    }

    public function render() {
        $valores = [];

        $posts = get_posts([
            'post_type'      => 'fabricante',
            'posts_per_page' => -1,
            'post_status'    => 'publish',
            'fields'         => 'ids',
        ]);

        foreach ($posts as $post_id) {
            $valor = get_field('fabricacao', $post_id);
            if (is_array($valor)) {
                $valores = array_merge($valores, $valor);
            } elseif (!empty($valor)) {
                $valores[] = $valor;
            }
        }

        $valores = array_unique($valores);
        sort($valores);

        ob_start(); ?>
        <form id="form-fabricacao" method="get">
            <label for="fabricacao">Categoria de fabricação:</label>
            <select name="fabricacao" id="fabricacao">
                <option value="" <?php selected($_GET['fabricacao'] ?? '', ''); ?>>Todas</option>
                <?php foreach ($valores as $valor): ?>
                    <option value="<?= esc_attr($valor); ?>" <?php selected($_GET['fabricacao'] ?? '', $valor); ?>>
                        <?= esc_html($valor); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </form>
        <script src="<?= plugin_dir_url(__FILE__) . '../../assets/js/style.js'; ?>"></script>
        <?php return ob_get_clean();
    }
}
