// =====================================================================
// VENTANA DE PAGO (pages/carrito.php)
//
// "Continuar a pagar" abre la ventana. Al enviar el formulario se valida
// campo por campo y, si todo está bien, se confirma el pedido.
//
// Usa cosas de js/carrito-pagina.js (se carga antes):
//     productosPorId, formatoPrecio(), dibujar(), ocultarAviso()
// y de js/carrito.js:  Carrito
//
// Al completar la compra se envía el pedido a la API (POST /api/pedidos.php).
// Se manda SOLO: datos de entrega + qué productos y cuántos. Los precios, el
// domicilio y el total los calcula el servidor. Los datos de la TARJETA se
// validan aquí pero NUNCA se envían ni se guardan (eso lo haría una pasarela de pagos).
// =====================================================================

const COSTO_DOMICILIO = 10000;      // siempre se cobra este valor

const modalPago        = document.getElementById('modalPago');
const formPago         = document.getElementById('formPago');
const pagoCuerpo       = document.getElementById('pagoCuerpo');
const pagoExito        = document.getElementById('pagoExito');
const pagoCaja         = document.getElementById('pagoCaja');
const pagoError        = document.getElementById('pagoError');
const btnCompletar     = document.getElementById('btnCompletar');
const btnPagarAbrir    = document.getElementById('btnPagar');

const campoNombre      = document.getElementById('pagoNombre');
const campoApellido    = document.getElementById('pagoApellido');
const campoCiudad      = document.getElementById('pagoCiudad');
const campoDireccion   = document.getElementById('pagoDireccion');
const campoTarjeta     = document.getElementById('pagoTarjeta');
const campoTitular     = document.getElementById('pagoTitular');
const campoVence       = document.getElementById('pagoVence');
const campoCvv         = document.getElementById('pagoCvv');

// ---------------------------------------------------------------------
// Abrir y cerrar
// ---------------------------------------------------------------------
function calcularSubtotal() {
    let unidades = 0;
    let subtotal = 0;
    Object.keys(Carrito.leer()).forEach(function (id) {
        const producto = productosPorId[id];
        const cantidad = Carrito.cantidad(id);
        if (producto && cantidad > 0) {
            unidades = unidades + cantidad;
            subtotal = subtotal + producto.precio * cantidad;
        }
    });
    return { unidades: unidades, subtotal: subtotal };
}

// Escribe en la ventana el resumen (productos, domicilio, total)
function pintarResumenPago() {
    const datos = calcularSubtotal();

    document.getElementById('pagoResumenProductos').textContent = 'Productos (' + datos.unidades + ')';
    document.getElementById('pagoSubtotal').textContent  = formatoPrecio(datos.subtotal);
    document.getElementById('pagoDomicilio').textContent = formatoPrecio(COSTO_DOMICILIO);
    document.getElementById('pagoTotal').textContent     = formatoPrecio(datos.subtotal + COSTO_DOMICILIO);
}

function abrirPago() {
    const datos = calcularSubtotal();
    if (datos.unidades === 0) {
        return;
    }

    pintarResumenPago();
    ocultarErrorPago();

    pagoCaja.classList.remove('modal-pago__caja--estrecha');
    pagoExito.hidden = true;
    pagoCuerpo.hidden = false;
    modalPago.hidden = false;
    document.body.style.overflow = 'hidden';
    campoNombre.focus();
}

function cerrarPago() {
    modalPago.hidden = true;
    document.body.style.overflow = '';
}

btnPagarAbrir.addEventListener('click', function () {
    if (!Sesion.token()) {                           // hay que iniciar sesión para comprar
        location.href = BASE_URL + '/ingresar.php';
        return;
    }
    abrirPago();
});

document.getElementById('pagoCerrar').addEventListener('click', cerrarPago);

modalPago.addEventListener('click', function (evento) {
    if (evento.target === modalPago) {               // clic en el fondo oscuro
        cerrarPago();
    }
});

document.addEventListener('keydown', function (evento) {
    if (evento.key === 'Escape' && !modalPago.hidden) {
        cerrarPago();
    }
});

// ---------------------------------------------------------------------
// Formato mientras se escribe (solo números)
// ---------------------------------------------------------------------
function soloDigitos(texto) {
    return texto.replace(/\D/g, '');
}

// Tarjeta: solo números, agrupados de 4 en 4 (máximo 19 dígitos)
campoTarjeta.addEventListener('input', function () {
    const digitos = soloDigitos(campoTarjeta.value).slice(0, 19);
    campoTarjeta.value = digitos.replace(/(\d{4})(?=\d)/g, '$1 ');
});

