<?php
// Foro del profesional: publicar como oficial y ver las publicaciones oficiales.
// Solo estructura: js/profesional-foro.js habla con la API.
$rolPanel     = 'profesional';
$titulo       = 'Foro';
$activo       = 'foro';
$accionesHtml = '<button type="button" class="btn-primary" id="btnNueva">+ Nueva publicación</button>';
require __DIR__ . '/../includes/panel_inicio.php';
?>
        <article class="card">
            <div class="card-head"><h2>Publicaciones oficiales</h2><a href="../pages/foro.php" class="link">Ir al foro</a></div>
            <div class="tabla-wrap">
                <table class="tabla tabla-pedidos tabla-foro">
                    <thead>
                        <tr>
                            <th>Publicación</th>
                            <th>Autor</th>
                            <th>Fecha</th>
                            <th class="der">Respuestas</th>
                        </tr>
                    </thead>
                    <tbody id="tbodyForo">
                        <tr><td colspan="4" class="vacio">Cargando publicaciones...</td></tr>
                    </tbody>
                </table>
            </div>
        </article>

<?php require __DIR__ . '/../includes/foro_modal_nueva.php'; ?>
<?php
$scripts = ['foro-comun.js', 'foro-publicar.js', 'profesional-foro.js'];
require __DIR__ . '/../includes/panel_fin.php';
