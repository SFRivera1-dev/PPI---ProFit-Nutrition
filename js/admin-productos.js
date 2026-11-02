// =====================================================================
// admin/productos.php
// Peticiones a la API:
//   GET    /api/productos.php?q=&categoria=       listar (con filtros)
//   POST   /api/productos.php                     crear
//   PUT    /api/productos.php?id=5                modificar
//   DELETE /api/productos.php?id=5                eliminar
//   POST   /api/productos_imagen.php?id=5         subir la foto (archivo)
//   DELETE /api/productos_imagen.php?id=5         quitar la foto
// (api.js y panel.js ya los cargó includes/panel_fin.php)
// =====================================================================
const caja            = document.getElementById('mensaje');
const cajaModal       = document.getElementById('mensajeModal');
const tbody           = document.getElementById('tbodyProductos');
const inputBuscar     = document.getElementById('buscar');
const filtroCategoria = document.getElementById('filtroCategoria');
const modal           = document.getElementById('modalProducto');
const formProducto    = document.getElementById('formProducto');
const modalTitulo     = document.getElementById('modalTitulo');
const btnGuardar      = document.getElementById('btnGuardar');
const inputImagen     = document.getElementById('inputImagen');
const textoArchivo    = document.getElementById('textoArchivo');
const vistaPrevia     = document.getElementById('vistaPrevia');
const btnQuitarImagen = document.getElementById('btnQuitarImagen');

const TAMANO_MAXIMO = 2 * 1024 * 1024;                              // 2 MB (igual que en el servidor)
const TIPOS_IMAGEN  = ['image/jpeg', 'image/png', 'image/webp'];

let productos          = [];      // la lista que se está mostrando (para saber qué producto se edita)
let categoriasCargadas = false;
let temporizador       = null;    // para esperar un momento mientras se escribe en el buscador
let idEditando         = 0;       // 0 = creando uno nuevo; otro número = editando ese producto
let tieneImagenGuardada = false;  // ¿el producto que se edita ya tiene foto en el servidor?
let quitarImagen       = false;   // ¿el admin pulsó "Quitar imagen"?

if (USUARIO) {
    cargarProductos();
}

/* ---------- LISTAR ---------- */
async function cargarProductos() {
    try {
        // Armamos la URL con los filtros que haya
        const filtros = new URLSearchParams();
        if (inputBuscar.value.trim() !== '') {
            filtros.set('q', inputBuscar.value.trim());
        }
        if (filtroCategoria.value !== '') {
            filtros.set('categoria', filtroCategoria.value);
        }

        let ruta = 'productos.php';
        if (filtros.toString() !== '') {
            ruta = ruta + '?' + filtros.toString();
        }

        const datos = await api('GET', ruta);          // {productos: [...], categorias: [...], total: N}
        productos = datos.productos;

        if (!categoriasCargadas) {
            llenarCategorias(datos.categorias);
            categoriasCargadas = true;
        }

        if (productos.length === 0) {
            tbody.innerHTML = '<tr><td colspan="5" class="vacio">No hay productos para mostrar.</td></tr>';
        } else {
            tbody.innerHTML = productos.map(filaHtml).join('');
        }
    } catch (error) {
        mostrarMensaje(caja, 'error', error.message);
    }
}

// Llena el filtro de la página y el selector del formulario con las categorías de la API
function llenarCategorias(categorias) {
    categorias.forEach(function (categoria) {
        filtroCategoria.add(new Option(categoria, categoria));
        formProducto.categoria.add(new Option(categoria, categoria));
    });
}