// CVV: solo números, máximo 3
campoCvv.addEventListener('input', function () {
    campoCvv.value = soloDigitos(campoCvv.value).slice(0, 3);
});

// Vencimiento: escribes 1228 y queda "12 / 28"
campoVence.addEventListener('input', function () {
    const digitos = soloDigitos(campoVence.value).slice(0, 4);
    if (digitos.length > 2) {
        campoVence.value = digitos.slice(0, 2) + ' / ' + digitos.slice(2);
    } else {
        campoVence.value = digitos;
    }
});

// ---------------------------------------------------------------------
// Validación: cada función devuelve el mensaje de error, o '' si está bien
// ---------------------------------------------------------------------
function errorTexto(valor, nombreCampo) {
    if (valor.trim().length < 2) {
        return nombreCampo + ' es obligatorio';
    }
    return '';
}

function errorDireccion(valor) {
    if (valor.trim().length < 5) {
        return 'Escribe una dirección completa (mínimo 5 caracteres)';
    }
    return '';
}

function errorTarjeta(valor) {
    const digitos = soloDigitos(valor);
    if (digitos.length < 16) {
        return 'La tarjeta debe tener mínimo 16 dígitos';
    }
    return '';
}

function errorVence(valor) {
    const digitos = soloDigitos(valor);
    if (digitos.length !== 4) {
        return 'Usa el formato MM / AA';
    }
    const mes = Number(digitos.slice(0, 2));
    const ano = 2000 + Number(digitos.slice(2));
    if (mes < 1 || mes > 12) {
        return 'El mes debe estar entre 01 y 12';
    }
    const hoy = new Date();
    const anoActual = hoy.getFullYear();
    const mesActual = hoy.getMonth() + 1;
    if (ano < anoActual || (ano === anoActual && mes < mesActual)) {
        return 'La tarjeta está vencida';
    }
    return '';
}

function errorCvv(valor) {
    if (!/^\d{3}$/.test(valor)) {
        return 'El CVV debe tener exactamente 3 dígitos.';
    }
    return '';
}

function mostrarErrorCampo(campo, mensaje) {
    const caja = document.querySelector('[data-error-de="' + campo.id + '"]');
    caja.textContent = mensaje;
    if (mensaje === '') {
        campo.classList.remove('invalido');
    } else {
        campo.classList.add('invalido');
    }
}

// Revisa todos los campos. Devuelve true si todo está bien.
function validarFormulario() {
    const resultados = [
        [campoNombre,    errorTexto(campoNombre.value, 'El nombre')],
        [campoApellido,  errorTexto(campoApellido.value, 'El apellido')],
        [campoCiudad,    errorTexto(campoCiudad.value, 'La ciudad')],
        [campoDireccion, errorDireccion(campoDireccion.value)],
        [campoTarjeta,   errorTarjeta(campoTarjeta.value)],
        [campoTitular,   errorTexto(campoTitular.value, 'El nombre del titular')],
        [campoVence,     errorVence(campoVence.value)],
        [campoCvv,       errorCvv(campoCvv.value)]
    ];

    let primerError = null;
    resultados.forEach(function (par) {
        mostrarErrorCampo(par[0], par[1]);
        if (par[1] !== '' && primerError === null) {
            primerError = par[0];
        }
    });

    if (primerError !== null) {
        primerError.focus();
        return false;
    }
    return true;
}

// Al corregir un campo, su error desaparece
formPago.addEventListener('input', function (evento) {
    if (evento.target.classList.contains('invalido')) {
        mostrarErrorCampo(evento.target, '');
    }
});

// ---------------------------------------------------------------------
// Error del servidor (sin stock, producto borrado...)
// ---------------------------------------------------------------------
function mostrarErrorPago(mensaje) {
    pagoError.textContent = mensaje;
    pagoError.hidden = false;
}

function ocultarErrorPago() {
    pagoError.textContent = '';
    pagoError.hidden = true;
}

// ---------------------------------------------------------------------
// Completar compra: valida, envía el pedido a la API y muestra la confirmación
// ---------------------------------------------------------------------

// Lo que se manda: [{id: 1, cantidad: 2}, ...]  (solo ids y cantidades, NUNCA precios)
function productosDelCarrito() {
    const lista = [];
    Object.keys(Carrito.leer()).forEach(function (id) {
        const cantidad = Carrito.cantidad(id);
        if (productosPorId[id] && cantidad > 0) {
            lista.push({ id: Number(id), cantidad: cantidad });
        }
    });
    return lista;
}

