<?php
/**
 * Modelo Evento: acceso a la tabla "eventos".
 */
class Evento extends Model
{
    protected string $tabla = 'eventos';

    public const ESTADOS = ['programado', 'en_curso', 'cerrado'];

    /**
     * Todos los eventos, los más recientes primero.
     */
    public function todos(): array
    {
        return $this->ejecutar("SELECT * FROM eventos ORDER BY fecha DESC")->fetchAll();
    }

    /**
     * Eventos que todavía aceptan reservas (no están cerrados).
     * Se usa para el formulario de "Nueva reserva": no tiene sentido
     * reservar una mesa para un evento que ya terminó.
     */
    public function disponibles(): array
    {
        return $this->ejecutar(
            "SELECT * FROM eventos WHERE estado != 'cerrado' ORDER BY fecha"
        )->fetchAll();
    }

    /**
     * Todos los eventos, con el número de entradas vendidas de cada uno
     * (columna "vendidas"), para mostrar el aforo sin hacer una consulta
     * aparte por cada evento.
     *
     * LEFT JOIN: trae el evento aunque todavía no tenga ninguna entrada
     * vendida (si usara INNER JOIN, esos eventos desaparecerían de la lista).
     * El CASE cuenta solo 'vendida' o 'usada'; una entrada 'anulada' no suma.
     */
    public function todosConAforo(): array
    {
        return $this->ejecutar(
            "SELECT e.*,
                    COALESCE(SUM(CASE WHEN t.estado IN ('vendida','usada') THEN 1 ELSE 0 END), 0) AS vendidas
               FROM eventos e
               LEFT JOIN entradas t ON t.evento_id = e.id
              GROUP BY e.id
              ORDER BY e.fecha DESC"
        )->fetchAll();
    }

    public function crear(array $datos): int
    {
        $this->ejecutar(
            "INSERT INTO eventos (nombre, descripcion, fecha, hora_inicio, dj_artista, precio_entrada, aforo, estado)
             VALUES (:nombre, :descripcion, :fecha, :hora_inicio, :dj_artista, :precio_entrada, :aforo, 'programado')",
            [
                ':nombre'         => $datos['nombre'],
                ':descripcion'    => $datos['descripcion'],
                ':fecha'          => $datos['fecha'],
                ':hora_inicio'    => $datos['hora_inicio'],
                ':dj_artista'     => $datos['dj_artista'],
                ':precio_entrada' => $datos['precio_entrada'],
                ':aforo'          => $datos['aforo'],
            ]
        );

        return (int) $this->db->lastInsertId();
    }

    public function actualizar(int $id, array $datos): bool
    {
        $stmt = $this->ejecutar(
            "UPDATE eventos
                SET nombre = :nombre, descripcion = :descripcion, fecha = :fecha,
                    hora_inicio = :hora_inicio, dj_artista = :dj_artista,
                    precio_entrada = :precio_entrada, aforo = :aforo
              WHERE id = :id",
            [
                ':nombre'         => $datos['nombre'],
                ':descripcion'    => $datos['descripcion'],
                ':fecha'          => $datos['fecha'],
                ':hora_inicio'    => $datos['hora_inicio'],
                ':dj_artista'     => $datos['dj_artista'],
                ':precio_entrada' => $datos['precio_entrada'],
                ':aforo'          => $datos['aforo'],
                ':id'             => $id,
            ]
        );

        return $stmt->rowCount() > 0;
    }

    /**
     * Cambia el estado del evento: 'programado', 'en_curso' o 'cerrado'.
     * No es un borrado lógico como en Producto: aquí el "estado" representa
     * en qué momento de su vida está el evento, no si está activo o no.
     */
    public function cambiarEstado(int $id, string $estado): bool
    {
        $stmt = $this->ejecutar(
            "UPDATE eventos SET estado = :estado WHERE id = :id",
            [':estado' => $estado, ':id' => $id]
        );

        return $stmt->rowCount() > 0;
    }
}