<?php
/**
 * LAYOUT "auth": página limpia, sin menú ni barra superior.
 * Se usa en el login y en las páginas de error.
 * Variables: $titulo y $contenido (la vista ya capturada).
 */
require VIEW_PATH . '/layouts/header.php';
echo $contenido;
require VIEW_PATH . '/layouts/footer.php';