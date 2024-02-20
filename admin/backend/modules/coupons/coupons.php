<?php

class coupons extends Backend {

	private $m_tree = 'coupon_folders';
	private $m_content = 'coupons';
	private $m_junction = 'coupons_by_folder';
	private $m_pagination = 5;

	/**
	 * @throws phpmailerException
	 */
	public function __construct() {
		$this->M_DIR = 'backend/modules/coupons/';
		$this->setTemplates(
			array(
				'deleteItem'=>$this->M_DIR.'forms/deleteItem.html',
				'deleteItemResult'=>$this->M_DIR.'forms/deleteItemResult.html',
				'main'=>$this->M_DIR.'coupons.html',
				'form'=>$this->M_DIR.'forms/form.html',
				'showContentTree'=>$this->M_DIR.'forms/contenttree.html',
				'couponInfo'=>$this->M_DIR.'forms/couponInfo.html',
				'folderProperties'=>$this->M_DIR.'forms/folder.html',
				'showFolderContent'=>$this->M_DIR.'forms/folderContent.html',
				'folderInfo'=>$this->M_DIR.'forms/folderInfo.html',
				'couponList'=>$this->M_DIR.'forms/couponList.html',
				'showSearchForm'=>$this->M_DIR.'forms/searchForm.html',
				'addContent'=>$this->M_DIR.'forms/addContent.html',
				'storeChainList'=>$this->M_DIR.'forms/storeChainList.html',
				'addFolder'=>$this->M_DIR.'forms/addFolder.html',
				'addCoupon'=>$this->M_DIR.'forms/addCoupon.html',
				'eventList'=>$this->M_DIR.'forms/eventList.html',
				'productList'=>$this->M_DIR.'forms/productList.html',
				'header'=>$this->M_DIR.'forms/heading.html',
				'editItem'=>$this->M_DIR.'forms/editCoupon.html',
				'editResult'=>$this->M_DIR.'forms/editResult.html',
				'eventList'=>$this->M_DIR.'forms/eventList.html',
				'storeList'=>$this->M_DIR.'forms/storeList.html',
				'productList'=>$this->M_DIR.'forms/productList.html',
				'couponList'=>$this->M_DIR.'forms/couponList.html',
				'newsList'=>$this->M_DIR.'forms/newsList.html',
				'blogList'=>$this->M_DIR.'forms/blogList.html',
				'advertList'=>$this->M_DIR.'forms/advertList.html',
				'deleteResult'=>$this->M_DIR.'forms/deleteResult.html'
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
			'advertByCouponList'=>array(
				'destAds'=>array('type'=>'select','multiple'=>'multiple','id'=>'destAds')
			),
			'blogByCouponList'=>array(
				'destBlogs'=>array('type'=>'select','multiple'=>'multiple','id'=>'destBlogs')
			),
			'newsByCouponList'=>array(
				'destNews'=>array('type'=>'select','multiple'=>'multiple','id'=>'destNews')
			),
			'productByCouponList'=>array(
				'relatedDestProducts'=>array('type'=>'select','multiple'=>'multiple','id'=>'relatedDestProducts')
			),
			'storeByCouponList'=>array(
				'destStores'=>array('type'=>'select','multiple'=>'multiple','id'=>'destStores')
			),
			'eventByCouponList'=>array(
				'destEvents'=>array('type'=>'select','multiple'=>'multiple','id'=>'destEvents')
			),
			'couponByCouponList'=>array(
				'destCoupons'=>array('type'=>'select','multiple'=>'multiple','id'=>'destCoupons')
			),
			'header'=>array(),
			'eventList'=>array(
				'name'=>array('type'=>'tag'),
				'start_date'=>array('type'=>'datestamp','mask'=>'d-M-Y')
			),
			'addContent'=>array(
				'options'=>array('method'=>'post','action'=>'/modit/ajax/addContent/coupons'),
				'created'=>array('type'=>'datestamp','database'=>false,'reformatting'=>true,'mask'=>'d-M-Y H:i:s'),
				'name'=>array('type'=>'input','required'=>true,'prettyName'=>'Name'),
				'subtitle'=>array('type'=>'input','required'=>false),
				'start_date'=>array('type'=>'datetimepicker','id'=>'addStartDate','AMPM'=>'AMPM'),
				'end_date'=>array('type'=>'datetimepicker','id'=>'addEndDate','AMPM'=>'AMPM'),
				'id'=>array('type'=>'tag','database'=>false),
				'code'=>array('type'=>'input','required'=>true,'prettyName'=>'Code'),
				'teaser'=>array('type'=>'textarea','required'=>false,'class'=>'mceSimple'),
				'description'=>array('type'=>'textarea','required'=>true,'id'=>'couponBody','class'=>'mceAdvanced','prettyName'=>'Description'),
				'enabled'=>array('type'=>'checkbox','required'=>false,'value'=>1,'checked'=>'checked'),
				'featured'=>array('type'=>'checkbox','required'=>false,'value'=>1),
				'published'=>array('type'=>'checkbox','value'=>1,'checked'=>'checked'),
				'deleted'=>array('type'=>'checkbox','value'=>1,'checked'=>'checked'),
				'image1'=>array('type'=>'tag','required'=>false,'prettyName'=>'Image 1'),
				'image2'=>array('type'=>'tag','prettyName'=>'Image 2'),
				'imagesel_a'=>array('type'=>'image','unknown'=>true,'database'=>false,'id'=>'imagesel_a'),
				'imagesel_b'=>array('type'=>'image','unknown'=>true,'database'=>false,'id'=>'imagesel_b'),
				'submit'=>array('type'=>'submitbutton','database'=>false,'value'=>'Save'),

				'productFolders'=>array('type'=>'select','database'=>false,'id'=>'productFolderSelector','reformatting'=>false,'options'=>$this->nodeSelect(0,'product_folders',2,false,false)),
				'couponFolders'=>array('type'=>'select','database'=>false,'id'=>'couponFolderSelector','options'=>$this->nodeSelect(0, 'coupon_folders', 2, false, false),'reformatting'=>false),
				'blogFolders'=>array('type'=>'select','database'=>false,'id'=>'blogFolderSelector','options'=>$this->nodeSelect(0, 'blog_folders', 2, false, false),'reformatting'=>false),
				'storeFolders'=>array('type'=>'select','database'=>false,'id'=>'storeFolderSelector','options'=>$this->nodeSelect(0, 'store_folders', 2, false, false),'reformatting'=>false),
				'newsFolders'=>array('type'=>'select','database'=>false,'id'=>'newsFolderSelector','options'=>$this->nodeSelect(0, 'news_folders', 2, false, false),'reformatting'=>false),
				'eventFolders'=>array('type'=>'select','database'=>false,'id'=>'eventFolderSelector','options'=>$this->nodeSelect(0, 'members_folders', 2, false, false),'reformatting'=>false),
				'advertFolders'=>array('type'=>'select','database'=>false,'id'=>'advertFolderSelector','options'=>$this->nodeSelect(0, 'advert_folders', 2, false, false),'reformatting'=>false),

				'destFolders'=>array('name'=>'destFolders','type'=>'select','multiple'=>'multiple','required'=>true,'id'=>'destFolders','database'=>false,'options'=>$this->nodeSelect(0, 'coupon_folders', 2, false, false),'reformatting'=>false,'prettyName'=>'Member Of'),
				'destStoreFolders'=>array('name'=>'destStoreFolders','type'=>'select','multiple'=>'multiple','required'=>false,'id'=>'destStoreFolders','database'=>false,'reformatting'=>false),
				'destStores'=>array('name'=>'destStores','type'=>'select','multiple'=>'multiple','required'=>false,'id'=>'destStores','database'=>false),
				'destProductFolders'=>array('name'=>'destProductFolders','type'=>'select','multiple'=>'multiple','required'=>false,'id'=>'destProductFolders','database'=>false,'reformatting'=>false),
				'relatedDestProducts'=>array('name'=>'relatedDestProducts','type'=>'select','multiple'=>'multiple','required'=>false,'id'=>'relatedDestProducts','database'=>false),
				'destEventFolders'=>array('name'=>'destEventFolders','type'=>'select','multiple'=>'multiple','required'=>false,'id'=>'destEventFolders','database'=>false,'reformatting'=>false),
				'destEvents'=>array('name'=>'destEvents','type'=>'select','multiple'=>'multiple','required'=>false,'id'=>'destEvents','database'=>false),
				'destNewsFolders'=>array('name'=>'destNewsFolders','type'=>'select','multiple'=>'multiple','required'=>false,'id'=>'destNewsFolders','database'=>false,'reformatting'=>false),
				'destNews'=>array('name'=>'destNews','type'=>'select','multiple'=>'multiple','required'=>false,'id'=>'destNews','database'=>false),
				'destBlogFolders'=>array('name'=>'destBlogFolders','type'=>'select','multiple'=>'multiple','required'=>false,'id'=>'destBlogFolders','database'=>false,'reformatting'=>false),
				'destBlogs'=>array('name'=>'destBlogs','type'=>'select','multiple'=>'multiple','required'=>false,'id'=>'destBlogs','database'=>false),
				'destAdFolders'=>array('name'=>'destAdFolders','type'=>'select','multiple'=>'multiple','required'=>false,'id'=>'destAdFolders','database'=>false,'reformatting'=>false),
				'destAds'=>array('name'=>'destAds','type'=>'select','multiple'=>'multiple','required'=>false,'id'=>'destAds','database'=>false),
				'percent_or_dollar'=>array('type'=>'select','required'=>true,'lookup'=>'discountTypes','prettyName'=>'Discount Type'),
				'amount'=>array('type'=>'input','required'=>true,'validation'=>'number','prettyName'=>'Amount'),
				'shipping_only'=>array('type'=>'checkbox','value'=>1),
				'min_amount'=>array('type'=>'input','required'=>false,'validation'=>'number','prettyName'=>'Minimum Amount'),
				'addContent'=>array('type'=>'hidden','value'=>1,'database'=>false)
			),
			'showSearchForm'=>array(
				'options'=>array('action'=>'showSearchForm','name'=>'searchForm','id'=>'search_form'),
				'opt_created'=>array('type'=>'select','name'=>'opt_created','lookup'=>'search_options','required'=>false),
				'opt_start_date'=>array('type'=>'select','name'=>'opt_start_date','lookup'=>'search_options','required'=>false),
				'opt_end_date'=>array('type'=>'select','name'=>'opt_end_date','lookup'=>'search_options','required'=>false),
				'opt_code'=>array('type'=>'select','name'=>'opt_code','lookup'=>'search_options','required'=>false),
				'code'=>array('type'=>'input','required'=>false),
				'created'=>array('type'=>'datepicker','required'=>false),
				'start_date'=>array('type'=>'datepicker','required'=>false,'id'=>'searchStartDate'),
				'end_date'=>array('type'=>'datepicker','required'=>false,'id'=>'searchEndDate'),
				'enabled'=>array('type'=>'select','required'=>false,'lookup'=>'boolean'),
				'published'=>array('type'=>'select','required'=>false,'lookup'=>'boolean'),
				'deleted'=>array('type'=>'select','required'=>false,'lookup'=>'boolean'),
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
				'image'=>array('type'=>'image','unknown'=>true),
				'rollover_image'=>array('type'=>'image','unknown'=>true),
				'sortby'=>array('type'=>'hidden','value'=>'created'),
				'sortorder'=>array('type'=>'hidden','value'=>'desc'),
				'pagenum'=>array('type'=>'hidden','value'=>1),			
				'pager'=>array('type'=>'select','required'=>true,'value'=>$this->m_pagination,'lookup'=>'paging','id'=>'pager'),
				'showFolderContent'=>array('type'=>'hidden','value'=>1)
			),
			'main' => array(
				'test'=>array('type'=>'tag')
			),
			'form' => array(),
			'folderProperties' => array(
				'options'=>array(
					'action'=>'/modit/coupons/showPageProperties',
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
				'newsFolders'=>array('type'=>'select','database'=>false,'id'=>'newsFolderSelector','options'=>$this->nodeSelect(0, 'news_folders', 2, false, false),'reformatting'=>false),
				'eventFolders'=>array('type'=>'select','database'=>false,'id'=>'eventFolderSelector','options'=>$this->nodeSelect(0, 'members_folders', 2, false, false),'reformatting'=>false),
				'advertFolders'=>array('type'=>'select','database'=>false,'id'=>'advertFolderSelector','options'=>$this->nodeSelect(0, 'advert_folders', 2, false, false),'reformatting'=>false),

				'destStoreFolders'=>array('name'=>'destStoreFolders','type'=>'select','multiple'=>'multiple','required'=>false,'id'=>'destStoreFolders','database'=>false,'reformatting'=>false),
				'destStores'=>array('name'=>'destStores','type'=>'select','multiple'=>'multiple','required'=>false,'id'=>'destStores','database'=>false),
				'destProductFolders'=>array('name'=>'destProductFolders','type'=>'select','multiple'=>'multiple','required'=>false,'id'=>'destProductFolders','database'=>false,'reformatting'=>false),
				'relatedDestProducts'=>array('name'=>'relatedDestProducts','type'=>'select','multiple'=>'multiple','required'=>false,'id'=>'relatedDestProducts','database'=>false),
				'destEventFolders'=>array('name'=>'destEventFolders','type'=>'select','multiple'=>'multiple','required'=>false,'id'=>'destEventFolders','database'=>false,'reformatting'=>false),
				'destEvents'=>array('name'=>'destEvents','type'=>'select','multiple'=>'multiple','required'=>false,'id'=>'destEvents','database'=>false),
				'destNewsFolders'=>array('name'=>'destNewsFolders','type'=>'select','multiple'=>'multiple','required'=>false,'id'=>'destNewsFolders','database'=>false,'reformatting'=>false),
				'destNews'=>array('name'=>'destNews','type'=>'select','multiple'=>'multiple','required'=>false,'id'=>'destNews','database'=>false),
				'destBlogFolders'=>array('name'=>'destBlogFolders','type'=>'select','multiple'=>'multiple','required'=>false,'id'=>'destBlogFolders','database'=>false,'reformatting'=>false),
				'destBlogs'=>array('name'=>'destBlogs','type'=>'select','multiple'=>'multiple','required'=>false,'id'=>'destBlogs','database'=>false),
				'destAdFolders'=>array('name'=>'destAdFolders','type'=>'select','multiple'=>'multiple','required'=>false,'id'=>'destAdFolders','database'=>false,'reformatting'=>false),
				'destAds'=>array('name'=>'destAds','type'=>'select','multiple'=>'multiple','required'=>false,'id'=>'destAds','database'=>false),
				'submit'=>array('type'=>'submitbutton','value'=>'Save','database'=>false)
			),
			'showContentTree' => array(),
			'couponInfo' => array(),
			'showcouponContent' => array(),
			'folderInfo' => array(
				'title'=>array('type'=>'tag'),
				'alternate_title'=>array('type'=>'tag'),
				'notes'=>array('type'=>'tag','reformatting'=>false),
				'description'=>array('type'=>'tag','reformatting'=>false),
				'image' => array('type'=>'image','unknown'=>true),
				'rollover_image'=>array('type'=>'image','unknown'=>true)
			),
			'couponList' => array(
				'id'=>array('type'=>'tag'),
				'title'=>array('type'=>'tag'),
				'code'=>array('type'=>'tag'),
				'name'=>array('type'=>'tag'),
				'created'=>array('type'=>'datestamp','mask'=>'d-M-Y h:m:i'),
				'published'=>array('type'=>'booleanIcon'),
				'deleted'=>array('type'=>'booleanIcon'),
				'enabled'=>array('type'=>'booleanIcon'),
				'featured'=>array('type'=>'booleanIcon'),
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
		if (!(array_key_exists('id',$_REQUEST) && $data = $this->fetchSingle(sprintf('select * from %s where id = %d',$this->m_tree,$_REQUEST['id'])))) {
			$data = array('enabled'=>1,'id'=>0,'p_id'=>0,'image'=>'','rollover_image'=>'');
		}
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

		$frmFlds['destStoreFolders']['options'] = $this->nodeSelect(0,'store_folders',2,false);
		$data['destStoreFolders'] = $this->loadRelations('destStoreFolders',$this,"altStoreFormat",$data['id'],'couponfolder','storefolder','store_folders','',false);
		$data['destStores']= $this->loadRelations('destStores',$this,"altStoreFormat",$data['id'],'couponfolder','store','stores','and deleted = 0 and enabled = 1 and published = 1',false);
		$frmFlds['destStores']['options'] = $this->fetchOptions(sprintf('select id as code, name as value from stores where deleted = 0 and enabled = 1 and published = 1 and id in (%s) order by code',implode(',',array_merge(array(0),$data['destStores']))));

		$frmFlds['destProductFolders']['options'] = $this->nodeSelect(0,'product_folders',2,false);
		$data['destProductFolders'] = $this->loadRelations('destProductFolders',$this,"altProductFormat",$data['id'],'couponfolder','productfolder','product_folders','',false);
		$data['relatedDestProducts']= $this->loadRelations('relatedDestProducts',$this,"altProductFormat",$data['id'],'couponfolder','product','product','and deleted = 0 and enabled = 1 and published = 1',false);
		$frmFlds['relatedDestProducts']['options'] = $this->fetchOptions(sprintf('select id as code, concat(code," - ",name) as value from product where deleted = 0 and enabled = 1 and published = 1 and id in (%s) order by code',implode(',',array_merge(array(0),$data['relatedDestProducts']))));

		$frmFlds['destEventFolders']['options'] = $this->nodeSelect(0,'members_folders',2,false);
		$data['destEventFolders'] = $this->loadRelations('destEventFolders',$this,"altEventFormat",$data['id'],'couponfolder','eventfolder','members_folders','',false);
		$data['destEvents']= $this->loadRelations('destEvents',$this,"altEventFormat",$data['id'],'couponfolder','event','events','and deleted = 0 and enabled = 1 and published = 1',false);
		$frmFlds['destEvents']['options'] = $this->fetchOptions(sprintf('select id as code, name as value from events where deleted = 0 and enabled = 1 and published = 1 and id in (%s) order by start_date',implode(',',array_merge(array(0),$data['destEvents']))));

		$frmFlds['destNewsFolders']['options'] = $this->nodeSelect(0,'news_folders',2,false);
		$data['destNewsFolders'] = $this->loadRelations('destNewsFolders',$this,"altNewsFormat",$data['id'],'couponfolder','newsfolder','news_folders','',false);
		$data['destNews']= $this->loadRelations('destNews',$this,"altNewsFormat",$data['id'],'couponfolder','news','news','and deleted = 0 and enabled = 1 and published = 1',false);
		$frmFlds['destNews']['options'] = $this->fetchOptions(sprintf('select id as code, title as value from news where deleted = 0 and enabled = 1 and published = 1 and id in (%s) order by title',implode(',',array_merge(array(0),$data['destNews']))));

		$frmFlds['destBlogFolders']['options'] = $this->nodeSelect(0,'blog_folders',2,false);
		$data['destBlogFolders'] = $this->loadRelations('destBlogFolders',$this,"altBlogFormat",$data['id'],'couponfolder','blogfolder','blog_folders','',false);
		$data['destBlogs']= $this->loadRelations('destBlogs',$this,"altBlogFormat",$data['id'],'couponfolder','blog','blog','and deleted = 0 and enabled = 1 and published = 1',false);
		$frmFlds['destBlogs']['options'] = $this->fetchOptions(sprintf('select id as code, title as value from blog where deleted = 0 and enabled = 1 and published = 1 and id in (%s) order by title',implode(',',array_merge(array(0),$data['destBlogs']))));

		$frmFlds['destAdFolders']['options'] = $this->nodeSelect(0,'advert_folders',2,false);
		$data['destAdFolders'] = $this->loadRelations('destAdFolders',$this,"altAdFormat",$data['id'],'couponfolder','adfolder','advert_folders','',false);
		$data['destAds']= $this->loadRelations('destAds',$this,"altAdFormat",$data['id'],'couponfolder','ad','advert','and deleted = 0 and enabled = 1 and published = 1',false);
		$frmFlds['destAds']['options'] = $this->fetchOptions(sprintf('select id as code, title as value from advert where deleted = 0 and enabled = 1 and published = 1 and id in (%s) order by title',implode(',',array_merge(array(0),$data['destAds']))));

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
				$stmt = $this->prepare(sprintf('update %s set %s where id = %d',$this->m_tree, implode(',',$flds),$data['id']));
				$stmt->bindParams(array_merge(array(str_repeat('s', count($flds))),$values));
				$this->beginTransaction();
				if ($status = $stmt->execute()) {
					$status = $status && $this->updateRelations('destStoreFolders',$data['id'],'couponfolder','storefolder','store_folders',false,false);
					$status = $status && $this->updateRelations('destStores',$data['id'],'couponfolder','store','stores',false,true);
					$status = $status && $this->updateRelations('destProductFolders',$data['id'],'couponfolder','productfolder','product_folders',false,false);
					$status = $status && $this->updateRelations('relatedDestProducts',$data['id'],'couponfolder','product','product',false,true);
					$status = $status && $this->updateRelations('destNewsFolders',$data['id'],'couponfolder','newsfolder','news_folders',false,false);
					$status = $status && $this->updateRelations('destNews',$data['id'],'couponfolder','news','news',false,true);
					$status = $status && $this->updateRelations('destEventFolders',$data['id'],'couponfolder','eventfolder','members_folders',false,false);
					$status = $status && $this->updateRelations('destEvents',$data['id'],'couponfolder','event','events',false,true);
					$status = $status && $this->updateRelations('destBlogFolders',$data['id'],'couponfolder','blogfolder','blog_folders',false,false);
					$status = $status && $this->updateRelations('destBlogs',$data['id'],'couponfolder','blog','blog',false,true);
					$status = $status && $this->updateRelations('destAdvertFolders',$data['id'],'couponfolder','advertfolder','advert_folders',false,false);
					$status = $status && $this->updateRelations('destAds',$data['id'],'couponfolder','ad','advert',false,true);
					if ($status) {
						$this->commitTransaction();
						if ($this->isAjax()) {
							$this->logMessage('showPageProperties', 'executing ajax success return', 3);
							$this->addMessage('Record successfully added');
							return $this->ajaxReturn(array('status'=>'true','html'=>'','url'=>'/modit/coupons?p_id='.$data['id']));
						}
					}
					else {
						$this->rollbackTransaction();
						$this->addError('Error occurred');
						$form->addTag('errorMessage',$this->showErrors(),false);
						if ($this->isAjax()) {
							$this->logMessage('showPageProperties', 'executing ajax error return', 3);
							return $this->ajaxReturn(array('status'=>'false','html'=>$form->show()));
						}
					}
				} else {
					$this->addError('Error occurred');
					$form->addTag('errorMessage',$this->showErrors(),false);
					if ($this->isAjax()) {
						$this->logMessage('showPageProperties', 'executing ajax error return', 3);
						return $this->ajaxReturn(array('status'=>'false','html'=>$form->show()));
					}
				}
				$form->addTag('errorMessage','Record added succesfully');
			}
			else {
				$return = 'false';
				$form->addTag('errorMessage','Form validation failed');
			}
		}
	
		if ($this->isAjax())
			return $this->ajaxReturn(array('status'=>$return,'html'=>$form->show()));
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
					$return = array('value'=>sprintf('<div class="wrapper"><a href="#" class="toggler" onclick="toggle(this);return false;">+</a>&nbsp;<a href="#" id="li_%d" class="%s icon_folder info">%s</a></div>',$data['id'], $expanded, htmlspecialchars($data['title'])),'submenu'=>$submenu);
				}
				else {
					$return = array('value'=>sprintf('<div class="wrapper"><a href="#" id="%s_li_%d" class="%s icon_folder info">%s</a></div>',$table,$data['id'], $expanded, htmlspecialchars($data['title'])),'submenu'=>array());
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
			if ($data = $this->fetchSingle(sprintf('select * from %s where id = %d', $this->m_tree, $_REQUEST['p_id']))) {
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
		if ($p_id > 0 && $data = $this->fetchSingle(sprintf('select * from %s where id = %d', $this->m_tree, $p_id))) {
			if (strlen($data['alternate_title']) > 0) $data['connector'] = '&nbsp;-&nbsp;';
			$form->init($this->getTemplate('showFolderContent'),array('name'=>'showFolderContent'));
			$frmFields = $form->buildForm($this->getFields('showFolderContent'));
			if (array_key_exists('pagenum',$_REQUEST)) 
				$pageNum = $_REQUEST['pagenum'];
			else
				$pageNum = 1;	// no 0 based calcs
			if ($pageNum <= 0) $pageNum = 1;
			$perPage = $this->m_pagination;
			if (array_key_exists('pager',$_REQUEST)) $perPage = $_REQUEST['pager'];
			$count = $this->fetchScalar(sprintf('select count(n.id) from %s n where n.deleted = 0 and n.id in (select f.coupon_id from %s f where f.folder_id = %d)', $this->m_content, $this->m_junction, $_REQUEST['p_id']));
			$pagination = $this->pagination($count, $perPage, $pageNum, 
				array('prev'=>$this->M_DIR.'forms/paginationPrev.html','next'=>$this->M_DIR.'forms/paginationNext.html',
						'pages'=>$this->M_DIR.'forms/paginationPage.html', 'wrapper'=>$this->M_DIR.'forms/paginationWrapper.html'));
			$start = ($pageNum-1)*$perPage;
			$sortby = 'id';
			$sortorder = 'desc';
			if (count($_POST) > 0 && array_key_exists('showFolderContent',$_POST)) {
				$sortby = $_POST['sortby'];
				$sortorder = $_POST['sortorder'];
				$form->addData($_POST);
			}
			$sql = sprintf('select i.*,j.id as j_id from %s i, %s j where i.deleted = 0 and j.folder_id = %d and i.id = j.coupon_id order by j.sequence limit %d,%d', $this->m_content, $this->m_junction, $_REQUEST['p_id'], $start, $perPage);
			$coupons = $this->fetchAll($sql);
			$this->logMessage('showPageContent', sprintf('sql [%s], records [%d]',$sql, count($coupons)), 2);
			$output = array();
			foreach($coupons as $coupon) {
				$frm = new Forms();
				$frm->init($this->getTemplate('couponList'),array());
				$tmp = $frm->buildForm($this->getFields('couponList'));
				$frm->addData($coupon);
				$output[] = $frm->show();
			}
			$form->addTag('coupons',implode('',$output),false);
			$form->addTag('pagination',$pagination,false);
			$form->addData($data);
		}
		$form->addTag('heading',$this->getHeader(),false);
		if ($this->isAjax()) {
			$tmp = $form->show();
			return $this->ajaxReturn(array(
				'status'=>'true','html'=>$tmp
			));
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
			if (array_key_exists('formData',$_SESSION) && array_key_exists('couponSearchForm', $_SESSION['formData']))
				$_POST = $_SESSION['formData']['couponSearchForm'];
			else
				$_POST = array('showSearchForm'=>1,'deleted'=>0,'sortby'=>'published','sortorder'=>'asc');
		$this->logMessage("showSearchForm",sprintf("post is [%s]",print_r($_POST,true)),2);
		if (count($_POST) > 0 && array_key_exists('showSearchForm',$_POST)) {
			if ((!array_key_exists('deleted',$_POST)) || strlen($_POST['deleted']) == 0) $_POST['deleted'] = 0;
			$form->addData($_POST);
			if ($form->validate()) {
				if (strlen($form->getData("quicksearch")) > 0) {
					$_SESSION['formData']['couponSearchForm'] = array('showSearchForm'=>1,'opt_quicksearch'=>'like','quicksearch'=>$form->getData("quicksearch"),'pager'=>$form->getData("pager"),'deleted'=>0);
				}
				else
					$_SESSION['formData']['couponSearchForm'] = $form->getAllData();
				$srch = array();
				foreach($frmFields as $key=>$value) {
					switch($key) {
						case 'quicksearch':
							if (array_key_exists('opt_quicksearch',$_POST) && strlen($_POST['opt_quicksearch']) > 0 && $value = $form->getData($key)) {
								if ($_POST['opt_quicksearch'] == 'like' && strpos($value,'%',0) === false) {
									$value = '%'.$value.'%';
								}
								$tmp = array();
								$tmp[] = sprintf(' code %s "%s"',$_POST['opt_quicksearch'],$this->escape_string($value));
								$tmp[] = sprintf(' name %s "%s"',$_POST['opt_quicksearch'],$this->escape_string($value));
								$tmp[] = sprintf(' description %s "%s"',$_POST['opt_quicksearch'],$this->escape_string($value));
								$tmp[] = sprintf(' teaser %s "%s"',$_POST['opt_quicksearch'],$this->escape_string($value));
								$srch = array(sprintf('(%s)',implode(' or ',$tmp)),'deleted = 0');
								continue;
							}
							break;
						case 'code':
							if (array_key_exists('opt_code',$_POST) && strlen($_POST['opt_code']) > 0 && $value = $form->getData($key)) {
								if ($_POST['opt_code'] == 'like' && strpos($value,'%',0) === false) {
									$value = '%'.$value.'%';
								}
								$srch[] = sprintf(' code %s "%s"',$_POST['opt_code'],$this->escape_string($value));
							}
							break;
						case 'created':
						case 'start_date':
						case 'end_date':
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
								$srch[] = sprintf(' id in (select coupon_id from %s where folder_id = %d) ',$this->m_junction, $value);
							}
							break;
						case 'deleted':
						case 'enabled':
						case 'published':
							if (!is_null($value = $form->getData($key)))
								if (strlen($value) > 0)
									$srch[] = sprintf(' %s = %s',$key,$this->escape_string($value));
							break;
						default:
							break;
					}
				}
				//$this->logMessage("showSearchForm",sprintf("srch [%s] post [%s] session [%s] fields [%s]",print_r($srch,true),print_r($_POST,true),print_r($_SESSION,true),print_r($frmFields,true)),2);
				if (count($srch) > 0) {
					if (array_key_exists('pagenum',$_REQUEST))
						$pageNum = $_REQUEST['pagenum'];
					else
						$pageNum = 1;	// no 0 based calcs
					$perPage = $this->m_pagination;
					if (array_key_exists('pager',$_REQUEST)) $perPage = $_REQUEST['pager'];
					$count = $this->fetchScalar(sprintf('select count(n.id) from %s n where 1 = 1 and %s', $this->m_content, implode(' and ',$srch)));
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
					$sql = sprintf('select c.*, 0 as j_id from %s c where 1 = 1 and %s order by %s %s limit %d,%d', $this->m_content, implode(' and ',$srch),$sortby, $sortorder, $start,$perPage);
					$recs = $this->fetchAll($sql);
					$this->logMessage('showSearchForm', sprintf('sql [%s] records [%d]',$sql,count($recs)), 2);
					$articles = array();
					foreach($recs as $article) {
						$frm = new Forms();
						$frm->init($this->getTemplate('couponList'),array());
						$tmp = $frm->buildForm($this->getFields('couponList'));
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
			$data = array('id'=>0,'published'=>false,'image1'=>'','image2'=>''); 
		}

		//$frmFields['destFolders'] = array('type'=>'ol','class'=>'coupondraggable draggable level_1 dest','id'=>'toList','options'=>array(),'database'=>false);
		if (count($_REQUEST) > 0 && array_key_exists('destFolders',$_REQUEST) || $data['id'] > 0) {
			$ids = array();
			if (array_key_exists('destFolders',$_REQUEST)) {
				$ids = $_REQUEST['destFolders'];
				if (!is_array($ids)) $ids = array($ids);
			}
			if ($data['id'] > 0) {
				$tmp = $this->fetchScalarAll(sprintf('select folder_id from %s where coupon_id = %d',$this->m_junction,$data['id']));
				$ids = array_merge($ids,$tmp);
			}
			if (count($ids) > 0) {
				$data['destFolders'] = $ids;
				//$ids = implode(',',$ids);
				//$source = $this->fetchAll(sprintf('select * from %s where id in (%s) order by left_id', $this->m_tree, $ids));
				//foreach($source as $key=>$fldr) {
				//	$frmFields['destFolders']['options'][$fldr['id']] = array('value'=>sprintf('%s%s',str_repeat('&nbsp;',$fldr['level']*2),htmlspecialchars($fldr['title'])), 'id'=>sprintf('couponfolder_%03d',$fldr['id']),'reformatting'=>false, 'class'=>sprintf('def_field_li sortorder_%03d',$fldr['left_id']));
				//}
			}
		}

		$frmFields['destStoreFolders']['options'] = $this->nodeSelect(0,'store_folders',2,false);
		$data['destStoreFolders'] = $this->loadRelations('destStoreFolders',$this,"altStoreFormat",$data['id'],'coupon','storefolder','store_folders','',false);
		$data['destStores']= $this->loadRelations('destStores',$this,"altStoreFormat",$data['id'],'coupon','store','stores','and deleted = 0 and enabled = 1 and published = 1',false);
		$frmFields['destStores']['options'] = $this->fetchOptions(sprintf('select id as code, name as value from stores where deleted = 0 and enabled = 1 and published = 1 and id in (%s) order by code',implode(',',array_merge(array(0),$data['destStores']))));

		$frmFields['destProductFolders']['options'] = $this->nodeSelect(0,'product_folders',2,false);
		$data['destProductFolders'] = $this->loadRelations('destProductFolders',$this,"altProductFormat",$data['id'],'coupon','productfolder','product_folders','',false);
		$data['relatedDestProducts']= $this->loadRelations('relatedDestProducts',$this,"altProductFormat",$data['id'],'coupon','product','product','and deleted = 0 and enabled = 1 and published = 1',false);
		$frmFields['relatedDestProducts']['options'] = $this->fetchOptions(sprintf('select id as code, concat(code," - ",name) as value from product where deleted = 0 and enabled = 1 and published = 1 and id in (%s) order by code',implode(',',array_merge(array(0),$data['relatedDestProducts']))));

		$frmFields['destEventFolders']['options'] = $this->nodeSelect(0,'members_folders',2,false);
		$data['destEventFolders'] = $this->loadRelations('destEventFolders',$this,"altEventFormat",$data['id'],'coupon','eventfolder','members_folders','',false);
		$data['destEvents']= $this->loadRelations('destEvents',$this,"altEventFormat",$data['id'],'coupon','event','events','and deleted = 0 and enabled = 1 and published = 1',false);
		$frmFields['destEvents']['options'] = $this->fetchOptions(sprintf('select id as code, name as value from events where deleted = 0 and enabled = 1 and published = 1 and id in (%s) order by start_date',implode(',',array_merge(array(0),$data['destEvents']))));

		$frmFields['destNewsFolders']['options'] = $this->nodeSelect(0,'news_folders',2,false);
		$data['destNewsFolders'] = $this->loadRelations('destNewsFolders',$this,"altNewsFormat",$data['id'],'coupon','newsfolder','news_folders','',false);
		$data['destNews']= $this->loadRelations('destNews',$this,"altNewsFormat",$data['id'],'coupon','news','news','and deleted = 0 and enabled = 1 and published = 1',false);
		$frmFields['destNews']['options'] = $this->fetchOptions(sprintf('select id as code, title as value from news where deleted = 0 and enabled = 1 and published = 1 and id in (%s) order by title',implode(',',array_merge(array(0),$data['destNews']))));

		$frmFields['destBlogFolders']['options'] = $this->nodeSelect(0,'blog_folders',2,false);
		$data['destBlogFolders'] = $this->loadRelations('destBlogFolders',$this,"altBlogFormat",$data['id'],'coupon','blogfolder','blog_folders','',false);
		$data['destBlogs']= $this->loadRelations('destBlogs',$this,"altBlogFormat",$data['id'],'coupon','blog','blog','and deleted = 0 and enabled = 1 and published = 1',false);
		$frmFields['destBlogs']['options'] = $this->fetchOptions(sprintf('select id as code, title as value from blog where deleted = 0 and enabled = 1 and published = 1 and id in (%s) order by title',implode(',',array_merge(array(0),$data['destBlogs']))));

		$frmFields['destAdFolders']['options'] = $this->nodeSelect(0,'advert_folders',2,false);
		$data['destAdFolders'] = $this->loadRelations('destAdFolders',$this,"altAdFormat",$data['id'],'coupon','adfolder','advert_folders','',false);
		$data['destAds']= $this->loadRelations('destAds',$this,"altAdFormat",$data['id'],'coupon','ad','advert','and deleted = 0 and enabled = 1 and published = 1',false);
		$frmFields['destAds']['options'] = $this->fetchOptions(sprintf('select id as code, title as value from advert where deleted = 0 and enabled = 1 and published = 1 and id in (%s) order by title',implode(',',array_merge(array(0),$data['destAds']))));

		$frmFields = $form->buildForm($frmFields);
		$data['imagesel_a'] = $data['image1'];
		$data['imagesel_b'] = $data['image2'];
		$form->addData($data);
		$status = 'false';	//assume it failed
		if (count($_POST) > 0 && array_key_exists('addContent',$_POST)) {
			$_POST['imagesel_a'] = $_POST['image1'];
			$_POST['imagesel_b'] = $_POST['image2'];
			$form->addData($_POST);
			$valid = $form->validate();
			$id = (int)$_POST['a_id'];
			if ($id == 0) {
				//
				//	make sure there is only 1 active code with this name
				//
				$ct = $this->fetchScalar(sprintf('select count(0) from coupons where code = "%s"',$this->escape_string($form->getData('code'))));
				if ($ct > 0) {
					$form->addTag('nameError','* Code already exists');
				}
			}
			if ($valid) {
				unset($frmFields['a_id']);
				unset($frmFields['options']);
				foreach($frmFields as $key=>$fld) {
					if (!(array_key_exists('database',$fld) && $fld['database'] == false)) {
						$values[] = $form->getData($fld['name']);//$_REQUEST[$fld['name']];
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
					if ($id == 0) $id = $this->insertId();

					$destFolders = $_POST['destFolders'];
					if (!is_array($destFolders)) $destFolders = array($destFolders);
					//
					//	delete folders we are no longer a member of
					//
					$this->execute(sprintf('delete from %s where coupon_id = %d and folder_id not in (%s)',$this->m_junction,$id,implode(',',$destFolders)));
					$sql = sprintf('select id from %s where id in (%s) and id not in (select folder_id from %s where coupon_id = %d and folder_id in (%s))', $this->m_tree, implode(',',$destFolders),$this->m_junction,$id,implode(',',$destFolders));
					$new = $this->fetchScalarAll($sql);
					$this->logMessage('addContent',sprintf('new coupon folders added [%s] [%s]',implode(',',$new),$sql),2);
					$status = true;
					foreach($new as $key=>$folder) {
						$obj = new preparedStatement(sprintf('insert into %s(coupon_id,folder_id) values(?,?)',$this->m_junction));
						$obj->bindParams(array('dd',$id,$folder));
						$status = $status && $obj->execute();
						$this->resequence($folder);
					}
					//$destFolders = array_key_exists('destStoreChains',$_POST)? $_POST['destStoreChains'] : array(0);
					$status = $status && $this->updateRelations('destStoreFolders',$id,'coupon','storefolder','store_folders',false,false);
					$status = $status && $this->updateRelations('destStores',$id,'coupon','store','stores',false,true);
					$status = $status && $this->updateRelations('destProductFolders',$id,'coupon','productfolder','product_folders',false,false);
					$status = $status && $this->updateRelations('relatedDestProducts',$id,'coupon','product','product',false,true);
					$status = $status && $this->updateRelations('destNewsFolders',$id,'coupon','newsfolder','news_folders',false,false);
					$status = $status && $this->updateRelations('destNews',$id,'coupon','news','news',false,true);
					$status = $status && $this->updateRelations('destEventFolders',$id,'coupon','eventfolder','members_folders',false,false);
					$status = $status && $this->updateRelations('destEvents',$id,'coupon','event','events',false,true);
					$status = $status && $this->updateRelations('destBlogFolders',$id,'coupon','blogfolder','blog_folders',false,false);
					$status = $status && $this->updateRelations('destBlogs',$id,'coupon','blog','blog',false,true);
					$status = $status && $this->updateRelations('destAdFolders',$id,'coupon','adfolder','advert_folders',false,false);
					$status = $status && $this->updateRelations('destAds',$id,'coupon','ad','advert',false,true);
					if ($status) {
						$this->commitTransaction();
						//
						//	if adding, default them back to the first folder they added to
						//
						if ($data['id'] == 0)
							return $this->ajaxReturn(array('status' => 'true','url' => sprintf('/modit/coupons?p_id=%d',$destFolders[0])));
						else {
							$form->init($this->getTemplate('editResult'));
							return $this->ajaxReturn(array('status' => 'true','html' => $form->show()));
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
    function moveCoupon() {
		$src = 0;
		$dest = 0;
		if (array_key_exists('src',$_REQUEST)) $src = $_REQUEST['src'];
		if (array_key_exists('dest',$_REQUEST)) $dest = $_REQUEST['dest'];
		if ($_REQUEST['type'] == 'tree') {
			if ($src == 0 || $dest == 0 || !array_key_exists('type',$_REQUEST)) {
				$this->addError('Either source or destination was missing');
				return $this->ajaxReturn(array('status' => 'false'));
			}
			$curr = $this->fetchScalar(sprintf('select coupon_id from %s where id = %d',$this->m_junction,$src));
			if ($curr > 0 && $folder = $this->fetchSingle(sprintf('select * from %s where id = %d',$this->m_tree,$dest))) {
				$status = true;
				if (array_key_exists('move',$_REQUEST)) {
					//
					//	move it - delete all other folders
					//
					$this->logMessage('moveCoupon', sprintf('moving coupon %d to folder %d',$src,$dest),2);
					$this->beginTransaction();
					if ($status = $this->execute(sprintf('delete from %s where id = %d', $this->m_junction, $src))) {
						if (!$this->fetchSingle(sprintf('select * from %s where coupon_id = %d and folder_id = %d',$this->m_junction,$curr,$dest))) {
							$obj = new preparedStatement(sprintf('insert into %s(coupon_id,folder_id) values(?,?)',$this->m_junction));
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
					$this->logMessage('moveCoupon', sprintf('cloning coupon %d to folder %d',$curr,$dest),2);
					if (!($this->fetchSingle(sprintf('select * from %s where coupon_id = %d and folder_id = %d',$this->m_junction,$curr,$dest)))) {
						$obj = new preparedStatement(sprintf('insert into %s(coupon_id,folder_id) values(?,?)',$this->m_junction));
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
				$this->addError('Either source or destination was missing');
				return $this->ajaxReturn(array('status' => 'false'));
			}
			$src = $this->fetchSingle(sprintf('select * from %s where id = %d',$this->m_junction,$src));
			$sql = sprintf('select * from %s where folder_id = %d order by sequence limit %d,1',$this->m_junction,$src['folder_id'],$dest);
			$dest = $this->fetchSingle($sql);
			$this->logMessage("moveArticle",sprintf("move src [%s] to dest [%s] sql [%s]",print_r($src,true),print_r($dest,true),$sql),2);
			if (count($src) == 0 || count($dest) == 0) {
				$status = false;
				$this->addMessage('Either the source or destination coupon was not found');
			}
			else {
				//
				//	swap the order of the coupons
				//
				$this->logMessage('moveCoupon', sprintf('swap the sort order of %d and %d',$src['id'],$dest['id']),2);
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

    /**
     * @param $folder
     * @return void
     * @throws phpmailerException
     */
    function resequence($folder) {
		$this->logMessage('resequence', 'resequencing folder $folder', 2);
		$coupons = $this->fetchAll(sprintf('select * from %s where folder_id = %d order by sequence',$this->m_junction,$folder));
		$seq = 10;
		foreach($coupons as $coupon) {
			$this->execute(sprintf('update %s set sequence = %d where id = %d',$this->m_junction,$seq,$coupon['id']));
			$seq += 10;
		}
	}

    /**
     * @return false|string
     * @throws phpmailerException
     */
    function deleteCoupon() {
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
						//$img = $this->fetchScalar(sprintf('select coupon_id from %s where id = %d',$this->m_junction,$_REQUEST['c_id']));
						$this->execute(sprintf('delete from %s where coupon_id = %d',$this->m_junction,$_REQUEST['c_id']));
						$this->execute(sprintf('update %s set deleted = 1 where id = %d',$this->m_content,$_REQUEST['c_id']));
						break;
					case 'one':
						$status = $this->execute(sprintf('delete from %s where id = %d',$this->m_junction,$_REQUEST['j_id']));
						$ct = $this->fetchScalar(sprintf('select count(0) from %s where coupon_id = %d',$this->m_junction,$_REQUEST['c_id']));
						if ($ct == 0)
							$this->execute(sprintf('update %s set deleted = 1 where id = %d',$this->m_content,$_REQUEST['c_id']));
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
    function addCoupon($fromMain = false) {
		$form = new Forms();
		$form->init($this->getTemplate('addCoupon'));
		if ($fromMain)
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
		if (array_key_exists('formData',$_SESSION) && array_key_exists('couponSearchForm', $_SESSION['formData'])) {
			$_POST = $_SESSION['formData']['couponSearchForm'];
			$msg = '';
		}
		else {
			$ct = $this->fetchScalar(sprintf('select count(0) from %s where published = 0',$this->m_content));
			if ($ct == 0) {
				$_POST = array('showSearchForm'=>1,'deleted'=>0,'sortby'=>'created','sortorder'=>'desc','pager'=>$this->m_pagination);
				$msg = "Showing latest coupons added";
			}
			else {
				$_POST = array('showSearchForm'=>1,'published'=>0,'sortby'=>'created','sortorder'=>'desc','pager'=>$this->m_pagination);
				$msg = "Showing unpublished coupons";
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
		else
			if (array_key_exists('formData',$_SESSION) && array_key_exists('couponSearchForm', $_SESSION['formData']))
				$form->addData($_SESSION['formData']['couponSearchForm']);
		return $form->show();
	}

    /**
     * @return false|string
     * @throws phpmailerException
     */
    function myCouponList() {
		if (array_key_exists('f_id',$_REQUEST) && $_REQUEST['f_id'] > 0) {
			$tmp = parent::couponList($_REQUEST['f_id'],$this->getTemplate('couponList'),$this->getFields('couponByCouponList'));
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
			$tmp = parent::eventList($_REQUEST['f_id'],$this->getTemplate('eventList'),$this->getFields('eventByCouponList'));
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
			$tmp = parent::storeList($_REQUEST['f_id'],$this->getTemplate('storeList'),$this->getFields('storeByCouponList'));
			return $this->ajaxReturn(array('status'=>'true','html'=>$tmp));
		}
		else return $this->ajaxReturn(array('status'=>'true','html'=>''));
	}

    /**
     * @return false|string
     * @throws phpmailerException
     */
    function myProductList() {
		if (array_key_exists('f_id',$_REQUEST) && $_REQUEST['f_id'] > 0) {
			$tmp = parent::productList($_REQUEST['f_id'],$this->getTemplate('productList'),$this->getFields('productByCouponList'));
			return $this->ajaxReturn(array('status'=>'true','html'=>$tmp));
		}
		else return $this->ajaxReturn(array('status'=>'true','html'=>''));
	}

    /**
     * @return false|string
     * @throws phpmailerException
     */
    function myNewsList() {
		if (array_key_exists('f_id',$_REQUEST) && $_REQUEST['f_id'] > 0) {
			$tmp = parent::newsList($_REQUEST['f_id'],$this->getTemplate('newsList'),$this->getFields('newsByCouponList'));
			return $this->ajaxReturn(array('status'=>'true','html'=>$tmp));
		}
		else return $this->ajaxReturn(array('status'=>'true','html'=>''));
	}

    /**
     * @return false|string
     * @throws phpmailerException
     */
    function myBlogList() {
		if (array_key_exists('f_id',$_REQUEST) && $_REQUEST['f_id'] > 0) {
			$tmp = parent::blogList($_REQUEST['f_id'],$this->getTemplate('blogList'),$this->getFields('blogByCouponList'));
			return $this->ajaxReturn(array('status'=>'true','html'=>$tmp));
		}
		else return $this->ajaxReturn(array('status'=>'true','html'=>''));
	}

    /**
     * @return false|string
     * @throws phpmailerException
     */
    function myAdvertList() {
		if (array_key_exists('f_id',$_REQUEST) && $_REQUEST['f_id'] > 0) {
			$tmp = parent::advertList($_REQUEST['f_id'],$this->getTemplate('advertList'),$this->getFields('advertByCouponList'));
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
				$this->addError('Coupons are still attached to this folder');
				return $this->ajaxReturn(array('status'=>'false'));
			}
			$ct = $this->fetchScalar(sprintf('select count(0) from %s t1, %s t2 where t2.id = %d and t1.left_id > t2.left_id and t1.right_id < t2.right_id and t1.level > t2.level',$this->m_tree, $this->m_tree, $_REQUEST['p_id']));
			if ($ct > 0) {
				$this->addError('Other categories are still attached to this folder');
				return $this->ajaxReturn(array('status'=>'false'));
			}
			if (!$this->deleteCheck('coupons',$_REQUEST['p_id'],$inUse)) {
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

}

?>
