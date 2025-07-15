<?php



class Pets extends ObjectModel
{
    public $id_customer;
    public $name;
    public $email;
    public $type;
    public $esterilized;
    public $genre;
    public $birth;
    public $breed;
    public $weight;
    public $desired_weight;
    public $feeding;
    public $activity;
    public $physical_condition;
    public $pathology;
    public $allergies;
    public $amount;
    public $is_amount_blocked;
    public $comment;
    public $message;
    public $is_validated;
    public $date_add;
    public $date_upd;
    public $active;

    public $translations;
    private $iso_code;

    public function __construct($id = null, $id_lang = null, $id_shop = null)
    {
        parent::__construct($id, $id_lang, $id_shop);
        $this->iso_code = Context::getContext()->language ? Context::getContext()->language->iso_code : 'es';
        require_once _PS_MODULE_DIR_ . 'profileadv/classes/TranslationManager.php';
        $this->translations = ProfileadvTranslationManager::getDataTranslations($this->iso_code);
    }

    /**
     * @see ObjectModel::$definition
     */
    public static $definition = [
        'table' => 'avatar2customer',
        'primary' => 'id',
        'multilang' => false,
        'fields' => [
            'id_customer' => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedId'],
            'name' => ['type' => self::TYPE_STRING, 'validate' => 'isCleanHtml', 'required' => true, 'size' => 255],
            'email' => ['type' => self::TYPE_STRING, 'validate' => 'isCleanHtml', 'required' => true, 'size' => 255],
            'type' => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedId'],
            'esterilized' => ['type' => self::TYPE_BOOL, 'validate' => 'isBool'],
            'genre' => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedId'],
            'birth' => ['type' => self::TYPE_STRING, 'validate' => 'isCleanHtml', 'required' => true, 'size' => 255],
            'breed' => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedId'],
            'weight' => ['type' => self::TYPE_FLOAT, 'validate' => 'isUnsignedFloat'],
            'desired_weight' => ['type' => self::TYPE_FLOAT, 'validate' => 'isUnsignedFloat'],
            'feeding' => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedId'],
            'activity' => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedId'],
            'physical_condition' => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedId'],
            'pathology' => ['type' => self::TYPE_STRING, 'validate' => 'isCleanHtml', 'required' => true, 'size' => 255],
            'allergies' => ['type' => self::TYPE_STRING, 'validate' => 'isCleanHtml', 'required' => true, 'size' => 255],
            'amount' => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedId'],
            'is_amount_blocked' => ['type' => self::TYPE_BOOL, 'validate' => 'isBool'],
            'comment' => ['type' => self::TYPE_STRING, 'validate' => 'isCleanHtml', 'required' => true, 'size' => 255],
            'message' => ['type' => self::TYPE_STRING, 'validate' => 'isCleanHtml', 'required' => true, 'size' => 255],
            'is_validated' => ['type' => self::TYPE_BOOL, 'validate' => 'isBool'],
            'date_add' => ['type' => self::TYPE_DATE, 'validate' => 'isDate'],
            'date_upd' => ['type' => self::TYPE_DATE, 'validate' => 'isDate'],
            'active' => ['type' => self::TYPE_BOOL, 'validate' => 'isBool'],
        ]
    ];

    protected $webserviceParameters = [
        'objectNodeName' => 'pet',
        'objectsNodeName' => 'pets',
        'fields' => [
            'id_customer' => [],
            'email' => [
                'getter' => 'getWsPetCustomerEmail',
                'setter' => false,
            ],
            'name' => [],
            'type' => [
                'getter' => 'getWsPetType',
                'setter' => false,
            ],
            'esterilized' => [
                'getter' => 'getWsPetEsterilized',
                'setter' => false,
            ],
            'genre' => [
                'getter' => 'getWsPetGenre',
                'setter' => false,
            ],
            'birth' => [],
            'breed' => [
                'getter' => 'getWsPetBreed',
                'setter' => false,
            ],
            'weight' => [],
            'desired_weight' => [],
            'feeding' => [
                'getter' => 'getWsPetFeeding',
                'setter' => false,
            ],
            'activity' => [
                'getter' => 'getWsPetActivity',
                'setter' => false,
            ],
            'physical_condition' => [
                'getter' => 'getWsPetPhysicalCondition',
                'setter' => false,
            ],
            'pathology' => [
                'getter' => 'getWsPetPathology',
                'setter' => false,
            ],
            'allergies' => [
                'getter' => 'getWsPetAllergies',
                'setter' => false,
            ],
            'amount' => [],
            'is_amount_blocked' => [],
            'comment' => [
                'getter' => 'getWsPetComment',
                'setter' => false,
            ],
            'message' => [
                'getter' => 'getWsPetMessage',
                'setter' => false,
            ],
            'is_validated' => [
                'getter' => 'getWsPetIsValidated',
                'setter' => false,
            ],
            'date_add' => [],
            'date_upd' => [],
            'active' => [],
        ]
    ];

    /**
     * @return string
     */
    public function getWsPetType(): string
    {

        return $this->translations['type'][strval($this->type)] ?? '';
    }


    public function getWsPetCustomerEmail(): string
    {

        $customer = new Customer($this->id_customer);

        return  pSQL($customer->email);
    }


    public function getWsPetEsterilized(): string
    {
        (int) $this->esterilized === 0 ? $this->esterilized = 2 : $this->esterilized;

        return $this->translations['esterilized'][strval($this->esterilized)] ?? '';
    }

    public function getWsPetGenre(): string
    {
        return $this->translations['genre'][strval($this->genre)] ?? '';
    }

    /**
     * @return string
     */
    public function getWsPetBreed(): string
    {

        (int) $this->type === 2 ? $type = "cat" : $type = "dog";

        return $this->translations['breed'][$type][strval($this->breed)] ?? '';
    }


    public function getWsPetFeeding(): string
    {
        return $this->translations['feeding'][strval($this->feeding)] ?? '';
    }

    /**
     * @return string
     */
    public function getWsPetActivity(): string
    {
        return $this->translations['activity'][strval($this->activity)] ?? '';
    }

    public function getWsPetPhysicalCondition(): string
    {
        return $this->translations['physical-condition'][strval($this->physical_condition)] ?? '';
    }

    public function getWsPetPathology(): string
    {
        $pathologies = [];
        $this->pathology = json_decode($this->pathology);

        foreach ($this->pathology as $value) {
            $pathologies[] = $this->translations['pathologies'][strval($value)] ?? '';
        }

        $pathologies = implode('|', $pathologies);

        return $pathologies;
    }

    public function getWsPetAllergies(): string
    {
        $allergies = [];
        $this->allergies = json_decode($this->allergies);

        foreach ($this->allergies as $value) {
            $allergies[] = $this->translations['allergies'][strval($value)] ?? '';
        }

        $allergies = implode('|', $allergies);

        return $allergies;
    }

    public function getWsPetComment(): string
    {
        return html_entity_decode($this->comment, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }

    public function getWsPetMessage(): string
    {
        return html_entity_decode($this->message, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }

    public function getWsPetIsValidated(): bool
    {
        return (int) $this->is_validated;
    }
}
