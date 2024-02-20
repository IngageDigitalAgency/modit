<?php

require_once './'.ADMIN.'/classes/Excel/PHPExcel.php';

/**
 * Members feature for backend
 */
class members extends Backend {

	private $m_tree = 'members_folders';
	private $m_content = 'members';
	private $m_junction = 'members_by_folder';
	private $m_media = 'members_media';
	private $m_pagination = 0;
	private $m_perrow = 15;

	/**
	 * @throws phpmailerException
	 */
	public function __construct() {
		$this->M_DIR = 'backend/modules/members/';
		$this->setTemplates(
			array(
				'deleteItem'=>$this->M_DIR.'forms/deleteItem.html',
				'deleteItemResult'=>$this->M_DIR.'forms/deleteItemResult.html',
				'main'=>$this->M_DIR.'members.html',
				'form'=>$this->M_DIR.'forms/form.html',
				'showContentTree'=>$this->M_DIR.'forms/contenttree.html',
				'membersInfo'=>$this->M_DIR.'forms/membersInfo.html',
				'folderProperties'=>$this->M_DIR.'forms/folder.html',
				'showFolderContent'=>$this->M_DIR.'forms/folderContent.html',
				'folderInfo'=>$this->M_DIR.'forms/folderInfo.html',
				'articleList'=>$this->M_DIR.'forms/articleList.html',
				'showSearchForm'=>$this->M_DIR.'forms/searchForm.html',
				'addContent'=>$this->M_DIR.'forms/addContent.html',
				'loadAddresses'=>$this->M_DIR.'forms/addressForm.html',
				'loadAddressesRow'=>$this->M_DIR.'forms/addressList.html',
				'editAddress'=>$this->M_DIR.'forms/editAddress.html',
				'editAddressSuccess'=>$this->M_DIR.'forms/editAddressSuccess.html',
				'getProfile'=>$this->M_DIR.'forms/getProfile.html',
				'header'=>$this->M_DIR.'forms/heading.html',
				'editProfile'=>$this->M_DIR.'forms/editProfile.html',
				'editProfileResult'=>$this->M_DIR.'forms/editProfileResult.html',
				'addContentResult'=>$this->M_DIR.'forms/addContentResult.html',
				'addFolder'=>$this->M_DIR.'forms/addFolder.html',
				'addItem'=>$this->M_DIR.'forms/addItem.html',
				'showOrders'=>$this->M_DIR.'forms/showOrders.html',
				'memberOrder'=>$this->M_DIR.'forms/memberOrder.html',
				'loadMedia'=>$this->M_DIR.'forms/loadMedia.html',
				'editMedia'=>$this->M_DIR.'forms/editMedia.html',
				'listMedia'=>$this->M_DIR.'forms/listMedia.html',
				'editMediaSuccess'=>$this->M_DIR.'forms/editMediaSuccess.html',
				'deleteMedia'=>$this->M_DIR.'forms/deleteMedia.html',
				'memberProducts'=>$this->M_DIR.'forms/memberProducts.html',
				'memberProductsRow'=>$this->M_DIR.'forms/memberProductsRow.html',
				'memberFedEx'=>$this->M_DIR.'forms/memberFedEx.html',
				'memberFedExRow'=>$this->M_DIR.'forms/memberFedExRow.html',
				'editProduct'=>$this->M_DIR.'forms/editProduct.html',
				'editProductSuccess'=>$this->M_DIR.'forms/editProductSuccess.html',
				'editFedEx'=>$this->M_DIR.'forms/editFedEx.html',
				'editFedExSuccess'=>$this->M_DIR.'forms/editFedExSuccess.html',
				'getProductOverride'=>$this->M_DIR.'forms/getProductOverride.html',
				'getLogins'=>$this->M_DIR.'forms/getLogins.html',
				'getLoginsRow'=>$this->M_DIR.'forms/getLoginsRow.html',
				'showPayments'=>$this->M_DIR.'forms/showPayments.html',
				'showPaymentsRow'=>$this->M_DIR.'forms/showPaymentsRow.html',
				'paymentDetails'=>$this->M_DIR.'forms/paymentDetails.html',
				'paymentDetailsRow'=>$this->M_DIR.'forms/paymentDetailsRow.html',
				'editMember'=>$this->M_DIR.'forms/editMember.html',
				'removeProduct'=>$this->M_DIR.'forms/removeProduct.html',
				'listPerPiece'=>$this->M_DIR.'forms/listPerPiece.html',
				'editPerPiece'=>$this->M_DIR.'forms/editPerPiece.html',
				'editPerPieceSuccess'=>$this->M_DIR.'forms/editPerPieceSuccess.html',
				'deletePerPiece'=>$this->M_DIR.'forms/deletePerPiece.html',
				'kmRates'=>$this->M_DIR.'forms/kmRates.html',
				'kmRatesRow'=>$this->M_DIR.'forms/kmRatesRow.html',
				'kmRatesEdit'=>$this->M_DIR.'forms/kmRateEdit.html',
				'kmRatesEditSuccess'=>$this->M_DIR.'forms/kmRateEditSuccess.html',
				'exportAddresses'=>$this->M_DIR.'forms/exportAddresses.html',
				'contacts'=>$this->M_DIR.'forms/contacts.html',
				'contactsRow'=>$this->M_DIR.'forms/contactsRow.html',
				'contactAddress'=>$this->M_DIR.'forms/contactAddress.html',
				'editContact'=>$this->M_DIR.'forms/editContact.html',
				'editContactSuccess'=>$this->M_DIR.'forms/editContactSuccess.html',
				'productByPC'=>$this->M_DIR.'forms/productByPC.html',
				'productByPCRow'=>$this->M_DIR.'forms/productByPCRow.html',
				'productByPCEdit'=>$this->M_DIR.'forms/productByPCEdit.html',
				'productByPCEditSuccess'=>$this->M_DIR.'forms/productByPCEditSuccess.html',
				'kmRateOverride'=>$this->M_DIR.'forms/kmRateOverride.html',
				'kmRateOverrideRow'=>$this->M_DIR.'forms/kmRateOverrideRow.html',
				'regroup'=>$this->M_DIR.'forms/regroup.html',
				'editKMOverride'=>$this->M_DIR.'forms/editKMOverride.html',
				'editKMOverrideSuccess'=>$this->M_DIR.'forms/editKMOverrideSuccess.html',
				'initKMOverrides'=>$this->M_DIR.'forms/initKMOverrides.html',
				'groupKMOverrides'=>$this->M_DIR.'forms/groupKMOverrides.html',
				'groupKMOverridesRow'=>$this->M_DIR.'forms/groupKMOverridesRow.html',
				'groupKMOverridesSuccess'=>$this->M_DIR.'forms/groupKMOverridesSuccess.html',
				'groupKMDeleteOverride'=>$this->M_DIR.'forms/groupKMDeleteOverride.html',
				'initKMOverridesRow'=>$this->M_DIR.'forms/initKMOverridesRow.html',
				'initKMOverridesSuccess'=>$this->M_DIR.'forms/initKMOverridesSuccess.html',
				'search'=>$this->M_DIR.'forms/search.html',
				'searchRow'=>$this->M_DIR.'forms/searchRow.html'
			)
		);
		$this->setFields(array(
			'editMedia'=>array(
				'name'=>array('type'=>'input','required'=>true),
				'description'=>array('type'=>'textarea','required'=>false,'class'=>'mceAdvanced'),
				'teaser'=>array('type'=>'textarea','required'=>false,'class'=>'mceSimple'),
				'filename'=>array('type'=>'fileupload','required'=>true,'validation'=>'fileupload','database'=>false),
				'submit'=>array('type'=>'submitbutton','name'=>'save','value'=>'Save','database'=>false),
				'id'=>array('type'=>'tag','database'=>false),
				'member_id'=>array('type'=>'tag'),
				'folder_id'=>array('type'=>'tag'),
				'editMedia'=>array('type'=>'hidden','value'=>1,'database'=>false)
			),
			'loadMedia'=>array(
				'member_id'=>array('type'=>'tag'),
				'folder_id'=>array('type'=>'tag')
			),
			'listMedia'=>array(
			),
			'deleteItem'=>array(
				'options'=>array('name'=>'deleteItem','database'=>false),
				'j_id'=>array('type'=>'tag'),
				'deleteItem'=>array('type'=>'hidden','value'=>1),
				'cancel'=>array('type'=>'radiobutton','name'=>'action','value'=>'cancel','checked'=>'checked'),
				'one'=>array('type'=>'radiobutton','name'=>'action','value'=>'one'),
				'all'=>array('type'=>'radiobutton','name'=>'action','value'=>'all')
			),
			'header'=>array(),
			'loadAddresses'=>array(
				'addressType'=>array('type'=>'select','idlookup'=>'memberAddressTypes','required'=>true,"value"=>ADDRESS_COMPANY),
				'pagenum'=>array('type'=>'hidden','value'=>1),
				'pager'=>array('type'=>'select','required'=>true,'value'=>$this->m_perrow,'lookup'=>'paging','id'=>'pager'),
				'o_id'=>array('type'=>'hidden'),
				'sortby'=>array("type"=>'select','options'=>array('company'=>'Company','line1'=>'Address','city'=>'City','postalcode'=>'Postal Code'),'required'=>true),
				'loadAddresses'=>array('type'=>'hidden','value'=>1),
				'addressText'=>array('type'=>'textfield')
			),
			'loadAddressesRow'=>array(
				'line1'=>array('type'=>'tag','reformatting'=>true),
				'line2'=>array('type'=>'tag','reformatting'=>true),
				'city'=>array('type'=>'tag','reformatting'=>true),
			),
			'editProfile'=>array(
				'options'=>array('method'=>'post','action'=>'/modit/ajax/editProfile/members','name'=>'editProfile'),
				'profile'=>array('type'=>'textarea','required'=>true,'id'=>'profile_editor','prettyName'=>'Profile','class'=>'mceAdvanced'),
				'teaser'=>array('type'=>'textarea','required'=>false,'id'=>'profile_teaser','prettyName'=>'Teaser','class'=>'mceSimple'),
				'image1'=>array('type'=>'tag'),
				'image2'=>array('type'=>'tag'),
				'imagesel_c'=>array('type'=>'image','unknown'=>true,'database'=>false,'id'=>'imagesel_c'),
				'imagesel_d'=>array('type'=>'image','unknown'=>true,'database'=>false,'id'=>'imagesel_d'),
				'editProfile'=>array('type'=>'hidden','value'=>1,'database'=>false),
				'p_id'=>array('type'=>'tag','database'=>false),
				'submit'=>array('type'=>'submitbutton','value'=>'Save Profile','database'=>false)
			),
			'getProfile'=>array(
				'id'=>array('type'=>'tag'),
				'title'=>array('type'=>'tag')
			),
			'editAddress'=>array(
				'options'=>array('method'=>'post','action'=>'/modit/ajax/editAddress/members'),
				'editAddress'=>array('type'=>'hidden','value'=>1,'database'=>false),
				'addresstype'=>array('type'=>'select','required'=>true,'sql'=>'select id, value from code_lookups where type = "memberAddressTypes"','prettyName'=>'Address Type'),
				'ownertype'=>array('type'=>'hidden','value'=>'member'),
				'ownerid'=>array('type'=>'hidden','id'=>'ownerid'),
				'firstname'=>array('type'=>'input','required'=>false,'prettyName'=>'First Name'),
				'lastname'=>array('type'=>'input','required'=>false,'prettyName'=>'Last Name'),
				'email'=>array('type'=>'input','required'=>false,'prettyName'=>'Email','validation'=>'email'),
				'line1'=>array('type'=>'input','required'=>true,'prettyName'=>'Address Line 1'),
				'line2'=>array('type'=>'input','required'=>false),
				'city'=>array('type'=>'input','required'=>true,'prettyName'=>'City'),
				'country_id'=>array('type'=>'countryselect','required'=>true,'id'=>'country_id','prettyName'=>'Country'),
				'province_id'=>array('type'=>'provinceselect','required'=>true,'id'=>'province_id','prettyName'=>'Province'),
				'postalcode'=>array('type'=>'input','required'=>true,'prettyName'=>'Postal Code'),
				'phone1'=>array('type'=>'input'),
				'phone2'=>array('type'=>'input'),
				'fax'=>array('type'=>'input'),
				'tax_address'=>array('type'=>'hidden'),
				'addresses'=>array('type'=>'select','database'=>false,'id'=>'addressSelector'),
				'company'=>array('type'=>'input','required'=>false),
				'latitude'=>array('type'=>'textfield','required'=>true,'class'=>'def_field_small','value'=>0.0,'validation'=>'number'),
				'longitude'=>array('type'=>'textfield','required'=>true,'class'=>'def_field_small','value'=>0.0,'validation'=>'number'),
				'geocode'=>array('type'=>'checkbox','value'=>1,'database'=>false),
				'residential'=>array('type'=>'checkbox','value'=>1),
				'address_book'=>array('type'=>'checkbox','value'=>1),
				'save'=>array('type'=>'submitbutton','database'=>false,'value'=>'Save Address')
			),
			'addContent'=>array(
				'options'=>array('method'=>'post','action'=>'/modit/ajax/addContent/members'),
				'firstname'=>array('type'=>'input','required'=>true,'prettyName'=>'First Name'),
				'lastname'=>array('type'=>'input','required'=>true,'prettyName'=>'Last Name'),
				'company'=>array('type'=>'input','required'=>false,'prettyName'=>'Company'),
				'password'=>array('type'=>'password','validation'=>'password','required'=>false),
				'enabled'=>array('type'=>'checkbox','value'=>1),
				'username'=>array('type'=>'textfield','required'=>false,'prettyName'=>'User Name'),
				'email'=>array('type'=>'input','required'=>false,'validation'=>'email','prettyName'=>'Email'),
				'expires'=>array('type'=>'datepicker','required'=>false,'id'=>'addExpires','prettyName'=>'Expires'),
				'featured'=>array('type'=>'checkbox','required'=>false,'value'=>1),
				'enabled'=>array('type'=>'checkbox','required'=>false,'value'=>1),
				'deleted'=>array('type'=>'checkbox','required'=>false,'value'=>1),
				'id'=>array('type'=>'tag','database'=>false),
				'biography'=>array('type'=>'textarea','required'=>false,'id'=>'memberBio','class'=>'mceAdvanced'),
				'submit'=>array('type'=>'submitbutton','database'=>false,'value'=>'Save Member'),
				'addContent'=>array('type'=>'hidden','value'=>1,'database'=>false),
				'addresses'=>array('type'=>'select','database'=>false,'id'=>'addressSelector'),
				'image1'=>array('type'=>'tag'),
				'image2'=>array('type'=>'tag'),
				'imagesel_a'=>array('type'=>'image','unknown'=>true,'database'=>false,'id'=>'imagesel_a'),
				'imagesel_b'=>array('type'=>'image','unknown'=>true,'database'=>false,'id'=>'imagesel_b'),
				'destFolders'=>array('name'=>'destFolders','type'=>'select','multiple'=>'multiple','required'=>false,'id'=>'destFolders','database'=>false,'options'=>$this->nodeSelect(0, 'members_folders', 2, false, false),'reformatting'=>false,'prettyName'=>'Member Of')
			),
			'login'=>array(
				'options'=>array('method'=>'post','action'=>'/modit/ajax/addContent/members'),
				'firstname'=>array('type'=>'input','required'=>true,'prettyName'=>'First Name'),
				'lastname'=>array('type'=>'input','required'=>true,'prettyName'=>'Last Name'),
				'password'=>array('type'=>'password','validation'=>'password','required'=>false),
				'email'=>array('type'=>'input','required'=>false,'validation'=>'email','prettyName'=>'Email'),
				'enabled'=>array('type'=>'checkbox','value'=>1),
				'expires'=>array('type'=>'datepicker','required'=>false,'id'=>'addExpires','prettyName'=>'Expires'),
				'addresses'=>array('type'=>'select','database'=>false,'id'=>'addressSelector'),
				'login'=>array('type'=>'hidden','value'=>1,'database'=>false)
			),
			'getLogins'=>array(
			),
			'getLoginsRow'=>array(
				'enabled'=>array('type'=>'booleanIcon'),
				'expiry'=>array('type'=>'datestamp')
				
			),
			'showSearchForm'=>array(
				'options'=>array('action'=>'showSearchForm','name'=>'searchForm','id'=>'search_form'),
				'opt_created'=>array('type'=>'select','name'=>'opt_created','lookup'=>'search_options'),
				'opt_expires'=>array('type'=>'select','name'=>'opt_expires','lookup'=>'search_options'),
				'opt_name'=>array('type'=>'select','name'=>'opt_name','lookup'=>'search_string'),
				'name'=>array('type'=>'input','required'=>false),
				'opt_email'=>array('type'=>'select','name'=>'opt_email','lookup'=>'search_string'),
				'email'=>array('type'=>'input','required'=>false),
				'opt_username'=>array('type'=>'select','name'=>'opt_username','lookup'=>'search_string'),
				'username'=>array('type'=>'input','required'=>false),
				'created'=>array('type'=>'datepicker','required'=>false),
				'expires'=>array('type'=>'datepicker','required'=>false,'id'=>'searchExpires'),
				'enabled'=>array('type'=>'select','lookup'=>'boolean'),
				'deleted'=>array('type'=>'select','lookup'=>'boolean'),
				'featured'=>array('type'=>'select','lookup'=>'boolean'),
				'showSearchForm'=>array('type'=>'hidden','value'=>1),
				'pagenum'=>array('type'=>'hidden','value'=>1),
				'sortby'=>array('type'=>'hidden','value'=>'created'),
				'sortorder'=>array('type'=>'hidden','value'=>'desc'),
				'folder'=>array('type'=>'select','optionslist' => array('table'=>$this->m_tree,'root'=>0,'indent'=>2,'inclusive'=>false),'database'=>false),
				'nonmember'=>array('type'=>'checkbox','value'=>1),
				'quicksearch'=>array('type'=>'input','name'=>'quicksearch','required'=>false),
				'opt_quicksearch'=>array('type'=>'hidden','value'=>'like'),
				'pager'=>array('type'=>'select','required'=>true,'value'=>$this->m_perrow,'lookup'=>'paging','id'=>'pager'),
				'custom_zones'=>array('type'=>'select','sql'=>'select id, title from zone_folders where enabled=1 order by title'),
				'submit'=>array('type'=>'submitbutton','value'=>'Search')
			),
			'showFolderContent'=>array(
				'options'=>array('action'=>'showPageContent'),
				'description'=>array('type'=>'tag','reformatting'=>false),
				'image'=>array('type'=>'image','unknown'=>true),
				'rollover_image'=>array('type'=>'image','unknown'=>true),
				'sortby'=>array('type'=>'hidden','value'=>'created'),
				'sortorder'=>array('type'=>'hidden','value'=>'desc'),
				'pagenum'=>array('type'=>'hidden','value'=>1),
				'pager'=>array('type'=>'select','required'=>true,'value'=>$this->m_perrow,'lookup'=>'paging','id'=>'pager'),
				'showFolderContent'=>array('type'=>'hidden','value'=>1)
			),
			'main' => array(
				'test'=>array('type'=>'tag')
			),
			'form' => array(),
			'folderProperties' => array(
				'options'=>array(
					'action'=>'/modit/members/showPageProperties',
					'method'=>'post'
				),
				'title'=>array('type'=>'textfield','required'=>true,'prettyName'=>'Title'),
				'showPageProperties'=>array('type'=>'hidden','value'=>1, 'database'=>false),
				'alternate_title'=>array('type'=>'textfield','required'=>false),
				'p_id'=>array('type'=>'select','required'=>true,'database'=>false,'optionslist'=>array('table'=>$this->m_tree,'root'=>0,'indent'=>2,'inclusive'=>true)),
				'enabled'=>array('type'=>'checkbox','required'=>false,'value'=>1),
				'notes'=>array('type'=>'textarea','required'=>false,'class'=>'mceNoEditor'),
				'description'=>array('type'=>'textarea','required'=>false, 'id'=>'folderDescription','class'=>'mceAdvanced'),
				'teaser'=>array('type'=>'textarea','required'=>false, 'id'=>'folderTeaser','class'=>'mceSimple'),
				'id'=>array('type'=>'tag', 'database'=>false),
				'image'=>array('type'=>'tag'),
				'rollover_image'=>array('type'=>'tag'),
				'imagesel_a'=>array('type'=>'image','unknown'=>true,'database'=>false,'id'=>'imagesel_a'),
				'imagesel_b'=>array('type'=>'image','unknown'=>true,'database'=>false,'id'=>'imagesel_b'),
				'template_id'=>array('type'=>'select','required'=>false,'sql'=>'select template_id,title from templates group by title order by title'),
				'submit'=>array('type'=>'submitbutton','value'=>'Save','database'=>false)
			),
			'showContentTree' => array(),
			'membersInfo' => array(),
			'showMembersContent' => array(),
			'folderInfo' => array(
				'title'=>array('type'=>'tag'),
				'alternate_title'=>array('type'=>'tag'),
				'notes'=>array('type'=>'tag','reformatting'=>false),
				'description'=>array('type'=>'tag','reformatting'=>false),
				'image' => array('type'=>'image','unknown'=>true),
				'rollover_image'=>array('type'=>'image','unknown'=>true)
			),
			'articleList' => array(
				'id'=>array('type'=>'tag'),
				'title'=>array('type'=>'tag'),
				'created'=>array('type'=>'datestamp','mask'=>'d-M-Y h:m:i a'),
				'enabled'=>array('type'=>'booleanIcon'),
				'featured'=>array('type'=>'booleanIcon'),
				'deleted'=>array('type'=>'booleanIcon'),
				'expires'=>array('type'=>'datestamp','mask'=>'d-M-Y','suppressNull'=>true)
			),
			'showOrders'=>array(
				'order_status'=>array('type'=>'select','multiple'=>true,'required'=>false,'lookup'=>'orderStatus'),
				'search'=>array('type'=>'submitbutton','value'=>'Search'),
				'o_id'=>array('type'=>'hidden'),
				'pagenum'=>array('type'=>'hidden','value'=>1),
				'showOrders'=>array('type'=>'hidden','value'=>1)
			),
			'editProduct'=>array(
				'product_id'=>array('type'=>'hidden','database'=>false),
				'zone_surcharge'=>array('type'=>'textfield','required'=>true,'value'=>0,'validation'=>'number','onchange'=>'getOverride(this);','class'=>'a-right def_field_small'),
				'downtown_surcharge'=>array('type'=>'textfield','required'=>true,'value'=>0,'validation'=>'number','onchange'=>'getOverride(this);','class'=>'a-right def_field_small'),
				'minimum_charge'=>array('type'=>'textfield','required'=>true,'value'=>0,'validation'=>'number','onchange'=>'getOverride(this);','class'=>'a-right def_field_small'),
				'inter_downtown'=>array('type'=>'textfield','required'=>true,'value'=>0,'validation'=>'number','onchange'=>'getOverride(this);','class'=>'a-right def_field_small'),
				'km_mincharge'=>array('type'=>'textfield','required'=>true,'value'=>0,'validation'=>'number','onchange'=>'getOverride(this);','class'=>'a-right def_field_small'),
				'km_maxcharge'=>array('type'=>'textfield','required'=>true,'value'=>0,'validation'=>'number','onchange'=>'getOverride(this);','class'=>'a-right def_field_small'),
				'km_charge'=>array('type'=>'textfield','required'=>true,'value'=>0,'validation'=>'number','onchange'=>'getOverride(this);','class'=>'a-right def_field_small'),
				'out_of_zone_rate'=>array('type'=>'textfield','required'=>true,'value'=>0,'validation'=>'number','onchange'=>'getOverride(this);','class'=>'a-right def_field_small'),
				'fuel_exempt'=>array('type'=>'checkbox','value'=>1,'class'=>'form-control'),
				'editProduct'=>array('type'=>'hidden','value'=>1,'database'=>false),
				'save'=>array('type'=>'submitbutton','value'=>'Save','database'=>false),
				'member_id'=>array('type'=>'hidden'),
				'isgroup'=>array('type'=>'hidden'),
				'toggle_km_zone'=>array('type'=>'checkbox','value'=>1),
				'cutoff_time'=>array('type'=>'timepicker','ampm'=>false),
				'unavailable'=>array('type'=>'checkbox','value'=>1,'onclick'=>'toggler(this);'),
				'admin_only'=>array('type'=>'checkbox','value'=>1),
				'out_of_zone_alternate'=>array('type'=>'productOptGroup','class'=>'form-control'),
				'p_id'=>array('type'=>'hidden','database'=>false)
			),
			'editFedEx'=>array(
				'product_id'=>array('type'=>'hidden','database'=>false),
				'fedex'=>array('type'=>'textfield','required'=>true,'value'=>0,'validation'=>'number','onchange'=>'getOverride(this);','class'=>'def_field_input a-right'),
				'minimum_charge'=>array('type'=>'textfield','required'=>true,'validation'=>'number','value'=>'0.00','class'=>'def_field_input a-right'),
				'editProduct'=>array('type'=>'hidden','value'=>1,'database'=>false),
				'save'=>array('type'=>'submitbutton','value'=>'Save','database'=>false),
				'member_id'=>array('type'=>'hidden'),
				'isgroup'=>array('type'=>'hidden'),
				'cutoff_time'=>array('type'=>'timepicker','ampm'=>false,'class'=>'def_field_timepicker form-control'),
				'p_id'=>array('type'=>'hidden','database'=>false)
			),
			'showPayments'=>array(
				'showPayments'=>array('type'=>'hidden','value'=>1),
				'pagenum'=>array('type'=>'hidden','value'=>1),
				'm_id'=>array('type'=>'hidden')
			),
			'showPaymentsRow'=>array(
				'reference_date'=>array('type'=>'datetimestamp'),
				'amount'=>array('type'=>'currency')
			),
			'paymentDetails'=>array(
				'paymentDetails'=>array('type'=>'hidden','value'=>1),
				'reference_date'=>array('type'=>'datetimestamp'),
				'amount'=>array('type'=>'currency')
			),
			'paymentDetailsRow'=>array(
				'amount'=>array('type'=>'currency')
			),
			'memberFedEx'=>array(
				'minimum_charge'=>array('type'=>'currency'),
				'cutoff_time'=>array('type'=>'timestamp')
			),
			'editPerPiece'=>array(
				'editPerPiece'=>array('type'=>'hidden','value'=>1,'database'=>false),
				'product_id'=>array('type'=>'productOptGroup','required'=>true),
				'p_id'=>array('type'=>'hidden','value'=>'%%request:p_id||id%%','database'=>false),
				'free'=>array('type'=>'number','required'=>true,'value'=>'1','min'=>1,'prettyName'=>'Free Pieces','validation'=>'number','class'=>'form-control a-right'),
				'member_id'=>array('type'=>'hidden','value'=>'%%request:m_id%%','name'=>'m_id'),
				'additional_fee'=>array('type'=>'textfield','required'=>true,'value'=>'1.00','prettyName'=>'Additional Fee','validation'=>'number','class'=>'form-control a-right')
			),
			'listPerPiece'=>array(
				'additional_fee'=>array('type'=>'currency')
			),
			'deletePerPiece'=>array(
			),
			'kmRates'=>array(
				'addNew'=>array('type'=>'button','value'=>'Add New','onclick'=>'editKMRate(0,%%p_id%%);return false;')
			),
			'kmRatesRow'=>array(
				'price'=>array('type'=>'currency'),
				'out_of_zone'=>array('type'=>'currency')
			),
			'kmRatesEdit'=>array(
				'options'=>array('database'=>false,'name'=>'kmRateEditForm'),
				'km_max'=>array('type'=>'textfield','required'=>true,'validation'=>'number','prettyName'=>'KM Max'),
				'price'=>array('type'=>'textfield','required'=>true,'validation'=>'number'),
				'out_of_zone'=>array('type'=>'textfield','required'=>true,'validation'=>'number'),
				'kmRatesEdit'=>array('type'=>'hidden','value'=>1,'database'=>false),
				'km_id'=>array('type'=>'hidden','value'=>'%%request:km_id%%','database'=>false),
				'member_product_option_id'=>array('type'=>'hidden','value'=>'%%request:opt_id||field:tag^member_product_option_id%%'),
				'addNew'=>array('type'=>'button','value'=>'Add New','onclick'=>'editKMRate(0,%%p_id%%);return false;','database'=>false,'class'=>'def_field_submit'),
				'save'=>array('type'=>'submitbutton','database'=>false)
			),
			'memberProducts'=>array(
				'cutoff_time'=>array('type'=>'timestamp'),
				'fuel_exempt'=>array('type'=>'booleanIcon')
			),
			'contacts'=>array(
				'member_id'=>array('type'=>'hidden')
			),
			'editContact'=>array(
				'firstname'=>array('type'=>'input','class'=>'form-control'),
				'lastname'=>array('type'=>'input','class'=>'form-control'),
				'company'=>array('type'=>'input','class'=>'form-control'),
				'phone1'=>array('type'=>'input','class'=>'form-control'),
				'email'=>array('type'=>'input','class'=>'form-control','validation'=>'email'),
				'line1'=>array('type'=>'input','class'=>'form-control'),
				'line2'=>array('type'=>'input','class'=>'form-control'),
				'city'=>array('type'=>'input','class'=>'form-control'),
				'postalcode'=>array('type'=>'input','class'=>'form-control'),
				'province_id'=>array('type'=>'provinceSelect','class'=>'form-control'),
				'country_id'=>array('type'=>'countrySelect','class'=>'form-control'),
				'notes'=>array('type'=>'textarea','class'=>'form-control','record'=>'contacts','style'=>'min-height:200px;'),
				'c_id'=>array('type'=>'hidden','database'=>false),
				'member_id'=>array('type'=>'hidden','record'=>'contacts'),
				'submit'=>array('type'=>'button','value'=>'Save Changes','onclick'=>'saveContact(this);return false;','class'=>'btn btn-primary','database'=>false),
				'editContact'=>array('type'=>'hidden','value'=>1,'database'=>false)
			),
			'productByPC'=>array(
				'addNew'=>array('type'=>'button','value'=>'Add New','onclick'=>'editPCRate(0,%%p_id%%);return false;')
			),
			'productByPCRow'=>array(
				"contracted_rate"=>array("type"=>"currency"),
				"fuel_exempt"=>array("type"=>"booleanIcon")
			),
			'productByPCEdit'=>array(
				'options'=>array('database'=>false,'name'=>'productByPCEditForm'),
				'from_postal_code'=>array('type'=>'textfield','required'=>true,'validation'=>'postalcode'),
				'to_postal_code'=>array('type'=>'textfield','required'=>true,'validation'=>'postalcode'),
				'contracted_rate'=>array('type'=>'textfield','required'=>true,'validation'=>'number'),
				'fuel_exempt'=>array('type'=>'checkbox','required'=>false,'value'=>'1'),
				'productByPCEdit'=>array('type'=>'hidden','value'=>1,'database'=>false),
				'pc_id'=>array('type'=>'hidden','value'=>'%%request:pc_id%%','database'=>false),
				'member_product_option_id'=>array('type'=>'hidden','value'=>'%%request:opt_id||field:tag^member_product_option_id%%'),
				'addNew'=>array('type'=>'button','value'=>'Add New','onclick'=>'editPCRate(0,%%p_id%%);return false;','database'=>false,'class'=>'def_field_submit'),
				'save'=>array('type'=>'submitbutton','database'=>false)
			),
			'regroup'=>array(
				'm_id'=>array('type'=>'select','sql'=>'select id, company from members where enabled = 1 and deleted = 0 and id = %%m_id%% order by company','required'=>true,'onchange'=>'reloadGroup(this);return false;'),
				'from_group'=>array('type'=>'select','sql'=>'select mf.id, mf.title from members_folders mf, members_by_folder mbf where mf.id = mbf.folder_id and mbf.member_id = %%m_id%% order by mf.title','required'=>true),
				'do_it'=>array('type'=>'hidden','value'=>0),
				'regroup'=>array('type'=>'hidden','value'=>1),
				'to_group'=>array('type'=>'select','sql'=>'select id, title from members_folders where id not in (select folder_id from members_by_folder where member_id = %%m_id%%) order by title','required'=>true),
				'change'=>array('type'=>'submitbutton','value'=>'Move to New Group','onclick'=>'doRegroup(this);return false;')
			),
			'kmRateOverride'=>array(),
			'kmRateOverrideRow'=>array(
				"price"=>array("type"=>"currency","mask"=>"$###,##0.00"),
				"out_of_zone"=>array("type"=>"currency","mask"=>"$###,##0.00"),
				"adjusted_price"=>array("type"=>"currency","mask"=>"$###,##0.00"),
				"adjusted_out_of_zone"=>array("type"=>"currency","mask"=>"$###,##0.00"),
				"in_zone_override"=>array("type"=>"currency","mask"=>"##0.00"),
				"out_of_zone_override"=>array("type"=>"currency","mask"=>"##0.00")
			),
			'editKMOverride'=>array(
				'id'=>array('type'=>'hidden',"value"=>0),
				"km_opt_id"=>array("type"=>"hidden","value"=>0),
				"junction_id"=>array("type"=>"hidden"),
				"in_zone_override"=>array("type"=>"number","step"=>".01","onchange"=>"recalcOverride(this);return false;","class"=>"def_field_input a-right","value"=>"0"),
				"out_of_zone_override"=>array("type"=>"number","step"=>".01","onchange"=>"recalcOverride(this);return false;","class"=>"def_field_input a-right","value"=>"0"),
				"in_zone_value"=>array("type"=>"currency","database"=>false),
				"out_of_zone_value"=>array("type"=>"currency","database"=>false),
				"editKMOverride"=>array("type"=>"hidden","database"=>false,"value"=>1),
				"tempUpdate"=>array("type"=>"hidden","value"=>0,"database"=>false),
				"submit"=>array("type"=>"submitbutton","value"=>"Save","database"=>false)
			),
			'initKMOverrides'=>array(
				'initKMOverrides'=>array('type'=>'hidden','value'=>1),
				'j_id'=>array('type'=>'hidden','database'=>false)
			),
			'initKMOverridesRow'=>array(
				'in_zone'=>array('type'=>'number','step'=>'.01','name'=>'in_zone[%%id%%]','class'=>'def_field_input a-right'),
				'out_of_zone'=>array('type'=>'number','step'=>'.01','name'=>'out_of_zone[%%id%%]','class'=>'def_field_input a-right'),
				'processit'=>array('type'=>'checkbox','value'=>1,'database'=>false,'name'=>'process[%%id%%]')
			),
			'groupKMOverrides'=>array(
				"opt_id"=>array("type"=>"select","onchange"=>"getGroupProdOverride(this,%%j_id%%);return false;"),
				"groupKMOverrides"=>array("type"=>"hidden","value"=>1)
			),
			'groupKMOverridesRow'=>array(
				"in_zone_override"=>array("type"=>"currency","mask"=>"##0.00"),
				"out_of_zone_override"=>array("type"=>"currency","mask"=>"##0.00"),
				"price"=>array("type"=>"currency"),
				"override_out_of_zone"=>array("type"=>"currency"),
				"override_price"=>array("type"=>"currency"),
				"out_of_zone"=>array("type"=>"currency")
			),
			'groupKMDeleteOverride'=>array(),
			'search'=>array(
				'options'=>array('name'=>'overrideSearchForm','methos'=>'POST','action'=>'search'),
				'selector'=>array('type'=>'select','class'=>'form-control','options'=>array(
					'GO'=>'Group Override',
					'MO'=>'Member Override'
					)
				),
				'selectorType'=>array('type'=>'select','class'=>'form-control','options'=>array(
					'F'=>'Fuel',
					'FE'=>'Fuel Exempt',
					'W'=>'Weight',
					'I'=>'Insurance',
					'FW'=>'Free Weight',
					'FZ'=>'First Zone',
					'ID'=>'Inter-Downtown',
					'DT'=>'Downtown Surcharge',
					'EZ'=>'Extra Zones',
					'KMN'=>'KM Min Charge',
					'KMM'=>'KM Max Charge',
					'KR'=>'KM Rate',
					'OZ'=>'KM Out of Zone Rate',
					'AD'=>'Admin Use Only',
					'TZ'=>'Toggle KM/Zone'
					
				)),
				'folder'=>array('type'=>'select','optionslist' => array('table'=>$this->m_tree,'root'=>0,'indent'=>2,'inclusive'=>false),'database'=>false),
				'product'=>array('type'=>'productOptGroup','required'=>false,'sql'=>'select id, concat(code," - ",name) from product where enabled = 1 and deleted = 0 order by code'),
				'opt_search'=>array('type'=>'select','lookup'=>'search_options'),
				'opt_search_value'=>array('type'=>'textfield','class'=>'form-control'),
				'find'=>array('type'=>'submitbutton','class'=>'btn btn-primary','value'=>'Search'),
				'search'=>array('type'=>'hidden','value'=>1)
			),
			'searchRow'=>array()
		));
	
		parent::__construct ();
	}

