<?php
class ProfileadvTranslationManager
{
    public static function getDataTranslations(string $iso)
    {
        $baseDir = _PS_MODULE_DIR_.'profileadv/translations/data/';
        $file = $baseDir.$iso.'.php';
        if (file_exists($file)) {
            return include $file;
        }
        // Fallback to main translations file
        $translations = include _PS_MODULE_DIR_.'profileadv/translations/translations.php';
        $fallback = isset($translations['genre'][$iso]) ? $iso : 'es';
        return [
            'breed' => [
                'dog' => $translations['breed']['dog'][$fallback] ?? [],
                'cat' => $translations['breed']['cat'][$fallback] ?? [],
            ],
            'genre' => $translations['genre'][$fallback] ?? [],
            'type' => $translations['type'][$fallback] ?? [],
            'esterilized' => $translations['esterilized'][$fallback] ?? [],
            'activity' => $translations['activity'][$fallback] ?? [],
            'physical-condition' => $translations['physical-condition'][$fallback] ?? [],
            'feeding' => $translations['feeding'][$fallback] ?? [],
            'pathologies' => $translations['pathologies'][$fallback] ?? [],
            'allergies' => $translations['allergies'][$fallback] ?? [],
            'dailyratio' => $translations['dailyratio']['messages'][$fallback] ?? [],
        ];
    }
}
?>
