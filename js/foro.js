// =====================================================================
// pages/foro.php
// Peticiones a la API:
//   GET  /api/foro.php                      publicaciones activas: {oficiales, comunidad}   (sin sesión)
//   GET  /api/foro.php?id=N                 una publicación con sus respuestas              (sin sesión)
//   POST /api/foro.php                      crear publicación   {categoria, titulo, contenido}
//   POST /api/foro_respuestas.php?publicacion=N   responder     {contenido}
// Si quien publica es admin o profesional, la publicación sale como OFICIAL (lo decide el servidor).
// (api.js, header-sesion.js y foro-comun.js ya están cargados)
// =====================================================================
const listaOficiales  = document.getElementById('listaOficiales');
const listaComunidad  = document.getElementById('listaComunidad');
const modalCrear      = document.getElementById('modalCrear');
const modalVer        = document.getElementById('modalVer');
const formCrear       = document.getElementById('formCrear');
const formResponder   = document.getElementById('formResponder');
const mensajeCrear    = document.getElementById('mensajeCrear');
const mensajeVer      = document.getElementById('mensajeVer');
const listaRespuestas = document.getElementById('listaRespuestas');
const btnPublicar     = document.getElementById('btnPublicar');
const btnResponder    = document.getElementById('btnResponder');

let publicacionAbierta = null;      // id de la publicación que se está viendo
let peticionVer        = 0;         // para ignorar respuestas viejas si el usuario hace clic rápido

cargarForo();

/* ---------- LISTAS ---------- */
async function cargarForo() {
    try {
        const datos = await api('GET', 'foro.php');
        dibujarOficiales(datos.oficiales);
        dibujarComunidad(datos.comunidad);
    } catch (error) {
        listaOficiales.innerHTML = '<p class="foro__vacio">' + escapar(error.message) + '</p>';
        listaComunidad.innerHTML = '';
    }
}

function textoRespuestas(n) {
    if (n === 1) {
        return '1 respuesta';
    }
    return n + ' respuestas';
}

function resumen(texto) {
    if (texto.length <= 140) {
        return texto;
    }
    return texto.slice(0, 140).trim() + '…';
}

// Cada tarjeta es clicable (y se puede abrir con Enter). Todo dato pasa por escapar() (anti XSS).
function dibujarOficiales(lista) {
    if (lista.length === 0) {
        listaOficiales.innerHTML = '<p class="foro__vacio">Todavía no hay publicaciones oficiales.</p>';
        return;
    }

    let html = '';
    lista.forEach(function (p, i) {
        const grande = (i === 0);
        let claseTarjeta = 'oficial';
        let claseAvatar  = 'avatar avatar--oficial avatar--chico';
        let claseTitulo  = 'oficial__titulo oficial__titulo--chico';
        let texto        = '';
        if (grande) {
            claseTarjeta = 'oficial oficial--grande';
            claseAvatar  = 'avatar avatar--oficial';
            claseTitulo  = 'oficial__titulo';
            texto        = '<p class="oficial__texto">' + escapar(resumen(p.contenido)) + '</p>';
        }

        html = html + `
            <article class="${claseTarjeta}" data-id="${p.id}" tabindex="0" role="button" aria-label="Abrir: ${escapar(p.titulo)}">
                <div class="oficial__cabecera">
                    <div class="${claseAvatar}">${escapar(p.autor.nombre.charAt(0).toUpperCase())}</div>
                    <div class="oficial__autor">
                        <strong>${escapar(nombreAutor(p.autor))}</strong>
                        <span>${escapar(p.categoria + ' · ' + rolForo(p.autor))}</span>
                    </div>
                    <span class="insignia">Oficial</span>
                </div>
                <h3 class="${claseTitulo}">${escapar(p.titulo)}</h3>
                ${texto}
                <div class="stats">
                    <span class="stats__item"><i class="fa-regular fa-comment"></i> ${textoRespuestas(p.respuestas)}</span>
                </div>
            </article>`;
    });
    listaOficiales.innerHTML = html;
}

