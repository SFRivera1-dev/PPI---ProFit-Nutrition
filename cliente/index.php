<?php
require_once __DIR__ . '/../includes/config.php';
$b = BASE_URL;

$tarjetas = [
    ['titulo' => 'Tus pedidos',                  'texto' => 'Revisa el estado de tus compras y el detalle de cada pedido',  'url' => "$b/cliente/pedidos.php"],
    ['titulo' => 'Agendar una cita',             'texto' => 'Agenda una asesoría con un profesional de ProFit',           'url' => "$b/pages/agenda.php"],
    ['titulo' => 'Foro',                         'texto' => 'Participa y consulta publicaciones de la comunidad',         'url' => "$b/pages/foro.php"],
    ['titulo' => 'Servicio al cliente',          'texto' => 'Explora opciones de ayuda o contáctanos.',                    'url' => "$b/pages/contacto.php"],
];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tu cuenta | ProFit Nutrition</title>
    <link rel="stylesheet" href="../css/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.3.1/css/all.min.css">
    <link rel="stylesheet" href="../css/header.css">
    <link rel="stylesheet" href="../css/footer.css">
    <link rel="icon" href="../src/image/p amarilla bg.png">
    <link rel="stylesheet" href="../css/cliente.css">
</head>
<body class="cuenta-body">

    <?php include '../includes/header.php'; ?>

    <main class="cuenta">
        <div id="mensaje"></div>

        <div class="cuenta-top">
            <div>
                <h1>Tu cuenta</h1>
                <p>Hola, <strong id="cuentaNombre"></strong> · <span id="cuentaCorreo"></span></p>
            </div>
            <a href="#" id="btnSalir" class="cuenta-salir"><i class="fa-solid fa-right-from-bracket"></i> Cerrar sesión</a>
        </div>

        <section class="cuenta-grid">
            <?php foreach ($tarjetas as $t):
                // Una tarjeta normal es un enlace; una que dice "Pronto" es un div sin enlace
                $tag           = 'a';
                $attrs         = ' href="' . $t['url'] . '"';
                $claseCard     = '';
                $etiquetaPronto = '';
                if (!empty($t['pronto'])) {
                    $tag            = 'div';
                    $attrs          = '';
                    $claseCard      = 'pronto';
                    $etiquetaPronto = '<em>Pronto</em>';
                }
            ?>
                <<?= $tag ?><?= $attrs ?> class="cuenta-card <?= $claseCard ?>">
                    <span class="cuenta-info">
                        <h2><?= $t['titulo'] ?><?= $etiquetaPronto ?></h2>
                        <p><?= $t['texto'] ?></p>
                    </span>
                </<?= $tag ?>>
            <?php endforeach; ?>
        </section>

        <!-- Citas del cliente (las llena js/cliente-cuenta.js con GET /api/citas.php) -->
        <section class="cuenta-citas">
            <div class="cuenta-citas__top">
                <h2>Tus citas</h2>
                <a href="../pages/agenda.php" class="cuenta-citas__agendar" id="enlaceAgendar" hidden>+ Agendar cita</a>
            </div>

            <p class="cuenta-citas__vacio" id="citasCargando">Cargando tus citas...</p>

            <div class="cuenta-citas__vacio" id="citasVacio" hidden>
                <p>Aún no tienes citas agendadas.</p>
                <a href="../pages/agenda.php" class="cuenta-citas__boton">Agenda tu primera cita</a>
            </div>

            <ul class="cuenta-citas__lista" id="listaCitas" hidden></ul>
        </section>
    </main>

    <?php include '../includes/footer.php'; ?>

    <script src="../js/front-js/search-delete.js"></script>
    <script src="../js/front-js/buscador-header.js"></script>
    <script src="../js/front-js/menu-header.js"></script>
    <script src="../js/front-js/buscador-responsive.js"></script>
    <script src="../js/citas-comun.js"></script>
    <script src="../js/cliente-cuenta.js"></script>
</body>
</html>
