// =====================================================================
// profesional/index.php   ->   GET /api/citas.php
// (api.js, panel.js y citas-comun.js ya están cargados)
// =====================================================================
if (USUARIO) {
    cargarDashboard();
}

async function cargarDashboard() {
    try {
        const datos = await api('GET', 'citas.php');
        const citas = datos.citas;

        // Contadores
        const contar = function (estado) {
            return citas.filter(function (c) { return c.estado === estado; }).length;
        };
        document.getElementById('cntPendiente').textContent  = contar('pendiente');
        document.getElementById('cntConfirmada').textContent = contar('confirmada');
        document.getElementById('cntFinalizada').textContent = contar('finalizada');
        document.getElementById('cntTotal').textContent      = citas.length;

        // Solicitudes pendientes (las más próximas primero: la API ya las trae ordenadas)
        const pendientes = citas.filter(function (c) { return c.estado === 'pendiente'; });
        let filas = '';
        pendientes.slice(0, 5).forEach(function (c) {
            filas = filas + '<tr><td>' + escapar(c.cliente.nombre + ' ' + c.cliente.apellido) + '</td><td>' +
                escapar(motivoCita(c)) + '</td><td>' + escapar(fechaCita(c.fecha) + ' · ' + horaCita(c.hora)) + '</td></tr>';
        });
        if (filas === '') {
            filas = '<tr><td colspan="3" class="vacio">No hay solicitudes pendientes.</td></tr>';
        }
        document.getElementById('tbodyPendientes').innerHTML = filas;

        // Próximas citas: las que todavía no se finalizan
        const proximas = citas.filter(function (c) { return c.estado !== 'finalizada'; }).slice(0, 4);
        let lista = '';
        proximas.forEach(function (c) {
            lista = lista + `
                <li>
                    <div class="avatar">${escapar(c.cliente.nombre[0])}</div>
                    <div><strong>${escapar(c.cliente.nombre + ' ' + c.cliente.apellido)}</strong><span>${escapar(fechaCita(c.fecha) + ' · ' + horaCita(c.hora) + ' · ' + textoEstadoCita(c.estado))}</span></div>
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
