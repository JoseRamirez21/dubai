<?php
/**
 * DashboardController: panel de inicio.
 *
 * El administrador ve, además de los módulos, los indicadores y los datos
 * para los gráficos. Cajero y mesero ven solo el panel simple de módulos.
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
        $this->requerirLogin();

        $usuario = Auth::usuario();
        $esAdmin = $usuario['rol'] === 'administrador';

        $datos = [
            'titulo'  => 'Panel',
            'usuario' => $usuario,
            'modulos' => self::MODULOS_POR_ROL[$usuario['rol']] ?? [],
            'esAdmin' => $esAdmin,
        ];

        // Los indicadores y gráficos solo se calculan si el rol los necesita
        if ($esAdmin) {
            $datos += $this->datosDashboard();
        }

        $this->vista('dashboard/inicio', $datos);
    }

    /**
     * Junta los datos de los 4 indicadores y los 2 gráficos.
     * Está en su propio método para que index() se lea de corrido.
     */
    private function datosDashboard(): array
    {
        $venta    = new Venta();
        $entrada  = new Entrada();
        $mesa     = new Mesa();
        $producto = new Producto();

        return [
            'ventasHoy'     => $venta->totalHoy(),
            'entradasHoy'   => $entrada->vendidasHoy(),
            'mesasOcupadas' => $mesa->contarPorEstado('ocupada'),
            'mesasTotal'    => count($mesa->todos()),
            'stockBajo'     => $producto->stockBajo(),
            'ventasPorDia'  => $venta->totalesPorDia(7),
            'productosTop'  => $producto->masVendidos(5),
        ];
    }
}