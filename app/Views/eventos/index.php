<?php
/**
 * VISTA: lista de eventos con su aforo. Administrador puede crear, editar y
 * cambiar el estado; cajero solo consulta (necesita ver el aforo antes de
 * vender entradas, en el Paso 4). Variables: $eventos, $esAdmin, $toast.
 */
?>
<div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
    <h1 class="titulo-pagina mb-0">Eventos</h1>
    <?php if ($esAdmin): ?>
        <button type="button" class="btn btn-dubai" id="btnNuevoEvento">
            <i class="bi bi-plus-lg" aria-hidden="true"></i> Nuevo evento
        </button>
    <?php endif; ?>
</div>

<div class="row g-3 g-md-4">
    <?php foreach ($eventos as $e): ?>
        <?php
            $vendidas  = (int) $e['vendidas'];
            $aforo     = (int) $e['aforo'];
            $porcentaje = $aforo > 0 ? min(100, round($vendidas / $aforo * 100)) : 0;
            $claseBarra = 'aforo-relleno';
            if ($porcentaje >= 100) { $claseBarra .= ' aforo-lleno'; }
            elseif ($porcentaje >= 80) { $claseBarra .= ' aforo-ambar'; }

            $colorEstado = ['programado' => 'estado-warn', 'en_curso' => 'estado-ok', 'cerrado' => 'estado-danger'];
        ?>
        <div class="col-12 col-lg-6">
            <article class="card-dubai h-100">
                <div class="d-flex justify-content-between align-items-start gap-2 mb-2">
                    <h2 class="card-titulo mb-0"><?= htmlspecialchars($e['nombre']) ?></h2>
                    <span class="estado-badge <?= $colorEstado[$e['estado']] ?? 'estado-ok' ?>">
                        <?= htmlspecialchars(str_replace('_', ' ', $e['estado'])) ?>
                    </span>
                </div>

                <p class="card-detalle mb-1">
                    <i class="bi bi-calendar-event" aria-hidden="true"></i>
                    <?= date('d/m/Y', strtotime($e['fecha'])) ?> ·
                    <?= substr($e['hora_inicio'], 0, 5) ?> ·
                    <?= htmlspecialchars($e['dj_artista']) ?>
                </p>
                <p class="card-detalle mb-3">
                    Entrada: <?= CURRENCY ?> <?= number_format((float) $e['precio_entrada'], 2) ?>
                </p>

                <div class="aforo-texto">
                    <span>Aforo</span>
                    <span><?= $vendidas ?> / <?= $aforo ?> (<?= $porcentaje ?>%)</span>
                </div>
                <div class="aforo-barra mb-3">
                    <div class="<?= $claseBarra ?>" style="width: <?= $porcentaje ?>%"></div>
                </div>

                <?php
                    $agotado     = $aforo > 0 && $vendidas >= $aforo;
                    $cerrado     = $e['estado'] === 'cerrado';
                    $puedeVender = !$agotado && !$cerrado;
                ?>
                <button type="button" class="btn btn-dubai btn-sm w-100 mb-2"
                        data-accion="vender"
                        data-id="<?= (int) $e['id'] ?>"
                        data-nombre-evento="<?= htmlspecialchars($e['nombre']) ?>"
                        <?= $puedeVender ? '' : 'disabled' ?>>
                    <i class="bi bi-ticket-perforated" aria-hidden="true"></i> Vender entrada
                </button>
                <?php if (!$puedeVender): ?>
                    <p class="text-secondary small mb-2">
                        <?= $cerrado ? 'Evento cerrado.' : 'Aforo agotado.' ?>
                    </p>
                <?php endif; ?>

                <?php if ($esAdmin): ?>
                    <div class="d-flex flex-wrap gap-2">
                        <button type="button" class="btn btn-dubai-outline btn-sm"
                                data-accion="editar"
                                data-id="<?= (int) $e['id'] ?>"
                                data-nombre="<?= htmlspecialchars($e['nombre']) ?>"
                                data-descripcion="<?= htmlspecialchars($e['descripcion'] ?? '') ?>"
                                data-fecha="<?= htmlspecialchars($e['fecha']) ?>"
                                data-hora="<?= htmlspecialchars(substr($e['hora_inicio'], 0, 5)) ?>"
                                data-dj="<?= htmlspecialchars($e['dj_artista']) ?>"
                                data-precio="<?= htmlspecialchars((string) $e['precio_entrada']) ?>"
                                data-aforo="<?= $aforo ?>">
                            <i class="bi bi-pencil" aria-hidden="true"></i> Editar
                        </button>

                        <select class="form-select form-select-sm w-auto"
                                data-accion="estado" data-id="<?= (int) $e['id'] ?>"
                                aria-label="Cambiar estado de <?= htmlspecialchars($e['nombre']) ?>">
                            <?php foreach (Evento::ESTADOS as $estadoOpcion): ?>
                                <option value="<?= $estadoOpcion ?>" <?= $estadoOpcion === $e['estado'] ? 'selected' : '' ?>>
                                    <?= ucfirst(str_replace('_', ' ', $estadoOpcion)) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                <?php endif; ?>
            </article>
        </div>
    <?php endforeach; ?>
