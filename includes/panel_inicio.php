<?php
// =====================================================================
// PLANTILLA DE PANEL — PARTE DE ARRIBA (barra lateral + encabezado)
// La usan el panel del admin y el del profesional.
//
// Cada página define estas variables y luego hace include de este archivo:
//     $rolPanel     -> 'admin' o 'profesional'        (decide qué menú se dibuja)
//     $titulo       -> título de la página            (ej: 'Dashboard')
//     $activo       -> id del ítem del menú resaltado (ej: 'dashboard')
//     $accionesHtml -> (opcional) botón arriba a la derecha
//
// PHP ya NO sabe quién eres: el nombre y el rol los rellena js/panel.js
// con los datos del usuario que guardó el login.
// El contenido propio de la página va después, y se cierra con panel_fin.php
// =====================================================================

require_once __DIR__ . '/config.php';
$b = BASE_URL;
if (!isset($seccion)) {
    $seccion = 'Panel';
}
if (!isset($accionesHtml)) {
    $accionesHtml = '';
}

// El saludo ("Buenos días, ...") solo se pone en el título del Dashboard
$saludo = '';
if ($titulo === 'Dashboard') {
    $saludo = '1';
}

// Menú lateral de cada rol. 'pronto' => la sección todavía no existe.
$menus = [
    'admin' => [
        ['id' => 'dashboard', 'texto' => 'Dashboard',     'icono' => 'fa-table-cells-large', 'url' => "$b/admin/index.php"],
        ['id' => 'productos', 'texto' => 'Productos',     'icono' => 'fa-box',               'url' => "$b/admin/productos.php"],
        ['id' => 'pedidos',   'texto' => 'Pedidos',       'icono' => 'fa-bag-shopping',      'url' => "$b/admin/pedidos.php"],
        ['id' => 'usuarios',  'texto' => 'Usuarios',      'icono' => 'fa-user-group',        'url' => "$b/admin/usuarios.php"],
        ['id' => 'citas',     'texto' => 'Citas',         'icono' => 'fa-calendar-check',    'url' => "$b/admin/citas.php"],
        ['id' => 'foro',      'texto' => 'Foro',          'icono' => 'fa-comment',           'url' => "$b/admin/foro.php"],
        ['id' => 'contacto',  'texto' => 'Contáctanos',   'icono' => 'fa-envelope',          'url' => "$b/admin/contacto.php"],
    ],
    'profesional' => [
        ['id' => 'dashboard', 'texto' => 'Dashboard',     'icono' => 'fa-table-cells-large', 'url' => "$b/profesional/index.php"],
        ['id' => 'citas',     'texto' => 'Mis citas',     'icono' => 'fa-calendar-check',    'url' => "$b/profesional/citas.php"],
        ['id' => 'pacientes', 'texto' => 'Pacientes',     'icono' => 'fa-user-group',        'url' => "$b/profesional/pacientes.php"],
        ['id' => 'foro',      'texto' => 'Foro',          'icono' => 'fa-comment',           'url' => "$b/profesional/foro.php"],
    ],
];
$menu = [];
if (isset($menus[$rolPanel])) {
    $menu = $menus[$rolPanel];
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $titulo ?> | ProFit Nutrition</title>
    <link rel="stylesheet" href="<?= $b ?>/css/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.3.1/css/all.min.css">
    <link rel="stylesheet" href="<?= $b ?>/css/panel.css">
    <link rel="icon" href="<?= $b ?>/src/image/p amarilla bg.png">
</head>
<body class="panel-body" data-rol="<?= $rolPanel ?>">
<div class="panel">

    <div class="panel-overlay" id="panelOverlay"></div>

    <aside class="sidebar" id="sidebar">
        <div class="sidebar-top">
            <a href="" class="sidebar-logo"><img src="../src/image/logo amarillo bg.png" alt="ProFit"></a>
            <nav class="sidebar-nav">
                <?php foreach ($menu as $item): ?>
                    <?php if (!empty($item['pronto'])): ?>
                        <span class="nav-item nav-pronto" title="Disponible próximamente">
                            <i class="fa-solid <?= $item['icono'] ?>"></i><?= $item['texto'] ?><em>Pronto</em>
                        </span>
                    <?php else: ?>
                        <?php
                        $claseItem = 'nav-item';
                        if ($item['id'] === $activo) {
                            $claseItem = 'nav-item active';
                        }
                        ?>
                        <a href="<?= $item['url'] ?>" class="<?= $claseItem ?>">
                            <i class="fa-solid <?= $item['icono'] ?>"></i><?= $item['texto'] ?>
                        </a>
                    <?php endif; ?>
                <?php endforeach; ?>
            </nav>
        </div>

        <div class="sidebar-user">
            <div class="sidebar-user-info">
                <strong id="panelNombre"></strong>
                <span id="panelRol"></span>
            </div>
            <a href="#" class="logout" id="btnSalir" title="Cerrar sesión"><i class="fa-solid fa-right-from-bracket"></i></a>
        </div>
    </aside>

    <main class="panel-main">
        <header class="panel-top">
            <button class="menu-toggle" id="menuToggle" aria-label="Abrir menú"><i class="fa-solid fa-bars"></i></button>
            <div class="panel-titulo">
                <small><?= $seccion ?> / <?= $titulo ?></small>
                <h1 id="panelTitulo" data-saludo="<?= $saludo ?>"><?= $titulo ?></h1>
            </div>
            <div class="panel-acciones">
                <?= $accionesHtml ?>
            </div>
        </header>

        <div id="mensaje"></div>
