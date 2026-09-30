<?php
/**
 * FRONT CONTROLLER (punto de entrada único)
 *
 * TODAS las peticiones del sistema pasan por este archivo.
 * Aquí se prepara el entorno; en el siguiente paso llamaremos al Router.
 */

// 1. Cargar la configuración
require_once dirname(__DIR__) . '/config/config.php';

// 2. Zona horaria del negocio (afecta a date() y a las fechas de ventas)
date_default_timezone_set(TIMEZONE);

// 3. Manejo de errores:
//    en desarrollo se muestran; en producción se ocultan y solo se registran,
//    para no exponer rutas ni datos sensibles a un atacante.
if (APP_DEBUG) {
    ini_set('display_errors', '1');
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', '0');
    ini_set('log_errors', '1');
    error_reporting(E_ALL);
}

// 4. AUTOLOADER: cuando el código usa una clase (ej. new Router),
//    PHP llama a esta función, que la busca en las carpetas indicadas
//    y la carga. Así no escribimos un "require" por cada clase.
spl_autoload_register(function (string $clase): void {
    $carpetas = [
        CORE_PATH,                   // Router, Controller, Model, Database...
        APP_PATH . '/Controllers',   // AuthController, etc.
        APP_PATH . '/Models',        // Usuario, Producto, etc.
    ];

    foreach ($carpetas as $carpeta) {
        $archivo = $carpeta . '/' . $clase . '.php';
        if (is_file($archivo)) {
            require_once $archivo;
            return;
        }
    }
});

// 5. ROUTER: interpreta la URL y ejecuta el controlador correspondiente
$router = new Router();
$router->despachar();