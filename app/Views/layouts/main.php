<?php
/**
 * LAYOUT "main": estructura del sistema con sesión iniciada.
 * Ahora con sidebar + barra superior + contenido, armado con parciales.
 * Variables: $titulo y $contenido (la vista ya capturada).
 */
require VIEW_PATH . '/layouts/header.php';
?>
<div class="app-shell">
    <?php require VIEW_PATH . '/layouts/sidebar.php'; ?>

    <div class="app-content">
        <?php require VIEW_PATH . '/layouts/topbar.php'; ?>
        <main class="container-fluid py-4 py-md-5">
            <?= $contenido ?>
        </main>
    </div>
</div>
<?php require VIEW_PATH . '/layouts/footer.php'; ?>