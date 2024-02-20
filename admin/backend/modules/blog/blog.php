<?php

class blog extends Backend {

	private $m_tree = 'blog_folders';
	private $m_content = 'blog';
	private $m_junction = 'blog_by_folder';
	private $m_comments = 'blog_comments';
	private $m_pagination = 0;
	private $m_perrow = 5;

    /**
     * @throws phpmailerException
     */
	public function __construct() {
		$this->M_DIR = 'backend/modules/blog/';
		$this->setTemplates(
			array(
				'deleteItem'=>$this->M_DIR.'forms/deleteItem.html',
				'deleteItemResult'=>$this->M_DIR.'forms/deleteItemResult.html',
				'main'=>$this->M_DIR.'blog.html',
				'form'=>$this->M_DIR.'forms/form.html',
				'showContentTree'=>$this->M_DIR.'forms/contenttree.html',
				'blogInfo'=>$this->M_DIR.'forms/blogInfo.html',
				'folderProperties'=>$this->M_DIR.'forms/folder.html',
				'showFolderContent'=>$this->M_DIR.'forms/folderContent.html',
				'folderInfo'=>$this->M_DIR.'forms/folderInfo.html',
				'blogList'=>$this->M_DIR.'forms/blogList.html',
				'showSearchForm'=>$this->M_DIR.'forms/searchForm.html',
				'addContent'=>$this->M_DIR.'forms/addContent.html',
				'blogComments'=>$this->M_DIR.'forms/blogComments.html',
				'blogComment'=>$this->M_DIR.'forms/blogCommentRow.html',
				'couponList'=>$this->M_DIR.'forms/couponsList.html',
				'eventList'=>$this->M_DIR.'forms/eventsList.html',
				'couponFolderList'=>$this->M_DIR.'forms/couponFoldersList.html',
				'eventFolderList'=>$this->M_DIR.'forms/eventFoldersList.html',
				'productFolderList'=>$this->M_DIR.'forms/productFoldersList.html',
				'productList'=>$this->M_DIR.'forms/productsList.html',
				'storeList'=>$this->M_DIR.'forms/storeList.html',
				'header'=>$this->M_DIR.'forms/heading.html',
				'editComment'=>$this->M_DIR.'forms/editComment.html',
				'editResult'=>$this->M_DIR.'forms/editResult.html',
				'addFolder'=>$this->M_DIR.'forms/addFolder.html',
				'couponByBlogList'=>$this->M_DIR.'forms/couponList.html',
				'eventByBlogList'=>$this->M_DIR.'forms/eventList.html',
				'addItem'=>$this->M_DIR.'forms/addItem.html',
				'previewVideo'=>$this->M_DIR.'forms/previewVideo.html',
				'previewAudio'=>$this->M_DIR.'forms/previewAudio.html',
				'previewImage'=>$this->M_DIR.'forms/previewImage.html',
				'deleteComment'=>$this->M_DIR.'forms/deleteCommentSuccess.html',
				'approval'=>$this->M_DIR.'forms/approval.html',
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
			'storeByBlogList'=>array(
				'destStores'=>array('type'=>'select','multiple'=>'multiple','id'=>'destStores')
			),
			'couponByBlogList'=>array(
				'destCoupons'=>array('type'=>'select','multiple'=>'multiple','id'=>'destCoupons')
			),
			'eventByBlogList'=>array(
				'destEvents'=>array('type'=>'select','multiple'=>'multiple','id'=>'destEvents')
			),
			'header'=>array(),
			'eventFolderList'=>array(
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
				'code'=>array('type'=>'tag'),
				'name'=>array('type'=>'tag'),
				'start_date'=>array('type'=>'datestamp'),
				'end_date'=>array('type'=>'datestamp')
			),
			'blogComments'=>array(
				'options'=>array('method'=>'post','action'=>'/modit/ajax/loadComments/blog'),
				'pagenum'=>array('type'=>'hidden'),
				'sortby'=>array('type'=>'hidden','value'=>'created'),
				'sortorder'=>array('type'=>'hidden','value'=>'desc'),
				'loadComments'=>array('type'=>'hidden','value'=>1)
			),
			'blogComment'=>array(
				'options'=>array(),
				'approved'=>array('type'=>'booleanIcon'),
				'created'=>array('type'=>'datetimestamp'),
				'content'=>array('type'=>'stripped','count'=>10,'name'=>'content','reformatting'=>false),
				'attachmentIcon'=>array('type'=>'booleanIcon')
			),
			'editComment'=>array(
				'options'=>array('method'=>'post','action'=>'/modit/ajax/editComment/blog','name'=>'editCommentForm','database'=>false),
				'approved'=>array('type'=>'checkbox','value'=>1),
				'created'=>array('type'=>'datetimestamp','database'=>false),
				'attachment'=>array('type'=>'input','id'=>'commentAttachment'),
				'mime_type'=>array('type'=>'select','lookup'=>'mimetypes'),
				'id'=>array('type'=>'tag'),
				'save'=>array('type'=>'submitbutton','database'=>false,'value'=>'Update Comment'),
				'content'=>array('type'=>'textarea','reformatting'=>false,'id'=>'commentTextarea','class'=>'mceSimple'),
				'author_id'=>array('type'=>'select','sql'=>'select id, concat(lastname,", ",firstname) as name from members where id = %%author_id%% order by name'), 
				'editComment'=>array('type'=>'hidden','value'=>1,'database'=>false),
				'rating'=>array('type'=>'select','required'=>false,'lookup'=>'blogRating')
			),
			'addContent'=>array(
				'options'=>array('method'=>'post','action'=>'/modit/ajax/addContent/blog'),
				'id'=>array('type'=>'tag','database'=>false),
				'title'=>array('type'=>'input','required'=>true,'prettyName'=>'Title'),
				'subtitle'=>array('type'=>'input','required'=>false),
				'created'=>array('type'=>'datestamp','mask'=>'d-M-Y h:m:i','database'=>false),
				'expires'=>array('type'=>'datepicker','required'=>false,'id'=>'addExpires','prettyName'=>'Expires'),
				'body'=>array('type'=>'textarea','required'=>true,'id'=>'blogBody','class'=>'mceAdvanced','prettyName'=>'Blog Text'),
				'teaser'=>array('type'=>'textarea','required'=>true,'class'=>'mceSimple','prettyName'=>'Teaser'),
				'published'=>array('type'=>'checkbox','required'=>false,'value'=>1,'checked'=>'checked'),
				'enabled'=>array('type'=>'checkbox','required'=>false,'value'=>1),
				'deleted'=>array('type'=>'checkbox','required'=>false,'value'=>1),
				'image1'=>array('type'=>'tag','required'=>false,'prettyName'=>'Image 1'),
				'image2'=>array('type'=>'tag','required'=>false),
				'imagesel_a'=>array('type'=>'image','unknown'=>true,'database'=>false,'id'=>'imagesel_a'),
				'imagesel_b'=>array('type'=>'image','unknown'=>true,'database'=>false,'id'=>'imagesel_b'),
				'submit'=>array('type'=>'submitbutton','database'=>false,'value'=>'Save'),
				'blogFolders'=>array('type'=>'select','database'=>false,'id'=>'blogFolderSelector','options'=>$this->nodeSelect(0, 'blog_folders', 2, false, false),'reformatting'=>false),
				'couponFolders'=>array('type'=>'select','database'=>false,'id'=>'couponFolderSelector','options'=>$this->nodeSelect(0, 'coupon_folders', 2, false, false),'reformatting'=>false),
				'eventFolders'=>array('type'=>'select','database'=>false,'id'=>'eventFolderSelector','options'=>$this->nodeSelect(0, 'members_folders', 2, false, false),'reformatting'=>false),
				'storeFolders'=>array('type'=>'select','database'=>false,'id'=>'storeFolderSelector','options'=>$this->nodeSelect(0, 'store_folders', 2, false, false),'reformatting'=>false),
				'productFolders'=>array('type'=>'select','database'=>false,'id'=>'productFolderSelector','options'=>$this->nodeSelect(0, 'product_folders', 2, false, false),'reformatting'=>false),
				'addContent'=>array('type'=>'hidden','value'=>1,'database'=>false),
				'destFolders'=>array('name'=>'destFolders','type'=>'select','multiple'=>'multiple','required'=>true,'id'=>'destFolders','database'=>false,'options'=>$this->nodeSelect(0, 'blog_folders', 2, false, false),'reformatting'=>false,'prettyName'=>'Member Of'),
				'destCoupons'=>array('type'=>'select','database'=>false,'id'=>'destCoupons','reformatting'=>false,'multiple'=>'multiple'),
				'destCouponFolders'=>array('type'=>'select','database'=>false,'id'=>'destCouponFolders','reformatting'=>false,'multiple'=>'multiple'),
				'destStores'=>array('type'=>'select','database'=>false,'id'=>'destStores','reformatting'=>false,'multiple'=>'multiple'),
				'destStoreFolders'=>array('type'=>'select','database'=>false,'id'=>'destStoreFolders','reformatting'=>false,'multiple'=>'multiple'),
				'relatedDestProducts'=>array('type'=>'select','database'=>false,'id'=>'relatedDestProducts','reformatting'=>false,'multiple'=>'multiple'),
				'destProductFolders'=>array('type'=>'select','database'=>false,'id'=>'destProductFolders','reformatting'=>false,'multiple'=>'multiple'),
				'destEvents'=>array('type'=>'select','database'=>false,'id'=>'destEvents','reformatting'=>false,'multiple'=>'multiple'),
				'destEventFolders'=>array('type'=>'select','database'=>false,'id'=>'destEventFolders','reformatting'=>false,'multiple'=>'multiple'),
				'twitterPublish'=>array('type'=>'checkbox','database'=>false,'value'=>1),
				'facebookPublish'=>array('type'=>'checkbox','database'=>false,'value'=>1),
				'destSearch'=>array('name'=>'destSearch','type'=>'select','multiple'=>'multiple','required'=>false,'id'=>'destSearch','database'=>false,'options'=>$this->nodeSelect(0, 'search_groups', 2, false, false),'reformatting'=>false,'prettyName'=>'Search Related'),
				'meta_keywords'=>array('type'=>'textarea'),
				'meta_description'=>array('type'=>'textarea'),
				'browser_title'=>array('type'=>'textfield'),
				'author'=>array('type'=>'select','required'=>false,'sql'=>'select id, concat(lname,", ",fname) from users where enabled = 1 and deleted = 0 and id = %%author%%','defaultvalue'=>0)
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
				'v_id'=>array('type'=>'hidden','value'=>'%%owner_id%%','name'=>'video[v_id]','database'=>false),
				'media_type'=>array('type'=>'select','required'=>true,'lookup'=>'multimedia_type','name'=>'video[media_type]'),
				'video_host'=>array('type'=>'select','lookup'=>'video_hosting','required'=>true,'name'=>'video[host]','prettyName'=>'Video Hosted By')
			),
			'showSearchForm'=>array(
				'options'=>array('action'=>'showSearchForm','name'=>'searchForm','id'=>'search_form'),
				'opt_created'=>array('type'=>'select','name'=>'opt_created','lookup'=>'search_options'),
				'opt_expires'=>array('type'=>'select','name'=>'opt_expires','lookup'=>'search_options'),
				'opt_title'=>array('type'=>'select','name'=>'opt_title','lookup'=>'search_string'),
				'title'=>array('type'=>'input','required'=>false),
				'created'=>array('type'=>'datepicker','required'=>false),
				'expires'=>array('type'=>'datepicker','required'=>false,'id'=>'searchExpires'),
				'published'=>array('type'=>'select','lookup'=>'boolean'),
				'enabled'=>array('type'=>'select','lookup'=>'boolean'),
				'deleted'=>array('type'=>'select','lookup'=>'boolean'),
				'showSearchForm'=>array('type'=>'hidden','value'=>1),
				'pagenum'=>array('type'=>'hidden','value'=>1),
				'sortby'=>array('type'=>'hidden','value'=>'created'),
				'sortorder'=>array('type'=>'hidden','value'=>'desc'),
				'folder'=>array('type'=>'select','optionslist' => array('table'=>$this->m_tree,'root'=>0,'indent'=>2,'inclusive'=>false),'database'=>false),
				'submit'=>array('type'=>'submitbutton','value'=>'Search'),
				'quicksearch'=>array('type'=>'input','name'=>'quicksearch','required'=>false),
				'pager'=>array('type'=>'select','required'=>true,'value'=>$this->m_perrow,'lookup'=>'paging','id'=>'pager'),
				'unapproved'=>array('type'=>'select','lookup'=>'boolean'),
				'opt_quicksearch'=>array('type'=>'hidden','value'=>'like')
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
					'action'=>'/modit/blog/showPageProperties',
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

				'productFolders'=>array('type'=>'select','database'=>false,'id'=>'productFolderSelector','reformatting'=>false,'options'=>$this->nodeSelect(0,'product_folders',2,false,false)),
				'couponFolders'=>array('type'=>'select','database'=>false,'id'=>'couponFolderSelector','options'=>$this->nodeSelect(0, 'coupon_folders', 2, false, false),'reformatting'=>false),
				'blogFolders'=>array('type'=>'select','database'=>false,'id'=>'blogFolderSelector','options'=>$this->nodeSelect(0, 'blog_folders', 2, false, false),'reformatting'=>false),
				'storeFolders'=>array('type'=>'select','database'=>false,'id'=>'storeFolderSelector','options'=>$this->nodeSelect(0, 'store_folders', 2, false, false),'reformatting'=>false),
				'eventFolders'=>array('type'=>'select','database'=>false,'id'=>'eventFolderSelector','options'=>$this->nodeSelect(0, 'members_folders', 2, false, false),'reformatting'=>false),

				'destCoupons'=>array('type'=>'select','database'=>false,'id'=>'destCoupons','reformatting'=>false,'multiple'=>'multiple'),
				'destCouponFolders'=>array('type'=>'select','database'=>false,'id'=>'destCouponFolders','reformatting'=>false,'multiple'=>'multiple'),
				'destProductFolders'=>array('name'=>'destProductFolders','type'=>'select','multiple'=>'multiple','required'=>false,'id'=>'destProductFolders','database'=>false,'reformatting'=>false),
				'relatedDestProducts'=>array('name'=>'relatedDestProducts','type'=>'select','multiple'=>'multiple','required'=>false,'id'=>'relatedDestProducts','database'=>false),
				'destEventFolders'=>array('name'=>'destEventFolders','type'=>'select','multiple'=>'multiple','required'=>false,'id'=>'destEventFolders','database'=>false,'reformatting'=>false),
				'destEvents'=>array('name'=>'destEvents','type'=>'select','multiple'=>'multiple','required'=>false,'id'=>'destEvents','database'=>false),
				'submit'=>array('type'=>'submitbutton','value'=>'Save','database'=>false),
				'meta_description'=>array('name'=>'meta_description','type'=>'textarea','required'=>false),
				'meta_keywords'=>array('name'=>'meta_keywords','type'=>'textarea','required'=>false),
				'browser_title'=>array('name'=>'browser_title','type'=>'textfield','required'=>false)
			),
			'showContentTree' => array(),
			'blogInfo' => array(),
			'showBlogContent' => array(),
			'folderInfo' => array(
				'title'=>array('type'=>'tag'),
				'alternate_title'=>array('type'=>'tag'),
				'notes'=>array('type'=>'tag','reformatting'=>false),
				'description'=>array('type'=>'tag','reformatting'=>false),
				'image' => array('type'=>'image','unknown'=>true),
				'rollover_image'=>array('type'=>'image','unknown'=>true)
			),
			'blogList' => array(
				'id'=>array('type'=>'tag'),
				'title'=>array('type'=>'tag'),
				'created'=>array('type'=>'datetimestamp'),
				'published'=>array('type'=>'booleanIcon'),
				'enabled'=>array('type'=>'booleanIcon'),
				'expires'=>array('type'=>'datestamp','mask'=>'d-M-Y','suppressNull'=>true)
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
		$frmFlds['destCoupons']['options'] = $this->fetchOptions(sprintf('select id as code,concat(code," - ",name) as value from coupons where deleted = 0 and enabled = 1 and published = 1 order by code'));
		$data['destCoupons'] = $this->loadRelations('destCoupons',$this,"altCouponFormat",$data['id'],'blogfolder','coupon','coupons','and deleted = 0 and enabled = 1 and published = 1',true);

		//
		//	load coupons attached to this folder
		//
		$frmFlds['destCouponFolders']['options'] = $this->nodeSelect(0,'coupon_folders',2,false);
		$data['destCouponFolders'] = $this->loadRelations('destCouponFolders',$this,"altCouponFormat",$data['id'],'blogfolder','couponfolder','coupon_folders','',true);
		
		//
		//	load event folders attached to this folder
		//
		$frmFlds['destEvents']['options'] = $this->fetchOptions(sprintf('select id as code,name as value from events where id in (select event_id from event_dates where event_date >= curdate()) and deleted = 0 and enabled = 1 and published = 1 order by start_date'));
		$data['destEvents'] = $this->loadRelations('destEvents',$this,"altEventFormat",$data['id'],'blogfolder','event','events','and deleted = 0 and enabled = 1 and published = 1',true);

		//
		//	load individual events attached to this folder
		//
		$frmFlds['destEventFolders']['options'] = $this->nodeSelect(0,'members_folders',2,false);
		$data['destEventFolders'] = $this->loadRelations('destEventFolders',$this,"altEventFormat",$data['id'],'blogfolder','eventfolder','members_folders','',true);

		//
		//	load product folders attached to this folder
		//
		$data['relatedDestProducts'] = $this->loadRelations('relatedDestProducts',$this,"altProductFormat",$data['id'],'blogfolder','product','product','and deleted = 0 and enabled = 1 and published = 1',true);
		$frmFlds['relatedDestProducts']['options'] = $this->fetchOptions(sprintf('select id as code,code as value from product where deleted = 0 and enabled = 1 and published = 1 and id in (%s) order by code',implode(',',array_merge(array(0),$data['relatedDestProducts']))));

		//
		//	load individual events attached to this folder
		//
		$frmFlds['destProductFolders']['options'] = $this->nodeSelect(0,'product_folders',2,false);
		$data['destProductFolders'] = $this->loadRelations('destProductFolders',$this,"altProductFormat",$data['id'],'blogfolder','productfolder','product_folders','',true);

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
					if ($status) $status = $status && $this->updateRelations('destCoupons',$data['id'],'blogfolder','coupon','coupons',true,true);
					if ($status) $status = $status && $this->updateRelations('destCouponFolders',$data['id'],'blogfolder','couponfolder','coupon_folders',true,false);
					if ($status) $status = $status && $this->updateRelations('destEvents',$data['id'],'blogfolder','event','events',true,true);
					if ($status) $status = $status && $this->updateRelations('destEventFolders',$data['id'],'blogfolder','eventfolder','members_folders',true,false);
					if ($status) $status = $status && $this->updateRelations('relatedDestProducts',$data['id'],'blogfolder','product','product',true,true);
					if ($status) $status = $status && $this->updateRelations('destProductFolders',$data['id'],'blogfolder','productfolder','product_folders',true,false);
				}
				if ($status) {
					if ($this->isAjax()) {
						$this->logMessage('showPageProperties', 'executing ajax success return', 3);
						$this->addMessage('Record successfully added');
						return $this->ajaxReturn(array(
								'status'=>'true',
								'html'=>'',
								'url'=>'/modit/blog?p_id='.$data['id']
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
			if (array_key_exists('pager',$_REQUEST)) 
				$perPage = $_REQUEST['pager'];
			else {
				$tmp = $this->checkArray("formData:blogSearchForm:pager",$_SESSION);
				if ($tmp > 0) 
					$perPage = $tmp;
				else
				$perPage = $this->m_perrow;
			}
			$form->setData('pager',$perPage);
			$count = $this->fetchScalar(sprintf('select count(n.id) from %s n where n.id in (select f.blog_id from %s f where f.folder_id = %d)', $this->m_content, $this->m_junction, $_REQUEST['p_id']));
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
			$sql = sprintf('select n.*, f.id as j_id, u.fname, u.lname from %s n left join users u on n.author = u.id left join %s f on n.id = f.blog_id where f.folder_id = %d order by %s %s limit %d,%d',  $this->m_content, $this->m_junction, $_REQUEST['p_id'],$sortby, $sortorder, $start,$perPage);
			$blog = $this->fetchAll($sql);
			$this->logMessage('showPageContent', sprintf('sql [%s], records [%d]',$sql, count($blog)), 2);
			$blogs = array();
			foreach($blog as $blog) {
				$frm = new Forms();
				$frm->init($this->getTemplate('blogList'),array());
				$tmp = $frm->buildForm($this->getFields('blogList'));
				$frm->addData($blog);
				$frm->addTag('accepted',$this->fetchScalar(sprintf('select count(0) from %s where blog_id = %d and approved = 1', $this->m_comments, $blog['id'])));
				$frm->addTag('unaccepted',$this->fetchScalar(sprintf('select count(0) from %s where blog_id = %d and approved = 0', $this->m_comments, $blog['id'])));
				$blogs[] = $frm->show();
			}
			$form->addTag('blogs',implode('',$blogs),false);
			$form->addTag('pagination',$pagination,false);
			$form->addData($data);
			$form->addTag('coupons',$this->displayRelations($data['id'],'coupons','blogfolder','coupon',' and c.deleted = 0',true,$this->getFields('couponList'),$this->getTemplate('couponList')),false);
			$form->addTag('couponFolders',$this->displayRelations($data['id'],'coupon_folders','blogfolder','couponfolder','',true,$this->getFields('couponFolderList'),$this->getTemplate('couponFolderList')),false);
			$form->addTag('events',$this->displayRelations($data['id'],'events','blogfolder','event',' and c.deleted = 0',true,$this->getFields('eventList'),$this->getTemplate('eventList')),false);
			$form->addTag('eventFolders',$this->displayRelations($data['id'],'members_folders','blogfolder','eventfolder','',true,$this->getFields('eventFolderList'),$this->getTemplate('eventFolderList')),false);
			$form->addTag('products',$this->displayRelations($data['id'],'product','blogfolder','event',' and c.deleted = 0',true,$this->getFields('productList'),$this->getTemplate('productList')),false);
			$form->addTag('productFolders',$this->displayRelations($data['id'],'product_folders','blogfolder','eventfolder','',true,$this->getFields('productFolderList'),$this->getTemplate('productFolderList')),false);
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
		$this->logMessage("showSearchForm",sprintf("in routine"),2);
		$form = new Forms();
		$form->init($this->getTemplate('showSearchForm'),array('name'=>'showSearchForm','persist'=>true));
		$frmFields = $form->buildForm($this->getFields('showSearchForm'));
		if (count($_POST) == 0)
			if (array_key_exists('formData',$_SESSION) && array_key_exists('blogSearchForm', $_SESSION['formData']))
				$_POST = $_SESSION['formData']['blogSearchForm'];
			else
				$_POST = array('showSearchForm'=>1,'deleted'=>0,'sortby'=>'published','sortorder'=>'asc','pager'=>$this->m_perrow);
		if (count($_POST) > 0 && array_key_exists('showSearchForm',$_POST)) {
			if ((!array_key_exists('deleted',$_POST)) || strlen($_POST['deleted']) == 0) $_POST['deleted'] = 0;
			$this->logMessage("showSearchForm",sprintf("in post"),2);
			$form->addData($_POST);
			if ($form->validate()) {
				$this->logMessage("showSearchForm",sprintf("validated"),2);
				if (strlen($form->getData("quicksearch")) > 0) {
					$_SESSION['formData']['blogSearchForm'] = array('showSearchForm'=>1,'opt_quicksearch'=>'like','quicksearch'=>$form->getData("quicksearch"),'pager'=>$form->getData("pager"),'deleted'=>0);
				}
				else
					$_SESSION['formData']['blogSearchForm'] = $form->getAllData();
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
								continue;
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
						case 'expires':
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
								$srch[] = sprintf(' n.id in (select blog_id from %s where folder_id = %d) ', $this->m_junction, $value);
							}
							break;
						case 'published':
						case 'enabled':
						case 'deleted':
							if (!is_null($value = $form->getData($key)))
								if (strlen($value) > 0)
									$srch[] = sprintf(' %s = %s',$key,$this->escape_string($value));
							break;
						case 'unapproved':
							if (!is_null($value = $form->getData($key)))
								if (strlen($value) > 0)
									$srch[] = sprintf(' n.id in (select c.blog_id from blog_comments c where c.approved != %d) ',$this->escape_string($value));
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
					if (array_key_exists('pager',$_REQUEST)) 
						$perPage = $_REQUEST['pager'];
					else {
						$tmp = $this->checkArray("formData:blogSearchForm:pager",$_SESSION);
						if ($tmp > 0) 
							$perPage = $tmp;
						else
						$perPage = $this->m_perrow;
					}
					$count = $this->fetchScalar(sprintf('select count(n.id) from %s n where 1=1 and %s', $this->m_content, implode(' and ',$srch)));
					$pageNum = max(1,min($pageNum, (floor(($count-1)/$perPage)+1)));
					$form->setData('pagenum', $pageNum);
					$pagination = $this->pagination($count, $perPage, $pageNum,
							array('prev'=>$this->M_DIR.'forms/paginationPrev.html','next'=>$this->M_DIR.'forms/paginationNext.html',
									'pages'=>$this->M_DIR.'forms/paginationPage.html', 'wrapper'=>$this->M_DIR.'forms/paginationWrapper.html'));
					$start = ($pageNum-1)*$perPage;
					$sortorder = 'desc';
					$sortby = 'created';
					if (array_key_exists('sortby',$_POST) && strlen($_POST['sortby']) > 0) {
						$sortby = $_POST['sortby'];
						$sortorder = $_POST['sortorder'];
					}
					$sql = sprintf('select n.*, 0 as j_id from %s n where %s order by %s %s limit %d,%d',
						 $this->m_content, implode(' and ',$srch),$sortby, $sortorder, $start,$perPage);
					$recs = $this->fetchAll($sql);
					$this->logMessage('showSearchForm', sprintf('sql [%s] records [%d]',$sql,count($recs)), 2);
					$blogs = array();
					foreach($recs as $blog) {
						$frm = new Forms();
						$frm->init($this->getTemplate('blogList'),array());
						$tmp = $frm->buildForm($this->getFields('blogList'));
						$frm->addData($blog);
						$frm->addTag('accepted',$this->fetchScalar(sprintf('select count(0) from %s where blog_id = %d and approved = 1', $this->m_comments, $blog['id'])));
						$frm->addTag('unaccepted',$this->fetchScalar(sprintf('select count(0) from %s where blog_id = %d and approved = 0', $this->m_comments, $blog['id'])));
						$blogs[] = $frm->show();
					}
					$form->addTag('blogs',implode('',$blogs),false);
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
			$data = array('id'=>0,'published'=>false,'image1'=>'','image2'=>'','author'=>$this->getUserInfo("id"));
		}
		if (count($_REQUEST) > 0 && array_key_exists('destFolders',$_REQUEST) || $data['id'] > 0) {
			$ids = array();
			if (array_key_exists('destFolders',$_REQUEST)) {
				$ids = $_REQUEST['destFolders'];
				if (!is_array($ids)) $ids = array($ids);
			}
			if ($data['id'] > 0) {
				$tmp = $this->fetchScalarAll(sprintf('select folder_id from %s where blog_id = %d', $this->m_junction, $data['id']));
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
				$tmp = $this->fetchScalarAll(sprintf('select folder_id from blog_by_search_group where blog_id = %d', $data['id']));
				$srch = array_merge($srch,$tmp);
			}
			if (count($srch) > 0) {
				$data['destSearch'] = $srch;
			}
			$this->logMessage(__FUNCTION__,sprintf('search folders [%s]',print_r($srch,true)),1);
		}

		//
		//	load coupon folders attached to this folder
		//
		$data['destCoupons'] = $this->loadRelations('destCoupons',$this,"altCouponFormat",$data['id'],'blog','coupon','coupons','and deleted = 0 and enabled = 1 and published = 1',true);
		$frmFields['destCoupons']['options'] = $this->fetchOptions(sprintf('select id as code,concat(code," - ",name) as value from coupons where deleted = 0 and enabled = 1 and published = 1 and id in (%s) order by code',implode(',',array_merge(array(0),$data['destCoupons']))));

		//
		//	load coupons attached to this folder
		//
		$frmFields['destCouponFolders']['options'] = $this->nodeSelect(0,'coupon_folders',2,false);
		$data['destCouponFolders'] = $this->loadRelations('destCouponFolders',$this,"altCouponFormat",$data['id'],'blog','couponfolder','coupon_folders','',true);
		
		//
		//	load event folders attached to this folder
		//
		$data['destEvents'] = $this->loadRelations('destEvents',$this,"altEventFormat",$data['id'],'blog','event','events','and deleted = 0 and enabled = 1 and published = 1',true);
		$frmFields['destEvents']['options'] = $this->fetchOptions(sprintf('select id as code,name as value from events where id in (select event_id from event_dates where event_date >= curdate()) and deleted = 0 and enabled = 1 and published = 1 and id in (%s) order by start_date',implode(',',array_merge(array(0),$data['destEvents']))));

		//
		//	load individual products attached to this folder
		//
		$frmFields['destEventFolders']['options'] = $this->nodeSelect(0,'members_folders',2,false);
		$data['destEventFolders'] = $this->loadRelations('destEventFolders',$this,"altEventFormat",$data['id'],'blog','eventfolder','members_folders','',true);

		//
		//	load event folders attached to this folder
		//
		$data['destStores'] = $this->loadRelations('destStores',$this,"altStoreFormat",$data['id'],'blog','store','stores','and deleted = 0 and enabled = 1 and published = 1',true);
		$frmFields['destStores']['options'] = $this->fetchOptions(sprintf('select id as code,name as value from stores where deleted = 0 and enabled = 1 and published = 1 and id in (%s) order by name',implode(',',array_merge(array(0),$data['destStores']))));

		//
		//	load individual products attached to this folder
		//
		$frmFields['destStoreFolders']['options'] = $this->nodeSelect(0,'store_folders',2,false);
		$data['destStoreFolders'] = $this->loadRelations('destStoreFolders',$this,"altStoreFormat",$data['id'],'blog','storefolder','store_folders','',true);

		//
		//	load event folders attached to this folder
		//
		$data['relatedDestProducts'] = $this->loadRelations('relatedDestProducts',$this,"altProductFormat",$data['id'],'blog','product','product','and deleted = 0 and enabled = 1 and published = 1',true);
		$frmFields['relatedDestProducts']['options'] = $this->fetchOptions(sprintf('select id as code,code as value from product where deleted = 0 and enabled = 1 and published = 1 and id in (%s)order by name',implode(',',array_merge(array(0),$data['relatedDestProducts']))));

		//
		//	load individual products attached to this folder
		//
		$frmFields['destProductFolders']['options'] = $this->nodeSelect(0,'product_folders',2,false);
		$data['destProductFolders'] = $this->loadRelations('destProductFolders',$this,"altProductFormat",$data['id'],'blog','productfolder','product_folders','',true);

		$customFields = new custom();
		if (method_exists($customFields,'blogDisplay')) {
			$custom = $customFields->blogDisplay();
			$form->addTag('customTab',sprintf('<li><a href="#tabs-custom">%s</a></li>',$custom['description']),false);
			$html = $form->getHTML();
			$html = str_replace('%%customInfo%%',$custom['form'],$html);
			$form->setHTML($html);
			$frmFields = array_merge($frmFields,$custom['fields']);
		}

		$frmFields = $form->buildForm($frmFields);
		$data['imagesel_a'] = $data['image1'];
		$data['imagesel_b'] = $data['image2'];
		$form->addData($data);
		$form->addTag('video',$this->getVideo($data['id']),false);
		$status = 'false';	//assume it failed
		if (count($_POST) > 0 && array_key_exists('addContent',$_POST)) {
			$_POST['imagesel_a'] = $_POST['image1'];
			$_POST['imagesel_b'] = $_POST['image2'];
			$form->addData($_POST);
			$valid = $form->validate();
			if (array_key_exists('video',$_POST) && count($_POST['video']) > 0) {
				$valid = $valid && $this->validateVideo($form);
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
				}
				else {
					$stmt = $this->prepare(sprintf('update %s set %s where id = %d', $this->m_content, implode(',',array_keys($flds)),$data['id']));
					$stmt->bindParams(array_merge(array(str_repeat('s', count($flds))),array_values($flds)));
				}
				$this->beginTransaction();
				if ($stmt->execute()) {
					if ($id == 0) {
						$id = $this->insertId();
						$data['id'] = $id;
						if (!file_exists('../files/attachments/blog_'.$id))
							mkdir('../files/attachments/blog_'.$id);
					}
					$destFolders = $_POST['destFolders'];
					if (!is_array($destFolders)) $destFolders = array($destFolders);
					//
					//	delete folders we are no longer a member of
					//
					$this->execute(sprintf('delete from %s where blog_id = %d and folder_id not in (%s)', $this->m_junction, $id,implode(',',$destFolders)));
					$new = $this->fetchScalarAll(sprintf('select id from %s where id in (%s) and id not in (select folder_id from %s where blog_id = %d and folder_id in (%s))',
						$this->m_tree,implode(',',$destFolders),$this->m_junction,$id,implode(',',$destFolders)));
					$status = true;
					foreach($new as $key=>$folder) {
						$obj = new preparedStatement(sprintf('insert into %s(blog_id,folder_id) values(?,?)',$this->m_junction));
						$obj->bindParams(array('dd',$id,$folder));
						$status = $status && $obj->execute();
					}
					$destSearch = array_key_exists('destSearch',$_POST) ? $_POST['destSearch'] : array(0);
					if (!is_array($destSearch)) $destSearch = array($destSearch);
					$this->execute(sprintf('delete from blog_by_search_group where blog_id = %d and folder_id not in (%s)', $id, implode(',',$destSearch)));
					$new = $this->fetchScalarAll(sprintf('select id from search_groups where id in (%s) and id not in (select folder_id from blog_by_search_group where blog_id = %d and folder_id in (%s))',
						implode(',',$destSearch),$id,implode(',',$destSearch)));
					foreach($new as $key=>$folder) {
						$obj = new preparedStatement('insert into blog_by_search_group(blog_id,folder_id) values(?,?)');
						$obj->bindParams(array('dd',$id,$folder));
						$status = $status && $obj->execute();
					}
					if ($status) {
						if ($status) $status = $status && $this->updateRelations('destCoupons',$data['id'],'blog','coupon','coupons',true,true);
						if ($status) $status = $status && $this->updateRelations('destCouponFolders',$data['id'],'blog','couponfolder','coupon_folders',true,false);
						if ($status) $status = $status && $this->updateRelations('destEvents',$data['id'],'blog','event','events',true,true);
						if ($status) $status = $status && $this->updateRelations('destEventFolders',$data['id'],'blog','eventfolder','members_folders',true,false);
						if ($status) $status = $status && $this->updateRelations('destStores',$data['id'],'blog','store','stores',true,true);
						if ($status) $status = $status && $this->updateRelations('destStoreFolders',$data['id'],'blog','storefolder','store_folders',true,false);
						if ($status) $status = $status && $this->updateRelations('relatedDestProducts',$data['id'],'blog','product','product',true,true);
						if ($status) $status = $status && $this->updateRelations('destProductFolders',$data['id'],'blog','productfolder','product_folders',true,false);
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
								if (!$status = $this->twitterPost($data['title'], $data['teaser'],sprintf('http://%s%s',HOSTNAME,$this->getUrl('blog',$id,$data)),$data))
									$this->addError('Error posting to Twitter');
							}
						}
						if ($form->getData('facebookPublish') != 0) {
							$data = $form->getAllData();
							if ($data['published'] == 0 || $data['enabled'] == 0) {
								$this->addError('Cannot post an unpublished or disabled item to Facebook');
								$status = false;
							}
							else {
								if (!$status = $this->facebookPost($data['title'], $data['teaser'],sprintf('http://%s%s',HOSTNAME,$this->getUrl('blog',$id,$data)),$data))
									$this->addError('Error posting to Facebook');
							}
						}
						if ($status) {
							return $this->ajaxReturn(array(
								'status' => 'true',
								'url' => sprintf('/modit/blog?p_id=%d',$destFolders[0])
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
			else {
				$this->addError('Form validation failed');
				$this->logMessage('addContent',sprintf('form validation dailed [%s]',print_r($form,true)),1);

			}
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
				return $this->ajaxReturn(array(
						'status' => 'false',
						'html' => 'Either source or destination was missing'
				));
			}
			$curr = $this->fetchScalar(sprintf('select blog_id from %s where id = %d',$this->m_junction,$src));
			if ($curr > 0 && $folder = $this->fetchSingle(sprintf('select * from %s where id = %d',$this->m_tree,$dest))) {
				$status = true;
				if (array_key_exists('move',$_REQUEST)) {
					//
					//	move it - delete all other folders
					//
					$this->logMessage('moveArticle', sprintf('moving blog %d to folder %d',$curr,$dest),2);
					$this->beginTransaction();
					if ($status = $this->execute(sprintf('delete from %s where id = %d', $this->m_junction, $src))) {
						if (!$this->fetchSingle(sprintf('select * from %s where blog_id = %d and folder_id = %d',$this->m_junction,$curr,$dest))) {
							$obj = new preparedStatement(sprintf('insert into %s(blog_id,folder_id) values(?,?)',$this->m_junction));
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
					$this->logMessage('moveArticle', sprintf('cloning blog %d to folder %d',$curr,$dest),2);
					if (!($this->fetchSingle(sprintf('select * from %s where blog_id = %d and folder_id = %d',$this->m_junction,$curr,$dest)))) {
						$obj = new preparedStatement(sprintf('insert into %s(blog_id,folder_id) values(?,?)',$this->m_junction));
						$obj->bindParams(array('dd',$curr,$dest));
						$status = $obj->execute();
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
				$this->logMessage("moveArticle",sprintf("move sql [$sql]"),2);
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
						//$img = $this->fetchScalar(sprintf('select blog_id from %s where id = %d',$this->m_junction,$_REQUEST['j_id']));
						$this->execute(sprintf('delete from %s where blog_id = %d',$this->m_junction,$_REQUEST['b_id']));
						$this->execute(sprintf('update %s set deleted = 1 where id = %d',$this->m_content,$_REQUEST['b_id']));
						break;
					case 'one':
						$status = $this->execute(sprintf('delete from %s where id = %d',$this->m_junction,$_REQUEST['j_id']));
						$ct = $this->fetchScalar(sprintf('select count(0) from %s where blog_id = %d',$this->m_junction,$_REQUEST['b_id']));
						if ($ct == 0)
							$this->execute(sprintf('update %s set deleted = 1 where id = %d',$this->m_content,$_REQUEST['b_id']));
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
    function loadComments() {
		if (array_key_exists('b_id',$_REQUEST) && $_REQUEST['b_id'] > 0) {
			if (!($blog = $this->fetchSingle(sprintf('select * from %s where id = %d',$this->m_content,$_REQUEST['b_id']))))
				return $this->ajaxReturn(array('status'=>'false'));
		}
		else 
			return $this->ajaxReturn(array('status'=>'false'));
		$form = new Forms();
		$form->init($this->getTemplate('blogComments'),array('name'=>'blogCommentsForm','action'=>'/modit/ajax/loadComments/blog'));
		$frmFields = $form->buildForm($this->getFields('blogComments'));
		$form->addData($blog);

		if (array_key_exists('pagenum',$_REQUEST))
			$pageNum = $_REQUEST['pagenum'];
		else
			$pageNum = 1;	// no 0 based calcs
		if ($pageNum <= 0) $pageNum = 1;
		$perPage = $this->m_perrow;
		$count = $this->fetchScalar(sprintf('select count(b.id) from %s b where b.blog_id = %d', $this->m_comments, $_REQUEST['b_id']));
		$pagination = $this->pagination($count, $perPage, $pageNum,
				array('prev'=>$this->M_DIR.'forms/paginationCommentsPrev.html','next'=>$this->M_DIR.'forms/paginationCommentsNext.html',
						'pages'=>$this->M_DIR.'forms/paginationCommentsPage.html', 'wrapper'=>$this->M_DIR.'forms/paginationWrapper.html'));
		$start = ($pageNum-1)*$perPage;
		$sortby = array_key_exists('sortby',$_POST) ? $_POST['sortby'] : 'created';
		$sortorder = array_key_exists('sortorder',$_POST) ? $_POST['sortorder'] : 'desc';
		if (count($_POST) > 0 && array_key_exists('loadComments',$_POST)) {
			$form->addData($_POST);
		}		
		$comments = $this->fetchAll(sprintf('select b.*, concat(m.lastname,", ",m.firstname," ",email) as author from %s b left join members m on m.id = b.author_id where blog_id = %d order by %s %s limit %d,%d', $this->m_comments, $blog['id'], $sortby, $sortorder, $start, $perPage));
		$output = array();
		$c = new Forms();
		$c->init($this->getTemplate('blogComment'));
		$flds = $c->buildForm($this->getFields('blogComment'));
		foreach($comments as $comment) {
			$comment['attachmentIcon'] = strlen($comment['attachment']) > 0;
			$c->addData($comment);
			$output[] = $c->show();
		}
		$form->addTag('pagination',$pagination,false);
		$form->addTag('comments',implode('',$output),false);
		return $this->ajaxReturn(array(
				'status'=>'true',
				'html' => $form->show()
		));
	}

    /**
     * @param bool $fromMain
     * @return array|false|mixed|string|string[]|void
     * @throws phpmailerException
     */
    function editComment($fromMain = false) {
		$form = new Forms();
		$status = 'false';
		$form->init($this->getTemplate('editComment'),array('name'=>'editComment'));
		if (!(array_key_exists('c_id',$_REQUEST) && $_REQUEST['c_id'] > 0 && $data = $this->fetchSingle(sprintf('select * from %s where id = %d', $this->m_comments, $_REQUEST['c_id'])))) {
			return $this->ajaxReturn(array('status'=>false));
		}
		$frmFields = $form->buildForm($this->getFields('editComment'));
		$form->addData($data);
		if (count($_POST) > 0 && array_key_exists('editComment',$_POST)) {
			$form->addData($_POST);
		}
		if (strpos($data['mime_type'],'video') !== false) {
			$preview = new Forms();
			$preview->init($this->getTemplate('previewVideo'));
			$preview->addData($data);
			$form->addTag('preview',$preview->show(),false);
		}
		if (strpos($data['mime_type'],'image') !== false) {
			$img = new image();
			$img->addAttribute('src',$data['attachment']);
			$preview = new Forms();
			$preview->init($this->getTemplate('previewImage'));
			$preview->addTag('img_attachment',$img->show(),false);
			$preview->addData($data);
			$form->addTag('preview',$preview->show(),false);
		}
		if (strpos($data['mime_type'],'audio') !== false) {
			$preview = new Forms();
			$preview->init($this->getTemplate('previewAudio'));
			$preview->addData($data);
			$form->addTag('preview',$preview->show(),false);
		}
		if (count($_POST) > 0 && array_key_exists('editComment',$_POST)) {
			if ($form->validate()) {
				foreach($frmFields as $key=>$fld) {
					if (!(array_key_exists('database',$fld) && $fld['database'] == false)) {
						if ($data['id'] > 0)
							$flds[sprintf('%s = ?',$fld['name'])] = $form->getData($fld['name']);
						else
							$flds[$fld['name']] = $form->getData($fld['name']);
					}
				}
				$stmt = $this->prepare(sprintf('update %s set %s where id = %d', $this->m_comments, implode(',',array_keys($flds)),$data['id']));
				$stmt->bindParams(array_merge(array(str_repeat('s', count($flds))),array_values($flds)));
				$this->beginTransaction();
				if ($stmt->execute()) {
					$this->commitTransaction();
					$this->addMessage('Comment Updated');
					$status = 'true';
					$form->init($this->getTemplate('editResult'));
					return $this->ajaxReturn(array('status'=>$status, 'html'=>$form->show()));
				}
				else {
					$this->rollbackTransaction();
					$this->addError('An error occurred updating the record');
				}
			}
		} else $status = 'true';
		if ($fromMain)
			return $this->show($form->show());
		else
			return $this->ajaxReturn(array('status'=>$status, 'html'=>$form->show()));
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
			if (array_key_exists('formData',$_SESSION) && array_key_exists('blogSearchForm', $_SESSION['formData']))
				$form->addData($_SESSION['formData']['blogSearchForm']);
		return $form->show();
	}

    /**
     * @param int $fromMain
     * @return array|false|mixed|string|string[]|void
     * @throws phpmailerException
     */
    function moduleStatus($fromMain = 0) {
		if (array_key_exists('formData',$_SESSION) && array_key_exists('blogSearchForm', $_SESSION['formData'])) {
			$_POST = $_SESSION['formData']['blogSearchForm'];
			$msg = '';
		}
		else {
			$ct = $this->fetchScalar(sprintf('select count(0) from %s where published = 0',$this->m_content));
			if ($ct == 0) {
				$_POST = array('showSearchForm'=>1,'deleted'=>0,'sortby'=>'created','sortorder'=>'desc','pager'=>$this->m_perrow);
				$msg = "Showing latest entries added";
			}
			else {
				$_POST = array('showSearchForm'=>1,'published'=>0,'sortby'=>'created','sortorder'=>'desc','pager'=>$this->m_perrow);
				$msg = "Showing unpublished entries";
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
     * @return false|string
     * @throws phpmailerException
     */
    function myCouponList() {
		if (array_key_exists('f_id',$_REQUEST) && $_REQUEST['f_id'] > 0) {
			$tmp = parent::couponList($_REQUEST['f_id'],$this->getTemplate('couponByBlogList'),$this->getFields('couponByBlogList'));
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
			$tmp = parent::eventList($_REQUEST['f_id'],$this->getTemplate('eventByBlogList'),$this->getFields('eventByBlogList'));
			return $this->ajaxReturn(array('status'=>'true','html'=>$tmp));
		}
		else return $this->ajaxReturn(array('status'=>'true','html'=>''));
	}

    /**
     * @return false|string
     * @throws phpmailerException
     */
    function myStoreList() {
		if (array_key_exists('f_id',$_REQUEST) && $_REQUEST['f_id'] > 0) {
			$tmp = parent::storeList($_REQUEST['f_id'],$this->getTemplate('storeList'),$this->getFields('storeByBlogList'));
			return $this->ajaxReturn(array('status'=>'true','html'=>$tmp));
		}
		else return $this->ajaxReturn(array('status'=>'true','html'=>''));
	}

    /**
     * @return false|string|void
     * @throws phpmailerException
     */
    function deleteContent() {
		if (array_key_exists('p_id',$_REQUEST)) {
			$ct = $this->fetchScalar(sprintf('select count(0) from %s where folder_id = %d',$this->m_junction,$_REQUEST['p_id']));
			if ($ct > 0) {
				$this->addError('Blogs are still attached to this folder');
				return $this->ajaxReturn(array('status'=>'false'));
			}
			$ct = $this->fetchScalar(sprintf('select count(0) from %s t1, %s t2 where t2.id = %d and t1.left_id > t2.left_id and t1.right_id < t2.right_id and t1.level > t2.level',$this->m_tree, $this->m_tree, $_REQUEST['p_id']));
			if ($ct > 0) {
				$this->addError('Other categories are still attached to this folder');
				return $this->ajaxReturn(array('status'=>'false'));
			}
			if (!$this->deleteCheck('blog',$_REQUEST['p_id'],$inUse)) {
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
    function deleteComment() {
		$c_id = array_key_exists('c_id',$_REQUEST) ? $_REQUEST['c_id'] : 0;
		$this->execute(sprintf('delete from blog_comments where id = %d', $c_id));
		$form = new Forms();
		$form->init($this->getTemplate('deleteComment'));
		return $this->ajaxReturn(array('status'=>true,'html'=>$form->show()));
	}

    /**
     * @return array|mixed|string|string[]|void
     * @throws phpmailerException
     */
    function approval() {
		$form = new Forms();
		$form->init($this->getTemplate('approval'));
		$flds = $form->buildForm($this->getFields('approval'));
		$form->addData($_REQUEST);
		return $this->show($form->show());
	}

    /**
     * @param int $b_id
     * @return array|mixed|string|string[]
     * @throws phpmailerException
     */
    function getVideo($b_id = 0) {
		$outer = new Forms();
		$outer->init($this->getTemplate('video'));
		if (!$data = $this->fetchSingle(sprintf('select * from videos where owner_id = %d and owner_type = "blog"', $b_id)))
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
		if (strlen($_POST['video']['url']) == 0 && strlen($_POST['video']['embed_code']) == 0) {
			if ($_POST['video']['v_id'] > 0)
				$this->execute(sprintf('delete from video where owner_type="blog" and owner_id = %d'));
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
		if ($data = $this->fetchSingle(sprintf('select * from videos where owner_type="blog" and owner_id = %d', $form->getData('v_id')))) {
			$stmt = $this->prepare(sprintf('update videos set %s=? where id = %d', implode('=?, ',array_keys($flds)),$data['id']));
		}
		else {
			$flds['owner_type'] = 'blog';
			$flds['owner_id'] = $id;
			$stmt = $this->prepare(sprintf('insert into videos(%s) values(%s?)', implode(', ', array_keys($flds)), str_repeat('?, ',count($flds)-1)));
		}
		$stmt->bindParams(array_merge(array(str_repeat('s',count($flds))),array_values($flds)));
		$valid = $stmt->execute();
		return $valid;
	}

    /**
     * @return false|string
     * @throws phpmailerException
     */
    function getNames() {
		$query = $_REQUEST['s'];
		$member = $this->fetchScalar(sprintf("select author_id from blog_comments where id = %d",$_REQUEST['m']));
		$select = new select();
		$select->addAttributes(array("sql"=>sprintf("select id, concat(lastname,', ',firstname,if(id=%d,'*',''),' ',email) from members where (firstname like '%%%s%%' or lastname = '%%%s%%' or id = %d) and deleted = 0 and enabled = 1 order by lastname, firstname", $member, $query, $query, $member ),"name"=>"author_id"));
		return $this->ajaxReturn(array('status'=>true,'html'=>$select->show()));
	}

    /**
     * @return false|string
     * @throws phpmailerException
     */
    function getAuthor() {
		$query = $_REQUEST['s'];
		$member = $this->fetchScalar(sprintf("select author from blog where id = %d",$_REQUEST['m']));
		$select = new select();
		$select->addAttributes(array("sql"=>sprintf("select id, concat(lname,', ',fname,if(id=%d,'*',''),' ',email) from users where (fname like '%%%s%%' or lname = '%%%s%%' or id = %d) and deleted = 0 and enabled = 1 order by lname, fname", $member, $query, $query, $member ),"name"=>"author_id"));
		return $this->ajaxReturn(array('status'=>true,'html'=>$select->show()));
	}

}

?>