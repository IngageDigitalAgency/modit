<?php

/**
 * Gallery feature for frontend
 */
class gallery extends Frontend {

    /**
     * @param $id
     * @param $module
     * @throws phpmailerException
     */
    public function __construct($id, $module = array()) {
		parent::__construct();
		$this->m_dir = ADMIN.'frontend/forms/gallery/';
		$this->m_moduleId = $id;
		$this->m_module = $module;
		$this->logMessage("__construct",sprintf("($id,[%s])",print_r($module,true)),2);
		$this->image_id = array_key_exists('image_id',$_REQUEST) ? $_REQUEST['image_id'] : 0;
	}

    /**
     * @return array|false
     * @throws phpmailerException
     */
    function getModule() {
		$module = parent::getModule();
		if (array_key_exists("image_id",$module)) $this->image_id = $module["image_id"];
		return $module;
	}

    /**
     * @param $data
     * @return mixed
     * @throws phpmailerException
     */
    function formatData($data) {
		$tmp = new image();
		$tmp->addAttributes(array('src'=>$data['image'],'alt'=>htmlentities(strip_tags($data['title']))));
		$data['img_image'] = $tmp->show();
		foreach($GLOBALS['gallery'] as $key=>$info) {
			$data['img_image'.'_'.$info['dir']] = str_replace('originals',$info['dir'],$data['img_image']);
			$data['image'.'_'.$info['dir']] = str_replace('originals',$info['dir'],$data['image']);
		}
		$this->logMessage(__FUNCTION__,sprintf("data [%s]", print_r($data,true)), 3);
		return $data;
	}

