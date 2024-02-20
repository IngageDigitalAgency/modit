<?php

class menu extends Backend {

	private $m_content = 'content';
	private $m_pagination = 5;

	function __construct() {
		$this->M_DIR = 'backend/modules/menu/';
		$this->setTemplates(
			array(
				'main'=>$this->M_DIR.'forms/menu.html',
				'deleteSuccess'=>$this->M_DIR.'forms/deleteSuccess.html',
				'folderProperties'=>$this->M_DIR.'forms/folder.html',
				'pageProperties'=>$this->M_DIR.'forms/page.html',
				'internalLinkProperties'=>$this->M_DIR.'forms/ilink.html',
				'externalLinkProperties'=>$this->M_DIR.'forms/elink.html',
				'showContentTree'=>$this->M_DIR.'forms/contenttree.html',
				'pageInfo'=>$this->M_DIR.'forms/pageInfo.html',
				'menuInfo'=>$this->M_DIR.'forms/menuInfo.html',
				'ilinkInfo'=>$this->M_DIR.'forms/ilinkInfo.html',
				'elinkInfo'=>$this->M_DIR.'forms/elinkInfo.html',
				'menuContentRow'=>$this->M_DIR.'forms/menuContentRow.html',
				'showILinkContent'=>$this->M_DIR.'forms/iLinkContent.html',
				'showELinkContent'=>$this->M_DIR.'forms/eLinkContent.html',
				'showPageContent'=>$this->M_DIR.'forms/pageContent.html',
				'editorForm'=>$this->M_DIR.'forms/editorForm.html',
				'header'=>$this->M_DIR.'forms/heading.html',
				'showSearchForm'=>$this->M_DIR.'forms/searchForm.html',
				'articleList'=>$this->M_DIR.'forms/articleList.html',
				'showMenuContent'=>$this->M_DIR.'forms/menuContent.html'
			)
		);
		$this->setFields(array(
			'articleList' => array(
				'id'=>array('type'=>'tag'),
				'title'=>array('type'=>'tag'),
				'created'=>array('type'=>'datestamp','mask'=>'d-M-Y h:m:i'),
				'published'=>array('type'=>'booleanIcon'),
				'versions'=>array('type'=>'select','class'=>'pageVersions','required'=>true),
				'enabled'=>array('type'=>'booleanIcon')
			),
			'showSearchForm'=>array(
				'options'=>array('action'=>'showSearchForm'),
				'published'=>array('type'=>'select','required'=>false,'lookup'=>'boolean'),
				'enabled'=>array('type'=>'select','required'=>false,'lookup'=>'boolean'),
				'showSearchForm'=>array('type'=>'hidden','value'=>1),
				'pagenum'=>array('type'=>'hidden','value'=>1),
				'sortby'=>array('type'=>'hidden','value'=>'left_id'),
				'sortorder'=>array('type'=>'hidden','value'=>'desc'),
				'pager'=>array('type'=>'select','required'=>true,'value'=>$this->m_pagination,'lookup'=>'paging','id'=>'pager'),
				'submit'=>array('type'=>'submitbutton','value'=>'Search')
			),
			'main' => array(),
			'folderProperties' => array(
				'options'=>array(
						'action'=>'/modit/menu/showPageProperties',
						'method'=>'post'
				),
				'title'=>array('type'=>'textfield','required'=>true,'prettyName'=>'Title'),
				'showPageProperties'=>array('type'=>'hidden','value'=>1, 'database'=>false),
				'alternate_title'=>array('type'=>'textfield','required'=>false),
				'p_id'=>array('type'=>'select','required'=>true,'database'=>false,'optionslist'=>array('table'=>'content','root'=>0,'indent'=>2,'inclusive'=>true),'addContentClass'=>true,'class'=>'contentClass'),
				'enabled'=>array('type'=>'checkbox','required'=>false,'value'=>1),
				'published'=>array('type'=>'checkbox','required'=>false,'value'=>1),
				'notes'=>array('type'=>'textarea','required'=>false),
				'teaser'=>array('type'=>'textarea','required'=>false,'class'=>'mceSimple'),
				'description'=>array('type'=>'textarea','required'=>false,'class'=>'mceAdvanced'),
				'type'=>array('type'=>'hidden','value'=>'folder'),
				'id'=>array('type'=>'hidden', 'database'=>false),
				'image'=>array('type'=>'tag'),
				'rollover_image'=>array('type'=>'tag'),
				'imagesel_a'=>array('type'=>'image','unknown'=>true,'database'=>false,'id'=>'imagesel_a'),
				'imagesel_b'=>array('type'=>'image','unknown'=>true,'database'=>false,'id'=>'imagesel_b'),
				'fromContent'=>array('type'=>'hidden','database'=>false),
				'icon_class'=>array('type'=>'textfield','required'=>false),
				'default_page'=>array('type'=>'checkbox','database'=>false,'value'=>1),
				'submit'=>array('type'=>'submitbutton','value'=>'Save','database'=>false)
			),
			'pageProperties' => array(
				'options'=>array(
						'action'=>'/modit/menu/showPageProperties',
						'method'=>'post'
				),
				'title'=>array('type'=>'textfield','required'=>true,'prettyName'=>'Title'),
				'alternate_title'=>array('type'=>'textfield','required'=>false),
				'browser_title'=>array('type'=>'textfield','required'=>false),
				'seo_url'=>array('type'=>'textfield','required'=>false),
				'showPageProperties'=>array('type'=>'hidden','value'=>1, 'database'=>false),
				'id'=>array('type'=>'hidden', 'database'=>false),
				'test'=>array('type'=>'hidden','value'=>1, 'database'=>false),
				'p_id'=>array('type'=>'select','required'=>true,'database'=>false,'optionslist'=>array('table'=>'content','root'=>0,'indent'=>2,'inclusive'=>true),'addContentClass'=>true,'class'=>'contentClass'),
				'template_id'=>array('type'=>'select','required'=>true,'database'=>false,'sql'=>'select template_id, title from templates t where deleted = 0 and t.enabled = 1 and t.version = (select max(version) from templates t1 where t1.template_id = t.template_id)','prettyName'=>'Template'),
				'enabled'=>array('type'=>'checkbox','required'=>false,'value'=>1),
				'published'=>array('type'=>'checkbox','required'=>false,'value'=>1),
				'notes'=>array('type'=>'textarea','required'=>false),
				'teaser'=>array('name'=>'teaser','type'=>'textarea','required'=>false,'class'=>'mceSimple'),
				'description'=>array('name'=>'description','type'=>'textarea','required'=>false,'class'=>'mceAdvanced'),
				'type'=>array('type'=>'hidden','value'=>'page'),
				'image'=>array('type'=>'tag'),
				'rollover_image'=>array('type'=>'tag'),
				'imagesel_a'=>array('type'=>'image','unknown'=>true,'database'=>false,'id'=>'imagesel_a'),
				'imagesel_b'=>array('type'=>'image','unknown'=>true,'database'=>false,'id'=>'imagesel_b'),
				'fromContent'=>array('type'=>'hidden','database'=>false),
				'default_page'=>array('type'=>'checkbox','database'=>false,'value'=>1),
				'secure'=>array('type'=>'checkbox','value'=>1),
				'meta_keywords'=>array('name'=>'meta_keywords','type'=>'textarea','required'=>false,'class'=>'mceNoEditor'),
				'meta_description'=>array('name'=>'meta_description','type'=>'textarea','required'=>false,'class'=>'mceNoEditor'),
				'icon_class'=>array('type'=>'textfield','required'=>false),
				'submit'=>array('type'=>'submitbutton','value'=>'Save','database'=>false)
			),
			'internalLinkProperties' => array(
				'type'=>array('type'=>'hidden','value'=>'internallink'),
				'p_id'=>array('type'=>'select','required'=>true,'database'=>false,'optionslist'=>array('table'=>'content','root'=>0,'indent'=>2,'inclusive'=>true),'addContentClass'=>true,'class'=>'contentClass'),
				'notes'=>array('type'=>'textarea','required'=>false),
				'teaser'=>array('type'=>'textarea','required'=>false,'class'=>'mceSimple'),
				'description'=>array('type'=>'textarea','required'=>false,'class'=>'mceAdvanced'),
				'enabled'=>array('type'=>'checkbox','required'=>false,'value'=>1),
				'published'=>array('type'=>'checkbox','required'=>false,'value'=>1),
				'new_window'=>array('type'=>'checkbox','required'=>false,'value'=>1),
				'showPageProperties'=>array('type'=>'hidden','value'=>1, 'database'=>false),
				'id'=>array('type'=>'hidden', 'database'=>false),
				'title'=>array('type'=>'textfield','required'=>true,'prettyName'=>'Title'),
				'alternate_title'=>array('type'=>'textfield','required'=>false),
				'internal_link'=>array('type'=>'select','required'=>true,'optionslist'=>array('table'=>'content','root'=>0,'indent'=>2,'inclusive'=>true),'prettyName'=>'Internal Link','addContentClass'=>true,'class'=>'contentClass'),
				'image'=>array('type'=>'tag'),
				'rollover_image'=>array('type'=>'tag'),
				'imagesel_a'=>array('type'=>'image','unknown'=>true,'database'=>false,'id'=>'imagesel_a'),
				'imagesel_b'=>array('type'=>'image','unknown'=>true,'database'=>false,'id'=>'imagesel_b'),
				'fromContent'=>array('type'=>'hidden','database'=>false),
				'secure'=>array('type'=>'checkbox','value'=>1),
				'icon_class'=>array('type'=>'textfield','required'=>false),
				'submit'=>array('type'=>'submitbutton','value'=>'Save','database'=>false)
			),
			'externalLinkProperties' => array(
				'type'=>array('type'=>'hidden','value'=>'externallink'),
				'p_id'=>array('type'=>'select','required'=>true,'database'=>false,'optionslist'=>array('table'=>'content','root'=>0,'indent'=>2,'inclusive'=>true),'addContentClass'=>true,'class'=>'contentClass'),
				'notes'=>array('type'=>'textarea','required'=>false),
				'teaser'=>array('type'=>'textarea','required'=>false,'class'=>'mceSimple'),
				'description'=>array('type'=>'textarea','required'=>false,'class'=>'mceAdvanced'),
				'enabled'=>array('type'=>'checkbox','required'=>false,'value'=>1),
				'published'=>array('type'=>'checkbox','required'=>false,'value'=>1),
				'new_window'=>array('type'=>'checkbox','required'=>false,'value'=>1),
				'showPageProperties'=>array('type'=>'hidden','value'=>1, 'database'=>false),
				'id'=>array('type'=>'hidden', 'database'=>false),
				'title'=>array('type'=>'textfield','required'=>true,'prettyName'=>'Title'),
				'alternate_title'=>array('type'=>'textfield','required'=>false),
				'external_link'=>array('type'=>'textfield','required'=>true,'prettyName'=>'External Link'),
				'image'=>array('type'=>'tag'),
				'rollover_image'=>array('type'=>'tag'),
				'imagesel_a'=>array('type'=>'image','unknown'=>true,'database'=>false,'id'=>'imagesel_a'),
				'imagesel_b'=>array('type'=>'image','unknown'=>true,'database'=>false,'id'=>'imagesel_b'),
				'fromContent'=>array('type'=>'hidden','database'=>false),
				'secure'=>array('type'=>'checkbox','value'=>1),
				'icon_class'=>array('type'=>'textfield','required'=>false),
				'submit'=>array('type'=>'submitbutton','value'=>'Save','database'=>false)
			),
			'ilinkInfo'=>array(
				'imagesel_a'=>array('type'=>'image','unknown'=>true,'database'=>false,'id'=>'imagesel_a'),
				'imagesel_b'=>array('type'=>'image','unknown'=>true,'database'=>false,'id'=>'imagesel_b'),
				'image'=>array('type'=>'image'),
				'rollover_image'=>array('type'=>'image')
			),
			'elinkInfo'=>array(
				'image'=>array('type'=>'tag','required'=>false,'prettyName'=>'Image 1'),
				'rollover_image'=>array('type'=>'tag','required'=>false),
				'imagesel_a'=>array('type'=>'image','unknown'=>true,'database'=>false,'id'=>'imagesel_a'),
				'imagesel_b'=>array('type'=>'image','unknown'=>true,'database'=>false,'id'=>'imagesel_b')
			),
			'pageInfo' => array(
				'versions' => array('type'=>'select','onchange'=>'loadVersion(this)','required'=>true,'id'=>'versionSelector')
			),
			'menuContentRow' => array(
				'image' => array('type'=>'image'),
				'rollover_image'=>array('type'=>'image'),
				'menuId'=>array('type'=>'tag'),
				'versions'=>array('type'=>'select')
			),
			'menuInfo' => array(
				'title'=>array('type'=>'tag'),
				'alternate_title'=>array('type'=>'tag'),
				'notes'=>array('type'=>'tag','reformatting'=>false),
				'image' => array('type'=>'image','unknown'=>true),
				'rollover_image'=>array('type'=>'image','unknown'=>true)
			)
		));
		parent::__construct();
	}

