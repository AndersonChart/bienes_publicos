// proceso_desincorporacion_datatable.js
// Configuración completa con validaciones de artículos y seriales
// Requisitos: jQuery, DataTables, backend en php/desincorporacion_ajax.php

window.addEventListener('load', function () {
    'use strict';
    
    // Interceptar clic en "Regresar"
    document.querySelector('.basics-container .new_user').addEventListener('click', function (e) {
        e.preventDefault(); // evitar redirección inmediata

        const modal = document.querySelector('dialog[data-modal="confirmar_regresar_desincorporacion"]');
        if (modal?.showModal) modal.showModal();
    });

    // Acción al confirmar
    document.getElementById('form_confirmar_regresar').addEventListener('submit', function (e) {
        e.preventDefault();
        // Redirigir a la lista de desincorporaciones
        window.location.href = "index.php?vista=listar_desincorporacion";
    });

    if (typeof $ !== 'function') {
        console.error('jQuery no está disponible');
        return;
    }

    const estado = {
        buffer: {},
        articuloActivo: null
    };

    function showDialog(selector) {
        const dlg = document.querySelector(selector);
        if (dlg && typeof dlg.showModal === 'function') dlg.showModal();
    }
    function closeDialog(selector) {
        const dlg = document.querySelector(selector);
        if (dlg && typeof dlg.close === 'function') dlg.close();
    }
    function setError(containerId, message) {
        const el = document.getElementById(containerId);
        if (el) el.textContent = message || '';
    }
    function clearError(containerId) {
        setError(containerId, '');
    }

    // ------------------------------
    // DataTable: Artículos
    // ------------------------------
    const tablaArticulos = $('#procesoDesincorporacionArticuloTabla').DataTable({
        scrollY: '500px',
        scrollCollapse: true,
        responsive: true,
        ajax: {
            url: 'php/desincorporacion_ajax.php',
            type: 'POST',
            data: function (d) {
                d.accion = 'listar_articulos_desincorporacion';
                d.categoria_id = document.getElementById('categoria_filtro')?.value || '';
                d.clasificacion_id = document.getElementById('clasificacion_filtro')?.value || '';
            },
            dataSrc: 'data',
            error: function (xhr, status, error) {
                console.error('Error AJAX (recepción artículos):', error);
                console.log('Respuesta del servidor:', xhr.responseText);
            }
        },
        columns: [
        { data: 'articulo_codigo', title: 'Código' },
        { data: 'articulo_nombre', title: 'Nombre' },
        { data: 'categoria_nombre', title: 'Categoría' },
        { data: 'clasificacion_nombre', title: 'Clasificación' },
        {
            data: 'articulo_imagen', title: 'Imagen',
            className: 'dt-center', // Centra el contenido
            render: function (data) {
                if (!data) return '<div class="no-img">N/A</div>';
                return `<img src="${data}?t=${Date.now()}" class="tabla_imagen" style="width:50px">`;
            },
            orderable: false
        },
        // COLUMNAS DE STOCK (Tu objetivo)
        { 
            data: 'stock_disponible', 
            title: 'Activos', 
            // Agregamos dt-center para alineación horizontal de DataTables
            className: 'dt-center font-bold', 
            render: function (data, type, row) {
                return `<span class="stock-badge activos">${data}</span>`;
            }
        },
        { 
            data: 'stock_disponible_seriales', 
            title: 'Con Serial',
            className: 'dt-center',
            render: function(data, type, row) {
                return `<span class="stock-badge serial">${data}</span>`;
            }
        },
        // COLUMNA INGRESAR (Input)
        {
            data: null, title: 'Ingresar',
            render: function (row) {
                const valor = estado.buffer[row.articulo_id]?.cantidad ?? '';
                return `<input type="number" class="input_text input_cantidad" 
                            min="0" max="${row.stock_disponible}"
                            data-id="${row.articulo_id}" 
                            data-codigo="${row.articulo_codigo}"
                            data-nombre="${row.articulo_nombre}"
                            value="${valor}" style="width:70px">`;
            },
            orderable: false
        },
        // ACCIONES
        {
            data: null, title: 'Acciones',
            render: function (row) {
                const cant = estado.buffer[row.articulo_id]?.cantidad ?? 0;
                const disabled = !(Number.isFinite(cant) && cant > 0);

                return `
                    <div class="acciones">
                        <div class="icon-action btn_ver_info"
                            data-modal-target="info_articulo"
                            data-id="${row.articulo_id}"
                            title="Info">
                            <img src="img/icons/info.png" alt="Info">
                        </div>
                        <button type="button"
                            class="new-proceso btn_agregar_seriales${disabled ? ' is-disabled' : ''}"
                            data-modal-target="seriales_articulo"
                            data-id="${row.articulo_id}"
                            data-codigo="${row.articulo_codigo}"
                            data-nombre="${row.articulo_nombre}"
                            title="Seleccionar Seriales"
                            ${disabled ? 'disabled aria-disabled="true" tabindex="-1"' : ''}>
                            Seleccionar...
                        </button>
                    </div>
                `;
            },
            orderable: false
        }
    ],
        paging: true,
        info: true,
        dom: '<"top"Bf>rt<"bottom"lpi><"clear">',
        buttons: ['pdf'],
        language: {
            emptyTable: 'No se encuentran registros',
            search: 'Buscar:',
            lengthMenu: 'Mostrar _MENU_ registros',
            info: 'Mostrando _START_ a _END_ de _TOTAL_ registros',
            infoEmpty: 'Mostrando 0 a 0 de 0 registros',
            infoFiltered: '(filtrado de _MAX_ registros en total)',
            paginate: { next: 'Siguiente', previous: 'Anterior' },
            zeroRecords: 'No se encontraron coincidencias'
        },
        lengthMenu: [[5, 10, 15, 20, 30], [5, 10, 15, 20, 30]],
        pageLength: 15
    });
    // 1. Manejar el dibujado (Persistencia al cambiar de página)
    tablaArticulos.on('draw', function () {
        $('#procesoDesincorporacionArticuloTabla tbody .input_cantidad').each(function () {
            const id = $(this).data('id');
            if (estado.buffer[id]) {
                $(this).val(estado.buffer[id].cantidad);
            }
        });

        $('#procesoDesincorporacionArticuloTabla tbody .btn_agregar_seriales').each(function () {
            const id = $(this).data('id');
            const cant = estado.buffer[id]?.cantidad ?? 0;
            const activo = Number.isFinite(cant) && cant > 0;
            $(this).toggleClass('is-disabled', !activo).prop('disabled', !activo);
        });
    });

    // 2. Manejar la entrada de datos (Lógica de activación)
    $('#procesoDesincorporacionArticuloTabla tbody').on('input', '.input_cantidad', function () {
        const id = $(this).data('id');
        const codigo = $(this).data('codigo');
        const nombre = $(this).data('nombre');
        const input = $(this);
        const val = input.val();
        const maxStock = parseInt(input.attr('max')) || 0;
        let cantidad = Number.parseInt(val, 10);

        // Validación: No permitir más del stock disponible
        if (cantidad > maxStock) {
            cantidad = maxStock;
            input.val(maxStock);
        }

        // Si la cantidad es inválida o 0, limpiar buffer y desactivar botón
        if (!Number.isFinite(cantidad) || cantidad <= 0) {
            if (estado.buffer[id]) {
                estado.buffer[id].cantidad = 0;
                estado.buffer[id].seriales = [];
            }
            const $btn = input.closest('tr').find('.btn_agregar_seriales');
            $btn.addClass('is-disabled').prop('disabled', true);
            
            // Si tienes la función de resumen, llámala aquí
            if (typeof actualizarResumenDesincorporacion === 'function') actualizarResumenDesincorporacion();
            return;
        }

        // Inicializar o actualizar el buffer
        if (!estado.buffer[id]) {
            estado.buffer[id] = { articulo_id: id, codigo, nombre, cantidad: 0, seriales: [] };
        }

        estado.buffer[id].cantidad = cantidad;
        
        // Ajustar arreglo de seriales sin perder los ya escritos
        const serialesPrevios = estado.buffer[id].seriales || [];
        estado.buffer[id].seriales = new Array(cantidad).fill('').map((_, i) => serialesPrevios[i] ?? '');

        // Activar botón de la fila actual
        const $btn = input.closest('tr').find('.btn_agregar_seriales');
        $btn.removeClass('is-disabled').prop('disabled', false);

        // Si tienes la función de resumen, llámala aquí
        if (typeof actualizarResumenDesincorporacion === 'function') actualizarResumenDesincorporacion();
    });
});
