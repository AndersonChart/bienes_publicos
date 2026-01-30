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

    // --- 1. UTILIDADES DE ERRORES (Declaradas una sola vez) ---
    function setError(containerId, message) {
        const el = document.getElementById(containerId);
        if (el) {
            el.textContent = message || '';
            // Aseguramos que si hay mensaje, el contenedor sea visible
            el.style.display = message ? 'block' : 'none'; 
            
            // Si usas clases de CSS para colores (ej. .error-message), asegúrate de que existan
            if (message) {
                el.classList.add('active'); 
            } else {
                el.classList.remove('active');
            }
        }
    }

    function clearError(containerId) {
        setError(containerId, '');
    }

    // Funciones específicas para el modal de seriales
    function resetErrorSerial() { clearError('error-container-proceso-desincorporacion-serial'); }
    function setErrorSerial(msg) { setError('error-container-proceso-desincorporacion-serial', msg); }

    // --- 2. ESTADO Y CONFIGURACIÓN ---
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

    // 2. Manejar la entrada de datos (Lógica de activación)
$('#procesoDesincorporacionArticuloTabla tbody').on('input', '.input_cantidad', function () {
    // 1. PRIMERO declaramos las variables extrayendo los datos del input
    const input = $(this);
    const id = input.data('id');
    const codigo = input.data('codigo');
    const nombre = input.data('nombre');
    const val = input.val();
    const maxStock = parseInt(input.attr('max')) || 0;
    let cantidad = Number.parseInt(val, 10);

    // 2. SEGUNDO validamos el stock
    if (cantidad > maxStock) {
        cantidad = maxStock;
        input.val(maxStock);
    }

    // 3. TERCERO manejamos si la cantidad es 0 o inválida
    if (!Number.isFinite(cantidad) || cantidad <= 0) {
        if (estado.buffer[id]) {
            estado.buffer[id].cantidad = 0;
            estado.buffer[id].seriales = [];
        }
        const $btn = input.closest('tr').find('.btn_agregar_seriales');
        $btn.addClass('is-disabled').prop('disabled', true);
        
        if (typeof actualizarResumenDesincorporacion === 'function') actualizarResumenDesincorporacion();
        return;
    }

    // 4. CUARTO: Ahora sí, inicializamos el buffer (las variables id, codigo y nombre ya existen)
    if (!estado.buffer[id]) {
        estado.buffer[id] = { 
            articulo_id: id, 
            codigo: codigo, 
            nombre: nombre, 
            cantidad: 0, 
            seriales: [] 
        };
    }

    estado.buffer[id].cantidad = cantidad;
    
    // Si no hay seriales, crear genéricos
    if (estado.buffer[id].seriales.length === 0) {
        estado.buffer[id].seriales = new Array(cantidad).fill(null).map(() => ({ 
            id: null, 
            serial: 'SIN SERIAL', 
            observacion: '' 
        }));
    }

    // Activar botón
    const $btn = input.closest('tr').find('.btn_agregar_seriales');
    $btn.removeClass('is-disabled').prop('disabled', false);

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
    $('#form_proceso_desincorporacion_seriales').on('submit', function(e) {
        e.preventDefault(); 
        resetErrorSerial();

        const filasSeleccionadas = tablaSeriales.rows({ selected: true });
        
        if (filasSeleccionadas.count() === 0) {
            setErrorSerial("Debe seleccionar al menos un serial para confirmar.");
            return;
        }

        const datosSeriales = [];
        filasSeleccionadas.nodes().each(function(node) {
            const data = tablaSeriales.row(node).data();
            const observacionInput = $(node).find('.input_serial').val(); 

            // IMPORTANTE: Verifica el nombre del campo ID aquí
            datosSeriales.push({
                articulo_serial_id: data.id || data.articulo_serial_id, 
                id: data.id,
                serial: data.serial,
                observacion: observacionInput
            });
        });

        // 1. Obtener metadatos que guardamos al abrir el modal
        const meta = estado.bufferMeta[estado.articuloActivo] || {};

        // 2. Guardar en buffer con TODO lo necesario para la tabla resumen
        estado.buffer[estado.articuloActivo] = {
            articulo_id: estado.articuloActivo,
            codigo: meta.codigo || 'S/C', // IMPORTANTE: Ahora el resumen tendrá de donde leer
            nombre: meta.nombre || 'S/N', // IMPORTANTE
            cantidad: filasSeleccionadas.count(),
            seriales: datosSeriales
        };

        // Actualizar input visual
        const inputPrincipal = $(`#procesoDesincorporacionArticuloTabla tr[data-id="${estado.articuloActivo}"] .input_cantidad`);
        if (inputPrincipal.length > 0) {
            inputPrincipal.val(filasSeleccionadas.count()).trigger('input'); 
        }

        actualizarResumenDesincorporacion();
        closeDialog('dialog[data-modal="seriales_articulo"]');
    });

    // ------------------------------
    // Resumen de Desincorporación con child rows
    // ------------------------------
    const tablaResumen = $('#procesoDesincorporacionResumenTabla').DataTable({
        data: [],
        columns: [
            { data: 'codigo', title: 'Código' },
            { data: 'nombre', title: 'Nombre' },
            { data: 'cantidad', title: 'Cantidad' },
            {
                className: 'dt-control',
                orderable: false,
                data: null,
                defaultContent: '<span class="toggle-details">▶</span>',
                title: 'Seriales'
            }
        ],
        ordering: true,
        scrollY: '300px',
        scrollCollapse: true,
        paging: false,
        searching: false,
        info: false,
        language: { emptyTable: 'No se encuentran registros' }
    });

    function actualizarResumenDesincorporacion() {
        const resumen = Object.values(estado.buffer)
            .filter(item => Array.isArray(item.seriales) && item.seriales.length > 0)
            .map(item => ({
                articulo_id: item.articulo_id,
                codigo: item.codigo,
                nombre: item.nombre,
                cantidad: item.seriales.length
            }));

        tablaResumen.clear();
        tablaResumen.rows.add(resumen);
        tablaResumen.draw();
    }
    
    function formatSerialesDesincorporacion(rowData) {
        const bufferItem = estado.buffer[rowData.articulo_id];
        if (!bufferItem || !Array.isArray(bufferItem.seriales) || bufferItem.seriales.length === 0) {
            return '<div class="seriales-empty">No se seleccionaron seriales</div>';
        }
        let html = '<div class="seriales-list"><ul>';
        bufferItem.seriales.forEach((s, i) => {
            html += `<li><strong>${i + 1}:</strong> ${s.serial}</li>`;
        });
        html += '</ul></div>';
        return html;
    }

    $('#procesoDesincorporacionResumenTabla tbody').on('click', 'td.dt-control', function () {
        const tr = $(this).closest('tr');
        const row = tablaResumen.row(tr);   

        if (row.child.isShown()) {
            row.child.hide();
            tr.removeClass('shown');
            $(this).find('.toggle-details').text('▶');
        } else {
            row.child(formatSerialesDesincorporacion(row.data())).show();
            tr.addClass('shown');
            $(this).find('.toggle-details').text('▼');
        }
    });

// ------------------------------
// Guardar Desincorporación Final
// ------------------------------
    // --- 3. EVENTO SUBMIT CORREGIDO ---
    $('#form_proceso_desincorporacion').on('submit', function (e) {
        e.preventDefault();
        
        // Limpiar errores previos antes de validar
        clearError('error-container-proceso-desincorporacion');
        $('.input_text, .document_wrapper, .input_date').removeClass('error_border');

        const fecha = String($('#proceso_desincorporacion_fecha').val() || '').trim();
        const actaInput = document.getElementById('acta_desincorporacion');

        // Validaciones Manuales
        if (!fecha) {
            setError('error-container-proceso-desincorporacion', 'Debe ingresar la fecha de la desincorporación');
            $('#proceso_desincorporacion_fecha').addClass('error_border').focus();
            return;
        }

        const hoy = new Date().toISOString().split('T')[0];
        if (fecha > hoy) {
            setError('error-container-proceso-desincorporacion', 'La fecha no puede ser posterior al día de hoy');
            $('#proceso_desincorporacion_fecha').addClass('error_border').focus();
            return;
        }

        if (!actaInput || !actaInput.files || actaInput.files.length === 0) {
            setError('error-container-proceso-desincorporacion', 'Debe subir el Acta de Desincorporación (PDF o Excel)');
            $('#wrapper_acta').addClass('error_border');
            return;
        }

        const articulos = Object.values(estado.buffer)
            .filter(item => item.cantidad > 0 && Array.isArray(item.seriales) && item.seriales.length > 0)
            .map(item => ({
                articulo_id: item.articulo_id,
                cantidad: item.cantidad,
                seriales: item.seriales.map(s => ({
                    id: s.id,
                    observacion: s.observacion
                }))
            }));

        if (articulos.length === 0) {
            setError('error-container-proceso-desincorporacion', 'Debe seleccionar al menos un artículo con sus seriales.');
            return;
        }

        // Preparar FormData
        const formData = new FormData(this); 
        formData.append('accion', 'crear');
        formData.append('ajuste_tipo', 0); 
        formData.append('articulos', JSON.stringify(articulos));

        $.ajax({
            url: 'php/desincorporacion_ajax.php',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            dataType: 'json',
            beforeSend: function() {
                $('#form_proceso_desincorporacion button[type="submit"]').prop('disabled', true).text('Procesando...');
            },
            success: function (resp) {
                if (resp && resp.error) {
                    // AQUÍ ES DONDE SE MUESTRA EL ERROR DEL BACKEND
                    setError('error-container-proceso-desincorporacion', resp.mensaje);

                    if (Array.isArray(resp.campos)) {
                        resp.campos.forEach(idHtml => {
                            const el = document.getElementById(idHtml);
                            if (el) el.classList.add('error_border');
                            if (idHtml === 'acta_desincorporacion') $('#wrapper_acta').addClass('error_border');
                        });
                    }
                    $('#form_proceso_desincorporacion button[type="submit"]').prop('disabled', false).text('Guardar Desincorporación');
                } else if (resp && (resp.exito || resp.valido)) {
                    // Éxito...
                    estado.buffer = {};
                    showDialog('dialog[data-modal="success"]');
                    setTimeout(() => window.location.href = "index.php?vista=listar_desincorporacion", 1500);
                }
            },
            error: function (xhr) {
                setError('error-container-proceso-desincorporacion', 'Error crítico de comunicación con el servidor.');
                $('#form_proceso_desincorporacion button[type="submit"]').prop('disabled', false).text('Guardar Desincorporación');
            }
        });
    });
    // Listener para cerrar el modal de éxito (opcional)
    $('#close-success-proceso-desincorporacion').on('click', function () {
        closeDialog('dialog[data-modal="success"]');
    });

    // Manejar el dibujado para persistencia al cambiar de página en la DataTable
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
});
