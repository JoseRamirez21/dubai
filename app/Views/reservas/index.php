<?php
/**
 * VISTA: mapa de mesas por zona (coloreado según estado) y la lista de
 * reservas activas. Variables: $mesas, $reservas, $eventos, $toast.
 */
$mesasPorZona = [];
foreach ($mesas as $m) {
    $mesasPorZona[$m['zona']][] = $m;
}
$claseEstado = ['libre' => 'mesa-libre', 'reservada' => 'mesa-reservada', 'ocupada' => 'mesa-ocupada'];
?>
<h1 class="titulo-pagina mb-4">Mesas y reservas</h1>

<div class="row g-3 g-md-4">
    <!-- ---------- Mapa de mesas ---------- -->
    <div class="col-12 col-lg-7">
        <section class="card-dubai h-100">
            <h2 class="card-titulo mb-3">Mapa de mesas</h2>

            <?php foreach ($mesasPorZona as $zona => $listaMesas): ?>
                <p class="mapa-zona-titulo">Zona <?= htmlspecialchars($zona) ?></p>
                <div class="mapa-grid">
                    <?php foreach ($listaMesas as $m): ?>
                        <?php if ($m['estado'] === 'libre'): ?>
                            <button type="button" class="mesa-box <?= $claseEstado[$m['estado']] ?>"
                                    data-accion="reservar"
                                    data-mesa-id="<?= (int) $m['id'] ?>"
                                    data-mesa-numero="<?= (int) $m['numero'] ?>"
                                    data-mesa-zona="<?= htmlspecialchars($zona) ?>"
                                    data-capacidad="<?= (int) $m['capacidad'] ?>"
                                    aria-label="Reservar mesa <?= (int) $m['numero'] ?>, libre">
                                <span class="mesa-numero">#<?= (int) $m['numero'] ?></span>
                                <span class="mesa-capacidad"><?= (int) $m['capacidad'] ?> pers.</span>
                            </button>
                        <?php else: ?>
                            <div class="mesa-box <?= $claseEstado[$m['estado']] ?>"
                                 aria-label="Mesa <?= (int) $m['numero'] ?>, <?= htmlspecialchars($m['estado']) ?>">
                                <span class="mesa-numero">#<?= (int) $m['numero'] ?></span>
                                <span class="mesa-capacidad"><?= htmlspecialchars($m['estado']) ?></span>
                            </div>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </div>
            <?php endforeach; ?>

            <div class="mapa-leyenda">
                <span><i style="background: var(--dubai-ok)"></i> Libre (toca para reservar)</span>
                <span><i style="background: var(--dubai-warn)"></i> Reservada</span>
                <span><i style="background: var(--dubai-danger)"></i> Ocupada</span>
            </div>
        </section>
    </div>

    <!-- ---------- Reservas activas ---------- -->
    <div class="col-12 col-lg-5">
        <section class="card-dubai h-100">
            <h2 class="card-titulo mb-3">Reservas activas</h2>

            <?php if (empty($reservas)): ?>
                <div class="estado-vacio">
                    <i class="bi bi-calendar-x" aria-hidden="true"></i>
                    <p>No hay reservas activas por ahora.</p>
                </div>
            <?php else: ?>
                <div class="d-flex flex-column gap-3">
                    <?php foreach ($reservas as $r): ?>
                        <article class="reserva-card border-bottom pb-3">
                            <p class="reserva-titulo">
                                Mesa #<?= (int) $r['mesa_numero'] ?>
                                <span class="text-secondary">(<?= htmlspecialchars($r['mesa_zona']) ?>)</span>
                            </p>
                            <p class="text-secondary mb-1"><?= htmlspecialchars($r['evento_nombre']) ?></p>
                            <p class="mb-1"><?= htmlspecialchars($r['cliente_nombre']) ?> · <?= htmlspecialchars($r['cliente_telefono']) ?></p>
                            <p class="text-secondary mb-2">
                                <?= (int) $r['cantidad_personas'] ?> personas ·
                                Garantía <?= CURRENCY ?> <?= number_format((float) $r['garantia'], 2) ?>
                            </p>
                            <div class="d-flex gap-2">
                                <button type="button" class="btn btn-dubai btn-sm"
                                        data-accion="cumplida" data-id="<?= (int) $r['id'] ?>"
                                        data-mensaje="¿Marcar como cumplida la reserva de &quot;<?= htmlspecialchars($r['cliente_nombre'], ENT_QUOTES) ?>&quot;? La mesa pasará a ocupada.">
                                    <i class="bi bi-check-circle" aria-hidden="true"></i> Cumplida
                                </button>
                                <button type="button" class="btn btn-dubai-outline btn-sm"
                                        data-accion="cancelar" data-id="<?= (int) $r['id'] ?>"
                                        data-mensaje="¿Cancelar la reserva de &quot;<?= htmlspecialchars($r['cliente_nombre'], ENT_QUOTES) ?>&quot;?">
                                    <i class="bi bi-x-circle" aria-hidden="true"></i> Cancelar
                                </button>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>
    </div>
