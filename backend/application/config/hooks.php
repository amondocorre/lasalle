<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/*
|--------------------------------------------------------------------------
| Hooks - Sistema Escolar Regencia
|--------------------------------------------------------------------------
| Se activa el hook para manejar CORS en todas las peticiones de la API.
*/

// Hook para responder preflight OPTIONS y añadir cabeceras CORS
$hook['post_controller_constructor'][] = array(
    'class'    => 'CorsHook',
    'function' => 'handle',
    'filename' => 'CorsHook.php',
    'filepath' => 'hooks',
);
