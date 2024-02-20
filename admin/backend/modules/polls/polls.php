<?php

class polls extends Backend {

	private $m_tree = 'poll_folders';
	private $m_content = 'poll';
	private $m_junction = 'poll_by_folder';
	private $m_details = 'poll_questions';
	private $m_questions = 'questions';
	private $m_responses = 'poll_responses';
	private $m_perrow = 5;

	public function __construct() {
		$this->M_DIR = 'backend/modules/polls/';
		$this->setTemplates(
			array(
				'main'=>$this->M_DIR.'polls.html',
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
				'deleteItemResult'=>$this->M_DIR.'forms/deleteItemResult.html',
				'deleteSuccess'=>$this->M_DIR.'forms/deleteSuccess.html',
				'questionOuter'=>$this->M_DIR.'forms/questionPicker.html',
				'questionInner'=>$this->M_DIR.'forms/questionRow.html',
				'removeQuestion'=>$this->M_DIR.'forms/removeQuestion.html',
				'copyPoll'=>$this->M_DIR.'forms/copyPoll.html',
				'copyPollSuccess'=>$this->M_DIR.'forms/copyPollSuccess.html',
				'questionChart'=>$this->M_DIR.'forms/questionChart.html',
				'pollResponses'=>$this->M_DIR.'forms/pollResponses.html',
				'pollResponsesRow'=>$this->M_DIR.'forms/pollResponsesRow.html',
				'pollResponsesLink'=>$this->M_DIR.'forms/pollResponsesLink.html',
				'pollOther'=>$this->M_DIR.'forms/pollOther.html',
				'pollOtherDetail'=>$this->M_DIR.'forms/pollOtherDetail.html',
				'comparator'=>$this->M_DIR.'forms/comparator.html',
				'comparatorRow'=>$this->M_DIR.'forms/comparatorRow.html',
				'comparePolls'=>$this->M_DIR.'forms/comparePolls.html',
				'comparePollsHeader'=>$this->M_DIR.'forms/comparePollsHeader.html',
				'comparePollsQuestion'=>$this->M_DIR.'forms/compareQuestion.html',
				'comparePollsQuestionResults'=>$this->M_DIR.'forms/compareQuestionResults.html',
				'responseDetails'=>$this->M_DIR.'forms/responseDetails.html',
				'responseDetailsRow'=>$this->M_DIR.'forms/responseDetailsRow.html',
				'responseSearch'=>$this->M_DIR.'forms/responseSearch.html',
				'viewResponse'=>$this->M_DIR.'forms/viewResponse.html',
				'viewResponseQuestion'=>$this->M_DIR.'forms/viewResponseQuestion.html'
			)
		);
		$this->setFields(array(
			'viewResponseDemographics'=>array(
				'firstname' => array('text'=>'First Name', 'field'=>'firstname'),
				'lastname' => array('text'=>'Last Name', 'field'=>'lastname'),
				'email' => array('text'=>'Email', 'field'=>'email'),
				'phone' => array('text'=>'Phone', 'field'=>'phone1'),
				'address' => array('text'=>'Address', 'field'=>'line1'),
				'city' => array('text'=>'City', 'field'=>'city'),
				'postalcode' => array('text'=>'Postal Code', 'field'=>'postalcode'),
				'province' => array('text'=>'Province', 'field'=>'province_id'),
				'country' => array('text'=>'Country', 'field'=>'country_id')
			),
			'responseDetails'=>array(
				'options'=>array('action'=>'responseDetails','url'=>'/modit/ajax/responseDetails/polls','destination'=>'foldertabs'),
				'sortby'=>array('type'=>'hidden','value'=>'created'),
				'sortorder'=>array('type'=>'hidden','value'=>'desc'),
				'pagenum'=>array('type'=>'hidden','value'=>1),			
				'p_id'=>array('type'=>'hidden'),
				'p_state'=>array('type'=>'hidden'),
				'pager'=>array('type'=>'select','required'=>true,'value'=>$this->m_perrow,'lookup'=>'paging','id'=>'pager'),
				'responseDetails'=>array('type'=>'hidden','value'=>1),
				'created'=>array('type'=>'datepicker','required'=>false),
				'opt_created'=>array('type'=>'select','lookup'=>'search_options'),
				'opt_ip_address'=>array('type'=>'select','lookup'=>'search_string'),
				'csv'=>array('type'=>'hidden','value'=>0),
				'ip_address'=>array('type'=>'input')
			),
			'responseDetailsRow'=>array(
				'created'=>array('type'=>'timestamp'),
				'completed'=>array('type'=>'booleanIcon')
			),
			'responseDemographics'=>array(
				'firstname'=>'First Name',
				'lastname'=>'Last Name',
				'email'=>'Email',
				'postalcode'=>'Postal Code'
			),
			'responseSearch'=>array(
			),
			'copyPoll'=>array(
				'copyPoll'=>array('type'=>'hidden','database'=>false,'value'=>1),
				'title'=>array('type'=>'input','required'=>true),
				'p_id'=>array('type'=>'select','sql'=>'select id,title from poll where deleted = 0 order by title','database'=>false),
				'destFolders'=>array('name'=>'destFolders','type'=>'select','multiple'=>'multiple','required'=>true,'id'=>'destFolders','database'=>false,'options'=>$this->nodeSelect(0, 'poll_folders', 2, false, false),'reformatting'=>false,'prettyName'=>'Member of'),
				'submit'=>array('type'=>'submitButton','value'=>'Duplicate Poll','database'=>false)
			),
			'questionList'=>array(
				'required'=>array('type'=>'booleanIcon'),
				'other_text'=>array('type'=>'booleanIcon')
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
				'image'=>array('type'=>'image')
			),
			'folderProperties' => array(
				'options'=>array(
					'action'=>'/modit/polls/showPageProperties',
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
				'submit'=>array('type'=>'submitbutton','value'=>'Save','database'=>false)
			),
			'editQuestion'=>array(
				'options'=>array('name'=>'editQuestionForm','database'=>false),
				'poll_id'=>array('type'=>'tag'),
				'submit'=>array('type'=>'submitButton','value'=>'Save','database'=>false),
				'question_id'=>array('type'=>'select','required'=>true,'sql'=>'select id,name from questions where deleted = 0'),
				'required'=>array('type'=>'checkbox','required'=>false,'value'=>1,'checked'=>'checked'),
				'other_text'=>array('type'=>'checkbox','required'=>false,'value'=>1),
				'other_text_prompt'=>array('type'=>'textfield','required'=>false),
				'editQuestion'=>array('type'=>'hidden','value'=>1,'database'=>false)
			),
			'addContent'=>array(
				'options'=>array('name'=>'addContent','action'=>'/modit/ajax/addContent/polls','database'=>false),
				'submit'=>array('type'=>'submitbutton','value'=>'Save','database'=>false),
				'addContent'=>array('type'=>'hidden','value'=>1,'database'=>false),
				'id'=>array('type'=>'tag','database'=>false),
				'title'=>array('type'=>'input','required'=>true,'prettyName'=>'Title'),
				'subtitle'=>array('type'=>'input','required'=>false),
				'teaser'=>array('type'=>'textarea','required'=>false,'class'=>'mceSimple'),
				'start_date'=>array('type'=>'datetimepicker','required'=>false,'id'=>'addStart','prettyName'=>'Start','AMPM'=>true),
				'end_date'=>array('type'=>'datetimepicker','required'=>false,'id'=>'addExpires','prettyName'=>'Expires','AMPM'=>true),
				'description'=>array('type'=>'textarea','required'=>true,'id'=>'pollBody','class'=>'mceAdvanced','prettyName'=>'Description'),
				'published'=>array('type'=>'checkbox','required'=>false,'value'=>1),
				'enabled'=>array('type'=>'checkbox','required'=>false,'value'=>1),
				'image1'=>array('type'=>'tag','required'=>false,'prettyName'=>'Image 1'),
				'image2'=>array('type'=>'tag','required'=>false,'prettyName'=>'Image 2'),
				'imagesel_a'=>array('type'=>'image','unknown'=>true,'database'=>false,'id'=>'imagesel_a'),
				'imagesel_b'=>array('type'=>'image','unknown'=>true,'database'=>false,'id'=>'imagesel_b'),
				'destFolders'=>array('name'=>'destFolders','type'=>'select','multiple'=>'multiple','required'=>true,'id'=>'destFolders','database'=>false,'options'=>$this->nodeSelect(0, 'poll_folders', 2, false, false),'reformatting'=>false,'prettyName'=>'Member of'),
				'contact_info'=>array('type'=>'checkbox','value'=>1,'onclick'=>"fnSetContact()"),
				'contact_enabled'=>array('type'=>'boolean','true_false'=>1,'database'=>false),
				'contact_firstname'=>array('type'=>'checkbox','value'=>1),
				'contact_lastname'=>array('type'=>'checkbox','value'=>1),
				'contact_phone'=>array('type'=>'checkbox','value'=>1),
				'contact_email'=>array('type'=>'checkbox','value'=>1),
				'contact_address'=>array('type'=>'checkbox','value'=>1),
				'contact_city'=>array('type'=>'checkbox','value'=>1),
				'contact_province'=>array('type'=>'checkbox','value'=>1),
				'contact_country'=>array('type'=>'checkbox','value'=>1),
				'contact_postalcode'=>array('type'=>'checkbox','value'=>1),
				'contact_comments'=>array('type'=>'checkbox','value'=>1),
				'firstname_required'=>array('type'=>'checkbox','value'=>1),
				'lastname_required'=>array('type'=>'checkbox','value'=>1),
				'phone_required'=>array('type'=>'checkbox','value'=>1),
				'email_required'=>array('type'=>'checkbox','value'=>1),
				'address_required'=>array('type'=>'checkbox','value'=>1),
				'city_required'=>array('type'=>'checkbox','value'=>1),
				'province_required'=>array('type'=>'checkbox','value'=>1),
				'country_required'=>array('type'=>'checkbox','value'=>1),
				'postalcode_required'=>array('type'=>'checkbox','value'=>1),
				'comments_required'=>array('type'=>'checkbox','value'=>1),
				'firstname_prompt'=>array('type'=>'input'),
				'lastname_prompt'=>array('type'=>'input'),
				'phone_prompt'=>array('type'=>'input'),
				'email_prompt'=>array('type'=>'input'),
				'address_prompt'=>array('type'=>'input'),
				'city_prompt'=>array('type'=>'input'),
				'postalcode_prompt'=>array('type'=>'input'),
				'province_prompt'=>array('type'=>'input'),
				'country_prompt'=>array('type'=>'input'),
				'comments_prompt'=>array('type'=>'input'),
				'categories'=>array('type'=>'select','required'=>false,'options'=>$this->nodeSelect(0, 'question_folders', 2, false, false),'reformatting'=>false,'onchange'=>'fnLoadQuestions(this,%%id%%);','database'=>false),
			),
			'header'=>array(),
			'showSearchForm'=>array(
				'options'=>array('action'=>'showSearchForm','name'=>'searchForm','id'=>'search_form'),
				'published'=>array('type'=>'select','required'=>false,'lookup'=>'boolean'),
				'enabled'=>array('type'=>'select','required'=>false,'lookup'=>'boolean'),
				'title'=>array('type'=>'input','required'=>false),
				'opt_title'=>array('type'=>'select','lookup'=>'search_string'),
				'created'=>array('type'=>'datepicker','required'=>false),
				'opt_created'=>array('type'=>'select','lookup'=>'search_options'),
				'start_date'=>array('type'=>'datepicker','required'=>false),
				'opt_start_date'=>array('type'=>'select','lookup'=>'search_options'),
				'end_date'=>array('type'=>'datepicker','required'=>false),
				'opt_end_date'=>array('type'=>'select','lookup'=>'search_options'),
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
				if ($status = $stmt->execute()) {
					if ($this->isAjax()) {
						$this->logMessage('showPageProperties', 'executing ajax success return', 3);
						$this->addMessage('Record successfully added');
						return $this->ajaxReturn(array(
								'status'=>'true',
								'html'=>'',
								'url'=>'/modit/polls?p_id='.$data['id']
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
			if (strlen($data['alternate_title']) > 0) $data['connector'] = '&nbsp;-&nbsp;';
			$form->init($this->getTemplate('showFolderContent'),array('name'=>'showFolderContent'));
			$frmFields = $form->buildForm($this->getFields('showFolderContent'));
			if (array_key_exists('pagenum',$_REQUEST)) 
				$pageNum = $_REQUEST['pagenum'];
			else
				$pageNum = 1;	// no 0 based calcs
			if ($pageNum <= 0) $pageNum = 1;
			if ($this->m_perrow > 0) {
				$perPage = $this->m_perrow;
				$count = $this->fetchScalar(sprintf('select count(n.id) from %s n where n.id in (select f.poll_id from %s f where f.folder_id = %d)', $this->m_content, $this->m_junction, $f_id));
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
			$sql = sprintf('select i.*,j.id as j_id from %s i, %s j where j.folder_id = %d and i.id = j.poll_id order by sequence limit %d,%d', $this->m_content, $this->m_junction, $f_id, $start, $perPage);
			$polls = $this->fetchAll($sql);
			$this->logMessage('showPageContent', sprintf('sql [%s], records [%d]',$sql, count($polls)), 2);
			$output = array();
			//$output[] = '<tr>';
			$ct = 0;
			foreach($polls as $poll) {
				$frm = new Forms();
				$tmp = $this->getFields('articleList');
				$frm->init($this->getTemplate('articleList'),array());
				$stats = $this->fetchAll(sprintf('select completed, count(completed) as ct from %s where poll_id = %d group by completed',$this->m_responses,$poll['id']));
				$frm->addTag('abandoned',0);
				$frm->addTag('completed',0);
				foreach($stats as $key=>$stat) {
					if ($stat['completed'] == 0)
						$frm->addTag('abandoned',$stat['ct']);
					else
						$frm->addTag('completed',$stat['ct']);
				}
				$tmp = $frm->buildForm($tmp);
				$frm->addData($poll);
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
			if (array_key_exists('formData',$_SESSION) && array_key_exists('pollSearchForm', $_SESSION['formData']))
				$_POST = $_SESSION['formData']['pollSearchForm'];
			else
				$_POST = array('showSearchForm'=>1,'enabled'=>0);
		}
		if (count($_POST) > 0 && array_key_exists('showSearchForm',$_POST)) {
			$form->addData($_POST);
			if ($form->validate()) {
				$_SESSION['formData']['pollSearchForm'] = $form->getAllData();
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
						case 'title':
							if (array_key_exists('opt_'.$key,$_POST) && strlen($_POST['opt_'.$key]) > 0 && $value = $form->getData($key)) {
								if ($_POST['opt_'.$key] == 'like' && strpos($value,'%',0) === false) {
									$value = '%'.$value.'%';
								}
								$srch[] = sprintf(' %s %s "%s"',$key, $_POST['opt_'.$key],$this->escape_string($value));
							}
							break;
						case 'created':
						case 'expires':
							$this->logMessage('showSearchForm',sprintf('in created code [%s]',$form->getData($key)),1);
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
								$srch[] = sprintf(' id in (select poll_id from %s where folder_id = %d) ',$this->m_junction, $value);
							}
							break;
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
					$output = array();
					$ct = 0;
					$output[] = '<tr>';
					foreach($recs as $poll) {
						$frm = new Forms();
						$frm->init($this->getTemplate('articleList'),array());
						$tmp = $frm->buildForm($this->getFields('articleList'));
						$frm->addData($poll);
						$stats = $this->fetchAll(sprintf('select completed, count(completed) as ct from %s where poll_id = %d group by completed',$this->m_responses,$poll['id']));
						$frm->addTag('abandoned',0);
						$frm->addTag('completed',0);
						foreach($stats as $key=>$stat) {
							if ($stat['completed'] == 0)
								$frm->addTag('abandoned',$stat['ct']);
							else
								$frm->addTag('completed',$stat['ct']);
						}
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
			$data = array('id'=>0,'image1'=>'','image2'=>'','contact_info'=>0); 
		}
		if (count($_REQUEST) > 0 && array_key_exists('destFolders',$_REQUEST) || $data['id'] > 0) {
			$ids = array();
			if (array_key_exists('destFolders',$_REQUEST)) {
				$ids = $_REQUEST['destFolders'];
				if (!is_array($ids)) $ids = array($ids);
			}
			if ($data['id'] > 0) {
				$tmp = $this->fetchScalarAll(sprintf('select folder_id from %s where poll_id = %d',$this->m_junction,$data['id']));
				$ids = array_merge($ids,$tmp);
			}
			if (count($ids) > 0) {
				$data['destFolders'] = $ids;
			}
		}
		$data['contact_enabled'] = $data['contact_info'];
		$data['imagesel_a'] = $data['image1'];
		$data['imagesel_b'] = $data['image2'];
		$data['questions'] = $this->getQuestions($data['id']);
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
					$form->setData('contact_enabled',$form->getData('contact_info'));
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
					$status = $status && $this->execute(sprintf('delete from %s where poll_id = %d and folder_id not in (%s)',$this->m_junction,$id,implode(',',$destFolders)));
					//
					//	insert new folders
					//
					$new = $this->fetchScalarAll(sprintf('select id from %s where id in (%s) and id not in (select folder_id from %s where poll_id = %d and folder_id in (%s))', $this->m_tree, implode(',',$destFolders),$this->m_junction,$id,implode(',',$destFolders)));
					$status = true;
					foreach($new as $key=>$folder) {
						$obj = new preparedStatement(sprintf('insert into %s(poll_id,folder_id) values(?,?)',$this->m_junction));
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

	function editQuestion() {
		$form = new Forms();
		$form->init($this->getTemplate('editQuestion'));
		if (!(array_key_exists('q_id',$_REQUEST) && $_REQUEST['q_id'] > 0 && $data = $this->fetchSingle(sprintf('select * from %s where id = %d and poll_id = %d', $this->m_details, $_REQUEST['q_id'], $_REQUEST['p_id'])))) {
			$data = array('id'=>0,'poll_id'=>$_REQUEST['p_id']); 
		}
		$form->addData($data);
		$frmFields = $form->buildForm($this->getFields('editQuestion'));
		$status = true;
		if (count($_POST) > 0 && array_key_exists('editQuestion',$_POST)) {
			$form->addData($_POST);
			if ($form->validate()) {
				$id = $_POST['q_id'];
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
					if (($ct = $this->fetchScalar(sprintf('select count(0) from %s where poll_id = %d and question_id = %d',$this->m_details,$form->getData('poll_id'),$form->getData('question_id')))) > 0) {
						$form->addFormError('This question already is assigned to the poll');
						$valid = false;
					}
					else
						$stmt = $this->prepare(sprintf('insert into %s(%s) values(%s?)',$this->m_details,implode(', ',array_keys($values)),str_repeat('?,',count($values)-1),$id));
				}
				if ($valid) {
					$stmt->bindParams(array_merge(array(str_repeat('s', count($values))),array_values($values)));
					$this->beginTransaction();
					if ($stmt->execute()) {
						$this->commitTransaction();
						$form->addMessage('Record Updated');
						$form->init($this->getTemplate('editQuestionSuccess'));
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

	function moduleStatus($fromMain = 0) {
		$ct = $this->fetchScalar(sprintf('select count(0) from %s where enabled = 0 or published = 0 order by title',$this->m_content));
		if ($ct > 0) {
			$_POST = array('showSearchForm'=>1,'opt_published'=>'=','published'=>'0','sortby'=>'created','sortorder'=>'desc','pager'=>$this->m_perrow);
			$msg = "Showing unpublished polls";
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
	
	function deleteArticle() {
		if (array_key_exists('a_id',$_REQUEST) && array_key_exists('deleteArticle',$_REQUEST)) {
			$this->execute(sprintf('delete from %s where game_id = %d',$this->m_details,$_REQUEST['a_id']));
			$this->execute(sprintf('delete from %s where id = %d',$this->m_content,$_REQUEST['a_id']));
		}
		$form = new Forms();
		$form->init($this->getTemplate('deleteItemResult'));
		return $this->ajaxReturn(array('status'=>true,'html'=>$form->show()));
	}

	function getQuestions($p_parm = null) {
		if (is_null($p_parm)) {
			$p_id = $_REQUEST['p_id'];
		}
		else $p_id = $p_parm;
		$q = $this->fetchAll(sprintf('select pq.*,q.name from %s pq, questions q where pq.poll_id = %d and q.id = pq.question_id and pq.deleted = 0 order by pq.sequence',$this->m_details,$p_id));
		$return = array();
		$form = new Forms();
		$form->init($this->getTemplate('questionList'));
		$flds = $form->buildForm($this->getFields('questionList'));
		foreach($q as $question) {
			$sql = sprintf('select count(0) from %s p, poll_response_answers a where p.completed = 1 and p.poll_id = %d and a.response_id = p.id and a.question_id = %d',$this->m_responses,$p_id,$question['question_id']);
			$ct = $this->fetchScalar($sql);
			$this->logMessage('getQuestions',sprintf('sql [%s] ct [%d]',$sql,$ct),1);
			$form->addTag('completed',$ct);
			$form->addData($question);
			$return[] = $form->show();
		}
		if (is_null($p_parm))
			return $this->ajaxReturn(array('status'=>true,'html'=>implode('',$return)));
		else
			return implode('',$return);
	}

	function addQuestion() {
		return '<script type="text/javascript">document.location="/modit/question";</script>';
	}

	function editContent() {
		$result = '';
		if (array_key_exists('p_id',$_REQUEST)) {
			$result = sprintf('<script type="text/javascript">fnEditArticle(%d)</script>',$_REQUEST['p_id']);
		}
		return $this->show($result);
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
			else {
				$this->addMessage('Polls are still attached to this folder');
			}
			if ($this->isAjax())
				return $this->ajaxReturn(array('status'=>$status ? 'true':'false','html'=>$form->show()));
			elseif ($fromMain)
				return $html;
			else
				return $this->show($html);
		}
	}

	function moveArticle() {
		$src = 0;
		$dest = 0;
		if (array_key_exists('src',$_REQUEST)) $src = $_REQUEST['src'];
		if (array_key_exists('dest',$_REQUEST)) $dest = $_REQUEST['dest'];
		$this->logMessage("moveArticle",sprintf("src [$src] dest [$dest]"),1);
		if ($_REQUEST['type'] == 'tree') {
			if ($src == 0 || $dest == 0 || !array_key_exists('type',$_REQUEST)) {
				$this->addError('Polls cannot be moved from search mode, only copied');
				return $this->ajaxReturn(array('status' => 'false'));
			}
			$curr = $src;	//$this->fetchScalar(sprintf('select article_id from %s where id = %d',$this->m_junction,$src));
			if ($curr > 0 && $folder = $this->fetchSingle(sprintf('select * from %s where id = %d',$this->m_tree,$dest))) {
				$status = true;
				if (array_key_exists('move',$_REQUEST)) {
					//
					//	move it - delete all other folders
					//
					$curr = $this->fetchScalar(sprintf('select poll_id from %s where id = %d',$this->m_junction,$src));
					$this->logMessage('moveArticle', sprintf('moving poll %d to folder %d',$curr,$dest),2);
					$this->beginTransaction();
					if ($status = $this->execute(sprintf('delete from %s where id = %d', $this->m_junction, $src))) {
						if (!$this->fetchSingle(sprintf('select * from %s where poll_id = %d and folder_id = %d',$this->m_junction,$curr,$dest))) {
							$obj = new preparedStatement(sprintf('insert into %s(poll_id,folder_id) values(?,?)',$this->m_junction));
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
					$this->logMessage('moveArticle', sprintf('cloning poll %d to folder %d',$curr,$dest),2);
					if (!($this->fetchSingle(sprintf('select * from %s where poll_id = %d and folder_id = %d',$this->m_junction,$curr,$dest)))) {
						$obj = new preparedStatement(sprintf('insert into %s(poll_id,folder_id) values(?,?)',$this->m_junction));
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
				$this->addMessage('Either source or destination was missing');
				return $this->ajaxReturn(array('status' => 'false'));
			}
			$src = $this->fetchSingle(sprintf('select * from %s where id = %d',$this->m_junction,$src));
			$sql = sprintf('select * from %s where folder_id = %d order by sequence limit %d,1',$this->m_junction,$src['folder_id'],$dest);
			$dest = $this->fetchSingle($sql);
			$this->logMessage("moveArticle",sprintf("move src [%s] to dest [%s] sql [%s]",print_r($src,true),print_r($dest,true),$sql),2);
			if (count($src) == 0 || count($dest) == 0) {
				$status = false;
				$this->addMessage('Either the source or destination poll was not found');
			}
			else {
				//
				//	swap the order of the polls
				//
				$this->logMessage('moveArticle', sprintf('swap the sort order of %d and %d',$src['id'],$dest['id']),2);
				$this->beginTransaction();
				$sql = sprintf('update %s set sequence = %d where id = %s',
					$this->m_junction, $src['sequence'] < $dest['sequence'] ? $dest['sequence']+1 : $dest['sequence']-1, $src['id']);
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

	function resequence($folder) {
		$this->logMessage('resequence', "resequencing folder $folder", 2);
		$articles = $this->fetchAll(sprintf('select * from %s where folder_id = %d order by sequence',$this->m_junction,$folder));
		$seq = 10;
		foreach($articles as $article) {
			$this->execute(sprintf('update %s set sequence = %d where id = %d',$this->m_junction,$seq,$article['id']));
			$seq += 10;
		}
	}

	function loadQuestions() {
		$poll = $_REQUEST['p_id'];
		$questions = $this->fetchAll(sprintf('select * from questions q, question_by_folder f where f.folder_id = %d and q.id = f.question_id and q.id not in (select question_id from poll_questions where poll_id = %d and deleted = 0) and deleted = 0 order by name',$_REQUEST['f_id'],$poll));
		$outer = new Forms();
		$outer->init($this->getTemplate('questionOuter'));
		$form = new Forms();
		$form->init($this->getTemplate('questionInner'));
		$return = array();
		foreach($questions as $rec) {
			$form->reset();
			$form->addData($rec);
			$form->addTag('poll_id',$poll);
			$return[] = $form->show();
		}
		$outer->addTag('questions',implode('',$return),false);
		return $this->ajaxReturn(array('status'=>true,'html'=>$outer->show()));
	}

	function dropQuestion() {
		$p_id = $_REQUEST['p_id'];
		$q_id = $_REQUEST['q_id'];
		$form = new Forms();
		$status = true;
		if (!$poll = $this->fetchSingle(sprintf('select * from %s where id = %d',$this->m_content,$p_id))) {
			$status = false;
			$this->addError('Could not locate the selected poll');
		}
		else {
			if (!$question = $this->fetchSingle(sprintf('select * from %s where id = %d',$this->m_questions,$q_id))) {
				$status = false;
				$this->addError('Could not locate the selected question');
			}
			else {
				if ($rec = $this->fetchSingle(sprintf('select * from %s where question_id = %d and poll_id = %d',$this->m_details,$q_id,$p_id))) {
					if ($rec['deleted'] == 0) {
						$status = false;
						$this->addError('This question is already assigned to the poll');
					}
					else {
						$this->execute(sprintf('update %s set deleted = 0 where id = %d',$this->m_details,$rec['id']));
						$form->init($this->getTemplate('editQuestionSuccess'));
						$form->addTag('poll_id',$p_id);
					}
				}
				else {
					$stmt = $this->prepare(sprintf('insert into %s (question_id,poll_id,sequence) values(?,?,?)',$this->m_details));
					$stmt->bindParams(array('ddd',$q_id,$p_id,999));
					if ($stmt->execute()) {
						$this->addMessage('Question added');
						$form->init($this->getTemplate('editQuestionSuccess'));
						$form->addTag('poll_id',$p_id);
						$this->resequenceQuestions($p_id);
					}
				}
			}
		}
		return $this->ajaxReturn(array('status'=>$status,'html'=>$form->show()));
	}
	
	function resequenceQuestions($poll) {
		$curr = 10;
		$recs = $this->fetchAll(sprintf('select * from %s where poll_id = %d order by sequence',$this->m_details,$poll));
		foreach($recs as $key=>$row) {
			$this->execute(sprintf('update %s set sequence = %d where id = %d',$this->m_details,$curr,$row['id']));
			$curr += 10;
		}
	}

	function resortQuestions() {
		$src = $_REQUEST['q_id'];
		$dest = $_REQUEST['dest'];
		$status = false;
		if ($src == 0 || $dest < 0) {
			$this->addMessage('Either source or destination was missing');
			return $this->ajaxReturn(array('status' => 'false'));
		}
		$src = $this->fetchSingle(sprintf('select * from %s where id = %d',$this->m_details,$src));
		$sql = sprintf('select * from %s where poll_id = %d order by sequence limit %d,1',$this->m_details,$src['poll_id'],$dest);
		$dest = $this->fetchSingle($sql);
		$this->logMessage("resortQuestions",sprintf("move src [%s] to dest [%s] sql [%s]",print_r($src,true),print_r($dest,true),$sql),2);
		if (count($src) == 0 || count($dest) == 0) {
			$status = false;
			$this->addMessage('Either the source or destination question was not found');
		}
		else {
			//
			//	swap the order of the polls
			//
			$this->logMessage('resortQuestions', sprintf('swap the sort order of %d and %d',$src['id'],$dest['id']),2);
			$this->beginTransaction();
			$sql = sprintf('update %s set sequence = %d where id = %s',
				$this->m_details, $src['sequence'] < $dest['sequence'] ? $dest['sequence']+1 : $dest['sequence']-1, $src['id']);
			if ($this->execute($sql)) {
				$this->resequenceQuestions($src['poll_id']);
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

	function removeQuestion() {
		$q_id = $_REQUEST['q_id'];
		$p_id = $_REQUEST['p_id'];
		$status = false;
		$form = new Forms();
		$form->init($this->getTemplate('editQuestionSuccess'));
		$form->addTag('poll_id',$p_id);
		if ($rec = $this->fetchSingle(sprintf('select * from %s where id = %d and poll_id = %d',$this->m_details,$q_id,$p_id))) {
			if ($ct = $this->fetchScalar(sprintf('select count(0) from %s where poll_id = %d',$this->m_responses,$p_id)) > 0) {
				//
				//	poll has already been answered so we can't delete it - just flag deleted
				//
				$status = $this->execute(sprintf('update %s set deleted = 1 where id = %d',$this->m_details,$rec['id']));
			}
			else {
				$status = $this->execute(sprintf('delete from %s where id = %d',$this->m_details,$rec['id']));
			}
		}
		else $this->addMessage('Could not locate that question');
		return $this->ajaxReturn(array('status'=>$status,'html'=>$form->show()));
	}

	function copyPoll() {
		$p_id = $_REQUEST['p_id'];
		$status = false;
		$form = new Forms();
		$form->init($this->getTemplate('copyPoll'));
		$form->setData('p_id',$p_id);
		$flds = $form->buildForm($this->getFields('copyPoll'));
		if (count($_POST) > 0 && array_key_exists('copyPoll',$_POST)) {
			$form->addData($_POST);
			if ($form->validate()) {
				$poll = $this->fetchSingle(sprintf('select * from %s where id = %d',$this->m_content,$p_id));
				$this->beginTransaction();
				unset($poll['id']);
				$poll['title'] = $_POST['title'];
				$poll['created'] = date(DATE_ATOM);
				$stmt = $this->prepare(sprintf('insert into %s(%s) values(%s?)',$this->m_content,implode(', ',array_keys($poll)),str_repeat('?, ',count($poll)-1)));
				$stmt->bindParams(array_merge(array(str_repeat('s', count($poll))),array_values($poll)));
				if ($valid = $stmt->execute()) {
					$id = $this->insertId();
					foreach($_POST['destFolders'] as $key=>$folder) {
						$stmt = $this->prepare(sprintf('insert into %s(poll_id,folder_id,sequence) values(?,?,999)',$this->m_junction));
						$stmt->bindParams(array('dd',$id,$folder));
						$valid &= $stmt->execute();
					}
				}
				if ($valid) {
					$questions = $this->fetchAll(sprintf('select * from %s where poll_id = %d and deleted = 0 order by sequence',$this->m_details,$p_id));
					foreach($questions as $key=>$question) {
						unset($question['id']);
						$question['poll_id'] = $id;
						$stmt = $this->prepare(sprintf('insert into %s(%s) values(%s?)',$this->m_details,implode(', ',array_keys($question)),str_repeat('?, ',count($question)-1)));
						$stmt->bindParams(array_merge(array(str_repeat('s', count($question))),array_values($question)));
						$valid &= $stmt->execute();
					}
				}
				if ($valid) {
					$this->addMessage('Duplicated');
					$form->init($this->getTemplate('copyPollSuccess'));
					$form->setData('f_id',$_POST['destFolders'][0]);
					$this->commitTransaction();
				}
				else 
					$this->addError('An Error Occurred');
				$this->rollbackTransaction();
			}
		}
		return $this->ajaxReturn(array('status'=>true,'html'=>$form->show()));
	}

	function pollResponse() {
		$q_id = $_REQUEST['q_id'];
		$sql = sprintf('SELECT qa.text, count(qa.question_id) as ct
FROM `poll_questions` pq, poll_responses pr, poll_response_answers pra, question_answers qa
WHERE pq.id = %d and 
pr.poll_id = pq.poll_id and 
pr.completed = 1 and 
pra.response_id = pr.id and 
pra.question_id = pq.question_id and
qa.id = pra.answer_id
group by qa.id',$q_id);
		$question = $this->fetchSingle(sprintf('select * from questions q, poll_questions pq where pq.id = %d and q.id = pq.question_id',$q_id));
		$stats = $this->fetchAll($sql);
		$result = array();
		$ticks = array();
		$series = array();
		$total = 0;
		$max = 0;
		$min = 999999;
		foreach($stats as $key=>$stat) {
			$total += $stat['ct'];
			$result[] = sprintf("['%s',%d]",$stat['text'],$stat['ct']);
			$ticks[] = sprintf("'%s'",$stat['text']);
			$series[] = sprintf("%d",$stat['ct']);
			if ($stat['ct'] < $min) $min = $stat['ct'];
			if ($stat['ct'] > $max) $max = $stat['ct'];
		}
		//
		//	check for 'other' responses
		//
		$sql = sprintf('SELECT count(0) as ct
FROM `poll_questions` pq, poll_responses pr, poll_response_answers pra
WHERE pq.id = %d and 
pr.poll_id = pq.poll_id and 
pr.completed = 1 and 
pra.response_id = pr.id and 
pra.question_id = pq.question_id and
pra.answer_id = 0 and
length(other) > 0',$q_id);
		if ($ct = $this->fetchScalar($sql) > 0) {
			$result[] = sprintf("['other',%d]",$ct);
			$ticks[] = "['other']";
			$series[] = $ct;
			if ($ct < $min) $min = $ct;
			if ($ct > $max) $max = $ct;
		}
		$this->logMessage('pollresponse',sprintf('min [%d] max [%d]',$min,$max),1);
		if (($max - $min) < 10) {
			$yaxis = array();
			$yaxis[] = $min-1;
			for($i = $min; $i <= $max; $i++) {
				$yaxis[] = $i;
			}
		} else {
			$inc = ceil(($max - $min) / 6);
			$this->logMessage('pollresponse',sprintf('inc is [%d] max-min/6 [%d] ceil [%d]',$inc,($max - $min) / 6,ceil(($max - $min) / 6)),1);
			$i = $min;
			$sanity = 0;
			$yaxis[] = $min-$inc >= 0 ? $min-$inc : 0;
			while($i <= ($max+$inc) && $sanity < 20) {
				$yaxis[] = $i <= $max ? $i : $max;
				$i += $inc;
				$sanity += 1;
			}
		}
		$form = new Forms();
		$form->addData($question);
		$form->init($this->getTemplate('questionChart'));
		$form->addTag('data',implode(',',$result),false);
		$form->addTag('series',implode(',',$series),false);
		$form->addTag('ticks',implode(',',$ticks),false);
		$form->addTag('yAxis',implode(',',$yaxis),false);
		return $this->ajaxReturn(array('status'=>true,'html'=>$form->show()));
	}

	function questionCSV() {
		$q_id = $_REQUEST['q_id'];
		$sql = sprintf('SELECT qa.text, count(qa.question_id) as ct
FROM `poll_questions` pq, poll_responses pr, poll_response_answers pra, question_answers qa
WHERE pq.id = %d and 
pr.poll_id = pq.poll_id and 
pr.completed = 1 and 
pra.response_id = pr.id and 
pra.question_id = pq.question_id and
qa.id = pra.answer_id
group by qa.id',$q_id);
		$stats = $this->fetchAll($sql);
		$question = $this->fetchSingle(sprintf('select * from questions q, poll_questions pq where pq.id = %d and q.id = pq.question_id',$q_id));
		ob_end_clean();
		header('Content-type: text/csv');
		header(sprintf('Content-Disposition: attachment; filename="%s.csv"',preg_replace('#[^a-z0-9_]#i', '-', strtolower($question['name']))));
		echo 'question;response;count;other text'.PHP_EOL;
		$name = str_replace('"','""',$question['name']);
		foreach($stats as $key=>$stat) {
			echo sprintf('%s;%s;%d;',$name,$stat['text'],$stat['ct']).PHP_EOL;
		}
		$sql = sprintf('SELECT other, count(0) as ct
FROM `poll_questions` pq, poll_responses pr, poll_response_answers pra
WHERE pq.id = %d and 
pr.poll_id = pq.poll_id and 
pr.completed = 1 and 
pra.response_id = pr.id and 
pra.question_id = pq.question_id and
pra.answer_id = 0 and
length(other) > 0
group by other',$q_id);
		if ($stats = $this->fetchAll($sql)) {
			foreach($stats as $key=>$stat) {
				$text = str_replace("\r\n"," ",$stat['other']);
				$text = str_replace("\n"," ",$text);
				$text = str_replace("\r"," ",$text);
				if (strpos(';',$text) !== false) $text = sprintf('"%s"',str_replace('"','""',$text));
				echo sprintf('%s;other;%d;%s',$name,$stat['ct'],$text).PHP_EOL;
			}
		}
		ob_flush();
	}

	function pollCSV() {
		$p_id = $_REQUEST['p_id'];
		ob_end_clean();
		$poll = $this->fetchSingle(sprintf('select * from poll where id = %d',$p_id));
		header('Content-type: text/csv');
		header(sprintf('Content-Disposition: attachment; filename="%s.csv"',preg_replace('#[^a-z0-9_]#i', '-', strtolower($poll['title']))));
		echo 'question;response;count;other text'.PHP_EOL;
		$questions = $this->fetchAll(sprintf('select q.*, pq.id as q_id from questions q, poll_questions pq 
where pq.poll_id = %d and 
pq.deleted = 0 and
q.id = pq.question_id
order by pq.sequence',$p_id));
		foreach($questions as $key=>$question) {
			$name = str_replace('"','""',$question['name']);
			$sql = sprintf('SELECT qa.text, count(qa.question_id) as ct
FROM `poll_questions` pq, poll_responses pr, poll_response_answers pra, question_answers qa
WHERE pq.poll_id = %d and 
pq.question_id = %d and
pr.poll_id = pq.poll_id and 
pr.completed = 1 and 
pra.response_id = pr.id and 
pra.question_id = pq.question_id and
qa.id = pra.answer_id
group by qa.id',$p_id,$question['id']);
			$stats = $this->fetchAll($sql);
			foreach($stats as $key=>$stat) {
				echo sprintf('%s;%s;%d;',$name,$stat['text'],$stat['ct']).PHP_EOL;
			}

			$sql = sprintf('SELECT other, count(0) as ct
	FROM `poll_questions` pq, poll_responses pr, poll_response_answers pra
	WHERE pq.poll_id = %d and 
	pq.question_id = %d and
	pr.poll_id = pq.poll_id and 
	pr.completed = 1 and 
	pra.response_id = pr.id and 
	pra.question_id = pq.question_id and
	pra.answer_id = 0 and
	length(other) > 0
	group by other',$p_id,$question['id']);
			if ($stats = $this->fetchAll($sql)) {
				foreach($stats as $key=>$stat) {
					$text = str_replace("\r\n"," ",$stat['other']);
					$text = str_replace("\n"," ",$text);
					$text = str_replace("\r"," ",$text);
					if (strpos(';',$text) !== false) $text = sprintf('"%s"',str_replace('"','""',$text));
					echo sprintf('%s;other;%d;%s',$name,$stat['ct'],$text).PHP_EOL;
				}
			}



		}
		ob_flush();
	}

	function pollResults() {
		$p_id = $_REQUEST['p_id'];
		ob_end_clean();
		$poll = $this->fetchSingle(sprintf('select * from poll where id = %d',$p_id));
		$questions = $this->fetchAll(sprintf('select q.*, pq.id as q_id from questions q, poll_questions pq 
where pq.poll_id = %d and 
pq.deleted = 0 and
q.id = pq.question_id
order by pq.sequence',$p_id));
		$rows = array();
		$inner = new Forms();
		$inner->init($this->getTemplate('pollResponsesRow'));
		$link = new Forms();
		$link->init($this->getTemplate('pollResponsesLink'));
		foreach($questions as $key=>$question) {
			$inner->reset();
			$link->addData($question);
			$question['link'] = $link->show();
			$inner->addData($question);
			$inner->addTag('class','header');
			$rows[] = $inner->show();
			$total = $this->fetchScalar(sprintf('SELECT count(0) as ct
FROM `poll_questions` pq, poll_responses pr, poll_response_answers pra, question_answers qa
WHERE pq.poll_id = %d and 
pq.question_id = %d and
pr.poll_id = pq.poll_id and 
pr.completed = 1 and 
pra.response_id = pr.id and 
pra.question_id = pq.question_id and
qa.id = pra.answer_id
group by pq.poll_id',$p_id,$question['id']));
			$total += $this->fetchScalar(sprintf('SELECT count(0) as ct
FROM `poll_questions` pq, poll_responses pr, poll_response_answers pra
WHERE pq.poll_id = %d and 
pq.question_id = %d and
pr.poll_id = pq.poll_id and 
pr.completed = 1 and 
pra.response_id = pr.id and 
pra.question_id = pq.question_id and
length(pra.other) > 0 and
pra.answer_id = 0
group by pq.poll_id',$p_id,$question['id']));
			$answers = $this->fetchAll(sprintf('select qa.* from question_answers qa, poll_questions pq where pq.poll_id = %d and pq.question_id = %d and qa.question_id = pq.question_id and pq.deleted = 0 order by qa.sequence',$p_id,$question['id']));
			$p_total = 0;
			$q_total = 0;
			foreach($answers as $key=>$answer) {
				$sql = sprintf('SELECT qa.text, count(qa.question_id) as ct
	FROM `poll_questions` pq, poll_responses pr, poll_response_answers pra, question_answers qa
	WHERE pq.poll_id = %d and 
	pq.question_id = %d and
	pr.poll_id = pq.poll_id and 
	pr.completed = 1 and 
	pra.response_id = pr.id and 
	pra.question_id = pq.question_id and
	qa.id = pra.answer_id and
	pra.answer_id = %d
	group by qa.id',$p_id,$question['id'],$answer['id']);
				$stats = $this->fetchAll($sql);
				if (count($stats) == 0) $stats = $this->fetchAll(sprintf('select text,0 as ct from question_answers qa where id = %d',$answer['id']));
				$tmp = 0;
				foreach($stats as $key=>$stat) {
					$inner->reset();
					$inner->addTag('class','detail');
					$tmp = $total > 0 ? round(100*$stat['ct']/$total,1) : 0;
					$p_total += $tmp;
					$q_total += $stat['ct'];
					$stat['percentage'] = $tmp;
					$inner->addData($stat);
					$rows[] = $inner->show();
				}
			}
			$sql = sprintf('SELECT other, count(0) as ct
	FROM `poll_questions` pq, poll_responses pr, poll_response_answers pra
	WHERE pq.poll_id = %d and 
	pq.question_id = %d and
	pr.poll_id = pq.poll_id and 
	pr.completed = 1 and 
	pra.response_id = pr.id and 
	pra.question_id = pq.question_id and
	pra.answer_id = 0 and
	length(other) > 0
	group by other',$p_id,$question['id']);
			if ($stats = $this->fetchAll($sql)) {
				foreach($stats as $key=>$stat) {
					$stat['text'] = 'other';
					$inner->reset();
					$tmp = round(100*$stat['ct']/$total,1);
					$p_total += $tmp;
					$q_total += $stat['ct'];
					$stat['percentage'] = $tmp;
					$inner->addData($stat);
					$inner->addTag('class','detail');
					$inner->setData('text',sprintf('<a href="#" onclick="fnShowOther(%d);return false;">other</a>',$question['q_id']),false);
					$this->logMessage('pollResults',sprintf('inner other [%s]',print_r($inner,true)),1);
					$rows[] = $inner->show();
				}
			}
			$inner->reset();
			$inner->addData(array('text'=>'Total','ct'=>$q_total,'percentage'=>$p_total));
			$inner->addTag('class','total');
			$rows[] = $inner->show();
		}
		$outer = new Forms();
		$outer->init($this->getTemplate('pollResponses'));
		$outer->addTag('rows',implode('',$rows),false);
		return $this->ajaxReturn(array('status'=>true,'html'=>$outer->show()));
	}

	function showOther() {
		$q_id = $_REQUEST['q_id'];
		$answers = $this->fetchAll(sprintf('select * from poll_response_answers pa, poll_responses pr, poll_questions pq
			where pq.id = %d and pr.poll_id = pq.poll_id and pr.completed = 1 and pa.response_id = pr.id and length(pa.other) > 0',$q_id));
		$outer = new Forms();
		$outer->init($this->getTemplate('pollOther'));
		$inner = new Forms();
		$inner->init($this->getTemplate('pollOtherDetail'));
		$results = array();
		$question = $this->fetchSingle(sprintf('select * from questions q, poll_questions pq where pq.id = %d and q.id = pq.question_id',$q_id));
		$outer->addData($question);
		foreach($answers as $key=>$answer) {
			$inner->reset();
			$inner->addData($answer);
			$results[] = $inner->show();
		}
		$outer->addTag('responses',implode('',$results),false);
		return $this->ajaxReturn(array('status'=>true,'html'=>$outer->show()));
	}

	function addComparison() {
		$p_id = array_key_exists('p_id',$_REQUEST) ? $_REQUEST['p_id'] : 0;
		if (!array_key_exists('poll_comparator',$_SESSION)) $_SESSION['poll_comparator'] = array();
		if ($poll = $this->fetchSingle(sprintf('select * from %s where deleted = 0 and id = %d',$this->m_content,$p_id))) {
			$found = 0;
			foreach($_SESSION['poll_comparator'] as $key=>$value) {
				$found |= $value == $p_id;
			}
			if (!$found)
				$_SESSION['poll_comparator'][] = $p_id;
		}
		$outer = new Forms();
		$outer->init($this->getTemplate('comparator'));
		$inner = new Forms();
		$inner->init($this->getTemplate('comparatorRow'));
		$results = array();
		foreach($_SESSION['poll_comparator'] as $key=>$value) {
			$inner->reset();
			$rec = $this->fetchSingle(sprintf('select * from %s where id = %d',$this->m_content,$value));
			$inner->addData($rec);
			$results[] = $inner->show();
		}
		$outer->addTag('polls',implode('',$results),false);
		return $this->ajaxReturn(array('status'=>true,'html'=>$outer->show()));
	}

	function removeComparison() {
		$p_id = array_key_exists('p_id',$_REQUEST) ? $_REQUEST['p_id'] : 0;
		foreach($_SESSION['poll_comparator'] as $key=>$value) {
			if ($value == $p_id)
				unset($_SESSION['poll_comparator'][$key]);
		}
		$_REQUEST['p_id'] = 0;
		return $this->addComparison();
	}

	function resortComparison() {
		$dest = array_key_exists('dest',$_REQUEST) ? $_REQUEST['dest'] : 0;
		$src = array_key_exists('src',$_REQUEST) ? $_REQUEST['src'] : 0;
		if ($dest >= 0 && $src >= 0) {
			$this->logMessage('resortComparison',sprintf('session pre [%s]',print_r($_SESSION['poll_comparator'],true)),1);
			$tmp = array();
			for($i = 0; $i < count($_SESSION['poll_comparator']); $i++ ) {
				if ($i == $dest) {
					if ($src < $dest) {
						$tmp[] = $_SESSION['poll_comparator'][$dest];
						$tmp[] = $_SESSION['poll_comparator'][$src];
					}
					else {
						$tmp[] = $_SESSION['poll_comparator'][$src];
						$tmp[] = $_SESSION['poll_comparator'][$dest];
					}
				}
				else if ($i != $src)
					$tmp[] = $_SESSION['poll_comparator'][$i];
				$this->logMessage('resortComparison',sprintf('i [%d] tmp [%s]',$i,print_r($tmp,true)),1);
			}
			$_SESSION['poll_comparator'] = $tmp;
			$this->logMessage('resortComparison',sprintf('session post [%s]',print_r($_SESSION['poll_comparator'],true)),1);
		}
		return $this->ajaxReturn(array('status'=>true));
	}
	
	function comparePolls() {
		if (array_key_exists('poll_comparator',$_SESSION) && is_array($_SESSION['poll_comparator']) && count($_SESSION['poll_comparator']) > 0) {
			$poll_ids = $_SESSION['poll_comparator'];
		}
		else {
			$this->addError('No Polls have been selected');
			$this->ajaxReturn(array('status'=>false));
		}
		$polls = $this->fetchAll(sprintf('select * from %s where deleted = 0 and id in (%s) order by instr("%s,",concat(id,","))',$this->m_content,implode(',',$poll_ids),implode(',',$poll_ids)));
		$outer = new Forms();
		$outer->init($this->getTemplate('comparePolls'));
		$names = array();
		$tmp = new Forms();
		$tmp->init($this->getTemplate('comparePollsHeader'));
		foreach($polls as $key=>$poll) {
			$tmp->addData($poll);
			$names[] = $tmp->show();
		}
		$questions = $this->fetchAll(sprintf('SELECT pq.question_id FROM %s pq WHERE pq.poll_id in (1,2,3) GROUP BY pq.question_id order by min(sequence)',$this->m_details,implode(',',$poll_ids)));
		$tmp = new Forms();
		$tmp->init($this->getTemplate('comparePollsQuestion'));
		$rows = array();
		foreach($questions as $key=>$question) {
			$q = $this->fetchSingle(sprintf('select * from %s where id = %d',$this->m_questions,$question['question_id']));
			$tmp->addData($q);
			$result = new Forms();
			$result->init($this->getTemplate('comparePollsQuestionResults'));
			$maxRows = 0;
			$answers = $this->fetchAll(sprintf('select * from question_answers qa where question_id = %d and deleted = 0',$q['id']));
			$stats = array();
			$rows[] = $tmp->show();
			$statForm = new Forms();
			$totals = array();
			$counts = array();
			foreach($poll_ids as $key=>$poll_id) {
					$total = $this->fetchScalar(sprintf('
select count(0) 
from poll_response_answers pra, poll_responses pr
where pr.completed = 1 and pr.poll_id = %d and pra.question_id = %d and pra.response_id = pr.id
group by pr.poll_id,pra.question_id', $poll_id, $question['question_id']));
				$totals[$poll_id] = $total;
				$counts[$poll_id]['cast'] = 0;
				$counts[$poll_id]['pct'] = 0;
			}
			foreach($answers as $key=>$answer) {
				$result->addData($answer);
				//$statForm->init($this->getTemplate());
				$stats = array();
				foreach($poll_ids as $key=>$poll_id) {
					$ct = $this->fetchScalar(sprintf('
select count(0) 
from poll_response_answers pra, poll_responses pr
where pr.completed = 1 and pr.poll_id = %d and pra.question_id = %d and pra.answer_id = %d and pra.response_id = pr.id', 
					$poll_id, $question['question_id'],$answer['id']));
					$counts[$poll_id]['cast'] += $ct;
					$counts[$poll_id]['pct'] += $totals[$poll_id] > 0 ? round(100*$ct/$totals[$poll_id],1) : 0;
					$stats[] = sprintf('<td class="stat">%d</td><td class="total">%3.1f</td>',$ct,$totals[$poll_id] > 0 ? round(100*$ct/$totals[$poll_id],1) : 0);
				}
				//$stats[] = $result->show();
				$result->addTag('stats',implode('',$stats),false);
				$rows[] = $result->show();
			}
					$other = $this->fetchScalar(sprintf('
select count(0) 
from poll_response_answers pra, poll_responses pr
where pr.completed = 1 and pr.poll_id in (%s) and pra.question_id = %d and pra.response_id = pr.id and pra.answer_id = 0 and length(other) > 9
group by pr.poll_id,pra.question_id', implode(',',$poll_ids), $question['question_id']));
			if ($other > 0) {
				$stats = array();
				foreach($poll_ids as $key=>$poll_id) {
					$other = $this->fetchScalar(sprintf('
select count(0) 
from poll_response_answers pra, poll_responses pr
where pr.completed = 1 and pr.poll_id = %s and pra.question_id = %d and pra.response_id = pr.id and pra.answer_id = 0 and length(other) > 9
group by pr.poll_id,pra.question_id', $poll_id, $question['question_id']));
					$counts[$poll_id]['cast'] += $other;
					$counts[$poll_id]['pct'] += $totals[$poll_id] > 0 ? round(100*$other/$totals[$poll_id],1) : 0;
					$stats[] = sprintf('<td class="stat">%d</td><td class="total">%3.1f</td>',$other,$totals[$poll_id] > 0 ? round(100*$other/$totals[$poll_id],1) : 0);
				}
				$result->reset();
				$result->addData(array('text'=>'Other'));
				$result->addTag('stats',implode('',$stats),false);
				$rows[] = $result->show();
			}
			$result->reset();
			$this->logMessage('comparePolls',sprintf('counts [%s]',print_r($counts,true)),1);
			$q_totals = array();
			foreach($poll_ids as $key=>$poll_id) {
				$q_totals[] = sprintf('<td class="stat summary">%d</td><td class="total summary">%3.1f</td>',$counts[$poll_id]['cast'],$counts[$poll_id]['pct']);
			}
			$t = array('text'=>'Total');
			$result->addData($t);
			$result->addTag('stats',implode('',$q_totals),false);
			$rows[] = $result->show();
		}
		$outer->addTag('comparison',implode('',$rows),false);
		$outer->addTag('names',implode('',$names),false);
		return $this->ajaxReturn(array('status'=>true,'html'=>$outer->show()));
	}

	function responseDetails() {
		$p_id = $_REQUEST['p_id'];
		$p_state = $_REQUEST['p_state'];
		$p_order_field = array_key_exists('sortby',$_REQUEST) ? $_REQUEST['sortby'] : 'created';
		$poll = $this->fetchSingle(sprintf('select * from %s where id = %d',$this->m_content,$p_id));
		$outer = new Forms();
		$inner = new Forms();
		$outer->init($this->getTemplate('responseDetails'),$fields['options']);
		$inner->init($this->getTemplate('responseDetailsRow'));
		$fields = $outer->buildForm($this->getFields('responseDetails'));
		$rowFields = $inner->buildForm($this->getFields('responseDetailsRow'));
		$p_asc_desc = array_key_exists('sortorder',$_REQUEST) ? $_REQUEST['sortorder'] : 'desc';
		$sql = sprintf('select pr.*, a.*, pr.id as response_id from poll_responses pr left join addresses a on a.ownerid = pr.id and a.ownertype="poll" where poll_id = %d and completed = %d',$p_id,$p_state);
		if (count($_POST) > 0 && array_key_exists('responseDetails',$_POST)) {
			$outer->addData($_POST);
			$outer->validate();
			foreach($fields as $key=>$field) {
				if (array_key_exists('opt_'.$key,$_POST) && array_key_exists($key,$_POST) && strlen($_POST['opt_'.$key]) && strlen($_POST[$key]) > 0) {
					if ($_POST['opt_'.$key] == 'like' && strpos('%',$_POST[$key]) == false)
						$sql .= sprintf(' and %s %s "%%%s%%"', $key, $_POST['opt_'.$key], $_POST[$key]);
					else
						$sql .= sprintf(' and %s %s "%s"', $key, $_POST['opt_'.$key], $_POST[$key]);
				}
			}
			foreach($this->getFields('responseDemographics') as $key=>$field) {
				if (array_key_exists('opt_'.$key,$_POST) && array_key_exists($key,$_POST) && strlen($_POST['opt_'.$key]) && strlen($_POST[$key]) > 0) {
					if ($_POST['opt_'.$key] == 'like' && strpos('%',$_POST[$key]) == false)
						$sql .= sprintf(' and %s %s "%%%s%%"', $key, $_POST['opt_'.$key], $_POST[$key]);
					else
						$sql .= sprintf(' and %s %s "%s"', $key, $_POST['opt_'.$key], $_POST[$key]);
				}
			}
		}
		$recs = $this->fetchAll($sql);
		if (array_key_exists('csv',$_POST) && $_POST['csv'] == 1) {
			return $this->responseCSV($sql);
		}
		$this->logMessage('responseDetails',sprintf('sql [%s] count [%d]',$sql,count($recs)),2);
		if (array_key_exists('pagenum',$_REQUEST)) 
			$pageNum = $_REQUEST['pagenum'];
		else
			$pageNum = 1;	// no 0 based calcs
		if ($pageNum <= 0) $pageNum = 1;
		$perPage = array_key_exists('pager',$_REQUEST) ? $_REQUEST['pager'] : $this->m_perrow;
		$pagination = $this->pagination(count($recs), $perPage, $pageNum, 
			array('prev'=>$this->M_DIR.'forms/paginationPrev.html','next'=>$this->M_DIR.'forms/paginationNext.html',
			'pages'=>$this->M_DIR.'forms/paginationPage.html', 'wrapper'=>$this->M_DIR.'forms/paginationWrapper.html'),
			$fields['options']);
		$start = ($pageNum-1)*$perPage;
		$sql .= sprintf(' order by %s %s limit %d,%d', $p_order_field, $p_asc_desc, $start, $perPage);
		$responses = $this->fetchAll($sql);
		$result = array();
		foreach($responses as $response) {
			$inner->reset();
			$inner->addData($response);
			$demo = array();
			foreach($this->getFields('responseDemographics') as $key=>$field) {
				$demo[] = sprintf('<td>%s</td>',array_key_exists($key,$response) ? $response[$key] : '');
			}
			$inner->addTag('demographics',implode('',$demo),false);
			$result[] = $inner->show();
		}
		$outer->addTag('pagination',$pagination,false);
		$outer->addData($_REQUEST);
		$search = array();
		$tmp = new Forms();
		$tmp->init($this->getTemplate('responseSearch'));
		$names = array();
		foreach($this->getFields('responseDemographics') as $key=>$field) {
			if ($poll['contact_'.$key]) {
				$names[] = sprintf('<th><span onclick=\'sort("%s","responseForm","/modit/ajax/%s/polls","foldertabs");\'>%s</span></th>',$key,$fields['options']['action'],$field);
				$tmp->reset();
				$opt = array(
					'select' => array('type'=>'select','lookup'=>'search_string','name'=>'opt_'.$key),
					'value' => array('type'=>'input','name'=>$key)
				);
				$opt = $tmp->buildForm($opt);
				$tmp->addTag('field',$field);
				$tmp->addData($_POST);
				$search[] = $tmp->show();
			}
		}
		$outer->addTag('title',$poll['title']);
		$outer->addTag('demographicNames',implode('',$names),false);
		$outer->addTag('search',implode('',$search),false);
		$outer->addTag('responses',implode('',$result),false);
		return $this->ajaxReturn(array('status'=>true,'html'=>$outer->show()));
	}

	function responseDetailsView() {
		$r_id = $_REQUEST['r_id'];
		if (!$response = $this->fetchSingle(sprintf('select * from %s where id = %d',$this->m_responses,$r_id))) {
			$this->addError("We couldn't locate that response");
			return $this->ajaxReturn(array('status'=>false));
		}
		$poll = $this->fetchSingle(sprintf('select * from %s where id = %d',$this->m_content,$response['poll_id']));
		$outer = new Forms();
		$outer->init($this->getTemplate('viewResponse'));
		$questions = $this->fetchAll(sprintf('select q.* from questions q, poll_questions pq where pq.poll_id = %d and q.id = pq.question_id and q.deleted = 0 order by pq.sequence',$response['poll_id']));
		$result = array();
		$q = new Forms();
		$q->init($this->getTemplate('viewResponseQuestion'));
		foreach($questions as $key=>$question) {
			$q->addData($question);
			$tmp = array();
			$sql = sprintf('select * from poll_response_answers pra left join question_answers qa on qa.id = pra.answer_id where pra.response_id = %d and pra.question_id = %d order by qa.sequence',$r_id,$question['id']);
			$responses = $this->fetchAll($sql);
			$this->logMessage('responseDetailsView',sprintf('sql [%s] responses [%s]',$sql,print_r($responses,true)),1);
			foreach($responses as $key=>$response) {
				if ($response['answer_id'] == 0)
					$tmp[] = $response['other'];
				else
					$tmp[] = $response['text'];
			}
			$q->addTag('answers',implode('<br/>',$tmp),false);
			$result[] = $q->show();
		}
		$demo = array();
		if ($demographics = $this->fetchSingle(sprintf('select * from addresses where ownertype="poll" and ownerid = %d',$r_id))) {
			foreach($this->getFields('viewResponseDemographics') as $key=>$field) {
				if ($poll['contact_'.$key])
					$demo[] = sprintf('<tr><th>%s:</th><td>%s</td>',$field['text'],$demographics[$field['field']]);
			}
		}
		$outer->addTag('demographics',implode('',$demo),false);
		$outer->addTag('questions',implode('',$result),false);
		return $this->ajaxReturn(array('status'=>true,'html'=>$outer->show()));
	}

	function responseCSV($sql) {
		$poll = $this->fetchSingle(sprintf('select * from %s where id = %d',$this->m_content,$_POST['p_id']));
		$records = $this->fetchAll($sql);
		$questions = $this->fetchAll(sprintf('select q.* from poll_questions pq, questions q where q.id = pq.question_id and pq.poll_id = %d order by pq.sequence',$poll['id']));
		ob_end_clean();
		header('Content-type: text/csv');
		header(sprintf('Content-Disposition: attachment; filename="%s-responses.csv"',preg_replace('#[^a-z0-9_]#i', '-', strtolower($poll['title']))));
		$hdr = 'id;ip_address;created;';
		$demo = array();
		foreach($poll as $key=>$value) {
			if (strpos($key,'contact_') !== false && $value == 1 && !($key == 'contact_info' || $key == 'contact_comments')) {
				$hdr .= sprintf('%s;',str_replace('contact_','',$key));
				$demo[] = str_replace('contact_','',$key);
			}
		}
		foreach($questions as $key=>$question) {
			$hdr .= sprintf('%s;',$question['name']);
		}
		echo $hdr.PHP_EOL;
		$demoFields = $this->getFields('viewResponseDemographics');
		$this->logMessage('responseCSV',sprintf('poll [%s] demo [%s] fields [%s]',print_r($poll,true),print_r($demo,true),print_r($demoFields,true)),1);
		foreach($records as $key=>$response) {
			$tmp = array($response['id'],$response['ip_address'],$response['created']);
			if (!$addr = $this->fetchSingle(sprintf('select * from addresses where ownertype="poll" and ownerid = %d',$response['id'])))
				$addr = array();
			foreach($demo as $key=>$field) {
				$tmp[] = array_key_exists($demoFields[$field]['field'],$addr) ? $addr[$demoFields[$field]['field']] : '';
			}
			foreach($questions as $key=>$question) {
				$answers = $this->fetchAll(sprintf('select pra.*, qa.text from poll_response_answers pra left join question_answers qa on qa.id = pra.answer_id where pra.response_id = %d and pra.question_id = %d',$response['id'],$question['id']));
				$a = array();
				foreach($answers as $key=>$answer) {
					if (strlen($answer['other']) > 0) {
						$text = str_replace("\r\n"," ",$answer['other']);
						$text = str_replace("\n"," ",$text);
						$text = str_replace("\r"," ",$text);
						$a[] = $text;
					}
					else
						$a[] = $answer['text'];
				}
				$tmp[] = sprintf('"%s"',str_replace('"','""',implode('~',$a)));
			}
			echo implode(';',$tmp).PHP_EOL;
		}
	}
}

?>
