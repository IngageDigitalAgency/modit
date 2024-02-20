<?php

class question extends Backend {

	private $m_tree = 'question_folders';
	private $m_content = 'questions';
	private $m_junction = 'question_by_folder';
	private $m_details = 'question_answers';
	private $m_responses = 'poll_response_answers';
	private $m_perrow = 5;

	public function __construct() {
		$this->M_DIR = 'backend/modules/question/';
		$this->setTemplates(
			array(
				'main'=>$this->M_DIR.'question.html',
				'showSearchForm'=>$this->M_DIR.'forms/searchForm.html',
				'articleList'=>$this->M_DIR.'forms/articleList.html',
				'folderProperties'=>$this->M_DIR.'forms/folder.html',
				'showFolderContent'=>$this->M_DIR.'forms/folderContent.html',
				'folderInfo'=>$this->M_DIR.'forms/folderInfo.html',
				'showContentTree'=>$this->M_DIR.'forms/contenttree.html',
				'addContent'=>$this->M_DIR.'forms/addContent.html',
				'header'=>$this->M_DIR.'forms/heading.html',
				'editQuestion'=>$this->M_DIR.'forms/editQuestion.html',
				'editQuestionSuccess'=>$this->M_DIR.'forms/editQuestionSuccess.html',
				'questionList'=>$this->M_DIR.'forms/questionList.html',
				'deleteItem'=>$this->M_DIR.'forms/deleteItem.html',
				'deleteItemResult'=>$this->M_DIR.'forms/deleteItemResult.html',
				'deleteSuccess'=>$this->M_DIR.'forms/deleteSuccess.html',
				'responses'=>$this->M_DIR.'forms/responses.html',
				'editResponse'=>$this->M_DIR.'forms/editResponse.html',
				'editResponseSuccess'=>$this->M_DIR.'forms/editResponseSuccess.html',
				'usage'=>$this->M_DIR.'forms/usage.html'
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
			'usage'=>array(),
			'editResponse'=>array(
				'options'=>array('name'=>'editResponseForm','database'=>false),
				'text'=>array('type'=>'input','required'=>true),
				'deleted'=>array('type'=>'checkbox','required'=>false,'value'=>1),
				'question_id'=>array('type'=>'tag'),
				'image1'=>array('type'=>'tag','required'=>false,'prettyName'=>'Image 1'),
				'image2'=>array('type'=>'tag','required'=>false),
				'imagesel_c'=>array('type'=>'image','unknown'=>true,'database'=>false,'id'=>'imagesel_c'),
				'imagesel_d'=>array('type'=>'image','unknown'=>true,'database'=>false,'id'=>'imagesel_d'),
				'submit'=>array('type'=>'submitButton','value'=>'Save','database'=>false),
				'editResponse'=>array('type'=>'hidden','value'=>1,'database'=>false)
			),
			'responses'=>array(
				'delete'=>array('type'=>'checkbox','name'=>'responseDelete[%%id%%]','value'=>1)
			),
			'showFolderContent'=>array(
				'options'=>array('action'=>'showPageContent'),
				'sortby'=>array('type'=>'hidden','value'=>'created'),
				'sortorder'=>array('type'=>'hidden','value'=>'desc'),
				'pagenum'=>array('type'=>'hidden','value'=>1),			
				'pager'=>array('type'=>'select','required'=>true,'value'=>$this->m_perrow,'lookup'=>'paging','id'=>'pager'),
				'showFolderContent'=>array('type'=>'hidden','value'=>1)
			),
			'articleList' => array(
				'id'=>array('type'=>'tag'),
				'title'=>array('type'=>'tag'),
				'created'=>array('type'=>'datestamp','mask'=>'d-M-Y h:i:s a'),
				'start_date'=>array('type'=>'datestamp','mask'=>'d-M-Y h:i:s a'),
				'end_date'=>array('type'=>'datestamp','mask'=>'d-M-Y h:i:s a'),
				'published'=>array('type'=>'booleanicon'),
				'enabled'=>array('type'=>'booleanicon'),
				'deleted'=>array('type'=>'booleanicon'),
				'image'=>array('type'=>'image')
			),
			'folderProperties' => array(
				'options'=>array(
					'action'=>'/modit/question/showPageProperties',
					'method'=>'post'
				),
				'title'=>array('type'=>'textfield','required'=>true,'prettyName'=>'Title'),
				'showPageProperties'=>array('type'=>'hidden','value'=>1, 'database'=>false),
				'p_id'=>array('type'=>'select','required'=>true,'database'=>false,'optionslist'=>array('table'=>$this->m_tree,'root'=>0,'indent'=>2,'inclusive'=>true)),
				'enabled'=>array('type'=>'checkbox','required'=>false,'value'=>1),
				'notes'=>array('type'=>'textarea','required'=>false,'class'=>'mceNoEditor'),
				'id'=>array('type'=>'hidden', 'database'=>false),
				'submit'=>array('type'=>'submitbutton','value'=>'Save','database'=>false)
			),
			'addContent'=>array(
				'options'=>array('name'=>'addContent','action'=>'/modit/ajax/addContent/question','database'=>false),
				'submit'=>array('type'=>'submitbutton','value'=>'Save','database'=>false),
				'addContent'=>array('type'=>'hidden','value'=>1,'database'=>false),
				'id'=>array('type'=>'tag','database'=>false),
				'name'=>array('type'=>'input','required'=>true,'prettyName'=>'Name'),
				'teaser'=>array('type'=>'textarea','required'=>false,'class'=>'mceSimple'),
				'type'=>array('type'=>'select','required'=>true,'lookup'=>'questionType'),
				'description'=>array('type'=>'textarea','required'=>true,'id'=>'questionBody','class'=>'mceAdvanced','prettyName'=>'Description'),
				'published'=>array('type'=>'checkbox','required'=>false,'value'=>1),
				'enabled'=>array('type'=>'checkbox','required'=>false,'value'=>1),
				'image1'=>array('type'=>'tag','required'=>false,'prettyName'=>'Image 1'),
				'image2'=>array('type'=>'tag','required'=>false,'prettyName'=>'Image 2'),
				'imagesel_a'=>array('type'=>'image','unknown'=>true,'database'=>false,'id'=>'imagesel_a'),
				'imagesel_b'=>array('type'=>'image','unknown'=>true,'database'=>false,'id'=>'imagesel_b'),
				'destFolders'=>array('name'=>'destFolders','type'=>'select','multiple'=>'multiple','required'=>true,'id'=>'destFolders','database'=>false,'options'=>$this->nodeSelect(0, 'question_folders', 2, false, false),'reformatting'=>false,'prettyName'=>'Member of')
			),
			'header'=>array(),
			'showSearchForm'=>array(
				'options'=>array('action'=>'showSearchForm','name'=>'searchForm','id'=>'search_form'),
				'id'=>array('type'=>'input','required'=>false),
				'opt_id'=>array('type'=>'select','lookup'=>'search_options'),
				'created'=>array('type'=>'datepicker','required'=>false),
				'opt_created'=>array('type'=>'select','lookup'=>'search_options'),
				'name'=>array('type'=>'input','required'=>false),
				'opt_name'=>array('type'=>'select','lookup'=>'search_string'),
				'type'=>array('type'=>'select','lookup'=>'questionType'),
				'published'=>array('type'=>'select','required'=>false,'lookup'=>'boolean'),
				'enabled'=>array('type'=>'select','required'=>false,'lookup'=>'boolean'),
				'deleted'=>array('type'=>'select','required'=>false,'lookup'=>'boolean'),
				'showSearchForm'=>array('type'=>'hidden','value'=>1),
				'pagenum'=>array('type'=>'hidden','value'=>1),
				'sortby'=>array('type'=>'hidden','value'=>'created'),
				'sortorder'=>array('type'=>'hidden','value'=>'desc'),
				'quicksearch'=>array('type'=>'input','name'=>'quicksearch','required'=>false),
				'opt_quicksearch'=>array('type'=>'hidden','value'=>'like'),
				'pager'=>array('type'=>'select','required'=>true,'value'=>$this->m_perrow,'lookup'=>'paging','id'=>'pager'),
				'submit'=>array('type'=>'submitbutton','value'=>'Search')
			)
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
		if (!(array_key_exists('id',$_REQUEST) && $data = $this->fetchSingle(sprintf('select * from %s where id = %d',$this->m_tree,$_REQUEST['id']))))
			$data = array('enabled'=>1,'id'=>0,'p_id'=>0);
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

		$frmFlds = $form->buildForm($frmFlds);
		$form->addData($data);
		if (count($_POST) > 0 && array_key_exists('showPageProperties',$_POST)) {
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
						$this->logMessage('showPageProperties', sprintf('moving [%d] to [%d] posted[%d]',$data['id'],$parent['id'], $_POST['p_id']), 1);
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
					if ($this->isAjax()) {
						$this->logMessage('showPageProperties', 'executing ajax success return', 3);
						$this->addMessage('Record successfully added');
						return $this->ajaxReturn(array(
								'status'=>'true',
								'html'=>'',
								'url'=>'/modit/question?p_id='.$data['id']
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
		if (array_key_exists('f_id',$_REQUEST)) {
			if ($data = $this->fetchSingle(sprintf('select * from %s where id = %d', $this->m_tree, $_REQUEST['f_id']))) {
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
		$f_id = array_key_exists('f_id',$_REQUEST) ? $_REQUEST['f_id'] : 0;
		$form = new Forms();
		if ($f_id > 0 && $data = $this->fetchSingle(sprintf('select * from %s where id = %d', $this->m_tree, $f_id))) {
			$form->init($this->getTemplate('showFolderContent'),array('name'=>'showFolderContent'));
			$frmFields = $form->buildForm($this->getFields('showFolderContent'));
			if (array_key_exists('pagenum',$_REQUEST)) 
				$pageNum = $_REQUEST['pagenum'];
			else
				$pageNum = 1;	// no 0 based calcs
			if ($pageNum <= 0) $pageNum = 1;
			if ($this->m_perrow > 0) {
				$perPage = $this->m_perrow;
				$count = $this->fetchScalar(sprintf('select count(n.id) from %s n where n.id in (select f.question_id from %s f where f.folder_id = %d)', $this->m_content, $this->m_junction, $f_id));
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
			$sql = sprintf('select i.*,j.id as j_id from %s i, %s j where j.folder_id = %d and i.id = j.question_id limit %d,%d', $this->m_content, $this->m_junction, $f_id, $start, $perPage);
			$images = $this->fetchAll($sql);
			$this->logMessage('showPageContent', sprintf('sql [%s], records [%d]',$sql, count($images)), 2);
			$output = array();
			//$output[] = '<tr>';
			$ct = 0;
			foreach($images as $image) {
				$frm = new Forms();
				$frm->init($this->getTemplate('articleList'),array());
				$tmp = $frm->buildForm($this->getFields('articleList'));
				$frm->addData($image);
				$tmp = $this->fetchScalar(sprintf('select count(0) from poll_questions where question_id = %d and deleted = 0',$image['id']));
				$frm->addTag('usage',$tmp);
				$tmp = $this->fetchScalar(sprintf('select count(0) as ct from (select distinct response_id from %s where question_id = %d) as x',$this->m_responses,$image['id']));
				$frm->addTag('responses',$tmp);
				$output[] = $frm->show();
				$ct += 1;
				//if ($ct % $this->m_perrow == 0)
				//	$output[] = '</tr><tr>';
			}
			//$output[] = '</tr>';
			$form->addTag('articles',implode('',$output),false);
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
		$this->logMessage('showSearchForm',sprintf('post [%s]',print_r($_POST,true)),1);
		$form = new Forms();
		$form->init($this->getTemplate('showSearchForm'),array('name'=>'showSearchForm','persist'=>true));
		$frmFields = $form->buildForm($this->getFields('showSearchForm'));
		if (count($_POST) == 0) {
			if (array_key_exists('formData',$_SESSION) && array_key_exists('questionSearchForm', $_SESSION['formData']))
				$_POST = $_SESSION['formData']['questionSearchForm'];
			else
				$_POST = array('showSearchForm'=>1,'enabled'=>0);
		}
		if (count($_POST) > 0 && array_key_exists('showSearchForm',$_POST)) {
			$form->addData($_POST);
			if ($form->validate()) {
				$_SESSION['formData']['questionSearchForm'] = $form->getAllData();
				$srch = array();
				foreach($frmFields as $key=>$value) {
					$this->logMessage('showSearchForm',sprintf('testing [%s]',$key),1);
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
						case 'name':
							if (array_key_exists('opt_'.$key,$_POST) && strlen($_POST['opt_'.$key]) > 0 && $value = $form->getData($key)) {
								if ($_POST['opt_'.$key] == 'like' && strpos($value,'%',0) === false) {
									$value = '%'.$value.'%';
								}
								$srch[] = sprintf(' %s %s "%s"',$key, $_POST['opt_'.$key],$this->escape_string($value));
							}
							break;
						case 'id':
						case 'created':
						case 'expires':
							$this->logMessage('showSearchForm',sprintf('in created code [%s]',$form->getData($key)),1);
							if (array_key_exists('opt_'.$key,$_POST) && strlen($_POST['opt_'.$key]) > 0 && !is_null($value = $form->getData($key))) {
								if ($_POST['opt_'.$key] == 'like') {
									$this->addError('Like is not supported for dates');
								}
								else
									$srch[] = sprintf(' %s %s "%s"',$key, $_POST['opt_'.$key],$this->escape_string($value));
							}
							break;
						case 'folder':
							if (($value = $form->getData($key)) > 0) {
								$srch[] = sprintf(' id in (select poll_id from %s where folder_id = %d) ',$this->m_junction, $value);
							}
							break;
						case 'enabled':
						case 'published':
						case 'deleted':
							if (!is_null($value = $form->getData($key)))
								if (strlen($value) > 0)
									$srch[] = sprintf(' %s = %s',$key,$this->escape_string($value));
							break;
						case 'type':
							if (!is_null($value = $form->getData($key)))
								if (strlen($value) > 0)
									$srch[] = sprintf(' %s = "%s"',$key,$this->escape_string($value));
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
					$perPage = $this->m_perrow;
					$sql = sprintf('select count(n.id) from %s n where 1=1 and %s', $this->m_content, implode(' and ',$srch));
					$count = $this->fetchScalar($sql);
					$this->logMessage('showSearchForm', sprintf('sql [%s] records [%d]',$sql,$count), 2);
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
					$recs = $this->fetchAll($sql);
					$this->logMessage('showSearchForm', sprintf('sql [%s] records [%d]',$sql,count($recs)), 2);
					$output = array();
					$ct = 0;
					$output[] = '<tr>';
					foreach($recs as $image) {
						$frm = new Forms();
						$frm->reset();
						$frm->init($this->getTemplate('articleList'),array());
						$tmp = $frm->buildForm($this->getFields('articleList'));
						$frm->addData($image);
						$tmp = $this->fetchScalar(sprintf('select count(0) from poll_questions where question_id = %d and deleted = 0',$image['id']));
						$frm->addTag('usage',$tmp);
						$tmp = $this->fetchScalar(sprintf('select count(0) as ct from (select distinct response_id from %s where question_id = %d) as x',$this->m_responses,$image['id']));
						$frm->addTag('responses',$tmp);
						$output[] = $frm->show();
						$ct += 1;
						//if ($ct % $this->m_perrow == 0)
						//	$output[] = '</tr><tr>';
					}
					$output[] = '</tr>';
					$form->addTag('articles',implode('',$output),false);
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

	function addContent($fromMain = false) {
		$form = new Forms();
		$form->init($this->getTemplate('addContent'));
		$frmFields = $this->getFields('addContent');
		if (!(array_key_exists('p_id',$_REQUEST) && $_REQUEST['p_id'] > 0 && $data = $this->fetchSingle(sprintf('select * from %s where id = %d', $this->m_content, $_REQUEST['p_id'])))) {
			$data = array('id'=>0,'image1'=>'','image2'=>''); 
		}

		if (count($_REQUEST) > 0 && array_key_exists('destFolders',$_REQUEST) || $data['id'] > 0) {
			$ids = array();
			if (array_key_exists('destFolders',$_REQUEST)) {
				$ids = $_REQUEST['destFolders'];
				if (!is_array($ids)) $ids = array($ids);
			}
			if ($data['id'] > 0) {
				$tmp = $this->fetchScalarAll(sprintf('select folder_id from %s where question_id = %d',$this->m_junction,$data['id']));
				$ids = array_merge($ids,$tmp);
			}
			if (count($ids) > 0) {
				$data['destFolders'] = $ids;
			}
		}
		$data['imagesel_a'] = $data['image1'];
		$data['imagesel_b'] = $data['image2'];
		$data['responses'] = $this->getResponses($data['id']);
		$data['usedBy'] = $this->getUsage($data['id']);
		$form->addData($data);
		$frmFields = $form->buildForm($frmFields);
		$status = true;
		if (count($_POST) > 0 && array_key_exists('addContent',$_POST)) {
			$_POST['imagesel_a'] = $_POST['image1'];
			$_POST['imagesel_b'] = $_POST['image2'];
			$form->addData($_POST);
			if ($form->validate()) {
				$id = $_POST['p_id'];
				foreach($frmFields as $key=>$fld) {
					if (!(array_key_exists('database',$fld) && $fld['database'] == false)) {
						$values[$key] = $form->getData($fld['name']);
					}
				}
				if ($id > 0) {
					$stmt = $this->prepare(sprintf('update %s set %s=? where id = %d',$this->m_content,implode('=?, ',array_keys($values)),$id));
				}
				else {
					$values['created'] = date(DATE_ATOM);
					$stmt = $this->prepare(sprintf('insert into %s(%s) values(%s?)',$this->m_content,implode(', ',array_keys($values)),str_repeat('?,',count($values)-1)));
				}
				$stmt->bindParams(array_merge(array(str_repeat('s', count($values))),array_values($values)));
				$this->beginTransaction();
				if ($stmt->execute()) {
					if ($id == 0) {
						$id = $this->insertId();
						$this->logMessage('addContent',sprintf('setting id to [%d]',$id),1);
						$form->setData('id',$id);
					}
					$destFolders = $_POST['destFolders'];
					if (!is_array($destFolders)) $destFolders = array($destFolders);
					//
					//	delete folders we are no longer a member of
					//
					$status = $status && $this->execute(sprintf('delete from %s where question_id = %d and folder_id not in (%s)',$this->m_junction,$id,implode(',',$destFolders)));
					//
					//	insert new folders
					//
					$new = $this->fetchScalarAll(sprintf('select id from %s where id in (%s) and id not in (select folder_id from %s where question_id = %d and folder_id in (%s))', $this->m_tree, implode(',',$destFolders),$this->m_junction,$id,implode(',',$destFolders)));
					$status = true;
					foreach($new as $key=>$folder) {
						$obj = new preparedStatement(sprintf('insert into %s(question_id,folder_id) values(?,?)',$this->m_junction));
						$obj->bindParams(array('dd',$id,$folder));
						$status = $status && $obj->execute();
					}
					$this->commitTransaction();
					$form->addMessage('Record Updated');
				}
				else {
					$form->addError('An Error Occurred');
					$this->rollbackTransaction();
				}
			}
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

	function deleteContent($fromMain = false) {
		$return = true;
		$form = new Forms();
		if ($this->getAccessLevel() > 3) {
			$this->addError($this->noAccessError());
			return $this->ajaxReturn(array('status'=>'false'));
		}
		if (array_key_exists('p_id',$_REQUEST)) {
			$p_id = $_REQUEST['p_id'];
			$ct = $this->fetchScalar(sprintf('select count(0) from %s where folder_id = %d',$this->m_junction,$p_id));
			if ($ct == 0) {
				$mptt = new mptt($this->m_tree);
				if (!$mptt->delete($p_id,true)) {
					$status = false;
					$this->addError('An error occurred while deleting the record');
				}
				else {
					$status = true;
					$this->addMessage('Record deleted successfully');
					$form->init($this->getTemplate('deleteSuccess'));
				}
			}
			if ($this->isAjax())
				return $this->ajaxReturn(array('status'=>$status ? 'true':'false','html'=>$form->show()));
			elseif ($fromMain)
				return $html;
			else
				return $this->show($html);
		}
	}

	function moduleStatus($fromMain = 0) {
		$_POST = array('showSearchForm'=>1,'opt_id'=>'>','id'=>'0','sortby'=>'id','sortorder'=>'desc','pager'=>$this->m_perrow);
		$msg = "Showing latest questions added";
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
	
	function deleteArticle() {
		$form = new Forms();
		$form->init($this->getTemplate('deleteItem'));
		$flds = $form->buildForm($this->getFields('deleteItem'));
		$used = $this->fetchScalar(sprintf('select count(0) from poll_questions where question_id = %d and deleted = 0',$_REQUEST['a_id']));
		$deleted = $this->fetchScalar(sprintf('select count(0) from poll_questions where question_id = %d and deleted = 1',$_REQUEST['a_id']));
		if (count($_REQUEST) > 0 && $_REQUEST['j_id'] == 0)
			$form->getField('one')->addAttribute('disabled','disabled');
		if ($used > 0) {
			$form->addMessage('Note: This question is still in use');
			$msg = 'Note: This question is still in use';
			$recs = $this->fetchAll(sprintf('select p.* from poll p, poll_questions pq where pq.question_id = %d and pq.deleted = 0 and p.id = pq.poll_id',$_REQUEST['a_id']));
			foreach($recs as $key=>$poll) {
				$msg .= sprintf('<br/>%s',$poll['title']);
			}
			$form->addTag('errorMessage',$msg,false);
		}
		$form->addData($_REQUEST);
		if (count($_REQUEST) > 0 && array_key_exists('deleteItem',$_REQUEST)) {
			if ($form->validate()) {
				$type = $form->getData('action');
				switch($type) {
					case 'cancel':
						return $this->ajaxReturn(array('status'=>'true','code'=>'closePopup();'));
						break;
					case 'all':
						$this->execute(sprintf('delete from %s where question_id = %d',$this->m_junction,$_REQUEST['a_id']));
						if ($used > 0 || $deleted > 0)
							$this->execute(sprintf('update %s set deleted = 1 where id = %d',$this->m_content,$_REQUEST['a_id']));
						else
							$this->execute(sprintf('delete from %s where id = %d',$this->m_content,$_REQUEST['a_id']));
						break;
					case 'one':
						$status = $this->execute(sprintf('delete from %s where id = %d',$this->m_junction,$_REQUEST['j_id']));
						$ct = $this->fetchScalar(sprintf('select count(0) from %s where question_id = %d',$this->m_junction,$_REQUEST['a_id']));
						if ($ct == 0) {
							if ($used > 0 || $deleted > 0)
								$this->execute(sprintf('update %s set deleted = 1 where id = %d',$this->m_content,$_REQUEST['a_id']));
							else
								$this->execute(sprintf('delete from %s where id = %d',$this->m_content,$_REQUEST['a_id']));
						}
						break;
					default:
						break;
				}
				$form->init($this->getTemplate('deleteItemResult'));
			}
		}
		return $this->ajaxReturn(array('status'=>'true','html'=>$form->show()));
	}

	function getResponses($id_parm = null) {
		if (is_null($id_parm)) {
			$id = $_REQUEST['p_id'];
		}
		else $id = $id_parm;
		$answers = $this->fetchAll(sprintf('select * from %s where question_id = %d order by sequence',$this->m_details,$id));
		$form = new Forms();
		$form->init($this->getTemplate('responses'));
		$flds = $form->buildForm($this->getFields('responses'));
		$return = array();
		foreach($answers as $key=>$rec) {
			$form->reset();
			$rec['icon'] = $rec['deleted']?'check':'remove';
			$img = new image();
			$img->addAttribute('src',$rec['image1']);
			$rec['image'] = $img->show();
			if ($ct = $this->fetchScalar(sprintf('select count(0) from %s where answer_id = %d',$this->m_responses,$rec['id'])) > 0) {
				$rec['answered'] = 'check';
				$rec['responses'] = $ct;
			}
			else {
				$rec['answered'] = 'remove';
			}
			$form->addData($rec);
			$return[] = $form->show();
		}
		if (is_null($id_parm))
			return $this->ajaxReturn(array('status'=>true,'html'=>implode('',$return)));
		else
			return implode('',$return);
	}

	function editResponse() {
		$form = new Forms();
		$form->init($this->getTemplate('editResponse'));
		if (!(array_key_exists('r_id',$_REQUEST) && $_REQUEST['r_id'] > 0 && $data = $this->fetchSingle(sprintf('select * from %s where id = %d and question_id = %d', $this->m_details, $_REQUEST['r_id'], $_REQUEST['q_id'])))) {
			$data = array('id'=>0,'question_id'=>$_REQUEST['q_id'],'image1'=>'','image2'=>''); 
		}
		$data['imagesel_c'] = $data['image1'];
		$data['imagesel_d'] = $data['image2'];
		$form->addData($data);
		$frmFields = $form->buildForm($this->getFields('editResponse'));
		$status = true;
		if (count($_POST) > 0 && array_key_exists('editResponse',$_POST)) {
			$_POST['imagesel_c'] = $_POST['image1'];
			$_POST['imagesel_d'] = $_POST['image2'];
			$form->addData($_POST);
			if ($form->validate()) {
				$id = $_POST['r_id'];
				foreach($frmFields as $key=>$fld) {
					if (!(array_key_exists('database',$fld) && $fld['database'] == false)) {
						$values[$key] = $form->getData($fld['name']);
					}
				}
				$valid = true;
				if ($id > 0) {
					$stmt = $this->prepare(sprintf('update %s set %s=? where id = %d',$this->m_details,implode('=?, ',array_keys($values)),$id));
				}
				else {
					$stmt = $this->prepare(sprintf('insert into %s(%s) values(%s?)',$this->m_details,implode(', ',array_keys($values)),str_repeat('?,',count($values)-1),$id));
				}
				if ($valid) {
					$stmt->bindParams(array_merge(array(str_repeat('s', count($values))),array_values($values)));
					$this->beginTransaction();
					if ($stmt->execute()) {
						$this->commitTransaction();
						$form->addMessage('Record Updated');
						$form->init($this->getTemplate('editResponseSuccess'));
					}
					else {
						$form->addError('An Error Occurred');
						$this->rollbackTransaction();
					}
				}
			}
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

	function getUsage($id) {
		$form = new Forms();
		$form->init($this->getTemplate('usage'));
		$frmFields = $form->buildForm($this->getFields('usage'));
		$usage = $this->fetchAll(sprintf('select * from poll where id in (select poll_id from poll_questions where question_id = %d) order by title',$id));
		$return = array();
		foreach($usage as $key=>$rec) {
			$form->reset();
			$form->addData($rec);
			$return[] = $form->show();
		}
		return implode('',$return);
	}

	function editQuestion() {
		$result = '';
		if (array_key_exists('q_id',$_REQUEST)) {
			$result = sprintf('<script type="text/javascript">fnEditArticle(%d)</script>',$_REQUEST['q_id']);
		}
		return $this->show($result);
	}

	function removeResponse() {
		$q_id = $_REQUEST['q_id'];
		$r_id = $_REQUEST['r_id'];
		$status = false;
		$form = new Forms();
		$form->init($this->getTemplate('editResponseSuccess'));
		$form->addTag('question_id',$q_id);
		if ($rec = $this->fetchSingle(sprintf('select * from %s where id = %d and question_id = %d',$this->m_details,$r_id,$q_id))) {
			if ($ct = $this->fetchScalar(sprintf('select count(0) from %s where answer_id = %d',$this->m_responses,$r_id)) > 0) {
				//
				//	question has already been answered so we can't delete it - just flag deleted
				//
				$status = $this->execute(sprintf('update %s set deleted = 1 where id = %d',$this->m_details,$rec['id']));
				$this->addMessage('This question has already been answered so we cannot delete it completely. It will not appear on new polls taken.');
			}
			else {
				$status = $this->execute(sprintf('delete from %s where id = %d',$this->m_details,$rec['id']));
			}
		}
		else $this->addMessage('Could not locate that response');
		return $this->ajaxReturn(array('status'=>$status,'html'=>$form->show()));
	}

	function resortResponses() {
		$src = $_REQUEST['r_id'];
		$dest = $_REQUEST['dest'];
		$status = false;
		if ($src == 0 || $dest < 0) {
			$this->addMessage('Either source or destination was missing');
			return $this->ajaxReturn(array('status' => 'false'));
		}
		$src = $this->fetchSingle(sprintf('select * from %s where id = %d',$this->m_details,$src));
		$sql = sprintf('select * from %s where question_id = %d order by sequence limit %d,1',$this->m_details,$src['question_id'],$dest);
		$dest = $this->fetchSingle($sql);
		$this->logMessage("resortResponses",sprintf("move src [%s] to dest [%s] sql [%s]",print_r($src,true),print_r($dest,true),$sql),2);
		if (count($src) == 0 || count($dest) == 0) {
			$status = false;
			$this->addMessage('Either the source or destination response was not found');
		}
		else {
			//
			//	swap the order of the responses
			//
			$this->logMessage('resortResponses', sprintf('swap the sort order of %d and %d',$src['id'],$dest['id']),2);
			$this->beginTransaction();
			$sql = sprintf('update %s set sequence = %d where id = %s',
				$this->m_details, $src['sequence'] < $dest['sequence'] ? $dest['sequence']+1 : $dest['sequence']-1, $src['id']);
			if ($this->execute($sql)) {
				$this->resequenceResponses($src['question_id']);
				$this->commitTransaction();
				$status = true;
			}
			else {
				$this->rollbackTransaction();
				$status = false;
			}					
		}
		return $this->ajaxReturn(array(
				'status'=>$status?'true':'false'
		));
	}

	function resequenceResponses($question) {
		$curr = 10;
		$recs = $this->fetchAll(sprintf('select * from %s where question_id = %d order by sequence',$this->m_details,$question));
		foreach($recs as $key=>$row) {
			$this->execute(sprintf('update %s set sequence = %d where id = %d',$this->m_details,$curr,$row['id']));
			$curr += 10;
		}
	}
}

?>