function dibujarComunidad(lista) {
    if (lista.length === 0) {
        listaComunidad.innerHTML = '<p class="foro__vacio">Todavía no hay publicaciones</p>';
        return;
    }

    let html = '';
    lista.forEach(function (p) {
        html = html + `
            <article class="post" data-id="${p.id}" tabindex="0" role="button" aria-label="Abrir: ${escapar(p.titulo)}">
                <div class="post__contenido">
                    <h3 class="post__titulo">${escapar(p.titulo)}</h3>
                    <p class="post__meta">${escapar(nombreAutor(p.autor) + ' - ' + p.categoria + ' - ' + fechaForo(p.creado_en))}</p>
                    <div class="stats">
                        <span class="stats__item"><i class="fa-regular fa-comment"></i> ${textoRespuestas(p.respuestas)}</span>
                    </div>
                </div>
            </article>`;
    });
    listaComunidad.innerHTML = html;
}

function abrirDesdeTarjeta(e) {
    const tarjeta = e.target.closest('[data-id]');
    if (tarjeta) {
        abrirPublicacion(Number(tarjeta.dataset.id));
    }
}
[listaOficiales, listaComunidad].forEach(function (lista) {
    lista.addEventListener('click', abrirDesdeTarjeta);
    lista.addEventListener('keydown', function (e) {
        if (e.key === 'Enter' && e.target.matches('[data-id]')) {
            abrirDesdeTarjeta(e);
        }
    });
});

/* ---------- VENTANAS ---------- */
function abrirModal(modal) {
    modal.classList.add('abierto');
    document.body.style.overflow = 'hidden';
}

function cerrarModales() {
    modalCrear.classList.remove('abierto');
    modalVer.classList.remove('abierto');
    document.body.style.overflow = '';
    publicacionAbierta = null;
    peticionVer = peticionVer + 1;
}

document.querySelectorAll('[data-cerrar]').forEach(function (el) {
    el.addEventListener('click', cerrarModales);
});
document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape') {
        cerrarModales();
    }
});

function mostrarAlerta(caja, texto) {
    caja.textContent = texto;
    caja.hidden = false;
}
function ocultarAlerta(caja) {
    caja.textContent = '';
    caja.hidden = true;
}

/* ---------- VER UNA PUBLICACIÓN ---------- */
async function abrirPublicacion(id) {
    peticionVer = peticionVer + 1;
    const miPeticion = peticionVer;

    ocultarAlerta(mensajeVer);
    publicacionAbierta = id;
    document.getElementById('verCategoria').textContent = '';
    document.getElementById('verTitulo').textContent    = 'Cargando...';
    document.getElementById('verTexto').textContent     = '';
    document.getElementById('verAutor').textContent     = '';
    document.getElementById('verMeta').textContent      = '';
    document.getElementById('verAvatar').textContent    = '';
    document.getElementById('verInsignia').hidden       = true;
    document.getElementById('verConteo').textContent    = 'Respuestas';
    listaRespuestas.innerHTML = '';
    prepararFormularioRespuesta();
    abrirModal(modalVer);

    try {
        const datos = await api('GET', 'foro.php?id=' + id);
        if (miPeticion !== peticionVer) {
            return;                                           // ya abrieron otra o cerraron la ventana
        }
        llenarPublicacion(datos.publicacion, datos.respuestas);
    } catch (error) {
        if (miPeticion !== peticionVer) {
            return;
        }
        document.getElementById('verTitulo').textContent = 'Publicación no disponible';
        mostrarAlerta(mensajeVer, error.message);
        formResponder.hidden = true;
        if (error.status === 404) {
            cargarForo();                                     // ya no existe: actualizamos las listas
        }
    }
}

function llenarPublicacion(p, respuestas) {
    document.getElementById('verCategoria').textContent = p.categoria;
    document.getElementById('verTitulo').textContent    = p.titulo;
    document.getElementById('verTexto').textContent     = p.contenido;           // textContent: nunca interpreta HTML
    document.getElementById('verAutor').textContent     = nombreAutor(p.autor);
    document.getElementById('verMeta').textContent      = rolForo(p.autor) + ' · ' + fechaForo(p.creado_en);
    document.getElementById('verAvatar').textContent    = p.autor.nombre.charAt(0).toUpperCase();
    document.getElementById('verInsignia').hidden       = !p.oficial;
    dibujarRespuestas(respuestas);
}

