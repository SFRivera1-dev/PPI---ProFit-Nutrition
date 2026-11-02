// =====================================================================
// pages/suplementos.php  (la tienda)      ->   GET /api/productos.php
// Es una ruta PÚBLICA: funciona sin iniciar sesión.
// (api.js ya lo cargó includes/header.php)
// =====================================================================
const listaCategorias = document.getElementById('listaCategorias');
const grid            = document.getElementById('gridProductos');
const resultados      = document.getElementById('resultados');
const selectOrden     = document.getElementById('orden');
const rango           = document.getElementById('rangoPrecio');
const precioActual    = document.getElementById('precioActual');

// La ventana del detalle del producto
const modalDetalle      = document.getElementById('modalDetalle');
const modalImagen       = document.getElementById('modalImagen');
const modalCategoria    = document.getElementById('modalCategoria');
const modalNombre       = document.getElementById('modalNombre');
const modalPrecio       = document.getElementById('modalPrecio');
const modalAgregar      = document.getElementById('modalAgregar');
const modalAgregarTexto = document.getElementById('modalAgregarTexto');
const modalCerrar       = document.getElementById('modalCerrar');

let categoriaActiva  = '';       // '' = todas
let busqueda         = '';       // texto que viene del buscador del header ('' = sin búsqueda)
let catalogoListo    = false;    // true cuando ya se dibujaron las categorías y el rango
let numeroPedido     = 0;        // para ignorar respuestas viejas si el usuario sigue escribiendo
let temporizador     = null;
let productosVisibles = [];      // los productos que están en pantalla (para saber cuál se tocó)
let productoAbierto  = null;     // el producto que muestra la ventana
let tarjetaQueAbrio  = null;     // la tarjeta que se tocó (para devolverle el foco al cerrar)
let temporizadorAviso = null;

// Si llegó desde el buscador de otra página (suplementos.php?q=whey), arranca ya filtrado
busqueda = new URLSearchParams(window.location.search).get('q');
if (busqueda === null) {
    busqueda = '';
}
busqueda = busqueda.trim();

cargarCatalogo();

// js/front-js/buscador-header.js llama a esta función cada vez que se escribe en el buscador del header
function buscarProductos(texto) {
    busqueda = texto.trim();

    // Una búsqueda nueva empieza limpia: sin categoría ni tope de precio de antes
    // (si no, "whey" dentro de la categoría Creatina no mostraría nada)
    categoriaActiva = '';
    listaCategorias.querySelectorAll('.filtros__link').forEach(function (a) {
        a.classList.remove('filtros__link--activo');
    });
    listaCategorias.querySelector('a[data-categoria=""]').classList.add('filtros__link--activo');
    rango.value = rango.max;
    precioActual.textContent = formatoPrecio(rango.max);

    cargarCatalogo();
}

/* ---------- Pedir los productos a la API y dibujarlos ---------- */
async function cargarCatalogo() {
    numeroPedido++;
    const miPedido = numeroPedido;

    try {
        // Armamos la URL con los filtros que haya
        const filtros = new URLSearchParams();
        if (busqueda !== '') {
            filtros.set('q', busqueda);
        }
        if (categoriaActiva !== '') {
            filtros.set('categoria', categoriaActiva);
        }
        if (Number(rango.value) < Number(rango.max)) {          // el rango al tope = sin límite de precio
            filtros.set('precio_max', rango.value);
        }
        if (selectOrden.value !== 'relevantes') {
            filtros.set('orden', selectOrden.value);
        }

        let ruta = 'productos.php';
        if (filtros.toString() !== '') {
            ruta = ruta + '?' + filtros.toString();
        }

        const datos = await api('GET', ruta);                   // {productos: [...], categorias: [...], total: N}

        if (miPedido !== numeroPedido) {
            return;                                             // ya hay una búsqueda más nueva
        }

        if (!catalogoListo) {
            catalogoListo = true;
            dibujarCategorias(datos.categorias);
            if (busqueda === '') {
                ajustarRango(datos.productos);
            } else {
                // el rango se calcula con TODO el catálogo, no solo con lo buscado
                const todos = await api('GET', 'productos.php');
                ajustarRango(todos.productos);
            }
        }

        productosVisibles = datos.productos;
        dibujarResultados(datos.total);
        if (datos.productos.length === 0) {
            grid.innerHTML = '<p class="grid__vacio">No encontramos productos con esa búsqueda o filtros.</p>';
        } else {
            grid.innerHTML = datos.productos.map(tarjetaHtml).join('');
        }
    } catch (error) {
        if (miPedido !== numeroPedido) {
            return;
        }
        grid.innerHTML = '<p class="grid__vacio">' + escapar(error.message) + '</p>';
    }
}

/* ---------- Dibujar ---------- */

// Cada producto es una tarjeta. Todo dato pasa por escapar() (anti XSS).
function tarjetaHtml(p) {
    let imagen = '';
    if (p.imagen_url !== '') {
        imagen = '<img src="' + escapar(p.imagen_url) + '" alt="' + escapar(p.nombre) + '">';
    }

    return `
        <article class="tarjeta" data-id="${p.id}" tabindex="0" role="button" aria-label="Ver ${escapar(p.nombre)}">
            <div class="tarjeta__imagen">${imagen}</div>
            <span class="tarjeta__categoria">${escapar(p.categoria)}</span>
            <h3 class="tarjeta__nombre">${escapar(p.nombre)}</h3>
            <p class="tarjeta__precio">${formatoPrecio(p.precio)}</p>
        </article>`;
}

