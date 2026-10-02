<?php
/**
 * VISTA: página de error. Variables: $codigo, $titulo y $mensaje.
 * Reutiliza el contenedor centrado y la tarjeta del login.
 */
?>
<main class="login-page">
    <section class="login-card error-card">
        <i class="bi bi-gem brand-icon" aria-hidden="true"></i>
        <p class="error-codigo"><?= (int) $codigo ?></p>
        <h1 class="error-titulo"><?= htmlspecialchars($titulo) ?></h1>
        <p class="error-mensaje"><?= htmlspecialchars($mensaje) ?></p>
        <a href="<?= BASE_URL ?>/dashboard" class="btn btn-dubai px-4">
            <i class="bi bi-house-door" aria-hidden="true"></i> Volver al inicio
        </a>
    </section>
</main>