<?php

/**
 * Menu feature for frontend
 */
class menu extends Frontend {

	private $m_dir = '';

    /**
     * @param $id
     * @param array $module
     * @throws phpmailerException
     */
    public function __construct($id, $module = array()) {
		parent::__construct();
		$this->m_dir = ADMIN.'frontend/forms/menu/';
		$this->m_moduleId = $id;
		$this->m_module = $module;
		$this->page = preg_replace('#[^a-z0-9-]#i', '_', strtolower(array_key_exists('page',$_REQUEST) ? $_REQUEST['page'] : ''));
		$this->logMessage("__construct",sprintf("($id,[%s])",print_r($module,true)),2);
	}

    /**
     * @param $data
     * @return mixed
     * @throws phpmailerException
     */
    private function formatData($data) {
		$tmp = new image();
		$tmp->addAttributes(array('src'=>$data['image'],'alt'=>htmlentities($data['title'])));
		$data['img_image'] = $tmp->show();
		$tmp->addAttributes(array('src'=>$data['rollover_image'],'alt'=>htmlentities($data['title'])));
		$data['img_rollover'] = $tmp->show();
		$data['url'] = $this->getUrl('menu',$data['id'],$data);
		$data['active'] = $data['search_title'] == $this->getPageName();
		if (!$this->nullImage($data['image']) && !$this->nullImage($data['rollover_image'])) {
			if ($data['active']) {
				$tmp->addAttributes(array('src'=>$data['rollover_image'],'alt'=>htmlentities($data['title']),
						'onmouseover'=>sprintf("this.src='%s'",$data['image']), 
						'onmouseout'=>sprintf("this.src='%s'",$data['rollover_image'])));
				$data['img_image'] = $tmp->show();
				$swp = $data['rollover_image'];
				$data['rollover_image'] = $data['image'];
				$data['image'] = $swp;
			}
			else {
				$tmp->addAttributes(array('src'=>$data['image'],'alt'=>htmlentities($data['title']),
						'onmouseover'=>sprintf("this.src='%s'",$data['rollover_image']), 
						'onmouseout'=>sprintf("this.src='%s'",$data['image'])));
				$data['img_image'] = $tmp->show();
			}
		}
		if (strlen($data['external_link']) != 0) {
			$data['url'] = $data['external_link'];
		}
		if ($data['internal_link'] != 0) {
			$data['url'] = $this->getUrl('menu',$data['internal_link'],$data);
		}
		$data['href'] = sprintf('<a href="%s" %s>',$data['url'],$data['new_window'] ? 'target="_blank"':'');
		$data['href_end'] = '</a>';
		if ($data['new_window']) $data['target'] = 'target="_blank"';
		$data['active'] = $data['active'] ? 'active' : '';
		$this->logMessage("formatData",sprintf("return [%s]",print_r($data,true)),4);
		return $data;
	}

    /**
     * @param $data
     * @param array $module
     * @return mixed
     * @throws phpmailerException
     */
    private function formatFolder($data, $module=array()) {
		if (array_key_exists('image',$data) && strlen($data['image']) > 0) {
			$tmp = new image();
			$tmp->addAttributes(array('src'=>$data['image'],'alt'=>htmlentities(strip_tags($data['title']))));
			$data['img_image'] = $tmp->show();
		}
		if (array_key_exists('rollover_image',$data) && strlen($data['rollover_image']) > 0) {
			$tmp = new image();
			$tmp->addAttributes(array('src'=>$data['rollover_image'],'alt'=>htmlentities(strip_tags($data['title']))));
			$data['img_rollover_image'] = $tmp->show();
		}
		$breadcrumbs = array();
		$breadcrumbs[] = sprintf('<a href="%s">%s</a>',$this->getUrl('menu',$data['id'],$data),htmlspecialchars($data['title']));
		$tmp = $data;
		$ct = 0;
		while(($tmp = $this->fetchSingleTest('select * from content where level = %d and left_id <= %d and right_id >= %d',$tmp['level']-1,$tmp['left_id'],$tmp['right_id'])) && $ct < 10) {
			$breadcrumbs[] = sprintf('<a href="%s">%s</a>',$this->getUrl('menu',$tmp['id'],$tmp),htmlspecialchars($tmp['title']));
			$ct++;
		}
		if ($this->hasOption('breadcrumbs')) {
			$this->logMessage('formatFolder',sprintf('truncating breadcrumbs as per config [%d]',$this->getOption('breadcrumbs')),2);
			if ($this->getOption('breadcrumbs') < 0) $breadcrumbs = array_slice($breadcrumbs,0,$this->getOption('breadcrumbs'));
		}
		$data['breadcrumbs'] = implode('&nbsp;>>&nbsp;',array_reverse($breadcrumbs));
		$this->logMessage("formatFolder",sprintf("return [%s]",print_r($data,true)),4);
		return $data;
	}

