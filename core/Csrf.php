<?php
/**
 * Csrf: protección contra ataques CSRF (Cross-Site Request Forgery).
 *
 * El ataque: una página maliciosa hace que tu navegador (con tu sesión abierta)
 * envíe un formulario a nuestro sistema sin que tú lo sepas.
 *
 * La defensa: cada formulario lleva un TOKEN secreto y aleatorio, guardado también
 * en la sesión. Al recibir el POST, comparamos ambos. Una página ajena no puede
 * conocer el token, así que su formulario falso es rechazado.
 */
class Csrf
{
    /**
     * Devuelve el token de la sesión; si aún no existe, lo crea.
     * random_bytes genera números impredecibles (seguros para criptografía).
     */
    public static function token(): string
    {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    /**
     * Devuelve el <input> oculto que se coloca dentro de cada formulario.
     * Uso en una vista:  <?= Csrf::campo() ?>
     */
    public static function campo(): string
    {
        return '<input type="hidden" name="csrf_token" value="'
             . htmlspecialchars(self::token()) . '">';
    }

    /**
     * Comprueba que el token recibido coincida con el de la sesión.
     * hash_equals compara en tiempo constante (evita ataques por medición de tiempo).
     * is_string protege si alguien envía algo raro (como un arreglo) en lugar de texto.
     */
    public static function validar($token): bool
    {
        return is_string($token)
            && isset($_SESSION['csrf_token'])
            && hash_equals($_SESSION['csrf_token'], $token);
    }
}