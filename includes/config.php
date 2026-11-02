<?php
// =====================================================================
// CONFIGURACIÓN GENERAL
// Todo lo que cambia entre tu PC y el servidor final vive aquí.
// =====================================================================

// Carpeta del proyecto dentro de localhost (http://localhost/ProFit-Nutrition)
// OJO: este valor también está en js/api.js (el JavaScript no puede leer PHP).
define('BASE_URL', '/ProFit-Nutrition');

// Datos de MySQL (los de XAMPP por defecto)
define('DB_HOST', 'localhost');
define('DB_NAME', 'profit_nutrition');
define('DB_USER', 'root');
define('DB_PASS', '');

// Clave secreta con la que el servidor FIRMA los tokens (JWT).
define('JWT_SECRETO', 'profit-nutrition');
define('JWT_DURACION', 60 * 60 * 8);   // el token dura 8 horas
