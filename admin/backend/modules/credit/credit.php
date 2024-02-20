<?php

include "./classes/quickbooks/autoload.php";
use QuickBooksOnline\API\Core\ServiceContext;
use QuickBooksOnline\API\DataService\DataService;
use QuickBooksOnline\API\PlatformService\PlatformService;
use QuickBooksOnline\API\Core\Http\Serialization\XmlObjectSerializer;
use QuickBooksOnline\API\Facades\Invoice;
use QuickBooksOnline\API\Facades\Line;
use QuickBooksOnline\API\Facades\Payment;

/**
 * Credit feature for backend
 */
class credit extends Backend {

	private $m_content = 'orders';
	private $m_perrow = 5;

    /**
     * @throws phpmailerException
     */
	public function __construct() {
		$this->m_perrow = defined('GLOBAL_PER_PAGE') ? GLOBAL_PER_PAGE : 5;
		$this->M_DIR = 'backend/modules/credit/';
		$this->setTemplates(
			array(
				'main'=>$this->M_DIR.'credit.html',
				'articleList'=>$this->M_DIR.'forms/articleList.html',
				'addContent'=>$this->M_DIR.'forms/addContent.html',
				'showSearchForm'=>$this->M_DIR.'forms/searchForm.html',
				'header'=>$this->M_DIR.'forms/heading.html',
				'dailyLog'=>$this->M_DIR.'forms/dailyLog.html',
				'dailyLogRow'=>$this->M_DIR.'forms/dailyLogRow.html',
				'showPageProperties'=>$this->M_DIR.'forms/showPageProperties.html',
				'showPagePropertiesRow'=>$this->M_DIR.'forms/showPagePropertiesRow.html',
				'getRemaining'=>$this->M_DIR.'forms/getRemaining.html',
				'getRemainingRow'=>$this->M_DIR.'forms/getRemainingRow.html',
				'editRecurring'=>$this->M_DIR.'forms/editRecurring.html',
				'dailyDetails'=>$this->M_DIR.'forms/dailyDetails.html',
				'dailyDetailsRow'=>$this->M_DIR.'forms/dailyDetailsRow.html',
				'nightlySchedule'=>$this->M_DIR.'forms/nightlySchedule.html',
				'nightlyScheduleRow'=>$this->M_DIR.'forms/nightlyScheduleRow.html',
				'addSchedule'=>$this->M_DIR.'forms/addSchedule.html',
				'exchange'=>$this->M_DIR.'forms/exchange.html',
				'exchangeRow'=>$this->M_DIR.'forms/exchangeRow.html',
				'addCurrency'=>$this->M_DIR.'forms/addCurrency.html',
				'salesReports'=>$this->M_DIR.'forms/salesReports.html',
				'salesReportsRow'=>$this->M_DIR.'forms/salesReportsRow.html',
				'recurringOrders'=>$this->M_DIR.'forms/recurringOrders.html',
				'recurringOrdersRow'=>$this->M_DIR.'forms/recurringOrdersRow.html',
				'exportSales'=>$this->M_DIR.'forms/exportSales.html',
				'exportSalesRow'=>$this->M_DIR.'forms/exportSalesRow.html',
				'exportDetails'=>$this->M_DIR.'forms/exportDetails.html',
				'exportDetailsRow'=>$this->M_DIR.'forms/exportDetailsRow.html',
				'allocation'=>$this->M_DIR.'forms/allocation.html',
				'allocationRow'=>$this->M_DIR.'forms/allocationRow.html',
				'QB'=>$this->M_DIR.'forms/QB.html',
				'QBConnect'=>$this->M_DIR.'forms/QBConnect.html',
				'QBRow'=>$this->M_DIR.'forms/QBRow.html',
				//'qbExport'=>$this->M_DIR.'forms/qbExport.html',
				//'qbExportRow'=>$this->M_DIR.'forms/qbExportRow.html',
				'qbExportCheck'=>$this->M_DIR.'forms/qbExportCheck.html',
				'qbExportCheckRow'=>$this->M_DIR.'forms/qbExportCheckRow.html',
				'qbDetails'=>$this->M_DIR.'forms/qbDetails.html',
				'qbDetailsRow'=>$this->M_DIR.'forms/qbDetailsRow.html',
				'qbProcess'=>$this->M_DIR.'forms/qbProcess.html',
				'qbProcessRow'=>$this->M_DIR.'forms/qbProcessRow.html',
				'qbInvoiceDetails'=>$this->M_DIR.'forms/qbInvoiceDetails.html',
				'qbInvoiceDetailsRow'=>$this->M_DIR.'forms/qbInvoiceDetailsRow.html',
				'unpaidBillNow'=>$this->M_DIR.'forms/unpaidBillNow.html',
				'unpaidBillNowRow'=>$this->M_DIR.'forms/unpaidBillNowRow.html',
				'authHistory'=>$this->M_DIR.'forms/authHistory.html',
				'authHistoryRow'=>$this->M_DIR.'forms/authHistoryRow.html',
				'processNow'=>$this->M_DIR.'forms/processNow.html',
				'processNowRow'=>$this->M_DIR.'forms/processNowRow.html',
				'removeSchedule'=>$this->M_DIR.'forms/removeSchedule.html',
				'editInvoices'=>$this->M_DIR.'forms/editInvoices.html',
				'editInvoicesRow'=>$this->M_DIR.'forms/editInvoicesRow.html',
				'getBilled'=>$this->M_DIR.'forms/getBilled.html',
				'getBilledRow'=>$this->M_DIR.'forms/getBilledRow.html',
				'getInvoice'=>$this->M_DIR.'forms/getInvoice.html',
				'getInvoiceRow'=>$this->M_DIR.'forms/getInvoiceRow.html',
				'consolidatedInvoices'=>$this->M_DIR.'forms/consolidated.html',
				'consolidatedInvoicesRow'=>$this->M_DIR.'forms/consolidatedRow.html'
			)
		);
		$this->setFields(array(
			'header'=>array(),
			'addContent'=>array(
				'options'=>array('name'=>'addContent','action'=>'/modit/ajax/addContent/credit','database'=>false),
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
				'opt_order_id'=>array('type'=>'select','lookup'=>'search_options'),
				'order_id'=>array('type'=>'input','required'=>false),
				'status'=>array('type'=>'select','required'=>false,'lookup'=>'orderStatus'),
				'opt_name'=>array('type'=>'select','name'=>'opt_name','lookup'=>'search_string'),
				'name'=>array('type'=>'input','required'=>false),
				'pager'=>array('type'=>'select','required'=>true,'value'=>$this->m_perrow,'lookup'=>'paging','id'=>'pager'),
				'pagenum'=>array('type'=>'hidden','value'=>1),
				'perpage'=>array('type'=>'hidden','value'=>$this->m_perrow,'name'=>'pager'),
				'submit'=>array('type'=>'submitbutton','value'=>'Search')
			),
			'articleList' => array(
				'id'=>array('type'=>'tag'),
				'title'=>array('type'=>'tag'),
				'order_date'=>array('type'=>'datetimestamp'),
				'deleted'=>array('type'=>'booleanIcon')
			),
			'dailyLog'=>array(
				'from'=>array('type'=>'datepicker','required'=>false),
				'to'=>array('type'=>'datepicker','required'=>false),
				'dailyLog'=>array('type'=>'hidden','value'=>1),
				'pager'=>array('type'=>'select','required'=>true,'value'=>$this->m_perrow,'lookup'=>'paging','id'=>'pager'),
				'pagenum'=>array('type'=>'hidden','value'=>1),
				'submit'=>array('type'=>'submitbutton','value'=>'Search')
			),
			'dailyLogRow'=>array(
				'started'=>array('type'=>'datetimestamp'),
				'completed'=>array('type'=>'datetimestamp'),
				'bill_date'=>array('type'=>'datestamp'),
				'start_date'=>array('type'=>'datestamp'),
				'end_date'=>array('type'=>'datestamp')
			),
			'showPagePropertiesRow'=>array(
				'billing_date'=>array('type'=>'datestamp'),
				'billed_on'=>array('type'=>'datetimestamp'),
				'authorization_amount'=>array('type'=>'currency')
			),
			'getRemaining'=>array(
				'pagenum'=>array('type'=>'hidden'),
				'o_id'=>array('type'=>'hidden')
			),
			'getRemainingRow'=>array(
				'billing_date'=>array('type'=>'datestamp')
			),
			'showPageProperties'=>array(
				'pager'=>array('type'=>'select','required'=>true,'value'=>$this->m_perrow,'lookup'=>'paging','id'=>'pager'),
				'pagenum'=>array('type'=>'hidden','value'=>1),
				'order_date'=>array('type'=>'datetimestamp'),
				'order_status'=>array('type'=>'select','multiple'=>true,'lookup'=>'orderStatus','database'=>false,'id'=>'order_status','enabled'=>false),
				'authorization_amount'=>array('type'=>'currency'),
				'value'=>array('type'=>'currency'),
				'discount_value'=>array('type'=>'currency'),
				'line_discount'=>array('type'=>'currency'),
				'shipping'=>array('type'=>'currency'),
				'taxes'=>array('type'=>'currency'),
				'total'=>array('type'=>'currency'),
				'ship_date'=>array('type'=>'datestamp')
			),
			'getBilled'=>array(
				'pager'=>array('type'=>'select','required'=>true,'value'=>$this->m_perrow,'lookup'=>'paging','id'=>'pager','class'=>'form-control'),
				'pagenum'=>array('type'=>'hidden','value'=>1),
				'o_id'=>array('type'=>'hidden')
			),
			'getBilledRow'=>array(
				'billing_date'=>array('type'=>'datestamp'),
				'billed_on'=>array('type'=>'datetimestamp'),
				'authorization_amount'=>array('type'=>'currency')
			),
			'editRecurring'=>array(
				'billing_date'=>array('type'=>'datepicker','required'=>true),
				'adjustment'=>array('type'=>'checkbox','value'=>1,'checked'=>'checked'),
				'editRecurring'=>array('type'=>'hidden','value'=>1),
				'r_id'=>array('type'=>'hidden'),
				'submit'=>array('type'=>'submitbutton','value'=>'Update Period')
			),
			'dailyDetails'=>array(
				'pager'=>array('type'=>'select','required'=>true,'value'=>$this->m_perrow,'lookup'=>'paging','id'=>'altPager'),
				'pagenum'=>array('type'=>'hidden','value'=>1),
				'perpage'=>array('type'=>'hidden','value'=>$this->m_perrow,'name'=>'pager'),
				'r_id'=>array('type'=>'hidden')
			),
			'nightlySchedule'=>array(
				'from'=>array('type'=>'datepicker','required'=>false),
				'to'=>array('type'=>'datepicker','required'=>false),
				'nightlySchedule'=>array('type'=>'hidden','value'=>1),
				'pager'=>array('type'=>'select','required'=>true,'value'=>$this->m_perrow,'lookup'=>'paging','id'=>'pager'),
				'pagenum'=>array('type'=>'hidden','value'=>1),
				'submit'=>array('type'=>'submitbutton','value'=>'Search'),
				'add'=>array('type'=>'button','value'=>'Add a Date','class'=>'def_field_submit','onclick'=>'addSchedule()')
			),
			'nightlyScheduleRow'=>array(
				'start_date'=>array('type'=>'datestamp'),
				'end_date'=>array('type'=>'datestamp'),
				'bill_date'=>array('type'=>'datestamp')
			),
			'addSchedule'=>array(
				'bill_date'=>array('type'=>'datepicker','required'=>true),
				'start_date'=>array('type'=>'datepicker','required'=>true),
				'end_date'=>array('type'=>'datepicker','required'=>true),
				'addSchedule'=>array('type'=>'hidden','value'=>1),
				'submit'=>array('type'=>'submitbutton','value'=>'Save'),
				's_id'=>array('type'=>'hidden','value'=>'%%id%%')
			),
			'exchange'=>array(
				'exchange'=>array('type'=>'hidden','value'=>1),
				'currency_id'=>array('type'=>'select','required'=>false,'idlookup'=>'currencies'),
				'submit'=>array('type'=>'submitbutton','value'=>'Search'),
				'from'=>array('type'=>'datepicker','required'=>false),
				'to'=>array('type'=>'datepicker','required'=>false),
				'addCurrency'=>array('type'=>'button','class'=>'def_field_submit','value'=>'Add a Rate','onclick'=>'addExchange(0);return false;')
			),
			'exchangeRow'=>array(
				'effective_date'=>array('type'=>'datestamp')
			),
			'addCurrency'=>array(
				'currency_id'=>array('type'=>'select','idlookup'=>'currencies','required'=>true),
				'effective_date'=>array('type'=>'datepicker','required'=>true),
				'exchange_rate'=>array('type'=>'textfield','validation'=>'number','required'=>true),
				'save'=>array('type'=>'submitButton','database'=>false,'value'=>'Save'),
				'e_id'=>array('type'=>'hidden','value'=>0,'database'=>false),
				'addCurrency'=>array('type'=>'hidden','value'=>1,'database'=>false)
			),
			'salesReports'=>array(
				'from'=>array('type'=>'datepicker','required'=>true),
				'to'=>array('type'=>'datepicker','required'=>true),
				'order_status'=>array('type'=>'select','required'=>true,'multiple'=>true,'lookup'=>'orderStatus'),
				'salesReports'=>array('type'=>'hidden','value'=>1),
				'pager'=>array('type'=>'select','required'=>true,'value'=>$this->m_perrow,'lookup'=>'paging','id'=>'pager'),
				'pagenum'=>array('type'=>'hidden','value'=>1),
				'clicked'=>array('type'=>'hidden'),
				'submit'=>array('type'=>'submitButton','value'=>'Search','name'=>'search','onclick'=>'setClicked(this);'),
				'product_id'=>array('type'=>'select','required'=>false,'multiple'=>true,'sql'=>'select id, concat(name," - ",code) as name from product where deleted = 0 order by name'),
				'sortby'=>array('type'=>'hidden','value'=>'id'),
				'sortorder'=>array('type'=>'hidden','value'=>'desc'),
			),
			'salesReportsRow'=>array(
				'order_date'=>array('type'=>'datetimestamp')
			),
			'recurringOrders'=>array(
				'from'=>array('type'=>'datepicker','required'=>true),
				'to'=>array('type'=>'datepicker','required'=>true),
				'order_status'=>array('type'=>'select','required'=>false,'multiple'=>true,'lookup'=>'orderStatus'),
				'recurringOrders'=>array('type'=>'hidden','value'=>1),
				'pager'=>array('type'=>'select','required'=>true,'value'=>$this->m_perrow,'lookup'=>'paging','id'=>'pager'),
				'pagenum'=>array('type'=>'hidden','value'=>1),
				'submit'=>array('type'=>'submitButton','value'=>'Search'),
				'product_id'=>array('type'=>'select','required'=>false,'sql'=>'select id, concat(name," - ",code) as name from product where deleted = 0 order by name'),
				'sortby'=>array('type'=>'hidden','value'=>'b.billing_date'),
				'sortorder'=>array('type'=>'hidden','value'=>'asc'),
			),
			'recurringOrdersRow'=>array(
				'order_date'=>array('type'=>'datetimestamp')
			),
			'allocation'=>array(
				'allocation'=>array('type'=>'hidden','value'=>1,'database'=>false),
				'member_id'=>array('type'=>'select','required'=>false,'sql'=>"select id, concat(company,', ',lastname,', ',firstname,' ',email) from members where deleted = 0 and enabled = 1 and id = %%member_id%% order by company, lastname, firstname",'prettyName'=>'Customer'),
				'start_date'=>array('type'=>'datepicker','required'=>false),
				'end_date'=>array('type'=>'datepicker','required'=>false),
				'submit'=>array('type'=>'submitbutton','value'=>'Search'),
				'update'=>array('type'=>'submitbutton','value'=>'Update'),
				'total'=>array('type'=>'textfield','value'=>'0.00','disabled'=>'disabled','class'=>'a-right def_field_input'),
				'unallocated'=>array('type'=>'textfield','value'=>'0.00','disabled'=>'disabled','class'=>'a-right def_field_input')
			),
			'allocationUpdate'=>array(
				'checkNumber'=>array('type'=>'textfield','required'=>true),
				'checkAmount'=>array('type'=>'textfield','validation'=>'number','value'=>0.00,'class'=>'a-right def_field_input'),
				'updateOrders'=>array('type'=>'hidden','value'=>1)
			),
			'allocationRow'=>array(
				'order_date'=>array('type'=>'datetimestamp'),
				'total'=>array('type'=>'currency'),
				'balance'=>array('type'=>'textfield','class'=>'a-right','name'=>'balance[%%id%%]','onchange'=>'calcAllocated();return false;'),
				'pay'=>array('type'=>'checkbox','value'=>1,'name'=>'pay[%%id%%]','onchange'=>'calcAllocated();return false;'),
				'authorization_amount'=>array('type'=>'currency','onchange'=>'calcAllocated();return false;')
			),
			'QB'=>array(
				'QB'=>array('type'=>'hidden','value'=>1),
				'member_id'=>array('type'=>'select','class'=>'form-control','sql'=>"select id, concat(if(company='','',concat(company,': ')),lastname,' ',firstname) from members where deleted = 0 and enabled = 1 order by 2","multiple"=>"multiple"),
				'search'=>array('type'=>'submitbutton','class'=>'btn btn-primary','value'=>'Search'),
				'export'=>array('type'=>'button','class'=>'btn btn-default','value'=>'Export','onclick'=>'checkExport(this);return false;','class'=>'btn btn-primary'),
				'consolidated'=>array('type'=>'button','class'=>'btn btn-default','value'=>'Consolidated Invoices','onclick'=>'doConsolidated(this);return false;','class'=>'btn btn-default'),
				'qbExportCheck'=>array('type'=>'hidden','value'=>1),
				'consolidatedInvoices'=>array('type'=>'hidden','value'=>1),
				'qb_invoice_id'=>array('type'=>'number','min'=>'0','required'=>false,'class'=>'form-control a-right'),
				'order_id'=>array('type'=>'number','min'=>'0','required'=>false,'class'=>'form-control a-right'),
				'invoice_date'=>array('type'=>'datepicker','required'=>false),
				'cutoff_date'=>array('type'=>'datepicker','required'=>false),
				'pager'=>array('type'=>'select','required'=>true,'value'=>$this->m_perrow,'lookup'=>'paging','id'=>'pager'),
				'billing_type'=>array('type'=>'select','required'=>true,'options'=>array('B'=>'Bill Later','P'=>'Prepaid','C'=>'Non-Delivery'),'value'=>'B')
			),
			'QBConnect'=>array(
			),
			'QBRow'=>array(
				'created'=>array('type'=>'datestamp'),
				'invoice_date'=>array('type'=>'datestamp'),
				'invoice_amount'=>array('type'=>'currency'),
				'tax_amount'=>array('type'=>'currency'),
				'total'=>array('type'=>'currency')
			),
			'qbExportCheck'=>array(
				'qbExportCheck'=>array('type'=>'hidden','value'=>1),
				'member_id'=>array('type'=>'select',"multiple"=>"multiple"),
				'billing_type'=>array('type'=>'hidden'),
				'invoice_date'=>array('type'=>'datepicker','validation'=>'date'),
				'cutoff_date'=>array('type'=>'datepicker','validation'=>'date','required'=>true),
				'order_id'=>array('type'=>'hidden'),
				'qb_invoice_id'=>array('type'=>'hidden','required'=>true,'validation'=>'number','prettyName'=>'Next Invoice #'),
			),
			'qbExportCheckRow'=>array(
				'total'=>array('type'=>'currency'),
				'paid'=>array('type'=>'currency'),
				'net'=>array('type'=>'currency'),
				'process'=>array('type'=>'checkbox','name'=>'process[]','value'=>'%%id%%','checked'=>'checked')
			),
			'qbDetails'=>array(
				'member_id'=>array('type'=>'select','class'=>'form-control','sql'=>"select id, concat(if(company='','',concat(company,': ')),lastname,' ',firstname) from members where id = 0%%request:member_id%%"),
				'order_id'=>array('type'=>'number','min'=>'0','required'=>false,'class'=>'form-control a-right'),
				'invoice_date'=>array('type'=>'datepicker','required'=>false),
				'cutoff_date'=>array('type'=>'datepicker','required'=>false)
			),
			'qbDetailsRow'=>array(
				'actual_date'=>array('type'=>'datetimestamp'),
				'value'=>array('type'=>'currency'),
				'taxes'=>array('type'=>'currency'),
				'total'=>array('type'=>'currency')
			),
			'qbProcess'=>array(
				'cutoff_date'=>array('type'=>'datepicker','required'=>true,'class'=>'hidden'),
				'invoice_date'=>array('type'=>'datepicker','required'=>false,'class'=>'hidden'),
				'qb_invoice_id'=>array('type'=>'tag','required'=>true,'validation'=>'number','prettyName'=>'Next Invoice #')
			),
			'qbProcessRow'=>array(
				'amount'=>array('type'=>'currency'),
				'rounding_error'=>array('type'=>'currency')
			),
			'qbInvoiceDetails'=>array(
				'qbInvoiceDetails'=>array('type'=>'hidden','value'=>1),
				'pagenum'=>array('type'=>'hidden','value'=>0),
				'pager'=>array('type'=>'select','required'=>true,'value'=>$this->m_perrow,'lookup'=>'paging','id'=>'pager'),
				'i_id'=>array('type'=>'hidden','value'=>'%%request:i_id%%')
			),
			'qbInvoiceDetailsRow'=>array(
				'order_date'=>array('type'=>'datetimestamp'),
				'total'=>array('type'=>'currency')
			),
			'unpaidBillNow'=>array(
				'member_id'=>array('type'=>'select'),
				'order_id'=>array('type'=>'textfield'),
				'actual_date'=>array('type'=>'datepicker'),
				'opt_actual_date'=>array('type'=>'select','name'=>'opt_created','lookup'=>'search_options'),
				'search'=>array('type'=>'submitbutton','class'=>'btn btn-primary','value'=>'Search'),
				'billing'=>array('type'=>'submitbutton','class'=>'btn btn-primary','value'=>'Bill Now','onclick'=>'billNow(this);return false;'),
				'completed'=>array('type'=>'select','lookup'=>'boolean','required'=>false),
				'unpaidBillNow'=>array('type'=>'hidden','value'=>1)
			),
			'unpaidBillNowRow'=>array(
				'actual_date'=>array('type'=>'datetimestamp'),
				'total'=>array('type'=>'currency'),
				'authorization_amount'=>array('type'=>'currency'),
				'owing'=>array('type'=>'currency'),
				'process'=>array('type'=>'checkbox','value'=>'1','name'=>'process[%%id%%]'),
				'authorized_amount'=>array('type'=>'currency')
			),
			'authHistoryRow'=>array(
				'authorization_date'=>array('type'=>'datetimestamp'),
				'authorization_amount'=>array('type'=>'currency')
			),
			'processNow'=>array(
			),
			'processNowRow'=>array(
				'AMT'=>array('type'=>'currency')
			),
			'removeSchedule'=>array(
				's_id'=>array('type'=>'tag','value'=>0),
				'success'=>array('type'=>'tag','value'=>0)
			),
			'editInvoices'=>array(
				'editInvoices'=>array('type'=>'hidden','value'=>1),
				'qb_invoice_id'=>array('type'=>'number','required'=>false,'validation'=>'number','class'=>'form-control a-right','value'=>0),
				'member_id'=>array('type'=>'select','required'=>false,'sql'=>"select id, concat(company,', ',lastname,', ',firstname,' ',email) from members where deleted = 0 and enabled = 1 and id = %%member_id||inline:0%% order by company, lastname, firstname",'prettyName'=>'Customer'),
				'start_date'=>array('type'=>'datepicker','required'=>false),
				'end_date'=>array('type'=>'datepicker','required'=>false),
				'order_id'=>array('type'=>'number','required'=>false,'value'=>0,'class'=>'form-control a-right','validation'=>'number'),
				'pager'=>array('type'=>'select','required'=>true,'value'=>$this->m_perrow,'lookup'=>'paging','id'=>'pager'),
				'pagenum'=>array('type'=>'hidden','value'=>1),
				'submit'=>array('type'=>'submitButton','value'=>'Search'),
				'sortby'=>array('type'=>'hidden','value'=>'qb_invoice_id'),
				'pageTotal'=>array('type'=>'currency'),
				'sortorder'=>array('type'=>'hidden','value'=>'desc')
			),
			'editInvoicesRow'=>array(
				'total'=>array('type'=>'currency'),
				'invoice_date'=>array('type'=>'datestamp')
			),
			'getInvoice'=>array(
				'getInvoice'=>array('type'=>'hidden','value'=>1),
				'i_id'=>array('type'=>'hidden','value'=>'%%request:i_id%%'),
				'parent_id'=>array('type'=>'select','required'=>false,'sql'=>"select id, concat(company,', ',lastname,', ',firstname,' ',email) from members where deleted = 0 and enabled = 1 and id = %%member_id||inline:0%% order by company, lastname, firstname",'prettyName'=>'Customer'),
				'delete'=>array('type'=>'checkbox','value'=>1),
				'submit'=>array('type'=>'submitbutton','value'=>'Save Changes','database'=>false)
			),
			'getInvoiceRow'=>array(
				'member_id'=>array('type'=>'select','required'=>true,'sql'=>"select id, concat(company,', ',lastname,', ',firstname,' ',email) from members where deleted = 0 and enabled = 1 and id = %%p_id||inline:0%% order by company, lastname, firstname",'prettyName'=>'Customer','name'=>'member_id[%%id||inline:0%%]','value'=>'%%p_id%%','id'=>'d_%%id%%'),
				'remove'=>array('type'=>'checkbox','value'=>1,'name'=>'remove[%%id||inline:0%%]')
			),
			'consolidatedInvoices'=>array(
				'cutoff_date'=>array('type'=>'datepicker','required'=>true)
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
		if ($o_id > 0 && $data = $this->fetchSingle(sprintf('select * from %s where id = %d',$this->m_content,$o_id))) {
			$form->init($this->getTemplate('showFolderContent'),array('name'=>'showFolderContent'));
			$frmFields = $form->buildForm($this->getFields('showFolderContent'));
			if (array_key_exists('pagenum',$_REQUEST)) 
				$pageNum = $_REQUEST['pagenum'];
			else
				$pageNum = 1;	// no 0 based calcs
			if ($pageNum <= 0) $pageNum = 1;
			$perPage = $this->m_perrow;
			if (array_key_exists('pager',$_REQUEST)) $perPage = $_REQUEST['pager'];
			$count = $this->fetchScalar(sprintf('select count(n.id) from %s n where deleted = 0', $this->m_content));
			$pagination = $this->pagination($count, $perPage, $pageNum, 
				array('prev'=>$this->M_DIR.'forms/paginationPrev.html','next'=>$this->M_DIR.'forms/paginationNext.html',
				'pages'=>$this->M_DIR.'forms/paginationPage.html', 'wrapper'=>$this->M_DIR.'forms/paginationWrapper.html'),
				array('url'=>'/modit/ajax/showFolderContent/credit','destination'=>'middleContent'));
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
			$orders = $this->fetchAll($sql);
			$this->logMessage('showPageContent', sprintf('sql [%s], records [%d]',$sql, count($orders)), 2);
			$articles = array();
			$frm = new Forms();
			$frm->init($this->getTemplate('articleList'),array());
			$tmp = $frm->buildForm($this->getFields('articleList'));
			$status = $this->fetchAll(sprintf('select * from code_lookups where type="orderStatus" order by sort, code'));
			foreach($orders as $order) {
				$order['owing'] = $this->my_money_format($order['total']-$order['authorization_amount']);
				$order['value'] = $this->my_money_format($order['value']);
				$order['total'] = '['.$this->my_money_format($order['total']).']';
				$order['taxes'] = $this->my_money_format($order['taxes']);
				$order['discount_value'] = $this->my_money_format($order['discount_value']);
				$order['line_discounts'] = $this->my_money_format($order['line_discounts']);
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
		if (count($_POST) == 0)
			if (array_key_exists('formData',$_SESSION) && array_key_exists('creditSearchForm', $_SESSION['formData']))
				$_POST = $_SESSION['formData']['creditSearchForm'];
			else
				$_POST = array('order_status'=>STATUS_PROCESSING,'sortby'=>'created','sortorder'=>'asc','showSearchForm'=>1);
		if (count($_POST) > 0 && array_key_exists('showSearchForm',$_POST)) {
			$form->addData($_POST);
			if ($form->validate()) {
				$_SESSION['formData']['creditSearchForm'] = $form->getAllData();
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
								break 2;
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
									$srch[] = sprintf(' o.%s %s "%s"',$key, $_POST['opt_'.$key],$this->escape_string($value));
							}
							break;
						case 'order_status':
							if (($value = $form->getData($key)) > 0) {
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
					$count = $this->fetchScalar(sprintf('select count(o.id) from %s o,members m where m.id = o.member_id and %s', $this->m_content, implode(' and ',$srch)));
					$pageNum = max(1,min($pageNum, (floor(($count-1)/$perPage)+1)));
					$form->setData('pagenum', $pageNum);
					$pagination = $this->pagination($count, $perPage, $pageNum,
							array('prev'=>$this->M_DIR.'forms/paginationPrev.html','next'=>$this->M_DIR.'forms/paginationNext.html',
							'pages'=>$this->M_DIR.'forms/paginationPage.html', 'wrapper'=>$this->M_DIR.'forms/paginationWrapper.html'),
							array('url'=>'/modit/ajax/showSearchForm/credit','destination'=>'middleContent')
						);
					$start = ($pageNum-1)*$perPage;
					$sortorder = 'desc';
					$sortby = 'created';
					if (array_key_exists('sortby',$_POST)) {
						$sortby = $_POST['sortby'];
						if ($sortby == 'name') $sortby = 'm.lastname';
						$sortorder = $_POST['sortorder'];
					}
					$sql = sprintf('select o.*, m.firstname, m.lastname, concat(m.firstname," ",m.lastname) as name, sum(l.quantity) as quantity, sum(l.shipped) as shipped from %s o, members m, order_lines l where l.order_id = o.id and m.id = o.member_id and %s group by o.id order by %s %s limit %d,%d',
						 $this->m_content, implode(' and ',$srch),$sortby, $sortorder, $start,$perPage);
					$recs = $this->fetchAll($sql);
					$this->logMessage('showSearchForm', sprintf('sql [%s] records [%d]',$sql,count($recs)), 2);
					$articles = array();
					$frm = new Forms();
					$frm->init($this->getTemplate('articleList'),array());
					$tmp = $frm->buildForm($this->getFields('articleList'));
					$status = $this->fetchAll(sprintf('select * from code_lookups where type="orderStatus" order by sort, code'));
					foreach($recs as $article) {
						$article['owing'] = $this->my_money_format($article['total']-$article['authorization_amount']);
						$article['value'] = $this->my_money_format($article['value']);
						$article['total'] = $this->my_money_format($article['total']);
						$article['taxes'] = $this->my_money_format($article['taxes']);
						$article['discount_value'] = $this->my_money_format($article['discount_value']);
						$article['line_discounts'] = $this->my_money_format($article['line_discounts']);
						$tmp = array();
						foreach($status as $key=>$value) {
							if ($article['order_status'] & (int)$value['code'])
								$tmp[] = $value['value'];
						}
						$article['order_status'] = implode(', ',$tmp);
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
		if (!(array_key_exists('o_id',$_REQUEST) && $_REQUEST['o_id'] > 0 && $data = $this->fetchSingle(sprintf('select * from %s where id = %d', $this->m_content, $_REQUEST['o_id'])))) {
			$data = array('id'=>0,'published'=>false,'tax_exemptions'=>'||','value'=>0,'authorization_amount'=>0,'coupon_id'=>0,'discount_type'=>'','order_status'=>0); 
			$frmFields['coupon_id']['sql'] = 'select id, concat(code," - ",name) from coupons where deleted = 0 and enabled = 1 and published = 1 order by code';
		} else {
			$frmFields['coupon_id']['sql'] = sprintf('select id, concat(code," - ",name) from coupons where (deleted = 0 and enabled = 1 and published = 1) or id = %d order by code',$data['coupon_id']);
		}
		$lines = $this->fetchAll(sprintf('select o.*, p.code, p.name, c.name as coupon_name from order_lines o left join coupons c on c.id = o.coupon_id, product p where o.order_id = %d and p.id = o.product_id and o.deleted = 0 order by line_id',$data['id']));
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
		$status = $this->fetchAll(sprintf('select * from code_lookups where type="orderStatus" order by sort, code'));
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
							if ($cp = $this->fetchSingle(sprintf('select * from coupons where id = %d',$_POST['coupon_id']))) {
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
					$tmp = array();
					$tmp['header'] = $form->getAllData();
					$tmp['products'] = $this->fetchAll(sprintf('select * from order_lines where order_id = %d and deleted = 0',$data['id']));
					$tmp['taxes'] = $this->fetchAll(sprintf('select * from order_taxes ot where ot.order_id = %d and ot.line_id in (select o.line_id from order_lines o where o.order_id = ot.order_id and o.deleted = 0)',$data['id']));
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
						$addresses = $this->fetchAll(sprintf('select * from addresses where ownertype="member" and ownerid = %d and deleted = 0',$form->getData('member_id')));
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
						$data = $this->fetchSingle(sprintf('select * from %s where id = %d', $this->m_content, $id));
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
						$status = $this->fetchAll(sprintf('select * from code_lookups where type="orderStatus" order by sort, code'));
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
							//$html = $this->fetchSingle($sql);
							$body->setHTML($html);
							$order = $this->fetchSingle(sprintf('select o.*, m.firstname, m.lastname, m.email from orders o, members m where o.id = %d and m.id = o.member_id',$id));
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
     * @param int $fromMain
     * @return array|false|mixed|string|string[]|void
     * @throws phpmailerException
     */
    function moduleStatus($fromMain = 0) {
		if (array_key_exists('formData',$_SESSION) && array_key_exists('creditSearchForm', $_SESSION['formData'])) {
			$msg = '';
			$_POST = $_SESSION['formData']['creditSearchForm'];
		}
		else {
			$ct = $this->fetchScalar(sprintf('select count(0) from %s where order_status & %d = %d',$this->m_content,STATUS_CREDIT_HOLD,STATUS_CREDIT_HOLD));
			$ct = $this->fetchScalar(sprintf('select count(0) from %s where order_status & %d = %d',$this->m_content,STATUS_EXPIRING,STATUS_EXPIRING));
			if ($ct == 0) {
				$_POST = array('showSearchForm'=>1,'deleted'=>0,'sortby'=>'created','sortorder'=>'desc','pager'=>$this->m_perrow,'order_status'=>STATUS_PROCESSING);
				$msg = "Showing unshipped orders";
			}
			else {
				$_POST = array('showSearchForm'=>1,'order_status'=>STATUS_EXPIRING,'sortby'=>'created','sortorder'=>'asc','pager'=>$this->m_perrow);
				$msg = "Showing expiring credit cards/authorizations";
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
     * @param bool $fromMain
     * @return array|false|mixed|string|string[]|void
     * @throws phpmailerException
     */
    function dailyLog($fromMain = false) {
		$outer = new Forms();
		$outer->init($this->getTemplate('dailyLog'));
		$flds = $outer->buildForm($this->getFields('dailyLog'));
		if (count($_POST) > 0 && array_key_exists('dailyLog',$_POST)) {
			$outer->addData($_POST);
			$valid = $outer->validate();
		}
		else $valid = true;
		if (array_key_exists('pagenum',$_REQUEST)) 
			$pageNum = $_REQUEST['pagenum'];
		else
			$pageNum = 1;	// no 0 based calcs
		if ($pageNum <= 0) $pageNum = 1;
		$perPage = $this->m_perrow;
		$where = array('completed != "0000-00-00 00:00:00"');
		if ($valid && strlen($outer->getData('from')) > 0) {
			$where[] = sprintf('start_date >= "%s"',$outer->getData('from'));
		}
		if ($valid && strlen($outer->getData('to')) > 0) {
			$where[] = sprintf('end_date <= "%s"',$outer->getData('to'));
		}
		if (array_key_exists('pager',$_REQUEST)) $perPage = $_REQUEST['pager'];
		$count = $this->fetchScalar(sprintf('select count(0) from order_processing where %s',implode(' and ',$where)));
		$pageNum = max(1,min($pageNum, (floor(($count-1)/$perPage)+1)));
		$outer->setData('pagenum', $pageNum);
		$pagination = $this->pagination($count, $perPage, $pageNum,
				array('prev'=>$this->M_DIR.'forms/paginationPrev.html','next'=>$this->M_DIR.'forms/paginationNext.html',
				'pages'=>$this->M_DIR.'forms/paginationPage.html', 'wrapper'=>$this->M_DIR.'forms/paginationWrapper.html'),
				array('url'=>'/modit/ajax/showSearchForm/credit','destination'=>'middleContent')
		);
		$start = ($pageNum-1)*$perPage;
		$sql = sprintf('select * from order_processing where %s order by created desc limit %d,%d',implode(' and ',$where),$start,$perPage);
		$recs = $this->fetchAll($sql);
		$result = array();
		$inner = new Forms();
		$inner->init($this->getTemplate('dailyLogRow'));
		$flds = $inner->buildForm($this->getFields('dailyLogRow'));
		foreach($recs as $key=>$rec) {
			$inner->addData($rec);
			$result[] = $inner->show();
		}
		$outer->addTag('rows',implode("",$result),false);
		if ($this->isAjax())
			return $this->ajaxReturn(array('status'=>true,'html'=>$outer->show()));
		else
			return $this->show($outer->show());
	}

    /**
     * @return array|false|mixed|string|string[]|void
     * @throws phpmailerException
     */
    function dailyDetails() {
		$r_id = array_key_exists('r_id',$_REQUEST) ? $_REQUEST['r_id'] : 0;
		$outer = new Forms();
		$outer->init($this->getTemplate('dailyDetails'));
		$outer->addData($_REQUEST);
		$outer->validate();
		$flds = $outer->buildForm($this->getFields('dailyDetails'));
		$pageNum = max(1,$outer->getData("pagenum"));
		$perPage = max($this->m_perrow,$outer->getData("pager"));
/*
		if (array_key_exists('pagenum',$_REQUEST)) 
			$pageNum = $_REQUEST['pagenum'];
		else
			$pageNum = 1;	// no 0 based calcs
		if ($pageNum <= 0) $pageNum = 1;
		$perPage = $this->m_perrow;
		if (array_key_exists('pager',$_REQUEST)) $perPage = $_REQUEST['pager'];
*/
		$count = $this->fetchScalar(sprintf('select count(0) from order_processing_details where processing_id = %d',$r_id));
		$pageNum = max(1,min($pageNum, (floor(($count-1)/$perPage)+1)));
		$outer->setData('pagenum', $pageNum);
		$pagination = $this->pagination($count, $perPage, $pageNum,
				array('prev'=>$this->M_DIR.'forms/altPaginationPrev.html','next'=>$this->M_DIR.'forms/altPaginationNext.html',
				'pages'=>$this->M_DIR.'forms/altPaginationPage.html', 'wrapper'=>$this->M_DIR.'forms/altPaginationWrapper.html'),
				array('url'=>'/modit/ajax/showSearchForm/credit','destination'=>'middleContent')
		);
		$start = ($pageNum-1)*$perPage;
		$sql = sprintf('select * from order_processing_details where processing_id = %d order by processing_status desc, id limit %d,%d',$r_id,$start,$perPage);
		$recs = $this->fetchAll($sql);
		$result = array();
		$inner = new Forms();
		$inner->init($this->getTemplate('dailyDetailsRow'));
		$flds = $inner->buildForm($this->getFields('dailyDetailsRow'));
		foreach($recs as $key=>$rec) {
			switch($rec['processing_status']) {
				case 0:
					$rec['processing_status'] = 'Success';
					break;
				case 1:
					$rec['processing_status'] = 'Warning';
					break;
				case 2:
					$rec['processing_status'] = 'Error';
					break;
				default:
			}
			$inner->addData($rec);
			$result[] = $inner->show();
		}
		$this->logMessage(__FUNCTION__,sprintf('rows [%s] from [%d] records sql [%s]',print_r($result,true),count($recs),$sql),1);
		$outer->addTag('rows',implode("",$result),false);
		$outer->addTag('pagination',$pagination,false);
		$outer->addData(array('r_id'=>$r_id,'pagenum'=>$pageNum));
		if ($this->isAjax())
			return $this->ajaxReturn(array('status'=>true,'html'=>$outer->show()));
		else
			return $this->show($outer->show());
	}

    /**
     * @param bool $fromMain
     * @return array|false|mixed|string|string[]
     * @throws phpmailerException
     */
    function showPageProperties($fromMain = false) {
		$o_id = array_key_exists('o_id',$_REQUEST) ? $_REQUEST['o_id'] : 0;
		$data = $this->fetchSingle(sprintf('select * from orders where id = %d', $o_id));
		$outer = new Forms();
		$outer->init($this->getTemplate('showPageProperties'));
		$flds = $outer->buildForm($this->getFields('showPageProperties'));
		$tmp = array();
		$status = $this->fetchAll(sprintf('select * from code_lookups where type="orderStatus" order by sort, code'));
		foreach($status as $key=>$value) {
			if ($data['order_status'] & (int)$value['code'])
				$tmp[] = $value['code'];
		}
		$data['order_status'] = $tmp;
		$outer->addData($data);

		$outer->setData("billed",$this->getBilled($o_id));
		$outer->addTag('toBeBilled',$this->getRemaining($o_id),false);
		if ($fromMain)
			return $outer->show();
		else
			return $this->ajaxReturn(array('status'=>true,'html'=>$outer->show()));
	}

    /**
     * @param $o_id
     * @return array|false|mixed|string|string[]
     * @throws phpmailerException
     */
    function getBilled($o_id = null) {
		$fromMain = !is_null($o_id);
		if (is_null($o_id)) {
			$o_id = array_key_exists("o_id",$_REQUEST) ? $_REQUEST["o_id"] : 0;
		}
		$outer = new Forms();
		$outer->init($this->getTemplate(__FUNCTION__));
		$flds = $outer->buildForm($this->getFields(__FUNCTION__));
		$inner = new Forms();
		$inner->init($this->getTemplate(__FUNCTION__."Row"));
		$flds = $inner->buildForm($this->getFields(__FUNCTION__."Row"));
		if (array_key_exists('pagenum',$_REQUEST)) 
			$pageNum = $_REQUEST['pagenum'];
		else
			$pageNum = 1;	// no 0 based calcs
		if ($pageNum <= 0) $pageNum = 1;
		$perPage = $this->m_perrow;
		$start = ($pageNum-1)*$perPage;
		$count = $this->fetchScalar(sprintf('select count(b.id) from order_billing b where b.billed = 1 and b.original_id = %d order by order_id',$o_id));
		$pagination = $this->pagination($count, $perPage, $pageNum, 
			array(
				'prev'=>$this->M_DIR.'forms/paginationPrev.html',
				'next'=>$this->M_DIR.'forms/paginationNext.html',
				'pages'=>$this->M_DIR.'forms/paginationPage.html',
				'wrapper'=>$this->M_DIR.'forms/paginationWrapper.html',
				'url'=>'/modit/ajax/getBilled/credit'), 
			array('destination'=>'tabs-2'));
		$outer->setData("pagination",$pagination);
		$other = $this->fetchAll(sprintf('select b.* from order_billing b where b.billed = 1 and b.original_id = %d order by order_id desc limit %d, %d',$o_id, $start, $this->m_perrow));
		$recurring = array();
		foreach($other as $key=>$order) {
			$inner->reset();
			$inner->addData($order);
			$recurring[] = $inner->show();
		}
		$outer->setData("o_id", $o_id);
		$outer->setData("recurring",implode('',$recurring),false);
		if ($fromMain)
			return $outer->show();
		else
			return $this->ajaxReturn(array("status"=>true,"html"=>$outer->show()));
	}

    /**
     * @param $o_id
     * @return array|false|mixed|string|string[]
     * @throws phpmailerException
     */
    function getRemaining($o_id = null) {
		$fromMain = true;
		if (is_null($o_id)) {
			$o_id = array_key_exists('o_id',$_REQUEST) ? $_REQUEST['o_id'] : 0;
			$fromMain = false;
		}
		$outer = new Forms();
		$outer->init($this->getTemplate('getRemaining'));
		$flds = $outer->buildForm($this->getFields('getRemaining'));
		$outer->addData($_REQUEST);
		$inner = new Forms();
		$inner->init($this->getTemplate('getRemainingRow'));
		$flds = $inner->buildForm($this->getFields('getRemainingRow'));
		$recurring = array();
		if (array_key_exists('pagenum',$_REQUEST)) 
			$pageNum = $_REQUEST['pagenum'];
		else
			$pageNum = 1;	// no 0 based calcs
		if ($pageNum <= 0) $pageNum = 1;
		$perPage = $this->m_perrow;
		if (array_key_exists('pager',$_REQUEST)) $perPage = $_REQUEST['pager'];
		$count = $this->fetchScalar(sprintf('select count(id) from order_billing where billed = 0 and original_id = %d', $o_id));
		$pagination = $this->pagination($count, $perPage, $pageNum, 
			array('prev'=>$this->M_DIR.'forms/altPaginationPrev.html','next'=>$this->M_DIR.'forms/altPaginationNext.html',
			'pages'=>$this->M_DIR.'forms/altPaginationPage.html', 'wrapper'=>$this->M_DIR.'forms/altPaginationWrapper.html'),
			array('url'=>'/modit/ajax/showFolderContent/credit','destination'=>'middleContent'));
		$start = ($pageNum-1)*$perPage;
		$other = $this->fetchAll(sprintf('select * from order_billing where original_id = %d and billed = 0 order by billing_date limit %d,%d',$o_id,$start,$perPage));
		foreach($other as $key=>$order) {
			$inner->reset();
			$inner->addData($order);
			$recurring[] = $inner->show();
		}
		$outer->addTag('rows',implode('',$recurring),false);
		$outer->addTag('pagination',$pagination,false);
		if (!$fromMain)
			return $this->ajaxReturn(array('status'=>true,'html'=>$outer->show()));
		else
			return $outer->show();
	}

    /**
     * @return false|string
     * @throws Exception
     */
    function editRecurring() {
		$r_id = array_key_exists('r_id',$_REQUEST) ? $_REQUEST['r_id'] : 0;
		$data = $this->fetchSingle(sprintf('select * from order_billing where id = %d', $r_id));
		$outer = new Forms();
		$outer->init($this->getTemplate('editRecurring'));
		$flds = $outer->buildForm($this->getFields('editRecurring'));
		$outer->addData(array('r_id'=>$r_id));
		$outer->addData($data);
		if (count($_POST) > 0 && array_key_exists('editRecurring',$_POST)) {
			$outer->addData($_POST);
			$valid = $outer->validate();
			if ($valid) {
				$new = $outer->getData('billing_date');
				if ($data['billing_date'] != $new) {
					if ($new <= date('Y-m-d') && DEV==0) {
						$valid = false;
						$this->addError('New Billing Date must be in the future');
					}
					if ($valid) {
						$curr = new DateTime($new);
						$diff = $curr->diff(new DateTime($data['billing_date']));
						$days = $diff->days;	//format('%d');
						$this->logMessage(__FUNCTION__,sprintf('curr [%s] diff [%s] days [%s]',print_r($curr,true),print_r($diff,true),$days),1);
						$this->beginTransaction();
						$valid = $valid && $this->execute(sprintf('update order_billing set billing_date = "%s" where id = %d',
							date('Y-m-d',strtotime($new)), $r_id));
						$ct = 1;
						if (array_key_exists('adjustment', $_POST) && $_POST['adjustment'] == 1) {
							$next = $this->fetchAll(sprintf('select * from order_billing where original_id = %d and period_number > %d and billed = 0', $data['original_id'], $data['period_number']));
							foreach($next as $key=>$rec) {
								$ct += 1;
								$valid = $valid && $this->execute(sprintf('update order_billing set billing_date = "%s" where id = %d',
									date('Y-m-d',strtotime(sprintf('%s %s %s days', $rec['billing_date'], $diff->invert > 0 ? '+' : '-', $days))), $rec['id']));
							}
							if ($valid) $this->addMessage(sprintf('Updated %d records',$ct));
						}
						if (!$valid) {
							$this->addError('An Error Occurred. No changes were made');
						}
						else {
							$this->commitTransaction();
							$this->addMessage('Update Successful');
						}
					}
				}
			}
			if (!$valid) {
				$this->addError('Form validation failed');
			}
		}
		return $this->ajaxReturn(array('status'=>true,'html'=>$outer->show()));
	}

    /**
     * @param bool $fromMain
     * @return array|false|mixed|string|string[]|void
     * @throws phpmailerException
     */
    function nightlySchedule($fromMain = false) {
		$outer = new Forms();
		$outer->init($this->getTemplate(__FUNCTION__));
		$flds = $outer->buildForm($this->getFields(__FUNCTION__));
		if (count($_POST) > 0 && array_key_exists(__FUNCTION__,$_POST)) {
			$outer->addData($_POST);
			$valid = $outer->validate();
			$where = array(' 1=1 ');
		}
		else {
			$valid = true;
			$where = array(' completed = "0000-00-00 00:00:00"');
		}
		if ($valid && strlen($outer->getData('from')) > 0) {
			$where[] = sprintf('start_date >= "%s"',$outer->getData('from'));
		}
		if ($valid && strlen($outer->getData('to')) > 0) {
			$where[] = sprintf('end_date <= "%s"',$outer->getData('to'));
		}
		if (array_key_exists('pager',$_REQUEST)) $perPage = $_REQUEST['pager'];
		$count = $this->fetchScalar(sprintf('select count(0) from order_processing where %s',implode(' and ',$where)));
		if (array_key_exists('pagenum',$_REQUEST)) 
			$pageNum = $_REQUEST['pagenum'];
		else
			$pageNum = 1;	// no 0 based calcs
		if ($pageNum <= 0) $pageNum = 1;
		$perPage = array_key_exists('pager',$_REQUEST) ? $_REQUEST['pager'] : $this->m_perrow;
		$pageNum = max(1,min($pageNum, (floor(($count-1)/$perPage)+1)));
		$outer->setData('pagenum', $pageNum);
		$pagination = $this->pagination($count, $perPage, $pageNum,
				array('prev'=>$this->M_DIR.'forms/paginationPrev.html','next'=>$this->M_DIR.'forms/paginationNext.html',
				'pages'=>$this->M_DIR.'forms/paginationPage.html', 'wrapper'=>$this->M_DIR.'forms/paginationWrapper.html'),
				array('url'=>'/modit/ajax/showSearchForm/credit','destination'=>'middleContent')
		);
		$start = ($pageNum-1)*$perPage;
		$sql = sprintf('select * from order_processing where %s order by bill_date asc limit %d,%d',implode(' and ',$where),$start,$perPage);
		$recs = $this->fetchAll($sql);
		$result = array();
		$inner = new Forms();
		$inner->init($this->getTemplate('nightlyScheduleRow'));
		$flds = $inner->buildForm($this->getFields('nightlyScheduleRow'));
		foreach($recs as $key=>$rec) {
			$inner->reset();
			$inner->addData($rec);
			$result[] = $inner->show();
		}
		$outer->addTag('rows',implode('',$result),false);
		$outer->addTag('pagination',$pagination,false);
		if ($this->isAjax())
			return $this->ajaxReturn(array('status'=>true,'html'=>$outer->show()));
		else
			return $this->show($outer->show());	
	}

    /**
     * @return false|string
     * @throws phpmailerException
     */
    function addSchedule() {
		$s_id = array_key_exists('s_id',$_REQUEST) ? $_REQUEST['s_id'] : 0;
		if (!$data = $this->fetchSingle(sprintf('select * from order_processing where id = %d', $s_id)))
			$data = array('id'=>0);
		$outer = new Forms();
		$outer->init($this->getTemplate(__FUNCTION__));
		$outer->addData($data);
		$flds = $outer->buildForm($this->getFields(__FUNCTION__));
		$this->logMessage(__FUNCTION__,sprintf('outer [%s]',print_r($outer,true)),1);
		if (count($_POST) > 0 && array_key_exists(__FUNCTION__,$_POST)) {
			$outer->addData($_POST);
			$valid = $outer->validate();
			if ($valid) {
				if ($outer->getData('start_date') > $outer->getData('bill_date')) {
					$valid = false;
					$this->addError('Start Date cannot be after Billing Date');
				}
				if ($outer->getData('end_date') < $outer->getData('bill_date')) {
					$valid = false;
					$this->addError('End Date cannot be before Billing Date');
				}
				if ($outer->getData('start_date') > $outer->getData('end_date')) {
					$valid = false;
					$this->addError('Start Date cannot be after Ending Date');
				}
				if ($ct = $this->fetchScalar(sprintf('select count(0) from order_processing where bill_date >= "%s" and bill_date <= "%s"', $outer->getData('start_date'), $outer->getData('end_date')))) {
					$this->addError('This date has already been scheduled');
					$valid = false;
				}
			}
			if ($valid) {
				$tmp = array(
					'bill_date'=>$outer->getData('bill_date'),
					'start_date'=>$outer->getData('start_date'),
					'end_date'=>$outer->getData('end_date')
				);
				if ($s_id == 0) {
					$stmt = $this->prepare(sprintf('insert into order_processing(%s) values(%s?)', implode(', ',array_keys($tmp)), str_repeat('?, ',count($tmp)-1)));
				}
				else {
					$stmt = $this->prepare(sprintf('update order_processing set %s=? where id = %d', implode('=?, ',array_keys($tmp)), $s_id));
				}
				$stmt->bindParams(array_merge(array(str_repeat('s',count($tmp))),array_values($tmp)));
				$valid = $valid && $stmt->execute();
				if ($valid) {
					$this->addMessage('The date was scheduled');
					$last = $this->fetchScalar(sprintf('select max(end_date) from order_processing where started = "0000-00-00 00:00:00"'));
					if ($last == "") 
						$last = date("Y-m-d");
					else
						$last = date("Y-m-d",strtotime(sprintf("%s + 1 day",$last)));
					$data = array('bill_date'=>$last, 'start_date'=>$last,'end_date'=>$last);
					$outer->addData($data);
				}
				else $this->addError('An error occurred');
			}
		} else {
			if ($data['id'] == 0) {
				$last = $this->fetchScalar(sprintf('select max(end_date) from order_processing where started = "0000-00-00 00:00:00"'));
				if ($last == "") 
					$last = date("Y-m-d");
				else
					$last = date("Y-m-d",strtotime(sprintf("%s + 1 day",$last)));
				$data = array('bill_date'=>$last, 'start_date'=>$last,'end_date'=>$last);
				$outer->addData($data);
			}
		}
		return $this->ajaxReturn(array('status'=>true,'html'=>$outer->show()));
	}

    /**
     * @return array|false|mixed|string|string[]|void
     * @throws phpmailerException
     */
    function exchange() {
		$outer = new Forms();
		$outer->init($this->getTemplate(__FUNCTION__));
		$flds = $outer->buildForm($this->getFields(__FUNCTION__));

		if (count($_POST) > 0 && array_key_exists(__FUNCTION__,$_POST)) {
			$outer->addData($_POST);
			$valid = $outer->validate();
		}
		else $valid = true;
		$where = array('l.id = e.currency_id');
		if (array_key_exists('currency_id',$_REQUEST) && $_REQUEST['currency_id'] > 0) {
			$where[] = sprintf('currency_id = %d',$_REQUEST['currency_id']);
		}
		if (array_key_exists('from',$_REQUEST) && strlen($outer->getData('from')) > 0) {
			$where[] = sprintf('effective_date >= "%s"',$outer->getData('from'));
		}
		if (array_key_exists('to',$_REQUEST) && strlen($outer->getData('to')) > 0) {
			$where[] = sprintf('effective_date <= "%s"',$outer->getData('to'));
		}
		if (array_key_exists('pager',$_REQUEST)) $perPage = $_REQUEST['pager'];
		$count = $this->fetchScalar(sprintf('select count(0) from exchange_rate e, code_lookups l where %s',implode(' and ',$where)));
		if (array_key_exists('pagenum',$_REQUEST)) 
			$pageNum = $_REQUEST['pagenum'];
		else
			$pageNum = 1;	// no 0 based calcs
		if ($pageNum <= 0) $pageNum = 1;
		$perPage = array_key_exists('pager',$_REQUEST) ? $_REQUEST['pager'] : $this->m_perrow;
		$pageNum = max(1,min($pageNum, (floor(($count-1)/$perPage)+1)));
		$outer->setData('pagenum', $pageNum);
		$pagination = $this->pagination($count, $perPage, $pageNum,
				array('prev'=>$this->M_DIR.'forms/paginationPrev.html','next'=>$this->M_DIR.'forms/paginationNext.html',
				'pages'=>$this->M_DIR.'forms/paginationPage.html', 'wrapper'=>$this->M_DIR.'forms/paginationWrapper.html'),
				array('url'=>'/modit/ajax/showSearchForm/credit','destination'=>'middleContent')
		);
		$start = ($pageNum-1)*$perPage;
		$sql = sprintf('select e.*, l.value as currency, l.extra from exchange_rate e, code_lookups l where %s order by effective_date desc limit %d,%d',implode(' and ',$where),$start,$perPage);
		$recs = $this->fetchAll($sql);
		$result = array();
		
		$inner = new Forms();
		$inner->init($this->getTemplate(__FUNCTION__.'Row'));
		$flds = $inner->buildForm($this->getFields(__FUNCTION__.'Row'));

		foreach($recs as $key=>$value) {
			//setlocale(LC_MONETARY,$value['extra']);
			$value['exchange_rate'] = number_format($value['exchange_rate'],4);
			$inner->addData($value);
			$result[] = $inner->show();
		}
		//setlocale(LC_MONETARY,CURRENCY);
		$outer->addTag('rows',implode('',$result),false);
		$outer->addTag('pagination',$pagination,false);

		if ($this->isAjax()) {
			return $this->ajaxReturn(array('status'=>true,'html'=>$outer->show()));
		}
		else
			return $this->show($outer->show());
	}

    /**
     * @return false|string
     * @throws phpmailerException
     */
    function addCurrency() {
		$e_id = array_key_exists('e_id',$_REQUEST) ? $_REQUEST['e_id'] : 0;
		if (!$data = $this->fetchSingle(sprintf('select * from exchange_rate where id = %d', $e_id)))
			$data = array('id'=>0);
		$outer = new Forms();
		$outer->init($this->getTemplate(__FUNCTION__));
		$outer->addData($data);
		$flds = $outer->buildForm($this->getFields(__FUNCTION__));
		if (count($_POST) > 0 && array_key_exists(__FUNCTION__,$_POST)) {
			$outer->addData($_POST);
			$valid = $outer->validate();
			if ($valid) {
				$tmp = array();
				foreach($flds as $key=>$fld) {
					if (!array_key_exists('database',$fld) || $fld['database'] != false) {
						$tmp[$key] = $outer->getData($key);
					}
				}
				if ($e_id == 0) {
					$stmt = $this->prepare(sprintf('insert into exchange_rate(%s) values(%s?)', implode(', ',array_keys($tmp)), str_repeat('?, ',count($tmp)-1)));
				}
				else {
					$stmt = $this->prepare(sprintf('update exchange_rate set %s=? where id = %d', implode('=?, ',array_keys($tmp)), $e_id));
				}
				$stmt->bindParams(array_merge(array(str_repeat('s',count($tmp))),array_values($tmp)));
				$valid = $valid && $stmt->execute();
				if ($valid) {
					$this->addMessage('The exchange rate was added');
				}
				else $this->addError('An error occurred');
			}
		}
		return $this->ajaxReturn(array('status'=>true,'html'=>$outer->show()));
	}

    /**
     * @return array|false|mixed|string|string[]|void
     * @throws phpmailerException
     */
    function salesReports() {
		$outer = new Forms();
		$outer->init($this->getTemplate(__FUNCTION__));
		$flds = $outer->buildForm($this->getFields(__FUNCTION__));
		$totalTotal = 0;
		$totalGoods = 0;
		$totalShipping = 0;
		$recs = array();
		if (count($_POST) > 0 && array_key_exists(__FUNCTION__,$_POST)) {
			$outer->addData($_POST);
			$valid = $outer->validate();
			if ($valid) {
				$where = array();
				if (array_key_exists('from',$_REQUEST) && strlen($outer->getData('from')) > 0) {
					$where[] = sprintf('o.order_date >= "%s 00:00:00"',$outer->getData('from'));
				}
				if (array_key_exists('to',$_REQUEST) && strlen($outer->getData('to')) > 0) {
					$where[] = sprintf('o.order_date <= "%s 23:59:59"',$outer->getData('to'));
				}
				if (array_key_exists('order_status',$_REQUEST) && count($_REQUEST['order_status']) > 0) {
					$status = 0;
					foreach($_REQUEST['order_status'] as $key=>$value) {
						$status |= $value;
					}
					$where[] = sprintf('o.order_status & %d = %d',$status,$status);
					if (($status & STATUS_RECURRING) == 0)
						$where[] = sprintf('o.order_status & %d = 0',STATUS_RECURRING);
				}
				if (array_key_exists('product_id',$_REQUEST) && count($_REQUEST['product_id']) > 0 && implode(",",$_REQUEST["product_id"]) != "") {
					$where[] = sprintf('o.id in (select order_id from order_lines where product_id in (%s))',implode(",",$_REQUEST['product_id']));
				}
				if (array_key_exists('clicked',$_POST) && strpos($_POST['clicked'],"Export") !== false) {
					switch($_POST['clicked']) {
					case "Export Orders":
						$this->exportSales($where);
						break;
					case "Export Details":
						$this->exportDetails($where);
						break;
					default:
						break;
					}
					exit();
				}
				if (array_key_exists('pager',$_REQUEST)) $perPage = $_REQUEST['pager'];
				$count = $this->fetchScalar(sprintf('select count(0) from orders o where %s',implode(' and ',$where)));
				$outer->addTag('count',$count);
				if (array_key_exists('product_id',$_REQUEST) && count($_REQUEST['product_id']) > 0 && implode(",",$_REQUEST["product_id"]) != "") {
					$t = $this->fetchSingle(sprintf("select sum(ol.value) as sumValue, sum(o.total) as totalValue, sum(o.shipping) as sumShipping from orders o, order_lines ol where %s and ol.product_id in (%s) and ol.deleted = 0 and ol.order_id = o.id group by ol.product_id, ol.options_id",implode(' and ',$where),implode(",",$_REQUEST['product_id'])));
					$totalShipping = $t['sumShipping'];
					$totalGoods = $t['sumValue'];
					$totalTotal = $t['totalValue'];
				}
				else {
					if ($t = $this->fetchSingle(sprintf("select sum(value) as sumValue, sum(shipping) as sumShipping, sum(total) as sumTotal from orders o where %s",implode(' and ',$where)))) {
						$totalShipping = $t['sumShipping'];
						$totalGoods = $t['sumValue'];
						$totalTotal = $t['sumTotal'];
					}
				}
				if (array_key_exists('pagenum',$_REQUEST)) 
					$pageNum = $_REQUEST['pagenum'];
				else
					$pageNum = 1;	// no 0 based calcs
				if ($pageNum <= 0) $pageNum = 1;
				$perPage = array_key_exists('pager',$_REQUEST) ? $_REQUEST['pager'] : $this->m_perrow;
				$pageNum = max(1,min($pageNum, (floor(($count-1)/$perPage)+1)));
				$outer->setData('pagenum', $pageNum);
				$pagination = $this->pagination($count, $perPage, $pageNum,
						array('prev'=>$this->M_DIR.'forms/paginationPrev.html','next'=>$this->M_DIR.'forms/paginationNext.html',
						'pages'=>$this->M_DIR.'forms/paginationPage.html', 'wrapper'=>$this->M_DIR.'forms/paginationWrapper.html'),
						array('url'=>'/modit/ajax/showSearchForm/credit','destination'=>'middleContent')
				);
				$start = ($pageNum-1)*$perPage;
				if (array_key_exists('product_id',$_REQUEST) && count($_REQUEST['product_id']) > 0 && implode(",",$_REQUEST["product_id"]) != "") {
					$sql = sprintf('select o.*, m.firstname, m.lastname, m.company, ol.value from orders o left join members m on o.member_id = m.id, order_lines ol where %s and ol.product_id in (%s) and ol.deleted = 0 and ol.order_id = o.id order by %s %s limit %d,%d',implode(' and ',$where),implode(",",$_REQUEST['product_id']),$_REQUEST['sortby'], $_REQUEST['sortorder'], $start,$perPage);
				}
				else {
					$sql = sprintf('select o.*, m.firstname, m.lastname, m.company from orders o left join members m on o.member_id = m.id where %s order by %s %s limit %d,%d',implode(' and ',$where),$_REQUEST['sortby'], $_REQUEST['sortorder'], $start,$perPage);
				}
				$recs = $this->fetchAll($sql);
				$outer->addTag('pagination',$pagination,false);
			}
		}
		$result = array();
		$inner = new Forms();
		$inner->init($this->getTemplate(__FUNCTION__.'Row'));
		$flds = $inner->buildForm($this->getFields(__FUNCTION__.'Row'));
		$goods = 0;
		$shipping = 0;
		$total = 0;
		foreach($recs as $key=>$value) {
			$order = $this->formatOrder($value);
			$inner->addData($order);
			$result[] = $inner->show();
			$goods += $value["value"];
			$shipping += $value["shipping"];
			$total += $value["total"];
		}
		$outer->addTag('rows',implode('',$result),false);
		$outer->addTag("pageGoods",$this->my_money_format($goods));
		$outer->addTag("pageShipping",$this->my_money_format($shipping));
		$outer->addTag("pageTotal",$this->my_money_format($total));
		$outer->addTag("totalGoods",$this->my_money_format($totalGoods));
		$outer->addTag("totalShipping",$this->my_money_format($totalShipping));
		$outer->addTag("totalTotal",$this->my_money_format($totalTotal));
		if ($this->isAjax()) {
			return $this->ajaxReturn(array('status'=>true,'html'=>$outer->show()));
		}
		else
			return $this->show($outer->show());
	}

    /**
     * @param $where
     * @return void
     * @throws phpmailerException
     */
    function exportSales($where) {
		if (array_key_exists('product_id',$_REQUEST) && count($_REQUEST['product_id']) > 0) {
			$sql = sprintf('select o.* from orders o, order_lines ol where %s and ol.product_id in (%s) and ol.deleted = 0 and ol.order_id = o.id',implode(' and ',$where),implode(",",$_REQUEST['product_id']));
		}
		else {
			$sql = sprintf('select o.* from orders o where %s',implode(' and ',$where));
		}
		$orders = $this->fetchAll($sql);
		$outer = new Forms();
		$outer->init($this->getTemplate(__FUNCTION__));
		$inner = new Forms();
		$inner->init($this->getTemplate(__FUNCTION__.'Row'));
		$rows = array();
		foreach($orders as $key=>$order) {
			$inner->reset();
			$order = $this->formatOrder($order);
			if ($rec = $this->fetchSingle(sprintf("select * from addresses where ownertype='order' and ownerid = %d and tax_address=1",$order['id'])))
				$order['shippingAddress'] = Address::formatData($rec);
			if ($rec = $this->fetchSingle(sprintf("select * from addresses where ownertype='order' and ownerid = %d and tax_address=0",$order['id'])))
				$order['billingAddress'] = Address::formatData($rec);
			$taxes = $this->fetchAll(sprintf("select ot.*, t.* from order_taxes ot, taxes t where t.id = ot.tax_id and ot.order_id = %d and line_id = 0",$order["id"]));
			$order["line_taxes"] = $taxes;
			$order['member'] = $this->fetchSingle(sprintf("select * from members where id = %d",$order['member_id']));
			$inner->addData($order);
			$rows[] = $inner->show();
		}
		$outer->addTag('rows',implode('',$rows),false);
		$tmp = $outer->show();
		$fromdate = date("Y-m-d", strtotime($_REQUEST['from']));
		$todate = date("Y-m-d", strtotime($_REQUEST['to']));
		header('Content-Type: application/csv');
		header(sprintf('Content-Disposition: attachment; filename=sales-%s-%s.csv',$fromdate,$todate));
		header("Content-Length: ".strlen($tmp));
		header('Pragma: no-cache');
		echo $tmp;
		exit();
	}

    /**
     * @param $where
     * @return void
     * @throws phpmailerException
     */
    function exportDetails($where) {
		if (array_key_exists('product_id',$_REQUEST) && count($_REQUEST['product_id']) > 0) {
			$sql = sprintf('select o.* from orders o, order_lines ol where %s and ol.product_id in (%s) and ol.deleted = 0 and ol.order_id = o.id',implode(' and ',$where),implode(",",$_REQUEST['product_id']));
		}
		else {
			$sql = sprintf('select o.* from orders o where %s',implode(' and ',$where));
		}
		$orders = $this->fetchAll($sql);
		$outer = new Forms();
		$outer->init($this->getTemplate(__FUNCTION__));
		$inner = new Forms();
		$inner->init($this->getTemplate(__FUNCTION__.'Row'));
		$rows = array();
		foreach($orders as $key=>$order) {
			$inner->reset();
			$order = $this->formatOrder($order);
			if ($rec = $this->fetchSingle(sprintf("select * from addresses where ownertype='order' and ownerid = %d and tax_address=1",$order['id'])))
				$order['shippingAddress'] = Address::formatData($rec);
			if ($rec = $this->fetchSingle(sprintf("select * from addresses where ownertype='order' and ownerid = %d and tax_address=0",$order['id'])))
				$order['billingAddress'] = Address::formatData($rec);
			$order['member'] = $this->fetchSingle(sprintf("select * from members where id = %d",$order['member_id']));
			if (array_key_exists('product_id',$_REQUEST) && count($_REQUEST['product_id']) > 0) {
				$lines = $this->fetchAll(sprintf("select ol.*, p.code, p.name, po.teaser as opt_teaser, pr.teaser as recur_teaser from order_lines ol left join product_options po on po.id = ol.options_id left join product_recurring pr on pr.id = ol.recurring_period, product p where ol.order_id = %d and ol.product_id in (%s) and p.id = ol.product_id",$order["id"],implode(",",$_REQUEST['product_id'])));
			}
			else {
				$lines = $this->fetchAll(sprintf("select ol.*, p.code, p.name, po.teaser as opt_teaser, pr.teaser as recur_teaser from order_lines ol left join product_options po on po.id = ol.options_id left join product_recurring pr on pr.id = ol.recurring_period, product p where ol.order_id = %d and p.id = ol.product_id",$order["id"]));
			}
			$taxes = $this->fetchAll(sprintf("select ot.*, t.* from order_taxes ot, taxes t where t.id = ot.tax_id and ot.order_id = %d and line_id = 0",$order["id"]));
			$order["line_taxes"] = $taxes;
			foreach($lines as $subkey=>$line) {
				$order['line'] = $this->formatOrderLine($line);
				$inner->addData($order);
				$rows[] = $inner->show();
			}
		}
		$outer->addTag('rows',implode('',$rows),false);
		$tmp = $outer->show();
		$fromdate = date("Y-m-d", strtotime($_REQUEST['from']));
		$todate = date("Y-m-d", strtotime($_REQUEST['to']));
		header('Content-Type: application/csv');
		header(sprintf('Content-Disposition: attachment; filename=sales-details-%s-%s.csv',$fromdate,$todate));
		header("Content-Length: ".strlen($tmp));
		header('Pragma: no-cache');
		echo $tmp;
		exit();
	}

    /**
     * @return array|false|mixed|string|string[]|void
     * @throws phpmailerException
     */
    function recurringOrders() {
		$outer = new Forms();
		$outer->init($this->getTemplate(__FUNCTION__));
		$flds = $outer->buildForm($this->getFields(__FUNCTION__));
		$totalTotal = 0;
		$totalGoods = 0;
		$totalShipping = 0;
		$recs = array();
		if (count($_POST) > 0 && array_key_exists(__FUNCTION__,$_POST)) {
			$outer->addData($_POST);
			$valid = $outer->validate();
			if ($valid) {
				$where = array("b.original_id = o.id");
				if (array_key_exists('from',$_REQUEST) && strlen($outer->getData('from')) > 0) {
					$where[] = sprintf('b.billing_date >= "%s"',$outer->getData('from'));
				}
				if (array_key_exists('to',$_REQUEST) && strlen($outer->getData('to')) > 0) {
					$where[] = sprintf('b.billing_date <= "%s"',$outer->getData('to'));
				}
				if (array_key_exists('order_status',$_REQUEST) && count($_REQUEST['order_status']) > 0) {
					$status = 0;
					foreach($_REQUEST['order_status'] as $key=>$value) {
						$status |= $value;
					}
					$where[] = sprintf('o.order_status & %d = %d',$status,$status);
				}
				if (array_key_exists('product_id',$_REQUEST) && $_REQUEST['product_id'] > 0) {
					$where[] = sprintf('o.id in (select order_id from order_lines where product_id = %d)',$_REQUEST['product_id']);
				}
				if (array_key_exists('pager',$_REQUEST)) $perPage = $_REQUEST['pager'];
				$count = $this->fetchScalar(sprintf('select count(0) from orders o, order_billing b where %s',implode(' and ',$where)));
				$outer->addTag('count',$count);
				if (array_key_exists('product_id',$_REQUEST) && $_REQUEST['product_id'] > 0) {
					$t = $this->fetchSingle(sprintf("select sum(ol.value) as sumValue, sum(o.total) as totalValue, sum(o.shipping) as sumShipping from orders o, order_lines ol, order_billing b where %s and ol.product_id = %d and ol.deleted = 0 and ol.order_id = o.id group by ol.product_id, ol.options_id",implode(' and ',$where),$_REQUEST['product_id']));
					$totalShipping = $t['sumShipping'];
					$totalGoods = $t['sumValue'];
					$totalTotal = $t['totalValue'];
				}
				else {
					if ($t = $this->fetchSingle(sprintf("select sum(value) as sumValue, sum(shipping) as sumShipping, sum(total) as sumTotal from orders o, order_billing b where %s",implode(' and ',$where)))) {
						$totalShipping = $t['sumShipping'];
						$totalGoods = $t['sumValue'];
						$totalTotal = $t['sumTotal'];
					}
				}
				if (array_key_exists('pagenum',$_REQUEST)) 
					$pageNum = $_REQUEST['pagenum'];
				else
					$pageNum = 1;	// no 0 based calcs
				if ($pageNum <= 0) $pageNum = 1;
				$perPage = array_key_exists('pager',$_REQUEST) ? $_REQUEST['pager'] : $this->m_perrow;
				$pageNum = max(1,min($pageNum, (floor(($count-1)/$perPage)+1)));
				$outer->setData('pagenum', $pageNum);
				$pagination = $this->pagination($count, $perPage, $pageNum,
						array('prev'=>$this->M_DIR.'forms/paginationPrev.html','next'=>$this->M_DIR.'forms/paginationNext.html',
						'pages'=>$this->M_DIR.'forms/paginationPage.html', 'wrapper'=>$this->M_DIR.'forms/paginationWrapper.html'),
						array('url'=>'/modit/ajax/showSearchForm/credit','destination'=>'middleContent')
				);
				$start = ($pageNum-1)*$perPage;
				if (array_key_exists('product_id',$_REQUEST) && $_REQUEST['product_id'] > 0) {
					$sql = sprintf('select o.*, m.firstname, m.lastname, ol.value, b.billing_date, b.order_id from orders o left join members m on o.member_id = m.id, order_lines ol, order_billing b where %s and ol.product_id = %d and ol.deleted = 0 and ol.order_id = o.id order by %s %s limit %d,%d',implode(' and ',$where),$_REQUEST['product_id'],$_REQUEST['sortby'], $_REQUEST['sortorder'], $start,$perPage);
				}
				else {
					$sql = sprintf('select o.*, m.firstname, m.lastname, b.billing_date, b.order_id from orders o left join members m on o.member_id = m.id, order_billing b where %s order by %s %s limit %d,%d',implode(' and ',$where),$_REQUEST['sortby'], $_REQUEST['sortorder'], $start,$perPage);
				}
				$recs = $this->fetchAll($sql);
				$outer->addTag('pagination',$pagination,false);
			}
		}
		$result = array();
		$inner = new Forms();
		$inner->init($this->getTemplate(__FUNCTION__.'Row'));
		$flds = $inner->buildForm($this->getFields(__FUNCTION__.'Row'));
		$goods = 0;
		$shipping = 0;
		$total = 0;
		foreach($recs as $key=>$value) {
			$order = $this->formatOrder($value);
			$inner->addData($order);
			$result[] = $inner->show();
			$goods += $value["value"];
			$shipping += $value["shipping"];
			$total += $value["total"];
		}
		$outer->addTag('rows',implode('',$result),false);
		$outer->addTag("pageGoods",$this->my_money_format($goods));
		$outer->addTag("pageShipping",$this->my_money_format($shipping));
		$outer->addTag("pageTotal",$this->my_money_format($total));
		$outer->addTag("totalGoods",$this->my_money_format($totalGoods));
		$outer->addTag("totalShipping",$this->my_money_format($totalShipping));
		$outer->addTag("totalTotal",$this->my_money_format($totalTotal));
		if ($this->isAjax()) {
			return $this->ajaxReturn(array('status'=>true,'html'=>$outer->show()));
		}
		else
			return $this->show($outer->show());
	}

    /**
     * @return array|false|mixed|string|string[]|void
     * @throws phpmailerException
     */
    function allocation() {
		$outer = new Forms();
		$outer->init($this->getTemplate(__FUNCTION__));
		$flds = $outer->buildForm($this->getFields(__FUNCTION__));
		if (count($_POST) > 0 && array_key_exists(__FUNCTION__,$_POST)) {
			$outer->addData($_POST);
			$valid = $outer->validate();
			if ($valid && $outer->getData("member_id") == 0) {
				$outer->addFormError("Customer is a required field");
				$valid = false;
			}
			if (array_key_exists("update",$_REQUEST)) {
				if (!array_key_exists("checkNumber",$_REQUEST) || strlen($_REQUEST["checkNumber"]) == 0) {
					$valid = false;
					$outer->addFormError("The Cheque Number is required");
				}
				if (!array_key_exists("checkAmount",$_REQUEST) || $_REQUEST["checkAmount"] <= 0) {
					$valid = false;
					$outer->addFormError("The Cheque Amount is required");
				}
				if (!array_key_exists("pay",$_REQUEST) || count($_REQUEST["pay"]) == 0) {
					$outer->addFormError("You must select which orders are being paid");
					$valid = false;
				}
				if ($valid) {
					$t = 0;
					foreach($_REQUEST["pay"] as $k=>$paying) {
						if ($paying == 1) {
							$t += $_REQUEST["balance"][$k];
							$b = $this->fetchSingle(sprintf("select total, authorization_amount from orders where id = %d", $k));
							if (($b["total"] - $b["authorization_amount"]) < $_REQUEST["balance"][$k]) {
								$valid = false;
								$outer->addFormError(sprintf("Order #%d is overpaid. Balance is %.2f, paid is %.2f",
									$k, $b["total"] - $b["authorization_amount"], $_REQUEST["balance"][$k]));
							}
						}
					}
					if (abs($t - $_REQUEST["checkAmount"]) >= .01) {
						$valid = false;
						$outer->addFormError(sprintf("Check Amount (%.2f) and selected order total (%.2f) do not match", $_REQUEST["checkAmount"], $t));
					}
				}
				if ($valid) {
					$p = array('member_id'=>$outer->getData('member_id'),'reference_number'=>$outer->getData('checkNumber'),
							'reference_date'=>date("Y-m-d H:i:s"),'amount'=>$outer->getData('checkAmount'));
					$stmt = $this->prepare(sprintf("insert into order_payment(%s) values(%s?)", implode(",",array_keys($p)), str_repeat("?, ", count($p)-1)));
					$stmt->bindParams(array_merge(array(str_repeat("s",count($p))),array_values($p)));
					$this->beginTransaction();
					if ($valid = $stmt->execute()) {
						$pmt = $this->insertId();
						foreach($_REQUEST["pay"] as $k=>$v) {
							if ($v == 1) {
								$d = array("payment_id"=>$pmt, "order_id"=>$k, "amount"=>$_REQUEST["balance"][$k]);
								$stmtd = $this->prepare(sprintf("insert into order_payment_detail(%s) values(%s?)", implode(", ",array_keys($d)), str_repeat("?, ", count($d)-1)));
								$stmtd->bindParams(array_merge(array(str_repeat("s",count($d))),array_values($d)));
								$valid &= $stmtd->execute();
								if ($valid) {
									$stmto = $this->prepare(sprintf("update orders set authorization_amount = authorization_amount + %.2f where id = %d", $_REQUEST["balance"][$k], $k));
									$valid &= $stmto->execute();
								}
							}
						}
					}
					if ($valid) {
						$this->commitTransaction();
						$_REQUEST["pay"] = array();
						$_REQUEST["balance"] = array();
						$outer->addData(array("checkNumber"=>"","checkAmount"=>0.00));
					}
					else $this->rollbackTransaction();
				}
			}
			//if ($valid) {
				$srch = array(sprintf("order_status & %d = %d",STATUS_PROCESSING,STATUS_PROCESSING));
				foreach($_POST as $key=>$value) {
					switch($key) {
						case "member_id":
							if ($value != 0) $srch[] = sprintf("o.member_id = %d",$value);
							break;
						case "start_date":
							if ($value != "") $srch[] = sprintf("o.order_date >= '%s 00:00:00'",$outer->getData($key));
							break;
						case "end_date":
							if ($value != "") $srch[] = sprintf("o.order_date <= '%s 23:59:59'",$outer->getData($key));
							break;
						default:
							break;
					}
					$recs = $this->fetchAll(sprintf("select o.*, m.company, m.firstname, m.lastname from orders o, members m where m.id = o.member_id and total - authorization_amount > 0.01 and %s",implode(" and ",$srch)));
					$inner = new Forms();
					$inner->init($this->getTemplate(__FUNCTION__."Row"));
					$flds = $inner->buildForm($this->getFields(__FUNCTION__."Row"));
					$result = array();
					if (count($recs) > 0) {
						$outer->addTag("ct",count($recs));
						$edit = $outer->buildForm($this->getFields(__FUNCTION__."Update"));
					}
					$status = $this->fetchAll(sprintf('select * from code_lookups where type="orderStatus" order by sort, code'));
					foreach($recs as $key=>$o) {
						$inner->reset();
						$tmp = array();
						foreach($status as $key=>$value) {
							if ($o['order_status'] & (int)$value['code'])
								$tmp[] = $value['value'];
						}
						$o['order_status'] = implode(', ',$tmp);
						$o["balance"] = $o["total"] - $o["authorization_amount"];
						$inner->getField("pay")->addAttribute("checked",array_key_exists("pay",$_REQUEST) && array_key_exists($o["id"],$_REQUEST["pay"]) ? "checked" : "");
						$o["pay[%%id%%]"] = array_key_exists("pay",$_REQUEST) && array_key_exists($o["id"],$_REQUEST["pay"]) ? "1" : "0";
						if (array_key_exists("balance",$_REQUEST) && array_key_exists($o["id"],$_REQUEST["balance"]) && $_REQUEST["balance"][$o["id"]] > 0)
							$o["balance"] = $_REQUEST["balance"][$o["id"]];
						$inner->addData($o);
						$result[] = $inner->show();
					}
					$outer->addTag("orders",implode("",$result),false);
				//}
			}
		}
		if ($this->isAjax())
			return $this->ajaxReturn(array('status'=>true,'html'=>$outer->show()));
		else return $this->show($outer->show());
	}

    /**
     * @return false|string
     * @throws phpmailerException
     */
    function getMembers() {
		$query = $_REQUEST['s'];
		$member = $this->fetchScalar(sprintf("select id from members where id = %d",$_REQUEST['m']));
		$select = new select();
		$select->addAttributes(array("sql"=>sprintf("select id, concat(company,', ',lastname,', ',firstname,if(id=%d,'*',''),' ',email) from members where (firstname like '%%%s%%' or lastname = '%%%s%%' or company like '%%%s%%' or id = %d) and deleted = 0 and enabled = 1 order by company, lastname, firstname", $member, $query, $query, $query, $member ),"name"=>"member_id"));
		return $this->ajaxReturn(array('status'=>true,'html'=>$select->show()));
	}

    /**
     * @return array|false|mixed|string|string[]|void
     * @throws Exception
     */
    function QB () {
		$form = new Forms();
		$form->init($this->getTemplate(__FUNCTION__));
		$form = $this->consolidatedExists($form);
		if ((!$this->checkArray("administrator:qb:accessToken",$_SESSION)) || date(DATE_ATOM) > date(DATE_ATOM,strtotime($_SESSION["administrator"]["qb"]["expiry"]))) {
			$inner = new Forms();
			$inner->init($this->getTemplate(__FUNCTION__."Connect"));
			$flds = $inner->buildForm($this->getFields(__FUNCTION__."Connect"));
			$inner->addData($GLOBALS["quickbooks"]);
			$_SESSION["administrator"]["qb"] = array("state"=>sprintf("security_token=%s",random_int(10000,100000)));
			$form->setData("connect",$inner->show());
		}
		$flds = $this->getFields(__FUNCTION__);
		if ($this->checkArray("administrator:qb:accessToken",$_SESSION)) {
			$flds["qb_invoice_id"]["required"] = true;
			$flds["qb_invoice_id"]["value"] = $this->fetchScalar(sprintf("select qb_invoice_id from qb_export order by id desc limit 1")) + 1;
		}
		$flds = $form->buildForm($flds);
		if (count($_POST) > 0 && array_key_exists(__FUNCTION__,$_POST)) {
			$form->addData($_POST);
			if ($form->validate()) {
				$srch = array("n/a"=>"1 = 1");
				foreach($flds as $k=>$v) {
					switch($k) {
					case "invoice_date":
						if (($v = $form->getData($k)) !="")
							$srch[$k] = sprintf("date(qb.invoice_date) >= '%s'", $v);
						break;
					case "cutoff_date":
						if (($v = $form->getData($k)) !="")
							$srch[$k] = sprintf("date(qb.invoice_date) <= '%s'", $v);
						break;
					case "qb_invoice_id":
						if (($v = $form->getData($k)) != 0)
							$srch[$k] = sprintf("qb_invoice_id = %d", $v);
						break;
					case "order_id":
						if (($v = $form->getData($k)) != 0)
							$srch[$k] = sprintf("order_id = %d", $v);
						break;
					case "member_id":
						$v = $form->getData($k);
						if (is_array($v) && count($v) > 0) {
							$srch[$k] = sprintf("member_id in (%s)", implode(",",$v));
						}
/*
						if (($v = $form->getData($k)) != 0)
							$srch[$k] = sprintf("member_id = %d", $v);
*/
						break;
					case "bill_me_later":
						$srch[$k] = sprintf("coalesce(a.authorization_amount,0) %s= 0.00", $v ? "!":"");
						break;
					default:
						break;
					}
				}
				$sql = sprintf("select count(qb.id) from qb_export qb, members m where m.id = qb.member_id and %s order by qb_invoice_id desc", implode(" and ",array_values($srch)));
				$ct = $this->fetchScalar($sql);
				$recs = $this->fetchAll(sprintf("select qb.*, m.company, invoice_amount+tax_amount as total from qb_export qb, members m where m.id = qb.member_id and %s order by qb_invoice_id desc", implode(" and ", array_values($srch))));
				$rows = array();
				$inner = new Forms();
				$inner->init($this->getTemplate(__FUNCTION__."Row"));
				$flds = $inner->buildForm($this->getFields(__FUNCTION__."Row"));
				foreach($recs as $k=>$v) {
					$inner->addData($v);
					$rows[] = $inner->show();
				}
				$form->setData("results",implode("",$rows));
			}
		}
		if ($this->isAjax())
			return $this->ajaxReturn(array("status"=>"true","html"=>$form->show()));
		else
			return $this->show($form->show());
	}

    /**
     * @return false|string
     * @throws phpmailerException
     */
    function qbExportCheck() {
		$form = new Forms();
		$form->init($this->getTemplate(__FUNCTION__));
		$flds = $form->buildForm($this->getFields(__FUNCTION__));
		$ct = $this->fetchScalarAll(sprintf("select distinct invoice_date from qb_export where consolidate_id < 0 group by consolidate_id"));
		$this->consolidatedExists($form);
		if (count($_POST) > 0 && array_key_exists(__FUNCTION__,$_POST)) {
			$form->addData($_POST);
			if ($form->validate()) {
				//$form->init($this->getTemplate(__FUNCTION__."Check"));
				//$flds = $form->buildForm($this->getFields(__FUNCTION__."Check"));
				$mode = $form->getData("billing_type");
				switch($mode) {
				case "C":
					$srch = array("cutoff_date"=>sprintf("date(cd2.actual_date) <= '%s'",date("Y-m-d")),
						"a"=>"o.custom_qb_order = 0",
						"b"=>"m.id = o.member_id",
						"c"=>sprintf("o.order_status = %d",STATUS_SHIPPED),
						"d"=>"o.deleted = 0",
						"e"=>"not exists (select 1 from custom_delivery cd where cd.order_id = o.id)");
					$sql = sprintf("select m.id, m.company, m.custom_qb_id, count(o.id) as ct, sum(total) as total, 0 as paid, sum(total) as net from orders o, members m where");
					break;
				case "B":
					$srch = array("cutoff_date"=>sprintf("date(cd2.actual_date) <= '%s'",date("Y-m-d")),
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
						"k"=>"not exists (select 1 from order_authorization oa where oa.order_id = o.id)");
					$sql = sprintf("select m.id, m.company, m.custom_qb_id, count(o.id) as ct, sum(total) as total, 0 as paid, sum(total) as net from orders o, custom_delivery cd1, custom_delivery cd2, members m where");
					break;
				case "P":
					$srch = array("cutoff_date"=>sprintf("date(cd2.actual_date) <= '%s'",date("Y-m-d")),
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
						"k"=>"oa.order_id = o.id",
						"l"=>"oa.authorization_type = 'D'",
						"m"=>"oa.authorization_success = 1");
					$sql = sprintf("select m.id, m.company, m.custom_qb_id, count(o.id) as ct, sum(total) as total, sum(oa.authorization_amount) as paid, sum(total) - sum(oa.authorization_amount) as net from orders o, order_authorization oa, custom_delivery cd1, custom_delivery cd2, members m where");
					break;
				}
				foreach($_REQUEST as $k=>$v) {
					switch($k) {
					case "invoice_date":
						if ($v != "")
							if ($mode == "C")
								$srch[$k] = sprintf("date(o.order_date) >= '%s'", $form->getData($k));
							else
								$srch[$k] = sprintf("date(cd2.actual_date) >= '%s'", $form->getData($k));
						break;
					case "cutoff_date":
						if ($v != "")
							if ($mode == "C")
								$srch[$k] = sprintf("date(o.order_date) <= '%s'", $form->getData($k));
							else
								$srch[$k] = sprintf("date(cd2.actual_date) <= '%s'", $form->getData($k));
						break;
					case "order_id":
							if ($v != "")
								$srch[$k] = sprintf("o.order_id = %d", $v);
							break;
					case "member_id":
						$v = $form->getData($k);
						if (is_array($v) && count($v) > 0) {
							$srch[$k] = sprintf("o.member_id in (%s)", implode(",",$v));
						}
/*
						if ($v > 0)
							$srch[$k] = sprintf("o.member_id = %d", $v);
*/
						break;
					case "billing_type":
						break;
//					case "bill_me_later":
//						$srch[$k] = sprintf("coalesce(a.authorization_amount,0) %s= 0.00", $v ? "!":"");
//						break;
					default:
						break;
					}
				}
				//$sql = sprintf("select m.id, m.company, m.custom_qb_id, count(o.id) as ct, sum(total) as total from orders o left join order_authorization a on a.order_id = o.id and a.authorization_type='D' and a.authorization_success = 1, custom_delivery cd1, custom_delivery cd2, members m where %s group by m.id order by m.company limit 25",implode(" and ",array_values($srch)));
				$sql = sprintf("%s %s group by m.id order by m.company limit %d", $sql, implode(" and ",array_values($srch)), $form->getData("pager"));
				$recs = $this->fetchAll($sql);
				$inner = new Forms();
				$inner->init($this->getTemplate(__FUNCTION__."Row"));
				$flds = $inner->buildForm($this->getFields(__FUNCTION__."Row"));
				$rows = array();
				foreach($recs as $k=>$v) {
					$inner->addData($v);
					$rows[] = $inner->show();
				}
				$form->setData("rows",implode("",$rows));
			}
		}
		return $this->ajaxReturn(array("status"=>true,"html"=>$form->show()));
	}

    /**
     * @return false|string
     * @throws phpmailerException
     */
    function qbDetails() {
		$form = new Forms();
		$form->init($this->getTemplate(__FUNCTION__));
		$flds = $form->buildForm($this->getFields(__FUNCTION__));


		if (count($_POST) > 0 && array_key_exists(__FUNCTION__,$_POST)) {
			$form->addData($_POST);
			if ($form->validate()) {
				$srch = array("cutoff_date"=>sprintf("date(cd2.actual_date) <= '%s'",date("Y-m-d")),
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
					"k"=>"ol.order_id = o.id",
					"l"=>"ol.custom_package='S'",
					"m"=>"p.id = ol.product_id");
				foreach($_REQUEST as $k=>$v) {
					switch($k) {
					case "invoice_date":
						if ($v != "")
							$srch[$k] = sprintf("date(cd2.actual_date) >= '%s'", $form->getData($k));
						break;
					case "cutoff_date":
						if ($v != "")
							$srch[$k] = sprintf("date(cd2.actual_date) <= '%s'", $form->getData($k));
						break;
					case "order_id":
							if ($v != "")
								$srch[$k] = sprintf("o.order_id = %d", $v);
							break;
					case "member_id":
						if ($v > 0)
							$srch[$k] = sprintf("o.member_id = %d", $v);
						break;
					default:
						break;
					}
				}
				//$sql = sprintf("select m.id, m.company, m.custom_qb_id, count(o.id) as ct, sum(total) as total from orders o, custom_delivery cd1, custom_delivery cd2, members m where %s group by m.id order by m.company limit 25",implode(" and ",array_values($srch)));
				//$recs = $this->fetchAll($sql);
				$sql = sprintf("select o.*, p.name, m.company, cd2.actual_date from orders o, order_lines ol, product p, custom_delivery cd1, custom_delivery cd2, members m where %s order by o.id", implode(" and ",$srch));
				$recs = $this->fetchAll($sql);
				$inner = new Forms();
				$inner->init($this->getTemplate(__FUNCTION__."Row"));
				$flds = $inner->buildForm($this->getFields(__FUNCTION__."Row"));
				$rows = array();
				foreach($recs as $k=>$v) {
					$inner->addData($v);
					$rows[] = $inner->show();
				}
				$form->setData("rows",implode("",$rows));
			}
		}
		return $this->ajaxReturn(array("status"=>true,"html"=>$form->show()));
	}

    /**
     * @return false|string
     * @throws \QuickBooksOnline\API\Exception\IdsException
     * @throws \QuickBooksOnline\API\Exception\SdkException
     * @throws phpmailerException
     */
    function qbProcess() {
		$form = new Forms();
		$form->init($this->getTemplate(__FUNCTION__));
		$flds = $form->buildForm($this->getFields(__FUNCTION__));
		$inner = new Forms();
		$inner->init($this->getTemplate(__FUNCTION__."Row"));
		$flds = $inner->buildForm($this->getFields(__FUNCTION__."Row"));
		if (array_key_exists("process",$_POST) && is_array($_POST["process"])) {
			$form->addData($_POST);
			$form->validate();
			$dataService = DataService::Configure(array(
				'auth_mode' => 'oauth2',
				'ClientID' => $GLOBALS["quickbooks"]["client_id"],
				'ClientSecret' => $GLOBALS["quickbooks"]["client_secret"],
				'accessTokenKey' => $_SESSION["administrator"]["qb"]["accessToken"],
				'refreshTokenKey' => $_SESSION["administrator"]["qb"]["refreshToken"],
				'QBORealmID' => $_SESSION["administrator"]["qb"]["realmId"],
				'baseUrl' => $GLOBALS["quickbooks"]["baseUrl"]	//"Development"
			));
			$dataService->setLogLocation(sprintf("qb-%s.log",date("Y-m-d")));
			$CompanyInfo = $dataService->getCompanyInfo();
			$error = $dataService->getLastError();

			$rows = array();
			$errFound = false;
//
//	Legacy iassue with out of province orders. ON tax is on the order but @ $0.00
//	Quickbooks can only have 1 tax
//

				$this->execute(sprintf("
delete FROM `order_taxes` WHERE 
line_id = 0 and tax_amount = 0 and order_id in (
	select * from (
		SELECT order_id FROM `order_taxes` ot 
		WHERE ot.line_id = 0 
		group by ot.order_id
		having count(ot.order_id) > 1) xxx)"));
			foreach($_POST["process"] as $k=>$member_id) {
				if (!$errFound) {
					$company = $this->fetchSingle(sprintf("select * from members where id = %d", $member_id));
//
//	old quickbooks had only 1 tax / invoice
//	now they allow tax(es) at the detail level
//	we don't need 1 invoice / tax any more
//
/*
					$sql = sprintf("select distinct t.custom_qb_id as qb_tax_id
from orders o left join order_authorization a on a.order_id = o.id and a.authorization_type='D' and a.authorization_success = 1, order_lines ol, product p, custom_delivery cd1, custom_delivery cd2, members m, taxes t, order_taxes ot
where o.member_id = %d and m.id = o.member_id and cd1.order_id = o.id and cd1.service_type = 'P' and cd1.completed = 1 and cd2.service_type = 'D' and 
cd2.order_id = o.id and cd2.completed = 1 and ol.order_id = o.id and ol.custom_package = 'S' and p.id = ol.product_id and o.custom_qb_order = 0 and 
date(cd2.actual_date) <= '%s' and ot.order_id = o.id and ot.line_id = 0 and t.id = ot.tax_id and o.order_status = %d and o.deleted = 0 and coalesce(a.authorization_amount,0) %s= 0", $member_id, $form->getData("cutoff_date"), STATUS_SHIPPED, $form->getData("bill_me_later") == 1 ? "!":"" );
					$taxes = $this->fetchAll($sql);
					foreach($taxes as $tk=>$tv) {
*/
					$this->beginTransaction();
/*
						$sql = sprintf("select o.*, p.name, m.company, m.custom_qb_id, t.custom_qb_id as qb_tax_id, coalesce(a.authorization_amount,0) as paid_amount
from orders o left join order_authorization a on a.order_id = o.id and a.authorization_type='D' and a.authorization_success = 1, order_lines ol, product p, custom_delivery cd1, custom_delivery cd2, members m, taxes t, order_taxes ot
where o.member_id = %d and m.id = o.member_id and cd1.order_id = o.id and cd1.service_type = 'P' and cd1.completed = 1 and cd2.service_type = 'D' and 
cd2.order_id = o.id and cd2.completed = 1 and ol.order_id = o.id and ol.custom_package = 'S' and p.id = ol.product_id and o.custom_qb_order = 0 and 
date(cd2.actual_date) <= '%s' and ot.order_id = o.id and ot.line_id = 0 and t.id = ot.tax_id and t.custom_qb_id = %d and o.order_status = %d and o.deleted = 0 and coalesce(a.authorization_amount,0) %s= 0
order by o.id", $member_id, $form->getData("cutoff_date"), $tv["qb_tax_id"], STATUS_SHIPPED, $form->getData("bill_me_later") == 1 ? "!":"" );
*/
					$parms = array(
						"a"=>sprintf("o.member_id = %d",$member_id),
						"b"=>"m.id = o.member_id",
						"c"=>"cd1.order_id = o.id",
						"d"=>"cd1.service_type = 'P'",
						"e"=>"cd1.completed = 1",
						"f"=>"cd2.service_type = 'D'",
						"g"=>"cd2.order_id = o.id",
						"h"=>"cd2.completed = 1",
						"i"=>"ol.order_id = o.id",
						"j"=>"ol.custom_package = 'S'",
						"k"=>"p.id = ol.product_id",
						"l"=>"o.custom_qb_order = 0",
						"m"=>sprintf("o.order_status = %d", STATUS_SHIPPED),
						"n"=>"o.deleted = 0",
						"o"=>sprintf("coalesce(a.authorization_amount,0) %s= 0",$form->getData("bill_me_later") == 1 ? "!":""),
						"p"=>sprintf("date(cd2.actual_date) <= '%s'",$form->getData("cutoff_date"))
					);
					if (($st_date = $form->getData("invoice_date")) != "") {
						$parms["q"] = sprintf("date(cd2.actual_date) >= '%s'", $st_date);
					}
					$sql = sprintf("select o.*, p.name, m.company, m.custom_qb_id, coalesce(t.custom_qb_id,0) as qb_tax_id, coalesce(a.authorization_amount,0) as paid_amount
from orders o left join order_authorization a on a.order_id = o.id and a.authorization_type='D' and a.authorization_success = 1 left join order_taxes ot on ot.order_id = o.id and ot.line_id = 0 left join taxes t on t.id = ot.tax_id, order_lines ol, product p, custom_delivery cd1, custom_delivery cd2, members m
where %s order by o.id", implode(" and ",$parms));
/*
						$sql = sprintf("select o.*, p.name, m.company, m.custom_qb_id, t.custom_qb_id as qb_tax_id, coalesce(a.authorization_amount,0) as paid_amount
from orders o left join order_authorization a on a.order_id = o.id and a.authorization_type='D' and a.authorization_success = 1, order_lines ol, product p, custom_delivery cd1, custom_delivery cd2, members m, taxes t, order_taxes ot
where o.member_id = %d and m.id = o.member_id and cd1.order_id = o.id and cd1.service_type = 'P' and cd1.completed = 1 and cd2.service_type = 'D' and 
cd2.order_id = o.id and cd2.completed = 1 and ol.order_id = o.id and ol.custom_package = 'S' and p.id = ol.product_id and o.custom_qb_order = 0 and 
date(cd2.actual_date) <= '%s' and ot.order_id = o.id and ot.line_id = 0 and t.id = ot.tax_id %s and o.order_status = %d and o.deleted = 0 and coalesce(a.authorization_amount,0) %s= 0
order by o.id", $member_id, $form->getData("cutoff_date"), $st_date, STATUS_SHIPPED, $form->getData("bill_me_later") == 1 ? "!":"" );
*/

					$recs = $this->fetchAll($sql);
					$hdr = array("created"=>date(DATE_ATOM),"member_id"=>$company["id"],"order_count"=>count($recs),"invoice_date"=>$form->getData("cutoff_date"),"rand_id"=>random_int(1,100000));
					if ($p = $this->fetchSingle(sprintf("select * from members where id = %d and custom_consolidate_invoices > 0", $company["custom_parent_org"]))) {
						//
						//	Store negative id until the "consolidation" run is complete. Flag -'s in the UI as incomplete
						//
						$hdr["consolidate_id"] = -$company["custom_parent_org"];
					}
					$hdr_stmt = $this->prepare(sprintf("insert into qb_export(%s) values(?%s)", implode(", ",array_keys($hdr)), str_repeat(", ?", count($hdr)-1)));
					$hdr_stmt->bindParams(array_merge(array(str_repeat('s', count($hdr))),array_values($hdr)));
					if (!$hdr_stmt->execute()) {
						$errFound = true;
						$errs = $this->showErrors();
						$form->addFormError($errs);
						$this->rollbackTransaction();
						$this->logMessage(__FUNCTION__,sprintf("error [%s]", print_r($errs,true)),1,true);
						continue;
					}
					$hdr_id = $this->insertId();
					$dtls = array();
					$orders = array();
					$taxes = 0;
					$inv_amt = 0;
					$inv_taxes = 0;
					$inv_paid = 0;
					$dtl = array("qb_export_id"=>$hdr_id,"order_id"=>0,"order_amount"=>0.0,"tax_amount"=>0.0,"paid_amount"=>0);
					$dtl_stmt = $this->prepare(sprintf("insert into qb_export_dtl(%s) values(?%s)", implode(", ",array_keys($dtl)), str_repeat(", ?", count($dtl)-1)));
					if (QB_API==1) {
						foreach($recs as $k=>$v) {
							$dtl["order_id"] = $v["id"];
							$dtl["order_amount"] = $v["value"];
							$dtl["tax_amount"] = $v["taxes"];
							$dtl["paid_amount"] = $v["paid_amount"];
							$inv_amt += $v["value"];
							$inv_taxes += $v["taxes"];
							$inv_paid += $v["paid_amount"];
							$dtl_stmt->bindParams(array_merge(array(str_repeat('s', count($dtl))),array_values($dtl)));
							$dtl_stmt->execute();
							$dtls[] = Line::create([
								"Id"=>$k,
								"LineNum"=>$k,
								"Description"=>sprintf("Order #%d", $v["id"]),
								"Amount"=>$v["value"],
								"DetailType" =>"SalesItemLineDetail",
								"SalesItemLineDetail"=> [
									"ItemRef"=> [
										"value"=>1,	//67?
										"name"=>$v["name"]	//sales?
									],
									"UnitPrice"=>$v["value"],
									"Qty"=>1,
									"TaxCodeRef"=>[
										"value"=>$v["qb_tax_id"] //Sonarcloud report - Variables should be initialized before use
									]
								]
							]);
							$taxes += $v["taxes"];
							$orders[] = $v["id"];
						}
					}
					else {
						foreach($recs as $k=>$v) {
							$dtl["order_id"] = $v["id"];
							$dtl["order_amount"] = $v["value"];
							$dtl["tax_amount"] = $v["taxes"];
							$dtl["paid_amount"] = $v["paid_amount"];
							$inv_amt += $v["value"];
							$inv_taxes += $v["taxes"];
							$inv_paid += $v["paid_amount"];
							$dtl_stmt->bindParams(array_merge(array(str_repeat('s', count($dtl))),array_values($dtl)));
							$dtl_stmt->execute();
							$dtls[] = Line::create([
								"Id"=>$k,
								"LineNum"=>$k,
								"Description"=>sprintf("Order #%d", $v["id"]),
								"Amount"=>$v["value"],
								"DetailType" =>"SalesItemLineDetail",
								"SalesItemLineDetail"=> [
									"ItemRef"=> [
										"value"=>QB_SERVICES_ID,
										"name"=>$v["name"]
									],
									"UnitPrice"=>$v["value"],
									"Qty"=>1,
									"TaxCodeRef"=>[
										"value"=> $v["taxes"] >= .01 ? $v["qb_tax_id"] : QB_TAX_EXEMPT
									]
								]
							]);
							$taxes += $v["taxes"];
							$orders[] = $v["id"];
						}
					}
					$upd = $this->prepare(sprintf("update qb_export set invoice_amount = ?, tax_amount = ?, paid_amount = ? where id = ?"));
					$upd->bindParams(array("dddi", $inv_amt, $inv_taxes, $inv_paid, $hdr_id ));
					$upd->execute();
					$this->logMessage(__FUNCTION__,sprintf("^^^ dtls [%s]", print_r($dtls,true)),1);
					$nxt = $form->getData("qb_invoice_id");
					$form->setData("qb_invoice_id",$nxt+1);
					$myInvoice = Invoice::create([
						"Line"=>$dtls,
						"DocNumber"=>$nxt,
						"TxnDate"=>$form->getData("cutoff_date"),
						"CustomerRef"=> [
							"value"=> defined("DEV") && DEV ? QB_TEST_ACCOUNT : $company["custom_qb_id"]
						]
					]);
					$doc_id = 0;
					$balance = 0;
					if (!(array_key_exists("consolidate_id",$hdr) && $hdr["consolidate_id"] != 0)) {
						$response = $dataService->Add($myInvoice);
						$error = $dataService->getLastError();
/*
						if ($response == null) {
							$form->addFormError(sprintf("Oops... something went wrong with company %s", $company["company"]));
							$this->rollbackTransaction();
							$this->logMessage(__FUNCTION__,sprintf("invoice [%s] error [%s] dataService [%s] response [%s]", print_r($myInvoice,true), 
								print_r($error,true), print_r($dataService,true), print_r($response,true)),1,true);
							$errFound = true;
							continue 1;
						}
						else {
*/
							if ($error == null) {
								$doc_id = $response->DocNumber;
								$balance = $response->Balance;
							}
/*
						}
*/
					}
					else {
						$error = null;
						$doc_id = $nxt;
						$response = array();
						$balance = $inv_amt;
						$this->logMessage(__FUNCTION__,sprintf("in consolidated part response [%s]", print_r($hdr,true)),1);
					}
					if ($error != null) {
						$form->addFormError("The Status code is: " . $error->getHttpStatusCode());
						$form->addFormError("The Helper message is: " . $error->getOAuthHelperError());
						$form->addFormError("The Response message is: " . $error->getResponseBody());
						$errFound = true;
						$this->rollbackTransaction();
						$this->logMessage(__FUNCTION__,sprintf("invoice [%s] error [%s] dataService [%s] response [%s]", print_r($myInvoice,true), 
							print_r($error,true), print_r($dataService,true), print_r($response,true)),1,true);
					}
					else {
						$this->logMessage(__FUNCTION__,sprintf("invoice [%s] error [%s] dataService [%s] response [%s]", print_r($myInvoice,true), 
							print_r($error,true), print_r($dataService,true), print_r($response,true)),1);
						//$doc_id = $response->DocNumber;
						if (DEV && $doc_id == 0) {
							$doc_id = $hdr_id;
						}
						$this->execute(sprintf("update qb_export set qb_invoice_id = %d where id = %d", $doc_id, $hdr_id));
						$this->execute(sprintf("update orders set custom_qb_order = %d where id in (%s)", $doc_id, implode(", ", $orders)));
						if ($form->getData("bill_me_later") == 0)
							$this->execute(sprintf("update orders o set o.authorization_amount = (select order_amount+tax_amount from qb_export_dtl qb where qb.order_id = o.id) where o.custom_qb_order =%d", $doc_id));
						$inner->addData(array("company"=>$company["company"],"invoice_id"=>$doc_id,"amount"=>$inv_amt + $inv_taxes,"order_count"=>count($recs), "rounding_error"=>round($inv_amt + $inv_taxes - $balance,2), "paid_amount"=>$inv_paid));
						$valid = true;
						if ($inv_paid > 0 && !(array_key_exists("consolidate_id",$hdr) && $hdr["consolidate_id"] > 0)) {
							$invId = $response->Id;
							$payment = Payment::create([
								"TotalAmt" => $inv_paid,
								"Line" => [
									"Amount" => $inv_paid,
									"LinkedTxn" => [
										"TxnId" => $response->Id,
										"TxnType" => "Invoice"
									]
								]
							]);

							$response = $dataService->Add($payment);
							$error = $dataService->getLastError();
							if ($error != null) {
								$form->addFormError("The Status code is: " . $error->getHttpStatusCode());
								$form->addFormError("The Helper message is: " . $error->getOAuthHelperError());
								$form->addFormError("The Response message is: " . $error->getResponseBody());
								$errFound = true;
								$this->rollbackTransaction();
								$this->logMessage(__FUNCTION__,sprintf("invoice [%s] error [%s] dataService [%s] response [%s]", print_r($myInvoice,true), 
									print_r($error,true), print_r($dataService,true), print_r($response,true)),1,true);
							}
							else {
								$this->logMessage(__FUNCTION__,sprintf("payment [%s] error [%s] dataService [%s] response [%s]", print_r($payment,true), 
									print_r($error,true), print_r($dataService,true), print_r($response,true)),1);
								$valid = false;
							}
						}
						if ($valid) {
							$rows[] = $inner->show();
							$this->commitTransaction();
							$this->emailInvoice($hdr_id);
						}
					}
				}
			}
			$form->setData("rows",implode("",$rows));
		}
		$form = $this->consolidatedExists($form);
		return $this->ajaxReturn(array("status"=>true,"html"=>$form->show()));
	}

    /**
     * @return false|string
     * @throws phpmailerException
     */
    function qbInvoiceDetails() {
		$form = new Forms();
		$form->init($this->getTemplate(__FUNCTION__));
		$flds = $form->buildForm($this->getFields(__FUNCTION__));
		$inner = new Forms();
		$inner->init($this->getTemplate(__FUNCTION__."Row"));
		$flds = $inner->buildForm($this->getFields(__FUNCTION__."Row"));
		$rows = array();
		if ($hdr = $this->fetchSingle(sprintf("select * from qb_export where id =%d", array_key_exists("i_id",$_REQUEST) ? $_REQUEST["i_id"] : 0))) {
			$form->addData($hdr);

			if (array_key_exists(__FUNCTION__,$_REQUEST)) {
				$form->addData($_REQUEST);
			}
			$pageNum = $form->getData("pagenum");
			$perPage = $form->getData("pager");
			if ($perPage <= 0) $perPage = $this->m_perrow;
			$count = $this->fetchScalar(sprintf("select count(0) from qb_export_dtl qb, orders o, order_lines ol, product p where qb.qb_export_id =%d and o.id = qb.order_id and ol.order_id = o.id and p.id = ol.product_id and ol.custom_package ='S'", $hdr["id"]));
			$pageNum = max(1,min($pageNum, (floor(($count-1)/$perPage)+1)));
			$pagination = $this->pagination($count, $perPage, $pageNum,
							array('prev'=>$this->M_DIR.'forms/altPaginationPrev.html','next'=>$this->M_DIR.'forms/altPaginationNext.html',
				'pages'=>$this->M_DIR.'forms/altPaginationPage.html', 'wrapper'=>$this->M_DIR.'forms/altPaginationWrapper.html'),
				array('url'=>'/modit/ajax/qbInvoiceDetails/credit','destination'=>'popup'));
			$this->logMessage(__FUNCTION__,sprintf("pagination [%s]", print_r($pagination,true)),1);
			$recs = $this->fetchAll(sprintf("select qb.*, o.order_date, o.total, p.name from qb_export_dtl qb, orders o, order_lines ol, product p where qb.qb_export_id =%d and o.id = qb.order_id and ol.order_id = o.id and p.id = ol.product_id and ol.custom_package ='S' order by qb.id limit %d,%d", $hdr["id"], ($pageNum-1)*$perPage, $perPage));
			foreach($recs as $k=>$v) {
				$inner->addData($v);
				$rows[] = $inner->show();
			}
			$form->setData("rows",implode("",$rows));
			$form->setData("pagination",$pagination);
		}
		return $this->ajaxReturn(array("status"=>true,"html"=>$form->show()));
	}

    /**
     * @return array|false|mixed|string|string[]|void
     * @throws phpmailerException
     */
    function unpaidBillNow() {
		$outer = new Forms();
		$outer->init($this->getTemplate(__FUNCTION__));
		$inner = new Forms();
		$inner->init($this->getTemplate(__FUNCTION__."Row"));
		$flds = $outer->buildForm($this->getFields(__FUNCTION__));
		$flds = $inner->buildForm($this->getFields(__FUNCTION__."Row"));
		if (array_key_exists(__FUNCTION__,$_REQUEST)) {
			$outer->addData($_REQUEST);
			if ($outer->validate()) {
				$srch = array(
					"a"=>"m.id = o.member_id",
					"b"=>"o.authorization_amount <= o.total",
					"c"=>sprintf("o.order_status & %d = %d",STATUS_SHIPPED, STATUS_SHIPPED),
					"d"=>"d.order_id = o.id",
					"e"=>"d.service_type='D'",
					"f"=>"a.order_id = o.id",
					"g"=>"a.authorization_success = 1",
					"h"=>"a.authorization_type = 'A'"
				);
				foreach($_REQUEST as $k=>$v) {
					switch($k) {
						case "member_id":
							if ($v > 0)
								$srch[$k] = sprintf("o.member_id = %d", $v);
							break;
						case "opt_created":
							if (strlen($v) > 0 && strlen($outer->getData("actual_date")) > 0) {
								$srch[$k] = sprintf("DATE(d.actual_date) %s '%s'", $v, $outer->getData("actual_date"));
							}
							break;
						case "order_id":
							if ($v > 0)
								$srch[$k] = sprintf("o.id = %d", $v);
							break;
						case "completed":
							if (strlen($v) > 0) {
								$srch[$k] = sprintf("coalesce(p.authorization_amount,0) %s= 0.00", $v ? "!":"");
							}
						default:
					}
				}
				$recs = $this->fetchAll(sprintf("select o.*, o.total - o.authorization_amount as owing, m.company, m.firstname, m.lastname, d.actual_date, a.authorization_amount as authorized_amount, coalesce(p.authorization_amount,0) as paid from orders o left join order_authorization p on p.order_id = o.id and p.authorization_type='D' and p.authorization_success=1, members m, custom_delivery d, order_authorization a where %s", implode(" and ",array_values($srch))));
				$rows = array();
				foreach($recs as $k=>$v) {
					$v["limit"] = round($v["authorized_amount"]*1.15 -.01,2);
					$inner->addData($this->formatOrder($v));
					$rows[] = $inner->show();
				}
				$outer->setData("orders",implode("",$rows));
			}
		}
		if ($this->isAjax())
			return $this->ajaxReturn(array("status"=>"true","html"=>$outer->show()));
		else
			return $this->show($outer->show());
	}

    /**
     * @return false|string
     * @throws phpmailerException
     */
    function authHistory() {
		$outer = new Forms();
		$outer->init($this->getTemplate(__FUNCTION__));
		$inner = new Forms();
		$inner->init($this->getTemplate(__FUNCTION__."Row"));
		$flds = $outer->buildForm($this->getFields(__FUNCTION__));
		$flds = $inner->buildForm($this->getFields(__FUNCTION__."Row"));
		$outer->addData($_REQUEST);
		$outer->validate();
		$recs = $this->fetchAll(sprintf("select * from order_authorization where order_id = %d order by id", $outer->getData("o_id")));
		$rows = array();
		foreach($recs as $k=>$v) {
			$inner->addData($v);
			$rows[] = $inner->show();
		}
		$outer->setData("auth",implode("",$rows));
		return $this->ajaxReturn(array("html"=>$outer->show(),"status"=>true));
	}

    /**
     * @return false|string
     * @throws phpmailerException
     */
    function processNow() {
		$outer = new Forms();
		$outer->init($this->getTemplate(__FUNCTION__));
		$inner = new Forms();
		$inner->init($this->getTemplate(__FUNCTION__."Row"));
		$flds = $outer->buildForm($this->getFields(__FUNCTION__));
		$flds = $inner->buildForm($this->getFields(__FUNCTION__."Row"));
		$outer->addData($_REQUEST);
		$rows = array();
		if ($outer->validate()) {
			if (array_key_exists("process",$_REQUEST) && is_array($_REQUEST["process"])) {

				$snoopy = new Snoopy();
				if (array_key_exists('payflow',$GLOBALS)) {
					$parms = $GLOBALS['payflow'];
					$query = array(
						'PARTNER'=>$parms['partner'],
						'VENDOR'=>$parms['vendor'],
						'USER'=>$parms['user'],
						'PWD'=>$parms['pwd'],
						'AMT'=>'0.00',
						'CURRENCY'=>$parms["currency"]
					);
					$snoopy->host = $parms['auth'];
					$snoopy->port = 443;
					$snoopy->httpmethod = 'POST';
					$snoopy->curl_path = $parms['curl_path'];
				}

				foreach($_REQUEST["process"] as $k=>$v) {
					if ($order = $this->fetchSingle(sprintf("select o.total, o.authorization_amount as paid, o.total - o.authorization_amount as balance, o.nags, o.order_status, a.* from orders o, order_authorization a where o.id = %d and a.order_id = o.id and a.authorization_type='A' and a.authorization_success = 1", $k))) {
						$query["TRXTYPE"] = "D";
						$query["TENDER"] = "C";
						$query["ORIGID"] = $order["authorization_transaction"];
						$query["AMT"] = min($order["balance"], round($order["authorization_amount"]*1.15 -.01,2));
						$snoopy->submit($parms['auth'],$query);
						$result = $this->depairOptions(urldecode($snoopy->results),array('&','='));
						$this->logMessage(__FUNCTION__ ,sprintf("results [%s]", print_r($result,true)), 2);
						$auth = array(
							"order_id"=>$k,
							"authorization_date" => date(DATE_ATOM),
							"authorization_type" => "D",
							"authorization_amount" => $query["AMT"],
							"authorization_info" => print_r($result,true),
							"authorization_code" => array_key_exists("PPREF",$result) ? $result["PPREF"]:"",
							"authorization_transaction" => array_key_exists("PNREF",$result) ? $result["PNREF"] : "",
							"authorization_message" => $result["RESPMSG"],
							"authorization_success" => $result["RESULT"] == 0
						);
						$stmt = $this->prepare(sprintf("insert into order_authorization(%s) values(?%s)", implode(", ", array_keys($auth)), str_repeat(", ?", count($auth)-1)));
						$stmt->bindParams(array_merge(array(str_repeat("s",count($auth))),array_values($auth)));
						$stmt->execute();
						if ($result["RESULT"] == 0) {
							$this->execute(sprintf("update orders set authorization_amount =%f, order_status = %d where id = %d", $query["AMT"], $order["order_status"] & ~(STATUS_CREDIT_HOLD | STATUS_TO_BE_BILLED), $k));
						}
						else {
							$this->execute(sprintf("update orders set nags = %d, order_status = %d where id = %d", $order["nags"]+1, $order["order_status"] | ($order["nags"] > 4 ? STATUS_CREDIT_HOLD : 0), $k));
						}
						$order["result"] = $result;
						$order["transaction"] = $query;
					}
					else {
						$order = array("id"=>$k,"status"=>"n/a");
					}
					$inner->addData($order);
					$rows[] = $inner->show();
				}
			}
			$outer->setData("auth",implode("",$rows));
		}
		return $this->ajaxReturn(array("html"=>$outer->show(),"status"=>true));
	}

    /**
     * @return false|string
     * @throws phpmailerException
     */
    function removeSchedule() {
		$outer = new Forms();
		$outer->init($this->getTemplate(__FUNCTION__));
		$flds = $outer->buildForm($this->getFields(__FUNCTION__));
		$valid = false;
		if (array_key_exists(__FUNCTION__,$_REQUEST) && $_REQUEST[__FUNCTION__] > 0) {
			$outer->addData($_REQUEST);
			if (($s_id = $outer->getData("s_id")) > 0) {
				if ($rec = $this->fetchSingle(sprintf("select * from order_processing where id = %d", $s_id))) {
					if ($rec["started"] != "0000-00-00 00:00:00") {
						$outer->addFormError("This date has already been processed");
					}
					else {
						$this->execute(sprintf("delete from order_processing where id = %d", $s_id));
						$outer->addFormSuccess("Record deleted");
						$outer->setData("success",1);
						$valid = true;
					}
				}
				else {
					$outer->addFormError("We could'nt locate that record");
				}
			}
		}
		return $this->ajaxReturn(array("status"=>$valid,"html"=>$outer->show()));
	}

    /**
     * @return array|false|mixed|string|string[]|void
     * @throws phpmailerException
     */
    function editInvoices() {
		$outer = new Forms();
		$outer->init($this->getTemplate(__FUNCTION__));
		$srchFlds = $outer->buildForm($this->getFields(__FUNCTION__));
		$inner = new Forms();
		$inner->init($this->getTemplate(__FUNCTION__."Row"));
		$flds = $inner->buildForm($this->getFields(__FUNCTION__."Row"));
		if (array_key_exists(__FUNCTION__,$_REQUEST)) {
			$outer->addData($_REQUEST);
			if ($valid = $outer->validate()) {
				$srch = array("a"=>"m.id = i.member_id");
				foreach($srchFlds as $k=>$v) {
					switch($k) {
					case "member_id":
						if (0 != (int)($val = $outer->getData($k)))
							$srch[$k] = sprintf("member_id = %d",$val);
						break;
					case "start_date":
						if ("" != ($val = $outer->getData($k)))
							$srch[$k] = sprintf("invoice_date >= '%s'",$val);
						break;
					case "end_date":
						if ("" != ($val = $outer->getData($k)))
							$srch[$k] = sprintf("invoice_date <= '%s'",$val);
						break;
					case "qb_invoice_id":
						if (0 != (int)($val = $outer->getData($k)))
							$srch[$k] = sprintf("i.qb_invoice_id = %d", $val);
						break;
					case "order_id":
						if (0 != (int)($val = $outer->getData($k))) {
								$srch = array("a"=>"m.id = i.member_id");	// order id is a definitive search result
							$srch[$k] = sprintf("i.id in (select qb_export_id from qb_export_dtl where order_id = %d)", $val);
						}
						break;
					default:
						break;
					}
				}
				$this->logMessage(__FUNCTION__,sprintf("srch [%s]", print_r($srch,true)),1);
				if (count($srch) > 1) {
					$sql = sprintf("select i.*, m.company, m.firstname, m.lastname from qb_export i, members m where %s", implode(" and ", $srch));

					$pageNum = $outer->getData("pagenum");
					$perPage = $outer->getData("pager");
					if ($perPage <= 0) $perPage = $this->m_perrow;
					$count = $this->fetchScalar(sprintf("select count(0) from qb_export i, members m where %s", implode(" and ", $srch)));
					$pageNum = max(1,min($pageNum, (floor(($count-1)/$perPage)+1)));
					$pagination = $this->pagination($count, $perPage, $pageNum);
					$start = ($pageNum-1)*$perPage;
					$sortby = $outer->getData('sortby');
					$sortorder = $outer->getData('sortorder');
					$recs = $this->fetchAll(sprintf("%s order by %s %s limit %d,%d", $sql, $sortby, $sortorder, $start, $perPage));
					$outer->setData("count",$count);
					$rows = array();
					$ct = 0;
					$tot = 0;
					foreach($recs as $k=>$v) {
						$v["order_count"] = $this->fetchScalar(sprintf("select count(0) from qb_export_dtl where qb_export_id = %d", $v["id"]));
						$v["total"] = $this->fetchScalar(sprintf("select sum(o.total) from orders o, qb_export_dtl qb where qb.qb_export_id = %d and o.id = qb.order_id", $v["id"]));
						$ct += $v["order_count"];
						$tot += $v["total"];
						$inner->addData($v);
						$rows[] = $inner->show();
					}
					$outer->setData("pageCount",$ct);
					$outer->setData("pageTotal",$tot);
					$outer->setData("rows",implode("",$rows));
					$outer->setData("pagination",$pagination,false);
				}
			}
		}
		if ($this->isAjax()) {
			return $this->ajaxReturn(array('status'=>true,'html'=>$outer->show()));
		}
		else
			return $this->show($outer->show());
	}

    /**
     * @return array|false|mixed|string|string[]|void
     * @throws phpmailerException
     */
    function getInvoice() {
		$outer = new Forms();
		$outer->init($this->getTemplate(__FUNCTION__));
		$srchFlds = $outer->buildForm($this->getFields(__FUNCTION__));
		$inner = new Forms();
		$inner->init($this->getTemplate(__FUNCTION__."Row"));
		$flds = $inner->buildForm($this->getFields(__FUNCTION__."Row"));
		$i_id = array_key_exists("i_id",$_REQUEST) ? $_REQUEST["i_id"] : 0;
		$outer->addData($this->fetchSingle(sprintf("select *, member_id as parent_id from qb_export where id = %d", $i_id)));
		$recs = $this->fetchAll(sprintf("select d.*, i.member_id as p_id from qb_export_dtl d, qb_export i where i.id = %d and d.qb_export_id = i.id order by order_id", $i_id));
		if (array_key_exists(__FUNCTION__,$_REQUEST)) {
			$m_id = $outer->getData("parent_id");
			$this->beginTransaction();
			$status = true;
			if (array_key_exists("delete",$_REQUEST) && $_REQUEST["delete"]==1) {
				$orders = $this->fetchScalarAll(sprintf("select order_id from qb_export_dtl where qb_export_id = %d", $i_id));
				foreach($orders as $k=>$v) {
					$status &= $this->execute(sprintf("update orders set custom_qb_order = 0, authorization_amount = %f where id = %d", $this->fetchScalar(sprintf("select sum(authorization_amount) from order_authorization where order_id = %d and authorization_type='D' and authorization_success=1", $v)),$v));
				}
				//$status &= $this->execute(sprintf("update orders set custom_qb_order = 0 where id in (select order_id from qb_export_dtl where qb_export_id = %d)", $i_id));
				$status &= $this->execute(sprintf("delete from qb_export_dtl where qb_export_id = %d", $i_id));
				$status &= $this->execute(sprintf("delete from qb_export where id = %d", $i_id));
				$outer->addData($this->fetchSingle(sprintf("select *, member_id as parent_id from qb_export where id = %d", $i_id)));
				if ($status) $outer->addFormSuccess("Removed the invoice");
			}
			else {
				if ($m_id != $_REQUEST["parent_id"]) {
					$status &= $this->execute(sprintf("update qb_export set member_id=%d where id = %d", $_REQUEST["parent_id"], $i_id));
					$status &= $this->execute(sprintf("update orders set member_id = %d where id in (select order_id from qb_export_dtl where qb_export_id = %d)", $_REQUEST["parent_id"], $i_id));
					$outer->addData($this->fetchSingle(sprintf("select *, member_id as parent_id from qb_export where id = %d", $i_id)));
					if ($status) $outer->addFormSuccess(sprintf("Changed invoice and orders to %s",$this->fetchScalar(sprintf("select company from members where id = %d", $_REQUEST["parent_id"]))));
				}
				else {
					foreach($_REQUEST["member_id"] as $k=>$v) {
						if ($v != $m_id) {
							//
							//	Changing the owner of an order. Remove it from the invoice also
							//
							$rec = $this->fetchSingle(sprintf("select * from qb_export_dtl where id = %d",$k));
							$status &= $this->execute(sprintf("update orders set member_id=%d, custom_qb_order=0, authorization_amount = %f where id = %d", $v, 
								$this->fetchScalar(sprintf("select sum(authorization_amount) from order_authorization where order_id = %d and authorization_type='D' and authorization_success=1", $rec["order_id"])), $rec["order_id"]));
							$status &= $this->execute(sprintf("delete from qb_export_dtl where id=%d and order_id = %d", $k, $rec["order_id"]));
							if ($status) $outer->addFormSuccess(sprintf("Reassign and remove order #%s",$rec["order_id"]));
						}
					}
					if (array_key_exists("remove",$_REQUEST)) {
						foreach($_REQUEST["remove"] as $k=>$v) {
							if ($v == 1) {
								$rec = $this->fetchSingle(sprintf("select * from qb_export_dtl where id = %d",$k));
								//$status = $this->execute(sprintf("update orders set custom_qb_order=0 where id = %d", $rec["order_id"]));
								$status &= $this->execute(sprintf("update orders set custom_qb_order = 0, authorization_amount = %f where id = %d", $this->fetchScalar(sprintf("select sum(authorization_amount) from order_authorization where order_id = %d and authorization_type='D' and authorization_success=1", $rec["order_id"])),$rec["order_id"]));
								$status &= $this->execute(sprintf("delete from qb_export_dtl where id = %d and order_id = %d",$rec["id"],$rec["order_id"]));
								if ($status) $outer->addFormSuccess(sprintf("Removed order #%s from invoice",$rec["order_id"]));
							}
						}
					}
				}
			}
			if ($status)
				$this->commitTransaction();
			else {
				$this->rollbackTransaction();
				$outer->addFormError(implode('<br/>',$GLOBALS['globals']->getErrors()));
			}
			$recs = $this->fetchAll(sprintf("select d.*, i.member_id as p_id from qb_export_dtl d, qb_export i where i.id = %d and d.qb_export_id = i.id order by order_id", $i_id));
		}
		$rows = array();
		foreach($recs as $k=>$v) {
			$inner->addData($v);
			$rows[] = $inner->show();
		}
		$outer->setData("rows",implode("",$rows));
		if ($this->isAjax()) {
			return $this->ajaxReturn(array('status'=>true,'html'=>$outer->show()));
		}
		else
			return $this->show($outer->show());
	}

    /**
     * @param $db_id
     * @param $consolidated
     * @return void
     * @throws phpmailerException
     */
    function emailInvoice($db_id, $consolidated = 0) {
		$inv = $this->fetchSingle(sprintf("select * from qb_export where id = %d", $db_id));
		if ($consolidated) {
			$member = $this->fetchSingle(sprintf("select * from members where id = %d", $inv["consolidate_id"]));
			$url = sprintf("https://%s/consolidated-invoice?d_id=%s&r_id=%d", HOSTNAME, $inv["invoice_date"], $inv["rand_id"] );
		}
		else {
			$member = $this->fetchSingle(sprintf("select * from members where id = %d", $inv["member_id"]));
			$url = sprintf("https://%s/invoice-pdf?i_id=%d&r_id=%d", HOSTNAME, $db_id, $inv["rand_id"] );
		}
		$mailer = new MyMailer();
		$mailer->Subject = sprintf("Invoice Generated - %s", SITENAME);
		$body = new Forms();
		$flds = array("invoice_date"=>array("type"=>"datestamp"),"invoice_total"=>array("type"=>"currency"));
		$flds = $body->buildForm($flds);
		$sql = sprintf('select * from htmlForms where class = %d and type = "invoiceEmail"',$this->getClassId('custom'));
		$html = $this->fetchSingle($sql);
		$body->setHTML($html['html']);
		$inv_rec = $this->fetchSingle(sprintf("select *, invoice_amount+tax_amount as invoice_total  from qb_export where id = %d",$db_id));
		$inv_rec["user"] = $member;
		if (!$address = $this->fetchSingle(sprintf("select * from addresses where ownertype='member' and ownerid = %d", $inv_rec["user"]["id"]))) {
			$address = array("id"=>0,"country_id"=>0,"province_id"=>0);
			$this->logMessage(__FUNCTION__,sprintf("No member address for company [%s] id [%d]", $member["company"], $inv_rec["user"]["id"]),1,true,false);
		}
		$inv_rec["address"] = Address::formatData($address);
		$body->addData($inv_rec);
		$body->setOption('formDelimiter','{{|}}');
		$mailer->Body = $body->show();
		$mailer->From = "noreply@".HOSTNAME;
		$mailer->FromName = "KJV Courier Services";
		if (defined("DEV") && DEV==1) {
			$mailer->addAddress("ian@ingagedigital.com","Ian MacArthur");
/*
			if (strlen($member["custom_invoice_email"])>0) {
				$tmp = explode(";",$member["custom_invoice_email"]);
				$this->logMessage(__FUNCTION__,sprintf("parsed [%s] into [%s]", $member["custom_invoice_email"], print_r($tmp,true)),1);
				foreach($tmp as $e1=>$e2) {
					$mailer->addAddress($e2);
					$this->logMessage(__FUNCTION__,sprintf("sending to [%s]", $e2),1);
				}
			}
*/
			$mailer->ConfirmReadingTo = "ian@ingagedigital.com";
		}
		else {
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
		$fn = tempnam( sys_get_temp_dir(), "inv");
		$fh = fopen( $fn, "w");
		$this->logMessage(__FUNCTION__,sprintf("file [%s] exists [%s] [%s]", $fn, file_exists($fn), print_r(lstat($fn),true)),1);
		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_FILE, $fh);
		curl_setopt($ch, CURLOPT_TIMEOUT, 60000);
		curl_exec($ch);
		$statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		fclose($fh);
		if(curl_errno($ch)) {
			$this->logMessage(__FUNCTION__,sprintf("curl failed [%s], [%s]", curl_errno($ch), $url), 1, true, 1);
			curl_close($ch);
			return;
		}
		curl_close($ch);
		$this->logMessage(__FUNCTION__,sprintf("file [%s] exists [%s] [%s] url [%s]", $fn, file_exists($fn), file_get_contents($fn), $url),1);
		if ($consolidated) {
			$mailer->AddAttachment($fn,sprintf("invoice-%dc.pdf",$inv["qb_invoice_id"]));
			$mailer->Subject = sprintf("%s - KJV Courier Invoice #%dC", $member["company"], $inv["qb_invoice_id"]);
		}
		else {
			$mailer->AddAttachment($fn,sprintf("invoice-%d.pdf",$inv["qb_invoice_id"]));
			$mailer->Subject = sprintf("%s - KJV Courier Invoice #%d", $member["company"], $inv["qb_invoice_id"]);
		}
		if (!$mailer->Send())
			$this->logMessage(__FUNCTION__,sprintf("mailer [%s] [%s]", print_r($mailer->ErrorInfo,true), print_r($mailer,true)),1,true,true);
		unlink($fn);
	}

    /**
     * @return false|string
     * @throws \QuickBooksOnline\API\Exception\IdsException
     * @throws \QuickBooksOnline\API\Exception\SdkException
     * @throws phpmailerException
     */
    function consolidatedInvoices() {
		$outer = new Forms();
		$outer->init($this->getTemplate(__FUNCTION__));
		$flds = $outer->buildForm($this->getFields(__FUNCTION__));
		$inner = new Forms();
		$inner->init($this->getTemplate(__FUNCTION__."Row"));
		$flds = $outer->buildForm($this->getFields(__FUNCTION__."Row"));
		if (array_key_exists(__FUNCTION__,$_REQUEST)) {
			$dataService = DataService::Configure(array(
				'auth_mode' => 'oauth2',
				'ClientID' => $GLOBALS["quickbooks"]["client_id"],
				'ClientSecret' => $GLOBALS["quickbooks"]["client_secret"],
				'accessTokenKey' => $_SESSION["administrator"]["qb"]["accessToken"],
				'refreshTokenKey' => $_SESSION["administrator"]["qb"]["refreshToken"],
				'QBORealmID' => $_SESSION["administrator"]["qb"]["realmId"],
				'baseUrl' => $GLOBALS["quickbooks"]["baseUrl"]	//"Development"
			));
			$dataService->setLogLocation(sprintf("qb-%s.log",date("Y-m-d")));
			$outer->addData($_REQUEST);
			if ($outer->validate()) {
				$dt = $outer->getData("cutoff_date");
				$recs = $this->fetchAll(sprintf("select min(i.id) as id, i.rand_id, invoice_date, i.qb_invoice_id, m.company, consolidate_id, m.custom_qb_id from qb_export i, members m where i.invoice_date = '%s' and i.consolidate_id < 0 and m.id = -i.consolidate_id group by i.consolidate_id order by min(i.id)", $dt));
				$rows = array();
				foreach($recs as $k=>$v) {
					$o = $this->fetchAll(sprintf("select sum(o.net) as value, custom_qb_order, m.company, t.custom_qb_id as qb_tax_id, t.name, sum(o.taxes) as taxes
from orders o left join order_taxes ot on ot.order_id = o.id and ot.line_id = 0 and ot.tax_amount > 0 left join taxes t on t.id = ot.tax_id, members m where o.custom_qb_order in (select qe.qb_invoice_id from qb_export qe where invoice_date = '%s' and consolidate_id = %d)
and m.id = o.member_id
and o.deleted = 0 
group by custom_qb_order", $dt, $v["consolidate_id"]));
					$dtls = array();
					foreach($o as $sk=>$sv) {
$this->logMessage(__FUNCTION__,sprintf("^^^ sv [%s]", print_r($sv,true)),1);
						$dtls[] = Line::create([
							"Id"=>$sk,
							"LineNum"=>$sk,
							"Description"=>sprintf("Invoice #%d (%s)", $sv["custom_qb_order"], $sv["company"]),
							"Amount"=>$sv["value"],
							"DetailType" =>"SalesItemLineDetail",
							"SalesItemLineDetail"=> [
								"ItemRef"=> [
									"value"=>QB_SERVICES_ID,
									"name"=>$sv["company"]
								],
								"UnitPrice"=>$sv["value"],
								"Qty"=>1,
								"TaxCodeRef"=>[
									"value"=> (float)$sv["taxes"] >= .01 ? $sv["qb_tax_id"] : QB_TAX_EXEMPT
								]
							]
						]);
					}
					$this->logMessage(__FUNCTION__,sprintf("^^^ dtls [%s]", print_r($dtls,true)),1);
					$myInvoice = Invoice::create([
						"Line"=>$dtls,
						"DocNumber"=>$v["qb_invoice_id"]."C",
						"TxnDate"=>$dt,
						"CustomerRef"=> [
							"value"=> defined("DEV") && DEV ? QB_TEST_ACCOUNT : $v["custom_qb_id"]
						]
					]);
					$response = $dataService->Add($myInvoice);
					$error = $dataService->getLastError();
					if ($error != null) {
						$outer->addFormError("The Status code is: " . $error->getHttpStatusCode());
						$outer->addFormError("The Helper message is: " . $error->getOAuthHelperError());
						$outer->addFormError("The Response message is: " . $error->getResponseBody());
						$v["hasError"] = true;
						$this->logMessage(__FUNCTION__,sprintf("invoice [%s] error [%s] dataService [%s] response [%s]", print_r($myInvoice,true), 
							print_r($error,true), print_r($dataService,true), print_r($response,true)),1,true);
					}
					else {
						if (!$this->execute(sprintf("update qb_export set consolidate_id = -consolidate_id where consolidate_id = %d and invoice_date = '%s'", $v["consolidate_id"], $dt))) {
							$outer->addFormError($this->showMessages());
							$v["hasError"] = true;
						}
						else
							$this->emailInvoice($v["id"], 1);
					}
					$inner->addData($v);
					$rows[] = $inner->show();
				}
				$outer->setData("rows",implode("",$rows));
			}
		}
		return $this->ajaxReturn(array("status"=>true,"html"=>$outer->show()));
	}

    /**
     * @param $form
     * @return mixed
     * @throws phpmailerException
     */
    function consolidatedExists($form ) {
		$ct = $this->fetchScalarAll(sprintf("select distinct invoice_date from qb_export where consolidate_id < 0 group by consolidate_id"));
		if ((int)$ct > 0) {
			$form->addFormError(sprintf("Unprocessed consolidated invoices exist (%s)",implode(", ",$ct)));
		}
		return $form;
	}
}

?>