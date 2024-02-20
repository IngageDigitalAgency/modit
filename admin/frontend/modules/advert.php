<?php

/**
 * Advertisement class for frontend
 */
class advert extends Frontend {

	private $m_dir = '';
	protected $module;

    /**
     * @param $id
     * @param $module
     * @throws phpmailerException
     */
    public function __construct($id, $module = array()) {
		parent::__construct();
		$this->m_dir = ADMIN.'frontend/forms/advert/';
		$this->m_moduleId = $id;
		$this->m_module = $module;
		$this->m_adId = 0;
		$this->logMessage("__construct",sprintf("($id,[%s])",print_r($module,true)),2);
	}

    /**
     * @param $module
     * @return string
     * @throws phpmailerException
     */
    private function buildSql($module) {
		$this->logMessage("buildSql",sprintf("module [%s]",print_r($module,true)),2);
		if ($module['folder_id'] > 0) {
			$sql = sprintf("select a.*, j.sequence, 0 as left_id, 0 as folder_id from advert a, advert_by_folder j where a.deleted = 0 and a.enabled = 1 and a.published = 1 and j.folder_id = %d and a.id = j.advert_id",$module['folder_id']);
		}
		else {
			$sql = "select a.*, 0 as sequence, 0 as left_id, 0 as folder_id from advert a where a.deleted = 0 and a.enabled = 1 and a.published = 1";
		}
		if ($module['featured']) {
			$sql .= ' and featured = 1 and (featured_start_date = "0000-00-00 00:00:00" or featured_start_date <= now()) and (featured_end_date = "0000-00-00 00:00:00" or featured_end_date >= now())';
		}
		$sql .= sprintf(" and (start_date <= '0000-00-00' or start_date < '%s') and (end_date = '0000-00-00' or end_date >= '%s') ",date("Y-m-d"),date("Y-m-d"));
		if (array_key_exists('search_group',$module) && $module['search_group'] > 0) {
			$sql .= sprintf(" and a.id in (select advert_id from advert_by_search_group where folder_id = %d)",$module['search_group']);
		}
		if (strlen($module['where_clause']) > 0)
			$sql .= " and ".$module['where_clause'];
		if (strlen($module['sort_by']) > 0)
			$sql .= " order by ".$module['sort_by'];
		else
			$sql .= " order by left_id, sequence";
		if (strlen($module['records']) > 0) {
			$tmp = explode(',',$module['records']);
			if (count($tmp) > 1)
				$total = $tmp[0]*$tmp[1];
			else
				$total = $tmp[0];
			$sql .= " limit ".$total;
		}
		return $sql;
	}

    /**
     * @param $data
     * @return mixed
     * @throws phpmailerException
     */
    private function formatData($data) {
		$tmp = new image();
		$tmp->addAttributes(array('src'=>$data['image1'],'alt'=>htmlentities(strip_tags(strlen($data['teaser']) > 0 ? $data['teaser'] : $data['title']))));
		$data['img_image1'] = $tmp->show();
		$tmp->addAttributes(array('src'=>$data['image2'],'alt'=>htmlentities(strip_tags(strlen($data['teaser']) > 0 ? $data['teaser'] : $data['title']))));
		$data['img_image2'] = $tmp->show();
		if (strlen($data['url']) != 0) {
			$data['raw_url'] = $data['url'];
			$data['url'] = $this->getUrl('advert',$data['id'],$data);
			$data['href'] = sprintf('<a href="%s" %s>',$data['url'],$data['new_window'] ? 'target="_blank"':'');
			$data['href_end'] = '</a>';
			if ($data['new_window']) $data['target'] = 'target="_blank"';
		}
		if (defined('FRONTEND'))
			$this->execute(sprintf('update advert set views = views+1 where id = %d',$data['id']));
		if ($video = $this->fetchSingleTest('select * from videos where owner_type="advert" and owner_id = %d', $data['id']))
			$data['video'] = advert::formatVideo($video);
		$this->logMessage('formatData',sprintf('return [%s]',print_r($data,true)),4);
		return $data;
	}

    /**
     * @param $data
     * @param array $module
     * @return mixed
     * @throws phpmailerException
     */
    private function formatFolder($data, $module = array()) {
		if (strlen($data['image']) > 0) {
			$tmp = new image();
			$tmp->addAttributes(array('src'=>$data['image'],'alt'=>htmlentities(strip_tags($data['title']))));
			$data['img_image'] = $tmp->show();
		}
		if (strlen($data['rollover_image']) > 0) {
			$tmp = new image();
			$tmp->addAttributes(array('src'=>$data['rollover_image'],'alt'=>htmlentities(strip_tags($data['title']))));
			$data['img_rollover_image'] = $tmp->show();
		}
		return $data;
	}

    /**
     * @param $data
     * @return mixed
     */
    function formatVideo($data) {
		return $data;
	}

