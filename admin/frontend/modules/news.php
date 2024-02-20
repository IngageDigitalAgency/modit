<?php

/**
 * News feature for frontend
 */
class news extends Frontend {

    /**
     * @param $id
     * @param array $module
     * @throws phpmailerException
     */
    public function __construct($id, $module = array()) {
		parent::__construct();
		$this->m_dir = ADMIN.'frontend/forms/news/';
		$this->m_moduleId = $id;
		$this->m_module = $module;
		$this->m_newsId = array_key_exists('news_id',$_REQUEST) ? $_REQUEST['news_id'] : 0;
		$this->logMessage("__construct",sprintf("($id,[%s])",print_r($module,true)),2);
	}

    /**
     * @param $module
     * @param bool $addLimit
     * @return string
     * @throws phpmailerException
     */
    private function buildSql($module, $addLimit = false) {
		if ($module['folder_id'] > 0) {
			if ($module['include_subfolders'] != 0) {
				$sql = sprintf('select n.id from news_folders n, news_folders n1 where n1.id = %d and n.left_id >= n1.left_id and n.right_id <= n1.right_id and n.enabled = 1',$module['folder_id']);
				$tmp = $this->fetchScalarAllTest($sql);
				$this->logMessage('buildSql',sprintf('sub folder sql [%s]',$sql),3);
				$sql = sprintf("select n.*, j.sequence, f.left_id, j.folder_id from news n, news_by_folder j, news_folders f where f.id = j.folder_id and deleted = 0 and n.enabled = 1 and published = 1 and (expires = '0000-00-00' or expires >= curdate()) and n.id = j.article_id and j.folder_id in (%s)",implode(',',$tmp));
			}
			else
				$sql = sprintf("select n.*, j.sequence, 0 as left_id, folder_id from news n, news_by_folder j where deleted = 0 and n.enabled = 1 and published = 1 and (expires = '0000-00-00' or expires >= curdate()) and j.folder_id = %d and n.id = j.article_id",$module['folder_id']);
		}
		else
			$sql = "select n.*, 0 as sequence, 0 as left_id, 0 as folder_id from news n where deleted = 0 and n.enabled = 1 and published = 1 and (expires = '0000-00-00' or expires >= curdate())";
		if ($module['featured']) {
			$sql .= " and featured = 1 and (featured_start_date = '0000-00-00 00:00:00' or featured_start_date <= now()) and (featured_end_date = '0000-00-00 00:00:00' or featured_end_date >= now())";
		}
		if ($this->hasOption('unique')) {
			$pos = strpos($sql,'from');
			$tmp = sprintf('select min(concat(left_id,"|",n.id)) %s group by n.id',substr($sql,$pos-1));
			$test = $this->fetchScalarAllTest($tmp);
			$this->logMessage(__FUNCTION__,sprintf('unique code produced [%s] with [%s] pos [%s]',print_r($test,true),$tmp,$pos),2);
			$sql .= sprintf(' and concat(left_id,"|",n.id) in ("%s")',implode($test,'","'));
		}

		if (array_key_exists('search_group',$module) && $module['search_group'] > 0) {
			$sql .= sprintf(" and n.id in (select article_id from news_by_search_group where folder_id = %d)",$module['search_group']);
		}
		if (array_key_exists('news_list',$module) && count($module['news_list']) > 0) {
			$sql .= sprintf(" and n.id in (%s)",implode(',',$module['news_list']));
		}
		if (strlen($module['where_clause']) > 0)
			$sql .= " and ".$module['where_clause'];
		if (strlen($module['sort_by']) > 0)
			$sql .= " order by ".$module['sort_by'];
		else
			$sql .= " order by sequence";
		if ($addLimit) {
			if (strlen($module['records']) > 0) {
				$tmp = explode(',',$module['records']);
				if (count($tmp) > 1)
					$total = $tmp[0]*$tmp[1];
				else
					$total = $tmp[0];
				$sql .= " limit ".$total;
			}
		}
		return $sql;
	}

