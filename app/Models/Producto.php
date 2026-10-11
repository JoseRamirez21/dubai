<?php
/**
 * Modelo Producto: acceso a la tabla "productos".
 *
 * Decisión de diseño: "eliminar" un producto NO borra la fila (DELETE).
 * En la base de datos, "productos" tiene una columna "estado" (activo/inactivo),
 * igual que "usuarios". Además, si un producto ya fue vendido alguna vez,
 * existe una fila en "detalle_venta" que apunta a él (FOREIGN KEY), y MySQL
 * RECHAZARÍA el borrado para no dejar ventas con un producto fantasma.
 * Por eso "eliminar" en este sistema significa "pasar a inactivo": el
 * producto deja de ofrecerse, pero el historial de ventas queda intacto.
 */
class Producto extends Model
{
    protected string $tabla = 'productos';

    // Lista fija de categorías válidas (coincide con el ENUM de la base de datos)
    public const CATEGORIAS = ['cerveza', 'coctel', 'licor', 'botella', 'gaseosa', 'snack'];

    /**
     * Todos los productos, con el más reciente primero.
     * (Sobrescribe el todos() genérico del padre solo para fijar el orden;
     * sigue siendo la misma idea: traer todas las filas de la tabla.)
     */
    public function todos(): array
    {
        return $this->ejecutar("SELECT * FROM productos ORDER BY nombre")->fetchAll();
    }

    /**
     * Inserta un producto nuevo. Devuelve el id que le asignó la base de datos.
     * $datos ya debe venir validado por el controlador (este método no valida,
     * solo guarda: esa es la responsabilidad de un Modelo).
     */
    public function crear(array $datos): int
    {
        $this->ejecutar(
            "INSERT INTO productos (nombre, categoria, precio, stock, stock_minimo, estado)
             VALUES (:nombre, :categoria, :precio, :stock, :stock_minimo, 'activo')",
            [
                ':nombre'       => $datos['nombre'],
                ':categoria'    => $datos['categoria'],
                ':precio'       => $datos['precio'],
                ':stock'        => $datos['stock'],
                ':stock_minimo' => $datos['stock_minimo'],
            ]
        );

        // lastInsertId() devuelve el id autoincremental que MySQL le dio a la fila
        return (int) $this->db->lastInsertId();
    }

    /**
     * Actualiza los datos de un producto existente (no toca su estado).
     */
    public function actualizar(int $id, array $datos): bool
    {
        $stmt = $this->ejecutar(
            "UPDATE productos
                SET nombre = :nombre, categoria = :categoria, precio = :precio,
                    stock = :stock, stock_minimo = :stock_minimo
              WHERE id = :id",
            [
                ':nombre'       => $datos['nombre'],
                ':categoria'    => $datos['categoria'],
                ':precio'       => $datos['precio'],
                ':stock'        => $datos['stock'],
                ':stock_minimo' => $datos['stock_minimo'],
                ':id'           => $id,
            ]
        );

        return $stmt->rowCount() > 0;
    }

    /**
     * Activa o desactiva un producto. $estado debe ser 'activo' o 'inactivo'.
     * Esta es la operación que el sistema usa como "eliminar" / "restaurar".
     */
    public function cambiarEstado(int $id, string $estado): bool
    {
        $stmt = $this->ejecutar(
            "UPDATE productos SET estado = :estado WHERE id = :id",
            [':estado' => $estado, ':id' => $id]
        );

        return $stmt->rowCount() > 0;
    }
    /**
     * Descuenta stock de forma SEGURA ante concurrencia: la condición
     * "stock >= :cantidad" va DENTRO del mismo UPDATE, no en un SELECT
     * aparte. Así, aunque dos ventas se cobren al mismo tiempo, MySQL
     * nunca deja el stock en negativo: si no alcanza, el UPDATE no
     * afecta ninguna fila y rowCount() da 0.
     *
     * Devuelve true si se pudo descontar, false si no había stock suficiente.
     */
    public function descontarStock(int $id, int $cantidad): bool
    {
        // Igual que en Venta::recalcularTotal(): con EMULATE_PREPARES en
        // false, cada ":marcador" necesita su propio valor aunque se
        // repita el nombre. Por eso :cant1 y :cant2, ambos con $cantidad.
        $stmt = $this->ejecutar(
            "UPDATE productos SET stock = stock - :cant1
              WHERE id = :id AND stock >= :cant2",
            [':cant1' => $cantidad, ':id' => $id, ':cant2' => $cantidad]
        );

        return $stmt->rowCount() > 0;
    }
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