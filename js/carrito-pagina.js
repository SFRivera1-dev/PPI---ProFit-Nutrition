// =====================================================================
// PÁGINA DEL CARRITO (pages/carrito.php)
//
// 1. Lee del navegador los ids y cantidades (Carrito.leer(), js/carrito.js)
// 2. Pide a la API esos productos:  GET /api/productos.php?ids=7,3
//    (así los precios, nombres y stock son siempre los reales)
// 3. Dibuja la lista y el resumen
// 4. Los botones  -  +  y Eliminar actualizan el carrito y vuelven a dibujar
// =====================================================================

const avisoCarrito       = document.getElementById('avisoCarrito');
const cargandoCarrito    = document.getElementById('cargandoCarrito');
const carritoVacio       = document.getElementById('carritoVacio');
const carritoContenido   = document.getElementById('carritoContenido');
const listaCarrito       = document.getElementById('listaCarrito');
const resumenProductos   = document.getElementById('resumenProductos');
const resumenSubtotal    = document.getElementById('resumenSubtotal');
const resumenTotal       = document.getElementById('resumenTotal');

// Productos que devolvió la API, por id: { "7": {id, nombre, precio, stock, ...} }
let productosPorId = {};

function formatoPrecio(numero) {
    return '$' + Number(numero).toLocaleString('es-CO');
}

function mostrarAviso(texto) {
    avisoCarrito.textContent = texto;
    avisoCarrito.hidden = false;
}

function ocultarAviso() {
    avisoCarrito.hidden = true;
}

// ---------------------------------------------------------------------
// Carga inicial
// ---------------------------------------------------------------------
async function cargarCarrito() {
    // Ids válidos guardados en el navegador (ignora basura)
    const guardado = Carrito.leer();
    const ids = [];
    Object.keys(guardado).forEach(function (id) {
        const cantidad = Number(guardado[id]);
        if (/^\d+$/.test(id) && Number.isInteger(cantidad) && cantidad > 0) {
            ids.push(id);
        }
    });

    if (ids.length === 0) {
        productosPorId = {};
        dibujar();
        return;
    }

    try {
        const datos = await api('GET', 'productos.php?ids=' + ids.join(','));
        productosPorId = {};
        datos.productos.forEach(function (p) {
            productosPorId[p.id] = p;
        });
    } catch (error) {
        cargandoCarrito.hidden = false;
        cargandoCarrito.textContent = error.message;
        return;
    }

    ajustarCarritoAlCatalogo(ids);
    dibujar();
}

// Quita del carrito lo que ya no existe o está agotado, y baja las cantidades al stock
function ajustarCarritoAlCatalogo(ids) {
    const avisos = [];

    ids.forEach(function (id) {
        const producto = productosPorId[id];

        if (!producto) {
            Carrito.quitar(id);
            avisos.push('Un producto ya no está disponible y se quitó de tu carrito.');
            return;
        }
        if (producto.stock <= 0) {
            Carrito.quitar(id);
            avisos.push('"' + producto.nombre + '" está agotado y se quitó de tu carrito.');
            return;
        }
        if (Carrito.cantidad(id) > producto.stock) {
            Carrito.cambiar(id, producto.stock);
            avisos.push('Solo quedan ' + producto.stock + ' unidades de "' + producto.nombre + '"; ajustamos la cantidad.');
        }
    });

    // Ids que no estaban en la lista limpia (basura) se limpian también
    const limpio = {};
    Object.keys(Carrito.leer()).forEach(function (id) {
        if (productosPorId[id]) {
            limpio[id] = Carrito.cantidad(id);
        }
    });
    Carrito.guardar(limpio);

    if (avisos.length > 0) {
        mostrarAviso(avisos.join(' '));
    }
}

// ---------------------------------------------------------------------
// Dibujar
// ---------------------------------------------------------------------
function htmlItem(producto, cantidad) {
    let imagen = '<i class="fa-solid fa-image"></i>';
    if (producto.imagen_url) {
        imagen = '<img src="' + escapar(producto.imagen_url) + '" alt="' + escapar(producto.nombre) + '">';
    }

    let unitario = '';
    if (cantidad > 1) {
        unitario = '<div class="item__unitario">' + formatoPrecio(producto.precio) + ' c/u</div>';
    }

    let desactivarMenos = '';
    if (cantidad <= 1) {
        desactivarMenos = ' disabled';
    }
    let desactivarMas = '';
    if (cantidad >= producto.stock) {
        desactivarMas = ' disabled';
    }

    return `
        <li class="item" data-id="${producto.id}">
            <div class="item__imagen">${imagen}</div>

            <div class="item__info">
                <span class="item__categoria">${escapar(producto.categoria)}</span>
                <h3 class="item__nombre">${escapar(producto.nombre)}</h3>
                <span class="item__stock">Disponibles: ${producto.stock}</span>
                <button type="button" class="item__eliminar" data-accion="quitar">
                    <i class="fa-regular fa-trash-can"></i> Eliminar
                </button>
            </div>

            <div class="item__lado">
                <div class="cantidad">
                    <button type="button" data-accion="menos" aria-label="Quitar una unidad"${desactivarMenos}>−</button>
                    <span>${cantidad}</span>
                    <button type="button" data-accion="mas" aria-label="Agregar una unidad"${desactivarMas}>+</button>
                </div>
                <div>
                    <div class="item__precio">${formatoPrecio(producto.precio * cantidad)}</div>
                    ${unitario}
                </div>
            </div>
        </li>`;
}

function dibujar() {
    cargandoCarrito.hidden = true;

    let html = '';
    let unidades = 0;
    let total = 0;

    Object.keys(Carrito.leer()).forEach(function (id) {
        const producto = productosPorId[id];
        const cantidad = Carrito.cantidad(id);
        if (producto && cantidad > 0) {
            html = html + htmlItem(producto, cantidad);
            unidades = unidades + cantidad;
            total = total + producto.precio * cantidad;
        }
    });

    if (unidades === 0) {
        carritoContenido.hidden = true;
        carritoVacio.hidden = false;
        return;
    }

    carritoVacio.hidden = true;
    carritoContenido.hidden = false;
    listaCarrito.innerHTML = html;

    resumenProductos.textContent = 'Productos (' + unidades + ')';
    resumenSubtotal.textContent  = formatoPrecio(total);
    resumenTotal.textContent     = formatoPrecio(total);
}

// ---------------------------------------------------------------------
// Botones de cada producto (delegación de eventos)
// ---------------------------------------------------------------------
listaCarrito.addEventListener('click', function (evento) {
    const boton = evento.target.closest('button[data-accion]');
    if (!boton) {
        return;
    }
    const fila = boton.closest('.item');
    const id = fila.dataset.id;
    const producto = productosPorId[id];
    const accion = boton.dataset.accion;
    const actual = Carrito.cantidad(id);

    ocultarAviso();

    if (accion === 'mas') {
        if (!Carrito.agregar(id, producto.stock)) {
            mostrarAviso('No hay más unidades disponibles de "' + producto.nombre + '".');
        }
    } else if (accion === 'menos') {
        Carrito.cambiar(id, actual - 1);
    } else if (accion === 'quitar') {
        Carrito.quitar(id);
    }

    dibujar();
});

cargarCarrito();