    /**
     * Destructor
     */
    function __destruct() {
	
	}

    /**
     * @param $injector
     * @return array|mixed|string|string[]|void
     * @throws phpmailerException
     */
    function show($injector = null) {
		$form = new Forms();
		$form->init($this->getTemplate('main'),array('name'=>'adminMenu'));
		$frmFields = $form->buildForm($this->getFields('main'));
		if ($injector == null || strlen($injector) == 0) {
			$injector = $this->moduleStatus(true);
		}
		$form->addTag('injector', $injector, false);
		return $form->show();
	}

    /**
     * @return array|mixed|string|string[]
     * @throws phpmailerException
     */
    function showForm() {
		$form = new Forms();
		$form->init($this->getTemplate('form'),array('name'=>'adminMenu'));
		$flds = $form->buildForm($this->getFields('form'));
		$form->getField('contenttree')->addAttribute('value',$this->buildTree($this->m_tree));
		if (count($_POST) > 0) {
			$form->addData($_POST);
			if ($form->validate()) {
				$this->addMessage('Validated');
				$tmp = array();
				foreach($_POST as $key=>$value) {
					$fld = new tag();
					$tmp[] = $fld->show(sprintf('name: %s value: [%s] post: [%s]', $key, $form->getData($key), $value));
				}
				$form->addTag('info', implode('<br/>',$tmp), false);
			}
			else {
				$this->addError('Validation failed');
			}
		}
		return $form->show();
	}

    /**
     * @return array|false|mixed|string|string[]
     * @throws phpmailerException
     */
    function showContentTree() {
		$form = new Forms();
		$form->init($this->getTemplate('showContentTree'),array());
		$form->addTag('tree',$this->buildTree($this->m_tree, array(), "ajaxBuild", array(0=>"<ol>%s</ol>",1=>"<li class='collapsed'>%s%s</li>",3=>"")),false);
		if ($this->isAjax())
			return $this->ajaxReturn(array('status'=>'true','html'=>$form->show()));
		else
			return $form->show();
	}

