<?php
// =====================================================================
// RECURSO: imagen de un producto      (solo ADMIN)
//
//   POST   /api/productos_imagen.php?id=5   -> sube (o reemplaza) la foto   200 {"producto":{...}}
//          cuerpo: formulario con el archivo en el campo "imagen"  (multipart/form-data)
//          acepta JPG, PNG o WEBP de hasta 2 MB. Se guarda en la carpeta uploads/
//   DELETE /api/productos_imagen.php?id=5   -> quita la foto                200 {"producto":{...}}
//
// Es un endpoint aparte porque un archivo no viaja como JSON: viaja como formulario.
// =====================================================================
require_once __DIR__ . '/../includes/conexion.php';
require_once __DIR__ . '/../includes/http/respuesta.php';
require_once __DIR__ . '/../includes/http/peticion.php';
require_once __DIR__ . '/../includes/seguridad/autenticacion.php';
require_once __DIR__ . '/../includes/productos/imagen.php';
require_once __DIR__ . '/../includes/productos/publico.php';

$metodo = permitir(['POST', 'DELETE']);
exigirRol('admin');                                  // 401 sin token · 403 si no es admin


/* ---------- Buscar el producto (viene en ?id=) ---------- */
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

$imagenAnterior = (string) $producto['imagen'];


/* ---------- POST: subir o reemplazar ---------- */
if ($metodo === 'POST') {
    if (!isset($_FILES['imagen'])) {
        // Si el archivo pesa más que el límite de PHP, ni siquiera llega aquí
        responderError(400, 'Envía la imagen en el campo "imagen" (JPG, PNG o WEBP, máximo 2 MB).');
    }

    $rutaNueva = guardarImagenSubida($_FILES['imagen']);

    $stmt = $pdo->prepare('UPDATE productos SET imagen = :imagen WHERE id = :id');
    $stmt->execute([':imagen' => $rutaNueva, ':id' => $id]);

    borrarImagenGuardada($imagenAnterior);           // la foto vieja ya no sirve
    $producto['imagen'] = $rutaNueva;

    responder(['producto' => productoPublico($producto)]);
}


/* ---------- DELETE: quitar la foto ---------- */
$stmt = $pdo->prepare("UPDATE productos SET imagen = '' WHERE id = :id");
$stmt->execute([':id' => $id]);

borrarImagenGuardada($imagenAnterior);
$producto['imagen'] = '';

responder(['producto' => productoPublico($producto)]);
