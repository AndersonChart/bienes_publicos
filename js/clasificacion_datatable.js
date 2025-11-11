window.addEventListener('load', function () {
    if (typeof $ !== 'function') {
        console.error('jQuery no está disponible');
        return;
    }

    let estadoActual = 1; // 0 = deshabilitados, 1 = habilitados
    const toggleBtn = document.getElementById('toggleEstado');

    toggleBtn.textContent = 'Deshabilitados';
    toggleBtn.classList.add('estado-rojo');

    const tabla = $('#clasificacionTabla').DataTable({
        scrollY: '500px',
        scrollCollapse: true,   
        responsive: true,
        ajax: {
            url: 'php/clasificacion_ajax.php',
            type: 'POST',
            data: function (d) {
                d.accion = 'leer_todos';
                d.estado = estadoActual;
                d.categoria_id = document.getElementById('categoria_filtro')?.value || '';
            },
            dataSrc: 'data',
            error: function (xhr, status, error) {
                console.error('Error AJAX:', error);
                console.log('Respuesta del servidor:', xhr.responseText);
            }
        },
        columns: [
            { data: 'clasificacion_codigo' },
            { data: 'clasificacion_nombre' },
            { data: 'categoria_nombre' },
            {
                data: 'clasificacion_descripcion',
                render: function (data, type, row) {
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
                    const estado = parseInt(row.clasificacion_estado);
                    let botones = '';

                        if (estado === 1) {
                            botones += `
                                <div class="acciones">
                                    <div class="icon-action" data-modal-target="new_clasificacion" title="Actualizar">
                                        <img src="img/icons/actualizar.png" alt="Actualizar">
                                    </div>
                                    <div class="icon-action btn_ver_info" data-modal-target="info_clasificacion" data-id="${row.clasificacion_id}" title="Info">
                                        <img src="img/icons/info.png" alt="Info">
                                    </div>
                                    <div class="icon-action btn_eliminar" data-id="${row.clasificacion_id}" title="Eliminar">
                                        <img src="img/icons/eliminar.png" alt="Eliminar">
                                    </div>
                                </div>
                            `;
                        } else {
                            botones += `
                                <div class="acciones">
                                    <div class="icon-action btn_ver_info" data-modal-target="info_clasificacion" data-id="${row.clasificacion_id}" title="Info">
                                        <img src="img/icons/info.png" alt="Info">
                                    </div>
                                    <div class="icon-action btn_recuperar" data-id="${row.clasificacion_id}" title="Recuperar">
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

    // Botón para alternar entre habilitados y deshabilitados
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

    // Acción: Actualizar
    $('#clasificacionTabla tbody').on('click', '.icon-action[title="Actualizar"]', function () {
        const fila = tabla.row($(this).closest('tr')).data();
        if (fila && fila.clasificacion_id) {
            abrirFormularioEdicionClasificacion(fila.clasificacion_id);
        }
    });

    // Acción: Ver info
    $('#clasificacionTabla tbody').on('click', '.btn_ver_info', function () {
        const id = $(this).data('id');
        if (!id) return;

        fetch('php/clasificacion_ajax.php', {
            method: 'POST',
            body: new URLSearchParams({ accion: 'obtener_clasificacion', id: id })
        })
        .then(res => res.json())
        .then(data => {
            if (data.exito && data.clasificacion) {
                mostrarInfoclasificacion(data.clasificacion);
            }
        });
    });

    // Eliminar
    $('#clasificacionTabla tbody').on('click', '.btn_eliminar', function () {
        const id = $(this).data('id');
        if (!id) return;

        fetch('php/clasificacion_ajax.php', {
            method: 'POST',
            body: new URLSearchParams({ accion: 'obtener_clasificacion', id })
        })
        .then(res => res.json())
        .then(data => {
            if (data.exito && data.clasificacion) {
                mostrarConfirmacionclasificacion(data.clasificacion, 'eliminar');
            }
        });
    });


    // Recuperar
    $('#clasificacionTabla tbody').on('click', '.btn_recuperar', function () {
        const id = $(this).data('id');
        if (!id) return;

        fetch('php/clasificacion_ajax.php', {
            method: 'POST',
            body: new URLSearchParams({ accion: 'obtener_clasificacion', id })
        })
        .then(res => res.json())
        .then(data => {
            if (data.exito && data.clasificacion) {
                mostrarConfirmacionclasificacion(data.clasificacion, 'recuperar');
            }
        });
    });


});