    /**
     * @param bool $fromMain
     * @return array|false|mixed|string|string[]|void
     * @throws phpmailerException
     */
    function showPageProperties($fromMain = false) {
		$result = array();
		$return = 'true';
		if (!(array_key_exists('id',$_REQUEST) && $data = $this->fetchSingleTest('select * from %s where id = %d',$this->m_tree, $_REQUEST['id'])))
			$data = array('enabled'=>1,'id'=>0,'p_id'=>0,'image'=>'','rollover_image'=>'');
		else {
			//
			//	get the parent node as well
			//
			$data['p_id'] = 0;
			if ($data['level'] > 1) {
				if ($p = $this->fetchSingleTest('select * from %s where level = %d and left_id < %d and right_id > %d', $this->m_tree, $data['level'] - 1, $data['left_id'], $data['right_id']))
					$data['p_id'] = $p['id'];
			}
		}
		$form = new Forms();
		$form->init($this->getTemplate('folderProperties'),array('name'=>'folderProperties'));
		$frmFlds = $this->getFields('folderProperties');
		$data['imagesel_a'] = $data['image'];
		$data['imagesel_b'] = $data['rollover_image'];
		$customFields = new custom();
		if (method_exists($customFields,'memberFolderDisplay')) {
			$custom = $customFields->memberFolderDisplay($data);
			$form->addTag('customTab',sprintf('<li><a href="#tabs-custom">%s</a></li>',$custom['description']),false);
			$html = $form->getHTML();
			$html = str_replace('%%customInfo%%',$custom['form'],$html);
			$form->setHTML($html);
			$frmFlds = array_merge($frmFlds,$custom['fields']);
			$this->logMessage(__FUNCTION__,sprintf("custom [%s]",print_r($custom,true)),1);
		}
		$frmFlds = $form->buildForm($frmFlds);
		$form->addData($data);
		if (count($_POST) > 0 && array_key_exists('showPageProperties',$_POST)) {
			$_POST['imagesel_a'] = $_POST['image'];
			$_POST['imagesel_b'] = $_POST['rollover_image'];
			$form->addData($_POST);
			$valid = $form->validate();
			if ($valid) {
				if (array_key_exists('options',$frmFlds)) unset($frmFlds['options']);
				$values = array();
				$flds = array();
				if ($data['id'] == 0) {
					$mptt = new mptt($this->m_tree);
					$data['id'] = $mptt->add($_POST['p_id'],999,array('title'=>'to be replaced'));
				} 
				else {
					//
					//	did we move the parent folder?
					//
					if ($data['level'] > 1)
						$parent = $this->fetchSingleTest('select * from %s where level = %d and left_id < %d and right_id > %d', $this->m_tree, $data['level'] - 1, $data['left_id'], $data['right_id']);
					else $parent['id'] = 0;
					if ($_POST['p_id'] != $parent['id']) {
						$this->logMessage('showPageProperties', sprintf('moving [%d] to [%d] posted[%d]',$data['id'],$parent['id'], $_POST['p_id']), 1);
						$mptt = new mptt($this->m_tree);
						$mptt->move($data['id'], $_POST['p_id']);
					}
				}
				foreach($frmFlds as $key=>$fld) {
					if (!(array_key_exists('database',$fld) && $fld['database'] == false)) {
						$values[] = $form->getData($fld['name']);
						if ($data['id'] > 0)
							$flds[] = sprintf('%s = ?',$fld['name']);
						else
							$flds[] = $fld['name'];
					}
				}
				$stmt = $this->prepare(sprintf('update %s set %s where id = %d',$this->m_tree,implode(',',$flds),$data['id']));
				$stmt->bindParams(array_merge(array(str_repeat('s', count($flds))),$values));
				if ($stmt->execute()) {
					if (method_exists($customFields,'memberFolderUpdate')) {
						$customFields->memberFolderUpdate(array_merge(array('id'=>$data["id"]),$flds),$_REQUEST);
					}
					if ($this->isAjax()) {
						$this->logMessage('showPageProperties', 'executing ajax success return', 3);
						$this->addMessage('Record successfully added');
						return $this->ajaxReturn(array(
								'status'=>'true',
								'html'=>'',
								'url'=>'/modit/members?p_id='.$data['id']
						));
					}
				} else {
					$this->addError('Error occurred');
					$form->addTag('errorMessage',$this->showErrors(),false);
					if ($this->isAjax()) {
						$this->logMessage('showPageProperties', 'executing ajax error return', 3);
						return $this->ajaxReturn(array(
								'status'=>'false',
								'html'=>$form->show()
						));
					}
				}
				$form->addTag('errorMessage','Record added succesfully');
			}
			else {
				$return = 'false';
				$form->addTag('errorMessage','Form validation failed');
			}
		}
	
		if ($this->isAjax())
			return $this->ajaxReturn(array(
					'status'=>$return,
					'html'=>$form->show()
			));
		elseif ($fromMain)
		return $form->show();
		else
			$this->show($form->show());
	}

    /**
     * @param $data
     * @param $table
     * @param $wrappers
     * @param $submenu
     * @return array
     * @throws Exception
     */
    function ajaxBuild($data, $table, $wrappers, $submenu) {
		switch($table) {
			case $this->m_tree:
				$value = new tag(false);
				$mptt = new mptt($table);
				$children = $mptt->fetchChildren($data['id']);
				if (count($_REQUEST) > 0 && array_key_exists('p_id',$_REQUEST)) {
					$expanded = $_REQUEST['p_id'] == $data['id'] ?  'active' : '';
				}
				else $expanded='';
				if (count($submenu) > 0) {
					$return = array('value'=>sprintf('<div class="wrapper"><div class="spacer"><a href="#" class="toggler" onclick="toggle(this);return false;">+</a></div><a href="#" id="li_%d" class="%s icon_folder info">%s</a></div>',$data['id'], $expanded, htmlspecialchars($data['title'])),'submenu'=>$submenu);
				}
				else {
					$return = array('value'=>sprintf('<div class="wrapper"><div class="spacer">&nbsp;</div><a href="#" id="li_%d" class="%s icon_folder info">%s</a></div>',$data['id'], $expanded, htmlspecialchars($data['title'])),'submenu'=>array());
				}
				break;
			default:
				break;
		}
		return $return;
	}

    /**
     * @param bool $fromMain
     * @return array|false|mixed|string|string[]|void
     * @throws phpmailerException
     */
    function getFolderInfo($fromMain = false) {
		if (array_key_exists('p_id',$_REQUEST)) {
			if ($data = $this->fetchSingleTest('select * from %s where id = %d',$this->m_tree,$_REQUEST['p_id'])) {
				$form = new Forms();
				$data['notes'] = nl2br($data['notes']);
				$template = 'folderInfo';
				$form->init($this->getTemplate($template), array());
				$frmFields = $form->buildForm($this->getFields($template));
				$form->addData($data);
				if ($this->isAjax())
					return $this->ajaxReturn(array('status'=>'true','html'=>$form->show()));
				elseif ($fromMain)
				return $form->show();
				else
					return $this->show($form->show());
			}
		}
	}

    /**
     * @param bool $fromMain
     * @return array|false|mixed|string|string[]|void
     * @throws phpmailerException
     */
    function showPageContent($fromMain = false) {
		$p_id = array_key_exists('p_id',$_REQUEST) ? $_REQUEST['p_id'] : 0;
		$form = new Forms();
		if ($p_id > 0 && $data = $this->fetchSingleTest('select * from %s where id = %d',$this->m_tree,$p_id)) {
			if (strlen($data['alternate_title']) > 0) $data['connector'] = '&nbsp;-&nbsp;';
			$form->init($this->getTemplate('showFolderContent'),array('name'=>'showFolderContent'));
			$frmFields = $form->buildForm($this->getFields('showFolderContent'));
			if (array_key_exists('pagenum',$_REQUEST)) 
				$pageNum = $_REQUEST['pagenum'];
			else
				$pageNum = 1;	// no 0 based calcs
			if ($pageNum <= 0) $pageNum = 1;
			$perPage = $this->m_perrow;
			if (array_key_exists('pager',$_REQUEST)) $perPage = $_REQUEST['pager'];
			//$count = $this->fetchScalarTest('select count(n.id) from %s n where n.id in (select f.member_id from %s f where f.folder_id = %d)', $this->m_content, $this->m_junction, $_REQUEST['p_id']);
			$sql = sprintf('select count(n.id) from '.$this->m_content.' n where n.id in (select f.member_id from '.$this->m_junction.' f where f.folder_id = %d)', $_REQUEST['p_id']);
			$count = $this->fetchScalarTest($sql);
			$pagination = $this->pagination($count, $perPage, $pageNum, 
				array('prev'=>$this->M_DIR.'forms/paginationPrev.html','next'=>$this->M_DIR.'forms/paginationNext.html',
						'pages'=>$this->M_DIR.'forms/paginationPage.html', 'wrapper'=>$this->M_DIR.'forms/paginationWrapper.html'));
			$start = ($pageNum-1)*$perPage;
			$sortby = 'id';
			$sortorder = 'desc';
			if (count($_POST) > 0 && array_key_exists('showFolderContent',$_POST)) {
				$sortby = $_POST['sortby'];
				$sortorder = $_POST['sortorder'];
				$form->addData($_POST);
			} else {
				$sortby = 'sequence';
				$sortorder = 'asc';
			}
			$sql = sprintf('select a.*, f.id as j_id from %s a left join %s f on a.id = f.member_id where f.folder_id = %d order by %s %s limit %d,%d',  $this->m_content, $this->m_junction, $_REQUEST['p_id'],$sortby, $sortorder, $start,$perPage);
			$members = $this->fetchAllTest($sql);
			$this->logMessage('showPageContent', sprintf('sql [%s], records [%d]',$sql, count($members)), 2);
			$articles = array();
			foreach($members as $article) {
				$frm = new Forms();
				$frm->init($this->getTemplate('articleList'),array());
				$tmp = $frm->buildForm($this->getFields('articleList'));
				$frm->addData($article);
				$articles[] = $frm->show();
			}
			$form->addTag('articles',implode('',$articles),false);
			$form->addTag('pagination',$pagination,false);
			$form->addData($data);
		}
		$form->addTag('heading',$this->getHeader(),false);
		if ($this->isAjax()) {
			$tmp = $form->show();
			return $this->ajaxReturn(array('status'=>'true','html'=>$tmp)); 
		}
		elseif ($fromMain)
		return $form->show();
		else
			return $this->show($form->show());
	}

    /**
     * @param bool $fromMain
     * @param string $msg
     * @return array|false|mixed|string|string[]|void
     * @throws phpmailerException
     */
    function showSearchForm($fromMain = false, $msg = '') {
		$form = new Forms();
		$form->init($this->getTemplate('showSearchForm'),array('name'=>'showSearchForm','persist'=>true));
		$frmFields = $form->buildForm($this->getFields('showSearchForm'));
		if (count($_POST) == 0)
			if (array_key_exists('formData',$_SESSION) && array_key_exists('membersSearchForm', $_SESSION['formData']))
				$_POST = $_SESSION['formData']['membersSearchForm'];
			else
				$_POST = array('showSearchForm'=>1,'deleted'=>0,'sortby'=>'created','sortorder'=>'desc');
		$this->logMessage(__FUNCTION__,sprintf("post is [%s]",print_r($_POST,true)),1);
		if (count($_POST) > 0 && array_key_exists('showSearchForm',$_POST)) {
			$form->addData($_POST);
			if ($form->validate()) {
				if (strlen($form->getData("quicksearch")) > 0) {
					$_SESSION['formData']['membersSearchForm'] = array('showSearchForm'=>1,'opt_quicksearch'=>'like','quicksearch'=>$form->getData("quicksearch"),'pager'=>$form->getData("pager"),'deleted'=>0);
				}
				else
					$_SESSION['formData']['membersSearchForm'] = $form->getAllData();
				$srch = array();
				foreach($frmFields as $key=>$value) {
					switch($key) {
						case 'quicksearch':
							if (array_key_exists('opt_quicksearch',$_POST) && $_POST['opt_quicksearch'] != '' && $value = $form->getData($key)) {
								//$srch = array();
								if ($_POST['opt_quicksearch'] == 'like' && strpos($value,'%',0) === false) {
									$value = '%'.$value.'%';
								}
								$srch = array(sprintf(' (firstname %s "%s" or lastname %s "%s" or concat(firstname," ",lastname) %s "%s" or email %s "%s" or company %s "%s") ',
									$_POST['opt_quicksearch'],$this->escape_string($value),
									$_POST['opt_quicksearch'],$this->escape_string($value),
									$_POST['opt_quicksearch'],$this->escape_string($value),
									$_POST['opt_quicksearch'],$this->escape_string($value),
									$_POST['opt_quicksearch'],$this->escape_string($value)),'deleted = 0');
								$frmFields = array();
								break 2;
							}
							break;
						case 'name':
							if (array_key_exists('opt_name',$_POST) && $_POST['opt_name'] != '' && $value = $form->getData($key)) {
								if ($_POST['opt_name'] == 'like' && strpos($value,'%',0) === false) {
									$value = '%'.$value.'%';
								}
								$srch[] = sprintf(' (firstname %s "%s" or lastname %s "%s" or company %s "%s") ',
									$_POST['opt_name'],$this->escape_string($value),
									$_POST['opt_name'],$this->escape_string($value),
									$_POST['opt_name'],$this->escape_string($value));
							}
							break;
						case 'username':
						case 'email':
							if (array_key_exists('opt_'.$key,$_POST) && $_POST['opt_'.$key] != '' && $value = $form->getData($key)) {
								if ($_POST['opt_'.$key] == 'like' && strpos($value,'%',0) === false) {
									$value = '%'.$value.'%';
								}
								$srch[] = sprintf(' %s %s "%s" ', $key, $_POST['opt_'.$key],$this->escape_string($value));
							}
							break;
						case 'created':
						case 'expires':
							if (array_key_exists('opt_'.$key,$_POST) && $value = $form->getData($key)) {
								if ($_POST['opt_'.$key] == 'like') {
									$this->addError('Like is not supported for dates');
								}
								else
									$srch[] = sprintf(' %s %s "%s"',$key, $_POST['opt_'.$key],$this->escape_string($value));
							}
							break;
						case 'folder':
							if (($value = $form->getData($key)) > 0) {
								$srch[] = sprintf(' n.id in (select member_id from %s where folder_id = %d) ', $this->m_junction, $value);
							}
							break;
						case 'featured':
						case 'enabled':
						case 'deleted':
							if (!is_null($value = $form->getData($key)))
								if (strlen($value) > 0) {
									$srch[] = sprintf(' n.%s = %s',$key,$this->escape_string($value));
								}
							break;
						case 'nonmember':
							if ($form->getData($key) != 0)
								$srch[] = sprintf(' not exists (select 1 from %s where member_id = n.id) ',$this->m_junction);
							break;
						case 'custom_zones':
							if (($z = $form->getData($key)) != 0) $srch[] = sprintf("n.custom_zones = %d",$z);
							break;
						default:
							break;
					}
				}
				$this->logMessage("showSearchForm",sprintf("srch [%s]",print_r($srch,true)),2);
				if (count($srch) > 0) {
					if (array_key_exists('pagenum',$_POST))
						$pageNum = $_POST['pagenum'];
					else
						$pageNum = 1;	// no 0 based calcs
					$perPage = $this->m_perrow;
					if (array_key_exists('pager',$_POST)) $perPage = $_POST['pager'];
					$count = $this->fetchScalarTest('select count(n.id) from '.$this->m_content.' n where 1=1 and %s', implode(' and ',$srch));
					$pageNum = max(1,min($pageNum, (floor(($count-1)/$perPage)+1)));
					$form->setData('pagenum', $pageNum);
					$pagination = $this->pagination($count, $perPage, $pageNum,
							array('prev'=>$this->M_DIR.'forms/paginationPrev.html','next'=>$this->M_DIR.'forms/paginationNext.html',
									'pages'=>$this->M_DIR.'forms/paginationPage.html', 'wrapper'=>$this->M_DIR.'forms/paginationWrapper.html'));
					$start = ($pageNum-1)*$perPage;
					$sort = 'created desc';
					if (array_key_exists('sortby',$_POST)) {
						$sortby = $_POST['sortby'];
						$sortorder = $_POST['sortorder'];
						if ($sortby == 'name') {
							$sort = sprintf('lastname %s, firstname %s',$sortorder,$sortorder);
						}
						else
							$sort = $sortby.' '.$sortorder;
					}
					//$sql = sprintf('select n.*, j.id as j_id from %s n, %s j where n.id = j.member_id and j.id = (select min(j1.id) from %s j1 where j1.member_id = n.id) and %s order by %s limit %d,%d',
					//	 $this->m_content, $this->m_junction, $this->m_junction, implode(' and ',$srch),$sort, $start,$perPage);
					if (array_key_exists("folder",$_POST) && $_POST["folder"] > 0) {
						$srch[] = sprintf("mbf.folder_id = %d and n.id = mbf.member_id", $_POST["folder"]);
						$sql = sprintf('select n.*, mbf.id as j_id from %s n, members_by_folder mbf where %s order by %s limit %d,%d',
						  $this->m_content, implode(' and ',$srch),$sort, $start,$perPage);
					}
					else
						$sql = sprintf('select n.*, mbf.id as j_id, mf.title from %s n left join members_by_folder mbf on mbf.member_id = n.id left join members_folders mf on mf.id = mbf.folder_id where %s order by %s limit %d,%d', $this->m_content, implode(' and ',$srch),$sort, $start,$perPage);
					$recs = $this->fetchAllTest($sql);
					$this->logMessage('showSearchForm', sprintf('sql [%s] records [%d]',$sql,count($recs)), 2);
					$articles = array();
					foreach($recs as $article) {
						$frm = new Forms();
						$frm->init($this->getTemplate('articleList'),array());
						$tmp = $frm->buildForm($this->getFields('articleList'));
						$frm->addData($article);
						$articles[] = $frm->show();
					}
					$form->addTag('articles',implode('',$articles),false);
					$form->addTag('pagination',$pagination,false);
					$form->addTag('statusMessage',sprintf('We found %d record%s matching the criteria',$count,$count > 1 ? 's' : ''));
				}
			}
		}
		$form->addTag('heading',$this->getHeader(),false);
		if (strlen($msg) > 0) $form->addTag('statusMessage',$msg,false);
		if ($this->isAjax()) {
			$tmp = $form->show();
			return $this->ajaxReturn(array('status'=>'true','html'=>$tmp));
		}
		elseif ($fromMain)
			return $form->show();
		else
			return $this->show($form->show());
	}

    /**
     * @param bool $fromMain
     * @return array|false|mixed|string|string[]|void
     * @throws phpmailerException
     */
    function showSearchResults($fromMain = false) {
		$form = new Forms();
		$form->init($this->getTemplate('showSearchResults'),array('name'=>'showSearchResults','persist'=>true));
		$frmFields = $form->buildForm($this->getFields('showSearchResults'));
		if ($this->isAjax()) {
			$tmp = $form->show();
			return $this->ajaxReturn(array('status'=>'true','html'=>$tmp));
		}
		elseif ($fromMain)
			return $form->show();
		else
			return $this->show($form->show());
		
	}

