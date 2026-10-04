<?php
/**
 * Modelo Entrada: acceso a la tabla "entradas".
 */
class Entrada extends Model
{
    protected string $tabla = 'entradas';

    /**
     * Cuenta las entradas vendidas HOY (sin contar las anuladas).
     * "vendida" o "usada" cuentan como venta real; "anulada" no.
     */
    public function vendidasHoy(): int
    {
        $fila = $this->ejecutar(
            "SELECT COUNT(*) AS total
               FROM entradas
              WHERE estado IN ('vendida', 'usada')
                AND DATE(fecha_venta) = CURDATE()"
        )->fetch();

        return (int) $fila['total'];
    }
}