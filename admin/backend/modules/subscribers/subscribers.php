<?php

/**
 * Manages subscribers for backend
 */
class subscribers extends Backend {

	private $m_tree = 'subscriber_folders';
	private $m_content = 'subscriber';
	private $m_junction = 'subscriber_by_folder';
	private $m_perrow = 5;

	/**
	 * @throws phpmailerException
	 */
	public function __construct() {
		$this->M_DIR = 'backend/modules/subscribers/';
		$this->setTemplates(
			array(
				'stats'=>$this->M_DIR.'forms/stats.html',
				'statsRow'=>$this->M_DIR.'forms/statsRow.html',
				'deleteItem'=>$this->M_DIR.'forms/deleteItem.html',
				'deleteItemResult'=>$this->M_DIR.'forms/deleteItemResult.html',
				'main'=>$this->M_DIR.'subscribers.html',
				'form'=>$this->M_DIR.'forms/form.html',
				'showContentTree'=>$this->M_DIR.'forms/contenttree.html',
				'subscriberInfo'=>$this->M_DIR.'forms/subscriberInfo.html',
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
				'addSubscriber'=>$this->M_DIR.'forms/addSubscriber.html'
			)
		);
		$this->setFields(array(
			'stats'=>array(),
			'statsRow'=>array(
				'started'=>array('type'=>'timestamp','suppressNull'=>true),
				'completed'=>array('type'=>'timestamp','suppressNull'=>true,'mask'=>'h:i:s a'),
				'aborted'=>array('type'=>'timestamp','suppressNull'=>true,'mask'=>'h:i:s a')
			),
			'deleteItem'=>array(
				'options'=>array('name'=>'deleteItem','database'=>false),
				'j_id'=>array('type'=>'tag'),
				'deleteItem'=>array('type'=>'hidden','value'=>1),
				'cancel'=>array('type'=>'radiobutton','name'=>'action','value'=>'cancel','checked'=>'checked'),
				'one'=>array('type'=>'radiobutton','name'=>'action','value'=>'one'),
				'all'=>array('type'=>'radiobutton','name'=>'action','value'=>'all')
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
				'options'=>array('method'=>'post','action'=>'/modit/ajax/addContent/subscribers'),
				'id'=>array('type'=>'tag','database'=>false),
				'company'=>array('type'=>'input','required'=>false,),
				'firstname'=>array('type'=>'input','required'=>false,'prettyName'=>'First Name'),
				'lastname'=>array('type'=>'input','required'=>false,'prettyName'=>'Last Name'),
				'email'=>array('type'=>'input','required'=>true,'validation'=>'email'),
				'created'=>array('type'=>'datestamp','mask'=>'d-M-Y h:m:i','database'=>false),
				'unsubscribed'=>array('type'=>'datestamp','mask'=>'d-M-Y h:m:i','database'=>false),
				'enabled'=>array('type'=>'checkbox','required'=>false,'value'=>1,'checked'=>'checked'),
				'deleted'=>array('type'=>'checkbox','required'=>false,'value'=>1),
				'testing'=>array('type'=>'checkbox','required'=>false,'value'=>1),
				'submit'=>array('type'=>'submitbutton','database'=>false,'value'=>'Save'),
				'destFolders'=>array('name'=>'destFolders','type'=>'select','multiple'=>'multiple','required'=>false,'id'=>'destFolders','database'=>false,'options'=>$this->nodeSelect(0, $this->m_tree, 2, false, false),'reformatting'=>false,'prettyName'=>'Member of Groups'),
				'addContent'=>array('type'=>'hidden','value'=>1,'database'=>false)
			),
			'showSearchForm'=>array(
				'options'=>array('action'=>'showSearchForm','name'=>'searchForm','id'=>'search_form'),
				'opt_name'=>array('type'=>'select','name'=>'opt_name','lookup'=>'search_string'),
				'opt_email'=>array('type'=>'select','name'=>'opt_email','lookup'=>'search_string'),
				'name'=>array('type'=>'input','required'=>false,'searchField'=>'concat(firstname," ",lastname)'),
				'email'=>array('type'=>'input','required'=>false),
				'created'=>array('type'=>'datepicker','required'=>false),
				'enabled'=>array('type'=>'select','lookup'=>'boolean'),
				'deleted'=>array('type'=>'select','lookup'=>'boolean'),
				'testing'=>array('type'=>'select','lookup'=>'boolean'),
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
					'action'=>'/modit/subscribers/showPageProperties',
					'method'=>'post'
				),
				'title'=>array('type'=>'textfield','required'=>true,'prettyName'=>'Title'),
				'showPageProperties'=>array('type'=>'hidden','value'=>1, 'database'=>false),
				'alternate_title'=>array('type'=>'textfield','required'=>false),
				'p_id'=>array('type'=>'select','required'=>true,'database'=>false,'optionslist'=>array('table'=>$this->m_tree,'root'=>0,'indent'=>2,'inclusive'=>true)),
				'enabled'=>array('type'=>'checkbox','required'=>false,'value'=>1),
				'description'=>array('type'=>'textarea','required'=>false, 'id'=>'folderDescription','class'=>'mceAdvanced'),
				'teaser'=>array('type'=>'textarea','required'=>false, 'id'=>'folderTeaser','class'=>'mceSimple'),
				'id'=>array('type'=>'hidden', 'database'=>false),
				'image'=>array('type'=>'tag'),
				'rollover_image'=>array('type'=>'tag'),
				'imagesel_a'=>array('type'=>'image','unknown'=>true,'database'=>false,'id'=>'imagesel_a'),
				'imagesel_b'=>array('type'=>'image','unknown'=>true,'database'=>false,'id'=>'imagesel_b'),
				'submit'=>array('type'=>'submitbutton','value'=>'Save','database'=>false)
			),
			'showContentTree' => array(),
			'subscriberInfo' => array(),
			'showSubscriberContent' => array(),
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
				'enabled'=>array('type'=>'booleanIcon'),
				'testing'=>array('type'=>'booleanIcon'),
				'deleted'=>array('type'=>'booleanIcon')
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
				$stmt = $this->prepare(sprintf('update %s set %s where id = %d',$this->m_tree,implode(',',$flds),$data['id']));
				$stmt->bindParams(array_merge(array(str_repeat('s', count($flds))),$values));
				if ($status = $stmt->execute()) {
					if ($this->isAjax()) {
						$this->logMessage('showPageProperties', 'executing ajax success return', 3);
						$this->addMessage('Record successfully added');
						return $this->ajaxReturn(array(
								'status'=>'true',
								'html'=>'',
								'url'=>'/modit/subscribers?p_id='.$data['id']
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
			$form->init($this->getTemplate('showFolderContent'),array('name'=>'showFolderContent'));
			$frmFields = $form->buildForm($this->getFields('showFolderContent'));
			if (array_key_exists('pagenum',$_REQUEST)) 
				$pageNum = $_REQUEST['pagenum'];
			else
				$pageNum = 1;	// no 0 based calcs
			if ($pageNum <= 0) $pageNum = 1;
			$perPage = $this->m_perrow;
			if (array_key_exists('pager',$_REQUEST)) $perPage = $_REQUEST['pager'];
			$count = $this->fetchScalarTest('select count(n.id) from '.$this->m_content.' n where n.id in (select f.subscriber_id from '.$this->m_junction.' f where f.folder_id = %d)', $_REQUEST['p_id']);
			$pagination = $this->pagination($count, $perPage, $pageNum, 
				array('prev'=>$this->M_DIR.'forms/paginationPrev.html','next'=>$this->M_DIR.'forms/paginationNext.html',
						'pages'=>$this->M_DIR.'forms/paginationPage.html', 'wrapper'=>$this->M_DIR.'forms/paginationWrapper.html'));
			$start = ($pageNum-1)*$perPage;
			$sortby = 'created';
			$sortorder = 'desc';
			if (count($_POST) > 0 && array_key_exists('showFolderContent',$_POST)) {
				$sortby = $_POST['sortby'];
				$sortorder = $_POST['sortorder'];
				$form->addData($_POST);
			}
			$sql = sprintf('select a.*, f.id as j_id, concat(firstname," ",lastname) as name from %s a left join %s f on a.id = f.subscriber_id where f.folder_id = %d order by %s %s limit %d,%d',  $this->m_content, $this->m_junction, $_REQUEST['p_id'],$sortby, $sortorder, $start,$perPage);
			$subscribers = $this->fetchAllTest($sql);
			$this->logMessage('showPageContent', sprintf('sql [%s], records [%d]',$sql, count($subscribers)), 2);
			$articles = array();
			foreach($subscribers as $article) {
				$frm = new Forms();
				$frm->init($this->getTemplate('articleList'),array());
				$tmp = $frm->buildForm($this->getFields('articleList'));
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
			if (array_key_exists('formData',$_SESSION) && array_key_exists('subscriberSearchForm', $_SESSION['formData']))
				$_POST = $_SESSION['formData']['subscriberSearchForm'];
			else
				$_POST = array('showSearchForm'=>1,'deleted'=>0,'sortby'=>'published','sortorder'=>'asc');
		$this->logMessage("showSearchForm",sprintf("post [%s]",print_r($_POST,true)),3);
		if (count($_POST) > 0 && array_key_exists('showSearchForm',$_POST)) {
			//if ((!array_key_exists('deleted',$_POST)) || strlen($_POST['deleted']) == 0) $_POST['deleted'] = 0;
			$form->addData($_POST);
			if ($form->validate()) {
				$_SESSION['formData']['subscriberSearchForm'] = $form->getAllData();
				$srch = array();
				foreach($frmFields as $key=>$opts) {
					switch($key) {
						case 'quicksearch':
							if (array_key_exists('opt_quicksearch',$_POST) && $_POST['opt_quicksearch'] != '' && $value = $form->getData($key)) {
								if ($_POST['opt_quicksearch'] == 'like' && strpos($value,'%',0) === false) {
									$value = '%'.$value.'%';
								}
								$tmp = array();
								$tmp[] = sprintf(' firstname %s "%s"',$_POST['opt_quicksearch'],$this->escape_string($value));
								$tmp[] = sprintf(' lastname %s "%s"',$_POST['opt_quicksearch'],$this->escape_string($value));
								$tmp[] = sprintf(' email %s "%s"',$_POST['opt_quicksearch'],$this->escape_string($value));
								$srch = array(sprintf('(%s)',implode(' or ',$tmp)));
							}
							break;
						case 'name':
						case 'email':
							if (array_key_exists('opt_'.$key,$_POST) && $_POST['opt_'.$key] != '' && $value = $form->getData($key)) {
								if ($_POST['opt_'.$key] == 'like' && strpos($value,'%',0) === false) {
									$value = '%'.$value.'%';
								}
								$srch[] = sprintf(' %s %s "%s"',array_key_exists('searchField',$opts) ? $opts['searchField'] : $key,$_POST['opt_'.$key],$this->escape_string($value));
							}
							break;
						case 'created':
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
								$srch[] = sprintf(' n.id in (select subscriber_id from %s where folder_id = %d) ', $this->m_junction, $value);
							}
							break;
						case 'enabled':
						case 'deleted':
						case 'testing':
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
					$perPage = $this->m_perrow;
					if (array_key_exists('pager',$_REQUEST)) $perPage = $_REQUEST['pager'];
					$count = $this->fetchScalarTest('select count(n.id) from '.$this->m_content.' n where 1=1 and %s', implode(' and ',$srch));
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
					$sql = sprintf('select n.*, 0 as j_id, concat(firstname," ",lastname) as name from %s n where 1=1 and %s order by %s %s limit %d,%d',
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
	 * @param bool $fromMain
	 * @return array|false|mixed|string|string[]|void
	 * @throws phpmailerException
	 */
    function addContent($fromMain = false) {
		$form = new Forms();
		$form->init($this->getTemplate('addContent'),array('name'=>'addContent'));
		if (!(array_key_exists('a_id',$_REQUEST) && $_REQUEST['a_id'] > 0 && $data = $this->fetchSingleTest('select * from %s where id = %d', $this->m_content, $_REQUEST['a_id']))) {
			$data = array('id'=>0,'enabled'=>true); 
		}
		if (count($_REQUEST) > 0 && array_key_exists('destFolders',$_REQUEST) || $data['id'] > 0) {
			$ids = array();
			if (array_key_exists('destFolders',$_REQUEST)) {
				$ids = $_REQUEST['destFolders'];
				if (!is_array($ids)) $ids = array($ids);
			}
			if ($data['id'] > 0) {
				$tmp = $this->fetchScalarAllTest('select folder_id from %s where subscriber_id = %d', $this->m_junction, $data['id']);
				$ids = array_merge($ids,$tmp);
			}
			if (count($ids) > 0) {
				$data['destFolders'] = $ids;
			}
		}
		$data['history'] = $this->getStats($data['id']);
		$frmFields = $form->buildForm($this->getFields('addContent'));
		$form->addData($data);
		$status = 'false';	//assume it failed
		if (count($_POST) > 0 && array_key_exists('addContent',$_POST)) {
			$form->addData($_POST);
			if ($form->validate()) {
				$id = $_POST['a_id'];
				unset($frmFields['a_id']);
				unset($frmFields['options']);
				$flds = array();
				foreach($frmFields as $key=>$fld) {
					if (!(array_key_exists('database',$fld) && $fld['database'] == false)) {
						$flds[$fld['name']] = $form->getData($fld['name']);
					}
				}				
				if ($form->getData('enabled') == 0 && $id > 0) {
					if ($this->fetchScalarTest('select enabled from %s where id = %d',$this->m_content,$data['id']) != 0) {
						$flds['unsubscribed'] = date('c');
						$flds['unsubscribed_ip'] = $_SERVER['REMOTE_ADDR'];
					}
				}
				else {
					$flds['unsubscribed'] = '0000-00-00 00:00:00';
					$flds['unsubscribed_ip'] = '';
				}
				if ($id == 0) {
					$flds['created'] = date('c');
					$flds['created_ip'] = $_SERVER['REMOTE_ADDR'];
					$stmt = $this->prepare(sprintf('insert into %s(%s) values(%s)', $this->m_content, implode(', ',array_keys($flds)), str_repeat('?, ', count($flds)-1).'?'));
				}
				else {
					$stmt = $this->prepare(sprintf('update %s set %s=? where id = %d', $this->m_content, implode('=?, ',array_keys($flds)),$data['id']));
				}
				$stmt->bindParams(array_merge(array(str_repeat('s', count($flds))),array_values($flds)));
				$this->beginTransaction();
				if ($stmt->execute()) {
					if ($id == 0) {
						$id = $this->insertId();
						$data['id'] = $id;
					}
					$destFolders = array_key_exists('destFolders',$_POST) ? $_POST['destFolders'] : array(0);
					if (!is_array($destFolders)) $destFolders = array($destFolders);
					//
					//	delete folders we are no longer a member of
					//
					$this->execute(sprintf('delete from %s where subscriber_id = %d and folder_id not in (%s)', $this->m_junction, $id,implode(',',$destFolders)));
					$sql = sprintf('select id from %s where id in (%s) and id not in (select folder_id from %s where subscriber_id = %d and folder_id in (%s))',
					$this->m_tree,implode(',',$destFolders),$this->m_junction,$id,implode(',',$destFolders));
					$new = $this->fetchScalarAllTest($sql);
					$status = true;
					foreach($new as $key=>$folder) {
						$obj = new preparedStatement(sprintf('insert into %s(subscriber_id,folder_id) values(?,?)',$this->m_junction));
						$obj->bindParams(array('dd',$id,$folder));
						$status = $status && $obj->execute();
					}
					if ($status) {
						$this->commitTransaction();
						return $this->ajaxReturn(array(
							'status' => 'true',
							'url' => sprintf('/modit/subscribers?p_id=%d',count($destFolders) > 0 ? $destFolders[0] : 0)
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
			if ($folder = $this->fetchSingleTest('select * from %s where id = %d',$this->m_tree,$dest)) {
				$curr = $this->fetchScalarTest('select subscriber_id from %s where id = %d',$this->m_junction,$src);
				$status = true;
				if (array_key_exists('move',$_REQUEST)) {
					//
					//	move it - delete all other folders
					//
					$this->logMessage('moveArticle', sprintf('moving subscriber %d to folder %d',$src,$dest),2);
					$this->beginTransaction();
					if ($status = $this->execute(sprintf('delete from %s where id = %d', $this->m_junction, $src))) {
						if (!$this->fetchSingleTest('select * from %s where subscriber_id = %d and folder_id = %d',$this->m_junction,$curr,$dest)) {
							$obj = new preparedStatement(sprintf('insert into %s(subscriber_id,folder_id) values(?,?)',$this->m_junction));
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
					$curr = $src;
					$this->logMessage('moveArticle', sprintf('cloning subscriber %d to folder %d',$curr,$dest),2);
					if (!($this->fetchSingleTest('select * from %s where subscriber_id = %d and folder_id = %d',$this->m_junction,$curr,$dest))) {
						$obj = new preparedStatement(sprintf('insert into %s(subscriber_id,folder_id) values(?,?)',$this->m_junction));
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
			$src = $this->fetchSingleTest('select * from %s where id = %d',$this->m_junction,$src);
			$sql = sprintf('select * from %s where folder_id = %d order by sequence limit %d,1',$this->m_junction,$src['folder_id'],$dest);
			$dest = $this->fetchSingleTest($sql);
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
						$this->logMessage('deleteArticle','deleting subsciber '.$_REQUEST['a_id'],1);
						$this->execute(sprintf('delete from %s where subscriber_id = %d',$this->m_junction,$_REQUEST['a_id']));
						$this->execute(sprintf('update %s set deleted = 1 where id = %d',$this->m_content,$_REQUEST['a_id']));
						break;
					case 'one':
						$status = $this->execute(sprintf('delete from %s where id = %d',$this->m_junction,$_REQUEST['j_id']));
						$ct = $this->fetchScalarTest('select count(0) from %s where subscriber_id = %d',$this->m_junction,$_REQUEST['a_id']);
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
		/*//Sonar Cloud - PHP Major bug - All code should be reachable
		if (array_key_exists('j_id',$_REQUEST)) {
			$id = $_REQUEST['j_id'];
			$curr = $this->fetchScalarTest('select subscriber_id from %s where id = %d',$this->m_junction,$id);
			$this->logMessage('deleteArticle', sprintf('deleting subscriber junction %d for store %d',$id,$curr), 2);
			$this->beginTransaction();
			$this->execute(sprintf('delete from %s where id = %d',$this->m_junction,$id));
			if (($remining = $this->fetchScalarTest('select count(0 from %s where subscriber_id = %d',$this->m_junction,$curr))) == 0) {
				$this->logMessage('deleteStore', sprintf('deleting ad %d - no more references',$curr), 2);
				$this->execute(sprintf('update %s set deleted = 1 where id = %d',$this->m_content,$curr));
			}
			$this->commitTransaction();
			return $this->ajaxReturn(array('status'=>'true'));
		}*/
	}

	/**
	 * @return false|string|void
	 * @throws phpmailerException
	 */
    function deleteContent() {
		$status = 'false';
		if (array_key_exists('p_id',$_REQUEST)) {
			$id = $_REQUEST['p_id'];
			$ct = $this->fetchScalarTest('select count(0) from %s where folder_id = %d',$this->m_junction,$_REQUEST['p_id']);
			if ($ct > 0) {
				$this->addError('Ads are still attached to this folder');
				return $this->ajaxReturn(array('status'=>'false'));
			}
			$ct = $this->fetchScalarTest(sprintf('select count(0) from %s t1, %s t2 where t2.id = %d and t1.left_id > t2.left_id and t1.right_id < t2.right_id and t1.level > t2.level',$this->m_tree, $this->m_tree, $_REQUEST['p_id']));
			if ($ct > 0) {
				$this->addError('Other categories are still attached to this folder');
				return $this->ajaxReturn(array('status'=>'false'));
			}
			if (!$this->deleteCheck('subscriber',$_REQUEST['p_id'],$inUse)) {
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
			if (array_key_exists('formData',$_SESSION) && array_key_exists('subscriberSearchForm', $_SESSION['formData']))
				$form->addData($_SESSION['formData']['subscriberSearchForm']);
		return $form->show();
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
    function addSubscriber($fromMain = false) {
		$form = new Forms();
		$form->init($this->getTemplate('addSubscriber'));
		if ($fromMain)
			return $form->show();
		else
			return $this->show($form->show());
	}

	/**
	 * @param int $fromMain
	 * @return array|false|mixed|string|string[]|void
	 * @throws phpmailerException
	 */
    function moduleStatus($fromMain = 0) {
		if (array_key_exists('formData',$_SESSION) && array_key_exists('subscriberSearchForm',$_SESSION['formData'])) {
			$_POST = $_SESSION['formData']['subscriberSearchForm'];
			$msg = 'Showing search results';
		}
		else {
			$ct = $this->fetchScalarTest('select count(0) from %s where enabled = 0',$this->m_content);
			if ($ct == 0) {
				$_POST = array('showSearchForm'=>1,'enabled'=>1,'sortby'=>'created','sortorder'=>'desc','pager'=>5);
				$msg = "Showing latest subscribers added";
			}
			else {
				$_POST = array('showSearchForm'=>1,'enabled'=>0,'deleted'=>0,'sortby'=>'created','sortorder'=>'desc','pager'=>5);
				$msg = "Showing unpublished subscribers";
			}
		}
		$result = $this->showSearchForm($fromMain,$msg);
		return $result;
	}

	/**
	 * @param $id
	 * @return string
	 * @throws phpmailerException
	 */
    function getStats($id) {
		$recs = $this->fetchAllTest('select n.*, b.started from newsletter n, newsletter_batch b, newsletter_batch_subscriber s where s.subscriber_id = %d and b.id = s.batch_id and n.id = b.newsletter_id order by b.started desc',$id);
		$inner = new Forms();
		$inner->init($this->getTemplate('statsRow'));
		$flds = $inner->buildForm($this->getFields('statsRow'));
		$return = array();
		foreach($recs as $send) {
			$inner->reset();
			$inner->addData($send);
			$return[] = $inner->show();
		}
		return implode('',$return);
	}

    /**
     * @return void
     */
    function maintainNewsletters() {
		header('/modit/newsletters');
	}
}

?>
