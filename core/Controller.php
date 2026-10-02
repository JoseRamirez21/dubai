<?php
/**
 * Controller: clase base (abstracta) de todos los controladores.
 *
 * Rol del controlador en MVC: recibe la petición, pide datos al Modelo
 * y le entrega el resultado a la Vista. Aquí va lo que TODOS comparten.
 */
abstract class Controller
{
    /**
     * Muestra una vista dentro de un LAYOUT (plantilla general de la página).
     *
     * Ejemplo: $this->vista('dashboard/inicio', ['titulo' => 'Panel']);
     *
     * Cómo funciona:
     *  1. La vista (solo el contenido propio de la pantalla) se "captura" en
     *     la variable $contenido, en lugar de enviarse al navegador.
     *  2. Se carga el layout, que arma la página completa (cabecera, menú,
     *     pie...) e inserta $contenido en el lugar correcto.
     *
     * Layouts disponibles (app/Views/layouts):
     *  - 'main': para el sistema ya con sesión (barra superior + contenido).
     *  - 'auth': pantalla limpia, sin menú (login y páginas de error).
     *
     * "protected": es una herramienta interna; no se puede ejecutar desde la URL.
     */
    protected function vista(string $vista, array $datos = [], string $layout = 'main'): void
    {
        // Los nombres los escribimos nosotros en el código, nunca vienen del usuario
        $archivoVista  = VIEW_PATH . '/' . $vista . '.php';
        $archivoLayout = VIEW_PATH . '/layouts/' . $layout . '.php';

        if (!is_file($archivoVista) || !is_file($archivoLayout)) {
            http_response_code(500);
            exit(APP_DEBUG
                ? 'Vista o layout no encontrado: ' . htmlspecialchars($vista . ' / ' . $layout)
                : 'Ocurrió un error en el sistema.');
        }

        // extract convierte ['titulo' => 'Hola'] en la variable $titulo
        extract($datos);

        // 1. Capturar el contenido de la vista
        ob_start();
        require $archivoVista;
        $contenido = ob_get_clean();

        // 2. Armar la página completa con el layout
        require $archivoLayout;
    }

    /**
     * Redirige a otra ruta del sistema. Ejemplo: $this->redirigir('auth/login');
     * exit detiene el script para que no se ejecute nada más después.
     */
    protected function redirigir(string $ruta): void
    {
        header('Location: ' . BASE_URL . '/' . ltrim($ruta, '/'));
        exit;
    }

    /**
     * Indica si la petición viene de un formulario enviado (método POST).
     */
    protected function esPost(): bool
    {
        return $_SERVER['REQUEST_METHOD'] === 'POST';
    }

    /**
     * Exige sesión iniciada. Si no la hay, manda al login.
     * Se llama al inicio de cada método que requiera estar conectado.
     */
    protected function requerirLogin(): void
    {
        if (!Auth::verificar()) {
            $this->redirigir('auth/login');
        }
    }

    /**
     * Exige sesión iniciada Y que el rol esté permitido.
     * Ejemplo: $this->requerirRol(['administrador', 'cajero']);
     * Esta es la verificación REAL en el servidor: no depende de ocultar botones.
     */
    protected function requerirRol(array $roles): void
    {
        $this->requerirLogin();

        if (!Auth::tieneRol($roles)) {
            // 403 = "prohibido": estás conectado pero no tienes permiso
            (new ErrorController())->prohibido();
        }
    }

    /**
     * Verifica el token CSRF de un formulario enviado por POST.
     * Se llama al inicio de todo método que reciba datos de un formulario.
     */
    protected function verificarCsrf(): void
    {
        if (!Csrf::validar($_POST['csrf_token'] ?? '')) {
            // 419 = token inválido o vencido
            (new ErrorController())->tokenInvalido();
        }
    }
}