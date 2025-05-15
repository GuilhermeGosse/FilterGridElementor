<?php
if (!defined('ABSPATH')) exit;

require_once __DIR__ . '/trait-helpers.php';

class FGE_Fabricante_Shortcode {

    use FGE_Helpers;

    protected $config;
    protected $post_type = 'fabricante';

    public function __construct() {
        $this->config = require __DIR__ . '/config.php';
        add_shortcode('filtro_fabricante', [$this, 'render']);
    }

    public function render() {
        ob_start();
        ?>
        <form id="form-fabricante" method="get">
            <?php foreach ($this->config as $name => $campo):
                $label = $campo['label'];
                $value_atual = $_GET[$name] ?? '';
                $valores = $this->get_unique_acf_values($this->post_type, $campo['acf_key']);
            ?>
                <label for="<?= esc_attr($name); ?>"><?= esc_html($label); ?>:</label>
                <select name="<?= esc_attr($name); ?>" id="<?= esc_attr($name); ?>">
                    <option value="">Todas</option>
                    <?php foreach ($valores as $valor): ?>
                        <option value="<?= esc_attr($valor); ?>" <?= selected($value_atual, $valor, false); ?>>
                            <?= esc_html($valor); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            <?php endforeach; ?>
            <button type="submit">Filtrar</button>
        </form>
        <?php
        return ob_get_clean();
    }
}
