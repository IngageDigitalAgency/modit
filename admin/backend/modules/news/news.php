<?php

class news extends Backend {

	private $m_tree = 'news_folders';
	private $m_content = 'news';
	private $m_junction = 'news_by_folder';
	private $m_pagination = 0;
	private $m_perrow = 5;

    /**
     * @throws phpmailerException
     */
	public function __construct() {
		$this->M_DIR = 'backend/modules/news/';
		$this->setTemplates(
			array(
				'deleteItem'=>$this->M_DIR.'forms/deleteItem.html',
				'deleteItemResult'=>$this->M_DIR.'forms/deleteItemResult.html',
				'main'=>$this->M_DIR.'news.html',
				'form'=>$this->M_DIR.'forms/form.html',
				'showContentTree'=>$this->M_DIR.'forms/contenttree.html',
				'newsInfo'=>$this->M_DIR.'forms/newsInfo.html',
				'folderProperties'=>$this->M_DIR.'forms/folder.html',
				'showFolderContent'=>$this->M_DIR.'forms/folderContent.html',
				'folderInfo'=>$this->M_DIR.'forms/folderInfo.html',
				'articleList'=>$this->M_DIR.'forms/articleList.html',
				'showSearchForm'=>$this->M_DIR.'forms/searchForm.html',
				'couponList'=>$this->M_DIR.'forms/couponsList.html',
				'infoEventList'=>$this->M_DIR.'forms/eventsList.html',
				'couponFolderList'=>$this->M_DIR.'forms/couponFoldersList.html',
				'infoEventFolderList'=>$this->M_DIR.'forms/eventFoldersList.html',
				'productFolderList'=>$this->M_DIR.'forms/productFoldersList.html',
				'productList'=>$this->M_DIR.'forms/productsList.html',
				'addContent'=>$this->M_DIR.'forms/addContent.html',
				'addContentSuccess'=>$this->M_DIR.'forms/addContentSuccess.html',
				'addArticle'=>$this->M_DIR.'forms/addArticle.html',
				'addFolder'=>$this->M_DIR.'forms/addFolder.html',
				'header'=>$this->M_DIR.'forms/heading.html',
				'editResult'=>$this->M_DIR.'forms/editResult.html',
				'deleteResult'=>$this->M_DIR.'forms/deleteResult.html',
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
			'productList'=>array(
				'options'=>array(),
				'enabled'=>array('type'=>'booleanIcon'),
				'published'=>array('type'=>'booleanIcon'),
				'name'=>array('type'=>'tag'),
				'code'=>array('type'=>'tag')
			),
			'addContent'=>array(
				'options'=>array('method'=>'post','action'=>'/modit/ajax/addContent/news'),
				'id'=>array('type'=>'tag','database'=>false),
				'title'=>array('type'=>'input','required'=>true,'prettyName'=>'Title'),
				'subtitle'=>array('type'=>'input','required'=>false),
				'news_type'=>array('type'=>'select','multiple'=>'multiple','required'=>false,'idlookup'=>'newsType','database'=>false,'id'=>'news_type'),
				'created'=>array('type'=>'datestamp','mask'=>'d-M-Y h:m:i','database'=>false),
				'expires'=>array('type'=>'datepicker','required'=>false,'id'=>'addExpires','prettyName'=>'Expires'),
				'body'=>array('type'=>'textarea','required'=>true,'id'=>'newsBody','class'=>'mceAdvanced','prettyName'=>'Article body'),
				'enabled'=>array('type'=>'checkbox','required'=>false,'value'=>1),
				'published'=>array('type'=>'checkbox','required'=>false,'value'=>1),
				'featured'=>array('type'=>'checkbox','required'=>false,'value'=>1),
				'featured_start_date'=>array('type'=>'datetimepicker','required'=>false,'id'=>'featuredStartDate','AMPM'=>'AMPM','prettyName'=>'Featured Start Date'),
				'featured_end_date'=>array('type'=>'datetimepicker','required'=>false,'id'=>'featuredEndDate','AMPM'=>'AMPM','prettyName'=>'Featured End Date'),
				'deleted'=>array('type'=>'checkbox','required'=>false,'value'=>1),
				'image1'=>array('type'=>'tag','required'=>false,'prettyName'=>'Image 1'),
				'image2'=>array('type'=>'tag','required'=>false),
				'imagesel_a'=>array('type'=>'image','unknown'=>true,'database'=>false,'id'=>'imagesel_a'),
				'imagesel_b'=>array('type'=>'image','unknown'=>true,'database'=>false,'id'=>'imagesel_b'),
				'teaser'=>array('type'=>'textarea','required'=>false,'id'=>'teaser','class'=>'mceSimple'),
				'submit'=>array('type'=>'submitbutton','database'=>false,'value'=>'Save'),
				'addContent'=>array('type'=>'hidden','value'=>1,'database'=>false),
				'attachment'=>array('type'=>'input'),
				'couponFolders'=>array('type'=>'select','database'=>false,'id'=>'couponFolderSelector','options'=>$this->nodeSelect(0, 'coupon_folders', 2, false, false),'reformatting'=>false),
				'eventFolders'=>array('type'=>'select','database'=>false,'id'=>'eventFolderSelector','options'=>$this->nodeSelect(0, 'members_folders', 2, false, false),'reformatting'=>false),
				'productFolders'=>array('type'=>'select','database'=>false,'id'=>'productFolderSelector','options'=>$this->nodeSelect(0, 'product_folders', 2, false, false),'reformatting'=>false),
				'twitterPublish'=>array('type'=>'checkbox','database'=>false,'value'=>1),
				'facebookPublish'=>array('type'=>'checkbox','database'=>false,'value'=>1),
				'destCoupons'=>array('type'=>'select','database'=>false,'id'=>'destCoupons','reformatting'=>false,'multiple'=>'multiple'),
				'destCouponFolders'=>array('type'=>'select','database'=>false,'id'=>'destCouponFolders','reformatting'=>false,'multiple'=>'multiple'),
				'destProductFolders'=>array('name'=>'destProductFolders','type'=>'select','multiple'=>'multiple','required'=>false,'id'=>'destProductFolders','database'=>false,'reformatting'=>false),
				'relatedDestProducts'=>array('name'=>'relatedDestProducts','type'=>'select','multiple'=>'multiple','required'=>false,'id'=>'relatedDestProducts','database'=>false),
				'destGalleryFolders'=>array('name'=>'destGalleryFolders','type'=>'select','multiple'=>'multiple','required'=>false,'id'=>'destGalleryFolders','database'=>false,'reformatting'=>false),
				'relatedDestGallery'=>array('name'=>'relatedDestGallery','type'=>'select','multiple'=>'multiple','required'=>false,'id'=>'relatedDestGallery','database'=>false),
				'galleryFolders'=>array('type'=>'select','database'=>false,'id'=>'galleryFolderSelector','options'=>$this->nodeSelect(0, 'gallery_folders', 2, false, false),'reformatting'=>false),
				'destEventFolders'=>array('name'=>'destEventFolders','type'=>'select','multiple'=>'multiple','required'=>false,'id'=>'destEventFolders','database'=>false,'reformatting'=>false),
				'destEvents'=>array('name'=>'destEvents','type'=>'select','multiple'=>'multiple','required'=>false,'id'=>'destEvents','database'=>false),
				'destFolders'=>array('name'=>'destFolders','type'=>'select','multiple'=>'multiple','required'=>true,'id'=>'destFolders','database'=>false,'options'=>$this->nodeSelect(0, 'news_folders', 2, false, false),'reformatting'=>false,'prettyName'=>'Member Of'),
				'destSearch'=>array('name'=>'destSearch','type'=>'select','multiple'=>'multiple','required'=>false,'id'=>'destSearch','database'=>false,'options'=>$this->nodeSelect(0, 'search_groups', 2, false, false),'reformatting'=>false,'prettyName'=>'Search Related'),
				'icon_class'=>array('name'=>'icon_class','type'=>'textfield')
			),
			'showSearchForm'=>array(
				'options'=>array('action'=>'showSearchForm','name'=>'searchForm','id'=>'search_form'),
				'opt_created'=>array('type'=>'select','name'=>'opt_created','lookup'=>'search_options'),
				'opt_expires'=>array('type'=>'select','name'=>'opt_expires','lookup'=>'search_options'),
				'opt_title'=>array('type'=>'select','name'=>'opt_title','lookup'=>'search_string'),
				'title'=>array('type'=>'input','required'=>false,'prettyName'=>'Title'),
				'created'=>array('type'=>'datepicker','required'=>false,'prettyName'=>'Created'),
				'expires'=>array('type'=>'datepicker','required'=>false,'id'=>'searchExpires','prettyName'=>'Expires'),
				'enabled'=>array('type'=>'select','lookup'=>'boolean'),
				'published'=>array('type'=>'select','lookup'=>'boolean'),
				'deleted'=>array('type'=>'select','lookup'=>'boolean'),
				'featured'=>array('type'=>'select','lookup'=>'boolean'),
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
				'image'=>array('type'=>'image'),
				'rollover_image'=>array('type'=>'image'),
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
					'action'=>'/modit/news/showPageProperties',
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
				'template_id'=>array('type'=>'select','required'=>false,'sql'=>'select template_id,title from templates group by id order by title'),

				'couponFolders'=>array('type'=>'select','database'=>false,'id'=>'couponFolderSelector','options'=>$this->nodeSelect(0, 'coupon_folders', 2, false, false),'reformatting'=>false),
				'eventFolders'=>array('type'=>'select','database'=>false,'id'=>'eventFolderSelector','options'=>$this->nodeSelect(0, 'members_folders', 2, false, false),'reformatting'=>false),
				'productFolders'=>array('type'=>'select','database'=>false,'id'=>'productFolderSelector','options'=>$this->nodeSelect(0, 'product_folders', 2, false, false),'reformatting'=>false),

				'destCoupons'=>array('type'=>'select','database'=>false,'id'=>'destCoupons','reformatting'=>false,'multiple'=>'multiple'),
				'destCouponFolders'=>array('type'=>'select','database'=>false,'id'=>'destCouponFolders','reformatting'=>false,'multiple'=>'multiple'),
				'destProductFolders'=>array('name'=>'destProductFolders','type'=>'select','multiple'=>'multiple','required'=>false,'id'=>'destProductFolders','database'=>false,'reformatting'=>false),
				'relatedDestProducts'=>array('name'=>'relatedDestProducts','type'=>'select','multiple'=>'multiple','required'=>false,'id'=>'relatedDestProducts','database'=>false),
				'destEventFolders'=>array('name'=>'destEventFolders','type'=>'select','multiple'=>'multiple','required'=>false,'id'=>'destEventFolders','database'=>false,'reformatting'=>false),
				'destEvents'=>array('name'=>'destEvents','type'=>'select','multiple'=>'multiple','required'=>false,'id'=>'destEvents','database'=>false),
				'submit'=>array('type'=>'submitbutton','value'=>'Save','database'=>false),
				'destGalleryFolders'=>array('name'=>'destGalleryFolders','type'=>'select','multiple'=>'multiple','required'=>false,'id'=>'destGalleryFolders','database'=>false,'reformatting'=>false),
				'relatedDestGallery'=>array('name'=>'relatedDestGallery','type'=>'select','multiple'=>'multiple','required'=>false,'id'=>'relatedDestGallery','database'=>false),
				'galleryFolders'=>array('type'=>'select','database'=>false,'id'=>'galleryFolderSelector','options'=>$this->nodeSelect(0, 'gallery_folders', 2, false, false),'reformatting'=>false)
			),
			'showContentTree' => array(),
			'newsInfo' => array(),
			'showNewsContent' => array(),
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
				'title'=>array('type'=>'tag','reformatting'=>true),
				'created'=>array('type'=>'datestamp','mask'=>'d-M-Y h:m:i'),
				'enabled'=>array('type'=>'booleanIcon'),
				'published'=>array('type'=>'booleanIcon'),
				'featured'=>array('type'=>'booleanIcon'),
				'expires'=>array('type'=>'datestamp','mask'=>'d-M-Y','suppressNull'=>true)
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
		$data['destCoupons'] = $this->loadRelations('destCoupons',$this,"altCouponFormat",$data['id'],'newsfolder','coupon','coupons','and deleted = 0 and enabled = 1 and published = 1',true);
		$sql = sprintf('select id as code,code as value from coupons where deleted = 0 and enabled = 1 and published = 1 and id in (%s) order by code',implode(',',array_merge(array(0),$data['destCoupons'])));
		$frmFlds['destCoupons']['options'] = $this->fetchOptions($sql);

		//
		//	load coupons attached to this folder
		//
		$frmFlds['destCouponFolders']['options'] = $this->nodeSelect(0,'coupon_folders',2,false);
		$data['destCouponFolders'] = $this->loadRelations('destCouponFolders',$this,"altCouponFormat",$data['id'],'newsfolder','couponfolder','coupon_folders','',true);
		
		//
		//	load event folders attached to this folder
		//
		$data['destEvents'] = $this->loadRelations('destEvents',$this,"altEventFormat",$data['id'],'newsfolder','event','events','and deleted = 0 and enabled = 1 and published = 1',true);
		$frmFlds['destEvents']['options'] = $this->fetchOptions(sprintf('select id as code,name as value from events where id in (%s) and id in (select event_id from event_dates where event_date >= curdate()) and deleted = 0 and enabled = 1 and published = 1 order by start_date',implode(',',array_merge(array(0),$data['destEvents']))));

		//
		//	load individual events attached to this folder
		//
		$frmFlds['destEventFolders']['options'] = $this->nodeSelect(0,'members_folders',2,false);
		$data['destEventFolders'] = $this->loadRelations('destEventFolders',$this,"altEventFormat",$data['id'],'newsfolder','eventfolder','members_folders','',true);

		//
		//	load event folders attached to this folder
		//
		$data['relatedDestProducts'] = $this->loadRelations('relatedDestProducts',$this,"altProductFormat",$data['id'],'newsfolder','product','product','and deleted = 0 and enabled = 1 and published = 1',true);
		$frmFlds['relatedDestProducts']['options'] = $this->fetchOptions(sprintf('select id as code,code as value from product where deleted = 0 and enabled = 1 and published = 1 and id in (%s) order by code',implode(',',array_merge(array(0),$data['relatedDestProducts']))));

		//
		//	load individual products attached to this folder
		//
		$frmFlds['destProductFolders']['options'] = $this->nodeSelect(0,'product_folders',2,false);
		$data['destProductFolders'] = $this->loadRelations('destProductFolders',$this,"altProductFormat",$data['id'],'newsfolder','productfolder','product_folders','',true);

		//
		//	load images attached to this category
		//
		$data['relatedDestGallery'] = $this->loadRelations('destGallery',$this,"altGalleryFormat",$data['id'],'newsfolder','gallery','gallery_images','and enabled = 1 and published = 1',true);
		$frmFlds['relatedDestGallery']['options'] = $this->fetchOptions(sprintf('select id as code, title as value from gallery_images where enabled = 1 and published = 1 and id in (%s) order by code',implode(',',array_merge(array(0),$data['relatedDestGallery']))));

		//
		//	load Gallery Folders attached to this category
		//
		$frmFlds['destGalleryFolders']['options'] = $this->nodeSelect(0,'gallery_folders',2,false);
		$data['destGalleryFolders'] = $this->loadRelations('destGalleryFolders',$this,"altGalleryFormat",$data['id'],'newsfolder','galleryfolder','gallery_folders','',true);

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
				if ($data['id'] == 0) {
					$this->noAccessError();
				}
				if (array_key_exists('showPageProperties',$_POST)) unset($_POST['showPageProperties']);
		}

		$customFields = new custom();
		if (method_exists($customFields,'newsFolderDisplay')) {
			$custom = $customFields->newsFolderDisplay();
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
					if ($status) $status = $status && $this->updateRelations('destCoupons',$data['id'],'newsfolder','coupon','coupons',true,true);
					if ($status) $status = $status && $this->updateRelations('destCouponFolders',$data['id'],'newsfolder','couponfolder','coupon_folders',true,false);
					if ($status) $status = $status && $this->updateRelations('destEvents',$data['id'],'newsfolder','event','events',true,true);
					if ($status) $status = $status && $this->updateRelations('destEventFolders',$data['id'],'newsfolder','eventfolder','members_folders',true,false);
					if ($status) $status = $status && $this->updateRelations('relatedDestProducts',$data['id'],'newsfolder','product','product',true,true);
					if ($status) $status = $status && $this->updateRelations('destProductFolders',$data['id'],'newsfolder','productfolder','product_folders',true,false);
					if ($status) $status = $status && $this->updateRelations('destRelatedGallery',$data['id'],'newsfolder','gallery','gallery_images',true,false);
					if ($status) $status = $status && $this->updateRelations('destGalleryFolders',$data['id'],'newsfolder','galleryfolder','gallery_folders',true,false);
				}
				if ($status) {
					$form->addFormSuccess('Record added succesfully');
					if ($this->isAjax()) {
						$this->logMessage('showPageProperties', 'executing ajax success return', 3);
						$this->addMessage('Record successfully added');
						return $this->ajaxReturn(array(
								'status'=>'true',
								'html'=>'',
								'url'=>'/modit/news?p_id='.$data['id']
						));
					}
				} else {
					$this->addError('Error occurred');
					$form->addFormError($this->showErrors(),false);
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
				$form->addFormError('Form validation failed');
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

    /**
     * @param bool $fromMain
     * @return array|false|mixed|string|string[]|void
     * @throws phpmailerException
     */
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
			$perPage = $this->m_perrow;
			if (array_key_exists('pager',$_REQUEST)) $perPage = $_REQUEST['pager'];
			$count = $this->fetchScalar(sprintf('select count(n.id) from %s n where n.id in (select f.article_id from %s f where f.folder_id = %d) and n.deleted = 0', $this->m_content, $this->m_junction, $_REQUEST['p_id']));
			$pagination = $this->pagination($count, $perPage, $pageNum, 
				array('prev'=>$this->M_DIR.'forms/paginationPrev.html','next'=>$this->M_DIR.'forms/paginationNext.html',
						'pages'=>$this->M_DIR.'forms/paginationPage.html', 'wrapper'=>$this->M_DIR.'forms/paginationWrapper.html'));
			$start = ($pageNum-1)*$perPage;
			$sortby = 'sequence';
			$sortorder = 'asc';
			if (count($_POST) > 0 && array_key_exists('showFolderContent',$_POST)) {
				//$sortby = $_POST['sortby'];
				//$sortorder = $_POST['sortorder'];
				$form->addData($_POST);
			}
			$sql = sprintf('select n.*, f.id as j_id, a.fname, a.lname from %s n left join users a on n.author = a.id left join %s f on n.id = f.article_id where f.folder_id = %d and n.deleted = 0 order by %s %s limit %d,%d',  $this->m_content, $this->m_junction, $_REQUEST['p_id'],$sortby, $sortorder, $start,$perPage);
			$news = $this->fetchAll($sql);
			$this->logMessage('showPageContent', sprintf('sql [%s], records [%d]',$sql, count($news)), 2);
			$articles = array();
			$frm = new Forms();
			$frm->init($this->getTemplate('articleList'),array());
			$tmp = $frm->buildForm($this->getFields('articleList'));
			foreach($news as $article) {
				$frm->addData($article);
				$articles[] = $frm->show();
			}
			$this->logMessage("showPageContent",sprintf("articles [%s]",print_r($articles,true)),2);
			$form->addTag('articles',implode('',$articles),false);
			$form->addTag('pagination',$pagination,false);
			$form->addData($data);
			$form->addTag('coupons',$this->displayRelations($data['id'],'coupons','newsfolder','coupon',' and c.deleted = 0',true,$this->getFields('couponList'),$this->getTemplate('couponList')),false);
			$form->addTag('couponFolders',$this->displayRelations($data['id'],'coupon_folders','newsfolder','couponfolder','',true,$this->getFields('couponFolderList'),$this->getTemplate('couponFolderList')),false);
			$form->addTag('events',$this->displayRelations($data['id'],'events','newsfolder','event',' and c.deleted = 0',true,$this->getFields('couponList'),$this->getTemplate('couponList')),false);
			$form->addTag('eventFolders',$this->displayRelations($data['id'],'members_folders','newsfolder','eventfolder','',true,$this->getFields('couponFolderList'),$this->getTemplate('couponFolderList')),false);
			$form->addTag('products',$this->displayRelations($data['id'],'product','newsfolder','product',' and c.deleted = 0',true,$this->getFields('productList'),$this->getTemplate('productList')),false);
			$form->addTag('productFolders',$this->displayRelations($data['id'],'product_folders','newsfolder','productfolder','',true,$this->getFields('productFolderList'),$this->getTemplate('productFolderList')),false);

			//
			//	get any applicable coupons
			//
			$coupons = $this->fetchAll(sprintf('select * from coupons c, relations r where c.deleted = 0 and r.owner_id = %d and r.owner_type = "newsfolder" and c.id = r.related_id and r.related_type="coupon"',$data['id']));
			$output = array();
			foreach($coupons as $coupon) {
				$frm = new Forms();
				$frm->init($this->getTemplate('couponList'),array());
				$tmp = $frm->buildForm($this->getFields('couponList'));
				$frm->addData($coupon);
				$output[] = $frm->show();
			}
			$form->addTag('coupons',implode('',$output),false);

			$coupons = $this->fetchAll(sprintf('select * from coupon_folders c, relations r where r.owner_id = %d and r.owner_type = "newsfolder" and c.id = r.related_id and r.related_type="couponfolder"',$data['id']));
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
			//	get any applicable events
			//
			$events = $this->fetchAll(sprintf('select * from events c, relations r where c.deleted = 0 and r.owner_id = %d and r.owner_type = "newsfolder" and c.id = r.related_id and r.related_type="event" and c.id in (select event_id from event_dates where event_date >= curdate())',$data['id']));
			$output = array();
			foreach($events as $event) {
				$frm = new Forms();
				$frm->init($this->getTemplate('infoEventList'),array());
				$tmp = $frm->buildForm($this->getFields('infoEventList'));
				$frm->addData($event);
				$output[] = $frm->show();
			}
			$form->addTag('events',implode('',$output),false);

			$events = $this->fetchAll(sprintf('select * from members_folders c, relations r where r.owner_id = %d and r.owner_type = "newsfolder" and c.id = r.related_id and r.related_type="eventfolder"',$data['id']));
			$output = array();
			foreach($events as $event) {
				$frm = new Forms();
				$frm->init($this->getTemplate('infoEventFolderList'),array());
				$tmp = $frm->buildForm($this->getFields('infoEventFolderList'));
				$frm->addData($event);
				$output[] = $frm->show();
			}
			$form->addTag('eventFolders',implode('',$output),false);
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
			if (array_key_exists('formData',$_SESSION) && array_key_exists('newsSearchForm', $_SESSION['formData']))
				$_POST = $_SESSION['formData']['newsSearchForm'];
			else
				$_POST = array('showSearchForm'=>1,'deleted'=>0,'sortby'=>'published','sortorder'=>'asc','pager'=>$this->m_perrow);
		$this->logMessage("showSearchForm",sprintf("post is now [%s]",print_r($_POST,true)),2);
		if (count($_POST) > 0 && array_key_exists('showSearchForm',$_POST)) {
			if ((!array_key_exists('deleted',$_POST)) || strlen($_POST['deleted']) == 0) $_POST['deleted'] = 0;
			$form->addData($_POST);
			if ($form->validate()) {
				if (strlen($form->getData("quicksearch")) > 0) {
					$_SESSION['formData']['newsSearchForm'] = array('showSearchForm'=>1,'opt_quicksearch'=>'like','quicksearch'=>$form->getData("quicksearch"),'pager'=>$form->getData("pager"),'deleted'=>0);
				}
				else
					$_SESSION['formData']['newsSearchForm'] = $form->getAllData();
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
								$tmp[] = sprintf(' body %s "%s"',$_POST['opt_quicksearch'],$this->escape_string($value));
								$srch = array(sprintf('(%s)',implode(' or ',$tmp)),'deleted = 0');
								break 2;
							}
							break;
						case 'title':
							if (array_key_exists('opt_'.$key,$_POST) && $_POST['opt_'.$key] != '' && strlen($_POST['opt_'.$key]) > 0 && $value = $form->getData($key)) {
								if ($_POST['opt_'.$key] == 'like' && strpos($value,'%',0) === false) {
									$value = '%'.$value.'%';
								}
								$srch[] = sprintf(' %s %s "%s"',$key,$_POST['opt_'.$key],$this->escape_string($value));
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
								$srch[] = sprintf(' n.id in (select article_id from %s where folder_id = %d) ', $this->m_junction, $value);
							}
							break;
						case 'enabled':
						case 'published':
						case 'deleted':
						case 'featured':
							if (!is_null($value = $form->getData($key)))
								if (strlen($value) > 0)
									$srch[] = sprintf(' %s = %s',$key,$this->escape_string($value));
							break;
						default:
							break;
					}
				}
				$this->logMessage("showSearchForm",sprintf("srch [%s] form [%s]",print_r($srch,true),print_r($form,true)),2);
				if (count($srch) > 0) {
					if (array_key_exists('pagenum',$_REQUEST))
						$pageNum = $_REQUEST['pagenum'];
					else
						$pageNum = 1;	// no 0 based calcs
					$perPage = $this->m_perrow;
					if (array_key_exists('pager',$_POST)) $perPage = $_POST['pager'];
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
					$sql = sprintf('select n.*, 0 as j_id from %s n where %s order by %s %s limit %d,%d',
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
			$data = array('id'=>0,'published'=>0,'image1'=>'','image2'=>''); 
		}

		if (count($_REQUEST) > 0 && array_key_exists('destFolders',$_REQUEST) || $data['id'] > 0) {
			$ids = array();
			$srch = array();
			if (array_key_exists('destFolders',$_REQUEST)) {
				$ids = $_REQUEST['destFolders'];
				if (!is_array($ids)) $ids = array($ids);
			}
			if (array_key_exists('destSearch',$_REQUEST)) {
				$srch = $_REQUEST['destSearch'];
				if (!is_array($srch)) $srch = array($srch);
			}
			if ($data['id'] > 0) {
				$tmp = $this->fetchScalarAll(sprintf('select folder_id from %s where article_id = %d', $this->m_junction, $data['id']));
				$ids = array_merge($ids,$tmp);
				$tmp = $this->fetchScalarAll(sprintf('select folder_id from news_by_search_group where article_id = %d', $data['id']));
				$srch = array_merge($srch,$tmp);
			}
			if (count($ids) > 0) {
				$data['destFolders'] = $ids;
			}
			if (count($srch) > 0) {
				$data['destSearch'] = $srch;
			}
		}

		//
		//	load coupon folders attached to this folder
		//
		$data['destCoupons'] = $this->loadRelations('destCoupons',$this,"altCouponFormat",$data['id'],'news','coupon','coupons','and deleted = 0 and enabled = 1 and published = 1',true);
		$frmFields['destCoupons']['options'] = $this->fetchOptions(sprintf('select id as code,code as value from coupons where deleted = 0 and enabled = 1 and published = 1 and id in (%s) order by code',implode(',',array_merge(array(0),$data['destCoupons']))));
		$this->logMessage('addContent',sprintf('destCoupons [%s] field [%s] ',print_r($data,true),print_r($frmFields['destCoupons'],true)),1);

		//
		//	load coupons attached to this folder
		//
		$frmFields['destCouponFolders']['options'] = $this->nodeSelect(0,'coupon_folders',2,false);
		$data['destCouponFolders'] = $this->loadRelations('destCouponFolders',$this,"altCouponFormat",$data['id'],'newsfolder','couponfolder','coupon_folders','',true);
		
		//
		//	load event folders attached to this folder
		//
		$data['destEvents'] = $this->loadRelations('destEvents',$this,"altEventFormat",$data['id'],'news','event','events','and deleted = 0 and enabled = 1 and published = 1',true);
		$frmFields['destEvents']['options'] = $this->fetchOptions(sprintf('select id as code,name as value from events where id in (select event_id from event_dates where event_date >= curdate()) and deleted = 0 and enabled = 1 and published = 1 and id in (%s) order by start_date',implode(',',array_merge(array(0),$data['destEvents']))));

		//
		//	load individual events attached to this folder
		//
		$frmFields['destEventFolders']['options'] = $this->nodeSelect(0,'members_folders',2,false);
		$data['destEventFolders'] = $this->loadRelations('destEventFolders',$this,"altEventFormat",$data['id'],'news','eventfolder','members_folders','',true);

		//
		//	load products attached to this article
		//
		$data['relatedDestProducts'] = $this->loadRelations('destProducts',$this,"altProductFormat",$data['id'],'news','product','product','and deleted = 0 and enabled = 1 and published = 1',true);
		$frmFields['relatedDestProducts']['options'] = $this->fetchOptions(sprintf('select id as code,concat(code," - ",name) as value from product where deleted = 0 and enabled = 1 and published = 1 and id in (%s) order by code',implode(',',array_merge(array(0),$data['relatedDestProducts']))));

		//
		//	load Product Folders attached to this article
		//
		$frmFields['destProductFolders']['options'] = $this->nodeSelect(0,'product_folders',2,false);
		$data['destProductFolders'] = $this->loadRelations('destProductFolders',$this,"altProductFormat",$data['id'],'news','productfolder','product_folders','',true);

		//
		//	load images attached to this article
		//
		$data['relatedDestGallery'] = $this->loadRelations('destGallery',$this,"altGalleryFormat",$data['id'],'news','gallery','gallery_images','and enabled = 1 and published = 1',true);
		$frmFields['relatedDestGallery']['options'] = $this->fetchOptions(sprintf('select id as code, title as value from gallery_images where enabled = 1 and published = 1 and id in (%s) order by code',implode(',',array_merge(array(0),$data['relatedDestGallery']))));

		//
		//	load Gallery Folders attached to this article
		//
		$frmFields['destGalleryFolders']['options'] = $this->nodeSelect(0,'gallery_folders',2,false);
		$data['destGalleryFolders'] = $this->loadRelations('destGalleryFolders',$this,"altGalleryFormat",$data['id'],'news','galleryfolder','gallery_folders','',true);

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
				break;
			case 4:
			default:
				unset($frmFields['submit']);
				foreach($frmFields as $key=>$fld) {
					$frmFields[$key]['disabled'] = true;
				}
				if ($data['id'] == 0) {
					$this->addMessage('You do not have access to this function');
				}
				if (array_key_exists('addContent',$_POST)) unset($_POST['addContent']);
		}

		$customFields = new custom();
		if (method_exists($customFields,'newsDisplay')) {
			$custom = $customFields->newsDisplay();
			$form->addTag('customTab',sprintf('<li><a href="#tabs-custom">%s</a></li>',$custom['description']),false);
			$html = $form->getHTML();
			$html = str_replace('%%customInfo%%',$custom['form'],$html);
			$form->setHTML($html);
			$frmFields = array_merge($frmFields,$custom['fields']);
		}

		if ((count($_REQUEST) > 0 && array_key_exists('news_type',$_REQUEST)) || $data['id'] > 0) {
			$ids = array();
			if (array_key_exists('news_type',$_REQUEST)) {
				$ids = $_REQUEST['news_type'];
				if (!is_array($ids)) $ids = array($ids);
				if ($ids[0] == '') unset($ids[0]);
			} elseif ($data['id'] > 0) {
				$tmp = $this->fetchScalarAll(sprintf('select news_type from news_types where news_id = %d', $data['id']));
				$ids = array_merge($ids,$tmp);
			}
			$data['news_type'] = $ids;
		}
		else $data['news_type'] = array();

		$frmFields = $form->buildForm($frmFields);
		$data['imagesel_a'] = $data['image1'];
		$data['imagesel_b'] = $data['image2'];
		$form->addData($data);
		$form->addTag('video',$this->getVideo($data['id'],false),false);
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
					$flds['author'] = array_key_exists('administrator',$_SESSION) ? $_SESSION['administrator']['user']['id']:0;
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
				if ($status = $stmt->execute()) {
					if ($id == 0) {
						$id = $this->insertId();
						$form->setData('id',$id);
					}
					$destFolders = $_POST['destFolders'];
					if (!is_array($destFolders)) $destFolders = array($destFolders);
					//
					//	delete folders we are no longer a member of
					//
					$this->execute(sprintf('delete from %s where article_id = %d and folder_id not in (%s)', $this->m_junction, $id,implode(',',$destFolders)));
					$new = $this->fetchScalarAll(sprintf('select id from %s where id in (%s) and id not in (select folder_id from %s where article_id = %d and folder_id in (%s))',
						$this->m_tree,implode(',',$destFolders),$this->m_junction,$id,implode(',',$destFolders)));
					foreach($new as $key=>$folder) {
						$obj = new preparedStatement(sprintf('insert into %s(article_id,folder_id) values(?,?)',$this->m_junction));
						$obj->bindParams(array('dd',$id,$folder));
						$status = $status && $obj->execute();
						$this->resequence($folder);
					}

					$destSearch = array_key_exists('destSearch',$_POST) ? $_POST['destSearch'] : array(0);
					if (!is_array($destSearch)) $destSearch = array($destSearch);
					$this->execute(sprintf('delete from news_by_search_group where article_id = %d and folder_id not in (%s)', $id, implode(',',$destSearch)));
					$new = $this->fetchScalarAll(sprintf('select id from %s where id in (%s) and id not in (select folder_id from news_by_search_group where article_id = %d and folder_id in (%s))',
						$this->m_tree,implode(',',$destSearch),$id,implode(',',$destSearch)));
					foreach($new as $key=>$folder) {
						$obj = new preparedStatement('insert into news_by_search_group(article_id,folder_id) values(?,?)');
						$obj->bindParams(array('dd',$id,$folder));
						$status = $status && $obj->execute();
						$this->resequence($folder);
					}

					if ($status) $status = $status && $this->updateRelations('destCoupons',$data['id'],'news','coupon','coupons',true,true);
					if ($status) $status = $status && $this->updateRelations('destCouponFolders',$data['id'],'news','couponfolder','coupon_folders',true,false);
					if ($status) $status = $status && $this->updateRelations('destProducts',$data['id'],'news','product','product',true,true);
					if ($status) $status = $status && $this->updateRelations('destProductFolders',$data['id'],'news','productfolder','product_folders',true,false);
					if ($status) $status = $status && $this->updateRelations('destEvents',$data['id'],'news','event','events',true,true);
					if ($status) $status = $status && $this->updateRelations('destEventFolders',$data['id'],'news','eventfolder','members_folders',true,false);
					if ($status) $status = $status && $this->updateRelations('relatedDestProducts',$data['id'],'news','product','product',true,true);
					if ($status) $status = $status && $this->updateRelations('destProductFolders',$data['id'],'news','productfolder','product_folders',true,false);
					if ($status) $status = $status && $this->updateRelations('destRelatedGallery',$data['id'],'news','gallery','gallery_images',true,false);
					if ($status) $status = $status && $this->updateRelations('destGalleryFolders',$data['id'],'news','galleryfolder','gallery_folders',true,false);
					if (array_key_exists('news_type',$data) && count($data['news_type']) > 0) {
						$status = $status && $this->execute(sprintf('delete from news_types where news_id = %d and news_type not in (%s)',$id,implode(',',array_merge(array(0),$data['news_type']))));
						$sql = sprintf('select news_type from news_types where news_id = %d and news_type in (%s)',$id,implode(',',array_merge(array(0),$data['news_type'])));
						$currIds = $this->fetchScalarAll($sql);
						$diff = array_diff($data['news_type'],$currIds);
						$this->logMessage('addContent',sprintf('sql [%s] currIds [%s] data [%s] diff [%s]',$sql,print_r($currIds,true),print_r($data['news_type'],true),print_r($diff,true)),1);
						foreach($diff as $key=>$value) {
							$stmt = $this->prepare(sprintf('insert into news_types(news_id,news_type) values(?,?)'));
							$stmt->bindParams(array('ii',$id,$value));
							$status = $status && $stmt->execute();
						}
					}
					else {
						$status = $status && $this->execute(sprintf('delete from news_types where news_id = %d',$id));
					}
					$status = $status && $this->saveVideo($id);
					if ($status) {
						$this->commitTransaction();
						if ($form->getData('twitterPublish') != 0) {
							$data = $form->getAllData();
							if ($data['published'] == 0 || $data['enabled'] == 0) {
								$this->addError('Cannot tweet an unpublished or disabled item');
								$status = false;
							}
							else {
								if (!$status = $this->twitterPost($data['title'], $data['teaser'],sprintf('http://%s%s',HOSTNAME,$this->getUrl('news',$id,$data)),$data))
									$this->addError('Error posting to Twitter');
							}
						}
						if ($form->getData('facebookPublish') != 0) {
							$data = $form->getAllData();
							if ($data['published'] == 0 || $data['enabled'] == 0) {
								$this->addError('Cannot post an unpublished or disabled item');
								$status = false;
							}
							else {
								if (!$status = $this->facebookPost($data['title'], $data['teaser'],sprintf('http://%s%s',HOSTNAME,$this->getUrl('news',$id,$data)),$data)) {
									$this->addError('Error posting to Facebook');
								}
							}
						}
						if ($status) {
							//
							//	if adding, default them back to the first folder they added to
							//
							if ($data['id'] == 0) {
								return $this->ajaxReturn(array('status' => 'true','url' => sprintf('/modit/news?p_id=%d',$destFolders[0])));
							}
							else {
								$form->init($this->getTemplate('editResult'));
								return $this->ajaxReturn(array('status' => 'true','html' => $form->show()));
							}
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
		if (array_key_exists('src',$_REQUEST)) $src = $_REQUEST['src'];
		if (array_key_exists('dest',$_REQUEST)) $dest = $_REQUEST['dest'];
		$this->logMessage("moveArticle",sprintf("src [$src] dest [$dest]"),1);
		if ($_REQUEST['type'] == 'tree') {
			if ($src == 0 || $dest == 0 || !array_key_exists('type',$_REQUEST)) {
				$this->addError('Articles cannot be moved from search mode, only copied');
				return $this->ajaxReturn(array('status' => 'false'));
			}
			$curr = $src;	//$this->fetchScalar(sprintf('select article_id from %s where id = %d',$this->m_junction,$src));
			if ($curr > 0 && $folder = $this->fetchSingle(sprintf('select * from %s where id = %d',$this->m_tree,$dest))) {
				$status = true;
				if (array_key_exists('move',$_REQUEST)) {
					//
					//	move it - delete all other folders
					//
					$curr = $this->fetchScalar(sprintf('select article_id from %s where id = %d',$this->m_junction,$src));
					$this->logMessage('moveArticle', sprintf('moving news %d to folder %d',$curr,$dest),2);
					$this->beginTransaction();
					if ($status = $this->execute(sprintf('delete from %s where id = %d', $this->m_junction, $src))) {
						if (!$this->fetchSingle(sprintf('select * from %s where article_id = %d and folder_id = %d',$this->m_junction,$curr,$dest))) {
							$obj = new preparedStatement(sprintf('insert into %s(article_id,folder_id) values(?,?)',$this->m_junction));
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
					$this->logMessage('moveArticle', sprintf('cloning news %d to folder %d',$curr,$dest),2);
					if (!($this->fetchSingle(sprintf('select * from %s where article_id = %d and folder_id = %d',$this->m_junction,$curr,$dest)))) {
						$obj = new preparedStatement(sprintf('insert into %s(article_id,folder_id) values(?,?)',$this->m_junction));
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
		$articles = $this->fetchAll(sprintf('select * from %s where folder_id = %d order by sequence',$this->m_junction,$folder));
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
						//$img = $this->fetchScalar(sprintf('select article_id from %s where id = %d',$this->m_junction,$_REQUEST['j_id']));
						$this->execute(sprintf('delete from %s where article_id = %d',$this->m_junction,$_REQUEST['a_id']));
						$this->execute(sprintf('update %s set deleted = 1 where id = %d',$this->m_content,$_REQUEST['a_id']));
						break;
					case 'one':
						$status = $this->execute(sprintf('delete from %s where id = %d',$this->m_junction,$_REQUEST['j_id']));
						$ct = $this->fetchScalar(sprintf('select count(0) from %s where article_id = %d',$this->m_junction,$_REQUEST['a_id']));
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
	}

    /**
     * @param bool $fromMain
     * @return array|mixed|string|string[]|void
     * @throws phpmailerException
     */
    function addArticle($fromMain = false) {
		$level = $this->getAccessLevel();
		if ($level == 0 || $level > 3) {
			$this->noAccessError();
			return $this->show();
		}
		$form = new Forms();
		$form->init($this->getTemplate('addArticle'));
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
    function addFolder($fromMain = false) {
		$form = new Forms();
		$form->init($this->getTemplate('addFolder'));
		if ($fromMain)
			return $form->show();
		else
			return $this->show($form->show());
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
			if (array_key_exists('formData',$_SESSION) && array_key_exists('newsSearchForm', $_SESSION['formData']))
				$form->addData($_SESSION['formData']['newsSearchForm']);
		return $form->show();
	}

    /**
     * @param int $fromMain
     * @return array|false|mixed|string|string[]|void
     * @throws phpmailerException
     */
    function moduleStatus($fromMain = 0) {
		if (array_key_exists('formData',$_SESSION) && array_key_exists('newsSearchForm', $_SESSION['formData'])) {
			$_POST = $_SESSION['formData']['newsSearchForm'];
			$msg = '';
		}
		else {
			$ct = $this->fetchScalar(sprintf('select count(0) from %s where published = 0',$this->m_content));
			if ($ct == 0) {
				$_POST = array('showSearchForm'=>1,'deleted'=>0,'sortby'=>'created','sortorder'=>'desc','pager'=>5);
				$msg = "Showing latest articles added";
			}
			else {
				$_POST = array('showSearchForm'=>1,'published'=>0,'sortby'=>'created','sortorder'=>'desc','pager'=>5);
				$msg = "Showing unpublished articles";
			}
		}
		$result = $this->showSearchForm($fromMain,$msg);
		return $result;
	}

    /**
     * @return false|string|void
     * @throws phpmailerException
     */
    function deleteContent() {
		if (array_key_exists('p_id',$_REQUEST)) {
			$ct = $this->fetchScalar(sprintf('select count(0) from %s where folder_id = %d',$this->m_junction,$_REQUEST['p_id']));
			if ($ct > 0) {
				$this->addError('Articles are still attached to this folder');
				return $this->ajaxReturn(array('status'=>'false'));
			}
			$ct = $this->fetchScalar(sprintf('select count(0) from %s t1, %s t2 where t2.id = %d and t1.left_id > t2.left_id and t1.right_id < t2.right_id and t1.level > t2.level',$this->m_tree, $this->m_tree, $_REQUEST['p_id']));
			if ($ct > 0) {
				$this->addError('Other categories are still attached to this folder');
				return $this->ajaxReturn(array('status'=>'false'));
			}
			if (!$this->deleteCheck('news',$_REQUEST['p_id'],$inUse)) {
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
			if (!$data = $this->fetchSingle(sprintf('select * from videos where owner_id = %d and owner_type = "news"', $b_id)))
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
				$this->execute(sprintf('delete from video where owner_type="news" and owner_id = %d'));
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
		if ($data = $this->fetchSingle(sprintf('select * from videos where owner_type="news" and owner_id = %d', $form->getData('v_id')))) {
			$stmt = $this->prepare(sprintf('update videos set %s=? where id = %d', implode('=?, ',array_keys($flds)),$data['id']));
		}
		else {
			$flds['owner_type'] = 'news';
			$flds['owner_id'] = $id;
			$stmt = $this->prepare(sprintf('insert into videos(%s) values(%s?)', implode(', ', array_keys($flds)), str_repeat('?, ',count($flds)-1)));
		}
		$stmt->bindParams(array_merge(array(str_repeat('s',count($flds))),array_values($flds)));
		$valid = $stmt->execute();
		return $valid;
	}

}

?>