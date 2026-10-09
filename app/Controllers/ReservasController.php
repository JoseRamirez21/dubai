<?php
/**
 * ReservasController: mapa de mesas y gestión de reservas.
 *
 * Según tu documento, "mesero: solo mesas y consumos". Administrador ve
 * todo. Por eso este módulo es para administrador Y mesero (coincide con
 * lo que ya configuramos en el sidebar desde la Fase 2).
 */
class ReservasController extends Controller
{
    private const MENSAJES = [
        'creada'              => ['tipo' => 'success', 'texto' => 'Reserva creada correctamente.'],
        'cancelada'           => ['tipo' => 'success', 'texto' => 'Reserva cancelada. La mesa quedó libre.'],
        'cumplida'            => ['tipo' => 'success', 'texto' => 'Reserva marcada como cumplida. La mesa está ocupada.'],
        'error_datos'         => ['tipo' => 'error',   'texto' => 'Revisa los datos: hay campos inválidos.'],
        'error_mesa_ocupada'  => ['tipo' => 'error',   'texto' => 'Esa mesa ya no está libre.'],
        'error_capacidad'     => ['tipo' => 'error',   'texto' => 'La cantidad de personas supera la capacidad de la mesa.'],
    ];

    /**
     * El mapa de mesas (todas, con su color según estado) y la lista de
     * reservas activas con sus datos.
     */
    public function index(): void
    {
        $this->requerirRol(['administrador', 'mesero']);

        $codigo = $_GET['m'] ?? '';
        $toast  = (is_string($codigo) && isset(self::MENSAJES[$codigo])) ? self::MENSAJES[$codigo] : null;

        $this->vista('reservas/index', [
            'titulo'   => 'Mesas y reservas',
            'mesas'    => (new Mesa())->todas(),
            'reservas' => (new Reserva())->activas(),
            'eventos'  => (new Evento())->disponibles(),
            'toast'    => $toast,
        ]);
    }

    /**
     * Crea una reserva: inserta la fila y cambia la mesa a "reservada",
     * las dos cosas dentro de una transacción.
     */
    public function crear(): void
    {
        $this->requerirRol(['administrador', 'mesero']);

        if (!$this->esPost()) {
            $this->redirigir('reservas');
        }
        $this->verificarCsrf();

        $resultado = $this->validar($_POST);
        if (!$resultado['ok']) {
            $this->redirigir('reservas?m=error_datos');
        }
        $datos = $resultado['datos'];

        $modeloMesa = new Mesa();
        $mesa = $modeloMesa->buscar($datos['mesa_id']);
        if ($mesa === null) {
            (new ErrorController())->noEncontrado();
        }

        // Regla de negocio 1: la mesa debe estar libre ahora mismo
        if ($mesa['estado'] !== 'libre') {
            $this->redirigir('reservas?m=error_mesa_ocupada');
        }

        // Regla de negocio 2: no se puede reservar para más personas de las que la mesa soporta
        if ($datos['cantidad_personas'] > (int) $mesa['capacidad']) {
            $this->redirigir('reservas?m=error_capacidad');
        }

        // ---- Transacción: las dos tablas cambian juntas, o ninguna cambia ----
        $db = Database::getConnection();
        $db->beginTransaction();
        try {
            (new Reserva())->crear($datos);
            $modeloMesa->actualizarEstado($datos['mesa_id'], 'reservada');
            $db->commit();
        } catch (Exception $e) {
            $db->rollBack();
            throw $e;
        }

        $this->redirigir('reservas?m=creada');
    }

    /**
     * Cancela una reserva activa: la reserva pasa a "cancelada" y
     * la mesa vuelve a "libre".
     */
    public function cancelar(string $id): void
    {
        $this->requerirRol(['administrador', 'mesero']);

        if (!$this->esPost()) {
            $this->redirigir('reservas');
        }
        $this->verificarCsrf();

        $this->cambiarEstadoReserva($id, 'cancelada', 'libre', 'cancelada');
    }

    /**
     * Marca una reserva como cumplida (el cliente llegó): la reserva pasa
     * a "cumplida" y la mesa pasa a "ocupada".
     */
    public function marcarCumplida(string $id): void
    {
        $this->requerirRol(['administrador', 'mesero']);

        if (!$this->esPost()) {
            $this->redirigir('reservas');
        }
        $this->verificarCsrf();

        $this->cambiarEstadoReserva($id, 'cumplida', 'ocupada', 'cumplida');
    }

    /**
     * Lógica compartida por cancelar() y marcarCumplida(): buscar la
     * reserva, validar que siga activa, y cambiar las dos tablas juntas.
     */
    private function cambiarEstadoReserva(string $id, string $estadoReserva, string $estadoMesa, string $mensaje): void
    {
        if (!ctype_digit($id)) {
            (new ErrorController())->noEncontrado();
        }
        $id = (int) $id;

        $modeloReserva = new Reserva();
        $reserva = $modeloReserva->buscar($id);

        // Solo se puede actuar sobre una reserva que SIGUE activa
        if ($reserva === null || $reserva['estado'] !== 'activa') {
            (new ErrorController())->noEncontrado();
        }

        $db = Database::getConnection();
        $db->beginTransaction();
        try {
            $modeloReserva->cambiarEstado($id, $estadoReserva);
            (new Mesa())->actualizarEstado((int) $reserva['mesa_id'], $estadoMesa);
            $db->commit();
        } catch (Exception $e) {
            $db->rollBack();
            throw $e;
        }

        $this->redirigir('reservas?m=' . $mensaje);
    }

    /**
     * Valida los datos del formulario de nueva reserva.
     */
    private function validar(array $entrada): array
    {
        $errores = [];
        $datos   = [];

        $datos['evento_id'] = (int) ($entrada['evento_id'] ?? -1);
        if ((new Evento())->buscar($datos['evento_id']) === null) {
            $errores[] = 'evento_id';
        }

        $datos['mesa_id'] = (int) ($entrada['mesa_id'] ?? -1);
        if ($datos['mesa_id'] <= 0) {
            $errores[] = 'mesa_id';
        }

        $datos['cliente_nombre'] = trim((string) ($entrada['cliente_nombre'] ?? ''));
        if ($datos['cliente_nombre'] === '' || strlen($datos['cliente_nombre']) > 100) {
            $errores[] = 'cliente_nombre';
        }

        $datos['cliente_telefono'] = trim((string) ($entrada['cliente_telefono'] ?? ''));
        if (!preg_match('/^\d{6,20}$/', $datos['cliente_telefono'])) {
            $errores[] = 'cliente_telefono';
        }

        $datos['cantidad_personas'] = (int) ($entrada['cantidad_personas'] ?? -1);
        if ($datos['cantidad_personas'] <= 0 || $datos['cantidad_personas'] > 50) {
            $errores[] = 'cantidad_personas';
        }

        $datos['garantia'] = (float) ($entrada['garantia'] ?? -1);
        if ($datos['garantia'] < 0 || $datos['garantia'] > 9999.99) {
            $errores[] = 'garantia';
        }

        return ['ok' => empty($errores), 'datos' => $datos, 'errores' => $errores];
    }
}