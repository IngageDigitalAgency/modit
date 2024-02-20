<?php

class advert extends Backend {

	private $m_tree = 'advert_folders';
	private $m_content = 'advert';
	private $m_junction = 'advert_by_folder';
	private $m_perrow = 5;

	public function __construct() {
		$this->M_DIR = 'backend/modules/advert/';
		$this->setTemplates(
			array(
				'deleteItem'=>$this->M_DIR.'forms/deleteItem.html',
				'deleteItemResult'=>$this->M_DIR.'forms/deleteItemResult.html',
				'main'=>$this->M_DIR.'advert.html',
				'form'=>$this->M_DIR.'forms/form.html',
				'showContentTree'=>$this->M_DIR.'forms/contenttree.html',
				'advertInfo'=>$this->M_DIR.'forms/advertInfo.html',
				'folderProperties'=>$this->M_DIR.'forms/folder.html',
				'showFolderContent'=>$this->M_DIR.'forms/folderContent.html',
				'folderInfo'=>$this->M_DIR.'forms/folderInfo.html',
				'articleList'=>$this->M_DIR.'forms/articleList.html',
				'showSearchForm'=>$this->M_DIR.'forms/searchForm.html',
				'addContent'=>$this->M_DIR.'forms/addContent.html',
				'couponList'=>$this->M_DIR.'forms/couponsList.html',
				'infoEventList'=>$this->M_DIR.'forms/eventsList.html',
				'eventList'=>$this->M_DIR.'forms/eventList.html',
				'couponFolderList'=>$this->M_DIR.'forms/couponFoldersList.html',
				'infoEventFolderList'=>$this->M_DIR.'forms/eventFoldersList.html',
				'productFolderList'=>$this->M_DIR.'forms/productFoldersList.html',
				'productList'=>$this->M_DIR.'forms/productList.html',
				'header'=>$this->M_DIR.'forms/heading.html',
				'addFolder'=>$this->M_DIR.'forms/addFolder.html',
				'addAdvert'=>$this->M_DIR.'forms/addAdvert.html',
				'eventList'=>$this->M_DIR.'forms/eventList.html',
				'storeList'=>$this->M_DIR.'forms/storeList.html',
				'productList'=>$this->M_DIR.'forms/productList.html',
				'couponByAdvertList'=>$this->M_DIR.'forms/couponList.html',
				'video'=>$this->M_DIR.'forms/video.html'
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
			'productByAdvertList'=>array(
				'relatedDestProducts'=>array('type'=>'select','multiple'=>'multiple','id'=>'relatedDestProducts')
			),
			'storeByAdvertList'=>array(
				'destStores'=>array('type'=>'select','multiple'=>'multiple','id'=>'destStores')
			),
			'eventByAdvertList'=>array(
				'destEvents'=>array('type'=>'select','multiple'=>'multiple','id'=>'destEvents')
			),
			'couponByAdvertList'=>array(
				'destCoupons'=>array('type'=>'select','multiple'=>'multiple','id'=>'destCoupons')
			),
			'header'=>array(
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
				'name'=>array('type'=>'tag'),
				'start_date'=>array('type'=>'datestamp','mask'=>'d-M-Y')
			),
			'infoEventList'=>array(
				'options'=>array(),
				'enabled'=>array('type'=>'booleanIcon'),
				'published'=>array('type'=>'booleanIcon'),
				'name'=>array('type'=>'tag'),
				'start_date'=>array('type'=>'datestamp'),
				'end_date'=>array('type'=>'datestamp')
			),
			'couponList'=>array(
				'options'=>array(),
				'enabled'=>array('type'=>'booleanIcon'),
				'published'=>array('type'=>'booleanIcon'),
				'name'=>array('type'=>'tag'),
				'end_date'=>array('type'=>'datestamp')
			),
			'couponByStoreList'=>array(
				'folderList'=>array('type'=>'ul','class'=>'draggable byStoreCouponList','id'=>'byStoreCouponSource'),
				'destList'=>array('type'=>'ul','class'=>'draggable byStoreCouponList','id'=>'byStoreCouponDest')
			),
			'addContent'=>array(
				'options'=>array('method'=>'post','action'=>'/modit/ajax/addContent/advert'),
				'id'=>array('type'=>'tag','database'=>false),
				'title'=>array('type'=>'input','required'=>true,'prettyName'=>'Title'),
				'subtitle'=>array('type'=>'input','required'=>false,'prettyName'=>'Sub-Title'),
				'teaser'=>array('type'=>'textarea','required'=>false,'class'=>'mceSimple','prettyName'=>'Teaser Line'),
				'created'=>array('type'=>'datestamp','mask'=>'d-M-Y h:m:i','database'=>false),
				'start_date'=>array('type'=>'datepicker','required'=>false,'id'=>'addStartDate','validation'=>'date','prettyName'=>'Start Date'),
				'end_date'=>array('type'=>'datepicker','required'=>false,'id'=>'addEndDate','prettyName'=>'End Date'),
				'description'=>array('type'=>'textarea','required'=>false,'id'=>'advertBody','class'=>'mceAdvanced'),
				'enabled'=>array('type'=>'checkbox','required'=>false,'value'=>1,'checked'=>'checked'),
				'featured'=>array('type'=>'checkbox','required'=>false,'value'=>1),
				'featured_start_date'=>array('type'=>'datetimepicker','required'=>false,'id'=>'featuredStartDate','AMPM'=>'AMPM','prettyName'=>'Featured Start Date'),
				'featured_end_date'=>array('type'=>'datetimepicker','required'=>false,'id'=>'featuredEndDate','AMPM'=>'AMPM','prettyName'=>'Featured End Date'),
				'deleted'=>array('type'=>'checkbox','required'=>false,'value'=>1),
				'published'=>array('type'=>'checkbox','required'=>false,'value'=>1,'checked'=>'checked'),
				'new_window'=>array('type'=>'checkbox','required'=>false,'value'=>1),
				'image1'=>array('type'=>'tag','required'=>true,'prettyName'=>'Image 1'),
				'image2'=>array('type'=>'tag','required'=>false),
				'imagesel_a'=>array('type'=>'image','unknown'=>true,'database'=>false,'id'=>'imagesel_a'),
				'imagesel_b'=>array('type'=>'image','unknown'=>true,'database'=>false,'id'=>'imagesel_b'),
				'url'=>array('type'=>'input'),
				'submit'=>array('type'=>'submitbutton','database'=>false,'value'=>'Save'),
				'addContent'=>array('type'=>'hidden','value'=>1,'database'=>false),
				'destSearch'=>array('name'=>'destSearch','type'=>'select','multiple'=>'multiple','required'=>false,'id'=>'destSearch','database'=>false,'options'=>$this->nodeSelect(0, 'search_groups', 2, false, false),'reformatting'=>false,'prettyName'=>'Search Related'),
				'couponFolders'=>array('type'=>'select','database'=>false,'id'=>'couponFolderSelector','options'=>$this->nodeSelect(0, 'coupon_folders', 2, false, false),'reformatting'=>false),
				'eventFolders'=>array('type'=>'select','database'=>false,'id'=>'eventFolderSelector','options'=>$this->nodeSelect(0, 'members_folders', 2, false, false),'reformatting'=>false),
				'storeFolders'=>array('type'=>'select','database'=>false,'id'=>'storeFolderSelector','options'=>$this->nodeSelect(0, 'store_folders', 2, false, false),'reformatting'=>false),
				'productFolders'=>array('type'=>'select','database'=>false,'id'=>'productFolderSelector','options'=>$this->nodeSelect(0, 'product_folders', 2, false, false),'reformatting'=>false),
				'twitterPublish'=>array('type'=>'checkbox','database'=>false,'value'=>1),
				'destFolders'=>array('name'=>'destFolders','type'=>'select','multiple'=>'multiple','required'=>true,'id'=>'destFolders','database'=>false,'options'=>$this->nodeSelect(0, 'advert_folders', 2, false, false),'reformatting'=>false,'prettyName'=>'Member of Groups'),
				'destCoupons'=>array('type'=>'select','database'=>false,'id'=>'destCoupons','reformatting'=>false,'multiple'=>'multiple'),
				'destCouponFolders'=>array('type'=>'select','database'=>false,'id'=>'destCouponFolders','reformatting'=>false,'multiple'=>'multiple'),
				'destStores'=>array('type'=>'select','database'=>false,'id'=>'destStores','reformatting'=>false,'multiple'=>'multiple'),
				'destStoreFolders'=>array('type'=>'select','database'=>false,'id'=>'destStoreFolders','reformatting'=>false,'multiple'=>'multiple'),
				'relatedDestProducts'=>array('type'=>'select','database'=>false,'id'=>'relatedDestProducts','reformatting'=>false,'multiple'=>'multiple'),
				'destProductFolders'=>array('type'=>'select','database'=>false,'id'=>'destProductFolders','reformatting'=>false,'multiple'=>'multiple'),
				'destEvents'=>array('type'=>'select','database'=>false,'id'=>'destEvents','reformatting'=>false,'multiple'=>'multiple'),
				'destEventFolders'=>array('type'=>'select','database'=>false,'id'=>'destEventFolders','reformatting'=>false,'multiple'=>'multiple')
			),
			'showSearchForm'=>array(
				'options'=>array('action'=>'showSearchForm','name'=>'searchForm','id'=>'search_form'),
				'opt_created'=>array('type'=>'select','name'=>'opt_created','lookup'=>'search_options'),
				'opt_end_date'=>array('type'=>'select','name'=>'opt_end_date','lookup'=>'search_options'),
				'opt_start_date'=>array('type'=>'select','name'=>'opt_start_date','lookup'=>'search_options'),
				'opt_title'=>array('type'=>'select','name'=>'opt_title','lookup'=>'search_string'),
				'title'=>array('type'=>'input','required'=>false),
				'created'=>array('type'=>'datepicker','required'=>false),
				'start_date'=>array('type'=>'datepicker','required'=>false,'id'=>'searchStartDate'),
				'end_date'=>array('type'=>'datepicker','required'=>false,'id'=>'searchEndDate'),
				'published'=>array('type'=>'select','lookup'=>'boolean'),
				'enabled'=>array('type'=>'select','lookup'=>'boolean'),
				'featured'=>array('type'=>'select','lookup'=>'boolean'),
				'deleted'=>array('type'=>'select','lookup'=>'boolean'),
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
				'sortby'=>array('type'=>'hidden','value'=>'sequence'),
				'sortorder'=>array('type'=>'hidden','value'=>'asc'),
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
					'action'=>'/modit/advert/showPageProperties',
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
				'id'=>array('type'=>'hidden', 'database'=>false),
				'image'=>array('type'=>'tag'),
				'rollover_image'=>array('type'=>'tag'),

				'couponFolders'=>array('type'=>'select','database'=>false,'id'=>'couponFolderSelector','options'=>$this->nodeSelect(0, 'coupon_folders', 2, false, false),'reformatting'=>false),
				'eventFolders'=>array('type'=>'select','database'=>false,'id'=>'eventFolderSelector','options'=>$this->nodeSelect(0, 'members_folders', 2, false, false),'reformatting'=>false),

				'imagesel_a'=>array('type'=>'image','unknown'=>true,'database'=>false,'id'=>'imagesel_a'),
				'imagesel_b'=>array('type'=>'image','unknown'=>true,'database'=>false,'id'=>'imagesel_b'),
				'destCoupons'=>array('type'=>'select','database'=>false,'id'=>'destCoupons','reformatting'=>false,'multiple'=>'multiple'),
				'destCouponFolders'=>array('type'=>'select','database'=>false,'id'=>'destCouponFolders','reformatting'=>false,'multiple'=>'multiple'),
				'destProductFolders'=>array('name'=>'destProductFolders','type'=>'select','multiple'=>'multiple','required'=>false,'id'=>'destProductFolders','database'=>false,'reformatting'=>false),
				'relatedDestProducts'=>array('name'=>'relatedDestProducts','type'=>'select','multiple'=>'multiple','required'=>false,'id'=>'relatedDestProducts','database'=>false),
				'destEventFolders'=>array('name'=>'destEventFolders','type'=>'select','multiple'=>'multiple','required'=>false,'id'=>'destEventFolders','database'=>false,'reformatting'=>false),
				'destEvents'=>array('name'=>'destEvents','type'=>'select','multiple'=>'multiple','required'=>false,'id'=>'destEvents','database'=>false),
				'submit'=>array('type'=>'submitbutton','value'=>'Save','database'=>false)
			),
			'showContentTree' => array(),
			'advertInfo' => array(),
			'showAdvertContent' => array(),
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
				'created'=>array('type'=>'datestamp','mask'=>'d-M-Y h:m:i'),
				'published'=>array('type'=>'booleanIcon'),
				'enabled'=>array('type'=>'booleanIcon'),
				'featured'=>array('type'=>'booleanIcon'),
				'deleted'=>array('type'=>'booleanIcon'),
				'start_date'=>array('type'=>'datestamp','mask'=>'d-M-Y','suppressNull'=>true),
				'end_date'=>array('type'=>'datestamp','mask'=>'d-M-Y','suppressNull'=>true)
			),
			'video'=>array(
				'url'=>array('type'=>'textfield','required'=>true,'name'=>'video[url]','prettyName'=>'Video URL'),
				'video_id'=>array('type'=>'textfield','required'=>true,'name'=>'video[video_id]','prettyName'=>'Video Id'),
				'height'=>array('type'=>'textfield','class'=>'def_field_small','validation'=>'number','name'=>'video[height]','prettyName'=>'Video height'),
				'width'=>array('type'=>'textfield','class'=>'def_field_small','validation'=>'number','name'=>'video[width]','prettyName'=>'Video Width'),
				'embed_code'=>array('type'=>'textarea','name'=>'video[embed_code]','prettyName'=>'Video Embed Code'),
				'title'=>array('type'=>'textfield','required'=>true,'name'=>'video[title]','prettyName'=>'Video Title'),
				'v_id'=>array('type'=>'hidden','value'=>'%%owner_id%%','name'=>'video[v_id]','database'=>false),
				'thumbnail'=>array('type'=>'textfield','required'=>false,'name'=>'video[thumbnail]','prettyName'=>'Thumbnail'),
				'fetchFromVimeo'=>array('type'=>'checkbox','value'=>1,'database'=>false),
				'media_type'=>array('type'=>'select','required'=>true,'lookup'=>'multimedia_type','name'=>'video[media_type]'),
				'video_host'=>array('type'=>'select','lookup'=>'video_hosting','required'=>true,'name'=>'video[host]','prettyName'=>'Video Hosted By')
			)
		));
	
