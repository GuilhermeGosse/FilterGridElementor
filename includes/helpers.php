<?php
if (!defined('ABSPATH')) exit;

/**
 * Versão aprimorada para ser reutilizável
 */
function fge_generate_acf_filter($field_name, $post_type, $query_param = '') {
    if (empty($query_param)) {
        $query_param = $field_name;
    }
    
    $values = fge_get_unique_acf_values($post_type, $field_name);
    
    ob_start(); ?>
    <form method="get" class="filtro-acf">
        <?php if (isset($_GET['post'])): ?>
            <input type="hidden" name="post" value="<?= esc_attr($_GET['post']) ?>">
        <?php endif; ?>
        
        <select name="<?= esc_attr($query_param) ?>" onchange="this.form.submit()">
            <option value="">Todos</option>
            <?php foreach ($values as $value): ?>
                <option value="<?= esc_attr($value) ?>" <?php selected($_GET[$query_param] ?? '', $value) ?>>
                    <?= esc_html($value) ?>
                </option>
            <?php endforeach; ?>
        </select>
        
        <?php if (isset($_GET['s'])): ?>
            <input type="hidden" name="s" value="<?= esc_attr($_GET['s']) ?>">
        <?php endif; ?>
    </form>
    <?php
    return ob_get_clean();
}