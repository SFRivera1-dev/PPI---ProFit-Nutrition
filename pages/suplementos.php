<?php require_once __DIR__ . '/../includes/config.php'; ?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ProFit Nutrition</title>
    <link rel="stylesheet" href="../css/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.3.1/css/all.min.css">
    <link rel="stylesheet" href="../css/header.css">
    <link rel="stylesheet" href="../css/footer.css">
    <link rel="stylesheet" href="../css/suplementos.css">
    <link rel="icon" href="../src/image/p amarilla bg.png">
</head>

<body>

    <?php include '../includes/header.php'; ?>

    <main class="catalogo">

        <aside class="filtros">
            <h2 class="filtros__titulo">Categorias</h2>
            <ul class="filtros__lista" id="listaCategorias">
                <li><a href="#" class="filtros__link filtros__link--activo" data-categoria="">Todos</a></li>
            </ul>

            <h2 class="filtros__titulo filtros__titulo--precio">Precio</h2>
            <input type="range" class="rango" id="rangoPrecio" min="0" max="200000" step="1000" value="200000" aria-label="Precio maximo">
            <div class="rango__etiquetas">
                <span>$0</span>
                <span id="precioActual">$200.000</span>
            </div>
        </aside>

        <section class="productos">

            <div class="productos__barra">
                <p class="productos__resultados" id="resultados"></p>
                <select class="productos__orden" id="orden" aria-label="Ordenar productos">
                    <option value="relevantes">Mas relevantes</option>
                    <option value="menor">Menor precio</option>
                    <option value="mayor">Mayor precio</option>
                    <option value="nombre">Nombre A-Z</option>
                </select>
            </div>

            <!-- cada producto se dibuja en js/suplementos.js con los datos de get /api/productos.php -->
            <div class="grid" id="gridProductos"></div>

            <button class="ver-mas" aria-label="Ver mas productos" hidden>
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="12" y1="5" x2="12" y2="19"></line>
                    <polyline points="19 12 12 19 5 12"></polyline>
                </svg>
            </button>

        </section>

    </main>




    <!-- ventana modal de cada producto -->
    <div class="modal-producto" id="modalDetalle" hidden>
        <div class="modal-producto__caja" role="dialog" aria-modal="true" aria-labelledby="modalNombre">

            <button type="button" class="modal-producto__cerrar" id="modalCerrar" aria-label="Cerrar">
                <i class="fa-solid fa-xmark"></i>
            </button>

            <div class="modal-producto__imagen" id="modalImagen"></div>

            <div class="modal-producto__info">
                <span class="modal-producto__categoria" id="modalCategoria"></span>
                <h2 class="modal-producto__nombre" id="modalNombre"></h2>
                <p class="modal-producto__precio" id="modalPrecio"></p>
                <button type="button" class="modal-producto__agregar" id="modalAgregar">
                    <i class="fa-solid fa-cart-shopping"></i>
                    <span id="modalAgregarTexto">Agregar al carrito</span>
                </button>
            </div>

        </div>
    </div>

    <?php include '../includes/footer.php'; ?>

    <script src="../js/front-js/search-delete.js"></script>
    <script src="../js/front-js/buscador-header.js"></script>
    <script src="../js/front-js/menu-header.js"></script>
    <script src="../js/front-js/buscador-responsive.js"></script>
    <script src="../js/suplementos.js"></script>
</body>

</html>