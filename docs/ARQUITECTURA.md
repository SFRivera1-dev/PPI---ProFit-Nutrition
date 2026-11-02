# ProFit Nutrition — Arquitectura

## Resumen
Aplicación web **cliente-servidor** con una **API REST** (PHP + MySQL) que responde en JSON,
y un front de HTML + CSS + JavaScript que consume esa API con `fetch()`.

| Capa | Qué hace | Dónde |
|---|---|---|
| Presentación | HTML, CSS, JS. Dibuja y pide datos con `fetch()` | `*.php` (cascarones), `css/`, `js/` |
| API REST | Endpoints que reciben HTTP y responden JSON | `api/` |
| Servicios | HTTP, seguridad, reglas de usuarios | `includes/http/`, `includes/seguridad/`, `includes/usuarios/` |
| Datos | MySQL con PDO | `database/`, `includes/conexion.php` |

Las páginas `.php` solo hacen `include` del header/plantilla. No manejan sesión ni consultan la BD.

## Autenticación (JWT, sin sesión en el servidor)
1. `POST /api/login.php` con `{correo, password}`.
2. El servidor verifica con `password_verify` y responde `{token, usuario}`.
3. El navegador guarda el token (`localStorage`) y lo envía en cada petición:
   `Authorization: Bearer <token>`.
4. Cada endpoint protegido llama a `exigirRol(...)`: verifica la firma y vigencia del token,
   busca al usuario en la BD (debe estar activo) y comprueba el rol.
   Sin token -> 401. Rol incorrecto -> 403.

## Orden de una petición en el backend
```
api/<endpoint>.php            1. puerta de entrada: recibe y responde
  includes/http/peticion.php      2. leer: permitir(), leerJson(), texto()
  includes/seguridad/             3. quién eres: token.php, autenticacion.php
  includes/usuarios/              4. reglas: validar.php, crear.php, publico.php
  includes/productos/                reglas de productos: validar.php, crear.php, publico.php
  includes/conexion.php           5. MySQL ($pdo)
  includes/http/respuesta.php     6. responder() en JSON
```

