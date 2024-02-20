<?php

use Ctct\ConstantContact;
use Ctct\Components\Contacts\Contact;
use Ctct\Components\Contacts\ContactList;
use Ctct\Components\Contacts\EmailAddress;
use Ctct\Exceptions\CtctException;

class newsletter extends Frontend {

	private $m_dir = '';
	protected $module;
	public function __construct($id,$module = array()) {
		parent::__construct();
		$this->m_dir = ADMIN.'frontend/forms/newsletter/';
		$this->m_moduleId = $id;
		$this->m_module = $module;
		$this->logMessage("__construct",sprintf("($id,[%s])",print_r($module,true)),2);
	}

	function formatData($data) {
		$tmp = new image();
		if ($data['image1'] != '') {
			$tmp->addAttributes(array('src'=>$data['image1'],'alt'=>htmlentities(strip_tags($data['name']))));
			$data['img_image1'] = $tmp->show();
		}
		if ($data['image2'] != '') {
			$tmp->addAttributes(array('src'=>$data['image2'],'alt'=>htmlentities(strip_tags($data['name']))));
			$data['img_image2'] = $tmp->show();
		}
		$data['url'] = $this->getUrl('page',$data['page_id']);
		$this->logMessage('formatData',sprintf('return [%s]',print_r($data,true)),4);
		return $data;
	}

	function formatFolder($data) {
		$tmp = new image();
		if (array_key_exists('image',$data) && $data['image'] != '') {
			$tmp->addAttributes(array('src'=>$data['image'],'alt'=>htmlentities(strip_tags($data['title']))));
			$data['img_image'] = $tmp->show();
		}
		if (array_key_exists('rollover_image',$data) && $data['rollover_image'] != '') {
			$tmp->addAttributes(array('src'=>$data['rollover_image'],'alt'=>htmlentities(strip_tags($data['title']))));
			$data['img_rollover_image'] = $tmp->show();
		}
		return $data;
	}

	function signup() {
		if (!$module = parent::getModule())
			return "";
		$outer = new Forms();
		$outer->init($this->m_dir.$module['outer_html']);
		$inner = new Forms();
		$inner->setModule($module);
		$inner->init($this->m_dir.$module['inner_html']);
		$flds = $inner->buildForm($this->config->getFields($module['configuration']));
		if ($module["folder_id"] > 0) {
			if ($f = $this->fetchSingle(sprintf("select * from newsletter_folders where id = %d",$module["folder_id"]))) {
				$outer->addData($this->formatFolder($f));
			}
		}
		if (count($_POST) > 0 && array_key_exists('newsletterSignup',$_POST)) {
			$inner->addData($_POST);
			if ($inner->validate()) {
				$id = (int)$this->fetchScalar(sprintf('select id from subscriber where email = "%s" and deleted = 0 and enabled = 1',$_POST['email']));
				$valid = true;
				$this->beginTransaction();
				if ($id == 0) {
					$form = array();
					foreach($flds as $key=>$fld) {
						if (!(array_key_exists('database',$fld) && $fld['database'] == false)) {
							$form[$fld['name']] = $inner->getData($fld['name']);
						}
					}
					$form['created'] = date('c');
					$form['created_ip'] = $_SERVER['REMOTE_ADDR'];
					$form['enabled'] = 1;
					$form['deleted'] = 0;
					$stmt = $this->prepare(sprintf('insert into subscriber(%s) values(%s)', implode(', ',array_keys($form)), str_repeat('?, ', count($form)-1).'?'));
					$stmt->bindParams(array_merge(array(str_repeat('s', count($form))),array_values($form)));
					$valid = $valid && $stmt->execute();
					$id = $this->insertId();
				}
				if ($module['folder_id'] != 0 && $valid) {
					$f_id = (int)$this->fetchScalar(sprintf('select id from subscriber_by_folder where folder_id = %d and subscriber_id = %d',$module['folder_id'],$id));
					if ($f_id == 0) {
						$stmt = $this->prepare('insert into subscriber_by_folder(folder_id,subscriber_id) values(?,?)');
						$stmt->bindParams(array('dd',$module['folder_id'],$id));
						$valid = $valid && $stmt->execute();
					}
				}
				if ($valid) {
					$this->commitTransaction();
					if (strlen($module['parm1']) > 0)
						$inner->init($this->m_dir.$module['parm1']);
					$inner->addFormSuccess('You were successfully subscribed');
					if (method_exists('custom','postNewsletterSignup')) {
						$this->config->postNewsletterSignup($id,$this,$module);
					}
				}
				else {
					$this->rollbackTransaction();
					$inner->addFormError('An Error Occurred');
				}
			}
		}
		$outer->addTag('form',$inner->show(),false);
		$subdata = $this->subForms($module['fetemplate_id'],'',array(),'outer');
		foreach($subdata as $key=>$value) {
			$outer->addTag($key,$value,false);
		}
		return $outer->show();
	}

