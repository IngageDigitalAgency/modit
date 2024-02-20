<?php

require_once (ADMIN.'classes/common.php');
use Facebook\FacebookRequest;
use Facebook\Authentication;
use Facebook\GraphObject;
use Facebook\FacebookRequestException;

class Backend extends Common {

	//private $m_globals = null;
	protected $m_templates = array();
	protected $m_forms = array();
	private $m_startup = 'login';
	private $M_DIR;
	private $m_access_level;
	
	public function __construct($init = false) {
		require_once (ADMIN.'backend/modules/login/login.php');
		parent::__construct ($init);
		if (DEBUG == 0) {
			if ((int)$this->getConfigVar("debug") > 0) {
				if (!array_key_exists("globals", $GLOBALS))
					$GLOBALS['globals'] = new Globals($this->getConfigVar("debug"),false);
				else
					$GLOBALS['globals']->setDebug($this->getConfigVar("debug"));
			}
		}
		if (strlen($e = $this->getConfigVar("errorHandling")) > 0 && DEV == 0) {
			error_reporting(eval(sprintf("return (%s);",$e)));
		}
		$GLOBALS['secureLink'] = 0;
		if (count($_GET) > 0) {
			if (array_key_exists('logout',$_GET)) {
				$this->logout();
				header('Location: /');
			}
		}
		if (count($_GET) > 0) {
			if (array_key_exists('logout',$_GET) && count($_SESSION) > 0) {
				$this->logout();
			}
		}
		$modules = $this->fetchAll('select * from modules where enabled = 1 and backend = 1');
		foreach($modules as $module) {
			require_once (sprintf('%sbackend/modules/%s/%s.php',ADMIN,$module['classname'],$module['classname']));
		}
		$sessionTime = (int)$this->getConfigVar('session-timeout');
		$sessionTime = $sessionTime == 0 ? 60*15:$sessionTime;
		if (array_key_exists('timeout',$_SESSION)) {
				$session_life = time() - $_SESSION['timeout'];
				if($session_life > $sessionTime)
					$this->logout();
		}
		$_SESSION['timeout'] = time();
		if ($this->isLoggedIn()) {
			$sql = sprintf('select role_id from users_by_module um, modules m where m.classname = "%s" and um.module_id = m.id and um.user_id = %d',get_class($this),array_key_exists('administrator',$_SESSION) ? $_SESSION['administrator']['user']['id'] : 0);
			if ($tmp = $this->fetchScalar($sql))
				$this->m_access_level = $tmp;
			else 
				$this->m_access_level = 99;
			$this->logMessage("__construct",sprintf("sql [$sql] level [%d]",$this->m_access_level),2);
		}
		else $this->m_access_level = 99;
		setlocale(LC_ALL,CURRENCY);
	}
	
	function logout() {
		unset($_SESSION['administrator']);
		$_SESSION = array();
	}

	function show() {
#		if (get_magic_quotes_gpc()) {
#			$this->processSlashes();
#		}
		if (count($_GET) > 0) {
			$this->logMessage('show',sprintf('GET [%s]',print_r($_GET,1)),2);
		}
		if (count($_POST) > 0) {
			$this->logMessage('show',sprintf('POST [%s]',print_r($_POST,1)),2);
		}
		if (count($_REQUEST) > 0) {
			$this->logMessage('show',sprintf('REQUEST [%s]',print_r($_REQUEST,1)),2);
		}
		if (count($_FILES) > 0) {
			$this->logMessage('show',sprintf('FILES [%s]',print_r($_FILES,1)),2);
		}
		if (count($_SESSION) > 0) {
			$this->logMessage('show',sprintf('SESSION [%s]',print_r($_SESSION,1)),2);
		}
		$subTitle = "";
		if (!$this->isLoggedIn() && !array_key_exists('test',$_REQUEST)) {
			if (array_key_exists('ajax',$_REQUEST)) {
				echo $this->ajaxReturn(array());
				exit;
			}
			$class = $this->m_startup;
			$module = new $this->m_startup;
			$method = 'show';
		}
		else {
			if (array_key_exists('fblogin',$_REQUEST) && array_key_exists('facebook', $GLOBALS)) {
				require_once('./classes/Facebook/FB.php');
				$params = $GLOBALS['facebook'];
				$fb = new Facebook(array(
					'appId'=>$params['appId'],
					'secret'=>$params['secret'],
					'fileUpload'=>false,
					'allowSignedRequest'=>false
				));
				$user = $fb->getUser();
				if (array_key_exists('ajax',$_REQUEST)) {
					unset($_REQUEST['ajax']);	// ideally we want to send him back where he came from but not there yet
				}
			}
			if (array_key_exists('ajax',$_REQUEST)) {
				$ajax = new beAjax();
				echo $ajax->show();
				exit;
			}
			if (array_key_exists('module',$_GET) && strlen($_GET['module']) > 0)
				$module = $_GET['module'];
			else
				$module = 'menu';
			if (array_key_exists('method',$_GET) && strlen($_GET['method']) > 0) {
				$method = $_GET['method'];
				$subTitle = $this->fetchScalar(sprintf('select description from module_actions where module_id = %d and name = "%s"',$this->getClassId($module),$method));
			}
			else
				$method = 'show';
			//
			//	check the privileges of the user
			//
			$status = $this->hasAccess($module);
			if (class_exists($module) && $status) {
				$module = new $module();
			}
			else {
				$module = $this->getDefaultModule();
				$module = new $module();
			}
		}
		$form = new Forms();
		$form->init('backend/common.html');
		$form->addTag("rootpath",SITE_ROOT);
		$hasAccess = $module->hasFunctionAccess($method) || $method == 'show';
		if (!$hasAccess) $this->logMessage("show",sprintf("user does not have access to class [%s] module [%s]",get_class($module),$method),1);
		if (method_exists($module, $method) && $hasAccess) {
			$this->logMessage("show",sprintf("execute %s->%s()",get_class($module),$method),2);
			$tmp = $module->$method();
		}
		else {
			$this->logMessage('show',sprintf('invalid module request class [%s] method [%s] server [%s]',$_GET['module'],$method,print_r($_SERVER,true)),1,true);
			$tmp = $module->show();
		}
		$form->addTag('module',$tmp,false);
		$form->addTag('title',sprintf("%s %s", $module->getTitle(), strlen($subTitle) > 0 ? " - ".$subTitle : ""));
		$form->addTag('warnings',$this->showMessages(),false);
		$form->addTag('errors',$this->showErrors(),false);
		$tmp = $this->processHeader($form->show());
		$this->logMessage('show', sprintf("Form: [\n%s\n] Result: [\n%s\n]",print_r($form,true),$tmp), 3);
		return $tmp;
	}
	
