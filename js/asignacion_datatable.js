// asignacion_datatable.js
// Configuración completa para la tabla de asignaciones
// Requisitos: jQuery, DataTables, backend en php/asignacion_ajax.php

window.addEventListener('load', function () {
    if (typeof $ !== 'function') {
        console.error('jQuery no está disponible');
        return;
    }

    let estadoAsignacion = 1; // 1 = habilitadas, 0 = deshabilitadas
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

    function toDateOnly(dstr) {
        if (!dstr) return null;
        // Soporta "YYYY-MM-DD" y fechas con tiempo
        const parts = String(dstr).split('T')[0].split(' ')[0];
        const [y, m, d] = parts.split('-').map(x => parseInt(x, 10));
        if (!y || !m || !d) return null;
        return new Date(y, m - 1, d);
    }
    function todayDateOnly() {
        const t = new Date();
        return new Date(t.getFullYear(), t.getMonth(), t.getDate());
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
            {
                data: 'asignacion_fecha',
                title: 'Desde',
                render: function (data, type, row) {
                    if (!data) return type === 'display' ? '' : '';
                    const fecha = toDateOnly(data) || new Date(data);

                    if (type === 'display') {
                        return fecha.toLocaleDateString('es-VE');
                    }
                    // Para ordenar y filtrar devolvemos un valor ISO (YYYY-MM-DD)
                    return fecha.toISOString().split('T')[0];
                }
            },
            {
                data: 'asignacion_fecha_fin',
                title: 'Hasta',
                render: function (data, type, row) {
                    if (!data) return type === 'display' ? '—' : '';
                    const fecha = toDateOnly(data) || new Date(data);

                    if (type === 'display') {
                        return fecha.toLocaleDateString('es-VE');
                    }
                    return fecha.toISOString().split('T')[0];
                }
            },
            { 
                data: null,
                title: 'Estado',
                className: 'dt-center',
                render: function (row) {
                    const estado = parseInt(row.asignacion_estado);
                    if (estado === 0) {
                        return '<span class="estado-badge anulado">Anulado</span>';
                    }

                    const fin = toDateOnly(row.asignacion_fecha_fin);
                    const hoy = todayDateOnly();
                    if (fin && fin.getTime() <= hoy.getTime()) {
                        return '<span class="estado-badge vencido">Vencido</span>';
                    }
                    return '<span class="estado-badge activo">Activo</span>';
                }
            },
            {
                data: null, 
                title: 'Acciones',
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
        pageLength: 15
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
                    const fecha = toDateOnly(fechaStr) || new Date(fechaStr);
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

    // ------------------------------
    // Tabla resumen de artículos asociados (child rows)
    // ------------------------------
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
    // Anular: abrir confirmación con datos
    // ------------------------------
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
                document.getElementById('anular_asignacion_id').textContent = data.asignacion.asignacion_id;
                document.getElementById('anular_personal_nombre').textContent = data.asignacion.persona_nombre + ' ' + (data.asignacion.persona_apellido || '');
                document.getElementById('anular_area_nombre').textContent = data.asignacion.area_nombre;

                const modal = document.querySelector('dialog[data-modal="anular_asignacion"]');
                if (modal?.showModal) modal.showModal();
            }
        });
    });

    // Confirmar anulación
    document.getElementById('form_anular_asignacion')?.addEventListener('submit', function (e) {
        e.preventDefault();
        const id = document.getElementById('anular_asignacion_id').textContent;
        if (!id) return;

        fetch('php/asignacion_ajax.php', {
            method: 'POST',
            body: new URLSearchParams({ accion: 'anular', id })
        })
        .then(res => res.json())
        .then(resp => {
            const modal = document.querySelector('dialog[data-modal="anular_asignacion"]');
            if (modal?.open) modal.close();

            if (resp.exito) {
                document.getElementById('success-message').textContent = resp.mensaje || 'La asignación fue anulada correctamente';
                const successModal = document.querySelector('dialog[data-modal="success"]');
                if (successModal?.showModal) successModal.showModal();
                tabla.ajax.reload(null, false);
            } else {
                document.getElementById('error-message').textContent = resp.mensaje || 'No se puede anular la asignación';
                const errorModal = document.querySelector('dialog[data-modal="error"]');
                if (errorModal?.showModal) errorModal.showModal();
            }
        })
        .catch(() => {
            const modal = document.querySelector('dialog[data-modal="anular_asignacion"]');
            if (modal?.open) modal.close();
            document.getElementById('error-message').textContent = 'Error de conexión con el servidor';
            const errorModal = document.querySelector('dialog[data-modal="error"]');
            if (errorModal?.showModal) errorModal.showModal();
        });
    });

    // ------------------------------
    // Recuperar: abrir confirmación con datos
    // ------------------------------
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
                document.getElementById('recuperar_asignacion_id').textContent = data.asignacion.asignacion_id;
                document.getElementById('recuperar_personal_nombre').textContent = data.asignacion.persona_nombre + ' ' + (data.asignacion.persona_apellido || '');
                document.getElementById('recuperar_area_nombre').textContent = data.asignacion.area_nombre;

                const modal = document.querySelector('dialog[data-modal="recuperar_asignacion"]');
                if (modal?.showModal) modal.showModal();
            }
        });
    });

    // Confirmar recuperación
    document.getElementById('form_recuperar_asignacion')?.addEventListener('submit', function (e) {
        e.preventDefault();
        const id = document.getElementById('recuperar_asignacion_id').textContent;
        if (!id) return;

        fetch('php/asignacion_ajax.php', {
            method: 'POST',
            body: new URLSearchParams({ accion: 'recuperar', id })
        })
        .then(res => res.json())
        .then(resp => {
            const modal = document.querySelector('dialog[data-modal="recuperar_asignacion"]');
            if (modal?.open) modal.close();

            if (resp.exito) {
                document.getElementById('success-message').textContent = resp.mensaje || 'La asignación fue recuperada correctamente';
                const successModal = document.querySelector('dialog[data-modal="success"]');
                if (successModal?.showModal) successModal.showModal();
                tabla.ajax.reload(null, false);
            } else {
                document.getElementById('error-message').textContent = resp.mensaje || 'No se pudo recuperar la asignación: seriales comprometidos';
                const errorModal = document.querySelector('dialog[data-modal="error"]');
                if (errorModal?.showModal) errorModal.showModal();
            }
        })
        .catch(() => {
            const modal = document.querySelector('dialog[data-modal="recuperar_asignacion"]');
            if (modal?.open) modal.close();
            document.getElementById('error-message').textContent = 'Error de conexión con el servidor';
            const errorModal = document.querySelector('dialog[data-modal="error"]');
            if (errorModal?.showModal) errorModal.showModal();
        });
    });

    // ------------------------------
    // Cierre de modales de éxito/error
    // ------------------------------
    document.getElementById('close-success-asignacion')?.addEventListener('click', function () {
        const modal = document.querySelector('dialog[data-modal="success"]');
        if (modal?.open) modal.close();
    });

    document.getElementById('close-error-asignacion')?.addEventListener('click', function () {
        const modal = document.querySelector('dialog[data-modal="error"]');
        if (modal?.open) modal.close();
    });
});
