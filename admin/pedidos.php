<?php
// Pedidos de todos los clientes js/admin-pedidos.js habla con la API.
$rolPanel = 'admin';
$titulo   = 'Pedidos';
$activo   = 'pedidos';
$seccion  = 'Panel';
require __DIR__ . '/../includes/panel_inicio.php';
?>
        <div class="barra-productos">
            <label class="buscador-panel buscador-panel--ancho" aria-label="Buscar pedido">
                <i class="fa-solid fa-magnifying-glass"></i>
                <input type="search" id="buscarPedido" placeholder="Buscar por número de pedido (ej: 20005) o por cliente..." autocomplete="off">
            </label>
        </div>

        <article class="card">
            <div class="tabla-wrap">
                <table class="tabla tabla-pedidos">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Cliente</th>
                            <th>Producto</th>
                            <th>Fecha</th>
                            <th>Estado</th>
                            <th class="der">Total</th>
                            <th class="col-flecha"><span class="solo-lectores">Ver</span></th>
                        </tr>
                    </thead>
                    <tbody id="tbodyPedidos">
                        <tr><td colspan="7" class="vacio">Cargando pedidos...</td></tr>
                    </tbody>
                </table>
            </div>
            <p class="tabla-pie" id="pedidosPie"></p>
        </article>

        <!-- Ventana del pedido (js/admin-pedidos.js la llena, la abre y la cierra) -->
        <div class="modal" id="modalPedido" hidden>
            <div class="modal-caja modal-caja--pedido" role="dialog" aria-modal="true" aria-labelledby="pedidoTitulo">
                <div class="card-head">
                    <h2 id="pedidoTitulo">Pedido</h2>
                    <button type="button" class="icon-btn" id="btnCerrarPedido" aria-label="Cerrar"><i class="fa-solid fa-xmark"></i></button>
                </div>

                <div id="mensajeModal"></div>

                <div class="pedido-info">
                    <div>
                        <h3>Cliente</h3>
                        <p class="pedido-info__fuerte" id="pedCliente"></p>
                        <p id="pedCorreo"></p>
                        <p id="pedFecha"></p>
                    </div>
                    <div>
                        <h3>Datos de entrega</h3>
                        <p class="pedido-info__fuerte" id="pedDestinatario"></p>
                        <p id="pedDireccion"></p>
                        <p id="pedCiudad"></p>
                    </div>
                </div>

                <h3 class="pedido-subtitulo">Productos del pedido</h3>
                <ul class="pedido-productos" id="pedProductos"></ul>

                <div class="pedido-totales">
                    <div><span>Productos</span><span id="pedSubtotal"></span></div>
                    <div><span>Domicilio</span><span id="pedDomicilio"></span></div>
                    <div class="pedido-totales__total"><span>Total</span><span id="pedTotal"></span></div>
                </div>

                <form id="formEstado" class="pedido-estado">
                    <label for="selectEstado">Estado del pedido</label>
                    <select id="selectEstado" class="select-panel">
                        <option value="confirmado">Confirmado</option>
                        <option value="enviado">Enviado</option>
                        <option value="entregado">Entregado</option>
                        <option value="cancelado">Cancelado</option>
                    </select>
                    <p class="pedido-estado__nota" id="notaEstado"></p>

                    <div class="modal-botones">
                        <button type="button" class="btn-secundario" id="btnCancelarPedido">Cerrar</button>
                        <button type="submit" class="btn-primary" id="btnGuardarEstado">Guardar estado</button>
                    </div>
                </form>
            </div>
        </div>
<?php
$scripts = ['admin-pedidos.js'];
require __DIR__ . '/../includes/panel_fin.php';
