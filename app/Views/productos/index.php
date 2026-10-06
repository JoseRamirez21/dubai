<?php
/**
 * VISTA: catálogo de productos. Una sola pantalla: la tabla, más un MODAL
 * que se reutiliza tanto para "Nuevo producto" como para "Editar producto".
 * Variables: $productos, $categorias, $toast (o null).
 */
?>
<div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
    <h1 class="titulo-pagina mb-0">Productos</h1>
    <button type="button" class="btn btn-dubai" id="btnNuevoProducto">
        <i class="bi bi-plus-lg" aria-hidden="true"></i> Nuevo producto
    </button>
</div>

<section class="card-dubai">
    <div class="tabla-toolbar">
        <input type="search" class="form-control tabla-buscador"
               placeholder="Buscar producto o categoría..."
               data-tabla-buscar="#tablaProductos">
    </div>

    <div class="tabla-dubai-wrap">
        <table class="tabla-dubai" id="tablaProductos">
            <thead>
                <tr>
                    <th>Nombre</th>
                    <th>Categoría</th>
                    <th>Precio</th>
                    <th>Stock</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($productos as $p): ?>
                    <?php $stockBajo = (int) $p['stock'] <= (int) $p['stock_minimo']; ?>
                    <tr>
                        <td><?= htmlspecialchars($p['nombre']) ?></td>
                        <td class="text-capitalize"><?= htmlspecialchars($p['categoria']) ?></td>
                        <td><?= CURRENCY ?> <?= number_format((float) $p['precio'], 2) ?></td>
                        <td>
                            <?= (int) $p['stock'] ?>
                            <?php if ($stockBajo): ?>
                                <i class="bi bi-exclamation-triangle-fill text-warning ms-1"
                                   title="Stock al mínimo o por debajo" aria-hidden="true"></i>
                            <?php endif; ?>
                        </td>
                        <td>
                            <span class="estado-badge <?= $p['estado'] === 'activo' ? 'estado-ok' : 'estado-danger' ?>">
                                <?= htmlspecialchars($p['estado']) ?>
                            </span>
                        </td>
                        <td>
                            <div class="d-flex gap-2">
                                <button type="button" class="btn btn-dubai-outline btn-sm"
                                        data-accion="editar"
                                        data-id="<?= (int) $p['id'] ?>"
                                        data-nombre="<?= htmlspecialchars($p['nombre']) ?>"
                                        data-categoria="<?= htmlspecialchars($p['categoria']) ?>"
                                        data-precio="<?= htmlspecialchars((string) $p['precio']) ?>"
                                        data-stock="<?= (int) $p['stock'] ?>"
                                        data-stock-minimo="<?= (int) $p['stock_minimo'] ?>"
                                        aria-label="Editar <?= htmlspecialchars($p['nombre']) ?>">
                                    <i class="bi bi-pencil" aria-hidden="true"></i>
                                </button>

                                <?php if ($p['estado'] === 'activo'): ?>
                                    <button type="button" class="btn btn-dubai-outline btn-sm"
                                            data-accion="estado" data-id="<?= (int) $p['id'] ?>"
                                            data-nuevo-estado="inactivo"
                                            data-mensaje="¿Desactivar &quot;<?= htmlspecialchars($p['nombre'], ENT_QUOTES) ?>&quot;?"
                                            aria-label="Desactivar <?= htmlspecialchars($p['nombre']) ?>">
                                        <i class="bi bi-slash-circle" aria-hidden="true"></i>
                                    </button>
                                <?php else: ?>
                                    <button type="button" class="btn btn-dubai-outline btn-sm"
                                            data-accion="estado" data-id="<?= (int) $p['id'] ?>"
                                            data-nuevo-estado="activo"
                                            data-mensaje="¿Activar &quot;<?= htmlspecialchars($p['nombre'], ENT_QUOTES) ?>&quot;?"
                                            aria-label="Activar <?= htmlspecialchars($p['nombre']) ?>">
                                        <i class="bi bi-check-circle" aria-hidden="true"></i>
                                    </button>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <div class="tabla-paginacion" data-tabla-paginacion="#tablaProductos"></div>
    </div>
</section>

