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
    <link rel="stylesheet" href="../css/foro.css">
    <link rel="icon" href="../src/image/p amarilla bg.png">
</head>

<body>

    <?php include '../includes/header.php'; ?>

    <main class="foro">

        <!-- Encabezado -->
        <header class="foro__cabecera">
            <span class="foro__etiqueta">Comunidad</span>
            <div class="foro__fila">
                <h1 class="foro__titulo">Foro ProFit</h1>
                <button type="button" class="btn-crear" id="btnCrear">Crear publicacion</button>
            </div>
        </header>

        <!-- Publicaciones oficiales (admin y profesionales). js/foro.js las dibuja con los datos de la API -->
        <section class="seccion">
            <h2 class="seccion__titulo">Publicaciones oficiales</h2>
            <div class="oficiales" id="listaOficiales">
                <p class="foro__vacio">Cargando publicaciones</p>
            </div>
        </section>

        <!-- Publicaciones de la comunidad (clientes) -->
        <section class="seccion">
            <h2 class="seccion__titulo">Publicaciones de la comunidad</h2>
            <div class="comunidad" id="listaComunidad">
                <p class="foro__vacio">Cargando publicaciones</p>
            </div>
        </section>

    </main>

    <!-- Ventana: crear publicacion (js/foro.js la abre y la cierra) -->
    <div class="modal" id="modalCrear">

        <div class="modal__fondo" data-cerrar></div>

        <div class="modal__caja" role="dialog" aria-modal="true" aria-labelledby="modal-titulo">

            <div class="modal__cabecera">
                <h2 id="modal-titulo">Crear publicacion</h2>
                <button type="button" class="modal__cerrar" data-cerrar aria-label="Cerrar">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <p class="modal__descripcion" id="crearDescripcion">Comparte tu duda o experiencia con la comunidad</p>

            <div class="foro__alerta" id="mensajeCrear" role="alert" hidden></div>

            <form class="modal__form" id="formCrear">

                <label for="pub-categoria">Categoria</label>
                <select name="categoria" id="pub-categoria" class="modal__campo" required>
                    <option value="" disabled selected>Selecciona una categoria</option>
                    <option value="Entrenamiento">Entrenamiento</option>
                    <option value="Nutricion">Nutricion</option>
                    <option value="Suplementos">Suplementos</option>
                    <option value="Motivacion">Motivacion</option>
                </select>

                <label for="pub-titulo">Titulo</label>
                <input type="text" name="titulo" id="pub-titulo" class="modal__campo" placeholder="¿Sobre qué quieres hablar?" minlength="5" maxlength="100" required>

                <label for="pub-contenido">Contenido</label>
                <textarea name="contenido" id="pub-contenido" class="modal__campo modal__campo--texto" placeholder="Escribe aquí tu publicación..." minlength="10" maxlength="1000" required></textarea>

                <div class="modal__botones">
                    <button type="button" class="modal__cancelar" data-cerrar>Cancelar</button>
                    <button type="submit" class="modal__publicar" id="btnPublicar">Publicar</button>
                </div>

            </form>

        </div>
    </div>

    <!-- Ventana: ver una publicacion y sus respuestas -->
    <div class="modal" id="modalVer">

        <div class="modal__fondo" data-cerrar></div>

        <div class="modal__caja modal__caja--ancha" role="dialog" aria-modal="true" aria-labelledby="verTitulo">

            <div class="modal__cabecera">
                <span class="ver__categoria" id="verCategoria"></span>
                <button type="button" class="modal__cerrar" data-cerrar aria-label="Cerrar">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <div class="foro__alerta" id="mensajeVer" role="alert" hidden></div>

            <article id="verPublicacion" class="ver">
                <div class="ver__autor">
                    <div class="avatar avatar--chico" id="verAvatar">ola</div>
                    <div class="oficial__autor">
                        <strong id="verAutor"></strong>
                        <span id="verMeta"></span>
                    </div>
                    <span class="insignia" id="verInsignia" hidden>Oficial</span>
                </div>
                <h2 class="ver__titulo" id="verTitulo"></h2>
                <p class="ver__texto" id="verTexto"></p>
            </article>

            <h3 class="ver__subtitulo" id="verConteo">Respuestas</h3>
            <ul class="respuestas" id="listaRespuestas"></ul>

            <form class="responder" id="formResponder" hidden>
                <label for="resp-contenido" class="solo-lectores">Tu respuesta</label>
                <textarea id="resp-contenido" class="modal__campo modal__campo--respuesta" placeholder="Escribe tu respuesta..." minlength="2" maxlength="500" required></textarea>
                <div class="modal__botones">
                    <button type="submit" class="modal__publicar" id="btnResponder">Responder</button>
                </div>
            </form>
            <p class="responder__aviso" id="avisoSesion" hidden>
                <a href="../ingresar.php">Inicia sesion</a> para responder a esta publicacion
            </p>
        </div>
    </div>

    <?php include '../includes/footer.php'; ?>

    <script src="../js/front-js/search-delete.js"></script>
    <script src="../js/front-js/buscador-header.js"></script>
    <script src="../js/front-js/menu-header.js"></script>
    <script src="../js/front-js/buscador-responsive.js"></script>
    <script src="../js/foro-comun.js"></script>
    <script src="../js/foro.js"></script>
</body>

</html>