    /**
     * @return array|mixed|string|string[]
     * @throws phpmailerException
     */
    public function ul_nav() {
		if (!$module = parent::getModule())
			return "";
		$this->parseOptions($module['misc_options']);
		if ($module['folder_id'] != 0) {
			$root = $this->fetchSingleTest('select * from content where id = %d',$module['folder_id']);
			if (!is_array($root)) {
				$this->logMessage('ul_nav',sprintf('A folder seems to have been deleted, module [%s]',print_r($module,true)),1,true);
				$root = array('left_id'=>0,'right_id'=>0,'level'=>0,'id'=>0,'title'=>'');
			}
		}
		else
			$root = array('left_id'=>0,'right_id'=>999999,'level'=>0,'id'=>0,'title'=>'');
		$menu = sprintf('<ul class="level_0 %s" %s>%s</ul>',
			$this->hasOption('ul_class') ? $this->getOption('ul_class'):'',
			$this->hasOption('ul_id') ? sprintf('id="%s"',$this->getOption('ul_id')):'',
			$this->buildUL($root,$module,0));
		$outer = new Forms();
		$outer->init($this->m_dir.$module['outer_html']);
		$subdata = $this->subForms($module['fetemplate_id'],'',array(),'outer');
		foreach($subdata as $key=>$value) {
			$outer->addTag($key,$value,false);
		}
		$outer->addTag('menu',$menu,false);
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
    private function buildUL($root, $module, $root_level) {
		$this->logMessage("buildUL",sprintf("root [%d] root_level [%d]",$root['id'],$root_level),2);
		if ($this->hasOption('maxLevel') && $root_level >= $this->getOption('maxLevel')) {
			$this->logMessage('buildUl',sprintf('max level exceeded',$this->getOption('maxLevel')),2);
			return "";
		}
		if ($this->hasOption('maxLevel') && $root_level <= $this->getOption('maxLevel') - 1 && !$this->hasOption('showFolders')) {
			$this->logMessage('buildUl','skipping folders as per config',1);
			$level = $this->fetchAllTest('select * from content where level = %d and left_id > %d and right_id < %d and enabled = 1 and published = 1 and type != "folder" order by left_id',$root['level']+1,$root['left_id'],$root['right_id']);
		}
		else
			$level = $this->fetchAllTest('select * from content where level = %d and left_id > %d and right_id < %d and enabled = 1 and published = 1 order by left_id',$root['level']+1,$root['left_id'],$root['right_id']);
		$form = new Forms();
		$form->init($this->m_dir.$module['inner_html']);
		$menu = array();
		$seq = 0;
		foreach($level as $key=>$item) {
			$seq += 1;
			$item = $this->formatData($item);
			$form->reset();
			$form->addData($item);
			$form->addTag('sequence',$seq);
			$form->addTag('level',$root_level+1);
			$subdata = $this->subForms($module['fetemplate_id'],'',array(),'inner');
			foreach($subdata as $key=>$value) {
				$form->addTag($key,$value,false);
			}
			$tmp = $form->show();
			$hasSubmenu = false;
			if ($item['type'] == 'folder' && $this->hasOption('megamenu')) {
				$this->logMessage(__FUNCTION__,sprintf("mega menu inserted here"),1);
					//
					//	this folder is a placeholder to the underlying mega menu. render the sub page & insert it here.
					//
					$sql = sprintf('select * from content where left_id > %d and right_id < %d and level = %d', $item['left_id'], $item['right_id'], $item['level']+1);
					$subnav = $this->fetchAllTest($sql);
					if (count($subnav) != 1) {
						$this->logMessage(__FUNCTION__,sprintf("invalid mega menu folder found sql [%s] from item [%s]", $sql, print_r($item,true)),1,true);
					}
					else {

						$sql = sprintf('select c.*, t.html, p.id as page_id, p.content from pages p, content c, templates t where t.deleted = 0 and t.enabled = 1 and c.id = %d and p.version = (select max(version) from pages p2 where p2.deleted = 0 and p2.content_id = p.content_id) and c.id = p.content_id and t.template_id = p.template_id and t.version = (select max(version) from templates t1 where t1.template_id = p.template_id)',$subnav[0]['id']);
						$page = $this->fetchSingleTest($sql);
						$c = new Frontend();
						$subMenu = $c->renderPage($page,false);
						$subMenu = $this->extractTag($subMenu,array(0=>'/<body(.*?)>(.*)<\/body>/si'),false,true);
						$tmp .= sprintf('<ul class="level_%d submenu"><li class="sequence_1">%s</li></ul>',$root_level+1,$subMenu);
						$hasSubmenu = true;
					}
			}
			else {
				if (($subMenu = $this->buildUL($item,$module,$root_level+1)) != '') {
					$tmp .= sprintf('<ul class="level_%d submenu">%s</ul>',$root_level+1,$subMenu);
					$hasSubmenu = true;
				}
			}
			if ($this->hasOption('delim') && $seq < count($level)) 
				$tmp .= sprintf('<span class="delim">%s</span>',$this->getOption('delim'));
			if (strlen($module['parm1']) > 0) {
				$li = new Forms();
				$li->init($this->m_dir.$module['parm1']);
				$li->addData(array_merge(array('delim'=>$this->getOption('delim'),
					'span'=>$tmp,'sequence'=>$seq,'hasSubmenu'=>$hasSubmenu ? 'hasSubmenu':'',$tmp,'item'=>$tmp), $item));
				$menu[] = $li->show();
			}
			else
				$menu[] = sprintf('<li class="sequence_%d %s %s %s">%s</li>',$seq,$item['active'],$item['icon_class'],$hasSubmenu ? 'hasSubmenu':'',$tmp);
		}
		$this->logMessage("buildUL",sprintf("return [%s]",print_r($menu,true)),3);
		return implode("",$menu);
	}

    /**
     * @return array|mixed|string|string[]
     * @throws phpmailerException
     */
    public function div_nav() {
		if (!$module = parent::getModule())
			return "";
		$this->parseOptions($module['misc_options']);
		if ($module['folder_id'] != 0)
			$root = $this->fetchSingleTest('select * from content where id = %d',$module['folder_id']);
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
		$outer->addTag('menu',$menu,false);
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
		$this->logMessage("buildDiv",sprintf("root [%d] root_level [%d]",$root['id'],$root_level),2);
		if ($this->hasOption('maxLevel') && $root_level >= $this->getOption('maxLevel')) {
			$this->logMessage(__FUNCTION__,sprintf('max level exceeded',$this->getOption('maxLevel')),2);
			return "";
		}
		if ($this->hasOption('maxLevel') && $root_level <= $this->getOption('maxLevel') - 1 && !$this->hasOption('showFolders')) {
			$this->logMessage(__FUNCTION__,'skipping folders as per config',1);
			$level = $this->fetchAllTest('select * from content where level = %d and left_id > %d and right_id < %d and enabled = 1 and published = 1 and type != "folder" order by left_id',$root['level']+1,$root['left_id'],$root['right_id']);
		}
		else
			$level = $this->fetchAllTest('select * from content where level = %d and left_id > %d and right_id < %d and enabled = 1 and published = 1 order by left_id',$root['level']+1,$root['left_id'],$root['right_id']);
		$form = new Forms();
		$form->init($this->m_dir.$module['inner_html']);
		$menu = array();
		$seq = 0;
		foreach($level as $key=>$item) {
			if ($seq == 0) $form->addTag("first","first");
			if ($seq == (count($level) - 1)) $form->addTag("last","last");
			$seq += 1;
			$form->addData($this->formatData($item));
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
     * @return array|false|mixed|string|string[]
     * @throws phpmailerException
     */
    function pageInfo() {
		if (!$module = parent::getModule()) return "";
		$outer = new Forms();
		$outer->init($this->m_dir.$module['outer_html']);
		if ($data = $this->fetchSingleTest('select c.* from content c, pages p where p.id = %d and c.id = p.content_id',$module['page_id'])) {
			$outer->addData($this->formatData($data));
			$subdata = $this->subForms($module['fetemplate_id'],'',array(),'outer');
			foreach($subdata as $key=>$value) {
				$outer->addTag($key,$value,false);
			}
		}
		if ($this->isAjax()) {
			return $this->ajaxReturn(array('status'=>true,'html'=>$outer->show()));
		}
		else return $outer->show();
	}

    /**
     * @return false|string
     * @throws phpmailerException
     */
    function getModuleInfo() {
		return parent::getModuleList(array('ul_nav','div_nav','pageInfo'));
	}

}

?>
