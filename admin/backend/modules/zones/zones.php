<?php

class zones extends Backend {

	private $m_tree = 'zone_folders';
	private $m_content = 'zones';
	private $m_junction = 'zones_by_folder';
	private $m_perrow = 5;

    /**
     * @throws phpmailerException
     */
	public function __construct() {
		$this->M_DIR = 'backend/modules/zones/';
		$this->setTemplates(
			array(
				'deleteItem'=>$this->M_DIR.'forms/deleteItem.html',
				'deleteItemError'=>$this->M_DIR.'forms/deleteItemError.html',
				'deleteItemResult'=>$this->M_DIR.'forms/deleteItemResult.html',
				'main'=>$this->M_DIR.'zones.html',
				'form'=>$this->M_DIR.'forms/form.html',
				'showContentTree'=>$this->M_DIR.'forms/contenttree.html',
				'zonesInfo'=>$this->M_DIR.'forms/zonesInfo.html',
				'folderProperties'=>$this->M_DIR.'forms/folder.html',
				'showFolderContent'=>$this->M_DIR.'forms/folderContent.html',
				'folderInfo'=>$this->M_DIR.'forms/folderInfo.html',
				'articleList'=>$this->M_DIR.'forms/articleList.html',
				'showSearchForm'=>$this->M_DIR.'forms/searchForm.html',
				'addContent'=>$this->M_DIR.'forms/addContent.html',
				'header'=>$this->M_DIR.'forms/heading.html',
				'addFolder'=>$this->M_DIR.'forms/addFolder.html',
				'addZones'=>$this->M_DIR.'forms/addZones.html',
				'loadFSA'=>$this->M_DIR.'forms/loadFSA.html',
				'fsaRow'=>$this->M_DIR.'forms/fsaRow.html',
				'addFSA'=>$this->M_DIR.'forms/addFSA.html',
				'addFSASuccess'=>$this->M_DIR.'forms/addFSASuccess.html',
				'loadInterzone'=>$this->M_DIR.'forms/loadInterzone.html',
				'interzoneRow'=>$this->M_DIR.'forms/interzoneRow.html',
				'addInterzone'=>$this->M_DIR.'forms/addInterzone.html',
				'addInterzoneSuccess'=>$this->M_DIR.'forms/addInterzoneSuccess.html',
				'zoneKmCaps'=>$this->M_DIR.'forms/zoneKmCaps.html',
				'kmCaps'=>$this->M_DIR.'forms/kmCaps.html',
				'addMinMax'=>$this->M_DIR.'forms/addMinMax.html',
				'addMinMaxSuccess'=>$this->M_DIR.'forms/addMinMaxSuccess.html',
				'cloneZone'=>$this->M_DIR.'forms/cloneZone.html',
				'removeFSA'=>$this->M_DIR.'forms/removeFSA.html',
				'editFSA'=>$this->M_DIR.'forms/editFSA.html',
				'editFSARow'=>$this->M_DIR.'forms/editFSARow.html',
				'editFSASuccess'=>$this->M_DIR.'forms/editFSASuccess.html',
				'fsaForm'=>$this->M_DIR.'forms/fsaForm.html',
				'fsaFormRow'=>$this->M_DIR.'forms/fsaFormRow.html'
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
			'addContent'=>array(
				'options'=>array('method'=>'post','action'=>'/modit/ajax/addContent/zones'),
				'id'=>array('type'=>'tag','database'=>false),
				'title'=>array('type'=>'input','required'=>true,'prettyName'=>'Title'),
				'subtitle'=>array('type'=>'input','required'=>false,'prettyName'=>'Sub-Title'),
				'teaser'=>array('type'=>'textarea','required'=>true,'class'=>'mceSimple','prettyName'=>'Teaser Line'),
				'created'=>array('type'=>'datestamp','mask'=>'d-M-Y h:m:i','database'=>false),
				'description'=>array('type'=>'textarea','required'=>true,'id'=>'zonesBody','class'=>'mceAdvanced'),
				'enabled'=>array('type'=>'checkbox','required'=>false,'value'=>1,'checked'=>'checked'),
				'deleted'=>array('type'=>'checkbox','required'=>false,'value'=>1),
				'delivery_surcharge'=>array('type'=>'textfield','required'=>false,'validation'=>'number','value'=>0),
				'image1'=>array('type'=>'tag','required'=>false,'prettyName'=>'Image 1'),
				'image2'=>array('type'=>'tag','required'=>false),
				'imagesel_a'=>array('type'=>'image','unknown'=>true,'database'=>false,'id'=>'imagesel_a'),
				'imagesel_b'=>array('type'=>'image','unknown'=>true,'database'=>false,'id'=>'imagesel_b'),
				'submit'=>array('type'=>'submitbutton','database'=>false,'value'=>'Save'),
				'destFolders'=>array('name'=>'destFolders','type'=>'select','multiple'=>'multiple','required'=>true,'id'=>'destFolders','database'=>false,'options'=>$this->nodeSelect(0, 'zone_folders', 2, false, false),'reformatting'=>false,'prettyName'=>'Member Of'),
				'addContent'=>array('type'=>'hidden','value'=>1,'database'=>false)
			),
			'showSearchForm'=>array(
				'options'=>array('action'=>'showSearchForm','name'=>'searchForm','id'=>'search_form'),
				'opt_title'=>array('type'=>'select','name'=>'opt_title','lookup'=>'search_string'),
				'title'=>array('type'=>'input','required'=>false),
				'enabled'=>array('type'=>'select','lookup'=>'boolean'),
				'deleted'=>array('type'=>'select','lookup'=>'boolean'),
				'showSearchForm'=>array('type'=>'hidden','value'=>1),
				'pagenum'=>array('type'=>'hidden','value'=>1),
				'sortby'=>array('type'=>'hidden','value'=>'created'),
				'sortorder'=>array('type'=>'hidden','value'=>'desc'),
				'folder'=>array('type'=>'select','optionslist' => array('table'=>$this->m_tree,'root'=>0,'indent'=>2,'inclusive'=>false),'database'=>false),
				'fsa'=>array('type'=>'textfield','required'=>false,'maxlength'=>3),
				'quicksearch'=>array('type'=>'input','name'=>'quicksearch','required'=>false),
				'opt_quicksearch'=>array('type'=>'hidden','value'=>'like'),
				'pager'=>array('type'=>'select','required'=>true,'lookup'=>'paging','id'=>'pager'),
				'submit'=>array('type'=>'submitbutton','value'=>'Search')
			),
			'showFolderContent'=>array(
				'options'=>array('action'=>'showPageContent'),
				'description'=>array('type'=>'tag','reformatting'=>false),
				'image'=>array('type'=>'image','unknown'=>true),
				'rollover_image'=>array('type'=>'image','unknown'=>true),
				'sortby'=>array('type'=>'hidden','value'=>'sequence'),
				'sortorder'=>array('type'=>'hidden','value'=>'asc'),
				'pagenum'=>array('type'=>'hidden','value'=>1),
				'pager'=>array('type'=>'select','required'=>true,'value'=>$this->m_perrow,'lookup'=>'paging','id'=>'pager'),
				'showFolderContent'=>array('type'=>'hidden','value'=>1)
			),
			'folderProperties' => array(
				'options'=>array(
					'action'=>'/modit/zones/showPageProperties',
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
			'showContentTree' => array(),
			'zonesInfo' => array(),
			'showZonesContent' => array(),
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
				'deleted'=>array('type'=>'booleanIcon'),
				'downtown_surcharge'=>array('type'=>'tag')
			),
			'fsaRow'=>array(
				'zone_id'=>array('type'=>'tag'),
				//'downtown'=>array('type'=>'booleanIcon'),
				'enabled'=>array('type'=>'booleanIcon')
			),
			'addFSA'=>array(
				'fsa_id'=>array('type'=>'select','required'=>true,'prettyName'=>'FSA'),
				'enabled'=>array('type'=>'checkbox','value'=>1),
				'addFSA'=>array('type'=>'hidden','value'=>1,'database'=>false),
				'zone_id'=>array('type'=>'hidden'),
				'j_id'=>array('type'=>'hidden','database'=>false),
				'id'=>array('type'=>'hidden','database'=>false),
				//'downtown'=>array('type'=>'checkbox','value'=>'1'),
				'submit'=>array('type'=>'submitbutton','value'=>'Save','database'=>false)
			),
			'interzoneRow'=>array(
			),
			'addInterzone'=>array(
				'submit'=>array('type'=>'submitbutton','value'=>'Save','database'=>false),
				'zone_add'=>array('type'=>'select','required'=>true,'database'=>false),
				'zone_edit'=>array('type'=>'tag','database'=>false),
				'cost'=>array('type'=>'textfield','required'=>true,'validation'=>'number'),
				'addInterzone'=>array('type'=>'hidden','value'=>1,'database'=>false),
				'j_id'=>array('type'=>'hidden','database'=>false,'value'=>'%%junction:id%%'),
				'zz_id'=>array('type'=>'hidden','database'=>false,'value'=>'%%id%%')
			),
			'zoneKmCaps'=>array(
				'options'=>array('action'=>'zoneKmCaps','name'=>'zoneKmCaps','id'=>'zoneKmCaps','method'=>'POST'),
				'pagenum'=>array('type'=>'hidden','value'=>1),
				'sortby'=>array('type'=>'hidden','value'=>'zoneName'),
				'sortorder'=>array('type'=>'hidden','value'=>'asc'),
				'pager'=>array('type'=>'select','required'=>true,'value'=>$this->m_perrow,'lookup'=>'paging','id'=>'pager'),
				'zoneKmCaps'=>array('type'=>'hidden','value'=>1),
				'zones'=>array('type'=>'select','sql'=>'select id, title from zones order by 2'),
				'groups'=>array('name'=>'groups','type'=>'select','required'=>true,'id'=>'destFolders','database'=>false,'options'=>$this->nodeSelect(0, 'zone_folders', 2, false, false),'reformatting'=>false,'prettyName'=>'Zone Groups'),
				'products'=>array('name'=>'products','type'=>'select','multiple'=>'multiple','sql'=>sprintf('select p.id, p.name from product p, product_by_folder pf where pf.folder_id = %d and p.id = pf.product_id order by 2',DELIVERY_TYPES))
			),
			'kmCaps'=>array(),
			'addMinMax'=>array(
				'zone_from'=>array('type'=>'select','required'=>true,'database'=>false),
				'zone_to'=>array('type'=>'select','required'=>true,'database'=>false),
				'groups'=>array('name'=>'groups','type'=>'select','required'=>true,'id'=>'destFolders','database'=>false,'options'=>$this->nodeSelect(0, 'zone_folders', 2, false, false),'reformatting'=>false,'prettyName'=>'Zone Groups','required'=>true,'database'=>false),
				'product_id'=>array('name'=>'product_id','type'=>'select','required'=>true,'sql'=>sprintf('select p.id, p.name from product p, product_by_folder pf where pf.folder_id = %d and p.id = pf.product_id order by 2',DELIVERY_TYPES)),
				'custom_km_mincharge'=>array('type'=>'textfield','required'=>true,'validation'=>'number','value'=>'0.00'),
				'custom_km_maxcharge'=>array('type'=>'textfield','required'=>true,'validation'=>'number','value'=>'0.00'),
				'zone_to_zone_id'=>array('type'=>'textarea','required'=>false),
				'save'=>array('type'=>'submitbutton','value'=>'Save','database'=>false),
				'c_id'=>array('type'=>'hidden','value'=>0,'database'=>false),
				'addMinMax'=>array('type'=>'hidden','value'=>1,'database'=>false)
			),
			'addMinMax-Edit'=>array(
				'custom_km_mincharge'=>array('type'=>'textfield','required'=>true,'validation'=>'number','value'=>'0.00'),
				'custom_km_maxcharge'=>array('type'=>'textfield','required'=>true,'validation'=>'number','value'=>'0.00'),
				'save'=>array('type'=>'submitbutton','value'=>'Save','database'=>false),
				'c_id'=>array('type'=>'hidden','value'=>0,'database'=>false),
				'addMinMax'=>array('type'=>'hidden','value'=>1,'database'=>false)
			),
			'cloneZone'=>array(
				'g_id'=>array('type'=>'select','required'=>true,'options'=>$this->nodeSelect(0, 'zone_folders', 2, false, false),'reformatting'=>false,'prettyName'=>'Zone Groups','database'=>false),
				'title'=>array('type'=>'textfield','required'=>true,'prettyName'=>'New Name'),
				'save'=>array('type'=>'submitbutton','value'=>'Clone it'),
				'cloneZone'=>array('type'=>'hidden','value'=>1)
			),
			'editFSA'=>array(
				'fsa'=>array('type'=>'textfield','required'=>true,'maxlength'=>3),
				'enabled'=>array('type'=>'checkbox','value'=>'1','required'=>false,'checked'=>'checked'),
				'downtown'=>array('type'=>'checkbox','value'=>'1','required'=>false),
				'deleted'=>array('type'=>'checkbox','value'=>'1','required'=>false),
				'submit'=>array('type'=>'submitbutton','database'=>false,'value'=>'Save'),
				'fsa_id'=>array('type'=>'hidden','database'=>false,'value'=>'%%id%%'),
				'editFSA'=>array('type'=>'hidden','value'=>1,'database'=>false)
			),
			'editFSARow'=>array(
				'enabled'=>array('type'=>'booleanIcon')
			),
			'fsaForm'=>array(
				'options'=>array('method'=>'post','action'=>'/modit/ajax/fsa/zones'),
				'fsaForm'=>array('type'=>'hidden','value'=>1),
				'pagenum'=>array('type'=>'hidden','value'=>1),
				'sortby'=>array('type'=>'hidden','value'=>'fsa'),
				'sortorder'=>array('type'=>'hidden','value'=>'asc'),
				'pager'=>array('type'=>'select','required'=>true,'value'=>$this->m_perrow,'lookup'=>'paging','id'=>'pager')
			),
			'fsaFormRow'=>array(
				'enabled'=>array('type'=>'booleanIcon'),
				'deleted'=>array('type'=>'booleanIcon'),
				'downtown'=>array('type'=>'booleanIcon')
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
		$frmFields = $this->getFields('main');
		$frmFields = $form->buildForm($frmFields);
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
		if (!(array_key_exists('id',$_REQUEST) && $data = $this->fetchSingle(sprintf('select * from %s where id = %d',$this->m_tree, $_REQUEST['id']))))
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
				$stmt = $this->prepare(sprintf('update %s set %s where id = %d',$this->m_tree,implode(',',$flds),$data['id']));
				$stmt->bindParams(array_merge(array(str_repeat('s', count($flds))),$values));
				$status = $stmt->execute();
				if ($status) {
					if ($this->isAjax()) {
						$this->logMessage('showPageProperties', 'executing ajax success return', 3);
						$this->addMessage('Record successfully added');
						return $this->ajaxReturn(array(
								'status'=>'true',
								'html'=>'',
								'url'=>'/modit/zones?p_id='.$data['id']
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
			if ($data = $this->fetchSingle(sprintf('select * from %s where id = %d',$this->m_tree,$_REQUEST['p_id']))) {
				$form = new Forms();
				$data['notes'] = nl2br($data['notes']);
				$template = 'folderInfo';
				$frmFields = $this->getFields($template);
				$form->init($this->getTemplate($template), array());
				$frmFields = $form->buildForm($frmFields);
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
		$j_id = array_key_exists('j_id',$_REQUEST) ? $_REQUEST['j_id'] : 0;
		$form = new Forms();
		if ($p_id > 0 && $data = $this->fetchSingle(sprintf('select * from %s where id = %d',$this->m_tree,$p_id))) {
			if (strlen($data['alternate_title']) > 0) $data['connector'] = '&nbsp;-&nbsp;';
			$form->init($this->getTemplate('showFolderContent'),array('name'=>'showFolderContent'));
			$frmFields = $this->getFields('showFolderContent');
			$frmFields = $form->buildForm($frmFields);
			if (array_key_exists('pagenum',$_REQUEST)) 
				$pageNum = $_REQUEST['pagenum'];
			else
				$pageNum = 1;	// no 0 based calcs
			if ($pageNum <= 0) $pageNum = 1;
			if (array_key_exists('pager',$_REQUEST)) 
				$perPage = $_REQUEST['pager'];
			else {
				$tmp = $this->checkArray("formData:zonesSearchForm:pager",$_SESSION);
				if ($tmp > 0) 
					$perPage = $tmp;
				else
				$perPage = $this->m_perrow;
			}
			$form->setData('pager',$perPage);
			$count = $this->fetchScalar(sprintf('select count(n.id) from %s n where n.id in (select f.zone_id from %s f where f.folder_id = %d)', $this->m_content, $this->m_junction, $_REQUEST['p_id']));
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
			$sql = sprintf('select a.*, f.id as j_id from %s a left join %s f on a.id = f.zone_id where f.folder_id = %d order by %s %s limit %d,%d',  $this->m_content, $this->m_junction, $_REQUEST['p_id'],$sortby, $sortorder, $start,$perPage);
			$zones = $this->fetchAll($sql);
			$this->logMessage('showPageContent', sprintf('sql [%s], records [%d]',$sql, count($zones)), 2);
			$articles = array();
			foreach($zones as $article) {
				$frm = new Forms();
				$tmp = $this->getFields('articleList');
				$frm->init($this->getTemplate('articleList'),array());
				$tmp = $frm->buildForm($tmp);
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
		$frmFields = $this->getFields('showSearchForm');
		$frmFields = $form->buildForm($frmFields);
		if (count($_POST) == 0)
			if (array_key_exists('formData',$_SESSION) && array_key_exists('zonesSearchForm', $_SESSION['formData']))
				$_POST = $_SESSION['formData']['zonesSearchForm'];
			else
				$_POST = array('showSearchForm'=>1,'deleted'=>0,'sortby'=>'created','sortorder'=>'desc');
		$this->logMessage("showSearchForm",sprintf("post [%s]",print_r($_POST,true)),3);
		if (count($_POST) > 0 && array_key_exists('showSearchForm',$_POST)) {
			if ((!array_key_exists('deleted',$_POST)) || strlen($_POST['deleted']) == 0) $_POST['deleted'] = 0;
			$form->addData($_POST);
			if ($form->validate()) {
				$_SESSION['formData']['zonesSearchForm'] = $form->getAllData();
				$srch = array();
				foreach($frmFields as $key=>$value) {
					switch($key) {
						case 'quicksearch':
							if (array_key_exists('opt_quicksearch',$_POST) && $_POST['opt_quicksearch'] != '' && $value = $form->getData($key)) {
								if ($_POST['opt_quicksearch'] == 'like' && strpos($value,'%',0) === false) {
									$value = '%'.$value.'%';
								}
								$tmp = array();
								$tmp[] = sprintf(' title %s "%s"',$_POST['opt_quicksearch'],$this->escape_string($value));
								$tmp[] = sprintf(' teaser %s "%s"',$_POST['opt_quicksearch'],$this->escape_string($value));
								$tmp[] = sprintf(' description %s "%s"',$_POST['opt_quicksearch'],$this->escape_string($value));
								$srch = array(sprintf('(%s)',implode(' or ',$tmp)));
								continue 2;
							}
							break;
						case 'fsa':
							$srch[] = sprintf("n.id in (select zone_id from zone_fsa z, fsa f where f.fsa = '%s' and z.fsa_id = f.id)", $form->getData($key));
							break;
						case 'title':
							if (array_key_exists('opt_title',$_POST) && $_POST['opt_title'] != '' && $value = $form->getData($key)) {
								if ($_POST['opt_title'] == 'like' && strpos($value,'%',0) === false) {
									$value = '%'.$value.'%';
								}
								$srch[] = sprintf(' title %s "%s"',$_POST['opt_title'],$this->escape_string($value));
							}
							break;
						case 'created':
						case 'start_date':
						case 'end_date':
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
								$srch[] = sprintf(' n.id in (select zone_id from %s where folder_id = %d) ', $this->m_junction, $value);
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
					$this->logMessage('showSearchForm',sprintf('search options [%s]',print_r($srch,true)),3);
					if (array_key_exists('pagenum',$_REQUEST))
						$pageNum = $_REQUEST['pagenum'];
					else
						$pageNum = 1;	// no 0 based calcs
					if (array_key_exists('pager',$_REQUEST)) 
						$perPage = $_REQUEST['pager'];
					else {
						$tmp = $this->checkArray("formData:zonesSearchForm:pager",$_SESSION);
						if ($tmp > 0) 
							$perPage = $tmp;
						else
						$perPage = $this->m_perrow;
					}
					$form->setData('pager',$perPage);
					$count = $this->fetchScalar(sprintf('select count(n.id) from %s n where 1=1 and %s', $this->m_content, implode(' and ',$srch)));
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
					//$sql = sprintf('select n.*, j.id as j_id from %s n, %s j where n.id = j.zone_id and j.id = (select min(j1.id) from %s j1 where j1.zone_id = n.id) and %s order by %s %s limit %d,%d',
					//	 $this->m_content, $this->m_junction, $this->m_junction, implode(' and ',$srch),$sortby, $sortorder, $start,$perPage);
					$sql = sprintf('select n.*, 0 as j_id from %s n where 1=1 and %s order by %s %s limit %d,%d',
						 $this->m_content, implode(' and ',$srch),$sortby, $sortorder, $start,$perPage);
					$recs = $this->fetchAll($sql);
					$this->logMessage('showSearchForm', sprintf('sql [%s] records [%d]',$sql,count($recs)), 2);
					$articles = array();
					foreach($recs as $article) {
						$frm = new Forms();
						$tmp = $this->getFields('articleList');
						$frm->init($this->getTemplate('articleList'),array());
						$tmp = $frm->buildForm($tmp);
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
		$frmFields = $this->getFields('showSearchResults');
		$frmFields = $form->buildForm($frmFields);
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
			$data = array('id'=>0,'image1'=>'','image2'=>''); 
		}
		if (count($_REQUEST) > 0 && array_key_exists('destFolders',$_REQUEST) || $data['id'] > 0) {
			$ids = array();
			if (array_key_exists('destFolders',$_REQUEST)) {
				$ids = $_REQUEST['destFolders'];
				if (!is_array($ids)) $ids = array($ids);
			}
			if ($data['id'] > 0) {
				$tmp = $this->fetchScalarAll(sprintf('select folder_id from %s where zone_id = %d', $this->m_junction, $data['id']));
				$ids = array_merge($ids,$tmp);
			}
			if (count($ids) > 0) {
				$data['destFolders'] = $ids;
			}
		}
		$frmFields = $form->buildForm($frmFields);
		$data['imagesel_a'] = $data['image1'];
		$data['imagesel_b'] = $data['image2'];
		$data['j_id'] = array_key_exists('j_id',$_REQUEST) ? $_REQUEST['j_id'] : 0;
		$data['fsa'] = $this->loadFSA($data);
		$data['interzone'] = $this->loadInterzone($data);
		$form->addData($data);
		$status = 'false';	//assume it failed
		if (count($_POST) > 0 && array_key_exists('addContent',$_POST)) {
			$_POST['imagesel_a'] = $_POST['image1'];
			$_POST['imagesel_b'] = $_POST['image2'];
			$form->addData($_POST);
			if ($form->validate()) {
				$id = $_POST['a_id'];
				unset($frmFields['a_id']);
				unset($frmFields['options']);
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
					if ($id == 0) {
						$id = $this->insertId();
						$data['id'] = $id;
						$form->setData('id',$id);
					}
					$destFolders = $_POST['destFolders'];
					if (!is_array($destFolders)) $destFolders = array($destFolders);
					//
					//	delete folders we are no longer a member of
					//
					$this->execute(sprintf('delete from %s where zone_id = %d and folder_id not in (%s)', $this->m_junction, $id,implode(',',$destFolders)));
					$new = $this->fetchScalarAll(sprintf('select id from %s where id in (%s) and id not in (select folder_id from %s where zone_id = %d and folder_id in (%s))',
						$this->m_tree,implode(',',$destFolders),$this->m_junction,$id,implode(',',$destFolders)));
					$status = true;
					foreach($new as $key=>$folder) {
						$obj = new preparedStatement(sprintf('insert into %s(zone_id,folder_id) values(?,?)',$this->m_junction));
						$obj->bindParams(array('dd',$id,$folder));
						$status = $status && $obj->execute();
						$this->resequence($folder);
					}
					if ($status) {
						$this->commitTransaction();
						if ($status) {
							return $this->ajaxReturn(array(
								'status' => 'true',
								'url' => sprintf('/modit/zones?p_id=%d',$destFolders[0])
							));
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
			if ($folder = $this->fetchSingle(sprintf('select * from %s where id = %d',$this->m_tree,$dest))) {
				$curr = $this->fetchScalar(sprintf('select zone_id from %s where id = %d',$this->m_junction,$src));
				$status = true;
				if (array_key_exists('move',$_REQUEST)) {
					//
					//	move it - delete all other folders
					//
					$this->logMessage('moveArticle', sprintf('moving zone %d to folder %d',$src,$dest),2);
					$this->beginTransaction();
					if ($status = $this->execute(sprintf('delete from %s where id = %d', $this->m_junction, $src))) {
						if (!$this->fetchSingle(sprintf('select * from %s where zone_id = %d and folder_id = %d',$this->m_junction,$curr,$dest))) {
							$obj = new preparedStatement(sprintf('insert into %s(zone_id,folder_id) values(?,?)',$this->m_junction));
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
					$curr = $src;
					$this->logMessage('moveArticle', sprintf('cloning zone %d to folder %d',$curr,$dest),2);
					if (!($this->fetchSingle(sprintf('select * from %s where zone_id = %d and folder_id = %d',$this->m_junction,$curr,$dest)))) {
						$obj = new preparedStatement(sprintf('insert into %s(zone_id,folder_id) values(?,?)',$this->m_junction));
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
				$this->addMessage('Either the source or destination article was not found');
			}
			else {
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
     * @return false|string
     * @throws phpmailerException
     */
    function deleteArticle() {
		$form = new Forms();
		$form->init($this->getTemplate('deleteItem'));
		$flds = $this->getFields('deleteItem');
		$flds = $form->buildForm($flds);
		if (count($_REQUEST) > 0 && $_REQUEST['j_id'] == 0)
			$form->getField('one')->addAttribute('disabled','disabled');
		$form->addData($_REQUEST);

		$ct = $this->fetchScalar(sprintf("select count(0) from zone_fsa where zone_id = %d", $form->getData("a_id")));
		if ($ct > 0) {
			$form->init($this->getTemplate("deleteItemError"));
		}

		if (count($_REQUEST) > 0 && array_key_exists('deleteItem',$_REQUEST)) {
			if ($form->validate()) {
				$type = $form->getData('action');
				switch($type) {
					case 'cancel':
						return $this->ajaxReturn(array('status'=>'true','code'=>'closePopup();'));
						break;
					case 'all':
						//$img = $this->fetchScalar(sprintf('select zone_id from %s where id = %d',$this->m_junction,$_REQUEST['j_id']));
						$this->execute(sprintf('delete from %s where zone_id = %d',$this->m_junction,$_REQUEST['a_id']));
						$this->execute(sprintf('update %s set deleted = 1 where id = %d',$this->m_content,$_REQUEST['a_id']));
						break;
					case 'one':
						$status = $this->execute(sprintf('delete from %s where id = %d',$this->m_junction,$_REQUEST['j_id']));
						$ct = $this->fetchScalar(sprintf('select count(0) from %s where zone_id = %d',$this->m_junction,$_REQUEST['a_id']));
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
			$curr = $this->fetchScalar(sprintf('select zones_id from %s where id = %d',$this->m_junction,$id));
			$this->logMessage('deleteArticle', sprintf('deleting zone junction %d for store %d',$id,$curr), 2);
			$this->beginTransaction();
			$this->execute(sprintf('delete from %s where id = %d',$this->m_junction,$id));
			if (($remining = $this->fetchScalar(sprintf('select count(0) from %s where zone_id = %d',$this->m_junction,$curr))) == 0) {
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
			$ct = $this->fetchScalar(sprintf('select count(0) from %s where folder_id = %d',$this->m_junction,$_REQUEST['p_id']));
			if ($ct > 0) {
				$this->addError('Ads are still attached to this folder');
				return $this->ajaxReturn(array('status'=>'false'));
			}
			$ct = $this->fetchScalar(sprintf('select count(0) from %s t1, %s t2 where t2.id = %d and t1.left_id > t2.left_id and t1.right_id < t2.right_id and t1.level > t2.level',$this->m_tree, $this->m_tree, $_REQUEST['p_id']));
			if ($ct > 0) {
				$this->addError('Other categories are still attached to this folder');
				return $this->ajaxReturn(array('status'=>'false'));
			}
			if (!$this->deleteCheck('zones',$_REQUEST['p_id'],$inUse)) {
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
		$flds = $this->getFields('showSearchForm');
		$flds = $form->buildForm($flds);
		if (array_key_exists('p_id',$_REQUEST))
			$form->addTag("j_id",$_REQUEST["p_id"]);
		else
			$form->addTag("j_id",0);
		if (count($_POST) > 0 && array_key_exists('showSearchForm',$_POST))
			$form->addData($_POST);
		else
			if (array_key_exists('formData',$_SESSION) && array_key_exists('zonesSearchForm', $_SESSION['formData']))
				$form->addData($_SESSION['formData']['zonesSearchForm']);
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
    function addZones($fromMain = false) {
		$form = new Forms();
		$form->init($this->getTemplate('addZones'));
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
		if (array_key_exists('formData',$_SESSION) && array_key_exists('zonesSearchForm', $_SESSION['formData'])) {
			$_POST = $_SESSION['formData']['zonesSearchForm'];
			$msg = "";
		}
		else {
			$ct = $this->fetchScalar(sprintf('select count(0) from %s where enabled = 0',$this->m_content));
			if ($ct == 0) {
				$_POST = array('showSearchForm'=>1,'deleted'=>0,'sortby'=>'created','sortorder'=>'desc');
				$msg = "Showing latest zones added";
			}
			else {
				$_POST = array('showSearchForm'=>1,'enabled'=>0,'sortby'=>'created','sortorder'=>'desc');
				$msg = "Showing disabled zones";
			}
		}
		$result = $this->showSearchForm($fromMain,$msg);
		return $result;
	}

    /**
     * @param $folder
     * @return void
     * @throws phpmailerException
     */
    function resequence($folder) {
		$this->logMessage('resequence', "resequencing folder $folder", 2);
		$articles = $this->fetchAll(sprintf('select * from %s where folder_id = %d order by sequence',$this->m_junction,$folder));
		$seq = 10;
		foreach($articles as $article) {
			$this->execute(sprintf('update %s set sequence = %d where id = %d',$this->m_junction,$seq,$article['id']));
			$seq += 10;
		}
	}

    /**
     * @param array $data
     * @return array|false|mixed|string|string[]
     * @throws phpmailerException
     */
    function loadFSA($data = array()) {
		if (array_key_exists('z_id',$_REQUEST)) $data["id"] = $_REQUEST["z_id"];
		if (array_key_exists('j_id',$_REQUEST)) $data["j_id"] = $_REQUEST["j_id"];
		if ($data["id"] == 0) return "";
		$form = new Forms();
		$form->init($this->getTemplate("loadFSA"));

		if (array_key_exists('pagenum',$_REQUEST)) 
			$pageNum = $_REQUEST['pagenum'];
		else
			$pageNum = 1;	// no 0 based calcs
		if ($pageNum <= 0) $pageNum = 1;
		$perPage = 10;
		if (array_key_exists('pager',$_REQUEST)) 
			$perPage = $_REQUEST['pager'];
		$count = $this->fetchScalar(sprintf("select count(0) from zone_fsa where zone_id = %d",$data["id"]));
		$pagination = $this->pagination($count, $perPage, $pageNum, 
			array('prev'=>$this->M_DIR.'forms/altPaginationPrev.html','next'=>$this->M_DIR.'forms/altPaginationNext.html',
					'pages'=>$this->M_DIR.'forms/altPaginationPage.html', 'wrapper'=>$this->M_DIR.'forms/paginationWrapper.html','url'=>'/modit/ajax/loadFSA/zones&z_id='.$data["id"]));
		$form->addTag("pagination",$pagination,false);

		$fsa = $this->fetchAll(sprintf("select z.*, f.fsa from zone_fsa z, fsa f where z.zone_id = %d and f.id = z.fsa_id order by f.fsa limit %d,%d",$data["id"],($pageNum-1)*$perPage,$perPage));
		$rows = array();
		$line = new Forms();
		$line->init($this->getTemplate("fsaRow"));
		$flds = $this->getFields("fsaRow");
		$flds = $line->buildForm($flds);
		foreach($fsa as $key=>$value) {
			$line->addData($value);
			$rows[] = $line->show();
		}
		$form->addData($data);
		$form->addTag("rows",implode("",$rows),false);
		if (array_key_exists('z_id',$_REQUEST))
			return $this->ajaxReturn(array('status'=>true,'html'=>$form->show()));
		else
			return $form->show();
	}

    /**
     * @return false|string
     * @throws phpmailerException
     */
    function addFSA() {
		$form = new Forms();
		$form->init($this->getTemplate(__FUNCTION__));
		$flds = $this->getFields(__FUNCTION__);
		$z = array_key_exists("zone_id",$_REQUEST) ? $_REQUEST["zone_id"] : 0;
		$id = array_key_exists("id",$_REQUEST) ? $_REQUEST["id"] : 0;
		$j_id = array_key_exists("j_id",$_REQUEST) ? $_REQUEST["j_id"] : 0;
		$form->addData($_REQUEST);

		//
		//	FSA's can now belong to multiple zones, but only 1 zone / folder
		//
		$zones = $this->fetchScalarAll(sprintf("select z2.zone_id from zones_by_folder z1, zones_by_folder z2 where z1.id = %d and z2.folder_id = z1.folder_id", $j_id));
		if ($id == 0) {
			$flds["fsa_id"]["sql"] = sprintf("select id, fsa from fsa where enabled = 1 and deleted = 0 and id not in (select fsa_id from zone_fsa zf where zf.zone_id in (%s)) order by fsa", implode(",",$zones),$id);
		}
		else {
			$flds["fsa_id"]["sql"] = sprintf("select f.id, fsa from fsa f, zone_fsa z where z.id = %d and f.id = z.fsa_id", $id);
			$form->addData($this->fetchSingle(sprintf("select * from zone_fsa where id = %d", $id)));
		}
/*
		if (!$fsa = $this->fetchSingle(sprintf("select * from zone_fsa where id = %d",$fsa))) {
			$fsa = array("id"=>0,"zone_id"=>$z);
			$flds["fsa_id"]["sql"] = sprintf("select id, fsa from fsa where id not in (select fsa_id from zone_fsa where zone_id = %d) and enabled = 1 and deleted = 0 order by fsa",$z);
		}
		else $flds["fsa_id"]["sql"] = sprintf("select id, fsa from fsa where id = %d",$fsa["fsa_id"]);
*/
		$flds = $form->buildForm($flds);
		//$form->addData($fsa);
		if (count($_POST) > 0 && array_key_exists(__FUNCTION__,$_POST)) {
			$form->addData($_POST);
			if ($valid = $form->validate()) {
				$rec = $form->getAllData();
/*
				$this->logMessage(__FUNCTION__,sprintf("form data [%s]",print_r($rec,true)),1);
				$ct = $this->fetchSingle(sprintf("select * from zone_fsa where fsa_id = %d",$rec["fsa_id"]));
				if ($rec["id"] == 0) {
					if (is_array($ct) > 0) {
						$this->addError("This FSA has already been assigned to a zone");
						$valid = false;
					}
				} else {
					if (is_array($ct) && $ct["zone_id"] != $rec["zone_id"]) {
						$this->addError("This FSA has already been assigned to a zone");
						$valid = false;
					}
				}
*/
				if ($valid) {
					$values = array();
					foreach($flds as $key=>$fld) {
						if (!(array_key_exists('database',$fld) && $fld['database'] == false)) {
							$values[$fld['name']] = $form->getData($fld['name']);
						}
					}
					if ($rec["id"] == 0) {
						$stmt = $this->prepare(sprintf('insert into zone_fsa(%s) values(%s)', implode(',',array_keys($values)), str_repeat('?,', count($values)-1).'?'));
					}
					else {
						$stmt = $this->prepare(sprintf('update zone_fsa set %s=? where id = %d', implode('=?,',array_keys($values)),$rec['id']));
					}
					$this->beginTransaction();
					$stmt->bindParams(array_merge(array(str_repeat("s",count($values))),array_values($values)));
					if ($status = $stmt->execute()) {
						$this->addMessage("validated");
						$this->commitTransaction();
						$form->init($this->getTemplate("addFSASuccess"));
					}
					else {
						$this->rollbackTransaction();
					}
				}
			}
			else {
				$this->addError("validation error");
			}
		}
		$this->logMessage(__FUNCTION__,sprintf("form [%s]",print_r($form,true)),1);
		return $this->ajaxReturn(array("status"=>true,"html"=>$form->show()));
	}

    /**
     * @param array $data
     * @return array|false|mixed|string|string[]
     * @throws phpmailerException
     */
    function loadInterzone($data = array()) {
		if (array_key_exists('z_id',$_REQUEST)) $data["id"] = $_REQUEST["z_id"];
		if ($data["id"] == 0) return "";
		$form = new Forms();
		$form->init($this->getTemplate("loadInterzone"));
		$costs = $this->fetchAll(sprintf("
select zz.*, z.title
from zone_to_zone zz, zones_by_folder zf1, zones_by_folder zf2, zones z
where zf1.id = %d and zz.zone_from = zf1.id and zf2.id = zz.zone_to and z.id = zf2.zone_id
union
select zz.*, z.title
from zone_to_zone zz, zones_by_folder zf1, zones_by_folder zf2, zones z
where zf1.id = %d and zz.zone_to = zf1.id and zf2.id = zz.zone_from and z.id = zf2.zone_id
",$_REQUEST["j_id"],$_REQUEST["j_id"]));
		//$costs = $this->fetchAll(sprintf("SELECT zz.* FROM zone_to_zone zz, zones_by_folder zf WHERE (zz.zone_from = zf.id or zz.zone_to = zf.id) and zf.zone_id = %d",$data["id"]));
		$rows = array();
		$line = new Forms();
		$line->init($this->getTemplate("interzoneRow"));
		$flds = $this->getFields("interzoneRow");
		$flds = $line->buildForm($flds);
		foreach($costs as $key=>$value) {
			$line->addData($value);
			$rows[] = $line->show();
		}
		$form->addData($data);
		$form->addTag("rows",implode("",$rows),false);
		if (array_key_exists('z_id',$_REQUEST))
			return $this->ajaxReturn(array('status'=>true,'html'=>$form->show()));
		else
			return $form->show();
	}

    /**
     * @return false|string
     * @throws phpmailerException
     */
    function addInterzone() {
		$j_id = array_key_exists("j_id",$_REQUEST) ? $_REQUEST["j_id"] : 0;
		$zz_id = array_key_exists("zz_id",$_REQUEST) ? $_REQUEST["zz_id"] : 0;
		$flds = $this->getFields("addInterzone");
		if (!$jn = $this->fetchSingle(sprintf("select zf.*, z.title from zones_by_folder zf, zones z where zf.id = %d and z.id = zf.zone_id",$j_id)))
			$jn = array("id"=>0,"folder_id"=>0,"zone_id"=>0);
		$exists = $this->fetchScalarAll(sprintf("
select distinct zf.zone_id from zones_by_folder zf, zone_to_zone zz where zz.zone_to = %d and zf.id = zz.zone_from
union
select distinct zone_id from zones_by_folder zf, zone_to_zone zz where zz.zone_from = %d and zf.id = zz.zone_to",$jn["id"],$jn["id"]));
		//$flds["zone_add"]["sql"] = sprintf("select z.id, z.title from zones z, zones_by_folder zf where zf.folder_id = %d and z.id = zf.zone_id and zf.id != %d and z.id not in (%s)",$jn["folder_id"],$jn["id"],implode(",",array_merge(array(0),$exists)));

		//
		//	Special MS request - allow zone to same zone limits for the hospital district special handling
		//
		$flds["zone_add"]["sql"] = sprintf("select z.id, z.title from zones z, zones_by_folder zf where zf.folder_id = %d and z.id = zf.zone_id and z.id not in (%s)",$jn["folder_id"],implode(",",array_merge(array(0),$exists)));

		$this->logMessage(__FUNCTION__,sprintf("zone_add sql [%s]", $flds["zone_add"]["sql"]),1);
		if (!$data = $this->fetchSingle(sprintf("select * from zone_to_zone where id = %d",$zz_id))) {
			$data = array('id'=>0,'cost'=>0.00);
		} else {
			if ($data["zone_from"] == $j_id)
				$dest = $this->fetchSingle(sprintf("select * from zones z, zones_by_folder zf where zf.id = %d and z.id = zf.zone_id",$data["zone_to"]));
			else
				$dest = $this->fetchSingle(sprintf("select * from zones z, zones_by_folder zf where zf.id = %d and z.id = zf.zone_id",$data["zone_from"]));
			$data["dest"] = $dest;
		}
		if ($data["id"] != 0) unset($flds["zone_add"]);
		$data["junction"] = $jn;
		$form = new Forms();
		$form->init($this->getTemplate("addInterzone"));
		$flds = $flds = $form->buildForm($flds);
		$form->addData($data);
		if (array_key_exists("addInterzone",$_REQUEST)) {
			$form->addData($_POST);
			if ($valid = $form->validate()) {
				$values = array();
				foreach($flds as $key=>$fld) {
					if (!(array_key_exists('database',$fld) && $fld['database'] == false)) {
						$values[$fld['name']] = $form->getData($fld['name']);
					}
				}
				if ($data["id"] == 0) {
					$values["zone_from"] = $jn["id"];
					$values["zone_to"] = $this->fetchScalar(sprintf("select id from zones_by_folder where folder_id = %d and zone_id = %d",$jn["folder_id"],$_REQUEST["zone_add"]));
					$stmt = $this->prepare(sprintf('insert into zone_to_zone(%s) values(%s)', implode(',',array_keys($values)), str_repeat('?,', count($values)-1).'?'));
				}
				else {
					$stmt = $this->prepare(sprintf('update zone_to_zone set %s=? where id = %d', implode('=?,',array_keys($values)),$data['id']));
				}
				$this->beginTransaction();
				$stmt->bindParams(array_merge(array(str_repeat("s",count($values))),array_values($values)));
				if ($status = $stmt->execute()) {
					$this->commitTransaction();
					$form->init($this->getTemplate("addInterzoneSuccess"));
				}
				else {
					$this->rollbackTransaction();
					$this->addError("something happened");
				}
			}
			else $this->addError("validate failed");
		}
		return $this->ajaxReturn(array('status'=>true,'html'=>$form->show()));
	}

    /**
     * @return array|false|mixed|string|string[]|void
     * @throws phpmailerException
     */
    function zoneKmCaps() {
		$outer = new Forms();
		$outer->init($this->getTemplate("zoneKmCaps"));
		$flds = $this->getFields("zoneKmCaps");

		if (count($_POST) == 0 || !(array_key_exists("zoneKmCaps",$_POST))) {
			if (array_key_exists('formData',$_SESSION) && array_key_exists('zoneKmCaps', $_SESSION['formData']))
				$_POST = $_SESSION['formData']['zoneKmCaps'];
			else
				$_POST = array("pagenum"=>1,"pager"=>$this->m_perrow,"zoneKmCaps"=>1,"sortby"=>"z1.title","sortorder"=>"asc");
		}
		$outer->addData($_POST);
		if (array_key_exists("groups",$_POST) && $_POST["groups"] > 0) {
			$flds["zones"]["sql"] = sprintf("select z.id, z.title from zones z, zones_by_folder zf where zf.folder_id = %d and z.id = zf.zone_id order by 2",$_POST["groups"]);
		}
		$flds = $outer->buildForm($flds);
		$srch = array("1=1");
		foreach($_POST as $key=>$value) {
			switch($key) {
				case "groups":
					if ($value != "")
						$srch[] = sprintf("zf.id = %d",$value);
					break;
				case "zones":
					if ($value != "")
						$srch[] = sprintf("(z1.id = %d or z2.id = %d)",$value, $value);
					break;
				case "products":
					if (is_array($value) && count($value) > 0)
						$srch[] = sprintf("p.id in (%s)",implode(",",$value));
					else
						if ((!is_array($value)) && $value != "")
							$srch[] = sprintf("p.id = %d",$value);
					break;
			}
		}
		$_SESSION['formData']['zoneKmCaps'] = $_POST;
		$where = implode(" and ",$srch);
		$sql = sprintf("select zf.title as zoneName, z1.title as zoneFrom, z2.title as zoneTo, p.name as productName, c.custom_km_mincharge, c.custom_km_maxcharge, c.id 
from zone_to_zone_caps c, zone_to_zone zz, zones_by_folder zf1, zones_by_folder zf2, zones z1, zones z2, product p, zone_folders zf 
where zz.id = c.zone_to_zone_id and zf1.id = zz.zone_from and z1.id = zf1.zone_id and zf2.id = zz.zone_to and z2.id = zf2.zone_id and p.id = c.product_id and zf.id = zf1.folder_id 
and %s",$where);
		$count = $this->fetchAll($sql);
		$perPage = $_POST["pager"];
		$pageNum = $_POST["pagenum"];
		$pagination = $this->pagination(count($count), $perPage, $pageNum, 
			array('prev'=>$this->M_DIR.'forms/paginationPrev.html','next'=>$this->M_DIR.'forms/paginationNext.html',
			'pages'=>$this->M_DIR.'forms/paginationPage.html', 'wrapper'=>$this->M_DIR.'forms/paginationWrapper.html'),
			array('url'=>'"/modit/ajax/zoneKmCaps/zones"','destination'=>'middleContent'));
		$recs = $this->fetchAll(sprintf("%s order by %s %s limit %d,%d",$sql,$_POST["sortby"],$_POST["sortorder"],($pageNum-1)*$perPage,$perPage));
		$outer->addTag("pagination",$pagination,false);
		$inner = new Forms();
		$inner->init($this->getTemplate("kmCaps"));
		$flds = $this->getFields("kmCaps");
		$flds = $inner->buildForm($flds);
		$result = array();
		foreach($recs as $key=>$value) {
			$inner->reset();
			$inner->addData($value);
			$result[] = $inner->show();
		}
		$outer->addTag("caps",implode("",$result),false);
		$outer->addTag("heading",$this->getHeader(),false);
		if ($this->isAjax()) {
			return $this->ajaxReturn(array('status'=>true,'html'=>$outer->show()));
		}
		else return $this->show($outer->show());
	}

    /**
     * @return false|string
     * @throws phpmailerException
     */
    function getZones() {
		$g_id = array_key_exists("g_id",$_REQUEST) ? $_REQUEST["g_id"] : 0;
		if (array_key_exists("r",$_REQUEST) && $_REQUEST["r"] == 0)
			$options = $this->fetchOptions(sprintf("select '','-select a zone-' union (select zf.id, z.title from zones z, zones_by_folder zf where zf.folder_id = %d and z.id = zf.zone_id order by z.title)",$g_id));
		else
			$options = $this->fetchOptions(sprintf("select zf.id, z.title from zones z, zones_by_folder zf where zf.folder_id = %d and z.id = zf.zone_id order by z.title",$g_id));
		$s = new select();
		if (array_key_exists("name",$_REQUEST))
			$s->addAttribute('name',$_REQUEST["name"]);
		else
			$s->addAttribute('name','zone_id');
		$s->addOptions($options);
		$html = $s->show();
		return $this->ajaxReturn(array('status'=>true,'html'=>$html));
	}

    /**
     * @return false|string
     * @throws phpmailerException
     */
    function addMinMax() {
		$c_id = array_key_exists("c_id",$_REQUEST) ? $_REQUEST["c_id"] : 0;
		$outer = new Forms();
		$outer->init($this->getTemplate("addMinMax"));
		$flds = $this->getFields("addMinMax");
		if ($c_id > 0) {
			$rec = $this->fetchSingle(sprintf("select c.*, c.id as c_id, zf.title as groups, p.code as product_id, z1.title as zone_from, z2.title as zone_to 
from zone_to_zone_caps c, zone_to_zone zz, zone_folders zf, zones_by_folder zf1, zones_by_folder zf2, zones z1, zones z2, product p 
where c.id = %d and zz.id = c.zone_to_zone_id and zf1.id = zz.zone_from and z1.id = zf1.zone_id and zf2.id = zz.zone_to and z2.id = zf2.zone_id and p.id = c.product_id and zf.id = zf1.folder_id",$c_id));
			$flds = $this->getFields("addMinMax-Edit");
			$outer->addData($rec);
		}
		$flds = $outer->buildForm($flds);
		if (count($_POST) > 0 && array_key_exists("addMinMax",$_POST)) {
			$outer->addData($_POST);
			$valid = $outer->validate();
			if ($valid) {
				if ($c_id == 0) {
					$zz_id = $this->fetchScalar(sprintf("select id from zone_to_zone where (zone_from = %d and zone_to = %d) or (zone_from = %d and zone_to = %d)",$_POST["zone_from"],$_POST["zone_to"],$_POST["zone_to"],$_POST["zone_from"]));
					if ($zz_id > 0) {
						$outer->setData("zone_to_zone_id",$zz_id);
					}
					else {
						$valid = false;
						$outer->addFormError("Internal Error - could not locate the zone junction");
					}
				}
			}
			if ($valid) {
				if ($_POST["custom_km_maxcharge"] < $_POST["custom_km_mincharge"]) {
					$valid = false;
					$outer->addFormError("Maximum charge must be greater then the Minimum charge");
				}
			}
			if ($valid && $c_id == 0) {
				if ($this->fetchSingle(sprintf("select * from zone_to_zone_caps where zone_to_zone_id = %d and product_id = %d",$zz_id, $_POST["product_id"]))) {
					$valid = false;
					$outer->addFormError("This combination of zone/product has already been defined for this group");
				}
			}
			if ($valid) {
				$upd = array();
				foreach($flds as $key=>$fld) {
					if (!(array_key_exists('database',$fld) && $fld['database'] == false)) {
						$upd[$fld["name"]] = $outer->getData($fld['name']);
					}
				}
				if ($c_id == 0)
					$stmt = $this->prepare(sprintf("insert into zone_to_zone_caps(%s) values(%s?)",implode(", ",array_keys($upd)),str_repeat("?,",count($upd)-1)));
				else
					$stmt = $this->prepare(sprintf("update zone_to_zone_caps set %s=? where id = %d", implode("=?, ", array_keys($upd)),$c_id));
				$stmt->bindParams(array_merge(array(str_repeat("s", count($upd))),array_values($upd)));
				$valid = $stmt->execute();
				if ($valid) {
					if ($c_id == 0) $c_id = $this->insertId();
					$outer->setData("c_id",$c_id);
					$outer->init($this->getTemplate("addMinMaxSuccess"));
					$outer->addFormSuccess("Record updated");
				}
			}
			if (!$valid) {
				$flds["zone_from"]["sql"] = sprintf("select zf.id, z.title from zones z, zones_by_folder zf where zf.folder_id = %d and z.id = zf.zone_id order by z.title",$_POST["groups"]);
				$flds["zone_to"]["sql"] = sprintf("
select zf2.id, z2.title from zones z, zones_by_folder zf, zone_to_zone zz, zones_by_folder zf2, zones z2
where zf.id = %d and z.id = zf.zone_id and zf.folder_id = %d and zz.zone_from = zf.id
and zf2.id = zz.zone_to and z2.id = zf2.zone_id
union
select zf2.id, z2.title from zones z, zones_by_folder zf, zone_to_zone zz, zones_by_folder zf2, zones z2
where zf.id = %d and z.id = zf.zone_id and zf.folder_id = %d and zz.zone_to = zf.id
and zf2.id = zz.zone_from and z2.id = zf2.zone_id
order by 2",$_POST["zone_from"],$_POST["groups"],$_POST["zone_from"],$_POST["groups"]);
				$flds = $outer->buildForm($flds);
			}
		}
		return $this->ajaxReturn(array('status'=>true,'html'=>$outer->show()));
	}

    /**
     * @return false|string
     * @throws phpmailerException
     */
    function getZoneDest() {
		$g_id = array_key_exists("g_id",$_REQUEST) ? $_REQUEST["g_id"] : 0;
		$s_id = array_key_exists("s_id",$_REQUEST) ? $_REQUEST["s_id"] : 0;
		$options = $this->fetchOptions(sprintf("select zf2.id, z2.title from zones z, zones_by_folder zf, zone_to_zone zz, zones_by_folder zf2, zones z2
where zf.id = %d and z.id = zf.zone_id and zf.folder_id = %d and zz.zone_from = zf.id
and zf2.id = zz.zone_to and z2.id = zf2.zone_id
union
select zf2.id, z2.title from zones z, zones_by_folder zf, zone_to_zone zz, zones_by_folder zf2, zones z2
where zf.id = %d and z.id = zf.zone_id and zf.folder_id = %d and zz.zone_to = zf.id
and zf2.id = zz.zone_from and z2.id = zf2.zone_id
order by 2",$s_id,$g_id,$s_id,$g_id));
		$s = new select();
		if (array_key_exists("name",$_REQUEST))
			$s->addAttribute('name',$_REQUEST["name"]);
		else
			$s->addAttribute('name','zone_id');
		$s->addOptions($options);
		$html = $s->show();
		return $this->ajaxReturn(array('status'=>true,'html'=>$html));
	}

    /**
     * @return false|string
     * @throws phpmailerException
     */
    function cloneZone() {
		$outer = new Forms();
		$outer->init($this->getTemplate("cloneZone"));
		$flds = $this->getFields("cloneZone");
		$outer->addData($_REQUEST);
		$flds = $outer->buildForm($flds);
		if (count($_POST) > 0 && array_key_exists(__FUNCTION__,$_POST)) {
			$outer->addData($_POST);
			$valid = $outer->validate();
			if ($valid) {
				$old_zone = $_POST["g_id"];
				$mptt = new mptt($this->m_tree);
				$new_id = $mptt->add(0,999,array('title'=>$_POST["title"]));
				$this->beginTransaction();
				$zbf = $this->fetchAll(sprintf("select * from zones_by_folder where folder_id = %d",$old_zone));
				$zf_ids = array();
				$status = true;
				foreach($zbf as $key=>$z) {
					$status = $status && $this->execute(sprintf("insert into zones_by_folder(folder_id,zone_id,sequence) values(%d,%d,%d)",$new_id,$z["zone_id"],$z["sequence"]));
					$zf_ids[$z["id"]] = $this->insertId();
				}
				$zz = $this->fetchAll(sprintf("select zz.* from zone_to_zone zz, zones_by_folder zf where zf.folder_id = %d and zz.zone_from = zf.zone_id union select zz.* from zone_to_zone zz, zones_by_folder zf where zf.folder_id = %d and zz.zone_to = zf.zone_id",$old_zone,$old_zone));
				$zz_ids = array();
				foreach($zz as $key=>$zz) {
					$status = $status && $this->execute(sprintf("insert into zone_to_zone(zone_from, zone_to, cost) values(%d, %d, %d)",$zf_ids[$zz["zone_from"]], $zf_ids[$zz["zone_to"]],$zz["cost"]));
					$zz_ids[$zz["id"]] = $this->insertId();
				}
				$zc = $this->fetchAll(sprintf("select zc.* from zone_to_zone_caps zc, zone_to_zone zz, zones_by_folder zbf, zone_folders zf
where zz.id = zc.zone_to_zone_id and zbf.id = zz.zone_from and zf.id = zbf.folder_id and zf.id = %d", $old_zone));
				foreach($zc as $key=>$z) {
					$status = $status && $this->execute(sprintf("insert into zone_to_zone_caps(zone_to_zone_id,product_id,custom_km_mincharge,custom_km_maxcharge) values(%d,%d,%f,%f)",$zz_ids[$z["zone_to_zone_id"]],$z["product_id"],$z["custom_km_mincharge"],$z["custom_km_maxcharge"]));
				}
				if ($status) {
					$outer->addFormSuccess("Cloned Successfully");
					$this->commitTransaction();
				}
				else {
					$this->rollbackTransaction();
					$outer->addFormError("An Error Occurred");
				}
			}
		}
		return $this->ajaxReturn(array("status"=>true,"html"=>$outer->show()));
	}

    /**
     * @return array|mixed|string|string[]
     * @throws phpmailerException
     */
    function removeFSA() {
		$outer = new Forms();
		$outer->init($this->getTemplate("removeFSA"));
		$flds = $this->getFields("removeFSA");
		$outer->addData($_REQUEST);
		$flds = $outer->buildForm($flds);
		$z_id = (int)$outer->getData("z_id");
		$fsa_id = (int)$outer->getData("fsa_id");
		if ($this->execute(sprintf("delete from zone_fsa where zone_id = %d and fsa_id = %d", $z_id, $fsa_id))) {
			$outer->addFormSuccess("FSA has been deleted");
		}
		else {
			$outer->addFormError("Oops - something went wrong");
		}
		$rows = $this->loadFSA($outer->getAllData());
		$outer->addTag("fsa",$rows,false);
		return $outer->show();
	}

    /**
     * @return false|string
     * @throws phpmailerException
     */
    function editFSA() {
		$outer = new Forms();
		$outer->init($this->getTemplate(__FUNCTION__));
		$flds = $this->getFields(__FUNCTION__);
		$flds = $outer->buildForm($flds);
		$id = array_key_exists("fsa_id",$_REQUEST) ? $_REQUEST["fsa_id"] : 0;
		if ($data = $this->fetchSingle(sprintf("select * from fsa where id = %d", $id))) {
			$outer->addData($data);
		}
		if (array_key_exists(__FUNCTION__,$_POST)) {
			$outer->addData($_POST);
			$valid = $outer->validate();
			$outer->setData("fsa",strtoupper($outer->getData("fsa")));
			if ($valid) {
				if ($outer->getData("fsa_id") == 0) {
					if ($this->fetchSingle(sprintf("select * from fsa where fsa = '%s'", $outer->getData("fsa")))) {
						$valid = false;
						$outer->addFormError("This FSA already esists");
					}
				}
			}
			if ($valid) {
				if (!preg_match("/^([a-ceghj-npr-tv-z]){1}[0-9]{1}[a-ceghj-npr-tv-z]{1}$/i", $outer->getData("fsa"))) {
					$valid = false;
					$outer->addFormError("FSA is not a Letter/Number/Letter format");
				}
			}
			if ($valid) {
				$data = array();
				foreach($flds as $key=>$fld) {
					if (!(array_key_exists('database',$fld) && $fld['database'] == false)) {
						$values[$fld['name']] = $outer->getData($fld['name']);
					}
				}
				if ($outer->getData("fsa_id")==0) 
					$stmt = $this->prepare(sprintf("insert into fsa(%s) values(?%s)", implode(",", array_keys($values)), str_repeat(", ?", count($values)-1)));
				else
					$stmt = $this->prepare(sprintf("update fsa set %s=? where id=%d", implode("=?,", array_keys($values)), $outer->getData("id")));
				$stmt->bindParams(array_merge(array(str_repeat("s", count($values))),array_values($values)));
				$valid = $stmt->execute();
				if ($valid) {
					$outer->addFormSuccess("FSA has been updated");
					$outer->init($this->getTemplate("editFSASuccess"));
				}
			}
		}
		$zones = $this->fetchAll(sprintf("select z.*, zf.title as folder_name, zbf.id as j_id from zones z, zone_folders zf, zones_by_folder zbf, zone_fsa zfsa 
		where zfsa.fsa_id = %s and z.id = zfsa.zone_id and zbf.zone_id = z.id and zf.id = zbf.folder_id", $outer->getData("id")));
		$rows = array();
		$inner = new Forms();
		$inner->init($this->getTemplate(__FUNCTION__."Row"));
		$flds = $this->getFields(__FUNCTION__."Row");
		$flds = $inner->buildForm($flds);
		foreach($zones as $k=>$v) {
			$inner->addData($v);
			$rows[] = $inner->show();
		}
		$outer->setData("usedIn", implode("",$rows));
		return $this->ajaxReturn(array('status'=>true,'html'=>$outer->show()));
	}

    /**
     * @param bool $fromMain
     * @return array|false|mixed|string|string[]|void
     * @throws phpmailerException
     */
    function fsa($fromMain = false) {
		$outer = new Forms();
		$outer->init($this->getTemplate(__FUNCTION__."Form"));
		$flds = $this->getFields(__FUNCTION__."Form");
		$flds = $outer->buildForm($flds);

		$header = new Forms();
		$header->init($this->M_DIR.'forms/fsaHeading.html');
		$outer->addTag('heading',$header->show(),false);

		$inner = new Forms();
		$inner->init($this->getTemplate(__FUNCTION__."FormRow"));
		$flds = $this->getFields(__FUNCTION__."FormRow");
		$flds = $inner->buildForm($flds);

		$sql = sprintf("select * from fsa");
		if (array_key_exists('pagenum',$_REQUEST)) 
			$pageNum = $_REQUEST['pagenum'];
		else
			$pageNum = 1;	// no 0 based calcs
		if ($pageNum <= 0) $pageNum = 1;
		if (array_key_exists('pager',$_REQUEST)) 
			$perPage = $_REQUEST['pager'];
		else
			$perPage = $this->m_perrow;
		$outer->setData('pager',$perPage);
		$count = $this->fetchScalar(sprintf('select count(0) from fsa'));
		$pagination = $this->pagination($count, $perPage, $pageNum);
		$start = ($pageNum-1)*$perPage;
		$sortby = 'fsa';
		$sortorder = 'asc';
		if (count($_POST) > 0 && array_key_exists(__FUNCTION__."Form",$_POST)) {
			$sortby = $_POST['sortby'];
			$sortorder = $_POST['sortorder'];
			$outer->addData($_POST);
		}
		$sql = sprintf('select * from fsa order by %s %s, fsa asc limit %d,%d', $sortby, $sortorder, $start,$perPage);
		$recs = $this->fetchAll($sql);
		$rows = array();
		foreach($recs as $k=>$v) {
			$inner->addData($v);
			$rows[] = $inner->show();
		}
		$outer->setData("rows",implode("",$rows));
		$outer->setData("pagination",$pagination,false);
		$this->logMessage(__FUNCTION__,sprintf("fsa form [%s]", print_r($outer->show(),true)),1);
		if (array_key_exists("pager",$_REQUEST))
			return $this->ajaxReturn(array("status"=>true,"html"=>$outer->show()));
		else
			return $this->show($outer->show());
	}
}

?>