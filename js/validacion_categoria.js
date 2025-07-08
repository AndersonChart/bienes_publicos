document.addEventListener('DOMContentLoaded', () => {
    const formulario = document.querySelector('.form');
    formulario.addEventListener('submit', function(e) {
        let contenedor = this.querySelector('.form-resultado');
        contenedor.innerHTML = "";

        // --- CAMPOS OBLIGATORIOS ---
        let nombre = this.nombre.value.trim();
        if (nombre === "") {    
            e.preventDefault();
            contenedor.innerHTML = `<div class="alert alert-danger">El nombre de la categoría es obligatorio.</div>`;
            return;
        }else{
            //validación de nombre
            if (nombre.length < 3) {
                e.preventDefault();
                contenedor.innerHTML = `<div class="alert alert-danger">El nombre de la categoría debe tener al menos 3 caracteres.</div>`;
                return;
            }
            if (nombre.length > 100) {
                e.preventDefault();
                contenedor.innerHTML = `<div class="alert alert-danger">El nombre de la categoría debe tener menos de 100 caracteres.</div>`;
                return;
            }
            if (!/^[a-zA-ZáéíóúÁÉÍÓÚ0-9\s]+$/.test(nombre)) {
                e.preventDefault();
                contenedor.innerHTML = `<div class="alert alert-danger">El nombre de la categoría solo puede tener letras, números y espacios.</div>`;
                return;
            }
        }

        // --- CAMPOS NO OBLIGATORIOS ---
        let descripcion = this.descripcion.value.trim();
        if (descripcion !== "") { // Solo validar si tiene algo
            if (!/^[a-zA-ZáéíóúÁÉÍÓÚñÑüÜ0-9\s.,:;¡!¿?\-()"'“”]+$/.test(descripcion)) {
                e.preventDefault();
                contenedor.innerHTML = `<div class="alert alert-danger">La descripcion de la categoría solo puede tener letras, números, algunos carácteres especiales y espacios.</div>`;
                return;
            }
            if (descripcion.length < 8) {
                e.preventDefault();
                contenedor.innerHTML = `<div class="alert alert-danger">La descripción de la categoría debe tener al menos 8 caracteres.</div>`;
                return;
            }
            if (descripcion.length > 100) {
                e.preventDefault();
                contenedor.innerHTML = `<div class="alert alert-danger">La descripción de la categoría debe tener menos de 100 caracteres.</div>`;
                return;
            }
        }
        // Si llegaste aquí, todo está bien y el formulario se puede enviar
    });
});