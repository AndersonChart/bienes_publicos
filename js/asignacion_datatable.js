window.addEventListener('load', function () {
    if (typeof $ !== 'function') {
        console.error('jQuery no está disponible');
        return;
    }

    let estadoActual = 1;
    const toggleBtn = document.getElementById('toggleEstadoAsignacion');

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

    const tabla = $('#asignacionTabla').DataTable({
        scrollY: '500px',
        scrollCollapse: true,
        responsive: true,
        ajax: {
            url: 'php/asignacion_ajax.php',
            type: 'POST',
            data: function (d) {
                d.accion = 'leer_todos';
                d.estado = estadoActual;
                d.area_id = document.getElementById('area_filtro')?.value || '';
                d.persona_id = document.getElementById('persona_filtro')?.value || '';
            },
            dataSrc: 'data'
        },
        columns: [
            { data: 'asignacion_id', title: 'ID' },
            { data: 'area_nombre', title: 'Área' },
            { data: 'persona_nombre', title: 'Persona' },
            { data: 'asignacion_fecha', title: 'Fecha' },
            { data: 'asignacion_fecha_fin', title: 'Fecha fin' },
            { data: 'cantidad_articulos', title: '# Artículos' },
            {
                data: null, title: 'Acciones',
                render: function (row) {
                   
                    const id = row.asignacion_id || '';
                    // ruta relativa desde la página; si tu app está en /bienes_publicos usa la ruta absoluta que indico abajo
                    const url = 'reportes/reporte_asignacion.php?id=' + encodeURIComponent(id);

                    let botones = `<div class="acciones">`;
                    botones += `<a class="icon-action btn_reporte" href="${url}" target="_blank" title="Reporte PDF"><img src="img/icons/reportepdf.png" alt="Reporte"></a>`;   
                    botones += `<div class="icon-action btn_info" data-id="${row.asignacion_id}" title="Info"><img src="img/icons/info.png" alt="Info"></div>`;
                    if (row.asignacion_estado == 1) {
                        botones += `<div class="icon-action btn_finalizar" data-id="${row.asignacion_id}" title="Finalizar"><img src="img/icons/eliminar.png" alt="Finalizar"></div>`;
                    } else {
                        botones += `<div class="icon-action btn_recuperar" data-id="${row.asignacion_id}" title="Recuperar"><img src="img/icons/recuperar.png" alt="Recuperar"></div>`;
                    }
                    botones += `</div>`;
                    return botones;
                },
                orderable: false
            }
        ],
        paging: true,
        info: true,
        language: {
            search: "Buscar:",
            lengthMenu: "Mostrar _MENU_ registros",
            zeroRecords: "No se encontraron registros",
            emptyTable: "No hay ningún registro",
            info: "Mostrando _START_ a _END_ de _TOTAL_ registros",
            paginate: { previous: "◀", next: "▶" }
        },
        lengthMenu: [[5,10,15,20,30],[5,10,15,20,30]],
        pageLength: 15
    });

    // Cargar filtros (areas/personas)
    function cargarFiltros() {
        fetch('php/area_ajax.php', { method: 'POST', body: new URLSearchParams({ accion: 'leer_todos' }) })
        .then(res => res.json())
        .then(data => {
            if (Array.isArray(data.data)) {
                let sel = document.getElementById('area_filtro');
                let sel2 = document.getElementById('area_id');
                data.data.forEach(a => {
                    const opt = document.createElement('option'); opt.value = a.area_id; opt.text = a.area_nombre;
                    sel.appendChild(opt);
                    const opt2 = opt.cloneNode(true); sel2.appendChild(opt2);
                });
            }
        });

        fetch('php/personal_ajax.php', { method: 'POST', body: new URLSearchParams({ accion: 'leer_todos' }) })
        .then(res => res.json())
        .then(data => {
            if (Array.isArray(data.data)) {
                let sel = document.getElementById('persona_filtro');
                let sel2 = document.getElementById('persona_id');
                data.data.forEach(p => {
                    const opt = document.createElement('option'); opt.value = p.persona_id; opt.text = p.persona_nombre + ' ' + p.persona_apellido;
                    sel.appendChild(opt);
                    const opt2 = opt.cloneNode(true); sel2.appendChild(opt2);
                });
            }
        });
    }
    cargarFiltros();

    // Recargar tabla al cambiar filtros
    document.getElementById('area_filtro')?.addEventListener('change', () => tabla.ajax.reload());
    document.getElementById('persona_filtro')?.addEventListener('change', () => tabla.ajax.reload());

    // Abrir modal nuevo
    document.querySelectorAll('[data-modal-target="new_asignacion"]').forEach(btn => {
        btn.addEventListener('click', () => {
            openModal('new_asignacion');
            // limpiar formulario
            document.getElementById('form_nueva_asignacion').reset();
            document.querySelector('#tabla_seriales_agregados tbody').innerHTML = '';
            document.getElementById('asignacion_id').value = '';
        });
    });

    // Buscar seriales disponibles
    document.getElementById('btn_buscar_serial')?.addEventListener('click', function () {
        const texto = document.getElementById('buscar_serial').value.trim();
        fetch('php/asignacion_ajax.php', {
            method: 'POST',
            body: new URLSearchParams({ accion: 'buscar_seriales', texto })
        }).then(r => r.json()).then(data => {
            const cont = document.getElementById('resultados_seriales');
            cont.innerHTML = '';
            if (data.data && data.data.length) {
                data.data.forEach(s => {
                    const div = document.createElement('div');
                    div.style.display = 'flex';
                    div.style.justifyContent = 'space-between';
                    div.style.padding = '6px';
                    div.style.borderBottom = '1px solid #eee';
                    div.innerHTML = `<div><strong>${s.articulo_serial}</strong> — ${s.articulo_nombre} ${s.articulo_modelo ? '('+s.articulo_modelo+')' : ''}</div>
                                     <div><button class="btn_agregar_serial" data-id="${s.articulo_serial_id}" data-serial="${s.articulo_serial}" data-nombre="${s.articulo_nombre}">Agregar</button></div>`;
                    cont.appendChild(div);
                });
            } else {
                cont.innerHTML = '<div>No hay seriales disponibles</div>';
            }
        });
    });

    // Delegación: agregar serial al listado
    document.getElementById('resultados_seriales').addEventListener('click', function (e) {
        if (e.target && e.target.matches('.btn_agregar_serial')) {
            const id = e.target.dataset.id;
            const serial = e.target.dataset.serial;
            const nombre = e.target.dataset.nombre;
            agregarSerialTabla(id, serial, nombre);
        }
    });

    function agregarSerialTabla(id, serial, nombre) {
        // evitar duplicados
        const rows = document.querySelectorAll('#tabla_seriales_agregados tbody tr');
        for (let r of rows) {
            if (r.dataset.id === String(id)) return;
        }
        const tr = document.createElement('tr');
        tr.dataset.id = id;
        tr.innerHTML = `<td>${serial}</td><td>${nombre}</td><td><button class="btn_quitar_serial">Quitar</button></td>`;
        document.querySelector('#tabla_seriales_agregados tbody').appendChild(tr);
    }

    // quitar serial
    document.querySelector('#tabla_seriales_agregados tbody').addEventListener('click', function (e) {
        if (e.target && e.target.matches('.btn_quitar_serial')) {
            e.target.closest('tr').remove();
        }
    });

    // submit crear asignacion
    document.getElementById('form_nueva_asignacion').addEventListener('submit', function (ev) {
        ev.preventDefault();
        const areaId = document.getElementById('area_id').value;
        const personaId = document.getElementById('persona_id').value;
        const fecha = document.getElementById('asignacion_fecha').value;
        const fechaFin = document.getElementById('asignacion_fecha_fin').value;
        const filas = document.querySelectorAll('#tabla_seriales_agregados tbody tr');
        const seriales = Array.from(filas).map(r => r.dataset.id);

        if (!areaId || !personaId || !fecha) {
            mostrarError('Rellene los campos obligatorios');
            return;
        }
        if (seriales.length === 0) {
            mostrarError('Agregue al menos un serial a la asignación');
            return;
        }

        fetch('php/asignacion_ajax.php', {
            method: 'POST',
            body: new URLSearchParams({
                accion: 'crear',
                area_id: areaId,
                persona_id: personaId,
                asignacion_fecha: fecha,
                asignacion_fecha_fin: fechaFin,
                seriales: JSON.stringify(seriales)
            })
        }).then(r => r.json()).then(data => {
            if (data.exito) {
                closeModal('new_asignacion');
                tabla.ajax.reload();
                mostrarSuccess(data.mensaje || 'Asignación creada');
            } else {
                mostrarError(data.mensaje || 'Error al crear');
            }
        }).catch(err => {
            console.error(err);
            mostrarError('Error interno');
        });
    });

    // Ver info
    $('#asignacionTabla tbody').on('click', '.btn_info', function () {
        const id = $(this).data('id');
        fetch('php/asignacion_ajax.php', {
            method: 'POST',
            body: new URLSearchParams({ accion: 'obtener_asignacion', id })
        }).then(res => res.json()).then(data => {
            if (data.exito && data.asignacion) {
                const a = data.asignacion;
                document.getElementById('info_id').textContent = a.asignacion_id;
                document.getElementById('info_area').textContent = a.area_nombre;
                document.getElementById('info_persona').textContent = a.persona_nombre;
                document.getElementById('info_fecha').textContent = a.asignacion_fecha;
                document.getElementById('info_fecha_fin').textContent = a.asignacion_fecha_fin || '—';
                document.getElementById('info_estado').textContent = a.asignacion_estado == 1 ? 'Activa' : 'Finalizada / Deshabilitada';
                const ul = document.getElementById('info_articulos_lista');
                ul.innerHTML = '';
                if (Array.isArray(a.articulos)) {
                    a.articulos.forEach(it => {
                        const li = document.createElement('li');
                        li.textContent = `${it.articulo_serial} — ${it.articulo_nombre} ${it.articulo_modelo ? '('+it.articulo_modelo+')' : ''}`;
                        ul.appendChild(li);
                    });
                }
                openModal('info_asignacion');
            }
        });
    });

    // Finalizar (usa icon eliminar como ejemplo)
    $('#asignacionTabla tbody').on('click', '.btn_finalizar', function () {
        const id = $(this).data('id');
        if (!confirm('¿Finalizar esta asignación? (esto la deshabilitará)')) return;
        fetch('php/asignacion_ajax.php', {
            method: 'POST',
            body: new URLSearchParams({ accion: 'finalizar', id, fecha_fin: new Date().toISOString().slice(0,10), devolver_seriales: 0 })
        }).then(r=>r.json()).then(d=>{
            if (d.exito) {
                tabla.ajax.reload();
                mostrarSuccess('Asignación finalizada');
            } else mostrarError(d.mensaje || 'Fallo');
        });
    });

    // Recuperar (si procede)
    $('#asignacionTabla tbody').on('click', '.btn_recuperar', function () {
        const id = $(this).data('id');
        if (!confirm('Recuperar asignación?')) return;
        fetch('php/asignacion_ajax.php', {
            method: 'POST',
            body: new URLSearchParams({ accion: 'recuperar', id })
        }).then(r=>r.json()).then(d=>{
            if (d.exito) { tabla.ajax.reload(); mostrarSuccess('Asignación recuperada'); }
            else mostrarError(d.mensaje || 'Fallo');
        });
    });

    // Helpers: modales y mensajes
    function openModal(name) {
        document.querySelectorAll('[data-modal]').forEach(d => { if (d.dataset.modal === name) d.showModal(); });
    }
    function closeModal(name) {
        document.querySelectorAll('[data-modal]').forEach(d => { if (d.dataset.modal === name) d.close(); });
    }
    function mostrarError(text) {
        const c = document.getElementById('error-container-asignacion');
        c.textContent = text;
        c.style.display = 'block';
        setTimeout(()=>c.style.display='none', 5000);
    }
    function mostrarSuccess(text) {
        // Reutiliza el modal success si lo tienes, si no: alert
        alert(text);
    }
});