function dibujarCategorias(categorias) {
    categorias.forEach(function (categoria) {
        const item = document.createElement('li');
        item.innerHTML = '<a href="#" class="filtros__link" data-categoria="' + escapar(categoria) + '">' + escapar(categoria) + '</a>';
        listaCategorias.appendChild(item);
    });
}

function dibujarResultados(total) {
    if (total === 1) {
        resultados.innerHTML = '1 <br>resultado';
    } else {
        resultados.innerHTML = total + ' <br>resultados';
    }
}

function formatoPrecio(numero) {
    return '$' + Number(numero).toLocaleString('es-CO');         // 145000 -> $145.000
}

// El rango llega hasta el producto más caro (mínimo $200.000) y empieza en el tope: se ven todos.
function ajustarRango(productos) {
    let masCaro = 0;
    productos.forEach(function (p) {
        if (p.precio > masCaro) {
            masCaro = p.precio;
        }
    });

    let tope = Math.ceil(masCaro / 10000) * 10000;
    if (tope < 200000) {
        tope = 200000;
    }
    rango.max   = tope;
    rango.value = tope;
    precioActual.textContent = formatoPrecio(tope);
}

/* ---------- Eventos ---------- */

// Clic en una categoría
listaCategorias.addEventListener('click', function (e) {
    const enlace = e.target.closest('a[data-categoria]');
    if (!enlace) {
        return;
    }
    e.preventDefault();

    listaCategorias.querySelectorAll('.filtros__link').forEach(function (a) {
        a.classList.remove('filtros__link--activo');
    });
    enlace.classList.add('filtros__link--activo');

    categoriaActiva = enlace.dataset.categoria;
    cargarCatalogo();
});

// Cambiar el orden
selectOrden.addEventListener('change', function () {
    cargarCatalogo();
});

// Mover el rango de precio (espera un momento a que el usuario suelte el control)
rango.addEventListener('input', function () {
    precioActual.textContent = formatoPrecio(rango.value);
    clearTimeout(temporizador);
    temporizador = setTimeout(function () { cargarCatalogo(); }, 250);
});


/* ---------- VENTANA DEL PRODUCTO ---------- */

// Abre la ventana con los datos del producto
function abrirDetalle(producto, tarjeta) {
    productoAbierto = producto;
    tarjetaQueAbrio = tarjeta;

    // Izquierda: la imagen completa (o un cuadro si no tiene)
    if (producto.imagen_url !== '') {
        modalImagen.innerHTML = '<img src="' + escapar(producto.imagen_url) + '" alt="' + escapar(producto.nombre) + '">';
    } else {
        modalImagen.innerHTML = '<span class="modal-producto__sin-imagen">Sin imagen</span>';
    }

    // Derecha: la información (textContent: lo escrito se muestra como texto, nunca como HTML)
    modalCategoria.textContent = producto.categoria;
    modalNombre.textContent    = producto.nombre;
    modalPrecio.textContent    = formatoPrecio(producto.precio);

    // Sin stock no se puede agregar
    clearTimeout(temporizadorAviso);
    if (producto.stock <= 0) {
        modalAgregar.disabled = true;
        modalAgregarTexto.textContent = 'Agotado';
    } else {
        modalAgregar.disabled = false;
        modalAgregarTexto.textContent = 'Agregar al carrito';
    }

    modalDetalle.hidden = false;
    document.body.style.overflow = 'hidden';         // la página de atrás no se desplaza
    modalCerrar.focus();
}

function cerrarDetalle() {
    modalDetalle.hidden = true;
    document.body.style.overflow = '';
    if (tarjetaQueAbrio) {
        tarjetaQueAbrio.focus();                     // el foco vuelve a la tarjeta
    }
}

// Clic en una tarjeta
grid.addEventListener('click', function (e) {
    const tarjeta = e.target.closest('.tarjeta');
    if (!tarjeta) {
        return;
    }
    const id = Number(tarjeta.dataset.id);
    const producto = productosVisibles.find(function (p) { return p.id === id; });
    if (producto) {
        abrirDetalle(producto, tarjeta);
    }
});

// Lo mismo con el teclado (Enter o espacio sobre la tarjeta)
grid.addEventListener('keydown', function (e) {
    if (e.key !== 'Enter' && e.key !== ' ') {
        return;
    }
    const tarjeta = e.target.closest('.tarjeta');
    if (!tarjeta) {
        return;
    }
    e.preventDefault();
    tarjeta.click();
});

// Botón "Agregar al carrito"
modalAgregar.addEventListener('click', function () {
    if (!productoAbierto) {
        return;
    }
    // js/carrito.js: no deja agregar más unidades de las que hay en stock
    const agregado = Carrito.agregar(productoAbierto.id, productoAbierto.stock);

    if (agregado) {
        modalAgregarTexto.textContent = 'Agregado';
    } else {
        modalAgregarTexto.textContent = 'Falta Stock';
    }
    clearTimeout(temporizadorAviso);
    temporizadorAviso = setTimeout(function () {
        modalAgregarTexto.textContent = 'Agregar al carrito';
    }, 1800);
});

// Formas de cerrar: la X, el fondo oscuro y la tecla Escape
modalCerrar.addEventListener('click', cerrarDetalle);
modalDetalle.addEventListener('click', function (e) {
    if (e.target === modalDetalle) {
        cerrarDetalle();
    }
});
document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape' && !modalDetalle.hidden) {
        cerrarDetalle();
    }
});
