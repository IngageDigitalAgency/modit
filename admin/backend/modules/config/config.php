<?php

class config extends Backend {

	private $m_content = 'config';
	private $m_pagination = 0;
	private $m_perrow = 4;
	
	public function __construct() {
		$this->M_DIR = 'backend/modules/config/';
		$this->setTemplates(
			array(
				'main'=>$this->M_DIR.'config.html',
				'form'=>$this->M_DIR.'forms/form.html',
				'showContentTree'=>$this->M_DIR.'forms/contenttree.html',
				'configInfo'=>$this->M_DIR.'forms/configInfo.html',
				'folderProperties'=>$this->M_DIR.'forms/folder.html',
				'showFolderContent'=>$this->M_DIR.'forms/folderContent.html',
				'folderInfo'=>$this->M_DIR.'forms/folderInfo.html',
				'showSearchForm'=>$this->M_DIR.'forms/searchForm.html',
				'articleList'=>$this->M_DIR.'forms/articleList.html',
				'addContent_text'=>$this->M_DIR.'forms/addContent_text.html',
				'addContent_textarea'=>$this->M_DIR.'forms/addContent_textarea.html',
				'addContent_image'=>$this->M_DIR.'forms/addContent_image.html',
				'addContent_address'=>$this->M_DIR.'forms/addContent_address.html',
				'addContent_paired_list'=>$this->M_DIR.'forms/addContent_paired_list.html',
				'addContent_paired_row'=>$this->M_DIR.'forms/addContent_paired_row.html',
				'showPageContent'=>$this->M_DIR.'forms/pageContent.html',
				'pageSubform'=>$this->M_DIR.'forms/pageSubform.html',
				'editTax'=>$this->M_DIR.'forms/editTax.html',
				'taxList'=>$this->M_DIR.'forms/taxList.html',
				'taxSuccess'=>$this->M_DIR.'forms/taxSuccess.html',
				'taxRow'=>$this->M_DIR.'forms/taxRow.html',
				'formList'=>$this->M_DIR.'forms/formList.html',
				'formSuccess'=>$this->M_DIR.'forms/formSuccess.html',
				'formRow'=>$this->M_DIR.'forms/formRow.html',
				'editForm'=>$this->M_DIR.'forms/editForm.html'
			)
		);
		$this->setFields(array(
			'editForm'=>array(
				'options'=>array('name'=>'formEditor','action'=>'editForm','method'=>'post','database'=>false),
				'class'=>array('type'=>'select','id'=>'class','sql'=>'select id, title from modules order by title','required'=>true),
				'type'=>array('type'=>'input','required'=>true),
				'html'=>array('type'=>'textarea','required'=>true,'class'=>'mceAdvanced'),
				'submit'=>array('type'=>'submitbutton','value'=>'Save','database'=>false),
				'formEditor'=>array('type'=>'hidden','value'=>1,'database'=>false)
			),
			'editTax'=>array(
				'options'=>array('name'=>'taxEditor','action'=>'editTax','method'=>'post','database'=>false),
				'country_id'=>array('type'=>'countrySelect','database'=>false,'id'=>'country_id'),
				'province_id'=>array('type'=>'provinceSelect','id'=>'province_id'),
				'name'=>array('type'=>'input','required'=>true),
				'tax_rate'=>array('type'=>'input','required'=>true,'validation'=>'number'),
				'deleted'=>array('type'=>'checkbox','required'=>false,'value'=>1),
				'submit'=>array('type'=>'submitbutton','value'=>'Save','database'=>false),
				'taxEditor'=>array('type'=>'hidden','value'=>1,'database'=>false)
			),
			'taxList'=>array(),
			'taxRow'=>array(),
			'formList'=>array(),
			'formRow'=>array(),
			'addContent_text'=>array(
				'options'=>array('method'=>'post','action'=>'/modit/ajax/addContent/config'),
				'id'=>array('type'=>'tag','database'=>false),
				'value'=>array('type'=>'input','required'=>true),
				'submit'=>array('type'=>'submitbutton','value'=>'Save','database'=>false),
				'addContent'=>array('type'=>'hidden','value'=>1,'database'=>false)
			),
			'addContent_textarea'=>array(
				'options'=>array('method'=>'post','action'=>'/modit/ajax/addContent/config'),
				'id'=>array('type'=>'tag','database'=>false),
				'value'=>array('type'=>'textarea','required'=>true,'reformatting'=>false,'class'=>'mceNoEditor'),
				'submit'=>array('type'=>'submitbutton','value'=>'Save','database'=>false),
				'addContent'=>array('type'=>'hidden','value'=>1,'database'=>false)
			),
			'addContent_image'=>array(
				'options'=>array('method'=>'post','action'=>'/modit/ajax/addContent/config'),
				'id'=>array('type'=>'tag','database'=>false),
				'value'=>array('type'=>'tag','required'=>true),
				'submit'=>array('type'=>'submitbutton','value'=>'Save','database'=>false),
				'addContent'=>array('type'=>'hidden','value'=>1,'database'=>false)
			),
			'addContent_address'=>array(
				'options'=>array('method'=>'post','action'=>'/modit/ajax/addContent/config'),
				'id'=>array('type'=>'tag','database'=>false),
				'value'=>array('type'=>'hidden','required'=>false),
				'addressname'=>array('type'=>'input','name'=>'address[addressname]','database'=>false),
				'line1'=>array('type'=>'input','name'=>'address[line1]','database'=>false),
				'line2'=>array('type'=>'input','name'=>'address[line2]','database'=>false),
				'city'=>array('type'=>'input','name'=>'address[city]','database'=>false),
				'postalcode'=>array('type'=>'input','name'=>'address[postalcode]','database'=>false),
				'country_id'=>array('type'=>'countrySelect','id'=>'country_id','name'=>'address[country_id]','database'=>false),
				'province_id'=>array('type'=>'provinceSelect','id'=>'province_id','name'=>'address[province_id]','database'=>false,'required'=>false),
				'phone1'=>array('type'=>'input','name'=>'address[phone1]','database'=>false),
				'phone2'=>array('type'=>'input','name'=>'address[phone2]','database'=>false),
				'fax'=>array('type'=>'input','name'=>'address[fax]','database'=>false),
				'email'=>array('type'=>'input','name'=>'address[email]','database'=>false),
				'firstname'=>array('type'=>'input','name'=>'address[lastname]','database'=>false),
				'lastname'=>array('type'=>'input','name'=>'address[firstname]','database'=>false),
				'latitude'=>array('type'=>'input','name'=>'address[latitude]','database'=>false,'validation'=>'number','value'=>0,'class'=>'def_field_small'),
				'longitude'=>array('type'=>'input','name'=>'address[longitude]','database'=>false,'validation'=>'number','value'=>0,'class'=>'def_field_small'),
				'geocode'=>array('type'=>'checkbox','value'=>'1','database'=>false),
				'submit'=>array('type'=>'submitbutton','value'=>'Save','database'=>false),
				'addContent'=>array('type'=>'hidden','value'=>1,'database'=>false)
			),
			'addContent_paired_list'=>array(
				'options'=>array('method'=>'post','action'=>'/modit/ajax/addContent/config'),
				'id'=>array('type'=>'tag','database'=>false),
				'value'=>array('type'=>'hidden'),
				'list'=>array('type'=>'tag','reformatting'=>false,'database'=>false),
				'submit'=>array('type'=>'submitbutton','value'=>'Save','database'=>false),
				'addContent'=>array('type'=>'hidden','value'=>1,'database'=>false)
			),
			'addContent_paired_row'=>array(
				'delete'=>array('type'=>'tag'),
				'name'=>array('type'=>'input','reformatting'=>true,'required'=>true,'name'=>'paired[name][]'),
				'email'=>array('type'=>'input','required'=>true,'validation'=>'email','name'=>'paired[email][]')
			),
			'showSearchForm'=>array(
				'options'=>array('action'=>'showSearchForm'),
				'articles'=>array('type'=>'tag','reformatting'=>false),
				'pagination'=>array('type'=>'tag','reformatting'=>false),
				'showSearchForm'=>array('type'=>'hidden','value'=>1),
				'pagenum'=>array('type'=>'hidden','value'=>0),
				'sortby'=>array('type'=>'hidden','value'=>'name'),
				'sortorder'=>array('type'=>'hidden','value'=>'asc')
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
				'name'=>array('type'=>'tag'),
				'value'=>array('type'=>'tag','reformatting'=>false)
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
			$injector = $this->showPageContent(true);
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
				if ($data['id'] == 0) {
					$this->noAccessError();
				}
				if (array_key_exists('showPageProperties',$_POST)) unset($_POST['showPageProperties']);
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
						$this->logMessage('showPageProperties', sprintf('moving [%d] to [%d] posted[%d]',$data['id'],$p['id'], $_POST['p_id']), 1);
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
				if ($status) {
					$form->addTag('errorMessage','Record added succesfully');
					if ($this->isAjax()) {
						$this->logMessage('showPageProperties', 'executing ajax success return', 3);
						$this->addMessage('Record successfully added');
						return $this->ajaxReturn(array(
								'status'=>'true',
								'html'=>'',
								'url'=>'/modit/config?p_id='.$data['id']
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
		$form->init($this->getTemplate('showPageContent'));
		$flds = $form->buildForm($this->getFields('showPageContent'));
		$types = $this->fetchScalarAll('select distinct groupName from config where type="config" order by groupName');
		$li = array();
		$groups = new Forms();
		$groups->init($this->getTemplate('pageSubform'));
		$subFlds = $groups->buildForm($this->getFields('pageSubform'));
		$grpList = array();
		foreach($types as $key=>$type) {
			$li[] = sprintf('<li><a href="#group_%d">%s</a></li>',$key,$type);
			$items = $this->fetchAll(sprintf('select * from config where groupName = "%s"',$type));
			$itemList = array();
			foreach($items as $subkey=>$item) {
				$itemList[] = sprintf('<tr><td>%s</td><td>%s</td><td><a href="#" onclick="editArticle(%d)"><i class="icon-edit"></i></a></td><td><a href="#"><i class="icon-trash"></i></a></td></tr>',$item['pretty_name'],$item['description'],$item['id']);
			}
			$groups->addTag('key',$key);
			$groups->addTag('title',$type);
			$groups->addTag('configs',implode('',$itemList),false);
			$grpList[$key] = $groups->show();
		}
		$form->addTag('groups',implode('',$li),false);
		$form->addTag('grouplists',implode('',$grpList),false);
		if ($fromMain)
			return $form->show();
		else
			if ($this->isAjax())
				return $this->ajaxResult(array('status'=>'true','html'=>$form->show()));
			else
				$this->show($form->show());
	}

	function showSearchForm($fromMain = false) {
		$form = new Forms();
		$form->init($this->getTemplate('showSearchForm'),array('name'=>'showSearchForm','persist'=>true));
		$frmFields = $form->buildForm($this->getFields('showSearchForm'));
		$this->logMessage("showSearchForm",sprintf("this [%s] fromMain [$fromMain]",print_r($this,true)),1);
		if (count($_POST) == 0)
			if (array_key_exists('formData',$_SESSION) && array_key_exists('configSearchForm', $_SESSION['formData']))
				$_POST = $_SESSION['formData']['configSearchForm'];
			else
				$_POST = array('showSearchForm'=>1,'sortby'=>'name','sortorder'=>'asc');
		if (count($_POST) > 0 && array_key_exists('showSearchForm',$_POST)) {
			$form->addData($_POST);
			if ($form->validate()) {
				$_SESSION['formData']['configSearchForm'] = $form->getAllData();
				$srch = array('type="config"');
				foreach($frmFields as $key=>$value) {
					switch($key) {
						case 'quicksearch':
							if (array_key_exists('opt_quicksearch',$_POST) && $value = $form->getData($key)) {
								if ($_POST['opt_quicksearch'] == 'like' && strpos($value,'%',0) === false) {
									$value = '%'.$value.'%';
								}
								$tmp = array();
								$tmp[] = sprintf(' title %s "%s"',$_POST['opt_quicksearch'],$this->escape_string($value));
								$tmp[] = sprintf(' body %s "%s"',$_POST['opt_quicksearch'],$this->escape_string($value));
								$srch[] = sprintf('(%s)',implode(' or ',$tmp));
							}
							break;
						case 'title':
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
					$perPage = 5;
					$count = $this->fetchScalar(sprintf('select count(n.id) from %s n where 1=1 and %s', $this->m_content, implode(' and ',$srch)));
					$pageNum = max(1,min($pageNum, (floor(($count-1)/$perPage)+1)));
					$form->setData('pagenum', $pageNum);
					$pagination = $this->pagination($count, $perPage, $pageNum,
							array('prev'=>$this->M_DIR.'forms/paginationPrev.html','next'=>$this->M_DIR.'forms/paginationNext.html',
									'pages'=>$this->M_DIR.'forms/paginationPage.html', 'wrapper'=>$this->M_DIR.'forms/paginationWrapper.html'));
					$start = ($pageNum-1)*5;
					$sortorder = 'desc';
					$sortby = 'created';
					if (array_key_exists('sortby',$_POST)) {
						$sortby = $_POST['sortby'];
						$sortorder = $_POST['sortorder'];
					}
					$sql = sprintf('select c.* from %s c where %s order by %s %s limit %d,%d',
						 $this->m_content, implode(' and ',$srch),$sortby, $sortorder, $start,$perPage);
					$recs = $this->fetchAll($sql);
					$this->logMessage('showSearchForm', sprintf('sql [%s] records [%d]',$sql,count($recs)), 2);
					$articles = array();
					$frm = new Forms();
					$frm->init($this->getTemplate('articleList'),array());
					$tmp = $frm->buildForm($this->getFields('articleList'));
					foreach($recs as $article) {
						$frm->getField('value')->addAttribute('reformatting',false);
						switch($article['field_type']) {
							case 'image':
								$tmp = new image();
								$tmp->addAttribute('src',$article['value']);
								$article['value'] = $tmp->show();
								break;
							case 'textarea':
								$article['value'] = $this->subwords($article['value'],20);
								$frm->getField('value')->addAttribute('reformatting',true);
								break;
							case 'paired_list':
								$article['value'] = str_replace('^','<br/>',$article['value']);
								break;
							case 'address':
								$tmp = $this->fetchSingle(sprintf('select * from addresses where id = %d',$article['value']));
								$article['value'] = Address::formatAddress($article['value'],1);
								break;
							case 'text':
							default:
								break;
						}
						$this->logMessage("showSearchForm",sprintf("article [%s]",print_r($article,true)),2);
						$frm->addData($article);
						$articles[] = $frm->show();
					}
					$form->addTag('articles',implode('',$articles),false);
					$form->addTag('pagination',$pagination,false);
				}
			}
		}
		
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
		if (!(array_key_exists('a_id',$_REQUEST) && $_REQUEST['a_id'] > 0 && $data = $this->fetchSingle(sprintf('select * from %s where id = %d', $this->m_content, $_REQUEST['a_id'])))) {
			$this->addError('Cannot add new config variables, please contact the site administrators');
			return $this->ajaxReturn(array('status'=>'false','html'=>''));
		}
		$form->init($this->getTemplate('addContent_'.$data['field_type']),array('name'=>'addContent'));
		$frmFields = $this->getFields('addContent_'.$data['field_type']);
		switch($data['field_type']) {
			case 'text':
				break;
			case 'image':
				break;
			case 'address':
				$tmp = $this->fetchSingle(sprintf('select * from addresses where id = %d',$data['value']));
				//unset($data['name']);
				$form->addData(array('address'=>$tmp));
				$this->logMessage("addContent",sprintf("address form [%s]",print_r($form,true)),2);
				break;
			case 'textarea':
				//$data['value'] = nl2br($data['value']);
				break;
			case 'paired_list':
				$tmp = explode('^',$data['value']);
				$subf = new Forms();
				$subf->init($this->getTemplate('addContent_paired_row'));
				$subFields = $subf->buildForm($this->getFields('addContent_paired_row'));
				$rows = array();
				$this->logMessage("addContent",sprintf("tmp [%s]",print_r($tmp,true)),2);
				foreach($tmp as $key=>$list) {
					$row = explode(':',$list);
					if (count($row) > 1)
						$subf->addData(array('name'=>$row[0],'email'=>$row[1],'delete'=>$key));	// can't start @ 0 as 0 value checkboxes do not get returned
					$this->logMessage("addContent",sprintf("row [%s] form [%s]",print_r($row,true),print_r($subf,true)),2);
					$rows[] = $subf->show();
				}
				$data['rows'] = implode('',$rows);
				break;
			default:
				break;
		}

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

		$frmFields = $form->buildForm($frmFields);
		$form->addData($data);
		$status = 'false';	//assume it failed
		if (count($_POST) > 0 && array_key_exists('addContent',$_POST)) {
			$form->addData($_POST);
			if ($form->validate()) {
				$id = $_POST['a_id'];
				$this->beginTransaction();
				$status = true;
				switch($data['field_type']) {
					case 'address':
						$address = $_POST['address'];
						if (array_key_exists("geocode",$_POST)) {
							$status &= $this->geocode($address,$address["latitude"],$address["longitude"]);
						}
						$flds = array();
						foreach($address as $key=>$value) {
							$flds[$key] = $value;
						}
						if ((int)($_POST['value']) > 0)
							$obj = new preparedStatement(sprintf('update addresses set %s where id = %d',implode('=?, ',array_keys($flds)).'=?',$data['value']));
						else {
							unset($flds['value']);
							$flds['ownertype'] = 'config';
							$flds['ownerid'] = $id;
							$flds['addresstype'] = $this->fetchScalar(sprintf('select id from code_lookups where type="storeAddressTypes" and code = "%s"', str_replace('-','',$data['name'])));
							$obj = new preparedStatement(sprintf('insert into addresses(%s) values(%s?)',implode(', ',array_keys($flds)),str_repeat('?, ',count($flds)-1)));
						}
						$obj->bindParams(array_merge(array(str_repeat('s',count($flds))),array_values($flds)));
						$status = $status && $obj->execute();
						if ((int)$_POST['value'] == 0) {
							$rec = $this->insertId();
							$form->setData('value',$rec);
							$this->logMessage('addContent',sprintf('insertid [%s] data [%s]',$rec, print_r($data,true)),1);
							$this->execute(sprintf('update %s set value="%s" where id = %d', $this->m_content, $rec, $data['id']));
						}
						break;
					case 'paired_list':
						$input = $_POST['paired'];
						if (array_key_exists('delete',$input)) {
							$this->logMessage("addContent",sprintf("performing some deleted [%s]",print_r($input['delete'],true)),2);
							foreach($input['delete'] as $key=>$value) {
								unset($input['name'][$value]);
								unset($input['email'][$value]);
							}
							$this->logMessage("addContent",sprintf("after delete [%s]",print_r($input,true)),2);
						}
						$tmp = array();
						foreach($input['name'] as $key=>$name) {
							$email = $input['email'][$key];
							$tmp[] = sprintf('%s:%s',$name,$email);
						}
						$form->setData('value',implode('^',$tmp));
						$this->logMessage("addContent",sprintf("data value [%s] tmp [%s]",$data['value'],print_r($tmp,true)),2);
						break;
					default:
						break;
				}
				unset($frmFields['a_id']);
				unset($frmFields['options']);
				$flds = array();
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
				if ($status = $status && $stmt->execute()) {
					if ($id == 0) $id = $this->insertId();
					if ($status) {
						$this->commitTransaction();
						return $this->ajaxReturn(array(
							'status' => 'true',
							'url' => '/modit/config'
						),array('<code>'=>1));
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
			),array('<code>'=>1));
		}
		elseif ($fromMain)
			return $form->show();
		else
			return $this->show($form->show());
	}

	function deleteArticle() {
		if (array_key_exists('j_id',$_REQUEST)) {
			$id = $_REQUEST['j_id'];
			$curr = $this->fetchScalar(sprintf('select article_id from %s where id = %d',$this->m_junction,$id));
			$this->logMessage('deleteArticle', sprintf('deleting news junction %d for article %d',$id,$curr), 2);
			$this->beginTransaction();
			$this->execute(sprintf('delete from %s where id = %d',$this->m_junction,$id));
			if (($remining = $this->fetchScalar(sprintf('select count(0) from %s where article_id = %d',$this->m_junction,$curr))) == 0) {
				$this->logMessage('deleteArticle', sprintf('deleting article %d - no more references',$curr), 2);
				$this->execute(sprintf('delete from %s where id = %d',$this->m_content,$curr));
			}
			$this->commitTransaction();
			return $this->ajaxReturn(array('status'=>'true'));
		}
	}

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

	function hasFunctionAccess($method) {
		if (parent::hasFunctionAccess($method)) return true;
		return true;
	}

	function addPairedRow() {
		$form = new Forms();
		$form->init($this->getTemplate('addContent_paired_row'));
		$fields = $form->buildForm($this->getFields('addContent_paired_row'));
		return $this->ajaxReturn(array('status'=>'true','html'=>$form->show()));
	}

	function taxList() {
		$outer = new Forms();
		$outer->init($this->getTemplate('taxList'));
		$flds = $outer->buildForm($this->getFields('taxList'));
		$taxes = $this->fetchAll(sprintf('select t.*,c.country,p.province from taxes t, countries c, provinces p where p.id = t.province_id and c.id = p.country_id and t.deleted = 0 order by c.country, p.province'));
		$inner = new Forms();
		$inner->init($this->getTemplate('taxRow'));
		$rowFlds = $inner->buildForm($this->getFields('taxRow'));
		$result = array();
		foreach($taxes as $key=>$tax) {
			$inner->addData($tax);
			$result[] = $inner->show();
		}
		$outer->addTag('taxRows',implode('',$result),false);
		return $this->ajaxReturn(array('html'=>$outer->show(),'status'=>'true'));
	}

	function editTax() {
		$outer = new Forms();
		$outer->init($this->getTemplate('editTax'));
		$flds = $outer->buildForm($this->getFields('editTax'));
		if (!($data = $this->fetchSingle(sprintf('select t.*, p.country_id from taxes t, provinces p where t.id = %d and p.id = t.province_id',$_REQUEST['t_id']))))
			$data = array('id'=>0);
		$outer->addData($data);
		if (count($_POST) > 0 && array_key_exists('taxEditor',$_POST)) {
			$outer->addData($_POST);
			if ($outer->validate()) {
				$values = array();
				foreach($flds as $key=>$value) {
					if (!array_key_exists('database',$value))
						$values[$key] = $outer->getData($key);
				}
				if ($_POST['t_id'] == 0)
					$stmt = $this->prepare(sprintf('insert into taxes(%s) values(%s)',implode(',',array_keys($values)),str_repeat('?,',count($values)-1).'?'));
				else
					$stmt = $this->prepare(sprintf('update taxes set %s where id = %d',implode('=?,',array_keys($values)).'=?',$_POST['t_id']));
				$stmt->bindParams(array_merge(array(str_repeat('s',count($values))),array_values($values)));
				if ($stmt->execute()) {
					$this->addMessage('Record Updated');
					$outer->init($this->getTemplate('taxSuccess'));
				}
				else
					$this->addError('An Error occurred');
			}
		}
		return $this->ajaxReturn(array('status'=>'true','html'=>$outer->show()));
	}

	function formList() {
		$outer = new Forms();
		$outer->init($this->getTemplate('formList'));
		$flds = $outer->buildForm($this->getFields('formList'));
		$forms = $this->fetchAll(sprintf('select h.*, m.title from htmlForms h, modules m where m.id = h.class order by m.title, h.type'));
		$inner = new Forms();
		$inner->init($this->getTemplate('formRow'));
		$rowFlds = $inner->buildForm($this->getFields('formRow'));
		$result = array();
		foreach($forms as $key=>$form) {
			$form['html_sample'] = $this->subwords(htmlspecialchars($form['html']),20);
			$inner->addData($form);
			$result[] = $inner->show();
		}
		$outer->addTag('formRows',implode('',$result),false);
		return $this->ajaxReturn(array('html'=>$outer->show(),'status'=>'true'));
	}

	function editForm() {
		$outer = new Forms();
		$outer->init($this->getTemplate('editForm'));
		$flds = $outer->buildForm($this->getFields('editForm'));
		if (!($data = $this->fetchSingle(sprintf('select h.* from htmlForms h where h.id = %d',$_REQUEST['f_id']))))
			$data = array('id'=>0);
		$outer->addData($data);
		if (count($_POST) > 0 && array_key_exists('formEditor',$_POST)) {
			$outer->addData($_POST);
			if ($outer->validate()) {
				$values = array();
				foreach($flds as $key=>$value) {
					if (!array_key_exists('database',$value))
						$values[$key] = $outer->getData($key);
				}
				if ($_POST['f_id'] == 0)
					$stmt = $this->prepare(sprintf('insert into htmlForms(%s) values(%s)',implode(',',array_keys($values)),str_repeat('?,',count($values)-1).'?'));
				else
					$stmt = $this->prepare(sprintf('update htmlForms set %s where id = %d',implode('=?,',array_keys($values)).'=?',$_POST['f_id']));
				$stmt->bindParams(array_merge(array(str_repeat('s',count($values))),array_values($values)));
				if ($stmt->execute()) {
					$this->addMessage('Record Updated');
					$outer->init($this->getTemplate('formSuccess'));
				}
				else
					$this->addError('An Error occurred');
			}
		}
		return $this->ajaxReturn(array('status'=>'true','html'=>$outer->show(false)));
	}
}

?>