	function show($injector = null) {
		$form = new Forms();
		$form->init($this->getTemplate('main'),array('name'=>'adminMenu'));
		$flds = $form->buildForm($this->getFields('main'));
		if (is_null($injector)) {
			$injector = $this->moduleStatus(true);
		}
		$form->addTag('injector', $injector, false);
		$this->logMessage('show',sprintf('return [%s]',print_r($form,true)),1);
		return $form->show();
	}

	function getTitle() {
		return 'Administrative Menu';
	}

	function formatTreeNode($data, $table, $wrappers, $submenu) {
		switch($table) {
		case 'content':
			$value = new tag(false);
			$return = array('value'=>$value->show(sprintf('<a href="#">%s</a>',htmlspecialchars($data['title']))),'submenu'=>$submenu);
			break;
		default:
			break;			
		}
		return $return;			
	}

	function showContentTree() {
		$form = new Forms();
		$form->init($this->getTemplate('showContentTree'),array());
		$form->addTag('tree',$this->buildTree('content', array(), "ajaxBuild", array(0=>"<ol>%s</ol>",1=>"<li class='collapsed'>%s%s</li>",3=>"")),false);
		if ($this->isAjax())
			return $this->ajaxReturn(array('status'=>'true','html'=>$form->show()));
		else
			return $form->show();
	}

