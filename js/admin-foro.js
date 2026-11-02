// =====================================================================
// admin/foro.php
// Peticiones a la API:
//   GET   /api/foro_admin.php?q=     todas las publicaciones (también las desactivadas)
//   PATCH /api/foro_admin.php?id=N   {"activa": false}  desactiva (deja de mostrarse en el foro)
//                                    {"activa": true}   la vuelve a mostrar
//   POST  /api/foro.php              publicar como oficial (js/foro-publicar.js)
// (api.js, panel.js, foro-comun.js y foro-publicar.js ya están cargados)
// =====================================================================
const caja        = document.getElementById('mensaje');
const tbody       = document.getElementById('tbodyForo');
const pie         = document.getElementById('foroPie');
const inputBuscar = document.getElementById('buscarForo');

let publicaciones = [];
let temporizador  = null;

if (USUARIO) {
    cargarForo();
}

async function cargarForo() {
    try {
        let ruta = 'foro_admin.php';
        if (inputBuscar.value.trim() !== '') {
            ruta = ruta + '?q=' + encodeURIComponent(inputBuscar.value.trim());
        }
        const datos = await api('GET', ruta);
        publicaciones = datos.publicaciones;
        dibujarTabla();
    } catch (error) {
        mostrarMensaje(caja, 'error', error.message);
    }
}

function dibujarTabla() {
    if (publicaciones.length === 0) {
        tbody.innerHTML = '<tr><td colspan="7" class="vacio">No hay publicaciones para mostrar.</td></tr>';
        pie.textContent = '';
        return;
    }
    tbody.innerHTML = publicaciones.map(filaHtml).join('');

    const desactivadas = publicaciones.filter(function (p) { return !p.activa; }).length;
    let texto = publicaciones.length + ' publicaciones';
    if (publicaciones.length === 1) {
        texto = '1 publicación';
    }
    if (desactivadas === 1) {
        texto = texto + ' · 1 desactivada';
    }
    if (desactivadas > 1) {
        texto = texto + ' · ' + desactivadas + ' desactivadas';
    }
    pie.textContent = texto;
}

// Cada publicación es una fila. Todo dato pasa por escapar() (anti XSS).
function filaHtml(p) {
    let tipo   = '<span class="badge info">Comunidad</span>';
    if (p.oficial) {
        tipo = '<span class="badge warn">Oficial</span>';
    }

    let estado = '<span class="badge ok">Activa</span>';
    let boton  = '<button type="button" class="btn-mini peligro" data-id="' + p.id + '" data-activa="false">Desactivar</button>';
    if (!p.activa) {
        estado = '<span class="badge bad">Desactivada</span>';
        boton  = '<button type="button" class="btn-mini" data-id="' + p.id + '" data-activa="true">Reactivar</button>';
    }

    let claseFila = '';
    if (!p.activa) {
        claseFila = 'fila-apagada';
    }

    return `
        <tr class="${claseFila}">
            <td class="celda-titulo">
                <strong>${escapar(p.titulo)}</strong><br>
                <span class="texto-suave">${escapar(p.categoria)}</span>
            </td>
            <td>${escapar(nombreAutor(p.autor))}<br><span class="texto-suave">${escapar(rolForo(p.autor))}</span></td>
            <td>${tipo}</td>
            <td>${escapar(fechaForo(p.creado_en))}</td>
            <td class="der">${p.respuestas}</td>
            <td>${estado}</td>
            <td class="der">${boton}</td>
        </tr>`;
}

inputBuscar.addEventListener('input', function () {
    clearTimeout(temporizador);
    temporizador = setTimeout(cargarForo, 300);
});

/* ---------- DESACTIVAR / REACTIVAR ---------- */
tbody.addEventListener('click', async function (e) {
    const boton = e.target.closest('button[data-id]');
    if (!boton) {
        return;
    }

    const activa = (boton.dataset.activa === 'true');
    boton.disabled = true;

    try {
        const datos = await api('PATCH', 'foro_admin.php?id=' + boton.dataset.id, { activa: activa });
        const nueva = datos.publicacion;
        publicaciones = publicaciones.map(function (p) {
            if (p.id === nueva.id) {
                return nueva;
            }
            return p;
        });
        dibujarTabla();

        if (activa) {
            mostrarMensaje(caja, 'ok', 'La publicación se volvió a mostrar en el foro.');
        } else {
            mostrarMensaje(caja, 'ok', 'La publicación se desactivó: ya no se muestra en el foro.');
        }
    } catch (error) {
        mostrarMensaje(caja, 'error', error.message);
        boton.disabled = false;
    }
});

/* ---------- Después de publicar (lo llama js/foro-publicar.js) ---------- */
function despuesDePublicar(publicacion) {
    inputBuscar.value = '';
    cargarForo();
    mostrarMensaje(caja, 'ok', 'Publicación oficial creada: “' + publicacion.titulo + '”.');
}
