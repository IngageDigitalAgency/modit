<?php

require_once (ADMIN.'classes/common.php');

/**
 * Main Class for website frontend functionality
 */
class Frontend extends Common {

	protected $m_templates = array();
	protected $m_fields = array();
	protected $m_classIds = array();

    /**
     * @param $init
     * @throws Exception
     */
    public function __construct($init = false) {
		parent::__construct ($init);
		if (strlen($e = $this->getConfigVar("errorHandling")) > 0) {
			error_reporting(eval(sprintf("return (%s);",$e)));
		}
		$sessionTime = (int)$this->getConfigVar('session-timeout');
		$sessionTime = $sessionTime == 0 ? 60*15:$sessionTime;
		if (array_key_exists('timeout',$_SESSION)) {
			$session_life = time() - $_SESSION['timeout'];
			if($session_life > $sessionTime) {
				$this->logout();
				if (array_key_exists("cart",$_SESSION)) unset($_SESSION["cart"]);
			}
		}
		if (array_key_exists('logout',$_REQUEST) && $_REQUEST['logout'] == 'logout') {
			$this->logout();
			header("Location:/");
			exit;
		}
		$_SESSION['timeout'] = time();
		$modules = $this->fetchAll('select * from modules where enabled = 1 and frontend = 1');
		if ($init) {
			foreach($modules as $module) {
				require_once (sprintf('%sfrontend/modules/%s.php',ADMIN,$module['classname']));
				$this->m_classIds[$module['classname']] = $module['id'];
			}
			$GLOBALS['globals']->setConfig(new custom(0));
		}
		$GLOBALS["g_secure"] = $this->getConfigVar("globalSecure");
		foreach($modules as $module) {
			$this->m_classIds[$module['classname']] = $module['id'];
		}
		$this->config = $GLOBALS['globals']->getConfig();
		$this->M_DIR = ADMIN.'frontend/';
		$this->setTemplates(
			array(
				'getModuleInfo'=>$this->M_DIR.'forms/getModuleInfo.html',
				'getFileList'=>$this->M_DIR.'forms/getFileList.html',
				'changeModule'=>$this->M_DIR.'forms/changeModule.html',
				'moduleWrapper'=>$this->M_DIR.'forms/moduleWrapper.html',
				'editorForm'=>$this->M_DIR.'forms/editorForm.html',
				'tinymceEditor'=>$this->M_DIR.'forms/tinymceEditor.html',
				'tinymceEditing'=>$this->M_DIR.'forms/tinymceEditing.html',
				'tinymceResult'=>$this->M_DIR.'forms/tinymceResult.html',
				'pageEditorForm'=>$this->M_DIR.'forms/pageEditorForm.html',
				'templateSuccess'=>$this->M_DIR.'forms/templateSuccess.html'
			)
		);
		$this->setFields(
			array(
				'tinymceResult'=>array(
					'content'=>array('type'=>'tag','reformatting'=>false)
				),
				'tinymceEditing'=>array(
					'content'=>array('type'=>'tag','reformatting'=>true),
					'saveContent'=>array('type'=>'hidden','value'=>1)
				),
				'tinymceEditor'=>array(
					'content'=>array('type'=>'tag','reformatting'=>false)
				),
				'getModuleInfo'=>array(
					'functions'=>array('type'=>'select','name'=>'module_function')
				),
				'getFileList'=>array(
					'files'=>array('type'=>'select','name'=>'file_list')
				),
				'changeModule'=>array(
					'module_id'=>array('type'=>'select','name'=>'module_id','sql'=>'select id, title from modules where enabled = 1 and frontend = 1 order by 2'),
					'state'=>array('type'=>'select','name'=>'state','lookup'=>'loggedInState'),
					'module_name'=>array('type'=>'hidden'),
					'page_id'=>array('type'=>'hidden','database'=>false),
					'page_type'=>array('type'=>'hidden','database'=>false),
					'fetemplate_id'=>array('type'=>'select','required'=>true),
					'folder_id'=>array('type'=>'select','reformatting'=>false),
					'include_subfolders'=>array('type'=>'checkbox','value'=>1),
					'allow_override'=>array('type'=>'checkbox','value'=>1),
					'content_area'=>array('type'=>'checkbox','value'=>1,'onclick'=>'fnSetContentState(this);'),
					'changeModule'=>array('type'=>'hidden','database'=>false,'value'=>0),
					'content'=>array('type'=>'textarea'),
					'query_linked'=>array('type'=>'checkbox','value'=>1),
					'reset'=>array('type'=>'hidden','value'=>0,'database'=>false)
				)
			)
		);
	}

	/**
	 * @return void
	 * @throws phpmailerException
	 */
	function logout() {
		$_SESSION = array_key_exists('administrator',$_SESSION) ? array('administrator'=>$_SESSION['administrator']) : array();
		$this->logMessage(__FUNCTION__,sprintf('logged out session [%s]',print_r($_SESSION,true)),1);
	}

