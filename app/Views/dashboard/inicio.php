<?php
/**
 * VISTA: panel de inicio provisional (se reemplaza más adelante en esta fase).
 * Solo contiene lo propio de esta pantalla: el layout "main" pone la barra superior.
 * Variables: $usuario (id, nombre, usuario, rol) y $modulos.
 */
?>
<h1 class="titulo-pagina">Bienvenido, <?= htmlspecialchars($usuario['nombre']) ?></h1>
<p class="text-secondary mb-4">
    Sesión iniciada con el rol <strong><?= htmlspecialchars($usuario['rol']) ?></strong>.
    Estos son los módulos que te corresponden:
</p>

<div class="row g-3 g-md-4">
    <?php foreach ($modulos as $m): ?>
        <div class="col-12 col-sm-6 col-lg-3">
            <article class="card-dubai h-100">
                <i class="bi <?= htmlspecialchars($m['icono']) ?> card-icono" aria-hidden="true"></i>
                <h2 class="card-titulo"><?= htmlspecialchars($m['nombre']) ?></h2>
                <p class="card-detalle"><?= htmlspecialchars($m['detalle']) ?></p>
                <span class="etiqueta-pronto">Próximamente</span>
            </article>
        </div>
    <?php endforeach; ?>
</div>