<?php

use BcMath\Number;

/**
 * 2011 - 2019 StorePrestaModules SPM LLC.
 *
 * MODULE profileadv
 *
 * @author    SPM <spm.presto@gmail.com>
 * @copyright Copyright (c) permanent, SPM
 * @license   Addons PrestaShop license limitation
 * @version   1.2.9
 * @link      http://addons.prestashop.com/en/2_community-developer?contributor=790166
 *
 * NOTICE OF LICENSE
 *
 * Don't use this module on several shops. The license provided by PrestaShop Addons
 * for all its modules is valid only once for a single shop.
 */

class profileAdvanced extends Module
{

	private $_width = 600;
	private $_height = 600;
	private $_step;
	private $_http;

	private $_name;
	private $id_customer;
	private $_is_cloud;



	public function __construct()
	{
		parent::__construct();

		$this->_name = "profileadv";

		$this->_step = (int)Configuration::get($this->_name . 'perpage_shoppers');
		$this->_http = $this->_http();

		$this->_http_host = Tools::getShopDomainSsl(true, true) . __PS_BASE_URI__;

		if (defined('_PS_HOST_MODE_'))
			$this->_is_cloud = 1;
		else
			$this->_is_cloud = 0;

		if ($this->_is_cloud) {
			$this->path_img_cloud = DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR . "img" . DIRECTORY_SEPARATOR . "pets" . DIRECTORY_SEPARATOR;
		} else {
			$this->path_img_cloud = DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR . "img" . DIRECTORY_SEPARATOR . "pets" . DIRECTORY_SEPARATOR;
		}

		$this->initContext();
	}

	private function initContext()
	{
		$this->context = Context::getContext();
	}