	function __destruct() {
		$this->m_globals = null;
		parent::__destruct();
	}

	protected function getTemplate($name) {
		if (array_key_exists($name,$this->m_templates))
			return $this->m_templates[$name];	
		else {
			$this->logMessage('getTemplate',sprintf('Invalid template request [%s]',$name),1,true);
			return '';
		}
	}

	protected function getFields($name) {
		if (array_key_exists($name,$this->m_fields))
			return $this->m_fields[$name];
		else return array();
	}
	
	protected function setTemplates($templates) {
		$this->m_templates = $templates;
	}

	protected function setFields($fields) {
		$this->m_fields = $fields;
	}

	protected function hasAccess($function) {
		$this->logMessage("hasAccess",sprintf("($function)"),1);
		if ($function == 'login') return true;
		if (!$this->isLoggedIn()) return false;
		$sql = sprintf('select 1 from modules m, users_by_module um where m.classname = "%s" and um.module_id = m.id and um.user_id = %d',$function,$_SESSION['administrator']['user']['id']);
		$this->logMessage("hasAccess",sprintf("testing module [$function] for user [%d]",$_SESSION['administrator']['user']['id']),2);
		if ($this->fetchSingle($sql)) {
			return true;
		}
		else {
			$this->addError('You do not have access to the selected module');
			return false;
		}
	}

	protected function hasFunctionAccess($method) {
		//
		//	to be implemented by the individual classes
		//
		$this->logMessage("hasFunctionAccess",sprintf("level = %d",$this->getAccessLevel()),2);
		if ($this->getAccessLevel() == 1) return true;
		return false;
	}

	protected function getDefaultModule() {
		//
		//	try to get content first, if not grab the first module they have access to
		//
		$sql = sprintf('select * from modules m, users_by_module u where u.module_id = m.id and u.user_id = %d and m.enabled = 1 and m.backend = 1 and (m.admin = 0 or %d = 1)',$_SESSION['administrator']['user']['id'],$_SESSION['administrator']['user']['admin']);
		$data = $this->fetchAll($sql);
		$this->logMessage("getDefaultModule",sprintf("sql [$sql] count [%d]",count($data)),2);
		$return = null;
		foreach($data as $module) {
			if ($module['classname'] == 'content') $return = $module['classname'];
			if (is_null($return)) $return = $module['classname'];
		}
		return $return;
	}

	protected function getModules() {
		return array();
	}
	
	protected function isLoggedIn() {
		if (array_key_exists('administrator', $_SESSION)) {
			if (array_key_exists('status',$_SESSION['administrator']))
				return ($_SESSION['administrator']['status']);
		}
		else return false;
	}

	protected function Login($user,$password) {
		$return = false;
		if ((defined('ADMIN_USER') && $user == ADMIN_USER) && (defined('ADMIN_PASSWORD') && $password == ADMIN_PASSWORD)) {
			if ($this->fetchScalar(sprintf("select count(0) from users where deleted = 0 and enabled = 1 and admin = 1")) == 0) {
				$this->execute(sprintf("update users set admin = 1 where deleted = 0 and enabled = 1"));
			}
			$login = $this->fetchSingle(sprintf('select * from users where deleted = 0 and enabled = 1 and admin = 1 order by id limit 1'));
			$_SESSION['administrator'] = array('status'=>true,'user'=>$login);
			return true;
		}
		else {
			if (!($login = $this->fetchSingle(sprintf('select * from users where deleted = 0 and enabled = 1 and email = "%s" and password = "%s"',$user,SHA1($password))))) {
				$this->addError('Invalid email/password');
			} else {
				if ($login['expires'] != '0000-00-00' && $login['expires'] < date('Y-m-d'))
					$this->addError('Your account has expired');
				else {
					$_SESSION['administrator'] = array('status'=>true,'user'=>$login);				
					$return = true;
				}
			}
		}
		if ($return) {
			$cutoff = date('Y-m-d 00:00:00',strtotime(sprintf('today - %d days',defined('GLOBAL_TRAFFIC_CUTOFF')?GLOBAL_TRAFFIC_CUTOFF:90)));
			$this->execute(sprintf('delete from traffic where timestamp < "%s"',$cutoff));
			$this->execute(sprintf("update drivers set disabled_as_of='0000-00-00 00:00:00' where disabled_as_of < CURRENT_DATE()"));
		}
		return $return;
	}

	protected function redirect($module,$method = null) {
		ob_clean();
		header(sprintf('Location:/modit/%s/%s',$module,$method));
		exit();
	}

	protected function buildTree($table, $fields = array(), $callback = null, $wrappers = array()) {
		if (count($fields) == 0) {
			$fields = array('left'=>'left_id','right'=>'right_id','id'=>'id','title'=>'title','level'=>'level');
		}
		if ($callback == null) $callback = "formatTreeNode";
		if (count($wrappers) == 0) $wrappers = array(0=>'<ul>%s</ul>',1=>'<li>%s%s</li>'); 
		$this->logMessage('buildTree', sprintf('Table [%s] Fields [%s] callBack [%s]', $table, print_r($fields,true), $callback), 3);
		$root = $this->fetchSingle(sprintf("select * from %s order by %s limit 1",$table,$fields['left']));
		$tmp = $this->iterateTree($root,0,$table,$fields, $callback, $wrappers);
		$ul = $this->formatUL($tmp,$wrappers);
		$this->logMessage('buildTree', sprintf('return [%s]',$ul), 4);
		return $ul;
	}

	private function formatUL($list,$wrappers) {
		$result = array();
		foreach($list as $node) {
			if (count($node['submenu']) > 0) {
				if (count($wrappers) > 0 && array_key_exists(2,$wrappers))
					$submenu = $this->formatUL($node['submenu'],array(0=>$wrappers[2],1=>$wrappers[1],2=>$wrappers[2]));
				else
					$submenu = $this->formatUL($node['submenu'],$wrappers);
			}
			else $submenu = '';
			$result[] = sprintf($wrappers[1],$node['value'],$submenu);
		}
		return sprintf($wrappers[0],implode('',$result));
	}

