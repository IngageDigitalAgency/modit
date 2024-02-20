<?php

/**
 * Members feature for frontend
 */
class members extends Frontend {

    /**
     * @param $id
     * @param array $module
     * @throws phpmailerException
     */
    public function __construct($id, $module = array()) {
		parent::__construct();
		$this->m_dir = ADMIN.'frontend/forms/members/';
		$this->m_moduleId = $id;
		$this->m_module = $module;
		$this->logMessage(__FUNCTION__,sprintf("($id,[%s])",print_r($module,true)),2);
	}

    /**
     * @param $module
     * @param bool $addLimit
     * @return string
     * @throws phpmailerException
     */
    private function buildSql($module, $addLimit = false) {
		//
		//	handled by allowOverride in config now
		//
		//if (array_key_exists('pf_id',$_REQUEST)) $module['folder_id'] = $_REQUEST['pf_id'];	
		if ($module['folder_id'] > 0) {
			if ($module['include_subfolders']) {
				$folders = $this->fetchScalarAllTest('select m1.id from members_folders m1, members_folders m2 where m1.left_id >= m2.left_id and m1.right_id <= m2.right_id and m2.id = %d order by m1.left_id',$module['folder_id']);
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

    /**
     * @param $data
     * @return mixed
     * @throws phpmailerException
     */
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
		if (array_key_exists("folder",$data) && $data["folder"]["id"] > 0 && $t = $this->fetchSingleTest("select * from members_by_folder where member_id = %d and folder_id = %d", $data["id"], $data["folder"]["id"])) {
			$data['url'] = $this->getUrl('profile',$t['id'],$data);
		}
		$this->logMessage(__FUNCTION__,sprintf("return [%s]", print_r($data,true)),4);
		return $data;
	}

    /**
     * @param $data
     * @return mixed
     * @throws phpmailerException
     */
    public function formatFolder($data) {
		if (strlen($data['image']) > 0 && $data['image'] != $this->defaultImage()) {
			$tmp = new image();
			$tmp->addAttributes(array('src'=>$data['image'],'alt'=>htmlentities('Group '.$data['title'])));
			$data['img_image'] = $tmp->show();
		}
		if (strlen($data['rollover_image']) > 0 && $data['rollover_image'] != $this->defaultImage()) {
			$tmp = new image();
			$tmp->addAttributes(array('src'=>$data['rollover_image'],'alt'=>htmlentities('Group '.$data['title'])));
			$data['img_rollover_image'] = $tmp->show();
		}
		$data["url"] = $this->getUrl('members',$data["id"],$data);
		$this->logMessage(__FUNCTION__,sprintf("return [%s]", print_r($data,true)),4);
		return $data;
	}

    /**
     * @param $data
     * @param $member
     * @return mixed
     * @throws phpmailerException
     */
    public function formatProfile($data, $member) {
		if (array_key_exists('image1',$data) && strlen($data['image1']) > 0 && $data['image1'] != $this->defaultImage()) {
			$tmp = new image();
			$tmp->addAttributes(array('src'=>$data['image1'],'alt'=>'Profile Photo'));
			$data['img_groupImage1'] = $tmp->show();
			$data['groupImage1'] = $data['image1'];
		}
		if (array_key_exists('image2',$data) && strlen($data['image2']) > 0 && $data['image2'] != $this->defaultImage()) {
			$tmp = new image();
			$tmp->addAttributes(array('src'=>$data['image2'],'alt'=>'Profile Photo'));
			$data['img_groupImage2'] = $tmp->show();
			$data['groupImage2'] = $data['image2'];
		}
		$addresses = $this->fetchAllTest("select a.*, c.code from addresses a, code_lookups c where a.ownerid = %d and a.deleted = 0 and c.id = a.addressType order by c.sort, a.id",$data["id"]);
		foreach($addresses as $key=>$value) {
			if (!array_key_exists("addresses",$data)) $data["addresses"] = array();
			if (!array_key_exists($value["code"],$data["addresses"])) $data["addresses"][$value["code"]] = array();
			$data["addresses"][$value["code"]][] = Address::formatData($value);
		}
		$data['groupId'] = $data['id'];
		$data['groupUrl'] = $this->getUrl('profile',$data['id'],$member);
		$data['groupProfile'] = array_key_exists('profile',$data) ? $data['profile'] : '';
		$data['groupTeaser'] = array_key_exists('teaser',$data) ? $data['teaser'] : '';
		unset($data['profile']);
		unset($data['teaser']);
		unset($data['image1']);
		unset($data['image2']);
		$this->logMessage(__FUNCTION__,sprintf('return [%s]',print_r($data,true)),4);
		return $data;
	}

	/**
	 * @param $data
	 * @return mixed
	 * @throws phpmailerException
	 */
    public function formatMedia($data) {
		if ($data['type'] == 'Image') {
			$tmp = new image();
			$tmp->addAttributes(array('src'=>$data['filename'],'alt'=>$data['name']));
			$data['img_filename'] = $tmp->show();
		}
		return $data;
	}

    /**
     * @return array|mixed|string|string[]
     * @throws phpmailerException
     */
    function listing() {
		if (!$module = $this->getModule())
			return "";
		$this->parseOptions($module['misc_options']);
		$this->logMessage(__FUNCTION__,sprintf("module [%s]",print_r($module,true)),2);
		$sql = $this->buildSql($module,true);
		if ($module['folder_id'] != 0)
			$root = $this->fetchSingleTest('select * from members_folders where id = %d',$module['folder_id']);
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
			$folders = $this->fetchAllTest($sql);
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

    /**
     * @param $root
     * @param $module
     * @param $root_level
     * @return string
     * @throws phpmailerException
     */
    private function buildUL($root, $module, $root_level) {
		$this->logMessage(__FUNCTION__,sprintf("root [%d] root_level [%d]",$root['id'],$root_level),1);
		if ($this->hasOption('maxLevel') && $root_level >= $this->getOption('maxLevel')) {
			$this->logMessage(__FUNCTION__,sprintf('max level exceeded',$this->getOption('maxLevel')),2);
			return "";
		}
		$level = $this->fetchAllTest('select * from members_folders where level = %d and left_id > %d and right_id < %d and enabled = 1 order by left_id',$root['level']+1,$root['left_id'],$root['right_id']);
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

    /**
     * @return array|mixed|string|string[]
     * @throws phpmailerException
     */
    function members() {
		if (!$module = $this->getModule())
			return "";
		$sql = $this->buildSql($module,false);
		$pagination = $this->getPagination($sql,$module,$recordcount);
		$outer = new Forms();
		$outer->init($this->m_dir.$module['outer_html']);
		$outer->addTag('pagination',$pagination,false);
		$members = $this->fetchAllTest($sql);
		$return = array();
		$inner = new Forms();
		$inner->init($this->m_dir.$module['inner_html']);
		$ct = 0;
		if ($this->hasOption('grpPrefix')) $return[] = $this->getOption('grpPrefix');
		$moduleFolders = array($module["folder_id"]);
		if ($module['folder_id'] > 0 && $module['include_subfolders'])
			$moduleFolders = $this->fetchScalarAllTest('select m1.id from members_folders m1, members_folders m2 where m1.left_id >= m2.left_id and m1.right_id <= m2.right_id and m2.id = %d order by m1.left_id',$module['folder_id']);
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
				if ($tmp = $this->fetchSingleTest('select f.*,mf.folder_id from members_folders f, members_by_folder mf where f.id = mf.folder_id and mf.member_id = %d and mf.folder_id in (%s) order by rand() limit 1',$member['id'],implode(",",$moduleFolders))) {
					$member["folder"] = $this->formatFolder($tmp);
				}
			}
			$subdata = $this->subForms($module['fetemplate_id'],null,array('folder_id'=>$module['folder_id'],'member_id'=>$member['id']),'inner');
			$this->logMessage(__FUNCTION__,sprintf('subforms [%s]',print_r($subdata,true)),3);
			foreach($subdata as $key=>$value) {
				$inner->addTag($key,$value,false);
			}
			$inner->addData($this->formatData($member));
			if ($member['folder_id'] > 0) {
				if ($profile = $this->fetchSingleTest('select * from members_by_folder where member_id = %d and folder_id = %d',$member['id'],$member['folder_id']))
					$inner->addData($this->formatProfile($profile,$member));
			}
			elseif ($module['folder_id'] != 0) {
				if ($profile = $this->fetchSingleTest('select * from members_by_folder where member_id = %d and folder_id = %d',$member['id'],$module['folder_id']))
					$inner->addData($this->formatProfile($profile,$member));
			}
			$return[] = $inner->show();
		}
		if ($this->hasOption('grpSuffix')) $return[] = $this->getOption('grpSuffix');
		$outer->addTag('members',implode('',$return),false);
		if ($module['folder_id'] != 0) {
			if ($folder = $this->fetchSingleTest('select * from members_folders where id = %d',$module['folder_id'])) {
				$outer->addData($this->formatFolder($folder));
			}
		}
		return $outer->show();
	}

    /**
     * @return array|mixed|string|string[]
     * @throws phpmailerException
     */
    function media() {
		if (!$module = $this->getModule())
			return "";
		$sql = 'select * from members_media where 1=1 ';
		if ($module['folder_id'] > 0) {
			if ((array_key_exists('include_subfolders',$module) && $module['include_subfolders']) ||
				($this->hasOption('include_subfolders') && $this->getOption('include_subfolder'))) {
				$folders = $this->fetchScalarAllTest('select f2.id from members_folders f1, members_folders f2 where f2.enabled = 1 and f2.left_id >= f1.left_id and f2.right_id <= f1.right_id and f1.id = %d',$module['folder_id']);
				$sql .= sprintf(' and folder_id in (%s) ',implode(',',$folders));
			}
			else {
				$sql .= sprintf(' and folder_id = %d ',$module['folder_id']);
			}
		}
		if ($this->hasOption('mediaType'))
			$sql .= sprintf(' and type in ("%s") ',implode('","',explode('|',$this->getOption('mediaType'))));
		if (strlen($module['where_clause']) > 0)
			$sql .= ' and '.$module['where_clause'];
		if (array_key_exists('member_id',$module) && $module['member_id'] > 0)
			$sql .= ' and member_id = '.$module['member_id'];
		if (strlen($module['sort_by']) > 0)
			$sql .= ' order by '.$module['sort_by'];
		else
			$sql .= ' order by rand() ';
		if ($module['limit'] > 0)
			$sql .= ' limit '.$module['limit'];
		$media = $this->fetchAllTest($sql);
		$this->logMessage(__FUNCTION__,sprintf('sql [%s] found [%s]',$sql,count($media)),2);
		$outer = new Forms();
		$outer->init($this->m_dir.$module['outer_html']);
		$return = array();
		$inner = new Forms();
		$inner->init($this->m_dir.$module['inner_html']);
		foreach($media as $key=>$file) {
			$inner->addData($this->formatMedia($file));
			$return[] = $inner->show();
		}
		$subdata = $this->subForms($this->m_module['fetemplate_id'],null,array('folder_id'=>$module['folder_id']),'outer');
		$this->logMessage(__FUNCTION__,sprintf('subforms [%s]',print_r($subdata,true)),3);
		foreach($subdata as $key=>$value) {
			$outer->addTag($key,$value,false);
		}
		$outer->addTag('files',implode('',$return),false);
		if ($module['folder_id'] != 0) {
			if ($folder = $this->fetchSingleTest('select * from members_folders where id = %d',$module['folder_id'])) {
				$outer->addData($this->formatFolder($folder));
			}
		}
		return $outer->show();
	}

    /**
     * @return array|mixed|string|string[]
     * @throws phpmailerException
     */
    function profile() {
		if (!$module = $this->getModule())
			return "";
		$profile_id = array_key_exists('profile_id',$_REQUEST) ? $_REQUEST['profile_id']:0;
		$profile = array('id'=>0,'folder_id'=>0,'member_id'=>array_key_exists('member_id',$_REQUEST) ? $_REQUEST['member_id']:0);
		if ($profile_id > 0 && (!$profile = $this->fetchSingleTest('select * from members_by_folder where id = %d',$profile_id))) {
			$this->logMessage(__FUNCTION__,sprintf('bail - could not find folder [%s]',$profile_id),1);
			return '';
		}
		$member = $this->fetchSingleTest('select * from members where id = %d and deleted = 0 and enabled = 1 and (expires = "0000-00-00" or expires > curdate()) %s',
					$profile['member_id'],strlen($module["where_clause"]) > 0 ? sprintf(" and %s", $module["where_clause"]):"");
		$outer = new Forms();
		$outer->init($this->m_dir.$module['outer_html']);
		if ($folder = $this->fetchSingleTest('select * from members_folders where id = %d',$profile['folder_id'])) {
			$member["folder"] = $this->formatFolder($folder);
			$outer->addData($member["folder"]);
		}
		$member["profile"] = $this->formatProfile($profile,$member);
		$inner = new Forms();
		$inner->init($this->m_dir.$module['inner_html']);
		$addresses = $this->fetchAllTest('select a.*, c.code from addresses a left join code_lookups c on c.id = a.addresstype where a.ownerid = %s and a.ownertype = "member"',$profile['member_id']);
		foreach($addresses as $subkey=>$address) {
			$member['address-'.$address['code']] = Address::formatData($address);
		}
		$inner->addData($this->formatData($member));
		$subdata = $this->subForms($this->m_module['fetemplate_id'],null,array('folder_id'=>$profile['folder_id'],'member_id'=>$profile['member_id']),'inner');
		foreach($subdata as $key=>$value) {
			$inner->addTag($key,$value,false);
		}
		$subdata = $this->subForms($this->m_module['fetemplate_id'],null,array('folder_id'=>$profile['folder_id'],'member_id'=>$profile['member_id']),'outer');
		foreach($subdata as $key=>$value) {
			$outer->addTag($key,$value,false);
		}
		$outer->addTag('profile',$inner->show(),false);
		return $outer->show();
	}

    /**
     * @return array|mixed|string|string[]
     * @throws phpmailerException
     */
    function updateMember() {
		if (!$module = $this->getModule())
			return "";
		$outer = new Forms();
		if (!$this->isLoggedIn()) {
			$retValue = '';
			if (strlen($module['parm1']) > 0) {
				$outer->init($this->m_dir.$module['parm1']);
				$retValue = $outer->show();
			}
			return $retValue;
		}
		$profile_id = $_SESSION['user']['info']['id'];
		if (!$profile = $this->fetchSingleTest('select * from members where id = %d and deleted = 0 and enabled = 1 and expires = "0000-00-00" or expires > curdate()',$profile_id)) {
			return '';
		}
		$profile['password'] = '';
		$profile['imagesel_a'] = $profile['image1'];
		$profile['imagesel_b'] = $profile['image2'];
		$outer->init($this->m_dir.$module['outer_html']);
		$inner = new Forms();
		$inner->init($this->m_dir.$module['inner_html']);
		$inner->addData($this->formatData($profile));
		$flds = $inner->buildForm($this->config->getFields($module['configuration']));
		if (count($_POST) > 0 && array_key_exists('updateMember',$_POST)) {
			$_POST['imagesel_a'] = $_POST['image1'];
			$_POST['imagesel_b'] = $_POST['image2'];
			$inner->addData($_POST);
			if ($inner->validate()) {
				foreach($flds as $key=>$fld) {
					if (!(array_key_exists('database',$fld) && $fld['database'] == false)) {
						if ($fld['name'] == 'password') {
							if (strlen($inner->getData($fld['name'])) > 0)
								$values[$fld['name']] = SHA1($inner->getData($fld['name']));
						}
						else
							$values[$fld['name']] = $inner->getData($fld['name']);
					}
				}
				$stmt = $this->prepare(sprintf('update members set %s=? where id = %d',implode('=?, ',array_keys($values)),$profile['id']));
				$stmt->bindParams(array_merge(array(str_repeat('s', count($values))),array_values($values)));
				$this->beginTransaction();
				if ($status = $stmt->execute()) {
					$this->commitTransaction();
					$outer->addFormSuccess('Changes saved');
				}
				else {
					$outer->addFormError('An Error Occurred. Changes were not saved');
					$this->rollbackTransaction();
				}
			}
			else
				$inner->addFormError('Form Validation Failed');
		}
		$outer->addTag('member',$inner->show(),false);
		$profile = $this->fetchSingleTest('select * from members where id = %d and deleted = 0 and enabled = 1 and expires = "0000-00-00" or expires > curdate()',$profile_id);
		$outer->addData($profile,false);
		$subdata = $this->subForms($this->m_module['fetemplate_id'],null,array('member_id'=>$profile['id']),'outer');
		foreach($subdata as $key=>$value) {
			$outer->addTag($key,$value,false);
		}
		return $outer->show();
	}

    /**
     * @return array|mixed|string|string[]
     * @throws phpmailerException
     */
    function profileListing() {
		if (!$module = $this->getModule())
			return "";
		if ($this->hasOption('fromLogin') && $this->isLoggedIn()) {
			$p_id = $_SESSION['user']['info']['id'];
			$sql = sprintf('select * from members_by_folder where member_id = %d',$_SESSION['user']['info']['id']);
		}
		else {
			$sql = sprintf('select * from members_by_folder where member_id = %d',array_key_exists('p_id',$_REQUEST)?$_REQUEST['p_id']:0);
			$p_id = $_REQUEST['p_id'];
		}
		$profiles = $this->fetchAllTest($sql);
		$this->logMessage(__FUNCTION__,sprintf('sql [%s] records [%s]',$sql,count($profiles)),2);
		$member = $this->fetchSingleTest('select * from members where id = %d',$p_id);
		$outer = new Forms();
		$outer->init($this->m_dir.$module['outer_html']);
		$inner = new Forms();
		$inner->init($this->m_dir.$module['inner_html']);
		$tmp = array();
		foreach($profiles as $profile) {
			$inner->addData($this->formatProfile($profile,$member),false);
			$inner->addData($this->formatData($member),false);
			$folder = $this->fetchSingleTest('select * from members_folders where id = %d',$profile['folder_id']);
			$inner->addData($this->formatFolder($folder),false);
			$subdata = $this->subForms($this->m_module['fetemplate_id'],null,array('folder_id'=>$profile['folder_id'],'member_id'=>$profile['member_id']),'inner');
			foreach($subdata as $key=>$value) {
				$inner->addTag($key,$value,false);
			}
			$this->logMessage(__FUNCTION__,sprintf('inner [%s]',print_r($inner,true)),2);
			$tmp[] = $inner->show();
		}
		$outer->addTag('profiles',implode('',$tmp),false);
		$subdata = $this->subForms($this->m_module['fetemplate_id'],null,array('folder_id'=>$module['folder_id'],'member_id'=>$p_id),'outer');
		foreach($subdata as $key=>$value) {
			$outer->addTag($key,$value,false);
		}
		return $outer->show();
	}

    /**
     * @return array|mixed|string|string[]
     * @throws phpmailerException
     */
    function updateProfile() {
		if (!$module = $this->getModule())
			return "";
		if (!$this->isLoggedIn()) {
			$retValue = '';
			$this->logMessage(__FUNCTION__,'user is not logged in',1);
			if (strlen($module['parm1']) > 0) {
				$outer = new Forms();
				$outer->init($this->m_dir.$module['parm1']);
				$retValue = $outer->show();
			}
			return $retValue;
		}
		$sql = sprintf('select * from members_by_folder where member_id = %d and id = %d',$_SESSION['user']['info']['id'],$_REQUEST['p_id']);
		if (count($_REQUEST) > 0 && 
				array_key_exists('p_id',$_REQUEST) && 
				$profile = $this->fetchSingleTest($sql)) {
		}
		else {
			$this->logMessage(__FUNCTION__,sprintf('could not locate profile sql [%s]',$sql),1);
			return "";
		}
		$outer = new Forms();
		$outer->init($this->m_dir.$module['outer_html']);
		if ($folder = $this->fetchSingleTest('select * from members_folders where id = %d',array_key_exists('folder_id',$profile?$profile['folder_id']:0))) {
			$outer->addData($this->formatFolder($folder));
		}
		$inner = new Forms();
		$flds = $inner->buildForm($this->config->getFields($module['configuration']));
		$inner->init($this->m_dir.$module['inner_html']);
		$member = $this->fetchSingleTest('select * from members where id = %d and deleted = 0',$profile['member_id']);
		$outer->addData($this->formatData($member));
		$profile['imagesel_a'] = $profile['image1'];
		$profile['imagesel_b'] = $profile['image2'];
		$inner->addData($profile);
		if (count($_POST) > 0 && array_key_exists('updateProfile',$_POST)) {
			$inner->addData($_POST);
			if ($inner->validate()) {
				foreach($flds as $key=>$fld) {
					if (!(array_key_exists('database',$fld) && $fld['database'] == false)) {
						if ($fld['name'] == 'password') {
							if (strlen($inner->getData($fld['name'])) > 0)
								$values[$fld['name']] = SHA1($inner->getData($fld['name']));
						}
						else
							$values[$fld['name']] = $inner->getData($fld['name']);
					}
				}
				$stmt = $this->prepare(sprintf('update members_by_folder set %s=? where id = %d',implode('=?, ',array_keys($values)),$profile['id']));
				$stmt->bindParams(array_merge(array(str_repeat('s', count($values))),array_values($values)));
				$this->beginTransaction();
				if ($status = $stmt->execute()) {
					$this->commitTransaction();
					$outer->addFormSuccess('Changes saved');
				}
				else {
					$outer->addFormError('An Error Occurred. Changes were not saved');
					$this->rollbackTransaction();
				}
			}
			else
				$inner->addFormError('Form Validation Failed');
		}
		$subdata = $this->subForms($this->m_module['fetemplate_id'],null,array('folder_id'=>$profile['folder_id'],'member_id'=>$profile['member_id']),'inner');
		foreach($subdata as $key=>$value) {
			$inner->addTag($key,$value,false);
		}
		$subdata = $this->subForms($this->m_module['fetemplate_id'],null,array('folder_id'=>$profile['folder_id'],'member_id'=>$profile['member_id']),'outer');
		foreach($subdata as $key=>$value) {
			$outer->addTag($key,$value,false);
		}
		$outer->addTag('form',$inner->show(),false);
		return $outer->show();
	}

    /**
     * @return array|mixed|string|string[]
     * @throws phpmailerException
     */
    function memberFiles() {
		if (!$module = $this->getModule())
			return "";
		if ($this->hasOption('fromLogin') && $this->isLoggedIn()) {
			$p_id = $_SESSION['user']['info']['id'];
			$sql = sprintf('select * from members_media where member_id = %d',$_SESSION['user']['info']['id']);
		}
		elseif ($this->hasOption('groupMedia') && $this->isLoggedIn()) {
			$f_id = $module['folder_id'];
			$p_id = 0;
			if ($f_id == 0) {
				$module['folder_id'] = $this->fetchScalarTest('select folder_id from members_by_folder where member_id = %d limit 1',$_SESSION['user']['info']['id']);
				$this->logMessage(__FUNCTION__,sprintf('folder overridden to %s',$module['folder_id']),2);
			}
			$sql = sprintf('select * from members_media where member_id = 0');
		}
		else {
			$p_id = array_key_exists('p_id',$_REQUEST)?$_REQUEST['p_id']:0;
			$sql = sprintf('select * from members_media where member_id = %d',$p_id);
		}
		if ($module['folder_id'] > 0)
			$sql .= sprintf(' and folder_id = %d',$module['folder_id']);
		if ($this->hasOption('mediaType')) {
			$sql .= sprintf(' and type in ("%s") ',implode('","',explode('|',$this->getOption('mediaType'))));
		}
		if (strlen($module['sort_by']) > 0)
			$sql .= ' order by '.$module['sort_by'];
		else
			$sql .= ' order by type, name';
		if (count($_POST) > 0 && array_key_exists('memberFiles',$_POST)) {
			if (array_key_exists('delete',$_POST)) {
				$deletes = $_POST['delete'];
				foreach($deletes as $key=>$value) {
					if ($file = $this->fetchSingleTest('select * from members_media where id in (%s) and ((member_id = %d) or (folder_id = %d and member_id = 0))',implode(',',$deletes),$p_id,$module['folder_id'],$p_id)) {
						$eLevel = error_reporting();
						error_reporting(0);
						unlink('.'.$file['filename']);
						error_reporting($eLevel);
						$this->execute(sprintf('delete from members_media where id = %s',$file['id']));
					}
				}
			}
		}
		$media = $this->fetchAllTest($sql);
		$this->logMessage(__FUNCTION__,sprintf('sql [%s] records [%s]',$sql,count($media)),2);
		$outer = new Forms();
		$outer->init($this->m_dir.$module['outer_html']);
		$inner = new Forms();
		$inner->init($this->m_dir.$module['inner_html']);
		$tmp = array();
		foreach($media as $file) {
			$inner->addData($this->formatMedia($file),false);
			$subdata = $this->subForms($this->m_module['fetemplate_id'],null,array('folder_id'=>$file['folder_id'],'member_id'=>$file['member_id']),'inner');
			foreach($subdata as $key=>$value) {
				$inner->addTag($key,$value,false);
			}
			$tmp[] = $inner->show();
		}
		$outer->addTag('media',implode('',$tmp),false);
		$outer->addData($module);
		if ($module['folder_id'] > 0) {
			if ($folder = $this->fetchSingleTest('select * from members_folders where id = %d',$module['folder_id'])) {
				$outer->addData($this->formatFolder($folder));
			}
		}
		$subdata = $this->subForms($this->m_module['fetemplate_id'],null,array('folder_id'=>$module['folder_id'],'member_id'=>$p_id),'outer');
		foreach($subdata as $key=>$value) {
			$outer->addTag($key,$value,false);
		}
		return $outer->show();
	}

    /**
     * @return array|mixed|string|string[]
     * @throws phpmailerException
     */
    function editMedia() {
		if (!$module = $this->getModule())
			return "";
		$m_id = 0;
		if (array_key_exists('m_id',$_REQUEST))
			$m_id = $_REQUEST['m_id'];
		if ($this->hasOption('fromLogin') && $this->isLoggedIn()) {
			$sql = sprintf('select * from members_media where id = %d and member_id = %d',$m_id,$_SESSION['user']['info']['id']);
		}
		else {
			$sql = sprintf('select * from members_media where id = %d',$m_id);
		}
		$outer = new Forms();
		$outer->init($this->m_dir.$module['outer_html']);
		$inner = new Forms();
		$inner->init($this->m_dir.$module['inner_html']);
		$flds = $inner->buildForm($this->config->getFields($module['configuration']));
		if (!$media = $this->fetchSingleTest($sql)) 
			$media = array('id'=>0,'type'=>'','member_id'=>$_SESSION['user']['info']['id'],'folder_id'=>$_REQUEST['folder_id']);
		else
			$inner->deleteElement('filename');
		$this->logMessage(__FUNCTION__,sprintf('sql [%s] count [%d]',$sql,count($media)),3);
		$inner->addData($this->formatMedia($media));
		if (count($_POST) > 0 && array_key_exists('editMedia',$_POST)) {
			$inner->addData($_POST);
			$valid = $inner->validate();
			if ($valid && $media['id'] == 0) {
				if (count($_FILES) == 0) {
					$valid = false;
					$inner->addFormError('No file was attached');
				}
				else {
					if (!($valid = $this->processUploadedFiles(array('Image','Video','Audio'),$return,$messages))) {
						foreach($messages as $key=>$value)
							$inner->addFormError($value);
					}
				}
			}
			if ($valid) {
				foreach($flds as $key=>$fld) {
					if (!(array_key_exists('database',$fld) && $fld['database'] == false)) {
						$values[$fld['name']] = $inner->getData($fld['name']);
					}
				}
				if ($media['id'] == 0) {
					$values['filename'] = $return['filename']['name'];
					$values['type'] = $return['filename']['type'];
				}
				if ($media['id'] == 0)
					$stmt = $this->prepare(sprintf('insert into members_media(%s) values(%s?)',implode(', ',array_keys($values)),str_repeat('?,',count($values)-1)));
				else {
					$stmt = $this->prepare(sprintf('update members_media set %s=? where id = %d',implode('=?, ',array_keys($values)),$media['id']));
				}
				$stmt->bindParams(array_merge(array(str_repeat('s', count($values))),array_values($values)));
				$this->beginTransaction();
				if ($status = $stmt->execute()) {
					$this->commitTransaction();
					$inner->deleteElement('filename');
					$outer->addFormSuccess('Changes saved');
					if (strlen($module['parm1']) > 0)
						$inner->init($this->m_dir.$module['parm1']);
				}
				else {
					$outer->addFormError('An Error Occurred. Changes were not saved');
					$this->rollbackTransaction();
				}
			}
		}
		$outer->addTag('form',$inner->show(),false);
		return $outer->show();
	}

    /**
     * @return array|mixed|string|string[]|void
     * @throws phpmailerException
     */
    public function signup() {
		if (!$module = parent::getModule())
			return "";
		$outer = new Forms();
		$outer->init($this->m_dir.$module['outer_html']);
		$subdata = $this->subForms($this->m_module['fetemplate_id'],null,array(),'outer');
		$this->logMessage(__FUNCTION__,sprintf('subforms [%s]',print_r($subdata,true)),3);
		foreach($subdata as $key=>$value) {
			$outer->addTag($key,$value,false);
		}
		if ($this->hasOption('usePassed') && count($_REQUEST) > 0 && array_key_exists('signupForm',$_REQUEST) && array_key_exists('m_id',$_REQUEST)) {
			$this->logMessage(__FUNCTION__,sprintf('adding member from m_id [%d]',$_REQUEST['m_id']),2);
			if ($member = $this->fetchSingleTest('select * from members where id = %d and deleted = 0',$_REQUEST['m_id'])) {
				$address = $this->fetchSingleTest('select * from addresses where ownerid = %d and ownertype = "member"',$_REQUEST['m_id']);
				$member['a_id'] = $address['id'];
				$member['m_id'] = $member['id'];
				$member['address'] = $address;
				$this->logMessage(__FUNCTION__,sprintf('adding data [%s]',print_r($member,true)),2);
				$outer->addData($this->formatData($member));
			}
		}
		$flds = $this->config->getFields($module['configuration']);
		if (array_key_exists('options',$flds)) $outer->setOptions($flds['options']);
		$outer->setModule($module);
		$flds = $outer->buildForm($flds);
		if (count($_POST) > 0 && array_key_exists('signupForm',$_POST)) {
			$outer->addData($_POST);
			if ($valid = $outer->validate()) {
				if (array_key_exists('password',$flds)) {
					if ($outer->getData('password') != $outer->getData('password_confirm')) {
						$valid = false;
						$outer->addFormError('Password and confirmation do not match');
					}
				}
				if (array_key_exists('email',$flds)) {
					if ($outer->getData('email') != $outer->getData('email_confirm')) {
						$valid = false;
						$outer->addFormError('Email and confirmation do not match');
					}
					$email = $this->fetchScalarTest('select count(*) from members where deleted = 0 and email = "%s"',$outer->getData('email'));
					if ($email > 0) {
						$valid = false;
						$outer->addFormError('This email has already been registered');
					}
				}
				if ($valid && array_key_exists('username',$flds)) {
					$email = $this->fetchScalarTest('select count(*) from members where deleted = 0 and username = "%s"',$outer->getData('username'));
					if ($email > 0) {
						$valid = false;
						$outer->addFormError('This login id has already been registered');
						$outer->addTag("duplicateUsernameError","*");
					}
				}
				$return = array();
				if (count($_FILES) > 0) {
					if (!($valid = $this->processUploadedFiles(array('Image'),$return,$messages))) {
						foreach($messages as $key=>$value)
							$outer->addFormError($value);//Sonarcloud report - Variables should be initialized before use
					}
				}
				if ($valid) {
					if (!array_key_exists("address",$_POST))
						$address = array();
					else {
						$address = $_POST['address'];
						unset($_POST['address']);
					}
					$values = array();
					foreach($_POST as $key=>$value) {
						if (array_key_exists($key,$flds)) {
							if ((!array_key_exists('database',$flds[$key]) || $flds[$key]['database'] == true)) {
								$values[$key] = $outer->getData($key);
							}
						}
					}
					if (array_key_exists('password',$flds))
						$values['password'] = SHA1($values['password']);
					$m_id = array_key_exists('m_id',$_POST) && $_POST['m_id'] > 0 ? $_POST['m_id'] : 0;
					$a_id = array_key_exists('a_id',$_POST) && $_POST['a_id'] > 0 ? $_POST['a_id'] : 0;
					if ($m_id > 0)
						$stmt = $this->prepare(sprintf('update members set %s=? where id = %d',implode('=?, ',array_keys($values)),$_POST['m_id']));
					else {
						$values['created'] = date(DATE_ATOM);
						$stmt = $this->prepare(sprintf('insert into members(%s) values(%s)',implode(',',array_keys($values)),str_repeat('?,',count($values)-1).'?'));
					}
					$stmt->bindParams(array_merge(array(str_repeat('s',count($values))),array_values($values)));
					$this->beginTransaction();
					if ($valid = $stmt->execute()) {
						if ($m_id == 0) {
							$m_id = $this->insertId();
							$values["id"] = $m_id;
							$outer->setData('m_id',$m_id);
						}

						$base_dir = sprintf('./images/members/%d',$m_id);	// relative path name
						if (!file_exists($base_dir))
							if (!$this->hasOption('noFolders'))
								mkdir($base_dir);
						if ($this->hasOption('autoJoin') && $module['folder_id'] > 0 && $valid) {
							if (!$grp = $this->fetchSingleTest('select * from members_by_folder where member_id = %d and folder_id = %d',$m_id,$module['folder_id'])) {
								$grp = array('member_id'=>$m_id,'folder_id'=>$module['folder_id']);
								$valid = $valid && $tmp = $this->prepare(sprintf('insert into members_by_folder(%s) values(%s?)',implode(', ',array_keys($grp)),str_repeat('?, ',count($grp)-1)));
								$tmp->bindParams(array_merge(array(str_repeat('s',count($grp))),array_values($grp)));
								$tmp->execute();
								$grp['id'] = $this->insertId();
							}
						}
						if (count($return) > 0 && $valid) {
							$media = array();
							foreach($return as $key=>$file) {
								$tmp = sprintf('%s/%s',$base_dir,str_replace('/files/','',$file['name']));
								rename('.'.$file['name'],$tmp);	// make relative again
								$file['name'] = substr($tmp,1);	// strip the leading . for the web site
								$media[$key] = $file['name'];
							}
							$tmp = $this->prepare(sprintf('update members set %s=? where id = %d',implode('=?, ',array_keys($media)),$m_id));
							$tmp->bindParams(array_merge(array(str_repeat('s',count($media))),array_values($media)));
							$valid = $valid && $tmp->execute();
						}
						$addr = array();
						foreach($address as $key=>$value) {
							$addr[$key] = $value;
						}
						$addr['firstname'] = $outer->getData('firstname');
						$addr['lastname'] = $outer->getData('lastname');
						$addr['email'] = $outer->getData('email');
						$addr['ownerid'] = $m_id;
						$addr['ownertype'] = 'member';
						$addr['tax_address'] = 1;
						$addr['addresstype'] = $this->fetchScalarTest('select id from code_lookups where type="memberAddressTypes" and extra = 1');
						if ($a_id == 0)
							$stmt = $this->prepare(sprintf('insert into addresses(%s) values(%s)',implode(',',array_keys($addr)),str_repeat('?,',count($addr)-1).'?'));
						else
							$stmt = $this->prepare(sprintf('update addresses set %s=? where id = %d and ownertype="member" and ownerid = %d',
								implode('=?, ',array_keys($addr)),$a_id,$m_id));
						$stmt->bindParams(array_merge(array(str_repeat('s',count($addr))),array_values($addr)));
						$valid = $valid && $stmt->execute();
						$outer->setData('a_id',$a_id);
						if ($valid) {
							$this->commitTransaction();
							$custom = new custom(0);
							if (method_exists($custom,"postSignup")) {
								$valid &= $custom->postSignup($values);
							}
							if (array_key_exists('password',$values) && !$this->hasOption('usePassed'))
								$valid = $this->logMeIn($values['username'],$values['password']);
							else $valid = true;
							if ($valid) {
								if (strlen($module['parm1']) > 0) {
									$outer = new Forms();
									$outer->init($this->m_dir.$module['parm1']);
								}
								if ($this->hasOption('showInputOnSuccess'))
									$flds = $outer->buildForm($flds);
								if ($member = $this->fetchSingleTest('select * from members where id = %d and deleted = 0',$m_id)) {
									$address = $this->fetchSingleTest('select * from addresses where ownerid = %d and ownertype = "member"',$m_id);
									$tmp = $this->formatData($member);
									$tmp['address'] = Address::formatData($address);
									$outer->addData($tmp);
								}
								$outer->addFormSuccess('Information Updated');
							}
						}
						else
							$this->rollbackTransaction();
					}
					else {
						$this->rollbackTransaction();
					}
					if (!$valid) $outer->addFormError('An error occurred. The Web Master has been notified');
				}
			}
		}
		if ($this->isAjax())
			$this->ajaxReturn(array('status'=>'true','html'=>$outer->show()));
		else
			return $outer->show();
	}

    /**
     * @return array|mixed|string|string[]
     * @throws phpmailerException
     */
    function search() {
		if (!$module = parent::getModule())
			return "";
		$outer = new Forms();
		$outer->init($this->m_dir.$module['outer_html']);
		$flds = $this->config->getFields($module['configuration']);
		if (array_key_exists('options',$flds)) $outer->setOptions($flds['options']);
		$flds = $outer->buildForm($flds);
		if (count($_POST) > 0 && array_key_exists('memberSearch',$_POST)) {
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
				$pagination = $this->getPagination($sql,$module,$recordcount);
				$outer->addTag("pagination",$pagination);
				$members = $this->fetchAllTest($sql);
				$this->logMessage(__FUNCTION__,sprintf('sql [%s] found [%d]',$sql,count($members)),2);
				$return = array();
				$inner = new Forms();
				$inner->init($this->m_dir.$module['inner_html']);
				foreach($members as $key=>$member) {
					$inner->reset();
					if ($member['addressId'] > 0) {
						$addr = $this->fetchSingleTest('select * from addresses where id = %d',$member['addressId']);
						$inner->addData(Address::formatData($addr));
					}
					$inner->addData($this->formatData($member));
					$return[] = $inner->show();
				}
				$outer->addTag('members',implode('',$return),false);
			}
		}
		return $outer->show();
	}

    /**
     * @return array|mixed|string|string[]
     * @throws phpmailerException
     */
    function orderSearch() {
		if (!$module = parent::getModule())
			return "";
		$outer = new Forms();
		$outer->init($this->m_dir.$module['outer_html']);
		$flds = $this->config->getFields($module['configuration']);
		if (array_key_exists('options',$flds)) $outer->setOptions($flds['options']);
		if ($module['folder_id'] > 0)
			$sql = sprintf('select id, company from members where deleted = 0 and enabled = 1 and id in (select member_id from members_by_folder where folder_id = %d) order by company',$module['folder_id']);
		else
			$sql = sprintf('select id, company from members where deleted = 0 and enabled = 1 order by company');
		$flds['member_id']['sql'] = $sql;
		$flds = $outer->buildForm($flds);
		$orders = array();
		if ((count($_POST) > 0 && array_key_exists('orderSearch',$_POST) && $_POST['orderSearch'] == $flds['orderSearch']['value']) || $this->hasOption('showAlways')) {
			$outer->addData($_POST);
			if ($outer->validate()) {
				$sql = 'select m.*, o.* from orders o, members m where m.id = o.member_id and o.deleted = 0 ';
				if (array_key_exists('order_id',$_REQUEST) && $_REQUEST['order_id'] > 0) {
					$sql .= sprintf(" and o.id = %d",$_REQUEST['order_id']);
				}
				else {
					if (array_key_exists('member_id',$_POST) && $_POST['member_id'] > 0) {
						$sql .= sprintf(' and o.member_id = %d',$_POST['member_id']);
					}
					if (array_key_exists('startDate',$_POST) && $_POST['startDate'] != '') {
						$sql .= sprintf(' and order_date >= "%s"',$outer->getData('startDate'));
					}
					if (array_key_exists('endDate',$_POST) && $_POST['endDate'] != '') {
						$sql .= sprintf(' and order_date <= "%s 23:59:59"',$outer->getData('endDate'));
					}
					if (array_key_exists('product_id',$_POST) && $_POST['product_id'] > 0) {
						$sql .= sprintf(' and o.id in (select order_id from order_lines where product_id = %d)',$outer->getData('product_id'));
						$outer->setData('product_id',$_POST['product_id']);
					}
				}
				if (strlen($module['sort_by']) > 0)
					$sql .= " order by ".$module['sort_by'];
				if ($module['rows'] > 1) {
					$pagination = $this->getPagination($sql,$module,$recordCount);
					$outer->addTag('pagination',$pagination,false);
				}
				$orders = $this->fetchAllTest($sql);
				$this->logMessage(__FUNCTION__,sprintf('sql [%s] found [%d]',$sql,count($orders)),2);
				$return = array();
				$inner = new Forms();
				$inner->init($this->m_dir.$module['inner_html']);
				foreach($orders as $key=>$order) {
					$inner->addData(common::formatOrder($order));
					$return[] = $inner->show();
				}
				$outer->addTag('orders',implode('',$return),false);
			}
		}
		$outer->addTag('orderCount',count($orders));
		return $outer->show();
	}

    /**
     * @return array|mixed|string|string[]
     * @throws phpmailerException
     */
    function createOrder() {
		if (!$module = parent::getModule())
			return "";
		$outer = new Forms();
		$outer->init($this->m_dir.$module['outer_html']);
		$flds = $this->config->getFields($module['configuration']);
		if (array_key_exists('options',$flds)) $outer->setOptions($flds['options']);
		$m_id = array_key_exists('m_id',$_REQUEST) ? $_REQUEST['m_id']:0;
		$o_id = array_key_exists('o_id',$_REQUEST) ? $_REQUEST['o_id']:0;
		$data = array('id'=>0,'member_id'=>$m_id);
		$member = array('id'=>$m_id);
		$address = $this->fetchSingleTest('select * from addresses where ownertype = "member" and ownerid = %d',$m_id);
		if ($module['folder_id'] > 0)
			$sql = sprintf('select id, company from members where deleted = 0 and enabled = 1 and id in (select member_id from members_by_folder where folder_id = %d) order by company',$module['folder_id']);
		else
			$sql = sprintf('select id, company from members where deleted = 0 and enabled = 1 order by company');
		$flds['member_id']['sql'] = $sql;
		if ($o_id > 0) {
			if ($data = $this->fetchSingleTest('select * from orders where id = %d',$_REQUEST['o_id'])) {
				$m_id = $data['member_id'];
				if (!$address = $this->fetchSingleTest('select * from addresses where ownertype="order" and ownerid = %d',$o_id)) {
					$outer->addFormError("Sorry, We couldn't locate an address for this customer");
					$this->logMessage(__FUNCTION__,sprintf('missing address for customer [%s]',$m_id),1,true);
					$address = array('id'=>0);
				}
			}
			else {
				$data = array('id'=>0,'member_id'=>$m_id);
			}
		}
		if (!$member = $this->fetchSingleTest('select * from members where id = %d',$m_id)) {
			$member = array('id'=>$m_id);
			$address = array();
		}
		else {
			unset($flds['member_id']);
			$outer->addTag('member_id',$member['company']);
		}
		$flds = $outer->buildForm($flds);
		$outer->addData($member);
		$outer->addData($address);
		$outer->addData($data);
		$inner = new Forms();
		$inner->init($this->m_dir.$module['inner_html']);
		$flds = $inner->buildForm($this->config->getFields($module['configuration']));
		if (count($_POST) > 0 && array_key_exists('createOrder',$_POST)) {
			$outer->addData($_POST);
			if ($valid = $outer->validate()) {
				if ($_POST['o_id'] == 0) {
					foreach($flds as $key=>$value) {
						if (!array_key_exists('database',$value)) {
							$values[$key] = $outer->getData($key);
						}
					}
					$values['created'] = date(DATE_ATOM);
					$values['order_date'] = date(DATE_ATOM);
					$valid = $valid && $stmt = $this->prepare(sprintf('insert into orders(%s) values (%s?)',implode(', ',array_keys($values)),str_repeat('?,',count($values)-1)));
				}
				else {
					$values = array('customAltOrderNumber'=>$_POST['customAltOrderNumber']);
					$valid = $valid && $stmt = $this->prepare(sprintf('update orders set %s=? where id = %d',implode('=?, ',array_keys($values)),$o_id));
				}
				$stmt->bindParams(array_merge(array(str_repeat('s', count($values))),array_values($values)));
				$this->logMessage(__FUNCTION__,sprintf('stmt [%s]',print_r($stmt,true)),1);
				$this->beginTransaction();
				if ($valid = $stmt->execute()) {
					if ($o_id == 0) {
						$o_id = $this->insertId();
						$_REQUEST['o_id'] = $o_id;
						$outer->addTag('o_id',$o_id);
						$outer->addTag('id',$o_id);
						$addresses = $this->fetchAllTest('select * from addresses where ownertype="member" and ownerid = %d',$values['member_id']);
						$tmp = array();
						foreach($addresses as $key=>$address) {
							$tmp = array();
							unset($address['id']);
							foreach($address as $subkey=>$value) {
								$tmp[$subkey] = $value;
							}
							$tmp['ownertype'] = 'order';
							$tmp['ownerid'] = $o_id;
							$stmt = $this->prepare(sprintf('insert into addresses(%s) values (%s?)',implode(', ',array_keys($tmp)),str_repeat('?,',count($tmp)-1)));
							$stmt->bindParams(array_merge(array(str_repeat('s', count($tmp))),array_values($tmp)));
							$stmt->execute();
						}
					}
					$this->commitTransaction();
				}
				else {
					$outer->addFormError('An Error Occurred');
					$this->rollbackTransaction();
				}
			}
		}
		$subdata = $this->subForms($this->m_module['fetemplate_id'],null,array('folder_id'=>$module['folder_id']),'outer');
		$this->logMessage(__FUNCTION__,sprintf('subforms [%s]',print_r($subdata,true)),3);
		foreach($subdata as $key=>$value) {
			$outer->addTag($key,$value,false);
		}
		return $outer->show();
	}

    /**
     * @return array|mixed|string|string[]
     * @throws phpmailerException
     */
    function createOrderLines() {
		if (!$module = parent::getModule())
			return "";
		$outer = new Forms();
		$outer->init($this->m_dir.$module['outer_html']);
		$flds = $this->config->getFields($module['configuration']);
		if (array_key_exists('options',$flds)) $outer->setOptions($flds['options']);
		$m_id = array_key_exists('m_id',$_REQUEST) ? $_REQUEST['m_id']:0;
		$o_id = array_key_exists('o_id',$_REQUEST) ? $_REQUEST['o_id']:0;
		$data = array('id'=>0,'member_id'=>$m_id);
		if ($o_id > 0) {
			if ($data = $this->fetchSingleTest('select * from orders where id = %d',$_REQUEST['o_id'])) {
				$m_id = $data['member_id'];
			}
			else {
				$data = array('id'=>0,'member_id'=>$m_id);
			}
		}
		$flds = $outer->buildForm($flds);
		$outer->addData($data);
		$inner = new Forms();
		$inner->init($this->m_dir.$module['inner_html']);
		$flds = $inner->buildForm($this->config->getFields($module['configuration']));
		$inner->setData('id',0);
		$blank = $inner->show();
		$return = array();
		if (count($_POST) > 0 && array_key_exists('createOrder',$_POST)) {
			//
			//	handle deletes first
			//
			$deletes = array();
			if (array_key_exists('delete',$_POST)) {
				$deletes = $_POST['delete'];
				unset($_POST['delete']);
				//
				//	can be a checkbox or hidden - checkboxes don't pass value=0, hidden does
				//
				foreach($deletes as $key=>$value) {
					if ($value == 0)
						unset($deletes[$key]);
				}
				if (count($deletes) > 0) {
					$sql = sprintf('delete from order_lines where id in (%s) and order_id = %d',implode(',',array_keys($deletes)),$o_id);
					$stmt = $this->prepare($sql);
					$stmt->execute();
				}
			}
			$lines = $_POST['lines'];
			unset($_POST['lines']);
			$return = array();
			$valid = true;
			foreach($lines as $key=>$values) {
				if ($key == 0 || !array_key_exists($key,$deletes)) {
					$this->logMessage(__FUNCTION__,sprintf('validate line [%s]',$key),1);
					$inner->reset();
					$inner->getField('options_id')->setOptions($this->fetchOptionsTest('select id,teaser from product_options where product_id = %d',$values['product_id']));
					$this->logMessage(__FUNCTION__,sprintf('options added (1) for %s [%s]',$values['product_id'],print_r($inner,true)),1);
					$inner->addData($values);
					$valid = $valid && $inner->validate();
					$return[] = $inner->show();
				}
			}
			if (!$valid) {
				$outer->addTag('lines',implode('',$return),false);
				$outer->addFormError('Data validation failed');
				return $outer->show();
			}
			if ($valid) {
				foreach($lines as $key=>$values) {
					if ($key == 0 || !array_key_exists($key,$deletes)) {
						$tmp = array();
						//foreach($values as $subKey=>$value) {
						foreach($flds as $subkey=>$fld) {
							if (!array_key_exists('database',$fld)) {
								if (array_key_exists($subkey,$values))
									$tmp[$subkey] = $values[$subkey];
							}
						}
						if ($key == 0) {
							$tmp['order_id'] = $o_id;
							$ct = $this->fetchScalarTest('select max(line_id) from order_lines where order_id = %d',$o_id);
							$tmp['line_id'] = $ct+1;
							$valid = $valid && $stmt = $this->prepare(sprintf('insert into order_lines(%s) values(%s?)',implode(', ',array_keys($tmp)),str_repeat('?, ',count($tmp)-1)));
						}
						else {
							$valid = $valid && $stmt = $this->prepare(sprintf('update order_lines set %s=? where id = %d and order_id = %d',implode('=?, ',array_keys($tmp)),$key,$o_id));
						}
						$stmt->bindParams(array_merge(array(str_repeat('s', count($tmp))),array_values($tmp)));
						$valid = $valid && $stmt->execute();
						if ($key == 0)
							$key = $this->insertId();
					}
				}
			}
			if (!$valid) {
				$outer->addTag('lines',implode('',$return),false);
				$outer->addFormError('An error occurred processing the line data');
				return $outer->show();
			}
			else $outer->addFormSuccess('Changes saved');
		}
		$lines = $this->fetchAllTest('select l.*, p.name, p.code, p.customMaterial, p.customWidth, p.customLabelFormat, c.id as labelId, c.value as labelValue from order_lines l, product p, code_lookups c where l.order_id = %d and p.id = l.product_id and c.type = "labelFormat" and c.id = p.customLabelFormat order by id',$data['id']);
		$return = array();
		foreach($lines as $key=>$line) {
			$inner->reset();
			$inner->getField('options_id')->setOptions($this->fetchOptionsTest('select id,teaser from product_options where product_id = %d',$line['product_id']));
			$this->logMessage(__FUNCTION__,sprintf('options added (2) for %s line [%s] [%s]',$line['product_id'],print_r($line,true),print_r($inner,true)),1);
			$inner->addData(common::formatOrderLine($line));
			$return[] = $inner->show();
		}
		if (count($return) == 0 || (array_key_exists('addRow',$_REQUEST) && $_REQUEST['addRow'] != 0)) {
			$return[] = $blank;
		}
		$outer->addTag('lines',implode('',$return),false);
		return $outer->show();
	}

    /**
     * @return array|mixed|string|string[]
     * @throws phpmailerException
     */
    function login() {
		//
		//	clone of htmlForms::login except for the check of membership
		//
		if (!$module = parent::getModule())
			return "";
		$obj = new htmlForms($this->m_moduleId,$module);
		$html = $obj->login();
		return $html;
	}

    /**
     * @return array|mixed|string|string[]
     * @throws phpmailerException
     */
    function memberOf() {
		if (!$module = parent::getModule())
			return "";
		$outer = new Forms();
		$outer->init($this->m_dir.$module['outer_html']);
		$flds = $this->config->getFields($module['configuration']);
		$inner = new Forms();
		$inner->init($this->m_dir.$module['inner_html']);
		$inner->buildForm($flds);
		if ($f = $this->fetchSingleTest("select * from members_folders where id = %d", $module["folder_id"])) {
			$outer->addData($this->formatFolder($f));
			if ($m = $this->fetchSingleTest("select * from members_by_folder where folder_id = %d and member_id = %d", $f["id"], $this->getUserInfo("id"))) {
				$inner->addData($m);
			}
			else {
				$inner->init($this->m_dir.$module['parm1']);
				$inner->addData(array("id"=>0,"folder_id"=>0));
			}
		}
		$outer->addTag( "member", $inner->show(), false );
		return $outer->show();
	}

    /**
     * @return false|string
     * @throws phpmailerException
     */
    function getModuleInfo() {
		return parent::getModuleList(array('listing','members','media','profile','updateMember','updateProfile','memberFiles','profileListing','editMedia','signup','search','orderSearch','createOrder','createOrderLines','login','memberOf'));
	}
	
}

?>
