window.addEventListener('load', function () {
    if (typeof $ !== 'function') {
        console.error('jQuery no está disponible');
        return;
    }

    let estadoActual = 1; // habilitados/deshabilitados
    let estadoArticuloActivo = null;
    let idsSeriales = [];

    const toggleBtn = document.getElementById('toggleEstado');

    if (toggleBtn) {
        toggleBtn.textContent = 'Deshabilitados';
        toggleBtn.classList.add('estado-rojo');

        toggleBtn.addEventListener('click', () => {
            estadoActual = estadoActual === 0 ? 1 : 0;

            if (estadoActual === 0) {
                toggleBtn.textContent = 'Habilitados';
                toggleBtn.classList.remove('estado-rojo');
                toggleBtn.classList.add('estado-verde');
            } else {
                toggleBtn.textContent = 'Deshabilitados';
                toggleBtn.classList.remove('estado-verde');
                toggleBtn.classList.add('estado-rojo');
            }
            tabla.ajax.reload(null, false);
        });
    }

    const tabla = $('#articuloTabla').DataTable({
        scrollY: '500px',
        scrollCollapse: true,
        responsive: true,
        ajax: {
            url: 'php/articulo_ajax.php',
            type: 'POST',
            data: function (d) {
                d.accion = 'leer_todos';
                d.estado = estadoActual;
                d.categoria_id = document.getElementById('categoria_filtro')?.value || '';
                d.clasificacion_id = document.getElementById('clasificacion_filtro')?.value || '';
                d.estado_id = document.getElementById('estado_filtro')?.value || ''; //  nuevo filtro
            },
            dataSrc: 'data',
            error: function (xhr, status, error) {
                console.error('Error AJAX:', error);
                console.log('Respuesta del servidor:', xhr.responseText);
            }
        },
        columns: [
            { data: 'articulo_codigo', title: 'Código' },
            { data: 'articulo_nombre', title: 'Nombre' },
            { data: 'categoria_nombre', title: 'Categoría' },
            { data: 'clasificacion_nombre', title: 'Clasificación' },
            { data: 'articulo_modelo', title: 'Modelo' },
            { data: 'marca_nombre', title: 'Marca' },
            { data: 'articulo_imagen', title: 'Imagen',
                render: function (data) {
                    if (!data || data.trim() === '') return '';
                    return `
                        <div class="imagen_celda_wrapper">
                            <img src="${data}?t=${new Date().getTime()}" class="tabla_imagen">
                        </div>
                    `;
                },
                orderable: false
            },
            //  Nueva columna de stock
            { data: null, title: 'Stock',
                    render: function (row) {
                        const estadoSeleccionado = document.getElementById('estado_filtro')?.value || '';

                        if (estadoSeleccionado === '' || estadoSeleccionado === 'todos') {
                            //  Mostrar solo el total en gris
                            return `<span class="stock-badge total">${row.stock_total}</span>`;
                        }

                        if (estadoSeleccionado === '1') {
                            return `<span class="stock-badge activos">${row.stock_activos}</span>`;
                        }
                        if (estadoSeleccionado === '2') {
                            return `<span class="stock-badge asignados">${row.stock_asignados}</span>`;
                        }
                        if (estadoSeleccionado === '3') {
                            return `<span class="stock-badge mantenimiento">${row.stock_mantenimiento}</span>`;
                        }

                        // fallback: mostrar total
                        return `<span class="stock-badge total">${row.stock_total}</span>`;
                    },
                    orderable: true
                },
            { data: null, title: 'Acciones',
                render: function (row) {
                    const estado = parseInt(row.articulo_estado);
                    let botones = '';
                    if (estado === 1) {
                        botones += `
                            <div class="acciones">
                                <div class="icon-action" data-modal-target="new_articulo" title="Actualizar">
                                    <img src="img/icons/actualizar.png" alt="Actualizar">
                                </div>
                                <div class="icon-action btn_ver_info" data-modal-target="info_articulo" data-id="${row.articulo_id}" title="Info">
                                    <img src="img/icons/info.png" alt="Info">
                                </div>
                                <div class="icon-action btn_ver" data-modal-target="ver_serial" data-id="${row.articulo_id}" title="Ver Seriales">
                                    <img src="img/icons/ver.png" alt="Ver Seriales">
                                </div>
                                <div class="icon-action btn_eliminar" data-id="${row.articulo_id}" title="Eliminar">
                                    <img src="img/icons/eliminar.png" alt="Eliminar">
                                </div>
                            </div>
                        `;
                    } else {
                        botones += `
                            <div class="acciones">
                                <div class="icon-action btn_ver_info" data-modal-target="info_articulo" data-id="${row.articulo_id}" title="Info">
                                    <img src="img/icons/info.png" alt="Info">
                                </div>
                                <div class="icon-action btn_recuperar" data-id="${row.articulo_id}" title="Recuperar">
                                    <img src="img/icons/recuperar.png" alt="Recuperar">
                                </div>
                            </div>
                        `;
                    }
                    return botones;
                },
                orderable: false
            }
        ],
        paging: true,
        info: true,
        dom: '<"top"Bf>rt<"bottom"lpi><"clear">',
       buttons: [
    {
        text: 'Generar Reporte',
        className: 'btn-reporte',
        action: function () {

            const categoria = document.getElementById('categoria_filtro')?.value || '';
            const clasificacion = document.getElementById('clasificacion_filtro')?.value || '';
            const estadoStock = document.getElementById('estado_filtro')?.value || '';
            const estadoArticulo = estadoActual; // habilitado / deshabilitado

            const params = new URLSearchParams({
                categoria_id: categoria,
                clasificacion_id: clasificacion,
                estado_stock: estadoStock,
                estado_articulo: estadoArticulo
            });

            window.open('reportes/reporte_inventario.php?' + params.toString(), '_blank');
        }
    }
],

        language: {
            search: "Buscar:",
            lengthMenu: "Mostrar _MENU_ registros",
            zeroRecords: "No se encontraron registros coincidentes",
            emptyTable: "No hay ningún registro",
            info: "Mostrando _START_ a _END_ de _TOTAL_ registros",
            infoEmpty: "Mostrando 0 a 0 de 0 registros",
            infoFiltered: "(filtrado de _MAX_ registros totales)",
            paginate: {
                previous: "◀",
                next: "▶"
            }
        },
        lengthMenu: [[5, 10, 15, 20, 30], [5, 10, 15, 20, 30]],
        pageLength: 15
    });

    // Listener para filtros
    $('#categoria_filtro, #clasificacion_filtro, #estado_filtro').on('change', function () {
        tabla.ajax.reload(null, false);
    });

    // Eventos de articulos (igual que antes)
    $('#articuloTabla tbody').on('click', '.icon-action[title="Actualizar"]', function () {
        const fila = tabla.row($(this).closest('tr')).data();
        if (fila && fila.articulo_id) {
            abrirFormularioEdicionArticulo(fila.articulo_id);
        }
    });

    $('#articuloTabla tbody').on('click', '.btn_ver_info', function () {
        const id = $(this).data('id');
        if (!id) return;
        fetch('php/articulo_ajax.php', {
            method: 'POST',
            body: new URLSearchParams({ accion: 'obtener_articulo', id })
        })
        .then(res => res.json())
        .then(data => {
            if (data.exito && data.articulo) {
                mostrarInfoArticulo(data.articulo);
            }
        });
    });

    // Acción: Deshabilitar artículo
    $('#articuloTabla tbody').on('click', '.btn_eliminar', function () {
        const id = $(this).data('id');
        if (!id) return;

        fetch('php/articulo_ajax.php', {
            method: 'POST',
            body: new URLSearchParams({ accion: 'obtener_articulo', id })
        })
        .then(res => res.json())
        .then(data => {
            if (data.exito && data.articulo) {
                // Abre el modal de confirmación con los datos del artículo
                mostrarConfirmacionArticulo(data.articulo, 'eliminar');
            } else {
                mostrarModalError(data.mensaje || 'No se pudo obtener el artículo');
            }
        })
        .catch(() => mostrarModalError('Error de conexión con el servidor'));
    });

    // Acción: Recuperar artículo
    $('#articuloTabla tbody').on('click', '.btn_recuperar', function () {
        const id = $(this).data('id');
        if (!id) return;

        fetch('php/articulo_ajax.php', {
            method: 'POST',
            body: new URLSearchParams({ accion: 'obtener_articulo', id })
        })
        .then(res => res.json())
        .then(data => {
            if (data.exito && data.articulo) {
                // Abre el modal de confirmación con los datos del artículo
                mostrarConfirmacionArticulo(data.articulo, 'recuperar');
            } else {
                mostrarModalError(data.mensaje || 'No se pudo obtener el artículo');
            }
        })
        .catch(() => mostrarModalError('Error de conexión con el servidor'));
    });


    // NUEVO: Evento para abrir modal de seriales

    // Inicializar DataTable para seriales
const tablaSeriales = $('#articuloSerialTabla').DataTable({
    paging: false,
    searching: false,
    info: false,
    ordering: true,
    scrollY: '300px',
    scrollCollapse: true,
    columns: [
        { data: 'numero', title: 'No.' },
        { data: 'serial', title: 'Serial' },
        { data: 'observacion', title: 'Observaciones' },
        { data: 'estado', title: 'Estado' }
    ],
    language: { emptyTable: 'No se encontraron seriales' }
});

// Generar botón de estado
function generarBotonEstado(estado, esAsignado) {
    let clase = '';
    let texto = '';
    switch (parseInt(estado)) {
        case 1: clase = 'btn-estado activo'; texto = 'Activo'; break;
        case 2: clase = 'btn-estado asignado'; texto = 'Asignado'; break;
        case 3: clase = 'btn-estado mantenimiento'; texto = 'Mantenimiento'; break;
        default: clase = 'btn-estado'; texto = 'Desconocido';
    }
    return `<button class="${clase}" data-estado="${estado}" ${esAsignado ? 'disabled' : ''}>${texto}</button>`;
}

// Función para recalcular stock dinámicamente en el modal
function actualizarStockEnModal() {
    let activos = 0, asignados = 0, mantenimiento = 0, total = 0;
    const filas = tablaSeriales.rows().nodes();
    $(filas).each(function () {
        const estadoBtn = $(this).find('button.btn-estado');
        const estado = parseInt(estadoBtn.data('estado'));
        if (estado !== 4) { // excluye desincorporados
            total++;
            if (estado === 1) activos++;
            if (estado === 2) asignados++;
            if (estado === 3) mantenimiento++;
        }
    });
    $('#stock_total').text(total);
    $('#stock_activos').text(activos);
    $('#stock_asignados').text(asignados);
    $('#stock_mantenimiento').text(mantenimiento);
}

// Abrir modal de seriales
$('#articuloTabla tbody').on('click', '.btn_ver', function () {
    const id = $(this).data('id');
    if (!id) return;
    estadoArticuloActivo = id;
    idsSeriales = [];
    $('#articulo_id_hidden').val(id);
    limpiarError('error-container-inventario-serial');

    // 1) Cargar seriales
    fetch('php/articulo_ajax.php', {
        method: 'POST',
        body: new URLSearchParams({ accion: 'listar_seriales', id })
    })
    .then(res => res.json())
    .then(data => {
        if (!data || !Array.isArray(data.data)) {
            mostrarError('error-container-inventario-serial', 'No se pudo cargar la lista de seriales.');
            tablaSeriales.clear().draw();
            return;
        }
        const filas = data.data.map((s, i) => {
            idsSeriales[i] = s.id;
            const esAsignado = parseInt(s.estado) === 2;
            return {
                DT_RowClass: esAsignado ? 'fila-asignada' : '',
                numero: i + 1,
                serial: `<input type="text" class="input_serial" value="${s.serial || ''}" ${esAsignado ? 'disabled' : ''}>`,
                observacion: `<input type="text" class="input_serial" value="${s.observacion || ''}" ${esAsignado ? 'disabled' : ''}>`,
                estado: generarBotonEstado(s.estado, esAsignado)
            };
        });
        tablaSeriales.clear().rows.add(filas).draw();
        actualizarStockEnModal(); // inicializa stock al abrir
    });

    // 2) Cargar stock desde BD
    fetch('php/articulo_ajax.php', {
        method: 'POST',
        body: new URLSearchParams({ accion: 'stock_articulo', id })
    })
    .then(res => res.json())
    .then(resp => {
        if (resp.exito && resp.stock) {
            $('#stock_total').text(resp.stock.total || 0);
            $('#stock_activos').text(resp.stock.activos || 0);
            $('#stock_asignados').text(resp.stock.asignados || 0);
            $('#stock_mantenimiento').text(resp.stock.mantenimiento || 0);
        }
    });

    // 3) Cargar info del artículo
    fetch('php/articulo_ajax.php', {
        method: 'POST',
        body: new URLSearchParams({ accion: 'obtener_articulo', id })
    })
    .then(res => res.json())
    .then(resp => {
        if (resp.exito && resp.articulo) {
            $('#serial_codigo_articulo').text(resp.articulo.articulo_codigo || '');
            $('#serial_nombre_articulo').text(resp.articulo.articulo_nombre || '');
            const imgEl = document.getElementById('serial_imagen_articulo');
            if (imgEl) {
                imgEl.src = resp.articulo.articulo_imagen ? resp.articulo.articulo_imagen + '?t=' + Date.now() : 'img/icons/articulo.png';
                imgEl.alt = 'Imagen del artículo';
            }
        }
    });

    // 4) Abrir modal
    const dlg = document.querySelector('dialog[data-modal="seriales_articulo"]');
    if (dlg && typeof dlg.showModal === 'function') dlg.showModal();
});

// Limpiar tabla al cerrar modal
const dlgSeriales = document.querySelector('dialog[data-modal="seriales_articulo"]');
if (dlgSeriales) {
    dlgSeriales.addEventListener('close', () => {
        tablaSeriales.clear().draw();
        idsSeriales = [];
        limpiarError('error-container-inventario-serial');
    });
}

// Listener para alternar estado con stock dinámico
$('#articuloSerialTabla tbody').on('click', '.btn-estado', function () {
    if ($(this).prop('disabled')) return;
    let estadoActual = parseInt($(this).data('estado'));
    let nuevoEstado = estadoActual === 1 ? 3 : 1;
    $(this).data('estado', nuevoEstado);
    if (nuevoEstado === 1) {
        $(this).removeClass('mantenimiento').addClass('activo').text('Activo');
    } else {
        $(this).removeClass('activo').addClass('mantenimiento').text('Mantenimiento');
    }
    actualizarStockEnModal(); // recalcula stock al cambiar estado
});

// Guardar cambios en seriales del inventario
$('#form_inventario_seriales').on('submit', function (e) {
    e.preventDefault();
    limpiarError('error-container-inventario-serial');

    const filas = tablaSeriales.rows().nodes();
    const seriales = [];
    const serialSet = new Set();
    let hayDuplicadoLocal = false;

    $(filas).each(function (i, fila) {
        const id = idsSeriales[i];
        const $fila = $(fila);
        const serialInput = $fila.find('input.input_serial').eq(0);
        const obsInput = $fila.find('input.input_serial').eq(1);
        const estadoBtn = $fila.find('button.btn-estado');
        const serial = String(serialInput.val() || '').trim();
        const observacion = String(obsInput.val() || '').trim();
        const estado = parseInt(estadoBtn.data('estado'));
        if (serial !== '') {
            if (serialSet.has(serial)) {
                hayDuplicadoLocal = true;
                return false;
            }
            serialSet.add(serial);
        }
        seriales.push({ id, serial, observacion, estado });
    });

    if (hayDuplicadoLocal) {
        mostrarError('error-container-inventario-serial', 'Hay seriales repetidos dentro de este artículo.');
        return;
    }

    const articuloId = $('#articulo_id_hidden').val();

    // Validación contra BD solo con los no vacíos
    const serialesNoVacios = seriales.filter(s => s.serial !== '');

    // Función para actualizar seriales
    const actualizarSeriales = () => {
        $.post('php/articulo_ajax.php', {
            accion: 'actualizar_seriales',
            id: articuloId,
            seriales: JSON.stringify(seriales)
        }, function (resp) {
            if (resp && resp.exito) {
                limpiarError('error-container-inventario-serial');

                // Cerrar modal de seriales
                const dlgSeriales = document.querySelector('dialog[data-modal="seriales_articulo"]');
                if (dlgSeriales && typeof dlgSeriales.close === 'function') dlgSeriales.close();

                // Recargar tabla principal
                $('#articuloTabla').DataTable().ajax.reload(null, false);

                // Mostrar mensaje de éxito
                document.getElementById('success-message').textContent = 'Seriales actualizados correctamente.';
                const dlgSuccess = document.querySelector('dialog[data-modal="success"]');
                if (dlgSuccess && typeof dlgSuccess.showModal === 'function') dlgSuccess.showModal();
            } else {
                mostrarError('error-container-inventario-serial', resp.mensaje || 'Error al actualizar los seriales.');
            }
        }, 'json')
        .fail(() => {
            mostrarError('error-container-inventario-serial', 'Error de conexión al actualizar seriales.');
        });
    };

    if (serialesNoVacios.length > 0) {
        $.post('php/articulo_ajax.php', {
            accion: 'validar_seriales',
            seriales: JSON.stringify(serialesNoVacios) // aquí van con id y serial
        }, function (resp) {
            if (resp && resp.exito && Array.isArray(resp.repetidos) && resp.repetidos.length > 0) {
                mostrarError('error-container-inventario-serial', 'Hay seriales repetidos en la base de datos: ' + resp.repetidos.join(', '));
                return;
            }
            actualizarSeriales();
        }, 'json')
        .fail(() => {
            mostrarError('error-container-inventario-serial', 'Error de conexión al validar seriales.');
        });
    } else {
        actualizarSeriales();
    }
});

});
