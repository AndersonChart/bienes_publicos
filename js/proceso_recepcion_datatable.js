// proceso_recepcion_datatable.js
// Configuración completa con validaciones de artículos y seriales
// Requisitos: jQuery, DataTables, backend en php/recepcion_ajax.php

window.addEventListener('load', function () {
    'use strict';

    if (typeof $ !== 'function') {
        console.error('jQuery no está disponible');
        return;
    }

    const estado = {
        buffer: {},
        articuloActivo: null
    };

    // Helpers UI
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
    const tablaArticulos = $('#procesoRecepcionArticuloTabla').DataTable({
        scrollY: '500px',
        scrollCollapse: true,
        responsive: true,
        ajax: {
            url: 'php/recepcion_ajax.php',
            type: 'POST',
            data: function (d) {
                d.accion = 'listar_articulos_recepcion';
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
                render: function (data) {
                    if (!data || String(data).trim() === '') return '';
                    return `<div class="imagen_celda_wrapper">
                                <img src="${data}?t=${Date.now()}" class="tabla_imagen" alt="Imagen">
                            </div>`;
                },
                orderable: false
            },
            {
                data: null, title: 'Cantidad',
                render: function (row) {
                    const valor = estado.buffer[row.articulo_id]?.cantidad ?? '';
                    return `<input type="number"
                                class="input_text input_cantidad"
                                min="0"
                                data-id="${row.articulo_id}"
                                data-codigo="${row.articulo_codigo}"
                                data-nombre="${row.articulo_nombre}"
                                value="${valor}"
                                style="width:80px">`;
                },
                orderable: false
            },
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
                                title="Añadir Seriales"
                                ${disabled ? 'disabled aria-disabled="true" tabindex="-1"' : ''}>
                                Añadir Seriales
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

    // Filtros
    $('#categoria_filtro, #clasificacion_filtro').on('change', function () {
        tablaArticulos.ajax.reload(null, false);
    });

    // Mantener cantidades y botones
    tablaArticulos.on('draw', function () {
        $('#procesoRecepcionArticuloTabla tbody .input_cantidad').each(function () {
            const id = $(this).data('id');
            if (estado.buffer[id]) {
                $(this).val(estado.buffer[id].cantidad);
            }
        });

        $('#procesoRecepcionArticuloTabla tbody .btn_agregar_seriales').each(function () {
            const id = $(this).data('id');
            const cant = estado.buffer[id]?.cantidad ?? 0;
            const activo = Number.isFinite(cant) && cant > 0;
            $(this).toggleClass('is-disabled', !activo).prop('disabled', !activo);
        });
    });

    // Cambios de cantidad
    $('#procesoRecepcionArticuloTabla tbody').on('input', '.input_cantidad', function () {
        const id = $(this).data('id');
        const codigo = $(this).data('codigo');
        const nombre = $(this).data('nombre');
        const val = $(this).val();
        const cantidad = Number.parseInt(val, 10);

        if (!Number.isFinite(cantidad) || cantidad <= 0) {
            if (estado.buffer[id]) {
                estado.buffer[id].cantidad = 0;
                estado.buffer[id].seriales = [];
            }
            const $btn = $(`#procesoRecepcionArticuloTabla tbody .btn_agregar_seriales[data-id="${id}"]`);
            $btn.addClass('is-disabled').prop('disabled', true);
            actualizarResumenRecepcion();
            return;
        }

        if (!estado.buffer[id]) {
            estado.buffer[id] = { articulo_id: id, codigo, nombre, cantidad: 0, seriales: [] };
        }

        estado.buffer[id].cantidad = cantidad;
        const serialesPrevios = estado.buffer[id].seriales || [];
        estado.buffer[id].seriales = new Array(cantidad).fill('').map((_, i) => serialesPrevios[i] ?? '');

        const $btn = $(`#procesoRecepcionArticuloTabla tbody .btn_agregar_seriales[data-id="${id}"]`);
        $btn.removeClass('is-disabled').prop('disabled', false);

        actualizarResumenRecepcion();
    });

    // ------------------------------
    // DataTable: Resumen
    // ------------------------------
    const tablaResumen = $('#procesoRecepcionResumenTabla').DataTable({
        data: [],
        columns: [
            { data: 'codigo', title: 'Código' },
            { data: 'nombre', title: 'Nombre' },
            { data: 'cantidad', title: 'Cantidad' }
        ],
        ordering: true,
        scrollY: '500px',
        scrollCollapse: true,
        paging: false,
        searching: false,
        info: false,
        language: { emptyTable: 'No se encuentran registros' }
    });

    function actualizarResumenRecepcion() {
        const resumen = Object.values(estado.buffer)
            .filter(item => Number.isFinite(item.cantidad) && item.cantidad > 0)
            .map(item => ({ codigo: item.codigo, nombre: item.nombre, cantidad: item.cantidad }));

        tablaResumen.clear();
        tablaResumen.rows.add(resumen);
        tablaResumen.draw();
    }

    // ------------------------------
    // DataTable: Seriales (modal)
    // ------------------------------
    const tablaSeriales = $('#procesoRecepcionSerialTabla').DataTable({
        scrollY: '300px',
        scrollCollapse: true,
        responsive: true,
        paging: false,
        searching: false,
        info: false,
        ordering: true,
        ajax: null,
        columns: [
            { data: 'numero', title: 'Número', orderable: true },
            { data: 'serial', title: 'Serial', orderable: false }
        ],
        language: { emptyTable: 'No se encuentran registros' }
    });

    function cargarSerialesEnModal(articuloId) {
        const buf = estado.buffer[articuloId];
        tablaSeriales.clear();

        if (!buf || !Number.isFinite(buf.cantidad) || buf.cantidad <= 0) {
            tablaSeriales.rows.add([{ numero: '', serial: 'Ingrese una cantidad primero' }]).draw();
            return;
        }

        const filas = [];
        for (let i = 0; i < buf.cantidad; i++) {
            const valor = buf.seriales[i] || '';
            filas.push({
                numero: i + 1,
                serial: `<input type="text" class="input_serial"
                                data-articulo="${buf.articulo_id}"
                                data-index="${i}"
                                value="${valor}">`
            });
        }
        tablaSeriales.rows.add(filas).draw();
    }

    // Abrir modal de info
    $('#procesoRecepcionArticuloTabla tbody').on('click', '.btn_ver_info', function () {
        const id = $(this).data('id');

        $.post('php/recepcion_ajax.php', { accion: 'obtener_articulo', id }, function (resp) {
            if (resp && resp.exito && resp.articulo) {
                const a = resp.articulo;
                $('#info_codigo').text(a.articulo_codigo ?? '');
                $('#info_nombre').text(a.articulo_nombre ?? '');
                $('#info_categoria').text(a.categoria_nombre ?? '');
                $('#info_clasificacion').text(a.clasificacion_nombre ?? '');
                $('#info_marca').text(a.marca_nombre ?? '');
                $('#info_modelo').text(a.articulo_modelo ?? '');
                $('#info_descripcion').text(a.articulo_descripcion ?? '');

                const imgEl = document.getElementById('info_imagen');
                if (imgEl) {
                    const src = (a.articulo_imagen && String(a.articulo_imagen).trim() !== '')
                        ? `${a.articulo_imagen}?t=${Date.now()}` : '';
                    imgEl.src = src;
                    imgEl.alt = 'Imagen del artículo';
                }

                if (Number(a.categoria_tipo) === 0) {
                    $('#li_info_marca').hide();
                    $('#li_info_modelo').hide();
                } else {
                    $('#li_info_marca').show();
                    $('#li_info_modelo').show();
                }

                showDialog('dialog[data-modal="info_articulo"]');
            }
        }, 'json');
    });

    // Abrir modal de seriales
    $('#procesoRecepcionArticuloTabla tbody').on('click', '.btn_agregar_seriales', function () {
        if ($(this).prop('disabled') || $(this).hasClass('is-disabled')) return;

        const articuloId = $(this).data('id');
        const codigo = $(this).data('codigo');
        const nombre = $(this).data('nombre');

        estado.articuloActivo = articuloId;

        $('#serial_codigo_articulo').text(codigo);
        $('#serial_nombre_articulo').text(nombre);

        cargarSerialesEnModal(articuloId);
        resetErrorSerial();

        showDialog('dialog[data-modal="seriales_articulo"]');
        tablaSeriales.columns.adjust().draw();
    });

    // Helpers de errores en modal de seriales
    function resetErrorSerial() {
        const el = document.getElementById('error-container-proceso-recepcion-serial');
        if (el) {
            el.textContent = '';
            el.style.display = 'none';
        }
    }

    function setErrorSerial(message) {
        const el = document.getElementById('error-container-proceso-recepcion-serial');
        if (el) {
            el.textContent = message || '';
            el.style.display = message ? 'block' : 'none';
        }
    }

    // Guardar seriales desde el modal
    $('#form_proceso_recepcion_seriales').on('submit', function (e) {
        e.preventDefault();

        if (estado.articuloActivo == null) {
            closeDialog('dialog[data-modal="seriales_articulo"]');
            return;
        }

        $('#procesoRecepcionSerialTabla tbody .input_serial').each(function () {
            const articuloId = $(this).data('articulo');
            const index = $(this).data('index');
            const valor = String($(this).val() || '').trim();

            if (estado.buffer[articuloId]) {
                estado.buffer[articuloId].seriales[index] = valor;
            }
        });

        const serialesNoVacios = (estado.buffer[estado.articuloActivo].seriales || []).filter(s => s !== '');
        const setLocal = new Set(serialesNoVacios);

        if (setLocal.size !== serialesNoVacios.length) {
            setErrorSerial('Hay seriales repetidos dentro del artículo. Corrija antes de guardar.');
            return;
        }

        if (serialesNoVacios.length > 0) {
            $.post('php/recepcion_ajax.php', {
                accion: 'validar_seriales',
                seriales: JSON.stringify(serialesNoVacios)
            }, function (resp) {
                if (resp && resp.exito && Array.isArray(resp.repetidos) && resp.repetidos.length > 0) {
                    setErrorSerial('Los siguientes seriales ya existen en el inventario: ' + resp.repetidos.join(', '));
                } else {
                    resetErrorSerial();
                    closeDialog('dialog[data-modal="seriales_articulo"]');
                }
            }, 'json');
        } else {
            resetErrorSerial();
            closeDialog('dialog[data-modal="seriales_articulo"]');
        }
    });

    // Validación duplicados entre artículos
    function validarDuplicadosEntreArticulos() {
        const todosSeriales = [];
        for (const item of Object.values(estado.buffer)) {
            if (Array.isArray(item.seriales)) {
                for (const s of item.seriales) {
                    const val = String(s || '').trim();
                    if (val !== '') todosSeriales.push({ articulo: item.articulo_id, serial: val });
                }
            }
        }

        const vistos = new Map();
        for (const { articulo, serial } of todosSeriales) {
            if (vistos.has(serial) && vistos.get(serial) !== articulo) {
                return `El serial ${serial} ya fue ingresado en otro artículo.`;
            }
            vistos.set(serial, articulo);
        }
        return null;
    }

    // Abrir modal de recepción
    document.querySelectorAll('[data-modal-target="modal_proceso_recepcion"]').forEach(el => {
        el.addEventListener('click', function () {
            actualizarResumenRecepcion();
            clearError('error-container-proceso-recepcion');
            showDialog('dialog[data-modal="modal_proceso_recepcion"]');
        });
    });

    // Envío del formulario de recepción
    $('#form_proceso_recepcion').on('submit', function (e) {
        e.preventDefault();
        clearError('error-container-proceso-recepcion');

        const fecha = String($('#proceso_recepcion_fecha').val() || '').trim();
        const descripcion = String($('#proceso_recepcion_descripcion').val() || '').trim();
        const tipo = 1;

        const articulos = Object.values(estado.buffer)
            .filter(item => Number.isFinite(item.cantidad) && item.cantidad > 0)
            .map(item => ({
                articulo_id: item.articulo_id,
                cantidad: item.cantidad,
                seriales: (item.seriales || []).map(s => (typeof s === 'string' ? s.trim() : ''))
            }));

        if (articulos.length === 0) {
            setError('error-container-proceso-recepcion', 'Debe ingresar al menos un artículo con cantidad.');
            return;
        }

        // Validación duplicados entre artículos
        const errorDuplicado = validarDuplicadosEntreArticulos();
        if (errorDuplicado) {
            setError('error-container-proceso-recepcion', errorDuplicado);
            return;
        }

        const payload = {
            accion: 'crear',
            ajuste_fecha: fecha,
            ajuste_descripcion: descripcion,
            ajuste_tipo: tipo,
            articulos: JSON.stringify(articulos)
        };

        $.post('php/recepcion_ajax.php', payload, function (resp) {
            if (resp && resp.exito) {
                estado.buffer = {};
                estado.articuloActivo = null;
                actualizarResumenRecepcion();

                closeDialog('dialog[data-modal="modal_proceso_recepcion"]');
                document.getElementById('success-message').textContent = 'La recepción fue registrada correctamente.';
                showDialog('dialog[data-modal="success"]');

                tablaArticulos.ajax.reload(null, false);
            } else if (resp && resp.error) {
                setError('error-container-proceso-recepcion', resp.mensaje || 'Ocurrió un error al registrar la recepción.');
                console.error('Detalle de error (backend):', resp.detalle || resp);
            } else {
                setError('error-container-proceso-recepcion', 'Respuesta inesperada del servidor.');
            }
        }, 'json');
    });

    // Cierre de modal de éxito
    $('#close-success-proceso-recepcion').on('click', function () {
        closeDialog('dialog[data-modal="success"]');
    });
});
