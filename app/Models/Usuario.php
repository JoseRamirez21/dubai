<?php
/**
 * Modelo Usuario: acceso a la tabla "usuarios".
 * Hereda de Model: ya trae todos(), buscar() y eliminar().
 */
class Usuario extends Model
{
    protected string $tabla = 'usuarios';

    /**
     * Busca un usuario por su nombre de usuario (para el login).
     * Sentencia preparada: :usuario viaja aparte, no se puede inyectar SQL.
     */
    public function buscarPorUsuario(string $usuario): ?array
    {
        $fila = $this->ejecutar(
            'SELECT id, nombre, usuario, password, rol, estado
               FROM usuarios
              WHERE usuario = :usuario
              LIMIT 1',
            [':usuario' => $usuario]
        )->fetch();

        return $fila ?: null;
    }
}