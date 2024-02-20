<?php

class users extends Backend {

	private $m_tree = 'users_roles';
	private $m_content = 'users';
	private $m_junction = 'users_by_module';
	//private $m_role = 'users_by_modulerole';
	private $m_roles = 'users_roles';
	private $m_pagination = 5;
	
	public function __construct() {
		$this->M_DIR = 'backend/modules/users/';
		$this->setTemplates(
			array(
				'main'=>$this->M_DIR.'users.html',
				'form'=>$this->M_DIR.'forms/form.html',
				'showContentTree'=>$this->M_DIR.'forms/contenttree.html',
				'userInfo'=>$this->M_DIR.'forms/userInfo.html',
				'folderProperties'=>$this->M_DIR.'forms/folder.html',
				'showFolderContent'=>$this->M_DIR.'forms/folderContent.html',
				'folderInfo'=>$this->M_DIR.'forms/folderInfo.html',
				'showSearchForm'=>$this->M_DIR.'forms/searchForm.html',
				'addContent'=>$this->M_DIR.'forms/addContent.html',
				'articleList'=>$this->M_DIR.'forms/articleList.html',
				'editItem'=>$this->M_DIR.'forms/editItem.html',
				'hasAccessTo'=>$this->M_DIR.'forms/hasAccessTo.html',
				'header'=>$this->M_DIR.'forms/heading.html',
				'addUser'=>$this->M_DIR.'forms/addUser.html'
			)
		);
		$this->setFields(array(
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
				'options'=>array('method'=>'post','action'=>'/modit/ajax/addContent/users','database'=>false),
				'fname'=>array('type'=>'input','required'=>true),
				'lname'=>array('type'=>'input','required'=>true),
				'email'=>array('type'=>'input','required'=>true,'validation'=>'email'),
				'expires'=>array('type'=>'datepicker','required'=>false,'validation'=>'date'),
				'password'=>array('type'=>'password','required'=>true),
				'enabled'=>array('type'=>'checkbox','required'=>false,'value'=>1),
				'deleted'=>array('type'=>'checkbox','required'=>false,'value'=>1),
				'enableAllModules'=>array('type'=>'checkbox','required'=>false,'database'=>false,'database'=>false,'value'=>1),
				'enableAllRole'=>array('type'=>'select','required'=>true,'sql'=>'select role_id,title from users_roles order by role_id desc','database'=>false),
				'created'=>array('type'=>'timestamp','database'=>false),
				'submit'=>array('type'=>'submitbutton','value'=>'Save','database'=>false),
				'addContent'=>array('type'=>'hidden','value'=>1,'database'=>false)
			),
			'hasAccessTo'=>array(
				'module_id'=>array('name'=>'access[module_id][]','type'=>'select','required'=>true,'sql'=>'select id,title from modules where backend = 1 and enabled = 1 order by title'),
				'role_id'=>array('name'=>'access[role_id][]','type'=>'select','required'=>true,'sql'=>'select role_id, title from users_roles order by title'),
				'deleteRole'=>array('type'=>'tag')
			),
			'showSearchForm'=>array(
				'options'=>array('action'=>'showSearchForm','name'=>'searchForm','id'=>'search_form'),
				'opt_created'=>array('type'=>'select','name'=>'opt_created','lookup'=>'search_options'),
				'opt_email'=>array('type'=>'select','name'=>'opt_email','lookup'=>'search_string'),
				'created'=>array('type'=>'datepicker','required'=>false),
				'email'=>array('type'=>'input','required'=>false),
				'enabled'=>array('type'=>'select','lookup'=>'boolean'),
				'deleted'=>array('type'=>'select','lookup'=>'boolean'),
				'quicksearch'=>array('type'=>'input','name'=>'quicksearch','required'=>false),
				'opt_quicksearch'=>array('type'=>'hidden','value'=>'like'),
				'role'=>array('type'=>'select','required'=>false,'options'=>$this->nodeSelect(0,'users_roles',2,false,false),'reformatting'=>false),
				'sortby'=>array('type'=>'hidden','value'=>'created'),
				'sortorder'=>array('type'=>'hidden','value'=>'desc'),
				'pager'=>array('type'=>'select','required'=>true,'value'=>$this->m_pagination,'lookup'=>'paging','id'=>'pager'),
				//
				//	don't call it just module - interferes with htaccess
				//
				'moduleType'=>array('type'=>'select','required'=>false,'sql'=>'select id,title from modules where enabled = 1 and backend = 1 order by title'),
				'submit'=>array('type'=>'submitbutton','value'=>'Search'),
				'pagenum'=>array('type'=>'hidden','value'=>0),
				'showSearchForm'=>array('type'=>'hidden','value'=>1)
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
			'articleList' => array(
				'id'=>array('type'=>'tag'),
				'fname'=>array('type'=>'tag'),
				'lname'=>array('type'=>'tag'),
				'email'=>array('type'=>'tag'),
				'created'=>array('type'=>'datestamp','mask'=>'d-M-Y h:m:i'),
				'enabled'=>array('type'=>'booleanIcon'),
				'deleted'=>array('type'=>'booleanIcon'),
				'expires'=>array('type'=>'datestamp','mask'=>'d-M-Y','suppressNull'=>true)
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

	function showContentTree() {
		$form = new Forms();
		$form->init($this->getTemplate('showContentTree'),array());
		$form->addTag('tree',$this->buildTree($this->m_tree, array(), "ajaxBuild", array(0=>"<ol>%s</ol>",1=>"<li class='collapsed'>%s%s</li>",3=>"")),false);
		if ($this->isAjax())
			return $this->ajaxReturn(array('status'=>'true','html'=>$form->show()));
		else
			return $form->show();
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

	function showPageContent($fromMain = false) {
		$p_id = array_key_exists('p_id',$_REQUEST) ? $_REQUEST['p_id'] : 0;
		$form = new Forms();
		if ($p_id > 0 && $data = $this->fetchSingle(sprintf('select * from %s where id = %d',$this->m_tree,$p_id))) {
			$form->init($this->getTemplate('showFolderContent'),array('name'=>'showFolderContent'));
			$frmFields = $form->buildForm($this->getFields('showFolderContent'));
			if (array_key_exists('pagenum',$_REQUEST)) 
				$pageNum = $_REQUEST['pagenum'];
			else
				$pageNum = 1;	// no 0 based calcs
			if ($pageNum <= 0) $pageNum = 1;
			$perPage = $this->m_pagination;
			if (array_key_exists('pager',$_REQUEST)) $perPage = $_REQUEST['pager'];
			$count = $this->fetchScalar(sprintf('select count(n.id) from %s n where n.id in (select f.user_id from %s f where f.role_id = %d)', $this->m_content, $this->m_junction, $_REQUEST['p_id']));
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
			$sql = sprintf('select * from %s u left join %s f on u.id = f.user_id where f.role_id = %d order by %s %s limit %d,%d',  $this->m_content, $this->m_junction, $_REQUEST['p_id'],$sortby, $sortorder, $start,$perPage);
			$users = $this->fetchAll($sql);
			$this->logMessage('showPageContent', sprintf('sql [%s], records [%d]',$sql, count($users)), 2);
			$articles = array();
			foreach($users as $article) {
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

	function showSearchForm($fromMain = false, $msg = '') {
		$form = new Forms();
		$form->init($this->getTemplate('showSearchForm'),array('name'=>'search_form','persist'=>true));
		$frmFields = $form->buildForm($this->getFields('showSearchForm'));
		$this->logMessage("showSearchForm",sprintf("this [%s] fromMain [$fromMain]",print_r($this,true)),1);
		if (count($_POST) == 0)
			if (array_key_exists('formData',$_SESSION) && array_key_exists('userSearchForm', $_SESSION['formData']))
				$_POST = $_SESSION['formData']['userSearchForm'];
			else
				$_POST = array('showSearchForm'=>1,'deleted'=>0);
		if (count($_POST) > 0 && array_key_exists('showSearchForm',$_POST)) {
			if ((!array_key_exists('deleted',$_POST)) || strlen($_POST['deleted']) == 0) $_POST['deleted'] = 0;
			$form->addData($_POST);
			if ($form->validate()) {
				$_SESSION['formData']['userSearchForm'] = $form->getAllData();
				$srch = array();
				foreach($frmFields as $key=>$value) {
					switch($key) {
						case 'quicksearch':
							if (array_key_exists('opt_'.$key,$_POST) && strlen($_POST['opt_quicksearch']) > 0 && $value = $form->getData($key)) {
								if ($_POST['opt_quicksearch'] == 'like' && strpos($value,'%',0) === false) {
									$value = '%'.$value.'%';
								}
								$tmp = array();
								$tmp[] = sprintf(' fname %s "%s"',$_POST['opt_quicksearch'],$this->escape_string($value));
								$tmp[] = sprintf(' lname %s "%s"',$_POST['opt_quicksearch'],$this->escape_string($value));
								$tmp[] = sprintf(' email %s "%s"',$_POST['opt_quicksearch'],$this->escape_string($value));
								$srch[] = sprintf('(%s)',implode(' or ',$tmp));
							}
							break;
						case 'fname':
						case 'lname':
						case 'email':
							if (array_key_exists('opt_'.$key,$_POST) && strlen($_POST['opt_'.$key]) > 0 && $value = $form->getData($key)) {
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
						case 'moduleType':
							if (($value = $form->getData($key)) > 0) {
								$srch[] = sprintf(' n.id in (select m.user_id from %s m where module_id = %d)', $this->m_junction, $value);
							}
							break;
						case 'role':
							if (($value = $form->getData($key)) > 0) {
								$srch[] = sprintf(' n.id in (select m.user_id from %s m where m.role_id = %d) ', $this->m_junction, $value);
							}
							break;
						case 'enabled':
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
					if (array_key_exists('pagenum',$_REQUEST))
						$pageNum = $_REQUEST['pagenum'];
					else
						$pageNum = 1;	// no 0 based calcs
					$perPage = $this->m_pagination;
					if (array_key_exists('pager',$_REQUEST)) $perPage = $_REQUEST['pager'];
					$sql = sprintf('select count(n.id) from %s n where 1=1 and %s', $this->m_content, implode(' and ',$srch));
					$count = $this->fetchScalar($sql);
					$this->logMessage("showSearchForm","sql [$sql] count [$count]",2);
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
					$sql = sprintf('select n.* from %s n where 1=1 and %s order by %s %s limit %d,%d',
						 $this->m_content, implode(' and ',$srch),$sortby, $sortorder, $start,$perPage);
					$recs = $this->fetchAll($sql);
					$this->logMessage('showSearchForm', sprintf('sql [%s] records [%d]',$sql,count($recs)), 2);
					$articles = array();
					$frm = new Forms();
					$frm->init($this->getTemplate('articleList'),array());
					$tmp = $frm->buildForm($this->getFields('articleList'));
					foreach($recs as $article) {
						$frm->addData($article);
						$articles[] = $frm->show();
					}
					$form->addTag('articles',implode('',$articles),false);
					$form->addTag('pagination',$pagination,false);
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
		$form->init($this->getTemplate('addContent'),array('name'=>'addContent'));
		$frmFields = $this->getFields('addContent');
		if (!(array_key_exists('a_id',$_REQUEST) && $_REQUEST['a_id'] > 0 && $data = $this->fetchSingle(sprintf('select * from %s where id = %d', $this->m_content, $_REQUEST['a_id'])))) {
			$data = array('id'=>0,'published'=>0,'password'=>''); 
		}

		if (count($_REQUEST) > 0 && array_key_exists('destFolders',$_REQUEST) || $data['id'] > 0) {
			$ids = array();
			if (array_key_exists('destFolders',$_REQUEST)) {
				$ids = $_REQUEST['destFolders'];
				if (!is_array($ids)) $ids = array($ids);
			}
			if ($data['id'] > 0) {
				$sql = sprintf('select j.id from %s j, modules m where j.user_id = %d and m.id = j.module_id order by m.title', $this->m_junction, $data['id']);
				$tmp = $this->fetchScalarAll($sql);
				$this->logMessage("addContent",sprintf("destFolders sql [$sql] count [".count($tmp)."]"),3);
				$ids = array_merge($ids,$tmp);
			}
			if (count($ids) > 0) {
				$data['destFolders'] = $ids;
			}
		}
		if (!array_key_exists('destFolders',$data)) $data['destFolders'] = array();
		//
		//	access levels
		//
		$level = $this->getAccessLevel();

		$subForm = new Forms();
		$subForm->init($this->getTemplate('hasAccessTo'));
		$subFields = $this->getFields('hasAccessTo');
		$tmp = array();
		if ($level >= 4) {
			foreach($subFields as $key=>$fld) {
				$subFields[$key]['disabled'] = true;
			}
		}
		$subFields = $subForm->buildForm($subFields);
		foreach($data['destFolders'] as $key=>$r_id) {
			$tmp[] = $this->showRole($r_id);
		}
		$data['destFolders'] = implode("",$tmp);
		
		//
		//	access levels
		//
		$level = $this->getAccessLevel();
		switch($level) {
			case 1:
				break;	// admin can do anything
			case 2:
			case 3:
			case 4:
			default:
				unset($frmFields['submit']);
				foreach($frmFields as $key=>$fld) {
					$frmFields[$key]['disabled'] = true;
				}
				if ($data['id'] == 0) $this->noAccessError();
				if (array_key_exists('addContent',$_POST)) unset($_POST['addContent']);
		}
		$frmFields = $form->buildForm($frmFields);
		$form->addData($data);
		$status = 'false';	//assume it failed
		if (count($_POST) > 0 && array_key_exists('addContent',$_POST)) {
			$form->addData($_POST);
			$id = $data['id'];
			$status = $form->validate();
			if ($id == 0) {
				//
				//	check for dupe emails
				//
				$ct = $this->fetchScalar(sprintf('select count(0) from users where email = "%s" and deleted = 0',$_POST['email']));
				if ($ct > 0) {
					$status = false;
					$this->addError('This email address is already in use');
				}
			}
			if (!(array_key_exists('access',$_REQUEST) || array_key_exists('enableAllModules',$_POST))) {
				$this->addError('At least 1 role must be defined');
				$status = false;
			}
			if ($status) {
				if ($data['password'] == $_REQUEST['password'])
					unset($frmFields['password']);
				else
					$form->setData('password',SHA1($form->getData('password')));
				foreach($frmFields as $key=>$fld) {
					if (!(array_key_exists('database',$fld) && $fld['database'] == false)) {
						$flds[$fld['name']] = $form->getData($fld['name']);
					}
				}
				$status = true;
				if ($id == 0) {
					$flds['created'] = date('c');
					$stmt = $this->prepare(sprintf('insert into %s(%s) values(%s)', $this->m_content, implode(',',array_keys($flds)), str_repeat('?,', count($flds)-1).'?'));
					$stmt->bindParams(array_merge(array(str_repeat('s', count($flds))),array_values($flds)));
				}
				else {
					$stmt = $this->prepare(sprintf('update %s set %s where id = %d', $this->m_content, implode('=?,',array_keys($flds)).'=?',$data['id']));
					$stmt->bindParams(array_merge(array(str_repeat('s', count($flds))),array_values($flds)));
					$this->addMessage('updating record');
				}
				$this->beginTransaction();
				if ($status = $status && $stmt->execute()) {
					if ($id == 0) $id = $this->insertId();
					if (array_key_exists('enableAllModules',$_POST) && $_POST['enableAllModules'] == 1) {
						$status = $this->execute(sprintf('delete from %s where user_id = %d',$this->m_junction,$id));
						$modules = $this->fetchAll('select * from modules where backend = 1 and enabled = 1 and admin = 0');
						foreach($modules as $module) {
							$stmt = $this->prepare(sprintf('insert into %s(user_id,module_id,role_id) values(?,?,?)', $this->m_junction));
							$stmt->bindParams(array('ddd',$id,$module['id'],$_POST['enableAllRole']));
							$status = $status && $stmt->execute();
						}
						unset($_POST['access']);
					} 
					else {
						if (array_key_exists('deletem',$_POST)) {
							$status = $status && $this->execute(sprintf('delete from %s where id in (%s)',$this->m_junction,implode(",",$_POST['deletem'])));
						}
						$roles = $_POST['access'];
						if (array_key_exists('deleteRole',$roles)) {
							foreach($roles['deleteRole'] as $key=>$value) {
								$this->logMessage("addContent",sprintf("deleting role id ".$value),2);
								$status = $status && $this->execute(sprintf('delete from users_by_module where id = %d',$value));
							}
						}
						if (array_key_exists('ids',$roles)) {
							foreach($roles['ids'] as $key=>$value) {
								if ($value == 0) {
									if ($this->fetchSingle(sprintf('select * from users_by_module where user_id = %d and module_id = %d',$id,$roles['module_id'][$key]))) {
										$status = false;
										$this->addMessage('A Module can have only 1 Role');
									}
									else {
										$this->logMessage("addContent",sprintf("adding role for module ".$roles['module_id'][$key]),2);
										$obj = new preparedStatement('insert into users_by_module(user_id,module_id,role_id) values(?,?,?)');
										$obj->bindParams(array('ddd',$id,$roles['module_id'][$key],$roles['role_id'][$key]));
										$status = $status && $obj->execute();
									}
								}
								else {
									$this->logMessage("addContent",sprintf("updating role id ".$value),2);
									$status = $status && $this->execute(sprintf('update users_by_module set module_id = %d, role_id = %d where id = %d',$roles['module_id'][$key],$roles['role_id'][$key],$value));
								}
							}
						}
					}
					if ($status) {
						$this->commitTransaction();
						return $this->ajaxReturn(array(
							'status' => 'true',
							'url' => sprintf('/modit/users')
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
	
	function deleteArticle() {
		if (array_key_exists('j_id',$_REQUEST)) {
			$level = $this->getAccessLevel();
			if ($level >= 4) return $this->ajaxReturn(array('status'=>'false','messages'=>$this->noAccessError()));
			$id = $_REQUEST['j_id'];
			$this->logMessage('deleteArticle', sprintf('deleting user %d',$id), 2);
			$this->beginTransaction();
			$this->execute(sprintf('update %s set deleted = 1 where id = %d',$this->m_content,$id));
			$this->commitTransaction();
			return $this->ajaxReturn(array('status'=>'true'));
		}
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

	function edit() {
		if (array_key_exists('u_id',$_REQUEST) && $data = $this->fetchSingle(sprintf('select * from %s where id = %d',$this->m_content,$_REQUEST['u_id']))) {
			$f = new Forms();
			$f->init($this->getTemplate('editItem'));
			$f->addData($data);
			return $this->show($f->show());
		}
		else
			return $this->show();
	}

	function addUser($fromMain = false) {
		$level = $this->getAccessLevel();
		if ($level == 0 || $level > 3) {
			$this->noAccessError();
			return $this->show();
		}
		$form = new Forms();
		$form->init($this->getTemplate('addUser'));
		if ($fromMain)
			return $form->show();
		else
			return $this->show($form->show());
	}

	function showRole($r_id = null) {
		if (is_null($r_id))
			$tmp = array_key_exists('r_id',$_REQUEST) ? $_REQUEST['r_id'] : 0;
		else
			$tmp = $r_id;
		if (!($data = $this->fetchSingle(sprintf('select *, id as deleteRole from users_by_module where id = %d',$tmp))))
			$data = array('deleteRole'=>0);
		$form = new Forms();
		$form->init($this->getTemplate('hasAccessTo'));
		$flds = $form->buildForm($this->getFields('hasAccessTo'));
		$form->addData($data);
		if (is_null($r_id))
			return $this->ajaxReturn(array('status'=>'true','html'=>$form->show()));
		else
			return $form->show();
	}

	function hasFunctionAccess($method) {
		if (parent::hasFunctionAccess($method)) return true;
		return true;
	}

	function getHeader() {
		$form = new Forms();
		$form->init($this->getTemplate('header'));
		$flds = $form->buildForm($this->getFields('showSearchForm'));
		if (count($_POST) > 0 && array_key_exists('showSearchForm',$_POST))
			$form->addData($_POST);
		return $form->show();
	}

	function moduleStatus($fromMain = 0) {
		$_POST = array('showSearchForm'=>1,'enabled'=>1,'sortby'=>'lname','sortorder'=>'asc','pager'=>$this->m_pagination);
		$msg = "Showing latest Administrators added";
		$result = $this->showSearchForm($fromMain,$msg);
		return $result;
	}

}

?>