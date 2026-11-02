<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ProFit Nutrition</title>
    <link rel="stylesheet" href="css/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.3.1/css/all.min.css">
    <link rel="icon" href="src/image/p amarilla bg.png">
    <link rel="stylesheet" href="css/registrar.css">
</head>
<body>
    <section>
        <a href="index.php"><img src="src/image/logo amarillo bg.png" alt=""></a>
        <p>Crea tu cuenta</p>
        <form id="formRegistro" method="POST">
            <div id="mensaje"></div>
            <div class="contenedor">

                <div class="box-input">
                    <label for="nombre">Nombre</label>
                    <div class="input-wrap">
                        <input type="text" id="nombre" name="nombre" placeholder="Tu nombre" class="SearchInput" maxlength="60" required autocomplete="given-name">
                        <button type="button" class="ButtonClear"><i class="fa-regular fa-circle-xmark" ></i></button>
                    </div>
                </div>

                <div class="box-input">
                    <label for="apellido">Apellido</label>
                    <div class="input-wrap">
                        <input type="text" id="apellido" name="apellido" placeholder="Tu apellido" class="SearchInput" maxlength="60" required autocomplete="family-name">
                        <button class="ButtonClear" type="button"><i class="fa-regular fa-circle-xmark"></i></button>
                    </div>
                </div>
            </div>
            
            <div class="box-input">
                <label for="correo">Correo</label>
                <div class="input-wrap">
                    <input type="email" id="correo" name="correo" placeholder="Tucorreo@ejemplo.com" class="SearchInput" required autocomplete="email">
                    <button class="ButtonClear" type="button"><i class="fa-regular fa-circle-xmark"></i></button>
                </div>
            </div>

            <div class="box-input">
                <label for="password">Contraseña</label>
                <div class="input-wrap">
                    <input class="password" id="password" name="password" type="password" placeholder="Minimo 8 caracteres" minlength="8" required autocomplete="new-password">
                    <button type="button" class="ButtonPasswordEye"><i class="fa-regular fa-eye"></i></button>
                </div>
            </div>

            <div class="box-input">
                <label for="confirmPassword">Confirmar contraseña</label>
                <div class="input-wrap">
                    <input class="password" id="confirmPassword" name="confirmar" type="password" placeholder="Repite tu contraseña" minlength="8" required autocomplete="new-password">
                    <button class="ButtonPasswordEye" type="button"><i class="fa-regular fa-eye"></i></button>
                </div>
            </div>
            
            
            <button type="submit" class="btn-acceso">Crear cuenta</button>
            <p>Ya tienes una cuenta? <a href="ingresar.php">Iniciar sesion</a></p>
        </form>
    </section>


    
    <script src="js/front-js/search-delete.js"></script>
    <script src="js/front-js/eyepassword.js"></script>
    <script src="js/api.js"></script>
    <script src="js/registrar.js"></script>
</body>
</html>