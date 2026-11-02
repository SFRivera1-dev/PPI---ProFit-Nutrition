<?php
// =====================================================================
// RECURSO: productos
//
//   GET    /api/productos.php            -> lista (PÚBLICO: la tienda lo usa sin iniciar sesión)
//          filtros opcionales en la URL:
//            ?categoria=Proteina   ?q=whey   (busca en el nombre)   ?precio_max=100000   ?ids=1,2,3
//            ?orden=menor | mayor | nombre   (sin orden: del más antiguo al más nuevo)
//          responde 200 {"productos":[...], "categorias":[...], "total": 3}
//   POST   /api/productos.php            -> crea         201 {"producto":{...}}     (solo ADMIN)
//          cuerpo: {nombre, categoria, precio, stock}
//   PUT    /api/productos.php?id=5       -> modifica     200 {"producto":{...}}     (solo ADMIN)
//          cuerpo: {nombre, categoria, precio, stock}   (el formulario de editar los manda todos)
//   DELETE /api/productos.php?id=5       -> elimina      200 {"mensaje":"..."}      (solo ADMIN)
//
// La FOTO del producto se sube aparte: api/productos_imagen.php
// =====================================================================
require_once __DIR__ . '/../includes/conexion.php';
require_once __DIR__ . '/../includes/http/respuesta.php';
require_once __DIR__ . '/../includes/http/peticion.php';
require_once __DIR__ . '/../includes/seguridad/autenticacion.php';
require_once __DIR__ . '/../includes/productos/validar.php';
require_once __DIR__ . '/../includes/productos/crear.php';
require_once __DIR__ . '/../includes/productos/imagen.php';
require_once __DIR__ . '/../includes/productos/publico.php';

$metodo = permitir(['GET', 'POST', 'PUT', 'DELETE']);


/* ---------------------------------------------------------------------
   GET: listar productos (público, no pide token)
   --------------------------------------------------------------------- */
if ($metodo === 'GET') {
    $condiciones = [];      // pedazos del WHERE
    $valores     = [];      // valores de los huecos :nombre

    $categoria = parametroUrl('categoria');
    if ($categoria !== '') {
        $condiciones[]         = 'categoria = :categoria';
        $valores[':categoria'] = $categoria;
    }

    // ?ids=1,2,3  -> solo esos productos (lo usa el carrito)
    $ids = parametroUrl('ids');
    if ($ids !== '') {
        $marcadores = [];
        foreach (explode(',', $ids) as $idTexto) {
            if (ctype_digit($idTexto) && count($marcadores) < 100) {
                $marcador            = ':id' . count($marcadores);
                $marcadores[]        = $marcador;
                $valores[$marcador]  = (int) $idTexto;
            }
        }

        if (count($marcadores) > 0) {
            $condiciones[] = 'id IN (' . implode(', ', $marcadores) . ')';
        } else {
            $condiciones[] = '1 = 0';              // ids inválidos: no devolvemos nada
        }
    }

    $buscar = parametroUrl('q');
    if ($buscar !== '') {
        // Busca solo en el NOMBRE del producto. Si escribió en plural ("wheys"),
        // también prueba sin la "s" final.
        $singular = $buscar;
        if (strlen($singular) > 3 && substr($singular, -1) === 's') {
            $singular = substr($singular, 0, -1);
        }
        $condiciones[]       = '(nombre LIKE :buscar1 OR nombre LIKE :buscar2)';
        $valores[':buscar1'] = '%' . $buscar . '%';
        $valores[':buscar2'] = '%' . $singular . '%';
    }

    $precioMax = parametroUrl('precio_max');
    if ($precioMax !== '' && ctype_digit($precioMax)) {
        $condiciones[]          = 'precio <= :precio_max';
        $valores[':precio_max'] = (int) $precioMax;
    }

    $sql = 'SELECT * FROM productos';
    if (count($condiciones) > 0) {
        $sql .= ' WHERE ' . implode(' AND ', $condiciones);
    }

    $orden = parametroUrl('orden');
    if ($orden === 'menor') {
        $sql .= ' ORDER BY precio ASC, id ASC';
    } elseif ($orden === 'mayor') {
        $sql .= ' ORDER BY precio DESC, id ASC';
    } elseif ($orden === 'nombre') {
        $sql .= ' ORDER BY nombre ASC';
    } else {
        $sql .= ' ORDER BY id ASC';
    }

    $stmt = $pdo->prepare($sql);
    $stmt->execute($valores);

    $lista = [];
    foreach ($stmt->fetchAll() as $fila) {
        $lista[] = productoPublico($fila);
    }

    responder([
        'productos'  => $lista,
        'categorias' => CATEGORIAS_VALIDAS,
        'total'      => count($lista),
    ]);
}


/* ---------------------------------------------------------------------
   Desde aquí todo es SOLO ADMIN (POST, PUT, DELETE)
   --------------------------------------------------------------------- */
exigirRol('admin');                                  // 401 sin token · 403 si no es admin


/* ---------- POST: crear ---------- */
if ($metodo === 'POST') {
    $producto = crearProducto(leerJson());
    responder(['producto' => productoPublico($producto)], 201);
}


/* ---------- PUT / DELETE trabajan sobre UN producto, que viene en ?id= ---------- */
$id = (int) parametroUrl('id');

if ($id <= 0) {
    responderError(400, 'Falta el parámetro ?id= con el producto.');
}

$stmt = $pdo->prepare('SELECT * FROM productos WHERE id = :id');
$stmt->execute([':id' => $id]);
$producto = $stmt->fetch();

if (!$producto) {
    responderError(404, 'El producto no existe.');
}


/* ---------- DELETE: eliminar ---------- */
if ($metodo === 'DELETE') {
    $stmt = $pdo->prepare('DELETE FROM productos WHERE id = :id');
    $stmt->execute([':id' => $id]);

    borrarImagenGuardada((string) $producto['imagen']);      // la foto también se borra de uploads/

    responder(['mensaje' => 'Producto eliminado.']);
}


/* ---------- PUT: modificar (el formulario de editar manda todos los datos) ---------- */
$datos = leerJson();

$nombre    = texto($datos, 'nombre');
$categoria = texto($datos, 'categoria');
$precio    = aEntero(valor($datos, 'precio'));
$stock     = aEntero(valor($datos, 'stock'));

validarProducto($nombre, $categoria, $precio, $stock);
revisarNombreLibre($nombre, $id);

$stmt = $pdo->prepare(
    'UPDATE productos
     SET nombre = :nombre, categoria = :categoria, precio = :precio, stock = :stock
     WHERE id = :id'
);
$stmt->execute([
    ':nombre'    => $nombre,
    ':categoria' => $categoria,
    ':precio'    => $precio,
    ':stock'     => $stock,
    ':id'        => $id,
]);

responder(['producto' => productoPublico([
    'id' => $id, 'nombre' => $nombre, 'categoria' => $categoria, 'precio' => $precio,
    'stock' => $stock, 'imagen' => $producto['imagen'], 'creado_en' => $producto['creado_en'],
])]);