    /**
     * @param bool $fromMain
     * @return array|false|mixed|string|string[]|void
     * @throws phpmailerException
     */
    function addContent($fromMain = false) {
		$form = new Forms();
		$form->init($this->getTemplate('addContent'),array('name'=>'addContent'));
		$frmFields = $this->getFields('addContent');
		if (!(array_key_exists('m_id',$_REQUEST) && $_REQUEST['m_id'] > 0 && $data = $this->fetchSingleTest('select * from %s where id = %d', $this->m_content, $_REQUEST['m_id']))) {
			$data = array('id'=>0,'published'=>false,'image1'=>'','image2'=>''); 
		}
		$data['destFolders'] = array();
		if (count($_REQUEST) > 0 && array_key_exists('destFolders',$_REQUEST) || $data['id'] > 0) {
			$ids = array();
			if (array_key_exists('destFolders',$_REQUEST)) {
				$ids = $_REQUEST['destFolders'];
				if (!is_array($ids)) $ids = array($ids);
			}
			if ($data['id'] > 0) {
				$tmp = $this->fetchScalarAllTest('select folder_id from %s where member_id = %d', $this->m_junction, $data['id']);
				$ids = array_merge($ids,$tmp);
			}
			if (count($ids) > 0) {
				$data['destFolders'] = $ids;
			}
		}
		$profiles = array();
		foreach($data['destFolders'] as $key=>$value) {
			$profiles[] = $this->getProfile($data['id'],$value);
		}
		$data["logins"] = $this->getLogins($data["id"]);
		$data['profiles'] = implode("",$profiles);
		$data['imagesel_a'] = $data['image1'];
		$data['imagesel_b'] = $data['image2'];
		$data["contacts"] = $this->buildContacts($data["id"]);
		$data['packageCharges'] = $this->listPerPiece($data['id'],1);
		$form->addTag('addressForm',$this->loadAddresses($data['id']),false);
		$status = 'false';	//assume it failed
		$customFields = new custom();
		if (method_exists($customFields,'memberDisplay')) {
			$custom = $customFields->memberDisplay($data);
			$form->addTag('customTab',sprintf('<li><a href="#tabs-custom">%s</a></li>',$custom['description']),false);
			$html = $form->getHTML();
			$html = str_replace('%%customInfo%%',$custom['form'],$html);
			$form->setHTML($html);
			$frmFields = array_merge($frmFields,$custom['fields']);
		}
		$form->addData($data);
		$frmFields = $form->buildForm($frmFields);
		if (count($_POST) > 0 && array_key_exists('addContent',$_POST)) {
			$_POST['imagesel_a'] = array_key_exists('image1',$_POST) ? $_POST['image1'] : '';
			$_POST['imagesel_b'] = array_key_exists('image1',$_POST) ? $_POST['image2'] : '';
			$form->addData($_POST);
			$valid = $form->validate();
			if ($valid) {
				if (array_key_exists('username',$_POST) && $_POST['username'] != '') {
					//
					//	make sure noone else has the username
					//
					$ct = $this->fetchScalarTest('select count(0) from %s where deleted = 0 and username = "%s" and id != %d',$this->m_content, $_POST['username'], $data['id']);
					if ($ct > 0) {
						$this->addError('Username is already used');
						$valid = false;
					}
				}
			}
			//
			//	check if the email is already used but not deleted
			//	remove unique email requirement - causing too many issues and they are logging in via username
			//
/*
			$ct = $this->fetchScalarTest("select count(0 from members where email = '%s' and deleted = 0 and id != %d", $form->getData("email"), $form->getData("id")));
			if ($ct > 0) {
				$valid = false;
				$form->addFormError("This email is already registered");
			}
*/
			if ($valid) {
				$id = $_POST['m_id'];
				
				//
				//	check to see if the password has been changed - if not unset it, otherwise encode it
				//
				$pwd = $this->fetchScalarTest('select password from members where id = %d',$id);
				if ($form->getData('password') == $pwd) {
					$this->logMessage('addContent','password was not changed, unsetting it',3);
					unset($frmFields['password']);
				}
				else {
					$this->logMessage('addContent','encrypting password',3);
					$form->setData('password',SHA1($form->getData('password')));
				}
				unset($frmFields['m_id']);
				unset($frmFields['options']);
				foreach($frmFields as $key=>$fld) {
					if (!(array_key_exists('database',$fld) && $fld['database'] == false)) {
						$values[] = $form->getData($fld['name']);	//$_REQUEST[$fld['name']];
						if ($data['id'] > 0)
							$flds[sprintf('%s = ?',$fld['name'])] = $form->getData($fld['name']);//$_REQUEST[$fld['name']];
						else
							$flds[$fld['name']] = $form->getData($fld['name']);//$_REQUEST[$fld['name']];
					}
				}
				$adding = $id == 0;
				if ($id == 0) {
					$flds['created'] = date('c');
					$stmt = $this->prepare(sprintf('insert into %s(%s) values(%s)', $this->m_content, implode(',',array_keys($flds)), str_repeat('?,', count($flds)-1).'?'));
					$stmt->bindParams(array_merge(array(str_repeat('s', count($flds))),array_values($flds)));
				}
				else {
					$stmt = $this->prepare(sprintf('update %s set %s where id = %d', $this->m_content, implode(',',array_keys($flds)),$data['id']));
					$stmt->bindParams(array_merge(array(str_repeat('s', count($flds))),array_values($flds)));
				}
				$this->beginTransaction();
				if ($stmt->execute()) {
					if ($id == 0) {
						$id = $this->insertId();
						$flds["id"] = $id;
					}
					else
						$flds["id"] = $data["id"];
					$base_dir = sprintf('../images/members/%d',$id);
					if (!file_exists($base_dir)) mkdir($base_dir);
					if (array_key_exists('destFolders',$_POST))
						$destFolders = $_POST['destFolders'];
					else $destFolders = "0";
					if (!is_array($destFolders)) $destFolders = array($destFolders);
					//
					//	delete folders we are no longer a member of
					//
					$this->execute(sprintf('delete from %s where member_id = %d and folder_id not in (%s)', $this->m_junction, $id,implode(',',$destFolders)));
					$sql = sprintf('select id from %s where id in (%s) and id not in (select folder_id from %s where member_id = %d and folder_id in (%s))',
					$this->m_tree,implode(',',$destFolders),$this->m_junction,$id,implode(',',$destFolders));
					$new = $this->fetchScalarAllTest($sql);
					$status = true;
					foreach($new as $key=>$folder) {
						$obj = new preparedStatement(sprintf('insert into %s(member_id,folder_id) values(?,?)',$this->m_junction));
						$obj->bindParams(array('dd',$id,$folder));
						$status = $status && $obj->execute();
						$sub_dir = sprintf('%s/%d',$base_dir,$folder);
						if (!file_exists($sub_dir)) mkdir($sub_dir);
					}
					if ($status) {
						$this->commitTransaction();
						if (method_exists($customFields,'memberUpdate')) {
							$customFields->memberUpdate($flds,$_REQUEST);
						}
						if ($adding) {
							//
							//	let the address editing kick in now
							//
							$this->addMessage('Member Added');
							$form->setData('id',$id);
							$profiles = array();
							foreach($data['destFolders'] as $key=>$value) {
								$profiles[] = $this->getProfile($id,$value);
							}
							$form->addTag('profiles',implode("",$profiles),false);
							return $this->ajaxReturn(array(
								'status' => 'true',
								'html' => $form->show()
							));
						}
						else {
							$form->init($this->getTemplate('addContentResult'));
							//return $this->ajaxReturn(array('status' => 'true','url' => sprintf('/modit/members?p_id=%d',$destFolders[0])));
							return $this->ajaxReturn(array('status' => 'true','html' => $form->show()));
						}
					}
					else {
						$this->rollbackTransaction();
						$this->addError('Error creating the record');
					}
				} else {
					$this->rollbackTransaction();
					$this->addError('Error creating the record');
				}
			}
			else
				$this->addError('form validation failed');
			$form->addTag('errorMessage',$this->showMessages(),false);
		}
		if ($this->isAjax()) {
			$tmp = $form->show();
			return $this->ajaxReturn(array(
				'status' => $status,
				'html' => $tmp
			));
		}
		elseif ($fromMain)
			return $form->show();
		else
			return $this->show($form->show());
	}

    /**
     * @return false|string
     * @throws phpmailerException
     */
    function moveArticle() {
		$src = 0;
		$dest = 0;
		$status = false;
		if (array_key_exists('src',$_REQUEST)) $src = $_REQUEST['src'];
		if (array_key_exists('dest',$_REQUEST)) $dest = $_REQUEST['dest'];
		if ($_REQUEST['type'] == 'tree') {
			if ($src == 0 || $dest == 0 || !array_key_exists('type',$_REQUEST)) {
				$this->addError('Either source or destination was missing');
				return $this->ajaxReturn(array('status' => 'false'));
			}
			if ($folder = $this->fetchSingleTest('select * from %s where id = %d',$this->m_tree,$dest)) {
				$curr = $this->fetchScalarTest('select member_id from %s where id = %d',$this->m_junction,$src);
				$status = true;
				if (array_key_exists('move',$_REQUEST)) {
					//
					//	move it - delete all other folders
					//
					$this->logMessage('moveArticle', sprintf('moving store %d to folder %d',$src,$dest),2);
					$this->beginTransaction();
					if ($status = $this->execute(sprintf('delete from %s where id = %d', $this->m_junction, $src))) {
						if (!$this->fetchSingleTest('select * from %s where member_id = %d and folder_id = %d',$this->m_junction,$curr,$dest)) {
							$obj = new preparedStatement(sprintf('insert into %s(member_id,folder_id) values(?,?)',$this->m_junction));
							$obj->bindParams(array('dd',$curr,$dest));
							$status = $obj->execute();
						}
					}
					if ($status)
						$this->commitTransaction();
					else
						$this->rollbackTransaction();
				}
				else {
					//
					//	add it - if it doesn't already exist
					//
					$curr = $src;
					$this->logMessage('moveArticle', sprintf('cloning member %d to folder %d',$curr,$dest),2);
					if (!($this->fetchSingleTest('select * from %s where member_id = %d and folder_id = %d',$this->m_junction,$curr,$dest))) {
						$obj = new preparedStatement(sprintf('insert into %s(member_id,folder_id) values(?,?)',$this->m_junction));
						$obj->bindParams(array('dd',$curr,$dest));
						$status = $obj->execute();
					}
				}
			} else {
				$status = false;
				$this->addError('Could not locate the destination folder');
			}
		}
		else {
			if ($src == 0 || $dest < 0) {
				$this->addMessage('Either source or destination was missing');
				return $this->ajaxReturn(array('status' => 'false'));
			}
			$src = $this->fetchSingleTest('select * from %s where id = %d',$this->m_junction,$src);
			$sql = sprintf('select * from %s where folder_id = %d order by sequence limit %d,1',$this->m_junction,$src['folder_id'],$dest);
			$dest = $this->fetchSingleTest($sql);
			$this->logMessage("moveArticle",sprintf("move src [%s] to dest [%s] sql [%s]",print_r($src,true),print_r($dest,true),$sql),2);
			if (count($src) == 0 || count($dest) == 0) {
				$status = false;
				$this->addMessage('Either the source or destination article was not found');
			}
			else {
				//
				//	swap the order of the images
				//
				$this->logMessage('moveArticle', sprintf('swap the sort order of %d and %d',$src['id'],$dest['id']),2);
				$this->beginTransaction();
				$sql = sprintf('update %s set sequence = %d where id = %s',
					$this->m_junction, $src['sequence'] < $dest['sequence'] ? $dest['sequence']+1 : $dest['sequence']-1, $src['id']);
				if ($this->execute($sql)) {
					$this->resequence($src['folder_id']);
					$this->commitTransaction();
					$status = true;
				}
				else {
					$this->rollbackTransaction();
					$status = false;
				}					
			}
		}
		return $this->ajaxReturn(array(
				'status'=>$status?'true':'false'
		));
	}

    /**
     * @param $folder
     * @return void
     * @throws phpmailerException
     */
    function resequence($folder) {
		$this->logMessage('resequence', "resequencing folder $folder", 2);
		$articles = $this->fetchAllTest('select * from %s where folder_id = %d order by sequence',$this->m_junction,$folder);
		$seq = 10;
		foreach($articles as $article) {
			$this->execute(sprintf('update %s set sequence = %d where id = %d',$this->m_junction,$seq,$article['id']));
			$seq += 10;
		}
	}

    /**
     * @return false|string
     * @throws phpmailerException
     */
    function deleteArticle() {
		$form = new Forms();
		$form->init($this->getTemplate('deleteItem'));
		$flds = $form->buildForm($this->getFields('deleteItem'));
		if (count($_REQUEST) > 0 && $_REQUEST['j_id'] == 0)
			$form->getField('one')->addAttribute('disabled','disabled');
		$form->addData($_REQUEST);
		if (count($_REQUEST) > 0 && array_key_exists('deleteItem',$_REQUEST)) {
			if ($form->validate()) {
				$type = $form->getData('action');
				switch($type) {
					case 'cancel':
						return $this->ajaxReturn(array('status'=>'true','code'=>'closePopup();'));
						break;
					case 'all':
						//$img = $this->fetchScalarTest('select member_id from %s where id = %d',$this->m_junction,$_REQUEST['j_id']);
						$this->execute(sprintf('delete from %s where member_id = %d',$this->m_junction,$_REQUEST['m_id']));
						$this->execute(sprintf('update %s set deleted = 1 where id = %d',$this->m_content,$_REQUEST['m_id']));
						break;
					case 'one':
						$status = $this->execute(sprintf('delete from %s where id = %d',$this->m_junction,$_REQUEST['j_id']));
						$ct = $this->fetchScalarTest('select count(0) from %s where member_id = %d',$this->m_junction,$_REQUEST['m_id']);
						if ($ct == 0)
							$this->execute(sprintf('update %s set deleted = 1 where id = %d',$this->m_content,$_REQUEST['m_id']));
						break;
					default:
						break;
				}
				$form->init($this->getTemplate('deleteItemResult'));
			}
		}
		return $this->ajaxReturn(array('status'=>'true','html'=>$form->show()));
	}

    /**
     * @return false|string
     * @throws phpmailerException
     */
    function editAddress() {
		if (!array_key_exists('a_id',$_REQUEST))
			return $this->ajaxReturn(array('status'=>'false','html'=>'No id passed'));
		$a_id = $_REQUEST['a_id'];
		$o_id = $_REQUEST['o_id'];
		if (!($data = $this->fetchSingleTest('select a.* from addresses a where a.id = %d and a.ownertype = "member" and a.ownerid = %d',$a_id,$o_id))) {
			$data = array('id'=>0,'ownertype'=>'member','ownerid'=>$o_id,'address_book'=>1);
			$addresses = array();
		}
		else 
			$addresses = $this->fetchAllTest('select * from addresses where ownertype = "member" and ownerid = %d and deleted = 0',$o_id);
		$form = new Forms();
		$form->init($this->getTemplate('editAddress'),array('name'=>'editAddress'));
		$frmFields = $this->getFields('editAddress');
		if (count($addresses) > 0) {
			$frmFields['delete'] = array('type'=>'button','class'=>'def_field_submit','value'=>'Delete Address','database'=>false,'onclick'=>sprintf('deleteAddress(%d,%d);return false;',$a_id,$o_id));
		}
		$frmFields = $form->buildForm($frmFields);
		$form->addData($data);
		if (count($_POST) > 0 && array_key_exists('editAddress',$_POST)) {
			$form->addData($_POST);
			if ($form->validate()) {
				unset($frmFields['options']);
				$form->setData('tax_address',$this->fetchScalarTest('select extra from code_lookups where id = %d',$_POST['addresstype']));
				if ($form->getData("addresstype") == ADDRESS_COMPANY) $form->setData("address_book",1);
				if ($form->getData("geocode") == 1) {
					$lat = 0;
					$lng = 0;
					$status = $this->geocode($form->getAllData(),$lat,$lng);
					if ($status) {
						$form->setData("latitude",$lat);
						$form->setData("longitude",$lng);
					}
				}
				foreach($frmFields as $key=>$fld) {
					if (!(array_key_exists('database',$fld) && $fld['database'] == false)) {
						$flds[$fld['name']] = $form->getData($fld['name']);//$_REQUEST[$fld['name']];
					}
				}
				if ($data['id'] == 0) {
					$stmt = $this->prepare(sprintf('insert into addresses(%s) values(%s)', implode(',',array_keys($flds)), str_repeat('?,', count($flds)-1).'?'));
				}
				else {
					$stmt = $this->prepare(sprintf('update addresses set %s=? where id = %d', implode('=?,',array_keys($flds)),$data['id']));
				}
				$stmt->bindParams(array_merge(array(str_repeat('s', count($flds))),array_values($flds)));
				$this->beginTransaction();
				if ($stmt->execute()) {
					if ($data["id"] == 0) {
						$data["id"] = $this->insertId();
						$this->logMessage(__FUNCTION__,sprintf("set id to [%s]",$data["id"]),1);
					}
					$this->commitTransaction();
					$form->init($this->getTemplate(__FUNCTION__."Success"));
				}
				else {
					$this->rollbackTransaction();
					$this->addError('An error occurred updating the database');
				}
			}
			else {
				$this->addError('Form validation Failed');
				$status = false;
			}
		}
		$addresses = $this->fetchAllTest('select * from addresses where ownertype = "member" and ownerid = %d and deleted = 0',$o_id);
		return $this->ajaxReturn(array('status'=>'true','html'=>$form->show()));
	}

    /**
     * @return false|string|void
     * @throws phpmailerException
     */
    function deleteAddress() {
		if (array_key_exists('a_id',$_REQUEST) && array_key_exists('o_id',$_REQUEST)) {
			$this->logMessage('deleteAddress',sprintf('deleting id [%d] owner [%d]',$_REQUEST['a_id'],$_REQUEST['o_id']),1);
			if ($data = $this->fetchSingleTest('select * from addresses where ownertype = "%s" and id = %d and ownerid = %d',$_REQUEST['type'],$_REQUEST['a_id'],$_REQUEST['o_id'])) {
				$this->execute(sprintf('update addresses set deleted = 1 where id = %d',$_REQUEST['a_id']));
				return $this->ajaxReturn(array('status'=>'true'));
			}
		}
	}

    /**
     * @param $member_id
     * @param $folder_id
     * @return array|mixed|string|string[]|void
     * @throws phpmailerException
     */
    function getProfile($member_id, $folder_id) {
		$this->logMessage("getProfile",sprintf("($member_id,$folder_id)"),1);
		if ($rec = $this->fetchSingleTest('select p.*, t.title from members_by_folder p, members_folders t where p.member_id = %d and p.folder_id = %d and t.id = p.folder_id',$member_id,$folder_id)) {
			$form = new Forms();
			$form->init($this->getTemplate('getProfile'));
			$flds = $form->buildForm($this->getFields('getProfile'));
			$form->addData($rec);
			return $form->show();
		}
	}

