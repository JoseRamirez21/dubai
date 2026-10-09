<?php
/**
 * EntradasController: venta de entradas para un evento y el ticket imprimible.
 *
 * Vender entradas es tarea de administrador Y cajero (tu documento dice que
 * el cajero "maneja entradas y ventas"). Administrar el EVENTO en sí
 * (crear, editar, cambiar estado) sigue siendo solo del administrador, en
 * EventosController.
 */
class EntradasController extends Controller
{
    /**
     * Vende una entrada para un evento. $eventoId viene de la URL:
     * /entradas/vender/3
     *
     * Supuesto del sistema: una entrada VIP cuesta el DOBLE que una general
     * (coincide con los datos de ejemplo de la Fase 1: evento 1 cobraba
     * S/ 40 general y S/ 80 VIP).
     */
    public function vender(string $eventoId): void
    {
        $this->requerirRol(['administrador', 'cajero']);

        if (!$this->esPost()) {
            $this->redirigir('eventos');
        }
        $this->verificarCsrf();

        if (!ctype_digit($eventoId)) {
            (new ErrorController())->noEncontrado();
        }
        $eventoId = (int) $eventoId;

        $modeloEvento = new Evento();
        $evento = $modeloEvento->buscar($eventoId);
        if ($evento === null) {
            (new ErrorController())->noEncontrado();
        }

        // ---- Validar lo que escribió el usuario ----
        $tipo    = (string) ($_POST['tipo'] ?? '');
        $cliente = trim((string) ($_POST['cliente_nombre'] ?? ''));

        if (!in_array($tipo, Entrada::TIPOS, true) || $cliente === '' || strlen($cliente) > 100) {
            $this->redirigir('eventos?m=error_datos');
        }

        // ---- Reglas de negocio: ¿se puede vender ESTA entrada ahora mismo? ----
        if ($evento['estado'] === 'cerrado') {
            $this->redirigir('eventos?m=evento_cerrado');
        }

        $modeloEntrada = new Entrada();
        $vendidas = $modeloEntrada->contarVendidasPorEvento($eventoId);
        if ($vendidas >= (int) $evento['aforo']) {
            $this->redirigir('eventos?m=aforo_lleno');
        }

        // El precio se calcula aquí, con el precio ACTUAL del evento. Así, si el
        // precio del evento cambia después, las entradas ya vendidas no se alteran.
        $precio = (float) $evento['precio_entrada'];
        if ($tipo === 'vip') {
            $precio *= 2;
        }

        $resultado = $modeloEntrada->crear([
            'evento_id'      => $eventoId,
            'usuario_id'     => Auth::usuario()['id'],
            'tipo'           => $tipo,
            'cliente_nombre' => $cliente,
            'precio'         => $precio,
        ]);

        // Vamos directo al ticket: es lo que el cajero necesita imprimir ya mismo.
        $this->redirigir('entradas/ticket/' . $resultado['id']);
    }

    /**
     * Muestra el ticket de una entrada ya vendida, listo para imprimir.
     */
    public function ticket(string $id): void
    {
        $this->requerirRol(['administrador', 'cajero']);

        if (!ctype_digit($id)) {
            (new ErrorController())->noEncontrado();
        }
        $id = (int) $id;

        $modeloEntrada = new Entrada();
        $entrada = $modeloEntrada->buscar($id);
        if ($entrada === null) {
            (new ErrorController())->noEncontrado();
        }

        $evento = (new Evento())->buscar((int) $entrada['evento_id']);

        // Layout 'auth': pantalla limpia, sin sidebar ni barra superior.
        // Es la que mejor se ve al imprimir (nada de menús en el papel).
        $this->vista('entradas/ticket', [
            'titulo'  => 'Ticket de entrada',
            'entrada' => $entrada,
            'evento'  => $evento,
        ], 'auth');
    }
}