	function unsubscribe() {
		if (!$module = parent::getModule())
			return "";
		$this->logMessage("unsubscribe",sprintf("module [%s]",print_r($module,true)),3);
		$outer = new Forms();
		$outer->init($this->m_dir.$module['outer_html']);
		$inner = new Forms();
		$inner->init($this->m_dir.$module['inner_html']);
		$flds = $inner->buildForm($this->config->getFields($module['configuration']));
		$r = $this->checkArray("r",$_REQUEST);
		$n = $this->checkArray("n",$_REQUEST);
		$s = $this->checkArray("s",$_REQUEST);
		if ($rec = $this->fetchSingle(sprintf("select s.*, s.id as sub_id from subscriber s, newsletter_batch_subscriber b where b.batch_id=%d and b.subscriber_id=%d and b.random_id=%d and s.id = b.subscriber_id", $n, $s, $r))) {
			$inner->addData($rec);
		}
		if (count($_REQUEST) > 0 && array_key_exists('newsletterUnsubscribe',$_REQUEST)) {
			$inner->addData($_REQUEST);
			if ($inner->validate()) {
				if ($this->checkArray("sub_id",$_REQUEST))
					$this->execute(sprintf('update subscriber set enabled = 0, unsubscribed = now(), unsubscribed_ip = "%s" where id = %d and email = "%s" and enabled = 1',$_SERVER['REMOTE_ADDR'],$_REQUEST["sub_id"],$_REQUEST['email']));
				else
					$this->execute(sprintf('update subscriber set enabled = 0, unsubscribed = now(), unsubscribed_ip = "%s" where email = "%s" and enabled = 1',$_SERVER['REMOTE_ADDR'],$_REQUEST['email']));
				$inner->init($this->m_dir.$module['parm1']);
			}
		}
		$outer->addTag('form',$inner->show(),false);
		if ($this->isAjax())
			return $this->ajaxReturn(array('status'=>true,'html'=>$outer->show()));
		else
			return $outer->show();
	}

