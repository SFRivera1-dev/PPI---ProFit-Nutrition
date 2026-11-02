// =====================================================================
// profesional/foro.php
//   GET  /api/foro.php    -> publicaciones activas: aquí mostramos las oficiales
//   POST /api/foro.php    -> publicar como oficial (js/foro-publicar.js)
// (api.js, panel.js, foro-comun.js y foro-publicar.js ya están cargados)
// =====================================================================
const caja  = document.getElementById('mensaje');
const tbody = document.getElementById('tbodyForo');

if (USUARIO) {
    cargarOficiales();
}

async function cargarOficiales() {
    try {
        const datos = await api('GET', 'foro.php');
        dibujarTabla(datos.oficiales);
    } catch (error) {
        mostrarMensaje(caja, 'error', error.message);
    }
}

function dibujarTabla(lista) {
    if (lista.length === 0) {
        tbody.innerHTML = '<tr><td colspan="4" class="vacio">Todavía no hay publicaciones oficiales. ¡Crea la primera!</td></tr>';
        return;
    }

    let html = '';
    lista.forEach(function (p) {
        html = html + `
            <tr>
                <td class="celda-titulo">
                    <strong>${escapar(p.titulo)}</strong><br>
                    <span class="texto-suave">${escapar(p.categoria)}</span>
                </td>
                <td>${escapar(nombreAutor(p.autor))}<br><span class="texto-suave">${escapar(rolForo(p.autor))}</span></td>
                <td>${escapar(fechaForo(p.creado_en))}</td>
                <td class="der">${p.respuestas}</td>
            </tr>`;
    });
    tbody.innerHTML = html;
}

// Lo llama js/foro-publicar.js cuando la publicación se creó
function despuesDePublicar(publicacion) {
    cargarOficiales();
    mostrarMensaje(caja, 'ok', 'Publicación oficial creada: “' + publicacion.titulo + '”. Ya aparece arriba en el foro.');
}
