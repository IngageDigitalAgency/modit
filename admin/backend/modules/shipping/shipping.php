<?php

/**
 * Shipping feature for backend
 */
class shipping extends Backend {

	private $m_content = 'orders';
	private $m_perrow = 5;

    /**
     * @throws phpmailerException
     */
	public function __construct() {
		$this->m_perrow = defined('GLOBAL_PER_PAGE') ? GLOBAL_PER_PAGE : 5;
		$this->M_DIR = 'backend/modules/shipping/';
		$this->setTemplates(
			array(
				'main'=>$this->M_DIR.'shipping.html',
				'articleList'=>$this->M_DIR.'forms/articleList.html',
				'addContent'=>$this->M_DIR.'forms/addContent.html',
				'showSearchForm'=>$this->M_DIR.'forms/searchForm.html',
				'editLine'=>$this->M_DIR.'forms/editLine.html',
				'orderLine'=>$this->M_DIR.'forms/orderLine.html',
				'addressForm'=>$this->M_DIR.'forms/addressForm.html',
				'addressList'=>$this->M_DIR.'forms/addressList.html',
				'editAddress'=>$this->M_DIR.'forms/editAddress.html',
				'editLineResult'=>$this->M_DIR.'forms/editLineResult.html',
				'addItem'=>$this->M_DIR.'forms/addItem.html',
				'showOrder'=>$this->M_DIR.'forms/showOrder.html',
				'header'=>$this->M_DIR.'forms/heading.html',
				'recurringInfo'=>$this->M_DIR.'forms/recurring.html',
				'recurringInfoRow'=>$this->M_DIR.'forms/recurringRow.html',
				'showRecurring'=>$this->M_DIR.'forms/showRecurring.html',
				'pickList'=>$this->M_DIR.'forms/pickList.html',
				'pickListRow'=>$this->M_DIR.'forms/pickListRow.html',
				'packList'=>$this->M_DIR.'forms/packList.html',
				'packListRow'=>$this->M_DIR.'forms/packListRow.html',
				'packTotal'=>$this->M_DIR.'forms/packTotal.html',
				'exportPick'=>$this->M_DIR.'forms/exportPick.html',
				'exportPickRow'=>$this->M_DIR.'forms/exportPickRow.html',
				'exportPack'=>$this->M_DIR.'forms/exportPack.html',
				'exportPackRow'=>$this->M_DIR.'forms/exportPackRow.html',
				'confirmation'=>$this->M_DIR.'forms/confirmation.html',
				//'confirmationRow'=>$this->M_DIR.'forms/confirmationRow.html',
				'confirmationOrder'=>$this->M_DIR.'forms/confirmationOrder.html',
				'confirmationOrderRow'=>$this->M_DIR.'forms/confirmationOrderRow.html',
			)
		);
		$this->setFields(array(
			'header'=>array(),
			'addressForm'=>array(),
			'addressList'=>array(
				'line1'=>array('type'=>'tag','reformatting'=>true),
				'line2'=>array('type'=>'tag','reformatting'=>true),
				'city'=>array('type'=>'tag','reformatting'=>true),
			),
			'editAddress'=>array(
				'options'=>array('method'=>'post','action'=>'/modit/ajax/editAddress/shipping'),
				'editAddress'=>array('type'=>'hidden','value'=>1,'database'=>false),
				'addresstype'=>array('type'=>'select','required'=>true,'sql'=>'select id, value from code_lookups where type = "memberAddressTypes"','prettyName'=>'Address Type'),
				'ownertype'=>array('type'=>'hidden','value'=>'order'),
				//'ownerid'=>array('type'=>'hidden','id'=>'ownerid'),
				'line1'=>array('type'=>'input','required'=>true,'prettyName'=>'Address Line 1'),
				'line2'=>array('type'=>'input','required'=>false),
				'city'=>array('type'=>'input','required'=>true,'prettyName'=>'City'),
				'country_id'=>array('type'=>'countryselect','required'=>true,'id'=>'country_id','prettyName'=>'Country'),
				'province_id'=>array('type'=>'provinceselect','required'=>true,'id'=>'province_id','prettyName'=>'Province'),
				'postalcode'=>array('type'=>'input','required'=>true,'prettyName'=>'Postal Code'),
				'phone1'=>array('type'=>'input'),
				'phone2'=>array('type'=>'input'),
				'fax'=>array('type'=>'input'),
				'addresses'=>array('type'=>'select','database'=>false,'id'=>'addressSelector'),
				'save'=>array('type'=>'submitbutton','database'=>false,'value'=>'Save Address')
			),
			'editLine'=>array(
				'options'=>array('name'=>'pFormEdit','database'=>false),
				'product_id'=>array('type'=>'select','required'=>true,'sql'=>'select id, concat(code," - ",name) from product where deleted = 0 order by code'),
				'coupon_id'=>array('type'=>'select','required'=>false,'sql'=>'select id, concat(code," - ",name) from coupons where deleted = 0 order by code','id'=>'editCouponId'),
				'quantity'=>array('type'=>'input','required'=>true,'validation'=>'number','prettyName'=>'Ordered'),
				'shipped'=>array('type'=>'input','required'=>false,'validation'=>'number','prettyName'=>'Shipped'),
				'deleted'=>array('type'=>'checkbox','value'=>1),
				'price'=>array('type'=>'input','required'=>true,'validation'=>'number','prettyName'=>'Price'),
				'discount_type'=>array('type'=>'select','required'=>false,'lookup'=>'discountTypes'),
				'discount_rate'=>array('type'=>'input','required'=>false,'validation'=>'number','prettyName'=>'Discount Rate'),
				'discount_value'=>array('type'=>'input','required'=>false,'validation'=>'number','prettyName'=>'Discount Amount'),
				'editLine'=>array('type'=>'hidden','value'=>1,'database'=>false),
				'tempEdit'=>array('type'=>'hidden','value'=>0,'database'=>false),
				'fldName'=>array('type'=>'hidden','value'=>'','database'=>false),
				'order_id'=>array('type'=>'tag'),
				'order_date'=>array('type'=>'hidden','database'=>false),
				'submit'=>array('type'=>'submitbutton','value'=>'Save','database'=>false),
				'deleted'=>array('type'=>'checkbox','value'=>1),
				'shipping'=>array('type'=>'input','validation'=>'number'),
				'shipping_only'=>array('type'=>'hidden'),
				'value'=>array('type'=>'input','disabled'=>'disabled'),
				'inventory_id'=>array('type'=>'select','required'=>false),
				'options_id'=>array('type'=>'select','required'=>false)
			),
			'orderLine'=>array(),
			'articleList' => array(
				'id'=>array('type'=>'tag'),
				'order_date'=>array('type'=>'datestamp','mask'=>'d-M-Y h:i:s'),
				'amount'=>array('type'=>'tag'),
				'owing'=>array('type'=>'tag'),
				'deleted'=>array('type'=>'booleanIcon')
			),
			'addContent'=>array(
				'options'=>array('name'=>'addContent','action'=>'/modit/ajax/addContent/shipping','database'=>false),
				'id'=>array('type'=>'tag','database'=>false),
				'member_id'=>array('type'=>'select','required'=>true,'sql'=>'select id, concat(lastname,", ",firstname) from members where deleted = 0 order by lastname, firstname'),
				'order_date'=>array('type'=>'datetimepicker','required'=>true,'AMPM'=>'AMPM','validation'=>'datetime','prettyName'=>'Order Date'),
				'coupon_id'=>array('type'=>'select','required'=>false,'sql'=>'select id, concat(code," - ",name) from coupons where deleted = 0 order by code','id'=>'editCouponId'),
				'value'=>array('type'=>'tag'),
				'authorization_info'=>array('type'=>'textarea','reformat'=>true,'class'=>'mceNoEditor'),
				'authorization_amount'=>array('type'=>'input','required'=>true,'validation'=>'number','prettyName'=>'Authorization Amount'),
				'authorization_amount_ro'=>array('type'=>'tag','required'=>true,'database'=>false),
				'authorization_code'=>array('type'=>'input','prettyName'=>'Authorization Code'),
				'authorization_transaction'=>array('type'=>'input','required'=>false,'prettyName'=>'Transaction Code'),
				'deleted'=>array('type'=>'checkbox','value'=>1),
				'submit'=>array('type'=>'submitbutton','value'=>'Save Order','database'=>false),
				'tempEdit'=>array('type'=>'hidden','value'=>0,'database'=>false),
				'fldName'=>array('type'=>'hidden','value'=>'','database'=>false),
				'discount_rate'=>array('type'=>'input','required'=>false,'validation'=>'number','readonly'=>'readonly','prettyName'=>'Discount Rate'),
				'discount_type'=>array('type'=>'tag'),
				'addContent'=>array('type'=>'hidden','value'=>1,'database'=>false),
				'orderTotal'=>array('type'=>'tag','database'=>false),
				'ship_via'=>array('type'=>'select','lookup'=>'shippers'),
				'ship_date'=>array('type'=>'datepicker'),
				'ship_tracking_code'=>array('type'=>'input'),
				'ship_comments'=>array('type'=>'textarea','class'=>'mceSimple','reformat'=>false),
				'order_status'=>array('type'=>'select','multiple'=>true,'lookup'=>'orderStatus','database'=>false,'id'=>'order_status')
			),
			'showSearchForm'=>array(
				'options'=>array('action'=>'showSearchForm','name'=>'searchForm','id'=>'search_form'),
				'order_status'=>array('type'=>'select','name'=>'order_status','lookup'=>'orderStatus'),
				'opt_created'=>array('type'=>'select','name'=>'opt_created','lookup'=>'search_options'),
				'created'=>array('type'=>'datepicker','required'=>false),
				'opt_quantity'=>array('type'=>'select','name'=>'opt_quantity','lookup'=>'search_options'),
				'quantity'=>array('type'=>'textfield','required'=>false,'validation'=>'number'),
				'opt_shipped'=>array('type'=>'select','name'=>'opt_shipped','lookup'=>'search_options'),
				'shipped'=>array('type'=>'textfield','required'=>false,'validation'=>'number'),
				'showSearchForm'=>array('type'=>'hidden','value'=>1),
				'pagenum'=>array('type'=>'hidden','value'=>1),
				'sortby'=>array('type'=>'hidden','value'=>'created'),
				'sortorder'=>array('type'=>'hidden','value'=>'desc'),
				'quicksearch'=>array('type'=>'input','name'=>'quicksearch','required'=>false),
				'opt_quicksearch'=>array('type'=>'hidden','value'=>'like'),
				'pager'=>array('type'=>'select','required'=>true,'value'=>$this->m_perrow,'lookup'=>'paging','id'=>'pager'),
				'pagenum'=>array('type'=>'hidden','value'=>1),
				'perpage'=>array('type'=>'hidden','value'=>$this->m_perrow,'name'=>'pager'),
				'submit'=>array('type'=>'submitbutton','value'=>'Search')
			),
			'articleList' => array(
				'id'=>array('type'=>'tag'),
				'title'=>array('type'=>'tag'),
				'order_date'=>array('type'=>'datestamp','mask'=>'d-M-Y h:i:s'),
				'deleted'=>array('type'=>'booleanIcon')
			),
			'recurringInfo'=>array(
				'delayShipping'=>array('type'=>'checkbox','value'=>1,'name'=>'delayShipping'),
				'delayUntil'=>array('type'=>'datepicker','required'=>false),
				'recurringInfo'=>array('type'=>'hidden','value'=>1),
				'submit'=>array('type'=>'submitbutton','value'=>'Put Order On Hold')
			),
			'recurringInfoRow' => array(
				'billing_date'=>array('type'=>'datestamp','mask'=>'d-M-Y'),
				'billed'=>array('type'=>'booleanIcon'),
				'billed_on'=>array('type'=>'datestamp','mask'=>'d-M-Y h:i:s')
			),
			'showRecurring' => array(
				'billing_date'=>array('type'=>'datestamp','mask'=>'d-M-Y'),
				'billed_on'=>array('type'=>'datestamp','mask'=>'d-M-Y h:i:s'),
				'authorization_info'=>array('type'=>'textarea')
			),
			'pickList'=>array(
				'pickList'=>array('type'=>'hidden','value'=>1),
				'start_date'=>array('type'=>'datepicker','required'=>true,'value'=>date('Y-m-d')),
				'end_date'=>array('type'=>'datepicker','required'=>true,'value'=>date('Y-m-d')),
				'submit'=>array('type'=>'submitbutton','value'=>'Search'),
				'export'=>array('type'=>'submitbutton','value'=>'Export')
			),
			'pickListRow' => array(),
			'packList'=>array(
				'packList'=>array('type'=>'hidden','value'=>1),
				'start_date'=>array('type'=>'datepicker','required'=>true,'value'=>date('Y-m-d')),
				'end_date'=>array('type'=>'datepicker','required'=>true,'value'=>date('Y-m-d')),
				'submit'=>array('type'=>'submitbutton','value'=>'Search'),
				'export'=>array('type'=>'submitbutton','value'=>'Export')
			),
			'packListRow' => array(),
			'packTotal' => array(),
			'exportPick'=>array(),
			'exportPickRow'=>array(),
			'exportPack'=>array(),
			'exportPackRow'=>array(),
			'confirmation'=>array(
				'confirmation'=>array('type'=>'hidden','value'=>1),
				'start_date'=>array('type'=>'datepicker','required'=>true,'value'=>date('Y-m-d')),
				'end_date'=>array('type'=>'datepicker','required'=>true,'value'=>date('Y-m-d')),
				'submit'=>array('type'=>'submitbutton','value'=>'Search Orders'),
				'reprint'=>array('type'=>'submitbutton','value'=>'Reprint Packing Slips'),
				'update'=>array('type'=>'submitbutton','value'=>'Update Orders'),
				'ship_via'=>array('type'=>'select','required'=>false,'sql'=>'SELECT id, value as name FROM code_lookups WHERE type = "ship_via" and extra like "%|1" ORDER BY sort, value ASC'),
				'order_id'=>array('type'=>'textfield','required'=>false,'validation'=>'number')
			),
			'confirmationRow' => array(
				'ship_tracking_code'=>array('type'=>'textfield','required'=>false,'name'=>'ship_tracking_code[%%id%%]','prompt'=>'Tracking Id'),
				'ship_comments'=>array('type'=>'textarea','required'=>false,'name'=>'ship_comments[%%id%%]'),
				'shipped'=>array('type'=>'checkbox','value'=>1,'name'=>'shipped[%%id%%]'),
				'email'=>array('type'=>'checkbox','value'=>1,'name'=>'email[%%id%%]'),
				'ship_date'=>array('type'=>'datepicker','value'=>date('Y-m-d'),'name'=>'ship_date[%%id%%]')
			),
			'confirmationOrder'=>array(
				'ship_tracking_code'=>array('type'=>'textfield','required'=>false,'name'=>'ship_tracking_code[%%id%%]','prompt'=>'Tracking Id'),
				'ship_comments'=>array('type'=>'textarea','required'=>false,'name'=>'ship_comments[%%id%%]'),
				'shipped'=>array('type'=>'checkbox','value'=>1,'name'=>'shipped[%%id%%]'),
				'email'=>array('type'=>'checkbox','value'=>1,'name'=>'email[%%id%%]'),
				'ship_date'=>array('type'=>'datepicker','value'=>date('Y-m-d'),'name'=>'ship_date[%%id%%]')
			),
			'confirmationOrderRow'=>array(
				'line_qty'=>array('type'=>'textfield','required'=>false,'name'=>'line_qty[%%order_id%%|%%line_id%%]','class'=>'shipped','value'=>'0')
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
		$this->logMessage('show',sprintf('injector [%s]',$injector),2);
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
     * @return string
     */
    function showContentTree() {
		return "";
	}

    /**
     * @param bool $fromMain
     * @return array|false|mixed|string|string[]|void
     * @throws phpmailerException
     */
    function showPageContent($fromMain = false) {
		$o_id = array_key_exists('o_id',$_REQUEST) ? $_REQUEST['o_id'] : 0;
		$form = new Forms();
		if ($o_id > 0 && $data = $this->fetchSingleTest('select * from %s where id = %d',$this->m_content,$o_id)) {
			$form->init($this->getTemplate('showFolderContent'),array('name'=>'showFolderContent'));
			$frmFields = $form->buildForm($this->getFields('showFolderContent'));
			if (array_key_exists('pagenum',$_REQUEST)) 
				$pageNum = $_REQUEST['pagenum'];
			else
				$pageNum = 1;	// no 0 based calcs
			if ($pageNum <= 0) $pageNum = 1;
			$perPage = $this->m_perrow;
			if (array_key_exists('pager',$_REQUEST)) $perPage = $_REQUEST['pager'];
			$count = $this->fetchScalarTest('select count(n.id) from '.$this->m_content.' n where deleted = 0');
			$pagination = $this->pagination($count, $perPage, $pageNum, 
				array('prev'=>$this->M_DIR.'forms/paginationPrev.html','next'=>$this->M_DIR.'forms/paginationNext.html',
				'pages'=>$this->M_DIR.'forms/paginationPage.html', 'wrapper'=>$this->M_DIR.'forms/paginationWrapper.html'),
				array('url'=>'/modit/ajax/showFolderContent/shipping','destination'=>'middleContent'));
			$start = ($pageNum-1)*$perPage;
			$sortby = 'id';
			$sortorder = 'desc';
			if (count($_POST) > 0 && array_key_exists('showFolderContent',$_POST)) {
				$sortby = $_POST['sortby'];
				if ($sortby == 'name') $sortby = 'm.lastname';
				$sortorder = $_POST['sortorder'];
				$form->addData($_POST);
			}
			$sql = sprintf('select a.*,m.firstname,m.lastname from %s a where a.id = %d order by %s %s limit %d,%d',  $this->m_content, $_REQUEST['o_id'],$sortby, $sortorder, $start,$perPage);
			$orders = $this->fetchAllTest($sql);
			$this->logMessage('showPageContent', sprintf('sql [%s], records [%d]',$sql, count($orders)), 2);
			$articles = array();
			$frm = new Forms();
			$frm->init($this->getTemplate('articleList'),array());
			$tmp = $frm->buildForm($this->getFields('articleList'));
			$status = $this->fetchAllTest('select * from code_lookups where type="orderStatus" order by sort, code');
			foreach($orders as $order) {
				$order['owing'] = money_format('%(#n',$order['total']-$order['authorization_amount']);
				$order['value'] = money_format('%(#n',$order['value']);
				$order['total'] = '['.money_format('%(#n',$order['total']).']';
				$order['taxes'] = money_format('%(#n',$order['taxes']);
				$order['discount_value'] = money_format('%(#n',$order['discount_value']);
				$order['line_discounts'] = money_format('%(#n',$order['line_discounts']);
				$this->logMessage("showSearchForm",sprintf("detail line form [%s]",print_r($frm,true)),2);
				$tmp = array();
				foreach($status as $key=>$value) {
					if ($order['order_status'] & (int)$value['code'])
						$tmp[] = $value['value'];
				}
				$order['order_status'] = implode(', ',$tmp);
				$frm->addData($order);
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
		$this->logMessage(__FUNCTION__,sprintf('post is [%s]',print_r($_POST,true)),1);
		if (count($_POST) == 0)
			if (array_key_exists('formData',$_SESSION) && array_key_exists('shippingSearchForm', $_SESSION['formData']))
				$_POST = $_SESSION['formData']['shippingSearchForm'];
			else
				$_POST = array('order_status'=>STATUS_PROCESSING,'sortby'=>'created','sortorder'=>'asc','showSearchForm'=>1);
		$this->logMessage(__FUNCTION__,sprintf('post is [%s]',print_r($_POST,true)),1);
		if (count($_POST) > 0 && array_key_exists('showSearchForm',$_POST)) {
			$form->addData($_POST);
			if ($form->validate()) {
				$_SESSION['formData']['shippingSearchForm'] = $form->getAllData();
				$srch = array();
				$quick = false;
				foreach($frmFields as $key=>$value) {
					if ($quick)
						break;
					switch($key) {
						case 'quicksearch':
							if (array_key_exists('opt_quicksearch',$_POST) && $_POST['opt_quicksearch'] != null && $value = $form->getData($key)) {
								if ($_POST['opt_quicksearch'] == 'like' && strpos($value,'%',0) === false) {
									$value = '%'.$value.'%';
								}
								$srch = array();
								$tmp = array();
								$quick = true;
								$tmp[] = sprintf(' concat(firstname," ",lastname) %s "%s"',$_POST['opt_quicksearch'],$this->escape_string($value));
								$tmp[] = sprintf(' o.id %s "%s"',$_POST['opt_quicksearch'],$this->escape_string($value));
								$srch = array(sprintf('(%s)',implode(' or ',$tmp)));
								continue;
							}
							break;
						case 'order_id':
							//
							//	normally it would be just id but the form has a hidden field id as well for paging
							//
							if (array_key_exists('opt_'.$key,$_POST) && $_POST['opt_'.$key] != null && !is_null($value = $form->getData($key))) {
								if ($_POST['opt_'.$key] == 'like' && strpos($value,'%',0) === false) {
									$value = '%'.$value.'%';
								}
								$srch[] = sprintf(' o.id %s %s',$_POST['opt_'.$key],$this->escape_string($value));
							}
							break;
						case 'name':
							if (array_key_exists('name',$_POST) && strlen($_POST['name']) > 0)
								$srch[] = sprintf("(m.firstname like '%%%s%%' or m.lastname like '%%%s%%')",$_POST['name'],$_POST['name']);
							break;
						case 'created':
							if (array_key_exists('opt_'.$key,$_POST) && $_POST['opt_'.$key] != null && $value = $form->getData($key)) {
								if ($_POST['opt_'.$key] == 'like') {
									$this->addError('Like is not supported for dates');
								}
								else
									$srch[] = sprintf(' date_format(o.%s,"%%Y-%%m-%%d") %s "%s"',$key, $_POST['opt_'.$key],date("Y-m-d",strtotime($this->escape_string($value))));
							}
							break;
						case 'order_status':
							if (($value = $form->getData($key)) > 0) {
								if ($value != STATUS_RECURRING) {
									$srch[] = sprintf(' ((o.order_status & %d) = %d)',$value,$value);
									$srch[] = sprintf(' ((o.order_status & %d) = 0)',STATUS_RECURRING);
								}
								else
									$srch[] = sprintf(' ((o.order_status & %d) = %d)',$value,$value);
							}
							break;
						case 'quantity':
						case 'shipped':
							if (array_key_exists('opt_'.$key,$_POST) && $_POST['opt_'.$key] != null && $value = $form->getData($key)) {
								if ($_POST['opt_'.$key] == 'like') {
									$this->addError('Like is not supported for numeric fields');
								}
								else
									$srch[] = sprintf(' %s %s "%s"',$key, $_POST['opt_'.$key],(int)$value);
							}
							break;
						default:
							break;
					}
				}
				$this->logMessage("showSearchForm",sprintf("srch [%s]",print_r($srch,true)),3);
				if (count($srch) > 0) {
					if (array_key_exists('pagenum',$_REQUEST))
						$pageNum = $_REQUEST['pagenum'];
					else
						$pageNum = 1;	// no 0 based calcs
					$perPage = $this->m_perrow;
					if (array_key_exists('pager',$_POST)) $perPage = $_POST['pager'];
					$count = $this->fetchScalarTest('select count(o.id) from %s o,members m where m.id = o.member_id and %s', $this->m_content, implode(' and ',$srch));
					$pageNum = max(1,min($pageNum, (floor(($count-1)/$perPage)+1)));
					$this->logMessage("showSearchForm",sprintf("pagenum is [$pageNum]"),2);
					$form->setData('pagenum', $pageNum);
					$pagination = $this->pagination($count, $perPage, $pageNum,
							array('prev'=>$this->M_DIR.'forms/paginationPrev.html','next'=>$this->M_DIR.'forms/paginationNext.html',
							'pages'=>$this->M_DIR.'forms/paginationPage.html', 'wrapper'=>$this->M_DIR.'forms/paginationWrapper.html'),
							array('url'=>'/modit/ajax/showSearchForm/shipping','destination'=>'middleContent')
						);
					$start = ($pageNum-1)*$perPage;
					$sortorder = 'desc';
					$sortby = 'created';
					if (array_key_exists('sortby',$_POST)) {
						$sortby = $_POST['sortby'];
						if ($sortby == 'name') $sortby = 'm.lastname';
						$sortorder = $_POST['sortorder'];
					}
					$this->logMessage("showSearchForm",sprintf("post [%s] srch [%s]",print_r($_POST,true),print_r($srch,true)),2);
					$sql = sprintf('select o.*, m.firstname, m.lastname, concat(m.firstname," ",m.lastname) as name, sum(l.quantity*l.qty_multiplier) as quantity, sum(l.shipped*l.qty_multiplier) as shipped from %s o, members m, order_lines l where l.deleted = 0 and l.order_id = o.id and m.id = o.member_id and %s group by o.id order by %s %s limit %d,%d',
						 $this->m_content, implode(' and ',$srch),$sortby, $sortorder, $start,$perPage);
					$recs = $this->fetchAllTest($sql);
					$this->logMessage('showSearchForm', sprintf('sql [%s] records [%d]',$sql,count($recs)), 2);
					$articles = array();
					$frm = new Forms();
					$frm->init($this->getTemplate('articleList'),array());
					$tmp = $frm->buildForm($this->getFields('articleList'));
					$status = $this->fetchAllTest('select * from code_lookups where type="orderStatus" order by sort, code');
					foreach($recs as $article) {
						$article['owing'] = money_format('%(#10n',$article['total']-$article['authorization_amount']);
						$article['value'] = money_format('%(#10n',$article['value']);
						$article['total'] = money_format('%(#10n',$article['total']);
						$article['taxes'] = money_format('%(#10n',$article['taxes']);
						$article['discount_value'] = money_format('%(#10n',$article['discount_value']);
						$article['line_discounts'] = money_format('%(#10n',$article['line_discounts']);
						$this->logMessage("showSearchForm",sprintf("detail line form [%s]",print_r($frm,true)),2);
						$tmp = array();
						foreach($status as $key=>$value) {
							if ($article['order_status'] & (int)$value['code'])
								$tmp[] = $value['value'];
						}
						$article['order_status'] = implode(', ',$tmp);
						$this->logMessage("showSearchForm",sprintf("detail line form [%s]",print_r($frm,true)),2);
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
     * @param $fromMain
     * @return array|false|mixed|string|string[]|void
     * @throws phpmailerException
     */
    function addContent($fromMain = false) {
		$form = new Forms();
		$form->init($this->getTemplate('addContent'));
		$frmFields = $this->getFields('addContent');
		if (!(array_key_exists('o_id',$_REQUEST) && $_REQUEST['o_id'] > 0 && $data = $this->fetchSingleTest('select * from %s where id = %d', $this->m_content, $_REQUEST['o_id']))) {
			$data = array('id'=>0,'published'=>false,'tax_exemptions'=>'||','value'=>0,'authorization_amount'=>0,'coupon_id'=>0,'discount_type'=>'','order_status'=>0); 
			$frmFields['coupon_id']['sql'] = 'select id, concat(code," - ",name) from coupons where deleted = 0 and enabled = 1 and published = 1 order by code';
		} else {
			$frmFields['coupon_id']['sql'] = sprintf('select id, concat(code," - ",name) from coupons where (deleted = 0 and enabled = 1 and published = 1) or id = %d order by code',$data['coupon_id']);
		}
		$lines = $this->fetchAllTest('select o.*, p.code, p.name, c.name as coupon_name from order_lines o left join coupons c on c.id = o.coupon_id, product p where o.deleted = 0 and o.order_id = %d and p.id = o.product_id and o.deleted = 0 order by line_id',$data['id']);
		$details = array();
		$dtlForm = new Forms();
		$dtlForm->init($this->getTemplate('orderLine'));
		$dtlFields = $dtlForm->buildForm($this->getFields('orderLine'));
		foreach($lines as $line) {
			$line['disc_dollar'] = $line['discount_type'] == 'D' ? '$':'';
			$line['disc_percent'] = $line['discount_type'] == 'P' ? '%':'';
			$dtlForm->addData($line);
			$details[] = $dtlForm->show();
		}
		$data['products'] = implode('',$details);
		$data['discount_dollar'] = '';
		$data['discount_percent'] = '';
		switch ($data['discount_type']) {
			case 'P':
				$data['discount_percent'] = '%';
				break;
			case 'D';
				$data['discount_dollar'] = '$';
				break;
			default:
			break;
		}
		$data['authorization_amount_ro'] = $data['authorization_amount'];
		$status = $this->fetchAllTest('select * from code_lookups where type="orderStatus" order by sort, code');
		$tmp = array();
		foreach($status as $key=>$value) {
			if ($data['order_status'] & (int)$value['code'])
				$tmp[] = $value['code'];
		}
		$data['order_status'] = $tmp;
		$form->addData($data);
		$form->addTag('addressForm',$this->loadAddresses($data['id']),false);
		$form->addTag('recurringInfo',$this->getRecurring($data['id']),false);

		$customFields = new custom();
		if (method_exists($customFields,'orderDisplay')) {
			$custom = $customFields->orderDisplay();
			$form->addTag('customTab',sprintf('<li><a href="#tabs-custom">%s</a></li>',$custom['description']),false);
			$html = $form->getHTML();
			$html = str_replace('%%customInfo%%',$custom['form'],$html);
			$form->setHTML($html);
			$frmFields = array_merge($frmFields,$custom['fields']);
		}
		$frmFields = $form->buildForm($frmFields);

		$status = 'false';	//assume it failed
		if (count($_POST) > 0 && array_key_exists('addContent',$_POST)) {
			$form->addData($_POST);
			$status = $form->validate();
			if ($status) {
				if ($_POST['tempEdit'] == 1) {
					switch($_POST['fldName']) {
						case 'coupon_id':
							if ($cp = $this->fetchSingleTest('select * from coupons where id = %d',$_POST['coupon_id'])) {
								$form->setData('discount_rate',$cp['amount']);
								$form->setData('discount_type',$cp['percent_or_dollar']);
							}
							else {
								$form->setData('discount_rate',0);
								$form->setData('discount_type','');
							}
							$form->setData('discount_dollar','');
							$form->setData('discount_percent','');
							switch ($cp['percent_or_dollar']) {
								case 'P':
									$form->setData('discount_percent','%');
									break;
								case 'D';
									$form->setData('discount_dollar','$');
									break;
								default:
								break;
							}
							break;
						default:
							break;
					}
					$this->logMessage("accContent",sprintf("calling recalcOrder from tempEdit"),2);
					//$tmp = $this->recalcOrder($data['id'],false);
					$tmp = array();
					$tmp['header'] = $form->getAllData();
					$tmp['products'] = $this->fetchAllTest('select * from order_lines where order_id = %d and deleted = 0',$data['id']);															
					$tmp['taxes'] = $this->fetchAllTest('select * from order_taxes ot where ot.order_id = %d and ot.line_id in (select o.line_id from order_lines o where o.deleted = 0 and o.order_id = ot.order_id and o.deleted = 0)',$data['id']);
					$tmp = Ecom::recalcOrder($tmp);
					$form->addData($tmp['header']);
					return $this->ajaxReturn(array('status'=>'true','html'=>$form->show()));
				}
				$id = $_POST['o_id'];
				unset($frmFields['o_id']);
				unset($frmFields['options']);
				foreach($frmFields as $key=>$fld) {
					if (!(array_key_exists('database',$fld) && $fld['database'] == false)) {
						$flds[$key] = $form->getData($fld['name']);
					}
				}
				$tmp = 0;
				foreach($_REQUEST['order_status'] as $key=>$value) {
					$tmp |= $value;
				}
				$flds['order_status'] = $tmp;
				if ($data['id'] == 0) {
					$flds['created'] = date(DATE_ATOM);
					$stmt = $this->prepare(sprintf('insert into %s(%s) values(%s)',$this->m_content,implode(',',array_keys($flds)),str_repeat('?, ',count($flds)-1).'?'));
				}
				else 
					$stmt = $this->prepare(sprintf('update %s set %s where id = %d',$this->m_content,implode('=?, ',array_keys($flds)).'=?',$data['id']));
				$this->logMessage("addContent",sprintf("data array before update [%s]",print_r($data,true)),2);
				$stmt->bindParams(array_merge(array(str_repeat('s', count($flds))),array_values($flds)));
				$this->beginTransaction();
				if ($status = $stmt->execute()) {
					if ($data['id'] == 0) {
						$id = $this->insertId();
						$addresses = $this->fetchAllTest('select * from addresses where ownertype="member" and ownerid = %d and deleted = 0',$form->getData('member_id'));
						foreach($addresses as $key=>$address) {
							$address['ownertype'] = 'order';
							$address['ownerid'] = $id;
							unset($address['id']);
							$stmt = $this->prepare(sprintf('insert into addresses(%s) values(%s)',implode(',',array_keys($address)),str_repeat('?,',count($address)-1).'?'));
							$stmt->bindParams(array_merge(array(str_repeat('s', count($address))),array_values($address)));
							$status = $status && $stmt->execute();
						}
					}
					else $id = $data['id'];
					$this->logMessage("accContent",sprintf("calling recalcOrder from save"),2);
					$tmp = $this->recalcOrder($id,true);
					$form->addData($tmp['header']);
					//
					//	copy any addresses associated with this customer as well
					//
					$this->execute(sprintf('delete from order_taxes where order_id = %d and line_id = 0',$id));
					foreach($tmp['taxes'] as $key=>$tax) {
						$stmt = $this->prepare('insert into order_taxes(order_id,line_id,tax_id,tax_amount) values(?,?,?,?)');
						$stmt->bindParams(array('iiid',$id,0,$key,$tax['tax_amount']));
						$status = $status && $stmt->execute();
					}
					if ($status) {
						$this->commitTransaction();
						if ($data['id'] == 0) 
							$form->addTag('id',$id);
						$this->addMessage('Record Updated');
						$data = $this->fetchSingleTest('select * from %s where id = %d', $this->m_content, $id);
						$data['discount_dollar'] = '';
						$data['discount_percent'] = '';
						switch ($data['discount_type']) {
							case 'P':
								$data['discount_percent'] = '%';
								break;
							case 'D';
								$data['discount_dollar'] = '$';
								break;
							default:
							break;
						}
						$data['authorization_amount_ro'] = $data['authorization_amount'];
						$status = $this->fetchAllTest('select * from code_lookups where type="orderStatus" order by sort, code');
						$tmp = array();
						foreach($status as $key=>$value) {
							if ($data['order_status'] & (int)$value['code'])
								$tmp[] = $value['code'];
						}
						$data['order_status'] = $tmp;
						$form->addData($data);
						if (array_key_exists('submitEmail',$_POST)) {
							$emails = $this->configEmails("ecommerce");
							if (count($emails) == 0)
								$emails = $this->configEmails("contact");
							$mailer = new PHPMailer();
							$mailer->Subject = sprintf("Order Status Update - %s", SITENAME);
							$body = new Forms();
							$html = $this->getHtmlForm('orderStatus');
							//$sql = sprintf('select * from htmlForms where class = %d and type = "orderStatus"',$this->getClassId('product'));
							//$html = $this->fetchSingleTest($sql);
							$body->setHTML($html);
							$order = $this->fetchSingleTest('select o.*, m.firstname, m.lastname, m.email from orders o, members m where o.id = %d and m.id = o.member_id',$id);
							$body->addData($this->formatOrder($order));
							$mailer->Body = $body->show();
							$mailer->From = $emails[0]['email'];
							$mailer->FromName = $emails[0]['name'];
							$this->logMessage('addContent',sprintf("mailer object [%s]",print_r($mailer,true)),1);
							$mailer->IsSMTP();
							$mailer->IsHTML(true);	
							$mailer->addAddress($order['email'],$order['firstname'].' '.$order['lastname']);
							if (!$mailer->Send()) {
								$this->addMessage('There was an error sending the email');
								$this->logMessage('addContent',sprintf("Email send failed [%s]",print_r($mailer,true)),1,true);
							}
							else
								$this->addMessage('Email has been sent');
						}
					}
					else {
						$this->rollbackTransaction();
						$this->addError('An Error occurred');
					}
				} else {
					$this->rollbackTransaction();
					$this->addError('An Error occurred');
				}
				return $this->ajaxReturn(array('status' => $status,'html' => $form->show()));
			}
			else {
				$this->addError('Form Validation Failed');
			}
			$form->addTag('errorMessage',$this->showMessages(),false);
		}
		if ($this->isAjax()) {
			$tmp = $form->show();
			$this->logMessage("addContent",sprintf("return form [%s]",print_r($form,true)),2);
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
     * @return false|string|void
     * @throws phpmailerException
     */
    function deleteArticle() {
		if (array_key_exists('j_id',$_REQUEST)) {
			$id = $_REQUEST['j_id'];
			$curr = $this->fetchScalarTest('select * from %s where id = %d',$this->m_content,$id);
			$this->logMessage('deleteArticle', sprintf('deleting order %d',$id), 2);
			$this->beginTransaction();
			$this->execute(sprintf('update %s set deleted = 1 where id = %d',$this->m_content,$curr));
			$this->commitTransaction();
			return $this->ajaxReturn(array('status'=>'true'));
		}
	}

    /**
     * @return array|mixed|string|string[]|void
     * @throws phpmailerException
     */
    function edit() {
		if (array_key_exists('e_id',$_REQUEST) && $data = $this->fetchSingleTest('select * from %s where id = %d',$this->m_content,$_REQUEST['e_id'])) {
			$f = new Forms();
			$f->init($this->getTemplate('editItem'));
			$f->addData($data);
			return $this->show($f->show());
		}
		else
			return $this->show();
	}

    /**
     * @return false|string
     * @throws phpmailerException
     */
    function editLine() {
		$flds = $this->getFields('editLine');
		if (!(array_key_exists('l_id',$_REQUEST) && $data = $this->fetchSingleTest('select * from order_lines where deleted = 0 and id = %d',$_REQUEST['l_id']))) {
			$data = array('id'=>0,'product_id'=>0,'coupon_id'=>0,'value'=>0,'quantity'=>0,'order_id'=>$_REQUEST['order_id']);
			$flds['coupon_id']['sql'] = 'select id, concat(code," - ",name) from coupons where deleted = 0 and enabled = 1 and published = 1 order by code';
		} else {
			$flds['coupon_id']['sql'] = sprintf('select id, concat(code," - ",name) from coupons where (deleted = 0 and enabled = 1 and published = 1) or id = %d order by code',$data['coupon_id']);
		}
		$order = $this->fetchSingleTest('select * from orders where id = %d',$_REQUEST['order_id']);
		$data['order_date'] = $order['order_date'];
		$form = new Forms();
		$form->init($this->getTemplate('editLine'));
		$flds['inventory_id']['sql'] = sprintf('select id, concat(start_date," - ",end_date," : ",quantity) from product_inventory where product_id = %d and (end_date >= curdate() or end_date = "0000-00-00") order by start_date',$data['product_id']);

		$customFields = new custom();
		if (method_exists($customFields,'orderLineDisplay')) {
			$custom = $customFields->orderLineDisplay();
			$form->addTag('customTab',sprintf('<li><a href="#tabs-custom">%s</a></li>',$custom['description']),false);
			$html = $form->getHTML();
			$html = str_replace('%%customInfo%%',$custom['form'],$html);
			$form->setHTML($html);
			$flds = array_merge($flds,$custom['fields']);
		}

		$flds = $form->buildForm($flds);
		$this->logMessage('editLine',sprintf('load options for product [%s]',$data['product_id']),1);
		$form->getField('options_id')->setOptions($this->fetchOptionsTest('select id,teaser from product_options where product_id = %d and deleted = 0',$data['product_id']));
		if ($form->getField('options_id')->hasOptions()) $form->getField('options_id')->addAttribute('required',true);
		$form->addData($data);
		if (array_key_exists('editLine',$_REQUEST) && count($_POST) > 0) {
			$form->addData($_POST);
			$form->getField('options_id')->setOptions($this->fetchOptionsTest('select id,teaser from product_options where product_id = %d and deleted = 0',$_POST['product_id']));
			if ($form->getField('options_id')->hasOptions()) $form->getField('options_id')->addAttribute('required',true);
			$form->getField('inventory_id')->addAttribute('sql',sprintf('select id, concat(start_date," - ",end_date," : ",quantity) from product_inventory where product_id = %d and (end_date >= curdate() or end_date = "0000-00-00") order by start_date',$_POST['product_id']));
			if ($_POST['tempEdit']) {
				//
				//	something changed
				//
				$status = true;
				switch($_POST['fldName']) {
					case 'options_id':
						$option = $this->fetchSingleTest('select * from product_options where id = %d',$_POST['options_id']);
						if ($p = Ecom::getPricing($_POST['product_id'],$_POST['quantity'],$_POST['order_date'])) {
							$_POST['price'] = $p['price'];
							$_POST['shipping'] = $p['shipping'];
						}
						$_POST['price'] += $option['price'];
						$_POST['shipping'] += $option['shipping'];
						break;
					case 'coupon_id':
						$sql = sprintf('select * from coupons where id = %d',$_POST['coupon_id']);
						$c = $this->fetchSingleTest($sql);
						$_POST['discount_type'] = $c['percent_or_dollar'];
						$_POST['discount_rate'] = $c['amount'];
						$_POST['shipping_only'] = $c['shipping_only'];
						$this->logMessage("editLine",sprintf("coupon override sql [$sql] [%s]",print_r($c,true)),2);
						break;
					case 'product_id':
						$_POST['options_id'] = 0;
						if ($p = Ecom::getPricing($_POST['product_id'],$_POST['quantity'],$_POST['order_date'])) {
							$_POST['price'] = $p['price'];
							$_POST['shipping'] = $p['shipping'];
							//
							//	user hasn't had chance to select option yet - default to the 1st one
							//
							if ($form->getField('options_id')->hasOptions()) {
								$opt = $form->getField('options_id')->getOptions();
								$tmp = array_keys($opt);
								$_POST['options_id'] = $tmp[0];
								$this->logMessage('editLine',sprintf('options returned [%s]',print_r($opt,true)),1);
								if ($option = $this->fetchSingleTest('select * from product_options where id = %d',$_POST['options_id'])) {
									$_POST['price'] += $option['price'];
									$_POST['shipping'] += $option['shipping'];
								}
							}
						} else {
							$this->addMessage('No pricing found for this quantity');
							$status = false;
						}
						$this->logMessage("editLine",sprintf("product override"),2);
					case 'quantity':
						//$sql = sprintf('select * from product_pricing where min_quantity <= %d and max_quantity >= %d and product_id = %d',$_POST['quantity'],$_POST['quantity'],$_POST['product_id']);
						//if ($p = $this->fetchSingleTest($sql)) {
						if ($p = Ecom::getPricing($_POST['product_id'],$_POST['quantity'],$_POST['order_date'])) {
							$_POST['price'] = $p['price'];
							$_POST['shipping'] = $p['shipping'];	//*$_POST['quantity'];
							if ($option = $this->fetchSingleTest('select * from product_options where id = %d',$_POST['options_id'])) {
								$_POST['price'] += $option['price'];
								$_POST['shipping'] += $option['shipping'];
							}
						} else {
							$this->addMessage('No pricing found for this quantity');
							$status = false;
						}
						$this->logMessage("editLine",sprintf("price override [%s]",print_r($p,true)),2);
						break;
					default:
						break;
				}
				$form->addData($_POST);
				$tmp = Ecom::lineValue($form->getAllData());
				$form->addData($tmp);
				if (!$status) {
					return $this->ajaxReturn(array('status'=>'true','html'=>$form->show()));
				}
			}
			else {
				if ($form->validate()) {
					$values = array();
					$tmp = Ecom::lineValue($form->getAllData());
					$form->addData($tmp);
					foreach($flds as $key=>$fld) {
						if (!(array_key_exists('database',$fld) && $fld['database'] == false))
							$values[$key] = $form->getData($fld['name']);
					}
					$values['total'] = $tmp['total'];
					$values['taxes'] = $tmp['taxes'];
					$prod = $this->fetchSingleTest('select * from product where id = %d',$values['product_id']);
					$values['tax_exemptions'] = $prod['tax_exemptions'];
					if ($data['id'] != 0) {
						$stmt = $this->prepare(sprintf('update order_lines set %s where id = %d',implode('=?,',array_keys($values)).'=?',$data['id']));
					}
					else {
						$seq = $this->fetchScalarTest('select max(line_id) from order_lines where order_id = %d',$values['order_id']);
						$values['line_id'] = $seq+1;
						$data['line_id'] = $values['line_id']; 
						$stmt = $this->prepare(sprintf('insert into order_lines(%s) values(%s)',implode(',',array_keys($values)),str_repeat('?,',count($values)-1).'?'));
					}
					$stmt->bindParams(array_merge(array(str_repeat('s', count($values))),array_values($values)));
					$this->logMessage("editLine",sprintf("values [%s] prod [%s]",print_r($values,true),print_r($prod,true)),2);
					$this->beginTransaction();
					$status = true;
					if ($data['id'] > 0) {
						//
						//	check for product/qty changes & adjust as appropriate
						//
						$oline = $this->fetchSingleTest('select * from order_lines where id = %d',$data['id']);
						if ($values['product_id'] == $oline['product_id'] && $values['inventory_id'] == $oline['inventory_id'] && $values['quantity'] != $oline['quantity']) {
							//
							//	just a qty change
							//
							$status = $status && Inventory::updateInventory($values['inventory_id'],$data['quantity'] - $values['quantity'],$data['order_id'],'Line edited');
						} else {
							if ($values['product_id'] != $oline['product_id'] || $values['inventory_id'] != $oline['inventory_id'] || $values['quantity'] != $oline['quantity']) {
								$status = $status && Inventory::updateInventory($data['inventory_id'],$data['quantity'],$data['order_id'],'Product removed');
								$status = $status && Inventory::updateInventory($values['inventory_id'],-$values['quantity'],$data['order_id'],'Product added');
							}
						}
					}
					else {
						$status = $status && Inventory::updateInventory($values['inventory_id'],-$values['quantity'],$data['order_id'],'Product added');
					}
					$status = $status && $stmt->execute();
					if ($status) {
						if ($data['id'] == 0) {
							$data['id'] = $this->insertId();
						}
						$sql = sprintf('delete from order_taxes where order_id = %d and line_id = %d',$data['order_id'],$data['line_id']);
						$this->execute($sql);
						$this->logMessage("editLine",sprintf("order_taxes sql [$sql] data [%s]",print_r($data,true)),2);
						foreach($tmp['taxdata'] as $key=>$tax) {
							$flds = array('order_id'=>$data['order_id'],'line_id'=>$data['line_id'],'tax_id'=>$key,'tax_amount'=>$tax['tax_amount'],'taxable_amount'=>$tax['taxable_amount']);
							$stmt = $this->prepare(sprintf('insert into order_taxes(order_id,line_id,tax_id,tax_amount,taxable_amount) values(?,?,?,?,?)'));
							$stmt->bindParams(array_merge(array('iiidd'),array_values($flds)));
							$status = $status && $stmt->execute();
						}
						$this->logMessage("accContent",sprintf("calling recalcOrder from editLine"),2);
						$this->recalcOrder($data['order_id']);
						$this->commitTransaction();
						$form->init($this->getTemplate('editLineResult'));
						$form->addData($data);
						$this->logMessage("editLine",sprintf("result form [%s]",print_r($form,true)),2);
						$status = "true";
					}
					else {
						$this->rollbackTransaction();
						$status = "false";
					}
					return $this->ajaxReturn(array('status'=>$status,'html'=>$form->show()));
				}
				else
					$form->addError('Form Validation Failed');
			}
		}
		return $this->ajaxReturn(array('status'=>'true','html'=>$form->show()));
	}

    /**
     * @param $passed_id
     * @return array|false|mixed|string|string[]
     * @throws phpmailerException
     */
    function loadAddresses($passed_id = null) {
		if ($passed_id == null) {
			if (array_key_exists('o_id',$_REQUEST))
				$o_id = $_REQUEST['o_id'];
			else
				$o_id = 0;
		}
		else
			$o_id = $passed_id;
		$addresses = $this->fetchAllTest('select a.*, c.value as addressType from addresses a, code_lookups c where ownertype = "order" and ownerid = %d and deleted = 0 and c.id = a.addressType',$o_id);
		$addressForm = new Forms();
		$addressForm->init($this->getTemplate('addressForm'));
		$addrForm = new Forms();
		$addrForm->init($this->getTemplate('addressList'));
		$addrFields = $addrForm->buildForm($this->getFields('addressList'));
		$addressList = array();
		foreach($addresses as $rec) {
			$addrForm->addData($rec);
			$addressList[] = $addrForm->show();
		}
		$this->logMessage("loadAddresses",sprintf("addresses [%s] addresslist [%s] form [%s]",print_r($addresses,true),print_r($addressList,true),print_r($addrForm,true)),2);
		$addressForm->addTag('addresses',implode('',$addressList),false);
		$addressForm->addTag('o_id',$o_id);
		if (!is_null($passed_id)) {
			$this->logMessage("loadAddresses",sprintf("returning normal show pass_id [%s] isAjax [%s]",$passed_id,$this->isAjax()),3);
			return $addressForm->show();
		}
		else {
			$this->logMessage("loadAddresses",sprintf("returning ajax result show pass_id [%s] isAjax [%s]",$passed_id,$this->isAjax()),3);
			return $this->ajaxReturn(array('status'=>'true','html'=>$addressForm->show()));
		}
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
		if (!($data = $this->fetchSingleTest('select a.* from addresses a where a.id = %d and a.ownertype = "order" and a.ownerid = %d',$a_id,$o_id))) {
			$data = array('id'=>0,'ownertype'=>'order','ownerid'=>$o_id);
			$addresses = array();
		}
		else 
			$addresses = $this->fetchAllTest('select * from addresses where ownertype = "order" and ownerid = %d and deleted = 0',$o_id);
		$form = new Forms();
		$form->init($this->getTemplate('editAddress'),array('name'=>'editAddress'));
		$frmFields = $this->getFields('editAddress');
		if (count($addresses) > 0) {
			$frmFields['delete'] = array('type'=>'button','value'=>'Delete Address','database'=>false,'onclick'=>sprintf('deleteAddress(%d,%d);return false;',$a_id,$o_id));
		}
		$frmFields = $form->buildForm($frmFields);
		$form->addData($data);
		$this->logMessage('editAddress',sprintf('form [%s]',print_r($form,true)),1);
		if (count($_POST) > 0 && array_key_exists('editAddress',$_POST)) {
			$form->addData($_POST);
			if ($form->validate()) {
				unset($frmFields['options']);
				foreach($frmFields as $key=>$fld) {
					if (!(array_key_exists('database',$fld) && $fld['database'] == false)) {
						if ($data['id'] > 0)
							$flds[sprintf('%s = ?',$fld['name'])] = $form->getData($fld['name']);//$_REQUEST[$fld['name']];
						else
							$flds[$fld['name']] = $form->getData($fld['name']);//$_REQUEST[$fld['name']];
					}
				}
				if ($data['id'] == 0) {
					$flds['tax_address'] = (int)$this->fetchScalarTest('select extra from code_lookups where id = %d',$form->getData('addresstype'));
					$stmt = $this->prepare(sprintf('insert into addresses(%s) values(%s)', implode(',',array_keys($flds)), str_repeat('?,', count($flds)-1).'?'));
					$stmt->bindParams(array_merge(array(str_repeat('s', count($flds))),array_values($flds)));
				}
				else {
					$flds['tax_address = ?'] = (int)$this->fetchScalarTest('select extra from code_lookups where id = %d',$form->getData('addresstype'));
					$stmt = $this->prepare(sprintf('update addresses set %s where id = %d', implode(',',array_keys($flds)),$data['id']));
					$stmt->bindParams(array_merge(array(str_repeat('s', count($flds))),array_values($flds)));
				}
				$this->beginTransaction();
				if ($stmt->execute())
					$this->commitTransaction();
				else {
					$this->rollbackTransaction();
					$this->addError('An error occurred updating the database');
				}
			}
			else $this->addError('Form validation Failed');
		}
		return $this->ajaxReturn(array('status'=>'true','html'=>$form->show()));
	}

    /**
     * @param int $fromMain
     * @return array|false|mixed|string|string[]|void
     * @throws phpmailerException
     */
    function moduleStatus($fromMain = 0) {
		if (array_key_exists('formData',$_SESSION) && array_key_exists('shippingSearchForm', $_SESSION['formData'])) {
			$msg = '';
			$_POST = $_SESSION['formData']['shippingSearchForm'];
		}
		else {
			$ct = $this->fetchScalarTest('select count(0) from %s where order_status & %d = %d',$this->m_content,STATUS_PROCESSING,STATUS_PROCESSING);
			if ($ct == 0) {
				$_POST = array('showSearchForm'=>1,'deleted'=>0,'sortby'=>'created','sortorder'=>'desc','pager'=>$this->m_perrow);
				$msg = "Showing latest orders added";
			}
			else {
				$_POST = array('showSearchForm'=>1,'order_status'=>STATUS_PROCESSING,'sortby'=>'created','sortorder'=>'asc','pager'=>$this->m_perrow);
				$msg = "Showing unshipped orders";
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
    function addItem($fromMain = false) {
		$form = new Forms();
		$form->init($this->getTemplate('addItem'));
		if ($fromMain)
			return $form->show();
		else
			return $this->show($form->show());
	}

    /**
     * @return array|mixed|string|string[]|void
     * @throws phpmailerException
     */
    function showOrder() {
		$form = new Forms();
		$form->init($this->getTemplate('showOrder'));
		$form->addData($_REQUEST);
		return $this->show($form->show());
	}

    /**
     * @param $data
     * @return mixed
     * @throws phpmailerException
     */
    function formatOrder($data) {
		$data['itemCount'] = $this->fetchScalarTest('select sum(quantity) from order_lines where order_id = %d and deleted = 0',$data['id']);
		$data['formattedTotal'] = money_format('%(.2n',$data['total']);
		$data['formattedNet'] = money_format('%(.2n',$data['net']);
		$data['formattedShipping'] = money_format('%(.2n',$data['shipping']);
		$data['formattedDiscountValue'] = money_format('%(.2n',$data['discount_value']);
		$data['formattedValue'] = money_format('%(.2n',$data['value']);
		$data['formattedTaxes'] = money_format('%(.2n',$data['taxes']);
		$data['formattedAuthorizationAmount'] = money_format('%(.2n',$data['authorization_amount']);
		if ($data['discount_type'] == 'D')
			$data['formattedDiscountRate'] = money_format('%(.2n',$data['discount_rate']);
		else
			$data['formattedDiscountRate'] = sprintf('%.2f%%',$data['discount_rate']);
		$data['formattedCreated'] = date(GLOBAL_DEFAULT_DATETIME_FORMAT,strtotime($data['created']));
		$status = $this->fetchAllTest('select * from code_lookups where type="orderStatus" order by sort');
		$tmp = array();
		foreach($status as $key=>$stat) {
			if (($data['order_status'] & (int)$stat['code']) == (int)$stat['code'])
				$tmp[] = $stat['value'];
		}
		$data['formattedStatus'] = implode(", ",$tmp);
		$data['formattedCreated'] = date(GLOBAL_DEFAULT_DATETIME_FORMAT,strtotime($data['created']));
		$data['formattedShipDate'] = date(GLOBAL_DEFAULT_DATE_FORMAT,strtotime($data['ship_date']));
		//$data['formattedShipVia'] = $this->fetchScalarTest('select value from code_lookups where code = "%s"',$data['ship_via']);
		$data['formattedShipVia'] = $this->fetchScalarTest('select value from code_lookups where id = %d',$data['ship_via']);
		$this->logMessage('formatOrder',sprintf('return [%s]',print_r($data,true)),4);
		return $data;
	}

    /**
     * @param bool $fromMain
     * @return array|mixed|string|string[]|void
     * @throws phpmailerException
     */
    function pickList($fromMain = false) {
		$form = new Forms();
		$form->init($this->getTemplate('pickList'));
		$flds = $form->buildForm($this->getFields('pickList'));
		if (count($_POST) > 0 && array_key_exists('pickList',$_POST)) {
			$form->addData($_POST);
			$valid = $form->validate();
			if ($valid && $form->getData('start_date') > $form->getData('end_date')) {
				$valid = false;
				$this->addError('Start Date cannot be after End Date');
			}
			if ($valid) {
				if (array_key_exists('export',$_REQUEST))
					$this->exportPick($form->getData('start_date'),$form->getData('end_date'));
				$recs = $this->fetchScalarAllTest('select o.id from %s o where order_status & %d  = %d and order_status & %d = 0 and order_date >= "%s 00:00:00" and order_date <= "%s 23:59:29"',
					$this->m_content, STATUS_PROCESSING, STATUS_PROCESSING, STATUS_CANCELLED | STATUS_SHIPPED | STATUS_RECURRING | STATUS_CREDIT_HOLD, 
					date('Y-m-d',strtotime($form->getData('start_date'))), date('Y-m-d', strtotime($form->getData('end_date'))));
				if (count($recs) == 0) $recs = array(0=>0);
				$form->addTag('count',count($recs));
				$sql = sprintf('select p.code, p.name, sum(d.quantity*d.qty_multiplier) as toPick from order_lines d, product p where d.deleted = 0 and d.order_id in (%s) and p.id = d.product_id group by p.code order by p.code',
						implode(',',$recs));
				$products = $this->fetchAllTest($sql);
				$this->logMessage(__FUNCTION__,sprintf('found [%d] products sql [%s]',count($products),$sql),3);
				$inner = new Forms();
				$inner->init($this->getTemplate('pickListRow'));
				$flds = $inner->buildForm($this->getFields('pickListRow'));
				$result = array();
				foreach($products as $key=>$rec) {
					$inner->addData($rec);
					$result[] = $inner->show();
				}
				$form->addTag('products',implode('',$result),false);
			}
		}
		if ($fromMain)
			return $form->show();
		else
			return $this->show($form->show());
	}

    /**
     * @param $sd
     * @param $ed
     * @return void
     * @throws phpmailerException
     */
    function exportPick($sd, $ed) {
		$form = new Forms();
		$form->init($this->getTemplate('exportPick'));
		$inner = new Forms();
		$inner->init($this->getTemplate('exportPickRow'));
		$flds = $inner->buildForm($this->getFields('exportPickRow'));
		$recs = $this->fetchScalarAllTest('select o.id from %s o where order_status & %d  = %d and order_status & %d = 0 and order_date >= "%s 00:00:00" and order_date <= "%s 23:59:29"',
			$this->m_content, STATUS_PROCESSING, STATUS_PROCESSING, STATUS_CANCELLED | STATUS_SHIPPED | STATUS_RECURRING | STATUS_CREDIT_HOLD, 
			date('Y-m-d',strtotime($sd)), date('Y-m-d', strtotime($ed)));
		$form->addTag('count',count($recs));
		if (count($recs) == 0) $recs = array(0=>0);
		$sql = sprintf('select p.code, p.name, sum(d.quantity*d.qty_multiplier) as toPick from order_lines d, product p where d.deleted = 0 and d.order_id in (%s) and p.id = d.product_id group by p.code order by p.code',
				implode(',',$recs));
		$products = $this->fetchAllTest($sql);
		$this->logMessage(__FUNCTION__,sprintf('found [%d] products sql [%s]',count($products),$sql),3);
		$flds = $inner->buildForm($flds);
		$result = array();
		foreach($products as $key=>$rec) {
			foreach($rec as $subkey=>$subval) {
				$this->logMessage(__FUNCTION__,sprintf('key [%s] val [%s] strpos [%s]',$subkey,$subval,strpos($subval,'"')),1);
				if (strpos($subval,'"') !== false)
					$rec[$subkey] = str_replace('"','""',$subval);
			}
			$inner->addData($rec);
			$result[] = $inner->show();
		}
		$form->addTag('products',implode("\r\n",$result),false);
		header('Content-Type: application/csv');
		header(sprintf('Content-Disposition: attachment; filename=picklist-%s-%s.csv',$sd,$ed));
		header('Pragma: no-cache');
		echo $form->show();
		exit;
	}

    /**
     * @param bool $fromMain
     * @return array|mixed|string|string[]|void
     * @throws phpmailerException
     */
    function packList($fromMain = false) {
		$form = new Forms();
		$form->init($this->getTemplate('packList'));
		$flds = $form->buildForm($this->getFields('packList'));
		$this->logMessage(__FUNCTION__,sprintf('test 1'),1);
		if (count($_POST) > 0 && array_key_exists('packList',$_POST)) {
			$form->addData($_POST);
			$valid = $form->validate();
			if ($valid && $form->getData('start_date') > $form->getData('end_date')) {
				$valid = false;
				$this->addError('Start Date cannot be after End Date');
			}
			if ($valid) {
				if (array_key_exists('export',$_REQUEST))
					$this->exportPack($form->getData('start_date'),$form->getData('end_date'));
				$recs = $this->fetchScalarAllTest('select o.id from %s o where order_status & %d  = %d and order_status & %d = 0 and order_date >= "%s 00:00:00" and order_date <= "%s 23:59:29"',
						$this->m_content, STATUS_PROCESSING, STATUS_PROCESSING, STATUS_CANCELLED | STATUS_SHIPPED | STATUS_RECURRING | STATUS_CREDIT_HOLD, 
						date('Y-m-d',strtotime($form->getData('start_date'))), date('Y-m-d', strtotime($form->getData('end_date'))));
				$form->addTag('count',count($recs));
				if (count($recs) == 0) $recs = array(0=>0);
				$sql = sprintf('select d.*, d.quantity * d.qty_multiplier as toPack,  p.code, p.name, l.value as formattedShipVia from order_lines d, product p, orders o left join code_lookups l on l.id = o.ship_via where d.deleted = 0 and o.id = d.order_id and d.order_id in (%s) and p.id = d.product_id group by d.order_id, d.line_id',
						implode(',',$recs));
				$products = $this->fetchAllTest($sql);
				$this->logMessage(__FUNCTION__,sprintf('found [%d] products sql [%s]',count($products),$sql),3);
				$inner = new Forms();
				$inner->init($this->getTemplate('packListRow'));
				$flds = $inner->buildForm($this->getFields('packListRow'));
				$tForm = new Forms();
				$tForm->init($this->getTemplate('packTotal'));
				$flds = $tForm->buildForm($this->getFields('packTotal'));
				$result = array();
				$order = count($products) > 0 ? $products[0]['order_id'] : 0;
				$qty = 0;
				$ct = 0;
				foreach($products as $key=>$rec) {
					$inner->reset();
					if ($order != $rec['order_id']) {
						$tForm->addTag('quantity',$qty);
						$result[] = $tForm->show();
						$qty = 0;
						$ct = 0;
						$order = $rec['order_id'];
					}
					else {
						if ($ct > 0) {
							unset($rec['order_id']);
							unset($rec['formattedShipVia']);
						}
					}
					$ct += 1;
					$qty += $rec['toPack'];
					$inner->addData($rec);
					$result[] = $inner->show();
				}
				$tForm->addTag('quantity',$qty);
				$result[] = $tForm->show();
				$form->addTag('products',implode('',$result),false);
			}
		}
		if ($fromMain)
			return $form->show();
		else
			return $this->show($form->show());
	}

    /**
     * @param $sd
     * @param $ed
     * @return void
     * @throws phpmailerException
     */
    function exportPack($sd, $ed) {
		$form = new Forms();
		$form->init($this->getTemplate('exportPack'));
		$inner = new Forms();
		$inner->init($this->getTemplate('exportPackRow'));
		$flds = $inner->buildForm($this->getFields('exportPackRow'));
		$recs = $this->fetchScalarAllTest('select o.id from %s o where order_status & %d  = %d and order_status & %d = 0 and order_date >= "%s 00:00:00" and order_date <= "%s 23:59:29"',
				$this->m_content, STATUS_PROCESSING, STATUS_PROCESSING, STATUS_CANCELLED | STATUS_SHIPPED | STATUS_RECURRING | STATUS_CREDIT_HOLD, 
				date('Y-m-d',strtotime($sd)), date('Y-m-d', strtotime($ed)));
		if (count($recs) == 0) $recs = array(0=>0);
		$sql = sprintf('select d.*, d.quantity*d.qty_multiplier as toPack, p.code, p.name from order_lines d, product p where d.deleted = 0 and d.order_id in (%s) and p.id = d.product_id group by d.order_id, d.line_id',
					implode(',',$recs));
		$products = $this->fetchAllTest($sql);
		$result = array();
		foreach($products as $key=>$rec) {
			foreach($rec as $subkey=>$subval) {
				$this->logMessage(__FUNCTION__,sprintf('key [%s] val [%s] strpos [%s]',$subkey,$subval,strpos($subval,'"')),1);
				if (strpos($subval,'"') !== false)
					$rec[$subkey] = str_replace('"','""',$subval);
			}
			$inner->addData($rec);
			$result[] = $inner->show();
		}
		$form->addTag('products',implode("\r\n",$result),false);
		header('Content-Type: application/csv');
		header(sprintf('Content-Disposition: attachment; filename=packlist-%s-%s.csv',$sd,$ed));
		header('Pragma: no-cache');
		echo $form->show();
		exit;
	}

    /**
     * @param $fromMain
     * @return array|mixed|string|string[]|void
     * @throws phpmailerException
     */
    function confirmation($fromMain = false) {
		$form = new Forms();
		$form->init($this->getTemplate('confirmation'));
		$flds = $form->buildForm($this->getFields('confirmation'));
		if (count($_POST) > 0 && array_key_exists('confirmation',$_POST)) {
			$form->addData($_POST);
			$valid = $form->validate();
			if ($valid && $form->getData('start_date') > $form->getData('end_date')) {
				$valid = false;
				$this->addError('Start Date cannot be after End Date');
			}
			if (array_key_exists("reprint",$_REQUEST)) {
				$file = $this->reprint($form->getData('start_date'),$form->getData('end_date'));
				if (strlen($file) == 0) {
					$this->addError("No Orders to reprint were found");
				}
				else {
					$form->addTag("download",sprintf("<a href='/modit/ajax/getCSV/shipping?filename=%s'>Download Your Packing Slips Here</a>",$file));
				}
			}
			$updated = array();
			if (array_key_exists('update',$_REQUEST) && $_REQUEST['update'] == 'Update Orders') {
				if (array_key_exists('shipped',$_REQUEST)) {
					foreach($_REQUEST['shipped'] as $key=>$value) {
						$lines = $this->fetchAllTest('select * from order_lines where order_id = %d',$key);
						$status = STATUS_SHIPPED;
						$valid = true;
						$this->beginTransaction();
						foreach($lines as $subkey=>$line) {
							if (array_key_exists($key.'|'.$line['line_id'],$_REQUEST['line_qty'])) {
								//
								//	checking for partial shipments - not sure what the final fix is
								//
								$tmp = $_REQUEST['line_qty'][$key.'|'.$line['line_id']] / $line["qty_multiplier"];
								$this->logMessage(__FUNCTION__,sprintf("tmp test is [%d] vs [%d]",$tmp,$tmp * $line["qty_multiplier"]),1);
								if ($tmp * $line["qty_multiplier"] != $_REQUEST['line_qty'][$key.'|'.$line['line_id']]) {
									$this->addError(sprintf("Detected a partial shipment on order #%, not supported yet",$line["order_id"]));
									$valid = false;
								}
								else {
									$valid = $valid && $this->execute(sprintf('update order_lines set shipped = shipped + %d where id = %d',$tmp,$line['id']));
									$line['shipped'] += $tmp;
								}
							}
							//$this->logMessage(__FUNCTION__,sprintf('checking for overship [%s] key [%s] request [%s]',print_r($line,true),$key,$_REQUEST['line_qty'][$key.'|'.$line['line_id']]),1);
							if ($line['shipped'] > $line['quantity']) {
								$form->addFormError(sprintf('Trying to ship more than ordered on Order #%d line %d, Ordered %d, Cumulative shipping %d',$key,$line['line_id'],$line['quantity'], $line['shipped']));
								$valid = false;
							}
							else
								if ($line['shipped'] < $line['quantity'])
									$status = STATUS_PARTIAL_SHIPMENT;
						}
						$curr_status = $this->fetchScalarTest('select order_status from orders where id = %d',$key);
						$tmp = array(
							'ship_tracking_code'=>$_REQUEST['ship_tracking_code'][$key],
							'ship_comments'=>nl2br($_REQUEST['ship_comments'][$key]),
							'ship_date'=>date("Y-m-d",strtotime($_REQUEST['ship_date'][$key]))
						);
						if ($status == STATUS_SHIPPED)
							$tmp['order_status'] = ($curr_status & !STATUS_PROCESSING) | $status;
						else
							$tmp['order_status'] = ($curr_status | $status);
						$stmt = $this->prepare(sprintf('update orders set %s=? where id = %d',
									implode('=?, ', array_keys($tmp)), $key));
						$stmt->bindParams(array_merge(array(str_repeat('s',count($tmp))),array_values($tmp)));
						$valid = $valid && $stmt->execute();
						if ($valid) {
							$this->commitTransaction();
							$form->addFormSuccess(sprintf('Order # %d updated',$key));
							$updated[] = $key;
							if (array_key_exists('email',$_REQUEST) && array_key_exists($key,$_REQUEST['email'])) {
								$emails = $this->configEmails("ecommerce");
								if (count($emails) == 0)
									$emails = $this->configEmails("contact");
								$mailer = new MyMailer();
								$mailer->Subject = sprintf("Order Status Update - %s", SITENAME);
								$body = new Forms();
								$body->setOption('formDelimiter','{{|}}');
								$html = $this->getHtmlForm('orderStatus','product');
								$o_fields = $body->buildForm($this->getFields('orderStatus'));
								$body->setHTML($html);
								$order = $this->fetchSingleTest('select o.*, m.firstname, m.lastname, m.email from orders o, members m where o.id = %d and m.id = o.member_id',$key);
								$body->addData($this->formatOrder($order));
								$mailer->Body = $body->show();
								$mailer->From = $emails[0]['email'];
								$mailer->FromName = $emails[0]['name'];
								$this->logMessage('addContent',sprintf("mailer object [%s]",print_r($mailer,true)),1);
								$mailer->IsHTML(true);	
								$mailer->addAddress($order['email'],$order['firstname'].' '.$order['lastname']);
								if (!$mailer->Send()) {
									$this->addMessage('There was an error sending the email for order #'.$key);
									$this->logMessage('addContent',sprintf("Email send failed [%s]",print_r($mailer,true)),1,true);
								}
							}
						}
						else {
							$form->addFormError("An Error occurred");
							$this->rollbackTransaction();
							//
							//	save any comments and tracking id's so they don't have to reenter them
							//
							unset($tmp['order_status']);
							$stmt = $this->prepare(sprintf('update orders set %s=? where id = %d',
									implode('=?, ', array_keys($tmp)), $key));
							$stmt->bindParams(array_merge(array(str_repeat('s',count($tmp))),array_values($tmp)));
							$stmt->execute();
						}
					}
				}
				if (count($updated) > 0) {
					$file = $this->shippingCSV($updated);
					$form->addTag("download",sprintf("<a href='/modit/ajax/getCSV/shipping?filename=%s'>Download Your Packing Slips Here</a>",$file));
				}
				$valid = true;
			}
			if ($valid) {
				if (array_key_exists("order_id",$_REQUEST) && $_REQUEST["order_id"] > 0) {
					//$orders = $this->fetchAllTest('select o.*, l.value as formattedShipVia from %s o left join code_lookups l on l.code = o.ship_via where order_status & %d = 0 and o.id = %d order by o.id',
					$orders = $this->fetchAllTest('select o.*, l.value as formattedShipVia from %s o left join code_lookups l on l.id = o.ship_via where order_status & %d = 0 and o.id = %d order by o.id',
						$this->m_content, STATUS_CANCELLED | STATUS_RECURRING | STATUS_CREDIT_HOLD, $_REQUEST["order_id"]);
				}
				else {
					if (array_key_exists('ship_via',$_REQUEST) && $_REQUEST['ship_via'] > 0) {
						$via = sprintf(' and ship_via = %d',$_REQUEST['ship_via']);
					}
					else $via = '';
/*
					$orders = $this->fetchAllTest('select o.*, l.value as formattedShipVia, o1.id as recurringId from %s o left join code_lookups l on l.code = o.ship_via left join orders o1 on o1.authorization_transaction = o.id where (o.order_status & %d  = %d or o.order_status & %d  = %d and o.order_status & %d = 0 and o.order_date >= "%s 00:00:00" and o.order_date <= "%s 23:59:29" %s order by o.id',
						$this->m_content, STATUS_PROCESSING, STATUS_PROCESSING, STATUS_SHIPPED, STATUS_SHIPPED, STATUS_CANCELLED | STATUS_RECURRING | STATUS_CREDIT_HOLD,
						date('Y-m-d',strtotime($form->getData('start_date'))), date('Y-m-d', strtotime($form->getData('end_date'))),$via));
*/					
					$orders = $this->fetchAllTest('select o.*, l.value as formattedShipVia from %s o left join code_lookups l on l.id = o.ship_via and l.type = "ship_via" where (o.order_status & %d  = %d or o.order_status & %d  = %d) and o.order_status & %d = 0 and o.order_date >= "%s 00:00:00" and o.order_date <= "%s 23:59:29" %s order by o.id',
						$this->m_content, STATUS_PROCESSING, STATUS_PROCESSING, STATUS_SHIPPED, STATUS_SHIPPED, STATUS_CANCELLED | STATUS_RECURRING | STATUS_CREDIT_HOLD,
						date('Y-m-d',strtotime($form->getData('start_date'))), date('Y-m-d', strtotime($form->getData('end_date'))),$via);
				}
				if (!is_array($orders)) $orders = array();
				$form->setData("count",count($orders));
				$inner = new Forms();
				$form->addTag('%%count%%',count($orders));
				$order = new Forms();
				$order->init($this->getTemplate('confirmationOrder'));
				$flds = $order->buildForm($this->getFields('confirmationOrder'));
				$details = new Forms();
				$details->init($this->getTemplate('confirmationOrderRow'));
				$flds = $details->buildForm($this->getFields('confirmationOrderRow'));
				$result = array();
				foreach($orders as $key=>$rec) {
					$inner->reset();
					$rec['ship_comments'] = strip_tags($rec['ship_comments']);					$rec['ship_date'] = $rec['ship_date'] == '0000-00-00' ? date('Y-m-d') : $rec['ship_date'];
					$order->addData($rec);
					$o = $this->fetchAllTest('select o.*, o.quantity * o.qty_multiplier as toShip, o.shipped * o.qty_multiplier as alreadyShipped,  p.code, p.name from order_lines o, product p where o.deleted = 0 and o.order_id = %d and p.id = o.product_id order by o.line_id',$rec['id']);
					$dtls = array();
					$member = $this->fetchSingleTest("select m.* from members m, addresses a where m.id = %d",$rec["member_id"]);
					$order->addData(array('member'=>$member));
					if ($address = $this->fetchSingleTest("select * from addresses where ownertype='order' and ownerid = %d and tax_address = 1",$rec['id'])) {
						$order->addData(array('address'=>Address::formatData($address)));
					}
					foreach($o as $subkey=>$line) {
						$line['line_qty'] = $line['quantity']*$line['qty_multiplier'] - $line['shipped'];
						$details->addData($line);
						$dtls[] = $details->show();
					}
					$order->addTag('lines',implode('',$dtls),false);
					$result[] = $order->show();
				}
				$form->addTag('orders',implode('',$result),false);
			}
		}
		if ($fromMain)
			return $form->show();
		else
			return $this->show($form->show());
	}

    /**
     * @param $orders
     * @return false|string|void
     * @throws phpmailerException
     */
    function shippingCSV($orders) {
		$csv = array();
		foreach($orders as $order) {
			$flds = array();
			//$o = $this->fetchSingleTest("select o.*, c.name as coupon_name, l.value as carrier from orders o left join coupons c on c.id = o.coupon_id left join code_lookups l on l.code = o.ship_via where o.id = %d",$order);
			$o = $this->fetchSingleTest("select o.*, c.name as coupon_name, l.value as carrier from orders o left join coupons c on c.id = o.coupon_id left join code_lookups l on l.id = o.ship_via where o.id = %d",$order);
			unset($o["authorization_info"]);
			$o["ship_comments"] = str_replace("\r\n\r\n","\r\n",str_replace("<br />","",$o["ship_comments"]));
			$o["order_date"] = date(GLOBAL_DEFAULT_DATETIME_FORMAT,strtotime($o["order_date"]));
			$o["created"] = date(GLOBAL_DEFAULT_DATETIME_FORMAT,strtotime($o["created"]));
			$o["ship_date"] = date(GLOBAL_DEFAULT_DATE_FORMAT,strtotime($o["ship_date"]));
			$flds["order"] = $o;
			$flds["bill_to"] = $this->fetchSingleTest("select * from addresses where ownerid = %d and ownertype = 'order' and tax_address = 0",$o["id"]);
			if (is_array($flds["bill_to"])) {
				$flds["bill_to"] = Address::formatData($flds["bill_to"]);
				unset($flds["bill_to"]["formattedAddress"]);
				unset($flds["bill_to"]["encodedAddress"]);
				unset($flds["bill_to"]["viewMap"]);
			}
			$flds["ship_to"] = $this->fetchSingleTest("select * from addresses where ownerid = %d and ownertype = 'order' and tax_address = 1",$o["id"]);
			if (is_array($flds["ship_to"])) {
				$flds["ship_to"] = Address::formatData($flds["ship_to"]);
				unset($flds["ship_to"]["formattedAddress"]);
				unset($flds["ship_to"]["encodedAddress"]);
				unset($flds["ship_to"]["viewMap"]);
			}
			$lines = $this->fetchAllTest("select o.*, p.code, p.name, po.teaser from order_lines o left join product_options po on po.id = o.options_id left join product p on p.id = o.product_id where o.deleted = 0 and order_id = %d order by line_id",$o["id"]);
			$i = 0;
			foreach($lines as $key=>$line) {
				$flds["products_".$i] = $line;
				$i++;
			}
			while ($i < 10) {
				$flds["products_".$i] = $flds["products_0"];
				foreach($flds["products_".$i] as $key=>$value) {
					$flds["products_".$i][$key]="";
				}
				$i++;
			}
			$aggregate = array();
			foreach($flds as $key=>$data) {
				if (!is_array($data)) {
					switch($key) {
					case "bill_to":
						$data = $flds["ship_to"];
						break;
					case "products_1":
					case "products_2":
					case "products_3":
					case "products_4":
					case "products_5":
					case "products_6":
					case "products_7":
					case "products_8":
					case "products_9":
						foreach($flds["products_0"] as $xxx=>$yyy) {
							$data[$xxx] = "";
						}
						break;
					default:
						$this->logMessage(__FUNCTION__,sprintf("unexpected data missing in export [%s] from [%s]", $key, print_r($flds,true)),1);
					}
				}
				if (is_array($data)) {
					foreach($data as $subkey=>$value) {
						$aggregate[$key."-".$subkey] = str_replace("\r","|",str_replace("\n","|",str_replace("\r\n","|",$value)));
					}
				}
			}
			$csv[] = $aggregate;
			$this->logMessage(__FUNCTION__,sprintf("flds [%s] aggregate [%s]",print_r($flds,true),print_r($aggregate,true)),1);
		}
		if (count($csv) > 0) {
			$file = tempnam("/tmp","shipping");
			$fh = fopen($file,"w");
			$hdr = array();
			foreach($csv[0] as $key=>$value) {
				$hdr[] = $key;
			}
			fwrite($fh,'"'.implode('","',$hdr).'"');
			foreach($csv as $key=>$data) {
				$tmp = array();
				foreach($data as $subkey=>$value) {
					if (is_array($value)) {
						$tmp[] = implode("|",$value);
					}
					else
						$tmp[] = $value;
				}
				fwrite($fh,"\n".'"'.implode('","',$tmp).'"');
			}
			$this->logMessage(__FUNCTION__,sprintf("file name is [%s]",$file),1);
			fclose($fh);
			return $file;
		}
	}

    /**
     * @param $sd
     * @param $ed
     * @return false|string|void
     * @throws phpmailerException
     */
    function reprint($sd, $ed) {
		$file="";
		if (array_key_exists("reprint",$_REQUEST)) {
			if (array_key_exists("order_id",$_REQUEST) && $_REQUEST["order_id"] > 0)
				$orders = $this->fetchScalarAllTest('select id from %s where order_status & %d  = %d and order_status & %d = 0 and id = %d',
						$this->m_content, STATUS_SHIPPED, STATUS_SHIPPED, STATUS_CANCELLED | STATUS_RECURRING | STATUS_CREDIT_HOLD, $_REQUEST["order_id"]);
			else
				$orders = $this->fetchScalarAllTest('select id from %s where order_status & %d  = %d and order_status & %d = 0 and order_date >= "%s 00:00:00" and order_date <= "%s 23:59:29" order by id',
						$this->m_content, STATUS_SHIPPED, STATUS_SHIPPED, STATUS_CANCELLED | STATUS_RECURRING | STATUS_CREDIT_HOLD,
						date('Y-m-d',strtotime($sd)), date('Y-m-d', strtotime($ed)));
			$this->logMessage(__FUNCTION__,sprintf("found %d orders to reprint",count($orders)),1);
			if (count($orders) > 0) {
				$file = $this->shippingCSV($orders);
			}
			else $this->addError("No Orders were found");
		}
		return $file;
	}

    /**
     * @return void
     */
    function getCSV() {
		if (array_key_exists("filename",$_REQUEST)) {
			ob_end_clean();
			header('Content-type: application/text');
			header('Content-Disposition: attachment; filename="ShippingConfirmation.csv"');
			$filename = strval($_REQUEST["filename"]);
			$info = pathinfo($filename);
			if($info['extension'] == 'csv'){
				readfile($filename);
			}			
			exit();
		}
	}
}

?>