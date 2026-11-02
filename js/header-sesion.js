// =====================================================================
// HEADER PÚBLICO: si hay sesión, muestra "Mi panel" / "Mi cuenta" y "Cerrar sesion".
// (Antes lo hacía PHP; ahora PHP no sabe quién eres: lo decide el navegador.)
// =====================================================================
(function () {
    const usuario = Sesion.usuario();
    if (!Sesion.token() || !usuario) return;   // visitante: se queda "Iniciar sesion"

    let texto = 'Mi panel';
    if (usuario.rol === 'cliente') {
        texto = 'Mi cuenta';
    }

    // Botón del header de escritorio
    document.querySelectorAll('.ingresar').forEach(function (enlace) {
        enlace.textContent = texto;
        enlace.href = urlPanel(usuario.rol);
    });

    // Menú lateral (móvil): "Mi panel" + "Cerrar sesion"
    const menu = document.querySelector('.iniciar-sesion-menu');
    if (menu) {
        menu.innerHTML = '';

        const panel = document.createElement('a');
        panel.href = urlPanel(usuario.rol);
        panel.textContent = texto;

        const salir = document.createElement('a');
        salir.href = '#';
        salir.textContent = 'Cerrar sesion';
        salir.addEventListener('click', function (e) { e.preventDefault(); cerrarSesion(); });

        menu.append(panel, salir);
    }
})();