## Endpoints
| Método | Ruta | Quién | Respuestas |
|---|---|---|---|
| POST | `/api/login.php` | público | 200 · 401 · 403 · 422 |
| POST | `/api/registro.php` | público | 201 · 409 · 422 |
| GET | `/api/me.php` | con token | 200 · 401 |
| GET | `/api/usuarios.php[?rol=]` | admin | 200 · 401 · 403 |
| POST | `/api/usuarios.php` | admin | 201 · 409 · 422 |
| PATCH | `/api/usuarios.php?id=N` `{rol}` o `{estado}` | admin | 200 · 403 · 404 · 422 |
| GET | `/api/productos.php[?categoria=&q=&precio_max=&orden=&ids=1,2,3]` | público | 200 |
| POST | `/api/productos.php` | admin | 201 · 401 · 403 · 409 · 422 |
| PUT | `/api/productos.php?id=N` | admin | 200 · 404 · 409 · 422 |
| DELETE | `/api/productos.php?id=N` | admin | 200 · 404 (también borra su foto) |
| POST | `/api/productos_imagen.php?id=N` | admin | 200 · 400 · 413 · 422 (archivo `imagen`, multipart) |
| DELETE | `/api/productos_imagen.php?id=N` | admin | 200 · 404 |
| POST | `/api/pedidos.php` | con token | 201 · 401 · 409 (sin stock) · 422 |
| GET | `/api/pedidos.php` | con token | 200 · 401 (tus pedidos con sus productos, máx. 100) |
| GET | `/api/pedidos_admin.php[?q=&limite=]` | admin | 200 · 401 · 403 (todos los pedidos; `q` = número de pedido o cliente) |
| PATCH | `/api/pedidos_admin.php?id=N` | admin | 200 · 404 · 409 · 422 (cambia el estado; al cancelar devuelve el stock) |
| GET | `/api/estadisticas.php` | admin | 200 (clientes, profesionales, ventas del mes, pedidos nuevos) |
| GET | `/api/citas.php?fecha=AAAA-MM-DD` | cualquier sesión | 200 `{fecha, horas:[{hora, libre}]}` |
| GET | `/api/citas.php` | cliente: las suyas · profesional/admin: todas (`?fecha=`, `?q=`) | 200 `{citas}` |
| POST | `/api/citas.php` | cliente | 201 `{cita}` · 409 si la hora está ocupada o ya tiene una cita sin finalizar |
| PATCH | `/api/citas.php?id=` `{estado}` | profesional/admin | 200 `{cita}` (pendiente → confirmada → finalizada, solo hacia adelante) |
| GET | `/api/citas.php?q=` | profesional/admin | Busca por cliente, correo, servicio, objetivo o número de cita |
| DELETE | `/api/citas.php?id=` | admin | 200 `{ok}` · cancela una cita pendiente/confirmada y libera la hora · 409 si ya está finalizada |
| GET | `/api/pacientes.php[?q=]` | profesional/admin | 200 `{pacientes}` — clientes con al menos una cita (total, finalizadas, en curso, última fecha) |
| GET | `/api/pacientes.php?id=` | profesional/admin | 200 `{paciente, citas}` — historial completo · 404 si no tiene citas |
| GET | `/api/foro.php` | público | 200 `{oficiales, comunidad}` — solo publicaciones activas, las más nuevas primero |
| GET | `/api/foro.php?id=` | público | 200 `{publicacion, respuestas}` · 404 si no existe o está desactivada |
| POST | `/api/foro.php` | cualquier sesión | 201 `{publicacion}` — si quien publica es admin/profesional sale **oficial**; si es cliente, en la comunidad (lo decide el servidor, no el navegador) |
| POST | `/api/foro_respuestas.php?publicacion=` | cualquier sesión | 201 `{respuesta}` · 404 si la publicación está desactivada |
| GET | `/api/foro_admin.php[?q=]` | admin | 200 `{publicaciones}` — todas, también las desactivadas |
| PATCH | `/api/foro_admin.php?id=` `{activa}` | admin | 200 `{publicacion}` — `false` la oculta del foro, `true` la vuelve a mostrar |
| POST | `/api/contacto.php` | público | 201 — guarda el mensaje de Contáctanos · 422 datos inválidos · 409 mensaje repetido · campo trampa `sitio_web` contra bots |
| GET | `/api/contacto_admin.php[?q=]` | admin | 200 `{mensajes, nuevos}` |
| PATCH | `/api/contacto_admin.php?id=` `{estado}` | admin | 200 `{mensaje}` — `nuevo` ↔ `leido` |

## Seguridad
| Amenaza | Defensa |
|---|---|
| Robo de contraseñas | Hash bcrypt (`password_hash` / `password_verify`) |
| SQL Injection | Consultas preparadas con PDO |
| XSS | `escapar()` en JS al pintar datos de usuario |
| Token falso o alterado | Firma HMAC-SHA256 con `JWT_SECRETO` + vencimiento (8 h) |
| Acceso a recursos ajenos | Control por rol en cada endpoint (401/403) |
| Usuario desactivado | El token se valida contra la BD en cada petición |
| Archivos maliciosos al subir imagen | Se abre el archivo con `getimagesize()` (solo JPG/PNG/WEBP, máx. 2 MB), nombre aleatorio, y `uploads/.htaccess` impide ejecutar código ahí |
| Precios manipulados / sobreventa | El pedido solo trae ids y cantidades; el servidor toma precio de la BD, fija el domicilio y descuenta stock con `UPDATE ... WHERE stock >= n` dentro de una transacción |
| Doble reserva de una hora / dos citas activas | Índices únicos en la BD: `UNIQUE(fecha,hora)` y `UNIQUE(usuario_id, activa)`; el servidor responde 409 aunque dos clientes manden la petición a la vez |
| Datos de tarjeta | Se validan en el navegador pero nunca viajan ni se guardan |
| Escalada de privilegios | El registro público siempre crea `cliente` |
| Enumeración de usuarios | Mismo mensaje si falla correo o contraseña |

Pendientes: HTTPS, límite de intentos de login, recuperar contraseña, revocación de tokens.

## Estructura
```
api/        endpoints REST (JSON)
includes/   config, conexion, api (JWT y helpers), usuarios, header, plantillas de panel
js/         api.js (cliente de la API) + un script por página
admin/ profesional/ cliente/ pages/   páginas (HTML + include)
database/   profit_nutrition.sql
```
