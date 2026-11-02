// =====================================================================
// admin/pedidos.php
// Peticiones a la API:
//   GET   /api/pedidos_admin.php?q=       listar y buscar (por número de pedido o cliente)
//   PATCH /api/pedidos_admin.php?id=N     cambiar el estado   {"estado": "enviado"}
// (api.js y panel.js ya los cargó includes/panel_fin.php)
// =====================================================================
const caja          = document.getElementById('mensaje');
const cajaModal     = document.getElementById('mensajeModal');
const tbody         = document.getElementById('tbodyPedidos');
const pie           = document.getElementById('pedidosPie');
const inputBuscar   = document.getElementById('buscarPedido');
const modal         = document.getElementById('modalPedido');
const formEstado    = document.getElementById('formEstado');
const selectEstado  = document.getElementById('selectEstado');
const notaEstado    = document.getElementById('notaEstado');
const btnGuardar    = document.getElementById('btnGuardarEstado');

// Cómo se llama cada estado y de qué color es su etiqueta (clases de css/panel.css)
const ESTADOS = {
    pendiente:  { texto: 'Pendiente',  clase: 'warn' },
    confirmado: { texto: 'Confirmado', clase: 'warn' },
    enviado:    { texto: 'Enviado',    clase: 'info' },
    entregado:  { texto: 'Entregado',  clase: 'ok' },
    cancelado:  { texto: 'Cancelado',  clase: 'bad' }
};

const MESES = ['ene', 'feb', 'mar', 'abr', 'may', 'jun', 'jul', 'ago', 'sep', 'oct', 'nov', 'dic'];

let pedidos       = [];     // la lista que se está mostrando
let totalPedidos  = 0;      // cuántos pedidos coinciden en total (la lista muestra máximo 100)
let temporizador  = null;   // para esperar un momento mientras se escribe en el buscador
let pedidoAbierto = null;   // el pedido que está abierto en la ventana (null = ninguno)

if (USUARIO) {
    cargarPedidos();
}

/* ---------- Formatos ---------- */
function formatoPrecio(numero) {
    return '$' + Number(numero).toLocaleString('es-CO');            // 145000 -> $145.000
}

// "2026-10-24 02:15:00" -> "24 oct" (si es de otro año: "24 oct 2025")
function formatoFecha(texto) {
    const partes = String(texto).slice(0, 10).split('-');
    let resultado = Number(partes[2]) + ' ' + MESES[Number(partes[1]) - 1];
    if (Number(partes[0]) !== new Date().getFullYear()) {
        resultado = resultado + ' ' + partes[0];
    }
    return resultado;
}

// "2026-10-24 02:15:00" -> "24 oct 2026"
function formatoFechaCompleta(texto) {
    const partes = String(texto).slice(0, 10).split('-');
    return Number(partes[2]) + ' ' + MESES[Number(partes[1]) - 1] + ' ' + partes[0];
}

function etiquetaEstado(estado) {
    let datos = { texto: estado, clase: 'warn' };
    if (ESTADOS[estado]) {
        datos = ESTADOS[estado];
    }
    return '<span class="badge ' + datos.clase + '">' + escapar(datos.texto) + '</span>';
}

// Nombre de quien compró: la cuenta si existe; si borraron la cuenta, el nombre de la entrega
function nombreCliente(pedido) {
    if (pedido.cliente) {
        return pedido.cliente.nombre + ' ' + pedido.cliente.apellido;
    }
    return pedido.entrega.nombre + ' ' + pedido.entrega.apellido;
}

// "Whey Gold" o "Whey Gold +2 más"
function resumenProductos(pedido) {
    if (pedido.productos.length === 0) {
        return '-';
    }
    let texto = pedido.productos[0].nombre;
    if (pedido.productos.length > 1) {
        texto = texto + ' +' + (pedido.productos.length - 1) + ' más';
    }
    return texto;
}

