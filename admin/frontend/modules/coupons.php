<?php

class coupons extends Frontend {

	public function __construct($id,$module = array()) {
		parent::__construct();
		$this->m_dir = ADMIN.'frontend/forms/coupons/';
		$this->m_moduleId = $id;
		$module['m_dir'] = $this->m_dir;
		$this->m_module = $module;
		$this->logMessage("__construct",sprintf("($id,[%s])",print_r($module,true)),2);
	}

	private function buildSql($module, $addLimit = true) {
		if ($module['folder_id'] > 0) {
			if ($this->hasOption('showAll'))
				$sql = sprintf("select c.* from coupons c, coupons_by_folder j where c.deleted = 0 and j.folder_id = %d and c.id = j.coupon_id",$module['folder_id']);
			else
				$sql = sprintf("select c.* from coupons c, coupons_by_folder j where c.deleted = 0 and c.enabled = 1 and c.published = 1 and j.folder_id = %d and c.id = j.coupon_id",$module['folder_id']);
		}
		else
			if ($this->hasOption('showAll'))
				$sql = "select c.* from coupons c where c.deleted = 0 ";
			else
				$sql = "select c.* from coupons c where c.deleted = 0 and c.enabled = 1 and c.published = 1";
		if (array_key_exists('coupon_list',$module)) {
				$sql .= sprintf(" and c.id in (%s) ",implode(",",$module['coupon_list']));
		}
		$sql .= sprintf(' and (start_date = "0000-00-00 00:00:00" or start_date < now()) and (end_date = "0000-00-00 00:00:00" or end_date > now())');
		if (strlen($module['where_clause']) > 0)
			$sql .= " and ".$module['where_clause'];
		if ($module['featured'])
			$sql .= " and featured = 1";
		if (strlen($module['sort_by']) > 0)
			$sql .= " order by ".$module['sort_by'];
		if ($addLimit && strlen($module['records']) > 0) {
			$tmp = explode(',',$module['records']);
			if (count($tmp) > 1)
				$total = $tmp[0]*$tmp[1];
			else
				$total = $tmp[0];
			if ($total > 0)
				$sql .= " limit ".$total;
		}
		$this->logMessage('buildSql',sprintf('sql [%s]',$sql),3);
		return $sql;
	}

	function formatFolder($data) {
		return $data;
	}

	function formatData($data) {
		for ($i = 1; $i < 2; $i++) {
			if (strlen($data['image'.$i]) > 0) {
				$tmp = new image();
				$tmp->addAttributes(array('src'=>$data['image'.$i],'alt'=>$data['name']));
				$data['img_image'.$i] = $tmp->show();
			}
		}
		return $data;
	}

	function offers() {
		if (!$module = parent::getModule())
			return "";
		$sql = $this->buildSql($module);
		$records = $this->fetchAll($sql);
		$this->logMessage("offers",sprintf("sql [%s] count [%d]",$sql,count($records)),2);
		$frm = new Forms();
		$frm->init($this->m_dir.$module['inner_html']);
		$ct = 0;
		foreach($records as $rec) {
			$frm->reset();
			$ct += 1;
			if ($module['rows'] > 0 && $ct > $module['columns']) {
				$ct = 1;
				$stores[] = '<div class="clearfix"></div>';
			}
			$frm->addTag('sequence',$ct);
			$frm->addData($this->formatData($rec));
			$stores[] = $frm->show();
		}
		$outer = new Forms();
		if ($this->m_module['folder_id'] != 0) {
			if ($fldr = $this->fetchSingle(sprintf('select * from coupon_folders where id = %d',$this->m_module['folder_id'])))
				$outer->addData($this->formatFolder($fldr));
		}
		$outer->init($this->m_dir.$module['outer_html']);
		$outer->addTag('offers',implode('',$stores),false);
		$tmp = $outer->show();
		return $tmp;
	}

	function details() {
	}

	function getModuleInfo() {
		return parent::getModuleList(array('details','offers'));
	}
}

?>
