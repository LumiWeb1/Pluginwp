<?php
/*
Plugin Name: Plugin OpenIA
Plugin URI: https://example.com/mi-plugin
Description: Este es un plugin de ejemplo con configuraciones personalizadas.
Version: 1.1.1
Author: Carlos Blanco Mauricio y Claude
Author URI: https://ejemplo.com
*/

add_action('admin_menu', 'mi_plugin_menu');

function mi_plugin_menu() {
    add_menu_page(
        'OpenIA', // Título de la página
        'OpenIA', // Título del menú
        'manage_options', // Capacidad requerida
        'mi-plugin', // Slug del menú
        'mi_plugin_opciones', // Función para renderizar la página de opciones
        'dashicons-admin-generic', // Icono del menú
        26 // Posición en el menú
    );
}

function mi_plugin_opciones() {
    // Verificar si se ha enviado el formulario
    if (isset($_POST['save_api_key'])) {
        // Procesar y guardar la API Key
        update_private_option('mi_plugin_api_key', sanitize_text_field($_POST['api_key']));
    }

    // Obtener la API Key guardada
    

    // Renderizar la página de opciones
    ?>
    <div class="wrap">
        <h1>API Key</h1>
        <form method="post" action="">
            <table class="form-table">
                <tr>
                    <th scope="row"><label for="api_key">API Key</label></th>
                    <td><input type="text" id="api_key" name="api_key" value="<?php echo esc_attr($api_key); ?>" /></td>
                </tr>
            </table>
            <?php submit_button('Save API Key', 'primary', 'save_api_key'); ?>
        </form>
    </div>
    <?php
}


function mi_plugin_estilos($hook) {
    if ('toplevel_page_mi-plugin' === $hook) {
        wp_enqueue_style('mi-plugin-estilos', plugin_dir_url(__FILE__) . 'css/estilos.css');
    }
}

function mi_plugin_scripts($hook) {
    if ('toplevel_page_mi-plugin' === $hook) {
        wp_enqueue_script('mi-plugin-scripts', plugin_dir_url(__FILE__) . 'js/scripts.js', array('jquery'), '1.0', true);
    }
}

add_action('admin_enqueue_scripts', 'mi_plugin_estilos');
add_action('admin_enqueue_scripts', 'mi_plugin_scripts');

// ################################################# shortcode

// Función para manejar el formulario de carga de audio


// Función para manejar el formulario de carga de audio
function handle_audio_upload() {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Verificar si se cargó un archivo
        if (isset($_FILES['audio_file']) && $_FILES['audio_file']['error'] === UPLOAD_ERR_OK) {
            $allowed_types = ['audio/wav', 'audio/mpeg', 'audio/ogg', 'audio/mp3'];
            $file_type = $_FILES['audio_file']['type'];

            // Validar el tipo de archivo
            if (!in_array($file_type, $allowed_types)) {
                echo '<p>Tipo de archivo no válido. Solo se permiten archivos de audio.</p>';
                return;
            }

            $audio_file = file_get_contents($_FILES['audio_file']['tmp_name']);

            // Obtener la clave de API desde un archivo externo
            $api_key = get_private_option('mi_plugin_api_key');

            // Realizar la solicitud a la API de OpenAI
            $url = 'https://api.openai.com/v1/audio/transcriptions';
            $headers = [
                'Authorization: Bearer ' . $api_key,
                'Content-Type: multipart/form-data',
            ];
            $data = [
                'file' => curl_file_create($_FILES['audio_file']['tmp_name'], $_FILES['audio_file']['type'], $_FILES['audio_file']['name']),
                'model' => 'whisper-1',
            ];

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

            $response = curl_exec($ch);
            $error = curl_error($ch);
            curl_close($ch);

            if ($error) {
                echo '<p>Error al comunicarse con la API: ' . $error . '</p>';
                return;
            }

            $response_data = json_decode($response, true);

            // Mostrar el texto transcrito en la página
            if (isset($response_data['text'])) {
                echo '<p>' . $response_data['text'] . '</p>';
            } else {
                echo '<p>Error al procesar el archivo de audio.</p>';
            }
        } else {
            echo '<p>No se cargó ningún archivo de audio.</p>';
        }
    }
}

// Crear el formulario de carga de audio
function audio_upload_form() {
    echo '<form method="post" enctype="multipart/form-data">';
    echo '<input type="file" name="audio_file" accept="audio/*" required>';
    echo '<input type="submit" value="Convertir Audio a Texto">';
    echo '</form>';
}

// Crear un shortcode para mostrar el formulario de carga de audio
function audio_to_text_shortcode() {
    ob_start();
    audio_upload_form();
    handle_audio_upload();
    return ob_get_clean();
}
add_shortcode('audio_to_text', 'audio_to_text_shortcode');
?>
