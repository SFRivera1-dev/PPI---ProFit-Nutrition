<?php 
require_once __DIR__ . '/config.php';   // para tener BASE_URL
$paginaActual = basename($_SERVER['PHP_SELF']);

// Clase de cada enlace del menú: solo el de la página donde estás lleva
// "active" (menú de escritorio) o "activo" (menú del celular)
$claseEscritorio = [];
$claseMovil      = [];
foreach (['index.php', 'suplementos.php', 'agenda.php', 'foro.php', 'contacto.php'] as $archivo) {
    $claseEscritorio[$archivo] = '';
    $claseMovil[$archivo]      = '';
    if ($paginaActual == $archivo) {
        $claseEscritorio[$archivo] = 'active';
        $claseMovil[$archivo]      = 'activo';
    }
}
?>

<header>
        <div class="header-sup">
            <button class="button-menu-lateral" id="abrirMenu"><i class="fa-solid fa-bars"></i></button>
            <a href=""><img src="<?= BASE_URL ?>/src/image/logo amarillo bg.png" alt=""></a>
            <div class="search">
                <form action="">
                    <span><i class="fa-brands fa-sistrix"></i></span>
                    <input type="text" class="SearchInput" placeholder="Buscar proteinas, creatinas...">
                    <button type="button" class="ButtonClear"><i class="fa-regular fa-circle-xmark"></i></button>

                </form>
            </div>
            <div class="buttons">
                <button id="buscar-responsive" class="search-responsive"><i class="fa-brands fa-sistrix"></i></button>
                <a href="<?= BASE_URL ?>/pages/carrito.php" class="cart-shopping" aria-label="Mi carrito"><i class="fa-solid fa-cart-shopping"></i><span>0</span></a>
                <a href="<?= BASE_URL ?>/ingresar.php" class="ingresar">Iniciar sesion</a>
            </div>
        </div>

        <nav>
            <a href="<?= BASE_URL ?>/index.php" class="<?= $claseEscritorio['index.php'] ?>">
            Inicio
            </a>
            <a href="<?= BASE_URL ?>/pages/suplementos.php" class="<?= $claseEscritorio['suplementos.php'] ?>">
                Suplementos
            </a>
            <a href="<?= BASE_URL ?>/pages/agenda.php" class="<?= $claseEscritorio['agenda.php'] ?>">
                <i class="fa-regular fa-calendar"></i>
                Agenda tu cita
            </a>

            <a href="<?= BASE_URL ?>/pages/foro.php" class="<?= $claseEscritorio['foro.php'] ?>">
                <i class="fa-regular fa-comment"></i>
                Foro
            </a>

            <a href="<?= BASE_URL ?>/pages/contacto.php" class="<?= $claseEscritorio['contacto.php'] ?>">
                Contáctanos
            </a>

        </nav>

        <div class="overlay" id="overlay"></div>

        <div class="menu-lateral" id="menuLateral">
            <div class="menu-superior">
                <a href=""><img src="<?= BASE_URL ?>/src/image/logo amarillo bg.png" alt=""></a>           
                <button id="menuCerrar"><i class="fa-solid fa-xmark"></i></button>
            </div>
            <div class="links-menu">
                <a href="<?= BASE_URL ?>/index.php" class="<?= $claseMovil['index.php'] ?>">Inicio<span>></span></a>
                <a href="<?= BASE_URL ?>/pages/suplementos.php" class="<?= $claseMovil['suplementos.php'] ?>">Suplementos<span>></span></a>
                <a href="<?= BASE_URL ?>/pages/agenda.php" class="<?= $claseMovil['agenda.php'] ?>"><span><i class="fa-regular fa-calendar"></i> Agenda tu cita</span><span>></span></a>
                <a href="<?= BASE_URL ?>/pages/foro.php" class="<?= $claseMovil['foro.php'] ?>"><span><i class="fa-regular fa-comment"></i> Foro</span><span>></span></a>
                <a href="<?= BASE_URL ?>/pages/contacto.php" class="<?= $claseMovil['contacto.php'] ?>">Contactanos<span>></span></a>
            </div>
            <div class="iniciar-sesion-menu">
                <a href="<?= BASE_URL ?>/ingresar.php">Iniciar sesion</a>
            </div>
        </div>

        <div class="buscador-responsive" id="BuscadorResponsive">
            <div class="leave-buscador">
                <button id="CerrarModal"><i class="fa-solid fa-xmark"></i></button>
            </div>
            <div class="buscador">
                <form action="">
                    <span><i class="fa-brands fa-sistrix"></i></span>
                    <input type="text" class="SearchInput" placeholder="Buscar proteinas, creatinas...">
                    <button type="button" class="ButtonClear"><i class="fa-regular fa-circle-xmark"></i></button>
                </form>
            </div>
        </div>
    </header>

<!-- Cuando el usuario ya inició sesión, este script cambia "Iniciar sesion" por "Mi panel" -->
<script src="<?= BASE_URL ?>/js/api.js"></script>
<script src="<?= BASE_URL ?>/js/header-sesion.js"></script>
<script src="<?= BASE_URL ?>/js/carrito.js"></script>