</div>

<!-- ---------- Modal: nueva reserva ---------- -->
<div class="modal fade" id="modalReserva" tabindex="-1" aria-labelledby="modalReservaTitulo" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="post" id="formReserva" class="needs-validation" novalidate>
                <?= Csrf::campo() ?>
                <input type="hidden" name="mesa_id" id="campoMesaId">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalReservaTitulo">Nueva reserva</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    <p class="text-secondary mb-3">Mesa: <strong id="reservaMesaTexto"></strong></p>

                    <div class="mb-3">
                        <label for="campoEventoRes" class="form-label">Evento</label>
                        <select class="form-select" id="campoEventoRes" name="evento_id" required>
                            <option value="" selected disabled>Elige un evento</option>
                            <?php foreach ($eventos as $ev): ?>
                                <option value="<?= (int) $ev['id'] ?>"><?= htmlspecialchars($ev['nombre']) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <div class="invalid-feedback">Elige el evento para esta reserva.</div>
                    </div>

                    <div class="mb-3">
                        <label for="campoClienteRes" class="form-label">Nombre del cliente</label>
                        <input type="text" class="form-control" id="campoClienteRes" name="cliente_nombre"
                               required maxlength="100" data-trim>
                        <div class="invalid-feedback">Escribe el nombre del cliente.</div>
                    </div>

                    <div class="mb-3">
                        <label for="campoTelefonoRes" class="form-label">Teléfono</label>
                        <input type="tel" class="form-control" id="campoTelefonoRes" name="cliente_telefono"
                               required pattern="\d{6,20}" maxlength="20" data-trim>
                        <div class="invalid-feedback">Solo números, entre 6 y 20 dígitos.</div>
                    </div>

                    <div class="row g-3">
                        <div class="col-6">
                            <label for="campoPersonasRes" class="form-label">Personas</label>
                            <input type="number" class="form-control" id="campoPersonasRes" name="cantidad_personas"
                                   required min="1" step="1">
                            <div class="invalid-feedback">No puede superar la capacidad de la mesa.</div>
                        </div>
                        <div class="col-6">
                            <label for="campoGarantiaRes" class="form-label">Garantía (<?= CURRENCY ?>)</label>
                            <input type="number" class="form-control" id="campoGarantiaRes" name="garantia"
                                   required min="0" step="0.01">
                            <div class="invalid-feedback">0 o más.</div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-dubai-outline btn-sm" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-dubai btn-sm">Reservar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Formulario oculto para cancelar / marcar cumplida (solo el token) -->
<form id="formAccionReserva" method="post" class="d-none"><?= Csrf::campo() ?></form>

<script>
document.addEventListener('DOMContentLoaded', function () {
    <?php if ($toast): ?>
        Dubai.toast(<?= json_encode($toast['tipo']) ?>, <?= json_encode($toast['texto']) ?>);
    <?php endif; ?>

    var urlBase      = '<?= BASE_URL ?>';
    var modalReserva = new bootstrap.Modal(document.getElementById('modalReserva'));
    var formReserva  = document.getElementById('formReserva');

    // Clic en una mesa LIBRE: abre el modal con esa mesa ya elegida
    document.querySelectorAll('[data-accion="reservar"]').forEach(function (caja) {
        caja.addEventListener('click', function () {
            formReserva.reset();
            formReserva.classList.remove('was-validated');
            formReserva.action = urlBase + '/reservas/crear';
            document.getElementById('campoMesaId').value = caja.dataset.mesaId;
            document.getElementById('reservaMesaTexto').textContent =
                'Mesa #' + caja.dataset.mesaNumero + ' (' + caja.dataset.mesaZona + ') · capacidad ' + caja.dataset.capacidad;
            // El máximo de personas no puede superar la capacidad de ESTA mesa
            document.getElementById('campoPersonasRes').max = caja.dataset.capacidad;
            modalReserva.show();
        });
    });

    // Cancelar / marcar cumplida: confirma con SweetAlert2 y envía el formulario oculto
    document.querySelectorAll('[data-accion="cancelar"], [data-accion="cumplida"]').forEach(function (boton) {
        boton.addEventListener('click', function () {
            Dubai.confirmar(boton.dataset.mensaje, 'Sí, continuar').then(function (confirmo) {
                if (!confirmo) { return; }

                var ruta = boton.dataset.accion === 'cumplida' ? 'marcarCumplida' : 'cancelar';
                var formAccion = document.getElementById('formAccionReserva');
                formAccion.action = urlBase + '/reservas/' + ruta + '/' + boton.dataset.id;
                formAccion.submit();
            });
        });
    });
});
</script>