    /**
     * @param $data
     * @param array $folder
     * @return mixed
     * @throws phpmailerException
     */
    public function formatData($data, $folder = array()) {
		if (array_key_exists('image1',$data) && strlen($data['image1']) > 0) {
			$tmp = new image();
			$tmp->addAttributes(array('src'=>$data['image1'],'alt'=>$data['title']));
			$data['img_image1'] = $tmp->show();
		}
		if (array_key_exists('image2',$data) && strlen($data['image2']) > 0) {
			$tmp = new image();
			$tmp->addAttributes(array('src'=>$data['image2'],'alt'=>$data['title']));
			$data['img_image2'] = $tmp->show();
		}
		if ($this->hasOption('dateFormat')) {
			$data['created_fmt'] = date($this->getOption('dateFormat'),strtotime($data['created']));
		}
		if (is_array($folder) && array_key_exists('id',$folder) && $folder['id'] > 0) {
			if (!array_key_exists('folder_id',$data) || $data['folder_id'] == 0) $data['folder_id'] = $folder['id'];
		}
		$data['url'] = $this->getUrl('news',$data['id'],$data);
		$data['href'] = sprintf('<a href="%s">',$data['url']);
		$data['href_end'] = '</a>';
		$data['formattedCreated'] = date(GLOBAL_DEFAULT_DATETIME_FORMAT,strtotime($data['created']));
		$data['utcCreated'] = date(DATE_ATOM,strtotime($data['created']));
		$data['created_month_short'] = date('M',strtotime($data['created']));
		$data['created_month_long'] = date('F',strtotime($data['created']));
		$data['created_day_0d'] = date('d',strtotime($data['created']));
		$data['created_day_d'] = date('j',strtotime($data['created']));
		if ($video = $this->fetchSingleTest('select * from videos where owner_type="news" and owner_id = %d', $data['id']))
			$data['video'] = news::formatVideo($video);
		$data["author_info"] = $this->fetchSingleTest("select * from users where id = %d",$data["author"]);
		if (property_exists('news','m_newsId') && $data['id'] == $this->m_newsId) $data['active'] = "active";
		$this->logMessage('formatData',sprintf('return data [%s]',print_r($data,true)),4);
		return $data;
	}

