<?php

class product extends Frontend {

	public function __construct($id,$module = array()) {
		parent::__construct();
		$this->m_dir = ADMIN.'frontend/forms/product/';
		$this->m_moduleId = $id;
		$this->m_module = $module;
		$this->m_productId = array_key_exists('prod_id',$_REQUEST) ? $_REQUEST['prod_id'] : 0;
		$this->logMessage("__construct",sprintf("($id,[%s])",print_r($module,true)),2);
	}

	function getModule() {
		$module = parent::getModule();
		if ($this->hasOption('buyUrl') && array_key_exists('addToCart',$_REQUEST)) {
			$this->logMessage('getModule',sprintf('redirecting after purchase to [%s]',$this->getOption('buyUrl')),1);			
			$allowedUrls = $this->getOptions();
			$url = $this->getOption('buyUrl');
			if (in_array($url, $allowedUrls, true)) {
				header('Location: '.$url);
			} else {						
				header('Location: '.$_SERVER['REQUEST_URI']);
			}
			return "";
		}
		//
		//	not sure if this should be template_allow_override or allow_override. process_overrides should handle it and i think allow_oveeride is correct
		//
		if (array_key_exists('cat_id',$_REQUEST) && $module['template_allow_override']) {
			$this->logMessage('getModule',sprintf('overriding module folder from [%s] to [%s] as per config',$module['folder_id'],$_REQUEST['cat_id']),2);
			$module['folder_id'] = $_REQUEST['cat_id'];
		}
		return $module;
	}
	
