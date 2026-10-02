<?php
/**
 * Auth: todo lo relacionado con la sesión y la autenticación.
 *
 *  - iniciarSesion(): arranca la sesión con ajustes seguros.
 *  - intentar():      valida usuario y contraseña (password_verify).
 *  - verificar():     ¿hay alguien con sesión iniciada?
 *  - usuario()/rol(): datos de quien está conectado.
 *  - tieneRol():      ¿su rol está entre los permitidos?
 *  - cerrarSesion():  cierra la sesión por completo.
 *
 * Son métodos estáticos porque la sesión es una sola por petición.
 */
class Auth
{
    // Nombre de la clave dentro de $_SESSION donde guardamos al usuario
    private const CLAVE = 'usuario';

    /**
     * Arranca la sesión de forma segura. Se llama UNA vez desde index.php.
     */
    public static function iniciarSesion(): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            return;
        }

        // Nombre propio para no confundirse con sesiones de otros proyectos
        session_name('DUBAI_SESID');

        // Solo aceptar IDs de sesión que el servidor creó (evita fijación de sesión)
        ini_set('session.use_strict_mode', '1');
        // El ID viaja solo en cookie, nunca en la URL
        ini_set('session.use_only_cookies', '1');
        ini_set('session.gc_maxlifetime', (string) SESSION_LIFETIME);

        session_set_cookie_params([
            'lifetime' => 0,                        // la cookie muere al cerrar el navegador
            'path'     => '/',
            'secure'   => !empty($_SERVER['HTTPS']), // solo por HTTPS si el sitio lo usa
            'httponly' => true,                     // JavaScript NO puede leer la cookie (frena robo por XSS)
            'samesite' => 'Lax',                    // el navegador no la envía en peticiones de otros sitios
        ]);

        session_start();

        // Cierre por inactividad: si pasó demasiado tiempo sin usar el sistema,
        // se vacía la sesión y se cambia el ID.
        if (isset($_SESSION['ultima_actividad'])
            && (time() - $_SESSION['ultima_actividad']) > SESSION_LIFETIME) {
            $_SESSION = [];
            session_regenerate_id(true);
        }
        $_SESSION['ultima_actividad'] = time();
    }

    /**
     * Valida las credenciales. Si son correctas, guarda al usuario en la sesión.
     * Devuelve true o false. No dice cuál de los dos datos falló (a propósito).
     */
    public static function intentar(string $usuario, string $password): bool
    {
        $modelo = new Usuario();
        $fila   = $modelo->buscarPorUsuario($usuario);

        // Usuario inexistente o desactivado
        if ($fila === null || $fila['estado'] !== 'activo') {
            return false;
        }

        // password_verify compara la contraseña escrita contra el HASH guardado
        if (!password_verify($password, $fila['password'])) {
            return false;
        }

        // Cambiamos el ID de sesión al iniciar: así un ID previo (posiblemente
        // plantado por un atacante) deja de servir. Se llama "fijación de sesión".
        session_regenerate_id(true);

        // Guardamos solo lo necesario. NUNCA la contraseña ni su hash.
        $_SESSION[self::CLAVE] = [
            'id'      => (int) $fila['id'],
            'nombre'  => $fila['nombre'],
            'usuario' => $fila['usuario'],
            'rol'     => $fila['rol'],
        ];

        return true;
    }

    /** ¿Hay una sesión iniciada? */
    public static function verificar(): bool
    {
        return isset($_SESSION[self::CLAVE]);
    }

    /** Datos del usuario conectado (o null si no hay nadie). */
    public static function usuario(): ?array
    {
        return $_SESSION[self::CLAVE] ?? null;
    }

    /** Rol del usuario conectado (o null). */
    public static function rol(): ?string
    {
        return $_SESSION[self::CLAVE]['rol'] ?? null;
    }

    /** ¿El rol del usuario está dentro de la lista de roles permitidos? */
    public static function tieneRol(array $rolesPermitidos): bool
    {
        return self::verificar() && in_array(self::rol(), $rolesPermitidos, true);
    }

    /**
     * Cierra la sesión por completo: vacía los datos, borra la cookie
     * y destruye la sesión en el servidor.
     */
    public static function cerrarSesion(): void
    {
        $_SESSION = [];

        if (ini_get('session.use_cookies')) {
            $p = session_get_cookie_params();
            setcookie(session_name(), '', [
                'expires'  => time() - 42000,
                'path'     => $p['path'],
                'secure'   => $p['secure'],
                'httponly' => $p['httponly'],
                'samesite' => $p['samesite'],
            ]);
        }

        session_destroy();
    }
}