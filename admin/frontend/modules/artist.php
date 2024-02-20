<?php

class artist extends Frontend {

	public function __construct($id,$module = array()) {
		parent::__construct();
		$this->m_dir = ADMIN.'frontend/forms/artist/';
		$this->m_moduleId = $id;
		$this->m_module = $module;
		$this->logMessage(__FUNCTION__,sprintf("($id,[%s])",print_r($module,true)),2);
	}

	private function buildSql($module,$addLimit = false) {
		//
		//	handled by allowOverride in config now
		//
		if ($module['folder_id'] > 0) {
			if ($module['include_subfolders']) {
				$folders = $this->fetchScalarAll(sprintf('select m1.id from members_folders m1, members_folders m2 where m1.left_id >= m2.left_id and m1.right_id <= m2.right_id and m2.id = %d order by m1.left_id',$module['folder_id']));
				$sql = sprintf("select m.*, j.sequence, j.folder_id from members m, members_by_folder j where m.deleted = 0 and m.enabled = 1 and (m.expires = '0000-00-00' or expires > curdate()) and j.folder_id in (%s) and j.member_id = m.id",implode(',',$folders));
			}
			else
				$sql = sprintf("select m.*, j.sequence, j.folder_id from members m, members_by_folder j where m.deleted = 0 and m.enabled = 1 and (m.expires = '0000-00-00' or expires > curdate()) and j.folder_id = %d and m.id = j.member_id",$module['folder_id']);
		}
		else
			$sql = "select m.*, 0 as sequence, 0 as folder_id from members m where m.deleted = 0 and m.enabled = 1 and (m.expires = '0000-00-00' or m.expires > curdate())";
		if (strlen($module['where_clause']) > 0)
			$sql .= " and ".$module['where_clause'];
		if ($module['featured'])
			$sql .= " and featured = 1";
		$sql .= " and enabled = 1 and deleted = 0";
		if ($this->hasOption("uniqueId")) $sql .= " group by m.id ";
		if (strlen($module['sort_by']) > 0)
			$sql .= " order by ".$module['sort_by'];
		else
			$sql .= " order by sequence asc";
		if ($addLimit) {
	 		if (strlen($module['records']) > 0) {
				$tmp = explode(',',$module['records']);
				if (count($tmp) > 1)
					$total = $tmp[0]*$tmp[1];
				else
					$total = $tmp[0];
				if ($total > 0)
					$sql .= " limit ".$total;
			}
		}
		$this->logMessage(__FUNCTION__,sprintf('sql [%s]',$sql),3);
		return $sql;
	}

	public function formatData($data) {
		if (strlen($data['image1']) > 0 && $data['image1'] != $this->defaultImage()) {
			$tmp = new image();
			$tmp->addAttributes(array('src'=>$data['image1'],'alt'=>htmlentities($data['firstname'].' '.$data['lastname'])));
			$data['img_image1'] = $tmp->show();
		}
		if (strlen($data['image2']) > 0 && $data['image2'] != $this->defaultImage()) {
			$tmp = new image();
			$tmp->addAttributes(array('src'=>$data['image2'],'alt'=>htmlentities($data['firstname'].' '.$data['lastname'])));
			$data['img_image2'] = $tmp->show();
		}
		$data['url'] = $this->getUrl('artist',$data["id"],$data);
		$data["strippedBio"] = strip_tags($data["biography"]);
		$data["strippedBio"] = str_replace("\r\n",' ',$data["strippedBio"]);
		$data["strippedBio"] = str_replace("\r",' ',$data["strippedBio"]);
		$data["strippedBio"] = str_replace("\n",' ',$data["strippedBio"]);
		$this->logMessage(__FUNCTION__,sprintf("return [%s]", print_r($data,true)),4);
		return $data;
	}
	
	public function formatFolder($data) {
		if (strlen($data['image']) > 0 && $data['image'] != $this->defaultImage()) {
			$tmp = new image();
			$tmp->addAttributes(array('src'=>$data['image'],'alt'=>htmlentities('Group '.$data['title'])));
			$data['img_image1'] = $tmp->show();
		}
		if (strlen($data['rollover_image']) > 0 && $data['image2'] != $this->defaultImage()) {
			$tmp = new image();
			$tmp->addAttributes(array('src'=>$data['rollover_image'],'alt'=>htmlentities('Group '.$data['title'])));
			$data['img_rollover_image'] = $tmp->show();
		}
		$data["url"] = $this->getUrl('artists',$data["id"],$data);
		$this->logMessage(__FUNCTION__,sprintf("return [%s]", print_r($data,true)),4);
		return $data;
	}

