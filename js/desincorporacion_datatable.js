// desincorporacion_datatable.js
window.addEventListener('load', function () {
    if (typeof $ !== 'function') {
        console.error('jQuery no está disponible');
        return;
    }

    let estadoDesincorporacion = 1; // 1 = habilitadas, 0 = anuladas
    const toggleBtn = document.getElementById('toggleEstadoDesincorporacion');

    if (toggleBtn) {
        toggleBtn.textContent = 'Anuladas';
        toggleBtn.classList.add('estado-rojo');

        toggleBtn.addEventListener('click', () => {
            estadoDesincorporacion = estadoDesincorporacion === 0 ? 1 : 0;

            if (estadodesincorporacion === 0) {
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

    // Tabla principal de desincorporaciones
    const tabla = $('#desincorporacionTabla').DataTable({
        scrollY: '500px',
        scrollCollapse: true,
        responsive: true,
        ajax: {
            url: 'php/desincorporacion_ajax.php',
            type: 'POST',
            data: function (d) {
                d.accion = 'leer_todos';
                d.estado = estadoDesincorporacion;
            },
            dataSrc: 'data',
            error: function (xhr, status, error) {
                console.error('Error AJAX:', error);
                console.log('Respuesta del servidor:', xhr.responseText);
            }
        },
        columns: [
            { data: 'desincorporacion_id' },
            { data: 'desincorporacion_fecha',
            render: function (data) {
                const fecha = new Date(data);
                return fecha.toLocaleDateString('es-VE');
            }
            },
            {
                data: 'desincorporacion_descripcion',
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
                    const estado = parseInt(row.desincorporacion_estado);
                    const id = row.desincorporacion_id || '';
                    const urlPDF = 'reportes/reporte_desincorporacion.php?id=' + encodeURIComponent(id);

                    let botones = `
                        <div class="acciones">
                            <div class="icon-action btn_ver_info" data-id="${row.desincorporacion_id}" title="Info">
                                <img src="img/icons/info.png" alt="Info">
                            </div>
                            <a class="icon-action btn_reporte" href="${urlPDF}" target="_blank" title="Reporte PDF">
                                <img src="img/icons/reportepdf.png" alt="PDF">
                            </a>
                    `;

                    if (estado === 1) {
                        botones += `
                            <div class="icon-action btn_anular" data-id="${row.desincorporacion_id}" title="Anular">
                                <img src="img/icons/anular.png" alt="Anular">
                            </div>
                        `;
                    } else {
                        botones += `
                            <div class="icon-action btn_recuperar" data-id="${row.desincorporacion_id}" title="Recuperar">
                                <img src="img/icons/recuperar.png" alt="Recuperar">
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
        buttons: [
            {
                text: 'Generar Reporte',
                className: 'btn-reporte',
                action: function () {
                window.open('reportes/reporte_desincorporaciones_general.php', '_blank');
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
            paginate: { previous: "◀", next: "▶" }
        },
        lengthMenu: [[5, 10, 15, 20, 30], [5, 10, 15, 20, 30]],
        pageLength: 15,
    });

    // Acción: Ver info + cargar resumen
    $('#desincorporacionTabla tbody').on('click', '.btn_ver_info', function () {
        const id = $(this).data('id');
        if (!id) return;

        // Primero obtener datos de la recepción
        fetch('php/desincorporacion_ajax.php', {
            method: 'POST',
            body: new URLSearchParams({ accion: 'obtener_desincorporacion', id })
        })
        .then(res => res.json())
        .then(data => {
            if (data.exito && data.desincorporacion) {
                // rellenar los spans
                document.getElementById('info_desincorporacion_id').textContent = data.desincorporacion.desincorporacion_id;
                document.getElementById('info_desincorporacion_fecha').textContent = data.desincorporacion.desincorporacion_fecha;
                document.getElementById('info_desincorporacion_descripcion').textContent = data.desincorporacion.desincorporacion_descripcion;

                // abrir modal
                const modal = document.querySelector('dialog[data-modal="info_desincorporacion"]');
                if (modal?.showModal) modal.showModal();

                // cargar artículos asociados
                fetch('php/desincorporacion_ajax.php', {
                    method: 'POST',
                    body: new URLSearchParams({ accion: 'listar_articulos_por_ajuste', id })
                })
                .then(res => res.json())
                .then(resp => {
                    if (resp.data) {
                        const resumenTabla = $('#desincorporacionResumenTabla').DataTable();
                        resumenTabla.clear().rows.add(resp.data).draw();
                    }
                });
            }
        });
    });



    // Acción: Anular
    $('#desincorporacionTabla tbody').on('click', '.btn_anular', function () {
        const id = $(this).data('id');
        if (!id) return;

        fetch('php/desincorporacion_ajax.php', {
            method: 'POST',
            body: new URLSearchParams({ accion: 'obtener_desincorporacion', id })
        })
        .then(res => res.json())
        .then(data => {
            if (data.exito && data.desincorporacion) {
                mostrarConfirmacionDesincorporacion(data.desincorporacion, 'anular');
            }
        });
    });

    // Acción: Recuperar
    $('#desincorporacionTabla tbody').on('click', '.btn_recuperar', function () {
        const id = $(this).data('id');
        if (!id) return;

        fetch('php/desincorporacion_ajax.php', {
            method: 'POST',
            body: new URLSearchParams({ accion: 'obtener_desincorporacion', id })
        })
        .then(res => res.json())
        .then(data => {
            if (data.exito && data.desincorporacion) {
                mostrarConfirmacionDesincorporacion(data.desincorporacion, 'recuperar');
            }
        });
    });

    // Tabla resumen de artículos asociados (con cantidad y child rows para seriales)
    const resumenTabla = $('#desincorporacionResumenTabla').DataTable({
        data: [],
        columns: [
            { data: 'articulo_codigo', title: 'Código' },
            { data: 'articulo_nombre', title: 'Nombre' },
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
        scrollY: '300px',       // altura fija para que no se expanda
        scrollCollapse: true,
        responsive: true,       //  adapta columnas al ancho
        paging: false,
        searching: false,
        info: false,
        language: { emptyTable: 'No se encuentran registros' }
    });


    // child rows para seriales
    function formatSeriales(rowData) {
        if (!rowData.seriales) return '<div>No se ingresaron seriales</div>';
        const lista = rowData.seriales.split(',').map(s => s.trim());
        let html = '<ul>';
        lista.forEach((s, i) => {
            html += `<li><strong>${i + 1}:</strong> ${s}</li>`;
        });
        html += '</ul>';
        return html;
    }

    $('#desincorporacionResumenTabla tbody').on('click', 'td.dt-control', function () {
        const tr = $(this).closest('tr');
        const row = resumenTabla.row(tr);

        if (row.child.isShown()) {
            row.child.hide();
            tr.removeClass('shown');
            $(this).find('.toggle-details').text('▶');
        } else {
            row.child(formatSeriales(row.data())).show();
            tr.addClass('shown');
            $(this).find('.toggle-details').text('▼');
        }
    });
});
