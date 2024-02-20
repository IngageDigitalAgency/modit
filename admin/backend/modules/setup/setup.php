<?php

class setup extends Backend {

	private $m_content = 'modules';
	private $m_relations = 'cross_functionality';
	private $m_perrow = 10;
	
	public function __construct() {
		$this->M_DIR = 'backend/modules/setup/';
		$this->setTemplates(
			array(
				'main'=>$this->M_DIR.'setup.html',
				'form'=>$this->M_DIR.'forms/form.html',
				'showContentTree'=>$this->M_DIR.'forms/contenttree.html',
				'setupInfo'=>$this->M_DIR.'forms/setupInfo.html',
				'articleList'=>$this->M_DIR.'forms/articleList.html',
				'showSearchForm'=>$this->M_DIR.'forms/searchForm.html',
				'addContent'=>$this->M_DIR.'forms/addContent.html',
				'addContentSuccess'=>$this->M_DIR.'forms/addContentSuccess.html',
				'relationsRow'=>$this->M_DIR.'forms/relationsRow.html',
				'editRelation'=>$this->M_DIR.'forms/loadRelations.html',
				'editSuccess'=>$this->M_DIR.'forms/editSuccess.html'
			)
		);
		$this->setFields(array(
			'addContent'=>array(
				'options'=>array('method'=>'post','action'=>'/modit/ajax/addContent/setup','database'=>false),
				'id'=>array('type'=>'tag','database'=>false),
				'title'=>array('type'=>'input','required'=>true,'name'=>'title'),
				'enabled'=>array('type'=>'checkbox','value'=>1,'name'=>'enabled'),
				'admin'=>array('type'=>'checkbox','value'=>1,'name'=>'admin'),
				'frontend'=>array('type'=>'checkbox','value'=>1,'name'=>'frontend'),
				'backend'=>array('type'=>'checkbox','value'=>1,'name'=>'backend'),
				'hidden'=>array('type'=>'checkbox','value'=>1,'name'=>'hidden'),
				'sort'=>array('type'=>'input','value'=>999,'name'=>'sort'),
				'submit'=>array('type'=>'submitButton','database'=>false,'value'=>'Save Changes'),
				'addContent'=>array('type'=>'hidden','database'=>false)
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
				'sortby'=>array('type'=>'hidden','value'=>'title'),
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
				'title'=>array('type'=>'tag'),
				'admin'=>array('type'=>'booleanIcon'),
				'enabled'=>array('type'=>'booleanIcon'),
				'backend'=>array('type'=>'booleanIcon'),
				'frontend'=>array('type'=>'booleanIcon'),
				'hidden'=>array('type'=>'booleanIcon')
			),
			'relationsRow'=>array(
				'enabled'=>array('type'=>'booleanIcon')
			),
			'editRelation'=>array(
				'editRelation'=>array('type'=>'hidden','value'=>1,'database'=>false),
				'enabled'=>array('type'=>'checkbox','value'=>1),
				'submit'=>array('type'=>'submitButton','database'=>false,'value'=>'Save Changes'),
				'p_id'=>array('type'=>'tag','database'=>false)
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
		$form->addData($_REQUEST);
		if (array_key_exists('pagenum',$_REQUEST))
			$pageNum = $_REQUEST['pagenum'];
		else
			$pageNum = 1;	// no 0 based calcs
		$perPage = $this->m_perrow;
		if (array_key_exists('pager',$_REQUEST)) $perPage = $_REQUEST['pager'];
		$count = $this->fetchScalar(sprintf('select count(n.id) from %s n', $this->m_content));
		$pageNum = max(1,min($pageNum, (floor(($count-1)/$perPage)+1)));
		$form->setData('pagenum', $pageNum);
		$pagination = $this->pagination($count, $perPage, $pageNum,
				array('prev'=>$this->M_DIR.'forms/paginationPrev.html','next'=>$this->M_DIR.'forms/paginationNext.html',
						'pages'=>$this->M_DIR.'forms/paginationPage.html', 'wrapper'=>$this->M_DIR.'forms/paginationWrapper.html'));
		$start = ($pageNum-1)*$perPage;
		$sortorder = 'asc';
		$sortby = 'title';
		if (array_key_exists('sortby',$_POST)) {
			$sortby = $_POST['sortby'];
			$sortorder = $_POST['sortorder'];
		}
		$sql = sprintf('select m.* from %s m order by %s %s limit %d,%d',
			 $this->m_content, $sortby, $sortorder, $start,$perPage);
		$recs = $this->fetchAll($sql);
		$this->logMessage('showSearchForm', sprintf('sql [%s] records [%d]',$sql,count($recs)), 2);
		$articles = array();
		foreach($recs as $article) {
			$frm = new Forms();
			$frm->init($this->getTemplate('articleList'),array());
			$article['ent_enabled'] = $this->fetchScalar(sprintf('select count(0) from cross_functionality where enabled = 1 and (related_from = %d or related_to = %d)',$article['id'],$article['id']));
			$article['ent_disabled'] = $this->fetchScalar(sprintf('select count(0) from cross_functionality where enabled = 0 and (related_from = %d or related_to = %d)',$article['id'],$article['id']));
			$tmp = $frm->buildForm($this->getFields('articleList'));
			$frm->addData($article);
			$articles[] = $frm->show();
		}
		$form->addTag('articles',implode('',$articles),false);
		$form->addTag('pagination',$pagination,false);
		
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
		if (!(array_key_exists('m_id',$_REQUEST) && $_REQUEST['m_id'] > 0 && $data = $this->fetchSingle(sprintf('select * from %s where id = %d', $this->m_content, $_REQUEST['m_id'])))) {
			$data = array('id'=>0,'published'=>false); 
		}
		$frmFields = $form->buildForm($this->getFields('addContent'));
		$form->addData($data);
		$status = 'false';	//assume it failed
		if (count($_POST) > 0 && array_key_exists('addContent',$_POST)) {
			$form->addData($_POST);
			if ($form->validate()) {
				$id = $_POST['m_id'];
				unset($frmFields['m_id']);
				foreach($frmFields as $key=>$fld) {
					if (!(array_key_exists('database',$fld) && $fld['database'] == false)) {
						$flds[$fld['name']] = $form->getData($fld['name']);
					}
				}
				$tmp = array();
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
					$form->init($this->getTemplate('addContentSuccess'));
					return $this->ajaxReturn(array(
						'status' => 'true',
						'html'=>$form->show()
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
		$form->addTag('relations',$this->myLoadRelations($data['id']),false);
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

	function myLoadRelations($passed_id = null) {
		if (is_null($passed_id)) 
			$r_id = $_REQUEST['r_id'];
		else $r_id = $passed_id;
		$recs = $this->fetchAll(sprintf('select cf.*, m1.title as rFrom, m2.title as rTo from cross_functionality cf, modules m1, modules m2 where (cf.related_from = %d or cf.related_to = %d) and m1.id = cf.related_from and m2.id = cf.related_to',$r_id,$r_id));
		$relations = new Forms();
		$relations->init($this->getTemplate('relationsRow'));
		$frmFields = $relations->buildForm($this->getFields('relationsRow'));
		$rows = array();
		foreach($recs as $key=>$value) {
			$value['p_id'] = $r_id;
			$relations->reset();
			$relations->addData($value);
			$rows[] = $relations->show();
		}
		if (is_null($passed_id))
			return $this->ajaxReturn(array('status'=>true,'html'=>implode('',$rows)));
		else
			return implode('',$rows);
	}

	function editRelation() {
		$r_id = $_REQUEST['r_id'];
		$p_id = $_REQUEST['p_id'];
		$form = new Forms();
		$form->init($this->getTemplate('editRelation'));
		$frmFields = $form->buildForm($this->getFields('editRelation'));
		$r = $this->fetchSingle(sprintf('select cf.id, m1.title as related_from, m2.title as related_to, cf.enabled from %s cf, modules m1, modules m2 where cf.id = %d and m1.id = cf.related_from and m2.id = cf.related_to',$this->m_relations,$r_id));
		$r['p_id'] = $p_id;
		$form->addData($r);
		if (count($_POST) > 0 && array_key_exists('editRelation',$_POST)) {
			$form->addData($_POST);
			if ($form->validate()) {
				$flds = array();
				foreach($frmFields as $key=>$fld) {
					if (!(array_key_exists('database',$fld) && $fld['database'] == false)) {
						$flds[$fld['name']] = $form->getData($fld['name']);
					}
				}
				$stmt = $this->prepare(sprintf('update %s set %s where id = %d', $this->m_relations, implode('=?, ',array_keys($flds)).'=? ',$r_id));
				$stmt->bindParams(array_merge(array(str_repeat('s', count($flds))),array_values($flds)));
				$this->beginTransaction();
				if ($status = $stmt->execute()) {
					$this->commitTransaction();
					$form->init($this->getTemplate('editSuccess'));
				}
				else
					$this->rollbackTransaction();
			}
		}
		return $this->ajaxReturn(array('status'=>true,'html'=>$form->show()));
	}

}

?>
