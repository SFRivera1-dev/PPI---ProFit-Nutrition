<?php
// =====================================================================
// PLANTILLA DE PANEL — PARTE DE ABAJO
// Cierra lo que abrió panel_inicio.php y carga los JavaScript.
//
// La página puede pedir sus propios scripts antes de incluir este archivo:
//     $scripts = ['admin-usuarios.js'];
// =====================================================================
if (!isset($scripts)) {
    $scripts = [];
}
?>
    </main>
</div>
<script src="<?= BASE_URL ?>/js/api.js"></script>
<script src="<?= BASE_URL ?>/js/panel.js"></script>
<?php foreach ($scripts as $archivo): ?>
<script src="<?= BASE_URL ?>/js/<?= $archivo ?>"></script>
<?php endforeach; ?>
</body>
</html>