	function ajaxBuild($data, $table, $wrappers, $submenu) {
		switch($table) {
			case 'content':
				$value = new tag(false);
				$mptt = new mptt($table);
				$children = $mptt->fetchChildren($data['id']);
				if (count($_REQUEST) > 0 && array_key_exists('p_id',$_REQUEST)) {
					$expanded = $_REQUEST['p_id'] == $data['id'] ?  'active' : '';
				}
				else $expanded='';
				if (count($submenu) > 0) {
					$return = array('value'=>sprintf('<div class="wrapper"><div class="spacer"><a href="#" class="toggler" onclick="toggle(this);return false;">+</a></div>&nbsp;<a href="#" id="li_%d" class="%s icon_%s info">%s</a></div>',$data['id'], $expanded, $data['type'], htmlspecialchars($data['title'])),'submenu'=>$submenu);
				}
				else {
					$return = array('value'=>sprintf('<div class="wrapper"><div class="spacer">&nbsp;</div>&nbsp;<a href="#" id="li_%d" class="%s icon_%s info">%s</a></div>',$data['id'], $expanded, $data['type'], htmlspecialchars($data['title'])),'submenu'=>array());
				}
				break;
			default:
				break;
		}
		return $return;
	}
	
	function showPageProperties($fromMain = false) {
		$result = array();
		$return = 'true';
		$type = array_key_exists('type',$_REQUEST) ? $_REQUEST['type'] : 'page';
		if (!(array_key_exists('id',$_REQUEST) && $data = $this->fetchSingle(sprintf('select * from content where id = %d',$_REQUEST['id']))))
			$data = array('enabled'=>0,'id'=>0,'type'=>$type,'published'=>0,'image'=>'','rollover_image'=>'');
		else {
			$type = $data['type'];
			$data['p_id'] = $this->fetchScalar(sprintf('select id from content where level = %d and left_id < %d and right_id > %d limit 1', $data['level']-1, $data['left_id'],$data['right_id']));
		}
		//
		//	flag to tell us where to return to after successful edit
		//
		if(array_key_exists('fromContent',$_REQUEST))
			$data['fromContent'] = $_REQUEST['fromContent'];
		$form = new Forms();
		switch(strtolower($type)) {
			case 'folder':
				$form->init($this->getTemplate('folderProperties'),array('name'=>'folderProperties'));
				$frmFlds = $this->getFields('folderProperties');
				$data['imagesel_a'] = $data['image'];
				$data['imagesel_b'] = $data['rollover_image'];
				break;
			case 'internallink':
				$form->init($this->getTemplate('internalLinkProperties'),array('name'=>'internallinkProperties'));
				$frmFlds = $this->getFields('internalLinkProperties');
				$data['imagesel_a'] = $data['image'];
				$data['imagesel_b'] = $data['rollover_image'];
				break;
			case 'externallink':
				$form->init($this->getTemplate('externalLinkProperties'),array('name'=>'externallinkProperties'));
				$frmFlds = $this->getFields('externalLinkProperties');
				$data['imagesel_a'] = $data['image'];
				$data['imagesel_b'] = $data['rollover_image'];
				break;
			case 'page':
			default:
				//$result[] = 'page properties go here';
				$data['imagesel_a'] = $data['image'];
				$data['imagesel_b'] = $data['rollover_image'];
				$data['template_id'] = $this->fetchScalar(sprintf('select template_id from pages where deleted = 0 and content_id = %d order by id desc limit 1',$data['id']));
				$form->init($this->getTemplate('pageProperties'),array('name'=>'pageProperties'));
				$frmFlds = $this->getFields('pageProperties');
				break;
		}
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
				unset($frmFlds['delete']);
				if ($data['id'] == 0) $this->noAccessError();
				if (array_key_exists('addContent',$_POST)) unset($_POST['addContent']);
		}

