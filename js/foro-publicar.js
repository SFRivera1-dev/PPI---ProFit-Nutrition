// =====================================================================
// Ventana "Nueva publicación oficial" (admin/foro.php y profesional/foro.php)
//   POST /api/foro.php   {categoria, titulo, contenido}
// Quien publica es admin o profesional, así que el servidor la marca como OFICIAL.
// La página que la usa define:   function despuesDePublicar(publicacion) { ... }
// (api.js, panel.js y foro-comun.js ya están cargados)
// =====================================================================
const modalNueva    = document.getElementById('modalNueva');
const formNueva     = document.getElementById('formNueva');
const cajaNueva     = document.getElementById('mensajeNueva');
const btnPublicarN  = document.getElementById('btnPublicarNueva');

document.getElementById('btnNueva').addEventListener('click', function () {
    cajaNueva.className = '';
    cajaNueva.textContent = '';
    modalNueva.hidden = false;
    document.body.style.overflow = 'hidden';
    formNueva.categoria.focus();
});

function cerrarNueva() {
    modalNueva.hidden = true;
    document.body.style.overflow = '';
}

document.getElementById('btnCerrarNueva').addEventListener('click', cerrarNueva);
document.getElementById('btnCancelarNueva').addEventListener('click', cerrarNueva);
modalNueva.addEventListener('click', function (e) {
    if (e.target === modalNueva) {
        cerrarNueva();
    }
});
document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape' && !modalNueva.hidden) {
        cerrarNueva();
    }
});

formNueva.addEventListener('submit', async function (e) {
    e.preventDefault();
    btnPublicarN.disabled = true;
    btnPublicarN.textContent = 'Publicando...';
    cajaNueva.className = '';
    cajaNueva.textContent = '';

    try {
        const datos = await api('POST', 'foro.php', {
            categoria: formNueva.categoria.value,
            titulo:    formNueva.titulo.value.trim(),
            contenido: formNueva.contenido.value.trim()
        });
        formNueva.reset();
        cerrarNueva();
        despuesDePublicar(datos.publicacion);
    } catch (error) {
        mostrarMensaje(cajaNueva, 'error', error.message);
    }

    btnPublicarN.disabled = false;
    btnPublicarN.textContent = 'Publicar';
});
