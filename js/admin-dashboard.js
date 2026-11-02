// =====================================================================
// admin/index.php  (dashboard del administrador)
// Pide a la API:  GET /api/estadisticas.php, GET /api/pedidos_admin.php?limite=5  y  GET /api/citas.php
// (citas-comun.js trae fechaCita() y horaCita())
// =====================================================================
if (USUARIO) {
    cargarDashboard();
}

async function cargarDashboard() {
    try {
        // Estadísticas reales (cuenta usuarios en MySQL)
        const stats = await api('GET', 'estadisticas.php');
        document.getElementById('statProfesionales').textContent = stats.profesionales_activos;
        document.getElementById('statClientes').textContent      = stats.clientes_activos.toLocaleString('es-CO');
        document.getElementById('statVentas').textContent        = '$' + stats.ventas_mes.toLocaleString('es-CO');
        document.getElementById('statPedidos').textContent       = stats.pedidos_nuevos.toLocaleString('es-CO');

        // Últimos pedidos (reales)
        const pedidos = await api('GET', 'pedidos_admin.php?limite=5');
        dibujarUltimosPedidos(pedidos.pedidos);

        // Próximas citas (reales): las que todavía no se finalizan, las más cercanas primero
        const datos    = await api('GET', 'citas.php');
        const proximas = datos.citas
            .filter(function (c) { return c.estado !== 'finalizada'; })
            .slice(0, 3);

        let lista = '';
        proximas.forEach(function (c) {
            lista = lista + `
                <li>
                    <div class="avatar">${escapar(c.cliente.nombre[0])}</div>
                    <div><strong>${escapar(c.cliente.nombre + ' ' + c.cliente.apellido)}</strong><span>${escapar(fechaCita(c.fecha) + ' · ' + horaCita(c.hora))}</span></div>
                </li>`;
        });
        if (lista === '') {
            lista = '<li><div><span>No hay citas próximas.</span></div></li>';
        }
        document.getElementById('listaProximas').innerHTML = lista;

    } catch (error) {
        mostrarMensaje(document.getElementById('mensaje'), 'error', error.message);
    }
}

// ---------------------------------------------------------------------
// Tabla "Últimos pedidos"
// ---------------------------------------------------------------------
const ESTADOS_PEDIDO = {
    pendiente:  { texto: 'Pendiente',  clase: 'warn' },
    confirmado: { texto: 'Confirmado', clase: 'warn' },
    enviado:    { texto: 'Enviado',    clase: 'info' },
    entregado:  { texto: 'Entregado',  clase: 'ok' },
    cancelado:  { texto: 'Cancelado',  clase: 'bad' }
};

function dibujarUltimosPedidos(pedidos) {
    const tbody = document.getElementById('tbodyUltimosPedidos');

    if (pedidos.length === 0) {
        tbody.innerHTML = '<tr><td colspan="5" class="vacio">Todavía no hay pedidos.</td></tr>';
        return;
    }

    let html = '';
    pedidos.forEach(function (p) {
        // Cliente: la cuenta si existe; si no, el nombre de la entrega
        let cliente = p.entrega.nombre + ' ' + p.entrega.apellido;
        if (p.cliente) {
            cliente = p.cliente.nombre + ' ' + p.cliente.apellido;
        }

        // Producto: el primero, y cuántos más hay
        let producto = '-';
        if (p.productos.length > 0) {
            producto = p.productos[0].nombre;
            if (p.productos.length > 1) {
                producto = producto + ' +' + (p.productos.length - 1) + ' más';
            }
        }

        let estado = { texto: p.estado, clase: 'warn' };
        if (ESTADOS_PEDIDO[p.estado]) {
            estado = ESTADOS_PEDIDO[p.estado];
        }

        html = html + `
            <tr>
                <td class="pedido-id">#${p.id}</td>
                <td>${escapar(cliente)}</td>
                <td>${escapar(producto)}</td>
                <td><span class="badge ${estado.clase}">${escapar(estado.texto)}</span></td>
                <td class="der">$${Number(p.total).toLocaleString('es-CO')}</td>
            </tr>`;
    });
    tbody.innerHTML = html;
}
