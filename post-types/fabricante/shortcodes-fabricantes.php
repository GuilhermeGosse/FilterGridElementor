<?php
if (!defined('ABSPATH')) exit;

function fge_fabricantes_shortcode() {
    // Obtém todos os valores únicos do campo ACF 'fabricacao'
    $valores = [];
    
    $posts = get_posts([
        'post_type' => 'fabricante',
        'posts_per_page' => -1,
        'fields' => 'ids'
    ]);

    foreach ($posts as $post_id) {
        $valor = get_field('fabricacao', $post_id);
        if ($valor) {
            $valores = array_merge($valores, (array)$valor);
        }
    }

    $valores = array_unique($valores);
    sort($valores);

    // Gera o HTML do filtro
    ob_start(); ?>
    <form method="get" class="filtro-acf">
        <select name="fabricacao" onchange="this.form.submit()">
            <option value="">Todos</option>
            <?php foreach ($valores as $valor): ?>
                <option value="<?= esc_attr($valor) ?>" <?php selected($_GET['fabricacao'] ?? '', $valor) ?>>
                    <?= esc_html($valor) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </form>
    <?php
    return ob_get_clean();
}