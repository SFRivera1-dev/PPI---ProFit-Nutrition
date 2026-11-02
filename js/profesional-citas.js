// =====================================================================
// profesional/citas.php
//   GET   /api/citas.php          -> todas las citas
//   PATCH /api/citas.php?id=N     -> cambia el estado   {"estado": "confirmada"}
// Estados:  pendiente -> confirmada -> finalizada   (solo se avanza)
// (api.js, panel.js y citas-comun.js ya están cargados)
// =====================================================================
const caja     = document.getElementById('mensaje');
const citasApp = document.getElementById('citasApp');
const cuerpo   = document.getElementById('citasBody');
const vacio    = document.getElementById('citasVacio');
const tabs     = citasApp.querySelectorAll('.tab');

let citas         = [];             // todas las citas que llegaron de la API
let pestanaActiva = 'pendiente';

// Qué botón sale según el estado actual: [texto del botón, estado al que pasa]
const ACCIONES = {
    pendiente:  { texto: 'Confirmar', nuevo: 'confirmada' },
    confirmada: { texto: 'Finalizar', nuevo: 'finalizada' }
};

if (USUARIO) {
    cargarCitas();
}

async function cargarCitas() {
    try {
        const datos = await api('GET', 'citas.php');
        citas = datos.citas;
        dibujar();
    } catch (error) {
        mostrarMensaje(caja, 'error', error.message);
    }
}

// Dibuja las filas de la pestaña activa y actualiza los contadores
function dibujar() {
    const conteo = { pendiente: 0, confirmada: 0, finalizada: 0 };
    citas.forEach(function (c) {
        conteo[c.estado] = conteo[c.estado] + 1;
    });
    Object.keys(conteo).forEach(function (estado) {
        citasApp.querySelector('[data-count="' + estado + '"]').textContent = conteo[estado];
    });

    const visibles = citas.filter(function (c) { return c.estado === pestanaActiva; });

    let html = '';
    visibles.forEach(function (c) {
        html = html + filaHtml(c);
    });
    cuerpo.innerHTML = html;
    vacio.hidden = visibles.length > 0;
}

// Cada cita es una fila. Todo dato pasa por escapar() (anti XSS).
function filaHtml(c) {
    let accion = '—';
    if (ACCIONES[c.estado]) {
        accion = '<button type="button" class="btn-mini" data-id="' + c.id + '" data-nuevo="' + ACCIONES[c.estado].nuevo + '">' +
                 escapar(ACCIONES[c.estado].texto) + '</button>';
    }

    return `
        <tr>
            <td>
                <strong>${escapar(c.cliente.nombre + ' ' + c.cliente.apellido)}</strong><br>
                <span class="texto-suave">${escapar(c.cliente.correo)}</span>
            </td>
            <td>${escapar(motivoCita(c))}<br><span class="texto-suave">Lleva: ${escapar(c.tiempo)}</span></td>
            <td>${escapar(fechaCita(c.fecha) + ' · ' + horaCita(c.hora))}</td>
            <td><span class="badge ${claseEstadoCita(c.estado)}">${escapar(textoEstadoCita(c.estado))}</span></td>
            <td class="der acciones-cita">${accion}</td>
        </tr>`;
}

// Botón Confirmar / Finalizar
cuerpo.addEventListener('click', async function (e) {
    const boton = e.target.closest('button[data-id]');
    if (!boton) {
        return;
    }

    boton.disabled = true;
    try {
        const datos = await api('PATCH', 'citas.php?id=' + boton.dataset.id, { estado: boton.dataset.nuevo });
        const nueva = datos.cita;

        citas = citas.map(function (c) {
            if (c.id === nueva.id) {
                return nueva;
            }
            return c;
        });
        dibujar();
        mostrarMensaje(caja, 'ok', 'Cita de ' + nueva.cliente.nombre + ' ' + nueva.cliente.apellido + ': ' + textoEstadoCita(nueva.estado).toLowerCase() + '.');
    } catch (error) {
        mostrarMensaje(caja, 'error', error.message);
        if (error.status === 409) {
            await cargarCitas();                       // otra persona la cambió: mostramos lo real
        }
        boton.disabled = false;
    }
});

tabs.forEach(function (tab) {
    tab.addEventListener('click', function () {
        tabs.forEach(function (t) { t.classList.remove('active'); });
        tab.classList.add('active');
        pestanaActiva = tab.dataset.estado;
        dibujar();
    });
});
