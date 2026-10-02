<?php
/**
 * PARCIAL: barra superior. En celular muestra el botón de hamburguesa
 * para abrir el sidebar; el nombre, el rol y "Salir" siempre están.
 */
$usuarioActual = Auth::usuario();
?>
<header class="topbar">
    <div class="topbar-inner">
        <!-- Solo visible en pantallas angostas (el sidebar ya se ve fijo en pantallas grandes) -->
        <button type="button" class="btn-hamburguesa d-lg-none" id="btnAbrirMenu"
                aria-controls="sidebarDubai" aria-expanded="false" aria-label="Abrir menú">
            <i class="bi bi-list" aria-hidden="true"></i>
        </button>

        <div class="d-flex align-items-center gap-3 ms-auto">
            <span class="topbar-usuario d-none d-sm-inline">
                <?= htmlspecialchars($usuarioActual['nombre']) ?>
            </span>
            <span class="badge-rol"><?= htmlspecialchars($usuarioActual['rol']) ?></span>

            <!-- Cerrar sesión: formulario POST con token CSRF -->
            <form method="post" action="<?= BASE_URL ?>/auth/salir" class="m-0">
                <?= Csrf::campo() ?>
                <button type="submit" class="btn btn-dubai-outline btn-sm">
                    <i class="bi bi-box-arrow-right" aria-hidden="true"></i>
                    <span class="d-none d-sm-inline">Salir</span>
                </button>
            </form>
        </div>
    </div>
</header>