    /**
     * @return false|string|void
     * @throws phpmailerException
     */
    function editProfile() {
		if (array_key_exists('p_id',$_REQUEST) && $data = $this->fetchSingleTest('select * from members_by_folder where id = %d',$_REQUEST['p_id'])) {
			$form = new Forms();
			$form->init($this->getTemplate('editProfile'));
			$formFlds = $this->getFields('editProfile');

			$customFields = new custom();
			if (method_exists($customFields,'memberGroupDisplay')) {
				$custom = $customFields->memberGroupDisplay();
				$form->addTag('customTab',sprintf('<li><a href="#tabs-custom">%s</a></li>',$custom['description']),false);
				$html = $form->getHTML();
				$html = str_replace('%%customInfo%%',$custom['form'],$html);
				$form->setHTML($html);
				$formFlds = array_merge($formFlds,$custom['fields']);
			}

			$formFlds = $form->buildForm($formFlds);
			$data['imagesel_c'] = $data['image1'];
			$data['imagesel_d'] = $data['image2'];
			$form->addData($data);
			if (count($_POST) > 0 && array_key_exists('editProfile',$_POST)) {
				$form->addData($_POST);
				if ($form->validate()) {
					$this->logMessage("editProfile",sprintf("validated"),2);
					$flds = array();
					foreach($formFlds as $key=>$fld) {
						if (!(array_key_exists('database',$fld) && $fld['database'] == false)) {
							if ($key != 'options')
								$flds[$key] = $form->getData($fld['name']);
						}
					}
					$stmt = $this->prepare(sprintf('update %s set %s where id = %d',$this->m_junction, implode('=?, ',array_keys($flds))."=?",$data['id']));
					$stmt->bindParams(array_merge(array(str_repeat('s', count($flds))),array_values($flds)));
					$this->beginTransaction();
					if (!$stmt->execute()) {
						$this->addError('Update Failed');
						$this->rollbackTransaction();
						$status = 'false';
					}
					else {
						$this->commitTransaction();
						$this->addMessage('Record Updated');
						$status = 'true';
					}
					$form->init($this->getTemplate('editProfileResult'));
					return $this->ajaxReturn(array('status'=>$status,'html'=>$form->show()));
				}
				else {
					$form->addError('Form Validation Failed');
					$this->logMessage("editProfile",sprintf("failed validation"),2);
				}

			}
			return $this->ajaxReturn(array('status'=>'true','html'=>$form->show()));
		}
	}

    /**
     * @param $passed_id
     * @param bool $byAjax
     * @return array|false|mixed|string|string[]
     * @throws phpmailerException
     */
    function loadAddresses($passed_id = null, $byAjax = true) {
		if ($passed_id == null) {
			if (array_key_exists('o_id',$_REQUEST))
				$o_id = $_REQUEST['o_id'];
			else
				$o_id = $_REQUEST['m_id'];
		}
		else
			$o_id = $passed_id;
		$addressForm = new Forms();
		$addressForm->init($this->getTemplate(__FUNCTION__));
		$addressForm->buildForm($this->getFields(__FUNCTION__));
		$addressForm->setData("o_id", $o_id);
		$addrForm = new Forms();
		$addrForm->init($this->getTemplate(__FUNCTION__."Row"));
		$addrFields = $addrForm->buildForm($this->getFields(__FUNCTION__."Row"));
		$srch = array('j1'=>'ownertype = "member"','j2'=>sprintf('ownerid = %d',$o_id),'j3'=>'deleted = 0','j4'=>'c.id = a.addressType','j5'=>'a.address_book = 1');
		$srch["addressType"] = sprintf('c.id = %d', $addressForm->getData("addressType") > 0 ? $addressForm->getData("addressType") : ADDRESS_COMPANY);
		if (array_key_exists(__FUNCTION__,$_POST)) {
			$addressForm->addData($_POST);
			foreach($_POST as $k=>$v) {
				switch($k) {
				case "addressType":
					$srch["addressType"] = sprintf('c.id = %d', $addressForm->getData("addressType"));
					break;
				case "addressText":
					if (strlen($v) > 0) $srch["addressText"] = sprintf('(city like "%%%1$s%%" or line1 like "%%%1$s%%" or company like "%%%1$s%%" or postalcode like "%%%1$s%%")', $v);
					break;
				default:
				}
			}
		}
		$count = $this->fetchScalarTest('select count(a.id) from addresses a, code_lookups c where  %s',implode(" and ",$srch));
		if (array_key_exists('pagenum',$_REQUEST)) 
			$pageNum = $_REQUEST['pagenum'];
		else
			$pageNum = 1;	// no 0 based calcs
		if ($pageNum <= 0) $pageNum = 1;
		$perPage = $this->m_perrow;
		if (array_key_exists('pager',$_REQUEST)) $perPage = $_REQUEST['pager'];
		$pagination = $this->pagination($count, $perPage, $pageNum, 
			array('prev'=>$this->M_DIR.'forms/paginationPrev.html','next'=>$this->M_DIR.'forms/paginationNext.html',
				'pages'=>$this->M_DIR.'forms/paginationPage.html', 'wrapper'=>$this->M_DIR.'forms/paginationWrapper.html','url'=>'addressPager()','dest'=>'addressTab'));
		$addressForm->setData('pagination',$pagination);
		$start = ($pageNum-1)*$perPage;
		$sortBy = strlen($addressForm->getData("sortby")) > 0 ? $addressForm->getData("sortby") : "company";		
		$addresses = $this->fetchAllTest(sprintf('select a.*, c.value as addressType from addresses a, code_lookups c where  %s order by %s limit %d, %d',implode(" and ",$srch), $sortBy, $start, $perPage));
		$addressList = array();
		foreach($addresses as $rec) {
			$addrForm->addData($rec);
			$addressList[] = $addrForm->show();
		}
		$addressForm->addTag('addresses',implode('',$addressList),false);
		if (!is_null($passed_id)) {
			$this->logMessage(__FUNCTION__,sprintf("returning normal show pass_id [%s] byAjax [%s]",$passed_id,$byAjax),3);
			return $addressForm->show();
		}
		else {
			$this->logMessage(__FUNCTION__,sprintf("returning ajax result show pass_id [%s] isAjax [%s]",$passed_id,$this->isAjax()),3);
			return $this->ajaxReturn(array('status'=>'true','html'=>$addressForm->show()));
		}
	}

    /**
     * @param int $fromMain
     * @return array|false|mixed|string|string[]|void
     * @throws phpmailerException
     */
    function moduleStatus($fromMain = 0) {
		if (array_key_exists('formData',$_SESSION) && array_key_exists('membersSearchForm', $_SESSION['formData'])) {
			$_POST = $_SESSION['formData']['membersSearchForm'];
			$msg = '';
		}
		else {
			$ct = $this->fetchScalarTest('select count(0) from %s where deleted = 0 and enabled = 0',$this->m_content);
			if ($ct == 0) {
				$_POST = array('showSearchForm'=>1,'deleted'=>0,'sortby'=>'created','sortorder'=>'desc','pager'=>$this->m_perrow);
				$msg = "Showing latest members added";
			}
			else {
				$_POST = array('showSearchForm'=>1,'enabled'=>0,'deleted'=>0,'sortby'=>'created','sortorder'=>'desc','pager'=>$this->m_perrow);
				$msg = "Showing disabled members";
			}
		}
		$result = $this->showSearchForm($fromMain,$msg);
		return $result;
	}

    /**
     * @return array|mixed|string|string[]
     * @throws phpmailerException
     */
    function getHeader() {
		$form = new Forms();
		$form->init($this->getTemplate('header'));
		$flds = $form->buildForm($this->getFields('showSearchForm'));
		if (count($_POST) > 0 && array_key_exists('showSearchForm',$_POST))
			$form->addData($_POST);
		return $form->show();
	}

    /**
     * @param bool $fromMain
     * @return array|mixed|string|string[]|void
     * @throws phpmailerException
     */
    function addFolder($fromMain = false) {
		$form = new Forms();
		$form->init($this->getTemplate('addFolder'));
		if ($fromMain)
			return $form->show();
		else
			return $this->show($form->show());
	}

    /**
     * @param bool $fromMain
     * @return array|mixed|string|string[]|void
     * @throws phpmailerException
     */
    function addItem($fromMain = false) {
		$form = new Forms();
		$form->init($this->getTemplate('addItem'));
		if ($fromMain)
			return $form->show();
		else
			return $this->show($form->show());
	}

    /**
     * @return false|string
     * @throws phpmailerException
     */
    function showOrders() {
		if (array_key_exists('o_id',$_REQUEST))
			$data = $this->fetchSingleTest('select * from members where id = %d',$_REQUEST['o_id']);
		else $data = array('id'=>0);
		$form = new Forms();
		$form->init($this->getTemplate('showOrders'));
		$formFlds = $form->buildForm($this->getFields('showOrders'));
		$form->addData($_REQUEST);
		$where = sprintf('select * from orders where member_id = %d',$data['id']);
		$and[] = "1=1";
		if (count($_POST) > 0 && array_key_exists("showOrders",$_POST)) {
			$status = 0;
			if (array_key_exists("order_status",$_POST)) {
				foreach($_POST["order_status"] as $key=>$value) {
					$status |= $value;
				}
			}
			if ($status > 0)
				$and[] = sprintf("order_status & %d = %d",$status,$status);
		}
		$count = $this->fetchScalarTest("select count(0) from orders where member_id = %d and %s",$data['id'], implode(" and ",$and));
		$perPage = $_SESSION["formData"]["membersSearchForm"]["pager"];
		$pageNum = array_key_exists('pagenum',$_POST) ? $_POST['pagenum'] : 1;
		$pagination = $this->pagination($count, $perPage, $pageNum, 
				array('prev'=>$this->M_DIR.'forms/paginationPrev.html','next'=>$this->M_DIR.'forms/paginationNext.html',
						'pages'=>$this->M_DIR.'forms/paginationPage.html', 'wrapper'=>$this->M_DIR.'forms/paginationWrapper.html','url'=>'"/modit/ajax/showOrders/members"','dest'=>'altPopup'));
		$start = ($pageNum-1)*$perPage;	
		$sql = sprintf("%s and %s order by id desc limit %d,%d",$where,implode(" and ",$and),$start,$perPage);	
		$orders = $this->fetchAllTest($sql);
		$form->addTag("pagination",$pagination,false);
		$return = array();
		$inner = new Forms();
		$inner->init($this->getTemplate('memberOrder'));
		$innerFlds = $inner->buildForm($this->getFields('memberOrder'));
		$orderObj = new orders();
		foreach($orders as $order) {
			$inner->addData($orderObj->formatOrder($order));
			$return[] = $inner->show();
		}
		$form->addTag('orders',implode('',$return),false);
		$form->addData($data);
		return $this->ajaxReturn(array('status'=>'true','html'=>$form->show()));
	}

    /**
     * @return false|string
     * @throws phpmailerException
     */
    function loadMedia() {
		$form = new Forms();
		$form->init($this->getTemplate('loadMedia'),array('name'=>'loadMedia'));
		$frmFields = $form->buildForm($this->getFields('loadMedia'));
		$f_id = array_key_exists('f_id',$_REQUEST) ? $_REQUEST['f_id'] : 0;
		$m_id = array_key_exists('m_id',$_REQUEST) ? $_REQUEST['m_id'] : 0;
		$form->setData('member_id',$m_id);
		$form->setData('folder_id',$f_id);
		$media = $this->fetchAllTest('select * from %s where folder_id = %d and member_id = %d',$this->m_media,$f_id,$m_id);
		$inner = new Forms();
		$inner->init($this->getTemplate('listMedia'),array('name'=>'listMedia'));
		$iFields = $this->getFields('listMedia');
		$return = array();
		foreach($media as $key=>$value) {
			$inner->addData($value);
			$return[] = $inner->show();
		}
		$form->addTag('media',implode('',$return),false);
		return $this->ajaxReturn(array('status'=>'true','html'=>$form->show()));
	}

    /**
     * @return false|string
     * @throws phpmailerException
     */
    function editMedia() {
		$form = new Forms();
		$form->init($this->getTemplate('editMedia'),array('name'=>'editMedia'));
		$frmFields = $this->getFields('editMedia');
		if (array_key_exists('m_id',$_REQUEST)) {
			$form->setData('member_id',$_REQUEST['m_id']);
		}
		if (array_key_exists('f_id',$_REQUEST)) {
			$form->setData('folder_id',$_REQUEST['f_id']);
		}
		if (!(array_key_exists('p_id',$_REQUEST) && $data = $this->fetchSingleTest('select * from %s where id = %d',$this->m_media,$_REQUEST['p_id']))) {
			$data = array('id'=>0);
		}
		if ($data['id'] != 0) {
			$frmFields['filename'] = array('type'=>'tag','required'=>false,'database'=>false,'reformatting'=>false);
			$tmp = new Forms();
			$tmp->init($this->M_DIR.'forms/play'.$data['type'].'.html');
			$tmp->addData($data);
			$data['filename'] = $tmp->show();
			$this->logMessage('editMedia',sprintf('reset filename'),1);
		}
		else $this->logMessage('editMedia',sprintf('must be an add [%s]',print_r($data,true)),1);
		$this->logMessage('editMedia',sprintf('frmFields [%s]',print_r($frmFields,true)),1);
		$frmFields = $form->buildForm($frmFields);
		$form->addData($data);
		if (array_key_exists('editMedia',$_REQUEST)) {
			$form->addData($_REQUEST);
			$valid = $form->validate();
			$this->logMessage('editMedia',sprintf('validate returned [%s]',$valid),1);
			$return = array();
			if ($valid && $data['id'] == 0) {
				if (count($_FILES) == 0) {
					$valid = false;
					$this->addFormError('No file was attached');
				}
				else {
					if (!($valid = $this->processUploadedFiles(array('Image','Video','Audio'),$return,$messages))) {
						foreach($messages as $key=>$value)
							$form->addFormError($value);
					}
				}
			}
			if ($valid) {
				foreach($frmFields as $key=>$fld) {
					if (!(array_key_exists('database',$fld) && $fld['database'] == false)) {
						$values[] = $form->getData($fld['name']);
						if ($data['id'] > 0)
							$flds[sprintf('%s = ?',$fld['name'])] = $form->getData($fld['name']);
						else
							$flds[$fld['name']] = $form->getData($fld['name']);
					}
				}
				if ($data['id'] == 0) {
					$flds['filename'] = $return['filename']['name'];
					$flds['type'] = $return['filename']['type'];
				}
				$adding = $data['id'] == 0;
				if ($adding) {
					$stmt = $this->prepare(sprintf('insert into %s(%s) values(%s)', $this->m_media, implode(',',array_keys($flds)), str_repeat('?,', count($flds)-1).'?'));
					$stmt->bindParams(array_merge(array(str_repeat('s', count($flds))),array_values($flds)));
				}
				else {
					$stmt = $this->prepare(sprintf('update %s set %s where id = %d', $this->m_media, implode(',',array_keys($flds)),$data['id']));
					$stmt->bindParams(array_merge(array(str_repeat('s', count($flds))),array_values($flds)));
				}
				$this->beginTransaction();
				if ($stmt->execute()) {
					$form->addFormError('data saved');
					$this->commitTransaction();
					$form->init($this->getTemplate('editMediaSuccess'));
				}
				else
					$form->addFormError('An Error occurred');
					$this->rollbackTransaction();
			}
		}
		return $this->ajaxReturn(array('status'=>'true','html'=>$form->show()));
	}

    /**
     * @return false|string
     * @throws phpmailerException
     */
    function deleteMedia() {
		$form = new Forms();
		$form->init($this->getTemplate('deleteMedia'));
		$form->addData($_REQUEST);
		if (array_key_exists('p_id',$_REQUEST)) {
			$file = $this->fetchScalarTest('select filename from %s where id = %d and folder_id = %d and member_id = %d',$this->m_media,$_REQUEST['p_id'],$_REQUEST['f_id'],$_REQUEST['m_id']);
			$status = $this->execute(sprintf('delete from %s where id = %d and folder_id = %d and member_id = %d',$this->m_media,$_REQUEST['p_id'],$_REQUEST['f_id'],$_REQUEST['m_id']));
			unlink('..'.$file);
		}
		else $status = false;
		return $this->ajaxReturn(array('status'=>$status,'html'=>$form->show()));
	}

    /**
     * @return false|string|void
     * @throws phpmailerException
     */
    function deleteContent() {
		if (array_key_exists('p_id',$_REQUEST)) {
			$ct = $this->fetchScalarTest('select count(0) from %s where folder_id = %d',$this->m_junction,$_REQUEST['p_id']);
			if ($ct > 0) {
				$this->addError('Events are still attached to this folder');
				return $this->ajaxReturn(array('status'=>'false'));
			}
			$ct = $this->fetchScalarTest(sprintf('select count(0) from %s t1, %s t2 where t2.id = %d and t1.left_id > t2.left_id and t1.right_id < t2.right_id and t1.level > t2.level',$this->m_tree, $this->m_tree, $_REQUEST['p_id']));
			if ($ct > 0) {
				$this->addError('Other categories are still attached to this folder');
				return $this->ajaxReturn(array('status'=>'false'));
			}
			$ct = $this->fetchScalarTest('select count(0) from members_by_folder where folder_id = %d', $_REQUEST['p_id']);
			if ($ct > 0) {
				$this->addError('Site Members are still attached to this folder');
				return $this->ajaxReturn(array('status'=>'false'));
			}
			if ($ct > 0) {
				$this->addError('Other categories are still attached to this folder');
				return $this->ajaxReturn(array('status'=>'false'));
			}

			if (!$this->deleteCheck('calendar',$_REQUEST['p_id'],$inUse)) {
				$this->addError('Some Pages or Templates still use this folder');
				foreach($inUse as $key=>$value) {
					$this->addError($value);
				}
				return $this->ajaxReturn(array('status'=>'false'));
			}
			$mptt = new mptt($this->m_tree);
			$mptt->delete($_REQUEST['p_id']);
			return $this->ajaxReturn(array('status'=>'true'));
		}
	}

    /**
     * @return false|string
     * @throws phpmailerException
     */
    function getProducts() {
		$m = array_key_exists('m',$_REQUEST) ? $_REQUEST['m'] : 0;
		$g = array_key_exists('g',$_REQUEST) ? $_REQUEST['g'] : 0;
		$f = new Forms();
		$f->init($this->getTemplate("memberProducts"));		
		$recs = $this->fetchAllTest("select cp.*, p.custom_out_of_zone_rate, p.custom_km_charge, p.custom_km_mincharge, p.custom_km_maxcharge, p.name, p.custom_zone_surcharge, p.custom_minimum_charge, p.custom_downtown_surcharge from custom_member_product_options cp, product p where cp.member_id = %d and cp.isgroup = %d and p.id = cp.product_id and p.is_fedex=0 order by p.custom_km_mincharge*(1+cp.km_mincharge/100)",$m,$g);
		$result = array();
		$inner = new Forms();
		$inner->init($this->getTemplate("memberProductsRow"));
		$flds = $inner->buildForm($this->getFields("memberProducts"));
		if ($g==0) $grp = $this->fetchSingleTest("select * from members_by_folder where member_id = %d limit 1",$m);
		foreach($recs as $key=>$value) {
			if ($g == 0) {
				//
				//	Member overrides are compounded onto group level overrides
				//
				$g_p = $this->fetchSingleTest("select cp.* from custom_member_product_options cp, members_by_folder mf where mf.id = %d and cp.member_id = mf.folder_id and isgroup = 1 and product_id = %d",$m,$value["product_id"]);
				$value["gZone"] = $this->my_money_format($value["custom_zone_surcharge"] * (1+(is_array($g_p) ? $g_p["zone_surcharge"] : 0 +$value["zone_surcharge"])/100));
				$value["gMinimum"] = $this->my_money_format($value["custom_minimum_charge"] * (1+(is_array($g_p) ? $g_p["minimum_charge"] : 0 +$value["minimum_charge"])/100));
				$value["gDowntown"] = $this->my_money_format($value["custom_downtown_surcharge"] * (1+(is_array($g_p) ? $g_p["downtown_surcharge"] : 0 +$value["downtown_surcharge"])/100));
				$value["gKmMin"] = $this->my_money_format($value["custom_km_mincharge"] * (1+(is_array($g_p) ? $g_p["km_mincharge"] : 0 +$value["km_mincharge"])/100));
				$value["gKmMax"] = $this->my_money_format($value["custom_km_maxcharge"] * (1+(is_array($g_p) ? $g_p["km_maxcharge"] : 0 +$value["km_maxcharge"])/100));
				$value["gKmRate"] = $this->my_money_format($value["custom_km_charge"] * (1+(is_array($g_p) ? $g_p["km_charge"] : 0 +$value["km_charge"])/100));
				$value["gOutOfZoneRate"] = $this->my_money_format($value["custom_out_of_zone_rate"] * (1+(is_array($g_p) ? $g_p["out_of_zone_rate"] : 0 +$value["out_of_zone_rate"])/100));
			}
			else {
				$value["gZone"] = $this->my_money_format($value["custom_zone_surcharge"] * (1+($value["zone_surcharge"]/100)));
				$value["gMinimum"] = $this->my_money_format($value["custom_minimum_charge"] * (1+($value["minimum_charge"]/100)));
				$value["gDowntown"] = $this->my_money_format($value["custom_downtown_surcharge"] * (1+($value["downtown_surcharge"]/100)));
				$value["gKmMin"] = $this->my_money_format($value["custom_km_mincharge"] * (1+($value["km_mincharge"]/100)));
				$value["gKmMax"] = $this->my_money_format($value["custom_km_maxcharge"] * (1+($value["km_maxcharge"]/100)));
				$value["gKmRate"] = $this->my_money_format($value["custom_km_charge"] * (1+($value["km_charge"]/100)));
				$value["gOutOfZoneRate"] = $this->my_money_format($value["custom_out_of_zone_rate"] * (1+($value["out_of_zone_rate"]/100)));
			}
			$inner->addData($value);
			$result[] = $inner->show();
		}
		$f->addTag("products",implode("",$result),false);
		$f->addTag("member_id",$m);
		$f->addTag("isgroup",$g);
		return $this->ajaxReturn(array('status'=>true,'html'=>$f->show()));
	}