    /**
     * @return string|void
     * @throws phpmailerException
     */
    function show() {
		$GLOBALS['secureLink'] = 0;
		if (method_exists('custom','initHook')) {
			$this->logMessage(__FUNCTION__,sprintf('calling initHook'),1);
			$init = new custom(0);
			$init->initHook();
		}
		$st = explode(' ',microtime());
#		if (get_magic_quotes_gpc()) {
#			$this->processSlashes();
#		}
		if (count($_GET) > 0) {
			$this->logMessage(__FUNCTION__,sprintf('GET [%s]',print_r($_GET,1)),1);
		}
		if (count($_POST) > 0) {
			$this->logMessage(__FUNCTION__,sprintf('POST [%s]',print_r($_POST,1)),1);
		}
		if (count($_REQUEST) > 0) {
			$this->logMessage(__FUNCTION__,sprintf('REQUEST [%s]',print_r($_REQUEST,1)),1);
		}
		if (count($_FILES) > 0) {
			$this->logMessage(__FUNCTION__,sprintf('FILES [%s]',print_r($_FILES,1)),1);
		}
		if (count($_SESSION) > 0) {
			$this->logMessage(__FUNCTION__,sprintf('SESSION [%s]',print_r($_SESSION,1)),1);
		}
		if (count($_SERVER) > 0) {
			$this->logMessage(__FUNCTION__,sprintf('SERVER [%s]',print_r($_SERVER,1)),4);
			if (array_key_exists('HTTP_REFERER',$_SERVER)) {
				$tmp = $this->getConfigVar('searchEngines');
				$adServers = array();
				$ret = array();
				if (strlen($tmp) > 0) {
					$list = explode("^",$tmp);
					foreach($list as $pair) {
						$tmp = explode(":",$pair);
						$adServers[] = array("source"=>$tmp[0],"query"=>$tmp[1]);
					}
				}
				$this->logMessage(__FUNCTION__,sprintf('search engine parms [%s] from [%s]',print_r($adServers,true),print_r($tmp,true)),2);
				$source = explode('?',$_SERVER['HTTP_REFERER']);
				foreach($adServers as $server) {
					$this->logMessage(__FUNCTION__,sprintf('looking for [%s] in [%s] pos [%s]',$server['source'],$source[0],strpos($source[0],$server['source'])),5);
					if (strpos($source[0],$server['source']) !== false) {
						$this->logMessage(__FUNCTION__,sprintf('located search from [%s]',$source[0]),5);
						$opts = explode('&',$source[1]);
						foreach($opts as $option) {
							$query = explode('=',$option);
							$this->logMessage(__FUNCTION__,sprintf('looking for [%s] in [%s]',$server['query'],print_r($query,true)),5);
							if ($option[0] == $server['query'] && count($query) > 1) {
								$this->setQueryOptions($query[1]);
								$this->logMessage(__FUNCTION__,sprintf('query string is [%s] value [%s] winner [%s]',$option[0],$query[1],$this->m_searchWinner),2);
							}
						}
					}
				}
			}
		}
		if (DEFINED('FRONTEND')) {
			$status = false;
			//if (array_key_exists('REDIRECT_URL',$_SERVER)) {
				$defunct = $this->fetchAll('select * from traffic_301');
				foreach($defunct as $rec) {
					if (preg_match($rec['url'],$_SERVER['REDIRECT_URL'])) {
						ob_clean();
						header(sprintf('Location:%s',$rec['redirect']),false,$rec['status']);
						exit();
					}
				}
				$ignored = $this->fetchAll('select * from traffic_ignored_urls');
				foreach($ignored as $rec) {
					$status = $status || preg_match($rec['url'],$_SERVER['REDIRECT_URL']);
				}
			//}
			if (!$status) {
				$stmt = $this->prepare(sprintf('insert into traffic(ip_address,agent,url,query_string,request) values(?,?,?,?,?)'));
				$stmt->bindParams(array('sssss',$_SERVER['REMOTE_ADDR'],$_SERVER['HTTP_USER_AGENT'],array_key_exists('REDIRECT_URL',$_SERVER)?$_SERVER['REDIRECT_URL']:'',$_SERVER['QUERY_STRING'],print_r($_SERVER['REQUEST_URI'],true)));
				$stmt->execute();
			}
		}
		$renderType = 'page';
		$page = array();
		if ((array_key_exists('addToCart',$_REQUEST) && $_REQUEST['addToCart'] > 0) || (array_key_exists('updateCart',$_REQUEST) && $_REQUEST['updateCart'] > 0)) {
			//
			//	an update to the ecom cart
			//
			$ecom = new Ecom();
			$ecom->updateCart();
		}
		if (array_key_exists('ajax',$_REQUEST)) {
			$ajax = new feAjax();
			echo $ajax->show();
			exit;
		}
		if (array_key_exists('siteSearchForm',$_REQUEST) && $_REQUEST['siteSearchForm'] == 1 && array_key_exists('siteSearchText',$_REQUEST)) {
			$_REQUEST['module'] = 'search';
		}
		if (array_key_exists('module',$_REQUEST)) {
			switch($_REQUEST['module']) {
				case 'tracker':
					//
					//	special case - reserved for newsletter tracking
					//
					$obj = new newsletter(array());
					return $obj->tracker();
					break;
				case 'page':
					$name = preg_replace('#[^a-z0-9]#i', '-',strtolower($_REQUEST['id']));
					if (strlen($name) > 0)
						$sql = sprintf('select c.*, t.html, p.id as page_id, p.content from pages p, content c, templates t where p.deleted = 0 and c.enabled = 1 and c.published = 1 and t.deleted = 0 and t.enabled = 1 and (c.search_title = "%s" or c.seo_url = "%s") and p.version = (select max(version) from pages p2 where p2.deleted = 0 and p2.content_id = p.content_id) and c.id = p.content_id and t.template_id = p.template_id and t.version = (select max(version) from templates t1 where t1.template_id = p.template_id)',$name,$name);
					else
						$sql = sprintf('select c.*, t.html, p.id as page_id, p.content from pages p, content c, templates t where p.deleted = 0 and c.enabled = 1 and c.published = 1 and  t.deleted = 0 and t.enabled = 1 and default_page = 1 and p.version = (select max(version) from pages p2 where p2.deleted = 0 and p2.content_id = p.content_id) and c.id = p.content_id and t.template_id = p.template_id and t.version = (select max(version) from templates t1 where t1.template_id = p.template_id)',$name,$name);
					if (!$page = $this->fetchSingle($sql)) {
						if ($link = $this->fetchSingle(sprintf("select * from content where enabled = 1 and published = 1 and type = 'internallink' and (search_title = '%s' or seo_url = '%s')",$name,$name))) {
							$sql = sprintf('select c.*, t.html, p.id as page_id, p.content from pages p, content c, templates t where p.deleted = 0 and c.enabled = 1 and c.published = 1 and t.deleted = 0 and t.enabled = 1 and c.id = %d and p.version = (select max(version) from pages p2 where p2.deleted = 0 and p2.content_id = p.content_id) and c.id = p.content_id and t.template_id = p.template_id and t.version = (select max(version) from templates t1 where t1.template_id = p.template_id)',$link['internal_link']);
							$page = $this->fetchSingle($sql);
						}
					}
					$this->setPageName($name);
					$this->logMessage(__FUNCTION__,sprintf("menu sql [%s] data [%s] pageName [%s]",$sql,print_r($page,true),$name),4);
					break;
				case 'menu':
					$sql = sprintf('select c.*, t.html, p.id as page_id, p.content from pages p, content c, templates t where t.deleted = 0 and t.enabled = 1 and p.content_id = %d and p.version = (select max(version) from pages p2 where p2.deleted = 0 and p2.content_id = p.content_id) and c.id = p.content_id and t.template_id = p.template_id and t.version = (select max(version) from templates t1 where t1.template_id = p.template_id)',$_REQUEST['id']);
					$page = $this->fetchSingle($sql);
					$this->logMessage(__FUNCTION__,sprintf("menu sql [%s] data [%s]",$sql,print_r($page,true)),4);
					break;
				case 'advert':
					$this->execute(sprintf('update advert set clicks = clicks + 1 where id = %d',$_REQUEST['advert_id']));
					$data = $this->fetchSingle(sprintf('select * from advert where id = %d',$_REQUEST['advert_id']));
					header(sprintf('Location: %s',$data['url']));
					exit;
					break;
				case 'news':
					//
					//	grab the most current news template, no content or pages involved here
					//
					$sql = 'SELECT t.*, 0 as page_id FROM `templates` t WHERE t.deleted = 0 and t.enabled = 1 and t.special_processing = "news" and t.version = (select max(version) from templates t1 where t1.template_id = t.template_id)';
					$page = $this->fetchSingle($sql);
					if (array_key_exists('f_id',$_REQUEST) && $_REQUEST['f_id'] > 0) {
						$tmpsql = sprintf("(select n2.* from news_folders n1, news_folders n2 where n1.id = %d and n2.level < n1.level and n2.left_id < n1.left_id and n2.right_id > n1.right_id and n2.template_id > 0)
union (select * from news_folders where id = %d and template_id > 0) order by level desc limit 1",$_REQUEST['f_id'],$_REQUEST['f_id']);
						if ($folder = $this->fetchSingle($tmpsql)) {
							$this->logMessage(__FUNCTION__,sprintf('news folder template test [%s] sql [%s]',print_r($folder,true),$tmpsql),2);
							$sql = sprintf('SELECT t.*, 0 as page_id FROM `templates` t WHERE t.deleted = 0 and t.enabled = 1 and t.template_id = %d and t.version = (select max(version) from templates t1 where t1.template_id = t.template_id)',$folder['template_id']);
							$page = $this->fetchSingle($sql);
						}
					}
					else {
						if ($folder = $this->fetchSingle(sprintf('select f.* from news_folders f, news_by_folder i where i.article_id = %d and f.id = i.folder_id order by rand() limit 1',array_key_exists('news_id',$_REQUEST) ? $_REQUEST['news_id'] : 0))) {
							$this->logMessage(__FUNCTION__,sprintf('news folder article test [%s]',print_r($folder,true)),2);
							if ($folder['template_id'] != 0) {
								$sql = sprintf('SELECT t.*, 0 as page_id FROM `templates` t WHERE t.deleted = 0 and t.enabled = 1 and t.template_id = %d and t.version = (select max(version) from templates t1 where t1.template_id = t.template_id)',$folder['template_id']);
								$page = $this->fetchSingle($sql);						
							}
						}
					}
					$renderType = 'template';
					break;
				case 'newscat':
					//
					//	grab the most current news template, no content or pages involved here
					//
					$sql = 'SELECT t.*, 0 as page_id FROM `templates` t WHERE t.deleted = 0 and t.enabled = 1 and t.special_processing = "newscat" and t.version = (select max(version) from templates t1 where t1.template_id = t.template_id)';
					$page = $this->fetchSingle($sql);
					$renderType = 'template';
					break;
				case 'storecat':
					//
					//	grab the most current news template, no content or pages involved here
					//
					$sql = 'SELECT t.*, 0 as page_id FROM `templates` t WHERE t.deleted = 0 and t.enabled = 1 and t.special_processing = "storecat" and t.version = (select max(version) from templates t1 where t1.template_id = t.template_id)';
					$page = $this->fetchSingle($sql);
					$renderType = 'template';
					break;
				case 'blogcategory':
					//
					//	grab the most current news template, no content or pages involved here
					//
					$sql = 'SELECT t.*, 0 as page_id FROM `templates` t WHERE t.deleted = 0 and t.enabled = 1 and t.special_processing = "blogcategory" and t.version = (select max(version) from templates t1 where t1.template_id = t.template_id)';
					$page = $this->fetchSingle($sql);
					$renderType = 'template';
					break;
				case 'category':
					//
					//	grab the most current category template, no content or pages involved here
					//
					$sql = 'SELECT t.*, 0 as page_id FROM `templates` t WHERE t.deleted = 0 and t.enabled = 1 and t.special_processing = "category" and t.version = (select max(version) from templates t1 where t1.template_id = t.template_id)';
					$page = $this->fetchSingle($sql);
					if (array_key_exists('cat_id',$_REQUEST) && $_REQUEST['cat_id'] > 0) {
						$cat = $this->fetchScalar(sprintf("select template_id from product_folders where id = %d",$_REQUEST["cat_id"]));
						if ( $cat > 0) {
							$sql = sprintf('SELECT t.*, 0 as page_id FROM `templates` t WHERE t.deleted = 0 and t.enabled = 1 and t.template_id = %d and t.version = (select max(version) from templates t1 where t1.template_id = t.template_id)',$cat);
							$page = $this->fetchSingle($sql);
						}
					}
					$renderType = 'template';
					break;
				case 'product':
					//
					//	grab the most current news template, no content or pages involved here
					//
					$sql = 'SELECT t.*, 0 as page_id FROM `templates` t WHERE t.deleted = 0 and t.enabled = 1 and t.special_processing = "product" and t.version = (select max(version) from templates t1 where t1.template_id = t.template_id and t1.deleted = 0)';
					$page = $this->fetchSingle($sql);
					if (array_key_exists('f_id',$_REQUEST) && $_REQUEST['f_id'] > 0) {
						$tmpsql = sprintf("(select p2.* from product_folders p1, product_folders p2 where p1.id = %d and p2.level < p1.level and p2.left_id < p1.left_id and p2.right_id > p1.right_id and p2.details_id > 0)
union (select * from product_folders where id = %d and details_id > 0) order by level desc limit 1",$_REQUEST['f_id'],$_REQUEST['f_id']);
						if ($folder = $this->fetchSingle($tmpsql)) {
							$this->logMessage(__FUNCTION__,sprintf('product folder template test [%s] sql [%s]',print_r($folder,true),$tmpsql),2);
							$sql = sprintf('SELECT t.*, 0 as page_id FROM `templates` t WHERE t.deleted = 0 and t.enabled = 1 and t.template_id = %d and t.version = (select max(version) from templates t1 where t1.template_id = t.template_id)',$folder['details_id']);
							$page = $this->fetchSingle($sql);
						}
					}
					else {
						if ($folder = $this->fetchSingle(sprintf('select f.* from product_folders f, product_by_folder i where i.product_id = %d and f.id = i.folder_id order by rand() limit 1',array_key_exists('prod_id',$_REQUEST) ? $_REQUEST['prod_id'] : 0))) {
							$this->logMessage(__FUNCTION__,sprintf('product folder article test [%s]',print_r($folder,true)),2);
							if ($folder['details_id'] != 0) {
								$sql = sprintf('SELECT t.*, 0 as page_id FROM `templates` t WHERE t.deleted = 0 and t.enabled = 1 and t.template_id = %d and t.version = (select max(version) from templates t1 where t1.template_id = t.template_id)',$folder['details_id']);
								$page = $this->fetchSingle($sql);
							}
						}
					}
					$renderType = 'template';
					break;

				case 'calendar':
					$sql = 'SELECT t.*, 0 as page_id FROM `templates` t WHERE t.deleted = 0 and t.enabled = 1 and t.special_processing = "calendar" and t.version = (select max(version) from templates t1 where t1.template_id = t.template_id and t1.deleted = 0)';
					$page = $this->fetchSingle($sql);
					if (array_key_exists('f_id',$_REQUEST) && $_REQUEST['f_id'] > 0) {
						$tmpsql = sprintf("(select p2.* from members_folders p1, members_folders p2 where p1.id = %d and p2.level < p1.level and p2.left_id < p1.left_id and p2.right_id > p1.right_id and p2.template_id > 0)
union (select * from members_folders where id = %d and template_id > 0) order by level desc limit 1",$_REQUEST['f_id'],$_REQUEST['f_id']);
						if ($folder = $this->fetchSingle($tmpsql)) {
							$this->logMessage(__FUNCTION__,sprintf('calendar folder template test [%s] sql [%s]',print_r($folder,true),$tmpsql),2);
							$sql = sprintf('SELECT t.*, 0 as page_id FROM `templates` t WHERE t.deleted = 0 and t.enabled = 1 and t.template_id = %d and t.version = (select max(version) from templates t1 where t1.template_id = t.template_id)',$folder['template_id']);
							$page = $this->fetchSingle($sql);
						}
					}
					else {
						if ($folder = $this->fetchSingle(sprintf('select f.* from members_folders f, events_by_folder i where i.event_id = %d and f.id = i.folder_id order by rand() limit 1',array_key_exists('event_id',$_REQUEST) ? $_REQUEST['event_id'] : 0))) {
							$this->logMessage(__FUNCTION__,sprintf('calendar folder article test [%s]',print_r($folder,true)),2);
							if ($folder['template_id'] != 0) {
								$sql = sprintf('SELECT t.*, 0 as page_id FROM `templates` t WHERE t.deleted = 0 and t.enabled = 1 and t.template_id = %d and t.version = (select max(version) from templates t1 where t1.template_id = t.template_id)',$folder['template_id']);
								$page = $this->fetchSingle($sql);
							}
						}
					}
					$renderType = 'template';
					break;

				case 'event':
					$sql = 'SELECT t.*, 0 as page_id FROM `templates` t WHERE t.deleted = 0 and t.enabled = 1 and t.special_processing = "event" and t.version = (select max(version) from templates t1 where t1.template_id = t.template_id and t1.deleted = 0)';
					$page = $this->fetchSingle($sql);
					$renderType = 'template';
					break;
				case 'gallery':
					$sql = 'SELECT t.*, 0 as page_id FROM `templates` t WHERE t.deleted = 0 and t.enabled = 1 and t.special_processing = "gallery" and t.version = (select max(version) from templates t1 where t1.template_id = t.template_id and t1.deleted = 0)';
					$page = $this->fetchSingle($sql);
					$renderType = 'template';
					break;
				case 'store':
					$sql = 'SELECT t.*, 0 as page_id FROM `templates` t WHERE t.deleted = 0 and t.enabled = 1 and t.special_processing = "store" and t.version = (select max(version) from templates t1 where t1.template_id = t.template_id and t1.deleted = 0)';
					$page = $this->fetchSingle($sql);
					if (array_key_exists('storecat',$_REQUEST) && $_REQUEST['storecat'] > 0) {
						$tmpsql = sprintf("(select n2.* from store_folders n1, store_folders n2 where n1.id = %d and n2.level < n1.level and n2.left_id < n1.left_id and n2.right_id > n1.right_id and n2.template_id > 0)
union (select * from store_folders where id = %d and template_id > 0) order by level desc limit 1",$_REQUEST['storecat'],$_REQUEST['storecat']);
						if ($folder = $this->fetchSingle($tmpsql)) {
							$this->logMessage('show',sprintf('store folder template test [%s] sql [%s]',print_r($folder,true),$tmpsql),2);
							$sql = sprintf('SELECT t.*, 0 as page_id FROM `templates` t WHERE t.deleted = 0 and t.enabled = 1 and t.template_id = %d and t.version = (select max(version) from templates t1 where t1.template_id = t.template_id)',$folder['template_id']);
							$page = $this->fetchSingle($sql);
						}
					}
					else {
						if ($folder = $this->fetchSingle(sprintf('select f.* from store_folders f, stores_by_folder i where i.store_id = %d and f.id = i.folder_id order by rand() limit 1',array_key_exists('store_id',$_REQUEST) ? $_REQUEST['store_id'] : 0))) {
							$this->logMessage('show',sprintf('store folder test [%s]',print_r($folder,true)),2);
							if ($folder['template_id'] != 0) {
								$sql = sprintf('SELECT t.*, 0 as page_id FROM `templates` t WHERE t.deleted = 0 and t.enabled = 1 and t.template_id = %d and t.version = (select max(version) from templates t1 where t1.template_id = t.template_id)',$folder['template_id']);
								$page = $this->fetchSingle($sql);						
							}
						}
					}
					$renderType = 'template';
					break;
				case 'member':
					$sql = 'SELECT t.*, 0 as page_id FROM `templates` t WHERE t.deleted = 0 and t.enabled = 1 and t.special_processing = "member" and t.version = (select max(version) from templates t1 where t1.template_id = t.template_id and t1.deleted = 0)';
					$page = $this->fetchSingle($sql);
					$renderType = 'template';
					break;
				case 'profile':
					$sql = 'SELECT t.*, 0 as page_id FROM `templates` t WHERE t.deleted = 0 and t.enabled = 1 and t.special_processing = "profile" and t.version = (select max(version) from templates t1 where t1.template_id = t.template_id and t1.deleted = 0)';
					$page = $this->fetchSingle($sql);
					$renderType = 'template';
					break;
				case 'search':
					$sql = 'SELECT t.*, 0 as page_id FROM `templates` t WHERE t.deleted = 0 and t.enabled = 1 and t.special_processing = "search" and t.version = (select max(version) from templates t1 where t1.template_id = t.template_id)';
					$page = $this->fetchSingle($sql);
					$renderType = 'template';
					break;
				case 'blog':
					$sql = 'SELECT t.*, 0 as page_id FROM `templates` t WHERE t.deleted = 0 and t.enabled = 1 and t.special_processing = "blog" and t.version = (select max(version) from templates t1 where t1.template_id = t.template_id)';
					$page = $this->fetchSingle($sql);
					$renderType = 'template';
					break;
				case 'blogcat':
					$sql = 'SELECT t.*, 0 as page_id FROM `templates` t WHERE t.deleted = 0 and t.enabled = 1 and t.special_processing = "blogcat" and t.version = (select max(version) from templates t1 where t1.template_id = t.template_id)';
					$page = $this->fetchSingle($sql);
					$renderType = 'template';
					break;
				case 'rss':
					$sql = 'SELECT t.*, 0 as page_id FROM `templates` t WHERE t.deleted = 0 and t.enabled = 1 and t.special_processing = "rss" and t.version = (select max(version) from templates t1 where t1.template_id = t.template_id)';
					$page = $this->fetchSingle($sql);
					$renderType = 'template';
					break;
				case 'rsscat':
					$sql = 'SELECT t.*, 0 as page_id FROM `templates` t WHERE t.deleted = 0 and t.enabled = 1 and t.special_processing = "rsscat" and t.version = (select max(version) from templates t1 where t1.template_id = t.template_id)';
					$page = $this->fetchSingle($sql);
					$renderType = 'template';
					break;
				case 'artist':
					$sql = 'SELECT t.*, 0 as page_id FROM `templates` t WHERE t.deleted = 0 and t.enabled = 1 and t.special_processing = "artist" and t.version = (select max(version) from templates t1 where t1.template_id = t.template_id and t1.deleted = 0)';
					$page = $this->fetchSingle($sql);
					$renderType = 'template';
					break;
				default:
					$sql = '';
					break;
			}
			if (!is_array($page) || count($page) == 0) {
				$this->logMessage(__FUNCTION__,sprintf("couldn't locate page [%s] redirect to home [%s]",print_r($_REQUEST,true),$sql),1);
				$sql = 'select c.*, t.html, p.id as page_id, p.content from pages p, content c, templates t where t.deleted = 0 and t.enabled = 1 and c.default_page = 1 and p.version = (select max(version) from pages p2 where p2.deleted = 0 and p2.content_id = p.content_id) and c.id = p.content_id and t.template_id = p.template_id and t.version = (select max(t1.version) from templates t1 where t1.template_id = p.template_id)';
				$page = $this->fetchSingle($sql);
				$renderType = 'page';
			}
		}
		else {
			if ($page = $this->fetchSingle('select c.*, t.html, p.id as page_id, p.content from pages p, content c, templates t where t.deleted = 0 and t.enabled = 1 and p.content_id = (select c.id from content c where c.default_page = 1) and p.version = (select max(version) from pages p2 where p2.deleted = 0 and p2.content_id = p.content_id) and c.id = p.content_id and t.template_id = p.template_id and t.version = (select max(version) from templates t1 where t1.template_id = p.template_id)')) {
				$this->setPageName(strlen($page['seo_url']) > 0 ? $page['seo_url'] : $page['search_title']);
				$renderType = 'page';
			}
		}
		$page['html'] = str_replace('%%basetag%%',sprintf('<base href="http://%s" />',HOSTNAME),$page['html']);
//		if (array_key_exists('secure',$page)) {
			if (((array_key_exists('secure',$page) && $page['secure'] != 0) || $GLOBALS["g_secure"]) && (!array_key_exists('HTTPS',$_SERVER) || $_SERVER['HTTPS'] != 'on')) {
				$url = sprintf('https://%s%s',HOSTNAME,$_SERVER['REQUEST_URI']);
				$this->logMessage(__FUNCTION__,sprintf('redirecting to secure version of the page [%s]',$url),1);
				header('Location: '.$url);
				exit;
			}
			elseif (((array_key_exists('secure',$page) && $page['secure'] == 0) || $GLOBALS["g_secure"] == 0) && array_key_exists('HTTPS',$_SERVER) && $_SERVER['HTTPS'] == 'on' && !$GLOBALS["g_secure"]) {
				$url = sprintf('http://%s%s',HOSTNAME,$_SERVER['REQUEST_URI']);
				$this->logMessage(__FUNCTION__,sprintf('redirecting to non-secure version of the page [%s]',$url),1);
				header('Location: '.$url);
				exit;
			}
			$GLOBALS['secureLink'] = (array_key_exists('secure',$page) && $page['secure']) || $GLOBALS["g_secure"];
		//}
		if ($renderType == 'page') {
			$tmp = $this->renderPage($page);
		}
		else
			$tmp = $this->renderTemplate($page);
		$tmp = $this->processHeader($tmp);
		$et = explode(' ',microtime());
		return $tmp.sprintf('<!-- render time is %f seconds -->',$et[1] - $st[1] + $et[0] - $st[0]);
	}

	/**
	 * Destructor
	 */
	function __destruct() {
		$this->m_globals = null;
		parent::__destruct();
	}

    /**
     * @param $name
     * @return mixed|string
     * @throws phpmailerException
     */
    protected function getTemplate($name) {
		if (array_key_exists($name,$this->m_templates))
			return $this->m_templates[$name];	
		else {
			$this->logMessage(__FUNCTION__,sprintf('Invalid template request [%s], this [%s]', $name, print_r($this,true)),1,true);
			return '';
		}
	}

    /**
     * @param $name
     * @return array|mixed
     */
    protected function getFields($name) {
		if (array_key_exists($name,$this->m_fields))
			return $this->m_fields[$name];
		else return array();
	}

    /**
     * @param $templates
     * @return void
     */
    protected function setTemplates($templates) {
		$this->m_templates = $templates;
	}

    /**
     * @param $fields
     * @return void
     */
    protected function setFields($fields) {
		$this->m_fields = $fields;
	}

	/**
	 * @param $function
	 * @return bool|void
	 * @throws phpmailerException
	 */
    protected function hasAccess($function) {
		if (strcmp($function,'login')) return true;
		if (!$this->isLoggedIn()) return false;
	}

    /**
     * @return array
     */
    protected function getModules() {
		return array();
	}

    /**
     * @return bool
     */
    function isUserLoggedIn() {
		return (int)$this->getUserInfo('id') > 0;
	}

    /**
     * @param bool $admin
     * @return bool|mixed
     * @throws phpmailerException
     */
    protected function isLoggedIn($admin = false) {
		if ($admin && array_key_exists('administrator', $_SESSION)) {
			if (array_key_exists('status',$_SESSION['administrator']))
			return ($_SESSION['administrator']['status']);
		}
		elseif (array_key_exists('user',$_SESSION) && is_array($_SESSION['user']) && array_key_exists('info',$_SESSION['user'])) {
			$user = $this->fetchSingle(sprintf('select * from members where id = %d and deleted = 0 and enabled = 1 and (expires = "0000-00-00" || expires > curdate())',$_SESSION['user']['info']['id']));
			return is_array($user) && count($user) > 0;
		}
		return false;
	}

    /**
     * @param $module
     * @return void
     */
    protected function redirect($module) {
		ob_clean();
		header(sprintf('Location:/modit/%s',$module));
		exit();
	}

    /**
     * @param $fromEditMode
     * @return array|false|mixed|string|string[]|void
     * @throws phpmailerException
     */
    function render($fromEditMode = false) {
		$GLOBALS['secureLink'] = 0;
		if (count($_SESSION) > 0) {
			$this->logMessage(__FUNCTION__,sprintf('SESSION [%s]',print_r($_SESSION,1)),2);
		}
		if (count($_REQUEST) > 0) {
			$this->logMessage(__FUNCTION__,sprintf('REQUEST [%s]',print_r($_REQUEST,1)),2);
		}
		if (!$this->isLoggedIn(true))
			header("Location:/modit");
		if (array_key_exists('ajax',$_REQUEST)) {
			$ajax = new feAjax();
			return $ajax->show();
		} else {
			$return = '';
			if (array_key_exists('t_id',$_REQUEST) && $template = $this->fetchSingle(sprintf('select * from templates where id = %d',$_REQUEST['t_id']))) {
				$return = $this->renderTemplate($template,$fromEditMode);
			}
			if (array_key_exists('p_id',$_REQUEST) && $page = $this->fetchSingle(sprintf('select c.*, t.html, p.id as page_id, p.content from pages p, content c, templates t where p.id = %d and c.id = p.content_id and t.template_id = p.template_id and t.version = (select max(version) from templates t1 where t1.template_id = p.template_id)',$_REQUEST['p_id']))) {
				$this->setPageName(strlen($page['seo_url']) > 0 ? $page['seo_url'] : $page['search_title']);
				$return = $this->renderPage($page,$fromEditMode);
				$this->logMessage(__FUNCTION__,sprintf("renderPage returned [%s]",$return),2);
			}
			return $return;
		}
	}

    /**
     * @return false|string
     * @throws phpmailerException
     */
    function getModuleInfo() {
		$this->addError('to be implemented by each class');
		return $this->getModuleList(array());
	}

	/**
	 * @param $methods
	 * @return false|string
	 * @throws phpmailerException
	 */
    function getModuleList($methods) {
		$form = new Forms();
		asort($methods);
		$form->init($this->getTemplate('getModuleInfo'));
		$fields = $this->getFields('getModuleInfo');
		$form->buildForm($fields);
		foreach($methods as $method) {
			$form->getField('functions')->addOption($method,$method);
		}
		return $this->ajaxReturn(array('status'=>'true','html'=>$form->show()));
	}

    /**
     * @return false|string
     * @throws phpmailerException
     */
    function getFileList() {
		$class = array_key_exists('module',$_REQUEST) ? $_REQUEST['module'] : '';
		if ((int)$class > 0) {
			$class = $this->fetchScalar(sprintf('select classname from modules where id = %d and enabled = 1 and frontend = 1',$class));
		}
		$form = new Forms();
		$form->init($this->getTemplate('getFileList'));
		$fields = $this->getFields('getFileList');
		$form->buildForm($fields);
		$dir = sprintf('%sforms/%s',$this->M_DIR,$class);
		$files = $this->getFiles($dir,0);
		asort($files);
		$form->getField('files')->addOptions($files);
		return $this->ajaxReturn(array('status'=>'true','html'=>$form->show()));
	}

    /**
     * @param $dir
     * @param $level
     * @return string[]
     * @throws phpmailerException
     */
    private function getFiles($dir, $level) {
		$path = explode('/',$dir);
		if ($level > 0)
			$cur = array_slice($path,-$level);
		else
			$cur = array();
		$this->logMessage(__FUNCTION__,sprintf('dir [%s] level [%s] path [%s] cur [%s]',$dir,$level,print_r($path,true),print_r($cur,true)),1);
		$files = array(""=>"");
		if (is_dir($dir) && $dh = opendir(realpath($dir))) {
			while (($file = readdir($dh)) !== false) {
				if (!($file == "." || $file == ".."))
					if (is_dir(sprintf('%s/%s',$dir,$file))) {
						$tmp = $this->getFiles($dir.'/'.$file,$level+1);
						$this->logMessage(__FUNCTION__,sprintf('return from subdir [%s]',print_r($tmp,true)),1);
						$files = array_merge($files,$tmp);
						$this->logMessage(__FUNCTION__,sprintf('subdir after merge [%s]',print_r($files,true)),1);
					}
					else {
						$nm = count($cur) > 0 ? implode('/',$cur).'/'.$file : $file;
						$files[$nm] = $nm;
					}
			}
			closedir($dh);
		}
		return $files;
	}

    /**
     * @param $data
     * @param $fromEditMode
     * @return array|mixed|string|string[]|void
     * @throws phpmailerException
     */
    function renderTemplate($data, $fromEditMode = false) {
		$this->logMessage(__FUNCTION__,sprintf('data [%s] fromEditMode [%s]',print_r($data,true),$fromEditMode),1);
		$form = new Forms();
		if (!defined('FRONTEND')) {
			$session_key = sprintf('%s_%d','T',$data['id']);
			if (!array_key_exists('changeModule',$_SESSION)) $_SESSION['changeModule'] = array();
			if (!array_key_exists($session_key,$_SESSION['changeModule'])) $_SESSION['changeModule'][$session_key] = array();
			$form = new Forms();
			$flds = $this->getFields('templateRender');
			$form->buildForm($flds);
			$tmp = array_key_exists('edit',$_REQUEST) ? $_REQUEST['edit'] : 'do nothing';
			$this->logMessage(__FUNCTION__,sprintf("function selected is [%s]",$tmp),2);
			$editing = $tmp == 'edit';
			$saving = $tmp == 'save' || $tmp == 'overwrite' || $tmp == 'changes';
			$overwrite = $tmp == 'overwrite';
			$changesOnly = $tmp == 'changes';
			$reverting = $tmp == 'revert';
			$deleting = $tmp == 'delete';
			$form->addTag('editing',$editing ? 'editing' : '');
			if ($reverting) {
				$this->logMessage(__FUNCTION__,sprintf('reverting back to saved data [%s]',$session_key),1);
				$_SESSION['changeModule'][$session_key] = array();
			}
			$editor = new Forms();
			$editor->init($this->getTemplate('editorForm'));
			$e_flds = array(
				'page_type'=>array('type'=>'tag','value'=>'t'),
				'page_id'=>array('type'=>'tag','value'=>$data['id'])
			);
			if ($editing) {
				$e_flds['saveButton'] = array('type'=>'button','value'=>'Save','onclick'=>'setEditMode("save");return false;');
				$e_flds['saveAndOverwrite'] = array('type'=>'button','value'=>'Save and Overwrite Pages','onclick'=>'setEditMode("overwrite");return false;');
				$e_flds['delete'] = array('type'=>'button','value'=>'Delete this Version','onclick'=>'setEditMode("delete");return false;');
				$e_flds['revertButton'] = array('type'=>'button','value'=>'Revert to Saved','onclick'=>'setEditMode("revert");return false;');
				$e_flds['previewButton'] = array('type'=>'button','value'=>'Preview Changes','onclick'=>'setEditMode("preview");return false;');
			}
			else {
				$e_flds['editButton'] = array('type'=>'button','value'=>'Edit','onclick'=>'setEditMode("edit");return false;');
				$e_flds['delete'] = array('type'=>'button','value'=>'Delete this Version','onclick'=>'setEditMode("delete");return false;');
			}
			if ($saving) {
				$this->logMessage(__FUNCTION__,'saving template data',1);
				$this->beginTransaction();
				//
				//	first, create a new template & increment the version #
				//
				$template = $this->fetchSingle(sprintf('select * from templates where id = %d',$_REQUEST['t_id']));
				$old_id = $template['id'];
				unset($template['id']);
				//
				//	i would normally expect this to be $tempalte['version']+1 but not necessarily
				//
				$version = $this->fetchScalar(sprintf('select max(version) from templates where template_id = (select template_id from templates where id = %d)',$_REQUEST['t_id']));
				$template['version'] = $version+1;
				$template['created'] = date(DATE_ATOM);
				$insert = array();
				foreach($template as $fld=>$value) {
					$insert[$fld] = $value;
				}
				$obj = new preparedStatement(sprintf('insert into templates(%s) values(?%s)',
					implode(',',array_keys($insert)),str_repeat(',?',count($insert)-1)));
				$obj->bindParams(array_merge(array(str_repeat('s',count($insert))),array_values($insert)));
				$status = $obj->execute();
				if ($status) {
					$t_id = $this->insertId();
					$_REQUEST['t_id'] = $t_id;
					//
					//	grab the current modules & compare to the session ones
					//
					$modules = $this->fetchAll(sprintf('select * from modules_by_page where page_type = "T" and page_id = %d',$old_id));
					$session = $_SESSION['changeModule']['T_'.$old_id];
					foreach($modules as $module) {
						if(array_key_exists($module['module_name'],$session)) {
							$newModule = $session[$module['module_name']];
							if ((array_key_exists('fetemplate_id',$newModule) && $newModule['fetemplate_id'] > 0) || $module['content_area']) {
								$this->logMessage(__FUNCTION__,'superceding module '.$module['module_name'],1);
							}
							else {
								$this->logMessage(__FUNCTION__,'removing module '.$module['module_name'],1);
								$newModule = array();
							}
							unset($session[$module['module_name']]);
						}
						else $newModule = $module;
						if (is_array($newModule) && count($newModule) > 0) {
							$this->logMessage(__FUNCTION__,sprintf('replacing [%s] with [%s]',print_r($module,true),print_r($newModule,true)),2);
							unset($module['id']);
							$newModule['page_id'] = $t_id;
							$insert = array();
							$module['page_id'] = $t_id;
							$module['fetemplate_id'] = $newModule['fetemplate_id'];
							$module['folder_id'] = $newModule['folder_id'];
							$module['state'] = $newModule['state'];
							$module['query_linked'] = $newModule['query_linked'];
							$module['include_subfolders'] = array_key_exists('include_subfolders',$newModule) ? $newModule['include_subfolders'] : 0;
							$module['allow_override'] = array_key_exists('allow_override',$newModule) ? $newModule['allow_override'] : 0;
							$module['content_area'] = array_key_exists('content_area',$newModule) ? $newModule['content_area'] : 0;
							$module['content'] = array_key_exists('content',$newModule) ? $newModule['content'] : '';
							$obj = new preparedStatement(sprintf('insert into modules_by_page(%s) values(?%s)',
								implode(',',array_keys($module)),str_repeat(',?',count($module)-1)));
							$obj->bindParams(array_merge(array(str_repeat('s',count($module))),array_values($module)));
							$status = $status && $obj->execute();
						}
					}
					foreach($session as $key=>$module) {
						if ((array_key_exists('fetemplate_id',$module) && $module['fetemplate_id'] != 0) || $module['content_area']) {
							if (!array_key_exists('include_subfolders',$module)) $module['include_subfolders'] = 0;
							$this->logMessage(__FUNCTION__,sprintf('adding [%s] ',print_r($module,true)),2);
							$obj = new preparedStatement(sprintf('insert into modules_by_page(page_id,page_type,module_name,fetemplate_id,folder_id,state,include_subfolders,content_area,allow_override,content,query_linked) values(?,?,?,?,?,?,?,?,?,?,?)'));
							$obj->bindParams(array('issiiiiiisi',$t_id,$module['page_type'],$module['module_name'],$module['fetemplate_id'],$module['folder_id'],$module['state'],
									$module['include_subfolders'],$module['content_area'],$module['allow_override'],$module['content'],$module['query_linked']));
							$status = $status && $obj->execute();
						}
						else {
							$this->logMessage(__FUNCTION__,sprintf('not saving removed template [%s] ',print_r($module,true)),2);
						}
					}
				}
				if ($status && $overwrite) {
					$modules = $this->fetchAll(sprintf('select * from modules_by_page where page_type = "T" and page_id = '.$t_id));
					$sql = sprintf('select p.* from pages p where template_id = (select template_id from templates where id = %d) and p.version = (select max(p1.version) from pages p1 where p1.content_id = p.content_id)',$t_id);
					$pages = $this->fetchAll($sql);
					$this->logMessage(__FUNCTION__,sprintf("overwrite found [%d] pages to update sq; [%s]",count($pages),$sql),2);
					foreach($pages as $page) {
						$this->logMessage(__FUNCTION__,sprintf("overwriting page [%d] content [%d] version [%d]",$page['id'],$page['content_id'],$page['version']),3);
						//$this->execute('delete from modules_by_page where page_type = "P" and page_id = '.$page['id']);
						$tmp = array();
						foreach($page as $key=>$value) {
							$tmp[$key] = $value;
						}
						unset($tmp['id']);
						$tmp['version'] = $this->fetchScalar(sprintf('select max(version) from pages where content_id = %d',$page['content_id']))+1;	//$tmp['version']+1;
						$tmp['created'] = date(DATE_ATOM);
						$stmt = $this->prepare(sprintf('insert into pages(%s) values(%s)',implode(',',array_keys($tmp)),str_repeat('?,',count($tmp)-1).'?'));
						$stmt->bindParams(array_merge(array(str_repeat('s',count($tmp))),array_values($tmp)));
						$stmt->execute();
						$p_id = $stmt->insertId();
						foreach($modules as $module) {
							$module['page_type'] = 'P';
							$module['page_id'] = $p_id;
							unset($module['id']);
							$stmt = $this->prepare(sprintf('insert into modules_by_page(%s) values(%s)',implode(',',array_keys($module)),str_repeat("?,",count($module)-1)."?"));
							$stmt->bindParams(array_merge(array(str_repeat('s', count($module))),$module));
							$status = $status && $stmt->execute();
						}
						//$this->execute(sprintf('update pages set template_id = %d where id = %d',$t_id,$page['id']));
					}
				}

				if ($status && $changesOnly) {
					$this->addMessage('saving changes only');
					$sql = sprintf('select p.* from pages p where template_id = (select template_id from templates where id = %d) and p.version = (select max(p1.version) from pages p1 where p1.content_id = p.content_id and p1.deleted = 0)',$t_id);
					$pages = $this->fetchAll($sql);
					$this->logMessage(__FUNCTION__,sprintf("overwrite found [%d] pages to update sq; [%s]",count($pages),$sql),2);
					foreach($pages as $page) {
						$this->logMessage(__FUNCTION__,sprintf("overwriting page [%d] content [%d] version [%d]",$page['id'],$page['content_id'],$page['version']),3);
						$tmp = array();
						foreach($page as $key=>$value) {
							$tmp[$key] = $value;
						}
						unset($tmp['id']);
						$tmp['version'] = $this->fetchScalar(sprintf('select max(version) from pages where content_id = %d',$page['content_id']))+1;	//$tmp['version']+1;
						$tmp['created'] = date(DATE_ATOM);
						$stmt = $this->prepare(sprintf('insert into pages(%s) values(%s)',implode(',',array_keys($tmp)),str_repeat('?,',count($tmp)-1).'?'));
						$stmt->bindParams(array_merge(array(str_repeat('s',count($tmp))),array_values($tmp)));
						$stmt->execute();
						$p_id = $stmt->insertId();
						$modules = $this->fetchAll(sprintf('select * from modules_by_page where page_id = %d and page_type = "P"',$page['id']));
						$session = $_SESSION['changeModule']['T_'.$old_id];
						//
						//	copy unchanged modules, ignore deleted modules
						//
						foreach($modules as $module) {
							$this->logMessage(__FUNCTION__,sprintf('checking module [%s]',$module['module_name']),2);
							if(array_key_exists($module['module_name'],$session)) {
								if ((array_key_exists('fetemplate_id',$session[$module['module_name']]) && $session[$module['module_name']]['fetemplate_id'] != 0) || $session[$module['module_name']]['content_area'] != 0) {
									$module = $this->fetchSingle(sprintf('select * from modules_by_page where page_type = "T" and page_id = %d and module_name = "%s"',$t_id,$module['module_name']));
									$module['page_type'] = "P";
									$module['page_id'] = $p_id;
									unset($module['id']);
									$stmt = $this->prepare(sprintf('insert into modules_by_page(%s) values(%s)',implode(',',array_keys($module)),str_repeat("?,",count($module)-1)."?"));
									$stmt->bindParams(array_merge(array(str_repeat('s', count($module))),$module));
									$status = $status && $stmt->execute();
								}
								else
									$this->logMessage(__FUNCTION__,sprintf('ignoring deleted module [%s]',$module['module_name']),1);
							}
							else {
								//
								//	nothing changed here - just copy it
								//
								$module['page_type'] = 'P';
								$module['page_id'] = $p_id;
								unset($module['id']);
								$stmt = $this->prepare(sprintf('insert into modules_by_page(%s) values(%s)',implode(',',array_keys($module)),str_repeat("?,",count($module)-1)."?"));
								$stmt->bindParams(array_merge(array(str_repeat('s', count($module))),$module));
								$status = $status && $stmt->execute();
							}
						}


						//
						//	now check for new modules
						//
						$modules = $this->fetchScalarAll(sprintf('select module_name from modules_by_page where page_type="P" and page_id = %d',$p_id));
						$modules = '~'.implode('~',$modules).'~';
						foreach($session as $key=>$module) {
							$this->logMessage(__FUNCTION__,sprintf('testing add of [%s] existing [%s]',print_r($module,true),$modules),2);
							if ((array_key_exists('fetemplate_id',$module) && $module['fetemplate_id'] > 0) || $module['content_area'] != 0) {
								if (strpos($modules,'~'.$module['module_name'].'~') === false) {
									$this->logMessage(__FUNCTION__,sprintf('adding new module [%s] to page [%s]',$module['module_name'],$modules),2);
									$module = $this->fetchSingle(sprintf('select * from modules_by_page where page_type = "T" and page_id = "%s" and module_name = "%s"',$t_id,$module['module_name']));
									$module['page_type'] = 'P';
									$module['page_id'] = $p_id;
									unset($module['id']);
									$stmt = $this->prepare(sprintf('insert into modules_by_page(%s) values(%s)',implode(',',array_keys($module)),str_repeat("?,",count($module)-1)."?"));
									$stmt->bindParams(array_merge(array(str_repeat('s', count($module))),$module));
									$status = $status && $stmt->execute();
								}
							}
						}
					}
				}

				if ($status) {
					$editor->addTag('errorMessage','The template was saved');
					$this->commitTransaction();
					$e_flds = array();
					$_SESSION['changeModule']['T_'.$old_id] = array();
					$_REQUEST['t_id'] = $t_id;
					$data = $this->fetchSingle(sprintf('select * from templates where id = %d',$_REQUEST['t_id']));
					//header(sprintf('Location:/render?t_id=%d',$t_id));
					$form = new Forms();
					$form->init($this->getTemplate('templateSuccess'));
					$form->addData($template);
					$form->addTag('t_id',$t_id);
					return $form->show();
				}
				else {
					$editor->addError('errorMessage','There was an error saving the template');
					$this->rollbackTransaction();
				}
				$this->logMessage(__FUNCTION__,sprintf('editor form [%s]',print_r($editor,true)),4);
			}
			if ($deleting) {
				$sql = sprintf('select count(id) from pages p where p.template_id = %d and p.version = (select max(version) from pages p1 where p1.content_id = p.content_id)',$_REQUEST['t_id']);
				$ct = $this->fetchScalar($sql);
				if ($ct > 0) {
					$this->addError('Current pages exist that rely on this template - cannot be deleted');
					$this->logMessage(__FUNCTION__,sprintf("delete error sql [$sql] ct [%s]",print_r($ct,true)),2);
					$status = false;
				}
				else {
					$sql = sprintf('select t.id from templates t, templates t1 where t1.id = %d and t.template_id = t1.template_id and t.version = (select max(version) from templates t2 where t2.template_id = t.template_id and t2.id != t1.id)',$_REQUEST['t_id']);
					$id = $this->fetchScalar($sql);
					//$this->execute(sprintf('delete from modules_by_page where page_type = "T" and page_id = %d',$_REQUEST['t_id']));
					$this->execute(sprintf('update templates set deleted = 1 where id = %d',$_REQUEST['t_id']));
					$redirect = sprintf("<html><head><script type='text/javascript'>window.top.location.href = '/modit/templates?t_id=%d';</script></head><body></body></html>",$id);
					$this->logMessage(__FUNCTION__,sprintf("delete success sql [$sql] id [%s], redirecting to [%s]",print_r($id,true),$redirect),2);
					echo $redirect;
					exit;
				}
			}
			$editor->buildForm($e_flds);
			$wrapper = new Forms();
			$wrapper->init($this->getTemplate('moduleWrapper'));
		} else {
			$wrapper = new Forms();
			$wrapper->setHTML('%%module%%');
			$editing = false;
			$editor = new Forms();
		}
		preg_match_all('#%%module:(.*?)%%#', $data['html'], $matches);
		foreach($matches[0] as $key=>$match) {
			$tmp = $matches[1][$key];
			$this->logMessage(__FUNCTION__,sprintf('session test [%s]',$tmp),4);
			$module = array();
			$wrapper->reset();
			$wrapper->addTag('title',$matches[1][$key],true);
			$wrapper->addTag('page_id',$data['id']);
			$wrapper->addTag('page_type','T');
			$wrapper->addTag('module_name',$matches[1][$key],true);
			$wrapper->addTag('dragOrDrop','droppable');
			if ((!defined('FRONTEND')) && 
				array_key_exists($session_key,$_SESSION['changeModule']) &&
				array_key_exists($tmp,$_SESSION['changeModule'][$session_key])) {
				$module = $_SESSION['changeModule'][$session_key][$tmp];
				//
				//	could have been superceded to -none-
				//
				if (array_key_exists('fetemplate_id',$module)) {
					$module['classname'] = $this->fetchScalar(sprintf('select classname from modules m, fetemplates t where t.id = %d and m.id = t.module_id',$module['fetemplate_id']));
					$module['module_function'] = $this->fetchScalar(sprintf('select module_function from fetemplates t where t.id = %d',$module['fetemplate_id']));
				}
				else {
					$module['classname'] = '';
					$module['module_function'] = '';
				}
			}
			else {
				$sql = sprintf('select p.*, t.module_function, m.classname, m.id as module_id from modules_by_page p, fetemplates t, modules m where m.id = t.module_id and t.id = p.fetemplate_id and p.page_type = "T" and p.page_id = %d and p.module_name = "%s"',$data['id'],$matches[1][$key]);
				$sql = sprintf('select p.*, t.module_function, m.classname, m.id as module_id from modules_by_page p left join fetemplates t on t.id = p.fetemplate_id left join modules m on m.id = t.module_id where p.page_type = "T" and p.page_id = %d and p.module_name = "%s"',$data['id'],$matches[1][$key]);
				$this->logMessage(__FUNCTION__,sprintf('testing match [%s] sql [%s]',$match,$sql),3);
				$module = $this->fetchSingle($sql);
			}
			$wrapper->addTag('id',0);
			$this->logMessage(__FUNCTION__,sprintf('module data [%s]',print_r($module,true)),3);
			if (is_array($module) && count($module) > 0) {
				$wrapper->addTag('id',$module['id']);
				if ($module['content_area'] == 0 && class_exists($module['classname'])) {
					$this->logMessage(__FUNCTION__,sprintf("init type [%s] parms [%d] module [%s]",$module['classname'],$module['id'],print_r($module,true)),2);
					$wrapper->addTag('dragOrDrop','draggable');
					$class = new $module['classname']($module['id'],$module);
					if (method_exists($module['classname'],$module['module_function'])) {
						if ($module['state'] == 0 || ($module['state'] == 1 && $this->isLoggedIn()) || ($module['state'] == 2 && !$this->isLoggedIn())) {
							$this->logMessage(__FUNCTION__,sprintf('rendering class [%s] function [%s] into [%s] state [%d]',$module['classname'],$module['module_function'],$match,$module['state']),1);
							$html = $class->{$module['module_function']}();
						}
						else $html = '';
					}
					else {
						$this->logMessage(__FUNCTION__,sprintf('Invalid function called [%s] Template [%s] module[%s]',$module['module_function'],print_r($data,true),print_r($module,true)),1,true);
					}
				}
				else {
					//
					//	start new code
					//
					$html = "";
					if ($module['content_area']) {
						if ((!defined('FRONTEND')) && 
								array_key_exists('changeModule',$_SESSION) && 
								array_key_exists($session_key,$_SESSION['changeModule']) &&
								array_key_exists($tmp,$_SESSION['changeModule'][$session_key]) &&
								array_key_exists('content',$_SESSION['changeModule'][$session_key][$tmp])) {
							$html = $_SESSION['changeModule'][$session_key][$tmp]['content'];
						}
						else {
							if ($module['state'] == 0 || ($module['state'] == 1 && $this->isLoggedIn()) || ($module['state'] == 2 && !$this->isLoggedIn())) {
								$html = sprintf('<div class="tinymce">%s</div>',$module['content']);
							}
						}
						if ($editing) {
							//
							//	embed the tinymce editor within a normal change module structure
							//
							//
							//	set up the tinymce content editor
							//
							$edit = new Forms();
							$edit->init($this->getTemplate('tinymceEditor'));
							$editFlds = $this->getFields('tinymceEditor');
							$edit->buildForm($editFlds);
							if ($html == '' || is_null($html)) $html = '&nbsp;';	// make the div visible
							$edit->addData($module);
							$edit->addTag('content',$html,false);
							$edit->addTag('pageContent',0);
							$html = $edit->show();
							$this->logMessage(__FUNCTION__,sprintf('html for tinymce [%s] is [%s] data [%s]',print_r($wrapper,true),$html,print_r($data,true)),1);
						}
						else {
							$html = str_replace("{{","%%",$html);
							$html = str_replace("}}","%%",$html);
							$this->logMessage(__FUNCTION__,sprintf("new content html is [%s]",$html),1);
						}
					}
					else {
						if ($module['fetemplate_id'] > 0)
							$this->logMessage(__FUNCTION__,sprintf('Invalid class[1] called [%s] Template [%s] module [%s]',$module['classname'],print_r($data,true),print_r($module,true)),1,true);
						$html = '&nbsp;';
					}
				}
				if ($editing) {
					$changer = $this->changeModule(array('id'=>0,'page_type'=>'T','module_name'=>$matches[1][$key],'module_id'=>0,'page_id'=>$data['id'],'template_id'=>$data['template_id']));
					$wrapper->addTag('changer',$changer,false);
					$wrapper->addTag('module',$html,false);
					$html = $wrapper->show();
				}
				$data['html'] = str_replace($match,$html,$data['html']);
			}
			else {
				if ($editing) {
					$changer = $this->changeModule(array('id'=>0,'page_type'=>'T','module_name'=>$matches[1][$key],'module_id'=>0,'page_id'=>$data['id'],'template_id'=>$data['template_id']));
					$wrapper->addTag('changer',$changer,false);
					$wrapper->addTag('module','&nbsp;',false);
					$html = $wrapper->show();
				}
				else $html = '';
				$data['html'] = str_replace($match,$html,$data['html']);
			}
		}

		preg_match_all('#%%page:(.*?)%%#', $data['html'], $matches);
		$this->logMessage(__FUNCTION__,sprintf("page matches [%s] data [%s]",print_r($matches,true),print_r($data,true)),4);
		foreach($matches[0] as $key=>$match) {
			$tmp = $matches[1][$key];
			if (array_key_exists($tmp,$data)) {
				if ($tmp == 'content') {
					if (array_key_exists('pageContent',$_SESSION) && array_key_exists('P_'.$data['page_id'],$_SESSION['pageContent'])) {
						$data[$tmp] = $_SESSION['pageContent']['P_'.$data['page_id']];
					}
					if ($editing) {
						//
						//	set up the tinymce content editor
						//
						$edit = new Forms();
						$edit->init($this->getTemplate('tinymceEditor'));
						$editFlds = $this->getFields('tinymceEditor');
						$edit->buildForm($editFlds);
						if ($data['content'] == '' || is_null($data['content'])) $data['content'] = '&nbsp;';	// make the div visible
						$data['page_type'] = 'P';
						$edit->addData($data);
						$edit->addTag('pageContent',1);
						$edit->addTag('module_name',$tmp);
						$data[$tmp] = $edit->show();
						$this->logMessage(__FUNCTION__,sprintf("content area form after rendering [%s]",$data[$tmp]),3);
					}
				}
				$data['html'] = str_replace($match,$data[$tmp],$data['html']);
			}
		}
		$this->logMessage(__FUNCTION__,sprintf("after page matches data [%s]",print_r($data,true)),4);

		preg_match_all('#%%meta:(.*?)%%#', $data['html'], $matches);
		$this->logMessage(__FUNCTION__,sprintf("page matches [%s] data [%s]",print_r($matches,true),print_r($data,true)),4);
		foreach($matches[0] as $key=>$match) {
			$tmp = $matches[1][$key];
			if (array_key_exists('meta_'.$tmp,$data) && strlen($data['meta_'.$tmp]) > 0) {
				$data['html'] = str_replace($match,$data['meta_'.$tmp],$data['html']);
			}
			else {
				$this->logMessage(__FUNCTION__,sprintf("meta testing [%s]",$tmp),2);
				if ($meta = $this->getConfigVar($tmp)) {
					if ($tmp == 'google_analytics' && defined('FRONTEND') && !$fromEditMode)
						$data['html'] = str_replace($match,$meta,$data['html']);
				}
			}
		}
		if (!defined('FRONTEND') && $editing) {
			$tmp = new Forms();
			$tmp->setHtml(file_get_contents('js/cms.js'));
			$form->addTag('cmsjs',$tmp->show(),false);
			$form->addTag('cmscss',file_get_contents('css/cms.css'),false);
			$form->addTag('formend',file_get_contents(sprintf('%sforms/formEnd.html',$this->M_DIR)),false);
		}
		$form->setHTML($data['html']);
		$editor->addTag('warnings',$this->showMessages(),false);
		$form->addTag('cmsbar',$editor->show(),false);

		if (array_key_exists('module',$_REQUEST)) {
			switch($_REQUEST['module']) {
			case 'news':
				if (array_key_exists('news_id',$_REQUEST)) {
					if ($article = $this->fetchSingle(sprintf("select * from news where deleted = 0 and enabled = 1 and published = 1 and id = %d",$_REQUEST["news_id"]))) {
						$f_id = 0;
						if (array_key_exists("f_id",$_REQUEST)) $f_id = $_REQUEST["f_id"];
						if (array_key_exists("folder_id",$_REQUEST)) $f_id = $_REQUEST["folder_id"];
						if (!$folder = $this->fetchSingle(sprintf("select * from news_folders where id = %d",$f_id))) {
							$folder = array("id"=>0,"title"=>"","level"=>0,"left_id"=>0,"right_id"=>0);
						}
						$p = new news(0);
						$article["folder_id"] = $f_id;
						$form->addData(array("article"=>$p->formatData($article),"folder"=>$p->formatFolder($folder)));
					}
				}
				break;
			case 'blogcat':
				if (array_key_exists("blogcat",$_REQUEST)) {
					if ($folder = $this->fetchSingle(sprintf("select * from blog_folders where id = %d",$_REQUEST['blogcat']))) {
						$p = new blog(0);
						$form->addData(array("folder"=>$p->formatFolder($folder)));
						$this->logMessage(__FUNCTION__,sprintf("form is now [%s]",print_r($form,true)),1);
					}
				}
				break;
			case 'category':
				if (array_key_exists("cat_id",$_REQUEST)) {
					if ($folder = $this->fetchSingle(sprintf("select * from product_folders where id = %d",$_REQUEST['cat_id']))) {
						$p = new product(0);
						$form->addData(array("folder"=>$p->formatFolder($folder)));
					}
				}
				break;
			case "product":
				if (array_key_exists('prod_id',$_REQUEST)) {
					if ($product = $this->fetchSingle(sprintf("select * from product where deleted = 0 and enabled = 1 and published = 1 and id = %d",$_REQUEST["prod_id"]))) {
						$f_id = 0;
						if (array_key_exists("cat_id",$_REQUEST)) $f_id = $_REQUEST["cat_id"];
						if (array_key_exists("f_id",$_REQUEST)) $f_id = $_REQUEST["f_id"];
						if (array_key_exists("folder_id",$_REQUEST)) $f_id = $_REQUEST["folder_id"];
						if (!$folder = $this->fetchSingle(sprintf("select * from product_folders where id = %d",$f_id))) {
							$folder = array("id"=>0,"title"=>"","level"=>0,"left_id"=>0,"right_id"=>0);
						}
						$p = new product(0);
						$product["folder_id"] = $f_id;
						$form->addData(array("product"=>$p->formatData($product),"folder"=>$p->formatFolder($folder)));
					}
				}
				break;
			case "blog":
				if (array_key_exists('blog_id',$_REQUEST)) {
					if ($blog = $this->fetchSingle(sprintf("select * from blog where deleted = 0 and enabled = 1 and published = 1 and id = %d",$_REQUEST["blog_id"]))) {
						$f_id = 0;
						if (array_key_exists("blog_cat",$_REQUEST)) $f_id = $_REQUEST["blog_cat"];
						if (array_key_exists("f_id",$_REQUEST)) $f_id = $_REQUEST["f_id"];
						if (array_key_exists("folder_id",$_REQUEST)) $f_id = $_REQUEST["folder_id"];
						if (!$folder = $this->fetchSingle(sprintf("select * from blog_folders where id = %d",$f_id))) {
							$folder = array("id"=>0,"title"=>"","level"=>0,"left_id"=>0,"right_id"=>0);
						}
						$p = new blog(0);
						$blog["folder_id"] = $folder["id"];
						$form->addData(array("blog"=>$p->formatData($blog),"folder"=>$p->formatFolder($folder)));
					}
				}
				break;
			case 'storecat':
				if (array_key_exists("folder_id",$_REQUEST)) {
					if ($folder = $this->fetchSingle(sprintf("select * from store_folders where id = %d",$_REQUEST['folder_id']))) {
						$p = new stores(0);
						$form->addData(array("folder"=>$p->formatFolder($folder)));
					}
				}
				break;
			case "store":
				if (array_key_exists('store_id',$_REQUEST)) {
					if ($store = $this->fetchSingle(sprintf("select * from stores where deleted = 0 and enabled = 1 and published = 1 and id = %d",$_REQUEST["store_id"]))) {
						$f_id = 0;
						if (array_key_exists("store_cat",$_REQUEST)) $f_id = $_REQUEST["store_cat"];
						if (array_key_exists("f_id",$_REQUEST)) $f_id = $_REQUEST["f_id"];
						if (array_key_exists("folder_id",$_REQUEST)) $f_id = $_REQUEST["folder_id"];
						if (!$folder = $this->fetchSingle(sprintf("select * from store_folders where id = %d",$f_id))) {
							$folder = array("id"=>0,"title"=>"","level"=>0,"left_id"=>0,"right_id"=>0);
						}
						$p = new stores(0);
						$store["folder_id"] = $folder["id"];
						$form->addData(array("store"=>$p->formatData($store),"folder"=>$p->formatFolder($folder)));
					}
				}
				break;
			case 'calendar':
			case 'member':
				if (array_key_exists("folder_id",$_REQUEST)) {
					if ($folder = $this->fetchSingle(sprintf("select * from members_folders where id = %d",$_REQUEST['folder_id']))) {
						$p = new calendar(0);
						$form->addData(array("folder"=>$p->formatFolder($folder)));
					}
				}
				break;
			case "event":
				if (array_key_exists('event_id',$_REQUEST)) {
					if ($event = $this->fetchSingle(sprintf("select * from events where deleted = 0 and enabled = 1 and published = 1 and id = %d",$_REQUEST["event_id"]))) {
						$f_id = 0;
						if (array_key_exists("event_cat",$_REQUEST)) $f_id = $_REQUEST["event_cat"];
						if (array_key_exists("f_id",$_REQUEST)) $f_id = $_REQUEST["f_id"];
						if (array_key_exists("folder_id",$_REQUEST)) $f_id = $_REQUEST["folder_id"];
						if (!$folder = $this->fetchSingle(sprintf("select * from members_folders where id = %d",$f_id))) {
							$folder = array("id"=>0,"title"=>"","level"=>0,"left_id"=>0,"right_id"=>0);
						}
						$p = new calendar(0);
						$event["folder_id"] = $folder["id"];
						$form->addData(array("event"=>$p->formatData($event),"folder"=>$p->formatFolder($folder)));
					}
				}
				break;
			default:
				break;
			}
		}
		return $this->processConditionals($form->show());
	}

    /**
     * @param $data
     * @param $fromEditMode
     * @return array|mixed|string|string[]|void
     * @throws phpmailerException
     */
    function renderPage($data, $fromEditMode = false) {
		$this->logMessage(__FUNCTION__,sprintf('data [%s] fromEditMode [%s]',print_r($data,true),$fromEditMode),1);
		$form = new Forms();
		$editing = false;
		if (!defined('FRONTEND') && $fromEditMode == true) {
			$session_key = sprintf('%s_%d','P',$data['page_id']);
			if (!array_key_exists('changeModule',$_SESSION)) $_SESSION['changeModule'] = array();
			if (!array_key_exists($session_key,$_SESSION['changeModule'])) $_SESSION['changeModule'][$session_key] = array();
			if (!array_key_exists('pageContent',$_SESSION)) $_SESSION['pageContent'] = array();
			$flds = $this->getFields('pageRender');
			$form->buildForm($flds);
			$tmp = array_key_exists('edit',$_REQUEST) ? $_REQUEST['edit'] : 'do nothing';
			$editing = $tmp == 'edit';
			$form->addTag('editing',$editing?'editing':'');
			$saving = $tmp == 'save' || $tmp == 'overwrite';
			$reverting = $tmp == 'revert';
			$deleting = $tmp == 'delete';
			$updating = $tmp == 'template';
			if ($reverting) {
				$this->logMessage(__FUNCTION__,sprintf('reverting back to saved data [%s]',$session_key),1);
				$_SESSION['changeModule'][$session_key] = array();
				unset($_SESSION['pageContent'][$session_key]);	// = array();
			}
			$editor = new Forms();
			$editor->init($this->getTemplate('pageEditorForm'));
			$e_flds = array(
				'page_type'=>array('type'=>'tag','value'=>'p'),
				'page_id'=>array('type'=>'tag','value'=>$data['page_id'])
			);
			if ($editing) {
				$e_flds['saveButton'] = array('type'=>'button','value'=>'Save','onclick'=>'setEditMode("save");return false;');
				$e_flds['deleteButton'] = array('type'=>'button','value'=>'Delete this Version','onclick'=>'setEditMode("delete");return false;');
				$e_flds['revertButton'] = array('type'=>'button','value'=>'Revert to Saved','onclick'=>'setEditMode("revert");return false;');
				$e_flds['previewButton'] = array('type'=>'button','value'=>'Preview Changes','onclick'=>'setEditMode("preview");return false;');
				$e_flds['templateButton'] = array('type'=>'button','value'=>'Update From Template','onclick'=>'setEditMode("template");return false;');
			}
			else {
				$e_flds['editButton'] = array('type'=>'button','value'=>'Edit','onclick'=>'setEditMode("edit");return false;');
				$e_flds['deleteButton'] = array('type'=>'button','value'=>'Delete this Version','onclick'=>'setEditMode("delete");return false;');
			}
			if ($updating) {
				$this->logMessage(__FUNCTION__,'updating elements from template',1);
				$this->beginTransaction();
				$template = $this->fetchSingle(sprintf('select * from pages where id = %d',$_REQUEST['p_id']));
				$old_id = $template['id'];
				unset($template['id']);
				//
				//	i would normally expect this to be $template['version']+1 but not necessarily
				//
				$version = $this->fetchScalar(sprintf('select max(version) from pages where content_id = %d',$template['content_id']));
				$template['version'] = $version+1;
				$template['created'] = date(DATE_ATOM);
				$insert = array();
				foreach($template as $fld=>$value) {
					$insert[$fld] = $value;
				}
				if (array_key_exists('pageContent',$_SESSION) && array_key_exists('P_'.$old_id,$_SESSION['pageContent'])) {
					$insert['content'] = $_SESSION['pageContent']['P_'.$old_id];
				}
				$obj = new preparedStatement(sprintf('insert into pages(%s) values(?%s)',
					implode(',',array_keys($insert)),str_repeat(',?',count($insert)-1)));
				$obj->bindParams(array_merge(array(str_repeat('s',count($insert))),array_values($insert)));
				if ($status = $obj->execute()) {
					$new_id = $this->insertId();
					$mods = $this->fetchAll(sprintf('select m.* from modules_by_page m where m.page_type="T" and m.page_id = (select t.id from templates t, pages p where t.template_id = p.template_id and p.id = %d order by t.version desc limit 1 ) and m.module_name not in (select m2.module_name from modules_by_page m2 where m2.page_id = %d)',$_REQUEST['p_id'],$_REQUEST['p_id']));
					$this->logMessage(__FUNCTION__,sprintf('applying modules [%s]',print_r($mods,true)),1);
					$oldmod = $this->fetchAll(sprintf('select * from modules_by_page where page_id = %d',$_REQUEST['p_id']));
					$this->logMessage(__FUNCTION__,sprintf('existing modules [%s]',print_r($oldmod,true)),1);
					$mods = array_merge($mods,$oldmod);
					$this->logMessage(__FUNCTION__,sprintf('combined modules [%s]',print_r($mods,true)),1);
					foreach($mods as $newmod) {
						$insert = array();
						unset($newmod['id']);
						foreach($newmod as $key=>$value) {
							$insert[$key] = $value;
						}
						$insert['page_id'] = $new_id;
						$insert['page_type'] = 'P';
						$obj = new preparedStatement(sprintf('insert into modules_by_page(%s) values(?%s)',
							implode(',',array_keys($insert)),str_repeat(',?',count($insert)-1)));
						$obj->bindParams(array_merge(array(str_repeat('s',count($insert))),array_values($insert)));
						$status = $status && $obj->execute();
					}
				}
				if ($status) {
					$this->commitTransaction();
					$sql = sprintf('select c.*, t.html, p.id as page_id from pages p, content c, templates t where p.id = %d and c.id = p.content_id and t.template_id = p.template_id and t.version = (select max(t1.version) from templates t1 where t1.template_id = p.template_id)',$new_id);
					$data = $this->fetchSingle($sql);
					$return = '<script type="text/javascript">parent.loadPage('.$data['id'].')</script>';
					$this->logMessage(__FUNCTION__,sprintf("redirecting to [%s] data [%s] sql [%s]",$return,print_r($data,true),$sql),2);
					return $return;
				}
				else {
					$this->rollbackTransaction();
				}
			}
			if ($saving) {
				$this->logMessage(__FUNCTION__,'saving page data',1);
				$this->beginTransaction();
				//
				//	first, create a new page & increment the version #
				//
				$template = $this->fetchSingle(sprintf('select * from pages where id = %d',$_REQUEST['p_id']));
				$old_id = $template['id'];
				unset($template['id']);
				//
				//	i would normally expect this to be $template['version']+1 but not necessarily
				//
				$version = $this->fetchScalar(sprintf('select max(version) from pages where content_id = %d',$template['content_id']));
				$template['version'] = $version+1;
				$template['created'] = date(DATE_ATOM);
				$insert = array();
				foreach($template as $fld=>$value) {
					$insert[$fld] = $value;
				}
				if (array_key_exists('pageContent',$_SESSION) && array_key_exists('P_'.$old_id,$_SESSION['pageContent'])) {
					$insert['content'] = $_SESSION['pageContent']['P_'.$old_id];
				}
				$obj = new preparedStatement(sprintf('insert into pages(%s) values(?%s)',
					implode(',',array_keys($insert)),str_repeat(',?',count($insert)-1)));
				$obj->bindParams(array_merge(array(str_repeat('s',count($insert))),array_values($insert)));
				$status = $obj->execute();
				if ($status) {
					$p_id = $this->insertId();
					$_REQUEST['p_id'] = $p_id;
					//
					//	grab the current modules & compare to the session ones
					//
					$modules = $this->fetchAll(sprintf('select * from modules_by_page where page_type = "P" and page_id = %d',$old_id));
					$session = $_SESSION['changeModule']['P_'.$old_id];
					$this->logMessage(__FUNCTION__,sprintf('existing modules [%s], session [%s]', print_r($modules,true), print_r($session,true)),4);
					foreach($modules as $module) {
						if(array_key_exists($module['module_name'],$session)) {
							$newModule = $session[$module['module_name']];
							$this->logMessage(__FUNCTION__,sprintf('new module test [%s]',print_r($newModule,true)),1);
							if ($newModule['fetemplate_id'] > 0 || $newModule['content_area']) {
								$this->logMessage(__FUNCTION__,'superceding module '.$module['module_name'],1);
							}
							else {
								$this->logMessage(__FUNCTION__,'removing module '.$module['module_name'],1);
								$newModule = array();
							}
							unset($session[$module['module_name']]);
						}
						else $newModule = $module;
						if (is_array($newModule) && count($newModule) > 0) {
							$newModule['page_id'] = $p_id;
							$this->logMessage(__FUNCTION__,sprintf('replacing [%s] with [%s]',print_r($module,true),print_r($newModule,true)),2);
							unset($module['id']);
							$insert = array();
							$newModule['page_id'] = $p_id;
							$obj = new preparedStatement(sprintf('insert into modules_by_page(page_id,page_type,module_name,fetemplate_id,folder_id,state,include_subfolders,content_area,allow_override,content) values(?,?,?,?,?,?,?,?,?,?)'));
							$obj->bindParams(array('issiiiiiis',$newModule['page_id'],$newModule['page_type'],$newModule['module_name'],$newModule['fetemplate_id'],$newModule['folder_id'],$newModule['state'],
									$newModule['include_subfolders'],$newModule['content_area'],$newModule['allow_override'],$newModule['content']));
							$status = $status && $obj->execute();
						}
					}
					foreach($session as $key=>$module) {
						$this->logMessage(__FUNCTION__,sprintf('adding [%s] ',print_r($module,true)),2);
						$obj = new preparedStatement(sprintf('insert into modules_by_page(page_id,page_type,module_name,fetemplate_id,folder_id,state,include_subfolders,content_area,allow_override,content,query_linked) values(?,?,?,?,?,?,?,?,?,?,?)'));
						$obj->bindParams(array('ssssssiiisi',$p_id,$module['page_type'],$module['module_name'],$module['fetemplate_id'],array_key_exists('folder_id',$module) ? $module['folder_id']:0,$module['state'],
									$module['include_subfolders'],$module['content_area'],$module['allow_override'],$module['content'],$module['query_linked']));
						$status = $status && $obj->execute();
					}
				}
				if ($status) {
					$this->logMessage(__FUNCTION__,sprintf("save was successful"),2);
					$editor->addTag('errorMessage','The page was saved');
					$this->commitTransaction();
					$e_flds = array();
					unset($_SESSION['changeModule']['P_'.$old_id]);
					unset($_SESSION['pageContent']['P_'.$old_id]);
					$_REQUEST['p_id'] = $p_id;
					$sql = sprintf('select c.*, t.html, p.id as page_id from pages p, content c, templates t where p.id = %d and c.id = p.content_id and t.template_id = p.template_id and t.version = (select max(t1.version) from templates t1 where t1.template_id = p.template_id)',$_REQUEST['p_id']);
					$data = $this->fetchSingle($sql);
					$return = '<script type="text/javascript">parent.loadPage('.$data['id'].')</script>';
					$this->logMessage(__FUNCTION__,sprintf("redirecting to [%s] data [%s] sql [%s]",$return,print_r($data,true),$sql),2);
					return $return;
				}
				else {
					$this->logMessage(__FUNCTION__,sprintf("save failed"),2);
					$editor->addError('errorMessage','There was an error saving the template');
					$this->rollbackTransaction();
				}
				$this->logMessage(__FUNCTION__,sprintf('editor form [%s]',print_r($editor,true)),4);
			}
			if ($deleting) {
				$status = true;
				$content_id = $this->fetchScalar(sprintf('select content_id from pages where id = %d',$_REQUEST['p_id']));
				$versions = $this->fetchScalar(sprintf('select count(0) from pages where content_id = %d',$content_id));
				$this->logMessage(__FUNCTION__,sprintf("delete content_id [%d] versions [%d]",$content_id,$versions),3);
				if ($versions == 1) {
					$links = $this->fetchScalarAll(sprintf('select title from content where internal_link = %d',$content_id));
					$mptt = new mptt('content');
					if (count($links) > 0) {
						$this->addError(sprintf('Links exist that rely on this page (%s)',implode($links)));
						$this->logMessage(__FUNCTION__,sprintf("cannot delete with existing links [%s]",implode($links)),4);
						$status = false;
					}
					else {
						$children = $this->fetchScalar(sprintf('select count(0) from content c1, content c2 where c1.id = %d and c2.left_id > c1.left_id and c2.right_id < c1.right_id and c2.level > c1.level',$content_id));
						if ($children > 0) {
							$this->addError(sprintf('Children exist and this is the last version of the content',implode($links)));
							$this->logMessage(__FUNCTION__,sprintf("cannot delete with existing children [%d]",$children),4);
							$status = false;
						}
					}
					$sql = sprintf('update pages set deleted = 1 where id = %d',$_REQUEST['p_id']);
					$this->execute($sql);
					//
					//	delete the content entry as well - no more pages exist
					//
					$mptt->delete($content_id,true);
				}
				else {
					$sql = sprintf('update pages set deleted = 1 where id = %d',$_REQUEST['p_id']);
					$this->execute($sql);
				}
				if ($status == true) {
					$redirect = sprintf("<html><head><script type='text/javascript'>window.top.location.href = '/modit/menu?p_id=%d';</script></head><body></body></html>",$content_id);
					$this->logMessage(__FUNCTION__,sprintf("delete success [%s] id [%s], redirecting to [%s]",$sql,print_r($content_id,true),$redirect),2);
					echo $redirect;
					return;
				}
			}
			$editor->buildForm($e_flds);
			$editor->addTag('errorMessage',$this->showErrors(),false);
			$form->addTag('cmsbar',$editor->show(),false);
			$wrapper = new Forms();
			$wrapper->init($this->getTemplate('moduleWrapper'));
		}
		else {
			$wrapper = new Forms();
			$wrapper->setHTML('%%module%%');
			$editing = false;
			$data = $this->processQueryData($data);
		}
		preg_match_all('#%%module:(.*?)%%#', $data['html'], $matches);
		foreach($matches[0] as $key=>$match) {
			$tmp = $matches[1][$key];
			$module = array();
			$html = '';
			$wrapper->reset();
			$wrapper->addTag('title',$matches[1][$key],true);
			$wrapper->addTag('page_id',$data['page_id']);
			$wrapper->addTag('page_type','P');
			$wrapper->addTag('module_name',$matches[1][$key],true);
			$wrapper->addTag('dragOrDrop','droppable');
			if ((!defined('FRONTEND')) && $fromEditMode == true && array_key_exists($tmp,$_SESSION['changeModule'][$session_key])) {
				$module = $_SESSION['changeModule'][$session_key][$tmp];
				//
				//	could have been superceded to -none-
				//
				if (array_key_exists('fetemplate_id',$module)) {
					$module['classname'] = $this->fetchScalar(sprintf('select classname from modules m, fetemplates t where t.id = %d and m.id = t.module_id',$module['fetemplate_id']));
					$module['module_function'] = $this->fetchScalar(sprintf('select module_function from fetemplates t where t.id = %d',$module['fetemplate_id']));
				}
				else {
					$module['classname'] = '';
					$module['module_function'] = '';
				}
			}
			else {
				$sql = sprintf('select p.*, t.module_function, m.classname, m.id as module_id from modules_by_page p, fetemplates t, modules m where m.id = t.module_id and t.id = p.fetemplate_id and p.page_type = "P" and p.page_id = %d and p.module_name = "%s"',$data['page_id'],$matches[1][$key]);
				$sql = sprintf('select p.*, t.module_function, m.classname, m.id as module_id from modules_by_page p left join fetemplates t on t.id = p.fetemplate_id left join modules m on m.id = t.module_id where p.page_type = "P" and p.page_id = %d and p.module_name = "%s"',$data['page_id'],$matches[1][$key]);
				$module = $this->fetchSingle($sql);
			}
			$wrapper->addTag('id',0);
			if (is_array($module) && count($module) > 0) {
				$wrapper->addTag('id',$module['id']);
				if (class_exists($module['classname'])) {
					$this->logMessage(__FUNCTION__,sprintf("init type [%s] parms [%d] module [%s]",$module['classname'],$module['id'],print_r($module,true)),2);
					$class = new $module['classname']($module['id'],$module);
					if (method_exists($module['classname'],$module['module_function'])) {
						if ($editing || $module['state'] == 0 || ($module['state'] == 1 && $this->isLoggedIn()) || ($module['state'] == 2 && !$this->isLoggedIn())) {
							$this->logMessage(__FUNCTION__,sprintf('rendering class [%s] function [%s] into [%s] state [%d]',$module['classname'],$module['module_function'],$match,$module['state']),1);
							$html = $class->{$module['module_function']}();
						}
						else {
							$this->logMessage(__FUNCTION__,sprintf('skipping module [%s] with state [%d]',$module['module_function'],$module['state']),1);
							$html = '';
						}
						if ($editing) {
							$changer = $this->changeModule($module);	//$module['id'],'P',$match);
							$wrapper->addData($module);	//!!
							$wrapper->addTag('changer',$changer,false);
							$wrapper->addTag('module',$html,false);
							$wrapper->addTag('dragOrDrop','draggable');
							$html = $wrapper->show();
						}
						$data['html'] = str_replace($match,$html,$data['html']);
					}
					else {
						if ($module['fetemplate_id'] > 0 || $module['module_id'] > 0)
							$this->logMessage(__FUNCTION__,sprintf('Invalid function called [%s] Template [%s] module[%s]',$module['module_function'],print_r($data,true),print_r($module,true)),1,true);
					}
				}
				else {
					//
					//	start new code
					//
					$html = "";
					if ($module['content_area']) {
						if ((!defined('FRONTEND')) && 
								array_key_exists('changeModule',$_SESSION) && 
								array_key_exists($session_key,$_SESSION['changeModule']) &&
								array_key_exists($tmp,$_SESSION['changeModule'][$session_key]) &&
								array_key_exists('content',$_SESSION['changeModule'][$session_key][$tmp])) {
							$html = sprintf('<div class="tinymce">%s</div>',$_SESSION['changeModule'][$session_key][$tmp]['content']);
						}
						else {
							if ($module['state'] == 0 || ($module['state'] == 1 && $this->isLoggedIn()) || ($module['state'] == 2 && !$this->isLoggedIn())) {
								$html = sprintf('<div class="tinymce">%s</div>',$module['content']);
							}
						}
						if ($editing) {
							//
							//	embed the tinymce editor within a normal change module structure
							//
							//
							//	set up the tinymce content editor
							//
							$edit = new Forms();
							$edit->init($this->getTemplate('tinymceEditor'));
							$editFlds = $this->getFields('tinymceEditor');
							$edit->buildForm($editFlds);
							if ($html == '' || is_null($html)) $html = '&nbsp;';	// make the div visible
							$edit->addData($module);
							$edit->addTag('content',$html,false);
							$edit->addTag('pageContent',0);
							//
							//	now inject tinymce into the normal form area with a change module as well
							//
							$changer = $this->changeModule($module);
							$wrapper->addData($module);
							$wrapper->addTag('changer',$changer,false);
							$wrapper->addTag('module',$edit->show(),false);
							$wrapper->addTag('dragOrDrop','draggable');
							$html = $wrapper->show();
							$this->logMessage(__FUNCTION__,sprintf('html for tinymce [%s] is [%s] data [%s]',print_r($wrapper,true),$html,print_r($data,true)),1);
						}
					}
					else {
						if ($module['fetemplate_id'] > 0)
							$this->logMessage(__FUNCTION__,sprintf('Invalid class[2] called [%s] Template [%s] module [%s]',$module['classname'],print_r($data,true),print_r($module,true)),1,true);
						if ($editing) {
							$changer = $this->changeModule($module);	//$module['id'],'P',$match);
							$wrapper->addTag('changer',$changer,false);
							$wrapper->addTag('module',$html,false);
							$html = $wrapper->show();
						}
					}
					$html = str_replace("{{","%%",$html);
					$html = str_replace("}}","%%",$html);
					$data['html'] = str_replace($match,$html,$data['html']);
					//
					//	end new code
					//
				}
			}
			else {
				if ($editing) {
					$changer = $this->changeModule(array('id'=>0,'page_type'=>'P','module_name'=>$matches[1][$key],'module_id'=>0,'page_id'=>$data['id']));
					$wrapper->reset();
					$wrapper->addTag('changer',$changer,false);
					$wrapper->addTag('module','&nbsp;',false);
					$html = $wrapper->show();
				}
				else $html = '';
				$data['html'] = str_replace($match,$html,$data['html']);
			}
		}
		if (!defined('FRONTEND') && $editing) {
			$tmp = new Forms();
			$tmp->setHtml(file_get_contents('js/cms.js'));
			$form->addTag('cmsjs',$tmp->show(),false);
			$form->addTag('cmscss',file_get_contents('css/cms.css'),false);
			$form->addTag('formend',file_get_contents(sprintf('%sforms/formEnd.html',$this->M_DIR)),false);
		}
		preg_match_all('#%%page:(.*?)%%#', $data['html'], $matches);
		$this->logMessage(__FUNCTION__,sprintf("page matches [%s] data [%s]",print_r($matches,true),print_r($data,true)),4);
		foreach($matches[0] as $key=>$match) {
			$tmp = $matches[1][$key];
			if (array_key_exists($tmp,$data)) {
				if ($tmp == 'title') {
					if (strlen($data['browser_title']) > 0) $tmp = 'browser_title';
				}
				if ($tmp == 'content') {
					if (array_key_exists('pageContent',$_SESSION) && array_key_exists('P_'.$data['page_id'],$_SESSION['pageContent'])) {
						$data[$tmp] = $_SESSION['pageContent']['P_'.$data['page_id']];
					}
					if ($editing) {
						//
						//	set up the tinymce content editor
						//
						$edit = new Forms();
						$edit->init($this->getTemplate('tinymceEditor'));
						$editFlds = $this->getFields('tinymceEditor');
						$edit->buildForm($editFlds);
						if ($data['content'] == '' || is_null($data['content'])) $data['content'] = '&nbsp;';	// make the div visible
						$data['page_type'] = 'P';
						$edit->addData($data);
						$edit->addTag('pageContent',1);
						$edit->addTag('module_name',$tmp);
						$data[$tmp] = $edit->show();
						$this->logMessage(__FUNCTION__,sprintf("content area form after rendering [%s]",$data[$tmp]),3);
					}
				}
				$data['html'] = str_replace($match,$data[$tmp],$data['html']);
			}
		}
		$this->logMessage(__FUNCTION__,sprintf("after page matches data [%s]",print_r($data,true)),4);
		preg_match_all('#%%meta:(.*?)%%#', $data['html'], $matches);
		$this->logMessage(__FUNCTION__,sprintf("page matches [%s] data [%s]",print_r($matches,true),print_r($data,true)),4);
		foreach($matches[0] as $key=>$match) {
			$tmp = $matches[1][$key];
			if (array_key_exists('meta_'.$tmp,$data) && strlen($data['meta_'.$tmp]) > 0) {
				$data['html'] = str_replace($match,$data['meta_'.$tmp],$data['html']);
			}
			else {
				if ($meta = $this->getConfigVar($tmp)) {
					if (!($tmp == 'google_analytics' && $fromEditMode))
						$data['html'] = str_replace($match,$meta,$data['html']);
				}
			}
		}
		$this->logMessage(__FUNCTION__,sprintf("after meta matches data [%s]",print_r($data,true)),4);
		$form->setHTML($data['html']);
		return $this->processConditionals($form->show());
	}

    /**
     * @param $data
     * @return mixed
     * @throws phpmailerException
     */
    function processQueryData($data) {
		if ($this->m_searchWinner == 0) {
			$this->logMessage(__FUNCTION__,sprintf('no query string found'),2);
			return $data;
		}
		if ($search = $this->fetchSingle(sprintf('select * from search_groups where id = %d',$this->m_searchWinner))) {
			$data['html'] = str_replace('%%meta:keywords%%',$search['keywords'],$data['html']);
			$data['html'] = str_replace('%%meta:description%%',$search['description'],$data['html']);
		}
		$sql = sprintf('select * from modules_by_page where page_id = %d and query_linked != 0',$data['page_id']);
		if ($modules = $this->fetchAll($sql)) {
			$this->logMessage(__FUNCTION__,sprintf('placing query related module [%s]',print_r($modules,true)),3);
			foreach($modules as $key=>$query) {
				$sql = sprintf('select p.*, t.module_function, m.classname, m.id as module_id from modules_by_page p left join fetemplates t on t.id = p.fetemplate_id left join modules m on m.id = t.module_id where p.page_type = "%s" and p.page_id = %d and p.module_name = "%s"',$query['page_type'],$query['page_id'],$query['module_name']);
				$module = $this->fetchSingle($sql);
				$module['search_group'] = $this->m_searchWinner;
				$class = new $module['classname']($module['id'],$module);
				if (method_exists($module['classname'],$module['module_function'])) {
					if ($module['state'] == 0 || ($module['state'] == 1 && $this->isLoggedIn()) || ($module['state'] == 2 && !$this->isLoggedIn())) {
						$this->logMessage(__FUNCTION__,sprintf('rendering class [%s] function [%s] into [%s] state [%d]',$module['classname'],$module['module_function'],$query['module_name'],$module['state']),2);
						$html = $class->{$module['module_function']}();
						if (strlen($html) > 0) {
							$data['html'] = str_replace(sprintf('%%%%module:%s%%%%',$query['module_name']),$html,$data['html']);
						}
					}
				}
			}
		}
		$this->logMessage(__FUNCTION__,sprintf('returning [%s]',print_r($data,true)),1);
		return $data;
	}

    /**
     * @return false|string
     * @throws phpmailerException
     */
    function renderModule() {
		$data = $_REQUEST;
		$key = sprintf('%s_%d',$data['page_type'],$data['page_id']);	//,$data['module_name']);
		$html = '';
		$this->logMessage(__FUNCTION__,sprintf('key [%s] module [%s]',$key,$data['module_name']),1);
		if (array_key_exists($data['module_name'],$_SESSION['changeModule'][$key])) {
			$module = $_SESSION['changeModule'][$key][$data['module_name']];
			//
			//	could have been superceded to -none-
			//
			if (array_key_exists('fetemplate_id',$module) && $module['fetemplate_id'] > 0) {
				$module['classname'] = $this->fetchScalar(sprintf('select classname from modules m, fetemplates t where t.id = %d and m.id = t.module_id',$module['fetemplate_id']));
				$module['module_function'] = $this->fetchScalar(sprintf('select module_function from fetemplates t where t.id = %d',$module['fetemplate_id']));
			}
			else {
				$module['classname'] = '';
				$module['module_function'] = '';
			}
		}
		else {
			$sql = sprintf('select p.*, t.module_function, m.classname, m.id as module_id from modules_by_page p, fetemplates t, modules m where m.id = t.module_id and t.id = p.fetemplate_id and p.page_type = "%s" and p.page_id = %d and p.module_name = "%s"',$data['page_type'],$data['page_id'],$data['module_name']);
			if (!($module = $this->fetchSingle($sql))) {
				$module = array('id'=>0,'classname'=>'');
			}
		}
		$this->logMessage(__FUNCTION__,sprintf('className [%s] function [%s]',$module['classname'],$module['module_function']),2);
		if (class_exists($module['classname'])) {
			$class = new $module['classname']($module['id'],$module);
			if (method_exists($module['classname'],$module['module_function'])) {
				$this->logMessage(__FUNCTION__,sprintf('rendering class [%s] function [%s] into [%s]',$module['classname'],$module['module_function'],$module['module_name']),2);
				$html = $class->{$module['module_function']}();
			}
			else {
				$this->logMessage(__FUNCTION__,sprintf('Invalid function called [%s] Template [%s] module[%s]',$module['module_function'],print_r($data,true),print_r($module,true)),1,true);
			}
		}
		else {
			if ($module['content_area']) {
				if ((!defined('FRONTEND')) && array_key_exists($key,$_SESSION['changeModule'])) {
					$html = $_SESSION['changeModule'][$key][$data['module_name']]['content'];
				}
				else {
					$html = $module['content'];	//$this->fetchScalar(sprintf('select content from pages where id = %d',$data['page_id']));
				}
				//
				//	embed the tinymce editor within a normal change module structure
				//
				//
				//	set up the tinymce content editor
				//
				$edit = new Forms();
				$edit->init($this->getTemplate('tinymceEditor'));
				$editFlds = $this->getFields('tinymceEditor');
				$edit->buildForm($editFlds);
				if ($html == '' || is_null($html)) $html = '&nbsp;';	// make the div visible
				$edit->addData($module);
				$edit->addTag('content',$html,false);
				$edit->addTag('pageContent',0);
				$html = $edit->show();
			}
			else
				if ($module['fetemplate_id'] > 0)
					$this->logMessage(__FUNCTION__,sprintf('Invalid class[3] called [%s] Template [%s] module [%s]',$module['classname'],print_r($data,true),print_r($module,true)),1,true);
		}
		return $this->ajaxReturn(array('status'=>'true','html'=>$html));
	}

    /**
     * @return false|string
     * @throws phpmailerException
     */
    function changeContent() {
		$form = new Forms();
		$form->init($this->getTemplate('tinymceEditing'));
		$flds = $this->getFields('tinymceEditing');
		$form->buildForm($flds);
		$key = sprintf('%s_%s',$_REQUEST['page_type'],$_REQUEST['page_id']);
		$form->addData($_REQUEST);
		if ($_REQUEST['pageContent']) {
			$data = $this->fetchSingle(sprintf('select * from pages where id = %d',$_REQUEST['page_id']));
			$form->addData($data);
			if (array_key_exists('pageContent',$_SESSION) && array_key_exists($key,$_SESSION['pageContent'])) {
				$form->setData('content',$_SESSION['pageContent'][$key]);
			}
		}
		else {
			if (array_key_exists('id',$_REQUEST) && $data = $this->fetchSingle(sprintf('select * from modules_by_page where id = %d',$_REQUEST['id']))) {
				$this->logMessage(__FUNCTION__,sprintf("adding data from database"),2);
				$form->addData($data);
			}
			if (array_key_exists('changeModule',$_SESSION) && array_key_exists($key,$_SESSION['changeModule']) && array_key_exists($_REQUEST['module_name'],$_SESSION['changeModule'][$key])) {
				$form->setData('content',$_SESSION['changeModule'][$key][$_REQUEST['module_name']]['content']);
			}
		}
		if (array_key_exists('saveContent',$_REQUEST)) {
			$this->logMessage(__FUNCTION__,sprintf("saving text to session and reloading"),4);
			if ($_REQUEST['pageContent'] == 1) {
				$_SESSION['pageContent'][$key] = $_REQUEST['content'];
				$form = new Forms();
				$form->setHtml('%%content%%');
			}
			else {
				if (!array_key_exists($_REQUEST['module_name'],$_SESSION['changeModule'][$key])) {
					$sql = sprintf('select p.*, t.module_function, m.classname, m.id as module_id from modules_by_page p left join fetemplates t on t.id = p.fetemplate_id left join modules m on m.id = t.module_id where p.page_type = "%s" and p.page_id = %d and p.module_name = "%s"',$_REQUEST['page_type'],$_REQUEST['page_id'],$_REQUEST['module_name']);
					if (!$module = $this->fetchSingle($sql))
						$this->logMessage(__FUNCTION__,sprintf('retrieve info failed [%s] request [%s]',$sql,print_r($_REQUEST,true)),1,true);
					$_SESSION['changeModule'][$key][$_REQUEST['module_name']] = $module;
				}
				$_SESSION['changeModule'][$key][$_REQUEST['module_name']]['content'] = $_REQUEST['content'];
				$form->init($this->getTemplate('tinymceResult'));
				$flds = $this->getFields('tinymceResult');
				$form->buildForm($flds);
				$_REQUEST['field'] = 'content';
				$_REQUEST['page_id'] = $data['id'];
			}
			$form->addData($_REQUEST);
		}
		return $this->ajaxReturn(array('status'=>'true','html'=>$form->show()));
	}

    /**
     * @param $data
     * @return array|false|mixed|string|string[]
     * @throws phpmailerException
     */
    function changeModule($data = array()) {
		//$this->logMessage(__FUNCTION__,sprintf('this [%s]',print_r($this,true)),1);
		$flds = $this->getFields('changeModule');
		$frm = new Forms();
		$frm->buildForm($flds);
		$frm->addData(array('fetemplate_id'=>0,'module_id'=>0,'state'=>0,'folder_id'=>0,'content'=>''));	// disabled fields from form are not returned, default to none
		$frm->addData($_REQUEST);
		$status = $frm->validate();
		if ($this->isAjax()) {
			$session_key = sprintf('%s_%d',$_REQUEST['page_type'],$_REQUEST['page_id']);
			$tmp = $_REQUEST['module_name'];
			if (!array_key_exists('include_subfolders',$_REQUEST)) $_REQUEST['include_subfolders'] = 0;
			$this->logMessage(__FUNCTION__,sprintf('reset test [%s][%s]',$session_key,$tmp),4);
			if (($_REQUEST['reset'] == 1 && array_key_exists($tmp,$_SESSION['changeModule'][$session_key])) || array_key_exists($tmp,$_SESSION['changeModule'][$session_key])) {
				if (array_key_exists($tmp,$_SESSION['changeModule'][$session_key])) {
					$data = $_SESSION['changeModule'][$session_key][$tmp];
					$this->logMessage(__FUNCTION__,sprintf('reset back to session [%s]',$tmp),2);
				}
			}
			else {
				$sql = sprintf('select p.*, t.module_function, m.classname, m.id as module_id from modules_by_page p, fetemplates t, modules m where m.id = t.module_id and t.id = p.fetemplate_id and p.page_type = "%s" and p.id = %d',$_REQUEST['page_type'],$_REQUEST['id']);
				$sql = sprintf('select p.*, t.module_function, m.classname, m.id as module_id from modules_by_page p left join fetemplates t on t.id = p.fetemplate_id left join modules m on m.id = t.module_id where p.page_type = "%s" and p.id = %d',$_REQUEST['page_type'],$_REQUEST['id']);
				if (!($data = $this->fetchSingle($sql))) {
					//
					//	nothing to revert to
					//
					$data = $frm->getAllData();	//$_REQUEST;
					$data['module_id'] = 0;
					$this->logMessage(__FUNCTION__,sprintf('nothing to reset back to [%s]',$sql),2);
				}
			}
			if (array_key_exists('changeModule',$_REQUEST) && $_REQUEST['changeModule'] == 1) {
				$data = $frm->getAllData();	//$_REQUEST;
				$data['changeModule'] = 0;
			}
			if (array_key_exists('changeModule',$_REQUEST) && $_REQUEST['changeModule'] == 2) {
				$data = $frm->getAllData();	//$_REQUEST;
				if (array_key_exists('query_linked',$data) && $data['query_linked'] != 0) {
					//
					//	we are only allowing 1 search related item / page for now
					//
					$ct = $this->fetchScalar(sprintf('select count(0) from modules_by_page where page_id = %d and query_linked != 0 and id != %d',$_REQUEST['page_id'],$_REQUEST['id']));
					$key = sprintf('%s_%d',$_REQUEST['page_type'],$_REQUEST['page_id']);
					if (array_key_exists($key,$_SESSION['changeModule'])) {
						foreach($_SESSION['changeModule'][$key] as $subkey=>$tester) {
							if (array_key_exists('query_linked',$tester) && $tester['query_linked'] != 0 && $subkey != $_REQUEST['module_name']) {
								$ct += 1;
							}
						}
					}
					if ($ct > 0) {
						$this->addError('Only 1 Query Related module is allowed on a page');
						if ($this->isAjax())
							return $this->ajaxReturn(array('status'=>'false','html'=>$frm->show()));
						else
							return $frm->show();
					}
				}
				$data['changeModule'] = 0;
				$this->logMessage(__FUNCTION__,sprintf('writing to session [%s]',print_r($data,true)),3);
				$_SESSION['changeModule'][sprintf('%s_%d',$data['page_type'],$data['page_id'])][$data['module_name']] = $data;
			}
		}
		$this->logMessage(__FUNCTION__,sprintf('data [%s] request [%s]',print_r($data,true),print_r($_REQUEST,true)),1);
		$form = new Forms();
		$form->init($this->getTemplate('changeModule'),array('name'=>'changeModule','method'=>'post','action'=>'changeModule'));
		$fields = $this->getFields('changeModule');
		$sql = sprintf("SELECT t.module_id FROM fetemplate_placement p, fetemplates t WHERE p.module_name='%s' and t.id = p.fetemplate_id",$data['module_name']);
		if ($data['page_type'] == 'T')
			$sql = sprintf("SELECT t.module_id FROM fetemplate_placement p, fetemplates t, templates t1 WHERE t1.id = %d and p.template_id = t1.template_id and p.module_name='%s' and t.id = p.fetemplate_id and t.internal_use = 0",$data['page_id'],$data['module_name']);
		else
			$sql = sprintf("SELECT t.module_id FROM fetemplate_placement p, fetemplates t, templates t1, pages p1 WHERE p1.id = %d and t1.template_id = p1.template_id and p.template_id = t1.template_id and p.module_name='%s' and t.id = p.fetemplate_id and t.internal_use = 0",$data['page_id'],$data['module_name']);
		$placement = $this->fetchScalarAll($sql);
		$this->logMessage(__FUNCTION__,sprintf('new code kicking in? [%s] [%d]',$sql,count($placement)),1);
		if (count($placement) > 0) {
			$fields['fetemplate_id']['sql'] = sprintf("SELECT t.id, t.module_name FROM fetemplate_placement p, fetemplates t WHERE p.module_name='%s' and t.id = p.fetemplate_id and t.module_id = %d",$data['module_name'],$data['module_id']);
			$fields['module_id']['sql'] = sprintf('select id, title from modules m where id in (%s) and m.enabled = 1 and m.frontend = 1 order by 2',implode(",",$placement));
		}
		else
			$fields['fetemplate_id']['sql'] = sprintf('select id,module_name from fetemplates where module_id = %d and internal_use = 0 order by module_name',$data['module_id']);
		$fields['folder_id']['options'] = $this->buildFolderList($data['module_id']);
		$form->buildForm($fields);
		$form->addData($data);
		if ($this->isAjax())
			return $this->ajaxReturn(array('status'=>'true','html'=>$form->show()));
		else
			return $form->show();
	}

    /**
     * @param $classId
     * @param $required
     * @return array|void
     * @throws phpmailerException
     */
    function buildFolderList($classId, $required=false) {
		$this->logMessage(__FUNCTION__,sprintf('classId [%s]',$classId),2);
		if ($className = $this->fetchScalar(sprintf('select classname from modules where id = %d and frontend = 1',$classId))) {
			switch($className) {
				case 'newsletter':
					$sql = 'select * from newsletter_folders order by left_id';
					break;
				case 'menu':
					$sql = 'select * from content order by left_id';
					break;
				case 'blog':
					$sql = 'select * from blog_folders order by left_id';
					break;
				case 'advert':
					$sql = 'select * from advert_folders order by left_id';
					break;
				case 'coupons':
					$sql = 'select * from coupon_folders order by left_id';
					break;
				case 'gallery':
					$sql = 'select * from gallery_folders order by left_id';
					break;
				case 'artist':
				case 'calendar':
				case 'members':
					$sql = 'select * from members_folders order by left_id';
					break;
				case 'news':
					$sql = 'select * from news_folders order by left_id';
					break;
				case 'product':
					$sql = 'select * from product_folders order by left_id';
					break;
				case 'stores':
					$sql = 'select * from store_folders order by left_id';
					break;
				case 'polls':
					$sql = 'select * from poll_folders where enabled = 1 order by left_id';
					break;
				case 'rss':
					$sql = 'select * from rss_folders where enabled = 1 order by left_id';
					break;
				case 'htmlForms':
				case 'feeds':
				case 'custom':
					$sql = '';
					break;
				default:
					$sql = '';
					break;
			}
			$options = array();
			if (strlen($sql) > 0) {
				$folders = $this->fetchAll($sql);
				if (!$required)
					$options[0] = '-none-';
				foreach($folders as $folder) {
					$options[$folder['id']] = sprintf('%s%s',str_repeat('&nbsp;',($folder['level']-1)*2),htmlspecialchars($folder['title']));
				}
			}
			$this->logMessage(__FUNCTION__,sprintf('classId [%s] className[%s] sql [%s]',$classId,$className,$sql),2);
			return $options;
		}
	}

    /**
     * @return array|false|mixed
     * @throws phpmailerException
     */
    function getModule() {
		if (is_array($this->m_module) && count($this->m_module) > 0) {
				$sql = sprintf('select %d as folder_id, fe.*, 0 as include_subfolders from fetemplates fe where fe.id = %d',array_key_exists('folder_id',$this->m_module) ? $this->m_module['folder_id'] : 0,$this->m_module['fetemplate_id']);
		}
		else {
			if ($this->m_moduleId == 0)
				$sql = sprintf('select %d as folder_id, fe.* from fetemplates fe where fe.id = %d',array_key_exists('folder_id',$this->m_module) ? $this->m_module['folder_id'] : 0,array_key_exists('fetemplate_id',$this->m_module) ? $this->m_module['fetemplate_id'] : 0);
			else
				$sql = sprintf('select p.*, fe.* from modules_by_page p, fetemplates fe where p.id = %d and fe.id = p.fetemplate_id',$this->m_moduleId);
		}
		if (!$module = $this->fetchSingle($sql)) {
			$this->logMessage(__FUNCTION__,sprintf("Missing module id [%d] class [%s] sql [$sql]", $this->m_moduleId,get_class($this),$sql),1,true);
			return false;
		}
		else {
			$this->parseOptions($module['misc_options']);
			$module['options'] = $this->m_options;
			$module = $this->processOverrides($module);
		}
		if (strlen($module['records']) > 0) {
			$tmp = explode(',',$module['records']);
			if (count($tmp) > 1) {
				$module['limit'] = $tmp[0]*$tmp[1];
				$module['rows'] = $tmp[0];
				$module['columns'] = $tmp[1];
			}
			else {
				$module['limit'] = $tmp[0];
				$module['rows'] = 1;
				$module['columns'] = $tmp[0];
			}
		}
		if ($this->hasOption('folder_id')) {	// && $module['folder_id'] == 0) {
			$module['folder_id'] = $this->getOption('folder_id');
			$this->m_module['folder_id'] = $this->getOption('folder_id');
		}
		if (array_key_exists('options',$this->m_module) && array_key_exists('options',$module) && is_array($this->m_module['options']) && is_array($module['options']))
			$this->m_module['options'] = array_merge($module['options'],$this->m_module['options']);
		$module = array_merge($module,$this->m_module);
		$this->m_options = $module['options'];
		$this->logMessage(__FUNCTION__,sprintf("return [%s]",print_r($module,true)),3);
		return $module;
	}

    /**
     * @param $module
     * @return mixed
     * @throws phpmailerException
     */
    function processOverrides($module) {
		if (array_key_exists('f_id',$_REQUEST)) {
			if ($module['template_allow_override'] || (array_key_exists('allow_override',$this->m_module) && $this->m_module['allow_override'] != 0)) {
				$module['folder_id'] = $_REQUEST['f_id'];
				$this->m_module['folder_id'] = $_REQUEST['f_id'];
				$this->logMessage(__FUNCTION__,sprintf('folder overriden to [%d]',$_REQUEST['f_id']),2);
			}
		}
		if (array_key_exists('folder_id',$_REQUEST)) {
			if ($module['template_allow_override'] || (array_key_exists('allow_override',$this->m_module) && $this->m_module['allow_override'] != 0)) {
				$module['folder_id'] = $_REQUEST['folder_id'];
				$this->m_module['folder_id'] = $_REQUEST['folder_id'];
				$this->logMessage(__FUNCTION__,sprintf('folder overriden to [%d]',$_REQUEST['folder_id']),2);
			}
		}
		//
		//	modify module parms from options if present [for internal functions]
		//
		foreach($module['options'] as $key=>$value) {
			if(array_key_exists($key,$module)) {
				$module[$key] = $value;
			}
		}
		return $module;
	}

    /**
     * @return false|string|void
     * @throws phpmailerException
     */
    function getConfigInfo() {
		if (array_key_exists('configuration',$_REQUEST)) {
			$select = new select();
			$select->addAttributes(array('name'=>'configuration','required'=>false,'value'=>$_REQUEST['configuration']));
			$config = $this->config->m_fields;
			$opts = array();
			$opts[''] = '';
			foreach($config as $key=>$fields) {
				if (!(array_key_exists('options',$fields) && array_key_exists('private',$fields['options']) && $fields['options']['private'] == true))
					$opts[$key]=$key;
			}
			asort($opts);
			$select->addOptions($opts);
			return $this->ajaxReturn(array('status'=>'true','html'=>$select->show()));
		}
	}

    /**
     * @param $className
     * @return array|int|mixed|null
     * @throws phpmailerException
     */
    function getClassId($className) {
		if (array_key_exists($className,$this->m_classIds))
			return $this->m_classIds[$className];
		else {
			$this->logMessage(__FUNCTION__,sprintf("request for an invalid class [%s] classes [%s]",$className,print_r($this->m_classIds,true)),1,true);
		}
		return 0;
	}

    /**
     * @param $template_id
     * @param $variable
     * @param $options
     * @param $location
     * @return array
     * @throws phpmailerException
     */
    protected function subForms($template_id, $variable, $options, $location) {
		$this->logMessage(__FUNCTION__,sprintf('(%d,[%s],[%s],[%s])',$template_id,$variable,print_r($options,true),$location),3);
		$sql = sprintf('select s.location, s.variable, s.submodule_id as fetemplate_id, t.module_function, t.module_id, c.classname from sub_templates s, fetemplates t, modules c where s.fetemplate_id = %d and t.id = s.submodule_id and c.id = t.module_id and ("%s" = "" or "%s" = location)',$template_id,$location,$location);
		$templates = $this->fetchAll($sql);
		$this->logMessage(__FUNCTION__,sprintf('sql [%s] count [%d]',$sql,count($templates)),3);
		$return = array();
		foreach($templates as $key=>$template) {
			if ((strlen($variable) == 0 || ($template['variable'] == $variable)) && $location = $template['location']) {
				foreach($options as $optkey=>$optvalue) {
					$template[$optkey] = $optvalue;
				}
				$class = new $template['classname'](null,$template);
				$html = $class->{$template['module_function']}();
				$return[$template['variable']] = $html;
			}
		}
		return $return;
	}

    /**
     * @param $totalRecs
     * @param $perPage
     * @param $currPage
     * @param array $templates
     * @param array $data
     * @return array|mixed|string|string[]
     * @throws phpmailerException
     */
    public function pagination($totalRecs, $perPage, &$currPage, $templates = array(), $data = array()) {
		if (!is_array($templates) || count($templates) == 0)
			$templates = array(
				'prev'=>ADMIN.'frontend/forms/paginationPrev.html',
				'next'=>ADMIN.'frontend/forms/paginationNext.html',
				'pages'=>ADMIN.'frontend/forms/paginationPage.html',
				'wrapper'=>ADMIN.'frontend/forms/paginationWrapper.html',
				'spacer'=>ADMIN.'frontend/forms/paginationSpacer.html');
		return parent::pagination($totalRecs, $perPage, $currPage, $templates, $data);
	}

    /**
     * @param $sql
     * @param $module
     * @param $recordCount
     * @param $pageNum
     * @return array|mixed|string|string[]
     * @throws phpmailerException
     */
    protected function getPagination(&$sql, $module, &$recordCount, &$pageNum = 0) {
		$this->logMessage(__FUNCTION__,sprintf('sql [%s] module[%s] count [%s]',print_r($sql,true),print_r($module,true),$recordCount),3);
		$pagination = "";
		if ($module['rows'] > 0) {
			if (array_key_exists('pagenum',$_REQUEST)) 
				$pageNum = $_REQUEST['pagenum'];
			else
				$pageNum = 1;	// no 0 based calcs
			if ($pageNum <= 0) $pageNum = 1;
			$perPage = $module['limit'];
			if (array_key_exists('pager',$_REQUEST)) $perPage = $_REQUEST['pager'];
			if ((is_null($sql) || $sql == "")) {
				$recs = array();
				for($i = 0; $i < $recordCount; $i++) {
					$recs[] = array($i=>array());
				}
			}
			else
				$recs = $this->fetchAll($sql);
			$recordCount = array_key_exists('maxUnpaged',$module) ? min($module['maxUnpaged'],count($recs)) : count($recs);
			if ($this->hasOption('pagination')) {
				$tmp = explode('|',$this->getOption('pagination'));
				$this->logMessage(__FUNCTION__,sprintf("looking for m_dir this [%s] module [%s]",print_r($this,true),print_r($module,true)),4);
				if (array_key_exists('m_dir',$module)) {
					$fldr = $module['m_dir'];
					$paging = array('prev'=>$fldr.$tmp[0],'next'=>$fldr.$tmp[1],'pages'=>$fldr.$tmp[2],'wrapper'=>$fldr.$tmp[3],'spacer'=>$fldr.$tmp[4]);
				}
				elseif (array_key_exists('m_dir',$module['options'])) {
					$fldr = $module['options']['m_dir'];
					$paging = array('prev'=>$fldr.$tmp[0],'next'=>$fldr.$tmp[1],'pages'=>$fldr.$tmp[2],'wrapper'=>$fldr.$tmp[3],'spacer'=>$fldr.$tmp[4]);
				}
				else
					$paging = array(
							'prev'=>strlen($tmp[0])>0 ? $this->m_dir.$tmp[0] : "",
							'next'=>strlen($tmp[1])>0 ? $this->m_dir.$tmp[1] : "",
							'pages'=>strlen($tmp[2])>0 ? $this->m_dir.$tmp[2] : "",
							'wrapper'=>strlen($tmp[3])>0 ? $this->m_dir.$tmp[3] : "",
							'spacer'=>strlen($tmp[4])>0 ? $this->m_dir.$tmp[4] : "");
				$pagination = $this->pagination($recordCount, $perPage, $pageNum, $paging, $module);
			}
			else 
				$pagination = $this->pagination($recordCount, $perPage, $pageNum, null, $module);
			$start = ($pageNum-1)*$perPage;
			if (!is_array($sql))
				$sql .= sprintf(' limit %d,%d',$start,$perPage);
			else
				$sql  = array_slice($sql,$start,$perPage);
		}
		else {
			$this->logMessage(__FUNCTION__,'no paging as per config',2);
			if (!is_array($sql))
				$sql .= sprintf(' limit %d',$module['limit']);
			else
				$sql  = array_slice($sql,0,$module['limit']);
		}
		$this->logMessage(__FUNCTION__,sprintf('return [%s] sql [%s] count [%d]',$pagination,print_r($sql,true),$recordCount),3);
		return $pagination;
	}

    /**
     * @param $data
     * @param $module
     * @param $recordCount
     * @param $pageNum
     * @return array
     * @throws phpmailerException
     */
    protected function getPaginationFromData($data, $module, &$recordCount, &$pageNum = 0) {
		$sql = null;
		$this->logMessage(__FUNCTION__,sprintf("passing data [%s] recordcount [%s] pagenum [%s]", print_r($data,true), $recordCount, $pageNum),1);
		$html = $this->getPagination($sql, $module, $recordCount, $pageNum);
		$perPage = $module['limit'];
		$start = ($pageNum-1)*$perPage;
		$data  = array_slice($data,$start,$perPage);
		$this->logMessage(__FUNCTION__,sprintf("returning html [%s] recordcount [%s] pagenum [%s] data [%s]", print_r($html,true), $recordCount, $pageNum, print_r($data,true)),1);
		return array("html"=>$html, "data"=>$data);
	}
}

/**
 * Handles ajax functionality on the frontend
 */
class feAjax extends Frontend {

	/**
	 * @throws Exception
	 */
	function __construct() {
		parent::__construct();
		$this->m_viaAjax = true;
	}

    /**
     * @return false|string|void
     * @throws phpmailerException
     */
    function show() {
#		if (get_magic_quotes_gpc()) {
#			$this->processSlashes();
#		}
		if (count($_GET) > 0) {
			$this->logMessage(__FUNCTION__,sprintf('GET [%s]',print_r($_GET,1)),2);
		}
		if (count($_POST) > 0) {
			$this->logMessage(__FUNCTION__,sprintf('POST [%s]',print_r($_POST,1)),2);
		}
		if (count($_REQUEST) > 0) {
			$this->logMessage(__FUNCTION__,sprintf('REQUEST [%s]',print_r($_REQUEST,1)),2);
		}
		if (count($_SESSION) > 0) {
			$this->logMessage(__FUNCTION__,sprintf('SESSION [%s]',print_r($_SESSION,1)),2);
		}
		$class = array_key_exists('module',$_REQUEST) ? $_REQUEST['module'] : '';
		if ((int)$class > 0) {
			$class = $this->fetchScalar(sprintf('select classname from modules where id = %d and enabled = 1 and frontend = 1',$class));
		}
		$function = array_key_exists('ajax',$_REQUEST) ? $_REQUEST['ajax'] : '';		
		$this->logMessage(__FUNCTION__, sprintf('Ajax request for %s::%s',$class,$function), 1);
		if (strlen($class) == 0) {
			if (method_exists($this,$function))
				return $this->{$function}();
			else {
				$err = sprintf('Invalid request: %s',$function);
				$this->logMessage(__FUNCTION__, $err, 1, DEV);
				$this->addError($err);
				return $this->ajaxReturn(array('status'=>'false','html'=>$err));
			}
		}
		else {
			if (class_exists($class)) {
				$class = new $class(0);
				$class->setAjax(true);
				if (method_exists($class, $function)) {
					return $class->{$function}();
				}
				else {
					$err = sprintf('Class [%s] Function [%s] did not exist',get_class($class),$function);
					$this->addError($err);
					$this->logMessage(__FUNCTION__, $err, 1, DEV);
					return $this->ajaxReturn(array('status'=>'false','html'=>'Class or Function did not exist'));
				}
			}
		}
	}

    /**
     * @return false|string
     * @throws phpmailerException
     */
    function loadProvinces() {
		if (array_key_exists('c_id',$_REQUEST)) {
			$c_id = $_REQUEST['c_id'];
			$select = new provinceSelect(false);
			$select->addAttributes(array(
				'country_id'=>$c_id,
				'id'=>'province_id',
				'required'=>true,
				'name'=>array_key_exists('name',$_REQUEST) ? $_REQUEST['name'] : 'province_id',
				'class'=>array_key_exists('class',$_REQUEST) ? $_REQUEST['class'] : ''
			));
			unset($_REQUEST["ajax"]);
			unset($_REQUEST["c_id"]);
			foreach($_REQUEST as $key=>$value) {
				$select->addAttribute($key,$value);
			}
			return $this->ajaxReturn(array(
					'status'=>'true',
					'html'=>$select->show()
				));
		}
		else return $this->ajaxReturn(array('status'=>false));
	}

	/**
	 * @param $fromEditMode
	 * @return array|false|mixed|string|string[]|void
	 * @throws phpmailerException
	 */
    function render($fromEditMode = false) {	// compatability only
		$t_id = $_REQUEST['t_id'];
		$sql = sprintf('select t.*, t.id as fetemplate_id, m.classname from fetemplates t, modules m where t.id = %d and m.id = t.module_id',$t_id);
		$return = array('html'=>'','status'=>false);
		$this->logMessage(__FUNCTION__,sprintf('sql [%s]',$sql),1);
		if ($module = $this->fetchSingle($sql)) {
			foreach($_REQUEST as $key=>$value) {
				if (!array_key_exists($key,$module))
					$module[$key] = $value;
			}
			$class = new $module['classname']($module['id'],$module);
			$return['html'] = $class->{$module['module_function']}();
			$return['status'] = true;
			if ($this->m_viaAjax)
				return $this->ajaxReturn($return);
			else
				return $return;
		}
		else
			if ($this->m_viaAjax)
				return $this->ajaxReturn($return);
			else
				return $return;
	}

    /**
     * @return false|string
     * @throws phpmailerException
     */
    function dragAndDrop() {
		$status = false;
		$html = '';
		if (array_key_exists('t_id',$_REQUEST)) {
			//
			//	verify the module is eligible for this location
			//
			$t_id = $_REQUEST['t_id'];
			$from = $_REQUEST['from'];
			$to = $_REQUEST['to'];
			$tmp = explode('_',$t_id);
			if ($tmp[0] == 'T') {
				$sql = sprintf("SELECT t.module_id FROM fetemplate_placement p, fetemplates t, templates t1 WHERE t1.id = %d and p.template_id = t1.template_id and p.module_name='%s' and t.id = p.fetemplate_id",$tmp[1],$_REQUEST['to']);
			}
			else {
				$sql = sprintf("SELECT distinct t.id FROM fetemplate_placement p, fetemplates t, templates t1, pages p1 WHERE p1.id = %d and t1.template_id = p1.template_id and p.template_id = t1.template_id and p.module_name='%s' and t.id = p.fetemplate_id",$tmp[1],$_REQUEST['to']);
			}
			$placement = $this->fetchScalarAll($sql);
			$this->logMessage(__FUNCTION__,sprintf('placement check [%s] templates [%s]',$sql,print_r($placement,true)),1);
			if (!array_key_exists($from,$_SESSION['changeModule'][$t_id])) {
				$tmp = explode('_',$t_id);
				if ($tmp[0] == 'T')
					$sql = sprintf('select p.*, t.module_function, m.classname, m.id as module_id from modules_by_page p, fetemplates t, modules m where m.id = t.module_id and t.id = p.fetemplate_id and p.page_type = "T" and p.page_id = %d and p.module_name = "%s"',$tmp[1],$from);
				else
					$sql = sprintf('select p.*, t.module_function, m.classname, m.id as module_id from modules_by_page p left join fetemplates t on t.id = p.fetemplate_id left join modules m on m.id = t.module_id where p.page_type = "P" and p.page_id = %d and p.module_name = "%s"',$tmp[1],$from);
				$this->logMessage(__FUNCTION__,sprintf('fetching module [%s] sql [%s]',$from,$sql),3);
				$module = $this->fetchSingle($sql);
				$_SESSION['changeModule'][$t_id][$from] = $module;
			}
			if (count($placement) > 0 && $_SESSION['changeModule'][$t_id][$from]['content_area'] == 0) {
				//
				//	this is a restricted drop location - make sure the module being dropped is elligible
				//
				$old = $_SESSION['changeModule'][$t_id][$from];
				$this->logMessage(__FUNCTION__,sprintf('restricted test old [%s] placement [%s]',print_r($old,true),print_r($placement,true)),1);
				if (strpos($old['fetemplate_id'].'|',implode('|',$placement).'|') === false)
					return $this->ajaxReturn(array('status'=>false,'code'=>'alert("That module is not allowed in this location");'));
			}
			$_SESSION['changeModule'][$t_id][$to] = $_SESSION['changeModule'][$t_id][$from];
			$_SESSION['changeModule'][$t_id][$to]['module_name'] = $to;
			$_SESSION['changeModule'][$t_id][$from]['fetemplate_id'] = 0;
			$_SESSION['changeModule'][$t_id][$from]['content_area'] = 0;
			$_SESSION['changeModule'][$t_id][$from]['module_id'] = 0;
			$_REQUEST = $_SESSION['changeModule'][$t_id][$to];
			$_REQUEST['t_id'] = $_REQUEST['fetemplate_id'];
			if ($_SESSION['changeModule'][$t_id][$to]['content_area'] == 1) {
				$html = $_SESSION['changeModule'][$t_id][$to]['content'];
			}
			else {
				$obj = new feAjax();
				$obj->m_viaAjax = false;
				$tmp = $obj->render();
				$this->logMessage(__FUNCTION__,sprintf('render result [%s]',print_r($tmp,true)),1);
				$html = $tmp['html'];
			}
			$status = true;
		}
		else {
		}
		return $this->ajaxReturn(array('html'=>$html,'status'=>$status));
	}

	/**
	 * @return array|false|string
	 * @throws phpmailerException
	 */
    function captcha() {
		return Ajax::captcha();
	}

}
?>
