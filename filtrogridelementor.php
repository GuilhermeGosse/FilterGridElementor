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

if (!defined('ABSPATH')) {
    exit; // Sai se acessado diretamente
}

class FiltroGridElementor {
    
    private $github_username = 'GuilhermeGosse';
    private $github_repo = 'FilterGridElementor';
    
    public function __construct() {
        // Verifica se o ACF está ativo
        if (!function_exists('get_field')) {
            add_action('admin_notices', array($this, 'acf_missing_notice'));
            return;
        }

        add_shortcode('filtro', array($this, 'filtro_elementor_shortcode'));
        add_action('elementor/query/fabricantes_query', array($this, 'fabricante_query'));
        add_filter('pre_set_site_transient_update_plugins', array($this, 'check_for_plugin_update'));
    }
    
    public function acf_missing_notice() {
        ?>
        <div class="notice notice-error">
            <p>O plugin <strong>Filtros Grid Elementor</strong> requer o plugin Advanced Custom Fields (ACF) para funcionar corretamente.</p>
        </div>
        <?php
    }
    
    public function check_for_plugin_update($transient) {
        if (empty($transient->checked)) {
            return $transient;
        }

        $plugin_slug = plugin_basename(__FILE__);
        $remote_version = $this->get_remote_version();

        if ($remote_version && version_compare($this->get_plugin_version(), $remote_version, '<')) {
            $response = new stdClass();
            $response->slug = 'filtrogridelementor';
            $response->plugin = $plugin_slug;
            $response->new_version = $remote_version;
            $response->package = "https://github.com/{$this->github_username}/{$this->github_repo}/archive/refs/tags/v{$remote_version}.zip";
            $response->tested = get_bloginfo('version');
            $transient->response[$plugin_slug] = $response;
        }

        return $transient;
    }

    private function get_remote_version() {
        $api_url = "https://api.github.com/repos/{$this->github_username}/{$this->github_repo}/releases/latest";
        
        $args = array(
            'headers' => array(
                'Accept' => 'application/vnd.github.v3+json'
            ),
            'timeout' => 15
        );
        
        $response = wp_remote_get($api_url, $args);
        
        if (is_wp_error($response)) {
            error_log('Erro ao verificar atualizações do plugin: ' . $response->get_error_message());
            return false;
        }

        if (wp_remote_retrieve_response_code($response) !== 200) {
            return false;
        }

        $body = json_decode(wp_remote_retrieve_body($response));
        return isset($body->tag_name) ? ltrim($body->tag_name, 'v') : false;
    }

    private function get_plugin_version() {
        $plugin_data = get_file_data(__FILE__, array('Version' => 'Version'));
        return $plugin_data['Version'];
    }
    
    public function filtro_elementor_shortcode() {
        $valores_fabricacao = array();
        $posts = get_posts(array(
            'post_type' => 'fabricante',
            'posts_per_page' => -1,
            'post_status' => 'publish',
            'fields' => 'ids',
        ));

        foreach ($posts as $post_id) {
            $valor = get_field('fabricacao', $post_id);
            if ($valor) {
                $valores = is_array($valor) ? $valor : array($valor);
                $valores_fabricacao = array_merge($valores_fabricacao, $valores);
            }
        }

        $valores_fabricacao = array_unique($valores_fabricacao);
        sort($valores_fabricacao);

        ob_start(); ?>
        <form id="form-fabricacao" method="get" class="filtro-grid-form">
            <label for="fabricacao" class="filtro-grid-label">Categoria de fabricação:</label>
            <select name="fabricacao" id="fabricacao" class="filtro-grid-select">
                <option value=""><?php esc_html_e('Todas', 'filtrogridelementor'); ?></option>
                <?php foreach ($valores_fabricacao as $valor): ?>
                    <option value="<?php echo esc_attr($valor); ?>" <?php selected(isset($_GET['fabricacao']) ? $_GET['fabricacao'] : '', $valor); ?>>
                        <?php echo esc_html($valor); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </form>

        <script>
        document.addEventListener('DOMContentLoaded', function() {
            var select = document.getElementById('fabricacao');
            if (select) {
                select.addEventListener('change', function() {
                    this.form.submit();
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
            
            $meta_query = (array) $query->get('meta_query');
            $meta_query[] = array(
                'key' => 'fabricacao',
                'value' => '"' . $fabricacao . '"',
                'compare' => 'LIKE'
            );
            
            $query->set('meta_query', $meta_query);
        }
    }
}

// Inicializa o plugin
add_action('plugins_loaded', function() {
    new FiltroGridElementor();
});
