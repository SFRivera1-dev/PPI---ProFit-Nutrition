// =====================================================================
// AGENDA TU CITA (pages/agenda.php)
//
//   GET  /api/citas.php                  -> mis citas (para saber si ya tengo una activa)
//   GET  /api/citas.php?fecha=2026-11-02 -> qué horas de ese día están libres u ocupadas
//   POST /api/citas.php                  -> agendar la cita
//
// Reglas (las vuelve a revisar el servidor):
//   - una fecha y hora que ya están ocupadas no se pueden pedir
//   - si ya tienes una cita pendiente o confirmada, no puedes agendar otra hasta que quede finalizada
// (api.js y citas-comun.js ya están cargados)
// =====================================================================
const usuarioAgenda = protegerPagina('cliente');       // sin sesión te manda a ingresar

const formCita        = document.getElementById('formCita');
const campoFecha      = document.getElementById('fechaCita');
const campoHora       = document.getElementById('horaCita');
const notaHoras       = document.getElementById('notaHoras');
const btnConfirmar    = document.getElementById('btnConfirmarCita');
const avisoActiva     = document.getElementById('avisoCitaActiva');
const avisoActivaTxt  = document.getElementById('avisoCitaTexto');
const mensajeCita     = document.getElementById('mensajeCita');
const modalCita       = document.getElementById('modalCita');

const DIAS_MAXIMOS = 90;      // igual que en el servidor

if (usuarioAgenda) {
    prepararFechas();
    revisarCitaActiva();
}

// ---------------------------------------------------------------------
// La fecha: desde mañana hasta dentro de 90 días
// ---------------------------------------------------------------------
function textoFechaInput(fecha) {
    const mes = String(fecha.getMonth() + 1).padStart(2, '0');
    const dia = String(fecha.getDate()).padStart(2, '0');
    return fecha.getFullYear() + '-' + mes + '-' + dia;
}

function prepararFechas() {
    const manana = new Date();
    manana.setDate(manana.getDate() + 1);

    const maxima = new Date();
    maxima.setDate(maxima.getDate() + DIAS_MAXIMOS);

    campoFecha.min = textoFechaInput(manana);
    campoFecha.max = textoFechaInput(maxima);
}

// ---------------------------------------------------------------------
// ¿Ya tengo una cita pendiente o confirmada?  Entonces el formulario se bloquea.
// ---------------------------------------------------------------------
async function revisarCitaActiva() {
    try {
        const datos = await api('GET', 'citas.php');

        const activa = datos.citas.find(function (cita) {
            return cita.estado !== 'finalizada';
        });
        if (activa) {
            bloquearFormulario(activa);
        }
    } catch (error) {
        mostrarError(error.message);
    }
}

function bloquearFormulario(cita) {
    avisoActivaTxt.textContent = 'Tu cita de ' + cita.servicio + ' es el ' + fechaCita(cita.fecha) +
        ' a las ' + horaCita(cita.hora) + ' (' + textoEstadoCita(cita.estado).toLowerCase() + ').';
    avisoActiva.hidden = false;

    // Todos los campos y el botón quedan desactivados
    formCita.querySelectorAll('input, select, button').forEach(function (campo) {
        campo.disabled = true;
    });
    formCita.classList.add('bloqueado');
}

// ---------------------------------------------------------------------
// Al elegir una fecha: preguntamos qué horas están libres y marcamos las ocupadas
// ---------------------------------------------------------------------
campoFecha.addEventListener('change', cargarHoras);

async function cargarHoras() {
    ocultarError();
    notaHoras.textContent = '';

    if (campoFecha.value === '') {
        return;
    }

    try {
        const datos = await api('GET', 'citas.php?fecha=' + encodeURIComponent(campoFecha.value));
        marcarHoras(datos.horas);
    } catch (error) {
        mostrarError(error.message);
    }
}

// Las horas ocupadas se desactivan y dicen "(ocupada)"
function marcarHoras(horas) {
    let libres = 0;

    horas.forEach(function (item) {
        const opcion = campoHora.querySelector('option[value="' + item.hora + '"]');
        if (!opcion) {
            return;
        }
        // El texto original ("8:00 am") lo calculamos con horaCita(), así no se acumulan los "(ocupada)"
        if (item.libre) {
            opcion.disabled = false;
            opcion.textContent = horaCita(item.hora);
            libres = libres + 1;
        } else {
            opcion.disabled = true;
            opcion.textContent = horaCita(item.hora) + ' (ocupada)';
        }
    });

    // Si la hora que estaba elegida se ocupó, la quitamos
    const elegida = campoHora.querySelector('option:checked');
    if (elegida && elegida.disabled) {
        campoHora.value = '';
    }

    if (libres === 0) {
        notaHoras.textContent = 'Ese día ya no tiene horas libres. Elige otra fecha.';
    }
}

// ---------------------------------------------------------------------
// Enviar el formulario
// ---------------------------------------------------------------------
formCita.addEventListener('submit', async function (evento) {
    evento.preventDefault();
    ocultarError();

    const servicio = formCita.querySelector('input[name="servicio"]:checked');
    if (!servicio) {
        mostrarError('Elige un tipo de servicio.');
        return;
    }

    const cita = {
        servicio: servicio.value,
        objetivo: formCita.objetivo.value.trim(),
        tiempo:   formCita.tiempo.value.trim(),
        fecha:    campoFecha.value,
        hora:     campoHora.value
    };

    btnConfirmar.disabled = true;
    btnConfirmar.textContent = 'Agendando...';

    try {
        const datos = await api('POST', 'citas.php', cita);
        abrirModal(datos.cita);
        bloquearFormulario(datos.cita);              // ya tiene una cita activa: no puede pedir otra
    } catch (error) {
        btnConfirmar.disabled = false;

        if (error.status === 409) {
            await cargarHoras();                    // alguien tomó esa hora: mostramos las horas reales
            await revisarCitaActiva();              // o ya tenías una cita: se bloquea el formulario
        }
        mostrarError(error.message);                // al final, porque cargarHoras() limpia los avisos
    }

    if (!formCita.classList.contains('bloqueado')) {
        btnConfirmar.disabled = false;
    }
    btnConfirmar.textContent = 'Confirmar cita';
});

// ---------------------------------------------------------------------
// Mensajes de error
// ---------------------------------------------------------------------
function mostrarError(texto) {
    mensajeCita.textContent = texto;
    mensajeCita.hidden = false;
    mensajeCita.scrollIntoView({ behavior: 'smooth', block: 'center' });
}

function ocultarError() {
    mensajeCita.textContent = '';
    mensajeCita.hidden = true;
}

// ---------------------------------------------------------------------
// La ventana "Tu cita está pendiente"
// ---------------------------------------------------------------------
function abrirModal(cita) {
    document.getElementById('modalCitaResumen').textContent =
        cita.servicio + ' · ' + fechaCita(cita.fecha) + ' · ' + horaCita(cita.hora);

    modalCita.hidden = false;
    document.body.style.overflow = 'hidden';
    document.getElementById('modalCitaCerrar').focus();
}

function cerrarModal() {
    modalCita.hidden = true;
    document.body.style.overflow = '';
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

document.getElementById('modalCitaCerrar').addEventListener('click', cerrarModal);

modalCita.addEventListener('click', function (evento) {
    if (evento.target === modalCita) {              // clic en el fondo oscuro
        cerrarModal();
    }
});

document.addEventListener('keydown', function (evento) {
    if (evento.key === 'Escape' && !modalCita.hidden) {
        cerrarModal();
    }
});