	private function iterateTree($root, $level, $table, $fields, $callback, $wrappers) {
		$links = array();
		if (!is_array($root)) return $links;
		if ($level == 0) 
			$currLevel = $this->fetchAll(sprintf('select * from %s where level = %d order by %s',$table, $root[$fields['level']],$fields['left']));
		else
			$currLevel = $this->fetchAll(sprintf('select * from %s where %s = %d and %s >= %d and %s <= %d order by %s', 
				$table, $fields['level'], $level, $fields['left'], $root[$fields['left']],
				$fields['right'], $root[$fields['right']],$fields['right']));
		foreach($currLevel as $rec) {
			$submenu = $this->iterateTree($rec, $rec['level']+1, $table, $fields, $callback, $wrappers);
			$links[] = $this->{$callback}($rec,$table,$wrappers,$submenu);
		}
		return $links;
	}

	protected function mainNav() {
		$form = new Forms();
		$form->init('backend/forms/mainNav.html',array('name'=>'mainNav'));
		$frmFields = array(
			'menu'=>array('type'=>'tag','reformatting'=>false)
		);
		$modules = $this->fetchAll(sprintf('select m.* from modules m, users_by_module u where m.enabled = 1 and u.module_id = m.id and u.user_id = %d and m.backend = 1 and m.hidden = 0 and (m.admin = 0 or %d = 1) order by sort',$_SESSION['administrator']['user']['id'],$_SESSION['administrator']['user']['admin']));
		$nav = array();
		foreach($modules as $module) {
			$submodules = $this->fetchAll(sprintf('select * from module_actions where module_id = %d and enabled = 1 order by sequence',$module['id']));
			$subnav = array();
			foreach($submodules as $submodule) {
				$subnav[] = sprintf('<li><a href="%s" class="sub" style="background-image:url(%s)">%s</a></li>',$this->getAdminLink($module['id'],$submodule['id']),$submodule['image'],$submodule['description']);
			}
			if (count($subnav) > 0) {
				$submenu = sprintf('<ul>%s</ul>',implode('',$subnav));
				$toggler = '<a onclick="toggle(this);return false;" class="toggler" href="#">+</a>';
				$class='collapsed';
			}
			else {
				$submenu = '';
				$toggler = '';
				$class='spacer';
			}
			$nav[] = sprintf('<li class="%s">%s<a href="%s" class="main">%s</a>%s</li>',$class,$toggler,$this->getAdminLink($module['id'],0),$module['title'],$submenu);
		}
		$data['menu'] = sprintf("<ul>%s</ul>",implode('',$nav));
		$form->buildForm($frmFields);
		$form->addData($data);
		if ($this->isAjax())
			return $this->ajaxReturn(array('status'=>'true','html'=>$form->show()));
		else
			return $form->show();
	}

	protected function header() {
		$form = new Forms();
		$form->init('backend/forms/header.html',array('name'=>'header'));
		$frmFields = array();
		$form->buildForm($frmFields);
		$form->addData($_SESSION['administrator']['user']);
		if ($this->isAjax())
			return $this->ajaxReturn(array('status'=>'true','html'=>$form->show()));
		else
			return $form->show();
	}

	private function getAdminLink($class, $function, $options = array()) {
		$module = $this->fetchSingle(sprintf('select * from modules where enabled = 1 and id = %d',$class));
		if ($function > 0) 
			$method = $this->fetchSingle(sprintf('select * from module_actions where enabled = 1 and id = %d',$function));
		else $method = array();
		$nav = sprintf('/modit/%s%s',$module['classname'],$function > 0 ? sprintf('/%s',$method['name']) : '');
		$navopt = array();
		if (count($options) > 0) {
			foreach($options as $key=>$value) {
				$navopt[] = sprintf('%s=%s',$key,htmlspecialchars($value));
			}
			$nav .= sprintf('?%s',implode('&amp;',$navopt));
		}
		return $nav;
	}
	
	function getTitle() {
		return $this->fetchScalar(sprintf('select title from modules where classname = "%s"',get_class($this)));
	}
	
	function updateRelations($postArg,$ownerId,$ownerType,$relatedType,$relatedTable,$isOwner = true,$hasDeleted = true) {
		$this->logMessage('updateRelations',sprintf('postArg [%s] ownerId [%d] ownerType [%s] relatedType [%s] relatedTable [%s] isOwner[%s] hasDeleted[%s]',$postArg,$ownerId,$ownerType,$relatedType,$relatedTable,$isOwner,$hasDeleted),1);
		//
		//	update relations for this item/type
		//
		$status = true;
		if (array_key_exists($postArg,$_POST))
			$destItems = $_POST[$postArg];
		else $destItems = array(0);
		if (!is_array($destItems)) $destItems = array($destItems);
		$this->logMessage('updateRelations',sprintf('items [%s]',print_r($destItems,true)),4);
		if ($isOwner) {
			//
			//	delete items we no longer own
			//
			$status = $status && $this->execute(sprintf('delete from relations where owner_id = %d and owner_type="%s" and related_id not in (%s) and related_type = "%s"',$ownerId,$ownerType,implode(',',$destItems),$relatedType));
			//
			//	insert new items
			//
			$new = $this->fetchScalarAll(sprintf('select id from %s where %s and id in (%s) and id not in (select related_id from relations where owner_id = %d and owner_type="%s" and related_id in (%s) and related_type = "%s")', 
				$relatedTable, $hasDeleted?'deleted = 0':'1=1', implode(',',$destItems), $ownerId, $ownerType, implode(',',$destItems),$relatedType));
			$status = true;
			foreach($new as $key=>$folder) {
				$obj = new preparedStatement(sprintf('insert into relations(owner_id,owner_type,related_id,related_type) values(?,?,?,?)'));
				$obj->bindParams(array('dsds',$ownerId,$ownerType,$folder,$relatedType));
				$this->logMessage('updateRelations',sprintf('adding id[%d] ownerType[%s] relatedId [%d] relatedType [%s]',$ownerId,$ownerType,$folder,$relatedType),3);
				$status = $status && $obj->execute();
				if (!$status) $this->logMessage('updateRelations',sprintf('Updates Failed: postArg [%s] ownerId [%d] ownerType [%s] relatedType [%s] relatedTable [%s] isOwner[%s] hasDeleted[%s]',$postArg,$ownerId,$ownerType,$relatedType,$relatedTable,$isOwner,$hasDeleted),1,true);
			}
		} else {
			//
			//	delete items we no longer own
			//
			$status = $status && $this->execute(sprintf('delete from relations where related_id = %d and related_type = "%s" and owner_id not in (%s) and owner_type="%s"',$ownerId,$ownerType,implode(',',$destItems),$relatedType));
			//
			//	insert new items
			//
			$new = $this->fetchScalarAll(sprintf('select id from %s where %s and id in (%s) and id not in (select owner_id from relations where related_id = %d and related_type="%s" and owner_id in (%s) and owner_type = "%s")', 
				$relatedTable, $hasDeleted?'deleted = 0':'1=1', implode(',',$destItems), $ownerId, $ownerType, implode(',',$destItems),$relatedType));
			$status = true;
			foreach($new as $key=>$folder) {
				$obj = new preparedStatement(sprintf('insert into relations(owner_id,owner_type,related_id,related_type) values(?,?,?,?)'));
				$obj->bindParams(array('dsds',$folder,$relatedType,$ownerId,$ownerType));
				$this->logMessage('updateRelations',sprintf('adding id[%d] ownerType[%s] relatedId [%d] relatedType [%s]',$folder,$relatedType,$ownerId,$ownerType),3);
				$status = $status && $obj->execute();
				if (!$status) $this->logMessage('updateRelations',sprintf('Updates Failed: postArg [%s] ownerId [%d] ownerType [%s] relatedType [%s] relatedTable [%s] isOwner[%s] hasDeleted[%s]',$postArg,$ownerId,$ownerType,$relatedType,$relatedTable,$isOwner,$hasDeleted),1,true);
			}
		}
		return $status;
	}

