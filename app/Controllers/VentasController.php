<?php
/**
 * VentasController: abrir cuentas (por mesa o en barra), agregar productos
 * y cobrar.
 *
 * Quién puede qué, según tu documento:
 *  - Administrador y cajero: TODO, incluyendo cobrar (el dinero).
 *  - Mesero: puede abrir cuentas y agregar productos (registrar consumos),
 *    pero NO cobrar. Tu documento dice "mesero: solo mesas y consumos",
 *    mientras que "cajero maneja... ventas" (el cobro).
 */
class VentasController extends Controller
{
    private const MENSAJES = [
        'abierta'       => ['tipo' => 'success', 'texto' => 'Cuenta abierta.'],
        'agregado'      => ['tipo' => 'success', 'texto' => 'Producto agregado a la cuenta.'],
        'cobrada'       => ['tipo' => 'success', 'texto' => 'Venta cobrada correctamente.'],
        'error_datos'   => ['tipo' => 'error',   'texto' => 'Revisa los datos: hay campos inválidos.'],
        'error_stock'   => ['tipo' => 'error',   'texto' => 'No hay stock suficiente de ese producto.'],
        'error_vacia'   => ['tipo' => 'error',   'texto' => 'No puedes cobrar una cuenta sin productos.'],
    ];

    /**
     * Lista todas las cuentas abiertas, cada una con sus productos.
     */
    public function index(): void
    {
        $this->requerirRol(['administrador', 'cajero', 'mesero']);

        $modeloVenta   = new Venta();
        $modeloDetalle = new DetalleVenta();

        $ventas = $modeloVenta->abiertas();
        // A cada venta le agregamos sus líneas de detalle, para mostrarlas
        // sin que la vista tenga que hacer más consultas.
        foreach ($ventas as &$venta) {
            $venta['detalle'] = $modeloDetalle->porVenta((int) $venta['id']);
        }
        unset($venta); // buena práctica: cortar la referencia del foreach

        $codigo = $_GET['m'] ?? '';
        $toast  = (is_string($codigo) && isset(self::MENSAJES[$codigo])) ? self::MENSAJES[$codigo] : null;

                // ids de mesas que YA tienen una cuenta abierta (para no ofrecerlas
        // de nuevo en el selector de "Abrir cuenta" y evitar confusión)
        $mesasConCuenta = array_filter(array_column($ventas, 'mesa_id'));

        $this->vista('ventas/index', [
            'titulo'           => 'Ventas',
            'ventas'           => $ventas,
            'mesas'            => (new Mesa())->todas(),
            'mesasConCuenta'   => $mesasConCuenta,
            'productos'        => array_filter((new Producto())->todos(), fn($p) => $p['estado'] === 'activo'),
            'esCajero'         => in_array(Auth::rol(), ['administrador', 'cajero'], true),
            'toast'            => $toast,
        ]);
    }

    /**
     * Abre una cuenta nueva. mesa_id = 0 significa "venta en barra" (sin mesa).
     */
    public function abrir(): void
    {
        $this->requerirRol(['administrador', 'cajero', 'mesero']);

        if (!$this->esPost()) {
            $this->redirigir('ventas');
        }
        $this->verificarCsrf();

        $mesaId = (int) ($_POST['mesa_id'] ?? 0);
        $modeloVenta = new Venta();

        if ($mesaId > 0) {
            // Si esa mesa ya tiene una cuenta abierta, no creamos otra: nos
            // quedamos en la misma. Evita cuentas duplicadas por accidente.
            $existente = $modeloVenta->abiertaPorMesa($mesaId);
            if ($existente !== null) {
                $this->redirigir('ventas?m=abierta');
            }
        }

        $modeloVenta->abrir([
            'mesa_id'    => $mesaId > 0 ? $mesaId : null,
            'usuario_id' => Auth::usuario()['id'],
            'evento_id'  => null,
        ]);

        $this->redirigir('ventas?m=abierta');
    }

