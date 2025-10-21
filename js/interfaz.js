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
});

//Función para mostrar contraseña
function togglePassword() {
    const input = document.getElementById("password");
    input.type = input.type === "password" ? "text" : "password";
}

//Generar ventanas modales
const botones = document.querySelectorAll('[data-modal-target]');
// Recorremos cada botón
botones.forEach(boton => {
    boton.addEventListener('click', () => {
        const modalID = boton.getAttribute('data-modal-target');
        const modal = document.querySelector(`[data-modal="${modalID}"]`);
        if (modal) {
        modal.showModal();
        }
    });
});

const modales = document.querySelectorAll('.modal');

modales.forEach(modal => {
  const cerrar = modal.querySelector('.modal__close');

  cerrar.addEventListener('click', (e) => {
    e.preventDefault();

    // Añade clase para activar animación de salida
    modal.classList.add('closing');

    // Espera a que termine la animación antes de cerrar
    setTimeout(() => {
      modal.close();           // Cierra el modal
      modal.classList.remove('closing'); // Limpia la clase
    }, 300); // Duración debe coincidir con la animación CSS
  });
});