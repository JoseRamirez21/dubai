/**
 * app.js: comportamientos comunes del sistema (JavaScript vanilla, sin librerías).
 *  1. Validación de formularios en el navegador.
 *  2. Sidebar en celular (abrir, cerrar, overlay).
 *  3. Mostrar / ocultar contraseña.
 *  4. Dubai: helpers de SweetAlert2 (confirmar y avisar) con el estilo del sistema.
 *  5. Dubai.tabla: tabla con búsqueda y paginación en el navegador (JS vanilla).
 */
document.addEventListener('DOMContentLoaded', function () {

    // ---- 1. Validación en cliente ----
    // Todo formulario con la clase "needs-validation" se revisa antes de enviarse.
    document.querySelectorAll('form.needs-validation').forEach(function (form) {
        form.addEventListener('submit', function (evento) {

            // Quita espacios al inicio y al final en los campos marcados con data-trim
            form.querySelectorAll('[data-trim]').forEach(function (campo) {
                campo.value = campo.value.trim();
            });

            // checkValidity() revisa required, maxlength, etc. de cada campo
            if (!form.checkValidity()) {
                evento.preventDefault();   // no envía el formulario
                evento.stopPropagation();

                // Lleva el cursor al primer campo con error
                var primero = form.querySelector(':invalid');
                if (primero) { primero.focus(); }
            }

            // Bootstrap usa esta clase para mostrar los mensajes de error
            form.classList.add('was-validated');
        });
    });

    // ---- 2. Sidebar en celular (abrir, cerrar, overlay) ----
    var sidebar  = document.getElementById('sidebarDubai');
    var overlay  = document.getElementById('sidebarOverlay');
    var boton    = document.getElementById('btnAbrirMenu');

    function abrirSidebar() {
        sidebar.classList.add('abierto');
        overlay.classList.add('visible');
        boton.setAttribute('aria-expanded', 'true');
        document.body.classList.add('sin-scroll');   // evita que el fondo se desplace
    }

    function cerrarSidebar() {
        sidebar.classList.remove('abierto');
        overlay.classList.remove('visible');
        boton.setAttribute('aria-expanded', 'false');
        document.body.classList.remove('sin-scroll');
    }

    if (boton && sidebar && overlay) {
        boton.addEventListener('click', function () {
            sidebar.classList.contains('abierto') ? cerrarSidebar() : abrirSidebar();
        });

        // Clic en el fondo oscuro: cierra el menú
        overlay.addEventListener('click', cerrarSidebar);

        // Tecla Escape: cierra el menú
        document.addEventListener('keydown', function (evento) {
            if (evento.key === 'Escape') { cerrarSidebar(); }
        });

        // Al elegir una opción del menú en celular, el menú se cierra solo
        sidebar.querySelectorAll('.sidebar-link').forEach(function (enlace) {
            enlace.addEventListener('click', cerrarSidebar);
        });
    }

    // ---- 3. Mostrar / ocultar contraseña ----
    document.querySelectorAll('[data-toggle-password]').forEach(function (boton) {
        boton.addEventListener('click', function () {
            var campo   = document.querySelector(boton.dataset.togglePassword);
            var mostrar = campo.type === 'password';

            campo.type = mostrar ? 'text' : 'password';
            boton.setAttribute('aria-pressed', mostrar ? 'true' : 'false');
            boton.setAttribute('aria-label', mostrar ? 'Ocultar contraseña' : 'Mostrar contraseña');
            boton.querySelector('i').className = mostrar ? 'bi bi-eye-slash' : 'bi bi-eye';
        });
    });
});

/**
 * Dubai: funciones de ayuda reutilizables en TODO el sistema.
 * Viven fuera del DOMContentLoaded porque los controladores de cada módulo
 * las llamarán después, al reaccionar a un clic (por ejemplo, "Eliminar").
 */