/* ---------- Cada producto es una fila. Todo dato pasa por escapar() (anti XSS). ---------- */
function filaHtml(p) {
    return `
        <tr>
            <td>
                <div class="producto-celda">
                    ${miniaturaHtml(p)}
                    <span>${escapar(p.nombre)}</span>
                </div>
            </td>
            <td>${escapar(p.categoria)}</td>
            <td>${formatoPrecio(p.precio)}</td>
            <td><span class="badge ${claseStock(p.stock)}">${p.stock} unid</span></td>
            <td class="der">
                <button type="button" class="btn-icono" data-accion="editar" data-id="${p.id}" title="Editar" aria-label="Editar ${escapar(p.nombre)}"><i class="fa-regular fa-pen-to-square"></i></button>
                <button type="button" class="btn-icono peligro" data-accion="eliminar" data-id="${p.id}" title="Eliminar" aria-label="Eliminar ${escapar(p.nombre)}"><i class="fa-regular fa-trash-can"></i></button>
            </td>
        </tr>`;
}

function miniaturaHtml(p) {
    if (p.imagen_url !== '') {
        return '<img class="miniatura" src="' + escapar(p.imagen_url) + '" alt="">';
    }
    return '<span class="miniatura"></span>';
}

function formatoPrecio(numero) {
    return '$' + Number(numero).toLocaleString('es-CO');     // 145000 -> $145.000
}

// Color de la etiqueta de stock: pocas unidades = rojo, medias = amarillo, muchas = verde
function claseStock(stock) {
    if (stock <= 5) {
        return 'bad';
    }
    if (stock <= 15) {
        return 'warn';
    }
    return 'ok';
}

/* ---------- FILTROS ---------- */
inputBuscar.addEventListener('input', function () {
    clearTimeout(temporizador);
    temporizador = setTimeout(cargarProductos, 300);        // espera 0,3 s después de la última tecla
});
filtroCategoria.addEventListener('change', cargarProductos);

/* ---------- VENTANA (modal) ---------- */
function abrirModal(producto) {
    cajaModal.className = '';
    cajaModal.textContent = '';
    formProducto.reset();                                   // también vacía el campo del archivo
    quitarImagen = false;
    textoArchivo.textContent = 'Subir imagen';

    if (producto) {
        idEditando = producto.id;
        modalTitulo.textContent = 'Editar producto';
        formProducto.nombre.value    = producto.nombre;
        formProducto.categoria.value = producto.categoria;
        formProducto.precio.value    = producto.precio;
        formProducto.stock.value     = producto.stock;
        tieneImagenGuardada = (producto.imagen_url !== '');
        mostrarVistaPrevia(producto.imagen_url);
    } else {
        idEditando = 0;
        modalTitulo.textContent = 'Nuevo producto';
        tieneImagenGuardada = false;
        mostrarVistaPrevia('');
    }

    modal.hidden = false;
    formProducto.nombre.focus();
}

function cerrarModal() {
    modal.hidden = true;
}

// Muestra (o esconde) la foto pequeña dentro del formulario y el botón "Quitar imagen"
function mostrarVistaPrevia(direccion) {
    if (direccion === '') {
        vistaPrevia.hidden = true;
        vistaPrevia.removeAttribute('src');
        btnQuitarImagen.hidden = true;
    } else {
        vistaPrevia.src = direccion;
        vistaPrevia.hidden = false;
        btnQuitarImagen.hidden = false;
    }
}

document.getElementById('btnNuevo').addEventListener('click', function () { abrirModal(null); });
document.getElementById('btnCerrarModal').addEventListener('click', cerrarModal);
document.getElementById('btnCancelar').addEventListener('click', cerrarModal);

modal.addEventListener('click', function (e) {
    if (e.target === modal) {                               // clic en el fondo oscuro
        cerrarModal();
    }
});
document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape' && !modal.hidden) {
        cerrarModal();
    }
});