function dibujarRespuestas(respuestas) {
    document.getElementById('verConteo').textContent = textoRespuestas(respuestas.length);

    if (respuestas.length === 0) {
        listaRespuestas.innerHTML = '<li class="respuestas__vacio">Aún no hay respuestas. ¡Escribe la primera!</li>';
        return;
    }

    let html = '';
    respuestas.forEach(function (r) {
        let etiqueta = '';
        if (r.autor.rol !== 'cliente') {
            etiqueta = '<span class="respuesta__rol respuesta__rol--' + escapar(r.autor.rol) + '">' + escapar(rolForo(r.autor)) + '</span>';
        }
        html = html + `
            <li class="respuesta">
                <div class="respuesta__cabecera">
                    <strong>${escapar(nombreAutor(r.autor))}</strong>
                    ${etiqueta}
                    <span class="respuesta__fecha">${escapar(fechaForo(r.creado_en))}</span>
                </div>
                <p>${escapar(r.contenido)}</p>
            </li>`;
    });
    listaRespuestas.innerHTML = html;
}

// Solo quien inició sesión puede responder; los visitantes ven un enlace para ingresar
function prepararFormularioRespuesta() {
    const conSesion = Sesion.token() && Sesion.usuario();
    formResponder.hidden = !conSesion;
    document.getElementById('avisoSesion').hidden = !!conSesion;
    document.getElementById('resp-contenido').value = '';
}

/* ---------- RESPONDER ---------- */
formResponder.addEventListener('submit', async function (e) {
    e.preventDefault();
    if (publicacionAbierta === null) {
        return;
    }

    const id = publicacionAbierta;
    const campo = document.getElementById('resp-contenido');
    ocultarAlerta(mensajeVer);
    btnResponder.disabled = true;
    btnResponder.textContent = 'Enviando...';

    try {
        await api('POST', 'foro_respuestas.php?publicacion=' + id, { contenido: campo.value.trim() });
        campo.value = '';
        const datos = await api('GET', 'foro.php?id=' + id);        // recargamos para ver la respuesta en orden
        if (publicacionAbierta === id) {
            dibujarRespuestas(datos.respuestas);
            listaRespuestas.lastElementChild.scrollIntoView({ block: 'nearest' });
        }
        cargarForo();                                               // actualiza los contadores de las tarjetas
    } catch (error) {
        mostrarAlerta(mensajeVer, error.message);
        if (error.status === 404) {
            formResponder.hidden = true;                            // la desactivaron mientras escribías
            cargarForo();
        }
    }

    btnResponder.disabled = false;
    btnResponder.textContent = 'Responder';
});

/* ---------- CREAR UNA PUBLICACIÓN ---------- */
document.getElementById('btnCrear').addEventListener('click', function () {
    const usuario = Sesion.usuario();
    if (!Sesion.token() || !usuario) {
        location.href = BASE_URL + '/ingresar.php';                 // para publicar hay que iniciar sesión
        return;
    }

    let descripcion = 'Comparte tu duda o experiencia con la comunidad';
    if (usuario.rol !== 'cliente') {
        descripcion = 'Tu publicación saldrá como oficial, arriba en el foro.';
    }
    document.getElementById('crearDescripcion').textContent = descripcion;

    ocultarAlerta(mensajeCrear);
    abrirModal(modalCrear);
    document.getElementById('pub-categoria').focus();
});

formCrear.addEventListener('submit', async function (e) {
    e.preventDefault();
    ocultarAlerta(mensajeCrear);
    btnPublicar.disabled = true;
    btnPublicar.textContent = 'Publicando...';

    try {
        const datos = await api('POST', 'foro.php', {
            categoria: formCrear.categoria.value,
            titulo:    formCrear.titulo.value.trim(),
            contenido: formCrear.contenido.value.trim()
        });
        formCrear.reset();
        modalCrear.classList.remove('abierto');
        await cargarForo();
        abrirPublicacion(datos.publicacion.id);                     // mostramos la publicación recién creada
    } catch (error) {
        mostrarAlerta(mensajeCrear, error.message);
    }

    btnPublicar.disabled = false;
    btnPublicar.textContent = 'Publicar';
});