	function loadRelations($postArg,$obj,$callback,$ownerId,$ownerType,$relatedType,$relatedTable,$where,$isOwner = true) {
		$this->logMessage('loadRelations',sprintf('postArg [%s] callback [%s] ownerId [%d] ownerType [%s] relatedType [%s] relatedTable[%s] where [%s] isOwner [%s]',
			$postArg,$callback,$ownerId,$ownerType,$relatedType,$relatedTable,$where,$isOwner),1);
		$options = array();
		if ((count($_REQUEST) > 0 && array_key_exists($postArg,$_REQUEST)) || $ownerId > 0) {
			$ids = array();
			if (array_key_exists($postArg,$_REQUEST)) {
				$ids = $_REQUEST[$postArg];
				if (!is_array($ids)) $ids = array($ids);
			}
			if ($ownerId > 0) {
				if ($isOwner) {
					$sql = sprintf('select related_id from relations where owner_id = %d and owner_type = "%s" and related_type = "%s"',$ownerId,$ownerType,$relatedType);
					$tmp = $this->fetchScalarAll($sql);
				}
				else {
					$sql = sprintf('select owner_id from relations where related_id = %d and related_type = "%s" and owner_type = "%s"',$ownerId,$ownerType,$relatedType);
					$tmp = $this->fetchScalarAll($sql);
				}
				$ids = array_merge($ids,$tmp);
				$this->logMessage('loadRelations',sprintf('sql [%s] count [%d]',$sql,count($ids)),3);
			}
			if (count($ids) > 0) {
				$ids = implode(',',$ids);
				$sql = sprintf('select * from %s where id in (%s) %s', $relatedTable, $ids, $where);
				$source = $this->fetchAll($sql);
				$this->logMessage('loadRelations',sprintf('sql [%s] count [%d]',$sql,count($source)),3);
				foreach($source as $key=>$rec) {
					$options[$rec['id']] = $obj->{$callback}($rec);
						//array('value'=>sprintf('%s - %s',htmlspecialchars($coupon['code']),htmlspecialchars($coupon['name'])), 'id'=>sprintf('c_by_s_%d',$coupon['id']),'reformatting'=>false);
				}
			}
		}
		return $options;
	}

	function displayRelations($id,$table,$ownerType,$relatedType,$where,$isOwner,$fields,$template) {
		$this->logMessage('displayRelations',sprintf('ownerId [%d] table [%s] ownerType [%s] relatedType [%s] where [%s] isOwner [%s] fields [%s] template [%s]',
			$id,$table,$ownerType,$relatedType,$where,$isOwner,print_r($fields,true),$template),1);
		$output = array();
		$frm = new Forms();
		$frm->init($template,array());
		$frm->buildForm($fields);
		if ($isOwner) {
			$sql = sprintf('select * from %s c, relations r where r.owner_id = %d and r.owner_type = "%s" and c.id = r.related_id and r.related_type="%s" %s',
							$table, $id, $ownerType, $relatedType, $where);
			$recs = $this->fetchAll($sql);
			$this->logMessage('displayRelations',sprintf('sql [%s] count [%d]',$sql,count($recs)),2);
			foreach($recs as $rec) {
				$frm->addData($rec);
				$output[] = $frm->show();
			}
		}
		else {
			$sql = sprintf('select * from %s c, relations r where r.related_id = %d and r.related_type = "%s" and c.id = r.owner_id and r.owner_type="%s" %s',
							$table, $id, $ownerType, $relatedType, $where);
			$recs = $this->fetchAll($sql);
			$this->logMessage('displayRelations',sprintf('sql [%s] count [%d]',$sql,count($recs)),2);
			foreach($recs as $rec) {
				$frm->addData($rec);
				$output[] = $frm->show();
			}
		}
		return implode('',$output);
	}

	function altAdFormat($rec) {
		$this->logMessage('altAdFormat',sprintf('rec [%s]',print_r($rec,true)),2);
		$tmp = $rec['id'];
		$this->logMessage('altAdFormat',sprintf('return [%s]',print_r($tmp,true)),3);
		return $tmp;
	}

	function couponFormat($rec) {
		$this->logMessage('couponFormat',sprintf('rec [%s]',print_r($rec,true)),2);
		$tmp = array('value'=>sprintf('%s - %s',htmlspecialchars($rec['code']),htmlspecialchars($rec['name'])), 'id'=>sprintf('c_by_p_%d',$rec['id']),'reformatting'=>false);
		$this->logMessage('couponFormat',sprintf('return [%s]',print_r($tmp,true)),3);
		return $tmp;	
	}

	function altCouponFormat($rec) {
		$this->logMessage('altCouponFormat',sprintf('rec [%s]',print_r($rec,true)),2);
		$tmp = $rec['id'];
		$this->logMessage('couponFormat',sprintf('return [%s]',print_r($tmp,true)),3);
		return $tmp;
	}
	