    /**
     * @param $data
     * @return mixed
     * @throws phpmailerException
     */
    public function formatFolder($data) {
		if (array_key_exists("image",$data) && strlen($data['image']) > 0) {
			$tmp = new image();
			$tmp->addAttributes(array('src'=>$data['image'],'alt'=>$data['title']));
			$data['img_image'] = $tmp->show();
		}
		if (array_key_exists("rollover_image",$data) && strlen($data['rollover_image']) > 0) {
			$tmp = new image();
			$tmp->addAttributes(array('src'=>$data['rollover_image'],'alt'=>$data['title']));
			$data['img_rollover_image'] = $tmp->show();
		}
		$data['url'] = $this->getUrl('newscat',$data['id'],$data);
		if (array_key_exists('newscat',$_REQUEST) && $data['id'] == $_REQUEST['newscat']) $data['active'] = 'active';
		$this->logMessage(__FUNCTION__,sprintf('data [%s]',print_r($data,true)),4);
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
    public function articles() {
		if (!$module = parent::getModule())
			return "";
		$this->parseOptions($module['misc_options']);
		$this->logMessage("articles",sprintf("module [%s]",print_r($module,true)),2);
		$sql = $this->buildSql($module,false);
		$pagination = $this->getPagination($sql,$module,$recordcount);
		$records = $this->fetchAllTest($sql);
		$this->logMessage("articles",sprintf("sql [%s] count [%d]",$sql,count($records)),2);
		$news = array();
		$frm = new Forms();
		$frm->init($this->m_dir.$module['inner_html']);
		$ct = 0;
		$ids = array(0);
		$articles = array();
		if ($this->hasOption('grpPrefix')) $news[] = $this->getOption('grpPrefix');
		$sequence = 0;
		foreach($records as $rec) {
			$ct += 1;
			if ($module['rows'] > 0 && $ct > $module['columns']) {
				$ct = 1;
				if ($this->hasOption('grpSuffix')) $news[] = $this->getOption('grpSuffix');
				if ($this->hasOption('grpPrefix')) 
					$news[] = $this->getOption('grpPrefix');
				else
					$news[] = '<div class="clearfix"></div>';
			}
			$frm->reset();
			if ($this->m_module['folder_id'] != 0)
				$fldr = $this->fetchSingleTest('select * from news_folders where id = %d',$this->m_module['folder_id']);
			else {
				$sql = sprintf('select * from news_folders where id in (select folder_id from news_by_folder where article_id =%d order by rand()) limit 1',$rec['id']);
				$fldr = $this->fetchSingleTest($sql);
			}
			$frm->addTag("first",$sequence == 0 ? "first":"");
			$frm->addTag("last",$sequence == (count($records) - 1) ? "last":"");
			$frm->setData("seq",$ct);
			$sequence += 1;
			$tmp = $this->formatData($rec,$fldr);
			$frm->addData($tmp);
			$articles[] = $tmp;
			$frm->addTag('sequence',$sequence);
			$subdata = $this->subForms($this->m_module['fetemplate_id'],null,array('folder_id'=>$module['folder_id'],'news_id'=>$rec['id']),'inner');
			$this->logMessage('articles',sprintf('subforms [%s]',print_r($subdata,true)),3);
			foreach($subdata as $key=>$value) {
				$frm->addTag($key,$value,false);
			}
			$news[] = $frm->show();
			$ids[] = $rec['id'];
		}
		if ($this->hasOption('grpSuffix')) $news[] = $this->getOption('grpSuffix');
		$outer = new Forms();
		$outer->setModule($module);
		$outer->addTag('articleCount',count($records));
		$outer->addData(array('newsletter'=>$articles));
		if ($this->m_module['folder_id'] != 0) {
			if ($fldr = $this->fetchSingleTest('select * from news_folders where id = %d',$this->m_module['folder_id']))
				$outer->addData($this->formatFolder($fldr));
		}
		else {
			if ($fldr = $this->fetchSingleTest('select * from news_folders where id in (select folder_id from news_by_folder where article_id in (%s)) order by rand() limit 1',implode(',',$ids)))
				$outer->addData($this->formatFolder($fldr));
		}
		$subdata = $this->subForms($this->m_module['fetemplate_id'],null,array(),'outer');
		$this->logMessage('articles',sprintf('subforms [%s]',print_r($subdata,true)),3);
		foreach($subdata as $key=>$value) {
			$outer->addTag($key,$value,false);
		}
		$outer->init($this->m_dir.$module['outer_html']);
		$outer->addTag('articles',implode('',$news),false);
		$outer->addTag('pagination',$pagination,false);
		$tmp = $outer->show();
		$this->logMessage("articles",sprintf("return [%s]",$tmp),3);
		return $tmp;
	}

    /**
     * @param $articleId
     * @return array|mixed|string|string[]
     * @throws phpmailerException
     */
    function single($articleId = null) {
		if (!$module = parent::getModule())
			return "";
		$this->parseOptions($module['misc_options']);
		$this->logMessage("articles",sprintf("module [%s]",print_r($module,true)),2);
		if (is_null($articleId)) $articleId = $this->m_newsId;
		$outer = new Forms();
		$outer->init($this->m_dir.$module['outer_html']);
		$sql = sprintf('select * from news where id = %d and enabled = 1 and published = 1 and (expires = "0000-00-00" or expires > curdate())',$articleId);
		if (!$rec = $this->fetchSingleTest($sql)) {
			$outer->addError("Sorry, we couldn't locate that article");
			return $outer->show();
		}
		if ($this->m_module['folder_id'] != 0) {
			$fldr = $this->fetchSingleTest('select * from news_folders where id = %d',$this->m_module['folder_id']);
		}
		else {
			$fldr = $this->fetchSingleTest('select * from news_folders where id = (select folder_id from news_by_folder where article_id = %d order by rand() limit 1)',$articleId);
		}
		$frm = new Forms();
		$frm->init($this->m_dir.$module['inner_html']);
		$frm->addData($this->formatData($rec,$fldr));
		$subdata = $this->subForms($this->m_module['fetemplate_id'],null,array(),'inner');
		foreach($subdata as $key=>$value) {
			$frm->addTag($key,$value,false);
		}
		if (is_array($fldr))
			$outer->addData($this->formatFolder($fldr));
		$outer->init($this->m_dir.$module['outer_html']);
		$outer->addTag('articles',$frm->show(),false);
		$subdata = $this->subForms($this->m_module['fetemplate_id'],null,array(),'outer');
		$this->logMessage('single',sprintf('subforms [%s]',print_r($subdata,true)),3);
		foreach($subdata as $key=>$value) {
			$outer->addTag($key,$value,false);
		}
		$tmp = $outer->show();
		$this->logMessage("single",sprintf("return [%s]",$tmp),3);
		return $tmp;
	}

    /**
     * @return array|mixed|string|string[]
     * @throws phpmailerException
     */
    function collapsible() {
		if (!$module = parent::getModule())
			return "";
		$this->parseOptions($module['misc_options']);
		$year_ct = $module['parm1'];
		$toggler = $module['parm2'];
		if ($module['folder_id'] != 0) {
			if ($module['include_subfolders']) {
				$root = $this->fetchSingleTest('select * from news_folders where id = %d',$module['folder_id']);
				$folderList = sprintf('and n.id in (select f.article_id from news_by_folder f, news_folders n where n.left_id >= %d and n.right_id <= %d and n.enabled = true and f.folder_id = n.id)',$root['left_id'],$root['right_id']);
			}
			else
				$folderList = sprintf('and n.id in (select f.article_id from news_by_folder f where f.folder_id = %d)',$module['folder_id']);
		}
		else $folderList = '';
		$sql = sprintf('
select tmp.year,count(tmp.id) as ct from (
	select distinct date_format(created,"%%Y") as year,n.id
	from news n
	where date_format(created,"%%Y") >= %d
	and n.enabled = true
	and n.published = true
	and n.deleted = false
	and (n.expires = "0000-00-00" or n.expires >= curdate())
	%s) as tmp
group by year order by year desc',date('Y')-$year_ct,$folderList);
		$years = $this->fetchAllTest($sql);
		$this->logMessage('collapsible',sprintf('years sql [%s] data [%s]',$sql,print_r($years,true)),3);
		$list = array();
		$frm = new Forms();
		$frm->init($this->m_dir.$module['inner_html']);
		foreach($years as $key=>$year) {
			$sql = sprintf('
select tmp.m_num,tmp.mth,count(tmp.id) as ct from (
	select distinct date_format(created,"%%m") as m_num, date_format(created,"%%b") as mth,n.id
	from news n
	where date_format(created,"%%Y") = %d
	and n.enabled = true
	and n.published = true
	and n.deleted = false
	and (n.expires = "0000-00-00" or n.expires >= curdate())
	%s) as tmp
group by m_num order by m_num desc',$year['year'],$folderList);
			$months = $this->fetchAllTest($sql);
			$this->logMessage('collapsible',sprintf('month sql [%s] count [%d]',$sql,count($months)),2);
			$mthList = array();
			foreach($months as $key=>$month) {
				$sql = sprintf('
select distinct n.*
from news n
where date_format(created,"%%Y-%%m") = "%s-%s"
and n.enabled = true
and n.published = true
and n.deleted = false
and (n.expires = "0000-00-00" or n.expires >= curdate())
%s
order by n.created desc', $year['year'], $month['m_num'], $folderList);
				$records = $this->fetchAllTest($sql);
				$this->logMessage('collapsible',sprintf('month sql [%s] count [%d]',$sql,count($records)),2);

				$articles = array();
				foreach($records as $rec) {
					$frm->reset();
					if ($module['folder_id'] > 0) $rec['folder_id'] = $module['folder_id'];
					$frm->addData($this->formatData($rec));
					$articles[] = $frm->show();
				}
				$mthList[] = sprintf('<li class="month collapsed">%s<span class="month">%s (%d)</span><span class="value">%d</span> <ul class="article">%s</ul></li>',$toggler,$month['mth'],$month['ct'],$month['m_num'],implode('',$articles));
			}

			$list[] = sprintf('<li class="year collapsed">%s<span class="year">%d (%d)</span><span class="value">%d</span><ul class="year">%s</ul></li>',$toggler,$year['year'],$year['ct'],$year['year'],implode('',$mthList));
		}
		$outer = new Forms();
		if ($this->m_module['folder_id'] != 0) {
			if ($fldr = $this->fetchSingleTest('select * from news_folders where id = %d',$this->m_module['folder_id']))
				$outer->addData($this->formatFolder($fldr));
		}
		$outer->init($this->m_dir.$module['outer_html']);
		$outer->addTag('collapsible','<ul class="listing">'.implode('',$list).'</ul>',false);
		$tmp = $outer->show();
		$this->logMessage("collapsible",sprintf("return [%s]",$tmp),3);
		return $tmp;
	}

    /**
     * @return array|mixed|string|string[]
     * @throws phpmailerException
     */
    function listing() {
		if (!$module = $this->getModule())
			return "";
		$this->parseOptions($module['misc_options']);
		$this->logMessage("listing",sprintf("module [%s]",print_r($module,true)),2);
		$sql = $this->buildSql($module);
		if ($module['folder_id'] != 0)
			$root = $this->fetchSingleTest('select * from news_folders where id = %d',$module['folder_id']);
		else
			$root = array('left_id'=>0,'right_id'=>999999,'level'=>0,'id'=>0);
		if ($this->hasOption('typeSelect')) {
			$folders = $this->fetchAllTest('select * from news_folders where left_id > %d and right_id < %d and level > %d order by left_id',$root['left_id'],$root['right_id'],$root['level']);
			$form = new Forms();
			$form->init($this->m_dir.$module['inner_html']);
			$result = array();
			foreach($folders as $key=>$folder) {
				$form->addData($this->formatFolder($folder));
				$form->addTag('selected',array_key_exists('pf_id',$_REQUEST) && $folder['id'] == $_REQUEST['pf_id'] ? 'selected':'');
				$result[] = $form->show();
			}
			$menu = implode("",$result);
		}
		else {
			$menu = sprintf('<ul class="level_0 %s">%s</ul>',$this->getOption("ul_class"),$this->buildUL($root,$module,0));
		}
		$outer = new Forms();
		$outer->init($this->m_dir.$module['outer_html']);
		$outer->addTag('listing',$menu,false);
		$outer->addData($this->formatFolder($root));
		$subdata = $this->subForms($this->m_module['fetemplate_id'],null,array('folder_id'=>$module['folder_id']),'outer');
		$this->logMessage(__FUNCTION__,sprintf('subforms [%s]',print_r($subdata,true)),3);
		foreach($subdata as $key=>$value) {
			$outer->addTag($key,$value,false);
		}
		$tmp = $outer->show();
		return $tmp;
	}

    /**
     * @param $root
     * @param $module
     * @param $root_level
     * @return string
     * @throws phpmailerException
     */
    private function buildUL($root, $module, $root_level) {
		$this->logMessage("buildUL",sprintf("root [%d] root_level [%d]",$root['id'],$root_level),1);
		if ($this->hasOption('maxLevel') && $root_level <= $this->getOption('maxLevel') - 1) {
			$this->logMessage(__FUNCTION__,'skipping folders as per config',1);
			return "";
		}
		$level = $this->fetchAllTest('select * from news_folders where level = %d and left_id > %d and right_id < %d and enabled = 1 order by left_id',$root['level']+1,$root['left_id'],$root['right_id']);
		$form = new Forms();
		$form->init($this->m_dir.$module['inner_html']);
		$menu = array();
		$seq = 0;
		foreach($level as $key=>$item) {
			$seq += 1;
			$form->addData($this->formatFolder($item));
			$form->addTag('sequence',$seq);
			$form->addTag('level',$root_level+1);
			$subdata = $this->subForms($this->m_module['fetemplate_id'],null,array('folder_id'=>$item['id']),'inner');
			$this->logMessage('buildUL',sprintf('subforms [%s]',print_r($subdata,true)),3);
			foreach($subdata as $key=>$value) {
				$form->addTag($key,$value,false);
			}
			$tmp = $form->show();
			if (($subMenu = $this->buildUL($item,$module,$root_level+1)) != '')
				$tmp .= sprintf('<ul class="level_%d submenu">%s</ul>',$root_level+1,$subMenu);
			if ($this->hasOption('delim') && $seq < count($level)) $tmp .= sprintf('<span class="delim">%s</span>',$this->getOption('delim'));
			if (strlen($module['parm1']) > 0) {
				$li = new Forms();
				$li->init($this->m_dir.$module['parm1']);
				$li->addTag('sequence',$seq);
				$li->addTag('menu',$tmp,false);
				$li->addData($this->formatFolder($item));
				$menu[] = $li->show();
			}
			else
				$menu[] = sprintf('<li class="sequence_%d">%s</li>',$seq,$tmp);
		}
		$this->logMessage("buildUL",sprintf("return [%s]",print_r($menu,true)),3);
		return implode("",$menu);
	}

    /**
     * @return array|mixed|string|string[]
     * @throws phpmailerException
     */
    function itemRelations() {
		if (!$module = $this->getModule())
			return "";
		$this->logMessage("itemRelations",sprintf("module [%s]",print_r($module,true)),2);
		if (!$this->hasOption('templateId')) {
			$this->logMessage('itemRelations',sprintf('attempt to implement cross class functionality with no config [%s] this [%s] request [%s]',print_r($module,true),print_r($this,true),print_r($_REQUEST,true)),1,true);
			return "";
		}
		$fn = $this->fetchSingleTest('select t.id as fetemplate_id, t.module_function, m.classname from fetemplates t, modules m where t.id = %d and m.id = t.module_id',$this->getOption('templateId'));
		$tmp = $this->m_module;
		$tmp['classname'] = $fn['classname'];
		$tmp['fetemplate_id'] = $fn['fetemplate_id'];
		$tmp['module_function'] = $fn['module_function'];
		if ($this->m_newsId != 0)
			$fn['news_id'] = $this->m_newsId;
		else
			if (!array_key_exists('news_id',$module) || $module['news_id'] == 0) {
				$this->logMessage(__FUNCTION__,sprintf("no current article"),1,true,true);
				return "";
			}
			else
				$fn['news_id'] = $module['news_id'];
		switch($fn['classname']) {
			case 'calendar':
				$module['id'] = $this->getOption('templateId');
				$this->logMessage(__FUNCTION__,"ready to call calendar",1);
				$html = $this->calendar($fn);
				break;
			case 'product':
				$module['id'] = $this->getOption('templateId');
				$html = $this->product($fn);
				break;
			default:
				$html = '';
		}
		$outer = new Forms();
		$outer->init($this->m_dir.$module['outer_html']);
		$flds = $this->config->getFields($module['configuration']);
		$outer->buildForm($flds);
		$outer->addTag('html',$html,false);
		return $outer->show();
	}

    /**
     * @param $module
     * @return string|void
     * @throws phpmailerException
     */
    function product($module) {
		$this->logMessage('product',sprintf('parms [%s]',print_r($module,true)),2);
		if ($this->m_newsId == 0 && array_key_exists('news_id',$module)) $this->m_newsId = $module['news_id'];
		if ($folders = $this->fetchSingleTest('select * from relations where owner_type = "news" and owner_id = %d and related_type = "productfolder"',$this->m_newsId)) {
			$module['folder_id'] = $folders['related_id'];
		}
		else {
			$folders = array();
			$module['folder_id'] = 0;
			$this->logMessage('product',sprintf('no product folder for article %d',$this->m_newsId),1);
		}
		if ($items = $this->fetchScalarAllTest('select related_id as product_id from relations where owner_type = "news" and owner_id = %d and related_type = "product"',$this->m_newsId))
			$module['product_list'] = $items;
		else {
			$items = array();
			$this->logMessage('product',sprintf('no products for article %d',$this->m_newsId),2);
		}
		if (count($folders) == 0 && count($items) == 0) {
			$this->logMessage('product',sprintf('bail - no products or folders for article %d',$this->m_newsId),1);
			return "";
		}
		$obj = new product($module['fetemplate_id'],$module);
		if (method_exists('product',$module['module_function'])) {
			$this->logMessage('product',sprintf('invoking products with [%s]',print_r($module,true)),2);
			$html = $obj->{$module['module_function']}();
			return $html;
		}
		else {
			$this->logMessage('product',sprintf('bail - no function [%s] in product for zarticle %d',$module['module_function'],$this->m_newsId),1,true);
		}
	}

    /**
     * @param $module
     * @return string|void
     * @throws phpmailerException
     */
    function calendar($module) {
		$this->logMessage(__FUNCTION__,sprintf('parms [%s]',print_r($module,true)),2);
		if ($this->m_newsId == 0 && array_key_exists('news_id',$module)) $this->m_newsId = $module['news_id'];
		if ($folders = $this->fetchSingleTest('select * from relations where owner_type = "news" and owner_id = %d and related_type = "eventfolder"',$this->m_newsId)) {
			$module['folder_id'] = $folders['related_id'];
		}
		else {
			$folders = array();
			$module['folder_id'] = 0;
			$this->logMessage(__FUNCTION__,sprintf('no calendar folder for article %d',$this->m_newsId),1);
		}
		if ($items = $this->fetchScalarAllTest('select related_id as product_id from relations where owner_type = "news" and owner_id = %d and related_type = "event"',$this->m_newsId))
			$module['event_list'] = $items;
		else {
			$items = array();
			$this->logMessage(__FUNCTION__,sprintf('no events for article %d',$this->m_newsId),2);
		}
		if (count($folders) == 0 && count($items) == 0) {
			$this->logMessage(__FUNCTION__,sprintf('bail - no events or folders for article %d',$this->m_newsId),1);
			return "";
		}
		$obj = new calendar($module['fetemplate_id'],$module);
		if (method_exists('calendar',$module['module_function'])) {
			$this->logMessage(__FUNCTION__,sprintf('invoking calendar with [%s]',print_r($module,true)),2);
			$html = $obj->{$module['module_function']}();
			return $html;
		}
		else {
			$this->logMessage(__FUNCTION__,sprintf('bail - no function [%s] in calendar for zarticle %d',$module['module_function'],$this->m_newsId),1,true);
		}
	}

    /**
     * @return array|mixed|string|string[]
     * @throws phpmailerException
     */
    public function div_listing() {
		if (!$module = parent::getModule())
			return "";
		$this->parseOptions($module['misc_options']);
		if ($module['folder_id'] != 0)
			$root = $this->fetchSingleTest('select * from news_folders where id = %d',$module['folder_id']);
		else
			$root = array('left_id'=>0,'right_id'=>999999,'level'=>0,'id'=>0,'title'=>'');
		$menu = $this->buildDiv($root,$module,0);
		$outer = new Forms();
		$outer->init($this->m_dir.$module['outer_html']);
		$subdata = $this->subForms($module['fetemplate_id'],'',array(),'outer');
		$this->logMessage(__FUNCTION__,sprintf("subdata [%s]",print_r($subdata,true)),3);
		foreach($subdata as $key=>$value) {
			$outer->addTag($key,$value,false);
		}
		$outer->addTag('listing',$menu,false);
		if ($root)
			$outer->addData($this->formatFolder($root,$module));
		return $outer->show();
	}

    /**
     * @param $root
     * @param $module
     * @param $root_level
     * @return string
     * @throws phpmailerException
     */
    private function buildDiv($root, $module, $root_level) {
		$this->logMessage(__FUNCTION__,sprintf("root [%d] root_level [%d]",$root['id'],$root_level),2);
		if ($this->hasOption('maxLevel') && $root_level >= $this->getOption('maxLevel')) {
			$this->logMessage(__FUNCTION__,sprintf('max level exceeded',$this->getOption('maxLevel')),2);
			return "";
		}
		if ($this->hasOption('maxLevel') && $root_level <= $this->getOption('maxLevel') - 1 && !$this->hasOption('showFolders')) {
			$this->logMessage(__FUNCTION__,'skipping folders as per config',1);
			$level = $this->fetchAllTest('select * from content where level = %d and left_id > %d and right_id < %d and enabled = 1 order by left_id',$root['level']+1,$root['left_id'],$root['right_id']);
		}
		else
			$level = $this->fetchAllTest('select * from news_folders where level = %d and left_id > %d and right_id < %d and enabled = 1 order by left_id',$root['level']+1,$root['left_id'],$root['right_id']);
		$form = new Forms();
		$form->init($this->m_dir.$module['inner_html']);
		$menu = array();
		$seq = 0;
		foreach($level as $key=>$item) {
			if ($seq == 0) $form->addTag("first","first");
			if ($seq == (count($level) - 1)) $form->addTag("last","last");
			$seq += 1;
			$form->addData($this->formatFolder($item));
			$form->addTag('sequence',$seq);
			$form->addTag('level',$root_level+1);

			$subdata = $this->subForms($module['fetemplate_id'],'',array('folder_id'=>$item["id"]),'inner');
			foreach($subdata as $key=>$value) {
				$form->addTag($key,$value,false);
			}

			$tmp = $form->show();
			if (($subMenu = $this->buildDiv($item,$module,$root_level+1)) != '') {
				$tmp .= sprintf('%s',$subMenu);
			}
			if ($this->hasOption('delim') && $seq < count($level)) $tmp .= sprintf('<span class="delim">%s</span>',$this->getOption('delim'));
			$menu[] = sprintf('%s',$tmp);
		}
		$this->logMessage("buildDiv",sprintf("return [%s]",print_r($menu,true)),4);
		return implode("",$menu);
	}

    /**
     * @return false|string
     * @throws phpmailerException
     */
    function getModuleInfo() {
		return parent::getModuleList(array('articles','single','collapsible','listing','itemRelations','div_listing'));
	}

}

?>
