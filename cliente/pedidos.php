<?php require_once __DIR__ . '/../includes/config.php'; ?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mis pedidos | ProFit Nutrition</title>
    <link rel="stylesheet" href="../css/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.3.1/css/all.min.css">
    <link rel="stylesheet" href="../css/header.css">
    <link rel="stylesheet" href="../css/footer.css">
    <link rel="stylesheet" href="../css/cliente.css">
    <link rel="stylesheet" href="../css/pedidos.css">
    <link rel="icon" href="../src/image/p amarilla bg.png">
</head>
<body class="cuenta-body">

    <?php include '../includes/header.php'; ?>

    <main class="pedidos">

        <a href="index.php" class="pedidos__volver"><i class="fa-solid fa-arrow-left"></i> Mi cuenta</a>
        <h1 class="pedidos__titulo">Mis pedidos</h1>

        <div id="mensaje"></div>

        <p class="pedidos__cargando" id="pedidosCargando">Cargando tus pedidos...</p>

        <!-- Sin pedidos todavía -->
        <section class="pedidos__vacio" id="pedidosVacio" hidden>
            <i class="fa-solid fa-bag-shopping"></i>
            <h2>Aún no tienes pedidos</h2>
            <p>Cuando compres algo, aparecerá aquí con su estado y su detalle.</p>
            <a href="../pages/suplementos.php" class="pedidos__boton">Ver suplementos</a>
        </section>

        <!-- Lista (la llena js/cliente-pedidos.js) -->
        <ul class="pedidos__lista" id="listaPedidos" hidden></ul>

    </main>

    <?php include '../includes/footer.php'; ?>

    <script src="../js/front-js/search-delete.js"></script>
    <script src="../js/front-js/buscador-header.js"></script>
    <script src="../js/front-js/menu-header.js"></script>
    <script src="../js/front-js/buscador-responsive.js"></script>
    <script src="../js/cliente-pedidos.js"></script>
</body>
</html>
