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

// ---- Consulta Pública ----
$route['api/estudiantes_controller/consultar']['POST'] = 'Estudiantes_Controller/consultar';
$route['api/estudiantes_controller/consultar']['OPTIONS'] = 'Estudiantes_Controller/consultar';

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
$route['api/auth/login/padre']['POST'] = 'Auth/login_padre';
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

// ---- Dashboard ----
$route['api/dashboard/stats']['GET']           = 'Dashboard/stats';

// ---- Asignaciones (Carga Académica) ----
$route['api/asignaciones/curso/(:num)']['GET']  = 'Asignaciones/curso/$1';
$route['api/asignaciones/batch']['POST']        = 'Asignaciones/batch';
$route['api/asignaciones/validar-choque']['POST'] = 'Asignaciones/validar_choque';
$route['api/asignaciones/(:num)']['DELETE']     = 'Asignaciones/destroy/$1';

// ---- Periodos (Configuración Horarios) ----
$route['api/periodos']['GET']                  = 'Periodos/index';
$route['api/periodos']['POST']                 = 'Periodos/store';
$route['api/periodos/(:num)']['PUT']           = 'Periodos/update/$1';
$route['api/periodos/(:num)']['DELETE']        = 'Periodos/destroy/$1';

// ---- Profesores ----
$route['api/profesores']['GET']                = 'Profesores/index';
$route['api/profesores']['POST']               = 'Profesores/store';
$route['api/profesores/(:num)']['GET']         = 'Profesores/show/$1';
$route['api/profesores/(:num)/materias']['GET'] = 'Profesores/materias/$1';
$route['api/profesores/materia/(:num)']['GET'] = 'Profesores/por_materia/$1';
$route['api/profesores/(:num)']['PUT']         = 'Profesores/update/$1';
$route['api/profesores/(:num)']['DELETE']      = 'Profesores/destroy/$1';

// ---- Novedades ----
$route['api/novedades/config']['GET']          = 'Novedades/config';
$route['api/novedades']['GET']                 = 'Novedades/index';
$route['api/novedades']['POST']                = 'Novedades/store';
$route['api/novedades/(:num)']['GET']          = 'Novedades/show/$1';
$route['api/novedades/estudiante/(:any)']['GET'] = 'Novedades/estudiante/$1';
$route['api/novedades/(:num)']['DELETE']       = 'Novedades/destroy/$1';

$route['api/reportes/monitor_rendimiento']['GET'] = 'Reportes/monitor_rendimiento';
$route['api/reportes/detalle_curso/(:num)']['GET'] = 'Reportes/detalle_curso/$1';
$route['api/reportes/stats_dashboard']['GET'] = 'Reportes/stats_dashboard';
$route['api/reportes/licencias_monitoreo']['GET'] = 'Reportes/licencias_monitoreo';
$route['api/reportes/retrasos_stats']['GET'] = 'Reportes/retrasos_stats';
$route['api/reportes/consolidado']['GET'] = 'Reportes/consolidado_mensual';
$route['api/reportes/historial_retrasos/(:num)']['GET'] = 'Reportes/historial_retrasos/$1';
$route['api/reportes/monitor_accesos_padres']['GET'] = 'Reportes/monitor_accesos_padres';

$route['api/permisos/perfiles']['GET'] = 'Permisos/perfiles';
$route['api/permisos/perfiles']['POST'] = 'Permisos/perfiles';
$route['api/permisos/perfiles/(:num)']['PUT'] = 'Permisos/perfil/$1';
$route['api/permisos/perfiles/(:num)']['DELETE'] = 'Permisos/perfil/$1';

$route['api/permisos/menus']['GET'] = 'Permisos/menus';

$route['api/permisos/asignar/(:num)']['GET'] = 'Permisos/asignar/$1';
$route['api/permisos/asignar/(:num)']['POST'] = 'Permisos/asignar/$1';
$route['api/permisos/asignar/(:num)']['PUT'] = 'Permisos/asignar/$1';

$route['api/calendario']['GET']    = 'Calendario/index';
$route['api/calendario']['POST']   = 'Calendario/save';
$route['api/calendario/(:num)']['DELETE'] = 'Calendario/delete/$1';
