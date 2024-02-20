<?php

use Facebook\FacebookRequest;
use Facebook\Session;
use Facebook\GraphObject;
use Facebook\FacebookRequestException;

require ADMIN.'classes/twilio/vendor/autoload.php';
use Twilio\Rest\Client;

function stripslashes_deep($value) {
	if ( is_array($value) ) {
		$value = array_map('stripslashes_deep', $value);
	} elseif ( is_object($value) ) {
		$vars = get_object_vars( $value );
		foreach ($vars as $key=>$data) {
			$value->{$key} = stripslashes_deep( $data );
		}
	} else {
		$value = stripslashes($value);
	}
	return $value;
}

function facebook_convert_entity($matches, $destroy = true) {
	static $table = array('quot' => '"','amp' => '&','lt' => '<','gt' => '>','circ' => 'ˆ','tilde' => '~','ndash' => '–','mdash' => '—','lsquo' => '‘','rsquo' => '’','sbquo' => '‚','ldquo' => '“','rdquo' => '”','bdquo' => '„','dagger' => '†','Dagger' => '‡','permil' => '‰','lsaquo' => '‹','rsaquo' => '›','euro' => '€',
		'bull' => '•','hellip' => '…','trade' => '™','nbsp' => ' ','cent' => '¢','pound' => '£','yen' => '¥',
		'brvbar' => '¦','sect' => '§','uml' => '¨','copy' => '©','ordf' => 'ª','laquo' => '«','not' => '¬','reg' => '®','macr' => '¯','deg' => '°','plusmn' => '±','acute' => '´','micro' => 'µ','para' => '¶','middot' => '·','cedil' => '¸','ordm' => 'º','raquo' => '»','frac14' => '¼','frac12' => '½','frac34' => '¾'
	);
	if (isset($table[$matches[1]])) return $table[$matches[1]];
	return $destroy ? '' : $matches[0];
}

function fedexRates( $r1, $r2 ) {
	if ($r1['rate'] == $r2['rate'])
		return 0;
	return ($r1['rate'] < $r2['rate']) ? -1 : 1;
}

class Common {
	
	protected $m_severity = 0;
	protected $m_options = array();
	protected $m_viaAjax = false;
	protected $m_pageName;
	protected $m_searchWinner = 0;

