<?php
// js/admin-foro.js habla con la API.
$rolPanel     = 'admin';
$titulo       = 'Foro';
$activo       = 'foro';
$seccion      = 'Panel';
$accionesHtml = '<button type="button" class="btn-primary" id="btnNueva">+ Nueva publicación</button>';
require __DIR__ . '/../includes/panel_inicio.php';
?>
        <div class="barra-productos">
            <label class="buscador-panel buscador-panel--ancho" aria-label="Buscar publicación">
                <i class="fa-solid fa-magnifying-glass"></i>
                <input type="search" id="buscarForo" placeholder="Buscar por título, contenido, autor o categoría..." autocomplete="off">
            </label>
        </div>

        <article class="card">
            <div class="tabla-wrap">
                <table class="tabla tabla-pedidos tabla-foro">
                    <thead>
                        <tr>
                            <th>Publicación</th>
                            <th>Autor</th>
                            <th>Tipo</th>
                            <th>Fecha</th>
                            <th class="der">Resp.</th>
                            <th>Estado</th>
                            <th class="der">Acción</th>
                        </tr>
                    </thead>
                    <tbody id="tbodyForo">
                        <tr><td colspan="7" class="vacio">Cargando publicaciones...</td></tr>
                    </tbody>
                </table>
            </div>
            <p class="tabla-pie" id="foroPie"></p>
        </article>

<?php require __DIR__ . '/../includes/foro_modal_nueva.php'; ?>
<?php
$scripts = ['foro-comun.js', 'foro-publicar.js', 'admin-foro.js'];
require __DIR__ . '/../includes/panel_fin.php';
