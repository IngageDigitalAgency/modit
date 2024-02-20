<?php

class dispatch extends Backend {

	private $m_tree = 'drivers';
	private $m_content = 'custom_delivery';
	private $m_perrow = 5;

	public function __construct() {
		$this->M_DIR = 'backend/modules/dispatch/';
		$this->setTemplates(
			array(
				'main'=>$this->M_DIR.'dispatch.html',
				'showSearchForm'=>$this->M_DIR.'forms/searchForm.html',
				'header'=>$this->M_DIR.'forms/heading.html',
				'articleList'=>$this->M_DIR.'forms/articleList.html',
				'drivers'=>$this->M_DIR.'forms/drivers.html',
				'driverRow'=>$this->M_DIR.'forms/driverRow.html',
				'moveArticle'=>$this->M_DIR.'forms/moveArticle.html',
				'moveArticleSuccess'=>$this->M_DIR.'forms/moveArticleSuccess.html',
				'status'=>$this->M_DIR.'forms/status.html',
				'statusRow'=>$this->M_DIR.'forms/statusRow.html',
				'statusAssigned'=>$this->M_DIR.'forms/statusAssigned.html',
				'getDriver'=>$this->M_DIR.'forms/getDriver.html',
				'getDriverPackage'=>$this->M_DIR.'forms/getDriverPackage.html',
				'mapIt'=>$this->M_DIR.'forms/map-outer.html',
				'mapItAddress'=>$this->M_DIR.'forms/map-inner.html',
				'mapItRoute'=>$this->M_DIR.'forms/map-route.html',
				'mapItDirections'=>$this->M_DIR.'forms/map-direction.html',
				'editPackage'=>$this->M_DIR.'forms/editPackage.html',
				'editPackageSuccess'=>$this->M_DIR.'forms/editPackageSuccess.html',
				'sameDays'=>$this->M_DIR.'forms/sameDays.html',
				'sameDaysRow'=>$this->M_DIR.'forms/sameDaysRow.html',
				'assignSameday'=>$this->M_DIR.'forms/assignSameday.html',
				'exportRoute'=>$this->M_DIR.'forms/exportRoute.html',
				'exportRoutePackage'=>$this->M_DIR.'forms/exportRoutePackage.html',
				'optimize'=>$this->M_DIR.'forms/optimize.html',
				'optimizeWP'=>$this->M_DIR.'forms/optimizeWP.html',
				'calendar'=>$this->M_DIR.'forms/calendar.html',
				'monthEvent'=>$this->M_DIR.'forms/monthEvent.html',
				'monthEvents'=>$this->M_DIR.'forms/monthEvents.html',
				'monthDay'=>$this->M_DIR.'forms/monthDay.html',
				'monthWeek'=>$this->M_DIR.'forms/monthWeeks.html',
				'headerByMonth'=>$this->M_DIR.'forms/headerByMonth.html',
				'editDate'=>$this->M_DIR.'forms/editDate.html',
				'editDateSuccess'=>$this->M_DIR.'forms/editDateSuccess.html',
				'arcgis'=>$this->M_DIR.'forms/arcgis.html',
				'arcgisWP'=>$this->M_DIR.'forms/argisWP.html',
				'allDeliveries'=>$this->M_DIR.'forms/allDeliveries.html',
				'allDeliveriesRow'=>$this->M_DIR.'forms/allDeliveriesRow.html',
				'messaging'=>$this->M_DIR.'forms/messaging.html',
				'messagingRow'=>$this->M_DIR.'forms/messagingRow.html',
				'editAck'=>$this->M_DIR.'forms/editAck.html',
				'getMessages'=>$this->M_DIR.'forms/getMessages.html',
				'getMessagesRow'=>$this->M_DIR.'forms/getMessagesRow.html',
				'manifests'=>$this->M_DIR.'forms/manifests.html',
				'manifestsRow'=>$this->M_DIR.'forms/manifestsRow.html',
				'editDelivery'=>$this->M_DIR.'forms/editDelivery.html',
				'enableDriver'=>$this->M_DIR.'forms/enableDriver.html',
				'disableDriver'=>$this->M_DIR.'forms/disableDriver.html',
				'sendSMS'=>$this->M_DIR.'forms/sendText.html'
			)
		);
		$this->setFields(array(
			'showSearchForm'=>array(
				'options'=>array('action'=>'showSearchForm','name'=>'searchForm','id'=>'search_form'),
				'opt_from'=>array('type'=>'select','lookup'=>'search_options','value'=>'<='),
				'from'=>array('type'=>'datepicker','required'=>false,'value'=>date('Y-m-d')),
				'opt_to'=>array('type'=>'select','lookup'=>'search_options'),
				'to'=>array('type'=>'datepicker','required'=>false),
				'driver_id'=>array('type'=>'select','required'=>false,'sql'=>'select 0 as id,"Unassigned" as code union (select d.id, concat(company, "-", lastname," ",firstname) as code from drivers d, members m where m.id = d.member_id and d.enabled = 1 and d.deleted = 0) order by if(id=0,id,code)'),
				'delivery_type'=>array('type'=>'select','required'=>false,'sql'=>sprintf('select p.id, name from product p, product_by_folder pf where pf.folder_id = %d and p.id = pf.product_id order by sequence',DELIVERY_TYPES)),
				'service_type'=>array('type'=>'select','required'=>false,'options'=>array(""=>"-none-","P"=>"Pickup","D"=>"Delivery")),
				'showSearchForm'=>array('type'=>'hidden','value'=>1),
				'pagenum'=>array('type'=>'hidden','value'=>1),
				'sortby'=>array('type'=>'hidden','value'=>'scheduled_date'),
				'sortorder'=>array('type'=>'hidden','value'=>'asc'),
				'quicksearch'=>array('type'=>'input','name'=>'quicksearch','required'=>false),
				'opt_quicksearch'=>array('type'=>'hidden','value'=>'like'),
				'pager'=>array('type'=>'select','required'=>true,'value'=>$this->m_perrow,'lookup'=>'paging','id'=>'pager'),
				'opt_delivered'=>array('type'=>'checkbox','required'=>false,'value'=>1),
				'third_party'=>array('type'=>'select','required'=>false,'required'=>false,'lookup'=>'boolean'),
				'order_id'=>array('type'=>'textfield','required'=>false,'class'=>'form-control'),
				'address'=>array('type'=>'textfield','required'=>false),
				'submit'=>array('type'=>'submitbutton','value'=>'Search')
			),
			'articleList'=>array(
				'scheduled_date'=>array('type'=>'datetimestamp','mask'=>'d-M h:i a')
			),
			'drivers'=>array(
			),
			'getDriver'=>array(
				'options'=>array('action'=>'getDriver','name'=>'getDriverForm','id'=>'getDriver_form','method'=>'POST'),
				'getDriver'=>array('type'=>'hidden','value'=>1),
				'scheduled_date'=>array('type'=>'datepicker','value'=>date('Y-m-d')),
				'opt_scheduled_date'=>array('type'=>'hidden'),
				'completed'=>array('type'=>'select','lookup'=>'boolean','value'=>'0'),
				'sortby'=>array('type'=>'hidden'),
				'sortorder'=>array('type'=>'hidden'),
				'driver_id'=>array('type'=>'tag'),
				'delivery_type'=>array('type'=>'select','required'=>false,'sql'=>sprintf('select p.id, name from product p, product_by_folder pf where pf.folder_id = %d and p.id = pf.product_id order by sequence',DELIVERY_TYPES)),
				'service_type'=>array('type'=>'select','required'=>false,'options'=>array(""=>"-none-","P"=>"Pickup","D"=>"Delivery")),
				'resort'=>array('type'=>'hidden','value'=>0),
				'resortId'=>array('type'=>'hidden','value'=>0),
				'resortTo'=>array('type'=>'hidden','value'=>0)
			),
			'getDriverPackage'=>array(
				'scheduled_date'=>array('type'=>'datetimestamp','mask'=>'d-M h:i a')
			),
			'mapIt'=>array(
			),
			'editPackage'=>array(
				'options'=>array('method'=>'POST','name'=>'editPackage','action'=>'/modit/ajax/editPackage/dispatch','database'=>false),
				'editPackage'=>array('type'=>'hidden','database'=>false),
				'p_id'=>array('type'=>'hidden','database'=>false,'value'=>'%%id%%'),
				'scheduled_date'=>array('type'=>'datetimepicker','required'=>true,'AMPM'=>true,'prettyName'=>'Scheduled Time'),
				'actual_date'=>array('type'=>'datetimepicker','required'=>false,'AMPM'=>true,'prettyName'=>'Actual Time'),
				'completed'=>array('type'=>'checkbox','value'=>1),
				'driver_id'=>array('type'=>'select','required'=>false,'sql'=>'select d.id,concat(company, "-", lastname," ",firstname) from drivers d, members m where d.enabled = 1 and d.deleted = 0 and m.id = d.member_id and d.enabled = 1 and d.deleted = 0 order by 2'),
				'comments'=>array('type'=>'textarea'),
				'instructions'=>array('type'=>'textarea'),
				'ack_status'=>array('type'=>'select','lookup'=>'driver_message_status','required'=>true),
				'dispatch_message'=>array('type'=>'textarea','value'=>'Please acknowledge change','database'=>true,'class'=>'xmceSimple'),
				'driver_message'=>array('type'=>'textarea','value'=>'','database'=>false,'class'=>'xmceSimple'),
				'company'=>array('type'=>'textfield','required'=>false,'name'=>'address[company]'),
				'firstname'=>array('type'=>'textfield','required'=>false,'name'=>'address[firstname]','prettyName'=>'First Name'),
				'lastname'=>array('type'=>'textfield','required'=>false,'name'=>'address[lastname]','prettyName'=>'Last Name'),
				'phone1'=>array('type'=>'textfield','required'=>false,'name'=>'address[phone1]','prettyName'=>'Phone Number'),
				'fax'=>array('type'=>'textfield','required'=>false,'name'=>'address[fax]'),
				'email'=>array('type'=>'textfield','required'=>false,'name'=>'address[email]','validation'=>'email','prettyName'=>'Email'),
				'custom_signature_required'=>array('type'=>'boolean','database'=>false),
				'line1'=>array('type'=>'textfield','required'=>true,'name'=>'address[line1]','prettyName'=>'Address Line 1'),
				'line2'=>array('type'=>'textfield','required'=>false,'name'=>'address[line2]','prettyName'=>'Address Line 2'),
				'city'=>array('type'=>'textfield','required'=>true,'name'=>'address[city]','prettyName'=>'City'),
				'postalcode'=>array('type'=>'textfield','required'=>true,'name'=>'address[postalcode]','prettyName'=>'Postal Code'),
				'province_id'=>array('type'=>'provinceSelect','required'=>true,'name'=>'address[province_id]','prettyName'=>'Province'),
				'country_id'=>array('type'=>'countrySelect','required'=>true,'name'=>'address[country_id]','prettyName'=>'Country'),
				'latitude'=>array('type'=>'textfield','required'=>false,'value'=>'0.0','validation'=>'number','name'=>'address[latitude]','class'=>'def_field_small'),
				'longitude'=>array('type'=>'textfield','required'=>false,'value'=>'0.0','validation'=>'number','name'=>'address[longitude]','class'=>'def_field_small'),
				'geocode'=>array('type'=>'checkbox','database'=>false,'value'=>1,'checked'=>'checked'),
				'submit'=>array('type'=>'submitbutton','value'=>'Save Changes','database'=>false)


			),
			'sameDays'=>array(
				'options'=>array('action'=>'sameDays','name'=>'sameDaysForm','id'=>'sameDays_form','method'=>'POST'),
				'pager'=>array('type'=>'select','required'=>true,'value'=>$this->m_perrow,'lookup'=>'paging','id'=>'pager'),
				'pagenum'=>array('type'=>'hidden','value'=>1),
				'sortby'=>array('type'=>'hidden','value'=>'scheduled_date'),
				'sortorder'=>array('type'=>'hidden','value'=>'asc'),
				'sameDays'=>array('type'=>'hidden','value'=>1),
				'scheduled_date'=>array('type'=>'datepicker','value'=>date('Y-m-d')),
				'opt_scheduled_date'=>array('type'=>'hidden','value'=>'<='),
				'opt_delivered'=>array('type'=>'select','lookup'=>'boolean'),
				'order_id'=>array('type'=>'number','class'=>'a-right form-field def_field_input'),
				'driver_id'=>array('type'=>'select','required'=>false,'sql'=>'select 0 as id,"Unassigned" as code union (select d.id, concat(company, "-", lastname," ",firstname) as code from drivers d, members m where m.id = d.member_id and d.enabled = 1 and d.deleted = 0) order by if(id=0,id,code)'),
				'delivery_type'=>array('type'=>'select','required'=>false,'sql'=>sprintf('select p.id, name from product p, product_by_folder pf where pf.folder_id = %d and p.id = pf.product_id order by sequence',DELIVERY_TYPES)),
				'third_party'=>array('type'=>'select','required'=>false,'lookup'=>'boolean'),
				'service_type'=>array('type'=>'select','required'=>false,'options'=>array(""=>"-none-","P"=>"Pickup","D"=>"Delivery")),
				'address'=>array('type'=>'textfield','required'=>false)
			),
			'sameDaysRow'=>array(
				'scheduled_date'=>array('type'=>'timestamp'),
				'actual_date'=>array('type'=>'timestamp')
			),
			'calendar'=>array(
				'options'=>array('name'=>'calendar','action'=>'calendar','method'=>'POST'),
				'calendar'=>array('type'=>'hidden','value'=>1),
				'sd'=>array('type'=>'hidden','value'=>date("Y-m-01")),
				'moveType'=>array('type'=>'hidden')
			),
			'editDate'=>array(
				'options'=>array('database'=>false,'name'=>'editDateForm','method'=>'POST','action'=>'editDate'),
				'editDate'=>array('type'=>'hidden','value'=>1,'database'=>false),
				'dt'=>array('type'=>'hidden','value'=>date("Y-m-d"),'database'=>false),
				'closed'=>array('type'=>'checkbox','value'=>1,'class'=>'form-control'),
				'close_time'=>array('type'=>'timepicker','AMPM'=>true,'required'=>false),
				'submit'=>array('type'=>'submitbutton','value'=>'Save','database'=>false),
				'id'=>array('type'=>'hidden','database'=>false),
				'office_date'=>array('type'=>'hidden'),
				'notes'=>array('type'=>'textarea','class'=>'mceSimple'),
				'stock_msg'=>array('type'=>'select','required'=>false,'lookup'=>'closed_messages'),
				'custom_msg'=>array('type'=>'textfield','required'=>false,'class'=>'form-control'),
				'delete'=>array('type'=>'checkbox','value'=>1,'database'=>false,'class'=>'form-control')
			),
			'allDeliveries'=>array(
				'options'=>array('action'=>'allDeliveries','name'=>'allDeliveriesForm','id'=>'allDeliveries_form','method'=>'POST'),
				'pager'=>array('type'=>'select','required'=>true,'value'=>$this->m_perrow,'lookup'=>'paging','id'=>'pager'),
				'pagenum'=>array('type'=>'hidden','value'=>1),
				'sortby'=>array('type'=>'hidden','value'=>'scheduled_date'),
				'sortorder'=>array('type'=>'hidden','value'=>'asc'),
				'allDeliveries'=>array('type'=>'hidden','value'=>1),
				'opt_scheduled_date'=>array('type'=>'select','lookup'=>'search_options'),
				'scheduled_date'=>array('type'=>'datepicker','value'=>date('Y-m-d')),
				'opt_delivered'=>array('type'=>'checkbox','value'=>1),
				'order_id'=>array('type'=>'textfield'),
				'custom_same_day'=>array('type'=>'select','required'=>false,'options'=>array(''=>'none','0'=>'Overnight','1'=>'Same Day')),
				'driver_id'=>array('type'=>'select','required'=>false,'sql'=>'select 0 as id,"Unassigned" as code union (select d.id, concat(company, "-", lastname," ",firstname) as code from drivers d, members m where m.id = d.member_id and d.enabled = 1 and d.deleted = 0) order by if(id=0,id,code)'),
				'delivery_type'=>array('type'=>'select','required'=>false,'sql'=>sprintf('select p.id, name from product p, product_by_folder pf where pf.folder_id in (%d,%d) and p.id = pf.product_id and p.enabled = 1 and p.published = 1 and p.deleted = 0 order by is_fedex,name',DELIVERY_TYPES,FEDEX_PRODUCTS)),
				'service_type'=>array('type'=>'select','required'=>false,'options'=>array(""=>"-none-","P"=>"Pickup","D"=>"Delivery"))
			),
			'allDeliveriesRow'=>array(
				'scheduled_date'=>array('type'=>'datestamp'),
				'actual_date'=>array('type'=>'timestamp')
			),
			'messaging'=>array(
				'options'=>array('action'=>'messaging','name'=>'messagingForm','id'=>'messagingform','method'=>'POST'),
				'pager'=>array('type'=>'select','required'=>true,'value'=>$this->m_perrow,'lookup'=>'paging','id'=>'pager'),
				'pagenum'=>array('type'=>'hidden','value'=>1),
				'sortby'=>array('type'=>'hidden','value'=>'scheduled_date'),
				'sortorder'=>array('type'=>'hidden','value'=>'asc'),
				'messaging'=>array('type'=>'hidden','value'=>1),
				'opt_scheduled_date'=>array('type'=>'select','lookup'=>'search_options'),
				'scheduled_date'=>array('type'=>'datepicker','value'=>date('Y-m-d')),
				'order_id'=>array('type'=>'textfield','value'=>0,'required'=>false),
				'custom_same_day'=>array('type'=>'select','required'=>false,'options'=>array(''=>'none','0'=>'Overnight','1'=>'Same Day')),
				'driver_id'=>array('type'=>'select','required'=>false,'sql'=>'select 0 as id,"Unassigned" as code union (select d.id, concat(company, "-", lastname," ",firstname) as code from drivers d, members m where m.id = d.member_id and d.enabled = 1 and d.deleted = 0) order by if(id=0,id,code)'),
				'service_type'=>array('type'=>'select','required'=>false,'options'=>array(""=>"-none-","P"=>"Pickup","D"=>"Delivery"))
			),
			'messagingRow'=>array(
				'created'=>array('type'=>'datetimestamp'),
				'acknowledged'=>array('type'=>'datetimestamp'),
				'status'=>array('type'=>'tag')
			),
			'editAck'=>array(
				'options'=>array('action'=>'editAck','name'=>'editAckForm','id'=>'editAckForm','database'=>false),
				'editAck'=>array('type'=>'hidden','value'=>1,'database'=>false),
				'a_id'=>array('type'=>'hidden',"value"=>"%%id%%",'database'=>false),
				'submit'=>array('type'=>'submitbutton','value'=>'Update','database'=>false),
				'created'=>array('type'=>'datetimestamp','database'=>false),
				'acknowledged'=>array('type'=>'datetimestamp','database'=>false),
				'dispatch_message'=>array('type'=>'textarea','class'=>'mceSimple'),
				'driver_message'=>array('type'=>'tag','disabled'=>'disabled','database'=>false,'class'=>'mceSimple'),
				'status'=>array('type'=>'select','lookup'=>'driver_message_status','required'=>true)
			),
			'getMessages'=>array(
				'created'=>array('type'=>'datetimestamp')
			),
			'manifests'=>array(
				'options'=>array('action'=>'manifests','name'=>'manifestsForm','id'=>'manifests_form','method'=>'POST'),
				'manifests'=>array('type'=>'hidden','value'=>1),
				'scheduled_date'=>array('type'=>'datepicker','value'=>date("Y-m-d",strtotime("today + 1 day"))),
				'opt_scheduled_date'=>array('type'=>'hidden'),
				'opt_delivered'=>array('type'=>'checkbox','value'=>1),
				'driver_id'=>array('type'=>'select','required'=>false,'sql'=>'select 0 as id,"Unassigned" as code union (select d.id, concat(company, "-", lastname," ",firstname) as code from drivers d, members m where m.id = d.member_id and d.enabled = 1 and d.deleted = 0) order by if(id=0,id,code)'),
				'delivery_type'=>array('type'=>'select','required'=>false,'sql'=>sprintf('select p.id, name from product p, product_by_folder pf where pf.folder_id = %d and p.id = pf.product_id order by sequence',DELIVERY_TYPES)),
				'service_type'=>array('type'=>'select','required'=>false,'options'=>array(""=>"-none-","P"=>"Pickup","D"=>"Delivery"))
			),
			'manifestsRow'=>array(
				'scheduled_date'=>array('type'=>'datetimestamp')
			),
			'sendSMS'=>array(
				"scheduled_date"=>array("type"=>"timestamp")
			)
		));
	
		parent::__construct ();
	}
	
