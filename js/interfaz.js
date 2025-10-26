//Función global: lista de usuario
function cargarUsuarios() {
    fetch('php/usuario_ajax.php', {
        method: 'POST',
        body: new URLSearchParams({ accion: 'leer_todos' })
    })
    .then(res => res.json())
    .then(data => {
        console.log(data);

        const contenedor = document.querySelector('.grid.grid-usuario');
        if (!contenedor) {
            console.error('No se encontró el contenedor .grid.grid-usuario');
            return;
        }

        const filasAnteriores = contenedor.querySelectorAll('.row');
        filasAnteriores.forEach(fila => fila.remove());

        if (Array.isArray(data) && data.length > 0) {
            data.forEach(usuario => {
                const telefono = usuario.usuario_telefono || '';
                const foto = usuario.usuario_foto || 'img/icons/perfil.png';

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

//Mostrar contraseña al presionar el ojo
document.addEventListener("click", function (e) {
    if (e.target.classList.contains("eye-icon")) {
        const container = e.target.closest(".input_text");
        const input = container.querySelector(".input_password");

        if (!input) {
            console.error("No se encontró el input asociado al eye-icon");
            return;
        }

        const isHidden = input.type === "password";
        input.type = isHidden ? "text" : "password";
        e.target.classList.toggle("visible", isHidden);
    }
});

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


    // Función para mostrar/ocultar contraseña
    function togglePassword() {
        const passwordInput = document.getElementById("password");
        const eyeIcon = document.querySelector(".eye-icon");

        if (!passwordInput) {
            console.error("No se encontró el input de contraseña");
            return;
        }

        const isHidden = passwordInput.type === "password";
        passwordInput.type = isHidden ? "text" : "password";
        eyeIcon.classList.toggle("visible", isHidden);
    }

    // Delegación global para cualquier .eye-icon
    document.addEventListener("click", function (e) {
        if (e.target.classList.contains("eye-icon")) {
            togglePassword();
        }
    });

    const botones = document.querySelectorAll('[data-modal-target]');
    botones.forEach(boton => {
        boton.addEventListener('click', () => {
            const modalID = boton.getAttribute('data-modal-target');
            const modal = document.querySelector(`[data-modal="${modalID}"]`);
            if (modal) modal.showModal();
        });
    });

    const btncrear = document.getElementById('btn_crear');
    if (btncrear) btncrear.disabled = false;

        const vistaActual = new URL(window.location.href).searchParams.get('vista');

    if (vistaActual === 'listar_usuario') {
        cargarUsuarios();
    } else if (vistaActual === 'listar_bien') {
        cargarBienes();
    } else if (vistaActual === 'listar_marca') {
        cargar();
    }
    // Puede seguirse agregando más vistas aquí

});

document.addEventListener('DOMContentLoaded', function () {
    //Nuevo usuario
    const form = document.getElementById('form_nuevo_usuario');
    const errorContainer = document.getElementById('error-container');

    form.addEventListener('submit', function (e) {
        e.preventDefault();

            // Combinar tipo + número de cédula
        const tipo = document.getElementById('tipo_cedula').value;
        const numero = document.getElementById('numero_cedula').value.trim();
        const cedulaCompleta = tipo + numero;

        // Insertar manualmente en el formulario
        const campoCedula = document.createElement('input');
        campoCedula.type = 'hidden';
        campoCedula.name = 'usuario_cedula';
        campoCedula.value = cedulaCompleta;
        form.appendChild(campoCedula);

        const formData = new FormData(form);
        formData.append('rol_id', '1');
        formData.append('accion', 'crear');

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
                form.reset();
                errorContainer.innerHTML = '';
                errorContainer.style.display = 'none';

                const modal = document.querySelector('dialog[data-modal="new_user"]');
                if (modal && typeof modal.close === 'function') {
                    modal.close();
                }

                cargarUsuarios();
            }
        })
        .catch(() => {
            errorContainer.innerHTML = 'Error de conexión con el servidor.';
            errorContainer.style.display = 'block';
        });
    });
});
