<?php
/**
 * VISTA: cuentas abiertas (por mesa o en barra). Mesero y cajero pueden
 * abrir cuentas y agregar productos; solo cajero (y administrador) ven el
 * botón "Cobrar". Variables: $ventas, $mesas, $mesasConCuenta, $productos,
 * $esCajero, $toast.
 */
?>
<div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
    <h1 class="titulo-pagina mb-0">Ventas</h1>
    <button type="button" class="btn btn-dubai" id="btnAbrirCuenta">
        <i class="bi bi-plus-lg" aria-hidden="true"></i> Abrir cuenta
    </button>
</div>

<?php if (empty($ventas)): ?>
    <div class="card-dubai">
        <div class="estado-vacio">
            <i class="bi bi-cup-straw" aria-hidden="true"></i>
            <p>No hay cuentas abiertas por ahora.</p>
        </div>
    </div>
<?php else: ?>
    <div class="row g-3 g-md-4">
        <?php foreach ($ventas as $v): ?>
            <div class="col-12 col-md-6 col-lg-4">
                <article class="card-dubai cuenta-card h-100">
                    <div class="cuenta-encabezado">
                        <h2 class="card-titulo mb-0">
                            <?= $v['mesa_numero'] ? 'Mesa #' . (int) $v['mesa_numero'] : 'Barra' ?>
                        </h2>
                        <?php if ($v['mesa_numero']): ?>
                            <span class="estado-badge estado-warn text-capitalize"><?= htmlspecialchars($v['mesa_zona']) ?></span>
                        <?php endif; ?>
                    </div>

                    <?php if (empty($v['detalle'])): ?>
                        <p class="text-secondary small mb-3">Todavía no tiene productos.</p>
                    <?php else: ?>
                        <ul class="cuenta-items">
                            <?php foreach ($v['detalle'] as $d): ?>
                                <li>
                                    <span><?= htmlspecialchars($d['producto_nombre']) ?> ×<?= (int) $d['cantidad'] ?></span>
                                    <span><?= CURRENCY ?> <?= number_format((float) $d['subtotal'], 2) ?></span>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>

                    <div class="cuenta-total">
                        <span>Total</span>
                        <span><?= CURRENCY ?> <?= number_format((float) $v['total'], 2) ?></span>
                    </div>

                    <div class="d-flex flex-wrap gap-2">
                        <button type="button" class="btn btn-dubai-outline btn-sm"
                                data-accion="agregar" data-id="<?= (int) $v['id'] ?>"
                                data-nombre="<?= $v['mesa_numero'] ? 'Mesa #' . (int) $v['mesa_numero'] : 'Barra' ?>">
                            <i class="bi bi-plus-circle" aria-hidden="true"></i> Agregar producto
                        </button>

                        <?php if ($esCajero): ?>
                            <button type="button" class="btn btn-dubai btn-sm"
                                    data-accion="cobrar" data-id="<?= (int) $v['id'] ?>"
                                    data-total="<?= number_format((float) $v['total'], 2) ?>">
                                <i class="bi bi-cash-coin" aria-hidden="true"></i> Cobrar
                            </button>
                        <?php endif; ?>
                    </div>
                </article>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<!-- ---------- Modal: abrir cuenta ---------- -->
<div class="modal fade" id="modalAbrir" tabindex="-1" aria-labelledby="modalAbrirTitulo" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="post" action="<?= BASE_URL ?>/ventas/abrir" id="formAbrir">
                <?= Csrf::campo() ?>
                <div class="modal-header">
                    <h5 class="modal-title" id="modalAbrirTitulo">Abrir cuenta</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    <label for="campoMesaAbrir" class="form-label">¿Mesa o barra?</label>
                    <select class="form-select" id="campoMesaAbrir" name="mesa_id" required>
                        <option value="0">Barra (sin mesa)</option>
                        <?php foreach ($mesas as $m): ?>
                            <option value="<?= (int) $m['id'] ?>"
                                <?= in_array($m['id'], $mesasConCuenta, true) ? 'disabled' : '' ?>>
                                Mesa #<?= (int) $m['numero'] ?> (<?= htmlspecialchars($m['zona']) ?>)
                                <?= in_array($m['id'], $mesasConCuenta, true) ? ' — ya tiene cuenta abierta' : '' ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-dubai-outline btn-sm" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-dubai btn-sm">Abrir</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ---------- Modal: agregar producto ---------- -->
