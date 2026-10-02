<?php
/**
 * Router: traduce la URL a "Controlador + Método + Parámetros".
 *
 * Ejemplo: /eventos/editar/5
 *   controlador = EventosController
 *   método      = editar
 *   parámetros  = [5]
 *
 * Una sola responsabilidad: decidir qué código se ejecuta.
 */
class Router
{
    private string $controlador = DEFAULT_CONTROLLER;
    private string $metodo      = DEFAULT_METHOD;
    private array  $parametros  = [];

    public function __construct()
    {
        $this->analizarUrl();
    }

    /**
     * Lee $_GET['url'] (la llena el .htaccess) y la divide en partes.
     */
    private function analizarUrl(): void
    {
        // trim quita las "/" sobrantes del inicio y del final
        $url = trim($_GET['url'] ?? '', '/');

        // URL vacía: se quedan los valores por defecto
        if ($url === '') {
            return;
        }

        $partes = explode('/', $url);

        // 1.ª parte = controlador (ej. "eventos" -> "Eventos")
        $this->controlador = ucfirst(strtolower($partes[0]));

        // 2.ª parte = método. Si no viene, se usa "index".
        $this->metodo = $partes[1] ?? 'index';

        // El resto de partes son los parámetros (ej. el id "5")
        $this->parametros = array_slice($partes, 2);
    }

    /**
     * Crea el controlador y ejecuta el método. Si algo no es válido, muestra 404.
     */
    public function despachar(): void
    {
        // SEGURIDAD: solo letras en el controlador; letras, números y "_" en el método.
        // Así nadie puede colar rutas de archivos ni nombres raros.
        $controladorValido = preg_match('/^[a-zA-Z]+$/', $this->controlador);
        $metodoValido      = preg_match('/^[a-zA-Z][a-zA-Z0-9_]*$/', $this->metodo);

        if (!$controladorValido || !$metodoValido) {
            $this->noEncontrado();
            return;
        }

        // El nombre de la clase siempre termina en "Controller"
        $clase = $this->controlador . 'Controller';

        // class_exists() dispara el autoloader que hicimos en el Paso 2
  if (!class_exists($clase) || !is_subclass_of($clase, 'Controller')) {
                $this->noEncontrado();
            return;
        }

        $objeto = new $clase();

        // is_callable devuelve false si el método no existe o NO es público.
        // Así los métodos private/protected jamás se ejecutan desde la URL.
        if (!is_callable([$objeto, $this->metodo])) {
            $this->noEncontrado();
            return;
        }

        // Ejecuta el método pasándole los parámetros de la URL
        call_user_func_array([$objeto, $this->metodo], $this->parametros);
    }

       /**
     * Respuesta 404 con el diseño DUBAI. No revela información interna.
     */
    private function noEncontrado(): void
    {
        (new ErrorController())->noEncontrado();
    }
}