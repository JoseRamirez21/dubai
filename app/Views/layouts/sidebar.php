<?php
/**
 * PARCIAL: sidebar (menú lateral). Se escribe UNA vez y aparece en todo
 * el sistema. El contenido cambia solo según el rol del usuario conectado.
 *
 * $rutaActual: primer segmento de la URL (ej. "dashboard"), para resaltar
 * la opción activa con la clase "activo".
 */
$rutaActual  = strtolower(explode('/', trim($_GET['url'] ?? '', '/'))[0] ?? '');
$opcionesMenu = Menu::paraRol(Auth::rol());
?>
<!-- Fondo oscuro detrás del sidebar en celular; clic ahí = cerrar el menú -->
<div class="sidebar-overlay" id="sidebarOverlay"></div>

<aside class="sidebar" id="sidebarDubai" aria-label="Menú principal">
    <div class="sidebar-marca">
        <i class="bi bi-gem" aria-hidden="true"></i>
        <span>DUBAI</span>
    </div>

    <nav class="sidebar-nav">
        <?php foreach ($opcionesMenu as $opcion): ?>
            <?php if ($opcion['disponible']): ?>
                <a href="<?= BASE_URL ?>/<?= htmlspecialchars($opcion['ruta']) ?>"
                   class="sidebar-link <?= $opcion['ruta'] === $rutaActual ? 'activo' : '' ?>"
                   <?= $opcion['ruta'] === $rutaActual ? 'aria-current="page"' : '' ?>>
                    <i class="bi <?= htmlspecialchars($opcion['icono']) ?>" aria-hidden="true"></i>
                    <span><?= htmlspecialchars($opcion['nombre']) ?></span>
                </a>
            <?php else: ?>
                <!-- Módulo de una fase futura: se ve, pero no se puede abrir todavía -->
                <span class="sidebar-link deshabilitado" aria-disabled="true">
                    <i class="bi <?= htmlspecialchars($opcion['icono']) ?>" aria-hidden="true"></i>
                    <span><?= htmlspecialchars($opcion['nombre']) ?></span>
                    <span class="sidebar-pronto">Pronto</span>
                </span>
            <?php endif; ?>
        <?php endforeach; ?>
    </nav>
</aside>