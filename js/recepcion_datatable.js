window.addEventListener('load', function () {
    if (typeof $ !== 'function') {
        console.error('jQuery no está disponible');
        return;
    }

    let estadoRecepcion = 1; // 1 = habilitadas, 0 = anuladas
    const toggleBtn = document.getElementById('toggleEstadoRecepcion');

    if (toggleBtn) {
        toggleBtn.textContent = 'Anuladas';
        toggleBtn.classList.add('estado-rojo');

        toggleBtn.addEventListener('click', () => {
            estadoRecepcion = estadoRecepcion === 0 ? 1 : 0;

            if (estadoRecepcion === 0) {
                toggleBtn.textContent = 'Habilitadas';
                toggleBtn.classList.remove('estado-rojo');
                toggleBtn.classList.add('estado-verde');
            } else {
                toggleBtn.textContent = 'Anuladas';
                toggleBtn.classList.remove('estado-verde');
                toggleBtn.classList.add('estado-rojo');
            }

            tabla.ajax.reload(null, false);
        });
    }

    const tabla = $('#recepcionTabla').DataTable({
        scrollY: '500px',
        scrollCollapse: true,
        responsive: true,
        ajax: {
            url: 'php/recepcion_ajax.php',
            type: 'POST',
            data: function (d) {
                d.accion = 'leer_todos';
                d.estado = estadoRecepcion; // se envía al backend para filtrar habilitadas/anuladas
            },
            dataSrc: 'data',
            error: function (xhr, status, error) {
                console.error('Error AJAX:', error);
                console.log('Respuesta del servidor:', xhr.responseText);
            }
        },
        columns: [
            { data: 'recepcion_id' },
            { data: 'recepcion_fecha' },
            {
                data: 'recepcion_descripcion',
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
                    const estado = parseInt(row.recepcion_estado);
                    let botones = '';

                    if (estado === 1) {
                        // Recepción habilitada → Info + Anular
                        botones += `
                            <div class="acciones">
                                <div class="icon-action btn_ver_info" data-modal-target="info_recepcion" data-id="${row.recepcion_id}" title="Info">
                                    <img src="img/icons/info.png" alt="Info">
                                </div>
                                <div class="icon-action btn_anular" data-id="${row.recepcion_id}" title="Anular">
                                    <img src="img/icons/anular.png" alt="Anular">
                                </div>
                            </div>
                        `;
                    } else {
                        // Recepción anulada → Info + Recuperar
                        botones += `
                            <div class="acciones">
                                <div class="icon-action btn_ver_info" data-modal-target="info_recepcion" data-id="${row.recepcion_id}" title="Info">
                                    <img src="img/icons/info.png" alt="Info">
                                </div>
                                <div class="icon-action btn_recuperar" data-id="${row.recepcion_id}" title="Recuperar">
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
            paginate: { previous: "◀", next: "▶" }
        },
        lengthMenu: [[5, 10, 15, 20, 30], [5, 10, 15, 20, 30]],
        pageLength: 15,
    });

    // Acción: Ver info
    $('#recepcionTabla tbody').on('click', '.btn_ver_info', function () {
        const id = $(this).data('id');
        if (!id) return;

        fetch('php/recepcion_ajax.php', {
            method: 'POST',
            body: new URLSearchParams({ accion: 'obtener_recepcion', id })
        })
        .then(res => res.json())
        .then(data => {
            if (data.exito && data.recepcion) {
                mostrarInfoRecepcion(data.recepcion);
            }
        });
    });

    // Acción: Anular
    $('#recepcionTabla tbody').on('click', '.btn_anular', function () {
        const id = $(this).data('id');
        if (!id) return;

        fetch('php/recepcion_ajax.php', {
            method: 'POST',
            body: new URLSearchParams({ accion: 'obtener_recepcion', id })
        })
        .then(res => res.json())
        .then(data => {
            if (data.exito && data.recepcion) {
                mostrarConfirmacionRecepcion(data.recepcion, 'anular');
            }
        });
    });

    // Acción: Recuperar
    $('#recepcionTabla tbody').on('click', '.btn_recuperar', function () {
        const id = $(this).data('id');
        if (!id) return;

        fetch('php/recepcion_ajax.php', {
            method: 'POST',
            body: new URLSearchParams({ accion: 'obtener_recepcion', id })
        })
        .then(res => res.json())
        .then(data => {
            if (data.exito && data.recepcion) {
                mostrarConfirmacionRecepcion(data.recepcion, 'recuperar');
            }
        });
    });
});
