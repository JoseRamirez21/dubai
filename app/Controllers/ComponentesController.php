<?php
/**
 * CONTROLADOR TEMPORAL solo para ver los componentes del Paso 3
 * (tabla con búsqueda/paginación, modal y SweetAlert2) con datos reales.
 * Se borra al terminar la Fase 2, cuando cada módulo use los componentes
 * con sus propios datos y acciones.
 */
class ComponentesController extends Controller
{
    public function index(): void
    {
        $this->requerirLogin();

        $modelo = new Mesa();

        $this->vista('componentes/demo', [
            'titulo' => 'Componentes',
            'mesas'  => $modelo->todos(),
        ]);
    }
}