    /**
     * @return array|mixed|string|string[]
     * @throws phpmailerException
     */
    public function show_ads() {
		if (!$module = parent::getModule())
			return "";
		$this->logMessage("show_ads",sprintf("module [%s]",print_r($module,true)),2);
		$sql = $this->buildSql($module);
		$records = $this->fetchAllTest($sql);
		$this->logMessage("show_ads",sprintf("sql [%s] count [%d]",$sql,count($records)),2);
		$ads = array();
		$frm = new Forms();
		$frm->init($this->m_dir.$module['inner_html']);
		$ct = 0;
		if ($this->hasOption('grpPrefix')) $ads[] = $this->getOption('grpPrefix');
		foreach($records as $rec) {
			$ct += 1;
			if ($module['rows'] > 0 && $ct > $module['columns']) {
				$ct = 1;
				if ($this->hasOption('grpSuffix')) $ads[] = $this->getOption('grpSuffix');
				if ($this->hasOption('grpPrefix')) 
					$ads[] = $this->getOption('grpPrefix');
				else
					$ads[] = '<div class="clearfix"></div>';
			}
			$frm->reset();
			$prod_folders = $this->fetchAllTest('select * from product_folders where id in (select related_id from relations where owner_type="ad" and related_type = "productfolder" and owner_id = %d)',$rec['id']);
			foreach($prod_folders as $fldr) {
				$tmp = product::formatFolder($fldr);
				$tmp['prodcat_url'] = $tmp['url'];
				unset($tmp['url']);
				$frm->addData($tmp);
			}
			$subdata = $this->subForms($this->m_module['fetemplate_id'],null,array('folder_id'=>$module['folder_id'],'ad_id'=>$rec['id']),'inner');
			$frm->addTag('seq',$ct);
			$this->m_adId = $rec['id'];
			$this->logMessage('articles',sprintf('subforms [%s]',print_r($subdata,true)),3);
			foreach($subdata as $key=>$value) {
				$frm->addTag($key,$value,false);
			}
			$frm->addData($this->formatData($rec));
			$subdata = $this->subForms($this->m_module['fetemplate_id'],null,array('folder_id'=>$module['folder_id']),'outer');
			$this->logMessage(__FUNCTION__,sprintf('subforms [%s]',print_r($subdata,true)),3);
			foreach($subdata as $key=>$value) {
				$frm->addTag($key,$value,false);
			}
			$ads[] = $frm->show();
		}
		if ($this->hasOption('grpSuffix')) $ads[] = $this->getOption('grpSuffix');
		$outer = new Forms();
		$outer->init($this->m_dir.$module['outer_html']);
		$outer->addTag('ads',implode('',$ads),false);
		if ($module['folder_id'] > 0 && $folder = $this->fetchSingleTest('select * from advert_folders where id = %d',$module['folder_id'])) {
			$outer->addData($this->formatFolder($folder,$module));
		}
		$subdata = $this->subForms($this->m_module['fetemplate_id'],null,array('folder_id'=>$module['folder_id']),'outer');
		$this->logMessage(__FUNCTION__,sprintf('subforms [%s]',print_r($subdata,true)),3);
		foreach($subdata as $key=>$value) {
			$outer->addTag($key,$value,false);
		}
		return $outer->show();
	}

    /**
     * @return string|void
     * @throws phpmailerException
     */
    function itemRelations() {
		if (!$module = $this->getModule())
			return "";
		$this->logMessage(__FUNCTION__,sprintf("module [%s]",print_r($module,true)),2);
		if (!$this->hasOption('templateId')) {
			$this->logMessage(__FUNCTION__,sprintf('attempt to implement cross class functionality with no config [%s] this [%s] request [%s]',print_r($module,true),print_r($this,true),print_r($_REQUEST,true)),1,true);
			return "";
		}
		$fn = $this->fetchSingleTest('select t.id as fetemplate_id, t.module_function, m.classname from fetemplates t, modules m where t.id = %d and m.id = t.module_id',$this->getOption('templateId'));
		$tmp = $this->m_module;
		$tmp['classname'] = $fn['classname'];
		$tmp['fetemplate_id'] = $fn['fetemplate_id'];
		$tmp['module_function'] = $fn['module_function'];
		switch($fn['classname']) {
			case 'product':
				$module['id'] = $this->getOption('templateId');
				$html = $this->product($fn,$module);
				break;
			default:
				$html = '';
		}
		return $html;
	}

    /**
     * @param $module
     * @return void
     * @throws phpmailerException
     */
    function product($module) {
		$this->logMessage(__FUNCTION__,sprintf('parms module [%s] this->module [%s]',print_r($module,true),print_r($this->m_module,true)),2);
		$ad = 0;
		if ($this->m_adId > 0)
			$ad = $this->m_adId;
		if (array_key_exists('ad_id',$this->m_module) && $this->m_module['ad_id'] > 0)
			$ad = $this->m_module['ad_id'];
		if ($products = $this->fetchScalarAllTest('select related_id from relations where owner_type = "ad" and owner_id = %d and related_type = "product"',$ad)) {
			$module['product_list'] = $products;
			$module['folder_id'] = 0;
			$obj = new product($module['fetemplate_id'],$module);
			if (method_exists('product',$module['module_function'])) {
				$this->logMessage(__FUNCTION__,sprintf('invoking class with [%s]',print_r($module,true)),2);
				$html = $obj->{$module['module_function']}();
				return $html;
			}
			else {
				$this->logMessage(__FUNCTION__,sprintf('bail - no function [%s] in class for event %d',$module['module_function'],$this->m_eventId),1,true);
			}
		}
	}

    /**
     * @return false|string
     * @throws phpmailerException
     */
    function getModuleInfo() {
		return parent::getModuleList(array('show_ads','itemRelations'));
	}
}

?>