formPago.addEventListener('submit', async function (evento) {
    evento.preventDefault();
    ocultarErrorPago();

    if (!validarFormulario()) {
        return;
    }

    const pedido = {
        nombre:    campoNombre.value.trim(),
        apellido:  campoApellido.value.trim(),
        ciudad:    campoCiudad.value.trim(),
        direccion: campoDireccion.value.trim(),
        productos: productosDelCarrito()
        // la tarjeta, el titular, el vencimiento y el CVV NO se envían
    };

    btnCompletar.disabled = true;                    // evita enviar dos veces
    btnCompletar.textContent = 'Procesando...';

    try {
        const respuesta = await api('POST', 'pedidos.php', pedido);

        Carrito.guardar({});                         // el pedido ya existe: vaciamos el carrito
        formPago.reset();                            // borra también los datos de la tarjeta
        mostrarConfirmacion(respuesta.pedido);

    } catch (error) {
        mostrarErrorPago(error.message);

        // 409 = el stock o los productos cambiaron: refrescamos el carrito con los datos reales
        if (error.status === 409) {
            await cargarCarrito();                   // js/carrito-pagina.js
            if (calcularSubtotal().unidades === 0) {
                cerrarPago();
            } else {
                pintarResumenPago();
            }
        }
    }

    btnCompletar.disabled = false;
    btnCompletar.textContent = 'Completar compra';
});

// ---------------------------------------------------------------------
// Pantalla de confirmación (usa lo que respondió la API)
// ---------------------------------------------------------------------
const ESTADOS = {
    pendiente:  'Pendiente',
    confirmado: 'Confirmado',
    enviado:    'Enviado',
    entregado:  'Entregado',
    cancelado:  'Cancelado'
};

// "2026-10-24 02:15:00" -> "24 oct 2026"
const MESES = ['ene', 'feb', 'mar', 'abr', 'may', 'jun', 'jul', 'ago', 'sep', 'oct', 'nov', 'dic'];

function formatoFecha(texto) {
    const partes = String(texto).slice(0, 10).split('-');
    return Number(partes[2]) + ' ' + MESES[Number(partes[1]) - 1] + ' ' + partes[0];
}

function mostrarConfirmacion(pedido) {
    const entrega = pedido.entrega;

    document.getElementById('exitoGracias').textContent =
        'Gracias, ' + entrega.nombre + ' ' + entrega.apellido + '. Tu pedido fue registrado.';
    document.getElementById('exitoOrden').textContent  = '#' + pedido.numero;
    document.getElementById('exitoFecha').textContent  = formatoFecha(pedido.fecha);
    document.getElementById('exitoEstado').textContent = ESTADOS[pedido.estado] || pedido.estado;

    document.getElementById('exitoDestinatario').textContent = entrega.nombre + ' ' + entrega.apellido;
    document.getElementById('exitoDireccion').textContent    = entrega.direccion;
    document.getElementById('exitoCiudad').textContent       = entrega.ciudad + ', ' + entrega.pais;

    let html = '';
    pedido.productos.forEach(function (item) {
        let imagen = '';
        if (item.imagen_url) {
            imagen = '<img src="' + escapar(item.imagen_url) + '" alt="">';
        }
        html = html + `
            <li class="exito__item">
                <div class="exito__miniatura">${imagen}</div>
                <div>
                    <div class="exito__nombre">${escapar(item.nombre)}</div>
                    <div class="exito__cantidad">Cantidad: ${item.cantidad}</div>
                </div>
                <div class="exito__precio">${formatoPrecio(item.subtotal)}</div>
            </li>`;
    });
    document.getElementById('exitoProductos').innerHTML = html;

    document.getElementById('exitoSubtotal').textContent  = formatoPrecio(pedido.subtotal);
    document.getElementById('exitoDomicilio').textContent = formatoPrecio(pedido.domicilio);
    document.getElementById('exitoTotal').textContent     = formatoPrecio(pedido.total);

    pagoCuerpo.hidden = true;
    pagoExito.hidden = false;
    pagoCaja.classList.add('modal-pago__caja--estrecha');
    pagoCaja.scrollTop = 0;
}

document.getElementById('pagoExitoCerrar').addEventListener('click', function () {
    cerrarPago();
    ocultarAviso();
    dibujar();                          // muestra el carrito vacío
});