	function storeChainFormat($rec) {
		$tmp = array('value'=>sprintf('%s',htmlspecialchars($rec['title'])), 'id'=>sprintf('storefolder_%d',$rec['id']),'reformatting'=>false, 'class'=>sprintf('def_field_li sortorder_%d',$rec['left_id']));
		$this->logMessage('storeChainFormat',sprintf('return [%s]',print_r($tmp,true)),3);
		return $tmp;
	}

	function productChainFormat($rec) {
		$tmp = array('value'=>sprintf('%s',htmlspecialchars($rec['title'])), 'id'=>sprintf('productfolder_%d',$rec['id']),'reformatting'=>false, 'class'=>sprintf('def_field_li sortorder_%d',$rec['left_id']));
		$this->logMessage('productChainFormat',sprintf('return [%s]',print_r($tmp,true)),3);
		return $tmp;	
	}

	function newsChainFormat($rec) {
		$tmp = array('value'=>sprintf('%s',htmlspecialchars($rec['title'])), 'id'=>sprintf('newsfolder_%d',$rec['id']),'reformatting'=>false, 'class'=>sprintf('def_field_li sortorder_%d',$rec['left_id']));
		$this->logMessage('newsChainFormat',sprintf('return [%s]',print_r($tmp,true)),3);
		return $tmp;	
	}

	function storeFormat($rec) {
		$tmp = array('value'=>htmlspecialchars($rec['name']), 'id'=>sprintf('store_%d',$rec['id']),'reformatting'=>false, 'class'=>sprintf('def_field_li storeorder_%d',$rec['id']));
		$this->logMessage('storeFormat',sprintf('return [%s]',print_r($tmp,true)),3);
		return $tmp;	
	}

	function altStoreFormat($rec) {
		$tmp = $rec['id'];
		$this->logMessage('storeFormat',sprintf('return [%s]',print_r($tmp,true)),3);
		return $tmp;	
	}

	function newsFormat($rec) {
		$tmp = array('value'=>htmlspecialchars($rec['title']), 'id'=>sprintf('article_%d',$rec['id']),'reformatting'=>false, 'class'=>sprintf('def_field_li articleorder_%d',$rec['id']));
		$this->logMessage('newsFormat',sprintf('return [%s]',print_r($tmp,true)),3);
		return $tmp;	
	}

	function altNewsFormat($rec) {
		$tmp = $rec['id'];
		$this->logMessage('altNewsFormat',sprintf('return [%s]',print_r($tmp,true)),3);
		return $tmp;	
	}

	function eventFormat($rec) {
		$tmp = array('value'=>sprintf('%s - %s',htmlspecialchars($rec['name']),date('d-M-Y',strtotime($rec['start_date']))), 'id'=>sprintf('event_%d',$rec['id']),'reformatting'=>false, 'class'=>sprintf('def_field_li eventorder_%d',$rec['id']));
		$this->logMessage('eventFormat',sprintf('return [%s]',print_r($tmp,true)),3);
		return $tmp;
	}

	function altEventFormat($rec) {
		$tmp = $rec['id'];
		$this->logMessage('altEventFormat',sprintf('return [%s]',print_r($tmp,true)),3);
		return $tmp;
	}

	function altGalleryFormat($rec) {
		$tmp = $rec['id'];
		$this->logMessage('altGalleryFormat',sprintf('return [%s]',print_r($tmp,true)),3);
		return $tmp;
	}

	function productFormat($rec) {
		$tmp = array('value'=>sprintf('%s - %s',htmlspecialchars($rec['code']),htmlspecialchars($rec['name'])), 'id'=>sprintf('product_%d',$rec['id']),'reformatting'=>false, 'class'=>sprintf('def_field_li productorder_%d',$rec['id']));
		$this->logMessage('productFormat',sprintf('return [%s]',print_r($tmp,true)),3);
		return $tmp;	
	}

	function altProductFormat($rec) {
		$tmp = $rec['id'];
		$this->logMessage('altProductFormat',sprintf('return [%s]',print_r($tmp,true)),3);
		return $tmp;	
	}

	function blogFormat($rec) {
		$tmp = array('value'=>sprintf('%s - %s',htmlspecialchars($rec['code']),htmlspecialchars($rec['title'])), 'id'=>sprintf('blog_%d',$rec['id']),'reformatting'=>false, 'class'=>sprintf('def_field_li blogorder_%d',$rec['id']));
		$this->logMessage('blogFormat',sprintf('return [%s]',print_r($tmp,true)),3);
		return $tmp;	
	}

	function altBlogFormat($rec) {
		$tmp = $rec['id'];
		$this->logMessage('altBlogFormat',sprintf('return [%s]',print_r($tmp,true)),3);
		return $tmp;	
	}

	function eventList($id,$form,$flds) {
		$output = array();
		$f = new Forms();
		$f->init($form);
		$f->buildForm($flds);
		$selected = array(0);
		if (array_key_exists('selected',$_REQUEST))
			$selected = array_values($_REQUEST['selected']);
		$sql = sprintf('select e.* from events e where e.enabled = 1 and e.published = 1 and (e.id in (%s) or e.id in (select f.event_id from events_by_folder f where f.folder_id = %s)) and e.id in (select event_id from event_dates where event_date >= curdate()) order by start_date',implode(',',$selected),$id);
		$events = $this->fetchAll($sql);
		$this->logMessage('eventList',sprintf('sql [%s] count [%d]',$sql,count($events)),3);
		$from = $f->getField('destEvents');
		$selected_str = '~'.implode('~',$selected).'~';
		foreach($events as $event) {
			$options = array('id'=>sprintf('event_%d',$event['id']),'value'=>$event['name']);
			if (strpos($selected_str,'~'.$event['id'].'~') !== false)
				$options['selected'] = 'selected';
			$from->addOption($event['id'],$options);
		}
		return $f->show();
	}

	function productList($id,$form,$flds) {
		$output = array();
		$f = new Forms();
		$f->init($form);
		$f->buildForm($flds);
		$selected = array(0);
		if (array_key_exists('selected',$_REQUEST))
			$selected = array_values($_REQUEST['selected']);
		$sql = sprintf('select p.* from product p where p.deleted = 0 and p.enabled = 1 and p.published = 1 and (p.id in (%s) or p.id in (select product_id from product_by_folder f where f.folder_id = %d and p.id = f.product_id))',implode(',',$selected),$id);
		$products = $this->fetchAll($sql);
		//$products = $this->fetchAll(sprintf('select p.id, p.code, p.name from product p, product_by_folder f where f.folder_id = %d and p.id = f.product_id and p.deleted = 0 order by code',$id));
		$from = $f->getField('destRelatedProducts');
		$selected_str = '~'.implode('~',$selected).'~';
		foreach($products as $product) {
			$options = array('id'=>sprintf('subproduct_%d',$product['id']),'value'=>sprintf("%s - %s",$product['code'],$product['name']));
			if (strpos($selected_str,'~'.$product['id'].'~') !== false)
				$options['selected'] = 'selected';
			$from->addOption($product['id'],$options);
		}
		return $f->show();
	}

