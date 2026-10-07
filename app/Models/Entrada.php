<?php
/**
 * Modelo Entrada: acceso a la tabla "entradas".
 */
class Entrada extends Model
{
    protected string $tabla = 'entradas';

    public const TIPOS = ['general', 'vip'];

    /**
     * Cuenta las entradas vendidas HOY (sin contar las anuladas).
     * (Se usa en el dashboard, desde la Fase 2.)
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

    /**
     * Cuenta cuántas entradas se han vendido para UN evento (para el aforo).
     * Una entrada "anulada" no cuenta: el cupo que dejó se considera libre otra vez.
     */
    public function contarVendidasPorEvento(int $eventoId): int
    {
        $fila = $this->ejecutar(
            "SELECT COUNT(*) AS total
               FROM entradas
              WHERE evento_id = :evento_id
                AND estado IN ('vendida', 'usada')",
            [':evento_id' => $eventoId]
        )->fetch();

        return (int) $fila['total'];
    }

    /**
     * Todas las entradas vendidas de un evento (para listarlas en pantalla).
     */
    public function porEvento(int $eventoId): array
    {
        return $this->ejecutar(
            "SELECT * FROM entradas WHERE evento_id = :evento_id ORDER BY fecha_venta DESC",
            [':evento_id' => $eventoId]
        )->fetchAll();
    }

    /**
     * Vende una entrada: genera un código único y la guarda.
     * Devuelve el id y el código, para poder mostrar el ticket enseguida.
     */
    public function crear(array $datos): array
    {
        $codigo = $this->generarCodigoUnico();

        $this->ejecutar(
            "INSERT INTO entradas (evento_id, usuario_id, tipo, cliente_nombre, precio, codigo, estado, fecha_venta)
             VALUES (:evento_id, :usuario_id, :tipo, :cliente_nombre, :precio, :codigo, 'vendida', NOW())",
            [
                ':evento_id'      => $datos['evento_id'],
                ':usuario_id'     => $datos['usuario_id'],
                ':tipo'           => $datos['tipo'],
                ':cliente_nombre' => $datos['cliente_nombre'],
                ':precio'         => $datos['precio'],
                ':codigo'         => $codigo,
            ]
        );

        return ['id' => (int) $this->db->lastInsertId(), 'codigo' => $codigo];
    }

    /**
     * Genera un código que no exista todavía en la tabla.
     * random_bytes es la misma función segura que usamos para el token CSRF.
     */
    private function generarCodigoUnico(): string
    {
        do {
            $codigo = 'DUB-' . strtoupper(bin2hex(random_bytes(4)));

            $fila = $this->ejecutar(
                "SELECT COUNT(*) AS total FROM entradas WHERE codigo = :codigo",
                [':codigo' => $codigo]
            )->fetch();
        } while ((int) $fila['total'] > 0);

        return $codigo;
    }
}