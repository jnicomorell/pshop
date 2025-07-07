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
            $this->translationList = require_once(_PS_MODULE_DIR_ . 'profileadv/translations/translations.php');
        }
    }

    protected function calculateAgeInMonths(string $birth)
    {
        $birth = new DateTime(date('Y/m/d', strtotime($birth)));
        $now = new DateTime(date('Y/m/d', time()));
        $age = date_diff($now, $birth);
        return ($age->y * 12) + $age->m;
    }

    protected function findTranslatedDataByParameters($type, int $value)
    {
        $cookie = Context::getContext()->cookie;
        $iso_code = isset($cookie->id_lang) ? Language::getIsoById((int) $cookie->id_lang) : 'es';
        switch ($type) {
            case 'dog':
                return $this->translationList['breed']['dog'][$iso_code][$value];
            case 'cat':
                return $this->translationList['breed']['cat'][$iso_code][$value];
            default:
                return $this->translationList[$type][$iso_code][$value];
        }
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
