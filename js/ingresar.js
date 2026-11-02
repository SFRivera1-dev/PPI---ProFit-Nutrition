// =====================================================================
// PÁGINA ingresar.php
// Recorrido:  submit del formulario -> fetch POST /api/login.php -> guardar token -> ir al panel
// =====================================================================

// Si ya hay sesión, no mostramos el login: vamos directo a su panel.
if (Sesion.token() && Sesion.usuario()) {
    location.replace(urlPanel(Sesion.usuario().rol));
}

const form  = document.getElementById('formLogin');
const caja  = document.getElementById('mensaje');
const boton = form.querySelector('button[type="submit"]');

form.addEventListener('submit', async function (evento) {
    evento.preventDefault();          // 1. que el navegador NO recargue la página
    boton.disabled = true;            //    (evita doble clic)

    try {
        // 2. Enviamos los datos a la API. form.correo y form.password son los inputs por su name="".
        const respuesta = await api('POST', 'login.php', {
            correo:   form.correo.value,
            password: form.password.value
        });

        // 3. La API respondió {token, usuario}: los guardamos en el navegador
        Sesion.guardar(respuesta.token, respuesta.usuario);

        // 4. Vamos al panel que corresponde al rol
        location.href = urlPanel(respuesta.usuario.rol);

    } catch (error) {
        // La API respondió un error (401, 403, 422...) o no hubo conexión
        mostrarMensaje(caja, 'error', error.message);
        boton.disabled = false;
    }
});