</div>

<?php if ($esAdmin): ?>
<!-- ---------- Modal reutilizado: Nuevo evento / Editar evento ---------- -->
<div class="modal fade" id="modalEvento" tabindex="-1" aria-labelledby="modalEventoTitulo" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="post" id="formEvento" class="needs-validation" novalidate>
                <?= Csrf::campo() ?>
                <div class="modal-header">
                    <h5 class="modal-title" id="modalEventoTitulo">Nuevo evento</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="campoNombreEv" class="form-label">Nombre</label>
                        <input type="text" class="form-control" id="campoNombreEv" name="nombre"
                               required maxlength="120" data-trim>
                        <div class="invalid-feedback">Escribe el nombre del evento.</div>
                    </div>

                    <div class="mb-3">
                        <label for="campoDescripcionEv" class="form-label">Descripción</label>
                        <textarea class="form-control" id="campoDescripcionEv" name="descripcion"
                                  rows="2" maxlength="1000"></textarea>
                    </div>

                    <div class="row g-3">
                        <div class="col-6">
                            <label for="campoFechaEv" class="form-label">Fecha</label>
                            <input type="date" class="form-control" id="campoFechaEv" name="fecha" required>
                            <div class="invalid-feedback">Elige una fecha.</div>
                        </div>
                        <div class="col-6">
                            <label for="campoHoraEv" class="form-label">Hora de inicio</label>
                            <input type="time" class="form-control" id="campoHoraEv" name="hora_inicio" required>
                            <div class="invalid-feedback">Elige una hora.</div>
                        </div>
                    </div>

                    <div class="mb-3 mt-3">
                        <label for="campoDjEv" class="form-label">DJ / Artista</label>
                        <input type="text" class="form-control" id="campoDjEv" name="dj_artista"
                               required maxlength="100" data-trim>
                        <div class="invalid-feedback">Escribe el DJ o artista.</div>
                    </div>

                    <div class="row g-3">
                        <div class="col-6">
                            <label for="campoPrecioEv" class="form-label">Precio entrada (<?= CURRENCY ?>)</label>
                            <input type="number" class="form-control" id="campoPrecioEv" name="precio_entrada"
                                   required min="0.01" max="9999.99" step="0.01">
                            <div class="invalid-feedback">Mayor a 0.</div>
                        </div>
                        <div class="col-6">
                            <label for="campoAforoEv" class="form-label">Aforo máximo</label>
                            <input type="number" class="form-control" id="campoAforoEv" name="aforo"
                                   required min="1" max="100000" step="1">
                            <div class="invalid-feedback">Al menos 1.</div>
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

<!-- Formulario oculto para cambiar el estado (solo el token) -->
<form id="formEstadoEvento" method="post" class="d-none"><?= Csrf::campo() ?></form>
<?php endif; ?>

