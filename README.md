# ProFit Nutrition

1. Copia la carpeta en `htdocs` de XAMPP (debe quedar `htdocs/ProFit-Nutrition`).
2. Enciende Apache y MySQL.
3. En phpMyAdmin -> Importar -> `database/profit_nutrition.sql`.
4. Abre http://localhost/ProFit-Nutrition/
5. Cambia `JWT_SECRETO` en `includes/config.php`.

Comprobar que la API responde: http://localhost/ProFit-Nutrition/api/me.php
(debe mostrar un JSON con "Token ausente...").

Usuarios de prueba:

| Rol | Correo | Contraseña |
|---|---|---|
| Admin | admin@profit.com | Admin12345 |
| Profesional | profesional@profit.com | Profesional123 |
| Cliente | cliente@profit.com | Cliente12345 |

Recarga automática: `npm install` y `npm run dev`.
Arquitectura y endpoints: `docs/ARQUITECTURA.md`.
