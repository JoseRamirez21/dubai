<?php
/**
 * ErrorController: muestra las páginas de error con el diseño DUBAI.
 * Lo usan el Router (404) y la clase Controller (403 y 419).
 * Todas pasan por mostrar(), para no repetir código.
 */
class ErrorController extends Controller
{
    // 404: la dirección no existe
    public function noEncontrado(): void
    {
        $this->mostrar(404, 'Página no encontrada',
            'La dirección que buscas no existe o fue movida.');
    }

    // 403: hay sesión, pero el rol no tiene permiso
    public function prohibido(): void
    {
        $this->mostrar(403, 'Acceso denegado',
            'No tienes permiso para entrar a esta sección.');
    }

    // 419: el formulario no trae un token válido (o venció la sesión)
    public function tokenInvalido(): void
    {
        $this->mostrar(419, 'Formulario vencido',
            'El formulario no es válido o venció. Vuelve atrás, recarga la página e inténtalo de nuevo.');
    }

    /**
     * Envía el código HTTP correcto, dibuja la página y detiene el script.
     * Los textos están escritos aquí: nada de lo que escriba el usuario se muestra.
     */
    private function mostrar(int $codigo, string $titulo, string $mensaje): void
    {
        http_response_code($codigo);

        $this->vista('errores/error', [
            'titulo'  => $titulo,
            'codigo'  => $codigo,
            'mensaje' => $mensaje,
        ], 'auth');

        exit;
    }
}