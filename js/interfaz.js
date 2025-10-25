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
    const btncrear = document.getElementById('btn_crear');
    if (btncrear) btncrear.disabled = false;

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

        const filasAnteriores = contenedor.querySelectorAll('.row');
        filasAnteriores.forEach(fila => fila.remove());

        if (Array.isArray(data) && data.length > 0) {
            data.forEach(usuario => {
            const telefono = usuario.usuario_telefono ? usuario.usuario_telefono : '';
            const foto = usuario.usuario_foto ? usuario.usuario_foto : 'img/icons/perfil.png'; // Asegúrate de que la ruta sea correcta

            contenedor.innerHTML += `
                <div class="row">${usuario.usuario_id}</div>
                <div class="row">${usuario.usuario_nombre}</div>
                <div class="row">${usuario.usuario_apellido}</div>
                <div class="row">${usuario.usuario_cedula}</div>
                <div class="row">${usuario.usuario_correo}</div>
                <div class="row">${telefono}</div>
                <div class="row"><img src="${foto}" alt="Foto" width="40"></div>
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

    //Llamada a la función para que se ejecute
    cargarUsuarios();
});

//crear usuario
document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('form_nuevo_usuario');
    const errorContainer = document.getElementById('error-container');

    form.addEventListener('submit', function (e) {
        e.preventDefault(); // Evita el envío tradicional del formulario
        
        const formData = new FormData(form);
        formData.append('rol_id', '1'); // Rol por defecto
        formData.append('accion', 'crear'); // Asegura que se envíe la acción esperada por PHP

        fetch('php/usuario_ajax.php', {
            method: 'POST',
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            errorContainer.innerHTML = '';
            errorContainer.style.display = 'none';

            if (data.error) {
                let mensaje = `<p>${data.mensaje}</p>`;
                if (data.campos && Array.isArray(data.campos)) {
                    mensaje += '<ul>';
                    data.campos.forEach(campo => {
                        mensaje += `<li>${campo.replace('usuario_', '').replace('_', ' ')} está vacío</li>`;
                    });
                    mensaje += '</ul>';
                }
                errorContainer.innerHTML = mensaje;
                errorContainer.style.display = 'block';
            } else if (data.exito) {
                alert(data.mensaje);
                form.reset(); // Limpia el formulario
                errorContainer.innerHTML = '';
                errorContainer.style.display = 'none';

                // Cierra el modal si lo deseas
                const modal = document.querySelector('dialog[data-modal="new_user"]');
                if (modal && typeof modal.close === 'function') {
                    modal.close();
                }
                    cargarUsuarios(); // Recarga la lista sin refrescar la página
            }
        })
        .catch(() => {
            errorContainer.innerHTML = 'Error de conexión con el servidor.';
            errorContainer.style.display = 'block';
        });
    });
});