	function formatPortfolio($data) {
		if (strlen($data['image1']) > 0 && $data['image1'] != $this->defaultImage()) {
			$tmp = new image();
			$tmp->addAttributes(array('src'=>$data['image1'],'alt'=>htmlentities($data['title'])));
			$data['img_image1'] = $tmp->show();
		}
		if (strlen($data['image2']) > 0 && $data['image2'] != $this->defaultImage()) {
			$tmp = new image();
			$tmp->addAttributes(array('src'=>$data['image2'],'alt'=>htmlentities($data['title'])));
			$data['img_image2'] = $tmp->show();
		}
		return $data;
	}

	function formatItem($data) {
		if (strlen($data['image1']) > 0 && $data['image1'] != $this->defaultImage()) {
			$tmp = new image();
			$tmp->addAttributes(array('src'=>$data['image1'],'alt'=>htmlentities($data['name'])));
			$data['img_image1'] = $tmp->show();
		}
		if (strlen($data['image2']) > 0 && $data['image2'] != $this->defaultImage()) {
			$tmp = new image();
			$tmp->addAttributes(array('src'=>$data['image2'],'alt'=>htmlentities($data['name'])));
			$data['img_image2'] = $tmp->show();
		}
		return $data;
	}

	function listing() {
		if (!$module = $this->getModule())
			return "";
		$this->parseOptions($module['misc_options']);
		$this->logMessage(__FUNCTION__,sprintf("module [%s]",print_r($module,true)),2);
		$sql = $this->buildSql($module,true);
		if ($module['folder_id'] != 0)
			$root = $this->fetchSingle(sprintf('select * from members_folders where id = %d',$module['folder_id']));
		else
			$root = array('left_id'=>0,'right_id'=>999999,'level'=>0,'id'=>0,'title'=>'','image'=>'','rollover_image'=>'');
		if ($this->hasOption('typeSelect')) {
			$sql = sprintf('select * from members_folders where enabled = 1');
			if ($module['folder_id'] > 0)
				$sql .= sprintf(' and left_id > %d and right_id < %d and level > %d',$root['left_id'],$root['right_id'],$root['level']);
			if (strlen($module['sort_by']) > 0) 
				$sql .= ' order by '.$module['sort_by'];
			else
				$sql .= ' order by left_id';
			if ($module['limit'] > 0)
				$sql .= ' limit '.$module['limit'];
			$folders = $this->fetchAll($sql);
			$this->logMessage(__FUNCTION__,sprintf('sql [%s] records [%d]',$sql,count($folders)),2);
			$form = new Forms();
			$form->init($this->m_dir.$module['inner_html']);
			$result = array();
			foreach($folders as $key=>$folder) {
				$level = $folder['level'] - $root['level'];
				$spacer = str_repeat('&nbsp;',$level*2);
				$form->addData($this->formatFolder($folder));
				$form->addTag('level',$level);
				$form->addTag('spacer',$spacer,false);
				if (array_key_exists('f_id',$_REQUEST))
					$form->addTag('selected', $folder['id'] == $_REQUEST['f_id'] ? 'selected':'');
				elseif (array_key_exists('cat_id',$_REQUEST))
					$form->addTag('selected', $folder['id'] == $_REQUEST['cat_id'] ? 'selected':'');
				$result[] = $form->show();
			}
			$menu = implode("",$result);
		}
		else {
			$menu = sprintf('<ul class="level_0">%s</ul>',$this->buildUL($root,$module,0));
		}
		$outer = new Forms();
		$outer->init($this->m_dir.$module['outer_html']);
		$outer->addTag('listing',$menu,false);
		$outer->addData($this->formatFolder($root));
		$tmp = $outer->show();
		return $tmp;
	}

