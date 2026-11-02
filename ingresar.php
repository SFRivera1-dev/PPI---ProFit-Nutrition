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
        <p>Ingresa a tu cuenta</p>
        <form id="formLogin" method="POST">
            <div id="mensaje"></div>
            <div class="box-input">
                <label for="correo">Correo</label>
                <div class="input-wrap">
                    <input type="email" id="correo" name="correo" placeholder="Tucorreo@ejemplo.com" class="SearchInput" required autocomplete="email">
                    <button type="button" class="ButtonClear"><i class="fa-regular fa-circle-xmark"></i></button>
                </div>
            </div>

            <div class="box-input">
                <label for="password">Contraseña</label>
                <div class="input-wrap">
                    <input type="password" placeholder="********" id="password" name="password" class="password" required autocomplete="current-password">
                    <button type="button" class="ButtonPasswordEye"><i class="fa-regular fa-eye"></i></button>
                </div>
            </div>
                
            <a href="">Olvidaste tu contraseña</a>
            <button type="submit" class="btn-acceso">Ingresar</button>
            <p>No tienes una cuenta? <a href="registrar.php">Registrate</a></p>
        </form>
    </section>

    <script src="js/front-js/search-delete.js"></script>
    <script src="js/front-js/eyepassword.js"></script>
    <script src="js/api.js"></script>
    <script src="js/ingresar.js"></script>
</body>
</html>