		$customFields = new custom();
		if (method_exists($customFields,'menuFolderDisplay')) {
			$custom = $customFields->menuFolderDisplay();
			$form->addTag('customTab',sprintf('<li><a href="#tabs-custom">%s</a></li>',$custom['description']),false);
			$html = $form->getHTML();
			$html = str_replace('%%customInfo%%',$custom['form'],$html);
			$form->setHTML($html);
			$frmFlds = array_merge($frmFlds,$custom['fields']);
		}

		$frmFlds = $form->buildForm($frmFlds);
		$form->addData($data);
		if (count($_POST) > 0 && array_key_exists('showPageProperties',$_POST)) {
			$_POST['imagesel_a'] = $_POST['image'];
			$_POST['imagesel_b'] = $_POST['rollover_image'];
			$form->addData($_POST);
			$valid = $form->validate();
			if ($valid && array_key_exists('seo_url',$_POST) && strlen($_POST['seo_url']) > 0) {
				$sql = sprintf('select title from content where (search_title = "%s" or seo_url = "%s") and id != %d',preg_replace('#[^a-z0-9_]#i', '-', strtolower($form->getData('seo_url'))),preg_replace('#[^a-z0-9_]#i', '-', strtolower($form->getData('seo_url'))),$data['id']);
				$other = $this->fetchScalarAll($sql);
				if (count($other) > 0) {
					$this->addError(sprintf('The SEO Url is already being used on page[s] (%s)',implode('), (',$other)));
					$valid = false;
				}
			}
			if ($valid) {
				if (array_key_exists('options',$frmFlds)) unset($frmFlds['options']);
				$values = array();
				$flds = array();
				if ($data['id'] == 0) {
					$mptt = new mptt('content');
					$data['id'] = $mptt->add($_POST['p_id'],999,array('title'=>'to be replaced','type'=>$type));
					$status = true;
					if ($data['type'] == 'page') {
						//
						//	create the corresponding pages data as well
						//
						$p_values = array('template_id'=>$_POST['template_id'],  'content_id'=>$data['id'],  'version'=>'1.0', 'user_id'=>array_key_exists('administrator',$_SESSION) ? $_SESSION['administrator']['user']['id'] : '0');
						$stmt = $this->prepare(sprintf('insert into pages(%s) values(%s)', implode(',',array_keys($p_values)), str_repeat('?,', count($p_values)-1).'?'));
						$stmt->bindParams(array_merge(array(str_repeat('s', count($p_values))),$p_values));
						$stmt->execute();
						$page_id = $this->insertId();
						//
						//	grab the default modules from the template
						//
						$modules = $this->fetchAll(sprintf('select * from modules_by_page where page_type = "T" and page_id = (select max(id) from templates t where t.template_id = %d)',$form->getData('template_id')));
						foreach($modules as $module) {
							unset($module['id']);
							$module['page_type'] = 'P';
							$module['page_id'] = $page_id;
							$new = array();
							$stmt = $this->prepare(sprintf('insert into modules_by_page(%s) values(%s)',implode(',',array_keys($module)),str_repeat("?,",count($module)-1)."?"));
							$stmt->bindParams(array_merge(array(str_repeat('s', count($module))),$module));
							$status = $status && $stmt->execute();
						}
					}
				}
				else if ($data['p_id'] != $_POST['p_id']) {
					//
					//	we changed the parent
					//
					$mptt = new mptt('content');
					$this->logMessage('showPageProperties',sprintf('mptt moving [%s] to [%s]',$data['id'],$_POST['p_id']),1);
					$mptt->move($data['id'],$_POST['p_id']);
				}
				if ($data['type'] == 'page') {
					//
					//	update the template type
					//
					$sql = sprintf('update pages set template_id = %d where content_id = %d and version = (select max(version) from (select * from pages) as x where content_id=%d)',$_POST['template_id'],$data['id'],$data['id']);
					$this->logMessage('showPageProperties',sprintf('updating template [%s]',$sql),2);
					if ($form->getData('default_page') != 0) {
						$this->execute(sprintf('update content set default_page=0 where id != %d and default_page != 0',$data['id']));
						$this->execute(sprintf('update content set default_page=1 where id = %d',$data['id']));
					}
					$this->execute($sql);
				}
				foreach($frmFlds as $key=>$fld) {
					if (!(array_key_exists('database',$fld) && $fld['database'] == false)) {
						if (array_key_exists('class',$fld) && strpos($fld['class'],'mceNoEditor') !== false) {
							$tmp  = $form->getData($fld['name']);
							$tmp = str_replace('"',"'",$tmp);
							$tmp = str_replace("\r\n"," ",$tmp);
							$tmp = str_replace("\n"," ",$tmp);
							$tmp = str_replace("\n"," ",$tmp);
							$values[] = $tmp;
						}
						else
							$values[] = $form->getData($fld['name']);
						if ($data['id'] > 0)
							$flds[] = sprintf('%s = ?',$fld['name']);
						else 
							$flds[] = $fld['name'];
					}
				}
				$flds[] = 'search_title = ?';
				if (($alt = $form->getData('alternate_title')) != '')
					$values[] = preg_replace('#[^a-z0-9]#i', '-', strtolower($form->getData('title').' '.$form->getData('alternate_title')));
				else
					$values[] = preg_replace('#[^a-z0-9]#i', '-', strtolower($form->getData('title')));
				$flds[] = 'seo_url = ?';
				$values[] = preg_replace('#[^a-z0-9]#i', '-', strtolower($form->getData('seo_url')));
				$stmt = $this->prepare(sprintf('update content set %s where id = %d',implode(',',$flds),$data['id']));
				$stmt->bindParams(array_merge(array(str_repeat('s', count($flds))),$values));
				if ($stmt->execute()) {
					if ($this->isAjax()) {
						$this->logMessage('showPageProperties', 'executing ajax success return', 3);
						$this->addMessage('Record successfully added');
						if (array_key_exists('fromContent',$_REQUEST) && $_REQUEST['fromContent'] != 0) {
							return $this->ajaxReturn(array(
									'status'=>'true',
									'code'=>'closePopup();loadContent('.$_REQUEST['fromContent'].')'
							));
						}
						else
							return $this->ajaxReturn(array(
									'status'=>'true',
									'url'=>'/modit/menu?p_id='.$data['id']
							));
					}
				} else {
					$this->addError('An Error occurred');
					$form->addTag('errorMessage',$this->showErrors(),false);
					if ($this->isAjax()) {
						$this->logMessage('showPageProperties', 'executing ajax error return', 3);
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
	
	function getPageInfo($fromMain = false) {
		$form = new Forms();
		if (array_key_exists('c_id',$_REQUEST)) {
			if ($data = $this->fetchSingle(sprintf('select * from content where id = %d',$_REQUEST['c_id']))) {
				$data['notes'] = nl2br($data['notes']);
				switch (strtolower($data['type'])) {
					case 'folder':
						$template = 'menuInfo';
						$frmFields = $this->getFields($template);
						$altData = array();
						break;
					case 'internallink':
						$template = 'ilinkInfo';
						$frmFields = $this->getFields($template);
						$altData = array();
						break;
					case 'externallink':
						$template = 'elinkInfo';
						$frmFields = $this->getFields($template);
						$altData = array();
						break;
					case 'page':
					default:
						$altData = $this->fetchSingle(sprintf('select p1.*, t.title as templatename, c.notes from pages p1, templates t, content c where c.id = p1.content_id and t.template_id = p1.template_id and p1.content_id = %d and p1.version = (select max(p2.version) from pages p2 where p2.content_id = p1.content_id)',$data['id']));
						$altData['notes'] = nl2br($altData['notes']);
						$template = 'pageInfo';
						$frmFields = $this->getFields($template);
						$frmFields['versions']['sql'] = sprintf('select id, concat(version,": ",date_format(created,"%%d-%%b-%%Y %%T")) from pages where content_id = %d and deleted = 0 order by id desc',$data['id']);
						if (array_key_exists('p_id',$_REQUEST)) {
							$form->setData('versions',$_REQUEST['p_id']);
						}
						break;
				}
				$form->init($this->getTemplate($template), array());
				$frmFields = $form->buildForm($frmFields);
				$form->addData($data);
				$form->addData($altData);
			}
		}
		if ($fromMain)
			return $form->show();
		else
			if ($this->isAjax())
				return $this->ajaxReturn(array('status'=>'true','html'=>$form->show()));
			else
				return $this->show($form->show());
		
	}
	
	function showPageContent($fromMain = false) {
		$p_id = array_key_exists('p_id',$_REQUEST) ? $_REQUEST['p_id'] : 0;
		$c_id = array_key_exists('c_id',$_REQUEST) ? $_REQUEST['c_id'] : 0;
		$form = new Forms();
		if ($c_id > 0 && $data = $this->fetchSingle(sprintf('select * from content where id = %d',$c_id))) {
			$altData = array();
			switch($data['type']) {
				case 'internallink':
					$form->init($this->getTemplate('showILinkContent'),array('name'=>'showILinkContent'));
					$frmFields = $this->getFields('showILinkContent');
					$form->addTag('url',$this->getUrl('menu',$data['id'],$data),false);
					$link = $this->fetchSingle(sprintf('select * from content where id = %d',$data['internal_link']));
					$form->addTag('link_name',$link['title']);
					$altData = array();
					break;
				case 'externallink':
					$form->init($this->getTemplate('showELinkContent'),array('name'=>'showELinkContent'));
					$frmFields = $this->getFields('showELinkContent');
					$form->addTag('url',$this->getUrl('menu',$data['id'],$data),false);
					$form->addTag('url_text',$data['external_link']);
					$altData = array();
					break;
				case 'folder':
					$form->init($this->getTemplate('showMenuContent'),array('name'=>'showMenuContent'));
					$frmFields = $this->getFields('showMenuContent');
					$menu = $this->fetchAll(sprintf('select c.*, l.value as prettyName from content c left join code_lookups l on l.code = c.type and l.type="contentTypes" where c.level = %d and c.left_id > %d and c.right_id < %d order by c.left_id',$data['level']+1, $data['left_id'], $data['right_id']));
					$result = array();
					foreach($menu as $item) {
						$row = new Forms();
						$row->init($this->getTemplate('menuContentRow'),array());
						$tmp = $row->buildForm($this->getFields('menuContentRow'));
						$item['menuId'] = $data['id'];	//	menu to refresh is we are in content area
						if ($item['type'] == 'page') {
							$options = $this->fetchAll(sprintf('select id, concat(version,", ",date_format(created,"%%c-%%b-%%Y %%T")) as versionDate from pages where content_id = %d order by id desc',$item['id']));
							foreach($options as $key=>$opt) {
								$row->getField('versions')->addOption($opt['id'],$opt['versionDate']);
							}
							$row->addTag('editlayout',sprintf('<a onclick="loadContent(%d)" href="#"><i class="icon-edit"></i>&nbsp;Layout</a>',$item['id']),false);
						}
						else {
							$row->deleteElement('versions');
						}
						$row->addData($item);
						$result[] = $row->show();
					}
					$altData = array('menu'=>implode('',$result));
					break;
				case 'page':
				default:
					if ($p_id == 0) {
						$p_id = $this->fetchScalar(sprintf('select id from pages where content_id = %d and deleted = 0 order by version desc limit 1',$c_id));
						$_REQUEST['p_id'] = $p_id;
					}
					$form->init($this->getTemplate('showPageContent'),array('name'=>'showPageContent'));
					$frmFields = $this->getFields('showPageContent');
					if ($this->getAccessLevel() < 4) {
						$frmFields['editButton'] = array('type'=>'button','value'=>'Edit Layout','onclick'=>'setEditMode("edit");return false;');
						$frmFields['delete'] = array('type'=>'button','value'=>'Delete this Version','onclick'=>'setEditMode("delete");return false;');
					}
					$sql = sprintf('select * from pages p where id = %d',$p_id);	//content_id = %d and ((%d != 0 and version = %d) or (%d = 0 and version = (select max(version) from pages where content_id = %d)))',$p_id, $v_id, $v_id, $v_id, $p_id);
					$altData = $this->fetchSingle($sql);
					if (array_key_exists('changeModule',$_SESSION) && array_key_exists('P_'.$altData['id'],$_SESSION['changeModule'])) {
						if (count($_SESSION['changeModule']['P_'.$altData['id']]) > 0) {
							$altData['errorMessage'] = 'Unsaved module changes exist for this page';
						}
					}
					if (array_key_exists('pageContent',$_SESSION) && array_key_exists('P_'.$altData['id'],$_SESSION['pageContent'])) {
						if (count($_SESSION['pageContent']['P_'.$altData['id']]) > 0) {
							$altData['errorMessage'] = 'Unsaved module changes exist for this page';
						}
					}
					$form->addTag('p_id',$p_id);
					$this->logMessage("showPageContent",sprintf("altData [%s] sql [%s]",print_r($altData,true),$sql),2);
					break;
			}		
			$frmFields = $form->buildForm($frmFields);
			$form->addData($data);
			$form->addTag('c_id',$c_id);
			$form->addData(is_array($altData) ? $altData: array());
		}
		$form->addTag('infoform',$this->getPageInfo(true),false);
		$form->addTag('header',$this->getHeader(),false);
		if ($this->isAjax())
			return $this->ajaxReturn(array('status'=>'true','html'=>$form->show()));
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
			$rec = $this->fetchSingle(sprintf('select * from content where id = %d',$p_id));
			$status = true;
			switch($rec['type']) {
				case 'page':
					$ct = $this->fetchScalar(sprintf('select count(0) from content where internal_link = %d',$p_id));
					if ($ct > 0) $this->addError('Links still exist to this page');
					$status = $ct == 0;
					break;
				case 'folder':
					$ct = $this->fetchScalar(sprintf('select count(0) from content where left_id > %d and right_id < %d and level > %d',$rec['left_id'],$rec['right_id'],$rec['level']));
					if ($ct > 0) $this->addError('Pages still exist for this menu');
					$status = $ct == 0;
					break;
				case 'internallink':
				case 'externallink':
				default:
					break;
			}
			if ($status) {
				$mptt = new mptt('content');
				if (!$mptt->delete($p_id,true)) {
					$status = false;
					$this->addError('An error occurred while deleting the record');
				}
				else {
					if ($rec['type'] == 'page') {
						$this->execute('update pages set deleted = 1 where content_id = '.$p_id);
					}
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

	function hasFunctionAccess($method) {
		if (parent::hasFunctionAccess($method)) return true;
		return true;
	}

	function setEditMode() {
		$level = $this->getAccessLevel();
		switch($level) {
			case 1:
			case 2:
			case 3:
				break;	// admin can do anything
			case 4:
			default:
				return $this->ajaxReturn(array('status'=>'true','html'=>'</div></div>'));
		}

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
			$flds['delete'] = array('type'=>'button','value'=>'Delete this Version','onclick'=>'setEditMode("delete");return false;');
			$flds['revertButton'] = array('type'=>'button','value'=>'Revert to Saved','onclick'=>'setEditMode("revert");return false;');
			$flds['previewButton'] = array('type'=>'button','value'=>'Preview Changes','onclick'=>'setEditMode("preview");return false;');
			$flds['templateButton'] = array('type'=>'button','value'=>'Update From Template','onclick'=>'setEditMode("template");return false;');
		}
		else {
			$flds['editButton'] = array('type'=>'button','value'=>'Edit Layout','onclick'=>'setEditMode("edit");return false;');
			$flds['delete'] = array('type'=>'button','value'=>'Delete this Version','onclick'=>'setEditMode("delete");return false;');
		}
		$flds = $editor->buildForm($flds);
		if (array_key_exists('changeModule',$_SESSION) && array_key_exists('P_'.$_REQUEST['p_id'],$_SESSION['changeModule'])) {
			if (count($_SESSION['changeModule']['P_'.$_REQUEST['p_id']]) > 0) {
				if (!$reverting)
					$_REQUEST['errorMessage'] = 'Unsaved module changes exist for this page';
			}
		}
		if (array_key_exists('pageContent',$_SESSION) && array_key_exists('P_'.$_REQUEST['p_id'],$_SESSION['pageContent'])) {
			if (strlen($_SESSION['pageContent']['P_'.$_REQUEST['p_id']]) > 0) {
				if (!$reverting)
					$_REQUEST['errorMessage'] = 'Unsaved module changes exist for this page';
			}
		}
		$editor->addData($_REQUEST);
		return $this->ajaxReturn(array('status'=>'true','html'=>$editor->show()));
	}

	function moduleStatus($fromMain = 0) {
		$ct = $this->fetchScalar(sprintf('select count(0) from %s where published = 0',$this->m_content));
		if ($ct > 0) {
			$_POST = array('showSearchForm'=>1,'published'=>0,'sortby'=>'left_id','sortorder'=>'asc','pager'=>$this->m_pagination);
			$msg = "Showing unpublished content";
			$result = $this->showSearchForm($fromMain,$msg);
		}
		else $result = $this->getHeader();
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

	function showSearchForm($fromMain = false,$msg = '') {
		$form = new Forms();
		$form->init($this->getTemplate('showSearchForm'),array('name'=>'searchForm','persist'=>true));
		$frmFields = $form->buildForm($this->getFields('showSearchForm'));
		if (count($_POST) == 0)
			if (array_key_exists('formData',$_SESSION) && array_key_exists('menuSearchForm', $_SESSION['formData']))
				$_POST = $_SESSION['formData']['menuSearchForm'];
			else
				$_POST = array('showSearchForm'=>1,'deleted'=>0,'sortby'=>'published','sortorder'=>'asc');
		$this->logMessage("showSearchForm",sprintf("post [%s]",print_r($_POST,true)),3);
		if (count($_POST) > 0 && array_key_exists('showSearchForm',$_POST)) {
			$form->addData($_POST);
			if ($form->validate()) {
				$_SESSION['formData']['menuSearchForm'] = $form->getAllData();
				$srch = array();
				foreach($frmFields as $key=>$value) {
					switch($key) {
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
					if (array_key_exists('pagenum',$_REQUEST))
						$pageNum = $_REQUEST['pagenum'];
					else
						$pageNum = 1;	// no 0 based calcs
					$perPage = $this->m_pagination;
					if (array_key_exists('pager',$_REQUEST)) $perPage = $_REQUEST['pager'];
					$count = $this->fetchScalar(sprintf('select count(n.id) from %s n where 1=1 and %s', $this->m_content, implode(' and ',$srch)));
					$pageNum = max(1,min($pageNum, (floor(($count-1)/$perPage)+1)));
					$form->setData('pagenum', $pageNum);
					$pagination = $this->pagination($count, $perPage, $pageNum,
							array('prev'=>$this->M_DIR.'forms/paginationPrev.html','next'=>$this->M_DIR.'forms/paginationNext.html',
									'pages'=>$this->M_DIR.'forms/paginationPage.html', 'wrapper'=>$this->M_DIR.'forms/paginationWrapper.html'));
					$start = ($pageNum-1)*$perPage;
					$sortorder = 'asc';
					$sortby = 'left_id';
					if (array_key_exists('sortby',$_POST)) {
						$sortby = $_POST['sortby'];
						$sortorder = $_POST['sortorder'];
					}
					$sql = sprintf('select n.*, 0 as j_id from %s n where 1=1 and %s order by %s %s limit %d,%d',
						 $this->m_content, implode(' and ',$srch),$sortby, $sortorder, $start,$perPage);
					$recs = $this->fetchAll($sql);
					$this->logMessage('showSearchForm', sprintf('sql [%s] records [%d]',$sql,count($recs)), 2);
					$articles = array();
					$frm = new Forms();
					$tmp = $this->getFields('articleList');
					$frm->init($this->getTemplate('articleList'),array());
					foreach($recs as $article) {
						$tmp = $frm->buildForm($tmp);
						$frm->addData($article);
						$frm->getField('versions')->setOptions(array());
						$frm->addTag('editlayout','');
						switch($article['type']) {
						case 'page':
							$frm->addTag('type','Page');
							$options = $this->fetchAll(sprintf('select id, concat(version,", ",date_format(created,"%%c-%%b-%%Y %%T")) as versionDate from pages where content_id = %d order by id desc',$article['id']));
							foreach($options as $key=>$opt) {
								$frm->getField('versions')->addOption($opt['id'],$opt['versionDate']);
							}
							$frm->addTag('editlayout',sprintf('<a href="#" onclick="loadContent(%d,%d)"><i class="icon-edit"></i>&nbsp;Layout</a>',$article['id'],$options[0]['id']),false);
							break;
						case 'folder':
							$frm->deleteElement('versions');
							$frm->addTag('type','Menu');
							break;
						case 'internallink':
							$frm->deleteElement('versions');
							$frm->addTag('type','Internal Link');
							break;
						case 'externallink':
							$frm->deleteElement('versions');
							$frm->addTag('type','External Link');
							break;
						default:
							break;
						}
						//$frm->setData('versions',$id);
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
		if ($fromMain)
			return $form->show();
		else {
			if ($this->isAjax()) {
				$tmp = $form->show();
				return $this->ajaxReturn(array('status'=>'true','html'=>$tmp));
			}
			else
				return $this->show($form->show());
		}
	}

	function areEditing($key = null) {
		$status = 'false';
		if (!is_null($key))
			$session_key = $key;
		else
			$session_key = array_key_exists('areEditing',$_REQUEST) && array_key_exists('p_id',$_REQUEST) ? $_REQUEST['p_id'] : "n/a";
		$this->logMessage('areEditing',sprintf('key [%s] session_key [%s] request [%s]',$key,$session_key,print_r($_REQUEST,true)),1);
		if (array_key_exists('changeModule',$_SESSION) &&
				array_key_exists($session_key,$_SESSION['changeModule']) &&
				count($_SESSION['changeModule'][$session_key]) > 0)
				$status = 'true';
		if ($status == 'false')
			if (array_key_exists('pageContent',$_SESSION) &&
					array_key_exists($session_key,$_SESSION['pageContent']) &&
					strlen($_SESSION['pageContent'][$session_key]) > 0)
					$status = 'true';	
		if (is_null($key))
			return $this->ajaxReturn(array('status'=>$status));
		else
			return $status;
	}

}

?>