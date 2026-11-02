// =====================================================================
// admin/contacto.php
// Peticiones a la API:
//   GET   /api/contacto_admin.php?q=   los mensajes del formulario (más nuevo primero)
//   PATCH /api/contacto_admin.php?id=N {"estado": "leido" | "nuevo"}
// Al abrir un mensaje "nuevo" se marca solo como "leído".
// (api.js, panel.js y foro-comun.js ya están cargados: de ahí viene fechaForo())
// =====================================================================
const caja        = document.getElementById('mensaje');
const cajaModal   = document.getElementById('mensajeModal');
const tbody       = document.getElementById('tbodyContactos');
const pie         = document.getElementById('contactoPie');
const inputBuscar = document.getElementById('buscarContacto');
const modal       = document.getElementById('modalContacto');
const btnAlternar = document.getElementById('btnAlternarLeido');
const btnCorreo   = document.getElementById('btnResponderCorreo');

let mensajes     = [];          // la lista que se está mostrando
let totalNuevos  = 0;           // cuántos mensajes sin abrir hay en total
let temporizador = null;
let abierto      = null;        // el mensaje abierto en la ventana (null = ninguno)

if (USUARIO) {
    cargarMensajes();
}

/* ---------- LISTAR / BUSCAR ---------- */
async function cargarMensajes() {
    try {
        let ruta = 'contacto_admin.php';
        if (inputBuscar.value.trim() !== '') {
            ruta = ruta + '?q=' + encodeURIComponent(inputBuscar.value.trim());
        }
        const datos = await api('GET', ruta);
        mensajes    = datos.mensajes;
        totalNuevos = datos.nuevos;
        dibujarTabla();
    } catch (error) {
        mostrarMensaje(caja, 'error', error.message);
    }
}

function etiquetaEstado(estado) {
    if (estado === 'nuevo') {
        return '<span class="badge warn">Nuevo</span>';
    }
    return '<span class="badge ok">Leído</span>';
}

function dibujarTabla() {
    if (mensajes.length === 0) {
        tbody.innerHTML = '<tr><td colspan="5" class="vacio">No hay mensajes para mostrar.</td></tr>';
        pie.textContent = '';
        return;
    }
    tbody.innerHTML = mensajes.map(filaHtml).join('');

    let texto = mensajes.length + ' mensajes';
    if (mensajes.length === 1) {
        texto = '1 mensaje';
    }
    if (totalNuevos > 0) {
        texto = texto + ' · ' + totalNuevos + ' sin leer';
    }
    pie.textContent = texto;
}

// Cada mensaje es una fila (los nuevos en negrita). Todo dato pasa por escapar() (anti XSS).
function filaHtml(m) {
    let claseFila = 'fila-pedido';
    if (m.estado === 'nuevo') {
        claseFila = 'fila-pedido fila-nueva';
    }

    return `
        <tr class="${claseFila}" data-id="${m.id}">
            <td>
                <strong>${escapar(m.nombre)}</strong><br>
                <span class="texto-suave">${escapar(m.correo)}</span>
            </td>
            <td class="celda-titulo">${escapar(m.asunto)}</td>
            <td>${escapar(fechaForo(m.creado_en))}</td>
            <td>${etiquetaEstado(m.estado)}</td>
            <td class="col-flecha">
                <button type="button" class="btn-icono" title="Ver mensaje" aria-label="Ver el mensaje de ${escapar(m.nombre)}"><i class="fa-solid fa-chevron-right"></i></button>
            </td>
        </tr>`;
}

inputBuscar.addEventListener('input', function () {
    clearTimeout(temporizador);
    temporizador = setTimeout(cargarMensajes, 300);
});

/* ---------- VENTANA (modal) ---------- */
tbody.addEventListener('click', function (e) {
    const fila = e.target.closest('.fila-pedido');
    if (!fila) {
        return;
    }
    const mensaje = mensajes.find(function (m) { return m.id === Number(fila.dataset.id); });
    if (mensaje) {
        abrirModal(mensaje);
    }
});

function abrirModal(mensaje) {
    abierto = mensaje;
    cajaModal.className = '';
    cajaModal.textContent = '';
    llenarModal(mensaje);
    modal.hidden = false;
    document.body.style.overflow = 'hidden';

    if (mensaje.estado === 'nuevo') {
        cambiarEstado(mensaje.id, 'leido', false);              // abrirlo = leerlo
    }
}

function cerrarModal() {
    modal.hidden = true;
    abierto = null;
    document.body.style.overflow = '';
}

function llenarModal(m) {
    document.getElementById('contactoTitulo').textContent = 'Mensaje #' + m.id;
    document.getElementById('ctoNombre').textContent  = m.nombre;
    document.getElementById('ctoCorreo').textContent  = m.correo;
    document.getElementById('ctoFecha').textContent   = fechaForo(m.creado_en);
    let textoEstado = 'Leído';
    if (m.estado === 'nuevo') {
        textoEstado = 'Sin leer';
    }
    document.getElementById('ctoEstado').textContent  = textoEstado;
    document.getElementById('ctoAsunto').textContent  = m.asunto;
    document.getElementById('ctoMensaje').textContent = m.mensaje;           // textContent: nunca interpreta HTML

    // "Responder por correo" abre el programa de correo con el asunto ya escrito
    btnCorreo.href = 'mailto:' + m.correo + '?subject=' + encodeURIComponent('Re: ' + m.asunto);

    if (m.estado === 'nuevo') {
        btnAlternar.textContent = 'Marcar como leído';
    } else {
        btnAlternar.textContent = 'Marcar como no leído';
    }
}

document.getElementById('btnCerrarContacto').addEventListener('click', cerrarModal);
document.getElementById('btnCerrarContacto2').addEventListener('click', cerrarModal);
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

/* ---------- LEÍDO / NO LEÍDO ---------- */
async function cambiarEstado(id, estado, avisar) {
    try {
        const datos = await api('PATCH', 'contacto_admin.php?id=' + id, { estado: estado });
        const nuevo = datos.mensaje;

        mensajes = mensajes.map(function (m) {
            if (m.id === nuevo.id) {
                return nuevo;
            }
            return m;
        });
        totalNuevos = mensajes.filter(function (m) { return m.estado === 'nuevo'; }).length;
        dibujarTabla();

        if (abierto !== null && abierto.id === nuevo.id) {
            abierto = nuevo;
            llenarModal(nuevo);
        }
        if (avisar) {
            let aviso = 'Marcado como no leído.';
            if (estado === 'leido') {
                aviso = 'Marcado como leído.';
            }
            mostrarMensaje(cajaModal, 'ok', aviso);
        }
    } catch (error) {
        mostrarMensaje(cajaModal, 'error', error.message);
    }
}

btnAlternar.addEventListener('click', function () {
    if (abierto === null) {
        return;
    }
    let nuevoEstado = 'nuevo';
    if (abierto.estado === 'nuevo') {
        nuevoEstado = 'leido';
    }
    cambiarEstado(abierto.id, nuevoEstado, true);
});
