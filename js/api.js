// =====================================================================
// CLIENTE DE LA API  (se carga en todas las páginas)
//
// Aquí vive todo lo que el navegador necesita para hablar con la API:
//   1. Guardar el token y el usuario (localStorage)
//   2. api():  enviar una petición con fetch y devolver el JSON
//   3. protegerPagina():  expulsar a quien no debe ver una página
//   4. Ayudantes de pantalla
// =====================================================================

// Debe ser igual que BASE_URL de includes/config.php
const BASE_URL = '/ProFit-Nutrition';
const API_URL  = BASE_URL + '/api';

/* ---------- 1. Guardar la sesión en el navegador ----------
   Con API REST no hay sesión en el servidor: el servidor te entrega un TOKEN
   al hacer login y tú lo guardas y lo envías en cada petición. */
const Sesion = {
    guardar(token, usuario) {
        localStorage.setItem('profit_token', token);
        localStorage.setItem('profit_usuario', JSON.stringify(usuario));
    },
    token() {
        return localStorage.getItem('profit_token');
    },
    usuario() {
        try { return JSON.parse(localStorage.getItem('profit_usuario')); }
        catch (e) { return null; }
    },
    borrar() {
        localStorage.removeItem('profit_token');
        localStorage.removeItem('profit_usuario');
    }
};

// A qué panel va cada rol (lo mismo que hacía urlPanel() en PHP)
function urlPanel(rol) {
    const paneles = {
        admin:       BASE_URL + '/admin/index.php',
        profesional: BASE_URL + '/profesional/index.php',
        cliente:     BASE_URL + '/cliente/index.php'
    };
    return paneles[rol] || BASE_URL + '/index.php';
}

/* ---------- 2. Hablar con la API ----------
   Uso:   const datos = await api('GET', 'usuarios.php');
          const datos = await api('POST', 'login.php', { correo: '...', password: '...' });
          Para subir un archivo: api('POST', 'x.php', formData)   (formData = new FormData())
   - Si la API responde 2xx, devuelve el JSON.
   - Si responde un error (400, 401, 403...), lanza un Error con el mensaje de la API. */
async function api(metodo, ruta, cuerpo) {
    const opciones = { method: metodo, headers: { 'Accept': 'application/json' } };

    const token = Sesion.token();
    if (token) {
        opciones.headers['Authorization'] = 'Bearer ' + token;   // así nos identificamos
    }
    if (cuerpo !== undefined) {
        if (cuerpo instanceof FormData) {
            // Un archivo viaja como formulario: NO ponemos Content-Type, el navegador lo pone solo
            opciones.body = cuerpo;
        } else {
            opciones.headers['Content-Type'] = 'application/json';
            opciones.body = JSON.stringify(cuerpo);
        }
    }

    let respuesta;
    try {
        respuesta = await fetch(API_URL + '/' + ruta, opciones);
    } catch (e) {
        throw new Error('No se pudo conectar con el servidor.');
    }

    let datos = null;
    try { datos = await respuesta.json(); } catch (e) { /* respuesta sin JSON */ }

    if (!respuesta.ok) {
        // 401 con un token enviado = token vencido o cuenta desactivada: cerramos la sesión
        if (respuesta.status === 401 && token) {
            Sesion.borrar();
            location.href = BASE_URL + '/ingresar.php';
        }
        const error = new Error((datos && datos.error) || 'Error ' + respuesta.status);
        error.status = respuesta.status;          // así quien llama puede distinguir 409, 422, etc.
        throw error;
    }
    return datos;
}

/* ---------- 3. Proteger páginas ----------
   Esto es SOLO comodidad (no mostrar un panel que no te corresponde).
   La seguridad real está en la API: cada endpoint verifica el token y el rol. */
function protegerPagina(rol) {
    const usuario = Sesion.usuario();
    if (!Sesion.token() || !usuario) {
        location.replace(BASE_URL + '/ingresar.php');
        return null;
    }
    if (usuario.rol !== rol) {
        location.replace(urlPanel(usuario.rol));
        return null;
    }
    return usuario;
}

function cerrarSesion() {
    // Con tokens no hay nada que "destruir" en el servidor: basta con olvidar el token.
    Sesion.borrar();
    location.href = BASE_URL + '/ingresar.php';
}

/* ---------- 4. Ayudantes de pantalla ---------- */

// Muestra un mensaje en una caja:  mostrarMensaje(caja, 'error', 'Texto')
function mostrarMensaje(caja, tipo, texto) {
    if (tipo === 'ok') {
        caja.className = 'alerta alerta-ok';
    } else {
        caja.className = 'alerta alerta-error';
    }
    caja.textContent = texto;
}

// Convierte "<script>" en texto inofensivo. Úsalo con TODO dato de usuario que metas
// dentro de innerHTML o de una plantilla `...`. (Es el h() que teníamos en PHP.)
function escapar(texto) {
    return String(texto)
        .replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;').replace(/'/g, '&#39;');
}
