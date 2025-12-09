window.addEventListener('load', function () {
    if (typeof $ !== 'function') {
        console.error('jQuery no está disponible');
        return;
    }

    let estadoActual = 1; // 0 = deshabilitados, 1 = habilitados
    const toggleBtn = document.getElementById('toggleEstado');
    const usuarioRol = parseInt(document.getElementById('usuario')?.dataset.id || '0');

    // Solo configurar el botón si existe (rol 3)
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

    const tabla = $('#usuarioTabla').DataTable({
        scrollY: '500px',
        scrollCollapse: true,   
        responsive: true,
        ajax: {
            url: 'php/usuario_ajax.php',
            type: 'POST',
            data: function (d) {
                d.accion = 'leer_todos';
                d.estado = estadoActual;
            },
            dataSrc: 'data',
            error: function (xhr, status, error) {
                console.error('Error AJAX:', error);
                console.log('Respuesta del servidor:', xhr.responseText);
            }
        },
        columns: [
            { data: 'usuario_id' },
            { data: 'usuario_nombre' },
            { data: 'usuario_apellido' },
            { data: 'usuario_cedula' },
            { data: 'usuario_correo' },
            { data: 'usuario_telefono' },
            {
                data: 'usuario_foto',
                render: function (data) {
                    const foto = data || 'img/icons/perfil.png';
                    return `<img src="${foto}" alt="Foto" width="40">`;
                },
                orderable: false
            },
            {
                data: null,
                render: function (data, type, row) {
                    const estado = parseInt(row.usuario_estado);
                    let botones = '';

                    if (usuarioRol !== 1) {
                        if (estado === 1) {
                            botones += `
                                <div class="acciones">
                                    <div class="icon-action" data-modal-target="new_user" title="Actualizar">
                                        <img src="img/icons/actualizar.png" alt="Actualizar">
                                    </div>
                                    <div class="icon-action btn_ver_info" data-modal-target="info_usuario" data-id="${row.usuario_id}" title="Info">
                                        <img src="img/icons/info.png" alt="Info">
                                    </div>
                                    <div class="icon-action btn_eliminar" data-id="${row.usuario_id}" title="Eliminar">
                                        <img src="img/icons/eliminar.png" alt="Eliminar">
                                    </div>
                                </div>
                            `;
                        } else {
                            botones += `
                                <div class="acciones">
                                    <div class="icon-action btn_ver_info" data-modal-target="info_usuario" data-id="${row.usuario_id}" title="Info">
                                        <img src="img/icons/info.png" alt="Info">
                                    </div>
                                    <div class="icon-action btn_recuperar" data-id="${row.usuario_id}" title="Recuperar">
                                        <img src="img/icons/recuperar.png" alt="Recuperar">
                                    </div>
                                </div>
                            `;
                        }
                    } else {
                        botones += `
                            <div class="acciones">
                                <div class="icon-action btn_ver_info" data-modal-target="info_usuario" data-id="${row.usuario_id}" title="Info">
                                    <img src="img/icons/info.png" alt="Info">
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
        buttons: [
            {
                text: 'Generar Reporte',
                className: 'btn-reporte',
                action: function () {
                window.open('reportes/reporte_usuario.php', '_blank');
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
            paginate: {
                previous: "◀",
                next: "▶"
            }
        },
        lengthMenu: [ [5, 10, 15, 20, 30], [5, 10, 15, 20, 30] ],
        pageLength: 15,
    });

    // Listeners para acciones
    $('#usuarioTabla tbody').on('click', '.icon-action[title="Actualizar"]', function () {
        const fila = tabla.row($(this).closest('tr')).data();
        if (fila?.usuario_id) abrirFormularioEdicionUsuario(fila.usuario_id);
    });

    $('#usuarioTabla tbody').on('click', '.btn_ver_info', function () {
        const id = $(this).data('id');
        if (!id) return;
        fetch('php/usuario_ajax.php', {
            method: 'POST',
            body: new URLSearchParams({ accion: 'obtener_usuario', id })
        })
        .then(res => res.json())
        .then(data => {
            if (data.exito && data.usuario) mostrarInfoUsuario(data.usuario);
        });
    });

    $('#usuarioTabla tbody').on('click', '.btn_eliminar', function () {
        const id = $(this).data('id');
        if (!id) return;
        fetch('php/usuario_ajax.php', {
            method: 'POST',
            body: new URLSearchParams({ accion: 'obtener_usuario', id })
        })
        .then(res => res.json())
        .then(data => {
            if (data.exito && data.usuario) mostrarConfirmacionUsuario(data.usuario, 'eliminar');
        });
    });

    $('#usuarioTabla tbody').on('click', '.btn_recuperar', function () {
        const id = $(this).data('id');
        if (!id) return;
        fetch('php/usuario_ajax.php', {
            method: 'POST',
            body: new URLSearchParams({ accion: 'obtener_usuario', id })
        })
        .then(res => res.json())
        .then(data => {
            if (data.exito && data.usuario) mostrarConfirmacionUsuario(data.usuario, 'recuperar');
        });
    });
});


