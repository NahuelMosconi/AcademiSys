<?php
/**
 * Íconos SVG del sistema (reemplazan a los emojis).
 * Son vectoriales, se dibujan con la línea del color actual (currentColor),
 * así heredan el color del contexto (menú, botón, badge) y quedan nítidos
 * en cualquier tamaño. El tamaño se controla desde el CSS (.ico).
 *
 * Uso:  <?= icono('alumnos') ?>   o   <?= icono('alumnos', 'ico-lg') ?>
 */

function icono(string $nombre, string $clase = ''): string
{
    // Contenido (paths) de cada ícono, en un viewBox 0 0 24 24.
    static $paths = [
        'inicio'      => '<path d="M3 11 12 3l9 8"/><path d="M5 10v10h5v-6h4v6h5V10"/>',
        'calendario'  => '<rect x="3" y="4.5" width="18" height="16" rx="2.5"/><path d="M3 9h18M8 2.5V6M16 2.5V6"/>',
        'documento'   => '<path d="M14 3H7a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V8z"/><path d="M14 3v5h5"/><path d="M9 13h6M9 17h5"/>',
        'inscripcion' => '<rect x="5" y="4" width="14" height="17" rx="2"/><rect x="8" y="2.5" width="8" height="4" rx="1.5"/><path d="M9 12h6M9 16h4"/>',
        'alumno'      => '<path d="M12 4 2 9l10 5 10-5-10-5Z"/><path d="M6 11.5V16c0 1.6 2.7 3 6 3s6-1.4 6-3v-4.5"/><path d="M22 9v5"/>',
        'docente'     => '<rect x="3" y="3.5" width="18" height="12" rx="2"/><path d="M12 15.5V18"/><path d="M8 21l4-3 4 3"/>',
        'materia'     => '<path d="M6 3h12a1 1 0 0 1 1 1v14H8a2 2 0 0 0-2 2z"/><path d="M6 18a2 2 0 0 0 2 2h11"/><path d="M9 7h7"/>',
        'carrera'     => '<path d="M12 3 3 8l9 5 9-5-9-5Z"/><path d="M7 10.5V15c0 1.5 2.2 2.5 5 2.5s5-1 5-2.5v-4.5"/><path d="M21 8v5"/>',
        'comision'    => '<path d="M4 21V8l8-4 8 4v13"/><path d="M3 21h18"/><rect x="10" y="14" width="4" height="7"/><path d="M8 9.5h0M16 9.5h0M8 12.5h0M16 12.5h0"/>',
        'auditoria'   => '<path d="M12 3 19 6v5c0 4.5-3 7.6-7 9-4-1.4-7-4.5-7-9V6z"/><path d="M9 12l2 2 4-4"/>',
        'usuarios'    => '<circle cx="9" cy="8" r="3.2"/><path d="M3.5 20a5.5 5.5 0 0 1 11 0"/><path d="M16 5.2a3.2 3.2 0 0 1 0 5.6"/><path d="M16.5 15.1A5.5 5.5 0 0 1 20.5 20"/>',
        'perfil'      => '<circle cx="12" cy="12" r="3.2"/><path d="M19.4 15a1.6 1.6 0 0 0 .3 1.8l.1.1a2 2 0 1 1-2.8 2.8l-.1-.1a1.6 1.6 0 0 0-2.7 1.1V21a2 2 0 0 1-4 0v-.1A1.6 1.6 0 0 0 7 19.6a1.6 1.6 0 0 0-1.8.3l-.1.1a2 2 0 1 1-2.8-2.8l.1-.1A1.6 1.6 0 0 0 3.5 15H3.4a2 2 0 0 1 0-4h.1A1.6 1.6 0 0 0 4.6 8.3a1.6 1.6 0 0 0-.3-1.8l-.1-.1a2 2 0 1 1 2.8-2.8l.1.1A1.6 1.6 0 0 0 9 4v-.1a2 2 0 0 1 4 0V4a1.6 1.6 0 0 0 2.7 1.1l.1-.1a2 2 0 1 1 2.8 2.8l-.1.1a1.6 1.6 0 0 0-.3 1.8v.1a1.6 1.6 0 0 0 1.5 1h.1a2 2 0 0 1 0 4h-.1a1.6 1.6 0 0 0-1.5 1z"/>',
        'check'       => '<circle cx="12" cy="12" r="9"/><path d="M8 12l2.5 2.5L16 9"/>',
        'volver'      => '<path d="M20 12H5"/><path d="M11 18l-6-6 6-6"/>',
        'mas'         => '<path d="M12 5v14M5 12h14"/>',
        'salir'       => '<path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><path d="M16 17l5-5-5-5"/><path d="M21 12H9"/>',
        'buscar'      => '<circle cx="11" cy="11" r="7"/><path d="M16.5 16.5 21 21"/>',
    ];

    $d = $paths[$nombre] ?? '';
    $clases = trim('ico ' . $clase);
    return '<svg class="' . $clases . '" viewBox="0 0 24 24" fill="none" stroke="currentColor" '
         . 'stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">'
         . $d . '</svg>';
}
