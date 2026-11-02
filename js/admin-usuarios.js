// =====================================================================
// admin/usuarios.php
// Peticiones a la API:
//   GET   /api/usuarios.php?rol=...   listar (con filtro opcional)
//   POST  /api/usuarios.php           crear
//   PATCH /api/usuarios.php?id=5      cambiar rol o estado
// =====================================================================
const ROLES = { admin: 'Administrador', profesional: 'Profesional', cliente: 'Cliente' };

const caja     = document.getElementById('mensaje');
const tbody    = document.getElementById('tbodyUsuarios');
const filtros  = document.getElementById('filtros');
let filtroRol  = '';          // '' = todos

if (USUARIO) {
    dibujarFiltros();
    cargarUsuarios();
}

/* ---------- LISTAR ---------- */
async function cargarUsuarios() {
    try {
        let ruta = 'usuarios.php';
        if (filtroRol) {
            ruta = 'usuarios.php?rol=' + filtroRol;
        }
        const datos = await api('GET', ruta);                       // {usuarios: [...]}

        document.getElementById('tituloLista').textContent = 'Todos los usuarios (' + datos.usuarios.length + ')';
        tbody.innerHTML = datos.usuarios.map(filaHtml).join('');
    } catch (error) {
        mostrarMensaje(caja, 'error', error.message);
    }
}

// Cada usuario es una fila. Todo dato de usuario pasa por escapar() (anti XSS).
function filaHtml(u) {
    const esYo   = u.id === USUARIO.id;
    const activo = u.estado === 'activo';

    // Las opciones del selector de rol (la del rol actual va marcada)
    let opciones = '';
    Object.entries(ROLES).forEach(function ([valor, texto]) {
        let marcada = '';
        if (u.rol === valor) {
            marcada = 'selected';
        }
        opciones = opciones + '<option value="' + valor + '" ' + marcada + '>' + texto + '</option>';
    });

    // Tu propia fila: sale la etiqueta "Tú", el rol en texto y sin botón (no puedes cambiarte a ti mismo)
    let etiquetaYo = '';
    let celdaRol   = '<select class="select-mini" data-id="' + u.id + '">' + opciones + '</select>';
    let botonEstado = '';

    // El badge y el botón dependen de si el usuario está activo o no
    let claseEstado = 'bad';
    let textoEstado = 'Inactivo';
    let nuevoEstado = 'activo';
    let textoBoton  = 'Activar';
    if (activo) {
        claseEstado = 'ok';
        textoEstado = 'Activo';
        nuevoEstado = 'inactivo';
        textoBoton  = 'Desactivar';
    }

    if (esYo) {
        etiquetaYo = '<span class="badge info">Tú</span>';
        celdaRol   = ROLES[u.rol];
    } else {
        botonEstado = '<button class="btn-mini" data-id="' + u.id + '" data-nuevo-estado="' + nuevoEstado + '">' + textoBoton + '</button>';
    }

    return `
        <tr>
            <td>
                <strong>${escapar(u.nombre + ' ' + u.apellido)}</strong>
                ${etiquetaYo}<br>
                <small class="muted">${escapar(u.correo)}</small>
            </td>
            <td>
                ${celdaRol}
            </td>
            <td><span class="badge ${claseEstado}">${textoEstado}</span></td>
            <td class="der">
                ${botonEstado}
            </td>
        </tr>`;
}

/* ---------- FILTROS (Todos / Administrador / ...) ---------- */
function dibujarFiltros() {
    const todos = { '': 'Todos', ...ROLES };
    let html = '';
    Object.entries(todos).forEach(function ([valor, texto]) {
        let clase = 'chip';
        if (valor === filtroRol) {
            clase = 'chip active';
        }
        html = html + '<a href="#" class="' + clase + '" data-rol="' + valor + '">' + texto + '</a>';
    });
    filtros.innerHTML = html;
}

filtros.addEventListener('click', function (e) {
    const chip = e.target.closest('.chip');
    if (!chip) return;
    e.preventDefault();
    filtroRol = chip.dataset.rol;
    dibujarFiltros();
    cargarUsuarios();
});

/* ---------- MODIFICAR: cambiar el rol (select) ---------- */
tbody.addEventListener('change', async function (e) {
    if (!e.target.matches('select[data-id]')) return;
    try {
        await api('PATCH', 'usuarios.php?id=' + e.target.dataset.id, { rol: e.target.value });
        mostrarMensaje(caja, 'ok', 'Rol actualizado.');
    } catch (error) {
        mostrarMensaje(caja, 'error', error.message);
    }
    cargarUsuarios();
});

/* ---------- MODIFICAR: activar / desactivar (botón) ---------- */
tbody.addEventListener('click', async function (e) {
    const boton = e.target.closest('button[data-id]');
    if (!boton) return;
    try {
        await api('PATCH', 'usuarios.php?id=' + boton.dataset.id, { estado: boton.dataset.nuevoEstado });
        mostrarMensaje(caja, 'ok', 'Estado del usuario actualizado.');
    } catch (error) {
        mostrarMensaje(caja, 'error', error.message);
    }
    cargarUsuarios();
});

/* ---------- CREAR ---------- */
document.getElementById('formCrear').addEventListener('submit', async function (e) {
    e.preventDefault();
    const form = e.target;
    try {
        const datos = await api('POST', 'usuarios.php', {
            nombre:   form.nombre.value,
            apellido: form.apellido.value,
            correo:   form.correo.value,
            password: form.password.value,
            rol:      form.rol.value
        });
        mostrarMensaje(caja, 'ok', 'Usuario ' + datos.usuario.nombre + ' ' + datos.usuario.apellido + ' creado como ' + ROLES[datos.usuario.rol] + '.');
        form.reset();
        cargarUsuarios();
    } catch (error) {
        mostrarMensaje(caja, 'error', error.message);
    }
});
