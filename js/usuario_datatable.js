    window.addEventListener('load', function () {
    if (typeof $ !== 'function') {
        console.error('jQuery no está disponible');
        return;
    }

    const usuarioRol = document.getElementById('usuario')?.dataset.id;

    const tabla = $('#usuarioTabla').DataTable({
        scrollY: '500px',
        scrollCollapse: true,
        responsive: true,
        ajax: {
        url: 'php/usuario_ajax.php',
        type: 'POST',
        data: { accion: 'leer_todos' },
        dataSrc: 'data',
        error: function (xhr, status, error) {
            console.error('Error AJAX:', error);
            console.log('Respuesta del servidor:', xhr.responseText);
        },
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
            if (usuarioRol === "2") {
                return `
                <div class="acciones">
                    <div class="icon-action" data-modal-target="new_user" title="Actualizar">
                        <img src="img/icons/actualizar.png" alt="Actualizar">
                    </div>
                    <div class="icon-action btn_ver_info" data-modal-target="info_usuario" data-id="${row.usuario_id}" title="Info">
                        <img src="img/icons/info.png" alt="Info">
                    </div>
                    <div class="icon-action" data-modal-target="eliminar_usuario" title="Eliminar">
                        <img src="img/icons/eliminar.png" alt="Eliminar">
                    </div>
                </div>
                `;
            } else {
                return `
                <div class="acciones">
                    <div class="icon-action btn_ver_info" data-modal-target="info_usuario" data-id="${row.usuario_id}" title="Info">
                        <img src="img/icons/info.png" alt="Info">
                    </div>
                </div>
                `;
            }
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
        info: "Mostrando _START_ a _END_ de _TOTAL_ registros",
        infoEmpty: "Mostrando 0 a 0 de 0 registros",
        infoFiltered: "(filtrado de _MAX_ registros totales)",
        paginate: {
            previous: "◀",
            next: "▶"
        }
        }
    ,
    lengthMenu: [ [5, 10, 15, 20, 30], [5, 10, 15, 20, 30] ],
    });
    // Activar botón "Actualizar" en cada fila
    $('#usuarioTabla tbody').on('click', '.icon-action[title="Actualizar"]', function () {
        const fila = tabla.row($(this).closest('tr')).data();
        if (fila && fila.usuario_id) {
            abrirFormularioEdicion(fila.usuario_id);
        }
    });
    // Activar botón "Info" en cada fila
    $('#usuarioTabla tbody').on('click', '.btn_ver_info', function () {
        const id = $(this).data('id');
        if (!id) return;

        fetch('php/usuario_ajax.php', {
            method: 'POST',
            body: new URLSearchParams({ accion: 'obtener_usuario', id: id })
        })
        .then(res => res.json())
        .then(data => {
            console.log('Datos recibidos:', data); // ← para depurar
            if (data.exito && data.usuario) {
                mostrarInfoUsuario(data.usuario);
            }
        });
    });

    $('#usuarioTabla tbody').on('click', '.icon-action[title="Eliminar"]', function () {
    const fila = tabla.row($(this).closest('tr')).data();
    if (fila && fila.usuario_id) {
        fetch('php/usuario_ajax.php', {
        method: 'POST',
        body: new URLSearchParams({ accion: 'obtener_usuario', id: fila.usuario_id })
        })
        .then(res => res.json())
        .then(data => {
        if (data.exito && data.usuario) {
            mostrarEliminarUsuario(data.usuario);
        }
        });
    }
    });


    });