	public function getShoppersList($data = null)
	{


		$start = $data['start'];
		$step = $this->_step;
		$search = isset($data['search']) ? $data['search'] : 0;

		$sql = '
		SELECT pc.*, a2c.avatar_thumb
		FROM `' . _DB_PREFIX_ . 'customer` pc LEFT JOIN `' . _DB_PREFIX_ . 'avatar2customer` a2c
		on(a2c.id_customer = pc.id_customer) 
		WHERE pc.active = 1 AND pc.deleted = 0 AND (a2c.`is_show` = 1 OR a2c.`is_show` IS NULL)
		ORDER BY pc.`id_customer` ASC LIMIT ' . (int)$start . ' ,' . (int)$step . '';

		$customers = Db::getInstance()->ExecuteS($sql);
		$i = 0;
		foreach ($customers as $_item_customer) {

			$avatar_thumb = $_item_customer['avatar_thumb'];
			$id_gender = $_item_customer['id_gender'];
			$this->id_customer = $_item_customer['id_customer'];

			// addresses
			$info_addresses = $this->_getAddresses(array('id_customer' => $this->id_customer));
			$address_item = end($info_addresses['multipleAddressesFormated']);
			$customers[$i]['country'] = @$address_item['country'];

			// user with avatar
			$info_path = $this->_getAvatarPath(array('id_gender' => $id_gender, 'avatar_thumb' => $avatar_thumb));

			$customers[$i]['avatar_thumb'] = $info_path['avatar_thumb'];
			$customers[$i]['exist_avatar'] = $info_path['exist_avatar'];

			$i++;
		}

		$data_count_customers = Db::getInstance()->getRow('
		SELECT COUNT(pc.id_customer) AS "count"
		FROM `' . _DB_PREFIX_ . 'customer` pc LEFT JOIN `' . _DB_PREFIX_ . 'avatar2customer` a2c
		on(a2c.id_customer = pc.id_customer) 
		WHERE pc.active = 1 AND pc.deleted = 0 AND (a2c.`is_show` = 1 OR a2c.`is_show` IS NULL)
		');

		$paging = $this->PageNav(array(
			'start' => $start,
			'step' => $step,
			'count' => $data_count_customers['count'],
			'search' => $search
		));

		return array(
			'customers' => $customers,
			'data_count_customers' => $data_count_customers['count'],
			'paging' => $paging
		);
	}


	public function getShoppersBlock($data = null)
	{


		$start = $data['start'];
		$step = isset($data['step']) ? $data['step'] : $this->_step;


		$customers = Db::getInstance()->ExecuteS('
		SELECT pc.*, a2c.avatar_thumb
		FROM `' . _DB_PREFIX_ . 'customer` pc LEFT JOIN `' . _DB_PREFIX_ . 'avatar2customer` a2c
		on(a2c.id_customer = pc.id_customer) 
		WHERE pc.active = 1 AND pc.deleted = 0 AND (a2c.`is_show` = 1 OR a2c.`is_show` IS NULL)
		ORDER BY RAND() 
		LIMIT ' . (int)$start . ' ,' . (int)$step . '');
		$i = 0;
		foreach ($customers as $_item_customer) {

			$avatar_thumb = $_item_customer['avatar_thumb'];
			$id_gender = $_item_customer['id_gender'];

			// user with avatar
			$info_path = $this->_getAvatarPath(array('id_gender' => $id_gender, 'avatar_thumb' => $avatar_thumb));

			$customers[$i]['avatar_thumb'] = $info_path['avatar_thumb'];
			$customers[$i]['exist_avatar'] = $info_path['exist_avatar'];

			$i++;
		}


		return array(
			'customers' => $customers
		);
	}

	public function getShoppersListSearch($data = null)
	{



		$start = $data['start'];
		$step = $this->_step;
		$search = isset($data['search']) ? $data['search'] : 0;
		$query = trim(htmlspecialchars(strip_tags($data['query'])));


		$customers = Db::getInstance()->ExecuteS('
		SELECT pc.*, a2c.avatar_thumb
		FROM `' . _DB_PREFIX_ . 'customer` pc LEFT JOIN `' . _DB_PREFIX_ . 'avatar2customer` a2c
		on(a2c.id_customer = pc.id_customer) 
		WHERE pc.active = 1 AND pc.deleted = 0 AND (a2c.`is_show` = 1 OR a2c.`is_show` IS NULL) AND
		(
		LOWER(pc.lastname) LIKE BINARY LOWER(\'%' . pSQL($query) . '%\')
	      OR
	     LOWER(pc.firstname) LIKE BINARY LOWER(\'%' . pSQL($query) . '%\')
	    )
		ORDER BY pc.`id_customer` ASC LIMIT ' . (int)$start . ' ,' . (int)$step . '');
		$i = 0;
		foreach ($customers as $_item_customer) {

			$avatar_thumb = $_item_customer['avatar_thumb'];
			$id_gender = $_item_customer['id_gender'];
			$this->id_customer = $_item_customer['id_customer'];

			// addresses
			$info_addresses = $this->_getAddresses(array('id_customer' => $this->id_customer));
			$address_item = end($info_addresses['multipleAddressesFormated']);
			$customers[$i]['country'] = @$address_item['country'];

			// user with avatar
			$info_path = $this->_getAvatarPath(array('id_gender' => $id_gender, 'avatar_thumb' => $avatar_thumb));

			$customers[$i]['avatar_thumb'] = $info_path['avatar_thumb'];
			$customers[$i]['exist_avatar'] = $info_path['exist_avatar'];

			$i++;
		}

		$data_count_customers = Db::getInstance()->getRow('
		SELECT COUNT(pc.id_customer) AS "count"
		FROM `' . _DB_PREFIX_ . 'customer` pc LEFT JOIN `' . _DB_PREFIX_ . 'avatar2customer` a2c
		on(a2c.id_customer = pc.id_customer)
		WHERE pc.active = 1 AND pc.deleted = 0 AND (a2c.`is_show` = 1 OR a2c.`is_show` IS NULL) AND
		(
		LOWER(pc.lastname) LIKE BINARY LOWER(\'%' . pSQL($query) . '%\')
	      OR
	     LOWER(pc.firstname) LIKE BINARY LOWER(\'%' . pSQL($query) . '%\')
	    )
		');

		$paging = $this->PageNav(array(
			'start' => $start,
			'step' => $step,
			'count' => $data_count_customers['count'],
			'search' => $search,
			'query' => $query
		));

		return array(
			'customers' => $customers,
			'data_count_customers' => $data_count_customers['count'],
			'paging' => $paging
		);
	}

	public function getShopperInfo($data = null)
	{

		$cookie = $this->context->cookie;

		$shopper_id = isset($data['shopper_id']) ? (int)$data['shopper_id'] : 0;

		$customers = Db::getInstance()->ExecuteS('
		SELECT pc.*, a2c.avatar_thumb
		FROM `' . _DB_PREFIX_ . 'customer` pc LEFT JOIN `' . _DB_PREFIX_ . 'avatar2customer` a2c
		on(a2c.id_customer = pc.id_customer) 
		WHERE pc.active = 1 AND pc.deleted = 0 AND (a2c.`is_show` = 1 OR a2c.`is_show` IS NULL)
		 AND pc.id_customer = "' . (int)$shopper_id);
		$i = 0;
		foreach ($customers as $_item_customer) {

			$avatar_thumb = $_item_customer['avatar_thumb'];
			$id_gender = $_item_customer['id_gender'];
			$this->id_customer = $_item_customer['id_customer'];

			// addresses
			$info_addresses = $this->_getAddresses(array('id_customer' => $this->id_customer));
			$customers[$i]['addresses'] = $info_addresses['multipleAddressesFormated'];
			$address_item = end($info_addresses['multipleAddressesFormated']);
			$customers[$i]['country'] = @$address_item['country'];

			// user with avatar
			$info_path = $this->_getAvatarPath(array('id_gender' => $id_gender, 'avatar_thumb' => $avatar_thumb));
			$customers[$i]['avatar_thumb'] = $info_path['avatar_thumb'];
			$customers[$i]['exist_avatar'] = $info_path['exist_avatar'];
			$customers[$i]['gender_txt'] = $info_path['gender_txt'];

			// load stats for customer
			$customer_obj = new Customer($this->id_customer);
			$stats = $customer_obj->getStats();
			$stats_tmp = array();
			foreach ($stats as $_key_stat => $_item_stat) {
				switch ($_key_stat) {
					case 'last_visit':
						$_item_stat = ($_item_stat ? @Tools::displayDate($_item_stat, (int)($cookie->id_lang), true) : $this->l('never'));
						break;
				}
				$stats_tmp[$_key_stat] = $_item_stat;
			}
			$customers[$i]['stats'] = $stats;


			$i++;
		}



		return array('customer' => $customers);
	}

	private function _getAddresses($data)
	{
		$cookie = $this->context->cookie;

		$this->id_customer = $data['id_customer'];
		// adresses
		$customer = new Customer($this->id_customer);
		$customerAddressesDetailed = $customer->getAddresses($cookie->id_lang);

		return array('multipleAddressesFormated' => $customerAddressesDetailed);
	}

	private function _getAvatarPath($data)
	{
		$avatar_thumb = $data['avatar_thumb'];
		$id_gender = $data['id_gender'];
		$exist_avatar = 0;
		// user with avatar
		$gender_txt = '';

		if (Tools::strlen($avatar_thumb) > 0) {

			if ($this->_is_cloud) {
				$path_img_cloud = "modules/" . $this->_name . "/upload/";
			} else {
				$path_img_cloud = "img/pets/";
			}

			$avatar_thumb = $this->_http . $path_img_cloud . $avatar_thumb;
			$exist_avatar = 1;
			switch ($id_gender) {
				case 1:
					//male
					$gender_txt = $this->l("Male");
					break;
				case 2:
					//female
					$gender_txt = $this->l("Female");
					break;
			}
		} else {
			// user without avatar
			switch ($id_gender) {
				case 1:
					//male
					$avatar_thumb = $this->_http . "modules/profileadv/views/img/avatar_m.gif";
					$gender_txt = $this->l("Male");
					break;
				case 2:
					//female
					$avatar_thumb = $this->_http . "modules/profileadv/views/img/avatar_w.gif";
					$gender_txt = $this->l("Female");

					break;
				default:
					//unknown
					$avatar_thumb = $this->_http . "modules/profileadv/views/img/avatar_n.gif";
					break;
			}
		}
		return array(
			'avatar_thumb' => $avatar_thumb,
			'exist_avatar' => $exist_avatar,
			'gender_txt' => $gender_txt
		);
	}

	public function saveImage($data = null)
	{

		$error = 0;
		$error_text = '';
		$files = isset($_FILES['profileadvimg']) ? $_FILES['profileadvimg'] : null;
		$updateAvatar = false; //Customer only modifies pet data (no avatar)
		$this->id_customer = isset($data['newpetdata']['pet-customer']) ? $data['newpetdata']['pet-customer'] : 1;

		if (empty($files['name'])) {
			if ($data['action'] === 'addpet' || $data['action'] === 'addfirstpet' || $data['action'] === 'addpetbo') {
				//Get associated breed image (customer does not upload images)
				$files = $this->_getAssociatedImageFromBreed($data['newpetdata']['pet-type'], $data['newpetdata']['pet-breed']);
			}
		}

		############### files ###############################
		if (!empty($files['name']) || !$updateAvatar) {
			if (!$files['error']  || !$updateAvatar) {
				$type_one = $files['type'];
				$ext = explode("/", $type_one);

				if (strpos('_' . $type_one, 'image') < 1  && $updateAvatar) {
					$error_text = $this->l('Invalid file type, please try again!');
					$error = 1;
				} elseif ($ext[0] !== "" && !in_array($ext[1], array('png', 'x-png', 'gif', 'jpg', 'jpeg', 'pjpeg'))) {
					$error_text = $this->l('Wrong file format, please try again!');
					$error = 1;
				} else {

					$_info = array();
					if (!isset($data['newpetdata']['pet-customer'])) {
						$_info = $this->getCustomerInfo((int)Context::getContext()->cookie->id_customer, $data['newpetdata']['pet-reference']);
					} else {
						$_info['id_customer'] = $data['newpetdata']['pet-customer'];
					}

					$id_customer = $_info['id_customer'];
					$data['show_my_profile'] = isset($data['show_my_profile']) ? (int) $data['show_my_profile'] : 1;
					if ($id_customer == 0) {
						$error_text = $this->l('User is unregistered!');
						$error = 1;
					} else {

						srand((float)microtime() * 1000000);
						$uniq_name_image = uniqid(rand());
						$type_one = Tools::substr($type_one, 6, Tools::strlen($type_one) - 6);
						$filename = $uniq_name_image . '.' . $type_one;

						//Detect if file is uploaded by customer or automatically

						if (isset($files['uploaded']) && !$files['uploaded']) {
							copy($files['tmp_name'], dirname(__FILE__) . $this->path_img_cloud . $filename);
						} else {
							move_uploaded_file($files['tmp_name'], dirname(__FILE__) . $this->path_img_cloud . $filename);
						}

						if (!empty($files['name'])) {
							$this->copyImage(
								array(
									'dir_without_ext' => dirname(__FILE__) . $this->path_img_cloud . $uniq_name_image,
									'name' => dirname(__FILE__) . $this->path_img_cloud . $filename
								)
							);
							$updateAvatar = true;
						}

						$this->saveAvatar(array(
							'id_customer' => $id_customer,
							'avatar' => $filename,
							'avatar_thumb' => $uniq_name_image . '.jpg',
							'show_my_profile' => $data['show_my_profile'],
							'pet_data' => $data,
							'update_avatar' => $updateAvatar
						));
					}
				}
			} else {
				### check  for errors ####
				switch ($files['error']) {
					case '1':
						$error_text = $this->l('The size of the uploaded file exceeds the') . ini_get('upload_max_filesize') . 'b';
						break;
					case '2':
						$error_text = $this->l('The size of  the uploaded file exceeds the specified parameter  MAX_FILE_SIZE in HTML form.');
						break;
					case '3':
						$error_text = $this->l('Loaded only a portion of the file');
						break;
					case '4':
						$error_text = $this->l('The file was not loaded (in the form user pointed the wrong path  to the file). ');
						break;
					case '6':
						$error_text = $this->l('Invalid  temporary directory.');
						break;
					case '7':
						$error_text = $this->l('Error writing file to disk');
						break;
					case '8':
						$error_text = $this->l('File download aborted');
						break;
					default:
						$error_text = $this->l('Unknown error code!');
						break;
				}
				$error = 1;
				########

			}
		} else {
			$this->_updateShowMyProfile(array('show_my_profile' => $data['show_my_profile']));
		}


		//For BO add pet
		$redirect = $this->context->link->getAdminLink('AdminCustomers', false) . '?token=' . Tools::getAdminToken('AdminCustomers' . intval(Tab::getIdFromClassName('AdminCustomers')) . intval($this->context->cookie->id_employee));
		$redirect .= '&viewcustomer&id_customer=' . $this->id_customer . '#mipets';

		return array(
			'error' => $error,
			'error_text' => $error_text,
			'data' => $data,
			'return_add_bo' => $redirect
		);
	}

	private function _is_show($data)
	{

		switch ($data['show_my_profile']) {
			case 'on':
				$show_my_profile = 1;
				break;
			default:
				$show_my_profile = 0;
				break;
		}
		return $show_my_profile;
	}

	private function _updateShowMyProfile($data)
	{
		$cookie = $this->context->cookie;

		$this->id_customer = $cookie->id_customer;

		$show_my_profile = $this->_is_show(array('show_my_profile' => $data['show_my_profile']));

		// if exist record
		$query = 'SELECT COUNT(*) as count from ' . _DB_PREFIX_ . 'avatar2customer 
												WHERE id_customer = "' . (int)$this->id_customer;

		$result = Db::getInstance()->GetRow($query);
		$exist_record = $result['count'];

		if ($exist_record) {
			//update
			$query = 'UPDATE ' . _DB_PREFIX_ . 'avatar2customer SET is_show = "' . (int)$show_my_profile . '
													    WHERE id_customer = "' . (int)$this->id_customer;
		} else {
			//insert
			$query = 'INSERT INTO ' . _DB_PREFIX_ . 'avatar2customer (id_customer, avatar, avatar_thumb,is_show) 
                             VALUES (' . (int)$this->id_customer . ', "", "", ' . (int)$show_my_profile . ') ';
		}

		Db::getInstance()->Execute($query);
	}

	private function _http()
	{

		$http = Tools::getShopDomainSsl(true, true) . __PS_BASE_URI__;


		return $http;
	}

	public function getCustomerInfo($id_customer = null, $pet_reference = null)
	{
		$cookie = $this->context->cookie;

		$exist_avatar = 0;
		$is_show = 0;

		if (($cookie->logged && isset($pet_reference)) || (isset($id_customer) && isset($pet_reference))) {

			$id_customer = isset($id_customer) ? $id_customer : $cookie->id_customer;

			$sql = 'SELECT * FROM `' . _DB_PREFIX_ . 'avatar2customer` 
		        WHERE `id_customer` = "' . (int)$id_customer . '" AND reference = "' . $pet_reference . '"';
			$result = Db::getInstance()->GetRow($sql);
			$avatar = $result['avatar'];
			$avatar_thumb = $result['avatar_thumb'];
			$is_show = isset($result['is_show']) ? $result['is_show'] : 1;

			// Pet data
			$pet_data['pet_reference'] = $result['reference'];
			$pet_data['pet_type'] = $result['type'];
			$pet_data['pet_name'] = $result['name'];
			$pet_data['pet_genre'] = $result['genre'];
			$pet_data['pet_birth'] = $result['birth'];
			$pet_data['pet_breed'] = $result['breed'];
			$pet_data['pet_weight'] = $result['weight'];
			$pet_data['pet_desired_weight'] = $result['desired_weight'];
			$pet_data['pet_feeding'] = $result['feeding'];
			$pet_data['pet_activity'] = $result['activity'];
			$pet_data['pet_physical_condition'] = $result['physical_condition'];
			$pet_data['pet_pathology'] = $result['pathology'];
			$pet_data['pet_allergies'] = $result['allergies'];
			$pet_data['comment'] = $result['comment'];
			$pet_data['message'] = $result['message'];

			// user with avatar
			if (Tools::strlen($avatar_thumb) > 0) {
				//$avatar_thumb = $this->_http."upload/".$avatar_thumb;

				if ($this->_is_cloud) {
					$path_img_cloud = "modules/" . $this->_name . "/upload/";
				} else {
					$path_img_cloud = "img/pets/";
				}

				$avatar_thumb = $this->_http . $path_img_cloud . $avatar_thumb;

				$exist_avatar = 1;
			} else {
				// user without avatar
				$info_customer_db = $this->_getInfoCustomerDB(array('id_customer' => $id_customer));
				switch ($info_customer_db['id_gender']) {
					case 1:
						//male
						$avatar_thumb = $this->_http . "modules/profileadv/views/img/avatar_m.gif";
						break;
					case 2:
						//female
						$avatar_thumb = $this->_http . "modules/profileadv/views/img/avatar_w.gif";
						break;
					default:
						//unknown
						$avatar_thumb = $this->_http . "modules/profileadv/views/img/avatar_n.gif";
						break;
				}
			}

			return array(
				'id_customer' => $id_customer,
				'avatar' => $avatar,
				'avatar_thumb' => $avatar_thumb,
				'exist_avatar' => $exist_avatar,
				'is_show' => $is_show,
				'pet_data' => $pet_data
			);
		}
	}

	public function getLastCustomerIdAddress($id_customer)
	{
		$sql = 'SELECT `id_address`
				FROM `' . _DB_PREFIX_ . 'address`
				WHERE `id_customer` = ' . intval($id_customer) . ' 
					AND `deleted` = 0 
					AND `active` = 1
				ORDER BY id_address DESC
				LIMIT 1';

		$result = Db::getInstance()->ExecuteS($sql);

		return $result;
	}

	private function _getInfoCustomerDB($data)
	{
		$id_customer = $data['id_customer'];
		$sql = 'SELECT * FROM `' . _DB_PREFIX_ . 'customer` 
		        WHERE `id_customer` = ' . (int)$id_customer;

		$result = Db::getInstance()->GetRow($sql);

		return $result;
	}

	public function isAmountBlockedByPetReferenceAndCustomer($reference, $customer)
	{
		$sql = 'SELECT is_amount_blocked FROM `' . _DB_PREFIX_ . 'avatar2customer` 
		WHERE `reference` = "' . pSQL($reference) . '"       
		AND `id_customer` = ' . (int)pSQL($customer);

		$result = Db::getInstance()->GetRow($sql);

		return (int)$result['is_amount_blocked'];
	}

	public function saveAvatar($data = null)
	{

		$id_customer = $data['id_customer'];
		$avatar = $data['avatar'];
		$avatar_thumb = $data['avatar_thumb'];
		$updateAvatar = $data['update_avatar'];

		$petData = [
			'name' => $data['pet_data']['newpetdata']['pet-name'],
			'reference' => $data['pet_data']['newpetdata']['pet-reference'],
			'type' => $data['pet_data']['newpetdata']['pet-type'],
			'genre' => $data['pet_data']['newpetdata']['pet-genre'],
			'birth' => $data['pet_data']['newpetdata']['pet-birth'],
			'breed' => $data['pet_data']['newpetdata']['pet-breed'],
			'esterilized' => $data['pet_data']['newpetdata']['pet-esterilized'],
			'weight' => $data['pet_data']['newpetdata']['pet-weight'],
			'desired-weight' => $data['pet_data']['newpetdata']['pet-desired-weight'],
			'feeding' => $data['pet_data']['newpetdata']['pet-feeding'],
			'activity' => $data['pet_data']['newpetdata']['pet-activity'],
			'physical-condition' => $data['pet_data']['newpetdata']['pet-physical-condition'],
			'pathology' => $data['pet_data']['newpetdata']['pet-pathology'],
			'allergies' => $data['pet_data']['newpetdata']['pet-allergies'],
			'amount' => $data['pet_data']['newpetdata']['pet-amount'],
			'amount-blocked' => $data['pet_data']['newpetdata']['pet-amount-blocked'],
			'message' => isset($data['pet_data']['newpetdata']['pet-message']) && !empty($data['pet_data']['newpetdata']['pet-message']) ? $data['pet_data']['newpetdata']['pet-message'] : null,
			'text' => isset($data['pet_data']['newpetdata']['text']) ? $data['pet_data']['newpetdata']['text'] : null,
			'active' => $data['pet_data']['newpetdata']['action'] === 'addfirstpet' ? 0 : 1, //By default 0 if it is the first pet (until validate)
			'is_validated' => $data['pet_data']['newpetdata']['action'] === 'addfirstpet' ? 0 : 1, //By default 0 if it is the first pet (until validate)
			'validate_token' => isset($data['pet_data']['validate_token']) ? $data['pet_data']['validate_token'] : false //Only for "new pet customers landing"
		];
		$show_my_profile = $this->_is_show(array('show_my_profile' => $data['show_my_profile']));
		$exist_record = false;

		if ($data['pet_data']['action'] !== 'addpet' && $data['pet_data']['action'] !== 'addfirstpet') {
			// if exist record
			$query = 'SELECT COUNT(*) as count from ' . _DB_PREFIX_ . 'avatar2customer 
												WHERE id_customer = ' . (int)$id_customer . ' AND reference = "' . pSQL($petData['reference']) . '"';
			$result = Db::getInstance()->GetRow($query);
			$exist_record = $result['count'];
		}

		$query = "";
		if ($exist_record && $data['pet_data']['action'] === 'editpet') {

			//update
			$query = 'UPDATE ' . _DB_PREFIX_ . 'avatar2customer SET ';
			if ($updateAvatar) {
				$query .= 'avatar =  "' . pSQL($avatar) . '",
    					  avatar_thumb = "' . pSQL($avatar_thumb) . '",';
			}

			$query .= ' type = ' . pSQL((int)$petData['type']) . ', 
						name = "' . pSQL($petData['name']) . '", 
						genre = ' . pSQL((int)$petData['genre']) . ', 
						birth = "' . pSQL($petData['birth']) . '", 
						breed = ' . pSQL((int)$petData['breed']) . ', 
						esterilized = ' . pSQL((int)$petData['esterilized']) . ', 
						weight = ' . pSQL((float)$petData['weight']) . ', 
						desired_weight = ' . pSQL((float)$petData['desired-weight']) . ', 
						feeding = ' . pSQL((int)$petData['feeding']) . ', 
						activity = ' . pSQL((int)$petData['activity']) . ', 
						physical_condition = ' . pSQL((int)$petData['physical-condition']) . ', 
						pathology = "' . pSQL(json_encode($petData['pathology'])) . '", 
						allergies = "' . pSQL(json_encode($petData['allergies'])) . '", ';
			if ((int)$petData['amount'] > 0) {
				$query .= "amount = " . pSQL((int)$petData['amount']) . ", ";
			}
			$query .= ' is_amount_blocked = "' . pSQL($petData['amount-blocked']) . '",
						comment = "' . pSQL($petData['text']) . '",
						message = "' . pSQL($petData['message']) . '",
						active = 1																
					WHERE id_customer = ' . (int)$id_customer . ' AND reference = "' . pSQL($petData['reference']) . '"';
		} else if ($data['pet_data']['action'] === 'addpet' || $data['pet_data']['action'] === 'addfirstpet') {
			if ($data['pet_data']['validate_token']) {
				$query = 'INSERT INTO ' . _DB_PREFIX_ . 'avatar2customer (id_customer, avatar, avatar_thumb,is_show, reference, type, esterilized, name, genre, birth, breed, weight, desired_weight, feeding, activity, physical_condition, pathology, allergies, amount, comment, message, active, is_validated, validate_token) 
							VALUES (' . (int)$id_customer . ', "' . pSQL($avatar) . '", "' . pSQL($avatar_thumb) . '", ' . (int)$show_my_profile . ',"' . pSQL($petData['reference']) . '",' . pSQL((int)$petData['type']) . ',' . pSQL((int)$petData['esterilized']) . ',"' . pSQL($petData['name']) . '",' . pSQL((int)$petData['genre']) . ',"' . pSQL($petData['birth']) . '",' . pSQL((int)$petData['breed']) . ',' . pSQL($petData['weight']) . ',' . pSQL($petData['desired-weight']) . ',' . pSQL((int)$petData['feeding']) . ',' . pSQL((int)$petData['activity']) . ',' . pSQL((int)$petData['physical-condition']) . ',"' . pSQL(json_encode($petData['pathology'])) . '","' . pSQL(json_encode($petData['allergies'])) . '",' . pSQL((int)$petData['amount']) . ',"' . pSQL($petData['text']) . '","' . pSQL($petData['message']) . '","' . pSQL($petData['active']) . '","' . pSQL($petData['is_validated']) . '","' . pSQL($petData['validate_token']) . '")';
			} else {
				$query = 'INSERT INTO ' . _DB_PREFIX_ . 'avatar2customer (id_customer, avatar, avatar_thumb,is_show, reference, type, esterilized, name, genre, birth, breed, weight, desired_weight, feeding, activity, physical_condition, pathology, allergies, amount, comment, message, active, is_validated) 
									 VALUES (' . (int)$id_customer . ', "' . pSQL($avatar) . '", "' . pSQL($avatar_thumb) . '", ' . (int)$show_my_profile . ',"' . pSQL($petData['reference']) . '",' . pSQL((int)$petData['type']) . ',' . pSQL((int)$petData['esterilized']) . ',"' . pSQL($petData['name']) . '",' . pSQL((int)$petData['genre']) . ',"' . pSQL($petData['birth']) . '",' . pSQL((int)$petData['breed']) . ',' . pSQL($petData['weight']) . ',' . pSQL($petData['desired-weight']) . ',' . pSQL((int)$petData['feeding']) . ',' . pSQL((int)$petData['activity']) . ',' . pSQL((int)$petData['physical-condition']) . ',"' . pSQL(json_encode($petData['pathology'])) . '","' . pSQL(json_encode($petData['allergies'])) . '",' . pSQL((int)$petData['amount']) . ',"' . pSQL($petData['text']) . '","' . pSQL($petData['message']) . '","' . pSQL($petData['active']) . '","' . pSQL($petData['is_validated']) . '")';
			}
		}

		Db::getInstance()->Execute($query);
	}

	public function updatePetDataFromBO(array $data)
	{
		$query = 'UPDATE ' . _DB_PREFIX_ . 'avatar2customer SET 
					type = ' . pSQL((int)$data['pet-type']) . ', 
					name = "' . pSQL($data['pet-name']) . '", 
					genre = ' . pSQL((int)$data['pet-genre']) . ', 
					birth = "' . pSQL($data['pet-birth']) . '", 
					breed = ' . pSQL((int)$data['pet-breed']) . ', 
					esterilized = ' . pSQL((int)$data['pet-esterilized']) . ', 
					weight = ' . pSQL((float)$data['pet-weight']) . ', 
					desired_weight = ' . pSQL((float)$data['pet-desired-weight']) . ', 
					feeding = ' . pSQL((int)$data['pet-feeding']) . ', 
					activity = ' . pSQL((int)$data['pet-activity']) . ', 
					physical_condition = ' . pSQL((int)$data['pet-physical-condition']) . ', 
					pathology = "' . pSQL(json_encode($data['pet-pathology'])) . '", 
					allergies = "' . pSQL(json_encode($data['pet-allergies'])) . '", 
					amount = ' . pSQL((int)$data['pet-amount']) . ',
					is_amount_blocked = ' . pSQL((int)$data['pet-amount-blocked']) . ',
					message = "' . pSQL($data['pet-message']) . '"
				WHERE id_customer = ' . (int)pSQL($data['pet-customer']) . ' 
					AND reference = "' . pSQL($data['pet-reference']) . '"';
		if (Db::getInstance()->Execute($query)) {
			return true;
		}

		return false;
	}

	public function deleteAvatar($data = null)
	{

		$id_customer = $data['id_customer'];
		$query = 'DELETE FROM ' . _DB_PREFIX_ . 'avatar2customer 
                              WHERE `id_customer` = ' . (int)$id_customer;

		if (Db::getInstance()->Execute($query)) {
			return true;
		} else {
			return false;
		}
	}

	public function copyImage($data)
	{

		$filename = $data['name'];
		$dir_without_ext = $data['dir_without_ext'];
		$width = $this->_width;
		$height = $this->_height;

		if (!$width) {
			$width = 85;
		};
		if (!$height) {
			$height = 85;
		};
		// Content type
		$size_img = getimagesize($filename);
		// Get new dimensions
		list($width_orig, $height_orig) = getimagesize($filename);
		$ratio_orig = $width_orig / $height_orig;

		if ($width_orig > $height_orig) {
			$height =  $width / $ratio_orig;
		} else {
			$width = $height * $ratio_orig;
		}
		if ($width_orig < $width) {
			$width = $width_orig;
			$height = $height_orig;
		}

		$image_p = imagecreatetruecolor($width, $height);
		$bgcolor = ImageColorAllocate($image_p, 255, 255, 255);
		//   
		imageFill($image_p, 5, 5, $bgcolor);

		if ($size_img[2] == 2) {
			$image = imagecreatefromjpeg($filename);
		} else if ($size_img[2] == 1) {
			$image = imagecreatefromgif($filename);
		} else if ($size_img[2] == 3) {
			$image = imagecreatefrompng($filename);
		}

		imagecopyresampled($image_p, $image, 0, 0, 0, 0, $width, $height, $width_orig, $height_orig);
		// Output
		$users_img = $dir_without_ext . '.jpg';
		if ($size_img[2] == 2)  imagejpeg($image_p, $users_img, 100);
		else if ($size_img[2] == 1)  imagejpeg($image_p, $users_img, 100);
		else if ($size_img[2] == 3)  imagejpeg($image_p, $users_img, 100);
		imageDestroy($image_p);
		imageDestroy($image);
		unlink($filename);
	}


	public function PageNav($data = null)
	{
		$start = $data['start'];
		$count = $data['count'];
		$step = $data['step'];
		$search = isset($data['search']) ? $data['search'] : 0;
		$query = isset($data['query']) ? $data['query'] : '';

		ob_start();
		include(dirname(__FILE__) . '/../views/templates/hooks/' . __FUNCTION__ . '.phtml');
		$res = ob_get_clean();


		return $res;
	}

	public function getLangISO()
	{
		$cookie = $this->context->cookie;
		$id_lang = (int)$cookie->id_lang;

		$all_laguages = Language::getLanguages(true);

		if ($this->isURLRewriting() && sizeof($all_laguages) > 1)
			$iso_lang = Language::getIsoById((int)($id_lang)) . "/";
		else
			$iso_lang = '';

		return $iso_lang;
	}

	public function isURLRewriting()
	{
		$_is_rewriting_settings = 0;
		if (Configuration::get('PS_REWRITING_SETTINGS')) {
			$_is_rewriting_settings = 1;
		}
		return $_is_rewriting_settings;
	}

	public function getSEOURLs()
	{


		$iso_code = $this->getLangISO();


		include_once(_PS_MODULE_DIR_ . $this->_name . '/' . $this->_name . '.php');
		$obj_module = new $this->_name();
		$token = $obj_module->getokencron();


		$delimeter_rewrite = '&';
		if (Configuration::get('PS_REWRITING_SETTINGS')) {
			$delimeter_rewrite = '?';
		}

		$link = new Link();

		$cookie = $this->context->cookie;
		$id_lang = (int)($cookie->id_lang);
		$id_shop = $this->getIdShop();

		$is_ssl = false;
		if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on' || (bool)Configuration::get('PS_SSL_ENABLED'))
			$is_ssl = true;


		if (Configuration::get('PS_REWRITING_SETTINGS')) {
			$shoppers_url = $this->getHttpost() . $iso_code . 'shoppers';
			$shopper_url = $this->getHttpost() . $iso_code . 'shopper?id=';
			$shopperaccount_url = $this->getHttpost() . $iso_code . 'shopperaccount';
			$my_account = $link->getPageLink("my-account", true, $id_lang);
			$pet_list = $link->getModuleLink($this->_name, 'petlist', array(), $is_ssl, $id_lang, $id_shop);
		} else {

			$shoppers_url = $link->getModuleLink($this->_name, 'shoppers', array(), $is_ssl, $id_lang, $id_shop);

			$shopper_url = $link->getModuleLink($this->_name, 'shopper', array(), $is_ssl, $id_lang, $id_shop);
			$shopper_url .= $delimeter_rewrite . 'id=';

			$shopperaccount_url = $link->getModuleLink($this->_name, 'shopperaccount', array(), $is_ssl, $id_lang, $id_shop);
			$my_account = $link->getPageLink("my-account", true, $id_lang);

			$pet_list = $link->getModuleLink($this->_name, 'petlist', array(), $is_ssl, $id_lang, $id_shop);
		}

		$ajax_profile_url = $link->getModuleLink($this->_name, 'ajaxprofileadv', array(), $is_ssl, $id_lang, $id_shop) . $delimeter_rewrite . 'token=' . $token;

		return array(
			'shoppers_url' => $shoppers_url,
			'shopper_url' => $shopper_url,
			'shopperaccount_url' => $shopperaccount_url,
			'my_account' => $my_account,
			'pet_list' => $pet_list,
			'ajax_profile_url' => $ajax_profile_url,

		);
	}

	public function getIdShop()
	{

		$id_shop = Context::getContext()->shop->id;

		return $id_shop;
	}

	public function getHttpost()
	{
		$custom_ssl_var = 0;
		if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on' || (bool)Configuration::get('PS_SSL_ENABLED'))
			$custom_ssl_var = 1;


		if ($custom_ssl_var == 1)
			$_http_host = _PS_BASE_URL_SSL_ . __PS_BASE_URI__;
		else
			$_http_host = _PS_BASE_URL_ . __PS_BASE_URI__;

		return $_http_host;
	}



	public function deleteGDPRCustomerData($email)
	{

		$data_customer = Customer::getCustomersByEmail($email);
		if (count($data_customer) > 0) {
			$id_customer = $data_customer[0]['id_customer'];

			// avatar2customer
			$sql = 'DELETE FROM `' . _DB_PREFIX_ . 'avatar2customer`
		        	WHERE  `customer_id` = "' . (int)$id_customer . '
		        	';
			Db::getInstance()->Execute($sql);
		}

		return true;
	}

	public function getGDPRCustomerData($email)
	{

		$data_customer = Customer::getCustomersByEmail($email);
		$customer_data = array();
		if (count($data_customer) > 0) {
			$id_customer = $data_customer[0]['id_customer'];


			// avatar2customer
			$sql = 'SELECT * FROM `' . _DB_PREFIX_ . 'avatar2customer`
		        	WHERE  `customer_id` = ' . (int)$id_customer . '
		        	';
			$result_statitics = Db::getInstance()->ExecuteS($sql);
			if (count($result_statitics) > 0)
				$customer_data['avatar2customer'] = $result_statitics;
		}
		return $customer_data;
	}

	public function getConsentMessage($id_module, $id_lang)
	{
		$sql = 'SELECT psgdprl.message FROM `' . _DB_PREFIX_ . 'psgdpr_consent` psgdpr
            LEFT JOIN ' . _DB_PREFIX_ . 'psgdpr_consent_lang psgdprl ON (psgdpr.id_gdpr_consent = psgdprl.id_gdpr_consent)
            WHERE psgdpr.id_module = "' . (int)$id_module . ' AND psgdprl.id_lang =' . (int)$id_lang;

		$result = Db::getInstance()->getValue($sql);

		return $result;
	}

	public function getConsentActive($id_module)
	{
		$sql = 'SELECT psgdpr.active FROM `' . _DB_PREFIX_ . 'psgdpr_consent` psgdpr
            WHERE psgdpr.id_module = "' . (int)$id_module;

		$result = (bool) Db::getInstance()->getValue($sql);

		return $result;
	}

	private function _getAssociatedImageFromBreed($type = false, $breed = false)
	{

		$imagesPath = "";
		$files = array();

		switch ($type) {
			case '2':
				$imagesPath = _PS_MODULE_DIR_ . $this->_name . '/views/img/breeds/cat/';
				break;
			default:
				$imagesPath = _PS_MODULE_DIR_ . $this->_name . '/views/img/breeds/dog/';
				break;
		}

		$fileToFind = $imagesPath . $breed . '.jpg';

		if (file_exists($fileToFind)) {
			$files['name'] = $breed . '.jpg';
			$files['type'] = 'image/jpeg';
			$files['tmp_name'] = $fileToFind;
			$files['error'] = 0;
			$files['size'] = filesize($fileToFind);
			$files['uploaded'] = false;
		} else {
			$files['name'] = 'default.jpg';
			$files['type'] = 'image/jpeg';
			$files['tmp_name'] = $imagesPath . 'default.jpg';
			$files['error'] = 0;
			$files['size'] = filesize($imagesPath . 'default.jpg');
			$files['uploaded'] = false;
		}

		return $files;
	}

	public function getPetDataFromReference(string $ref, $customer = false)
	{
		if ($customer) {
			$sql = 'SELECT * FROM `' . _DB_PREFIX_ . 'avatar2customer`
		        	WHERE  `id_customer` = ' . (int)pSQL($customer) . ' AND reference = "' . pSQL($ref) . '"';
		} else {
			$sql = 'SELECT * FROM `' . _DB_PREFIX_ . 'avatar2customer`
		        	WHERE  reference = "' . pSQL($ref) . '"';
		}
		$results = Db::getInstance()->ExecuteS($sql);

		$results[0]['return_customer'] = $this->context->link->getAdminLink('AdminCustomers', false) . '?token=' . Tools::getAdminToken('AdminCustomers' . intval(Tab::getIdFromClassName('AdminCustomers')) . intval($this->context->cookie->id_employee));
		$results[0]['return_customer'] .= '&viewcustomer&id_customer=' . $customer . '#mipets';

		if (count($results) > 0) {
			return $results[0];
		} else {
			return false;
		}
	}

	public function getInitialListPetData()
	{
		$sql = 'SELECT pa.`id`,pa.`avatar_thumb`, pa.`reference`, pa.`type`, pa.`name`, pa.`genre`, pa.`amount`, pa.`id_customer`, pa.`active`, pa.`is_validated`
				FROM `' . _DB_PREFIX_ . 'avatar2customer` pa
					LEFT JOIN `' . _DB_PREFIX_ . 'customer` pc
						ON pa.id_customer = pc.id_customer
				WHERE pa.active = 1
				ORDER BY pa.id DESC
				LIMIT 50';

		return Db::getInstance()->ExecuteS($sql);
	}

	public function getListPetDataFromName($name)
	{

		$sql = 'SELECT pa.`id`,pa.`avatar_thumb`, pa.`reference`, pa.`type`, pa.`name`, pa.`genre`, pa.`amount`, pa.`id_customer`, pa.`active`, pa.`is_validated`
				FROM `' . _DB_PREFIX_ . 'avatar2customer` pa
					LEFT JOIN `' . _DB_PREFIX_ . 'customer` pc
						ON pa.id_customer = pc.id_customer
				WHERE pa.name like "%' . pSQL($name) . '%"
				ORDER BY pa.id DESC';

		return Db::getInstance()->ExecuteS($sql);
	}

	public function getListPetDataFromCustomerEmail($customer)
	{

		$sql = 'SELECT pa.`id`,pa.`avatar_thumb`, pa.`reference`, pa.`type`, pa.`name`, pa.`genre`, pa.`amount`, pa.`id_customer`
				FROM `' . _DB_PREFIX_ . 'avatar2customer` pa
					LEFT JOIN `' . _DB_PREFIX_ . 'customer` pc
						ON pa.id_customer = pc.id_customer
				WHERE pc.email = "' . pSQL($customer) . '"
				ORDER BY pa.id DESC';
		return Db::getInstance()->ExecuteS($sql);
	}

	/**
	 * Retrieve pet list from email populated on message column (new customers)
	 * @param mixed $customer
	 */
	public function getListPetDataFromCustomerEmailOnMessage($customer)
	{

		$sql = 'SELECT pa.`reference`
				FROM `' . _DB_PREFIX_ . 'avatar2customer` pa
				WHERE pa.message = "' . pSQL($customer) . '"
				ORDER BY pa.id DESC';
		var_dump($sql);
		return Db::getInstance()->ExecuteS($sql);
	}


	public function getListPetDataWithoutAmount()
	{
		$sql = 'SELECT pa.`id`,pa.`avatar_thumb`, pa.`reference`, pa.`type`, pa.`name`, pa.`genre`, pa.`amount`, pa.`id_customer`, pa.`message`
				FROM `' . _DB_PREFIX_ . 'avatar2customer` pa
					LEFT JOIN `' . _DB_PREFIX_ . 'customer` pc
						ON pa.id_customer = pc.id_customer
				WHERE pa.amount = 0
				AND pa.date_add between "2025-03-20" and NOW()
				ORDER BY pa.id DESC';
		return Db::getInstance()->ExecuteS($sql);
	}

	public function getPetListToValidate(): array
	{
		$last_day = date('Y-m-d 00:00:01', strtotime('-1000 days'));
		$current_day = date('Y-m-d H:m:s', strtotime('-12 hours'));

		$sql = 'SELECT pa.`id`,pa.`avatar_thumb`, pa.`reference`, pa.`type`, pa.`name`, pa.`genre`, pa.`amount`, pa.`message`, pa.`sended_email`, pa.`date_add`, pa.`active`, pa.`is_validated`
				FROM `' . _DB_PREFIX_ . 'avatar2customer` pa
				WHERE pa.`id_customer` = 1
				AND pa.`active` = 0
				AND pa.`is_validated` = 0
				AND pa.`sended_email` < 5
				AND pa.`date_add` between "' . pSQL($last_day) . '" and "' . pSQL($current_day) . '"
				GROUP BY pa.`message`, pa.`name` 
				ORDER BY pa.id DESC';

		return Db::getInstance()->ExecuteS($sql);
	}

	public function getValidationTokenFromReference($reference): mixed
	{
		$sql = 'SELECT pa.validate_token
				FROM `' . _DB_PREFIX_ . 'avatar2customer` pa
				WHERE pa.reference = "' . pSQL($reference) . '"';
		$result = Db::getInstance()->ExecuteS($sql);

		return count($result) > 0 ? $result[0]['validate_token'] : 0;
	}

	public function checkValidationTokenFromReference($reference, $token): bool
	{
		$sql = 'SELECT pa.reference, pa.validate_token
				FROM `' . _DB_PREFIX_ . 'avatar2customer` pa
				WHERE pa.reference = "' . pSQL($reference) . '" AND pa.validate_token = "' . pSQL($token) . '" AND is_validated = 0';
		$result = Db::getInstance()->ExecuteS($sql);

		return count($result) > 0 ? true : false;
	}

	public function validatePetFromReference(string $reference, int $id_customer): bool
	{
		$sql = 'UPDATE ' . _DB_PREFIX_ . 'avatar2customer SET id_customer = ' . pSQL($id_customer) . ', active = 1, is_validated = 1, message ="" WHERE reference = "' . pSQL($reference) . '"';
		if (!Db::getInstance()->execute($sql))
			return false;
		return true;
	}

	public function getIdCustomerFromPetReference($reference): mixed
	{
		$sql = 'SELECT pa.id_customer
				FROM `' . _DB_PREFIX_ . 'avatar2customer` pa
				WHERE pa.reference = "' . pSQL($reference) . '"';

		$result = Db::getInstance()->ExecuteS($sql);

		return count($result) > 0 && (int)$result[0]['id_customer'] > 1 ? (int)$result[0]['id_customer'] : false;
	}

	public function customerIdExists($id_customer)
	{
		$row = Db::getInstance()->getRow('
		SELECT `id_customer`
		FROM ' . _DB_PREFIX_ . 'customer c
		WHERE c.`id_customer` = ' . intval($id_customer));

		return isset($row['id_customer']);
	}

	public function getNotValidatedPetsFromDate(string $date): mixed
	{
		$sql = 'SELECT pa.id
				FROM `' . _DB_PREFIX_ . 'avatar2customer` pa
				WHERE pa.date_add < "' . pSQL($date) . '" AND is_validated = 0 ORDER BY id ASC';

		$result = Db::getInstance()->ExecuteS($sql);

		return count($result) > 0 ? $result : false;
	}

	public function getNotValidatedPetsBetweenDates(string $from, string $to, int $sendedState): mixed
	{
		$sql = 'SELECT *
				FROM `' . _DB_PREFIX_ . 'avatar2customer` pa
				WHERE pa.date_add BETWEEN "' . pSQL($to) . '" AND "' . pSQL($from) . '" AND is_validated = 0 AND sended_email != ' . $sendedState . ' AND sended_email != 5 GROUP BY message ORDER BY id ASC';

		$result = Db::getInstance()->ExecuteS($sql);

		return count($result) > 0 ? $result : false;
	}

	public function updatePetCustomerSendedEmail(string $reference, int $sended_email): void
	{
		$sql = 'UPDATE ' . _DB_PREFIX_ . 'avatar2customer SET sended_email = ' . pSQL($sended_email) . ' WHERE reference = "' . pSQL($reference) . '"';

		$result = Db::getInstance()->execute($sql) ? true : false;
		echo $result;
	}

	public function deleteNotValidatedPets(array $list): bool
	{
		$query = 'DELETE FROM ' . _DB_PREFIX_ . 'avatar2customer 
		WHERE `id` IN (' . implode(",", $list) . ')';

		if (Db::getInstance()->Execute($query)) {
			return true;
		} else {
			return false;
		}
	}
}