<!-- ---------- Modal: vender entrada (administrador y cajero) ---------- -->
<div class="modal fade" id="modalVenta" tabindex="-1" aria-labelledby="modalVentaTitulo" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="post" id="formVenta" class="needs-validation" novalidate>
                <?= Csrf::campo() ?>
                <div class="modal-header">
                    <h5 class="modal-title" id="modalVentaTitulo">Vender entrada</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    <p class="text-secondary mb-3">Evento: <strong id="ventaNombreEvento"></strong></p>

                    <div class="mb-3">
                        <label for="campoCliente" class="form-label">Nombre del cliente</label>
                        <input type="text" class="form-control" id="campoCliente" name="cliente_nombre"
                               required maxlength="100" data-trim>
                        <div class="invalid-feedback">Escribe el nombre del cliente.</div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label d-block">Tipo de entrada</label>
                        <div class="btn-group w-100" role="group" aria-label="Tipo de entrada">
                            <input type="radio" class="btn-check" name="tipo" id="tipoGeneral" value="general" checked required>
                            <label class="btn btn-dubai-outline" for="tipoGeneral">General</label>

                            <input type="radio" class="btn-check" name="tipo" id="tipoVip" value="vip" required>
                            <label class="btn btn-dubai-outline" for="tipoVip">VIP</label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-dubai-outline btn-sm" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-dubai btn-sm">Vender</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    var modalVentaEl = document.getElementById('modalVenta');
    var modalVenta    = new bootstrap.Modal(modalVentaEl);
    var formVenta      = document.getElementById('formVenta');
    var urlBaseVenta    = '<?= BASE_URL ?>';

    document.querySelectorAll('[data-accion="vender"]').forEach(function (boton) {
        boton.addEventListener('click', function () {
            formVenta.reset();
            formVenta.classList.remove('was-validated');
            formVenta.action = urlBaseVenta + '/entradas/vender/' + boton.dataset.id;
            document.getElementById('ventaNombreEvento').textContent = boton.dataset.nombreEvento;
            modalVenta.show();
        });
    });

    <?php if ($toast): ?>
        Dubai.toast(<?= json_encode($toast['tipo']) ?>, <?= json_encode($toast['texto']) ?>);
    <?php endif; ?>

    <?php if ($esAdmin): ?>
        var modalEl     = document.getElementById('modalEvento');
        var modal       = new bootstrap.Modal(modalEl);
        var form        = document.getElementById('formEvento');
        var tituloModal = document.getElementById('modalEventoTitulo');
        var urlBase     = '<?= BASE_URL ?>';

        document.getElementById('btnNuevoEvento').addEventListener('click', function () {
            form.reset();
            form.classList.remove('was-validated');
            form.action = urlBase + '/eventos/crear';
            tituloModal.textContent = 'Nuevo evento';
            modal.show();
        });

        document.querySelectorAll('[data-accion="editar"]').forEach(function (boton) {
            boton.addEventListener('click', function () {
                form.reset();
                form.classList.remove('was-validated');
                form.action = urlBase + '/eventos/actualizar/' + boton.dataset.id;
                document.getElementById('campoNombreEv').value = boton.dataset.nombre;
                document.getElementById('campoDescripcionEv').value = boton.dataset.descripcion;
                document.getElementById('campoFechaEv').value = boton.dataset.fecha;
                document.getElementById('campoHoraEv').value = boton.dataset.hora;
                document.getElementById('campoDjEv').value = boton.dataset.dj;
                document.getElementById('campoPrecioEv').value = boton.dataset.precio;
                document.getElementById('campoAforoEv').value = boton.dataset.aforo;
                tituloModal.textContent = 'Editar evento';
                modal.show();
            });
        });

        // Al elegir otro estado en el <select>, confirma y envía el formulario oculto
        document.querySelectorAll('[data-accion="estado"]').forEach(function (select) {
            var valorAnterior = select.value;

            select.addEventListener('change', function () {
                var nuevoEstado = select.value;

                Dubai.confirmar('¿Cambiar el estado a "' + select.options[select.selectedIndex].text + '"?')
                    .then(function (confirmo) {
                        if (!confirmo) {
                            select.value = valorAnterior;   // el usuario canceló: regresa la opción anterior
                            return;
                        }
                        var formEstado = document.getElementById('formEstadoEvento');
                        formEstado.action = urlBase + '/eventos/cambiarEstado/' + select.dataset.id + '/' + nuevoEstado;
                        formEstado.submit();
                    });
            });
        });
    <?php endif; ?>
});
</script>