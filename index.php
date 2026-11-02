<?php require_once __DIR__ . '/includes/config.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ProFit Nutrition</title>
    <link rel="stylesheet" href="css/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.3.1/css/all.min.css">
    <link rel="stylesheet" href="css/header.css">
    <link rel="stylesheet" href="css/footer.css">
    <link rel="icon" href="src/image/p amarilla bg.png">
    <link rel="stylesheet" href="css/index.css">
</head>
<body>

    <?php include 'includes/header.php'; ?>
    
    <section class="info1">
        <div class="contenedor contenedor-informacion">
            <h3>NUTRICION A TU MEDIDA</h3>
            <h2>Encuentra el <br> suplemento que tu <br> cuerpo necesita</h2>
            <p>Agenda tu cita con nuestros especialistas y te podremos decir exactamente que necesitas segun tus objetivos</p>
            <div class="buttons">
                <a href="" class="agendar">Agendar cita</a>
                <a href="" class="suplementos">Ver suplementos</a>
            </div>
        </div>
        <div class="contenedor contenedor-cajas">

            <div class="caja">
                <i class="fa-solid fa-dumbbell"></i>
                <span>Ganancia muscular</span>
                <p>48 productos</p>
            </div>

            <div class="caja">
                <i class="fa-solid fa-fire"></i>
                <span>Quema de grasa</span>
                <p>32 productos</p>
            </div>

            <div class="caja">
                <i class="fa-regular fa-moon"></i>
                <span>Descanso y recuperacion</span>
                <p>15 productos</p>
            </div>

            <div class="caja">
                <i class="fa-solid fa-apple-whole"></i>
                <span>Nutricion diaria</span>
                <p>21 productos</p>
            </div>

        </div>
    </section>

    
    <?php include 'includes/footer.php'; ?>

    <script src="js/front-js/search-delete.js"></script>
    <script src="js/front-js/buscador-header.js"></script>
    <script src="js/front-js/menu-header.js"></script>
    <script src="js/front-js/buscador-responsive.js"></script>
</body>
</html>