/* ---------- LISTAR / BUSCAR ---------- */
async function cargarPedidos() {
    try {
        let ruta = 'pedidos_admin.php';
        if (inputBuscar.value.trim() !== '') {
            ruta = ruta + '?q=' + encodeURIComponent(inputBuscar.value.trim());
        }

        const datos = await api('GET', ruta);            // {pedidos: [...], total: N}
        pedidos = datos.pedidos;
        totalPedidos = datos.total;
        dibujarTabla();
    } catch (error) {
        mostrarMensaje(caja, 'error', error.message);
    }
}

function dibujarTabla() {
    if (pedidos.length === 0) {
        tbody.innerHTML = '<tr><td colspan="7" class="vacio">No hay pedidos para mostrar.</td></tr>';
        pie.textContent = '';
        return;
    }

    tbody.innerHTML = pedidos.map(filaHtml).join('');

    if (totalPedidos > pedidos.length) {
        pie.textContent = 'Mostrando los ' + pedidos.length + ' más recientes de ' + totalPedidos + ' pedidos. Usa el buscador para encontrar uno.';
    } else if (totalPedidos === 1) {
        pie.textContent = '1 pedido';
    } else {
        pie.textContent = totalPedidos + ' pedidos';
    }
}

// Cada pedido es una fila. Todo dato pasa por escapar() (anti XSS).
function filaHtml(p) {
    return `
        <tr class="fila-pedido" data-id="${p.id}">
            <td class="pedido-id">#${p.id}</td>
            <td><strong>${escapar(nombreCliente(p))}</strong></td>
            <td>${escapar(resumenProductos(p))}</td>
            <td>${escapar(formatoFecha(p.fecha))}</td>
            <td>${etiquetaEstado(p.estado)}</td>
            <td class="der"><strong>${formatoPrecio(p.total)}</strong></td>
            <td class="col-flecha">
                <button type="button" class="btn-icono" data-id="${p.id}" title="Ver y modificar" aria-label="Ver y modificar el pedido ${p.id}"><i class="fa-solid fa-chevron-right"></i></button>
            </td>
        </tr>`;
}

/* ---------- BUSCADOR ---------- */
inputBuscar.addEventListener('input', function () {
    clearTimeout(temporizador);
    temporizador = setTimeout(cargarPedidos, 300);        // espera 0,3 s después de la última tecla
});

/* ---------- VENTANA (modal) ---------- */
// Se abre con la flecha o con un clic en cualquier parte de la fila
tbody.addEventListener('click', function (e) {
    const fila = e.target.closest('.fila-pedido');
    if (!fila) {
        return;
    }
    const pedido = pedidos.find(function (p) { return p.id === Number(fila.dataset.id); });
    if (pedido) {
        abrirModal(pedido);
    }
});

function abrirModal(pedido) {
    pedidoAbierto = pedido;
    cajaModal.className = '';
    cajaModal.textContent = '';
    llenarModal(pedido);
    modal.hidden = false;
    document.body.style.overflow = 'hidden';
    selectEstado.focus();
}

function cerrarModal() {
    modal.hidden = true;
    pedidoAbierto = null;
    document.body.style.overflow = '';
}

function llenarModal(p) {
    document.getElementById('pedidoTitulo').textContent = 'Pedido #' + p.numero;

    if (p.cliente) {
        document.getElementById('pedCliente').textContent = p.cliente.nombre + ' ' + p.cliente.apellido;
        document.getElementById('pedCorreo').textContent  = p.cliente.correo;
    } else {
        document.getElementById('pedCliente').textContent = nombreCliente(p);
        document.getElementById('pedCorreo').textContent  = '(la cuenta ya no existe)';
    }
    document.getElementById('pedFecha').textContent = 'Fecha: ' + formatoFechaCompleta(p.fecha);

    document.getElementById('pedDestinatario').textContent = p.entrega.nombre + ' ' + p.entrega.apellido;
    document.getElementById('pedDireccion').textContent    = p.entrega.direccion;
    document.getElementById('pedCiudad').textContent       = p.entrega.ciudad + ', ' + p.entrega.pais;

    let html = '';
    p.productos.forEach(function (item) {
        let imagen = '<span class="miniatura miniatura--chica"></span>';
        if (item.imagen_url) {
            imagen = '<img class="miniatura miniatura--chica" src="' + escapar(item.imagen_url) + '" alt="">';
        }
        html = html + `
            <li>
                ${imagen}
                <div>
                    <strong>${escapar(item.nombre)}</strong>
                    <span>${item.cantidad} x ${formatoPrecio(item.precio_unitario)}</span>
                </div>
                <strong>${formatoPrecio(item.subtotal)}</strong>
            </li>`;
    });
    document.getElementById('pedProductos').innerHTML = html;

    document.getElementById('pedSubtotal').textContent  = formatoPrecio(p.subtotal);
    document.getElementById('pedDomicilio').textContent = formatoPrecio(p.domicilio);
    document.getElementById('pedTotal').textContent     = formatoPrecio(p.total);

    // Estado: qué se puede elegir según el estado actual
    selectEstado.value = p.estado;
    prepararSelectorEstado(p.estado);
}

