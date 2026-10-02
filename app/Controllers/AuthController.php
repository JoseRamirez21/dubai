<?php
/**
 * AuthController: pantalla de login, validación del acceso y cierre de sesión.
 *
 * Rutas:
 *   /auth/login        -> muestra el formulario (GET)
 *   /auth/autenticar   -> recibe el formulario (POST)
 *   /auth/salir        -> cierra la sesión (POST)
 */
class AuthController extends Controller
{
    /**
     * Muestra el formulario de login.
     * Si ya hay sesión iniciada, no tiene sentido verlo: va al panel.
     */
    public function login(): void
    {
        if (Auth::verificar()) {
            $this->redirigir('dashboard');
        }

        // Los mensajes viajan en la URL como un CÓDIGO corto (?m=credenciales).
        // Solo se muestran los que están en esta lista: así nadie puede
        // inyectar texto propio en la pantalla (evita XSS y engaños).
        $mensajes = [
            'vacio'        => ['tipo' => 'warning', 'texto' => 'Escribe tu usuario y tu contraseña.'],
            'credenciales' => ['tipo' => 'danger',  'texto' => 'Usuario o contraseña incorrectos.'],
            'salio'        => ['tipo' => 'success', 'texto' => 'Cerraste sesión correctamente.'],
        ];

        $codigo = $_GET['m'] ?? '';
        $alerta = (is_string($codigo) && isset($mensajes[$codigo])) ? $mensajes[$codigo] : null;

        $this->vista('auth/login', [
            'titulo' => 'Iniciar sesión',
            'alerta' => $alerta,
        ],'auth');
    }

    /**
     * Recibe el formulario de login (validación en el SERVIDOR).
     * La validación del navegador es solo comodidad: siempre se repite aquí,
     * porque un atacante puede saltarse el navegador.
     */
    public function autenticar(): void
    {
        // Solo se acepta POST (los datos no deben viajar por la URL)
        if (!$this->esPost()) {
            $this->redirigir('auth/login');
        }

        $this->verificarCsrf();

        // is_string evita errores si alguien envía un arreglo en vez de texto
        $usuario  = is_string($_POST['usuario'] ?? null)  ? trim($_POST['usuario']) : '';
        $password = is_string($_POST['password'] ?? null) ? $_POST['password']      : '';

        // 1. Campos obligatorios
        if ($usuario === '' || $password === '') {
            $this->redirigir('auth/login?m=vacio');
        }

        // 2. Largo máximo (coincide con la base de datos). Mismo mensaje genérico.
        if (strlen($usuario) > 50 || strlen($password) > 255) {
            $this->redirigir('auth/login?m=credenciales');
        }

        // 3. Comprobar credenciales (Auth usa password_verify)
        if (!Auth::intentar($usuario, $password)) {
            $this->redirigir('auth/login?m=credenciales');
        }

        // 4. Acceso correcto: todos entran al panel; allí cada rol
        //    ve solo los módulos que le corresponden.
        $this->redirigir('dashboard');
    }

    /**
     * Cierra la sesión. Solo por POST con token CSRF: así una página ajena
     * no puede cerrarte la sesión con un simple enlace o imagen.
     */
    public function salir(): void
    {
        if (!$this->esPost()) {
            $this->redirigir('dashboard');
        }

        $this->verificarCsrf();
        Auth::cerrarSesion();
        $this->redirigir('auth/login?m=salio');
    }
}