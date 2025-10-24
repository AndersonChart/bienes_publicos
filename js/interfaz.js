document.addEventListener('DOMContentLoaded', () => {
    const icons = document.querySelectorAll('.icon');
    const menus = document.querySelectorAll('.menu-content');

    icons.forEach(icon => {
        icon.addEventListener('click', () => {
            const target = icon.getAttribute('data-menu');
            icons.forEach(i => i.classList.remove('active'));
            menus.forEach(menu => menu.classList.remove('active'));
            icon.classList.add('active');
            const targetMenu = document.getElementById(target);
            if (targetMenu) {
                targetMenu.classList.add('active');
            }
        });
    });

    // Mostrar contraseña
    function togglePassword() {
        const input = document.getElementById("password");
        input.type = input.type === "password" ? "text" : "password";
    }

    // Ventanas modales
    const botones = document.querySelectorAll('[data-modal-target]');
    botones.forEach(boton => {
        boton.addEventListener('click', () => {
            const modalID = boton.getAttribute('data-modal-target');
            const modal = document.querySelector(`[data-modal="${modalID}"]`);
            if (modal) modal.showModal();
        });
    });

    // Evitar doble envío
    const btnGuardar = document.getElementById('btn_guardar');
    if (btnGuardar) btnGuardar.disabled = true;

    // Cargar usuarios
    function cargarUsuarios() {
    fetch('php/usuario_ajax.php', {
        method: 'POST',
        body: new URLSearchParams({ accion: 'leer_todos' })
    })
    .then(res => res.json())
    .then(data => {
        console.log(data); // Verifica si es un array

        const contenedor = document.querySelector('.grid.grid-usuario');
        if (!contenedor) {
            console.error('No se encontró el contenedor .grid.grid-usuario');
            return;
        }

        // 🔥 Elimina solo las filas anteriores, sin borrar encabezados ni título
        const filasAnteriores = contenedor.querySelectorAll('.row');
        filasAnteriores.forEach(fila => fila.remove());

        if (Array.isArray(data) && data.length > 0) {
            data.forEach(usuario => {
                contenedor.innerHTML += `
                    <div class="row">${usuario.usuario_id}</div>
                    <div class="row">${usuario.usuario_nombre}</div>
                    <div class="row">${usuario.usuario_apellido}</div>
                    <div class="row">${usuario.usuario_cedula}</div>
                    <div class="row">${usuario.usuario_correo}</div>
                    <div class="row">${usuario.usuario_telefono}</div>
                    <div class="row"><img src="${usuario.usuario_foto}" alt="Foto" width="40"></div>
                    <div class="row">
                        <div class="icon-action actualizar" data-url="form_actualizar_usuario.php" data-id="${usuario.usuario_id}" data-action="actualizar" title="Actualizar">
                            <img src="img/icons/actualizar.png" alt="Actualizar">
                        </div>
                        <div class="icon-action info" data-url="info_usuario.php" data-id="${usuario.usuario_id}" data-action="info" title="Info">
                            <img src="img/icons/info.png" alt="Info">
                        </div>
                        <div class="icon-action eliminar" data-id="${usuario.usuario_id}" data-action="eliminar" title="Eliminar">
                            <img src="img/icons/eliminar.png" alt="Eliminar">
                        </div>
                    </div>
                `;
            });
        } else {
            contenedor.innerHTML += `<div class="text-empty">No hay ningún registro</div>`;
        }
    })
    .catch(err => {
        console.error('Error AJAX:', err);
    });
}


    // ✅ Llamada a la función para que se ejecute
    cargarUsuarios();
});