// Reglas (las mismas del servidor): cancelado es final; un entregado no se puede cancelar
function prepararSelectorEstado(estadoActual) {
    const opcionCancelado = selectEstado.querySelector('option[value="cancelado"]');
    opcionCancelado.disabled = (estadoActual === 'entregado');

    notaEstado.classList.remove('pedido-estado__nota--alerta');

    const bloqueado = (estadoActual === 'cancelado');
    selectEstado.disabled = bloqueado;
    btnGuardar.disabled   = bloqueado;

    if (bloqueado) {
        notaEstado.textContent = 'Este pedido está cancelado y ya no se puede modificar.';
    } else if (estadoActual === 'entregado') {
        notaEstado.textContent = 'Un pedido entregado ya no se puede cancelar.';
    } else {
        notaEstado.textContent = '';
    }
}

// Al elegir "Cancelado" avisamos lo que pasa
selectEstado.addEventListener('change', function () {
    if (selectEstado.value === 'cancelado') {
        notaEstado.textContent = 'Al cancelar, las unidades vuelven al stock y el pedido ya no se podrá reabrir.';
        notaEstado.classList.add('pedido-estado__nota--alerta');
    } else {
        notaEstado.textContent = '';
        notaEstado.classList.remove('pedido-estado__nota--alerta');
    }
});

document.getElementById('btnCerrarPedido').addEventListener('click', cerrarModal);
document.getElementById('btnCancelarPedido').addEventListener('click', cerrarModal);

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

/* ---------- GUARDAR EL NUEVO ESTADO ---------- */
formEstado.addEventListener('submit', async function (e) {
    e.preventDefault();

    if (pedidoAbierto === null) {
        return;
    }
    if (selectEstado.value === pedidoAbierto.estado) {
        cerrarModal();                                      // no cambió nada
        return;
    }

    btnGuardar.disabled = true;
    btnGuardar.textContent = 'Guardando...';
    cajaModal.className = '';
    cajaModal.textContent = '';

    try {
        const datos = await api('PATCH', 'pedidos_admin.php?id=' + pedidoAbierto.id, { estado: selectEstado.value });
        const nuevo = datos.pedido;

        // Reemplazamos el pedido en la lista y dibujamos de nuevo
        pedidos = pedidos.map(function (p) {
            if (p.id === nuevo.id) {
                return nuevo;
            }
            return p;
        });
        dibujarTabla();
        cerrarModal();
        mostrarMensaje(caja, 'ok', 'Pedido #' + nuevo.numero + ' actualizado: ' + ESTADOS[nuevo.estado].texto + '.');
    } catch (error) {
        mostrarMensaje(cajaModal, 'error', error.message);
        if (error.status === 409) {
            await cargarPedidos();                          // el pedido cambió: mostramos lo real
            const actualizado = pedidos.find(function (p) { return p.id === pedidoAbierto.id; });
            if (actualizado) {
                pedidoAbierto = actualizado;
                llenarModal(actualizado);
            }
        }
    }

    btnGuardar.textContent = 'Guardar estado';
    if (pedidoAbierto !== null) {
        prepararSelectorEstado(pedidoAbierto.estado);
    }
});
