<?php
/**
 * Modelo Venta: acceso a la tabla "ventas", para los indicadores del dashboard.
 */
class Venta extends Model
{
    protected string $tabla = 'ventas';

    /**
     * Suma el total de las ventas PAGADAS de hoy (las "abiertas" o "anuladas"
     * no cuentan: una cuenta abierta aún no es dinero cobrado).
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
     * Total vendido por día, de los últimos $dias días (incluye hoy).
     * Devuelve SIEMPRE un valor por cada día, aunque no haya habido ventas
     * ese día (con 0.00), para que el gráfico no tenga huecos.
     *
     * Resultado: ['2026-09-27' => 120.00, '2026-09-28' => 0.00, ...]
     */
    public function totalesPorDia(int $dias = 7): array
    {
        // :dias se envía como número entero (PARAM_INT), nunca como texto
        $filas = $this->ejecutar(
            "SELECT DATE(created_at) AS fecha, SUM(total) AS total
               FROM ventas
              WHERE estado = 'pagada'
                AND created_at >= DATE_SUB(CURDATE(), INTERVAL :dias DAY)
              GROUP BY DATE(created_at)",
            [':dias' => $dias - 1]
        )->fetchAll();

        // Pasamos el resultado a un arreglo fácil de consultar: fecha => total
        $porFecha = [];
        foreach ($filas as $fila) {
            $porFecha[$fila['fecha']] = (float) $fila['total'];
        }

        // Rellenamos los días sin ventas con 0, para no dejar huecos en el gráfico
        $resultado = [];
        for ($i = $dias - 1; $i >= 0; $i--) {
            $fecha = date('Y-m-d', strtotime("-{$i} days"));
            $resultado[$fecha] = $porFecha[$fecha] ?? 0.0;
        }

        return $resultado;
    }
}