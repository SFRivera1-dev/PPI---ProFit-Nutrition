// =====================================================================
// COMÚN A LOS PANELES (admin y profesional)
//   1. Comprueba que hay sesión y que el rol coincide con el panel
//   2. Rellena el nombre y el rol de la barra lateral
//   3. Botón de cerrar sesión y menú lateral en móvil
//
// Deja la variable global USUARIO para que la usen los scripts de cada página.
// =====================================================================

// <body data-rol="admin">  ->  protegerPagina('admin')
const USUARIO = protegerPagina(document.body.dataset.rol);

if (USUARIO) {
    const textosRol = { admin: 'Administrador', profesional: 'Profesional', cliente: 'Cliente' };

    document.getElementById('panelNombre').textContent = USUARIO.nombre + ' ' + USUARIO.apellido;
    document.getElementById('panelRol').textContent    = textosRol[USUARIO.rol] || '';

    // En el dashboard el título es "Hola, Nombre"
    const titulo = document.getElementById('panelTitulo');
    if (titulo.dataset.saludo) {
        titulo.textContent = 'Hola, ' + USUARIO.nombre;
    }

    document.getElementById('btnSalir').addEventListener('click', function (e) {
        e.preventDefault();
        cerrarSesion();
    });
}

/* ---------- Menú lateral en móvil ---------- */
const sidebar = document.getElementById('sidebar');
const overlay = document.getElementById('panelOverlay');
const toggle  = document.getElementById('menuToggle');

function abrirSidebar(abrir) {
    sidebar.classList.toggle('open', abrir);
    overlay.classList.toggle('open', abrir);
}
if (toggle) {
    toggle.addEventListener('click', () => abrirSidebar(true));
    overlay.addEventListener('click', () => abrirSidebar(false));
}
