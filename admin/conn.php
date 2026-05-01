<?php
// Mantener compatibilidad: delegar la conexión centralizada en la raíz
require_once __DIR__ . '/../conn.php';

// (La función e() se define en la conexión raíz; definirla localmente sólo si no existe)
if (!function_exists('e')) {
    function e($v){ return htmlspecialchars($v ?? '', ENT_QUOTES, 'UTF-8'); }
}

