<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/*
|--------------------------------------------------------------------------
| Configuración de Rutas - Sistema Escolar Regencia
|--------------------------------------------------------------------------
| Todas las rutas de la API REST prefijadas con /api/
*/

// Ruta por defecto (no se usa en API)
$route['default_controller'] = 'welcome';
$route['404_override'] = '';
$route['translate_uri_dashes'] = FALSE;

// ---- Estudiantes ----
$route['api/estudiantes']['GET']             = 'Estudiantes/index';
$route['api/estudiantes']['POST']            = 'Estudiantes/store';
$route['api/estudiantes/(:any)']['GET']      = 'Estudiantes/show/$1';
$route['api/estudiantes/(:any)']['PUT']      = 'Estudiantes/update/$1';
$route['api/estudiantes/(:any)']['DELETE']   = 'Estudiantes/destroy/$1';
$route['api/estudiantes/(:any)/foto']['POST'] = 'Estudiantes/uploadFoto/$1';

// ---- Cursos ----
$route['api/cursos']['GET']                  = 'Cursos/index';
$route['api/cursos']['POST']                 = 'Cursos/store';
$route['api/cursos/(:num)']['GET']           = 'Cursos/show/$1';
$route['api/cursos/(:num)']['PUT']           = 'Cursos/update/$1';
$route['api/cursos/(:num)']['DELETE']        = 'Cursos/destroy/$1';
$route['api/cursos/(:num)/estudiantes']['GET'] = 'Cursos/estudiantes/$1';

// ---- Horarios ----
$route['api/horarios']['GET']                = 'Horarios/index';
$route['api/horarios']['POST']               = 'Horarios/store';
$route['api/horarios/(:num)']['GET']         = 'Horarios/show/$1';
$route['api/horarios/(:num)']['PUT']         = 'Horarios/update/$1';
$route['api/horarios/(:num)']['DELETE']      = 'Horarios/destroy/$1';

// ---- Asistencias ----
$route['api/asistencias']['GET']             = 'Asistencias/index';
$route['api/asistencias/escanear']['POST']   = 'Asistencias/escanear';   // Endpoint de escaneo de RUDE
$route['api/asistencias/reporte']['GET']     = 'Asistencias/reporte';

// ---- Licencias ----
$route['api/licencias']['GET']               = 'Licencias/index';
$route['api/licencias']['POST']              = 'Licencias/store';
$route['api/licencias/(:num)']['GET']        = 'Licencias/show/$1';
$route['api/licencias/(:num)']['PUT']        = 'Licencias/update/$1';
$route['api/licencias/(:num)/upload']['POST'] = 'Licencias/uploadArchivo/$1';
$route['api/licencias/estudiante/(:any)']['GET'] = 'Licencias/porEstudiante/$1';

// ---- Autenticación ----
$route['api/auth/login']['POST']  = 'Auth/login';
$route['api/auth/me']['GET']     = 'Auth/me';
$route['api/auth/logout']['POST'] = 'Auth/logout';

// ---- Gestión de Usuarios (Admin) ----
$route['api/usuarios']['GET']                = 'Usuarios/index';
$route['api/usuarios']['POST']               = 'Usuarios/store';
$route['api/usuarios/(:num)']['PUT']         = 'Usuarios/update/$1';
$route['api/usuarios/(:num)']['DELETE']      = 'Usuarios/destroy/$1';

// ---- Materias ----
$route['api/materias']['GET']                  = 'Materias/index';
$route['api/materias']['POST']                 = 'Materias/store';
$route['api/materias/(:num)']['PUT']           = 'Materias/update/$1';
$route['api/materias/(:num)']['DELETE']        = 'Materias/destroy/$1';

// ---- Retrasos ----
$route['api/retrasos']['GET']                  = 'Retrasos/index';
$route['api/retrasos']['POST']                 = 'Retrasos/store';
$route['api/retrasos/(:num)']['DELETE']        = 'Retrasos/destroy/$1';
$route['api/retrasos/estudiante/(:any)']['GET'] = 'Retrasos/porEstudiante/$1';