    /**
     * @param $module
     * @param $addLimit
     * @return string
     * @throws phpmailerException
     */
    private function buildSql($module, $addLimit = false) {
		if (array_key_exists('unpublished',$module) && $module['unpublished'] == 1)
			$published = ' 1=1 ';
		else
			$published = ' i.enabled = 1 and i.published = 1 ';
		if ($module['folder_id'] > 0) {
			if ($module['include_subfolders'] != 0) {
				$sql = sprintf('select i.id from gallery_folders i, gallery_folders i1 where i1.id = %d and i.left_id >= i1.left_id and i.right_id <= i1.right_id and i.enabled = 1',$module['folder_id']);
				$tmp = $this->fetchScalarAll($sql);
				$this->logMessage(__FUNCTION__,sprintf('sub folder sql [%s]',$sql),3);
				$sql = sprintf("select i.*, j.sequence, f.left_id, j.folder_id from gallery_images i, gallery_images_by_folder j, gallery_folders f where f.id = j.folder_id and i.enabled = 1 and i.published = 1 and j.folder_id in (%s) and i.id = j.image_id",implode(',',$tmp));
			}
			else
				$sql = sprintf("select i.*, sequence, 0 as left_id, j.folder_id from gallery_images i, gallery_images_by_folder j where %s and j.folder_id = %d and i.id = j.image_id",$published,$module['folder_id']);
		}
		else
			$sql = sprintf("select i.*, 0 as sequence, 0 as left_id, 0 as folder_id from gallery_images i where %s",$published);
		if (strlen($module['where_clause']) > 0)
			$sql .= " and ".$module['where_clause'];
		if (array_key_exists('image_list',$module) && count($module['image_list']) > 0)
			$sql .= sprintf(' and i.id in (%s)',implode(',',$module['image_list']));
		if ($module['featured'])
			$sql .= " and featured = 1";

		if ($this->hasOption('unique')) {
			$pos = strpos($sql,'from');
			$tmp = sprintf('select min(concat(left_id,"|",i.id)) %s group by i.id',substr($sql,$pos-1));
			$test = $this->fetchScalarAll($tmp);
			$this->logMessage(__FUNCTION__,sprintf('unique code produced [%s] with [%s] pos [%s] sql [%s]',print_r($test,true),$tmp,$pos,$sql),2);
			$sql .= sprintf(' and concat(left_id,"|",i.id) in ("%s")',implode($test,'","'));
		}

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
				if ($total > 0)
					$sql .= " limit ".$total;
			}
		}
		$this->logMessage('buildSql',sprintf('sql [%s]',$sql),3);
		return $sql;
	}

    /**
     * @param $data
     * @return mixed
     * @throws phpmailerException
     */
    private function formatFolder($data) {
		$tmp = new image();
		if (array_key_exists('image',$data)) {
			$tmp->addAttributes(array('src'=>$data['image'],'alt'=>htmlentities(strip_tags($data['title']))));
			$data['img_image'] = $tmp->show();
		}
		if (array_key_exists('rollover_image',$data)) {
			$tmp->addAttributes(array('src'=>$data['rollover_image'],'alt'=>htmlentities(strip_tags($data['title']))));
			$data['img_rollover_image'] = $tmp->show();
		}
		$data['url'] = $this->getUrl('gallery',$data['id'],$data);
		return $data;
	}

    /**
     * @return array|mixed|string|string[]
     * @throws phpmailerException
     */
    public function images() {
		if (!$module = $this->getModule())
			return "";
		$this->parseOptions($module['misc_options']);
		$this->logMessage("images",sprintf("module [%s]",print_r($module,true)),2);
		$sql = $this->buildSql($module);
		$pagination = $this->getPagination($sql,$module,$recordCount);
		$outer = new Forms();
		$outer->setModule($module);
		$outer->init($this->m_dir.$module['outer_html']);
		if ($module['folder_id'] != 0) {
			$folder = $this->fetchSingle(sprintf('select * from gallery_folders where id = %d',$module['folder_id']));
			$outer->addData($this->formatFolder($folder));
		}
		$images = $this->fetchAll($sql);
		$this->logMessage("images",sprintf("image count[%d] sql[%s]",count($images),$sql),2);
		$return = array();
		$form = new Forms();
		$form->init($this->m_dir.$module['inner_html']);
		$ct = 0;
		if ($this->hasOption('grpPrefix')) $return[] = $this->getOption('grpPrefix');
		$sequence = 0;
		foreach($images as $image) {
			$image["folder"] = $this->formatFolder($folder);
			$ct += 1;
			if ($module['rows'] > 0 && $ct > $module['columns']) {
				$ct = 1;
				if ($this->hasOption('grpSuffix')) $return[] = $this->getOption('grpSuffix');
				if ($this->hasOption('grpPrefix')) 
					$return[] = $this->getOption('grpPrefix');
				else
					$return[] = '<div class="clearfix"></div>';
			}
			if ($sequence == 0) $form->addTag("first","first");
			if ($sequence == (count($images) -1)) $form->addTag("last","last");
			$sequence += 1;
			$form->addTag('sequence',$sequence);
			$form->addTag('counter',$ct);
			$form->addData($this->formatData($image));

			$subdata = $this->subForms($this->m_module['fetemplate_id'],null,array('image_id'=>$image['id']),'inner');
			$this->logMessage(__FUNCTION__,sprintf('inner subforms [%s]',print_r($subdata,true)),3);
			foreach($subdata as $key=>$value) {
				$form->addTag($key,$value,false);
			}

			$return[] = $form->show();
		}
		if ($this->hasOption('grpSuffix')) $return[] = $this->getOption('grpSuffix');
		$outer->addTag('pagination',$pagination,false);	
		$outer->addTag('images',implode('',$return),false);

		$subdata = $this->subForms($this->m_module['fetemplate_id'],null,array('folder_id'=>$module['folder_id']),'outer');
		$this->logMessage(__FUNCTION__,sprintf('outer subforms [%s]',print_r($subdata,true)),3);
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
		if (!$module = $this->getModule())
			return "";
		$this->parseOptions($module['misc_options']);
		$this->logMessage("listing",sprintf("module [%s]",print_r($module,true)),2);
		$sql = $this->buildSql($module);
		if ($module['folder_id'] != 0)
			$root = $this->fetchSingle(sprintf('select * from gallery_folders where id = %d',$module['folder_id']));
		else
			$root = array('left_id'=>0,'right_id'=>999999,'level'=>0,'id'=>0);
		if ($this->hasOption('typeSelect')) {
			$folders = $this->fetchAll(sprintf('select * from gallery_folders where left_id > %d and right_id < %d and level > %d order by left_id',$root['left_id'],$root['right_id'],$root['level']));
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
			$menu = sprintf('<ul class="level_0">%s</ul>',$this->buildUL($root,$module,0));
		}
		$outer = new Forms();
		$outer->setModule($module);
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
		$level = $this->fetchAll(sprintf('select * from gallery_folders where level = %d and left_id > %d and right_id < %d and enabled = 1 order by left_id',$root['level']+1,$root['left_id'],$root['right_id']));
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
			$menu[] = sprintf('<li class="sequence_%d">%s</li>',$seq,$tmp);
		}
		$this->logMessage("buildUL",sprintf("return [%s]",print_r($menu,true)),3);
		return implode("",$menu);
	}

    /**
     * @return array|mixed|string|string[]
     * @throws phpmailerException
     */
    function divListing() {
		if (!$module = $this->getModule())
			return "";
		$this->parseOptions($module['misc_options']);
		$this->logMessage(__FUNCTION__,sprintf("module [%s]",print_r($module,true)),2);
		$sql = $this->buildSql($module);
		if ($module['folder_id'] != 0)
			$root = $this->fetchSingle(sprintf('select * from gallery_folders where id = %d',$module['folder_id']));
		else
			$root = array('left_id'=>0,'right_id'=>999999,'level'=>0,'id'=>0);
		$images = $this->buildDiv($root,$module,0);
		$outer = new Forms();
		$outer->setModule($module);
		$outer->init($this->m_dir.$module['outer_html']);
		$outer->addTag('images',$images,false);
		$outer->addData($this->formatFolder($root));
		$tmp = $outer->show();
		return $tmp;
	}

    /**
     * @param $root
     * @param $module
     * @param $root_level
     * @return array|mixed|string|string[]
     * @throws phpmailerException
     */
    function buildDiv($root, $module, $root_level) {
		$this->logMessage(__FUNCTION__,sprintf("root [%d] root_level [%d]",$root['id'],$root_level),1);
		if ($this->hasOption('maxLevel') && $root_level >= $this->getOption('maxLevel')) {
			$this->logMessage(__FUNCTION__,sprintf('max level exceeded',$this->getOption('maxLevel')),2);
			return "";
		}
		$sort = strlen($module["sort_by"]) > 0 ? $module["sort_by"] : "left_id";
		$limit = $module["limit"] > 0 ? $module["columns"] : 9999;
		$level = $this->fetchAll(sprintf('select * from gallery_folders where level = %d and left_id > %d and right_id < %d and enabled = 1  order by %s limit %d',$root['level']+1,$root['left_id'],$root['right_id'], $sort, $limit));
		$outer = new Forms();
		//
		//	root is formatted in the caller
		//
		if ($root_level > 0) {
			$outer->init($this->m_dir.$module['parm1']);
		}
		else {
			$outer->init($this->m_dir.$module['parm2']);
		}
		$inner = new Forms();
		$inner->init($this->m_dir.$module['inner_html']);
		$outer->addData($this->formatFolder($root));
		$outer->addTag('level',$root_level+1);
		$sql = $this->buildSql($module,true);
		$images = $this->fetchAll($sql);
		$this->logMessage(__FUNCTION__,sprintf('sql [%s] found [%d] records',$sql,count($images)),2);
		$result = array();
		$seq = 0;
		foreach($images as $key=>$image) {
			$inner->reset();
			$inner->addData($this->formatData($image));
			$inner->addTag('sequence',$seq);
			$inner->addTag('sequence_hr',$seq+1);
			$inner->addTag('level',$root_level+1);
			$result[] = $inner->show();
			$seq += 1;
		}
		$tmp = implode('',$result);
		$outer->addTag('images',$tmp,false);
		$seq = 0;
		$menu = array();
		foreach($level as $key=>$item) {
			$seq += 1;
			$module['folder_id'] = $item['id'];
			$menu[] = $this->buildDiv($item,$module,$root_level+1);
		}
		$outer->addTag('subfolder',implode('',$menu),false);
		//if (count($images) == 0)
		//	$menu = implode('',$menu);
		//else
			$menu = $outer->show();
		$this->logMessage(__FUNCTION__,sprintf("return [%s]",print_r($menu,true)),4);
		return $menu;
	}

    /**
     * @return array|false|mixed|string|string[]
     * @throws phpmailerException
     */
    function upload() {
		if (!$module = $this->getModule())
			return "";
		$outer = new Forms();
		$outer->setModule($module);
		$outer->init($this->m_dir.$module['outer_html']);
		$flds = $outer->buildForm($this->config->getFields($module['configuration']));
		if (count($_REQUEST) > 0 && array_key_exists('mediaUpload',$_REQUEST)) {
			$outer->addData($_POST);
			$status = $outer->validate();
			if ($status) {
				foreach($flds as $key=>$field) {
					if ($field['type'] == 'fileupload') {
						if (!$this->hasOption('MimeUploadTypes')) {
							$this->logMessage('contactUs',sprintf('file upload is enabled with no file types module [%s]',print_r($module,true)),1,true);
							$status = false;
							$messages = array(0=>'An error occurred. The webmaster has been notified');
						}
						else
							$status = $this->processUploadedFiles(explode('|',$this->getOption('MimeUploadTypes')),$files,$messages);
						if (!$status) {
							foreach($messages as $key=>$value) {
								$outer->addFormError($value);
							}
						}
						else {
							foreach($files as $key=>$value) {
								$outer->setData($key,$value);
							}
						}				
					}
				}
			}
			if ($status) {
				$outer->addFormSuccess('Photo uploaded');
				$stmt = $this->prepare('insert into gallery_images(description,image,created,published,enabled) values(?,?,now(),0,0)');
				$file = $outer->getData('attachment');
				$tmp = explode('/',$file['name']);
				$tmp = sprintf('/images/gallery/originals/%s',$tmp[count($tmp)-1]);
				$ffilename = '.'.$file['name'];
				$ttmp = '.'.$tmp;
				$status = $status && rename(realpath($ffilename),realpath($ttmp));
				$status = $status && $stmt->bindParams(array('ss',sprintf('<p>%s</p>',nl2br($outer->getData('message'))),$tmp));
				if ($stmt->execute() && $module['folder_id'] > 0) {
					$id = $this->insertId();
					$stmt = $this->prepare('insert into gallery_images_by_folder(image_id,folder_id) values(?,?)');
					$stmt->bindParams(array('dd',$id,$module['folder_id']));
					$status = $status && $stmt->execute();
				}
				if ($status) {
					$data = $this->fetchSingle(sprintf('select * from gallery_images where id = %d',$id));
					$data['description'] = strip_tags(str_replace('<br/>',' ',$data['description']));
					$data['title-text'] = $data['title'];
					$data['description'] = str_replace('"',"'",$data['description']);
					$data['description'] = str_replace("\r\n","",$data['description']);
					$data['description'] = str_replace("\n","",$data['description']);
					$data['description'] = str_replace("\r","",$data['description']);
					if ($status && strlen($module['parm1']) > 0)
						$outer->init($this->m_dir.$module['parm1']);
					$outer->addData($data);
				}
			}
			else
				$outer->addFormError('Form validation failed');
		}
		if ($this->isAjax())
			return $this->ajaxReturn(array('status'=>true,'html'=>$outer->show()));
		else
			return $outer->show();
	}

    /**
     * @return array|mixed|string|string[]
     * @throws phpmailerException
     */
    function single() {
		if (!$module = $this->getModule())
			return "";
		$outer = new Forms();
		$outer->setModule($module);
		$outer->init($this->m_dir.$module['outer_html']);
		$flds = $outer->buildForm($this->config->getFields($module['configuration']));
		$module['image_list'] = array($this->image_id);
		$sql = $this->buildSql($module,false);
		if ($images = $this->fetchAll($sql)) {
			$inner = new Forms();
			$inner->init($this->m_dir.$module['inner_html']);
			$result = array();
			foreach($images as $key=>$value) {
				$inner->reset();
				$inner->addData($this->formatData($value));
				$result[] = $inner->show();
			}
			$outer->addTag('images',implode('',$result),false);
		}
		if ($module['folder_id'] > 0 && $folder = $this->fetchSingle(sprintf('select * from gallery_folders where id = %d',$module['folder_id'])))
			$outer->addData($this->formatFolder($folder));
		return $outer->show();
	}

    /**
     * @return array|mixed|string|string[]
     * @throws phpmailerException
     */
    function memberOf() {
		if (!$module = $this->getModule())
			return "";
		$outer = new Forms();
		$outer->setModule($module);
		$outer->init($this->m_dir.$module['outer_html']);
		if ($this->image_id > 0) {
			$folders = $this->fetchAll(sprintf("select * from gallery_folders f where f.id in (select folder_id from gallery_images_by_folder where image_id = %d) and f.enabled = 1 order by left_id",$this->image_id));
		}
		elseif ($module["folder_id"] > 0) {
			$root = $this->fetchSingle(sprintf("select * from gallery_folders where id = %d",$module["folder_id"]));
			$folders = $this->fetchAll(sprintf("select * from gallery_folders f where left_id > %d and right_id < %d and enabled = 1 order by left_id",$root["left_id"],$root["right_id"]));
		}
		else $folders = array();
		$recs = array();
		$inner = new Forms();
		$inner->init($this->m_dir.$module['inner_html']);
		$flds = $inner->buildForm($this->config->getFields($module['configuration']));
		foreach($folders as $key=>$folder) {
			$inner->reset();
			$inner->addData($this->formatFolder($folder));
			$recs[] = $inner->show();
		}
		$outer->addTag("listing",implode("",$recs),false);
		return $outer->show();
	}

    /**
     * @return false|string
     * @throws phpmailerException
     */
    function getModuleInfo() {
		return parent::getModuleList(array('single','images','listing','upload','divListing','memberOf'));
	}
}

?>