    /**
     * @return false|string
     * @throws phpmailerException
     */
    function editProduct() {
		if (count($_POST) > 0 && array_key_exists('editProduct',$_POST)) {
			$p_id = array_key_exists('p_id',$_REQUEST) ? $_REQUEST['p_id'] : 0;
			$g = array_key_exists('isgroup',$_REQUEST) ? $_REQUEST['isgroup'] : 0;
			$m = array_key_exists('member_id',$_REQUEST) ? $_REQUEST['member_id'] : 0;
		}
		else {
			$p_id = array_key_exists('p',$_REQUEST) ? $_REQUEST['p'] : 0;
			$g = array_key_exists('g',$_REQUEST) ? $_REQUEST['g'] : 0;
			$m = array_key_exists('m',$_REQUEST) ? $_REQUEST['m'] : 0;
		}
		$has_grp = array_key_exists('has_grp',$_REQUEST) ? $_REQUEST['has_grp'] : 1;
		$f = new Forms();
		$f->init($this->getTemplate("editProduct"));
		$flds = $this->getFields("editProduct");
		if ($g == 1) {
			if (!($rec = $this->fetchSingleTest("select cp.*,p.name from custom_member_product_options cp, product p where cp.id = %d and cp.member_id = %d and p.id = cp.product_id and cp.isgroup = %d",$p_id,$m,$g))) {
				$rec = array("id"=>0,"member_id"=>$m,"isgroup"=>$g,"product_id"=>$p_id,"zone_surcharge"=>0,"downtown_surcharge"=>0,"minimum_charge"=>0,"km_mincharge"=>0,"km_maxcharge"=>0,"km_charge"=>0);
				$flds['product_id'] = array('type'=>'productOptGroup','required'=>true,'where'=>sprintf('p.id not in (select product_id from custom_member_product_options where member_id = %d and isgroup = %d)',$m,$g),'onchange'=>'getAllOverrides(this);');
			}
		} else {
			if (!$has_grp) {
				$rec = array("id"=>0,"member_id"=>$m,"isgroup"=>$g,"product_id"=>$p_id,"zone_surcharge"=>0,"downtown_surcharge"=>0,"minimum_charge"=>0,"km_mincharge"=>0,"km_maxcharge"=>0,"km_charge"=>0);
				$flds['product_id'] = array('type'=>'select','required'=>true,'sql'=>sprintf('select id,name from product where enabled = 1 and deleted = 0 and is_fedex = 0 and custom_special_requirement = 0 and id not in (select product_id from custom_member_product_options cpo, members_by_folder mbf where cpo.member_id = mbf.folder_id and mbf.id = %d and isgroup = 1) order by name', $m),'onchange'=>'getAllOverrides(this);');
			}
			else
				if (!($rec = $this->fetchSingleTest("select cp.*,p.name from custom_member_product_options cp, product p where cp.id = %d and cp.member_id = %d and p.id = cp.product_id and cp.isgroup = %d",$p_id,$m,$g))) {
					$rec = array("id"=>0,"member_id"=>$m,"isgroup"=>$g,"product_id"=>$p_id,"zone_surcharge"=>0,"downtown_surcharge"=>0,"minimum_charge"=>0,"km_mincharge"=>0,"km_maxcharge"=>0,"km_charge"=>0);
					$flds['product_id'] = array('type'=>'select','required'=>true,'sql'=>'select id,name from product where enabled = 1 and deleted = 0 and id not in (select product_id from custom_member_product_options where member_id = %%member_id%% and isgroup = %%isgroup%%) and id in (select product_id from custom_member_product_options cp, members_by_folder mf where mf.id = %%member_id%% and cp.member_id = mf.folder_id and cp.isgroup = 1) order by name','onchange'=>'getAllOverrides(this);');
				}
		}
		if ($rec["product_id"] > 0) {
			$p = $this->fetchSingleTest("select * from product where id = %d",$rec["product_id"]);
				//
				//	get group level overrides
				//
			if ($grp = $this->fetchSingleTest("select cp.* from custom_member_product_options cp, members_by_folder mf where mf.id = %d and cp.member_id = mf.folder_id and cp.product_id = %d",$m,$p["id"])) {
				$rec["zone_surcharge_display"] = $this->my_money_format($p["custom_zone_surcharge"]*(1+(is_array($grp) ? $grp["zone_surcharge"] : 0 +$rec["inter_downtown"])/100));
				$rec["inter_downtown_display"] = $this->my_money_format(round($p["custom_inter_downtown"]*(1+(is_array($grp) ? $grp["inter_downtown"] : 0 +$rec["inter_downtown"])/100),2));
				$rec["downtown_surcharge_display"] = $this->my_money_format($p["custom_downtown_surcharge"]*(1+(is_array($grp) ? $grp["downtown_surcharge"] : 0 +$rec["downtown_surcharge"])/100));
				$rec["minimum_charge_display"] = $this->my_money_format($p["custom_minimum_charge"]*(1+(is_array($grp) ? $grp["minimum_charge"] : 0 +$rec["minimum_charge"])/100));
				$rec["km_mincharge_display"] = $this->my_money_format($p["custom_km_mincharge"]*(1+(is_array($grp) ? $grp["km_mincharge"] : 0 +$rec["km_mincharge"])/100));
				$rec["km_maxcharge_display"] = $this->my_money_format($p["custom_km_maxcharge"]*(1+(is_array($grp) ? $grp["km_maxcharge"] : 0 +$rec["km_maxcharge"])/100));
				$rec["km_charge_display"] = $this->my_money_format($p["custom_km_charge"]*(1+(is_array($grp) ? $grp["km_charge"] : 0 +$rec["km_charge"])/100));
				$rec["out_of_zone_rate_display"] = $this->my_money_format($p["custom_out_of_zone_rate"]*(1+(is_array($grp) ? $grp["out_of_zone_rate"] : 0 +$rec["out_of_zone_rate"])/100));
			}
		}
		$this->logMessage(__FUNCTION__,sprintf("rec is [%s]", print_r($rec,true)),1);
		$rec["p_id"] = $p_id;
		$flds = $f->buildForm($flds);
		$f->addData($rec);
		if (count($_POST) > 0 && array_key_exists('editProduct',$_POST)) {
			$f->addData($_POST);
			if ($f->validate()) {
				$upd = array();
				foreach($flds as $key=>$fld) {
					if (!(array_key_exists('database',$fld) && $fld['database'] == false)) {
						$upd[$fld['name']] = $f->getData($fld['name']);
					}
				}
				if ($_POST["p_id"] == 0) {
					$stmt = $this->prepare(sprintf("insert into custom_member_product_options(%s) values(%s?)",implode(",",array_keys($upd)),str_repeat("?,", count($upd)-1)));
				}
				else {
					$stmt = $this->prepare(sprintf("update custom_member_product_options set %s=? where id = %d",implode("=?,",array_keys($upd)),$_POST["p_id"]));
				}
				$stmt->bindParams(array_merge(array(str_repeat("s", count($upd))),array_values($upd)));
				if (!$stmt->execute()) {
					$this->addError("Edit Failed");
				}
				else {
					if ($_POST["p_id"] == 0) {
						$p_id = $this->insertId();
						$f->setData("p_id",$p_id);
						$f->setData("new_product",1);
					}
					else {
						$f->setData("new_product",0);
					}
					$f->init($this->getTemplate("editProductSuccess"));
				}
			}
		}
		$f->addTag("kmRates",$this->kmRates($p_id));
		$f->addTag("byPC",$this->productByPC($p_id));
		if ($g == 0) {
			$f->addTag("kmOverrides",$this->kmRateOverride($p_id,$m));
		}
		return $this->ajaxReturn(array('status'=>true,'html'=>$f->show()));
	}

    /**
     * @return false|string
     * @throws phpmailerException
     */
    function editFedEx() {
		if (count($_POST) > 0 && array_key_exists('editFedEx',$_POST)) {
			$p_id = array_key_exists('p_id',$_REQUEST) ? $_REQUEST['p_id'] : 0;
			$g = array_key_exists('isgroup',$_REQUEST) ? $_REQUEST['isgroup'] : 0;
			$m = array_key_exists('member_id',$_REQUEST) ? $_REQUEST['member_id'] : 0;
		}
		else {
			$p_id = array_key_exists('p',$_REQUEST) ? $_REQUEST['p'] : 0;
			$g = array_key_exists('g',$_REQUEST) ? $_REQUEST['g'] : 0;
			$m = array_key_exists('m',$_REQUEST) ? $_REQUEST['m'] : 0;
		}
		$f = new Forms();
		$f->init($this->getTemplate("editFedEx"));
		$flds = $this->getFields("editFedEx");
		if ($g == 1) {
			if (!($rec = $this->fetchSingleTest("select cp.*,p.name from custom_member_product_options cp, product p where cp.id = %d and cp.member_id = %d and p.id = cp.product_id and cp.isgroup = %d",$p_id,$m,$g))) {
				$rec = array("id"=>0,"member_id"=>$m,"isgroup"=>$g,"product_id"=>$p_id,"zone_surcharge"=>0,"downtown_surcharge"=>0,"minimum_charge"=>0,"km_mincharge"=>0,"km_maxcharge"=>0,"km_charge"=>0);
				$flds['product_id'] = array('type'=>'select','required'=>true,'sql'=>sprintf('select p.id,p.name from product p, product_by_folder pf where enabled = 1 and deleted = 0 and p.id not in (select product_id from custom_member_product_options where member_id = %%%%member_id%%%% and isgroup = %%%%isgroup%%%%) and p.id = pf.product_id and pf.folder_id = %d order by name', FEDEX_PRODUCTS),'onchange'=>'getAllOverrides(this);');
			}
		} else {
			if (!($rec = $this->fetchSingleTest("select cp.*,p.name from custom_member_product_options cp, product p, product_by_folder pf where cp.id = %d and cp.member_id = %d and p.id = cp.product_id and cp.isgroup = %d and p.id = pf.product_id and pf.folder_id = %d",$p_id,$m,$g, FEDEX_PRODUCTS))) {
				$rec = array("id"=>0,"member_id"=>$m,"isgroup"=>$g,"product_id"=>$p_id,"zone_surcharge"=>0,"downtown_surcharge"=>0,"minimum_charge"=>0,"km_mincharge"=>0,"km_maxcharge"=>0,"km_charge"=>0);
				$flds['product_id'] = array('type'=>'select','required'=>true,'sql'=>sprintf('select p.id, p.name from product p, product_by_folder pf where p.enabled = 1 and p.deleted = 0 and p.id not in (select product_id from custom_member_product_options where member_id = %%%%member_id%%%% and isgroup = %%%%isgroup%%%%) and p.id in (select product_id from custom_member_product_options cp, members_by_folder mf where mf.id = %%%%member_id%%%% and cp.member_id = mf.folder_id and cp.isgroup = 1) and p.id = pf.product_id and pf.folder_id = %d order by name', FEDEX_PRODUCTS),'onchange'=>'getAllOverrides(this);');
			}
		}
/*
		if ($rec["product_id"] > 0) {
			$p = $this->fetchSingleTest("select * from product where id = %d",$rec["product_id"]);
			if ($g == 0) {
				//
				//	get group level overrides
				//
				$grp = $this->fetchSingleTest("select cp.* from custom_member_product_options cp, members_by_folder mf where mf.id = %d and cp.member_id = mf.folder_id and cp.product_id = %d",$m,$p["id"]);
				$p["custom_zone_surcharge"] = $p["custom_zone_surcharge"]*(1+$grp["zone_surcharge"]/100);
				$p["custom_downtown_surcharge"] = $p["custom_downtown_surcharge"]*(1+$grp["downtown_surcharge"]/100);
				$p["custom_minimum_charge"] = $p["custom_minimum_charge"]*(1+$grp["minimum_charge"]/100);
				$p["custom_km_mincharge"] = $p["custom_km_mincharge"]*(1+$grp["km_mincharge"]/100);
				$p["custom_km_maxcharge"] = $p["custom_km_maxcharge"]*(1+$grp["km_maxcharge"]/100);
				$p["custom_km_charge"] = $p["custom_km_charge"]*(1+$grp["km_charge"]/100);
				$p["custom_out_of_zone_rate"] = $p["custom_out_of_zone_rate"]*(1+$grp["out_of_zone_rate"]/100);

			}
			$rec["zone_surcharge_display"] = $this->my_money_format(round($p["custom_zone_surcharge"]*(1+$rec["zone_surcharge"]/100),2));
			$rec["downtown_surcharge_display"] = $this->my_money_format(round($p["custom_downtown_surcharge"]*(1+$rec["downtown_surcharge"]/100),2));
			$rec["minimum_charge_display"] = $this->my_money_format(round($p["custom_minimum_charge"]*(1+$rec["minimum_charge"]/100),2));
			$rec["km_mincharge_display"] = $this->my_money_format(round($p["custom_km_mincharge"]*(1+$rec["km_mincharge"]/100),2));
			$rec["km_maxcharge_display"] = $this->my_money_format(round($p["custom_km_maxcharge"]*(1+$rec["km_maxcharge"]/100),2));
			$rec["km_charge_display"] = $this->my_money_format(round($p["custom_km_charge"]*(1+$rec["km_charge"]/100),2));
			$rec["out_of_zone_rate_display"] = $this->my_money_format(round($p["custom_out_of_zone_rate"]*(1+$rec["out_of_zone_rate"]/100),2));

		}
		$this->logMessage(__FUNCTION__,sprintf("rec is [%s]", print_r($rec,true)),1);
*/
		$rec["p_id"] = $p_id;
		$flds = $f->buildForm($flds);
		$f->addData($rec);
		if (count($_POST) > 0 && array_key_exists('editProduct',$_POST)) {
			$f->addData($_POST);
			if ($f->validate()) {
				$upd = array();
				foreach($flds as $key=>$fld) {
					if (!(array_key_exists('database',$fld) && $fld['database'] == false)) {
						$upd[$fld['name']] = $f->getData($fld['name']);
					}
				}
				if ($_POST["p_id"] == 0) {
					$stmt = $this->prepare(sprintf("insert into custom_member_product_options(%s) values(%s?)",implode(",",array_keys($upd)),str_repeat("?,", count($upd)-1)));
				}
				else {
					$stmt = $this->prepare(sprintf("update custom_member_product_options set %s=? where id = %d",implode("=?,",array_keys($upd)),$_POST["p_id"]));
				}
				$stmt->bindParams(array_merge(array(str_repeat("s", count($upd))),array_values($upd)));
				if (!$stmt->execute()) {
					$this->addError("Edit Failed");
				}
				else {
					$f->init($this->getTemplate("editFedExSuccess"));
				}
			}
		}
		return $this->ajaxReturn(array('status'=>true,'html'=>$f->show()));
	}

    /**
     * @return false|string
     * @throws phpmailerException
     */
    function getProductOverride() {
		$v = $_REQUEST["v"];
		$r = "0.00";
		$f = new Forms();
		$f->init($this->getTemplate('getProductOverride'));
		if ($p = $this->fetchSingleTest("select * from product where id = %d",$_REQUEST["p_id"])) {
			$override = 0;
			if ($_REQUEST["isgroup"] == 0) {
				//
				//	Get the group override first
				//
				$grp = $this->fetchSingleTest("select cp.* from custom_member_product_options cp, members_by_folder mf where mf.id = %d and cp.member_id = mf.folder_id and cp.product_id = %d",$_REQUEST["m_id"],$_REQUEST["p_id"]);
				$override = is_array($grp) ? $grp[$_REQUEST["fld"]] : 0;
				//$p["custom_".$_REQUEST["fld"]] = $p["custom_".$_REQUEST["fld"]]*(1+$grp[$_REQUEST["fld"]]/100);
			}
			$r = round($p["custom_".$_REQUEST["fld"]] * (1+($override + $v)/100),2);
			$f->addData($p);
		}
		$f->addTag('result',$this->my_money_format($r),false);
		return $this->ajaxReturn(array('status'=>true,'html'=>$f->show()));
	}

    /**
     * @param $id
     * @return array|mixed|string|string[]
     * @throws phpmailerException
     */
    function getLogins($id) {
		$outer = new Forms();
		$outer->init($this->getTemplate(__FUNCTION__));
		$inner = new Forms();
		$inner->init($this->getTemplate(__FUNCTION__."Row"));
		$flds = $inner->buildForm($this->getFields(__FUNCTION__."Row"));
		$p = $this->fetchAllTest("select * from members_login where member_id = %d",$id);
		$recs = array();
		foreach($p as $key=>$value) {
			$inner->addData($value);
			$recs[] = $inner->show();
		}
		$outer->addTag("rows",implode("",$recs),false);
		return $outer->show();
	}

    /**
     * @return false|string
     * @throws phpmailerException
     */
    function showPayments() {
		$perPage = 5;
		$m_id = array_key_exists("m_id",$_REQUEST) ? $_REQUEST["m_id"] : 0;
		$outer = new Forms();
		$outer->init($this->getTemplate(__FUNCTION__));
		$oflds = $outer->buildForm($this->getFields(__FUNCTION__));
		$inner = new Forms();
		$inner->init($this->getTemplate(__FUNCTION__."Row"));
		$iflds = $inner->buildForm($this->getFields(__FUNCTION__."Row"));
		if (count($_POST) > 0 && array_key_exists(__FUNCTION__,$_POST))
			$outer->addData($_POST);
		else $outer->addData(array("pagenum"=>1));
		$pageNum = $outer->getData("pagenum");
		$count = $this->fetchScalarTest("select count(0) from order_payment where member_id = %d", $m_id);
		$pagination = $this->pagination($count, $perPage, $pageNum, 
				array('prev'=>$this->M_DIR.'forms/paginationPrev.html','next'=>$this->M_DIR.'forms/paginationNext.html',
						'pages'=>$this->M_DIR.'forms/paginationPage.html', 'wrapper'=>$this->M_DIR.'forms/paginationWrapper.html',
						'url'=>'"/modit/ajax/showPayments/members"'),array('dest'=>'popup'));
		$start = ($pageNum-1)*$perPage;
		$sql = sprintf("select * from order_payment where member_id = %d order by id desc limit %d,%d", $m_id, $start, $perPage);
		$recs = $this->fetchAllTest($sql);
		$data = array();
		foreach($recs as $key=>$rec) {
			$inner->addData($rec);
			$data[] = $inner->show();
		}
		$outer->addTag("payments",implode("",$data),false);
		$outer->addTag("pagination",$pagination,false);
		$outer->setData("m_id",$m_id);
		return $this->ajaxReturn(array('html'=>$outer->show(),'status'=>true));
	}