	function logMessage($function, $message, $severity, $email = false, $trace = true) {
		if (!array_key_exists('globals',$GLOBALS)) {
			return;	// not initiated yet
		}
		if ($severity <= $GLOBALS['globals']->getSeverity() && $GLOBALS['globals']->getHandle()) {
			$s = sprintf("%s %s::%s:%s\n", date('d-M-Y H:i:s'), get_class($this), $function, $message);
			fwrite($GLOBALS['globals']->getHandle(), $s);
		}
		if ($email) {
			if ($trace)
				$s = sprintf("%s %s::%s:%s\n\nSession:\n%s\nPost:\n%s\nRequest:\n%s\nTrace:\n%s\n", 
					date('d-M-Y H:i:s'), get_class($this), $function, $message, print_r($_SESSION,true), 
					print_r($_POST,true), print_r($_REQUEST,true),print_r(debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS),true));
			else
				$s = sprintf("%s %s::%s:%s\n\nSession:\n%s\nPost:\n%s\nRequest:\n%s\n", 
					date('d-M-Y H:i:s'), get_class($this), $function, $message, print_r($_SESSION,true), 
					print_r($_POST,true), print_r($_REQUEST,true));
			$mailer = new MyMailer();
			$mailer->Subject = sprintf("Error Occurred - %s", SITENAME);
			$mailer->Body = $s;
			$emails = $this->configEmails("admin");
			foreach($emails as $key=>$info) {
				$mailer->AddAddress($info['email'],$info['name']);
			}
			$mailer->From= $emails[0]['email'];
			$mailer->FromName = $emails[0]['name'];
			if (!$mailer->Send())
				if ($function != __FUNCTION__)
					Common::logMessage($function,sprintf("Email send failed [%s]",print_r($mailer,true)),1,false);
				else {
					$sanitisedmailer = $this->sanitizehtmlspecialchars($mailer);
					echo sprintf(sprintf("Email send failed [%s]",print_r($sanitisedmailer,true)));
				}
		}
	}
	
	function __construct($init = false,$wurfl=true, $debugLog = '') {
		if ($init) {
			$this->m_privateid = random_int(1, 1000000);
			$GLOBALS['globals'] = new Globals(DEBUG,$wurfl,$debugLog);
		}
		else {
			$this->m_privateid = '';
		}
	}
	
	function __destruct() {
		
	}

	function getDebug() {
		return $GLOBALS["globals"]->getDebug();
	}

	function setDebug($level) {
		$GLOBALS["globals"]->setDebug($level);
	}

	function setPageName($name) {
		$GLOBALS['globals']->m_pageName = $name;
	}

	function getPageName() {
		return $GLOBALS['globals']->m_pageName;
	}

	function setUserInfo($user) {
		$_SESSION['user']['info'] = $user;
	}	

	function getUserInfo($field = null, $FORCE_FRONTEND = null) {
		if (defined("FRONTEND") || ($FORCE_FRONTEND != null && $FORCE_FRONTEND)) 
			$root = array("user","info");
		else {
		 	$root = array("administrator","user");
		}
		if (is_null($field)) {
			if (array_key_exists($root[0],$_SESSION)) 
				return $_SESSION[$root[0]][$root[1]];
			else
				return array('id'=>0);
		}
		else {
			if (array_key_exists($root[0],$_SESSION) && is_array($_SESSION[$root[0]]) && array_key_exists($field,$_SESSION[$root[0]][$root[1]]))
				return $_SESSION[$root[0]][$root[1]][$field];
			else
				return 'null';
		}
	}

	function getPrivateId() {
		return $this->m_privateid;
	}

	function defaultImage() {
		return '/images/unknown.jpg';
	}

	function nullImage($img) {
		return is_null($img) || strlen($img) == 0 || $img == $this->defaultImage();
	}

	function configEmails($type) {
		$tmp = $this->getConfigVar(sprintf('%s_list',$type));
		if (strlen($tmp) == 0) return array();
		$list = explode("^",$tmp);
		$ret = array();
		foreach($list as $pair) {
			$tmp = explode(":",$pair);
			$ret[] = array("name"=>$tmp[0],"email"=>$tmp[1]);
		}
		return $ret;
	}

	function depairOptions($str,$delims = array('^',':')) {
		$options = array();
		if (strlen($str) > 0) {
			$tmp = explode($delims[0],$str);
			foreach($tmp as $key) {
				$x = explode($delims[1],$key);
				if (!array_key_exists(1,$x)) {
					$this->logMessage(__FUNCTION__,sprintf("invalid parsing from [%s] delims [%s]",$str,print_r($delims,true)),1,true,true);
					$options = array();
				}
				else $options[$x[0]] = $x[1];
			}
		}
		return $options;
	}

	protected function parseOptions($str,$delims = array('^',':')) {
		$this->m_options = $this->depairOptions($str,$delims);
	}
	
	protected function hasOption($optName) {
		return array_key_exists($optName,$this->m_options);
	}

	function hasOptions() {
		return count($this->m_options) > 0;
	}

	protected function getOption($optName) {
		if ($this->hasOption($optName))
			return $this->m_options[$optName];
	}

	function setOption($name,$value) {
		$this->m_options[$name] = $value;
	}

	function setOptions($options,$init = false) {
		if ($init) $this->m_options = array();
		foreach($options as $key=>$opt) {
			$this->setOption($key,$opt);
		}
	}

	function getOptions() {
		return $this->m_options;
	}

	function allowOverride() {
		$ret = true;
		$p1 = $this->getOption('allowoverride',$this->m_options);
		$this->logMessage('allow_override','return [$ret]',3);
		return $ret;
	}

	function getConfigVar($name,$type='config') {
		if ($data = $this->fetchSingle(sprintf("select * from config where name = '%s' and type = '%s'",$name,$type))) {
			switch($data['field_type']) {
				case 'address':
					$address = $this->fetchSingle(sprintf('select * from addresses where id = %d',$data['value']));
					$data['value'] = Address::formatData($address);
					break;
			}
			$this->logMessage('getConfigVar',sprintf('return is [%s]',print_r($data,true)),4);
			return $data['value'];
		}
		else return null;
	}

	function getConfigData($name) {
		$data = $this->fetchSingle(sprintf("select * from config where name = '%s' and type = 'config'",$name));
		$this->logMessage('getConfigData',sprintf('return is [%s]',print_r($data,true)),4);
		return $data;
	}

	function getGlobalVar($name) {
		if (array_key_exists($name,$GLOBALS))
			return $GLOBALS[$name];
		else
			return $this->fetchScalar(sprintf("select value from config where name = '%s' and type = 'global'",$name));
	}
	
	function processHeader($html) {
		//$html = $this->m_html;
		if (stripos($html,"<code>") === false) return $html;
		//
		//	ignore <code> in <textarea>'s
		//
		while(stripos($html,"<code>") !== false) {
			$header = Common::extractTag($html,array(0=>"<head>",1=>"</head>"),false);
			$code = Common::extractTag($html,array(0=>"<code>",1=>"</code>"),false);
			$header .= $code;
			$temp = Common::replaceTag($html,array(0=>"<head>",1=>"</head>"),false,$header);
			$html = Common::replaceTag($temp,array(0=>"<code>",1=>"</code>"),true,"");
		}
		return $html;
	}
	
	static function extractTag($source, $tags, $inclusive, $regex = false ) {
		$html = "";
		if ($regex) {
			if (preg_match($tags[0], $source, $matches) !== false)
				if ($inclusive)
					$html = $matches[0];
				else
					$html = $matches[count($matches)-1];
		}
		else {
			if (stripos($source,$tags[0]) === false) return "";
			$start = stripos($source,$tags[0]);
			$end = stripos($source,$tags[1]);
			if ($inclusive)
				$html = substr($source,$start,$end-$start+strlen($tags[1]));
			else
				$html = substr($source, $start+strlen($tags[0]),$end - $start-strlen($tags[1])+1);
		}
		return $html;
	}
	
	static function replaceTag($source, $tags, $inclusive, $replace ) {
		if (stripos($source,$tags[0]) === false) return $source;
		$start = stripos($source,$tags[0]);
		$end = stripos($source,$tags[1]);
		if ($inclusive) {
			$html = substr($source,0,$start-1);
			$html .= $replace;
			$html .= substr($source,$end+strlen($tags[1]));
		}
		else {
			$html = substr($source,0,$start+strlen($tags[0]));
			$html .= $replace;
			$html .= substr($source,$end);
		}
		return $html;
	}

	protected function addEcomMessage($message) {
		$GLOBALS['globals']->addEcomMessage($message);
	}

	protected function addEcomError($message) {
		$GLOBALS['globals']->addEcomError($message);
	}

	protected function showEcomMessages() {
		return $GLOBALS['globals']->showEcomMessages().$GLOBALS['globals']->showEcomErrors();
	}

	protected function addMessage($message) {
		$GLOBALS['globals']->addMessage($message);
	}

	protected function addError($message) {
		$GLOBALS['globals']->addError($message);
	}
	
	protected function showMessages() {
		return $GLOBALS['globals']->showMessages().$GLOBALS['globals']->showErrors();
	}

	protected function showErrors() {
		return $GLOBALS['globals']->showMessages().$GLOBALS['globals']->showErrors();
	}

	protected function logLogin($userid, $admin = false) {
		$stmt = $this->prepare('insert into logins(admin_login,user_id,ip_address,extra) values(?,?,?,?)');
		if (array_key_exists("password",$_REQUEST)) unset($_REQUEST["password"]);
		$stmt->bindParams(array('iiss',$admin,$userid,$_SERVER['REMOTE_ADDR'],print_r($_REQUEST,true)));
		$stmt->execute();
	}

	protected function isAjax() {
		return $this->m_viaAjax;
	}
	
	protected function setAjax($mode) {
		$this->m_viaAjax = $mode;
	}

	//	return a list of folder id's used for recursion, membership etc
	//
	protected function nodeList( $root, $type ) {
		$this->logMessage('nodeList', sprintf('(%s,%s)', $root, $type), 1);
		switch($type) {
			default:
				$tbl = $type;
				$fld = 'id';
				break;
		}
		if ($root == 0) {
			$node = array("left_id"=>0,"right_id"=>9999);
		}
		else {
			if (!$node = $this->fetchSingle(sprintf("select * from %s where %s = %d",$tbl,$fld,$root))) {
				$body = sprintf("A folder [%d] has been deleted from [%s] that is still in use.\nDetails: %s", $root, $tbl, print_r($this,true));
				$this->logMessage('nodeList', $body, 1, defined('DEVMODE') && DEVMODE == 1);
				$ret = array("left_id"=>0,"right_id"=>9999);
			}
		}
		$nodes = $this->fetchAll(sprintf("select %s from %s where left_id >= %d and right_id <= %d order by left_id",$fld, $tbl, $node["left_id"], $node["right_id"]));
		$this->logMessage("nodeList","return [".print_r($nodes,true)."]",3);
		return $nodes;
	}

	//
	//	return an escaped select list from folders [used as an option to the form->add
	//
	//	this used to be a database call but we have to escape part of the data [title - News & Highlights], but not all [&nbsp;]
	//
	//	$o->add("categories","select",array("name"=>"categories","class"=>"def_field_ddl"),$this->nodeSelect($this->id,"stores",2,true,"store list is here"));
	//
	function nodeSelect( $root, $type, $indent = 1, $required = false, $inclusive = false, $description = "-none-", $where = null) {
		if (is_null($description)) $description = "-none-";
		if ((int)$indent < 0) $indent = 1;
		if ((int)$root < 0) $indent = 0;
		$this->logMessage("nodeSelect","($root,$type,$indent,$required,$inclusive,$description,$where)",4);
		$node = array("left_id"=>0,"right_id"=>9999,"level"=>0);
		//$options = is_array($this->options) ? $this->options : array();
		switch($type) {
		default:
			$tbl = $type;
			$fld = "id";
			$title = "title";
			break;
		}
		if ($root != 0) {
			if (!$node = $this->fetchSingle(sprintf("select * from %s where %s = %d %s",$tbl,$fld,$root,$where))) {
				$body = sprintf("A folder [%d] has been deleted from [%s] that is still in use.\nDetails: %s", $root, $tbl, print_r($this,true));
				$this->logMessage('nodeSelect',$body,1,defined('DEVMODE') && DEVMODE == 1);
				$node = array("left_id"=>0,"right_id"=>9999,"level"=>0);
			}
		}
		if ($inclusive) {
			$sql = sprintf("select %s, %s, level from %s where left_id >= %d and right_id <= %d %s order by left_id",$fld, $title, $tbl, $node["left_id"], $node["right_id"],$where);
		}
		else {
			//
			//	note it's > and < here [vs >= and <= for the actual store search]
			//
			$sql = sprintf("select %s, %s, level from %s where left_id > %d and right_id < %d %s order by left_id",$fld, $title, $tbl, $node["left_id"], $node["right_id"], $where);
		}
		$tmp = $this->fetchAll($sql);
		$nodes = array();
		if (!$required)
			$nodes[""] = $description;
		if ($inclusive && $root == 0)
			$nodes[0] = "Root";
		foreach($tmp as $rec) {
			if (!array_key_exists("max_level",$this->m_options) || $rec["level"] - $node['level'] <= $this->m_options["max_level"])
				$nodes[$rec["id"]] = str_repeat("&nbsp;",($rec["level"] - $node['level'])*$indent).htmlspecialchars($rec[$title]);
		}
		$this->logMessage("nodeSelect","return [".print_r($nodes,true)."] sql [$sql]",5);
		return $nodes;
	}

	function logDBMessage($function,$message,$severity) {
		if (defined('DEV') && DEV == 1) {
			$this->addError(sprintf('%s::%s',$function,$message));
		}
		$this->logMessage($function, $message, $severity, true, true);
	}
	
	function beginTransaction($h = null) {
		$this->logMessage(__FUNCTION__,'transaction starting',1);
		if ($h == null) $h = $GLOBALS['globals']->getConnection();
		if (!$this->execute('START TRANSACTION'))
			$this->logDBMessage('beginTransaction', 'transaction start failed', 1, true);
	}
	
	function rollbackTransaction($h = null) {
		$this->logMessage(__FUNCTION__,'transaction rolling back',1);
		if ($h == null) $h = $GLOBALS['globals']->getConnection();
		if (!$this->execute('ROLLBACK'))
			$this->logDBMessage('rollbackTransaction', 'rollback transaction failed', 1, true);
	}
	
	function commitTransaction($h = null) {
		$this->logMessage(__FUNCTION__,'transaction committing',1);
		if ($h == null) $h = $GLOBALS['globals']->getConnection();
		if (!$this->execute('COMMIT'))
			$this->logDBMessage('commitTransaction', 'commit transaction failed', 1, true);
		else
			$this->logMessage('commitTransaction', 'succeeded', 4);
	}
	
	function insertId($h = null) {
		if ($h == null) $h = $GLOBALS['globals']->getConnection();
		return $h->insert_id;
	}
	
	function prepare($sql) {
		$this->logMessage('prepare', sprintf('sql [%s]',$sql), 2);
		$obj = new preparedStatement($sql);
		return $obj;
	}
	
	function fetchSingle($sql,$h = null) {
		if ($h == null) $h = $GLOBALS['globals']->getConnection();
		$this->logMessage(__FUNCTION__,sprintf('sql [%s]',$sql),2);
		if ($r = $h->query($sql)) {
			return $r->fetch_assoc();
		}
		else
			$this->logDBMessage('fetchSingle', sprintf("Error: sql[%s] Msg[%s]", $sql, $h->error), 1);
		return array();
	}
	
	function fetchAll($sql=null,$h = null) {
		//
		//	sql = null req'd for preparedStatement
		//
		if (is_null($sql)) $this->logMessage('fetchAll',sprintf('missing sql parameter [%s]',print_r($this,true)),1,true);
		if ($h == null) $h = $GLOBALS['globals']->getConnection();
		$this->logMessage(__FUNCTION__,sprintf('sql [%s]',$sql),2);
		if ($r = $h->query($sql)) {
			$data = array();
			while($row = $r->fetch_assoc()) {
				$data[] = $row;
			}
			return $data;
		}
		else
			$this->logDBMessage('fetchAll', sprintf("Error: sql[%s] Msg[%s]", $sql, $h->error), 1);
		return array();
	}
	
	function fetchOptions($sql, $h = null) {
		if ($h == null) $h = $GLOBALS['globals']->getConnection();
		$this->logMessage(__FUNCTION__,sprintf('sql [%s]',$sql),2);
		if ($r = $h->query($sql)) {
			$results = array();
			while($row = $r->fetch_array(MYSQLI_NUM)) {
				$results[$row[0]] = $row[1];
			}
			return $results;
		}
		else
			$this->logDBMessage('fetchOptions', sprintf("Error: sql[%s] Msg[%s]", $sql, $h->error), 1);
		return array();
	}

	function fetchScalar($sql, $h = null) {
		if ($h == null) $h = $GLOBALS['globals']->getConnection();
		$this->logMessage(__FUNCTION__,sprintf('sql [%s]',$sql),2);
		if ($r = $h->query($sql)) {
			if ($tmp = $r->fetch_row())
				return $tmp[0];
			else return null;
		}
		else
			$this->logDBMessage('fetchScalar', sprintf("Error: sql[%s] Msg[%s]", $sql, $h->error), 1);
		return array();
	}
	
	function fetchScalarAll($sql, $h = null) {
		if ($h == null) $h = $GLOBALS['globals']->getConnection();
		$this->logMessage(__FUNCTION__,sprintf('sql [%s]',$sql),2);
		if ($r = $h->query($sql)) {
			$data = array();
			while($row = $r->fetch_row()) {
				$data[] = $row[0];
			}
			return $data;
		}
		else
			$this->logDBMessage('fetchScalarAll', sprintf("Error: sql[%s] Msg[%s]", $sql, $h->error), 1);
		return array();
	}
	
	function execute($sql = null, $h = null) {
		//
		//	sql = null req'd for preparedStatement
		//
		if (is_null($sql)) $this->logMessage('execute',sprintf('missing sql parameter [%s]',print_r($this,true)),1,true);
		if ($h == null) $h = $GLOBALS['globals']->getConnection();
		$this->logMessage(__FUNCTION__,sprintf('executing [%s]',$sql),2);
		if ($h->query($sql))
			return true;
		else
			$this->logDBMessage('execute', sprintf("Error: sql[%s] Msg[%s]", $sql, $h->error), 1);
		 return false;
	}
	
	function ajaxReturn( $parms, $stripCodes = array('<code>'=>1,'<script>'=>1) ) {
		$this->logMessage(__FUNCTION__, sprintf('input parms [%s] [%s]',print_r($parms,1),print_r($stripCodes,true)), 3);
		$jsSrc = array();
		if (array_key_exists('html',$parms)) {
			$tmp = $this->processConditionals($parms['html']);
			if (!$this->hasOption("leaveCode")) {
				$code = Common::extractTag($tmp,array(0=>"<code>",1=>"</code>"),false);
				$tmp = Common::replaceTag($tmp,array(0=>"<code>",1=>"</code>"),true,"");
				while (strlen($code) > 0 && array_key_exists('<code>',$stripCodes)) {
					//
					//	strip the <script></script> for the js eval()
					//
					if (($i = strpos($code,'//<![CDATA[')) !== false) {
						$code = substr($code,$i+11);
						$i = strpos($code,'//]]>');
						$code = substr($code,0,$i-1);
					}
					$parms['html'] = $tmp;
					if (array_key_exists('code',$parms))
						$parms['code'] .= $code;
					else
						$parms['code'] = $code;
					$code = '';
					$code = Common::extractTag($tmp,array(0=>"<code>",1=>"</code>"),false);
					$tmp = Common::replaceTag($tmp,array(0=>"<code>",1=>"</code>"),true,"");
				}
				$script = strpos($tmp,'<script');
				while ($script !== false   && array_key_exists('<script>',$stripCodes)) {
					$b = strpos($tmp,'>',$script+1);
					$e = strpos($tmp,'</script>',$b);
					if (($src = strpos($tmp,'src=',$script+1)) !== false && $src < $e) {
						//
						//	reference to an src= script. pass it through
						//
						$this->logMessage(__FUNCTION__,sprintf('passing through script [%s]',substr($tmp,$script,$b-$e)),5);
						$c = "";
					}
					else {
						$c = substr($tmp,$b+1,$e-$b-1);
					}
					$code = $c;
					$this->logMessage(__FUNCTION__,sprintf('<script> script [%d] src [%s] b [%s] e [%s] tmp [%s]',$script,$c,$b,$e,$tmp),5);
					$tmp = substr($tmp,0,$script > 0 ? $script-1 : 0).substr($tmp,$e+10);
					$this->logMessage(__FUNCTION__,sprintf('<code> [%s] tmp [%s]',$code,$tmp),5);
					if (array_key_exists('code',$parms))
						$parms['code'] .= $code;
					else
						$parms['code'] = $code;
					$parms['html'] = $tmp;
					$script = $script >= strlen($tmp) ? false : strpos($tmp,'<script',$script+1);
				}
			}
			else {
				$this->logMessage(__FUNCTION__,sprintf("leaving script intact as per config"),1);
			}
		}
		$parms['messages'] = $this->showMessages();
		if (gettype($parms['status']) != "string")
			$parms['status'] = $parms['status'] ? 'true':'false';
		if (count($jsSrc) > 0)
			$parms['src'] = implode('',$jsSrc);
		$this->logMessage(__FUNCTION__, sprintf('parms [%s]',print_r($parms,1)), 3);
		return json_encode($parms,JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP);
	}

	function pagination($totalRecs, $perPage, &$currPage, $templates = array('prev'=>'backend/forms/paginationPrev.html','next'=>'backend/forms/paginationNext.html','pages'=>'backend/forms/paginationPages.html','wrapper'=>'backend/forms/paginationWrapper.html','spacer'=>'backend/forms/paginationSpacer.html', 'url'=>"getUrl()"),$data = array()) {
		if (array_key_exists('url',$data)) $templates['url'] = $data['url'];
		if (!array_key_exists("url",$templates)) $templates["url"] = "getUrl()";
		$this->logMessage('pagination',sprintf('(%d,%d,%d,[%s])',$totalRecs,$perPage,$currPage,print_r($templates,true)),4);
		if ($perPage > 0)
			$pages = floor(($totalRecs-1)/$perPage)+1;
		else $pages = 0;
		if (!array_key_exists('spacer',$templates)) $templates['spacer'] = 'backend/forms/paginationSpacer.html';
		$this->logMessage('pagination', sprintf('totalRecs = [%d] perPage [%d] currPage [%d] templates [%s] data [%s] total pages [%d]', $totalRecs, $perPage, $currPage, print_r($templates,true), print_r($data,true), $pages), 4);
		if ($currPage > $pages && $pages > 0) {
			$this->logMessage("pagination",sprintf("resetting current page [%d] to [%d]",$currPage,$pages+1),2);
			$currPage = $pages;
		}
		if ($totalRecs == 0 || $totalRecs <= $perPage || $perPage <= 0) return "";
		if ($currPage <=0) $currPage = 1;	// keep everything in readble terms - no page 0
		$pre = new Forms();
		$pre->init($templates['prev'],array());
		$pre->addTag('pageNum',$currPage-1);
		$pre->addTag('text',sprintf('Go to page %d',$currPage-1));
		$pre->addData($data);
		$pre->addTag('url',$templates['url'],false);
		if (array_key_exists("dest",$templates)) $pre->addTag('dest',$templates['dest'],false);
		$post = new Forms();
		$post->init($templates['next'],array());
		$post->addTag('pageNum',$currPage+1);
		$post->addTag('text',sprintf('Go to page %d',$currPage+1));
		$post->addData($data);
		$post->addTag('url',$templates['url'],false);
		if (array_key_exists("dest",$templates)) $post->addTag('dest',$templates['dest'],false);
		$middle = array();
		$tmp = new Forms();
		$tmp->init($templates['pages'],array());
		$tmp->addTag('url',$templates['url'],false);
		if (array_key_exists("dest",$templates)) $tmp->addTag('dest',$templates['dest'],false);
		if ($pages <= 10) {
			for($i = 1; $i <= $pages; $i++) {
				$tmp->addTag('pageNum',$i);
				$tmp->addTag('text',sprintf('Go to page %d',$i));
				$tmp->addData($data);
				$tmp->addTag('active',$i == $currPage ? 'active':'');
				$middle[] = $tmp->show();
			}
		}
		else {
			if ($currPage <= 5 || $currPage >= $pages-4) {
				for($i = 1; $i <= 5; $i++) {
					$tmp->addTag('pageNum',$i);
					$tmp->addTag('text',sprintf('Go to page %d',$i));
					$tmp->addData($data);
					$tmp->addTag('active',$i == $currPage ? 'active':'');
					$middle[] = $tmp->show();
				}
				$t = new Forms();
				$t->init($templates['spacer']);
				$middle[] = $t->show();
				for ($i = $pages - 4; $i <= $pages; $i++) {
					$tmp->addTag('pageNum',$i);
					$tmp->addTag('text',sprintf('Go to page %d',$i));
					$tmp->addData($data);
					$tmp->addTag('active',$i == $currPage ? 'active':'');
					$middle[] = $tmp->show();
				}
			}
			else {
				for($i = 1; $i <= 3; $i++) {
					$tmp->addTag('pageNum',$i);
					$tmp->addTag('text',sprintf('Go to page %d',$i));
					$tmp->addData($data);
					$tmp->addTag('active',$i == $currPage ? 'active':'');
					$middle[] = $tmp->show();
				}
				$t = new Forms();
				$t->init($templates['spacer']);
				$middle[] = $t->show();
				for ($i = $currPage - 1; $i <= $currPage+1;$i++) {
					$tmp->addTag('pageNum',$i);
					$tmp->addTag('text',sprintf('Go to page %d',$i));
					$tmp->addData($data);
					$tmp->addTag('active',$i == $currPage ? 'active':'');
					$middle[] = $tmp->show();
				}
				$t = new Forms();
				$t->init($templates['spacer']);
				$middle[] = $t->show();
				for ($i = $pages - 2; $i <= $pages; $i++) {
					$tmp->addTag('pageNum',$i);
					$tmp->addTag('text',sprintf('Go to page %d',$i));
					$tmp->addData($data);
					$tmp->addTag('active',$i == $currPage ? 'active':'');
					$middle[] = $tmp->show();
				}
			}
		}
		$wrapper = new Forms();
		$wrapper->init($templates['wrapper'],array());
		if ($currPage > 1) $wrapper->addTag('prev',$pre->show(),false);
		if ($currPage < $pages) $wrapper->addTag('next',$post->show(),false);
		$wrapper->addTag('pages',implode('',$middle),false);
		return $wrapper->show();
	}

	function subwords($str, $limit) {
		$words = explode(' ', $str);
		$words = array_slice($words, 0, $limit);
		return implode(' ', $words);
	}

	function highlight($terms,$text) {
		$this->logMessage(__FUNCTION__,sprintf('terms [%s], text [%s]',print_r($terms,true),$text),2);
		for($x = 0; $x < count($terms); $x++) {
			$tmp = str_replace('%','',$terms[$x]);
			$text = preg_replace(sprintf('#%s#i',$tmp),sprintf('<span class="highlight">%s</span>',$tmp),$text);
			$this->logMessage(__FUNCTION__,sprintf('text [%s] term [%s]',$text,$tmp),1);
		}
		return $text;
	}

	function getUrl($class,$id,$data = array()) {
		$this->logMessage("getURL",sprintf("($class,$id)"),3);
		if ($id == 0) return '#';
		$url = '';
		switch($class) {
			case "artist":
				if (!array_key_exists('firstname',$data)) $this->logMessage(__FUNCTION__,sprintf("unknown data [%s]", print_r($data,true)),1);
				$url = sprintf('/%s/%s/%d',preg_replace('#[^a-z0-9_]#i', '-',strtolower($data['firstname'].'-'.$data['lastname'])),$class,$id);
				break;
			case 'artists':
			case 'members':
				$url = sprintf('/%s/%s/%d',preg_replace('#[^a-z0-9_]#i', '-',strtolower(array_key_exists("title",$data) ? $data['title'] : $data["id"])),$class,$id);
				break;
			case 'profile':
				$url = sprintf('/%s/%s/%d',preg_replace('#[^a-z0-9_]#i', '-',strtolower($data['firstname'].'-'.$data['lastname'])),$class,$id);
				break;
			case 'eventday':
				if (method_exists($this->config,'eventday'))
					$url = $this->config->eventday($id,$data);
				else {
					$sd = strtotime($data['date']);
					$url = sprintf('/events/%d/%d/%d/%d',date('d',$sd),date('m',$sd),date('Y',$sd),array_key_exists("folder_id",$data) ? $data["folder_id"] : $this->getModule()["folder_id"]);
				}
				break;
			case 'page':
				$data = $this->fetchSingle(sprintf('select * from content where id = %d',$id));
			case 'menu':
				switch($data['type']) {
					case 'page':
						$tmp = strlen($data['seo_url']) > 0 ? 'seo_url' : 'search_title';
						if ($data['secure'])
							$url = sprintf('https://%s/%s/',HOSTNAME,strtolower($data[$tmp]));
						else
							$url = sprintf('/%s/',strtolower($data[$tmp]));
						break;
					case 'internallink':
						$p = $this->fetchSingle(sprintf('select * from content where id = %d',$data['internal_link']));
						$tmp = strlen($p['seo_url']) > 0 ? 'seo_url' : 'search_title';
						if ($p['secure'])
							$url = sprintf('https://%s/%s/',HOSTNAME,strtolower($p[$tmp]));
						else
							$url = sprintf('/%s/',strtolower($p[$tmp]));
						break;
					case 'externallink':
						$url = sprintf('%s',strtolower($data['external_link']));
						break;
					case 'folder':
						break;
					default:
						if ($id > 0)
							$this->logMessage("getUrl",sprintf("unknown menu type [%s] passed",$data['type']),1,true);
				}
				break;
			case 'store':
				if (array_key_exists('folder_id',$data) && $data['folder_id'] > 0)
					$url = strtolower(sprintf('/%s/%s/%d/%d',preg_replace('#[^a-z0-9_]#i', '-',$data['name']),$class,$id,$data['folder_id']));
				else
					$url = strtolower(sprintf('/%s/%s/%d',preg_replace('#[^a-z0-9_]#i', '-',$data['name']),$class,$id));
				break;
			case 'blog':
			case 'advert':
			case 'news':
			case 'rss':
				if (array_key_exists('folder_id',$data) && $data['folder_id'] > 0)
					$url = strtolower(sprintf('/%s/%s/%d/%d',preg_replace('#[^a-z0-9_]#i', '-',$data['title']),$class,$id,$data['folder_id']));
				else
					$url = strtolower(sprintf('/%s/%s/%d',preg_replace('#[^a-z0-9_]#i', '-',$data['title']),$class,$id));
				break;
			case 'storecat':
			case 'blogcat':
			case 'newscat':
			case 'calendar':
			case 'gallery':
			case 'rsscat':
					$url = strtolower(sprintf('/%s/%s/%d',preg_replace('#[^a-z0-9_]#i', '-',$data['title']),$class,$id));
					break;
			case 'event':
				if (array_key_exists('folder_id',$data) && $data['folder_id'] > 0)
					$url = strtolower(sprintf('/%s/%s/%d/%d',preg_replace('#[^a-z0-9_]#i', '-',$data['name']),$class,$id,$data['folder_id']));
				else
					$url = strtolower(sprintf('/%s/%s/%d',preg_replace('#[^a-z0-9_]#i', '-',$data['name']),$class,$id));
				break;
			case 'category':
					$url = strtolower(sprintf('/%s/%s/%d',preg_replace('#[^a-z0-9_]#i', '-',$data['title']),$class,$data['internal_link'] > 0 ? $data['internal_link'] : $id));
					break;
			case 'product':
				if (array_key_exists('folder_id',$data) && $data['folder_id'] > 0)
					$url = strtolower(sprintf('/%s/%s/%d/%d',preg_replace('#[^a-z0-9_]#i', '-',$data['name']),$class,$id,$data['folder_id']));
				else
					$url = strtolower(sprintf('/%s/%s/%d',preg_replace('#[^a-z0-9_]#i', '-',$data['name']),$class,$id));
				break;
			case 'image':
				if (array_key_exists('folder_id',$data) && $data['folder_id'] > 0)
					$url = strtolower(sprintf('/%s/%s/%d/%d',preg_replace('#[^a-z0-9_]#i', '-',$data['title']),$class,$id,$data['folder_id']));
				else
					$url = strtolower(sprintf('/%s/%s/%d',preg_replace('#[^a-z0-9_]#i', '-',$data['title']),$class,$id));
				break;
			case 'lease':
				if (!($l = $this->fetchSingle(sprintf("select mk.name as make, ml.model from leases l, lease_make mk, lease_model ml where l.id = %d and mk.id = l.make and ml.id = l.model",$data["id"]))))
					$l = array("make"=>"view","model"=>"lease");
				if (array_key_exists('folder_id',$data) && $data['folder_id'] > 0)
					$url = strtolower(sprintf('/%s-%s/%s/%d/%d',preg_replace('#[^a-z0-9_]#i', '-',$l["make"]),preg_replace('#[^a-z0-9_]#i', '-',$l["model"]),$class,$id,$data['folder_id']));
				else
					$url = strtolower(sprintf('/%s-%s/%s/%d',preg_replace('#[^a-z0-9_]#i', '-',$l["make"]),preg_replace('#[^a-z0-9_]#i', '-',$l["model"]),$class,$id));
				break;
			case 'leaseFolder':
			case 'leasefolder':
					$url = strtolower(sprintf('/%s/leases/%d',preg_replace('#[^a-z0-9_]#i', '-',$data['title']),$id));
					break;
			default:
				$url = strtolower(sprintf('/%s/%d',$class,$id));
				$this->logMessage('getUrl',sprintf('unknown class [%s] id [%d] data [%s]',$class,$id,print_r($data,true)),1,true);
		}
		$this->logMessage("getURL",sprintf("return [%s]",$url),3);
		return $url;
	}

	function logMeIn( $username, $password, $rememberMe = false, $bypassId = 0 ) {
		$this->logMessage("logMeIn",sprintf("($username,$password,$rememberMe)"),1);
		//
		//	Modify to allow username only - emails are now allowed to be duplicated
		//
		$sql = sprintf('select * from members where username = "%s" and password = "%s" and deleted = 0 and enabled = 1 and (expires = "0000-00-00" or expires > curdate()) %s',$username, $password, array_key_exists('where_clause',$this->m_module) ? $this->m_module['where_clause'] : '');
		if ($bypassId != 0)
			$sql = sprintf('select * from members where id = %d and deleted = 0',$bypassId);	// allow logins for invoicing purposes
			//$sql = sprintf('select * from members where id = %d and deleted = 0 and enabled = 1 and (expires = "0000-00-00" or expires > curdate())',$bypassId);
		if ($user = $this->fetchSingle($sql)) {
			if ($this->hasOption('enforce_membership') && array_key_exists('folder_id',$this->m_module) && $this->m_module['folder_id'] > 0) {
				//
				//	make sure this user is a member of the selected group
				//
				if (!$this->fetchSingle(sprintf('select 1 from members_by_folder where member_id = %d and folder_id = %d',$user['id'],$this->m_module['folder_id']))) {
					$this->addError('No Access Allowed');
					$user = false;
				}
			}
		}
		if (is_array($user) && count($user) > 0) {
			$this->setUserInfo($user);		
			if ($rememberMe) {
				ob_clean();
				$user = serialize($_SESSION["user"]['info']);
				$dys = $this->getConfigVar("cookie-lifetime");
				setcookie("user|uinfo", $user, time()+$dys*24*3600,"/",HOSTNAME);
			}
			if (!array_key_exists("cart",$_SESSION)) $_SESSION["cart"] = Ecom::initCart();
			if (array_key_exists('cart',$_SESSION)) {
				$this->logMessage('logMeIn','grabbing shipto/billto addresses',3);
				if ($tmp = $this->fetchSingle(sprintf('select * from addresses where deleted = 0 and ownertype = "member" and ownerid = %d and addresstype = %d',$_SESSION['user']['info']['id'],ADDRESS_PICKUP)))
					$_SESSION['cart']['addresses']['shipping'] = $tmp;
				else
					$_SESSION['cart']['addresses']['shipping'] = array('id'=>0,'province_id'=>0,'country_id'=>0);
				$e = new Ecom();
				$e->updateCart();	// updates taxes
			}
			$status = true;
			if (method_exists($this->config,"postLogin"))
				$this->config->postLogin($user);
		}
		else $status = false;
		$this->logMessage("logMeIn",sprintf("return status [%d] sql [%s]",$status,$sql),2);
		return $status;
	}
	
	function getHtmlForm($name,$class = null) {
		if (is_null($class))
			$sql = sprintf('select html from htmlForms where class = %d and type = "%s"',$this->getClassId(get_class($this)),$name);
		else
			$sql = sprintf('select html from htmlForms f, modules m where m.name = "%s" and f.class = m.id and type = "%s"',$class,$name);
		$html = $this->fetchScalar($sql);
		$this->logMessage('getHtmlForm',sprintf('name [%s] return [%s]',$name,$html),3);
		return $html;
	}

	function formatOrder($data) {
		$data['itemCount'] = $this->fetchScalar(sprintf('select sum(quantity) from order_lines where order_id = %d and deleted = 0 and custom_package = "P"',$data['id']));
		$data['formattedTotal'] = $this->my_money_format($data['total']);
		$data['formattedNet'] = $this->my_money_format($data['net']);
		$data['formattedShipping'] = $this->my_money_format($data['shipping']);
		$data['formattedDiscountValue'] = $this->my_money_format($data['discount_value']);
		$data['formattedRedemptionValue'] = $this->my_money_format($data['redemption_value']);
		$data['formattedLineDiscount'] = $this->my_money_format($data['line_discounts']);
		$data['formattedValue'] = $this->my_money_format($data['value']);
		$data['formattedTaxes'] = $this->my_money_format($data['taxes']);
		$data['formattedAuthorizationAmount'] = $this->my_money_format($data['authorization_amount']);
		$data['formattedHandlingFee'] = $this->my_money_format($data['handling']);
		if ($data['discount_type'] == 'D')
			$data['formattedDiscountRate'] = $this->my_money_format($data['discount_rate']);
		else
			$data['formattedDiscountRate'] = sprintf('%.2f%%',$data['discount_rate']);
		$data['formattedCreated'] = date(GLOBAL_DEFAULT_DATETIME_FORMAT,strtotime($data['created']));
		$data['formattedOrderDate'] = date(GLOBAL_DEFAULT_DATETIME_FORMAT,strtotime($data['order_date']));
		$status = $this->fetchAll(sprintf('select * from code_lookups where type="orderStatus" order by sort'));
		$tmp = array();
		foreach($status as $key=>$stat) {
			if (($data['order_status'] & (int)$stat['code']) == (int)$stat['code'])
				$tmp[] = $stat['value'];
		}
		$data['formattedStatus'] = implode(", ",$tmp);
		$data['formattedShipVia'] = $this->fetchScalar(sprintf('select value from code_lookups where type="ship_via" and id=%d',$data['ship_via']));
		$this->logMessage('formatOrder',sprintf('return [%s]',print_r($data,true)),4);
		return $data;
	}

	function formatOrderLine($data) {
		$data['formattedPrice'] = $this->my_money_format($data['price']);
		$data['formattedTotal'] = $this->my_money_format($data['total']);
		$data['formattedValue'] = $this->my_money_format($data['value']);
		$data['formattedTaxes'] = $this->my_money_format($data['taxes']);
		$data['formattedShipping'] = $this->my_money_format($data['shipping']);
		$data['formattedDiscountValue'] = $this->my_money_format($data['discount_value']);
		if ($data['discount_type'] == 'D')
			$data['formattedDiscountRate'] = $this->my_money_format($data['discount_rate']);
		else
			$data['formattedDiscountRate'] = sprintf('%.2f%%',$data['discount_rate']);
		if ($data['options_id'] != 0) {
			$data['optionsName'] = $this->fetchScalar(sprintf('select teaser from product_options where id = %d',$data['options_id']));
		}
		else $data['optionsName'] = '';
		if ($data['color'] != 0) {
			$data['colorName'] = $this->fetchScalar(sprintf('select value from code_lookups c where c.id = %d',$data['color']));
		}	
		else $data['colorName'] = '';
		if ($data['size'] != 0) {
			$data['sizeName'] = $this->fetchScalar(sprintf('select value from code_lookups c where c.id = %d',$data['size']));
		}
		else $data['sizeName'] = '';
		if ($data['recurring_period'] != 0) {
			$data['recurringInfo'] = $this->fetchSingle(sprintf('select * from product_recurring where id = %d',$data['recurring_period']));
			if ($data['recurringInfo']['percent_or_dollar'] == 'P')
				$data['recurringInfo']['formattedDiscountRate'] = sprintf("%.2f%%",$data['recurringInfo']['discount_rate']);
			else
				$data['recurringInfo']['formattedDiscountRate'] = $this->my_money_format($data['recurringInfo']['discount_rate']);
		}
		$wt = $this->fetchSingle(sprintf("select l.code, sum(od.weight*od.quantity) as weight from code_lookups l, order_lines_dimensions od, orders o where od.line_id = %d and od.order_id = %d and o.id = od.order_id and l.id = o.custom_weight_code", $data["line_id"], $data["order_id"]));
		$data["weight"] = $wt["weight"];
		$data["weightCode"] = $wt["code"];
		return $data;
	}

	function getClassId($className) {
		$this->logMessage('getClassId',sprintf('(%s)',$className),1);
		return $this->fetchScalar(sprintf('select id from modules where classname = "%s"',$className));
	}

	function deleteCheck($class, $id, &$errors) {
		$this->logMessage('deleteCheck',sprintf('(%s,%s)',$class,$id),1);
		$errors = array();
		$sql = sprintf('
SELECT mp.*, p.*, c.* FROM modules_by_page mp, fetemplates f, modules m, pages p, content c
where f.id = mp.fetemplate_id and m.id = f.module_id
and m.classname = "%s"
and mp.folder_id = %d
and p.id = mp.page_id
and p.id = (select max(p1.id) from pages p1 where p1.content_id = p.content_id)
and c.id = p.content_id and mp.page_type = "P"', $class, $id);
		$pages = $this->fetchAll($sql);
		$this->logMessage('deleteCheck',sprintf('sql [%s] count [%d]',$sql,count($pages)),3);
		foreach($pages as $page) {
			$errors[] = 'Page: '.$page['title'];
		}
		$sql = sprintf('
SELECT mp.*, t.*
FROM modules_by_page mp, fetemplates f, modules m, templates t
WHERE f.id = mp.fetemplate_id
and m.id = f.module_id
and m.classname = "%s"
and mp.folder_id = %d
and mp.page_type = "T"
and t.id = mp.page_id
and t.id = (select max(t1.id) from templates t1 where template_id = t.template_id)', $class, $id );
		$templates = $this->fetchAll($sql);
		$this->logMessage('deleteCheck',sprintf('sql [%s] count [%d]',$sql,count($templates)),3);
		foreach($templates as $template) {
			$errors[] = 'Template: '.$template['title'];
		}
		return count($errors) == 0;
	}

	function processSlashes() {
		$_POST = stripslashes_deep($_POST);
		$_GET = stripslashes_deep($_GET);
		$_REQUEST = stripslashes_deep($_REQUEST);
	}

	function processUploadedFiles($allowedTypes,&$return,&$messages) {
		$this->logMessage('processUploadedFiles',sprintf('allowed [%s]',implode(',',$allowedTypes)),1);
		$return = array();
		$messages = array();
		$status = true;
		foreach($_FILES as $key=>$file) {
			$file['type'] = str_replace('"','',$file['type']);	// some mime types come in quoted "application/x-zip"
			if (strlen($file['name']) > 0) {
				if ($file['error'] != 0) {
					$status = false;
					$messages[] = sprintf('An error occurred uploading your file %s',$file['name']);
					break;
				}
				$sql = sprintf('select extra from code_lookups where type ="MimeUploadTypes" and code in ("%s")',implode('","',$allowedTypes));
				$types = $this->fetchScalarAll($sql);
				$this->logMessage('processUploadedFiles',sprintf('allowed types [%s] vs [%s] sql [%s]',implode('|',$types),$file['type'],$sql),2);
				if (strpos(implode('|',$types),$file['type']) === false) {
					$status = false;
					$messages[] = sprintf('File %s is not of an allowed type',$file['name']);
					break;
				}
				$tmp = explode('.',$file['name']);
				$tmp[count($tmp)-2] .= sprintf('-%d',random_int(1000,10000));
				$name = sprintf('files/%s',preg_replace('#[^a-z0-9.]#i', '-',strtolower(implode('.',$tmp))));
				$return[$key] = array('name'=>'/'.$name,'type'=>$this->fetchScalar(sprintf('select code from code_lookups where type="MimeUploadTypes" and extra like "%%%s%%"',$file['type'])));
				if (defined('FRONTEND')) 
					$name = './'.$name;
				else
					$name = '../'.$name;
				if (!move_uploaded_file($file['tmp_name'],$name)) {
					$messages[] = sprintf('Unable to move the file [%s]',$name);
					$status = false;
				}
				$this->logMessage('processUploadedFiles',sprintf('rename [%s] as [%s]',$file['tmp_name'],$name),2);
			}
		}
		$this->logMessage('processUploadedFiles',sprintf('return [%s] files[%s] messages [%s] ',$status,print_r($return,true),print_r($messages,true)),3);
		return $status;
	}

	function setQueryOptions($queryString) {
		$queryString = str_replace('%20','+',$queryString);
		$this->logMessage('setQueryOptions',sprintf('parms [%s]',$queryString),2);
		$keywords = explode('+',$queryString);
		$source = $this->fetchAll(sprintf('select * from search_keywords where keyword in ("%s")',implode('","',$keywords)));
		$this->logMessage('setQueryOptions',sprintf('found [%s]',print_r($source,true)),1);
		$totals = array();
		foreach($source as $key=>$rec) {
			$totals[$rec['folder_id']] = $rec['weight'] + (array_key_exists($rec['folder_id'],$totals) ? $totals[$rec['folder_id']] : 0);
		}
		$winner = 0;
		$max = 0;
		foreach($totals as $folder=>$value) {
			if ($value > $max) {
				$max = $value;
				$winner = $folder;
			}
		}
		$this->m_searchWinner = $winner;
		$this->logMessage('setQueryOptions',sprintf('totals [%s] winner is [%s] this [%s]',print_r($totals,true),$winner,print_r($this,true)),1);
		return $winner;
	}

	function twitterPost($title, $text, $url, $data = array()) {
		$this->logMessage(__FUNCTION__,sprintf('parms title [%s] text [%s] url [%s] data [%s]',$title,$text,$url,print_r($data,true)),1);
		$settings = $GLOBALS['twitter'];
		require_once('classes/TwitterAPIExchange.php');
		$status = $twitter = new TwitterAPIExchange($settings['settings']);
		$text = str_replace('&nbsp;',' ',$text);
		$text = html_entity_decode(strip_tags($text));
		$mode = $settings['tweet'];
		$maxLength=140;
		$postfields = array();
		if (array_key_exists('image',$data) && strlen($data['image']) > 0) {
			$mode = $settings['tweetWithMedia'];
			$postfields['media[]'] = file_get_contents(sprintf(realpath('..%s',$data['image'])));
			$maxLength -= 23;
		}
		if (array_key_exists('image1',$data) && strlen($data['image1']) > 0) {
			$mode = $settings['tweetWithMedia'];
			$postfields['media[]'] = file_get_contents(sprintf(realpath('..%s',$data['image1'])));
			$maxLength -= 23;
		}
		if (strlen($url) > 0) {
			//
			//	shorten the url with bitly
			//
			$bitly = $GLOBALS['bitly'];
			$s = new Snoopy();
			$s->host = $bitly['url'];
			$s->port = 443;
			$s->httpmethod='GET';
			$vars = array(
				'access_token'=>$bitly['access_token'],
				'longUrl'=>$url
			);
			$s->curl_path = $bitly['curl_path'];
			$s->submit($bitly['url'],$vars);
			$r = json_decode($s->results,true);
			if ($r['status_txt'] == 'OK') $url = $r['data']['url'];
			$l = strlen($text) + strlen($url);
			if ($l > $maxLength) {
				$text = sprintf('%s...',substr($text, 0, $maxLength - strlen($url) - 6));
				$this->logMessage(__FUNCTION__,sprintf('shortened to [%s] length [%s]',$text,strlen($text)),1);
			}
			$postfields = array_merge($postfields,array(
				'status'=>sprintf('%s %s',$text,strlen($url) > 0 ? $url : '')
			));
		}
		else {
			$l = strlen($text);
			if ($l > $maxLength) $text = sprintf('%s...',substr($text, 0, $maxLength - 6));
			$postfields = array_merge($postfields,array(
				'status'=>$text
			));
		}
		try {
			$status = $twitter->buildOauth($mode, 'POST');
			$status = $twitter->setPostFields($postfields);
			$status = $twitter->performRequest();
			$obj = json_decode($status,true);
			$this->logMessage(__FUNCTION__,sprintf('json decode [%s]',print_r($obj,true)),4);
			if (array_key_exists('errors',$obj)) {
				foreach($obj['errors'] as $key=>$value) {
					$this->addError(sprintf('twitterPost: %s Code:%s',$value['message'],$value['code']));
				}
				$status = false;
			}
			elseif (array_key_exists('error',$obj)) {
				$this->addError(sprintf('twitterPost: %s',$obj['message']));
				$status = false;
			}
			else $status = true;
		}
		catch(Exception $err) {
			$this->addError(sprintf('twitterPost: %s',$err->getMessage()));
			$status = false;
		}
		return $status;
	}

	function facebookPost($title, $text, $url, $data = array()) {
		$params = $GLOBALS['facebook'];
		$status = false;
		require_once('./classes/Facebook/autoload.php');
		if (!(array_key_exists("facebook",$_SESSION) && array_key_exists("status",$_SESSION["facebook"]))) {
			$this->addError("You do not appear to be logged in to Facebook");
			return false;
		}
		if ($_SESSION["facebook"]["status"] != "connected") {
			$this->addError("You haven't allowed us to post to Facebook yet");
			return false;
		}
		if (!(array_key_exists("response",$_SESSION["facebook"]) && 
					array_key_exists("authResponse",$_SESSION["facebook"]["response"]) &&
					array_key_exists("accessToken",$_SESSION["facebook"]["response"]["authResponse"]))) {
			$this->addError("We couldn't find a Facebook token to post with");
			$this->logMessage(__FUNCTION__,sprintf("no token found"),1,true,true);
			return false;
		}
		try {
			$fb = new Facebook\Facebook([
				'app_id'=>$params["appId"],
				'app_secret'=>$params["secret"],
				'default_graph_version'=>'v2.4'
			]);
			$vars = array(
				'message'=>utf8_encode($this->decode_entities_full(strip_tags($text))),
				'name'=>$title,
				'link'=>$url
			);
			if (array_key_exists("pageId",$params)) {
				$r = $fb->get("/me/accounts",$_SESSION["facebook"]["response"]["authResponse"]["accessToken"]);
				$this->logMessage(__FUNCTION__,sprintf("pageId response [%s]",print_r($r,true)),1);
				$this->logMessage(__FUNCTION__,sprintf("decoded body [%s]",print_r($r->getDecodedBody(),true)),1);
				$status = false;
				foreach($r->getDecodedBody()["data"] as $key=>$pg) {
					if ($pg["id"] == $params["pageId"]) {
						$this->logMessage(__FUNCTION__,sprintf("found the page to update"),1);
						$response = $fb->post(sprintf("/%s/feed",$params["pageId"]), $vars, $pg["access_token"]);
						$status = true;
					}
				}
				if (!$status) {
					$this->logMessage(__FUNCTION__,sprintf("invalid page request [%s] response [%s]", $params["pageId"],print_r($r,true)),1,true);
					$this->addError("We couldn't find your requested page to update");
					return false;
				}
			}
			else
				$response = $fb->post(sprintf("/%s/feed",$_SESSION["facebook"]["response"]["authResponse"]["userID"]), $vars, $_SESSION["facebook"]["response"]["authResponse"]["accessToken"]);
			if ($response->getHttpStatusCode() != 200) {
				$this->logMessage(__FUNCTION__,sprintf("facebook post failed title [%s] text [%s] url [%s] data [%s] response[%]", $title, $url, print_r($data,true), print_r($response,true)),1,true,true);
			}
			$status = $response->getHttpStatusCode() == 200;
		}
		catch(Facebook\Exceptions\FacebookAuthenticationException $e) {
			$this->logMessage(__FUNCTION__,sprintf("fatal facebook error [%] title [%s] text [%s] url [%s] data [%s]", print_r($e,true), $title, $text, $url, print_r($data,true)),1,true,true);
			$this->addError($e->getResponseData()["error"]["message"]);
		}
		catch (Facebook\Exceptions\FacebookResponseException $e) {
			$this->logMessage(__FUNCTION__,sprintf("fatal facebook error [%s] title [%s] text [%s] url [%s] data [%s]", print_r($e,true), $title, $text, $url, print_r($data,true)),1,true,true);
			$this->addError($e->getResponseData()["error"]["message"]);
		}
		return $status;
	}

	function checkArray($test,$list) {
		$this->logMessage(__FUNCTION__,sprintf('test [%s] list [%s]',print_r($test,true),print_r($list,true)),4);
		$tmp = explode(':',$test);
		$loc = $list;
		foreach($tmp as $xx=>$yy) {
			if (!is_array($loc)) {
				$this->logMessage(__FUNCTION__,sprintf("expected an array found [%s], parms test [%s] list [%s] request [%s]",print_r($loc,true),
					print_r($test,true), print_r($list,true), print_r($_REQUEST,true)), 4);
				return "";
			}
			if (array_key_exists($yy,$loc))
				$loc = $loc[$yy];
			else {
				$this->logMessage(__FUNCTION__,sprintf('bail - field [%s] not found in [%s]',$test,print_r($loc,true)),4);
				return "";
			}
		}
		$this->logMessage(__FUNCTION__,sprintf('field exists loc is now [%s]',print_r($loc,true)),4);
		return $loc;
	}

	function decode_entities_full($string, $quotes = ENT_COMPAT, $charset = 'ISO-8859-1') {
  	return html_entity_decode(preg_replace_callback('/&([a-zA-Z][a-zA-Z0-9]+);/', 'facebook_convert_entity', $string), $quotes, $charset); 
	}

	function age($pubDate) {
		$age = strtotime(date('r')) - strtotime($pubDate);
		$days = (int)($age / (3600.*24.));
		if ($days > 0)
			if ($days == 1)
				return "yesterday";
			else
				return $days." days ago";
		$age = $age - $days*3600*24;
		$hrs = (int)($age / 3600.);
		if ($hrs > 0)
			return sprintf("%d hour%s ago",$hrs,$hrs > 1 ? "s":"");
		$age = $age - $hrs * 3600;
		$min = (int)($age / 60.);
		return sprintf("%d minute%s ago",$min,$min > 1 ? "s":"");
	}

	function processConditionals($html,$level=0) {
		if ($this->hasOption("leaveConditionals")) return $html;
		if ($level>40) {
			//
			//	assume something has gone horribly wrong
			//
			$this->logMessage(__FUNCTION__,sprintf("max levels exceeded [%s] html [%s]",$level,$html),1,true,true);
			return $html;
		}
		$this->logMessage(__FUNCTION__,sprintf("input [%s] level %d",$html,$level),4);
		if ($level == 0) {
			$ct = preg_match_all('#<!@@(.*?)@@!>#', $html, $matches);
			foreach($matches[0] as $key=>$value) {
				try {
					$this->logMessage(__FUNCTION__,sprintf("evaling [%s] within [%s] [%s]",$matches[1][$key], print_r($matches,true), $html),3);
					$result = eval(sprintf("return %s;",$matches[1][$key]));
					$html = str_replace($matches[0][$key],$result,$html);
				}
				catch(Exception $err) {
					$this->logMessage(__FUNCTION__,sprintf("Error parsing [%s] from [%s] [%s] this [%s]", $matches[0][$key], $html, print_r($err,true), print_r($this,true)),1,true,true);
				}
			}
		}
		$ct = preg_match_all('#<!if(.*?)!>#', $html, $matches);
		$this->logMessage(__FUNCTION__,sprintf("input [%s] match count [%s] level [%d]",$html,$ct,$level),5);
		if ($ct > 1) {
			$pos = strpos($html,$matches[0][0]);
			$pre = substr($html,0,$pos);
			$this->logMessage(__FUNCTION__,sprintf("pos [%s]\npre [%s]\nrest [%s]\n",$pos,$pre,substr($html,$pos+strlen($matches[0][0]))),4);
			$temp = $this->processConditionals(substr($html,$pos+strlen($matches[0][0])),$level+1);
			$this->logMessage(__FUNCTION__,sprintf("return was [%s]\npre [%s]\n",$temp,$pre),4);
			$fi = strpos($temp,"<!fi!>",0);
			$tail = substr($temp,$fi+6);
			$cond = substr($temp,0,$fi);
			$e = strpos($cond,"<!else!>",0);
			if ($e !== false) {
				$else = substr($cond,$e+8);
				$cond = substr($cond,0,$e);
			}
			else $else = "";
			$this->logMessage(__FUNCTION__,sprintf("[%d] fi [%d]\npre [%s]\ncond [%s]\ntail [%s]\n",$level,$fi,$pre,$cond,$tail),4);
			if (eval(sprintf("return %s;",$matches[1][0]))) {
				$else = "";
			}
			else {
				$cond = "";
			}
			$html = $pre.$cond.$else.$tail;
		}
		else if ($ct == 1) {
			$pos = strpos($html,$matches[0][0]);
			$this->logMessage(__FUNCTION__,sprintf("evaling [%s] within [%s] [%s]",print_r($matches[1][0],true), print_r($matches,true), $html),3);
			$val = eval(sprintf("return %s;",$matches[1][0]));
			$tail = substr($html,strpos($html,"<!fi!>",$pos+1)+6);
			$pre = substr($html,0,strpos($html,$matches[0][0]));
			$cond = substr($html,strpos($html,$matches[0][0]) + strlen($matches[0][0]));
			$cond = substr($cond,0,strpos($cond,"<!fi!>"));
			$e = strpos($cond,"<!else!>",0);
			if ($e !== false) {
				$else = substr($cond,$e+8);
				$cond = substr($cond,0,$e);
			}
			else {
				$else = "";
			}
			if ($val) 
				$else = "";
			else 
				$cond = "";
			$this->logMessage(__FUNCTION__,sprintf("pre [%s]\ncond [%s]\nelse [%s]\ntail [%s]\n",$pre,$cond,$else,$tail),4);
			$html = $pre.$cond.$else.$tail;
		}
		$this->logMessage(__FUNCTION__,sprintf("return[%d] [%s]\n",$level,$html),3);
		return $html;
	}

	function my_money_format($value,$mask = "$###,##0.00") {
		$c = new custom(0);
		if (method_exists($c,'custom_money_format')) {
			return $c->custom_money_format($value,$mask);
		}
		$fmt = new NumberFormatter(CURRENCY, NumberFormatter::CURRENCY);
		$fmt->setPattern($mask);
		$cart = Ecom::getCart();
		if ($this->hasOption('convertCurrency') && array_key_exists('exchange_format',$cart['header']) && strlen($cart['header']['exchange_format']) > 0) {
#			return money_format($cart['header']['exchange_format'],round($value * ($convert ? $cart['header']['exchange_rate'] : 1.0),2));
			return $fmt->formatCurrency($value, DOLLARS);
		}
		else
#			return money_format(GLOBAL_DEFAULT_CURRENCY_FORMAT,$value);
			return $fmt->formatCurrency($value, DOLLARS);
	}

	function XMLToArrayFlat($xml, &$return, $path='', $root=false) {
		$children = array();
		if ($xml instanceof SimpleXMLElement) {
			$children = $xml->children();
			if ($root) { // we're at root
				$path .= '/'.$xml->getName();
			}
		}
		if ( count($children) == 0 ){
			$return[$path] = (string)$xml;
			return;
		}
		$seen=array();
		foreach ($children as $child => $value) {
			$childname = ($child instanceof SimpleXMLElement)?$child->getName():$child;
			if ( !isset($seen[$childname])){
				$seen[$childname]=0;
			}
			$seen[$childname]++;
			$this->XMLToArrayFlat($value, $return, $path.'/'.$child.'['.$seen[$childname].']');
		}
	} 

	public static function geoCode($address, &$lat, &$long ) {
		$c = new Common();
		$c->logMessage(__FUNCTION__,sprintf("received [%s]", print_r($address,true)),4);
		$url = urlencode(sprintf("%s, %s, %s %s",$address['line1'],$address['city'],
			$c->fetchScalar(sprintf('select province_code from provinces where id = %d',$address['province_id'])),$address['postalcode']));
		$s = new Snoopy();
		$s->host = 'https://maps.googleapis.com/maps/api/geocode/json';
		$s->port = 443;
		$s->curl_path = $GLOBALS['curl_path'];
		$s->fetch(sprintf('https://maps.googleapis.com/maps/api/geocode/json?address=%s&sensor=false&key=%s',$url,$c->getConfigVar("google_maps_key")));
		$result = $s->results;
		$valid = false;
		$c->logMessage(__FUNCTION__,sprintf("request [%s] from url [%s]", print_r($s,true), $url),4);
		if ($s->status == 200) {
			$result = json_decode($result,true);
			if (array_key_exists("results",$result) && is_array($result["results"]) && count($result["results"]) > 0) {
				$tmp = $result["results"][0];
				if (array_key_exists("geometry",$tmp) && array_key_exists("location",$tmp["geometry"])) {
					$lat = $tmp["geometry"]["location"]["lat"];
					$long = $tmp["geometry"]["location"]["lng"];
					$valid = true;
				}
				if (array_key_exists("address_components",$tmp)) {
					//
					//	Check the postal code - see if it is correct
					//
					foreach($tmp["address_components"] as $k=>$v) {
						$c->logMessage(__FUNCTION__,sprintf("v [%s]", print_r($v,true)), 4);
						foreach($v["types"] as $sk=>$sv) {
							if ($sv == "administrative_area_level_1") {
								$c->logMessage(__FUNCTION__,sprintf("*** province validation [%s] vs [%s]", print_r($sv,true), print_r($address,true)),4);
								//
								//	Province validation
								//
								if (array_key_exists("short_name",$v) & $prov = $c->fetchSingle(sprintf("select * from provinces where province_code = '%s'", $v["short_name"]))) {
									if ($address["province_id"] != $prov["id"]) {
										$valid = false;
										$c->addError(sprintf("Province Mismatch [%s] vs [%s]", $v["short_name"], $c->fetchScalar(sprintf("select province_code from provinces where id = %d", $address["province_id"]))));
									}
								}
							}
							if ($sv == "postal_code") {
								//
								//	Postal code validation [FSA only] - and google seems to have a lot of mistakes
								//
								$c->logMessage(__FUNCTION__,sprintf("test [%s] vs [%s]", str_replace(" ","",$address["postalcode"]), str_replace(" ","",$v["long_name"])),4);
								if (strpos($v["long_name"],substr($address["postalcode"],0,3),0) === false) {
									$c->logMessage(__FUNCTION__,sprintf("invalid postal code"),1);
									$c->addError(sprintf("Postal Code Mismatch [%s] vs [%s]", $address["postalcode"],$v["long_name"]));
									if (!$c->checkArray("mgmt:user:id",$_SESSION))
										$valid = false;
									else {
									 		$c->logMessage(__FUNCTION__,sprintf("postal code check override kicked in"),1);
									}
								}
							}
						}
					}
				}
			}
		}
		else {
			$c->logMessage(__FUNCTION__, sprintf("Address geocode failed, status [%s] return is [%s] from [%s] [%s]", $s->status, print_r($s,true), print_r($address,true), $url), 1,true);
		}
		if (!$valid)
			$c->addError("Unable to geocode address for GPS co-ordinates");
		return $valid;
	}

	function isNull($value) {
		if (is_string($value)) return strlen($value) == 0;
		if (is_numeric($value)) return strlen($value) == 0;
		if (is_array($value)) return count($value) == 0;
		return strlen(sprintf("%s",$value)) == 0;
	}

	function escape_string($s) {
		return mysqli_real_escape_string($GLOBALS['globals']->getConnection(),$s);
	}

	function getThumbnail($hoster,$video_id,$size="large") {
		$result = "";
		switch(strtolower($hoster)) {
		case "vimeo":
			$s = new Snoopy();
			$url = sprintf("http://vimeo.com/api/v2/video/%s.json",$video_id);
			$s->host = "vimeo.com";
			$s->submit_method = 'GET';
			$s->httpmethod = 'GET';
			$s->curl_path = $GLOBALS['curl_path'];
			$s->fetch($url);
			if ($s->status == 200) {
				$r = json_decode($s->results,true);
				$this->logMessage(__FUNCTION__,sprintf("returned result is [%s] from [%s]",print_r($r,true),print_r($s,true)),4);
				if (is_array($r))
					$result = $r[0][sprintf("thumbnail_%s",$size)];
			}
			else $this->addError(sprintf("Could not retrieve the Vimeo thumbnail url"));
			break;
		default:
		}
		return $result;
	}

	function processPoints( $orderid ) {
		$order = $this->fetchSingle(sprintf("select * from orders where id = %d", $orderid));
		if ($order['points_redeemed'] > 0) {
			$flds = array('member_id'=>$order["member_id"],'created_date'=>date('Y-m-d H:i:s'), 'points'=>-$order['points_redeemed'],'created_type'=>'Redemption','created_reference'=>$orderid);
			$p_r = $this->prepare(sprintf('insert into members_points(%s) values(?%s)', implode(",",array_keys($flds)), str_repeat(", ?", count($flds)-1)));
			$p_r->bindParams(array_merge(array(str_repeat("s",count($flds))),array_values($flds)));
			$p_r->execute();
		}
		if ($order['points_collected'] > 0) {
			$flds = array('member_id'=>$order["member_id"],'created_date'=>date('Y-m-d H:i:s'), 'points'=>$order['points_collected'],'created_type'=>'Purchase','created_reference'=>$orderid);
			$p_r = $this->prepare(sprintf('insert into members_points(%s) values(?%s)', implode(",",array_keys($flds)), str_repeat(", ?", count($flds)-1)));
			$p_r->bindParams(array_merge(array(str_repeat("s",count($flds))),array_values($flds)));
			$p_r->execute();
		}
	}

	function calc_driver_allocations($o_id) {
		$this->logMessage(__FUNCTION__,sprintf("calc order %d",$o_id),1);
		if (!$pu_driver = $this->fetchSingle(sprintf("select cd.id as del_id, d.* from custom_delivery cd, drivers d where cd.service_type='P' and cd.order_id = %d and d.id = cd.driver_id",$o_id)))
			$pu_driver = array("id"=>0,"vehicle_id"=>0,"del_id"=>0);
		if (!$del_driver = $this->fetchSingle(sprintf("select cd.id as del_id, d.* from custom_delivery cd, drivers d where cd.service_type='D' and cd.order_id = %d and d.id = cd.driver_id",$o_id)))
			$del_driver = array("id"=>0,"vehicle_id"=>0,"del_id"=>0);
		$prod = $this->fetchSingle(sprintf("select p.* from order_lines ol, product p where ol.order_id = %d and p.id = ol.product_id and custom_package = 'S' and ol.deleted = 0", $o_id));
		if ($prod["is_fedex"]) {
			$this->logMessage(__FUNCTION__,sprintf("fedex order"),1);
			//
			//	if a fedex p/u we dont care - never gets used here, otherwise set p/u to product fedex rate
			//
			$this->execute(sprintf("update custom_delivery set percent_of_delivery = %s where id = %d", $prod["custom_driver_split"], is_array($pu_driver) ? $pu_driver["del_id"] : 0));
			$this->execute(sprintf("update custom_delivery set percent_of_delivery = %s where id = %d", 100 - $prod["custom_driver_split"], is_array($del_driver) ? $del_driver["del_id"] : 0));
		}
		else {
			if ($pu_driver["id"] == $del_driver["id"]) {// || $pu_driver["vehicle_id"] == $del_driver["vehicle_id"]) {
				$this->logMessage(__FUNCTION__,sprintf("same vehicle type or driver"),1);
				$this->execute(sprintf("update custom_delivery set percent_of_delivery = 50 where order_id = %d", $o_id));
			}
			else {
				//
				//	this needs an update to check the service product and get the split from there
				//
				if ($pu_driver["vehicle_id"] == VEHICLE_BIKE) {
					//
					//	same day - both sides split1, overnight p/u split 1
					//
					$this->logMessage(__FUNCTION__,sprintf("bike p/u"),1);
					$this->execute(sprintf("update custom_delivery set percent_of_delivery = %f where id = %d", $prod["custom_biker_split1"], $pu_driver["del_id"]));
					$this->execute(sprintf("update custom_delivery set percent_of_delivery = %f where id = %d", 100 - $prod["custom_biker_split1"], $del_driver["del_id"]));
				}
				else {
					//
					//	car pickup
					//
					if ($del_driver["vehicle_id"] == VEHICLE_BIKE) {
						if (!$prod["custom_same_day"]) {
							$this->logMessage(__FUNCTION__,sprintf("bike del overnight"),1);
							$this->execute(sprintf("update custom_delivery set percent_of_delivery = %f where id = %d", $prod["custom_biker_split2"], $del_driver["del_id"]));
							$this->execute(sprintf("update custom_delivery set percent_of_delivery = %f where id = %d", 100 - $prod["custom_biker_split2"], $pu_driver["del_id"]));						
						}
						else {
							$this->logMessage(__FUNCTION__,sprintf("bike del same day"),1);
							$this->execute(sprintf("update custom_delivery set percent_of_delivery = %f where id = %d", $prod["custom_biker_split1"], $del_driver["del_id"]));
							$this->execute(sprintf("update custom_delivery set percent_of_delivery = %f where id = %d", 100 - $prod["custom_biker_split1"], $pu_driver["del_id"]));						
						}
					}
					else {
						$this->logMessage(__FUNCTION__,sprintf("car p/u & del - new logic kicks in here [different p/u del driver]"),1);
						$this->execute(sprintf("update custom_delivery set percent_of_delivery = %f where id = %d", $prod["custom_driver_split"], $del_driver["del_id"]));
						$this->execute(sprintf("update custom_delivery set percent_of_delivery = %f where id = %d", 100 - $prod["custom_driver_split"], $pu_driver["del_id"]));
					}
				}
			}
		}
		//
		//	check for car usages - remove if no cars, possibly add is not there
		//
		//$this->recalcDowntown($o_id);
		$this->calculateCommissions($o_id);
	}

	function calculateCommissions($o_id) {
		$this->logMessage(__FUNCTION__,sprintf("calc order %d",$o_id),1);
		//$value = $this->fetchScalar(sprintf("select sum(o.value) from order_lines o, product p where o.order_id = %d and p.id = o.product_id and p.custom_commission = 1",$o_id));
		//$this->execute(sprintf("update orders set custom_commissionable_amt = %s where id = %s",(float)$value,$o_id));
		if (!$actions = $this->fetchAll(sprintf("select cd.*, d.commission, d.after_hours_commission, v.fuel_charge from custom_delivery cd, drivers d, vehicles v where cd.order_id = %d and d.id = cd.driver_id and v.id = d.vehicle_id",$o_id)))
			return;
		$group = $this->fetchSingle(sprintf("select f.* from members_folders f, members_by_folder m, orders o where o.id = %d and m.member_id = o.member_id and f.id = m.folder_id limit 1",$o_id));
		$member = $this->fetchSingle(sprintf("select m.* from members m, orders o where o.id = %d and m.id = o.member_id",$o_id));
		$recs = $this->fetchAll(sprintf("select o.* from order_lines o where o.order_id = %d and o.product_id = %d and o.deleted = 0",$o_id,$group["custom_fuel"]));
		$recalc = false;
		$fuelCharge = 0;
		$this->logMessage(__FUNCTION__, sprintf("actions [%s]", print_r($actions,true)), 1);
		if ((array_key_exists(0,$actions) && $actions[0]["fuel_charge"] == 1) || (array_key_exists(1,$actions) && $actions[1]["fuel_charge"] == 1)) {
			//
			//	check for fuel exempt - remove/create/recalculate as required
			//
			$fuelExempt = 0;
			if ($g_override = $this->fetchSingle(sprintf("select mpo.* from custom_member_product_options mpo, order_lines ol where ol.order_id = %d and ol.custom_package = 'S' and ol.deleted = 0 and mpo.member_id = %d and mpo.product_id = ol.product_id and mpo.isgroup = 1", $o_id, $group["id"]))) {
				$fuelExempt = $g_override["fuel_exempt"];
				$this->logMessage(__FUNCTION__,sprintf("group fuel exempt [%s]", print_r($g_override,true)),1);
			}
			if ($m_override = $this->fetchSingle(sprintf("select mpo.* from custom_member_product_options mpo, members_by_folder mbf, order_lines ol where ol.order_id = %d and ol.custom_package = 'S' and ol.deleted = 0 and mbf.member_id = %d and mpo.member_id = mbf.id and mpo.product_id = ol.product_id and mpo.isgroup = 0", $o_id, $member["id"]))) {
				if ($m_override["fuel_exempt"] == 1) {
					$this->logMessage(__FUNCTION__ ,sprintf("override fuel charge to 0 from exemption (member product option override)"),1);
					$fuelExempt = true;
				}
				else {
					$pc = $this->fetchScalarAll(sprintf('select replace(ucase(postalcode)," ","") from addresses where ownertype="order" and ownerid = %d and addresstype in (%d,%d)', $o_id, ADDRESS_PICKUP, ADDRESS_DELIVERY ));
					if ($pc_override = $this->fetchSingle(sprintf('select * from custom_product_by_pc where ((from_postal_code = "%s" and to_postal_code = "%s") or (from_postal_code = "%2$s" and to_postal_code = "%1$s")) and member_product_option_id = %d', $pc[0], $pc[1], $m_override["id"]))) {
						if ($pc_override["fuel_exempt"] == 1) {
							$this->logMessage(__FUNCTION__ ,sprintf("override fuel charge to 0 from exemption (postalcode override)"),1);
							$fuelExempt = true;
						}
					}
				}
			}

			//
			//	Extension to the CONTRACTED_RATE & Fuel Exempt - check for a corresponding entry in custom_product_by_pc based on from/to postal codes
			//
			$from_pc = strtoupper(str_replace(" ","",$this->fetchScalar(sprintf("select postalcode from addresses where ownertype='order' and ownerid = %d and addresstype = %d", $o_id, ADDRESS_PICKUP))));
			$to_pc = strtoupper(str_replace(" ","",$this->fetchScalar(sprintf("select postalcode from addresses where ownertype='order' and ownerid = %d and addresstype = %d", $o_id, ADDRESS_DELIVERY))));
			if (is_array($m_override) && $by_pc = $this->fetchSingle(sprintf('select * from custom_product_by_pc where member_product_option_id = %d and ((from_postal_code = "%s" and to_postal_code = "%s") or (from_postal_code = "%3$s" and to_postal_code = "%2$s"))', $m_override["id"], $from_pc, $to_pc))) {
				$this->logMessage(__FUNCTION__, sprintf("start new member contracted rate logic"),1);
				$fuelExempt = $by_pc["fuel_exempt"];
			}
			else {
				if (is_array($g_override)) {
					if ($by_pc = $this->fetchSingle(sprintf('select * from custom_product_by_pc where member_product_option_id = %d and ((from_postal_code = "%s" and to_postal_code = "%s") or (from_postal_code = "%3$s" and to_postal_code = "%2$s"))', $g_override["id"], $from_pc, $to_pc))) {
						$this->logMessage(__FUNCTION__, sprintf("start new group contracted rate logic"),1);
						$fuelExempt = $by_pc["fuel_exempt"];
					}
				}
			}


			if ($fuelExempt) {
				$recalc = true;
				$this->execute(sprintf("delete from order_lines where order_id = %d and product_id = %d", $o_id, $group["custom_fuel"]));
				$fuelCharge = 0;
			}
			else {
				if (!$this->fetchSingle(sprintf("select * from order_lines where order_id = %d and product_id = %d and deleted = 0", $o_id, $group["custom_fuel"]))) {
					$recalc = true;
					$line_id = $this->fetchScalar(sprintf("select max(line_id) from order_lines where order_id = %d", $o_id));
					$value = $this->fetchScalar(sprintf("select coalesce(sum(value),0) from order_lines ol, product p where ol.order_id = %d and ol.deleted = 0 and ol.product_id != %d and p.id = ol.product_id and p.custom_commission = 1 and p.custom_has_fuel_surcharge = 1", $o_id, $group["custom_fuel"]));

					$fuel = $this->fetchSingle(sprintf("select * from product where id = %d", $group["custom_fuel"]));
					$fuelCharge = max(0,round(($fuel["custom_minimum_charge"] + $group["custom_fuel_override"] + $member["custom_fuel_override"])/100,2));

					$tmp = $this->fetchScalar(sprintf("select custom_fuel_rate from orders where id = %d", $o_id));
					if ($tmp >= .01) $fuelCharge = $tmp;

					if ($fuelCharge >= .01) {
						$fuelLine = array("order_id"=>$o_id, "line_id"=>$line_id+1, "product_id"=>$group["custom_fuel"], "price"=>round($value * $fuelCharge,2), "quantity"=>1, "qty_multiplier"=>1, "value"=>round($value * $fuelCharge,2), 
							"total"=>round($value * $fuelCharge,2), "custom_package"=>"A", "discount_type"=>0, "tax_exemptions"=>"||","recurring_discount_type"=>0, "order_id"=>$o_id,
							"shipping"=>0, "shipping_only"=>0, "discount_rate"=>0, "recurring_shipping_only"=>0, "recurring_discount_rate" => 0 );
						$fuelLine = Ecom::lineValue($fuelLine);
						$this->logMessage(__FUNCTION__, sprintf("fuelLine [%s]", print_r($fuelLine,true)), 1);
						unset($fuelLine["taxdata"]);
						$stmt = $this->prepare(sprintf("insert into order_lines(%s) values(%s?)", implode(", ", array_keys($fuelLine)), str_repeat("?, ", count($fuelLine)-1)));
						$stmt->bindParams(array_merge(array(str_repeat("s",count($fuelLine))),array_values($fuelLine)));
						$stmt->execute();
						$this->logMessage(__FUNCTION__, sprintf("adding a fuel charge"), 1);
					}
				}
				else {
					$fuelCharge = $this->fetchScalar(sprintf("select price from order_lines where order_id = %d and product_id = %d and deleted = 0", $o_id, $group["custom_fuel"]));
				}
			}
		}
		else {
			if ($this->fetchSingle(sprintf("select * from order_lines where order_id = %d and product_id = %d and deleted = 0", $o_id, $group["custom_fuel"]))) {
				$recalc = true;
				$this->execute(sprintf("delete from order_lines where order_id = %d and product_id = %d", $o_id, $group["custom_fuel"]));
				$this->logMessage(__FUNCTION__, sprintf("removing a fuel charge"), 1);
			}
		}
		$this->logMessage(__FUNCTION__, sprintf("fuel charge [%s] recalc [%s]", $fuelCharge, $recalc), 1);
		if ($recalc) {
			Ecom::recalcOrderFromDB($o_id);
		}
		foreach($actions as $key=>$rec) {
			//$this->calcDriverCommission($rec["id"]);
			$this->execute(sprintf("delete from custom_delivery_commissions where delivery_id = %d", $rec["id"]));
			$c_rate = $this->getCommissionRate($rec);
			$recs = $this->fetchAll(sprintf("select o.*, p.custom_driver_split, p.custom_biker_split2, p.is_fedex, p.custom_split_allocation, p.custom_has_fuel_surcharge from order_lines o, product p where o.deleted = 0 and o.order_id = %d and p.id = o.product_id and p.custom_commission = 1 and (o.custom_package in ('S','P') or (o.custom_package = 'A' and p.custom_split_allocation in ('B','%s')))",$o_id, $rec["service_type"]));
			$fuel = 0;
			foreach($recs as $k=>$v) {
				if ($v["custom_package"] != "A") {
					//
					//	took care of fedex already
					//
					$flds = array("delivery_id"=>$rec["id"], "product_id"=>$v["product_id"], "base_amount"=>$v["value"],"calculated"=>round($rec["percent_of_delivery"]/100*$v["value"]*$rec["commission"]/100,2), "line_commission"=>$rec["commission"],"split_rate"=>$rec["percent_of_delivery"]);
					$flds = array("delivery_id"=>$rec["id"], "product_id"=>$v["product_id"], "base_amount"=>$v["value"],"calculated"=>round($rec["percent_of_delivery"]/100*$v["value"]*$c_rate/100,2), "line_commission"=>$c_rate,"split_rate"=>$rec["percent_of_delivery"]);
					$stmt = $this->prepare(sprintf("insert into custom_delivery_commissions(%s) values(?%s)", implode(", ", array_keys($flds)), str_repeat(", ?", count($flds)-1)));
					$stmt->bindParams(array_merge(array(str_repeat("s",count($flds))),array_values($flds)));
					$stmt->execute();
					if ($v["custom_has_fuel_surcharge"]) $fuel += $v["value"];
				}
				else {
					if ($v["custom_split_allocation"] == "B")
						$flds = array("delivery_id"=>$rec["id"], "product_id"=>$v["product_id"], "base_amount"=>$v["value"],"calculated"=>round($rec["percent_of_delivery"]/100*$v["value"]*$c_rate/100,2), "line_commission"=>$c_rate,"split_rate"=>$rec["percent_of_delivery"]);
					else
						$flds = array("delivery_id"=>$rec["id"], "product_id"=>$v["product_id"], "base_amount"=>$v["value"],"calculated"=>round($v["custom_driver_split"]/100*$v["value"]*$c_rate/100,2), "line_commission"=>$c_rate,"split_rate"=>$v["custom_driver_split"]);
					$stmt = $this->prepare(sprintf("insert into custom_delivery_commissions(%s) values(?%s)", implode(", ", array_keys($flds)), str_repeat(", ?", count($flds)-1)));
					$stmt->bindParams(array_merge(array(str_repeat("s",count($flds))),array_values($flds)));
					$stmt->execute();
					if ($v["custom_has_fuel_surcharge"]) $fuel += $v["value"];
				}
			}
			$this->logMessage(__FUNCTION__, sprintf("fuel calculated from [%s]", $fuel), 1);
			//
			//	If the customer is not charged a fuel surcharge, do not give the driver one
			//
			if ($fuelCharge >= 0.01) {
				$driver = $this->fetchSingle(sprintf("select d.*, v.fuel_charge from drivers d, vehicles v where d.id = %d and v.id = d.vehicle_id", $rec["driver_id"]));
				$value = $this->fetchScalar(sprintf("select coalesce(sum(value),0) from order_lines ol, product p where ol.order_id = %d and ol.deleted = 0 and ol.product_id != %d and p.id = ol.product_id and p.custom_commission = 1 and p.custom_has_fuel_surcharge = 1", $o_id, $group["custom_fuel"]));
				$this->execute(sprintf("update orders set custom_commissionable_amt = %f where id = %d", $value, $o_id));
				$recs = $this->fetchAll(sprintf("select o.* from order_lines o where o.order_id = %d and o.product_id = %d and o.deleted = 0",$o_id,$group["custom_fuel"]));
				if ($driver["fuel_charge"] != 0) {
					foreach($recs as $k=>$v) {
						$flds = array("delivery_id"=>$rec["id"], "product_id"=>$v["product_id"], "calculated"=>round($rec["percent_of_delivery"]/100*$value * $driver["fuel_surcharge"]/100,2), "base_amount"=>$value, "line_commission"=>$driver["fuel_surcharge"],"split_rate"=>$rec["percent_of_delivery"]);
						$stmt = $this->prepare(sprintf("insert into custom_delivery_commissions(%s) values(?%s)", implode(", ", array_keys($flds)), str_repeat(", ?", count($flds)-1)));
						$stmt->bindParams(array_merge(array(str_repeat("s",count($flds))),array_values($flds)));
						$stmt->execute();
					}
				}
			}
			else {
				$this->logMessage(__FUNCTION__,sprintf("not giving driver a fuel commission - customer not charged [%s]", $fuelCharge), 1);
			}
			$this->execute(sprintf("update custom_delivery set payment = (select sum(calculated) from custom_delivery_commissions where delivery_id = %d) where id = %d", $rec["id"], $rec["id"]));
		}
	}

	function getCommissionRate( $action ) {
		$hrs = $this->depairOptions($this->getConfigVar("otCommissions"));
		$dt = ($action["actual_date"] == "0000-00-00 00:00:00") ? $action["scheduled_date"] : $action["actual_date"];
		$ot = false;
		$dow = date("w", strtotime($dt));
		$tm = date("H:i", strtotime($dt));
		foreach($hrs as $k=>$v) {
			$parms = explode(",",$v);
			$days = explode("-",$parms[1]);
			$times = explode("-",str_replace(".",":",$parms[0]));
			if ((count($days) > 1 && $days[0] <= $dow && $days[1] >= $dow) || $dow == $days[0]) {
				$this->logMessage(__FUNCTION__,sprintf("have the right day"),1);
				if ($times[0] <= $tm && $times[1] >= $tm) {
					$this->logMessage(__FUNCTION__,sprintf("have the right time"),1);
					$ot = true;
				}
			}
		}
		return $ot && $action["after_hours_commission"] > .01 ? $action["after_hours_commission"] : $action["commission"];
	}

	function calcDriverCommission($d_id) {
		$this->logMessage(__FUNCTION__,sprintf("calc delivery_id %d",$d_id),1);
		//
		//	calculate the drivers commission on a pickup or delivery
		//	main split already done, now apply the driver's % and calc fuel surcharge
		//
		$r = $this->fetchSingle(sprintf("select * from custom_delivery where id = %d",$d_id));
		$d = $this->fetchSingle(sprintf("select * from drivers where id = %d",$r["driver_id"]));
		$o = $this->fetchSingle(sprintf("select * from orders where id = %d",$r["order_id"]));
		$f = $this->fetchSingle(sprintf("select ol.* 
from order_lines ol, product p, members_by_folder mbf, members_folders mf  
where ol.order_id = %d and deleted = 0 and ol.product_id = mf.product_id and mf.id = mbf.folder_id and mf.member_id = %d", 
$r["order_id"], $o["member_id"]));
		$this->logMessage(__FUNCTION__,sprintf("delivery [%s] driver [%s] order [%s]",print_r($r,true),print_r($d,true),print_r($o,true)),1);
		$p = $o["custom_commissionable_amt"] * $r["percent_of_delivery"]/100 * $d["commission"]/100;
		$p += $o["custom_commissionable_amt"] * $d["fuel_surcharge"]/100;
		return $this->execute(sprintf("update custom_delivery set payment = %s where id = %d",round($p,2),$d_id));
	}

	function checkDowntown( $m_id, $quote, $product, $fromZone, $toZone, $pickupDriver, $deliveryDriver ) {
		$this->logMessage(__FUNCTION__,sprintf("quote [%s] product [%s] from [%s] to [%s] pickup [%s] delivery [%s]", print_r($quote,true), print_r($product,true), print_r($fromZone,true), print_r($toZone,true), print_r($pickupDriver,true), print_r($deliveryDriver,true)),3);
		$quote["downtownCharge"] = 0;
		//
		//	new logic - check member custom_downtown_surcharge first
		//	if applicable - have we assigned a car driver for either pickup or delivery
		//
		//if (!(is_array($pickupDriver) && is_array($deliveryDriver))) {
		//	$this->logMessage(__FUNCTION__,sprintf("neither a pickup or delivery driver"),1);
		//	return $quote;
		//}
		if (is_array($fromZone) && is_array($toZone) && array_key_exists("downtown", $fromZone) && $fromZone["downtown"] == 1 && array_key_exists("downtown", $toZone) && $toZone["downtown"] == 1) {
			$this->logMessage(__FUNCTION__,sprintf("downtown - downtown - no surcharge"),1);
		}
		elseif (is_array($fromZone) && is_array($toZone) && array_key_exists("downtown", $fromZone) && $fromZone["downtown"] == 0 && array_key_exists("downtown", $toZone) && $toZone["downtown"] == 0) {
			$this->logMessage(__FUNCTION__,sprintf("neither p/u or del is downtown"),1);
		}
		else {
			//
			//	either p/u or del is out of zone
			//
			if (is_array($fromZone) && $fromZone["downtown"] == 1 || is_array($toZone) && $toZone["downtown"] == 1) {
				if ($this->fetchScalar(sprintf("select custom_downtown_surcharge from members where id = %d", $m_id)) == 0) {
					$this->logMessage(__FUNCTION__,sprintf("no surcharge for this member"),1);
				}
				else {
					if (!$grp_override = $this->fetchScalar(sprintf("select downtown_surcharge from custom_member_product_options cpo, members_by_folder mf where mf.member_id = %d and cpo.member_id = mf.folder_id and cpo.isgroup = 1 and cpo.product_id = %d", $m_id, $product["id"]))) $grp_override = 0;
					if (!$mbr_override = $this->fetchScalar(sprintf("select downtown_surcharge from custom_member_product_options cpo, members_by_folder mbf where mbf.member_id = %d and cpo.member_id = mbf.id and cpo.isgroup = 0 and cpo.product_id = %d", $m_id, $product["id"]))) $mbr_override = 0;
					$rate = round($product["custom_downtown_surcharge"] * (1+($grp_override + $mbr_override)/100),2);
					$quote["downtownCharge"] = $rate;
					$quote["downtownChargeType"] = is_array($fromZone) && array_key_exists("downtown",$fromZone) && $fromZone["downtown"] ? DOWNTOWN_PU_ID : DOWNTOWN_DEL_ID;
					$this->logMessage(__FUNCTION__,sprintf("returning [%s]", print_r($quote,true)),4);
				}
			}
		}
		return $quote;
	}

	function recalcDowntown($o_id) {
		$order = $this->fetchSingle(sprintf("select * from orders where id = %d", $o_id));
		$customer = $this->fetchSingle(sprintf("select * from members where id = %d", $order["member_id"]));
		if ($customer["custom_downtown_surcharge"] == 0) {
			if ($car = $this->fetchSingle(sprintf("select * from order_lines where order_id = %d and product_id in(%d,%d) and deleted = 0", $o_id, DOWNTOWN_PU_ID, DOWNTOWN_DEL_ID))) {
				$this->execute(sprintf("delete from order_lines where order_id = %d and id = %d", $o_id, $car["id"]));
				$this->initRecalc($o_id);
			}
			return;
		}
		$addresses = $this->fetchAll(sprintf("select a.*, l.extra from addresses a left join code_lookups l on a.addresstype = l.id where ownertype='order' and ownerid = %d",$o_id));
		foreach($addresses as $k=>$v) {
			switch($v["addresstype"]) {
			case ADDRESS_PICKUP:
				$fromZone = $this->fetchSingle(sprintf("select zf.*, f.downtown from zone_fsa zf, fsa f where f.fsa='%s' and zf.fsa_id = f.id",strtoupper(substr($v["postalcode"],0,3))));
				break;
			case ADDRESS_DELIVERY:
				$toZone = $this->fetchSingle(sprintf("select zf.*, f.downtown from zone_fsa zf, fsa f where f.fsa='%s' and zf.fsa_id = f.id",strtoupper(substr($v["postalcode"],0,3))));
				break;
			default:
				break;
			}
		}
		$pu_driver = $this->fetchSingle(sprintf("select d.*, v.name as vehicleType from drivers d, custom_delivery cd, vehicles v where cd.order_id = %d and service_type='P' and d.id = cd.driver_id and v.id = d.vehicle_id", $o_id));
		$del_driver = $this->fetchSingle(sprintf("select d.*, v.name as vehicleType from drivers d, custom_delivery cd, vehicles v where cd.order_id = %d and service_type='D' and d.id = cd.driver_id and v.id = d.vehicle_id", $o_id));
		$product = $this->fetchSingle(sprintf("select p.* from product p, order_lines o where order_id = %d and custom_package='S' and p.id = o.product_id and o.deleted = 0", $o_id));
		$quote = $this->checkDowntown($order["member_id"], array("downtownCharge"=>0,"downtownChargeType"=>0), $product, $fromZone, $toZone, $pu_driver, $del_driver);
		$c = $this->fetchSingle(sprintf("select * from order_lines where order_id = %d and product_id = %d and deleted = 0", $o_id, $quote["downtownChargeType"]));
		$this->beginTransaction();
		if ($quote["downtownCharge"] < .01) {
			//
			//	We aren't charging it anymore - delete & recalc if was being charged
			//
			if (is_array($c)) {
				$this->execute(sprintf("delete from order_lines where order_id = %d and id = %d", $o_id, $c["id"]));
				$this->execute(sprintf("delete from order_taxes where order_id = %d and line_id = %d", $o_id, $c["line_id"]));
				$this->initRecalc($o_id);
			}
		}
		else {
			if (is_array($c) && $c["value"] != $quote["downtownCharge"]) {
				$this->logMessage(__FUNCTION__,sprintf("rate has changed from [%s] to [%s]", $c["value"], $quote["downtownCharge"]),1);
				$this->execute(sprintf("update order_lines set price=%f, value=%f where order_id = %d and id = %d", $quote["downtownCharge"], $quote["downtownCharge"], $o_id, $c["id"]));
				$this->initRecalc($o_id);
			}
			if (!($c = $this->fetchSingle(sprintf("select * from order_lines where order_id = %d and product_id = %d and deleted = 0", $o_id, $quote["downtownChargeType"])))) {
				$e = new Ecom();
				$tmp = $e->initCart();
				$tmp["header"]["id"] = $o_id;

				if (!$address = $this->fetchSingle(sprintf('select * from addresses where ownerid = %d and ownertype = "order" and addresstype=12',$o_id)))
					$address = array('id'=>0,'province_id'=>0,'country_id'=>0);
				$tmp['addresses']['shipping'] = $address;

				$this->logMessage(__FUNCTION__,sprintf("adding a downtown surcharge"),1);

				$key = sprintf("%d|0|0|0", $quote["downtownChargeType"] );
				//$tmp = $e->updateCart($tmp, array("addToCart"=>1,"product_id"=>$quote["downtownChargeType"],"color"=>0,"size"=>0,"options_id"=>0,"product_quantity"=>1));
				$tmp = $e->updateLine($tmp, $key, $quote["downtownChargeType"], 0, 0, 0, 1, 0, 0, '');
				$tmp["products"][$key]["price"] = $product["custom_downtown_surcharge"];
				$tmp["products"][$key]["value"] = $tmp["products"][$key]["price"];
				$tmp["products"][$key]["regularPrice"] = $tmp["products"][$key]["price"];
				$tmp["products"][$key]["shipping"] = 0;
				$tmp["products"][$key]["total"] = $tmp["products"][$key]["value"];
				$tmp["products"][$key]["taxes"] = 0;
				$this->logMessage(__FUNCTION__, sprintf("updated cart is [%s]", print_r($tmp,true)), 1);
				$tmp = $e->recalcOrder($tmp);
				$this->logMessage(__FUNCTION__, sprintf("updated cart is [%s]", print_r($tmp,true)), 1);

				$line = array("product_id"=>$quote["downtownChargeType"],"quantity"=>1,"price"=>$tmp["header"]["value"],
					"order_id"=>$o_id, "line_id"=>$this->fetchScalar(sprintf("select max(line_id) from order_lines where order_id = %d", $o_id))+1,
					"value"=>$tmp["header"]["value"], "custom_package"=>"A", "total"=>$tmp["header"]["value"],
					"taxes"=>$tmp["header"]["taxes"]);
				$stmt = $this->prepare(sprintf("insert into order_lines(%s) values(?%s)", implode(", ",array_keys($line)), str_repeat(", ?", count($line)-1)));
				$stmt->bindParams(array_merge(array(str_repeat("s", count($line))), array_values($line)));
				$stmt->execute();
				foreach($tmp["taxes"] as $k=>$v) {
					$t_line = array(
						"order_id"=>$o_id, "line_id"=>$line["line_id"], "tax_id"=>$k, "tax_amount"=>$v["tax_amount"], "taxable_amount"=>$v["taxable_amount"]
					);
					$stmt = $this->prepare(sprintf("insert into order_taxes(%s) values(?%s)", implode(", ",array_keys($t_line)), str_repeat(", ?", count($t_line)-1)));
					$stmt->bindParams(array_merge(array(str_repeat("s", count($t_line))), array_values($t_line)));
					$stmt->execute();
				}
				$this->initRecalc($o_id);
			}
		}
		$this->commitTransaction();
	}

	function initRecalc($o_id) {
		Ecom::recalcOrderFromDB($o_id);
		return;
		$tmp = Ecom::initCart();
		$products = $this->fetchAll(sprintf('select p.*, options_id, color, size, l.price, l.quantity, l.value, l.shipping, l.discount_value, l.recurring_discount_value, l.total, l.line_id, l.taxes, l.product_id from product p, order_lines l where l.order_id = %d and l.deleted = 0 and p.id = l.product_id',$o_id));
		foreach($products as $k=>$v) {
			$key = sprintf("%d|%d|%d|%d", $v["id"], $v["options_id"], $v["color"], $v["size"]);
			$tmp["products"][$key] = $v;
			$tmp["products"][$key]["taxdata"] = $this->fetchAll(sprintf("select * from order_taxes where order_id = %d and line_id = %d", $o_id, $v["line_id"]));
		}
$this->logMessage(__FUNCTION__, sprintf("*** initRecalc tax check [%s]", print_r($tmp,true)), 1);
		$tmp['taxes'] = $this->fetchAll(sprintf('select * from order_taxes ot where ot.order_id = %d and ot.line_id in (select o.line_id from order_lines o where o.order_id = ot.order_id and o.deleted = 0)',$o_id));
		if (!$address = $this->fetchSingle(sprintf('select * from addresses where ownerid = %d and ownertype = "order" and tax_address=1',$o_id)))
			$address = array('id'=>0,'province_id'=>0,'country_id'=>0);
		$tmp['addresses']['shipping'] = $address;
//		if (!$address = $this->fetchSingle(sprintf('select * from addresses where ownerid = %d and ownertype = "order" and tax_address=0',$o_id)))
//			$address = array('id'=>0,'province_id'=>0,'country_id'=>0);
//		$tmp['addresses']['billing'] = $address;
		$tmp['header'] = $this->fetchSingle(sprintf("select * from orders where id = %d", $o_id));
		$tmp = Ecom::recalcOrder($tmp);
		$this->logMessage(__FUNCTION__,sprintf("updated cart is [%s]", print_r($tmp,true)),1);
		$hdr = array("value"=>$tmp["header"]["value"], "net"=>$tmp["header"]["net"], "taxes"=>$tmp["header"]["taxes"], 
			"total"=>$tmp["header"]["total"], "custom_commissionable_amt"=>$tmp["header"]["custom_commissionable_amt"]);
		$stmt = $this->prepare(sprintf("update orders set %s=? where id = %d", implode("=?, ", array_keys($hdr)), $o_id ));
		$stmt->bindParams(array_merge(array(str_repeat("s", count($hdr))), array_values($hdr)));
		$stmt->execute();
	}

	function sendText($d_id, $form, $fields) {
		$this->logMessage(__FUNCTION__,sprintf("d_id [%s] form [%s] fields [%s]", $d_id, print_r($form,true), print_r($fields,true)), 1);
		if (!$delivery = $this->fetchSingle(sprintf("select cd.*, d.member_id, m.firstname, m.lastname from custom_delivery cd, drivers d, members m where cd.id = %d and d.id = cd.driver_id and m.id = d.member_id", $d_id))) {
			$this->logMessage(__FUNCTION__,sprintf("no assigned driver"),1);
			return;
		}
		$dt = strtotime($delivery["scheduled_date"]);
		if ((date("Y-m-d",$dt) > date("Y-m-d")) || $delivery["completed"]) {
			$this->logMessage(__FUNCTION__,sprintf("future dated [%s]", $delivery["scheduled_date"]),1);
			return;
		}
		if ($address = $this->fetchSingle(sprintf("select * from addresses where ownertype='member' and ownerid = %d and phone1 != '' and deleted = 0 and addresstype = %d", $delivery["member_id"], ADDRESS_COMPANY))) {

			$body = new Forms();
			$body->init($form);
			$flds = $body->buildForm($fields);
			$delivery["address"] = Address::formatData($this->fetchSingle(sprintf("select * from addresses where ownertype='order' and ownerid = %s and addresstype=%d",
				$delivery["order_id"], $delivery["service_type"] == "P" ? ADDRESS_PICKUP :ADDRESS_DELIVERY )));
			$body->addData($delivery);

			$sid = $GLOBALS['twilio']['account'];
			$token = $GLOBALS['twilio']['token'];
			try {
				$client = new Client($sid, $token);
				$client->messages->create(
					sprintf('+1%s', $address["phone1"]),
					array(
						'from' => $GLOBALS['twilio']['sender'][random_int(0,count($GLOBALS['twilio']['sender']) - 1)],
						'body' => $body->show()
					)
				);
			}
			catch(Twilio\Exceptions\RestException $err) {
				$this->logMessage(__FUNCTION__,sprintf("twilio exception [%s]", print_r($err,true)), 1, true);
				$this->addError(sprintf("Twilio error occurred [%s]", $err->getMessage()));
			}
		}
	}

	function sendAlert($d_id, $form, $fields) {
		$this->logMessage(__FUNCTION__,sprintf("d_id [%s] form [%s] fields [%s]", $d_id, print_r($form,true), print_r($fields,true)), 1);
		if (!$delivery = $this->fetchSingle(sprintf("select cd.* from custom_delivery cd where cd.id = %d", $d_id))) {
			$this->logMessage(__FUNCTION__,sprintf("no assigned driver"),1);
			return;
		}
		$body = new Forms();
		$body->init($form);
		$flds = $body->buildForm($fields);
		$delivery["address"] = Address::formatData($this->fetchSingle(sprintf("select * from addresses where ownertype='order' and ownerid = %s and addresstype=%d",
			$delivery["order_id"], $delivery["service_type"] == "P" ? ADDRESS_PICKUP :ADDRESS_DELIVERY )));
		$body->addData($delivery);
		$sid = $GLOBALS['twilio']['account'];
		$token = $GLOBALS['twilio']['token'];
		$alertTo = $this->depairOptions($this->getConfigVar('afterhours_list'));
		$this->logMessage(__FUNCTION__,sprintf("alertTo [%s]", print_r($alertTo,true)),1);
		try {
			foreach($alertTo as $k=>$v) {
				$client = new Client($sid, $token);
				$client->messages->create(
					sprintf('+1%s', $v),
					array(
						'from' => $GLOBALS['twilio']['sender'][random_int(0,count($GLOBALS['twilio']['sender']) - 1)],
						'body' => $body->show()
					)
				);
			}
		}
		catch(Twilio\Exceptions\RestException $err) {
			$this->logMessage(__FUNCTION__,sprintf("twilio exception [%s]", print_r($err,true)), 1, true);
			$this->addError(sprintf("Twilio error occurred [%s]", $err->getMessage()));
		}
	}

	function fedExRates($cart,$quote) {

		$this->logMessage(__FUNCTION__,sprintf("cart [%s] quote [%s]", print_r($cart,true), print_r($quote,true)),2);

		require_once(ADMIN."classes/fed-ex/library/fedex-common.php5");
		$cart["custom"]["rates"] = array();
		$cart["custom"]["minRate"] = 0;
		$cart["custom"]["ourRates"] = array();
		//
		//	local pickup - pickup address is kjv
		//	out-of-zone pickup - no kjv intervention - pickup & delivery by fedex
		//

		$allowedZones = $this->fetchScalarAll(sprintf("select zone_id from zones_by_folder z, members m where m.id = %d and z.folder_id = m.custom_zones", $this->getUserInfo("id",true)));

		$fromZone = $this->fetchSingle(sprintf("select zf.*, f.downtown from zone_fsa zf, fsa f where f.fsa='%s' and zf.fsa_id = f.id and zone_id in (%s)",
				strtoupper(substr($cart["addresses"]["pickup"]["postalcode"],0,3)), count($allowedZones) > 0 ? implode(", ", $allowedZones) : 0));
		$toZone = $this->fetchSingle(sprintf("select zf.*, f.downtown from zone_fsa zf, fsa f where f.fsa='%s' and zf.fsa_id = f.id and zone_id in (%s)",
				strtoupper(substr($cart["addresses"]["shipping"]["postalcode"],0,3)), count($allowedZones) > 0 ? implode(", ", $allowedZones) : 0));

		$this->logMessage(__FUNCTION__,sprintf("fromZone [%s] toZone [%s]", print_r($fromZone,true), print_r($toZone,true)),3);
		ini_set("soap.wsdl_cache_enabled", "0");
		$client = new SoapClient(ADMIN."classes/fed-ex/wsdl/RateService_v14.wsdl", array('trace' => 1)); // Refer to http://us3.php.net/manual/en/ref.soap.php for more information
		$fedex = $GLOBALS['shipping']['fedex'];
		$request['WebAuthenticationDetail'] = array(
			'UserCredential' => array(
				'Key' => $fedex['key'], 
				'Password' => $fedex['password']
			)
		); 
		$request['ClientDetail'] = array(
			'AccountNumber' => $fedex['shipaccount'], 
			'MeterNumber' => $fedex['meter']
		);
		$request['TransactionDetail'] = array('CustomerTransactionId' => ' *** Rate Available Services Request v14 using PHP ***');
		$request['Version'] = array(
			'ServiceId' => 'crs', 
			'Major' => '14', 
			'Intermediate' => '0', 
			'Minor' => '0'
		);
/*
		if ($quote["custom_declared_value"]> 0) {
			$this->logMessage(__FUNCTION__,sprintf("adding declared value [%s]", $quote["custom_declared_value"]),1);
			//$request['RequestedShipment']['FreightShipmentDetail']['DeclaredValuePerUnit'] = $quote["custom_declared_value"];
			//$request['RequestedShipment']['FreightShipmentDetail']['DeclaredValueUnits'] = 1;
			//$request["InsuredValue"]["Amount"] = $quote["custom_declared_value"];
			$request['RequestedShipment']['TotalInsuredValue']['Amount'] = $quote["custom_declared_value"];
			$request['RequestedShipment']['PackageCount'] = 1;
			//$request['RequestedShipment']['ShipmentOnlyFields'] = array('INSURED_VALUE');
		}
*/
		$request['RequestedShipment']['PreferredCurrency'] = 'CAD';
		$request['RequestedShipment']['RateRequestType'] = 'PREFERRED';
		$request['ReturnTransitAndCommit'] = true;
		$request['RequestedShipment']['DropoffType'] = 'REGULAR_PICKUP'; // valid values REGULAR_PICKUP, REQUEST_COURIER, ...
		if ($this->checkArray("quote:pickup_datetime",$cart)) {
			$xxx = explode("/", $cart["quote"]["pickup_datetime"]);
			$dt1 = strtotime(sprintf("%s-%s-%s %02d:%02d %s", $xxx[2], $xxx[0], $xxx[1], $cart["quote"]["pickup_datetime_hh"], $cart["quote"]["pickup_datetime_mm"], $cart["quote"]["pickup_datetime_ampm"]));
		}
		else
			if ($this->checkArray("header:pickup_datetime",$cart))
				$dt1 = strtotime($cart["header"]["pickup_datetime"]);
			else $dt1 = date(DATE_ATOM);
		$dt2 = strtotime(date("c"));
		$dt = max($dt1,$dt2);
		$request['RequestedShipment']['ShipTimestamp'] = date("c",$dt);	//date('c');
		$shipper = $this->getConfigVar('mailing-address','config');
		if (is_array($fromZone)) {
			$request['RequestedShipment']['Shipper'] = array(
				'Address'=> array(
					'StreetLines' => array($shipper['line1'],$shipper['line2']),
					'City' => $shipper['city'],
					'StateOrProvinceCode' => $shipper['provinceCode'],
					'PostalCode' => $shipper['postalcode'],
					'CountryCode' => $shipper['countryCode'],
					'Residential' => 0
				)
			);
			$request['RequestedShipment']['Recipient'] = array(
				'Address'=> array(
					'StreetLines' => array($cart['addresses']['shipping']['line1'],$cart['addresses']['shipping']['line2']),
					'City' => $cart['addresses']['shipping']['city'],
					'PostalCode' => $cart['addresses']['shipping']['postalcode'],
					'CountryCode' => $this->fetchScalar(sprintf('select country_code from countries where id = %d',$cart['addresses']['shipping']['country_id'])),
					'Residential' => $cart['addresses']['shipping']['residential']
				)
			);
			if ($province = $this->fetchScalar(sprintf('select province_code from provinces where id=%d',$cart['addresses']['shipping']['province_id'])))
				$request['RequestedShipment']['Recipient']['Address']['StateOrProvinceCode'] = $province;
		}
		else {
			$pu = Address::formatData($cart["addresses"]["pickup"]);
			$st = Address::formatData($cart["addresses"]["shipping"]);
			$request['RequestedShipment']['Shipper'] = array(
				'Address'=> array(
					'StreetLines' => array($pu['line1'],$pu['line2']),
					'City' => $pu['city'],
					'StateOrProvinceCode' => $pu['provinceCode'],
					'PostalCode' => $pu['postalcode'],
					'CountryCode' => $pu['countryCode'],
					'Residential' => $st['residential']
				)
			);
			$request['RequestedShipment']['Recipient'] = array(
				'Address'=> array(
					'StreetLines' => array($st['line1'],$st['line2']),
					'City' => $st['city'],
					'StateOrProvinceCode' => $st['provinceCode'],
					'PostalCode' => $st['postalcode'],
					'CountryCode' => $st['countryCode'],
					'Residential' => $st['residential']
				)
			);
		}
		$request['RequestedShipment']['ShippingChargesPayment'] = array(
			'PaymentType' => 'SENDER',
		   	'Payor' => array(
				'ResponsibleParty' => array(
					'AccountNumber' => $fedex['billaccount'],
					'Currency'=>'CAD',
					'Contact' => null,
					'Address' => array(
						'CountryCode' => 'CA'
					)
				)
			)
		);
		$request['RequestedShipment']['RateRequestTypes'] = 'ACCOUNT'; 
		$request['RequestedShipment']['RateRequestTypes'] = 'LIST'; 
		$request['RequestedShipment']['PackageCount'] = 0;
		$request['RequestedShipment']['PackagingType'] = "YOUR_PACKAGING";
		$request['RequestedShipment']['RequestedPackageLineItems'] = array();
		$seq = 0;
		foreach($quote["prod"] as $k=>$p) {
			if ($xxx = $this->fetchSingle(sprintf("select * from product where id = %d", $p["product_id"])))
				$request['RequestedShipment']['PackagingType'] = $xxx["fedex_package_type"];
			else
				$request['RequestedShipment']['PackagingType'] = "YOUR_PACKAGING";
			foreach($p["dimensions"] as $sk=>$d) {
				$request['RequestedShipment']['PackageCount'] += $d["quantity"];
				$request['RequestedShipment']['RequestedPackageLineItems'][$seq] = array(
					'SequenceNumber' => $seq+1,
					'GroupPackageCount' => $d["quantity"],
					'Weight' => array(
						'Value' => max(round($d['weight'],0),.1),
						'Units' => $quote["wt"]["code"]	//'KG'
					)
				);
				if ($quote["custom_declared_value"] > 0) {
					$request['RequestedShipment']['RequestedPackageLineItems'][$seq]['InsuredValue'] = array("Currency"=>"CAD","Amount"=>$quote["custom_declared_value"]);
				}
				if (array_key_exists("custom_signature_required", $quote) && $quote["custom_signature_required"] == 1) {
					$request['RequestedShipment']['RequestedPackageLineItems'][$seq]['SpecialServicesRequested'] = array("SpecialServiceTypes"=>"SIGNATURE_OPTION",
						"SignatureOptionDetail"=>array("OptionType"=>"DIRECT")
					);
				}
				if ($request['RequestedShipment']['PackagingType'] == "YOUR_PACKAGING") {
					$wtTmp = array(max(round($d['depth'],0),1), max(round($d['width']),1), max(round($d['height']),1));
					sort($wtTmp);
					$request['RequestedShipment']['RequestedPackageLineItems'][$seq]['Dimensions'] = array(
						'Length' =>$wtTmp[2],
						'Width' =>$wtTmp[1],
						'Height' =>$wtTmp[0],
						'Units' => $quote["sz"]["code"]	//'CM'
					);
				}
				$seq += 1;
			}
		}
		$this->logMessage(__FUNCTION__,sprintf('request is [%s]',print_r($request,true)),3);
		try {
			if(setEndpoint('changeEndpoint')){
				$newLocation = $client->__setLocation(setEndpoint('endpoint'));
			}
			$response = $client->getRates($request);
			$this->logMessage(__FUNCTION__,sprintf('response [%s]',print_r($response,true)),3);
			$cart['custom']['rates'] = array();
			if (!($response->HighestSeverity == 'FAILURE' || $response->HighestSeverity == 'ERROR')){
				$minRate = 99999;
				if (property_exists($response,'RateReplyDetails')) {
					if (is_array($response->RateReplyDetails)) {
						foreach($response->RateReplyDetails as $subkey=>$tmp) {
							foreach($tmp->RatedShipmentDetails as $x1=>$rate) {
								list($cart,$minRate) = $this->checkFedexRate($tmp, $rate, $cart, $minRate);
							}
						}
					} else {
						foreach($response->RateReplyDetails->RatedShipmentDetails as $x1=>$rate) {
							list($cart,$minRate) = $this->checkFedexRate($response->RateReplyDetails, $rate, $cart, $minRate);
						}
					}
					if (!(array_key_exists('minRate',$cart['custom'])) || $minRate < $cart['custom']['minRate'])
						$cart['custom']['minRate'] = $minRate;
				}
				if ($response->HighestSeverity == 'WARNING') {
					$this->logMessage(__FUNCTION__,sprintf('fed-ex shipping warning [%s] response [%s] cart [%s]',
						print_r($request,true),print_r($response,true),print_r($cart,true)),1);
					if (is_array($response->Notifications)) {
						$tmp = array();
						foreach($response->Notifications as $xxx=>$yyy) {
							$tmp[] = $yyy->Message;
						}
						$this->addEcomError(sprintf("Rating Service: %s",implode("<br/>",$tmp)));
					}
					else
						$this->addEcomError(sprintf("Rating Service: %s",$response->Notifications->Message));
				}
				if (array_key_exists("custom",$cart) && array_key_exists("rates",$cart["custom"]) && count($cart["custom"]["rates"]) > 0) {
					foreach($cart["custom"]["rates"] as $k=>$r) {
						if ($c = $this->fetchSingle(sprintf("select * from code_lookups where type='ship_via' and code = '%s'", $k))) {
							$e = explode("|",$c["extra"]);
							if ($e[1] > 0) {
								$p = $this->fetchSingle(sprintf("select p.* from product p, product_by_folder pf, custom_member_product_options cpo, members_by_folder mf where p.deleted = 0 and p.enabled = 1 and p.id = %d and pf.product_id = p.id and pf.folder_id = %d and cpo.product_id = p.id and mf.member_id = %d and cpo.isgroup = 1 and cpo.member_id = mf.folder_id",
										$e[1], FEDEX_PRODUCTS, $this->getUserInfo("id",TRUE))); 
								$s = $this->fetchSingle(sprintf("select cpo.* from product p, product_by_folder pf, custom_member_product_options cpo, members_by_folder mf where p.deleted = 0 and p.enabled = 1 and p.id = %d and pf.product_id = p.id and pf.folder_id = %d and cpo.product_id = p.id and cpo.member_id = mf.id and mf.member_id = %d and cpo.isgroup = 0",
										$e[1], FEDEX_PRODUCTS, $this->getUserInfo("id",TRUE)));
								$g = $this->fetchSingle(sprintf("select cpo.* from product p, product_by_folder pf, custom_member_product_options cpo, members_by_folder mf where p.deleted = 0 and p.enabled = 1 and p.id = %d and pf.product_id = p.id and pf.folder_id = %d and cpo.product_id = p.id and mf.member_id = %d and cpo.isgroup = 1 and cpo.member_id = mf.folder_id",
										$e[1], FEDEX_PRODUCTS, $this->getUserInfo("id",TRUE))); 
								if (is_array($p)) {
									$rate = round($r["net"]*(1+$p["custom_fedex"]/100),2);
									$o_rate = $rate;
									$inflator = 0;
									if (is_array($g))
										$inflator = $g["fedex"];
									if (is_array($s))
										$inflator += $s["fedex"];
									$rate = round($rate*(1+$inflator/100),2);
									$override = is_array($s) && $s["minimum_charge"] > .01 ? $s["minimum_charge"] : $g["minimum_charge"];
									$r_min_charge = false;
									if ($rate < $override && $override > .01) {
										$this->logMessage(__FUNCTION__,sprintf("override fedex minimum from %s to %s", $rate, $override),2);
										$rate = $override;
										$r_min_charge = true;
									}
									$this->logMessage(__FUNCTION__,sprintf("rate [%s] inflator [%s] r [%s] p [%s] g [%s] s [%s]", $rate, $inflator, print_r($r,true), print_r($p,true), print_r($g,true), print_r($s,true)), 2);
									if (is_array($g)) {
										$quote["services"][$g["id"]] = array("name"=>sprintf("%s",$p["name"]),"surcharges"=>$r["surcharges"],"rate"=>$rate,"product_id"=>$p["id"],"group_id"=> $g["id"],"member_id"=> is_array($s) ? $s["id"] : 0,"minimum_charge"=>$r_min_charge, "expectedDelivery"=>$r["expectedDelivery"]);
										$quote["rates"][$g["id"]] = $rate;
									}
								}
							}
						}
						else {
							$this->logMessage(__FUNCTION__,sprintf("unknown fedex code [%s] from cart [%s] quote [%s]",$k,print_r($cart,true),print_r($quote,true)),1);
						}
					}
					if (array_key_exists("services",$quote) && is_array($quote["services"])) {
						usort($quote["services"],"fedexRates");
						$this->logMessage(__FUNCTION__,sprintf("sorted array [%s]",print_r($quote["services"],true)),3);
						$cart["custom"]["ourRates"] = $quote["services"];
					}
					else {
						$this->addEcomError(sprintf("Rating Service: There are no services available"));
						$cart["custom"]["ourRates"] = array();
					}
				}
			}
			else {
				$this->logMessage(__FUNCTION__,sprintf('fed-ex shipping failed request [%s] response [%s] cart [%s]',
						print_r($request,true),print_r($response,true),print_r($cart,true)),1,true);
				if (is_array($response->Notifications)) {
					$tmp = array();
					foreach($response->Notifications as $xxx=>$yyy) {
						$tmp[] = $yyy->Message;
					}
					$this->addEcomError(sprintf("Rating Service: %s",implode("<br/>",$tmp)));
				}
				else
					$this->addEcomError(sprintf("Rating Service: %s",$response->Notifications->Message));
			}
		} catch (SoapFault $exception) {
			$this->logMessage(__FUNCTION__,sprintf('fed-ex shipping failed request [%s] exception [%s] cart [%s]',
					print_r($request,true),print_r($exception,true),print_r($cart,true)),1,true);
			$this->addEcomError(sprintf("Oops... an error occurred. The Web Master has been notified."));
		}
		return $cart;
	}

	function checkFedexRate($tmp, $rate, $cart, $minRate) {
		$this->logMessage(__FUNCTION__,sprintf("tmp [%s] rate [%s]", print_r($tmp,true), print_r($rate,true)),3);
//		if ($rate->ShipmentRateDetail->RateType == "PAYOR_ACCOUNT_SHIPMENT" || $rate->ShipmentRateDetail->RateType == "PAYOR_ACCOUNT_PACKAGE") {
		if ($rate->ShipmentRateDetail->RateType == "PAYOR_LIST_SHIPMENT" || $rate->ShipmentRateDetail->RateType == "PAYOR_LIST_PACKAGE") {
//			if ($minRate > $rate->ShipmentRateDetail->TotalNetFedExCharge->Amount)
//				$minRate = $rate->ShipmentRateDetail->TotalNetFedExCharge->Amount;
			if ($minRate > $rate->ShipmentRateDetail->TotalBaseCharge->Amount)
				$minRate = $rate->ShipmentRateDetail->TotalBaseCharge->Amount;
			$cart['custom']['rates'][$tmp->ServiceType] = array(
				'code'=>$tmp->ServiceType,
//				'net'=>$rate->ShipmentRateDetail->TotalNetFedExCharge->Amount,
				'net'=>$rate->ShipmentRateDetail->TotalBaseCharge->Amount,
				'taxes'=>array(),
				'carrier'=>'FEDEX',
				'expectedDelivery'=>property_exists($tmp,"DeliveryTimestamp") ? $tmp->DeliveryTimestamp : ""
			);
			if (property_exists($rate->ShipmentRateDetail,"Taxes")) {
				if (is_array($rate->ShipmentRateDetail->Taxes)) {
					foreach($rate->ShipmentRateDetail->Taxes as $xkey=>$tax) {
						$cart['custom']['rates'][$tmp->ServiceType]['taxes'][$tax->TaxType] = $tax->Amount->Amount;
					}
				}
				else {
					$cart['custom']['rates'][$tmp->ServiceType]['taxes'][$rate->ShipmentRateDetail->Taxes->TaxType] = 
									$rate->ShipmentRateDetail->Taxes->Amount->Amount;
				}
			}
			$cart['custom']['rates'][$tmp->ServiceType]["surcharges"] = array();
			if (property_exists($rate->ShipmentRateDetail,"Surcharges")) {
				$this->logMessage(__FUNCTION__,sprintf("surcharge parsing [%s]", print_r($rate->ShipmentRateDetail->Surcharges,true)),2);
				if (is_array($rate->ShipmentRateDetail->Surcharges)) {
					foreach($rate->ShipmentRateDetail->Surcharges as $k=>$v) {
						$cart = $this->parseFedexSurcharge($v,$tmp,$cart);
					}
				}
				else {
					$cart = $this->parseFedexSurcharge($rate->ShipmentRateDetail->Surcharges,$tmp,$cart);
				}
				$this->logMessage(__FUNCTION__,sprintf("cart after surcharges [%s]", print_r($cart,true)),2);
			}
		}
		return array($cart,$minRate);
	}

	function parseFedexSurcharge($v,$tmp,$cart) {
		if ($sc = $this->fetchSingle(sprintf("select * from code_lookups where type='ship_via' and code = '%s'", $v->SurchargeType))) {
			$this->logMessage(__FUNCTION__, sprintf("found a surcharge [%s] from [%s]", print_r($sc,true), print_r($v,true)), 2);
			$cd = explode("|",$sc["extra"]);
			if ($surcharge = $this->fetchSingle(sprintf("select * from product where id = %d and deleted = 0 and published = 1 and enabled = 1", $cd[1]))) {
				//
				//	1. No override - accept price as is
				//	2. Group override
				//	3. Company override
				//

				$s = $this->fetchSingle(sprintf("select cpo.* from product p, product_by_folder pf, custom_member_product_options cpo, members_by_folder mf where p.deleted = 0 and p.enabled = 1 and p.id = %d and pf.product_id = p.id and pf.folder_id = %d and cpo.product_id = p.id and cpo.member_id = mf.id and mf.member_id = %d and cpo.isgroup = 0",
						$surcharge["id"], FEDEX_PRODUCTS, $this->getUserInfo("id",TRUE)));
				$g = $this->fetchSingle(sprintf("select cpo.* from product p, product_by_folder pf, custom_member_product_options cpo, members_by_folder mf where p.deleted = 0 and p.enabled = 1 and p.id = %d and pf.product_id = p.id and pf.folder_id = %d and cpo.product_id = p.id and mf.member_id = %d and cpo.isgroup = 1 and cpo.member_id = mf.folder_id",
						$surcharge["id"], FEDEX_PRODUCTS, $this->getUserInfo("id",TRUE)));
				$newCharge = 0;
				$newPct = 0;
				$newMin = 0;
				if (is_array($g)) {
					$newPct = round(1+$g["fedex"]/100,2);
					$newMin = $g["minimum_charge"];
				}
				if (is_array($s)) {
					$newPct += round($s["fedex"]/100,2);
					$newMin += $s["minimum_charge"];
				}
				if (!(is_array($s) || is_array($g))) {
					if ($surcharge["custom_minimum_charge"] > 0.01) {
						$newMin = $surcharge["custom_minimum_charge"];
					}
				}
				else {
					if (abs($newPct) >= .01)
						$newCharge = round($v->Amount->Amount*$newPct,2);
					if ($newMin > 0 && $newCharge < $newMin)
						$newCharge = $newMin;
				}
				if ($newCharge > 0) {
					$v->Amount->Amount = $newCharge;
				}
				if ($newMin > 0 && $newMin > $v->Amount->Amount) {
					$v->Amount->Amount = $surcharge["custom_minimum_charge"];
				}
				$cart['custom']['rates'][$tmp->ServiceType]["surcharges"][$v->SurchargeType] = array("product_id"=>$cd[1],"amount"=>$v->Amount->Amount,"name"=>$surcharge["name"]);
			}
		} else {
			if (property_exists($tmp,"Amount") && $tmp->Amount->Amount > 0)
				$this->logMessage(__FUNCTION__, sprintf("unknown fedex surcharge [%s] rating [%s]", print_r($v,true), print_r($tmp,true)), 1, true);
		}
		return $cart;
	}

	function updateNotes( $o_id, $msg ) {
		$m_stmt = $this->prepare(sprintf("update orders set order_status = order_status | ?, notes = concat(notes, ?) where id = ?"));
		$m_stmt->bindParams(array("isi", STATUS_DRIVER_CHANGED, sprintf("%s: %s\n", date("Y-m-d h:i a"), $msg), $o_id));
		$m_stmt->execute();
	}

	function byKmOverride( $product ) {
		$default = $this->getUserInfo("custom_by_km");
		$m_id = $this->getUserInfo("id");
		if (!$junction = $this->fetchSingle(sprintf("select * from members_by_folder where member_id = %d limit 1", $m_id))) return $default;
		$grp = $this->fetchSingle(sprintf("select * from custom_member_product_options where isgroup = 1 and member_id = %d and product_id = %d", $junction["folder_id"], $product["id"]));
		$mbr = $this->fetchSingle(sprintf("select * from custom_member_product_options where isgroup = 0 and member_id = %d and product_id = %d", $junction["id"], $product["id"]));
		if (is_array($grp) && $grp["toggle_km_zone"]) $default = !$default;
		if (is_array($mbr) && $mbr["toggle_km_zone"]) $default = !$default;
		if ((array_key_exists("optType",$_POST) && $_POST["optType"] == "S" && array_key_exists("selectService",$_POST)) ||
				($this->checkArray("quote:optType",$_SESSION) && $_SESSION["quote"]["optType"] == "S")) {
			$this->logMessage(__FUNCTION__, sprintf("/// temp force to by km for same day out of zone"),1);
			$default = true;
		}
		else {
			$this->logMessage(__FUNCTION__, sprintf("override failed post [%s] request [%s] session [%s] trace [%s]", print_r($_POST,true), print_r($_REQUEST,true), print_r($_SESSION,true), print_r(debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS),true)),1);
		}
		return $default;
	}	
	/**
	 * sanitizehtmlspecialchars
	 *
	 * @param  mixed $data
	 * @return void
	 */
	function sanitizehtmlspecialchars($data) {
		if (is_array($data)) {
			foreach ( $data as $key => $value ) {
				$data[htmlspecialchars($key)] = $this->sanitizehtmlspecialchars($value);
			}
		} else if (is_object($data)) {
			$values = get_class_vars(get_class($data));
			foreach ( $values as $key => $value ) {
				$data->{htmlspecialchars($key)} = $this->sanitizehtmlspecialchars($value);
			}
		} else {
			$data = htmlspecialchars($data);
		}
		return $data;
	}
}

