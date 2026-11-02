// =====================================================================
// PÁGINA registrar.php
// Recorrido:  submit -> fetch POST /api/registro.php -> guardar token -> ir al panel del cliente
// =====================================================================

if (Sesion.token() && Sesion.usuario()) {
    location.replace(urlPanel(Sesion.usuario().rol));
}

const form  = document.getElementById('formRegistro');
const caja  = document.getElementById('mensaje');
const boton = form.querySelector('button[type="submit"]');

form.addEventListener('submit', async function (evento) {
    evento.preventDefault();

    // "confirmar" es solo para que el usuario no se equivoque: no se envía a la API
    if (form.password.value !== form.confirmar.value) {
        mostrarMensaje(caja, 'error', 'Las contraseñas no coinciden.');
        return;
    }

    boton.disabled = true;
    try {
        const respuesta = await api('POST', 'registro.php', {
            nombre:   form.nombre.value,
            apellido: form.apellido.value,
            correo:   form.correo.value,
            password: form.password.value
        });

        Sesion.guardar(respuesta.token, respuesta.usuario);   // registrarse = quedar con sesión iniciada
        location.href = urlPanel(respuesta.usuario.rol);

    } catch (error) {
        mostrarMensaje(caja, 'error', error.message);
        boton.disabled = false;
    }
});
