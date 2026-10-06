<?php
/**
 * ProductosController: catálogo de productos (CRUD) y su stock.
 *
 * Por ahora, gestionar el catálogo (crear, editar, activar/desactivar
 * productos) es una tarea del ADMINISTRADOR: es quien decide qué se vende
 * y a qué precio. Cajero y mesero USARÁN estos productos para vender en la
 * Fase 6 (Ventas), pero no editan el catálogo. Por eso todo este
 * controlador exige el rol 'administrador'.
 */
class ProductosController extends Controller
{
    // Mensajes que puede mostrar esta pantalla, identificados por un código corto
    // en la URL (?m=creado). El mismo patrón que ya usamos en AuthController.
    private const MENSAJES = [
        'creado'       => ['tipo' => 'success', 'texto' => 'Producto creado correctamente.'],
        'actualizado'  => ['tipo' => 'success', 'texto' => 'Producto actualizado correctamente.'],
        'desactivado'  => ['tipo' => 'success', 'texto' => 'Producto desactivado.'],
        'activado'     => ['tipo' => 'success', 'texto' => 'Producto activado.'],
        'error_datos'  => ['tipo' => 'error',   'texto' => 'Revisa los datos: hay campos inválidos.'],
    ];

    /**
     * Lista todos los productos. Esta es la única pantalla del módulo:
     * crear y editar se hacen con un MODAL sobre esta misma lista.
     */
    public function index(): void
    {
        $this->requerirRol(['administrador']);

        $modelo = new Producto();

        $codigo = $_GET['m'] ?? '';
        $toast  = (is_string($codigo) && isset(self::MENSAJES[$codigo])) ? self::MENSAJES[$codigo] : null;

                $this->vista('productos/index', [
            'titulo'     => 'Productos',
            'productos'  => $modelo->todos(),
            'categorias' => Producto::CATEGORIAS,
            'toast'      => $toast,
        ]);
    }

    /**
     * Crea un producto nuevo. Solo responde a POST (viene del modal "Nuevo producto").
     */
    public function crear(): void
    {
        $this->requerirRol(['administrador']);

        if (!$this->esPost()) {
            $this->redirigir('productos');
        }
        $this->verificarCsrf();

        $resultado = $this->validar($_POST);
        if (!$resultado['ok']) {
            $this->redirigir('productos?m=error_datos');
        }

        (new Producto())->crear($resultado['datos']);
        $this->redirigir('productos?m=creado');
    }

    /**
     * Actualiza un producto existente. $id viene de la URL: /productos/actualizar/7
     *
     * $id llega como string (así entrega el Router todos los parámetros de la
     * URL). Lo validamos con ctype_digit ANTES de convertirlo a número: si
     * alguien escribe una URL rara (letras, vacío, símbolos), esto evita un
     * error de PHP y responde con un 404 limpio, como el resto del sistema.
     */
    public function actualizar(string $id): void
    {
        $this->requerirRol(['administrador']);

        if (!$this->esPost()) {
            $this->redirigir('productos');
        }
        $this->verificarCsrf();

        if (!ctype_digit($id)) {
            (new ErrorController())->noEncontrado();
        }
        $id = (int) $id;

        $modelo = new Producto();
        if ($modelo->buscar($id) === null) {
            (new ErrorController())->noEncontrado();
        }

        $resultado = $this->validar($_POST);
        if (!$resultado['ok']) {
            $this->redirigir('productos?m=error_datos');
        }

        $modelo->actualizar($id, $resultado['datos']);
        $this->redirigir('productos?m=actualizado');
    }

    /**
     * Activa o desactiva un producto. $id y $estado llegan en la URL:
     * /productos/cambiarEstado/7/inactivo
     * Esta es la acción que la vista usa como "Eliminar" / "Restaurar".
     */
    public function cambiarEstado(string $id, string $estado): void
    {
        $this->requerirRol(['administrador']);

        if (!$this->esPost()) {
            $this->redirigir('productos');
        }
        $this->verificarCsrf();

        // Dos validaciones de lista blanca: el id debe ser un número,
        // y el estado debe ser EXACTAMENTE uno de estos dos valores.
        if (!ctype_digit($id) || !in_array($estado, ['activo', 'inactivo'], true)) {
            (new ErrorController())->noEncontrado();
        }
        $id = (int) $id;

        $modelo = new Producto();
        if ($modelo->buscar($id) === null) {
            (new ErrorController())->noEncontrado();
        }

        $modelo->cambiarEstado($id, $estado);
        $this->redirigir('productos?m=' . ($estado === 'inactivo' ? 'desactivado' : 'activado'));
    }

    /**
     * Valida los datos de un producto (para crear() y actualizar()).
     * Devuelve ['ok' => bool, 'datos' => arreglo ya limpio, 'errores' => [...]].
     * El Modelo nunca valida nada: esa es tarea del Controlador.
     */
    private function validar(array $entrada): array
    {
        $errores = [];
        $datos   = [];

        $datos['nombre'] = trim((string) ($entrada['nombre'] ?? ''));
        if ($datos['nombre'] === '' || strlen($datos['nombre']) > 100) {
            $errores[] = 'nombre';
        }

        // "Lista blanca": la categoría debe ser EXACTAMENTE una de las permitidas.
        // Así, aunque alguien manipule el formulario, nunca llega un valor inventado.
        $datos['categoria'] = (string) ($entrada['categoria'] ?? '');
        if (!in_array($datos['categoria'], Producto::CATEGORIAS, true)) {
            $errores[] = 'categoria';
        }

        $datos['precio'] = (float) ($entrada['precio'] ?? -1);
        if ($datos['precio'] <= 0 || $datos['precio'] > 9999.99) {
            $errores[] = 'precio';
        }

        $datos['stock'] = (int) ($entrada['stock'] ?? -1);
        if ($datos['stock'] < 0) {
            $errores[] = 'stock';
        }

        $datos['stock_minimo'] = (int) ($entrada['stock_minimo'] ?? -1);
        if ($datos['stock_minimo'] < 0) {
            $errores[] = 'stock_minimo';
        }

        return ['ok' => empty($errores), 'datos' => $datos, 'errores' => $errores];
    }
}