	private function buildUL($root,$module,$root_level) {
		$this->logMessage(__FUNCTION__,sprintf("root [%d] root_level [%d]",$root['id'],$root_level),1);
		if ($this->hasOption('maxLevel') && $root_level >= $this->getOption('maxLevel')) {
			$this->logMessage(__FUNCTION__,sprintf('max level exceeded',$this->getOption('maxLevel')),2);
			return "";
		}
		$level = $this->fetchAll(sprintf('select * from members_folders where level = %d and left_id > %d and right_id < %d and enabled = 1 order by left_id',$root['level']+1,$root['left_id'],$root['right_id']));
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
			$this->logMessage(__FUNCTION__,sprintf('subforms [%s]',print_r($subdata,true)),3);
			foreach($subdata as $key=>$value) {
				$form->addTag($key,$value,false);
			}
			$tmp = $form->show();
			$hasSubmenu = false;
			if (($subMenu = $this->buildUL($item,$module,$root_level+1)) != '') {
				$hasSubmenu = true;
				$tmp .= sprintf('<ul class="level_%d submenu">%s</ul>',$root_level+1,$subMenu);
			}
			if ($this->hasOption('delim') && $seq < count($level)) $tmp .= sprintf('<span class="delim">%s</span>',$this->getOption('delim'));
			$menu[] = sprintf('<li class="sequence_%d %s">%s</li>',$seq,$hasSubmenu ? 'hasSubmenu':'',$tmp);
		}
		$this->logMessage(__FUNCTION__,sprintf("return [%s]",print_r($menu,true)),3);
		return implode("",$menu);
	}
	
	function members() {
		if (!$module = $this->getModule())
			return "";
		$sql = $this->buildSql($module,false);
		$pagination = $this->getPagination($sql,$module,$recordcount);
		$outer = new Forms();
		$outer->init($this->m_dir.$module['outer_html']);
		$outer->setModule($module);
		$this->logMessage(__FUNCTION__,sprintf("outer [%s]", print_r($outer,true)),1);
		$outer->addTag('pagination',$pagination,false);
		$members = $this->fetchAll($sql);
		$return = array();
		$inner = new Forms();
		$inner->init($this->m_dir.$module['inner_html']);
		$ct = 0;
		if ($this->hasOption('grpPrefix')) $return[] = $this->getOption('grpPrefix');
		$moduleFolders = array($module["folder_id"]);
		if ($module['folder_id'] > 0 && $module['include_subfolders'])
			$moduleFolders = $this->fetchScalarAll(sprintf('select m1.id from members_folders m1, members_folders m2 where m1.left_id >= m2.left_id and m1.right_id <= m2.right_id and m2.id = %d order by m1.left_id',$module['folder_id']));
		foreach($members as $key=>$member) {
			$ct += 1;
			$inner->reset();
			if ($module['rows'] > 0 && $ct > $module['columns']) {
				$ct = 1;
				if ($this->hasOption('grpSuffix')) $return[] = $this->getOption('grpSuffix');
				if ($this->hasOption('grpPrefix')) 
					$return[] = $this->getOption('grpPrefix');
				else
				$return[] = '<div class="break"></div>';
			}
			if ($this->hasOption('folderInfo')) {
				if ($tmp = $this->fetchSingle(sprintf('select f.*,mf.folder_id from members_folders f, members_by_folder mf where f.id = mf.folder_id and mf.member_id = %d and mf.folder_id in (%s) order by rand() limit 1',$member['id'],implode(",",$moduleFolders)))) {
					$member["folder"] = $this->formatFolder($tmp);
				}
			}
			$subdata = $this->subForms($module['fetemplate_id'],null,array('folder_id'=>$module['folder_id'],'member_id'=>$member['id']),'inner');
			$this->logMessage(__FUNCTION__,sprintf('subforms [%s]',print_r($subdata,true)),3);
			foreach($subdata as $key=>$value) {
				$inner->addTag($key,$value,false);
			}
			if ($member['folder_id'] > 0) {
				if ($profile = $this->fetchSingle(sprintf('select * from members_by_folder where member_id = %d and folder_id = %d',$member['id'],$member['folder_id'])))
					$member["folder"] = $profile;
			}
			elseif ($module['folder_id'] != 0) {
				if ($profile = $this->fetchSingle(sprintf('select * from members_by_folder where member_id = %d and folder_id = %d',$member['id'],$module['folder_id'])))
					$member["folder"] = $profile;
			}
			$inner->addData($this->formatData($member));
			$return[] = $inner->show();
		}
		if ($this->hasOption('grpSuffix')) $return[] = $this->getOption('grpSuffix');
		$outer->addTag('members',implode('',$return),false);
		if ($module['folder_id'] != 0) {
			if ($folder = $this->fetchSingle(sprintf('select * from members_folders where id = %d',$module['folder_id']))) {
				$outer->addData($this->formatFolder($folder));
			}
		}
		$subdata = $this->subForms($module['fetemplate_id'],null,array('folder_id'=>$module['folder_id']),'outer');
		$this->logMessage(__FUNCTION__,sprintf('subforms [%s]',print_r($subdata,true)),3);
		foreach($subdata as $key=>$value) {
			$outer->addTag($key,$value,false);
		}
		return $outer->show();
	}

	function profile() {
		if (!$module = $this->getModule())
			return "";
		$profile_id = array_key_exists('member_id',$_REQUEST) ? $_REQUEST['member_id']:0;
		$outer = new Forms();
		$outer->init($this->m_dir.$module['outer_html']);
		if ($module["folder_id"] > 0 && $folder = $this->fetchSingle(sprintf('select * from members_folders where id = %d',$module['folder_id']))) {
			$profile["folder"] = $this->formatFolder($folder);
			$outer->addData($profile["folder"]);
		}
		$inner = new Forms();
		if ($profile = $this->fetchSingle(sprintf('select * from members where id = %d and enabled = 1 and deleted = 0',$profile_id))) {
			$inner->init($this->m_dir.$module['inner_html']);
			$inner->addData($this->formatData($profile));
		} else {
			$inner->init($this->m_dir.$module['parm1']);
		}
		$subdata = $this->subForms($this->m_module['fetemplate_id'],null,array('folder_id'=>$module['folder_id'],'member_id'=>$profile['id']),'inner');
		foreach($subdata as $key=>$value) {
			$inner->addTag($key,$value,false);
		}
		$subdata = $this->subForms($this->m_module['fetemplate_id'],null,array('folder_id'=>$module['folder_id'],'member_id'=>$profile['id']),'outer');
		foreach($subdata as $key=>$value) {
			$outer->addTag($key,$value,false);
		}
		$outer->addTag('profile',$inner->show(),false);
		return $outer->show();
	}

	function socialLinks() {
		if (!$module = $this->getModule())
			return "";
		$profile_id = array_key_exists('member_id',$_REQUEST) ? $_REQUEST['member_id']:0;
		$outer = new Forms();
		$outer->init($this->m_dir.$module['outer_html']);
		if ($module["folder_id"] > 0 && $folder = $this->fetchSingle(sprintf('select * from members_folders where id = %d',$module['folder_id']))) {
			$profile["folder"] = $this->formatFolder($folder);
			$outer->addData($profile["folder"]);
		}
		$inner = new Forms();
		if ($profile = $this->fetchSingle(sprintf('select * from members where id = %d',$profile_id))) {
			$inner->init($this->m_dir.$module['inner_html']);
			$inner->addData($this->formatData($profile));
		}
		$recs = $this->fetchAll(sprintf("select s.*, c.extra from members_social s, code_lookups c where member_id = %d and enabled = 1 and deleted = 0 and c.type='social_media' and c.code = s.social_type", $profile_id));
		$return = array();
		foreach($recs as $key=>$value) {
			$inner->addData($value);
			$return[] = $inner->show();
		}
		$outer->addTag("social",implode("",$return));
		return $outer->show();
	}

	function portfolios() {
		if (!$module = $this->getModule())
			return "";
		$profile_id = array_key_exists('member_id',$_REQUEST) ? $_REQUEST['member_id']:0;
		$outer = new Forms();
		$outer->init($this->m_dir.$module['outer_html']);
		if ($profile = $this->fetchSingle(sprintf('select * from members where id = %d',$profile_id))) {
			if ($module["folder_id"] > 0 && $folder = $this->fetchSingle(sprintf('select * from members_folders where id = %d',$module['folder_id']))) {
				$profile["folder"] = $this->formatFolder($folder);
			}
			$outer->addData($this->formatData($profile));
		}
		$inner = new Forms();
		$inner->init($this->m_dir.$module['inner_html']);
		$recs = $this->fetchAll(sprintf("select * from portfolio where member_id = %d and enabled = 1 and deleted = 0 order by %s limit %d", $profile_id, strlen($module["sort_by"]) > 0 ? $module["sort_by"]:"seq", $module["records"]));
		$return = array();
		foreach($recs as $key=>$value) {
			$inner->addData($this->formatPortfolio($value));
			$return[] = $inner->show();
		}
		$outer->addTag("portfolios",implode("",$return));
		return $outer->show();
	}

	function portfolio() {
		if (!$module = $this->getModule())
			return "";
		$portfolio_id = array_key_exists('portfolio_id',$_REQUEST) ? $_REQUEST['portfolio_id']:0;
		$outer = new Forms();
		$outer->init($this->m_dir.$module['outer_html']);
		if ($portfolio = $this->fetchSingle(sprintf('select * from portfolio where id = %d',$portfolio_id)))
			$outer->addData($this->formatPortfolio($portfolio));
		$inner = new Forms();
		$inner->init($this->m_dir.$module['inner_html']);
		$recs = $this->fetchAll(sprintf("select * from portfolio_item where portfolio_id = %d and enabled = 1 and deleted = 0 order by %s limit %d", $portfolio_id, strlen($module["sort_by"]) > 0 ? $module["sort_by"]:"seq", $module["records"]));
		$return = array();
		foreach($recs as $key=>$value) {
			$inner->addData($this->formatItem($value));
			$return[] = $inner->show();
		}
		$this->logMessage(__FUNCTION__,sprintf("return [%s] from [%s]", print_r($return,true), print_r($recs,true)),1);
		$outer->addTag("items",implode("",$return));
		return $outer->show();
	}

	function search() {
		if (!$module = parent::getModule())
			return "";
		$outer = new Forms();
		$outer->init($this->m_dir.$module['outer_html']);
		$flds = $this->config->getFields($module['configuration']);
		if (array_key_exists('options',$flds)) $outer->setOptions($flds['options']);
		$flds = $outer->buildForm($flds);
		if (count($_POST) > 0 && array_key_exists('artistSearch',$_POST)) {
			$outer->addData($_POST);
			if ($outer->validate()) {
				$sql = 'select m.*, a.id as addressId from members m left join addresses a on a.ownerid = m.id and a.ownertype = "member" where 1=1 ';
				if ($this->hasOption('fields')) {
					$tmp = explode('|',$this->getOption('fields'));
				}
				else {
					$tmp = array('m.company','m.firstname','m.lastname','line1','city');
				}
				$sql .= sprintf(' and (%s like "%%%s%%")',implode(sprintf(' like "%%%s%%" or ',$_POST['search']),$tmp),$_POST['search']);
				if ($module['folder_id'] != 0) {
					if ($module["include_subfolders"] != 0) {
						$sql .= sprintf(' and m.id in (select member_id from members_by_folder where folder_id in (select f1.id from members_folders f1, members_folders f2 where f2.id = %d and f1.left_id >= f2.left_id and f1.right_id <= f2.right_id and f1.enabled = 1))',$module['folder_id']);
					}
					else
						$sql .= sprintf(' and m.id in (select member_id from members_by_folder where folder_id = %d)',$module['folder_id']);
				}
				$ct = $this->fetchAll($sql);
				$outer->addTag("count",count($ct));
				$pagination = $this->getPagination($sql,$module,$recordcount);
				$outer->addTag("pagination",$pagination);
				$members = $this->fetchAll($sql);
				$this->logMessage(__FUNCTION__,sprintf('sql [%s] found [%d]',$sql,count($members)),2);
				$return = array();
				$inner = new Forms();
				$inner->init($this->m_dir.$module['inner_html']);
				foreach($members as $key=>$member) {
					$inner->reset();
					if ($member['addressId'] > 0) {
						$addr = $this->fetchSingle(sprintf('select * from addresses where id = %d',$member['addressId']));
						$member["address"] = Address::formatData($addr);
					}
					$inner->addData($this->formatData($member));
					$return[] = $inner->show();
				}
				$outer->addTag('artists',implode('',$return),false);
			}
		}
		return $outer->show();
	}

	function getModuleInfo() {
		return parent::getModuleList(array('listing','members','profile','socialLinks','portfolios','portfolio','search'));
	}
	
}

?>
