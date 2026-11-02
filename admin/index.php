<?php
$rolPanel     = 'admin';
$titulo       = 'Dashboard';
$activo       = 'dashboard';
require __DIR__ . '/../includes/panel_inicio.php';
?>

        <section class="stats">
            <article class="stat"><span>Ventas del mes</span><strong id="statVentas">...</strong></article>
            <article class="stat"><span>Pedidos nuevos</span><strong id="statPedidos">...</strong></article>
            <article class="stat"><span>Profesionales activos</span><strong id="statProfesionales">...</strong></article>
            <article class="stat"><span>Clientes activos</span><strong id="statClientes">...</strong></article>
        </section>

        <section class="grid-2">
            <article class="card">
                <div class="card-head"><h2>Últimos pedidos</h2><a href="pedidos.php" class="link">Ver todos</a></div>
                <div class="tabla-wrap">
                    <table class="tabla">
                        <thead><tr><th>Pedido</th><th>Cliente</th><th>Producto</th><th>Estado</th><th class="der">Total</th></tr></thead>
                        <tbody id="tbodyUltimosPedidos">
                            <tr><td colspan="5" class="vacio">Cargando pedidos...</td></tr>
                        </tbody>
                    </table>
                </div>
            </article>

            <article class="card">
                <div class="card-head"><h2>Próximas citas</h2><a href="citas.php" class="link">Gestionar</a></div>
                <ul class="lista-citas" id="listaProximas"></ul>
            </article>
        </section>


<?php
$scripts = ['citas-comun.js', 'admin-dashboard.js'];
require __DIR__ . '/../includes/panel_fin.php';
