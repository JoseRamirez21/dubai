<?php
/**
 * Modelo Mesa (adelanto temporal, solo para el Paso 3).
 * En la Fase 5 este modelo crecerá con crear(), actualizar(), etc.
 */
class Mesa extends Model
{
    protected string $tabla = 'mesas';
     /**
     * Cuenta cuántas mesas están en un estado dado (ej. 'ocupada').
     */
    public function contarPorEstado(string $estado): int
    {
        $fila = $this->ejecutar(
            "SELECT COUNT(*) AS total FROM mesas WHERE estado = :estado",
            [':estado' => $estado]
        )->fetch();

        return (int) $fila['total'];
    }
}   