<?php
// =====================================================================
// IMÁGENES DE PRODUCTOS: guardar y borrar archivos en la carpeta uploads/
//
//   guardarImagenSubida($archivo) -> valida el archivo subido, lo guarda en uploads/
//                                    y devuelve su ruta, por ejemplo "uploads/3fa9c1d2e4b5a6f7.jpg"
//   borrarImagenGuardada($ruta)   -> borra ese archivo de uploads/
//
// En la tabla productos se guarda SOLO la ruta (texto), no la imagen.
// =====================================================================
require_once __DIR__ . '/../http/respuesta.php';

const CARPETA_UPLOADS        = 'uploads';             // carpeta en la raíz del proyecto
const TAMANO_MAXIMO_IMAGEN   = 2 * 1024 * 1024;       // 2 MB

// Qué hace: Dice dónde está la carpeta uploads/ dentro del disco del servidor.
// Recibe:   Nada.
// Entrega:  La ruta de la carpeta (texto).
function carpetaUploadsEnDisco()
{
    return __DIR__ . '/../../' . CARPETA_UPLOADS;
}

// Qué hace: Revisa y guarda la imagen que subió el admin (tipo y peso permitidos) con un nombre al
//           azar.
// Recibe:   $archivo: un elemento de $_FILES (por ejemplo $_FILES["imagen"]).
// Entrega:  La ruta donde quedó guardada (uploads/...). Si algo está mal, responde el error y
//           termina.
function guardarImagenSubida($archivo)
{
    // 1. ¿El archivo llegó bien?
    if ($archivo['error'] === UPLOAD_ERR_INI_SIZE || $archivo['error'] === UPLOAD_ERR_FORM_SIZE) {
        responderError(413, 'La imagen es demasiado grande. Máximo 2 MB.');
    }
    if ($archivo['error'] !== UPLOAD_ERR_OK) {
        responderError(400, 'No se pudo recibir la imagen. Inténtalo de nuevo.');
    }
    if (!is_uploaded_file($archivo['tmp_name'])) {
        responderError(400, 'El archivo recibido no es válido.');
    }
    if ($archivo['size'] > TAMANO_MAXIMO_IMAGEN) {
        responderError(413, 'La imagen es demasiado grande. Máximo 2 MB.');
    }

    // 2. ¿Es de verdad una imagen JPG, PNG o WEBP?
    //    OJO: no nos fiamos del nombre que manda el navegador (se puede falsificar);
    //    getimagesize() abre el archivo y mira su contenido.
    $info = @getimagesize($archivo['tmp_name']);
    if ($info === false) {
        responderError(422, 'El archivo no es una imagen válida.');
    }

    $extensiones = [
        IMAGETYPE_JPEG => 'jpg',
        IMAGETYPE_PNG  => 'png',
        IMAGETYPE_WEBP => 'webp',
    ];
    $tipo = $info[2];
    if (!isset($extensiones[$tipo])) {
        responderError(422, 'Formato no permitido. Usa JPG, PNG o WEBP.');
    }

    // 3. Que exista la carpeta uploads/
    $carpeta = carpetaUploadsEnDisco();
    if (!is_dir($carpeta)) {
        if (!mkdir($carpeta, 0755, true)) {
            responderError(500, 'No se pudo crear la carpeta uploads.');
        }
    }

    // 4. Guardar con un nombre aleatorio (nunca con el nombre que trae el archivo)
    $nombre = bin2hex(random_bytes(8)) . '.' . $extensiones[$tipo];
    if (!move_uploaded_file($archivo['tmp_name'], $carpeta . '/' . $nombre)) {
        responderError(500, 'No se pudo guardar la imagen en el servidor.');
    }

    return CARPETA_UPLOADS . '/' . $nombre;
}

// Qué hace: Borra una imagen guardada. Solo borra archivos con la forma de los que guardamos
//           nosotros (uploads/ + 16 letras o números + .jpg/.png/.webp): así nunca se borra otra
//           cosa.
// Recibe:   $ruta: la ruta guardada en la base de datos.
// Entrega:  Nada.
function borrarImagenGuardada($ruta)
{
    if (!preg_match('/^uploads\/[a-f0-9]{16}\.(jpg|png|webp)$/', $ruta)) {
        return;
    }

    $archivo = __DIR__ . '/../../' . $ruta;
    if (is_file($archivo)) {
        unlink($archivo);
    }
}