class preparedStatement extends Common {

	private $m_statement = null;
	private $m_handle;

	function __construct($sql,$h = null) {
		parent::__construct();
		$this->logMessage('__construct', sprintf('sql : [%s]',$sql), 2);
		if ($h == null) $h = $GLOBALS['globals']->getConnection();
		$this->m_handle = $h;
		if (!($this->m_statement = $this->m_handle->prepare($sql)))
			$this->logDBMessage('prepare', sprintf("Error: sql[%s] Msg[%s]", $sql, $this->m_handle->error), 1);
	}

	function byReference($parms) {
		$refs = array();
		foreach($parms as $key=>$value) {
			$refs[$key] = &$parms[$key];
		}
		return $refs;
	}

	function bindParams($fields) {
		$this->logMessage('bindParams', sprintf('Fields: [%s]',print_r($fields,true)), 2);
		$method = new ReflectionMethod('mysqli_stmt', 'bind_param');
		$status = false;
		if (!is_bool($this->m_statement))
			try {
				$method->invokeArgs($this->m_statement, $this->byReference($fields));
				$status = true;
			}
			catch (Exception $e) {
				$this->logMessage('bindParams',sprintf('Error: %s',$e->getMessage()),1,true);
			}
		return $status;
	}

	function execute($sql=null,$handle=null) {
		if (!is_bool($this->m_statement)) {
			if (!$this->m_statement->execute()) {
				$this->logDBMessage('execute', sprintf("Error: Msg[%s]", $this->m_handle->error), 1);
				return false;
			}
			else return true;
		} else {
			$this->logDBMessage('execute', 'Error: invalid statement handle', 1);
			return false;
		}
	}

