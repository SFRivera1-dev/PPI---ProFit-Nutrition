<?php
// Mensajes del formulario "Contáctanos" (solo admin). Solo estructura: js/admin-contacto.js habla con la API.
$rolPanel = 'admin';
$titulo   = 'Contáctanos';
$activo   = 'contacto';
$seccion  = 'Panel';
require __DIR__ . '/../includes/panel_inicio.php';
?>
        <div class="barra-productos">
            <label class="buscador-panel buscador-panel--ancho" aria-label="Buscar mensaje">
                <i class="fa-solid fa-magnifying-glass"></i>
                <input type="search" id="buscarContacto" placeholder="Buscar por nombre, correo, asunto o mensaje..." autocomplete="off">
            </label>
        </div>

        <article class="card">
            <div class="tabla-wrap">
                <table class="tabla tabla-pedidos">
                    <thead>
                        <tr>
                            <th>Remitente</th>
                            <th>Asunto</th>
                            <th>Fecha</th>
                            <th>Estado</th>
                            <th class="col-flecha"><span class="solo-lectores">Ver</span></th>
                        </tr>
                    </thead>
                    <tbody id="tbodyContactos">
                        <tr><td colspan="5" class="vacio">Cargando mensajes...</td></tr>
                    </tbody>
                </table>
            </div>
            <p class="tabla-pie" id="contactoPie"></p>
        </article>

        <!-- Ventana modal del mensaje (js/admin-contacto.js la llena, la abre y la cierra) -->
        <div class="modal" id="modalContacto" hidden>
            <div class="modal-caja modal-caja--pedido" role="dialog" aria-modal="true" aria-labelledby="contactoTitulo">
                <div class="card-head">
                    <h2 id="contactoTitulo">Mensaje</h2>
                    <button type="button" class="icon-btn" id="btnCerrarContacto" aria-label="Cerrar"><i class="fa-solid fa-xmark"></i></button>
                </div>

                <div id="mensajeModal"></div>

                <div class="pedido-info">
                    <div>
                        <h3>Remitente</h3>
                        <p class="pedido-info__fuerte" id="ctoNombre"></p>
                        <p id="ctoCorreo"></p>
                    </div>
                    <div>
                        <h3>Recibido</h3>
                        <p class="pedido-info__fuerte" id="ctoFecha"></p>
                        <p id="ctoEstado"></p>
                    </div>
                </div>

                <h3 class="pedido-subtitulo">Asunto</h3>
                <p class="cita-detalle" id="ctoAsunto"></p>

                <h3 class="pedido-subtitulo">Mensaje</h3>
                <p class="contacto-texto" id="ctoMensaje"></p>

                <div class="modal-botones modal-botones--entre">
                    <button type="button" class="btn-mini" id="btnAlternarLeido">Marcar como no leído</button>
                    <span class="modal-botones__grupo">
                        <button type="button" class="btn-secundario" id="btnCerrarContacto2">Cerrar</button>
                        <a class="btn-primary" id="btnResponderCorreo" href="#"><i class="fa-regular fa-envelope"></i> Responder por correo</a>
                    </span>
                </div>
            </div>
        </div>
<?php
$scripts = ['foro-comun.js', 'admin-contacto.js'];
require __DIR__ . '/../includes/panel_fin.php';
