<?php
// Panel del profesional: solo estructura. profesional-dashboard.js pide los datos a la API.
$rolPanel     = 'profesional';
$titulo       = 'Dashboard';
$activo       = 'dashboard';
$accionesHtml = '<a href="citas.php" class="btn-primary">Ver mis citas</a>';
require __DIR__ . '/../includes/panel_inicio.php';
?>
        <section class="stats">
            <article class="stat"><span>Pendientes</span><strong id="cntPendiente">0</strong></article>
            <article class="stat"><span>Confirmadas</span><strong id="cntConfirmada">0</strong></article>
            <article class="stat"><span>Finalizadas</span><strong id="cntFinalizada">0</strong></article>
            <article class="stat"><span>Total de citas</span><strong id="cntTotal">0</strong></article>
        </section>

        <section class="grid-2">
            <article class="card">
                <div class="card-head"><h2>Solicitudes pendientes</h2><a href="citas.php" class="link">Gestionar</a></div>
                <div class="tabla-wrap">
                    <table class="tabla">
                        <thead><tr><th>Cliente</th><th>Motivo</th><th>Fecha y hora</th></tr></thead>
                        <tbody id="tbodyPendientes"></tbody>
                    </table>
                </div>
            </article>

            <article class="card">
                <div class="card-head"><h2>Próximas citas</h2></div>
                <ul class="lista-citas" id="listaProximas"></ul>
            </article>
        </section>

<?php
$scripts = ['citas-comun.js', 'profesional-dashboard.js'];
require __DIR__ . '/../includes/panel_fin.php';
