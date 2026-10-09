<?php
/**
 * VISTA: ticket de una entrada, listo para imprimir.
 * Variables: $entrada (tabla entradas) y $evento (tabla eventos).
 */
?>
<main class="login-page">
    <section class="login-card ticket-card">
        <div class="text-center mb-2">
            <i class="bi bi-gem brand-icon" aria-hidden="true"></i>
            <p class="brand-subtitle mb-0">Entrada</p>
        </div>

        <h1 class="ticket-evento"><?= htmlspecialchars($evento['nombre']) ?></h1>
        <p class="text-center text-secondary mb-0">
            <?= date('d/m/Y', strtotime($evento['fecha'])) ?> ·
            <?= substr($evento['hora_inicio'], 0, 5) ?> ·
            <?= htmlspecialchars($evento['dj_artista']) ?>
        </p>

        <hr class="ticket-separador">

        <div class="ticket-filas">
            <div class="ticket-fila">
                <span>Cliente</span>
                <span><?= htmlspecialchars($entrada['cliente_nombre']) ?></span>
            </div>
            <div class="ticket-fila">
                <span>Tipo</span>
                <span class="text-capitalize"><?= htmlspecialchars($entrada['tipo']) ?></span>
            </div>
            <div class="ticket-fila">
                <span>Precio</span>
                <span><?= CURRENCY ?> <?= number_format((float) $entrada['precio'], 2) ?></span>
            </div>
            <div class="ticket-fila">
                <span>Fecha de venta</span>
                <span><?= date('d/m/Y H:i', strtotime($entrada['fecha_venta'])) ?></span>
            </div>
        </div>

        <div class="ticket-codigo">
            <span class="ticket-codigo-valor"><?= htmlspecialchars($entrada['codigo']) ?></span>
        </div>

        <div class="d-flex gap-2 no-imprimir">
            <a href="<?= BASE_URL ?>/eventos" class="btn btn-dubai-outline w-50">
                <i class="bi bi-arrow-left" aria-hidden="true"></i> Volver
            </a>
            <button type="button" class="btn btn-dubai w-50" onclick="window.print()">
                <i class="bi bi-printer" aria-hidden="true"></i> Imprimir
            </button>
        </div>
    </section>
</main>