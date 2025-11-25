window.addEventListener('load', function () {
    if (typeof $ !== 'function') {
        console.error('jQuery no está disponible');
        return;
    }

    const usuarioRol = parseInt(document.getElementById('usuario')?.dataset.id || '0');

    const tabla = $('#recepcionTabla').DataTable({
        scrollY: '500px',
        scrollCollapse: true,
        responsive: true,
        ajax: {
            url: 'php/recepcion_ajax.php',
            type: 'POST',
            data: function (d) {
                d.accion = 'leer_todos';
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
            { data: 'recepcion_descripcion' },
            {
                data: null,
                render: function (data, type, row) {
                    let botones = `
                        <div class="acciones">
                            <div class="icon-action btn_ver_info" data-id="${row.recepcion_id}" title="Info">
                                <img src="img/icons/info.png" alt="Info">
                            </div>
                    `;

                    // Solo rol 2 (Administrador) y 3 (Ingeniero) pueden ver el botón Anular
                    if (usuarioRol === 2 || usuarioRol === 3) {
                        botones += `
                            <div class="icon-action btn_anular" data-id="${row.recepcion_id}" title="Anular">
                                <img src="img/icons/anular.png" alt="Anular">
                            </div>
                        `;
                    }

                    botones += `</div>`;
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

    // Listener: ver información
    $('#recepcionTabla tbody').on('click', '.btn_ver_info', function () {
        const id = $(this).data('id');
        if (!id) return;
        fetch('php/recepcion_ajax.php', {
            method: 'POST',
            body: new URLSearchParams({ accion: 'obtener_recepcion', id })
        })
        .then(res => res.json())
        .then(data => {
            if (data.exito && data.recepcion) mostrarInfoRecepcion(data.recepcion);
        });
    });

    // Listener: anular recepción
    $('#recepcionTabla tbody').on('click', '.btn_anular', function () {
        const id = $(this).data('id');
        if (!id) return;
        fetch('php/recepcion_ajax.php', {
            method: 'POST',
            body: new URLSearchParams({ accion: 'obtener_recepcion', id })
        })
        .then(res => res.json())
        .then(data => {
            if (data.exito && data.recepcion) mostrarConfirmacionRecepcion(data.recepcion, 'anular');
        });
    });
});

