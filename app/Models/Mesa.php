<?php
/**
 * Modelo Mesa: acceso a la tabla "mesas".
 * Las mesas ya existen desde los datos de ejemplo (Paso 6); aquí no se
 * crean ni se borran, solo se consultan y se cambia su estado.
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
        /**
     * Todas las mesas ordenadas por zona y número (para el mapa visual).
     */
    public function todas(): array
    {
        return $this->ejecutar(
            "SELECT * FROM mesas ORDER BY zona, numero"
        )->fetchAll();
    }

    /**
     * Cambia el estado de una mesa (libre / reservada / ocupada).
     * Lo usa ReservasController dentro de una transacción, junto con los
     * cambios en la tabla "reservas", para que las dos cosas ocurran juntas.
     */
    public function actualizarEstado(int $id, string $estado): bool
    {
        $stmt = $this->ejecutar(
            "UPDATE mesas SET estado = :estado WHERE id = :id",
            [':estado' => $estado, ':id' => $id]
        );

        return $stmt->rowCount() > 0;
    }
}   