/* ---------- ELEGIR LA IMAGEN ---------- */
inputImagen.addEventListener('change', function () {
    cajaModal.className = '';
    cajaModal.textContent = '';

    const archivo = inputImagen.files[0];
    if (!archivo) {
        return;
    }

    // Revisamos aquí para avisar rápido (el servidor las vuelve a revisar)
    if (TIPOS_IMAGEN.indexOf(archivo.type) === -1) {
        mostrarMensaje(cajaModal, 'error', 'Formato no permitido. Usa JPG, PNG o WEBP.');
        inputImagen.value = '';
        return;
    }
    if (archivo.size > TAMANO_MAXIMO) {
        mostrarMensaje(cajaModal, 'error', 'La imagen es demasiado grande. Máximo 2 MB.');
        inputImagen.value = '';
        return;
    }

    quitarImagen = false;
    textoArchivo.textContent = 'Cambiar imagen';
    mostrarVistaPrevia(URL.createObjectURL(archivo));       // foto temporal, solo en el navegador
});

btnQuitarImagen.addEventListener('click', function () {
    inputImagen.value = '';                                  // olvida el archivo elegido
    textoArchivo.textContent = 'Subir imagen';
    mostrarVistaPrevia('');
    if (tieneImagenGuardada) {
        quitarImagen = true;                                 // al guardar, se borra la foto del servidor
    }
});

/* ---------- CREAR / MODIFICAR (el formulario de la ventana) ---------- */
formProducto.addEventListener('submit', async function (e) {
    e.preventDefault();
    btnGuardar.disabled = true;

    const cuerpo = {
        nombre:    formProducto.nombre.value,
        categoria: formProducto.categoria.value,
        precio:    Number(formProducto.precio.value),
        stock:     Number(formProducto.stock.value)
    };

    // PASO 1: guardar los datos del producto (JSON)
    let producto = null;
    try {
        let respuesta;
        if (idEditando > 0) {
            respuesta = await api('PUT', 'productos.php?id=' + idEditando, cuerpo);
        } else {
            respuesta = await api('POST', 'productos.php', cuerpo);
        }
        producto = respuesta.producto;
    } catch (error) {
        mostrarMensaje(cajaModal, 'error', error.message);
        btnGuardar.disabled = false;
        return;
    }

    // PASO 2: la foto (si hay que subirla o quitarla). Va aparte porque un archivo no viaja como JSON.
    try {
        const archivo = inputImagen.files[0];
        if (archivo) {
            const formulario = new FormData();
            formulario.append('imagen', archivo);
            await api('POST', 'productos_imagen.php?id=' + producto.id, formulario);
        } else if (quitarImagen) {
            await api('DELETE', 'productos_imagen.php?id=' + producto.id);
        }
    } catch (error) {
        // El producto SÍ se guardó; solo falló la foto. Dejamos la ventana abierta para reintentar.
        idEditando = producto.id;
        modalTitulo.textContent = 'Editar producto';
        mostrarMensaje(cajaModal, 'error', 'El producto se guardó, pero la imagen no se pudo subir: ' + error.message);
        cargarProductos();
        btnGuardar.disabled = false;
        return;
    }

    if (idEditando > 0) {
        mostrarMensaje(caja, 'ok', 'Producto actualizado.');
    } else {
        mostrarMensaje(caja, 'ok', 'Producto creado.');
    }
    cerrarModal();
    cargarProductos();
    btnGuardar.disabled = false;
});

/* ---------- EDITAR / ELIMINAR (los iconos de cada fila) ---------- */
tbody.addEventListener('click', async function (e) {
    const boton = e.target.closest('button[data-accion]');
    if (!boton) {
        return;
    }

    const id = Number(boton.dataset.id);
    const producto = productos.find(function (p) { return p.id === id; });
    if (!producto) {
        return;
    }

    if (boton.dataset.accion === 'editar') {
        abrirModal(producto);
        return;
    }

    // Eliminar
    if (!confirm('¿Eliminar "' + producto.nombre + '"? Esta acción no se puede deshacer.')) {
        return;
    }
    try {
        await api('DELETE', 'productos.php?id=' + id);
        mostrarMensaje(caja, 'ok', 'Producto eliminado.');
    } catch (error) {
        mostrarMensaje(caja, 'error', error.message);
    }
    cargarProductos();
});