	function couponList($id,$form,$flds) {
		$this->logMessage('couponList',sprintf("($id,$form,%s)",print_r($flds,true)),2);
		$f = new Forms();
		$f->init($form);
		$f->buildForm($flds);
		$sql = sprintf('select c.* from coupons c, coupons_by_folder f where c.deleted = 0 and f.folder_id = %d and c.id = f.coupon_id',$id);
		$selected = array(0);
		if (array_key_exists('selected',$_REQUEST))
			$selected = array_values($_REQUEST['selected']);
		$sql = sprintf('select c.* from coupons c where c.deleted = 0 and c.enabled = 1 and c.published = 1 and (c.id in (%s) or c.id in (select coupon_id from coupons_by_folder f where f.folder_id = %d and c.id = f.coupon_id))',implode(',',$selected),$id);
		$coupons = $this->fetchAll($sql);
		$this->logMessage('couponList',sprintf('sql [%s] count [%d]',$sql,count($coupons)),1);
		$from = $f->getField('destCoupons');
		$selected_str = '~'.implode('~',$selected).'~';
		foreach($coupons as $coupon) {
			$options = array('id'=>sprintf('coupon_%d',$coupon['id']),'value'=>$coupon['code']);
			if (strpos($selected_str,'~'.$coupon['id'].'~') !== false)
				$options['selected'] = 'selected';
			$from->addOption($coupon['id'],$options);
		}
		return $f->show();
	}

	function newsList($id,$form,$flds) {
		$this->logMessage('newsList',sprintf("($id,$form,%s)",print_r($flds,true)),2);
		$f = new Forms();
		$f->init($form);
		$f->buildForm($flds);
		$sql = sprintf('select n.* from news n, news_by_folder f where n.deleted = 0 and f.folder_id = %d and n.id = f.article_id',$id);
		$selected = array(0);
		if (array_key_exists('selected',$_REQUEST))
			$selected = array_values($_REQUEST['selected']);
		$sql = sprintf('select n.* from news n where n.deleted = 0 and n.enabled = 1 and n.published = 1 and (n.id in (%s) or n.id in (select article_id from news_by_folder f where f.folder_id = %d and n.id = f.article_id))',implode(',',$selected),$id);
		$articles = $this->fetchAll($sql);
		$this->logMessage('newsList',sprintf('sql [%s] count [%d]',$sql,count($articles)),1);
		$from = $f->getField('destNews');
		$selected_str = '~'.implode('~',$selected).'~';
		foreach($articles as $article) {
			$options = array('id'=>sprintf('article_%d',$article['id']),'value'=>$article['title']);
			if (strpos($selected_str,'~'.$article['id'].'~') !== false)
				$options['selected'] = 'selected';
			$from->addOption($article['id'],$options);
		}
		return $f->show();
	}


	function storeChainList($id,$form,$flds) {
		$stores = $this->fetchAll(sprintf('select s.id, s.name, f.sequence from stores s, stores_by_folder f where f.folder_id = %d and s.id = f.store_id order by sequence',$id));
		$output = array();
		$f = new Forms();
		$f->init($form);
		$f->buildForm($flds);
		foreach($stores as $store) {
			$f->addData($store);
			$output[] = $f->show();
		}
		$tmp = sprintf('<ul id="fromStoreStoresList" class="storeDraggable draggable">%s</ul>',implode('',$output));
		return $tmp;
	}

	function storeList($id,$form,$flds) {
		$this->logMessage('storeList',sprintf("($id,$form,%s)",print_r($flds,true)),2);
		$output = array();
		$f = new Forms();
		$f->init($form);
		$f->buildForm($flds);
		$selected = array(0);
		if (array_key_exists('selected',$_REQUEST))
			$selected = array_values($_REQUEST['selected']);
		$sql = sprintf('select s.* from stores s, stores_by_folder f where f.folder_id = %d and s.id = f.store_id and s.deleted = 0 order by name',$id);
		$sql = sprintf('select s.* from stores s where s.deleted = 0 and s.enabled = 1 and s.published = 1 and (s.id in (%s) or s.id in (select store_id from stores_by_folder f where f.folder_id = %d))',implode(',',$selected),$id);
		$stores = $this->fetchAll($sql);
		$this->logMessage('storeList',sprintf('sql [%s] count [%d]',$sql,count($stores)),3);
		$from = $f->getField('destStores');
		$selected_str = '~'.implode('~',$selected).'~';
		foreach($stores as $store) {
			$options = array('id'=>sprintf('store_%d',$store['id']),'value'=>$store['name']);
			if (strpos($selected_str,'~'.$store['id'].'~') !== false)
				$options['selected'] = 'selected';
			$from->addOption($store['id'],$options);
		}
		return $f->show();
	}

	function blogList($id,$form,$flds) {
		$this->logMessage('blogList',sprintf("($id,$form,%s)",print_r($flds,true)),2);
		$output = array();
		$f = new Forms();
		$f->init($form);
		$f->buildForm($flds);
		$selected = array(0);
		if (array_key_exists('selected',$_REQUEST))
			$selected = array_values($_REQUEST['selected']);
		$sql = sprintf('select b.* from blog s, blog_by_folder f where f.folder_id = %d and s.id = f.blog_id and s.deleted = 0 order by name',$id);
		$sql = sprintf('select b.* from blog b where b.deleted = 0 and b.enabled = 1 and b.published = 1 and (b.id in (%s) or b.id in (select blog_id from blog_by_folder f where f.folder_id = %d))',implode(',',$selected),$id);
		$blogs = $this->fetchAll($sql);
		$this->logMessage('blogList',sprintf('sql [%s] count [%d]',$sql,count($blogs)),3);
		$from = $f->getField('destBlogs');
		$selected_str = '~'.implode('~',$selected).'~';
		foreach($blogs as $blog) {
			$options = array('id'=>sprintf('blog_%d',$blog['id']),'value'=>$blog['title']);
			if (strpos($selected_str,'~'.$blog['id'].'~') !== false)
				$options['selected'] = 'selected';
			$from->addOption($blog['id'],$options);
		}
		return $f->show();
	}
	
