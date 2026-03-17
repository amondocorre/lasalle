<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * CorsHook - Manejo de CORS para la API REST
 *
 * Permite que el frontend React (en otro puerto o dominio)
 * pueda comunicarse con el backend CodeIgniter.
 */
class CorsHook
{
    /**
     * Agrega los headers necesarios para CORS y responde a los
     * preflight OPTIONS sin ejecutar el controlador.
     */
    public function handle(): void
    {
        // Orígenes permitidos (ajustar en producción)
        $allowedOrigins = [
            'http://localhost:5173',  // Vite dev server
            'http://localhost:3000',  // Alternativa React
            'http://localhost',
        ];

        $origin = $_SERVER['HTTP_ORIGIN'] ?? '';

        if (in_array($origin, $allowedOrigins, true)) {
            header("Access-Control-Allow-Origin: {$origin}");
        } else {
            header('Access-Control-Allow-Origin: http://localhost:5173');
        }

        header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
        header('Access-Control-Allow-Credentials: true');
        header('Access-Control-Max-Age: 86400');

        // Responder inmediatamente a preflight OPTIONS
        if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
            http_response_code(204);
            exit();
        }
    }
}
