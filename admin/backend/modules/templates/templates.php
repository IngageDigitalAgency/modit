<?php

class templates extends Backend {

	private $m_tree = 'template_folders';
	private $m_content = 'templates';
	private $m_junction = 'templates_by_folder';
	private $m_pagination = 5;
		
	public function __construct() {
		$this->M_DIR = 'backend/modules/templates/';
		$this->setTemplates(
			array(
				'main'=>$this->M_DIR.'templates.html',
				'showTemplate'=>$this->M_DIR.'forms/showTemplate.html',
				'showContentTree'=>$this->M_DIR.'forms/contenttree.html',
				'showFolderContent'=>$this->M_DIR.'forms/folderContent.html',
				'folderInfo'=>$this->M_DIR.'forms/folderInfo.html',
				'folderProperties'=>$this->M_DIR.'forms/folder.html',
				'articleList'=>$this->M_DIR.'forms/articleList.html',
				'templateProperties'=>$this->M_DIR.'forms/showTemplate.html',
				'templateList'=>$this->M_DIR.'forms/templateList.html',
				'addContent'=>$this->M_DIR.'forms/addContent.html',
				'templateListRow'=>$this->M_DIR.'forms/templateListRow.html',
				'templateInfo'=>$this->M_DIR.'forms/templateInfo.html',
				'copyTemplate'=>$this->M_DIR.'forms/copyTemplate.html',
				'editorForm'=>$this->M_DIR.'forms/editorForm.html',
				'header'=>$this->M_DIR.'forms/heading.html',
				'templatePages'=>$this->M_DIR.'forms/templatePages.html'
			)
		);
		$this->setFields(array(
			'showTemplate'=>array(
				'versions'=>array('type'=>'select','required'=>true,'id'=>'versionSelector')
			),
			'folderProperties' => array(
				'options'=>array(
					'action'=>'/modit/templates/showPageProperties',
					'method'=>'post',
					'name'=>'folderData'
				),
				'title'=>array('type'=>'textfield','required'=>true,'prettyName'=>'Title'),
				'showFolderProperties'=>array('type'=>'hidden','value'=>1, 'database'=>false),
				'p_id'=>array('type'=>'select','required'=>true,'database'=>false,'optionslist'=>array('table'=>$this->m_tree,'root'=>0,'indent'=>2,'inclusive'=>true)),
				'notes'=>array('type'=>'textarea','required'=>false,'class'=>'mceNoEditor'),
				'id'=>array('type'=>'hidden', 'database'=>false),
				'submit'=>array('type'=>'submitbutton','value'=>'Save','database'=>false)
			),
			'articleList' => array(
				'id'=>array('type'=>'tag'),
				'title'=>array('type'=>'tag'),
				'created'=>array('type'=>'datestamp'),
				'enabled'=>array('type'=>'booleanIcon'),
				'deleted'=>array('type'=>'booleanIcon'),
				'versions'=>array('type'=>'select','class'=>'editSelector'),
				'pages'=>array('type'=>'select','class'=>'pageSelector')
			),
			'showFolderContent'=>array(
				'options'=>array('action'=>'showPageContent'),
				'description'=>array('type'=>'tag','reformatting'=>false),
				'image'=>array('type'=>'image','unknown'=>true),
				'rollover_image'=>array('type'=>'image','unknown'=>true),
				'sortby'=>array('type'=>'hidden','value'=>'sequence'),
				'sortorder'=>array('type'=>'hidden','value'=>'asc'),
				'pagenum'=>array('type'=>'hidden','value'=>1),
				'pager'=>array('type'=>'select','required'=>true,'value'=>$this->m_pagination,'lookup'=>'paging','id'=>'pager'),
				'showFolderContent'=>array('type'=>'hidden','value'=>1)
			),
			'folderInfo' => array(
				'title'=>array('type'=>'tag'),
				'alternate_title'=>array('type'=>'tag'),
				'notes'=>array('type'=>'tag','reformatting'=>false),
				'description'=>array('type'=>'tag','reformatting'=>false),
				'image' => array('type'=>'image','unknown'=>true),
				'rollover_image'=>array('type'=>'image','unknown'=>true)
			),
			'templatePages'=>array(
				'title'=>array('type'=>'tag')
			),
			'copyTemplate'=>array(
				'options'=>array('name'=>'copyTemplate','method'=>'post'),
				't_id'=>array('type'=>'hidden'),
				'old_name'=>array('type'=>'tag'),
				'special_processing'=>array('type'=>'select','lookup'=>'specialProcessing','required'=>false),
				'destFolders'=>array('name'=>'destFolders','type'=>'select','required'=>true,'id'=>'destFolders','database'=>false,'options'=>$this->nodeSelect(0, 'template_folders', 2, true, false),'reformatting'=>false,'prettyName'=>'Member of'),
				'title'=>array('type'=>'input','required'=>true,'prettyName'=>'Title'),
				'notes'=>array('type'=>'textarea','required'=>false),
				'submit'=>array('type'=>'submitButton','value'=>'Copy Template'),
				'copyTemplate'=>array('type'=>'hidden','value'=>1)
			),
			'templateInfo'=>array(
				'title'=>array('type'=>'tag'),
				'notes'=>array('type'=>'tag','reformatting'=>false),
				'versions'=>array('type'=>'select','id'=>'templateVersions')
			),
			'templateListRow'=>array(),
			'templateList'=>array(),
			'addContent'=>array(
				'options'=>array('method'=>'post','action'=>'/modit/ajax/addContent/templates'),
				'title'=>array('type'=>'textfield','required'=>true,'prettyName'=>'Title'),
				'special_processing'=>array('type'=>'select','lookup'=>'specialProcessing','required'=>false),
				'html'=>array('type'=>'textarea','required'=>true,'reformatting'=>true,'class'=>'mceNoEditor','prettyName'=>'HTML'),
				'notes'=>array('type'=>'textarea','required'=>false,'reformatting'=>true),
				'version'=>array('type'=>'tag','database'=>false),
				'enabled'=>array('type'=>'checkbox','value'=>1),
				'addContent'=>array('type'=>'hidden','value'=>1,'database'=>false),
				'template_id'=>array('type'=>'hidden'),
				'destFolders'=>array('name'=>'destFolders','type'=>'select','required'=>true,'id'=>'destFolders','database'=>false,'options'=>$this->nodeSelect(0, 'template_folders', 2, true, false),'reformatting'=>false,'prettyName'=>'Member of'),
				'submit'=>array('type'=>'submitbutton','value'=>'Save','database'=>false)
			),
			'showSearchForm'=>array(
				'options'=>array('action'=>'showSearchForm'),
				'opt_created'=>array('type'=>'select','name'=>'opt_created','lookup'=>'search_options','required'=>true),
				'opt_expires'=>array('type'=>'select','name'=>'opt_expires','lookup'=>'search_options','required'=>true),
				'opt_name'=>array('type'=>'select','name'=>'opt_name','lookup'=>'search_options','required'=>true),
				'name'=>array('type'=>'input','required'=>false),
				'created'=>array('type'=>'datepicker','required'=>false,'prettyName'=>'Created'),
				'expires'=>array('type'=>'datepicker','required'=>false,'prettyName'=>'Expires'),
				'published'=>array('type'=>'select','required'=>false,'lookup'=>'boolean'),
				'enabled'=>array('type'=>'select','required'=>false,'lookup'=>'boolean'),
				'showSearchForm'=>array('type'=>'hidden','value'=>1),
				'pagenum'=>array('type'=>'hidden','value'=>1),
				'sortby'=>array('type'=>'hidden','value'=>'created'),
				'sortorder'=>array('type'=>'hidden','value'=>'desc'),
				'quicksearch'=>array('type'=>'input','name'=>'quicksearch','required'=>false),
				'opt_quicksearch'=>array('type'=>'hidden','value'=>'like'),
				'submit'=>array('type'=>'submitbutton','value'=>'Search')
			),
			'showPageContent'=>array(
				'options'=>array('action'=>'showPageContent'),
				'description'=>array('type'=>'tag','reformatting'=>false),
				'image'=>array('type'=>'image','unknown'=>true),
				'rollover_image'=>array('type'=>'image','unknown'=>true),
				'sortby'=>array('type'=>'hidden','value'=>'created'),
				'sortorder'=>array('type'=>'hidden','value'=>'desc'),
				'pagenum'=>array('type'=>'hidden','value'=>1),			
				'showPageContent'=>array('type'=>'hidden','value'=>1)
			),
			'main' => array(
				'test'=>array('type'=>'tag')
			),
			'form' => array(),
			'templateProperties' => array(
				'options'=>array(
					'action'=>'/modit/templates/showPageProperties',
					'method'=>'post'
				),
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
			//$injector = $this->showTemplate(0,true);
			$injector = $this->moduleStatus();
		}
		$form->addTag('injector', $injector, false);
		return $form->show();
	}

	function showTemplate($id = null,$fromMain = false) {
		if ($id == null || $id == 0) {
			if (array_key_exists('t_id',$_REQUEST) && $_REQUEST['t_id'] > 0) {
				$id = $_REQUEST['t_id'];
			}
			else
				$id = $this->fetchScalar('select template_id from templates where deleted = 0 order by id desc limit 1');
		}
		$form = new Forms();
		$form->init($this->getTemplate('showTemplate'));
		if ($this->areEditing('T_'.$id) == 'true') {
			$form->addTag('errorMessage','Unsaved edits exist for this template');
		}
		$flds = $this->getFields('showTemplate');
		$flds['editButton'] = array('type'=>'button','value'=>'Edit','onclick'=>'setEditMode("edit");return false;');
		$flds['delete'] = array('type'=>'button','value'=>'Delete this Version','onclick'=>'setEditMode("delete");return false;');
		$form->addTag('page_type','t');
		$form->addTag('page_id',$id);
		$flds = $form->buildForm($flds);
		//if ($rec = $this->fetchSingle(sprintf('select * from templates t where deleted = 0 and t.template_id = %d and t.version = (select max(version) from templates where template_id = t.template_id)',$id)))
		if ($rec = $this->fetchSingle(sprintf('select * from templates t where t.id = %d',$id))) {
			$form->addData($rec);
			$form->setData('versions',$id);
			$form->getField('versions')->addAttribute('sql',sprintf('select id, concat(version,", ",date_format(created,"%%d-%%b-%%Y %%T")) from %s where template_id = %d and deleted = 0 order by id desc',$this->m_content,$rec['template_id']));
		}
		if ($this->isAjax()) {
			return $this->ajaxReturn(array('status'=>'true','html'=>$form->show()));
		}
		if ($fromMain) 
			return $form->show();
		else
			$this->show($form->show());
	}

	function showFolderProperties($fromMain = false) {
		$result = array();
		$return = 'true';
		if (!(array_key_exists('id',$_REQUEST) && $data = $this->fetchSingle(sprintf('select * from %s where id = %d',$this->m_tree, $_REQUEST['id']))))
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
		$frmFlds = $form->buildForm($this->getFields('folderProperties'));
		$form->addData($data);
		if (count($_POST) > 0 && array_key_exists('showFolderProperties',$_POST)) {
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
						//$this->logMessage('showPageProperties', sprintf('moving [%d] to [%d] posted[%d]',$data['id'],$p['id'], $_POST['p_id']), 1);
						$mptt = new mptt($this->m_tree);
						$mptt->move($data['id'], $_POST['p_id']);
					}
				}
				foreach($frmFlds as $key=>$fld) {
					if (!(array_key_exists('database',$fld) && $fld['database'] == false)) {
						$values[] = $_REQUEST[$fld['name']];
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
								'url'=>'/modit/templates?p_id='.$data['id']
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

	function showPageProperties($fromMain = false) {
		$result = array();
		$return = 'true';
		if (!(array_key_exists('id',$_REQUEST) && $data = $this->fetchSingle(sprintf('select * from %s where deleted = 0 and id = %d',$this->m_content,$_REQUEST['id']))))
			$data = array('enabled'=>1,'id'=>0,'t_id'=>0);
		$form = new Forms();
		$form->init($this->getTemplate('templateProperties'),array('name'=>'templateProperties'));
		$frmFlds = $form->buildForm($this->getFields('templateProperties'));
		$form->addData($data);
		if (count($_POST) > 0 && array_key_exists('showPageProperties',$_POST)) {
			$form->addData($_POST);
			$valid = $form->validate();
			if ($valid) {
				if (array_key_exists('options',$frmFlds)) unset($frmFlds['options']);
				$values = array();
				$flds = array();
				//
				//	we always do an update - increment the version #
				//
				foreach($frmFlds as $key=>$fld) {
					if (!(array_key_exists('database',$fld) && $fld['database'] == false)) {
						$values[] = $_REQUEST[$fld['name']];
						if ($data['id'] > 0)
							$flds[] = sprintf('%s = ?',$fld['name']);
						else
							$flds[] = $fld['name'];
					}
				}
				$this->beginTransaction();
				$stmt = $this->prepare(sprintf('update %s set %s where id = %d',$this->m_tree, implode(',',$flds),$data['id']));
				$stmt->bindParams(array_merge(array(str_repeat('s', count($flds))),$values));
				$status = false;
				if ($stmt->execute()) {
					$status = true;
				}
				if ($status) {
					$this->commitTransaction();
					if ($this->isAjax()) {
						$this->addMessage('Record successfully added');
						return $this->ajaxReturn(array(
								'status'=>'true',
								'html'=>'',
								'url'=>'/modit/templates?t_id='.$data['id']
						));
					}
				} else {
					$this->rollbackTransaction();
					$this->addError('Error occurred');
					$form->addTag('errorMessage',$this->showErrors(),false);
					if ($this->isAjax()) {
						return $this->ajaxReturn(array(
								'status'=>'false',
								'html'=>$form->show()
						));
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
				if (count($_REQUEST) > 0 && array_key_exists('t_id',$_REQUEST)) {
					$expanded = $_REQUEST['t_id'] == $data['id'] ?  'active' : '';
				}
				else $expanded='';
				if (count($submenu) > 0) {
					$return = array('value'=>sprintf('<div class="wrapper"><div class="spacer"><a href="#" class="toggler" onclick="toggle(this);return false;">+</a></div>&nbsp;<a href="#" id="li_%d" class="%s icon_folder info">%s</a></div>',$data['id'], $expanded, htmlspecialchars($data['title'])),'submenu'=>$submenu);
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

	function getTemplateInfo($fromMain = false) {
		if (array_key_exists('t_id',$_REQUEST)) {
			if ($data = $this->fetchSingle(sprintf('select * from %s where deleted = 0 and id = %d', $this->m_content, $_REQUEST['t_id']))) {
				$form = new Forms();
				$data['notes'] = nl2br($data['notes']);
				$template = 'templateInfo';
				$frmFields = $this->getFields($template);
				$form->init($this->getTemplate($template), array());
				$frmFields['versions']['sql'] = sprintf('select id, concat(version,", ",date_format(created,"%%d-%%b-%%Y %%T")) as value from templates where template_id = %d and deleted = 0 order by version desc',$data['template_id']);
				$data['versions'] = $_REQUEST['t_id'];
				$frmFields = $form->buildForm($frmFields);
				$form->addData($data);
				$form->addTag('currentId',$_REQUEST['t_id']);
				$pages = $this->fetchAll(sprintf('select * from content where id in (select content_id from pages where deleted = 0 and template_id = %d)',$data['template_id']));
				$subf = new Forms();
				$subf->init($this->getTemplate('templatePages'));
				$subFlds = $subf->buildForm($this->getFields('templatePages'));
				$pageList = array();
				foreach($pages as $page) {
					$subf->addData($page);
					$pageList[] = $subf->show();
				}
				$form->addTag('pages',implode('',$pageList),false);
				if ($this->isAjax())
					return $this->ajaxReturn(array('status'=>'true','html'=>$form->show()));
				elseif ($fromMain)
					return $form->show();
				else
					return $this->show($form->show());
			}
			else {
				$this->addError("We couldn't find that version");
				return $this->ajaxReturn(array('status'=>'false'));
			}
		}
	}

	function getFolderInfo($fromMain = false) {
		$form = new Forms();
		$template = 'folderInfo';
		$form->init($this->getTemplate($template), array());
		$frmFields = $form->buildForm($this->getFields($template));
		if (array_key_exists('t_id',$_REQUEST) && $data = $this->fetchSingle(sprintf('select * from %s where id = %d',$this->m_tree,$_REQUEST['t_id']))) {
			$data['notes'] = nl2br($data['notes']);
			$form->addData($data);
		}
		if ($this->isAjax())
			return $this->ajaxReturn(array('status'=>'true','html'=>$form->show()));
		elseif ($fromMain)
			return $form->show();
		else
			return $this->show($form->show());
	}

	function showPageContent($fromMain = false) {
		$t_id = array_key_exists('t_id',$_REQUEST) ? $_REQUEST['t_id'] : 0;
		$this->logMessage('showPageContent',sprintf('fromMain [%s] t_id [%s]',$fromMain,$t_id),1);
		$form = new Forms();
		if ($t_id > 0 && $data = $this->fetchSingle(sprintf('select * from %s where id = %d', $this->m_tree, $t_id))) {
			$form->init($this->getTemplate('showFolderContent'),array('name'=>'showFolderContent'));
			$frmFields = $form->buildForm($this->getFields('showFolderContent'));
			if (array_key_exists('pagenum',$_REQUEST)) 
				$pageNum = $_REQUEST['pagenum'];
			else
				$pageNum = 1;	// no 0 based calcs
			if ($pageNum <= 0) $pageNum = 1;
			$perPage = $this->m_pagination;
			if (array_key_exists('pager',$_REQUEST)) $perPage = $_REQUEST['pager'];
			$count = $this->fetchScalar(sprintf('select count(distinct(n.template_id)) from %s n where n.template_id in (select f.template_id from %s f where f.folder_id = %d)', $this->m_content, $this->m_junction, $_REQUEST['t_id']));
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
			$sql = sprintf('select t.*, f.id as j_id from %s t, %s f where t.template_id = f.template_id and f.folder_id = %d and t.version = (select max(version) from templates t1 where t1.template_id = t.template_id and t1.deleted = 0) and t.deleted = 0 order by %s %s limit %d,%d',  $this->m_content, $this->m_junction, $_REQUEST['t_id'],$sortby, $sortorder, $start,$perPage);
			$templates = $this->fetchAll($sql);
			$this->logMessage('showPageContent', sprintf('sql [%s], records [%d]',$sql, count($templates)), 2);
			$articles = array();
			$frm = new Forms();
			$frm->init($this->getTemplate('articleList'),array());
			$tmp = $frm->buildForm($this->getFields('articleList'));
			foreach($templates as $article) {
				//$versions = $this->fetchAll(sprintf('select id,version from %s where template_id = %d order by version desc',$this->m_content,$article['template_id']));
				//foreach($versions as $tmp) {
				//	$opt[$tmp['id']] = $tmp['version'];
				//}
				$frm->getField('versions')->setOptions(array());
				$frm->getField('pages')->setOptions(array());
				$frm->getField('versions')->addAttribute('sql',sprintf('select id,concat(version,", ",date_format(created,"%%d-%%b-%%Y %%T")) as version_date from %s where template_id = %d and deleted = 0 order by version desc',$this->m_content,$article['template_id']));
				$frm->getField('pages')->addAttribute('sql',sprintf('select id,title from content c where c.id in (select content_id from pages where deleted = 0 and template_id = %d)',$article['template_id']));
				$frm->addData($article);
				$articles[] = $frm->show();
			}
			$this->logMessage('showPageContent',sprintf('articles [%s]',print_r($articles,true)),2);
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

	function getHeader() {
		$form = new Forms();
		$form->init($this->getTemplate('header'));
		$flds = $form->buildForm($this->getFields('showSearchForm'));
		if (count($_POST) > 0 && array_key_exists('showSearchForm',$_POST))
			$form->addData($_POST);
		return $form->show();
	}

	function addContent($fromMain = false) {
		$form = new Forms();
		$form->init($this->getTemplate('addContent'),array('name'=>'addContent'));
		$frmFields = $form->buildForm($this->getFields('addContent'));
		if (!(array_key_exists('t_id',$_REQUEST) && $_REQUEST['t_id'] > 0 && $data = $this->fetchSingle(sprintf('select * from %s where deleted = 0 and id = %d', $this->m_content, $_REQUEST['t_id'])))) {
			$data = array('id'=>0,'published'=>false,'template_id'=>$this->fetchScalar('select max(template_id) from templates')+1);
		}
		$data['destFolders'] = $this->fetchScalar(sprintf('select folder_id from templates_by_folder where template_id = %d',$data['template_id']));
		$form->addData($data);
		$status = 'true';	//assume it failed
		if (count($_POST) > 0 && array_key_exists('addContent',$_POST)) {
			$status = false;
			$form->addData($_POST);
			if ($form->validate()) {
				$id = $_POST['t_id'];
				unset($frmFields['options']);
				foreach($frmFields as $key=>$fld) {
					if (!(array_key_exists('database',$fld) && $fld['database'] == false)) {
						$values[] = $form->getData($fld['name']);//$_REQUEST[$fld['name']];
						$flds[$fld['name']] = $form->getData($fld['name']);
					}
				}
				//
				//	always add - increment the version #
				//
				$adding = true;
				if ($data['id'] == 0)
					$data['template_id'] = $this->fetchScalar('select max(template_id) from templates')+1;
				$flds['created'] = date('c');
				$flds['version'] = $this->fetchScalar(sprintf('select max(version) from templates where template_id = %d',$data['template_id']))+1;
				$stmt = $this->prepare(sprintf('insert into %s(%s) values(%s)', $this->m_content, implode(',',array_keys($flds)), str_repeat('?,', count($flds)-1).'?'));
				$stmt->bindParams(array_merge(array(str_repeat('s', count($flds))),array_values($flds)));
				$this->beginTransaction();
				if ($status = $stmt->execute()) {
					$newId = $this->insertId();
					$this->logMessage('module',sprintf('testing template_by_folder [%d] [%d]',$data['id'],$_REQUEST['destFolders']),1);
					if ($data['id'] == 0) {
						$this->logMessage('module',sprintf('adding template_by_folder [%d] [%d]',$data['template_id'],$_REQUEST['destFolders']),1);
						$this->execute(sprintf('insert into %s(template_id,folder_id) values(%d,%d)',$this->m_junction,$data['template_id'],$_REQUEST['destFolders']));
					}
					else {
						$this->execute(sprintf('update %s set folder_id = %d where template_id = %d',$this->m_junction,$_POST['destFolders'],$data['template_id']));
					}
					$sql = sprintf('select * from modules_by_page where page_type = "T" and page_id = %d',$data['id']);
					$modules = $this->fetchAll($sql);
					$this->logMessage("addContent",sprintf("copy modules from [%d] to [%d] sql [%s] count[%d]",$data['id'],$newId,$sql,count($modules)),3);
					foreach($modules as $module) {
						$module['page_id'] = $newId;
						unset($module['id']);
						$stmt = $this->prepare(sprintf('insert into modules_by_page(%s) values(?%s)',implode(',',array_keys($module)),str_repeat(',?',count($module)-1)));
						$stmt->bindParams(array_merge(array(str_repeat('s', count($module))),array_values($module)));
						$status = $status && $stmt->execute();
					}
				}
				if ($status) {
					$this->commitTransaction();
					return $this->ajaxReturn(array(
						'status' => 'true',
						'url' => sprintf('/modit/templates?t_id=%d',$newId)
					));
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
			$tmp = $form->show(false);
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

	function templateList() {
		$f = new Forms();
		$f->init($this->getTemplate('templateList'));
		$flds = $f->buildForm($this->getFields('templateList'));
		$recs = $this->fetchAll('select t.* from templates t where t.deleted = 0 and t.id = (select max(id) from templates t1 where t1.template_id=t.template_id)');
		if (array_key_exists('t_id',$_REQUEST) && $_REQUEST['t_id'] > 0) {
			$active = $_REQUEST['t_id'];
		}
		else $active = count($recs) > 0 ? $recs[0]['id'] : 0;
		$f->addTag('active',$active);
		$this->logMessage('templateList',sprintf('record count [%d]',count($recs)),2);
		$output = array();
		$subf = new Forms();
		$subf->init($this->getTemplate('templateListRow'));
		$flds = $subf->buildForm($this->getFields('templateListRow'));
		foreach($recs as $key=>$template) {
			$subf->addData($template);
			if ($template['id'] == $active) 
				$subf->addTag('active','active');
			else
				$subf->addTag('active','');
			$output[] = $subf->show();
		}
		$f->addTag('templateList',implode('',$output),false);
		if ($this->isAjax())
			return $this->ajaxReturn(array('status'=>'true','html'=>$f->show()));
		else
			return $this->show();
	}

	function copyTemplate() {
		if (array_key_exists('t_id',$_REQUEST) && ($data = $this->fetchSingle(sprintf('select * from templates where id = %d',$_REQUEST['t_id'])))) {
			$form = new Forms();
			$form->init($this->getTemplate('copyTemplate'));
			$flds = $form->buildForm($this->getFields('copyTemplate'));
			$data['old_name'] = $data['title'];
			$data['t_id'] = $data['id'];
			$form->addData($data);
			if (count($_POST) > 0 && array_key_exists('copyTemplate',$_POST)) {
				$form->addData($_POST);
				if ($form->validate()) {
					$this->beginTransaction();
					$maxId = $this->fetchScalar('select max(template_id) from templates') + 1;
					$newRec = array();
					$newRec['template_id'] = $maxId;
					$newRec['version'] = 1;
					$newRec['enabled'] = $data['enabled'];
					$newRec['notes'] = $_POST['notes'];
					$newRec['title'] = $_POST['title'];
					$newRec['special_processing'] = $_POST['special_processing'];
					$newRec['html'] = $data['html'];
					$newRec['created'] = date(DATE_ATOM);
					$stmt = $this->prepare(sprintf('insert into %s(%s) values(?%s)',$this->m_content, implode(',',array_keys($newRec)),str_repeat(',?',count($newRec)-1)));
					$stmt->bindParams(array_merge(array(str_repeat('s', count($newRec))),array_values($newRec)));
					if ($status = ($stmt->execute())) {
						$newId = $stmt->insertId();
						$modules = $this->fetchAll(sprintf('select * from modules_by_page where page_type = "T" and page_id = %d',$data['id']));
						foreach($modules as $module) {
							$module['page_id'] = $newId;
							unset($module['id']);
							$stmt = $this->prepare(sprintf('insert into modules_by_page(%s) values(?%s)',implode(',',array_keys($module)),str_repeat(',?',count($module)-1)));
							$stmt->bindParams(array_merge(array(str_repeat('s', count($module))),array_values($module)));
							$status = $status && $stmt->execute();
						}
						//$folder = $this->fetchSingle(sprintf('select * from templates_by_folder where template_id = %d',$data['template_id']));
						$stmt = $this->prepare(sprintf('insert into templates_by_folder(folder_id,template_id) values(%d,%d)',$_POST['destFolders'],$newRec['template_id']));
						$status = $status && $stmt->execute();
					}
					if ($status) {
						$this->commitTransaction();
						return $this->ajaxReturn(array('status'=>'true','url'=>'/modit/templates/?t_id='.$newId));
					}
					else {
						$form->addTag('errorMessage','Copy Failed');
						$this->rollbackTransaction();
						return $this->ajaxReturn(array('status'=>'false','html'=>$form->show()));
					}
				}
				else
					$form->addTag('errorMessage','Form validation failed');
			}
			return $this->ajaxReturn(array('status'=>'true','html'=>$form->show()));
		}
		else
			return $this->ajaxReturn(array('status'=>'false'));
	}
	
	function deleteTemplate() {
		$status = 'false';
		if (array_key_exists('t_id',$_REQUEST) && ($data = $this->fetchSingle(sprintf('select * from templates where id = %d',$_REQUEST['t_id'])))) {
			//$template = $this->fetchScalar(sprintf('select template_id from templates where id = %d',$_REQUEST['t_id']));
			$ct = $this->fetchScalarAll(sprintf('select title from content where id in (select content_id from pages where deleted = 0 and template_id = %d)',$_REQUEST['t_id']));
			$this->logMessage('deleteTemplate',sprintf('delete request for id [%d] pages in use [%d]',$_REQUEST['t_id'],count($ct)),2);
			if (count($ct) > 0) {
				$this->addError(sprintf('Warning: This template is used by the following pages: %s',implode(', ',$ct)));
				$status = 'false';
			}
			else {
				$this->execute(sprintf('update templates set deleted = 1 where template_id = %d',$_REQUEST['t_id']));
				$status = 'true';
			}
		}
		else {
			$this->addError('Could not locate the template');
		}
		return $this->ajaxReturn(array('status'=>$status));
	}

	function setEditMode() {
		//
		//	just pass all info to the iframe module and let it do its thing
		//
		$tmp = array_key_exists('edit',$_REQUEST) ? $_REQUEST['edit'] : 'do nothing';
		$this->logMessage("setEditMode",sprintf("function selected is [%s]",$tmp),2);
		$editing = $tmp == 'edit';
		$saving = $tmp == 'save' || $tmp == 'overwrite';
		$overwrite = $tmp == 'overwrite';
		$reverting = $tmp == 'revert';
		$deleting = $tmp == 'delete';
		$editor = new Forms();
		$editor->init($this->getTemplate('editorForm'));
		$flds = array();
		if ($editing) {
			$flds['saveButton'] = array('type'=>'button','value'=>'Save','onclick'=>'setEditMode("save");return false;');
			$flds['saveAndOverwrite'] = array('type'=>'button','value'=>'Save and Overwrite All Modules','onclick'=>'setEditMode("overwrite");return false;','class'=>'def_field_button alert alert-warning');
			$flds['saveChanges'] = array('type'=>'button','value'=>'Save and Overwrite Changed Modules','onclick'=>'setEditMode("changes");return false;');
			$flds['delete'] = array('type'=>'button','value'=>'Delete this Version','onclick'=>'setEditMode("delete");return false;');
			$flds['revertButton'] = array('type'=>'button','value'=>'Revert to Saved','onclick'=>'setEditMode("revert");return false;');
			$flds['previewButton'] = array('type'=>'button','value'=>'Preview Changes','onclick'=>'setEditMode("preview");return false;');
		}
		else {
			$flds['editButton'] = array('type'=>'button','value'=>'Edit','onclick'=>'setEditMode("edit");return false;');
			$flds['delete'] = array('type'=>'button','value'=>'Delete this Version','onclick'=>'setEditMode("delete");return false;');
		}
		if (!($saving || $reverting || $deleting)) {
			//
			//	the action hasn't actually happened yet so don't test for no edits
			//
			if (array_key_exists('t_id',$_REQUEST)) {
				if ($this->areEditing('T_'.$_REQUEST['t_id']) == 'true') {
					$editor->addTag('errorMessage','Unsaved edits exist for this template');
				}
				$editor->addTag('page_type','t');
				$editor->addTag('page_id',$_REQUEST['t_id']);
			}
		}
		$flds = $editor->buildForm($flds);
		return $this->ajaxReturn(array('status'=>'true','html'=>$editor->show()));
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

	function moduleStatus($fromMain = 0) {
		$result = $this->getHeader();
		return $result;
	}

	function areEditing($key = null) {
		$status = 'false';
		if (!is_null($key))
			$session_key = $key;
		else
			$session_key = array_key_exists('areEditing',$_REQUEST) && array_key_exists('t_id',$_REQUEST) ? $_REQUEST['t_id'] : "n/a";
		$this->logMessage('areEditing',sprintf('key [%s] session_key [%s]',$key,$session_key),1);
		if (array_key_exists('changeModule',$_SESSION) &&
				array_key_exists($session_key,$_SESSION['changeModule']) &&
				count($_SESSION['changeModule'][$session_key]) > 0)
				$status = 'true';
		if (is_null($key))
			return $this->ajaxReturn(array('status'=>$status));
		else
			return $status;
	}
}

?>
