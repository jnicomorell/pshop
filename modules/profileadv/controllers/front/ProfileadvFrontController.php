<?php

class ProfileadvFrontController extends ModuleFrontController
{
    /** @var array */
    public $translationList = array();

    /** @var bool */
    protected $addCustomInputFileAssets = false;

    /** @var bool */
    protected $addProfileadvCustomCss = false;

    /** @var bool */
    protected $addProfileadvJs = false;

    protected function loadTranslations()
    {
        if (empty($this->translationList)) {
            require_once _PS_MODULE_DIR_ . 'profileadv/classes/TranslationManager.php';
            $iso = $this->context->language ? $this->context->language->iso_code : 'es';
            $this->translationList = ProfileadvTranslationManager::getDataTranslations($iso);
        }
    }

    protected function findTranslatedDataByParameters($type, int $value)
    {
        switch ($type) {
            case 'dog':
                return $this->translationList['breed']['dog'][$value] ?? '';
            case 'cat':
                return $this->translationList['breed']['cat'][$value] ?? '';
            default:
                return $this->translationList[$type][$value] ?? '';
        }
    }

    /**
     * Assign translation lists to Smarty.
     *
     * @param array  $translations
     * @param string $moduleName
     */
    protected function assignTranslations(array $translations, string $moduleName)
    {
        $this->context->smarty->assign([
            $moduleName . 'dogbreedlist'          => $translations['breed']['dog'],
            $moduleName . 'catbreedlist'          => $translations['breed']['cat'],
            $moduleName . 'typelist'              => $translations['type'],
            $moduleName . 'genrelist'             => $translations['genre'],
            $moduleName . 'esterilizedlist'       => $translations['esterilized'],
            $moduleName . 'activitylist'          => $translations['activity'],
            $moduleName . 'physicalconditionlist' => $translations['physical-condition'],
            $moduleName . 'feedinglist'           => $translations['feeding'],
            $moduleName . 'pathologieslist'       => $translations['pathologies'],
            $moduleName . 'allergieslist'         => $translations['allergies'],
        ]);
    }

    protected function assignBrowserDetection()
    {
        $isChrome = (bool) (preg_match('/chrome/i', $_SERVER['HTTP_USER_AGENT']) ||
            preg_match('/Firefox\/10\.0\.1/i', $_SERVER['HTTP_USER_AGENT']));
        $this->context->smarty->assign('profileadvis_chrome', (int) $isChrome);
    }

    public function setMedia()
    {
        $module_name = 'profileadv';
        if ($this->addCustomInputFileAssets) {
            $this->context->controller->addCSS(__PS_BASE_URI__ . 'modules/' . $module_name . '/views/css/custom-input-file.css');
            $this->context->controller->addJs(__PS_BASE_URI__ . 'modules/' . $module_name . '/views/js/custom-input-file.js');
        }
        if ($this->addProfileadvCustomCss) {
            $this->context->controller->addCSS(__PS_BASE_URI__ . 'modules/' . $module_name . '/views/css/profileadv-custom.css');
        }
        if ($this->addProfileadvJs) {
            $this->context->controller->addJS(__PS_BASE_URI__ . 'modules/' . $module_name . '/views/js/profileadv-custom.js');
        }
        parent::setMedia();
    }
}
