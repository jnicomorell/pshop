<?php
$translations = include dirname(__DIR__).'/translations.php';
$iso = 'es';
return [
    'breed' => [
        'dog' => $translations['breed']['dog'][$iso] ?? [],
        'cat' => $translations['breed']['cat'][$iso] ?? [],
    ],
    'genre' => $translations['genre'][$iso] ?? [],
    'type' => $translations['type'][$iso] ?? [],
    'esterilized' => $translations['esterilized'][$iso] ?? [],
    'activity' => $translations['activity'][$iso] ?? [],
    'physical-condition' => $translations['physical-condition'][$iso] ?? [],
    'feeding' => $translations['feeding'][$iso] ?? [],
    'pathologies' => $translations['pathologies'][$iso] ?? [],
    'allergies' => $translations['allergies'][$iso] ?? [],
    'dailyratio' => $translations['dailyratio']['messages'][$iso] ?? [],
];
?>