    /**
     * Agrega un producto a una cuenta abierta. El stock NO se toca aquí.
     */
    public function agregarProducto(string $ventaId): void
    {
        $this->requerirRol(['administrador', 'cajero', 'mesero']);

        if (!$this->esPost()) {
            $this->redirigir('ventas');
        }
        $this->verificarCsrf();

        if (!ctype_digit($ventaId)) {
            (new ErrorController())->noEncontrado();
        }
        $ventaId = (int) $ventaId;

        $modeloVenta = new Venta();
        $venta = $modeloVenta->buscar($ventaId);
        if ($venta === null || $venta['estado'] !== 'abierta') {
            (new ErrorController())->noEncontrado();
        }

        $productoId = (int) ($_POST['producto_id'] ?? 0);
        $cantidad   = (int) ($_POST['cantidad'] ?? 0);

        $modeloProducto = new Producto();
        $producto = $modeloProducto->buscar($productoId);

        if ($producto === null || $producto['estado'] !== 'activo' || $cantidad <= 0) {
            $this->redirigir('ventas?m=error_datos');
        }

        // Aviso temprano: no dejamos agregar más de lo que hay en stock.
        // (La revisión que de verdad protege el dato es descontarStock(),
        // al cobrar; esto es solo para no confundir al usuario antes de eso.)
        if ($cantidad > (int) $producto['stock']) {
            $this->redirigir('ventas?m=error_stock');
        }

        $precioUnitario = (float) $producto['precio'];
        $subtotal = $precioUnitario * $cantidad;

        $db = Database::getConnection();
        $db->beginTransaction();
        try {
            (new DetalleVenta())->agregar([
                'venta_id'        => $ventaId,
                'producto_id'     => $productoId,
                'cantidad'        => $cantidad,
                'precio_unitario' => $precioUnitario,
                'subtotal'        => $subtotal,
            ]);
            $modeloVenta->recalcularTotal($ventaId);
            $db->commit();
        } catch (Exception $e) {
            $db->rollBack();
            throw $e;
        }

        $this->redirigir('ventas?m=agregado');
    }

    /**
     * Cobra una cuenta: descuenta el stock de cada línea (de forma segura,
     * con descontarStock), marca la venta como pagada y libera la mesa.
     * Solo administrador y cajero: aquí se maneja el dinero.
     */
    public function cobrar(string $id): void
    {
        $this->requerirRol(['administrador', 'cajero']);

        if (!$this->esPost()) {
            $this->redirigir('ventas');
        }
        $this->verificarCsrf();

        if (!ctype_digit($id)) {
            (new ErrorController())->noEncontrado();
        }
        $id = (int) $id;

        $modeloVenta = new Venta();
        $venta = $modeloVenta->buscar($id);
        if ($venta === null || $venta['estado'] !== 'abierta') {
            (new ErrorController())->noEncontrado();
        }

        $metodoPago = (string) ($_POST['metodo_pago'] ?? '');
        if (!in_array($metodoPago, Venta::METODOS_PAGO, true)) {
            $this->redirigir('ventas?m=error_datos');
        }

        $modeloDetalle = new DetalleVenta();
        $lineas = $modeloDetalle->porVenta($id);
        if (empty($lineas)) {
            $this->redirigir('ventas?m=error_vacia');
        }

        $modeloProducto = new Producto();

        $db = Database::getConnection();
        $db->beginTransaction();
        try {
            foreach ($lineas as $linea) {
                $ok = $modeloProducto->descontarStock((int) $linea['producto_id'], (int) $linea['cantidad']);
                if (!$ok) {
                    throw new RuntimeException('stock');
                }
            }

            $modeloVenta->cobrar($id, $metodoPago);

            // Si era una venta de mesa, esa mesa vuelve a estar libre.
            if ($venta['mesa_id'] !== null) {
                (new Mesa())->actualizarEstado((int) $venta['mesa_id'], 'libre');
            }

            $db->commit();
        } catch (RuntimeException $e) {
            $db->rollBack();
            $this->redirigir('ventas?m=error_stock');
        } catch (Exception $e) {
            $db->rollBack();
            throw $e;
        }

        $this->redirigir('ventas/ticket/' . $id);
    }

    /**
     * Ticket de consumo, listo para imprimir. Solo quien puede cobrar
     * puede ver el comprobante de pago.
     */
    public function ticket(string $id): void
    {
        $this->requerirRol(['administrador', 'cajero']);

        if (!ctype_digit($id)) {
            (new ErrorController())->noEncontrado();
        }
        $id = (int) $id;

        $modeloVenta = new Venta();
        $venta = $modeloVenta->buscar($id);
        if ($venta === null || $venta['estado'] !== 'pagada') {
            (new ErrorController())->noEncontrado();
        }

        $this->vista('ventas/ticket', [
            'titulo'  => 'Ticket de consumo',
            'venta'   => $venta,
            'detalle' => (new DetalleVenta())->porVenta($id),
        ], 'auth');
    }
}