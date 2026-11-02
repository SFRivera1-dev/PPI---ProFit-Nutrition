// =====================================================================
// profesional/pacientes.php
// Peticiones a la API:
//   GET /api/pacientes.php?q=     los clientes que han tenido alguna cita (buscar por nombre o correo)
//   GET /api/pacientes.php?id=N   un paciente con todo su historial de citas
// (api.js, panel.js y citas-comun.js ya están cargados)
// =====================================================================
const caja        = document.getElementById('mensaje');
const cajaModal   = document.getElementById('mensajeModal');
const tbody       = document.getElementById('tbodyPacientes');
const pie         = document.getElementById('pacientesPie');
const inputBuscar = document.getElementById('buscarPaciente');
const modal       = document.getElementById('modalPaciente');

let pacientes    = [];
let temporizador = null;
let peticion     = 0;           // para ignorar respuestas viejas si el profesional hace clic rápido

if (USUARIO) {
    cargarPacientes();
}

/* ---------- LISTAR / BUSCAR ---------- */
async function cargarPacientes() {
    try {
        let ruta = 'pacientes.php';
        if (inputBuscar.value.trim() !== '') {
            ruta = ruta + '?q=' + encodeURIComponent(inputBuscar.value.trim());
        }
        const datos = await api('GET', ruta);
        pacientes = datos.pacientes;
        dibujarTabla();
    } catch (error) {
        mostrarMensaje(caja, 'error', error.message);
    }
}

function dibujarTabla() {
    if (pacientes.length === 0) {
        tbody.innerHTML = '<tr><td colspan="5" class="vacio">No hay pacientes para mostrar.</td></tr>';
        pie.textContent = '';
        return;
    }
    tbody.innerHTML = pacientes.map(filaHtml).join('');
    if (pacientes.length === 1) {
        pie.textContent = '1 paciente';
    } else {
        pie.textContent = pacientes.length + ' pacientes';
    }
}

// Cada paciente es una fila. Todo dato pasa por escapar() (anti XSS).
function filaHtml(p) {
    let situacion = '<span class="badge ok">Al día</span>';
    if (p.en_curso > 0) {
        situacion = '<span class="badge warn">Cita en curso</span>';
    }

    return `
        <tr class="fila-pedido" data-id="${p.id}">
            <td>
                <strong>${escapar(p.nombre + ' ' + p.apellido)}</strong><br>
                <span class="texto-suave">${escapar(p.correo)}</span>
            </td>
            <td>${p.total_citas}</td>
            <td>${escapar(fechaCita(p.ultima_fecha))}</td>
            <td>${situacion}</td>
            <td class="col-flecha">
                <button type="button" class="btn-icono" title="Ver historial" aria-label="Ver el historial de ${escapar(p.nombre + ' ' + p.apellido)}"><i class="fa-solid fa-chevron-right"></i></button>
            </td>
        </tr>`;
}

inputBuscar.addEventListener('input', function () {
    clearTimeout(temporizador);
    temporizador = setTimeout(cargarPacientes, 300);
});

/* ---------- VENTANA CON EL HISTORIAL ---------- */
tbody.addEventListener('click', function (e) {
    const fila = e.target.closest('.fila-pedido');
    if (fila) {
        abrirHistorial(Number(fila.dataset.id));
    }
});

async function abrirHistorial(id) {
    peticion = peticion + 1;
    const miPeticion = peticion;

    cajaModal.className = '';
    cajaModal.textContent = '';
    document.getElementById('pacienteTitulo').textContent = 'Ficha del paciente';
    document.getElementById('pacNombre').textContent = '';
    document.getElementById('pacCorreo').textContent = '';
    document.getElementById('pacResumen').textContent = '';
    document.getElementById('pacHistorial').innerHTML = '<li class="historial-citas__vacio">Cargando historial...</li>';
    modal.hidden = false;
    document.body.style.overflow = 'hidden';

    try {
        const datos = await api('GET', 'pacientes.php?id=' + id);
        if (miPeticion !== peticion || modal.hidden) {
            return;                                          // ya abrieron otro paciente o cerraron la ventana
        }
        llenarModal(datos.paciente, datos.citas);
    } catch (error) {
        if (miPeticion === peticion) {
            document.getElementById('pacHistorial').innerHTML = '';
            mostrarMensaje(cajaModal, 'error', error.message);
        }
    }
}

function llenarModal(p, citas) {
    document.getElementById('pacienteTitulo').textContent = 'Ficha del paciente';
    document.getElementById('pacNombre').textContent = p.nombre + ' ' + p.apellido;
    document.getElementById('pacCorreo').textContent = p.correo;

    let textoCitas = ' citas';
    if (p.total_citas === 1) {
        textoCitas = ' cita';
    }
    let textoFinalizadas = ' finalizadas';
    if (p.finalizadas === 1) {
        textoFinalizadas = ' finalizada';
    }
    let resumen = p.total_citas + textoCitas + ' en total · ' + p.finalizadas + textoFinalizadas;
    if (p.en_curso > 0) {
        resumen = resumen + ' · 1 en curso';
    }
    document.getElementById('pacResumen').textContent = resumen;

    let html = '';
    citas.forEach(function (c) {
        html = html + `
            <li class="historial-cita">
                <div class="historial-cita__fecha">
                    <strong>${escapar(fechaCita(c.fecha))}</strong>
                    <span>${escapar(horaCita(c.hora))}</span>
                </div>
                <div class="historial-cita__motivo">
                    <strong>${escapar(c.servicio)}</strong>
                    <span>${escapar(c.objetivo)}</span>
                    <span class="texto-suave">Lleva: ${escapar(c.tiempo)}</span>
                </div>
                <span class="badge ${claseEstadoCita(c.estado)}">${escapar(textoEstadoCita(c.estado))}</span>
            </li>`;
    });
    document.getElementById('pacHistorial').innerHTML = html;
}

function cerrarModal() {
    modal.hidden = true;
    peticion = peticion + 1;
    document.body.style.overflow = '';
}

document.getElementById('btnCerrarPaciente').addEventListener('click', cerrarModal);
document.getElementById('btnCerrarPaciente2').addEventListener('click', cerrarModal);
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