		parent::__construct ();
	}
	
	function __destruct() {
	
	}

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

	function showContentTree() {
		$form = new Forms();
		$form->init($this->getTemplate('showContentTree'),array());
		$form->addTag('tree',$this->buildTree($this->m_tree, array(), "ajaxBuild", array(0=>"<ol>%s</ol>",1=>"<li class='collapsed'>%s%s</li>",3=>"")),false);
		if ($this->isAjax())
			return $this->ajaxReturn(array('status'=>'true','html'=>$form->show()));
		else
			return $form->show();
	}

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
		$frmFlds = $this->getFields('folderProperties');

		//
		//	load coupon folders attached to this folder
		//
		$data['destCoupons'] = $this->loadRelations('destCoupons',$this,"altCouponFormat",$data['id'],'adfolder','coupon','coupons','and deleted = 0 and enabled = 1 and published = 1',true);
		$frmFlds['destCoupons']['options'] = $this->fetchOptions(sprintf('select id as code,code as value from coupons where deleted = 0 and enabled = 1 and published = 1 and id in (%s) order by code',implode(',',array_merge(array(0),$data['destCoupons']))));

		//
		//	load coupons attached to this folder
		//
		$frmFlds['destCouponFolders']['options'] = $this->nodeSelect(0,'coupon_folders',2,false);
		$data['destCouponFolders'] = $this->loadRelations('destCouponFolders',$this,"altCouponFormat",$data['id'],'adfolder','couponfolder','coupon_folders','',true);
		
		//
		//	load event folders attached to this folder
		//
		$data['destEvents'] = $this->loadRelations('destEvents',$this,"altEventFormat",$data['id'],'adfolder','event','events','and deleted = 0 and enabled = 1 and published = 1',true);
		$frmFlds['destEvents']['options'] = $this->fetchOptions(sprintf('select id as code,name as value from events where id in (select event_id from event_dates where event_date >= curdate()) and deleted = 0 and enabled = 1 and published = 1 and id in (%s) order by start_date',implode(',',array_merge(array(0),$data['destEvents']))));

		$frmFlds['destEventFolders']['options'] = $this->nodeSelect(0,'members_folders',2,false);
		$data['destEventFolders'] = $this->loadRelations('destEventFolders',$this,"altEventFormat",$data['id'],'adfolder','eventfolder','members_folders','',true);

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
				$stmt = $this->prepare(sprintf('update %s set %s where id = %d',$this->m_tree,implode(',',$flds),$data['id']));
				$stmt->bindParams(array_merge(array(str_repeat('s', count($flds))),$values));
				if ($status = $stmt->execute()) {
					$status = $status && $this->updateRelations('destCoupons',$data['id'],'adfolder','coupon','coupons',true,true);
					$status = $status && $this->updateRelations('destCouponFolders',$data['id'],'adfolder','couponfolder','coupon_folders',true,false);
					$status = $status && $this->updateRelations('destEvents',$data['id'],'adfolder','event','events',true,true);
					$status = $status && $this->updateRelations('destEventFolders',$data['id'],'adfolder','eventfolder','members_folders',true,false);
				}
				if ($status) {
					if ($this->isAjax()) {
						$this->logMessage('showPageProperties', 'executing ajax success return', 3);
						$this->addMessage('Record successfully added');
						return $this->ajaxReturn(array(
								'status'=>'true',
								'html'=>'',
								'url'=>'/modit/advert?p_id='.$data['id']
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

	function getFolderInfo($fromMain = false) {
		if (array_key_exists('p_id',$_REQUEST)) {
			if ($data = $this->fetchSingle(sprintf('select * from %s where id = %d',$this->m_tree,$_REQUEST['p_id']))) {
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

	function showPageContent($fromMain = false) {
		$p_id = array_key_exists('p_id',$_REQUEST) ? $_REQUEST['p_id'] : 0;
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
				$tmp = $this->checkArray("formData:advertSearchForm:pager",$_SESSION);
				if ($tmp > 0) 
					$perPage = $tmp;
				else
				$perPage = $this->m_perrow;
			}
			$form->setData('pager',$perPage);
			$count = $this->fetchScalar(sprintf('select count(n.id) from %s n where n.id in (select f.advert_id from %s f where f.folder_id = %d)', $this->m_content, $this->m_junction, $_REQUEST['p_id']));
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
			$sql = sprintf('select a.*, f.id as j_id from %s a left join %s f on a.id = f.advert_id where f.folder_id = %d order by %s %s limit %d,%d',  $this->m_content, $this->m_junction, $_REQUEST['p_id'],$sortby, $sortorder, $start,$perPage);
			$adverts = $this->fetchAll($sql);
			$this->logMessage('showPageContent', sprintf('sql [%s], records [%d]',$sql, count($adverts)), 2);
			$articles = array();
			foreach($adverts as $article) {
				$frm = new Forms();
				$frm->init($this->getTemplate('articleList'),array());
				$tmp = $frm->buildForm($this->getFields('articleList'));
				$frm->addData($article);
				$articles[] = $frm->show();
			}
			$form->addTag('articles',implode('',$articles),false);
			$form->addTag('pagination',$pagination,false);
			$form->addData($data);
			$form->addTag('coupons',$this->displayRelations($data['id'],'coupons','adfolder','coupon',' and c.deleted = 0',true,$this->getFields('couponList'),$this->getTemplate('couponList')),false);
			$form->addTag('couponFolders',$this->displayRelations($data['id'],'coupon_folders','adfolder','couponfolder','',true,$this->getFields('couponFolderList'),$this->getTemplate('couponFolderList')),false);
			$form->addTag('events',$this->displayRelations($data['id'],'events','adfolder','event',' and c.deleted = 0',true,$this->getFields('couponList'),$this->getTemplate('couponList')),false);
			$form->addTag('eventFolders',$this->displayRelations($data['id'],'members_folders','adfolder','eventfolder','',true,$this->getFields('couponFolderList'),$this->getTemplate('couponFolderList')),false);
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
			if (array_key_exists('formData',$_SESSION) && array_key_exists('advertSearchForm', $_SESSION['formData']))
				$_POST = $_SESSION['formData']['advertSearchForm'];
			else
				$_POST = array('showSearchForm'=>1,'deleted'=>0,'sortby'=>'published','sortorder'=>'asc');
		$this->logMessage("showSearchForm",sprintf("post [%s]",print_r($_POST,true)),3);
		if (count($_POST) > 0 && array_key_exists('showSearchForm',$_POST)) {
			if ((!array_key_exists('deleted',$_POST)) || strlen($_POST['deleted']) == 0) $_POST['deleted'] = 0;
			$form->addData($_POST);
			if ($form->validate()) {
				$_SESSION['formData']['advertSearchForm'] = $form->getAllData();
				$srch = array();
				foreach($frmFields as $key=>$value) {
					switch($key) {
						case 'quicksearch':
							if (array_key_exists('opt_quicksearch',$_POST) && $_POST['opt_quicksearch'] != '' && $value = $form->getData($key)) {
								if ($_POST['opt_quicksearch'] == 'like' && strpos($value,'%',0) === false) {
									$value = '%'.$value.'%';
								}
								$tmp = array();
								$tmp[] = sprintf(' title %s "%s"',$_POST['opt_quicksearch'],$this->escape_string($value));
								$tmp[] = sprintf(' teaser %s "%s"',$_POST['opt_quicksearch'],$this->escape_string($value));
								$tmp[] = sprintf(' description %s "%s"',$_POST['opt_quicksearch'],$this->escape_string($value));
								$srch[] = sprintf('(%s)',implode(' or ',$tmp));
							}
							break;
						case 'title':
							if (array_key_exists('opt_title',$_POST) && $_POST['opt_title'] != '' && $value = $form->getData($key)) {
								if ($_POST['opt_title'] == 'like' && strpos($value,'%',0) === false) {
									$value = '%'.$value.'%';
								}
								$srch[] = sprintf(' title %s "%s"',$_POST['opt_title'],$this->escape_string($value));
							}
							break;
						case 'created':
						case 'start_date':
						case 'end_date':
							if (array_key_exists('opt_'.$key,$_POST) && $_POST['opt_'.$key] != '' && $value = $form->getData($key)) {
								if ($_POST['opt_'.$key] == 'like') {
									$this->addError('Like is not supported for dates');
								}
								else
									$srch[] = sprintf(' %s %s "%s"',$key, $_POST['opt_'.$key],$this->escape_string($value));
							}
							break;
						case 'folder':
							if (($value = $form->getData($key)) > 0) {
								$srch[] = sprintf(' n.id in (select advert_id from %s where folder_id = %d) ', $this->m_junction, $value);
							}
							break;
						case 'enabled':
						case 'featured':
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
					$this->logMessage('showSearchForm',sprintf('search options [%s]',print_r($srch,true)),3);
					if (array_key_exists('pagenum',$_REQUEST))
						$pageNum = $_REQUEST['pagenum'];
					else
						$pageNum = 1;	// no 0 based calcs
					if (array_key_exists('pager',$_REQUEST)) 
						$perPage = $_REQUEST['pager'];
					else {
						$tmp = $this->checkArray("formData:advertSearchForm:pager",$_SESSION);
						if ($tmp > 0) 
							$perPage = $tmp;
						else
						$perPage = $this->m_perrow;
					}
					$form->setData('pager',$perPage);
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
					//$sql = sprintf('select n.*, j.id as j_id from %s n, %s j where n.id = j.advert_id and j.id = (select min(j1.id) from %s j1 where j1.advert_id = n.id) and %s order by %s %s limit %d,%d',
					//	 $this->m_content, $this->m_junction, $this->m_junction, implode(' and ',$srch),$sortby, $sortorder, $start,$perPage);
					$sql = sprintf('select n.*, 0 as j_id from %s n where 1=1 and %s order by %s %s limit %d,%d',
						 $this->m_content, implode(' and ',$srch),$sortby, $sortorder, $start,$perPage);
					$recs = $this->fetchAll($sql);
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
		if (!(array_key_exists('a_id',$_REQUEST) && $_REQUEST['a_id'] > 0 && $data = $this->fetchSingle(sprintf('select * from %s where id = %d', $this->m_content, $_REQUEST['a_id'])))) {
			$data = array('id'=>0,'published'=>false,'image1'=>'','image2'=>''); 
		}
		if (count($_REQUEST) > 0 && array_key_exists('destFolders',$_REQUEST) || $data['id'] > 0) {
			$ids = array();
			if (array_key_exists('destFolders',$_REQUEST)) {
				$ids = $_REQUEST['destFolders'];
				if (!is_array($ids)) $ids = array($ids);
			}
			if ($data['id'] > 0) {
				$tmp = $this->fetchScalarAll(sprintf('select folder_id from %s where advert_id = %d', $this->m_junction, $data['id']));
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
				$tmp = $this->fetchScalarAll(sprintf('select folder_id from advert_by_search_group where event_id = %d', $data['id']));
				$srch = array_merge($srch,$tmp);
			}
			if (count($srch) > 0) {
				$data['destSearch'] = $srch;
			}
			$this->logMessage(__FUNCTION__,sprintf('search folders [%s]',print_r($srch,true)),1);
		}

		$customFields = new custom();
		if (method_exists($customFields,'advertDisplay')) {
			$custom = $customFields->advertDisplay();
			$form->addTag('customTab',sprintf('<li><a href="#tabs-custom">%s</a></li>',$custom['description']),false);
			$form->addTag('customInfo',$custom['form'],false);
			$frmFields = array_merge($frmFields,$custom['fields']);
		}

		//
		//	load coupon folders attached to this folder
		//
		$data['destCoupons'] = $this->loadRelations('destCoupons',$this,"altCouponFormat",$data['id'],'ad','coupon','coupons','and deleted = 0 and enabled = 1 and published = 1',true);
		$frmFields['destCoupons']['options'] = $this->fetchOptions(sprintf('select id as code,code as value from coupons where deleted = 0 and enabled = 1 and published = 1 and id in (%s) order by code',implode(',',array_merge(array(0),$data['destCoupons']))));

		//
		//	load coupons attached to this folder
		//
		$frmFields['destCouponFolders']['options'] = $this->nodeSelect(0,'coupon_folders',2,false);
		$data['destCouponFolders'] = $this->loadRelations('destCouponFolders',$this,"altCouponFormat",$data['id'],'ad','couponfolder','coupon_folders','',true);
		
		//
		//	load event folders attached to this folder
		//
		$data['destEvents'] = $this->loadRelations('destEvents',$this,"altEventFormat",$data['id'],'ad','event','events','and deleted = 0 and enabled = 1 and published = 1',true);
		$frmFields['destEvents']['options'] = $this->fetchOptions(sprintf('select id as code,name as value from events where id in (select event_id from event_dates where event_date >= curdate()) and deleted = 0 and enabled = 1 and published = 1 and id in (%s) order by start_date',implode(',',array_merge(array(0),$data['destEvents']))));

		//
		//	load individual products attached to this folder
		//
		$frmFields['destEventFolders']['options'] = $this->nodeSelect(0,'members_folders',2,false);
		$data['destEventFolders'] = $this->loadRelations('destEventFolders',$this,"altEventFormat",$data['id'],'ad','eventfolder','members_folders','',true);

		//
		//	load event folders attached to this folder
		//
		$data['destStores'] = $this->loadRelations('destStores',$this,"altStoreFormat",$data['id'],'ad','store','stores','and deleted = 0 and enabled = 1 and published = 1',true);
		$frmFields['destStores']['options'] = $this->fetchOptions(sprintf('select id as code,name as value from stores where deleted = 0 and enabled = 1 and published = 1 and id in (%s) order by name',implode(',',array_merge(array(0),$data['destStores']))));

		//
		//	load individual products attached to this folder
		//
		$frmFields['destStoreFolders']['options'] = $this->nodeSelect(0,'store_folders',2,false);
		$data['destStoreFolders'] = $this->loadRelations('destStoreFolders',$this,"altStoreFormat",$data['id'],'ad','storefolder','store_folders','',true);

		$data['relatedDestProducts'] = $this->loadRelations('relatedDestProducts',$this,"altProductFormat",$data['id'],'ad','product','product','and deleted = 0 and enabled = 1 and published = 1',true);
		$frmFields['relatedDestProducts']['options'] = $this->fetchOptions(sprintf('select id as code,name as value from product where deleted = 0 and enabled = 1 and published = 1 and id in (%s) order by name',implode(',',array_merge(array(0),$data['relatedDestProducts']))));

		$frmFields['destProductFolders']['options'] = $this->nodeSelect(0,'product_folders',2,false);
		$data['destProductFolders'] = $this->loadRelations('destProductFolders',$this,"altProductFormat",$data['id'],'ad','productfolder','product_folders','',true);

		$frmFields = $form->buildForm($frmFields);
		$data['imagesel_a'] = $data['image1'];
		$data['imagesel_b'] = $data['image2'];

		$form->addTag('video',$this->getVideo($data['id'],false),false);

		$form->addData($data);
		$status = 'false';	//assume it failed
		if (count($_POST) > 0 && array_key_exists('addContent',$_POST)) {
			$_POST['imagesel_a'] = $_POST['image1'];
			$_POST['imagesel_b'] = $_POST['image2'];
			$form->addData($_POST);
			$valid = $form->validate();
			if (array_key_exists('video',$_POST) && count($_POST['video']) > 0) {
				$valid = $valid && $this->validateVideo($form);
				$form->addTag('video',$this->getVideo($data['id'],true),false);
			}
			if ($valid) {
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
					$this->execute(sprintf('delete from %s where advert_id = %d and folder_id not in (%s)', $this->m_junction, $id,implode(',',$destFolders)));
					$new = $this->fetchScalarAll(sprintf('select id from %s where id in (%s) and id not in (select folder_id from %s where advert_id = %d and folder_id in (%s))',
						$this->m_tree,implode(',',$destFolders),$this->m_junction,$id,implode(',',$destFolders)));
					$status = true;
					foreach($new as $key=>$folder) {
						$obj = new preparedStatement(sprintf('insert into %s(advert_id,folder_id) values(?,?)',$this->m_junction));
						$obj->bindParams(array('dd',$id,$folder));
						$status = $status && $obj->execute();
						$this->resequence($folder);
					}
					$destSearch = array_key_exists('destSearch',$_POST) ? $_POST['destSearch'] : array(0);
					if (!is_array($destSearch)) $destSearch = array($destSearch);
					$this->execute(sprintf('delete from advert_by_search_group where event_id = %d and folder_id not in (%s)', $id, implode(',',$destSearch)));
					$new = $this->fetchScalarAll(sprintf('select id from search_groups where id in (%s) and id not in (select folder_id from advert_by_search_group where event_id = %d and folder_id in (%s))',
						implode(',',$destSearch),$id,implode(',',$destSearch)));
					foreach($new as $key=>$folder) {
						$obj = new preparedStatement('insert into advert_by_search_group(event_id,folder_id) values(?,?)');
						$obj->bindParams(array('dd',$id,$folder));
						$status = $status && $obj->execute();
					}
					if ($status) {
						if ($status) $status = $status && $this->updateRelations('destCoupons',$data['id'],'ad','coupon','coupons',true,true);
						if ($status) $status = $status && $this->updateRelations('destCouponFolders',$data['id'],'ad','couponfolder','coupon_folders',true,false);
						if ($status) $status = $status && $this->updateRelations('destEvents',$data['id'],'ad','event','events',true,true);
						if ($status) $status = $status && $this->updateRelations('destEventFolders',$data['id'],'ad','eventfolder','members_folders',true,false);
						if ($status) $status = $status && $this->updateRelations('destStores',$data['id'],'ad','store','stores',true,true);
						if ($status) $status = $status && $this->updateRelations('destStoreFolders',$data['id'],'ad','storefolder','store_folders',true,false);
						if ($status) $status = $status && $this->updateRelations('relatedDestProducts',$data['id'],'ad','product','product',true,true);
						if ($status) $status = $status && $this->updateRelations('destProductFolders',$data['id'],'ad','productfolder','product_folders',true,false);
					}
					$status = $status && $this->saveVideo($data["id"]);
					if ($status) {
						$this->commitTransaction();
						if ($form->getData('twitterPublish') != 0) {
							$data = $form->getAllData();
							if ($data['published'] == 0 || $data['enabled'] == 0) {
								$this->addError('Cannot tweet an unpublished or disabled item');
								$status = false;
							}
							else {
								if (!$status = $this->twitterPost($data['title'], $data['teaser'],sprintf('http://%s%s',HOSTNAME,$this->getUrl('advert',$id,$data)),$data))
									$this->addError('Error posting to Twitter');
							}
						}
						if ($status) {
							return $this->ajaxReturn(array(
								'status' => 'true',
								'url' => sprintf('/modit/advert?p_id=%d',$destFolders[0])
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

	function moveArticle() {
		$src = 0;
		$dest = 0;
		if (array_key_exists('src',$_REQUEST)) $src = $_REQUEST['src'];
		if (array_key_exists('dest',$_REQUEST)) $dest = $_REQUEST['dest'];
		if ($_REQUEST['type'] == 'tree') {
			if ($src == 0 || $dest == 0 || !array_key_exists('type',$_REQUEST)) {
				$this->addError('Either source or destination was missing');
				return $this->ajaxReturn(array('status' => 'false'));
			}
			if ($folder = $this->fetchSingle(sprintf('select * from %s where id = %d',$this->m_tree,$dest))) {
				$curr = $this->fetchScalar(sprintf('select advert_id from %s where id = %d',$this->m_junction,$src));
				$status = true;
				if (array_key_exists('move',$_REQUEST)) {
					//
					//	move it - delete all other folders
					//
					$this->logMessage('moveArticle', sprintf('moving advert %d to folder %d',$src,$dest),2);
					$this->beginTransaction();
					if ($status = $this->execute(sprintf('delete from %s where id = %d', $this->m_junction, $src))) {
						if (!$this->fetchSingle(sprintf('select * from %s where advert_id = %d and folder_id = %d',$this->m_junction,$curr,$dest))) {
							$obj = new preparedStatement(sprintf('insert into %s(advert_id,folder_id) values(?,?)',$this->m_junction));
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
					$curr = $src;
					$this->logMessage('moveArticle', sprintf('cloning advert %d to folder %d',$curr,$dest),2);
					if (!($this->fetchSingle(sprintf('select * from %s where advert_id = %d and folder_id = %d',$this->m_junction,$curr,$dest)))) {
						$obj = new preparedStatement(sprintf('insert into %s(advert_id,folder_id) values(?,?)',$this->m_junction));
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
			$this->logMessage("moveArticle",sprintf("move src [%s] to dest [%s] sql [%s]",print_r($src,true),print_r($dest,true),$sql),2);
			if (count($src) == 0 || count($dest) == 0) {
				$status = false;
				$this->addMessage('Either the source or destination article was not found');
			}
			else {
				$this->beginTransaction();
				$sql = sprintf('update %s set sequence = %d where id = %s',
					$this->m_junction, $src['sequence'] < $dest['sequence'] ? $dest['sequence']+1 : $dest['sequence']-1, $src['id']);
				$this->logMessage("moveArticle",sprintf("move sql [$sql]"),3);
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
						//$img = $this->fetchScalar(sprintf('select advert_id from %s where id = %d',$this->m_junction,$_REQUEST['j_id']));
						$this->execute(sprintf('delete from %s where advert_id = %d',$this->m_junction,$_REQUEST['a_id']));
						$this->execute(sprintf('update %s set deleted = 1 where id = %d',$this->m_content,$_REQUEST['a_id']));
						break;
					case 'one':
						$status = $this->execute(sprintf('delete from %s where id = %d',$this->m_junction,$_REQUEST['j_id']));
						$ct = $this->fetchScalar(sprintf('select count(0) from %s where advert_id = %d',$this->m_junction,$_REQUEST['a_id']));
						if ($ct == 0)
							$this->execute(sprintf('update %s set deleted = 1 where id = %d',$this->m_content,$_REQUEST['a_id']));
						break;
					default:
						break;
				}
				$form->init($this->getTemplate('deleteItemResult'));
			}
		}
		return $this->ajaxReturn(array('status'=>'true','html'=>$form->show()));
		if (array_key_exists('j_id',$_REQUEST)) {
			$id = $_REQUEST['j_id'];
			$curr = $this->fetchScalar(sprintf('select advert_id from %s where id = %d',$this->m_junction,$id));
			$this->logMessage('deleteArticle', sprintf('deleting advert junction %d for store %d',$id,$curr), 2);
			$this->beginTransaction();
			$this->execute(sprintf('delete from %s where id = %d',$this->m_junction,$id));
			if (($remining = $this->fetchScalar(sprintf('select count(0) from %s where advert_id = %d',$this->m_junction,$curr))) == 0) {
				$this->logMessage('deleteStore', sprintf('deleting ad %d - no more references',$curr), 2);
				$this->execute(sprintf('update %s set deleted = 1 where id = %d',$this->m_content,$curr));
			}
			$this->commitTransaction();
			return $this->ajaxReturn(array('status'=>'true'));
		}
	}
	
	function deleteContent() {
		$status = 'false';
		if (array_key_exists('p_id',$_REQUEST)) {
			$id = $_REQUEST['p_id'];
			$ct = $this->fetchScalar(sprintf('select count(0) from %s where folder_id = %d',$this->m_junction,$_REQUEST['p_id']));
			if ($ct > 0) {
				$this->addError('Ads are still attached to this folder');
				return $this->ajaxReturn(array('status'=>'false'));
			}
			$ct = $this->fetchScalar(sprintf('select count(0) from %s t1, %s t2 where t2.id = %d and t1.left_id > t2.left_id and t1.right_id < t2.right_id and t1.level > t2.level',$this->m_tree, $this->m_tree, $_REQUEST['p_id']));
			if ($ct > 0) {
				$this->addError('Other categories are still attached to this folder');
				return $this->ajaxReturn(array('status'=>'false'));
			}
			if (!$this->deleteCheck('advert',$_REQUEST['p_id'],$inUse)) {
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

	function getHeader() {
		$form = new Forms();
		$form->init($this->getTemplate('header'));
		$flds = $form->buildForm($this->getFields('showSearchForm'));
		if (count($_POST) > 0 && array_key_exists('showSearchForm',$_POST))
			$form->addData($_POST);
		else
			if (array_key_exists('formData',$_SESSION) && array_key_exists('advertSearchForm', $_SESSION['formData']))
				$form->addData($_SESSION['formData']['advertSearchForm']);
		return $form->show();
	}

	function addFolder($fromMain = false) {
		$form = new Forms();
		$form->init($this->getTemplate('addFolder'));
		if ($fromMain)
			return $form->show();
		else
			return $this->show($form->show());
	}

	function addAdvert($fromMain = false) {
		$form = new Forms();
		$form->init($this->getTemplate('addAdvert'));
		if ($fromMain)
			return $form->show();
		else
			return $this->show($form->show());
	}

	function moduleStatus($fromMain = 0) {
		if (array_key_exists('formData',$_SESSION) && array_key_exists('advertSearchForm', $_SESSION['formData'])) {
			$_POST = $_SESSION['formData']['advertSearchForm'];
			$msg = "";
		}
		else {
			$ct = $this->fetchScalar(sprintf('select count(0) from %s where published = 0',$this->m_content));
			if ($ct == 0) {
				$_POST = array('showSearchForm'=>1,'deleted'=>0,'sortby'=>'created','sortorder'=>'desc','pager'=>$this->m_perrow);
				$msg = "Showing latest advertisements added";
			}
			else {
				$_POST = array('showSearchForm'=>1,'published'=>0,'sortby'=>'created','sortorder'=>'desc','pager'=>$this->m_perrow);
				$msg = "Showing unpublished advertisements";
			}
		}
		$result = $this->showSearchForm($fromMain,$msg);
		return $result;
	}

	function resequence($folder) {
		$this->logMessage('resequence', "resequencing folder $folder", 2);
		$articles = $this->fetchAll(sprintf('select * from %s where folder_id = %d order by sequence',$this->m_junction,$folder));
		$seq = 10;
		foreach($articles as $article) {
			$this->execute(sprintf('update %s set sequence = %d where id = %d',$this->m_junction,$seq,$article['id']));
			$seq += 10;
		}
	}

	function myCouponList() {
		if (array_key_exists('f_id',$_REQUEST) && $_REQUEST['f_id'] > 0) {
			$tmp = parent::couponList($_REQUEST['f_id'],$this->getTemplate('couponByAdvertList'),$this->getFields('couponByAdvertList'));
			return $this->ajaxReturn(array('status'=>'true','html'=>$tmp));
		}
		else return $this->ajaxReturn(array('status'=>'true','html'=>''));
	}

	function myEventList() {
		if (array_key_exists('f_id',$_REQUEST) && $_REQUEST['f_id'] > 0) {
			$tmp = parent::eventList($_REQUEST['f_id'],$this->getTemplate('eventList'),$this->getFields('eventByAdvertList'));
			return $this->ajaxReturn(array('status'=>'true','html'=>$tmp));
		}
		else return $this->ajaxReturn(array('status'=>'true','html'=>''));
	}

	function myStoreList() {
		if (array_key_exists('f_id',$_REQUEST) && $_REQUEST['f_id'] > 0) {
			$tmp = parent::storeList($_REQUEST['f_id'],$this->getTemplate('storeList'),$this->getFields('storeByAdvertList'));
			return $this->ajaxReturn(array('status'=>'true','html'=>$tmp));
		}
		else return $this->ajaxReturn(array('status'=>'true','html'=>''));
	}

	function myProductList() {
		if (array_key_exists('f_id',$_REQUEST) && $_REQUEST['f_id'] > 0) {
			$tmp = parent::productList($_REQUEST['f_id'],$this->getTemplate('productList'),$this->getFields('productByAdvertList'));
			return $this->ajaxReturn(array('status'=>'true','html'=>$tmp));
		}
		else return $this->ajaxReturn(array('status'=>'true','html'=>''));
	}

	function getVideo($b_id = 0,$fromPost) {
		$outer = new Forms();
		$outer->init($this->getTemplate('video'));
		if ($fromPost)
			$data = array_key_exists('video',$_POST) ? $_POST['video'] : array('id'=>0);
		else
			if (!$data = $this->fetchSingle(sprintf('select * from videos where owner_id = %d and owner_type = "advert"', $b_id)))
				$data = array('id'=>0);
		$flds = $outer->buildForm($this->getFields('video'));
		$outer->addData($data);
		return $outer->show();
	}

	function validateVideo(&$form) {
		if (strlen($_POST['video']['url']) == 0 && strlen($_POST['video']['embed_code']) == 0) {
			return true;
		}
		$outer = new Forms();
		$outer->init($this->getTemplate('video'));
		$flds = $outer->buildForm($this->getFields('video'));
		$outer->addData($_POST['video']);
		$valid = $outer->validate();
		$msg = $outer->getFormErrors();
		foreach($msg as $key=>$value) {
			$form->addFormError($value);
		}
		return $valid;
	}

	function saveVideo($id) {
		if (strlen($_POST['video']['url']) == 0 && strlen($_POST['video']['embed_code']) == 0) {
			if ($_POST['video']['v_id'] > 0)
				$this->execute(sprintf('delete from video where owner_type="advert" and owner_id = %d'));
			return true;
		}
		$form = new Forms();
		$tmp = $form->buildForm($this->getFields('video'));
		$form->addData($_POST['video']);
		$flds = array();
		foreach($tmp as $key=>$fld) {
			if (!(array_key_exists('database',$fld) && $fld['database'] == false)) {
				$flds[str_replace(']','',str_replace('video[','',$fld['name']))] = $form->getData($fld['name']);
			}
		}
		if ($data = $this->fetchSingle(sprintf('select * from videos where owner_type="advert" and owner_id = %d', $form->getData('v_id')))) {
			$stmt = $this->prepare(sprintf('update videos set %s=? where id = %d', implode('=?, ',array_keys($flds)),$data['id']));
		}
		else {
			$flds['owner_type'] = 'advert';
			$flds['owner_id'] = $id;
			$stmt = $this->prepare(sprintf('insert into videos(%s) values(%s?)', implode(', ', array_keys($flds)), str_repeat('?, ',count($flds)-1)));
		}
		$stmt->bindParams(array_merge(array(str_repeat('s',count($flds))),array_values($flds)));
		$valid = $stmt->execute();
		return $valid;
	}

}

?>