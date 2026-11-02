// =====================================================================
// MIS PEDIDOS (cliente/pedidos.php)      ->   GET /api/pedidos.php
//
// 1. Pide a la API los pedidos del usuario que inició sesión
// 2. Dibuja una tarjeta por pedido (fila de arriba + detalle que se abre)
// 3. Al hacer clic en la fila de arriba, abre o cierra el detalle
//
// (api.js y header-sesion.js ya los cargó includes/header.php)
// =====================================================================
const usuarioPedidos = protegerPagina('cliente');      // si no eres cliente, te redirige

const pedidosCargando = document.getElementById('pedidosCargando');
const pedidosVacio    = document.getElementById('pedidosVacio');
const listaPedidos    = document.getElementById('listaPedidos');

// Cómo se llama cada estado en pantalla (el de la base de datos es el de la izquierda)
const ESTADOS_PEDIDO = {
    pendiente:  'Pendiente',
    confirmado: 'Confirmado',
    enviado:    'En camino',
    entregado:  'Entregado',
    cancelado:  'Cancelado'
};

const MESES_PEDIDO = ['ene', 'feb', 'mar', 'abr', 'may', 'jun', 'jul', 'ago', 'sep', 'oct', 'nov', 'dic'];

function formatoPrecio(numero) {
    return '$' + Number(numero).toLocaleString('es-CO');
}

// "2026-10-24 02:15:00" -> "24 oct 2026"
function formatoFecha(texto) {
    const partes = String(texto).slice(0, 10).split('-');
    return Number(partes[2]) + ' ' + MESES_PEDIDO[Number(partes[1]) - 1] + ' ' + partes[0];
}

// ---------------------------------------------------------------------
// Dibujar
// ---------------------------------------------------------------------
function htmlProducto(producto) {
    let imagen = '';
    if (producto.imagen_url) {
        imagen = '<img src="' + escapar(producto.imagen_url) + '" alt="">';
    }

    return `
        <li class="pedido__producto">
            <div class="pedido__miniatura">${imagen}</div>
            <div>
                <div class="pedido__producto-nombre">${escapar(producto.nombre)}</div>
                <div class="pedido__producto-cantidad">Cantidad: ${producto.cantidad}</div>
            </div>
            <div class="pedido__producto-precio">${formatoPrecio(producto.subtotal)}</div>
        </li>`;
}

function htmlPedido(pedido, abierto) {
    const entrega = pedido.entrega;

    let nombreEstado = pedido.estado;
    if (ESTADOS_PEDIDO[pedido.estado]) {
        nombreEstado = ESTADOS_PEDIDO[pedido.estado];
    }

    let clasePedido = 'pedido';
    let ocultoDetalle = ' hidden';
    if (abierto) {
        clasePedido = 'pedido pedido--abierto';
        ocultoDetalle = '';
    }

    let productos = '';
    pedido.productos.forEach(function (producto) {
        productos = productos + htmlProducto(producto);
    });

    return `
        <li class="${clasePedido}">
            <button type="button" class="pedido__cabecera" aria-expanded="${abierto}">
                <span class="pedido__izquierda">
                    <span class="pedido__linea">
                        <span class="pedido__numero">#${escapar(pedido.numero)}</span>
                        <span class="estado estado--${escapar(pedido.estado)}">${escapar(nombreEstado)}</span>
                    </span>
                    <span class="pedido__fecha">${formatoFecha(pedido.fecha)}</span>
                </span>
                <span class="pedido__derecha">
                    <span class="pedido__total"><small>Total</small><strong>${formatoPrecio(pedido.total)}</strong></span>
                    <i class="fa-solid fa-chevron-down pedido__flecha"></i>
                </span>
            </button>

            <div class="pedido__detalle"${ocultoDetalle}>
                <div class="pedido__bloque">
                    <h3>Datos de entrega</h3>
                    <p class="pedido__destinatario">${escapar(entrega.nombre)} ${escapar(entrega.apellido)}</p>
                    <p class="pedido__texto">${escapar(entrega.direccion)}</p>
                    <p class="pedido__texto">${escapar(entrega.ciudad)}, ${escapar(entrega.pais)}</p>
                </div>

                <div class="pedido__bloque">
                    <h3>Resumen</h3>
                    <div class="pedido__fila"><span>Productos</span><span>${formatoPrecio(pedido.subtotal)}</span></div>
                    <div class="pedido__fila"><span>Domicilio</span><span>${formatoPrecio(pedido.domicilio)}</span></div>
                    <div class="pedido__fila pedido__fila--total"><span>Total pagado</span><span>${formatoPrecio(pedido.total)}</span></div>
                </div>

                <div class="pedido__bloque pedido__bloque--ancho">
                    <h3>Productos del pedido</h3>
                    <ul class="pedido__productos">${productos}</ul>
                </div>
            </div>
        </li>`;
}

function dibujarPedidos(pedidos) {
    pedidosCargando.hidden = true;

    if (pedidos.length === 0) {
        pedidosVacio.hidden = false;
        listaPedidos.hidden = true;
        return;
    }

    let html = '';
    pedidos.forEach(function (pedido, posicion) {
        html = html + htmlPedido(pedido, posicion === 0);      // el más reciente sale abierto
    });
    listaPedidos.innerHTML = html;
    listaPedidos.hidden = false;
    pedidosVacio.hidden = true;
}

// ---------------------------------------------------------------------
// Abrir y cerrar cada pedido (un solo listener para toda la lista)
// ---------------------------------------------------------------------
listaPedidos.addEventListener('click', function (evento) {
    const cabecera = evento.target.closest('.pedido__cabecera');
    if (!cabecera) {
        return;
    }

    const pedido  = cabecera.closest('.pedido');
    const detalle = pedido.querySelector('.pedido__detalle');
    const abrir   = detalle.hidden;                 // si estaba cerrado, lo abrimos

    detalle.hidden = !abrir;
    pedido.classList.toggle('pedido--abierto', abrir);
    cabecera.setAttribute('aria-expanded', abrir);
});

// ---------------------------------------------------------------------
// Cargar
// ---------------------------------------------------------------------
async function cargarPedidos() {
    try {
        const datos = await api('GET', 'pedidos.php');
        dibujarPedidos(datos.pedidos);
    } catch (error) {
        pedidosCargando.hidden = true;
        mostrarMensaje(document.getElementById('mensaje'), 'error', error.message);
    }
}

if (usuarioPedidos) {
    cargarPedidos();
}