	function fetchAll($sql=null,$handle=null) {
		if (!is_bool($this->m_statement)) {
			if (!$this->m_statement->execute()) {
				$this->logDBMessage('fetchAll', sprintf("Error: sql[%s] Msg[%s]", $sql, $this->m_handle->error), 1);
				return false;
			}
			else {
				$res = $this->m_statement->get_result();
				$result = $this->m_statement->fetch_all();
				return $result;
			}
		} else {
			$this->logDBMessage('fetchAll', 'Error: invalid statement handle', 1);
			return false;
		}
	}

	function getStatement() {
		return $this->m_statement;
	}

}

class ajax extends Common {
	
	function __construct() {
		parent::__construct();
	}
	
	function show() {
		if (count($_REQUEST) != 0 && array_key_exists('ajax',$_REQUEST) && array_key_exists('module',$_REQUEST)) {
			$class = $_REQUEST['module'];
			$function = $_REQUEST['ajax'];
			$this->logMessage('show', sprintf('Ajax request for %s::%s',$class,$function), 2);
			if (class_exists($class)) {
				$class = new $class();
				if (method_exists($class, $function)) {
					return $class->{$function}();
				}
				else {
					$this->logMessage('show', 'Class or Function did not exist', 2);
				}
			}
		}
	}

	function loadProvinces() {
		if (array_key_exists('c_id',$_REQUEST)) {
			$c_id = $_REQUEST['c_id'];
			$select = new provinceSelect(false);
			$select->addAttributes(array(
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

	static function captcha() {
		//require_once(ADMIN.'classes/recaptchalib.php');
		$c = new Common();
		$keys = $c->depairOptions($c->getConfigVar('captcha'));
		//$html = recaptcha_get_html($keys['public'], false, array_key_exists("secureLink",$GLOBALS) ? $GLOBALS["secureLink"] : 0);
		if ($c->hasOption("captcha"))
			$html = $c->getOption("captcha");
		else
			$html = sprintf("<script type='text/javascript' src='%s' async defer></script> <div id='recaptcha' class='g-recaptcha' data-sitekey='%s'></div>",$GLOBALS["recaptcha"]["src"],$keys["public"]);
		$status = true;
		if ($c->isAjax()) {
			return $c->ajaxReturn(array('status'=>true,'html'=>$html),array('code'=>1));
		}
		else return array('status'=>true,'html'=>$html);
	}

}

class Inventory extends Common {

	static function auditInventory( $inventory_id, $quantity, $reference_id, $comments ) {
		$c = new Common();
		$c->logMessage('auditInventory',"($inventory_id,$quantity,$reference_id,$comments)",1);
		$obj = new preparedStatement('insert into product_inventory_audit(inventory_id,audit_date,quantity,comment,order_id) values(?,?,?,?,?)');
		$obj->bindParams(array('dsdsd',$inventory_id,date(DATE_ATOM),$quantity,$comments,$reference_id));
		return $obj->execute();
	}

	static function updateInventory( $inventory_id, $quantity, $reference_id, $comments ) {
		$c = new Common();
		$c->logMessage('updateInventory',"($inventory_id,$quantity,$reference_id,$comments)",1);
		if ($inventory_id == 0) return true;
		$inv = $c->fetchSingle(sprintf('select * from product_inventory where id = %d',$inventory_id));
		//$c->beginTransaction();
		if ($status = $c->execute(sprintf('update product_inventory set quantity = quantity %s %d where id = %d',$quantity < 0 ? '-':'+',abs($quantity),$inventory_id))) {
			$status = $status && Inventory::auditInventory($inventory_id,$quantity,$reference_id,$comments);
		}
		$c->logMessage("updateInventory",sprintf("return status is [$status]"),4);
		return $status;
	}
}

class Address extends Common {

	static function formatData($data) {
		$c = new Common();
		if (is_null($data) || !(is_array($data) && array_key_exists("id",$data)))
			return array("id"=>0,"country_id"=>0,"province_id"=>0);
		if (array_key_exists("id",$data)) $data['formattedAddress'] = Address::formatAddress($data['id']);
		if (array_key_exists('province_id',$data) && $p = $c->fetchSingle(sprintf('select * from provinces where id = %d',$data['province_id']))) {
			$data['province'] = $p['province'];
			$data['provinceCode'] = $p['province_code'];
		}
		else {
			$data['province'] = "";
			$data['provinceCode'] = "";
		}
		if ($ctry = $c->fetchSingle(sprintf('select * from countries where id = %d',$data['country_id']))) {
			$data['country'] = $ctry['country'];
			$data['countryCode'] = $ctry['country_code'];
		}
		$data['mapAddress'] = Address::formatAddress($data['id'],'map');
		$data['encodedAddress'] = urlencode($data['mapAddress']);
		$data['viewMap'] = sprintf('<a title="View Map" onclick="map_popup(\'http://maps.google.ca/maps?f=q&amp;q=%s\')" href="#">VIEW MAP</a>',urlencode($data['mapAddress']));
		if (array_key_exists("addresstype",$data))
			$data["code"] = $c->fetchSingle(sprintf("select * from code_lookups where id = %d", $data["addresstype"]));
		$c->logMessage('formatData',sprintf('return is [%s]',print_r($data,true)),4);
		return $data;
	}

	static function formatAddress($id, $formatType = null) {
		$c = new Common();
		$c->logMessage('formatAddress',sprintf('(%d,%s)',$id,$formatType),2);
		$return = '';
		$addr = array();
		if ($address = $c->fetchSingle(sprintf('select * from addresses where id = %d',$id))) {
			if (strlen($address['province_id']) > 0 && $prov = $c->fetchSingle(sprintf('select * from provinces where id = %d',$address['province_id']))) {
				$address['province_code'] = $prov['province_code'];
				$address['province'] = $prov['province'];
			}
			else {
				$address['province'] = '';
				$address['province_code'] = '';
			}
			if (strlen($address['country_id']) > 0 && $country = $c->fetchSingle(sprintf('select * from countries where id = %d',$address['country_id']))) {
				$address['country_code'] = $country['country_code'];
				$address['country'] = $country['country'];
			}
			else {
				$address['country'] = '';
				$address['country_code'] = '';
			}
			switch($formatType) {
			case 'map':
				$return = sprintf("%s %s", $address['addressname'], $address['line1']);
				if (strlen($address['line2']) > 0) $return .= ' '.$address['line2'];
				if (strlen($address['city']) > 0) $return .= ' '.$address['city'];
				if (strlen($address['province']) > 0) $return .= ' '.$address['province'];
				if (strlen($address['postalcode']) > 0) $return .= ' '.$address['postalcode'];
				break;
			default:
				$return = sprintf("
				<div class='formattedAddress'>
					<div class='name'>%s</div>
					<div class='line seq1'>%s</div>
					%s
					<div class='city'>%s</div>
					<div class='comma'>%s</div>
					<div class='province'>%s</div>
					<div class='province_code'>%s</div>
					<div class='postal'>%s</div>
					<div class='country'>%s</div>
					<div class='countryCode'>%s</div>
					<div class='clearfix'></div>
				</div>",$address['addressname'],$address['line1'],strlen($address['line2']) > 0 ? sprintf("<div class='line seq2'>%s</div>",
				$address['line2']) : '',$address['city'],strlen($address['city']) > 0?',':'',
				$address['province'],$address['province_code'],$address['postalcode'],$address['country'],$address['country_code']);
			}
		}
		return $return;
	}
	
	public static function geocode($flds, &$lat, &$lng) {	// lat/lng for compability
		$return = array("latitude"=>0,"longitude"=>0,"status"=>false);
		$return["status"] = parent::geoCode($flds,$return["latitude"],$return["longitude"]);
		return $return;
	}

}

class Ecom extends Common {

	static function formatCart($cart) {
		$c = new Common();
		$cart['header']['formattedTotal'] = $c->my_money_format($cart['header']['total']);
		$cart['header']['formattedValue'] = $c->my_money_format($cart['header']['value']);
		$cart['header']['formattedShipping'] = $c->my_money_format($cart['header']['shipping']);
		$cart['header']['formattedTaxes'] = $c->my_money_format($cart['header']['taxes']);
		$cart['header']['formattedDiscountValue'] = $c->my_money_format(array_key_exists('discount_value',$cart['header']) ? $cart['header']['discount_value'] : 0);
		$cart['header']['formattedRedemptionValue'] = $c->my_money_format($cart['header']['redemption_value']);
		if ($cart['header']['discount_type'] == 'P')
			$cart['header']['formattedDiscountRate'] = sprintf('%.02f',$cart['header']['discount_rate']).'%';
		else
			$cart['header']['formattedDiscountRate'] = $c->my_money_format($cart['header']['discount_rate']);
		$cart['header']['formattedLineDiscounts'] = $c->my_money_format(array_key_exists('line_discounts',$cart['header']) ? $cart['header']['line_discounts'] : 0);
		$cart['header']['formattedNet'] = $c->my_money_format(array_key_exists('net',$cart['header']) ? $cart['header']['net'] : 0);
		if (array_key_exists('addresses',$cart)) {
			foreach($cart["addresses"] as $key=>$addr) {
				if (is_array($addr) && ($addr["id"] != 0))
					$cart['addresses'][$key] = Address::formatData($addr);
				else unset($cart["addresses"][$key]);
			}
		}
		$savings = 0;
		foreach($cart['products'] as $key=>$product) {
			$cart['products'][$key]['formattedValue'] = $c->my_money_format($product['value']);
			$cart['products'][$key]['formattedPrice'] = $c->my_money_format($product['price']*$product['qty_multiplier']);
			$cart['products'][$key]['formattedExtended'] = $c->my_money_format($product['price']*$product['qty_multiplier']*$product['quantity']);
			$cart['products'][$key]['formattedShipping'] = $c->my_money_format($product['shipping']);
			foreach($product['taxdata'] as $subkey=>$tax) {
				$cart['products'][$key]['taxdata'][$subkey]['formattedTaxAmount'] = $c->my_money_format($tax['tax_amount']);
				$cart['products'][$key]['taxdata'][$subkey]['formattedTaxableAmount'] = $c->my_money_format($tax['taxable_amount']);
			}
			//
			//	discounts are negative, others positive
			//
			$cart['products'][$key]['savings'] = round($cart['products'][$key]['regularPrice']*$cart['products'][$key]['quantity']*$cart['products'][$key]['qty_multiplier'],2) 
					- $cart['products'][$key]['value'] - $cart['products'][$key]['recurring_discount_value'] - $cart['products'][$key]['discount_value'];
			$savings += $cart['products'][$key]['savings'];
			$cart['products'][$key]['formattedSavings'] = $c->my_money_format($cart['products'][$key]['savings']);
		}
		//$savings -= $cart['header']['discount_value'];
		$cart['header']['savings'] = $savings;
		$cart['header']['formattedFreeShipping'] = $c->my_money_format($cart['header']['freeShipping']);
		$cart['header']['formattedSavings'] = $c->my_money_format($savings);
		$cart['header']['totalSavings'] = $cart['header']['savings'] + $cart['header']['freeShipping'] - $cart['header']['discount_value'];
		$cart['header']['formattedTotalSavings'] = $c->my_money_format($cart['header']['totalSavings']);
		$cart['header']['formattedHandlingFee'] = $c->my_money_format($cart['header']['handling']);
		$config = new custom(0);
		if (method_exists($config,'formatCart')) {
			$cart = $config->formatCart($cart);
		}
		$c->logMessage(__FUNCTION__,sprintf('return cart [%s]',print_r($cart,true)),2);
		return $cart;
	}

	static function initCart($passedId = null) {
		$c = new Common();
		if (is_null($passedId)) $passedId = array_key_exists('user',$_SESSION) ? $_SESSION['user']['info']['id'] : 0;
		$cart = array('products'=>array(),'header'=>array('value'=>0,'shipping'=>0,'total'=>0,'taxes'=>0),'addresses'=>array('shipping'=>array('id'=>0,'country_id'=>0,'province_id'=>0,'email'=>''),'billing'=>array('id'=>0,'country_id'=>0,'province_id'=>0,'email'=>'')));
		if (array_key_exists('user',$_SESSION)) {
			//if ($tmp = $c->fetchSingle(sprintf('select * from addresses where deleted = 0 and ownertype = "member" and ownerid = %d and addresstype=%d',$passedId,ADDRESS_PICKUP)))
			//	$cart['addresses']['pickup'] = $tmp;
			//if ($tmp = $c->fetchSingle(sprintf('select * from addresses where deleted = 0 and ownertype = "member" and ownerid = %d and addresstype=%d',$passedId,ADDRESS_DELIVERY)))
			//	$cart['addresses']['shipping'] = $tmp;
			//if ($tmp = $c->fetchSingle(sprintf('select * from addresses where deleted = 0 and ownertype = "member" and ownerid = %d and addresstype=%d',$passedId,ADDRESS_BILLING)))
			//	$cart['addresses']['billing'] = $tmp;
		}
		$cart['header']['coupon_id'] = 0;
		$cart['header']['discount_rate'] = 0;
		$cart['header']['discount_value'] = 0;
		$cart['header']['discount_type'] = '';
		$cart['header']['shipping_only'] = 0;
		$cart['header']['discount_teaser'] = '';
		$cart['header']['discount_code'] = '';
		$cart['header']['line_discounts'] = 0;
		$cart['header']['member_id'] = $passedId;
		$cart['header']['freeShipping'] = 0;
		$cart['header']['savings'] = 0;
		$cart['header']['handling'] = 0;
		$cart['header']['exchange_rate'] = 1.0;
		$cart['header']['points_collected'] = 0;
		$cart['header']['points_redeemed'] = 0;
		$cart['header']['redemption_value'] = 0;
		$config = new custom(0);
		if (method_exists($config,'initCart')) {
			$cart = $config->initCart($cart);
		}
		$_SESSION['cart'] = $cart;
		$c = new common;
		$c->logMessage(__FUNCTION__,sprintf("returning [%s]",print_r($cart,true)),4);
		return $cart;
	}

	static function getCart() {
		return array_key_exists('cart',$_SESSION) ? $_SESSION['cart'] : Ecom::initCart();
	}

	function updateLine($cart,$key,$p_id,$opt,$color,$size,$qty,$r_prd,$r_qty,$msg) {
		$this->logMessage(__FUNCTION__,sprintf("key [%s] p_id [%s] opt [%s] color [%s] size [%s] qty [%s] r_prd [%s] r_ty [%s]",$key,$p_id,$opt,$color,$size,$qty,$r_prd,$r_qty),1);
		if (!array_key_exists($key,$cart['products'])) {
			$prod = $this->fetchSingle(sprintf('select * from product where id = %d',$p_id));
			unset($prod['attachment_content']);	// can kill any cookies we use
			$cart['products'][$key] = $prod;
			$cart['products'][$key]['url'] = $this->getUrl('product',$p_id,$prod);
			$cart['products'][$key]['line_id'] = count($cart['products']);
			$cart['products'][$key]['message'] = '';
			$cart['products'][$key]['inventory_id'] = 0;
			$cart['products'][$key]['color'] =  $color;
			$cart['products'][$key]['size'] =  $size;
		}
		$invAmt = $this->fetchSingle(sprintf("select * from product_inventory where product_id = %d and options_id = %d and color = %d and size = %d and (start_date = '0000-00-00' or start_date <= CURDATE()) and (end_date = '0000-00-00' or end_date >= CURDATE())",$p_id,$opt,$color,$size));
		if (is_array($invAmt) && count($invAmt) > 0) {
			if ($qty > $invAmt["quantity"]) {
				$this->addEcomError(sprintf("Available inventory has been exceeded for '%s'",$cart['products'][$key]["name"]));
				$qty = $invAmt["quantity"];
			}
			$cart['products'][$key]["inventory_id"] = $invAmt["id"];
		}
		$cart['products'][$key]['coupon_id'] = 0;
		$cart['products'][$key]['discount_rate'] = 0;
		$cart['products'][$key]['discount_value'] = 0;
		$cart['products'][$key]['shipping_only'] = 0;
		$cart['products'][$key]['discount_type'] = '';
		$cart['products'][$key]['recurring_period'] = 0;
		$cart['products'][$key]['recurring_discount_rate'] = 0;
		$cart['products'][$key]['recurring_discount_value'] = 0;
		$cart['products'][$key]['recurring_shipping_only'] = 0;
		$cart['products'][$key]['recurring_discount_type'] = '';
		$cart['products'][$key]['quantity'] = $qty;
		$cart['products'][$key]['options_id'] = $opt;
		if ($opt > 0)
			$cart['products'][$key]['qty_multiplier'] = $this->fetchScalar(sprintf('select qty_multiplier from product_options where id = %d',$opt));
		else
			$cart['products'][$key]['qty_multiplier'] = 1;
		$cart['products'][$key]['product_id'] = $p_id;
		$cart['products'][$key]['recurring_qty'] = $r_qty;	//array_key_exists('recurring_qty',$request) && $request['recurring_qty'] > 0 ? $request['recurring_qty'] : 0;
		$cart['products'][$key]['recurring_period'] = $r_prd;	//array_key_exists('recurring_qty',$request) && $request['recurring_qty'] > 0 ? $request['recurring_period'] : '';
		$cart['products'][$key]['message'] = $msg;	//array_key_exists('message',$request) ? $request['message'] : '';
		if ($r_prd > 0 && $r_qty > 0) {
			$this->logMessage(__FUNCTION__,sprintf('key [%s] setting recurring discount',$key,$cart['products'][$key]),1);
			$cart['products'][$key]['recurring_qty'] = $r_qty;
			$cart['products'][$key]['recurring_period'] = $r_prd;
			$tmp = $this->fetchSingle(sprintf('select * from product_recurring where id = %d',$r_prd));
			$cart['products'][$key]['recurring_discount_rate'] = $tmp['discount_rate'];
			$cart['products'][$key]['recurring_discount_type'] = $tmp['percent_or_dollar'];
			$cart['products'][$key]['recurring_shipping_only'] = $tmp['shipping_only'];
		}
		$this->logMessage(__FUNCTION__,sprintf("returning [%s]", print_r($cart,true)),1);
		return $cart;
	}

	function updateCart($cart=array(),$toUpdate = null) {
		$this->logMessage('updateCart',sprintf('begin update cart [%s] toUpdate [%s]',print_r($cart,true),print_r($toUpdate,true)),3);
		$fromPassed = count($cart) > 0;
		if (!is_null($toUpdate) && is_array($toUpdate))
			$request = $toUpdate;
		else
			$request = $_REQUEST;
		if (!$fromPassed) {
			if (!array_key_exists('cart',$_SESSION)) $_SESSION['cart'] = Ecom::initCart();
			$cart = $_SESSION['cart'];
		}
		$cart['header']['order_id'] = 0;
		if ($this->getUserInfo('id') != 0 && $cart['header']['member_id'] == 0)
			$cart['header']['member_id'] = $this->getUserInfo('id');
		if (array_key_exists('currency_id',$request)) {
			if (!array_key_exists('currency_id',$cart['header']) || $cart['header']['currency_id'] != $request['currency_id']) {
				$cart['header']['currency_id'] = $request['currency_id'];
				$tmp = $this->fetchSingle(sprintf('select e.*, l.extra from exchange_rate e, code_lookups l where l.id = %d and e.currency_id = l.id order by effective_date desc limit 1',$request['currency_id']));
				$cart['header']['exchange_rate'] = $tmp['exchange_rate'];
				$cart['header']['exchange_format'] = $tmp['extra'];
			}
		}
		if (array_key_exists('updateCart',$request)) {
			if (array_key_exists('removeProduct',$request)) {
				foreach($request['removeProduct'] as $key=>$value) {
					if ($value == 1) {
						unset($cart['products'][$key]);
						unset($request['quantity'][$key]);
						unset($request['options_id'][$key]);
						if (array_key_exists('message',$request)) unset($request['message'][$key]);
						if (array_key_exists('recurring_period',$request)) unset($request['recurring_period'][$key]);
						if (array_key_exists('recurring_qty',$request)) unset($request['recurring_qty'][$key]);
					}
				}
			}
			if (array_key_exists('quantity',$request)) {
				foreach($request['quantity'] as $key=>$value) {
					if ($value == 0 || !array_key_exists($key,$cart["products"])) {
						unset($cart['products'][$key]);
						if (array_key_exists("options_id",$cart) && array_key_exists($key,$cart["options_id"])) unset($cart['options_id'][$key]);
						if (array_key_exists("color",$cart) && array_key_exists($key,$cart["color"])) unset($cart['color'][$key]);
						if (array_key_exists("size",$cart) && array_key_exists($key,$cart["size"])) unset($cart['size'][$key]);
						if (array_key_exists("quantity",$cart) && array_key_exists($key,$cart["quantity"])) unset($cart['quantity'][$key]);
					}
					else {
						$cart['products'][$key]['quantity'] = $value;
						//
						//	key is in format id|option|color|size of original selection, but option/color/size maye have changed or not be present at all
						//
						$tmp = explode("|",$key);
						$tmpProd = $tmp[0];
						$tmpOption = array_key_exists("options_id",$request) && array_key_exists($key,$request["options_id"]) ? $request["options_id"][$key] : 0;
						$tmpColor = array_key_exists("color",$request) && array_key_exists($key,$request["color"]) ? $request["color"][$key] : 0;
						$tmpSize = array_key_exists("size",$request) && array_key_exists($key,$request["size"]) ? $request["size"][$key] : 0;
						$invAmt = $this->fetchSingle(sprintf("select i.* from product_inventory i where product_id = %d and options_id = %d and color = %d and size = %d and (start_date = '0000-00-00' or start_date <= CURDATE()) and (end_date = '0000-00-00' or end_date >= CURDATE())",$tmpProd,$tmpOption,$tmpColor,$tmpSize));
						if (is_array($invAmt) && count($invAmt) > 0) {
							if ($value > $invAmt["quantity"]) {
								$this->addEcomError(sprintf("Available inventory has been exceeded for '%s' (%s)",$cart['products'][$key]["name"],$invAmt["quantity"]));
								$cart['products'][$key]["quantity"] = $invAmt["quantity"];
							}
							$cart['products'][$key]["inventory_id"] = $invAmt["id"];
						}
					}
				}
			}
			if (array_key_exists('options_id',$request)) {
				foreach($request['options_id'] as $key=>$value) {
					if (array_key_exists($key,$cart['products']) && $value > 0) {
						$cart['products'][$key]['options_id'] = $value;
						$cart['products'][$key]['qty_multiplier'] = $this->fetchScalar(sprintf('select qty_multiplier from product_options where id = %d',$value));
					}
				}
			}
			if (array_key_exists('message',$request)) {
				foreach($request['message'] as $key=>$value) {
					if (array_key_exists($key,$cart['products']))
						$cart['products'][$key]['message'] = $value;
				}
			}
			if (array_key_exists('color',$request)) {
				foreach($request['color'] as $key=>$value) {
					if (array_key_exists($key,$cart['products']))
						$cart['products'][$key]['color'] = $value;
				}
			}
			if (array_key_exists('size',$request)) {
				foreach($request['size'] as $key=>$value) {
					if (array_key_exists($key,$cart['products']))
						$cart['products'][$key]['size'] = $value;
				}
			}
			if (array_key_exists('recurring_period',$request)) {
				foreach($request['recurring_period'] as $key=>$value) {
					$this->logMessage(__FUNCTION__,sprintf('recurring key [%s] current [%d] change to [%d] period [%d]',$key,$cart['products'][$key]['recurring_qty'],$request['recurring_qty'][$key],$value),3);
					if ($value > 0 && array_key_exists($key,$request['recurring_qty']) && $request['recurring_qty'][$key] > 0) {
						$cart['products'][$key]['recurring_qty'] = $request['recurring_qty'][$key];
						$cart['products'][$key]['recurring_period'] = $request['recurring_period'][$key];
					}
					else {
						$cart['products'][$key]['recurring_qty'] = 0;
						$cart['products'][$key]['recurring_period'] = '';
						$cart['products'][$key]['recurring_discount_rate'] = 0;
						$cart['products'][$key]['recurring_discount_type'] = '';
						$cart['products'][$key]['recurring_shipping_only'] = 0;
					}
				}
			}
		}
		elseif (array_key_exists('addToCart',$request) && array_key_exists('product_id',$request) && $request['product_id'] > 0 && $request['product_quantity'] > 0) {
			if (is_array($request["product_id"])) {
				foreach($request["product_id"] as $key=>$prod) {
					$opt = array_key_exists('options_id',$request) ? $request['options_id'][$key] : '0';
					$a_key = sprintf('%s|%s',$prod,$opt);
					$color = array_key_exists('color',$request) ? $request['color'][$key] : '0';
					$a_key .= sprintf('|%s',$color);
					$size = array_key_exists('size',$request) ? $request['size'][$key] : '0';
					$a_key .= sprintf('|%s',$size);
					$qty = $request['product_quantity'][$key];
					$r_prd = array_key_exists('recurring_period',$request) ? $request['recurring_period'][$key] : '0';
					$r_qty = array_key_exists('recurring_qty',$request) ? $request['recurring_qty'][$key] : '0';
					$msg = array_key_exists('message',$request) ? $request['message'][$key] : '';
					if ($qty > 0)
						$cart = Ecom::updateLine($cart,$a_key,$prod,$opt,$color,$size,$qty,$r_prd,$r_qty,$msg);
				}
			}
			else {
					$opt = array_key_exists('options_id',$request) ? $request['options_id'] : '0';
					$key = sprintf('%s|%s',$request['product_id'],$opt);
					$color = array_key_exists('color',$request) ? $request['color'] : '0';
					$key .= sprintf('|%s',$color);
					$size = array_key_exists('size',$request) ? $request['size'] : '0';
					$key .= sprintf('|%s',$size);
					$qty = $request['product_quantity'];
					$r_prd = array_key_exists('recurring_period',$request) ? $request['recurring_period'] : '0';
					$r_qty = array_key_exists('recurring_qty',$request) ? $request['recurring_qty'] : '0';
					$msg = array_key_exists('message',$request) ? $request['message'] : '';
					if ($qty > 0)
						$cart = Ecom::updateLine($cart,$key,$request['product_id'],$opt,$color,$size,$qty,$r_prd,$r_qty,$msg);
			}
		}
		if (array_key_exists('discount_code',$request)) {
			foreach($cart['products'] as $key=>$prod) {
				$cart['products'][$key]['coupon_id'] = 0;
				$cart['products'][$key]['discount_rate'] = 0;
				$cart['products'][$key]['discount_value'] = 0;
				$cart['products'][$key]['inventory_id'] = 0;
				$cart['products'][$key]['shipping_only'] = 0;
				$cart['products'][$key]['discount_type'] = '';
			}
			$cart['header']['discount_code'] = '';
			$cart['header']['coupon_id'] = 0;
			$cart['header']['discount_value'] = 0;
			$cart['header']['discount_rate'] = 0;
			$cart['header']['discount_type'] = '';
			$cart['header']['discount_teaser'] = '';
			$cart['header']['line_discounts'] = 0;
			unset($cart['header']['discount']);
		}
		if (array_key_exists('discount_code',$request) && strlen($request['discount_code']) > 0) {
			if (!$code = $this->fetchSingle(sprintf('select * from coupons where code = "%s" and enabled = 1 and published = 1 and (start_date = "0000-00-00 00:00:00" or start_date <= now()) and (end_date = "0000-00-00 00:00:00" or end_date >= now())',$request['discount_code']))) {
				$this->addEcomError('Invalid Coupon Code entered');
			} else {
				$cart['header']['discount_code'] = $code['code'];
				$cart['header']['discount_teaser'] = $code['name'];
				$cart['header']['discount'] = $code;
				$valid = true;
				$header = true;
				if ($products = $this->fetchScalarAll(sprintf('select owner_id from relations where owner_type = "product" and related_type = "coupon" and related_id = %d',$code['id']))) {
					$this->logMessage('updateCart',sprintf('related product check [%s]',print_r($products,true)),3);
					$valid = false;
					foreach($cart['products'] as $key=>$line) {
						if (strpos('|'.implode('|',$products).'|','|'.$line['product_id'].'|') !== false) {
							//if (!$valid) {
								$cart['products'][$key]['coupon_id'] = $code['id'];
						    $cart['products'][$key]['discount_rate'] = $code['amount'];
    						$cart['products'][$key]['discount_type'] = $code['percent_or_dollar'];
								$cart['products'][$key]['shipping_only'] = $code['shipping_only'];
								$valid = true;
							//}
							//else $this->addEcomMessage('Discount will only apply to the first item');
						}
					}
					if (!$valid)
						$this->addEcomError('No eligible products were found for this Coupon');
					$header = false;
				} else {
					if ($folders = $this->fetchScalarAll(sprintf('select owner_id from relations where owner_type = "productfolder" and related_type = "coupon" and related_id = %d',$code['id']))) {
						$valid = false;
						$this->logMessage('updateCart',sprintf('related product check [%s] vs folder [%s]',print_r($cart['products'],true),print_r($folders,true)),3);
						foreach($folders as $folder) {
							foreach($cart['products'] as $key=>$line) {
								if ($member = $this->fetchSingle(sprintf('select 1 from product_by_folder where folder_id = %d and product_id = %d',$folder['owner_id'],$line['product_id']))) {
									$valid = true;
									$cart['products'][$key]['coupon_id'] = $code['id'];
							    $cart['products'][$key]['discount_rate'] = $code['amount'];
    							$cart['products'][$key]['discount_type'] = $code['percent_or_dollar'];
									$cart['products'][$key]['shipping_only'] = $code['shipping_only'];
								}
							}
						}
						if ($valid) $header = false;
					}
				}
				if ($valid && $header) {
					$cart['header']['coupon_id'] = $code['id'];
					$cart['header']['discount_rate'] = $code['amount'];
					$cart['header']['discount_type'] = $code['percent_or_dollar'];
					$cart['header']['shipping_only'] = $code['shipping_only'];
				}
			}
		}
		$points = 0;
		foreach($cart['products'] as $key=>$item) {
			$sql = sprintf('select pr.*, p.sale_startdate, p.sale_enddate from product_pricing pr, product p where p.id = pr.product_id and product_id = %d and min_quantity <= %d and max_quantity >= %d',$item['product_id'],$item['quantity'],$item['quantity']);
			$item['points'] = 0;
			if ($pricing = $this->fetchSingle($sql)) {
				$this->logMessage('updateCart',sprintf('updating key [%s] [%s] pricing [%s] sql [%s]',$key,print_r($item,true),print_r($pricing,true),$sql),4);
				$item['points'] = $item['quantity']*$item['qty_multiplier']*$pricing['points_buy'];
				$points += $item['points'];
				$item['price'] = $pricing['price'];
				$item['regularPrice'] = $pricing['price'];
				$item['onSale'] = false;
				if ($pricing['sale_startdate'] <= date('Y-m-d H:i:s') && $pricing['sale_enddate'] > date('Y-m-d H:i:s')) {
					$item['price'] = $pricing['sale_price'];
					$item['onSale'] = true;
				}
				if ($pricing['shipping_type'] == 'E') {
					$item['shipping'] = $item['quantity']*$pricing['shipping'];
				}
				else {
					$item['shipping'] = $pricing['shipping'];
				}
			}
			else {
				$item['price'] = 0;
				$item['shipping'] = 0;
			}
			if ($item['options_id'] != 0) {
				if ($options = $this->fetchSingle(sprintf('select * from product_options where product_id = %d and id = %d',$item['product_id'],$item['options_id']))) {
					if ($item['onSale'] && $options['sale_price'] != 0)
						$item['price'] += $options['sale_price'];
					else
						$item['price'] += $options['price'];
					if ($options['shipping'] != 0) {
						//
						//	options shipping are always each - exec decision
						//
						$item['shipping'] += $item['quantity']*$options['shipping'];
					}
				}
				else {
					$this->logMessage('updateCart',sprintf('Invalid options_id used item [%s] request [%s] cart [%s]',print_r($item,true),print_r($request,true),print_r($cart,true)),1,true);
				}
			}
			if ($item['recurring_period'] > 0 && $item['coupon_id'] == 0) {
				$tmp = $this->fetchSingle(sprintf('select * from product_recurring where id = %d',$item['recurring_period']));
				$item['recurring_discount_rate'] = $tmp['discount_rate'];
				$item['recurring_discount_type'] = $tmp['percent_or_dollar'];
				$item['recurring_shipping_only'] = $tmp['shipping_only'];
				$this->logMessage(__FUNCTION__,sprintf('product after recurring change [%s]',print_r($cart['products'][$key],true)),3);
			}
			$post = Ecom::lineValue($item);
			$post['savings'] = ($post['regularPrice'] - $post['price'])*$post['quantity']*$post['qty_multiplier'] - $post['discount_value'] - $post['recurring_discount_value'];
			$this->logMessage('updateCart',sprintf('pre lineValue [%s] post [%s]',print_r($item,true),print_r($post,true)),4);
			$item = $post;
			$cart['products'][$key] = $item;
		}
		$cart['header']['points_collected'] = $points;
		$cart = Ecom::recalcOrder($cart);
		if ($cart['header']['coupon_id'] > 0) {
			$code = $this->fetchSingle(sprintf('select * from coupons where id = %d and enabled = 1 and published = 1 and (start_date = "0000-00-00 00:00:00" or start_date <= now()) and (end_date = "0000-00-00 00:00:00" or end_date >= now())',$cart['header']['coupon_id']));
			if ($code['min_amount'] > 0 && $cart['header']['value'] < $code['min_amount']) {
				$cart['header']['discount_rate'] = 0;
				$this->addEcomError('Minimum amount not met for the given Discount Code');
				$cart = Ecom::recalcOrder($cart);
			}
		}
		$cart = Ecom::updateAbandoned($cart);
		$this->logMessage('updateCart',sprintf('cart after recalc [%s]',print_r($cart,true)),4);
		if (!$fromPassed)
			$_SESSION['cart'] = $cart;
		else
			return $cart;
	}

	static function updateAbandoned($cart) {
		$c = new Common();
		$c->logMessage(__FUNCTION__,sprintf('trackAbandoned [%s]',$c->getConfigVar('trackAbandoned')),1);
		if ((int)$c->getConfigVar('trackAbandoned') == 0) return $cart;
		$abandoned = array(
			'value'=>$cart['header']['value'],
			'shipping'=>$cart['header']['shipping'],
			'total'=>$cart['header']['total'],
			'taxes'=>$cart['header']['taxes'],
			'coupon_id'=>$cart['header']['coupon_id'],
			'member_id'=>$cart['header']['member_id'],
			'line_discounts'=>$cart['header']['line_discounts'],
			'url'=>$_SERVER['REQUEST_URI']
		);
		if (!array_key_exists('abandoned',$cart)) {
			$abandoned['created'] = date(DATE_ATOM);
			unset($abandoned['order_id']);
			$stmt = $c->prepare(sprintf('insert into cart_header(%s) values(%s?)',implode(', ',array_keys($abandoned)),str_repeat('?, ',count($abandoned)-1)));
		}
		else
			$stmt = $c->prepare(sprintf('update cart_header set %s=? where id = %d',implode('=?, ',array_keys($abandoned)),$cart['abandoned']['id']));
		$stmt->bindParams(array_merge(array(str_repeat('s',count($abandoned))),array_values($abandoned)));
		$stmt->execute();
		if (!array_key_exists('abandoned',$cart)) {
			$cart['abandoned']['id'] = $c->insertId();
			$cart['abandoned']['products'] = array();
		}
		$abandoned = array();
		foreach($cart['products'] as $key=>$product) {
			$abandoned['product_id'] = $product['product_id'];
			$abandoned['quantity'] = $product['quantity'];
			$abandoned['options_id'] = $product['options_id'];
			$abandoned['color'] = $product['color'];
			$abandoned['size'] = $product['size'];
			$abandoned['recurring_qty'] = $product['recurring_qty'];
			$abandoned['recurring_period'] = $product['recurring_period'];
			$abandoned['order_id'] = $cart['abandoned']['id'];
			$abandoned['line_id'] = $key;
			if (!array_key_exists($key,$cart['abandoned']['products'])) {
				$stmt = $c->prepare(sprintf('insert into cart_lines(%s) values(%s?)',
					implode(', ',array_keys($abandoned)), str_repeat('?, ', count($abandoned)-1)));
			}
			else {
				$stmt = $c->prepare(sprintf('update cart_lines set %s=? where id = %d',
					implode('=?, ',array_keys($abandoned)),$cart['abandoned']['products'][$key]));
			}
			$stmt->bindParams(array_merge(array(str_repeat('s',count($abandoned))),array_values($abandoned)));
			$stmt->execute();
			if (!array_key_exists($key,$cart['abandoned']['products'])) {
				$cart['abandoned']['products'][$key] = $c->insertId();
			}
		}
		if (count($cart['products']) > 0)
			$c->execute(sprintf('delete from cart_lines where order_id = %d and line_id not in ("%s")',
				$cart['abandoned']['id'], implode('","',array_keys($cart['products']))));
		else
			$c->execute(sprintf('delete from cart_lines where order_id = %d',$cart['abandoned']['id']));
		$tmp = $c->fetchAll(sprintf('select id,line_id from cart_lines where order_id=%d',$cart['abandoned']['id']));
		$cart['abandoned']['products'] = array();
		foreach($tmp as $key=>$value) {
			$cart['abandoned']['products'][$value['line_id']] = $value['id'];
		}
		return $cart;
	}

	static function lineValue($data) {
		$c = new Common();
		if ($data['shipping'] <= 0) $data['shipping'] = (float)$c->getConfigVar('productMinShipping');
		$maxShipping = (float)$c->getConfigVar('productMaxShipping');
		if ($maxShipping > 0 && $data['shipping'] > $maxShipping) $data['shipping'] = $maxShipping;
		$c->logMessage('lineValue',sprintf('shipping check maxShipping [%s] (float)maxShipping [%s] data[shipping] [%s]',$maxShipping,(float)$maxShipping,$data['shipping']),4);
		$net = ($data['quantity'] * $data['price'] * $data['qty_multiplier']) + $data['shipping'];
		$tmp = $data;
		//if (!$coupon = $c->fetchSingle(sprintf('select * from coupons where id = %d',$data['coupon_id'])))
		//	$coupon = array('shipping_only'=>0);
		$net = round($data['quantity'] * $data['price'] * $data['qty_multiplier'],2);
		switch($data['shipping_only']) {
			case 1:
				switch($tmp['discount_type']) {
					case 'D':
						//
						//	$ discount
						//
						$tmp['discount_value'] = -min($tmp['shipping'],$data['discount_rate']);
						break;
					case 'P':
						//
						//	% discount
						//
						$tmp['discount_value'] = -round(($tmp['shipping'] * $data['discount_rate'])/100,2);
						break;
					default:
						break;
				}
				$tmp['value'] = $data['quantity'] * $data['price'] + $tmp['shipping'];
				break;
			case 0:
			default:
				switch($tmp['discount_type']) {
					case 'D':
						//
						//	$ discount
						//
						$tmp['discount_value'] = -min($net,$data['discount_rate']);
						break;
					case 'P':
						//
						//	% discount
						//
						$tmp['discount_value'] = -round($net * ($data['discount_rate']/100),2);
						break;
					default:
						$tmp['discount_value'] = 0;
						break;
				}
				break;
		}
		$tmp['value'] = $net;
		switch($data['recurring_shipping_only']) {
			case 1:
				switch($tmp['recurring_discount_type']) {
					case 'D':
						//
						//	$ discount
						//
						$tmp['recurring_discount_value'] = -min($tmp['shipping'],$data['recurring_discount_rate']);
						break;
					case 'P':
						//
						//	% discount
						//
						$tmp['recurring_discount_value'] = -round(($tmp['shipping'] * $data['recurring_discount_rate'])/100,2);
						break;
					default:
						break;
				}
				break;
			case 0:
			default:
				switch($tmp['recurring_discount_type']) {
					case 'D':
						//
						//	$ discount
						//
						$tmp['recurring_discount_value'] = -min($net,$data['recurring_discount_rate']);
						break;
					case 'P':
						//
						//	% discount
						//
						$tmp['recurring_discount_value'] = -round($net * ($data['recurring_discount_rate']/100),2);
						break;
					default:
						$tmp['recurring_discount_value'] = 0;
						break;
				}
				break;
		}
		$tmp['value'] = $net;
		$net = $tmp['value'] + $tmp['recurring_discount_value'] + $tmp['discount_value'] + $tmp['shipping'];

		//
		//	taxes - grab the ship to address
		//
		//
		//	KJV - taxes based on the delivery address, international - no taxes either way
		//
		if (!array_key_exists('taxdata',$tmp)) $tmp['taxdata'] = array();
		$taxamt = 0;
		$product = $c->fetchSingle(sprintf('select * from product where id = %d',$tmp['product_id']));
		$address = array();
		if (array_key_exists('order_id',$tmp)) {
			//if ($c->fetchScalar(sprintf("select count(id) from addresses where country_id > 1 and ownertype='order' and ownerid=%d", $tmp["order_id"])) > 0)
			//	$address = array();
			//else
				$address = $c->fetchSingle(sprintf('select * from addresses where ownerid = %d and ownertype = "order" and addresstype = %d',$tmp['order_id'], ADDRESS_DELIVERY));
		}
		else {
			if (array_key_exists('shipping',$_SESSION['cart']['addresses']))
				$address = $_SESSION['cart']['addresses']['shipping'];
/*
			foreach($_SESSION['cart']['addresses'] as $k=>$v) {
				if ((!is_array($v)) || $v["country_id"] > 1) $address=array();
			}
*/
		}
		$tmp['taxdata'] = array();
		$c->logMessage('lineValue',sprintf('address for taxes [%s]',print_r($address,true)),4);
		if (count($address) > 0) {
			$prov = $address['province_id'];
			$taxes = $c->fetchAll(sprintf('select * from taxes where province_id = %d and deleted = 0 and 1=1',$prov));
			$p_tmp = $product; 
			unset($p_tmp['attachment_content']);
			$c->logMessage("lineValue",sprintf("taxes [%s] product [%s]",print_r($taxes,true),print_r($p_tmp,true)),4);
			foreach($taxes as $tax) {
				if (strpos($product['tax_exemptions'],'|'.$tax['name'].'|') === false)
					$amt = $net;
				else
					$amt = $tmp['shipping'];
				if (!array_key_exists($tax['id'],$tmp['taxdata']))
					$tmp['taxdata'][$tax['id']] = array('id'=>0,'line_id'=>0);
				$tmp['taxdata'][$tax['id']]['rate'] = $tax['tax_rate'];
				$tmp['taxdata'][$tax['id']]['tax_amount'] = round(($amt * $tax['tax_rate'])/100,2);
				$tmp['taxdata'][$tax['id']]['taxable_amount'] = $amt;
				$taxamt += $tmp['taxdata'][$tax['id']]['tax_amount'];
			}
		}
		else {
			$c->addMessage('No Tax Address found');
			$c->logMessage(__FUNCTION__,sprintf("no tax address from data [%s] cart [%s] tmp [%s]",print_r($data,true), print_r($_SESSION['cart']['addresses'],true), print_r($tmp,true)),1);
		}
		$tmp['taxes'] = $taxamt;
		$tmp['total'] = $net;
		$c->logMessage("lineValue",sprintf("return (%s)",print_r($tmp,true)),4);
		return $tmp;
	}

	static function recalcOrder($order) {
		$c = new Common();
		$c->logMessage(__FUNCTION__,sprintf("input [%s]",print_r($order,true)),3);
		$value = 0;
		$shipping = 0;
		$taxable = array();
		$discounts = 0;
		$amounts = array();
		$config = new custom(0);
		if (method_exists($config,'preRecalc')) {
			$c->logMessage(__FUNCTION__,sprintf("*** taxable before preRecalc [%s] order [%s]", print_r($taxable,true), print_r($order,true)), 4);
			$order = $config->preRecalc($order);
			$shipping = $order['header']['shipping'];
			$handling = $order['header']['handling'];
			$taxable = array_key_exists('taxes',$order) ? $order['taxes'] : array();
			$c->logMessage(__FUNCTION__,sprintf("*** taxable from preRecalc [%s]", print_r($taxable,true)), 4);
		} else {
			$shipping = 0;
			$handling = 0;
		}
		$amounts['discounted'] = array('value'=>0,'shipping'=>0,'discount_value'=>0,'recurring_discount_value'=>0,'total'=>0,'taxes'=>0,'net'=>0,'lines'=>array(),'taxable'=>array('goods'=>array(),'shipping'=>array()));
		$amounts['undiscounted'] = array('value'=>0,'shipping'=>0,'discount_value'=>0,'recurring_discount_value'=>0,'total'=>0,'taxes'=>0,'net'=>0,'lines'=>array(),'taxable'=>array('goods'=>array(),'shipping'=>array()));
		if (!array_key_exists('coupon_id',$order['header'])) {
			$order['header']['discount_value'] = 0;
			$order['header']['discount_rate'] = 0;
			$order['header']['coupon_id'] = 0;
		}
		$itemCount = 0;
		foreach($order['products'] as $key=>$line) {
			$value += $line['value'];
			$shipping += $line['shipping'];
			$discounts += $line['discount_value'] + $line['recurring_discount_value'];
			$itemCount += $line['quantity'];
			if ($line['discount_value'] == 0 ) {	//&& $line['recurring_discount_value'] == 0) {
				$amounts['undiscounted']['value'] += $line['value'] + $line['discount_value'] + $line['recurring_discount_value'];
				//$amounts['undiscounted']['value'] += $line['value'];
				$amounts['undiscounted']['shipping'] += $line['shipping'];
				$amounts['undiscounted']['discount_value'] += $line['discount_value'];
				$amounts['undiscounted']['recurring_discount_value'] += $line['recurring_discount_value'];
if (!array_key_exists("total",$line)) $c->logMessage(__FUNCTION__,sprintf("missing taxes for [%s]", print_r($line,true)), 1);
				$amounts['undiscounted']['total'] += $line['total'];
				$amounts['undiscounted']['taxes'] += $line['taxes'];
				$amounts['undiscounted']['net'] += $line['value'] + $line['shipping'];
				$amounts['undiscounted']['lines'][$line['line_id']] = array();
			}
			else {
				$amounts['discounted']['value'] += $line['value'] + $line['discount_value'] + $line['recurring_discount_value'];
				//$amounts['discounted']['value'] += $line['value'];
				$amounts['discounted']['shipping'] += $line['shipping'];
				$amounts['discounted']['discount_value'] += $line['discount_value'];
				$amounts['discounted']['recurring_discount_value'] += $line['recurring_discount_value'];
				$amounts['discounted']['total'] += $line['total'];
				$amounts['discounted']['taxes'] += $line['taxes'];
				$amounts['discounted']['net'] += $line['value'] + $line['shipping'] + $line['discount_value'] + $line['recurring_discount_value'];
				$amounts['discounted']['lines'][$line['line_id']] = array();
			}
		}
		$c->logMessage(__FUNCTION__,sprintf("split by discount [%s]",print_r($amounts,true)),4);
		$order['header']['value'] = $value;

		//
		//	adding loyalty points redemption
		//	at this time no ide how the coupons & sales pricing will affect points redemption
		//
		if ($order['header']['points_redeemed'] > 0) {
			$prices = $c->fetchAll(sprintf("select * from product_pricing pr, product p where p.code = '%s' and pr.product_id = p.id order by pr.min_quantity",REDEMPTION_PRODUCT));
			$amt = 0;
			$pts = $order['header']['points_redeemed'];
			foreach($prices as $k=>$v) {
				if ($pts > 0) {
					$net = min($pts,$v["max_quantity"]);
					$amt += $net*$v["price"];
					$pts -= $net;
				}
			}
			if ($pts > 0) $order['header']['points_redeemed'] -= $pts;
			$order['header']['redemption_value'] = $amt;
		}

		$order['header']['line_discounts'] = $discounts;
		if ($itemCount > 0) {
			$tmp = (float)$c->getConfigVar('orderMinShipping');
			if ($tmp > 0 && $shipping < $tmp) $shipping = $tmp;
			$tmp = (float)$c->getConfigVar('orderMaxShipping');
			if ($tmp > 0 && $shipping > $tmp) $shipping = $tmp;
			if (method_exists($config,'calcShipping')) {
				$order = $config->calcShipping($order);
				$shipping = $order['header']['shipping'];
			}
		}
		$order['header']['shipping'] = $shipping;
		$order['header']['handling'] = $handling;
		$order['header']['net'] = $value + $shipping + $discounts + $handling;
		$diff = $amounts['undiscounted']['shipping'] - $shipping;
		$amounts['undiscounted']['shipping'] = $shipping;	// not sure this is 100% correct yet
		$amounts['undiscounted']['net'] += $diff;

		if (array_key_exists('coupon_id',$order['header']))
			$coupon = $c->fetchSingle(sprintf('select * from coupons where id = %d',$order['header']['coupon_id']));
		else
			$coupon = array('shipping_only'=>0);
		$taxable_goods = array();
		$taxable_shipping = array();
		//
		//	taxes - grab the ship to address
		//
		//if (!array_key_exists('taxes',$order)) $order['taxes'] = array();
		$order['taxes'] = array();
		$taxamt = 0;
		//
		//	at this point, the only tax changes should be on the discount we just created
		//
		$address = array();
		if (array_key_exists('id',$order['header']))
			$address = $c->fetchSingle(sprintf('select * from addresses where ownerid = %d and ownertype = "order" and addresstype=%d',$order['header']['id'],ADDRESS_DELIVERY));
		else {
			if (array_key_exists('cart',$_SESSION) && array_key_exists('shipping',$_SESSION['cart']['addresses']))
				$address = $_SESSION['cart']['addresses']['shipping'];
			foreach($_SESSION['cart']['addresses'] as $k=>$v) {
				if (is_array($v) && $v["country_id"] > 1) $address=array();
			}
		}
		if (count($address) > 0 ) {
			$prov = $address['province_id'];
			$taxes = $c->fetchAll(sprintf('select * from taxes where province_id = %d and deleted = 0 and 2=2',$prov));
			foreach($taxes as $tax) {
				if (!array_key_exists($tax['id'],$taxable_goods))
					$taxable_goods[$tax['id']] = 0;
				if (!array_key_exists($tax['id'],$taxable_shipping))
					$taxable_shipping[$tax['id']] = 0;
				if (!array_key_exists($tax['id'],$amounts['undiscounted']['taxable']['goods']))
					$amounts['undiscounted']['taxable']['goods'][$tax['id']] = 0;
				if (!array_key_exists($tax['id'],$amounts['discounted']['taxable']['goods']))
					$amounts['discounted']['taxable']['goods'][$tax['id']] = 0;
				if (!array_key_exists($tax['id'],$amounts['undiscounted']['taxable']['shipping']))
					$amounts['undiscounted']['taxable']['shipping'][$tax['id']] = 0;
				if (!array_key_exists($tax['id'],$amounts['discounted']['taxable']['shipping']))
					$amounts['discounted']['taxable']['shipping'][$tax['id']] = 0;
				foreach($order['products'] as $line) {
					$key = array_key_exists($line['line_id'],$amounts['discounted']['lines']) ? 'discounted' : 'undiscounted';
					if (strpos($line['tax_exemptions'],'|'.$tax['name'].'|') === false) {
						$taxable_goods[$tax['id']] += $line['total'] - $line['shipping'];
						$amounts[$key]['taxable']['goods'][$tax['id']] += $line['total'] - $line['shipping'];
					}
					$taxable_shipping[$tax['id']] += $line['shipping'];
					$amounts[$key]['taxable']['shipping'][$tax['id']] += $line['shipping'];
				}
			}
		}
		else {
			$c->addMessage('No Tax Address found');
			$c->logMessage(__FUNCTION__,sprintf("no tax address found [%s]", print_r($order,true)),1);
			$taxes = array();
		}
		$c->logMessage(__FUNCTION__,sprintf("taxes pre discount goods [%s] shipping [%s] amounts[%s] discount [%f]",print_r($taxable_goods,true),print_r($taxable_shipping,true),print_r($amounts,true),$order['header']['discount_rate']),4);
		if ($diff != 0) {
			foreach($taxable_shipping as $key=>$tax) {
				$taxable_shipping[$key] -= $diff;
			}
			foreach($amounts['undiscounted']['taxable']['shipping'] as $key=>$tax) {
				$amounts['undiscounted']['taxable']['shipping'][$key] -= $diff;
			}
			$c->logMessage(__FUNCTION__,sprintf("taxes adjusted for min/max shipping pre discount goods [%s] shipping [%s] amounts[%s] discount [%f]",print_r($taxable_goods,true),print_r($taxable_shipping,true),print_r($amounts,true),$order['header']['discount_rate']),4);
		}
		if ($order['header']['discount_rate'] != 0) {
			switch($coupon['shipping_only']) {
				case 1:
					if ($order['header']['discount_type'] == 'D') {
						$order['header']['discount_value'] = -min($order['header']['discount_rate'],$amounts['undiscounted']['shipping']);
						$c->logMessage(__FUNCTION__,sprintf("$ shipping discount set to [%f] rate [%f] amount [%d]",$order['header']['discount_value'],$order['header']['discount_rate'],$amounts['undiscounted']['shipping']),4);
						foreach($taxable_shipping as $key=>$line) {
							$taxable_shipping[$key] -= $order['header']['discount_value'];
							$amounts['undiscounted']['taxable']['shipping'][$key]  -= $order['header']['discount_value'];
						}
					}
					else {
						$order['header']['discount_value'] = -round($order['header']['discount_rate']*($amounts['undiscounted']['shipping'])/100,2);
						$c->logMessage(__FUNCTION__,sprintf("%% shipping discount set to [%f] rate [%f] amount [%d]",$order['header']['discount_value'],$order['header']['discount_rate'],$amounts['undiscounted']['shipping']),4);
						foreach($taxable_shipping as $key=>$line) {
							$taxable_shipping[$key] -= round($taxable_shipping[$key]*$order['header']['discount_rate']/100,2);
							$amounts['undiscounted']['taxable']['shipping'][$key]  -= round($amounts['undiscounted']['taxable']['shipping'][$key]*$order['header']['discount_rate']/100,2);
						}
					}
					break;
				case 0:
					if ($order['header']['discount_type'] == 'D') {
						$order['header']['discount_value'] = -min($order['header']['discount_rate'],$amounts['undiscounted']['value']);
						$amounts['undiscounted']['discount_value'] = -min($order['header']['discount_value'],$amounts['undiscounted']['value']);
						$c->logMessage(__FUNCTION__,sprintf("$ discount set to [%f] rate [%f] amount [%d]",$order['header']['discount_value'],$order['header']['discount_rate'],$amounts['undiscounted']['value']),4);
						foreach($taxable_goods as $key=>$line) {
							$taxable_goods[$key] -= $order['header']['discount_value'];
							$amounts['undiscounted']['taxable']['goods'][$key]  -= $order['header']['discount_value'];
						}
					}
					else {
						$order['header']['discount_value'] = round(-$order['header']['discount_rate']*($amounts['undiscounted']['value'])/100,2);
						$amounts['undiscounted']['discount_value'] = -round($order['header']['discount_rate']*($amounts['undiscounted']['value'])/100,2);
						$c->logMessage(__FUNCTION__,sprintf("%% discount set to [%f] rate [%f] amount [%d]",$order['header']['discount_value'],$order['header']['discount_rate'],$amounts['undiscounted']['value']),4);
						foreach($taxable_goods as $key=>$line) {
							$taxable_goods[$key] -= round($taxable_goods[$key]*$order['header']['discount_rate']/100,2);
							$amounts['undiscounted']['taxable']['goods'][$key]  -= round($amounts['undiscounted']['taxable']['goods'][$key]*$order['header']['discount_rate']/100,2);
						}
					}
					break;
			}
		}
		else $order['header']['discount_value'] = 0;
		$c->logMessage(__FUNCTION__,sprintf("taxes post discount goods [%s] shipping [%s] amounts [%s]",print_r($taxable_goods,true),print_r($taxable_shipping,true),print_r($amounts,true)),4);
		$taxamt = 0;
		//$taxable = array();
		$c->logMessage(__FUNCTION__,sprintf("*** tax check taxes [%s] order [%s] taxable [%s]", print_r($taxes,true), print_r($order,true), print_r($taxable,true)),4);
		foreach($taxes as $key=>$tax) {
			$tmp = round(($amounts['undiscounted']['taxable']['goods'][$tax['id']]+$amounts['undiscounted']['taxable']['shipping'][$tax['id']]) * $tax['tax_rate']/100,2) + 
				round(($amounts['discounted']['taxable']['goods'][$tax['id']]+$amounts['discounted']['taxable']['shipping'][$tax['id']] + $handling) * $tax['tax_rate']/100,2);
			$amt = $amounts['undiscounted']['taxable']['goods'][$tax['id']]+$amounts['undiscounted']['taxable']['shipping'][$tax['id']] +
				$amounts['discounted']['taxable']['goods'][$tax['id']]+$amounts['discounted']['taxable']['shipping'][$tax['id']];
			if (!array_key_exists($tax['id'],$taxable)) {
				$c->logMessage(__FUNCTION__,sprintf('init key %d amount %.2f',$tax['id'],$tmp),1);
				$taxable[$tax['id']] = array('tax_amount'=>$tmp,
					'taxable_amount'=>$amt,
					'tax_rate'=>$tax['tax_rate']);
			}
			else {
				$c->logMessage(__FUNCTION__,sprintf('add to key %d amount %.2f',$tax['id'],$tmp),4);
				$taxable[$tax['id']]['tax_amount'] += $tmp;
				$taxable[$tax['id']]['taxable_amount'] += $amounts['undiscounted']['taxable']['goods'][$tax['id']]+$amounts['undiscounted']['taxable']['shipping'][$tax['id']];
			}
			$taxamt += $tmp;
		}
		$c->logMessage(__FUNCTION__,sprintf("net [%f] value [%f] shipping [%f] line discount [%f] discount [%f] taxable [%s] amounts [%s]" , $order['header']['net'], $value, $shipping, $discounts, $order['header']['discount_value'], print_r($taxable,true),print_r($amounts,true)),4);
		//
		//	now add back in the already discounted tax info
		//
		foreach($amounts['discounted']['lines'] as $line_id=>$dummy) {
			foreach($order['taxes'] as $linetax) {
				if ($linetax['line_id'] == $line_id) {
					$taxable[$linetax['tax_id']]['tax_amount'] += $linetax['tax_amount'];
					$taxable[$linetax['tax_id']]['taxable_amount'] += $linetax['taxable_amount'];
					$taxamt += $linetax['tax_amount'];
				}
			}
		}
		$c->logMessage(__FUNCTION__,sprintf("discounted readded back in net [%f] value [%f] shipping [%f] line discount [%f] discount [%f] taxes [%s] amounts [%s]" , $order['header']['net'], $value, $shipping, $discounts, $order['header']['discount_value'], print_r($taxable,true),print_r($amounts,true)),4);
		$order['header']['taxes'] = $taxamt;
		$order['header']['total'] = $order['header']['net'] + $taxamt + $order['header']['discount_value'];
		$order['taxes'] = $taxable;
		if (method_exists($config,'postRecalc')) {
			$order = $config->postRecalc($order);
		}
		$c->logMessage(__FUNCTION__,sprintf("return [%s]",print_r($order,true)),3);
		return $order;
	}
	
	static function getPricing($prod_id, $qty, $order_date) {
		$c = new Common();
		$c->logMessage("getPricing",sprintf("(%d,%d,%s)",$prod_id,$qty,$order_date),3);
		if ($pricing = $c->fetchSingle(sprintf('select * from product_pricing where product_id = %d and min_quantity <= %d and max_quantity >= %d',$prod_id,$qty,$qty))) {
			$prod = $c->fetchSingle(sprintf('select * from product where id = %s',$prod_id));
			if ($prod['sale_startdate'] != '0000-00-00 00:00:00' && $prod['sale_startdate'] <= $order_date && 
				$prod['sale_enddate'] != '0000-00-00 00:00:00' && $prod['sale_enddate'] >= $order_date) {
				$pricing['price'] = $pricing['sale_price'];
			}
			if ($pricing['shipping_type'] == 'E') {
				$pricing['shipping'] = $pricing['shipping']*$qty;
			}
			return $pricing;
		}
		else {
			if ($qty == 0)
				return array('price'=>0,'shipping'=>0);
		}
		return false;
	}

	static function recalcOrderFromDB( $order_id ) {
		$c = new Common(0);
		$c->beginTransaction();
		$cart = array("products"=>array(),"header"=>array(),"addresses"=>array());
		foreach($c->fetchAll(sprintf("select * from order_lines ol where ol.order_id = %d and ol.deleted = 0 order by line_id", $order_id)) as $k=>$v) {
			$key = sprintf("%d|%d|%d|%d", $v["product_id"], $v["options_id"], $v["color"], $v["size"]);
			$cart["products"][$key] = $v;
			$cart["products"][$key]["taxdata"] = $c->fetchAll(sprintf("select * from order_taxes where order_id = %d and line_id = %d", $order_id, $v["line_id"]));
			$cart["products"][$key]["dimensions"] = $c->fetchAll(sprintf("select * from order_lines_dimensions where order_id = %d and line_id = %d", $order_id, $v["line_id"]));
			$cart["products"][$key] = Ecom::lineValue($cart["products"][$key]);
		}
		$cart["header"] = $c->fetchSingle(sprintf("select * from orders where id = %s", $order_id));
		foreach($c->fetchAll(sprintf("select * from addresses where ownertype='order' and ownerid = %d", $order_id)) as $k=>$v) {
			$cart["addresses"][$v["addresstype"]==ADDRESS_PICKUP ? "pickup" : "shipping"] = $v;
		}
		$c->logMessage(__FUNCTION__, sprintf("cart [%s]", print_r($cart,true)), 1);
		$cart = self::recalcOrder($cart);
		$c->execute(sprintf("update orders set value=%f, net = %f, taxes = %f, total = %f where id = %d", $cart["header"]["value"], $cart["header"]["net"], $cart["header"]["taxes"], $cart["header"]["total"], $order_id));
		$c->execute(sprintf("delete from order_taxes where order_id = %d", $order_id));
		foreach($cart["taxes"] as $k=>$v) {
			$c->execute(sprintf("insert into order_taxes(tax_amount, taxable_amount, tax_id, order_id, line_id) values(%f, %f, %d, %d, %d)",
				$v["tax_amount"], $v["taxable_amount"], $k, $order_id, 0));
		}
		foreach($cart["products"] as $k=>$v) {
			$c->execute(sprintf("update order_lines set value=%f, taxes = %f, total = %f where order_id = %d and line_id = %d", $v["value"], $v["taxes"], $v["total"], $order_id, $v["line_id"]));
			foreach($v["taxdata"] as $sk=>$sv) {
			$c->execute(sprintf("insert into order_taxes(tax_amount, taxable_amount, tax_id, order_id, line_id) values(%f, %f, %d, %d, %d)",
				$sv["tax_amount"], $sv["taxable_amount"], $sk, $order_id, $v["line_id"]));
			}
		}
		$c->commitTransaction();
	}
}

class imageResize extends Common {
	var $filename_src;
	var $filename_dest;
	
	function __construct($src) {
		parent::__construct();
		$this->filename_src = $src;
	}
	
	function croppedResize($dest, $width, $height) {
		if ($width <= 0 || $height <= 0)
			$this->logMessage('croppedResize',sprintf('invalid image sizes width [%s] height [%s] this [%s]',$width,$height,print_r($this,true)),1);
		$info = getimagesize($this->filename_src);
		// make the image
		switch ( $info[2] ) {
		case IMAGETYPE_GIF:
			$image = imagecreatefromgif($this->filename_src);
			break;
		case IMAGETYPE_JPEG:
			$image = imagecreatefromjpeg($this->filename_src);
			break;
		case IMAGETYPE_PNG:
			$image = imagecreatefrompng($this->filename_src);
			break;
		default:
			return false;
		}
		// make the the thumbnail
		$thumb = imagecreatetruecolor($width, $height);
		// find out if height or width is larger
		if ( $info[0] > $info[1] ) {
			$dh = $height;
			$dw = ($height * $info[0]) / $info[1];
			$dy = 0;
			$dx = -($dw - $width) / 2;
			//log_msg(get_class($this),"cropped_resize","by height width [$width=>$dw] height [$height=>$dh]",3);
		} else {
			$dw = $width;
			$dh = ($width * $info[1]) / $info[0];
			$dx = 0;
			$dy = -($dh - $height) / 3;
			//log_msg(get_class($this),"cropped_resize","by width width [$width=>$dw] height [$height=>$dh]",3);
		}

		// cast
		$dw = (int) $dw;
		$dh = (int) $dh;
		$dx = (int) $dx;
		$dy = (int) $dy;
		
		// resample the image into the thumbnail
		imagecopyresampled($thumb, $image, $dx, $dy, 0, 0, $dw, $dh, $info[0], $info[1]);
		
		// write out the thumbnail
		switch ( $info[2] ) {
		case IMAGETYPE_GIF:
			imagegif($thumb, $this->filename_dest);
			break;
		case IMAGETYPE_JPEG:
			imagejpeg($thumb, $this->filename_dest, 99);
			break;
		case IMAGETYPE_PNG:
			imagepng($thumb, $this->filename_dest,9);
			break;
		default:
			return false;
		}
		// chmod
		chmod($this->filename_dest, 0750);
		return true;
	}
	
	function resize($dest, $width = 0, $height = 0, $proportional = true, $crop = false, $use_linux_command = false ) {
		// set the destination
		$this->filename_dest = $dest;
		// handle cropping
		if ( $crop === TRUE ) return $this->croppedResize($dest, $width, $height);
		// check for file_exists on source
		if ( !file_exists($this->filename_src) ) return false;
		if ( $height <= 0 && $width <= 0 ) return false;
		$info = getimagesize($this->filename_src);
		$image = '';
		$final_width = 0;
		$final_height = 0;
		list($width_old, $height_old) = $info;
		if ($proportional) {
			$proportion = $width_old / $height_old;
			if ( $width_old > $height_old && $width != 0 ) {
				$final_width = $width;
				$final_height = $final_width / $proportion;
			}
			elseif ( $width_old < $height_old && $height != 0 ) {
				$final_height = $height;
				$final_width = $final_height * $proportion;
			}
			elseif ( $width == 0 ) {
				$final_height = $height;
				$final_width = $final_height * $proportion;
			}
			elseif ( $height == 0) {
				$final_width = $width;
				$final_height = $final_width / $proportion;
			}
			else {
				$final_width = $width;
				$final_height = $height;
			}
		}
		else {
			$final_width = ( $width <= 0 ) ? $width_old : $width;
			$final_height = ( $height <= 0 ) ? $height_old : $height;
		}
		switch ( $info[2] ) {
		case IMAGETYPE_GIF:
			$image = imagecreatefromgif($this->filename_src);
			break;
		case IMAGETYPE_JPEG:
			$image = imagecreatefromjpeg($this->filename_src);
			break;
		case IMAGETYPE_PNG:
			$image = imagecreatefrompng($this->filename_src);
			break;
		default:
			return false;
		}
		$image_resized = imagecreatetruecolor( $final_width, $final_height );
		imagecolortransparent($image_resized, imagecolorallocate($image_resized, 0, 0, 0) );
		imagealphablending($image_resized, false);
		imagesavealpha($image_resized, true);
		imagecopyresampled($image_resized, $image, 0, 0, 0, 0, $final_width, $final_height, $width_old, $height_old);
		switch ( $info[2] ) {
		case IMAGETYPE_GIF:
			imagegif($image_resized, $this->filename_dest);
			break;
		case IMAGETYPE_JPEG:
			imagejpeg($image_resized, $this->filename_dest, 99);
			break;
		case IMAGETYPE_PNG:
			imagepng($image_resized, $this->filename_dest, 9);
			break;
		default:
			return false;
		}
		return true;
	}	
}

class myMailer extends PHPMailer {
	function __construct() {
		parent::__construct();
		if (array_key_exists('mail',$GLOBALS)) {
			$this->SMTPDebug = array_key_exists('debug',$GLOBALS['mail']) ? $GLOBALS['mail']['debug'] : 0;
			if (array_key_exists('user',$GLOBALS['mail'])) {
				$this->Username = $GLOBALS['mail']['user'];
				$this->Host = $GLOBALS['mail']['host'];
				if (array_key_exists("port",$GLOBALS["mail"])) $this->Port = $GLOBALS['mail']['port'];
				$this->Password= $GLOBALS['mail']['password'];
				$this->AuthType = $GLOBALS["mail"]["authtype"];
				$this->SMTPAuth = true;
			}
			if ($GLOBALS['mail']['type'] == 'smtp') {
				$this->isSMTP();
			}
			else {
				$this->isMail();
			}
		}
		else {
			if (MAILTYPE == 'smtp') {
				$this->isSMTP();
			}
			else $this->isMail();
		}
		$this->SMTPAutoTLS = false;
		$this->SMTPSecure = false;
	}
}

?>
