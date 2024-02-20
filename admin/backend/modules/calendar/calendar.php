<?php

/**
 * Calendar feature in the backend section
 */
class calendar extends Backend {

	private $m_tree = 'members_folders';
	private $m_content = 'events';
	private $m_junction = 'events_by_folder';
	private $m_pagination = 5;

    /**
     * @throws phpmailerException
     */
	public function __construct() {
		$this->M_DIR = 'backend/modules/calendar/';
		$this->setTemplates(
			array(
				'deleteAddress'=>$this->M_DIR.'forms/deleteAddress.html',
				'loadAddresses'=>$this->M_DIR.'forms/loadAddresses.html',
				'deleteItem'=>$this->M_DIR.'forms/deleteItem.html',
				'deleteItemResult'=>$this->M_DIR.'forms/deleteItemResult.html',
				'main'=>$this->M_DIR.'calendar.html',
				'form'=>$this->M_DIR.'forms/form.html',
				'showContentTree'=>$this->M_DIR.'forms/contenttree.html',
				'calendarInfo'=>$this->M_DIR.'forms/calendarInfo.html',
				'folderProperties'=>$this->M_DIR.'forms/folder.html',
				'showFolderContent'=>$this->M_DIR.'forms/folderContent.html',
				'folderInfo'=>$this->M_DIR.'forms/folderInfo.html',
				'articleList'=>$this->M_DIR.'forms/articleList.html',
				'showSearchForm'=>$this->M_DIR.'forms/searchForm.html',
				'addContent'=>$this->M_DIR.'forms/addContent.html',
				'calendarByFolder'=>$this->M_DIR.'forms/calendarList.html',
				'addFolder'=>$this->M_DIR.'forms/addFolder.html',
				'addEvent'=>$this->M_DIR.'forms/addEvent.html',
				'headerByWeek'=>$this->M_DIR.'forms/headerByWeek.html',
				'headerByMonth'=>$this->M_DIR.'forms/headerByMonth.html',
				'headerByDay'=>$this->M_DIR.'forms/headerByDay.html',
				'monthEvent'=>$this->M_DIR.'forms/monthEvent.html',
				'monthDay'=>$this->M_DIR.'forms/monthDay.html',
				'monthWeek'=>$this->M_DIR.'forms/monthWeeks.html',
				'monthEvents'=>$this->M_DIR.'forms/monthEvents.html',
				'weekEvent'=>$this->M_DIR.'forms/weekEvent.html',
				'weekDay'=>$this->M_DIR.'forms/weekDay.html',
				'weekEvents'=>$this->M_DIR.'forms/weekEvents.html',
				'dayEvent'=>$this->M_DIR.'forms/dayEvent.html',
				'dayEvents'=>$this->M_DIR.'forms/dayEvents.html',
				'editAddress'=>$this->M_DIR.'forms/editAddress.html',
				'editAddressSuccess'=>$this->M_DIR.'forms/editAddressSuccess.html',
				'couponByEventList'=>$this->M_DIR.'forms/couponList.html',
				'newsByEventList'=>$this->M_DIR.'forms/newsAjaxList.html',
				'blogByEventList'=>$this->M_DIR.'forms/blogAjaxList.html',
				'storeList'=>$this->M_DIR.'forms/storeList.html',
				'productList'=>$this->M_DIR.'forms/productList.html',
				'editItem'=>$this->M_DIR.'forms/editItem.html',
				'blogFolderList'=>$this->M_DIR.'forms/blogFoldersList.html',
				'blogList'=>$this->M_DIR.'forms/blogsList.html',
				'storeFolderList'=>$this->M_DIR.'forms/storeFoldersList.html',
				'storesList'=>$this->M_DIR.'forms/storesList.html',
				'newsFolderList'=>$this->M_DIR.'forms/newsFoldersList.html',
				'newsList'=>$this->M_DIR.'forms/newsList.html',
				'couponFolderList'=>$this->M_DIR.'forms/couponFoldersList.html',
				'couponList'=>$this->M_DIR.'forms/couponsList.html',
				'header'=>$this->M_DIR.'forms/heading.html',
				'recurringEvent'=>$this->M_DIR.'forms/recurrenceForm.html',
				'recurrenceDaily'=>$this->M_DIR.'forms/recurrenceDaily.html',
				'recurrenceWeekly'=>$this->M_DIR.'forms/recurrenceWeekly.html',
				'recurrenceMonthly'=>$this->M_DIR.'forms/recurrenceMonthly.html',
				'recurrenceAnnually'=>$this->M_DIR.'forms/recurrenceAnnually.html',
				'eventByFolder'=>$this->M_DIR.'forms/eventList.html'
			)
		);
		$this->setFields(array(
			'loadAddresses'=>array(),
			'deleteItem'=>array(
				'options'=>array('name'=>'deleteItem','database'=>false),
				'j_id'=>array('type'=>'tag'),
				'deleteItem'=>array('type'=>'hidden','value'=>1),
				'cancel'=>array('type'=>'radiobutton','name'=>'action','value'=>'cancel','checked'=>'checked'),
				'one'=>array('type'=>'radiobutton','name'=>'action','value'=>'one'),
				'all'=>array('type'=>'radiobutton','name'=>'action','value'=>'all')
			),
			'recurrenceDaily'=>array(
				'recurring_frequency'=>array('type'=>'select','id'=>'recurringFrequency','name'=>'recurring_frequency'),
				'recurring_weekdays'=>array('type'=>'hidden','value'=>0,'name'=>'recurring_weekdays','id'=>'recurrencePattern'),
				'recurring_by_position'=>array('type'=>'hidden','value'=>0,'name'=>'recurring_by_position'),
				'recurring_position'=>array('type'=>'hidden','value'=>0,'name'=>'recurring_position')
			),
			'recurrenceWeekly'=>array(
				'recurring_frequency'=>array('type'=>'select','id'=>'recurringFrequency','name'=>'recurring_frequency'),
				'recurring_weekdays'=>array('type'=>'select','multiple'=>'multiple','lookup'=>'recurringWeekdays','required'=>true,'name'=>'recurring_weekdays','id'=>'recurrencePattern','prettyName'=>'Recurring Weekdays'),
				'recurring_by_position'=>array('type'=>'hidden','value'=>0,'name'=>'recurring_by_position'),
				'recurring_position'=>array('type'=>'hidden','value'=>0,'name'=>'recurring_position')
			),
			'recurrenceMonthly'=>array(
				'recurring_frequency'=>array('type'=>'select','id'=>'recurringFrequency','name'=>'recurring_frequency'),
				'recurring_weekdays'=>array('type'=>'select','multiple'=>'multiple','lookup'=>'recurringWeekdays','required'=>true,'name'=>'recurring_weekdays','id'=>'recurrencePattern'),
				'recurring_position'=>array('type'=>'select','lookup'=>'monthPosition','required'=>false,'id'=>'recurringPosition','name'=>'recurring_position'),
				'recurring_by_position'=>array('type'=>'checkbox','id'=>'recurringByPosition','onclick'=>'setByPosition()','value'=>1,'required'=>true,'name'=>'recurring_by_position')
			),
			'recurrenceAnnually'=>array(
				'recurring_frequency'=>array('type'=>'select','id'=>'recurringFrequency','name'=>'recurring_frequency'),
				'recurring_weekdays'=>array('type'=>'hidden','value'=>0,'name'=>'recurring_weekdays','id'=>'recurrencePattern'),
				'recurring_by_position'=>array('type'=>'hidden','value'=>0,'name'=>'recurring_by_position'),
				'recurring_position'=>array('type'=>'hidden','value'=>0,'name'=>'recurring_position')
			),
			'recurringEvent'=>array(
				'recurring_event'=>array('type'=>'checkbox','value'=>1,'id'=>'recurringEvent','onclick'=>'checkRecurrence();','name'=>'recurring_event'),
				'recurring_type'=>array('type'=>'select','lookup'=>'recurrenceType','required'=>false,'id'=>'recurringType','name'=>'recurring_type'),
				'recurring_end_date'=>array('type'=>'datepicker','required'=>false,'id'=>'recurringEndDate','name'=>'recurring_end_date'),
				'separate_events'=>array('type'=>'checkbox','value'=>1,'database'=>false)
			),
			'header'=>array(),
			'blogFolderList'=>array(
				'options'=>array(),
				'enabled'=>array('type'=>'booleanIcon'),
				'title'=>array('type'=>'tag')
			),
			'blogList'=>array(
				'options'=>array(),
				'enabled'=>array('type'=>'booleanIcon'),
				'published'=>array('type'=>'booleanIcon'),
				'title'=>array('type'=>'tag'),
				'expires'=>array('type'=>'datestamp')
			),
			'storeFolderList'=>array(
				'options'=>array(),
				'enabled'=>array('type'=>'booleanIcon'),
				'title'=>array('type'=>'tag')
			),
			'storesList'=>array(
				'options'=>array(),
				'enabled'=>array('type'=>'booleanIcon'),
				'published'=>array('type'=>'booleanIcon'),
				'title'=>array('type'=>'tag'),
				'expires'=>array('type'=>'datestamp')
			),
			'newsFolderList'=>array(
				'options'=>array(),
				'enabled'=>array('type'=>'booleanIcon'),
				'title'=>array('type'=>'tag')
			),
			'newsList'=>array(
				'options'=>array(),
				'enabled'=>array('type'=>'booleanIcon'),
				'published'=>array('type'=>'booleanIcon'),
				'title'=>array('type'=>'tag'),
				'expires'=>array('type'=>'datestamp')
			),
			'couponFolderList'=>array(
				'options'=>array(),
				'enabled'=>array('type'=>'booleanIcon'),
				'title'=>array('type'=>'tag')
			),
			'couponList'=>array(
				'options'=>array(),
				'enabled'=>array('type'=>'booleanIcon'),
				'published'=>array('type'=>'booleanIcon'),
				'name'=>array('type'=>'tag'),
				'expires'=>array('type'=>'datestamp')
			),
			'couponByEventList'=>array(
				'destCoupons'=>array('type'=>'select','multiple'=>'multiple','id'=>'destCoupons')
			),
			'newsByEventList'=>array(
				'destNews'=>array('type'=>'select','multiple'=>'multiple','id'=>'destNews')
			),
			'blogByEventList'=>array(
				'destBlogs'=>array('type'=>'select','multiple'=>'multiple','id'=>'destBlogs')
			),
			'storeList'=>array(
				'destStores'=>array('type'=>'select','multiple'=>'multiple','id'=>'destStores')
			),
			'editAddress'=>array(
				'options'=>array('method'=>'post','action'=>'/modit/ajax/editAddress/calendar'),
				'editAddress'=>array('type'=>'hidden','value'=>1,'database'=>false),
				'addresstype'=>array('type'=>'select','required'=>true,'prettyName'=>'Address Type','sql'=>'select id,value from code_lookups where type="eventAddressTypes" order by value'),
				'ownertype'=>array('type'=>'hidden','value'=>'store'),
				'ownerid'=>array('type'=>'tag'),
				'addressname'=>array('type'=>'input','required'=>true,'prettyName'=>'Location Name'),
				'line1'=>array('type'=>'input','required'=>true,'prettyName'=>'Line 1'),
				'line2'=>array('type'=>'input','required'=>false),
				'city'=>array('type'=>'input','required'=>true,'prettyName'=>'City'),
				'country_id'=>array('type'=>'countryselect','required'=>true,'id'=>'country_id','prettyName'=>'Country'),
				'province_id'=>array('type'=>'provinceselect','required'=>true,'id'=>'province_id','prettyName'=>'Province'),
				'postalcode'=>array('type'=>'input','required'=>true,'prettyName'=>'Postal Code'),
				'phone1'=>array('type'=>'input'),
				'phone2'=>array('type'=>'input'),
				'fax'=>array('type'=>'input'),
				'latitude'=>array('type'=>'number'),
				'longitude'=>array('type'=>'number'),
				'geocode'=>array('type'=>'checkbox','value'=>1,'database'=>false),
				'save'=>array('type'=>'submitbutton','database'=>false,'value'=>'Save Address')
			),
			'dayEvent'=>array(
				'name'=>array('type'=>'tag'),
				'location'=>array('type'=>'tag')
			),
			'dayEvents'=>array(),
			'weekEvent'=>array(
				'name'=>array('type'=>'tag')
			),
			'weekDay'=>array(),
			'weekWeek'=>array(),
			'monthEvent'=>array(
				'name'=>array('type'=>'tag')
			),
			'monthDay'=>array(),
			'monthWeek'=>array(),
			'addContent'=>array(
				'options'=>array('method'=>'post','action'=>'/modit/ajax/addContent/calendar'),
				'id'=>array('type'=>'tag','database'=>false),
				'name'=>array('type'=>'input','required'=>true,'prettyName'=>'Name'),
				'subtitle'=>array('type'=>'input','required'=>false),
				'event_type'=>array('type'=>'select','multiple'=>'multiple','required'=>false,'idlookup'=>'eventType','database'=>false,'id'=>'event_type'),
				'teaser'=>array('type'=>'textarea','required'=>true,'class'=>'mceSimple','prettyName'=>'Teaser'),
				'created'=>array('type'=>'datestamp','mask'=>'d-M-Y h:m:i','database'=>false),
				'featured'=>array('type'=>'checkbox','required'=>false,'value'=>1),
				'deleted'=>array('type'=>'checkbox','required'=>false,'value'=>1),
				'published'=>array('type'=>'checkbox','required'=>false,'value'=>1,'checked'=>'checked'),
				'description'=>array('type'=>'textarea','required'=>true,'id'=>'eventBody','class'=>'mceAdvanced','prettyName'=>'Description'),
				'enabled'=>array('type'=>'checkbox','required'=>false,'value'=>1,'checked'=>'checked'),
				'image1'=>array('type'=>'tag','required'=>false,'prettyName'=>'Image 1'),
				'image2'=>array('type'=>'tag'),
				'image3'=>array('type'=>'tag'),
				'image4'=>array('type'=>'tag'),
				'imagesel_a'=>array('type'=>'image','unknown'=>true,'database'=>false,'id'=>'imagesel_a'),
				'imagesel_b'=>array('type'=>'image','unknown'=>true,'database'=>false,'id'=>'imagesel_b'),
				'imagesel_c'=>array('type'=>'image','unknown'=>true,'database'=>false,'id'=>'imagesel_c'),
				'imagesel_d'=>array('type'=>'image','unknown'=>true,'database'=>false,'id'=>'imagesel_d'),
				'start_date'=>array('type'=>'datepicker','id'=>'sale_startdate','validation'=>'date','required'=>true,'prettyName'=>'Start Date'),
				'end_date'=>array('type'=>'datepicker','id'=>'sale_enddate','validation'=>'date','required'=>false,'prettyName'=>'End Date'),
				'start_time'=>array('type'=>'timepicker','id'=>'start_time','validation'=>'time','required'=>false,'AMPM'=>'AMPM','prettyName'=>'Start Time'),
				'end_time'=>array('type'=>'timepicker','id'=>'end_time','validation'=>'time','required'=>false,'AMPM'=>'AMPM','prettyName'=>'End Time'),
				'submit'=>array('type'=>'submitbutton','database'=>false,'value'=>'Save'),
				'website'=>array('type'=>'input'),
				'addContent'=>array('type'=>'hidden','value'=>1,'database'=>false),
				'couponFolders'=>array('type'=>'select','database'=>false,'id'=>'couponFolderSelector','options'=>$this->nodeSelect(0, 'coupon_folders', 2, false, false),'reformatting'=>false),
				'blogFolders'=>array('type'=>'select','database'=>false,'id'=>'blogFolderSelector','options'=>$this->nodeSelect(0, 'blog_folders', 2, false, false),'reformatting'=>false),
				'storeFolders'=>array('type'=>'select','database'=>false,'id'=>'storeFolderSelector','options'=>$this->nodeSelect(0, 'store_folders', 2, false, false),'reformatting'=>false),
				'newsFolders'=>array('type'=>'select','database'=>false,'id'=>'newsFolderSelector','options'=>$this->nodeSelect(0, 'news_folders', 2, false, false),'reformatting'=>false),
				'productFolders'=>array('type'=>'select','database'=>false,'id'=>'productFolderSelector','options'=>$this->nodeSelect(0, 'product_folders', 2, false, false),'reformatting'=>false),
				'latitude'=>array('type'=>'input','required'=>true,'value'=>0,'validation'=>'number','class'=>'def_field_input_tiny'),
				'longitude'=>array('type'=>'input','required'=>true,'value'=>0,'validation'=>'number','class'=>'def_field_input_tiny'),
				'contactperson'=>array('type'=>'input','required'=>false),
				'email'=>array('type'=>'input','required'=>false,'validation'=>'email'),
				'calendarFolders'=>array('type'=>'select','database'=>false,'id'=>'calendarFolderSelector','options'=>$this->nodeSelect(0, 'members_folders', 2, false, false),'reformatting'=>false),
				'destCouponFolders'=>array('type'=>'select','database'=>false,'id'=>'destCouponFolders','options'=>$this->nodeSelect(0, 'coupon_folders', 2, false, false),'reformatting'=>false,'multiple'=>'multiple'),
				'destCoupons'=>array('type'=>'select','database'=>false,'id'=>'destCoupons','reformatting'=>false,'multiple'=>'multiple'),
				'destProductFolders'=>array('type'=>'select','database'=>false,'id'=>'destProductFolders','options'=>$this->nodeSelect(0, 'product_folders', 2, false, false),'reformatting'=>false,'multiple'=>'multiple'),
				'relatedDestProducts'=>array('type'=>'select','database'=>false,'id'=>'relatedDestProducts','reformatting'=>false,'multiple'=>'multiple'),
				'destStoreFolders'=>array('type'=>'select','database'=>false,'id'=>'destStoreFolders','options'=>$this->nodeSelect(0, 'store_folders', 2, false, false),'reformatting'=>false,'multiple'=>'multiple'),
				'destStores'=>array('type'=>'select','database'=>false,'id'=>'destStores','reformatting'=>false,'multiple'=>'multiple'),
				'destNewsFolders'=>array('type'=>'select','database'=>false,'id'=>'destNewsFolders','options'=>$this->nodeSelect(0, 'news_folders', 2, false, false),'reformatting'=>false,'multiple'=>'multiple'),
				'destNews'=>array('type'=>'select','database'=>false,'id'=>'destNews','reformatting'=>false,'multiple'=>'multiple'),
				'destFolders'=>array('name'=>'destFolders','type'=>'select','multiple'=>'multiple','required'=>true,'id'=>'calendarDestFolders','database'=>false,'options'=>$this->nodeSelect(0, 'members_folders', 2, false, false),'reformatting'=>false,'prettyName'=>'Member Of'),
				'destSearch'=>array('name'=>'destSearch','type'=>'select','multiple'=>'multiple','required'=>false,'id'=>'destSearch','database'=>false,'options'=>$this->nodeSelect(0, 'search_groups', 2, false, false),'reformatting'=>false,'prettyName'=>'Search Related'),
				'recurring_event'=>array('type'=>'checkbox','value'=>1),
				'meta_description'=>array('name'=>'meta_description','type'=>'textarea','required'=>false),
				'social_description'=>array('name'=>'social_description','type'=>'textarea','required'=>false),
				'meta_keywords'=>array('name'=>'meta_keywords','type'=>'textarea','required'=>false),
				'browser_title'=>array('name'=>'browser_title','type'=>'textfield','required'=>false),
				'adword_conversion'=>array('type'=>'textarea','required'=>false)
			),
			'showSearchForm'=>array(
				'options'=>array('action'=>'showSearchForm','name'=>'searchForm','id'=>'search_form'),
				'opt_created'=>array('type'=>'select','name'=>'opt_created','lookup'=>'search_options'),
				'opt_expires'=>array('type'=>'select','name'=>'opt_expires','lookup'=>'search_options'),
				'opt_code'=>array('type'=>'select','name'=>'opt_code','lookup'=>'search_options'),
				'opt_start_date'=>array('type'=>'select','name'=>'opt_start_date','lookup'=>'search_options'),
				'start_date'=>array('type'=>'datepicker','required'=>false,'id'=>'searchStartDate','prettyName'=>'Start Date'),
				'opt_end_date'=>array('type'=>'select','name'=>'opt_end_date','lookup'=>'search_options','prettyName'=>'End Date'),
				'end_date'=>array('type'=>'datepicker','required'=>false,'id'=>'searchEndDate'),
				'code'=>array('type'=>'input','required'=>false),
				'opt_name'=>array('type'=>'select','name'=>'opt_name','lookup'=>'search_string'),
				'name'=>array('type'=>'input','required'=>false),
				'created'=>array('type'=>'datepicker','required'=>false),
				'expires'=>array('type'=>'datepicker','required'=>false,'id'=>'searchExpires'),
				'published'=>array('type'=>'select','lookup'=>'boolean'),
				'enabled'=>array('type'=>'select','lookup'=>'boolean'),
				'deleted'=>array('type'=>'select','lookup'=>'boolean'),
				'featured'=>array('type'=>'select','lookup'=>'boolean'),
				'showSearchForm'=>array('type'=>'hidden','value'=>1),
				'pagenum'=>array('type'=>'hidden','value'=>1),
				'sortby'=>array('type'=>'hidden','value'=>'created'),
				'sortorder'=>array('type'=>'hidden','value'=>'desc'),
				'folder'=>array('type'=>'select','optionslist' => array('table'=>$this->m_tree,'root'=>0,'indent'=>2,'inclusive'=>false),'database'=>false),
				'quicksearch'=>array('type'=>'input','name'=>'quicksearch','required'=>false),
				'opt_quicksearch'=>array('type'=>'hidden','value'=>'like'),
				'pager'=>array('type'=>'select','required'=>true,'value'=>$this->m_pagination,'lookup'=>'paging','id'=>'pager'),
				'submit'=>array('type'=>'submitbutton','value'=>'Search')
			),
			'showFolderContent'=>array(
				'options'=>array('action'=>'showPageContent'),
				'description'=>array('type'=>'tag','reformatting'=>false),
				'image'=>array('type'=>'image'),
				'rollover_image'=>array('type'=>'image'),
				'sortby'=>array('type'=>'hidden','value'=>'created'),
				'sortorder'=>array('type'=>'hidden','value'=>'desc'),
				'pagenum'=>array('type'=>'hidden','value'=>1),
				'currentDate'=>array('type'=>'hidden','database'=>false,'value'=>date('Y-m-d')),
				'moveType'=>array('type'=>'hidden','database'=>false),
				'viewType'=>array('type'=>'hidden','value'=>'M'),
				'calendarContentForm'=>array('type'=>'input','value'=>1),
				'showFolderContent'=>array('type'=>'hidden','value'=>1),
				'pager'=>array('type'=>'select','required'=>true,'value'=>$this->m_pagination,'lookup'=>'paging','id'=>'pager'),
				'submit'=>array('type'=>'hidden')
			),
			'main' => array(
				'test'=>array('type'=>'tag')
			),
			'form' => array(),
			'folderProperties' => array(
				'options'=>array(
					'action'=>'/modit/calendar/showPageProperties',
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

				'couponFolders'=>array('type'=>'select','database'=>false,'id'=>'couponFolderSelector','options'=>$this->nodeSelect(0, 'coupon_folders', 2, false, false),'reformatting'=>false),
				'blogFolders'=>array('type'=>'select','database'=>false,'id'=>'blogFolderSelector','options'=>$this->nodeSelect(0, 'blog_folders', 2, false, false),'reformatting'=>false),
				'storeFolders'=>array('type'=>'select','database'=>false,'id'=>'storeFolderSelector','options'=>$this->nodeSelect(0, 'store_folders', 2, false, false),'reformatting'=>false),
				'newsFolders'=>array('type'=>'select','database'=>false,'id'=>'newsFolderSelector','options'=>$this->nodeSelect(0, 'news_folders', 2, false, false),'reformatting'=>false),

				'destCoupons'=>array('type'=>'select','database'=>false,'id'=>'destCoupons','reformatting'=>false,'multiple'=>'multiple'),
				'destCouponFolders'=>array('type'=>'select','database'=>false,'id'=>'destCouponFolders','reformatting'=>false,'multiple'=>'multiple'),
				'destNews'=>array('type'=>'select','database'=>false,'id'=>'destNews','reformatting'=>false,'multiple'=>'multiple'),
				'destNewsFolders'=>array('type'=>'select','database'=>false,'id'=>'destNewsFolders','reformatting'=>false,'multiple'=>'multiple'),
				'destStores'=>array('type'=>'select','database'=>false,'id'=>'destStores','reformatting'=>false,'multiple'=>'multiple'),
				'destStoreFolders'=>array('type'=>'select','database'=>false,'id'=>'destStoreFolders','reformatting'=>false,'multiple'=>'multiple'),
				'destBlogs'=>array('type'=>'select','database'=>false,'id'=>'destBlogs','reformatting'=>false,'multiple'=>'multiple'),
				'destBlogFolders'=>array('type'=>'select','database'=>false,'id'=>'destBlogFolders','reformatting'=>false,'multiple'=>'multiple'),
				'submit'=>array('type'=>'submitbutton','value'=>'Save','database'=>false)
			),
			'showContentTree' => array(),
			'calendarInfo' => array(),
			'showCalendarContent' => array(),
			'folderInfo' => array(
				'title'=>array('type'=>'tag'),
				'alternate_title'=>array('type'=>'tag'),
				'notes'=>array('type'=>'tag','reformatting'=>false),
				'description'=>array('type'=>'tag','reformatting'=>false),
				'image' => array('type'=>'image'),
				'rollover_image'=>array('type'=>'image')
			),
			'articleList' => array(
				'id'=>array('type'=>'tag'),
				'title'=>array('type'=>'tag'),
				'start_date'=>array('type'=>'datestamp','mask'=>'d-M-Y'),
				'end_date'=>array('type'=>'datestamp','mask'=>'d-M-Y','suppressNull'=>true),
				'published'=>array('type'=>'booleanIcon'),
				'enabled'=>array('type'=>'booleanIcon'),
				'featured'=>array('type'=>'booleanIcon')
			),
			'eventByFolder'=>array(
				'destEvents'=>array('type'=>'select','id'=>'byEventSource','multiple'=>'multiple')
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

		//
		//	load coupons attached to this folder
		//
		$data['destCoupons'] = $this->loadRelations('destCoupons',$this,"altCouponFormat",$data['id'],'eventfolder','coupon','coupons','and deleted = 0 and enabled = 1 and published = 1',true);		
		$frmFlds['destCoupons']['options'] = $this->fetchOptionsTest(sprintf('select id as code,concat(code," - ",name) as value from coupons where deleted = 0 and enabled = 1 and published = 1 and id in (%s) order by code',implode(',',array_merge(array(0),$data['destCoupons']))));

		//
		//	load coupon folders attached to this folder
		//
		$frmFlds['destCouponFolders']['options'] = $this->nodeSelect(0,'coupon_folders',2,false);
		$data['destCouponFolders'] = $this->loadRelations('destCouponFolders',$this,"altCouponFormat",$data['id'],'eventfolder','couponfolder','coupon_folders','',true);

		//
		//	load news attached to this folder [owned by]
		//
		$data['destNews'] = $this->loadRelations('destNews',$this,"altNewsFormat",$data['id'],'eventfolder','news','news','and deleted = 0 and enabled = 1 and published = 1',false);		
		$frmFlds['destNews']['options'] = $this->fetchOptionsTest(sprintf('select id as code,title as value from news where deleted = 0 and enabled = 1 and published = 1 and id in (%s) order by title',implode(',',array_merge(array(0),$data['destNews']))));

		//
		//	load news folders attached to this folder [owned by]
		//
		$frmFlds['destNewsFolders']['options'] = $this->nodeSelect(0,'news_folders',2,false);
		$data['destNewsFolders'] = $this->loadRelations('destNewsFolders',$this,"altNewsFormat",$data['id'],'eventfolder','newsfolder','news_folders','',false);

		//
		//	load stores attached to this folder [owned by]
		//
		$data['destStores'] = $this->loadRelations('destStores',$this,"altStoreFormat",$data['id'],'eventfolder','store','stores','and deleted = 0 and enabled = 1 and published = 1',false);		
		$frmFlds['destStores']['options'] = $this->fetchOptionsTest(sprintf('select id as code,name as value from stores where deleted = 0 and enabled = 1 and published = 1 and id in (%s) order by name',implode(',',array_merge(array(0),$data['destStores']))));

		//
		//	load Store folders attached to this folder [owned by]
		//
		$frmFlds['destStoreFolders']['options'] = $this->nodeSelect(0,'store_folders',2,false);
		$data['destStoreFolders'] = $this->loadRelations('destStoreFolders',$this,"altStoreFormat",$data['id'],'eventfolder','storefolder','store_folders','',false);

		//
		//	load blogs attached to this folder [owned by]
		//
		$data['destBlogs'] = $this->loadRelations('destBlogs',$this,"altBlogFormat",$data['id'],'eventfolder','blog','blog','and deleted = 0 and enabled = 1 and published = 1',false);		
		$frmFlds['destBlogs']['options'] = $this->fetchOptionsTest(sprintf('select id as code,title as value from blog where deleted = 0 and enabled = 1 and published = 1 and id in (%s) order by title',implode(',',array_merge(array(0),$data['destBlogs']))));

		//
		//	load blog folders attached to this folder [owned by]
		//
		$frmFlds['destBlogFolders']['options'] = $this->nodeSelect(0,'blog_folders',2,false);
		$data['destBlogFolders'] = $this->loadRelations('destBlogFolders',$this,"altBlogFormat",$data['id'],'eventfolder','blogfolder','blog_folders','',false);

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
						$parent = $this->fetchSingleTest('select * from %s where level = %d and left_id < %d and right_id > %d', $this->m_tree, $data['level'] - 1, $data['left_id'], $data['right_id']);
					else $parent['id'] = 0;
					if ($_POST['p_id'] != $parent['id']) {
						$this->logMessage('showPageProperties', sprintf('moving [%d] to [%d] posted[%d]',$data['id'],$p['id'], $_POST['p_id']), 1);
						$mptt = new mptt($this->m_tree);
						$mptt->move($data['id'], $_POST['p_id']);
					}
				}
				$this->beginTransaction();
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
				if ($status = $stmt->execute()) {
					$status = $status && $this->updateRelations('destCoupons',$data['id'],'eventfolder','coupon','coupons',true,true);
					$status = $status && $this->updateRelations('destCouponFolders',$data['id'],'eventfolder','couponfolder','coupon_folders',true,false);
					$status = $status && $this->updateRelations('destNews',$data['id'],'eventfolder','news','news',false,true);
					$status = $status && $this->updateRelations('destNewsFolders',$data['id'],'eventfolder','newsfolder','news_folders',false,false);
					$status = $status && $this->updateRelations('destStores',$data['id'],'eventfolder','store','stores',false,true);
					$status = $status && $this->updateRelations('destStoreFolders',$data['id'],'eventfolder','storefolder','store_folders',false,false);
					$status = $status && $this->updateRelations('destBlogs',$data['id'],'eventfolder','blog','blog',false,true);
					$status = $status && $this->updateRelations('destBlogFolders',$data['id'],'eventfolder','blogfolder','blog_folders',false,false);
				}
				if ($status) {
					if ($this->isAjax()) {
						$this->logMessage('showPageProperties', 'executing ajax success return', 3);
						$this->addMessage('Record successfully added');
						$this->commitTransaction();
						return $this->ajaxReturn(array(
								'status'=>'true',
								'html'=>'',
								'url'=>'/modit/calendar?p_id='.$data['id']
						));
					}
				} else {
					$this->rollbackTransaction();
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
			if (count($_POST) == 0 && array_key_exists('formData',$_SESSION) && array_key_exists('calendarContentForm', $_SESSION['formData']))
				$_POST = $_SESSION['formData']['calendarContentForm'];
			$form->init($this->getTemplate('showFolderContent'),array('name'=>'showFolderContent'));
			$frmFields = $form->buildForm($this->getFields('showFolderContent'));
			if (count($_POST) > 0 && array_key_exists('showFolderContent',$_POST))
				$form->addData($_POST);
			if (array_key_exists('pagenum',$_REQUEST)) 
				$pageNum = $_REQUEST['pagenum'];
			else
				$pageNum = 1;	// no 0 based calcs
			if ($pageNum <= 0) $pageNum = 1;
			$perPage = $this->m_pagination;
			if (array_key_exists('pager',$_REQUEST)) $perPage = $_REQUEST['pager'];
			$currDate = $form->getData('currentDate');
			if ($currDate == '') $currDate = date('Y-m-d');
			$type = $form->getData('viewType');
			if (($moveType = $form->getData('moveType')) != null) {
				$this->logMessage('showPageContent',sprintf('moving %s start date is [%s]',$moveType,$currDate),3);
				switch($moveType) {
					case '+':
						$currDate = sprintf('%s + 1 %s',$currDate,$type);
						break;
					case '-':
						$currDate = sprintf('%s - 1 %s',$currDate,$type);
						break;
					case '+M':
						$currDate = sprintf('%s + 1 month',$currDate);
						break;
					case '-M':
						$currDate = sprintf('%s - 1 month',$currDate);
						break;
					case '+Y':
						$currDate = sprintf('%s + 1 year',$currDate);
						break;
					case '-Y':
						$currDate = sprintf('%s - 1 year',$currDate);
						break;
					case 'T':
						$currDate = date('Y-m-d');
						break;
					default:
						break;
				}
				$this->logMessage('showPageContent',sprintf('moving %s result date is [%s]',$moveType,$currDate),3);
				$form->setData('moveType','');
			}
			$currDate = strtotime($currDate);
			$form->addTag('startDate',date('M-Y',$currDate));
			$form->setData('currentDate',date('Y-m-d',$currDate));
			switch($type) {
				case 'week':
					$wk = strftime('%w',$currDate);
					$sd = date('Y-m-d',strtotime(sprintf('%s - %d days',date('Y-m-d',$currDate),$wk)));
					$ed = date('Y-m-d',strtotime(sprintf('%s + 6 days',$sd)));
					$this->logMessage('showPageContent',sprintf('weekday is [%d] start [%s] end [%s]',$wk,$sd,$ed),3);
					$tmp = new Forms();
					$tmp->init($this->getTemplate('headerByWeek'));
					$form->addTag('headerOptions',$tmp->show(),false);
					$form->addTag('calendar',$this->loadWeekEvents($sd,$ed),false);
					break;
				case 'day':
					$sd = date('Y-m-d',$currDate);
					$ed = $sd;
					$this->logMessage('showPageContent',sprintf('day is start [%s] end [%s]',$sd,$ed),3);
					$tmp = new Forms();
					$tmp->init($this->getTemplate('headerByDay'));
					$form->addTag('headerOptions',$tmp->show(),false);
					$form->addTag('calendar',$this->loadDailyEvents($sd,$ed),false);
					break;
				case 'month':
				default:
					$sd = sprintf('%04d-%02d-01',date('Y',$currDate),date('m',$currDate));
					$tmp = sprintf('%s%02d',substr($sd,0,8),date('t',$currDate));
					$this->logMessage('showPageContent',"tmp [$tmp]",1);
					$ed = date('Y-m-d',strtotime($tmp));
					$tmp = new Forms();
					$tmp->init($this->getTemplate('headerByMonth'));
					$form->addTag('headerOptions',$tmp->show(),false);
					$form->addTag('calendar',$this->loadMonthEvents($sd,$ed),false);
					break;
			}
			$this->logMessage('showPageContent',sprintf('start/end dates [%s]/[%s]',$sd,$ed),3);
			$sql = sprintf('select count(e.id) from %s e, %s f where e.deleted = 0 and f.folder_id = %d and e.id = f.event_id and e.id in (select event_id from event_dates where event_date between "%s" and "%s")', 
					$this->m_content, $this->m_junction, $_REQUEST['p_id'],$sd,$ed);
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
				//$form->addData($_POST);
			}
			$sql = sprintf('select e.*, f.id as j_id from %s e, %s f where e.deleted = 0 and e.id = f.event_id and f.folder_id = %d and e.id in (select event_id from event_dates where event_date between "%s" and "%s") order by %s %s limit %d,%d',  
				$this->m_content, $this->m_junction, $_REQUEST['p_id'],$sd,$ed,$sortby, $sortorder, $start,$perPage);
			$products = $this->fetchAllTest($sql);
			$this->logMessage('showPageContent', sprintf('sql [%s], records [%d]',$sql, count($products)), 2);
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
			$form->addTag('coupons',$this->displayRelations($data['id'],'coupons','eventfolder','coupon',' and c.deleted = 0',true,$this->getFields('couponList'),$this->getTemplate('couponList')),false);
			$form->addTag('couponFolders',$this->displayRelations($data['id'],'coupon_folders','eventfolder','couponfolder','',true,$this->getFields('couponFolderList'),$this->getTemplate('couponFolderList')),false);
			$form->addTag('news',$this->displayRelations($data['id'],'news','eventfolder','news',' and c.deleted = 0',false,$this->getFields('newsList'),$this->getTemplate('newsList')),false);
			$form->addTag('newsFolders',$this->displayRelations($data['id'],'news_folders','eventfolder','newsfolder','',false,$this->getFields('newsFolderList'),$this->getTemplate('newsFolderList')),false);
			$form->addTag('stores',$this->displayRelations($data['id'],'stores','eventfolder','store',' and c.deleted = 0',false,$this->getFields('storesList'),$this->getTemplate('storesList')),false);
			$form->addTag('storeFolders',$this->displayRelations($data['id'],'store_folders','eventfolder','storefolder','',false,$this->getFields('storeFolderList'),$this->getTemplate('storeFolderList')),false);
			$form->addTag('blog',$this->displayRelations($data['id'],'blog','eventfolder','blog',' and c.deleted = 0',false,$this->getFields('blogList'),$this->getTemplate('blogList')),false);
			$form->addTag('blogFolders',$this->displayRelations($data['id'],'blog_folders','eventfolder','blogfolder','',false,$this->getFields('blogFolderList'),$this->getTemplate('blogFolderList')),false);
		}
		$_SESSION['formData']['calendarContentForm'] = $form->getAllData();
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
			if (array_key_exists('formData',$_SESSION) && array_key_exists('calendarSearchForm', $_SESSION['formData']))
				$_POST = $_SESSION['formData']['calendarSearchForm'];
			else
				$_POST = array('showSearchForm'=>1,'deleted'=>0,'sortby'=>'published','sortorder'=>'asc');
		if (count($_POST) > 0 && array_key_exists('showSearchForm',$_POST)) {
			if ((!array_key_exists('deleted',$_POST)) || strlen($_POST['deleted']) == 0) $_POST['deleted'] = 0;
			$form->addData($_POST);
			if ($form->validate()) {
				if (strlen($form->getData("quicksearch")) > 0) {
					$_SESSION['formData']['calendarSearchForm'] = array('showSearchForm'=>1,'opt_quicksearch'=>'like','quicksearch'=>$form->getData("quicksearch"),'pager'=>$form->getData("pager"),'deleted'=>0);
				}
				else
					$_SESSION['formData']['calendarSearchForm'] = $form->getAllData();
				$srch = array();
				foreach($frmFields as $key=>$value) {
					switch($key) {
						case 'quicksearch':
							if (array_key_exists('opt_quicksearch',$_POST) && strlen($_POST['opt_quicksearch']) > 0 && $value = $form->getData($key)) {
								if ($_POST['opt_quicksearch'] == 'like' && strpos($value,'%',0) === false) {
									$value = '%'.$value.'%';
								}
								$tmp = array();
								$tmp[] = sprintf(' name %s "%s"',$_POST['opt_quicksearch'],$this->escape_string($value));
								$tmp[] = sprintf(' description %s "%s"',$_POST['opt_quicksearch'],$this->escape_string($value));
								$srch = array(sprintf('(%s)',implode(' or ',$tmp)),'deleted = 0');
								break 2;
								//break; //Sonar Cloud - PHP Major bug - All code should be reachable
							}
							break;
						case 'name':
							if (array_key_exists('opt_name',$_POST) && strlen($_POST['opt_name']) > 0 && $value = $form->getData($key)) {
								if ($_POST['opt_name'] == 'like' && strpos($value,'%',0) === false) {
									$value = '%'.$value.'%';
								}
								$srch[] = sprintf(' %s %s "%s"',$key,$_POST['opt_name'],$this->escape_string($value));
							}
							break;
						case 'start_date':
						case 'end_date':
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
								$srch[] = sprintf(' n.id in (select event_id from %s where folder_id = %d) ', $this->m_junction, $value);
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
				$this->logMessage('showSearchForm',sprintf('search criteria [%s]',print_r($srch,true)),3);
				if (count($srch) > 0) {
					if (array_key_exists('pagenum',$_REQUEST))
						$pageNum = $_REQUEST['pagenum'];
					else
						$pageNum = 1;	// no 0 based calcs
					$perPage = $this->m_pagination;
					if (array_key_exists('pager',$_REQUEST)) $perPage = $_REQUEST['pager'];
					$count = $this->fetchScalarTest('select count(n.id) from '.$this->m_content.' n where 1=1 and %s',  implode(' and ',$srch));
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
					$sql = sprintf('select n.*, 0 as j_id from %s n where %s order by %s %s limit %d,%d',
						 $this->m_content, implode(' and ',$srch),$sortby, $sortorder, $start,$perPage);
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
     * @param $fromMain
     * @return array|false|mixed|string|string[]|void
     * @throws Exception
     */
    function addContent($fromMain = false) {
		$form = new Forms();
		$form->init($this->getTemplate('addContent'),array('name'=>'addContent'));
		$frmFields = $this->getFields('addContent');
		if (!(array_key_exists('p_id',$_REQUEST) && $_REQUEST['p_id'] > 0 && $data = $this->fetchSingleTest('select * from %s where id = %d', $this->m_content, $_REQUEST['p_id']))) {
			$data = array('id'=>0,'published'=>false,'image1'=>'','image2'=>'','image3'=>'','image4'=>''); 
		}
		//
		//	redo source folders as a collapsible list - a lot of folders is too long as a single <ul>
		//
		if ((count($_REQUEST) > 0 && array_key_exists('addContent',$_REQUEST))) {
			$ids = array();
			if (array_key_exists('destFolders',$_REQUEST)) {
				$ids = $_REQUEST['destFolders'];
				if (!is_array($ids)) $ids = array($ids);
			}
			//if ($data['id'] > 0) {
			//	$tmp = $this->fetchScalarAllTest('select folder_id from %s where event_id = %d', $this->m_junction, $data['id']);
			//	$ids = array_merge($ids,$tmp);
			//}
			if (count($ids) > 0) {
				$data['destFolders'] = $ids;
			}
		}
		elseif ($data['id'] > 0) {
			$data['destFolders'] = $this->fetchScalarAllTest('select folder_id from %s where event_id = %d', $this->m_junction, $data['id']);
		}
		if ((count($_REQUEST) > 0 && array_key_exists('destSearch',$_REQUEST)) || $data['id'] > 0) {
			$srch = array();
			if (array_key_exists('destSearch',$_REQUEST)) {
				$srch = $_REQUEST['destSearch'];
				if (!is_array($srch)) $srch = array($srch);
			}
			if ($data['id'] > 0) {
				$tmp = $this->fetchScalarAllTest('select folder_id from event_by_search_group where event_id = %d', $data['id']);
				$srch = array_merge($srch,$tmp);
			}
			if (count($srch) > 0) {
				$data['destSearch'] = $srch;
			}
			$this->logMessage(__FUNCTION__,sprintf('search folders [%s]',print_r($srch,true)),1);
		}
		if ((count($_REQUEST) > 0 && array_key_exists('event_type',$_REQUEST)) || $data['id'] > 0) {
			$ids = array();
			if (array_key_exists('event_type',$_REQUEST)) {
				$ids = $_REQUEST['event_type'];
				if (!is_array($ids)) $ids = array($ids);
				if ($ids[0] == '') unset($ids[0]);
			} elseif ($data['id'] > 0) {
				$tmp = $this->fetchScalarAllTest('select event_type from event_types where event_id = %d', $data['id']);
				$ids = array_merge($ids,$tmp);
			}
			$data['event_type'] = $ids;
		}
		else $data['event_type'] = array();

		//
		//	load coupon folders attached to this folder
		//
		$data['destCoupons'] = $this->loadRelations('destCoupons',$this,"altCouponFormat",$data['id'],'event','coupon','coupons','and deleted = 0 and enabled = 1 and published = 1',true);
		$frmFields['destCoupons']['options'] = $this->fetchOptionsTest('select id as code,concat(code," - ",name) as value from coupons where deleted = 0 and enabled = 1 and published = 1 and id in (%s) order by code',implode(',',array_merge(array(0),$data['destCoupons'])));

		//
		//	load coupons attached to this folder
		//
		$frmFields['destCouponFolders']['options'] = $this->nodeSelect(0,'coupon_folders',2,false);
		$data['destCouponFolders'] = $this->loadRelations('destCouponFolders',$this,"altCouponFormat",$data['id'],'event','couponfolder','coupon_folders','',true);

		//
		//	load product folders attached to this folder
		//
		$data['relatedDestProducts'] = $this->loadRelations('destProducts',$this,"altProductFormat",$data['id'],'event','product','product','and deleted = 0 and enabled = 1 and published = 1',true);
		$frmFields['relatedDestProducts']['options'] = $this->fetchOptionsTest('select id as code,concat(code," - ",name) as value from product where deleted = 0 and enabled = 1 and published = 1 and id in (%s) order by code',implode(',',array_merge(array(0),$data['relatedDestProducts'])));

		//
		//	load products attached to this folder
		//
		$frmFields['destProductFolders']['options'] = $this->nodeSelect(0,'product_folders',2,false);
		$data['destProductFolders'] = $this->loadRelations('destProductFolders',$this,"altProductFormat",$data['id'],'event','productfolder','product_folders','',true);

		//
		//	load store folders attached to this folder
		//
		$data['destStores'] = $this->loadRelations('destStores',$this,"altStoreFormat",$data['id'],'event','store','stores','and deleted = 0 and enabled = 1 and published = 1',false);
		$frmFields['destStores']['options'] = $this->fetchOptionsTest('select id as code,name as value from stores where deleted = 0 and enabled = 1 and published = 1 and id in (%s) order by name',implode(',',array_merge(array(0),$data['destStores'])));

		//
		//	load Stores attached to this folder
		//
		$frmFields['destStoreFolders']['options'] = $this->nodeSelect(0,'store_folders',2,false);
		$data['destStoreFolders'] = $this->loadRelations('destStoreFolders',$this,"altStoreFormat",$data['id'],'event','storefolder','store_folders','',true);

		$data['destNews'] = $this->loadRelations('destNews',$this,"altNewsFormat",$data['id'],'event','news','news','and deleted = 0 and enabled = 1 and published = 1',false);
		$frmFields['destNews']['options'] = $this->fetchOptionsTest('select id as code,title as value from news where deleted = 0 and enabled = 1 and published = 1 and id in (%s) order by title',implode(',',array_merge(array(0),$data['destNews'])));
		$frmFields['destNewsFolders']['options'] = $this->nodeSelect(0,'news_folders',2,false);
		$data['destNewsFolders'] = $this->loadRelations('destNewsFolders',$this,"altNewsFormat",$data['id'],'event','newsfolder','news_folders','',true);

		$data['imagesel_a'] = $data['image1'];
		$data['imagesel_b'] = $data['image2'];
		$data['imagesel_c'] = $data['image3'];
		$data['imagesel_d'] = $data['image4'];

		$form->addTag('addresses',$this->loadAddresses($data['id']),false);
		$form->addData($data);
		$form->addTag('recurringform',$this->recurringEvent($data),false);
		$status = 'false';	//assume it failed

		$customFields = new custom();
		if (method_exists($customFields,'eventDisplay')) {
			$custom = $customFields->eventDisplay();
			$form->addTag('customTab',sprintf('<li><a href="#tabs-custom">%s</a></li>',$custom['description']),false);
			$html = $form->getHTML();
			$html = str_replace('%%customInfo%%',$custom['form'],$html);
			$form->setHTML($html);
			$frmFields = array_merge($frmFields,$custom['fields']);
		}

		$frmFields = $form->buildForm($frmFields);
		if (count($_POST) > 0 && array_key_exists('addContent',$_POST)) {
			$_POST['imagesel_a'] = $_POST['image1'];
			$_POST['imagesel_b'] = $_POST['image2'];
			$_POST['imagesel_c'] = $_POST['image3'];
			$_POST['imagesel_d'] = $_POST['image4'];
			$form->addData($_POST);
			$form->validate();	// set checkboxes for recurring first?
			//
			//	add in the recurrence subform
			//
			switch($form->getData('recurring_type')) {
			case 'Daily':
				$subflds = $this->getFields('recurrenceDaily');
				break;
			case 'Weekly':
				$subflds = $this->getFields('recurrenceDaily');
				break;
			case 'Monthly':
				$subflds = $this->getFields('recurrenceDaily');
				break;
			case 'Annual':
				$subflds = $this->getFields('recurrenceDaily');
				break;
			default:
				$subflds = array();
				//$form->setData('recurring_type',0);
				$form->setData('recurring_end_date','0000-00-00');
				break;
			}
			$frmFields = array_merge($frmFields,$subflds);
			$subflds = $this->getFields('recurringEvent');
			$frmFields = array_merge($frmFields,$subflds);
			$this->logMessage("addContent",sprintf("add subform fields [%s] formFields [%s]",print_r($subflds,true),print_r($frmFields,true)),2);
			$subflds = $form->buildForm($subflds);
			$valid = $form->validate();
			$ed = $form->getData('end_date');
			$recur_ed = $form->getData('recurring_end_date');
			if ($valid) {
				if ((!($ed == '0000-00-00' || $ed == '')) &&  ($ed < $form->getData('start_date'))) {
					$valid = false;
					$form->addError('End date must be after the Start Date');
				}
			}
			if ($form->getData('separate_events') != 0) {
				if ($recur_ed == '0000-00-00' || $recur_ed == '') {
					$valid = false;
					$form->addError('Recurring End date is required for creating separate events');
				}
			}
			if ($valid) {
				$id = $_POST['p_id'];
				unset($frmFields['p_id']);
				unset($frmFields['options']);
				$tmp = $form->getData('recurring_weekdays');
				if (is_array($tmp)) {
					$mask = 0;
					foreach($tmp as $key=>$value) {
						$mask |= $value;
					}
					$form->setData('recurring_weekdays',$mask);
				}
				$flds = array();
				foreach($frmFields as $key=>$fld) {
					if (!(array_key_exists('database',$fld) && $fld['database'] == false)) {
						$flds[$fld['name']] = $form->getData($fld['name']);	//$_REQUEST[$fld['name']];
					}
				}
				if ($form->getData('separate_events') != 0) {
					//
					//	creating separate db records that will be edited individually
					//	other records created in duplicateEvent
					//
			    $flds['recurring_event'] = 0;
    			$flds['recurring_frequency'] = 0;
    			$flds['recurring_weekdays'] = 0;
    			$flds['recurring_by_position'] = 0;
    			$flds['recurring_position'] = 0;
    			$flds['recurring_type'] = '';
    			$flds['recurring_end_date'] = '0000-00-00';
    			$flds['end_date'] = '0000-00-00';
				}
				$this->logMessage('addContent',sprintf('flds [%s] form [%s]',print_r($flds,true),print_r($form,true)),4);
				if ($id == 0) {
					$flds['created'] = date('c');
					$stmt = $this->prepare(sprintf('insert into %s(%s) values(%s)', $this->m_content, implode(',',array_keys($flds)), str_repeat('?, ', count($flds)-1).'?'));
					$stmt->bindParams(array_merge(array(str_repeat('s', count($flds))),array_values($flds)));
				}
				else {
					$stmt = $this->prepare(sprintf('update %s set %s=? where id = %d', $this->m_content, implode('=?, ',array_keys($flds)),$data['id']));
					$stmt->bindParams(array_merge(array(str_repeat('s', count($flds))),array_values($flds)));
				}
				$this->beginTransaction();
				if ($status = $stmt->execute()) {
					if ($id == 0) $id = $this->insertId();
					$destFolders = array_key_exists("destFolders",$_REQUEST) ? $_REQUEST['destFolders'] : array();
					if (!is_array($destFolders)) $destFolders = array($destFolders);
					//
					//	delete folders we are no longer a member of
					//
					$this->execute(sprintf('delete from %s where event_id = %d and folder_id not in (%s)', $this->m_junction, $id,implode(',',$destFolders)));
					$sql = sprintf('select id from %s where id in (%s) and id not in (select folder_id from %s where event_id = %d and folder_id in (%s))',
					$this->m_tree,implode(',',$destFolders),$this->m_junction,$id,implode(',',$destFolders));
					$new = $this->fetchScalarAllTest($sql);
					foreach($new as $key=>$folder) {
						$obj = new preparedStatement(sprintf('insert into %s(event_id,folder_id) values(?,?)',$this->m_junction));
						$obj->bindParams(array('dd',$id,$folder));
						$status = $status && $obj->execute();
					}

					$destSearch = array_key_exists('destSearch',$_POST) ? $_POST['destSearch'] : array(0);
					if (!is_array($destSearch)) $destSearch = array($destSearch);
					$this->execute(sprintf('delete from event_by_search_group where event_id = %d and folder_id not in (%s)', $id, implode(',',$destSearch)));
					$new = $this->fetchScalarAllTest('select id from search_groups where id in (%s) and id not in (select folder_id from event_by_search_group where event_id = %d and folder_id in (%s))',
						implode(',',$destSearch),$id,implode(',',$destSearch));
					foreach($new as $key=>$folder) {
						$obj = new preparedStatement('insert into event_by_search_group(event_id,folder_id) values(?,?)');
						$obj->bindParams(array('dd',$id,$folder));
						$status = $status && $obj->execute();
					}



					$this->logMessage("addContent",sprintf("pre updateRelations status [%s]",$status),2);
					$status = $status && $this->updateRelations('destCoupons',$id,'event','coupon','coupons',true,true);
					$status = $status && $this->updateRelations('destCouponFolders',$id,'event','couponfolder','coupon_folders',true,false);
					$status = $status && $this->updateRelations('relatedDestProducts',$id,'event','product','product',true,true);
					$status = $status && $this->updateRelations('destProductFolders',$id,'event','productfolder','product_folders',true,false);
					$status = $status && $this->updateRelations('destStores',$id,'event','store','stores',false,true);
					$status = $status && $this->updateRelations('destStoreFolders',$id,'event','storefolder','store_folders',true,false);
					$status = $status && $this->updateRelations('destNews',$id,'event','news','news',false,true);
					$status = $status && $this->updateRelations('destNewsFolders',$id,'newsfolder','event','news_folders',false,false);
					$status = $status && $this->handleRecurringEvents($id,$form->getAllData());
					if (array_key_exists('event_type',$data) && count($data['event_type']) > 0) {
						$status = $status && $this->execute(sprintf('delete from event_types where event_id = %d and event_type not in (%s)',$id,implode(',',array_merge(array(0),$data['event_type']))));
						$sql = sprintf('select event_type from event_types where event_id = %d and event_type in (%s)',$id,implode(',',array_merge(array(0),$data['event_type'])));
						$currIds = $this->fetchScalarAllTest($sql);
						$diff = array_diff($data['event_type'],$currIds);
						$this->logMessage('addContent',sprintf('sql [%s] currIds [%s] data [%s] diff [%s]',$sql,print_r($currIds,true),print_r($data['event_type'],true),print_r($diff,true)),1);
						foreach($diff as $key=>$value) {
							$stmt = $this->prepare(sprintf('insert into event_types(event_id,event_type) values(?,?)'));
							$stmt->bindParams(array('ii',$id,$value));
							$status = $status && $stmt->execute();
						}
					}
					else {
						$status = $status && $this->execute(sprintf('delete from event_types where event_id = %d',$id));
					}
					if ($status) {
						$this->commitTransaction();
						return $this->ajaxReturn(array(
							'status' => 'true',
							'url' => sprintf('/modit/calendar?p_id=%d',$destFolders[0])
						));
					}
					else {
						$this->rollbackTransaction();
						$this->addError('An Error occurred');
					}
				} else {
					$this->rollbackTransaction();
					$this->addError('An Error occurred');
				}
			}
			else
				$this->addError('Form Validation Failed');
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
		if (array_key_exists('src',$_REQUEST)) $src = $_REQUEST['src'];
		if (array_key_exists('dest',$_REQUEST)) $dest = $_REQUEST['dest'];
		if ($_REQUEST['type'] == 'tree') {
			if ($src == 0 || $dest == 0 || !array_key_exists('type',$_REQUEST)) {
				$this->addError('Events cannot be moved from search mode, only copied');
				return $this->ajaxReturn(array('status' => 'false'));
			}
			$curr = $src;//$this->fetchScalarTest('select event_id from %s where id = %d',$this->m_junction,$src);
			if ($curr > 0 && $folder = $this->fetchSingleTest('select * from %s where id = %d',$this->m_tree,$dest)) {
				$status = true;
				if (array_key_exists('move',$_REQUEST)) {
					if ($src == 0) {
						$this->addError('Events cannot be moved from search mode, only copied');
						return $this->ajaxReturn(array($status=>'false'));
					}
					$curr = $this->fetchScalarTest('select event_id from %s where id = %d',$this->m_junction,$src);
					//
					//	move it - delete source folder
					//
					$this->logMessage('moveArticle', sprintf('moving event %d to folder %d',$src,$dest),2);
					$this->beginTransaction();
					if ($status = $this->execute(sprintf('delete from %s where id = %d', $this->m_junction, $src))) {
						if (!$this->fetchSingleTest('select * from %s where event_id = %d and folder_id = %d',$this->m_junction,$curr,$dest)) {
							$obj = new preparedStatement(sprintf('insert into %s(event_id,folder_id) values(?,?)',$this->m_junction));
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
					$this->logMessage('moveArticle', sprintf('cloning event %d to folder %d',$curr,$dest),2);
					if (!($this->fetchSingleTest('select * from %s where event_id = %d and folder_id = %d',$this->m_junction,$curr,$dest))) {
						$obj = new preparedStatement(sprintf('insert into %s(event_id,folder_id) values(?,?)',$this->m_junction));
						$obj->bindParams(array('dd',$curr,$dest));
						$status = $obj->execute();
					}
				}
			} else {
				$status = false;
				$this->addError('Could not locate the destination folder');
			}
		}
		return $this->ajaxReturn(array(
				'status'=>$status?'true':'false'
		));
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
						$this->execute(sprintf('delete from %s where event_id = %d',$this->m_junction,$_REQUEST['e_id']));
						$this->execute(sprintf('update %s set deleted = 1 where id = %d',$this->m_content,$_REQUEST['e_id']));
						break;
					case 'one':
						$status = $this->execute(sprintf('update %s set deleted = 1 where id = %d',$this->m_junction,$_REQUEST['j_id']));
						$ct = $this->fetchScalarTest('select count(0_ from %s where event_id = %d',$this->m_junction,$_REQUEST['e_id']);
						if ($ct == 0)
							$this->execute(sprintf('update %s set deleted = 1 where id = %d',$this->m_content,$_REQUEST['e_id']));
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
    function myProductList() {
		if (array_key_exists('f_id',$_REQUEST) && $_REQUEST['f_id'] > 0) {
			$tmp = parent::productList($_REQUEST['f_id'],$this->getTemplate('productList'),$this->getFields('productList'));
			return $this->ajaxReturn(array('status'=>'true','html'=>$tmp));
		}
		else return $this->ajaxReturn(array('status'=>'true','html'=>''));
	}

    /**
     * @return false|string|void
     * @throws phpmailerException
     */
    function myCouponList() {
		if (array_key_exists('f_id',$_REQUEST)) {
			$tmp = parent::couponList($_REQUEST['f_id'],$this->getTemplate('couponByEventList'),$this->getFields('couponByEventList'));
			return $this->ajaxReturn(array('status'=>'true','html'=>$tmp));
		}
	}

    /**
     * @return false|string|void
     * @throws phpmailerException
     */
    function myNewsList() {
		if (array_key_exists('f_id',$_REQUEST)) {
			$tmp = parent::newsList($_REQUEST['f_id'],$this->getTemplate('newsByEventList'),$this->getFields('newsByEventList'));
			return $this->ajaxReturn(array('status'=>'true','html'=>$tmp));
		}
	}

    /**
     * @return false|string|void
     * @throws phpmailerException
     */
    function myBlogList() {
		if (array_key_exists('f_id',$_REQUEST)) {
			$tmp = parent::blogList($_REQUEST['f_id'],$this->getTemplate('blogByEventList'),$this->getFields('blogByEventList'));
			return $this->ajaxReturn(array('status'=>'true','html'=>$tmp));
		}
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
    function addEvent($fromMain = false) {
		$form = new Forms();
		$form->init($this->getTemplate('addEvent'));
		if ($fromMain)
			return $form->show();
		else
			return $this->show($form->show());
	}

    /**
     * @param $sd
     * @param $ed
     * @return array|mixed|string|string[]
     * @throws phpmailerException
     */
    function loadDailyEvents($sd, $ed) {
		$eventForm = new Forms();
		$eventForm->init($this->getTemplate('dayEvent'));
		$eventFields = $eventForm->buildForm($this->getFields('dayEvent'));
		$dayForm = new Forms();
		$dayForm->init($this->getTemplate('dayEvents'));
		$dayFields = $dayForm->buildForm($this->getFields('dayEvents'));
		$sql = sprintf('select e.*, f.id as j_id from %s e, %s f, event_dates d where e.deleted = 0 and e.id = f.event_id and f.folder_id = %d and d.event_id = e.id and d.event_date = "%s" order by start_date, start_time',
			$this->m_content, $this->m_junction, $_REQUEST['p_id'],$sd);
		$events = $this->fetchAllTest($sql);
		$this->logMessage('loadDailyEvents',sprintf('sql [%s] count[%d]',$sql,count($events)),2);
		$ct = 0;
		$day = array();
		foreach($events as $event) {
			$eventForm->addData($this->format($event));
			if ($ct %2 == 0) 
				$eventForm->addTag('zebra','even');
			else
				$eventForm->addTag('zebra','odd');
			$day[] = $eventForm->show();
			$ct+=1;
		}
		$dayForm->addTag('class',$sd == date('Y-m-d') ? 'today':'');
		$dayForm->addTag('events',implode('',$day),false);
		$dayForm->addTag('day',date('d',strtotime($sd)));
		$this->logMessage('loadDailyEvents',sprintf('return form [%s]',print_r($dayForm,true)),4);
		return $dayForm->show();
	}

    /**
     * @param $sd
     * @param $ed
     * @return array|mixed|string|string[]
     * @throws phpmailerException
     */
    function loadWeekEvents($sd, $ed) {
		$output = array();
		$eventForm = new Forms();
		$eventForm->init($this->getTemplate('weekEvent'));
		$eventFields = $eventForm->buildForm($this->getFields('weekEvent'));
		$dayForm = new Forms();
		$dayForm->init($this->getTemplate('weekDay'));
		$dayFields = $dayForm->buildForm($this->getFields('weekDay'));
		$weekForm = new Forms();
		$weekForm->init($this->getTemplate('weekEvents'));
		$weekFields = $weekForm->buildForm($this->getFields('weekEvents'));
		while ($sd <= $ed) {
			$day = array();
			$sql = sprintf('select e.*, f.id as j_id from %s e, %s f, event_dates d where e.deleted = 0 and e.id = f.event_id and f.folder_id = %d and d.event_id = e.id and d.event_date = "%s" order by start_date, start_time',
				$this->m_content, $this->m_junction, $_REQUEST['p_id'],$sd);
			$events = $this->fetchAllTest($sql);
			$this->logMessage('loadWeekEvents',sprintf('sql [%s] count[%d]',$sql,count($events)),2);
			$ct = 0;
			foreach($events as $event) {
				$eventForm->addData($this->format($event));
				if ($ct %2 == 0) 
					$eventForm->addTag('zebra','even');
				else
					$eventForm->addTag('zebra','odd');
				$day[] = $eventForm->show();
				$ct+=1;
			}
			$dayForm->addTag('class',$sd == date('Y-m-d') ? 'today':'');
			$dayForm->addTag('events',implode('',$day),false);
			$dayForm->addTag('day',date('d',strtotime($sd)));
			if (count($events) > 0)
				$dayForm->addTag('eventCount',sprintf('%d event%s',count($events),count($events) > 1?'s':''));
			else 
				$dayForm->addTag('eventCount','');
			$week[] = $dayForm->show();
			$sd = date('Y-m-d',strtotime(sprintf('%s + 1 day',$sd)));
		}
		$weekForm->addTag('days',implode('',$week),false);
		$output[] = $weekForm->show();
		$this->logMessage('loadWeekEvents',sprintf('return form [%s]',print_r($weekForm,true)),4);
		return $weekForm->show();
	}

    /**
     * @param $sd
     * @param $ed
     * @return array|mixed|string|string[]
     * @throws phpmailerException
     */
    function loadMonthEvents($sd, $ed) {
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
		for ($i = 0; $i < $wk; $i++) {
			//
			//	build empty days prior to the start of the month
			//
			$week[] = '<td></td>';
		}
		$wk = strftime('%U',strtotime($sd));
		while ($sd <= $ed) {
			$currWk = strftime('%U',strtotime($sd));
			if ($currWk != $wk) {
				$this->logMessage('loadMonthEvents',sprintf('new week week [%s] currWk [%s] wk [%s] sd [%s] ed [%s]',print_r($week,true),$currWk,$wk,$sd,$ed),2);
				$weekForm->addTag('days',implode('',$week),false);
				$output[] = $weekForm->show();
				$wk = $currWk;
				$week = array();
			}
			$day = array();
			$sql = sprintf('select e.*, f.id as j_id from %s e, %s f, event_dates d where e.deleted = 0 and e.id = f.event_id and f.folder_id = %d and d.event_id = e.id and d.event_date = "%s" order by start_date, start_time',
				$this->m_content, $this->m_junction, $_REQUEST['p_id'],$sd);
			$events = $this->fetchAllTest($sql);
			$this->logMessage('loadMonthEvents',sprintf('sql [%s] count[%d]',$sql,count($events)),2);
			$ct = 0;
			foreach($events as $event) {
				$eventForm->addTag('zebra',$ct % 2 == 0 ? 'even':'odd');
				$eventForm->addData($this->format($event));
				$day[] = $eventForm->show();
				$ct += 1;
			}
			$dayForm->addTag('class',$sd == date('Y-m-d') ? 'today':'');
			$dayForm->addTag('events',implode('',$day),false);
			$dayForm->addTag('day',date('d',strtotime($sd)));
			if (count($events) > 0)
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
		$this->logMessage('loadMonthEvents',sprintf('return form [%s]',print_r($monthForm,true)),4);
		return $monthForm->show();
	}

    /**
     * @param $evt
     * @return mixed
     */
    function format($evt) {
		if ($evt['end_date'] != '0000-00-00') {
			$sd = date_parse($evt['start_date']);
			$ed = date_parse($evt['end_date']);
			if ($sd['month'] == $ed['month'] && $sd['year'] == $ed['year'])
				$evt['dates'] = sprintf('%d-%d %s',$sd['day'],$ed['day'],date('M-Y',strtotime($evt['start_date'])));
			else if ($sd['year'] == $ed['year'])
				$evt['dates'] = sprintf('%s - %s',date('d-M',strtotime($evt['start_date'])),date('d-M Y',strtotime($evt['end_date'])));
			else
				$evt['dates'] = sprintf('%s - %s ',date('d-M-Y',strtotime($evt['start_date'])),date('d-M-Y',strtotime($evt['end_date'])));
		}
		else
			$evt['dates'] = sprintf('%s',date('d-M-Y',strtotime($evt['start_date'])));
		$evt['times'] = '';
		$evt['recurs']  = $evt['recurring_type'];
		if ($evt['start_time'] != '00:00') $evt['times'] = date('h:i a',strtotime($evt['start_time']));
		if ($evt['end_time'] != '00:00') $evt['times'] .= sprintf(' - %s',date('h:i a',strtotime($evt['end_time'])));
		return $evt;
	}

    /**
     * @return false|string
     * @throws phpmailerException
     */
    function editAddress() {
		if (!array_key_exists('o_id',$_REQUEST))
			return $this->ajaxReturn(array('status'=>'false','html'=>'No id passed'));
		$a_id = $_REQUEST['a_id'];
		$o_id = $_REQUEST['o_id'];
		if (!($data = $this->fetchSingleTest('select * from addresses where id = %d and ownertype = "event" and ownerid = %d',$a_id,$o_id))) {
			$data = array('id'=>0,'ownertype'=>'event','ownerid'=>$o_id);
			$addresses = array();
		}
		$form = new Forms();
		$form->init($this->getTemplate('editAddress'),array('name'=>'editAddress'));
		$frmFields = $form->buildForm($this->getFields('editAddress'));
		$form->addData($data);
		if (count($_POST) > 0 && array_key_exists('editAddress',$_POST)) {
			$form->addData($_POST);
			if ($form->validate()) {
				unset($frmFields['options']);
				foreach($frmFields as $key=>$fld) {
					if (!(array_key_exists('database',$fld) && $fld['database'] == false)) {
						$flds[$fld['name']] = $form->getData($fld['name']);
					}
				}
				if (array_key_exists("geocode",$_REQUEST) && $_REQUEST["geocode"] == 1) {
					$valid = $this->geocode($flds,$flds["latitude"],$flds["longitude"]);
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
					$this->commitTransaction();
					$form->init($this->getTemplate('editAddressSuccess'));
				}
				else {
					$this->rollbackTransaction();
					$this->addError('An error occurred updating the database');
				}
			}
			else $form->addFormError('Form validation Failed');
		}
		return $this->ajaxReturn(array('status'=>'true','html'=>$form->show()));
	}

    /**
     * @return false|string|void
     * @throws phpmailerException
     */
    function deleteAddress() {
		if (array_key_exists('a_id',$_REQUEST) && array_key_exists('o_id',$_REQUEST)) {
			$this->logMessage('deleteAddress',sprintf('deleting id [%d] owner [%d]',$_REQUEST['a_id'],$_REQUEST['o_id']),1);
			if ($data = $this->fetchSingleTest('select * from addresses where ownertype = "event" and id = %d and ownerid = %d',$_REQUEST['a_id'],$_REQUEST['o_id'])) {
				$this->execute(sprintf('update addresses set deleted = 1 where id = %d',$_REQUEST['a_id']));
				$form = new Forms();
				$this->addMessage('The address was deleted');
				$form->init($this->getTemplate('deleteAddress'));
				$form->addData($_REQUEST);
				return $this->ajaxReturn(array('status'=>'true','html'=>$form->show()));
			}
		}
	}

    /**
     * @return false|string
     * @throws phpmailerException
     */
    function myStoreList() {
		if (array_key_exists('f_id',$_REQUEST) && $_REQUEST['f_id'] > 0) {
			$tmp = parent::storeList($_REQUEST['f_id'],$this->getTemplate('storeList'),$this->getFields('storeList'));
			return $this->ajaxReturn(array('status'=>'true','html'=>$tmp));
		}
		else return $this->ajaxReturn(array('status'=>'true','html'=>''));
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
     * @param $method
     * @return bool
     * @throws phpmailerException
     */
    function hasFunctionAccess($method) {
		if (parent::hasFunctionAccess($method)) return true;
		return true;
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
			if (array_key_exists('formData',$_SESSION) && array_key_exists('calendarSearchForm', $_SESSION['formData']))
				$form->addData($_SESSION['formData']['calendarSearchForm']);
		return $form->show();
	}

    /**
     * @param int $fromMain
     * @return array|false|mixed|string|string[]|void
     * @throws phpmailerException
     */
    function moduleStatus($fromMain = 0) {
		if (array_key_exists('formData',$_SESSION) && array_key_exists('calendarSearchForm', $_SESSION['formData'])) {
			$_POST = $_SESSION['formData']['calendarSearchForm'];
			$msg = '';
		}
		else {
			$ct = $this->fetchScalarTest('select count(0) from %s where published = 0 and deleted = 0',$this->m_content);
			if ($ct == 0) {
				$_POST = array('showSearchForm'=>1,'deleted'=>0,'sortby'=>'created','sortorder'=>'desc','pager'=>$this->m_pagination);
				$msg = "Showing last added events";
			}
			else {
				$_POST = array('showSearchForm'=>1,'published'=>0,'deleted'=>0,'sortby'=>'created','sortorder'=>'desc','pager'=>$this->m_pagination);
				$msg = "Showing unpublished events";
			}
		}
		$result = $this->showSearchForm($fromMain,$msg);
		return $result;
	}

    /**
     * @param array $event_data
     * @return array|false|mixed|string|string[]
     * @throws phpmailerException
     */
    function recurringEvent($event_data = array()) {
		$fromMain = count($event_data) > 0;
		$form = new Forms();
		$form->init($this->getTemplate('recurringEvent'));
		$flds = $form->buildForm($this->getFields('recurringEvent'));
		$form->addData($event_data);
		if (count($_REQUEST) > 0 && array_key_exists('recurrenceForm',$_REQUEST)) {
			$form->addData($_REQUEST);
		}
		$subform = new Forms();
		switch($form->getData('recurring_type')) {
		case 'Daily':
			$subform->init($this->getTemplate('recurrenceDaily'));
			$subflds = $subform->buildForm($this->getFields('recurrenceDaily'));
			$options = array();
			for ($x = 0; $x < 365; $x++) {
				$options[$x+1] = $x+1;
			}
			$subform->getField('recurring_frequency')->addOptions($options);
			break;
		case 'Weekly':
			$subform->init($this->getTemplate('recurrenceWeekly'));
			$subflds = $subform->buildForm($this->getFields('recurrenceWeekly'));
			for ($x = 0; $x < 52; $x++) {
				$options[$x+1] = $x+1;
			}
			$subform->getField('recurring_frequency')->addOptions($options);
			break;
		case 'Monthly':
			$subform->init($this->getTemplate('recurrenceMonthly'));
			$subflds = $subform->buildForm($this->getFields('recurrenceMonthly'));
			for ($x = 0; $x < 12; $x++) {
				$options[$x+1] = $x+1;
			}
			$subform->getField('recurring_frequency')->addOptions($options);
			break;
		case 'Annual':
			$subform->init($this->getTemplate('recurrenceAnnually'));
			$subflds = $subform->buildForm($this->getFields('recurrenceAnnually'));
			for ($x = 0; $x < 5; $x++) {
				$options[$x+1] = $x+1;
			}
			$subform->getField('recurring_frequency')->addOptions($options);
			break;
		default:
			break;
		}
		$subform->addData($event_data);
		if (count($_REQUEST) > 0 && array_key_exists('recurrenceForm',$_REQUEST)) {
			$subform->addData($_REQUEST);
		}
		$mask = $subform->getData('recurring_weekdays');
		if ($mask != 0) {
			$tmp = array();
			$this->logMessage("recurringEvent",sprintf("mask [%d] tmp [%s] form [%s]",$mask,print_r($tmp,true),print_r($subform,true)),2);
			for ($i = 0; $i < 7; $i++) {
				if (($mask & pow(2,$i)) != 0) {
					$tmp[] = pow(2,$i);
				}
			}
			$subform->setData('recurring_weekdays',$tmp);
			$this->logMessage("recurringEvent",sprintf("form after [%s]",print_r($subform,true)),2);
		}
		$form->addTag('recurrenceSubForm',$subform->show(),false);
		if ($fromMain) {
			return $form->show();
		}
		else
			return $this->ajaxReturn(array('status'=>'true','html'=>$form->show()));
	}

    /**
     * @param $id
     * @param $data
     * @return bool|void
     * @throws Exception
     */
    function handleRecurringEvents($id, $data) {
		//
		//	handle recurring events
		//
		$status = true;
		$this->execute(sprintf('delete from event_dates where event_id = %d',$id));
		if ($data['recurring_event'] == 0) {
			//
			//	just a regular event. add days from start to end
			//
			$sd = date('Y-m-d',strtotime($data['start_date']));
			if ($data['end_date'] == '0000-00-00' || $data['end_date'] == '')
				$ed = $sd;
			else
				$ed = date('Y-m-d',strtotime($data['end_date']));
			$stmt = $this->prepare(sprintf('insert into event_dates(event_id,event_date) values(?,?)'));
			while($sd <= $ed && $status) {
				$stmt->bindParams(array('ds', $id,$sd));
				$status = $status && $stmt->execute();
				$sd = date('Y-m-d',strtotime(sprintf('%s + 1 days',$sd)));
			}
			return $status;
		}
		$d1 = new DateTime($data['start_date']);
		if ($data['end_date'] != '' && $data['end_date'] != '0000-00-00')
			$d2 = new DateTime($data['end_date']);
		else $d2 = $d1;
		$d3 = $d2->diff($d1);
		$span = $d3->days+1;
		$sd = date('Y-m-d',strtotime($data['start_date']));
		$ed = date('Y-m-d',strtotime($data['recurring_end_date']));
		$this->logMessage("handleRecurringEvents",sprintf("d1 [%s] d2 [%s] diff [%s]",print_r($d1,true),print_r($d2,true),print_r($span,true)),2);
		switch($data['recurring_type']) {
		case 'Daily':
			if ($span > $data['recurring_frequency']) {
				$this->addError('The # of days must be less than the recurring frequency');
				return false;
			}
			while($sd <= $ed && $status) {
				for($x = 0; $x < $span; $x++) {
					$dt = date('Y-m-d',strtotime(sprintf('%s + %d day',$sd,$x)));
					$stmt = $this->prepare(sprintf('insert into event_dates(event_id,event_date) values(?,?)'));
					if ($data['separate_events'] != 0 && $dt != $data['start_date']) {
						$new_id = $this->duplicateEvent($id,$dt);
						$stmt->bindParams(array('ds', $new_id,$dt));
					}
					else
						$stmt->bindParams(array('ds', $id,$dt));
					$status = $status && $stmt->execute();
				}
				$sd = date('Y-m-d',strtotime(sprintf('%s + %d days',$sd,$data['recurring_frequency'])));
			}
			break;
		case 'Weekly':
			//
			//	old logic, now applies to 1st event as well - 1st ocurrence can start on any day, then we have to reproduce the recurring based on the weekday mask
			//
			//for($x = 0; $x < $span; $x++) {
			//	$dt = date('Y-m-d',strtotime(sprintf('%s + %d day',$sd,$x)));
			//	$stmt = $this->prepare(sprintf('insert into event_dates(event_id,event_date) values(?,?)'));
			//	if ($data['separate_events'] != 0 && $dt != $data['start_date']) {
			//		$new_id = $this->duplicateEvent($id,$dt);
			//		$stmt->bindParams(array('ds', $new_id,$dt));
			//	}
			//	else
			//		$stmt->bindParams(array('ds', $id,$dt));
			//	$status = $status && $stmt->execute();
			//}
			//$sd = date('Y-m-d',strtotime(sprintf('%s + %d weeks',$sd,$data['recurring_frequency'])));
			$tmp = date('w',strtotime($sd));
			$sd = date('Y-m-d',strtotime(sprintf('%s - %d days',$sd,$tmp)));
			$this->logMessage("handleRecurringEvents",sprintf("adjusted start date [%s] week start [%d] weekdays [%s]",$sd,$tmp,print_r($data['recurring_weekdays'],true)),2);
			while($sd <= $ed && $status) {
				for($dy = 0; $dy < 7; $dy++) {
					$this->logMessage("handleRecurringEvents",sprintf("dy [%d] pow [%d] mask [%d]",$dy,pow(2,$dy),$data['recurring_weekdays']),4);
					if (($data['recurring_weekdays'] & pow(2,$dy)) == pow(2,$dy)) {
						$tmp = date('Y-m-d',strtotime(sprintf('%s + %d days',$sd,$dy)));
						//for($x = 0; $x < $span; $x++) {
							$dt = $tmp;	//date('Y-m-d',strtotime(sprintf('%s + %d day',$tmp,$x)));
							if ($dt <= $ed) {
								$stmt = $this->prepare(sprintf('insert into event_dates(event_id,event_date) values(?,?)'));
								if ($data['separate_events'] != 0 && $dt != $data['start_date']) {
									$new_id = $this->duplicateEvent($id,$dt);
									$stmt->bindParams(array('ds', $new_id,$dt));
								}
								else
									$stmt->bindParams(array('ds', $id,$dt));
								$status = $status && $stmt->execute();
							}
						//}
					}
				}
				$sd = date('Y-m-d',strtotime(sprintf('%s + %d weeks',$sd,$data['recurring_frequency'])));
			}
			break;
		case 'Monthly':
			//
			//	1st ocurrence can start on any day, then we have to reproduce the recurring based on the weekday mask
			//
			//for($x = 0; $x < $span; $x++) {
			//	$dt = date('Y-m-d',strtotime(sprintf('%s + %d day',$sd,$x)));
			//	$stmt = $this->prepare(sprintf('insert into event_dates(event_id,event_date) values(?,?)'));
			//	if ($data['separate_events'] != 0 && $dt != $data['start_date']) {
			//		$new_id = $this->duplicateEvent($id,$dt);
			//		$stmt->bindParams(array('ds', $new_id,$dt));
			//	}
			//	else
			//		$stmt->bindParams(array('ds', $id,$dt));
			//	$status = $status && $stmt->execute();
			//}
			//$sd = date('Y-m-d',strtotime(sprintf('%s + %d months',$sd,$data['recurring_frequency'])));
			if ($data['recurring_by_position'] == 0) {
				//
				//	just straight same days ie. 1st -> 1st
				//
				while($sd <= $ed && $status) {
					for($dy = 0; $dy < 7; $dy++) {
						$this->logMessage("handleRecurringEvents",sprintf("dy [%d] pow [%d] mask [%d]",$dy,pow(2,$dy),$data['recurring_weekdays']),4);
						if (($data['recurring_weekdays'] & pow(2,$dy)) == pow(2,$dy)) {
							$tmp = date('Y-m-d',strtotime(sprintf('%s + %d days',$sd,$dy)));
							for($x = 0; $x < $span; $x++) {
								$dt = date('Y-m-d',strtotime(sprintf('%s + %d day',$sd,$x)));
								$stmt = $this->prepare(sprintf('insert into event_dates(event_id,event_date) values(?,?)'));
								if ($data['separate_events'] != 0 && $dt != $data['start_date']) {
									$new_id = $this->duplicateEvent($id,$dt);
									$stmt->bindParams(array('ds', $new_id,$dt));
								}
								else
									$stmt->bindParams(array('ds', $id,$dt));
								$status = $status && $stmt->execute();
							}
						}
					}
					$sd = date('Y-m-d',strtotime(sprintf('%s + %d months',$sd,$data['recurring_frequency'])));
				}
			}
			else {
				//
				//	most complicated - copy to 1st monday of the month, compounded with multiple days in the week
				//
				$tmp = date('w',strtotime($sd));
				//$sd = date('Y-m-d',strtotime(sprintf('%s - %d days',$sd,$tmp-1)));
				$this->logMessage("handleRecurringEvents",sprintf("adjusted start date [%s] week start [%d]",$sd,$tmp),2);
				if ($data['recurring_weekdays'] == 0) {
					//
					//	no days specfied - juss copy to the same day[s] of the week
					//
					$data['recurring_weekdays'] = pow(2,$tmp-1);
				}
				$work_date = $this->adjustWeek($sd,$data['recurring_position'],$data['recurring_weekdays']);
				while ($sd <= $ed && $status) {
					for($dy = 0; $dy < 7; $dy++) {
						$this->logMessage("handleRecurringEvents",sprintf("dy [%d] pow [%d] mask [%d]",$dy,pow(2,$dy),$data['recurring_weekdays']),4);
						if (($data['recurring_weekdays'] & pow(2,$dy)) == pow(2,$dy)) {
							$tmp = date('Y-m-d',strtotime(sprintf('%s + %d days',$work_date,$dy)));
							for($x = 0; $x < $span; $x++) {
								$dt = date('Y-m-d',strtotime(sprintf('%s + %d day',$tmp,$x)));
								$stmt = $this->prepare(sprintf('insert into event_dates(event_id,event_date) values(?,?)'));
								if ($data['separate_events'] != 0 && $dt != $data['start_date']) {
									$new_id = $this->duplicateEvent($id,$dt);
									$stmt->bindParams(array('ds', $new_id,$dt));
								}
								else
									$stmt->bindParams(array('ds', $id,$dt));
								$status = $status && $stmt->execute();
							}
						}
					}
					$tmp = $sd;
					$sd = date('Y-m-d',strtotime(sprintf('%s + %d months',$sd,$data['recurring_frequency'])));
					$this->logMessage("handleRecurringEvents",sprintf("old sd [$tmp] new sd [$sd] add [%d] months",$data['recurring_frequency']),2);
					if ($tmp == $sd) exit;
					$work_date = $this->adjustWeek($sd,$data['recurring_position'],$data['recurring_weekdays']);
				}
			}
			break;
		case 'Annual':
			while($sd <= $ed && $status) {
				for($x = 0; $x < $span; $x++) {
					$dt = date('Y-m-d',strtotime(sprintf('%s + %d day',$sd,$x)));
					$stmt = $this->prepare(sprintf('insert into event_dates(event_id,event_date) values(?,?)'));
					if ($data['separate_events'] != 0 && $dt != $data['start_date']) {
						$new_id = $this->duplicateEvent($id,$dt);
						$stmt->bindParams(array('ds', $new_id,$dt));
					}
					else
						$stmt->bindParams(array('ds', $id,$dt));
					$status = $status && $stmt->execute();
				}
				$sd = date('Y-m-d',strtotime(sprintf('%s + %d years',$sd,$data['recurring_frequency'])));
			}
			break;
		default:
		}
		return true;
	}

    /**
     * @param $id
     * @param $sd
     * @return mixed
     * @throws phpmailerException
     */
    private function duplicateEvent($id, $sd) {
		$old = $this->fetchSingleTest('select * from events where id = %d',$id);
		unset($old['id']);
		$old['end_date'] = '0000-00-00';	// force every day into it's own event
		$old['start_date'] = $sd;
		$stmt = $this->prepare(sprintf('insert into %s(%s) values(%s?)',$this->m_content,implode(', ',array_keys($old)),str_repeat('?, ',count($old)-1)));
		$stmt->bindParams(array_merge(array(str_repeat('s',count($old))),array_values($old)));
		$stmt->execute();
		$new_id = $this->insertId();
		$folders = $this->fetchAllTest('select * from events_by_folder where event_id = %d',$id);
		foreach($folders as $key=>$folder) {
			unset($folder['id']);
			$folder['event_id'] = $new_id;
			$stmt = $this->prepare(sprintf('insert into %s(%s) values(%s?)',$this->m_junction,implode(', ',array_keys($folder)),str_repeat('?, ',count($folder)-1)));
			$stmt->bindParams(array_merge(array(str_repeat('s',count($folder))),array_values($folder)));
			$stmt->execute();
		}

		$r_stmt = $this->prepare(sprintf('insert into relations(owner_type,owner_id,related_type,related_id) values(?, ?, ?, ?)'));
		$r = $this->fetchAllTest("select * from relations where related_type = 'event' and related_id = %d",$id);
		foreach($r as $skey=>$svalue) {
			$r_stmt->bindParams(array('sdsd', $svalue["owner_type"],$svalue["owner_id"],$svalue["related_type"],$new_id));
			$r_stmt->execute();
		}

		$r = $this->fetchAllTest("select * from relations where owner_type = 'event' and owner_id = %d",$id);
		foreach($r as $skey=>$svalue) {
			$r_stmt->bindParams(array('sdsd', $svalue["owner_type"], $new_id, $svalue["related_type"], $svalue["related_id"] ));
			$r_stmt->execute();
		}

		return $new_id;
	}

    /**
     * @param $dt
     * @param $position
     * @param $weekdays
     * @return false|string
     * @throws phpmailerException
     */
    private function adjustWeek($dt, $position, $weekdays) {
		//
		//	grab the 1st of the month, get the week #, then get the week # of the requested date
		//	return 1st monday of the month etc
		//
		$this->logMessage("adjustWeek",sprintf("($dt,$position,$weekdays)"),2);
		$m_st = substr($dt,0,8).'01';
		if ($position > 0) {
			$day = -1;
			for($x = 0; $x < 7 && $day < 0; $x++) {
				if (($weekdays & pow(2,$x)) == pow(2,$x)) {
					$day = $x;
				}
			}
			$w_st = strftime('%U',strtotime($m_st));
			$d_st = date('w',strtotime($m_st));
			if ($d_st > $day) {
				//
				//	1st day of week required is before start of month - bump a week
				//
				$this->logMessage("adjustWeek",sprintf("bump by $position weeks"),3);
				$dt = date('Y-m-d',strtotime(sprintf('%s + %d weeks',$m_st,$position)));
			}
			else {
				//
				//	1st day of week required is after start of month - use required week
				//
				$this->logMessage("adjustWeek",sprintf("bump by %d weeks",$position-1),3);
				$dt = date('Y-m-d',strtotime(sprintf('%s + %d weeks',$m_st,$position-1)));
			}
			$tmp = $day - $d_st;
		} else {
			//
			//	work back from the end of the month
			//
			$day = -1;
			for($x = 0; $x < 7; $x++) {
				if (($weekdays & pow(2,$x)) == pow(2,$x)) {
					$day = $x;
				}
			}
			$m_ed = date('Y-m-d',strtotime(sprintf('%s + 1 month - 1 day',$m_st)));
			$w_ed = strftime('%U',strtotime($m_ed));
			$d_ed = date('w',strtotime($m_ed));
			if ($day <= $d_ed) {
				if ($position < -1)
					$dt = date('Y-m-d',strtotime(sprintf('%s %d weeks',$m_ed,$position+1)));
				else
					$dt = $m_ed;
			}
			else {
				$dt = date('Y-m-d',strtotime(sprintf('%s %d weeks',$m_ed,$position)));
			}
			$tmp = $day - $d_ed;
		}
		//
		//	return the 1st day of the week
		//
		$this->logMessage("adjustWeek",sprintf("dt after week adjustment [$dt] move %d days",$tmp),3);
		$tmp = date('w',strtotime($dt));
		if ($tmp > 0)
			$dt = date('Y-m-d',strtotime(sprintf('%s %d days',$dt,-$tmp)));
		$this->logMessage("adjustWeek",sprintf("return $dt"),2);
		return $dt;
	}

    /**
     * @param $owner
     * @return false|string
     * @throws phpmailerException
     */
    function loadAddresses($owner = null) {
		if (array_key_exists('o_id',$_REQUEST) && is_null($owner)) {
			$addresses = $this->fetchAllTest('select a.*, c.value from addresses a, code_lookups c where a.ownerid = %d and a.ownertype = "event" and c.id = a.addresstype and deleted = 0 order by c.value',$_REQUEST['o_id']);
		}
		else
			$addresses = $this->fetchAllTest('select a.*, c.value from addresses a, code_lookups c where a.ownerid = %d and a.ownertype = "event" and c.id = a.addresstype and deleted = 0 order by c.value',$owner);
		$form = new Forms();
		$form->init($this->getTemplate('loadAddresses'));
		$flds = $form->buildForm($this->getFields('loadAddresses'));
		$return = array();
		foreach($addresses as $rec) {
			$form->addData($rec);
			$return[] = $form->show();
		}
		if (is_null($owner)) {
			return $this->ajaxReturn(array('status'=>'true','html'=>implode('',$return)));
		}
		else
		return implode('',$return);
	}

    /**
     * @return false|string
     * @throws phpmailerException
     */
    function myEventList() {
		if (array_key_exists('f_id',$_REQUEST) && $_REQUEST['f_id'] > 0) {
			$tmp = parent::eventList($_REQUEST['f_id'],$this->getTemplate('eventByFolder'),$this->getFields('eventByFolder'));
			return $this->ajaxReturn(array('status'=>'true','html'=>$tmp));
		}
		else return $this->ajaxReturn(array('status'=>'true','html'=>''));
	}

}

?>