// =====================================================================
// cliente/index.php  ("Tu cuenta")      ->   GET /api/me.php  y  GET /api/citas.php
// (api.js y header-sesion.js ya los cargó includes/header.php)
// =====================================================================
const usuarioCliente = protegerPagina('cliente');

if (usuarioCliente) {
    // Mostramos de inmediato lo que guardó el login...
    pintarUsuario(usuarioCliente);

    // ...y confirmamos con la API que el token sigue siendo válido (y traemos datos frescos)
    api('GET', 'me.php')
        .then(datos => pintarUsuario(datos.usuario))
        .catch(error => mostrarMensaje(document.getElementById('mensaje'), 'error', error.message));

    cargarCitas();

    document.getElementById('btnSalir').addEventListener('click', function (e) {
        e.preventDefault();
        cerrarSesion();
    });
}

function pintarUsuario(u) {
    document.getElementById('cuentaNombre').textContent = u.nombre + ' ' + u.apellido;
    document.getElementById('cuentaCorreo').textContent = u.correo;
}

// ---------------------------------------------------------------------
// Mis citas  ->  GET /api/citas.php  (un cliente solo recibe las suyas)
// ---------------------------------------------------------------------
async function cargarCitas() {
    const cargando = document.getElementById('citasCargando');
    const vacio    = document.getElementById('citasVacio');
    const lista    = document.getElementById('listaCitas');
    const agendar  = document.getElementById('enlaceAgendar');

    try {
        const datos = await api('GET', 'citas.php');
        cargando.hidden = true;

        if (datos.citas.length === 0) {
            vacio.hidden = false;
            return;
        }

        let html = '';
        datos.citas.forEach(function (cita) {
            html = html + `
                <li class="cita">
                    <div class="cita__fecha">
                        <strong>${escapar(fechaCita(cita.fecha))}</strong>
                        <span>${escapar(horaCita(cita.hora))}</span>
                    </div>
                    <div class="cita__info">
                        <strong>${escapar(cita.servicio)}</strong>
                        <span>${escapar(cita.objetivo)}</span>
                    </div>
                    <span class="cita__estado cita__estado--${escapar(cita.estado)}">${escapar(textoEstadoCita(cita.estado))}</span>
                </li>`;
        });
        lista.innerHTML = html;
        lista.hidden = false;

        // Solo puede agendar otra si todas sus citas están finalizadas
        const hayActiva = datos.citas.some(function (cita) {
            return cita.estado !== 'finalizada';
        });
        agendar.hidden = hayActiva;

    } catch (error) {
        cargando.hidden = true;
        mostrarMensaje(document.getElementById('mensaje'), 'error', error.message);
    }
}
