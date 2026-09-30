<?php
/**
 * DUBAI – Nightclub Manager
 * Archivo de configuración central.
 *
 * ¿Por qué existe?
 * Para que los datos que pueden cambiar (base de datos, rutas, zona horaria)
 * estén en UN solo lugar y nunca dentro de las clases.
 */

// ---------- Datos generales de la aplicación ----------
define('APP_NAME', 'DUBAI – Nightclub Manager');
define('APP_DEBUG', true);            // true = muestra errores (desarrollo). En producción: false
define('TIMEZONE', 'America/Lima');   // Zona horaria del negocio
define('CURRENCY', 'S/');             // Símbolo de la moneda (Soles)

// ---------- Rutas ----------
// BASE_URL: parte de la dirección web donde vive el proyecto.
// Si tu carpeta no se llama "dubai", cámbiala aquí.
define('BASE_URL', '/dubai/public');

// ROOT_PATH: ruta física del proyecto en el disco (la calcula PHP sola).
define('ROOT_PATH', dirname(__DIR__));
define('APP_PATH',  ROOT_PATH . '/app');
define('CORE_PATH', ROOT_PATH . '/core');
define('VIEW_PATH', APP_PATH . '/Views');

// ---------- Base de datos (usados solo por la clase Database) ----------
define('DB_HOST',    'localhost');
define('DB_NAME',    'dubai_db');
define('DB_USER',    'root');
define('DB_PASS',    '');            // XAMPP viene sin contraseña por defecto
define('DB_CHARSET', 'utf8mb4');

// ---------- Sesión ----------
define('SESSION_LIFETIME', 1800);    // 30 minutos de inactividad (en segundos)


// ---------- Router: qué se ejecuta cuando la URL viene vacía ----------
// Temporal: en el Paso 8 lo cambiaremos a 'Auth' y 'login'.
define('DEFAULT_CONTROLLER', 'Prueba');
define('DEFAULT_METHOD',     'index');