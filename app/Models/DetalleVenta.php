<?php
/**
 * Modelo DetalleVenta: acceso a la tabla "detalle_venta"
 * (las líneas de productos dentro de una venta).
 */
class DetalleVenta extends Model
{
    protected string $tabla = 'detalle_venta';

    /**
     * Agrega un producto a una venta. precio_unitario se pasa ya calculado
     * (es una "fotografía" del precio del producto en este momento, igual
     * que hicimos con las entradas: si el precio cambia después, esta
     * línea ya vendida no se altera).
     */
    public function agregar(array $datos): int
    {
        $this->ejecutar(
            "INSERT INTO detalle_venta (venta_id, producto_id, cantidad, precio_unitario, subtotal)
             VALUES (:venta_id, :producto_id, :cantidad, :precio_unitario, :subtotal)",
            [
                ':venta_id'        => $datos['venta_id'],
                ':producto_id'     => $datos['producto_id'],
                ':cantidad'        => $datos['cantidad'],
                ':precio_unitario' => $datos['precio_unitario'],
                ':subtotal'        => $datos['subtotal'],
            ]
        );

        return (int) $this->db->lastInsertId();
    }

    /**
     * Las líneas de una venta, con el nombre del producto (join), para mostrarlas.
     */
    public function porVenta(int $ventaId): array
    {
        return $this->ejecutar(
            "SELECT d.*, p.nombre AS producto_nombre
               FROM detalle_venta d
               JOIN productos p ON p.id = d.producto_id
              WHERE d.venta_id = :venta_id
              ORDER BY d.id",
            [':venta_id' => $ventaId]
        )->fetchAll();
    }
}