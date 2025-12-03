// proceso_asignacion_datatable.js
// Configuración completa para el proceso de asignación
// Requisitos: jQuery, DataTables, backend en php/asignacion_ajax.php

window.addEventListener('load', function () {
    'use strict';

    if (typeof $ !== 'function') {
        console.error('jQuery no está disponible');
        return;
    }

    const estado = {
        buffer: {},          // seriales seleccionados por artículo
        articuloActivo: null // artículo en edición dentro del modal
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
        if (el) {
            el.textContent = message || '';
            el.style.display = message ? 'block' : 'none';
        }
    }
    function clearError(containerId) {
        setError(containerId, '');
    }

    // ------------------------------
    // DataTable: Artículos disponibles
    // ------------------------------
    const tablaArticulos = $('#procesoAsignacionArticuloTabla').DataTable({
        scrollY: '500px',
        scrollCollapse: true,
        responsive: true,
        ajax: {
            url: 'php/asignacion_ajax.php',
            type: 'POST',
            data: function (d) {
                d.accion = 'listar_articulos_asignacion';
                d.categoria_id = document.getElementById('categoria_filtro')?.value || '';
                d.clasificacion_id = document.getElementById('clasificacion_filtro')?.value || '';
            },
            dataSrc: 'data',
            error: function (xhr, status, error) {
                console.error('Error AJAX (asignación artículos):', error);
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
                render: function (data) {
                    if (!data || String(data).trim() === '') return '';
                    return `<div class="imagen_celda_wrapper">
                                <img src="${data}?t=${Date.now()}" class="tabla_imagen" alt="Imagen">
                            </div>`;
                },
                orderable: false
            },
            {
                data: 'articulo_id', title: 'Stock Disponible',
                render: function (id) {
                    return estado.buffer[id]?.seriales?.length || 0;
                },
                orderable: false
            },
            {
                data: 'articulo_id', title: 'Acciones',
                render: function (id) {
                    return `
                        <div class="acciones">
                            <div class="icon-action btn_ver_info"
                                data-id="${id}"
                                title="Info">
                                <img src="img/icons/info.png" alt="Info">
                            </div>
                            <button type="button"
                                class="new-proceso btn_seleccionar_seriales"
                                data-id="${id}"
                                title="Seleccionar Seriales">
                                Seleccionar
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
        buttons: ['excel', 'pdf'],
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

    $('#categoria_filtro, #clasificacion_filtro').on('change', function () {
        tablaArticulos.ajax.reload(null, false);
    });

    // ------------------------------
    // Modal Info artículo
    // ------------------------------
    $('#procesoAsignacionArticuloTabla tbody').on('click', '.btn_ver_info', function () {
        const id = $(this).data('id');
        $.post('php/asignacion_ajax.php', { accion: 'obtener_articulo', id }, function (resp) {
            if (resp && resp.exito && resp.articulo) {
                const a = resp.articulo;
                $('#info_codigo').text(a.articulo_codigo ?? '');
                $('#info_nombre').text(a.articulo_nombre ?? '');
                $('#info_categoria').text(a.categoria_nombre ?? '');
                $('#info_clasificacion').text(a.clasificacion_nombre ?? '');
                $('#info_descripcion').text(a.articulo_descripcion ?? '');
                $('#info_imagen').attr('src', a.articulo_imagen ? `${a.articulo_imagen}?t=${Date.now()}` : '');

                showDialog('dialog[data-modal="info_articulo"]');
            }
        }, 'json');
    });

    // ------------------------------
    // Modal Seleccionar seriales
    // ------------------------------
    const tablaSeriales = $('#procesoAsignacionSerialTabla').DataTable({
        scrollY: '300px',
        scrollCollapse: true,
        responsive: true,
        paging: false,
        searching: false,
        info: false,
        ordering: true,
        ajax: null,
        columns: [
            { data: 'id', title: '', orderable: false, defaultContent: '' },
            { data: 'serial', title: 'Serial' },
            { data: 'observacion', title: 'Observación' },
            { data: 'estado', title: 'Estado' }
        ],
        select: { style: 'multi', selector: 'td:first-child' },
        language: { emptyTable: 'No se encuentran registros' }
    });

    $('#procesoAsignacionArticuloTabla tbody').on('click', '.btn_seleccionar_seriales', function () {
        const articuloId = $(this).data('id');
        estado.articuloActivo = articuloId;

        tablaSeriales.ajax = {
            url: 'php/asignacion_ajax.php',
            type: 'POST',
            data: { accion: 'leer_seriales_articulo', id: articuloId },
            dataSrc: 'data'
        };
        tablaSeriales.ajax.reload();

        showDialog('dialog[data-modal="seriales_articulo"]');
    });

    $('#form_proceso_asignacion_seriales').on('submit', function (e) {
        e.preventDefault();
        const seleccionados = tablaSeriales.rows({ selected: true }).data().toArray();
        estado.buffer[estado.articuloActivo] = {
            articulo_id: estado.articuloActivo,
            seriales: seleccionados.map(s => s.id)
        };
        tablaArticulos.ajax.reload(null, false);
        closeDialog('dialog[data-modal="seriales_articulo"]');
    });

    // ------------------------------
    // Modal Formulario de Asignación
    // ------------------------------
    document.querySelectorAll('[data-modal-target="modal_proceso_asignacion"]').forEach(el => {
        el.addEventListener('click', function () {
            actualizarResumenAsignacion();
            clearError('error-container-proceso-asignacion');
            showDialog('dialog[data-modal="modal_proceso_asignacion"]');
        });
    });

    const tablaResumen = $('#procesoAsignacionResumenTabla').DataTable({
        data: [],
        columns: [
            { data: 'codigo', title: 'Código' },
            { data: 'nombre', title: 'Nombre' },
            { data: 'cantidad', title: 'Cantidad' },
            { data: 'seriales', title: 'Seriales' }
        ],
        paging: false,
        searching: false,
        info: false,
        language: { emptyTable: 'No se encuentran registros' }
    });

    function actualizarResumenAsignacion() {
        const resumen = Object.values(estado.buffer).map(item => ({
            codigo: item.articulo_id,
            nombre: '', // se puede enriquecer con más datos si se cargan
            cantidad: item.seriales.length,
            seriales: item.seriales.join(', ')
        }));
        tablaResumen.clear();
        tablaResumen.rows.add(resumen);
        tablaResumen.draw();
    }

    // ------------------------------
    // Guardar asignación
    // ------------------------------
    $('#form_proceso_asignacion').on('submit', function (e) {
        e.preventDefault();
        clearError('error-container-proceso-asignacion');

        const fecha = String($('#proceso_asignacion_fecha').val() || '').trim();
        const fechaFin = String($('#proceso_asignacion_fecha_fin').val() || '').trim();
        const areaId = $('#proceso_asignacion_area').val();
        const personaId = $('#proceso_asignacion_persona').val();
        const descripcion = String($('#proceso_asignacion_descripcion').val() || '').trim();

        // Validaciones básicas
        if (!fecha || !areaId || !personaId) {
            setError('error-container-proceso-asignacion', 'Debe rellenar los campos obligatorios');
            return;
        }

        // Validación de duplicados entre artículos
        const todosSeriales = Object.values(estado.buffer).flatMap(item => item.seriales || []);
        const setSeriales = new Set(todosSeriales.filter(s => s !== ''));
        if (setSeriales.size !== todosSeriales.filter(s => s !== '').length) {
            setError('error-container-proceso-asignacion', 'Hay seriales repetidos en la asignación');
            return;
        }

        const payload = {
            accion: 'crear',
            area_id: areaId,
            persona_id: personaId,
            asignacion_fecha: fecha,
            asignacion_fecha_fin: fechaFin,
            asignacion_descripcion: descripcion,
            seriales: JSON.stringify(todosSeriales)
        };

        $.post('php/asignacion_ajax.php', payload, function (resp) {
            if (resp && resp.exito) {
                estado.buffer = {};
                estado.articuloActivo = null;
                actualizarResumenAsignacion();

                closeDialog('dialog[data-modal="modal_proceso_asignacion"]');
                document.getElementById('success-message').textContent = resp.mensaje;
                showDialog('dialog[data-modal="success"]');

                tablaArticulos.ajax.reload(null, false);

                // Redirigir a la lista de asignaciones después de unos segundos
                setTimeout(function() {
                    window.location.href = "index.php?vista=listar_asignacion";
                }, 1000);

            } else if (resp && resp.error) {
                setError('error-container-proceso-asignacion', resp.mensaje || 'Ocurrió un error al registrar la asignación.');
                console.error('Detalle de error (backend):', resp.detalle || resp);
            } else {
                setError('error-container-proceso-asignacion', 'Respuesta inesperada del servidor.');
            }
        }, 'json');
    });

    // ------------------------------
    // Eventos de cierre de modales
    // ------------------------------
    $('#close-success-proceso-asignacion').on('click', function () {
        closeDialog('dialog[data-modal="success"]');
    });

    $('#close-error').on('click', function () {
        closeDialog('dialog[data-modal="error"]');
    });
});