	function advertList($id,$form,$flds) {
		$this->logMessage('advertList',sprintf("($id,$form,%s)",print_r($flds,true)),2);
		$output = array();
		$f = new Forms();
		$f->init($form);
		$f->buildForm($flds);
		$selected = array(0);
		if (array_key_exists('selected',$_REQUEST))
			$selected = array_values($_REQUEST['selected']);
		$sql = sprintf('select a.* from advert a, advert_by_folder f where f.folder_id = %d and a.id = f.advert_id and a.deleted = 0 order by name',$id);
		$sql = sprintf('select a.* from advert a where a.deleted = 0 and a.enabled = 1 and a.published = 1 and (a.id in (%s) or a.id in (select advert_id from advert_by_folder f where f.folder_id = %d))',implode(',',$selected),$id);
		$adverts = $this->fetchAll($sql);
		$this->logMessage('advertList',sprintf('sql [%s] count [%d]',$sql,count($adverts)),3);
		$from = $f->getField('destAds');
		$selected_str = '~'.implode('~',$selected).'~';
		foreach($adverts as $advert) {
			$options = array('id'=>sprintf('advert_%d',$advert['id']),'value'=>$advert['title']);
			if (strpos($selected_str,'~'.$advert['id'].'~') !== false)
				$options['selected'] = 'selected';
			$from->addOption($advert['id'],$options);
		}
		return $f->show();
	}

	function galleryList($id,$form,$flds) {
		$output = array();
		$f = new Forms();
		$f->init($form);
		$f->buildForm($flds);
		$selected = array(0);
		if (array_key_exists('selected',$_REQUEST))
			$selected = array_values($_REQUEST['selected']);
		$sql = sprintf('select i.* from gallery_images i where i.enabled = 1 and i.published = 1 and (i.id in (%s) or i.id in (select image_id from gallery_images_by_folder f where f.folder_id = %d and i.id = f.image_id))',implode(',',$selected),$id);
		$images = $this->fetchAll($sql);
		$from = $f->getField('destRelatedGallery');
		$selected_str = '~'.implode('~',$selected).'~';
		foreach($images as $image) {
			$options = array('id'=>sprintf('image_%d',$image['id']),'value'=>$image['title']);
			if (strpos($selected_str,'~'.$image['id'].'~') !== false)
				$options['selected'] = 'selected';
			$from->addOption($image['id'],$options);
		}
		return $f->show();
	}

	function getAccessLevel() {
		//$sql = sprintf('select role_id from users_by_module um, modules m where m.classname = "%s" and um.module_id = m.id and um.user_id = %d',get_class($this),$_SESSION['administrator']['user']['id']);
		//$level = $this->fetchScalar($sql);
		//$this->logMessage("getAccessLevel",sprintf("sql [$sql] level [$level]"),2);
		$this->logMessage("getAccessLevel",$this->m_access_level,2);
		return $this->m_access_level;
	}

	function noAccessError() {
		$this->addError('You do not have access to this function');
	}

	function ajaxReturn( $parms, $stripCodes = array('<script>'=>1,'<code>'=>1) ) {
		if (!$this->isLoggedIn()) {
			$this->logMessage('ajaxReturn',sprintf('forcing ajax back to login'),2);
			$parms = array('html'=>'<div></div>','code'=>'document.location="/modit/";','status'=>'true');
		}
		return parent::ajaxReturn($parms, $stripCodes);
	}

}

class beAjax extends Backend {

	private $_M_DIR;

	function __construct() {
		parent::__construct();
		$this->m_viaAjax = true;
		$this->M_DIR = 'backend/';
		$this->setTemplates(
			array(
				'myAccount'=>$this->M_DIR.'forms/myAccount.html',
				'myAccountSuccess'=>$this->M_DIR.'forms/myAccountSuccess.html',
			)
		);
		$GLOBALS['secureLink'] = 0;
		$this->setFields(
			array(
				'myAccount'=>array(
					'fname'=>array('type'=>'input','required'=>true),
					'lname'=>array('type'=>'input','required'=>true),
					'password'=>array('type'=>'password','required'=>false,'validation'=>'password'),
					'passwordConfirm'=>array('type'=>'password','required'=>false,'validation'=>'password','database'=>false),
					'email'=>array('type'=>'input','required'=>true,'validation'=>'email'),
					'submit'=>array('type'=>'submitbutton','value'=>'Save Changes','database'=>false),
					'myAccount'=>array('type'=>'hidden','value'=>1,'database'=>false)
				)
			)
		);
	}

	function show() {
#		if (get_magic_quotes_gpc()) {
#			$this->processSlashes();
#		}
		$class = array_key_exists('module',$_REQUEST) ? $_REQUEST['module'] : '';
		$function = array_key_exists('ajax',$_REQUEST) ? $_REQUEST['ajax'] : '';		
		$this->logMessage('show', sprintf('Ajax request for %s::%s',$class,$function), 1);
		if (strlen($class) == 0) {
			if (method_exists($this,$function))
				return $this->{$function}();
			else {
				$err = sprintf('Invalid request: %s',$function);
				$this->logMessage('show', $err, 1, DEV);
				$this->addError($err);
				return $this->ajaxReturn(array('status'=>'false','html'=>$err));
			}
		}
		else {
			if (class_exists($class) && $this->hasAccess($class)) {
				$class = new $class();
				$class->setAjax(true);
				if (method_exists($class, $function)) {
 					if ($class->hasFunctionAccess($function)) return $class->{$function}();
					return $this->ajaxReturn(array('status'=>'false','html'=>'You do not have access to this function'));
				}
				else {
					$err = sprintf('Class [%s] Function [%s] did not exist',get_class($class),$function);
					$this->addError($err);
					$this->logMessage('show', $err, 1, DEV);
					return $this->ajaxReturn(array('status'=>'false','html'=>'Class or Function did not exist'));
				}
			} else {
				return $this->ajaxReturn(array('status'=>'false','html'=>'You do not have access to this function'));
			}
		}
	}
	
