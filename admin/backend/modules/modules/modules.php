<?php

class modules extends Backend {

	private $m_content = 'fetemplates';
	private $m_perrow = 5;
	
	public function __construct() {
		$this->M_DIR = 'backend/modules/modules/';
		$this->setTemplates(
			array(
				'main'=>$this->M_DIR.'modules.html',
				'form'=>$this->M_DIR.'forms/form.html',
				'showContentTree'=>$this->M_DIR.'forms/contenttree.html',
				'modulesInfo'=>$this->M_DIR.'forms/modulesInfo.html',
				'articleList'=>$this->M_DIR.'forms/articleList.html',
				'showSearchForm'=>$this->M_DIR.'forms/searchForm.html',
				'addContent'=>$this->M_DIR.'forms/addContent.html',
				'buildOptions'=>$this->M_DIR.'forms/buildOptions.html',
				'subtemplates'=>$this->M_DIR.'forms/subtemplate.html',
				'placements'=>$this->M_DIR.'forms/placement.html',
				'addPlacement'=>$this->M_DIR.'forms/addPlacement.html',
				'addPlacementSuccess'=>$this->M_DIR.'forms/addPlacementSuccess.html',
				'deletePlacement'=>$this->M_DIR.'forms/deletePlacements.html',
				'addSubtemplate'=>$this->M_DIR.'forms/addSubtemplate.html',
				'addSubtemplateSuccess'=>$this->M_DIR.'forms/addSubtemplateSuccess.html',
				'deleteSubtemplate'=>$this->M_DIR.'forms/deleteSubtemplate.html',
				'editHtml'=>$this->M_DIR.'forms/editHtml.html'
			)
		);
		$this->setFields(array(
			'addPlacement'=>array(
				'module_name'=>array('type'=>'input','required'=>true),
				'template_id'=>array('type'=>'select','required'=>true,'sql'=>'select distinct t.template_id, t.title
from templates t, templates t1
where t.template_id = t1.template_id
and t.version = (
select max(t1.version)
from templates t2
where t2.template_id = t1.template_id
group by t1.template_id, t1.version
) union select 0," All" order by 2,1'),
				'fetemplate_id'=>array('type'=>'hidden'),
				'save'=>array('type'=>'submitbutton','value'=>'Save','database'=>false),
				'addPlacementForm'=>array('type'=>'hidden','value'=>1,'database'=>false),
				'options'=>array('name'=>'addPlacementForm','database'=>false)
			),
			'addSubtemplate'=>array(
				'submodule_id'=>array('type'=>'select','required'=>true,'sql'=>'select f.id, concat(title, ": ", module_name ) from fetemplates f, modules m where f.module_id = m.id order by title,module_name'),
				'variable'=>array('type'=>'input','required'=>true),
				'save'=>array('type'=>'submitbutton','value'=>'Save','database'=>false),
				'addSubtemplateForm'=>array('type'=>'hidden','value'=>1,'database'=>false),
				'fetemplate_id'=>array('type'=>'hidden'),
				'location'=>array('type'=>'select','options'=>array('inner'=>'Inner','outer'=>'Outer'),'required'=>true),
				'options'=>array('name'=>'addSubtemplateForm','database'=>false)
			),
			'subtemplate'=>array(
			),
			'placement'=>array(
			),
			'buildOptions'=>array(
				'key'=>array('type'=>'input','name'=>'options[key][]'),
				'value'=>array('type'=>'input','name'=>'options[value][]')
			),
			'addContent'=>array(
				'options'=>array('method'=>'post','action'=>'/modit/ajax/addContent/modules'),
				'id'=>array('type'=>'tag','database'=>false),
				'module_name'=>array('type'=>'input','required'=>true),
				'module_id'=>array('type'=>'select','id'=>'module_id','sql'=>'select id,title from modules where enabled = 1 and frontend = 1 order by title','required'=>true,'showNone'=>true),
				'module_function'=>array('type'=>'input','required'=>true),
				'outer_html'=>array('type'=>'input','required'=>true),
				'inner_html'=>array('type'=>'input','required'=>false),
				'records'=>array('type'=>'input','required'=>true),
				'sort_by'=>array('type'=>'input'),
				'featured'=>array('type'=>'checkbox','value'=>1),
				'where_clause'=>array('type'=>'input'),
				'configuration'=>array('type'=>'input'),
				//'misc_options'=>array('type'=>'input'),
				'template_allow_override'=>array('type'=>'checkbox','value'=>1),
				'parm1'=>array('type'=>'input'),
				'parm2'=>array('type'=>'input'),
				'parm3'=>array('type'=>'input'),
				'parm4'=>array('type'=>'input'),
				'parm5'=>array('type'=>'input'),
				'submit'=>array('type'=>'submitbutton','database'=>false,'value'=>'Save'),
				'addContent'=>array('type'=>'hidden','value'=>1,'database'=>false),
				'internal_use'=>array('type'=>'checkbox','value'=>1),
				'usage'=>array('type'=>'select','database'=>false,'required'=>false),
				'editOuter'=>array('type'=>'button','value'=>'Edit','onclick'=>'editHtml(this);return false;','database'=>false),
				'editInner'=>array('type'=>'button','value'=>'Edit','onclick'=>'editHtml(this);return false;','database'=>false)
			),
			'showSearchForm'=>array(
				'options'=>array('action'=>'showSearchForm'),
				'opt_module_name'=>array('type'=>'select','name'=>'opt_module_name','lookup'=>'search_options'),
				'opt_module_id'=>array('type'=>'select','name'=>'opt_module_id','lookup'=>'search_options'),
				'opt_module_function'=>array('type'=>'select','name'=>'opt_module_function','lookup'=>'search_options'),
				'opt_outer_html'=>array('type'=>'select','name'=>'opt_outer_html','lookup'=>'search_options'),
				'opt_inner_html'=>array('type'=>'select','name'=>'opt_inner_html','lookup'=>'search_options'),
				'module_name'=>array('type'=>'input','required'=>false),
				'module_id'=>array('type'=>'select','sql'=>'select id, title from modules where enabled = 1 and frontend = 1 order by title'),
				'module_function'=>array('type'=>'input','required'=>false),
				'outer_html'=>array('type'=>'input','required'=>false),
				'inner_html'=>array('type'=>'input','required'=>false),
				'showSearchForm'=>array('type'=>'hidden','value'=>1),
				'pagenum'=>array('type'=>'hidden','value'=>1),
				'sortby'=>array('type'=>'hidden','value'=>'module_name'),
				'sortorder'=>array('type'=>'hidden','value'=>'asc'),
				'pager'=>array('type'=>'select','required'=>true,'value'=>$this->m_perrow,'lookup'=>'paging','id'=>'pager'),
				'quicksearch'=>array('type'=>'input','name'=>'quicksearch','required'=>false),
				'opt_quicksearch'=>array('type'=>'hidden','value'=>'like'),
				'submit'=>array('type'=>'submitbutton','value'=>'Search'),
				't_id'=>array('type'=>'input','required'=>false,'name'=>'t_id')
			),
			'main' => array(
				'test'=>array('type'=>'tag')
			),
			'articleList' => array(
				'id'=>array('type'=>'tag'),
				'module_name'=>array('type'=>'tag'),
				'module_function'=>array('type'=>'tag'),
				'inner_html'=>array('type'=>'tag'),
				'outer_html'=>array('type'=>'tag')
			),
			'editHtml'=>array(
				'class'=>array('type'=>'hidden'),
				'file'=>array('type'=>'hidden'),
				'save'=>array('type'=>'submitbutton','value'=>'Save'),
				'editHtml'=>array('type'=>'hidden','value'=>'1')
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
			$injector = $this->showSearchForm(true);
		}
		$form->addTag('injector', $injector, false);
		return $form->show();
	}

	function showSearchForm($fromMain = false) {
		$form = new Forms();
		$form->init($this->getTemplate('showSearchForm'),array('name'=>'showSearchForm','persist'=>true,'action'=>'showSearchForm'));
		$frmFields = $form->buildForm($this->getFields('showSearchForm'));
		if (count($_POST) == 0 && array_key_exists('formData',$_SESSION) && array_key_exists('modulesSearchForm', $_SESSION['formData']))
			$_POST = $_SESSION['formData']['modulesSearchForm'];
		if (count($_POST) > 0 && array_key_exists('showSearchForm',$_POST)) {
			$form->addData($_POST);
			if ($form->validate()) {
				$_SESSION['formData']['modulesSearchForm'] = $form->getAllData();
				$srch = array();
				foreach($frmFields as $key=>$value) {
					switch($key) {
						case 't_id':
							$this->logMessage(__FUNCTION__,sprintf('key [%s] value [%s]',print_r($key,true),print_r($value,true)),1);
							if ($form->getData($key) > 0)
								$srch = array(sprintf(' n.id = %s',$form->getData($key)));
							break;
						case 'module_function':
						case 'module_id':
						case 'inner_html':
						case 'outer_html':
						case 'module_name':
							if (array_key_exists('opt_'.$key,$_POST) && $value = $form->getData($key)) {
								if ($opt = $_POST['opt_'.$key]) {
									if ($opt == 'like' && strpos($value,'%',0) === false) {
										$value = '%'.$value.'%';
									}
									$srch[] = sprintf(' %s %s "%s"',$key,$opt,$this->escape_string($value));
								}
							}
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
					if (array_key_exists('pager',$_REQUEST)) $perPage = $_REQUEST['pager'];
					if (array_key_exists('pager',$_POST)) $perPage = $_POST['pager'];
					$count = $this->fetchScalar(sprintf('select count(n.id) from %s n where 1=1 and %s', $this->m_content, implode(' and ',$srch)));
					$pageNum = max(1,min($pageNum, (floor(($count-1)/$perPage)+1)));
					$form->setData('pagenum', $pageNum);
					$pagination = $this->pagination($count, $perPage, $pageNum,
							array('prev'=>$this->M_DIR.'forms/paginationPrev.html','next'=>$this->M_DIR.'forms/paginationNext.html',
									'pages'=>$this->M_DIR.'forms/paginationPage.html', 'wrapper'=>$this->M_DIR.'forms/paginationWrapper.html'));
					$start = ($pageNum-1)*$perPage;
					$sortorder = 'desc';
					$sortby = 'module_name';
					if (array_key_exists('sortby',$_POST)) {
						$sortby = $_POST['sortby'];
						$sortorder = $_POST['sortorder'];
					}
					$sql = sprintf('select n.*, m.title as class from %s n, modules m where n.module_id = m.id and %s order by %s %s limit %d,%d',
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
		$form->init($this->getTemplate('addContent'),array('name'=>'addContent','action'=>'addContent'));
		$frmFields = $this->getFields('addContent');
		if (!(array_key_exists('m_id',$_REQUEST) && $_REQUEST['m_id'] > 0 && $data = $this->fetchSingle(sprintf('select * from %s where id = %d', $this->m_content, $_REQUEST['m_id'])))) {
			$data = array('id'=>0,'published'=>false); 
		}
		if ($data['id'] > 0)
			$frmFields['usage']['sql'] = sprintf("SELECT p.id, concat('Template: ', t.title, ' ', t.version)
FROM `modules_by_page` p, templates t
WHERE fetemplate_id=%d and page_type='T' and t.id = p.page_id
union
SELECT mp.id, concat('Page: ', c.title, ' ', p.version)
FROM `modules_by_page` mp, pages p, content c
WHERE mp.fetemplate_id=%d and mp.page_type='P' and p.id = mp.page_id and c.id = p.content_id
order by 2",$data['id'],$data['id']);
		$form->addTag('options_list',$this->buildOptions($data),false);
		$form->addTag('subtemplates',$this->buildTemplates($data),false);
		$form->addTag('placements',$this->buildPlacements($data),false);
		$frmFields = $form->buildForm($frmFields);
		$form->addData($data);
		$status = 'false';	//assume it failed
		if (count($_POST) > 0 && array_key_exists('addContent',$_POST)) {
			$form->addData($_POST);
			if ($form->validate()) {
				$id = $_POST['m_id'];
				unset($frmFields['m_id']);
				unset($frmFields['options']);
				foreach($frmFields as $key=>$fld) {
					if (!(array_key_exists('database',$fld) && $fld['database'] == false)) {
						//$values[] = $form->getData($fld['name']);	//$_REQUEST[$fld['name']];
						//if ($data['id'] > 0)
						//	$flds[sprintf('%s = ?',$fld['name'])] = $form->getData($fld['name']);//$_REQUEST[$fld['name']];
						//else
							$flds[$fld['name']] = $form->getData($fld['name']);
					}
				}
				$tmp = array();
				if (array_key_exists('options',$_POST)) {
					$options = $_POST['options'];
					foreach($options['key'] as $key=>$value) {
						if (strlen($value) > 0) {
							$tmp[] = $value.':'.$options['value'][$key];
						}
					}
				}
				$flds['misc_options'] = implode('^',$tmp);
				if ($id == 0) {
					$stmt = $this->prepare(sprintf('insert into %s(%s) values(%s)', $this->m_content, implode(',',array_keys($flds)), str_repeat('?,', count($flds)-1).'?'));
				}
				else {
					$stmt = $this->prepare(sprintf('update %s set %s where id = %d', $this->m_content, implode('=?, ',array_keys($flds)).'=? ',$data['id']));
				}
				$stmt->bindParams(array_merge(array(str_repeat('s', count($flds))),array_values($flds)));
				$this->beginTransaction();
				if ($stmt->execute()) {
					if ($id == 0) $id = $this->insertId();
					$this->commitTransaction();
					return $this->ajaxReturn(array(
						'status' => 'true',
						'url' => sprintf('/modit/modules?m_id=%d',$data['id'])
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
	
	function deleteContent() {
		$status = 'false';
		if (array_key_exists('p_id',$_REQUEST)) {
			$id = $_REQUEST['p_id'];
			$this->logMessage('deleteContent',sprintf('delete ad folder %d',$id),1);
			//
			//	currently the ads are not deleted, just folder membership. 
			//	not sure if that is good or bad. ads can be in more than 1 folder
			//	disable ads in no folder?
			//
			$this->beginTransaction();
			$tmp = $this->execute(sprintf('delete from %s where folder_id = %d',$this->m_junction,$id));
			if ($tmp) $tmp = $this->execute(sprintf('update advert a set a.published = 0 where a.id not in (select advert_id from %s)',$this->m_junction));
			if ($tmp) $tmp = $this->execute(sprintf('delete from %s where id = %d',$this->m_tree,$id));
			if ($tmp) {
				$this->commitTransaction();
				$this->addMessage('Folder Deleted');
				$status = 'true';
			}
			else {
				$this->rollbackTransaction();
				$this->addMessage('Delete failed');
			}
		}
		return $this->ajaxReturn(array('status'=>$status));
	}

	function buildOptions($data) {
		$return = array();
		$tmp = new Common();
		$tmp->parseOptions(array_key_exists('misc_options',$data) ? $data['misc_options']:'');
		$form = new Forms();
		$form->init($this->getTemplate('buildOptions'));
		$flds = $form->buildForm($this->getFields('buildOptions'));
		foreach($tmp->m_options as $key=>$value) {
			$form->addData(array('key'=>$key,'value'=>$value));
			$return[] = $form->show();
		}
		return implode('',$return);
	}

	function getOptionsRow() {
		$form = new Forms();
		$form->init($this->getTemplate('buildOptions'));
		$flds = $form->buildForm($this->getFields('buildOptions'));
		return $this->ajaxReturn(array('status'=>'true','html'=>$form->show()));
	}

	function buildTemplates($data = null) {
		$byAjax = is_null($data);
		if (is_null($data))
			$data = array('id'=>array_key_exists('fetemplate_id',$_REQUEST) ? $_REQUEST['fetemplate_id']:0);
		//if ($data['id'] <= 0) return "";
		$templates = $this->fetchAll(sprintf('select st.*, t.module_name, m.title from sub_templates st, fetemplates t, modules m where fetemplate_id = %d and t.id = st.submodule_id and m.id = t.module_id',$data['id']));
		$form = new Forms();
		$form->init($this->getTemplate('subtemplates'));
		$flds = $form->buildForm($this->getFields('subtemplates'));
		$return = array();
		foreach($templates as $key=>$rec) {
			$form->addData($rec);
			$return[] = $form->show();
		}
		if ($byAjax)
			return $this->ajaxReturn(array('status'=>'true','html'=>implode('',$return)));
		else
			return implode('',$return);
	}

	function buildPlacements($data = null) {
		$byAjax = is_null($data);
		if (is_null($data))
			$data = array('id'=>array_key_exists('fetemplate_id',$_REQUEST) ? $_REQUEST['fetemplate_id']:0);
		$templates = $this->fetchAll(sprintf('select p.*, t.title from fetemplate_placement p, templates t where p.fetemplate_id = %d and t.template_id = p.template_id and t.version = (select max(t1.version) from templates t1 where t1.template_id = t.template_id) union select p.*," All" from fetemplate_placement p where template_id = 0 and fetemplate_id = %d order by 5,4',$data['id'],$data['id']));
		$form = new Forms();
		$form->init($this->getTemplate('placements'));
		$flds = $form->buildForm($this->getFields('placements'));
		$return = array();
		foreach($templates as $key=>$rec) {
			$form->addData($rec);
			$return[] = $form->show();
		}
		if ($byAjax)
			return $this->ajaxReturn(array('status'=>'true','html'=>implode('',$return)));
		else
			return implode('',$return);
	}

	function addSubtemplate() {
		$form = new Forms();
		$form->init($this->getTemplate('addSubtemplate'));
		$flds = $form->buildForm($this->getFields('addSubtemplate'));
		if (array_key_exists('s_id',$_REQUEST) && $_REQUEST['s_id'] > 0) {
			$data = $this->fetchSingle(sprintf('select * from sub_templates where id = %d',$_REQUEST['s_id']));
		}
		else $data = array('id'=>0,'fetemplate_id'=>$_REQUEST['fetemplate_id']);
		$form->addData($data);
		if (count($_POST) > 0 && array_key_exists('addSubtemplateForm',$_POST)) {
			$form->addData($_POST);
			if ($form->validate()) {
				$values = array();
				foreach($flds as $key=>$fld) {
					if (!(array_key_exists('database',$fld) && $fld['database'] == false))
						$values[$key] = $form->getData($key);
				}
				if ($_REQUEST['s_id'] == 0)
					$stmt = $this->prepare(sprintf('insert into sub_templates(%s) values(%s)',implode(',',array_keys($values)),str_repeat('?,',count($values)-1).'?'));
				else
					$stmt = $this->prepare(sprintf('update sub_templates set %s where id = %d',implode('=?, ',array_keys($values)).'=?',$_REQUEST['s_id']));
				$stmt->bindParams(array_merge(array(str_repeat('s', count($values))),array_values($values)));
				if ($stmt->execute()) {
					$form = new Forms();
					$form->addData($_POST);
					$form->init($this->getTemplate('addSubtemplateSuccess'));
				}
				else
					$this->addError('An Error Occurred');
			}
		}
		return $this->ajaxReturn(array('status'=>'true','html'=>$form->show()));
	}	

	function deleteSubtemplate() {
		if (count($_POST) > 0 && array_key_exists('deleteSubtemplateForm',$_POST)) {
			$this->execute(sprintf('delete from sub_templates where id = %d and fetemplate_id = %d',$_POST['s_id'],$_POST['p_id']));
		}
		$form = new Forms();
		$form->init($this->getTemplate('deleteSubtemplate'));
		$form->addData($_POST);
		return $this->ajaxReturn(array('status'=>'true','html'=>$form->show()));
	}

	function deleteArticle() {
		if (array_key_exists('j_id',$_POST)) {
			$this->execute(sprintf('delete from fetemplates where id = %d',$_POST['j_id']));
		}
		return $this->ajaxReturn(array('status'=>'true'));
	}

	function addPlacement() {
		$form = new Forms();
		$form->init($this->getTemplate('addPlacement'));
		$flds = $form->buildForm($this->getFields('addPlacement'));
		if (array_key_exists('s_id',$_REQUEST) && $_REQUEST['s_id'] > 0) {
			$data = $this->fetchSingle(sprintf('select * from fetemplate_placement where id = %d',$_REQUEST['s_id']));
		}
		else $data = array('id'=>0,'fetemplate_id'=>$_REQUEST['fetemplate_id']);
		$form->addData($data);
		if (count($_POST) > 0 && array_key_exists('addPlacementForm',$_POST)) {
			$form->addData($_POST);
			if ($form->validate()) {
				$values = array();
				foreach($flds as $key=>$fld) {
					if (!(array_key_exists('database',$fld) && $fld['database'] == false))
						$values[$key] = $form->getData($key);
				}
				if ($_REQUEST['s_id'] == 0)
					$stmt = $this->prepare(sprintf('insert into fetemplate_placement(%s) values(%s)',implode(',',array_keys($values)),str_repeat('?,',count($values)-1).'?'));
				else
					$stmt = $this->prepare(sprintf('update fetemplate_placement set %s where id = %d',implode('=?, ',array_keys($values)).'=?',$_REQUEST['s_id']));
				$stmt->bindParams(array_merge(array(str_repeat('s', count($values))),array_values($values)));
				if ($stmt->execute()) {
					$form = new Forms();
					$form->addData($_POST);
					$form->init($this->getTemplate('addPlacementSuccess'));
				}
				else
					$this->addError('An Error Occurred');
			}
		}
		return $this->ajaxReturn(array('status'=>'true','html'=>$form->show()));
	}	

	function deletePlacement() {
		if (count($_POST) > 0 && array_key_exists('deletePlacementForm',$_POST)) {
			$this->execute(sprintf('delete from fetemplate_placement where id = %d and fetemplate_id = %d',$_POST['s_id'],$_POST['p_id']));
		}
		$form = new Forms();
		$form->init($this->getTemplate('deletePlacement'));
		$form->addData($_POST);
		return $this->ajaxReturn(array('status'=>'true','html'=>$form->show()));
	}

	function editHtml() {
		$class = $this->fetchSingle(sprintf("select * from modules where id = %d",$_REQUEST["class"]));
		$file = sprintf("frontend/forms/%s/%s",$class["classname"],$_REQUEST["file"]);
		if ($fh = @fopen($file,"r")) {
			$html = fread($fh,filesize($file));
			fclose($fh);
		}
		else $html = "Warning: File does not exist";
		$outer = new Forms();
		$outer->init($this->getTemplate(__FUNCTION__));
		$class["html"] = $html;
		$outer->addData($class);
		$outer->addData($_REQUEST);
		if (array_key_exists(__FUNCTION__,$_REQUEST) && $_REQUEST[__FUNCTION__] != 0) {
			$bak = str_replace(".html",".bak",$file);
			if ($nh = fopen($bak,"w")) {
				$i = fwrite($nh,$html);
				if ($fh = fopen($file,"w")) {
					fwrite($fh,$_REQUEST["html"]);
					fclose($fh);
				}
				else {
					$this->logMessage(__FUNCTION__,sprintf("Failed to open file [%s] status [%s]", $file, $fh),1,true,true);
				}
			}
			else {
				$this->logMessage(__FUNCTION__,sprintf("Failed to open file [%s] status [%s]", $bak, $nh),1,true,true);
			}
		}
		$outer->setOption('formDelimiter','<<|>>');
		$outer->setOption("leaveConditionals",1);
		$outer->setOption("leaveCode",1);
		$fields = $outer->buildForm($this->getFields(__FUNCTION__));
		return $outer->ajaxReturn(array("status"=>true,"html"=>$outer->show()));
	}
}

?>
