<?php
/**
 * EventosController: CRUD de eventos.
 *
 * Crear y editar eventos es tarea del ADMINISTRADOR (qué noche se organiza,
 * con qué DJ, a qué precio). Administrador y CAJERO pueden ver la lista con
 * el aforo, porque el cajero la necesita para saber cuántas entradas quedan
 * antes de vender (la venta de entradas se construye en el Paso 4).
 */
class EventosController extends Controller
{
    private const MENSAJES = [
        'creado'         => ['tipo' => 'success', 'texto' => 'Evento creado correctamente.'],
        'actualizado'    => ['tipo' => 'success', 'texto' => 'Evento actualizado correctamente.'],
        'estado'         => ['tipo' => 'success', 'texto' => 'Estado del evento actualizado.'],
        'error_datos'    => ['tipo' => 'error',   'texto' => 'Revisa los datos: hay campos inválidos.'],
        'error_aforo'    => ['tipo' => 'error',   'texto' => 'El aforo no puede ser menor a las entradas ya vendidas.'],
    ];

    /**
     * Lista los eventos con su aforo (vendidas / máximo).
     * Administrador y cajero pueden verla.
     */
    public function index(): void
    {
        $this->requerirRol(['administrador', 'cajero']);

        $modelo = new Evento();

        $codigo = $_GET['m'] ?? '';
        $toast  = (is_string($codigo) && isset(self::MENSAJES[$codigo])) ? self::MENSAJES[$codigo] : null;

        $this->vista('eventos/index', [
            'titulo'  => 'Eventos',
            'eventos' => $modelo->todosConAforo(),
            'esAdmin' => Auth::rol() === 'administrador',
            'toast'   => $toast,
        ]);
    }

    /**
     * Crea un evento nuevo. Solo administrador.
     */
    public function crear(): void
    {
        $this->requerirRol(['administrador']);

        if (!$this->esPost()) {
            $this->redirigir('eventos');
        }
        $this->verificarCsrf();

        $resultado = $this->validar($_POST);
        if (!$resultado['ok']) {
            $this->redirigir('eventos?m=error_datos');
        }

        (new Evento())->crear($resultado['datos']);
        $this->redirigir('eventos?m=creado');
    }

    /**
     * Actualiza un evento existente. Solo administrador.
     */
    public function actualizar(string $id): void
    {
        $this->requerirRol(['administrador']);

        if (!$this->esPost()) {
            $this->redirigir('eventos');
        }
        $this->verificarCsrf();

        if (!ctype_digit($id)) {
            (new ErrorController())->noEncontrado();
        }
        $id = (int) $id;

        $modelo = new Evento();
        if ($modelo->buscar($id) === null) {
            (new ErrorController())->noEncontrado();
        }

        $resultado = $this->validar($_POST);
        if (!$resultado['ok']) {
            $this->redirigir('eventos?m=error_datos');
        }

        // El aforo nuevo nunca puede ser menor a lo que ya se vendió:
        // dejaría entradas "de más" vendidas, imposibles de explicar.
        $vendidas = (new Entrada())->contarVendidasPorEvento($id);
        if ($resultado['datos']['aforo'] < $vendidas) {
            $this->redirigir('eventos?m=error_aforo');
        }

        $modelo->actualizar($id, $resultado['datos']);
        $this->redirigir('eventos?m=actualizado');
    }

    /**
     * Cambia el estado del evento (programado / en_curso / cerrado). Solo administrador.
     */
    public function cambiarEstado(string $id, string $estado): void
    {
        $this->requerirRol(['administrador']);

        if (!$this->esPost()) {
            $this->redirigir('eventos');
        }
        $this->verificarCsrf();

        if (!ctype_digit($id) || !in_array($estado, Evento::ESTADOS, true)) {
            (new ErrorController())->noEncontrado();
        }
        $id = (int) $id;

        $modelo = new Evento();
        if ($modelo->buscar($id) === null) {
            (new ErrorController())->noEncontrado();
        }

        $modelo->cambiarEstado($id, $estado);
        $this->redirigir('eventos?m=estado');
    }

    /**
     * Valida los datos de un evento (para crear() y actualizar()).
     */
    private function validar(array $entrada): array
    {
        $errores = [];
        $datos   = [];

        $datos['nombre'] = trim((string) ($entrada['nombre'] ?? ''));
        if ($datos['nombre'] === '' || strlen($datos['nombre']) > 120) {
            $errores[] = 'nombre';
        }

        $datos['descripcion'] = trim((string) ($entrada['descripcion'] ?? ''));
        if (strlen($datos['descripcion']) > 1000) {
            $errores[] = 'descripcion';
        }

        // checkdate confirma que sea una fecha real (ej. rechaza 31 de febrero)
        $fecha = (string) ($entrada['fecha'] ?? '');
        $partesFecha = explode('-', $fecha);
        $datos['fecha'] = $fecha;
        if (count($partesFecha) !== 3 || !checkdate((int) $partesFecha[1], (int) $partesFecha[2], (int) $partesFecha[0])) {
            $errores[] = 'fecha';
        }

        $hora = (string) ($entrada['hora_inicio'] ?? '');
        $datos['hora_inicio'] = $hora;
        if (!preg_match('/^([01]\d|2[0-3]):[0-5]\d$/', $hora)) {
            $errores[] = 'hora_inicio';
        }

        $datos['dj_artista'] = trim((string) ($entrada['dj_artista'] ?? ''));
        if ($datos['dj_artista'] === '' || strlen($datos['dj_artista']) > 100) {
            $errores[] = 'dj_artista';
        }

        $datos['precio_entrada'] = (float) ($entrada['precio_entrada'] ?? -1);
        if ($datos['precio_entrada'] <= 0 || $datos['precio_entrada'] > 9999.99) {
            $errores[] = 'precio_entrada';
        }

        $datos['aforo'] = (int) ($entrada['aforo'] ?? -1);
        if ($datos['aforo'] <= 0 || $datos['aforo'] > 100000) {
            $errores[] = 'aforo';
        }

        return ['ok' => empty($errores), 'datos' => $datos, 'errores' => $errores];
    }
}