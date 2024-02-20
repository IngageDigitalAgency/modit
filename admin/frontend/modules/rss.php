<?php

class rss extends Frontend {
	
	public function __construct($id,$module = array()) {
		parent::__construct();
		$this->m_dir = ADMIN.'frontend/forms/rss/';
		$this->m_moduleId = $id;
		$this->m_module = $module;
		$this->m_rssId = array_key_exists('rss_id',$_REQUEST) ? $_REQUEST['rss_id'] : 0;
	}

	function buildSql($module,$addLimit = false) {
		if ($module['folder_id'] > 0) {
			if ($module['include_subfolders'] != 0) {
				$sql = sprintf('select n.id from rss_folders n, rss_folders n1 where n1.id = %d and n.left_id >= n1.left_id and n.right_id <= n1.right_id and n.enabled = 1',$module['folder_id']);
				$tmp = $this->fetchScalarAll($sql);
				$this->logMessage('buildSql',sprintf('sub folder sql [%s]',$sql),3);
				$sql = sprintf("select n.*, j.sequence, f.left_id, j.folder_id from rss n, rss_by_folder j, rss_folders f where f.id = j.folder_id and deleted = 0 and n.enabled = 1 and published = 1 and (expires = '0000-00-00' or expires >= curdate()) and n.id = j.article_id and j.folder_id in (%s)",implode(',',$tmp));
			}
			else
				$sql = sprintf("select n.*, j.sequence, 0 as left_id, folder_id from rss n, rss_by_folder j where deleted = 0 and n.enabled = 1 and published = 1 and (expires = '0000-00-00' or expires >= curdate()) and j.folder_id = %d and n.id = j.article_id",$module['folder_id']);
		}
		else
			$sql = "select n.*, 0 as sequence, 0 as left_id, 0 as folder_id from rss n where deleted = 0 and n.enabled = 1 and published = 1 and (expires = '0000-00-00' or expires >= curdate())";
		if ($module['featured'])
			$sql .= " and featured = 1 ";

		if ($this->hasOption('unique')) {
			$pos = strpos($sql,'from');
			$tmp = sprintf('select min(concat(left_id,"|",n.id)) %s group by n.id',substr($sql,$pos-1));
			$test = $this->fetchScalarAll($tmp);
			$this->logMessage(__FUNCTION__,sprintf('unique code produced [%s] with [%s] pos [%s]',print_r($test,true),$tmp,$pos),2);
			$sql .= sprintf(' and concat(left_id,"|",n.id) in ("%s")',implode($test,'","'));
		}

		if (array_key_exists('search_group',$module) && $module['search_group'] > 0) {
			$sql .= sprintf(" and n.id in (select article_id from rss_by_search_group where folder_id = %d)",$module['search_group']);
		}
		if (array_key_exists('rss_list',$module) && count($module['rss_list']) > 0) {
			$sql .= sprintf(" and n.id in (%s)",implode(',',$module['rss_list']));
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

	public function formatData($data,$folder = array()) {
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
		$data['url'] = $this->getUrl('rss',$data['id'],$data);
		$data['href'] = sprintf('<a href="%s">',$data['url']);
		$data['href_end'] = '</a>';
		$data['formattedCreated'] = date(GLOBAL_DEFAULT_DATETIME_FORMAT,strtotime($data['created']));
		$data['utcCreated'] = date(DATE_ATOM,strtotime($data['created']));
		$data['created_month_short'] = date('M',strtotime($data['created']));
		$data['created_month_long'] = date('F',strtotime($data['created']));
		$data['created_day_0d'] = date('d',strtotime($data['created']));
		$data['created_day_d'] = date('j',strtotime($data['created']));
		if ($video = $this->fetchSingle(sprintf('select * from videos where owner_type="rss" and owner_id = %d', $data['id'])))
			$data['video'] = rss::formatVideo($video);
		if (property_exists('rss','m_rssId') && $data['id'] == $this->m_rssId) $data['active'] = "active";
		$this->logMessage('formatData',sprintf('return data [%s]',print_r($data,true)),4);
		return $data;
	}

	private function formatFolder($data) {
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
		$data['url'] = $this->getUrl('rsscat',$data['id'],$data);
		$this->logMessage(__FUNCTION__,sprintf('data [%s]',print_r($data,true)),4);
		return $data;
	}

	function formatVideo($data) {
		return $data;
	}

	function listing() {
		if (!$module = $this->getModule())
			return "";
		$this->parseOptions($module['misc_options']);
		$this->logMessage("listing",sprintf("module [%s]",print_r($module,true)),2);
		$sql = $this->buildSql($module);
		if ($module['folder_id'] != 0)
			$root = $this->fetchSingle(sprintf('select * from rss_folders where id = %d',$module['folder_id']));
		else
			$root = array('left_id'=>0,'right_id'=>999999,'level'=>0,'id'=>0);
		if ($this->hasOption('typeSelect')) {
			$folders = $this->fetchAll(sprintf('select * from rss_folders where left_id > %d and right_id < %d and level > %d order by left_id',$root['left_id'],$root['right_id'],$root['level']));
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

	private function buildUL($root,$module,$root_level) {
		$this->logMessage("buildUL",sprintf("root [%d] root_level [%d]",$root['id'],$root_level),1);
		$level = $this->fetchAll(sprintf('select * from rss_folders where level = %d and left_id > %d and right_id < %d and enabled = 1 order by left_id',$root['level']+1,$root['left_id'],$root['right_id']));
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

	public function items() {
		if (!$module = parent::getModule())
			return "";
		$this->parseOptions($module['misc_options']);
		$this->logMessage(__FUNCTION__,sprintf("module [%s]",print_r($module,true)),2);
		$sql = $this->buildSql($module,false);
		$pagination = $this->getPagination($sql,$module,$recordcount);
		$records = $this->fetchAll($sql);
		$this->logMessage(__FUNCTION__,sprintf("sql [%s] count [%d]",$sql,count($records)),2);
		$rss = array();
		$frm = new Forms();
		$frm->init($this->m_dir.$module['inner_html']);
		$ct = 0;
		$ids = array(0);
		$articles = array();
		if ($this->hasOption('grpPrefix')) $rss[] = $this->getOption('grpPrefix');
		foreach($records as $rec) {
			$ct += 1;
			if ($module['rows'] > 0 && $ct > $module['columns']) {
				$ct = 1;
				if ($this->hasOption('grpSuffix')) $rss[] = $this->getOption('grpSuffix');
				if ($this->hasOption('grpPrefix')) 
					$rss[] = $this->getOption('grpPrefix');
				else
					$rss[] = '<div class="clearfix"></div>';
			}
			$frm->reset();
			if ($this->m_module['folder_id'] != 0)
				$fldr = $this->fetchSingle(sprintf('select * from rss_folders where id = %d',$this->m_module['folder_id']));
			else {
				$sql = sprintf('select * from rss_folders where id in (select folder_id from rss_by_folder where article_id =%d order by rand()) limit 1',$rec['id']);
				$fldr = $this->fetchSingle($sql);
			}
			$rec["author"] = $this->fetchSingle(sprintf("select * from users where id = %d",$rec["author_id"]));
			$tmp = $this->formatData($rec,$fldr);
			$frm->addData($tmp);
			$articles[] = $tmp;
			$frm->addTag('sequence',$ct);
			$subdata = $this->subForms($this->m_module['fetemplate_id'],null,array('folder_id'=>$module['folder_id'],'rss_id'=>$rec['id']),'inner');
			$this->logMessage(__FUNCTION__,sprintf('subforms [%s]',print_r($subdata,true)),3);
			foreach($subdata as $key=>$value) {
				$frm->addTag($key,$value,false);
			}
			$rss[] = $frm->show();
			$ids[] = $rec['id'];
		}
		if ($this->hasOption('grpSuffix')) $rss[] = $this->getOption('grpSuffix');
		$outer = new Forms();
		$outer->setModule($module);
		$outer->addTag('articleCount',count($records));
		$outer->addData(array('items'=>$articles));
		if ($this->m_module['folder_id'] != 0) {
			if ($fldr = $this->fetchSingle(sprintf('select * from rss_folders where id = %d',$this->m_module['folder_id'])))
				$outer->addData($this->formatFolder($fldr));
		}
		else {
			if ($fldr = $this->fetchSingle(sprintf('select * from rss_folders where id in (select folder_id from rss_by_folder where article_id in (%s)) order by rand() limit 1',implode(',',$ids))))
				$outer->addData($this->formatFolder($fldr));
		}
		$subdata = $this->subForms($this->m_module['fetemplate_id'],null,array(),'outer');
		$this->logMessage(__FUNCTION__,sprintf('subforms [%s]',print_r($subdata,true)),3);
		foreach($subdata as $key=>$value) {
			$outer->addTag($key,$value,false);
		}
		$outer->init($this->m_dir.$module['outer_html']);
		$outer->addTag(__FUNCTION__,implode('',$rss),false);
		$outer->addTag('pagination',$pagination,false);
		$tmp = $outer->show();
		$this->logMessage(__FUNCTION__,sprintf("return [%s]",$tmp),3);
		return $tmp;
	}

	function details($articleId = null) {
		if (!$module = parent::getModule())
			return "";
		$this->parseOptions($module['misc_options']);
		$this->logMessage(__FUNCTION__,sprintf("module [%s]",print_r($module,true)),2);
		if (is_null($articleId)) $articleId = $this->m_rssId;
		$outer = new Forms();
		$outer->init($this->m_dir.$module['outer_html']);
		$sql = sprintf('select * from rss where id = %d and enabled = 1 and published = 1 and (expires = "0000-00-00" or expires > curdate())',$articleId);
		if (!$rec = $this->fetchSingle($sql)) {
			$outer->addError("Sorry, we couldn't locate that article");
			return $outer->show();
		}
		if ($this->m_module['folder_id'] != 0) {
			$fldr = $this->fetchSingle(sprintf('select * from rss_folders where id = %d',$this->m_module['folder_id']));
		}
		else {
			$fldr = $this->fetchSingle(sprintf('select * from rss_folders where id = (select folder_id from rss_by_folder where article_id = %d order by rand() limit 1)',$articleId));
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
		$outer->addTag('items',$frm->show(),false);
		$subdata = $this->subForms($this->m_module['fetemplate_id'],null,array(),'outer');
		$this->logMessage(__FUNCTION__,sprintf('subforms [%s]',print_r($subdata,true)),3);
		foreach($subdata as $key=>$value) {
			$outer->addTag($key,$value,false);
		}
		$tmp = $outer->show();
		$this->logMessage(__FUNCTION__,sprintf("return [%s]",$tmp),3);
		return $tmp;
	}

	function rssSearchForm() {
		if (!$module = parent::getModule())
			return "";
		$this->parseOptions($module['misc_options']);
		$outer = new Forms();
		$inner = new Forms();
		$outer->setModule($module);
		$outer->init($this->m_dir.$module['outer_html']);
		$inner->init($this->m_dir.$module['inner_html']);
		$flds = $inner->buildForm($this->config->getFields($module['configuration']));
		if ($module["folder_id"] > 0) {
			$fldr = $this->fetchSingle(sprintf("select * from rss_folders where id = %d", $module["folder_id"]));
			$outer->addData($this->formatFolder($fldr));
		}
		if (array_key_exists(__FUNCTION__,$_REQUEST) && $_REQUEST[__FUNCTION__] > 0) {
			$inner->addData($_REQUEST);
			$inner->validate();
		}
		$outer->addTag("form",$inner->show(),false);
		return $outer->show();
	}

	function rssSearchResults() {
		if (!$module = parent::getModule())
			return "";
		$this->parseOptions($module['misc_options']);
		$outer = new Forms();
		$inner = new Forms();
		$outer->setModule($module);
		$flds = $inner->buildForm($this->config->getFields($module['configuration']));
		$outer->init($this->m_dir.$module['outer_html']);
		$inner->init($this->m_dir.$module['inner_html']);
		$outer->addTag("form",$inner->show(),false);
		$outer->addTag("count",0);
		if ($module["folder_id"] > 0) {
			$fldr = $this->fetchSingle(sprintf("select * from rss_folders where id = %d", $module["folder_id"]));
			$outer->addData($this->formatFolder($fldr));
		}
		else $fldr = array("id"=>0, "left_id"=>0, "right_id"=>99999);
		if ($module["include_subfolders"] != 0) {
			$fldrList = $this->fetchScalarAll(sprintf("select id from rss_folders where left_id >= %d and right_id <= %d and enabled = 1", $fldr["left_id"], $fldr["right_id"]));
		}
		else {
			$fldrList = array($fldr["id"]);
		}
		if (array_key_exists(__FUNCTION__,$_REQUEST) && $_REQUEST[__FUNCTION__] > 0) {
			$searchTerms = $_REQUEST['searchText'];
			$searchPhrase = explode(' ',$searchTerms);
			$rss = array();
			for($x = 0; $x < count($searchPhrase); $x++) {
				$rss[$x] = $this->fetchAll(sprintf('select * from rss where enabled = 1 and published = 1 and (title like "%%%s%%" or teaser like "%%%s%%" or body like "%%%s%%")',
					$searchPhrase[$x],$searchPhrase[$x],$searchPhrase[$x]));
				foreach($rss[$x] as $key=>$article) {
					$t = strip_tags($article["teaser"]);
					$b = strip_tags($article["body"]);
					if (stripos($article["title"],$searchPhrase[$x]) === false && stripos($t,$searchPhrase[$x]) === false && stripos($b,$searchPhrase[$x]) === false) {
						$this->logMessage(__FUNCTION__,sprintf("dropping article [%d] after stripping", $article["id"]),1);
						unset($rss[$x][$key]);
					}
					else
						$rss[$x][$key] = $article;
				}
			}
			$weighted = array();
			for($x = 0; $x < count($searchPhrase); $x++) {
				foreach($rss[$x] as $item) {
					if (!array_key_exists($item['id'],$weighted))
						$weighted[$item['id']] = array('ct'=>0,'created'=>$item['created']);
					$weighted[$item['id']]['ct'] += 1;
				}
			}
			$sorted = array();
			foreach($weighted as $key=>$item) {
				$sorted[$item['ct']][] = array('type'=>$key,'sort'=>$item['created']);
			}
			$merged = array();
			for($x = count($searchPhrase); $x > 0; $x--) {
				if (array_key_exists($x,$sorted)) {
					usort($sorted[$x],"sortSearchItems");
					$merged = array_merge($merged,$sorted[$x]);
				}
			}
			$outer->addTag('searchCount',count($merged));
			$ids = array(0);
			foreach($merged as $key=>$item) { $ids[] = $item["type"]; }
			$sql = sprintf("select * from rss where id in (%s) and id in (select article_id from rss_by_folder f where f.folder_id in (%s)) order by instr('|%s|',concat('|',id,'|')) ", 
					implode(",",array_values($ids)), implode(",", $fldrList), implode("|",array_values($ids)));
			$pagenum = 0;
			$ct;
			$pagination = $this->getPagination($sql,$module,$ct,$pagenum);
			$this->logMessage(__FUNCTION__,sprintf('weighted [%s] sorted [%s] merged [%s]', print_r($weighted,true), print_r($sorted,true), print_r($merged,true)),1);
			$outer->addTag("pagination",$pagination,false);
			$this->logMessage(__FUNCTION__,sprintf("pagination [%s] from [%s] page [%s]", $pagination, $sql, $pagenum),1);
			$results = array();
			$recs = $this->fetchAll($sql);
			foreach($recs as $key=>$rec) {
				$inner->addData($this->formatData($rec));
				$results[] = $inner->show();
			}
			$outer->addTag("items",implode("",$results),false);
			$outer->addTag("count",count($results));
		}
		return $outer->show();
	}

	function getModuleInfo() {
		return parent::getModuleList(array('listing','items','details','rssSearchForm','rssSearchResults'));
	}

}
?>