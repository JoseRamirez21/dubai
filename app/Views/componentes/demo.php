<?php
/**
 * VISTA TEMPORAL: muestra los componentes del Paso 3 con las 12 mesas
 * reales de la base de datos. Se borra junto con ComponentesController.
 */
$colorEstado = [
    'libre'     => 'estado-ok',
    'reservada' => 'estado-warn',
    'ocupada'   => 'estado-danger',
];
?>
<h1 class="titulo-pagina mb-4">Componentes (demostración)</h1>

<!-- ---------- 1. Botones de SweetAlert2 ---------- -->
<section class="card-dubai mb-4">
    <h2 class="card-titulo mb-3">1. Alertas con SweetAlert2</h2>
    <div class="d-flex flex-wrap gap-2">
        <button class="btn btn-dubai-outline btn-sm" id="btnToastExito">Toast de éxito</button>
        <button class="btn btn-dubai-outline btn-sm" id="btnToastError">Toast de error</button>
        <button class="btn btn-dubai-outline btn-sm" id="btnConfirmar">Confirmar eliminación</button>
        <button class="btn btn-dubai-outline btn-sm" data-bs-toggle="modal" data-bs-target="#modalDemo">
            Abrir modal
        </button>
    </div>
</section>

<!-- ---------- 2. Tabla con búsqueda y paginación ---------- -->
<section class="card-dubai">
    <h2 class="card-titulo mb-3">2. Tabla: mesas del local</h2>

    <div class="tabla-toolbar">
        <input type="search" class="form-control tabla-buscador"
               placeholder="Buscar mesa, zona o estado..."
               data-tabla-buscar="#tablaMesas">
    </div>

    <div class="tabla-dubai-wrap">
        <table class="tabla-dubai" id="tablaMesas">
            <thead>
                <tr>
                    <th>N° mesa</th>
                    <th>Zona</th>
                    <th>Capacidad</th>
                    <th>Consumo mínimo</th>
                    <th>Estado</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($mesas as $mesa): ?>
                    <tr>
                        <td>Mesa <?= (int) $mesa['numero'] ?></td>
                        <td class="text-capitalize"><?= htmlspecialchars($mesa['zona']) ?></td>
                        <td><?= (int) $mesa['capacidad'] ?> personas</td>
                        <td><?= CURRENCY ?> <?= number_format((float) $mesa['consumo_minimo'], 2) ?></td>
                        <td>
                            <span class="estado-badge <?= $colorEstado[$mesa['estado']] ?? 'estado-ok' ?>">
                                <?= htmlspecialchars($mesa['estado']) ?>
                            </span>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <div class="tabla-paginacion" data-tabla-paginacion="#tablaMesas"></div>
    </div>
</section>

<!-- ---------- 3. Modal de ejemplo ---------- -->
<div class="modal fade" id="modalDemo" tabindex="-1" aria-labelledby="modalDemoTitulo" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalDemoTitulo">Nueva mesa (ejemplo)</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <p class="text-secondary">
                    Este es solo el estilo del modal. El formulario real de mesas
                    se construye en la Fase 5.
                </p>
                <div class="mb-3">
                    <label class="form-label">Número de mesa</label>
                    <input type="text" class="form-control" disabled placeholder="13">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-dubai-outline btn-sm" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-dubai btn-sm" data-bs-dismiss="modal">Guardar</button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    Dubai.tabla('#tablaMesas', { porPagina: 6 });

    document.getElementById('btnToastExito').addEventListener('click', function () {
        Dubai.toast('success', 'Esto es un toast de éxito');
    });
    document.getElementById('btnToastError').addEventListener('click', function () {
        Dubai.toast('error', 'Esto es un toast de error');
    });
    document.getElementById('btnConfirmar').addEventListener('click', function () {
        Dubai.confirmar('¿Eliminar este producto?').then(function (confirmo) {
            Dubai.toast(confirmo ? 'success' : 'info', confirmo ? 'Confirmaste' : 'Cancelaste');
        });
    });
});
</script>