<?php
// =====================================================================
// RECURSO: citas   (hay que iniciar sesión)
//
//   GET   /api/citas.php?fecha=2026-11-02  -> horas libres/ocupadas de ese día          (cualquier usuario con sesión)
//         200 {"fecha":"2026-11-02","horas":[{"hora":"08:00","libre":true}, ...]}
//   GET   /api/citas.php                   -> CLIENTE: sus citas (la más nueva primero)
//                                             PROFESIONAL / ADMIN: todas las citas (las próximas primero)
//         200 {"citas":[...]}
//   POST  /api/citas.php                   -> agenda una cita          201 {"cita":{...}}   (solo CLIENTE)
//         cuerpo: {servicio, objetivo, tiempo, fecha, hora}
//         409 si la fecha y hora ya están ocupadas, o si ya tienes una cita pendiente/confirmada
//   PATCH /api/citas.php?id=5              -> cambia el estado          200 {"cita":{...}}   (PROFESIONAL / ADMIN)
//                                             cuerpo: {"estado": "confirmada" | "finalizada"}
//   DELETE /api/citas.php?id=5             -> cancela (borra) una cita pendiente o confirmada   (solo ADMIN)
//         la hora queda libre otra vez. Una cita finalizada no se puede cancelar (409).
//   Búsqueda (profesional/admin):  GET /api/citas.php?q=ana  (nombre, apellido, correo, servicio u objetivo; un número busca por id)
//
// Estados:  pendiente -> confirmada -> finalizada   (solo se avanza)
// =====================================================================
require_once __DIR__ . '/../includes/conexion.php';
require_once __DIR__ . '/../includes/http/respuesta.php';
require_once __DIR__ . '/../includes/http/peticion.php';
require_once __DIR__ . '/../includes/seguridad/autenticacion.php';
require_once __DIR__ . '/../includes/citas/validar.php';
require_once __DIR__ . '/../includes/citas/publico.php';
require_once __DIR__ . '/../includes/citas/crear.php';
require_once __DIR__ . '/../includes/citas/estado.php';
require_once __DIR__ . '/../includes/citas/disponibilidad.php';
require_once __DIR__ . '/../includes/citas/eliminar.php';

$metodo  = permitir(['GET', 'POST', 'PATCH', 'DELETE']);
$usuario = usuarioActual();                              // 401 si no hay token válido


/* ---------- GET ---------- */
if ($metodo === 'GET') {

    // Horas libres de un día (para el formulario de agenda)
    $fecha = parametroUrl('fecha');
    if ($fecha !== '') {
        responder(['fecha' => $fecha, 'horas' => horasDelDia($fecha)]);
    }

    // Un cliente solo ve SUS citas
    if ($usuario['rol'] === 'cliente') {
        $citas = buscarCitas('c.usuario_id = :usuario', [':usuario' => $usuario['id']], 'c.fecha DESC, c.hora DESC', 100);
        responder(['citas' => $citas]);
    }

    // Profesional y admin ven todas (opcionalmente las que coinciden con una búsqueda)
    $condiciones = [];
    $valores     = [];

    $busqueda = trim(parametroUrl('q'));
    if ($busqueda !== '') {
        $sinNumeral = ltrim($busqueda, '#');
        if ($sinNumeral !== '' && ctype_digit($sinNumeral)) {
            $condiciones[]  = 'c.id = :id';
            $valores[':id'] = (int) $sinNumeral;
        } else {
            $condiciones[] = '(u.nombre LIKE :t1 OR u.apellido LIKE :t2 OR u.correo LIKE :t3
                              OR c.servicio LIKE :t4 OR c.objetivo LIKE :t5)';
            $texto = '%' . $busqueda . '%';
            $valores[':t1'] = $texto;
            $valores[':t2'] = $texto;
            $valores[':t3'] = $texto;
            $valores[':t4'] = $texto;
            $valores[':t5'] = $texto;
        }
    }

    // Las citas por atender primero (la más cercana arriba); las finalizadas al final
    $orden = "CASE WHEN c.estado = 'finalizada' THEN 1 ELSE 0 END, c.fecha ASC, c.hora ASC";
    responder(['citas' => buscarCitas(implode(' AND ', $condiciones), $valores, $orden, 500)]);
}


/* ---------- POST: agendar (solo clientes) ---------- */
if ($metodo === 'POST') {
    if ($usuario['rol'] !== 'cliente') {
        responderError(403, 'Solo los clientes pueden agendar citas.');
    }
    responder(['cita' => crearCita($usuario, leerJson())], 201);
}


/* ---------- DELETE: cancelar una cita (solo admin) ---------- */
if ($metodo === 'DELETE') {
    if ($usuario['rol'] !== 'admin') {
        responderError(403, 'Solo el administrador puede cancelar citas.');
    }
    $id = (int) parametroUrl('id');
    if ($id <= 0) {
        responderError(400, 'Falta el parámetro ?id= con la cita.');
    }
    cancelarCita($id);
    responder(['ok' => true]);
}


/* ---------- PATCH: cambiar el estado (profesional o admin) ---------- */
if ($usuario['rol'] !== 'profesional' && $usuario['rol'] !== 'admin') {
    responderError(403, 'No tienes permiso para esta acción.');
}

$id = (int) parametroUrl('id');
if ($id <= 0) {
    responderError(400, 'Falta el parámetro ?id= con la cita.');
}

$datos  = leerJson();
$estado = texto($datos, 'estado');
if ($estado === '') {
    responderError(422, 'Falta el nuevo estado.');
}

responder(['cita' => cambiarEstadoCita($id, $estado)]);
