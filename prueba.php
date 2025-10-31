<?php
echo password_hash("admin123", PASSWORD_DEFAULT);
?>



    

    <form id="buscador" method="POST" autocomplete="off" class="buscador">
        <input type="text" maxlength="10" name="buscador" class="input_buscar" placeholder="buscar...">
        <button type="submit" class="buscar">
            <img src="img/icons/buscar.png" alt="Buscar">
        </button>
    </form>
</div>






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

        const usuarioRol = document.getElementById('usuario')?.dataset.id;

        // Limpiar filas anteriores
        const filasAnteriores = contenedor.querySelectorAll('.row');
        filasAnteriores.forEach(fila => fila.remove());

        if (Array.isArray(data) && data.length > 0) {
            data.forEach(usuario => {
                const telefono = usuario.usuario_telefono || '';
                const foto = usuario.usuario_foto || 'img/icons/perfil.png';

                const campos = [
                    usuario.usuario_id,
                    usuario.usuario_nombre,
                    usuario.usuario_apellido,
                    usuario.usuario_cedula,
                    usuario.usuario_correo,
                    telefono
                ];

                campos.forEach(valor => {
                    const celda = document.createElement('div');
                    celda.classList.add('row');
                    celda.textContent = valor;
                    contenedor.appendChild(celda);
                });

                // Celda de la foto
                const celdaFoto = document.createElement('div');
                celdaFoto.classList.add('row');
                const img = document.createElement('img');
                img.src = foto;
                img.alt = 'Foto';
                img.width = 40;
                celdaFoto.appendChild(img);
                contenedor.appendChild(celdaFoto);

                // Celda de acciones
                const celdaAcciones = document.createElement('div');
                celdaAcciones.classList.add('row');

                if (usuarioRol === "2") {
                    // Botón: Actualizar
                    const btnActualizar = document.createElement('div');
                    btnActualizar.classList.add('icon-action');
                    btnActualizar.setAttribute('data-modal-target', 'new_user');
                    btnActualizar.setAttribute('title', 'Actualizar');
                    const imgActualizar = document.createElement('img');
                    imgActualizar.src = 'img/icons/actualizar.png';
                    imgActualizar.alt = 'Actualizar';
                    btnActualizar.appendChild(imgActualizar);

                    // Botón: Info
                    const btnInfo = document.createElement('div');
                    btnInfo.classList.add('icon-action');
                    btnInfo.setAttribute('data-modal-target', 'info_usuario');
                    btnInfo.setAttribute('title', 'Info');
                    const imgInfo = document.createElement('img');
                    imgInfo.src = 'img/icons/info.png';
                    imgInfo.alt = 'Info';
                    btnInfo.appendChild(imgInfo);

                    // Botón: Eliminar
                    const btnEliminar = document.createElement('div');
                    btnEliminar.classList.add('icon-action');
                    btnEliminar.setAttribute('data-modal-target', 'eliminar_usuario');
                    btnEliminar.setAttribute('title', 'Eliminar');
                    const imgEliminar = document.createElement('img');
                    imgEliminar.src = 'img/icons/eliminar.png';
                    imgEliminar.alt = 'Eliminar';
                    btnEliminar.appendChild(imgEliminar);

                    celdaAcciones.appendChild(btnActualizar);
                    celdaAcciones.appendChild(btnInfo);
                    celdaAcciones.appendChild(btnEliminar);
                } else {
                    const span = document.createElement('span');
                    span.classList.add('text-empty');
                    span.textContent = 'Ninguno';
                    celdaAcciones.appendChild(span);
                }

                contenedor.appendChild(celdaAcciones);
            });
        } else {
            const vacio = document.createElement('div');
            vacio.classList.add('text-empty');
            vacio.textContent = 'No hay ningún registro';
            contenedor.appendChild(vacio);
        }
    })
    .catch(err => {
        console.error('Error AJAX:', err);
    });
}

cargarUsuarios();
