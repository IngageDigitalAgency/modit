<?php

class itinerary extends Frontend {

	private $m_dir = '';
	protected $module;
	public function __construct($id,$module = array()) {
		parent::__construct();
		$this->m_dir = ADMIN.'frontend/forms/itinerary/';
		$this->m_moduleId = $id;
		$this->m_module = $module;
		$this->logMessage("__construct",sprintf("($id,[%s])",print_r($module,true)),2);
	}

	private function getId() {
		if (!array_key_exists('itinerary',$_SESSION) || !array_key_exists('id',$_SESSION['itinerary'])) {
			$stmt = $this->prepare('insert into itinerary(random) values(?)');
			$rand = rand();
			$stmt->bindParams(array('s',$rand));
			$stmt->execute();
			$id = $this->insertId();
			$_SESSION['itinerary'] = array('id'=>$id,'rand'=>$rand);
		}
		return $_SESSION['itinerary']['id'];
	}

	function addItem() {
		if (!$module = parent::getModule())
			return "";
		$this->logMessage("addItem",sprintf("module [%s]",print_r($module,true)),2);
		$outer = new Forms();
		$outer->init($this->m_dir.$module['outer_html']);
		if (count($_REQUEST) > 0 && array_key_exists('addItem',$_REQUEST)) {
			$id = $this->getId();
			$ct = $this->fetchScalar(sprintf('select count(0) from itinerary_details where itinerary_id = %d and type = "%s" and item_id = %d',
				$id, $_REQUEST['i_type'], $_REQUEST['i_id']));
			if ($ct == 0) {
				$stmt = $this->prepare('insert into itinerary_details(itinerary_id,type,item_id) values(?,?,?)');
				$stmt->bindParams(array('dsd',$id,$_REQUEST['i_type'],$_REQUEST['i_id']));
				$stmt->execute();
			}
			else {
				$outer->addFormError($module['parm1']);
			}
			$ct = $this->fetchScalar(sprintf('select count(*) from itinerary_details where itinerary_id = %d',$id));
			$outer->addTag('count',$ct);
			if ($this->isAjax())
				return $this->ajaxReturn(array('status'=>'true','html'=>$outer->show()));
			else
				return $outer->show();
		}
	}

	function ajaxDelete() {
	}

	function deleteItem() {
		//if (!$module = parent::getModule()) {
			if (!$this->isAjax())
				return "";
			if (count($_POST) > 0 && array_key_exists('deleteItem',$_POST)) {
				$id = $this->getId();
				$sql = sprintf('delete from itinerary_details where itinerary_id = %d and item_id = %d and type = "%s"',$id,$_POST['i_id'],$_POST['i_type']);
				$this->logMessage('deleteItem',sprintf('delete sql [%s]',$sql),3);
				$status = $this->execute($sql);
				return $this->ajaxReturn(array('status'=>$status?'true':'false','html'=>''));
			}
		//}
	}
	
	function listItems() {
		if (!$module = parent::getModule())
			return "";
		$this->logMessage("listItems",sprintf("module [%s]",print_r($module,true)),2);
		$outer = new Forms();
		$outer->init($this->m_dir.$module['outer_html']);
		if (!(array_key_exists('itinerary',$_SESSION))) {
			$outer->addTag('events',$module['parm5'],false);
		}
		else {
			$ct = $this->fetchScalar(sprintf('select count(*) from itinerary_details where itinerary_id = %d',$_SESSION['itinerary']['id']));
			$outer->addTag('count',$ct);
			$storeList = array();
			if (strlen($module['parm1']) > 0) {
				$stores = $this->fetchAll(sprintf('select s.* from stores s, itinerary_details i where i.itinerary_id = %d and i.type = "store" and s.id = i.item_id order by i.id',$_SESSION['itinerary']['id']));
				$sClass = new stores(0);
				$sForm = new Forms();
				$sForm->init($this->m_dir.$module['parm1']);
				foreach($stores as $store) {
					$sForm->addData($sClass->formatData($store));
					$storeList[] = $sForm->show();
				}
			}
			$eventList = array();
			if (strlen($module['parm2']) > 0) {
				$events = $this->fetchAll(sprintf('select e.* from events e, itinerary_details i where i.itinerary_id = %d and type = "event" and e.id = i.item_id order by e.start_date',$_SESSION['itinerary']['id']));
				$eClass = new calendar(0);
				$eForm = new Forms();
				$eForm->init($this->m_dir.$module['parm2']);
				foreach($events as $event) {
					$eForm->addData($eClass->formatData($event));
					$eventList[] = $eForm->show();
				}
			}
			$outer->addTag('stores',implode('',$storeList),false);
			$outer->addTag('events',implode('',$eventList),false);
		}
		$tmp = $outer->show();
		$this->logMessage('listing',sprintf('return [%s]',$tmp),2);
		if ($this->isAjax())
			$this->ajaxReturn(array('status'=>'false','html'=>$tmp));
		else
			return $tmp;
	}
	
	function getModuleInfo() {
		return parent::getModuleList(array('addItem','deleteItem','listItems'));
	}

}
?>