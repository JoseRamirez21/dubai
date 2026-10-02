<?php
/**
 * DashboardController: panel de inicio.
 *
 * En la Fase 1 es una pantalla sencilla que confirma el login y muestra qué
 * módulos corresponden a cada rol. En la Fase 2 se convertirá en el dashboard
 * real con indicadores y gráficos.
 */
class DashboardController extends Controller
{
    // Módulos que ve cada rol (según el documento del proyecto)
    private const MODULOS_POR_ROL = [
        'administrador' => [
            ['icono' => 'bi-graph-up-arrow', 'nombre' => 'Dashboard y reportes',  'detalle' => 'Indicadores, gráficos y reporte de ventas'],
            ['icono' => 'bi-calendar-event', 'nombre' => 'Eventos y entradas',    'detalle' => 'Eventos, venta de entradas y aforo'],
            ['icono' => 'bi-grid-3x3-gap',   'nombre' => 'Reservas de mesas',     'detalle' => 'Mapa de mesas y reservas'],
            ['icono' => 'bi-cup-straw',      'nombre' => 'Productos y ventas',    'detalle' => 'Catálogo, stock y consumos'],
        ],
        'cajero' => [
            ['icono' => 'bi-calendar-event', 'nombre' => 'Eventos y entradas',    'detalle' => 'Venta de entradas y aforo'],
            ['icono' => 'bi-cup-straw',      'nombre' => 'Productos y ventas',    'detalle' => 'Ventas y cobro de cuentas'],
        ],
        'mesero' => [
            ['icono' => 'bi-grid-3x3-gap',   'nombre' => 'Mesas',                 'detalle' => 'Estado de las mesas'],
            ['icono' => 'bi-receipt',        'nombre' => 'Consumos por mesa',     'detalle' => 'Registrar lo que consume cada mesa'],
        ],
    ];

    public function index(): void
    {
        // Verificación en el servidor: sin sesión no se entra
        $this->requerirLogin();

        $usuario = Auth::usuario();

        $this->vista('dashboard/inicio', [
            'titulo'  => 'Panel',
            'usuario' => $usuario,
            'modulos' => self::MODULOS_POR_ROL[$usuario['rol']] ?? [],
        ]);
    }
}