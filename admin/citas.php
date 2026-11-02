<?php
// Citas de todos los clientes (solo admin). Solo estructura: js/admin-citas.js habla con la API.
$rolPanel = 'admin';
$titulo   = 'Citas';
$activo   = 'citas';
$seccion  = 'Panel';
require __DIR__ . '/../includes/panel_inicio.php';
?>
        <div class="barra-productos">
            <label class="buscador-panel buscador-panel--ancho" aria-label="Buscar cita">
                <i class="fa-solid fa-magnifying-glass"></i>
                <input type="search" id="buscarCita" placeholder="Buscar por cliente, correo, servicio o número de cita..." autocomplete="off">
            </label>
        </div>

        <article class="card">
            <div class="tabla-wrap">
                <table class="tabla tabla-pedidos">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Cliente</th>
                            <th>Servicio</th>
                            <th>Fecha y hora</th>
                            <th>Estado</th>
                            <th class="col-flecha"><span class="solo-lectores">Ver</span></th>
                        </tr>
                    </thead>
                    <tbody id="tbodyCitas">
                        <tr><td colspan="6" class="vacio">Cargando citas...</td></tr>
                    </tbody>
                </table>
            </div>
            <p class="tabla-pie" id="citasPie"></p>
        </article>

        <!-- Ventana de la cita (js/admin-citas.js la llena, la abre y la cierra) -->
        <div class="modal" id="modalCita" hidden>
            <div class="modal-caja modal-caja--pedido" role="dialog" aria-modal="true" aria-labelledby="citaTitulo">
                <div class="card-head">
                    <h2 id="citaTitulo">Cita</h2>
                    <button type="button" class="icon-btn" id="btnCerrarCita" aria-label="Cerrar"><i class="fa-solid fa-xmark"></i></button>
                </div>

                <div id="mensajeModal"></div>

                <div class="pedido-info">
                    <div>
                        <h3>Cliente</h3>
                        <p class="pedido-info__fuerte" id="citaCliente"></p>
                        <p id="citaCorreo"></p>
                        <p id="citaCreada"></p>
                    </div>
                    <div>
                        <h3>Cita</h3>
                        <p class="pedido-info__fuerte" id="citaFecha"></p>
                        <p id="citaServicio"></p>
                    </div>
                </div>

                <h3 class="pedido-subtitulo">Motivo de la consulta</h3>
                <p class="cita-detalle" id="citaObjetivo"></p>
                <p class="cita-detalle cita-detalle--suave" id="citaTiempo"></p>

                <form id="formEstadoCita" class="pedido-estado">
                    <label for="selectEstadoCita">Estado de la cita</label>
                    <select id="selectEstadoCita" class="select-panel">
                        <option value="pendiente">Pendiente</option>
                        <option value="confirmada">Confirmada</option>
                        <option value="finalizada">Finalizada</option>
                    </select>
                    <p class="pedido-estado__nota" id="notaEstadoCita"></p>

                    <div class="modal-botones modal-botones--entre">
                        <button type="button" class="btn-mini peligro" id="btnCancelarCita">Cancelar cita</button>
                        <span class="modal-botones__grupo">
                            <button type="submit" class="btn-primary" id="btnGuardarEstadoCita">Guardar estado</button>
                        </span>
                    </div>
                </form>
            </div>
        </div>
<?php
$scripts = ['citas-comun.js', 'admin-citas.js'];
require __DIR__ . '/../includes/panel_fin.php';