var Dubai = {

    /**
     * Toast: avisito que aparece arriba y se cierra solo. Para confirmar una
     * acción ya hecha ("Producto guardado").
     * tipo: 'success' | 'error' | 'warning' | 'info'
     */
    toast: function (tipo, mensaje) {
        Swal.fire({
            toast: true,
            position: 'top-end',
            icon: tipo,
            title: mensaje,
            showConfirmButton: false,
            timer: 2600,
            timerProgressBar: true
        });
    },

    /**
     * Confirmar: pregunta antes de una acción que no se puede deshacer
     * (por ejemplo, eliminar). Devuelve una Promise: true si aceptó.
     *
     * Uso típico:
     *   Dubai.confirmar('¿Eliminar este producto?').then(function (si) {
     *       if (si) { ...enviar el formulario o la petición... }
     *   });
     */
    confirmar: function (mensaje, textoBoton) {
        return Swal.fire({
            title: mensaje,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: textoBoton || 'Sí, continuar',
            cancelButtonText: 'Cancelar',
            reverseButtons: true,
            focusCancel: true   // por seguridad, el foco inicial NO es el botón destructivo
        }).then(function (resultado) {
            return resultado.isConfirmed;
        });
    },

    /**
     * tabla: convierte una <table> normal en una tabla con buscador y
     * paginación, sin recargar la página. No usa ninguna librería externa.
     *
     * Uso: Dubai.tabla('#miTabla', { porPagina: 8 });
     * Requiere en el HTML, junto a la tabla, un <input data-tabla-buscar>
     * (opcional) para el buscador.
     */
    tabla: function (selectorTabla, opciones) {
        var config = Object.assign({ porPagina: 8 }, opciones || {});
        var tabla  = document.querySelector(selectorTabla);
        if (!tabla) { return; }

        var cuerpo         = tabla.querySelector('tbody');
        var filas           = Array.prototype.slice.call(cuerpo.querySelectorAll('tr'));
        var buscador        = document.querySelector('[data-tabla-buscar="' + selectorTabla + '"]');
        var paginacion       = document.querySelector('[data-tabla-paginacion="' + selectorTabla + '"]');
        var paginaActual     = 1;

        function filasFiltradas() {
            var texto = buscador ? buscador.value.trim().toLowerCase() : '';
            if (texto === '') { return filas; }

            // Busca el texto en TODAS las columnas de cada fila
            return filas.filter(function (fila) {
                return fila.textContent.toLowerCase().indexOf(texto) !== -1;
            });
        }

        function dibujar() {
            var visibles = filasFiltradas();
            var totalPaginas = Math.max(1, Math.ceil(visibles.length / config.porPagina));
            if (paginaActual > totalPaginas) { paginaActual = totalPaginas; }

            // Oculta todas las filas y solo muestra las de la página actual
            filas.forEach(function (fila) { fila.style.display = 'none'; });

            var inicio = (paginaActual - 1) * config.porPagina;
            visibles.slice(inicio, inicio + config.porPagina).forEach(function (fila) {
                fila.style.display = '';
            });

            // Estado vacío: no hay ninguna fila que coincida con la búsqueda
            var filaVacia = tabla.querySelector('.fila-estado-vacio');
            if (filaVacia) { filaVacia.remove(); }

            if (visibles.length === 0) {
                var columnas = tabla.querySelectorAll('thead th').length;
                var tr = document.createElement('tr');
                tr.className = 'fila-estado-vacio';
                tr.innerHTML = '<td colspan="' + columnas + '">'
                    + '<div class="estado-vacio"><i class="bi bi-inbox"></i>'
                    + '<p>No se encontraron resultados.</p></div></td>';
                cuerpo.appendChild(tr);
            }

            if (paginacion) { dibujarPaginacion(totalPaginas); }
        }

        function dibujarPaginacion(totalPaginas) {
            paginacion.innerHTML = '';

            var anterior = document.createElement('button');
            anterior.type = 'button';
            anterior.innerHTML = '<i class="bi bi-chevron-left"></i>';
            anterior.disabled = paginaActual === 1;
            anterior.addEventListener('click', function () { paginaActual--; dibujar(); });
            paginacion.appendChild(anterior);

            for (var i = 1; i <= totalPaginas; i++) {
                (function (numero) {
                    var boton = document.createElement('button');
                    boton.type = 'button';
                    boton.textContent = numero;
                    if (numero === paginaActual) { boton.className = 'activo'; }
                    boton.addEventListener('click', function () { paginaActual = numero; dibujar(); });
                    paginacion.appendChild(boton);
                })(i);
            }

            var siguiente = document.createElement('button');
            siguiente.type = 'button';
            siguiente.innerHTML = '<i class="bi bi-chevron-right"></i>';
            siguiente.disabled = paginaActual === totalPaginas;
            siguiente.addEventListener('click', function () { paginaActual++; dibujar(); });
            paginacion.appendChild(siguiente);
        }

        if (buscador) {
            buscador.addEventListener('input', function () { paginaActual = 1; dibujar(); });
        }

        dibujar();
    }
};