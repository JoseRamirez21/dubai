<?php
/**
 * VISTA: pantalla de login. Solo muestra HTML.
 * Variables que llegan del controlador: $titulo y $alerta (o null).
 */

?>
<main class="login-page">
    <section class="login-card" aria-labelledby="login-titulo">

        <div class="text-center mb-4">
            <i class="bi bi-gem brand-icon" aria-hidden="true"></i>
            <h1 id="login-titulo" class="brand-title">DUBAI</h1>
            <p class="brand-subtitle">Nightclub Manager</p>
        </div>

        <?php if ($alerta): ?>
            <!-- role="alert": los lectores de pantalla anuncian el mensaje -->
            <div class="alert alert-<?= htmlspecialchars($alerta['tipo']) ?> dubai-alert" role="alert">
                <?= htmlspecialchars($alerta['texto']) ?>
            </div>
        <?php endif; ?>

        <!-- novalidate: desactiva los globos del navegador para usar los mensajes propios.
             needs-validation: lo detecta app.js para validar antes de enviar. -->
        <form method="post" action="<?= BASE_URL ?>/auth/autenticar" class="needs-validation" novalidate>
            <?= Csrf::campo() ?>

            <div class="mb-3">
                <label for="usuario" class="form-label">Usuario</label>
                <div class="input-group has-validation">
                    <span class="input-group-text"><i class="bi bi-person" aria-hidden="true"></i></span>
                    <input type="text" id="usuario" name="usuario" class="form-control"
                           required maxlength="50" autocomplete="username" autofocus data-trim>
                    <div class="invalid-feedback">Escribe tu usuario.</div>
                </div>
            </div>

            <div class="mb-4">
                <label for="password" class="form-label">Contraseña</label>
                <div class="input-group has-validation">
                    <span class="input-group-text"><i class="bi bi-lock" aria-hidden="true"></i></span>
                    <input type="password" id="password" name="password" class="form-control"
                           required maxlength="255" autocomplete="current-password">
                    <button type="button" class="btn btn-toggle" data-toggle-password="#password"
                            aria-label="Mostrar contraseña" aria-pressed="false">
                        <i class="bi bi-eye" aria-hidden="true"></i>
                    </button>
                    <div class="invalid-feedback">Escribe tu contraseña.</div>
                </div>
            </div>

            <button type="submit" class="btn btn-dubai w-100 py-2">Ingresar</button>
        </form>

        <p class="login-nota">Acceso solo para personal autorizado</p>
    </section>
</main>
