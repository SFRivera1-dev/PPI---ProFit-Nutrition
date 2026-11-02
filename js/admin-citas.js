// =====================================================================
// admin/citas.php
// Peticiones a la API:
//   GET    /api/citas.php?q=       listar y buscar (cliente, correo, servicio, número)
//   PATCH  /api/citas.php?id=N     cambiar el estado   {"estado": "confirmada"}
//   DELETE /api/citas.php?id=N     cancelar la cita (solo pendiente o confirmada)
// Estados:  pendiente -> confirmada -> finalizada   (solo se avanza)
// (api.js, panel.js y citas-comun.js ya están cargados)
// =====================================================================
const caja        = document.getElementById('mensaje');
const cajaModal   = document.getElementById('mensajeModal');
const tbody       = document.getElementById('tbodyCitas');
const pie         = document.getElementById('citasPie');
const inputBuscar = document.getElementById('buscarCita');
const modal       = document.getElementById('modalCita');
const formEstado  = document.getElementById('formEstadoCita');
const selectEst   = document.getElementById('selectEstadoCita');
const notaEstado  = document.getElementById('notaEstadoCita');
const btnGuardar  = document.getElementById('btnGuardarEstadoCita');
const btnCancelar = document.getElementById('btnCancelarCita');

const ORDEN_ESTADOS = ['pendiente', 'confirmada', 'finalizada'];

let citas        = [];          // la lista que se está mostrando
let temporizador = null;        // espera un momento mientras se escribe en el buscador
let citaAbierta  = null;        // la cita abierta en la ventana (null = ninguna)
let confirmando  = false;       // true = ya se pulsó "Cancelar cita" una vez y falta confirmar

if (USUARIO) {
    cargarCitas();
}

/* ---------- LISTAR / BUSCAR ---------- */
async function cargarCitas() {
    try {
        let ruta = 'citas.php';
        if (inputBuscar.value.trim() !== '') {
            ruta = ruta + '?q=' + encodeURIComponent(inputBuscar.value.trim());
        }
        const datos = await api('GET', ruta);
        citas = datos.citas;
        dibujarTabla();
    } catch (error) {
        mostrarMensaje(caja, 'error', error.message);
    }
}

function dibujarTabla() {
    if (citas.length === 0) {
        tbody.innerHTML = '<tr><td colspan="6" class="vacio">No hay citas para mostrar.</td></tr>';
        pie.textContent = '';
        return;
    }

    tbody.innerHTML = citas.map(filaHtml).join('');
    if (citas.length === 1) {
        pie.textContent = '1 cita';
    } else {
        pie.textContent = citas.length + ' citas';
    }
}

// Cada cita es una fila. Todo dato pasa por escapar() (anti XSS).
function filaHtml(c) {
    return `
        <tr class="fila-pedido" data-id="${c.id}">
            <td class="pedido-id">#${c.id}</td>
            <td>
                <strong>${escapar(c.cliente.nombre + ' ' + c.cliente.apellido)}</strong><br>
                <span class="texto-suave">${escapar(c.cliente.correo)}</span>
            </td>
            <td>${escapar(c.servicio)}<br><span class="texto-suave">${escapar(c.objetivo)}</span></td>
            <td>${escapar(fechaCita(c.fecha) + ' · ' + horaCita(c.hora))}</td>
            <td><span class="badge ${claseEstadoCita(c.estado)}">${escapar(textoEstadoCita(c.estado))}</span></td>
            <td class="col-flecha">
                <button type="button" class="btn-icono" title="Ver y gestionar" aria-label="Ver y gestionar la cita ${c.id}"><i class="fa-solid fa-chevron-right"></i></button>
            </td>
        </tr>`;
}

inputBuscar.addEventListener('input', function () {
    clearTimeout(temporizador);
    temporizador = setTimeout(cargarCitas, 300);
});

/* ---------- VENTANA (modal) ---------- */
tbody.addEventListener('click', function (e) {
    const fila = e.target.closest('.fila-pedido');
    if (!fila) {
        return;
    }
    const cita = citas.find(function (c) { return c.id === Number(fila.dataset.id); });
    if (cita) {
        abrirModal(cita);
    }
});

function abrirModal(cita) {
    citaAbierta = cita;
    cajaModal.className = '';
    cajaModal.textContent = '';
    llenarModal(cita);
    modal.hidden = false;
    document.body.style.overflow = 'hidden';
    selectEst.focus();
}

function cerrarModal() {
    modal.hidden = true;
    citaAbierta = null;
    document.body.style.overflow = '';
}

