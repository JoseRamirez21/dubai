<?php
/**
 * Modelo Reserva: acceso a la tabla "reservas".
 */
class Reserva extends Model
{
    protected string $tabla = 'reservas';

    public const ESTADOS = ['activa', 'cancelada', 'cumplida'];

    /**
     * Todas las reservas ACTIVAS, con el número de mesa y su zona
     * (join con "mesas"), para mostrarlas en pantalla sin otra consulta.
     */
       public function activas(): array
    {
        return $this->ejecutar(
            "SELECT r.*, m.numero AS mesa_numero, m.zona AS mesa_zona, e.nombre AS evento_nombre
               FROM reservas r
               JOIN mesas m  ON m.id = r.mesa_id
               JOIN eventos e ON e.id = r.evento_id
              WHERE r.estado = 'activa'
              ORDER BY r.created_at DESC"
        )->fetchAll();
    }

    public function crear(array $datos): int
    {
        $this->ejecutar(
            "INSERT INTO reservas (evento_id, mesa_id, cliente_nombre, cliente_telefono, cantidad_personas, garantia, estado)
             VALUES (:evento_id, :mesa_id, :cliente_nombre, :cliente_telefono, :cantidad_personas, :garantia, 'activa')",
            [
                ':evento_id'         => $datos['evento_id'],
                ':mesa_id'           => $datos['mesa_id'],
                ':cliente_nombre'    => $datos['cliente_nombre'],
                ':cliente_telefono'  => $datos['cliente_telefono'],
                ':cantidad_personas' => $datos['cantidad_personas'],
                ':garantia'          => $datos['garantia'],
            ]
        );

        return (int) $this->db->lastInsertId();
    }

    /**
     * Cambia el estado de la reserva (activa / cancelada / cumplida).
     */
    public function cambiarEstado(int $id, string $estado): bool
    {
        $stmt = $this->ejecutar(
            "UPDATE reservas SET estado = :estado WHERE id = :id",
            [':estado' => $estado, ':id' => $id]
        );

        return $stmt->rowCount() > 0;
    }
}