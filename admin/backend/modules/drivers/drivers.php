<?php

class MyTCPDF extends TCPDF {
	private $m_html = "";

    /**
     * @param $html
     */
    public function __construct($html ) {
		parent::__construct('L', PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
		$this->m_html = $html;
	}

    /**
     * @return void
     */
    public function Header() {
		$this->writeHTML($this->m_html, true, false, true, false, '');
	}
}

class drivers extends Backend {

	private $m_content = 'drivers';
	private $m_perrow = 5;

	/**
	 * @throws phpmailerException
	 */
	public function __construct() {
		$this->M_DIR = 'backend/modules/drivers/';
		$this->setTemplates(
			array(
				'header'=>$this->M_DIR.'forms/heading.html',
				'main'=>$this->M_DIR.'drivers.html',
				'form'=>$this->M_DIR.'forms/form.html',
				'driversInfo'=>$this->M_DIR.'forms/driversInfo.html',
				'showFolderContent'=>$this->M_DIR.'forms/folderContent.html',
				'showSearchForm'=>$this->M_DIR.'forms/searchForm.html',
				'buildTree'=>$this->M_DIR.'forms/buildTree.html',
				'buildTreeRow'=>$this->M_DIR.'forms/buildTreeRow.html',
				'articleList'=>$this->M_DIR.'forms/articleList.html',
				'editDriver'=>$this->M_DIR.'forms/editDriver.html',
				'editDriverSuccess'=>$this->M_DIR.'forms/editDriverSuccess.html',
				'showSchedule'=>$this->M_DIR.'forms/showSchedule.html',
				'scheduleRow'=>$this->M_DIR.'forms/showScheduleRow.html',
				'editPackage'=>$this->M_DIR.'forms/editPackage.html',
				'editPackageSuccess'=>$this->M_DIR.'forms/editPackageSuccess.html',
				'getFSA'=>$this->M_DIR.'forms/getFSA.html',
				'getFSARow'=>$this->M_DIR.'forms/getFSARow.html',
				'addFSA'=>$this->M_DIR.'forms/addFSA.html',
				'addFSASuccess'=>$this->M_DIR.'forms/addFSASuccess.html',
				'zones'=>$this->M_DIR.'forms/zones.html',
				'zonesRow'=>$this->M_DIR.'forms/zonesRow.html',
				'editZone'=>$this->M_DIR.'forms/editZone.html',
				'editZoneSuccess'=>$this->M_DIR.'forms/editZoneSuccess.html',
				'payments'=>$this->M_DIR.'forms/payments.html',
				'paymentsRow'=>$this->M_DIR.'forms/paymentsRow.html',
				'paymentsDriver'=>$this->M_DIR.'forms/paymentsDriver.html',
				'paymentsAll'=>$this->M_DIR.'forms/paymentsAll.html',
				'paymentsSummary'=>$this->M_DIR.'forms/paymentsSummary.html',
				'paymentsSummaryRow'=>$this->M_DIR.'forms/paymentsSummaryRow.html',
				'paymentsSummaryTable'=>$this->M_DIR.'forms/paymentsSummaryTable.html',
				'editPayment'=>$this->M_DIR.'forms/editPayment.html',
				'editPaymentSuccess'=>$this->M_DIR.'forms/editPaymentSuccess.html',
				'mapIt'=>$this->M_DIR.'forms/map-outer.html',
				'mapItAddress'=>$this->M_DIR.'forms/map-inner.html',
				'mapItRoute'=>$this->M_DIR.'forms/map-route.html',
				'mapItDirections'=>$this->M_DIR.'forms/map-direction.html',
				'showPayments'=>$this->M_DIR.'forms/showPayments.html',
				'showPaymentsRow'=>$this->M_DIR.'forms/showPaymentsRow.html',
				'deductions'=>$this->M_DIR.'forms/deductions.html',
				'deductionsRow'=>$this->M_DIR.'forms/deductionsRow.html',
				'getCommissionDetails'=>$this->M_DIR.'forms/getCommissionDetails.html',
				'getCommissionDetailsRow'=>$this->M_DIR.'forms/getCommissionDetailsRow.html',
				'editCommission'=>$this->M_DIR.'forms/editCommission.html',
				'editCommissionSuccess'=>$this->M_DIR.'forms/editCommissionSuccess.html',
				'zoneDelete'=>$this->M_DIR.'forms/zoneDelete.html',
				'getZones'=>$this->M_DIR.'forms/getZones.html',
				'printPayout'=>$this->M_DIR.'forms/printPayout.html',
				'printPayoutRow'=>$this->M_DIR.'forms/printPayoutRow.html',
				'printPayoutDay'=>$this->M_DIR.'forms/printPayoutDay.html',
				'editService'=>$this->M_DIR.'forms/editService.html',
				'sendPDF'=>$this->M_DIR.'forms/sendPDF.html'
			)
		);
		$this->setFields(array(
			'addContent'=>array(
				'options'=>array('method'=>'post','action'=>'/modit/ajax/addContent/drivers'),
				'addContent'=>array('type'=>'hidden','value'=>1,'database'=>false)
			),
			'showSearchForm'=>array(
				'options'=>array('action'=>'showSearchForm','name'=>'searchForm','id'=>'search_form'),
				'opt_name'=>array('type'=>'select','name'=>'opt_name','lookup'=>'search_string'),
				'vehicle_id'=>array('type'=>'select','sql'=>'select id, name from vehicles order by name','required'=>false),
				'name'=>array('type'=>'input','required'=>false),
				'enabled'=>array('type'=>'select','lookup'=>'boolean'),
				'deleted'=>array('type'=>'select','lookup'=>'boolean'),
				'showSearchForm'=>array('type'=>'hidden','value'=>1),
				'pagenum'=>array('type'=>'hidden','value'=>1),
				'sortby'=>array('type'=>'hidden','value'=>'company'),
				'sortorder'=>array('type'=>'hidden','value'=>'asc'),
				'quicksearch'=>array('type'=>'input','name'=>'quicksearch','required'=>false),
				'opt_quicksearch'=>array('type'=>'hidden','value'=>'like'),
				"group_id"=>array('type'=>'select','required'=>false,'idlookup'=>'driver_groups'),
				'pager'=>array('type'=>'select','required'=>true,'value'=>$this->m_perrow,'lookup'=>'paging','id'=>'pager'),
				'submit'=>array('type'=>'submitbutton','value'=>'Search')
			),
			'articleList'=>array(
				'enabled'=>array("type"=>"booleanIcon"),
				'deleted'=>array("type"=>"booleanIcon")
			),
			'editDriver'=>array(
				"enabled"=>array("type"=>"checkbox","value"=>1),
				"deleted"=>array("type"=>"checkbox","value"=>1),
				"member_id"=>array("type"=>"hidden"),
				"vehicle_id"=>array("type"=>"select","sql"=>"select id, name from vehicles order by name","required"=>true),
				"commission"=>array("type"=>"textfield","validation"=>"number","required"=>true,"value"=>0.00,'class'=>'def_field_input a-right'),
				"after_hours_commission"=>array("type"=>"textfield","validation"=>"number","required"=>true,"value"=>0.00,'class'=>'def_field_input a-right'),
				"fuel_surcharge"=>array("type"=>"textfield","validation"=>"number","required"=>true,"value"=>0.00,'class'=>'def_field_input a-right'),
				"deduction_radio"=>array("type"=>"textfield","validation"=>"number","required"=>true,"value"=>0.00,'class'=>'def_field_input a-right'),
				"deduction_other"=>array("type"=>"textfield","validation"=>"number","required"=>true,"value"=>0.00,'class'=>'def_field_input a-right'),
				"save"=>array("type"=>"submitbutton","value"=>"Save","database"=>false),
				"d_id"=>array("type"=>"hidden","database"=>false),
				"third_party"=>array("type"=>"checkbox","value"=>1),
				"third_party_commission"=>array("type"=>"textfield","value"=>0.00,'required'=>false,'validation'=>'number','class'=>'def_field_input a-right'),
				"group_id"=>array('type'=>'select','required'=>false,'idlookup'=>'driver_groups'),
				"editDriver"=>array("type"=>"hidden","value"=>1,"database"=>false)
			),
			'showSchedule' => array(
				'options'=>array('action'=>'showSchedule','name'=>'showScheduleForm','id'=>'showSchedule_form','method'=>'POST'),
				'showSchedule'=>array('type'=>'hidden','value'=>1),
				'scheduled_date'=>array('type'=>'datepicker'),
				'opt_scheduled_date'=>array('type'=>'select','name'=>'opt_scheduled_date','lookup'=>'search_options','class'=>'form-control',"value"=>"<="),
				'sortby'=>array('type'=>'hidden'),
				'sortorder'=>array('type'=>'hidden'),
				'd_id'=>array('type'=>'hidden'),
				'status'=>array('type'=>'select','lookup'=>'boolean','required'=>false,"value"=>0),
				'delivery_type'=>array('type'=>'select','required'=>false,'sql'=>sprintf('select p.id, name from product p where deleted = 0 and enabled = 1 and custom_special_requirement = 0 order by name')),
				'service_type'=>array('type'=>'select','required'=>false,'options'=>array(""=>"-none-","P"=>"Pickup","D"=>"Delivery"))
			),
			'scheduleRow'=>array(
				'scheduled_date'=>array('type'=>'timestamp','mask'=>'d-M H:i')
			),
			'editPackage'=>array(
				'options'=>array('method'=>'POST','name'=>'editPackage','action'=>'/modit/ajax/editPackage/drivers','database'=>false),
				'editPackage'=>array('type'=>'hidden','database'=>false),
				'p_id'=>array('type'=>'hidden','database'=>false,'value'=>'%%id%%'),
				'scheduled_date'=>array('type'=>'datetimestamp','database'=>false,'mask'=>'d-M H:i'),
				'actual_date'=>array('type'=>'datetimepicker','required'=>false,'AMPM'=>true),
				'completed'=>array('type'=>'checkbox','value'=>1),
				'comments'=>array('type'=>'textarea'),
				'instructions'=>array('type'=>'textarea'),
				'delivery_name'=>array('type'=>'textfield','required'=>false,'prettyName'=>'Delivery Name'),
				'submit'=>array('type'=>'submitbutton','value'=>'Save Changes','database'=>false)
			),
			'getFSA'=>array(),
			'addFSA'=>array(
				'addFSA'=>array('type'=>'hidden','value'=>1,'database'=>false),
				'driver_id'=>array('type'=>'hidden'),
				'fsa_id'=>array('type'=>'select','required'=>true,'multiple'=>true,'database'=>false),
				'submit'=>array('type'=>'submitbutton','value'=>'Add','database'=>false)
			),
			'zones'=>array(
				'options'=>array('name'=>'zoneSearch'),
				'sort'=>array('type'=>'hidden','value'=>'z.name'),
				'sortDir'=>array('type'=>'hidden','value'=>'asc'),
				'zonesModule'=>array('type'=>'hidden','value'=>1,'name'=>'zones')
			),
			'zonesRow'=>array(
				'enabled'=>array('type'=>'booleanIcon')
			),
			'editZone'=>array(
				'editZone'=>array('type'=>'hidden','value'=>1,'database'=>false),
				'z_id'=>array('type'=>'hidden','value'=>'%%z_id%%','database'=>false),
				'driver_id'=>array('type'=>'select','required'=>true,'sql'=>sprintf("select d.id, concat(company, '-', lastname,', ',firstname) from drivers d, members m where d.enabled = 1 and d.deleted = 0 and m.id = d.member_id and m.enabled = 1 and m.deleted = 0 order by 2")),
				'enabled'=>array('type'=>'checkbox','value'=>1),
				'name'=>array('type'=>'textfield','required'=>true),
				'submit'=>array('type'=>'submitbutton','value'=>'Save','database'=>false),
				'vehicle_id'=>array('type'=>'select','required'=>true,'sql'=>'select id, name from vehicles order by name','onchange'=>'getDrivers($(this).val())'),
				'fsa'=>array('type'=>'select','multiple'=>true,'required'=>false,'database'=>false)
			),
			'payments'=>array(
				'options'=>array('action'=>'payments','name'=>'paymentsForm','id'=>'payments_form','method'=>'POST'),
				'payments'=>array('type'=>'hidden','value'=>1),
				'start_date'=>array('type'=>'datepicker'),
				'end_date'=>array('type'=>'datepicker','value'=>date("Y-m-d")),
				'sortby'=>array('type'=>'hidden','value'=>'name'),
				'sortorder'=>array('type'=>'hidden','value'=>'asc'),
				'completed'=>array('type'=>'select','lookup'=>'boolean','value'=>1),
				'overridden'=>array('type'=>'select','lookup'=>'boolean','value'=>''),
				'paid'=>array('type'=>'select','lookup'=>'boolean','value'=>'0'),
				'driver_id'=>array('type'=>'select','sql'=>'select d.id, concat(m.company, "-", m.lastname," ",m.firstname) from drivers d, members m where m.id = d.member_id and d.enabled = 1 and d.deleted = 0 order by 2'),
				'product_id'=>array('type'=>'select','required'=>false,'sql'=>sprintf('select p.id, name from product p, product_by_folder pf where pf.folder_id in(%d,%d) and p.id = pf.product_id order by name',DELIVERY_TYPES,FEDEX_PRODUCTS)),
				'service_type'=>array('type'=>'select','required'=>false,'options'=>array(""=>"-none-","P"=>"Pickup","D"=>"Delivery")),
				'mark_as_paid'=>array('type'=>'hidden','value'=>0),
				'order_id'=>array('type'=>'number', 'required'=>false,'validation'=>'number','class'=>'a-right def_field_input')
			),
			'paymentsRow'=>array(
				'actual_date'=>array('type'=>'datetimestamp'),
				'payment'=>array('type'=>'currency'),
				'value'=>array('type'=>'currency'),
				'paid'=>array('type'=>'booleanIcon'),
				'paid_timestamp'=>array('type'=>'datetimestamp')
			),
			'editPayment'=>array(
				'custom_commissionable_amt'=>array('type'=>'currency','database'=>false),
				'editPayment'=>array('type'=>'hidden','value'=>1,'database'=>false),
				'p_id'=>array('type'=>'hidden','database'=>false,'value'=>'%%id%%'),
				'percent_of_delivery'=>array('type'=>'textfield','required'=>true,'validation'=>'number','class'=>'a-right def_field_input'),
				'paid'=>array('type'=>'select','required'=>true,'lookup'=>'boolean'),
				'paid_timestamp'=>array('type'=>'datetimestamp'),
				'payment'=>array('type'=>'currency'),
				//'adjust'=>array('type'=>'checkbox','value'=>1,'database'=>false),
				'save'=>array('type'=>'submitbutton','value'=>'Save','database'=>false)
			),
			'mapIt'=>array(
			),
			'paymentsSummaryRow'=>array('value'=>array('type'=>'currency'),'payment'=>array('type'=>'currency')),
			'paymentsDriverRow'=>array('value'=>array('type'=>'currency'),'payment'=>array('type'=>'currency')),
			'showPayments'=>array(
				'start_date'=>array('type'=>'datepicker'),
				'end_date'=>array('type'=>'datepicker'),
				'total'=>array('type'=>'currency'),
				'base'=>array('type'=>'currency'),
				'commission'=>array('type'=>'currency'),
				'override'=>array('type'=>'currency')
			),
			'showPaymentsRow'=>array(
				'value'=>array('type'=>'currency'),
				'payment'=>array('type'=>'currency'),
				'custom_commissionable_amt'=>array('type'=>'currency'),
				'calculated'=>array('type'=>'currency'),
				'override'=>array('type'=>'currency'),
				'actual_date'=>array('type'=>'datetimestamp'),
				'scheduled_date'=>array('type'=>'datetimestamp')
			),
			'deductionsRow'=>array(
				'deduction_radio'=>array('type'=>'currency'),
				'deduction_other'=>array('type'=>'currency')
			),
			'getZones'=>array(
			),
			'getCommissionDetails'=>array(
			),
			'getCommissionDetailsRow'=>array(
				'base_amount'=>array('type'=>'currency'),
				'calculated'=>array('type'=>'currency'),
				'value'=>array('type'=>'currency'),
				'overridden'=>array('type'=>'booleanIcon'),
			),
			'editCommission'=>array(
				'c_id'=>array('type'=>'hidden','value'=>'%%request:c_id%%','database'=>false),
				'calculated'=>array('type'=>'currency','database'=>false),
				'overridden'=>array('type'=>'checkbox','value'=>1),
				'value'=>array('type'=>'textfield','validation'=>'number','required'=>true,'class'=>'a-right def_field_input'),
				'base_amount'=>array('type'=>'currency'),
				'editCommission'=>array('type'=>'hidden','value'=>1,'database'=>false),
				'save'=>array('type'=>'submitbutton','value'=>'Save','database'=>false),
				'comment'=>array('type'=>'textarea')
			),
			'printPayout'=>array(
				'start_date'=>array('type'=>'datestamp'),
				'end_date'=>array('type'=>'datestamp'),
				'custom_commissionable_amt'=>array('type'=>'currency'),
				'fuel'=>array('type'=>'currency'),
				'payment'=>array('type'=>'currency'),
				'deduction_radio'=>array('type'=>'currency'),
				'deduction_other'=>array('type'=>'currency'),
				'adjTotal'=>array('type'=>'currency')
			),
			'printPayoutRow'=>array(
				'scheduled_date'=>array('type'=>'datetimestamp','mask'=>'d-M'),
				'actual_date'=>array('type'=>'datetimestamp','mask'=>'d-M'),
				'calc'=>array('type'=>'currency'),
				'fuel'=>array('type'=>'currency'),
				'val'=>array('type'=>'currency'),
				'o_comm'=>array('type'=>'currency'),
				'o_paid'=>array('type'=>'currency')
			),
			'printPayoutDay'=>array(
				'date'=>array('type'=>'datestamp'),
				'dayTotal'=>array('type'=>'currency')
			),
			'sendPDF'=>array(
				"start_date"=>array("type"=>"datestamp"),
				"end_date"=>array("type"=>"datestamp")
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
		$frmFields = $this->getFields('main');
		$frmFields = $form->buildForm($frmFields);
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
		//$form->getField('contenttree')->addAttribute('value',$this->buildTree());
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
     * @return false|string
     * @throws phpmailerException
     */
    function showContentTree() {
		$d = $this->fetchAll(sprintf("select d.*, m.company, m.firstname, m.lastname from drivers d, members m where m.id = d.member_id and d.deleted = 0 and d.enabled = 1 and m.enabled = 1 and m.deleted = 0 order by company, lastname, firstname"));
		$outer = new Forms();
		$outer->init($this->getTemplate("buildTree"));
		$result = array();
		$inner = new Forms();
		$inner->init($this->getTemplate("buildTreeRow"));
		$result = array();
		$flds = $this->getFields("buildTree");
		$flds = $inner->buildForm($flds);
		foreach($d as $key=>$member) {
			$inner->reset();
			$inner->addData($member);
			$result[] = $inner->show();
		}
		$outer->addTag("drivers",implode("",$result),false);
		return $this->ajaxReturn(array("status"=>true,"html"=>$outer->show()));
	}

    /**
     * @param bool $fromMain
     * @return array|false|mixed|string|string[]|void
     * @throws phpmailerException
     */
    function showPageProperties($fromMain = false) {
		$result = array();
		$return = 'true';
		if (!(array_key_exists('id',$_REQUEST) && $data = $this->fetchSingle(sprintf('select * from %s where id = %d',$this->m_tree, $_REQUEST['id']))))
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
		$frmFlds = $form->buildForm($this->getFields('folderProperties'));
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
				$stmt = $this->prepare(sprintf('update %s set %s where id = %d',$this->m_tree,implode(',',$flds),$data['id']));
				$stmt->bindParams(array_merge(array(str_repeat('s', count($flds))),$values));
				$status = $stmt->execute();
				if ($status) {
					if ($this->isAjax()) {
						$this->logMessage('showPageProperties', 'executing ajax success return', 3);
						$this->addMessage('Record successfully added');
						return $this->ajaxReturn(array(
								'status'=>'true',
								'html'=>'',
								'url'=>'/modit/drivers?p_id='.$data['id']
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
     * @param bool $fromMain
     * @return array|false|mixed|string|string[]|void
     * @throws phpmailerException
     */
    function showPageContent($fromMain = false) {
		$p_id = array_key_exists('p_id',$_REQUEST) ? $_REQUEST['p_id'] : 0;
		$j_id = array_key_exists('j_id',$_REQUEST) ? $_REQUEST['j_id'] : 0;
		$form = new Forms();
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
				$tmp = $this->checkArray("formData:driversSearchForm:pager",$_SESSION);
				if ($tmp > 0) 
					$perPage = $tmp;
				else
				$perPage = $this->m_perrow;
			}
			$form->setData('pager',$perPage);
			$count = $this->fetchScalar(sprintf('select count(n.id) from %s n where n.id in (select f.zone_id from %s f where f.folder_id = %d)', $this->m_content, $this->m_junction, $_REQUEST['p_id']));
			$pagination = $this->pagination($count, $perPage, $pageNum, 
				array('prev'=>$this->M_DIR.'forms/paginationPrev.html','next'=>$this->M_DIR.'forms/paginationNext.html',
						'pages'=>$this->M_DIR.'forms/paginationPage.html', 'wrapper'=>$this->M_DIR.'forms/paginationWrapper.html'));
			$start = ($pageNum-1)*$perPage;
			$sortby = 'sequence';
			$sortorder = 'asc';
			if (count($_POST) > 0 && array_key_exists('showFolderContent',$_POST)) {
				$sortby = $_POST['sortby'];
				$sortorder = $_POST['sortorder'];
				$form->addData($_POST);
			}
			$sql = sprintf('select a.*, f.id as j_id from %s a left join %s f on a.id = f.zone_id where f.folder_id = %d order by %s %s limit %d,%d',  $this->m_content, $this->m_junction, $_REQUEST['p_id'],$sortby, $sortorder, $start,$perPage);
			$drivers = $this->fetchAll($sql);
			$this->logMessage('showPageContent', sprintf('sql [%s], records [%d]',$sql, count($drivers)), 2);
			$articles = array();
			foreach($drivers as $article) {
				$frm = new Forms();
				$tmp = $this->getFields('articleList');
				$frm->init($this->getTemplate('articleList'),array());
				$tmp = $frm->buildForm($tmp);
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
			if (array_key_exists('formData',$_SESSION) && array_key_exists('driversSearchForm', $_SESSION['formData']))
				$_POST = $_SESSION['formData']['driversSearchForm'];
			else
				$_POST = array('showSearchForm'=>1,'deleted'=>0,'enabled'=>1,'sortby'=>'company','sortorder'=>'asc');
		$this->logMessage("showSearchForm",sprintf("post [%s]",print_r($_POST,true)),1);
		if (count($_POST) > 0 && array_key_exists('showSearchForm',$_POST)) {
			if ((!array_key_exists('deleted',$_POST)) || strlen($_POST['deleted']) == 0) $_POST['deleted'] = 0;
			$form->addData($_POST);
			if ($form->validate()) {
				$_SESSION['formData']['driversSearchForm'] = $form->getAllData();
				$srch = array(0=>sprintf("a.addresstype = %d",ADDRESS_COMPANY), 1=>"a.address_book = 1", 2=> "a.deleted = 0");
				$srch = array();
				foreach($frmFields as $key=>$value) {
					switch($key) {
						case 'quicksearch':
							if (array_key_exists('opt_quicksearch',$_POST) && $_POST['opt_quicksearch'] != '' && $value = $form->getData($key)) {
								if ($_POST['opt_quicksearch'] == 'like' && strpos($value,'%',0) === false) {
									$value = '%'.$value.'%';
								}
								$tmp = array();
								$tmp[] = sprintf(' concat(m.company, "-", m.firstname," ",m.lastname) %s "%s"',$_POST['opt_quicksearch'],$this->escape_string($value));
								$srch[] = sprintf('(%s)',implode(' or ',$tmp));
							}
							break;
						case 'name':
							if (array_key_exists('opt_name',$_POST) && $_POST['opt_name'] != '' && $value = $form->getData($key)) {
								if ($_POST['opt_name'] == 'like' && strpos($value,'%',0) === false) {
									$value = '%'.$value.'%';
								}
								$srch[] = sprintf(' concat(m.company, "-", m.firstname," ",m.lastname) %s "%s"',$_POST['opt_name'],$this->escape_string($value));
							}
							break;
						case 'group_id':
						case 'vehicle_id':
							if (!is_null($value = $form->getData($key)))
								if ((int)$value > 0)
									$srch[] = sprintf(' d.%s = %s',$key,$this->escape_string($value));
							break;
						case 'enabled':
						case 'deleted':
							if (!is_null($value = $form->getData($key)))
								$srch[] = sprintf(' d.%s = %s',$key,$this->escape_string($value));
							break;
						default:
							break;
					}
				}
				if (count($srch) > 0) {
					$this->logMessage('showSearchForm',sprintf('search options [%s]',print_r($srch,true)),3);
					if (array_key_exists('pagenum',$_REQUEST))
						$pageNum = $_REQUEST['pagenum'];
					else
						$pageNum = 1;	// no 0 based calcs
					if (array_key_exists('pager',$_REQUEST)) 
						$perPage = $_REQUEST['pager'];
					else {
						$tmp = $this->checkArray("formData:driversSearchForm:pager",$_SESSION);
						if ($tmp > 0) 
							$perPage = $tmp;
						else
						$perPage = $this->m_perrow;
					}
					$form->setData('pager',$perPage);
					$count = $this->fetchScalar(sprintf('select count(d.id) from %s d, members m left join addresses a on a.ownerid = m.id and a.ownertype="member" and a.deleted = 0 and a.addresstype = 392 and a.address_book = 1 where m.id = d.member_id and %s', $this->m_content, implode(' and ',$srch)));
					$pageNum = max(1,min($pageNum, (floor(($count-1)/$perPage)+1)));
					$form->setData('pagenum', $pageNum);
					$pagination = $this->pagination($count, $perPage, $pageNum,
							array('prev'=>$this->M_DIR.'forms/paginationPrev.html','next'=>$this->M_DIR.'forms/paginationNext.html',
									'pages'=>$this->M_DIR.'forms/paginationPage.html', 'wrapper'=>$this->M_DIR.'forms/paginationWrapper.html'));
					$start = ($pageNum-1)*$perPage;
					$sortby = array_key_exists('sortby',$_POST) ? $_POST['sortby'] : "company";
					$sortorder = array_key_exists('sortorder',$_POST) ? $_POST['sortorder'] : "asc";
					$sql = sprintf('select d.*, m.company, m.firstname, m.lastname, m.email, v.name as vehicleType, a.phone1, cl.code, cl.value as group_name from %s d left join vehicles v on v.id = d.vehicle_id left join code_lookups cl on d.group_id = cl.id, members m left join addresses a on a.ownerid = m.id and a.ownertype="member" and a.addresstype=392 and a.deleted = 0 and a.address_book = 1 where m.id = d.member_id and %s order by %s %s limit %d,%d',
						 $this->m_content, implode(' and ',$srch),$sortby, $sortorder, $start,$perPage);
					$recs = $this->fetchAll($sql);
					$this->logMessage('showSearchForm', sprintf('sql [%s] records [%d]',$sql,count($recs)), 2);
					$articles = array();
					$frm = new Forms();
					$tmp = $this->getFields('articleList');
					$frm->init($this->getTemplate('articleList'),array());
					$tmp = $frm->buildForm($tmp);
					foreach($recs as $article) {
						$frm->reset();
						if ($address = $this->fetchSingle(sprintf("select * from addresses where ownertype='member' and ownerid = %d",$article["id"])))
							$article["address"] = Address::formatData($address);
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
		if (!(array_key_exists('a_id',$_REQUEST) && $_REQUEST['a_id'] > 0 && $data = $this->fetchSingle(sprintf('select * from %s where id = %d', $this->m_content, $_REQUEST['a_id'])))) {
			$data = array('id'=>0,'image1'=>'','image2'=>''); 
		}
		if (count($_REQUEST) > 0 && array_key_exists('destFolders',$_REQUEST) || $data['id'] > 0) {
			$ids = array();
			if (array_key_exists('destFolders',$_REQUEST)) {
				$ids = $_REQUEST['destFolders'];
				if (!is_array($ids)) $ids = array($ids);
			}
			if ($data['id'] > 0) {
				$tmp = $this->fetchScalarAll(sprintf('select folder_id from %s where zone_id = %d', $this->m_junction, $data['id']));
				$ids = array_merge($ids,$tmp);
			}
			if (count($ids) > 0) {
				$data['destFolders'] = $ids;
			}
		}
		$frmFields = $form->buildForm($frmFields);
		$data['imagesel_a'] = $data['image1'];
		$data['imagesel_b'] = $data['image2'];
		$data['fsa'] = $this->loadFSA($data);
		$data['j_id'] = array_key_exists('j_id',$_REQUEST) ? $_REQUEST['j_id'] : 0;
		$data['interzone'] = $this->loadInterzone($data);
		$form->addData($data);
		$status = 'false';	//assume it failed
		if (count($_POST) > 0 && array_key_exists('addContent',$_POST)) {
			$_POST['imagesel_a'] = $_POST['image1'];
			$_POST['imagesel_b'] = $_POST['image2'];
			$form->addData($_POST);
			if ($form->validate()) {
				$id = $_POST['a_id'];
				unset($frmFields['a_id']);
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
					if ($id == 0) {
						$id = $this->insertId();
						$data['id'] = $id;
						$form->setData('id',$id);
					}
					$destFolders = $_POST['destFolders'];
					if (!is_array($destFolders)) $destFolders = array($destFolders);
					//
					//	delete folders we are no longer a member of
					//
					$this->execute(sprintf('delete from %s where zone_id = %d and folder_id not in (%s)', $this->m_junction, $id,implode(',',$destFolders)));
					$new = $this->fetchScalarAll(sprintf('select id from %s where id in (%s) and id not in (select folder_id from %s where zone_id = %d and folder_id in (%s))',
						$this->m_tree,implode(',',$destFolders),$this->m_junction,$id,implode(',',$destFolders)));
					$status = true;
					foreach($new as $key=>$folder) {
						$obj = new preparedStatement(sprintf('insert into %s(zone_id,folder_id) values(?,?)',$this->m_junction));
						$obj->bindParams(array('dd',$id,$folder));
						$status = $status && $obj->execute();
						$this->resequence($folder);
					}
					if ($status) {
						$this->commitTransaction();
						if ($status) {
							return $this->ajaxReturn(array(
								'status' => 'true',
								'url' => sprintf('/modit/drivers?p_id=%d',$destFolders[0])
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
     * @return array|mixed|string|string[]
     * @throws phpmailerException
     */
    function getHeader() {
		$form = new Forms();
		$form->init($this->getTemplate('header'));
		$flds = $form->buildForm($this->getFields('showSearchForm'));
		if (array_key_exists('p_id',$_REQUEST))
			$form->addTag("j_id",$_REQUEST["p_id"]);
		else
			$form->addTag("j_id",0);
		if (count($_POST) > 0 && array_key_exists('showSearchForm',$_POST))
			$form->addData($_POST);
		else
			if (array_key_exists('formData',$_SESSION) && array_key_exists('driversSearchForm', $_SESSION['formData']))
				$form->addData($_SESSION['formData']['driversSearchForm']);
		return $form->show();
	}

    /**
     * @param int $fromMain
     * @return array|false|mixed|string|string[]|void
     * @throws phpmailerException
     */
    function moduleStatus($fromMain = 0) {
		if (array_key_exists('formData',$_SESSION) && array_key_exists('driversSearchForm', $_SESSION['formData'])) {
			$_POST = $_SESSION['formData']['driversSearchForm'];
			$msg = "";
		}
		else {
			$_POST = array('showSearchForm'=>1,'deleted'=>0,'enabled'=>1,'sortby'=>'company','sortorder'=>'asc','pager'=>$this->m_perrow);
			$msg = "Showing all drivers";
		}
		$result = $this->showSearchForm($fromMain,$msg);
		return $result;
	}

    /**
     * @return false|string
     * @throws phpmailerException
     */
    function editDriver() {
		$outer = new Forms();
		$outer->init($this->getTemplate("editDriver"));
		$d_id = array_key_exists("d_id",$_REQUEST) ? $_REQUEST["d_id"] : 0;
		$flds = $this->getFields("editDriver");
		if (!$d = $this->fetchSingle(sprintf("select d.*, m.company, m.firstname, m.lastname from drivers d, members m where d.id = %d and m.id = d.member_id",$d_id))) {
			$d = array("id"=>0,"member_id"=>0);
			$flds["member_id"] = array("type"=>"select","required"=>true,"sql"=>sprintf("select id, concat( company, '-', lastname, ' ', firstname ) from members where deleted = 0 and enabled = 1 and id not in (select member_id from drivers where deleted = 0) and id in (select member_id from members_by_folder mf where folder_id = %d) order by company, lastname, firstname",DRIVER_FOLDER));
		}
		$flds = $outer->buildForm($flds);
		$outer->setData("d_id",$d_id);
		//$d["fsa"] = $this->getFSA(true);
		$outer->addData($d);
		if (count($_POST) != 0 && array_key_exists(__FUNCTION__,$_POST)) {
			$outer->addData($_POST);
			$valid = $outer->validate();
			if ($valid) {
				$upd = array();
				foreach($flds as $key=>$fld) {
					if (!(array_key_exists('database',$fld) && $fld['database'] == false)) {
						$upd[$fld["name"]] = $outer->getData($fld['name']);
					}
				}
				if ($d_id == 0)
					$stmt = $this->prepare(sprintf("insert into drivers(%s) values(%s?)",implode(", ",array_keys($upd)),str_repeat("?,",count($upd)-1)));
				else
					$stmt = $this->prepare(sprintf("update drivers set %s=? where id = %d", implode("=?, ", array_keys($upd)),$d_id));
				$stmt->bindParams(array_merge(array(str_repeat("s", count($upd))),array_values($upd)));
				$valid = $valid && $stmt->execute();
				if ($valid) {
					$outer->init($this->getTemplate("editDriverSuccess"));
					$outer->addFormSuccess("Record updated");
				}
				else $outer->addFormError("An Error occurred");
			}
			else $outer->addFormError("Form validation failed");
		}
		return $this->ajaxReturn(array('status'=>true,'html'=>$outer->show()));
	}

    /**
     * @return array|false|mixed|string|string[]|void
     * @throws phpmailerException
     */
    function showSchedule() {
		$outer = new Forms();
		$outer->init($this->getTemplate(__FUNCTION__));
		$flds = $this->getFields(__FUNCTION__);

		if (count($_POST) == 0 || !(array_key_exists(__FUNCTION__,$_POST))) {
			$_POST = array("scheduled_date"=>date("Y-m-d"),"pagenum"=>1,"sortby"=>"scheduled_date","sortorder"=>"asc","pager"=>$this->m_perrow,__FUNCTION__=>1,"status"=>0,"opt_scheduled_date"=>">=");
		}
		if (array_key_exists("d_id",$_REQUEST)) $_POST["d_id"] = $_REQUEST["d_id"];
		$driver = $this->fetchSingle(sprintf("select d.*, m.company, m.firstname, m.lastname from drivers d, members m where d.id = %d and m.id = d.member_id",$_REQUEST["d_id"]));
		$driver["zones"] = implode(", ", $this->getZones( $_REQUEST["d_id"] ));
		$outer->addData($driver);
		$outer->addData($_POST);
		$flds = $outer->buildForm($flds);
		$srch = array("driver_id"=>sprintf("driver_id = %d",$_REQUEST["d_id"]),"status"=>"completed = 0");
		foreach($_POST as $key=>$value) {
			switch($key) {
				case "opt_scheduled_date":
					$srch[$key] = sprintf("date_format(scheduled_date,'%%Y-%%m-%%d') %s '%s'", $value, date("Y-m-d",strtotime($_POST["scheduled_date"])));
					break;
				case "delivery_type":
					if ($value != "") $srch[$key] = sprintf("o1.product_id = %d",$value);
					break;
				case "service_type":
					if ($value != "") $srch[$key] = sprintf("service_type = '%s'",$value);
					break;
				case "product_id":
					if (is_array($value) && count($value) > 0)
						$srch[$key] = sprintf("product_id in (%s)",implode(",",$value));
					else
						if ((!is_array($value)) && $value != "")
							$srch[$key] = sprintf("product_id = %d",$value);
					break;
				case "status":
					if (strlen($value) > 0)
						$srch[$key] = sprintf("completed = %d", $value);
					else unset($srch[$key]);
			}
		}
		$_SESSION['formData'][__FUNCTION__] = $_POST;
		$where = implode(" and ",$srch);
		if ($_POST["sortorder"] == "asc" && $_POST["sortby"] == "scheduled_date") {
			$sortby = "IF(service_type='P',scheduled_date,'')";
		}
		$sql = sprintf("select c.*, p1.name as deliveryType from custom_delivery c, product p1, order_lines o1, orders o where o.deleted = 0 and o1.order_id = c.order_id and o.id = c.order_id AND o1.custom_package = 'S' AND p1.id = o1.product_id and %s order by %s %s", $where, $_POST["sortby"], $_POST["sortorder"]);
		$recs = $this->fetchAll($sql);
		$inner = new Forms();
		$inner->init($this->getTemplate("scheduleRow"));
		$flds = $inner->buildForm($this->getFields("scheduleRow"));
		$result = array();
		foreach($recs as $key=>$value) {
			$inner->reset();
			$address = $this->fetchSingle(sprintf("select * from addresses where ownerid = %d and ownertype='order' and addresstype = %d",$value["order_id"],$value["service_type"]=="D"?ADDRESS_DELIVERY:ADDRESS_PICKUP));
			$value["address"] = Address::formatData($address);
			$inner->addData($value);
			$result[] = $inner->show();
		}
		$outer->addTag("schedule",implode("",$result),false);
		$outer->addTag("heading",$this->getHeader(),false);
		if ($this->isAjax()) {
			return $this->ajaxReturn(array('status'=>true,'html'=>$outer->show()));
		}
		else return $this->show($outer->show());
	}

    /**
     * @return array|false|mixed|string|string[]
     * @throws phpmailerException
     */
    function editPackage() {
		$p_id = array_key_exists("p_id",$_REQUEST) ? $_REQUEST["p_id"] : 0;
		$package = $this->fetchSingle(sprintf("select * from custom_delivery where id = %d",$p_id));
		$package["address"] = $this->fetchSingle(sprintf("select * from addresses where ownerid = %d and ownertype = 'order'",$package["order_id"],$package["service_type"] == "P" ? ADDRESS_PICKUP : ADDRESS_DELIVERY));
		$outer = new Forms();
		$outer->init($this->getTemplate(__FUNCTION__));
		$flds = $outer->buildForm($this->getFields(__FUNCTION__));
		$outer->addData($package);
		if (count($_POST) > 0 && array_key_exists(__FUNCTION__,$_POST)) {
			$outer->addData($_POST);
			$valid = $outer->validate();
			if ($valid) {
				$upd = array();
				$address = array();
				foreach($flds as $key=>$fld) {
					if (!(array_key_exists('database',$fld) && $fld['database'] == false)) {
						$upd[$fld["name"]] = $outer->getData($fld["name"]);
					}
				}
				$stmt = $this->prepare(sprintf("update custom_delivery set %s=? where id = %d",implode("=?, ",array_keys($upd)),$package["id"]));
				$stmt->bindParams(array_merge(array(str_repeat("s",count($upd))),array_values($upd)));
				$valid = $valid & $stmt->execute();
				if ($valid) {
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

    /**
     * @param bool $fromMain
     * @return array|false|mixed|string|string[]
     * @throws phpmailerException
     */
    function getFSA_dnu($fromMain = false) {
		$d_id = array_key_exists("d_id",$_REQUEST) ? $_REQUEST["d_id"] : 0;
		$driver = $this->fetchSingle(sprintf("select * from drivers where id = %d",$d_id));
		$outer = new Forms();
		$outer->init($this->getTemplate(__FUNCTION__));
		$inner = new Forms();
		$inner->init($this->getTemplate(__FUNCTION__."Row"));
		$flds = $inner->buildForm($this->getFields(__FUNCTION__));
		$rows = $this->fetchAll(sprintf("select d.*, f.fsa, z.title from driver_fsa d, fsa f, zone_fsa zf, zones z where d.driver_id = %d and f.id = d.fsa_id and zf.fsa_id = f.id and z.id = zf.zone_id order by f.fsa",$d_id));
		$result = array();
		foreach($rows as $key=>$value) {
			$inner->reset();
			$inner->addData($value);
			$result[] = $inner->show();
		}
		$driver["fsa"] = implode("",$result);
		$outer->addData($driver);
		if ($fromMain)
			return $outer->show();
		else return $this->ajaxReturn(array('status'=>true,'html'=>$outer->show()));
	}

    /**
     * @return false|string
     * @throws phpmailerException
     */
    function addFSA() {
		$d_id = array_key_exists("d_id",$_REQUEST) ? $_REQUEST["d_id"] : 0;
		if ($d_id == 0) $d_id = array_key_exists("driver_id",$_REQUEST) ? $_REQUEST["driver_id"] : 0;
		$driver = $this->fetchSingle(sprintf("select * from drivers where id = %d",$d_id));
		$driver["driver_id"] = $d_id;
		$driver["d_id"] = $d_id;
		$outer = new Forms();
		$outer->init($this->getTemplate(__FUNCTION__));
		$flds = $this->getFields(__FUNCTION__);
		//
		//	an FSA can only be assigned to 1 driver of a given vehicle type
		//	bike & driver can get the same FSA, but not bike & bike or car & car
		//
		$flds["fsa_id"]["sql"] = sprintf("select f.id, concat(z.title,' - ',f.fsa) from fsa f, zones z, zone_fsa zf where f.id not in (select fsa_id from driver_fsa f1, drivers d1, vehicles v1 where v1.id = %d and d1.vehicle_id = v1.id and f1.driver_id = d1.id) and zf.fsa_id = f.id and z.id = zf.zone_id order by z.title, f.fsa",$driver["vehicle_id"]);
		$flds = $outer->buildForm($flds);
		$outer->addData($driver);
		if (count($_POST) > 0 && array_key_exists(__FUNCTION__,$_POST)) {
			$outer->addData($_POST);
			$valid = $outer->validate();
			if ($valid) {
				$upd = array();
				foreach($flds as $key=>$fld) {
					$this->logMessage(__FUNCTION__,sprintf("testing [%s] exists [%s] value [%s]",$fld["name"],array_key_exists("database",$fld),array_key_exists("database",$fld)?$fld["database"]:"n/a"),1);
					if (!(array_key_exists('database',$fld) && $fld['database'] == false)) {
						$upd[$key] = $outer->getData($fld["name"]);
					}
				}
				foreach($_REQUEST["fsa_id"] as $key=>$value) {
					//
					//	make sure this vehicle type can deliver here [bikes are only allowed in designated downtown zones]
					//
					$v_type = $this->fetchSingle(sprintf("select v.* from drivers d, vehicles v where d.id = %d and v.id = d.vehicle_id",$d_id));
					$tmp_valid = true;
					if ($v_type["downtown_only"]) {
						$f_type = $this->fetchSingle(sprintf("select * from fsa where id = %d",$value));
						if (!$f_type["downtown"]) {
							$tmp_valid = false;
							$valid = false;
							$outer->addFormError(sprintf("This vehicle type cannot deliver to FSA %s",$f_type["fsa"]));
						}
					}
					if ($tmp_valid) {
						$upd["fsa_id"] = $value;
						$stmt = $this->prepare(sprintf("insert into driver_fsa(%s) values(%s?)",implode(", ",array_keys($upd)),str_repeat("?, ",count($upd)-1)));
						$stmt->bindParams(array_merge(array(str_repeat("s",count($upd))),array_values($upd)));
						$stmt->execute();
					}
				}
				if ($valid)
					$outer->init($this->getTemplate(__FUNCTION__."Success"));
			}
		}
		return $this->ajaxReturn(array('status'=>true,'html'=>$outer->show()));
	}

    /**
     * @return false|string
     * @throws phpmailerException
     */
    function removeFSA() {
		$f_id = array_key_exists("f_id",$_REQUEST) ? $_REQUEST["f_id"] : 0;
		$d_id = array_key_exists("d_id",$_REQUEST) ? $_REQUEST["d_id"] : 0;
		$stmt = $this->prepare(sprintf("delete from driver_fsa where id = %d and driver_id = %d",$f_id,$d_id));
		$status = $stmt->execute();
		$this->addMessage("Record Deleted");
		return $this->ajaxReturn(array("status"=>$status,"html"=>""));
	}

    /**
     * @return array|false|mixed|string|string[]|void
     * @throws phpmailerException
     */
    function zones() {
		$outer = new Forms();
		$outer->init($this->getTemplate(__FUNCTION__));
		$flds = $outer->buildForm($this->getFields(__FUNCTION__));
		$inner = new Forms();
		$inner->init($this->getTemplate(__FUNCTION__."Row"));
		$flds = $inner->buildForm($this->getFields(__FUNCTION__."Row"));
		$result = array();
		if(array_key_exists(__FUNCTION__,$_REQUEST)) $outer->addData($_REQUEST);
		$this->logMessage(__FUNCTION__,sprintf("outer [%s] getData [%s]", print_r($outer,true), $outer->getData("sort")),1);
		$recs = $this->fetchAll(sprintf("select z.*, m.company, m.lastname, m.firstname, concat(m.company, '-', m.lastname,' ',m.firstname) as dname, v.name as vehicle from delivery_zones z, drivers d, members m, vehicles v where d.id = z.driver_id and m.id = d.member_id and v.id = d.vehicle_id order by z.name asc"));
		foreach($recs as $key=>$value) {
			$inner->addData($value);
			$result[] = $inner->show();
		}
		$outer->addTag("zones",implode("",$result),false);
		$outer->addTag('heading',$this->getHeader(),false);
		if ($this->isAjax())
			return $this->ajaxReturn(array('status'=>true,'html'=>$outer->show()));
		else
			return $this->show($outer->show());
	}

    /**
     * @return false|string
     * @throws phpmailerException
     */
    function editZone() {
		$outer = new Forms();
		$outer->init($this->getTemplate(__FUNCTION__),array("method"=>"POST","name"=>"editZone","action"=>"/modit/ajax/editZone/drivers"));
		$flds = $this->getFields(__FUNCTION__);
		$z_id = array_key_exists("z_id",$_REQUEST) ? $_REQUEST["z_id"] : 0;
		if (!$zone = $this->fetchSingle(sprintf("select z.*, v.name as vehicle_name from delivery_zones z, vehicles v where z.id = %d and v.id = z.vehicle_id",$z_id))) {
			$zone = array("id"=>0,"vehicle_id"=>0);
		}
		else $flds["vehicle_id"]["type"] = "hidden";
		$zone["fsa"] = $this->fetchScalarAll(sprintf("select fsa_id from delivery_zones_fsa where delivery_zone_id = %d",$z_id));
		$flds['fsa']['sql'] = sprintf("select f.id,concat(z.title,' - ',f.fsa) from fsa f, zones z, zone_fsa zf where zf.fsa_id = f.id and z.id = zf.zone_id and (f.id in (select dz.id from delivery_zones_fsa dz where dz.delivery_zone_id = %d) or f.id not in (select dz.id from delivery_zones_fsa dz where delivery_zone_id != %d)) order by 2",$z_id,$z_id);
		$flds['fsa']['sql'] = sprintf("select f.id, f.fsa from fsa f, zone_fsa zf, zones z where f.enabled = 1 and f.deleted = 0 and zf.fsa_id = f.id and z.id = zf.zone_id and f.id not in (select fsa_id from delivery_zones_fsa where vehicle_id = %d and delivery_zone_id != %d) order by 2", $zone["vehicle_id"], $z_id);
		$outer->setData("z_id",$z_id);
		$outer->addData($zone);
		$flds = $outer->buildForm($flds);
		if (count($_POST) > 0 && array_key_exists(__FUNCTION__,$_POST)) {
			$outer->addData($_POST);
			if ($valid = $outer->validate()) {
				$upd = array();
				foreach($flds as $key=>$fld) {
					if (!(array_key_exists('database',$fld) && $fld['database'] == false)) {
						$upd[$key] = $outer->getData($fld["name"]);
					}
				}
				if ($z_id == 0)
					$stmt = $this->prepare(sprintf("insert into delivery_zones(%s) values(%s?)",implode(", ",array_keys($upd)), str_repeat("?, ",count($upd)-1)));
				else
					$stmt = $this->prepare(sprintf("update delivery_zones set %s=? where id = %d",implode("=?, ",array_keys($upd)),$z_id));
				$stmt->bindParams(array_merge(array(str_repeat('s', count($upd))),array_values($upd)));
				$this->beginTransaction();
				$valid = $valid & $stmt->execute();
				if ($z_id == 0) $z_id = $this->insertId();
				$outer->setData("z_id",$z_id);
				$exists = $this->fetchScalarAll(sprintf("select fsa_id from delivery_zones_fsa where delivery_zone_id = %d",$z_id));
				$toDelete = array_diff($exists,array_key_exists("fsa",$_POST) ? $_POST["fsa"]: array(0=>0));
				$toAdd = array_diff(array_key_exists("fsa",$_POST) ? $_POST["fsa"] : array(0=>0),$exists);
				$vehicle_type = $this->fetchScalar(sprintf("select vehicle_id from drivers d where d.id = %d",$_POST["driver_id"]));
				$this->logMessage(__FUNCTION__,sprintf(" adding [%s] removing [%s]",print_r($toAdd,true),print_r($toDelete,true)),1);
				foreach($toAdd as $key=>$value) {
					$tmp = $this->execute(sprintf("insert into delivery_zones_fsa(fsa_id,delivery_zone_id,vehicle_id) values(%d,%d,%d)",$value,$z_id,$vehicle_type));
					if (!$tmp) $outer->addFormError(sprintf("FSA %s is probably already assigned to the same vehicle type",$this->fetchScalar(sprintf("select fsa from fsa where id = %d",$value))));
					$valid &= $tmp;
				}
				$valid = $valid & $this->execute(sprintf("delete from delivery_zones_fsa where fsa_id in (%s) and delivery_zone_id = %d",implode(", ",array_merge(array(0),array_values($toDelete))), $z_id));
				$exists = $this->fetchScalarAll(sprintf("select fsa_id from delivery_zones_fsa where delivery_zone_id = %d",$z_id));
				$outer->setData("zones",$exists);
				if ($valid) {
					$this->commitTransaction();
					$outer->init($this->getTemplate(__FUNCTION__."Success"));
				}
				else {
					$outer->addFormError("An Error Occurred");
					$this->rollbackTransaction();
				}
			}
		}
		return $this->ajaxReturn(array("status"=>true,"html"=>$outer->show()));
	}

    /**
     * @return false|string
     * @throws phpmailerException
     */
    function zoneDrivers() {
		$v_id = array_key_exists("vehicle_id",$_REQUEST) ? $_REQUEST["vehicle_id"] : 0;
		$drivers = new select();
		$drivers->addAttributes(array("name"=>"driver_id","required"=>true,"sql"=>sprintf("select d.id, concat(company, '-', lastname,', ',firstname) from drivers d, members m where d.vehicle_id = %d and m.id = d.member_id order by 2",$v_id)));
		return $this->ajaxReturn(array("status"=>true,"html"=>$drivers->show()));
	}

    /**
     * @return false|string
     * @throws phpmailerException
     */
    function zoneFSA() {
		$v_id = array_key_exists("v_id",$_REQUEST) ? $_REQUEST["v_id"] : 0;
		$z_id = array_key_exists("z_id",$_REQUEST) ? $_REQUEST["z_id"] : 0;
		$fsa = new select();
		$exists = $this->fetchOptions(sprintf("select f.id, f.fsa from fsa f, delivery_zones_fsa zf where zf.delivery_zone_id = %d",$z_id ));
		$fsa->addAttributes(array("required"=>true,"name"=>"fsa[]","multiple"=>true,"sql"=>sprintf("select f.id, concat(z.title,' - ',f.fsa) from fsa f, zone_fsa zf, zones z where zf.fsa_id = f.id and z.id = zf.zone_id and f.id not in (select fsa_id from delivery_zones_fsa where vehicle_id = %d and delivery_zone_id != %d) order by 2", $v_id, $z_id)));
		$this->logMessage(__FUNCTION__,sprintf("fsa [%s] exists [%s]",print_r($fsa,true),print_r($exists,true)),1);
		return $this->ajaxReturn(array("status"=>true,"html"=>$fsa->show($exists)));
	}

    /**
     * @return array|false|mixed|string|string[]|void
     * @throws phpmailerException
     */
    public function payments() {
		$outer = new Forms();
		$outer->init($this->getTemplate(__FUNCTION__));
		$flds = $outer->buildForm($this->getFields(__FUNCTION__));
		$outer->setData("ct",0);
		if (count($_POST)==0) {
			if ($this->checkArray("formData:paymentSearch",$_SESSION)) $_POST = $_SESSION["formData"]["paymentSearch"];
		}
		if (count($_POST) > 0 && array_key_exists(__FUNCTION__,$_POST)) {
			$srch = array();
			$outer->addData($_POST);
			if ($outer->validate())
				$_SESSION["formData"]["paymentSearch"] = $_POST;
			foreach($_POST as $key=>$value) {
				switch($key) {
					case "completed":
						if ($value != "") {
							$srch[$key] = sprintf("c.completed = %d",$value);
							$this->logMessage(__FUNCTION__,sprintf("completed [%s] [%s]", print_r($value,true), print_r($srch,true)), 1);
							if (array_key_exists("start_date",$srch)) {
								$this->logMessage(__FUNCTION__,sprintf("reset start date"),1);
								if ($outer->getData("completed")==1)
									$srch["start_date"] = sprintf("date_format(actual_date,'%%Y-%%m-%%d') >= '%s'",$outer->getData("start_date"));
								else
									$srch["start_date"] = sprintf("date_format(scheduled_date,'%%Y-%%m-%%d') >= '%s'",$outer->getData("start_date"));
							}
							if (array_key_exists("end_date",$srch)) {
								$this->logMessage(__FUNCTION__,sprintf("reset end date"),1);
								if ($outer->getData("completed")==1)
									$srch["end_date"] = sprintf("date_format(actual_date,'%%Y-%%m-%%d') <= '%s'",$outer->getData("end_date"));
								else
									$srch["end_date"] = sprintf("date_format(scheduled_date,'%%Y-%%m-%%d') <= '%s'",$outer->getData("end_date"));
							}
						}
						else {
							$srch["start_date"] = sprintf("date_format(scheduled_date,'%%Y-%%m-%%d') >= '%s'",$outer->getData("start_date"));
							$srch["end_date"] = sprintf("date_format(scheduled_date,'%%Y-%%m-%%d') <= '%s'",$outer->getData("end_date"));
						}
						break;
					case "driver_id":
						if ($value > 0)
							$srch[$key] = sprintf("c.driver_id = %d",$value);
						break;
					case "start_date":
						$srch[$key] = sprintf("date_format(actual_date,'%%Y-%%m-%%d') >= '%s'",$outer->getData("start_date"));
						$this->logMessage(__FUNCTION__,sprintf("set start date"),1);
						break;
					case "end_date":
						$srch[$key] = sprintf("date_format(actual_date,'%%Y-%%m-%%d') <= '%s'",$outer->getData("end_date"));
						$this->logMessage(__FUNCTION__,sprintf("set end date"),1);
						break;
					case "service_type":
						if ($value != "")
							$srch[$key] = sprintf("service_type = '%s'",$value);
						break;
					case "product_id":
						if ($value > 0)
							$srch[$key] = sprintf("order_id in (select order_id from order_lines where deleted = 0 and product_id = %d)",$value);
						break;
					case "order_id":
						if ($value > 0) {
							$srch = array();	//only search by order # if supplied
							$srch[$key] = sprintf("order_id = %d", $value);
							break 2;
						}
						break;
					case "overridden":
						if ($value !="")
							$srch[$key] = sprintf("c.id in (select delivery_id from custom_delivery_commissions where overridden = %d)", $value);
					case "paid":
						if ($value != "")
							$srch[$key] = sprintf("paid = %d",$value);
						break;
					default:
				}
			}
			if (array_key_exists('pagenum',$_REQUEST)) 
				$pageNum = $_REQUEST['pagenum'];
			else
				$pageNum = 1;	// no 0 based calcs
			if ($pageNum <= 0) $pageNum = 1;
			if (array_key_exists('pager',$_REQUEST)) 
				$perPage = $_REQUEST['pager'];
			else {
				$perPage = $this->m_perrow;
			}
			$outer->setData('pager',$perPage);
			if (array_key_exists("driver_id",$_REQUEST) && $_REQUEST["driver_id"] > 0) {
				$sql = sprintf("select count(c.id) from custom_delivery c, drivers d, members m, orders o where o.id = c.order_id and o.order_status & ~%d = 0 and d.id = c.driver_id and m.id = d.member_id and %s", STATUS_SHIPPED | STATUS_PROCESSING,  implode(" and ",$srch));
				$count = $this->fetchScalar($sql);
				$sortby = $_POST["sortby"];
				$sortorder = $_POST["sortorder"];
				$sql = sprintf("select c.*, concat(m.company, '-', m.lastname,' ',m.firstname) as name, m.company, m.firstname, m.lastname, o.custom_commissionable_amt as value from custom_delivery c, drivers d, members m, orders o where o.id = c.order_id and d.id = c.driver_id and m.id = d.member_id and o.order_status & ~%d = 0 and %s order by %s %s",
					STATUS_SHIPPED | STATUS_PROCESSING, implode(" and ",$srch), $sortby,$sortorder);
				$recs = $this->fetchAll($sql);
				$inner = new Forms();
				$inner->init($this->getTemplate(__FUNCTION__."Row"));
				$flds = $inner->buildForm($this->getFields(__FUNCTION__."Row"));
				$list = array();
				$pmtTotal = 0;
				$invTotal = 0;
				$dt = date(DATE_ATOM);
				$pdCt = 0;
				$pdAmt = 0;
				foreach($recs as $key=>$rec) {
					if ($outer->getData("mark_as_paid") == 1 && $rec["paid"] == 0) {
						$rec["paid"] = 1;
						$rec["paid_timestamp"] = $dt;
						$pdCt += 1;
						$pdAmt += $rec["payment"];
						$this->execute(sprintf("update custom_delivery set paid=1, paid_timestamp='%s' where id = %d", $dt, $rec["id"]));
					}
					$inner->addData($rec);
					$list[] = $inner->show();
					$pmtTotal += $rec["payment"];
					$invTotal += $rec["value"];
				}
				$tmp = new Forms();
				$tmp->init($this->getTemplate(__FUNCTION__."Driver"));
				$tmp->addTag("payment",implode("",$list),false);
				$outer->setData("ct",count($recs));
				$outer->setData("mark_as_paid",0);
				if ($pdCt > 0) {
					$outer->addFormSuccess(sprintf("Marked %d deliveries paid for %s.", $pdCt, money_format('%i', $pdAmt)));
				}
			}
			else {
				$sortby = $_POST["sortby"];
				$sortorder = $_POST["sortorder"];

				if ($outer->getData("mark_as_paid") == 1) {
					$dt = date(DATE_ATOM);
					$sql = sprintf("select distinct c.id from custom_delivery c, drivers d, members m, orders o where o.id = c.order_id and d.id = c.driver_id and m.id = d.member_id and c.paid = 0 and %s order by c.id",
					implode(" and ",$srch));
					$recs = $this->fetchScalarAll($sql);
					$this->logMessage(__FUNCTION__, sprintf("marking actions [%s] as paid", print_r($recs,true)), 1);
					foreach($recs as $k=>$v) {
						$this->execute(sprintf("update custom_delivery set paid=1, paid_timestamp = '%s' where id = %d", $dt, $v));
					}
				}

				$sql = sprintf("select count(c.id) from custom_delivery c, drivers d, members m, orders o where o.id = c.order_id and o.order_status & ~%d = 0 and d.id = c.driver_id and m.id = d.member_id and %s", STATUS_SHIPPED | STATUS_PROCESSING, implode(" and ",$srch));
				$count = $this->fetchScalar($sql);
				$sql = sprintf("select count(c.id) as ct, d.id, concat(m.company, '-', m.lastname,' ',m.firstname) as name, m.company, m.firstname, m.lastname, sum(o.custom_commissionable_amt) as value, sum(c.payment) as payment from custom_delivery c, drivers d, members m, orders o where o.id = c.order_id and d.id = c.driver_id and m.id = d.member_id and o.order_status & ~%d = 0 and %s group by d.id order by %s %s",
					STATUS_PROCESSING | STATUS_SHIPPED, implode(" and ",$srch),$sortby,$sortorder);
				$recs = $this->fetchAll($sql);
				$inner = new Forms();
				$inner->init($this->getTemplate(__FUNCTION__."SummaryRow"));
				$flds = $inner->buildForm($this->getFields(__FUNCTION__."SummaryRow"));
				$list = array();
				$pmtTotal = 0;
				$invTotal = 0;
				$inner->addData($_REQUEST);
				foreach($recs as $key=>$rec) {
					$inner->addData($rec);
					$list[] = $inner->show();
					$pmtTotal += $rec["payment"];
					$invTotal += $rec["value"];
				}
				$tmp = new Forms();
				$tmp->init($this->getTemplate(__FUNCTION__."SummaryTable"));
				$tmp->addTag("payment",implode("",$list),false);
				$outer->setData("ct",count($recs));
			}
			$tmp->addElement("paymentTotal","currency",array("value"=>$pmtTotal));
			$tmp->addElement("commissionTotal","currency",array("value"=>$invTotal));
			$outer->addTag("table",$tmp->show(),false);
			$outer->addTag("articles",implode("",$list),false);
		}
		if ($this->isAjax())
			return $this->ajaxReturn(array('html'=>$outer->show(),'status'=>true));
		else
			return $this->show($outer->show());
	}

    /**
     * @return false|string
     * @throws phpmailerException
     */
    public function editPayment() {
		$this->logMessage(__FUNCTION__,sprintf("post is [%s]", print_r($_POST,true)),1);
		$outer = new Forms();
		$outer->init($this->getTemplate(__FUNCTION__));
		$flds = $outer->buildForm($this->getFields(__FUNCTION__));
		$p_id = array_key_exists("p_id",$_REQUEST) ? $_REQUEST["p_id"] : 0;
		$data = $this->fetchSingle(sprintf("select c.*, o.custom_commissionable_amt, m.company, m.firstname, m.lastname, a.line1, a.company, a.addresstype from custom_delivery c, orders o, drivers d, members m, addresses a where d.id = c.driver_id and m.id = d.member_id and o.id = c.order_id and a.ownerid = c.order_id and a.ownertype = 'order' and a.addresstype = IF(c.service_type='P',%d,%d) and c.id = %d",ADDRESS_PICKUP,ADDRESS_DELIVERY,$p_id));
		$data["details"] = $this->getCommissionDetails($p_id);
		$outer->addData($data);
		if (array_key_exists(__FUNCTION__,$_POST) && $_POST[__FUNCTION__] == 1) {
			$old_rate = $data["percent_of_delivery"];
			$outer->addData($_POST);
			if ($outer->validate()) {
				$new_rate = $outer->getData("percent_of_delivery");
				$old = $this->fetchSingle(sprintf("select * from orders where id = %d",$data["order_id"]));
				$outer->setData("payment",round($old["custom_commissionable_amt"]*$outer->getData("percent_of_delivery")/100,2));
				$upd = array();
				foreach($flds as $key=>$fld) {
					if (!(array_key_exists('database',$fld) && $fld['database'] == false)) {
						$upd[$key] = $outer->getData($fld["name"]);
					}
				}
				$stmt = $this->prepare(sprintf("update custom_delivery set %s=? where id = %d", implode("=?, ",array_keys($upd)),$p_id));
				$stmt->bindParams(array_merge(array(str_repeat("s",count($upd))),array_values($upd)));
				$stmt->execute();
				$reciprocal = $this->fetchScalar(sprintf("select id from custom_delivery where order_id=%d and id != %d", $data["order_id"], $p_id));
				$this->execute(sprintf("update custom_delivery set percent_of_delivery = %s where id = %d", 100 - $_POST["percent_of_delivery"], $reciprocal));
				$this->execute(sprintf("update custom_delivery_commissions set split_rate = %s where delivery_id = %d", 100 - $_POST["percent_of_delivery"], $reciprocal));
				$this->calculateCommissions($data["order_id"]);
				$data["details"] = $this->getCommissionDetails($p_id);
				$data = $this->fetchSingle(sprintf("select c.*, o.custom_commissionable_amt, m.firstname, m.lastname, a.line1, a.company, a.addresstype from custom_delivery c, orders o, drivers d, members m, addresses a where d.id = c.driver_id and m.id = d.member_id and o.id = c.order_id and a.ownerid = c.order_id and a.ownertype = 'order' and a.addresstype = IF(c.service_type='P',%d,%d) and c.id = %d",ADDRESS_PICKUP,ADDRESS_DELIVERY,$p_id));
				$outer->addData($data);
/*
				if (array_key_exists("adjust",$_POST) && $_POST["adjust"] == 1) {
					$_POST["p_id"] = $this->fetchScalar(sprintf("select id from custom_delivery where order_id=%d and id != %d", 
					$_POST["percent_of_delivery"] = ;
					$_POST["adjust"] = 0;
					$throwaway = $this->editPayment();
					$this->logMessage(__FUNCTION__,sprintf("throwaway [%s]", print_r($throwaway,true)),1);
				}
*/
				$outer->init($this->getTemplate(__FUNCTION__."Success"));
			}
			else {
				$outer->addError("Form Validation Failed");
			}
		}
		return $this->ajaxReturn(array("html"=>$outer->show(),"status"=>true));
	}

    /**
     * @return array|false|mixed|string|string[]
     * @throws phpmailerException
     */
    function mapIt() {
		$d_id = array_key_exists("d_id",$_REQUEST) ? $_REQUEST["d_id"] : 0;
		$dt = array_key_exists("scheduled_date",$_REQUEST) ? date("Y-m-d",strtotime($_REQUEST["scheduled_date"])) : date("Y-m-d");
		$driver = $this->fetchSingle(sprintf("select m.*, a.* from drivers d, members m left join addresses a on a.ownertype = 'member' and a.ownerid = m.id where d.id = %d and m.id = d.member_id limit 1",$d_id));
		$outer = new Forms();
		$outer->init($this->getTemplate("mapIt"));
		$outer->addData($driver);
		$flds = $this->getFields("mapIt");
		$packages = $this->fetchAll(sprintf("select c.*, a.id as a_id, p1.code deliveryCode, p1.name as deliveryName from custom_delivery c, addresses a, product p1, order_lines o where o.deleted = 0 and date(c.scheduled_date) = '%s' and c.driver_id = %d and a.ownerid = c.order_id and a.addresstype = IF(c.service_type='P',%d,%d) and o.order_id = c.order_id and o.custom_package = 'S' and p1.id = o.product_id order by driver_sequence",$dt,$d_id,ADDRESS_PICKUP,ADDRESS_DELIVERY));
		$inner = new Forms();
		$inner->init($this->getTemplate(__FUNCTION__."Address"));
		$flds = $inner->buildForm($flds);
		$result = array();
		$addresses = array();
		foreach($packages as $key=>$value) {
			$address = $this->fetchSingle(sprintf("select * from addresses where id = %d",$value["a_id"]));
			$addresses[$value["id"]] = $address;
			$value["address"] = Address::formatData($address);
			$value["sequence"] = $key;
			$inner->reset();
			$inner->addData($value);
			$result[] = $inner->show();
		}
		$outer->addTag("count",count($packages));
		$outer->addTag("addresses",implode("",$result),false);
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
			//$this->logMessage(__FUNCTION__,sprintf("route is [%s]",print_r($r["routes"]["features"][0]["geometry"]["paths"],true)),1);
			$this->logMessage(__FUNCTION__,sprintf("route is [%s]",print_r($r,true)),1);
			foreach($r["routes"]["features"][0]["geometry"]["paths"] as $key=>$stop) {
				foreach($stop as $subkey=>$leg) {
					$inner->reset();
					$inner->addData($leg);
					if ($key < 2)
						$this->logMessage(__FUNCTION__,sprintf("leg is [%s] inner is [%s]",print_r($leg,true),print_r($inner,true)),1);
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
				//$this->logMessage(__FUNCTION__,sprintf("arrival [%s] [%s] [%s]",$st,$ed,substr($dir["attributes"]["text"],$st+10,$ed-$st-10)),1);
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

    /**
     * @return false|string
     * @throws phpmailerException
     */
    function showPayments() {
		$outer = new Forms();
		$outer->init($this->getTemplate(__FUNCTION__));
		$flds = $outer->buildForm($this->getFields(__FUNCTION__));
		$outer->addData($_REQUEST);
		$outer->validate();

		$inner = new Forms();
		$inner->init($this->getTemplate(__FUNCTION__."Row"));
		$flds = $inner->buildForm($this->getFields(__FUNCTION__."Row"));
		$srch["status"] = sprintf("o.order_status & ~%d = 0", STATUS_SHIPPED | STATUS_PROCESSING);
		foreach($_REQUEST as $key=>$value) {
			switch($key) {
				case "completed":
					if ($value != "") {
						$srch[$key] = sprintf("c.completed = %d",$value);
						if (array_key_exists("start_date",$srch)) {
							$this->logMessage(__FUNCTION__,sprintf("reset start date"),1);
							if ($outer->getData("completed")==1)
								$srch["start_date"] = sprintf("date_format(actual_date,'%%Y-%%m-%%d') >= '%s'",$outer->getData("start_date"));
							else
								$srch["start_date"] = sprintf("date_format(scheduled_date,'%%Y-%%m-%%d') >= '%s'",$outer->getData("start_date"));
						}
						if (array_key_exists("end_date",$srch)) {
							$this->logMessage(__FUNCTION__,sprintf("reset end date"),1);
							if ($outer->getData("completed")==1)
								$srch["end_date"] = sprintf("date_format(actual_date,'%%Y-%%m-%%d') <= '%s'",$outer->getData("end_date"));
							else
								$srch["end_date"] = sprintf("date_format(scheduled_date,'%%Y-%%m-%%d') <= '%s'",$outer->getData("end_date"));
						}
					}
					else {
						$srch["start_date"] = sprintf("date_format(scheduled_date,'%%Y-%%m-%%d') >= '%s'",$outer->getData("start_date"));
						$srch["end_date"] = sprintf("date_format(scheduled_date,'%%Y-%%m-%%d') <= '%s'",$outer->getData("end_date"));
					}
					$this->logMessage(__FUNCTION__,sprintf("completed [%s] [%s]", print_r($value,true), print_r($srch,true)), 1);
					break;
				case "driver_id":
					if ($value > 0)
						$srch[$key] = sprintf("c.driver_id = %d",$value);
					break;
				case "start_date":
					$srch[$key] = sprintf("date_format(actual_date,'%%Y-%%m-%%d') >= '%s'",$outer->getData("start_date"));
					break;
				case "end_date":
					$srch[$key] = sprintf("date_format(actual_date,'%%Y-%%m-%%d') <= '%s'",$outer->getData("end_date"));
					break;
				case "service_type":
					if ($value != "")
						$srch[$key] = sprintf("service_type = '%s'",$value);
					break;
				case "delivery_type":
					if ($value > 0)
						$srch[$key] = sprintf("delivery_type = %d",$value);
					break;
					case "order_id":
						if ($value > 0) {
							$srch = array();
							$srch[$key] = sprintf("order_id = %d", $value);
							break 2;
						}
						break;
				default:
			}
		}
		$driver = $this->fetchSingle(sprintf("select m.* from members m, drivers d where d.id = %d and m.id = d.member_id", $_REQUEST["driver_id"]));
		$sql = sprintf("select count(c.id) as ct from custom_delivery c, drivers d, members m, orders o where o.id = c.order_id and d.id = c.driver_id and m.id = d.member_id and %s",implode(" and ",$srch));
		$count = $this->fetchScalar($sql);
		$outer->addData(array("driver"=>$driver,"ct"=>$count));
		$sortby = $_REQUEST["sortby"];
		$sortorder = $_REQUEST["sortorder"];
		$sql = sprintf("select c.*, o.custom_commissionable_amt, sum(calculated) as calculated, sum(if(overridden,overridden,calculated)) as override from custom_delivery c, drivers d, orders o, custom_delivery_commissions cm where cm.delivery_id = c.id and o.id = c.order_id and d.id = c.driver_id and %s  group by c.id order by c.id asc",
			implode(" and ",$srch),$sortby,$sortorder);
		$rows = array();
		$recs = $this->fetchAll($sql);
		$pmt = 0;
		$override = 0;
		$base = 0;
		foreach($recs as $key=>$rec) {
			$inner->addData($rec);
			$rows[] = $inner->show();
			$pmt += $rec["custom_commissionable_amt"];
			$base += $rec["calculated"];
			$override += $rec["override"];
		}
		$outer->addTag("xpayments",implode("",$rows),false);
		$outer->addData(array("base"=>$pmt, "commission"=>$base,"override"=>$override));
		return $this->ajaxReturn(array('html'=>$outer->show(),'status'=>true));
	}

    /**
     * @return array|false|mixed|string|string[]|void
     * @throws phpmailerException
     */
    function deductions() {
		$outer = new Forms();
		$outer->init($this->getTemplate(__FUNCTION__));
		$flds = $outer->buildForm($this->getFields(__FUNCTION__));
		$outer->addData($_REQUEST);
		$outer->validate();

		$inner = new Forms();
		$inner->init($this->getTemplate(__FUNCTION__."Row"));
		$flds = $inner->buildForm($this->getFields(__FUNCTION__."Row"));
		
		$drivers = $this->fetchAll(sprintf("select d.*, m.company, m.firstname, m.lastname from drivers d, members m where m.id = d.member_id and d.enabled = 1 and d.deleted = 0 order by m.company, m.lastname, m.firstname"));
		$recs = array();
		foreach($drivers as $k=>$d) {
			$inner->addData($d);
			$recs[] = $inner->show();
		}
		$outer->setData("drivers",implode("",$recs));
		$outer->addTag('heading',$this->getHeader(),false);
		if ($this->isAjax()) {
			return $this->ajaxReturn(array('status'=>true,'html'=>$outer->show()));
		}
		else return $this->show($outer->show());
	}

    /**
     * @param $d_id
     * @return array
     * @throws phpmailerException
     */
    function getZones($d_id) {
		$outer = new Forms();
		$outer->init($this->getTemplate(__FUNCTION__));
		$flds = $outer->buildForm($this->getFields(__FUNCTION__));
		$zones = $this->fetchAll(sprintf("select * from delivery_zones where driver_id = %d order by name", $d_id));
		$recs = array();
		foreach($zones as $k=>$v) {
			$outer->addData($v);
			$recs[] = $outer->show();
		}
		return $recs;
	}

    /**
     * @param $p_id
     * @return array|mixed|string|string[]
     * @throws phpmailerException
     */
    function getCommissionDetails($p_id = NULL) {
		if (is_null($p_id)) $p_id = array_key_exists("p_id",$_REQUEST) ? $_REQUEST["p_id"] : 0;
		$outer = new Forms();
		$outer->init($this->getTemplate(__FUNCTION__));
		$inner = new Forms();
		$inner->init($this->getTemplate(__FUNCTION__."Row"));
		$flds = $outer->buildForm($this->getFields(__FUNCTION__));
		$flds = $inner->buildForm($this->getFields(__FUNCTION__."Row"));
		$dtls = $this->fetchAll(sprintf("select cdc.*, p.name, ol.custom_package, cd.percent_of_delivery, p.custom_commission from custom_delivery_commissions cdc, product p, order_lines ol, custom_delivery cd where ol.deleted = 0 and cd.id = cdc.delivery_id and cdc.delivery_id = %d and p.id = cdc.product_id and ol.order_id = cd.order_id and ol.product_id = p.id order by ol.line_id", $p_id));
		$recs = array();
		$calc = 0;
		$pmt = 0;
		foreach($dtls as $k=>$v) {
			$inner->addData($v);
			$recs[] = $inner->show();
			$calc += $v["calculated"];
			$pmt += $v["overridden"] ? $v["value"] : $v["calculated"];
		}
		$outer->setData("calc",$this->my_money_format($calc));
		$outer->setData("override",$this->my_money_format($pmt));
		$outer->setData("details",implode("",$recs));
		return $outer->show();
	}

    /**
     * @return false|string
     * @throws phpmailerException
     */
    function editCommission() {
		$outer = new Forms();
		$outer->init($this->getTemplate(__FUNCTION__));
		$flds = $outer->buildForm($this->getFields(__FUNCTION__));
		$rec = $this->fetchSingle(sprintf("select cdc.*, p.name from custom_delivery_commissions cdc, product p, custom_delivery cd where cdc.id = %d and cd.id = cdc.delivery_id and p.id = cdc.product_id", $_REQUEST["c_id"]));
		$outer->addData($rec);
		if (array_key_exists(__FUNCTION__,$_REQUEST)) {
			$outer->addData($_REQUEST);
			if ($outer->validate()) {
				$values = array();
				foreach($flds as $k=>$fld) {
					if (!(array_key_exists('database',$fld) && $fld['database'] == false)) {
						$values[$fld['name']] = $outer->getData($fld['name']);
					}
				}
				$stmt = $this->prepare(sprintf("update custom_delivery_commissions set %s=? where id = %d", implode("=?, ", array_keys($values)), $_REQUEST["c_id"]));
				$stmt->bindParams(array_merge( array(str_repeat("s", count($values))), array_values($values) ));
				if ($valid = $stmt->execute()) {
					$outer->init($this->getTemplate(__FUNCTION__."Success"));
					$outer->addFormSuccess("Record Updated");
					$this->execute(sprintf("update custom_delivery set payment = (select sum(if(overridden,value,calculated)) from custom_delivery_commissions where delivery_id = %d) where id = %d", $rec["delivery_id"], $rec["delivery_id"]));
				}
			}
		}
		return $this->ajaxReturn(array("status"=>true, "html"=>$outer->show()));
	}

    /**
     * @return false|string
     * @throws phpmailerException
     */
    function zoneDelete() {
		$outer = new Forms();
		$outer->init($this->getTemplate(__FUNCTION__));
		$z_id = array_key_exists("z_id", $_REQUEST) ? $_REQUEST["z_id"] : 0;
		$status = false;
		if ($rec = $this->fetchSingle(sprintf("select * from delivery_zones where id = %d", $z_id))) {
			$ct = $this->fetchScalar(sprintf("select count(0) from delivery_zones_fsa where delivery_zone_id = %d", $z_id));
			if ($ct == 0) {
				$status = $this->execute(sprintf("delete from delivery_zones where id = %d", $z_id));
			}
			$outer->addData($rec);
			$outer->setData('ct',$ct);
		}
		return $this->ajaxReturn(array("status"=>$status, "html"=>$outer->show()));
	}

    /**
     * @param $asFile
     * @return bool|string|void
     * @throws phpmailerException
     */
    function printPayout($asFile = null ) {
		$outer = new Forms();
		$outer->init($this->getTemplate(__FUNCTION__));
		$flds = $outer->buildForm($this->getFields(__FUNCTION__));

		if ($this->checkArray("formData:paymentSearch",$_SESSION)) {
			$_POST = $_SESSION["formData"]["paymentSearch"];
			$outer->addData($_POST);
			$outer->validate();

			$pdf = new TCPDF('P', PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
			$pdf->SetMargins(10, 10, 10, true);
			$pdf->SetCreator(PDF_CREATOR);
			$pdf->SetAuthor('KJV Couriers');
			$pdf->SetTitle(sprintf('Payout Report %s - %s',date("d-M-Y", strtotime($outer->getData("start_date"))), 
					date("d-M-Y", strtotime($outer->getData("end_date")))));
			$pdf->SetSubject('KJV Courier Payout');
			$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
			$pdf->SetHeaderData("logos/kjv-courier.jpg", 30, sprintf("Driver Payout"), "KJV Couriers\nkjvcourier.com");
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
			$srch = array("a"=>"d.id = cd.driver_id", "b"=>"m.id = d.member_id", "c"=>"ol.custom_package='S'", "d"=>"p.id = ol.product_id", "e"=>"ol.order_id = cd.order_id", 
				"f"=>"cdc.delivery_id = cd.id","g"=>"ol.deleted = 0","h"=>sprintf("o.order_status & ~%d = 0", STATUS_PROCESSING | STATUS_SHIPPED), "i" => "o.id = cd.order_id" );
			foreach($_POST as $key=>$value) {
				switch($key) {
					case "completed":
						if ($value != "") {
							$srch[$key] = sprintf("cd.completed = %d",$value);
							$this->logMessage(__FUNCTION__,sprintf("completed [%s] [%s]", print_r($value,true), print_r($srch,true)), 1);
							if (array_key_exists("start_date",$srch)) {
								$this->logMessage(__FUNCTION__,sprintf("reset start date"),1);
								if ($outer->getData("completed")==1)
									$srch["start_date"] = sprintf("date_format(actual_date,'%%Y-%%m-%%d') >= '%s'",date("Y-m-d", strtotime($outer->getData("start_date"))));
								else
									$srch["start_date"] = sprintf("date_format(scheduled_date,'%%Y-%%m-%%d') >= '%s'",date("Y-m-d",strtotime($outer->getData("start_date"))));
							}
							if (array_key_exists("end_date",$srch)) {
								$this->logMessage(__FUNCTION__,sprintf("reset end date"),1);
								if ($outer->getData("completed")==1)
									$srch["end_date"] = sprintf("date_format(actual_date,'%%Y-%%m-%%d') <= '%s'",date("Y-m-d",strtotime($outer->getData("end_date"))));
								else
									$srch["end_date"] = sprintf("date_format(scheduled_date,'%%Y-%%m-%%d') <= '%s'",date("Y-m-d",strtotime($outer->getData("end_date"))));
							}
						}
						else {
							$srch["start_date"] = sprintf("date_format(scheduled_date,'%%Y-%%m-%%d') >= '%s'",date("Y-m-d",strtotime($outer->getData("start_date"))));
							$srch["end_date"] = sprintf("date_format(scheduled_date,'%%Y-%%m-%%d') <= '%s'",date("Y-m-d",strtotime($outer->getData("end_date"))));
						}
						break;
					case "driver_id":
						if ($value > 0)
							$srch[$key] = sprintf("cd.driver_id = %d",$value);
						break;
					case "start_date":
						$srch[$key] = sprintf("date_format(actual_date,'%%Y-%%m-%%d') >= '%s'",date("Y-m-d",strtotime($outer->getData("start_date"))));
						$this->logMessage(__FUNCTION__,sprintf("set start date"),1);
						break;
					case "end_date":
						$srch[$key] = sprintf("date_format(actual_date,'%%Y-%%m-%%d') <= '%s'",date("Y-m-d",strtotime($outer->getData("end_date"))));
						$this->logMessage(__FUNCTION__,sprintf("set end date"),1);
						break;
					case "service_type":
						if ($value != "")
							$srch[$key] = sprintf("service_type = '%s'",$value);
						break;
					case "product_id":
						if ($value > 0)
							$srch[$key] = sprintf("ol.product_id = %d",$value);
						break;
					case "order_id":
						if ($value > 0) {
							//$srch = array(); -- oops, loses join parms
							$srch[$key] = sprintf("cd.order_id = %d", $value);
						}
						break;
					case "overridden":
						if ($value !="")
							$srch[$key] = sprintf("cd.id in (select delivery_id from custom_delivery_commissions where overridden = %d)", $value);
					case "paid":
						if ($value != "")
							$srch[$key] = sprintf("cd.paid = %d",$value);
						break;
					default:
				}
			}
			$sortby = "m.company asc, m.lastname asc, m.firstname asc, if(cd.completed,actual_date,scheduled_date), order_id asc";
			$sql = sprintf("select c.*, concat(m.company, '-', m.lastname,', ',m.firstname) as name, m.firstname, m.lastname, o.custom_commissionable_amt, m.company, p.name as product, o.member_id, d.commission, d.deduction_radio, d.deduction_other
			from custom_delivery c, drivers d, members m, orders o, product p, order_lines ol 
			where ol.deleted = 0 and o.id = c.order_id and d.id = c.driver_id and m.id = d.member_id and ol.order_id = c.order_id and ol.custom_package='S' and p.id = ol.product_id and %s order by %s",
				implode(" and ",$srch),$sortby);

			$sql = sprintf("select cd.*, sum(if(cdc.overridden,cdc.value,cdc.calculated)) as paidAmt, m.firstname, m.lastname, m.company, p.name, d.deduction_radio, d.deduction_other
from custom_delivery_commissions cdc, custom_delivery cd, order_lines ol, product p, members m, drivers d, orders o
where %s group by cd.driver_id, cd.order_id
order by %s", implode(" and ", $srch), $sortby);

			$recs = $this->fetchAll($sql);
			$inner = new Forms();
			$inner->init($this->getTemplate(__FUNCTION__."Row"));
			$flds = $inner->buildForm($this->getFields(__FUNCTION__."Row"));
			$day = new Forms();
			$day->init($this->getTemplate(__FUNCTION__."Day"));
			$flds = $day->buildForm($this->getFields(__FUNCTION__."Day"));
			$list = array();
			$dayTotal = 0;
			$driverTotal = 0;
			$driver = 0;
			$dt = "";
			$list = array();

			//
			//	Major revision about calculating fuel charges.
			//	We are keeping the same calculations but burying the fuel charges into the commissionable total
			//	then applying the driver's %-age to the fuel also - it's an office political issue to hide the fuel charges
			//

			foreach($recs as $key=>$rec) {
				$cdate = date("Y-m-d", strtotime($rec["actual_date"] == "0000-00-00 00:00:00" ? $rec["scheduled_date"] : $rec["actual_date"]));
				if ($driver != $rec["driver_id"]) {
					if ($driver != 0) {
						$day->addData(array("date"=>$dt, "dayTotal"=>$dayTotal));
						$list[] = $day->show();
						$dayTotal = 0;
						$outer->addData(array("payments"=>implode("",$list),"payment"=>$driverTotal,"adjTotal"=>$driverTotal+$outer->getData("deduction_radio")+$outer->getData("deduction_other")));
						$driverTotal = 0;
						$this->logMessage(__FUNCTION__,sprintf("driver break [%s]", print_r($outer,true)),1);
						$pdf->writeHTML($outer->show(), true, false, true, false, '');
						$pdf->addPage();
					}
					$outer->addData($rec);	// sets driver info
					$list = array();
				}
				elseif ($dt != "" and $dt != $cdate) {
					$this->logMessage(__FUNCTION__,sprintf("end day @ %s", $dayTotal),1);
					$day->addData(array("date"=>$dt, "dayTotal"=>$dayTotal));
					$list[] = $day->show();
					$dayTotal = 0;
				}
				$this->logMessage(__FUNCTION__, sprintf("cdate [%s] dt [%s] total [%s] actual [%s] scheduled [%s]", $cdate, $dt, $dayTotal, $rec["actual_date"], $rec["scheduled_date"]),1);
				$rec["pu_address"] = Address::formatData($this->fetchSingle(sprintf("select * from addresses where ownertype='order' and ownerid=%d and addresstype =%d", $rec["order_id"], ADDRESS_PICKUP )));
				$rec["del_address"] = Address::formatData($this->fetchSingle(sprintf("select * from addresses where ownertype='order' and ownerid=%d and addresstype =%d", $rec["order_id"], ADDRESS_DELIVERY )));
				if ($this->fetchScalar(sprintf("select count(cd.id) from custom_delivery cd where cd.order_id = %d and driver_id = %d and DATE(actual_date) between '%s' and '%s'", $rec["order_id"], $rec["driver_id"], date("Y-m-d", strtotime($outer->getData("start_date"))), date("Y-m-d", strtotime($outer->getData("end_date"))))) > 1) $rec["service_type"] = "B";
				$inner->addData($rec);
				$list[] = $inner->show();
				$driver = $rec["driver_id"];
				$dayTotal += $rec["paidAmt"];
				$driverTotal += $rec["paidAmt"];
				$dt = $cdate;
			}
			$day->addData(array("date"=>$dt, "dayTotal"=>$dayTotal));
			$list[] = $day->show();
			$dayTotal = 0;
			$outer->addData(array("payments"=>implode("",$list),"payment"=>$driverTotal,"adjTotal"=>$driverTotal+$outer->getData("deduction_radio")+$outer->getData("deduction_other")));
			$this->logMessage(__FUNCTION__,sprintf("driver break [%s]", print_r($outer,true)),1);
			$pdf->writeHTML($outer->show(), true, false, true, false, '');
		}
		$pdf->lastPage();
		try {
			ob_clean();
			if ($asFile) {
				$driver = $this->fetchSingle(sprintf("select m.* from members m, drivers d where m.id = d.member_id and d.id = %d", $outer->getData("driver_id")));
				$this->logmessage(__FUNCTION__,sprintf("getcwd [%s]", getcwd()),1);
				$fname = sprintf("%s/payout-%s-%s-%s.pdf", str_replace("admin","files",getcwd()), $driver["company"], date("Y-m-d", strtotime($outer->getData("start_date"))), date("Y-m-d",strtotime($outer->getData("end_date"))));
				$pdf->Output($fname, "F");
				return $fname;
			}
			else {
				$pdf->Output(sprintf('payoutReport-%s-%s.pdf',date("d-M-Y", strtotime($_POST["start_date"])), date("d-M-Y", strtotime($_POST["end_date"]))), 'I');
			}
		}
		catch(Exception $err) {
			return print_r($err,true);
		}
	}

    /**
     * @return array|mixed|string|string[]|void
     * @throws phpmailerException
     */
    function editService() {
		$form = new Forms();
		$form->init($this->getTemplate(__FUNCTION__));
		$form->addData($_REQUEST);
		return $this->show($form->show());
	}

    /**
     * @return false|string
     * @throws phpmailerException
     */
    function sendPDF() {
		$file = $this->printPayout(true);
		$paths = explode("/",$file);

		$outer = new Forms();
		$outer->init($this->getTemplate(__FUNCTION__));
		$flds = $outer->buildForm($this->getFields(__FUNCTION__));
		$outer->addData($_REQUEST);
		$outer->setData("driver", $this->fetchSingle(sprintf("select m.*, a.email as email_to from members m left join addresses a on a.ownertype='member' and a.ownerid = m.id and a.addresstype = %d and a.addressbook_id > 0, drivers d where m.id = d.member_id and d.id = %d", ADDRESS_COMPANY, $outer->getData("driver_id"))));
		$outer->validate();
		$mailer = new MyMailer();
		$mailer->Subject = sprintf("Driver Payout - %s to %s", date("d-M-Y", strtotime($outer->getData("start_date"))), date("d-M-Y", strtotime($outer->getData("end_date"))));
		$mailer->Body = $outer->show();
		$mailer->From = "noreply@".HOSTNAME;
		$mailer->FromName = "KJV Courier Services";
		if (defined("DEV") && DEV==1) {
			$mailer->addAddress("ian@kjvcourier.com", sprintf("%s %s", $outer->getData("driver:firstname"), $outer->getData("driver:lastname")));
			$mailer->addAddress("vpileggi@kjvcourier.com", sprintf("%s %s", $outer->getData("driver:firstname"), $outer->getData("driver:lastname")));
		}
		else {
			$e = $outer->getData("driver:email_to");
			if (strlen($e) < 1) $e = $outer->getData("driver:email");
			if (strlen($e) < 1) {
				return $this->ajaxReturn(array("status"=>false,"html"=>"<div class='alert alert-danger'>No valid email address found</div>"));
			}
			$mailer->addAddress($e, sprintf("%s %s", $outer->getData("driver:firstname"), $outer->getData("driver:lastname")));
			$mailer->addBCC("ian@kjvcourier.com","Ian (payout monitor");
		}
		$mailer->IsHTML(true);
		$mailer->AddAttachment($file,$paths[count($paths)-1]);
		if (!($status = $mailer->Send()))
			$this->logMessage(__FUNCTION__,sprintf("mailer [%s] [%s]", print_r($mailer->ErrorInfo,true), print_r($mailer,true)),1);
		unlink($file);

		if ($this->isAjax())
		if ($status)
			return $this->ajaxReturn(array("status"=>true,"html"=>sprintf("<div class='alert alert-success'>Report was emailed</div>")));
		else
			return $this->ajaxReturn(array("status"=>false,"html"=>sprintf("<div class='alert alert-error'>An error occurred</div>")));
			else return sprintf("File sent [%s]", $file);
	}
}

?>
