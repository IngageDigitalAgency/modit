<?php
require "./classes/KJV.php";

class orders extends Backend {

	private $m_content = 'orders';
	private $m_perrow = 10;


	public function __construct() {
		$this->m_perrow = defined('GLOBAL_PER_PAGE') ? GLOBAL_PER_PAGE : 5;
		$this->M_DIR = 'backend/modules/orders/';
		$this->setTemplates(
			array(
				'main'=>$this->M_DIR.'orders.html',
				'articleList'=>$this->M_DIR.'forms/articleList.html',
				'addContent'=>$this->M_DIR.'forms/addContent.html',
				'showSearchForm'=>$this->M_DIR.'forms/searchForm.html',
				'editLine'=>$this->M_DIR.'forms/editLine.html',
				'orderLine'=>$this->M_DIR.'forms/orderLine.html',
				'orderDims'=>$this->M_DIR.'forms/orderDims.html',
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
				'pickup'=>$this->M_DIR.'forms/pickup.html',
				'delivery'=>$this->M_DIR.'forms/delivery.html',
				'getAllocations'=>$this->M_DIR.'forms/allocations.html',
				'oneTime'=>$this->M_DIR.'forms/oneTime.html',
				'oneTimeRow'=>$this->M_DIR.'forms/articleList.html',
				'onAccount'=>$this->M_DIR.'forms/onAccount.html',
				'onAccountRow'=>$this->M_DIR.'forms/articleList.html',
				'getCommissions'=>$this->M_DIR.'forms/getCommissions.html',
				'recalcFuel'=>$this->M_DIR.'forms/recalcFuel.html',
				'getPayments'=>$this->M_DIR.'forms/getPayments.html',
				'getPaymentsRow'=>$this->M_DIR.'forms/getPaymentsRow.html',
				'weightDims'=>$this->M_DIR.'forms/weightDims.html',
				'weightDimsRow'=>$this->M_DIR.'forms/weightDimsRow.html',
				'addDims'=>$this->M_DIR.'forms/addDims.html',
				'recalcFedex'=>$this->M_DIR.'forms/recalcFedex.html',
				'onDemand'=>$this->M_DIR.'forms/onDemand.html',
				'onDemandRow'=>$this->M_DIR.'forms/onDemandRow.html',
				'onDemandCompany'=>$this->M_DIR.'forms/onDemandCompany.html',
				'importOrders'=>$this->M_DIR.'forms/importOrders.html',
				'importOrdersRow'=>$this->M_DIR.'forms/importOrdersRow.html',
				'addImport'=>$this->M_DIR.'forms/addImport.html',
				'addImportRow'=>$this->M_DIR.'forms/addImportRow.html',
				'deleteImport'=>$this->M_DIR.'forms/deleteImport.html'
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
				'options'=>array('method'=>'post','action'=>'/modit/ajax/editAddress/orders'),
				'editAddress'=>array('type'=>'hidden','value'=>1,'database'=>false),
				'addresstype'=>array('type'=>'select','required'=>true,'sql'=>'select id, value from code_lookups where type = "memberAddressTypes"','prettyName'=>'Address Type'),
				'ownertype'=>array('type'=>'hidden','value'=>'order'),
				'ownerid'=>array('type'=>'hidden','id'=>'ownerid'),
				'company'=>array('type'=>'input','required'=>false,'prettyName'=>'Company'),
				'firstname'=>array('type'=>'input','required'=>false,'prettyName'=>'First Name'),
				'lastname'=>array('type'=>'input','required'=>false,'prettyName'=>'Last Name'),
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
				'addressList'=>array('type'=>'select','sql'=>'select a.id, concat(company,", ", line1, ", ",city) from addresses a, orders o where ownertype="member" and ownerid = o.member_id and o.id = %%ownerid%% order by company','required'=>false,'database'=>false,'onchange'=>'cloneAddress(this);return false;'),
				'save'=>array('type'=>'submitbutton','database'=>false,'value'=>'Save Address')
			),
			'editLine'=>array(
				'options'=>array('name'=>'pFormEdit','database'=>false),
				'product_id'=>array('type'=>'productOptGroup','required'=>true,'sql'=>'select id, concat(code," - ",name) from product where deleted = 0 order by code'),
				'coupon_id'=>array('type'=>'select','required'=>false,'sql'=>'select id, concat(code," - ",name) from coupons where deleted = 0 order by code','id'=>'editCouponId'),
				'quantity'=>array('type'=>'input','required'=>true,'validation'=>'number','prettyName'=>'Ordered'),
				'shipped'=>array('type'=>'input','required'=>false,'validation'=>'number','prettyName'=>'Shipped'),
				'deleted'=>array('type'=>'checkbox','value'=>1),
				'price'=>array('type'=>'input','required'=>true,'validation'=>'number','prettyName'=>'Price','value'=>'0.00'),
				'discount_type'=>array('type'=>'select','required'=>false,'lookup'=>'discountTypes'),
				'discount_rate'=>array('type'=>'input','required'=>false,'validation'=>'number','prettyName'=>'Discount Rate'),
				'discount_value'=>array('type'=>'input','required'=>false,'validation'=>'number','prettyName'=>'Discount Amount'),
				'fedex_package_type'=>array('type'=>'select','sql'=>'select id, name from product where enabled and !deleted and fedex_package_type != "" order by name'),
				'recurring_period'=>array('type'=>'select','name'=>'recurring_period','required'=>false,'sql'=>'select id,teaser from product_recurring where product_id = %%product_id%%'),
				'recurring_discount_type'=>array('type'=>'select','required'=>false,'lookup'=>'discountTypes'),
				'recurring_discount_rate'=>array('type'=>'input','required'=>false,'validation'=>'number','prettyName'=>'Recurring Discount Rate'),
				'recurring_discount_value'=>array('type'=>'input','required'=>false,'validation'=>'number','prettyName'=>'Recurring Discount Amount'),
				'recurring_qty'=>array('type'=>'input','required'=>false,'validation'=>'number','prettyName'=>'Recurring Quantity'),
				'qty_multiplier'=>array('type'=>'input','required'=>true,'value'=>1,'prettyName'=>'Options Multiplier'),
				'qty_multiplierHidden'=>array('type'=>'hidden','value'=>1,'database'=>false),

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
				'options_id'=>array('type'=>'select','required'=>false),
				'color'=>array('type'=>'select','required'=>false),
				'size'=>array('type'=>'select','required'=>false),
				'custom_package'=>array('type'=>'select','required'=>true,'options'=>array('A'=>'Additional Charge','P'=>'Package Type','S'=>'Service Type'))
			),
			'orderLine'=>array(),
			'orderDims'=>array(),
			'articleList' => array(
				'id'=>array('type'=>'tag'),
				'order_date'=>array('type'=>'timestamp'),
				'amount'=>array('type'=>'currency'),
				'owing'=>array('type'=>'currency'),
				'deleted'=>array('type'=>'booleanIcon'),
				'options_id'=>array('type'=>'tag')
			),
			'addContent'=>array(
				'options'=>array('name'=>'addContent','action'=>'/modit/ajax/addContent/orders','database'=>false),
				'id'=>array('type'=>'tag','database'=>false),
				'member_id'=>array('type'=>'select','required'=>true,'sql'=>'select id, concat(if(company="","",concat(company,": ")),lastname,", ",firstname) from members where id = %%member_id%% order by company,lastname, firstname'),
				'order_date'=>array('type'=>'datetimepicker','required'=>true,'AMPM'=>'AMPM','validation'=>'datetime','prettyName'=>'Order Date'),
				'coupon_id'=>array('type'=>'select','required'=>false,'sql'=>'select id, concat(code," - ",name) from coupons where deleted = 0 order by code','id'=>'editCouponId'),
				'value'=>array('type'=>'tag'),
				'handling'=>array('type'=>'textfield','required'=>true,'value'=>0.00,'validation'=>'number'),
				'authorization_info'=>array('type'=>'textarea','reformat'=>true,'class'=>'mceNoEditor'),
				'authorization_amount'=>array('type'=>'input','required'=>true,'validation'=>'number','prettyName'=>'Authorization Amount'),
				'authorization_amount_ro'=>array('type'=>'tag','required'=>true,'database'=>false),
				'authorization_code'=>array('type'=>'input','prettyName'=>'Authorization Code'),
				'authorization_transaction'=>array('type'=>'input','required'=>false,'prettyName'=>'Transaction Code'),
				'deleted'=>array('type'=>'checkbox','value'=>1),
				'submit'=>array('type'=>'submitbutton','value'=>'Save Order','database'=>false),
				'recalc'=>array('type'=>'submitbutton','value'=>'Save & Recalculate','database'=>false),
				'tempEdit'=>array('type'=>'hidden','value'=>0,'database'=>false),
				'fldName'=>array('type'=>'hidden','value'=>'','database'=>false),
				'discount_rate'=>array('type'=>'input','required'=>false,'validation'=>'number','readonly'=>'readonly','prettyName'=>'Discount Rate'),
				'discount_type'=>array('type'=>'tag'),
				'addContent'=>array('type'=>'hidden','value'=>1,'database'=>false),
				'orderTotal'=>array('type'=>'tag','database'=>false),
				'ship_via'=>array('type'=>'select','lookup'=>'ship_via'),
				'ship_date'=>array('type'=>'datepicker'),
				'ship_tracking_code'=>array('type'=>'input'),
				'ship_comments'=>array('type'=>'textarea','class'=>'mceSimple'),
				'order_status'=>array('type'=>'select','multiple'=>true,'lookup'=>'orderStatus','database'=>false,'id'=>'order_status'),
				'currency_id'=>array('type'=>'select','required'=>false,'idlookup'=>'currencies'),
				'submitEmail'=>array('type'=>'checkbox','value'=>1,'name'=>'submitEmail','database'=>false),
				'notes'=>array('type'=>'textarea','required'=>false,'style'=>'min-width:100%;resize:none;'),
				'custom_commissionable_amt'=>array('type'=>'currency','value'=>0)
			),
			'showSearchForm'=>array(
				'options'=>array('action'=>'showSearchForm','name'=>'searchForm','id'=>'search_form'),
				'order_status'=>array('type'=>'select','name'=>'order_status','lookup'=>'orderStatus','multiple'=>true),
				'opt_created'=>array('type'=>'select','name'=>'opt_created','lookup'=>'search_options'),
				'created'=>array('type'=>'datepicker','required'=>false),
				'opt_name'=>array('type'=>'select','name'=>'opt_name','lookup'=>'search_string'),
				'name'=>array('type'=>'input','required'=>false),
				'opt_order_id'=>array('type'=>'select','lookup'=>'search_options'),
				'order_id'=>array('type'=>'input','required'=>false),
				'product'=>array('type'=>'select','required'=>false,'sql'=>'select id, concat(code," - ",name) from product where deleted = 0 order by code'),
				'status'=>array('type'=>'select','required'=>false,'lookup'=>'orderStatus'),
				'deleted'=>array('type'=>'select','lookup'=>'boolean'),
				'showSearchForm'=>array('type'=>'hidden','value'=>1),
				'pagenum'=>array('type'=>'hidden','value'=>1),
				'sortby'=>array('type'=>'hidden','value'=>'created'),
				'sortorder'=>array('type'=>'hidden','value'=>'desc'),
				'quicksearch'=>array('type'=>'input','name'=>'quicksearch','required'=>false),
				'opt_quicksearch'=>array('type'=>'hidden','value'=>'like'),
				'opt_owing'=>array('type'=>'select','lookup'=>'search_options'),
				'owing'=>array('type'=>'input','required'=>false,'validation'=>'number','prettyName'=>'Amount Owing'),
				'coupon'=>array('type'=>'select','required'=>false,'sql'=>'select id,concat(code," - ",name) from coupons where deleted = 0'),
				'opt_total'=>array('type'=>'select','lookup'=>'search_options'),
				'total'=>array('type'=>'input','required'=>false,'validation'=>'number','prettyName'=>'Total'),
				'custom_qb_order'=>array('type'=>'number','min'=>0,'class'=>'a-right def_field_input','required'=>false,'prettyName'=>'Quickbooks Invoice #'),
				'pager'=>array('type'=>'select','required'=>true,'value'=>$this->m_perrow,'lookup'=>'paging','id'=>'pager'),
				'pagenum'=>array('type'=>'hidden','value'=>1),
				'perpage'=>array('type'=>'hidden','value'=>$this->m_perrow,'name'=>'pager'),
				'pu_driver'=>array('type'=>'select','required'=>false,'sql'=>'select -1,"-Unassigned-" union select d.id, concat(m.company, " - ",m.lastname," ",m.firstname) from drivers d, members m where m.id = d.member_id and m.deleted = 0 and m.enabled = 1 and d.deleted = 0 and d.enabled = 1 order by 2'),
				'del_driver'=>array('type'=>'select','required'=>false,'sql'=>'select -1,"-Unassigned-" union select d.id, concat(m.company, " - ",m.lastname," ",m.firstname) from drivers d, members m where m.id = d.member_id and m.deleted = 0 and m.enabled = 1 and d.deleted = 0 and d.enabled = 1 order by 2'),
				'submit'=>array('type'=>'submitbutton','value'=>'Search')
			),
			'articleList' => array(
				'id'=>array('type'=>'tag'),
				'title'=>array('type'=>'tag'),
				'order_date'=>array('type'=>'datetimestamp','mask'=>'d-M H:i'),
				'deleted'=>array('type'=>'booleanIcon')
			),
			'recurringInfo'=>array(
				'delayShipping'=>array('type'=>'checkbox','value'=>1,'name'=>'delayShipping', 'database'=>false),
				'delayUntil'=>array('type'=>'datepicker','required'=>false, 'database'=>false),
				'getRecurring'=>array('type'=>'hidden','value'=>1,'database'=>false),
				'custom_recurring_pu_driver'=>array('type'=>'select','sql'=>'select d.id, concat(m.company, " - ",m.lastname," ",m.firstname) from drivers d, members m where m.id = d.member_id and m.deleted = 0 and m.enabled = 1 and d.deleted = 0 and d.enabled = 1 order by 2'),
				'custom_recurring_del_driver'=>array('type'=>'select','sql'=>'select d.id, concat(m.company, " - ",m.lastname," ",m.firstname) from drivers d, members m where m.id = d.member_id and m.deleted = 0 and m.enabled = 1 and d.deleted = 0 and d.enabled = 1 order by 2'),
				'custom_recurring_pu_time'=>array('type'=>'time','class'=>'form-control'),
				'route_id'=>array('type'=>'select','idlookup'=>'routes','required'=>false),
				'submit'=>array('type'=>'submitbutton','value'=>'Apply Changes','class'=>'btn btn-default form-control','onclick'=>'updateDrivers(this);return false;','database'=>false)
			),
			'recurringInfoRow' => array(
				'billing_date'=>array('type'=>'datestamp','mask'=>'d-M-Y'),
				'billed'=>array('type'=>'booleanIcon'),
				'billed_on'=>array('type'=>'datestamp')
			),
			'showRecurring' => array(
				'billing_date'=>array('type'=>'datestamp','mask'=>'d-M-Y'),
				'billed_on'=>array('type'=>'datestamp'),
				'authorization_info'=>array('type'=>'textarea')
			),
			'pickup'=>array(
				'driver_id'=>array('name'=>'pickup[driver_id]','type'=>'select','sql'=>'select d.id, concat(m.company, " - ",m.lastname,", ",m.firstname) from drivers d, members m where m.id = d.member_id order by 2','required'=>false,'disabled'=>'disabled'),
				'vehicle_id'=>array('name'=>'pickup[vehicle_id]','type'=>'select','sql'=>'select id,name from vehicles order by 2','disabled'=>'disabled'),
				'scheduled_date'=>array('type'=>'datetimepicker','required'=>true,"AMPM"=>true,'name'=>'pickup[scheduled_date]'),
				'actual_date'=>array('type'=>'datetimestamp','database'=>false),
				'completed'=>array('type'=>'booleanIcon','database'=>false),
				'percent_of_delivery'=>array('name'=>'pickup[percent_of_delivery]','type'=>'tag','validation'=>'number','required'=>true,'onchange'=>'calcPct(this);','database'=>false),
				'payment'=>array('type'=>'currency','name'=>'pickup[payment]','database'=>false),
				'p_id'=>array('name'=>'pickup[id]','type'=>'hidden','value'=>'%%id%%')
			),
			'delivery'=>array(
				'driver_id'=>array('name'=>'delivery[driver_id]','type'=>'select','sql'=>'select d.id, concat(m.company," - ",m.lastname,", ",m.firstname) from drivers d, members m where m.id = d.member_id order by 2','required'=>false,'disabled'=>'disabled'),
				'vehicle_id'=>array('type'=>'select','sql'=>'select id,name from vehicles order by 2','disabled'=>'disabled'),
				'scheduled_date'=>array('type'=>'datetimepicker','required'=>true,"AMPM"=>true,'name'=>'delivery[scheduled_date]'),
				'actual_date'=>array('type'=>'datetimestamp','database'=>false),
				'completed'=>array('type'=>'booleanIcon','database'=>false),
				'percent_of_delivery'=>array('name'=>'delivery[percent_of_delivery]','type'=>'tag','validation'=>'number','required'=>true,'onchange'=>'calcPct(this);','database'=>false),
				'payment'=>array('type'=>'currency','database'=>false),
				'custom_signature_required'=>array('type'=>'booleanIcon','required'=>false,'database'=>false),
				'd_id'=>array('name'=>'delivery[id]','type'=>'hidden','value'=>'%%id%%')
			),
			'getAllocations'=>array(
				'reference_date'=>array('type'=>'datetimestamp'),
				'amount'=>array('type'=>'currency')
			),
			'oneTime'=>array(
				'options'=>array('action'=>'onAccount','name'=>'onAccount','id'=>'oneTime'),
				'pager'=>array('type'=>'select','required'=>true,'value'=>$this->m_perrow,'lookup'=>'paging','id'=>'pager'),
				'pagenum'=>array('type'=>'hidden','value'=>1),
				'oneTime'=>array('type'=>'hidden','value'=>1)
			),
			'oneTimeRow'=>array(
				'owing'=>array('type'=>'currency'),
				'total'=>array('type'=>'currency'),
				'deleted'=>array('type'=>'booleanIcon')
			),
			'onAccount'=>array(
				'options'=>array('action'=>'onAccount','name'=>'onAccount','id'=>'onAccount'),
				'pager'=>array('type'=>'select','required'=>true,'value'=>$this->m_perrow,'lookup'=>'paging','id'=>'pager'),
				'pagenum'=>array('type'=>'hidden','value'=>1),
				'onAccount'=>array('type'=>'hidden','value'=>1)
			),
			'onAccountRow'=>array(
				'owing'=>array('type'=>'currency'),
				'total'=>array('type'=>'currency'),
				'deleted'=>array('type'=>'booleanIcon')
			),
			'getCommissions'=>array(
				'base_amount'=>array('type'=>'currency'),
				'calculated'=>array('type'=>'currency'),
				'overridden'=>array('type'=>'booleanIcon'),
				'value'=>array('type'=>'currency')
			),
			'recalcFuel'=>array(
			),
			'getPayments'=>array(
				'authorization_date'=>array('type'=>'datetimestamp'),
				'authorization_success'=>array('type'=>'booleanicon'),
				'authorization_amount'=>array('type'=>'currency')
			),
			'weightDims'=>array(
				'submit'=>array('type'=>'button','value'=>'Save','onclick'=>'updateDims(this);return false;','class'=>'btn btn-primary'),
				'add'=>array('type'=>'button','value'=>'Add','onclick'=>'addDims(this);return false;','class'=>'btn btn-primary'),
				'weightDims'=>array('type'=>'hidden','value'=>1),
				'l_id'=>array('type'=>'hidden','value'=>'%%request:l_id%%')
			),
			'weightDimsRow'=>array(
				'quantity'=>array('type'=>'number','required'=>true,'name'=>'d[%%id%%][quantity]','class'=>'def_field_input a-right'),
				'weight'=>array('type'=>'number','required'=>true,'step'=>.01,'name'=>'d[%%id%%][weight]','class'=>'def_field_input a-right'),
				'height'=>array('type'=>'number','required'=>true,'step'=>.01,'name'=>'d[%%id%%][height]','class'=>'def_field_input a-right'),
				'width'=>array('type'=>'number','required'=>true,'step'=>.01,'name'=>'d[%%id%%][width]','class'=>'def_field_input a-right'),
				'depth'=>array('type'=>'number','required'=>true,'step'=>.01,'name'=>'d[%%id%%][depth]','class'=>'def_field_input a-right'),
				'delete'=>array('type'=>'checkbox','value'=>1,'name'=>'delete[%%id%%]')
			),
			'addDims'=>array(
				'quantity'=>array('type'=>'number','required'=>true,'value'=>0,'name'=>'d[%%id%%][quantity]','class'=>'def_field_input a-right'),
				'weight'=>array('type'=>'number','required'=>true,'value'=>0,'step'=>.01,'name'=>'d[%%id%%][weight]','class'=>'def_field_input a-right'),
				'height'=>array('type'=>'number','required'=>true,'value'=>0,'step'=>.01,'name'=>'d[%%id%%][height]','class'=>'def_field_input a-right'),
				'width'=>array('type'=>'number','required'=>true,'value'=>0,'step'=>.01,'name'=>'d[%%id%%][width]','class'=>'def_field_input a-right'),
				'depth'=>array('type'=>'number','required'=>true,'value'=>0,'step'=>.01,'name'=>'d[%%id%%][depth]','class'=>'def_field_input a-right'),
				'delete'=>array('type'=>'checkbox','value'=>1,'name'=>'delete[%%id%%]')
			),
			'onDemand'=>array(
				'options'=>array('method'=>'post','action'=>'onDemand','name'=>'demandForm'),
				'hidden'=>array('type'=>'tag','value'=>'hidden'),
				'onDemand'=>array('type'=>'hidden','value'=>1),
				'route_id'=>array('type'=>'select','idlookup'=>'routes','required'=>false),
				'pu_driver'=>array('type'=>'select','sql'=>'select d.id, concat(m.company, " - ",m.lastname," ",m.firstname) from drivers d, members m where m.id = d.member_id and m.deleted = 0 and m.enabled = 1 and d.deleted = 0 and d.enabled = 1 order by 2','required'=>false),
				'del_driver'=>array('type'=>'select','sql'=>'select d.id, concat(m.company, " - ",m.lastname," ",m.firstname) from drivers d, members m where m.id = d.member_id and m.deleted = 0 and m.enabled = 1 and d.deleted = 0 and d.enabled = 1 order by 2','required'=>false),
				'dow'=>array('type'=>'select','lookup'=>'recurringWeekdays','required'=>true),
				'order_status'=>array('type'=>'select','options'=>array(STATUS_ON_DEMAND=>"On Demand", STATUS_HOLIDAY=>"Holiday"), 'required'=>true,'onchange'=>'setHoliday(this);return false;','value'=>STATUS_ON_DEMAND),
				'do_it'=>array('type'=>'checkbox','value'=>1,'class'=>''),
				'submit'=>array('type'=>'submitbutton','value'=>'Search','class'=>'form-control btn'),
				'pickup_time'=>array('type'=>'time','class'=>'form-control',"value"=>date("H:i")),
				'use_scheduled_time'=>array('type'=>'checkbox','value'=>1,'class'=>''),
				'pickup_date'=>array('type'=>'datefield','class'=>'form-control','value'=>date('Y-m-d')),
				'pager'=>array('type'=>'select','required'=>true,'value'=>$this->m_perrow,'lookup'=>'paging','id'=>'pager'),
				'pagenum'=>array('type'=>'hidden','value'=>1),
				'perpage'=>array('type'=>'hidden','value'=>$this->m_perrow,'name'=>'pager'),
				'member_id'=>array('type'=>'select','required'=>false),
				'sort_by'=>array('type'=>'select','required'=>true,'class'=>'form-control','options'=>array(
					'O'=>'Order #',
					'P'=>'Pickup Address',
					'PC'=>'Pickup City',
					'S'=>'Shipping Address',
					'SC'=>'Shipping City',
					'V'=>'Service',
					'Q'=>'Pickup Sequence',
					'D'=>'Delivery Sequence'
				))
			),
			'onDemandRow'=>array(
				'process'=>array('type'=>'checkbox','name'=>'process[%%id%%]','class'=>'form-control','value'=>1),
				'order_date'=>array('type'=>'datetimestamp')
			),
			'onDemandCompany'=>array(
				'member_id'=>array('type'=>'select','required'=>false)
			),
			'importOrders'=>array(
				'options'=>array('name'=>'importOrdersForm','action'=>'importOrders','database'=>false),
				'member_id'=>array('type'=>'select', 'sql'=>'select id, company from members where enabled = 1 and deleted = 0 order by company'),
				'created'=>array('type'=>'datepicker','value'=>date('Y-m-d'),'required'=>true,'class'=>'def_field_datepicker form-control'),
				'opt_created'=>array('type'=>'select','name'=>'opt_created','lookup'=>'search_options','value'=>'='),
				'processed'=>array('type'=>'select','lookup'=>'boolean','class'=>'form-control w100'),
				'validate_only'=>array('type'=>'select','lookup'=>'boolean','class'=>'form-control w100'),
				'submit'=>array('type'=>'submitbutton','value'=>'Search','class'=>'form-control btn'),
				'add'=>array('type'=>'button','value'=>'Add','class'=>'form-control btn','onclick'=>'addImport(0);return false;'),
				'importOrders'=>array('type'=>'hidden','value'=>1)
			),
			'importOrdersRow'=>array(
				'created'=>array('type'=>'datetimestamp'),
				'validate_only'=>array('type'=>'booleanIcon'),
				'processed'=>array('type'=>'booleanIcon')
			),
			'addImport'=>array(
				'member_id'=>array('type'=>'select','sql'=>'select id,company from members where enabled = 1 and deleted = 0 order by company','required'=>true),
				'attachment'=>array('type'=>'fileupload','database'=>false),
				'created'=>array('type'=>'datetimestamp','database'=>false),
				'processed'=>array('type'=>'checkbox','value'=>1),
				'validate_only'=>array('type'=>'checkbox','value'=>1),
				'addImport'=>array('type'=>'hidden','value'=>1,'database'=>false),
				'handling_fee'=>array('type'=>'productOptGroup','required'=>false,'sql'=>'select id, concat(code," - ",name) from product where deleted = 0 and enabled = 1 order by code','required'=>false),
				'i_id'=>array('type'=>'hidden','value'=>0,'database'=>false),
				'abort'=>array('type'=>'checkbox','value'=>1),
				'submit'=>array('type'=>'submitbutton','value'=>'Save','class'=>'form-control btn','database'=>false)
			),
			'addImportRow'=>array(
				'successful'=>array('type'=>'boolean'),
				'scheduled_date'=>array('type'=>'datetimestamp'),
				'total'=>array('type'=>'currency')
			),
			'deleteImport'=>array()
		));
	
