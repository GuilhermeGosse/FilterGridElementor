<?php
if (!defined('ABSPATH')) exit;

require_once __DIR__ . '../../../includes/trait-helpers.php';

class FGE_Fabricante_Shortcode {

    use FGE_Helpers;

    protected $config;
    protected $post_type = 'distribuidora';

    public function __construct() {
        $this->config = require __DIR__ . '/config.php';
        add_shortcode('filtro_distribuidora', [$this, 'render']);
		add_action('wp_enqueue_scripts', [$this, 'enqueue_styles']);
    }
	
	public function enqueue_styles() {
        wp_enqueue_style(
            'fge-filtro-styles',
            plugin_dir_url(__FILE__) . '../../assets/css/styles.css',
            [],
			'1.0.0'
          
        );
    }

  public function render() {
    ob_start();
    ?>
    <form id="form-distribuidora" method="get">
       <?php foreach ($this->config as $name => $campo):
    if ($campo['type'] !== 'select') continue;

    $label = $campo['label'];
    $valores = $this->get_unique_acf_values($this->post_type, $campo['acf_key']);
    // Pega valores selecionados (agora serão múltiplos)
    $valores_selecionados = isset($_GET[$name]) ? (array) $_GET[$name] : [];
?>
    <fieldset>
        <legend><?= esc_html($label); ?>:</legend>
        <?php foreach ($valores as $valor): ?>
            <label>
                <input 
                    type="checkbox" 
                    name="<?= esc_attr($name); ?>[]" 
                    value="<?= esc_attr($valor); ?>" 
                    <?= in_array($valor, $valores_selecionados) ? 'checked' : ''; ?>
                >
                <?= esc_html($valor); ?>
            </label>
        <?php endforeach; ?>
    </fieldset>
<?php endforeach; ?>

        <!-- Campo de busca textual -->
        <label for="search_term">Buscar distribuidor:</label>
        <input type="text" name="search_term" id="search_term"
               value="<?= esc_attr($_GET['search_term'] ?? ''); ?>"
               placeholder="Digite o nome...">

        <button type="submit">Filtrar</button>
<button type="button" onclick="document.getElementById('form-distribuidora').reset(); window.location.href = window.location.pathname;">Limpar</button>

    </form>
    <?php
    return ob_get_clean();
}

}