	private function buildSql($module,$addLimit = false) {
		//
		//	handled by allowOverride in config now
		//
		//if (array_key_exists('pf_id',$_REQUEST)) $module['folder_id'] = $_REQUEST['pf_id'];	
		if ($module['folder_id'] > 0) {
			if ($module['include_subfolders'] != 0) {
				$sql = sprintf('select p.id from product_folders p, product_folders p1 where p1.id = %d and p.left_id >= p1.left_id and p.right_id <= p1.right_id and p.enabled = 1',$module['folder_id']);
				$tmp = array_merge(array(0),$this->fetchScalarAll($sql));
				$this->logMessage('buildSql',sprintf('sub folder sql [%s]',$sql),3);
				$sql = sprintf("select p.*, j.sequence, f.left_id, j.folder_id from product p, product_by_folder j, product_folders f where f.id = j.folder_id and p.deleted = 0 and p.enabled = 1 and p.published = 1 and j.folder_id in (%s) and p.id = j.product_id",implode(',',$tmp));
			}
			else
				$sql = sprintf("select p.*, j.sequence, f.left_id, j.folder_id from product p, product_by_folder j, product_folders f where f.id = j.folder_id and p.deleted = 0 and p.enabled = 1 and p.published = 1 and j.folder_id = %d and p.id = j.product_id",$module['folder_id']);
		}
		else
			$sql = "select p.*, 0 as sequence, 0 as left_id, 0 as folder_id from product p where p.deleted = 0 and p.enabled = 1 and p.published = 1";
		if (array_key_exists('search_group',$module) && $module['search_group'] > 0) {
			$sql .= sprintf(" and e.id in (select product_id from product_by_search_group where folder_id = %d)",$module['search_group']);
		}
		if (array_key_exists('product_list',$module) && count($module['product_list']) > 0)
			$sql .= sprintf(' and p.id in (%s)',implode(',',$module['product_list']));
		if (strlen($module['where_clause']) > 0)
			$sql .= " and ".$module['where_clause'];
		if ($module['featured'])
			$sql .= " and featured = 1";

		if ($this->hasOption('unique')) {
			$pos = strpos($sql,'from');
			$tmp = sprintf('select min(concat(left_id,"|",p.id)) %s group by p.id',substr($sql,$pos-1));
			$test = $this->fetchScalarAll($tmp);
			$this->logMessage(__FUNCTION__,sprintf('unique code produced [%s] with [%s] pos [%s] sql [%s]',print_r($test,true),$tmp,$pos,$sql),2);
			$sql .= sprintf(' and concat(left_id,"|",p.id) in ("%s")',implode($test,'","'));
		}

		if (strlen($module['sort_by']) > 0) {
			if (strpos($module['sort_by'],'price') !== false) {
				$sql = str_replace("folder_id from","folder_id, min(pr.price) as price from",$sql);
				$ct = 1;
				$sql = str_replace(" product p, "," product p left join product_pricing pr on pr.product_id = p.id, ",$sql, $ct);
				$sql .= " group by p.id ";
			}
			$sql .= " order by ".$module['sort_by'];
		}
		else
			$sql .= " order by left_id, sequence";
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

	function formatOption($data,$checked = false) {
		$data['teaser'] = htmlspecialchars($data['teaser']);
		$data['description'] = htmlspecialchars($data['description']);
		$radio = new radiobutton();
		$radio->addAttributes(array('name'=>'options_id','value'=>$data['id']));
		if ($checked)
			$radio->addAttribute('checked','checked');
		$data['radio'] = $radio->show();
		$this->logMessage(__FUNCTION__,sprintf("return [%s]",print_r($data,true)),1);
		return $data;
	}

	static function formatData($data,$getPricing = true) {
		$c = new Common();
		for($x = 1; $x < 5; $x++) {
			if (strlen($data['image'.$x]) > 0) {
				$tmp = new image();
				$tmp->addAttributes(array('src'=>$data['image'.$x],'alt'=>htmlentities(strip_tags($data['name']))));
				$data['img_image'.$x] = $tmp->show();
				foreach($GLOBALS['product'] as $key=>$info) {
					$data['img_image'.$x.'_'.$info['dir']] = str_replace('originals',$info['dir'],$data['img_image'.$x]);
					$data['image'.$x.'_'.$info['dir']] = str_replace('originals',$info['dir'],$data['image'.$x]);
				}
			}
		}
		$data['url'] = $c->getUrl('product',$data['id'],$data);
		$data['href'] = sprintf('<a href="%s">',$data['url']);
		$data['href_end'] = '</a>';
		if ($getPricing && $pricing = $c->fetchSingle(sprintf('select * from product_pricing where product_id = %d order by min_quantity limit 1',$data['id']))) {
			$data['onSale'] = 0;
			$pricing['price'] = round($pricing['price'],2);
			$pricing['sale_price'] = round($pricing['sale_price'],2);
			$pricing['shipping'] = round($pricing['shipping'],2);
			$data['price'] = $pricing['price'];
			$data['pricing'] = $pricing;
			$data['regularPrice'] = $data['price'];
			$data['regularPriceFormatted'] = $c->my_money_format($data['regularPrice']);
			if ($data['sale_startdate'] <= date('Y-m-d H:i:s') && $data['sale_enddate'] > date('Y-m-d H:i:s')) {
				$data['onSale'] = 1;
				$data['saleStart'] = date(GLOBAL_DEFAULT_DATETIME_FORMAT,strtotime($data['sale_startdate']));
				$data['saleEnd'] = date(GLOBAL_DEFAULT_DATETIME_FORMAT,strtotime($data['sale_enddate']));
				$data['regularPrice'] = $pricing['price'];
				$data['price'] = $pricing['sale_price'];
				$data['saleDiscount'] = $data['regularPrice'] - $data['price'];
				$data['saleDiscountFormatted'] = $c->my_money_format($data['saleDiscount']);
				$data['salePercentFormatted'] = round(100*(1-($data['price']/$data['regularPrice'])),1);
				$data['regularPriceFormatted'] = $c->my_money_format($data['regularPrice']);
			}
		}
		else {
			$data['price'] = 0;
		}
		$opts = $c->fetchAll(sprintf('select * from product_options where deleted = 0 and product_id = %d order by sequence',$data['id']));
		$frm = new Forms();
		$select = new select();
		$select->addAttributes(array('name'=>'options_id','value'=>array_key_exists('options_id',$data) ? $data['options_id'] : 0));
		foreach($opts as $key=>$opt) {
			if ($data['onSale'])
				$select->addOption($opt['id'],sprintf('%s : %s',$opt['teaser'],$c->my_money_format(round($opt['qty_multiplier']*($opt['sale_price']+$data['price']),2))));
			else
				$select->addOption($opt['id'],sprintf('%s : %s',$opt['teaser'],$c->my_money_format($opt['qty_multiplier']*(round($opt['price']+$data['price'],2)))));
		}
		$data['options'] = $select->show();
		$data['optionsRadio'] = array();
		foreach($opts as $key=>$opt) {
			$radio = new radiobutton();
			if (array_key_exists('options_id',$data))
				$optKey = $data['options_id'];
			else {
				$optKey = $key == 0 ? $opt['id'] : 0;
			}
			$radio->addAttributes(array('name'=>'options_id','value'=>$opt['id']));
			if ($optKey == $opt['id'])
				$radio->addAttribute('checked','checked');
			$data['optionsRadio'.$key] = sprintf(sprintf('<span class="radio">%s</span>&nbsp;<span class="teaser">%s</span>&nbsp;<span class="price">%s</span>',$radio->show(),$opt['teaser'],$this->my_money_format(round($opt['price']+$data['price'],2))));
			$data['optionsRadio'][] = sprintf(sprintf('<span class="radio">%s</span>&nbsp;<span class="teaser">%s</span>&nbsp;<span class="price">%s</span>',$radio->show(),$opt['teaser'],$this->my_money_format(round($opt['price']+$data['price'],2))));
		}
		$data['price_fmt'] = $c->my_money_format($data['price']);
		if (array_key_exists('qty_multiplier',$data))
			$data['extendedPrice'] = round($data['price']*$data['qty_multiplier'],2);
		else
			$data['extendedPrice'] = round($data['price'],2);
		$data['extendedPriceFormatted'] = $c->my_money_format($data['extendedPrice']);
		if (array_key_exists('color',$data)) {
			$data['colorName'] = $c->fetchScalar(sprintf('select value from code_lookups where id = %d',$data['color']));
		}
		if (array_key_exists('size',$data)) {
			$data['sizeName'] = $c->fetchScalar(sprintf('select value from code_lookups where id = %d',$data['size']));
		}
		if (array_key_exists('shipping',$data))
			$data['shipping_fmt'] = $c->my_money_format(round($data['shipping'],2));
		if ($data['status'] > 0 && $status = $c->fetchSingle(sprintf('select * from code_lookups where type="product_status" and id=%d',$data['status']))) {
			$status['state'] = $c->depairOptions($status['extra']);
			$data['inventoryState'] = $status;
		}
		else {
			$data['inventoryState'] = array('state'=>array('inventoryStatus'=>1));
		}
		$invAmt = $c->fetchSingle(sprintf("select * from product_inventory where product_id = %d and options_id = 0 and color = 0 and size = 0 and (start_date = '0000-00-00' or start_date <= CURDATE()) and (end_date = '0000-00-00' or end_date >= CURDATE())",$data["id"]));
		$data["availability"] = -1;
		if (is_array($invAmt) && count($invAmt) > 0) {
			if ($invAmt["quantity"] <= 0) {
				$status = $c->fetchSingle(sprintf('select * from code_lookups where type="product_status" and extra like "%%inventoryState:%d%%"',$data['status'],PRODUCT_OUT_OF_STOCK));
				$status['state'] = $c->depairOptions($status['extra']);
				$data['inventoryState'] = $state;
			} else {
			 	$data["availability"] = $invAmt["quantity"];
			}
		}
		$tmp = $data;
		unset($tmp['attachment_content']);
		if ($video = $c->fetchSingle(sprintf('select * from videos where owner_type="product" and owner_id = %d', $data['id']))) {
			$data['video'] = $video;
		}
		$reviews = $c->fetchSingle(sprintf("select count(product_id) as ct, avg(rating) as avg from product_reviews where product_id = %d and approved = 1 group by product_id",$data["id"]));
		if (is_array($reviews)) {
			$data["reviews"] = array_key_exists("ct",$reviews) ? $reviews["ct"] : 0;
			$data["rating"] = array_key_exists("avg",$reviews) ? (int)($reviews["avg"]*2) : 0;
		}
		else {
			$data["reviews"] = 0;
			$data["rating"] = 0;
		}
		$data["manufacturer"] = $c->fetchSingle(sprintf("select * from code_lookups where id = %d",$data["manufacturer_id"]));
		$c->logMessage('formatData',sprintf('return data [%s]',print_r($data,true)),3);
		return $data;
	}

	function formatFolder($data,$module = array()) {
		$data['url'] = $this->getUrl('category',$data['id'],$data);
		if (array_key_exists("image",$data) && strlen($data['image']) > 0) {
			$tmp = new image();
			$tmp->addAttributes(array('src'=>$data['image'],'alt'=>htmlentities(strip_tags($data['title']))));
			$data['img_image'] = $tmp->show();
		}
		if (array_key_exists("rollover_image",$data) && strlen($data['rollover_image']) > 0) {
			$tmp = new image();
			$tmp->addAttributes(array('src'=>$data['rollover_image'],'alt'=>htmlentities(strip_tags($data['title']))));
			$data['img_rollover_image'] = $tmp->show();
		}
		if (array_key_exists('folder_id',$module) && $data['id'] == $module['folder_id']) $data['active'] = 'active';
		if (array_key_exists('cat_id',$_REQUEST) && $data['id'] == $_REQUEST['cat_id']) $data['active'] = 'active';
		$level = 1;
		$breadcrumbs = array();
		$breadcrumbs[$level] = sprintf('<a href="%s" class="sequence-~seq~">%s</a>',$this->getUrl('category',$data['id'],$data),htmlspecialchars($data['title']));
		$tmp = $data;
		$ct = 0;
		while(($tmp = $this->fetchSingle(sprintf('select * from product_folders where level = %d and left_id <= %d and right_id >= %d',$tmp['level']-1,$tmp['left_id'],$tmp['right_id']))) && $ct < 10) {
			$level += 1;
			$this->logMessage('formatFolder',sprintf('breadcrumb [%s] folder [%s]',print_r($breadcrumbs,true),print_r($tmp,true)),3);
			$breadcrumbs[$level] = sprintf('<a href="%s" class="sequence-~seq~">%s</a>',$this->getUrl('category',$tmp['id'],$tmp),htmlspecialchars($tmp['title']));
			$ct++;
		}
		if ($this->hasOption('breadcrumbs')) {
			$this->logMessage('formatFolder',sprintf('truncating breadcrumbs as per config [%d]',$this->getOption('breadcrumbs')),2);
			if ($this->getOption('breadcrumbs') < 0) $breadcrumbs = array_slice($breadcrumbs,0,$this->getOption('breadcrumbs'));
		}
		$data['breadcrumbs'] = implode('&nbsp;>>&nbsp;',array_reverse($breadcrumbs));
		$this->logMessage('formatFolder',sprintf('return data [%s]',print_r($data,true)),3);
		return $data;
	}

	function formatReview($data) {
		$data["formattedCreated"] = date(GLOBAL_DEFAULT_DATETIME_FORMAT,strtotime($data['created']));
		return $data;
	}

	function products() {
		if (!$module = $this->getModule())
			return "";
		$outer = new Forms();
		$outer->setModule($module);
		$outer->init($this->m_dir.$module['outer_html']);
		if (count($_POST) > 0 && array_key_exists('productProducts',$_POST)) {
			$outer->addData($_POST);
			if (array_key_exists('sort_by',$_POST) && strlen($_POST['sort_by']) > 0)
				$module['sort_by'] = $_POST['sort_by'];
		}
		$sql = $this->buildSql($module);
		$pagination = $this->getPagination($sql,$module,$recordCount);
		$outer->addTag('recordCount',$recordCount);
		$products = $this->fetchAll($sql);
		$this->logMessage('products',sprintf('sql [%s] count [%d]',$sql,count($products)),2);
		$return = array();
		$frm = new Forms();
		$frm->init($this->m_dir.$module['inner_html']);
		$ct = 0;
		$flds = $this->config->getFields($module['configuration']);
		if ($this->hasOption('grpPrefix')) $return[] = $this->getOption('grpPrefix');
		foreach($products as $key=>$product) {
			if ($product['folder_id'] == 0) $product['folder_id'] = $module['folder_id'];
			$ct += 1;
			if ($module['rows'] > 0 && $ct > $module['columns']) {
				$ct = 1;
				if ($this->hasOption('grpSuffix')) $return[] = $this->getOption('grpSuffix');
				if ($this->hasOption('grpPrefix')) 
					$return[] = $this->getOption('grpPrefix');
				else
					$return[] = '<div class="clearfix"></div>';
			}
			$frm->reset();
			$flds = $frm->buildForm($flds);
			$frm->addTag('sequence',$ct);
			if ($product['folder_id'] > 0 && $fldr = $this->fetchSingle(sprintf('select * from product_folders where id = %d and enabled = 1',$product['folder_id']))) {
				$product['folder'] = $this->formatFolder($fldr);
			}
			$frm->addData($this->formatData($product));
			$subdata = $this->subForms($this->m_module['fetemplate_id'],null,array('folder_id'=>$module['folder_id'],'product_id'=>$product['id']),'inner');
			$this->logMessage(__FUNCTION__,sprintf('subforms [%s]',print_r($subdata,true)),3);
			foreach($subdata as $key=>$value) {
				$frm->addTag($key,$value,false);
			}
			$return[] = $frm->show();
		}
		if ($this->hasOption('grpSuffix')) $return[] = $this->getOption('grpSuffix');
		$outer->addTag('pagination',$pagination,false);
		if ($module['folder_id'] != 0) {
			if ($fldr = $this->fetchSingle(sprintf('select * from product_folders where id = %d',$module['folder_id'])))
				$outer->addData($this->formatFolder($fldr));
		}
		$outer->addTag('products',implode('',$return),false);
		$subdata = $this->subForms($this->m_module['fetemplate_id'],null,array('folder_id'=>$module['folder_id']),'outer');
		$this->logMessage('products',sprintf('subforms [%s]',print_r($subdata,true)),3);
		foreach($subdata as $key=>$value) {
			$outer->addTag($key,$value,false);
		}
		$tmp = $outer->show();
		return $tmp;
	}
	
	function listing() {
		if (!$module = $this->getModule())
			return "";
		$this->parseOptions($module['misc_options']);
		//$sql = $this->buildSql($module,true);
		if ($module['folder_id'] != 0)
			$root = $this->fetchSingle(sprintf('select * from product_folders where id = %d',$module['folder_id']));
		else
			$root = array('left_id'=>0,'right_id'=>999999,'level'=>0,'id'=>0,'title'=>'','image'=>'','rollover_image'=>'');
		$outer = new Forms();
		$outer->setModule($module);
		$outer->init($this->m_dir.$module['outer_html']);
		$flds = $outer->buildForm($this->config->getFields($module['configuration']));
		if ($this->hasOption('postParam') && array_key_exists($this->getOption('postParam'),$_POST)) {
			$this->logMessage('listing',sprintf('adding post to outer as per config [%s]',$this->getOption('postParam')),2);
			$outer->addData($_POST);
		}
		if ($this->hasOption('typeSelect')) {
			$menu = $this->buildOpt($root,$module,0);
		}
		else {
			$menu = sprintf('<ul class="level_0 %s">%s</ul>',$this->getOption("ul_class"),$this->buildUL($root,$module,0));
		}
		$outer->addTag('listing',$menu,false);
		$subdata = $this->subForms($module['fetemplate_id'],null,array(),'outer');
		$this->logMessage('listing',sprintf('outer subforms [%s]',print_r($subdata,true)),4);
		foreach($subdata as $key=>$value) {
			$outer->addTag($key,$value,false);
		}
		$outer->addData($this->formatFolder($root));
		$tmp = $outer->show();
		return $tmp;
	}

	private function buildOpt($root,$module,$root_level) {
		$this->logMessage(__FUNCTION__,sprintf('root [%s] level [%s]',print_r($root,true),$root_level),4);
		$folders = $this->fetchAll(sprintf('select * from product_folders where left_id > %d and right_id < %d and level = %d order by left_id',$root['left_id'],$root['right_id'],$root['level']+1));
		$form = new Forms();
		$form->init($this->m_dir.$module['inner_html']);
		$result = array();
		foreach($folders as $key=>$folder) {
			if ($folder['internal_link'] != 0) {
				$sql = sprintf('select * from product_folders where id = %d and enabled = 1',$folder['internal_link']);
				$this->logMessage('listing',sprintf('superceding folder [%s] with [%s] sql [%s]',$folder['id'],$folder['internal_link'],$sql),1);
				$folder = $this->fetchSingle($sql);
			}
			//$level = $folder['level'] - $root['level'];
			$spacer = str_repeat('&nbsp;',$root_level*2);
			$form->reset();
			$folder = $this->formatFolder($folder,$module);
			$form->addData($folder);
			$form->addTag('level',$root_level);
			$form->addTag('spacer',$spacer,false);
			if (array_key_exists('f_id',$_REQUEST))
				$form->addTag('selected', $folder['id'] == $_REQUEST['f_id'] ? 'selected':'');
			elseif (array_key_exists('cat_id',$_REQUEST)) {
				$this->logMessage('listing',sprintf('selected test [%s] vs [%s]',$folder['id'],$_REQUEST['cat_id']),1);
				$form->addTag('selected', $folder['id'] == $_REQUEST['cat_id'] ? 'selected':'');
			}
			$result[] = $form->show();
			$tmp = $this->buildOpt($folder,$module,$root_level+1);
			if (strlen($tmp) > 0) $result[] = $tmp;
		}
		$this->logMessage(__FUNCTION__,sprintf('return [%s]',print_r($result,true)),4);
		return implode("",$result);
	}

	private function buildUL($root,$module,$root_level) {
		$this->logMessage("buildUL",sprintf("root [%d] root_level [%d]",$root['id'],$root_level),2);
		if ($this->hasOption('maxLevel') && $root_level >= $this->getOption('maxLevel')) {
			$this->logMessage('buildUL',sprintf('max level exceeded',$this->getOption('maxLevel')),2);
			return "";
		}
		if (array_key_exists('internal_link',$root) && $root['internal_link'] > 0) {
			$this->logMessage(__FUNCTION__,sprintf("replacing folder from internal link"),1);
			$root = $this->fetchSingle(sprintf('select * from product_folders where id = %d',$root['internal_link']));
		}
		$level = $this->fetchAll(sprintf('select * from product_folders where level = %d and left_id > %d and right_id < %d and enabled = 1 order by left_id',$root['level']+1,$root['left_id'],$root['right_id']));
		$form = new Forms();
		$form->init($this->m_dir.$module['inner_html']);
		$menu = array();
		$seq = 0;
		foreach($level as $key=>$item) {
			$seq += 1;
			$form->reset();
			$item = $this->formatFolder($item,$module);
			$form->addData($item);
			$form->addTag('sequence',$seq);
			$form->addTag('level',$root_level+1);
			$subdata = $this->subForms($this->m_module['fetemplate_id'],null,array('folder_id'=>$item['internal_link'] > 0 ? $item['internal_link'] : $item['id']),'inner');
			$this->logMessage('buildUL',sprintf('subforms [%s]',print_r($subdata,true)),3);
			foreach($subdata as $key=>$value) {
				$form->addTag($key,$value,false);
			}
			$tmp = $form->show();
			$hasSubmenu = false;
			if (($subMenu = $this->buildUL($item,$module,$root_level+1)) != '') {
				$tmp .= sprintf('<ul class="level_%d submenu">%s</ul>',$root_level+1,$subMenu);
				$hasSubmenu = true;
			}
			if ($this->hasOption('delim') && $seq < count($level)) $tmp .= sprintf('<span class="delim">%s</span>',$this->getOption('delim'));
			if (strlen($module['parm1']) > 0) {
				$li = new Forms();
				$li->init($this->m_dir.$module['parm1']);
				$li->addData(array_merge(array('delim'=>$this->getOption('delim'),
					'span'=>$tmp,'sequence'=>$seq,'hasSubmenu'=>$hasSubmenu ? 'hasSubmenu':'','item'=>$tmp), $item));
				$menu[] = $li->show();
			}
			else
				$menu[] = sprintf('<li class="sequence_%d %s %s">%s</li>',$seq,$hasSubmenu ? 'hasSubmenu':'',array_key_exists('active',$item)?$item['active']:'',$tmp);
		}
		$this->logMessage("buildUL",sprintf("return [%s]",print_r($menu,true)),3);
		return implode("",$menu);
	}
	
	function details() {
		if (!$module = $this->getModule())
			return "";
		$this->parseOptions($module['misc_options']);
		$outer = new Forms();
		$outer->setModule($module);
		$outer->init($this->m_dir.$module['outer_html']);
		if (array_key_exists('prod_id',$_REQUEST) || $this->hasOption('showAlways')) {
			if ($module['folder_id'] != 0) {
				if ($fldr = $this->fetchSingle(sprintf('select * from product_folders where enabled = 1 and id = %d',$module['folder_id']))) {
					$outer->addData($this->formatFolder($fldr));
				}
				if (array_key_exists('prod_id',$_REQUEST))
					$sql = sprintf('select p.* from product p where p.id = %d and p.deleted = 0 and p.published = 1 and p.enabled = 1 and exists (select 1 from product_by_folder f where f.product_id = p.id and f.folder_id = %d)',$_REQUEST['prod_id'],$module['folder_id']);
				else
					$sql = $this->buildSql($module,true);
			}
			else {
				$sql = sprintf('select * from product_folders where enabled = 1 and id in (select folder_id from product_by_folder where product_id = %d) order by rand() limit 1',$this->m_productId);
				if ($fldr = $this->fetchSingle($sql)) {
					$outer->addData($this->formatFolder($fldr));
					$module['folder_id'] = $fldr['id'];
				}
				if (array_key_exists('prod_id',$_REQUEST))
					$sql = sprintf('select * from product where id = %d and deleted = 0 and published = 1 and enabled = 1',$_REQUEST['prod_id']);
				else
					$sql = $this->buildSql($module,true);
			}
			$products = $this->fetchAll($sql);
			$this->logMessage('details',sprintf('sql [%s] found [%d]',$sql,count($products)),2);
			$return = array();
			$inner = new Forms();
			$inner->init($this->m_dir.$module['inner_html']);
			$flds = $this->config->getFields($module['configuration']);
			foreach($products as $product) {
				$product['folder_id'] = $module['folder_id'];
				$inner->reset();
				if (array_key_exists('recurring_period',$flds))
					$flds['recurring_period']['sql'] = sprintf('select r.id, r.teaser from product_recurring r, code_lookups l where r.enabled = 1 and r.published = 1 and r.deleted = 0 and r.product_id = %d and l.id = r.lookup_id order by sequence',$product['id']);
				$flds = $inner->buildForm($flds);
				$subdata = $this->subForms($this->m_module['fetemplate_id'],null,array(),'inner');
				$this->logMessage('details',sprintf('subforms [%s]',print_r($subdata,true)),3);
				foreach($subdata as $key=>$value) {
					$inner->addTag($key,$value,false);
				}
				$inner->addData($this->formatData($product));
				foreach($this->getOptions() as $key=>$value) {
					$inner->addTag($key,$value,false);
				}
				$return[] = $inner->show();
			}
			$subdata = $this->subForms($this->m_module['fetemplate_id'],null,array(),'outer');
			$this->logMessage('details',sprintf('subforms [%s]',print_r($subdata,true)),3);
			foreach($subdata as $key=>$value) {
				$outer->addTag($key,$value,false);
			}
			if (count($return) > 0) {
				$outer->addTag('products',implode('',$return),false);
				$tmp = $outer->show();
			}
			else $tmp = '';
			return $tmp;
		}
	}

	function cartInfo() {
		if (!$module = $this->getModule())
			return "";
		$this->parseOptions($module['misc_options']);
		$templates = array('header'=>$module['parm1'],'body'=>$module['parm2'],'bodyRow'=>$module['parm3'],'total'=>$module['parm4'],'totals'=>$module['parm5']);
		$this->logMessage("cartInfo",sprintf("module [%s] templates[%s]",print_r($module,true),print_r($templates,true)),2);
		$header = new Forms();
		$cart = Ecom::formatCart(array_key_exists('cart',$_SESSION) ? $_SESSION['cart'] : Ecom::initCart());
		$header->init($this->m_dir.$templates['header']);
		$qty = 0;
		$bodyRow = new Forms();
		$body = new Forms();
		if (strlen($templates['body']) > 0) {
			$body->init($this->m_dir.$templates['body']);
			$bodyRow->init($this->m_dir.$templates['bodyRow']);
		}
		$prodRows = array();
		foreach($cart['products'] as $key=>$product) {
			$bodyRow->reset();
			$formatted = $this->formatData($product);
			if (!$this->hasOption('displayOnly')) {
				$flds = $this->config->getFields($module['configuration']);
				if (count($flds) == 0) {
					$form = array(
						'remove'=>array('type'=>'checkbox','name'=>sprintf('removeProduct[%s]',$key),'value'=>1),
						'quantity'=>array('type'=>'textfield','name'=>sprintf('quantity[%s]',$key),'value'=>$product['quantity']),
						'message'=>array('type'=>'textarea','name'=>sprintf('message[%s]',$key),'value'=>$product['message']),
						'color'=>array('type'=>'select','name'=>sprintf('color[%s]',$key),'class'=>'def_field_ddl','value'=>$product['color'],'sql'=>sprintf('select l.id,l.value from code_lookups l, product_options_info poi where l.type = "color" and l.id = poi.type_id and poi.options_type = "color" and poi.options_id = %d order by sort, value',$product['options_id'])),
						'size'=>array('type'=>'select','name'=>sprintf('size[%s]',$key),'class'=>'def_field_ddl','value'=>$product['size'],'sql'=>sprintf('select l.id,l.value from code_lookups l, product_options_info poi where l.type = "size" and l.id = poi.type_id and poi.options_type = "size" and poi.options_id = %d order by sort, value',$product['options_id'])),
						'recurring_period'=>array('type'=>'select','required'=>false,'name'=>sprintf('recurring_period[%s]',$key),'sql'=>sprintf('select r.id, r.teaser from product_recurring r, code_lookups l where r.enabled = 1 and r.published = 1 and r.deleted = 0 and r.product_id = %d and l.id = r.lookup_id order by sequence',$product['id']),'required'=>false,'value'=>$product['recurring_period']),
						'recurring_qty'=>array('type'=>'textfield','required'=>false,'name'=>sprintf('recurring_qty[%s]',$key),'validation'=>'number','value'=>$product['recurring_qty'])
					);
				}
				else {
					if (array_key_exists('recurring_period',$flds))
						if (!array_key_exists('sql',$flds['recurring_period']))
							$flds['recurring_period']['sql'] = sprintf('select r.id, r.teaser from product_recurring r, code_lookups l where r.enabled = 1 and r.published = 1 and r.deleted = 0 and r.product_id = %d and l.id = r.lookup_id order by sequence',$product['id']);
					$form = $flds;
				}
				if ($product['options_id'] == 0) {
					$form['options_id'] = array('type'=>'select','required'=>false,'name'=>sprintf('options_id[%s]',$key),'value'=>$product['options_id'],'sql'=>sprintf('select id,teaser from product_options where product_id = %d and deleted = 0 order by sequence',$product['id']));
				}
				else {
					$form['options_id'] = array('type'=>'select','name'=>sprintf('options_id[%s]',$key),'value'=>$product['options_id'],'sql'=>sprintf('select id,teaser from product_options where product_id = %d and deleted = 0 order by sequence',$product['id']));
				}
				if (array_key_exists('options',$formatted))
					$bodyRow->addTag('formattedOptions',str_replace("options_id",sprintf("options_id[%s]",$key),$formatted['options']));
			}
			else {
				$product['options_id'] = $this->fetchScalar(sprintf('select teaser from product_options where id = %d',$product['options_id']));
				$product['color'] = $this->fetchScalar(sprintf('select value from code_lookups where id = %d',$product['color']));
				$product['size'] = $this->fetchScalar(sprintf('select value from code_lookups where id = %d',$product['size']));
				$product['recurring_period'] = $this->fetchScalar(sprintf('select teaser from product_recurring r where r.id = %d',$product['recurring_period']));
				$form = array();
			}
			$bodyRow->addData($this->formatData($product));
			$qty += $product['quantity'];
			$bodyRow->addTag('key',$key);
			$product['formattedExtended'] = $this->my_money_format($product['price']*$product['quantity']);
			$form = $bodyRow->buildForm($form);
			$prodRows[] = $bodyRow->show();
		}
		$body->addTag('products',implode('',$prodRows),false);
		$totals = new Forms();
		$totals->init($this->m_dir.$templates['total']);
		$tot = array();
		$totals->addTag('description','Sub Total:');
		$totals->addTag('total',$cart['header']['formattedValue']);
		$tot[] = $totals->show();

		if ($cart['header']['redemption_value'] != 0) {
			$totals = new Forms();
			$totals->init($this->m_dir.$templates['total']);
			$totals->addTag('description','Points Redeemed:');
			$totals->addTag('total',$cart['header']['formattedRedemptionValue']);
			$tot[] = $totals->show();
		}

		if ($cart["header"]["shipping"] > 0) {
			$totals->addTag('description','Shipping:');
			$totals->addTag('total',$cart['header']['formattedShipping']);
			$tot[] = $totals->show();
		}
		if (array_key_exists('handling',$cart['header']) && $cart['header']['handling'] != 0) {
			$totals->addTag('description','Handling Fee:');
			$totals->addTag('total',$cart['header']['formattedHandlingFee']);
			$tot[] = $totals->show();
		}
		if (array_key_exists('line_discounts',$cart['header']) && $cart['header']['line_discounts'] != 0) {
			$totals->addTag('description','Item Discount:');
			$totals->addTag('total',$cart['header']['formattedLineDiscounts']);
			$tot[] = $totals->show();
		}
		if (array_key_exists('discount_value',$cart['header']) && $cart['header']['discount_value'] != 0) {
			if ($cart['header']['discount_type'] == 'P')
				$totals->addTag('description',sprintf('Discount (%.02f%%) :',$cart['header']['discount_rate']));
			else
				$totals->addTag('description','Discount:');
			$totals->addTag('total',$cart['header']['formattedDiscountValue']);
			$tot[] = $totals->show();
		}
		if ($cart['header']['taxes'] > .01) {
			$totals->addTag('description','Taxes:');
			$totals->addTag('total',$cart['header']['formattedTaxes']);
			$tot[] = $totals->show();
		}
		$totals->addTag('description','Grand Total:');
		$totals->addTag('total',$cart['header']['formattedTotal']);
		if (count($_POST) > 0 && array_key_exists('updateCart',$_POST)) $totals->addData($_POST);
		$tot[] = $totals->show();

		if ($cart['header']['points_collected'] > 0) {
			$totals->addTag('description','Points Collected:');
			$totals->addTag('total',number_format($cart['header']['points_collected']));
			$tot[] = $totals->show();
		}

		if ($cart['header']['points_redeemed'] > 0) {
			$totals->addTag('description','Points Redeemed:');
			$totals->addTag('total',number_format($cart['header']['points_redeemed']));
			$tot[] = $totals->show();
		}

		$header->addTag('total',$cart['header']['formattedTotal']);
		$header->addTag('quantity',$qty);
		$header->addTag('plural',$qty == 1 ? '' : 's');
		if (count($_POST) > 0 && array_key_exists('updateCart',$_POST)) $header->addData($_POST);
		$outer = new Forms();
		$outer->setModule($module);
		if (count($_POST) > 0 && array_key_exists('updateCart',$_POST)) $outer->addData($_POST);
		$outer->init($this->m_dir.$module['outer_html']);
		$outer->addTag('header',$header->show(),false);
		$outer->addTag('products',$body->show(),false);
		$totals = new Forms();
		$totals->init($this->m_dir.$templates['totals']);
		$totals->addTag('totals',implode('',$tot),false);
		$totals->addData($cart['header']);
		$outer->addTag('totals',$totals->show(),false);
		$cart = Ecom::formatCart($cart);
		$outer->addData($cart['header']);
		$subdata = $this->subForms($this->m_module['fetemplate_id'],null,array('folder_id'=>$module['folder_id']),'outer');
		$this->logMessage('cartInfo',sprintf('subforms [%s]',print_r($subdata,true)),3);
		foreach($subdata as $key=>$value) {
			$outer->addTag($key,$value,false);
		}
		if ($outer->hasTag("ecomErrors"))
			$outer->addTag('ecomErrors',$this->showEcomMessages(),false);
		$tmp = $outer->show();
		return $tmp;
	}

	function shippingAddress() {
		if (!$module = $this->getModule())
			return "";
		$this->parseOptions($module['misc_options']);
		$outer = new Forms();
		$outer->setModule($module);
		$outer->init($this->m_dir.$module['outer_html']);
		if (!array_key_exists('cart',$_SESSION)) $_SESSION['cart'] = Ecom::initCart();
		if ((!array_key_exists('shipping',$_SESSION['cart']['addresses']))  || count($_SESSION['cart']['addresses']['shipping']) == 0)
			if ($this->isLoggedIn())
				$_SESSION['cart']['addresses']['shipping'] = $this->fetchSingle(sprintf('select * from addresses where ownertype = "member" and ownerid = %d and tax_address = 1',$_SESSION['user']['info']['id']));
		if (!array_key_exists('id',$_SESSION['cart']['addresses']['shipping']))
			$this->logMessage('shippingAddress',sprintf('no shipping address found session [%s]',print_r($_SESSION,true)),1);
		$address = Address::formatData($_SESSION['cart']['addresses']['shipping']);
		$address['addressId'] = $address['id'];
		$nonEditFields = $this->config->getFields($module['configuration']);
		$editFields = $this->config->getFields($module['configuration'].'Edit');
		if (count($editFields) == 0) $editField = $nonEditFields;
		if (count($_POST) == 0 || !(array_key_exists('shippingAddressForm',$_POST))) {
			$flds = $nonEditFields;
			$this->logMessage('shippingAddress','using config - nonedit',1);
		}
		else {
			$flds = $editFields;
			$this->logMessage('shippingAddress',sprintf('using config - edit [%s]',print_r($flds,true)),1);
		}
		$flds = $outer->buildForm($flds);
		//$address['addressId'] = $address['id'];	//can't use id directly - mucks up .htaccess
		$outer->addData($address);
		if (count($_POST) > 0 && array_key_exists('shippingAddressForm',$_POST)) {
			if (array_key_exists('saveAddress',$_POST)) {
				$outer->addData($_POST);
				if ($outer->validate()) {
					$values = array();
					foreach($flds as $key=>$value) {
						if (!array_key_exists('database',$value)) {
							$values[$key] = $outer->getData($key);
						}
					}
					//
					//	save the address to the session cart - this is the users default address
					//
					$outer = new Forms();
					$outer->init($this->m_dir.$module['outer_html']);
					$_SESSION['cart']['addresses']['shipping'] = array_merge($this->fetchSingle(sprintf('select * from addresses where ownertype="member" and id = %d',$_POST['addressId'])),$values);
					if (array_key_exists('edit',$nonEditFields)) $flds['edit'] = $nonEditFields['edit'];
					if (array_key_exists('editButton',$nonEditFields)) $flds['editButton'] = $nonEditFields['editButton'];
					$flds = $outer->buildForm($flds);
					$e = new Ecom();
					$e->updateCart();
					$this->logMessage('shippingAddress',sprintf('redirecting back to [%s] after tax updates',$_SERVER['REQUEST_URI']),1);
					$allowedUrls = $this->getOptions();
					$url = $this->getOption('recalcOrder');
					if (in_array($url, $allowedUrls, true)) {						
						header('Location: '.$url);
					}
					else
						header('Location: '.$_SERVER['REQUEST_URI']);	//refresh the screen to reflect the new cart taxes
					exit();
				}
			}
		}
		return $outer->show();
	}

	function billingAddress() {
		if (!$module = $this->getModule())
			return "";
		$this->parseOptions($module['misc_options']);
		$outer = new Forms();
		$outer->setModule($module);
		$outer->init($this->m_dir.$module['outer_html']);
		if (!array_key_exists('cart',$_SESSION)) $_SESSION['cart'] = Ecom::initCart();
		if ((!array_key_exists('billing',$_SESSION['cart']['addresses'])) || count($_SESSION['cart']['addresses']['billing']) == 0)
			if (array_key_exists('member_id',$_SESSION['cart']['header'])) {
				if ($tmp = $this->fetchSingle(sprintf('select * from addresses where ownertype = "member" and ownerid = %d and tax_address = 0',$_SESSION['cart']['header']['member_id'])))
					$_SESSION['cart']['addresses']['billing'] = $tmp;
			}
		if (!array_key_exists('id',$_SESSION['cart']['addresses']['billing']))
			$this->logMessage('billingAddress',sprintf('no billing address found session [%s]',print_r($_SESSION,true)),1);
		$address = Address::formatData($_SESSION['cart']['addresses']['billing']);
		$address['addressId'] = $address['id'];
		$nonEditFields = $this->config->getFields($module['configuration']);
		$editFields = $this->config->getFields($module['configuration'].'Edit');
		if (count($editFields) == 0) $editField = $nonEditFields;
		if (count($_POST) == 0 || !(array_key_exists('billingAddressForm',$_POST))) {
			$flds = $nonEditFields;
			$this->logMessage('billingAddress','using config - nonedit',1);
		}
		else {
			$flds = $editFields;
			$this->logMessage('billingAddress',sprintf('using config - edit [%s]',print_r($flds,true)),1);
		}
		$flds = $outer->buildForm($flds);
		$outer->addData($address);
		if (count($_POST) > 0 && array_key_exists('billingAddressForm',$_POST)) {
			if (array_key_exists('saveAddress',$_POST)) {
				$outer->addData($_POST);
				if ($outer->validate()) {
					$values = array();
					foreach($flds as $key=>$value) {
						if (!array_key_exists('database',$value)) {
							$values[$key] = $outer->getData($key);
						}
					}
					//
					//	save the address to the session cart - this is the users default address
					//
					$outer = new Forms();
					$outer->init($this->m_dir.$module['outer_html']);
					$_SESSION['cart']['addresses']['billing'] = array_merge($_SESSION['cart']['addresses']['billing'],$values);
					if (array_key_exists('edit',$nonEditFields)) $flds['edit'] = $nonEditFields['edit'];
					if (array_key_exists('editButton',$nonEditFields)) $flds['editButton'] = $nonEditFields['editButton'];
					$flds = $outer->buildForm($flds);
					$outer->addData($_POST);
					$e = new Ecom();
					$e->updateCart();
					$this->logMessage('billingAddress',sprintf('redirecting back to [%s] after tax updates',$_SERVER['REQUEST_URI']),1);
					header('Location: '.$_SERVER['REQUEST_URI']);	//refresh the screen to reflect the new cart taxes
					exit();
				}
			}
		}
		return $outer->show();
	}

	function payment() {

//
//	Note: This is being heavily modified for KJV only. The only active functionality to be considered is the PayFlow logic 
//	- moving the authorization results to the new table order_authorization
//	- June 2018

		if (!$module = $this->getModule())
			return "";
		$outer = new Forms();
		$outer->setModule($module);
		$outer->init($this->m_dir.$module['outer_html']);
		if ((!array_key_exists('cart',$_SESSION)) || count($_SESSION['cart']['products']) == 0) {
			if (defined('FRONTEND'))
				if ($this->hasOption('sendTo')) {
					$allowedUrls = $this->getOptions();
					$url = $this->getOption('sendTo');
					if (in_array($url, $allowedUrls, true)) {
						header(sprintf('Location: %s', $url));
					} else {						
						header('Location: '.$_SERVER['REQUEST_URI']);
					}					
					exit;
				}
				else return $outer->show();
		}
		$inner = new Common();
		$inner->parseOptions($module['parm1']);
		$response = new Common();
		$response->parseOptions($module['parm2']);
		$details = new Common();
		$details->parseOptions($module['parm3']);
		$errors = new Common();
		$errors->parseOptions($module['parm4']);
		$nextStep = new Common();
		$nextStep->parseOptions($module['parm5']);
		$formattedCart = Ecom::formatCart($_SESSION['cart']);
		$reauthorizing = array_key_exists('reauthorize',$formattedCart) && $formattedCart['reauthorize']['o_id'] > 0;
		if (count($_REQUEST) > 0) {
			if ($this->hasOption('moneris') && array_key_exists('isMoneris',$_REQUEST)) {
				if (array_key_exists('response_order_id',$_POST)) {
					$order_id = explode('-',$_POST['response_order_id']);
					$return = array();
					foreach($_POST as $key=>$value) {
						$return[] = sprintf('%s = %s',$key,$value);
					}
					$tmp = array('authorization_amount'=>$_POST['charge_total'],
						'authorization_type'=>'Moneris',
						'authorization_code'=>$_POST['bank_approval_code'],
						'authorization_transaction'=>$_POST['transactionKey'],
						'authorization_info'=>implode("\n",$return)
					);
					if ($_POST['response_code'] == '027') {
						$tmp['order_status'] = $this->fetchScalar('select code from code_lookups where type="orderStatus" order by sort limit 1');
					}
					$stmt = $this->prepare(sprintf('update orders set %s where id = %d',implode('=?,',array_keys($tmp)).'=?',$order_id[0]));
					$stmt->bindParams(array_merge(array(str_repeat('s',count($tmp))),array_values($tmp)));
					if ($stmt->execute()) {
						$outer->init($this->m_dir.$response->getOption('moneris'));
						$_SESSION['user']['lastOrder'] = $order_id[0];
						if ($_POST['response_code'] == '027') {
							$this->activateRecurring($order_id[0]);
							$this->config->postSaleProcessing($order_id[0],true,$this);
							unset($_SESSION['cart']);
						}
						else
							$this->config->postSaleProcessing($order_id,false,$this);
						$outer->addData($_REQUEST);
						$outer->addData(array('cart'=>$formattedCart));
						$outer->addTag('ecomErrors',$this->showEcomMessages(),false);
						return $outer->show();
					}
				}
				else $outer->addFormError('There was a problem processing your information');
			}
			if ($this->hasOption('2checkout') && array_key_exists('is2checkout',$_REQUEST)) {
				$return = array();
				foreach($_POST as $key=>$value) {
					$return[] = sprintf('%s = %s',$key,$value);
				}
				$order_id = $_SESSION['cart']['header']['order_id'];
				$tmp = array(
					'authorization_amount'=>$_POST['total'],
					'authorization_type'=>'2Checkout',
					'authorization_code'=>$_POST['key'],
					'authorization_transaction'=>$_POST['invoice_id'],
					'authorization_info'=>implode("\n",$return),
					'order_status'=>$this->fetchScalar('select code from code_lookups where type="orderStatus" order by sort limit 1')
				);
				$stmt = $this->prepare(sprintf('update orders set %s where id = %d',implode('=?,',array_keys($tmp)).'=?',$order_id));
				$stmt->bindParams(array_merge(array(str_repeat('s',count($tmp))),array_values($tmp)));
				if ($stmt->execute()) {
					$_SESSION['user']['lastOrder'] = $order_id;
					$this->activateRecurring($order_id);
					$this->config->postSaleProcessing($order_id,true,$this);
					$outer->init($this->m_dir.$response->getOption('2checkout'));
					$outer->addData($_REQUEST);
					$outer->addData(array('cart'=>$formattedCart));
					unset($_SESSION['cart']);
					$outer->addTag('ecomErrors',$this->showEcomMessages(),false);
					return $outer->show();
				}
			}
			if ($this->hasOption('beanstream') && array_key_exists('ref1',$_REQUEST) && $_REQUEST['ref1'] == 'isBeanstream') {
				$return = array();
				foreach($_REQUEST as $key=>$value) {
					$return[] = sprintf('%s = %s',$key,$value);
				}
				$order_id = $_SESSION['cart']['header']['order_id'];
				if ($_REQUEST['messageId'] == 1) {
					// approved
					$tmp = array(
						'authorization_amount'=>$_REQUEST['trnAmount'],
						'authorization_type'=>'Beanstream',
						'authorization_code'=>$_REQUEST['authCode'],
						'authorization_transaction'=>$_REQUEST['trnId'],
						'authorization_info'=>implode("\n",$return),
						'order_status'=>$this->fetchScalar('select code from code_lookups where type="orderStatus" order by sort limit 1')
					);
					$outer->init($this->m_dir.$response->getOption('beanstream'));
				}
				else {
					// declined
					$tmp = array(
						'authorization_amount'=>0,
						'authorization_type'=>'Beanstream',
						'authorization_code'=>$_REQUEST['trnId'],
						'authorization_transaction'=>$_REQUEST['messageText'],
						'authorization_info'=>implode("\n",$return),
						'order_status'=>$this->fetchScalar('select code from code_lookups where type="orderStatus" and sort = 4')
					);
					$outer->init($this->m_dir.$errors->getOption('beanstream'));
				}
				$outer->addData($_REQUEST);
				$outer->addData(array('cart'=>$formattedCart));
				$stmt = $this->prepare(sprintf('update orders set %s where id = %d',implode('=?,',array_keys($tmp)).'=?',$order_id));
				$stmt->bindParams(array_merge(array(str_repeat('s',count($tmp))),array_values($tmp)));
				if ($stmt->execute()) {
					$_SESSION['user']['lastOrder'] = $order_id;
					$this->activateRecurring($order_id);
					$this->config->postSaleProcessing($order_id,true,$this);
					unset($_SESSION['cart']);
					$outer->addTag('ecomErrors',$this->showEcomMessages(),false);
					return $outer->show();
				}
			}
			if ($this->hasOption('paypal') && array_key_exists('tx',$_REQUEST) && array_key_exists('st',$_REQUEST)) {
				$order_id = $_SESSION['cart']['header']['order_id'];	//explode('-',$_POST['response_order_id']);
				$tmp = array('authorization_amount'=>$_REQUEST['amt'],
					'authorization_type'=>'Paypal',
					'authorization_code'=>$_REQUEST['tx'],
					'order_status'=>$this->fetchScalar('select code from code_lookups where type="orderStatus" order by sort limit 1')
				);
				$this->beginTransaction();
				$stmt = $this->prepare(sprintf('update orders set %s where id = %d',implode('=?,',array_keys($tmp)).'=?',$order_id));
				$stmt->bindParams(array_merge(array(str_repeat('s',count($tmp))),array_values($tmp)));
				if ($valid = $stmt->execute()) {
					$_SESSION['user']['lastOrder'] = $order_id;

					//
					//	Get the Paypal specific transaction info
					//
					$parms = $GLOBALS['paypal'];
					$s = new Snoopy();
					$s->host = $parms['auth'];
					$s->port = 443;
					$s->httpmethod = 'POST';
					$vars = array(
							'cmd'=>'_notify-synch',
							'tx'=>$_REQUEST['tx'],
							'at'=>$parms['token']
					);
					$s->curl_path = $parms['curl_path'];
					$s->submit($parms['auth'],$vars);
					$this->logMessage(__FUNCTION__,sprintf('snoopy [%s] result [%s]',print_r($s,true),print_r($s->results,true)),2);
					$r = $s->results;
					$tmp['authorization_info'] = urldecode($r);
					$stmt = $this->prepare(sprintf('update orders set authorization_info = ? where id = %d',$order_id));
					$stmt->bindParams(array('s',$r));
					$valid = $valid && $stmt->execute();
					if ($valid) {
						$this->activateRecurring($order_id);
						$this->config->postSaleProcessing($order_id,true,$this);
						$outer->init($this->m_dir.$response->getOption('paypal'));
						$outer->addData($_REQUEST);
						$outer->addData(array('cart'=>$formattedCart));
						unset($_SESSION['cart']);
						$this->commitTransaction();
					}
					else {
						$this->addError('An Error Occurred. The Web Master has been notified');
						$this->rollbackTransaction();
					}
					$outer->addTag('ecomErrors',$this->showEcomMessages(),false);
					return $outer->show();
				}
			}
			if ($this->hasOption('payflow')) {
				if (array_key_exists('SECURETOKEN',$_REQUEST)) {
					$order_id = $_SESSION['cart']['header']['order_id'];
					$this->beginTransaction();
					$valid = true;
					if ($reauthorizing) {
						//
						//	set this order to processing, recurring, and cancel the original recurring
						//
						$expiring = $this->fetchSingle(sprintf('select o2.* from orders o1, orders o2 where o1.id = %d and o2.id = o1.authorization_transaction',$order_id));
						$original = $this->fetchSingle(sprintf('select * from orders where id = %d',$expiring['authorization_transaction']));
						$this->logMessage(__FUNCTION__,sprintf('expiring [%s] original [%s]',print_r($expiring,true),print_r($original,true)),1);
					}
					$success = $this->fetchScalar("select extra from code_lookups where type='payflowResponse' and code='success'");
					$tmp = array();
					if (array_key_exists('RESULT',$_REQUEST) && strpos($success,"|".$_REQUEST['RESULT']."|") !== false) {
						//
						//	AVS check. If AVSZIP and/or AVSADDR = N additional checks required
						//
						if (array_key_exists("AVSADDR",$_REQUEST) && array_key_exists("AVSZIP",$_REQUEST)) {
							$parms = $GLOBALS['payflow'];
							$this->logMessage(__FUNCTION__,sprintf("parms [%s] search [|%s%s|] strpos [%s]", print_r($parms,true), 
							$_REQUEST["AVSADDR"], $_REQUEST["AVSZIP"], 
							strpos($parms["AVS"],sprintf("|%s%s|",$_REQUEST["AVSADDR"],$_REQUEST["AVSZIP"]))),1 );
							if (strpos($parms["AVS"],sprintf("|%s%s|",$_REQUEST["AVSADDR"],$_REQUEST["AVSZIP"]))===false) {
								//
								//	processing failed address verification
								//
								$this->addError("There was an error with the Billing Address verification. The order was declined. The Billing Address must match your card's statement address.");
								$_REQUEST["RESULT"] = "AVSADDR";
								$tmp = array('authorization_amount'=>0,
									'authorization_partner'=>'PayFlow',
									'authorization_code'=>"AVSADDR",
									'authorization_type'=>$_REQUEST['TRXTYPE'],
									'authorization_message'=>sprintf("%s %s", $_REQUEST['RESPMSG'], array_key_exists("PREFPSMSG", $_REQUEST ) ? $_REQUEST['PREFPSMSG'] : ""),
									'authorization_info'=>print_r($_REQUEST,true)
								);
								$outer->setData("checkAddress",1);
								$this->logMessage(__FUNCTION__,sprintf("AVS triggered [%s]", print_r($_REQUEST,true)),1,true);
							}
						}
					}
					if (array_key_exists('RESULT',$_REQUEST) && strpos($success,"|".$_REQUEST['RESULT']."|") !== false) {
						if ($_REQUEST['RESULT'] != 0) {
							//
							//	accepted but something unusual about it
							//
							$this->logMessage(__FUNCTION__,sprintf("A suspicious order has been placed [%s]",$order_id),1,true,false);
						}
						if (array_key_exists("PPREF",$_REQUEST)) {
							//
							//	They used PayPal vs PayFlow
							//
							$tmp = array('authorization_amount'=>$_REQUEST['AMT'],
								'authorization_partner'=>'PayPal via PayFlow',
								'authorization_type'=>$_REQUEST['TRXTYPE'],
								'authorization_message'=>sprintf("%s %s", $_REQUEST['RESPMSG'], array_key_exists("PREFPSMSG", $_REQUEST ) ? $_REQUEST['PREFPSMSG'] : ""),
								'authorization_code'=>$_REQUEST['PPREF'],
								'authorization_info'=>print_r($_REQUEST,true),
								'authorization_transaction'=>$_REQUEST['PNREF'],
								'authorization_date'=>date(DATE_ATOM),
								'authorization_success'=>1,
								'order_id'=>$order_id
							);
							$tmp1 = array(
								'order_status'=>STATUS_PROCESSING,
								'cc_expiry'=>''
							);
							if ($reauthorizing)
								$this->execute(sprintf("update orders set authorization_type = 'PayPal via PayFlow' where id = %d",$original["id"]));
						}
						else {
							$tmp = array('authorization_amount'=>$_REQUEST['AMT'],
								'authorization_partner'=>'PayFlow',
								'authorization_type'=>$_REQUEST['TRXTYPE'],
								'authorization_message'=>sprintf("%s %s", $_REQUEST['RESPMSG'], array_key_exists("PREFPSMSG", $_REQUEST ) ? $_REQUEST['PREFPSMSG'] : ""),
								'authorization_code'=>$_REQUEST['AUTHCODE'],
								'authorization_info'=>print_r($_REQUEST,true),
								'authorization_transaction'=>$_REQUEST['PNREF'],
								'authorization_date'=>date(DATE_ATOM),
								'authorization_success'=>1,
								'order_id'=>$order_id
							);
							$tmp1 = array(
								'order_status'=>STATUS_PROCESSING,
								'cc_expiry'=>sprintf('%04d-%02d', substr($_REQUEST['EXPDATE'],2,2)+2000,substr($_REQUEST['EXPDATE'],0,2))
							);
							if ($reauthorizing)
								$this->execute(sprintf("update orders set authorization_type = 'PayFlow', baid = '', ba_authorization_transaction = '' where id = %d",$original["id"]));
						}
						if ($reauthorizing) {
							$valid &= $this->execute(sprintf('update orders set order_status = %d where id = %d', STATUS_CANCELLED | STATUS_RECURRING, $expiring['id']));
							$valid &= $this->execute(sprintf('update orders set order_status = %d, authorization_info = "%s", authorization_transaction = "%d" where id = %d', STATUS_PROCESSING | STATUS_RECURRING, print_r($_REQUEST,true),$original['id'], $order_id));
							//$valid &= $this->execute(sprintf('update orders set cc_expiry = "" where id = %d', $original['id']));
						}
						//else {
						//	$valid &= $this->execute(sprintf('update orders set authorization_info = "%s" where authorization_transaction = %d', print_r($_REQUEST,true), $order_id));
						//}
					}
					else {
						if (count($tmp)==0) {
							$tmp = array('authorization_amount'=>0,
								'authorization_partner'=>'PayFlow',
								'authorization_type'=>$_REQUEST['TRXTYPE'],
								'authorization_message'=>sprintf("%s %s", $_REQUEST['RESPMSG'], array_key_exists("PREFPSMSG", $_REQUEST ) ? $_REQUEST['PREFPSMSG'] : ""),
								'authorization_code'=>array_key_exists('AUTHCODE',$_REQUEST) ? $_REQUEST['AUTHCODE'] : "Cancelled",
								'authorization_info'=>print_r($_REQUEST,true),
								'order_id'=>$order_id
							);
							$tmp1 = array(
								'order_status'=>STATUS_CANCELLED,
							);
							$this->addError(sprintf('Your Order did not process correctly [%s]',array_key_exists('RESPMSG',$_REQUEST) ? $_REQUEST['RESPMSG'] : "unknown reason"));
						}
					}
					if ($reauthorizing) {
						$stmt = $this->prepare(sprintf('update orders set %s where id = %d',implode('=?,',array_keys($tmp1)).'=?',$original['id']));
					}
					else {
						$stmt = $this->prepare(sprintf('update orders set %s where id = %d',implode('=?,',array_keys($tmp1)).'=?',$order_id));
					}
					$stmt->bindParams(array_merge(array(str_repeat('s',count($tmp1))),array_values($tmp1)));
					$valid &= $stmt->execute();
					$tmp["authorization_date"] = date(DATE_ATOM);
					//$tmp["authorization_type"] = $_REQUEST['TRXTYPE'];
					$stmt = $this->prepare(sprintf("insert into order_authorization(%s) values(?%s)", implode(", ", array_keys($tmp)), str_repeat(", ?", count($tmp)-1)));
					$stmt->bindParams(array_merge(array(str_repeat("s",count($tmp))), array_values($tmp)));
					$valid &= $stmt->execute();
					if (array_key_exists('RESULT',$_REQUEST) && strpos($success,"|".$_REQUEST['RESULT']."|") !== false) {
						if ($valid) {
							$_SESSION['user']['lastOrder'] = $order_id;
							$outer->init($this->m_dir.$response->getOption('payflow'));
							$outer->addData(array('cart'=>$formattedCart));
							$temp = $outer->show();	// capture error messages [if any]
							if ($reauthorizing) {
								$tobill = $this->execute(sprintf('update order_billing set original_id = %d, attempts=0, authorization_message = "" where billed = 0 and original_id = %d',
										$order_id, $formattedCart['reauthorize']['o_id']));
							}
							else
								$this->activateRecurring($order_id);
							$this->config->postSaleProcessing($order_id,true,$this);
							$this->commitTransaction();
							//
							//	If this is a PayPal purchase with recurring we have to create a billing agreement now
							//
							if (array_key_exists("PPREF",$_REQUEST)) {
								if ($reauthorizing) $order_id = $original["id"];
								if ($ba = $this->fetchSingle(sprintf("select * from orders where authorization_transaction = '%d' and order_status & %d = %d limit 1",
									$order_id, STATUS_PROCESSING | STATUS_RECURRING, STATUS_PROCESSING | STATUS_RECURRING))) {
									$_SESSION['billing_agreement'] = $ba["id"];
									if (!$this->hasOption("billingAgreement")) {
										$this->logMessage(__FUNCTION__,sprintf("missing billing agreement config for order #%d",$order_id),1,true);
									}
									else {
										$fn = $this->fetchSingle(sprintf('select t.id as fetemplate_id, t.module_function, m.classname from fetemplates t, modules m where t.id = %d and m.id = t.module_id',$this->getOption('billingAgreement')));
										$obj = new product($fn['fetemplate_id'],$fn);
										if (method_exists('product',$fn['module_function'])) {
											$this->logMessage(__FUNCTION__,sprintf('invoking product with [%s]',print_r($fn,true)),2);
											$html = $obj->{$fn['module_function']}();
											$this->logMessage(__FUNCTION__,sprintf("billingAgreement returned html [%s]",$html),1);
											$outer->setHtml($html);
											$temp = $html;
										}
									}
								}
							}
						}
						else {
							$this->addError('An Error Occurred. The Web Master has been notified');
							$this->rollbackTransaction();
						}
						unset($_SESSION['cart']);
						return $temp;
					}
					else $this->commitTransaction();
				}
				else {
					if (count($_POST) > 0 && array_key_exists("HOSTCODE",$_POST)) {
						$this->logMessage(__FUNCTION__,sprintf("an odd payflow response [%s] from [%s]", print_r($_POST,true), print_r($_SESSION,true)),1,true);
						$outer->addFormError(sprintf("We received an unexpected response: %s The Web Master has been notified", $_POST["RESPMSG"]));
					}
				}
			}
			if ($this->hasOption('other') && array_key_exists('isOther',$_REQUEST)) {
				$order_id = $_SESSION['cart']['header']['order_id'];	//explode('-',$_POST['response_order_id']);
				$tmp = array('authorization_amount'=>0,
					'authorization_type'=>'Other',
					'order_status'=>$this->fetchScalar('select code from code_lookups where type="orderStatus" order by sort limit 1')
				);
				$stmt = $this->prepare(sprintf('update orders set %s where id = %d',implode('=?,',array_keys($tmp)).'=?',$order_id));
				$stmt->bindParams(array_merge(array(str_repeat('s',count($tmp))),array_values($tmp)));
				if ($stmt->execute()) {
					$_SESSION['user']['lastOrder'] = $order_id;
					$this->activateRecurring($order_id);
					$this->config->postSaleProcessing($order_id,true,$this);
					$outer->init($this->m_dir.$response->getOption('other'));
					unset($_SESSION['cart']);
					$outer->addData($_REQUEST);
					$outer->addData(array('cart'=>$formattedCart));
					$outer->addTag('ecomErrors',$this->showEcomMessages(),false);
					return $outer->show();
				}
			}
		}
		else {
			if ($this->hasOption('moneris') && array_key_exists('cancelTXN',$_REQUEST)) {
				$this->logMessage('payment',sprintf('processing cancelled order [%s]',$_REQUEST['order_id']),1);
				$order_id = explode('_',$_REQUEST['order_id']);
				$tmp = array();
				$tmp['order_status'] = 8;
				$stmt = $this->prepare(sprintf('update orders set %s where id = %d',implode('=?,',array_keys($tmp)).'=?',$order_id[0]));
				$stmt->bindParams(array_merge(array(str_repeat('s',count($tmp))),array_values($tmp)));
				if ($stmt->execute()) {
					$outer->init($this->m_dir.$response->getOption('moneris'));
					$_SESSION['user']['lastOrder'] = $order_id[0];
					$this->config->postSaleProcessing($order_id,false,$this);
					$outer->addTag('ecomErrors',$this->showEcomMessages(),false);
					return $outer->show();
				}
			}
		}
		$cart = array_key_exists('cart',$_SESSION) ? $_SESSION['cart'] : Ecom::initCart();
		//if (strlen($cart['addresses']['shipping']['email']) == 0) $cart['addresses']['shipping']['email'] = $this->getUserInfo('email');
		if (defined('FRONTEND') && (!array_key_exists('order_id',$cart['header']) || $cart['header']['order_id'] == 0)) {
			$recurring = array();
			foreach($cart['products'] as $key=>$product) {
				if ($product['recurring_qty'] > 0) {
					$cd = $this->fetchSingle(sprintf('select * from product_recurring where id = %d',$product['recurring_period']));
					$this->logMessage(__FUNCTION__,sprintf('adding recurring item [%s]',print_r($product,true)),1);
					if (!array_key_exists($cd['lookup_id'],$recurring)) {
						$recurring[$cd['lookup_id']] = $cart;
						$recurring[$cd['lookup_id']]['products'] = array();
					}
					$product['quantity'] = $product['recurring_qty'];
					$recurring[$cd['lookup_id']]['products'][$key] = $product;
				}
			}
			$this->logMessage(__FUNCTION__,sprintf('recurring carts [%s]',print_r($recurring,true)),1);
			$e = new Ecom();
			foreach($recurring as $key=>$recur) {
				$recurring[$key] = $e->updateCart($recur);
			}
			$this->logMessage(__FUNCTION__,sprintf('recurring carts [%s]',print_r($recurring,true)),1);
			$_SESSION['recurring_items'] = $recurring;
			$this->beginTransaction();
			$valid = $this->createOrder($cart,$orderid);
			if ($valid) {
				$cart['header']['order_id'] = $orderid;
				$formattedCart['header']['order_id'] = $orderid;
				$_SESSION['cart'] = $cart;
				$this->commitTransaction();
				if ($reauthorizing) {
					$this->logMessage(__FUNCTION__,sprintf('skipping recurring for reauthorizing order #[%s]',$cart['reauthorize']['o_id']),1);
				}
				else {
					$this->initRecurring($orderid);
				}
			}
			else {
				$this->addError('An error occurred. The Web Master has been notified');
				$this->rollbackTransaction();
			}
		}
		$sql = sprintf('select province_code from provinces where id = %d',$cart['addresses']['shipping']['province_id']);
		$cart['addresses']['shipping']['province'] = $this->fetchScalar($sql);
		$cart['addresses']['shipping']['country'] = $this->fetchScalar(sprintf('select country from countries where id = %d',$cart['addresses']['shipping']['country_id']));
		if ($this->checkArray("addresses:billing",$cart) && count($cart['addresses']['billing']) > 0) {
			$cart['addresses']['billing']['province'] = $this->fetchScalar(sprintf('select province_code from provinces where id = %d',$cart['addresses']['billing']['province_id']));
			$cart['addresses']['billing']['country'] = $this->fetchScalar(sprintf('select country from countries where id = %d',$cart['addresses']['billing']['country_id']));
		}
		$f = new Forms();
		if ($this->hasOption('moneris')) {
			$this->logMessage('payment',sprintf('adding moneris template [%s]',$this->m_dir.$inner->getOption('moneris')),3);
			$f->init($this->m_dir.$inner->getOption('moneris'));
			$f->addData(Ecom::formatCart($cart));
			$f->addData($GLOBALS['moneris']);
			$outer->addTag('moneris',$f->show(),false);
		}
		if ($this->hasOption('beanstream')) {
			$this->logMessage('payment',sprintf('adding beanstream template [%s]',$this->m_dir.$inner->getOption('beanstream')),3);
			$f->init($this->m_dir.$inner->getOption('beanstream'));
			$f->addData(Ecom::formatCart($cart));
			$f->addData($GLOBALS['beanstream']);
			$outer->addTag('beanstream',$f->show(),false);
		}
		if ($this->hasOption('paypal')) {
			$this->logMessage('payment',sprintf('adding paypal template [%s]',$this->m_dir.$inner->getOption('paypal')),3);
			$f->init($this->m_dir.$inner->getOption('paypal'));
			$f->addData(Ecom::formatCart($cart));
			$f->addData($GLOBALS['paypal']);
			$outer->addTag('paypal',$f->show(),false);
		}
		if ($this->hasOption('payflow')) {
			$this->logMessage(__FUNCTION__,sprintf('adding payflow template [%s]',$this->m_dir.$inner->getOption('paypal')),3);
			$parms = $GLOBALS['payflow'];
			$s = new Snoopy();
			$securetoken = substr(SHA1(date(DATE_ATOM).' '.$parms['partner']),0,32);
			$_SESSION['cart']['header']['securetoken'] = $securetoken;
			$trxtype=$parms["trxtype"];
			if ($reauthorizing || $this->hasOption("authorize_only")) $trxtype="A";
			$formvars = array(
				'PARTNER'=>$parms['partner'],
				'VENDOR'=>$parms['vendor'],
				'PWD'=>$parms['pwd'],
				'USER'=>$parms['user'],
				'TRXTYPE'=>$trxtype,
				'AMT'=>number_format($formattedCart['header']['total'],2,'.',''),
				'CREATESECURETOKEN'=>'Y',
				'SECURETOKENID'=>$securetoken
			);
			$s->host = $parms['auth'];
			$s->port = 443;
			$s->httpmethod = 'POST';
			$s->curl_path = $parms['curl_path'];
			$s->submit($parms['auth'],$formvars);
			$this->logMessage(__FUNCTION__,sprintf('payflow snoopy [%s] formvars [%s] result [%s]',print_r($s,true),print_r($formvars,true),print_r($s->results,true)),2);
			$result = $this->depairOptions(urldecode($s->results),array('&','='));
			$f->init($this->m_dir.$inner->getOption('payflow'));
			if (!(array_key_exists("RESPMSG",$result) && $result['RESPMSG'] == 'Approved')) {
				$this->logMessage(__FUNCTION__,sprintf("payflow token failed parms [%s] result [%s]",print_r($formvars,true),print_r($result,true)),1,true);
				$this->addEcomError("An internal error ocurred. The Web Master has been notified");
				if (strlen($s->error) > 0 && DEBUG > 0)
					$this->addEcomError($s->error);
			}
			else {
				$f->addData($formattedCart);
				$f->addData($parms);
				$f->addData($result);
				$outer->addTag('payflow',$f->show(),false);
			}
		}
		if ($this->hasOption('2checkout')) {
			$this->logMessage('payment',sprintf('adding 2checkout template [%s]',$this->m_dir.$inner->getOption('2checkout')),3);
			$f->init($this->m_dir.$inner->getOption('2checkout'));
			$f->addData(Ecom::formatCart($cart));
			$f->addData($GLOBALS['2checkout']);
			$idx = 0;
			$temp = array();
			foreach($cart['products'] as $key=>$value) {
				$dtl = new Forms();
				$dtl->init($this->m_dir.$details->getOption('2checkout'));
				$dtl->addData($value);
				$dtl->addTag('key',$idx);
				$temp[] = $dtl->show();
				$idx++;
			}
			$outer->addTag('details',implode($temp,''));
			$outer->addTag('2checkout',$f->show(),false);
		}
		if ($this->hasOption('other')) {
			$this->logMessage('payment',sprintf('adding other template [%s]',$this->m_dir.$inner->getOption('other')),3);
			$f->init($this->m_dir.$inner->getOption('other'));
			$f->addData($cart['header']);
			$outer->addData($GLOBALS['other']);
			$outer->addTag('other',$f->show(),false);
		}
		if ($this->hasOption("custom") && method_exists($this->config,$this->getOption("custom"))) {
			$outer->addTag('custom',$this->config->{$this->getOption("custom")}($module),false);
		}
		$outer->addData($cart['header']);
		$outer->addTag('formattedTotal',sprintf('%.02f',$cart['header']['total']));
		$tmp = array();
		if ($this->checkArray("addresses:billing", $cart)) {
			foreach(count($cart['addresses']['billing']) > 0 ? $cart['addresses']['billing']:$cart['addresses']['shipping'] as $key=>$value) {
				$tmp['bill_'.$key] = $value;
			}
		}
		$outer->addData($tmp);
		$outer->addData($cart['addresses']['shipping']);
		$outer->addData($this->getUserInfo());
		$outer->addTag('ecomErrors',$this->showEcomMessages());
		$subdata = $this->subForms($this->m_module['fetemplate_id'],null,array(),'outer');
		$this->logMessage(__FUNCTION__,sprintf('subforms [%s]',print_r($subdata,true)),3);
		foreach($subdata as $key=>$value) {
			$outer->addTag($key,$value,false);
		}
		return $outer->show();
	}

	public function createOrder($cart,&$orderid) {
		$kjv = new KJV();
		return $kjv->insertOrder($cart,$orderid);
	}

	public function createOrderDNU($cart,&$orderid) {
		$this->logMessage(__FUNCTION__,sprintf("cart [%s]", print_r($cart,true)),1);
		$reauthorizing = array_key_exists('reauthorize',$cart) && array_key_exists('o_id',$cart['reauthorize']) && $cart['reauthorize']['o_id'] > 0;
		$stmt = $this->prepare('insert into orders(member_id, order_status, value, coupon_id, discount_value, discount_rate, line_discounts, net, shipping, taxes, total, order_date, created, random, discount_type, recurring_period, currency_id, exchange_rate, ship_via, authorization_code, authorization_transaction, handling, points_redeemed, points_collected) values(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
		$stmt->bindParams(array('iididddddddssisiidsssddd', 
			$cart['header']['member_id'] > 0 ? $cart['header']['member_id'] : $_SESSION['user']['info']['id'], 
			STATUS_INCOMPLETE | ($reauthorizing ? (STATUS_REAUTHORIZING | STATUS_RECURRING) : 0), $cart['header']['value'], $cart['header']['coupon_id'], $cart['header']['discount_value'], $cart['header']['discount_rate'], 
			$cart['header']['line_discounts'], $cart['header']['net'], $cart['header']['shipping'], $cart['header']['taxes'], $cart['header']['total'], 
			date(DATE_ATOM), date(DATE_ATOM), rand(), $cart['header']['discount_type'],
			array_key_exists('recurring_period',$cart['header']) ? $cart['header']['recurring_period'] : 0,
			array_key_exists('currency_id',$cart['header'])?$cart['header']['currency_id']:0, 
			array_key_exists('exchange_rate',$cart['header'])?$cart['header']['exchange_rate']:0,
			array_key_exists('ship_via',$cart['header'])?$cart['header']['ship_via']:"",
			$reauthorizing ? sprintf('Reauthorizing Order #%d',$this->fetchScalar(sprintf('select authorization_transaction from orders where id = %d',$cart['reauthorize']['o_id']))) : "",
			$reauthorizing ? $cart['reauthorize']['o_id'] : "", $cart['header']['handling'], $cart['header']['points_redeemed'], $cart['header']['points_collected']));
		if ($valid = $stmt->execute()) {
			$orderid = $this->insertId();
			$idx = 0;
			//$valid = true;
			foreach($cart['products'] as $key=>$line) {
				$idx += 1;
				$stmt = $this->prepare('insert into order_lines(order_id,line_id,product_id,options_id,quantity,price,coupon_id,discount_value,discount_rate,discount_type,value,shipping,inventory_id,total,taxes,tax_exemptions,color,size,recurring_discount_rate,recurring_discount_value,recurring_shipping_only,recurring_discount_type,recurring_period,recurring_qty,qty_multiplier,custom_package) values(?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)');
				$stmt->bindParams(array('iiiiididddddiddsddddisddds',$orderid,$idx,$line['product_id'],$line['options_id'],$line['quantity'],$line['price'],$line['coupon_id'],$line['discount_value'],$line['discount_rate'],$line['discount_type'],$line['value'],$line['shipping'],$line['inventory_id'],$line['total'],$line['taxes'],$line['tax_exemptions'],
					$line['color'],$line['size'],$line['recurring_discount_rate'], $line['recurring_discount_value'], $line['recurring_shipping_only'], $line['recurring_discount_type'],
					$line['recurring_period'], $line['recurring_qty'],$line['qty_multiplier'],$line["custom_package"]));
				$valid = $valid && $stmt->execute();
				$line_id = $this->insertId();
				foreach($line['taxdata'] as $taxkey=>$taxline) {
					$tax = $this->prepare('insert into order_taxes(order_id,line_id,tax_id,tax_amount,taxable_amount) values(?,?,?,?,?)');
					$tax->bindParams(array('iiidd',$orderid,$idx,$taxkey,$taxline['tax_amount'],$taxline['taxable_amount']));
					$valid = $valid && $tax->execute();
				}
				if (array_key_exists("dimensions",$line)) {
					foreach($line["dimensions"] as $skey=>$dim) {
						$this->logMessage(__FUNCTION__,sprintf("dim is [%s]",print_r($dim,true)),1);
						$stmt = $this->prepare("insert into order_lines_dimensions(order_id, line_id, weight, height, width, depth, quantity) values(?, ?, ?, ?, ?, ?, ?)");
						$stmt->bindParams(array("iiddddd",$orderid, $idx,$dim["weight"],$dim["height"],$dim["width"],$dim["depth"],$dim["quantity"]));
						$valid = $valid && $stmt->execute();
					}
				}
			}
			foreach($cart['addresses'] as $key=>$address) {
				if (is_array($address) && array_key_exists('ownerid',$address) && $address['ownerid'] > 0) {
					$address['ownertype'] = 'order';
					$address['ownerid'] = $orderid;
					$address['addressbook_id'] = $address['id'];
					unset($address['id']);
					$stmt = $this->prepare(sprintf('insert into addresses(%s) values(%s)',implode(',',array_keys($address)),str_repeat('?,',count($address)-1).'?'));
					$stmt->bindParams(array_merge(array(str_repeat('s',count($address))),array_values($address)));
					$valid = $valid && $stmt->execute();
				}
			}
			foreach($cart['taxes'] as $taxkey=>$taxline) {
				$tax = $this->prepare('insert into order_taxes(order_id,line_id,tax_id,tax_amount,taxable_amount) values(?,?,?,?,?)');
				$tax->bindParams(array('iiidd',$orderid,0,$taxkey,$taxline['tax_amount'],$taxline['taxable_amount']));
				$valid = $valid && $tax->execute();
			}
		}
		$this->logMessage(__FUNCTION__,sprintf('returning state [%s]',$valid),1);
		return $valid;
	}

	function orderHistory() {
		if (!$module = $this->getModule())
			return "";
		$outer = new Forms();
		$outer->setModule($module);
		$outer->init($this->m_dir.$module['outer_html']);
		$user = array_key_exists('user',$_SESSION) && is_array($_SESSION['user']) ? $_SESSION['user']['info']['id'] : 0;
		if ($this->hasOption('usePassed') && array_key_exists('m_id',$_REQUEST) && $_REQUEST['m_id'] > 0) {
			$user = $_REQUEST['m_id'];
			$this->logMessage('orderHistory',sprintf('user superceded with usePassed [%d]',$user),2);
		}
		$where = '1 = 1 ';
		if ($this->hasOption('useDates')) {
			if (array_key_exists('d_from',$_REQUEST) && strlen($_REQUEST['d_from']) > 0) {
				$where .= sprintf('and order_date >= "%s 00:00:00" ',date('Y-m-d',strtotime($_REQUEST['d_from'])));
			}
			if (array_key_exists('d_to',$_REQUEST) && strlen($_REQUEST['d_to']) > 0) {
				$where .= sprintf('and order_date <= "%s 23:59:59" ',date('Y-m-d',strtotime($_REQUEST['d_to'])));
			}
		}
		if ($this->hasOption('where_clause') && strlen($this->getOption('where_clause')) > 0)
			$where .= " and ".$this->getOption('where_clause');
		$sql = sprintf('select o.*, o1.id as recurringId, o1.order_status as recurringStatus from orders o left join orders o1 on o1.authorization_transaction = o.id where %s and o.member_id = %s order by id desc',$where,$user);
		$pagination = $this->getPagination($sql,$module,$recordCount);
		$orders = $this->fetchAll($sql);
		$return = array();
		$inner = new Forms();
		$inner->init($this->m_dir.$module['inner_html']);
		foreach($orders as $key=>$order) {
			$inner->reset();
			$inner->addData($this->formatOrder($order));
			$subdata = $this->subForms($this->m_module['fetemplate_id'],null,array("o_id"=>$order["id"]),'inner');
			$this->logMessage(__FUNCTION__,sprintf('subforms [%s]',print_r($subdata,true)),3);
			foreach($subdata as $key=>$value) {
				$inner->addTag($key,$value,false);
			}
			$return[] = $inner->show();
		}
		$outer->addTag('orders',implode('',$return),false);
		$outer->addData(array('recordCount'=>count($orders)));
		if ($tmp = $this->fetchSingle(sprintf('select * from members where id = %d',$user)))
			$outer->addData($tmp);
		$subdata = $this->subForms($this->m_module['fetemplate_id'],null,array(),'outer');
		$this->logMessage(__FUNCTION__,sprintf('subforms [%s]',print_r($subdata,true)),3);
		foreach($subdata as $key=>$value) {
			$outer->addTag($key,$value,false);
		}
		$outer->addTag('pagination',$pagination,false);
		return $outer->show();
	}

	function orderDetails($o_id = null) {
		if (!$module = $this->getModule())
			return "";
		$this->parseOptions($module['misc_options']);
		$outer = new Forms();
		$outer->setModule($module);
		$outer->init($this->m_dir.$module['outer_html']);
		if (is_null($o_id)) $o_id = array_key_exists('o_id',$_REQUEST) ? $_REQUEST['o_id'] : 0;
		if ($this->hasOption('usePassed') && array_key_exists('m_id',$_REQUEST))
			$user = $_REQUEST['m_id'];
		else
			$user = array_key_exists('user',$_SESSION) && is_array($_SESSION['user']) ? $_SESSION['user']['info']['id'] : 0;
		$this->logMessage('orderDetails',sprintf('printing order %d',$o_id),2);
		if (!$header = $this->fetchSingle(sprintf('select o.* from orders o, members m where o.id = %d and o.member_id = m.id and (m.id = %d or m.custom_parent_org = %2$d)',$o_id,$user)))
			return $outer->show();
		$header["notes_formatted"] = nl2br($header["notes"]);
		$return = array();
		$inner = new Forms();
		$inner->init($this->m_dir.$module['inner_html']);
		$hasRecalc = $this->fetchScalar(sprintf("select id from order_lines where order_id = %d and product_id = %d and deleted = 0", $header['id'], FEDEX_RECALC));
		$outer->setData("hasRecalc", $hasRecalc > 0);
		$lines = $this->fetchAll(sprintf('select l.*, p.*, l.id as oline_id from order_lines l, product p where l.order_id = %d and l.deleted = 0 and p.id = l.product_id order by line_id',$header['id']));
		foreach($lines as $key=>$line) {
			$inner->reset();
			$inner->setData("hasRecalc", $hasRecalc > 0);
			$inner->addData($this->formatOrderLine($line));
			$return[] = $inner->show();
		}
		$inner->init($this->m_dir.$module['parm2']);
		$fmtHeader = $this->formatOrder($header);
		$inner->setData('description','Goods Total');
		$inner->setData('amount',$fmtHeader['formattedValue']);
		$return[] = $inner->show();
		if ($header["shipping"] > .01) {
			$inner->setData('description','Shipping');
			$inner->setData('amount',$fmtHeader['formattedShipping']);
			$return[] = $inner->show();
		}
		if (array_key_exists('handling',$fmtHeader) && $fmtHeader['handling'] != 0) {
			$inner->setData('description','Handling Fee');
			$inner->setData('amount',$fmtHeader['formattedHandlingFee']);
			$return[] = $inner->show();
		}

		if ($header['discount_value'] != 0) {
			if ($header['discount_type'] == 'P')
				$inner->setData('description',sprintf('Discount (%.02f%%)',$fmtHeader['discount_rate']));
			else
				$inner->setData('description','Discount');
			$inner->setData('amount',$fmtHeader['formattedDiscountValue']);
			$return[] = $inner->show();
		}
		if ($header['line_discounts'] != 0) {
			$inner->setData('description','Item Discounts');
			$inner->setData('amount',$fmtHeader['formattedLineDiscount']);
			$return[] = $inner->show();
		}

		if ($fmtHeader['taxes'] > .01) {
			$inner->setData('description','Taxes');
			$inner->setData('amount',$fmtHeader['formattedTaxes']);
			$return[] = $inner->show();
		}
		$inner->setData('description','Total');
		$inner->setData('amount',$fmtHeader['formattedTotal']);
		$return[] = $inner->show();

		if ($auth = $this->fetchSingle(sprintf("select * from order_authorization where order_id = %d and authorization_type = 'D' and authorization_success=1", $o_id))) {
			$inner->setData('description','Paid');
			$inner->setData('amount',$this->my_money_format($auth["authorization_amount"]));
			$return[] = $inner->show();
			$inner->setData('description','Balance');
			$inner->setData('amount',$this->my_money_format($header["total"] - $auth["authorization_amount"]));
			$return[] = $inner->show();
		}

		$outer->addTag('lines',implode('',$return),false);
		$addresses = $this->fetchAll(sprintf('select a.*, p.province, c.country from addresses a left join provinces p on p.id = a.province_id, countries c, code_lookups l where ownertype="order" and ownerid = %d and c.id = a.country_id and l.id = addresstype order by l.sort',$header['id']));
		$inner->init($this->m_dir.$module['parm1']);
		$return = array();
$member = $this->fetchSingle(sprintf("select * from members where id = %d",$header["member_id"]));
		foreach($addresses as $key=>$address) {
			$inner->reset();
			$inner->setData('addressType',$address['tax_address'] == 1 ? 'Shipping' : 'Billing');
			if ($action = $this->fetchSingle(sprintf("select cd.*, m.firstname,m.lastname from custom_delivery cd left join drivers d on d.id = cd.driver_id left join members m on m.id = d.member_id where cd.order_id = %d and service_type='%s'", $o_id, $address["addresstype"] == ADDRESS_PICKUP ? "P":"D"))) {
				$action["has_signature"] = strlen($action["signature"]) > 0;
				$action["sig_image"] = base64_encode($action["signature"]);
				$address["action"] = $action;
			}
			$inner->addData(Address::formatData($address));
			if ($address["addresstype"] != ADDRESS_BILLING || $member["custom_on_account"] == 0 ) {
				$return[] = $inner->show();
			}
		}
		$outer->setData('addresses',implode('',$return),false);
		$outer->addData($this->formatOrder($header));
		$subdata = $this->subForms($this->m_module['fetemplate_id'],null,array(),'outer');
		$this->logMessage('details',sprintf('subforms [%s]',print_r($subdata,true)),3);
		foreach($subdata as $key=>$value) {
			$outer->addTag($key,$value,false);
		}
		return $outer->show();
	}

	function receipt() {
		if (!$module = $this->getModule())
			return "";
		$this->parseOptions($module['misc_options']);
		if (array_key_exists('user',$_SESSION) && is_array($_SESSION['user']) && array_key_exists('lastOrder',$_SESSION['user'])) {
			$o_id = $_SESSION['user']['lastOrder'];
		}
		else $o_id = array_key_exists('o_id',$_REQUEST) ? $_REQUEST['o_id'] : 0;
		return $this->orderDetails($o_id);
	}

	function search() {
		if (!$module = $this->getModule())
			return "";
		$this->parseOptions($module['misc_options']);
		$outer = new Forms();
		$outer->setModule($module);
		$outer->init($this->m_dir.$module['outer_html']);
		$flds = $outer->buildForm($this->config->getFields($module['configuration']));
		if (count($_POST) > 0 && array_key_exists('productSearchForm',$_POST)) {
			$outer->addData($_POST);
			if ($outer->validate()) {
				$module['where_clause'] = '';
				if (strlen($_POST['productSearch']) > 0)
					$module['where_clause'] .= sprintf('(p.code like "%%%s%%" or p.name like "%%%s%%" or p.teaser like "%%%s%%" or p.description like "%%%s%%" or p.alternate_description like "%%%s%%")',
						$_POST['productSearch'],$_POST['productSearch'],$_POST['productSearch'],$_POST['productSearch'],$_POST['productSearch']);
				foreach($flds as $key=>$fld) {
					if (array_key_exists('searchField',$fld) && $fld['searchField'] && $outer->getData($key) != '') {
						if ($fld['type'] == 'select')
							$module['where_clause'] .= sprintf('%s %s = "%s" ', strlen($module['where_clause']) > 0 ? 'and':'',$key, $outer->getData($key));
						else
							$module['where_clause'] .= sprintf('%s %s like "%%%s%%" ', strlen($module['where_clause']) > 0 ? 'and':'',$key, $outer->getData($key));
					}
				}
				if (strlen($module['sort_by']) == 0) $module['sort_by'] = 'p.name';
				$sql = $this->buildSql($module);
				$raw = $this->fetchAll($sql);
				$omit = array(0);
				foreach($raw as $key=>$p) {
					$t = strip_tags($p["teaser"]);
					$b = strip_tags($p["description"]);
					if (stripos($p["name"],$_POST['productSearch']) == false && stripos($t,$_POST['productSearch']) === false && stripos($b,$_POST['productSearch']) === false) {
						$this->logMessage(__FUNCTION__,sprintf("dropping product [%d] after stripping tags", $p["id"]),1);
						$omit[] = $p["id"];
					}
				}
				$module['where_clause'] .= sprintf(" and p.id not in (%s)", implode(",",$omit));
				$sql = $this->buildSql($module);
				$pagination = $this->getPagination($sql,$module,$recordCount,$pageNum);
				$outer->setData('pagenum',$pageNum);
				$products = $this->fetchAll($sql);
				$this->logMessage('search',sprintf('sql [%s] returned [%d] records',$sql,count($products)),2);
				$inner = new Forms();
				$inner->init($this->m_dir.$module['inner_html']);
				$result = array();
				$ct = 0;
				foreach($products as $product) {
					$ct += 1;
					if ($module['rows'] > 0 && $ct > $module['columns']) {
						$ct = 1;
						$result[] = '<div class="clearfix"></div>';
					}
					$inner->reset();
					$product = $this->formatData($product);
					$searchPhrase = array($_POST['productSearch']);
					$product['teaser'] = $this->highlight($searchPhrase,$product['teaser']);
					$product['code'] = $this->highlight($searchPhrase,$product['code']);
					$product['name'] = $this->highlight($searchPhrase,$product['name']);
					$inner->addData($product);
					$result[] = $inner->show();
				}
				if (count($result) == 0 && strlen($module['parm1']) > 0) {
					$tmp = new Forms();
					$tmp->init($this->m_dir.$module['parm1']);
					$outer->addTag('products',$tmp->show(),false);
				}
				else
					$outer->addTag('products',implode('',$result),false);
				$outer->addTag('pagination',$pagination,false);
				$outer->addTag('recordCount',$recordCount);
			}
		}
		return $outer->show();
	}

	function relatedProducts() {
		if (!$module = $this->getModule())
			return "";
		$this->parseOptions($module['misc_options']);
		$outer = new Forms();
		$outer->setModule($module);
		$outer->init($this->m_dir.$module['outer_html']);
		if (array_key_exists('prod_id',$_REQUEST)) {
			$inner = new Forms();
			$inner->init($this->m_dir.$module['inner_html']);
			$sql = 'select * from product where enabled = 1 and published = 1 ';
			if ($this->hasOption('relatedFrom')) {
				$sql .= sprintf(' and id in (select owner_id from relations where owner_type = "product" and related_type = "product" and related_id = %d)',$_REQUEST['prod_id']);
			}
			else if ($this->hasOption('relatedTo')) {
				$sql .= sprintf(' and id in (select related_id from relations where owner_type = "product" and related_type = "product" and owner_id = %d)',$_REQUEST['prod_id']);
			}
			else {
				$sql .= sprintf('
and id in (
select owner_id from relations where owner_type = "product" and related_type = "product" and related_id = %d 
union
select related_id from relations where owner_type = "product" and related_type = "product" and owner_id = %d
) and id != %d',$_REQUEST['prod_id'],$_REQUEST['prod_id'],$_REQUEST['prod_id']);
			}
			if (strlen($module['sort_by']) > 0)
				$sql .= ' order by '.$module['sort_by'];
			if ($module['limit'] > 0)
				$sql .= ' limit '.$module['limit'];
			$prods = $this->fetchAll($sql);
			$this->logMessage('relatedProducts',sprintf('sql [%s] records [%d]',$sql,count($prods)),2);
			$return = array();
			foreach($prods as $prod) {
				$inner->reset();
				$inner->addData($this->formatData($prod));
				$return[] = $inner->show();
			}
			$outer->addTag('products',implode('',$return),false);
 			if (count($prods) > 0 || $this->hasOption('showAlways'))
	 			return $outer->show();
		}
	}

	function productOptions() {
		if (!$module = $this->getModule())
			return "";
		$this->parseOptions($module['misc_options']);
		$outer = new Forms();
		$outer->setModule($module);
		$outer->init($this->m_dir.$module['outer_html']);
		if (array_key_exists('prod_id',$_REQUEST) || $this->hasOption('showAlways')) {
			if ($prod = $this->fetchSingle(sprintf('select * from product where id = %d and deleted = 0 and enabled = 1',$_REQUEST['prod_id']))) {
				if (array_key_exists('options_id',$_REQUEST)) {
					$sql = sprintf('select l.id, l.value, o.price, o.teaser, o.description from code_lookups l, product_options_info poi, product_options o where poi.options_id = %d and poi.options_type = "%s" and l.type = poi.options_type and o.id = poi.options_id and l.id = poi.type_id order by l.sort, l.value',$_REQUEST['options_id'],$_REQUEST['type']);
				}
				else {
					$sql = sprintf('select opt.* from product p, product_options opt where p.id = %d and p.deleted = 0 and p.published = 1 and p.enabled = 1 and opt.product_id = p.id and opt.deleted = 0',$_REQUEST['prod_id']);
					if (strlen($module['sort_by']) > 0)
						$sql .= ' order by '.$module['sort_by'];
				}
				$opts = $this->fetchAll($sql);
				$this->logMessage('productOptions',sprintf('sql [%s] found [%d]',$sql,count($opts)),1);
				$product = $this->formatData($prod);
				$inner = new Forms();
				$inner->init($this->m_dir.$module['inner_html']);
				$result = array();
				foreach($opts as $key=>$values) {
					$inner->reset();
					foreach($_REQUEST as $key=>$value) {
						$inner->addTag('arg_'.$key,$value);
					}
					$values['formattedPrice'] = money_format('%(.2n', $product['price']+$values['price']);
					$values['minPrice'] = $product['price']+$values['price'];
					$inner->addData($this->formatOption($values,$key == 0));
					$result[] = $inner->show();
				}
				foreach($_REQUEST as $key=>$value) {
					$outer->addTag('arg_'.$key,$value);
				}
				$outer->addData($this->formatData($prod));
				$outer->addTag('options',implode('',$result),false);
			}
			return $outer->show();
		}
	}

	function folderRelations() {
		if (!$module = $this->getModule())
			return "";
		$this->logMessage("folderRelations",sprintf("module [%s]",print_r($module,true)),2);
		if ($module['folder_id'] == 0) {
			$this->logMessage('folderRelations','bail - no default folder',1);
			return '';
		}
		if (!$this->hasOption('templateId')) {
			$this->logMessage('folderRelations',sprintf('attempt to implement cross class functionality with no config [%s] this [%s] request [%s]',print_r($module,true),print_r($this,true),print_r($_REQUEST,true)),1,true);
			return "";
		}
		$fn = $this->fetchSingle(sprintf('select t.id as fetemplate_id, t.module_function, m.classname from fetemplates t, modules m where t.id = %d and m.id = t.module_id',$this->getOption('templateId')));
		$tmp = $this->m_module;
		$tmp['classname'] = $fn['classname'];
		$tmp['fetemplate_id'] = $fn['fetemplate_id'];
		$tmp['module_function'] = $fn['module_function'];
		switch($fn['classname']) {
		case 'gallery':
			$module['id'] = $this->getOption('templateId');
			$html = $this->galleryFolder($fn,$module['folder_id']);
			break;
		}
		return $html;
	}

	function itemRelations() {
		if (!$module = $this->getModule())
			return "";
		$this->logMessage("itemRelations",sprintf("module [%s]",print_r($module,true)),2);
		if (!$this->hasOption('templateId')) {
			$this->logMessage('itemRelations',sprintf('attempt to implement cross class functionality with no config [%s] this [%s] request [%s]',print_r($module,true),print_r($this,true),print_r($_REQUEST,true)),1,true);
			return "";
		}
		$fn = $this->fetchSingle(sprintf('select t.id as fetemplate_id, t.module_function, m.classname from fetemplates t, modules m where t.id = %d and m.id = t.module_id',$this->getOption('templateId')));
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
			case 'news':
				$module['id'] = $this->getOption('templateId');
				$html = $this->news($fn);
				break;
			case 'blog':
				$module['id'] = $this->getOption('templateId');
				$html = $this->blog($fn);
				break;
			default:
				$html = '';
		}
		return $html;
	}

	private function galleryFolder($module,$folder) {
		$this->logMessage('galleryFolder',sprintf('parms [%s]',print_r($module,true)),2);
		if ($folders = $this->fetchSingle(sprintf('select * from relations where owner_type = "productfolder" and owner_id = %d and related_type = "galleryfolder"',$folder))) {
			$module['folder_id'] = $folders['related_id'];
		}
		else {
			$folders = array();
			$this->logMessage('calendar',sprintf('no gallery for productFolder %d',$folder),1);
			return "";
		}
		$obj = new gallery($module['fetemplate_id'],$module);
		if (method_exists('gallery',$module['module_function'])) {
			$this->logMessage('galleryFolder',sprintf('invoking gallery with [%s]',print_r($module,true)),2);
			$html = $obj->{$module['module_function']}();
			return $html;
		}
		else {
			$this->logMessage('galleryFolder',sprintf('bail - no function [%s] in gallery for productFolder %d',$module['module_function'],$folder),1,true);
		}
	}

	function sizeOrColor() {
		if (!$module = $this->getModule())
			return "";
		$this->parseOptions($module['misc_options']);
		$outer = new Forms();
		$outer->init($this->m_dir.$module['outer_html']);
		$inner = new Forms();
		$inner->init($this->m_dir.$module['inner_html']);
		if ($prod = $this->fetchSingle(sprintf('select * from product where deleted = 0 and enabled = 1 and published = 1 and id = %d',$this->m_productId))) {
			foreach($_REQUEST as $key=>$value) {
				if (!array_key_exists($key,$prod)) $prod[$key] = $value;
			}
			if (array_key_exists('product_quantity',$_REQUEST)) {
				$pricing = $this->fetchSingle(sprintf('select * from product_pricing where product_id = %d and min_quantity <= %d and max_quantity > %d order by min_quantity limit 1',$prod['id']));
			}
			else
				$pricing = $this->fetchSingle(sprintf('select * from product_pricing where product_id = %d order by min_quantity limit 1',$prod['id']));
			$outer->addData($this->formatData($prod));
			$options = array();
			if (array_key_exists('color',$_REQUEST)) {
				$data = $this->fetchAll(sprintf('
select o.*, ls.value as sizeName, ls.id as size, lc.value as colorName, lc.id as color
from code_lookups ls, code_lookups as lc, product_options o, product_options_info poi
where o.id in (
select poi.options_id
from product_options o1, product_options_info poi1
where o1.product_id = %d and poi1.options_id = o.id and poi1.options_type = "color" and poi1.type_id = %d
) and poi.options_id = o.id and poi.options_type = "size" and ls.id = poi.type_id and lc.id = %d
order by ls.sort, ls.value',$prod['id'],$_REQUEST['color'],$_REQUEST['color']));
			}
			elseif (array_key_exists('size',$_REQUEST)) {
				$data = $this->fetchAll(sprintf('select o.*, l1.value as colorName, l2.value as sizeName from product_options o, code_lookups l1, code_lookups l2 where o.product_id = %d and o.size = %d and l1.id = o.color and l2.id = o.size and o.deleted = 0',$prod['id'],$_REQUEST['size']));
			}
			else {
				$data = $this->fetchAll(sprintf('select o.*, l1.value as colorName, l2.value as sizeName from product_options o, code_lookups l1, code_lookups l2 where o.product_id = %d and l1.id = o.color and l2.id = o.size and o.deleted = 0',$prod['id']));
			}
			foreach($data as $key=>$value) {
				$inner->reset();
				$inner->addData($value);
				$inner->addTag('formattedShipping',money_format('%(.2n',$value['shipping']));
				$inner->addTag('formattedPrice',money_format('%(.2n',$pricing['price'] + $value['price']));
				$options[] = $inner->show();
			}
			$outer->addTag('options',implode('',$options),false);
			if ($this->isAjax())
				return $this->ajaxReturn(array('status'=>true,'html'=>$outer->show()));
			else
				return $outer->show();
		}
		else {
			if ($this->isAjax())
				return $this->ajaxReturn(array('status'=>false,'html'=>""));
			else
				return "";
		}
	}

	function ipn() {
		if (!$module = $this->getModule())
			return "";
		$outer = new Forms();
		$outer->setModule($module);
		$outer->init($this->m_dir.$module['outer_html']);
		$inner = new Forms();
		$inner->init($this->m_dir.$module['inner_html']);
		if (count($_POST) > 0) {
			$inner->addTag('ipn',print_r($_POST,true),false);
			$inner->addData($_POST);
			if (array_key_exists('item_name',$_POST)) {
				$x = preg_match('/\d+/',$_POST['item_name'],$matches);
				$this->logMessage(__FUNCTION__,sprintf('in update code matches [%s]',print_r($matches,true)),1);
				if (count($matches) > 0) {
					$value = $_POST['mc_gross'];
					$sql = sprintf(sprintf('select * from orders where id = %d and total = %s',$matches[0],$value));
					$order = $this->fetchSingle($sql);
					$this->logMessage(__FUNCTION__,sprintf('order is [%s] from [%s]',print_r($order,true),$sql),1);
					if ($order['order_status'] == 1) {
						$order_id = $order['id'];
						$return = array();
						foreach($_POST as $key=>$value) {
							$return[] = sprintf('%s = %s',$key,$value);
						}
						$tmp = array('authorization_amount'=>$value,
							'authorization_type'=>'Paypal IPN',
							'authorization_code'=>$_POST['txn_id'],
							'authorization_info'=>implode("\n",$return),
							'order_status'=>STATUS_PROCESSING,
							'authorization_amount'=>$_POST['mc_gross']
						);
						$stmt = $this->prepare(sprintf('update orders set %s where id = %d',implode('=?,',array_keys($tmp)).'=?',$order_id));
						$stmt->bindParams(array_merge(array(str_repeat('s',count($tmp))),array_values($tmp)));
						$stmt->execute();
						$this->activateRecurring($order_id);
					}
				}
			}
		}
		$outer->addTag('form',$inner->show(),false);
		$s = new Snoopy();
		$parms = $GLOBALS['paypal'];
		$s->host = $parms['auth'];
		$s->port = 443;
		$s->httpmethod = 'POST';
		$vars = array_merge(array('cmd'=>'_notify-validate'),$_POST);
		$s->curl_path = $parms['curl_path'];
		$s->submit($parms['auth'],$vars);
		$emails = $this->configEmails("ecommerce");
		if (count($emails) == 0)
			$emails = $this->configEmails("contact");
		$mailer = new MyMailer();
		$mailer->Subject = sprintf("PayPal IPN Notification - %s", SITENAME);
		$body = new Forms();
		$body->setHTML($outer->show());
		$mailer->From = $emails[0]['email'];
		$mailer->FromName = $emails[0]['name'];
		$mailer->Body = $body->show();
		$mailer->IsHTML(true);	
		foreach($emails as $key=>$value) {
			$mailer->addAddress($value['email'],$value['name']);
		}
		if (count($_POST) > 0 && DEFINED('FRONTEND')) {
			if (!$mailer->Send()) {
				$this->logMessage(__FUNCTION__,sprintf("Email send failed [%s]",print_r($mailer,true)),1,true);
			}
			exit;
		}
	}

	private function news($module) {
		$this->logMessage(__FUNCTION__,sprintf('parms [%s]',print_r($module,true)),2);
		if ($folders = $this->fetchSingle(sprintf('select * from relations where owner_type = "news" and related_id = %d and related_type = "newsfolder"',$this->m_productId))) {
			$module['folder_id'] = $folders['related_id'];
		}
		else {
			$folders = array();
			$module['folder_id'] = 0;
			$this->logMessage(__FUNCTION__,sprintf('bail - no news for product %d',$this->m_productId),1);
		}
		if ($items = $this->fetchScalarAll(sprintf('select owner_id as news_id from relations where owner_type = "news" and related_id = %d and related_type = "product"',$this->m_productId)))
			$module['news_list'] = $items;
		else {
			$items = array();
			$this->logMessage(__FUNCTION__,sprintf('bail - no news for product %d',$this->m_productId),2);
		}
		if (count($folders) == 0 && count($items) == 0) {
			$this->logMessage(__FUNCTION__,sprintf('bail - no items or folders for product %d',$this->m_productId),1);
			return "";
		}
		$obj = new news($module['fetemplate_id'],$module);
		if (method_exists('news',$module['module_function'])) {
			$this->logMessage(__FUNCTION__,sprintf('invoking news with [%s]',print_r($module,true)),2);
			$html = $obj->{$module['module_function']}();
			return $html;
		}
		else {
			$this->logMessage(__FUNCTION__,sprintf('bail - no function [%s] in news for product %d',$module['module_function'],$this->m_productId),1,true);
		}
	}

	private function blog($module) {
		if (array_key_exists('product_id',$this->m_module))
			$p_id = $this->m_module['product_id'];
		else
			$p_id = $this->m_productId;
		if ($folders = $this->fetchSingle(sprintf('select * from relations where owner_type = "blogfolder" and related_id = %d and related_type = "product"',$p_id))) {
			$module['folder_id'] = $folders['related_id'];
		}
		else {
			$folders = array();
			$module['folder_id'] = 0;
			$this->logMessage(__FUNCTION__,sprintf('no blog folder for product %d',$this->m_productId),2);
		}
		if ($items = $this->fetchScalarAll(sprintf('select owner_id as blog_id from relations where owner_type = "blog" and related_id = %d and related_type = "product"',$p_id)))
			$module['blog_list'] = $items;
		else {
			$items = array();
			$this->logMessage(__FUNCTION__,sprintf('no blog for product %d',$p_id),2);
		}
		if (count($folders) == 0 && count($items) == 0) {
			$this->logMessage(__FUNCTION__,sprintf('bail - no items or folders for product %d',$p_id),1);
			return "";
		}
		$obj = new blog($module['fetemplate_id'],$module);
		if (method_exists('blog',$module['module_function'])) {
			$html = $obj->{$module['module_function']}();
			return $html;
		}
		else {
			$this->logMessage(__FUNCTION__,sprintf('bail - no function [%s] in blog for product %d',$module['module_function'],$p_id),1,true);
		}
	}

	function pricing() {
		if (!$module = $this->getModule())
			return "";
		if ($this->m_productId == 0) {
			$this->logMessage(__FUNCTION__,sprintf('called with no product id this [%s]',print_r($this,true)),1,true);
			return "";
		}
		$outer = new Forms();
		$outer->setModule($module);
		$outer->init($this->m_dir.$module['outer_html']);
		$inner = new Forms();
		$inner->init($this->m_dir.$module['inner_html']);
		if ($prod = $this->fetchSingle(sprintf('select * from product where deleted = 0 and enabled = 1 and published = 1 and id = %d',$this->m_productId))) {
			$prod = $this->formatData($prod);
			$outer->addData($prod);
			$prices = $this->fetchAll(sprintf('select * from product_pricing where product_id = %d order by min_quantity',$prod['id']));
			$lines = array();
			foreach($prices as $key=>$price) {
				$inner->reset();
				$inner->addData($this->formatPrice($price,$prod));
				$lines[] = $inner->show();
			}
			$outer->addTag('prices',implode('',$lines),false);
		}
		return $outer->show();
	}

	function formatPrice($price,$product) {
		if ($product['onSale'] == 1 && $price['sale_price'] > 0) {
			$price['formattedPrice'] = money_format('%(.2n',$price['sale_price']);
			$price['formattedRegular'] = money_format('%(.2n',$price['price']);
			$price['formattedDiscount'] = money_format('%(.2n',$price['price'] - $price['sale_price']);
			$price['formattedPercent'] = round(100*(1-($price['sale_price']) / $price['price']),1);
		}
		else {
			$price['formattedPrice'] = money_format('%(.2n',$price['price']);
		}
		if ($price['shipping'] > 0) {
			$price['formattedShipping'] = money_format('%(.2n',$price['shipping']);
		}
		$price['product'] = $product;
		$this->logMessage(__FUNCTION__,sprintf("return [%s]",print_r($price,true)),1);
		return $price;
	}

	function initRecurring($order_id) {
		$valid = true;
		foreach($_SESSION['recurring_items'] as $recKey=>$recurCart) {
			$term = $this->fetchSingle(sprintf('select * from code_lookups l where l.id = %d and l.type="recurringBilling"',$recKey));
			$term['options'] = explode('|',$term['extra']);	// start date|periods|auto-extend
			$recurCart['header']['recurring_period'] = $recKey;
			foreach($recurCart['products'] as $prodKey=>$product) {
				if (!$discount = $this->fetchSingle(sprintf('select * from product_recurring where id = %d and product_id = %d',$product['recurring_period'],$product['id']))) {
					$this->logMessage(__FUNCTION__,sprintf("can't find recurring [%s] for product [%s] for order [%s]",$product['recurring_period'],$product['id'],$order_id),1,true);
					$this->addError('An internal error occurred on the recurring billing. The Web Master has been notified');
					$valid = false;
				} else {
					$product['recurring_discount_rate'] = $discount['discount_rate'];
					$product['recurring_shipping_only'] = $discount['shipping_only'];
					$product['recurring_discount_type'] = $discount['percent_or_dollar'];
					$recurCart['products'][$prodKey] = $product;
				}
			}
			if ($valid) {
				$e = new Ecom();
				$recurCart = $e->updateCart($recurCart);
				$this->logMessage(__FUNCTION__,sprintf('recurring key [%s] cart [%s]',$recKey,print_r($recurCart,true)),1);
				if ($valid = $this->createOrder($recurCart,$recId)) {
					$tmpCart = Ecom::formatCart($recurCart);
					$recTmp = array(
						'authorization_type'=>$term['value'],
						'authorization_amount'=>$tmpCart['header']['total'],
						'authorization_code'=>'From order #'.$order_id,
						'authorization_transaction'=>$order_id,
						'order_status'=>STATUS_RECURRING | STATUS_INCOMPLETE
					);
					$recStmt = $this->prepare(sprintf('update orders set %s where id = %d',implode('=?,',array_keys($recTmp)).'=?',$recId));
					$recStmt->bindParams(array_merge(array(str_repeat('s',count($recTmp))),array_values($recTmp)));
					$valid = $valid && $recStmt->execute();
					$dt = date('Y-m-d');
					for($prd = 0; $prd < $term['options'][1]; $prd++) {
						$tmp = strtotime(sprintf('%s %s',$dt,$term['options'][0]));
						$this->logMessage(__FUNCTION__,sprintf('billing period [%s] from [%s]',$tmp,print_r($term,true)),1);
						$dt = date('Y-m-d', $tmp);
						$recTmp = array(
							'original_id'=>$recId,
							'billing_date'=>$dt,
							'period_number'=>$prd
						);
						$recStmt = $this->prepare(sprintf('insert into order_billing(%s) values(%s?)',
							implode(', ',array_keys($recTmp)),str_repeat('?, ',count($recTmp)-1)));
						$recStmt->bindParams(array_merge(array(str_repeat('s',count($recTmp))),array_values($recTmp)));
						$valid = $valid && $recStmt->execute();
					}
				}
			}
		}
		unset($_SESSION['recurring_items']);
		return $valid;
	}

	function activateRecurring($order_id) {
		$info = $this->fetchSingle(sprintf('select * from orders where id = %d',$order_id));
		$recurring = $this->fetchAll(sprintf('select * from orders where member_id = %d and authorization_transaction = "%d" and order_status & %d = %d',
						$info['member_id'],$info['id'],STATUS_INCOMPLETE,STATUS_INCOMPLETE));
		foreach($recurring as $key=>$rec) {
			$this->execute(sprintf('update orders set order_status = %d, cc_expiry="%s" where id = %d',($rec['order_status'] - STATUS_INCOMPLETE) | STATUS_PROCESSING,$info['cc_expiry'],$rec['id']));
			if (method_exists('activateRecurring','custom')) {
				$this->config->activateRecurring($rec['id']);
			}
		}
	}

	function reauthorize() {
		if (!$module = $this->getModule())
			return "";
		$o_id = 0;
		$cart = Ecom::getCart();
		if (array_key_exists('reauthorize',$cart) && array_key_exists('o_id',$cart['reauthorize'])) {
			$o_id = array_key_exists('o_id',$cart['reauthorize']) ? $cart['reauthorize']['o_id'] : 0;
			$r_id = array_key_exists('o_id',$cart['reauthorize']) ? $cart['reauthorize']['r_id'] : 0;
		}
		elseif (array_key_exists('o',$_REQUEST) && array_key_exists('r',$_REQUEST)) {
			$o_id = $_REQUEST['o'];
			$r_id = $_REQUEST['r'];
			$cart = Ecom::initCart();
			$cart['reauthorize'] = array('o_id'=>$o_id,'r_id'=>$r_id);
		}
		else {
			$o_id=0;
			$r_id=0;
		}
		$outer = new Forms();
		$outer->setModule($module);
		$outer->init($this->m_dir.$module['outer_html']);
		$status = true;
		if (!($order = $this->fetchSingle(sprintf('select * from orders where id = %d and member_id = %d and random = %d',$o_id,$this->getUserInfo('id'),$r_id)))) {
			$status = false;
			$this->addError('Sorry, We could not locate that order');
		}
		elseif (($order['order_status'] & (STATUS_EXPIRING | STATUS_RECURRING)) == 0) {
			$this->addError('This order does not need to be reauthorised yet');
			$status = false;
		}
		if (!$status)
			return $outer->show();
		$inner = new Forms();
		$inner->init($this->m_dir.$module['inner_html']);
		$inner->addData($this->formatOrder($order));
		$flds = $inner->buildForm($this->config->getFields($module['configuration']));
		if (count($_POST) > 0 && array_key_exists('reauthorize',$_POST)) {
			$hdr = $this->fetchSingle(sprintf('select * from orders where id = %d',$o_id));
			$dtls = $this->fetchAll(sprintf('select p.*, l.product_id, l.line_id, l.value, l.shipping, l.total, l.qty_multiplier, l.coupon_id, l.discount_rate, l.discount_value, l.shipping_only, l.discount_type, l.recurring_period, l.recurring_discount_rate, l.recurring_discount_value, l.recurring_shipping_only, l.recurring_discount_type, l.quantity, l.options_id, l.recurring_qty, l.price, l.price as regularPrice, l.recurring_period, l.color, l.size, 0 as inventory_id from order_lines l, product p where l.order_id = %d and p.id = l.product_id order by l.id',$o_id));
			$addresses = $this->fetchAll(sprintf('select * from addresses where ownertype = "order" and ownerid = %d order by id',$o_id));
			if ($exchange = $this->fetchSingle(sprintf('select * from exchange_rate where currency_id = %d and effective_date <= "%s" order by effective_date desc limit 1',$hdr['currency_id'],date("Y-m-d"))))
				$hdr['exchange_rate'] = $exchange['exchange_rate'];
			$cart['header'] = $hdr;
			$cart['header']['order_status'] |= STATUS_REAUTHORIZING;
			foreach($addresses as $key=>$address) {
				if ($address['tax_address'] == 1) {
					$cart['addresses']['shipping'] = $address;
				}
				else {
					$cart['addresses']['billing'] = $address;
				}
			}
			foreach($dtls as $key=>$product) {
				$cart['products'][$key] = $product;
				$cart['products'][$key]['id'] = $product['product_id'];
				$taxes = $this->fetchAll(sprintf('select * from order_taxes where order_id = %d and line_id = %d',$o_id,$product['line_id']));
				$cart['products'][$key]['taxdata'] = array();
				$cart['products'][$key]['taxes'] = 0;
				foreach($taxes as $subkey=>$tax) {
					$cart['products'][$key]['taxdata'][$tax['tax_id']] = $tax;
					$cart['products'][$key]['taxes'] += $tax['tax_amount'];
				}
			}
			$this->logMessage(__FUNCTION__,sprintf('return cart is [%s]',print_r($cart,true)),1);
			$cart = Ecom::recalcOrder($cart);
			if (strlen($module['parm1']) > 0) {
				$_SESSION['user']['lastOrder'] = $o_id;
				$inner->init($this->m_dir.$module['parm1']);
				$inner->addData($cart);
			}
		}
		$outer->addTag('order',$inner->show(),false);
		$_SESSION['cart'] = $cart;
		return $outer->show();
	}

	private function gallery($module) {
		$this->logMessage(__FUNCTION__,sprintf('parms [%s]',print_r($module,true)),2);
		if ($folders = $this->fetchSingle(sprintf('select * from relations where owner_type = "product" and owner_id = %d and related_type = "galleryfolder"',$this->m_productId))) {
			$module['folder_id'] = $folders['related_id'];
		}
		else {
			$folders = array();
			$module['folder_id'] = 0;
			$this->logMessage(__FUNCTION__,sprintf('bail - no news for product %d',$this->m_productId),1);
		}
		if (count($folders) == 0) {
			$this->logMessage(__FUNCTION__,sprintf('bail - no items or folders for product %d',$this->m_productId),1);
			return "";
		}
		$obj = new gallery($module['fetemplate_id'],$module);
		if (method_exists('gallery',$module['module_function'])) {
			$this->logMessage(__FUNCTION__,sprintf('invoking gallery with [%s]',print_r($module,true)),2);
			$html = $obj->{$module['module_function']}();
			return $html;
		}
		else {
			$this->logMessage(__FUNCTION__,sprintf('bail - no function [%s] in gallery for product %d',$module['module_function'],$this->m_productId),1,true);
		}
	}

	function relatedCategories() {
		if (!$module = $this->getModule())
			return "";
		$this->parseOptions($module['misc_options']);
		$outer = new Forms();
		$outer->init($this->m_dir.$module['outer_html']);
		if (array_key_exists('prod_id',$_REQUEST)) {
			$inner = new Forms();
			$inner->init($this->m_dir.$module['inner_html']);
			$sql = sprintf('select * from product_folders where enabled = 1 and id in (select related_id from relations where owner_type="product" and owner_id = %d and related_type="product_category")',$this->m_productId);
			if (strlen($module['sort_by']) > 0)
				$sql .= ' order by '.$module['sort_by'];
			if ($module['limit'] > 0)
				$sql .= ' limit '.$module['limit'];
			$folders = $this->fetchAll($sql);
			$this->logMessage(__FUNCTION__,sprintf('sql [%s] records [%d]',$sql,count($folders)),2);
			$return = array();
			foreach($folders as $folder) {
				$inner->reset();
				$inner->addData($this->formatFolder($folder));
				$subdata = $this->subForms($this->m_module['fetemplate_id'],null,array('folder_id'=>$folder['id'],'product_id'=>$this->m_productId),'inner');
				$this->logMessage(__FUNCTION__,sprintf('subforms [%s]',print_r($subdata,true)),3);
				foreach($subdata as $key=>$value) {
					$inner->addTag($key,$value,false);
				}
				$return[] = $inner->show();
			}
			$outer->addTag('folders',implode('',$return),false);
 			if (count($folders) > 0 || $this->hasOption('showAlways'))
	 			return $outer->show();
		}
	}

	function billingAgreement() {
		if (!$module = $this->getModule()) {
			return "";
		}
		$o_id = 0;
		if (array_key_exists("o_id",$_REQUEST) && array_key_exists("r",$_REQUEST)) {
			$o_id = $this->fetchScalar(sprintf("select id from orders where id = %d and member_id = %d and random = %d",
				$_REQUEST['o_id'], $this->getUserInfo("id"), $_REQUEST['r']));
			if ($o_id > 0) {
				$o_id = $this->fetchScalar(sprintf("select * from orders where authorization_transaction = '%d' and order_status & %d = %d",
					$o_id, STATUS_PROCESSING | STATUS_RECURRING, STATUS_PROCESSING | STATUS_RECURRING));
			}
			$_SESSION["billing_agreement"] = (int)$o_id;
		}
		else
			$o_id = array_key_exists("billing_agreement",$_SESSION) ? $_SESSION["billing_agreement"] : 0;
		$outer = new Forms();
		$outer->setModule($module);
		$outer->init($this->m_dir.$module["outer_html"]);
		$parms = $GLOBALS['payflow'];
		$outer->addData($parms);
		if (count($_REQUEST) > 0 && array_key_exists('token',$_REQUEST)) {
			$snoopy = new Snoopy();
			$query = array(
				'PARTNER'=>$parms['partner'],
				'VENDOR'=>$parms['vendor'],
				'PWD'=>$parms['pwd'],
				'USER'=>$parms['user'],
				'TRXTYPE'=>'A',
				'ACTION'=>'G',
				'TENDER'=>'P',
				'TOKEN'=>$_REQUEST['token']
			);
			$snoopy->host = $parms['auth'];
			$snoopy->port = 443;
			$snoopy->_submit_method = 'GET';
			$snoopy->curl_path = $parms['curl_path'];
			$snoopy->submit($parms['auth'],$query);
			$result = $this->depairOptions(urldecode($snoopy->results),array('&','='));
			$this->logMessage(__FUNCTION__,sprintf("get status returned [%s] from [%s]",print_r($result,true),print_r($snoopy,true)),2);

			//
			//	curl not supported with new ssl, back to Snoopy
			//
			//$tmp = tempnam("/tmp","ppd");
			//$data = array();
			//foreach($query as $key=>$value) {
			//	$data[] = sprintf("%s=%s",$key,$value);
			//}
			//$data = implode("&",$data);
			//$exec = sprintf("/usr/bin/curl -k -D \"%s\" -H \"User-Agent: Snoopy v1.2.4\" -H \"Host: %s:443\" -H \"Accept: image/gif, image/x-xbitmap, image/jpeg, image/pjpeg, */*\" -H \"Content-type: application/x-www-form-urlencoded\" -H \"Content-length: %d\" -d \"%s\" %s",
			//	$tmp, str_replace("https://","",$parms['auth']), strlen($data), $data, $parms['auth']);
			//exec($exec,$results,$return);
			//$result = $this->depairOptions(urldecode($results[0]),array('&','='));
			//$this->logMessage(__FUNCTION__,sprintf("token request [%s] returned [%s] tmp [%s]",$exec,print_r($results,true),$tmp),2);

			//
			//	result = 0 whether they accepted or not. only accepted have buyer info.
			//
			if (!(array_key_exists("RESULT",$result) && array_key_exists("PAYERID",$result))) {
				$this->logMessage(__FUNCTION__,sprintf("something went wrong getting the billing agreement result [%s]", print_r($results,true)),1,true);
				$outer->addFormError("Something went wrong. The Web Master has been notified");
			}
			else {
				if ($result['RESULT'] == 0 && array_key_exists('PAYERID',$result)) {
					$query['ACTION'] = 'X';
					//$snoopy->submit($parms['auth'],$query);

					$tmp = tempnam("/tmp","ppd");
					$data = array();
					foreach($query as $key=>$value) {
						$data[] = sprintf("%s=%s",$key,$value);
					}
					$data = implode("&",$data);					
					$arg = sprintf("-k -D \"%s\" -H \"User-Agent: Snoopy v1.2.4\" -H \"Host: %s:443\" -H \"Accept: image/gif, image/x-xbitmap, image/jpeg, image/pjpeg, */*\" -H \"Content-type: application/x-www-form-urlencoded\" -H \"Content-length: %d\" -d \"%s\" %s",
						$tmp, str_replace("https://","",$parms['auth']), strlen($data), $data, $parms['auth']);
					$exec = "/usr/bin/curl ".escapeshellarg($arg);
					exec($exec,$results,$return);
					$ba = array();
					foreach($results as $key=>$result) {
						if (strpos($result, "BAID") !== FALSE) {
							$ba = $this->depairOptions(urldecode($result),array('&','='));
						}
					}
					//$result = $this->depairOptions(urldecode($results[0]),array('&','='));
					$this->logMessage(__FUNCTION__,sprintf("billing agreement request [%s] results [%s] return [%s] tmp [%s]",$exec,print_r($results,true), print_r($return,true), $tmp),2);
					//$ba = $this->depairOptions(urldecode($results[1]),array('&','='));
					//$this->logMessage(__FUNCTION__,sprintf("create billing agreement returned [%s] from [%s]",print_r($ba,true),print_r($query,true)),2);

					//$ba = $this->depairOptions(urldecode($snoopy->results),array('&','='));
					//$this->logMessage(__FUNCTION__,sprintf("create billing agreement returned [%s] from [%s]",print_r($snoopy->results,true),print_r($query,true)),2);
					if (array_key_exists("RESULT",$ba) && $ba["RESULT"] == 0) {
						$orig_id = $this->fetchScalar(sprintf("select o1.authorization_transaction from orders o1 where o1.id = %s",$o_id));
						$stmt = $this->prepare(sprintf("update orders set order_status = order_status & ~%d, baid=?, ba_authorization_transaction = ?, ba_info = ? where id = ?",STATUS_CREDIT_HOLD));
						$data = array("ssss",$ba["BAID"], $ba["PNREF"], print_r($ba,true), $orig_id);
						$status = $stmt->bindParams($data);
						$status = $stmt->execute();
						$this->execute(sprintf("update orders set order_status = order_status & ~%d where id = %d",STATUS_CREDIT_HOLD,$o_id));
						$outer->addData($ba);
						$outer->init($this->m_dir.$module["inner_html"]);
						$subdata = $this->subForms($this->m_module['fetemplate_id'],null,array(),'inner');
						$this->logMessage(__FUNCTION__,sprintf('subforms [%s]',print_r($subdata,true)),3);
						foreach($subdata as $key=>$value) {
							$outer->addTag($key,$value,false);
						}
						unset($_SESSION['billing_agreement']);
					}
					else {
						if ($result['RESULT'] != 0 && $result['RESULT'] != 12) {
							$this->addError(sprintf("Oops. Something went wrong. The Web Master has been notified [%s]",array_key_exists('RESPMSG',$ba) ? $ba['RESPMSG']: $ba['RESULT']));
							$this->logMessage(__FUNCTION__,sprintf("create billing agreement failed [%s] returned [%s]",
								print_r($query,true), print_r($ba,true)),1,true);
						}
						else {
							$this->addError(sprintf("It appears you did not accept the agreement.<br/>To receive future shipments you must accept it.<br/>%s",array_key_exists('RESPMSG',$ba) ? $ba['RESPMSG']: "Unknown Error"));
							$this->logMessage(__FUNCTION__,sprintf("PayPal billing agreement was declined result [%s] ba [%s]", print_r($result,TRUE), print_r($ba,TRUE)),1,TRUE);
							$outer->addData($result);
						}
						$result['RESULT'] = $ba['RESULT'];
					}
				}
				else {
					if ($result['RESULT'] != 0) {
						$this->addError(sprintf("Oops. Something went wrong. The Web Master has been notified [%s]",array_key_exists('RESPMSG',$result) ? $result['RESPMSG']: $result['RESULT']));
						$this->logMessage(__FUNCTION__,sprintf("create billing agreement failed [%s]", print_r($result,true)),1,true);
					}
					else {
						$this->addError("It appears you did not accept the agreement.<br/>To receive future shipments you must accept it.");
						$this->logMessage(__FUNCTION__,sprintf("PayPal billing agreement was declined result [%s]", print_r($result,TRUE)),1,TRUE);
						$outer->addData($result);
					}
				}
				if (array_key_exists('RESULT', $result) && $result['RESULT'] != 0) {
					//
					//	generate a new token
					//
					$t = $this->generatePayPalToken($o_id);
					$outer->addData($t);
				}
			}
		}
		//else {
			$t = $this->generatePayPalToken($o_id);
			$outer->addData($t);
		//}
		return $outer->show();
	}

	function generatePayPalToken($o_id) {
		//
		//	1st pass - get a token so we can direct them to paypal
		//
		$parms = $GLOBALS['payflow'];
		$snoopy = new Snoopy();
		$order = $this->fetchSingle(sprintf("select o.*, c.value as recurringDesc from orders o, code_lookups c where o.id = %d and c.id = o.recurring_period",$o_id));
		$lines = $this->fetchAll(sprintf("select l.*, p.name from order_lines l, product p where l.order_id = %d and p.id = l.product_id",$o_id));
		$query = array(
			'PARTNER'=>$parms['partner'],
			'VENDOR'=>$parms['vendor'],
			'PWD'=>$parms['pwd'],
			'USER'=>$parms['user'],
			'TRXTYPE'=>'A',
			'ACTION'=>'S',
			'TENDER'=>'P',
			'CANCELURL'=>sprintf("http://%s/%s",HOSTNAME,$this->getOption("cancelUrl")),
			'RETURNURL'=>sprintf("http://%s/%s",HOSTNAME,$this->getOption("returnUrl")),
			'AMT'=>$order['total'],
			'BA_DESC'=>'Recurring Billing Agreement',
			'L_BILLINGTYPE0'=>'MerchantInitiatedBilling',
			'L_BILLINGAGREEMENTDESCRIPTION0'=>sprintf('Recurring Billing of Order #%d',$o_id),
			'PAYMENTTYPE'=>'instantonly',
			'CURRENCY'=>$parms["currency"]
		);
		$amt = 0;
		$coupon = 0;
		$recurring = 0;
		$max = 0;
		$names = array();
		/*
		 *	known paypal bug that won't let us use a -ive amout for the coupon discount
		 *	when this is fixed we can put the item details back in
		 */
		foreach($lines as $key=>$value) {
			//$query[sprintf('L_NAME%d',$key)] = $value["name"];
			//$query[sprintf('L_DESC%d',$key)] = $value["name"];
			//$query[sprintf('L_COST%d',$key)] = (float)$value['price'];
			$amt += (float)$value['price']*$value['qty_multiplier'];
			$coupon += (float)$value['discount_value'];
			$recurring += (float)$value['recurring_discount_value'];
			$names[] = $value["name"];
			//$query[sprintf('L_QTY%d',$key)] = (int)$value['quantity'];
			$max += 1;
		}
		if ($order['line_discounts'] != 0) {
			//$query[sprintf('L_NAME%d',$max)] = "Line Discounts";
			//$query[sprintf('L_DESC%d',$max)] = "Line Discounts";
			//$query[sprintf('L_COST%d',$max)] = sprintf("%0.3f",$order['line_discounts']);
			//$query[sprintf('L_QTY%d',$max)] = 1;
			$max += 1;
		}
		if ($order["discount_value"] != 0) {
			//$query[sprintf('L_NAME%d',$max)] = "CouponDiscounts";
			//$query[sprintf('L_DESC%d',$max)] = "Coupon Discounts";
			//$query[sprintf('L_COST%d',$max)] = (float)$order["discount value"];
			//$query[sprintf('L_QTY%d',$max)] = 1;
			$max += 1;
		}
		$query['L_NAME0'] = $order["recurringDesc"];
		$query['L_DESC0'] = implode(", ",$names);
		$query['L_COST0'] = $amt + $recurring + $order['discount_value'];
		$query['L_QTY0'] = 1;
		$query["ITEMAMT"] = $amt + $recurring + $order['discount_value'];
		$query["TAXAMT"] = $order['taxes'];
		$query["HANDLINGAMT"] = $order["handling"];
		$query["FREIGHTAMT"] = $order["shipping"];
		$tmp = tempnam("/tmp","ppd");
		$data = "";
		//
		//	Payflow side we can't use Snoopy - it urlencodes which breaks paypal
		//
		foreach($query as $key=>$value) {
			if (strlen($data) > 0) $data .= "&";
			if (is_string($value))
				$data .= sprintf("%s[%d]=%s",$key,strlen($value),$value);
			else
				if (is_float($value))
					$data .= sprintf("%s=%0.2f",$key,$value);
				else
					$data .= sprintf("%s=%s",$key,$value);
		}		
		$arg = sprintf("-k -D \"%s\" -H \"User-Agent: Snoopy v1.2.4\" -H \"Host: %s:443\" -H \"Accept: image/gif, image/x-xbitmap, image/jpeg, image/pjpeg, */*\" -H \"Content-type: application/x-www-form-urlencoded\" -H \"Content-length: %d\" -d \"%s\" %s",
			$tmp, str_replace("https://","",$parms['auth']), strlen($data), $data, $parms['auth']);			
		$exec = "/usr/bin/curl ".escapeshellarg($arg);
		exec($exec,$results,$return);
		$t = $this->depairOptions(urldecode($results[0]),array('&','='));
		$this->logMessage(__FUNCTION__,sprintf("token request [%s] returned [%s]",$exec,print_r($results,true)),2);
		if (!(array_key_exists('RESULT',$t) || $t['RESULT'] != 0 || !array_key_exists('TOKEN',$t))) {
			$this->logMessage(__FUNCTION__,sprintf("get token failed input [%s] returned [%s]", $exec, print_r($t,true)),1,true);
		}
		return $t;
	}

	function reviews() {
		if (!$module = $this->getModule()) {
			return "";
		}
		$outer = new Forms();
		$outer->setModule($module);
		$outer->init($this->m_dir.$module["outer_html"]);
		$product = $this->fetchSingle(sprintf("select * from product where id = %d",$this->m_productId));
		$outer->addData($this->formatData($product));
		$sql = sprintf("select * from product_reviews where product_id = %d and approved = 1",$this->m_productId);
		if (strlen($module["sort_by"]) > 0) $sql = sprintf("%s order by %s", $sql, $module["sort_by"]);
		$pagination = $this->getPagination($sql,$module,$recordCount);
		$reviews = $this->fetchAll($sql);
		$inner = new Forms();
		$inner->init($this->m_dir.$module['inner_html']);
		$flds = $inner->buildForm($this->config->getFields($module['configuration']));
		$result = array();
		foreach($reviews as $key=>$review) {
			$inner->addData($this->formatReview($review));
			$result[] = $inner->show();
		}
		$outer->addTag("comments",implode("",$result),false);
		$outer->addTag("pagination",$pagination,false);
		return $outer->show();
	}

	function writeaReview() {
		if (!$module = $this->getModule()) {
			return "";
		}
		$outer = new Forms();
		$outer->setModule($module);
		$outer->init($this->m_dir.$module["outer_html"]);
		if ($product = $this->fetchSingle(sprintf("select * from product where id = %d",array_key_exists("product_id",$_REQUEST) ? $_REQUEST["product_id"] : $this->m_productId)))
			$product = $this->formatData($product);
		$outer->addData($product);
		$inner = new Forms();
		$inner->init($this->m_dir.$module['inner_html'],array('name'=>'writeaReview'));
		$flds = $inner->buildForm($this->config->getFields($module['configuration']));
		$inner->addData(array("product"=>$product));
		if (count($_POST) > 0 && array_key_exists("writeaReview",$_POST)) {
			$inner->addData($_POST);
			$valid = $inner->validate();
			if ($valid) {
				if ($inner->getData('r_id') != $_SESSION['forms'][$inner->getOption('name')]['r_secret']) {
					$valid = false;
					$this->addError('An internal error occurred');
					$this->logMessage(__FUNCTION__,sprintf('possible spam [%s]',print_r($_POST,true)),1,true);
				}
			}
			//
			//	stop reposts from a refresh
			//
			$inner->setData('r_secret',rand());
			$_SESSION['forms'][$inner->getOption('name')]['r_secret'] = $inner->getData('r_secret');
			if ($valid) {
				$tmp = array();
				foreach($flds as $key=>$fld) {
					if (!(array_key_exists('database',$fld) && $fld['database'] == false)) {
						$tmp[$fld["name"]] = $inner->getData($fld["name"]);
					}
				}
				$tmp["rand"] = rand(0,1000000);
				$tmp["comment"] = sprintf("<p>%s</p>",str_replace("\n","<br/>",strip_tags($tmp["comment"])));
				$stmt = $this->prepare(sprintf("insert into product_reviews(%s,created) values(%s?,now())", implode(",",array_keys($tmp)),str_repeat("?,",count($tmp)-1)));
				$stmt->bindParams(array_merge(array(str_repeat("s",count($tmp))),array_values($tmp)));
				if ($stmt->execute()) {
					$c_id = $this->insertId();
					if (strlen($module["parm1"]) > 0) $inner->init($this->m_dir.$module["parm1"]);
					if (strlen($module["parm2"]) > 0)
						$inner->addFormSuccess($module["parm2"]);
					else
						$inner->addFormSuccess("Your comment has been accepted for review");
					$this->logMessage(__FUNCTION__,sprintf("hasemail test [%s] from [%s]",$this->hasOption("email"),print_r($this,true)),1);
					if ($this->hasOption("email")) {
						$emails = $this->configEmails("ecommerce");
						if (count($emails) == 0) $emails = $this->configEmails("contact");
						$body = new Forms();
						$mailer = new MyMailer();
						$mailer->Subject = sprintf("Product Review - %s", SITENAME);
						$body = new Forms();
						$html = $this->fetchSingle(sprintf('select * from htmlForms where class = %d and type = "%s"',$this->getClassId('product'),$this->getOption('email')));
						$body->setHTML($html['html']);
						$body->addData(array("product"=>$product));
						$body->addData($inner->getAllData());
						$body->addData($tmp);
						$body->addTag('c_id',$c_id);
						$body->setOption('formDelimiter','{{|}}');
						$mailer->Body = $body->show();
						$mailer->From = 'noreply@'.HOSTNAME;	//$inner->getData('email');
						$mailer->FromName = $inner->getData('firstname').' '.$inner->getData('lastname');
						$mailer->AddReplyTo($inner->getData('email'),$inner->getData('firstname').' '.$inner->getData('lastname'));
						$mailer->IsHTML(true);
						foreach($emails as $key=>$value) {
							$mailer->addAddress($value['email'],$value['name']);
						}
						if (!$mailer->Send()) {
							$this->logMessage(__FUNCTION__,sprintf("Email send failed [%s]",print_r($mailer,true)),1,true);
						}
					}
				}
			}
			else {
				$inner->addFormError('An Error Occurred');
				$inner->setData('r_id',$_SESSION['forms'][$inner->getOption('name')]['r_secret']);
			}
		}
		else {
			//
			//	every non-post change the random id
			//
			$this->logMessage(__FUNCTION__,sprintf('changing secret to [%s]',$flds['r_id']['value']),1);
			$_SESSION['forms'][$inner->getOption('name')]['r_secret'] = $flds['r_id']['value'];
		}
		$outer->addTag("form",$inner->show(),false);
		return $outer->show();
	}

	function commentApproval() {
		if (!$module = $this->getModule()) {
			return "";
		}
		$outer = new Forms();
		$outer->setModule($module);
		$outer->init($this->m_dir.$module["outer_html"]);
		$rand = array_key_exists("rand",$_REQUEST) ? $_REQUEST["rand"] : 0;
		$comment = array_key_exists("c_id",$_REQUEST) ? $_REQUEST["c_id"] : 0;
		$approval = array_key_exists("c_approval",$_REQUEST) ? $_REQUEST["c_approval"] : 0;
		if ($review = $this->fetchSingle(sprintf("select * from product_reviews where id = %d and rand = %d",$comment,$rand))) {
			$this->execute(sprintf("update product_reviews set approved = 1 where id = %d and rand = %d",$comment,$rand));
			$outer->addFormSuccess("The Comment has been approved");
			$outer->addData($review);
		}
		else {
			$outer->addFormError("We couldn't locate that comment for approval");
		}
		return $outer->show();
	}

	public function divListing() {
		if (!$module = parent::getModule())
			return "";
		$this->parseOptions($module['misc_options']);
		if ($module['folder_id'] != 0)
			$root = $this->fetchSingle(sprintf('select * from product_folders where id = %d',$module['folder_id']));
		else
			$root = array('left_id'=>0,'right_id'=>999999,'level'=>0,'id'=>0,'title'=>'');
		$menu = $this->buildDiv($root,$module,0);
		$outer = new Forms();
		$outer->setModule($module);
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

	private function buildDiv($root,$module,$root_level) {
		$this->logMessage("buildDiv",sprintf("root [%d] root_level [%d]",$root['id'],$root_level),2);
		if ($this->hasOption('maxLevel') && $root_level >= $this->getOption('maxLevel')) {
			$this->logMessage(__FUNCTION__,sprintf('max level exceeded',$this->getOption('maxLevel')),2);
			return "";
		}
		if ($this->hasOption('maxLevel') && $root_level <= $this->getOption('maxLevel') - 1 && !$this->hasOption('showFolders')) {
			$this->logMessage(__FUNCTION__,'skipping folders as per config',1);
			$level = $this->fetchAll(sprintf('select * from product_folders where level = %d and left_id > %d and right_id < %d and enabled = 1 order by left_id',$root['level']+1,$root['left_id'],$root['right_id']));
		}
		else
			$level = $this->fetchAll(sprintf('select * from product_folders where level = %d and left_id > %d and right_id < %d and enabled = 1 order by left_id',$root['level']+1,$root['left_id'],$root['right_id']));
		$form = new Forms();
		$form->init($this->m_dir.$module['inner_html']);
		$menu = array();
		if ($this->hasOption('grpPrefix')) $menu[] = $this->getOption('grpPrefix');
		$seq = 0;
		$ct = 0;
		foreach($level as $key=>$item) {
			$ct += 1;
			if ($module['rows'] > 0 && $ct > $module['columns']) {
				$ct = 1;
				if ($this->hasOption('grpSuffix')) $menu[] = $this->getOption('grpSuffix');
				if ($this->hasOption('grpPrefix')) 
					$menu[] = $this->getOption('grpPrefix');
				else
					$menu[] = '<div class="clearfix"></div>';
			}
			$form->reset();
			if ($seq == 0) $form->addTag("first","first");
			if ($seq == (count($level) - 1)) $form->addTag("last","last");
			$seq += 1;
			$form->addData($this->formatFolder($item));
			$form->addTag('sequence',$seq);
			$form->addTag('level',$root_level+1);
			if (($subMenu = $this->buildDiv($item,$module,$root_level+1)) != '') {
				$form->addTag("submenu",$subMenu,false);
				//$tmp .= sprintf('%s',$subMenu);
			}
			$tmp = $form->show();
			if ($this->hasOption('delim') && $seq < count($level)) $tmp .= sprintf('<span class="delim">%s</span>',$this->getOption('delim'));
			$menu[] = sprintf('%s',$tmp);
		}
		if ($this->hasOption('grpSuffix')) $menu[] = $this->getOption('grpSuffix');
		$this->logMessage(__FUNCTION__,sprintf("return [%s]",print_r($menu,true)),4);
		return implode("",$menu);
	}

	function getInventory() {
		if (!$module = $this->getModule()) {
			return "";
		}
		$outer = new Forms();
		$outer->setModule($module);
		$outer->init($this->m_dir.$module["outer_html"]);
		$inner = new Forms();
		$inner->setModule($module);
		$prod = $_REQUEST["p_id"];
		$option = array_key_exists("opt_id",$_REQUEST) ? $_REQUEST["opt_id"] : 0;
		$color = array_key_exists("c_id",$_REQUEST) ? $_REQUEST["c_id"] : 0;
		$size = array_key_exists("s_id",$_REQUEST) ? $_REQUEST["s_id"] : 0;
		$invAmt = $this->fetchSingle(sprintf("select i.* from product_inventory i where product_id = %d and options_id = %d and color = %d and size = %d and (start_date = '0000-00-00' or start_date <= CURDATE()) and (end_date = '0000-00-00' or end_date >= CURDATE()) and deleted = 0",$prod,$option,$color,$size));
		if (is_array($invAmt) && count($invAmt) > 0) {
			$inner->init($this->m_dir.$module["inner_html"]);
			$inner->addData($invAmt);
		}
		else{
			$inner->init($this->m_dir.$module["parm1"]);
		}
		$outer->addTag("inventory",$inner->show(),false);
		return $outer->show();
	}

	function buildAssembly() {
		if (!$module = parent::getModule())
			return "";
		$outer = new Forms();
		$outer->init($this->m_dir.$module["outer_html"]);
		$outer->setModule($module);
		$inner = new Forms();
		$inner->init($this->m_dir.$module["inner_html"]);
		$inner->setModule($module);
		$flds = $this->config->getFields($module["configuration"]);
		$p_id = 0;
		if (array_key_exists("prod_id",$_REQUEST)) $p_id = $_REQUEST["prod_id"];
		if (array_key_exists("prod_id",$module)) $p_id = $module["prod_id"];
		if ($p_id == 0) return "";
		$f_id = $module["folder_id"];
		if ($f_id == 0 && array_key_exists("cat_id",$module)) $f_id = $module["cat_id"];
		$prod = $this->fetchSingle(sprintf("select * from product where id = %d", $p_id));
		if ($f_id > 0) {
			$folder = $this->fetchSingle(sprintf("select * from product_folders where id = %d", $f_id));
			$prod["folder"] = self::formatFolder($folder);
		}
		$outer->addData(self::formatData($prod));
		$products = $this->fetchAll(sprintf("select p.*, pp.quantity as multiplier from product p, product_by_package pp where p.id = pp.subproduct_id and pp.product_id = %d order by pp.sequence", $p_id));
		$recs = array();
		foreach($products as $key=>$value) {
			$inner->buildForm($flds);
			$inner->addData(self::formatData($value));
			$recs[] = $inner->show();
		}
		$outer->setData("products",implode("",$recs));
		return $outer->show();
	}

	function redeemPoints() {
		if (!$module = $this->getModule())
			return "";
		$outer = new Forms();
		$outer->init($this->m_dir.$module['outer_html']);
		$inner = new Forms();
		$inner->setModule($module);
		$inner->init($this->m_dir.$module['inner_html']);
		$flds = $this->config->getFields($module["configuration"]);
		$inner->buildForm($flds);
		$pts = (int)$this->fetchScalar(sprintf("select sum(points) from members_points where member_id = %d", $this->getUserInfo("id")));
		$total_pts = $pts;
		$outer->setData("points",$pts);
		$inner->setData("points",$pts);
		$prices = $this->fetchAll(sprintf("select * from product_pricing pr, product p where p.code = '%s' and pr.product_id = p.id order by pr.min_quantity",REDEMPTION_PRODUCT));
		$amt = 0;
		foreach($prices as $k=>$v) {
			if ($pts > 0) {
				$net = min($pts,$v["max_quantity"]);
				$amt += $net*$v["price"];
				$pts -= $net;
			}
		}
		if (array_key_exists(__FUNCTION__,$_POST)) {
			$inner->addData($_POST);
			if ($inner->validate()) {
				$this->logMessage(__FUNCTION__,sprintf("made it past validation"),1);
				$to_redeem = min($_POST["to_redeem"], $total_pts);
				$e = new Ecom();
				$cart = $e->getCart();
				$cart['header']['points_redeemed'] = $to_redeem;
				$cart['header']['redemption_value'] = -min($net,$cart['header']['value']);
				$cart = $e->updateCart($cart);
				$_SESSION['cart'] = $cart;
				$this->logMessage(__FUNCTION__,sprintf("updated cart [%s]", print_r($cart,true)), 1);
				if (strlen($module["parm1"]) > 0) {
					$inner->init($this->m_dir.$module["parm1"]);
				}
			}
		}
		$outer->setData("amount",$amt);
		$inner->setData("amount",$amt);
		$outer->addTag("form",$inner->show(),false);
		return $outer->show();
	}

	function getModuleInfo() {
		return parent::getModuleList(array('products','listing','details','cartInfo','shippingAddress','billingAddress','payment','orderHistory','orderDetails','receipt','search','relatedProducts','productOptions','folderRelations','itemRelations','sizeOrColor','ipn','pricing','reauthorize','relatedCategories','billingAgreement','reviews','writeaReview','commentApproval','divListing','getInventory','buildAssembly','redeemPoints'));
	}

}

?>