	function tracker() {
		$blank = './images/blank.gif';
		$mode = array_key_exists('mode',$_REQUEST) ? $_REQUEST['mode'] : '';
		$nl = $this->fetchSingle(sprintf('select * from newsletter n, newsletter_batch b where b.id = %d and n.id = b.newsletter_id',array_key_exists('nl_id',$_REQUEST) ? $_REQUEST['nl_id'] : 0));
		switch($mode) {
		case 'view':
			if (array_key_exists('sub_id',$_REQUEST) && array_key_exists('rand',$_REQUEST)) {
				$this->execute(sprintf('update newsletter_batch_subscriber set views = views+1 where batch_id = %d and subscriber_id = %d and random_id = %d',
					$_REQUEST['nl_id'],$_REQUEST['sub_id'],$_REQUEST['rand']));
			}
			break;
		case 'unsub':
			if (array_key_exists('sub_id',$_REQUEST) && array_key_exists('rand',$_REQUEST)) {
				$this->execute(sprintf('update newsletter_batch_subscriber set unsubscribe = unsubscribe+1 where batch_id = %d and subscriber_id = %d and random_id = %d',
					$_REQUEST['nl_id'],$_REQUEST['sub_id'],$_REQUEST['rand']));
				if ($nl['testing'] == 0) {
					if ($id = $this->fetchScalar(sprintf('select subscriber_id from newsletter_batch_subscriber where batch_id = %d and subscriber_id = %d and random_id = %d',
								$_REQUEST['nl_id'],$_REQUEST['sub_id'],$_REQUEST['rand'])))
						$this->execute(sprintf('update subscriber set enabled = 0, unsubscribed = now(), unsubscribed_ip = "%s" where id = "%d" and enabled = 1',$_SERVER['REMOTE_ADDR'],$id));
				}
			}
			if ($nl['unsubscribe'] > 0) {
				$p = $this->fetchSingle(sprintf('select * from content where id = %d',$nl['unsubscribe']));
				$url = sprintf('%s?n=%s&s=%d&r=%d',$this->getUrl('menu',$nl['unsubscribe'],$p),$_REQUEST['nl_id'],$_REQUEST['sub_id'],$_REQUEST['rand']);
			}
			else $url = '/';
			ob_clean();
			header('Location: '.$url);
			exit;
			break;
		default:
			break;
		}
		ob_clean();
		header( 'Content-type: image/gif' );
		header('Content-Length: ' . filesize($blank));
		readfile($blank);
		exit;
	}

	function subscriberInfo() {
		if (!$module = parent::getModule())
			return "";
		$this->logMessage("unsubscribe",sprintf("module [%s]",print_r($module,true)),3);
		$outer = new Forms();
		$outer->init($this->m_dir.$module['outer_html']);
		$flds = $outer->buildForm($this->config->getFields($module['configuration']));
		$subscriber = array();
		if (array_key_exists('n',$_REQUEST) && array_key_exists('s',$_REQUEST) && array_key_exists('r',$_REQUEST)) {
			$subscriber = $this->fetchSingle(sprintf('select s.* from subscriber s, newsletter_batch_subscriber b where b.batch_id = %d and b.subscriber_id = %d and b.random_id = %d and s.id = b.subscriber_id',
								$_REQUEST['n'],$_REQUEST['s'],$_REQUEST['r']));
		}
		else {
		}
		$outer->addData($subscriber);
		$subdata = $this->subForms($module['fetemplate_id'],'',array(),'outer');
		foreach($subdata as $key=>$value) {
			$outer->addTag($key,$value,false);
		}
		return $outer->show();
	}

	function view() {
		if (!$module = parent::getModule())
			return "";
		$this->logMessage("view",sprintf("module [%s]",print_r($module,true)),3);
		$outer = new Forms();
		$outer->init($this->m_dir.$module['outer_html']);
		$flds = $this->config->getFields($module['configuration']);
		$inner = new Forms();
		$inner->init($this->m_dir.$module['inner_html']);
		$flds = $inner->buildForm($flds);
		$subdata = $this->subForms($module['fetemplate_id'],'',array(),'outer');
		foreach($subdata as $key=>$value) {
			$outer->addTag($key,$value,false);
		}
		return $outer->show();
	}