    /**
     * @return false|string
     * @throws phpmailerException
     */
    function paymentDetails() {
		$m_id = array_key_exists("m_id",$_REQUEST) ? $_REQUEST["m_id"] : 0;
		$p_id = array_key_exists("p_id",$_REQUEST) ? $_REQUEST["p_id"] : 0;
		$outer = new Forms();
		$outer->init($this->getTemplate(__FUNCTION__));
		$oflds = $outer->buildForm($this->getFields(__FUNCTION__));
		$inner = new Forms();
		$inner->init($this->getTemplate(__FUNCTION__."Row"));
		$iflds = $inner->buildForm($this->getFields(__FUNCTION__."Row"));
		$outer->addData($this->fetchSingleTest("select p.* from order_payment p where id = %d and member_id = %d", $p_id, $m_id));
		$recs = $this->fetchAllTest("select d.* from order_payment_detail d, order_payment p where p.member_id = %d and p.id = %d and d.payment_id = p.id order by order_id", $m_id, $p_id);
		$data = array();
		foreach($recs as $key=>$rec) {
			$inner->addData($rec);
			$data[] = $inner->show();
		}
		$outer->addTag("payments",implode("",$data),false);
		return $this->ajaxReturn(array('html'=>$outer->show(),'status'=>true));
	}

    /**
     * @return array|mixed|string|string[]|void
     * @throws phpmailerException
     */
    function editMember() {
		$outer = new Forms();
		$outer->init($this->getTemplate(__FUNCTION__));
		$outer->addData($_REQUEST);
		//return $this->ajaxReturn(array('status'=>true,'html'=>$outer->show()));
		return $this->show($outer->show());
	}

    /**
     * @return false|string
     * @throws phpmailerException
     */
    function getFedEx() {
		$m = array_key_exists('m',$_REQUEST) ? $_REQUEST['m'] : 0;
		$g = array_key_exists('g',$_REQUEST) ? $_REQUEST['g'] : 0;
		$f = new Forms();
		$f->init($this->getTemplate("memberFedEx"));
		$recs = $this->fetchAllTest("select cp.*, p.custom_fedex, p.custom_out_of_zone_rate, p.custom_km_charge, p.custom_km_mincharge, p.custom_km_maxcharge, p.name, p.custom_zone_surcharge, p.custom_minimum_charge, p.custom_downtown_surcharge from custom_member_product_options cp, product p, product_by_folder pf where cp.member_id = %d and cp.isgroup = %d and p.id = cp.product_id and p.id = pf.product_id and pf.folder_id = %d",$m,$g,FEDEX_PRODUCTS);
		$result = array();
		$inner = new Forms();
		$inner->init($this->getTemplate("memberFedExRow"));
		$flds = $inner->buildForm($this->getFields("memberFedEx"));
		if ($g==0) $grp = $this->fetchSingleTest("select * from members_by_folder where member_id = %d limit 1",$m);
		foreach($recs as $key=>$value) {
			$override = 0;
			if ($g == 0) {
				//
				//	Member overrides are compounded onto group level overrides
				//
				$g_p = $this->fetchSingleTest("select cp.* from custom_member_product_options cp, members_by_folder mf where mf.id = %d and cp.member_id = mf.folder_id and isgroup = 1 and product_id = %d",$m,$value["product_id"]);
				//$value["gfedex"] = ((1+$value["custom_fedex"]/100)*(1+$g_p["fedex"]/100));
				//$value["gfedex"] = round((($value["gfedex"] * (1+($value["fedex"]/100)))-1)*100,2);
				$override = $g_p["fedex"];
			}
			$value["gfedex"] = round($value["custom_fedex"] + $override + $value["fedex"],2);
			$inner->addData($value);
			$result[] = $inner->show();
		}
		$f->addTag("products",implode("",$result),false);
		$f->addTag("member_id",$m);
		$f->addTag("isgroup",$g);
		return $this->ajaxReturn(array('status'=>true,'html'=>$f->show()));
	}

    /**
     * @return false|string
     * @throws phpmailerException
     */
    function removeProduct() {
		$p_id = array_key_exists("id",$_REQUEST) ? $_REQUEST["id"] : 0;
		$m_id = array_key_exists("id",$_REQUEST) ? $_REQUEST["m_id"] : 0;
		$g_id = array_key_exists("id",$_REQUEST) ? $_REQUEST["is_group"] : 0;
		$outer = new Forms();
		$outer->init($this->getTemplate(__FUNCTION__));
		$status = false;
		if ($rec = $this->fetchSingleTest("select * from custom_member_product_options where id = %d and member_id = %d and isgroup = %d", $p_id, $m_id, $g_id )) {
			$this->execute(sprintf("delete from custom_member_product_options where id = %d", $p_id));
			$outer->addData($rec);
			$status = true;
		}
		else {
		}
		return $this->ajaxReturn(array('status'=>$status,'html'=>$outer->show()));
	}

    /**
     * @param $id
     * @param int $fromMain
     * @return false|string
     * @throws phpmailerException
     */
    function listPerPiece($id = null, $fromMain = 0) {
		$outer = new Forms();
		if (!$fromMain) $id = array_key_exists("m_id", $_REQUEST) ? $_REQUEST["m_id"] : 0;
		$outer->init($this->getTemplate(__FUNCTION__));
		$flds = $outer->buildForm($this->getFields(__FUNCTION__));
		$recs = $this->fetchAllTest("select c.*, p.name, p.code from member_package_charges c, product p where c.member_id = %d and p.id = c.product_id order by p.name", $id);
		$rows = array();
		foreach($recs as $k=>$v) {
			$outer->addData($v);
			$rows[] = $outer->show();
		}
		if ($fromMain) {
			return implode("",$rows);
		}
		else {
			return $this->ajaxReturn(array("status"=>true, "html"=>implode("",$rows)));
		}
	}

    /**
     * @return false|string
     * @throws phpmailerException
     */
    function editPerPiece() {
		$outer = new Forms();
		$outer->init($this->getTemplate(__FUNCTION__));
		$flds = $outer->buildForm($this->getFields(__FUNCTION__));
		$p_id = array_key_exists("p_id",$_REQUEST) ? $_REQUEST["p_id"] : 0;
		$m_id = array_key_exists("m_id",$_REQUEST) ? $_REQUEST["m_id"] : 0;
		if (!$data = $this->fetchSingleTest("select * from member_package_charges where id = %d and member_id = %d", $p_id, $m_id))
			$data = array("id"=>0,"member_id"=>$m_id);
		$outer->addData($data);
		if (array_key_exists(__FUNCTION__,$_POST)) {
			$outer->addData($_POST);
			if ($outer->validate()) {
				$upd = array();
				foreach($flds as $k=>$v) {
					if (!(array_key_exists("database",$v) && $v["database"]==false)) {
						$upd[$k] = $outer->getData($k);
					}
				}
				if ($p_id == 0) {
					$upd["member_id"] = $outer->getData("m_id");
					$stmt = $this->prepare(sprintf("insert into member_package_charges(%s) values(?%s)", implode(", ", array_keys($upd)), str_repeat(", ?", count($upd)-1)));
				}
				else
					$stmt = $this->prepare(sprintf("update member_package_charges set %s = ? where id = %d", implode("=?, ", array_keys($upd)), $p_id));
				$stmt->bindParams(array_merge(array(str_repeat("s", count($upd))), array_values($upd)));
				if ($valid = $stmt->execute())
					$outer->init($this->getTemplate(__FUNCTION__."Success"));
			}
		}
		$this->logMessage(__FUNCTION__,sprintf("show [%s], form [%s]", $outer->show(), print_r($outer,true)),1);
		return $this->ajaxReturn(array("status"=>true,"html"=>$outer->show()));
	}

    /**
     * @return false|string
     * @throws phpmailerException
     */
    function deletePerPiece() {
		$outer = new Forms();
		$outer->init($this->getTemplate(__FUNCTION__));
		$flds = $outer->buildForm($this->getFields(__FUNCTION__));
		$p_id = array_key_exists("p_id",$_REQUEST) ? $_REQUEST["p_id"] : 0;
		$m_id = array_key_exists("m_id",$_REQUEST) ? $_REQUEST["m_id"] : 0;
		$this->execute(sprintf("delete from member_package_charges where id = %d and member_id = %d", $p_id, $m_id));
		return $this->ajaxReturn(array("status"=>true,"html"=>$outer->show()));
	}

    /**
     * @param int $p_id
     * @return array|false|mixed|string|string[]
     * @throws phpmailerException
     */
    function kmRates($p_id = 0) {
		$outer = new Forms();
		$outer->init($this->getTemplate(__FUNCTION__));
		$flds = $outer->buildForm($this->getFields(__FUNCTION__));
		$inner = new Forms();
		$inner->init($this->getTemplate(__FUNCTION__."Row"));
		$flds = $inner->buildForm($this->getFields(__FUNCTION__."Row"));
		$ajax = false;
		if ($p_id == 0 && array_key_exists("opt_id",$_REQUEST)) {
			$p_id = $_REQUEST["opt_id"];
			$ajax = true;
		}
		$recs = $this->fetchAllTest("select * from custom_product_km_ranges where member_product_option_id = %d order by km_max", $p_id);
		$rows = array();
		foreach($recs as $k=>$v) {
			$inner->addData($v);
			$rows[] = $inner->show();
		}
		$outer->setData("rows",implode("",$rows));
		$outer->setData("p_id",$p_id);
		if ($ajax)
			return $this->ajaxReturn(array("status"=>true,"html"=>$outer->show()));
		else
			return $outer->show();
	}

    /**
     * @return false|string
     * @throws phpmailerException
     */
    function kmRatesEdit() {
		$outer = new Forms();
		$outer->init($this->getTemplate(__FUNCTION__));
		$flds = $outer->buildForm($this->getFields(__FUNCTION__));
		$km_id = array_key_exists('km_id',$_REQUEST) ? $_REQUEST["km_id"] : 0;
		if ($rec = $this->fetchSingleTest("select * from custom_product_km_ranges where id = %d",$km_id)) {
			$outer->addData($rec);
		}
		if (array_key_exists(__FUNCTION__,$_REQUEST) && $_REQUEST[__FUNCTION__]==1) {
			$outer->addData($_REQUEST);
			$valid = $outer->validate();
			if ($valid) {
				$upd = array();
				foreach($flds as $k=>$v) {
					if (!(array_key_exists("database",$v) && $v["database"]==false)) {
						$upd[$k] = $outer->getData($k);
					}
				}
				if ($outer->getData("km_id")==0)
					$stmt = $this->prepare(sprintf("insert into custom_product_km_ranges(%s) values(%s?)", implode(", ",array_keys($upd)), str_repeat("?, ", count($upd)-1)));
				else {
				 	$stmt = $this->prepare(sprintf("update custom_product_km_ranges set %s=? where id = %d", implode("=?, ",array_keys($upd)),$outer->getData("km_id")));
				}
				$stmt->bindParams(array_merge(array(str_repeat("s",count($upd))),array_values($upd)));
				if ($stmt->execute())
					$outer->init($this->getTemplate(__FUNCTION__."Success"));
				if ($outer->getData("km_id")==0) $outer->setData("km_id",$this->insertId());
			}
		}
		return $this->ajaxReturn(array("status"=>true,"html"=>$outer->show()));
	}

    /**
     * @return void
     * @throws phpmailerException
     */
    function kmRateDelete() {
		$this->execute(sprintf("delete from custom_product_km_ranges where id = %d", array_key_exists("km_id",$_REQUEST) ? $_REQUEST["km_id"] : 0));
	}

