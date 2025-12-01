window.addEventListener('load', function () {
    if (typeof $ !== 'function') {
        console.error('jQuery no está disponible');
        return;
    }

    let estadoActual = 1;
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
                                <div class="icon-action btn_ver" data-modal-target="ver_serial" data-id="${row.articulo_id}" title="Ver">
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
                                <div class="icon-action btn_ver" data-modal-target="ver_serial" data-id="${row.articulo_id}" title="Ver">
                                    <img src="img/icons/ver.png" alt="Ver Seriales">
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
        buttons: ['excel', 'pdf'],
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
        pageLength: 15,

        drawCallback: function(settings) {
            const api = this.api();
            const data = api.rows({ page: 'current' }).data();

            if (!data || data.length === 0) return;

            const tipos = [];
            for (let i = 0; i < data.length; i++) {
                const row = data[i];
                const tipoFila = row.categoria_tipo;
                const tipo = (tipoFila !== undefined && tipoFila !== null)
                    ? Number(tipoFila)
                    : Number(categoriaTipoMap[String(row.categoria_id)]);
                if (!Number.isNaN(tipo)) tipos.push(tipo);
            }

            if (tipos.length === 0) {
                api.column(4).visible(true);
                api.column(5).visible(true);
                console.warn('Sin categoria_tipo en filas ni mapa: columnas visibles por seguridad');
                return;
            }

            const todasBasicas   = tipos.every(t => t === 0);
            const todasCompletas = tipos.every(t => t === 1);

            if (todasBasicas) {
                api.column(4).visible(false);
                api.column(5).visible(false);
            } else {
                api.column(4).visible(true);
                api.column(5).visible(true);
            }
        }
    });

    // Eventos de articulos

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
                mostrarConfirmacionArticulo(data.articulo, 'eliminar');
            }
        });
    });

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
                mostrarConfirmacionArticulo(data.articulo, 'recuperar');
            }
        });
    });

    // NUEVO: Evento para abrir modal de seriales

    // Inicializar DataTable para seriales
    const tablaSeriales = $('#articuloSerialTabla').DataTable({
        paging: false,
        searching: false,
        info: false,
        ordering: false,
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


    // Abrir modal de seriales
    $('#articuloTabla tbody').on('click', '.btn_ver', function () {
        const id = $(this).data('id');
        if (!id) return;

        estadoArticuloActivo = id;
        idsSeriales = [];

        // 1) Cargar seriales con DataTable
        fetch('php/articulo_ajax.php', {
            method: 'POST',
            body: new URLSearchParams({ accion: 'listar_seriales', id })
        })
        .then(res => res.json())
        .then(data => {
            if (data.data) {
                const filas = data.data.map((s, i) => {
                    idsSeriales[i] = s.id;
                    const disabled = (parseInt(s.estado) === 2);
                    return {
                        numero: i + 1,
                        serial: `<input type="text" class="input_serial" value="${s.serial}" ${disabled ? 'disabled' : ''}>`,
                        observacion: `<input type="text" class="input_serial" value="${s.observacion || ''}" ${disabled ? 'disabled' : ''}>`,
                        estado: `<select class="input_serial" ${disabled ? 'disabled' : ''}>
                                    <option value="1" ${s.estado == 1 ? 'selected' : ''}>Activo</option>
                                    <option value="2" ${s.estado == 2 ? 'selected' : ''}>Asignado</option>
                                    <option value="3" ${s.estado == 3 ? 'selected' : ''}>Mantenimiento</option>
                                </select>`
                    };
                });

                tablaSeriales.clear().rows.add(filas).draw();
            }
        });

        // 2) Cargar stock
        fetch('php/articulo_ajax.php', {
            method: 'POST',
            body: new URLSearchParams({ accion: 'stock_articulo', id })
        })
        .then(res => res.json())
        .then(resp => {
            if (resp.exito && resp.stock) {
                $('.cantidad_seriales-total').text('Total: ' + resp.stock.total);
                $('.cantidad_seriales.activos').text('Activos: ' + resp.stock.activos);
                $('.cantidad_seriales.asignados').text('Asignados: ' + resp.stock.asignados);
                $('.cantidad_seriales.mantenimiento').text('Mantenimiento: ' + resp.stock.mantenimiento);
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
});
