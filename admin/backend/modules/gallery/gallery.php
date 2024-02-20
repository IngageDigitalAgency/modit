<?php

/**
 * Gallery feature for backend
 */
class gallery extends Backend {

	private $m_tree = 'gallery_folders';
	private $m_content = 'gallery_images';
	private $m_junction = 'gallery_images_by_folder';
	private $m_pagination = 999;

    /**
     * @throws phpmailerException
     */
	public function __construct() {
		$this->M_DIR = 'backend/modules/gallery/';
		$this->M_ROOT = '/images/gallery/originals/';
		$this->setTemplates(
			array(
				'deleteItem'=>$this->M_DIR.'forms/deleteItem.html',
				'deleteItemResult'=>$this->M_DIR.'forms/deleteItemResult.html',
				'main'=>$this->M_DIR.'gallery.html',
				'form'=>$this->M_DIR.'forms/form.html',
				'showContentTree'=>$this->M_DIR.'forms/contenttree.html',
				'galleryInfo'=>$this->M_DIR.'forms/galleryInfo.html',
				'folderProperties'=>$this->M_DIR.'forms/folder.html',
				'showFolderContent'=>$this->M_DIR.'forms/folderContent.html',
				'folderInfo'=>$this->M_DIR.'forms/folderInfo.html',
				'imageList'=>$this->M_DIR.'forms/imageList.html',
				'showSearchForm'=>$this->M_DIR.'forms/searchForm.html',
				'couponList'=>$this->M_DIR.'forms/couponsList.html',
				'infoEventList'=>$this->M_DIR.'forms/eventsList.html',
				'couponFolderList'=>$this->M_DIR.'forms/couponFoldersList.html',
				'infoEventFolderList'=>$this->M_DIR.'forms/eventFoldersList.html',
				'productFolderList'=>$this->M_DIR.'forms/productFoldersList.html',
				'header'=>$this->M_DIR.'forms/heading.html',
				'addContent'=>$this->M_DIR.'forms/addContent.html',
				'addFolder'=>$this->M_DIR.'forms/addFolder.html',
				'addItem'=>$this->M_DIR.'forms/addItem.html',
				'galleryByFolder'=>$this->M_DIR.'forms/galleryList.html',
				'video'=>$this->M_DIR.'forms/video.html'
			)
		);
		$this->setFields(array(
			'deleteItem'=>array(
				'options'=>array('name'=>'deleteItem','database'=>false),
				'p_id'=>array('type'=>'tag'),
				'deleteImage'=>array('type'=>'hidden','value'=>1),
				'cancel'=>array('type'=>'radiobutton','name'=>'action','value'=>'cancel','checked'=>'checked'),
				'one'=>array('type'=>'radiobutton','name'=>'action','value'=>'one'),
				'all'=>array('type'=>'radiobutton','name'=>'action','value'=>'all')
			),
			'header'=>array(),
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
				'expires'=>array('type'=>'datestamp')
			),
			'imageList'=>array(
				'options'=>array(),
				'image'=>array('type'=>'image')
			),
			'addContent'=>array(
				'options'=>array('method'=>'post','action'=>'/modit/ajax/addContent/gallery'),
				'created'=>array('type'=>'datestamp','database'=>false,'reformatting'=>true),
				'id'=>array('type'=>'tag','database'=>false),
				'title'=>array('type'=>'input','required'=>true,'prettyName'=>'Title'),
				'subtitle'=>array('type'=>'input','required'=>false),
				'teaser'=>array('type'=>'textarea','required'=>false,'class'=>'mceSimple'),
				'expires'=>array('type'=>'datepicker','required'=>false,'id'=>'addExpires','prettyName'=>'Expires'),
				'description'=>array('type'=>'textarea','required'=>false,'id'=>'galleryBody','class'=>'mceAdvanced','prettyName'=>'Description'),
				'published'=>array('type'=>'checkbox','required'=>false,'value'=>1),
				'enabled'=>array('type'=>'checkbox','required'=>false,'value'=>1),
				'featured'=>array('type'=>'checkbox','required'=>false,'value'=>1),
				'featured_start_date'=>array('type'=>'datetimepicker','required'=>false,'AMPM'=>'AMPM','id'=>'featured_start_date','prettyName'=>'Featured Start Date'),
				'featured_end_date'=>array('type'=>'datetimepicker','required'=>false,'AMPM'=>'AMPM','id'=>'featured_end_date','prettyName'=>'Featured End Date'),
				'image'=>array('type'=>'tag','required'=>true,'prettyName'=>'Image'),
				'thumbnail'=>array('type'=>'tag'),
				'imagesel_a'=>array('type'=>'image','unknown'=>true,'database'=>false,'id'=>'imagesel_a'),
				'imagesel_b'=>array('type'=>'image','unknown'=>true,'database'=>false,'id'=>'imagesel_b'),
				'submit'=>array('type'=>'submitbutton','database'=>false,'value'=>'Save'),
				'destFolders'=>array('name'=>'destFolders','type'=>'select','multiple'=>'multiple','required'=>true,'id'=>'destFolders','database'=>false,'options'=>$this->nodeSelect(0, 'gallery_folders', 2, false, false),'reformatting'=>false,'prettyName'=>'Member Of'),
				'addContent'=>array('type'=>'hidden','value'=>1,'database'=>false),
				'newsFolders'=>array('type'=>'select','database'=>false,'id'=>'newsFolderSelector','options'=>$this->nodeSelect(0, 'news_folders', 2, false, false),'reformatting'=>false),
				'destNews'=>array('name'=>'destNews','type'=>'select','multiple'=>'multiple','required'=>false,'id'=>'destNews','database'=>false),
				'destNewsFolders'=>array('name'=>'destNewsFolders','type'=>'select','multiple'=>'multiple','required'=>false,'id'=>'destNewsFolders','database'=>false,'reformatting'=>false),
				'storeFolders'=>array('type'=>'select','database'=>false,'id'=>'storeFolderSelector','options'=>$this->nodeSelect(0, 'store_folders', 2, false, false),'reformatting'=>false),
				'destStore'=>array('name'=>'destStore','type'=>'select','multiple'=>'multiple','required'=>false,'id'=>'destStore','database'=>false),
				'destStoreFolders'=>array('name'=>'destStoreFolders','type'=>'select','multiple'=>'multiple','required'=>false,'id'=>'destStoreFolders','database'=>false,'reformatting'=>false),
				'slider_class'=>array('type'=>'textfield','required'=>false),
				'slider_animation'=>array('type'=>'textfield','required'=>false),
				'slider_text'=>array('type'=>'textarea','required'=>false,'class'=>'mceSimple'),
				'slider_style'=>array('type'=>'textfield','required'=>false)
			),
			'showSearchForm'=>array(
				'options'=>array('action'=>'showSearchForm','name'=>'searchForm','id'=>'search_form'),
				'opt_created'=>array('type'=>'select','name'=>'opt_created','lookup'=>'search_options'),
				'opt_expires'=>array('type'=>'select','name'=>'opt_expires','lookup'=>'search_options'),
				'opt_title'=>array('type'=>'select','name'=>'opt_title','lookup'=>'search_options'),
				'title'=>array('type'=>'input','required'=>false,'prettyName'=>'Title'),
				'created'=>array('type'=>'datepicker','required'=>false,'prettyName'=>'Created'),
				'expires'=>array('type'=>'datepicker','required'=>false,'id'=>'searchExpires','prettyName'=>'Expires'),
				'published'=>array('type'=>'select','lookup'=>'boolean'),
				'enabled'=>array('type'=>'select','lookup'=>'boolean'),
				'featured'=>array('type'=>'select','lookup'=>'boolean'),
				'showSearchForm'=>array('type'=>'hidden','value'=>1),
				'pagenum'=>array('type'=>'hidden','value'=>1),
				'sortby'=>array('type'=>'hidden','value'=>'created'),
				'sortorder'=>array('type'=>'hidden','value'=>'desc'),
				'folder'=>array('type'=>'select','optionslist' => array('table'=>$this->m_tree,'root'=>0,'indent'=>2,'inclusive'=>false),'database'=>false),
				'quicksearch'=>array('type'=>'input','name'=>'quicksearch','required'=>false),
				'opt_quicksearch'=>array('type'=>'hidden','value'=>'like'),
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
				'showFolderContent'=>array('type'=>'hidden','value'=>1)
			),
			'main' => array(
				'test'=>array('type'=>'tag')
			),
			'form' => array(),
			'folderProperties' => array(
				'options'=>array(
					'action'=>'/modit/gallery/showPageProperties',
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
				'imagesel_a'=>array('type'=>'image','unknown'=>true,'database'=>false,'id'=>'imagesel_a'),
				'imagesel_b'=>array('type'=>'image','unknown'=>true,'database'=>false,'id'=>'imagesel_b'),
				'template_id'=>array('type'=>'select','required'=>false,'sql'=>'select template_id,title from templates group by title order by title'),
				'couponFolders'=>array('type'=>'select','database'=>false,'id'=>'couponFolderSelector','options'=>$this->nodeSelect(0, 'coupon_folders', 2, false, false),'reformatting'=>false),
				'eventFolders'=>array('type'=>'select','database'=>false,'id'=>'eventFolderSelector','options'=>$this->nodeSelect(0, 'members_folders', 2, false, false),'reformatting'=>false),
				'productFolders'=>array('type'=>'select','database'=>false,'id'=>'productFolderSelector','options'=>$this->nodeSelect(0, 'product_folders', 2, false, false),'reformatting'=>false),
				'destCoupons'=>array('type'=>'select','database'=>false,'id'=>'destCouponSelector','reformatting'=>false,'multiple'=>'multiple'),
				'destCouponFolders'=>array('type'=>'select','database'=>false,'id'=>'destCouponFolderSelector','reformatting'=>false,'multiple'=>'multiple'),
				'destProducts'=>array('name'=>'destProducts','type'=>'select','multiple'=>'multiple','required'=>false,'id'=>'destProducts','database'=>false),
				'destProductFolders'=>array('name'=>'destProductFolders','type'=>'select','multiple'=>'multiple','required'=>false,'id'=>'destProductFolders','database'=>false,'reformatting'=>false),
				'destEvents'=>array('name'=>'destEvents','type'=>'select','multiple'=>'multiple','required'=>false,'id'=>'destEvents','database'=>false),
				'destEventFolders'=>array('name'=>'destEventFolders','type'=>'select','multiple'=>'multiple','required'=>false,'id'=>'destEventFolders','database'=>false,'reformatting'=>false),
				'twitterPublish'=>array('type'=>'checkbox','database'=>false,'value'=>1),
				'submit'=>array('type'=>'submitbutton','value'=>'Save','database'=>false),
				'newsFolders'=>array('type'=>'select','database'=>false,'id'=>'newsFolderSelector','options'=>$this->nodeSelect(0, 'news_folders', 2, false, false),'reformatting'=>false),
				'destNews'=>array('name'=>'destNews','type'=>'select','multiple'=>'multiple','required'=>false,'id'=>'destNews','database'=>false),
				'destNewsFolders'=>array('name'=>'destNewsFolders','type'=>'select','multiple'=>'multiple','required'=>false,'id'=>'destNewsFolders','database'=>false,'reformatting'=>false),
				'destStore'=>array('name'=>'destStore','type'=>'select','multiple'=>'multiple','required'=>false,'id'=>'destStore','database'=>false),
				'destStoreFolders'=>array('name'=>'destStoreFolders','type'=>'select','multiple'=>'multiple','required'=>false,'id'=>'destStoreFolders','database'=>false,'reformatting'=>false),
				'storeFolders'=>array('type'=>'select','database'=>false,'id'=>'storeFolderSelector','options'=>$this->nodeSelect(0, 'store_folders', 2, false, false),'reformatting'=>false),
				'slider_class'=>array('type'=>'textfield','required'=>false),
				'slider_animation'=>array('type'=>'textfield','required'=>false),
				'slider_text'=>array('type'=>'textarea','required'=>false,'class'=>'mceSimple'),
				'slider_style'=>array('type'=>'textfield','required'=>false)
			),
			'showContentTree' => array(),
			'galleryInfo' => array(),
			'showgalleryContent' => array(),
			'folderInfo' => array(
				'title'=>array('type'=>'tag'),
				'alternate_title'=>array('type'=>'tag'),
				'notes'=>array('type'=>'tag','reformatting'=>false),
				'description'=>array('type'=>'tag','reformatting'=>false),
				'image' => array('type'=>'image','unknown'=>true),
				'rollover_image'=>array('type'=>'image','unknown'=>true)
			),
			'imageList' => array(
				'id'=>array('type'=>'tag'),
				'title'=>array('type'=>'tag'),
				'created'=>array('type'=>'datestamp','mask'=>'d-M-Y h:m:i'),
				'published'=>array('type'=>'boolean'),
				'enabled'=>array('type'=>'boolean'),
				'featured'=>array('type'=>'boolean'),
				'expires'=>array('type'=>'datestamp','mask'=>'d-M-Y','suppressNull'=>true),
				'image'=>array('type'=>'image')
			),
			'galleryByFolder'=>array(
				'destRelatedGallery'=>array('type'=>'select','id'=>'destRelatedGallery','multiple'=>'multiple')
			),
			'video'=>array(
				'url'=>array('type'=>'textfield','required'=>true,'name'=>'video[url]','prettyName'=>'Video URL'),
				'video_id'=>array('type'=>'textfield','required'=>true,'name'=>'video[video_id]','prettyName'=>'Video Id'),
				'height'=>array('type'=>'textfield','class'=>'def_field_small','validation'=>'number','name'=>'video[height]','prettyName'=>'Video height'),
				'width'=>array('type'=>'textfield','class'=>'def_field_small','validation'=>'number','name'=>'video[width]','prettyName'=>'Video Width'),
				'embed_code'=>array('type'=>'textarea','name'=>'video[embed_code]','prettyName'=>'Video Embed Code'),
				'title'=>array('type'=>'textfield','required'=>true,'name'=>'video[title]','prettyName'=>'Video Title'),
				'thumbnail'=>array('type'=>'textfield','required'=>false,'name'=>'video[thumbnail]','prettyName'=>'Thumbnail'),
				'fetchFromVimeo'=>array('type'=>'checkbox','value'=>1,'database'=>false),
				'media_type'=>array('type'=>'select','required'=>true,'lookup'=>'multimedia_type','name'=>'video[media_type]'),
				'v_id'=>array('type'=>'hidden','value'=>'%%owner_id%%','name'=>'video[v_id]','database'=>false),
				'video_host'=>array('type'=>'select','lookup'=>'video_hosting','required'=>true,'name'=>'video[host]','prettyName'=>'Video Hosted By')
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
		if (!(array_key_exists('id',$_REQUEST) && $data = $this->fetchSingleTest('select * from %s where id = %d',$this->m_tree,$_REQUEST['id'])))
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
		//	load coupon folders attached to this gallery
		//
		$data['destCoupons'] = $this->loadRelations('destCoupons',$this,"altCouponFormat",$data['id'],'galleryfolder','coupon','coupons','and deleted = 0 and enabled = 1 and published = 1',true);
		$frmFlds['destCoupons']['options'] = $this->fetchOptionsTest('select id as code,concat(code," - ",name) as value from coupons where deleted = 0 and enabled = 1 and published = 1 and id in (%s) order by code',implode(',',array_merge(array(0),$data['destCoupons'])));

		//
		//	load coupons attached to this gallery
		//
		$frmFlds['destCouponFolders']['options'] = $this->nodeSelect(0,'coupon_folders',2,false);
		$data['destCouponFolders'] = $this->loadRelations('destCouponFolders',$this,"altCouponFormat",$data['id'],'galleryfolder','couponfolder','coupon_folders','',true);
		
		//
		//	load product folders attached to this gallery
		//
		$frmFlds['destProducts']['options'] = $this->fetchOptionsTest('select id as code,concat(code," - ",name) as value from product where deleted = 0 and enabled = 1 and published = 1 order by code');
		$data['destProducts'] = $this->loadRelations('destProducts',$this,"altProductFormat",$data['id'],'galleryfolder','product','product','and deleted = 0 and enabled = 1 and published = 1',true);

		//
		//	load individual products attached to this gallery
		//
		$frmFlds['destProductFolders']['options'] = $this->nodeSelect(0,'product_folders',2,false);
		$data['destProductFolders'] = $this->loadRelations('destProductFolders',$this,"altCouponFormat",$data['id'],'galleryfolder','productfolder','product_folders','',true);

		//
		//	load event folders attached to this gallery
		//
		$data['destEvents'] = $this->loadRelations('destEvents',$this,"altEventFormat",$data['id'],'galleryfolder','event','events','and deleted = 0 and enabled = 1 and published = 1',true);
		$frmFlds['destEvents']['options'] = $this->fetchOptionsTest('select id as code,name as value from events where id in (select event_id from event_dates where event_date >= curdate()) and deleted = 0 and enabled = 1 and published = 1 and id in (%s) order by start_date',implode(',',array_merge(array(0),$data['destEvents'])));

		//
		//	load individual events attached to this gallery
		//
		$frmFlds['destEventFolders']['options'] = $this->nodeSelect(0,'members_folders',2,false);
		$data['destEventFolders'] = $this->loadRelations('destEventFolders',$this,"altEventFormat",$data['id'],'galleryfolder','eventfolder','members_folders','',true);

		//
		//	load news attached to this gallery
		//
		$data['destNews'] = $this->loadRelations('destNews',$this,"altNewsFormat",$data['id'],'galleryfolder','news','news','and deleted = 0 and enabled = 1 and published = 1',false);
		$frmFlds['destNews']['options'] = $this->fetchOptionsTest('select id as code, title as value from news where deleted = 0 and enabled = 1 and published = 1 and id in (%s)',implode(',',array_merge(array(0),$data['destNews'])));

		//
		//	load news folders attached to this gallery
		//
		$frmFlds['destNewsFolders']['options'] = $this->nodeSelect(0,'news_folders',2,false);
		$data['destNewsFolders'] = $this->loadRelations('destNewsFolders',$this,"altNewsFormat",$data['id'],'galleryfolder','newsfolder','news_folders','',false);

		//
		//	load stores attached to this gallery
		//
		$data['destStore'] = $this->loadRelations('destStore',$this,"altStoreFormat",$data['id'],'galleryfolder','store','stores','and deleted = 0 and enabled = 1 and published = 1',false);
		$frmFlds['destStore']['options'] = $this->fetchOptionsTest('select id as code, name as value from stores where deleted = 0 and enabled = 1 and published = 1 and id in (%s)',implode(',',array_merge(array(0),$data['destStore'])));

		//
		//	load store folders attached to this gallery
		//
		$frmFlds['destStoreFolders']['options'] = $this->nodeSelect(0,'store_folders',2,false);
		$data['destStoreFolders'] = $this->loadRelations('destStoreFolders',$this,"altStoreFormat",$data['id'],'galleryfolder','storefolder','store_folders','',false);

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
				if (array_key_exists('addContent',$_POST)) unset($_POST['addContent']);
		}

		$customFields = new custom();
		if (method_exists($customFields,'galleryFolder')) {
			$custom = $customFields->galleryDisplay();
			$form->addTag('customTab',sprintf('<li><a href="#tabs-custom">%s</a></li>',$custom['description']),false);
			$html = $form->getHTML();
			$html = str_replace('%%customInfo%%',$custom['form'],$html);
			$form->setHTML($html);
			$frmFlds = array_merge($frmFlds,$custom['fields']);
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
					$form->setData('id',$data['id']);
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
				foreach($frmFlds as $key=>$fld) {
					if (!(array_key_exists('database',$fld) && $fld['database'] == false)) {
						$values[] = $form->getData($fld['name']);
						if ($data['id'] > 0)
							$flds[] = sprintf('%s = ?',$fld['name']);
						else
							$flds[] = $fld['name'];
					}
				}
				$stmt = $this->prepare(sprintf('update %s set %s where id = %d',$this->m_tree, implode(',',$flds),$data['id']));
				$stmt->bindParams(array_merge(array(str_repeat('s', count($flds))),$values));
				if ($status = $stmt->execute()) {
					if ($status) $status = $status && $this->updateRelations('destCoupons',$data['id'],'galleryfolder','coupon','coupons',true,true);
					if ($status) $status = $status && $this->updateRelations('destCouponFolders',$data['id'],'galleryfolder','couponfolder','coupon_folders',true,false);
					if ($status) $status = $status && $this->updateRelations('destProducts',$data['id'],'galleryfolder','product','product',true,true);
					if ($status) $status = $status && $this->updateRelations('destProductFolders',$data['id'],'galleryfolder','productfolder','product_folders',true,false);
					if ($status) $status = $status && $this->updateRelations('destEvents',$data['id'],'galleryfolder','event','events',true,true);
					if ($status) $status = $status && $this->updateRelations('destEventFolders',$data['id'],'galleryfolder','eventfolder','members_folders',true,false);
					if ($status) $status = $status && $this->updateRelations('destNews',$data['id'],'galleryfolder','news','news',false,true);
					if ($status) $status = $status && $this->updateRelations('destNewsFolders',$data['id'],'galleryfolder','newsfolder','news_folders',false,false);
					if ($status) $status = $status && $this->updateRelations('destStores',$data['id'],'galleryfolder','store','stores',false,true);
					if ($status) $status = $status && $this->updateRelations('destStoreFolders',$data['id'],'galleryfolder','storefolder','store_folders',false,false);
				}
				if ($status) {
					if ($form->getData('twitterPublish') != 0) {
						$data = $form->getAllData();
						if (!$data['enabled']) {
							$status = false;
							$this->addError('Cannot tweet an disabled gallery');
						}
						else
							if (!$status = $this->twitterPost($data['title'], $data['teaser'],sprintf('http://%s%s',HOSTNAME,$this->getUrl('gallery',$data['id'],$data)),$data))
								$this->addError('Error posting to Twitter');
					}
				}
				if ($status) {
					if ($this->isAjax()) {
						$this->logMessage('showPageProperties', 'executing ajax success return', 3);
						$this->addMessage('Record successfully added');
						return $this->ajaxReturn(array(
								'status'=>'true',
								'html'=>'',
								'url'=>'/modit/gallery?p_id='.$data['id']
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
			if ($data = $this->fetchSingleTest('select * from %s where id = %d', $this->m_tree, $_REQUEST['p_id'])) {
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
		if ($p_id > 0 && $data = $this->fetchSingleTest('select * from %s where id = %d', $this->m_tree, $p_id)) {
			if (strlen($data['alternate_title']) > 0) $data['connector'] = '&nbsp;-&nbsp;';
			$form->init($this->getTemplate('showFolderContent'),array('name'=>'showFolderContent'));
			$frmFields = $form->buildForm($this->getFields('showFolderContent'));
			if (array_key_exists('pagenum',$_REQUEST)) 
				$pageNum = $_REQUEST['pagenum'];
			else
				$pageNum = 1;	// no 0 based calcs
			if ($pageNum <= 0) $pageNum = 1;
			if ($this->m_pagination > 0) {
				$perPage = $this->m_pagination;
				$count = $this->fetchScalarTest('select count(n.id) from %s n where n.id in (select f.image_id from %s f where f.folder_id = %d)', $this->m_content, $this->m_junction, $_REQUEST['p_id']);
				$pagination = $this->pagination($count, $perPage, $pageNum, 
					array('prev'=>$this->M_DIR.'forms/paginationPrev.html','next'=>$this->M_DIR.'forms/paginationNext.html',
							'pages'=>$this->M_DIR.'forms/paginationPage.html', 'wrapper'=>$this->M_DIR.'forms/paginationWrapper.html'));
				$start = ($pageNum-1)*$perPage;
			} else {
				$perPage = 9999;
				$start = 0;
				$pagination = '';
			}
			$sortby = 'id';
			$sortorder = 'desc';
			if (count($_POST) > 0 && array_key_exists('showFolderContent',$_POST)) {
				$sortby = $_POST['sortby'];
				$sortorder = $_POST['sortorder'];
				$form->addData($_POST);
			}
			$sql = sprintf('select i.*,j.id as j_id from %s i, %s j where j.folder_id = %d and i.id = j.image_id order by j.sequence limit %d,%d', $this->m_content, $this->m_junction, $_REQUEST['p_id'], $start, $perPage);
			$images = $this->fetchAllTest($sql);
			$this->logMessage('showPageContent', sprintf('sql [%s], records [%d]',$sql, count($images)), 2);
			$output = array();
			//$output[] = '<tr>';
			$ct = 0;
			foreach($images as $image) {
				$frm = new Forms();
				$frm->init($this->getTemplate('imageList'),array());
				$tmp = $frm->buildForm($this->getFields('imageList'));
				$frm->addData($image);
				$output[] = $frm->show();
				$ct += 1;
				//if ($ct % $this->m_perrow == 0)
				//	$output[] = '</tr><tr>';
			}
			//$output[] = '</tr>';
			$form->addTag('images',implode('',$output),false);
			$form->addTag('pagination',$pagination,false);

			//
			//	get any applicable coupons
			//
			$sql = sprintf('select * from coupons c, relations r where c.deleted = 0 and r.owner_id = %d and r.owner_type = "galleryfolder" and c.id = r.related_id and r.related_type="coupon"',$data['id']);
			$coupons = $this->fetchAllTest($sql);
			$output = array();
			foreach($coupons as $coupon) {
				$frm = new Forms();
				$frm->init($this->getTemplate('couponList'),array());
				$tmp = $frm->buildForm($this->getFields('couponList'));
				$frm->addData($coupon);
				$output[] = $frm->show();
			}
			$form->addTag('coupons',implode('',$output),false);

			$coupons = $this->fetchAllTest('select * from coupon_folders c, relations r where r.owner_id = %d and r.owner_type = "galleryfolder" and c.id = r.related_id and r.related_type="couponfolder"',$data['id']);
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
			$sql = sprintf('select * from product c, relations r where c.deleted = 0 and r.owner_id = %d and r.owner_type = "galleryfolder" and c.id = r.related_id and r.related_type="product"',$data['id']);
			$products = $this->fetchAllTest($sql);
			$output = array();
			foreach($products as $product) {
				$frm = new Forms();
				$frm->init($this->getTemplate('infoProductList'),array());
				$tmp = $frm->buildForm($this->getFields('infoProductList'));
				$frm->addData($product);
				$output[] = $frm->show();
			}
			$form->addTag('products',implode('',$output),false);

			$products = $this->fetchAllTest('select * from product_folders c, relations r where r.owner_id = %d and r.owner_type = "galleryfolder" and c.id = r.related_id and r.related_type="productfolder"',$data['id']);
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
			$sql = sprintf('select * from events c, relations r where c.deleted = 0 and r.owner_id = %d and r.owner_type = "galleryfolder" and c.id = r.related_id and r.related_type="event" and c.id in (select event_id from event_dates where event_date >= curdate())',$data['id']);
			$events = $this->fetchAllTest($sql);
			$this->logMessage('showPageContent',sprintf('coupon sql [%s] count[%d]',$sql,count($coupons)),3);
			$output = array();
			foreach($events as $event) {
				$frm = new Forms();
				$frm->init($this->getTemplate('infoEventList'),array());
				$tmp = $frm->buildForm($this->getFields('infoEventList'));
				$frm->addData($event);
				$output[] = $frm->show();
			}
			$form->addTag('events',implode('',$output),false);

			$events = $this->fetchAllTest('select * from members_folders c, relations r where r.owner_id = %d and r.owner_type = "galleryfolder" and c.id = r.related_id and r.related_type="eventfolder"',$data['id']);
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
		if (count($_POST) == 0) {
			if (array_key_exists('formData',$_SESSION) && array_key_exists('gallerySearchForm', $_SESSION['formData']))
				$_POST = $_SESSION['formData']['gallerySearchForm'];
			else
				$_POST = array('showSearchForm'=>1,'enabled'=>0);
		}
		if (count($_POST) > 0 && array_key_exists('showSearchForm',$_POST)) {
			$form->addData($_POST);
			if ($form->validate()) {
				$_SESSION['formData']['gallerySearchForm'] = $form->getAllData();
				$srch = array();
				foreach($frmFields as $key=>$value) {
					switch($key) {
						case 'quicksearch':
							if (array_key_exists('opt_quicksearch',$_POST) && strlen($_POST['opt_quicksearch']) > 0 && $value = $form->getData($key)) {
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
							if (array_key_exists('opt_'.$key,$_POST) && strlen($_POST['opt_'.$key]) > 0 && $value = $form->getData($key)) {
								if ($_POST['opt_'.$key] == 'like' && strpos($value,'%',0) === false) {
									$value = '%'.$value.'%';
								}
								$srch[] = sprintf(' %s %s "%s"',$key, $_POST['opt_'.$key],$this->escape_string($value));
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
								$srch[] = sprintf(' id in (select image_id from %s where folder_id = %d) ',$this->m_junction, $value);
							}
							break;
						case 'enabled':
						case 'published':
						case 'featured':
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
					$perPage = $this->m_pagination;
					$count = $this->fetchScalarTest('select count(n.id) from %s n where 1=1 and %s', $this->m_content, implode(' and ',$srch));
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
					$sql = sprintf('select *, 0 as j_id from %s where 1=1 and %s order by %s %s limit %d,%d', $this->m_content, implode(' and ',$srch),$sortby, $sortorder, $start,$perPage);
					$recs = $this->fetchAllTest($sql);
					$this->logMessage('showSearchForm', sprintf('sql [%s] records [%d]',$sql,count($recs)), 2);
					$output = array();
					$ct = 0;
					$output[] = '<tr>';
					foreach($recs as $image) {
						$frm = new Forms();
						$frm->init($this->getTemplate('imageList'),array());
						$tmp = $frm->buildForm($this->getFields('imageList'));
						$frm->addData($image);
						$output[] = $frm->show();
						$ct += 1;
						//if ($ct % $this->m_perrow == 0)
						//	$output[] = '</tr><tr>';
					}
					$output[] = '</tr>';
					$form->addTag('images',implode('',$output),false);
					$form->addTag('pagination',$pagination,false);
					$form->addTag('statusMessage',sprintf('We found %d record%s matching the criteria',$count,$count > 1 ? 's' : ''));
				}
				else $this->logMessage('showSearchForm',sprintf('no search criteria found [%s]',print_r($_REQUEST,true)),1);
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
		//Sonarcloud report - Variables should be initialized before use
		if(!isset($code)){
			$code = "";
		}
		$form->init($this->getTemplate('showSearchResults'),array('name'=>'showSearchResults','persist'=>true));
		$frmFields = $form->buildForm($this->getFields('showSearchResults'));
		if ($this->isAjax()) {
			$tmp = $form->show();
			return $this->ajaxReturn(array('status'=>'true','html'=>$tmp,'code'=>$code));
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
		if (!(array_key_exists('a_id',$_REQUEST) && $_REQUEST['a_id'] > 0 && $data = $this->fetchSingleTest('select * from %s where id = %d', $this->m_content, $_REQUEST['a_id']))) {
			$data = array('id'=>0,'published'=>false,'image'=>'','thumbnail'=>''); 
		}
		if (count($_REQUEST) > 0 && array_key_exists('destFolders',$_REQUEST) || $data['id'] > 0) {
			$ids = array();
			if (array_key_exists('destFolders',$_REQUEST)) {
				$ids = $_REQUEST['destFolders'];
				if (!is_array($ids)) $ids = array($ids);
			}
			if ($data['id'] > 0) {
				$tmp = $this->fetchScalarAllTest('select folder_id from %s where image_id = %d',$this->m_junction,$data['id']);
				$ids = array_merge($ids,$tmp);
			}
			if (count($ids) > 0) {
				$data['destFolders'] = $ids;
			}
		}

		//
		//	load news attached to this gallery
		//
		$data['destNews'] = $this->loadRelations('destNews',$this,"altNewsFormat",$data['id'],'gallery','news','news','and deleted = 0 and enabled = 1 and published = 1',false);
		$frmFields['destNews']['options'] = $this->fetchOptionsTest('select id as code, title as value from news where deleted = 0 and enabled = 1 and published = 1 and id in (%s)',implode(',',array_merge(array(0),$data['destNews'])));

		//
		//	load news folders attached to this gallery
		//
		$frmFields['destNewsFolders']['options'] = $this->nodeSelect(0,'news_folders',2,false);
		$data['destNewsFolders'] = $this->loadRelations('destNewsFolders',$this,"altNewsFormat",$data['id'],'gallery','newsfolder','news_folders','',false);

		//
		//	load stores attached to this gallery
		//
		$data['destStore'] = $this->loadRelations('destStore',$this,"altStoreFormat",$data['id'],'gallery','store','stores','and deleted = 0 and enabled = 1 and published = 1',false);
		$frmFields['destStore']['options'] = $this->fetchOptionsTest('select id as code, name as value from stores where deleted = 0 and enabled = 1 and published = 1 and id in (%s)',implode(',',array_merge(array(0),$data['destStore'])));

		//
		//	load store folders attached to this gallery
		//
		$frmFields['destStoreFolders']['options'] = $this->nodeSelect(0,'store_folders',2,false);
		$data['destStoreFolders'] = $this->loadRelations('destStoreFolders',$this,"altStoreFormat",$data['id'],'gallery','storefolder','store_folders','',false);

		//
		//	access levels
		//
		$level = $this->getAccessLevel();
		switch($level) {
			case 1:
			case 2:
				break;	// admin can do anything
			case 3:
				$frmFields['published']['disabled'] = true;
			case 4:
			default:
				unset($frmFields['submit']);
				foreach($frmFields as $key=>$fld) {
					$frmFields[$key]['disabled'] = true;
				}
				if ($data['id'] == 0) $this->noAccessError();
				if (array_key_exists('addContent',$_POST)) unset($_POST['addContent']);
		}

		$customFields = new custom();
		if (method_exists($customFields,'galleryDisplay')) {
			$custom = $customFields->galleryDisplay();
			$form->addTag('customTab',sprintf('<li><a href="#tabs-custom">%s</a></li>',$custom['description']),false);
			$html = $form->getHTML();
			$html = str_replace('%%customInfo%%',$custom['form'],$html);
			$form->setHTML($html);
			$frmFields = array_merge($frmFields,$custom['fields']);
		}

		$frmFields = $form->buildForm($frmFields);
		$data['imagesel_a'] = $data['image'];
		$data['imagesel_b'] = $data['thumbnail'];
		$form->addData($data);
		$form->addTag('video',$this->getVideo($data['id'],false),false);
		$status = 'false';	//assume it failed
		if (count($_POST) > 0 && array_key_exists('addContent',$_POST)) {
			$_POST['imagesel_a'] = $_POST['image'];
			$_POST['imagesel_b'] = $_POST['thumbnail'];
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
						//$values[] = $form->getData($fld['name']);//$_REQUEST[$fld['name']];
						//if ($data['id'] > 0)
						//	$flds[sprintf('%s = ?',$fld['name'])] = $form->getData($fld['name']);//$_REQUEST[$fld['name']];
						//else
							$flds[$fld['name']] = $form->getData($fld['name']);
					}
				}				
				if ($id == 0) {
					$flds['created'] = date('c');
					$stmt = $this->prepare(sprintf('insert into %s(%s) values(%s)', $this->m_content, implode(',',array_keys($flds)), str_repeat('?,', count($flds)-1).'?'));
					$stmt->bindParams(array_merge(array(str_repeat('s', count($flds))),array_values($flds)));
					$this->addMessage('adding record');
				}
				else {
					$stmt = $this->prepare(sprintf('update %s set %s where id = %d', $this->m_content, implode('=?,',array_keys($flds)).'=?',$data['id']));
					$stmt->bindParams(array_merge(array(str_repeat('s', count($flds))),array_values($flds)));
					$this->addMessage('updating record');
				}
				$this->beginTransaction();
				if ($stmt->execute()) {
					if ($id == 0) $id = $this->insertId();
					$destFolders = $_POST['destFolders'];
					if (!is_array($destFolders)) $destFolders = array($destFolders);
					//
					//	delete folders we are no longer a member of
					//
					$this->execute(sprintf('delete from %s where image_id = %d and folder_id not in (%s)',$this->m_junction,$id,implode(',',$destFolders)));
					$sql = sprintf('select id from %s where id in (%s) and id not in (select folder_id from %s where image_id = %d and folder_id in (%s))', $this->m_tree, implode(',',$destFolders),$this->m_junction,$id,implode(',',$destFolders));
					$new = $this->fetchScalarAllTest($sql);
					$status = true;
					foreach($new as $key=>$folder) {
						$obj = new preparedStatement(sprintf('insert into %s(image_id,folder_id) values(?,?)',$this->m_junction));
						$obj->bindParams(array('dd',$id,$folder));
						$status = $status && $obj->execute();
						$this->resequence($folder);
					}
					$status = $status && $this->saveVideo($id);
					if ($status) {
						$this->buildAltSizes($form->getData('image'));
						$this->commitTransaction();
						return $this->ajaxReturn(array(
							'status' => 'true',
							'url' => sprintf('/modit/gallery?p_id=%d',$destFolders[0])
						));
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
    function moveImage() {
		$src = 0;
		$dest = 0;
		if (array_key_exists('src',$_REQUEST)) $src = $_REQUEST['src'];
		if (array_key_exists('dest',$_REQUEST)) $dest = $_REQUEST['dest'];
		if ($_REQUEST['type'] == 'tree') {
			if ($src == 0 || $dest == 0) {
				$this->addMessage('Either source or destination was missing');
				return $this->ajaxReturn(array('status' => 'false'));
			}
			$curr = $src;	//$this->fetchScalarTest('select image_id from %s where id = %d',$this->m_junction,$src);
			if ($curr > 0 && $folder = $this->fetchSingleTest('select * from %s where id = %d',$this->m_tree,$dest)) {
				$status = true;
				if (array_key_exists('move',$_REQUEST)) {
					//
					//	move it - delete all other folders
					//
					$curr = $this->fetchScalarTest('select image_id from %s where id = %d',$this->m_junction,$src);
					$this->logMessage('moveImage', sprintf('moving image %d to folder %d',$curr,$dest),2);
					$this->beginTransaction();
					if ($status = $this->execute(sprintf('delete from %s where id = %d', $this->m_junction, $src))) {
						if (!$this->fetchSingleTest('select * from %s where image_id = %d and folder_id = %d',$this->m_junction,$curr,$dest)) {
							$obj = new preparedStatement(sprintf('insert into %s(image_id,folder_id) values(?,?)',$this->m_junction));
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
					$this->logMessage('moveImage', sprintf('cloning image %d to folder %d',$curr,$dest),2);
					if (!($this->fetchSingleTest('select * from %s where image_id = %d and folder_id = %d',$this->m_junction,$curr,$dest))) {
						$obj = new preparedStatement(sprintf('insert into %s(image_id,folder_id) values(?,?)',$this->m_junction));
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
			$src = $this->fetchSingleTest('select * from %s where id = %d',$this->m_junction,$src);
			$sql = sprintf('select * from %s where folder_id = %d order by sequence limit %d,1',$this->m_junction,$src['folder_id'],$dest);
			$dest = $this->fetchSingleTest($sql);
			$this->logMessage("moveImage",sprintf("move src [%s] to dest [%s] sql [%s]",print_r($src,true),print_r($dest,true),$sql),2);
			if (count($src) == 0 || count($dest) == 0) {
				$status = false;
				$this->addMessage('Either the source or destination image was not found');
			}
			else {
				//
				//	swap the order of the images
				//
				$this->logMessage('moveImage', sprintf('swap the sort order of %d and %d',$src['id'],$dest['id']),2);
				$this->beginTransaction();
				$sql = sprintf('update %s set sequence = %d where id = %s',
					$this->m_junction, $src['sequence'] < $dest['sequence'] ? $dest['sequence']+1 : $dest['sequence']-1, $src['id']);
				$this->logMessage("moveImage",sprintf("resequence sql [%s]",$sql),2);
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
		return $this->ajaxReturn(array('status'=>$status?'true':'false'));
	}

    /**
     * @param $folder
     * @return void
     * @throws phpmailerException
     */
    function resequence($folder) {
		$this->logMessage('resequence', "resequencing folder $folder", 2);
		$images = $this->fetchAllTest('select * from %s where folder_id = %d order by sequence',$this->m_junction,$folder);
		$seq = 10;
		foreach($images as $image) {
			$this->execute(sprintf('update %s set sequence = %d where id = %d',$this->m_junction,$seq,$image['id']));
			$seq += 10;
		}
	}

    /**
     * @return false|string
     * @throws phpmailerException
     */
    function deleteImage() {
		$form = new Forms();
		$form->init($this->getTemplate('deleteItem'));
		$flds = $form->buildForm($this->getFields('deleteItem'));
		if (count($_REQUEST) > 0 && $_REQUEST['j_id'] == 0)
			$form->getField('one')->addAttribute('disabled','disabled');
		$form->addData($_REQUEST);
		if (count($_REQUEST) > 0 && array_key_exists('deleteImage',$_REQUEST)) {
			if ($form->validate()) {
				$type = $form->getData('action');
				switch($type) {
					case 'cancel':
						return $this->ajaxReturn(array('status'=>'true','code'=>'closePopup();'));
						break;
					case 'all':
						//$img = $this->fetchScalarTest('select image_id from %s where id = %d',$this->m_junction,$_REQUEST['p_id']);
						$this->execute(sprintf('delete from %s where image_id = %d',$this->m_junction,$_REQUEST['i_id']));
						$this->execute(sprintf('delete from %s where id = %d',$this->m_content,$_REQUEST['i_id']));
						break;
					case 'one':
						$status = $this->execute(sprintf('delete from %s where id = %d',$this->m_junction,$_REQUEST['j_id']));
						$ct = $this->fetchScalarTest('select count(0) from %s where image_id = %d',$this->m_junction,$_REQUEST['i_id']);
						if ($ct == 0)
							$this->execute(sprintf('delete from %s where id = %d',$this->m_content,$_REQUEST['i_id']));
						break;
					default:
						break;
				}
				$form->init($this->getTemplate('deleteItemResult'));
			}
		}
		return $this->ajaxReturn(array('status'=>'true','html'=>$form->show()));
		/*//Sonar Cloud - PHP Major bug - All code should be reachable
		if (array_key_exists('j_id',$_REQUEST)) {
			$id = $_REQUEST['j_id'];
			$curr = $this->fetchScalarTest('select image_id from %s where id = %d',$this->m_junction,$id);
			$level = $this->getAccessLevel();
			if ($level >= 4) return $this->ajaxReturn(array('status'=>'false','messages'=>$this->noAccessError()));
			$this->logMessage('deleteImage', sprintf('deleting image junction %d for image %d',$id,$curr), 2);
			$this->beginTransaction();
			$this->execute(sprintf('delete from %s where id = %d',$this->m_junction,$id));
			if (($remining = $this->fetchScalarTest('select count(0 from %s where image_id = %d',$this->m_junction,$curr))) == 0) {
				$this->logMessage('deleteImage', sprintf('deleting image %d - no more references',$curr), 2);
				$this->execute(sprintf('delete from %s where id = %d',$this->m_content,$curr));
			}
			$this->commitTransaction();
			return $this->ajaxReturn(array('status'=>'true'));
		}*/
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
     * @param int $fromMain
     * @return array|false|mixed|string|string[]|void
     * @throws phpmailerException
     */
    function moduleStatus($fromMain = 0) {
		$ct = $this->fetchScalarTest('select count(0) from %s where published = 0',$this->m_content);
		if ($ct == 0) {
			$_POST = array('showSearchForm'=>1,'published'=>1,'sortby'=>'created','sortorder'=>'desc','pager'=>$this->m_pagination);
			$msg = 'Showing latest images added';
		}
		else {
			$_POST = array('showSearchForm'=>1,'published'=>0,'sortby'=>'created','sortorder'=>'desc','pager'=>$this->m_pagination);
			$msg = 'Showing unpublished images';
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
     * @param $original
     * @return void
     * @throws phpmailerException
     */
    function buildAltSizes($original) {
		$sizes = $GLOBALS['gallery'];
		foreach($sizes as $size) {
			$this->resize($original,$size);
		}
	}

    /**
     * @param $original
     * @param $size
     * @return void
     * @throws phpmailerException
     */
    function resize($original, $size) {
		$this->logMessage("resize",sprintf("original [%s] size [%s]",$original,print_r($size,true)),2);
		$src = urldecode($original);	//switch from url to local file
		$test = str_replace($this->M_ROOT,"",$src);	// strip to file name and possibly subdirectory
		$this->logMessage("resize",sprintf("src [%s] test [%s]",$src,$test),2);
		if (($i = strrpos($test, '/')) !== false) {
			$filename = substr($test, $i + 1);
			$folder = substr($test,0,$i);
		}
		else {
			$filename = $test;
			$folder = "";
		}
		$this->logMessage("resize",sprintf("filename [%s] folder [%s]",$filename,$folder),2);
		$img = new imageresize("..".$src);
		if (!array_key_exists("proportional",$size)) $size["proportional"] = true;
		$tmp = "..".str_replace('originals',$size['dir'],$this->M_ROOT);
		if ($folder != "") {
			if (!file_exists($tmp.$folder)) {
				$full = explode('/',$folder);
				$root = $tmp;
				foreach($full as $tmp) {
					$this->logMessage('resize',sprintf('root [%s] tmp [%s]',$root,$tmp),1);
					$root = $root.$tmp.'/';
					if (!file_exists($root))
						mkdir($root);
				}
			}
			$tmp = "..".str_replace('originals',$size['dir'],$this->M_ROOT);
			$tmp .= $folder."/";
		}
		//else $tmp = $size['dir'];
		$this->logMessage("resize",sprintf("src [%s] dest [%s]",$src,$tmp.$filename),2);
		$img->resize($tmp.$filename,
						array_key_exists('width',$size) ? $size['width'] : 0,
						array_key_exists('height',$size) ? $size['height'] : 0,
						array_key_exists('proportional',$size) ? $size['proportional'] : true,
						array_key_exists('crop',$size) ? $size['crop'] : false
		);
		unset($img);
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
     * @return void
     * @throws phpmailerException
     */
    function resizeAll() {
		set_time_limit(0);
		$images = $this->fetchAllTest('select * from gallery_images');
		foreach($images as $image) {
			$this->buildAltSizes($image['image']);
		}
	}

    /**
     * @return false|string|void
     * @throws phpmailerException
     */
    function deleteContent() {
		if (array_key_exists('p_id',$_REQUEST)) {
			$ct = $this->fetchScalarTest('select count(0) from %s where folder_id = %d',$this->m_junction,$_REQUEST['p_id']);
			if ($ct > 0) {
				$this->addError('Images are still attached to this folder');
				return $this->ajaxReturn(array('status'=>'false'));
			}
			$ct = $this->fetchScalarTest('select count(0) from %s t1, %s t2 where t2.id = %d and t1.left_id > t2.left_id and t1.right_id < t2.right_id and t1.level > t2.level',$this->m_tree, $this->m_tree, $_REQUEST['p_id']);
			if ($ct > 0) {
				$this->addError('Other categories are still attached to this folder');
				return $this->ajaxReturn(array('status'=>'false'));
			}
			if (!$this->deleteCheck('gallery',$_REQUEST['p_id'],$inUse)) {
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
    function myGalleryList() {
		if (array_key_exists('f_id',$_REQUEST) && $_REQUEST['f_id'] > 0) {
			$tmp = parent::galleryList($_REQUEST['f_id'],$this->getTemplate('galleryByFolder'),$this->getFields('galleryByFolder'));
			return $this->ajaxReturn(array('status'=>'true','html'=>$tmp));
		}
		else return $this->ajaxReturn(array('status'=>'true','html'=>''));
	}

    /**
     * @param int $b_id
     * @param $fromPost
     * @return array|mixed|string|string[]
     * @throws phpmailerException
     */
    function getVideo($b_id = 0, $fromPost) {
		$outer = new Forms();
		$outer->init($this->getTemplate('video'));
		if ($fromPost)
			$data = array_key_exists('video',$_POST) ? $_POST['video'] : array('id'=>0);
		else
			if (!$data = $this->fetchSingleTest('select * from videos where owner_id = %d and owner_type = "gallery"', $b_id))
				$data = array('id'=>0);
		$flds = $outer->buildForm($this->getFields('video'));
		$outer->addData($data);
		return $outer->show();
	}

    /**
     * @param $form
     * @return bool
     * @throws phpmailerException
     */
    function validateVideo(&$form) {
		if (!array_key_exists("video",$_POST)) return true;
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

    /**
     * @param $id
     * @return bool
     * @throws phpmailerException
     */
    function saveVideo($id) {
		if (!array_key_exists("video",$_POST)) return true;
		if (strlen($_POST['video']['url']) == 0 && strlen($_POST['video']['embed_code']) == 0) {
			if ($_POST['video']['v_id'] > 0)
				$this->execute(sprintf('delete from videos where owner_type="gallery" and owner_id = %d',$_POST['video']['v_id']));
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
		if (array_key_exists("fetchFromVimeo",$_POST) && $_POST["fetchFromVimeo"] == 1 && array_key_exists("video_id",$_POST["video"]) && strlen($_POST["video"]["video_id"]) > 0) {
			$flds["thumbnail"] = $this->getThumbnail("vimeo",$_POST["video"]["video_id"]);
		}
		if ($data = $this->fetchSingleTest('select * from videos where owner_type="gallery" and owner_id = %d', $form->getData('v_id'))) {
			$stmt = $this->prepare(sprintf('update videos set %s=? where id = %d', implode('=?, ',array_keys($flds)),$data['id']));
		}
		else {
			$flds['owner_type'] = 'gallery';
			$flds['owner_id'] = $id;
			$stmt = $this->prepare(sprintf('insert into videos(%s) values(%s?)', implode(', ', array_keys($flds)), str_repeat('?, ',count($flds)-1)));
		}
		$stmt->bindParams(array_merge(array(str_repeat('s',count($flds))),array_values($flds)));
		$valid = $stmt->execute();
		return $valid;
	}

}

?>