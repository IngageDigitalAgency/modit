<?php

class stores extends Backend {

	private $m_tree = 'store_folders';
	private $m_content = 'stores';
	private $m_junction = 'stores_by_folder';
	private $m_perrow = 5;

    /**
     * @throws phpmailerException
     */
	public function __construct() {
		$this->M_DIR = 'backend/modules/stores/';
		$this->setTemplates(
			array(
				'deleteItem'=>$this->M_DIR.'forms/deleteItem.html',
				'deleteItemResult'=>$this->M_DIR.'forms/deleteItemResult.html',
				'main'=>$this->M_DIR.'stores.html',
				'form'=>$this->M_DIR.'forms/form.html',
				'showContentTree'=>$this->M_DIR.'forms/contenttree.html',
				'storeInfo'=>$this->M_DIR.'forms/storeInfo.html',
				'folderProperties'=>$this->M_DIR.'forms/folder.html',
				'showFolderContent'=>$this->M_DIR.'forms/folderContent.html',
				'folderInfo'=>$this->M_DIR.'forms/folderInfo.html',
				'storeList'=>$this->M_DIR.'forms/storeList.html',
				'couponList'=>$this->M_DIR.'forms/couponsList.html',
				'infoProductList'=>$this->M_DIR.'forms/productsList.html',
				'infoEventList'=>$this->M_DIR.'forms/eventsList.html',
				'showSearchForm'=>$this->M_DIR.'forms/searchForm.html',
				'addContent'=>$this->M_DIR.'forms/addContent.html',
				'addContentSuccess'=>$this->M_DIR.'forms/addContentSuccess.html',
				'editAddress'=>$this->M_DIR.'forms/editAddress.html',
				'editAddressSuccess'=>$this->M_DIR.'forms/editAddressSuccess.html',
				'addressForm'=>$this->M_DIR.'forms/addressForm.html',
				'addressList'=>$this->M_DIR.'forms/addressList.html',
				'couponByStoreList'=>$this->M_DIR.'forms/couponList.html',
				'eventList'=>$this->M_DIR.'forms/eventList.html',
				'couponFolderList'=>$this->M_DIR.'forms/couponFoldersList.html',
				'infoEventFolderList'=>$this->M_DIR.'forms/eventFoldersList.html',
				'productFolderList'=>$this->M_DIR.'forms/productFoldersList.html',
				'productList'=>$this->M_DIR.'forms/productList.html',
				'header'=>$this->M_DIR.'forms/heading.html',
				'editItem'=>$this->M_DIR.'forms/editItem.html',
				'addFolder'=>$this->M_DIR.'forms/addFolder.html',
				'addItem'=>$this->M_DIR.'forms/addItem.html',
				'storeByFolder'=>$this->M_DIR.'forms/storesList.html'
			)
		);
		$this->setFields(array(
			'deleteItem'=>array(
				'options'=>array('name'=>'deleteItem','database'=>false),
				'j_id'=>array('type'=>'tag'),
				'deleteItem'=>array('type'=>'hidden','value'=>1),
				'cancel'=>array('type'=>'radiobutton','name'=>'action','value'=>'cancel','checked'=>'checked'),
				'one'=>array('type'=>'radiobutton','name'=>'action','value'=>'one'),
				'all'=>array('type'=>'radiobutton','name'=>'action','value'=>'all')
			),
			'header'=>array(
			),
			'addressForm'=>array(),
			'addressList'=>array(
				'line1'=>array('type'=>'tag','reformatting'=>true),
				'line2'=>array('type'=>'tag','reformatting'=>true),
				'city'=>array('type'=>'tag','reformatting'=>true),
			),
			'infoEventFolderList'=>array(
				'options'=>array(),
				'enabled'=>array('type'=>'booleanIcon'),
				'title'=>array('type'=>'tag')
			),
			'couponFolderList'=>array(
				'options'=>array(),
				'enabled'=>array('type'=>'booleanIcon'),
				'title'=>array('type'=>'tag')
			),
			'productFolderList'=>array(
				'options'=>array(),
				'enabled'=>array('type'=>'booleanIcon'),
				'title'=>array('type'=>'tag')
			),
			'eventList'=>array(
				'destEvents'=>array('type'=>'select','id'=>'destEvents','multiple'=>'multiple'),
			),
			'couponByStoreList'=>array(
				'destCoupons'=>array('type'=>'select','id'=>'destCoupons','multiple'=>'multiple'),
			),
			'editAddress'=>array(
				'options'=>array('method'=>'post','action'=>'/modit/ajax/editAddress/stores'),
				'editAddress'=>array('type'=>'hidden','value'=>1,'database'=>false),
				'addresstype'=>array('type'=>'select','required'=>true,'sql'=>'select id, value from code_lookups where type = "storeAddressTypes"','prettyName'=>'Address Type'),
				'addressname'=>array('type'=>'input'),
				'ownertype'=>array('type'=>'hidden','value'=>'store'),
				'ownerid'=>array('type'=>'hidden','id'=>'ownerid'),
				'addressname'=>array('type'=>'input','required'=>false,'prettyName'=>'Name'),
				'line1'=>array('type'=>'input','required'=>true,'prettyName'=>'Address Line 1'),
				'line2'=>array('type'=>'input','required'=>false),
				'city'=>array('type'=>'input','required'=>true,'prettyName'=>'City'),
				'country_id'=>array('type'=>'countryselect','required'=>true,'id'=>'country_id','prettyName'=>'Country'),
				'province_id'=>array('type'=>'provinceselect','required'=>true,'id'=>'province_id','prettyName'=>'Province'),
				'postalcode'=>array('type'=>'input','required'=>false,'prettyName'=>'Postal Code','validation'=>'postalcode'),
				'phone1'=>array('type'=>'input'),
				'phone2'=>array('type'=>'input'),
				'fax'=>array('type'=>'input'),
				'email'=>array('type'=>'input','validation'=>'email'),
				'firstname'=>array('type'=>'input'),
				'lastname'=>array('type'=>'input'),
				'addresses'=>array('type'=>'select','database'=>false,'id'=>'addressSelector'),
				'save'=>array('type'=>'submitbutton','database'=>false,'value'=>'Save Address'),
				'geocode'=>array('type'=>'checkbox','value'=>1,'database'=>false)
			),
			'storeList'=>array(
				'options'=>array(),
				'enabled'=>array('type'=>'checkbox','disabled'=>'disabled'),
				'featured'=>array('type'=>'checkbox','disabled'=>'disabled'),
				'deleted'=>array('type'=>'checkbox','disabled'=>'disabled'),
				'name'=>array('type'=>'tag','reformat'=>true)
			),
			'couponList'=>array(
				'options'=>array(),
				'enabled'=>array('type'=>'booleanIcon'),
				'published'=>array('type'=>'booleanIcon'),
				'name'=>array('type'=>'tag'),
				'expires'=>array('type'=>'datestamp')
			),
			'infoProductList'=>array(
				'options'=>array(),
				'enabled'=>array('type'=>'booleanIcon'),
				'published'=>array('type'=>'booleanIcon'),
				'name'=>array('type'=>'tag'),
				'expires'=>array('type'=>'datestamp')
			),
			'infoEventList'=>array(
				'options'=>array(),
				'enabled'=>array('type'=>'booleanIcon'),
				'published'=>array('type'=>'booleanIcon'),
				'name'=>array('type'=>'tag'),
				'start_date'=>array('type'=>'datestamp'),
				'end_date'=>array('type'=>'datestamp')
			),
			'addContent'=>array(
				'options'=>array('method'=>'post','action'=>'/modit/ajax/addContent/stores'),
				'created'=>array('type'=>'datestamp','database'=>false,'reformatting'=>true,'mask'=>'d-M-Y H:i:s'),
				'expires'=>array('type'=>'datepicker','id'=>'addExpires','prettyName'=>'Expires'),
				'id'=>array('type'=>'tag','database'=>false),
				'name'=>array('type'=>'input','required'=>true,'prettyName'=>'Name'),
				'subtitle'=>array('type'=>'input','required'=>false,'prettyName'=>'Sub-Title'),
				'teaser'=>array('type'=>'textarea','required'=>false,'class'=>'mceSimple'),
				'description'=>array('type'=>'textarea','required'=>false,'id'=>'storeBody','class'=>'mceAdvanced','prettyName'=>'Description'),
				'hours'=>array('type'=>'textarea','required'=>false,'id'=>'storeHours','class'=>'mceSimple','prettyName'=>'Store Hours'),
				'enabled'=>array('type'=>'checkbox','required'=>false,'value'=>1,'checked'=>'checked'),
				'featured'=>array('type'=>'checkbox','required'=>false,'value'=>1),
				'published'=>array('type'=>'checkbox','value'=>1,'checked'=>'checked'),
				'deleted'=>array('type'=>'checkbox','value'=>1),
				'longitude'=>array('type'=>'input','required'=>false,'validation'=>'number'),
				'latitude'=>array('type'=>'input','required'=>false,'validation'=>'number'),
				'website'=>array('type'=>'input','required'=>false),
				'image1'=>array('type'=>'tag','required'=>false,'prettyName'=>'Image 1'),
				'image2'=>array('type'=>'tag'),
				'image3'=>array('type'=>'tag'),
				'image4'=>array('type'=>'tag'),
				'mapmarker'=>array('type'=>'tag'),
				'imagesel_a'=>array('type'=>'image','unknown'=>true,'database'=>false,'id'=>'imagesel_a'),
				'imagesel_b'=>array('type'=>'image','unknown'=>true,'database'=>false,'id'=>'imagesel_b'),
				'imagesel_c'=>array('type'=>'image','unknown'=>true,'database'=>false,'id'=>'imagesel_c'),
				'imagesel_d'=>array('type'=>'image','unknown'=>true,'database'=>false,'id'=>'imagesel_d'),
				'imagesel_e'=>array('type'=>'image','unknown'=>true,'database'=>false,'id'=>'imagesel_e'),
				'submit'=>array('type'=>'submitbutton','database'=>false,'value'=>'Save'),
				'destFolders'=>array('name'=>'destFolders','type'=>'select','multiple'=>'multiple','required'=>true,'id'=>'destFolders','database'=>false,'options'=>$this->nodeSelect(0, 'store_folders', 2, false, false),'reformatting'=>false,'prettyName'=>'Member of'),
				'addresses'=>array('type'=>'select','database'=>false,'id'=>'addressSelector'),

				'couponFolders'=>array('type'=>'select','database'=>false,'id'=>'couponFolderSelector','options'=>$this->nodeSelect(0, 'coupon_folders', 2, false, false),'reformatting'=>false),
				'eventFolders'=>array('type'=>'select','database'=>false,'id'=>'eventFolderSelector','options'=>$this->nodeSelect(0, 'members_folders', 2, false, false),'reformatting'=>false),
				'productFolders'=>array('type'=>'select','database'=>false,'id'=>'productFolderSelector','options'=>$this->nodeSelect(0, 'product_folders', 2, false, false),'reformatting'=>false),
				'amenities'=>array('type'=>'select','multiple'=>true,'required'=>false,'idlookup'=>'amenities','database'=>false),

				'destCoupons'=>array('type'=>'select','database'=>false,'id'=>'destCoupons','reformatting'=>false,'multiple'=>'multiple'),
				'destCouponFolders'=>array('type'=>'select','database'=>false,'id'=>'destCouponFolders','reformatting'=>false,'multiple'=>'multiple'),
				'destProductFolders'=>array('name'=>'destProductFolders','type'=>'select','multiple'=>'multiple','required'=>false,'id'=>'destProductFolders','database'=>false,'reformatting'=>false),
				'relatedDestProducts'=>array('name'=>'relatedDestProducts','type'=>'select','multiple'=>'multiple','required'=>false,'id'=>'relatedDestProducts','database'=>false),
				'destEventFolders'=>array('name'=>'destEventFolders','type'=>'select','multiple'=>'multiple','required'=>false,'id'=>'destEventFolders','database'=>false,'reformatting'=>false),
				'destGalleryFolders'=>array('name'=>'destGalleryFolders','type'=>'select','multiple'=>'multiple','required'=>false,'id'=>'destGalleryFolders','database'=>false,'reformatting'=>false),
				'destEvents'=>array('name'=>'destEvents','type'=>'select','multiple'=>'multiple','required'=>false,'id'=>'destEvents','database'=>false),
				'addContent'=>array('type'=>'hidden','value'=>1,'database'=>false),
				'destSearch'=>array('name'=>'destSearch','type'=>'select','multiple'=>'multiple','required'=>false,'id'=>'destSearch','database'=>false,'options'=>$this->nodeSelect(0, 'search_groups', 2, false, false),'reformatting'=>false,'prettyName'=>'Search Related'),
				'meta_description'=>array('name'=>'meta_description','type'=>'textarea','required'=>false),
				'social_description'=>array('name'=>'social_description','type'=>'textarea','required'=>false),
				'meta_keywords'=>array('name'=>'meta_keywords','type'=>'textarea','required'=>false),
				'browser_title'=>array('name'=>'browser_title','type'=>'textfield','required'=>false),
				'adword_conversion'=>array('type'=>'textarea','required'=>false)
			),
			'addContentSuccess'=>array(),
			'showSearchForm'=>array(
				'options'=>array('action'=>'showSearchForm','name'=>'searchForm','id'=>'search_form'),
				'opt_created'=>array('type'=>'select','name'=>'opt_created','lookup'=>'search_options','required'=>false),
				'opt_expires'=>array('type'=>'select','name'=>'opt_expires','lookup'=>'search_options','required'=>false),
				'opt_name'=>array('type'=>'select','name'=>'opt_name','lookup'=>'search_string','required'=>false),
				'name'=>array('type'=>'input','required'=>false),
				'created'=>array('type'=>'datepicker','required'=>false,'prettyName'=>'Created'),
				'expires'=>array('type'=>'datepicker','required'=>false,'prettyName'=>'Expires'),
				'published'=>array('type'=>'select','required'=>false,'lookup'=>'boolean'),
				'deleted'=>array('type'=>'select','required'=>false,'lookup'=>'boolean'),
				'enabled'=>array('type'=>'select','required'=>false,'lookup'=>'boolean'),
				'featured'=>array('type'=>'select','required'=>false,'lookup'=>'boolean'),
				'showSearchForm'=>array('type'=>'hidden','value'=>1),
				'pagenum'=>array('type'=>'hidden','value'=>1),
				'sortby'=>array('type'=>'hidden','value'=>'created'),
				'sortorder'=>array('type'=>'hidden','value'=>'desc'),
				'folder'=>array('type'=>'select','optionslist' => array('table'=>$this->m_tree,'root'=>0,'indent'=>2,'inclusive'=>false),'database'=>false),
				'quicksearch'=>array('type'=>'input','name'=>'quicksearch','required'=>false),
				'opt_quicksearch'=>array('type'=>'hidden','value'=>'like'),
				'pager'=>array('type'=>'select','required'=>true,'value'=>$this->m_perrow,'lookup'=>'paging','id'=>'pager'),
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
					'action'=>'/modit/stores/showPageProperties',
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
				'id'=>array('type'=>'hidden', 'database'=>false,'id'=>'p_id'),
				'image'=>array('type'=>'tag'),
				'rollover_image'=>array('type'=>'tag'),
				'imagesel_a'=>array('type'=>'image','unknown'=>true,'database'=>false,'id'=>'imagesel_a'),
				'imagesel_b'=>array('type'=>'image','unknown'=>true,'database'=>false,'id'=>'imagesel_b'),
				'template_id'=>array('type'=>'select','required'=>false,'sql'=>'select template_id,title from templates group by title order by title'),
				'couponFolders'=>array('type'=>'select','database'=>false,'id'=>'couponFolderSelector','options'=>$this->nodeSelect(0, 'coupon_folders', 2, false, false),'reformatting'=>false),
				'productFolders'=>array('type'=>'select','database'=>false,'id'=>'productFolderSelector','options'=>$this->nodeSelect(0, 'product_folders', 2, false, false),'reformatting'=>false),
				'eventFolders'=>array('type'=>'select','database'=>false,'id'=>'eventFolderSelector','options'=>$this->nodeSelect(0, 'members_folders', 2, false, false),'reformatting'=>false),
				'destCoupons'=>array('type'=>'select','database'=>false,'id'=>'destCoupons','reformatting'=>false,'multiple'=>'multiple'),
				'destCouponFolders'=>array('type'=>'select','database'=>false,'id'=>'destCouponFolderSelector','reformatting'=>false,'multiple'=>'multiple'),
				'relatedDestProducts'=>array('name'=>'relatedDestProducts','type'=>'select','multiple'=>'multiple','required'=>false,'id'=>'relatedDestProducts','database'=>false),
				'destProductFolders'=>array('name'=>'destProductFolders','type'=>'select','multiple'=>'multiple','required'=>false,'id'=>'destProductFolders','database'=>false,'reformatting'=>false),
				'destEvents'=>array('name'=>'destEvents','type'=>'select','multiple'=>'multiple','required'=>false,'id'=>'destEvents','database'=>false),
				'destEventFolders'=>array('name'=>'destEventFolders','type'=>'select','multiple'=>'multiple','required'=>false,'id'=>'destEventFolders','database'=>false,'reformatting'=>false),
				'destGalleryFolders'=>array('name'=>'destGalleryFolders','type'=>'select','multiple'=>'multiple','required'=>false,'id'=>'destGalleryFolders','database'=>false,'reformatting'=>false),
				'meta_description'=>array('name'=>'meta_description','type'=>'textarea','required'=>false),
				'social_description'=>array('name'=>'social_description','type'=>'textarea','required'=>false),
				'meta_keywords'=>array('name'=>'meta_keywords','type'=>'textarea','required'=>false),
				'browser_title'=>array('name'=>'browser_title','type'=>'textfield','required'=>false),
				'submit'=>array('type'=>'submitbutton','value'=>'Save','database'=>false)
			),
			'showContentTree' => array(),
			'storeInfo' => array(),
			'showstoreContent' => array(),
			'folderInfo' => array(
				'title'=>array('type'=>'tag'),
				'alternate_title'=>array('type'=>'tag'),
				'notes'=>array('type'=>'tag','reformatting'=>false),
				'description'=>array('type'=>'tag','reformatting'=>false),
				'image' => array('type'=>'image','unknown'=>true),
				'rollover_image'=>array('type'=>'image','unknown'=>true)
			),
			'storeList' => array(
				'id'=>array('type'=>'tag'),
				'name'=>array('type'=>'tag'),
				'created'=>array('type'=>'datestamp','mask'=>'d-M-Y h:m:i'),
				'published'=>array('type'=>'booleanIcon'),
				'deleted'=>array('type'=>'booleanIcon'),
				'enabled'=>array('type'=>'booleanIcon'),
				'featured'=>array('type'=>'booleanIcon'),
				'expires'=>array('type'=>'datestamp','mask'=>'d-M-Y','suppressNull'=>true)
			),
			'storeByFolder'=>array(
				'destStores'=>array('type'=>'select','id'=>'destStores','multiple'=>'multiple')
			)
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
		$frmFields = $form->buildForm($this->getFields('form'));
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
		if (!(array_key_exists('id',$_REQUEST) && $data = $this->fetchSingle(sprintf('select * from %s where id = %d',$this->m_tree,$_REQUEST['id']))))
			$data = array('enabled'=>1,'id'=>0,'p_id'=>0,'image'=>'','rollover_image'=>'');
		else {
			//
			//	get the parent node as well
			//
			$data['p_id'] = 0;
			if ($data['level'] > 1) {
				if ($p = $this->fetchSingle(sprintf('select * from %s where level = %d and left_id < %d and right_id > %d', $this->m_tree, $data['level'] - 1, $data['left_id'], $data['right_id'])))
					$data['p_id'] = $p['id'];
			}
		}
		$form = new Forms();
		$form->init($this->getTemplate('folderProperties'),array('name'=>'folderProperties'));
		$frmFlds = $this->getFields('folderProperties');

		//
		//	load coupon folders attached to this store
		//
		$data['destCoupons'] = $this->loadRelations('destCoupons',$this,"altCouponFormat",$data['id'],'storefolder','coupon','coupons','and deleted = 0 and enabled = 1 and published = 1',true);
		$frmFlds['destCoupons']['options'] = $this->fetchOptions(sprintf('select id as code,code as value from coupons where deleted = 0 and enabled = 1 and published = 1 and id in (%s) order by code',implode(',',array_merge(array(0),$data['destCoupons']))));

		//
		//	load coupons attached to this store
		//
		$frmFlds['destCouponFolders']['options'] = $this->nodeSelect(0,'coupon_folders',2,false);
		$data['destCouponFolders'] = $this->loadRelations('destCouponFolders',$this,"altCouponFormat",$data['id'],'storefolder','couponfolder','coupon_folders','',true);
		
		//
		//	load product folders attached to this store
		//
		$data['relatedDestProducts'] = $this->loadRelations('relatedDestProducts',$this,"altCouponFormat",$data['id'],'storefolder','product','product','and deleted = 0 and enabled = 1 and published = 1',true);
		$frmFlds['relatedDestProducts']['options'] = $this->fetchOptions(sprintf('select id as code,concat(code," - ",name) as value from product where deleted = 0 and enabled = 1 and published = 1 and id in (%s) order by code',implode(',',array_merge(array(0),$data['relatedDestProducts']))));

		//
		//	load individual products attached to this store
		//
		$frmFlds['destProductFolders']['options'] = $this->nodeSelect(0,'product_folders',2,false);
		$data['destProductFolders'] = $this->loadRelations('destProductFolders',$this,"altCouponFormat",$data['id'],'storefolder','productfolder','product_folders','',true);

		//
		//	load event folders attached to this store
		//
		$data['destEvents'] = $this->loadRelations('destEvents',$this,"altEventFormat",$data['id'],'storefolder','event','events','and deleted = 0 and enabled = 1 and published = 1',true);
		$frmFlds['destEvents']['options'] = $this->fetchOptions(sprintf('select id as code,name as value from events where id in (select event_id from event_dates where event_date >= curdate()) and deleted = 0 and enabled = 1 and published = 1 and id in (%s) order by start_date',implode(',',array_merge(array(0),$data['destEvents']))));

		//
		//	load individual products attached to this store
		//
		$frmFlds['destEventFolders']['options'] = $this->nodeSelect(0,'members_folders',2,false);
		$data['destEventFolders'] = $this->loadRelations('destEventFolders',$this,"altEventFormat",$data['id'],'storefolder','eventfolder','members_folders','',true);
		$frmFlds['destGalleryFolders']['options'] = $this->nodeSelect(0,'gallery_folders',2,false);
		$data['destGalleryFolders'] = $this->loadRelations('destGalleryFolders',$this,"altGalleryFormat",$data['id'],'storefolder','gallleryfolder','gallery_folders','',true);

		//
		//	access levels
		//
		$level = $this->getAccessLevel();
		switch($level) {
			case 1:
			case 2:
			case 3:
				break;	// admin can do anything
			case 4:
			default:
				unset($frmFlds['submit']);
				foreach($frmFlds as $key=>$fld) {
					$frmFlds[$key]['disabled'] = true;
				}
				if ($data['id'] == 0) $this->noAccessError();
				if (array_key_exists('showPageProperties',$_POST)) unset($_POST['showPageProperties']);
		}

		$frmFlds = $form->buildForm($frmFlds);
		$data['imagesel_a'] = $data['image'];
		$data['imagesel_b'] = $data['rollover_image'];
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
						$parent = $this->fetchSingle(sprintf('select * from %s where level = %d and left_id < %d and right_id > %d', $this->m_tree, $data['level'] - 1, $data['left_id'], $data['right_id']));
					else $parent['id'] = 0;
					if ($_POST['p_id'] != $parent['id']) {
						$this->logMessage('showPageProperties', sprintf('moving [%d] to [%d] posted[%d]',$data['id'],$p['id'], $_POST['p_id']), 1);
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
				$this->beginTransaction();
				$stmt = $this->prepare(sprintf('update %s set %s where id = %d',$this->m_tree, implode(',',$flds),$data['id']));
				$stmt->bindParams(array_merge(array(str_repeat('s', count($flds))),$values));
				$status = false;
				if ($status = $stmt->execute()) {
					if ($status) $status = $status && $this->updateRelations('destCoupons',$data['id'],'storefolder','coupon','coupons',true,true);
					if ($status) $status = $status && $this->updateRelations('destCouponFolders',$data['id'],'storefolder','couponfolder','coupon_folders',true,false);
					if ($status) $status = $status && $this->updateRelations('relatedDestProducts',$data['id'],'storefolder','product','product',true,true);
					if ($status) $status = $status && $this->updateRelations('destProductFolders',$data['id'],'storefolder','productfolder','product_folders',true,false);
					if ($status) $status = $status && $this->updateRelations('destEvents',$data['id'],'storefolder','event','events',true,true);
					if ($status) $status = $status && $this->updateRelations('destEventFolders',$data['id'],'storefolder','eventfolder','members_folders',true,false);
					if ($status) $status = $status && $this->updateRelations('destGalleryFolders',$data['id'],'storefolder','galleryfolder','gallery_folders',true,false);
				}
				if ($status) {
					$this->commitTransaction();
					if ($this->isAjax()) {
						$this->addMessage('Record successfully added');
						$this->logMessage("showPageProperties",sprintf("looking for message [%s]",print_r($this,true)),2);
						return $this->ajaxReturn(array(
								'status'=>'true',
								'html'=>'',
								'url'=>'/modit/stores?p_id='.$data['id']
						));
					}
				} else {
					$this->rollbackTransaction();
					$this->addError('Error occurred');
					$form->addTag('errorMessage',$this->showErrors(),false);
					if ($this->isAjax()) {
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
					$return = array('value'=>sprintf('<div class="wrapper"><div class="spacer"><a href="#" class="toggler" onclick="toggle(this);return false;">+</a></div>&nbsp;<a href="#" id="li_%d" class="%s icon_folder info">%s</a></div>',$data['id'], $expanded, htmlspecialchars($data['title'])),'submenu'=>$submenu);
				}
				else {
					$return = array('value'=>sprintf('<div class="wrapper"><div class="spacer">&nbsp;</div><a href="#" id="li_%d" class="%s icon_folder info">%s</a></div>',$data['id'], $expanded, htmlspecialchars($data['title'])),'submenu'=>array());
				}
				break;
			default:
				$value = new tag(false);
				$mptt = new mptt($table);
				$children = $mptt->fetchChildren($data['id']);
				$expanded='';
				if (count($submenu) > 0) {
					$return = array('value'=>sprintf('<div class="wrapper"><div class="spacer"><a href="#" class="toggler" onclick="toggle(this);return false;">+</a></div><a href="#" id="li_%d" class="%s icon_folder info">%s</a></div>',$data['id'], $expanded, htmlspecialchars($data['title'])),'submenu'=>$submenu);
				}
				else {
					$return = array('value'=>sprintf('<div class="wrapper"><div class="spacer">&nbsp;</div><a href="#" id="%s_li_%d" class="%s icon_folder info">%s</a></div>',$table,$data['id'], $expanded, htmlspecialchars($data['title'])),'submenu'=>array());
				}
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
			if ($data = $this->fetchSingle(sprintf('select * from %s where id = %d', $this->m_tree, $_REQUEST['p_id']))) {
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
		if ($p_id > 0 && $data = $this->fetchSingle(sprintf('select * from %s where id = %d', $this->m_tree, $p_id))) {
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
			$count = $this->fetchScalar(sprintf('select count(n.id) from %s n where n.id in (select f.store_id from %s f where f.folder_id = %d)', $this->m_content, $this->m_junction, $_REQUEST['p_id']));
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
			}
			$sql = sprintf('select i.*,j.id as j_id from %s i, %s j where j.folder_id = %d and i.id = j.store_id order by j.sequence limit %d,%d', $this->m_content, $this->m_junction, $_REQUEST['p_id'], $start, $perPage);
			$stores = $this->fetchAll($sql);
			$this->logMessage('showPageContent', sprintf('sql [%s], records [%d]',$sql, count($stores)), 2);
			$output = array();
			foreach($stores as $store) {
				$frm = new Forms();
				$frm->init($this->getTemplate('storeList'),array());
				$tmp = $frm->buildForm($this->getFields('storeList'));
				$frm->addData($store);
				$output[] = $frm->show();
			}
			$form->addTag('stores',implode('',$output),false);
			$form->addTag('pagination',$pagination,false);
			//
			//	get any applicable coupons
			//
			$coupons = $this->fetchAll(sprintf('select * from coupons c, relations r where c.deleted = 0 and r.owner_id = %d and r.owner_type = "storefolder" and c.id = r.related_id and r.related_type="coupon"',$data['id']));
			$output = array();
			foreach($coupons as $coupon) {
				$frm = new Forms();
				$frm->init($this->getTemplate('couponList'),array());
				$tmp = $frm->buildForm($this->getFields('couponList'));
				$frm->addData($coupon);
				$output[] = $frm->show();
			}
			$form->addTag('coupons',implode('',$output),false);

			$coupons = $this->fetchAll(sprintf('select * from coupon_folders c, relations r where r.owner_id = %d and r.owner_type = "storefolder" and c.id = r.related_id and r.related_type="couponfolder"',$data['id']));
			$output = array();
			foreach($coupons as $coupon) {
				$frm = new Forms();
				$frm->init($this->getTemplate('couponFolderList'),array());
				$tmp = $frm->buildForm($this->getFields('couponFolderList'));
				$frm->addData($coupon);
				$output[] = $frm->show();
			}
			$form->addTag('couponFolders',implode('',$output),false);

			//
			//	get any applicable products
			//
			$products = $this->fetchAll(sprintf('select * from product c, relations r where c.deleted = 0 and r.owner_id = %d and r.owner_type = "storefolder" and c.id = r.related_id and r.related_type="product"',$data['id']));
			$output = array();
			foreach($products as $product) {
				$frm = new Forms();
				$frm->init($this->getTemplate('infoProductList'),array());
				$tmp = $frm->buildForm($this->getFields('infoProductList'));
				$frm->addData($product);
				$output[] = $frm->show();
			}
			$form->addTag('products',implode('',$output),false);

			$products = $this->fetchAll(sprintf('select * from product_folders c, relations r where r.owner_id = %d and r.owner_type = "storefolder" and c.id = r.related_id and r.related_type="productfolder"',$data['id']));
			$output = array();
			foreach($products as $product) {
				$frm = new Forms();
				$frm->init($this->getTemplate('productFolderList'),array());
				$tmp = $frm->buildForm($this->getFields('productFolderList'));
				$frm->addData($product);
				$output[] = $frm->show();
			}
			$form->addTag('productFolders',implode('',$output),false);

			//
			//	get any applicable events
			//
			$events = $this->fetchAll(sprintf('select * from events c, relations r where c.deleted = 0 and r.owner_id = %d and r.owner_type = "storefolder" and c.id = r.related_id and r.related_type="event" and c.id in (select event_id from event_dates where event_date >= curdate())',$data['id']));
			$output = array();
			foreach($events as $event) {
				$frm = new Forms();
				$frm->init($this->getTemplate('infoEventList'),array());
				$tmp = $frm->buildForm($this->getFields('infoEventList'));
				$frm->addData($event);
				$output[] = $frm->show();
			}
			$form->addTag('events',implode('',$output),false);

			$events = $this->fetchAll(sprintf('select * from members_folders c, relations r where r.owner_id = %d and r.owner_type = "storefolder" and c.id = r.related_id and r.related_type="eventfolder"',$data['id']));
			$output = array();
			foreach($events as $event) {
				$frm = new Forms();
				$frm->init($this->getTemplate('infoEventFolderList'),array());
				$tmp = $frm->buildForm($this->getFields('infoEventFolderList'));
				$frm->addData($event);
				$output[] = $frm->show();
			}
			$form->addTag('eventFolders',implode('',$output),false);
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
			if (array_key_exists('formData',$_SESSION) && array_key_exists('storeSearchForm', $_SESSION['formData']))
				$_POST = $_SESSION['formData']['storeSearchForm'];
			else
				$_POST = array('showSearchForm'=>1,'deleted'=>0,'sortby'=>'published','sortorder'=>'asc');
		if (count($_POST) > 0 && array_key_exists('showSearchForm',$_POST)) {
			if ((!array_key_exists('deleted',$_POST)) || strlen($_POST['deleted']) == 0) $_POST['deleted'] = 0;
			$form->addData($_POST);
			$this->logMessage("showSearchForm",sprintf("before validation"),2);
			if ($form->validate()) {
				if (strlen($form->getData("quicksearch")) > 0) {
					$_SESSION['formData']['storeSearchForm'] = array('showSearchForm'=>1,'opt_quicksearch'=>'like','quicksearch'=>$form->getData("quicksearch"),'pager'=>$form->getData("pager"));
				}
				else
					$_SESSION['formData']['storeSearchForm'] = $form->getAllData();
				$srch = array();
				foreach($frmFields as $key=>$value) {
					switch($key) {
						case 'quicksearch':
							if (array_key_exists('opt_'.$key,$_POST) && strlen($_POST['opt_quicksearch']) > 0 && $value = $form->getData($key)) {
								if ($_POST['opt_quicksearch'] == 'like' && strpos($value,'%',0) === false) {
									$value = '%'.$value.'%';
								}
								$tmp = array();
								$tmp[] = sprintf(' teaser %s "%s"',$_POST['opt_quicksearch'],$this->escape_string($value));
								$tmp[] = sprintf(' name %s "%s"',$_POST['opt_quicksearch'],$this->escape_string($value));
								$tmp[] = sprintf(' description %s "%s"',$_POST['opt_quicksearch'],$this->escape_string($value));
								$srch = array(sprintf('(%s)',implode(' or ',$tmp)),' deleted = 0');
								continue;
							}
							break;
						case 'name':
							if (array_key_exists('opt_'.$key,$_POST) && strlen($_POST['opt_'.$key]) > 0 && $value = $form->getData($key)) {
								if ($_POST['opt_name'] == 'like' && strpos($value,'%',0) === false) {
									$value = '%'.$value.'%';
								}
								$srch[] = sprintf(' name %s "%s"',$_POST['opt_name'],$this->escape_string($value));
							}
							break;
						case 'created':
						case 'expires':
							if (array_key_exists('opt_'.$key,$_POST) && strlen($_POST['opt_'.$key]) > 0 && $value = $form->getData($key)) {
								if ($_POST['opt_'.$key] == 'like') {
									$this->addError('Like is not supported for dates');
								}
								else
									$srch[] = sprintf(' %s %s "%s"',$key, $_POST['opt_'.$key],$this->escape_string($value));
							}
							break;
						case 'folder':
							if (($value = $form->getData($key)) > 0) {
								$srch[] = sprintf(' id in (select store_id from %s where folder_id = %d) ',$this->m_junction, $value);
							}
							break;
						case 'featured':
						case 'enabled':
						case 'published':
						case 'deleted':
							if (!is_null($value = $form->getData($key)))
								if (strlen($value) > 0)
									$srch[] = sprintf(' %s = %s',$key,$this->escape_string($value));
							break;
						default:
							break;
					}
				}
				if (count($srch) > 0) {
					if (array_key_exists('pagenum',$_REQUEST))
						$pageNum = $_REQUEST['pagenum'];
					else
						$pageNum = 1;	// no 0 based calcs
					$perPage = $this->m_perrow;
					if (array_key_exists('pager',$_REQUEST)) $perPage = $_REQUEST['pager'];
					$count = $this->fetchScalar(sprintf('select count(n.id) from %s n where 1=1 and %s', $this->m_content, implode(' and ',$srch)));
					$pageNum = max(1,min($pageNum, (floor(($count-1)/$perPage)+1)));
					$form->setData('pagenum', $pageNum);
					$pagination = $this->pagination($count, $perPage, $pageNum,
							array('prev'=>$this->M_DIR.'forms/paginationPrev.html','next'=>$this->M_DIR.'forms/paginationNext.html',
									'pages'=>$this->M_DIR.'forms/paginationPage.html', 'wrapper'=>$this->M_DIR.'forms/paginationWrapper.html'));
					$start = ($pageNum-1)*$perPage;
					$sortorder = 'desc';
					$sortby = 'created';
					if (array_key_exists('sortby',$_POST)) {
						$sortby = $_POST['sortby'];
						$sortorder = $_POST['sortorder'];
					}
					$sql = sprintf('select *,0 as j_id from %s where 1=1 and %s order by %s %s limit %d,%d', $this->m_content, implode(' and ',$srch),$sortby, $sortorder, $start,$perPage);
					$recs = $this->fetchAll($sql);
					$this->logMessage('showSearchForm', sprintf('sql [%s] records [%d]',$sql,count($recs)), 2);
					$articles = array();
					foreach($recs as $article) {
						$frm = new Forms();
						$frm->init($this->getTemplate('storeList'),array());
						$tmp = $frm->buildForm($this->getFields('storeList'));
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
			//$code = Common::extractTag($tmp,array(0=>"<code>",1=>"</code>"),false);
			//$tmp = Common::replaceTag($tmp,array(0=>"<code>",1=>"</code>"),true,"");
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
			//$code = Common::extractTag($tmp,array(0=>"<code>",1=>"</code>"),false);
			//$tmp = Common::replaceTag($tmp,array(0=>"<code>",1=>"</code>"),true,"");
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
		if (!(array_key_exists('s_id',$_REQUEST) && $_REQUEST['s_id'] > 0 && $data = $this->fetchSingle(sprintf('select * from %s where id = %d', $this->m_content, $_REQUEST['s_id'])))) {
			$data = array('id'=>0,'published'=>false,'image1'=>'','image2'=>'','image3'=>'','image4'=>'','mapmarker'=>'');
			$addresses = array();
		} else {
			$addresses = $this->fetchAll(sprintf('select a.*, c.value from addresses a, code_lookups c where c.code = a.addresstype and c.type = "storeAddressTypes" and a.ownertype = "store" and a.ownerid = %d and a.deleted = 0',$_REQUEST['s_id']));
		}
		if ((count($_REQUEST) > 0 && array_key_exists('destSearch',$_REQUEST)) || $data['id'] > 0) {
			$srch = array();
			if (array_key_exists('destSearch',$_REQUEST)) {
				$srch = $_REQUEST['destSearch'];
				if (!is_array($srch)) $srch = array($srch);
			}
			if ($data['id'] > 0) {
				$tmp = $this->fetchScalarAll(sprintf('select folder_id from store_by_search_group where store_id = %d', $data['id']));
				$srch = array_merge($srch,$tmp);
			}
			if (count($srch) > 0) {
				$data['destSearch'] = $srch;
			}
			$this->logMessage(__FUNCTION__,sprintf('search folders [%s]',print_r($srch,true)),1);
		}

		//
		//	load folders this store does belong to
		//
		//$frmFields['destFolders'] = array('type'=>'ol','class'=>'draggable level_1 dest','id'=>'toFolderList','options'=>array(),'database'=>false);
		if (count($_REQUEST) > 0 && array_key_exists('destFolders',$_REQUEST) || $data['id'] > 0) {
			$ids = array();
			if (array_key_exists('destFolders',$_REQUEST)) {
				$ids = $_REQUEST['destFolders'];
				if (!is_array($ids)) $ids = array($ids);
			}
			if ($data['id'] > 0) {
				$tmp = $this->fetchScalarAll(sprintf('select folder_id from %s where store_id = %d',$this->m_junction,$data['id']));
				$ids = array_merge($ids,$tmp);
			}
			if (count($ids) > 0) {
				$data['destFolders'] = $ids;
			}
		}
		if ((count($_REQUEST) > 0 && array_key_exists('destSearch',$_REQUEST)) || $data['id'] > 0) {
			$srch = array();
			if (array_key_exists('destSearch',$_REQUEST)) {
				$srch = $_REQUEST['destSearch'];
				if (!is_array($srch)) $srch = array($srch);
			}
			if ($data['id'] > 0) {
				$tmp = $this->fetchScalarAll(sprintf('select folder_id from store_by_search_group where store_id = %d', $data['id']));
				$srch = array_merge($srch,$tmp);
			}
			if (count($srch) > 0) {
				$data['destSearch'] = $srch;
			}
			$this->logMessage(__FUNCTION__,sprintf('search folders [%s]',print_r($srch,true)),1);
		}

		$data["amenities"] = $this->fetchScalarAll(sprintf("select option_id from store_options where store_id = %d",$data["id"]));

		//
		//	load coupons attached to this store
		//
		$data['destCoupons'] = $this->loadRelations('destCoupons',$this,"altCouponFormat",$data['id'],'store','coupon','coupons','and deleted = 0 and enabled = 1 and published = 1',true);
		$frmFields['destCoupons']['options'] = $this->fetchOptions(sprintf('select id as code,code as value from coupons where deleted = 0 and enabled = 1 and published = 1 and id in (%s) order by code',implode(',',array_merge(array(0),$data['destCoupons']))));
		$frmFields['destCouponFolders']['options'] = $this->nodeSelect(0,'coupon_folders',2,false);
		$data['destCouponFolders'] = $this->loadRelations('destCouponFolders',$this,"altCouponFormat",$data['id'],'store','couponfolder','coupon_folders','',true);

		//
		//	load product folders attached to this store
		//
		$frmFields['destProductFolders']['options'] = $this->nodeSelect(0,'product_folders',2,false);
		$data['destProductFolders'] = $this->loadRelations('destProductFolders',$this,"altProductFormat",$data['id'],'store','productfolder','product_folders','',true);

		//
		//	load individual products attached to this store
		//
		$data['relatedDestProducts'] = $this->loadRelations('realtedDestProducts',$this,"altProductFormat",$data['id'],'store','product','product','and deleted = 0 and enabled = 1 and published = 1',true);
		$frmFields['relatedDestProducts']['options'] = $this->fetchOptions(sprintf('select id as code,concat(code," - ",name) as value from product where deleted = 0 and enabled = 1 and published = 1 and id in (%s) order by code',implode(',',array_merge(array(0),$data['relatedDestProducts']))));

		//
		//	load event folders attached to this store
		//
		$frmFields['destEventFolders']['options'] = $this->nodeSelect(0,'members_folders',2,false);
		$data['destEventFolders'] = $this->loadRelations('destEventFolders',$this,"altEventFormat",$data['id'],'store','eventfolder','members_folders','',true);
		$frmFields['destGalleryFolders']['options'] = $this->nodeSelect(0,'gallery_folders',2,false);
		$data['destGalleryFolders'] = $this->loadRelations('destGalleryFolders',$this,"altGalleryFormat",$data['id'],'store','galleryfolder','gallery_folders','',true);


		//
		//	load individual events attached to this store
		//
		$data['destEvents'] = $this->loadRelations('destEvents',$this,"altEventFormat",$data['id'],'store','event','events','and deleted = 0 and enabled = 1 and published = 1',true);
		$frmFields['destEvents']['options'] = $this->fetchOptions(sprintf('select id as code,name as value from events where id in (select event_id from event_dates where deleted = 0 and enabled = 1 and event_date >= curdate()) and id in (%s) order by start_date',implode(',',array_merge(array(0),$data['destEvents']))));
		//	access levels
		//
		$level = $this->getAccessLevel();
		switch($level) {
			case 1:
			case 2:
				break;	// admin can do anything
			case 3:
				$frmFields['published']['disabled'] = true;
				break;	// admin can do anything
			case 4:
			default:
				unset($frmFields['submit']);
				foreach($frmFields as $key=>$fld) {
					$frmFields[$key]['disabled'] = true;
				}
				if ($data['id'] == 0) $this->noAccessError();
				if (array_key_exists('addContent',$_POST)) unset($_POST['addContent']);
		}
		$data['imagesel_a'] = $data['image1'];
		$data['imagesel_b'] = $data['image2'];
		$data['imagesel_c'] = $data['image3'];
		$data['imagesel_d'] = $data['image4'];
		$data['imagesel_e'] = $data['mapmarker'];
		$form->addData($data);
		$form->addTag('addressForm',$this->loadAddresses($data['id']),false);

		$customFields = new custom();
		if (method_exists($customFields,'storeDisplay')) {
			$custom = $customFields->storeDisplay();
			$form->addTag('customTab',sprintf('<li><a href="#tabs-custom">%s</a></li>',$custom['description']),false);
			$html = $form->getHTML();
			$html = str_replace('%%customInfo%%',$custom['form'],$html);
			$form->setHTML($html);
			$frmFields = array_merge($frmFields,$custom['fields']);
		}
		$frmFields = $form->buildForm($frmFields);

		$addr = $form->getField('addresses');
		$addr->addOption('','-select an address-');
		foreach($addresses as $rec) {
			$addr->addOption($rec['id'],$rec['value']);
		}
		$status = 'false';	//assume it failed
		if (count($_POST) > 0 && array_key_exists('addContent',$_POST)) {
			$_POST['imagesel_a'] = $_POST['image1'];
			$_POST['imagesel_b'] = $_POST['image2'];
			$_POST['imagesel_c'] = $_POST['image3'];
			$_POST['imagesel_d'] = $_POST['image4'];
			$_POST['imagesel_e'] = $_POST['mapmarker'];
			if (!array_key_exists("destFolders",$_POST)) $_POST["destFolders"] = array();
			$form->addData($_POST);
			if ($form->validate()) {
				$id = $_POST['s_id'];
				unset($frmFields['s_id']);
				unset($frmFields['options']);
				foreach($frmFields as $key=>$fld) {
					if (!(array_key_exists('database',$fld) && $fld['database'] == false)) {
						$values[] = $form->getData($fld['name']);//$_REQUEST[$fld['name']];
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
					$this->addMessage('adding record');
				}
				else {
					$stmt = $this->prepare(sprintf('update %s set %s where id = %d', $this->m_content, implode(',',array_keys($flds)),$data['id']));
					$stmt->bindParams(array_merge(array(str_repeat('s', count($flds))),array_values($flds)));
					$this->addMessage('updating record');
				}
				$this->beginTransaction();
				if ($stmt->execute()) {
					$status = true;
					if ($id == 0) $id = $this->insertId();
					$destFolders = $_POST['destFolders'];
					if (!is_array($destFolders)) $destFolders = array($destFolders);
					//
					//	delete folders we are no longer a member of
					//
					$status = $status && $this->execute(sprintf('delete from %s where store_id = %d and folder_id not in (%s)',$this->m_junction,$id,implode(',',$destFolders)));
					//
					//	insert new folders
					//
					$new = $this->fetchScalarAll(sprintf('select id from %s where id in (%s) and id not in (select folder_id from %s where store_id = %d and folder_id in (%s))', $this->m_tree, implode(',',$destFolders),$this->m_junction,$id,implode(',',$destFolders)));
					$status = true;
					foreach($new as $key=>$folder) {
						$obj = new preparedStatement(sprintf('insert into %s(store_id,folder_id) values(?,?)',$this->m_junction));
						$obj->bindParams(array('dd',$id,$folder));
						$status = $status && $obj->execute();
						$this->resequence($folder);
					}
					$destSearch = array_key_exists('destSearch',$_POST) ? $_POST['destSearch'] : array(0);
					if (!is_array($destSearch)) $destSearch = array($destSearch);
					$this->execute(sprintf('delete from store_by_search_group where store_id = %d and folder_id not in (%s)', $id, implode(',',$destSearch)));
					$new = $this->fetchScalarAll(sprintf('select id from search_groups where id in (%s) and id not in (select folder_id from store_by_search_group where store_id = %d and folder_id in (%s))',
						implode(',',$destSearch),$id,implode(',',$destSearch)));
					foreach($new as $key=>$folder) {
						$obj = new preparedStatement('insert into store_by_search_group(store_id,folder_id) values(?,?)');
						$obj->bindParams(array('dd',$id,$folder));
						$status = $status && $obj->execute();
					}
					if ($status) $status = $status && $this->updateRelations('destCoupons',$id,'store','coupon','coupons',true,true);
					if ($status) $status = $status && $this->updateRelations('destCouponFolders',$id,'store','couponfolder','coupon_folders',true,false);
					if ($status) $status = $status && $this->updateRelations('destProductFolders',$id,'store','productfolder','product_folders',true,false);
					if ($status) $status = $status && $this->updateRelations('relatedDestProducts',$id,'store','product','product',true,false);
					if ($status) $status = $status && $this->updateRelations('destEventFolders',$id,'store','eventfolder','members_folders',true,false);
					if ($status) $status = $status && $this->updateRelations('destGalleryFolders',$id,'store','galleryfolder','gallery_folders',true,false);
					if ($status) $status = $status && $this->updateRelations('destEvents',$id,'store','event','events',true,false);

					if (array_key_exists("amenities",$_REQUEST)) {
						$stmt = $stmt && $this->execute(sprintf("delete from store_options where store_id = %d and option_id not in (%s)",$id,implode(",",array_merge(array(0),$_REQUEST["amenities"]))));
						$exists = "|".implode("|",$this->fetchScalarAll(sprintf("select option_id from store_options where store_id = %d",$id)))."|";
						foreach($_REQUEST["amenities"] as $key=>$opt) {
							if (strpos($exists,"|".$opt."|") === false) {
								$stmt = $this->prepare(sprintf("insert into store_options(store_id,option_id) values(?,?)"));
								$stmt->bindParams(array("ss",$id,$opt));
								$status = $status && $stmt->execute();
							}
						}
					}
					else $stmt = $stmt && $this->execute(sprintf("delete from store_options where store_id = %d",$id));
					if (method_exists($customFields,'storeUpdate')) {
						$flds["id"] = $id;
						$status = $status && $customFields->storeUpdate($flds,$_REQUEST);
					}

					if ($status) {
						$this->commitTransaction();
						if ($adding) {
							//
							//	let the address editing kick in now
							//
							$this->addMessage('Store Added');
							$form->setData('id',$id);
							return $this->ajaxReturn(array(
								'status' => 'true',
								'html' => $form->show(),
								'code' => 'loadContent('.$destFolders[0].')'
							));
						} else {
							$form->init($this->getTemplate('addContentSuccess'));
							$form->addData($_REQUEST);
							return $this->ajaxReturn(array(
								'status' => 'true',
								'html'=>$form->show()
							));
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
    function moveStore() {
		$src = 0;
		$dest = 0;
		if (array_key_exists('src',$_REQUEST)) $src = $_REQUEST['src'];
		if (array_key_exists('dest',$_REQUEST)) $dest = $_REQUEST['dest'];
		if ($_REQUEST['type'] == 'tree') {
			if ($src == 0 || $dest == 0 || !array_key_exists('type',$_REQUEST)) {
				return $this->ajaxReturn(array(
					'status' => 'false',
					'html' => 'Either source or destination was missing'
				));
			}
			$curr = $this->fetchScalar(sprintf('select store_id from %s where id = %d',$this->m_junction,$src));
			if ($curr > 0 && $folder = $this->fetchSingle(sprintf('select * from %s where id = %d',$this->m_tree,$dest))) {
				$status = true;
				if (array_key_exists('move',$_REQUEST)) {
					//
					//	move it - delete all other folders
					//
					$this->logMessage('moveStore', sprintf('moving store %d to folder %d',$src,$dest),2);
					$this->beginTransaction();
					if ($status = $this->execute(sprintf('delete from %s where id = %d', $this->m_junction, $src))) {
						if (!$this->fetchSingle(sprintf('select * from %s where store_id = %d and folder_id = %d',$this->m_junction,$curr,$dest))) {
							$obj = new preparedStatement(sprintf('insert into %s(store_id,folder_id) values(?,?)',$this->m_junction));
							$obj->bindParams(array('dd',$curr,$dest));
							if ($status = $obj->execute())
								$this->resequence($dest);
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
					$this->logMessage('moveStore', sprintf('cloning store %d to folder %d',$curr,$dest),2);
					if (!($this->fetchSingle(sprintf('select * from %s where store_id = %d and folder_id = %d',$this->m_junction,$curr,$dest)))) {
						$obj = new preparedStatement(sprintf('insert into %s(store_id,folder_id) values(?,?)',$this->m_junction));
						$obj->bindParams(array('dd',$curr,$dest));
						$status = $obj->execute();
						$this->resequence($dest);
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
			$src = $this->fetchSingle(sprintf('select * from %s where id = %d',$this->m_junction,$src));
			$sql = sprintf('select * from %s where folder_id = %d order by sequence limit %d,1',$this->m_junction,$src['folder_id'],$dest);
			$dest = $this->fetchSingle($sql);
			if (count($src) == 0 || count($dest) == 0) {
				$status = false;
				$this->addMessage('Either the source or destination store was not found');
			}
			else {
				$this->beginTransaction();
				$sql = sprintf('update %s set sequence = %d where id = %s',
					$this->m_junction, $src['sequence'] < $dest['sequence'] ? $dest['sequence']+1 : $dest['sequence']-1, $src['id']);
				$this->logMessage("moveStore",sprintf("move sql [$sql]"),2);
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
		$this->logMessage('resequence', 'resequencing folder $folder', 2);
		$stores = $this->fetchAll(sprintf('select * from %s where folder_id = %d order by sequence',$this->m_junction,$folder));
		$seq = 10;
		foreach($stores as $store) {
			$this->execute(sprintf('update %s set sequence = %d where id = %d',$this->m_junction,$seq,$store['id']));
			$seq += 10;
		}
	}

    /**
     * @return false|string
     * @throws phpmailerException
     */
    function deleteStore() {
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
						//$img = $this->fetchScalar(sprintf('select store_id from %s where id = %d',$this->m_junction,$_REQUEST['j_id']));
						$this->execute(sprintf('delete from %s where store_id = %d',$this->m_junction,$_REQUEST['j_id']));
						$this->execute(sprintf('update %s set deleted = 1 where id = %d',$this->m_content,$_REQUEST['s_id']));
						break;
					case 'one':
						$status = $this->execute(sprintf('delete from %s where id = %d',$this->m_junction,$_REQUEST['j_id']));
						$ct = $this->fetchScalar(sprintf('select count(0) from %s where store_id = %d',$this->m_junction,$_REQUEST['s_id']));
						if ($ct == 0)
							$this->execute(sprintf('update %s set deleted = 1 where id = %d',$this->m_content,$_REQUEST['s_id']));
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
		if (!array_key_exists('o_id',$_REQUEST))
			return $this->ajaxReturn(array('status'=>'false','html'=>'No id passed'));
		$status = true;
		$a_id = $_REQUEST['a_id'];
		$o_id = $_REQUEST['o_id'];
		if (!($data = $this->fetchSingle(sprintf('select * from addresses where id = %d and ownertype = "store" and ownerid = %d',$a_id,$o_id)))) {
			$data = array('id'=>0,'ownertype'=>'store','ownerid'=>$o_id);
			$addresses = array();
		}
		else 
			$addresses = $this->fetchAll(sprintf('select a.*, c.value from addresses a, code_lookups c where c.code = a.addresstype and c.type = "storeAddressTypes" and a.ownertype = "store" and a.ownerid = %d and a.deleted = 0',$_REQUEST['o_id']));
		$form = new Forms();
		$form->init($this->getTemplate('editAddress'),array('name'=>'editAddress'));
		$frmFields = $this->getFields('editAddress');
		if (count($addresses) > 0) {
			$frmFields['delete'] = array('type'=>'button','value'=>'Delete Address','database'=>false,'onclick'=>sprintf('deleteAddress(%d,%d);return false;',$a_id,$o_id));
		}
		$frmFields = $form->buildForm($frmFields);
		$form->addData($data);
		if (count($_POST) > 0 && array_key_exists('editAddress',$_POST)) {
			$form->addData($_POST);
			if ($status = $form->validate()) {
				unset($frmFields['options']);
				foreach($frmFields as $key=>$fld) {
					if (!(array_key_exists('database',$fld) && $fld['database'] == false)) {
						$flds[$fld['name']] = $form->getData($fld['name']);//$_REQUEST[$fld['name']];
					}
				}
				if ($data['id'] == 0) {
					$stmt = $this->prepare(sprintf('insert into addresses(%s) values(%s)', implode(',',array_keys($flds)), str_repeat('?,', count($flds)-1).'?'));
					$stmt->bindParams(array_merge(array(str_repeat('s', count($flds))),array_values($flds)));
				}
				else {
					$stmt = $this->prepare(sprintf('update addresses set %s=? where id = %d', implode('=?, ',array_keys($flds)),$data['id']));
					$stmt->bindParams(array_merge(array(str_repeat('s', count($flds))),array_values($flds)));
				}
				$this->beginTransaction();
				$store = $this->fetchSingle(sprintf("select * from stores where id = %d",$_POST["o_id"]));
				if ($status &= $stmt->execute()) {
					if (array_key_exists("geocode",$_POST) && $_POST["geocode"] == 1) {
						$url = urlencode(sprintf("%s, %s, %s %s",$flds['line1'],$flds['city'],
							$this->fetchScalar(sprintf('select province_code from provinces where id = %d',$flds['province_id'])),$flds['postalcode']));
						$this->logMessage(__FUNCTION__,sprintf("taken from [%s]",print_r($flds,true)),1);
						$s = new Snoopy();
						$s->host = 'https://maps.googleapis.com/maps/api/geocode/json';
						$s->port = 443;
						$s->curl_path = $GLOBALS['curl_path'];
						$s->fetch(sprintf('https://maps.googleapis.com/maps/api/geocode/json?address=%s&sensor=false&key=%s',$url,$this->getConfigVar("google_maps_key")));
						$result = $s->results;
						if ($s->status == "OK") {
							$result = json_decode($result,true);
							if (array_key_exists("results",$result) && is_array($result["results"]) && count($result["results"]) > 0) {
								$tmp = $result["results"][0];
								if (array_key_exists("geometry",$tmp) && array_key_exists("location",$tmp["geometry"])) {
									$xxx = $this->prepare(sprintf("update stores set latitude=?, longitude=? where id = %d", $_POST["o_id"]));
									$xxx->bindParams(array("dd",$tmp["geometry"]["location"]["lat"],$tmp["geometry"]["location"]["lng"]));
									$status &= $xxx->execute();
									$form->addData(array("geocode"=>$tmp));
								}
							} else {
								$this->addError("The address did not geocode");
								$status = false;
								$this->logMessage(__FUNCTION__,sprintf('snoopy [%s] result [%s]',print_r($s,true),print_r($result,true)),1,true,false);
							}
						} else {
							$this->addError("The address did not geocode");
							$status = false;
							$this->logMessage(__FUNCTION__,sprintf('snoopy [%s] result [%s]',print_r($s,true),print_r($result,true)),1,true,false);
						}
					}
				}
				if ($status) {
					$this->commitTransaction();
					$form->init($this->getTemplate('editAddressSuccess'));
				}
				else {
					$this->rollbackTransaction();
					$this->addError('An error occurred updating the database');
				}
			}
			else $this->addError('Form validation Failed');
		}
		$addresses = $this->fetchAll(sprintf('select * from addresses where ownertype = "store" and ownerid = %d and deleted = 0',$o_id));
		$addresses = $this->fetchAll(sprintf('select a.*, c.value from addresses a, code_lookups c where c.code = a.addresstype and c.type = "storeAddressTypes" and a.ownertype = "store" and a.ownerid = %d and a.deleted = 0',$_REQUEST['o_id']));
		$addr = $form->getField('addresses');
		$addr->addOption('','-select an address-');
		foreach($addresses as $rec) {
			$addr->addOption($rec['id'],$rec['value']);
		}
		return $this->ajaxReturn(array('status'=>$status,'html'=>$form->show()));
	}

    /**
     * @return false|string|void
     * @throws phpmailerException
     */
    function deleteAddress() {
		if (array_key_exists('a_id',$_REQUEST) && array_key_exists('o_id',$_REQUEST)) {
			$this->logMessage('deleteAddress',sprintf('deleting id [%d] owner [%d]',$_REQUEST['a_id'],$_REQUEST['o_id']),1);
			if ($data = $this->fetchSingle(sprintf('select * from addresses where ownertype = "store" and id = %d and ownerid = %d',$_REQUEST['a_id'],$_REQUEST['o_id']))) {
				$this->execute(sprintf('update addresses set deleted = 1 where id = %d',$_REQUEST['a_id']));
				return $this->ajaxReturn(array('status'=>'true'));
			}
		}
	}

    /**
     * @return false|string|void
     * @throws phpmailerException
     */
    function myCouponList() {
		if (array_key_exists('f_id',$_REQUEST)) {
			$tmp = parent::couponList($_REQUEST['f_id'],$this->getTemplate('couponByStoreList'),$this->getFields('couponByStoreList'));
			return $this->ajaxReturn(array('status'=>'true','html'=>$tmp));
		}
	}

    /**
     * @return false|string
     * @throws phpmailerException
     */
    function myProductList() {
		if (array_key_exists('p_id',$_REQUEST) && $_REQUEST['p_id'] > 0) {
			$tmp = parent::productList($_REQUEST['p_id'],$this->getTemplate('productList'),$this->getFields('productList'));
			return $this->ajaxReturn(array('status'=>'true','html'=>$tmp));
		}
		else return $this->ajaxReturn(array('status'=>'true','html'=>''));
	}

    /**
     * @return false|string
     * @throws phpmailerException
     */
    function myEventList() {
		if (array_key_exists('f_id',$_REQUEST) && $_REQUEST['f_id'] > 0) {
			$tmp = parent::eventList($_REQUEST['f_id'],$this->getTemplate('eventList'),$this->getFields('eventList'));
			return $this->ajaxReturn(array('status'=>'true','html'=>$tmp));
		}
		else return $this->ajaxReturn(array('status'=>'true','html'=>''));
	}

    /**
     * @return array|mixed|string|string[]|void
     * @throws phpmailerException
     */
    function edit() {
		if (array_key_exists('e_id',$_REQUEST) && $data = $this->fetchSingle(sprintf('select * from %s where id = %d',$this->m_content,$_REQUEST['e_id']))) {
			$f = new Forms();
			$f->init($this->getTemplate('editItem'));
			$f->addData($data);
			return $this->show($f->show());
		}
		else
			return $this->show();
	}

    /**
     * @param $method
     * @return bool
     * @throws phpmailerException
     */
    function hasFunctionAccess($method) {
		if (parent::hasFunctionAccess($method)) return true;
		return true;
	}

    /**
     * @param $passed_id
     * @return array|false|mixed|string|string[]
     * @throws phpmailerException
     */
    function loadAddresses($passed_id = null) {
		if ($passed_id == null)
			$o_id = array_key_exists('s_id',$_REQUEST) ? $_REQUEST['s_id'] : $_REQUEST['o_id'];
		else
			$o_id = $passed_id;
		$addresses = $this->fetchAll(sprintf('select a.*, c.value as addressType from addresses a, code_lookups c where ownertype = "store" and ownerid = %d and deleted = 0 and c.id = a.addressType',$o_id));
		$addressForm = new Forms();
		$addressForm->init($this->getTemplate('addressForm'));
		$addrForm = new Forms();
		$addrForm->init($this->getTemplate('addressList'));
		$addrFields = $addrForm->buildForm($this->getFields('addressList'));
		$addressList = array();
		foreach($addresses as $rec) {
			$addrForm->addData($rec);
			$addressList[] = $addrForm->show();
			//$addr->addOption($rec['id'],$rec['addresstype']);
		}
		$this->logMessage("loadAddresses",sprintf("addresses [%s] addresslist [%s] form [%s]",print_r($addresses,true),print_r($addressList,true),print_r($addrForm,true)),2);
		$addressForm->addTag('addresses',implode('',$addressList),false);
		if (!is_null($passed_id))
			return $addressForm->show();
		else
			return $this->ajaxReturn(array('status'=>'true','html'=>$addressForm->show()));
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
		else
			if (array_key_exists('formData',$_SESSION) && array_key_exists('storeSearchForm', $_SESSION['formData']))
				$form->addData($_SESSION['formData']['storeSearchForm']);
		return $form->show();
	}

    /**
     * @param int $fromMain
     * @return array|false|mixed|string|string[]|void
     * @throws phpmailerException
     */
    function moduleStatus($fromMain = 0) {
		if (array_key_exists('formData',$_SESSION) && array_key_exists('storeSearchForm', $_SESSION['formData'])) {
			$_POST = $_SESSION['formData']['storeSearchForm'];
			$msg = '';
		}
		else {
			$ct = $this->fetchScalar(sprintf('select count(0) from %s where deleted = 0 and published = 0',$this->m_content));
			if ($ct == 0) {
				$_POST = array('showSearchForm'=>1,'deleted'=>0,'sortby'=>'created','sortorder'=>'desc','pager'=>$this->m_perrow);
				$msg = "Showing latest stores added";
			}
			else {
				$_POST = array('showSearchForm'=>1,'published'=>0,'sortby'=>'created','sortorder'=>'desc','pager'=>$this->m_perrow);
				$msg = "Showing unpublished stores";
			}
		}
		$result = $this->showSearchForm($fromMain,$msg);
		return $result;
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
     * @return false|string|void
     * @throws phpmailerException
     */
    function deleteContent() {
		if (array_key_exists('p_id',$_REQUEST)) {
			$ct = $this->fetchScalar(sprintf('select count(0) from %s where folder_id = %d',$this->m_junction,$_REQUEST['p_id']));
			if ($ct > 0) {
				$this->addError('Stores are still attached to this folder');
				return $this->ajaxReturn(array('status'=>'false'));
			}
			$ct = $this->fetchScalar(sprintf('select count(0) from %s t1, %s t2 where t2.id = %d and t1.left_id > t2.left_id and t1.right_id < t2.right_id and t1.level > t2.level',$this->m_tree, $this->m_tree, $_REQUEST['p_id']));
			if ($ct > 0) {
				$this->addError('Other categories are still attached to this folder');
				return $this->ajaxReturn(array('status'=>'false'));
			}
			if (!$this->deleteCheck('stores',$_REQUEST['p_id'],$inUse)) {
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
    function myStoreList() {
		if (array_key_exists('f_id',$_REQUEST) && $_REQUEST['f_id'] > 0) {
			$tmp = parent::storeList($_REQUEST['f_id'],$this->getTemplate('storeByFolder'),$this->getFields('storeByFolder'));
			return $this->ajaxReturn(array('status'=>'true','html'=>$tmp));
		}
		else return $this->ajaxReturn(array('status'=>'true','html'=>''));
	}

}

?>