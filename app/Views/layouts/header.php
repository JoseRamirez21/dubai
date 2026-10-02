<?php
/**
 * PARCIAL: cabecera HTML común a todas las pantallas.
 * Variable esperada: $titulo (la envía el controlador).
 * Bootstrap, iconos y estilos se cargan desde archivos LOCALES (sin internet).
 */
?>
<!DOCTYPE html>
<html lang="es" data-bs-theme="dark">
<head>
    <meta charset="UTF-8">
    <!-- viewport: hace que la página se adapte al ancho real del celular -->
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#0B0B0D">
    <title><?= htmlspecialchars($titulo) ?> | <?= htmlspecialchars(APP_NAME) ?></title>

    <link rel="stylesheet" href="<?= BASE_URL ?>/vendor/bootstrap/bootstrap.min.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/vendor/bootstrap-icons/bootstrap-icons.min.css">
    <!-- Nuestro CSS va al final para poder personalizar Bootstrap y SweetAlert2 -->
    <link rel="stylesheet" href="<?= BASE_URL ?>/css/dubai.css">

    <!-- SweetAlert2 inyecta su propio <style>; por eso nuestro CSS va DESPUÉS en el <head> -->
    <script src="<?= BASE_URL ?>/vendor/sweetalert2/sweetalert2.all.min.js"></script>
</head>
<body>