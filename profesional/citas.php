<?php
// Mis citas (profesional). Solo estructura: profesional-citas.js dibuja las filas con los datos de la API.
// El profesional ve el correo del cliente porque es el medio por el que lo contacta.
$rolPanel = 'profesional';
$titulo   = 'Mis citas';
$activo   = 'citas';
require __DIR__ . '/../includes/panel_inicio.php';
?>
        <article class="card" id="citasApp">
            <div class="tabs" id="citasTabs">
                <button type="button" class="tab active" data-estado="pendiente">Pendientes <span class="tab-count" data-count="pendiente">0</span></button>
                <button type="button" class="tab" data-estado="confirmada">Confirmadas <span class="tab-count" data-count="confirmada">0</span></button>
                <button type="button" class="tab" data-estado="finalizada">Finalizadas <span class="tab-count" data-count="finalizada">0</span></button>
            </div>

            <div class="tabla-wrap">
                <table class="tabla">
                    <thead><tr><th>Cliente</th><th>Motivo</th><th>Fecha y hora</th><th>Estado</th><th class="der">Acciones</th></tr></thead>
                    <tbody id="citasBody"></tbody>
                </table>
                <p class="vacio" id="citasVacio" hidden>No hay citas en este estado.</p>
            </div>
        </article>
<?php
$scripts = ['citas-comun.js', 'profesional-citas.js'];
require __DIR__ . '/../includes/panel_fin.php';