	function __destruct() {
	
	}

	function show($injector = null) {
		$form = new Forms();
		$form->init($this->getTemplate('main'),array('name'=>'adminMenu'));
		$frmFields = $this->getFields('main');
		$frmFields = $form->buildForm($frmFields);
		if ($injector == null || strlen($injector) == 0) {
			$injector = $this->moduleStatus(true);
		}
		$form->addTag('injector', $injector, false);
		return $form->show();
	}

	function showForm() {
		$form = new Forms();
		$form->init($this->getTemplate('form'),array('name'=>'adminMenu'));
		$flds = $form->buildForm($this->getFields('form'));
		$form->getField('contenttree')->addAttribute('value',$this->buildTree($this->m_tree));
		if (count($_POST) > 0) {
			$form->addData($_POST);
			if ($form->validate()) {
				$this->addMessage('Validated');
			}
			else {
				$this->addError('Validation failed');
			}
		}
		return $form->show();
	}

	function showContentTree() {
		$form = new Forms();
		//
		// List of active drivers
		//
		$form->init($this->getTemplate('showContentTree'),array());
		if ($this->isAjax())
			return $this->ajaxReturn(array('status'=>'true','html'=>$form->show()));
		else
			return $form->show();
	}

	function showPageProperties($fromMain = false) {
		$result = array();
		$return = 'true';
		//Sonarcloud report - Variables should be initialized before use
		$form = new Forms();
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

	function showPageContent($fromMain = false) {
		$p_id = array_key_exists('p_id',$_REQUEST) ? $_REQUEST['p_id'] : 0;
		$form = new Forms();
		$perPage = $this->m_perrow;
		if ($p_id > 0 && $data = $this->fetchSingle(sprintf('select * from %s where id = %d',$this->m_tree,$p_id))) {
			if (strlen($data['alternate_title']) > 0) $data['connector'] = '&nbsp;-&nbsp;';
			$form->init($this->getTemplate('showFolderContent'),array('name'=>'showFolderContent'));
			$frmFields = $form->buildForm($this->getFields('showFolderContent'));
			if (array_key_exists('pagenum',$_REQUEST)) 
				$pageNum = $_REQUEST['pagenum'];
			else
				$pageNum = 1;	// no 0 based calcs
			if ($pageNum <= 0) $pageNum = 1;
			if (array_key_exists('pager',$_REQUEST)) 
				$perPage = $_REQUEST['pager'];
			else {
				$tmp = $this->checkArray("formData:dispatchSearchForm:pager",$_SESSION);
				if ($tmp > 0) 
					$perPage = $tmp;
				else
				$perPage = $this->m_perrow;
			}
			$form->setData('pager',$perPage);
			$count = $this->fetchScalar(sprintf('select count(n.id) from %s n where n.id in (select f.product_id from %s f where f.folder_id = %d) and n.deleted = 0', $this->m_content, $this->m_junction, $_REQUEST['p_id']));
			$pagination = $this->pagination($count, $perPage, $pageNum, 
				array('prev'=>$this->M_DIR.'forms/paginationPrev.html','next'=>$this->M_DIR.'forms/paginationNext.html',
				'pages'=>$this->M_DIR.'forms/paginationPage.html', 'wrapper'=>$this->M_DIR.'forms/paginationWrapper.html'),
				array('url'=>'/modit/ajax/showPageContent/dispatch','destination'=>'middleContent'));
			$start = ($pageNum-1)*$perPage;
			$sortby = 'sequence';
			$sortorder = 'asc';
			if (count($_POST) > 0 && array_key_exists('showFolderContent',$_POST)) {
				$sortby = $_POST['sortby'];
				$sortorder = $_POST['sortorder'];
				$form->addData($_POST);
			}
			$sql = sprintf('select a.*, f.id as j_id from %s a left join %s f on a.id = f.product_id where a.deleted = 0 and f.folder_id = %d order by %s %s limit %d,%d',  $this->m_content, $this->m_junction, $_REQUEST['p_id'],$sortby, $sortorder, $start,$perPage);
			$products = $this->fetchAll($sql);
			$articles = array();
			foreach($products as $product) {
				$frm = new Forms();
				$frm->init($this->getTemplate('articleList'),array());
				$tmp = $frm->buildForm($this->getFields('articleList'));
				$frm->addData($product);
				$articles[] = $frm->show();
			}
			$form->addTag('articles',implode('',$articles),false);
			$form->addTag('pagination',$pagination,false);
			$form->addData($data);
		}
		$form->addTag('heading',$this->getHeader(),false);
		if (array_key_exists('formData',$_SESSION) && array_key_exists('dispatchSearchForm', $_SESSION['formData']))
			$_SESSION['formData']['dispatchSearchForm']['pager'] = $perPage;
		if ($this->isAjax()) {
			$tmp = $form->show();
			return $this->ajaxReturn(array('status'=>'true','html'=>$tmp)); 
		}
		elseif ($fromMain)
			return $form->show();
		else
			return $this->show($form->show());
	}

	function showSearchForm($fromMain = false,$msg = '') {
		$form = new Forms();
		$form->init($this->getTemplate('showSearchForm'),array('name'=>'showSearchForm','persist'=>true));
		$frmFields = $form->buildForm($this->getFields('showSearchForm'));
		if (count($_POST) == 0)
			if (array_key_exists('formData',$_SESSION) && array_key_exists('dispatchSearchForm', $_SESSION['formData']))
				$_POST = $_SESSION['formData']['dispatchSearchForm'];
			else
				$_POST = array('showSearchForm'=>1,'deleted'=>0,'sortby'=>'published','sortorder'=>'asc');
		if (count($_POST) > 0 && array_key_exists('showSearchForm',$_POST)) {
			$form->addData($_POST);
			if ($form->validate()) {
				if (strlen($form->getData("quicksearch")) > 0) {
					$_SESSION['formData']['dispatchSearchForm'] = array('showSearchForm'=>1,'opt_quicksearch'=>'like','quicksearch'=>$form->getData("quicksearch"),'pager'=>$form->getData("pager"));
				}
				else
					$_SESSION['formData']['dispatchSearchForm'] = $form->getAllData();
				$srch = array("opt_delivered"=>"completed = 0 and o.order_status_processing","custom_same_day"=>"custom_same_day = 0","j1"=>"ol.order_id = n.order_id",
					"j2"=>"p.id = ol.product_id","j3"=>"ol.custom_package = 'S'","j4"=>"o.id = ol.order_id","j5"=>"a.ownerid = o.id","j6"=>"a.ownertype='order'");
				foreach($frmFields as $key=>$value) {
					switch($key) {
						case 'quicksearch':
							if (array_key_exists('opt_quicksearch',$_POST) && $_POST['opt_quicksearch'] != null && $value = $form->getData($key)) {
								if ($_POST['opt_quicksearch'] == 'like' && strpos($value,'%',0) === false) {
									$value = '%'.$value.'%';
								}
								$tmp = array();
								$tmp[] = sprintf('code %s "%s"',$_POST['opt_quicksearch'],$this->escape_string($value));
								$tmp[] = sprintf('name %s "%s"',$_POST['opt_quicksearch'],$this->escape_string($value));
								$tmp[] = sprintf('description %s "%s"',$_POST['opt_quicksearch'],$this->escape_string($value));
								$srch["all"] = array(sprintf('(%s)',implode(' or ',$tmp)),'deleted = 0');
								break 2;
							}
							break;
						case 'from':
						case 'to':
							if (array_key_exists("opt_$key",$_POST) && $_POST["opt_$key"] != null && $value = $form->getData($key)) {
								if ($_POST["opt_$key"] == 'like' && strpos($value,'%',0) === false) {
									$value = '%'.$value.'%';
								}
								$srch[$key] = sprintf('date(scheduled_date) %s "%s"',$_POST["opt_$key"],$this->escape_string($value));
							}
							break;
						case 'service_type':
						case 'driver_id':
							if (array_key_exists($key,$_POST)) {
								$value = $_POST[$key];
								if (strlen($value) > 0)
									$srch[$key] = sprintf('%s = "%s"',$key,$this->escape_string($value));
							}
							break;
						case 'custom_same_day':
							$this->logMessage(__FUNCTION__,sprintf("custom_same_day value is [%s] strlen [%s]",$_POST[$key], strlen($_POST[$key])),1);
							if (array_key_exists($key,$_POST)) {
								$value = $_POST[$key];
								if (strlen($value) > 0)
									$srch[$key] = sprintf('n.order_id in (select ol.order_id from order_lines ol, product p where p.id = ol.product_id and p.custom_same_day = %d and ol.custom_package="S")',$this->escape_string($value));
							}
							break;
						case 'delivery_type':
							if (array_key_exists($key,$_POST)) {
								$value = $_POST[$key];
								if (strlen($value) > 0)
									$srch[$key] = sprintf('n.order_id in (select ol.order_id from order_lines ol, product p where p.id = ol.product_id and p.id = %d and ol.custom_package="S")',$this->escape_string($value));
							}
							break;
						case 'opt_delivered':
							if (array_key_exists($key,$_POST)) {
								$value = $_POST[$key];
								if (strlen($value) > 0)
									$srch[$key] = sprintf('n.completed = %d',$value);
							}
							break;
						case "third_party":
							if (strlen($form->getData($key)) > 0)
								$srch[$key] = sprintf('d.third_party = %d',$form->getData($key));
							break;
						case "order_id":
							if (array_key_exists("order_id", $_POST) && strlen($value = $_POST[$key]) > 0)
								$srch[$key] = sprintf('n.order_id = %d',$value);
							break;
						case "address":
							if (strlen($form->getData($key)) > 0)
								$srch[$key] = sprintf('(a.line1 like "%%%1$s%%" or a.city like "%%%1$s%%" or a.postalcode like "%%%1$s%%")',$form->getData($key));
							break;
						default:
							break;
					}
				}
				$srch["not_cancelled"] = sprintf("o.order_status & %d = 0", STATUS_CANCELLED);
				if (count($srch) > 0) {
					if (array_key_exists('pagenum',$_REQUEST))
						$pageNum = $_REQUEST['pagenum'];
					else
						$pageNum = 1;	// no 0 based calcs
					if (array_key_exists('pager',$_REQUEST)) 
						$perPage = $_REQUEST['pager'];
					else {
						$tmp = $this->checkArray("formData:dispatchSearchForm:pager",$_SESSION);
						if ($tmp > 0) 
							$perPage = $tmp;
						else
						$perPage = $this->m_perrow;
					}
					$form->setData('pager',$perPage);
					$count = $this->fetchScalar(sprintf('select count(n.id) from %s n left join drivers d on n.driver_id = d.id, order_lines ol, product p, orders o, addresses a where %s', $this->m_content, implode(' and ',array_values($srch))));
					$pageNum = max(1,min($pageNum, (floor(($count-1)/$perPage)+1)));
					$form->setData('pagenum', $pageNum);
					$pagination = $this->pagination($count, $perPage, $pageNum,
							array('prev'=>$this->M_DIR.'forms/paginationPrev.html','next'=>$this->M_DIR.'forms/paginationNext.html',
							'pages'=>$this->M_DIR.'forms/paginationPage.html', 'wrapper'=>$this->M_DIR.'forms/paginationWrapper.html'),
							array('url'=>'/modit/ajax/showSearchForm/dispatch','destination'=>'middleContent')
						);
					$start = ($pageNum-1)*$perPage;
					$sortorder = 'asc';
					$sortby = 'scheduled_date';
					if (array_key_exists('sortby',$_POST)) {
						$sortby = $_POST['sortby'];
						$sortorder = $_POST['sortorder'];
					}
					$sql = sprintf('select n.*, p.code, p.name, o.member_id, o.order_status & %d as needs_approval, concat(m.company, "-", m.lastname," ",m.firstname) as drivername, a.line1, a.city, a.postalcode, date(scheduled_date) as cd, curdate() as td 
from %s n left join drivers d on d.id = n.driver_id left join members m on m.id = d.member_id, product p, orders o, addresses a, order_lines ol 
where %s and o.deleted = 0 and o.id = n.order_id and a.addresstype = IF(n.service_type="P",%d,%d)  and ol.order_id = o.id and ol.custom_package = "S" and p.id = ol.product_id order by %s %s limit %d,%d',
						 STATUS_NEEDS_APPROVAL, $this->m_content, implode(' and ',$srch), ADDRESS_PICKUP, ADDRESS_DELIVERY, $sortby, $sortorder, $start,$perPage);
					$recs = $this->fetchAll($sql);
					$articles = array();
					foreach($recs as $article) {
						$frm = new Forms();
						$frm->init($this->getTemplate('articleList'),array());
						$tmp = $frm->buildForm($this->getFields('articleList'));
						$article["services"] = implode(", ", $this->fetchScalarAll(sprintf("select p.name from product p, order_lines ol where ol.order_id = %d and ol.custom_package = 'A' and ol.deleted = 0 and p.id = ol.product_id and p.custom_special_requirement = 1", $article["order_id"])));
						$frm->addData($article);
						$articles[] = $frm->show();
					}
					$form->addTag('articles',implode('',$articles),false);
					$form->addTag('pagination',$pagination,false);
					$form->addTag('statusMessage',sprintf('We found %d record%s matching the criteria',$count,$count != 1 ? 's' : ''));
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

	function addContent($fromMain = false) {
		$form = new Forms();
		$form->init($this->getTemplate('addContent'),array('name'=>'addContent'));
		$frmFields = $this->getFields('addContent');
		//Sonarcloud report - Variables should be initialized before use
        if (!isset($status)) {
            $status = "";
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

	function moveArticle() {
		$src = 0;
		$dest = 0;
		$outer = new Forms();
		$outer->init($this->getTemplate("moveArticle"));
		if (array_key_exists('src',$_REQUEST)) $src = $_REQUEST['src'];
		if (array_key_exists('dest',$_REQUEST)) $dest = $_REQUEST['dest'];
		if ($_REQUEST['type'] == 'tree') {
			if ($src == 0 || $dest == 0 || !array_key_exists('type',$_REQUEST)) {
				$this->addError('Either source or destination was missing');
				return $this->ajaxReturn(array('status' => 'false'));
			}
			if ($driver = $this->fetchSingle(sprintf('select * from drivers where id = %d',$dest))) {
				//
				//	Assigning to a new/different driver
				//
				//	1 - recheck "downtown" surcharges
				//	2 - recalculate commissions
				//	3 - set status for driver confirmation
				//
				$curr = $this->fetchSingle(sprintf("select * from custom_delivery where id = %d", $src));
				$this->execute(sprintf("update custom_delivery set driver_id = %d, driver_sequence = 9999, ack_status = %d, ack_requested = '%s', dispatch_message = '%s', driver_message='' where id = %d",$dest, ACK_REQUEST, date(DATE_ATOM), 'Assigned by dispatch', $src));
				$this->resequence($driver["id"], date("Y-m-d", strtotime($curr["scheduled_date"])));

				$this->sendSMS($src);

				$o_id = $this->fetchScalar(sprintf("select order_id from custom_delivery where id = %d",$src));
				$c = new Common();
				$c->recalcDowntown($o_id);
				$this->calc_driver_allocations($o_id);
				$this->calculateCommissions($o_id);
				$status = true;
				$outer->init($this->getTemplate("moveArticleSuccess"));
			} else {
				$status = false;
				$this->addError('Could not locate the destination driver');
			}
		}
		else {
			if ($src == 0 || $dest < 0) {
				$this->addMessage('Either source or destination was missing');
				return $this->ajaxReturn(array('status' => 'false'));
			}
			$src = $this->fetchSingle(sprintf('select * from %s where id = %d',$this->m_content,$src));
			$sql = sprintf('select * from %s where driver_id = %d and id = %d',$this->m_content,$src['driver_id'],$dest);
			$dest = $this->fetchSingle($sql);
			$this->logMessage("moveArticle",sprintf("move src [%s] to dest [%s] sql [%s]",print_r($src,true),print_r($dest,true),$sql),2);
			if (count($src) == 0 || count($dest) == 0) {
				$status = false;
				$this->addMessage('Either the source or destination package was not found');
			}
			else {
				//
				//	swap the order of the images
				//
				$this->logMessage('moveArticle', sprintf('swap the sort order of %d and %d',$src['id'],$dest['id']),2);
				$this->beginTransaction();
				$sql = sprintf('update %s set driver_sequence = %d where id = %s',
					$this->m_content, $src['driver_sequence'] < $dest['driver_sequence'] ? $dest['driver_sequence']+1 : $dest['driver_sequence']-1, $src['id']);
				if ($this->execute($sql)) {
					$this->resequence($src['driver_id']);
					$this->commitTransaction();
					$status = true;
					$outer->init($this->getTemplate("moveArticleSuccess"));
				}
				else {
					$this->rollbackTransaction();
					$status = false;
				}
			}
		}
		return $this->ajaxReturn(array(
				'status'=>$status?'true':'false','html'=>$outer->show(),'messages'=>$this->showMessages()
		));
	}
	
	function resequence($driver, $dt) {
		$this->logMessage(__FUNCTION__, sprintf("resequencing driver %d for date %s", $driver, $dt), 2);
		$articles = $this->fetchAll(sprintf('select cd.* from %s cd, orders o where driver_id = %d and date(scheduled_date) <= "%s" and completed = 0 and o.id = cd.order_id and o.order_status & %d = %d  order by driver_sequence',$this->m_content,$driver, max(date("Y-m-d"),$dt), STATUS_PROCESSING, STATUS_PROCESSING));
		$seq = 10;
		foreach($articles as $article) {
			$this->execute(sprintf('update %s set driver_sequence = %d where id = %d',$this->m_content,$seq,$article['id']));
			$seq += 10;
		}
	}

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

	function moduleStatus($fromMain = 0) {
		if (count($_POST) == 0 && array_key_exists("overnights",$_REQUEST)) {
			$_POST = array_merge(array("showSearchForm"=>1, "pager"=>$this->m_perrow, "pagenum"=>1, "sortby"=>"scheduled_date", "sortorder"=>"asc"), $_REQUEST);
			$msg = "";
		}
		else {
			if (array_key_exists('formData',$_SESSION) && array_key_exists('dispatchSearchForm', $_SESSION['formData'])) {
				$_POST = $_SESSION['formData']['dispatchSearchForm'];
				$msg = '';
			}
			else {
				$_POST = array('showSearchForm'=>1,'from'=>date("Y-m-d"),'opt_from'=>'<=','pager'=>$this->m_perrow,'sortby'=>'scheduled_date','sortorder'=>'asc','custom_same_day'=>0);
				$msg = "Showing todays delivery/pickups";
			}
		}
		$result = $this->showSearchForm($fromMain,$msg);
		return $result;
	}

	function getHeader() {
		$form = new Forms();
		$form->init($this->getTemplate('header'));
		$flds = $form->buildForm($this->getFields('showSearchForm'));
		if (count($_POST) > 0 && array_key_exists('showSearchForm',$_POST))
			$form->addData($_POST);
		return $form->show();
	}

	function hasFunctionAccess($method) {
		if (parent::hasFunctionAccess($method)) return true;
		return true;
	}

	function getDrivers() {
		$outer = new Forms();
		$outer->init($this->getTemplate('drivers'));
		$flds = $this->getFields('drivers');
		$inner = new Forms();
		$inner->init($this->getTemplate('driverRow'));
		$flds = $inner->buildForm($flds);
		$drivers = $this->fetchAll(sprintf("select d.*, m.company, m.firstname, m.lastname from members m, drivers d where d.enabled = 1 and d.deleted = 0 and m.id = d.member_id order by m.company, lastname, firstname"));
		$result = array();
		foreach($drivers as $key=>$value) {
			$value["disabled"] = date("Y-m-d") == date("Y-m-d",strtotime($value["disabled_as_of"]));
			$inner->reset();
			$inner->addData($value);
			$result[] = $inner->show();
		}
		$outer->addTag("drivers",implode("",$result),false);
		if ($this->isAjax())
			return $this->ajaxReturn(array("status"=>true,"html"=>$outer->show()));
		else
			return $outer->show();
	}

	function getStatus() {
		$outer = new Forms();
		$outer->init($this->getTemplate('status'));
		$inner = new Forms();
		$inner->init($this->getTemplate('statusRow'));
		$flds = $inner->buildForm($this->getFields('status'));
		$packages = $this->fetchAll(sprintf("select count(cd.id) as cnt, service_type, completed as delivered from custom_delivery cd left join drivers d on d.id = cd.driver_id where date(scheduled_date) <= '%s' and actual_date = '0000-00-00 00:00:00' and third_party=0 group by service_type, delivered",date("Y-m-d")));
		//$packages = $this->fetchAll(sprintf("select count(cd.id) as cnt, service_type, completed as delivered from custom_delivery cd where date(scheduled_date) <= '%s' and actual_date = '0000-00-00 00:00:00' group by service_type, delivered",date("Y-m-d")));
		$result = array();
		foreach($packages as $key=>$value) {
			$inner->reset();
			$inner->addData($value);
			$result[] = $inner->show();
		}
		$outer->addTag("status",implode("",$result),false);
		$packages = $this->fetchAll(sprintf("select count(cd.id) as cnt, service_type, IF(driver_id=0,0,1) as assigned from custom_delivery cd left join drivers d on d.id = cd.driver_id where date(scheduled_date) <= '%s' and actual_date = '0000-00-00 00:00:00' and third_party = 0 group by service_type, assigned",date("Y-m-d")));
		//$packages = $this->fetchAll(sprintf("select count(cd.id) as cnt, service_type, IF(driver_id=0,0,1) as assigned from custom_delivery cd where date(scheduled_date) <= '%s' and actual_date = '0000-00-00 00:00:00' group by service_type, assigned",date("Y-m-d")));
		$result = array();
		$inner->init($this->getTemplate('statusAssigned'));
		foreach($packages as $key=>$value) {
			$inner->reset();
			$inner->addData($value);
			$result[] = $inner->show();
		}
		$outer->addTag("assigned",implode("",$result),false);
		if ($this->isAjax())
			return $this->ajaxReturn(array("status"=>true,"html"=>$outer->show()));
		else
			return $outer->show();
	}

	function getDriver() {
		$outer = new Forms();
		$outer->init($this->getTemplate("getDriver"));
		$flds = $outer->buildForm($this->getFields("getDriver"));
		if (count($_POST) == 0 || !array_key_exists("getDriver",$_POST)) {
			if (array_key_exists('formData',$_SESSION) && array_key_exists('getDriverSearchForm', $_SESSION['formData']))
				$_POST = $_SESSION['formData']['getDriverSearchForm'];
			else
				$_POST = array("scheduled_date"=>date("Y-m-d"),"opt_scheduled_date"=>"<=","pagenum"=>1,"pager"=>$this->m_perrow,"sortby"=>"driver_sequence","sortorder"=>"asc","driver_id"=>array_key_exists("d_id",$_REQUEST) ? $_REQUEST["d_id"] : 0,"completed"=>"0");
		}
		if (array_key_exists("d_id",$_REQUEST) && ($_REQUEST["d_id"] != $_POST["driver_id"])) $_POST["driver_id"] = $_REQUEST["d_id"];
		$outer->addData($_POST);
		$outer->validate();
		if (array_key_exists("resort",$_POST) && $_POST["resort"] == 1) {
			$resort = true;
			if ($outer->getData("completed") != 0) {
				$outer->addFormError("Resorting can not be done when showing completed services");
				$resort = false;
			}
			if ($outer->getData("service_type") != "") {
				$outer->addFormError("Resorting can not be done when service type is selected");
				$resort = false;
			}
			if ($resort) {
				if ($outer->getData("resortTo") < 0) {
					$this->execute(sprintf("update custom_delivery set driver_sequence = 0 where id = %d", $outer->getData("resortId")));
				}
				else {
					$this->execute(sprintf("update custom_delivery set driver_sequence = %d+1 where id = %d", 
						$this->fetchScalar(sprintf("select driver_sequence from custom_delivery where id = %d", $outer->getData("resortTo"))),
						$outer->getData("resortId")));
				}
				$recs = $this->fetchAll(sprintf("select * from custom_delivery where driver_id = %d and scheduled_date <= '%s' and completed = 0 order by driver_sequence",
					$outer->getData("driver_id"), $outer->getData("scheduled_date")));
				$recs = $this->fetchAll(sprintf("select c.id from custom_delivery c, orders o 
where date_format(scheduled_date,'%%Y-%%m-%%d') <= '%s' and driver_id = %d and c.order_id = o.id and o.order_status & %d = %d 
order by driver_sequence asc", $outer->getData("scheduled_date"), $outer->getData("driver_id"), STATUS_PROCESSING, STATUS_PROCESSING));
				$x = 0;
				foreach($recs as $k=>$v) {
					$x += 1;
					$this->execute(sprintf("update custom_delivery set driver_sequence = %d where id = %d", $x*10, $v["id"]));
				}
			}
		}
		$_POST["resort"] = 0;
		$srch = array();
		$rt = array();
		foreach($_POST as $key=>$value) {
			switch($key) {
			case "opt_scheduled_date":
				$rt_dt = date("Y-m-d",strtotime($_POST["scheduled_date"]));
				$srch[$key] = sprintf("date_format(scheduled_date,'%%Y-%%m-%%d') <= '%s'", $rt_dt);
				break;
			case "driver_id":
				if ($value != "") $srch[] = sprintf("driver_id = %d",$value);
				$rt_d_id = $value;
				break;
			case "delivery_type":
				if (array_key_exists($key,$_POST)) {
					$value = $_POST[$key];
					if (strlen($value) > 0)
						$srch[$key] = sprintf(' ol.order_id in (select ol.order_id from order_lines ol, product p where p.id = ol.product_id and p.id = %d and ol.custom_package="S")',$this->escape_string($value));
				}
				break;
			case "service_type":
				if ($value != "") {
					$srch[$key] = sprintf("service_type = '%s'",$value);
				}
				break;
			case "completed":
				if ($value != "") {
					$srch[$key] = sprintf("completed = %d",$value);
				}
			}
		}
		$_SESSION['formData']['getDriverSearchForm'] = $_POST;
		$where = implode(" and ",$srch);
		$packages = $this->fetchAll(sprintf("select c.*, curdate() as td, date(scheduled_date) as check_date, a.line1, a.line2, a.city, a.postalcode, a.phone1, a.phone2, p1.code deliveryCode, p1.name as deliveryName from custom_delivery c, addresses a, product p1, order_lines ol, orders o where %s and a.ownertype='order' and a.ownerid = c.order_id and a.addresstype = IF(c.service_type='P',%d,%d) and p1.id = ol.product_id and ol.custom_package = 'S' and ol.order_id = c.order_id and o.id = ol.order_id and order_status & %d = %d order by %s %s",$where,ADDRESS_PICKUP,ADDRESS_DELIVERY, STATUS_PROCESSING, STATUS_PROCESSING, $_POST["sortby"], $_POST["sortorder"]));

		$d_id = $_POST["driver_id"];
		$driver = $this->fetchSingle(sprintf("select d.id as driver_id, m.*, a.line1, a.line2, a.city, a.phone1, a.phone2, a.fax, a.email from drivers d, members m left join addresses a on a.ownertype = 'member' and a.ownerid = m.id where d.id = %d and m.id = d.member_id limit 1",$d_id));
		$outer->addData($driver);

		$inner = new Forms();
		$inner->init($this->getTemplate("getDriverPackage"));
		$flds = $flds = $inner->buildForm($this->getFields("getDriverPackage"));
		$result = array();
		$curr_list = array();
		foreach($packages as $key=>$value) {
			$curr_list[] = $value["id"];
		}
		$rt = $this->fetchSingle(sprintf("select * from custom_delivery_route where driver_id = %d and delivery_date = '%s'",$rt_d_id,$rt_dt));
		if (is_array($rt) && count($rt) > 0) {
			if (strpos($rt["delivery_ids"], sprintf("|%s|",implode("|",$curr_list)), 0) !== false) {
				if ($rt["optimized"])
					$outer->addFormSuccess("This route has already been optimized");
				else
					$outer->addFormSuccess("This route has already been calculated but not optimized");
			}
			else {
			 	$outer->addFormError("The deliveries have changed since this was routed");
			}
		}
		else $outer->addFormError("This route has not been optimized yet");

		foreach($packages as $key=>$value) {
			$inner->reset();
			if (is_array($rt)) {
				$value["routed"] = strpos($rt["delivery_ids"],sprintf("|%d|",$value["id"])) !== false;
				$value["optimized"] = $rt["optimized"];
			}
			$inner->addData($value);
			$result[] = $inner->show();
		}

		$outer->addTag("packages",implode("",$result),false);
		$outer->addData($_POST);
		if ($this->isAjax())
			return $this->ajaxReturn(array('status'=>true,'html'=>$outer->show()));
		else
		 	return $outer->show();
	}

	function mapIt() {
		$d_id = array_key_exists("driver_id",$_REQUEST) ? $_REQUEST["driver_id"] : 0;
		$dt = array_key_exists("scheduled_date",$_REQUEST) ? date("Y-m-d",strtotime($_REQUEST["scheduled_date"])) : date("Y-m-d");
		$driver = $this->fetchSingle(sprintf("select m.*, a.* from drivers d, members m left join addresses a on a.ownertype = 'member' and a.ownerid = m.id where d.id = %d and m.id = d.member_id limit 1",$d_id));
		$outer = new Forms();
		$outer->init($this->getTemplate("mapIt"));
		$outer->addData($driver);
		$outer->validate();
		$flds = $this->getFields("mapIt");
		if ($route = $this->fetchSingle(sprintf("select * from custom_delivery_route where driver_id = %d and delivery_date = '%s'", $d_id, $dt))) {
			$addr = explode("|",$route["delivery_ids"]);
			$this->logMessage(__FUNCTION__,sprintf("route addresses [%s]", print_r($addr,true)), 3);
			$inner = new Forms();
			$inner->init($this->getTemplate(__FUNCTION__."Address"));
			$flds = $inner->buildForm($flds);
			$result = array();
			$addresses = array();
			foreach($addr as  $key=>$value) {
				if ($value > 0) {
					if ($address = $this->fetchSingle(sprintf("select a.*, DATEDIFF(cd.scheduled_date,'%s') as daysToDeliver, p.custom_same_day from addresses a, custom_delivery cd, product p, order_lines ol where cd.id = %d and a.ownerid = cd.order_id and a.ownertype='order' and addresstype=if(cd.service_type='P',%d,%d) and ol.order_id = a.ownerid and ol.custom_package='S' and p.id = ol.product_id",$dt, $value, ADDRESS_PICKUP, ADDRESS_DELIVERY))) {
						$addresses[$value] = $address;
						$tmp = array("address"=> Address::formatData($address),"sequence"=>$key);
						$inner->reset();
						$inner->addData($tmp);
						$result[] = $inner->show();
					}
					else {
						$this->logMessage(__FUNCTION__,sprintf("^^^ found a bad order from %d", $value),1);
						$o_id = $this->fetchScalar(sprintf("select order_id from custom_delivery where id = %d",$value));
						$outer->addFormError(sprintf("Order #%d has no service",$o_id));
					}
				}
			}
			$outer->addTag("count",count($addr));
			$outer->addTag("addresses",implode("",$result),false);
		}
		if ($route = $this->fetchSingle(sprintf("select * from custom_delivery_route where driver_id = %d and delivery_date = '%s'",$d_id,$dt))) {
			if (!$route["optimized"]) 
				$outer->addFormError("This route has NOT been optimized");
			else {
			 	$outer->addFormSuccess("This route has been optimized");
			}
			$r = json_decode($route["route"],true);
			$inner = new Forms();
			$inner->init($this->getTemplate(__FUNCTION__."Route"));
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
			$this->logMessage(__FUNCTION__,sprintf("polyline [%s]",print_r($polyline,true)),1);
			$inner->init($this->getTemplate(__FUNCTION__."Directions"));
			$directions = array();
			foreach($r["directions"][0]["features"] as $key=>$dir) {
				if ($key > 0) {
					$inner->reset();
					$dir["key"] = $key;
					$st = -1;
					if ($dir["attributes"]["maneuverType"] == "esriDMTStop" && ($st = strpos($dir["attributes"]["text"],"Arrive at ")) !== FALSE) {
						$ed = strpos($dir["attributes"]["text"],",",$st+1);
						$lt = 10;
					}
					else
						if ($dir["attributes"]["maneuverType"] == "esriDMTDepart" && ($st = strpos($dir["attributes"]["text"],"Depart ")) !== FALSE) {
							$ed = strpos($dir["attributes"]["text"],",",$st+1);
							$lt = 7;
						}
						else {
							if ($dir["attributes"]["maneuverType"] == "esriDMTStop" && ($st = strpos($dir["attributes"]["text"],"Finish at ")) !== FALSE) {
								$ed = strpos($dir["attributes"]["text"],",",$st+1);
								$lt = 10;
							}
						}
					if ($st >= 0) {
						if ($ed > 0)
							$id = substr($dir["attributes"]["text"],$st+$lt,$ed-$st-$lt);
						else $id = substr($dir["attributes"]["text"],$st+$lt);
						if ($id == "" || !array_key_exists($id,$addresses)) {
							$this->logMessage(__FUNCTION__,sprintf("invalid id [%s] found in [%s] [%s] [%s]",$id,$dir["attributes"]["text"],$st,$ed),1);
						}
						else {
							$this->logMessage(__FUNCTION__,sprintf("replace [%s] with [%s] in [%s]",$id,$addresses[$id]["line1"],$dir["attributes"]["text"]),1);
							$dir["attributes"]["text"] = str_replace($id, $addresses[$id]["line1"], $dir["attributes"]["text"]);
						}
					}
					$this->logMessage(__FUNCTION__,sprintf("dir [%s]",print_r($dir,true)),1);
					$inner->addData($dir);
					$directions[] = $inner->show();
				}
			}
			$this->logMessage(__FUNCTION__,sprintf("directions [%s] from [%s]",print_r($directions,true),print_r($inner,true)),1);
			$outer->addTag("route",implode("\n",$polyline));
			$outer->addTag("directions",implode("\n",$directions),false);
		}
		if ($this->isAjax())
			return $this->ajaxReturn(array('status'=>true,'html'=>$outer->show()));
		else
		 	return $outer->show();
	}

	function editPackage() {
		$p_id = array_key_exists("p_id",$_REQUEST) ? $_REQUEST["p_id"] : 0;
		$package = $this->fetchSingle(sprintf("select cd.*, o.custom_signature_required from custom_delivery cd, orders o where cd.id = %d and o.id = cd.order_id",$p_id));
		$package["address"] = $this->fetchSingle(sprintf("select * from addresses where ownerid = %d and ownertype = 'order' and addresstype=%d",$package["order_id"],$package["service_type"] == "P" ? ADDRESS_PICKUP : ADDRESS_DELIVERY));
		if ($package["service_type"] == "P") {
			$tmp = Address::formatData($this->fetchSingle(sprintf("select * from addresses where ownerid = %d and ownertype = 'order' and addresstype=%d",$package["order_id"], ADDRESS_DELIVERY)));
			$package["delivery"] = array();
			foreach($tmp as $key=>$value) {
				$package["delivery"]["d_".$key] = $value;
			}
		}
		$package["mgmt"] = $this->fetchSingle(sprintf("select * from members m, orders o where o.id = %d and m.id = o.login_id", $package["order_id"] ));
		$package["account"] = $this->fetchSingle(sprintf("select * from members m, orders o where o.id = %d and m.id = o.member_id", $package["order_id"] ));
		$package["order"] = $this->fetchSingle(sprintf("select * from orders where id = %d", $package["order_id"] ));
		$outer = new Forms();
		$outer->init($this->getTemplate("editPackage"));
		$flds = $outer->buildForm($this->getFields("editPackage"));
		//$package["messaging"] = $this->getMessages($package["id"]);
		$outer->addData($package);
		if (count($_POST) > 0 && array_key_exists("editPackage",$_POST)) {
			$outer->addData($_POST);
			$valid = $outer->validate();
			if ($valid) {
				$upd = array();
				$address = array();
				foreach($flds as $key=>$fld) {
					if (!(array_key_exists('database',$fld) && $fld['database'] == false)) {
						if (strpos($fld["name"],"address[") === FALSE)
							$upd[$key] = $outer->getData($fld["name"]);
						else {
						 	$address[$key] = $outer->getData($fld["name"]);
						}
					}
				}
				if ($upd["driver_id"] != $package["driver_id"]) {
					$upd["ack_status"] = ACK_REQUEST;
					if ($upd["dispatch_message"] == $package["dispatch_message"]) $upd["dispatch_message"] = "Reassigned by dispatcher";
					$this->sendSMS($package["id"]);
				}
				if ($upd["ack_status"] == ACK_REQUEST) $upd["ack_requested"] = date(DATE_ATOM);
				$stmt = $this->prepare(sprintf("update custom_delivery set %s=? where id = %d",implode("=?, ",array_keys($upd)),$package["id"]));
				$stmt->bindParams(array_merge(array(str_repeat("s",count($upd))),array_values($upd)));
				$valid = $valid & $stmt->execute();
				if (array_key_exists("geocode",$_REQUEST) && $_REQUEST["geocode"] == 1) {
					$lat = 0;
					$long = 0;
					if ($this->geoCode($address,$lat,$long)) {
						$address["latitude"] = $lat;
						$address["longitude"] = $long;
					}
					else {
						$valid = false;
						$outer->addFormError("The address did not geocode");
					}
				}
				$stmt = $this->prepare(sprintf("update addresses set %s=? where id = %d",implode("=?, ",array_keys($address)),$package["address"]["id"]));
				$stmt->bindParams(array_merge(array(str_repeat("s",count($address))),array_values($address)));
				$valid = $valid & $stmt->execute();
				if ($valid) {
					$this->recalcDowntown($package["order_id"]);
					$this->calc_driver_allocations($package["order_id"]);
					$this->calculateCommissions($package["order_id"]);
					$outer->addFormSuccess("Record Updated");
					$outer->init($this->getTemplate("editPackageSuccess"));
				}
			}
		}
		if ($this->isAjax())
			return $this->ajaxReturn(array('status'=>true,'html'=>$outer->show()));
		else
			return $outer->show();
	}

	function sameDays($fromMain = false) {
		$outer = new Forms();
		$outer->init($this->getTemplate("sameDays"));
		$flds = $outer->buildForm($this->getFields("sameDays"));
		$outer->addTag('heading',$this->getHeader(),false);
		$inner = new Forms();
		$inner->init($this->getTemplate("sameDaysRow"));
		$flds = $inner->buildForm($this->getFields("sameDaysRow"));
		if (array_key_exists(__FUNCTION__, $_REQUEST) && count($_POST) == 0) {
		 	$_POST = array_merge(array("pager"=>$this->m_perrow,"pagenum"=>1, "sortby"=>"scheduled_date", "sortorder"=>"asc"), $_REQUEST);
		}
		else
			if (count($_POST) == 0 || !(array_key_exists("sameDays",$_POST))) {
				if (array_key_exists('formData',$_SESSION) && array_key_exists('sameDays', $_SESSION['formData']))
					$_POST = $_SESSION['formData']['sameDays'];
				else
					$_POST = array("scheduled_date"=>date("Y-m-d"),"opt_scheduled_date"=>"<=","pagenum"=>1,"sortby"=>"scheduled_date","sortorder"=>"asc","pager"=>$this->m_perrow,"sameDays"=>1,"third_party"=>0,"opt_delivered"=>0);
			}
		$outer->addData($_POST);
		$srch = array("opt_delivered"=>"completed = 0");
		$x2 = "x2";
		$_POST["custom_same_day"] = "1";
		foreach($_POST as $key=>$value) {
			switch($key) {
			case "opt_scheduled_date":
				if ($value != "" && $outer->getData("scheduled_date") != "1969-12-31")
					$srch[$key] = sprintf("date_format(scheduled_date,'%%Y-%%m-%%d') %s '%s'", $_POST["opt_scheduled_date"], date("Y-m-d",strtotime($_POST["scheduled_date"])));
				break;
			case "driver_id":
				if ($value != "") $srch[$key] = sprintf("driver_id = %d",$value);
				break;
			case "delivery_type":
				if (array_key_exists($key,$_POST)) {
					$value = $_POST[$key];
					if (strlen($value) > 0)
						$srch[$key] = sprintf(' cd.order_id in (select ol.order_id from order_lines ol, product p where p.id = ol.product_id and p.id = %d and ol.custom_package="S")',$this->escape_string($value));
				}
				break;
			case "service_type":
				if ($value != "") {
					$srch[$key] = sprintf("service_type = %d",$value);
					$x2 = "";
				}
				break;
			case "opt_delivered":
				if (strlen($value) > 0) {
					$srch[$key] = sprintf("completed = %d", $outer->getData("opt_delivered"));
				}
				break;
			case "custom_same_day":
				if ($value != "") {
					$srch[$key] = sprintf("cd.order_id in (select order_id from order_lines ol, product p where ol.custom_package='S' and p.id = ol.product_id and p.custom_same_day = %d)",$value);
				}
				break;
			case "third_party":
				if ($value !="") {
					$srch[$key] = sprintf("(cd.driver_id = 0 or d.third_party = %d)", $outer->getData($key));
				}
				break;
			case "order_id":
				if (($value = $outer->getData($key)) > 0)
					$srch[$key] = sprintf("cd.order_id = %d", $value );
				break;
			case "address":
				if (strlen($outer->getData($key)) > 0)
					$srch[$key] = sprintf('(a.line1 like "%%%1$s%%" or a.city like "%%%1$s%%" or a.postalcode like "%%%1$s%%")',$outer->getData($key));
				break;
			default:
				break;
			}
		}
		$srch["not_cancelled"] = sprintf("o.order_status & %d = 0", STATUS_CANCELLED);
		$srch["not_on_demand"] = sprintf("o.order_status & %d = 0", STATUS_ON_DEMAND);
		$srch["not_deleted"] = "o.deleted = 0";
		$srch["o"] = "o.id = cd.order_id";
		$srch["alink_1"] = "a.ownerid = o.id";
		$srch["alink_2"] = "a.ownertype = 'order'";
		$_SESSION['formData']['sameDays'] = $_POST;
		$where = implode(" and ",array_values($srch));
		$sql = sprintf("select count(distinct(order_id)) as order_id from orders o, addresses a, custom_delivery cd left join drivers d on d.id = cd.driver_id where %s", $where );
		$count = $this->fetchScalar($sql);
		$perPage = $_POST["pager"];
		$pageNum = $_POST["pagenum"];
		$pagination = $this->pagination($count, $perPage, $pageNum, 
			array('prev'=>$this->M_DIR.'forms/paginationPrev.html','next'=>$this->M_DIR.'forms/paginationNext.html',
			'pages'=>$this->M_DIR.'forms/paginationPage.html', 'wrapper'=>$this->M_DIR.'forms/paginationWrapper.html'),
			array('url'=>'/modit/ajax/sameDays/dispatch','destination'=>'middleContent'));
		$outer->addTag('pagination',$pagination,false);
		$start = ($pageNum-1)*$perPage;
		$sortby = $_POST["sortby"];
		$sortorder = $_POST["sortorder"];
		if ($sortorder == "asc" && $sortby == "scheduled_date") {
			$sortby = "IF(service_type='P',scheduled_date,'')";
		}
		$sql = sprintf("select distinct(cd.order_id) from orders o, addresses a, custom_delivery cd left join drivers d on d.id = cd.driver_id, product p, order_lines ol where p.id = ol.product_id and p.custom_same_day = 1 and ol.order_id = cd.order_id and ol.custom_package = 'S' and %s order by %s %s limit %d,%d", $where, $sortby, $sortorder, $perPage * ($pageNum-1), $perPage);
		$records = $this->fetchScalarAll($sql);
		$result = array();
		foreach($records as $key=>$value) {
			if ($x2 == "x2" || ($x2==""  && $_POST["service_type"] == "P")) {
				$p = $this->fetchSingle(sprintf("select c.*, curdate() as td, a.id as a_id, p1.code as deliveryCode, p1.name as deliveryName, concat(m.company, '-', m.lastname,', ',m.firstname) as drivername, o.custom_placed_by from orders o, custom_delivery c left join drivers d on d.id = c.driver_id left join members m on m.id = d.member_id, addresses a, product p1, order_lines ol where c.order_id = %d and service_type = 'P' and a.ownerid = c.order_id and a.addresstype = %d and p1.id = ol.product_id and ol.custom_package = 'S' and ol.order_id = c.order_id and o.id = c.order_id ",$value,ADDRESS_PICKUP));
				$p["address"] = Address::formatData($this->fetchSingle(sprintf("select * from addresses where id = %d",$p["a_id"])));
			}
			else $p = array();
			if ($x2 == "x2" || ($x2==""  && $_POST["service_type"] == "D")) {
				$d = $this->fetchSingle(sprintf("select c.*, curdate() as td, a.id as a_id, p1.code as deliveryCode, p1.name as deliveryName, concat(m.company, '-', m.lastname,', ',m.firstname) as drivername from custom_delivery c left join drivers d on d.id = c.driver_id left join members m on m.id = d.member_id, addresses a, product p1, order_lines ol where c.order_id = %d and service_type = 'D' and a.ownerid = c.order_id and a.addresstype = %d and p1.id = ol.product_id and ol.custom_package = 'S' and ol.order_id = c.order_id ",$value,ADDRESS_DELIVERY));
				$d["address"] = Address::formatData($this->fetchSingle(sprintf("select * from addresses where id = %d",$d["a_id"])));
			}
			else $d = array();
			//$inner->reset();
			$inner->addTag("service_type",array_key_exists("service_type",$_POST) ? $_POST["service_type"] : "");
			$inner->addTag("services", implode(", ", $this->fetchScalarAll(sprintf("select p.name from product p, order_lines ol where ol.order_id = %d and ol.custom_package = 'A' and ol.deleted = 0 and p.id = ol.product_id and p.custom_special_requirement = 1", $value))));

			$inner->addData(array("pickup"=>$p,"delivery"=>$d));
			$result[] = $inner->show();
		}
		$outer->addTag("x2",$x2);
		$outer->addTag("deliveries",implode("",$result),false);
		if ($this->isAjax())
			return $this->ajaxReturn(array('status'=>true,'html'=>$outer->show()));
		else
			return $this->show($outer->show());
	}

	function assignSameday() {
		$o_id = array_key_exists("o_id",$_POST) ? $_POST["o_id"] : 0;
		$d_id = array_key_exists("d_id",$_POST) ? $_POST["d_id"] : 0;

		$curr = $this->fetchSingle(sprintf("select * from custom_delivery where order_id = %d and service_type='P'", $o_id));
		$this->execute(sprintf("update custom_delivery set driver_id = %d, driver_sequence = 9999, ack_status = %d, ack_requested = '%s', dispatch_message = '%s', driver_message='' where order_id = %d", $d_id, ACK_REQUEST, date(DATE_ATOM), 'Assigned by dispatch', $o_id));
		$this->resequence($d_id, date("Y-m-d", strtotime($curr["scheduled_date"])));

		$this->sendSMS($curr["id"]);

		$c = new Common();
		$c->recalcDowntown($o_id);
		$this->calc_driver_allocations($o_id);
		$this->calculateCommissions($o_id);
		$form = new Forms();
		$form->init($this->getTemplate("assignSameday"));
		return $this->ajaxReturn(array("status"=>true,"html"=>$form->show()));
	}

	function exportRoute() {
		$outer = new Forms();
		$outer->init($this->getTemplate("exportRoute"));
		$d = $this->fetchSingle(sprintf("select m.* from members m, drivers d where d.id = %d and m.id = d.member_id",$_REQUEST["d_id"]));
		$p = $_REQUEST["r_id"];
		$route = $this->fetchAll(sprintf("select a.*, c.id as route_id, p.province_code, ct.country as country from custom_delivery c, addresses a left join provinces p on p.id = a.province_id left join countries ct on ct.id = a.country_id where c.id in (%s) and a.ownerid = c.order_id and a.addresstype = IF(service_type='P',%d,%d) order by instr('%s',concat('|',c.id,'|'))",implode(",",$p),ADDRESS_PICKUP,ADDRESS_DELIVERY,sprintf("|%s|",implode("|",$p))));
		$inner = new Forms();
		$inner->init($this->getTemplate("exportRoutePackage"));
		$result = array();
		foreach($route as $key=>$r) {
			$inner->reset();
			$inner->addData($r);
			$result[] = $inner->show();
		}
		$outer->addTag("route",implode("\r\n",$result),false);
		$tmp = $outer->show();
		ob_end_clean();
		header('Content-Type: application/csv');
		header(sprintf('Content-Disposition: attachment; filename=route-%s-%s.csv',$d["lastname"],$d["firstname"]));
		header("Content-Length: ".strlen($tmp));
		header('Pragma: no-cache');
		echo $tmp;
		exit();
	}

	function optimizeGoogle($request = null) {
		$outer = new Forms();
		$outer->init($this->getTemplate("optimize"));
		if (is_null($request)) $request = $_POST;
		$srch = array();
		foreach($request as $key=>$value) {
			switch($key) {
			case "opt_scheduled_date":
				$srch[] = sprintf("date_format(scheduled_date,'%%Y-%%m-%%d') %s '%s'", $request["opt_scheduled_date"], date("Y-m-d",strtotime($request["scheduled_date"])));
				break;
			case "d_id":
			case "driver_id":
				if ($value != "") $srch[] = sprintf("driver_id = %d",$value);
				break;
			case "service_type":
				if ($value != "") {
					$srch[] = sprintf("service_type = '%s'",$value);
				}
				break;
			}
		}
		$recs = $this->fetchAll(sprintf("select * from custom_delivery c where %s order by driver_sequence", implode(" and ",$srch)));
		if (count($recs) > 23) {
			$this->addError("Too many waypoints (maximum of 23 allowed)");
			return $this->ajaxReturn(array('status'=>false,'html'=>$outer->show()));
		}
		//$st_address = $this->getConfigVar("mailing-address");
		$route = array();
		$inner = new Forms();
		$inner->init($this->getTemplate("optimizeWP"));
		$sequence = array();
		foreach($recs as $key=>$wp) {
			$address = $this->fetchSingle(sprintf("select * from addresses where ownerid = %d and ownertype='order' and addresstype=%d",$wp["order_id"],$wp["service_type"]=="P"?ADDRESS_PICKUP:ADDRESS_DELIVERY));
			$wp["address"] = Address::formatData($address);
			if ($key == 0) 
				$st_address = $wp["address"];
			else
				if ($key == count($recs)-1) 
					$e_address = $wp["address"];
				else {
					$inner->reset();
					$inner->addData($wp);
					$route[] = urlencode($inner->show());
				}
			$sequence[$key]["id"] = $wp["id"];
			$sequence[$key]["address"] = $wp["address"];
		}
		$outer->addData(array("start"=>$st_address,"end"=>$e_address,"wp"=>implode("|",$route)));
		$rt = $outer->show();
		$s = new Snoopy();
		$s->host = $GLOBALS["optimizer"]["url"];
		$s->port = $GLOBALS["optimizer"]["port"];
		$s->curl_path = $GLOBALS["optimizer"]['curl_path'];
		$s->fetch($rt);
		$result = json_decode($s->results,true);
		if ($result["status"] == "OK" || $result["status"] == "200") {
			$this->logMessage(__FUNCTION__,sprintf("snoopy result is [%s] from [%s] [%s]",print_r($result,true),$key,print_r($s,true)),3);
			$this->logMessage(__FUNCTION__,sprintf("waypoints are [%s] from addresses [%s]",print_r($result["routes"][0]["waypoint_order"],true),print_r($sequence,true)),1);
			//
			//	Start/End points are not touched here - first wp = 20 in the sequence
			//	$sequence[0] = start which is not in the returned waypoints
			//
			for($idx = 0; $idx < count($result["routes"][0]["waypoint_order"]); $idx++) {
				$this->execute(sprintf("update custom_delivery set driver_sequence = %d where id = %d",($idx+2)*10,$sequence[$result["routes"][0]["waypoint_order"][$idx]+1]["id"]));
				$this->logMessage(__FUNCTION__,sprintf("result [%d] is address [%s]",$idx,$sequence[$idx]["address"]["line1"]),1);
			}
		}
		else {
			$this->logMessage(__FUNCTION__,sprintf("routing failed [%s] url [%s] results [%s]",$result["status"],$rt,print_r($s,true)),1,true);
		}
		return $this->ajaxReturn(array("status"=>true,"html"=>$outer->show()));
	}

	function calendar() {
		$outer = new Forms();
		$outer->init($this->getTemplate("calendar"));
		$flds = $outer->buildForm($this->getFields("calendar"));
		$sd = array_key_exists("sd",$_REQUEST) ? $_REQUEST["sd"] : date("Y-m-01");
		if (array_key_exists("calendar",$_REQUEST) && $_REQUEST["calendar"] == 1) {
			if (array_key_exists("moveType",$_REQUEST) && $_REQUEST["moveType"] != "") {
				switch($_REQUEST["moveType"]) {
					case "+M":
						$sd = date("Y-m-01",strtotime($sd." + 1 month"));
						break;
					case "-M":
						$sd = date("Y-m-01",strtotime($sd." - 1 month"));
						break;
					case "+Y":
						$sd = date("Y-m-01",strtotime($sd." + 1 year"));
						break;
					case "-Y":
						$sd = date("Y-m-01",strtotime($sd." - 1 year"));
						break;
					case "T":
						$sd = date("Y-m-01");
						break;
					default:
						break;
				}
				$_REQUEST["sd"] = $sd;
			}
			$outer->addData($_REQUEST);
		}
		$ed = date("Y-m-d",strtotime(sprintf("%s + 1 month",$sd)));
		$tmp = new Forms();
		$tmp->init($this->getTemplate('headerByMonth'));
		$outer->addTag('headerOptions',$tmp->show(),false);

		$output = array();
		$week = array();
		$wk = date('w',strtotime($sd));
		$eventForm = new Forms();
		$eventForm->init($this->getTemplate('monthEvent'));
		$eventFields = $eventForm->buildForm($this->getFields('monthEvent'));
		$dayForm = new Forms();
		$dayForm->init($this->getTemplate('monthDay'));
		$dayFields = $dayForm->buildForm($this->getFields('monthDay'));
		$weekForm = new Forms();
		$weekForm->init($this->getTemplate('monthWeek'));
		$weekFields = $weekForm->buildForm($this->getFields('monthWeek'));
		$outer->addTag("startDate",date("M-Y",strtotime($sd)));
		for ($i = 0; $i < $wk; $i++) {
			//
			//	build empty days prior to the start of the month
			//
			$week[] = '<td></td>';
		}
		$wk = strftime('%U',strtotime($sd));
		while ($sd < $ed) {
			$currWk = strftime('%U',strtotime($sd));
			if ($currWk != $wk) {
				$this->logMessage(__FUNCTION__,sprintf('new week week [%s] currWk [%s] wk [%s] sd [%s] ed [%s]',print_r($week,true),$currWk,$wk,$sd,$ed),2);
				$weekForm->addTag('days',implode('',$week),false);
				$output[] = $weekForm->show();
				$wk = $currWk;
				$week = array();
			}
			$day = array();
			$dayForm->reset();
			$sql = sprintf('select * from office_schedule where office_date = "%s"',$sd);
			if ($events = $this->fetchSingle($sql))
				$dayForm->addData($this->formatDay($events));
			$dayForm->addTag('class',$sd == date('Y-m-d') ? 'today':'');
			$dayForm->addTag('events',implode('',$day),false);
			$dayForm->addTag('day',date('d',strtotime($sd)));
			$dayForm->addTag('date',$sd);
			if (is_array($events) && count($events) > 0)
				$dayForm->addTag('eventCount',sprintf('%d event%s',count($events),count($events) > 1?'s':''));
			else 
				$dayForm->addTag('eventCount','');
			$week[] = $dayForm->show();
			$sd = date('Y-m-d',strtotime(sprintf('%s + 1 day',$sd)));
		}
		$weekForm->addTag('days',implode('',$week),false);
		$output[] = $weekForm->show();
		$monthForm = new Forms();
		$monthForm->init($this->getTemplate('monthEvents'));
		$monthFields = $monthForm->buildForm($this->getFields('monthEvents'));
		$monthForm->addTag('weeks',implode('',$output),false);
		$this->logMessage(__FUNCTION__,sprintf('return form [%s]',print_r($monthForm,true)),1);
		$this->logMessage(__FUNCTION__,sprintf("outer is [%s]",print_r($outer,true)),1);
		$outer->addTag("month",$monthForm->show(),false);
		if ($this->isAjax())
			return $this->ajaxReturn(array("status"=>true,"html"=>$outer->show()));
		else return $this->show($outer->show());
	}

	function editDate() {
		$outer = new Forms();
		$outer->init($this->getTemplate(__FUNCTION__));
		$flds = $outer->buildForm($this->getFields(__FUNCTION__));
		$dt = array_key_exists("dt",$_REQUEST) ? $_REQUEST["dt"] : date("Y-m-d");
		$id = array_key_exists("id",$_REQUEST) ? $_REQUEST["id"] : 0;
		if (!($rec = $this->fetchSingle(sprintf("select * from office_schedule where id = %d",$id)))) {
			$rec = array("id"=>0,"office_date"=>$dt);
		}
		$outer->addData($rec);
		if (count($_POST) > 0 && array_key_exists(__FUNCTION__,$_POST)) {
			$outer->addData($_POST);
			$valid = $outer->validate();
			if ($valid) {
/*
				if ($outer->getData("closed") == 0 && $outer->getData("close_time") == "") {
					$valid = false;
					$outer->addFormError("You must select either a time or closed for the day");
				}
*/
				if ($valid) {
					if ($outer->getData("delete") == 1) {
						$this->execute(sprintf("delete from office_schedule where id = %d",$outer->getData("id")));
					}
					else {
						$upd = array();
						foreach($flds as $key=>$fld) {
							if (!(array_key_exists('database',$fld) && $fld['database'] == false))
								$upd[$key] = $outer->getData($fld["name"]);
						}
						if ($_POST["id"] == 0)
							$stmt = $this->prepare(sprintf("insert into office_schedule(%s) values(%s?)",implode(",",array_keys($upd)),str_repeat("?, ",count($upd)-1)));
						else
							$stmt = $this->prepare(sprintf("update office_schedule set %s=? where id = %d",implode("=?, ",array_keys($upd)),$rec["id"]));
						$stmt->bindParams(array_merge(array(str_repeat("s",count($upd))),array_values($upd)));
						$valid = $valid && $stmt->execute();
					}
					if ($valid)
						$outer->init($this->getTemplate(__FUNCTION__."Success"));
				}
			}
		}
		$outer->addTag("date",date("d-M-Y",strtotime($dt)));
		return $this->ajaxReturn(array('status'=>true,'html'=>$outer->show()));
	}

	function formatDay($data) {
		if ($data["close_time"] != "00:00:00") $data["close_time_display"] = date("h:i A",strtotime($data["close_time"]));
		return $data;
	}

	function optimize($request = null) {
		$parms = $GLOBALS["arcgis"];
		if (!array_key_exists("arcgis",$_SESSION["administrator"])) {
			$this->getToken($parms);
		} else {
			$this->logMessage(__FUNCTION__,sprintf("token expiration [%s] vs [%s]",$_SESSION["administrator"]["arcgis"]["expiration"],strtotime("now")),1);
			if ($_SESSION["administrator"]["arcgis"]["expiration"] < strtotime("now")) {
				$this->getToken($parms);
			}
		}
		$outer = new Forms();
		$outer->init($this->getTemplate(__FUNCTION__));
		$route = array("delivery_ids"=>array());
		if (is_null($request)) $request = $_POST;
		$srch = array(
			"a"=>"actual_date='0000-00-00 00:00:00'",
			"b"=>sprintf("order_status & %d = %d", STATUS_PROCESSING, STATUS_PROCESSING),
			"c"=>"o.id = c.order_id"
		);
		foreach($request as $key=>$value) {
			switch($key) {
			case "completed":
				if ($value != "") {
					$srch[$key] = sprintf("completed = %d",$value);
				}
				break;
			case "scheduled_date":
				$srch[] = sprintf("date_format(scheduled_date,'%%Y-%%m-%%d') <= '%s'", date("Y-m-d",strtotime($value)));
				$route["delivery_date"] = date("Y-m-d",strtotime($value));
				break;
			case "d_id":
			case "driver_id":
				if ($value != "") $srch[] = sprintf("driver_id = %d",$value);
				$route["driver_id"] = $value;
				break;
			case "service_type":
				if ($value != "") {
					$srch[] = sprintf("service_type = '%s'",$value);
				}
				break;
			}
		}
		$recs = $this->fetchAll(sprintf("select c.* from custom_delivery c, orders o where %s order by driver_sequence", implode(" and ",$srch)));
		$routes = array();
		$inner = new Forms();
		$inner->init($this->getTemplate(__FUNCTION__."WP"));
		$sequence = array();
		$valid = true;
		$errors = array();
		foreach($recs as $key=>$wp) {
			$address = $this->fetchSingle(sprintf("select * from addresses where ownerid = %d and ownertype='order' and addresstype=%d",$wp["order_id"],$wp["service_type"]=="P"?ADDRESS_PICKUP:ADDRESS_DELIVERY));
			if (abs($address["latitude"]) <.1 || abs($address["longitude"]) < .1) {
				$lat = 0;
				$lng = 0;
				if (!$this->geoCode($address,$lat,$lng)) {
					$errors[] = sprintf("No GPS for Address [%s %s %s]",$address["line1"],$address["city"],$address["postalcode"]);
					$valid = false;
				}
				else {
					$this->execute(sprintf("update addresses set latitude=%f, longitude=%f where id = %d",$lat,$lng,$address["id"]));
					$address["latitude"] = $lat;
					$address["longitude"] = $lng;
				}
			}
			if (abs($address["latitude"]) >.1 || abs($address["longitude"]) > .1) {
				$wp["address"] = Address::formatData($address);
				$inner->reset();
				$inner->addData($wp);
				$routes[] = urlencode($inner->show());
				$sequence[$key]["id"] = $wp["id"];
				$sequence[$key]["address"] = $wp["address"];
			}
		}
		$outer->addData(array("parms"=>$GLOBALS["optimizer"],"wp"=>implode(",",$routes)));
		$rt = $outer->show();
		//
		//	optimized route vs current order
		//
		if (array_key_exists("optimize",$_POST) && $_POST["optimize"] == 0) $rt = str_replace("&findBestSequence=true","",$rt);
		$this->logMessage(__FUNCTION__,sprintf("request is [%s]",$rt),3);
		if (!$valid) {
			foreach($errors as $key=>$value) {
				$this->addError($value);
			}
			return $this->ajaxReturn(array("html"=>"","status"=>false));
		}
		$s = new Snoopy();
		$s->curl_path = $GLOBALS['curl_path'];
		$s->fetch($rt);
		$r = $s->results;
		$this->logMessage(__FUNCTION__,sprintf('snoopy [%s] routes [%s] rt [%s]',
				print_r($s,true), print_r($routes,true), $rt),2);

		$route["route"] = $r;
		$tmp = json_decode($r,true);
		$this->logMessage(__FUNCTION__,sprintf("result is [%s]",print_r($tmp,true)),1);
		if (array_key_exists("stops",$tmp) && array_key_exists("features",$tmp["stops"]) && is_array($tmp["stops"]["features"])) {
			foreach($tmp["stops"]["features"] as $key=>$stop) {
				$this->execute(sprintf('update custom_delivery set driver_sequence = %d where id = %d',10*$stop["attributes"]["Sequence"],$stop["attributes"]["Name"]));
				$route["delivery_ids"][$stop["attributes"]["Name"]] = $stop["attributes"]["Sequence"];
			}
			$this->logMessage(__FUNCTION__,sprintf("route [%s]",print_r($route["delivery_ids"],true)),1);
			asort($route["delivery_ids"],SORT_NUMERIC);
			$this->logMessage(__FUNCTION__,sprintf("route [%s]",print_r($route["delivery_ids"],true)),1);
			$this->logMessage(__FUNCTION__,sprintf("route [%s]",print_r($route,true)),1);
			$route["delivery_ids"] = sprintf("|%s|",implode("|",array_keys($route["delivery_ids"])));
			$route["optimized"] = $_POST["optimize"];
			$stmt = $this->prepare(sprintf("replace into custom_delivery_route(%s) values(%s?)",implode(", ",array_keys($route)),str_repeat("?, ",count($route)-1)));
			$stmt->bindParams(array_merge(array(str_repeat("s",count($route))),array_values($route)));
			$valid = $valid & $stmt->execute();
		}
		else {
			$valid = false;
			$tmp = json_decode($route["route"],true);
			$this->logMessage(__FUNCTION__,sprintf("invalid arcgis response [%s] from [%s] [%s]",print_r($route["route"],true), print_r($tmp,true), $rt),1,true);
			$this->addError("The routing request did not complete successfully");
			if ($this->checkArray("error:message",$tmp)) $this->addError(sprintf("Message: %s", $tmp["error"]["message"]));
		}
		return $this->ajaxReturn(array("html"=>"","status"=>$valid));
		/*//Sonar Cloud - PHP Major bug - All code should be reachable
		$s = new Snoopy();
		$s->host = $GLOBALS["optimizer"]["url"];
		$s->port = $GLOBALS["optimizer"]["port"];
		$s->curl_path = $GLOBALS["optimizer"]['curl_path'];
		$s->fetch($rt);
		$result = json_decode($s->results,true);
		if ($result["status"] == "OK" || $result["status"] == "200") {
			$this->logMessage(__FUNCTION__,sprintf("snoopy result is [%s] from [%s] [%s]",print_r($result,true),$key,print_r($s,true)),3);
			$this->logMessage(__FUNCTION__,sprintf("waypoints are [%s] from addresses [%s]",print_r($result["routes"][0]["waypoint_order"],true),print_r($sequence,true)),1);
			//
			//	Start/End points are not touched here - first wp = 20 in the sequence
			//	$sequence[0] = start which is not in the returned waypoints
			//
			for($idx = 0; $idx < count($result["routes"][0]["waypoint_order"]); $idx++) {
				$this->execute(sprintf("update custom_delivery set driver_sequence = %d where id = %d",($idx+2)*10,$sequence[$result["routes"][0]["waypoint_order"][$idx]+1]["id"]));
				$this->logMessage(__FUNCTION__,sprintf("result [%d] is address [%s]",$idx,$sequence[$idx]["address"]["line1"]),1);
			}
		}
		else {
			$this->logMessage(__FUNCTION__,sprintf("routing failed [%s] url [%s] results [%s]",$result["status"],$rt,print_r($s,true)),1,true);
		}
		return $this->ajaxReturn(array("status"=>true,"html"=>$outer->show()));*/
	}

	private function getToken($parms) {
		$tmp = new Forms();
		$tmp->setHtml($parms["tokenUrl"]);
		$tmp->addData($parms);
		$url = $tmp->show();
		$s = new Snoopy();
		$s->port = 443;
		$s->curl_path = $GLOBALS['curl_path'];
		$s->fetch($url);
		$r = $s->results;
		$this->logMessage(__FUNCTION__,sprintf('snoopy [%s] result [%s] from [%s]',print_r($s,true),print_r($r,true),$url),2);
		$valid = false;
		if ($s->status == 200) {
			$r = json_decode($s->results,true);
			if (array_key_exists("error",$r)) {
				$this->addError(sprintf("Arcgis Error: %s, Status: %d",$r["error"]["error_description"],$r["error"]["code"]));
			}
			else {
				$r["expiration"] = date(strtotime(sprintf("now + %d seconds",$r["expires_in"])));
				$_SESSION["administrator"]["arcgis"] = $r;
				$this->logMessage(__FUNCTION__,sprintf("session is now [%s]", print_r($_SESSION,true)),1);
				$valid = true;
			}
		}
		else {
			$this->addError(sprintf("Could not connect to Arcgis, status is %d",$s->status));
		}
		return $valid;
	}

	function allDeliveries($fromMain = false) {
		$outer = new Forms();
		$outer->init($this->getTemplate(__FUNCTION__));
		$flds = $outer->buildForm($this->getFields(__FUNCTION__));
		$outer->addTag('heading',$this->getHeader(),false);
		$inner = new Forms();
		$inner->init($this->getTemplate(__FUNCTION__."Row"));
		$flds = $inner->buildForm($this->getFields(__FUNCTION__."Row"));
		if (count($_POST) == 0 || !(array_key_exists(__FUNCTION__,$_POST))) {
			if (array_key_exists('formData',$_SESSION) && array_key_exists(__FUNCTION__, $_SESSION['formData']))
				$_POST = $_SESSION['formData'][__FUNCTION__];
			else
				$_POST = array("scheduled_date"=>date("Y-m-d",strtotime("today")),"opt_scheduled_date"=>">=","pagenum"=>1,"sortby"=>"scheduled_date","sortorder"=>"asc","pager"=>$this->m_perrow,__FUNCTION__=>1);
		}
		$outer->addData($_POST);
		$srch = array();
		$x2 = "x2";
		$srch = array();
		$srch["opt_delivered"] = "completed = 0";
		$srch["service_type"] = "service_type = 'P'";	// only pull orders based on pickup date >= selected date
		foreach($_POST as $key=>$value) {
			switch($key) {
			case "opt_scheduled_date":
				if ($value !="" && strlen($outer->getData("scheduled_date")) > 0)
					$srch[$key] = sprintf("date_format(scheduled_date,'%%Y-%%m-%%d') %s '%s'", $_POST["opt_scheduled_date"], date("Y-m-d",strtotime($_POST["scheduled_date"])));
				break;
			case "driver_id":
				if ($value != "") $srch[$key] = sprintf("driver_id = %d",$value);
				break;
			case "delivery_type":
				if (array_key_exists($key,$_POST)) {
					$value = $_POST[$key];
					if (strlen($value) > 0)
						$srch[$key] = sprintf(' cd.order_id in (select ol.order_id from order_lines ol, product p where p.id = ol.product_id and p.id = %d and ol.custom_package="S")',$this->escape_string($value));
				}
				break;
			case "service_type":
				if ($value != "") {
					$srch[$key] = sprintf("service_type = %d",$value);
					$x2 = "";
				}
				break;
			case "opt_delivered":
				if ($value == 1) {
					$srch[$key] = "completed = 1";
				}
				break;
			case "custom_same_day":
				if ($value != "") {
					$srch[$key] = sprintf("cd.order_id in (select order_id from order_lines ol, product p where ol.custom_package='S' and p.id = ol.product_id and p.custom_same_day = %d)",$value);
				}
				break;
			case "order_id":
				if (($value = $outer->getData($key)) > 0) {
					$srch[$key] = sprintf("cd.order_id = %d", $value);
				}
				break;
			}
		}
		$srch["a"] = "o.id = cd.order_id";
		$srch["b"] = sprintf("o.order_status &  %d = 0", STATUS_CANCELLED);
		$srch["c"] = "o.deleted = 0";
		$_SESSION['formData'][__FUNCTION__] = $_POST;
		$where = implode(" and ",array_values($srch));
		$sql = sprintf("select count(distinct(order_id)) as order_id from custom_delivery cd, orders o where %s",$where);
		$count = $this->fetchScalar($sql);
		$perPage = $_POST["pager"];
		$pageNum = $_POST["pagenum"];
		$pagination = $this->pagination($count, $perPage, $pageNum, 
			array('prev'=>$this->M_DIR.'forms/paginationPrev.html','next'=>$this->M_DIR.'forms/paginationNext.html',
			'pages'=>$this->M_DIR.'forms/paginationPage.html', 'wrapper'=>$this->M_DIR.'forms/paginationWrapper.html'),
			array('url'=>'/modit/ajax/allDeliveries/dispatch','destination'=>'middleContent'));
		$outer->addTag('pagination',$pagination,false);
		$start = ($pageNum-1)*$perPage;
		$sortby = $_POST["sortby"];
		$sortorder = $_POST["sortorder"];
		if ($sortorder == "asc" && $sortby == "scheduled_date") {
			$sortby = "IF(service_type='P',scheduled_date,'')";
		}
		$sql = sprintf("select distinct(cd.order_id) from orders o, custom_delivery cd, product p, order_lines ol where p.id = ol.product_id and ol.order_id = cd.order_id and ol.custom_package = 'S' and %s order by %s %s limit %d,%d", $where, $sortby, $sortorder, $perPage * ($pageNum-1), $perPage);
		$records = $this->fetchScalarAll($sql);
		$result = array();
		foreach($records as $key=>$value) {
			if ($x2 == "x2" || ($x2==""  && $_POST["service_type"] == "P")) {
				$p = $this->fetchSingle(sprintf("select c.*, a.id as a_id, p1.code as deliveryCode, p1.name as deliveryName, concat(m.company, '-', m.lastname,', ',m.firstname) as drivername, o.custom_placed_by from custom_delivery c left join drivers d on d.id = c.driver_id left join members m on m.id = d.member_id, addresses a, product p1, order_lines ol, orders o where c.order_id = %d and service_type = 'P' and a.ownerid = c.order_id and a.addresstype = %d and ol.order_id = c.order_id and p1.id = ol.product_id and ol.custom_package = 'S' and o.id = ol.order_id ",$value,ADDRESS_PICKUP));
				$p["address"] = Address::formatData($this->fetchSingle(sprintf("select * from addresses where id = %d",$p["a_id"])));
			}
			else $p = array();
			if ($x2 == "x2" || ($x2==""  && $_POST["service_type"] == "D")) {
				$d = $this->fetchSingle(sprintf("select c.*, a.id as a_id, p1.code as deliveryCode, p1.name as deliveryName, concat(m.company, '-', m.lastname,', ',m.firstname) as drivername from custom_delivery c left join drivers d on d.id = c.driver_id left join members m on m.id = d.member_id, addresses a, product p1, order_lines ol where c.order_id = %d and service_type = 'D' and a.ownerid = c.order_id and a.addresstype = %d and p1.id = ol.product_id and ol.custom_package = 'S' ",$value,ADDRESS_DELIVERY));
				$d["address"] = Address::formatData($this->fetchSingle(sprintf("select * from addresses where id = %d",$d["a_id"])));
			}
			else $d = array();
			//$inner->reset();
			$inner->addTag("service_type",array_key_exists("service_type",$_POST) ? $_POST["service_type"] : "");
			$inner->addData(array("pickup"=>$p,"delivery"=>$d));
			$result[] = $inner->show();
		}
		$outer->addTag("x2",$x2);
		$outer->addTag("deliveries",implode("",$result),false);
		if ($this->isAjax())
			return $this->ajaxReturn(array('status'=>true,'html'=>$outer->show()));
		else
			return $this->show($outer->show());
	}

	function messaging() {
		$outer = new Forms();
		$outer->init($this->getTemplate(__FUNCTION__));
		$flds = $outer->buildForm($this->getFields(__FUNCTION__));
		$outer->addTag('heading',$this->getHeader(),false);
		$inner = new Forms();
		$inner->init($this->getTemplate(__FUNCTION__."Row"));
		$flds = $inner->buildForm($this->getFields(__FUNCTION__."Row"));
		if (!(array_key_exists(__FUNCTION__,$_POST))) {
			$_POST = array("messaging"=>"1","opt_scheduled_date"=>"=", "scheduled_date"=>date("Y-m-d"));
		}
		$outer->addData($_POST);
		$outer->validate();
		$srch = array( 
			"ol.order_id = cd.order_id",
			"ol.custom_package = 'S'",
			"cda.delivery_id = cd.id",
			"p.id = ol.product_id",
			"d.id = cd.driver_id",
			"m.id = d.member_id"
		);
		foreach($_POST as $k=>$v) {
			$this->logMessage(__FUNCTION__, sprintf("k [%s] v [%s]", $k, $v ), 1);
			switch($k) {
			case "opt_scheduled_date":
				if (array_key_exists("scheduled_date",$_POST))
					$srch[] = sprintf("DATE_FORMAT(cda.created,'%%Y-%%m-%%d') %s '%s'", $v, $outer->getData("scheduled_date"));
					break;
			case "driver_id":
				if ((int)$v != 0)
					$srch[] = sprintf("driver_id = %d", $outer->getData("driver_id"));
				break;
			case "order_id":
				if ((int)$v != 0)
					$srch[] = sprintf("cd.order_id = %d", $outer->getData("order_id"));
				break;
			case "service_type":
				if ($v != "")
					$srch[] = sprintf("service_type = '%s'", $outer->getData("service_type"));
				break;
			case "delivery_type":
				if ((int)$v != 0)
					$srch[] = sprintf("p.id = %d", $outer->getData("delivery_type"));
				break;
			default:
			}
		}
		$sql = "select cda.*, m.company, m.firstname, m.lastname, cd.order_id, cd.service_type from custom_delivery_acknowledgement cda, custom_delivery cd, order_lines ol, product p, drivers d, members m where ";
		$sql .= implode(" and ",$srch);
		$this->logMessage(__FUNCTION__,sprintf("search statement [%s]", print_r($sql,true)), 1);
		$recs = $this->fetchAll($sql);
		$rows = array();
		foreach($recs as $k=>$rec) {
			$rec["driver_message"] = nl2br($rec["driver_message"]);
			$inner->addData($rec);
			$rows[] = $inner->show();
		}
		$outer->setData("messages",implode("",$rows));
		if ($this->isAjax())
			return $this->ajaxReturn(array('status'=>true,'html'=>$outer->show()));
		else
			return $this->show($outer->show());
	}

	function editAck() {
		$outer = new Forms();
		$outer->init($this->getTemplate(__FUNCTION__));
		$flds = $outer->buildForm($this->getFields(__FUNCTION__));
		$a_id = array_key_exists("a_id",$_REQUEST) ? $_REQUEST["a_id"] : 0;
		if ($data = $this->fetchSingle(sprintf("select cda.*, cd.order_id, cd.driver_id from custom_delivery_acknowledgement cda, custom_delivery cd where cda.id = %d and cd.id = cda.delivery_id", $a_id))) {
			$pu = $this->fetchSingle(sprintf("select * from addresses where ownerid = %d and ownertype = 'order' and addresstype=12", $data["order_id"]));
			$data["pickup"] = Address::formatData($pu);
			$del= $this->fetchSingle(sprintf("select * from addresses where ownerid = %d and ownertype = 'order' and addresstype=13", $data["order_id"]));
			$data["delivery"] = Address::formatData($del);
			$driver = $this->fetchSingle(sprintf("select * from members m, drivers d where d.id = %d and m.id = d.member_id", $data["driver_id"]));
			$data["driver"] = $driver;
			$outer->addData($data);
		}
		if (array_key_exists(__FUNCTION__,$_POST)) {
			$outer->addData($_POST);
			if ($outer->validate()) {
				$upd = array();
				foreach($flds as $key=>$fld) {
					$this->logMessage(__FUNCTION__,sprintf("testing [%s] exists [%s] value [%s]",$fld["name"],array_key_exists("database",$fld),array_key_exists("database",$fld)?$fld["database"]:"n/a"),1);
					if (!(array_key_exists('database',$fld) && $fld['database'] == false)) {
						$upd[$key] = $outer->getData($fld["name"]);
					}
				}
				if ($outer->getData("a_id") == 0) 
					$stmt = $this->prepare(sprintf("insert into custom_delivery_acknowledgement(%s) values(?%s)", implode(", ", array_keys($upd)), str_repeat(", ?", count($upd)-1)));
				else
					$stmt = $this->prepare(sprintf("update custom_delivery_acknowledgement set %s=? where id = %d", implode( "=?, ", array_keys($upd) ), $outer->getData("a_id") ) );
				$stmt->bindParams( array_merge( array(str_repeat("s",count($upd))), array_values($upd) ) );
				$stmt->execute();
				$outer->addFormSuccess("Message Updated");
			}
		}
		if ($this->isAjax())
			return $this->ajaxReturn(array('status'=>true,'html'=>$outer->show()));
		else
			return $this->show($outer->show());
	}

	function getMessages($p_id) {
		$outer = new Forms();
		$outer->init($this->getTemplate(__FUNCTION__));
		$inner = new Forms();
		$inner->init($this->getTemplate(__FUNCTION__."Row"));
		$flds = $inner->buildForm($this->getFields(__FUNCTION__));
		$msgs = $this->fetchAll(sprintf("select cda.*, m.company, m.firstname, m.lastname, u.fname, u.lname from custom_delivery_acknowledgement cda left join members m on m.id = cda.author_id left join users u on u.id = cda.author_id where delivery_id = %d order by created desc", $p_id));
		$recs = array();
		foreach($msgs as $k=>$v) {
			$inner->addData($v);
			$recs[] = $inner->show();
		}
		$outer->setData("messaging",implode("",$recs));
		return $outer->show();
	}

	function manifests($fromMain = false) {
		$outer = new Forms();
		$outer->init($this->getTemplate(__FUNCTION__));
		$flds = $outer->buildForm($this->getFields(__FUNCTION__));
		$outer->addTag('heading',$this->getHeader(),false);
		$inner = new Forms();
		$inner->init($this->getTemplate(__FUNCTION__."Row"));
		$flds = $inner->buildForm($this->getFields(__FUNCTION__."Row"));
		if (count($_POST) > 0 && array_key_exists(__FUNCTION__,$_POST)) {
			$outer->addData($_POST);
			$rpt_date = date("Y-m-d",strtotime("today + 1 day"));
			$srch = array("opt_delivered"=>"completed= 0","type"=>"service_type='D'","order_status"=>sprintf("order_status & %d = %d", STATUS_PROCESSING, STATUS_PROCESSING));
			foreach($_POST as $key=>$value) {
				switch($key) {
				case "opt_scheduled_date":
					$srch[$key] = sprintf("date_format(scheduled_date,'%%Y-%%m-%%d') <= '%s'", date("Y-m-d",strtotime($_POST["scheduled_date"])));
					$rpt_date = date("d-M-Y",strtotime($_POST["scheduled_date"]));
					break;
				case "driver_id":
					if ($value != "") $srch[$key] = sprintf("driver_id = %d",$value);
					break;
				case "delivery_type":
					if (array_key_exists($key,$_POST)) {
						$value = $_POST[$key];
						if (strlen($value) > 0)
							$srch[$key] = sprintf(' cd.order_id in (select ol.order_id from order_lines ol, product p where p.id = ol.product_id and p.id = %d and ol.custom_package="S")',$this->escape_string($value));
					}
					break;
				case "service_type":
					if ($value != "") {
						$srch[$key] = sprintf("service_type = %d",$value);
						$x2 = "";
					}
					break;
				case "opt_delivered":
					if ($value == 1) {
						$srch[$key] = "completed = 1";
					}
					break;
				case "custom_same_day":
					if ($value != "") {
						$srch[$key] = sprintf("cd.order_id in (select order_id from order_lines ol, product p where ol.custom_package='S' and p.id = ol.product_id and p.custom_same_day = %d)",$value);
					}
					break;
				}
			}
			$_SESSION['formData'][__FUNCTION__] = $_POST;
			$where = implode(" and ",array_values($srch));
			$sql = sprintf("select cd.*, m.company, m.firstname, m.lastname
from custom_delivery cd left join drivers d on cd.driver_id = d.id left join members m on m.id = d.member_id, product p, order_lines ol, orders o 
where o.deleted = 0 and p.id = ol.product_id and ol.order_id = cd.order_id and ol.custom_package = 'S' and o.id = ol.order_id and %s order by driver_id, order_id", $where );

			$records = $this->fetchAll($sql);
			$result = array();
			$pdf = new TCPDF('L', PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
			$pdf->SetCreator(PDF_CREATOR);
			$pdf->SetAuthor('KJV Courier');
			$pdf->SetTitle(sprintf('Manifest for %s',$rpt_date));
			$pdf->SetSubject('KJV Daily Manifest');
			$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
			$pdf->SetHeaderData("../../images/icons/kjv-logo.png", 30, "", sprintf("KJV Couriers\nManifest for %s",$rpt_date));
			$pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
			$pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));
			$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
			$pdf->SetMargins(PDF_MARGIN_LEFT, 35, PDF_MARGIN_RIGHT);
			$pdf->SetHeaderMargin(4);
			$pdf->SetFooterMargin(4);
			$pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
			if (@file_exists(dirname(__FILE__).'/lang/eng.php')) {
				require_once(dirname(__FILE__).'/lang/eng.php');
				$pdf->setLanguageArray($l);
			}
			$pdf->SetFont('times', '', 10);
			$pdf->AddPage();
			$pdf->setCellPaddings(0,0,0,0);
			$pdf->setCellMargins(0,0,0,0);
			$pdf->SetFillColor(255, 255, 127);
			$ct = 0;
			$p_id = 0;
			foreach($records as $key=>$value) {
				if ($ct > 0 && $p_id != $value["driver_id"]) {
					$pdf->addPage("L");
					$ct = 0;
				}
				$value["ct"] = $ct;
				$value["address"] = Address::formatData($this->fetchSingle(sprintf("select * from addresses where ownerid = %d and addresstype=%d and ownertype='order'",$value["order_id"], ADDRESS_DELIVERY )));
				$value["product"] = $this->fetchSingle(sprintf("select p.* from product p, order_lines ol where ol.order_id = %d and p.id = ol.product_id and ol.custom_package='S'", $value["order_id"]));
				$tmp = $this->fetchAll(sprintf("select p.name, sum(od.quantity) as qty, sum(od.weight) as wt, cd.code as UOM from product p, order_lines ol, order_lines_dimensions od, orders o, code_lookups cd where ol.order_id = %d and p.id = ol.product_id and ol.custom_package='P' and od.order_id = ol.order_id and od.line_id = ol.line_id and o.id = ol.order_id and cd.id = o.custom_weight_code group by p.id", $value["order_id"]));
				$pkgs = array();
				foreach($tmp as $sk=>$sv) {
					$pkgs[] = sprintf("%s : %d, %d %s", $sv["name"], $sv["qty"], $sv["wt"], $sv["UOM"]);
				}
				$value["packages"] = implode("<br/>", $pkgs);

				$tmp = $this->fetchAll(sprintf("select p.* from product p, order_lines ol where ol.order_id = %d and p.id = ol.product_id and ol.custom_package='A' and p.custom_special_requirement = 1", $value["order_id"]));
				$pkgs = array();
				foreach($tmp as $sk=>$sv) {
					$pkgs[] = $sv["name"];
				}
				$value["specials"] = "<strong>".implode(",", $pkgs)."</strong>";

				$inner->addData($value);
				$ct += 1;
				$p_id = $value["driver_id"];
				$pdf->writeHTML($inner->show(), true, false, true, false, '');
			}
			try {
				ob_clean();
				$pdf->Output(sprintf('manifest-%s.pdf',$rpt_date), 'I');
			}
			catch(Exception $err) {
				return print_r($err,true);
			}
		}
		else {
			if ($this->isAjax())
				return $this->ajaxReturn(array('status'=>true,'html'=>$outer->show()));
			else
				return $this->show($outer->show());
		}
	}

	function editDelivery() {
		$d_id = array_key_exists("d_id",$_REQUEST) ? $_REQUEST["d_id"] : 0;
		$outer = new Forms();
		$outer->init($this->getTemplate(__FUNCTION__));
		$outer->addData($_REQUEST);
		return $this->show($outer->show());
	}

	function sendSMS($d_id) {
		return $this->sendText($d_id, $this->getTemplate(__FUNCTION__), $this->getFields(__FUNCTION__));
	}

	function enableDriver() {
		$d_id = array_key_exists("d_id", $_REQUEST) ? $_REQUEST["d_id"] : 0;
		if (array_key_exists(__FUNCTION__,$_REQUEST))
			$this->execute(sprintf("update drivers set disabled_as_of='0000-00-00 00:00:00' where id = %d", $d_id));
		$form = new Forms();
		$form->init($this->getTemplate(__FUNCTION__));
		if ($this->isAjax())
			return $this->ajaxReturn(array('status'=>true,'html'=>$form->show()));
		else
			return $this->show($form->show());
	}

	function disableDriver() {
		$d_id = array_key_exists("d_id", $_REQUEST) ? $_REQUEST["d_id"] : 0;
		if (array_key_exists(__FUNCTION__,$_REQUEST))
			$this->execute(sprintf("update drivers set disabled_as_of=CURRENT_TIMESTAMP() where id = %d", $d_id));
		$form = new Forms();
		$form->init($this->getTemplate(__FUNCTION__));
		if ($this->isAjax())
			return $this->ajaxReturn(array('status'=>true,'html'=>$form->show()));
		else
			return $this->show($form->show());
	}

}

?>