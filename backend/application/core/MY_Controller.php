<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * MY_Controller - Controlador Base REST para la API
 *
 * En CodeIgniter 3, los archivos en application/core/ deben
 * usar el prefijo MY_ para ser cargados automáticamente.
 *
 * Todos los controllers de la API extienden de esta clase.
 */
class MY_Controller extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        // Cargar base de datos en todos los controladores
        $this->load->database();
    }

    /**
     * Envía una respuesta JSON con el código HTTP indicado.
     *
     * @param mixed $data    Datos a serializar
     * @param int   $status  Código HTTP (200, 201, 400, 404, 500...)
     */
    protected function jsonResponse($data, int $status = 200): void
    {
        $this->output
            ->set_status_header($status)
            ->set_content_type('application/json', 'utf-8')
            ->set_output(json_encode($data, JSON_UNESCAPED_UNICODE));
    }

    /**
     * Respuesta de éxito estándar.
     */
    protected function success($data = null, string $message = 'OK', int $status = 200): void
    {
        $this->jsonResponse([
            'success' => true,
            'message' => $message,
            'data'    => $data,
        ], $status);
    }

    /**
     * Respuesta de error estándar.
     */
    protected function error(string $message = 'Error', int $status = 400, $errors = null): void
    {
        $this->jsonResponse([
            'success' => false,
            'message' => $message,
            'errors'  => $errors,
        ], $status);
    }

    /**
     * Obtiene el cuerpo de la petición como JSON (para PUT y POST con JSON).
     */
    protected function getJsonBody(): array
    {
        $raw  = file_get_contents('php://input');
        $data = json_decode($raw, true);
        return is_array($data) ? $data : [];
    }
}

/**
 * Alias por compatibilidad — permite que los controladores
 * usen el nombre REST_Controller si así se definieron.
 */
class REST_Controller extends MY_Controller
{
    // Hereda todo de MY_Controller
}