<div class="modal fade" id="modalAgregar" tabindex="-1" aria-labelledby="modalAgregarTitulo" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="post" id="formAgregar" class="needs-validation" novalidate>
                <?= Csrf::campo() ?>
                <div class="modal-header">
                    <h5 class="modal-title" id="modalAgregarTitulo">Agregar producto</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    <p class="text-secondary mb-3">Cuenta: <strong id="agregarCuentaTexto"></strong></p>

                    <div class="mb-3">
                        <label for="campoProducto" class="form-label">Producto</label>
                        <select class="form-select" id="campoProducto" name="producto_id" required>
                            <option value="" selected disabled>Elige un producto</option>
                            <?php foreach ($productos as $p): ?>
                                <option value="<?= (int) $p['id'] ?>">
                                    <?= htmlspecialchars($p['nombre']) ?> — <?= CURRENCY ?> <?= number_format((float) $p['precio'], 2) ?>
                                    (stock: <?= (int) $p['stock'] ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <div class="invalid-feedback">Elige un producto.</div>
                    </div>

                    <div class="mb-3">
                        <label for="campoCantidad" class="form-label">Cantidad</label>
                        <input type="number" class="form-control" id="campoCantidad" name="cantidad"
                               required min="1" step="1" value="1">
                        <div class="invalid-feedback">Al menos 1.</div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-dubai-outline btn-sm" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-dubai btn-sm">Agregar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php if ($esCajero): ?>
<!-- ---------- Modal: cobrar (solo cajero/administrador) ---------- -->
<div class="modal fade" id="modalCobrar" tabindex="-1" aria-labelledby="modalCobrarTitulo" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="post" id="formCobrar">
                <?= Csrf::campo() ?>
                <div class="modal-header">
                    <h5 class="modal-title" id="modalCobrarTitulo">Cobrar</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    <p class="mb-3">Total a cobrar: <strong id="cobrarTotalTexto" class="text-warning"></strong></p>

                    <label for="campoMetodoPago" class="form-label">Método de pago</label>
                    <select class="form-select" id="campoMetodoPago" name="metodo_pago" required>
                        <option value="efectivo">Efectivo</option>
                        <option value="tarjeta">Tarjeta</option>
                        <option value="yape">Yape</option>
                        <option value="plin">Plin</option>
                    </select>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-dubai-outline btn-sm" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-dubai btn-sm">Confirmar cobro</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>

<script>
document.addEventListener('DOMContentLoaded', function () {
    <?php if ($toast): ?>
        Dubai.toast(<?= json_encode($toast['tipo']) ?>, <?= json_encode($toast['texto']) ?>);
    <?php endif; ?>

    var urlBase = '<?= BASE_URL ?>';

    // ---- Abrir cuenta ----
    var modalAbrir = new bootstrap.Modal(document.getElementById('modalAbrir'));
    document.getElementById('btnAbrirCuenta').addEventListener('click', function () {
        document.getElementById('formAbrir').reset();
        modalAbrir.show();
    });

    // ---- Agregar producto ----
    var modalAgregar = new bootstrap.Modal(document.getElementById('modalAgregar'));
    var formAgregar   = document.getElementById('formAgregar');
    document.querySelectorAll('[data-accion="agregar"]').forEach(function (boton) {
        boton.addEventListener('click', function () {
            formAgregar.reset();
            formAgregar.classList.remove('was-validated');
            formAgregar.action = urlBase + '/ventas/agregarProducto/' + boton.dataset.id;
            document.getElementById('agregarCuentaTexto').textContent = boton.dataset.nombre;
            modalAgregar.show();
        });
    });

    // ---- Cobrar (solo si el rol lo permite: el modal ni existe si no) ----
    var elCobrar = document.getElementById('modalCobrar');
    if (elCobrar) {
        var modalCobrar = new bootstrap.Modal(elCobrar);
        var formCobrar   = document.getElementById('formCobrar');
        document.querySelectorAll('[data-accion="cobrar"]').forEach(function (boton) {
            boton.addEventListener('click', function () {
                formCobrar.reset();
                formCobrar.action = urlBase + '/ventas/cobrar/' + boton.dataset.id;
                document.getElementById('cobrarTotalTexto').textContent = '<?= CURRENCY ?> ' + boton.dataset.total;
                modalCobrar.show();
            });
        });
    }
});
</script>