function llenarModal(c) {
    document.getElementById('citaTitulo').textContent  = 'Cita #' + c.id;
    document.getElementById('citaCliente').textContent = c.cliente.nombre + ' ' + c.cliente.apellido;
    document.getElementById('citaCorreo').textContent  = c.cliente.correo;
    document.getElementById('citaCreada').textContent  = 'Solicitada el ' + fechaCita(String(c.creado_en).slice(0, 10));
    document.getElementById('citaFecha').textContent   = fechaCita(c.fecha) + ' · ' + horaCita(c.hora);
    document.getElementById('citaServicio').textContent = 'Servicio: ' + c.servicio;
    document.getElementById('citaObjetivo').textContent = c.objetivo;
    document.getElementById('citaTiempo').textContent   = 'Lleva: ' + c.tiempo;

    selectEst.value = c.estado;
    prepararControles(c.estado);
}

// Reglas (las mismas del servidor): solo se avanza; finalizada es final y no se cancela
function prepararControles(estadoActual) {
    const posicionActual = ORDEN_ESTADOS.indexOf(estadoActual);
    selectEst.querySelectorAll('option').forEach(function (opcion) {
        opcion.disabled = ORDEN_ESTADOS.indexOf(opcion.value) < posicionActual;
    });

    const finalizada = (estadoActual === 'finalizada');
    selectEst.disabled     = finalizada;
    btnGuardar.disabled    = finalizada;
    btnCancelar.hidden     = finalizada;

    confirmando = false;
    btnCancelar.textContent = 'Cancelar cita';

    if (finalizada) {
        notaEstado.textContent = 'Esta cita está finalizada: es parte del historial y ya no se puede modificar.';
    } else {
        notaEstado.textContent = 'Los estados solo avanzan, no se puede retroceder';
    }
}

document.getElementById('btnCerrarCita').addEventListener('click', cerrarModal);
modal.addEventListener('click', function (e) {
    if (e.target === modal) {
        cerrarModal();
    }
});
document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape' && !modal.hidden) {
        cerrarModal();
    }
});

/* ---------- GUARDAR EL NUEVO ESTADO ---------- */
formEstado.addEventListener('submit', async function (e) {
    e.preventDefault();

    if (citaAbierta === null) {
        return;
    }
    if (selectEst.value === citaAbierta.estado) {
        cerrarModal();                                       // no cambió nada
        return;
    }

    btnGuardar.disabled = true;
    btnGuardar.textContent = 'Guardando...';
    cajaModal.className = '';
    cajaModal.textContent = '';

    try {
        const datos = await api('PATCH', 'citas.php?id=' + citaAbierta.id, { estado: selectEst.value });
        const nueva = datos.cita;

        citas = citas.map(function (c) {
            if (c.id === nueva.id) {
                return nueva;
            }
            return c;
        });
        dibujarTabla();
        cerrarModal();
        mostrarMensaje(caja, 'ok', 'Cita #' + nueva.id + ' actualizada: ' + textoEstadoCita(nueva.estado).toLowerCase() + '.');
    } catch (error) {
        mostrarMensaje(cajaModal, 'error', error.message);
        if (error.status === 409) {
            await recargarCitaAbierta();
        }
    }

    btnGuardar.textContent = 'Guardar estado';
    if (citaAbierta !== null) {
        prepararControles(citaAbierta.estado);
    }
});

// Si alguien más cambió la cita, mostramos lo real
async function recargarCitaAbierta() {
    const id = citaAbierta.id;
    await cargarCitas();
    const actualizada = citas.find(function (c) { return c.id === id; });
    if (actualizada) {
        citaAbierta = actualizada;
        llenarModal(actualizada);
    } else {
        cerrarModal();                                       // ya no existe (la cancelaron)
    }
}

/* ---------- CANCELAR LA CITA (pide confirmar con un segundo clic) ---------- */
btnCancelar.addEventListener('click', async function () {
    if (citaAbierta === null) {
        return;
    }

    if (!confirmando) {
        confirmando = true;
        btnCancelar.textContent = '¿Seguro? Pulsa otra vez';
        notaEstado.textContent = 'La cita se borra, la hora queda libre y el cliente podrá agendar una nueva.';
        notaEstado.classList.add('pedido-estado__nota--alerta');
        return;
    }

    btnCancelar.disabled = true;
    try {
        const id = citaAbierta.id;
        await api('DELETE', 'citas.php?id=' + id);
        citas = citas.filter(function (c) { return c.id !== id; });
        dibujarTabla();
        cerrarModal();
        mostrarMensaje(caja, 'ok', 'Cita #' + id + ' cancelada. La hora quedó libre.');
    } catch (error) {
        mostrarMensaje(cajaModal, 'error', error.message);
        if (error.status === 409 || error.status === 404) {
            await recargarCitaAbierta();
        }
    }
    btnCancelar.disabled = false;
    notaEstado.classList.remove('pedido-estado__nota--alerta');
    if (citaAbierta !== null) {
        prepararControles(citaAbierta.estado);
    }
});
