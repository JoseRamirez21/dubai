<?php
/**
 * Modelo Venta: acceso a la tabla "ventas".
 */
class Venta extends Model
{
    protected string $tabla = 'ventas';

    public const METODOS_PAGO = ['efectivo', 'tarjeta', 'yape', 'plin'];

    /**
     * Suma el total de las ventas PAGADAS de hoy.
     * (Se usa en el dashboard, desde la Fase 2.)
     */
    public function totalHoy(): float
    {
        $fila = $this->ejecutar(
            "SELECT COALESCE(SUM(total), 0) AS total
               FROM ventas
              WHERE estado = 'pagada'
                AND DATE(created_at) = CURDATE()"
        )->fetch();

        return (float) $fila['total'];
    }

    /**
     * Total vendido por día, de los últimos $dias días.
     * (Se usa en el dashboard, desde la Fase 2.)
     */
    public function totalesPorDia(int $dias = 7): array
    {
        $filas = $this->ejecutar(
            "SELECT DATE(created_at) AS fecha, SUM(total) AS total
               FROM ventas
              WHERE estado = 'pagada'
                AND created_at >= DATE_SUB(CURDATE(), INTERVAL :dias DAY)
              GROUP BY DATE(created_at)",
            [':dias' => $dias - 1]
        )->fetchAll();

        $porFecha = [];
        foreach ($filas as $fila) {
            $porFecha[$fila['fecha']] = (float) $fila['total'];
        }

        $resultado = [];
        for ($i = $dias - 1; $i >= 0; $i--) {
            $fecha = date('Y-m-d', strtotime("-{$i} days"));
            $resultado[$fecha] = $porFecha[$fecha] ?? 0.0;
        }

        return $resultado;
    }

    /**
     * Las cuentas que siguen ABIERTAS (sin cobrar todavía), con el número
     * de mesa si la tiene (una venta de barra no tiene mesa: mesa_id es NULL).
     */
    public function abiertas(): array
    {
        return $this->ejecutar(
            "SELECT v.*, m.numero AS mesa_numero, m.zona AS mesa_zona
               FROM ventas v
               LEFT JOIN mesas m ON m.id = v.mesa_id
              WHERE v.estado = 'abierta'
              ORDER BY v.created_at DESC"
        )->fetchAll();
    }
    /**
     * ¿Esa mesa ya tiene una cuenta abierta? Evita abrir dos cuentas
     * distintas para la misma mesa al mismo tiempo.
     */
    public function abiertaPorMesa(int $mesaId): ?array
    {
        $fila = $this->ejecutar(
            "SELECT * FROM ventas WHERE mesa_id = :mesa_id AND estado = 'abierta' LIMIT 1",
            [':mesa_id' => $mesaId]
        )->fetch();

        return $fila ?: null;
    }
    /**
     * Abre una cuenta nueva (mesa_id puede ser null: venta en barra).
     * Empieza en 0 porque todavía no tiene productos.
     */
    public function abrir(array $datos): int
    {
        $this->ejecutar(
            "INSERT INTO ventas (mesa_id, usuario_id, evento_id, total, metodo_pago, estado)
             VALUES (:mesa_id, :usuario_id, :evento_id, 0, NULL, 'abierta')",
            [
                ':mesa_id'    => $datos['mesa_id'],
                ':usuario_id' => $datos['usuario_id'],
                ':evento_id'  => $datos['evento_id'],
            ]
        );

        return (int) $this->db->lastInsertId();
    }

    /**
     * Recalcula el total de la venta sumando TODOS sus detalles.
     * Se llama después de agregar un producto: así el total nunca se
     * desincroniza de la suma real de lo que lleva la cuenta.
     */
    public function recalcularTotal(int $ventaId): void
    {
        // OJO: con EMULATE_PREPARES en false (Paso 4), MySQL prepara la
        // consulta "de verdad", y ahí cada ":marcador" necesita SU PROPIO
        // valor, aunque se llamen igual. Por eso uso :id1 y :id2 en vez de
        // reutilizar :id dos veces (eso sí funcionaría con emulación, pero
        // no con sentencias preparadas nativas).
        $this->ejecutar(
            "UPDATE ventas SET total = (
                SELECT COALESCE(SUM(subtotal), 0) FROM detalle_venta WHERE venta_id = :id1
             ) WHERE id = :id2",
            [':id1' => $ventaId, ':id2' => $ventaId]
        );
    }

    /**
     * Marca la venta como pagada con el método indicado.
     */
    public function cobrar(int $id, string $metodoPago): bool
    {
        $stmt = $this->ejecutar(
            "UPDATE ventas SET estado = 'pagada', metodo_pago = :metodo WHERE id = :id",
            [':metodo' => $metodoPago, ':id' => $id]
        );

        return $stmt->rowCount() > 0;
    }
}