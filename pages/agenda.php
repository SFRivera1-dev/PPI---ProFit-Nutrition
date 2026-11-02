<?php require_once __DIR__ . '/../includes/config.php'; ?>
<!DOCTYPE html>

<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agenda tu cita | ProFit Nutrition</title>
    <link rel="stylesheet" href="../css/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.3.1/css/all.min.css">
    <link rel="stylesheet" href="../css/header.css">
    <link rel="stylesheet" href="../css/footer.css">
    <link rel="icon" href="../src/image/p amarilla bg.png">
    <link rel="stylesheet" href="../css/agenda.css">
</head>

<body>

    <?php include '../includes/header.php'; ?>

    <main class="agenda-section">

        <article>
            <h2>Agenda tu cita para conocer más sobre tu salud</h2>
            <p>Elige el servicio, la fecha y el entorno</p>
        </article>

        <!-- Aviso cuando el cliente ya tiene una cita pendiente o confirmada (lo llena js/agenda.js) -->
        <div class="agenda-aviso" id="avisoCitaActiva" hidden>
            <div>
                <strong>Ya tienes una cita agendada</strong>
                <p id="avisoCitaTexto"></p>
                <p>Podrás agendar otra cuando esta quede finalizada</p>
                <a href="../cliente/index.php" class="agenda-aviso__enlace">Ver mis citas</a>
            </div>
        </div>

        <!-- Errores (hora ocupada, datos incorrectos...) -->
        <div class="agenda-alerta" id="mensajeCita" role="alert" hidden></div>

        <section>
            <form id="formCita">

                <div class="contenedores-menu">

                    <span>1. Tipo de servicio</span>

                    <div class="tipos-servicio">

                        <input type="radio" name="servicio" id="nutricion" value="Nutricion" required>
                        <label for="nutricion" class="servicio">
                            <i class="fa-solid fa-egg"></i>
                            Nutrición
                        </label>

                        <input type="radio" name="servicio" id="fisico" value="Fisico">
                        <label for="fisico" class="servicio">
                           <i class="fa-solid fa-dumbbell"></i>
                            Físico
                        </label>

                        <input type="radio" name="servicio" id="deporte" value="Deporte">
                        <label for="deporte" class="servicio">
                            <i class="fa-solid fa-futbol"></i>
                            Deporte
                        </label>

                        <input type="radio" name="servicio" id="seguimiento" value="Seguimiento">
                        <label for="seguimiento" class="servicio">
                            <i class="fa-solid fa-stethoscope"></i>
                            Seguimiento
                        </label>

                        <input type="radio" name="servicio" id="general" value="General">
                        <label for="general" class="servicio">
                            <i class="fa-solid fa-gears"></i>
                            General
                        </label>

                    </div>

                    <p class="info-servicios">
                        Si no encuentras el servicio adecuado pon "General"
                    </p>

                    <span>2. Datos personales</span>

                    <input type="text" name="objetivo" placeholder="¿Cuál es tu objetivo?" class="objetivo-datos" maxlength="200" required>

                    <input type="text" name="tiempo" placeholder="¿Cuanto tiempo llevas?" class="nivel-datos" maxlength="100" required>

                </div>

                <div class="contenedores-menu">

                    <span>3. Fecha y hora</span>

                    <input type="date" name="fecha" id="fechaCita" class="fecha-cita" required>

                    <select name="hora" id="horaCita" class="hora-cita" required>
                        <option value="" disabled selected>Selecciona una hora</option>
                        <option value="08:00">8:00 am</option>
                        <option value="09:00">9:00 am</option>
                        <option value="10:00">10:00 am</option>
                        <option value="11:00">11:00 am</option>
                        <option value="14:00">2:00 pm</option>
                        <option value="15:00">3:00 pm</option>
                        <option value="16:00">4:00 pm</option>
                    </select>

                    <span>4. Entorno</span>

                    <input type="text" value="Asesoría virtual - ProFit Nutrition" readonly class="entorno">

                    <p class="agenda-nota" id="notaHoras"></p>

                    <br>

                    <button class="confirmar-cita" type="submit" id="btnConfirmarCita">
                        Confirmar cita
                    </button>

                </div>

            </form>
        </section>

    </main>

    <!-- Ventana que aparece cuando la cita se guardó (js/agenda.js la abre) -->
    <div class="agenda-modal" id="modalCita" hidden>
        <div class="agenda-modal__caja" role="dialog" aria-modal="true" aria-labelledby="modalCitaTitulo">
            <div class="agenda-modal__icono"><i class="fa-solid fa-check"></i></div>
            <h2 id="modalCitaTitulo">Tu cita está pendiente</h2>
            <p class="agenda-modal__resumen" id="modalCitaResumen"></p>
            <p>Tu asesor te contactará muy pronto vía correo electrónico.</p>
            <p class="agenda-modal__gracias">¡Muchas gracias por todo!</p>
            <div class="agenda-modal__botones">
                <a href="../cliente/index.php" class="agenda-modal__secundario">Ver mis citas</a>
                <button type="button" class="agenda-modal__principal" id="modalCitaCerrar">Entendido</button>
            </div>
        </div>
    </div>

    <?php include '../includes/footer.php'; ?>

    <script src="../js/front-js/search-delete.js"></script>
    <script src="../js/front-js/buscador-header.js"></script>
    <script src="../js/front-js/menu-header.js"></script>
    <script src="../js/front-js/buscador-responsive.js"></script>
    <script src="../js/citas-comun.js"></script>
    <script src="../js/agenda.js"></script>
</body>
</html>
