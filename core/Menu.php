<?php
/**
 * Menu: la lista de opciones del sidebar, organizada por rol.
 *
 * Es una clase de apoyo (no hereda de Model ni Controller): solo entrega
 * datos fijos, no toca la base de datos. Por eso sus métodos son estáticos.
 *
 * "disponible" marca si el módulo ya existe en el sistema. Los que faltan
 * (los construiremos en las próximas fases) se muestran pero sin poder
 * hacer clic, con una etiqueta "Pronto". Así el sidebar queda completo
 * desde ya y no hay que tocarlo en cada fase nueva: solo cambiamos
 * "disponible" a true cuando el módulo esté listo.
 */
class Menu
{
    // 'rol': qué roles ven esta opción (según el documento del proyecto)
    private const OPCIONES = [
        ['rol' => ['administrador', 'cajero', 'mesero'],
         'icono' => 'bi-speedometer2', 'nombre' => 'Inicio',
         'ruta' => 'dashboard', 'disponible' => true],

        ['rol' => ['administrador'],
         'icono' => 'bi-graph-up-arrow', 'nombre' => 'Reportes',
         'ruta' => 'reportes', 'disponible' => false],

        ['rol' => ['administrador', 'cajero'],
         'icono' => 'bi-calendar-event', 'nombre' => 'Eventos y entradas',
         'ruta' => 'eventos', 'disponible' => true],

               ['rol' => ['administrador', 'mesero'],
         'icono' => 'bi-grid-3x3-gap', 'nombre' => 'Mesas y reservas',
         'ruta' => 'reservas', 'disponible' => true],

        ['rol' => ['administrador'],
         'icono' => 'bi-cup-straw', 'nombre' => 'Productos',
         'ruta' => 'productos', 'disponible' => true],
    ];

    /**
     * Devuelve solo las opciones que le corresponden a un rol.
     */
    public static function paraRol(string $rol): array
    {
        return array_values(array_filter(
            self::OPCIONES,
            fn(array $opcion): bool => in_array($rol, $opcion['rol'], true)
        ));
    }
}