<!-- ---------- Modal reutilizado: Nuevo producto / Editar producto ---------- -->
<div class="modal fade" id="modalProducto" tabindex="-1" aria-labelledby="modalProductoTitulo" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="post" id="formProducto" class="needs-validation" novalidate>
                <?= Csrf::campo() ?>
                <div class="modal-header">
                    <h5 class="modal-title" id="modalProductoTitulo">Nuevo producto</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="campoNombre" class="form-label">Nombre</label>
                        <input type="text" class="form-control" id="campoNombre" name="nombre"
                               required maxlength="100" data-trim>
                        <div class="invalid-feedback">Escribe el nombre del producto.</div>
                    </div>

                    <div class="mb-3">
                        <label for="campoCategoria" class="form-label">Categoría</label>
                        <select class="form-select" id="campoCategoria" name="categoria" required>
                            <option value="" selected disabled>Elige una categoría</option>
                            <?php foreach ($categorias as $c): ?>
                                <option value="<?= htmlspecialchars($c) ?>" class="text-capitalize">
                                    <?= htmlspecialchars(ucfirst($c)) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <div class="invalid-feedback">Elige una categoría.</div>
                    </div>

                    <div class="row g-3">
                        <div class="col-12 col-sm-4">
                            <label for="campoPrecio" class="form-label">Precio (<?= CURRENCY ?>)</label>
                            <input type="number" class="form-control" id="campoPrecio" name="precio"
                                   required min="0.01" max="9999.99" step="0.01">
                            <div class="invalid-feedback">Mayor a 0.</div>
                        </div>
                        <div class="col-6 col-sm-4">
                            <label for="campoStock" class="form-label">Stock</label>
                            <input type="number" class="form-control" id="campoStock" name="stock"
                                   required min="0" step="1">
                            <div class="invalid-feedback">0 o más.</div>
                        </div>
                        <div class="col-6 col-sm-4">
                            <label for="campoStockMinimo" class="form-label">Stock mínimo</label>
                            <input type="number" class="form-control" id="campoStockMinimo" name="stock_minimo"
                                   required min="0" step="1">
                            <div class="invalid-feedback">0 o más.</div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-dubai-outline btn-sm" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-dubai btn-sm">Guardar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Formulario oculto reutilizado para activar/desactivar (no necesita campos, solo el token) -->
<form id="formEstado" method="post" class="d-none"><?= Csrf::campo() ?></form>

<script>
document.addEventListener('DOMContentLoaded', function () {
    Dubai.tabla('#tablaProductos', { porPagina: 8 });

    <?php if ($toast): ?>
        Dubai.toast(<?= json_encode($toast['tipo']) ?>, <?= json_encode($toast['texto']) ?>);
    <?php endif; ?>

    var modalEl    = document.getElementById('modalProducto');
    var modal      = new bootstrap.Modal(modalEl);
    var form       = document.getElementById('formProducto');
    var tituloModal = document.getElementById('modalProductoTitulo');
    var urlBase     = '<?= BASE_URL ?>';

    // "Nuevo producto": vacía el formulario y apunta a /productos/crear
    document.getElementById('btnNuevoProducto').addEventListener('click', function () {
        form.reset();
        form.classList.remove('was-validated');
        form.action = urlBase + '/productos/crear';
        tituloModal.textContent = 'Nuevo producto';
        modal.show();
    });

    // "Editar": llena el formulario con los data-* del botón y apunta a /productos/actualizar/{id}
    document.querySelectorAll('[data-accion="editar"]').forEach(function (boton) {
        boton.addEventListener('click', function () {
            form.reset();
            form.classList.remove('was-validated');
            form.action = urlBase + '/productos/actualizar/' + boton.dataset.id;
            document.getElementById('campoNombre').value = boton.dataset.nombre;
            document.getElementById('campoCategoria').value = boton.dataset.categoria;
            document.getElementById('campoPrecio').value = boton.dataset.precio;
            document.getElementById('campoStock').value = boton.dataset.stock;
            document.getElementById('campoStockMinimo').value = boton.dataset.stockMinimo;
            tituloModal.textContent = 'Editar producto';
            modal.show();
        });
    });

    // Activar / Desactivar: confirma con SweetAlert2 y envía el formulario oculto
    document.querySelectorAll('[data-accion="estado"]').forEach(function (boton) {
        boton.addEventListener('click', function () {
            Dubai.confirmar(boton.dataset.mensaje, 'Sí, continuar').then(function (confirmo) {
                if (!confirmo) { return; }

                var formEstado = document.getElementById('formEstado');
                formEstado.action = urlBase + '/productos/cambiarEstado/'
                    + boton.dataset.id + '/' + boton.dataset.nuevoEstado;
                formEstado.submit();
            });
        });
    });
});
</script>