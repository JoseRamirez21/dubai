<?php
/**
 * VISTA: panel de inicio.
 * Solo contiene lo propio de esta pantalla: el layout "main" pone la barra superior.
 * Variables: $usuario, $modulos, $esAdmin y (solo si $esAdmin) $ventasHoy,
 * $entradasHoy, $mesasOcupadas, $mesasTotal, $stockBajo, $ventasPorDia, $productosTop.
 */
?>
<?php if ($esAdmin): ?>
    <!-- ---------- Las 4 tarjetas de indicadores (solo administrador) ---------- -->
    <div class="row g-3 g-md-4 mb-4">

        <div class="col-6 col-lg-3">
            <article class="card-dubai indicador-card h-100">
                <span class="indicador-icono indicador-dorado">
                    <i class="bi bi-cash-coin" aria-hidden="true"></i>
                </span>
                <div>
                    <p class="indicador-valor"><?= CURRENCY ?> <?= number_format($ventasHoy, 2) ?></p>
                    <p class="indicador-etiqueta">Ventas de hoy</p>
                </div>
            </article>
        </div>

        <div class="col-6 col-lg-3">
            <article class="card-dubai indicador-card h-100">
                <span class="indicador-icono indicador-verde">
                    <i class="bi bi-ticket-perforated" aria-hidden="true"></i>
                </span>
                <div>
                    <p class="indicador-valor"><?= $entradasHoy ?></p>
                    <p class="indicador-etiqueta">Entradas vendidas hoy</p>
                </div>
            </article>
        </div>

        <div class="col-6 col-lg-3">
            <article class="card-dubai indicador-card h-100">
                <span class="indicador-icono indicador-ambar">
                    <i class="bi bi-grid-3x3-gap-fill" aria-hidden="true"></i>
                </span>
                <div>
                    <p class="indicador-valor"><?= $mesasOcupadas ?> <span class="text-secondary" style="font-size:0.95rem;">/ <?= $mesasTotal ?></span></p>
                    <p class="indicador-etiqueta">Mesas ocupadas</p>
                </div>
            </article>
        </div>

        <div class="col-6 col-lg-3">
            <article class="card-dubai indicador-card h-100">
                <!-- Color dinámico: rojo si hay alertas, verde si todo está bien -->
                <span class="indicador-icono <?= count($stockBajo) > 0 ? 'indicador-rojo' : 'indicador-verde' ?>">
                    <i class="bi bi-exclamation-triangle" aria-hidden="true"></i>
                </span>
                <div>
                    <p class="indicador-valor"><?= count($stockBajo) ?></p>
                    <p class="indicador-etiqueta">Productos con stock bajo</p>
                </div>
            </article>
        </div>

       </div>

    <!-- ---------- Los 2 gráficos (solo administrador) ---------- -->
    <div class="row g-3 g-md-4 mb-4">

        <div class="col-12 col-lg-7">
            <section class="card-dubai h-100">
                <h2 class="card-titulo mb-3">Ventas de los últimos 7 días</h2>
                <div class="grafico-wrap">
                    <canvas id="graficoVentas"></canvas>
                </div>
            </section>
        </div>

        <div class="col-12 col-lg-5">
            <section class="card-dubai h-100">
                <h2 class="card-titulo mb-3">Productos más vendidos</h2>
                <div class="grafico-wrap">
                    <canvas id="graficoTop"></canvas>
                </div>
            </section>
        </div>

    </div>

    <!-- Chart.js solo se carga aquí, en la única pantalla que lo necesita -->
    <script src="<?= BASE_URL ?>/vendor/chartjs/chart.umd.min.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function () {
        // Los datos los calculó PHP; json_encode los convierte a JavaScript válido
        const etiquetasVentas = <?= json_encode(array_map(
            fn($fecha) => date('d/m', strtotime($fecha)),
            array_keys($ventasPorDia)
        )) ?>;
        const valoresVentas = <?= json_encode(array_values($ventasPorDia)) ?>;

        const etiquetasTop = <?= json_encode(array_column($productosTop, 'nombre')) ?>;
        const valoresTop   = <?= json_encode(array_map('intval', array_column($productosTop, 'cantidad'))) ?>;

        // Leemos los colores directo de nuestras variables CSS, para que el
        // gráfico combine con el tema sin tener que repetir los códigos de color.
        const estilos    = getComputedStyle(document.documentElement);
        const colorGold   = estilos.getPropertyValue('--dubai-gold').trim();
        const colorMuted  = estilos.getPropertyValue('--dubai-muted').trim();
        const colorBorder = estilos.getPropertyValue('--dubai-border').trim();

        // ---- Gráfico 1: ventas por día (líneas) ----
        new Chart(document.getElementById('graficoVentas'), {
            type: 'line',
            data: {
                labels: etiquetasVentas,
                datasets: [{
                    label: 'Ventas (S/)',
                    data: valoresVentas,
                    borderColor: colorGold,
                    backgroundColor: 'rgba(212, 175, 55, 0.15)',
                    pointBackgroundColor: colorGold,
                    tension: 0.3,   // curva suave en vez de líneas rectas
                    fill: true
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    x: { ticks: { color: colorMuted }, grid: { color: colorBorder } },
                    y: { ticks: { color: colorMuted }, grid: { color: colorBorder }, beginAtZero: true }
                }
            }
        });

        // ---- Gráfico 2: productos más vendidos (barras horizontales) ----
        new Chart(document.getElementById('graficoTop'), {
            type: 'bar',
            data: {
                labels: etiquetasTop,
                datasets: [{
                    label: 'Unidades vendidas',
                    data: valoresTop,
                    backgroundColor: colorGold,
                    borderRadius: 4
                }]
            },
            options: {
                indexAxis: 'y',   // barras horizontales: los nombres largos se leen mejor
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    x: { ticks: { color: colorMuted }, grid: { color: colorBorder }, beginAtZero: true },
                    y: { ticks: { color: colorMuted }, grid: { display: false } }
                }
            }
        });
    });
    </script>
<?php endif; ?>

<h1 class="titulo-pagina">Bienvenido, <?= htmlspecialchars($usuario['nombre']) ?></h1>
<p class="text-secondary mb-4">
    Sesión iniciada con el rol <strong><?= htmlspecialchars($usuario['rol']) ?></strong>.
    Estos son los módulos que te corresponden:
</p>

<div class="row g-3 g-md-4">
    <?php foreach ($modulos as $m): ?>
        <div class="col-12 col-sm-6 col-lg-3">
            <article class="card-dubai h-100">
                <i class="bi <?= htmlspecialchars($m['icono']) ?> card-icono" aria-hidden="true"></i>
                <h2 class="card-titulo"><?= htmlspecialchars($m['nombre']) ?></h2>
                <p class="card-detalle"><?= htmlspecialchars($m['detalle']) ?></p>
                <span class="etiqueta-pronto">Próximamente</span>
            </article>
        </div>
    <?php endforeach; ?>
</div>