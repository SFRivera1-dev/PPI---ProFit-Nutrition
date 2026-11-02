<?php
// =====================================================================
// REGLAS DEL FORO (el servidor SIEMPRE valida; el navegador se puede saltar)
// =====================================================================
require_once __DIR__ . '/../http/respuesta.php';

const CATEGORIAS_FORO = ['Entrenamiento', 'Nutricion', 'Suplementos', 'Motivacion'];

// Qué hace: Valida una publicación: categoría de la lista, título de 5 a 100 caracteres y contenido
//           de 10 a 1000.
// Recibe:   $categoria, $titulo, $contenido: ya limpios con texto().
// Entrega:  Nada. Si algo está mal, responde 422 y termina.
function validarPublicacion($categoria, $titulo, $contenido)
{
    if (!in_array($categoria, CATEGORIAS_FORO, true)) {
        responderError(422, 'Elige una categoría.');
    }
    if (mb_strlen($titulo) < 5 || mb_strlen($titulo) > 100) {
        responderError(422, 'El título debe tener entre 5 y 100 caracteres.');
    }
    if (mb_strlen($contenido) < 10 || mb_strlen($contenido) > 1000) {
        responderError(422, 'El contenido debe tener entre 10 y 1000 caracteres.');
    }
}

// Qué hace: Valida una respuesta: de 2 a 500 caracteres.
// Recibe:   $contenido: ya limpio con texto().
// Entrega:  Nada. Si algo está mal, responde 422 y termina.
function validarRespuesta($contenido)
{
    if (mb_strlen($contenido) < 2 || mb_strlen($contenido) > 500) {
        responderError(422, 'La respuesta debe tener entre 2 y 500 caracteres.');
    }
}
