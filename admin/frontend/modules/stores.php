<?php

/**
 * Manages stores for frontend
 */
class stores extends Frontend {

    /**
     * @param $id
     * @param array $module
     * @throws phpmailerException
     */
	public function __construct($id, $module = array()) {
		parent::__construct();
		$this->m_dir = ADMIN.'frontend/forms/stores/';
		$this->m_moduleId = $id;
		$this->m_module = $module;
		$this->m_storeId = array_key_exists('store_id',$_REQUEST) ? $_REQUEST['store_id'] : 0;
		$this->m_folderId = array_key_exists('storecat',$_REQUEST) ? $_REQUEST['storecat'] : 0;
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
				$sql = sprintf('select sf.id from store_folders sf, store_folders sf1 where sf1.id = %d and sf.left_id >= sf1.left_id and sf.right_id <= sf1.right_id and sf.enabled = 1',$module['folder_id']);
				$tmp = array_merge(array(0),$this->fetchScalarAllTest($sql));
				$sql = sprintf("select s.*, j.sequence from stores s, stores_by_folder j where s.deleted = 0 and s.enabled = 1 and s.published = 1 and j.store_id = s.id and j.folder_id in (%s)",implode(",",$tmp));
			}
			else
				$sql = sprintf("select s.*, j.sequence from stores s, stores_by_folder j where s.deleted = 0 and s.enabled = 1 and s.published = 1 and j.folder_id = %d and s.id = j.store_id",$module['folder_id']);
		}
		else
			$sql = "select s.*, 0 as sequence from stores s where deleted = 0 and enabled = 1 and published = 1";
		if (array_key_exists('search_group',$module) && $module['search_group'] > 0) {
			$sql .= sprintf(" and s.id in (select store_id from store_by_search_group where folder_id = %d)",$module['search_group']);
		}
		if (array_key_exists('store_list',$module) && count($module['store_list']) > 0) {
			$sql .= sprintf(" and s.id in (%s)",implode(",",$module['store_list']));
		}
		if (strlen($module['where_clause']) > 0)
			$sql .= " and ".$module['where_clause'];
		if ($module['featured'])
			$sql .= " and featured = 1";
		if (strlen($module['sort_by']) > 0)
			$sql .= " order by ".$module['sort_by'];
		else
			$sql .= " order by sequence";
		if (strlen($module['records']) > 0 && $addLimit) {
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
     * @param array $folder
     * @return mixed
     * @throws phpmailerException
     */
	function formatData($data, $folder=array()) {
		if (array_key_exists("id",$folder) && $folder["id"] > 0) $data["folder_id"] = $folder["id"];
		if (!array_key_exists('address_id',$data)) {
			if ($this->hasOption('addressType'))
				$addressType = $this->getOption('addressType');
			else $addressType = $this->fetchScalarTest('select id from code_lookups where type = "storeAddressTypes" and code = "mailingaddress"');
			if ($address = $this->fetchSingleTest('select a.*, p.province_code as provinceCode, p.province, c.country_code as countryCode, c.country as countryName from addresses a left join provinces p on p.id = a.province_id left join countries c on c.id = a.country_id where a.ownertype="store" and a.ownerid = %d and a.addresstype = %d',$data['id'],$addressType)) {
				$address['address_id'] = $address['id'];
				$address['formattedAddress'] = $address['line1'];
				if (strlen($address['line2']) > 0) $address['formattedAddress'] .= '<br/>'.$address['line2'];
				if (strlen($address['city']) > 0 || strlen($address['provinceCode']) > 0 || $address['postalcode']) {
					$address['formattedAddress'] .= '<br/>'.$address['city'].' '.$address['postalcode'].' '.$address['province'];
				}
				unset($address['id']);
				$data = array_merge($data,$address);
			}
		}
		for ($i = 1; $i < 5; $i++) {
			if (strlen($data['image'.$i]) > 0) {
				$tmp = new image();
				$tmp->addAttributes(array('src'=>$data['image'.$i],'alt'=>$data['name']));
				$data['img_image'.$i] = $tmp->show();
			}
		}
		if (strlen($data['mapmarker']) > 0) {
			$tmp = new image();
			$tmp->addAttributes(array('src'=>$data['mapmarker'],'alt'=>$data['name']));
			$data['img_mapmarker'] = $tmp->show();
		}
		if ($this->hasOption('dateFormat')) {
			$data['created_fmt'] = date($this->getOption('dateFormat'),strtotime($data['created']));
		}
		$data['url'] = $this->getUrl('store',$data['id'],$data);
		$data['href'] = sprintf('<a href="%s">',$data['url']);
		$data['href_end'] = '</a>';
		$data['escapedDescription'] = str_replace("\r\n","",str_replace('"','&quot;',$data['description']));
		if ($data['latitude'] != 0 && $data['longitude'] != 0)
			$data['storeGPS'] = sprintf('(%.4f,%.4f)',$data['latitude'],$data['longitude']);
		if (strlen($data['website']) > 0)
			$data['websiteLink'] = sprintf('<a href="%s" target="_blank">Visit Website</a>',$data['website']);
		if ($address = $this->fetchSingleTest('select * from addresses where ownertype="store" and ownerid = %d',$data['id'])) {
			$data['address'] = Address::formatAddress($address['id']);
			$data['mapAddress'] = Address::formatAddress($address['id'],'map');
			$data['encodedAddress'] = urlencode($data['mapAddress']);
			$data['viewMap'] = sprintf('<a title="View Map" onclick="map_popup(\'http://maps.google.ca/maps?f=q&amp;q=%s\')" href="#">VIEW MAP</a>',urlencode($data['mapAddress']));
			if (strlen($address['email']) > 0) {
				$data['contact'] = sprintf('<a title="Contact" href="mailto:%s">CONTACT</a>',$address['email']);
			}
			$data['addressName'] = $address['addressname'];
			$data['phone1'] = $address['phone1'];
			$data['email'] = $address['email'];
		}
		$addresses = $this->fetchAllTest('select a.*, c.code from addresses a, code_lookups c where c.id = a.addresstype and ownertype="store" and ownerid = %d',$data['id']);
		foreach($addresses as $address) {
			$address = Address::formatData($address);
			$typ = $address['code']['code'];
			$data[$typ] = $address;	//array();
			$data[$typ]['address'] = Address::formatAddress($address['id']);
			if (strlen($address['email']) > 0) {
				$data[$typ]['contact'] = sprintf('<a title="Contact" href="mailto:%s">CONTACT</a>',$address['email']);
			}
		}
		$this->logMessage(__FUNCTION__,sprintf('return data [%s]',print_r($data,true)),3);
		return $data;
	}

    /**
     * @param $data
     * @return mixed
     * @throws phpmailerException
     */
	public function formatFolder($data) {
		if (array_key_exists('image',$data) && strlen($data['image']) > 0) {
			$tmp = new image();
			$tmp->addAttributes(array('src'=>$data['image'],'alt'=>$data['title']));
			$data['img_image'] = $tmp->show();
		}
		$data['url'] = $this->getUrl('storecat',$data["id"],$data);
		return $data;
	}

    /**
     * @return array|mixed|string|string[]
     * @throws phpmailerException
     */
	public function storeListing() {
		if (!$module = parent::getModule())
			return "";
		$this->parseOptions($module['misc_options']);
		$this->logMessage(__FUNCTION__,sprintf("module [%s]",print_r($module,true)),2);
		$sql = $this->buildSql($module,false);
		$pagination = $this->getPagination($sql,$module,$recordCount);

		$records = $this->fetchAllTest($sql);
		$this->logMessage(__FUNCTION__,sprintf("sql [%s] count [%d]",$sql,count($records)),2);
		$stores = array();
		$frm = new Forms();
		$frm->init($this->m_dir.$module['inner_html']);
		$ct = 0;
		$outer = new Forms();
		$fldr = array();
		if ($module['folder_id'] != 0) {
			if ($fldr = $this->fetchSingleTest('select * from store_folders where id = %d',$this->m_module['folder_id']))
				$outer->addData($this->formatFolder($fldr));
		}
		if ($this->hasOption('grpPrefix')) $stores[] = $this->getOption('grpPrefix');
		foreach($records as $rec) {
			$frm->reset();
			$ct += 1;
			if ($module['rows'] > 0 && $ct > $module['columns']) {
				$ct = 1;
				if ($this->hasOption('grpSuffix')) $stores[] = $this->getOption('grpSuffix');
				if ($this->hasOption('grpPrefix')) 
					$stores[] = $this->getOption('grpPrefix');
				else
					$stores[] = '<div class="clearfix"></div>';
			}
			$frm->addTag('sequence',$ct);
			if ($module['folder_id'] != 0) $rec['folder_id'] = $module['folder_id'];
			$frm->addData($this->formatData($rec,$fldr));
			$subdata = $this->subForms($this->m_module['fetemplate_id'],null,array('store_id'=>$rec['id']),'inner');
			$this->logMessage(__FUNCTION__,sprintf('subforms [%s]',print_r($subdata,true)),3);
			foreach($subdata as $key=>$value) {
				$frm->addTag($key,$value,false);
			}
			$stores[] = $frm->show();
		}
		if ($this->hasOption('grpSuffix')) $stores[] = $this->getOption('grpSuffix');
		$outer->init($this->m_dir.$module['outer_html']);
		$outer->addTag('stores',implode('',$stores),false);
		$outer->addTag("pagination",$pagination,false);
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
	function listing() {
		if (!$module = parent::getModule())
			return "";
		$this->parseOptions($module['misc_options']);
		$this->logMessage(__FUNCTION__,sprintf("module [%s]",print_r($module,true)),2);
		$sql = $this->buildSql($module);
		if ($module['folder_id'] != 0)
			$root = $this->fetchSingleTest('select * from store_folders where id = %d',$module['folder_id']);
		else
			$root = array('left_id'=>0,'right_id'=>999999,'level'=>0,'id'=>0,'title'=>'');
		$menu = sprintf('<ul class="level_0 %s" %s>%s</ul>',
			$this->hasOption('ul_class') ? $this->getOption('ul_class'):'',
			$this->hasOption('ul_id') ? sprintf('id="%s"',$this->getOption('ul_id')):'',
			$this->buildUL($root,$module,0));
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
		$this->logMessage(__FUNCTION__,sprintf("return [%s]",$tmp),3);
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
		$this->logMessage(__FUNCTION__,sprintf("root [%d] level [%d] root_level [%d]",$root['id'],$root['level'],$root_level),2);
		if ($this->hasOption('maxLevel') && $root_level >= $this->getOption('maxLevel')) {
			$this->logMessage(__FUNCTION__,sprintf('max level exceeded',$this->getOption('maxLevel')),2);
			return "";
		}
		$level = $this->fetchAllTest('select * from store_folders where level = %d and left_id > %d and right_id < %d and enabled = 1 order by left_id',$root['level']+1,$root['left_id'],$root['right_id']);
		$form = new Forms();
		$form->init($this->m_dir.$module['inner_html']);
		$menu = array();
		$seq = 0;
		foreach($level as $key=>$item) {
			$seq += 1;
			$form->reset();
			$form->addData($this->formatFolder($item));
			$form->addTag('sequence',$seq);
			$form->addTag('level',$root_level+1);
			$subdata = $this->subForms($this->m_module['fetemplate_id'],null,array('folder_id'=>$item['id']),'inner');
			$this->logMessage(__FUNCTION__,sprintf('subforms [%s]',print_r($subdata,true)),3);
			foreach($subdata as $key=>$value) {
				$form->addTag($key,$value,false);
			}
			if (($subMenu = $this->buildUL($item,$module,$root_level+1)) != '') {
				$subnav = sprintf('<ul class="level_%d submenu">%s</ul>',$root_level+1,$subMenu);
				$hasSubnav = true;
				$form->addTag("hassubnav","hassubnav");
			}
			else {
				$hasSubnav = false;
				$subnav = "";
				$form->addTag("hassubnav","");
			}
			$tmp = $form->show().$subnav;
			if ($this->hasOption('delim') && $seq < count($level)) $tmp .= sprintf('<span class="delim">%s</span>',$this->getOption('delim'));
			$active = $this->m_folderId == $item['id'] ? 'active':'';
			$menu[] = sprintf('<li class="sequence_%d %s %s">%s</li>',$seq,$active,$hasSubnav?"hassubnav":"",$tmp);
		}
		$this->logMessage(__FUNCTION__,sprintf("return [%s]",print_r($menu,true)),3);
		return implode("",$menu);
	}

    /**
     * @return array|mixed|string|string[]
     * @throws phpmailerException
     */
	function search() {
		if (!$module = parent::getModule())
			return "";
		$this->parseOptions($module['misc_options']);
		$this->logMessage(__FUNCTION__,sprintf("module [%s]",print_r($module,true)),2);
		$outer = new Forms();
		$outer->setModule($module);
		$outer->init($this->m_dir.$module['outer_html']);
		$flds = $outer->buildForm($this->config->getFields($module['configuration']));
		$outer->setData('searchCount',0);
		$stores = array();
		if ($this->hasOption('showNoSubmit') && $this->getOption('showNoSubmit') == 0)
			if (count($_POST) == 0 || !array_key_exists('storeSearch',$_POST)) {
				$this->logMessage(__FUNCTION__,'abandon - no params and not to be shown',1);
				return "";
			}
		if (count($_POST) > 0 && array_key_exists('storeSearch',$_POST)) {
			$outer->addData($_POST);
			$valid = $outer->validate();
			$byGPS = false;
			if ($valid) {
				$cityOrPostalCode = $outer->getData('cityOrPostalCode');
				if (strlen($cityOrPostalCode) > 0) {
					if (preg_match('/[A-Z][0-9][A-Z]/',substr(strtoupper($cityOrPostalCode),0,3))) {
						$byPCode = substr(strtoupper($cityOrPostalCode),0,3);
						$byCityName = '';
						$_POST['postalCode'] = $byPCode;
					}
					else {
						$byCityName = $cityOrPostalCode;
						$_POST['cityOrName'] = $byCityName;
						$byPCode = '';
					}
					$outer->addData($_POST);
				}
				else {
					$byCityName = $outer->getData('cityOrName');
					$byPCode = $outer->getData('postalCode');
				}
				if ($outer->getData("lat") != 0.0 && $outer->getData("lng") != 0.0) {
					$byCityName = "";
					$byPCode = "";
					$byGPS = true;
				}
				if (strlen($byCityName) == 0 && strlen($byPCode) == 0 && !$byGPS) {
					$valid = false;
					$outer->addFormError('You must enter a City or Name, a Postal Code or GPS Coordinates');
				}
				else {
					if (strlen($byPCode) > 0) {
						if (strlen($byPCode) < 3) {
							$valid = false;
							$outer->addFormError('Postal Code must be at least 3 characters');
						}
						else {
							$byPCode = strtoupper(substr($byPCode,0,3));
							$ct = $this->fetchScalarTest('select count(0) from pcodes where fsa = "%s"',$byPCode);
							if ($ct < 1) {
								$valid = false;
								$outer->addFormError('Invalid Postal Code entered - '.$byPCode);
							}
						}
					}
					if (strlen($byCityName) > 0) {
						$byCityName = str_replace('.','',$byCityName);
						$byCityName = str_replace("'","",$byCityName);
						$byCityName = str_replace('"','',$byCityName);
					}
					$this->logMessage(__FUNCTION__,sprintf('city/name [%s] postal code [%s]',$byCityName,$byPCode),2);
				}
			}
			if ($valid) {
				if ($byPCode) {
					$fsa = $this->fetchScalarAllTest('select to_pcode from pcode_by_distance where from_pcode = "%s" and distance <= %d order by distance',$byPCode,$outer->getData('resultDistance'));
				}
				$sql = 'select s.* from stores s, addresses a where a.ownertype="store" and a.ownerid = s.id and s.deleted = 0 and s.enabled = 1 and s.published = 1 ';
				if ($byCityName) {
					$searchParams[] = sprintf('City or name: '.$_POST['cityOrName']);
					$sql .= sprintf(' and (%s REGEXP "%s" or %s REGEXP "%s")',
						'replace(replace(replace(name,\'"\',""),"\'",""),".","")',$byCityName,
						'replace(replace(replace(city,\'"\',""),"\'",""),".","")',$byCityName);
				}
				if ($byPCode) {
					$searchParams[] = sprintf('Closest to '.$byPCode);
					$searchParams[] = sprintf(' within %dKm',$outer->getData('resultDistance'));
					$sql .= sprintf(' and substring(a.postalcode,1,3) in ("%s")',implode('","',$fsa));
					$sql .= sprintf(' order by locate(substring(a.postalcode,1,3),"|%s|")',implode('|',$fsa));
				}
				else if ($byCityName) {
					$sql .= sprintf(' order by instr(replace(replace(replace(name,\'"\',""),"\'",""),".",""),"%s"), instr(replace(replace(replace(city,\'"\',""),"\'",""),".",""),"%s")',$byCityName,$byCityName);
				}
				else if ($byGPS) {
					$lat = $outer->getData("lat");
					$lng = $outer->getData("lng");
					$searchParams[] = sprintf('GPS : (%0.5f,%0.5f)',$lat,$lng);
					$sql = sprintf("select s.*, 
		6371*SQRT(POW(RADIANS(longitude - %f) * COS((RADIANS(latitude) + RADIANS(%f))/2),2) + POW(RADIANS(latitude - %f),2)) as distance 
from stores s where s.deleted = 0 and s.enabled = 1 and s.published = 1 and s.latitude != 0 and s.longitude != 0 and 
6371*SQRT(POW(RADIANS(longitude - %f) * COS((RADIANS(latitude) + RADIANS(%f))/2),2) + POW(RADIANS(latitude - %f),2)) <= %d
ORDER BY 6371*SQRT(POW(RADIANS(longitude - %f) * COS((RADIANS(latitude) + RADIANS(%f))/2),2)  
+ POW(RADIANS(latitude - %f),2)) ",$lng,$lat,$lat,$lng,$lat,$lat,$outer->getData('resultDistance'),$lng,$lat,$lat);
				}
				if ($this->hasOption("unpaged"))
					$module['maxUnpaged'] = $outer->getData('resultCount');
				if ($module['limit'] > 0) {
					$pagination =  $this->getPagination($sql,$module,$recordcount);
					$this->logMessage(__FUNCTION__,sprintf('returned recordcount [%d] sql [%s]',$recordcount,$sql),1);
				}
				else {
					$pagination =  '';
					$recordcount = 0;
				}
				$searchParams[] = sprintf('up to %d stores',$outer->getData('resultCount'));
				$results = $this->fetchAllTest($sql);
				$outer->addTag('searchParams','Searching for: '.implode(' ',$searchParams),false);
				$outer->addTag('searchCount',$recordcount);
				$outer->addTag('pagination',$pagination,false);
				$this->logMessage(__FUNCTION__,sprintf('sql [%s], count [%d]',$sql,count($results)),2);
				$inner = new Forms();
				$inner->init($this->m_dir.$module['inner_html']);
				$seq = 0;
				foreach($results as $store) {
					$inner->addData($this->formatData($store));
					$seq += 1;
					$inner->addTag('sequence',$seq);
					$stores[] = $inner->show();
				}
			}
		}
		$outer->addTag('stores',implode('',$stores),false);
		$tmp = $outer->show();
		$this->logMessage(__FUNCTION__,sprintf("return [%s]",$tmp),3);
		return $tmp;	
	}

    /**
     * @return array|mixed|string|string[]|void
     * @throws phpmailerException
     */
	function details() {
		if (!$module = $this->getModule())
			return "";
		$this->parseOptions($module['misc_options']);
		$this->logMessage(__FUNCTION__,sprintf("module [%s]",print_r($module,true)),2);
		$outer = new Forms();
		$outer->init($this->m_dir.$module['outer_html']);
		if ($this->m_storeId > 0) {
			if ($module['folder_id'] != 0) {
				$fldr = $this->fetchSingleTest('select * from store_folders where id = %d',$module['folder_id']);
				$outer->addData($this->formatFolder($fldr));
				if ($module['include_subfolders'] != 0) {
					$sql = sprintf('select sf.id from store_folders sf, store_folders sf1 where sf1.id = %d and sf.left_id >= sf1.left_id and sf.right_id <= sf1.right_id and sf.enabled = 1',$module['folder_id']);
					$tmp = array_merge(array(0),$this->fetchScalarAllTest($sql));
					$sql = sprintf("select s.*, j.sequence from stores s, stores_by_folder j where s.deleted = 0 and s.enabled = 1 and s.published = 1 and j.store_id = s.id and j.folder_id in (%s)",implode(",",$tmp));
					$sql = sprintf('select s.* from stores s where s.id = %d and s.deleted = 0 and s.published = 1 and s.enabled = 1 and exists (select 1 from stores_by_folder f where f.store_id = s.id and f.folder_id in (%s))',$this->m_storeId,implode(",",$tmp));
				}
				else {
					$sql = sprintf('select s.* from stores s where s.id = %d and s.deleted = 0 and s.published = 1 and s.enabled = 1 and exists (select 1 from stores_by_folder f where f.store_id = s.id and f.folder_id = %d)',$this->m_storeId,$module['folder_id']);
				}
			}
			else {
				$sql = sprintf('select * from stores where id = %d and deleted = 0 and published = 1 and enabled = 1',$this->m_storeId);
			}
			if ($store = $this->fetchSingleTest($sql)) {
				$inner = new Forms();
				$inner->init($this->m_dir.$module['inner_html']);
				if ($module['folder_id'] != 0) $store['folder_id'] = $module['folder_id'];
				$inner->addData($this->formatData($store));
				foreach($this->getOptions() as $key=>$value) {
					$inner->addTag($key,$value,false);
				}
				$subdata = $this->subForms($this->m_module['fetemplate_id'],null,array('folder_id'=>$module['folder_id']),'inner');
				$this->logMessage(__FUNCTION__,sprintf('inner subforms [%s]',print_r($subdata,true)),3);
				foreach($subdata as $key=>$value) {
					$inner->addTag($key,$value,false);
				}
				$outer->addTag('stores',$inner->show(),false);
				$subdata = $this->subForms($this->m_module['fetemplate_id'],null,array('folder_id'=>$module['folder_id']),'outer');
				$this->logMessage(__FUNCTION__,sprintf('outer subforms [%s]',print_r($subdata,true)),3);
				foreach($subdata as $key=>$value) {
					$outer->addTag($key,$value,false);
				}
				$tmp = $outer->show();
				return $tmp;
			}
		}
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
			case 'calendar':
				$module['id'] = $this->getOption('templateId');
				$html = $this->calendar($fn);
				break;
			case 'members':
				$module['id'] = $this->getOption('templateId');
				$html = $this->members($fn);
				break;
			case 'gallery':
				$module['id'] = $this->getOption('templateId');
				$html = $this->gallery($fn);
				break;
			case 'coupons':
				$module['id'] = $this->getOption('templateId');
				$html = $this->coupons($fn);
				break;
			default:
				$html = '';
		}
		return $html;
	}

    /**
     * @param $module
     * @return string|void
     * @throws phpmailerException
     */
	function calendar($module) {
		$this->logMessage(__FUNCTION__,sprintf('parms [%s]',print_r($module,true)),2);
		if ($folders = $this->fetchSingleTest('select * from relations where owner_type = "store" and owner_id = %d and related_type = "eventfolder"',$this->m_storeId)) {
			$module['folder_id'] = $folders['related_id'];
		}
		else {
			$folders = array();
			$this->logMessage(__FUNCTION__,sprintf('bail - no calendar for store %d',$this->m_storeId),1);
		}
		if ($items = $this->fetchScalarAllTest('select related_id as event_id from relations where owner_type = "store" and owner_id = %d and related_type = "event"',$this->m_storeId))
			$module['event_list'] = $items;
		else {
			$items = array();
			$this->logMessage(__FUNCTION__,sprintf('bail - no events for store %d',$this->m_storeId),2);
		}
		if (count($folders) == 0 && count($items) == 0) {
			$this->logMessage(__FUNCTION__,sprintf('bail - no events or folders for store %d',$this->m_storeId),1);
			return "";
		}
		$obj = new calendar($module['fetemplate_id'],$module);
		if (method_exists('calendar',$module['module_function'])) {
			$this->logMessage(__FUNCTION__,sprintf('invoking calendar with [%s]',print_r($module,true)),2);
			$html = $obj->{$module['module_function']}();
			return $html;
		}
		else {
			$this->logMessage(__FUNCTION__,sprintf('bail - no function [%s] in calendar for store %d',$module['module_function'],$this->m_storeId),1,true);
		}
	}

    /**
     * @param $module
     * @return string|void
     * @throws phpmailerException
     */
	function members($module) {
		$this->logMessage(__FUNCTION__,sprintf('parms [%s]',print_r($module,true)),2);
		if ($folders = $this->fetchSingleTest('select * from relations where owner_type = "store" and owner_id = %d and related_type = "eventfolder"',$this->m_storeId))
			$module['folder_id'] = $folders['related_id'];
		else {
			$this->logMessage(__FUNCTION__,sprintf('bail - no calendar for store %d',$this->m_storeId),1);
			return "";
		}
		$obj = new members($module['fetemplate_id'],$module);
		if (method_exists('members',$module['module_function'])) {
			$this->logMessage(__FUNCTION__,sprintf('invoking members with [%s]',print_r($module,true)),2);
			$html = $obj->{$module['module_function']}();
			return $html;
		}
		else {
			$this->logMessage(__FUNCTION__,sprintf('bail - no function [%s] in members for store %d',$module['module_function'],$this->m_storeId),1,true);
		}
	}

    /**
     * @param $module
     * @return string|void
     * @throws phpmailerException
     */
	function gallery($module) {
		$this->logMessage(__FUNCTION__,sprintf('parms [%s]',print_r($module,true)),2);
		if ($folders = $this->fetchSingleTest('select * from relations where owner_type = "store" and owner_id = %d and related_type = "galleryfolder"',$this->m_storeId))
			$module['folder_id'] = $folders['related_id'];
		else {
			$this->logMessage(__FUNCTION__,sprintf('bail - no gallery for store %d',$this->m_storeId),1);
			return "";
		}
		$obj = new gallery($module['fetemplate_id'],$module);
		if (method_exists('gallery',$module['module_function'])) {
			$this->logMessage(__FUNCTION__,sprintf('invoking gallery with [%s]',print_r($module,true)),2);
			$html = $obj->{$module['module_function']}();
			return $html;
		}
		else {
			$this->logMessage(__FUNCTION__,sprintf('bail - no function [%s] in gallery for store %d',$module['module_function'],$this->m_storeId),1,true);
		}
	}

    /**
     * @param $module
     * @return string|void
     * @throws phpmailerException
     */
	function coupons($module) {
		$this->logMessage(__FUNCTION__,sprintf('parms [%s]',print_r($module,true)),2);
		if ($folders = $this->fetchSingleTest('select * from relations where owner_type = "store" and owner_id = %d and related_type = "couponfolder"',$this->m_storeId)) {
			$module['folder_id'] = $folders['related_id'];
		}
		else {
			$folders = array();
			$this->logMessage(__FUNCTION__,sprintf('bail - no calendar for store %d',$this->m_storeId),1);
		}
		if ($items = $this->fetchScalarAllTest('select related_id as coupon_id from relations where owner_type = "store" and owner_id = %d and related_type = "coupon"',$this->m_storeId))
			$module['event_list'] = $items;
		else {
			$items = array();
			$this->logMessage(__FUNCTION__,sprintf('bail - no events for store %d',$this->m_storeId),2);
		}
		if (count($folders) == 0 && count($items) == 0) {
			$this->logMessage(__FUNCTION__,sprintf('bail - no items or folders for store %d',$this->m_storeId),1);
			return "";
		}
		$obj = new calendar($module['fetemplate_id'],$module);
		if (method_exists('coupons',$module['module_function'])) {
			$this->logMessage(__FUNCTION__,sprintf('invoking coupons with [%s]',print_r($module,true)),2);
			$html = $obj->{$module['module_function']}();
			return $html;
		}
		else {
			$this->logMessage(__FUNCTION__,sprintf('bail - no function [%s] in coupons for store %d',$module['module_function'],$this->m_storeId),1,true);
		}
	}

    /**
     * @return array|mixed|string|string[]
     * @throws phpmailerException
     */
	public function options() {
		if (!$module = parent::getModule()) return "";
		if ($this->m_storeId == 0) {
			$this->logMessage(__FUNCTION__,sprintf("options requested for unknown store [%s] module [%s]",print_r($_REQUEST,true),print_r($modulemtrue)),1,true);
			return "";
		}
		$store = $this->fetchSingleTest("select * from stores where id = %d",$this->m_storeId);
		$outer = new Forms();
		$outer->init($this->m_dir.$module['outer_html']);
		$outer->addData($this->formatData($store));
		$inner = new Forms();
		$inner->init($this->m_dir.$module['inner_html']);
		$result = array();
		$ct = 0;
		$opts = $this->fetchAllTest("select c.* from code_lookups c, store_options s where s.store_id = %d and c.id = s.option_id order by %s",
			$this->m_storeId, strlen($module["sort_by"]) > 0 ? $module["sort_by"] : "c.sort");
		foreach($opts as $key=>$option) {
			$inner->reset();
			$inner->addData($option);
			$result[] = $inner->show();
		}
		$outer->addTag("options",implode("",$result),false);
		return $outer->show();
	}

    /**
     * @return false|string
     * @throws phpmailerException
     */
	function getModuleInfo() {
		return parent::getModuleList(array('storeListing','single','listing','search','details','itemRelations','options'));
	}

}

?>
