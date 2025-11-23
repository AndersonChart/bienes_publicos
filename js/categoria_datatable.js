window.addEventListener('load', function () {
    if (typeof $ !== 'function') {
        console.error('jQuery no está disponible');
        return;
    }

    let estadoActual = 1; // 0 = deshabilitadas, 1 = habilitadas
    const toggleBtn = document.getElementById('toggleEstado');

    if (toggleBtn) {
        toggleBtn.textContent = 'Deshabilitadas';
        toggleBtn.classList.add('estado-rojo');

        toggleBtn.addEventListener('click', () => {
            estadoActual = estadoActual === 0 ? 1 : 0;

            if (estadoActual === 0) {
                toggleBtn.textContent = 'Habilitadas';
                toggleBtn.classList.remove('estado-rojo');
                toggleBtn.classList.add('estado-verde');
            } else {
                toggleBtn.textContent = 'Deshabilitadas';
                toggleBtn.classList.remove('estado-verde');
                toggleBtn.classList.add('estado-rojo');
            }

            tabla.ajax.reload(null, false);
        });
    }

    const tabla = $('#categoriaTabla').DataTable({
        scrollY: '500px',
        scrollCollapse: true,
        responsive: true,
        ajax: {
            url: 'php/categoria_ajax.php',
            type: 'POST',
            data: function (d) {
                d.accion = 'leer_todas';
                d.estado = estadoActual;
                d.categoria_tipo = document.getElementById('categoria_tipo_filtro')?.value || '';
            },
            dataSrc: 'data',
            error: function (xhr, status, error) {
                console.error('Error AJAX:', error);
                console.log('Respuesta del servidor:', xhr.responseText);
            }
        },
        columns: [
            { data: 'categoria_codigo' },
            { data: 'categoria_nombre' },
            {
                data: 'categoria_tipo',
                render: function (data) {
                    return parseInt(data) === 1 ? 'Completo' : 'Básico';
                }
            },
            {
                data: 'categoria_descripcion',
                render: function (data) {
                    if (!data) return '';
                    const maxLength = 100;
                    return data.length > maxLength
                        ? data.substring(0, maxLength) + '…'
                        : data;
                }
            },
            {
                data: null,
                render: function (data, type, row) {
                    const estado = parseInt(row.categoria_estado);
                    let botones = '';

                    if (estado === 1) {
                        botones += `
                            <div class="acciones">
                                <div class="icon-action" data-modal-target="new_categoria" title="Actualizar">
                                    <img src="img/icons/actualizar.png" alt="Actualizar">
                                </div>
                                <div class="icon-action btn_ver_info" data-modal-target="info_categoria" data-id="${row.categoria_id}" title="Info">
                                    <img src="img/icons/info.png" alt="Info">
                                </div>
                                <div class="icon-action btn_eliminar" data-id="${row.categoria_id}" title="Eliminar">
                                    <img src="img/icons/eliminar.png" alt="Eliminar">
                                </div>
                            </div>
                        `;
                    } else {
                        botones += `
                            <div class="acciones">
                                <div class="icon-action btn_ver_info" data-modal-target="info_categoria" data-id="${row.categoria_id}" title="Info">
                                    <img src="img/icons/info.png" alt="Info">
                                </div>
                                <div class="icon-action btn_recuperar" data-id="${row.categoria_id}" title="Recuperar">
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
        lengthMenu: [ [5, 10, 15, 20, 30], [5, 10, 15, 20, 30] ],
        pageLength: 15,
    });

    // Filtro de tipo
    const tipoFiltro = document.getElementById('categoria_tipo_filtro');
    if (tipoFiltro) {
        tipoFiltro.addEventListener('change', () => {
            tabla.ajax.reload(null, false);
        });
    }

    // Acción: Actualizar
    $('#categoriaTabla tbody').on('click', '.icon-action[title="Actualizar"]', function () {
        const fila = tabla.row($(this).closest('tr')).data();
        if (fila?.categoria_id) {
            abrirFormularioEdicionCategoria(fila.categoria_id);
        }
    });

    // Acción: Ver info
    $('#categoriaTabla tbody').on('click', '.btn_ver_info', function () {
        const id = $(this).data('id');
        if (!id) return;

        fetch('php/categoria_ajax.php', {
            method: 'POST',
            body: new URLSearchParams({ accion: 'obtener_categoria', id })
        })
        .then(res => res.json())
        .then(data => {
            if (data.exito && data.categoria) {
                mostrarInfoCategoria(data.categoria);
            }
        });
    });

    // Eliminar
    $('#categoriaTabla tbody').on('click', '.btn_eliminar', function () {
        const id = $(this).data('id');
        if (!id) return;

        fetch('php/categoria_ajax.php', {
            method: 'POST',
            body: new URLSearchParams({ accion: 'obtener_categoria', id })
        })
        .then(res => res.json())
        .then(data => {
            if (data.exito && data.categoria) {
                mostrarConfirmacionCategoria(data.categoria, 'eliminar');
            }
        });
    });

    // Recuperar
    $('#categoriaTabla tbody').on('click', '.btn_recuperar', function () {
        const id = $(this).data('id');
        if (!id) return;

        fetch('php/categoria_ajax.php', {
            method: 'POST',
            body: new URLSearchParams({ accion: 'obtener_categoria', id })
        })
        .then(res => res.json())
        .then(data => {
            if (data.exito && data.categoria) {
                mostrarConfirmacionCategoria(data.categoria, 'recuperar');
            }
        });
    });
});
