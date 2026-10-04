<?php
/**
 * Modelo Producto: acceso a la tabla "productos".
 */
class Producto extends Model
{
    protected string $tabla = 'productos';

    /**
     * Productos activos cuyo stock llegó al mínimo o menos (alerta de reposición).
     */
    public function stockBajo(): array
    {
        return $this->ejecutar(
            "SELECT id, nombre, stock, stock_minimo
               FROM productos
              WHERE estado = 'activo'
                AND stock <= stock_minimo
              ORDER BY nombre"
        )->fetchAll();
    }

    /**
     * Los $limite productos más vendidos, sumando las cantidades de todas
     * las ventas PAGADAS (una venta "abierta" o "anulada" no cuenta).
     */
    public function masVendidos(int $limite = 5): array
    {
        // $limite nunca viene del usuario (siempre lo pone el código), así que
        // es seguro convertirlo a entero e insertarlo directo en el LIMIT:
        // PDO no permite enviar el LIMIT como parámetro preparado de forma confiable.
        $limite = max(1, $limite);

        return $this->ejecutar(
            "SELECT p.nombre, SUM(d.cantidad) AS cantidad
               FROM detalle_venta d
               JOIN ventas v    ON v.id = d.venta_id
               JOIN productos p ON p.id = d.producto_id
              WHERE v.estado = 'pagada'
              GROUP BY p.id, p.nombre
              ORDER BY cantidad DESC
              LIMIT {$limite}"
        )->fetchAll();
    }
}