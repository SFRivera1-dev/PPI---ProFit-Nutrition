-- =====================================================
-- ProFit Nutrition - Base de datos
-- Importar desde phpMyAdmin (pestaña "Importar")
-- =====================================================
CREATE DATABASE IF NOT EXISTS profit_nutrition
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE profit_nutrition;

-- -----------------------------------------------------
-- Tabla: usuarios (admin, profesional, cliente)
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS usuarios (
    id            INT UNSIGNED NOT NULL AUTO_INCREMENT,
    nombre        VARCHAR(60)  NOT NULL,
    apellido      VARCHAR(60)  NOT NULL,
    correo        VARCHAR(120) NOT NULL,
    password      VARCHAR(255) NOT NULL,           -- hash bcrypt (password_hash)
    rol           ENUM('admin','profesional','cliente') NOT NULL DEFAULT 'cliente',
    estado        ENUM('activo','inactivo') NOT NULL DEFAULT 'activo',
    creado_en     TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    ultimo_acceso DATETIME     NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uq_usuarios_correo (correo),
    KEY idx_usuarios_rol (rol)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------
-- Usuarios de prueba (cambia las contraseñas antes de publicar)
--   admin@profit.com        / Admin12345
--   profesional@profit.com  / Profesional123
--   cliente@profit.com      / Cliente12345
-- -----------------------------------------------------
INSERT IGNORE INTO usuarios (nombre, apellido, correo, password, rol) VALUES
('Andrea', 'Ruiz',   'admin@profit.com',       '$2b$10$Y4LGg4AtxXCUYVM6MQTZ/OwaGaaERTF2siu1a4rAYnXQd6n1hYfJS', 'admin'),
('Camilo', 'Torres', 'profesional@profit.com', '$2b$10$5lxHoHKOmezTkJ1QPk0/cOxZUCAKkcn7RC.s0h.BPa/OeNrBNeXMa', 'profesional'),
('Laura',  'Gomez',  'cliente@profit.com',     '$2b$10$En5y0V7iMmQQ9XDsuLhmSeDqfqznurXA5/VWNt7z6rM/3HKCcG0.S', 'cliente');

-- -----------------------------------------------------
-- Tabla: productos
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS productos (
    id        INT UNSIGNED NOT NULL AUTO_INCREMENT,
    nombre    VARCHAR(120) NOT NULL,
    categoria VARCHAR(40)  NOT NULL,                 -- Proteina, Creatina, Pre-entreno, Vitaminas, Snacks fit
    precio    INT UNSIGNED NOT NULL,                 -- en pesos, sin decimales
    stock     INT UNSIGNED NOT NULL DEFAULT 0,
    imagen    VARCHAR(255) NOT NULL DEFAULT '',      -- enlace (URL) de la foto; vacío = sin foto
    creado_en TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_productos_nombre (nombre),
    KEY idx_productos_categoria (categoria)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Productos de ejemplo (los de la plantilla de suplementos)
INSERT IGNORE INTO productos (nombre, categoria, precio, stock) VALUES
('Whey Gold Standard',     'Proteina',    145000, 42),
('Creatina Monohidratada', 'Creatina',     89000, 78),
('C4 Explosivo',           'Pre-entreno', 110000,  3),
('Multivitaminico Daily',  'Vitaminas',    52000, 35),
('Barra proteica x12',     'Snacks fit',   38000, 120),
('Isolate Zero Carb',      'Proteina',    168000, 12);

-- ---------------------------------------------------------------------
-- Pedidos (nunca se guardan datos de tarjeta)
-- ---------------------------------------------------------------------
-- Un registro por compra. Su "id" es el NÚMERO DE ORDEN (se muestra como PF-20001).
CREATE TABLE IF NOT EXISTS pedidos (
    id                INT UNSIGNED NOT NULL AUTO_INCREMENT,      -- número de orden
    usuario_id        INT UNSIGNED NULL,                         -- quién compró (NULL si luego borran su cuenta)

    -- Datos de entrega (se copian aquí: si el usuario cambia su perfil, el pedido no cambia)
    entrega_nombre    VARCHAR(60)  NOT NULL,
    entrega_apellido  VARCHAR(60)  NOT NULL,
    entrega_pais      VARCHAR(60)  NOT NULL DEFAULT 'Colombia',
    entrega_ciudad    VARCHAR(60)  NOT NULL,
    entrega_direccion VARCHAR(120) NOT NULL,

    -- Dinero, en pesos sin decimales
    subtotal          INT UNSIGNED NOT NULL,                     -- suma de los productos
    domicilio         INT UNSIGNED NOT NULL DEFAULT 10000,       -- costo del envío
    total             INT UNSIGNED NOT NULL,                     -- subtotal + domicilio

    metodo_pago       VARCHAR(30)  NOT NULL DEFAULT 'tarjeta',   -- solo el tipo, nunca los datos
    estado            ENUM('pendiente','confirmado','enviado','entregado','cancelado')
                      NOT NULL DEFAULT 'confirmado',

    creado_en         TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,                              -- fecha del pedido
    actualizado_en    TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,  -- último cambio de estado

    PRIMARY KEY (id),
    KEY idx_pedidos_usuario (usuario_id, creado_en),
    KEY idx_pedidos_estado (estado),
    CONSTRAINT fk_pedidos_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios (id) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=20001 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Una fila por producto comprado. Se guardan el nombre y el precio DE ESE MOMENTO,
-- así el pedido no cambia si luego editas o borras el producto.
CREATE TABLE IF NOT EXISTS pedido_items (
    id              INT UNSIGNED      NOT NULL AUTO_INCREMENT,
    pedido_id       INT UNSIGNED      NOT NULL,
    producto_id     INT UNSIGNED      NULL,                      -- NULL si el producto se borró del catálogo
    nombre          VARCHAR(120)      NOT NULL,                  -- copia del nombre al comprar
    categoria       VARCHAR(40)       NOT NULL,
    precio_unitario INT UNSIGNED      NOT NULL,                  -- copia del precio al comprar
    cantidad        SMALLINT UNSIGNED NOT NULL,
    subtotal        INT UNSIGNED      NOT NULL,                  -- precio_unitario * cantidad
    PRIMARY KEY (id),
    KEY idx_items_pedido (pedido_id),
    CONSTRAINT fk_items_pedido   FOREIGN KEY (pedido_id)   REFERENCES pedidos (id)   ON DELETE CASCADE,
    CONSTRAINT fk_items_producto FOREIGN KEY (producto_id) REFERENCES productos (id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- Citas (agenda del cliente)
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS citas (
    id             INT UNSIGNED NOT NULL AUTO_INCREMENT,
    usuario_id     INT UNSIGNED NOT NULL,                       -- el cliente que agendó

    servicio       ENUM('Nutricion','Fisico','Deporte','Seguimiento','General') NOT NULL,
    objetivo       VARCHAR(200) NOT NULL,                       -- "¿Cuál es tu objetivo?"
    tiempo         VARCHAR(100) NOT NULL,                       -- "¿Cuánto tiempo llevas?"

    fecha          DATE         NOT NULL,
    hora           CHAR(5)      NOT NULL,                       -- '08:00', '14:00'...

    estado         ENUM('pendiente','confirmada','finalizada') NOT NULL DEFAULT 'pendiente',

    -- 1 mientras la cita está pendiente o confirmada; NULL cuando se finaliza.
    -- Junto con el UNIQUE de abajo, la base de datos misma impide que un cliente tenga 2 citas activas.
    activa         TINYINT      NULL DEFAULT 1,

    creado_en      TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    actualizado_en TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (id),
    UNIQUE KEY uq_citas_horario (fecha, hora),                  -- una fecha y hora solo puede tomarse UNA vez
    UNIQUE KEY uq_citas_activa (usuario_id, activa),            -- un cliente solo puede tener UNA cita activa
    KEY idx_citas_usuario (usuario_id, fecha),
    KEY idx_citas_estado (estado, fecha),
    CONSTRAINT fk_citas_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ---------------------------------------------------------------------
-- FORO: publicaciones y respuestas (también está en database/foro.sql)
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS foro_publicaciones (
    id          INT UNSIGNED NOT NULL AUTO_INCREMENT,
    usuario_id  INT UNSIGNED NOT NULL,                          -- quién publicó

    categoria   ENUM('Entrenamiento','Nutricion','Suplementos','Motivacion') NOT NULL,
    titulo      VARCHAR(100)  NOT NULL,
    contenido   VARCHAR(1000) NOT NULL,

    oficial     TINYINT(1)   NOT NULL DEFAULT 0,                -- 1 = publicación oficial (admin / profesional)
    activa      TINYINT(1)   NOT NULL DEFAULT 1,                -- 0 = desactivada por el admin

    creado_en   TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,

    PRIMARY KEY (id),
    KEY idx_foro_pub_listado (activa, oficial, creado_en),
    KEY idx_foro_pub_usuario (usuario_id),
    CONSTRAINT fk_foro_pub_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS foro_respuestas (
    id              INT UNSIGNED NOT NULL AUTO_INCREMENT,
    publicacion_id  INT UNSIGNED NOT NULL,                      -- a qué publicación responde
    usuario_id      INT UNSIGNED NOT NULL,                      -- quién responde

    contenido       VARCHAR(500) NOT NULL,
    creado_en       TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,

    PRIMARY KEY (id),
    KEY idx_foro_resp_publicacion (publicacion_id, creado_en),
    KEY idx_foro_resp_usuario (usuario_id),
    CONSTRAINT fk_foro_resp_publicacion FOREIGN KEY (publicacion_id) REFERENCES foro_publicaciones (id) ON DELETE CASCADE,
    CONSTRAINT fk_foro_resp_usuario     FOREIGN KEY (usuario_id)     REFERENCES usuarios (id)          ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ---------------------------------------------------------------------
-- CONTÁCTANOS: mensajes del formulario (también está en database/contacto.sql)
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS contactos (
    id         INT UNSIGNED NOT NULL AUTO_INCREMENT,

    nombre     VARCHAR(100)  NOT NULL,
    correo     VARCHAR(150)  NOT NULL,
    asunto     VARCHAR(150)  NOT NULL,
    mensaje    VARCHAR(2000) NOT NULL,

    estado     ENUM('nuevo','leido') NOT NULL DEFAULT 'nuevo',
    creado_en  TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,

    PRIMARY KEY (id),
    KEY idx_contactos_estado (estado, creado_en)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
