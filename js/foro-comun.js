// =====================================================================
// Funciones que comparten las páginas del foro (público, admin y profesional)
//   fechaForo('2026-10-27 22:00:00') -> '27 oct 2026'
//   nombreAutor(autor)               -> 'Ana Gomez'
//   rolForo(autor)                   -> 'Administrador' | 'Profesional' | 'Comunidad'
// =====================================================================
const MESES_FORO = ['ene', 'feb', 'mar', 'abr', 'may', 'jun', 'jul', 'ago', 'sep', 'oct', 'nov', 'dic'];

const ROLES_FORO = {
    admin:       'Administrador',
    profesional: 'Profesional',
    cliente:     'Comunidad'
};

function fechaForo(texto) {
    const partes = String(texto).slice(0, 10).split('-');
    return Number(partes[2]) + ' ' + MESES_FORO[Number(partes[1]) - 1] + ' ' + partes[0];
}

function nombreAutor(autor) {
    return autor.nombre + ' ' + autor.apellido;
}

function rolForo(autor) {
    if (ROLES_FORO[autor.rol]) {
        return ROLES_FORO[autor.rol];
    }
    return 'Comunidad';
}