		parent::__construct ();
	}
	
	function __destruct() {
	
	}

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

	function showContentTree() {
		return "";
	}

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
				array('url'=>'/modit/ajax/showFolderContent/orders','destination'=>'middleContent'));
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

	function showSearchForm($fromMain = false,$msg = '') {
		$form = new Forms();
		$form->init($this->getTemplate('showSearchForm'),array('name'=>'showSearchForm','persist'=>true));
		$frmFields = $form->buildForm($this->getFields('showSearchForm'));
		if (count($_POST) == 0)
			if (array_key_exists('formData',$_SESSION) && array_key_exists('orderSearchForm', $_SESSION['formData']))
				$_POST = $_SESSION['formData']['orderSearchForm'];
			else
				$_POST = array('deleted'=>0,'sortby'=>'created','sortorder'=>'desc','showSearchForm'=>1);
		if (count($_POST) > 0 && array_key_exists('showSearchForm',$_POST)) {
			$form->addData($_POST);
			if ($form->validate()) {
				$_SESSION['formData']['orderSearchForm'] = $form->getAllData();
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
								$tmp[] = sprintf(' concat(m.firstname," ",m.lastname) %s "%s"',$_POST['opt_quicksearch'],$this->escape_string($value));
								$tmp[] = sprintf(' o.id %s "%s"',$_POST['opt_quicksearch'],$this->escape_string($value));
								$tmp[] = sprintf(' m.company %s "%s"',$_POST['opt_quicksearch'],$this->escape_string($value));
								$srch[] = sprintf('(%s)',implode(' or ',$tmp));
								$frmFields = array();
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
						case 'total':
							if (array_key_exists('opt_'.$key,$_POST) && $_POST['opt_'.$key] != null && !is_null($value = $form->getData($key))) {
								if ($_POST['opt_'.$key] == 'like' && strpos($value,'%',0) === false) {
									$value = '%'.$value.'%';
								}
								$srch[] = sprintf(' o.%s %s %s',$key,$_POST['opt_'.$key],$this->escape_string($value));
							}
							break;
						case 'owing':
							if (array_key_exists('opt_'.$key,$_POST) && $_POST['opt_'.$key] != null && !is_null($value = $form->getData($key))) {
								if ($_POST['opt_'.$key] == 'like' && strpos($value,'%',0) === false) {
									$value = '%'.$value.'%';
								}
								$srch[] = sprintf(' (total - authorization_amount) %s %s',$_POST['opt_'.$key],$this->escape_string($value));
							}
							break;
						case 'name':
							if (array_key_exists('name',$_POST) && strlen($_POST['name']) > 0)
								$srch[] = sprintf('(m.firstname like "%%%1$s%%" or m.lastname like "%%%1$s%%" or m.company like "%%%1$s%%")',$_POST['name']);
							break;
						case 'product':
							if (array_key_exists('product',$_POST) && $_POST['product'] > 0)
								$srch[] = sprintf("o.id in (select order_id from order_lines where product_id = %d and deleted = 0)",$_POST['product']);
							break;
						case 'coupon':
							if (array_key_exists('coupon',$_POST) && $_POST['coupon'] > 0)
								$srch[] = sprintf("o.coupon_id = %d",$_POST['coupon']);
							break;
						case 'status':
							if (array_key_exists('status',$_POST) && $_POST['status'] > 0)
								$srch[] = sprintf("o.status = %d",$_POST['status'],$_POST['status']);
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
						case 'deleted':
							if (!is_null($value = $form->getData($key)))
								if (strlen($value) > 0)
									$srch[] = sprintf(' o.%s = %s',$key,$this->escape_string($value));
							break;
						case 'order_status':
							if (array_key_exists("order_status",$_POST) && is_array($_POST["order_status"]) && count($_POST["order_status"]) > 0) {
								$tmp = 0;
								foreach($_POST["order_status"] as $sk=>$sv) {
									$tmp |= $sv;
								}
								$srch[] = sprintf(' ((o.order_status & %d) = %d)',$tmp,$tmp);
							}
							else {
								if (($value = $form->getData($key)) > 0) {
									$srch[] = sprintf(' ((o.order_status & %d) = %d)',$value,$value);
								}
							}
							break;
						case 'custom_qb_order':
							if (!is_null($value = $form->getData($key)))
								if (strlen($value) > 0)
									$srch[] = sprintf(' o.%s = %s',$key,$this->escape_string($value));
							break;
						case 'pu_driver':
							$value = $form->getData($key);
							if ($value < 0) {
								$srch[] = sprintf(" exists (select 1 from custom_delivery cd where cd.driver_id = 0 and cd.order_id = o.id and cd.service_type = 'P')");
							}
							else if ($value > 0) {
								$srch[] = sprintf(" exists (select 1 from custom_delivery cd where cd.driver_id = %d and cd.order_id = o.id and cd.service_type = 'P')", $value);
							}
						case 'del_driver':
							$value = $form->getData($key);
							if ($value < 0) {
								$srch[] = sprintf(" exists (select 1 from custom_delivery cd where cd.driver_id = 0 and cd.order_id = o.id and cd.service_type = 'D')");
							}
							else if ($value > 0) {
								$srch[] = sprintf(" exists (select 1 from custom_delivery cd where cd.driver_id = %d and cd.order_id = o.id and cd.service_type = 'D')", $value);
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
					if (array_key_exists('pager',$_REQUEST)) $perPage = $_REQUEST['pager'];
					$count = $this->fetchScalar(sprintf('select count(o.id) from %s o,members m where m.id = o.member_id and %s', $this->m_content, implode(' and ',$srch)));
					$pageNum = max(1,min($pageNum, (floor(($count-1)/$perPage)+1)));
					$this->logMessage("showSearchForm",sprintf("pagenum is [$pageNum]"),2);
					$form->setData('pagenum', $pageNum);
					$pagination = $this->pagination($count, $perPage, $pageNum,
							array('prev'=>$this->M_DIR.'forms/paginationPrev.html','next'=>$this->M_DIR.'forms/paginationNext.html',
							'pages'=>$this->M_DIR.'forms/paginationPage.html', 'wrapper'=>$this->M_DIR.'forms/paginationWrapper.html'),
							array('url'=>'/modit/ajax/showSearchForm/orders','destination'=>'middleContent')
						);
					$start = ($pageNum-1)*$perPage;
					$sortorder = 'desc';
					$sortby = 'created';
					if (array_key_exists('sortby',$_POST)) {
						$sortby = $_POST['sortby'];
						if ($sortby == 'name') $sortby = 'm.lastname';
						$sortorder = $_POST['sortorder'];
					}
					$srch[] = sprintf("a.ownertype='order'");
					$srch[] = sprintf("a.ownerid = o.id");
					$srch[] = sprintf("a.addresstype = %d", ADDRESS_PICKUP);
					$this->logMessage("showSearchForm",sprintf("post [%s] srch [%s]",print_r($_POST,true),print_r($srch,true)),2);
					$sql = sprintf('select o.*, m.firstname, m.lastname, m.company, a.line1 from %s o, members m, addresses a where m.id = o.member_id and %s order by %s %s limit %d,%d',
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


	function addContent($fromMain = false) {
		$form = new Forms();
		$form->init($this->getTemplate('addContent'));
		$frmFields = $this->getFields('addContent');
		if (!(array_key_exists('o_id',$_REQUEST) && $_REQUEST['o_id'] > 0 && $data = $this->fetchSingle(sprintf('select * from %s where id = %d', $this->m_content, $_REQUEST['o_id'])))) {
			$data = array('id'=>0,'published'=>false,'tax_exemptions'=>'||','value'=>0,'authorization_amount'=>0,'coupon_id'=>0,'discount_type'=>'','order_status'=>0,'currency_id'=>0,'login_id'=>0);
			$frmFields['coupon_id']['sql'] = 'select id, concat(code," - ",name) from coupons where deleted = 0 and enabled = 1 and published = 1 order by code';
			$frmFields['member_id']['sql'] = 'select 0,"-"';
		} else {
			$frmFields['coupon_id']['sql'] = sprintf('select id, concat(code," - ",name) from coupons where (deleted = 0 and enabled = 1 and published = 1) or id = %d order by code',$data['coupon_id']);
		}
		$data['mgmt'] = $this->fetchSingle(sprintf("select * from members where id = %d", $data["login_id"] ));
		$data['currency_code'] = $this->fetchScalar(sprintf("select value from code_lookups where id = %d",$data['currency_id']));
		$lines = $this->fetchAll(sprintf('select o.*, IF(o.custom_package="A","Additional Charge",IF(o.custom_package="S","Service","Package")) as l_type, p.code, p.name, c.name as coupon_name, po.teaser, p.is_fedex from order_lines o left join coupons c on c.id = o.coupon_id left join product_options po on o.options_id = po.id, product p where o.order_id = %d and p.id = o.product_id and o.deleted = 0 order by line_id',$data['id']));
		$details = array();
		$dtlForm = new Forms();
		$dtlForm->init($this->getTemplate('orderLine'));
		$dtlFields = $dtlForm->buildForm($this->getFields('orderLine'));
		$dimsForm = new Forms();
		$dimsForm->init($this->getTemplate('orderDims'));
		$dimsFields = $dtlForm->buildForm($this->getFields('orderDims'));
		$data["isFedex"] = 0;
		foreach($lines as $line) {
			$line['disc_dollar'] = $line['discount_type'] == 'D' ? '$':'';
			$line['disc_percent'] = $line['discount_type'] == 'P' ? '%':'';
			$dims = array();
			$subrecs = $this->fetchAll(sprintf("select old.*, l1.code as wt_unit, l2.code as dim_unit from order_lines_dimensions old, orders o, code_lookups l1, code_lookups l2 where order_id = %d and line_id = %d and o.id = old.order_id and l1.id = o.custom_weight_code and l2.id = custom_dimension_code", $line["order_id"], $line["line_id"]));
			foreach($subrecs as $sk=>$sv) {
				$dimsForm->addData($sv);
				$dims[] = $dimsForm->show();
			}
			$line["dims"] = implode("",$dims);
			$dtlForm->addData($line);
			$details[] = $dtlForm->show();
			$data["isFedex"] |= $line["custom_package"] && $line["is_fedex"] || ($line["id"] > 0 && $line["product_id"] == FEDEX_RECALC);
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
		$data["isFedex"] = $this->fetchScalar(sprintf("select p.is_fedex from product p, order_lines ol where ol.order_id = %d and ol.custom_package = 'S' and p.id = ol.product_id and ol.deleted = 0", $data["id"]));
		$status = $this->fetchAll(sprintf('select * from code_lookups where type="orderStatus" order by sort, code'));
		$tmp = array();
		$form->addTag('hasRecurring',$data['order_status'] & (STATUS_RECURRING | STATUS_ON_DEMAND));
		foreach($status as $key=>$value) {
			if ($data['order_status'] & (int)$value['code'])
				$tmp[] = $value['code'];
		}
		$data['order_status'] = $tmp;
		$data["allocations"] = $this->getAllocations($data["id"]);
		$form->addData($data);
		$form->addTag('addressForm',$this->loadAddresses($data['id']),false);
		$form->addTag('recurringInfo',$this->getRecurring($data['id']),false);
		$form->addTag('pickup',$this->getPickup($data['id']),false);
		$form->addTag('delivery',$this->getDelivery($data['id']),false);
		$form->addTag('payments',$this->getPayments($data['id']),false);
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
		$split = $this->fetchScalar(sprintf("select sum(percent_of_delivery) from custom_delivery where order_id = %d",$_REQUEST['o_id']));
		if ($split == 0) 
			$form->addError("Driver allocations have not been made");
		else
			if ($split != 100) $form->addError(sprintf("Driver split allocation is %s%%",$split));
		$status = 'false';	//assume it failed
		if (count($_POST) > 0 && array_key_exists('addContent',$_POST)) {
			$form->addData($_POST);
			$status = $form->validate();
			if ($status) {
				if ($_POST['tempEdit'] == 1) {
					switch($_POST['fldName']) {
						case 'recurring_period':
							if ($rp = $this->fetchSingle(sprintf('select * from product_recurring where id = %d',$_POST['recurring_period']))) {
								$form->setData('recurring_discount_rate',$rp['amount']);
								$form->setData('recurring_discount_type',$rp['percent_or_dollar']);
							}
							else {
								$form->setData('recurring_discount_rate',0);
								$form->setData('recurring_discount_type','');
							}
							$form->setData('recurring_discount_dollar','');
							$form->setData('recurring_discount_percent','');
							switch ($rp['percent_or_dollar']) {
								case 'P':
									$form->setData('recurring_discount_percent','%');
									break;
								case 'D';
									$form->setData('recurring_discount_dollar','$');
									break;
								default:
								break;
							}
							$this->logMessage(__FUNCTION__,sprintf('updated recurring from [%s]',print_r($rp,true)),1);
							break;
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
					$this->logMessage("accContent",sprintf("calling recalcOrder from tempEdit"),2);
					$tmp = array();
					$tmp['header'] = $form->getAllData();
					$tmp['products'] = $this->fetchAll(sprintf('select * from order_lines where order_id = %d and deleted = 0',$data['id']));

					if (!$address = $this->fetchSingle(sprintf('select * from addresses where ownerid = %d and ownertype = "order" and tax_address=1',$data['id'])))
						$address = array('id'=>0,'province_id'=>0,'country_id'=>0);
					$tmp['addresses']['shipping'] = $address;
					if (!$address = $this->fetchSingle(sprintf('select * from addresses where ownerid = %d and ownertype = "order" and tax_address=0',$data['id'])))
						$address = array('id'=>0,'province_id'=>0,'country_id'=>0);
					$tmp['addresses']['billing'] = $address;
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
				if (($tmp & STATUS_RECURRING) == STATUS_RECURRING) {
					$this->logMessage(__FUNCTION__,sprintf("setting auth amount to [%s]",$form->getData('total')),1);
					$flds['authorization_amount'] = $form->getData('total');
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
						$data["id"] = $id;
						$form->setData("o_id",$id);
						$form->addTag('addressForm',$this->loadAddresses($data['id']),false);
						$form->addTag('recurringInfo',$this->getRecurring($data['id']),false);
						$form->addTag('pickup',$this->getPickup($data['id']),false);
						$form->addTag('delivery',$this->getDelivery($data['id']),false);
						$form->addTag('payments',$this->getPayments($data['id']),false);
						$form->setData("products","");
					}
					else $id = $data['id'];
					$cart = $this->recalcOrder($id,true);
					$this->logMessage("accContent",sprintf("calling recalcOrder from save"),2);
					//
					//	copy any addresses associated with this customer as well
					//
					$this->execute(sprintf('delete from order_taxes where order_id = %d and line_id = 0',$id));
					foreach($cart['taxes'] as $key=>$tax) {
						$stmt = $this->prepare('insert into order_taxes(order_id,line_id,tax_id,tax_amount) values(?,?,?,?)');
						$stmt->bindParams(array('iiid',$id,0,$key,$tax['tax_amount']));
						$status = $status && $stmt->execute();
					}
					$c = new custom();
					if ($status) {
						$this->commitTransaction();
						if ($data['id'] == 0) 
							$form->addTag('id',$id);
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
						$data["pickup"] = $this->getPickup($data['id']);
						$data["delivery"] = $this->getDelivery($data['id']);
						$this->addMessage('Record Updated');
						$form->addData($data);
						if (array_key_exists('submitEmail',$_POST) && $_POST['submitEmail'] == 1) {
							$emails = $this->configEmails("ecommerce");
							if (count($emails) == 0)
								$emails = $this->configEmails("contact");
							$mailer = new MyMailer();
							$mailer->Subject = sprintf("Order Status Update - %s", SITENAME);
							$body = new Forms();
							$body->setOption('formDelimiter','{{|}}');
							$html = $this->getHtmlForm('orderStatus');
							$o_fields = $body->buildForm($this->getFields('orderStatus'));
							$body->setHTML($html);
							$order = $this->fetchSingle(sprintf('select o.*, m.firstname, m.lastname, m.email from orders o, members m where o.id = %d and m.id = o.member_id',$id));
							$body->addData($this->formatOrder($order));
							$mailer->Body = $body->show();
							$mailer->From = $emails[0]['email'];
							$mailer->FromName = $emails[0]['name'];
							$this->logMessage('addContent',sprintf("mailer object [%s]",print_r($mailer,true)),1);
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
	
	function deleteArticle() {
		if (array_key_exists('j_id',$_REQUEST)) {
			$id = $_REQUEST['j_id'];
			$curr = $this->fetchScalar(sprintf('select * from %s where id = %d',$this->m_content,$id));
			$this->logMessage('deleteArticle', sprintf('deleting order %d',$id), 2);
			$this->beginTransaction();
			$this->execute(sprintf('update %s set deleted = 1 where id = %d',$this->m_content,$curr));
			$this->commitTransaction();
			return $this->ajaxReturn(array('status'=>'true'));
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

	function editLine() {
		$flds = $this->getFields('editLine');
		if (!(array_key_exists('l_id',$_REQUEST) && $data = $this->fetchSingle(sprintf('select * from order_lines where id = %d',$_REQUEST['l_id'])))) {
			$data = array('id'=>0,'product_id'=>0,'coupon_id'=>0,'value'=>0,'quantity'=>0,'order_id'=>$_REQUEST['order_id'],'options_id'=>0,'recurring_shipping_only'=>0);
			$flds['coupon_id']['sql'] = 'select id, concat(code," - ",name) from coupons where deleted = 0 and enabled = 1 and published = 1 order by code';
		} else {
			$flds['coupon_id']['sql'] = sprintf('select id, concat(code," - ",name) from coupons where (deleted = 0 and enabled = 1 and published = 1) or id = %d order by code',$data['coupon_id']);
		}
		$order = $this->fetchSingle(sprintf('select * from orders where id = %d',$_REQUEST['order_id']));
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
		$form->setData("weightDims",$this->weightDims($data));
		$flds = $form->buildForm($flds);
		$this->logMessage('editLine',sprintf('load options for product [%s]',$data['product_id']),1);
		$form->getField('options_id')->addAttribute("sql",sprintf('select id,teaser from product_options where product_id = %d and deleted = 0',$data['product_id']));
		$form->getField('color')->addAttribute("sql",sprintf('select l.id, l.value from product_options_info o, code_lookups l where o.options_id = %d and l.id = o.type_id and o.options_type="color" order by l.code',$data['options_id']));
		$form->getField('size')->addAttribute("sql",sprintf('select l.id, l.value from product_options_info o, code_lookups l where o.options_id = %d and l.id = o.type_id and o.options_type="size" order by l.code',$data['options_id']));
		if ($form->getField('options_id')->hasOptions()) $form->getField('options_id')->addAttribute('required',true);
		$form->addData($data);
		if (array_key_exists('editLine',$_REQUEST) && count($_POST) > 0) {
			$form->addData($_POST);
			$form->getField('options_id')->addAttribute("sql",sprintf('select id,teaser from product_options where product_id = %d and deleted = 0',$_POST['product_id']));
			$form->getField('color')->addAttribute("sql",sprintf('select l.id, l.value from product_options_info o, code_lookups l where o.options_id = %d and l.id = o.type_id and o.options_type="color" order by l.code',$_POST['options_id']));
			$form->getField('size')->addAttribute("sql",sprintf('select l.id, l.value from product_options_info o, code_lookups l where o.options_id = %d and l.id = o.type_id and o.options_type="size" order by l.code',$_POST['options_id']));
			if ($form->getField('options_id')->hasOptions()) $form->getField('options_id')->addAttribute('required',true);
			$form->getField('inventory_id')->addAttribute('sql',sprintf('select id, concat(start_date," - ",end_date," : ",quantity) from product_inventory where product_id = %d and (end_date >= curdate() or end_date = "0000-00-00") order by start_date',$_POST['product_id']));
			if ($_POST['tempEdit']) {
				//
				//	something changed
				//
				$status = true;
				switch($_POST['fldName']) {
					case 'recurring_period':
						if ($rp = $this->fetchSingle(sprintf('select * from product_recurring where id = %d',$_POST['recurring_period']))) {
							$_POST['recurring_discount_rate'] = $rp['discount_rate'];
							$_POST['recurring_discount_type'] = $rp['percent_or_dollar'];
						}
						else {
							$_POST['recurring_discount_rate'] = 0;
							$_POST['recurring_discount_type'] = '';
						}
						$this->logMessage(__FUNCTION__,sprintf('updated recurring from [%s]',print_r($rp,true)),1);
						break;
					case 'options_id':
						$option = $this->fetchSingle(sprintf('select * from product_options where id = %d',$_POST['options_id']));
						if ($p = Ecom::getPricing($_POST['product_id'],$_POST['quantity'],$_POST['order_date'])) {
							$_POST['price'] = $p['price'];
							$_POST['shipping'] = $p['shipping'];
						}
						$_POST['price'] += $option['price'];
						$_POST['shipping'] += $option['shipping'];
						$_POST['qty_multiplier'] = $option['qty_multiplier'];
						break;
					case 'coupon_id':
						$sql = sprintf('select * from coupons where id = %d',$_POST['coupon_id']);
						$c = $this->fetchSingle($sql);
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
								if ($option = $this->fetchSingle(sprintf('select * from product_options where id = %d',$_POST['options_id']))) {
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
						//if ($p = $this->fetchSingle($sql)) {
						if ($p = Ecom::getPricing($_POST['product_id'],$_POST['quantity'],$_POST['order_date'])) {
							$_POST['price'] = $p['price'];
							$_POST['shipping'] = $p['shipping'];	//*$_POST['quantity'];
							if ($option = $this->fetchSingle(sprintf('select * from product_options where id = %d',$_POST['options_id']))) {
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
					$status = true;
					if ($form->getData("custom_package") == "S" && 0 != $this->fetchScalar(sprintf("select count(0) from order_lines where deleted = 0 and order_id = %d and custom_package = 'S' and line_id != %d", $form->getData("order_id"), $form->getData("line_id")))) {
						$form->addError("Only 1 'Service' type can be on any order");
						$status = false;
					}
					$values = array();
					$tmp = Ecom::lineValue($form->getAllData());
					$form->addData($tmp);
					foreach($flds as $key=>$fld) {
						if (!(array_key_exists('database',$fld) && $fld['database'] == false))
							$values[$key] = $form->getData($fld['name']);
					}
					$values['total'] = $tmp['total'];
					$values['taxes'] = $tmp['taxes'];
					$prod = $this->fetchSingle(sprintf('select * from product where id = %d',$values['product_id']));
					$values['tax_exemptions'] = $prod['tax_exemptions'];
					if ($data['id'] != 0) {
						$stmt = $this->prepare(sprintf('update order_lines set %s where id = %d',implode('=?,',array_keys($values)).'=?',$data['id']));
					}
					else {
						$seq = $this->fetchScalar(sprintf('select max(line_id) from order_lines where order_id = %d',$values['order_id']));
						$values['line_id'] = $seq+1;
						$data['line_id'] = $values['line_id']; 
						$stmt = $this->prepare(sprintf('insert into order_lines(%s) values(%s)',implode(',',array_keys($values)),str_repeat('?,',count($values)-1).'?'));
					}
					$stmt->bindParams(array_merge(array(str_repeat('s', count($values))),array_values($values)));
					$this->beginTransaction();
					$stmt->execute();
					if ($data['id'] > 0) {
						//
						//	check for product/qty changes & adjust as appropriate
						//
						$oline = $this->fetchSingle(sprintf('select * from order_lines where id = %d',$data['id']));
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
					//$status = $status && $stmt->execute();
					if ($status) {
						if ($data['id'] == 0) {
							$data['id'] = $this->insertId();
							if ($form->getData("product_id") == FEDEX_RECALC) {
								$nDim = $this->fetchAll(sprintf("select old.* from order_lines_dimensions old, order_lines ol where ol.order_id = %d and ol.custom_package ='P' and old.order_id = ol.order_id and old.line_id = ol.line_id and ol.deleted = 0 order by id", $form->getData("order_id")));
								$pType = $this->fetchScalar(sprintf("select product_id from order_lines where order_id = %d and custom_package = 'P'", $form->getData("order_id")));
								$this->execute(sprintf("update order_lines set fedex_package_type = %d where id = %d", $pType, $data["id"]));
								foreach($nDim as $sk=>$sv) {
									unset($sv["id"]);
									$sv["line_id"] = $data["line_id"];
									$dimStmt = $this->prepare(sprintf("insert into order_lines_dimensions(%s) values (?%s)", implode(", ", array_keys($sv)), str_repeat(", ?", count($sv)-1)));
									$dimStmt->bindParams(array_merge(array(str_repeat("s",count($sv))), array_values($sv)));
									$dimStmt->execute();
								}
							}
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
						$this->recalcOrder($data['order_id']);
						$this->commitTransaction();
						$form->init($this->getTemplate('editLineResult'));
						$form->addData($data);
						$status = "true";
					}
					else {
						$this->rollbackTransaction();
						$form->addError("An error occurred");
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

	function loadAddresses($passed_id = null) {
		if ($passed_id == null) {
			if (array_key_exists('o_id',$_REQUEST))
				$o_id = $_REQUEST['o_id'];
			else
				$o_id = 0;
		}
		else
			$o_id = $passed_id;
		$addresses = $this->fetchAll(sprintf('select a.*, c.value as addressType from addresses a, code_lookups c where ownertype = "order" and ownerid = %d and deleted = 0 and c.id = a.addressType',$o_id));
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
		$this->logMessage("loadAddresses",sprintf("addresses [%s] addresslist [%s] form [%s]",print_r($addresses,true),print_r($addressList,true),print_r($addrForm,true)),4);
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

	function editAddress() {
		if (!array_key_exists('a_id',$_REQUEST))
			return $this->ajaxReturn(array('status'=>'false','html'=>'No id passed'));
		$a_id = $_REQUEST['a_id'];
		if (array_key_exists("o_id",$_REQUEST))
			$o_id =  $_REQUEST["o_id"];
		else if (array_key_exists("ownerid",$_REQUEST)) 
			$o_id = $_REQUEST['ownerid'];
		else $o_id = 0;
		if (!($data = $this->fetchSingle(sprintf('select a.* from addresses a where a.id = %d and a.ownertype = "order" and a.ownerid = %d',$a_id,$o_id)))) {
			$data = array('id'=>0,'ownertype'=>'order','ownerid'=>$o_id);
			$addresses = array();
		}
		else 
			$addresses = $this->fetchAll(sprintf('select * from addresses where ownertype = "order" and ownerid = %d and deleted = 0',$o_id));
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
					$flds['tax_address'] = (int)$this->fetchScalar(sprintf('select extra from code_lookups where id = %d',$form->getData('addresstype')));
					$stmt = $this->prepare(sprintf('insert into addresses(%s) values(%s)', implode(',',array_keys($flds)), str_repeat('?,', count($flds)-1).'?'));
					$stmt->bindParams(array_merge(array(str_repeat('s', count($flds))),array_values($flds)));
				}
				else {
					$flds['tax_address = ?'] = (int)$this->fetchScalar(sprintf('select extra from code_lookups where id = %d',$form->getData('addresstype')));
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

	function deleteAddress() {
		if (array_key_exists('a_id',$_REQUEST) && array_key_exists('o_id',$_REQUEST)) {
			$this->logMessage('deleteAddress',sprintf('deleting id [%d] owner [%d]',$_REQUEST['a_id'],$_REQUEST['o_id']),1);
			if ($data = $this->fetchSingle(sprintf('select * from addresses where ownertype = "%s" and id = %d and ownerid = %d',$_REQUEST['type'],$_REQUEST['a_id'],$_REQUEST['o_id']))) {
				$this->execute(sprintf('update addresses set deleted = 1 where id = %d',$_REQUEST['a_id']));
				return $this->ajaxReturn(array('status'=>'true'));
			}
		}
	}

	function moduleStatus($fromMain = 0) {
		if (array_key_exists('formData',$_SESSION) && array_key_exists('orderSearchForm', $_SESSION['formData'])) {
			$_POST = $_SESSION['formData']['orderSearchForm'];
			$msg = "";
		}
		else {
			$ct = $this->fetchScalar(sprintf('select count(0) from %s where order_status & 2 = 2 and (total - authorization_amount) != 0',$this->m_content));
			if ($ct == 0) {
				$_POST = array('showSearchForm'=>1,'order_status'=>2,'deleted'=>0,'sortby'=>'created','sortorder'=>'desc','pager'=>$this->m_perrow);
				$msg = "Showing latest orders added";
			}
			else {
				$_POST = array('showSearchForm'=>1,'order_status'=>2,'opt_owing'=>'!=','owing'=>0,'sortby'=>'created','sortorder'=>'desc','pager'=>$this->m_perrow);
				$msg = "Showing unpaid orders";
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

	function recalcOrder($order_id,$update = true) {
		//
		//	now recalc order value & taxes etc
		//
		$order = array();
		$order['header'] = $this->fetchSingle(sprintf('select * from orders where id = %d',$order_id));
		$order['products'] = $this->fetchAll(sprintf('select * from order_lines where order_id = %d and deleted = 0',$order_id));
		//$order['taxes'] = $this->fetchAll(sprintf('select * from order_taxes ot where ot.order_id = %d and ot.line_id in (select o.line_id from order_lines o where o.order_id = ot.order_id and o.deleted = 0)',$order_id));
		if (!$address = $this->fetchSingle(sprintf('select * from addresses where ownerid = %d and ownertype = "order" and addresstype = %d',$order_id, ADDRESS_DELIVERY)))
			$address = array('id'=>0,'province_id'=>0,'country_id'=>0);
		$order['addresses']['shipping'] = $address;
		if (!$address = $this->fetchSingle(sprintf('select * from addresses where ownerid = %d and ownertype = "order" and tax_address=0',$order_id)))
			$address = array('id'=>0,'province_id'=>0,'country_id'=>0);
		$order['addresses']['billing'] = $address;

		if (!$address = $this->fetchSingle(sprintf('select * from addresses where ownerid = %d and ownertype = "order" and addresstype = %d',$order_id, ADDRESS_PICKUP)))
			$address = array('id'=>0,'province_id'=>0,'country_id'=>0);
		$order['addresses']['pickup'] = $address;

		$order = Ecom::recalcOrder($order);
		if ($update) {
			$sql = sprintf('update orders set discount_value = %f, value = %f, shipping = %f, taxes = %f, line_discounts = %f, total = %f, net = %f where id = %d',
				$order['header']['discount_value'],
				$order['header']['value'],
				$order['header']['shipping'],
				$order['header']['taxes'],
				$order['header']['line_discounts'],
				$order['header']['total'],
				$order['header']['net'],
				$order_id);
			$this->logMessage("recalcOrder",sprintf("header update [$sql]"),2);
			$this->execute($sql);
			$this->execute(sprintf('delete from order_taxes where order_id = %d and line_id = 0',$order_id));
			$this->execute(sprintf('delete from order_taxes where order_id = %d and line_id = 0',$order_id));
			foreach($order['taxes'] as $key=>$tax) {
				$stmt = $this->prepare('insert into order_taxes(order_id,line_id,tax_id,tax_amount) values(?,?,?,?)');
				$stmt->bindParams(array('iiid',$order_id,0,$key,$tax['tax_amount']));
				$stmt->execute();
			}
		}
		return $order;
	}

	function addItem($fromMain = false) {
		$form = new Forms();
		$form->init($this->getTemplate('addItem'));
		if ($fromMain)
			return $form->show();
		else
			return $this->show($form->show());
	}

	function showOrder() {
		$form = new Forms();
		$form->init($this->getTemplate('showOrder'));
		$form->addData($_REQUEST);
		return $this->show($form->show());
	}

	function formatOrder($data) {
		$data['itemCount'] = $this->fetchScalar(sprintf('select sum(quantity) from order_lines where order_id = %d and deleted = 0',$data['id']));
		$data['formattedTotal'] = $this->my_money_format($data['total']);
		$data['formattedNet'] = $this->my_money_format($data['net']);
		$data['formattedShipping'] = $this->my_money_format($data['shipping']);
		$data['formattedDiscountValue'] = $this->my_money_format($data['discount_value']);
		$data['formattedValue'] = $this->my_money_format($data['value']);
		$data['formattedTaxes'] = $this->my_money_format($data['taxes']);
		$data['formattedAuthorizationAmount'] = $this->my_money_format($data['authorization_amount']);
		if ($data['discount_type'] == 'D')
			$data['formattedDiscountRate'] = $this->my_money_format($data['discount_rate']);
		else
			$data['formattedDiscountRate'] = sprintf('%.2f%%',$data['discount_rate']);
		$data['formattedCreated'] = date(GLOBAL_DEFAULT_DATETIME_FORMAT,strtotime($data['created']));
		$data['formattedShipped'] = date(GLOBAL_DEFAULT_DATETIME_FORMAT,strtotime($data['ship_date']));
		$status = $this->fetchAll(sprintf('select * from code_lookups where type="orderStatus" order by sort'));
		$tmp = array();
		foreach($status as $key=>$stat) {
			if (($data['order_status'] & (int)$stat['code']) == (int)$stat['code'])
				$tmp[] = $stat['value'];
		}
		$data['formattedStatus'] = implode(", ",$tmp);
		$data['formattedCreated'] = date(GLOBAL_DEFAULT_DATETIME_FORMAT,strtotime($data['created']));
		$this->logMessage('formatOrder',sprintf('return [%s]',print_r($data,true)),4);
		return $data;
	}

	function getPayments($o_id) {
		$outer = new Forms();
		$outer->init($this->getTemplate(__FUNCTION__));
		$inner = new Forms();
		$inner->init($this->getTemplate(__FUNCTION__."Row"));
		$flds = $inner->buildForm($this->getFields(__FUNCTION__));
		$recs = $this->fetchAll(sprintf("select * from order_authorization where order_id = %d order by authorization_date", $o_id));
		$rows = array();
		foreach($recs as $k=>$v) {
			$inner->addData($v);
			$rows[] = $inner->show();
		}
		$outer->setData("rows",implode("",$rows));
		return $outer->show();
	}

	function getRecurring($o_id = null) {
		$byAjax = false;
		if (is_null($o_id)) {
			$o_id = $_REQUEST['o_id'];
			$byAjax = true;
		}
		$result = array();
		$outer = new Forms();
		$outer->init($this->getTemplate('recurringInfo'));
		$flds = $outer->buildForm($this->getFields('recurringInfo'));
		if (array_key_exists(__FUNCTION__,$_POST)) {
			$outer->addData($_POST);
			if ($outer->validate()) {
				$values = array();
				foreach($flds as $key=>$fld) {
					if (!(array_key_exists('database',$fld) && $fld['database'] == false)) {
						$values[$fld['name']] = $outer->getData($fld['name']);
					}
				}
				$stmt = $this->prepare(sprintf("update orders set %s=? where id = %d", implode("=?, ", array_keys($values)), $o_id));
				$stmt->bindParams(array_merge(array(str_repeat("s",count($values))),array_values($values)));
				$stmt->execute();
			}
			//$this->execute(sprintf("update orders set custom_recurring_pu_driver = %d, custom_recurring_del_driver = %d, custom_recurring_pu_time = '%s', route_id = %d where id = %d", $outer->getData("custom_recurring_pu_driver"), $outer->getData("custom_recurring_del_driver"), $outer->getData("custom_recurring_pu_time"), $outer->getData("route_id"), $outer->getData("o_id")));
		}
		$count = $this->fetchScalar(sprintf('select count(0) from order_billing where original_id = %s order by billing_date',$o_id));
		if (array_key_exists('pagenum',$_REQUEST)) 
			$pageNum = $_REQUEST['pagenum'];
		else
			$pageNum = 1;	// no 0 based calcs
		if ($pageNum <= 0) $pageNum = 1;
		$perPage = $this->m_perrow;
		if (array_key_exists('pager',$_REQUEST)) $perPage = $_REQUEST['pager'];
		$pagination = $this->pagination($count, $perPage, $pageNum, 
			array('prev'=>$this->M_DIR.'forms/paginationPrev.html','next'=>$this->M_DIR.'forms/paginationNext.html',
			'pages'=>$this->M_DIR.'forms/paginationPage.html', 'wrapper'=>$this->M_DIR.'forms/paginationWrapper.html'),
			array('url'=>'/modit/ajax/getRecurring/orders','destination'=>'tabs-6'));
		$start = ($pageNum-1)*$perPage;
		$sql = sprintf('select * from order_billing where original_id = %s order by billing_date limit %d,%d',$o_id,$start,$perPage);
		$recs = $this->fetchAll($sql);
		$this->logMessage(__FUNCTION__,sprintf('records [%s] count [%d]',$sql,count($recs)),3);
		$form = new Forms();
		$form->init($this->getTemplate('recurringInfoRow'));
		$flds = $form->buildForm($this->getFields('recurringInfoRow'));
		foreach($recs as $key=>$rec) {
			$form->reset();
			$form->addData($rec);
			$result[] = $form->show();
		}
		$outer->addTag('pagination',$pagination,false);
		$outer->addTag('data',implode('',$result),false);
		$outer->addTag('o_id',$o_id);
		$outer->addTag('pagenum',$pageNum);
		$outer->addData($this->fetchSingle(sprintf("select * from orders where id = %d", $o_id)));
		if ($byAjax) {
			return $this->ajaxReturn(array('status'=>true,'html'=>$outer->show()));
		}
		else
			return $outer->show();
	}

	function showRecurring() {
		$r_id = array_key_exists('r_id',$_REQUEST) ? $_REQUEST['r_id'] : 0;
		$rec = $this->fetchSingle(sprintf('select * from order_billing where id = %d',$r_id));
		$form = new Forms();
		$form->init($this->getTemplate('showRecurring'));
		$flds = $form->buildForm($this->getFields('showRecurring'));
		$form->addData($rec);
		return $this->ajaxReturn(array('status'=>true,'html'=>$form->show()));
	}
	
	function getNames() {
		$query = $_REQUEST['s'];
		$member = $this->fetchScalar(sprintf("select member_id from orders where id = %d",$_REQUEST['m']));
		$select = new select();
		$select->addAttributes(array("sql"=>sprintf("select id, concat(if(company='','',concat(company,': ')),lastname,' ',firstname,if(id=%d,'*','')) from members where (firstname like '%%%s%%' or lastname like '%%%s%%' or id = %d or company like '%%%s%%') and deleted = 0 and enabled = 1 order by company, lastname, firstname", $member, $query, $query, $member, $query ),"name"=>"member_id"));
		return $this->ajaxReturn(array('status'=>true,'html'=>$select->show()));
	}

	function getPickup($o_id) {
		$form = new Forms();
		$form->init($this->getTemplate("pickup"));
		$flds = $form->buildForm($this->getFields("pickup"));
		if ($p = $this->fetchSingle(sprintf("select c.*, d.vehicle_id from custom_delivery c left join drivers d on d.id = c.driver_id where c.service_type='P' and c.order_id = %d",$o_id))) {
			$p["pu_commissions"] = $this->getCommissions($p["id"]);
			$form->addData($p);
		}
		$this->logMessage(__FUNCTION__,sprintf("form [%s]", print_r($form,true)),1);
		return $form->show();
	}

	function getDelivery($o_id) {
		$form = new Forms();
		$form->init($this->getTemplate("delivery"));
		$flds = $form->buildForm($this->getFields("delivery"));
		if ($p = $this->fetchSingle(sprintf("select c.*, d.vehicle_id from custom_delivery c left join drivers d on d.id = c.driver_id where c.service_type='D' and c.order_id = %d",$o_id))) {
			$form->setData("order", $this->fetchSingle(sprintf("select * from orders o where o.id = %d", $o_id)));
			$p["del_commissions"] = $this->getCommissions($p["id"]);
			$p["signature_encoded"] = base64_encode($p["signature"]);
			$this->logMessage(__FUNCTION__,sprintf("signature encoded to [%s]", $p["signature_encoded"]),1);
			$form->addData($p);
		}
		return $form->show();
	}

	function getCommissions($d_id) {
		$form = new Forms();
		$form->init($this->getTemplate(__FUNCTION__));
		$flds = $form->buildForm($this->getFields(__FUNCTION__));
		$p = $this->fetchAll(sprintf("select cdc.*, p.name from custom_delivery_commissions cdc, product p where cdc.delivery_id = %d and p.id = cdc.product_id order by p.custom_commission desc, name",$d_id));
		$recs = array();
		foreach($p as $k=>$v) {
			$form->addData($v);
			$recs[] = $form->show();
		}
		$this->logMessage(__FUNCTION__,sprintf("returning [%s]", print_r($recs,true)),1);
		return implode("",$recs);
	}

	function getAllocations($o_id) {
		$outer = new Forms();
		$outer->init($this->getTemplate(__FUNCTION__));
		$flds = $outer->buildForm($this->getFields(__FUNCTION__));
		$recs = $this->fetchAll(sprintf("select p.reference_number, p.reference_date, d.* from order_payment p, order_payment_detail d where d.order_id = %d and p.id = d.payment_id order by p.id", $o_id));
		$data = array();
		foreach($recs as $key=>$rec) {
			$outer->addData($rec);
			$data[] = $outer->show();
		}
		return implode("",$data);
	}

	function oneTime() {
		$outer = new Forms();
		$outer->init($this->getTemplate(__FUNCTION__));
		$flds = $outer->buildForm($this->getFields(__FUNCTION__));
		$inner = new Forms();
		$inner->init($this->getTemplate(__FUNCTION__."Row"));
		$flds = $inner->buildForm($this->getFields(__FUNCTION__."Row"));
		if (array_key_exists(__FUNCTION__,$_REQUEST)) $outer->addData($_REQUEST);
		if (array_key_exists('pagenum',$_REQUEST)) 
			$pageNum = $_REQUEST['pagenum'];
		else
			$pageNum = 1;	// no 0 based calcs
		if ($pageNum <= 0) $pageNum = 1;
		$perPage = $this->m_perrow;
		if (array_key_exists('pager',$_REQUEST)) $perPage = $_REQUEST['pager'];
		$count = $this->fetchScalar('select count(o.id) from orders o, members m where m.id = o.member_id and m.custom_on_account = 0 and o.total != o.authorization_amount');
		$pagination = $this->pagination($count, $perPage, $pageNum, 
			array('prev'=>$this->M_DIR.'forms/paginationPrev.html','next'=>$this->M_DIR.'forms/paginationNext.html',
			'pages'=>$this->M_DIR.'forms/paginationPage.html', 'wrapper'=>$this->M_DIR.'forms/paginationWrapper.html'),
			array('url'=>'/modit/ajax/onAccount/orders','destination'=>'middleContent'));
		$start = ($pageNum-1)*$perPage;
		$recs = $this->fetchAll(sprintf("select o.*, m.firstname, m.lastname from orders o, members m where m.id = o.member_id and m.custom_on_account = 0 and o.total != o.authorization_amount limit %d,%d",$start,$perPage));
		$data = array();
		$status = $this->fetchAll(sprintf('select * from code_lookups where type="orderStatus" order by sort, code'));
		foreach($recs as $key=>$rec) {
			$rec["owing"] = $rec["total"] - $rec["authorization_amount"];
			$tmp = array();
			foreach($status as $key=>$value) {
				if ($rec['order_status'] & (int)$value['code'])
					$tmp[] = $value['value'];
			}
			$rec['order_status'] = implode(', ',$tmp);
			$inner->addData($rec);
			$data[] = $inner->show();
		}
		$outer->addTag('heading',$this->getHeader(),false);
		$outer->addTag("orders",implode("",$data),false);
		return $this->show($outer->show());
	}

	function onAccount() {
		$outer = new Forms();
		$outer->init($this->getTemplate(__FUNCTION__));
		$flds = $outer->buildForm($this->getFields(__FUNCTION__));
		$inner = new Forms();
		$inner->init($this->getTemplate(__FUNCTION__."Row"));
		$flds = $inner->buildForm($this->getFields(__FUNCTION__."Row"));
		if (array_key_exists(__FUNCTION__,$_REQUEST)) $outer->addData($_REQUEST);
		if (array_key_exists('pagenum',$_REQUEST)) 
			$pageNum = $_REQUEST['pagenum'];
		else
			$pageNum = 1;	// no 0 based calcs
		if ($pageNum <= 0) $pageNum = 1;
		$perPage = $this->m_perrow;
		if (array_key_exists('pager',$_REQUEST)) $perPage = $_REQUEST['pager'];
		$count = $this->fetchScalar('select count(o.id) from orders o, members m where o.order_status_processing and o.deleted = 0 and m.id = o.member_id and m.custom_on_account = 1 and o.total != o.authorization_amount');
		$pagination = $this->pagination($count, $perPage, $pageNum, 
			array('prev'=>$this->M_DIR.'forms/paginationPrev.html','next'=>$this->M_DIR.'forms/paginationNext.html',
			'pages'=>$this->M_DIR.'forms/paginationPage.html', 'wrapper'=>$this->M_DIR.'forms/paginationWrapper.html'),
			array('url'=>'/modit/ajax/onAccount/orders','destination'=>'middleContent'));
		$start = ($pageNum-1)*$perPage;
		$recs = $this->fetchAll(sprintf("select o.*, m.firstname, m.lastname from orders o, members m where o.order_status_processing and o.deleted = 0 and m.id = o.member_id and m.custom_on_account = 1 and o.total != o.authorization_amount order by o.id desc limit %d,%d",$start,$perPage));
		$data = array();
		$status = $this->fetchAll(sprintf('select * from code_lookups where type="orderStatus" order by sort, code'));
		foreach($recs as $key=>$rec) {
			$rec["owing"] = $rec["total"] - $rec["authorization_amount"];
			$tmp = array();
			foreach($status as $key=>$value) {
				if ($rec['order_status'] & (int)$value['code'])
					$tmp[] = $value['value'];
			}
			$rec['order_status'] = implode(', ',$tmp);
			$inner->addData($rec);
			$data[] = $inner->show();
		}
		$outer->addTag("pagination",$pagination,false);
		$outer->addTag('heading',$this->getHeader(),false);
		$outer->addTag("orders",implode("",$data),false);
		if ($this->isAjax())
			return $this->ajaxReturn(array('status'=>true,'html'=>$outer->show()));
		else
			return $this->show($outer->show());
	}

	function recalcFuel() {
		$outer = new Forms();
		$outer->init($this->getTemplate(__FUNCTION__));
		$flds = $outer->buildForm($this->getFields(__FUNCTION__));
		$o_id = array_key_exists("o_id",$_REQUEST) ? $_REQUEST["o_id"] : 0;
		$outer->addData($_REQUEST);
		$r = $this->fetchSingle(sprintf("select p.custom_minimum_charge, mf.custom_fuel_override as g_rate, m.custom_fuel_override as m_rate, p.id as f_id
from product p, members_by_folder mbf, members_folders mf, orders o, members m
where o.id = %d and mbf.member_id = o.member_id and mf.id = mbf.folder_id and p.id = mf.custom_fuel and m.id = o.member_id", $o_id));
		$p = $this->fetchScalar(sprintf("select sum(value) as value from order_lines ol, product p 
		where ol.order_id = %d and p.id = ol.product_id and ol.deleted = 0 and p.custom_has_fuel_surcharge", $o_id));
		$rt = $r["custom_minimum_charge"] + $r["g_rate"] + $r["m_rate"];

		$tmp = $this->fetchScalar(sprintf("select custom_fuel_rate from orders where id = %d", $o_id));
		if ($tmp >= .01) $rt = $tmp*100;

		$fc = round($p * ($rt/100),2);
		if ($line = $this->fetchSingle(sprintf("select * from order_lines where order_id = %d and product_id = %d and deleted = 0", $o_id, $r["f_id"]))) {
			$line["price"] = $fc;
			$this->logMessage(__FUNCTION__, sprintf("line [%s]", print_r($line,true)),1);
			$line = Ecom::lineValue($line);
			$this->logMessage(__FUNCTION__, sprintf("line [%s]", print_r($line,true)),1);
			$this->logMessage(__FUNCTION__, sprintf("rate info [%s] product info [%s] rt [%s] fc [%s]", print_r($r,true), print_r($p,true), $rt, $fc),1);
			$this->execute(sprintf("update order_lines set price=%f, value=%f, taxes = %f, total = %f where order_id = %d and product_id = %d", 
				$line["price"], $line["value"], $line["taxes"], $line["total"], $o_id, $r["f_id"]));
			$this->execute(sprintf("delete from order_taxes where order_id = %d and line_id = %d", $o_id, $line["line_id"]));
			foreach($line["taxdata"] as $k=>$v) {
				$this->logMessage(__FUNCTION__,sprintf("k [%s] v [%s]", print_r($k,true), print_r($v,true)),1);
				$this->execute(sprintf("insert into order_taxes(order_id, line_id, tax_id, tax_amount, taxable_amount) values(%d, %d, %d, %f, %f)",
					$o_id, $line["line_id"], $k, $v["tax_amount"], $v["taxable_amount"]));
			}
		}
		$this->recalcOrder($o_id,true);
		$this->calc_driver_allocations($o_id);
		return $this->ajaxReturn(array("status"=>true,"html"=>$outer->show()));
	}

	function cloneAddress() {
		$form = new Forms();
		$form->init($this->getTemplate("editAddress"));
		$flds = $form->buildForm($this->getFields("editAddress"));
		$form->addData($this->fetchSingle(sprintf("select * from addresses where id = %d", array_key_exists("a_id",$_REQUEST) ? $_REQUEST["a_id"] : 0)));
		return $this->ajaxReturn(array("status"=>true, "html"=>$form->show()));
	}

	function weightDims( $data = array() ) {
		$ajax = 0;
		if (count($data) == 0) {
			$data = array("id"=>array_key_exists("l_id",$_REQUEST) ? $_REQUEST["l_id"] : 0);
			$data = $this->fetchSingle(sprintf("select * from order_lines where id = %d", $data["id"]));
			$ajax = 1;
		}
		$ol = $this->fetchSingle(sprintf("select * from order_lines where id = %d", $data["id"]));
		$outer = new Forms();
		$outer->init($this->getTemplate(__FUNCTION__));
		$flds = $outer->buildForm($this->getFields(__FUNCTION__));
		$inner = new Forms();
		$inner->init($this->getTemplate(__FUNCTION__."Row"));
		$flds = $inner->buildForm($this->getFields(__FUNCTION__."Row"));
		$valid = true;
		if (count($_POST) > 0 && array_key_exists(__FUNCTION__,$_POST)) {
			$this->beginTransaction();
			if (array_key_exists("delete", $_POST)) {
				if (count($_POST["delete"])) {
					$ct = $this->fetchScalar(sprintf("select count(old.id) from order_lines_dimensions old, order_lines ol where ol.id = %d and old.order_id = ol.order_id and old.line_id = ol.line_id", $data["id"]));
					if (count($_POST["delete"]) >= $ct) {
						$valid = false;
						$outer->addFormError("Cannot delete all dimensions");
					}
					else {
						foreach($_POST["delete"] as $k=>$v) {
							$this->execute(sprintf("delete from order_lines_dimensions where id = %d", $k));
							unset($_POST["d"][$k]);
						}
						$this->logMessage(__FUNCTION__,sprintf("post after deletes [%s]", print_r($_POST,true)),1);
					}
				}
			}
			$parms = array();
			$qty = 0;
			$wt = 0;
			$old = $this->fetchSingle(sprintf("select sum(quantity) as qty, sum(weight) as wt from order_lines_dimensions where order_id = %d group by order_id", $data["order_id"]));
			foreach($_POST["d"] as $k=>$v) {
				if ($k > 0) {
					if (count($parms) == 0) {
						$stmt = $this->prepare(sprintf("update order_lines_dimensions set %s=? where id = ?", implode("=?, ",array_keys($v))));
					}
					$qty += $v["quantity"];
					$wt += $v["quantity"] * $v["weight"];
					$parms = $v;
					$stmt->bindParams(array_merge(array(str_repeat("s",count($parms)+1)), array_merge(array_values($parms),array($k))));
					$stmt->execute();
				}
			}
			$parms = array();
			foreach($_POST["d"] as $k=>$v) {
				if ($k < 0) {
					if (count($parms) == 0) {
						$parms = $v;
						$parms["order_id"] = $ol["order_id"];
						$parms["line_id"] = $ol["line_id"];
						$stmt = $this->prepare(sprintf("insert into order_lines_dimensions(%s) values(%s?)", implode(", ",array_keys($parms)),str_repeat("?,",count($parms)-1)));
					}
					$parms = array_merge($v,$parms);
					$qty += $v["quantity"];
					$wt += $v["quantity"] * $v["weight"];
					$stmt->bindParams(array_merge(array(str_repeat("s",count($parms))), array_values($parms)));
					$stmt->execute();
				}
			}
			if ($valid)  {
				$wt = round($wt,2);
				$n = $this->fetchScalar(sprintf("select notes from orders where id = %d", $data["order_id"]));
				$note = sprintf("%s\n%s: Quantity updated from %s to %s, weight from %.2f to %.2f", $n, date("Y-M-d h:ia"), $old["qty"], $qty, $old["wt"], $wt);
				$stmt = $this->prepare(sprintf("update orders set notes = ? where id = ?"));
				$stmt->bindParams(array("ss", $note, $data["order_id"] ));
				$stmt->execute();
				$this->execute(sprintf("update order_lines set quantity = %d where id = %d", $qty, $data["id"]));
				$this->commitTransaction();
				$outer->addFormSuccess("Record(s) updated");
				$outer->addFormSuccess("Any corrections for Per Piece charges were NOT applied");
			}
			else
				$this->rollbackTransaction();
		}
		$recs = $this->fetchAll(sprintf("select old.* from order_lines_dimensions old, order_lines ol where ol.id = %d and old.order_id = ol.order_id and old.line_id = ol.line_id order by id", $data["id"]));
		$data["pType"] = $this->fetchScalar(sprintf("select p.name from product p, order_lines ol where ol.id = %d and p.id = ol.product_id", $data["id"]));
		$data["weightUnits"] = $this->fetchScalar(sprintf("select code from code_lookups cl, order_lines ol, orders o where ol.id = %d and o.id = ol.order_id and cl.id = o.custom_weight_code", $data["id"]));
		$data["dimUnits"] = $this->fetchScalar(sprintf("select code from code_lookups cl, order_lines ol, orders o where ol.id = %d and o.id = ol.order_id and cl.id = o.custom_dimension_code", $data["id"]));
		$outer->addData($data);
		$rows = array();
		foreach($recs as $k=>$v) {
			$inner->addData($v);
			$rows[] = $inner->show();
		}
		$outer->setData("rows", implode("",$rows));
		if ($ajax) {
			return $this->ajaxReturn(array('status'=>'true','html'=>$outer->show())); 
		}
		return $outer->show();
	}

	function addDims() {
		$outer = new Forms();
		$outer->init($this->getTemplate(__FUNCTION__));
		$flds = $outer->buildForm($this->getFields(__FUNCTION__));
		$outer->setData("id",-random_int(10,1000));
		return $outer->show();
	}

	function recalcFedex() {
		$outer = new Forms();
		$outer->init($this->getTemplate(__FUNCTION__));
		$outer->addData($_REQUEST);
		$flds = $outer->buildForm($this->getFields(__FUNCTION__));
		$o_id = array_key_exists("o_id",$_REQUEST) ? $_REQUEST["o_id"] : 0;
		$order = $this->fetchSingle(sprintf("select * from orders where id = %d", $o_id));
		$session = $_SESSION;
		$this->m_module = array();
		$this->config = new Custom();
		$this->logMeIn("","",false,$order["member_id"]);
		$svc = $this->fetchSingle(sprintf("select *, p.code from order_lines ol, product p where ol.order_id = %d and ol.custom_package = 'S' and p.id = ol.product_id and ol.deleted = 0", $o_id));
		$grp = $this->fetchSingle(sprintf("select * from members_by_folder where member_id = %d order by id limit 1", $order["member_id"]));
		$g_svc = $this->fetchSingle(sprintf("select * from custom_member_product_options where member_id = %d and product_id = %d and isgroup = 1", $grp["folder_id"], $svc["product_id"]));
		$m_svc = $this->fetchSingle(sprintf("select * from custom_member_product_options where member_id = %d and product_id = %d and isgroup = 0", $order["member_id"], $svc["product_id"]));
		$quote = array(
			"custom_weight_code"=>$order["custom_weight_code"],
			"custom_dimension_code"=>$order["custom_dimension_code"],
			"sequence"=>1,
			"prod"=>array(),
			"custom_placed_by"=> $order["custom_placed_by"],
			"custom_pickup_email" => $order["custom_pickup_email"],
			"custom_email_confirmation" => $order["custom_email_confirmation"],
			"pickupInstructions" => "",
			"deliveryInstructions" => "",
			"custom_declared_value" => $order["custom_declared_value"],
			"custom_reference_number" => $order["custom_reference_number"],
			"pickup_datetime" => date("Y-m-d"),
			"pickup_datetime_hh" => date("h"),
			"pickup_datetime_mm" => date("i"),
			"pickup_datetime_ampm" => date("A"),
			"pickup_datetime_ss" => 30,
			"optType" => 0,
			"serviceType" => is_array($m_svc) ? $m_svc["id"] : $g_svc["id"],
			"custom_override_price" => 0.00,
			"custom_recurring_pu_driver" => "",
			"custom_recurring_del_driver" => "",
			"selectService" => 1,
			"t_id" => 11,
			"s_type" => 0,
			"wt"=>$this->fetchSingle(sprintf("select * from code_lookups where id = %d", $order["custom_weight_code"])),
			"sz"=>$this->fetchSingle(sprintf("select * from code_lookups where id = %d", $order["custom_dimension_code"])),
		);
		if (!($p = $this->fetchAll(sprintf("select * from order_lines where custom_package = 'A' and product_id = %d and order_id = %d and deleted = 0", FEDEX_RECALC, $o_id)))) {
			$p = $this->fetchAll(sprintf("select * from order_lines where custom_package='P' and order_id = %d and deleted = 0", $o_id));
		}
		foreach($p as $k=>$v) {
			$r = $this->fetchAll(sprintf("select * from order_lines_dimensions where order_id = %d and line_id = %d order by id", $v["order_id"], $v["line_id"]));
			$dims = array();
			foreach($r as $sk=>$sv) {
				$dims[$sk+1] = array(
					"quantity"=>$sv["quantity"],
					"weight"=>$sv["weight"],
					"depth"=>$sv["depth"],
					"width"=>$sv["width"],
					"height"=>$sv["height"]
				);
			}
			$quote["prod"][$k+1] = array(
				"product_id"=>$v["fedex_package_type"] > 0 ? $v["fedex_package_type"] : $v["product_id"],
				"dimensions"=>$dims
			);
		}
/*
		if ($p = $this->fetchSingle(sprintf("select old.* from order_lines_dimensions old, order_lines ol where ol.product_id = %d and ol.order_id = %d and ol.deleted = 0 and old.order_id = ol.order_id and old.line_id = ol.line_id", FEDEX_RECALC, $o_id))) {
			//
			//	Some additional weight charged to the order. Add to the original weight for the recalc
			//
			$quote["prod"][1]["dimensions"][1]["weight"] = $p["weight"];
		}
*/
		$this->logMessage(__FUNCTION__,sprintf("quote is now [%s]", print_r($quote,true)),1);
		$cart = $_SESSION["cart"];
		$cart["header"]["pickup_datetime"] = date(DATE_ATOM);
		$cart["addresses"]["shipping"] = $this->fetchSingle(sprintf("select * from addresses where ownertype='order' and ownerid = %d and addresstype = %d", $o_id, ADDRESS_DELIVERY));
		$cart["addresses"]["pickup"] = $this->fetchSingle(sprintf("select * from addresses where ownertype='order' and ownerid = %d and addresstype = %d", $o_id, ADDRESS_PICKUP));
		$this->logMessage(__FUNCTION__,sprintf("quote [%s] cart [%s]", print_r($quote,true), print_r($cart,true)),1);
		$cart = $this->fedExRates( $cart, $quote );
		$tmp = $GLOBALS['globals']->showEcomErrors();
		if (strlen($tmp) > 0) {
			$outer->setData("ecomErrors",$tmp);
			$outer->setData("hasEcomErrors",1);
		}
		foreach($cart["custom"]["ourRates"] as $k=>$v) {
			if ($v["product_id"] == $svc["product_id"]) {
				$newPrc = $v["rate"];
				$diff = $newPrc - $svc["price"];
				if (abs($diff) > .01) {
					$rec = $this->fetchSingle(sprintf("select * from order_lines where order_id = %d and product_id = %d and deleted = 0", $o_id, FEDEX_RECALC));
					if (is_array($rec)) {
						$rec["price"] = $diff;
						$rec["value"] = $diff;
						$rec1 = Ecom::lineValue($rec);
						$this->logMessage(__FUNCTION__,sprintf("input [%s] output [%s]", print_r($rec,true), print_r($rec1,true)),1);
						$stmt = $this->prepare(sprintf("update order_lines set price=?, total=? where order_id = ? and line_id = ?"));
						$stmt->bindParams(array("ddii", $rec1["price"], $rec1["total"], $o_id, $rec["line_id"]));
						$stmt->execute();
					}
					else {
						$tmp = $svc;
						$tmp["product_id"] = FEDEX_RECALC;
						$tmp["price"] = $diff;
						$tmp["value"] = $diff;
						$tmp1 = Ecom::lineValue($tmp);
						$this->logMessage(__FUNCTION__,sprintf("input [%s] output [%s]", print_r($tmp,true), print_r($tmp1,true)),1);
						$prod = array(
							"order_id"=>$o_id,
							"line_id"=>1+(int)$this->fetchScalar(sprintf("select max(line_id) from order_lines where order_id = %d group by order_id", $o_id)),
							"product_id"=>$tmp["product_id"],
							"quantity"=>1,
							"price"=>$diff,
							"value"=>$tmp["value"],
							"total"=>$tmp["total"],
							"taxes"=>$tmp["taxes"],
							"custom_package"=>"A"
						);
						$stmt = $this->prepare(sprintf("insert into order_lines(%s) values (%s?)", implode(", ", array_keys($prod)), str_repeat("?, ", count($prod)-1)));
						$stmt->bindParams(array_merge(array(str_repeat("s",count($prod))),array_values($prod)));
						$stmt->execute();
					}
					//$stmt = $this->prepare(sprintf("insert into order_taxes(order_id, line_id, tax_id, tax_amount, taxable_amount) values(?,?,?,?,?)"));
					//foreach($tmp1["taxdata"] as $sk=>$sv) {
					//	$stmt->bindParams(array("iiidd", $o_id, $prod["line_id"], $sk, $sv["tax_amount"], $sv["taxable_amount"] ));
					//	$stmt->execute();
					//}
					Ecom::recalcOrderFromDB($o_id);
					$this->recalcFuel($o_id);
				}
			}
		}
		$_SESSION = $session;
		return $this->ajaxReturn(array('status'=>'true','html'=>$outer->show(),'ecomError'=>$tmp)); 
	}

	function onDemand() {
		$outer = new Forms();
		$outer->init($this->getTemplate(__FUNCTION__));
		$flds = $this->getFields(__FUNCTION__);
		if (count($_POST) > 0 && array_key_exists(__FUNCTION__,$_POST)) {
			$outer->addData($_POST);
		}
		else {
			$outer->setData("order_status",STATUS_ON_DEMAND);
		}
		$org = array_merge(array(0),$this->fetchScalarAll(sprintf("select distinct member_id from orders where order_status & %d and deleted = 0 and !(order_status & %d)", $outer->getData("order_status"), STATUS_CANCELLED)));
		$p_org = $this->fetchScalarAll(sprintf("select custom_parent_org from members where id in (%s)", implode(", ",$org)));
		$all = array_merge($org, $p_org);
		$flds["member_id"]["sql"] = sprintf("select id, company from members where id in (%s) and deleted = 0 and enabled = 1 order by company", implode(", ",$all));
		$flds = $outer->buildForm($flds);
		$inner = new Forms();
		$inner->init($this->getTemplate(__FUNCTION__."Row"));
		$flds = $inner->buildForm($this->getFields(__FUNCTION__."Row"));
		if (array_key_exists(__FUNCTION__,$_REQUEST)) $outer->addData($_REQUEST);
		if (array_key_exists('pagenum',$_REQUEST)) 
			$pageNum = $_REQUEST['pagenum'];
		else
			$pageNum = 1;	// no 0 based calcs
		if ($pageNum <= 0) $pageNum = 1;
		$perPage = $this->m_perrow;
		if (array_key_exists('pager',$_REQUEST)) $perPage = $_REQUEST['pager'];
		$start = ($pageNum-1)*$perPage;
		if (count($_POST) > 0 && array_key_exists(__FUNCTION__,$_POST)) {
			$outer->addData($_POST);
			if ($outer->validate()) {
				$outer->getField("hidden")->addAttribute('value','');
				$srch = array("j1"=>"m.id = o.member_id", "j2"=>'order_status & %1$d = %1$d', "j3"=>"m.deleted = 0", "j4"=>"o.deleted = 0", "j5"=>"m.enabled = 1", "j6"=>'(member_id = %2$d or m.custom_parent_org = %2$d)');
				$sql = sprintf('from orders o, members m where m.id = o.member_id and order_status & %1$d = %1$d and m.deleted = 0 and o.deleted = 0 and m.enabled = 1 and (member_id = %2$d or m.custom_parent_org = %2$d)', $outer->getData("order_status"), $outer->getData("member_id"));
				if ($outer->getData("order_status") == STATUS_HOLIDAY)
					$sql .= sprintf(' and (recurring_weekdays & %d or recurring_weekdays = 0)', $outer->getData("dow"));
				if ($outer->getData("route_id") > 0)
					$sql .= sprintf(' and route_id = %d', $outer->getData("route_id"));
				$count = $this->fetchScalar(sprintf('select count(o.id) %s', $sql));
				$sql = 'select TIME_FORMAT(custom_recurring_pu_time,"%h:%i %p") as scheduledTime, o.*, m.company '.$sql;
				$pagination = $this->pagination($count, $perPage, $pageNum, 
					array('prev'=>$this->M_DIR.'forms/paginationPrev.html','next'=>$this->M_DIR.'forms/paginationNext.html',
					'pages'=>$this->M_DIR.'forms/paginationPage.html', 'wrapper'=>$this->M_DIR.'forms/paginationWrapper.html'),
					array('url'=>'/modit/ajax/onDemand/orders','destination'=>'middleContent'));
				$start = ($pageNum-1)*$perPage;
				switch($outer->getData("sort_by")) {
				case "O":
					//$sql = sprintf('select o.*, m.company from orders o, members m where m.id = o.member_id and order_status = %1$d and m.deleted = 0 and o.deleted = 0 and m.enabled = 1 and (member_id = %2$d or m.custom_parent_org = %2$d) order by o.id LIMIT %3$d,%4$d', $outer->getData("order_status"), $outer->getData("member_id"), $start, $perPage);
					$sql .= sprintf(" order by o.id limit %d, %d", $start, $perPage);
					break;
				case "S":
					//$sql = sprintf('select o.*, m.company from orders o, members m, addresses a where m.id = o.member_id and order_status = %1$d and m.deleted = 0 and o.deleted = 0 and m.enabled = 1 and (member_id = %2$d or m.custom_parent_org = %2$d) and a.ownertype = "order" and a.ownerid = o.id and a.addresstype = %5$d order by a.line1 LIMIT %3$d,%4$d', $outer->getData("order_status"), $outer->getData("member_id"), $start, $perPage, ADDRESS_DELIVERY);
					$sql .= sprintf(" order by o.id limit %d, %d", $start, $perPage);
					break;
				case "SC":
					$sql = sprintf('select o.*, m.company from orders o, members m, addresses a where m.id = o.member_id and order_status = %1$d and m.deleted = 0 and o.deleted = 0 and m.enabled = 1 and (member_id = %2$d or m.custom_parent_org = %2$d) and a.ownertype = "order" and a.ownerid = o.id and a.addresstype = %5$d order by a.city, a.line1 LIMIT %3$d,%4$d', $outer->getData("order_status"), $outer->getData("member_id"), $start, $perPage, ADDRESS_DELIVERY);
					break;
				case "P":
					$sql = sprintf('select o.*, m.company from orders o, members m, addresses a where m.id = o.member_id and order_status = %1$d and m.deleted = 0 and o.deleted = 0 and m.enabled = 1 and (member_id = %2$d or m.custom_parent_org = %2$d) and a.ownertype = "order" and a.ownerid = o.id and a.addresstype = %5$d order by a.line1 LIMIT %3$d,%4$d', $outer->getData("order_status"), $outer->getData("member_id"), $start, $perPage, ADDRESS_PICKUP);
					break;
				case "PC":
					$sql = sprintf('select o.*, m.company from orders o, members m, addresses a where m.id = o.member_id and order_status = %1$d and m.deleted = 0 and o.deleted = 0 and m.enabled = 1 and (member_id = %2$d or m.custom_parent_org = %2$d) and a.ownertype = "order" and a.ownerid = o.id and a.addresstype = %5$d order by a.city, a.line1 LIMIT %3$d,%4$d', $outer->getData("order_status"), $outer->getData("member_id"), $start, $perPage, ADDRESS_PICKUP);
					break;
				case "V":
					$sql = sprintf('select o.*, m.company from orders o, members m, order_lines ol, product p where m.id = o.member_id and order_status = %1$d and m.deleted = 0 and o.deleted = 0 and m.enabled = 1 and (member_id = %2$d or m.custom_parent_org = %2$d) and ol.order_id = o.id and ol.custom_package = "S" and p.id = ol.product_id order by p.code LIMIT %3$d,%4$d', $outer->getData("order_status"), $outer->getData("member_id"), $start, $perPage);
					break;
				case 'Q':
//					$sql = sprintf('select o.*, m.company from orders o, members m, order_lines ol, product p where m.id = o.member_id and order_status = %1$d and m.deleted = 0 and o.deleted = 0 and m.enabled = 1 and (member_id = %2$d or m.custom_parent_org = %2$d) and ol.order_id = o.id and ol.custom_package = "S" and p.id = ol.product_id order by o.recurring_sequence_pickup LIMIT %3$d,%4$d', $outer->getData("order_status"), $outer->getData("member_id"), $start, $perPage);
					$sql .= sprintf(" order by o.recurring_sequence_pickup limit %d, %d", $start, $perPage);
					break;
				case 'D':
				//		$sql = sprintf('select o.*, m.company from orders o, members m, order_lines ol, product p where m.id = o.member_id and order_status = %1$d and m.deleted = 0 and o.deleted = 0 and m.enabled = 1 and (member_id = %2$d or m.custom_parent_org = %2$d) and ol.order_id = o.id and ol.custom_package = "S" and p.id = ol.product_id order by o.recurring_sequence_delivery LIMIT %3$d,%4$d', $outer->getData("order_status"), $outer->getData("member_id"), $start, $perPage);
					$sql .= sprintf(" order by o.recurring_sequence_delivery limit %d, %d", $start, $perPage);

						break;
				}
				$recs = $this->fetchAll($sql);
				$rows = array();
				foreach($recs as $k=>$v) {
					$v["service"] = $this->fetchSingle(sprintf("select p.* from product p, order_lines ol where ol.order_id = %d and ol.custom_package = 'S' and p.id = ol.product_id", $v["id"]));
					$v["pickup"] = $this->fetchSingle(sprintf("select a.* from addresses a where a.ownerid = %d and a.ownertype = 'order' and a.addressType = %d", $v["id"], ADDRESS_PICKUP));
					$v["delivery"] = $this->fetchSingle(sprintf("select a.* from addresses a where a.ownerid = %d and a.ownertype = 'order' and a.addressType = %d", $v["id"], ADDRESS_DELIVERY));
					$inner->addData($v);
					$rows[] = $inner->show();
				}
				$outer->setData("rows", implode("",$rows));
				$outer->setData("pagination", $pagination);
				if (array_key_exists("process",$_POST) && count($_POST["process"]) > 0 && $outer->getData("do_it") == 1) {
					$processed = array();
					$toResort = array();
					foreach($_POST["process"] as $k=>$v) {
						if ($v == 1) {
							if ($newOrder = $this->dupeOrder($k, $outer)) {
								$processed[] = sprintf('%1$s => <a href="/modit/orders/showOrder?o_id=%2$d" target="_new">%2$d</a>', $this->fetchScalar(sprintf("select m.company from members m, orders o where o.id = %d and m.id = o.member_id", $k)), $newOrder);
								$toResort[$k] = $newOrder;
							}
							else
								$outer->addFormError(sprintf("Error processing #%d",$k));
						}
					}
					if (count($processed) > 0) {
						//
						//	Put the p/u & delivery into preasssigned sort sequence
						//
						foreach($toResort as $k=>$v) {
							$actions = $this->fetchAll(sprintf("select cd.*, o.recurring_sequence_pickup, o.recurring_sequence_delivery from custom_delivery cd, orders o where cd.order_id = %d and o.id = %d", $v, $k));
							foreach($actions as $sk=>$sv) {
								if ($sv["service_type"] == "P")
									$seq = $sv["recurring_sequence_pickup"] == 0 ? 9999 : $sv["recurring_sequence_pickup"];
								else
									$seq = $sv["recurring_sequence_delivery"] == 0 ? 9999 : $sv["recurring_sequence_delivery"];
								$this->execute(sprintf("update custom_delivery set driver_sequence = %d where id = %d", $seq, $sv["id"]));
							}
						}
						$outer->addFormSuccess(sprintf("Processed:"));
						foreach($processed as $k=>$v) {
							$outer->addFormSuccess($v);
						}
					}
				}
			}
		}
		else {
			$outer->addData(array("order_status"=>STATUS_ON_DEMAND));
		}
		if ($this->isAjax())
			return $this->ajaxReturn(array('status'=>true,'html'=>$outer->show()));
		else
			return $this->show($outer->show());
	}

	function onDemandCompany() {
		$outer = new Forms();
		$outer->init($this->getTemplate(__FUNCTION__));
		$flds = $this->getFields(__FUNCTION__);
		$outer->addData($_REQUEST);
		$org = array_merge(array(0),$this->fetchScalarAll(sprintf("select distinct member_id from orders where order_status & %d and deleted = 0 and !(order_status & %d)", $outer->getData("order_status"), STATUS_CANCELLED)));
		$p_org = $this->fetchScalarAll(sprintf("select custom_parent_org from members where id in (%s)", implode(", ",$org)));
		$all = array_merge($org, $p_org);
		$flds["member_id"]["sql"] = sprintf("select id, company from members where id in (%s) and deleted = 0 and enabled = 1 order by company", implode(", ",$all));
		$flds = $outer->buildForm($flds);
		return $this->ajaxReturn(array('status'=>true,'html'=>$outer->show()));
	}

	private function dupeOrder($oldNumber, $form) {
		$valid = true;
		$c = new Common(true,false);
		$c->m_module = array();
		$c->config = new Custom(0);

		$src_order = $this->fetchSingle(sprintf("select * from orders where id = %d", $oldNumber));
		$user = $this->fetchSingle(sprintf("select * from members where id = %d", $src_order["member_id"]));
		$currentSession = $_SESSION;
		if (array_key_exists("cart",$_SESSION)) unset($_SESSION["cart"]);
		$c->logMeIn($user["username"], $user["password"]);
		$this->logMessage(__FUNCTION__, sprintf("*** post logmein session [%s]", print_r($_SESSION,true)), 1);

		$src_service = $this->fetchSingle(sprintf("select p.*, ol.line_id from product p, order_lines ol where ol.order_id = %d and p.id = ol.product_id and ol.custom_package = 'S' and ol.deleted = 0", $src_order["id"]));
		$src_packages = $this->fetchAll(sprintf("select ol.* from order_lines ol where ol.order_id = %d and ol.custom_package = 'P' and ol.deleted = 0", $src_order["id"]));
		$src_contracted = $this->fetchSingle(sprintf("select * from order_lines where order_id = %d and product_id = %d", $src_order["id"], CONTRACTED_RATE));
		$this->logMessage(__FUNCTION__, sprintf("src_packages [%s]", print_r($src_packages,true)), 1);
		$src_extras = $this->fetchScalarAll(sprintf("select ol.product_id from order_lines ol, product p where ol.order_id = %d and ol.custom_package = 'A' and p.id = ol.product_id and p.custom_special_requirement and ol.deleted = 0", $src_order["id"]));
		$src_dimensions = array();
		$src_product = array();
		$qty = 0;
		$wt = 0;
		foreach($src_packages as $k=>$v) {
			$src_product[$k]["product_id"] = $v["product_id"];
			$src_product[$k]["dimensions"] = $this->fetchAll(sprintf("select quantity,weight,height,width,depth from order_lines_dimensions od where od.order_id = %d and od.line_id = %d", $src_order["id"], $v["line_id"]));
			foreach($src_product[$k]["dimensions"] as $sk=>$sv) {
				$wt += $sv["weight"];
				$qty = $sv["quantity"];
			}
			$src_product[$k]["custom_weight"] = $wt;
			$src_product[$k]["quantity"] = $qty;
		}
		$pickup = $this->fetchSingle(sprintf("select * from addresses where ownertype='order' and ownerid=%d and addresstype=%d", $src_order["id"], ADDRESS_PICKUP));
		$delivery = $this->fetchSingle(sprintf("select * from addresses where ownertype='order' and ownerid=%d and addresstype=%d", $src_order["id"], ADDRESS_DELIVERY));
		if ($form->getData("use_scheduled_time") == 1 && $src_order["custom_recurring_pu_time"] != "00:00:00")
			$_SESSION["cart"]["header"]["pickup_datetime"] = sprintf("%s %s", $form->getData("pickup_date"), $src_order["custom_recurring_pu_time"]);
		else
			$_SESSION["cart"]["header"]["pickup_datetime"] = sprintf("%s %s", $form->getData("pickup_date"), $form->getData("pickup_time"));
		$_SESSION["quote"]["custom_declared_value"] = $src_order["custom_declared_value"];
		$_SESSION["cart"]["header"]["custom_declared_value"] = $src_order["custom_declared_value"];

		if (is_array($src_contracted)) {
			$this->logMessage(__FUNCTION__,sprintf("set override price to [%s]", $src_contracted["price"]),1);
			$_SESSION["cart"]["header"]["custom_override_price"] = $src_contracted["price"];
		} else {
			$_SESSION["cart"]["header"]["custom_override_price"] = 0;
		}
		$_SESSION["cart"]["addresses"]["shipping"] = $delivery;
		$_SESSION["cart"]["addresses"]["pickup"] = $pickup;
		$group = $this->fetchSingle(sprintf("select * from members_by_folder where member_id = %d", $src_order["member_id"]));
		$g_product = $this->fetchSingle(sprintf("select * from custom_member_product_options where member_id = %d and isgroup = 1 and product_id = %d", $group["folder_id"], $src_service["id"]));
		$m_product = $this->fetchSingle(sprintf("select * from custom_member_product_options where member_id = %d and isgroup = 0 and product_id = %d", $group["id"], $src_service["id"]));
		if (!is_array($g_product)) {
			//
			//	not a valid product any longer for this customer - log and move on
			//
			$this->logMessage(__FUNCTION__,sprintf("invalid product [%d] for order [%d]", $src_service["id"], $oldNumber ), 1, true, true);
			$valid = false;
		}
		else {
			$product_opt = is_array($m_product) ? $m_product["id"] : $g_product["id"];
			$weight_cd = $src_order["custom_weight_code"];
			$dimension_cd = $src_order["custom_dimension_code"];
			$_SESSION["cart"]["header"]["custom_dimension_code"] = $src_order["custom_dimension_code"];
			$_SESSION["cart"]["header"]["custom_weight_code"] = $src_order["custom_weight_code"];
			$_SESSION["cart"]["header"]["custom_reference_number"] = $src_order["custom_reference_number"];
			$_SESSION["cart"]["header"]["pickupInstructions"] = $this->fetchScalar(sprintf("select instructions from custom_delivery where order_id=%d and service_type='P'",$oldNumber));
			$_SESSION["cart"]["header"]["deliveryInstructions"] = $this->fetchScalar(sprintf("select instructions from custom_delivery where order_id=%d and service_type='D'",$oldNumber));
			$_SESSION["cart"]["header"]["custom_weight_code"] = $src_order["custom_weight_code"];
			$_SESSION["cart"]["header"]["custom_dimension_code"] = $src_order["custom_dimension_code"];
			$_SESSION["cart"]["header"]["custom_insurance"] = $src_order["custom_insurance"];
			$_SESSION["cart"]["header"]["custom_declared_value"] = $src_order["custom_declared_value"];
			$_SESSION["cart"]["header"]["custom_reference_number"] = $src_order["custom_reference_number"];

			$_REQUEST = array(
				"ajax" => "render",
				"t_id" => 37,
				"KJVService" => 1,
				"custom_weight_code" => $weight_cd,
				"custom_dimension_code" => $dimension_cd,
				"serviceType" => $product_opt
			);
			$_POST = $_REQUEST;
			$allowedZones = $this->fetchScalarAll(sprintf("select zone_id from zones_by_folder z, members m where m.id = %d and z.folder_id = m.custom_zones", $c->getUserInfo("id",true)));

			$fromZone = $this->fetchSingle(sprintf("select zf.*, f.downtown from zone_fsa zf, fsa f where f.fsa='%s' and zf.fsa_id = f.id and zone_id in (%s)",
									strtoupper(substr($_SESSION["cart"]["addresses"]["pickup"]["postalcode"],0,3)), is_array($allowedZones) ? implode(", ", $allowedZones) : 0));
			$toZone = $this->fetchSingle(sprintf("select zf.*, f.downtown from zone_fsa zf, fsa f where f.fsa='%s' and zf.fsa_id = f.id and zone_id in (%s)",
									strtoupper(substr($_SESSION["cart"]["addresses"]["shipping"]["postalcode"],0,3)), is_array($allowedZones) ? implode(", ", $allowedZones) : 0));
			//
			//	inzone = kjv delivery or fedex [different rating code]
			//
			$_SESSION["cart"]["header"]["inzone"] = (is_array($fromZone) && is_array($toZone)) ? 1:0;

			$calc = new Custom(0);
			$cart = $_SESSION["cart"];
			$kjv = new KJV();
			if ($src_service["is_fedex"]==1) {
				$quote["wt"] = $this->fetchSingle(sprintf("select * from code_lookups where id = %d",$weight_cd));
				$quote["sz"] = $this->fetchSingle(sprintf("select * from code_lookups where id = %d",$dimension_cd));
				$quote["custom_declared_value"] = $cart["header"]["custom_declared_value"];
				$quote["prod"] = array();
				foreach($src_product as $k=>$v) {
					$quote["prod"][] = $v;
				}
				$_SESSION["quote"]["prod"] = $quote["prod"];
				$cart = $kjv->getFedex($cart,$quote);
				$_SESSION["cart"] = $cart;
				$this->logMessage(__FUNCTION__, sprintf("cart after getfedex [%s]", print_r($cart,true)),1);
				if (!(count($cart) > 0 && $this->checkArray("custom:rates",$cart))) {
					$this->addEcomError(sprintf("No valid FedEx rate returned"));
					$valid = false;
				}
			}
			if ($valid) {
				$quote = $kjv->getPrice( $src_product, $src_extras, $pickup, $delivery, $product_opt, $weight_cd, $dimension_cd );
				$quote["pickupInstructions"] = $this->fetchScalar(sprintf("select instructions from custom_delivery where order_id = %d and service_type='P'",$oldNumber));
				$quote["deliveryInstructions"] = $this->fetchScalar(sprintf("select instructions from custom_delivery where order_id = %d and service_type='P'",$oldNumber));

			//
			//	auto-assign if the recurring drivers are set
			//
				$quote["custom_recurring_pu_driver"] = $src_order["custom_recurring_pu_driver"] > 0 ? $this->fetchScalar(sprintf("select d.id from drivers d, members m where m.id = d.member_id and m.deleted = 0 and m.enabled = 1 and d.deleted = 0 and d.enabled = 1 and d.id = %d",$src_order["custom_recurring_pu_driver"])) : 0;
				if ($form->getData("pu_driver") > 0)
					$quote["custom_recurring_pu_driver"] = $this->fetchScalar(sprintf("select d.id from drivers d, members m where m.id = d.member_id and m.deleted = 0 and m.enabled = 1 and d.deleted = 0 and d.enabled = 1 and d.id = %d",$form->getData("pu_driver")));
				$quote["custom_recurring_del_driver"] = $src_order["custom_recurring_del_driver"] > 0 ? $this->fetchScalar(sprintf("select d.id from drivers d, members m where m.id = d.member_id and m.deleted = 0 and m.enabled = 1 and d.deleted = 0 and d.enabled = 1 and d.id = %d",$src_order["custom_recurring_del_driver"])) : 0;
				if ($form->getData("del_driver") > 0)
					$quote["custom_recurring_del_driver"] = $this->fetchScalar(sprintf("select d.id from drivers d, members m where m.id = d.member_id and m.deleted = 0 and m.enabled = 1 and d.deleted = 0 and d.enabled = 1 and d.id = %d",$form->getData("del_driver")));

				$_SESSION["quote"] = $quote;
				$this->beginTransaction();
				$valid = $kjv->insertOrder($_SESSION["cart"], $orderId);
				$this->logMessage(__FUNCTION__,sprintf("order # is %d, status is [%s]", $orderId, $valid),1);
				if ($valid) {
					$this->commitTransaction();
					$_SESSION["quote"]["fromNightly"] = 1;
					$kjv->finalizeOrder($orderId, true, $this);
					$this->execute(sprintf("update orders set order_status = %d where id = %d", STATUS_PROCESSING, $orderId));
					$valid = $orderId;
				}
				else
					$this->rollbackTransaction();
			}
		}
		$_SESSION = $currentSession;
		return $valid;
	}

	function importOrders() {
		$outer = new Forms();
		$outer->init($this->getTemplate(__FUNCTION__));
		$flds = $outer->buildForm($this->getFields(__FUNCTION__));
		$inner = new Forms();
		$inner->init($this->getTemplate(__FUNCTION__."Row"));
		$flds = $inner->buildForm($this->getFields(__FUNCTION__."Row"));
		if (count($_POST) > 0 && array_key_exists(__FUNCTION__, $_POST)) {
			$outer->addData($_POST);
			$outer->validate();
			$srch = array("a"=>"m.id = oi.member_id");
			foreach($_POST as $k=>$v) {
				switch($k) {
				case "member_id":
					if ($v > 0)
						$srch[$k] = sprintf("%s = %s", $k, $v);
					break;
				case "validate_only":
				case "processed":
					if (strlen($v)) $srch[$k] = sprintf("%s = %s", $k, $v);
					break;
				case "created":
					$srch["created"] = sprintf("date(oi.%s) %s '%s'", $k, $outer->getData("opt_created"), date('Y-m-d',strtotime($outer->getData("created"))));
					break;
				default:
					break;
				}
			}
			$rows = array();
			$recs = $this->fetchAll(sprintf("select oi.*, p.name as fee_code, m.company from order_import oi left join product p on p.id = oi.handling_fee, members m where %s", implode(" and ", $srch)));
			foreach($recs as $k=>$v) {
				$inner->addData($v);
				$rows[] = $inner->show();
			}
			$this->logMessage(__FUNCTION__, sprintf("rows [%s] from recs [%s]", print_r($rows,true), print_r($recs,true)),1);
			$outer->setData("rows", implode("",$rows));
		}
		if ($this->isAjax())
			return $this->ajaxReturn(array('status'=>true,'html'=>$outer->show()));
		else
			return $this->show($outer->show());
	}

	function addImport() {
		$inner = new Forms();
		$inner->init($this->getTemplate(__FUNCTION__."Row"));
		$flds = $inner->buildForm($this->getFields(__FUNCTION__."Row"));
		$outer = new Forms();
		$outer->init($this->getTemplate(__FUNCTION__));
		$flds = $outer->buildForm($this->getFields(__FUNCTION__));
		$i_id = array_key_exists("i_id", $_REQUEST) ? $_REQUEST["i_id"] : 0;
		$outer->setData("i_id", $i_id);
		if (count($_POST) > 0 && array_key_exists(__FUNCTION__, $_POST)) {
			$outer->addData($_POST);
			$valid = $outer->validate();
			$values = array();
			$i_id = $outer->getData("i_id");
			if ($i_id == 0) {
				if (count($_FILES) == 0) {
					$outer->addFormError("Upload file is missing");
					$valid = false;
				}
			}
			if (count($_FILES) > 0) {
				$files = array();
				$msg = array();
				$valid = $this->processUploadedFiles(array("Excel"),$files,$msg);
				foreach($msg as $k=>$v) {
					$outer->addFormError($v);
				}
				$values["created"] = date(DATE_ATOM);
				if ($valid) {
					$values["attachment"] = file_get_contents("..".$files["filename"]["name"]);
					$values["mime_type"] = $_FILES["filename"]["type"];
					$values["filename"] = $_FILES["filename"]["name"];
					unlink("..".$files["filename"]["name"]);
				}
				$this->logMessage(__FUNCTION__,sprintf("processUploadedFiles: files [%s] msg [%s]", print_r($files,true), print_r($msg,true)),1);
			}
			if ($valid) {
				foreach($flds as $key=>$fld) {
					if (!(array_key_exists('database',$fld) && $fld['database'] == false)) {
						$values[$fld['name']] = $outer->getData($fld['name']);
					}
				}
				if ($i_id == 0) 
					$stmt = $this->prepare(sprintf("insert into order_import(%s) values(%s?)", implode(", ",array_keys($values)), str_repeat("?, ",count($values)-1)));
				else
					$stmt = $this->prepare(sprintf("update order_import set %s=? where id = %d", implode("=?, ", array_keys($values)), $i_id));
				$stmt->bindParams(array_merge(array(str_repeat("s",count($values))),array_values($values)));
				if ($stmt->execute()) {
					if ($i_id == 0) $outer->setData("i_id", $this->insertId());
					$outer->addFormSuccess("Record updated");
				}
				else $outer->addFormError("An error occurred");
			}
		}
		if (($i_id = $outer->getData("i_id")) > 0) {
			$outer->addData($this->fetchSingle(sprintf("select * from order_import where id = %d", $i_id)));
		}
		$rows = array();
		$recs = $this->fetchAll(sprintf("select od.*, o.total, p.name as service, cd.scheduled_date, a1.line1 as pu_address, a1.city as pu_city, a1.postalcode as pu_pcode, a2.line1 as del_address, a2.city as del_city, a2.postalcode as del_pcode from order_import_details od, orders o, order_lines ol, product p, custom_delivery cd, addresses a1, addresses a2 where cd.order_id = o.id and cd.service_type = 'P' and ol.order_id = o.id and ol.custom_package = 'S' and od.order_id = o.id and p.id = ol.product_id and a1.ownertype = 'order' and a1.ownerid = o.id and a1.addresstype = %d and a2.ownertype = 'order' and a2.ownerid = o.id and a2.addresstype = %d and import_id = %d order by id", ADDRESS_PICKUP, ADDRESS_DELIVERY, $i_id));
		foreach($recs as $k=>$v) {
			$inner->addData($v);
			$rows[] = $inner->show();
		}
		$outer->setData("orders", implode("", $rows));
		$rows = array();
		$recs = $this->fetchAll(sprintf("select * from order_import_messages where import_id = %d and status = 1", $i_id));
		foreach($recs as $k=>$v) {
			$rows[] = $v["message"];
		}
		if (count($rows) > 0)
			$outer->setData("error_messages",sprintf("<div class='alert alert-error'>%s</div>",implode("<br/>",$rows)));

		$rows = array();
		$recs = $this->fetchAll(sprintf("select * from order_import_messages where import_id = %d and status = 2", $i_id));
		foreach($recs as $k=>$v) {
			$rows[] = $v["message"];
		}
		if (count($rows) > 0)
			$outer->setData("warning_messages",sprintf("<div class='alert alert-warning'>%s</div>",implode("<br/>",$rows)));
	
		if ($this->isAjax())
			return $this->ajaxReturn(array('status'=>true,'html'=>$outer->show()));
		else
			return $this->show($outer->show());
	}

	function getImportAttachment() {
		$i_id = array_key_exists("i_id", $_REQUEST) ? $_REQUEST["i_id"] : 0;
		if ($rec = $this->fetchSingle(sprintf("select * from order_import where id = %d", $i_id))) {
			ob_end_clean();
			header(sprintf('Content-type: %s', $rec["mime_type"]));
			$fn = preg_replace_callback("/([^A-Za-z0-9!*+\/-])/", function($matches) {
				foreach($matches as $match) {
					return '='.sprintf('%02X', ord($match));
				}
			}, "this is a test name");
			$this->logMessage(__FUNCTION__, sprintf("proposed file name [%s]", $fn),1);
			header(sprintf('Content-Disposition: attachment;filename="%s"',$rec["filename"]));
			echo $rec["attachment"];
			exit;
		}
	}

	function deleteImport() {
		$outer = new Forms();
		$outer->init($this->getTemplate(__FUNCTION__));
		$flds = $outer->buildForm($this->getFields(__FUNCTION__));
		$i_id = array_key_exists("i_id", $_REQUEST) ? $_REQUEST["i_id"] : 0;
		$this->beginTransaction();
		$this->execute(sprintf("delete from order_import_messages where import_id in (select id from order_import_details where import_id = %d)", $i_id));
		$this->execute(sprintf("delete from order_import_details where import_id = %d", $i_id));
		$this->execute(sprintf("delete from order_import where id = %d", $i_id));
		$this->commitTransaction();
		if ($this->isAjax())
			return $this->ajaxReturn(array('status'=>true,'html'=>$outer->show()));
		else
			return $this->show($outer->show());
	}
}

?>
