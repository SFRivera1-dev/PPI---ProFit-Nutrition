<?php
// Pacientes (profesional): los clientes que han tenido alguna cita.
// Solo estructura: js/profesional-pacientes.js habla con la API.
$rolPanel = 'profesional';
$titulo   = 'Pacientes';
$activo   = 'pacientes';
require __DIR__ . '/../includes/panel_inicio.php';
?>
        <div class="barra-productos">
            <label class="buscador-panel buscador-panel--ancho" aria-label="Buscar paciente">
                <i class="fa-solid fa-magnifying-glass"></i>
                <input type="search" id="buscarPaciente" placeholder="Buscar paciente por nombre o correo..." autocomplete="off">
            </label>
        </div>

        <article class="card">
            <div class="tabla-wrap">
                <table class="tabla tabla-pedidos">
                    <thead>
                        <tr>
                            <th>Paciente</th>
                            <th>Citas</th>
                            <th>Última cita</th>
                            <th>Situación</th>
                            <th class="col-flecha"><span class="solo-lectores">Ver historial</span></th>
                        </tr>
                    </thead>
                    <tbody id="tbodyPacientes">
                        <tr><td colspan="5" class="vacio">Cargando pacientes...</td></tr>
                    </tbody>
                </table>
            </div>
            <p class="tabla-pie" id="pacientesPie"></p>
        </article>

        <!-- Historial del paciente (js/profesional-pacientes.js la llena, la abre y la cierra) -->
        <div class="modal" id="modalPaciente" hidden>
            <div class="modal-caja modal-caja--pedido" role="dialog" aria-modal="true" aria-labelledby="pacienteTitulo">
                <div class="card-head">
                    <h2 id="pacienteTitulo">Ficha del paciente</h2>
                    <button type="button" class="icon-btn" id="btnCerrarPaciente" aria-label="Cerrar"><i class="fa-solid fa-xmark"></i></button>
                </div>

                <div id="mensajeModal"></div>

                <p class="pedido-info__fuerte" id="pacNombre"></p>
                <p class="cita-detalle cita-detalle--suave" id="pacCorreo"></p>
                <p class="cita-detalle cita-detalle--suave" id="pacResumen"></p>

                <h3 class="pedido-subtitulo">Historial de citas</h3>
                <ul class="historial-citas" id="pacHistorial"></ul>

                <div class="modal-botones">
                    <button type="button" class="btn-secundario" id="btnCerrarPaciente2">Cerrar</button>
                </div>
            </div>
        </div>
<?php
$scripts = ['citas-comun.js', 'profesional-pacientes.js'];
require __DIR__ . '/../includes/panel_fin.php';