	function updateTree() {
		$srcId = array_key_exists('src',$_REQUEST) ? $_REQUEST['src'] : 0;
		$destId = array_key_exists('dest',$_REQUEST) ? $_REQUEST['dest'] : 0;
		$table = array_key_exists('table',$_REQUEST) ? $_REQUEST['table'] : '';
		$position = array_key_exists('type',$_REQUEST) ? $_REQUEST['type'] : 'after';
		if (strlen($table) == 0) {
			$this->logMessage('updateTree','No table specified',1);
			return json_encode(array('status'=>"false",'error'=>'No table specified'));
		}
		$src = $this->fetchSingle(sprintf('select * from %s where id = %d',$table,$srcId));
		$dest = $this->fetchSingle(sprintf('select * from %s where id = %d',$table,$destId));
		if (count($src) == 0 || count($dest) == 0) {
			$this->logMessage('updateTree',sprintf('Either source [%d] or dest [%d] was not found',count($src),count($dest)),1);
			return json_encode(array('status'=>"false",'error'=>'Either the source or destination node was not found'));
		}
		$offset = 999;
		$mptt = new mptt($table);
		switch($position) {
			case 'before':
				//
				//	drop the source before the dest - grab the parent and calc the offset
				//
				if ($p = $this->fetchSingle(sprintf('select * from %s where level = %d and left_id < %d and right_id > %d order by left_id limit 1',$table,$dest['level']-1, $dest['left_id'],$dest['right_id']))) {
					$destId = $p['id'];
					$offset = ($dest['left_id'] - $p['left_id'] - 1) / 2;
				}
				else {
					$destId = 0;
					$offset = 999;
				}
				break;
			case 'append':
				//
				//	append to the current node - no children just append, otherwise move to last
				//
				$children = $mptt->fetchChildren($destId);
				if (count($children) == 0) {
					$offset = 0;
				}
				else {
					$offset = $dest['right_id'];	// move it to the bottom
				}
				break;
			case 'after':
				//
				//	drop the source after the dest - grab the parent and calc the offset
				//
				if ($p = $this->fetchSingle(sprintf('select * from %s where level = %d and left_id < %d and right_id > %d order by left_id limit 1',$table,$dest['level']-1, $dest['left_id'],$dest['right_id']))) {
					$children = $this->fetchScalarAll(sprintf('select id from %s where level = %d and left_id >= %d and right_id <= %d order by left_id',$table,$dest['level'],$p['left_id'],$p['right_id']));
					//$destId = $p['id'];
					$toId = $p['id'];
				}
				else {
					$children = $this->fetchScalarAll(sprintf('select id from %s where level = %d order by left_id',$table,$dest['level']));
					$toId = 0;
				}
				$offset = 0;
				foreach($children as $key=>$id) {
					if ($id == $destId) $offset = $key + 1;
				}
				$destId = $toId;
				break;
			default:
				break;
		}
		if ($mptt->move($srcId,$destId,$offset))
			return json_encode(array('status'=>"true",'error'=>''));
		else {
			$this->logMessage('updateTree','update failed',1); 
			return json_encode(array('status'=>"false",'error'=>'Update failed'));		
		}
	}

	function loadProvinces() {
		if (array_key_exists('c_id',$_REQUEST)) {
			$c_id = $_REQUEST['c_id'];
			$select = new provinceSelect(false);
			$select->addAttributes(array(
				'country_id'=>$c_id,
				'id'=>'province_id',
				'required'=>true,
				'name'=>array_key_exists('name',$_REQUEST) ? $_REQUEST['name'] : 'province_id'
			));
			return $this->ajaxReturn(array(
					'status'=>'true',
					'html'=>$select->show()
				));
		}
		else return $this->ajaxReturn(array('status'=>false));
	}

	function deleteRelation() {
		if (array_key_exists('j_id',$_REQUEST)) {
			$id = $_REQUEST['j_id'];
			$this->logMessage('deleteRelation', sprintf('deleting relation %d',$id), 1);
			$this->beginTransaction();
			$this->execute(sprintf('delete from relations where id = %d',$id));
			$this->commitTransaction();
			return $this->ajaxReturn(array('status'=>'true'));
		}
	}

	function myAccount() {
		$form = new Forms();
		$form->init($this->getTemplate('myAccount'),array('name'=>'myAccount','method'=>'post','action'=>'myAccount'));
		$frmFlds = $this->getFields('myAccount');
		$form->buildForm($frmFlds);
		$id = $_SESSION['administrator']['user']['id'];
		if ($rec = $this->fetchSingle(sprintf('select * from users where id = %d',$id))) {
			$rec['password'] = '';
			$form->addData($rec);
		}
		if (count($_POST) > 0 && array_key_exists('myAccount',$_POST)) {
			$form->addData($_POST);
			if ($status = $form->validate()) {
				if ($form->getData('password') != '' && $form->getData('passwordConfirm') != $form->getData('password')) {
					$form->addError('Password and Confirmation do not match');
					$status = false;
				}
			}
			if ($status) {
				foreach($frmFlds as $key=>$fld) {
					if (!(array_key_exists('database',$fld) && $fld['database'] == false)) {
						$flds[$fld['name']] = $form->getData($fld['name']);
					}
				}
				if ($form->getData('password') == '')
					unset($flds['password']);
				else
					$flds['password'] = SHA1($form->getData('password'));
				$stmt = $this->prepare(sprintf('update users set %s where id = %d', implode('=?,',array_keys($flds)).'=?',$id));
				$stmt->bindParams(array_merge(array(str_repeat('s', count($flds))),array_values($flds)));
				$this->beginTransaction();
				if ($status = $stmt->execute()) {
					$this->addMessage('Updated Succesfully');
					$this->commitTransaction();
					$_SESSION['administrator']['user'] = $this->fetchSingle(sprintf('select * from users where id = %d',$id));
					$form->init($this->getTemplate('myAccountSuccess'));
				}
				else {
					$this->addError('An Error occurred');
					$this->rollbackTransaction();
				}
			}
		}
		return $this->ajaxReturn(array('status'=>'true','html'=>$form->show()));
	}

	function setFBStatus() {
		if (array_key_exists("st",$_REQUEST)) {
			$_SESSION["facebook"] = array("status"=>$_REQUEST["st"]);
			if (array_key_exists("r",$_REQUEST))
				$_SESSION["facebook"]["response"] = $_REQUEST["r"];
		}
		$this->logMessage(__FUNCTION__,sprintf("session is now [%s]",print_r($_SESSION,true)),1);
		return $this->ajaxReturn(array("status"=>true,"html"=>""));
	}

}
?>
