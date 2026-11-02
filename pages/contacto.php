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
    <link rel="stylesheet" href="../css/contacto.css">
    <link rel="icon" href="../src/image/p amarilla bg.png">
</head>
<body>

    <?php include '../includes/header.php'; ?>

    <section>
        <div class="menu-informacion">
            <div class="informacion">
                <h3>CONTACTANOS</h3>
                <span>Hablemos</span>
                <p>Escribemos y te respondemos en menos de 24 horas</p>
            </div>
            <div class="datos">
                <div class="info-dato">
                    <i class="fa-regular fa-envelope"></i>
                    <p>feliperiverac02@gmail.com</p>
                </div>
                <div class="info-dato">
                    <i class="fa-solid fa-phone"></i>
                    <p>+57 322 367 87 19</p>
                </div>
                <div class="info-dato">
                    <i class="fa-solid fa-location-dot"></i>
                    <p>Medellin, Colombia</p>
                </div>
            </div>
        </div>
        <div class="menu-inputs">
            <form id="formContacto" novalidate>
                <div class="aviso-contacto" id="avisoContacto" role="alert" hidden></div>

                <div class="contenedor">

                    <div class="box-input">
                        <label for="cNombre">Nombre</label>
                        <div class="input-wrap">
                            <input type="text" id="cNombre" name="nombre" placeholder="Tu nombre" class="SearchInput" minlength="2" maxlength="100" autocomplete="name" required>
                            <button type="button" class="ButtonClear"><i class="fa-regular fa-circle-xmark" ></i></button>
                        </div>
                    </div>

                    <div class="box-input">
                        <label for="cCorreo">Correo</label>
                        <div class="input-wrap">
                            <input type="email" id="cCorreo" name="correo" placeholder="Tucorreo@ejemplo.com" class="SearchInput" maxlength="150" autocomplete="email" required>
                            <button type="button" class="ButtonClear"><i class="fa-regular fa-circle-xmark" ></i></button>
                        </div>
                    </div>

                </div>

                <div class="box-input">
                    <label for="cAsunto">Asunto</label>
                    <div class="input-wrap">
                        <input type="text" id="cAsunto" name="asunto" placeholder="¿En que te podemos ayudar?" class="SearchInput" minlength="3" maxlength="150" required>
                        <button class="ButtonClear" type="button"><i class="fa-regular fa-circle-xmark"></i></button>
                    </div>
                </div>

                <div class="box-input">
                    <label for="cMensaje">Mensaje</label>
                    <div class="input-wrap">
                        <textarea id="cMensaje" name="mensaje" minlength="10" maxlength="2000" required></textarea>
                    </div>
                </div>

                <!-- Campo trampa para bots: las personas no lo ven. NO lo borres. -->
                <div class="campo-trampa" aria-hidden="true">
                    <label for="cSitioWeb">No llenes este campo</label>
                    <input type="text" id="cSitioWeb" name="sitio_web" tabindex="-1" autocomplete="off">
                </div>

                <button class="submit-message" type="submit" id="btnEnviarContacto">Enviar mensaje</button>

            </form>

        </div>

    </section>

    <?php include '../includes/footer.php'; ?>

    <script src="../js/front-js/search-delete.js"></script>
    <script src="../js/front-js/buscador-header.js"></script>
    <script src="../js/front-js/menu-header.js"></script>
    <script src="../js/front-js/buscador-responsive.js"></script>
    <script src="../js/contacto.js"></script>
</body>
</html>