	function latest() {
		if (!$module = parent::getModule())
			return "";
		$this->logMessage("latest",sprintf("module [%s]",print_r($module,true)),3);
		$outer = new Forms();
		if ($module['folder_id'] > 0) {
			$folder = $this->fetchSingle(sprintf('select * from newsletter_folders where id = %d',$module['folder_id']));
			$outer->addData($this->formatFolder($folder));
			$rec = $this->fetchAll(sprintf('select * from newsletter n, newsletter_by_folder nf where nf.folder_id = %d and n.id = nf.newsletter_id %s limit %d',$folder['id'],$module['sort_by'] != '' ? 'order by '.$module['sort_by'] : '',$module['records']));
		}
		else {
			$rec = $this->fetchSingle(sprintf('select * from newsletter where id = %d',$module['nl_id']));
		}
		$outer->init($this->m_dir.$module['outer_html']);
		$flds = $this->config->getFields($module['configuration']);
		$inner = new Forms();
		$inner->init($this->m_dir.$module['inner_html']);
		$flds = $inner->buildForm($flds);
		$list = array();
		foreach($rec as $key=>$nl) {
			$inner->addData($this->formatData($nl));
			$subdata = $this->subForms($module['fetemplate_id'],'',array(),'inner');
			foreach($subdata as $key=>$value) {
				$outer->addTag($key,$value,false);
			}
			$list[] = $inner->show();
		}
		$outer->addTag('articles',implode('',$list),false);
		return $outer->show();
	}

	function constantContact() {
		if (!$module = parent::getModule())
			return "";
		$outer = new Forms();
		$outer->init($this->m_dir.$module["outer_html"]);
		$inner = new Forms();
		$inner->init($this->m_dir.$module['inner_html']);
		$flds = $inner->buildForm($this->config->getFields($module['configuration']));
		if (array_key_exists(__FUNCTION__,$_REQUEST) && $_REQUEST[__FUNCTION__] == 1) {
			$inner->addData($_REQUEST);
			$valid = $inner->validate();
			if ($inner->getData("optIn") != 1) {
				$inner->addFormError("You must agree to accept the emails to signup");
				$valid = false;
			}
			if ($valid) {
				require_once (ADMIN.'classes/Ctct/autoload.php');
				$parms = $GLOBALS["constantContact"];
				$cc = new ConstantContact($parms["apiKey"]);
				$lists = $cc->getLists($parms["apiToken"]);
				$response = $cc->getContactByEmail($parms["apiToken"],$_REQUEST['email']);
				$list_id = 0;
				foreach($lists as $key=>$list) {
					if ($list->name == $parms["apiList"]) {
						$list_id = $list->id;
					}
				}
				if ($list_id == 0) {
					$valid = false;
					$this->logMessage(__FUNCTION__,sprintf("no valid list found [%s] from [%s]",$parms["apiList"],print_r($lists,true)),1,true);
				}
				else {
					try {
						if (empty($response->results)) {
							$contact = new Contact();
							$contact->addEmail($_REQUEST['email']);
							$contact->addList($list_id);
							if (array_key_exists('firstname',$_REQUEST)) $contact->first_name = $_REQUEST['firstname'];
							if (array_key_exists('lastname',$_REQUEST)) $contact->last_name = $_REQUEST['lastname'];
							$returnContact = $cc->addContact($parms["apiToken"], $contact, true);
						}
						else {
							$contact = $response->results[0];
							$contact->addList($list_id);
							if (array_key_exists('firstname',$_REQUEST)) $contact->first_name = $_REQUEST['firstname'];
							if (array_key_exists('lastname',$_REQUEST)) $contact->last_name = $_REQUEST['lastname'];
							$returnContact = $cc->updateContact($parms["apiToken"], $contact, true);
						}
					}
					catch(CtctException $ex) {
						$this->logMessage(__FUNCTION__,sprintf("constant contact returned an error [%s]",print_r($ex,true)),1,true);
						$valid = false;
					}
				}
				if ($valid) {
					$inner->init($this->m_dir.$module['parm1']);
					$inner->addFormSuccess("You have been subscribed to the newsletter");
				}
				else $inner->addFormError("An internal error occurred. The Web Master has been notified");
			}
			else $inner->addFormError('Validation Failed');
		}
		$outer->addTag("form",$inner->show(),false);
		return $outer->show();
	}

	function getModuleInfo() {
		return parent::getModuleList(array('signup','unsubscribe','subscriberInfo','view','latest','constantContact'));
	}
}

?>