    /**
     * @return false|string|void
     * @throws PHPExcel_Exception
     * @throws PHPExcel_Reader_Exception
     * @throws PHPExcel_Writer_Exception
     * @throws phpmailerException
     */
    function exportAddresses() {
		$outer = new Forms();
		$outer->init($this->getTemplate(__FUNCTION__));
		$o_id = array_key_exists("o_id", $_REQUEST) ? $_REQUEST["o_id"] : 0;
		$addresses = $this->fetchAllTest("select a.*, p.province_code, c.code as type from addresses a, provinces p, code_lookups c where a.ownertype='member' and a.ownerid = %d and p.id = a.province_id and c.id = a.addresstype order by c.code, a.company", $o_id);
		if (count($addresses) ==  0) {
			return $this->ajaxReturn(array("status"=>false,"message"=>"There are no addresses"));
		}
		$xls = new PHPExcel();
		$xls->getProperties()->setCreator("IngageDigital")->setTitle("Address Export");
		$xls->setActiveSheetIndex(0);
		$row_ctr = 0;
		$flds = explode(";",$outer->getHtml());
		$this->logMessage(__FUNCTION__,sprintf("flds [%s]", print_r($flds,true)),1);
		foreach($flds as $sk=>$sv) {
			$cell = sprintf("%s%d",chr(65+$sk),$row_ctr+1);
			$xls->setActiveSheetIndex(0)->setCellValue($cell,$sv);
		}
		$row_ctr += 1;
		foreach($addresses as $k=>$v) {
			$outer->addData($v);
			foreach($flds as $sk=>$sv) {
				$cell = sprintf("%s%d",chr(65+$sk),$row_ctr+1);
				$xls->setActiveSheetIndex(0)->setCellValue($cell,$outer->getData($sv));
			}
			$row_ctr += 1;
		}
		$objWriter = PHPExcel_IOFactory::createWriter($xls, 'Excel2007');
		$fn = sprintf("addresses_%d.xlsx", $o_id);
		$objWriter->save(sprintf("../files/%s",$fn));
		ob_end_clean();
		header('Content-type: application/application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
		header(sprintf("Content-Disposition: attachment;filename=%s",$fn));
		echo file_get_contents(sprintf("../files/%s",$fn));
		exit;
	}

    /**
     * @param $id
     * @return array|false|mixed|string|string[]
     * @throws phpmailerException
     */
    function buildContacts($id = null ) {
		$byAjax = false;
		if ($id == null) {
			$id = array_key_exists("member_id", $_REQUEST) ? $_REQUEST["member_id"] : 0;
			$byAjax = true;
		}
		$outer = new Forms();
		$outer->init($this->getTemplate("contacts"));
		$inner= new Forms();
		$inner->init($this->getTemplate("contactsRow"));
		$recs = array();
		$data = $this->fetchAllTest("select c.id as p_key, c.member_id, c.notes, a.* from contacts c, addresses a where c.deleted = 0 and c.member_id = %d and a.ownerid = c.id and ownertype='contact' order by id", $id);
		foreach($data as $k=>$v) {
			$inner->addData($v);
			$recs[] = $inner->show();
		}
		$outer->setData("contacts", implode("",$recs));
		$outer->setData("member_id",$id);
		if ($byAjax)
			return $this->ajaxReturn(array("status"=>true,"html"=>$outer->show()));
		else
			return $outer->show();
	}

    /**
     * @return false|string
     * @throws phpmailerException
     */
    function editContact() {
		$outer = new Forms();
		$outer->init($this->getTemplate(__FUNCTION__));
		$flds = $outer->buildForm($this->getFields(__FUNCTION__));
		$id = array_key_exists("c_id", $_REQUEST) ? $_REQUEST["c_id"] : 0;
		if ($data = $this->fetchSingleTest("select c.id as c_id, c.member_id, c.notes, a.* from contacts c, addresses a where c.id = %d and a.ownerid = c.id and ownertype='contact' order by id", $id))
			$outer->addData($data);
		if ($id < 0) {
			$outer->setData("member_id", -$id);
			$id = -$id;
		}
		$valid = false;
		if (array_key_exists(__FUNCTION__,$_POST)) {
			$outer->addData($_POST);
			if ($outer->validate()) {
				$upd = array();
				$c_rec = array();
				foreach($flds as $k=>$v) {
					if (!(array_key_exists("database",$v) && $v["database"]==false)) {
						if (array_key_exists("record",$v)) {
							$c_rec[$k] = $outer->getData($k);
						}
						else
							$upd[$k] = $outer->getData($k);
					}
				}
				if ($id == 0)
					$stmt = $this->prepare(sprintf("insert into contacts(%s) values(?%s)", implode(", ",array_keys($c_rec)), str_repeat(", ?",count($c_rec) - 1)));
				else
					$stmt = $this->prepare(sprintf("update contacts set %s=? where id = %d", implode("=?, ", array_keys($c_rec)), $outer->getData("c_id")));
				$stmt->bindParams(array_merge(array(str_repeat('s', count($c_rec))),$c_rec));
				$valid = $stmt->execute();
				if ($id == 0) {
					$id = $this->insertId();
					$outer->setData("c_id", $id);
				}
				if ($outer->getData("id") == 0) {
					$upd["ownertype"] = "contact";
					$upd["ownerid"] = $id;
					$stmt = $this->prepare(sprintf("insert into addresses(%s) values(%s?)", implode(", ", array_keys($upd)), str_repeat("?, ", count($upd)-1)));
				}
				else
					$stmt = $this->prepare(sprintf("update addresses set %s=? where id = %d", implode("=?, ", array_keys($upd)), $outer->getData("id")));
				$stmt->bindParams(array_merge(array(str_repeat('s', count($upd))),$upd));
				$valid &= $stmt->execute();
			}
		}
		if ($valid) {
			if ($id == 0) 
				$outer->addFormSuccess("Record added");
			else $outer->addFormSuccess("Record updated");
			$outer->init($this->getTemplate(__FUNCTION__."Success"));
		}
		return $this->ajaxReturn(array("status"=>true,"html"=>$outer->show()));
	}

    /**
     * @return false|string
     * @throws phpmailerException
     */
    function deleteContact() {
		$id = array_key_exists("c_id", $_REQUEST) ? $_REQUEST["c_id"] : 0;
		$this->execute(sprintf("update contacts set deleted = 1 where id = %d", $id));
		return $this->ajaxReturn(array("status"=>true,"html"=>""));
	}

    /**
     * @param int $p_id
     * @return array|false|mixed|string|string[]
     * @throws phpmailerException
     */
    function productByPC($p_id = 0) {
		$ajax = false;
		$outer = new Forms();
		if ($p_id == 0 && array_key_exists("opt_id",$_REQUEST)) {
			$p_id = $_REQUEST["opt_id"];
			$ajax = true;
		}
		$outer->init($this->getTemplate(__FUNCTION__));
		$flds = $outer->buildForm($this->getFields(__FUNCTION__));
		$inner = new Forms();
		$inner->init($this->getTemplate(__FUNCTION__."Row"));
		$flds = $inner->buildForm($this->getFields(__FUNCTION__."Row"));
		$recs = $this->fetchAllTest("select * from custom_product_by_pc where member_product_option_id = %d order by from_postal_code", $p_id);
		$rows = array();
		foreach($recs as $k=>$v) {
			$inner->addData($v);
			$rows[] = $inner->show();
		}
		$outer->setData("p_id",$p_id);
		$outer->setData("rows",implode("",$rows));
		if ($ajax)
			return $this->ajaxReturn(array("status"=>true,"html"=>$outer->show()));
		else
			return $outer->show();
	}

    /**
     * @return false|string
     * @throws phpmailerException
     */
    function productByPCEdit() {
		$outer = new Forms();
		$outer->init($this->getTemplate(__FUNCTION__));
		$flds = $outer->buildForm($this->getFields(__FUNCTION__));
		$pc_id = array_key_exists('pc_id',$_REQUEST) ? $_REQUEST["pc_id"] : 0;
		if ($rec = $this->fetchSingleTest("select * from custom_product_by_pc where id = %d",$pc_id)) {
			$outer->addData($rec);
		}
		if (array_key_exists(__FUNCTION__,$_REQUEST) && $_REQUEST[__FUNCTION__]==1) {
			$outer->addData($_REQUEST);
			$valid = $outer->validate();
			if ($valid) {
				$upd = array();
				foreach($flds as $k=>$v) {
					if (!(array_key_exists("database",$v) && $v["database"]==false)) {
						$upd[$k] = $outer->getData($k);
					}
				}
				$upd["from_postal_code"] = strtoupper(str_replace(" ","",$upd["from_postal_code"]));
				$upd["to_postal_code"] = strtoupper(str_replace(" ","",$upd["to_postal_code"]));
				if ($outer->getData("pc_id")==0)
					$stmt = $this->prepare(sprintf("insert into custom_product_by_pc(%s) values(%s?)", implode(", ",array_keys($upd)), str_repeat("?, ", count($upd)-1)));
				else {
				 	$stmt = $this->prepare(sprintf("update custom_product_by_pc set %s=? where id = %d", implode("=?, ",array_keys($upd)),$outer->getData("pc_id")));
				}
				$stmt->bindParams(array_merge(array(str_repeat("s",count($upd))),array_values($upd)));
				if ($stmt->execute())
					$outer->init($this->getTemplate(__FUNCTION__."Success"));
				if ($outer->getData("pc_id")==0) $outer->setData("pc_id",$this->insertId());
			}
		}
		return $this->ajaxReturn(array("status"=>true,"html"=>$outer->show()));
	}

    /**
     * @return void
     * @throws phpmailerException
     */
    function productByPCDelete() {
		$this->execute(sprintf("delete from custom_product_by_pc where id = %d", array_key_exists("pc_id",$_REQUEST) ? $_REQUEST["pc_id"] : 0));
	}

    /**
     * @return false|string
     * @throws phpmailerException
     */
    function regroup() {
		$outer = new Forms();
		$outer->init($this->getTemplate(__FUNCTION__));
		$flds = $outer->buildForm($this->getFields(__FUNCTION__));
		$m_id = array_key_exists('m_id',$_REQUEST) ? $_REQUEST["m_id"] : 0;
		if ($rec = $this->fetchSingleTest("select * from members where id = %d",$m_id)) {
			$outer->addData($rec);
			$outer->setData("m_id", $m_id);
		}
		if (array_key_exists(__FUNCTION__,$_REQUEST) && $_REQUEST[__FUNCTION__]==1) {
			$outer->addData($_REQUEST);
			if ($outer->validate()) {
				$this->execute(sprintf("update members_by_folder set folder_id = %d where folder_id = %d and member_id = %d", $outer->getData("to_group"), $outer->getData("from_group"), $outer->getData("m_id")));
			}
		}
		return $this->ajaxReturn(array("status"=>true,"html"=>$outer->show()));
	}

    /**
     * @param int $p_id
     * @param int $j_id
     * @return array|false|mixed|string|string[]
     * @throws phpmailerException
     */
    function kmRateOverride($p_id = 0, $j_id = 0) {
		$this->logMessage(__FUNCTION__, sprintf("p_id [%s] j_id [%s]", $p_id, $j_id),1);
		$outer = new Forms();
		$outer->init($this->getTemplate(__FUNCTION__));
		$flds = $outer->buildForm($this->getFields(__FUNCTION__));
		$inner = new Forms();
		$inner->init($this->getTemplate(__FUNCTION__."Row"));
		$flds = $inner->buildForm($this->getFields(__FUNCTION__."Row"));
		$ajax = false;
		//
		//	Using the p_id and group this belongs to, lookup the group km pricing
		//
		$recs = array();
		if ($p_id == 0 && array_key_exists("opt_id",$_REQUEST)) {
			$recs = $this->fetchAllTest('select kmo.*, km.km_max, km.price, km.out_of_zone, km.id as opt_id
			from custom_product_km_ranges km left join custom_product_km_range_overrides kmo on kmo.km_opt_id = km.id and kmo.junction_id = (select junction_id from custom_product_km_range_overrides where id = %1$d)
			where km.member_product_option_id = (select member_product_option_id from custom_product_km_ranges r1, custom_product_km_range_overrides r2 where r2.id = %1$d and r1.id = r2.km_opt_id)
			order by km_max', $_REQUEST["opt_id"]);
			//$_REQUEST["m"] = $recs[0]["junction_id"];
			$ajax = true;
		}
		else {
			if ($grp_prod = $this->fetchSingleTest("select grp.* from custom_member_product_options cpo, members_by_folder mbf, custom_member_product_options grp where cpo.id = %d and mbf.id = cpo.member_id and grp.product_id = cpo.product_id and grp.isgroup = 1 and grp.member_id = mbf.folder_id", $p_id))
				$recs = $this->fetchAllTest("select kmo.*, km.km_max, km.price, km.out_of_zone, km.id as opt_id, %d as j_id from custom_product_km_ranges km left join custom_product_km_range_overrides kmo on kmo.km_opt_id = km.id and kmo.junction_id = %d where km.member_product_option_id = %d order by km_max", $j_id, $j_id, $grp_prod["id"]);
		}
		$rows = array();
		foreach($recs as $k=>$v) {
			$v["adjusted_price"] = round($v["price"] * (1+$v["in_zone_override"]/100),2);
			$v["adjusted_out_of_zone"] = round($v["out_of_zone"] * (1+$v["out_of_zone_override"]/100),2);
			$inner->addData($v);
			$rows[] = $inner->show();
		}
		$outer->setData("rows",implode("",$rows));
		$outer->setData("p_id",$p_id);
		if ($ajax)
			return $this->ajaxReturn(array("status"=>true,"html"=>$outer->show()));
		else
			return $outer->show();
	}

    /**
     * @return false|string
     * @throws phpmailerException
     */
    function editKMOverride() {
		$outer = new Forms();
		$outer->init($this->getTemplate(__FUNCTION__));
		$flds = $outer->buildForm($this->getFields(__FUNCTION__));
		$km_id = array_key_exists('km_id',$_REQUEST) ? $_REQUEST["km_id"] : 0;
		if ($km_id == 0 && array_key_exists('id',$_REQUEST)) $km_id = $_REQUEST["id"];
		$this->logMessage(__FUNCTION__,sprintf("km_id == 0 [%s] key_exists [%s] combo [%s]", $km_id == 0, array_key_exists('id',$_REQUEST), $km_id == 0 && array_key_exists('id',$_REQUEST) ),1);
		$opt_id = array_key_exists('opt_id',$_REQUEST) ? $_REQUEST["opt_id"] : 0;
		if ($opt_id == 0 && array_key_exists('km_opt_id',$_REQUEST)) $opt_id = $_REQUEST["km_opt_id"];
		$j_id = array_key_exists('j_id',$_REQUEST) ? $_REQUEST["j_id"] : 0;
		if ($j_id == 0 && array_key_exists('junction_id',$_REQUEST)) $j_id = $_REQUEST["junction_id"];
		if ($rec = $this->fetchSingleTest("select * from custom_product_km_range_overrides where id = %d and junction_id = %d",$km_id, $j_id)) {
			$outer->addData($rec);
		}
		$opt = $this->fetchSingleTest("select * from custom_product_km_ranges where id = %d", $opt_id);
		$outer->setData("km_id", $km_id);
		$outer->setData("km_opt_id", $opt_id);
		$outer->setData("junction_id", $j_id);
		$this->logMessage(__FUNCTION__,sprintf("km_id [%s] km_opt_id [%s] junction_id [%s]", print_r($km_id,true), print_r($opt_id,true), print_r($j_id,true)),1);
		if (array_key_exists(__FUNCTION__,$_REQUEST) && $_REQUEST[__FUNCTION__]==1) {
			$outer->addData($_REQUEST);
			$valid = $outer->validate();
			if ($valid && !array_key_exists("temp", $_POST)) {
				$upd = array();
				foreach($flds as $k=>$v) {
					if (!(array_key_exists("database",$v) && $v["database"]==false)) {
						$upd[$k] = $outer->getData($k);
					}
				}
				if ($outer->getData("km_id")==0)
					$stmt = $this->prepare(sprintf("insert into custom_product_km_range_overrides(%s) values(%s?)", implode(", ",array_keys($upd)), str_repeat("?, ", count($upd)-1)));
				else {
				 	$stmt = $this->prepare(sprintf("update custom_product_km_range_overrides set %s=? where id = %d", implode("=?, ",array_keys($upd)),$outer->getData("km_id")));
				}
				$stmt->bindParams(array_merge(array(str_repeat("s",count($upd))),array_values($upd)));
				if ($stmt->execute())
					$outer->init($this->getTemplate(__FUNCTION__."Success"));
				if ($outer->getData("km_id")==0) $outer->setData("id",$this->insertId());
			}
		}
		$outer->setData("in_zone_value",round($opt["price"] * (1+$outer->getData("in_zone_override")/100),2));
		$outer->setData("out_of_zone_value",round($opt["out_of_zone"] * (1+$outer->getData("out_of_zone_override")/100),2));
		$outer->setData("opt", $opt);
		return $this->ajaxReturn(array("status"=>true,"html"=>$outer->show()));
	}

    /**
     * @return false|string
     * @throws phpmailerException
     */
    function deleteKMOverride() {
		$id = array_key_exists("opt_id",$_REQUEST) ? $_REQUEST["opt_id"] : 0;
		$html = "<div>Oops</div>";
		if ($rec = $this->fetchSingleTest("select * from custom_product_km_range_overrides where id = %d", $id)) {
			if ($grp = $this->fetchSingleTest("select cpo.* from custom_member_product_options cpo, custom_product_km_ranges km, custom_product_km_range_overrides kmo where kmo.id = %d and km.id = kmo.km_opt_id and cpo.id = km.member_product_option_id", $id)) {
				$mbr = $this->fetchSingleTest("select * from custom_member_product_options cpo where member_id = %d and product_id = %d and isgroup = 0", $rec["junction_id"], $grp["product_id"]);
				$this->execute(sprintf("delete from custom_product_km_range_overrides where id = %d", $id));
				$html = $this->kmRateOverride( $mbr["id"], $mbr["member_id"]);
			}
		}
		return $this->ajaxReturn(array("status"=>true,"html"=>$html));
	}

    /**
     * @return false|string
     * @throws phpmailerException
     */
    function initKMOverrides() {
		$outer = new Forms();
		$outer->init($this->getTemplate(__FUNCTION__));
		$flds = $outer->buildForm($this->getFields(__FUNCTION__));
		$inner = new Forms();
		$inner->init($this->getTemplate(__FUNCTION__."Row"));
		$flds = $inner->buildForm($this->getFields(__FUNCTION__."Row"));
		$j_id = array_key_exists("j_id", $_REQUEST) ? $_REQUEST["j_id"] : 0;
		$outer->setData("j_id",$j_id);
		$j = $this->fetchSingleTest("select * from members_by_folder where id = %d", $j_id);
		if (array_key_exists(__FUNCTION__,$_POST) && array_key_exists("process",$_POST)) {
			$outer->addData($_POST);
			$inner->addData($_POST);
			if ($outer->validate()) {
				$ins = array("junction_id"=>0,"km_opt_id"=>0,"in_zone_override"=>0,"out_of_zone_override"=>0);
				$p_ins = array("member_id"=>$j_id,"product_id"=>0,"isgroup"=>0);
				$this->beginTransaction();
				$valid = true;
				$stmt = $this->prepare(sprintf("insert into custom_product_km_range_overrides(%s) values(?%s)", implode(", ",array_keys($ins)), str_repeat(", ?", count($ins) - 1)));
				$prd = $this->prepare(sprintf("insert into custom_member_product_options(%s) values(?,?,?)", implode(", ", array_keys($p_ins))));
				foreach($_POST["process"] as $k=>$v) {
					$this->logMessage(__FUNCTION__,sprintf("k [%s] v [%s]", print_r($k,true), print_r($v,true)),1);
					if (abs($outer->getData("in_zone")[$k]) != 0 || abs($outer->getData("out_of_zone")[$k]) != 0) {
						$p_ins["product_id"] = $this->fetchScalarTest("select product_id from custom_member_product_options where id = %d", $k);
						//if (!$this->fetchSingleTest("select * from custom_member_product_options where member_id = %d and isgroup = 0 and product_id = %d", $j_id, $p_ins["product_id"])) {
						//	$prd->bindParams(array_merge(array("sss"), array_values($p_ins)));
						//	$valid &= $prd->execute();
						//}
						$outer->addFormSuccess(sprintf("Processed %s", $this->fetchScalarTest("select name from product p, custom_member_product_options cpo where cpo.id = %d and p.id = cpo.product_id", $k)));
						$km = $this->fetchAllTest("select km.* from custom_product_km_ranges km where member_product_option_id = %d", $k);
						$this->logMessage(__FUNCTION__,sprintf("km [%s]", print_r($km,true)),1);
						foreach($km as $sk=>$sv) {
							$ins["junction_id"] = $j_id;
							$ins["km_opt_id"] = $sv["id"];
							$ins["in_zone_override"] = $outer->getData("in_zone")[$k];
							$ins["out_of_zone_override"] = $outer->getData("out_of_zone")[$k];
							$this->logMessage(__FUNCTION__,sprintf("ins [%s]", print_r($ins,true)),1);
							$stmt->bindParams(array_merge(array("ssss"),array_values($ins)));
							$valid &= $stmt->execute();
						}
					}
				}
				if ($valid)
					$this->commitTransaction();
				else
					$this->rollbackTransaction();
			}
		}
		$recs = $this->fetchAllTest('select p.name, cpo.* from product p, custom_member_product_options cpo where cpo.member_id = %d and isgroup = 1 and p.id = cpo.product_id and is_fedex = 0 and cpo.product_id not in (
			SELECT cpo.product_id FROM `custom_product_km_range_overrides` kmo, custom_product_km_ranges km, custom_member_product_options cpo where kmo.junction_id = %d and km.id = kmo.km_opt_id and cpo.id = km.member_product_option_id
		) order by p.name', $j["folder_id"], $j_id);
		$rows = array();
		foreach($recs as $k=>$v) {
			$inner->addData($v);
			$rows[] = $inner->show();
		}
		$outer->setData("products",implode("",$rows));
		return $this->ajaxReturn(array("status"=>true,"html"=>$outer->show()));
	}

    /**
     * @param int $j_id
     * @return false|string
     * @throws phpmailerException
     */
    function groupKMOverrides($j_id = 0) {
		$outer = new Forms();
		$outer->init($this->getTemplate(__FUNCTION__));
		if ($j_id == 0)
			$j_id = array_key_exists("j_id", $_REQUEST) ? $_REQUEST["j_id"] : 0;
		$flds = $this->getFields(__FUNCTION__);
		$flds["opt_id"]["sql"] = sprintf("select distinct kr.member_product_option_id, p.name from custom_product_km_range_overrides ovr, custom_product_km_ranges kr, custom_member_product_options cpo, product p 
		where ovr.junction_id = %d and kr.id = ovr.km_opt_id and cpo.id = kr.member_product_option_id and p.id = cpo.product_id and cpo.isgroup = 1
		order by p.name", $j_id);
		$flds = $outer->buildForm($flds);
		$inner = new Forms();
		$inner->init($this->getTemplate(__FUNCTION__."Row"));
		$flds = $inner->buildForm($this->getFields(__FUNCTION__."Row"));
		$outer->setData("j_id",$j_id);
		if (array_key_exists(__FUNCTION__,$_REQUEST)) {
			$this->logMessage(__FUNCTION__,sprintf("added request"),1);
			$outer->addData($_REQUEST);
		}
		if (($p_id = $outer->getData("opt_id")) > 0) {
			$recs = $this->fetchAllTest("select ovr.*, kr.km_max, kr.price, kr.out_of_zone from custom_product_km_ranges kr left join custom_product_km_range_overrides ovr on ovr.km_opt_id = kr.id where kr.member_product_option_id = %d and ovr.junction_id = %d order by km_max", $p_id, $j_id);
			$rows = array();
			foreach($recs as $k=>$v) {
				$v["override_price"] = round($v["price"] * (1+$v["in_zone_override"]/100),2);
				$v["override_out_of_zone"] = round($v["out_of_zone"] * (1+$v["out_of_zone_override"]/100),2);
				$inner->addData($v);
				$rows[] = $inner->show();
			}
			$outer->setData("overrides", implode("",$rows));
		}
		return $this->ajaxReturn(array("status"=>true,"html"=>$outer->show()));
	}

    /**
     * @return false|string
     * @throws phpmailerException
     */
    function groupKMDeleteOverride() {
		$id = array_key_exists("opt_id",$_REQUEST) ? $_REQUEST["opt_id"] : 0;
		$html = "<div>Oops</div>";
		$this->beginTransaction();
		if ($rec = $this->fetchSingleTest("select * from custom_product_km_range_overrides where id = %d", $id)) {
			if ($grp = $this->fetchSingleTest("select cpo.* from custom_member_product_options cpo, custom_product_km_ranges km, custom_product_km_range_overrides kmo where kmo.id = %d and km.id = kmo.km_opt_id and cpo.id = km.member_product_option_id", $id)) {
				//$mbr = $this->fetchSingleTest("select * from custom_member_product_options cpo where member_id = %d and product_id = %d and isgroup = 1", $rec["junction_id"], $grp["product_id"]);
				$this->execute(sprintf("delete from custom_product_km_range_overrides where id = %d", $id));
				$html = $this->groupKMOverrides($rec["junction_id"]);
			}
		}
		$this->rollbackTransaction();
		return $html;
	}

    /**
     * @return array|false|mixed|string|string[]|void
     * @throws phpmailerException
     */
    public function search() {
		$outer = new Forms();
		$outer->init($this->getTemplate(__FUNCTION__));
		$j_id = array_key_exists("j_id", $_REQUEST) ? $_REQUEST["j_id"] : 0;
		$flds = $this->getFields(__FUNCTION__);
		$flds = $outer->buildForm($flds);
		$inner = new Forms();
		$inner->init($this->getTemplate(__FUNCTION__."Row"));
		$flds = $inner->buildForm($this->getFields(__FUNCTION__."Row"));
		$opts = array("opt_search"=>"!=","opt_search_value"=>"0.0");
		if (array_key_exists(__FUNCTION__,$_POST) && $_POST[__FUNCTION__] == 1) {
			$outer->addData($_POST);
			$outer->validate();
			foreach($_POST as $k=>$v) {
				switch($k) {
					case "selector":
					case "folder":
					case "product":
						break;
					case "opt_search":
					case "opt_search_value":
						if (strlen($v) > 0) $opts[$k] = $v;
						break;
					default:
				}
			}
			if ($outer->getData("selectorType") == "F") {
				$this->logMessage(__FUNCTION__, sprintf("*** in group code"),1);
			}
		}
		if ($this->isAjax()) {
			return $this->ajaxReturn(array("status"=>true,"html"=>$outer->show()));
		}
		else return $this->show($outer->show());
	}
}

?>
