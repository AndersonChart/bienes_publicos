// asignacion_datatable.js
// Configuración completa para la tabla de asignaciones
// Requisitos: jQuery, DataTables, backend en php/asignacion_ajax.php

window.addEventListener('load', function () {
    if (typeof $ !== 'function') {
        console.error('jQuery no está disponible');
        return;
    }

    let estadoAsignacion = 1; // 1 = activas, 0 = deshabilitadas
    const toggleBtn = document.getElementById('toggleEstadoAsignacion');

    if (toggleBtn) {
        toggleBtn.textContent = 'Deshabilitadas';
        toggleBtn.classList.add('estado-rojo');

        toggleBtn.addEventListener('click', () => {
            estadoAsignacion = estadoAsignacion === 0 ? 1 : 0;

            if (estadoAsignacion === 0) {
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

    // Tabla principal de asignaciones
    const tabla = $('#asignacionTabla').DataTable({
        scrollY: '500px',
        scrollCollapse: true,
        responsive: true,
        ajax: {
            url: 'php/asignacion_ajax.php',
            type: 'POST',
            data: function (d) {
                d.accion = 'leer_todos';
                d.estado = estadoAsignacion;
                d.cargo_id   = document.getElementById('cargo_filtro')?.value || '';
                d.persona_id = document.getElementById('persona_filtro')?.value || '';
                d.area_id    = document.getElementById('area_filtro')?.value || '';
            },
            dataSrc: 'data',
            error: function (xhr, status, error) {
                console.error('Error AJAX (asignaciones):', error);
                console.log('Respuesta del servidor:', xhr.responseText);
            }
        },
        columns: [
            { data: 'asignacion_id', title: 'ID' },
            { data: 'persona_nombre', title: 'Personal' },
            { data: 'cargo_nombre', title: 'Cargo' },
            { data: 'area_nombre', title: 'Área' },
            { data: 'asignacion_fecha', title: 'Desde',
            render: function (data) {
                if (!data) return '';
                const fecha = new Date(data);
                return fecha.toLocaleDateString('es-VE');
            }
        },
        { data: 'asignacion_fecha_fin', title: 'Hasta',
            render: function (data) {
                if (!data) return '—';
                const fecha = new Date(data);
                return fecha.toLocaleDateString('es-VE');
            }
            },
            {
                data: null, title: 'Acciones',
                render: function (row) {
                    const estado = parseInt(row.asignacion_estado);
                    const id = row.asignacion_id || '';
                    const url = 'reportes/reporte_asignacion.php?id=' + encodeURIComponent(id);

                    let botones = '<div class="acciones">';
                    botones += `<a class="icon-action btn_reporte" href="${url}" target="_blank" title="Reporte PDF">
                                    <img src="img/icons/reportepdf.png" alt="Reporte">
                                </a>`;
                    botones += `<div class="icon-action btn_ver_info" data-id="${id}" title="Info">
                                    <img src="img/icons/info.png" alt="Info">
                                </div>`;
                    if (estado === 1) {
                        botones += `<div class="icon-action btn_anular" data-id="${id}" title="Anular">
                                        <img src="img/icons/anular.png" alt="Anular">
                                    </div>`;
                    } else {
                        botones += `<div class="icon-action btn_recuperar" data-id="${id}" title="Recuperar">
                                        <img src="img/icons/recuperar.png" alt="Recuperar">
                                    </div>`;
                    }
                    botones += '</div>';
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

    // Recargar tabla al cambiar filtros
    $('#cargo_filtro, #persona_filtro, #area_filtro').on('change', function () {
        tabla.ajax.reload(null, false);
    });

    // Acción: Ver info + cargar resumen
    $('#asignacionTabla tbody').on('click', '.btn_ver_info', function () {
        const id = $(this).data('id');
        if (!id) return;

        fetch('php/asignacion_ajax.php', {
            method: 'POST',
            body: new URLSearchParams({ accion: 'obtener_asignacion', id })
        })
        .then(res => res.json())
        .then(data => {
            if (data.exito && data.asignacion) {
                document.getElementById('info_id').textContent = data.asignacion.asignacion_id;
                document.getElementById('info_area').textContent = data.asignacion.area_nombre;
                document.getElementById('info_persona').textContent = data.asignacion.persona_nombre + ' ' + (data.asignacion.persona_apellido || '');
                document.getElementById('info_cargo').textContent = data.asignacion.cargo_nombre;

                function formatFecha(fechaStr) {
                    if (!fechaStr) return '—';
                    const fecha = new Date(fechaStr);
                    return fecha.toLocaleDateString('es-VE');
                }

                document.getElementById('info_fecha').textContent = formatFecha(data.asignacion.asignacion_fecha);
                document.getElementById('info_fecha_fin').textContent = formatFecha(data.asignacion.asignacion_fecha_fin);
                document.getElementById('info_descripcion').textContent = data.asignacion.asignacion_descripcion || '';

                const modal = document.querySelector('dialog[data-modal="info_asignacion"]');
                if (modal?.showModal) modal.showModal();

                fetch('php/asignacion_ajax.php', {
                    method: 'POST',
                    body: new URLSearchParams({ accion: 'listar_articulos_por_asignacion', id })
                })
                .then(res => res.json())
                .then(resp => {
                    if (resp.data) {
                        const resumenTabla = $('#asignacionResumenTabla').DataTable();
                        resumenTabla.clear().rows.add(resp.data).draw();
                    }
                });
            }
        });
    });

    // Acción: Anular
    $('#asignacionTabla tbody').on('click', '.btn_anular', function () {
        const id = $(this).data('id');
        if (!id) return;

        fetch('php/asignacion_ajax.php', {
            method: 'POST',
            body: new URLSearchParams({ accion: 'obtener_asignacion', id })
        })
        .then(res => res.json())
        .then(data => {
            if (data.exito && data.asignacion) {
                mostrarConfirmacionAsignacion(data.asignacion, 'anular');
            }
        });
    });

    // Acción: Recuperar
    $('#asignacionTabla tbody').on('click', '.btn_recuperar', function () {
        const id = $(this).data('id');
        if (!id) return;

        fetch('php/asignacion_ajax.php', {
            method: 'POST',
            body: new URLSearchParams({ accion: 'obtener_asignacion', id })
        })
        .then(res => res.json())
        .then(data => {
            if (data.exito && data.asignacion) {
                mostrarConfirmacionAsignacion(data.asignacion, 'recuperar');
            }
        });
    });

        // Tabla resumen de artículos asociados
    const resumenTabla = $('#asignacionResumenTabla').DataTable({
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
        scrollY: '300px',
        scrollCollapse: true,
        responsive: true,
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

    $('#asignacionResumenTabla tbody').on('click', 'td.dt-control', function () {
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

    // ------------------------------
    // Helpers de confirmación (anular/recuperar)
    // ------------------------------
    function mostrarConfirmacionAsignacion(asignacion, accion) {
        let modalSelector = '';
        if (accion === 'anular') {
            modalSelector = 'dialog[data-modal="anular_asignacion"]';
            document.getElementById('anular_asignacion_id').textContent = asignacion.asignacion_id;
            document.getElementById('anular_asignacion_descripcion').textContent = asignacion.asignacion_descripcion || '';
            const form = document.getElementById('form_anular_asignacion');
            form.onsubmit = function (e) {
                e.preventDefault();
                fetch('php/asignacion_ajax.php', {
                    method: 'POST',
                    body: new URLSearchParams({ accion: 'anular', id: asignacion.asignacion_id })
                })
                .then(res => res.json())
                .then(resp => {
                    if (resp.exito) {
                        tabla.ajax.reload(null, false);
                        closeDialog(modalSelector);
                        // mensaje de éxito
                        document.getElementById('mensaje_exito').textContent = 'Asignación anulada correctamente';
                        const modalMsg = document.querySelector('dialog[data-modal="mensaje_exito"]');
                        if (modalMsg?.showModal) modalMsg.showModal();
                    } else {
                        // mensaje de error
                        document.getElementById('mensaje_error').textContent = resp.mensaje || 'Error al anular la asignación';
                        const modalMsg = document.querySelector('dialog[data-modal="mensaje_error"]');
                        if (modalMsg?.showModal) modalMsg.showModal();
                    }
                });
            };
        } else if (accion === 'recuperar') {
            modalSelector = 'dialog[data-modal="recuperar_asignacion"]';
            document.getElementById('recuperar_asignacion_id').textContent = asignacion.asignacion_id;
            document.getElementById('recuperar_asignacion_descripcion').textContent = asignacion.asignacion_descripcion || '';
            const form = document.getElementById('form_recuperar_asignacion');
            form.onsubmit = function (e) {
                e.preventDefault();
                fetch('php/asignacion_ajax.php', {
                    method: 'POST',
                    body: new URLSearchParams({ accion: 'recuperar', id: asignacion.asignacion_id })
                })
                .then(res => res.json())
                .then(resp => {
                    if (resp.exito) {
                        tabla.ajax.reload(null, false);
                        closeDialog(modalSelector);
                        // mensaje de éxito
                        document.getElementById('mensaje_exito').textContent = 'Asignación recuperada correctamente';
                        const modalMsg = document.querySelector('dialog[data-modal="mensaje_exito"]');
                        if (modalMsg?.showModal) modalMsg.showModal();
                    } else {
                        // mensaje de error
                        document.getElementById('mensaje_error').textContent = resp.mensaje || 'Error al recuperar la asignación';
                        const modalMsg = document.querySelector('dialog[data-modal="mensaje_error"]');
                        if (modalMsg?.showModal) modalMsg.showModal();
                    }
                });
            };
        }

        const modal = document.querySelector(modalSelector);
        if (modal?.showModal) modal.showModal();
    }

    function closeDialog(selector) {
        const dlg = document.querySelector(selector);
        if (dlg && typeof dlg.close === 'function') dlg.close();
    }
});
