<?php

require ADMIN."classes/pdfcrowd.php";

/**
 * @param $r1
 * @param $r2
 * @return int
 */
function customRateSort($r1, $r2 ) {
	if ($r1['net'] == $r2['net'])
		return 0;
	return ($r1['net'] < $r2['net']) ? -1 : 1;
}

/**
 * @param $r1
 * @param $r2
 * @return int
 */
function customServiceSort($r1, $r2 ) {
	if ($r1['total'] == $r2['total'])
		return 0;
	return ($r1['total'] < $r2['total']) ? -1 : 1;
}

/**
 * Handles some custom functionality for frontend
 */
class custom extends Frontend {

	protected $m_dir = '';
	protected $module;
	protected $m_fields = array();

    /**
     * @param $id
     * @param $module
     * @throws Exception
     */
    public function __construct($id, $module = array()) {
		parent::__construct();
		$this->m_dir = ADMIN.'frontend/forms/custom/';
		$this->m_moduleId = $id;
		$this->m_module = $module;
		$this->m_fields = array(
			// special cases for internal use only [duped in Frontend and used for backend config]
			'getFileList'=>array(	
				'options'=>array('private'=>true),
				'files'=>array('type'=>'select','name'=>'file_list')
			),
			'getModuleInfo'=>array(
				'options'=>array('private'=>true),
				'functions'=>array('type'=>'select','name'=>'module_function')
			),
			'forgotPassword'=>array(
				'forgotPassword'=>array('type'=>'hidden','value'=>1),
				'username'=>array('type'=>'textfield','required'=>true,'placeholder'=>'Username','class'=>'form-control'),
				't_id'=>array('type'=>'hidden','value'=>'%%module:fetemplate_id%%'),
				'submit'=>array('type'=>'submitButton','value'=>'SEND AGAIN','class'=>'btn btn-primary form-control')
			),
			'contactUs'=>array(
				'subject'=>array('type'=>'textfield','required'=>true,'prettyName'=>'Subject','placeholder'=>'Subject','class'=>'form-control'),
				'email'=>array('type'=>'textfield','required'=>true,'validation'=>'email','placeholder'=>'Email','class'=>'form-control'),
				'message'=>array('type'=>'textarea','required'=>true,'wrap'=>'virtual','placeholder'=>'Message','class'=>'form-control'),
				'phone'=>array('type'=>'textfield','required'=>true,'prettyName'=>'Phone #','placeholder'=>'Phone #','class'=>'form-control'),
				'name'=>array('type'=>'textfield','required'=>true,'prettyName'=>'Name','placeholder'=>'Name','class'=>'form-control'),
				'company'=>array('type'=>'textfield','required'=>true,'prettyName'=>'Company','placeholder'=>'Company','class'=>'form-control'),
				'submit'=>array('type'=>'submitbutton','value'=>'Send','class'=>'btn btn-default form-control'),
				'contactUs'=>array('type'=>'hidden','value'=>1),
				'captcha'=>array('type'=>'captcha','required'=>true,'database'=>false,'validation'=>'captcha','prettyName'=>'Validation text'),
				'r_id'=>array('type'=>'hidden','value'=>random_int(1, 1000000)),
				'r_secret'=>array('type'=>'tag','value'=>0,'persist'=>true),
				't_id'=>array('type'=>'hidden','value'=>'%%module:fetemplate_id%%')
			),
			'addToCart'=>array(
				'quantity'=>array('type'=>'textfield','name'=>'product_quantity','value'=>1,'required'=>true,'validation'=>'number'),
				'product_id'=>array('type'=>'hidden','value'=>'%%id%%'),
				'addToCart'=>array('type'=>'hidden','value'=>1),
				'recurring_period'=>array('type'=>'select','required'=>false,'name'=>'recurring_period','required'=>false,'nonename'=>'Select Autoship and Save'),
				'recurring_qty'=>array('type'=>'textfield','required'=>false,'name'=>'recurring_qty','validation'=>'number','value'=>0,'min'=>0),
				'currency_id'=>array('type'=>'select','idlookup'=>'currencies','required'=>true)
			),
			'login'=>array(
				'username'=>array('type'=>'textfield','required'=>true,'prettyName'=>'Username','placeholder'=>'USERNAME','class'=>'form-control'),
				'password'=>array('type'=>'password','required'=>true,'prettyName'=>'Password','placeholder'=>'PASSWORD','class'=>'form-control'),
				't_id'=>array('type'=>'hidden','value'=>'%%module:fetemplate_id%%'),
				'loginForm'=>array('type'=>'hidden','value'=>1)
			),
			'mobilelogin'=>array(
				'username'=>array('type'=>'textfield','required'=>true,'prettyName'=>'Username','placeholder'=>'USERNAME','class'=>'form-control black-bg'),
				'password'=>array('type'=>'password','required'=>true,'prettyName'=>'Password','placeholder'=>'Password','class'=>'form-control toggle black-bg'),
				'submit'=>array('type'=>'submitButton','value'=>'Sign In','class'=>'btn red-bg form-control'),
				'loginForm'=>array('type'=>'hidden','value'=>1)
			),
			'signup'=>array(
				'password'=>array('type'=>'password','required'=>true,'prettyName'=>'Password','class'=>'def_field_input form-control','validation'=>'password','placeholder'=>'Password'),
				'password_confirm'=>array('type'=>'password','required'=>true,'prettyName'=>'Password Confirm','class'=>'def_field_input form-control','database'=>false,'placeholder'=>'Password Confirm'),
				'company'=>array('type'=>'textfield','required'=>true,'prettyName'=>'Company','class'=>'def_field_input form-control','placeholder'=>'Company Name'),
				'firstname'=>array('type'=>'textfield','required'=>true,'prettyName'=>'First Name','class'=>'def_field_input form-control','placeholder'=>'First Name'),
				'lastname'=>array('type'=>'textfield','required'=>true,'prettyName'=>'Last Name','class'=>'def_field_input form-control','placeholder'=>'Last Name'),
				'country_id'=>array('name'=>'address[country_id]','type'=>'select','required'=>true,'sql'=>'select id,country from countries where deleted = 0 order by sort','id'=>'country_id','class'=>'form-control'),
				'province_id'=>array('name'=>'address[province_id]','type'=>'provinceSelect','required'=>true,'sql'=>'select p.id,p.province from provinces p, countries c where p.deleted = 0 and p.country_id = c.id and c.sort = (select min(c1.sort)  from countries c1 where c1.deleted = 0 order by c1.sort) order by p.sort','id'=>'province_id','prettyName'=>'Province/State','class'=>'form-control'),
				'city'=>array('name'=>'address[city]','type'=>'textfield','required'=>true,'prettyName'=>'City','class'=>'form-control','placeholder'=>'City'),
				'line1'=>array('name'=>'address[line1]','type'=>'textfield','required'=>true,'prettyName'=>'Address Line 1','class'=>'form-control','placeholder'=>'Address Line 1'),
				'line2'=>array('name'=>'address[line2]','type'=>'textfield','required'=>false,'class'=>'form-control','placeholder'=>'Unit/Suite #'),
				'postalcode'=>array('name'=>'address[postalcode]','type'=>'textfield','required'=>true,'prettyName'=>'Postal/Zip','validation'=>'postalcode','class'=>'form-control','placeholder'=>'Postal Code'),
				'phone1'=>array('type'=>'textfield','required'=>true,'prettyName'=>'Phone','name'=>'address[phone1]','class'=>'form-control','placeholder'=>'Phone Number'),
				'fax'=>array('type'=>'textfield','required'=>false,'name'=>'address[fax]','class'=>'form-control','placeholder'=>'Fax Number'),
				'email'=>array('type'=>'textfield','required'=>true,'validation'=>'email','prettyName'=>'E-Mail','class'=>'def_field_input','class'=>'form-control','placeholder'=>'Email'),
				'email_confirm'=>array('type'=>'textfield','required'=>true,'validation'=>'email','prettyName'=>'E-Mail Confirm','database'=>false,'class'=>'form-control','placeholder'=>'Email Confirm'),
				'username'=>array('type'=>'textfield','placeholder'=>'Login Name','class'=>'form-control','required'=>true),
				'username_confirm'=>array('type'=>'textfield','placeholder'=>'Login Name Confirm','class'=>'form-control','required'=>true,'database'=>false),
				'custom_pickup_notification'=>array('type'=>'checkbox','value'=>1,'required'=>false,'checked'=>'checked','class'=>'form-control'),
				'custom_delivery_notification'=>array('type'=>'checkbox','value'=>1,'required'=>false,'checked'=>'checked','class'=>'form-control'),
				'signupForm'=>array('type'=>'hidden','value'=>1,'database'=>false),
				'button'=>array('type'=>'submitButton','value'=>'REGISTER NOW','database'=>false,'class'=>'btn btn-primary'),
				'm_id'=>array('type'=>'hidden','database'=>false),
				'a_id'=>array('type'=>'hidden','database'=>false),
				't_id'=>array('type'=>'hidden','database'=>false,'value'=>'%%module:fetemplate_id%%'),
				'folder_id'=>array('type'=>'hidden','value'=>'%%module:folder_id%%','database'=>false),
				'captcha'=>array('type'=>'captcha','required'=>true,'database'=>false,'validation'=>'captcha','prettyName'=>'Validation text'),
				'options'=>array('name'=>'signup','method'=>'post','action'=>'')
			),
			'shippingAddress'=>array(
				'company'=>array('type'=>'textfield','disabled'=>true,'prettyName'=>'Company','class'=>'form-control'),
				'firstname'=>array('type'=>'textfield','disabled'=>true,'class'=>'form-control'),
				'lastname'=>array('type'=>'textfield','disabled'=>true,'class'=>'form-control'),
				'email'=>array('type'=>'textfield','disabled'=>true,'class'=>'form-control'),
				'line1'=>array('type'=>'textfield','disabled'=>true,'class'=>'form-control'),
				'line2'=>array('type'=>'textfield','disabled'=>true,'class'=>'form-control'),
				'city'=>array('type'=>'textfield','disabled'=>true,'class'=>'form-control'),
				'company'=>array('type'=>'textfield','disabled'=>true,'prettyName'=>'Company','class'=>'form-control'),
				'phone1'=>array('type'=>'textfield','disabled'=>true,'class'=>'form-control'),
				'postalcode'=>array('type'=>'textfield','disabled'=>true,'class'=>'form-control'),
				'country'=>array('type'=>'textfield','disabled'=>true,'class'=>'form-control'),
				'province'=>array('type'=>'textfield','disabled'=>true,'database'=>false,'class'=>'form-control'),
				'edit'=>array('type'=>'hidden','value'=>'Edit','database'=>false),
				'editButton'=>array('type'=>'button','value'=>'Edit','onclick'=>'editMe(this);return false;'),
				'addressId'=>array('type'=>'hidden','database'=>false),
				'addresstype'=>array('type'=>'hidden'),
				'tax_address'=>array('type'=>'hidden'),
				'shippingAddressForm'=>array('type'=>'hidden','database'=>false,'value'=>1),
				'country_id'=>array('type'=>'hidden'),
				'province_id'=>array('type'=>'hidden'),
				'residential'=>array('type'=>'checkbox','disabled'=>true,'class'=>'form-control'),
				'address_book'=>array('type'=>'checkbox','disabled'=>true,'class'=>'form-control'),
				'proceed'=>array('type'=>'button','value'=>'Proceed to Package','onclick'=>'shippingHere(%%id%%);return false;')
			),
			'shippingAddressEdit'=>array(
				'company'=>array('type'=>'textfield','required'=>true,'prettyName'=>'Company'),
				'firstname'=>array('type'=>'textfield','required'=>false),
				'lastname'=>array('type'=>'textfield','required'=>false),
				'email'=>array('type'=>'textfield','required'=>false,'validation'=>'email'),
				'line1'=>array('type'=>'textfield','required'=>true),
				'line2'=>array('type'=>'textfield','required'=>true),
				'city'=>array('type'=>'textfield','required'=>true),
				'country_id'=>array('type'=>'countrySelect','required'=>true,'id'=>'country_id'),
				'province_id'=>array('type'=>'provinceSelect','required'=>true,'prettyName'=>'Province/State'),
				'company'=>array('type'=>'textfield','required'=>false,'prettyName'=>'Company'),
				'phone1'=>array('type'=>'textfield','required'=>true),
				'postalcode'=>array('type'=>'textfield','required'=>true,'validation'=>'postalcode'),
				'addressId'=>array('type'=>'hidden','database'=>false),
				'addresstype'=>array('type'=>'hidden'),
				'tax_address'=>array('type'=>'hidden'),
				'saveAddress'=>array('type'=>'hidden','value'=>'saveAddress','database'=>false),
				'saveButton'=>array('type'=>'button','value'=>'Save','database'=>false,'onclick'=>'editMe(this);return false;'),
				'shippingAddressForm'=>array('type'=>'hidden','database'=>false,'value'=>1),
				'province'=>array('type'=>'hidden','database'=>false),
				'residential'=>array('type'=>'checkbox','value'=>1,'class'=>'form-control'),
				'address_book'=>array('type'=>'checkbox','value'=>1,'class'=>'form-control'),
				'country'=>array('type'=>'hidden','database'=>false)
			),
			'billingAddress'=>array(
				'company'=>array('type'=>'textfield','disabled'=>false,'prettyName'=>'Company','class'=>'form-control'),
				'firstname'=>array('type'=>'textfield','disabled'=>true,'class'=>'form-control'),
				'lastname'=>array('type'=>'textfield','disabled'=>true,'class'=>'form-control'),
				'email'=>array('type'=>'textfield','disabled'=>true,'validation'=>'email','class'=>'form-control'),
				'line1'=>array('type'=>'textfield','disabled'=>true,'class'=>'form-control'),
				'line2'=>array('type'=>'textfield','disabled'=>true,'class'=>'form-control'),
				'city'=>array('type'=>'textfield','disabled'=>true,'class'=>'form-control'),
				'company'=>array('type'=>'textfield','disabled'=>true,'prettyName'=>'Company','class'=>'form-control'),
				'phone1'=>array('type'=>'textfield','disabled'=>true,'class'=>'form-control'),
				'postalcode'=>array('type'=>'textfield','disabled'=>true,'class'=>'form-control'),
				'country'=>array('type'=>'textfield','disabled'=>true,'class'=>'form-control'),
				'province'=>array('type'=>'textfield','disabled'=>true,'class'=>'form-control'),
				'edit'=>array('type'=>'hidden','value'=>'Edit','database'=>false),
				'editButton'=>array('type'=>'button','value'=>'Edit','onclick'=>'editMe(this);return false;'),
				'addressId'=>array('type'=>'hidden','database'=>false),
				'addresstype'=>array('type'=>'hidden'),
				'tax_address'=>array('type'=>'hidden'),
				'pickupAddressForm'=>array('type'=>'hidden','database'=>false,'value'=>1),
				'country_id'=>array('type'=>'hidden'),
				'province_id'=>array('type'=>'hidden'),
				'residential'=>array('type'=>'checkbox','disabled'=>true,'class'=>'form-control'),
				'address_book'=>array('type'=>'checkbox','disabled'=>true,'class'=>'form-control'),
				'proceed'=>array('type'=>'button','value'=>'Proceed to Shipping Address','onclick'=>'pickupHere(%%id%%);return false;')
			),
			'billingAddressEdit'=>array(
				'company'=>array('type'=>'textfield','required'=>true,'prettyName'=>'Company'),
				'firstname'=>array('type'=>'textfield','required'=>false),
				'lastname'=>array('type'=>'textfield','required'=>false),
				'email'=>array('type'=>'textfield','required'=>false,'validation'=>'email'),
				'line1'=>array('type'=>'textfield','required'=>true,'prettyName'=>'Address'),
				'line2'=>array('type'=>'textfield','required'=>true),
				'city'=>array('type'=>'textfield','required'=>true),
				'country_id'=>array('type'=>'countrySelect','required'=>true,'id'=>'country_id'),
				'province_id'=>array('type'=>'provinceSelect','required'=>true,'prettyName'=>'Province/State'),
				'company'=>array('type'=>'textfield','required'=>false,'prettyName'=>'Company'),
				'phone1'=>array('type'=>'textfield','required'=>true,'prettyName'=>'Phone'),
				'postalcode'=>array('type'=>'textfield','required'=>true,'prettyName'=>'Postal/Zip Code'),
				'addressId'=>array('type'=>'hidden','database'=>false),
				'addresstype'=>array('type'=>'hidden'),
				'tax_address'=>array('type'=>'hidden'),
				'saveAddress'=>array('type'=>'hidden','value'=>'saveAddress','database'=>false),
				'saveButton'=>array('type'=>'button','value'=>'Save','database'=>false,'onclick'=>'editMe(this);return false;'),
				'billingAddressForm'=>array('type'=>'hidden','database'=>false,'value'=>1),
				'pickupAddressForm'=>array('type'=>'hidden','database'=>false,'value'=>1),
				'province'=>array('type'=>'hidden','database'=>false),
				'residential'=>array('type'=>'checkbox','value'=>1,'class'=>'form-control'),
				'address_book'=>array('type'=>'checkbox','value'=>1,'class'=>'form-control'),
				'country'=>array('type'=>'hidden','database'=>false)
			),
			'accountInfo'=>array(
				'addressId'=>array('type'=>'hidden','database'=>false),
				'editBtn'=>array('type'=>'link','href'=>'#','onclick'=>'fnSubmit(this,0);return false;','value'=>'Edit Account Details','database'=>false),
				'options'=>array('name'=>'accountInfo','method'=>'post','action'=>''),
				'company'=>array('type'=>'textfield','prettyName'=>'Company','disabled'=>true,'class'=>'form-control'),
				'firstname'=>array('type'=>'textfield','prettyName'=>'First Name','disabled'=>true,'class'=>'form-control'),
				'lastname'=>array('type'=>'textfield','prettyName'=>'Last Name','disabled'=>true,'class'=>'form-control'),
				'phone1'=>array('type'=>'textfield','prettyName'=>'Phone','name'=>'address[phone1]','disabled'=>true,'class'=>'form-control'),
				'line1'=>array('name'=>'address[line1]','type'=>'textfield','prettyName'=>'Address Line 1','disabled'=>true,'class'=>'form-control'),
				'country_id'=>array('name'=>'address[country_id]','type'=>'countrySelect','disabled'=>true,'class'=>'form-control'),
				'province_id'=>array('name'=>'address[province_id]','type'=>'provinceSelect','id'=>'province_id','disabled'=>true,'class'=>'form-control'),
				'city'=>array('name'=>'address[city]','type'=>'textfield','prettyName'=>'City','disabled'=>true,'class'=>'form-control'),
				'postalcode'=>array('name'=>'address[postalcode]','type'=>'textfield','prettyName'=>'Postal/Zip','disabled'=>true,'class'=>'form-control'),
				'residential'=>array('type'=>'booleanIcon'),
				'email'=>array('type'=>'textfield','validation'=>'email','prettyName'=>'Email','disabled'=>true,'class'=>'form-control'),
				'emailConfirm'=>array('type'=>'textfield','validation'=>'email','prettyName'=>'E-Mail','database'=>false,'disabled'=>true,'class'=>'form-control'),
				'password'=>array('type'=>'password','validation'=>'password','prettyName'=>'Password','database'=>false,'disabled'=>true,'class'=>'form-control'),
				'passwordConfirm'=>array('type'=>'password','validation'=>'password','prettyName'=>'Password','database'=>false,'disabled'=>true,'class'=>'form-control'),
				'saveInfo'=>array('type'=>'hidden','value'=>'0','database'=>false),
				'edit'=>array('type'=>'submitbutton','value'=>'Edit Information','class'=>'btn btn-red fontsize10',"onclick"=>"eAccount(this);return false;"),
				'custom_pickup_notification'=>array('type'=>'checkbox','disabled'=>true,'class'=>'form-control'),
				'custom_delivery_notification'=>array('type'=>'checkbox','disabled'=>true,'class'=>'form-control'),
				'accountInfoForm'=>array('type'=>'hidden','value'=>'1','database'=>false)
			),
			'accountInfoEdit'=>array(
				'company'=>array('type'=>'textfield','prettyName'=>'Company','class'=>'form-control','placeholder'=>'Company'),
				'firstname'=>array('type'=>'textfield','required'=>true,'prettyName'=>'First Name','placeholder'=>'First Name','class'=>'form-control'),
				'lastname'=>array('type'=>'textfield','required'=>true,'prettyName'=>'Last Name','placeholder'=>'Last Name','class'=>'form-control'),
				'country_id'=>array('name'=>'address[country_id]','type'=>'countrySelect','required'=>true,'sql'=>'select id,country from countries where deleted = 0 order by sort','id'=>'country_id','class'=>'form-control'),
				'province_id'=>array('name'=>'address[province_id]','type'=>'provinceSelect','required'=>true,'sql'=>'select p.id,p.province from provinces p, countries c where p.deleted = 0 and p.country_id = c.id and c.sort = (select min(c1.sort)  from countries c1 where c1.deleted = 0 order by c1.sort) order by p.sort','id'=>'province_id','prettyName'=>'Province/State','class'=>'form-control'),
				'city'=>array('name'=>'address[city]','type'=>'textfield','required'=>true,'prettyName'=>'City','placeholder'=>'City','class'=>'form-control'),
				'line1'=>array('name'=>'address[line1]','type'=>'textfield','required'=>true,'prettyName'=>'Address Line 1','placeholder'=>'Line 1','class'=>'form-control'),
				'line2'=>array('name'=>'address[line2]','type'=>'textfield','required'=>false,'placeholder'=>'Unit/Suite #','class'=>'form-control'),
				'postalcode'=>array('name'=>'address[postalcode]','type'=>'textfield','required'=>true,'prettyName'=>'Postal/Zip','placeholder'=>'Postal Code','validation'=>'postalcode','class'=>'form-control'),
				'phone1'=>array('type'=>'textfield','required'=>true,'prettyName'=>'Phone','name'=>'address[phone1]','placeholder'=>'Phone','class'=>'form-control'),
				'residential'=>array('type'=>'checkbox','value'=>1,'class'=>'form-control'),
				'fax'=>array('type'=>'textfield','required'=>false,'name'=>'address[fax]','placeholder'=>'Fax','class'=>'form-control'),
				'email'=>array('type'=>'textfield','required'=>true,'validation'=>'email','prettyName'=>'Email','placeholder'=>'Email','class'=>'form-control'),
				'emailConfirm'=>array('type'=>'textfield','required'=>false,'validation'=>'email','prettyName'=>'E-Mail','database'=>false,'placeholder'=>'Email Confirm','class'=>'form-control'),
				'password'=>array('type'=>'password','required'=>false,'validation'=>'password','prettyName'=>'Password','database'=>false,'class'=>'form-control'),
				'passwordConfirm'=>array('type'=>'password','required'=>false,'validation'=>'password','prettyName'=>'Password','database'=>false,'class'=>'form-control'),
				'custom_pickup_notification'=>array('type'=>'checkbox','value'=>1,'required'=>false,'class'=>'form-control'),
				'custom_delivery_notification'=>array('type'=>'checkbox','value'=>1,'required'=>false,'class'=>'form-control'),
				'accountInfoForm'=>array('type'=>'hidden','value'=>1,'database'=>false),
				'addressId'=>array('type'=>'hidden','database'=>false),
				'saveBtn'=>array('type'=>'submitbutton','value'=>'Save Information','class'=>'btn btn-red fontsize10',"onclick"=>"eAccount(this);return false;"),
				'saveInfo'=>array('type'=>'hidden','value'=>'1','database'=>false),
				'options'=>array('name'=>'accountInfo','method'=>'post','action'=>'')
			),
			'updateCart'=>array(
				'discountCode'=>array('type'=>'textfield','required'=>false,'value'=>'%%discount_code%%'),
				'quantity'=>array('type'=>'textfield','name'=>'quantity[%%key%%]','value'=>'%%quantity%%'),
				'recurring_period'=>array('type'=>'select','required'=>false,'name'=>'recurring_period[%%key%%]','required'=>false,'nonename'=>'Select Autoship And Save'),
				'recurring_qty'=>array('type'=>'hidden','required'=>false,'name'=>'recurring_qty[%%key%%]','value'=>'%%quantity%%'),
				'remove'=>array('type'=>'checkbox','required'=>false,'name'=>'removeProduct[%%key%%]','value'=>'1'),
				'updateCart'=>array('type'=>'hidden','value'=>1)
			),
			'userTesting'=>array(
				'email'=>array('type'=>'textfield','required'=>true,'validation'=>'email'),
				'member_id'=>array('type'=>'textfield','required'=>true,'validation'=>'number'),
				'userTesting'=>array('type'=>'hidden','value'=>1),
				'o_id'=>array('type'=>'textfield','validation'=>'number','required'=>false),
				'submit'=>array('type'=>'submitbutton')
			),
			'getQuote'=>array(
				'pickup_datetime'=>array('required'=>true,'type'=>'datetimepicker','AMPM'=>true,'value'=>date("Y-m-d H:i:00"),'onchange'=>'getDateOptions(this);return false;'),
				'custom_weight_code'=>array('type'=>'select','idlookup'=>'weights','required'=>true,'class'=>'form-control'),
				'custom_dimension_code'=>array('type'=>'select','idlookup'=>'dimensions','required'=>true,'class'=>'form-control'),
				'serviceType'=>array('type'=>'select','required'=>true,'class'=>'form-control','onchange'=>'serviceChange()'),
				'pkgTypes'=>array('type'=>'select'),
				'getQuote'=>array('type'=>'hidden','value'=>1)
			),
			'getQuoteRow'=>array(
				'product_id'=>array('type'=>'select','name'=>'prod[%%row%%][product_id]','required'=>true,'class'=>'def_field_select product form-control'),
				'quantity'=>array('type'=>'textfield','required'=>true,'validation'=>'number','name'=>'prod[%%row%%][quantity]','prettyName'=>'Package [%%row%%] Quantity','value'=>'','onchange'=>'dimensions(this);','class'=>'def_field_input quantity form-control input-sm','readonly'=>'readonly'),
				'custom_weight'=>array('type'=>'textfield','required'=>true,'validation'=>'number','name'=>'prod[%%row%%][custom_weight]','prettyName'=>'Package [%%row%%] Weight','value'=>'0','class'=>'def_field_input weight form-control input-sm','readonly'=>'readonly'),
				'sequence'=>array('type'=>'hidden','value'=>'%%row%%','name'=>'sequence')
			),
			'selectService'=>array(
				'custom_weight_code'=>array('type'=>'select','idlookup'=>'weights','required'=>true,'class'=>'form-control'),
				'custom_dimension_code'=>array('type'=>'select','idlookup'=>'dimensions','required'=>true,'class'=>'form-control'),
				'pickup_datetime'=>array('required'=>true,'type'=>'datetimepicker','AMPM'=>true,'value'=>date("Y-m-d H:i:00"),'onchange'=>'getDateOptions(this);return false;'),
				'serviceType'=>array('type'=>'select','required'=>true,'class'=>'form-control','onchange'=>'serviceChange()'),
				'selectService'=>array('type'=>'hidden','value'=>1),
				'optSameDay'=>array('type'=>'radiobutton','name'=>'optType','required'=>false,'class'=>'form-control','value'=>'S'),
				'optOvernight'=>array('type'=>'radiobutton','name'=>'optType','required'=>false,'class'=>'form-control','value'=>'O'),
				'pickupInstructions'=>array('type'=>'textarea','required'=>false,'class'=>'form-control','rows'=>4),
				'deliveryInstructions'=>array('type'=>'textarea','required'=>false,'class'=>'form-control','rows'=>4),
				'customs_declaration'=>array('type'=>'textarea','required'=>false,'class'=>'form-control','rows'=>4,'validation'=>'custom:customsDocs'),
				'custom_reference_number'=>array('type'=>'textfield','required'=>false,'class'=>'form-control'),
				'custom_declared_value'=>array('type'=>'textfield','required'=>false,'class'=>'form-control','validation'=>'number','prettyName'=>'Declared Value'),
				'custom_insurance'=>array('type'=>'checkbox','required'=>false,'class'=>'form-control','value'=>1),
				'custom_signature_required'=>array('type'=>'checkbox','required'=>false,'class'=>'form-control','value'=>1),
				'custom_recurring'=>array('type'=>'checkbox','value'=>1,'class'=>'form-control','onclick'=>'toggleRecurring(this);'),
				'custom_override_price'=>array('type'=>'textfield', 'class'=>'form-control text-right', 'validation'=>'number', 'required'=>false, 'value'=>'0.00'),
				'extras'=>array('type'=>'select','required'=>false,'name'=>'extras[]','class'=>'form-control','multiple'=>true,'onchange'=>'setExtraOptions(this);'),
				'dry_ice_weight'=>array('type'=>'number','required'=>false,'validation'=>'custom:dryIceWeight','class'=>'form-control text-right','value'=>0,'step'=>'.1','min'=>'0.0','prettyName'=>'Dry Ice Weight'),
				'custom_placed_by'=>array('type'=>'textfield','required'=>true,'prettyName' => 'Placed By','class'=>'form-control','value'=>'%%session:quote:custom_placed_by%%','validation'=>'custom:checkName'),
				'custom_pickup_email'=>array('type'=>'textfield','required'=>true,'prettyName' => 'Confirmation Email','class'=>'form-control','value'=>'%%session:quote:custom_email_confirmation%%','validation'=>'email','required'=>false),
				'custom_email_confirmation'=>array('type'=>'textfield','required'=>true,'prettyName' => 'Confirmation Email','class'=>'form-control','value'=>'%%session:quote:custom_email_confirmation%%','validation'=>'email','required'=>false),
				'custom_recurring_pu_driver'=>array('type'=>'select','class'=>'form-control','sql'=>'select d.id, concat(company, "-", lastname," ",firstname) from members m, drivers d where m.id = d.member_id and m.deleted = 0 and m.enabled = 1 and d.deleted = 0 and d.enabled = 1 order by 2'),
				'custom_recurring_del_driver'=>array('type'=>'select','class'=>'form-control','sql'=>'select d.id, concat(company, "-", lastname," ",firstname) from members m, drivers d where m.id = d.member_id and m.deleted = 0 and m.enabled = 1 and d.deleted = 0 and d.enabled = 1 order by 2'),
				'custom_recurring_type'=>array('type'=>'select','lookup'=>'recurrenceType','required'=>false,'id'=>'recurringType','name'=>'recurring[type]','class'=>'form-control','onchange'=>'getRecurringInfo(this);return false;','disabled'=>'true'),
				'optional_field_check'=>array('type'=>'hidden','database'=>false,'validation'=>'custom:optionalFieldCheck','value'=>'1','required'=>false)
			),
			'selectServiceRow'=>array(
				'product_id'=>array('type'=>'select','name'=>'prod[%%row%%][product_id]','required'=>true,'class'=>'def_field_select product form-control'),
				'quantity'=>array('type'=>'hidden','required'=>true,'validation'=>'number','name'=>'prod[%%row%%][quantity]','prettyName'=>'Package [%%row%%] Quantity','value'=>'','onchange'=>'dimensions(this);','class'=>'def_field_input quantity','readonly'=>'readonly'),
				'custom_weight'=>array('type'=>'hidden','required'=>true,'validation'=>'number','name'=>'prod[%%row%%][custom_weight]','prettyName'=>'Package [%%row%%] Weight','value'=>'0','class'=>'def_field_input weight','readonly'=>'readonly'),
				'sequence'=>array('type'=>'hidden','value'=>'%%row%%','name'=>'sequence')
			),
			'selectServiceDimensions'=>array(
				'sequence'=>array('type'=>'hidden'),
				'quantity'=>array('type'=>'textfield','class'=>'text-right input-sm quantity form-control','name'=>'prod[%%row%%][dimensions][%%seq%%][quantity]','value'=>'%%qty%%','required'=>true,'validation'=>'number','onchange'=>'setQty(this);return false;','min'=>0),
				'weight'=>array('type'=>'textfield','class'=>'text-right input-sm weight form-control','name'=>'prod[%%row%%][dimensions][%%seq%%][weight]','value'=>'%%wt%%','required'=>true,'validation'=>'number','onchange'=>'setQty(this);return false;','min'=>0),
				'height'=>array('type'=>'textfield','class'=>'text-right input-sm height form-control','name'=>'prod[%%row%%][dimensions][%%seq%%][height]','value'=>'%%ht%%','required'=>true,'validation'=>'number','min'=>0),
				'width'=>array('type'=>'textfield','class'=>'text-right input-sm width form-control','name'=>'prod[%%row%%][dimensions][%%seq%%][width]','value'=>'%%wd%%','required'=>true,'validation'=>'number','min'=>0),
				'depth'=>array('type'=>'textfield','class'=>'text-right input-sm depth form-control','name'=>'prod[%%row%%][dimensions][%%seq%%][depth]','value'=>'%%dp%%','required'=>true,'validation'=>'number','min'=>0)
			),
			'selectServiceExtras'=>array(
				'extras'=>array('type'=>'select','sql'=>sprintf('select p.id,name from product p, product_by_folder pf where pf.folder_id = %d and p.id = pf.product_id order by pf.sequence', CONSUMER_DELIVERY_SURCHARGES),'required'=>false,'name'=>'extras[]','class'=>'form-control','multiple'=>true)
			),
			'KJVService'=>array(
				'pickup_datetime'=>array('required'=>true,'type'=>'datetimepicker','AMPM'=>true,'value'=>date("Y-m-d H:i:00")),
				'serviceType'=>array('type'=>'select','required'=>true,'onchange'=>'serviceChange()'),
				'pkgTypes'=>array('type'=>'select'),
				'KJVService'=>array('type'=>'hidden','value'=>1),
				'custom_weight_code'=>array('type'=>'hidden'),
				'custom_dimension_code'=>array('type'=>'hidden')
			),
			'KJVServiceRow'=>array(
				'product_id'=>array('type'=>'hidden','name'=>'prod[%%row%%][product_id]'),
				'quantity'=>array('type'=>'textfield','required'=>true,'validation'=>'number','name'=>'prod[%%row%%][quantity]','prettyName'=>'Package [%%row%%] Quantity','value'=>'','onchange'=>'dimensions(this);','class'=>'def_field_input quantity form-control input-sm','readonly'=>'readonly'),
				'custom_weight'=>array('type'=>'textfield','required'=>true,'validation'=>'number','name'=>'prod[%%row%%][custom_weight]','prettyName'=>'Package [%%row%%] Weight','value'=>'0','class'=>'def_field_input weight form-control input-sm','readonly'=>'readonly'),
				'sequence'=>array('type'=>'hidden','value'=>'%%row%%','name'=>'sequence')
			),
			'FedExService'=>array(
				'pickup_datetime'=>array('required'=>true,'type'=>'datetimepicker','AMPM'=>true,'value'=>date("Y-m-d H:i:00")),
				'serviceType'=>array('type'=>'select','required'=>true,'onchange'=>'serviceChange()'),
				'pkgTypes'=>array('type'=>'select'),
				'FedExService'=>array('type'=>'hidden','value'=>1),
				'custom_weight_code'=>array('type'=>'hidden'),
				'custom_dimension_code'=>array('type'=>'hidden')
			),
			'FedExServiceRow'=>array(
				'product_id'=>array('type'=>'hidden','name'=>'prod[%%row%%][product_id]'),
				'quantity'=>array('type'=>'textfield','required'=>true,'validation'=>'number','name'=>'prod[%%row%%][quantity]','prettyName'=>'Package [%%row%%] Quantity','value'=>'','onchange'=>'dimensions(this);','class'=>'def_field_input quantity form-control input-sm','readonly'=>'readonly'),
				'custom_weight'=>array('type'=>'textfield','required'=>true,'validation'=>'number','name'=>'prod[%%row%%][custom_weight]','prettyName'=>'Package [%%row%%] Weight','value'=>'0','class'=>'def_field_input weight form-control input-sm','readonly'=>'readonly'),
				'sequence'=>array('type'=>'hidden','value'=>'%%row%%','name'=>'sequence')
			),
			'editAddress'=>array(
				'company'=>array('type'=>'textfield','required'=>false,'class'=>'form-control','placeholder'=>'Company','validation'=>'custom:notSpaces','prettyName'=>'Company or Name'),
				'phone1'=>array('type'=>'textfield','required'=>true,'prettyName'=>'Phone Number','class'=>'form-control','placeholder'=>'Phone'),
				'line1'=>array('type'=>'textfield','required'=>true,'prettyName'=>'Line 1','class'=>'form-control','placeholder'=>'Address Line 1'),
				'line2'=>array('type'=>'textfield','required'=>false,'prettyName'=>'Unit/Suite #','class'=>'form-control','placeholder'=>'Unit/Suite #'),
				'firstname'=>array('type'=>'textfield','required'=>false,'prettyName'=>'Contact First Name','class'=>'form-control','placeholder'=>'First Name'),
				'lastname'=>array('type'=>'textfield','required'=>false,'prettyName'=>'Contact Last Name','class'=>'form-control','placeholder'=>'Last Name'),
				'city'=>array('type'=>'textfield','required'=>true,'prettyName'=>'City','class'=>'form-control','placeholder'=>'City'),
				'email'=>array('type'=>'textfield','required'=>false,'prettyName'=>'Email','validation'=>'email','class'=>'form-control','placeholder'=>'E-mail'),
				'country_id'=>array('name'=>'country_id','type'=>'countrySelect','required'=>true,'sql'=>'select id,country from countries where deleted = 0 order by sort','id'=>'country_id','class'=>'form-control','placeholder'=>'Country'),
				'province_id'=>array('name'=>'province_id','type'=>'provinceSelect','required'=>true,'sql'=>'select p.id,p.province from provinces p, countries c where p.deleted = 0 and p.country_id = c.id and c.sort = (select min(c1.sort)  from countries c1 where c1.deleted = 0 order by c1.sort) order by p.sort','id'=>'province_id','prettyName'=>'Province/State','class'=>'form-control'),
				'postalcode'=>array('name'=>'postalcode','type'=>'textfield','required'=>true,'prettyName'=>'Postal/Zip','validation'=>'postalcode','class'=>'form-control','placeholder'=>'Postal Code'),
				'residential'=>array('type'=>'checkbox','value'=>1,'class'=>'form-control'),
				'addressEditing'=>array('name'=>'addressEditing','type'=>'hidden','value'=>1,'database'=>false),
				'address_book'=>array('type'=>'checkbox','class'=>'form-control','value'=>'1','checked'=>'checked'),
				'a_id'=>array('name'=>'a_id','type'=>'hidden','value'=>"%%id%%",'database'=>false),
				'save'=>array('name'=>'save','type'=>'submitbutton','value'=>'Save the Changes','database'=>false,'class'=>'btn btn-black form-control')
			),
			'driverSchedule'=>array(
				'driverSchedule'=>array('type'=>'hidden','value'=>1),
				'pager'=>array('type'=>'select','required'=>true,'lookup'=>'paging','id'=>'pager','class'=>'form-control','onchange'=>'repage(this);return false;'),
				'pagenum'=>array('type'=>'hidden','value'=>1),
				'lat'=>array('type'=>'hidden','required'=>false),
				'long'=>array('type'=>'hidden','required'=>false),
				't_id'=>array('type'=>'hidden','value'=>'%%module:fetemplate_id%%')
			),
			'driverScheduleRow'=>array(
				'scheduled_date'=>array('type'=>'timestamp')
			),
			'serviceInfo'=>array(
			),
			'pickupInfo'=>array(
				'delivery_name'=>array('type'=>'textfield','required'=>false,'class'=>'form-control','validation'=>'custom:validatePickup'),
				'comments'=>array('type'=>'textarea','required'=>false,'class'=>'form-control'),
				'completed'=>array('type'=>'checkbox','value'=>1),
				'd_id'=>array('type'=>'hidden','value'=>'%%id%%','name'=>'c_id','database'=>false),
				'btn'=>array('type'=>'submitButton','value'=>'COMPLETE PICKUP','class'=>'btn red-bg form-control','database'=>false),
				'actual_date'=>array('type'=>'datetimepicker','required'=>false,'template'=>'<div class="row"><div class="col-sm-8 form-group"><label>Actual Date/Time</label>%%date%%</div><div class="col-sm-8 form-group"><label>&nbsp;</label>%%time%%</div></div>','class'=>'def_field_datetimepicker form-control','AMPM'=>true,'validation'=>'datetime','value'=>date('Y-m-d h:i a')),
				'lat'=>array('type'=>'hidden','required'=>false,'value'=>'0.0','database'=>false),
				'long'=>array('type'=>'hidden','required'=>false,'value'=>'0.0','database'=>false),
				'pickupInfo'=>array('type'=>'hidden','value'=>1,'database'=>false)
			),
			'pickupInfoRow'=>array(
			),
			'deliveryInfo'=>array(
				'comments'=>array('type'=>'textarea','class'=>'form-control'),
				'd_id'=>array('type'=>'hidden','value'=>'%%id%%','name'=>'c_id','database'=>false),
				'btn'=>array('type'=>'submitButton','value'=>'COMPLETE DELIVERY','class'=>'btn red-bg form-control','database'=>false),
				'deliveryInfo'=>array('type'=>'hidden','value'=>1,'database'=>false)
			),
			'editpackageLine'=>array(
				'editPackageLine'=>array('type'=>'hidden','value'=>1,'database'=>false),
				'product_id'=>array('type'=>'select','required'=>true,'class'=>'form-control','db'=>'line'),
				'weight'=>array('type'=>'textfield','validation'=>'number','required'=>true,'class'=>'text-right form-control','db'=>'dim'),
				'height'=>array('type'=>'textfield','validation'=>'number','required'=>true,'class'=>'text-right form-control','db'=>'dim'),
				'depth'=>array('type'=>'textfield','validation'=>'number','required'=>true,'class'=>'text-right form-control','db'=>'dim'),
				'width'=>array('type'=>'textfield','validation'=>'number','required'=>true,'class'=>'text-right form-control','db'=>'dim'),
				'quantity'=>array('type'=>'number','validation'=>'number','required'=>true,'class'=>'form-control text-right','db'=>'dim'),
				'o_id'=>array('type'=>'hidden','database'=>false),
				'l_id'=>array('type'=>'hidden','database'=>false),
				'c_id'=>array('type'=>'hidden','database'=>false,'value'=>'%%request:c_id%%'),
				't_id'=>array('type'=>'hidden','database'=>false,'value'=>'%%module:fetemplate_id%%'),
				'cancel'=>array('type'=>'hidden','database'=>false,'value'=>0),
				'delete'=>array('type'=>'hidden','database'=>false,'value'=>0)
			),
			'editPackageLineSuccess'=>array(
				'editPackageLine'=>array('type'=>'hidden','value'=>1,'database'=>false)
			),
			'editServiceLine'=>array(
				'editServiceLine'=>array('type'=>'hidden','value'=>1,'database'=>false),
				'l_id'=>array('type'=>'hidden','database'=>false),
				'o_id'=>array('type'=>'hidden','database'=>false),
				'c_id'=>array('type'=>'hidden','value'=>'%%request:c_id%%','database'=>false),
				'product_id'=>array('type'=>'select','required'=>true,'class'=>'form-control','db'=>'line'),
				'quantity'=>array('type'=>'number','validation'=>'number','required'=>true,'class'=>'form-control text-right','db'=>'line','min'=>'1'),
				'delete'=>array('type'=>'hidden','database'=>false,'value'=>0),
				'cancel'=>array('type'=>'hidden','database'=>false,'value'=>0)
			),
			'editServiceLineSuccess'=>array(
				'editServiceLine'=>array('type'=>'hidden','value'=>1,'database'=>false)
			),
			'pickupComments'=>array(
				'pickupComments'=>array('type'=>'hidden','value'=>1,'database'=>false),
				'comments'=>array('type'=>'textarea','required'=>false,'class'=>'form-control'),
				'cancel'=>array('type'=>'hidden','database'=>false,'value'=>0)
			),
			'pickupCommentsSuccess'=>array(
				'pickupComments'=>array('type'=>'hidden','value'=>1,'database'=>false),
				'cancel'=>array('type'=>'hidden','database'=>false,'value'=>0)
			),
			'completePickup'=>array(
				'completePickup'=>array('type'=>'hidden','value'=>1,'database'=>false)
			),
			'captureSignature'=>array(
				'captureSignature'=>array('type'=>'hidden','value'=>1),
				'actual_date'=>array('type'=>'datetimepicker','required'=>false,'template'=>'<div class="col-xs-8">%%date%%</div><div class="col-xs-8">%%time%%</div>','class'=>'def_field_datetimepicker form-control','AMPM'=>true,'validation'=>'datetime','value'=>date('Y-m-d h:i a')),
				'scheduled_date'=>array('type'=>'datetimepicker','required'=>false,'template'=>'<div class="col-xs-8">%%date%%</div><div class="col-xs-8">%%time%%</div>','class'=>'def_field_datetimepicker form-control','AMPM'=>true,'validation'=>'datetime','value'=>date('Y-m-d h:i a')),
				'do_not_bill'=>array('type'=>'checkbox','value'=>1,'required'=>false,'class'=>'form-control'),
				'lat'=>array('type'=>'hidden','required'=>false,'value'=>'0.0','database'=>false),
				'long'=>array('type'=>'hidden','required'=>false,'value'=>'0.0','database'=>false),
				'delivery_name'=>array('type'=>'textfield','required'=>true,'class'=>'form-control','placeholder'=>'Type your name here','prettyName'=>'Delivery Typed Name')
			),
			'orderHistory'=>array(
				'orderHistory'=>array('type'=>'hidden','value'=>1),
				'startDate'=>array('type'=>'datepicker','required'=>false,'class'=>'form-control def_field_datepicker'),
				'endDate'=>array('type'=>'datepicker','required'=>false,'class'=>'form-control def_field_datepicker'),
				'pickupAddress'=>array('type'=>'textfield','required'=>false,'class'=>'form-control'),
				'deliveryAddress'=>array('type'=>'textfield','required'=>false,'class'=>'form-control'),
				'serviceType'=>array('type'=>'select','class'=>'form-control','required'=>false,'nonename'=>'-Any-','multiple'=>true),
				'packageType'=>array('type'=>'select','class'=>'form-control','required'=>false,'nonename'=>'-Any-'),
				'pagenum'=>array('type'=>'hidden','value'=>1),
				'onTime'=>array('type'=>'checkbox','value'=>1,'prettyName'=>'Late Delivery','class'=>'form-control'),
				'extras'=>array('type'=>'select','class'=>'form-control'),
				'order_id'=>array('type'=>'number','required'=>false,'prettyName'=>'Order #','class'=>'form-control'),
				'exportBtn'=>array('type'=>'button','value'=>'Export','class'=>'form-control btn btn-red','onclick'=>'exportCSV(this);return false;'),
				'export'=>array('type'=>'hidden','value'=>0),
				'order_status'=>array('type'=>'select','lookup'=>'orderStatus','required'=>false,'class'=>'form-control'),
				'submitBtn'=>array('type'=>'button','value'=>'Search','class'=>'form-control btn btn-red','onclick'=>'setCompany(this);return false;')
			),
			"orderHistoryCSV"=>array(
			),
			'mgmtCheck'=>array(
				'custom_weight_code'=>array('type'=>'select','idlookup'=>'weights','required'=>true,'class'=>'form-control'),
				'custom_dimension_code'=>array('type'=>'select','idlookup'=>'dimensions','required'=>true,'class'=>'form-control'),
				'pickup_datetime'=>array('required'=>true,'type'=>'datetimepicker','AMPM'=>true,'value'=>date("Y-m-d H:i:00")),
				'serviceType'=>array('type'=>'select','required'=>true,'class'=>'form-control')
			),
			'driverPayout'=>array(
				'driverPayout'=>array('type'=>'hidden','value'=>1),
				'startDate'=>array('type'=>'datepicker','required'=>true,'class'=>'form-control def_field_datepicker','prettyName'=>'Start Date'),
				'endDate'=>array('type'=>'datepicker','required'=>true,'class'=>'form-control def_field_datepicker','prettyName'=>'End Date'),
				'serviceType'=>array('type'=>'select','class'=>'form-control','required'=>false,'nonename'=>'-Any-','multiple'=>true,'sql'=>sprintf('select p.id,name from product p, product_by_folder pf where pf.folder_id = %d and p.id = pf.product_id and p.enabled = 1 and p.deleted = 0 order by pf.sequence',DELIVERY_TYPES)),
				'submit'=>array('type'=>'submitButton','class'=>'button','value'=>'Search','onclick'=>'getPayouts(this);return false;'),
				'pagenum'=>array('type'=>'hidden','value'=>0),
				'actual_date'=>array('type'=>'datetimestamp','required'=>false),
				'payment'=>array('type'=>'currency','required'=>false)
			),
			'waybill'=>array(
				'actual_date'=>array('type'=>'datetimestamp'),
				'scheduled_date'=>array('type'=>'datetimestamp'),
				'created'=>array('type'=>'datetimestamp')
			),
			'resetDates'=>array(
				'resetDates'=>array('type'=>'hidden','value'=>1),
				'resetBtn'=>array('type'=>'button','value'=>'Reset Dates','onclick'=>'resetAllDates(this);return false;','class'=>'btn btn-warning')
			),
			'driverSearch'=>array(
				'driverSearch'=>array('type'=>'hidden','value'=>1),
				'order_id'=>array('type'=>'number','required'=>false,'class'=>'form-control','placeholder'=>'Order #','min'=>0),
				'driver_id'=>array('type'=>'select','required'=>false,'sql'=>'select d.id, concat(company, "-", lastname," ",firstname) from drivers d, members m where m.id = d.member_id and d.enabled=1 and d.deleted = 0 order by 2','class'=>'form-control','placeholder'=>'Driver'),
				'submitButton'=>array('type'=>'button','value'=>'Search','class'=>'btn','onclick'=>'dSearch(this);return false;'),
				'address'=>array('type'=>'textfield','required'=>false,'placeholder'=>'Address','class'=>'form-control')
			),
			'creditApplication'=>array(
				'contactUs'=>array('type'=>'hidden','value'=>1),
				'r_id'=>array('type'=>'hidden','value'=>random_int(1, 1000000)),
				'company'=>array('type'=>'textfield','name'=>'company','required'=>true,'class'=>'form-control','placeholder'=>'Company' ),
				'incorporated'=>array('type'=>'checkbox','value'=>0,'class'=>'form-control','checked'=>false,'checkType'=>1,'placeholder'=>'' ),
				'partnership'=>array('type'=>'checkbox','value'=>0,'class'=>'form-control','checked'=>false,'checkType'=>1,'placeholder'=>'' ),
				'soleProprietor'=>array('type'=>'checkbox','value'=>0,'class'=>'form-control','checked'=>false,'checkType'=>1,'placeholder'=>'' ),
				'line1'=>array('type'=>'textfield','required'=>true,'class'=>'form-control','prettyName'=>'Address Line 1','placeholder'=>'Address' ),
				'suite'=>array('type'=>'textfield','required'=>false,'class'=>'form-control','placeholder'=>'Suite' ),
				'city'=>array('type'=>'textfield','required'=>true,'class'=>'form-control','placeholder'=>'City' ),
				'country_id'=>array('type'=>'countrySelect','required'=>true,'class'=>'form-control','prettyName'=>'Country','placeholder'=>'Country'),
				'province_id'=>array('type'=>'provinceSelect','required'=>true,'class'=>'form-control','prettyName'=>'Province/State','placeholder'=>'Province'),
				'postalCode'=>array('type'=>'textfield','required'=>true,'class'=>'form-control','prettyName'=>'Postal/Zip Code','placeholder'=>'Postal/Zip Code'),
				'phone'=>array('type'=>'textfield','required'=>true,'class'=>'form-control','placeholder'=>'Phone'),
				'fax'=>array('type'=>'textfield','required'=>false,'class'=>'form-control','placeholder'=>'Fax'),
				'email'=>array('type'=>'textfield','required'=>true,'validation'=>'email','class'=>'form-control','placeholder'=>'Email'),
				'contact'=>array('type'=>'textfield','required'=>true,'class'=>'form-control','prettyName'=>'Contact Name','placeholder'=>'Contact Name'),
				'contactExt'=>array('type'=>'textfield','required'=>false,'class'=>'form-control','placeholder'=>'Contact Ext'),
				'apContact'=>array('type'=>'textfield','required'=>true,'class'=>'form-control','prettyName'=>'AP Contact','placeholder'=>'A/P Contact'),
				'apExt'=>array('type'=>'textfield','required'=>false,'class'=>'form-control','placeholder'=>'A/P Ext'),
				'businessType'=>array('type'=>'textfield','required'=>true,'class'=>'form-control','prettyName'=>'Business Type','placeholder'=>'Business Type'),
				'heardFrom'=>array('type'=>'textfield','required'=>true,'class'=>'form-control','prettyName'=>'Heard From','placeholder'=>'Heard From'),
				'expectedBillings'=>array('type'=>'textfield','required'=>true,'validation'=>'number','class'=>'form-control','prettyName'=>'Expected Billings','placeholder'=>'Expected Billings'),
				'deliveries'=>array('type'=>'textfield','required'=>true,'validation'=>'number','class'=>'form-control','placeholder'=>'# of Deliveries'),
				'owner1'=>array('type'=>'textfield','required'=>false,'class'=>'form-control','placeholder'=>'Owner #1'),
				'owner2'=>array('type'=>'textfield','required'=>false,'class'=>'form-control','placeholder'=>'Owner #2'),
				'owner3'=>array('type'=>'textfield','required'=>false,'class'=>'form-control','placeholder'=>'Owner #3'),
				'comments'=>array('type'=>'textarea','required'=>false,'class'=>'form-control','placeholder'=>''),
				'bankName'=>array('type'=>'textfield','required'=>true,'class'=>'form-control','prettyName'=>'Bank Name','placeholder'=>'Bank Name'),
				'bankAddress'=>array('type'=>'textfield','required'=>true,'class'=>'form-control','prettyName'=>'Bank Address','placeholder'=>'Bank Address'),
				'bankManager'=>array('type'=>'textfield','required'=>true,'class'=>'form-control','prettyName'=>'Bank Manager','placeholder'=>'Bank Manager'),
				'bankPhone'=>array('type'=>'textfield','required'=>true,'class'=>'form-control','prettyName'=>'Bank Phone','placeholder'=>'Bank Phone'),
				'trName1'=>array('type'=>'textfield','required'=>false,'class'=>'form-control','placeholder'=>'Name','placeholder'=>'Ref #1 Company'),
				'trName2'=>array('type'=>'textfield','required'=>false,'class'=>'form-control','placeholder'=>'Name','placeholder'=>'Ref #2 Company'),
				'trName3'=>array('type'=>'textfield','required'=>false,'class'=>'form-control','placeholder'=>'Name','placeholder'=>'Ref #3 Company'),
				'trAddress1'=>array('type'=>'textfield','required'=>false,'class'=>'form-control','placeholder'=>'Address','placeholder'=>'Ref #1 Address'),
				'trAddress2'=>array('type'=>'textfield','required'=>false,'class'=>'form-control','placeholder'=>'Address','placeholder'=>'Ref #2 Address'),
				'trAddress3'=>array('type'=>'textfield','required'=>false,'class'=>'form-control','placeholder'=>'Address','placeholder'=>'Ref #3 Address'),
				'trContact1'=>array('type'=>'textfield','required'=>false,'class'=>'form-control','placeholder'=>'Contact','placeholder'=>'Ref #1 Name'),
				'trContact2'=>array('type'=>'textfield','required'=>false,'class'=>'form-control','placeholder'=>'Contact','placeholder'=>'Ref #2 Name'),
				'trContact3'=>array('type'=>'textfield','required'=>false,'class'=>'form-control','placeholder'=>'Contact','placeholder'=>'Ref #3 Name'),
				'trPhone1'=>array('type'=>'textfield','required'=>false,'class'=>'form-control','placeholder'=>'Phone','placeholder'=>'Ref #1 Phone'),
				'trPhone2'=>array('type'=>'textfield','required'=>false,'class'=>'form-control','placeholder'=>'Phone','placeholder'=>'Ref #2 Phone'),
				'trPhone3'=>array('type'=>'textfield','required'=>false,'class'=>'form-control','placeholder'=>'Phone','placeholder'=>'Ref #3 Phone'),
				'submitBtn'=>array('type'=>'submitbutton','value'=>'Apply Today','class'=>'btn btn-large')
			),
			'dispatching'=>array(
				'dispatching'=>array('type'=>'hidden','value'=>1),
				'pager'=>array('type'=>'select','required'=>true,'value'=>25,'lookup'=>'paging','id'=>'pager','class'=>'form-control'),
				'pagenum'=>array('type'=>'hidden','value'=>1),
				'completed'=>array('type'=>'select','lookup'=>'boolean', 'required'=>true, 'class'=>'form-control','value'=>0),
				'product_id'=>array('type'=>'select','required'=>false,'class'=>'form-control','sql'=>'select id, name from product where custom_same_day = 1 order by name'),
				'driver_id'=>array('type'=>'select','required'=>false,'class'=>'form-control','sql'=>'select 0 as id,"Unassigned" as code union (select d.id, concat(company, "-", lastname," ",firstname) as code from drivers d, members m where m.id = d.member_id and d.enabled = 1 and d.deleted = 0) order by if(id=0,id,code)','value'=>0),
				'i_delay'=>array('type'=>'select','lookup'=>'refresh_rate','required'=>true,'class'=>'form-control'),
				'group_id'=>array('type'=>'select','idlookup'=>'driver_groups','required'=>false,'class'=>'form-control'),
				'pu_date'=>array('type'=>'select','required'=>true,'class'=>'form-control'),
				'query'=>array('type'=>'button','class'=>'btn btn-primary form-control','value'=>'Apply Filter','onclick'=>'getResults(this);return false;')
			),
			'dispatchingRow'=>array(
				'scheduled_date'=>array('type'=>'datetimestamp','mask'=>'d-M h:i a'),
				'actual_date'=>array('type'=>'datetimestamp','mask'=>'d-M h:i a'),
				'custom_signature_required'=>array('type'=>'boolean')
			),
			'overnightDeliveries'=>array(
				'overnightDeliveries'=>array('type'=>'hidden','value'=>1),
				'i_delay'=>array('type'=>'select','lookup'=>'refresh_rate','required'=>true,'class'=>'form-control'),
				'pager'=>array('type'=>'select','required'=>true,'lookup'=>'paging','id'=>'pager','class'=>'form-control','value'=>25),
				'pagenum'=>array('type'=>'hidden','value'=>1),
				'completed'=>array('type'=>'select','lookup'=>'boolean', 'required'=>true, 'class'=>'form-control','value'=>0),
				'product_id'=>array('type'=>'select','required'=>false,'class'=>'form-control','sql'=>'select id, name from product where custom_same_day = 0 and deleted = 0 and enabled = 1 order by name'),
				'driver_id'=>array('type'=>'select','required'=>false,'class'=>'form-control','sql'=>'select 0 as id,"Unassigned" as code union (select d.id, concat(company, "-", lastname," ",firstname) as code from drivers d, members m where m.id = d.member_id and d.enabled = 1 and d.deleted = 0) order by if(id=0,id,code)'),
				'query'=>array('type'=>'button','class'=>'btn btn-primary form-control','value'=>'Apply Filter','onclick'=>'getResults(this);return false;')
			),
			'overnightDeliveriesRow'=>array(
				'scheduled_date'=>array('type'=>'datetimestamp','mask'=>'d-M h:i a'),
				'actual_date'=>array('type'=>'datetimestamp','mask'=>'d-M h:i a'),
				'custom_signature_required'=>array('type'=>'boolean')
			),
			'accountSearch'=>array(
				'accountSearch'=>array('type'=>'hidden','value'=>1),
				'sd'=>array('type'=>'datepicker', 'required'=>false, 'class'=>'form-control def_field_datepicker','prettyName'=>'Start Date','placeholder'=>'Pick a Date' ),
				'ed'=>array('type'=>'datepicker', 'required'=>false, 'class'=>'form-control def_field_datepicker','prettyName'=>'End Date','placeholder'=>'Pick a Date' ),
				'product_id'=>array('type'=>'select','required'=>false,'class'=>'form-control'),
				'pager'=>array('type'=>'select','required'=>true,'value'=>5,'lookup'=>'paging','id'=>'pager','class'=>'form-control'),
				'pagenum'=>array('type'=>'hidden','value'=>1),
				'query'=>array('type'=>'submitButton','class'=>'btn btn-primary','value'=>'Search'),
				'order_id'=>array('type'=>'input','class'=>'form-control','required'=>false),
				'address_id'=>array('type'=>'select','required'=>false,'class'=>'form-control'),
				't_id'=>array('type'=>'hidden','value'=>'%%module:fetemplate_id%%')
			),
			'recurring'=>array(),
			'recurringDaily'=>array(
				'recurring_frequency'=>array('type'=>'select','id'=>'recurringFrequency','name'=>'recurring[frequency]','sequence'=>'1|31','class'=>'form-control'),
				'recurring_weekdays'=>array('type'=>'hidden','value'=>0,'name'=>'recurring[weekdays]','id'=>'recurrencePattern'),
				'recurring_by_position'=>array('type'=>'hidden','value'=>0,'name'=>'recurring[by_position]'),
				'recurring_position'=>array('type'=>'hidden','value'=>0,'name'=>'recurring[position]')
			),
			'recurringWeekly'=>array(
				'recurring_frequency'=>array('type'=>'select','id'=>'recurringFrequency','name'=>'recurring[frequency]','sequence'=>'1|52','class'=>'form-control'),
				'recurring_weekdays'=>array('type'=>'select','lookup'=>'recurringWeekdays','required'=>true,'name'=>'recurring[weekdays]','id'=>'recurrencePattern','prettyName'=>'Recurring Weekdays','class'=>'form-control'),
				'recurring_by_position'=>array('type'=>'hidden','value'=>0,'name'=>'recurring[by_position]'),
				'recurring_position'=>array('type'=>'hidden','value'=>0,'name'=>'recurring[position]')
			),
			'recurringMonthly'=>array(
				'recurring_frequency'=>array('type'=>'select','id'=>'recurringFrequency','name'=>'recurring[frequency]','sequence'=>'1|12','class'=>'form-control'),
				'recurring_weekdays'=>array('type'=>'select','lookup'=>'recurringWeekdays','required'=>true,'name'=>'recurring[weekdays]','id'=>'recurrencePattern','class'=>'form-control'),
				'recurring_position'=>array('type'=>'select','lookup'=>'monthPosition','required'=>false,'id'=>'recurringPosition','name'=>'recurring[position]','class'=>'form-control'),
				'recurring_by_position'=>array('type'=>'checkbox','id'=>'recurringByPosition','value'=>1,'required'=>true,'name'=>'recurring[by_position]','checked'=>'false')
			),
			'overnightPickups'=>array(
				'overnightPickups'=>array('type'=>'hidden','value'=>1),
				'pager'=>array('type'=>'select','required'=>true,'lookup'=>'paging','id'=>'pager','class'=>'form-control'),
				'pagenum'=>array('type'=>'hidden','value'=>1),
				'completed'=>array('type'=>'select','lookup'=>'boolean', 'required'=>true, 'class'=>'form-control'),
				'i_delay'=>array('type'=>'select','lookup'=>'refresh_rate','required'=>true,'class'=>'form-control'),
				'product_id'=>array('type'=>'select','required'=>false,'class'=>'form-control','sql'=>'select id, name from product where custom_same_day = 0 and deleted = 0 and enabled = 1 order by name'),
				'driver_id'=>array('type'=>'select','required'=>false,'class'=>'form-control','sql'=>'select 0 as id,"Unassigned" as code union (select d.id, concat(company, "-", lastname," ",firstname) as code from drivers d, members m where m.id = d.member_id and d.enabled = 1 and d.deleted = 0) order by if(id=0,id,code)'),
				'query'=>array('type'=>'button','class'=>'form-control btn btn-primary','value'=>'Apply Filter','onclick'=>'getResults(this);return false;')
			),
			'overnightPickupsRow'=>array(
				'scheduled_date'=>array('type'=>'datetimestamp','mask'=>'d-M h:i a'),
				'actual_date'=>array('type'=>'datetimestamp','mask'=>'d-M h:i a'),
				'custom_signature_required'=>array('type'=>'boolean')
			),
			'outOfZoneOptions'=>array(
				'pickup_datetime'=>array('required'=>false,'type'=>'datetimepicker','AMPM'=>true,'value'=>date("Y-m-d H:i:00")),
				'serviceType'=>array('type'=>'select','name'=>'serviceType','onchange'=>'serviceChange();')
			),
			'outOfZoneOptionsRow'=>array(
				'total'=>array('type'=>'currency'),
				'rate'=>array('type'=>'currency'),
				'expectedDelivery'=>array('type'=>'datetimestamp','mask'=>'d-M h:i a')
			),
			'messageing'=>array(
				'ack_requested'=>array('type'=>'datetimestamp','mask'=>'d-M h:i a'),
				'scheduled_date'=>array('type'=>'datetimestamp','mask'=>'d-M h:i a'),
				'delay'=>array('type'=>'datetimestamp','mask'=>'H:i')
			),
			'acknowledgement'=>array(
				'ack_status'=>array('type'=>'select','lookup'=>'driver_message_status','required'=>true,'class'=>'form-control'),
				'acknowledgement'=>array('type'=>'hidden','value'=>1,'database'=>false),
				't_id'=>array('type'=>'hidden','value'=>'%%module:fetemplate_id%%','database'=>false),
				'a_id'=>array('type'=>'hidden','value'=>'%%request:a_id%%','database'=>false),
				'dispatch_message'=>array('type'=>'textarea','class'=>'mceSimple','class'=>'form-control'),
				'driver_message'=>array('type'=>'textarea','class'=>'mceSimple','class'=>'form-control'),
				'submit'=>array('type'=>'submitbutton','value'=>'Respond','database'=>false,'class'=>'btn btn-default'),
				'ack_requested'=>array('type'=>'datetimestamp','database'=>false,'mask'=>'d-M h:i a'),
				'scheduled_date'=>array('type'=>'datetimestamp','database'=>false,'mask'=>'d-M h:i a')
			),
			'dispatchAcks'=>array(
				'scheduled_date'=>array('type'=>'datetimestamp','mask'=>'d-M h:i a'),
				'ack_requested'=>array('type'=>'datetimestamp','mask'=>'d-M h:i a'),
				'delay'=>array('type'=>'datetimestamp','mask'=>'h:i'),
				'pagenum'=>array('type'=>'hidden','value'=>1),
				'dispatchAcks'=>array('type'=>'hidden','value'=>1),
				'pager'=>array('type'=>'select','required'=>true,'value'=>5,'lookup'=>'paging','id'=>'pager','class'=>'form-control'),
				't_id'=>array('type'=>'hidden','value'=>'%%module:fetemplate_id%%')
			),
			'liveExceptions'=>array(
				'liveExceptions'=>array('type'=>'hidden','value'=>1),
				'pagenum'=>array('type'=>'hidden','value'=>1),
				'pager'=>array('type'=>'select','required'=>true,'value'=>5,'lookup'=>'paging','id'=>'pager','class'=>'form-control'),
				't_id'=>array('type'=>'hidden','value'=>'%%module:fetemplate_id%%')
			),
			'liveExceptionsRow'=>array(
				'scheduled_date'=>array('type'=>'datetimestamp','mask'=>'d-M h:i a')
			),
			'myInvoices'=>array(
				'pagenum'=>array('type'=>'hidden','value'=>0),
				'email_to'=>array('type'=>'textfield','required'=>false,'validation'=>'custom:checkEmail','class'=>'form-control','value'=>'%%session:user:info:custom_invoice_email%%'),
				'cc_to'=>array('type'=>'textfield','required'=>false,'validation'=>'email','class'=>'form-control'),
				'comment'=>array('type'=>'textarea','required'=>false,'class'=>'form-control'),
				'submitBtn'=>array('type'=>'button','class'=>'btn btn-primary form-control','value'=>'Send Email','onclick'=>'sendEmail(this);return false;')
			),
			'myInvoicesRow'=>array(
				'created'=>array('type'=>'datetimestamp'),
				'invoice_date'=>array('type'=>'datestamp'),
				'total'=>array('type'=>'currency'),
				'email'=>array('type'=>'checkbox','required'=>false,'value'=>1,'name'=>'email[%%id%%]')
			),
			'invoiceDetails' => array(
				'invoiceDetails'=>array('type'=>'hidden','value'=>1),
				'pagenum'=>array('type'=>'hidden','value'=>0),
				'i_id'=>array('type'=>'hidden')
			),
			'invoiceDetailsRow' => array(
				'order_date'=>array('type'=>'datetimestamp'),
				'pickup_date'=>array('type'=>'datetimestamp'),
				'delivery_date'=>array('type'=>'datetimestamp'),
				'total'=>array('type'=>'currency')
			),
			'invoicePDF'=>array(
				'i_total'=>array('type'=>'currency'),
				'subtotal'=>array('type'=>'currency'),
				'taxes'=>array('type'=>'currency'),
				'total'=>array('type'=>'currency'),
				'paid_amount'=>array('type'=>'currency'),
				'balance'=>array('type'=>'currency','value'=>'%%field:tag^total%% - %%field:tag^paid_amount%%'),
				'invoice_date'=>array('type'=>'datestamp')
			),
			'invoicePDFRow'=>array(
				'order_date'=>array('type'=>'datetimestamp'),
				'total'=>array('type'=>'currency'),
				'scheduled_date'=>array('type'=>'datetimestamp','mask'=>'d-M-Y h:i A'),
				'actual_date'=>array('type'=>'datetimestamp','mask'=>'d-M-Y h:i A'),
				'value'=>array('type'=>'currency'),
				'paid_amount'=>array('type'=>'currency'),
				'authorization_amount'=>array('type'=>'currency'),
				'balance'=>array('type'=>'currency'),
				'taxes'=>array('type'=>'currency')
			),
			'invoicePDFDetail'=>array(
				'price'=>array('type'=>'currency'),
				'paid_amount'=>array('type'=>'currency'),
				'balance'=>array('type'=>'currency'),
				'value'=>array('type'=>'currency')
			),
			'myAddresses'=>array(
				'myAddresses'=>array('type'=>'hidden','value'=>1),
				'd_id'=>array('type'=>'hidden','value'=>0),
				'a_id'=>array('type'=>'hidden','value'=>0),
				't_id'=>array('type'=>'hidden','value'=>'%%module:fetemplate_id%%')
			),
			'mobileDetails'=>array(
				'scheduled_date'=>array('type'=>'datetimestamp','mask'=>'M d h:i a')
			),
			'userTesting'=>array(
				'email'=>array('type'=>'textfield','required'=>true,'validation'=>'email'),
				'member_id'=>array('type'=>'textfield','required'=>true,'validation'=>'number'),
				'userTesting'=>array('type'=>'hidden','value'=>1),
				'o_id'=>array('type'=>'textfield','validation'=>'number','required'=>false),
				'submit'=>array('type'=>'submitbutton')
			),
			'simulateDriver'=>array(
				'simulateDriver'=>array('type'=>'hidden','value'=>1),
				'member_id'=>array('type'=>'select','required'=>true,'class'=>'form-control','sql'=>sprintf("select id, concat(company,'-', lastname,', ',firstname) from members m where m.enabled = 1 and m.deleted = 0 and m.id in (select member_id from members_by_folder mbf where mbf.folder_id = %d) order by 2", DRIVER_FOLDER)),
				'submit'=>array('type'=>'submitButton','value'=>'Log In As','class'=>'btn btn-primary form-control')
			),
			'billingReport'=>array(
				'billingReport'=>array('type'=>'hidden','value'=>1),
				'from'=>array('type'=>'datepicker','required'=>true,'class'=>'form-control def_field_datepicker'),
				'to'=>array('type'=>'datepicker','required'=>true,'class'=>'form-control def_field_datepicker'),
				'order_status'=>array('type'=>'select','lookup'=>'orderStatus','multiple'=>true,'required'=>true,'class'=>'form-control'),
				'member_id'=>array('type'=>'select','required'=>false,'class'=>'form-control','sql'=>'select id, concat(company," ",lastname," ",firstname) from members where enabled=1 and deleted=0'),
				'product_id'=>array('type'=>'select','sql'=>'select id, concat(name," ",code) from product order by name','required'=>false),
				'pager'=>array('type'=>'select','required'=>true,'value'=>20,'lookup'=>'paging','id'=>'pager','class'=>'form-control','onchange'=>'getOrders(this);return false;'),
				'pagenum'=>array('type'=>'hidden'),
				'invoiced'=>array('type'=>'select','required'=>true,'lookup'=>'boolean'),
				'billing_type'=>array('type'=>'select','required'=>true,'options'=>array('B'=>'Bill Later','P'=>'Prepaid','C'=>'Non-Delivery'),'value'=>'B'),
				'invoiced'=>array('type'=>'select','lookup'=>'boolean','required'=>true,'value'=>0),
				//'bill_me_later'=>array('type'=>'select','required'=>true,'lookup'=>'boolean'),
				'orderby'=>array('type'=>'select','options'=>array('o.id'=>'Order #','m.company, o.id'=>'Company'),'required'=>true),
				'summary'=>array('type'=>'select','lookup'=>'boolean','required'=>true),
				'submit'=>array('type'=>'submitButton','value'=>'Search','class'=>'btn btn-primary form-control','class'=>'form-control btn btn-primary')
			),
			'billingReportRow'=>array(
				'net'=>array('type'=>'currency'),
				'taxable'=>array('type'=>'currency'),
				'nontaxable'=>array('type'=>'currency'),
				'taxes'=>array('type'=>'currency'),
				'total'=>array('type'=>'currency'),
				'paid'=>array('type'=>'currency'),
				'balance'=>array('type'=>'currency'),
				'actual_date'=>array('type'=>'datetimestamp','mask'=>'d-M h:i a'),
				'scheduled_date'=>array('type'=>'datetimestamp','mask'=>'d-M h:i a'),
			),
			'billingReportSummary'=>array(
				'net'=>array('type'=>'currency'),
				'paid'=>array('type'=>'currency'),
				'value'=>array('type'=>'currency'),
				'taxable'=>array('type'=>'currency'),
				'nontaxable'=>array('type'=>'currency'),
				'taxes'=>array('type'=>'currency'),
				'total'=>array('type'=>'currency')
			),
			'fedexStatus'=>array(
				'pagenum'=>array('type'=>'hidden','value'=>0),
				't_id'=>array('type'=>'hidden','value'=>'%%module:fetemplate_id%%')
			),
			'fedexStatusRow'=>array(
				'scheduled_date'=>array('type'=>'datetimestamp','mask'=>'d-M h:i a'),
			),
			'sendText'=>array(
				"scheduled_date"=>array("type"=>"timestamp")
			),
			'sendAlert'=>array(
				"scheduled_date"=>array("type"=>"timestamp")
			),
			'invoicing'=>array(
				'start_date'=>array('type'=>'datepicker','required'=>false,'validation'=>'date','class'=>'form-control def_field_datepicker'),
				'end_date'=>array('type'=>'datepicker','required'=>true,'validation'=>'date','class'=>'form-control def_field_datepicker'),
				'invoicing'=>array('type'=>'hidden','value'=>1),
				'invoice_id'=>array('type'=>'number','validation'=>'number','required'=>true,'class'=>'form-control text-right'),
				'do_it'=>array('type'=>'select','required'=>false, 'class'=>'form-control','value'=>1,'options'=>array(""=>"","C"=>"Check Orders","I"=>"Create Invoices")),
				'submit'=>array('type'=>'submitButton','value'=>'Process','class'=>'btn btn-primary form-control'),
				'report_only'=>array('type'=>'checkbox','value'=>1,'class'=>'form-control'),
				'order_type'=>array('type'=>'select','required'=>true,'lookup'=>'invoice_type',"value"=>"B"),
				't_id'=>array('type'=>'hidden','value'=>'%%module:fetemplate_id%%','name'=>'t_id'),
				'member_id'=>array('type'=>'select','required'=>false,'class'=>'form-control','sql'=>'select id, concat(company," ",lastname," ",firstname) from members where enabled=1 and deleted=0 order by company, lastname, firstname')
			),
			'invoicingRow'=>array(
				"invoice_amount"=>array("type"=>"currency"),
				"nontaxable"=>array("type"=>"currency"),
				"tax_amount"=>array("type"=>"currency"),
				"paid_amount"=>array("type"=>"currency"),
				"net"=>array("type"=>"currency"),
				"invoice_total"=>array("type"=>"currency")
			),
			'invoiceSearch'=>array(
			),
			'orderAuthorizations'=>array(
				'authorization_date'=>array('type'=>'datetimestamp'),
				'authorization_amount'=>array('type'=>'currency'),
				'authorization_success'=>array('type'=>'boolean')
			),
			'subsidiaryInvoices'=>array(
				'subsidiaryInvoices'=>array('type'=>'hidden','value'=>1),
				'invoice_date'=>array('type'=>'select','sql'=>sprintf('select invoice_date, date_format(invoice_date,"%%d-%%M-%%Y") from qb_export qb where qb.consolidate_id = %d group by invoice_date order by invoice_date desc', $this->getUserInfo("id")),'class'=>'form-control','onchange'=>'getConsolidated(this);return false;'),
				'send_consolidated'=>array('type'=>'checkbox','required'=>false,'value'=>1),
				'pager'=>array('type'=>'hidden','value'=>10),
				'pagenum'=>array('type'=>'hidden','value'=>1),
				'invoice_total'=>array('type'=>'currency'),
				'email_to'=>array('type'=>'textfield','required'=>false,'validation'=>'custom:checkEmail','class'=>'form-control','value'=>'%%session:user:info:custom_invoice_email%%'),
				'cc_to'=>array('type'=>'textfield','required'=>false,'validation'=>'email','class'=>'form-control'),
				'comment'=>array('type'=>'textarea','required'=>false,'class'=>'form-control'),
				'submitBtn'=>array('type'=>'button','class'=>'btn btn-primary form-control','value'=>'Send Email','onclick'=>'getConsolidated(this);return false;'),
				't_id'=>array('type'=>'hidden','value'=>'%%module:fetemplate_id%%')
			),
			'subsidiaryInvoicesRow'=>array(
				'total'=>array('type'=>'currency'),
				'subtotal'=>array('type'=>'currency'),
				'taxes'=>array('type'=>'currency'),
				'email'=>array('type'=>'checkbox','required'=>false,'value'=>1,'name'=>'email[%%id%%]')
			),
			'driverQueue'=>array(
				'driverType'=>array('type'=>'select','options'=>array('0'=>'All','1'=>'Same Day','2'=>'Overnight'),'value'=>1,'required'=>false,'class'=>'form-control'),
				'driverQueue'=>array('type'=>'hidden','value'=>1),
				'address'=>array('type'=>'textfield','placeholder'=>'Address/Postal Code','class'=>'form-control'),
				'driver_id'=>array('type'=>'select','required'=>false,'sql'=>'select d.id, concat(company, "-", lastname," ",firstname) from drivers d, members m where m.id = d.member_id and d.enabled=1 and d.deleted = 0 order by 2','class'=>'form-control','placeholder'=>'Driver'),
				'query'=>array('type'=>'button','class'=>'btn btn-primary form-control','value'=>'Apply Filter','onclick'=>'getResults(this);return false;'),
				'group_id'=>array('type'=>'select','idlookup'=>'driver_groups','required'=>false,'class'=>'form-control'),
				'i_delay'=>array('type'=>'select','lookup'=>'refresh_rate','required'=>true,'class'=>'form-control')
			),
			'driverQueueRow'=>array(
				'scheduled_date'=>array('type'=>'datetimestamp','mask'=>'d-M h:i a'),
			),
			'getAQuote'=>array(
				'getAQuote'=>array('type'=>'hidden','value'=>1),
				'custom_weight_code'=>array('type'=>'select','idlookup'=>'weights','required'=>true,'class'=>'form-control'),
				'custom_dimension_code'=>array('type'=>'select','idlookup'=>'dimensions','required'=>true,'class'=>'form-control'),
				'serviceType'=>array('type'=>'select','required'=>true,'class'=>'form-control','onchange'=>'serviceChange()','prettyName'=>'Service'),
				'product_id'=>array('type'=>'select','required'=>true,'class'=>'form-control','prettyName'=>'Packaging'),
				'quantity'=>array('type'=>'number','class'=>'text-right input-sm quantity form-control','name'=>'quantity','value'=>'0','required'=>true,'validation'=>'number','onchange'=>'setQty(this);return false;','min'=>0),
				'weight'=>array('type'=>'textfield','class'=>'text-right input-sm weight form-control','name'=>'weight','required'=>true,'validation'=>'number','onchange'=>'setQty(this);return false;','min'=>0),
				'height'=>array('type'=>'textfield','class'=>'text-right input-sm height form-control','name'=>'height','required'=>true,'validation'=>'number','min'=>0),
				'width'=>array('type'=>'textfield','class'=>'text-right input-sm width form-control','name'=>'width','required'=>true,'validation'=>'number','min'=>0),
				'depth'=>array('type'=>'textfield','class'=>'text-right input-sm depth form-control','name'=>'depth','required'=>true,'validation'=>'number','min'=>0),
				'from'=>array('type'=>'textfield','required'=>true,'class'=>'form-control','maxlength'=>6,'name'=>'from','prettyName'=>'From FSA'),
				'to'=>array('type'=>'textfield','required'=>true,'class'=>'form-control','maxlength'=>6,'name'=>'to','prettyName'=>'To FSA'),
				'quoteMe'=>array('type'=>'submitButton','value'=>'Get Quote','class'=>'btn btn-primary form-control')
			),
			'driverLocations'=>array(
				'driverLocations'=>array('type'=>'hidden','value'=>1),
				'driver_id'=>array('type'=>'select','sql'=>sprintf('select m.id,concat(company, "-", lastname," ",firstname) from drivers d, members m, members_by_folder mbf where mbf.folder_id = %d and m.id = mbf.member_id and d.member_id = m.id and m.enabled = 1 and m.deleted = 0 and d.enabled = 1 and d.deleted = 0 order by 2',DRIVER_FOLDER),'class'=>'form-control'),
				'i_delay'=>array('type'=>'select','lookup'=>'refresh_rate','required'=>true,'class'=>'form-control','value'=>'120000'),
				't_id'=>array('type'=>'hidden','value'=>'%%module:fetemplate_id%%'),
				'refresh'=>array('type'=>'button','value'=>'Refresh','class'=>'form-control btn-primary','onclick'=>'getLocations();return false;')
			),
			'gpsLocation'=>array(
				'latitude'=>array('type'=>'hidden','required'=>false),
				'longitude'=>array('type'=>'hidden','required'=>false),
				'gpsLocation'=>array('type'=>'hidden','database'=>false,'value'=>1),
				'member_id'=>array('type'=>'hidden','value'=>'%%session:user:info:id%%'),
				'delivery_id'=>array('type'=>'hidden','value'=>'%%request:c_id%%'),
				'datetime'=>array('type'=>'hidden','value'=>date(DATE_ATOM)),
				't_id'=>array('type'=>'hidden','value'=>'%%module:fetemplate_id%%','database'=>false)
			),
			'showDriverRoute'=>array(),
			'showDriverRouteInner'=>array()
		);
	}

    /**
     * @param $orderId
     * @param $valid
     * @param $caller
     * @return void
     * @throws \Twilio\Exceptions\ConfigurationException
     * @throws phpmailerException
     */
    function postSaleProcessing($orderId, $valid, $caller) {
		if (0 == $orderId) return;	//session expired?
		if ($orderId > 0) {
			if ($mgmt = $this->checkArray("mgmt:user:id",$_SESSION)) {
				$this->execute(sprintf("update orders set login_id = %d where id = %d", $mgmt, $orderId ));
			}
//
//	Some sanity checks to find issues
//
			if ($this->fetchSingleTest("select o.id from orders o, product p, order_lines ol, custom_delivery cd where o.id = %d and ol.order_id = o.id and p.id = ol.product_id and p.custom_same_day = 0 and ol.custom_package='S' and cd.order_id = o.id and cd.service_type='D' and cd.driver_id = 0", $orderId)) {
				$this->logMessage(__FUNCTION__,sprintf("no delivery driver assigned [%s]", $orderId),1,true,false);
			}
			if (!$this->fetchSingleTest("select * from order_lines where custom_package = 'S' and order_id = %d", $orderId)) {
				$this->logMessage(__FUNCTION__,sprintf("no service type [%s]", $orderId),1,true,false);
				// - todo $this->sendAlert($d_id, $form, $fields);
			}
			$cart = $_SESSION["cart"];
			$quote = array_key_exists("quote",$_SESSION) ? $_SESSION["quote"] : array();
			//
			//	grab the fuel rate and save it - if the rate changes we can use the rate as of the order date
			//
			if ($this->checkArray("quote:fuelRate",$cart) && $cart["quote"]["fuelRate"] > .01) {
				$this->execute(sprintf("update orders set custom_fuel_rate = %f where id = %d", $cart["quote"]["fuelRate"], $orderId));
			}
			$tmp = array();
			foreach($cart["products"] as $key=>$p) {
				if ($p["custom_package"] == "S") $tmp = $p;
			}
			$this->logMessage(__FUNCTION__,sprintf("service is set to [%s]",print_r($tmp,true)),1);
			if (!($this->checkArray("mgmt:user:custom_super_user",$_SESSION) && $_SESSION["mgmt"]["user"]["custom_super_user"] == 1))
				$cart["header"]["pickup_datetime"] = max(date("Y-m-d H:i:s", strtotime($cart["header"]["pickup_datetime"])),date("Y-m-d H:i:s"));

//
//	recurring order check - can assign specific drivers to p/u & deliver
//
			if (array_key_exists("custom_recurring_pu_driver",$quote) && $quote["custom_recurring_pu_driver"] > 0) {
				$cart["quote"]["pickup_driver"] = $this->fetchSingleTest("select * from drivers where id = %d",$quote["custom_recurring_pu_driver"]);
			}
			if (array_key_exists("custom_recurring_del_driver",$quote) && $quote["custom_recurring_del_driver"] > 0) {
				$cart["quote"]["delivery_driver"] = $this->fetchSingleTest("select * from drivers where id = %d",$quote["custom_recurring_del_driver"]);
			}

			$delivery = array("order_id"=>$orderId,"service_type"=>"P","scheduled_date"=>$cart["header"]["pickup_datetime"],"driver_sequence"=>9999);
			$delivery["driver_id"] = array_key_exists("quote",$cart) && array_key_exists("pickup_driver",$cart["quote"]) ? $cart["quote"]["pickup_driver"]["id"] : 0;
			$pu_driver = $delivery["driver_id"];
			$del_driver = array_key_exists("quote",$cart) && array_key_exists("delivery_driver",$cart["quote"]) ? $cart["quote"]["delivery_driver"]["id"] : 0;
			if ($pu_driver == $del_driver)
				$delivery["percent_of_delivery"] = 50;
			else
				$delivery["percent_of_delivery"] = 25;
			if (array_key_exists("pickupInstructions",$cart["header"]) && strlen($cart["header"]["pickupInstructions"]) > 0)
				$delivery["instructions"] = $cart["header"]["pickupInstructions"];
			elseif (array_key_exists("pickupInstructions",$quote) && strlen($quote["pickupInstructions"]) > 0)
				$delivery["instructions"] = $quote["pickupInstructions"];
			$delivery["ack_requested"] = date(DATE_ATOM);
			$delivery["dispatch_message"] = "New order placed";
			$stmt = $this->prepare(sprintf("insert into custom_delivery(%s) values(%s?)", implode(",",array_keys($delivery)),str_repeat("?, ",count($delivery)-1)));
			$stmt->bindParams(array_merge(array(str_repeat("s",count($delivery))),array_values($delivery)));
			$stmt->execute();
			$p_id = $this->insertId();
			$pickup = $delivery;
			$delivery["service_type"] = "D";
			$delivery["instructions"] = "";
			if (array_key_exists("deliveryInstructions",$cart["header"]) && strlen($cart["header"]["deliveryInstructions"]) > 0)
				$delivery["instructions"] = $cart["header"]["deliveryInstructions"];
			elseif (array_key_exists("deliveryInstructions",$quote) && strlen($quote["deliveryInstructions"]) > 0)
				$delivery["instructions"] = $quote["deliveryInstructions"];

			$delivery["scheduled_date"] = "";
			foreach($cart["products"] as $k1=>$v1) {
				if ($v1["is_fedex"]) {
					foreach($_SESSION["cart"]["custom"]["rates"] as $k2 =>$v2) {
						$this->logMessage(__FUNCTION__,sprintf("test 2 v2 [%s] code [%s]", print_r($v2,true), $v1["code"]), 1);
						if ($v2["code"] == $v1["code"]) {
							if ($v2["expectedDelivery"] != "") {
								$delivery["scheduled_date"] = $v2["expectedDelivery"];
							}
						}
					}
				}
			}
			if ($delivery["scheduled_date"] == "") {
				$kjv = new KJV();
				$delivery["scheduled_date"] = $kjv->calcDelivery($_SESSION["cart"]["header"]["pickup_datetime"],$tmp);
			}
			$delivery["driver_id"] = $del_driver;
			if ($pu_driver == $del_driver)
				$delivery["percent_of_delivery"] = 50;
			else
				$delivery["percent_of_delivery"] = 75;
			$stmt = $this->prepare(sprintf("insert into custom_delivery(%s) values(%s?)", implode(",",array_keys($delivery)),str_repeat("?, ",count($delivery)-1)));
			$stmt->bindParams(array_merge(array(str_repeat("s",count($delivery))),array_values($delivery)));
			$stmt->execute();
			$d_id = $this->insertId();
			$seq = 0;
$this->calc_driver_allocations($orderId);
			$custom = array(
				"custom_weight_code"=>$cart["header"]["custom_weight_code"],
				"custom_insurance"=>$cart["header"]["custom_insurance"],
				"custom_placed_by"=>$this->checkArray("quote:custom_placed_by",$_SESSION) ? $_SESSION["quote"]["custom_placed_by"] : "",
				"custom_email_confirmation"=>$this->checkArray("quote:custom_email_confirmation",$_SESSION) ? $_SESSION["quote"]["custom_email_confirmation"] : "",
				"custom_pickup_email"=>$this->checkArray("quote:custom_pickup_email",$_SESSION) ? $_SESSION["quote"]["custom_pickup_email"] : "",
				"custom_dimension_code"=>$cart["header"]["custom_dimension_code"],
				"custom_declared_value"=>$cart["header"]["custom_declared_value"],
				"custom_reference_number"=>$cart["header"]["custom_reference_number"],
				"custom_recurring_pu_driver"=>$this->checkArray("quote:custom_recurring_pu_driver",$_SESSION) ? $_SESSION["quote"]["custom_recurring_pu_driver"] : 0,
				"custom_recurring_del_driver"=>$this->checkArray("quote:custom_recurring_del_driver",$_SESSION) ? $_SESSION["quote"]["custom_recurring_del_driver"] : 0,
				"custom_signature_required"=>$this->checkArray("quote:custom_signature_required",$_SESSION) ? $_SESSION["quote"]["custom_signature_required"] : 0,
				"customs_declaration"=>$this->checkArray("quote:customs_declaration",$_SESSION) ? $this->checkArray("quote:customs_declaration",$_SESSION) : ""
			);
			$stmt = $this->prepare(sprintf("update orders set %s=? where id = %d", implode("=?, ",array_keys($custom)),$orderId));
			$stmt->bindParams(array_merge(array(str_repeat("s",count($custom))),array_values($custom)));
			$stmt->execute();

//
//	Dont send texts if its the nightly recurring process
//
			if (!(array_key_exists("fromNightly",$quote) && $quote["fromNightly"]==1)) {
				$hours = $this->getConfigVar("normalHours","config");
				$hr_pts = explode(",",$hours);
				$time = explode("-",$hr_pts[0]);
				$days = explode("-",$hr_pts[1]);
				$pu_dt = strtotime($pickup["scheduled_date"]);
				$this->logMessage(__FUNCTION__, sprintf("pu_dt [%s] w [%s] H [%s]", $pickup["scheduled_date"], date("w", $pu_dt), date("H", $pu_dt)),1);
				if ((int)date("w", $pu_dt) < $days[0] || (int)date("w", $pu_dt) > $days[1] || (int)date("H", $pu_dt) < $time[0] || (int)date("H", $pu_dt) > $time[1]) {
					$this->logMessage(__FUNCTION__,sprintf("calling sendAlert"),1);
					$this->sendAlert( $p_id, $this->m_dir."sendAlert.html", $this->getFields("sendAlert"));
				}
				$this->sendText( $p_id, $this->m_dir."sendText.html", $this->getFields("sendText"));
				if ($pu_driver != $del_driver)
					$this->sendText( $d_id, $this->m_dir."sendText.html", $this->getFields("sendText"));
			}
			$this->calculateCommissions($orderId);
			$this->logMessage(__FUNCTION__,sprintf("recurring check cart [%s]", print_r($cart,true)),1);
			if (array_key_exists("recurring",$cart) && is_array($cart["recurring"]) && count($cart["recurring"]) > 0 && $cart["recurring"]["frequency"] > 0) {
				$this->setRecurring($orderId,$cart);
			}
			if ($this->checkArray(sprintf("cart:products:%d|0|0|0:id", DRY_ICE), $_SESSION)) {
				if ($dtl = $this->fetchSingleTest("select * from order_lines where order_id = %d and product_id = %d", $orderId, DRY_ICE)) {
					$old = $this->prepare(sprintf("insert into order_lines_dimensions(order_id, line_id, quantity, weight) values(?, ?, ?, ?)"));
					$old->bindParams(array("iiid", $orderId, $dtl["line_id"], 1, $_SESSION["quote"]["dry_ice_weight"]));
					$old->execute();
				}
			}
		}
		if (array_key_exists('cart',$_SESSION) && array_key_exists('abandoned',$_SESSION['cart'])) {
			if (array_key_exists('id',$_SESSION['cart']['abandoned'])) {
				$this->execute(sprintf('delete from cart_header where id = %d',$_SESSION['cart']['abandoned']['id']));
				$this->execute(sprintf('delete from cart_lines where order_id = %d',$_SESSION['cart']['abandoned']['id']));
			}
			unset($_SESSION['cart']['abandoned']);
		}
		if (array_key_exists('quote',$_SESSION)) unset($_SESSION["quote"]);
		$orderHtml = "";
		$emails = $this->configEmails("ecommerce");
		if (count($emails) == 0)
			$emails = $this->configEmails("contact");
		$this->logMessage('postSaleProcessing',sprintf('notifying on order [%d] status [%s] emails [%s] caller [%s]',$orderId,$valid,print_r($emails,true),print_r($caller,true)),1);
		$body = new Forms();
		$mailer = new MyMailer();
		$mailer->Subject = sprintf("Order Processing - %s", SITENAME);
		$body = new Forms();
		$sql = sprintf('select * from htmlForms where class = %d and type = "orderEmail"',$this->getClassId('product'));
		$html = $this->fetchSingleTest($sql);
		$body->setHTML($html['html']);
		if (!$order = $this->fetchSingleTest('select o.*, m.firstname, m.lastname, m.email from orders o, members m where o.id = %d and m.id = o.member_id',$orderId))
			$this->logMessage(__FUNCTION__,sprintf('cannot locate order #[%d]',$orderId),1,true);
		$body->addData($this->formatOrder($order));
		if ($caller->hasOption('receiptPrint') && $module = $this->fetchSingleTest('select t.*, m.classname, t.id as fetemplate_id from fetemplates t, modules m where t.id = %d and m.id = t.module_id',$caller->getOption('receiptPrint'))) {
			$this->logMessage('postSaleProcessing',sprintf('caller module [%s] this [%s]',print_r($module,true),print_r($this,true)),4);
			$class = new $module['classname']($module['id'],$module);
			$orderHtml = $class->{$module['module_function']}();
			$body->addTag('order',$orderHtml,false);
		}
		if ($this->hasOption('receiptPrint') && $module = $this->fetchSingleTest('select t.*, m.classname from fetemplates t, modules m where t.id = %d and m.id = t.module_id',$this->getOption('receiptPrint'))) {
			$this->logMessage('postSalePrcessing',sprintf('this module [%s] this [%s]',print_r($module,true),print_r($this,true)),2);
			$class = new $module['classname']($module['id'],$module);
			$orderHtml = $class->{$module['module_function']}();
			$body->addTag('order',$orderHtml,false);
		}
		$body->setOption('formDelimiter','{{|}}');
		$mailer->Body = $body->show();
		$mailer->From = $order['email'];
		$mailer->FromName = $order['firstname'].' '.$order['lastname'];
		$mailer->IsHTML(true);
		foreach($emails as $key=>$value) {
			$mailer->addAddress($value['email'],$value['name']);
		}
/*
		if (!$mailer->Send()) {
			$this->logMessage('postSaleProcessing',sprintf("Email send failed [%s]",print_r($mailer,true)),1,true);
		}
*/
		if (($this->hasOption('userEmail') || $caller->hasOption('userEmail')) && strlen($orderHtml) > 0) {
			/*
				User Email here
			*/
			$this->logMessage(__FUNCTION__,'sending user receipt email',3);
			$mailer = new MyMailer();
			$mailer->Subject = sprintf("Your Order Receipt - %s", SITENAME);
			$body = new Forms();
			$sql = sprintf('select * from htmlForms where class = %d and type = "userEmail"',$this->getClassId('product'));
			$html = $this->fetchSingleTest($sql);
			$body->setHTML($html['html']);
			$body->addData($this->formatOrder($order));
			$body->addTag('order',$orderHtml,false);
			$body->setOption('formDelimiter','{{|}}');
			$mailer->Body = $body->show();
			$mailer->From = $emails[0]['email'];
			$mailer->FromName = $emails[0]['name'];
			$mailer->IsHTML(true);	
			$mailer->addAddress($order['email'],$order['firstname'].' '.$order['lastname']);
			if (!$mailer->Send()) {
				$this->logMessage(__FUNCTION__,sprintf("User Email send failed [%s]",print_r($mailer,true)),1,true);
			}
		}
	}

    /**
     * @param $orderId
     * @param $cart
     * @return bool
     * @throws phpmailerException
     */
    function setRecurring($orderId, $cart) {
		$this->logMessage(__FUNCTION__,sprintf("start recurring on order %d cart [%s]", $orderId, print_r($cart,true)),1);
		$status = true;
		$o = $this->fetchSingleTest("select * from orders where id = %d", $orderId);
		unset($o["id"]);
		$o["order_status"] |= STATUS_RECURRING;
		$o["authorization_code"] = "From Order #".$orderId;
		$o["authorization_transaction"] = $orderId;
		//$o["authorization_type"] = "TBD";
		$o["recurring_period"] = $cart["recurring"]["frequency"];
		$o["recurring_type"] = $cart["recurring"]["type"];
		$tmp = 0;
		//foreach($cart["recurring"]["weekdays"] as $key=>$value) {
		//	$tmp |= $value;
		//}
		$o["recurring_weekdays"] = $cart["recurring"]["weekdays"];
		$o["recurring_by_position"] = $cart["recurring"]["by_position"];
		$o["recurring_position"] = $cart["recurring"]["position"];
		$stmt = $this->prepare(sprintf("insert into orders(%s) values(?%s)", implode(",",array_keys($o)), str_repeat(", ?", count($o)-1)));
		$status = $status && $stmt->bindParams(array_merge(array(str_repeat("s",count($o))),array_values($o)));
		$status = $status && $stmt->execute();
		$newId = $this->insertId();
		$d = $this->fetchAllTest("select * from order_lines where order_id = %d and deleted = 0 order by line_id", $orderId);
		foreach($d as $key=>$line) {
			unset($line["id"]);
			$line["order_id"] = $newId;
			$stmt = $this->prepare(sprintf("insert into order_lines(%s) values(?%s)", implode(",",array_keys($line)), str_repeat(", ?", count($line)-1)));
			$status = $status && $stmt->bindParams(array_merge(array(str_repeat("s",count($line))),array_values($line)));
			$status = $status && $stmt->execute();
		}
		$d = $this->fetchAllTest("select * from order_lines_dimensions where order_id = %d", $orderId);
		foreach($d as $key=>$line) {
			unset($line["id"]);
			$line["order_id"] = $newId;
			$stmt = $this->prepare(sprintf("insert into order_lines_dimensions(%s) values(?%s)", implode(",",array_keys($line)), str_repeat(", ?", count($line)-1)));
			$status = $status && $stmt->bindParams(array_merge(array(str_repeat("s",count($line))),array_values($line)));
			$status = $status && $stmt->execute();
		}
		$d = $this->fetchAllTest("select * from addresses where ownertype='order' and ownerid = %d", $orderId);
		foreach($d as $key=>$line) {
			unset($line["id"]);
			$line["ownerid"] = $newId;
			$stmt = $this->prepare(sprintf("insert into addresses(%s) values(?%s)", implode(",",array_keys($line)), str_repeat(", ?", count($line)-1)));
			$status = $status && $stmt->bindParams(array_merge(array(str_repeat("s",count($line))),array_values($line)));
			$status = $status && $stmt->execute();
		}
/*
		$deliveries = $this->fetchAllTest("select * from custom_delivery where order_id = %d", $orderId);
		foreach($deliveries as $k=>$v) {
			$v["order_id"] = $newId;
			$v["completed"] = 1;	// so they don't show up in the schedules, unset when we reschedule
			unset($v["id"]);
			$stmt = $this->prepare(sprintf("insert into custom_delivery(%s) values(%s?)", implode(", ", array_keys($v)), str_repeat("?, ",count($v)-1)));
			$stmt->bindParams(array_merge(array(str_repeat("s",count($v))), array_values($v)));
			$stmt->execute();
		}
*/
		$status = $status && $this->setNextDate($orderId,$newId);
		return $status;
	}

    /**
     * @param $origId
     * @param $orderId
     * @return bool
     * @throws phpmailerException
     */
    function setNextDate($origId, $orderId) {
		$this->logMessage(__FUNCTION__,sprintf("scheduling recurring order %d original [%s]", $orderId, $origId),1);
		$status = true;
		$order = $this->fetchSingleTest("select * from orders where id = %d", $orderId);
		$billed = $this->fetchSingleTest("select * from order_billing where original_id = %d order by period_number desc", $orderId);
		$this->logMessage(__FUNCTION__,sprintf("billed [%s]", print_r($billed,true)),1);
		if (is_array($billed)) {
			$cd = date("Y-m-d", strtotime($billed["billing_date"]));
			$period = $billed["period_number"]+1;
		}
		else {
			$pu = $this->fetchSingleTest("select * from custom_delivery where order_id = %d and service_type='P'",$origId);
			$cd = date("Y-m-d", strtotime($pu["scheduled_date"]));
			$period = 1;
		}
		$this->logMessage(__FUNCTION__,sprintf("calc next date from %s, recurring type %s", $cd, $order["recurring_type"]),1);
		switch($order["recurring_type"]) {
		case "Weekly":
			$td = date("Y-m-d", strtotime(sprintf("%s + %d weeks", $cd, $order["recurring_period"])));
			break;
		case "Daily":
			$td = date("Y-m-d", strtotime(sprintf("%s + %d days", $cd, $order["recurring_period"])));
			break;
		case "Monthly":
			$td = date("Y-m-d", strtotime(sprintf("%s + %d months", $cd, $order["recurring_period"])));
			break;
		}
		$p = $this->fetchSingleTest("select * from order_lines where order_id = %d and custom_package = 'S' and deleted = 0", $orderId);
		$nd = $this->calcPickup($td,$p);
		$this->logMessage(__FUNCTION__,sprintf("recalced pickup date %s to %s", $td, $nd),1);
		$billing = array("original_id"=>$orderId,"billing_date"=>$nd,"period_number"=>$period);
		$stmt = $this->prepare(sprintf("insert into order_billing(%s) values(?%s)", implode(",",array_keys($billing)), str_repeat(", ?", count($billing)-1)));
		$stmt->bindParams(array_merge(array(str_repeat("s",count($billing))),array_values($billing)));
		$status = $status && $stmt->execute();
		$ct = $this->fetchScalarTest("select count(0) from order_billing where original_id = %d and billed = 0", $orderId);
		if ($ct < 10) {
			$status &= $this->setNextDate($origId,$orderId);
		}
		return $status;
	}

    /**
     * @param $memberId
     * @param $obj
     * @param $module
     * @return void
     */
    function postNewsletterSignup($memberId, $obj, $module) {
		$obj->logMessage(__FUNCTION__,sprintf('member id [%d]',$memberId),1);
	}

    /**
     * @param $data
     * @return mixed
     * @throws phpmailerException
     */
    function rssParse($data) {
		$ct = preg_match("/<p>(.*?)<\/p>/s",$data['description'],$results);
		if ($ct > 0)
			$data['description'] = $results[0];
		$this->logMessage(__FUNCTION__,sprintf('ct = [%s], results [%s] data [%s]',$ct,print_r($results,true),print_r($data,true)),1);
		return $data;
	}

	/**
	 * @return void
	 */
	function initHook() {
	}

    /**
     * @param $cart
     * @return mixed
     * @throws phpmailerException
     */
    function preRecalc($cart) {
		if (!$this->isLoggedIn()) return $cart;
		$cart["header"]["freeShipping"] = 0;
		if ($fuel = $this->fetchSingleTest("select p.* from members_folders mf, members_by_folder mbf, product p where mbf.member_id = %d and mf.id = mbf.folder_id and p.id = mf.custom_fuel and mf.id = %d limit 1", $cart["header"]["member_id"], MSH_GROUP)) {
			//
			//	Mount Sinai Group - no fuel charge is between specific postal codes
			//
			if ($this->checkArray("addresses:shipping:postalcode", $cart) && $this->checkArray("addresses:pickup:postalcode", $cart) &&
					strpos(MSH_NO_FUEL,sprintf("|%s|", substr($cart["addresses"]["shipping"]["postalcode"],0,3))) !== false &&
					strpos(MSH_NO_FUEL,sprintf("|%s|", substr($cart["addresses"]["pickup"]["postalcode"],0,3))) !== false) {
				foreach($cart["products"] as $k=>$v) {
					if ($v["id"] == $fuel["id"]) {
						$cart["products"][$k]["price"] = 0;
						$cart["products"][$k]["value"] = 0;
						$cart["products"][$k] = Ecom::lineValue($cart["products"][$k]);
					}
				}
			}
		}
		$this->logMessage(__FUNCTION__,sprintf('returned cart [%s]',print_r($cart,true)),3);
		return $cart;
	}

    /**
     * @param $cart
     * @return mixed
     * @throws phpmailerException
     */
    function postRecalc($cart) {
		$this->logMessage(__FUNCTION__,sprintf('input cart [%s]',print_r($cart,true)),3);
		$value = 0;
		foreach($cart["products"] as $key=>$line) {
			if ($this->fetchScalarTest("select custom_commission from product where id = %d",$line["product_id"]))
				$value += $line["total"];
		}
		$cart["header"]["custom_commissionable_amt"] = $value;
		$this->logMessage(__FUNCTION__,sprintf('returned cart [%s]',print_r($cart,true)),3);
		return $cart;
	}

    /**
     * @param $cart
     * @return mixed
     * @throws phpmailerException
     */
    function calcShipping($cart) {
		$this->logMessage(__FUNCTION__,sprintf("called with [%s]",print_r($cart,true)),3);
		return $cart;
	}

    /**
     * @param $cart
     * @return array
     */
    function initCart($cart) {
		$cart["header"]["pickup_datetime"] = date(GLOBAL_DEFAULT_DATETIME_FORMAT);
		$cart["header"]["shipping"] = 0;
		$cart["header"]["handling"] = 0;
		$cart["header"]["discount_type"] = "";
		return $cart;
	}

    /**
     * @param $cart
     * @return mixed
     */
    function formatCart($cart) {
		return $cart;
	}

    /**
     * @param $cart
     * @param $quote
     * @return array|mixed
     * @throws SoapFault
     * @throws phpmailerException
     */
    function getFedEx($cart, $quote) {
		return $this->fedExRates( $cart, $quote );
	}

    /**
     * @return array|mixed|string|string[]
     * @throws phpmailerException
     */
    function pickupAddresses() {
		if (!$module = $this->getModule())
			return "";
		$outer = new Forms();
		$outer->init($this->m_dir.$module["outer_html"]);
		$outer->setModule($module);
		$member = $this->getUserInfo("id");
		$inner = new Forms();
		$inner->init($this->m_dir.$module["inner_html"]);
		$flds = $outer->buildForm($this->config->getFields($module['configuration']));
		$flds = $inner->buildForm($flds);
		if (array_key_exists("a_id",$_REQUEST)) {
			if (!array_key_exists("cart",$_SESSION)) $_SESSION["cart"] = Ecom::initCart();
			if ($addresses = $this->fetchAllTest("select * from addresses where ownertype='member' and ownerid = %d and id = %d order by company, line1",$member,$_REQUEST["a_id"]))
				$_SESSION["cart"]["addresses"]["pickup"] = $addresses[0];
			else $_SESSION["cart"]["addresses"]["pickup"] = array("id"=>0);
			$_SESSION["cart"]["addresses"]["pickup"]["addresstype"] = ADDRESS_PICKUP;
		}
		else
			$addresses = $this->fetchAllTest("select * from addresses where ownertype='member' and ownerid = %d and address_book=1 and deleted = 0 and addresstype != %d order by company, line1",$member, ADDRESS_COMPANY);
		$result = array();
		foreach($addresses as $key=>$address) {
			$inner->reset();
			$inner->addData(Address::formatData($address));
			$result[] = $inner->show();
		}
		if (array_key_exists("a_id",$_REQUEST) && $_REQUEST["a_id"] == 0) {
			$result[] = $inner->show();
		}
		if (array_key_exists("pickupAddressForm",$_REQUEST) && array_key_exists("saveAddress",$_REQUEST)) {
			$inner->addData($_POST);
			if ($inner->validate()) {
				$editFlds = $inner->buildForm($editFlds);
				$values = array();
				foreach($editFlds as $key=>$fld) {
					if (!(array_key_exists('database',$fld) && $fld['database'] == false)) {
						$values[$fld['name']] = $inner->getData($fld['name']);
					}
				}
				$values["ownerid"] = $member;
				$values["ownertype"] = "member";
				$values["addresstype"] = ADDRESS_PICKUP;	//$this->fetchScalarTest("select id from code_lookups where type='memberAddressTypes' order by sort desc limit 1");
				if ($_REQUEST["a_id"] == 0) {
					$stmt = $this->prepare(sprintf("insert into addresses(%s) values(?%s)",implode(", ",array_keys($values)),str_repeat(", ?",count($values)-1)));
				}
				else {
					$stmt = $this->prepare(sprintf("update addresses set %s=? where id = %d",implode("=?, ",array_keys($values)),$_REQUEST["a_id"]));
				}
				$stmt->bindParams(array_merge(array(str_repeat("s",count($values))),array_values($values)));
				if ($stmt->execute()) {
					if ($_REQUEST["a_id"] == 0)
						$_REQUEST["a_id"] = $this->insertId();
					$inner->setData("a_id",$_REQUEST["a_id"]);
					$geocode = Address::geocode($inner->getAllData());
					if ($geocode["status"]) {
						$geo = $this->prepare("update addresses set latitude = ?, longitude = ? where id = ?");
						$geo->bindParams(array("ddd",$geocode["latitude"],$geocode["longitude"],$_REQUEST["a_id"]));
						$geo->execute();
					}
					if (strlen($module["parm1"]) > 0)
						$inner->init($this->m_dir.$module["parm1"]);
				}
			}
			$result = array($inner->show());
		}
		else {
			//$outer->setData("test","i have an address");
		}
		$subdata = $this->subForms($this->m_module['fetemplate_id'],null,array(),'outer');
		$this->logMessage(__FUNCTION__,sprintf('subforms [%s]',print_r($subdata,true)),3);
		foreach($subdata as $key=>$value) {
			$outer->addTag($key,$value,false);
		}
		$outer->addTag("addresses",implode("",$result),false);
		if ($this->checkArray("cart:addresses:pickup:id",$_SESSION) && $_SESSION["cart"]["addresses"]["pickup"]["id"] > 0) {
			$outer->setData("hasPickup",$_SESSION["cart"]["addresses"]["pickup"]["id"]);
		}
		return $outer->show();
	}

    /**
     * @return array|mixed|string|string[]
     * @throws phpmailerException
     */
    function shippingAddresses() {
		if (!$module = $this->getModule())
			return "";
		$outer = new Forms();
		$outer->init($this->m_dir.$module["outer_html"]);
		$outer->setModule($module);
		$member = $this->getUserInfo("id");
		$inner = new Forms();
		$inner->init($this->m_dir.$module["inner_html"]);
		$flds = $outer->buildForm($this->config->getFields($module['configuration']));
		$flds = $inner->buildForm($flds);
		if (array_key_exists("a_id",$_REQUEST)) {
			if ($addresses = $this->fetchAllTest("select * from addresses where ownertype='member' and ownerid = %d and id = %d order by company, line1",$member,$_REQUEST["a_id"])) {
				$_SESSION["cart"]["addresses"]["shipping"] = $addresses[0];
				if (array_key_exists("pickup",$_SESSION["cart"]["addresses"]) && $_SESSION["cart"]["addresses"]["pickup"]["id"] > 0) {

					$allowedZones = $this->fetchScalarAllTest("select zone_id from zones_by_folder z, members m where m.id = %d and z.folder_id = m.custom_zones", $this->getUserInfo("id"));

					$fromZone = $this->fetchSingleTest("select zf.*, f.downtown from zone_fsa zf, fsa f where f.fsa='%s' and zf.fsa_id = f.id and zone_id in (%s)",
						strtoupper(substr($_SESSION["cart"]["addresses"]["pickup"]["postalcode"],0,3)), count($allowedZones) > 0 ? implode(", ", $allowedZones) : 0);
					$toZone = $this->fetchSingleTest("select zf.*, f.downtown from zone_fsa zf, fsa f where f.fsa='%s' and zf.fsa_id = f.id and zone_id in (%s)",
						strtoupper(substr($_SESSION["cart"]["addresses"]["shipping"]["postalcode"],0,3)), count($allowedZones) > 0 ? implode(", ", $allowedZones) : 0);
					//
					//	inzone = kjv delivery or fedex [different rating code]
					//
					$_SESSION["cart"]["header"]["inzone"] = (is_array($fromZone) && is_array($toZone)) ? 1:0;
					$this->logMessage(__FUNCTION__,sprintf("cart is now [%s]",print_r($_SESSION["cart"],true)),4);
				}
			}
			else $_SESSION["cart"]["addresses"]["shipping"] = array("id"=>0);
			$_SESSION["cart"]["addresses"]["shipping"]["addresstype"] = ADDRESS_DELIVERY;
		}
		else
			$addresses = $this->fetchAllTest("select * from addresses where ownertype='member' and ownerid = %d and address_book=1 and deleted = 0 and addresstype != %d order by company, line1",$member, ADDRESS_COMPANY);
		$result = array();
		foreach($addresses as $key=>$address) {
			$inner->reset();
			$inner->addData(Address::formatData($address));
			$result[] = $inner->show();
		}
		if (array_key_exists("a_id",$_REQUEST) && $_REQUEST["a_id"] == 0) {
			$result[] = $inner->show();
		}
		if (array_key_exists("shippingAddressForm",$_REQUEST) && array_key_exists("saveAddress",$_REQUEST)) {
			$inner->addData($_POST);
			if ($inner->validate()) {
				$editFlds = $inner->buildForm($editFlds);
				$values = array();
				$lat = 0;
				$long = 0;
				$tmp = array("line1"=>$inner->getData($editFlds['line1']['name']),
											"city"=>$inner->getData($editFlds['city']['name']),
											"province_id"=>$inner->getData($editFlds['province_id']['name']),
											"country_id"=>$inner->getData($editFlds['country_id']['name']),
											"postalcode"=>$inner->getData($editFlds['postalcode']['name']));
				if ($this->geoCode($tmp,$lat,$long)) {
					$values["latitude"] = $lat;
					$values["longitude"] = $long;
				}
				foreach($editFlds as $key=>$fld) {
					if (!(array_key_exists('database',$fld) && $fld['database'] == false)) {
						$values[$fld['name']] = $inner->getData($fld['name']);
					}
				}
				$values["ownerid"] = $member;
				$values["ownertype"] = "member";
				$values["addresstype"] = ADDRESS_DELIVERY;	//$this->fetchScalarTest("select id from code_lookups where type='memberAddressTypes' order by sort asc limit 1");
				if ($_REQUEST["a_id"] == 0) {
					$stmt = $this->prepare(sprintf("insert into addresses(%s) values(?%s)",implode(", ",array_keys($values)),str_repeat(", ?",count($values)-1)));
				}
				else {
					$stmt = $this->prepare(sprintf("update addresses set %s=? where id = %d",implode("=?, ",array_keys($values)),$_REQUEST["a_id"]));
				}
				$stmt->bindParams(array_merge(array(str_repeat("s",count($values))),array_values($values)));
				if ($stmt->execute()) {
					if ($_REQUEST["a_id"] == 0)
						$_REQUEST["a_id"] = $this->insertId();
					$inner->setData("a_id",$_REQUEST["a_id"]);
					if (strlen($module["parm1"]) > 0)
						$inner->init($this->m_dir.$module["parm1"]);
				}
			}
			$result = array($inner->show());
		}
		$outer->addTag("addresses",implode("",$result),false);
		$subdata = $this->subForms($this->m_module['fetemplate_id'],null,array(),'outer');
		$this->logMessage(__FUNCTION__,sprintf('subforms [%s]',print_r($subdata,true)),3);
		foreach($subdata as $key=>$value) {
			$outer->addTag($key,$value,false);
		}
		if ($this->checkArray("cart:addresses:shipping:id",$_SESSION) && $_SESSION["cart"]["addresses"]["shipping"]["id"] > 0) {
			$outer->setData("hasShipping",$_SESSION["cart"]["addresses"]["shipping"]["id"]);
		}
		return $outer->show();
	}

    /**
     * @return array|mixed|string|string[]
     * @throws phpmailerException
     */
    function setAddressType() {
		if (!$module = $this->getModule())
			return "";
		$outer = new Forms();
		$outer->init($this->m_dir.$module["outer_html"]);
		if (!array_key_exists("cart",$_SESSION)) $cart = Ecom::initCart();
		$_SESSION["cart"]["addresses"][$_REQUEST["a_type"]] = $this->fetchSingleTest("select * from addresses where id = %d",$_REQUEST["a_id"]);
		$outer->addData($_SESSION["cart"]["addresses"][$_REQUEST["a_type"]]);
		return $outer->show();
	}

    /**
     * @return array|mixed|string|string[]
     * @throws phpmailerException
     */
    public function checkSubsidiary() {
		if (!$module = $this->getModule())
			return "";
		$outer = new Forms();
		$outer->init($this->m_dir.$module["outer_html"]);
		$outer->setModule($module);
		$flds = $outer->buildForm($this->config->getFields($module['configuration']));
		$inner = new Forms();
		$inner->init($this->m_dir.$module["inner_html"]);
		if (array_key_exists("checkSubsidiary",$_REQUEST)) {
			if ($_REQUEST["c_id"] > 0) {
				if (!array_key_exists("mgmt",$_SESSION)) $_SESSION["mgmt"]["user"] = $this->fetchSingleTest("select * from members where id = %d", $this->getUserInfo("id"));
				if ($company = $this->fetchSingleTest("select * from members where id = %d and (custom_parent_org = %d || %d = 1) and id not in (select member_id from drivers)", $_REQUEST["c_id"], $_SESSION["mgmt"]["user"]["id"],$_SESSION["mgmt"]["user"]["custom_super_user"])) {
					if ($this->logMeIn($company["username"],$company["password"],0,$company["id"])) {
						$_SESSION["cart"] = Ecom::initCart();
						if (strlen($module["parm1"]) > 0)
							$outer->init($this->m_dir.$module["parm1"]);
					}
				}
			}
			else {
				if (array_key_exists("mgmt",$_SESSION)) {
					if ($company = $this->fetchSingleTest("select * from members where id = %d", $_SESSION["mgmt"]["user"]["id"])) {
						if ($this->logMeIn($company["username"],$company["password"],0,$company["id"])) {
							$_SESSION["cart"] = Ecom::initCart();
							if (strlen($module["parm1"]) > 0)
								$outer->init($this->m_dir.$module["parm1"]);
						}
					}
				}
			}
		}
		$test = $this->getUserInfo("custom_super_user") > 0  || ($this->checkArray("mgmt:user:custom_super_user",$_SESSION) && $_SESSION["mgmt"]["user"]["custom_super_user"] > 0);
		if ($test) {
			//$companies = $this->fetchAllTest("select * from members where deleted = 0 and enabled = 1 and custom_on_account = 1 and id not in (select member_id from drivers) order by company, lastname");
			$companies = $this->fetchAllTest("select * from members where deleted = 0 and enabled = 1 and id not in (select member_id from drivers) order by company, lastname");
		}
		else {
			if (array_key_exists("mgmt",$_SESSION) && array_key_exists("user",$_SESSION["mgmt"]) && $_SESSION["mgmt"]["user"]["id"] > 0) {
				$companies = $this->fetchAllTest("select * from members where (custom_parent_org = %d || %d = 1) and deleted = 0 and enabled = 1 and id not in (select member_id from drivers) order by company, lastname",$_SESSION["mgmt"]["user"]["id"],$_SESSION["mgmt"]["user"]["custom_super_user"]);
			}
			else {
				$companies = $this->fetchAllTest("select * from members where custom_parent_org = %d and deleted = 0 and enabled = 1 order by company, lastname",$this->getUserInfo("id"));
			}
		}
		if (array_key_exists("mgmt",$_SESSION)) {
			$ho = $this->fetchSingleTest("select * from members where (id=%d || id=%d)",$_SESSION["mgmt"]["user"]["id"], $_SESSION["mgmt"]["user"]["custom_super_user"] );
			$outer->addData($ho);
		}
		else {
			$outer->addData(array_key_exists("user",$_SESSION) ? $_SESSION["user"]["info"] : array());
		}
		$result = array();
		foreach($companies as $key=>$value) {
			$inner->reset();
			if ($this->getUserInfo("id") == $value["id"]) $inner->setData("c_id",$value["id"]);
			$inner->addTag("selected",$this->getUserInfo("id") == $value["id"] ? "selected" : "");
			$inner->addData($value);
			$result[] = $inner->show();
		}
		$outer->setData("companyCount",count($companies));
		$outer->addTag("companies",implode("",$result),false);
		$subdata = $this->subForms($this->m_module['fetemplate_id'],null,array(),'outer');
		$this->logMessage(__FUNCTION__,sprintf('subforms [%s]',print_r($subdata,true)),3);
		foreach($subdata as $key=>$value) {
			$outer->addTag($key,$value,false);
		}
		return $outer->show();
	}

    /**
     * @return array|false|mixed|string|string[]
     * @throws SoapFault
     * @throws phpmailerException
     */
    function selectService() {
		if (!$module = $this->getModule())
			return "";

		if (array_key_exists("prod",$_REQUEST) && is_array($_REQUEST["prod"])) {
			foreach($_REQUEST["prod"] as $k=>$v) {
				$ct = 0;
				$wt = 0;
				foreach($v["dimensions"] as $sk=>$sv) {
					$ct += $sv["quantity"];
					$wt += $sv["weight"] * $sv["quantity"];
				}
				$_REQUEST["prod"][$k]["quantity"] = $ct;
				$_REQUEST["prod"][$k]["custom_weight"] = $wt;
			}
			$_POST["prod"] = $_REQUEST["prod"];
		}
		if (array_key_exists("addPkg",$_REQUEST)) return $this->selectServiceRow($module);
		$cart = Ecom::getCart();
		$outer = new Forms();
		$outer->init($this->m_dir.$module["outer_html"]);
		$outer->setModule($module);
		$outer->setData("hasDryIce", 0);
		if ($this->checkArray("extras", $_REQUEST)) {
			foreach($_REQUEST["extras"] as $k=>$v) {
				if ($v == DRY_ICE) $outer->setData("hasDryIce",1);
			}

		}
		$addressValid = true;
		if (!($this->checkArray("addresses:shipping:country_id",$cart) && $cart["addresses"]["shipping"]["country_id"] > 0)) {
			$outer->addFormError("Invalid Shipping Address");
			$addressValid = false;
		}
		if (!($this->checkArray("addresses:pickup:country_id",$cart) && $cart["addresses"]["pickup"]["country_id"] > 0)) {
			$outer->addFormError("Invalid Pickup Address");
			$addressValid = false;
		}

		$inner = new Forms();
		$inner->init($this->m_dir.$module["inner_html"]);
		$m_id = $this->getUserInfo("id");
		if (!$group = $this->fetchSingleTest("select mf.* from members_folders mf, members_by_folder mb where mb.member_id = %d and mf.id = mb.folder_id",$m_id))
			$group = array("id"=>0, "custom_additional_services"=>0);
		$g_id = $group["id"];	//$this->fetchScalarTest("select folder_id from members_by_folder where member_id = %d",$m_id);
		$flds = $this->config->getFields($module["configuration"]);
		if ($this->getUserInfo("custom_additional_services") > 0)
			$flds["extras"]["sql"] = sprintf('select p.id,name from product p, product_by_folder pf where pf.folder_id = %d and p.id = pf.product_id and p.deleted = 0 and p.published = 1 and p.enabled = 1 order by pf.sequence', $this->getUserInfo("custom_additional_services"));
		else
			$flds["extras"]["sql"] = sprintf('select p.id,name from product p, product_by_folder pf where pf.folder_id = %d and p.id = pf.product_id and p.deleted = 0 and p.published = 1 and p.enabled = 1 order by pf.sequence', $group["custom_additional_services"]);
		$dtFrm = new Forms();
		$tmpFlds = $flds;
		$tmpFlds = $dtFrm->buildForm($tmpFlds);
		if (array_key_exists(__FUNCTION__,$_REQUEST)) {
			$dtFrm->addData($_REQUEST);
			$outer->addData($_REQUEST);
		}
		else {
			$dtFrm->addData(array_key_exists("quote",$_SESSION) ? $_SESSION["quote"] : array("pickup_datetime"=>date(DATE_ATOM)));
			$outer->addData(array_key_exists("quote",$_SESSION) ? $_SESSION["quote"] : array("pickup_datetime"=>date(DATE_ATOM)));
		}
		$dtFrm->validate();
		$this->logMessage(__FUNCTION__, sprintf("dtForm [%s]", print_r($dtFrm,true)),4);
		$pu_dt = $dtFrm->getData("pickup_datetime");
		$pu_dow = date("w",strtotime($pu_dt));
		if(!$group = $this->fetchSingleTest("select mf.* from members_folders mf, members_by_folder mb where mb.member_id = %d and mf.id = mb.folder_id",$m_id))
			$group = array("id"=>0,"custom_package_types"=>0);
		$g_id = $group["id"];	//$this->fetchScalarTest("select folder_id from members_by_folder where member_id = %d",$m_id);
//		$m_services = $this->fetchAllTest("select c.id, p.name, c.product_id, p.custom_minimum_charge, p.custom_km_mincharge from product p, custom_member_product_options c, members_by_folder mf where p.id = c.product_id and c.member_id = mf.id and mf.member_id = %d and mf.folder_id = %d and isgroup = 0 and is_fedex = 0 and p.deleted = 0 and p.published = 1 and p.enabled = 1 and custom_availability & %3\$d = %3\$d and unavailable = 0",$m_id,$g_id, 2**$pu_dow);
		$super = $this->checkArray("mgmt:user:custom_super_user",$_SESSION) && $_SESSION["mgmt"]["user"]["custom_super_user"] == 1;
		//$m_temp = $this->fetchAllTest("select 0 as total, c.*, p.name, p.custom_minimum_charge, p.custom_km_mincharge from product p, custom_member_product_options c, members_by_folder mf where p.id = c.product_id and c.member_id = mf.id and mf.member_id = %d and mf.folder_id = %d and isgroup = 0 and is_fedex = 0 and p.deleted = 0 and p.published = 1 and p.enabled = 1 and custom_availability & %3\$d = %3\$d and unavailable = 0 and (admin_only = 0 or %4\$d = 1)",$m_id,$g_id, 2**$pu_dow, $super);
		$m_temp = $this->fetchAllTest(sprintf("select 0 as total, c.*, p.name, p.custom_minimum_charge, p.custom_km_mincharge from product p, custom_member_product_options c, members_by_folder mf where p.id = c.product_id and c.member_id = mf.id and mf.member_id = %d and mf.folder_id = %d and isgroup = 0 and is_fedex = 0 and p.deleted = 0 and p.published = 1 and p.enabled = 1 and custom_availability & %3\$d = %3\$d and unavailable = 0 and (admin_only = 0 or %4\$d = 1)",$m_id,$g_id, 2**$pu_dow, $super));
		$tmp = array(0);
		foreach($m_temp as $key=>$value) {
			$tmp[] = $value["product_id"];
		}
		if (!$m_remove = $this->fetchScalarAllTest("select c.product_id from product p, custom_member_product_options c, members_by_folder mf where p.id = c.product_id and c.member_id = mf.id and mf.member_id = %d and mf.folder_id = %d and isgroup = 0 and is_fedex = 0 and p.deleted = 0 and p.published = 1 and p.enabled = 1 and unavailable = 1",$m_id,$g_id))
			$m_remove = array(0=>0);
//		$g_services = $this->fetchAllTest("select c.id, p.name, c.product_id, p.custom_minimum_charge, p.custom_km_mincharge from product p, custom_member_product_options c where p.id = c.product_id and c.member_id = %d and isgroup = 1 and p.id not in (%s) and is_fedex = 0 and p.deleted = 0 and p.published = 1 and p.enabled = 1 and c.product_id not in (%s) and custom_availability & %4\$d = %4\$d order by p.custom_minimum_charge",$g_id, implode(",",array_merge(array(0),$tmp)), implode(", ", $m_remove), 2**$pu_dow);
		//$g_temp = $this->fetchAllTest("select 0 as total, c.*, p.name, p.custom_minimum_charge, p.custom_km_mincharge from product p, custom_member_product_options c where p.id = c.product_id and c.member_id = %d and isgroup = 1 and p.id not in (%s) and is_fedex = 0 and p.deleted = 0 and p.published = 1 and p.enabled = 1 and custom_availability & %3\$d = %3\$d and p.id not in (%4\$s) and (admin_only = 0 or %5\$d = 1) order by p.custom_minimum_charge",$g_id, implode(",",array_merge(array(0),$tmp)), 2**$pu_dow, implode(", ", $m_remove), $super);
		$g_temp = $this->fetchAllTest(sprintf("select 0 as total, c.*, p.name, p.custom_minimum_charge, p.custom_km_mincharge from product p, custom_member_product_options c where p.id = c.product_id and c.member_id = %d and isgroup = 1 and p.id not in (%s) and is_fedex = 0 and p.deleted = 0 and p.published = 1 and p.enabled = 1 and custom_availability & %3\$d = %3\$d and p.id not in (%4\$s) and (admin_only = 0 or %5\$d = 1) order by p.custom_minimum_charge",$g_id, implode(",",array_merge(array(0),$tmp)), 2**$pu_dow, implode(", ", $m_remove), $super));
//		$services = array_merge($m_services, $g_services);
		$s_temp = array_merge($m_temp, $g_temp);
		$kjv = new KJV();
		if ($this->checkArray("addresses:pickup:id",$cart) && $this->checkArray("addresses:shipping:id", $cart)) {
			if ($this->getUserInfo("custom_by_km") == 1) {
				if ($this->checkArray("cart:custom:ourRates", $_SESSION) && count($_SESSION["cart"]["custom"]["ourRates"]) > 0 && array_key_exists("optType",$_REQUEST) && $_REQUEST["optType"] == "O") {
					$this->logMessage(__FUNCTION__,sprintf("not calling getWalkingDistance"),1);
					$km = 0;	// we alreday have fedex rates & asking for the final quote
				}
				else
					$km = $kjv->getWalkingDistance($cart["addresses"]);
			}
			else $km = 0;
			$cart = Ecom::getCart();
			$dbg = $this->getDebug();
			if (array_key_exists("prod",$_REQUEST)) {
				$_SESSION["quote"] = $_REQUEST;
			}
			if ($this->checkArray("quote:prod",$_SESSION)) {
				$_SESSION["cart"]["header"]["km_calced"] = $km;
				//$_SESSION["quote"]["prod"] = $_REQUEST["prod"];
				foreach($s_temp as $k=>$v) {
					$quote = $kjv->getPrice( $_SESSION["quote"]["prod"], $this->checkArray("quote:extras",$_SESSION) ? $_SESSION["quote"]["extras"] : array(), $cart["addresses"]["pickup"], $cart["addresses"]["shipping"], $v["id"], $_SESSION["quote"]["custom_weight_code"], $_SESSION["quote"]["custom_dimension_code"]);
					if ($this->checkArray("cart:custom:ourRates", $_SESSION)) {
						$this->logMessage(__FUNCTION__, sprintf("/*/*/* have a fedex delivery [%s]", print_r($_SESSION["cart"]["custom"]["ourRates"],true)),1);
						break 1;
					}
					$s_temp[$k]["total"] = $_SESSION["cart"]["header"]["total"];
				}
				unset($_SESSION["cart"]["header"]["km_calced"]);
			}
		}
		else {
			$this->logMessage(__FUNCTION__,sprintf("*** skipped getWalkingDistance due to no addresses"),1);
		}
		usort($s_temp,'customServiceSort');
		$result = array();
		foreach($s_temp as $key=>$rec) {
			if ($rec["total"] < 0.01) 
				$result[$rec["id"]] = $rec["name"];
			else
				$result[$rec["id"]] = sprintf("%s (Approx. %s)", $rec["name"], $this->my_money_format($rec["total"]));
		}
		$flds = $outer->buildForm($flds);
		$outer->getField("serviceType")->setOptions($result);
		$i_flds = $this->config->getFields($module["configuration"]."Row");
		$outer->setData("products",implode("",$this->getProducts($inner,$outer,$module,$i_flds,$s_temp,false)),false);

		$outer->addData(array(
			"pickupInstructions"=>array_key_exists("pickupInstructions",$cart["header"]) ? $cart["header"]["pickupInstructions"] : "",
			"deliveryInstructions"=>array_key_exists("deliveryInstructions",$cart["header"]) ? $cart["header"]["deliveryInstructions"] : "",
			"custom_insurance"=>array_key_exists("custom_insurance",$cart["header"]) ? $cart["header"]["custom_insurance"] : "",
			"custom_reference_number"=>array_key_exists("custom_reference_number",$cart["header"]) ? $cart["header"]["custom_reference_number"] : "",
			"custom_declared_value"=>array_key_exists("custom_declared_value",$cart["header"]) ? $cart["header"]["custom_declared_value"] : ""
		));
		if (array_key_exists(__FUNCTION__,$_REQUEST)) {

			$cart["header"]["custom_override_price"] = array_key_exists("custom_override_price",$_REQUEST) ? $_REQUEST["custom_override_price"] : 0.00;
			$this->logMessage(__FUNCTION__,sprintf("price override cart [%s] request [%s]", print_r($cart,true), print_r($_REQUEST,true)),1);

			$outer->addData($_REQUEST);
			$valid = $outer->validate();
			$recs = array();
			$prods = array();
			$msg = array(0=>array(),1=>array());
			if ($valid) {
				if (array_key_exists("recurring", $_REQUEST)) {
					$cart["recurring"] = $_REQUEST["recurring"];
					$this->logMessage(__FUNCTION__,sprintf("adding recurring to cart [%s]", print_r($_SESSION,true)),1);
				}
			}
			if ($valid) {
				if (array_key_exists("serviceType",$_REQUEST) && $this->checkArray("cart:header:inzone",$_SESSION) && $_SESSION["cart"]["header"]["inzone"] == 1) {
					if ($product = $this->fetchSingleTest("select p.*, c.cutoff_time from product p, custom_member_product_options c where c.id = %d and p.id = c.product_id",$_REQUEST["serviceType"])) {
						if ($product["cutoff_time"] != "00:00:00") $product["custom_cutoff_time"] = $product["cutoff_time"];
						$cart["header"]["pickup_datetime"] = max(date("Y-m-d H:i:s"),$outer->getData("pickup_datetime"));
						$cart["header"]["pickupInstructions"] = array_key_exists("pickupInstructions",$_REQUEST) ? $_REQUEST["pickupInstructions"] : "";
						$cart["header"]["deliveryInstructions"] = array_key_exists("deliveryInstructions",$_REQUEST) ? $_REQUEST["deliveryInstructions"] : "";
//
//	test to see if the service is available on the pickup date selected
//
						$pu_time = strtotime($cart["header"]["pickup_datetime"]);
						$wd = pow(2,date("w", $pu_time));
						if (0 == ($product["custom_availability"] & $wd)) {
							$this->logMessage(__FUNCTION__,sprintf("in office closed"),1);
							if ($this->checkArray("mgmt:user:custom_super_user",$_SESSION) && $_SESSION["mgmt"]["user"]["custom_super_user"] == 1) {
								$this->logMessage(__FUNCTION__,sprintf("super user override of office closed check"),1);
								$outer->addFormSuccess("Warning: Overriding Office Closed");
							}
							else {
								$valid = false;
								$this->logMessage(__FUNCTION__,sprintf("pickup day is not available [%s] vs [%s] from [%s]",$cart["header"]["pickup_datetime"],date(DATE_ATOM,strtotime($product["custom_cutoff_time"])),$product["custom_cutoff_time"]),2);
								$outer->addFormError("This service is not available on the pickup date you selected");
							}
						}
//
//	test to see if the office is closed on the pickup date selected
//
						if ($isClosed = $this->fetchSingleTest("select * from office_schedule where office_date = '%s'", date("Y-m-d",$pu_time))) {
							if ($isClosed["closed"] == 1 || ($isClosed["close_time"] != "00:00:00" && $isClosed["close_time"] <= date("H:i:s",$pu_time))) {
								if ($this->checkArray("mgmt:user:custom_super_user",$_SESSION) && $_SESSION["mgmt"]["user"]["custom_super_user"] == 1) {
									$this->logMessage(__FUNCTION__,sprintf("super user override of office closed check"),1);
									$outer->addFormSuccess("Warning: Overriding Office Closed");
								}
								else {
									$valid = false;
									$this->logMessage(__FUNCTION__,sprintf("office is closed [%s] vs [%s]",$cart["header"]["pickup_datetime"],print_r($isClosed,true)),2);
									$outer->addFormError("The office is/will be closed on the pickup date/time you selected");
								}
							}
						}
//
//	kjv "super user" can override the too late to pick up
//
						if (date("Y-m-d",$pu_time) == date("Y-m-d") && date(DATE_ATOM,$pu_time) > date(DATE_ATOM,strtotime($product["custom_cutoff_time"]))) {
							if ($this->checkArray("mgmt:user:custom_super_user",$_SESSION) && $_SESSION["mgmt"]["user"]["custom_super_user"] == 1) {
								$this->logMessage(__FUNCTION__,sprintf("super user override of too late check"),1);
							}
							else {
								$valid = false;
								$this->logMessage(__FUNCTION__,sprintf("pickup time is too late [%s] vs [%s] from [%s]",$cart["header"]["pickup_datetime"],date(DATE_ATOM,strtotime($product["custom_cutoff_time"])),$product["custom_cutoff_time"]),2);
								$outer->addFormError("It's too late to pick this package up today");
							}
						}
						$this->logMessage(__FUNCTION__,sprintf("cart pickup [%s/%s] product pickup [%s/%s]", 
									date("H:i:s",strtotime($cart["header"]["pickup_datetime"])), $cart["header"]["pickup_datetime"],
									date("H:i:s",strtotime($product["custom_pickup_start"])), $product["custom_pickup_start"]), 3);
						if (date("H:i:s",$pu_time) < $product["custom_pickup_start"]) {
							$valid = false;
							$this->logMessage(__FUNCTION__,sprintf("pickup time is too early [%s] vs [%s] from [%s]",$cart["header"]["pickup_datetime"],date(DATE_ATOM,strtotime($product["custom_pickup_start"])),$product["custom_cutoff_time"]),2);
							$outer->addFormError(sprintf("The earliest pickup time is %s", date("h:i a",strtotime($product["custom_pickup_start"]))));
						}
					}
					else {
						$this->logMessage(__FUNCTION__,sprintf("invalid product searched for [%s] request [%s] session [%s]",$_REQUEST["serviceType"],print_r($_REQUEST,true),print_r($_SESSION,true)),1,true);
						$outer->addFormError("An internal error occurred<br/>The Web Master has been notified");
						$valid = false;
					}
				}
			}
			if ($valid) {
				//
				//	Got a product. Get the rate & all overrides
				//
				$cart["header"]["custom_weight_code"] = $_REQUEST["custom_weight_code"];
				$cart["header"]["custom_dimension_code"] = $_REQUEST["custom_dimension_code"];
				$cart["header"]["custom_insurance"] = array_key_exists("custom_insurance",$_REQUEST) ? $_REQUEST["custom_insurance"] : 0;
				$cart["header"]["custom_declared_value"] = array_key_exists("custom_declared_value",$_REQUEST) ? $_REQUEST["custom_declared_value"] : 0;
				$cart["header"]["custom_reference_number"] = array_key_exists("custom_reference_number",$_REQUEST) ? $_REQUEST["custom_reference_number"] : 0;
				$_SESSION["cart"] = $cart;
				$_SESSION["quote"] = $_REQUEST;
				if (strlen($module["parm1"]) > 0)
					$outer->init($this->m_dir.$module["parm1"]);
			}
			else {
				if (count($msg[0]) > 0 || count($msg[1]) > 0) {
					$msgTmp = "";
					if (strlen($tmp = implode("<br/>",$msg[0])) > 5) {
						$msgTmp = sprintf('<div class="alert alert-success">%s</div>',$tmp,false);
					}
					if (strlen($tmp = implode("<br/>",$msg[1])) > 5) {
						$msgTmp .= sprintf('<div class="alert alert-danger">%s</div>',$tmp,false);
					}
					if (strlen($msgTmp) > 0) $outer->addTag("errorMessage",$msgTmp,false);
					$this->logMessage(__FUNCTION__,sprintf("zoiks [%s] errors [%s]", print_r($outer,true), print_r($msg,true)),1,true,true);
				}
				//$outer->addTag("products",implode("",$recs),false);
			}
		}
		$outer->addTag("service",implode("",$result),false);
		$subdata = $this->subForms($this->m_module['fetemplate_id'],null,array('folder_id'=>$group["custom_package_types"]),'outer');
		foreach($subdata as $key=>$value) {
			$outer->addTag($key,$value,false);
		}
		if ($this->checkArray("recurring",$cart)) {
			$outer->addData(array('recurring'=>$cart["recurring"]));
			$outer->addData(array("custom_recurring"=>1));
			$outer->getField("custom_recurring_type")->removeAttribute("disabled");
		}
		return $outer->show();
	}

    /**
     * @param $inner
     * @param $outer
     * @param $module
     * @param $i_flds
     * @param $services
     * @param $fromCart
     * @return array
     * @throws phpmailerException
     */
    private function getProducts(&$inner, &$outer, $module, $i_flds, $services, $fromCart) {
		$this->logMessage(__FUNCTION__,sprintf("services [%s] inner [%s] outer [%s] module [%s] fields [%s]", print_r($services,true), print_r($inner,true), print_r($outer,true), print_r($module,true), print_r($i_flds,true)),3);
		$result = array();
		$p = new product(0);
		$i_flds["product_id"]["sql"] = sprintf("select p.id, p.name, p.id as xxx from product p, product_by_folder pf, members_folders m, members_by_folder mf where mf.member_id = %d and m.id = mf.folder_id and pf.folder_id = m.custom_package_types and p.id = pf.product_id and p.enabled = 1 and p.deleted = 0 order by pf.sequence",$this->getUserInfo("id"));
		$i_flds = $inner->buildForm($i_flds);
		$row = 1;
		$source = array();
		if ($this->checkArray("quote:prod",$_SESSION) && is_array($_SESSION["quote"]["prod"]))
			$source = $_SESSION["quote"]["prod"];
		if (array_key_exists("prod",$_REQUEST)) {
			foreach($_REQUEST["prod"] as $key=>$rec) {
				$inner->reset();
				$rec["product"] = $p->formatData($this->fetchSingleTest("select * from product where id = %d",$rec["product_id"]));
				$rec["row"] = $key;
				$inner->addData($rec);
				$subdata = $this->subForms($this->m_module['fetemplate_id'],null,array("row"=>$key,"seq"=>1,"qty"=>$rec["quantity"],"wt"=>0,"product"=>$rec),'inner');
				$this->logMessage(__FUNCTION__,sprintf('subforms [%s]',print_r($subdata,true)),3);
				foreach($subdata as $key=>$value) {
					$inner->setData($key,$value,false);
				}
				$result[] = $inner->show();
				$row += 1;
			}
		}
		else {
			$ct = 0;
			foreach($source as $key=>$rec) {
				$ct += 1;
				$inner->reset();
				$rec["product"] = $p->formatData($this->fetchSingleTest("select * from product where id = %d",$rec["product_id"]));
				$rec["row"] = $ct;
				$inner->addData($rec);
				$subdata = $this->subForms($this->m_module['fetemplate_id'],null,array("row"=>$row,"seq"=>1,"qty"=>$rec["quantity"],"wt"=>0,"product"=>$rec),'inner');
				foreach($subdata as $key=>$value) {
					$inner->addTag($key,$value,false);
				}
				$result[] = $inner->show();
				$row += 1;
			}
		}
		if (count($result) == 0) {
			$inner->reset();
			$inner->addData(array());
			$inner->setData("row",$row);
			$subdata = $this->subForms($this->m_module['fetemplate_id'],null,array("row"=>$row,"seq"=>1,"qty"=>0,"wt"=>0),'inner');
			foreach($subdata as $key=>$value) {
				$inner->addTag($key,$value,false);
			}
			$result[] = $inner->show();
		}
		$this->logMessage(__FUNCTION__,sprintf("returning [%s] from request [%s]", print_r($result,true), print_r($_REQUEST,true)),3);
		return $result;
	}

    /**
     * @param $inner
     * @param $outer
     * @param $module
     * @return array
     * @throws phpmailerException
     */
    private function getExtras(&$inner, &$outer, $module) {
		$result = array();
		$p = new product(0);
		$flds = $this->getFields($module["configuration"]."Extras");
		$flds = $inner->buildForm($flds);
		$recs = array();
		if (array_key_exists("extras",$_REQUEST)) {
			foreach($_REQUEST["extras"] as $key=>$rec) {
				if ($rec > 0) {
					$inner->reset();
					$inner->addData($p->formatData($this->fetchSingleTest("select * from product where id = %d",$rec)));
					$inner->setData("extras",$rec);
					$recs[] = $inner->show();
				}
			}
		}
		if (count($recs)==0) $recs[] = $inner->show();
		return $recs;
	}

    /**
     * @param $module
     * @return array|false|mixed|string|string[]
     * @throws phpmailerException
     */
    function selectServiceRow($module) {
		$outer = new Forms();
		$outer->init($this->m_dir.$module["outer_html"]);
		$outer->setModule($module);
		$inner = new Forms();
		$inner->init($this->m_dir.$module["inner_html"]);
		$m_id = $this->getUserInfo("id");
		$flds = $this->config->getFields($module["configuration"]);
		$i_flds = $inner->buildForm($this->config->getFields($module["configuration"]."Row"));
		if (array_key_exists("pkg",$_REQUEST) && count($_REQUEST["pkg"]) > 0)
			$exists = implode(",",$_REQUEST["pkg"]);
		else $exists = "0";
		$inner->getField("product_id")->setOptions($this->fetchOptionsTest("select p.id, p.name from product p, product_by_folder pf, members_folders m, members_by_folder mf where mf.member_id = %d and m.id = mf.folder_id and pf.folder_id = m.custom_package_types and p.id = pf.product_id and p.enabled = 1 and p.deleted = 0 and p.id not in (%s) order by p.name",$this->getUserInfo("id"),$exists));
		$inner->setData('row',$_REQUEST["r_ct"]);
		$subdata = $this->subForms($this->m_module['fetemplate_id'],null,array("row"=>$_REQUEST["r_ct"],"seq"=>1,"qty"=>0,"wt"=>0),'inner');
		$this->logMessage(__FUNCTION__,sprintf('subforms [%s]',print_r($subdata,true)),3);
		foreach($subdata as $key=>$value) {
			$inner->addTag($key,$value,false);
		}
		if ($this->isAjax())
			return $this->ajaxReturn(array('status'=>true,'html'=>$inner->show()));
		else 
			return $inner->show();
	}

    /**
     * @param $module
     * @return array|false|mixed|string|string[]
     * @throws phpmailerException
     */
    function selectServiceDimensions($module = null) {
		if (!is_array($module)) $module = $this->getModule();
		$outer = new Forms();
		$outer->init($this->m_dir.$module["outer_html"]);
		$outer->setModule($module);
		$inner = new Forms();
		$inner->init($this->m_dir.$module["inner_html"]);
		$m_id = $this->getUserInfo("id");
		$flds = $inner->buildForm($this->config->getFields($module["configuration"]));
		$rows = array();
		if (array_key_exists("product",$module) && array_key_exists("dimensions",$module["product"]) && count($module["product"]["dimensions"]) > 0) {
			$ct = 1;
			foreach($module["product"]["dimensions"] as $k=>$d) {
				$inner->reset();
				$inner->addData(array(
					"qty" => $d["quantity"],
					"row" => $module["row"],
					"seq" => array_key_exists("sequence",$d) ? $d["sequence"] : $k,
					"sequence" => array_key_exists("sequence",$d) ? $d["sequence"] : $k,
					"wt" => $d["weight"],
					"ht"=>$d["height"],
					"wd"=>$d["width"],
					"dp"=>$d["depth"],
					"ct"=>$ct
				));
				$ct += 1;
				$rows[] = $inner->show();
			}
		}
		else {
			if (array_key_exists("addPkg",$_REQUEST)) {
				if (array_key_exists("pkg",$_REQUEST) && count($_REQUEST["pkg"]) > 0)
					$exists = implode(",",$_REQUEST["pkg"]);
				else $exists = "0";
				$o = $this->fetchOptionsTest("select p.id, p.name from product p, product_by_folder pf, members_folders m, members_by_folder mf where mf.member_id = %d and m.id = mf.folder_id and pf.folder_id = m.custom_package_types and p.id = pf.product_id and p.enabled = 1 and p.deleted = 0 and p.id not in (%s) order by p.name",$this->getUserInfo("id"),$exists);
				$p = 0;
				foreach($o as $key=>$value) {
					if ($p==0) $p=$key;
				}
				$p = $this->fetchSingleTest("select * from product where id = %d",$p);
				$inner->addData(array(
					"ht"=>$p["height"],
					"wd"=>$p["width"],
					"dp"=>$p["depth"],
					"qty"=>0,
					"row" => array_key_exists("row",$_REQUEST) ? $_REQUEST["row"] : $module["row"],
					"seq" => array_key_exists("seq",$_REQUEST) ? $_REQUEST["seq"] : $module["seq"],
					"sequence"=>array_key_exists("seq",$_REQUEST) ? $_REQUEST["seq"] : $module["seq"],
					"ct"=>1
				));
				$this->logMessage(__FUNCTION__,sprintf("inner is [%s]",print_r($inner,true)),1);
			}
			else {
				$inner->addData(array(
						"qty" => array_key_exists("qty",$_REQUEST) ? $_REQUEST["qty"] : $module["qty"],
						"row" => array_key_exists("row",$_REQUEST) ? $_REQUEST["row"] : $module["row"],
						"seq" => array_key_exists("seq",$_REQUEST) ? $_REQUEST["seq"] : $module["seq"],
						"wt" => array_key_exists("wt",$_REQUEST) ? $_REQUEST["wt"] : $module["wt"],
						"ht" => array_key_exists("ht",$_REQUEST) ? $_REQUEST["ht"] : 0,
						"wd" => array_key_exists("wd",$_REQUEST) ? $_REQUEST["wd"] : 0,
						"dp" => array_key_exists("dp",$_REQUEST) ? $_REQUEST["dp"] : 0,
						"sequence"=>array_key_exists("seq",$_REQUEST) ? $_REQUEST["seq"] : $module["seq"],
						"ct"=>1
					)
				);
			}
			$rows[] = $inner->show();
		}
		$outer->addTag("row",implode("",$rows),false);
		if ($this->isAjax())
			return $this->ajaxReturn(array('status'=>true,'html'=>$outer->show()));
		else 
			return $outer->show();
	}

    /**
     * @return array|mixed|string|string[]
     * @throws SoapFault
     * @throws phpmailerException
     */
    function KJVService() {
		if (!$module = $this->getModule())
			return "";
		$cart = Ecom::getCart();
		$outer = new Forms();
		$outer->init($this->m_dir.$module["outer_html"]);
		$outer->setModule($module);
		$inner = new Forms();
		$inner->init($this->m_dir.$module["inner_html"]);
		$m_id = $this->getUserInfo("id");
		$quote = $_SESSION["quote"];
		$quote["wt"] = $this->fetchSingleTest("select * from code_lookups where id = %d",$quote["custom_weight_code"]);
		$quote["sz"] = $this->fetchSingleTest("select * from code_lookups where id = %d",$quote["custom_dimension_code"]);
		$outer->addData($quote);
		$flds = $this->config->getFields($module["configuration"]);
		$group = $this->fetchSingleTest("select mf.* from members_folders mf, members_by_folder mb where mb.member_id = %d and mf.id = mb.folder_id",$m_id);
		$g_id = $group["id"];	//$this->fetchScalarTest("select folder_id from members_by_folder where member_id = %d",$m_id);
		$super = $this->checkArray("mgmt:user:custom_super_user",$_SESSION) && $_SESSION["mgmt"]["user"]["custom_super_user"] == 1;
		$m_services = $this->fetchAllTest("select c.id, p.name, c.product_id from product p, custom_member_product_options c, members_by_folder mf where p.id = c.product_id and c.member_id = mf.id and mf.member_id = %d and mf.folder_id = %d and isgroup = 0 and p.enabled = 1 and p.published = 1 and unavailable = 0 and (admin_only = 0 or %d = 1) order by p.name",$m_id,$g_id, $super);
		$tmp = array(0);
		foreach($m_services as $key=>$value) {
			$tmp[] = $value["product_id"];
		}

		if (!$m_remove = $this->fetchScalarAllTest("select c.product_id from product p, custom_member_product_options c, members_by_folder mf where p.id = c.product_id and c.member_id = mf.id and mf.member_id = %d and mf.folder_id = %d and isgroup = 0 and is_fedex = 0 and p.deleted = 0 and p.published = 1 and p.enabled = 1 and unavailable = 1",$m_id,$g_id))
			$m_remove = array(0=>0);

		$g_services = $this->fetchAllTest("select c.id, p.name, c.product_id from product p, custom_member_product_options c where p.id = c.product_id and c.member_id = %d and isgroup = 1 and p.id not in (%s) and p.id not in (%s) and (admin_only = 0 or %d = 1)",$g_id, implode(",",array_merge(array(0),$tmp)), implode(", ", $m_remove), $super);
		$services = array_merge($m_services, $g_services);
		$result = array();
		foreach($services as $key=>$rec) {
			$result[$rec["id"]] = $rec["name"];
		}
		$flds = $outer->buildForm($flds);
		$outer->getField("serviceType")->setOptions($result);
		$i_flds = $this->config->getFields($module["configuration"]."Row");
		$outer->addTag("products",implode("",$this->getProducts($inner,$outer,$module,$i_flds,$services,false)),false);
		$msg = array();
		if (array_key_exists(__FUNCTION__,$_REQUEST)) {
			$outer->addData($_REQUEST);
			$valid = $outer->validate();
			$recs = array();
			$prods = $_SESSION["quote"]["prod"];
			if ($valid) {
				foreach($prods as $sk=>$sv) {
					$valid &= $sv["quantity"] > 0;
					foreach($sv["dimensions"] as $kk=>$kv) {
						$valid &= $kv["quantity"] > 0 && $kv["weight"] > 0 && $kv["height"] > 0 && $kv["width"] > 0 && $kv["depth"] > 0;
					}
				}
				if (!$valid) {
					$outer->addFormError("Some quantity/weights are missing");
				}
			}
			$extras = array_key_exists("extras",$_SESSION["quote"]) ? $_SESSION["quote"]["extras"] : array();
			if ($valid) {
				$this->logMessage(__FUNCTION__,sprintf("test cutoff"),1);
				if ($product = $this->fetchSingleTest("select p.*, c.cutoff_time from product p, custom_member_product_options c where c.id = %d and p.id = c.product_id",$_REQUEST["serviceType"])) {

					if ($product["cutoff_time"] != "00:00:00") $product["custom_cutoff_time"] = $product["cutoff_time"];

					$cart["header"]["pickup_datetime"] = max(date("Y-m-d H:i:s"),$outer->getData("pickup_datetime"));
//
//	kjv "super user" can override the too late to pick up
//
					if (date("H:i",strtotime($cart["header"]["pickup_datetime"])) > date("H:i",strtotime($product["custom_cutoff_time"]))) {
						if ($this->checkArray("mgmt:user:custom_super_user",$_SESSION) && $_SESSION["mgmt"]["user"]["custom_super_user"] == 1) {
							$this->logMessage(__FUNCTION__,sprintf("super user override of too late check"),1);
						}
						else {
							$valid = false;
							$this->logMessage(__FUNCTION__,sprintf("pickup time is too late [%s] vs [%s] from [%s]",$cart["header"]["pickup_datetime"],date("Y-m-d H:i:s",strtotime($product["custom_cutoff_time"])),$product["custom_cutoff_time"]),2);
							$outer->addFormError(sprintf("Pickup time is after the cutoff time for this service"));
						}
					}
				}
				else {
					$this->logMessage(__FUNCTION__,sprintf("invalid product searched for [%s] request [%s] session [%s]",$_REQUEST["serviceType"],print_r($_REQUEST,true),print_r($_SESSION,true)),1,true);
					$outer->addFormError("An internal error occurred<br/>The Web Master has been notified");
					$valid = false;
				}
			}
			if ($valid) {
				//
				//	Got a product. Get the rate & all overrides
				//
				$cart["header"]["custom_weight_code"] = $_REQUEST["custom_weight_code"];
				$cart["header"]["custom_dimension_code"] = $_REQUEST["custom_dimension_code"];
				$_SESSION["cart"] = $cart;
				$kjv = new KJV();
				$quote = $kjv->getPrice($prods,$extras,$cart["addresses"]["pickup"],$cart["addresses"]["shipping"],$_REQUEST["serviceType"],$cart["header"]["custom_weight_code"],$cart["header"]["custom_dimension_code"]);
				$_SESSION["quote"] = $quote;
				$subdata = $this->subForms($this->m_module['fetemplate_id'],null,array(),'outer');
				$this->logMessage(__FUNCTION__,sprintf('subforms [%s]',print_r($subdata,true)),3);
				foreach($subdata as $key=>$value) {
					$outer->addTag($key,$value,false);
				}
				$outer->addTag("products",implode("",$this->getProducts($inner,$outer,$module,$i_flds,$prods,true)),false);
				$outer->addTag("quote",nl2br(print_r($quote,true)),false);
				if (strlen($module["parm1"]) > 0)
					$outer->init($this->m_dir.$module["parm1"]);
			}
			else {
				$msgTmp = "";
				if (count($msg) > 0) {
					if (strlen($tmp = implode("<br/>",$msg[0])) > 5) {
						$msgTmp = sprintf('<div class="alert alert-success">%s</div>',$tmp,false);
					}
					if (strlen($tmp = implode("<br/>",$msg[1])) > 5) {
						$msgTmp .= sprintf('<div class="alert alert-danger">%s</div>',$tmp,false);
					}
					if (strlen($msgTmp) > 0) $outer->addTag("errorMessage",$msgTmp,false);
				}
			}
		}
		$outer->addTag("service",implode("",$result),false);
		$err = $this->showEcomMessages();
		$this->logMessage(__FUNCTION__,sprintf("ecom errors [%s] form [%s]", print_r($err,true), print_r($outer,true)),4);
		$outer->setData("ecomErrors",$err);
		return $outer->show();
	}

    /**
     * @return array|mixed|string|string[]
     * @throws SoapFault
     * @throws phpmailerException
     */
    function FedExService() {
		//
		//	if no post, get the fedex rating for the current products
		//
		if (!$module = $this->getModule())
			return "";
		$cart = Ecom::getCart();
		$quote = $_SESSION["quote"];
		$outer = new Forms();
		$outer->init($this->m_dir.$module["outer_html"]);
		$outer->setModule($module);
		$inner = new Forms();
		$inner->init($this->m_dir.$module["inner_html"]);
		$m_id = $this->getUserInfo("id");
		$quote = $_SESSION["quote"];
		$quote["wt"] = $this->fetchSingleTest("select * from code_lookups where id = %d",$quote["custom_weight_code"]);
		$quote["sz"] = $this->fetchSingleTest("select * from code_lookups where id = %d",$quote["custom_dimension_code"]);
		$outer->addData($quote);
		$result = array();
		$flds = $outer->buildForm($this->config->getFields($module["configuration"]));
		$outer->addData($quote);
		if (!array_key_exists("rates",$quote) || count($quote["rates"]) == 0) {
			$cart["products"] = array();
			$cart = $this->getFedEx($cart,$quote);
			$this->logMessage(__FUNCTION__,sprintf("getFedEx returned [%s]",print_r($cart,true)),3);
			$quote["services"] = array();
			if (array_key_exists("custom",$cart) && array_key_exists("rates",$cart["custom"]) && count($cart["custom"]["rates"]) > 0) {
				foreach($cart["custom"]["rates"] as $k=>$r) {
					if ($s = $this->fetchSingleTest("select * from product where code='%s' and enabled = 1 and deleted = 0",$k)) {
						$quote["services"][$s["id"]] = sprintf("%s - %s",$s["value"],$this->my_money_format($r["net"]));
						$quote["rates"][$s["id"]] = $r["net"];
					}
					else {
						$this->logMessage(__FUNCTION__,sprintf("unknown fedex code [%s] from cart [%s] quote [%s]",$k,print_r($cart,true),print_r($quote,true)),1);
					}
				}
			}
		}
		$outer->getField("serviceType")->setOptions($quote["services"]);
		$i_flds = $this->config->getFields($module["configuration"]."Row");
		$outer->addTag("products",implode("",$this->getProducts($inner,$outer,$module,$i_flds,$quote["services"],false)),false);
		if (array_key_exists(__FUNCTION__,$_REQUEST)) {
			$quote["serviceType"] = $_REQUEST["serviceType"];
			$outer->addData("serviceType",$_REQUEST["serviceType"]);
			$this->logMessage(__FUNCTION__,sprintf("outer [%s] should be [%s]",print_r($outer,true),$_REQUEST["serviceType"]),1);
			if (strlen($module["parm1"]) > 0)
				$outer->init($this->m_dir.$module["parm1"]);
			$key = sprintf("%d|0|0|0",$_REQUEST["serviceType"]);
			$cart['products'] = array();
			$cart = $this->initPriceLine($cart,$key,$this->fetchSingleTest("select * from product where id = %d",$_REQUEST["serviceType"]));
			$cart['products'][$key]['line_id'] = count($cart['products']);
			$cart['products'][$key]['price'] = $quote["rates"][$_REQUEST["serviceType"]];
			$cart['products'][$key]['regularPrice'] = $quote["rates"][$_REQUEST["serviceType"]];
			$cart['products'][$key]['value'] = $quote["rates"][$_REQUEST["serviceType"]];
			$cart['products'][$key]['quantity'] = 1;
			$cart['products'][$key]['product_id'] = $_REQUEST["serviceType"];
			foreach($cart["products"] as $key=>$item) {
				$cart["products"][$key] = Ecom::lineValue($item);
			}
			$cart = Ecom::recalcOrder($cart);
			$_SESSION["cart"] = $cart;
			$subdata = $this->subForms($this->m_module['fetemplate_id'],null,array(),'outer');
			$this->logMessage(__FUNCTION__,sprintf('subforms [%s]',print_r($subdata,true)),3);
			foreach($subdata as $key=>$value) {
				$outer->addTag($key,$value,false);
			}
		}
		$_SESSION["quote"] = $quote;
		return $outer->show();
	}

    /**
     * @param $pickup
     * @param $product
     * @return false|string
     * @throws phpmailerException
     */
    function calcPickup($pickup, $product) {
		$p = $this->fetchSingleTest("select * from product where id = %d",$product["product_id"]);
		$dt = strtotime($pickup);
		$dow = date("w",$dt);
		$i = 0;
		$this->logMessage(__FUNCTION__,sprintf("pickup [%s] dow [%d] available [%d]", $pickup, $dow,$p["custom_availability"] & pow(2,$dow)),3);
		while(($p["custom_availability"] & pow(2,$dow)) == 0 && $i < 10) {
			$dt += 24*60*60;
			$dow = date("w",$dt);
			$i += 1;	// failsafe
			$this->logMessage(__FUNCTION__,sprintf("dow [%d] available [%d] from [%d]",$dow,pow(2,$dow) & $p["custom_availability"],$p["custom_availability"]),3);
		}
		$dt = date("Y-m-d H:i:s",$dt);
		$this->logMessage(__FUNCTION__,sprintf("pickup is [%s] delivery is [%s]",$pickup,$dt),2);
		return $dt;
	}

    /**
     * @return array|mixed|string|string[]
     * @throws phpmailerException
     */
    function myAddresses() {
		if (!$module = parent::getModule())
			return "";
		$a_id = array_key_exists("a_id",$_REQUEST) ? $_REQUEST["a_id"] : 0;
		$outer = new Forms();
		$outer->init($this->m_dir.$module['outer_html']);
		$flds = $outer->buildForm($this->config->getFields($module['configuration']));
		$outer->setModule($module);
		$inner = new Forms();
		$inner->init($this->m_dir.$module['inner_html']);
		$flds = $inner->buildForm($this->config->getFields($module['configuration']."Row"));

		if (array_key_exists(__FUNCTION__,$_REQUEST)) {
			$inner->addData($_REQUEST);
			if (($d_id = $inner->getData("d_id")) > 0)
				$this->execute(sprintf("update addresses set deleted = 1 where ownertype='member' and ownerid = %d and id = %d", $this->getUserInfo("id"), $d_id));
		}

		$addresses = $this->fetchAllTest("select * from addresses where ownertype='member' and ownerid = %d and deleted = 0 and address_book = 1 and addresstype != %d order by company, lastname, firstname",$this->getUserInfo("id"), ADDRESS_COMPANY);
		$recs = array();
		$ct = 0;
		foreach($addresses as $key=>$rec) {
			$ct += 1;
			$inner->reset();
			$inner->addTag("sequence",$ct);
			$inner->addTag("active",$rec["id"] == $a_id ? "active" : "");
			$inner->addData(Address::formatData($rec));
			$recs[] = $inner->show();
		}
		$outer->addTag("addresses",implode("",$recs),false);
		return $outer->show();
	}

    /**
     * @return array|mixed|string|string[]
     * @throws phpmailerException
     */
    function addressEditing() {
		if (!$module = parent::getModule())
			return "";
		$a_id = array_key_exists("a_id",$_REQUEST) ? $_REQUEST["a_id"] : 0;
		if (array_key_exists("returnTo",$_REQUEST) && $_REQUEST["returnTo"] > 0) {
			if (array_key_exists('HTTP_REFERER',$_SERVER) && strlen($_SERVER["HTTP_REFERER"]) > 0 && strpos($_SERVER["HTTP_REFERER"],$_SERVER["REQUEST_URI"]) === false) {
				$_SESSION["returnUrl"] = $_SERVER["HTTP_REFERER"];
			}
		}
		$outer = new Forms();
		$outer->init($this->m_dir.$module['outer_html']);
		$outer->setModule($module);
		$inner = new Forms();
		$inner->init($this->m_dir.$module['inner_html']);
		$flds = $inner->buildForm($this->config->getFields($module['configuration']));
		if (!($data = $this->fetchSingleTest("select * from addresses where id = %d and ownerid = %d",$a_id,$this->getUserInfo("id"))))
			$data = array("id"=>0);
		$inner->addData($data);
		if (count($_POST) > 0 && array_key_exists(__FUNCTION__,$_POST)) {
			$inner->addData($_POST);
			$valid = $inner->validate();
			if ($valid) {
				$geocode = Address::geocode($inner->getAllData(),$geocode["latitude"],$geocode["longitude"]);				
				if (!$geocode["status"]) {
					$valid = false;
					$inner->addFormError("GPS Encoding failed");
				}
			}
			if ($valid) {
				$values = array("ownertype"=>"member","ownerid"=>$this->getUserInfo("id"));
				foreach($flds as $key=>$fld) {
					if (!(array_key_exists('database',$fld) && $fld['database'] == false)) {
						$values[$fld['name']] = $inner->getData($fld['name']);
					}
				}
				$values["latitude"] = $geocode["latitude"];
				$values["longitude"] = $geocode["longitude"];
				if ((!array_key_exists("addresstype",$values)) || $values["addresstype"] == 0)
					$values["addresstype"] = ADDRESS_PICKUP;	//$this->fetchScalarTest("select id from code_lookups where type='memberAddressTypes' and extra = 1");		
				//$values["tax_address"] = 1;
				if ($data["id"] == 0) {
					$stmt = $this->prepare(sprintf('insert into addresses(%s) values(%s)', implode(', ',array_keys($values)), str_repeat('?,', count($values)-1).'?'));
				}
				else {
					$stmt = $this->prepare(sprintf('update addresses set %s=? where id = %d', implode('=?, ',array_keys($values)),$data['id']));
				}
				$stmt->bindParams(array_merge(array(str_repeat('s', count($values))),array_values($values)));
				$this->beginTransaction();
				if ($stmt->execute()) {
					if ($data["id"] == 0) $data["id"] = $this->insertId();
					$this->commitTransaction();
					$outer->init($this->m_dir.$module['parm1']);
					$outer->setData("id",$data["id"]);
				}
				else {
					$outer->addFormError("An Error Occurred. The Web Master has been notified");
					$this->rollbackTransaction();
				}
			}
			$outer->setData("successful",$valid);
		}
		$outer->addTag("form",$inner->show(),false);
		$subdata = $this->subForms($this->m_module['fetemplate_id'],null,array(),'outer');
		$this->logMessage(__FUNCTION__,sprintf('subforms [%s]',print_r($subdata,true)),3);
		foreach($subdata as $key=>$value) {
			$outer->addTag($key,$value,false);
		}
		return $outer->show();
	}

    /**
     * @return array|mixed|string|string[]
     * @throws phpmailerException
     */
    function driverSchedule() {
		if (!$module = parent::getModule())
			return "";
		if (defined("FRONTEND") && $this->getUserInfo("id") == 0)
			return "";
		$add = false;
		if (array_key_exists("lat", $_REQUEST) && abs($_REQUEST["lat"]) > .01 &&
				array_key_exists("lng", $_REQUEST) && abs($_REQUEST["lng"]) > .01) {
			if ($pos = $this->fetchSingleTest("select * from driver_location where member_id = %d order by datetime desc limit 1", $this->getUserInfo("id"))) {
				$d1 = new DateTime($pos["datetime"]);
				$d2 = new DateTime();
				$diff = $d1->diff($d2);
				$this->logMessage(__FUNCTION__,sprintf("d1 [%s] d2 [%s] diff [%s/%s]", print_r($d1,true), print_r($d2,true), print_r($diff,true), $diff->format("%i")),1);
				$delay = (int)$this->getConfigVar("gpstiming","config");
				$add = (int)$diff->format("%i") >= $delay;
				$deltaLat = $pos["latitude"] - $_REQUEST["lat"];
				$deltaLong = $pos["longitude"] - $_REQUEST["lng"];
				$a = pow(sin(($deltaLat)/2),2) + cos($pos["latitude"])*cos($_REQUEST["lat"]) * pow(sin($deltaLong/2),2);
				$c=2*atan2(sqrt($a),sqrt(1-$a));
				$d1km=6371*$c;
				$add |= abs($d1km) > .25;
			}
			else {
				$add = true;
			}
			if ($add) {
				$rt = explode(",",$_REQUEST["rt"]);
				$parms = array("latitude"=>$_REQUEST["lat"], "longitude"=>$_REQUEST["lng"],"member_id"=>$this->getUserInfo("id"),"datetime"=>date(DATE_ATOM));
				if (count($rt) > 0) $parms["delivery_id"] = $rt[0];
				$stmt = $this->prepare(sprintf("insert into driver_location(%s) values(?%s)", implode(", ", array_keys($parms)), str_repeat(", ?", count($parms)-1)));
				$stmt->bindParams(array_merge(array(str_repeat("s",count($parms))),array_values($parms)));
				$stmt->execute();
			}
		}
		$outer = new Forms();
		$outer->init($this->m_dir.$module['outer_html']);
		$outer->setModule($module);
		$flds = $outer->buildForm($this->config->getFields($module['configuration']));

		$inner = new Forms();
		$inner->init($this->m_dir.$module['inner_html']);
		$flds = $inner->buildForm($this->config->getFields($module['configuration']."Row"));

		$sql = sprintf("select d.*, p.name from custom_delivery d, order_lines l, product p, drivers dr, orders o 
where o.deleted = 0 and o.order_status_processing and o.order_status & %d = 0 and o.id = l.order_id and dr.member_id = %d and d.driver_id = dr.id and completed = 0 and (date_format(scheduled_date,'%%Y-%%m-%%d') <= '%s' or scheduled_date < '%s 08:00:00') and l.order_id = d.order_id and l.custom_package = 'S' and p.id = l.product_id and l.deleted = 0 and d.ack_status = %d %s 
order by driver_sequence ", STATUS_CANCELLED, $this->getUserInfo("id"),date("Y-m-d"), date("Y-m-d", strtotime("today + 1 day")), ACK_ACKNOWLEDGE, $module["where_clause"]);

		if (array_key_exists(__FUNCTION__,$_REQUEST)) $outer->addData($_REQUEST);
		$module["records"] = $outer->getData("pager");
		$pagination = $this->getPagination($sql,$module,$recordCount);
		$outer->setData("pagination",$pagination);

		$recs = $this->fetchAllTest($sql);
		$data = array();
		//$pkg = new Forms();
		//$pkg->init($this->m_dir.$module["parm1"]);
		//$flds = $inner->buildForm($this->config->getFields($module['configuration']."Row"));
		//$flds = $pkg->buildForm($flds);
		if (array_key_exists("rt",$_REQUEST)) {
			$tmp = array();
			foreach($recs as $key=>$rec) {
				$tmp[] = $rec["id"];
			}
			if (implode(",",$tmp) == $_REQUEST["rt"]) {
				return "false";
			}
		}
		foreach($recs as $key=>$rec) {
			if ($address = $this->fetchSingleTest("select * from addresses where ownertype='order' and ownerid = %d and addresstype = '%s'",
										$rec["order_id"],$rec["service_type"] == "P" ? ADDRESS_PICKUP : ADDRESS_DELIVERY))
				$rec["address"] = Address::formatData($address);
			//$pkgs = $this->fetchAllTest("select l.*, p.name from order_lines l, product p where l.order_id = %d and l.is_product = 1 and p.id = l.product_id",$rec["order_id"]);
			$t = floor((strtotime($rec["scheduled_date"]) - strtotime("now"))/60);
			if ($t <= 0)
				$rec["time_span"] = "danger";
			elseif($t <= 15)
				$rec["time_span"] = "warning";
			else
				$rec["time_span"] = "";
			$rec["has_notice"] = strlen($rec["instructions"]) > 0;
			$extras = $this->fetchScalarAllTest("select p.name from product p, order_lines ol where ol.order_id = %d and ol.deleted = 0 and p.id = ol.product_id and ol.custom_package = 'A' and p.custom_special_requirement order by ol.line_id", $rec["order_id"]);
			$rec["extras"] = implode(", ", $extras);
			$pkgs = $this->fetchScalarAllTest("select concat(sum(ol.quantity),' @ ',p.name) as pkgs from product p, order_lines ol where ol.order_id = %d and ol.deleted = 0 and p.id = ol.product_id and ol.custom_package = 'P' group by p.name", $rec["order_id"]);
			$rec["pkgs"] = implode("<br/>", $pkgs);
			//$ps = array();
			//foreach($pkgs as $skey=>$p) {
			//	$pkg->addData($p);
			//	$ps[] = $pkg->show();
			//}
			$inner->addData($rec);
			//$inner->addTag("packages",implode("",$ps),false);
			$data[] = $inner->show();
		}
		$outer->addTag("count",count($recs));
		$outer->addTag("deliveries",implode("",$data),false);
		return $outer->show();
	}

    /**
     * @return array|mixed|string|string[]
     * @throws phpmailerException
     */
    function pickupInfo() {
		if (!$module = parent::getModule())
			return "";
		if (defined("FRONTEND") && $this->getUserInfo("id") == 0)
				return "";
		$c_id = array_key_exists("c_id",$_REQUEST) ? $_REQUEST["c_id"] : 0;
		$outer = new Forms();
		$outer->init($this->m_dir.$module['outer_html']);
		$outer->setModule($module);
		$inner = new Forms();
		$inner->init($this->m_dir.$module['inner_html']);
		$flds = $inner->buildForm($this->config->getFields($module['configuration'].'Row'));
		$flds = $outer->buildForm($this->config->getFields($module['configuration']));
		
		if ($rec = $this->fetchSingleTest("select d.*, p.name, c1.code as weight_type, c2.code as dimension_type, l.quantity as quantity, o.order_status, CURRENT_TIMESTAMP() as actual_date from orders o, custom_delivery d, order_lines l, product p, code_lookups c1, code_lookups c2, drivers dr where dr.member_id = %d and d.driver_id = dr.id and d.id = %d and l.order_id = d.order_id and l.custom_package = 'S' and p.id = l.product_id and c1.id = custom_weight_code and c2.id = custom_dimension_code and o.id = d.order_id and l.deleted = 0 %s order by driver_sequence limit %d", $this->getUserInfo("id"), $c_id, $module["where_clause"], $module["records"])) {				
				
			$rec["address"] = Address::formatData($this->fetchSingleTest("select * from addresses where ownerid = %d and ownertype = 'order' and addresstype=%d", $rec["order_id"], ADDRESS_PICKUP ));
			$pkgs = $this->fetchAllTest("select od.*, p.name from order_lines ol, order_lines_dimensions od, product p where ol.deleted = 0 and ol.order_id = %d and ol.custom_package='P' and p.id = ol.product_id and od.order_id = ol.order_id and od.line_id = ol.line_id and ol.deleted = 0 order by ol.line_id, od.id", $rec["order_id"]);
			$p = array();
			if ($delivery = $this->fetchSingleTest("select * from custom_delivery where order_id = %d and service_type = 'D'",$rec["order_id"])) {
				$delivery["address"] = Address::formatData($this->fetchSingleTest("select * from addresses where ownertype='order' and ownerid = %d and addresstype = %d", $rec["order_id"], ADDRESS_DELIVERY));
				$rec["delivery"] = $delivery;
			}
			$outer->addData($rec);
			$seq = 0;
			foreach($pkgs as $key=>$tmp) {
				$tmp["sequence"] = ++$seq;
				$tmp["weight_type"] = $rec["weight_type"];
				$tmp["dimension_type"] = $rec["dimension_type"];
				$tmp["o_id"] = $c_id;
				$inner->addData($tmp);
				$p[] = $inner->show();
			}
			$outer->addTag("packages",implode("",$p),false);
			$pkgs = $this->fetchAllTest("select ol.*, p.name from order_lines ol, product p, product_by_folder pf where ol.order_id = %d and ol.custom_package='A' and p.id = ol.product_id and pf.folder_id = %d and pf.product_id = p.id and ol.deleted = 0 order by ol.line_id", $rec["order_id"],DELIVERY_SURCHARGES);
			$p = array();
			$inner->init($this->m_dir.$module["parm1"]);
			foreach($pkgs as $key=>$tmp) {
				$inner->addData($tmp);
				$p[] = $inner->show();
			}
			$outer->addTag("services",implode("",$p),false);
		}
		if (array_key_exists(__FUNCTION__,$_REQUEST)) {
			$outer->addData($_REQUEST);
			if ($outer->validate()) {
				$values = array();
				foreach($flds as $key=>$fld) {
					if (!(array_key_exists('database',$fld) && $fld['database'] == false)) {
						$values[$fld['name']] = $outer->getData($fld['name']);
					}
				}
				$values["completed"] = 1;
				$values["actual_date"] = array_key_exists("actual_date",$_REQUEST) ? $outer->getData("actual_date") : date("Y-m-d H:i:s");
				$values["comments"] = array_key_exists("comments",$_POST) ? $_POST["comments"] : "";
				$stmt = $this->prepare(sprintf("update custom_delivery set %s=? where id = %d", implode("=?,",array_keys($values)), $c_id));
				$stmt->bindParams(array_merge(array(str_repeat("s",count($values))),array_values($values)));
				$stmt->execute();
				//
				//	If comments are entered still needs modified by driver status
				//
				if (strlen($values["comments"]) > 0) {
					$this->updateNotes($rec["order_id"], "Pickup Comments Updated");
				}
				$this->execute(sprintf("update orders set order_status = order_status & ~%d where id = %d", STATUS_NEEDS_APPROVAL, $rec["order_id"]));
				$this->calculateCommissions($rec["order_id"]);
				$outer->init($this->m_dir.$module['parm2']);
				if ($this->hasOption("email")) {
					if ($conf = $this->fetchSingleTest("select o.custom_pickup_email, m.* from orders o, members m where o.id = %d and m.id = o.member_id", $rec["order_id"])) {
						if ($conf["custom_pickup_notification"] || strlen($conf["custom_pickup_email"]) > 0 || strlen($conf["custom_pickup_emails"]) > 0) {
							$orderHtml = "";
							$emails = $this->configEmails("ecommerce");
							$orderId = $rec["order_id"];
							if (count($emails) == 0)
								$emails = $this->configEmails("contact");
							$body = new Forms();
							$body->setOption('formDelimiter','{{|}}');
							$mailer = new MyMailer();
							$mailer->Subject = sprintf("Pickup Completed - %s", SITENAME);
							$sql = sprintf('select * from htmlForms where class = %d and type = "pickupComplete"',$this->getClassId('custom'));
							$html = $this->fetchSingleTest($sql);
							$body->setHTML($html['html']);
							if (!$order = $this->fetchSingleTest('select o.*, cd.delivery_name, m.firstname, m.lastname, m.email from orders o, members m, custom_delivery cd where o.id = %d and m.id = o.member_id and cd.order_id = o.id and cd.service_type = "D"',$orderId))
								$this->logMessage(__FUNCTION__,sprintf('cannot locate order #[%d]',$orderId),1,true);
							if ($module = $this->fetchSingleTest('select t.*, m.classname, t.id as fetemplate_id from fetemplates t, modules m where t.id = %d and m.id = t.module_id',$this->getOption('email'))) {
								$p = new product($module['id'],$module);
								$_REQUEST["m_id"] = $conf["id"];
								$orderHtml = $p->orderDetails($orderId);
								$body->addTag('order',$orderHtml,false);
								$mailer->Body = $body->show();
								$mailer->FromName = "KJV Courier Services";
								$mailer->From = "noreply@".HOSTNAME;
								$mailer->IsHTML(true);
								if ($conf["custom_pickup_notification"] != 0) {
									$tmp = strlen($conf["custom_pickup_emails"]) > 0 ? explode(";",$conf["custom_pickup_emails"]) : array();
									if (count($tmp) == 0) $tmp[] = $conf["email"];
									foreach($tmp as $sk=>$sv) {
										$mailer->addAddress($sv);
										if (!$mailer->Send()) {
											$this->logMessage(__FUNCTION__,sprintf("Email send failed [%s]",print_r($mailer,true)),1,true);
										}
										$mailer->clearAddresses();
									}
								}
								if (strlen($conf["custom_pickup_email"]) > 0) {
									$tmp = explode(";",$conf["custom_pickup_email"]);
									foreach($tmp as $sk=>$sv) {
										$mailer->addAddress($sv);
										if (!$mailer->Send()) {
											$this->logMessage(__FUNCTION__,sprintf("Email send failed [%s]",print_r($mailer,true)),1,true);
										}
										$mailer->clearAddresses();
									}
								}
							}
							else
								$this->logMessage(__FUNCTION__,sprintf('pickup confirmation failed [%s]',print_r($this,true)),1,true);
						}
					}

				}
				if ((double)$outer->getData("lat") != 0.0) {
					$parms = array("latitude"=>$_REQUEST["lat"], "longitude"=>$_REQUEST["long"],"member_id"=>$this->getUserInfo("id"),"datetime"=>date(DATE_ATOM));
					$parms["delivery_id"] = $c_id;
					$stmt = $this->prepare(sprintf("insert into driver_location(%s) values(?%s)", implode(", ", array_keys($parms)), str_repeat(", ?", count($parms)-1)));
					$stmt->bindParams(array_merge(array(str_repeat("s",count($parms))),array_values($parms)));
					$stmt->execute();
				}


			}
			$this->logMessage(__FUNCTION__,sprintf("outer [%s]", print_r($outer,true)),1);
		}
		return $outer->show();
	}

    /**
     * @param $data
     * @param $obj
     * @param $dataKey
     * @param $req
     * @param $errors
     * @param $messages
     * @return bool
     * @throws phpmailerException
     */
    function validatePickup($data, $obj, $dataKey, $req, &$errors, &$messages) {
		$status = true;
		if ($dataKey == "delivery_name") {
			$o = $this->fetchSingleTest("select * from orders where id = %d", $data["order_id"]);
			if (($o["order_status"] & STATUS_NEEDS_APPROVAL) != 0) {
				if (strlen($data["delivery_name"]) == 0) {
					$errors["pickup".$dataKey] = "Authorized By is required";
					$messages[] = array("pickup".$dataKey=>"Authorized By");
					$status = false;
				}
			}
		}
		return $status;
	}

    /**
     * @return array|mixed|string|string[]
     * @throws phpmailerException
     */
    function editPackageLine() {
		if (!$module = parent::getModule())
			return "";
		if (defined("FRONTEND") && $this->getUserInfo("id") == 0)
				return "";
		$l_id = array_key_exists("l_id",$_REQUEST) ? $_REQUEST["l_id"] : 0;
		$o_id = array_key_exists("o_id",$_REQUEST) ? $_REQUEST["o_id"] : 0;
		$c_id = array_key_exists("c_id",$_REQUEST) ? $_REQUEST["c_id"] : 0;
		if ($l_id == 0)
			$rec = $this->fetchSingleTest("select o.member_id, o.id as order_id, l1.value as weight_code, l2.value as dimension_code, d.service_type 
			from custom_delivery d, orders o, drivers dr, code_lookups l1, code_lookups l2  
			where dr.member_id = %d and d.driver_id = dr.id and o.id = %d and d.order_id = o.id
			and l1.id = o.custom_weight_code and l2.id = o.custom_dimension_code", $this->getUserInfo("id"), $o_id);
		else
			$rec = $this->fetchSingleTest("
select od.*, ol.product_id, o.member_id, p.name, od.id, od.line_id, l1.value as weight_code, l2.value as dimension_code, d.service_type
from custom_delivery d, order_lines_dimensions od, orders o, order_lines ol, product p, drivers dr, code_lookups l1, code_lookups l2 
where d.id = %d and dr.member_id = %d and d.driver_id = dr.id and d.order_id = %d and o.id = d.order_id and
ol.order_id = o.id and od.id = %d and od.order_id = ol.order_id and od.line_id = ol.line_id and p.id = ol.product_id
and l1.id = o.custom_weight_code and l2.id = o.custom_dimension_code and ol.deleted = 0", $c_id, $this->getUserInfo("id"), $o_id, $l_id);
		$outer = new Forms();
		$outer->init($this->m_dir.$module['outer_html']);
		$outer->setModule($module);
		$flds = $this->getFields($module["configuration"]);
		$flds["product_id"]["sql"] = sprintf("select p.id, p.name, p.id as xxx from product p, product_by_folder pf, members_folders m, members_by_folder mf where mf.member_id = %d and m.id = mf.folder_id and pf.folder_id = m.custom_package_types and p.id = pf.product_id and p.enabled = 1 and p.deleted = 0 order by p.name",$rec["member_id"]);
		$flds = $outer->buildForm($flds);
		$rec["o_id"] = $o_id;
		$rec["l_id"] = $l_id;
		$outer->addData($rec);
		if (count($_REQUEST) > 0 && array_key_exists(__FUNCTION__,$_REQUEST)) {
			if (array_key_exists("delete",$_REQUEST) && $_REQUEST["delete"] == 1) {
				$this->updateNotes($rec["order_id"], sprintf("Deleting package type %s, %d @ %.2f x %.2f x %.2f %.2f%s", 
					$this->fetchScalarTest("select p.name from product p, order_lines ol, order_lines_dimensions old where old.id = %d and ol.order_id = old.order_id and ol.line_id = old.line_id and p.id = ol.product_id", $rec["l_id"]),
					$outer->getData("quantity"), $outer->getData("height"), $outer->getData("width"), $outer->getData("depth"),
					$outer->getData("weight"), $rec["weight_code"]));
				$this->beginTransaction();
				$this->execute(sprintf("update orders set order_status = order_status | %d where id = %d", STATUS_NEEDS_APPROVAL | STATUS_DRIVER_CHANGED, $rec["order_id"]));
				$this->execute(sprintf("delete from order_lines_dimensions where id = %d and order_id = %d",$_REQUEST["l_id"],$_REQUEST["o_id"]));
				if (($ct = $this->fetchScalarTest("select count(0) from order_lines_dimensions where id = %d and order_id = %d",$_REQUEST["l_id"],$_REQUEST["o_id"])) == 0) {
					$this->execute(sprintf("delete from order_lines where order_id = %d and line_id = %d",$_REQUEST["l_id"],$_REQUEST["o_id"]));
				}
				$this->commitTransaction();
				return $outer->show();
			}
			else {
				$outer->addData($_REQUEST);
				$valid = $outer->validate();
				if ($valid) {
					$upd = array();
					$dim = array();
					foreach($flds as $key=>$value) {
						if ((!array_key_exists("database",$value)) || $value["database"] != false) {
							if (array_key_exists("db",$value) && $value["db"] == "dim")
								$dim[$value["name"]] = $outer->getData($value["name"]);
							else
								$upd[$value["name"]] = $outer->getData($value["name"]);
						}
					}
					$this->logMessage(__FUNCTION__,sprintf("upd is [%s] dim is [%s]",print_r($upd,true),print_r($dim,true)),1);
					if ($l_id == 0) {
						$this->updateNotes($rec["order_id"], sprintf("Adding package type %s, %d @ %.2f x %.2f x %.2f %.2f%s", 
							$this->fetchScalarTest("select p.name from product p, order_lines ol, order_lines_dimensions old where old.id = %d and ol.order_id = old.order_id and ol.line_id = old.line_id and p.id = ol.product_id", $rec["l_id"]),
							$outer->getData("quantity"), $outer->getData("height"), $outer->getData("width"), $outer->getData("depth"),
							$outer->getData("weight"), $rec["weight_code"]));
						$this->beginTransaction();
						$this->execute(sprintf("update orders set order_status = order_status | %d where id = %d", STATUS_NEEDS_APPROVAL | STATUS_DRIVER_CHANGED, $rec["order_id"]));
						if ($ol = $this->fetchSingleTest("select order_id, line_id from order_lines where order_id = %d and product_id = %d and deleted = 0",$o_id,$_REQUEST["product_id"])) {
							$l_id = $ol["line_id"];
						}
						else {
							$l_id = $this->fetchScalarTest("select max(line_id) from order_lines o where o.order_id = %d",$o_id) + 1;
							$ol = array(
								"order_id"=>$outer->getData("o_id"),
								"product_id"=>$outer->getData("product_id"),
								"quantity"=>$outer->getData("quantity"),
								"qty_multiplier"=>1,
								"is_product"=>1,
								"custom_package"=>"P",
								"line_id"=>$l_id);
							$this->logMessage(__FUNCTION__,sprintf("ol is [%s]",print_r($ol,true)),1);
							$stmt = $this->prepare(sprintf("insert into order_lines(%s) values(%s?)", implode(", ",array_keys($ol)), str_repeat("?, ",count($ol)-1)));
							$stmt->bindParams(array_merge(array(str_repeat("s",count($ol))),array_values($ol)));
							$valid = $stmt->execute();
						}
						if ($valid) {
							$od = array(
								"height"=>$outer->getData("height"),
								"width"=>$outer->getData("width"),
								"depth"=>$outer->getData("depth"),
								"weight"=>$outer->getData("weight"),
								"order_id"=>$ol["order_id"],
								"quantity"=>$outer->getData("quantity"),
								"line_id"=>$l_id
							);
							$stmt = $this->prepare(sprintf("insert into order_lines_dimensions(%s) values(%s?)", implode(", ",array_keys($od)), str_repeat("?, ", count($od)-1)));
							$stmt->bindParams(array_merge(array(str_repeat("s",count($od))),array_values($od)));
							$valid = $stmt->execute();
						}
						if ($valid) {
							$valid &= $this->execute(sprintf("update order_lines set quantity = (select sum(quantity) from order_lines_dimensions where order_id = %d and line_id = %d) where order_id = %d and line_id = %d",$ol["order_id"],$ol["line_id"],$ol["order_id"],$ol["line_id"]));
							$flds = $this->getFields($module["configuration"]."Success");
							//$outer = new Forms();
							$_REQUEST["name"] = $this->fetchScalarTest("select name from product where id = %d",$_REQUEST["product_id"]);
							//$outer->addData($_REQUEST);
							$flds = $outer->buildForm($flds);
							$outer->init($this->m_dir.$module['inner_html']);
							$this->logMessage(__FUNCTION__,sprintf("outer [%s]", print_r($outer,true)), 1);
							$this->commitTransaction();
						}
						else {
							$this->logMessage(__FUNCTION__,sprintf("in failed update"),1);
							$this->rollbackTransaction();
							$outer->addFormError("An Error Occurred");
						}
					}
					else {

						$this->updateNotes($rec["order_id"], sprintf("Updating package type %s, %d @ %.2f x %.2f x %.2f %.2f %s", 
							$this->fetchScalarTest("select p.name from product p, order_lines ol, order_lines_dimensions old where old.id = %d and ol.order_id = old.order_id and ol.line_id = old.line_id and p.id = ol.product_id", $rec["l_id"]),
							$outer->getData("quantity"), $outer->getData("height"), $outer->getData("width"), $outer->getData("depth"),
							$outer->getData("weight"), $rec["weight_code"]));

						$this->execute(sprintf("update orders set order_status = order_status | %d where id = %d", STATUS_NEEDS_APPROVAL | STATUS_DRIVER_CHANGED, $rec["order_id"]));
						$stmt = $this->prepare(sprintf("update order_lines_dimensions set %s=? where order_id = %d and id = %d",implode("=?, ", array_keys($dim)),$_REQUEST["o_id"],$_REQUEST["l_id"]));
						$stmt->bindParams(array_merge(array(str_repeat("s",count($dim))),array_values($dim)));
						$valid = $stmt->execute();
						$valid &= $this->execute(sprintf("update order_lines set quantity = (select sum(quantity) from order_lines_dimensions where order_id = %d and line_id = %d) where order_id = %d and line_id = %d",$rec["order_id"],$rec["line_id"],$rec["order_id"],$rec["line_id"]));
	
						if ($valid) {
							$outer->init($this->m_dir.$module['inner_html']);
						}
						else {
							$outer->addFormError("An Error Occurred");
						}
					}
				}
			}
			//
			//	test the current weight total for extra charges
			//
			$wt = $this->fetchSingleTest("select sum(weight) as weight, l.* from order_lines_dimensions old, code_lookups l, orders o where o.id = %d and old.order_id = o.id and l.id = o.custom_weight_code", $rec["order_id"]);
			$wt["weight"] = $wt["weight"] * $wt["extra"];
			$grp = $this->fetchSingleTest("select mf.*, mbf.id as junction from members_folders mf, members_by_folder mbf where mbf.member_id = %d and mf.id = mbf.folder_id limit 1", $rec["member_id"]);
			$mbr = $this->fetchSingleTest("select * from members where id = %d", $rec["member_id"]);
			$free_weight = max($grp["custom_free_weight"],$mbr["custom_free_weight"]);
			$charge_weight = round($wt["weight"] - $free_weight,0);
			$curr_charge = $this->fetchSingleTest("select * from order_lines where order_id = %d and product_id = %d and deleted = 0", $rec["order_id"], $grp["custom_weight"]);
			$this->logMessage(__FUNCTION__, sprintf("current weight [%s] free weight [%s] charge weight [%f] curr_charge [%s]", 
					print_r($wt,true), $free_weight, $charge_weight, print_r($curr_charge,true)), 1);

			$line = $this->fetchSingleTest("select * from product where id = %d", $grp["custom_weight"]);
			$price = $this->fetchSingleTest("select * from product_pricing where product_id = %d order by max_quantity desc limit 1", $line["id"]);
			$line["quantity"] = $charge_weight;
			$line["shipping"]=0;
			$line["qty_multiplier"]=1;
			$line["shipping_only"]=0;
			$line["discount_type"] = "";
			$line["recurring_shipping_only"] = 0;
			$line["recurring_discount_type"] = "";
			$line["product_id"] = $line["id"];
			$line["price"] = round($price["price"] * (1+($grp["custom_weight_override"]+$mbr["custom_weight_override"])/100),2);
			$line["total"] = round($line["quantity"] * $line["price"],2);
			$line["order_id"] = $rec["order_id"];
			$line = Ecom::lineValue($line);
			$this->logMessage(__FUNCTION__,sprintf("line is [%s]", print_r($line,true)), 1);
			$tax_amt = 0;
			foreach($line["taxdata"] as $k=>$v) {
				$tax_amt += $v["tax_amount"];
			}
			$line["taxes"] = $tax_amt;
			$line["tax_exemptions"] = "||";
			$this->beginTransaction();
			if ($charge_weight > 0.1) {
				$this->logMessage(__FUNCTION__,sprintf("add weight charge"),1);
				$upd = array("product_id"=>$line["product_id"], "qty_multiplier"=>1, "quantity"=>$line["quantity"], "price"=>$line["price"], "is_product"=>0, "total"=>$line["total"], "custom_package"=>"A", "value"=>$line["total"]);
				if (is_array($curr_charge)) {
					$line["line_id"] = $curr_charge["line_id"];
					$stmt = $this->prepare(sprintf("update order_lines set %s=? where order_id = %d and line_id = %d", implode("=?, ",array_keys($upd)), $rec["order_id"], $curr_charge["line_id"]));
				}
				else {
					$line["line_id"] = $this->fetchScalarTest("select max(line_id) from order_lines where order_id = %d", $rec["order_id"]) + 1;
					$upd["order_id"] = $rec["order_id"];
					$upd["line_id"] = $line["line_id"];
					$stmt = $this->prepare(sprintf("insert into order_lines(%s) values(%s?)", implode(", ", array_keys($upd)), str_repeat("?, ", count($upd)-1)));
				}
				$stmt->bindParams(array_merge(array(str_repeat("s",count($upd))), array_values($upd)));
				$stmt->execute();
				$this->execute(sprintf("delete from order_taxes where order_id = %d and line_id = %d", $rec["order_id"], $line["line_id"]));
				foreach($line["taxdata"] as $k=>$v) {
					$this->execute(sprintf("insert into order_taxes( order_id, line_id, tax_amount, taxable_amount, tax_id) values( %d, %d, %f, %f, %d )",
						$rec["order_id"], $line["line_id"], $v["tax_amount"], $v["taxable_amount"], $k));
				}
			}
			if ($charge_weight <= .1) {
				$this->logMessage(__FUNCTION__,sprintf("remove weight charge"),1);
				if (is_array($curr_charge))
					$this->execute(sprintf("update order_lines set deleted=1 where order_id = %d and line_id = %d and product_id = %d", $rec["order_id"], $curr_charge["line_id"], $curr_charge["product_id"]));
			}
			$msgs = $this->calcFreeItems($o_id);
			if (count($msgs) > 0) {
				$outer->addFormSuccess(implode("",$msgs));
			}
			Ecom::recalcOrderFromDB($rec["order_id"]);
			$this->calculateCommissions($rec["order_id"]);
			$this->commitTransaction();
		}
		return $outer->show();
	}

    /**
     * @return array|mixed|string|string[]
     * @throws phpmailerException
     */
    function editServiceLine() {
		if (!$module = parent::getModule())
			return "";
		if (defined("FRONTEND") && $this->getUserInfo("id") == 0)
				return "";
		$l_id = array_key_exists("l_id",$_REQUEST) ? $_REQUEST["l_id"] : 0;
		$o_id = array_key_exists("o_id",$_REQUEST) ? $_REQUEST["o_id"] : 0;
		$c_id = array_key_exists("o_id",$_REQUEST) ? $_REQUEST["c_id"] : 0;
		$flds = $this->getFields($module["configuration"]);
		if ($l_id == 0) {
			$rec = $this->fetchSingleTest("select o.member_id, o.id as order_id, d.service_type from custom_delivery d, orders o, drivers dr where dr.member_id = %d and d.driver_id = dr.id and d.id = %d and o.id = d.order_id", $this->getUserInfo("id", $c_id));
			$flds["product_id"]["sql"] = sprintf("select p.id, p.name from product p, product_by_folder pf where pf.folder_id = %d and p.id = pf.product_id and p.enabled = 1 and p.deleted = 0 and custom_split_allocation in ('B','%s') and p.id not in (select product_id from order_lines where order_id = %d) order by p.name",DELIVERY_SURCHARGES,$rec["service_type"], $o_id);
		}
		else {
			$rec = $this->fetchSingleTest("select ol.*, d.service_type from custom_delivery d, order_lines ol, drivers dr where dr.member_id = %d and d.driver_id = dr.id and ol.order_id = d.order_id and ol.line_id = %d and d.id = %d and ol.deleted = 0", $this->getUserInfo("id", $l_id, $c_id));
			$flds["product_id"]["sql"] = sprintf("select p.id, p.name from product p, order_lines ol where ol.order_id = %d and ol.line_id = %d and p.id = ol.product_id",$o_id, $l_id);
		}
		$outer = new Forms();
		$outer->init($this->m_dir.$module['outer_html']);
		$outer->setModule($module);
		$flds = $outer->buildForm($flds);
		$rec["o_id"] = $o_id;
		$rec["l_id"] = $l_id;
		$outer->addData($rec);
		if (count($_REQUEST) > 0 && array_key_exists(__FUNCTION__,$_REQUEST)) {
			if (array_key_exists("delete",$_REQUEST) && $_REQUEST["delete"] == 1) {
				$this->updateNotes($rec["order_id"], sprintf("Deleting product %s", $this->fetchScalarTest("select p.name from product p, order_lines ol where ol.order_id = %d and ol.line_id = %d and p.id = ol.product_id", $rec["order_id"], $_REQUEST["l_id"])));
				$this->beginTransaction();
				$this->execute(sprintf("update orders set order_status = order_status | %d where id = %d", STATUS_NEEDS_APPROVAL | STATUS_DRIVER_CHANGED, $rec["order_id"]));
				$this->execute(sprintf("delete from order_lines where line_id = %d and order_id = %d",$_REQUEST["l_id"],$rec["order_id"]));
				$this->commitTransaction();
				return $outer->show();
			}
			$outer->addData($_REQUEST);
			$valid = $outer->validate();
			if ($valid) {
				$upd = array();
				$dim = array();
				foreach($flds as $key=>$value) {
					if ((!array_key_exists("database",$value)) || $value["database"] != false)
						$upd[$value["name"]] = $outer->getData($value["name"]);
				}
				$product = $this->fetchSingleTest("select * from product where id = %d", $outer->getData("product_id"));

				if ($valid && ( $product["id"] == PU_WAIT_TIME || $product["id"] == DEL_WAIT_TIME)) {
					$qty = $outer->getData("quantity");
					if ($qty < $product["custom_km_mincharge"]) {
						$product["custom_minimum_charge"] = 0;
					}
				}

				$upd["value"] = $product["custom_minimum_charge"]*$outer->getData("quantity");
				$upd["price"] = $product["custom_minimum_charge"];
				$upd["total"] = $upd["value"];
				$this->logMessage(__FUNCTION__,sprintf("upd is [%s] dim is [%s]",print_r($upd,true),print_r($dim,true)),1);
				$this->beginTransaction();
				$this->execute(sprintf("update orders set order_status = order_status | %d where id = %d", STATUS_NEEDS_APPROVAL | STATUS_DRIVER_CHANGED, $rec["order_id"]));
				if ($l_id == 0) {

					$this->updateNotes($rec["order_id"], sprintf("Adding product %s, Quantity: %d", $product["name"], $outer->getData("quantity")));

					$l_id = $this->fetchScalarTest("select max(line_id) from order_lines ol where ol.order_id = %d",$o_id) + 1;
					$upd["order_id"] = $outer->getData("o_id");
					$upd["product_id"] = $outer->getData("product_id");
					$upd["qty_multiplier"] = 1;
					$upd["is_product"] = 1;
					$upd["custom_package"] = "A";
					$upd["line_id"] = $l_id;
					$this->logMessage(__FUNCTION__,sprintf("ol is [%s]",print_r($upd,true)),1);
					$stmt = $this->prepare(sprintf("insert into order_lines(%s) values(%s?)", implode(", ",array_keys($upd)), str_repeat("?, ",count($upd)-1)));
					$stmt->bindParams(array_merge(array(str_repeat("s",count($upd))),array_values($upd)));
					$valid = $stmt->execute();
				}
				else {

					$this->updateNotes($rec["order_id"], sprintf("Updating product %s, Quantity: %d", $this->fetchScalarTest("select p.name from product p, order_lines ol where ol.order_id = %d and ol.line_id = %d and p.id = ol.product_id", $rec["order_id"], $_REQUEST["l_id"]), $outer->getData("quantity")));

					$stmt = $this->prepare(sprintf("update order_lines set %s=? where order_id = %d and line_id = %d",implode("=?, ", array_keys($upd)),$rec["order_id"],$_REQUEST["l_id"]));
					$stmt->bindParams(array_merge(array(str_repeat("s",count($upd))),array_values($upd)));
					$valid = $stmt->execute();
				}
				if ($valid) {
					$line = $this->fetchSingleTest("select * from order_lines ol where order_id = %d and line_id = %d",$outer->getData("o_id", $l_id));
					$line = Ecom::lineValue($line);
					$this->execute(sprintf("update order_lines set price=%f, value=%f, taxes = %f, total = %f where order_id = %d and product_id = %d", 
						$line["price"], $line["value"], $line["taxes"], $line["total"], $outer->getData("o_id"), $l_id));
					$this->execute(sprintf("delete from order_taxes where order_id = %d and line_id = %d", $outer->getData("o_id"), $l_id));
					foreach($line["taxdata"] as $k=>$v) {
						$this->logMessage(__FUNCTION__,sprintf("k [%s] v [%s]", print_r($k,true), print_r($v,true)),1);
						$this->execute(sprintf("insert into order_taxes(order_id, line_id, tax_id, tax_amount, taxable_amount) values(%d, %d, %d, %f, %f)",
							$outer->getData("o_id"), $line["line_id"], $k, $v["tax_amount"], $v["taxable_amount"]));
					}
					Ecom::recalcOrderFromDB($outer->getData("o_id"));
					$this->calc_driver_allocations($outer->getData("o_id"));
					$this->commitTransaction();
					$flds = $this->getFields($module["configuration"]."Success");
					$flds = $outer->buildForm($flds);
					$outer->init($this->m_dir.$module['inner_html']);
				}
				else {
					$outer->addFormError("An Error Occurred");
					$this->rollbackTransaction();
				}
			}
		}
		return $outer->show();
	}

    /**
     * @return array|mixed|string|string[]
     * @throws phpmailerException
     */
    function pickupComments() {
		if (!$module = parent::getModule())
			return "";
		if (defined("FRONTEND") && $this->getUserInfo("id") == 0)
				return "";
		$o_id = array_key_exists("o_id",$_REQUEST) ? $_REQUEST["o_id"] : 0;
		$rec = $this->fetchSingleTest("select d.* from custom_delivery d where d.driver_id = %d and d.id = %d", $this->getUserInfo("id", $o_id));
		$outer = new Forms();
		$outer->init($this->m_dir.$module['outer_html']);
		$outer->setModule($module);
		$flds = $outer->buildForm($this->getFields($module["configuration"]));
		$rec["o_id"] = $o_id;
		$outer->addData($rec);
		if (count($_REQUEST) > 0 && array_key_exists(__FUNCTION__,$_REQUEST)) {
			$outer->addData($_REQUEST);
			if ($outer->getData("cancel") == 1) {
				$outer = new Forms();
				$outer->addData($rec);
				$flds = $outer->buildForm($this->getFields($module["configuration"]."Success"));
				$outer->init($this->m_dir.$module['inner_html']);
				return $outer->show();
			}
			if ($outer->validate()) {
				$upd = array();
				foreach($flds as $key=>$value) {
					if ((!array_key_exists("database",$value)) || $value["database"] != false)
						$upd[$value["name"]] = $outer->getData($value["name"]);
				}
				$stmt = $this->prepare(sprintf("update custom_delivery set %s=? where id = %d",implode("=?, ", array_keys($upd)),$o_id));
				$stmt->bindParams(array_merge(array(str_repeat("s",count($upd))),array_values($upd)));
				if ($valid = $stmt->execute()) {
					$this->commitTransaction();
					$outer = new Forms();
					$outer->addData($_REQUEST);
					$flds = $outer->buildForm($this->getFields($module["configuration"]."Success"));
					$outer->init($this->m_dir.$module['inner_html']);
				}
				else {
					$this->rollbackTransaction();
				}
			}
		}
		return $outer->show();
	}

    /**
     * @return array|mixed|string|string[]
     * @throws phpmailerException
     */
    function completePickup() {
		if (!$module = parent::getModule())
			return "";
		if (defined("FRONTEND") && $this->getUserInfo("id") == 0)
				return "";
		$o_id = array_key_exists("o_id",$_REQUEST) ? $_REQUEST["o_id"] : 0;
		$rec = $this->fetchSingleTest("select d.* from custom_delivery d where d.driver_id = %d and d.id = %d", $this->getUserInfo("id", $o_id));
		$outer = new Forms();
		$outer->init($this->m_dir.$module['outer_html']);
		$outer->setModule($module);
		$flds = $outer->buildForm($this->getFields($module["configuration"]));
		$rec["o_id"] = $o_id;
		$outer->addData($rec);
		if (count($_REQUEST) > 0 && array_key_exists(__FUNCTION__,$_REQUEST)) {
			$outer->addData($_REQUEST);
			if ($outer->validate()) {
				$this->execute(sprintf("update custom_delivery set actual_date = now(), completed=1 where id = %d",$outer->getData("o_id")));
				$outer->init($this->m_dir.$module['inner_html']);
			}
		}
		return $outer->show();
	}

    /**
     * @return array|mixed|string|string[]
     * @throws phpmailerException
     */
    function deliveryInfo() {
		if (!$module = parent::getModule())
			return "";
		if (defined("FRONTEND") && $this->getUserInfo("id") == 0)
				return "";
		$c_id = array_key_exists("c_id",$_REQUEST) ? $_REQUEST["c_id"] : 0;
		$outer = new Forms();
		$outer->init($this->m_dir.$module['outer_html']);
		$outer->setModule($module);
		$inner = new Forms();
		$inner->init($this->m_dir.$module['inner_html']);
		$flds = $inner->buildForm($this->config->getFields($module['configuration']."Row"));
		$flds = $outer->buildForm($this->config->getFields($module['configuration']));
		if ($rec = $this->fetchSingleTest("select d.*, p.name, c1.code as weight_type, c2.code as dimension_type, l.quantity as quantity from orders o, custom_delivery d, order_lines l, product p, code_lookups c1, code_lookups c2, drivers dr where dr.member_id = %d and d.driver_id = dr.id and d.id = %d and l.order_id = d.order_id and l.custom_package = 'S' and p.id = l.product_id and c1.id = custom_weight_code and c2.id = custom_dimension_code and o.id = d.order_id and l.deleted = 0 %s order by driver_sequence limit %d", $this->getUserInfo("id", $c_id, $module["where_clause"], $module["records"]))) {
			$rec["address"] = Address::formatData($this->fetchSingleTest("select * from addresses where ownerid = %d and ownertype = 'order' and addresstype=%d", $rec["order_id"], ADDRESS_PICKUP ));
			$rec["pu_action"] = $this->fetchSingleTest("select * from custom_delivery where order_id = %d and service_type='P'", $rec["order_id"]);
			$outer->addData($rec);

			if (array_key_exists(__FUNCTION__,$_POST) && $_POST[__FUNCTION__] > 0) {
				$outer->addData($_POST);
				$valid = $outer->validate();
				if ($valid) {
					$upd = array();
					foreach($flds as $k=>$v) {
						if (!(array_key_exists("database",$v) && $v["database"] == false)) {
							$upd[$k] = $outer->getData($k);
						}
					}
					$stmt = $this->prepare(sprintf("update custom_delivery set %s=? where id = %d", implode("=?, ", array_keys($upd)), $c_id));
					$stmt->bindParams(array_merge( array(str_repeat("s", count($upd))), array_values($upd) ));
					$old_notes = $this->fetchScalarTest("select comments from custom_delivery where id = %d", $c_id);
					if ($stmt->execute()) {
						if (array_key_exists("comments", $upd) && strlen($upd["comments"]) > 0) {
							if ($old_notes != $upd["comments"])
								$this->updateNotes( $rec["order_id"], "Delivery Comments Updated");
							//$this->execute(sprintf("update orders set order_status = order_status | %d where id = %d", STATUS_DRIVER_CHANGED, $rec["order_id"]));
						}
						$outer->init($this->m_dir.$module["parm2"]);
					}
				}
			}
			$pkgs = $this->fetchAllTest("select od.*, p.name from order_lines ol, order_lines_dimensions od, product p where ol.order_id = %d and ol.custom_package='P' and p.id = ol.product_id and od.order_id = ol.order_id and od.line_id = ol.line_id and ol.deleted = 0 order by ol.line_id, od.id", $rec["order_id"]);
			$p = array();
			foreach($pkgs as $key=>$tmp) {
				$tmp["o_id"] = $c_id;
				$inner->addData($tmp);
				$p[] = $inner->show();
			}
			$outer->addTag("packages",implode("",$p),false);
			$pkgs = $this->fetchAllTest("select ol.*, p.name from order_lines ol, product p, product_by_folder pf where ol.order_id = %d and ol.custom_package='A' and p.id = ol.product_id and pf.folder_id = %d and pf.product_id = p.id and custom_split_allocation in ('B','D') and ol.deleted = 0 order by ol.line_id", $rec["order_id"],DELIVERY_SURCHARGES);
			$p = array();
			$inner->init($this->m_dir.$module["parm1"]);
			foreach($pkgs as $key=>$tmp) {
				$inner->addData($tmp);
				$p[] = $inner->show();
			}
			$outer->addTag("services",implode("",$p),false);
			//$inner->init($this->m_dir.$module["parm2"]);
			//$inner->addData($rec);
			//$outer->addTag("comments",$inner->show(),false);
		}
		return $outer->show();
	}

    /**
     * @return array|mixed|string|string[]
     * @throws phpmailerException
     */
    function captureSignature() {
		if (!$module = parent::getModule())
			return "";
		$c_id = array_key_exists("c_id",$_REQUEST) ? $_REQUEST["c_id"] : 0;
		$outer = new Forms();
		if (!$rec = $this->fetchSingleTest("select d.*, m.custom_delivery_notification, o.custom_email_confirmation, o.custom_signature_required from custom_delivery d, drivers dr, orders o, members m where d.id = %d and d.driver_id = dr.id and dr.member_id = %d and service_type='D' and o.id = d.order_id and m.id = o.member_id",$c_id, $this->getUserInfo("id"))) {
			$outer->init($this->m_dir.$module["parm1"]);
			$outer->addFormError("Sorry, but we couldn't locate that order");
			return $outer->show();
		}
		if ($rec["completed"] == 1) {
			$outer->init($this->m_dir.$module["parm1"]);
			$outer->addFormError("This delivery has been completed at ".date("d-M-Y h:i a", strtotime($rec["actual_date"])));
			return $outer->show();
		}
		$rec["actual_date"] = date('Y-m-d H:i:s');
		$outer->init($this->m_dir.$module['outer_html']);
		$outer->setModule($module);
		$outer->addData($rec);
		$flds = $outer->buildForm($this->config->getFields($module['configuration']));
		if (count($_REQUEST) > 0 && array_key_exists("captureSignature",$_REQUEST)) {
			$outer->init($this->m_dir.$module['inner_html']);
			$outer->addData($_REQUEST);
			if (!$outer->validate()) {
				return $outer->show();
			}
			include "./common/license.php";
			$signData = $_REQUEST["ctlSignature_data"]; // the data that comes from client side
			$signDataSmooth = $_REQUEST["ctlSignature_data_canvas"]; // the smooth data that comes from client side
			$im = null;
			if (strlen($signDataSmooth) > 0)
				$im = GetSignatureImageSmooth($signDataSmooth);
			elseif (strlen($signData) > 0)
				$im = GetSignatureImage($signData);
			else $outer->addFormError("No signature returned");
			if ($im != null) {
				$fn = sprintf("./images/signature-%s.png",random_int(1000,10000));
				imagepng($im,$fn,0,NULL);
				$outer->addTag("fn",$fn);
				$fh = fopen($fn,"r");
				$img = fread($fh,filesize($fn));
				fclose($fh);
				unlink($fn);

				$image = imagecreatefromstring($img);
				$bg = imagecreatetruecolor(imagesx($image), imagesy($image));
				imagefill($bg, 0, 0, imagecolorallocate($bg, 255, 255, 255));
				imagealphablending($bg, TRUE);
				imagecopy($bg, $image, 0, 0, 0, 0, imagesx($image), imagesy($image));
				imagedestroy($image);
				$quality = 80;
				imagejpeg($bg, $fn, $quality);
				imagedestroy($bg);
				$img = file_get_contents($fn);
				$this->logMessage(__FUNCTION__,sprintf("image [%s] outer [%s]", $img, print_r($outer,true)),1);

				$upd = array("signature"=>$img, "completed"=>1, "delivery_name"=>$outer->getData("delivery_name"));
				$upd["actual_date"] = array_key_exists("actual_date", $_REQUEST) ? $outer->getData("actual_date") : date(DATE_ATOM);
				if (array_key_exists("scheduled_date",$_REQUEST))
					$upd["scheduled_date"] = $outer->getData("scheduled_date");;
				$stmt = $this->prepare(sprintf("update custom_delivery set %s=? where id = %d", implode("=?, ",array_keys($upd)), $c_id));
				$stmt->bindParams(array_merge(array(str_repeat("s",count($upd))),array_values($upd)));
				$stmt->execute();
				$this->calculateCommissions($rec["order_id"]);
/*
*
*				For admin fedex processing - allow them to enter the date/time, signature etc but not mark as shipped
*				waiting for the actual fedex order to arrive to check the rates
*
*/
				if ($outer->getData("do_not_bill") != 1)
					$this->execute(sprintf("update orders set order_status = ((order_status & ~%d) | %d) where id = %d", STATUS_PROCESSING, STATUS_SHIPPED, $rec["order_id"]));
				if ($this->fetchScalarTest("select coalesce(count(order_id,0)) from order_authorization where order_id = %d and authorization_type='A' and authorization_success = 1", $rec["order_id"]) > 0) {
					$this->execute(sprintf("update orders set order_status = order_status | %d where id = %d", STATUS_TO_BE_BILLED, $rec["order_id"]));
				}
				$outer->addTag("img",base64_encode($img),false);
				if ($this->hasOption("email") && ($rec["custom_delivery_notification"] != 0 || strlen($rec["custom_email_confirmation"]) > 0)) {
					$orderHtml = "";
					$emails = $this->configEmails("ecommerce");
					$orderId = $rec["order_id"];
					if (count($emails) == 0)
						$emails = $this->configEmails("contact");
					$body = new Forms();
					$body->setOption('formDelimiter','{{|}}');
					$mailer = new MyMailer();
					$mailer->Subject = sprintf("Delivery Completed - %s", SITENAME);
					$mailer->AddAttachment($fn,"signature.jpg");
					$sql = sprintf('select * from htmlForms where class = %d and type = "deliveryComplete"',$this->getClassId('custom'));
					$html = $this->fetchSingleTest($sql);
					$body->setHTML($html['html']);
					if (!$order = $this->fetchSingleTest('select o.*, cd.delivery_name, m.firstname, m.lastname, m.email, m.custom_delivery_emails from orders o, members m, custom_delivery cd where o.id = %d and m.id = o.member_id and cd.order_id = o.id and cd.service_type = "D"',$orderId))
						$this->logMessage(__FUNCTION__,sprintf('cannot locate order #[%d]',$orderId),1,true);
					if ($module = $this->fetchSingleTest('select t.*, m.classname, t.id as fetemplate_id from fetemplates t, modules m where t.id = %d and m.id = t.module_id',$this->getOption('email'))) {
						$p = new product($module['id'],$module);
						$_REQUEST["m_id"] = $order["member_id"];
						$orderHtml = $p->orderDetails($orderId);
						$body->addTag('order',$orderHtml,false);

						$mailer->Body = $body->show();
						$mailer->FromName = "KJV Courier Services";
						$mailer->From = "noreply@".HOSTNAME;
						$mailer->IsHTML(true);
						if ($rec["custom_delivery_notification"] != 0) {
							$tmp = strlen($order["custom_delivery_emails"]) > 0 ? explode(";",$order["custom_delivery_emails"]) : array();
							if (count($tmp) == 0) $tmp[] = $order["email"];
							if (defined("DEV") && DEV==1) $tmp = array("ian.w.macarthur@gmail.com");
							foreach($tmp as $sk=>$sv) {
								$mailer->addAddress($sv);
								if (!$mailer->Send()) {
									$this->logMessage(__FUNCTION__,sprintf("Email send failed [%s]",print_r($mailer,true)),1,true);
								}
								$mailer->clearAddresses();
							}
						}
						if (strlen($rec["custom_email_confirmation"]) > 0) {
							$tmp = explode(";",$rec["custom_email_confirmation"]);
							if (defined("DEV") && DEV==1) $tmp = array("ian.w.macarthur@gmail.com");
							foreach($tmp as $sk=>$sv) {
								$mailer->addAddress($sv);
								if (!$mailer->Send()) {
									$this->logMessage(__FUNCTION__,sprintf("Email send failed [%s]",print_r($mailer,true)),1,true);
								}
								$mailer->clearAddresses();
							}
						}
					}
					else
						$this->logMessage(__FUNCTION__,sprintf('delivery confirmation failed [%s]',print_r($this,true)),1,true);
				}
				if ((double)$outer->getData("lat") != 0.0) {
					$parms = array("latitude"=>$_REQUEST["lat"], "longitude"=>$_REQUEST["long"],"member_id"=>$this->getUserInfo("id"),"datetime"=>date(DATE_ATOM));
					$parms["delivery_id"] = $c_id;
					$stmt = $this->prepare(sprintf("insert into driver_location(%s) values(?%s)", implode(", ", array_keys($parms)), str_repeat(", ?", count($parms)-1)));
					$stmt->bindParams(array_merge(array(str_repeat("s",count($parms))),array_values($parms)));
					$stmt->execute();
				}
				if (file_exists($fn)) unlink($fn);
			}
		}
		return $outer->show();
	}

    /**
     * @return array|mixed|string|string[]|void
     * @throws phpmailerException
     */
    function orderHistory() {
		if (!$module = parent::getModule())
			return "";
		$outer = new Forms();
		$outer->setModule($module);
		$outer->init($this->m_dir.$module['outer_html']);
		$flds = $this->config->getFields($module['configuration']);

		//
		//	checksubsidiary - for mgmt positions reporting on multiple companies
		//
		$subdata = $this->subForms($this->m_module['fetemplate_id'],null,array(),'outer');
		$this->logMessage(__FUNCTION__,sprintf('subforms [%s]',print_r($subdata,true)),3);
		foreach($subdata as $key=>$value) {
			$outer->addTag($key,$value,false);
		}
		$m_id = $this->getUserInfo("id");
		$group = $this->fetchSingleTest("select mf.* from members_folders mf, members_by_folder mb where mb.member_id = %d and mf.id = mb.folder_id",$m_id);
		$g_id = $group["id"];
		$m_services = $this->fetchAllTest("select c.id, p.name, c.product_id from product p, custom_member_product_options c, members_by_folder mf where p.id = c.product_id and c.member_id = mf.id and mf.member_id = %d and mf.folder_id = %d and isgroup = 0",$m_id,$g_id);
		$tmp = array(0);
		foreach($m_services as $key=>$value) {
			$tmp[] = $value["product_id"];
		}
		$g_services = $this->fetchAllTest("select c.id, p.name, c.product_id from product p, custom_member_product_options c where p.id = c.product_id and c.member_id = %d and isgroup = 1 and p.id not in (%s)",$g_id, implode(",",array_merge(array(0),$tmp)));
		$services = array_merge($m_services, $g_services);
		$tmp = array("0"=>"-Any-");
		foreach($services as $key=>$value) {
			$tmp[$value["product_id"]] = $value["name"];
		}
		$flds["serviceType"]["options"] = $tmp;

		if ($this->getUserInfo("custom_additional_services") > 0)
			$flds["extras"]["sql"] = sprintf('select p.id,name from product p, product_by_folder pf where pf.folder_id = %d and p.id = pf.product_id and p.deleted = 0 and p.published = 1 and p.enabled = 1 order by pf.sequence', $this->getUserInfo("custom_additional_services"));
		else
			$flds["extras"]["sql"] = sprintf('select p.id,name from product p, product_by_folder pf where pf.folder_id = %d and p.id = pf.product_id and p.deleted = 0 and p.published = 1 and p.enabled = 1 order by pf.sequence', $group["custom_additional_services"]);
		$this->logMessage(__FUNCTION__,sprintf("extras sql [%s]", $flds["extras"]["sql"]),1);
		$flds["packageType"]["sql"] = sprintf("select p.id, p.name, p.id as xxx from product p, product_by_folder pf, members_folders m, members_by_folder mf where mf.member_id = %d and m.id = mf.folder_id and pf.folder_id = m.custom_package_types and p.id = pf.product_id and p.enabled = 1 and p.deleted = 0 order by p.name",$m_id);
		$sql = sprintf("from orders o where member_id = %d and ((o.order_status & %d) = 0) ", $m_id, STATUS_RECURRING);
		$sqlPlus = "";
		$flds = $outer->buildForm($flds);
		if (array_key_exists(__FUNCTION__,$_REQUEST)) {
			$outer->addData($_REQUEST);
			if ($outer->validate()) {
				if (array_key_exists("startDate",$_REQUEST) && $_REQUEST["startDate"] != "")
					$sqlPlus .= sprintf(" and order_date >= '%s'", date("Y-m-d", strtotime($_REQUEST["startDate"])));
				if (array_key_exists("endDate",$_REQUEST) && $_REQUEST["endDate"] != "")
					$sqlPlus .= sprintf(" and order_date <= '%s 23:59:59'", date("Y-m-d", strtotime($_REQUEST["endDate"])));
				if (array_key_exists("pickupAddress",$_REQUEST) && strlen($_REQUEST["pickupAddress"]) > 0) {
					$sqlPlus .= sprintf(" and exists (select 1 from addresses a where a.addresstype=%d and a.ownertype = 'order' and a.ownerid = o.id and a.line1 like '%%%s%%')", ADDRESS_PICKUP, $_REQUEST["pickupAddress"]);
				}
				if (array_key_exists("deliveryAddress",$_REQUEST) && strlen($_REQUEST["deliveryAddress"]) > 0) {
					$sqlPlus .= sprintf(" and exists (select 1 from addresses a where a.addresstype=%d and a.ownertype = 'order' and a.ownerid = o.id and a.line1 like '%%%s%%')", ADDRESS_DELIVERY, $_REQUEST["deliveryAddress"]);
				}
				if (array_key_exists("serviceType",$_REQUEST) && is_array($_REQUEST["serviceType"]) && count($_REQUEST["serviceType"] > 0)) {
					$sqlPlus .= sprintf(" and exists (select 1 from order_lines ol where ol.order_id = o.id and ol.deleted = 0 and ol.product_id in (%s))",implode(",",$_REQUEST["serviceType"]));
				}
				if (array_key_exists("packageType",$_REQUEST) && $_REQUEST["packageType"] > 0) {
					$sqlPlus .= sprintf(" and exists (select 1 from order_lines ol where ol.order_id = o.id and ol.deleted = 0 and ol.product_id = %d)",$_REQUEST["packageType"]);
				}
				if (array_key_exists("onTime",$_REQUEST) && $_REQUEST["onTime"] == 1) {
					$sqlPlus .= sprintf(" and exists (select 1 from custom_delivery cd where cd.service_type='D' and cd.order_id = o.id and completed = 1 and actual_date > addtime(scheduled_date,'%s'))",MAX_LATE);
				}
				if (array_key_exists("extras",$_REQUEST) && $_REQUEST["extras"] > 0) {
					$sqlPlus .= sprintf(" and o.id in (select order_id from order_lines ol, orders o where o.member_id = %d and ol.order_id = o.id and ol.product_id = %d and ol.deleted = 0)", $this->getUserInfo("id"), $_REQUEST["extras"]);
				}
				if (($o_status = $outer->getData("order_status")) > 0) {
					$sqlPlus .= sprintf(" and o.order_status & %d = %d", $o_status, $o_status);
				}
				if (array_key_exists("order_id",$_REQUEST) && $_REQUEST["order_id"] > 0) {
					//
					//	Throw away other parameters if they want an explicit order
					//
					$sqlPlus = sprintf(" and o.id = %d", $_REQUEST["order_id"]);
				}
			}
		}
		if ($outer->getData("export") > 0) {
			if (!($outer->getData("c_id")==0 && $orgs = $this->fetchScalarAllTest("select id from members where custom_parent_org = %d", $this->getUserInfo("id"))))
				$orgs = array($this->getUserInfo("id"));
			$sql = sprintf("select m.company, o.id, o.created, cd1.scheduled_date as pu_scheduled, cd1.actual_date as pu_actual, TIMEDIFF(cd1.actual_date,cd1.scheduled_date) as pu_delta, date_format(cd1.scheduled_date,'%%Y-%%m') as pu_month, cd2.scheduled_date as del_scheduled, cd2.actual_date as del_actual, TIMEDIFF(cd2.actual_date,cd2.scheduled_date) as del_delta, date_format(cd2.scheduled_date,'%%Y-%%m') as del_month, o.total, p.name as service, a1.line1 as pu_address, a1.city as pu_city, a1.postalcode as pu_postal, a2.line1 as del_address, a2.city as del_city, a2.postalcode as del_postalcode, ol2.quantity as km, order_status, o.custom_reference_number 
from `orders` o, `custom_delivery` cd1, `custom_delivery` cd2, `addresses` a1, `addresses` a2, `product` p, `order_lines` ol left join order_lines ol2 on ol2.product_id =27 and ol2.order_id = ol.order_id, `members` m 
where cd1.order_id = o.id and cd1.service_type = 'P' and cd2.order_id = o.id and cd2.service_type='D' and a1.ownertype='order' and a1.ownerid=o.id and a1.ownertype='order' and a1.addresstype=12 and a2.ownertype='order' and a2.addresstype=13 and a2.ownerid = o.id and ol.order_id = o.id and ol.custom_package='S' and p.id = ol.product_id and m.id = o.member_id and m.id in (%s) %s",
implode(",",$orgs), $sqlPlus);
			$orders = $this->fetchAllTest($sql);
			$recs = array();
			$flds = array();
			$o_status = $this->fetchAllTest('select * from code_lookups where type="orderStatus" order by sort');
			foreach($orders[0] as $k=>$v) {
				$flds[] = sprintf('"%s"',$k);
			}
			$recs[] = implode(",",$flds);
			$inner = new Forms();
			$inner->init($this->m_dir.$module['parm1']);
			foreach($orders as $k=>$o) {
				$flds = array();
				$tmp = array();
				foreach($o_status as $key=>$stat) {
					if (($o['order_status'] & (int)$stat['code']) == (int)$stat['code'])
						$tmp[] = $stat['value'];
				}
				$o['order_status'] = implode(', ',$tmp);
				foreach($o as $sk=>$sv) {
					while (strpos($sv,'"')) {
						$sv = str_replace('"','^',$sv);
					}
					$sv = str_replace('^','""',$sv);
					$flds[] = sprintf('"%s"',$sv);
				}
				$recs[] = implode(",",$flds);
			}
$this->logMessage(__FUNCTION__,sprintf("recs [%s] orders [%s]", print_r($recs,true), print_r($orders,true)),1);
			ob_end_clean();
			header('Content-type: application/CSV');
			header(sprintf("Content-Disposition: filename=order_export_%s.csv",date("Y-m-d-h-i-s")));
			echo implode("\r\n",$recs);
			exit;
		}
		else {
			$totals = $this->fetchSingleTest(sprintf("select coalesce(sum(total),0) as g_total, coalesce(sum(authorization_amount),0) as g_authorization_amount, count(1) as g_ct %s %s", $sql, $sqlPlus));
			foreach($totals as $k=>$v) {
				$outer->setData($k,$v);
			}
			$sql = sprintf("select * %s %s order by order_date desc", $sql, $sqlPlus);
			$pagination = $this->getPagination($sql,$module,$recordCount);
			$orders = $this->fetchAllTest($sql);
			$recs = array();
			$inner = new Forms();
			$inner->init($this->m_dir.$module['inner_html']);
			$totals = array("p_ct"=>0,"p_total"=>0,"p_authorization_amount"=>0);
			foreach($orders as $key=>$value) {
				$totals["p_ct"] += 1;
				$totals["p_total"] += $value["total"];
				$totals["p_authorization_amount"] += $value["authorization_amount"];
				$inner->reset();
				$value["serviceType"] = $this->fetchScalarTest("select p.name from product p, order_lines ol where ol.order_id = %d and ol.custom_package = 'S' and p.id = ol.product_id and ol.deleted = 0", $value["id"]);
				$value["early"] = 0;
				if ($pickup = $this->fetchSingleTest("select * from custom_delivery where order_id = %d and service_type='P'", $value["id"])) {
					$pickup["address"] = $this->fetchSingleTest("select * from addresses where ownerid = %d and ownertype='order' and addresstype=%d", $value["id"], ADDRESS_PICKUP);
					$value["pickup"] = $pickup;
				}
				if ($delivery = $this->fetchSingleTest("select * from custom_delivery where order_id = %d and service_type='D'", $value["id"])) {
					$delivery["address"] = $this->fetchSingleTest("select * from addresses where ownerid = %d and ownertype='order' and addresstype=%d", $value["id"], ADDRESS_DELIVERY);
					$value["delivery"] = $delivery;
					$value["early"] = is_array($delivery) && $delivery["completed"] && $delivery["actual_date"] <= date("Y-m-d H:i:s", strtotime(sprintf("%s + 15 minutes", $delivery["scheduled_date"])));
				}
				$inner->addData($this->formatOrder($value));
				$recs[] = $inner->show();
			}
			foreach($totals as $k=>$v) {
				$outer->setData($k,$v);
			}
			//$outer->addData($totals);
			$outer->addTag("orders",implode("",$recs),false);
			$outer->addTag("pagination",$pagination,false);
			return $outer->show();
		}
	}

    /**
     * @return array|mixed|string|string[]
     * @throws phpmailerException
     */
    function getDimensions() {
		if (!$module = parent::getModule())
			return "";
		$outer = new Forms();
		$outer->setModule($module);
		$outer->init($this->m_dir.$module['outer_html']);
		$flds = $this->config->getFields($module['configuration']);
		$p = $this->fetchSingleTest("select * from product where id = %d", array_key_exists("p_id",$_REQUEST ? $_REQUEST["p_id"] : 0));
		$outer->addData($p);
		return $outer->show();
	}

    /**
     * @param $data
     * @return bool
     * @throws phpmailerException
     */
    function postSignup($data = array()) {
		$result = true;
		if ((!array_key_exists("custom_zones",$data)) || $data["custom_zones"] == 0) {
			$rec = $this->fetchSingleTest("select * from members where id = %d", $data["id"]);
			if ($rec["custom_zones"] != 0) {
				$this->logMessage(__FUNCTION__,sprintf("*** found it *** data [%s]", print_r($data,true)), 1,true,true);
			}
			else {
				$result = $this->execute(sprintf("update members set custom_zones = %d where id = %d", DEFAULT_ZONE_GROUP, array_key_exists("id",$data) ? $data["id"] : 0));
			}
		}
		$data["address"] = Address::formatData($this->fetchSingleTest("select * from addresses where ownertype='member' and ownerid=%d", $data["id"]));
		$this->logMessage(__FUNCTION__,sprintf("input data [%s] post [%s]", print_r($data,true), print_r($_POST,true)),1);
		$emails = $this->configEmails("ecommerce");
		if (count($emails) == 0)
			$emails = $this->configEmails("contact");
		$mailer = new MyMailer();
		$mailer->Subject = sprintf("New Account Created - %s", SITENAME);
		$body = new Forms();
		$body->setOption('formDelimiter','{{|}}');
		$sql = sprintf('select * from htmlForms where class = %d and type = "New Account Created"',$this->getClassId('custom'));
		$html = $this->fetchSingleTest($sql);
		$body->setHTML($html['html']);
		$body->addData($data);
		$mailer->Body = $body->show();
		$mailer->From = $emails[0]['email'];
		$mailer->FromName = $emails[0]['name'];
		$mailer->IsHTML(true);
		foreach($emails as $k=>$v) {
			$mailer->addAddress($v['email'],$v['name']);
		}
		if (!$mailer->Send()) {
			$this->logMessage(__FUNCTION__,sprintf("Signup Email send failed [%s]",print_r($mailer,true)),1,true);
		}
		return $result;
	}

    /**
     * @return array|mixed|string|string[]
     * @throws phpmailerException
     */
    function orderPayments() {
		if (!$module = $this->getModule())
			return "";
		$outer = new Forms();
		$outer->init($this->m_dir.$module["outer_html"]);
		$inner = new Forms();
		$inner->init($this->m_dir.$module["inner_html"]);
		$flds = $inner->buildForm($this->getFields($module["configuration"]));
		$o_id = array_key_exists("o_id",$_REQUEST) ? $_REQUEST["o_id"] : 0;
		$pmts = $this->fetchAllTest("select d.*, p.reference_number, p.reference_date from order_payment p, order_payment_detail d where d.order_id = %d and p.id = d.payment_id", $o_id);
		$result = array();
		foreach($pmts as $key=>$rec) {
			$inner->addData($rec);
			$result[] = $inner->show();
		}
		$outer->addTag("payments", implode("",$result), false);
		return $outer->show();
	}

    /**
     * @return array|mixed|string|string[]
     * @throws phpmailerException
     */
    function getSignature() {
		if (!$module = $this->getModule())
			return "";
		$outer = new Forms();
		$outer->init($this->m_dir.$module["outer_html"]);
		$inner = new Forms();
		$inner->init($this->m_dir.$module["inner_html"]);
		$rec = $this->fetchSingleTest("select * from custom_delivery where id = %d", array_key_exists("c_id", $_REQUEST ? $_REQUEST["c_id"] : 0));
		$order = $this->fetchSingleTest("select * from orders where id = %d", $rec["order_id"]);
		$rec["img"] = base64_encode($rec["signature"]);
		$rec["order"] = $order;
		//$fh = fopen(sprintf("images/members/%d/%d.png", $order["member_id"], $order["id"]),"w");
		//$x = fwrite($fh, $rec["signature"], strlen($rec["signature"]));
		//$this->logMessage(__FUNCTION__,sprintf("written [%d] to write [%d] data [%s]", $x, strlen($rec["signature"]), $rec["signature"]),1);
		//fclose($fh);
		$inner->addData($rec);
		$outer->addData($order);
		$outer->addTag("signature",$inner->show());
		return $outer->show();
	}

    /**
     * @return array|mixed|string|string[]
     * @throws phpmailerException
     */
    function driverPayments() {
		if (!$module = $this->getModule())
			return "";
		$outer = new Forms();
		$outer->init($this->m_dir.$module["outer_html"]);
		$outer->setModule($module);
		$inner = new Forms();
		$inner->init($this->m_dir.$module["inner_html"]);
		$flds = $outer->buildForm($this->getFields($module["configuration"]));
		$flds = $inner->buildForm($flds);
		$p_count = 0;
		$p_total = 0;
		if (array_key_exists("driverPayout",$_REQUEST)) {
			$outer->addData($_REQUEST);
			if ($outer->validate()) {
				$g = $this->fetchSingleTest(sprintf("select coalesce(count(c.id),0) as ct, coalesce(sum(payment),0) as pay from custom_delivery c, product p, order_lines o where completed = 1 and driver_id = %d and date_format(actual_date,'%%Y-%%m-%%d') >= '%s' and date_format(actual_date,'%%Y-%%m-%%d') <= '%s' and o.order_id = c.order_id and o.custom_package = 'S' %s and p.id = o.product_id and o.deleted = 0 order by actual_date", $this->getUserInfo("id"), $outer->getData("startDate"), $outer->getData("endDate"), array_key_exists("serviceType",$_REQUEST) ? sprintf('and o.product_id in (%s)',implode(",",array_merge(array(0),array_values($_REQUEST["serviceType"])))):''));
				$this->logMessage(__FUNCTION__,sprintf("g is [%s]", print_r($g,true)),1);
				$outer->setData("g_count",$g["ct"]);
				$outer->setData("g_total",$g["pay"]);
				$sql = sprintf("select c.*, p.name from custom_delivery c, product p, order_lines o where completed = 1 and driver_id = %d and date_format(actual_date,'%%Y-%%m-%%d') >= '%s' and date_format(actual_date,'%%Y-%%m-%%d') <= '%s' and o.order_id = c.order_id and o.custom_package = 'S' and p.id = o.product_id and o.deleted = 0 %s order by actual_date", $this->getUserInfo("id"), $outer->getData("startDate"), $outer->getData("endDate"), array_key_exists("serviceType",$_REQUEST) ? sprintf('and o.product_id in (%s)',implode(",",array_merge(array(0),array_values($_REQUEST["serviceType"])))):'');
				$pagination = $this->getPagination($sql,$module,$recordCount);
				$this->logMessage(__FUNCTION__,sprintf("pagination [%s] sql [%s]",$pagination,$sql),1);
				$outer->addTag("pagination",$pagination,false);
				$recs = $this->fetchAllTest($sql);
				$result = array();
				foreach($recs as $rec) {
					$inner->addData($rec);
					$result[] = $inner->show();
					$p_total += $rec["payment"];
					$p_count += 1;
				}
				$outer->addTag("payments",implode("",$result),false);
				$outer->setData("p_count",$p_count);
				$outer->setData("p_total",$p_total);
			}
			else {
				$outer->addFormError("Form Validation Failed");
				$this->logMessage(__FUNCTION__,sprintf("form validation failed [%s]",print_r($outer,true)),1);
			}
		}
		return $outer->show();
	}

    /**
     * @return string|void
     * @throws \Pdfcrowd\Error
     * @throws phpmailerException
     */
    function waybill() {
		if (!$module = $this->getModule())
			return "";
		$o_id = array_key_exists("o_id",$_REQUEST) ? $_REQUEST["o_id"] : 0;
		if ($o_id == 0) return;
		$sql = sprintf('select o.*, c1.code as wtCode, c2.code as dimCode from orders o, code_lookups c1, code_lookups c2, members m 
		where o.id = %d and m.id = o.member_id and (m.id = %d or m.custom_parent_org = %2$d or m.id in (select m1.id from members m1 where custom_parent_org = %d)) and c1.id = o.custom_weight_code and c2.id = o.custom_dimension_code', $o_id, 
						$this->getUserInfo("id"), $this->checkArray("mgmt:user:id",$_SESSION));
		if ($order = $this->fetchSingleTest($sql)) {
			$pu = $this->fetchSingleTest("select * from custom_delivery where order_id = %d and service_type = 'P'",$o_id);
			$de = $this->fetchSingleTest("select * from custom_delivery where order_id = %d and service_type = 'D'",$o_id);
			$pu["address"] = Address::formatData($this->fetchSingleTest("select * from addresses where ownerid = %d and ownertype = 'order' and addresstype = %d",$o_id,ADDRESS_PICKUP));
			$de["address"] = Address::formatData($this->fetchSingleTest("select * from addresses where ownerid = %d and ownertype = 'order' and addresstype = %d",$o_id,ADDRESS_DELIVERY));
			$pu["driver"] = $this->fetchSingleTest("select m.* from drivers d, members m where d.id = %d and m.id = d.member_id", $pu["driver_id"]);
			$de["driver"] = $this->fetchSingleTest("select m.* from drivers d, members m where d.id = %d and m.id = d.member_id", $de["driver_id"]);
			$de["has_signature"] = strlen($de["signature"]) > 0;
			$de["sig_image"] = base64_encode($de["signature"]);
			$order["pickup"] = $pu;
			$order["delivery"] = $de;
			$service = $this->fetchSingleTest("select ol.*, p.name from order_lines ol, product p where ol.order_id = %d and ol.custom_package='S' and p.id = ol.product_id and ol.deleted = 0",$o_id);
			$packages = $this->fetchAllTest("select ol.*, p.name, ol.product_id, d.quantity as qty, d.height, d.width, d.depth, d.weight, d.quantity*d.weight as tWeight 
from order_lines ol, product p, order_lines_dimensions d 
where ol.order_id = %d and (ol.custom_package='P' or ol.product_id = %d) and p.id = ol.product_id and d.line_id = ol.line_id and d.order_id = ol.order_id and ol.deleted = 0
order by ol.line_id", $o_id, FEDEX_RECALC );
			$additional = $this->fetchAllTest("select ol.*, p.name from order_lines ol, product p where ol.order_id = %d and ol.custom_package='A' and p.id = ol.product_id and ol.deleted = 0",$o_id);
			$order["service"] = $service;
			$order["packages"] = $packages;
			$order["charges"] = $additional;
		}
		$outer = new Forms();
		$outer->init($this->m_dir.$module["outer_html"]);
		$outer->setModule($module);
		$inner = new Forms();
		$inner->init($this->m_dir.$module["inner_html"]);
		$flds = $outer->buildForm($this->getFields($module["configuration"]));
		$outer->addData($order);
		$products = array();
		$wt = 0;
		$qty = 0;
		$hasRecalc = 0;
		foreach($packages as $key=>$prod) {
			$hasRecalc |= $prod["product_id"] == FEDEX_RECALC;
		}
		$outer->setData("hasRecalc", $hasRecalc);
		$inner->setData("hasRecalc", $hasRecalc);
		foreach($packages as $key=>$prod) {
			$inner->addData($prod);
			if ($hasRecalc && $prod["product_id"] == FEDEX_RECALC) {
					$wt += $prod["tWeight"];
					$qty += $prod["qty"];
			}
			else {
				if (!$hasRecalc) {
					$wt += $prod["tWeight"];
					$qty += $prod["qty"];
				}
			}
			$products[] = $inner->show();
		}
		$outer->addTag("qty",$qty);
		$outer->addTag("tWeight",sprintf("%.2f",$wt));
		$outer->addTag("products",implode("",$products),false);
		$html = $outer->show();
		try {
			// create the API client instance
			$client = new \Pdfcrowd\HtmlToPdfClient($GLOBALS["pdfcrowd"]["user"], $GLOBALS["pdfcrowd"]["token"]);
			// run the conversion and write the result to a file
			ob_clean();
			$pdf = $client->convertString($html);
			header('Content-Type: application/pdf');
			header('Cache-Control: no-cache');
			header('Accept-Ranges: none');
			header(sprintf('Content-Disposition: filename="%s"',sprintf('waybill-%d.pdf',$o_id)));
			echo $pdf;
			exit;
		}
		catch(\Pdfcrowd\Error $why) {
			// report the error
			error_log("Pdfcrowd Error: {$why}\n");
			// rethrow or handle the exception
			throw $why;
		}
/*
		$pdf->writeHTML($html, true, false, true, false, '');
		$pdf->lastPage();
		try {
			ob_clean();
			$pdf->Output(sprintf('waybill-%d.pdf',$o_id), 'I');
		}
		catch(Exception $err) {
			return print_r($err,true);
		}
*/
	}

    /**
     * @return array|mixed|string|string[]
     * @throws phpmailerException
     */
    function resetDates() {
		if (!$module = $this->getModule())
			return "";
		$outer = new Forms();
		$outer->init($this->m_dir.$module["outer_html"]);
		$flds = $outer->buildForm($this->getFields($module["configuration"]));
		$outer->setModule($module);
		if ($this->checkArray(__FUNCTION__,$_REQUEST)) {
			$this->beginTransaction();
			$this->execute("update custom_delivery set 
scheduled_date = concat(curdate(),' ',date_format(scheduled_date,'%H:%i:%s')), completed=0
where id in (select id from (select c.id from custom_delivery c, order_lines ol, product p where ol.order_id = c.order_id and p.id = ol.product_id and p.custom_same_day = 1 and ol.custom_package = 'S' and ol.deleted = 0) as ids);");
			$recs = $this->fetchAllTest("select order_id, cast(rand() as unsigned) as r from order_lines ol, product p where p.id = ol.product_id and p.custom_same_day = 0 and ol.custom_package = 'S' and ol.deleted = 0");
			foreach($recs as $rec) {
				if ($rec["r"] == 0) {
					$this->execute(sprintf("update custom_delivery set scheduled_date = addtime(concat(curdate(),' ',date_format(scheduled_date,'%%H:%%i:%%s')), '-1 0:0:0'), actual_date='0000-00-00 00:00:00',completed=0 where order_id = %d and service_type='P'", $rec["order_id"]));
					$this->execute(sprintf("update custom_delivery set scheduled_date = concat(curdate(),' ',date_format(scheduled_date,'%%H:%%i:%%s')), actual_date='0000-00-00 00:00:00', completed=0 where order_id = %d and service_type='D'", $rec["order_id"]));
				}
				else {
					$this->execute(sprintf("update custom_delivery set scheduled_date = addtime(concat(curdate(),' ',date_format(scheduled_date,'%%H:%%i:%%s')), '1 0:0:0'), actual_date='0000-00-00 00:00:00',completed=0 where order_id = %d and service_type='D'", $rec["order_id"]));
					$this->execute(sprintf("update custom_delivery set scheduled_date = concat(curdate(),' ',date_format(scheduled_date,'%%H:%%i:%%s')), actual_date='0000-00-00 00:00:00', completed=0 where order_id = %d and service_type='P'", $rec["order_id"]));
				}
			}
			$this->commitTransaction();
			$outer->addFormSuccess("Pickup/Delivery dates have been reset");
		}
		return $outer->show();
	}

    /**
     * @return array|mixed|string|string[]
     * @throws phpmailerException
     */
    function driverSearch() {
		if (!$module = $this->getModule())
			return "";
		$outer = new Forms();
		$outer->init($this->m_dir.$module["outer_html"]);
		$flds = $outer->buildForm($this->getFields($module["configuration"]));
		$outer->setModule($module);
		if (array_key_exists(__FUNCTION__,$_REQUEST)) {
			$outer->addData($_REQUEST);
			if (array_key_exists("c_id",$_REQUEST) && array_key_exists("o_id",$_REQUEST)) {
				$this->execute(sprintf("update custom_delivery set driver_id = (select id from drivers where member_id = %d), scheduled_date = now(), comments = 'Driver overridden' where id = %d and order_id = %d and service_type = 'D'", $this->getUserInfo("id"), $_REQUEST["c_id"], $_REQUEST["o_id"]));
			}
			$sql = sprintf("select order_id from custom_delivery c, drivers d where d.member_id = %d and c.driver_id = d.id and service_type='P'", $this->getUserInfo("id"));
			$cur = array_merge(array(0),$this->fetchScalarAllTest($sql));
			$sql = sprintf("select o.*, m.firstname, m.lastname, a.line1, a.city, a.postalcode, c.id as delivery_id, c.scheduled_date from orders o, addresses a, custom_delivery c left join drivers d on d.id = c.driver_id left join members m on m.id = d.member_id where o.id in (%s) and a.ownertype='order' and a.ownerid = o.id and a.addresstype = %d and c.order_id = o.id and c.service_type = 'D' and o.order_status & %d = %d", implode(",",$cur), ADDRESS_DELIVERY, STATUS_PROCESSING, STATUS_PROCESSING );
			foreach($_REQUEST as $key=>$value) {
				if ($value != "") {
					switch($key) {
					case "order_id":
						if ((int)$value > 0)
							$sql .= sprintf(" and o.id = %d", $value);
						break;
					case "driver_id":
						$sql .= sprintf(" and d.id = %d", $value);
						break;
					case "address":
						$sql .= sprintf(" and (a.line1 like '%%%s%%' or a.postalcode like '%%%s%%')", $value, $value);
						break;
					}
				}
			}
			$orders = $this->fetchAllTest($sql);
			$results = array();
			$inner = new Forms();
			$inner->init($this->m_dir.$module["inner_html"]);
			foreach($orders as $key=>$rec) {
				$inner->addData($rec);
				$results[] = $inner->show();
			}
			$outer->addTag("results",implode("",$results),false);
		}
		return $outer->show();
	}

    /**
     * @return array|mixed|string|string[]
     * @throws phpmailerException
     */
    function dispatching() {
		if (!$module = parent::getModule())
			return "";
		$outer = new Forms();
		$outer->init($this->m_dir.$module['outer_html']);
		$o_flds = $this->config->getFields($module['configuration']);
		$o_flds["pu_date"]["sql"] = "select distinct date(cd.scheduled_date), date_format(cd.scheduled_date,'%d-%b-%Y') from custom_delivery as cd, order_lines ol, product p, orders o where  cd.service_type = 'P' and DATE(cd.scheduled_date) >= CURDATE() and ol.order_id = cd.order_id and ol.custom_package = 'S' and p.id = ol.product_id and p.custom_same_day = 1 and o.id = ol.order_id and o.order_status & 40 = 0 and o.deleted = 0 and ol.deleted = 0 and o.order_status_processing and cd.completed = 0 order by 1";
		$o_flds = $outer->buildForm($o_flds);
		$inner = new Forms();
		$inner->init($this->m_dir.$module['inner_html']);
		$flds = $inner->buildForm($this->config->getFields($module['configuration']."Row"));
		$sql = sprintf("select distinct m.company, o.custom_declared_value, o.custom_signature_required, o.custom_placed_by, o.member_id, ol.*, p.name as product_name, p.custom_color_coding from members m, custom_delivery as c1 left join drivers d1 on d1.id = c1.driver_id, custom_delivery as c2 left join drivers d2 on d2.id = c2.driver_id, order_lines ol, product p, orders o where ");
		if (!(count($_REQUEST) > 0 && array_key_exists(__FUNCTION__,$_REQUEST))) {
			$_REQUEST["completed"] = 0;
			$_REQUEST["pager"] = "25";
			$_REQUEST["driver_id"] = "";
			$_REQUEST[__FUNCTION__] = 1;
		}
		$outer->addData($_REQUEST);
		$outer->validate();
		$parms = array(
			"a"=>"c1.service_type = 'P' and DATE(c1.scheduled_date) <= CURDATE()",
			"b"=>"c2.service_type = 'D' and DATE(c2.scheduled_date) <= CURDATE()",
			"c"=>"c1.order_id = c2.order_id",
			"d"=>"ol.order_id = c1.order_id",
			"e"=>"ol.custom_package = 'S'",
			"f"=>"p.id = ol.product_id",
			"g"=>"p.custom_same_day = 1",
			"h"=>"o.id = ol.order_id",
			"i"=>sprintf("o.order_status & %d = 0",STATUS_RECURRING | STATUS_CANCELLED), 
			"j"=>"o.deleted = 0",
			"k"=>"ol.deleted = 0",
			"l"=>"(isnull(d2.id) or d2.third_party=0)",
			"m"=>"(isnull(d1.id) or d2.third_party=0)",
			"n"=>"o.order_status_processing",
			"o"=>"m.id = o.member_id"
		);
		foreach($o_flds as $sk=>$sv) {
			switch($sk) {
			case "pager":
				$pager = $outer->getData("pager");
				break;
			case "completed":
				if (strlen($tmp = $outer->getData("completed")) > 0) {
					$parms[$sk] = sprintf("c2.completed = %d", $tmp);
					if ($tmp==1) {
						$parms["b"] = "c2.service_type = 'D' and DATE(c2.actual_date) = CURDATE()";
						$parms["n"] = sprintf("o.order_status & %d = %d", STATUS_SHIPPED, STATUS_SHIPPED);
					}
					else
						$parms["b"] = "c2.service_type = 'D' and DATE(c2.scheduled_date) <= CURDATE()";
				}
				break;
			case "product_id":
				if (($tmp = $outer->getData("product_id")) > 0)
					$parms[$sk] = sprintf("p.id = %d ", $tmp);
				break;
			case "driver_id":
				if (strlen($tmp = $outer->getData("driver_id")) > 0)
					$parms[$sk] = sprintf("(c1.driver_id = %d or c2.driver_id = %d) ", $tmp, $tmp);
				break;
			case 'group_id':
				if (($tmp = $outer->getData("group_id")) > 0)
					$parms[$sk] = sprintf("(d1.group_id = %d or d2.group_id = %d) ", $tmp, $tmp);
				break;
			default:
			}
		}
		$pu_date = $outer->getData("pu_date");
		if ($pu_date != date("Y-m-d") && $pu_date != '') {
			$parms["a"] = sprintf("c1.service_type = 'P' and DATE(c1.scheduled_date) = '%s'", $pu_date);
			$parms["b"] = "c2.service_type = 'D'";
		}
		$sql = sprintf("%s %s %s limit %d", $sql, implode(" and ", array_values($parms))," order by IF(c1.driver_id=0,0,1), IF(c1.driver_id=0,c1.scheduled_date,1), IF(c2.actual_date='0000-00-00 00:00:00',c2.scheduled_date,c1.actual_date)", $outer->getData("pager"));
		$outer->setModule($module);
		$recs = array();
		$data = $this->fetchAllTest($sql);
		foreach($data as $rec) {
			if ($rec["pickup"] = $this->fetchSingleTest("select *, date(scheduled_date) as sd, curdate() as td from custom_delivery where order_id = %d and service_type='P'",$rec["order_id"])) {
				$d = new DateTime($rec["pickup"]["scheduled_date"]);
				$et = $d->diff(new DateTime(date(DATE_ATOM)));
				$rec["pickup"]["et"] = (60*$et->h + $et->i)*($et->invert == 0 ? -1:1);
				if ($et->d <= 0)
					$rec["pickup"]["et"] = (60*$et->h + $et->i)*($et->invert == 0 ? -1:1);
				else
					$rec["pickup"]["et"] = 999;
				if (!$rec["pickup"]["driver"] = $this->fetchSingleTest("select v.name as vehicle, m.* from members m, drivers d, custom_delivery c, vehicles v where c.order_id = %d and service_type='P' and d.id = c.driver_id and m.id = d.member_id and v.id = d.vehicle_id", $rec["order_id"]))
					$rec["pickup"]["driver"] = array("id"=>0,"firstname"=>"","lastname"=>"","vehicle"=>"0");
			}
			if ($rec["delivery"] = $this->fetchSingleTest("select *, date(scheduled_date) as sd, curdate() as td from custom_delivery where order_id = %d and service_type='D'",$rec["order_id"])) {
				$d = new DateTime($rec["delivery"]["scheduled_date"]);
				$et = $d->diff(new DateTime(date(DATE_ATOM)));
				$rec["delivery"]["et"] = (60*$et->h + $et->i)*($et->invert == 0 ? -1:1);
				if (!$rec["delivery"]["driver"] = $this->fetchSingleTest("select v.name as vehicle, m.* from members m, drivers d, custom_delivery c, vehicles v where c.order_id = %d and service_type='D' and d.id = c.driver_id and m.id = d.member_id and v.id = d.vehicle_id", $rec["order_id"]))
					$rec["delivery"]["driver"] = array("id"=>0,"firstname"=>"","lastname"=>"","vehicle"=>"0");
			}
			$rec["pickup"]["address"] = $this->fetchSingleTest("select * from addresses where ownerid=%d and ownertype='order' and addresstype=%d", $rec["order_id"], ADDRESS_PICKUP);
			$rec["delivery"]["address"] = $this->fetchSingleTest("select * from addresses where ownerid=%d and ownertype='order' and addresstype=%d", $rec["order_id"], ADDRESS_DELIVERY);
			$rec["services"] = implode(", ", $this->fetchScalarAllTest("select p.name from product p, order_lines ol where ol.order_id = %d and ol.custom_package = 'A' and ol.deleted = 0 and p.id = ol.product_id and p.custom_special_requirement = 1", $rec["order_id"]));
			$rec["billto"] = $this->fetchSingleTest("select * from addresses where ownerid = %d and ownertype = 'member' and addresstype = %d and deleted = 0", $rec["member_id"], ADDRESS_COMPANY);
			$packages = $this->fetchAllTest("select sum(old.quantity) as qty, sum(old.weight) as weight, p.name, c.code, c1.code as dim_units, old.height, old.width, old.depth from order_lines ol, order_lines_dimensions old, product p, orders o, code_lookups c, code_lookups c1 where ol.order_id = %d and ol.custom_package='P' and p.id = ol.product_id and old.order_id = ol.order_id and old.line_id = ol.line_id and o.id = ol.order_id and c.id = o.custom_weight_code and c1.id = o.custom_dimension_code group by old.line_id", $rec["order_id"]);
			$tmp = array();
			foreach($packages as $k=>$v) {
				$tmp[] = sprintf("%s - %d @ %d %s<br/>%d x %d x %d %s", $v["name"], $v["qty"], $v["weight"], $v["code"], $v["height"], $v["width"], $v["depth"], $v["dim_units"]);
			}
			$rec["packages"] = implode("<br/>",$tmp);
			$inner->reset();
			$inner->addData($rec);
			$recs[] = $inner->show();
		}
		$outer->addTag("deliveries", implode("", $recs), false);
		$subdata = $this->subForms($this->m_module['fetemplate_id'],null,array(),'outer');
		$this->logMessage(__FUNCTION__,sprintf('subforms [%s]',print_r($subdata,true)),3);
		foreach($subdata as $key=>$value) {
			$outer->addTag($key,$value,false);
		}
		return $outer->show();
	}

    /**
     * @return array|mixed|string|string[]
     * @throws Exception
     */
    function overnightPickups() {
		if (!$module = parent::getModule())
			return "";
		$outer = new Forms();
		$outer->init($this->m_dir.$module['outer_html']);
		$o_flds = $outer->buildForm($this->config->getFields($module['configuration']));
		$inner = new Forms();
		$inner->init($this->m_dir.$module['inner_html']);
		$flds = $inner->buildForm($this->config->getFields($module['configuration']."Row"));
		$sql = sprintf("select o.custom_declared_value, o.custom_signature_required, o.custom_placed_by, ol.*, c1.id as pickup, c2.id as delivery, p.name as product_name, o.order_status & %d as needs_approval, p.custom_color_coding
from custom_delivery c1 left join drivers d1 on d1.id = c1.driver_id, orders o, order_lines ol, product p, custom_delivery c2 left join drivers d2 on d2.id = c2.driver_id where ", STATUS_NEEDS_APPROVAL);
$parms = array(
"a"=>"c1.service_type = 'P'",
"b"=>"o.id = c1.order_id",
"c"=>"ol.order_id = o.id",
"d"=>"ol.custom_package = 'S'",
"e"=>"p.id = ol.product_id",
"f"=>"p.custom_same_day = 0",
"g"=>"c2.order_id = c1.order_id",
"h"=>"c2.service_type='D'",
"i"=>"c2.completed = 0",
"k"=>"ol.custom_package = 'S'",
"l"=>"ol.deleted = 0",
"m"=>"o.deleted = 0",
"n"=>sprintf("o.order_status & %d = 0", STATUS_INCOMPLETE | STATUS_CANCELLED),
"o"=>"(isnull(d1.id) or d1.third_party=1)",
#"p"=>"(isnull(d2.id) or d2.third_party=0)",
"q"=>"o.order_status_processing"
);

		if (!(count($_REQUEST) > 0 && array_key_exists(__FUNCTION__,$_REQUEST))) {
			$_REQUEST[__FUNCTION__] = 1;
			$_REQUEST["driver_id"] = "";
			$_REQUEST["completed"] = "0";
			$_REQUEST["pager"] = 25;
		}
		$outer->addData($_REQUEST);
		$outer->setModule($module);
		foreach($o_flds as $sk=>$sv) {
			$tmp = $outer->getData($sk);
			switch($sk) {
			case "product_id":
				if (strlen($tmp) > 0)
					$parms[$sk] = sprintf("p.id = %d ", $tmp);
					break;
			case "driver_id":
				if (strlen($tmp) > 0)
					$parms["o"] = sprintf("(c1.driver_id = %d or c2.driver_id = %d) ", $tmp, $tmp);
					break;
			case "completed":
				if ($tmp == 0)
					$parms[$sk] = sprintf("(c1.completed = %d and DATE(c1.scheduled_date) <= CURDATE())", $tmp);
				else {
					$parms[$sk] = sprintf("(c1.completed = %d and DATE(c1.actual_date) = CURDATE())", $tmp);
					unset($parms["o"]);
				}
				break;
			case "pager":
				break;
			default:
			}
		}
		$sql = sprintf("%s %s order by c1.driver_id!=0, needs_approval=1, c1.completed, IF(c1.completed,c1.actual_date,c1.scheduled_date) limit %d", $sql, implode(" and ", array_values($parms)), $outer->getData("pager"));
		$recs = array();
		$data = $this->fetchAllTest($sql);
		foreach($data as $rec) {
			if ($rec["pickup"] = $this->fetchSingleTest("select *, CURDATE() as td, DATE(actual_date) as ad, DATE(scheduled_date) as sd from custom_delivery where id=%d",$rec["pickup"])) {
				$d = new DateTime($rec["pickup"]["scheduled_date"]);
				$et = $d->diff(new DateTime(date(DATE_ATOM)));
				$rec["pickup"]["et"] = (60*$et->h + $et->i)*($et->invert == 0 ? -1:1);
				if (!($rec["pickup"]["driver"] = $this->fetchSingleTest("select v.name as vehicle, m.* from members m, drivers d, custom_delivery c, vehicles v where c.order_id = %d and service_type='P' and d.id = c.driver_id and m.id = d.member_id and v.id = d.vehicle_id", $rec["order_id"])))
					$rec["pickup"]["driver"] = array("id"=>0,"firstname"=>"","lastname"=>"");
			}
			if ($rec["delivery"] = $this->fetchSingleTest("select *, CURDATE() as td, DATE(actual_date) as ad, DATE(scheduled_date) as sd from custom_delivery where id=%d",$rec["delivery"])) {
				$d = new DateTime($rec["delivery"]["scheduled_date"]);
				$et = $d->diff(new DateTime(date(DATE_ATOM)));
				$rec["delivery"]["et"] = (60*$et->h + $et->i)*($et->invert == 0 ? -1:1);
				if (!($rec["delivery"]["driver"] = $this->fetchSingleTest("select v.name as vehicle, m.* from members m, drivers d, custom_delivery c, vehicles v where c.order_id = %d and service_type='D' and d.id = c.driver_id and m.id = d.member_id and v.id = d.vehicle_id", $rec["order_id"])))
					$rec["delivery"]["driver"] = array("id"=>0,"firstname"=>"","lastname"=>"");
			}
			$rec["pickup"]["address"] = $this->fetchSingleTest("select * from addresses where ownerid=%d and ownertype='order' and addresstype=%d", $rec["order_id"], ADDRESS_PICKUP);
			$rec["delivery"]["address"] = $this->fetchSingleTest("select * from addresses where ownerid=%d and ownertype='order' and addresstype=%d", $rec["order_id"], ADDRESS_DELIVERY);
			$rec["services"] = implode(", ", $this->fetchScalarAllTest("select p.name from product p, order_lines ol where ol.order_id = %d and ol.custom_package = 'A' and ol.deleted = 0 and p.id = ol.product_id and p.custom_special_requirement = 1", $rec["order_id"]));

			$packages = $this->fetchAllTest("select sum(old.quantity) as qty, sum(old.weight) as weight, p.name, c.code from order_lines ol, order_lines_dimensions old, product p, orders o, code_lookups c where ol.order_id = %d and ol.custom_package='P' and p.id = ol.product_id and old.order_id = ol.order_id and old.line_id = ol.line_id and o.id = ol.order_id and c.id = o.custom_weight_code group by old.line_id", $rec["order_id"]);
			$tmp = array();
			foreach($packages as $k=>$v) {
				$tmp[] = sprintf("%s - %d @ %d %s", $v["name"], $v["qty"], $v["weight"], $v["code"]);
			}
			$rec["packages"] = implode("<br/>",$tmp);

			$inner->reset();
			$inner->addData($rec);
			$recs[] = $inner->show();
		}
		$outer->addTag("deliveries", implode("", $recs), false);
		return $outer->show();
	}

    /**
     * @return array|mixed|string|string[]
     * @throws Exception
     */
    function overnightDeliveries() {
		if (!$module = parent::getModule())
			return "";
		$outer = new Forms();
		$outer->init($this->m_dir.$module['outer_html']);
		$o_flds = $outer->buildForm($this->config->getFields($module['configuration']));
		$inner = new Forms();
		$inner->init($this->m_dir.$module['inner_html']);
		$flds = $inner->buildForm($this->config->getFields($module['configuration']."Row"));
		$pager = array_key_exists("pager",$_REQUEST) ? $_REQUEST["pager"] : 5;
		$module["rows"] = $pager;
		$module["limit"] = $pager;
		$srch = array(
			"a"=>"c2.service_type = 'D'",
			"b"=>"o.id = c1.order_id",
			"c"=>"ol.order_id = o.id",
			"d"=>"ol.custom_package = 'S'",
			"e"=>"p.id = ol.product_id",
			"f"=>"p.custom_same_day = 0",
			"g"=>"c2.order_id = c1.order_id",
			"h"=>"c2.service_type='D'",
			"i"=>"c1.service_type='P'",
			"j"=>"c1.completed = 1",
			"k"=>"ol.custom_package = 'S'",
			"l"=>"ol.deleted = 0",
			"m"=>sprintf("o.order_status & ~%d = 0", STATUS_PROCESSING | STATUS_SHIPPED),
			"n"=>"(isnull(d1.id) or d1.third_party=0)",
			"o"=>"(isnull(d2.id) or d2.third_party=0)",
			"p"=>"o.deleted = 0",
			"completed"=>"IF(c2.completed,DATE(c2.actual_date) = CURDATE(),DATE(c2.scheduled_date) <= CURDATE())"
		);
		if (!(count($_REQUEST) > 0 && array_key_exists(__FUNCTION__,$_REQUEST))) {
			$_REQUEST[__FUNCTION__] = 1;
			$_REQUEST["pager"] = 25;
			$_REQUEST["completed"] = 0;
			$_REQUEST["driver_id"] = "";
			$_REQUEST["product_id"] = "";
		}
		$outer->addData($_REQUEST);
		foreach($o_flds as $sk=>$sv) {
			$tmp = $outer->getData($sk);
			switch($sk) {
			case "completed":
				if ($tmp == 0)
					$srch["completed"] = sprintf("c2.completed = %d and DATE(c2.scheduled_date) <= CURDATE()", $tmp);
				else
					$srch["completed"] = sprintf("c2.completed = %d and DATE(c2.actual_date) = CURDATE()", $tmp);
				break;
			case "product_id":
			if (strlen($tmp) > 0)
					$srch["product_id"] = sprintf("p.id = %d ", $tmp);
				break;
			case "driver_id":
				if (strlen($tmp) > 0)
					$srch["driver_id"] = sprintf("c2.driver_id = %d", $tmp);
				break;
			default:
			}
		}
/*
			$pager = array_key_exists("pager",$_REQUEST) ? $_REQUEST["pager"] : 5;
			if (array_key_exists("driver_id",$_REQUEST) && strlen($_REQUEST["driver_id"]) > 0) {
				
			}
			if (array_key_exists("product_id",$_REQUEST) && $_REQUEST["product_id"] > 0) {
			}
			if (array_key_exists("completed",$_REQUEST) && strlen($_REQUEST["completed"]) > 0) {
			}
			$outer->addData($_REQUEST);
		}
*/
		$sql = sprintf("select o.custom_signature_required, o.custom_placed_by, ol.*, c1.id as pickup, c2.id as delivery, p.name as product_name, o.order_status & %d as needs_approval, p.custom_color_coding
from custom_delivery c1 left join drivers d1 on d1.id = c1.driver_id, orders o, order_lines ol, product p, custom_delivery c2 left join drivers d2 on d2.id = c2.driver_id
where %s", STATUS_NEEDS_APPROVAL, implode(" and ",array_values($srch)));
		$sql .= sprintf(" order by c2.driver_id>0, c2.completed, IF(c2.completed,c2.actual_date,c2.scheduled_date) limit %d", $outer->getData("pager"));
		//$pagination = $this->getPagination($sql,$module,$pager);
		//$outer->addTag("pagination",$pagination,false);
		$outer->setModule($module);
		$recs = array();
		$data = $this->fetchAllTest($sql);
		foreach($data as $rec) {
			if ($rec["delivery"] = $this->fetchSingleTest("select *, CURDATE() as td, DATE(actual_date) as ad, DATE(scheduled_date) as sd from custom_delivery where order_id = %d and service_type='D'",$rec["order_id"])) {
				$d = new DateTime($rec["delivery"]["scheduled_date"]);
				$et = $d->diff(new DateTime(date(DATE_ATOM)));
				$rec["delivery"]["et"] = (60*$et->h + $et->i)*($et->invert == 0 ? -1:1);
				if (!$rec["delivery"]["driver"] = $this->fetchSingleTest("select v.name as vehicle, m.* from members m, drivers d, custom_delivery c, vehicles v where c.order_id = %d and service_type='D' and d.id = c.driver_id and m.id = d.member_id and v.id = d.vehicle_id", $rec["order_id"]))
					$rec["delivery"]["driver"] = array("id"=>0);
			}
			$rec["delivery"]["address"] = $this->fetchSingleTest("select * from addresses where ownerid=%d and ownertype='order' and addresstype=%d", $rec["order_id"], ADDRESS_DELIVERY);
			$inner->reset();
			$rec["services"] = implode(", ", $this->fetchScalarAllTest("select p.name from product p, order_lines ol where ol.order_id = %d and ol.custom_package = 'A' and ol.deleted = 0 and p.id = ol.product_id and p.custom_special_requirement = 1", $rec["order_id"]));
			$inner->addData($rec);
			$recs[] = $inner->show();
		}
		$outer->addTag("deliveries", implode("", $recs), false);
		return $outer->show();
	}

    /**
     * @return array|mixed|string|string[]
     * @throws phpmailerException
     */
    function accountSearch() {
		if (!$module = parent::getModule())
			return "";
		$outer = new Forms();
		$outer->init($this->m_dir.$module['outer_html']);
		$outer->setModule($module);
		$flds = $outer->buildForm($this->config->getFields($module['configuration']));
		$inner = new Forms();
		$inner->init($this->m_dir.$module['inner_html']);
		$flds = $inner->buildForm($this->config->getFields($module['configuration']."Row"));
		$pager = 5;
		$module["rows"] = $pager;
		$module["limit"] = $pager;
		$m_id = $this->getUserInfo("id");
		$outer->getField("address_id")->addAttribute("sql",sprintf("select id, concat(line1,' ',city,' ',postalcode) from addresses where ownertype='member' and ownerid = %d order by 2", $m_id));
		$group = $this->fetchSingleTest("select mf.* from members_folders mf, members_by_folder mb where mb.member_id = %d and mf.id = mb.folder_id",$m_id);
		$g_id = $group["id"];	//$this->fetchScalarTest("select folder_id from members_by_folder where member_id = %d",$m_id);
		$m_services = $this->fetchAllTest("select c.id, p.name, c.product_id from product p, custom_member_product_options c, members_by_folder mf where p.id = c.product_id and c.member_id = mf.id and mf.member_id = %d and mf.folder_id = %d and isgroup = 0",$m_id,$g_id);
		$tmp = array(0);
		foreach($m_services as $key=>$value) {
			$tmp[] = $value["product_id"];
		}
		$g_services = $this->fetchAllTest("select c.id, p.name, c.product_id from product p, custom_member_product_options c where p.id = c.product_id and c.member_id = %d and isgroup = 1 and p.id not in (%s)",$g_id, implode(",",array_merge(array(0),$tmp)));
		$services = array_merge($m_services, $g_services);
		$result = array();
		foreach($services as $key=>$rec) {
			$result[$rec["id"]] = $rec["name"];
		}

		$group = $this->fetchSingleTest("select mf.* from members_folders mf, members_by_folder mb where mb.member_id = %d and mf.id = mb.folder_id",$m_id);
		$g_id = $group["id"];	//$this->fetchScalarTest("select folder_id from members_by_folder where member_id = %d",$m_id);
		$m_services = $this->fetchAllTest("select c.id, p.name, c.product_id from product p, custom_member_product_options c, members_by_folder mf where p.id = c.product_id and c.member_id = mf.id and mf.member_id = %d and mf.folder_id = %d and isgroup = 0",$m_id,$g_id);
		$tmp = array(0);
		foreach($m_services as $key=>$value) {
			$tmp[] = $value["product_id"];
		}
		$g_services = $this->fetchAllTest("select c.id, p.name, c.product_id from product p, custom_member_product_options c where p.id = c.product_id and c.member_id = %d and isgroup = 1 and p.id not in (%s)",$g_id, implode(",",array_merge(array(0),$tmp)));
		$services = array_merge($m_services, $g_services);
		$result = array();
		foreach($services as $key=>$rec) {
			$result[$rec["id"]] = $rec["name"];
		}
		$outer->getField("product_id")->setOptions(array_merge(array("0"=>"-All-"),$result));

		if( array_key_exists(__FUNCTION__,$_REQUEST) && $_REQUEST[__FUNCTION__] == 1) {
			$this->logMessage(__FUNCTION__,"in validation",1);
			$outer->addData($_REQUEST);
			$valid = $outer->validate();
			if ($valid) {
				$parms = array();
				$parms[] = sprintf("o.order_status & %d = %d", STATUS_PROCESSING, STATUS_PROCESSING);
				foreach($_REQUEST as $key=>$value) {
					switch($key) {
					case 'sd':
						if ($outer->getData($key) !="") {
							$parms[] = sprintf('date(c.scheduled_date) >= "%s"',$outer->getData($key));
						}
						break;
					case 'ed':
						if ($outer->getData($key) !="") {
							$parms[] = sprintf('date(c.scheduled_date) <= "%s"',$outer->getData($key));
						}
						break;
					case 'address_id':
						if ($outer->getData($key) !=0) {
							$parms[] = 'address_id';
						}
						break;
					case 'product_id':
						if ($outer->getData($key) !=0) {
							$parms[] = sprintf('o.id in (select o.id from orders o, order_lines ol, product p where o.member_id = %d and ol.order_id = o.id and p.id = ol.product_id and ol.custom_package = "S" and o.order_status & %d = %d and ol.deleted = 0)',$m_id, STATUS_PROCESSING, STATUS_PROCESSING);
						}
						break;
					case 'order_id':
						if ($outer->getData($key) !=0) {
							$parms[] = 'order_id';
						}
						break;
					default:
					}
				}
				if (count($parms) == 0) {
					$valid = false;
					$outer->addFormError("You must select some criteria");
				}
				if ($valid) {
					$parms[] = "o.id = c.order_id";
					$parms[] = sprintf("o.member_id = %d",$m_id);
					$sql = sprintf("select o.* from orders o, custom_delivery c where %s", implode(" and ", $parms));
					$recs = $this->fetchAllTest($sql);
				}
			}
		}
		return $outer->show();
	}

    /**
     * @return array|mixed|string|string[]
     * @throws phpmailerException
     */
    function recurring() {
		if (!$module = parent::getModule())
			return "";
		$outer = new Forms();
		if (!array_key_exists("r_opt",$_REQUEST)) {
			if ($this->checkArray("cart:recurring",$_SESSION)) {
				$_REQUEST = $_SESSION["cart"]["recurring"];
				$_REQUEST["custom_recurring"] = 1;
				$_REQUEST["r_opt"] = $_SESSION["cart"]["recurring"]["type"];
			}
			else
				return "";
		}
		$outer->addData($_REQUEST);
		$outer->init(sprintf("%s%s.html",$this->m_dir.$module['outer_html'],$_REQUEST["r_opt"]));
		$outer->addData(array('recurring[by_position]'=>0));
		$outer->setModule($module);
		$flds = $outer->buildForm($this->config->getFields(sprintf("%s%s",$module['configuration'],$_REQUEST["r_opt"])));
		$flds = $this->config->getFields($module['configuration']."Row");
		return $outer->show();
	}

    /**
     * @param $orderId
     * @return string
     * @throws phpmailerException
     */
    function getBillingType($orderId) {
		$o = $this->fetchSingleTest("select * from orders where id = %d", $orderId);
		$ret = "";
		switch($o["authorization_type"]) {
			case "PayPal":
			case "PayPal via PayFlow":
				$ret = "PayPal";
				break;
			case "PayFlow":
				$ret = "PayFlow";
				break;
			case "Other":
				$c = $this->fetchSingleTest("select * from members where id = %d", $o["member_id"]);
				if (!$c["custom_on_account"]) {
					$this->logMessage(__FUNCTION__, sprintf("order %d was has no payment method and is not a bill later account", $o["id"]), 1, true);
				}
				else $ret = "onAccount";
		}
		$this->logMessage(__FUNCTION__,sprintf("order %d is type [%s] from [%s]", $orderId, $ret, $o["authorization_type"]),2);
		return $ret;
	}

    /**
     * @param $orderId
     * @return void
     */
    function postRecurringOrder($orderId) {
	}

    /**
     * @param $orderId
     * @return void
     */
    function preRecurringOrder($orderId) {
	}

    /**
     * @return array|mixed|string|string[]
     * @throws SoapFault
     * @throws phpmailerException
     */
    function outOfZoneOptions() {
		if (!$module = parent::getModule())
			return "";
		$outer = new Forms();
		$outer->init($this->m_dir.$module["outer_html"]);
		$inner = new Forms();
		$inner->init($this->m_dir.$module["inner_html"]);
		$flds = $inner->buildForm($this->getFields($module["configuration"]."Row"));
		$flds = $outer->buildForm($this->getFields($module["configuration"]));
		$m_id = $this->getUserInfo("id");
		$result = array();
		$cart = Ecom::getCart();
		$data = array();
		if (!array_key_exists("optType",$_REQUEST)) {
			if (array_key_exists("quote",$cart)) {
				$data = $cart["quote"];
			}
		}
		else {
			$data = $_REQUEST;
		}
		if (!array_key_exists("optType",$data)) {
			return "";
		}
		if (count($data["prod"]) == 0) {
			$this->addEcomError("At least 1 item to ship is required");
		}
		else {
			$wt = 0;
			foreach($data["prod"] as $key=>$value) {
				foreach($value["dimensions"] as $sk=>$d) {
					$wt += $d["weight"];
				}
			}
			if ($wt < .1) {
				$this->addEcomError("Please enter a valid weight/quantity");
			}
			else {
				if ($data["optType"]=="S") {

					$dtForm = new Forms();
					$flds = $dtForm->buildForm($flds);
					$dtForm->addData($_REQUEST);
					$pu_dt = $dtForm->getData("pickup_datetime");
					$pu_dow = date("w", strtotime($pu_dt));
					$this->logMessage(__FUNCTION__,sprintf("pu_dt [%s] pu_dow [%s]", $pu_dt, $pu_dow),1);
					$super = $this->checkArray("mgmt:user:custom_super_user",$_SESSION) && $_SESSION["mgmt"]["user"]["custom_super_user"] == 1;
					$group = $this->fetchSingleTest("select mf.* from members_folders mf, members_by_folder mb where mb.member_id = %d and mf.id = mb.folder_id",$m_id);
					$g_id = $group["id"];
					$m_services = $this->fetchAllTest("select c.id, c.id as serviceType, p.name, c.product_id, p.custom_minimum_charge from product p, custom_member_product_options c, members_by_folder mf where p.id = c.product_id and c.member_id = mf.id and mf.member_id = %d and mf.folder_id = %d and isgroup = 0 and custom_same_day = 1 and custom_availability & %3\$d = %3\$d and unavailable = 0 and (admin_only = 0 or %4\$d = 1)",$m_id,$g_id, 2**$pu_dow, $super);
					$tmp = array(0);
					foreach($m_services as $key=>$value) {
						$tmp[] = $value["product_id"];
					}

					if (!$m_remove = $this->fetchScalarAllTest("select c.product_id from product p, custom_member_product_options c, members_by_folder mf where p.id = c.product_id and c.member_id = mf.id and mf.member_id = %d and mf.folder_id = %d and isgroup = 0 and custom_same_day = 1 and unavailable = 1",$m_id,$g_id))
						$m_remove = array("0"=>"0");
					$g_normal = $this->fetchAllTest("select c.id, c.id as serviceType, p.name, c.product_id, p.custom_minimum_charge from product p, custom_member_product_options c where p.id = c.product_id and c.member_id = %d and isgroup = 1 and p.id not in (%s) and custom_same_day = 1 and custom_availability & %3\$d = %3\$d and p.id not in (%4\$s) and (admin_only = 0 or %5\$d = 1) and c.out_of_zone_alternate = 0",$g_id, implode(",",array_merge(array(0),$tmp)), 2**$pu_dow, implode(", ", $m_remove), $super);
					$g_prod = $this->fetchScalarAllTest("select c.out_of_zone_alternate from product p, custom_member_product_options c where p.id = c.product_id and c.member_id = %d and isgroup = 1 and p.id not in (%s) and custom_same_day = 1 and custom_availability & %3\$d = %3\$d and p.id not in (%4\$s) and (admin_only = 0 or %5\$d = 1) and c.out_of_zone_alternate > 0",$g_id, implode(",",array_merge(array(0),$tmp)), 2**$pu_dow, implode(", ", $m_remove), $super);
					$g_abnormal = $this->fetchAllTest("select c.id, c.id as serviceType, p.name, c.product_id, p.custom_minimum_charge from product p, custom_member_product_options c where p.id = c.product_id and c.member_id = %d and isgroup = 1 and p.id not in (%s) and custom_same_day = 1 and custom_availability & %3\$d = %3\$d and p.id not in (%4\$s) and c.product_id in (%5\$s)",$g_id, implode(",",array_merge(array(0),$tmp)), 2**$pu_dow, implode(", ", $m_remove), implode(",",array_merge(array(0),$g_prod)));
					$g_temp = array_merge($g_normal, $g_abnormal);
					$g_services = array();
					foreach($g_temp as $sk=>$sv) {
						$g_services[$sv["id"]] = $sv;
					}
					$this->logMessage(__FUNCTION__,sprintf("^^^ g_normal [%s] g_prod [%s] g_abnormal [%s] g_services [%s]", print_r($g_normal,true), print_r($g_prod,true), print_r($g_abnormal,true), print_r($g_services,true)),1);
					//$g_services = $this->fetchAllTest("select c.id, c.id as serviceType, p.name, c.product_id, p.custom_minimum_charge from product p, custom_member_product_options c where p.id = c.product_id and c.member_id = %d and isgroup = 1 and p.id not in (%s) and custom_same_day = 1 and custom_availability & %3\$d = %3\$d and p.id not in (%4\$s) and (admin_only = 0 or %5\$d = 1)",$g_id, implode(",",array_merge(array(0),$tmp)), 2**$pu_dow, implode(", ", $m_remove), $super);
					$services = array();
					foreach(array_merge($m_services, $g_services) as $key=>$value) {
						$services[] = $value;
					}
					if (array_key_exists("prod",$_POST) && is_array($_POST["prod"])) {
						foreach($_POST["prod"] as $k=>$v) {
							$ct = 0;
							$wt = 0;
							foreach($v["dimensions"] as $sk=>$sv) {
								$ct += $sv["quantity"];
								$wt += $sv["weight"];
							}
							$_POST["prod"][$k]["quantity"] = $ct;
							$_POST["prod"][$k]["custom_weight"] = $wt;
						}
						//$_POST["prod"] = $_SESSION["prod"];
					}
//					if ($this->checkArray("addresses:pickup:id",$cart) && $this->checkArray("addresses:shipping:id", $cart) && $this->checkArray("quote:prod", $_SESSION)) {
					if ($this->checkArray("addresses:pickup:id",$cart) && $this->checkArray("addresses:shipping:id", $cart)) {
						$kjv = new KJV();
						$km = $kjv->getWalkingDistance($cart["addresses"]);
						$_SESSION["cart"]["header"]["km_calced"] = $km;
						$_SESSION["quote"] = $_POST;
						$dbg = $this->getDebug();
						foreach($services as $k=>$v) {
							$quote = $kjv->getPrice( $_SESSION["quote"]["prod"], $this->checkArray("quote:extras",$_SESSION) ? $_SESSION["quote"]["extras"] : array(), $cart["addresses"]["pickup"], $cart["addresses"]["shipping"], $v["id"], $_SESSION["quote"]["custom_weight_code"], $_SESSION["quote"]["custom_dimension_code"]);
							$services[$k]["total"] = $_SESSION["cart"]["header"]["total"];
						}
						unset($_SESSION["cart"]["header"]["km_calced"]);
					}
					usort($services,'customServiceSort');
					foreach($services as $key=>$rec) {
						$inner->addData($rec);
						$result[] = $inner->show();
					}
				}
				else {
					$quote = $data;
					$quote["wt"] = $this->fetchSingleTest("select * from code_lookups where id = %d",$quote["custom_weight_code"]);
					$quote["sz"] = $this->fetchSingleTest("select * from code_lookups where id = %d",$quote["custom_dimension_code"]);
					$cart = $this->getFedEx($cart,$quote);
					$this->logMessage(__FUNCTION__,sprintf("getFedEx returned cart [%s] quote [%s]",print_r($cart,true), print_r($quote,true)),3);
					if (array_key_exists("custom", $cart) && array_key_exists("ourRates", $cart["custom"])) {
						foreach($cart["custom"]["ourRates"] as $key=>$rec) {
							$rec["serviceType"] = $rec["member_id"] > 0 ? $rec["member_id"] : $rec["group_id"];
							$inner->addData($rec);
							$result[] = $inner->show();
						}
					}
					$_SESSION["cart"] = $cart;
				}
			}
		}
		if ($outer->hasTag("ecomErrors")) {
			$err = $this->showEcomMessages();
			if (strlen($err) > 0) {
				$outer->addTag('ecomErrors',$err,false);
				$outer->setData("reset",1);
			}
		}
		$outer->addTag("options",implode("",$result),false);
		return $outer->show();
	}

    /**
     * @return array|mixed|string|string[]
     * @throws phpmailerException
     */
    function addExtra() {
		if (!$module = $this->getModule())
			return "";
		$outer = new Forms();
		$outer->init($this->m_dir.$module["outer_html"]);
		$outer->setModule($module);
		$inner = new Forms();
		$inner->init($this->m_dir.$module["inner_html"]);
		$flds = $inner->buildForm($this->getFields($module["configuration"]));
		$outer->setData("extra",$inner->show());
		return $outer->show();
	}

    /**
     * @param $user
     * @return void
     * @throws phpmailerException
     */
    function postLogin($user) {
		$cart = Ecom::getCart();
		$cart["addresses"] = array('shipping'=>array('id'=>0,'country_id'=>0,'province_id'=>0), 'pickup'=>array('id'=>0,'country_id'=>0,'province_id'=>0), 'billing'=>$this->fetchSingleTest("select * from addresses where ownertype='member' and ownerid = %d and addresstype=%d", $this->getUserInfo("id", ADDRESS_BILLING)));
		$_SESSION["cart"] = $cart;
	}

    /**
     * @return array|mixed|string|string[]
     * @throws phpmailerException
     */
    function messageing() {
		if (!$module = $this->getModule())
			return "";
		$outer = new Forms();
		$outer->init($this->m_dir.$module["outer_html"]);
		$outer->setModule($module);
		$inner = new Forms();
		$inner->init($this->m_dir.$module["inner_html"]);
		$flds = $inner->buildForm($this->getFields($module["configuration"]));
		$recs = $this->fetchAllTest("select cd.*, p.name, ol.custom_package, timediff(CURRENT_TIMESTAMP(),ack_requested) as delay from custom_delivery cd, drivers d, members m, order_lines ol, product p 
where cd.driver_id = d.id and m.id = %d and d.member_id = m.id and cd.ack_status = %d and ol.order_id = cd.order_id and p.id = ol.product_id and ol.custom_package = 'S' and (scheduled_date <= '%s 23:59:59' or scheduled_date < '%s 08:00:00') and cd.order_id not in (select id from orders where order_status & %d = %d) and ol.deleted = 0 order by cd.id", 
$this->getUserInfo("id"), ACK_REQUEST, date("Y-m-d"), date("Y-m-d", strtotime("today + 1 day")), STATUS_CANCELLED , STATUS_CANCELLED );
		$msg = array();
		$ids = array();
		if (array_key_exists(__FUNCTION__,$_REQUEST) && array_key_exists("accept_all",$_REQUEST) && array_key_exists("d_id",$_REQUEST)) {
			foreach($recs as $key=>$rec) {
				$this->execute(sprintf("update custom_delivery set ack_status=%d, driver_message='%s' where id = %d", ACK_ACKNOWLEDGE, "Auto Accepted", $rec["id"]));
			}
		}
		else {
			foreach($recs as $key=>$rec) {
				$inner->addData($rec);
				$ids[] = $rec["id"];
				$msg[] = $inner->show();
			}
		}
		if (array_key_exists("ids",$_REQUEST) && implode(",",$ids) == $_REQUEST["ids"]) {
			$outer->setData("status",0);
			$this->logMessage(__FUNCTION__,sprintf("set status to 0"),1);
			$outer->init($this->m_dir.$module["parm1"]);
		}
		else {
			$outer->setData("status",1);
			$this->logMessage(__FUNCTION__,sprintf("set status to 1"),1);
			$outer->setData("messages",implode("",$msg));
		}
		$outer->setData("ids",implode(",",$ids));
		return $outer->show();
	}

    /**
     * @return array|mixed|string|string[]
     * @throws phpmailerException
     */
    function acknowledgement() {
		if (!$module = $this->getModule())
			return "";
		$outer = new Forms();
		$outer->init($this->m_dir.$module["outer_html"]);
		$outer->setModule($module);
		$flds = $outer->buildForm($this->getFields($module["configuration"]));
		$a_id = array_key_exists("a_id",$_REQUEST) ? $_REQUEST["a_id"] : 0;
		if ($rec = $this->fetchSingleTest("select cd.*, p.code, p.name from custom_delivery cd, drivers d, product p, order_lines ol where cd.id = %d and d.id = cd.driver_id and d.member_id = %d and ol.order_id = cd.order_id and ol.custom_package='S' and p.id = ol.product_id and ol.deleted = 0", $a_id, $this->getUserInfo("id"))) {
			$address = $this->fetchSingleTest("select * from addresses where ownerid = %d and ownertype='order' and addresstype=%d", $rec["order_id"], $rec["service_type"] == "P" ? ADDRESS_PICKUP : ADDRESS_DELIVERY);
			$now = new DateTime();
			$c1 = new DateTime($rec["ack_requested"]);
			$c2 = $now->diff($c1);
			$c3 = $c2->format("%h:%i");
			$rec["created_delay"] = $c2->format("%h:%i");

			$c1 = new DateTime($rec["scheduled_date"]);
			$c2 = $now->diff($c1);
			$c3 = $c2->format("%h:%i");
			$rec["scheduled_delay"] = $c2->format("%h:%i");

			$this->logMessage(__FUNCTION__, sprintf( "now [%s] c1 [%s] c2 [%s] diff [%s]", print_r($now,true), print_r($c1,true), print_r($c2,true), print_r($c3,true)), 1);
			$rec["address"] = Address::formatData($address);
			//$rec["prev_message"] = $rec["message"];
			//$rec["prev_status"] = $rec["status"];
			//$rec["message"] = "";
			$outer->addData($rec);
		}
		if (array_key_exists(__FUNCTION__,$_POST)) {
			$outer->addData($_POST);
			$valid = $outer->validate();
			if ($valid) {
				$values = array();
				$values["ack_status"] = $outer->getData("ack_status");
				$values["driver_message"] = $outer->getData("driver_message");
				$stmt = $this->prepare(sprintf("update custom_delivery set %s=? where id = %d",implode("=?, ",array_keys($values)),$a_id));
				$stmt->bindParams(array_merge(array(str_repeat("s",count($values))),array_values($values)));
				if ($stmt->execute()) {
					$outer->init($this->m_dir.$module["parm1"]);
				}
				if ($outer->getData("new_status")==1) {
					$values = array("delivery_id"=>$rec["delivery_id"], "created"=>date(DATE_ATOM), "author_type"=>"D", "response_type"=>"O", "driver_id"=>$rec["driver_id"], "message"=>$outer->getData("response"));
					$stmt = $this->prepare(sprintf("insert into custom_delivery_acknowledgement(%s) values(?%s)",implode(", ",array_keys($values)),str_repeat(", ?",count($values)-1)));
					$stmt->bindParams(array_merge(array(str_repeat("s",count($values))),array_values($values)));
					$stmt->execute();
				}
			}
		}
		return $outer->show();
	}

    /**
     * @return array|mixed|string|string[]
     * @throws phpmailerException
     */
    function dispatchAcks() {
		if (!$module = $this->getModule())
			return "";
		$outer = new Forms();
		$outer->init($this->m_dir.$module["outer_html"]);
		$outer->setModule($module);
		$flds = $outer->buildForm($this->getFields($module["configuration"]));
		if (array_key_exists(__FUNCTION__,$_REQUEST)) {
			$outer->addData($_REQUEST);
		}
		$inner = new Forms();
		$inner->init($this->m_dir.$module["inner_html"]);
		$inner->setModule($module);
		$flds = $inner->buildForm($this->getFields($module["configuration"]));
		$sort = "cd.scheduled_date";
		$sql = sprintf("select cd.*, m.firstname, m.lastname, p.name, p.custom_same_day, timediff(CURRENT_TIMESTAMP, ack_requested) as delay from custom_delivery cd, drivers d, members m, order_lines ol, product p, orders o where d.id = cd.driver_id and m.id = d.member_id and cd.ack_status != %d and cd.actual_date = '0000-00-00 00:00:00' and ol.order_id = cd.order_id and ol.custom_package = 'S' and p.id = ol.product_id and DATE(scheduled_date) <= CURDATE() and o.id = cd.order_id and o.order_status_processing and d.third_party =0 and ol.deleted = 0 order by cd.ack_status desc, %s", ACK_ACKNOWLEDGE, $sort);
		$pagination = $this->getPagination($sql,$module,$recordCount);
		$recs = $this->fetchAllTest($sql);
		$rows = array();
		foreach($recs as $k=>$rec) {
			$inner->addData($rec);
			$rows[] = $inner->show();
		}
		$outer->setData("services",implode("",$rows));
		$outer->setData("pagination",$pagination);
		return $outer->show();
	}

    /**
     * @return array|mixed|string|string[]
     * @throws phpmailerException
     */
    function liveExceptions() {
		if (!$module = parent::getModule())
			return "";
		$outer = new Forms();
		$outer->init($this->m_dir.$module['outer_html']);
		$flds = $outer->buildForm($this->config->getFields($module['configuration']));
		$inner = new Forms();
		$inner->init($this->m_dir.$module['inner_html']);
		$flds = $inner->buildForm($this->config->getFields($module['configuration']."Row"));
		$pager = array_key_exists("pager",$_REQUEST) ? $_REQUEST["pager"] : 5;
		$module["rows"] = $pager;
		$module["limit"] = $pager;
		$sql = sprintf("select distinct cd.*, p.name as product, m.firstname, m.lastname, ss.name, time(cd.scheduled_date) 
from custom_delivery cd left join drivers d on d.id = cd.driver_id left join members m on d.member_id = m.id 
left join order_lines os on os.order_id = cd.order_id left join product ss on ss.id = os.product_id and ss.custom_special_requirement = 1, order_lines ol, product p 
where date(scheduled_date) >= '%s' and ol.order_id = cd.order_id and ol.custom_package = 'S' and p.id = ol.product_id 
and ((!isnull(ss.name)) or time(scheduled_date) < '09:00:00' or time(scheduled_date) > '17:00:00') and ol.deleted = 0 order by scheduled_date", date("Y-m-d"));


$sql = sprintf("select cd.*, p.name from custom_delivery cd, product p, order_lines ol where 
ol.order_id = cd.order_id and p.id = ol.product_id and ((time(cd.scheduled_date) < '09:00:00' or time(scheduled_date) > '17:00:00') or 
(cd.order_id in (select order_id from order_lines ol1, product p1 where ol1.custom_package = 'A' and p1.id = ol1.product_id and p1.custom_special_requirement = 1 and ol1.deleted = 0))) and 
date(cd.scheduled_date) >= '%s' and ol.custom_package = 'S' and ol.deleted = 0 order by scheduled_date", date("Y-m-d"));

		if (count($_REQUEST) > 0 && array_key_exists(__FUNCTION__,$_REQUEST)) {
			$pager = array_key_exists("pager",$_REQUEST) ? $_REQUEST["pager"] : 5;
			$outer->addData($_REQUEST);
		}
		$pagination = $this->getPagination($sql,$module,$pager);
		$outer->addTag("pagination",$pagination,false);
		$outer->setModule($module);
		$recs = array();
		$data = $this->fetchAllTest($sql);
		foreach($data as $rec) {
			$inner->reset();
			$rec["address"] = Address::formatData($this->fetchSingleTest("select * from addresses where ownertype='order' and ownerid=%d and addresstype = %d", $rec["order_id"], $rec["service_type"] == "P" ? ADDRESS_PICKUP : ADDRESS_DELIVERY));
			if ($services = $this->fetchScalarAllTest("select name from product p, order_lines ol where ol.order_id = %d and ol.custom_package = 'A' and p.id = ol.product_id and p.custom_special_requirement = 1 and ol.deleted = 0", $rec["order_id"]))
				$rec["services"] = implode(", ",$services);
			$rec["driver"] = $this->fetchSingleTest("select * from members m, drivers d where d.id = %d and m.id = d.member_id", $rec["driver_id"]);
			$inner->addData($rec);
			$recs[] = $inner->show();
		}
		$outer->addTag("deliveries", implode("", $recs), false);
		return $outer->show();
	}

    /**
     * @return array|mixed|string|string[]
     * @throws phpmailerException
     */
    function invoices() {
		if (!$module = parent::getModule())
			return "";
		$outer = new Forms();
		$outer->init($this->m_dir.$module['outer_html']);
		$flds = $outer->buildForm($this->config->getFields($module['configuration']));
		$outer->setModule($module);
		$inner = new Forms();
		$inner->init($this->m_dir.$module['inner_html']);
		$flds = $inner->buildForm($this->config->getFields($module['configuration']."Row"));
		$emails = array();
		if (count($_REQUEST) > 0 && array_key_exists("email",$_REQUEST) && is_array($_REQUEST["email"]) && count($_REQUEST["email"]) > 0) {
			$outer->addData($_REQUEST);
			$valid = $outer->validate();
			if ($valid) {
				$outer = $this->emailInvoices($_REQUEST, $module, $outer);
			}
		}
		$sql = sprintf("select qb.*, count(o.id) as ct, sum(o.total) as total from qb_export qb, orders o, qb_export_dtl qbd where o.deleted = 0 and qb.member_id = %d and qbd.qb_export_id = qb.id and o.id = qbd.order_id and o.order_status & %d = 0 group by qb.id order by created desc", $this->getUserInfo("id"), STATUS_CANCELLED );
		$pagination = $this->getPagination($sql,$module,$recordCount);
		$outer->setData("pagination",$pagination);
		$invoices = $this->fetchAllTest($sql);
		$rows = array();
		foreach($invoices as $k=>$v) {
			$v["email"] = array($v["id"] => array_key_exists($v["id"],$emails) ? 1:0);
			$inner->addData($v);
			$inner->getField("email")->addAttribute("name",sprintf("email[%d]",$v["id"]));
			$inner->setData(sprintf("email[%d]",$v["id"]),array_key_exists($v["id"],$emails) ? 1:0);
			$rows[] = $inner->show();
		}
		$outer->setData("rows",implode("",$rows));
		return $outer->show();
	}

    /**
     * @return array|mixed|string|string[]
     * @throws phpmailerException
     */
    function invoiceDetails() {
		if (!$module = parent::getModule())
			return "";
		$outer = new Forms();
		$outer->init($this->m_dir.$module['outer_html']);
		$flds = $outer->buildForm($this->config->getFields($module['configuration']));
		$outer->setModule($module);
		$inner = new Forms();
		$inner->init($this->m_dir.$module['inner_html']);
		$flds = $inner->buildForm($this->config->getFields($module['configuration']."Row"));
		$outer->addData($_REQUEST);
		if ($inv = $this->fetchSingleTest('select qb.* from qb_export qb, members m where qb.id = %d and m.id = qb.member_id and (m.id = %2$d or m.custom_parent_org = %2$d)', array_key_exists("i_id",$_REQUEST) ? $_REQUEST["i_id"] : 0,$this->getUserInfo("id"))) {
			$outer->addData($inv);
			$sql = sprintf("select qb.*, p.name, o.total, o.order_date, cd1.actual_date as pickup_date, cd2.actual_date as delivery_date, o.member_id from qb_export_dtl qb, orders o left join custom_delivery cd1 on cd1.order_id = o.id and cd1.service_type ='P' left join custom_delivery cd2 on cd2.order_id = o.id and cd2.service_type ='D', order_lines ol, product p where qb_export_id = %d and o.id = qb.order_id and ol.order_id = o.id and ol.custom_package='S' and p.id = ol.product_id and ol.deleted = 0 and o.order_status & %d = 0 and o.deleted = 0 order by qb.id", $inv["id"], STATUS_CANCELLED );
			$pagination = $this->getPagination($sql,$module,$recordCount);
			$outer->setData("pagination",$pagination);
			$invoices = $this->fetchAllTest($sql);
			$rows = array();
			foreach($invoices as $k=>$v) {
				$inner->addData($v);
				$rows[] = $inner->show();
			}
			$outer->setData("rows",implode("",$rows));
		}
		return $outer->show();
	}

    /**
     * @param $saveAsFile
     * @param $filename
     * @return string|void
     * @throws \Pdfcrowd\Error
     * @throws phpmailerException
     */
    function invoicePDF($saveAsFile = false, $filename = "" ) {
		if (!$module = parent::getModule())
			return "";
		set_time_limit(10*60);
		$outer = new Forms();
		$outer->init($this->m_dir.$module['outer_html']);
		$flds = $outer->buildForm($this->config->getFields($module['configuration']));
		$outer->setModule($module);
		$inner = new Forms();
		$inner->init($this->m_dir.$module['inner_html']);
		$flds = $inner->buildForm($this->config->getFields($module['configuration']."Row"));
		$i_id = array_key_exists("i_id",$_REQUEST) ? $_REQUEST["i_id"] : 0;
		$r_id = array_key_exists("r_id",$_REQUEST) ? $_REQUEST["r_id"] : 0;
		//$inv = $this->fetchSingleTest("select *, invoice_amount+tax_amount as i_total, invoice_amount + tax_amount - paid_amount as balance from qb_export where id = %d and member_id = %d", $i_id,$this->getUserInfo("id"));
		if ($r_id == 0) {
			$inv = $this->fetchSingleTest(sprintf('select e.*, sum(o.total) as i_total, sum(o.total) - sum(coalesce(a.authorization_amount,0)) as balance 
from qb_export e, qb_export_dtl d, orders o left join order_authorization a on a.order_id = o.id, members m
where o.deleted = 0 and e.id = %d and m.id = e.member_id and (m.id = %2$d or m.custom_parent_org = %2$d) and d.qb_export_id = e.id and o.id = d.order_id and o.order_status & %3$d = 0', $i_id,$this->getUserInfo("id"), STATUS_CANCELLED));
		}
		else {
			$inv = $this->fetchSingleTest(sprintf('select e.*, sum(o.total) as i_total, sum(o.total) - sum(coalesce(a.authorization_amount,0)) as balance 
from qb_export e, qb_export_dtl d, orders o left join order_authorization a on a.order_id = o.id, members m
where o.deleted = 0 and e.id = %d and rand_id = %d and m.id = e.member_id and d.qb_export_id = e.id and o.id = d.order_id and o.order_status & %3$d = 0', $i_id, $r_id, STATUS_CANCELLED));
		}
		$outer->addData($inv);

		$details = new Forms();
		$details->init($this->m_dir.$module['parm1']);
		$flds = $details->buildForm($this->config->getFields($module['configuration']."Detail"));
		$sql = sprintf("select qb.*, p.name, o.total, o.order_date, cd1.actual_date as pickup_date, cd2.actual_date as delivery_date from qb_export_dtl qb, orders o, order_lines ol, product p, custom_delivery cd1, custom_delivery cd2 where qb_export_id > 0 and qb_export_id = %d and o.id = qb.order_id and ol.order_id = o.id and ol.custom_package='S' and p.id = ol.product_id and cd1.order_id = o.id and cd1.service_type ='P' and cd2.order_id = o.id and cd2.service_type ='D' and ol.deleted = 0 order by o.id", $inv["id"]);
		$sql = sprintf("select qb.*, p.name, o.total, o.order_date, cd1.actual_date as pickup_date, cd2.actual_date as delivery_date from qb_export_dtl qb, orders o left join custom_delivery cd1 on cd1.order_id = o.id and cd1.service_type ='P' left join custom_delivery cd2 on cd2.order_id = o.id and cd2.service_type ='D', order_lines ol, product p where qb_export_id > 0 and qb_export_id = %d and o.id = qb.order_id and ol.order_id = o.id and ol.custom_package='S' and p.id = ol.product_id and ol.deleted = 0 and o.id = qb.order_id and o.order_status & %d = 0 and o.deleted = 0 order by o.id", $inv["id"], STATUS_CANCELLED );
		$recs = $this->fetchAllTest($sql);
		$rows = array();
		$ct = 0;
		$taxes = 0;
		$total = 0;
		$subtotal = 0;
		foreach($recs as $k=>$v) {
			if (!$address = $this->fetchSingleTest("select * from addresses where ownertype='order' and ownerid = %d and addresstype=%d", $v["order_id"], ADDRESS_PICKUP ))
				$this->logMessage(__FUNCTION__,sprintf("missing pickup address for [%s]", print_r($rec,true)),1,true);
			else
				$v["pickup"] = Address::formatData($address);
			if (!$address = $this->fetchSingleTest("select * from addresses where ownertype='order' and ownerid = %d and addresstype=%d", $v["order_id"], ADDRESS_DELIVERY ))
				$this->logMessage(__FUNCTION__,sprintf("missing delivery address for [%s]", print_r($rec,true)),1,true);
			else
				$v["delivery"] = Address::formatData($address);
			$v["pu"] = $this->fetchSingleTest("select * from custom_delivery where order_id = %d and service_type = 'P'", $v["order_id"]);
			$v["del"] = $this->fetchSingleTest("select * from custom_delivery where order_id = %d and service_type = 'D'", $v["order_id"]);
			$v["order"] = $this->fetchSingleTest("select o.*, o.total - o.authorization_amount as balance, coalesce(a.authorization_amount,0) as paid_amount from orders o left join order_authorization a on a.order_id = o.id and a.authorization_type='D' and a.authorization_success = 1 where o.id = %d", $v["order_id"]);

			$inner->addData($v);
			$dtls = $this->fetchAllTest("select ol.*, p.name, p.subtitle from order_lines ol, product p where order_id = %d and p.id = ol.product_id and ol.deleted = 0 and custom_package != 'P' and (value > .01 or custom_package = 'S') order by line_id", $v["order_id"]);
			$dtl = array();
			foreach($dtls as $sk=>$sv) {
				if ($sv["product_id"] == FEDEX_RECALC) {
					$sv["dims"] = $this->fetchSingleTest("select code as wt_code, sum(weight) as wt, sum(quantity) as qty from order_lines_dimensions old, code_lookups cl where old.order_id = %d and old.line_id = %d and cl.id = %d", $sv["order_id"], $sv["line_id"], $v["order"]["custom_weight_code"]);
				}
				$details->addData($sv);
				$dtl[] = $details->show();
			}
			//$this->logMessage(__FUNCTION__,sprintf("details [%s] inner [%s]", print_r($dtl,true), print_r($details,true)),1);
			$inner->setData("lines",implode("",$dtl));
			$subtotal += $v["order"]["value"];
			$ct += 1;
			$taxes += $v["order"]["taxes"];
			$total += $v["order"]["total"];
			$rows[] = $inner->show();

		}
		$this->logMessage(__FUNCTION__,sprintf("rows [%s]", print_r($rows,true)),3);
		$inv["member"] = $this->fetchSingleTest("select * from members m where m.id = %d", $inv["member_id"]);
		if (!$address = $this->fetchSingleTest("select a.* from addresses a, code_lookups cl where a.ownertype='member' and a.ownerid = %d and cl.id = a.addresstype and cl.id = %d and a.deleted = 0 order by a.id limit 1", $inv["member_id"], ADDRESS_COMPANY))
			$this->logMessage(__FUNCTION__,sprintf("missing member address from [%s]", print_r($inv,true)),1,true);
		else
			$inv["address"] = Address::formatData($address);
		$outer->addData($inv);
		$outer->setData("count",$ct);
		$outer->setData("subtotal",$subtotal);
		$outer->setData("taxes",$taxes);
		$outer->setData("total",$total);
		$outer->setData("orders",implode("",$rows));
		$html = $outer->show();
		$this->logMessage(__FUNCTION__,sprintf("html [%s]", $html),1);
		try {
			// create the API client instance
			$client = new \Pdfcrowd\HtmlToPdfClient($GLOBALS["pdfcrowd"]["user"], $GLOBALS["pdfcrowd"]["token"]);
			// run the conversion and write the result to a file
			if ($saveAsFile) {
				$tmp = $client->convertStringToFile($html, $filename);
				return $tmp;
			}
			else {
				ob_clean();
				$pdf = $client->convertString($html);
				header('Content-Type: application/pdf');
				header('Cache-Control: no-cache');
				header('Accept-Ranges: none');
				header(sprintf('Content-Disposition: filename="%s"',sprintf('invoice-%d.pdf',$outer->getData("qb_invoice_id"))));
				echo $pdf;
				exit;
			}
		}
		catch(\Pdfcrowd\Error $why) {
			// report the error
			error_log("Pdfcrowd Error: {$why}\n");
			// rethrow or handle the exception
			throw $why;
		}
/*
		$pdf->writeHTML($html, true, false, true, false, '');
		$pdf->lastPage();
		try {
			if ($saveAsFile) {
				return $pdf->Output($filename, 'F');
			}
			else {
				ob_clean();
				$pdf->Output(sprintf('invoice-%d.pdf',$outer->getData("qb_invoice_id")), 'I');
			}
		}
		catch(Exception $err) {
			return print_r($err,true);
		}
*/
	}

    /**
     * @return array|mixed|string|string[]
     * @throws phpmailerException
     */
    function ytdStats() {
		if (!$module = parent::getModule())
			return "";
		$outer = new Forms();
		$outer->init($this->m_dir.$module['outer_html']);
		$flds = $outer->buildForm($this->config->getFields($module['configuration']));
		$outer->setModule($module);
		$id = $this->getUserInfo("id");
		$ct = $this->fetchScalarTest("select count(0) from orders where member_id = %d and order_status & %d != 0 and year(order_date) = %d", $id, STATUS_PROCESSING | STATUS_SHIPPED, date("Y"));
		$completed = $this->fetchScalarTest("select count(o.id) from orders o, custom_delivery d where o.member_id = %d and d.completed = 1 and d.order_id = o.id and d.service_type='D'", $id);
		$late = $this->fetchScalarTest("select count(order_id) from orders o, custom_delivery d where o.member_id = %d and d.completed = 1 and d.order_id = o.id and d.actual_date < d.scheduled_date + INTERVAL 15 MINUTE and d.service_type='D'", $id);
		$percent = (int) ($completed > 0 ? $late/$completed*100 : 0);
		$cost = $this->fetchScalarTest("select sum(total) from orders where member_id = %d and order_status & %d != 0 and year(order_date) = %d", $id, STATUS_PROCESSING | STATUS_SHIPPED, date("Y"));
		$most_used = $this->fetchScalarAllTest("select p.name as name, count(p.id) as ct from orders o, product p, order_lines ol 
where o.member_id = %d and o.order_status & %d != 0 and year(o.order_date) = %d
and ol.order_id = o.id and p.id = ol.product_id
and ol.custom_package = 'S' and ol.deleted = 0
group by p.name
order by count(p.id) desc limit 1", $id, STATUS_PROCESSING | STATUS_SHIPPED, date("Y"));
		$this->logMessage(__FUNCTION__,sprintf("outer [%s] most_used [%s]", print_r($outer,true), print_r($most_used,true)),1);
		$outer->addData(array("count"=>$ct,"percent"=>$percent,"cost"=>$this->my_money_format($cost),"most_used"=>is_array($most_used) && count($most_used) > 0 ? $most_used[0]:""));
		return $outer->show();
	}

    /**
     * @return array|mixed|string|string[]
     * @throws phpmailerException
     */
    function mobileDetails() {
		if (!$module = parent::getModule())
			return "";
		$outer = new Forms();
		$outer->init($this->m_dir.$module['outer_html']);
		$flds = $outer->buildForm($this->config->getFields($module['configuration']));
		$outer->setModule($module);
		$c_id = array_key_exists("c_id",$_REQUEST) ? $_REQUEST["c_id"] : 0;
		if ($action = $this->fetchSingleTest("select c.*, o.member_id from custom_delivery c, orders o where c.id = %d and o.id = c.order_id", $c_id)) {
			if ($addr = $this->fetchSingleTest("select a.*, cd.scheduled_date from addresses a, custom_delivery cd where a.ownertype='order' and a.ownerid = %d and a.addresstype='%s' and cd.order_id = a.ownerid and cd.service_type='P'", $action["order_id"], ADDRESS_PICKUP))
				$action["pickup"] = Address::formatData($addr);
			else $action["pickup"] = array();
			if ($addr = $this->fetchSingleTest("select a.*, cd.scheduled_date from addresses a, custom_delivery cd where a.ownertype='order' and a.ownerid = %d and a.addresstype='%s' and cd.order_id = a.ownerid and cd.service_type='D'", $action["order_id"], ADDRESS_DELIVERY))
				$action["delivery"] = Address::formatData($addr);
			else $action["delivery"] = array();
			if ($action["service_type"]=="P")
				$action["action"] = $action["pickup"];
			else
				$action["action"] = $action["delivery"];

			if ($action["action"]["ownerid"] != $action["member_id"]) {
				$action["client"] = $this->fetchSingleTest("select m.*, a.phone1, a.email from members m left join addresses a on a.ownertype='member' and a.ownerid = m.id and a.addresstype = %d where m.id = %d", ADDRESS_COMPANY, $action["member_id"]);
				$action["showClient"] = $action["client"]["company"] != $action["action"]["company"];
			}
			if ($action["service_type"] == "D") {
				$action["pu_action"] = $this->fetchSingleTest("select * from custom_delivery where order_id = %d and service_type='P'", $action["order_id"]);
			}
			$outer->addData($action);
		}
		$this->logMessage(__FUNCTION__,sprintf("outer [%s]", print_r($outer,true)),1);
		return $outer->show();
	}

    /**
     * @return array|mixed|string|string[]
     * @throws phpmailerException
     */
    function customOrderHistory() {
		if (!$module = $this->getModule())
			return "";
		$outer = new Forms();
		$outer->setModule($module);
		$outer->init($this->m_dir.$module['outer_html']);
		$user = array_key_exists('user',$_SESSION) && is_array($_SESSION['user']) ? $_SESSION['user']['info']['id'] : 0;
		if ($this->hasOption('usePassed') && array_key_exists('m_id',$_REQUEST) && $_REQUEST['m_id'] > 0) {
			$user = $_REQUEST['m_id'];
			$this->logMessage('orderHistory',sprintf('user superceded with usePassed [%d]',$user),2);
		}
		$where = sprintf('cd.order_id = o.id and cd.service_type = "P" and ((o.order_status & %d) = 0) and o.deleted = 0 ', STATUS_RECURRING);
		if ($this->hasOption('useDates')) {
			if (array_key_exists('d_from',$_REQUEST) && strlen($_REQUEST['d_from']) > 0) {
				$where .= sprintf('and order_date >= "%s 00:00:00" ',date('Y-m-d',strtotime($_REQUEST['d_from'])));
			}
			if (array_key_exists('d_to',$_REQUEST) && strlen($_REQUEST['d_to']) > 0) {
				$where .= sprintf('and order_date <= "%s 23:59:59" ',date('Y-m-d',strtotime($_REQUEST['d_to'])));
			}
		}
		if ($this->hasOption('where_clause') && strlen($this->getOption('where_clause')) > 0)
			$where .= " and ".$this->getOption('where_clause');
		$sql = sprintf('select o.*, cd.scheduled_date from orders o, custom_delivery cd where %s and o.member_id = %s order by cd.scheduled_date desc',$where,$user);
		$pagination = $this->getPagination($sql,$module,$recordCount);
		$orders = $this->fetchAllTest($sql);
		$return = array();
		$inner = new Forms();
		$inner->init($this->m_dir.$module['inner_html']);
		foreach($orders as $key=>$order) {
			$order["pu_address"] = Address::formatData($this->fetchSingleTest("select * from addresses where ownertype='order' and ownerid=%d and addresstype=%d limit 1", $order["id"], ADDRESS_PICKUP));
			$order["del_address"] = Address::formatData($this->fetchSingleTest("select * from addresses where ownertype='order' and ownerid=%d and addresstype=%d limit 1", $order["id"], ADDRESS_DELIVERY));
			$order["service"] = $this->fetchSingleTest("select * from product p, order_lines od where od.order_id = %d and p.id = od.product_id and od.custom_package='S' and od.deleted = 0", $order["id"]);
			$inner->reset();
			$inner->addData($this->formatOrder($order));
			$subdata = $this->subForms($this->m_module['fetemplate_id'],null,array("o_id"=>$order["id"]),'inner');
			$this->logMessage(__FUNCTION__,sprintf('subforms [%s]',print_r($subdata,true)),3);
			foreach($subdata as $key=>$value) {
				$inner->addTag($key,$value,false);
			}
			$return[] = $inner->show();
		}
		$outer->addTag('orders',implode('',$return),false);
		$outer->addData(array('recordCount'=>count($orders)));
		if ($tmp = $this->fetchSingleTest('select * from members where id = %d',$user))
			$outer->addData($tmp);
		$subdata = $this->subForms($this->m_module['fetemplate_id'],null,array(),'outer');
		$this->logMessage(__FUNCTION__,sprintf('subforms [%s]',print_r($subdata,true)),3);
		foreach($subdata as $key=>$value) {
			$outer->addTag($key,$value,false);
		}
		$outer->addTag('pagination',$pagination,false);
		return $outer->show();
	}

    /**
     * @return array|mixed|string|string[]
     * @throws phpmailerException
     */
    function simulateDriver() {
		if (!$module = $this->getModule())
			return "";
		if ($this->getUserInfo("custom_super_user") > 0) {
			if (!array_key_exists("mgmt",$_SESSION))
				$_SESSION["mgmt"]["user"] = $_SESSION["user"]["info"];
		}
		$outer = new Forms();
		$outer->setModule($module);
		$outer->init($this->m_dir.$module['outer_html']);
		$flds = $outer->buildForm($this->config->getFields($module['configuration']));
		if ($rec = $this->fetchSingleTest("select * from members where deleted = 0 and enabled = 1 and id = %d", $this->getUserInfo("id")))
			$outer->addData($rec);
		if (count($_POST) > 0 && array_key_exists(__FUNCTION__,$_POST)) {
			$outer->addData($_POST);
			if ($outer->validate()) {
				if ($rec = $this->fetchSingleTest("select * from members where id = %d and enabled = 1 and deleted = 0", $outer->getData("member_id"))) {
					$this->logMeIn($rec["username"], $rec["password"],0,$rec["id"]);
					$outer->init($this->m_dir.$module["parm1"]);
				}
			}
		}
		$this->logMessage(__FUNCTION__,sprintf("outer [%s]", print_r($outer,true)), 1);
		return $outer->show();
	}

    /**
     * @return array|mixed|string|string[]
     * @throws phpmailerException
     */
    function billingReport() {
		if (!$module = $this->getModule())
			return "";
		$outer = new Forms();
		$outer->setModule($module);
		$outer->init($this->m_dir.$module['outer_html']);
		$flds = $outer->buildForm($this->config->getFields($module['configuration']));
		$inner = new Forms();
		$inner->init($this->m_dir.$module['inner_html']);
		$flds = $inner->buildForm($this->config->getFields($module['configuration']."Row"));
		$totals = array("taxable"=>0,"nontaxable"=>0,"taxes"=>0,"total"=>0,"paid"=>0,"balance"=>0);
		$outer->setData("totals",$totals);
		if (count($_POST) > 0 && array_key_exists(__FUNCTION__,$_POST)) {
			$outer->addData($_POST);
			if ($outer->validate()) {
				$sql = $this->getBillingSql($outer);


				if (array_key_exists("summary",$_REQUEST) && $_REQUEST["summary"] == 1) {
					//$sql = "select m.company, m.firstname, m.lastname, sum(o.value) as value, sum(o.taxes) as taxes, sum(o.total) as total, t.name, count(o.id) as ct, coalesce(a.authorization_amount,0) as paid from orders o left join order_taxes ot on ot.order_id = o.id and ot.line_id = 0 left join taxes t on t.id = ot.tax_id left join order_authorization a on a.order_id = o.id and a.authorization_type='D' and a.authorization_success = 1, members m, custom_delivery c1, custom_delivery c2, drivers d1, drivers d2, order_lines ol, product p where";
					//$sql = sprintf("%s %s group by m.id, t.name order by m.company, t.name", $sql, implode(" and ",$srch));
					$sql = sprintf("%s group by m.id, t.name order by m.company, t.name", $sql);

					$recs = $this->fetchAllTest($sql);
					foreach($recs as $k=>$r) {
						$totals["taxable"] += $r["taxable"];
						$totals["nontaxable"] += $r["nontaxable"];
						$totals["taxes"] += $r["taxes"];
						$totals["total"] += $r["total"];
						$totals["paid"] += $r["paid"];
						$totals["balance"] += $r["balance"];
					}
					$outer->setData("totals",$totals);
					$pagination = $this->getPagination($sql,$module,$recordCount);
					$outer->setData("pagination",$pagination);
					$recs = $this->fetchAllTest($sql);
					$rows = array();

					$inner = new Forms();
					$inner->init($this->m_dir.$module['parm1']);
					$flds = $inner->buildForm($this->config->getFields($module['configuration']."Summary"));


					foreach($recs as $k=>$v) {
						$inner->addData($v);
						$rows[] = $inner->show();
					}
				}
				else {
					//$sql = sprintf("select o.*, m.company, m.firstname, m.lastname, c1.id as pu_id, c2.id as del_id, p.name, coalesce(a.authorization_amount,0) as paid, o.total - coalesce(a.authorization_amount,0) as balance from orders o left join order_authorization a on a.order_id = o.id and a.authorization_type='D' and a.authorization_success = 1, members m, custom_delivery c1, custom_delivery c2, drivers d1, drivers d2, order_lines ol, product p where ");
					//$sql = sprintf("%s %s order by %s", $sql, implode(" and ",$srch), $outer->getData("orderby"));
					$sql = sprintf("%s group by o.id order by %s, IF(ol.custom_package ='S',1,2)", $sql, $outer->getData("orderby"));

					$recs = $this->fetchAllTest($sql);
					foreach($recs as $k=>$r) {
						$totals["taxable"] += $r["taxable"];
						$totals["nontaxable"] += $r["nontaxable"];
						$totals["taxes"] += $r["taxes"];
						$totals["total"] += $r["total"];
						$totals["paid"] += $r["paid"];
						$totals["balance"] += $r["balance"];
					}
					$outer->setData("totals",$totals);

					$pagination = $this->getPagination($sql,$module,$recordCount);
					$outer->setData("pagination",$pagination);
					$recs = $this->fetchAllTest($sql);
					$rows = array();
					foreach($recs as $k=>$v) {
						$v["pickup"] = $this->fetchSingleTest("select cd.*, m.firstname, m.lastname from custom_delivery cd, drivers d, members m where cd.id = %d and d.id = cd.driver_id and m.id = d.member_id", $v["pu_id"]);
						$v["pickup"]["address"] = Address::formatData($this->fetchSingleTest("select * from addresses where ownertype='order' and ownerid = %d and addresstype=%d", $v["id"], ADDRESS_PICKUP));
						$v["delivery"] = $this->fetchSingleTest("select cd.*, m.firstname, m.lastname from custom_delivery cd, drivers d, members m where cd.id = %d and d.id = cd.driver_id and m.id = d.member_id", $v["del_id"]);
						$v["delivery"]["address"] = Address::formatData($this->fetchSingleTest("select * from addresses where ownertype='order' and ownerid = %d and addresstype=%d", $v["id"], ADDRESS_DELIVERY));
						$v["tax_codes"] = implode(", ", $this->fetchScalarAllTest("select t.name from order_taxes ot, taxes t where t.id = ot.tax_id and ot.order_id = %d and ot.line_id = 0 and ot.tax_amount >= .01", $v["id"]));
						$inner->addData($v);
						$rows[] = $inner->show();
					}
				}
				$outer->setData("rows",implode("",$rows),false);
			}
		}
		return $outer->show();
	}

    /**
     * @param $form
     * @return string
     */
    private function getBillingSql($form) {
		$srch = array("a"=>"m.id = o.member_id","b"=>"c1.order_id = o.id","c"=>"c2.order_id = o.id","d"=>"d1.id = c1.driver_id","e"=>"d2.id = c2.driver_id","f"=>"c1.service_type='P'","g"=>"c2.service_type='D'",
			"h"=>"ol.order_id = o.id", "j"=>"p.id = ol.product_id","k"=>"ol.order_id = o.id","l"=>"ol.deleted = 0","m"=>"o.deleted = 0");
		$type = $form->getData("billing_type");
		switch($type) {
			case "P":	// prepaid
				$srch["k"] = sprintf("coalesce(a.authorization_amount,0) != 0.00");
				break;
			case "B":	// bill me later 
				$srch["k"] = sprintf("coalesce(a.authorization_amount,0) = 0.00");
				break;
			case "C":
				$srch = array("a"=>"m.id = o.member_id","b"=>"ol.order_id = o.id", "c"=>"ol.deleted = 0", "d"=>"p.id = ol.product_id",
					"e"=>"not exists(select 1 from custom_delivery cd1 where cd1.order_id = o.id)"
				);
				break;
		}
		foreach($_POST as $k=>$v) {
			switch($k) {
			case "member_id":
				if ($v > 0) $srch[$k] = sprintf("m.id = %d",$v);
				break;
			case "invoiced":
				$srch[$k] = sprintf("o.custom_qb_order %s= 0", $v ? "!" : "");
				break;
			case "order_status":
				if (is_array($v) && count($v) > 0) {
					$status = 0;
					foreach($v as $sk=>$sv) {
						$status |= $sv;
					}
					if ($sv > 0) $srch[$k] = sprintf("o.order_status = %d",$status);
				}
				break;
			case "from":
				if ($type == "C") {
					if (strlen($form->getData($k)) > 0) $srch[$k] = sprintf("DATE(o.order_date) >= '%s'", $form->getData($k));
				}
				else {
					if (strlen($form->getData($k)) > 0) $srch[$k] = sprintf("DATE(c2.actual_date) >= '%s'", $form->getData($k));
				}
				break;
			case "to":
				if ($type == "C") {
					if (strlen($form->getData($k)) > 0) $srch[$k] = sprintf("DATE(o.order_date) <= '%s'", $form->getData($k));
				}
				else {
					if (strlen($form->getData($k)) > 0) $srch[$k] = sprintf("DATE(c2.actual_date) <= '%s'", $form->getData($k));
				}
				break;
			case "product_id":
				if ($v > 0)
					$srch[$k] = sprintf("p.id = %d", $v);
				break;
			case "billing_type":
				break;
			default:
				break;
			}
		}
		if (array_key_exists("summary",$_REQUEST) && $_REQUEST["summary"] == 1) {
			if ($type == "C")
				$sql = "select m.company, m.firstname, m.lastname, sum(IF(ol.taxes < 0.01,ol.total,0)) as nontaxable, sum(IF(ol.taxes > 0,ol.total,0)) as taxable, sum(ol.taxes) as taxes, sum(o.value) as value, sum(ol.total + ol.taxes) as total, t.name, count(distinct o.id) as ct, coalesce(a.authorization_amount,0) as paid, sum(ol.total+ol.taxes) - coalesce(a.authorization_amount,0) as net from orders o left join order_taxes ot on ot.order_id = o.id and ot.line_id = 0 left join taxes t on t.id = ot.tax_id left join order_authorization a on a.order_id = o.id and a.authorization_type='D' and a.authorization_success = 1, members m, order_lines ol, product p where";
			else
				$sql = "select m.company, m.firstname, m.lastname, sum(IF(ol.taxes < 0.01,ol.total,0)) as nontaxable, sum(IF(ol.taxes > 0,ol.total,0)) as taxable, sum(ol.taxes) as taxes, sum(o.value) as value, sum(ol.total + ol.taxes) as total, t.name, count(distinct o.id) as ct, coalesce(a.authorization_amount,0) as paid, sum(ol.total+ol.taxes) - coalesce(a.authorization_amount,0) as net from orders o left join order_taxes ot on ot.order_id = o.id and ot.line_id = 0 left join taxes t on t.id = ot.tax_id left join order_authorization a on a.order_id = o.id and a.authorization_type='D' and a.authorization_success = 1, members m, custom_delivery c1, custom_delivery c2, drivers d1, drivers d2, order_lines ol, product p where";
		}
		else {
			if ($type == "C")
				$sql = sprintf("select o.*, m.company, m.firstname, m.lastname, 0 as pu_id, 0 as del_id, p.name, coalesce(a.authorization_amount,0) as paid, o.total - coalesce(a.authorization_amount,0) as balance, sum(IF(ol.taxes < 0.01,ol.total,0)) as nontaxable, sum(IF(ol.taxes > 0,ol.total,0)) as taxable, sum(ol.taxes) as taxes from orders o left join order_authorization a on a.order_id = o.id and a.authorization_type='D' and a.authorization_success = 1, members m, order_lines ol, product p where ");
			else
				$sql = sprintf("select o.*, m.company, m.firstname, m.lastname, c1.id as pu_id, c2.id as del_id, p.name, coalesce(a.authorization_amount,0) as paid, o.total - coalesce(a.authorization_amount,0) as balance, sum(IF(ol.taxes < 0.01,ol.total,0)) as nontaxable, sum(IF(ol.taxes > 0,ol.total,0)) as taxable, sum(ol.taxes) as taxes from orders o left join order_authorization a on a.order_id = o.id and a.authorization_type='D' and a.authorization_success = 1, members m, custom_delivery c1, custom_delivery c2, drivers d1, drivers d2, order_lines ol, product p where ");
		}
		return sprintf("%s %s", $sql, implode(" and ",array_values($srch)));
	}

    /**
     * @return array|mixed|string|string[]
     * @throws phpmailerException
     */
    function fedexStatus() {
		if (!$module = $this->getModule())
			return "";
		$outer = new Forms();
		$outer->setModule($module);
		$outer->init($this->m_dir.$module['outer_html']);
		$flds = $outer->buildForm($this->config->getFields($module['configuration']));

		$inner = new Forms();
		$inner->init($this->m_dir.$module['inner_html']);

		$flds = $inner->buildForm($this->config->getFields($module['configuration']."Row"));

		$sql = sprintf("select cd1.id as pu_id, cd2.id as del_id, cd1.order_id, cd1.scheduled_date, o.customs_declaration from custom_delivery cd1 left join drivers d1 on d1.id = cd1.driver_id, custom_delivery cd2 left join drivers d2 on d2.id = cd2.driver_id, orders o where (d1.third_party = 1 or d2.third_party = 1) and cd1.order_id = cd2.order_id and cd1.service_type='P' and cd2.service_type='D' and cd1.scheduled_date >= '%s' and o.id = cd1.order_id and o.order_status & ~%d = 0 and o.deleted = 0 order by cd1.scheduled_date desc", date("Y-m-d",strtotime(sprintf("today - %d days", $this->getOption("daysToShow")))), STATUS_PROCESSING | STATUS_SHIPPED);
		$pagination = $this->getPagination($sql,$module,$recordCount);

		$rows = array();
		$recs = $this->fetchAllTest($sql);

		$p_form = new Forms();
		$p_form->init($this->m_dir.$module['parm1']);
		$flds = $p_form->buildForm($this->config->getFields($module['configuration']."Package"));
		foreach($recs as $k=>$v) {
			$v["customs_declaration"] = nl2br($v["customs_declaration"]);
			$v["pickup"] = $this->fetchSingleTest("select * from custom_delivery where id = %d", $v["pu_id"]);
			$v["pickup"]["address"] = Address::formatData($this->fetchSingleTest("select * from addresses where ownertype='order' and ownerid = %d and addresstype = %d", $v["order_id"], ADDRESS_PICKUP ));
			$v["pickup"]["driver"] = $this->fetchSingleTest("select m.*, d.third_party from members m, drivers d where d.id = %d and m.id = d.member_id", $v["pickup"]["driver_id"]);
			$v["delivery"] = $this->fetchSingleTest("select * from custom_delivery where id = %d", $v["del_id"]);
			$v["delivery"]["address"] = Address::formatData($this->fetchSingleTest("select * from addresses where ownertype='order' and ownerid = %d and addresstype = %d", $v["order_id"], ADDRESS_DELIVERY ));
			$v["delivery"]["driver"] = $this->fetchSingleTest("select m.*, d.third_party from members m, drivers d where d.id = %d and m.id = d.member_id", $v["delivery"]["driver_id"]);
			$v["service"] = $this->fetchSingleTest("select p.* from product p, order_lines ol where ol.order_id = %d and ol.custom_package = 'S' and p.id = ol.product_id", $v["order_id"]);
			$packages = $this->fetchAllTest("select p.name, old.*, c.code, c.value from product p, order_lines ol, order_lines_dimensions old, orders o, code_lookups c where o.id = %d and ol.order_id = o.id and ol.custom_package = 'P' and p.id = ol.product_id and ol.deleted = 0 and old.order_id = ol.order_id and old.line_id = ol.line_id and c.id = o.custom_weight_code", $v["order_id"]);
			$p_rows = array();
			foreach($packages as $sk=>$sv) {
				$p_form->addData($sv);
				$p_rows[] = $p_form->show();
			}
			$inner->setData("packages",implode("",$p_rows));
			$inner->addData($v);
			$rows[] = $inner->show();
		}
		$outer->setData("rows",implode("",$rows));
		$outer->setData("pagination",$pagination);
		return $outer->show();
	}

    /**
     * @return array|mixed|string|string[]
     * @throws phpmailerException
     */
    function invoicing() {
		if (!$module = $this->getModule())
			return "";
		set_time_limit(60*60);
		$outer = new Forms();
		$outer->setModule($module);
		$outer->init($this->m_dir.$module['outer_html']);
		$inner = new Forms();
		$inner->init($this->m_dir.$module['inner_html']);
		$consolidated = new Forms();
		$consolidated->init($this->m_dir.$module['parm1']);
		$flds = $inner->buildForm($this->config->getFields($module['configuration']."Row"));
		$flds = $consolidated->buildForm($this->config->getFields($module['configuration']."Row"));
		$flds = $this->config->getFields($module['configuration']);
		$flds["invoice_id"]["value"] = $this->fetchScalarTest("select max(qb_invoice_id) from qb_export") + 1;
		$flds = $outer->buildForm($flds);
		$rows = array();
		$curr_user = $_SESSION["user"];
		if (count($_REQUEST) > 0 && array_key_exists(__FUNCTION__,$_REQUEST)) {
			$outer->addData($_REQUEST);
			$valid = $outer->validate();
			if ($valid) {
				if ($outer->getData("report_only") == 1) {
					$outer->setData("invoices",$this->getInvoiceReport($flds, $outer, $inner, $consolidated));
					$outer->addFormSuccess("Reporting only");
					return $outer->show();
				}
				$ct = $this->fetchScalarTest("select count(0) from qb_export where qb_invoice_id >= %d", $outer->getData("invoice_id"));
				if ($ct > 0) {
					$valid = false;
					$outer->addFormError(sprintf("Invoices have already been generated with the starting invoice #"));
				}
			}
			if ($valid) {
				$sql1 = $this->invoicingSql($outer->getData("order_type"),1,$flds,$outer);
				$sql2 = $this->invoicingSql($outer->getData("order_type"),2,$flds,$outer);
				$recs = $this->fetchAllTest($sql1);
				$outer->addFormSuccess(sprintf("Found %d companies to be invoiced", count($recs)));
				if ($outer->getData("do_it") == "C") {
					$orders = $this->fetchAllTest($sql2);
					//
					//	Loop through the orders and check for the tax errors
					//
					$valid = true;
					$recalculated = 0;
					foreach($orders as $k=>$v) {
						if ($this->orderNeedsRecalc($v["id"])) {
							Ecom::recalcOrderFromDB($v["id"]);
							if ($this->orderNeedsRecalc($v["id"])) {
								$outer->addFormError(sprintf("Error recalculating order #%d", $v["id"]));
								$valid = false;
							}
							else $recalculated += 1;
						}
					}
					if ($recalculated > 0) $outer->addFormSuccess(sprintf("Recalculated %d orders successfully", $recalculated));
				}
				if ($outer->getData("do_it") == "I") {
					$valid = true;
					$stmt = $this->prepare(sprintf("insert into `inv-temp`(invoice_id, order_id, invoice_date, member_id) values(?,?,?,?);"));
					$this->beginTransaction();
					$this->execute(sprintf("delete from `inv-temp`;"));
					$invoice_id = $outer->getData("invoice_id");
					foreach($recs as $k=>$v) {
						$outer->setData("member_id",$v["id"]);
						$sql2 = str_replace("group by m.id"," and o.id not in (select order_id from `inv-temp`) ",$this->invoicingSql($outer->getData("order_type"),2,$flds,$outer));
						//$srch["member_id"] = sprintf("o.member_id = %d", $v["id"]);
						$orders = $this->fetchAllTest($sql2);
						foreach($orders as $sk=>$sv) {
							$stmt->bindParams(array("ddsd", $invoice_id, $sv["id"], $outer->getData("end_date"), $v["id"]));
							if (!$stmt->execute()) {
								$outer->addFormError("An error occurred. The Web Master has been notified");
								$valid = false;
								break 2;
							}
						}
						$invoice_id++;
					}
					if ($valid) {
						$this->commitTransaction();
						$rows = $this->createInvoices($inner);
					}
					else {
					 	$this->rollbackTransaction();
					}
				}
			}
		}
		$_SESSION["user"] = $curr_user;
		$outer->setData("invoices",implode("",$rows));
		return $outer->show();
	}

    /**
     * @param $flds
     * @param $outer
     * @param $inner
     * @param $consolidated
     * @return string
     * @throws phpmailerException
     */
    private function getInvoiceReport($flds, $outer, $inner, $consolidated) {
		$srch = array(
			"a" => "qbd.qb_export_id = qb.id",
			"b" => "o.id = qbd.order_id",
			"c" => "m.id = o.member_id",
			"d" => sprintf("o.order_status & %d = %d", STATUS_SHIPPED, STATUS_SHIPPED),
			"e" => "o.member_id = qb.member_id"
		);
		$rows = array();
		foreach($flds as $k=>$v) {
			switch($v["name"]) {
				case "start_date":
					if (($d = $outer->getData($v["name"])) != "")
						$srch[$v["name"]] = sprintf("date(qb.invoice_date) >= '%s'", $d);
						break;
				case "end_date":
					if (($d = $outer->getData($v["name"])) != "0000-00-00")
						$srch[$v["name"]] = sprintf("date(qb.invoice_date) <= '%s'", $d);
						break;
				case "member_id":
					if (($d = $outer->getData($v["name"])) != 0)
						$srch[$v["name"]] = sprintf("o.member_id = %d", $d);
						break;
				default:
					break;
			}
		}
		$tmp = $srch;
		$tmp["zz"] = "consolidate_id > 0";
		$tmp["c"] = "m.id = qb.consolidate_id";
		$sql = sprintf("select distinct consolidate_id, m.company from qb_export qb, qb_export_dtl qbd, orders o, members m where %s group by qb.consolidate_id", implode(" and ",$tmp));
		$recs = $this->fetchAllTest($sql);
		foreach($recs as $k=>$v) {
			$tmp["zz"] = sprintf("consolidate_id = %d", $v["consolidate_id"]);
			$tmp["c"] = "m.id = o.member_id";
			unset($tmp["e"]);
			$c = $this->fetchSingleTest("select min(qb.qb_invoice_id) as qb_invoice_id, m.company, qb.member_id, count(o.id) as order_count from qb_export qb, qb_export_dtl qbd, orders o, members m where %s group by qb.consolidate_id", implode(" and ",$tmp));
			$consolidated->addData($c);
			$consolidated->addData($v);
			//$rows[] = $consolidated->show();
			$d = $this->fetchAllTest("select qb.qb_invoice_id, m.company, qb.member_id, count(o.id) as order_count from qb_export qb, qb_export_dtl qbd, orders o, members m where %s group by qb.qb_invoice_id", implode(" and ",$tmp));
			$r_tmp = array();
			$totals = array('invoice_amount' => 0, 'nontaxable' => 0, 'taxable' => 0, 'paid_amount' => 0, 'tax_amount' => 0, 'invoice_total'=>0, 'net'=>0);
			foreach($d as $sk=>$sv) {
				$d = array("member"=>array(),"invoice"=>array("qb_invoice_id"=>$sv["qb_invoice_id"], "order_count"=>$sv["order_count"]));
				$d["member"]["company"] = $sv["company"];
				$o_sql = sprintf("select qbd.order_id from qb_export qb, qb_export_dtl qbd, orders o where qb_invoice_id = %d and qbd.qb_export_id = qb.id and o.id = qbd.order_id and o.order_status & %d = %d", $sv["qb_invoice_id"], STATUS_SHIPPED, STATUS_SHIPPED);
				$o_ids = $this->fetchScalarAllTest($o_sql);				
				$hdr = $this->fetchSingleTest("select round(sum(IF(ol.taxes > 0,ol.total,0)),2) as taxable, sum(IF(ol.taxes < 0.01,ol.total,0)) as nontaxable, o.authorization_amount from orders o, order_lines ol where ol.order_id = o.id and ol.deleted = 0 and o.id in (%s)",implode(",",$o_ids));
				$taxes = $this->fetchScalarTest("select round(sum(o.taxes,2)) as taxes from orders o where o.id in (%s)", implode(",",$o_ids));
				$d["invoice"]["invoice_amount"] = $hdr["taxable"];
				$d["invoice"]["nontaxable"] = $hdr["nontaxable"];
				$d["invoice"]["paid_amount"] = 0;
				$d["invoice"]["tax_amount"] = $taxes;
				$d["invoice"]["invoice_total"] = $hdr["taxable"] + $hdr["nontaxable"] + $taxes;
				$d["invoice"]["net"] = $hdr["taxable"] + $hdr["nontaxable"] + $taxes;
				$d["consolidate_id"] = $v["consolidate_id"];
				$totals["invoice_amount"] += $hdr["taxable"];
				$totals["nontaxable"] += $hdr["nontaxable"];
				$totals["paid_amount"] += 0;
				$totals["tax_amount"] += $taxes;
				$totals["invoice_total"] += $hdr["taxable"] + $hdr["nontaxable"] + $taxes;
				$totals["net"] += $d["invoice"]["invoice_total"] - $d["invoice"]["paid_amount"];
				$inner->addData($d);
				$r_tmp[] = $inner->show();
			}
			$consolidated->setData("totals",$totals);
			$rows[] = $consolidated->show();
			$rows = array_merge($rows,$r_tmp);
		}
		$inner->reset();
		$srch["zz"] = "consolidate_id = 0";
		$sql = sprintf("select qb.qb_invoice_id, m.company, qb.member_id, count(o.id) as order_count from qb_export qb, qb_export_dtl qbd, orders o, members m where %s group by qb.qb_invoice_id", implode(" and ",$srch));
		$recs = $this->fetchAllTest($sql);
		foreach($recs as $k=>$v) {
			$d = array("member"=>array(),"invoice"=>array("qb_invoice_id"=>$v["qb_invoice_id"], "order_count"=>$v["order_count"]));
			$d["member"]["company"] = $v["company"];
			$o_sql = sprintf("select qbd.order_id from qb_export qb, qb_export_dtl qbd, orders o where qb_invoice_id = %d and qbd.qb_export_id = qb.id and o.id = qbd.order_id and o.order_status & %d = %d", $v["qb_invoice_id"], STATUS_SHIPPED, STATUS_SHIPPED);
			$o_ids = $this->fetchScalarAllTest($o_sql);
			$hdr = $this->fetchSingleTest("select round(sum(IF(ol.taxes > 0,ol.total,0)),2) as taxable, sum(IF(ol.taxes < 0.01,ol.total,0)) as nontaxable, o.authorization_amount from orders o, order_lines ol where ol.order_id = o.id and ol.deleted = 0 and o.id in (%s)",implode(",",$o_ids));			
			$taxes = $this->fetchScalarTest("select round(sum(o.taxes,2)) as taxes from orders o where o.id in (%s)", implode(",",$o_ids));
			$d["invoice"]["invoice_amount"] = $hdr["taxable"];
			$d["invoice"]["nontaxable"] = $hdr["nontaxable"];
			$d["invoice"]["paid_amount"] = 0;
			$d["invoice"]["tax_amount"] = $taxes;
			$d["invoice"]["invoice_total"] = $hdr["taxable"] + $hdr["nontaxable"] + $taxes;
			$d["invoice"]["net"] = $d["invoice"]["invoice_total"] - $d["invoice"]["paid_amount"];
			$inner->addData($d);
			$rows[] = $inner->show();
		}
		$this->logMessage(__FUNCTION__,sprintf("rows [%s] inner [%s]", print_r($rows,true), print_r($inner,true)),1);
		return implode("",$rows);
	}

    /**
     * @param $type
     * @param $mode
     * @param $flds
     * @param $outer
     * @return string
     * @throws phpmailerException
     */
    private function invoicingSql($type, $mode, $flds, $outer) {
		$srch = array();
		switch($type) {
			case "B":	// bill-me-later - must not have an order_authorization
				$srch = array(
					"a"=>"o.custom_qb_order = 0",
					"b"=>"cd1.order_id = o.id",
					"c"=>"cd1.service_type = 'P'",
					"d"=>"cd1.completed = 1",
					"e"=>"cd2.service_type = 'D'",
					"f"=>"cd2.order_id = o.id",
					"g"=>"cd2.completed = 1",
					"h"=>"m.id = o.member_id",
					"i"=>sprintf("o.order_status = %d",STATUS_SHIPPED),
					"j"=>"o.deleted = 0",
					"k"=>"not exists (select 1 from order_authorization a where a.order_id = o.id)",
					"start_date"=>"date(cd2.actual_date) >= '1990-01-01'"
				);
				$sql1 = "select m.id, m.company, m.custom_qb_id, count(o.id) as ct, sum(total) as total, 0 as paid from orders o, custom_delivery cd1, custom_delivery cd2, members m where %s group by m.id order by m.company";
				$sql2 = "select o.id from orders o, custom_delivery cd1, custom_delivery cd2, members m where %s group by m.id order by m.company";
				foreach($flds as $k=>$v) {
					switch($v["name"]) {
						case "start_date":
							if (($d = $outer->getData($v["name"])) != "")
								$srch[$v["name"]] = sprintf("date(cd2.actual_date) >= '%s'", $d);
								break;
						case "end_date":
							if (($d = $outer->getData($v["name"])) != "0000-00-00")
								$srch[$v["name"]] = sprintf("date(cd2.actual_date) <= '%s'", $d);
								break;
						case "member_id":
							if (strlen($d = $outer->getData($v["name"])) > 0) {
								$tmp = $this->fetchScalarAllTest("select m1.id from members m1, members m2 where m1.custom_parent_org = %d and m2.id = m1.custom_parent_org and m2.custom_consolidate_invoices > 0", $d);
								if (count($tmp) > 0) {
									$srch[$v["name"]] = sprintf("o.member_id in (%s)", implode(", ", array_merge($tmp, array($d))));
								}
								else {
									$srch[$v["name"]] = sprintf("o.member_id = %d", $d);
								}
							}
							break;
						default:
							break;
					}
				}
				break;
			case "P":	// prepaid - must have an order_authorization record
				$srch = array(
					"a"=>"o.custom_qb_order = 0",
					"b"=>"cd1.order_id = o.id",
					"c"=>"cd1.service_type = 'P'",
					"d"=>"cd1.completed = 1",
					"e"=>"cd2.service_type = 'D'",
					"f"=>"cd2.order_id = o.id",
					"g"=>"cd2.completed = 1",
					"h"=>"m.id = o.member_id",
					"i"=>sprintf("o.order_status & %d = %d", STATUS_SHIPPED, STATUS_SHIPPED),
					"j"=>"o.deleted = 0",
					"k"=>"a.order_id = o.id",
					"l"=>"a.authorization_success = 1",
					"m"=>"a.authorization_type = 'D'",
					"start_date"=>"date(cd2.actual_date) >= '1990-01-01'"
				);
				$sql1 = "select m.id, m.company, m.custom_qb_id, count(o.id) as ct, sum(total) as total, coalesce(a.authorization_amount,0) as paid from orders o , order_authorization a, custom_delivery cd1, custom_delivery cd2, members m where %s group by m.id order by m.company";
				$sql2 = "select o.id from orders o , order_authorization a, custom_delivery cd1, custom_delivery cd2, members m where %s group by m.id order by m.company";
				foreach($flds as $k=>$v) {
					switch($v["name"]) {
						case "start_date":
							if (($d = $outer->getData($v["name"])) != "")
								$srch[$v["name"]] = sprintf("date(cd2.actual_date) >= '%s'", $d);
								break;
						case "end_date":
							if (($d = $outer->getData($v["name"])) != "0000-00-00")
								$srch[$v["name"]] = sprintf("date(cd2.actual_date) <= '%s'", $d);
								break;
						case "member_id":
							if (($d = $outer->getData($v["name"])) != 0)
								$srch[$v["name"]] = sprintf("o.member_id = %d", $d);
								break;
						default:
							break;
					}
				}
				break;
			case "C":	// non-delivery related charges - no custom_delivery record
				$srch = array(
					"a"=>"o.custom_qb_order = 0",
					"b"=>"m.id = o.member_id",
					"c"=>sprintf("o.order_status = %d",STATUS_SHIPPED),
					"d"=>"o.deleted = 0",
					"e"=>"not exists (select 1 from custom_delivery cd where cd.order_id = o.id)",
					"start_date"=>"date(o.order_date) >= '1990-01-01'"
				);
				$sql1 = "select m.id, m.company, m.custom_qb_id, count(o.id) as ct, sum(total) as total, 0 as paid from orders o, members m where %s group by m.id order by m.company";
				$sql2 = "select o.id from orders o, members m where %s group by m.id order by m.company";
				foreach($flds as $k=>$v) {
					switch($v["name"]) {
						case "start_date":
							if (($d = $outer->getData($v["name"])) != "")
								$srch[$v["name"]] = sprintf("date(o.order_date) >= '%s'", $d);
								break;
						case "end_date":
							if (($d = $outer->getData($v["name"])) != "0000-00-00")
								$srch[$v["name"]] = sprintf("date(o.order_date) <= '%s'", $d);
								break;
						case "member_id":
							if (($d = $outer->getData($v["name"])) != 0)
								$srch[$v["name"]] = sprintf("o.member_id = %d", $d);
								break;
						default:
							break;
					}
				}
				break;
		}
		if ($mode == 1)
			return sprintf($sql1,implode(" and ", array_values($srch)));
		else {
		 	return sprintf($sql2,implode(" and ", array_values($srch)));
		} 
	}

    /**
     * @param $o_id
     * @return bool
     * @throws phpmailerException
     */
    private function orderNeedsRecalc($o_id ) {
		$o = $this->fetchSingleTest("select * from orders where id = %d", $o_id);
		$lines = $this->fetchAllTest("select * from order_lines where order_id = %d and deleted = 0", $o_id);
		$taxes = $this->fetchAllTest("select * from order_taxes where order_id = %d and line_id > 0", $o_id);
		$line_tax = array("value"=>0,"tax"=>0);
		$tax_tax = array("value"=>0,"tax"=>0);
		foreach($lines as $sk=>$sv) {
			$line_tax["value"] += $sv["value"];
			$line_tax["tax"] += $sv["taxes"];
		}
		foreach($taxes as $sk=>$sv) {
			$tax_tax["value"] += $sv["taxable_amount"];
			$tax_tax["tax"] += $sv["tax_amount"];
		}
		//
		//	we've added non-taxable items now, order header value vs tax line value does not reflect that
		//
		//if (abs($o["value"] - $line_tax["value"]) > .02 || (count($taxes) > 0 && abs($o["value"] - $tax_tax["value"]) > .02) || 
		if (abs($o["taxes"] - $line_tax["tax"]) > .02 || (count($taxes) > 0 && abs($o["taxes"] - $tax_tax["tax"]) > .02)) {
			$this->logMessage(__FUNCTION__,sprintf("tax summary order [%s] value [%s] taxes [%s] line [%s] taxes [%s]", $o["id"], $o["value"], $o["taxes"], print_r($line_tax,true), print_r($tax_tax,true)), 1);
			$this->logMessage(__FUNCTION__, sprintf("tax discrepancy found order [%s] lines [%s] taxes [%s]", print_r($o, true), print_r($lines,true), print_r($taxes,true)), 1);
			$ret = true;
		}
		else $ret = false;
		return $ret;
	}

    /**
     * @param $form
     * @return array
     * @throws phpmailerException
     */
    private function createInvoices($form) {
		$rows = array();
		$order_ins = array(
			"qb_export_id"=>0,
			"order_id"=>0,
			"order_amount"=>0,
			"paid_amount"=>0,
			"tax_amount"=>0
		);
		$order_stmt = $this->prepare(sprintf("insert into qb_export_dtl(%s) values(?%s)", implode(", ",array_keys($order_ins)), str_repeat(", ?",count($order_ins)-1)));
		$inv = array(
			"created"=>date(DATE_ATOM), 
			"invoice_date"=>"2018-06-01", 
			"member_id"=>0, 
			"order_count"=>0,
			"qb_invoice_id"=>0,
			"consolidate_id"=>0
		);
		$inv_stmt = $this->prepare(sprintf("insert into qb_export(%s) values(?%s)", implode(", ",array_keys($inv)), str_repeat(", ?", count($inv)-1)));
		$this->execute(sprintf("update `inv-temp` i set consolidate_id = (select m2.id from members m1, members m2 where m1.id = i.member_id and m2.id = m1.custom_parent_org and m2.custom_consolidate_invoices != 0)"));
		$invoices = $this->fetchAllTest("select distinct invoice_id as invoice_id, invoice_date as invoice_date, consolidate_id from `inv-temp` where invoice_id not in (select qb_invoice_id from qb_export) order by consolidate_id");
		$this->logMessage(__FUNCTION__,sprintf("invoices [%s]", print_r($invoices,true)), 3);
		foreach($invoices as $k=>$invoice) {
			$orders = $this->fetchAllTest("select order_id, member_id, custom_parent_org from `inv-temp` i, members m where m.id = i.member_id and i.invoice_id = %d", $invoice["invoice_id"]);
			$this->beginTransaction();
			$inv["member_id"] = $orders[0]["member_id"];
			$inv["order_count"] = count($orders);
			$inv["qb_invoice_id"] = $invoice["invoice_id"];
			$inv["invoice_date"] = $invoice["invoice_date"];
			$inv["consolidate_id"] = $invoice["consolidate_id"];
			$inv_stmt->bindParams(array_merge(array(str_repeat("s",count($inv))),array_values($inv)));
			$inv_stmt->execute();
			$inv_id = $this->insertId();
			$amts = array("invoice_amount"=>0,"nontaxable"=>0,"tax_amount"=>0,"invoice_total"=>0,"paid_amount"=>0,"net"=>0);
			$valid = true;
			foreach($orders as $sk=>$order) {
				$order_ins["qb_export_id"]= $inv_id;
				$order_ins["order_id"] = $order["order_id"];
				//$hdr = $this->fetchSingleTest("select * from orders where id = %d", $order["order_id"]);
				$hdr = $this->fetchSingleTest("select sum(IF(ol.taxes > 0,ol.total,0)) as taxable, sum(IF(ol.taxes < 0.01,ol.total,0)) as nontaxable, sum(ol.taxes) as taxes, o.authorization_amount from orders o, order_lines ol where ol.order_id = o.id and ol.deleted = 0 and o.id = %d group by o.id",$order["order_id"]);				
				$order_ins["order_amount"] = $hdr["taxable"] + $hdr["nontaxable"];
				$order_ins["tax_amount"] = $hdr["taxes"];
				$order_ins["paid_amount"] = $hdr["authorization_amount"];
				$amts["invoice_amount"] += $hdr["taxable"];
				$amts["nontaxable"] += $hdr["nontaxable"];
				$amts["paid_amount"] += $hdr["authorization_amount"];
				$amts["tax_amount"] += $hdr["taxes"];
				$order_stmt->bindParams(array_merge(array(str_repeat("s",count($order_ins))),array_values($order_ins)));
				$valid &= $order_stmt->execute();
				if ($this->fetchScalarTest("select coalesce(count(order_id,0)) from order_authorization where order_id = %d", $order["order_id"]) == 0)
					$valid &= $this->execute(sprintf("update orders set custom_qb_order = %d, authorization_amount = %f where id = %d", $invoice["invoice_id"], $order_ins["order_amount"]+$order_ins["tax_amount"], $order["order_id"]));
			}
			$valid &= $this->execute(sprintf("update qb_export set invoice_amount = %f, tax_amount = %f, paid_amount = %f, nontaxable = %f where id = %d", $amts["invoice_amount"], $amts["tax_amount"], $amts["paid_amount"], $amts["nontaxable"], $inv_id));
			if ($valid) {
				//### testing $this->commitTransaction(); -- remove rollback below
				$member = $this->fetchSingleTest("select * from members where id = %d", $orders[0]["member_id"]);
				$form->addFormSuccess("Created");
				$amts["invoice_total"] = $amts["invoice_amount"] + $amts["nontaxable"] + $amts["tax_amount"];
				$amts["net"] = $amts["invoice_total"] - $amts["paid_amount"];
				$form->addData(array("invoice"=>array_merge($inv,$amts),"member"=>$member,"status"=>true));
				$rows[] = $form->show();
				$this->m_module = array();
				$this->config = new Custom(0);
				$this->logMeIn($member["username"],$member["password"],0,$member["id"]);
				$sql = 'select p.*, t.module_function, m.classname, m.id as module_id from modules_by_page p left join fetemplates t on t.id = p.fetemplate_id left join modules m on m.id = t.module_id where p.page_type = "P" and p.page_id = 163 and p.module_name = "main1"';
				$module = $this->fetchSingleTest($sql);
				$class = new Custom($module['id'],$module);
				$class->config = new Custom(0);
				$_REQUEST["i_id"] = $inv_id;
				$fn = sprintf($_SERVER['DOCUMENT_ROOT']."/files/invoice-%d.pdf",$invoice["invoice_id"]);
				$pdf = $class->{$module['module_function']}(true, $fn);
				$mailer = new MyMailer();
				$mailer->Subject = sprintf("Order Processing - %s", SITENAME);
				$body = new Forms();
				$flds = array("invoice_date"=>array("type"=>"datestamp"),"invoice_total"=>array("type"=>"currency"));
				$flds = $body->buildForm($flds);
				$sql = sprintf('select * from htmlForms where class = %d and type = "invoiceEmail"',$this->getClassId('custom'));
				$html = $this->fetchSingleTest($sql);
				$body->setHTML($html['html']);
				$inv_rec = $this->fetchSingleTest("select *, invoice_amount+tax_amount as invoice_total  from qb_export where id = %d",$inv_id);
				$inv_rec["user"] = $_SESSION["user"]["info"];
				if (!$address = $this->fetchSingleTest("select * from addresses where ownertype='member' and ownerid = %d", $inv_rec["user"]["id"])) {
					$address = array("id"=>0,"country_id"=>0,"province_id"=>0);
					$this->logMessage(__FUNCTION__,sprintf("No member address for company [%s] id [%d]", $member["company"], $inv_rec["user"]["id"]),1,true,false);
				}
				$inv_rec["address"] = Address::formatData($address);
				$body->addData($inv_rec);
				$body->setOption('formDelimiter','{{|}}');
				$mailer->Body = $body->show();
				if (defined("DEV") && DEV==1) {
					$mailer->From = "noreply@".HOSTNAME;
					$mailer->addReplyTo("bb@".HOSTNAME,"KJV Courier Services");
					$mailer->addAddress("ian@kjvcourier.com","Ian MacArthur");
					if (strlen($member["custom_invoice_email"])>0) {
						$tmp = explode(";",$member["custom_invoice_email"]);
						$this->logMessage(__FUNCTION__,sprintf("parsed [%s] into [%s]", $member["custom_invoice_email"], print_r($tmp,true)),1);
						foreach($tmp as $e1=>$e2) {
							$mailer->addAddress($e2, $member["company"]);
							$this->logMessage(__FUNCTION__,sprintf("sending to [%s]", $e2),1);
						}
					}
					$mailer->ConfirmReadingTo = "ian@kjvcourier.com";
				}
				else {
					$mailer->From = "noreply@".HOSTNAME;
					$mailer->FromName = "KJV Courier Services";
					$mailer->addReplyTo("bb@".HOSTNAME,"KJV Courier Services");
					if (strlen($member["custom_invoice_email"])>0) {
						$tmp = explode(";",$member["custom_invoice_email"]);
						$this->logMessage(__FUNCTION__,sprintf("parsed [%s] into [%s]", $member["custom_invoice_email"], print_r($tmp,true)),1);
						foreach($tmp as $e1=>$e2) {
							$mailer->addAddress($e2, $member["company"]);
							$this->logMessage(__FUNCTION__,sprintf("sending to [%s]", $e2),1);
						}
					}
					else {
						$mailer->addAddress($member["email"], $member["company"]);
					}
					$mailer->addBCC("lpileggi@kjvcourier.com","Lisa Pileggi");
					$mailer->ConfirmReadingTo = "lpileggi@kjvcourier.com";
				}

				$mailer->IsHTML(true);
				$mailer->AddAttachment($fn,sprintf("invoice-%d.pdf",$invoice["invoice_id"]));
				$mailer->Subject = sprintf("%s - KJV Courier Invoice #%d", $member["company"], $invoice["invoice_id"]);
				if (!$mailer->Send())
					$this->logMessage(__FUNCTION__,sprintf("mailer [%s] [%s]", print_r($mailer->ErrorInfo,true), print_r($mailer,true)),1);
				unlink($fn);
				$this->rollbackTransaction(); //### testing - undo commit above
			}
			else {
				$this->rollbackTransaction();
				$form->addFormError("Error");
				$form->addData(array("invoice"=>$inv,"member"=>$member,"status"=>false));
				$rows[] = $form->show();
			}
		}
		if (is_array($invoices) && count($invoices) > 0) {
			$consolidated = $this->fetchScalarAllTest("select m2.id from qb_export qb, members m1, members m2 where m1.id = qb.member_id and invoice_date = '%s' and m2.id = m1.custom_parent_org and m2.custom_consolidate_invoices > 0 group by m2.id", $invoices[0]["invoice_date"]);
			foreach($consolidated as $k=>$v) {
				$member = $this->fetchSingleTest("select m.* from members m where id = %d", $v);
				$this->m_module = array();
				$this->config = new Custom(0);
				$this->logMeIn($member["username"],$member["password"],0,$member["id"]);
				$sql = 'select p.*, t.module_function, m.classname, m.id as module_id from modules_by_page p left join fetemplates t on t.id = p.fetemplate_id left join modules m on m.id = t.module_id where p.page_type = "P" and p.page_id = 273 and p.module_name = "main1"';
				$module = $this->fetchSingleTest($sql);
				$class = new Custom($module['id'],$module);
				$class->config = new Custom(0);
				$_REQUEST["i_id"] = $inv_id;
				$fn = sprintf($_SERVER['DOCUMENT_ROOT']."/files/consolidation-%s.pdf",$invoices[0]["invoice_date"]);
				$_REQUEST["d_id"] = $invoices[0]["invoice_date"];
				$pdf = $class->{$module['module_function']}(true, $fn);
				$mailer = new MyMailer();
				$mailer->Subject = sprintf("Order Processing - %s", SITENAME);
				$body = new Forms();
				$flds = array("invoice_date"=>array("type"=>"datestamp"),"invoice_total"=>array("type"=>"currency"));
				$flds = $body->buildForm($flds);
				$sql = sprintf('select * from htmlForms where class = %d and type = "invoiceEmail"',$this->getClassId('custom'));
				$html = $this->fetchSingleTest($sql);
				$body->setHTML($html['html']);
				$inv_rec = $this->fetchSingleTest("select *, sum(invoice_amount+tax_amount) as invoice_total from qb_export where consolidate_id = %d and invoice_date = '%s'",$v, $invoices[0]["invoice_date"]);
				$inv_rec["user"] = $_SESSION["user"]["info"];
				if (!$address = $this->fetchSingleTest("select a.* from addresses a, code_lookups c where a.ownertype='member' and a.ownerid = %d and a.addresstype = c.id and c.id = %d and c.type = 'memberAddressType' and a.deleted = 0", $inv_rec["user"]["id"], ADDRESS_COMPANY)) {
					$address = array("id"=>0,"country_id"=>0,"province_id"=>0);
					$this->logMessage(__FUNCTION__,sprintf("No member address for company [%s] id [%d]", $member["company"], $inv_rec["user"]["id"]),1,true,false);
				}
				$inv_rec["address"] = Address::formatData($address);
				$body->addData($inv_rec);
				$body->setOption('formDelimiter','{{|}}');
				$mailer->Body = $body->show();
				if (defined("DEV") && DEV==1) {
					$mailer->From = "noreply@".HOSTNAME;
					$mailer->addReplyTo("bb@".HOSTNAME,"KJV Courier Services");
					$mailer->addAddress("ian@kjvcourier.com","Ian MacArthur");
					if (strlen($member["custom_invoice_email"])>0) {
						$tmp = explode(";",$member["custom_invoice_email"]);
						$this->logMessage(__FUNCTION__,sprintf("parsed [%s] into [%s]", $member["custom_invoice_email"], print_r($tmp,true)),1);
						foreach($tmp as $e1=>$e2) {
							$mailer->addAddress($e2, $member["company"]);
							$this->logMessage(__FUNCTION__,sprintf("sending to [%s]", $e2),1);
						}
					}
					$mailer->ConfirmReadingTo = "ian@kjvcourier.com";
				}
				else {
					$mailer->From = "noreply@".HOSTNAME;
					$mailer->FromName = "KJV Courier Services";
					$mailer->addReplyTo("bb@".HOSTNAME,"KJV Courier Services");
					if (strlen($member["custom_invoice_email"])>0) {
						$tmp = explode(";",$member["custom_invoice_email"]);
						$this->logMessage(__FUNCTION__,sprintf("parsed [%s] into [%s]", $member["custom_invoice_email"], print_r($tmp,true)),1);
						foreach($tmp as $e1=>$e2) {
							$mailer->addAddress($e2);
							$this->logMessage(__FUNCTION__,sprintf("sending to [%s]", $e2),1);
						}
					}
					else {
						$mailer->addAddress($member["email"], $member["company"]);
					}
					$mailer->addBCC("lpileggi@kjvcourier.com","Lisa Pileggi");
					$mailer->ConfirmReadingTo = "lpileggi@kjvcourier.com";
				}

				$mailer->IsHTML(true);
				$mailer->AddAttachment($fn,sprintf("consolidated-%s.pdf",$invoices[0]["invoice_date"]));
				$mailer->Subject = sprintf("%s - KJV Courier Consolidated Invoice %s", $member["company"], $invoices[0]["invoice_date"]);
				if (!$mailer->Send())
					$this->logMessage(__FUNCTION__,sprintf("mailer [%s] [%s]", print_r($mailer->ErrorInfo,true), print_r($mailer,true)),1,true);
				else
					$this->logMessage(__FUNCTION__,sprintf("email sent [%s]", print_r($mailer,true)),1,true);
				unlink($fn);


			}
		}
		return $rows;
	}

    /**
     * @return array|mixed|string|string[]
     * @throws phpmailerException
     */
    function orderAuthorizations() {
		if (!$module = $this->getModule())
			return "";
		$outer = new Forms();
		$outer->setModule($module);
		$outer->init($this->m_dir.$module['outer_html']);
		$inner = new Forms();
		$inner->init($this->m_dir.$module['inner_html']);
		$flds = $inner->buildForm($this->config->getFields($module['configuration']));
		$rows = array();
		$o_id = array_key_exists("o_id",$_REQUEST) ? $_REQUEST["o_id"] : 0;
		$auth = $this->fetchAllTest("select * from order_authorization where order_id = %d order by id", $o_id);
		$rows = array();
		foreach($auth as $k=>$v) {
			$inner->addData($v);
			$rows[] = $inner->show();
		}
		if (count($rows) > 0) {
			$outer->setData("authorizations",implode("",$rows));
			return $outer->show();
		}
		else return "";
	}

    /**
     * @return array|mixed|string|string[]
     * @throws phpmailerException
     */
    function modifiedByDriver() {
		if (!$module = $this->getModule())
			return "";
		$outer = new Forms();
		$outer->setModule($module);
		$outer->init($this->m_dir.$module['outer_html']);
		$inner = new Forms();
		$inner->init($this->m_dir.$module['inner_html']);
		$flds = $inner->buildForm($this->config->getFields($module['configuration']));
		$rows = array();
		$o_id = array_key_exists("o_id",$_REQUEST) ? $_REQUEST["o_id"] : 0;
		$auth = $this->fetchAllTest("select distinct o.* from orders o, custom_delivery cd where o.order_status & %d != 0 and cd.order_id = o.id and ((cd.completed = 0 and date(cd.scheduled_date) = curdate()) or (cd.completed = 1 and date(cd.actual_date) = curdate())) order by o.id", STATUS_NEEDS_APPROVAL | STATUS_DRIVER_CHANGED);
		$rows = array();
		foreach($auth as $k=>$v) {
			$v["order_status"] = $v["order_status"] & ~(STATUS_PROCESSING | STATUS_SHIPPED);
			$v = $this->formatOrder($v);
			$v["customer"] = $this->fetchSingleTest("select * from members where id = %d", $v["member_id"]);
			$v["notes_br"] = nl2br($v["notes"]);
			$v["pickup"] = $this->fetchSingleTest("select cd.*, m.firstname, m.lastname from custom_delivery cd left join drivers d on d.id = cd.driver_id left join members m on m.id = d.member_id where order_id=%d and service_type ='P'", $v["id"]);
			$v["pickup"]["address"] = Address::formatData($this->fetchSingleTest("select * from addresses where ownertype='order' and ownerid = %d and addresstype = %d", $v["id"], ADDRESS_PICKUP));
			$v["delivery"] = $this->fetchSingleTest("select cd.*, m.firstname, m.lastname from custom_delivery cd left join drivers d on d.id = cd.driver_id left join members m on m.id = d.member_id where order_id=%d and service_type ='P'", $v["id"]);
			$v["delivery"]["address"] = Address::formatData($this->fetchSingleTest("select * from addresses where ownertype='order' and ownerid = %d and addresstype = %d", $v["id"], ADDRESS_DELIVERY));
			$this->logMessage(__FUNCTION__,sprintf("*** Data [%s]", print_r($v,true)),1);
			$inner->addData($v);
			$rows[] = $inner->show();
		}
		if (count($rows) > 0) {
			$outer->setData("orders",implode("",$rows));
			return $outer->show();
		}
		else return "";
	}

    /**
     * @param $data
     * @param $obj
     * @param $key
     * @param $req
     * @param $errors
     * @param $messages
     * @return bool
     * @throws phpmailerException
     */
    function checkName($data, $obj, $key, $req, &$errors, &$messages) {
		$this->logMessage(__FUNCTION__,sprintf("data [%s] obj [%s] key [%s] errors [%s] messages [%s]", print_r($data,true), print_r($obj,true), $key, print_r($req,true), print_r($errors,true), print_r($messages,true)),3);
		if (!array_key_exists($key,$data)) {
				$errors[$key] = "*";
				$messages[] = array("isRequired"=>"Placed By");
				return false;
		}
		if (strlen($data[$key]) < 3) {
			$errors[$key] = "*";
			$messages[] = array("notLongEnough"=>"Placed By");
			return false;
		}
		return true;
	}

    /**
     * @param $data
     * @param $obj
     * @param $key
     * @param $req
     * @param $errors
     * @param $messages
     * @return bool
     * @throws phpmailerException
     */
    function notSpaces($data, $obj, $key, $req, &$errors, &$messages) {
		$this->logMessage(__FUNCTION__,sprintf("data [%s] obj [%s] key [%s] errors [%s] messages [%s]", print_r($data,true), print_r($obj,true), $key, print_r($req,true), print_r($errors,true), print_r($messages,true)),1);
		if (!array_key_exists($key,$data)) {
				$errors[$key] = "*";
				$messages[] = array("isRequired"=>$req->getPrettyName($obj->getAllData()));
				return false;
		}
		$test = preg_replace('/[^a-z0-9]/','',strtolower(sprintf("%s%s%s", $data[$key], array_key_exists('firstname',$data) ? $data["firstname"] : '', array_key_exists('lastname',$data) ? $data["lastname"] : '')));
		$this->logMessage(__FUNCTION__,sprintf("test [%s] preg [%s]", $test, preg_replace('/[^a-z0-9]/','',$test)),1);
		if (strlen(trim($test)) < 3) {
			$errors[$key] = "*";
			$messages[] = array("notLongEnough"=>$req->getPrettyName($obj->getAllData()));
			return false;
		}
		return true;
	}

    /**
     * @param $data
     * @param $obj
     * @param $key
     * @param $req
     * @param $errors
     * @param $messages
     * @return bool
     * @throws phpmailerException
     */
    function dryIceWeight($data, $obj, $key, $req, &$errors, &$messages) {
		$hasIce = false;
		$wt = 0;
		if ($this->checkArray("extras", $data)) {
			foreach($data["extras"] as $k=>$v) {
				if ($v == DRY_ICE) $hasIce = true;
			}
		}
		if ($hasIce) {
			if ($data[$key] <= 0) {
				$errors[$key] = "*";
				$messages[] = array("isRequired"=>$req->getPrettyName($obj->getAllData()));
				return false;
			}
		}
		return true;
	}

    /**
     * @param $data
     * @param $obj
     * @param $key
     * @param $req
     * @param $errors
     * @param $messages
     * @return bool
     */
    function checkEmail($data, $obj, $key, $req, &$errors, &$messages) {
		if (!array_key_exists($key,$data)) return true;
		$tmp = explode(";",$data[$key]);
		foreach($tmp as $e1=>$e2) {
			if (!preg_match('/^\S+@[\w\d.-]{2,}\.[\w]{2,6}$/iU', $e2)) {
				$errors[$key] = "*";
				$messages[] = array("email"=>sprintf("Email To (%s)",$e2));
				return false;
			}
		}
		return true;
	}

    /**
     * @return array|mixed|string|string[]
     * @throws phpmailerException
     */
    function startOver() {
		if (!$module = $this->getModule())
			return "";
		$outer = new Forms();
		$outer->setModule($module);
		$outer->init($this->m_dir.$module['outer_html']);
		if (array_key_exists(__FUNCTION__,$_REQUEST)) {
			$_SESSION["cart"] = Ecom::initCart();
			$outer->init($this->m_dir.$module["parm1"]);
		}
		return $outer->show();
	}

    /**
     * @return array|mixed|string|string[]
     * @throws phpmailerException
     */
    function subsidiaryInvoices() {
		if (!$module = $this->getModule())
			return "";
		$outer = new Forms();
		$outer->setModule($module);
		$outer->init($this->m_dir.$module['outer_html']);
		$flds = $outer->buildForm($this->config->getFields($module['configuration']));
		$inner = new Forms();
		$inner->setModule($module);
		$inner->init($this->m_dir.$module['inner_html']);
		$flds = $inner->buildForm($this->config->getFields($module['configuration']."Row"));
		if (count($_POST) > 0 && array_key_exists(__FUNCTION__,$_POST)) {
			$outer->addData($_POST);
			if ($outer->validate()) {

				$outer = $this->emailInvoices($_REQUEST, $module, $outer);

				$recs = $this->fetchAllTest("select qb.id, qb_invoice_id, count(o.id) as ct, sum(o.total) as total, m.company 
from qb_export qb, qb_export_dtl qbd, orders o, members m where qb.invoice_date = '%s' and qbd.qb_export_id = qb.id and o.id = qbd.order_id and m.id = o.member_id and qb.consolidate_id = %d
group by qb.qb_invoice_id order by company", $outer->getData("invoice_date"), $this->getUserInfo("id"));
				$rows = array();
				$inv_ct = 0;
				$inv_tot = 0;
				$ord_ct = 0;
				foreach($recs as $k=>$v) {
					$inner->addData($v);
					$rows[] = $inner->show();
					$ord_ct += $v["ct"];
					$inv_tot += $v["total"];
					$inv_ct += 1;
				}
				$outer->setData("invoice_count",$inv_ct);
				$outer->setData("invoice_total",$inv_tot);
				$outer->setData("order_count",$ord_ct);
				$outer->setData("invoices", implode("",$rows));
			}
		}
		return $outer->show();
	}

    /**
     * @param $saveAsFile
     * @param $filename
     * @return string|void
     * @throws \Pdfcrowd\Error
     * @throws phpmailerException
     */
    function consolidatedPDF($saveAsFile = false, $filename = "" ) {
		if (!$module = parent::getModule())
			return "";
		$outer = new Forms();
		$outer->init($this->m_dir.$module['outer_html']);
		$flds = $outer->buildForm($this->config->getFields($module['configuration']));
		$outer->setModule($module);
		$inner = new Forms();
		$inner->init($this->m_dir.$module['inner_html']);
		$flds = $inner->buildForm($this->config->getFields($module['configuration']."Row"));
		$d_id = array_key_exists("d_id",$_REQUEST) ? $_REQUEST["d_id"] : date("Y-m-d");
		$r_id = array_key_exists("r_id",$_REQUEST) ? $_REQUEST["r_id"] : 0;
		if ($r_id == 0) {
			$member = $this->fetchSingleTest("select m.* from members m where id = %d", $this->getUserInfo("id"));
		}
		else {
			$member = $this->fetchSingleTest("select m.* from members m, qb_export i where i.rand_id = %d and i.invoice_date = '%s' and m.id = i.consolidate_id", $r_id, $d_id);
		}
		if (!is_array($member)) return "";
		$member["address"] = ($addr = $this->fetchSingleTest("select a.* from addresses a, code_lookups l where a.ownerid = %d and a.ownertype='member' and a.addresstype = l.id and l.type='memberAddressTypes' and l.id = %d and a.deleted = 0", $member["id"], ADDRESS_COMPANY)) ? Address::formatData($addr) : array();
		$outer->addData($member);
/*
		$pdf = new TCPDF('P', PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
		$pdf->SetCreator(PDF_CREATOR);
		$pdf->SetAuthor('KJV Courier');
		$pdf->SetTitle(sprintf('Invoice-%s',date("d-M-Y", strtotime($d_id))));
		$pdf->SetSubject('KJV Courier Invoice');
		$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
		$pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));
		$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
		$pdf->SetMargins(5, 8, 5, 8);
		$pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
		$pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
		$pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
		if (@file_exists(dirname(__FILE__).'/lang/eng.php')) {
			require_once(dirname(__FILE__).'/lang/eng.php');
			$pdf->setLanguageArray($l);
		}
		$pdf->SetFont('Courier', '', 10);
		$pdf->AddPage();
		$pdf->setCellPaddings(0,0,0,0);
		$pdf->setCellMargins(0,0,0,0);
		$pdf->SetFillColor(255, 255, 127);
*/
		if ($r_id == 0) {
			$sql = sprintf("select qb.*, m.*, count(o.id) as ct, sum(o.value) as subtotal, sum(o.taxes) as taxes, sum(o.total) as total from qb_export qb, members m, orders o, qb_export_dtl qbd where qb.invoice_date = '%s' and m.id = qb.member_id and qbd.qb_export_id = qb.id and o.id = qbd.order_id and m.custom_parent_org = %d and qb.consolidate_id = %d and o.order_status & %d = 0 group by qb.id order by qb.id", $d_id, $this->getUserInfo("id"), $this->getUserInfo("id"), STATUS_CANCELLED);
		}
		else {
			$sql = sprintf("select qb.*, m.*, count(o.id) as ct, sum(o.value) as subtotal, sum(o.taxes) as taxes, sum(o.total) as total from qb_export qb, members m, orders o, qb_export_dtl qbd where qb.invoice_date = '%s' and m.id = qb.member_id and qbd.qb_export_id = qb.id and o.id = qbd.order_id and m.custom_parent_org = %d and qb.consolidate_id = %d and o.order_status & %d = 0 group by qb.id order by qb.id", $d_id, $member["id"], $member["id"], STATUS_CANCELLED);
		}
		$recs = $this->fetchAllTest($sql);
		$rows = array();
		$ct = 0;
		$taxes = 0;
		$total = 0;
		$subtotal = 0;
		foreach($recs as $k=>$v) {
			$ct += 1;
			$total += $v["total"];
			$subtotal += $v["subtotal"];
			$taxes += $v["taxes"];
			$inner->addData($v);
			$rows[] = $inner->show();
		}
		$this->logMessage(__FUNCTION__,sprintf("rows [%s]", print_r($rows,true)),3);
		$outer->setData("count",$ct);
		$outer->setData("total",$total);
		$outer->setData("subtotal",$subtotal);
		$outer->setData("taxes",$taxes);
		$outer->setData("invoices",implode("",$rows));
		if (count($recs) > 0) $outer->setData("qb_invoice_id", $recs[0]["qb_invoice_id"]);
/*
		$pdf->writeHTML($outer->show(), true, false, true, false, '');
		$pdf->lastPage();
*/
		$html = $outer->show();
		try {
			// create the API client instance
			$client = new \Pdfcrowd\HtmlToPdfClient($GLOBALS["pdfcrowd"]["user"], $GLOBALS["pdfcrowd"]["token"]);
			// run the conversion and write the result to a file
			if ($saveAsFile) {
				$tmp = $client->convertStringToFile($html, $filename);
				return $tmp;
			}
			else {
				ob_clean();
				$pdf = $client->convertString($html);
				header('Content-Type: application/pdf');
				header('Cache-Control: no-cache');
				header('Accept-Ranges: none');
				header('Content-Length: '.strlen($pdf));
				header(sprintf('Content-Disposition: filename="%s"',sprintf('invoice-%d.pdf',$outer->getData("qb_invoice_id"))));
				echo $pdf;
				exit;
			}
		}
		catch(\Pdfcrowd\Error $why) {
			// report the error
			error_log("Pdfcrowd Error: {$why}\n");
			// rethrow or handle the exception
			throw $why;
		}

/*
		try {
			if ($saveAsFile) {
				return $pdf->Output($filename, 'F');
			}
			else {
				ob_clean();
				$pdf->Output(sprintf('invoice-%s.pdf',date("d-M-Y", strtotime($d_id))), 'I');
			}
		}
		catch(Exception $err) {
			return print_r($err,true);
		}
*/
	}

    /**
     * @param $request
     * @param $module
     * @param $form
     * @return mixed
     * @throws phpmailerException
     */
    function emailInvoices($request, $module, $form ) {
		$this->logMessage(__FUNCTION__, sprintf("request [%s] module [%s] form [%s]", print_r($request,true), print_r($module,true), print_r($form,true)),1);
		$currentLogin = $_SESSION;
		$pdf = array();
		$emails = $form->getData("email");
		if (is_array($emails)) {
			foreach($emails as $k=>$v) {
				if ($v==1) {
					$member = $this->fetchSingleTest("select m.* from members m, qb_export i where i.id = %d and m.id = i.member_id", $k);
					$this->m_module = array();
					$this->config = new Custom(0);
					//$this->logMeIn($member["username"],$member["password"],0,$member["id"]);
					$sql = sprintf('select p.*, t.module_function, m.classname, m.id as module_id from modules_by_page p left join fetemplates t on t.id = p.fetemplate_id left join modules m on m.id = t.module_id where p.page_type = "P" and p.page_id = %d and p.module_name = "main1"', $this->getOption("page_id"));
					$i_module = $this->fetchSingleTest($sql);
					$class = new Custom($i_module['id'],$i_module);
					$class->config = new Custom(0);
					$_REQUEST["i_id"] = $k;
					$invoice = $this->fetchSingleTest("select * from qb_export where id = %d", $k);
					$fn = sprintf($_SERVER['DOCUMENT_ROOT']."/files/invoice-%d.pdf",$invoice["qb_invoice_id"]);
					$tmp = $class->{$i_module['module_function']}(true, $fn);
					$pdf[] = array(
						"id"=>$invoice["qb_invoice_id"],
						"path"=>$fn,
						"content"=>$tmp
					);
				}
			}
		}
		if (array_key_exists("send_consolidated",$request) && $request["send_consolidated"] == 1) {
			$this->m_module = array();
			$this->config = new Custom(0);
			$sql = sprintf('select p.*, t.module_function, m.classname, m.id as module_id from modules_by_page p left join fetemplates t on t.id = p.fetemplate_id left join modules m on m.id = t.module_id where p.page_type = "P" and p.page_id = %d and p.module_name = "main1"', $this->getOption("consolidated_id"));
			$i_module = $this->fetchSingleTest($sql);
			$class = new Custom($i_module['id'],$i_module);
			$class->config = new Custom(0);
			$invoice = $this->fetchSingleTest("select * from qb_export where invoice_date = '%s'", $form->getData("invoice_date"));
			$fn = sprintf($_SERVER['DOCUMENT_ROOT']."/files/invoice-%s.pdf",date("d-M-Y",strtotime($invoice["invoice_date"])));
			$_REQUEST["d_id"] = $invoice["invoice_date"];
			$pdf[] = array(
				"id"=>$invoice["qb_invoice_id"],
				"path"=>$fn,
				"content"=>$class->{$i_module['module_function']}(true, $fn)
			);
		}
		$this->logMessage(__FUNCTION__,sprintf("pdf count [%d] data [%s]", count($pdf), print_r($pdf,true)),1);
		if (count($pdf) > 0) {
			$mailer = new MyMailer();
			$mailer->Subject = sprintf("%s - %s", $module["parm1"], SITENAME);
			$body = new Forms();
			$sql = sprintf('select * from htmlForms where class = %d and type = "%s"',$this->getClassId('custom'), $module["parm1"]);
			$html = $this->fetchSingleTest($sql);
			$body->setHTML($html['html']);
			$body->setOption('formDelimiter','{{|}}');
			$body->addData($request);
			$mailer->Body = $body->show();
			if (defined("DEV") && DEV==1) {
				$mailer->From = "noreply@".HOSTNAME;
				$mailer->FromName = SITENAME;
				$mailer->addAddress("ian@kjvcourier.com","Ian MacArthur (kjv)");
				$mailer->addAddress("ian.w.macarthur@gmail.com","Ian MacArthur (gmail)");
				$mailer->addAddress("ian.macarthur@live.ca","Ian MacArthur (live)");
/*
				foreach(explode(";",$form->getData("email_to")) as $e1=>$e2) {
					$this->logMessage(__FUNCTION__,sprintf("adding %s", $e2),1);
					$mailer->addAddress($e2);
				}
*/
			}
			else {
				$mailer->From = "noreply@".HOSTNAME;
				$mailer->FromName = SITENAME;
				foreach(explode(";",$form->getData("email_to")) as $e1=>$e2) {
					$this->logMessage(__FUNCTION__,sprintf("adding %s", $e2),1);
					$mailer->addAddress($e2);
				}
			}
			if (strlen($form->getData("cc_to"))>0) {
				foreach(explode(";",$form->getData("cc_to")) as $e1=>$e2) {
					$mailer->addAddress($e2);
				}
			}
			$mailer->IsHTML(true);
			foreach($pdf as $sk=>$sv) {
				$tmp = explode("/",$sv["path"]);
				$mailer->AddAttachment($sv["path"],$tmp[count($tmp)-1]);
			}
			$mailer->Subject = $module["parm2"];
			if (!$mailer->Send()) {
				$this->logMessage(__FUNCTION__,sprintf("mailer [%s] [%s]", print_r($mailer->ErrorInfo,true), print_r($mailer,true)),1);
				$form->addFormError(sprintf("Send Failed [%s]", $mailer->ErrorInfo));
			}
			else $form->addFormSuccess(sprintf("Sent %d invoices", count($pdf)));
			foreach($pdf as $sk=>$sv) {
				unlink($sv["path"]);
			}
		}
		return $form;
	}

    /**
     * @return array|mixed|string|string[]
     * @throws phpmailerException
     */
    function incompletes() {
		if (!$module = $this->getModule()) return "";
		$outer = new Forms();
		$outer->init($this->m_dir.$module['outer_html']);
		$flds = $outer->buildForm($this->config->getFields($module['configuration']));
		$outer->setModule($module);
		$inner = new Forms();
		$inner->init($this->m_dir.$module['inner_html']);
		$flds = $inner->buildForm($this->config->getFields($module['configuration']."Row"));		
		$recs = $this->fetchAllTest(sprintf("select o.id, a.id, ol.id, ol.custom_package, p.id, m.*, a.phone1, a.email, o.id, p.name, p.custom_color_coding 
		from members m, addresses a, orders o, order_lines ol, product p, code_lookups l 
		where o.order_status_incomplete and m.id = o.member_id and a.ownerid=m.id and a.ownertype='member' and ol.order_id = o.id and ol.custom_package = 'S' and p.id = ol.product_id 
		and l.id = a.addresstype and l.code = 'Company' and ol.deleted = 0 and DATE_FORMAT(o.order_date,'%%Y-%%m-%%d') = CURDATE()
		order by o.id desc limit %d", $module["records"]));
		$rows = array();
		foreach($recs as $k=>$v) {
			$inner->addData($v);
			$rows[] = $inner->show();
		}
		$outer->setData("orders",implode("",$rows));
		$outer->setData("ct",count($rows));
		return $outer->show();
	}

    /**
     * @return array|mixed|string|string[]
     * @throws phpmailerException
     */
    function driverQueue() {
		if (!$module = $this->getModule()) return "";
		$outer = new Forms();
		$outer->init($this->m_dir.$module['outer_html']);
		$flds = $outer->buildForm($this->config->getFields($module['configuration']));
		$outer->setModule($module);
		$inner = new Forms();
		$inner->init($this->m_dir.$module['inner_html']);
		$flds = $inner->buildForm($this->config->getFields($module['configuration']."Row"));
		if (count($_POST) > 0 && array_key_exists(__FUNCTION__,$_POST)) {
			$outer->addData($_POST);
			$d_type = $outer->getData("driverType");
		}
		else $d_type = 1;
		$sub = new Forms();
		$sub->init($this->m_dir.$module['parm1']);
		$flds = $sub->buildForm($this->config->getFields($module['configuration']."Row"));
		$opts = array(
			"a"=>"d.enabled = 1",
			"b"=>"d.deleted = 0",
			"c"=>"m.id = d.member_id",
			"d"=>"m.deleted = 0",
			"e"=>"cd.driver_id = d.id",
			"f"=>"cd.completed = 0",
			"g"=>"o.id = cd.order_id",
			"h"=>"o.order_status_processing",
			"i"=>"ol.order_id = o.id",
			"j"=>"ol.custom_package = 'S'",
			"k"=>"p.id = ol.product_id",
			"l"=>"ol.deleted = 0",
			"m"=>"o.deleted = 0",
			"n"=>sprintf("cd.scheduled_date <= '%s 23:59:29'", date("Y-m-d")),
			"o"=>"a.ownerid = o.id",
			"p"=>"a.ownertype = 'order'",
			"q"=>sprintf("IF(cd.service_type='P', a.addresstype=%d, a.addresstype=%d)", ADDRESS_PICKUP, ADDRESS_DELIVERY),
			"r"=>"d.third_party = 0",
			"s"=>sprintf("o.order_status & %d = 0", STATUS_CANCELLED)
		);
		if ($d_type != 0) {
			$opts["t"] = sprintf("p.custom_same_day = %d", $d_type == 1 ? 1 : 0);
		}
		if (($d_id = $outer->getData("driver_id")) > 0) {
			$opts["u"] = sprintf("d.id = %d", $d_id);
		}
		if (($g_id = $outer->getData("group_id")) > 0) {
			$opts["v"] = sprintf("d.group_id = %d", $g_id);
		}
		$addrCheck = $outer->getData("address");
		$outer->validate();
		$drivers = $this->fetchAllTest("select m.company, m.firstname, m.lastname, cd.*, o.id, a.postalcode, a.line1, a.city, p.custom_same_day, p.custom_color_coding from drivers d, members m, custom_delivery cd, orders o, order_lines ol, product p, addresses a where %s order by m.company, cd.driver_sequence", implode(" and ",$opts));
		$recs = array();
		$d_id = 0;
		$ct = 0;
		$addresses = array();
		foreach($drivers as $k=>$v) {
			$v["line1"] = strtolower($v["line1"]);
			$v["postalcode"] = strtolower($v["postalcode"]);
			$v["firstname"] = strtolower($v["firstname"]);
			$v["lastname"] = strtolower($v["lastname"]);
			if ($d_id != $v["driver_id"]) {
				if ($d_id != 0) {
					$inner->setData("addresses",implode("",$addresses));
					$inner->setData("ct",count($addresses));
					$recs[] = $inner->show();
				}
				$addresses = array();
				$inner->addData($v);
			}
			if (strlen($addrCheck) > 0) {
				$v["line1"] = $this->highlight(array($addrCheck),$v["line1"]);
				$v["postalcode"] = $this->highlight(array($addrCheck),$v["postalcode"]);
			}
			$sub->addData($v);
			$addresses[] = $sub->show();
			$d_id = $v["driver_id"];
			//$opts["s"] = sprintf("d.id = %d", $v["driver_id"]);
			//$addresses = $this->fetchAllTest("select m.firstname, m.lastname, cd.*, o.id, a.line1, a.city from drivers d, members m, custom_delivery cd, orders o, order_lines ol, product p, addresses a where %s order by m.lastname, cd.driver_sequence", implode(" and ",$opts));
			//$inner->addData($addresses[0]);
			//$rows = array();
			//foreach($addresses as $sk=>$sv) {
			//	$sub->addData($sv);
			//	$rows[] = $sub->show();
			//}
			//$inner->setData("addresses",implode("",$rows));
			//$inner->setData("ct", count($addresses));
			//$recs[] = $inner->show();
		}
		$inner->setData("addresses",implode("",$addresses));
		$inner->setData("ct",count($addresses));
		$recs[] = $inner->show();
		$outer->setData("drivers",implode("",$recs));

		$subdata = $this->subForms($this->m_module['fetemplate_id'],null,array(),'outer');
		$this->logMessage(__FUNCTION__,sprintf('subforms [%s]',print_r($subdata,true)),2);
		foreach($subdata as $key=>$value) {
			$outer->addTag($key,$value,false);
		}


		return $outer->show();
	}

    /**
     * @return array|mixed|string|string[]
     * @throws phpmailerException
     */
    function isOfficeClosed() {
		if (!$module = $this->getModule()) return "";
		$outer = new Forms();
		$outer->init($this->m_dir.$module['outer_html']);
		$flds = $outer->buildForm($this->config->getFields($module['configuration']));
		$outer->setModule($module);
		if (!$stat = $this->fetchSingleTest("select os.*, cl.extra as msg from office_schedule os left join code_lookups cl on cl.code = os.stock_msg and cl.type = 'closed_messages' where office_date = '%s'", date("Y-m-d"))) {
			$stat = array("closed"=>0, "close_time"=>"00:00:00");
		}
		$stat["weekend"] = (date("w") == 0 || date("w") == 6 || $stat["closed"] == 1 || ($stat["close_time"] != "00:00:00"));
		$stat["weekend"] = (date("w") == 0 || date("w") == 6 || $stat["closed"] == 1 || ($stat["close_time"] != "00:00:00" && $stat["close_time"] < date("H:i:s")));
		$stat["active"] = $stat["close_time"] < date("H:i:s");
		$stat["pending"] = !$stat["active"] & ($stat["close_time"] > date("H:i:s",strtotime("-2 hours")));
		$stat["formattedTime"] = date("h:i A", strtotime($stat["close_time"]));
		$this->logMessage(__FUNCTION__,sprintf("stat [%s] time [%s] 2 hours [%s]", print_r($stat,true), date("H:i:s"), date("H:i:s",strtotime("-2 hours"))),1);
		$outer->addData($stat);
		return $outer->show();
	}

    /**
     * @return array|mixed|string|string[]
     * @throws phpmailerException
     */
    function getAQuote() {
		if (!$module = $this->getModule()) return "";
		$outer = new Forms();
		$outer->init($this->m_dir.$module['outer_html']);
		$outer->setModule($module);
		if (0 == $this->getUserInfo("id")) {
			$m_id = PLACEHOLDER_ACCOUNT;
		}
		else {
			$m_id = $this->getUserInfo("id");
		}
		$group = $this->fetchSingleTest("select mf.* from members_folders mf, members_by_folder mb where mb.member_id = %d and mf.id = mb.folder_id",$m_id);
		$g_id = $group["id"];	//$this->fetchScalarTest("select folder_id from members_by_folder where member_id = %d",$m_id);
		$flds = $this->config->getFields($module["configuration"]);
		if ($this->getUserInfo("custom_additional_services") > 0)
			$flds["extras"]["sql"] = sprintf('select p.id,name from product p, product_by_folder pf where pf.folder_id = %d and p.id = pf.product_id and p.deleted = 0 and p.published = 1 and p.enabled = 1 order by pf.sequence', $this->getUserInfo("custom_additional_services"));
		else
			$flds["extras"]["sql"] = sprintf('select p.id,name from product p, product_by_folder pf where pf.folder_id = %d and p.id = pf.product_id and p.deleted = 0 and p.published = 1 and p.enabled = 1 order by pf.sequence', $group["custom_additional_services"]);
		$m_services = $this->fetchAllTest("select c.id, p.name, c.product_id from product p, custom_member_product_options c, members_by_folder mf where p.id = c.product_id and c.member_id = mf.id and mf.member_id = %d and mf.folder_id = %d and isgroup = 0 and is_fedex = 0 and p.deleted = 0 and p.published = 1 and p.enabled = 1",$m_id,$g_id);
		$tmp = array(0);
		foreach($m_services as $key=>$value) {
			$tmp[] = $value["product_id"];
		}
		$g_services = $this->fetchAllTest("select c.id, p.name, c.product_id from product p, custom_member_product_options c where p.id = c.product_id and c.member_id = %d and isgroup = 1 and p.id not in (%s) and is_fedex = 0 and p.deleted = 0 and p.published = 1 and p.enabled = 1",$g_id, implode(",",array_merge(array(0),$tmp)));
		$services = array_merge($m_services, $g_services);
		$result = array();
		foreach($services as $key=>$rec) {
			$result[$rec["id"]] = $rec["name"];
		}
		$flds = $this->config->getFields($module['configuration']);
		$flds["serviceType"]["options"] = $result;
		$flds["product_id"]["sql"] = sprintf("select p.id, p.name, p.id as xxx from product p, product_by_folder pf, members_folders m, members_by_folder mf where mf.member_id = %d and m.id = mf.folder_id and pf.folder_id = m.custom_package_types and p.id = pf.product_id and p.enabled = 1 and p.deleted = 0 order by pf.sequence",$m_id);

		$flds = $outer->buildForm($flds);
		if (array_key_exists(__FUNCTION__,$_POST)) {
			$outer->addData($_POST);
			if ($outer->validate()) {
				if (0 == $this->getUserInfo("id")) {
					$this->logMeIn("","",0,PLACEHOLDER_ACCOUNT);
				}
				$cart = $_SESSION["cart"];
				$cart["header"] = array(
					"pickup_datetime" => DATE(DATE_ATOM),
					"custom_declared_value" => 0,
					"custom_weight_code" => $_REQUEST["custom_weight_code"],
					"custom_dimension_code" => $_REQUEST["custom_dimension_code"],
					"custom_override_price"=>array_key_exists("custom_override_price",$_REQUEST) ? $_REQUEST["custom_override_price"] : 0.00,
					"shipping"=>0,
					"handling"=>0,
					"points_redeemed"=>0,
					"points_collected"=>0,
					"redemption_value"=>0,
					"discount_type" => ""
				);
				$cart["addresses"]["shipping"] = array("postalcode" => $_REQUEST["to"],"line1"=>"","city"=>"","province_id"=>0,"country_id"=>0,"id"=>0);
				$cart["addresses"]["pickup"] = array("postalcode" => $_REQUEST["from"],"line1"=>"","city"=>"","province_id"=>0,"country_id"=>0,"id"=>0);
				$_REQUEST["prod"][1] = array(
					"product_id"=>19,
					"quantity"=>$_REQUEST["quantity"],
					"custom_weight"=>$_REQUEST["weight"],
					"dimensions" => array()
				);
				$_REQUEST["prod"][1]["dimensions"][1] = array(
					"quantity"=>$_REQUEST["quantity"],
					"weight"=>$_REQUEST["weight"],
					"height"=>$_REQUEST["height"],
					"width"=>$_REQUEST["width"],
					"depth"=>$_REQUEST["depth"]
				);
				$_REQUEST["pickup_datetime"] = date("m/d/Y");
				$_REQUEST["pickup_datetime_hh"] = date("h");
				$_REQUEST["pickup_datetime_mm"] = date("i");
				$_REQUEST["pickup_datetime_ampm"] = date("a");
				$tmp = $_SESSION;
				$_SESSION["quote"] = array("custom_declared_value"=>0);
				$_SESSION["cart"] = $cart;
				$quote = $this->getPrice($_REQUEST["prod"], array(), $cart["addresses"]["pickup"], $cart["addresses"]["shipping"], $_REQUEST["serviceType"], $cart["header"]["custom_weight_code"], $cart["header"]["custom_dimension_code"]);
				$inner = new Forms();
				$inner->init($this->m_dir.$module['inner_html']);
				$subdata = $this->subForms($this->m_module['fetemplate_id'],null,array(),'inner');
				$this->logMessage(__FUNCTION__,sprintf('subforms [%s]',print_r($subdata,true)),3);
				foreach($subdata as $key=>$value) {
					$inner->addTag($key,$value,false);
				}
				$outer->setData("result",$inner->show());
				$_SESSION = $tmp;
			}
		}
		return $outer->show();
	}


    /**
     * @return array|mixed|string|string[]
     * @throws phpmailerException
     */
    function idleDrivers() {
		if (!$module = $this->getModule())
			return "";
		$outer = new Forms();
		$outer->init($this->m_dir.$module['outer_html']);
		$outer->setModule($module);
		$inner = new Forms();
		$inner->init($this->m_dir.$module['inner_html']);
		$d = $this->fetchAllTest("select m.company, m.firstname, m.lastname from members m, drivers d 
where d.deleted = 0 and d.enabled and m.id = d.member_id and d.id not in (
	select c1.driver_id from custom_delivery c1, orders o where c1.completed = 0 and o.order_status_processing and o.id = c1.order_id and DATE(c1.scheduled_date) <= CURRENT_DATE UNION
	select c2.driver_id from custom_delivery c2, orders o where c2.completed = 0 and o.order_status_processing and o.id = c2.order_id and DATE(c2.scheduled_date) <= CURRENT_DATE
) order by m.company, lastname, firstname");
		$rows = array();
		foreach($d as $k=>$v) {
			$inner->addData($v);
			$rows[] = $inner->show();
		}
		$outer->setData("drivers", implode("",$rows));
		if (count($rows) > 0)
			return $outer->show();
		else return "";
	}

    /**
     * @return array|mixed|string|string[]
     * @throws phpmailerException
     */
    function driverLocations() {
		if (!$module = $this->getModule())
			return "";
		$outer = new Forms();
		$flds = $outer->buildForm($this->config->getFields($module['configuration']));
		$outer->setModule($module);
		$outer->init($this->m_dir.$module['outer_html']);
		$outer->setModule($module);
		$inner = new Forms();
		$flds = $inner->buildForm($this->config->getFields($module['configuration']."Row"));
		$inner->init($this->m_dir.$module['inner_html']);
		$outer->setData("routing",0);
		if (array_key_exists("driver_id",$_REQUEST) && (int)$_REQUEST["driver_id"] > 0) {
			$recs = $this->fetchAllTest("select dl.member_id, dl.datetime, dl.latitude, dl.longitude, m.company, m.firstname, m.lastname, cd.order_id, timediff(now(),datetime) as elapsed
from driver_location dl left join custom_delivery cd on cd.id = dl.delivery_id, members m 
where m.id = dl.member_id and date(datetime) = current_date() and dl.member_id = %d", $_REQUEST["driver_id"]);
			$outer->setData("routing",1);
		}
		else {
			$recs = $this->fetchAllTest("select d.vehicle_id, dl.member_id, dl.datetime, dl.latitude, dl.longitude, m.company, m.firstname, m.lastname, cd.order_id, timediff(now(),datetime) as elapsed 
from driver_location dl left join custom_delivery cd on cd.id = dl.delivery_id, members m, drivers d 
where d.member_id = m.id and m.id = dl.member_id and date(datetime) = current_date() and datetime = (
	select max(datetime) 
	from driver_location dl1 
	where dl1.member_id = dl.member_id
) group by dl.member_id");
		}
		$rows = array();
		foreach($recs as $k=>$v) {
			$inner->addData($v);
			$rows[] = $inner->show();
		}
		$outer->setData("rows", implode("",$rows));
		return $outer->show();
	}

    /**
     * @param $o_id
     * @return string[]
     * @throws phpmailerException
     */
    private function calcFreeItems($o_id) {
		$this->logMessage(__FUNCTION__,sprintf("Start with order #%d", $o_id),1);
		$p = $this->fetchAllTest("select ol.product_id, sum(old.quantity) as qty from order_lines ol, order_lines_dimensions old where ol.order_id = %d and ol.custom_package = 'P' and old.order_id = ol.order_id and old.line_id = ol.line_id group by ol.product_id", $o_id);
		$charges = 0;
		$member = $this->fetchSingleTest("select m.* from members m, orders o where o.id = %d and m.id = o.member_id", $o_id);
		$total = array();
		foreach($p as $k=>$v) {
			if ($fee = $this->fetchSingleTest("select * from member_package_charges where member_id = %d and product_id = %d", $member["id"], $v["product_id"])) {
				if ($v["qty"] - $fee["free"] > 0)
					$total[$v["product_id"]] = array("qty"=>$v["qty"] - $fee["free"],"charge"=>($v["qty"] - $fee["free"]) * $fee["additional_fee"]);
			}
			else {
				if ($member["custom_free_item_count"] > 0 && $v["qty"] - $member["custom_free_item_count"] > 0) {
					$total[0] = array("qty"=>$v["qty"] - $member["custom_free_item_count"],"charge"=>($v["qty"] - $member["custom_free_item_count"]) * $member["custom_free_item_charge"]);
				}
			}
		}
		$this->execute(sprintf("delete from order_lines where order_id = %d and product_id = %d", $o_id, ADDITIONAL_PIECE_CHARGES));
		if (count($total) > 0) {
			$ct = $this->fetchScalarTest("select max(line_id) from order_lines where order_id = %d", $o_id);
			$tot = 0;
			$val = 0;
			foreach($total as $k=>$v) {
				$tot += $v["qty"];
				$val += $v["charge"];
			}
			$pcLine = array("order_id"=>$o_id, "line_id"=>$ct+1, "product_id"=>ADDITIONAL_PIECE_CHARGES, "price"=>round($val/$tot,2), "quantity"=>$tot, "qty_multiplier"=>1, "value"=>round($val,2), 
				"total"=>round($val,2), "custom_package"=>"A", "discount_type"=>0, "tax_exemptions"=>"||","recurring_discount_type"=>0, "order_id"=>$o_id,
				"shipping"=>0, "shipping_only"=>0, "discount_rate"=>0, "recurring_shipping_only"=>0, "recurring_discount_rate" => 0 );
			$pcLine = Ecom::lineValue($pcLine);
			$this->logMessage(__FUNCTION__, sprintf("pcLine [%s]", print_r($pcLine,true)), 1);
			unset($pcLine["taxdata"]);
			$stmt = $this->prepare(sprintf("insert into order_lines(%s) values(%s?)", implode(", ", array_keys($pcLine)), str_repeat("?, ", count($pcLine)-1)));
			$stmt->bindParams(array_merge(array(str_repeat("s",count($pcLine))),array_values($pcLine)));
			$stmt->execute();
		}
		Ecom::recalcOrderFromDB($o_id);
		return array("line 1","line 2");
	}

    /**
     * @return array|mixed|string|string[]
     * @throws Exception
     */
    function gpsLocation() {
		if (!$module = $this->getModule())
			return "";
		$outer = new Forms();
		$flds = $outer->buildForm($this->config->getFields($module['configuration']));
		$outer->setModule($module);
		$outer->init($this->m_dir.$module['outer_html']);
		$outer->setModule($module);
		if (array_key_exists("latitude", $_POST) && abs($_POST["latitude"]) > .01 &&
				array_key_exists("longitude", $_POST) && abs($_POST["longitude"]) > .01) {
			if ($pos = $this->fetchSingleTest("select * from driver_location where member_id = %d order by datetime desc limit 1", $this->getUserInfo("id"))) {
				$d1 = new DateTime($pos["datetime"]);
				$d2 = new DateTime();
				$diff = $d1->diff($d2);
				$delay = (int)$this->getConfigVar("gpstiming","config");
				$add = (int)$diff->format("%i") >= $delay;
				$deltaLat = $pos["latitude"] - $_REQUEST["latitude"];
				$deltaLong = $pos["longitude"] - $_REQUEST["longitude"];
				$a = pow(sin(($deltaLat)/2),2) + cos($pos["latitude"])*cos($_REQUEST["latitude"]) * pow(sin($deltaLong/2),2);
				$c=2*atan2(sqrt($a),sqrt(1-$a));
				$d1km=6371*$c;
				$add |= abs($d1km) > .25;
			}
			else {
				$add = true;
			}
			if ($add || $outer->getData("recordAlways")) {
				$outer->addData($_POST);
				$outer->setData("datetime",date(DATE_ATOM));
				$outer->validate();
				$upd = array();
				foreach($flds as $k=>$v) {
					if (!(array_key_exists("database",$v) && $v["database"] == false)) {
						$upd[$k] = $outer->getData($k);
					}
				}
				$stmt = $this->prepare(sprintf("insert into driver_location(%s) values(?%s)", implode(", ", array_keys($upd)), str_repeat(", ?", count($upd)-1)));
				$stmt->bindParams(array_merge(array(str_repeat("s",count($upd))),array_values($upd)));
				$stmt->execute();
			}
		}
		return $outer->show();
	}

    /**
     * @return array|mixed|string|string[]
     * @throws phpmailerException
     */
    function showDriverRoute() {
		if (!$module = $this->getModule())
			return "";
		$outer = new Forms();
		$flds = $outer->buildForm($this->config->getFields($module['configuration']));
		$outer->setModule($module);
		$outer->init($this->m_dir.$module['outer_html']);
		$outer->setModule($module);
		if (array_key_exists("d_id", $_REQUEST) && $_REQUEST["d_id"] > 0) {
			$_REQUEST["driver"] = $this->fetchSingleTest("select m.* from members m, drivers d where m.id = d.member_id and d.id = %d", $_REQUEST["d_id"]);
			$outer->addData($_REQUEST);
			$outer->validate();
			if ($route = $this->fetchSingleTest("select * from custom_delivery_route where driver_id = %d and delivery_date = '%s'", $outer->getData("d_id"), date("Y-m-d"))) {
				$addr = explode("|",$route["delivery_ids"]);
				$this->logMessage(__FUNCTION__,sprintf("route addresses [%s]", print_r($addr,true)), 3);
				$result = array();
				$addresses = array();
				$inner = new Forms();
				$inner->setModule($module);
				$inner->init($this->m_dir.$module['inner_html']);
				foreach($addr as  $key=>$value) {
					if ($value > 0) {
						$address = $this->fetchSingleTest("select a.*, cd.order_id, cd.actual_date, p.custom_same_day from addresses a, custom_delivery cd, product p, order_lines ol where cd.id = %d and a.ownerid = cd.order_id and a.ownertype='order' and addresstype=if(cd.service_type='P',%d,%d) and ol.order_id = cd.order_id and ol.custom_package = 'S' and p.id = ol.product_id",$value,ADDRESS_PICKUP,ADDRESS_DELIVERY);
						if ($address["actual_date"] == "0000-00-00 00:00:00") {
							$addresses[$value] = $address;
							$tmp = array("address"=> Address::formatData($address),"sequence"=>$key);
							$inner->reset();
							$inner->addData($tmp);
							$result[] = $inner->show();
						}
					}
				}
				$outer->addTag("count",count($addr));
				$outer->addTag("addresses",implode("",$result),false);


				$inner = new Forms();
				$inner->setModule($module);
				$inner->init($this->m_dir.$module['parm1']);
				$r = json_decode($route["route"],true);
				$polyline = array();
				$this->logMessage(__FUNCTION__,sprintf("route is [%s]",print_r($r,true)),3);
				foreach($r["routes"]["features"][0]["geometry"]["paths"] as $key=>$stop) {
					foreach($stop as $subkey=>$leg) {
						$inner->reset();
						$inner->addData($leg);
						if ($key < 2)
							$this->logMessage(__FUNCTION__,sprintf("leg is [%s] inner is [%s]",print_r($leg,true),print_r($inner,true)),4);
						$polyline[] = $inner->show();
					}
				}
				$outer->addTag("route",implode("\n",$polyline));
			}
		}
		return $outer->show();
	}

	/**
	 * @param $data
	 * @param $obj
	 * @param $key
	 * @param $req
	 * @param $errors
	 * @param $messages
	 * @return bool
	 * @throws phpmailerException
	 */
    function customsDocs($data, $obj, $key, $req, &$errors, &$messages) {
		$cart = Ecom::getCart();
		if ($cart["addresses"]["shipping"]["country_id"] > 1 || $cart["addresses"]["pickup"]["country_id"] > 1) {
			if ((!array_key_exists($key, $data)) || strlen($data[$key]) <= 0) {
				$errors[$key] = "*";
				$messages[] = array("isRequired"=>$req->getPrettyName($obj->getAllData()));
				return false;
			}
		}
		return true;
	}

    /**
     * @param $data
     * @param $obj
     * @param $key
     * @param $req
     * @param $errors
     * @param $messages
     * @return bool
     * @throws phpmailerException
     */
    function optionalFieldCheck($data, $obj, $key, $req, &$errors, &$messages) {
		$opts = $this->fetchAllTest("select cv.* from code_lookups cv, member_optional_fields mof where mof.member_id = %d and cv.id = mof.optional_id", $this->getUserInfo("id"));		
		$form = new Forms();
		$form->setHTML("<%%errorMessage%%>");
		$flds = array();
		foreach($opts as $k=>$v) {
			$reqs = array("type"=>"hidden");
			foreach(explode("|",$v["extra"]) as $sk=>$sv) {
				$tmp = explode("^",$sv);
				$reqs[$tmp[0]] = $tmp[1];
			}
			$flds[$v["code"]] = $reqs;
		}
		$form->buildForm($flds);
		$form->addData($data);
		$form->validate();
		$errs = $form->getValidation();
		$this->logMessage(__FUNCTION__,sprintf("flds [%s] errors [%s] show [%s] messages [%s] errors [%s]", print_r($flds,true), print_r($errs,true), $form->show(), print_r($messages,true), print_r($errors,true)),1);
		if (count($errs) > 0) {
			$messages = array_merge($errs, $messages);
		}
		return true;
	}

    /**
     * @return array|mixed|string|string[]
     * @throws phpmailerException
     */
    function sanityCheck() {
		if (!$module = $this->getModule())
			return "";
		$outer = new Forms();
		$flds = $outer->buildForm($this->config->getFields($module['configuration']));
		$outer->setModule($module);
		$outer->init($this->m_dir.$module['outer_html']);
		$outer->setModule($module);
		$prods = $this->checkArray("cart:products", $_SESSION) ? $_SESSION["cart"]["products"] : array();
		$found = false;
		foreach($prods as $k=>$v) {
			$found |= (array_key_exists("custom_package", $v) && $v["custom_package"] == "S");
			$this->logMessage(__FUNCTION__,sprintf("^^^ found [%s] v [%s] session [%s]", $found, print_r($v,true), print_r($_SESSION,true)),1);
		}
		$outer->setData("foundService", $found);
		return $outer->show();
	}

    /**
     * @return false|string
     * @throws phpmailerException
     */
    function getModuleInfo() {
		return parent::getModuleList(array('shippingAddresses','pickupAddresses','setAddressType','checkSubsidiary','selectService','myAddresses','addressEditing','selectServiceDimensions','KJVService','FedExService','driverSchedule','pickupInfo','editPackageLine','editServiceLine','completePickup','pickupComments','completePickup','deliveryInfo','captureSignature','orderHistory','getDimensions','orderPayments','getSignature','driverPayments','waybill','resetDates','driverSearch','dispatching','overnightPickups','overnightDeliveries','accountSearch','recurring','outOfZoneOptions','addExtra','messageing','acknowledgement','dispatchAcks','liveExceptions','invoices','invoiceDetails','invoicePDF','ytdStats','mobileDetails','customOrderHistory','simulateDriver','billingReport','fedexStatus','invoicing','orderAuthorizations', 'modifiedByDriver','startOver','subsidiaryInvoices','consolidatedPDF','incompletes','driverQueue','isOfficeClosed','getAQuote','idleDrivers','driverLocations','gpsLocation','showDriverRoute','sanityCheck'));
	}

}

?>
