<?php
$translations = include dirname(__DIR__).'/translations.php';
$iso = 'fr';
$fallback = 'es';
return [
    'breed' => [
        'dog' => $translations['breed']['dog'][$iso] ?? $translations['breed']['dog'][$fallback],
        'cat' => $translations['breed']['cat'][$iso] ?? $translations['breed']['cat'][$fallback],
    ],
    'genre' => $translations['genre'][$iso] ?? $translations['genre'][$fallback],
    'type' => $translations['type'][$iso] ?? $translations['type'][$fallback],
    'esterilized' => $translations['esterilized'][$iso] ?? $translations['esterilized'][$fallback],
    'activity' => $translations['activity'][$iso] ?? $translations['activity'][$fallback],
    'physical-condition' => $translations['physical-condition'][$iso] ?? $translations['physical-condition'][$fallback],
    'feeding' => $translations['feeding'][$iso] ?? $translations['feeding'][$fallback],
    'pathologies' => $translations['pathologies'][$iso] ?? $translations['pathologies'][$fallback],
    'allergies' => $translations['allergies'][$iso] ?? $translations['allergies'][$fallback],
    'dailyratio' => $translations['dailyratio']['messages'][$iso] ?? $translations['dailyratio']['messages'][$fallback],
];
?>
