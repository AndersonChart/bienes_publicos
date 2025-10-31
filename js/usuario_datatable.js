    window.addEventListener('load', function () {
    if (typeof $ !== 'function') {
        console.error('jQuery no está disponible');
        return;
    }

    const usuarioRol = document.getElementById('usuario')?.dataset.id;

    const tabla = $('#miTabla').DataTable({
        scrollY: '400px',
  scrollCollapse: true,
  scrollX: true,
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
            render: function (_, __, ___) {
            if (usuarioRol === "2") {
                return `
                <div class="acciones">
                    <div class="icon-action" data-modal-target="new_user" title="Actualizar">
                    <img src="img/icons/actualizar.png" alt="Actualizar">
                    </div>
                    <div class="icon-action" data-modal-target="info_usuario" title="Info">
                    <img src="img/icons/info.png" alt="Info">
                    </div>
                    <div class="icon-action" data-modal-target="eliminar_usuario" title="Eliminar">
                    <img src="img/icons/eliminar.png" alt="Eliminar">
                    </div>
                </div>
                `;
            } else {
                return `<span class="text-empty">Ninguno</span>`;
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
        paginate: {
            previous: "◀",
            next: "▶",
        },
        info: "Mostrando _START_ a _END_ de _TOTAL_ registros",
    },
    lengthMenu: [ [5, 10, 15, 20, 30], [5, 10, 15, 20, 30] ],
    });

    });
