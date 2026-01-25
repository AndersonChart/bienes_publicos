// asignacion_datatable.js
// Configuración completa para la tabla de asignaciones
// Incluye: filtros, reporte individual, reporte general, estados, modales

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
        const parts = String(dstr).split('T')[0].split(' ')[0];
        const [y, m, d] = parts.split('-').map(x => parseInt(x, 10));
        if (!y || !m || !d) return null;
        return new Date(y, m - 1, d);
    }

    function todayDateOnly() {
        const t = new Date();
        return new Date(t.getFullYear(), t.getMonth(), t.getDate());
    }

    /* ===========================
       TABLA PRINCIPAL
    ============================ */
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
            dataSrc: 'data'
        },
        columns: [
            { data: 'asignacion_id', title: 'ID' },
            { data: 'persona_nombre', title: 'Personal' },
            { data: 'cargo_nombre', title: 'Cargo' },
            { data: 'area_nombre', title: 'Área' },
            {
                data: 'asignacion_fecha',
                title: 'Desde',
                render: function (data, type) {
                    if (!data) return '';
                    const f = toDateOnly(data) || new Date(data);
                    return type === 'display'
                        ? f.toLocaleDateString('es-VE')
                        : f.toISOString().split('T')[0];
                }
            },
            {
                data: 'asignacion_fecha_fin',
                title: 'Hasta',
                render: function (data, type) {
                    if (!data) return type === 'display' ? '—' : '';
                    const f = toDateOnly(data) || new Date(data);
                    return type === 'display'
                        ? f.toLocaleDateString('es-VE')
                        : f.toISOString().split('T')[0];
                }
            },
            {
                data: null,
                title: 'Estado',
                className: 'dt-center',
                render: function (row) {
                    if (parseInt(row.asignacion_estado) === 0) {
                        return '<span class="estado-badge anulado">Anulado</span>';
                    }

                    const fin = toDateOnly(row.asignacion_fecha_fin);
                    const hoy = todayDateOnly();
                    if (fin && fin <= hoy) {
                        return '<span class="estado-badge vencido">Vencido</span>';
                    }
                    return '<span class="estado-badge activo">Activo</span>';
                }
            },
            {
                data: null,
                title: 'Acciones',
                orderable: false,
                render: function (row) {
                    const id = row.asignacion_id;
                    const estado = parseInt(row.asignacion_estado);
                    const url = 'reportes/reporte_asignacion.php?id=' + id;

                    let html = '<div class="acciones">';

                    if (estado === 1) {
                        html += `
                            <a class="icon-action" href="${url}" target="_blank" title="Reporte PDF">
                                <img src="img/icons/reportepdf.png">
                            </a>
                            <a class="icon-action" href="index.php?vista=procesar_asignacion&id=${id}" title="Reasignar">
                                <img src="img/icons/reasignar.png">
                            </a>
                            <div class="icon-action btn_ver_info" data-id="${id}">
                                <img src="img/icons/info.png">
                            </div>
                            <div class="icon-action btn_anular" data-id="${id}">
                                <img src="img/icons/anular.png">
                            </div>`;
                    } else {
                        html += `
                            <div class="icon-action btn_ver_info" data-id="${id}">
                                <img src="img/icons/info.png">
                            </div>
                            <div class="icon-action btn_recuperar" data-id="${id}">
                                <img src="img/icons/recuperar.png">
                            </div>`;
                    }

                    html += '</div>';
                    return html;
                }
            }
        ],
        dom: '<"top"Bf>rt<"bottom"lpi><"clear">',
        buttons: [
            {
                text: 'Generar Reporte',
                action: function () {
                    const params = new URLSearchParams({
                        estado: estadoAsignacion,
                        cargo_id: document.getElementById('cargo_filtro')?.value || '',
                        persona_id: document.getElementById('persona_filtro')?.value || '',
                        area_id: document.getElementById('area_filtro')?.value || ''
                    });

                    window.open(
                        'reportes/reporte_asignacion_general.php?' + params.toString(),
                        '_blank'
                    );
                }
            }
        ],
        language: {
            search: "Buscar:",
            lengthMenu: "Mostrar _MENU_ registros",
            zeroRecords: "No se encontraron registros",
            emptyTable: "No hay registros",
            info: "Mostrando _START_ a _END_ de _TOTAL_",
            infoFiltered: "(filtrado de _MAX_)",
            paginate: { previous: "◀", next: "▶" }
        },
        pageLength: 15
    });

    // Recargar tabla al cambiar filtros
    $('#cargo_filtro, #persona_filtro, #area_filtro').on('change', function () {
        tabla.ajax.reload(null, false);
    });

});
