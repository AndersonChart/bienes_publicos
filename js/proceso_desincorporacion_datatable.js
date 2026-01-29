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

    function resetErrorSerial() {
        const el = document.getElementById('error-container-proceso-desincorporacion-serial');
        if (el) {
            el.textContent = '';
            el.style.display = 'none';
        }
    }

    function setErrorSerial(message) {
        const el = document.getElementById('error-container-proceso-desincorporacion-serial');
        if (el) {
            el.textContent = message || '';
            el.style.display = message ? 'block' : 'none';
        }
    }

    const estado = {
        buffer: {},
        bufferMeta: {},
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
        createdRow: function(row, data, dataIndex) {
            $(row).attr('data-id', data.articulo_id);
        },
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

    //RECARGAR TABLA POR FILTROS
    $('#categoria_filtro, #clasificacion_filtro').on('change', function () {
        tablaArticulos.ajax.reload(null, false);
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

        // Inicializar o actualizar el buffer básico
        if (!estado.buffer[id]) {
            estado.buffer[id] = { articulo_id: id, codigo, nombre, cantidad: 0, seriales: [] };
        }

        estado.buffer[id].cantidad = cantidad;
        
        // Solo inicializamos el array si está vacío (primera vez que se escribe)
        // Si ya tiene datos (porque el usuario entró al modal y seleccionó), 
        // no lo tocamos para no borrar los IDs de los seriales reales.
        if (estado.buffer[id].seriales.length === 0) {
            estado.buffer[id].seriales = new Array(cantidad).fill({ 
                id: null, 
                serial: 'SIN SERIAL', 
                observacion: '' 
            });
        }

        // Activar botón de la fila actual
        const $btn = input.closest('tr').find('.btn_agregar_seriales');
        $btn.removeClass('is-disabled').prop('disabled', false);

        // Si tienes la función de resumen, llámala aquí
        if (typeof actualizarResumenDesincorporacion === 'function') actualizarResumenDesincorporacion();
    });

    //INFO DE ARTICULOS
    $('#procesoDesincorporacionArticuloTabla tbody').on('click', '.btn_ver_info', function () {
        const id = $(this).data('id');

        $.post('php/desincorporacion_ajax.php', { accion: 'obtener_articulo', id }, function (resp) {
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

    //Tabla de seriales dentro del modal TABLA DE SERIALES ESTRUCTURA BASICA
    const tablaSeriales = $('#procesoDesincorporacionSerialTabla').DataTable({
    scrollY: '300px',
    scrollCollapse: true,
    paging: false,
    searching: false,
    info: false,
    ordering: true,
    columns: [
        { 
            data: null, 
            title: 'No.',
            orderable: true,
            // Agregamos una clase extra 'con-numero' para controlar el estilo por CSS
            className: 'select-checkbox con-numero', 
            render: function (data, type, row, meta) {
                // Retornamos el número. 
                // El checkbox aparecerá encima o al lado dependiendo del CSS.
                return meta.row + 1;
            }
        },
        { 
            data: 'serial', 
            title: 'Serial',
            render: function(data) {
                if (data === 'SIN SERIAL') {
                    // Aquí aplicas el estilo (ejemplo: gris y cursiva)
                    return `<span class="text-muted">${data}</span>`;
                }
                return `<strong>${data}</strong>`; // Estilo para seriales reales
            }
        },
        { 
            data: 'observacion', 
            title: 'Observación',
            render: function (data, type, row) {
                // Implementando los inputs que querías antes
                const esSoloGenerico = row.serial === 'SIN SERIAL';
                // BUSCAMOS SI YA HABÍAMOS GUARDADO UNA OBSERVACIÓN PARA ESTE SERIAL ESPECÍFICO
                const bufferActual = estado.buffer[estado.articuloActivo]?.seriales || [];
                const registroPrevio = bufferActual.find(s => s.id === row.id);
                
                // Prioridad: 1. Lo que está en el buffer, 2. Lo que viene de la DB, 3. Vacío
                const valorMostrar = registroPrevio ? registroPrevio.observacion : (data || '');

                // IMPORTANTE: Aquí usamos ${valorMostrar} en el value
                return `<input type="text" class="input_serial" 
                        value="${valorMostrar}"
                        ${esSoloGenerico ? 'disabled' : ''}>`;
            }
        }
    ],
    select: {
        style: 'multi',
        selector: 'td:first-child' // O selecciona por el checkbox
    },
    language: { emptyTable: 'No hay seriales disponibles' }
});

    // Evento adaptado para DESINCORPORACIÓN
    $('#procesoDesincorporacionArticuloTabla tbody').on('click', '.btn_agregar_seriales', function () {
        if ($(this).prop('disabled') || $(this).hasClass('is-disabled')) return;

        const articuloId = $(this).data('id');
        estado.articuloActivo = articuloId;

        resetErrorSerial();

        // Petición 1: Cargar los seriales disponibles
        $.post('php/desincorporacion_ajax.php', { 
            accion: 'leer_seriales_articulo', 
            id: articuloId 
        }, function (resp) {
            if (resp && Array.isArray(resp.data)) {
                tablaSeriales.clear();
                tablaSeriales.rows.add(resp.data).draw();

                // --- LÓGICA DE AUTO-SELECCIÓN ---
                
                // 1. Obtener cantidad ingresada desde el input de la tabla principal
                const filaPrincipal = $(`#procesoDesincorporacionArticuloTabla tr[data-id="${articuloId}"]`);
                const cantidadSolicitada = parseInt(filaPrincipal.find('.input_cantidad').val()) || 0;

                // 2. Verificar si tenemos seriales REALES (con ID) en el buffer
                const serialesEnBuffer = estado.buffer[articuloId]?.seriales || [];
                const tieneSerialesReales = serialesEnBuffer.length > 0 && serialesEnBuffer[0].id !== null;

                if (tieneSerialesReales) {
                    const seleccionadosPrevios = serialesEnBuffer.map(s => s.id);
                    tablaSeriales.rows().every(function () {
                        if (seleccionadosPrevios.includes(this.data().id)) {
                            this.select();
                        }
                    });
                } else if (cantidadSolicitada > 0) {
                    let contador = 0;
                    tablaSeriales.rows({ order: 'current' }).every(function () {
                        if (contador < cantidadSolicitada) {
                            this.select();
                            contador++;
                        }
                    });
                }
            }
        }, 'json');

        // Petición 2: Cargar datos visuales (Nombre/Imagen) del artículo en el modal
        $.post('php/desincorporacion_ajax.php', { accion: 'obtener_articulo', id: articuloId }, function (resp) {
            if (resp && resp.exito && resp.articulo) {
                $('#serial_codigo_articulo').text(resp.articulo.articulo_codigo || '');
                $('#serial_nombre_articulo').text(resp.articulo.articulo_nombre || '');
                const imgEl = document.getElementById('serial_imagen_articulo');
                if (imgEl) {
                    imgEl.src = resp.articulo.articulo_imagen ? resp.articulo.articulo_imagen + '?t=' + Date.now() : 'img/icons/articulo.png';
                    imgEl.alt = 'Imagen del artículo';
                }
                estado.bufferMeta[articuloId] = {
                    codigo: resp.articulo.articulo_codigo || String(articuloId),
                    nombre: resp.articulo.articulo_nombre || ''
                };
            }
        }, 'json');

        showDialog('dialog[data-modal="seriales_articulo"]');
    });

    //GUARDAR SERIALES SELECCIONADOS
    // Usamos el ID exacto de tu input submit
    $('#btn_guardar_proceso_desincorporacion_seriales').on('click', function(e) {
        e.preventDefault(); // IMPORTANTE: Evita que el formulario recargue la página

        resetErrorSerial();

        const filasSeleccionadas = tablaSeriales.rows({ selected: true });
        const cantidadSeleccionada = filasSeleccionadas.count();
        
        // Validación A: No seleccionó nada
        if (cantidadSeleccionada === 0) {
            setErrorSerial("Debe seleccionar al menos un serial para confirmar.");
            return;
        }

        // Validación B: La selección no coincide con lo ingresado afuera
        if (cantidadSeleccionada !== cantidadRequerida) {
            setErrorSerial(`La cantidad seleccionada (${cantidadSeleccionada}) no coincide con la cantidad solicitada (${cantidadRequerida}).`);
            return;
        }

        const datosSeriales = [];

        // Recolectar datos y observaciones de los inputs
        filasSeleccionadas.nodes().each(function(node) {
            const data = tablaSeriales.row(node).data();
            const observacionInput = $(node).find('.input_serial').val(); 

            datosSeriales.push({
                id: data.id,
                serial: data.serial,
                observacion: observacionInput
            });
        });

        // 1. Guardar en el buffer (mantenemos los metadatos anteriores si existían)
        if (!estado.buffer[estado.articuloActivo]) {
            estado.buffer[estado.articuloActivo] = {};
        }
        
        estado.buffer[estado.articuloActivo].cantidad = cantidadSeleccionada;
        estado.buffer[estado.articuloActivo].seriales = datosSeriales;

        // 2. ACTUALIZAR CANTIDAD EN LA TABLA DE AFUERA
        const inputPrincipal = $(`#procesoDesincorporacionArticuloTabla tr[data-id="${estado.articuloActivo}"] .input_cantidad`);
        
        if (inputPrincipal.length > 0) {
            inputPrincipal.val(cantidadSeleccionada);
            // Disparar input para que se ejecute la lógica de habilitar/deshabilitar botones
            inputPrincipal.trigger('input'); 
        }
        resetErrorSerial();
        closeDialog('dialog[data-modal="seriales_articulo"]');
    });
});
