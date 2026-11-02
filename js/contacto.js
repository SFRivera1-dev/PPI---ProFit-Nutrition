// =====================================================================
// pages/contacto.php
//   POST /api/contacto.php   {nombre, correo, asunto, mensaje, sitio_web}
// No hace falta iniciar sesión. Si la persona ya inició sesión, le rellenamos nombre y correo.
// (api.js ya lo cargó includes/header.php)
// =====================================================================
const formContacto = document.getElementById('formContacto');
const aviso        = document.getElementById('avisoContacto');
const btnEnviar    = document.getElementById('btnEnviarContacto');

prellenarDatos();

function prellenarDatos() {
    const usuario = Sesion.usuario();
    if (!Sesion.token() || !usuario) {
        return;
    }
    formContacto.nombre.value = (usuario.nombre + ' ' + usuario.apellido).trim();
    formContacto.correo.value = usuario.correo || '';
    mostrarBotonesLimpiar();
}

// search-delete.js muestra la "x" solo mientras se escribe; si ya hay texto, la mostramos
function mostrarBotonesLimpiar() {
    formContacto.querySelectorAll('.SearchInput').forEach(function (campo) {
        const boton = campo.parentElement.querySelector('.ButtonClear');
        if (boton && campo.value.length > 0) {
            boton.style.visibility = 'visible';
        }
    });
}

function mostrarAviso(tipo, texto) {
    aviso.className = 'aviso-contacto aviso-contacto--' + tipo;
    aviso.textContent = texto;
    aviso.hidden = false;
    aviso.scrollIntoView({ behavior: 'smooth', block: 'center' });
}

formContacto.addEventListener('submit', async function (evento) {
    evento.preventDefault();
    aviso.hidden = true;

    // Validación del navegador (campos vacíos, correo mal escrito...)
    if (!formContacto.checkValidity()) {
        formContacto.reportValidity();
        return;
    }

    btnEnviar.disabled = true;
    btnEnviar.textContent = 'Enviando...';

    try {
        const datos = await api('POST', 'contacto.php', {
            nombre:    formContacto.nombre.value.trim(),
            correo:    formContacto.correo.value.trim(),
            asunto:    formContacto.asunto.value.trim(),
            mensaje:   formContacto.mensaje.value.trim(),
            sitio_web: formContacto.sitio_web.value
        });

        // Dejamos el formulario limpio (menos nombre y correo si la persona tiene sesión)
        formContacto.asunto.value  = '';
        formContacto.mensaje.value = '';
        if (!Sesion.token()) {
            formContacto.nombre.value = '';
            formContacto.correo.value = '';
        }
        formContacto.querySelectorAll('.ButtonClear').forEach(function (boton) {
            boton.style.visibility = 'hidden';
        });
        mostrarBotonesLimpiar();

        mostrarAviso('ok', datos.mensaje);
    } catch (error) {
        mostrarAviso('error', error.message);
    }

    btnEnviar.disabled = false;
    btnEnviar.textContent = 'Enviar mensaje';
});
