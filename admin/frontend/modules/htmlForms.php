<?php

/**
 * @param $a
 * @param $b
 * @return int
 */
function sortSearchItems($a, $b) {
	return strtotime($a['sort']) < strtotime($b['sort']) ? 1:-1;
}

/**
 * Contains html forms related functionality for frontend
 */
class htmlForms extends Frontend {
	
	private $m_dir = '';
	protected $module;

    /**
     * @param $id
     * @param $module
     * @throws phpmailerException
     */
    public function __construct($id, $module = array()) {
		parent::__construct();
		$this->m_dir = ADMIN.'frontend/forms/htmlForms/';
		$this->m_moduleId = $id;
		$this->m_module = $module;
		$this->logMessage(__FUNCTION__,sprintf("($id,[%s])",print_r($module,true)),2);
	}

    /**
     * @return array|mixed|string|string[]
     * @throws phpmailerException
     */
    public function login() {
		if (!$module = parent::getModule())
			return "";
		$this->logMessage(__FUNCTION__,sprintf("module [%s]",print_r($module,true)),3);
		$outer = new Forms();
		$outer->setModule($module);
		if ($this->isLoggedIn())
			$outer->addData($_SESSION['user']['info']);
		if ($this->isLoggedIn() && strlen($module['parm1']) > 0)
			$outer->init($this->m_dir.$module['parm1']);
		else
			$outer->init($this->m_dir.$module['outer_html']);
		$page = '';
		$subdata = $this->subForms($this->m_module['fetemplate_id'],null,array(),'outer');
		$this->logMessage(__FUNCTION__,sprintf('subforms [%s]',print_r($subdata,true)),3);
		foreach($subdata as $key=>$value) {
			$outer->addTag($key,$value,false);
		}
		if (defined('FRONTEND')) {
			if ($this->hasOption('redirect')) {
				$outer->addTag('redirect',$this->getOption('redirect'));
				if ($this->getOption("redirect") == 0) {
					if (array_key_exists('HTTP_REFERER',$_SERVER) && strlen($_SERVER["HTTP_REFERER"]) > 0 && strpos($_SERVER["HTTP_REFERER"],$_SERVER["REQUEST_URI"]) === false) {
						$this->logMessage(__FUNCTION__,"set referer to ".$_SERVER["HTTP_REFERER"],3);
						$_SESSION["login_referer"] = $_SERVER["HTTP_REFERER"];
					}
				}
				$page = array_key_exists('login_referer',$_SESSION) ? $_SESSION["login_referer"] : '';
				$outer->addTag('redirect',$page);
			}
		}
		$flds = $outer->buildForm($this->config->getFields($module['configuration']));
		if (count($_POST) > 0 && array_key_exists('loginForm',$_POST)) {
			$outer->addData($_POST);
			if ($outer->validate()) {
				$status = $this->logMeIn(
					$_POST['username'],
					SHA1($outer->getData('password')),
					array_key_exists('rememberme',$_POST) ? $_POST['rememberme'] : false
				);
				if ($status) {
					$outer->addData($_SESSION['user']['info']);
					if (strlen($page) > 0) {
						$this->logMessage(__FUNCTION__,sprintf('redirect force to page [%s]',$page),1);
						header("Location: $page");
					}
					elseif ($this->hasOption('sendTo')) {
						$this->logMessage(__FUNCTION__,sprintf('sendto force to page [%s]',$this->getOption("sendTo")),1);
						$allowedUrls = $this->getOptions();
						$url = $this->getOption("sendTo");
						if (in_array($url, $allowedUrls, true)) {							
							header(sprintf("Location: %s",$url));
						} else {
							header("Location: /");
						}						
					}
					else
						if (strlen($module['parm1']) > 0) 
							$outer->init($this->m_dir.$module['parm1']);
						else {
							$this->logMessage(__FUNCTION__,sprintf('default force to /'),1);
							header("Location: /");
						}
				}
				else
					$outer->addFormError('Invalid Email or password');
			}
			else
				$outer->addFormError('Invalid Email or password');
		}
		return $outer->show();
	}

    /**
     * @return array|mixed|string|string[]
     * @throws phpmailerException
     */
    public function signup() {
		if (!$module = parent::getModule())
			return "";
		$this->logMessage(__FUNCTION__,sprintf("module [%s]",print_r($module,true)),3);
		$outer = new Forms();
		$outer->setModule($module);
		if ($this->isLoggedIn()) {
			$outer->init($this->m_dir.$module['parm2']);
			$outer->addData($_SESSION['user']['info']);
			return $outer->show();
		}
		$outer->init($this->m_dir.$module['outer_html']);
		$flds = $this->config->getFields($module['configuration']);
		if (array_key_exists('options',$flds)) $outer->setOptions($flds['options']);
		$flds = $outer->buildForm($flds);
		$subdata = $this->subForms($this->m_module['fetemplate_id'],null,array(),'outer');
		$this->logMessage(__FUNCTION__,sprintf('subforms [%s]',print_r($subdata,true)),1);
		foreach($subdata as $key=>$value) {
			$outer->addTag($key,$value,false);
		}
		if (count($_POST) > 0 && array_key_exists('signupForm',$_POST)) {
			$outer->addData($_POST);
			if ($valid = $outer->validate()) {
				if ($outer->getData('password') != $outer->getData('password_confirm')) {
					$valid = false;
					$outer->addFormError('Password and confirmation do not match');
				}
				if ($outer->getData('email') != $outer->getData('email_confirm')) {
					$valid = false;
					$outer->addFormError('Email and confirmation do not match');
				}
				if ($valid && array_key_exists('email',$flds)) {
					$email = $this->fetchScalarTest('select count(*) from members where deleted = 0 and email = "%s"',$outer->getData('email'));
					if ($email > 0) {
						$valid = false;
						$outer->addFormError('This email has already been registered');
						$outer->addTag("duplicateEmailError","*");
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
				if ($valid) {
					$address = $_POST['address'];
					unset($_POST['address']);
					$values = array();
					foreach($_POST as $key=>$value) {
						if (array_key_exists($key,$flds)) {
							if ((!array_key_exists('database',$flds[$key]) || $flds[$key]['database'] == true)) {
								$values[$key] = $outer->getData($key);
							}
						}
					}
					$values['created'] = date(DATE_ATOM);
					if (array_key_exists('password',$values)) $values['password'] = SHA1($values['password']);
					$stmt = $this->prepare(sprintf('insert into members(%s) values(%s)',implode(',',array_keys($values)),str_repeat('?,',count($values)-1).'?'));
					$stmt->bindParams(array_merge(array(str_repeat('s',count($values))),array_values($values)));
					$this->beginTransaction();
					if ($valid = $stmt->execute()) {
						$id = $this->insertId();
						$outer->setData("id",$id);
						$addr = array();
						foreach($address as $key=>$value) {
							$addr[$key] = $value;
						}
						$addr['firstname'] = $outer->getData('firstname');
						$addr['lastname'] = $outer->getData('lastname');
						$addr['email'] = $outer->getData('email');
						$addr['ownerid'] = $id;
						$addr['ownertype'] = 'member';
						$addr['tax_address'] = 1;
						$addr['addresstype'] = $this->fetchScalarTest('select id from code_lookups where type="memberAddressTypes" and extra = 1');
						$stmt = $this->prepare(sprintf('insert into addresses(%s) values(%s)',implode(',',array_keys($addr)),str_repeat('?,',count($addr)-1).'?'));
						$stmt->bindParams(array_merge(array(str_repeat('s',count($addr))),array_values($addr)));
						$valid = $valid && $stmt->execute();
						$addr['id'] = $this->insertId();
						if ($valid) {
							$this->commitTransaction();
							$c = new Custom(0,$module);
							if (method_exists($c,"postSignup")) $c->postSignup($this, $outer->getAllData());
							if (array_key_exists('email',$values) && array_key_exists('password',$values))
								$valid = $this->logMeIn($values['username'],$values['password']);
							else
								$valid = $this->logMeIn('','',0,$id);
							if ($valid) {
								$outer->addFormSuccess("Your account was created");
								if (strlen($module['parm1']) > 0) {
									$outer = new Forms();
									$outer->init($this->m_dir.$module['parm1']);
								}
								if ($this->hasOption('showInputOnSuccess'))
									$flds = $outer->buildForm($flds);
								$outer->addData($_POST);
								$outer->addData(Address::formatData($addr));
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
		return $outer->show();
	}

    /**
     * @return array|false|mixed|string|string[]
     * @throws phpmailerException
     */
    public function contactUs() {
		if (!$module = parent::getModule())
			return "";
		$this->logMessage(__FUNCTION__,sprintf("module [%s]",print_r($module,true)),3);
		$flds = $this->config->getFields($module['configuration']);
		if (array_key_exists('options',$flds) && array_key_exists('source',$flds['options']))
			$emails = $this->configEmails($flds['options']['source']);
		else
			$emails = $this->configEmails("contact");
		$options = array();
		if (array_key_exists('contactPerson',$flds)) {
			$flds['contactPerson']['options'] = array();
		}
		foreach($emails as $key=>$info) {
			$options[$key] = $info;
			if (array_key_exists('contactPerson',$flds)) {
				$flds['contactPerson']['options'][$key] = $info['name'];
			}
		}
		$form = new Forms();
		$form->setModule($module);
		$form->init($this->m_dir.$module['outer_html'],array('name'=>'contactUsOuter'));
		$inner = new Forms();
		$inner->setModule($module);
		$inner->init($this->m_dir.$module['inner_html'],array('name'=>'contactUsInner'));
		//
		//	get the config mailing address
		//
		$config = $this->fetchSingleTest("select * from config where name = '%s' and type = 'config'",'mailing-address');
		$address = $this->fetchSingleTest('select * from addresses where id = %d',$config['value']);
		$address = Address::formatData($address);
		$form->addData(array("address"=>$address));
		$flds = $inner->buildForm($flds);
		$subdata = $this->subForms($this->m_module['fetemplate_id'],null,array(),'inner');
		$this->logMessage(__FUNCTION__,sprintf('subforms [%s]',print_r($subdata,true)),3);
		foreach($subdata as $key=>$value) {
			$inner->addTag($key,$value,false);
		}
		$hasFiles = array();
		if (count($_POST) > 0 && array_key_exists('contactUs',$_POST)) {
			$inner->addData($_POST);
			$status = $inner->validate();
			if ($status) {
				foreach($flds as $key=>$field) {
					if ($field['type'] == 'fileupload') {
						if (!$this->hasOption('MimeUploadTypes')) {
							$this->logMessage(__FUNCTION__,sprintf('file upload is enabled with no file types module [%s]',print_r($module,true)),1,true);
							$status = false;
							$messages = array(0=>'An error occurred. The webmaster has been notified');
						}
						else
							$status = $this->processUploadedFiles(explode('|',$this->getOption('MimeUploadTypes')),$files,$messages);
						if (!$status) {
							foreach($messages as $key=>$value) {
								$inner->addFormError($value);
							}
						}
						else {
							foreach($files as $key=>$value) {
								$inner->setData($key,$value);
								$hasFiles[] = $value;
							}
						}
					}
				}
			}
			if ($status) {
				if ($inner->getData('r_id') != $_SESSION['forms'][$inner->getOption('name')]['r_secret']) {
					$status = false;
					$this->addError('An internal error occurred');
					$this->logMessage(__FUNCTION__,sprintf('possible spam [%s]',print_r($_POST,true)),1,true);
				}
			}
			//
			//	stop reposts from a refresh
			//
			$inner->setData('r_secret',random_int(1, 1000000));
			$_SESSION['forms'][$inner->getOption('name')]['r_secret'] = $inner->getData('r_secret');
			if ($status) {
				$mailer = new MyMailer();
				$mailer->Subject = sprintf("Contact Request - %s", SITENAME);
				$body = new Forms();
				$body->setOption('formDelimiter','{{|}}');
				if ($disp = $this->config->getFields($module['configuration'].'Display')) {
					$disp = $body->buildForm($disp);
				}
				foreach($subdata as $key=>$value) {
					$body->addTag($key,$value,false);
				}
				$sql = sprintf('select * from htmlForms where class = %d and type = "%s"',$this->getClassId(get_class($this)),$module['parm1']);
				$html = $this->fetchSingleTest($sql);
				$this->logMessage(__FUNCTION__,sprintf("result email sql [$sql] html [%s]",print_r($html,true)),2);
				$body->setHTML($html['html']);
				$body->addData($inner->getAllData());
				$mailer->Body = $body->show();
				if (array_key_exists('contactPerson',$flds)) {
					foreach($options as $key=>$info) {
						if ($key == $inner->getData('contactPerson')) {
							$mailer->AddAddress($info['email'],$info['name']);
						}
					}
				}
				else
					foreach($emails as $key=>$info) {
						$mailer->AddAddress($info['email'],$info['name']);
					}
				$mailer->From= 'noreply@'.HOSTNAME;	//$inner->getData('email');
				$mailer->FromName = $inner->getData('firstname').' '.$inner->getData('lastname');
				$mailer->AddReplyTo($inner->getData('email'),$inner->getData('firstname').' '.$inner->getData('lastname'));
				$mailer->IsHTML(true);
				if ($this->hasOption("emailAttachment") && count($hasFiles) > 0) {
					foreach($hasFiles as $key=>$value) {
						$mailer->addAttachment( ".".$value["name"] );
					}
				}
				if (!$mailer->Send()) {
					$inner->addFormError("We're sorry but the send failed");
					$this->logMessage(__FUNCTION__,sprintf("Email send failed [%s]",print_r($mailer,true)),1,true);
				} else {
					if ($this->hasOption("deleteAttachment")) {
						foreach($hasFiles as $key=>$value) {
							unlink(".".$value["name"] );
						}
					}
					if (strlen($module['parm2']) > 0)
						$inner->init($this->m_dir.$module['parm2']);
					else
						$inner->addFormSuccess('Email successfully sent');
				}
			}
			else {
				$inner->addFormError('Form Validation Failed');
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
		$form->addTag('form',$inner->show(),false);
		$subdata = $this->subForms($this->m_module['fetemplate_id'],null,array(),'outer');
		$this->logMessage(__FUNCTION__,sprintf('subforms [%s]',print_r($subdata,true)),3);
		foreach($subdata as $key=>$value) {
			$form->addTag($key,$value,false);
		}
		if ($this->isAjax())
			return $this->ajaxReturn(array('html'=>$form->show(),'status'=>true));
		else
			return $form->show();
	}

    /**
     * @return array|mixed|string|string[]
     * @throws phpmailerException
     */
    function generic() {
		if (!$module = parent::getModule())
			return "";
		$this->logMessage(__FUNCTION__,sprintf("module [%s]",print_r($module,true)),3);
		$form = new Forms();
		$form->setModule($module);
		$form->init($this->m_dir.$module['outer_html']);
		$flds = $form->buildForm($this->config->getFields($module['configuration']));
		$subdata = $this->subForms($this->m_module['fetemplate_id'],null,array(),'outer');
		$this->logMessage(__FUNCTION__,sprintf('subforms [%s]',print_r($subdata,true)),4);
		foreach($subdata as $key=>$value) {
			$form->addTag($key,$value,false);
		}
		return $form->show();
	}

    /**
     * @return array|mixed|string|string[]
     * @throws phpmailerException
     */
    function forgotPassword() {
		if (!$module = parent::getModule())
			return "";
		$this->logMessage(__FUNCTION__,sprintf("module [%s]",print_r($module,true)),3);
		$form = new Forms();
		$form->setModule($module);
		$form->init($this->m_dir.$module['outer_html']);
		$flds = $form->buildForm($this->config->getFields($module['configuration']));
		if (count($_POST) > 0 && array_key_exists('forgotPassword',$_POST)) {
			$form->addData($_POST);
			if ($form->validate()) {
				if ($user = $this->fetchSingleTest('select * from members where email = "%s" and deleted = 0 and enabled = 1 and (expires = "0000-00-00" or expires > curdate())', $_POST['email'])) {
					$password = sprintf('%d',random_int(100000,9999999));
					$mailer = new MyMailer();
					$emails = $this->configEmails("contact");
					$mailer->From= $emails[0]['email'];
					$mailer->FromName = $emails[0]['name'];
					$mailer->Subject = sprintf("Password Reset - %s", SITENAME);
					$mailer->IsHTML(true);
					$body = new Forms();
					$sql = sprintf('select * from htmlForms where class = %d and type = "%s"',$this->getClassId(get_class($this)),$module['parm1']);
					$html = $this->fetchSingleTest($sql);
					$this->logMessage(__FUNCTION__,sprintf("result email sql [$sql] html [%s]",print_r($html,true)),2);
					$body->setHTML($html['html']);
					$body->addData($user);
					$body->setOption('formDelimiter','{{|}}');
					$body->addTag('password',$password);
					$mailer->Body = $body->show();
					$mailer->AddAddress($user['email'],$user['firstname'].' '.$user['lastname']);
					$this->logMessage(__FUNCTION__,sprintf('mailer [%s]',print_r($mailer,1)),3);

					if ($mailer->send()) {
						$form->addFormSuccess("An email has been sent to the registered email address with a new password");
						$this->execute(sprintf('update members set password = "%s" where id = %d',sha1($password),$user['id']));
					}
					else
						$form->addFormError("An error occurred while sending the email");
				}
				else
					$form->addFormError("Sorry, we couldn't find that account");
			}
		}
		$subdata = $this->subForms($this->m_module['fetemplate_id'],null,array(),'outer');
		$this->logMessage(__FUNCTION__,sprintf('subforms [%s]',print_r($subdata,true)),3);
		foreach($subdata as $key=>$value) {
			$form->addTag($key,$value,false);
		}
		return $form->show();
	}

    /**
     * @return array|mixed|string|string[]
     * @throws phpmailerException
     */
    function configParam() {
		if (!$module = parent::getModule())
			return "";
		$this->logMessage(__FUNCTION__,sprintf("module [%s]",print_r($module,true)),3);
		$form = new Forms();
		$form->setModule($module);
		$form->init($this->m_dir.$module['outer_html']);
		$flds = $form->buildForm($this->config->getFields($module['configuration']));
		$data = $this->fetchSingleTest("select * from config where name = '%s' and type = 'config'",$module['parm1']);
		switch($data['field_type']) {
			case 'address':
				$tmp = $this->fetchSingleTest('select a.*, p.*, c.* from addresses a, provinces p, countries c where a.id = %d and p.id = a.province_id and c.id = a.country_id',$data['value']);
				$tmp['formattedAddress'] = Address::formatAddress($data['value']);
				$data = $tmp;
				break;
		}
		$form->addData($data);
		return $form->show();
	}

    /**
     * @return array|mixed|string|string[]
     * @throws phpmailerException
     */
    function accountInfo() {
		if (!$module = parent::getModule())
			return "";
		$form = new Forms();
		$form->setModule($module);
		$form->init($this->m_dir.$module['outer_html']);
		$flds = $this->config->getFields($module['configuration']);
		$user = $this->fetchSingleTest('select * from members where id = %d and deleted = 0',$this->getUserInfo('id'));
		if (!$address = $this->fetchSingleTest('select * from addresses where ownerType="member" and ownerId = %d order by id limit 1',$this->getUserInfo('id')))
			$address = array('country_id'=>0,'province_id'=>0,'id'=>0);
		if (count($_POST) == 0 || !(array_key_exists('accountInfoForm',$_POST))) {
			//$address['country_id'] = $this->fetchScalarTest('select country from countries where id = %d',$address['country_id']);
			//$address['province_id'] = $this->fetchScalarTest('select province from provinces where id = %d',$address['province_id']);
		}
		else {
			$flds = $this->config->getFields($module['configuration'].'Edit');
		}
		$flds = $form->buildForm($flds);
		$user['password'] = '';
		$user['addressId'] = $address['id'];
		//$form->addData($address);
		$user['address'] = $address;
		$form->addData($user);
		if (count($_POST) > 0 && array_key_exists('accountInfoForm',$_POST) && array_key_exists('saveInfo',$_POST) && $_POST['saveInfo'] == 1) {
			$form->addData($_POST);
			$valid = $form->validate();
			if ($valid) {
				if ($form->getData('email') != $_SESSION['user']['info']['email']) {
					if ($form->getData('email') != $form->getData('emailConfirm')) {
						$valid = false;
						$form->addFormError('Email and Email Confirm do not match');
					}
					else {
						$ct = $this->fetchScalarTest('select count(email) from members where email = "%s" and id != %d and deleted = 0',$form->getData('email'),$_SESSION['user']['info']['id']);
						if ($ct > 0) {
							$valid = false;
							$form->addFormError('This Email is already registered');
						}
					}
				}
				if (strlen($form->getData('password')) > 0) {
					if ($form->getData('password') != $form->getData('passwordConfirm')) {
						$form->addFormError('Password and Password Confirm do not match');
						$valid = false;
					}
				}
			}
			if ($valid) {
				$address = $_POST['address'];
				unset($_POST['address']);
				$values = array();
				foreach($_POST as $key=>$value) {
					if (array_key_exists($key,$flds)) {
						if (!(array_key_exists('database',$flds[$key]) && $flds[$key]['database'] == false)) {
							$values[$key] = $form->getData($key);
						}
					}
				}
				if (strlen($form->getData('password')) > 0)
					$values['password'] = SHA1($form->getData('password'));
				$stmt = $this->prepare(sprintf('update members set %s where id = %d',implode('=?, ',array_keys($values)).'=?',$_SESSION['user']['info']['id']));
				$stmt->bindParams(array_merge(array(str_repeat('s',count($values))),array_values($values)));
				$this->beginTransaction();
				if ($valid = $stmt->execute()) {
					$addr = array();
					foreach($address as $key=>$value) {
						$addr[$key] = $value;
					}
					$addr['firstname'] = $form->getData('firstname');
					$addr['lastname'] = $form->getData('lastname');
					$addr['email'] = $form->getData('email');
					if ($_POST['addressId'] == 0) {
						$addr['ownertype'] = 'member';
						$addr['ownerid'] = $user['id'];
						$addr['addresstype'] = $this->fetchScalarTest('select id from code_lookups where type="memberAddressTypes" and extra = "1"');
						$addr['tax_address'] = 1;
						$stmt = $this->prepare(sprintf('insert into addresses(%s) values(%s?)',implode(', ',array_keys($addr)),str_repeat('?,',count($addr)-1),$_POST['addressId']));
					}
					else {
						$stmt = $this->prepare(sprintf('update addresses set %s where id = %d',implode('=?, ',array_keys($addr)).'=?',$_POST['addressId']));
					}
					$stmt->bindParams(array_merge(array(str_repeat('s',count($addr))),array_values($addr)));
					$valid = $valid && $stmt->execute();
					if ($valid) {
						if ($_POST['addressId'] == 0) {
							$address['addressId'] = $this->insertId();
							$address['id'] = $address['addressId'];
						}
						$this->commitTransaction();
						$_SESSION['user']['info'] = $this->fetchSingleTest('select * from members where id = %d',$_SESSION['user']['info']['id']);
						$valid = $this->logMeIn($_SESSION['user']['info']['username'],$_SESSION['user']['info']['password']);
						if ($valid) {
							$form = new Forms();
							$form->setModule($module);
							$form->init($this->m_dir.$module['outer_html']);
							if (strlen($module['parm1']) > 0) {
								$form = new Forms();
								$form->init($this->m_dir.$module['parm1']);
							}
							$tmp = $form->buildForm($this->config->getFields($module['configuration']));
							$tmp = $_SESSION['user']['info'];
							unset($tmp["password"]);
							$form->addData($tmp);
							$form->addData($address);
							$form->addFormSuccess('Update successful');
						}
						else
							$this->rollbackTransaction();
					}
					else {
						$this->rollbackTransaction();
					}
					if (!$valid) $form->addFormError('An error occurred. The Web Master has been notified');
				}
			}
		}
		return $form->show();
	}

    /**
     * @return array|mixed|string|string[]
     * @throws phpmailerException
     */
    function searchResults() {
		if (!$module = parent::getModule())
			return "";
		$outer = new Forms();
		$outer->setModule($module);
		$outer->init($this->m_dir.$module['outer_html']);
		$flds = $outer->buildForm($this->config->getFields($module['configuration']));
		//
		//	search events, news, stores, content, products for the phrase, sort by date & output
		//
		if (array_key_exists('siteSearchForm',$_REQUEST) && array_key_exists('siteSearchText',$_REQUEST)) {
			$outer->addData($_REQUEST);
		}
		if (array_key_exists('siteSearchForm',$_REQUEST) && array_key_exists('siteSearchText',$_REQUEST) && strlen($module['inner_html']) > 0) {
			$searchTerms = $_REQUEST['siteSearchText'];
			$searchPhrase = explode(' ',$searchTerms);
			$evts = array();
			$news = array();
			$stores = array();
			$products = array();
			$content = array();
			$blogs = array();
			$searchTypes =  array();
			if ($this->hasOption("searchTypes")) {
				$tmp = explode("|",$this->getOption("searchTypes"));
				foreach($tmp as $key=>$value) {
					$searchTypes[$value] = 1;
				}
			}
			for($x = 0; $x < count($searchPhrase); $x++) {

				if (count($searchTypes) == 0 || array_key_exists("event",$searchTypes))
					$evts[$x] = $this->fetchAllTest('select e.id,"event" as resultType,e.start_date as resultDate, name, teaser, description from events e where e.id in (select event_id from event_dates ed where ed.event_date >= curdate()) and e.enabled = 1 and e.published = 1 and (e.name like "%%%s%%" or e.teaser like "%%%s%%" or e.description like "%%%s%%")',
						$searchPhrase[$x],$searchPhrase[$x],$searchPhrase[$x]);
				else $evts = array(0=>array());
				if (count($searchTypes) == 0 || array_key_exists("news",$searchTypes))
					$news[$x] = $this->fetchAllTest('select n.id,"news" as resultType, n.created as resultDate, title, teaser, body from news n where n.enabled = 1 and n.published = 1 and (n.expires = "0000-00-00" or n.expires >= curdate()) and (n.title like "%%%s%%" or n.teaser like "%%%s%%" or n.body like "%%%s%%")',
						$searchPhrase[$x],$searchPhrase[$x],$searchPhrase[$x]);
				else $news = array(0=>array());

				if (count($searchTypes) == 0 || array_key_exists("store",$searchTypes))
					$stores[$x] = $this->fetchAllTest('select s.id,"store" as resultType, s.created as resultDate, name, teaser, description from stores s where s.enabled = 1 and s.published = 1 and (s.name like "%%%s%%" or s.teaser like "%%%s%%" or s.description like "%%%s%%")',
						$searchPhrase[$x],$searchPhrase[$x],$searchPhrase[$x]);
				else $stores = array(0=>array());

				if (count($searchTypes) == 0 || array_key_exists("product",$searchTypes))
					$products[$x] = $this->fetchAllTest('select p.id, "product" as resultType, p.created as resultDate, name, teaser, description from product p where p.enabled = 1 and p.published = 1 and p.deleted = 0 and (p.name like "%%%s%%" or p.teaser like "%%%s%%" or p.description like "%%%s%%")',
						$searchPhrase[$x],$searchPhrase[$x],$searchPhrase[$x]);
				else $products = array(0=>array());

				if (count($searchTypes) == 0 || array_key_exists("blog",$searchTypes))
					$blogs[$x] = $this->fetchAllTest('select b.id, "blog" as resultType, b.created as resultDate, title, teaser, body from blog b where b.enabled = 1 and b.published = 1 and b.deleted = 0 and (b.title like "%%%s%%" or b.teaser like "%%%s%%" or b.body like "%%%s%%")',
						$searchPhrase[$x],$searchPhrase[$x],$searchPhrase[$x]);
				else $blogs = array(0=>array());

				if (count($searchTypes) == 0 || array_key_exists("page",$searchTypes))
					$content[$x] = $this->fetchAllTest('select p.id, "page" as resultType, p.created as resultDate, p.content from pages p, content c where p.content like "%%%s%%" and p.version = (select max(p1.version) from pages p1 where p1.content_id = p.content_id) and c.id = p.content_id', $searchPhrase[$x]);
				else $content = array(0=>array());

				foreach($news[$x] as $key=>$article) {
					$t = strip_tags($article["teaser"]);
					$b = strip_tags($article["body"]);
					if (stripos($article["title"],$searchPhrase[$x]) === false && stripos($t,$searchPhrase[$x]) === false && stripos($b,$searchPhrase[$x]) === false) {
						$this->logMessage(__FUNCTION__,sprintf("dropping article [%d] after stripping", $article["id"]),1);
						unset($news[$x][$key]);
					}
					else
						$news[$x][$key] = $article;
				}
				foreach($evts[$x] as $key=>$event) {
					$t = strip_tags($event["teaser"]);
					$b = strip_tags($event["description"]);
					if (stripos($event["name"],$searchPhrase[$x]) == false && stripos($t,$searchPhrase[$x]) === false && stripos($b,$searchPhrase[$x]) === false) {
						$this->logMessage(__FUNCTION__,sprintf("dropping event [%d] after stripping tags name [%s] t [%s] b [%s]", $event["id"], $event["name"], $t, $b),1);
						unset($evts[$x][$key]);
					}
					else
						$evts[$x][$key] = $event;
				}
				foreach($products[$x] as $key=>$product) {
					$t = strip_tags($product["teaser"]);
					$b = strip_tags($product["description"]);
					if (stripos($product["name"],$searchPhrase[$x]) == false && stripos($t,$searchPhrase[$x]) === false && stripos($b,$searchPhrase[$x]) === false) {
						$this->logMessage(__FUNCTION__,sprintf("dropping product [%d] after stripping tags", $product["id"]),1);
						unset($products[$x][$key]);
					}
					else
						$products[$x][$key] = $product;
				}
				foreach($stores[$x] as $key=>$store) {
					$t = strip_tags($store["teaser"]);
					$b = strip_tags($store["description"]);
					if (stripos($store["name"],$searchPhrase[$x]) == false && stripos($t,$searchPhrase[$x]) === false && stripos($b,$searchPhrase[$x]) === false) {
						$this->logMessage(__FUNCTION__,sprintf("dropping store [%d] after stripping tags", $store["id"]),1);
						unset($stores[$x][$key]);
					}
					else
						$stores[$x][$key] = $store;
				}
				foreach($content[$x] as $key=>$page) {
					$t = strip_tags($page["content"]);
					if (stripos($t,$searchPhrase[$x]) === false) {
						$this->logMessage(__FUNCTION__,sprintf("dropping store [%d] after stripping tags", $page["id"]),1);
						unset($content[$x][$key]);
					}
					else
						$content[$x][$key] = $page;
				}
				foreach($blogs[$x] as $key=>$blog) {
					$t = strip_tags($blog["teaser"]);
					$b = strip_tags($blog["body"]);
					if (stripos($blog["title"],$searchPhrase[$x]) == false && stripos($t,$searchPhrase[$x]) === false && stripos($b,$searchPhrase[$x]) === false) {
						$this->logMessage(__FUNCTION__,sprintf("dropping blog [%d] after stripping tags", $blog["id"]),1);
						unset($blog[$x][$key]);
					}
					else
						$blog[$x][$key] = $blog;
				}
			}

			$weighted = array();
			for($x = 0; $x < count($searchPhrase); $x++) {
				foreach($evts[$x] as $item) {
					if (!array_key_exists($item['resultType']."_".$item['id'],$weighted))
						$weighted[$item['resultType']."_".$item['id']] = array('ct'=>0,'dt'=>$item['resultDate']);
					$weighted[$item['resultType']."_".$item['id']]['ct'] += 1;
				}
				foreach($news[$x] as $item) {
					if (!array_key_exists($item['resultType']."_".$item['id'],$weighted))
						$weighted[$item['resultType']."_".$item['id']] = array('ct'=>0,'dt'=>$item['resultDate']);
					$weighted[$item['resultType']."_".$item['id']]['ct'] += 1;
				}
				foreach($stores[$x] as $item) {
					if (!array_key_exists($item['resultType']."_".$item['id'],$weighted))
						$weighted[$item['resultType']."_".$item['id']] = array('ct'=>0,'dt'=>$item['resultDate']);
					$weighted[$item['resultType']."_".$item['id']]['ct'] += 1;
				}
				foreach($products[$x] as $item) {
					if (!array_key_exists($item['resultType']."_".$item['id'],$weighted))
						$weighted[$item['resultType']."_".$item['id']] = array('ct'=>0,'dt'=>$item['resultDate']);
					$weighted[$item['resultType']."_".$item['id']]['ct'] += 1;
				}
				foreach($blogs[$x] as $item) {
					if (!array_key_exists($item['resultType']."_".$item['id'],$weighted))
						$weighted[$item['resultType']."_".$item['id']] = array('ct'=>0,'dt'=>$item['resultDate']);
					$weighted[$item['resultType']."_".$item['id']]['ct'] += 1;
				}
				foreach($content[$x] as $item) {
					if (!array_key_exists($item['resultType']."_".$item['id'],$weighted))
						$weighted[$item['resultType']."_".$item['id']] = array('ct'=>0,'dt'=>$item['resultDate']);
					$weighted[$item['resultType']."_".$item['id']]['ct'] += 1;
				}
			}

			$this->logMessage(__FUNCTION__,sprintf('weighted results [%s]',print_r($weighted,true)),3);
			$sorted = array();
			foreach($weighted as $key=>$item) {
				$sorted[$item['ct']][] = array('type'=>$key,'sort'=>$item['dt']);
			}
			$this->logMessage(__FUNCTION__,sprintf('sorted results [%s]',print_r($sorted,true)),3);
			$merged = array();
			for($x = count($searchPhrase); $x > 0; $x--) {
				if (array_key_exists($x,$sorted)) {
					usort($sorted[$x],"sortSearchItems");
					$merged = array_merge($merged,$sorted[$x]);
				}
			}
			$outer->addTag('searchCount',count($merged));
			$this->logMessage(__FUNCTION__,sprintf('merged [%s]',print_r($merged,true)),3);
			$ct = count($merged);
			$page = 0;
			$paged = $this->getPaginationFromData($merged,$module,$ct,$page);
			$this->logMessage(__FUNCTION__,sprintf("getPagination returned [%s]", print_r($paged,true)),1);
			$pagination = $paged["html"];
			$merged = $paged["data"];
			$this->logMessage(__FUNCTION__,sprintf('merged after pagination [%s] ct [%d] page [%d]',print_r($merged,true), $ct, $page),3);
			$outer->addTag('pagination',$pagination,false);
			$inner = new Forms();
			$inner->init($this->m_dir.$module['inner_html']);
			$return = array();
			$ct = 0;
			if ($this->hasOption('grpPrefix')) $return[] = $this->getOption('grpPrefix');
			if (class_exists("news")) $n = new news(0,$module);
			if (class_exists("product")) $p = new product(0,$module);
			if (class_exists("calendar")) $e = new calendar(0,$module);
			if (class_exists("stores")) $s = new stores(0,$module);
			if (class_exists("blog")) $b = new blog(0);
			foreach($merged as $key=>$value) {
				$ct += 1;
				if ($module['rows'] > 0 && $ct > $module['columns']) {
					$ct = 1;
					if ($this->hasOption('grpSuffix')) $return[] = $this->getOption('grpSuffix');
					if ($this->hasOption('grpPrefix')) 
						$return[] = $this->getOption('grpPrefix');
					else
						$return[] = '<div class="clearfix"></div>';
				}
				$inner->reset();
				$item = explode('_',$value['type']);
				switch($item[0]) {
					case 'news':
						$data = $this->fetchSingleTest('select * from news where id = %d',$item[1]);
						$data = $n->formatData($data);
						$data["teaser"] = strip_tags($data["teaser"]);
						$data["body"] = strip_tags($data["body"]);
						break;
					case 'product':
						$data = $this->fetchSingleTest('select * from product where id = %d',$item[1]);
						$data = $p->formatData($data);
						$data["teaser"] = strip_tags($data["teaser"]);
						$data["description"] = strip_tags($data["description"]);
						break;
					case 'page':
						$data = $this->fetchSingleTest('select c.*,p.content from pages p, content c where p.id = %d and c.id = p.content_id',$item[1]);
						$data['url'] = $this->getUrl('menu',$data['id'],$data);
						$data['teaser'] = $this->subwords(strip_tags($data['content']),15);
						break;
					case 'event':
						$data = $this->fetchSingleTest('select * from events where id = %d',$item[1]);
						$data = $e->formatData($data);
						$data["teaser"] = strip_tags($data["teaser"]);
						$data["description"] = strip_tags($data["description"]);
						break;
					case 'store':
						$data = $this->fetchSingleTest('select * from stores where id = %d',$item[1]);
						$data = $s->formatData($data);
						$data["teaser"] = strip_tags($data["teaser"]);
						$data["description"] = strip_tags($data["description"]);
						break;
					case 'blog':
						$data = $this->fetchSingleTest('select * from blog where id = %d',$item[1]);
						$data = $b->formatData($data);
						$data["teaser"] = strip_tags($data["teaser"]);
						$data["description"] = strip_tags($data["body"]);
						break;
					default:
						$data = array();
				}
				$data['date'] = date(GLOBAL_DEFAULT_DATE_FORMAT,strtotime($value['sort']));
				$data['teaser'] = $this->highlight($searchPhrase,$data['teaser']);
				$data["result_type"] = $item[0];
				if (array_key_exists('title',$data)) $data['title'] = $this->highlight($searchPhrase,$data['title']);
				if (array_key_exists('name',$data)) $data['name'] = $this->highlight($searchPhrase,$data['name']);
				$inner->addData($data);
				$return[] = $inner->show();
			}
			if ($this->hasOption('grpSuffix')) $return[] = $this->getOption('grpSuffix');
			$outer->addTag('results',implode('',$return),false);
		}
		return $outer->show();
	}

    /**
     * @return array|mixed|string|string[]
     * @throws phpmailerException
     */
    function userTesting() {
		if (!$module = parent::getModule())
			return "";
		$outer = new Forms();
		$outer->setModule($module);
		$outer->init($this->m_dir.$module['outer_html']);
		$page = '';
		$flds = $outer->buildForm($this->config->getFields($module['configuration']));
		if (count($_POST) > 0 && array_key_exists('userTesting',$_POST)) {
			$outer->addData($_POST);
			if ($outer->validate()) {
				if (array_key_exists('o_id',$_REQUEST) && $_REQUEST['o_id'] > 0)
					$_SESSION['user']['lastOrder'] = $_REQUEST['o_id'];
				if ($user = $this->fetchSingleTest('select * from members where email = "%s" and id = %d',$outer->getData("email",$outer->getData("member_id")))) {
					$status = $this->logMeIn("","",0,$user['id']);
				} else $status = false;
				if ($status) {
					$outer->addData($_SESSION['user']['info']);
					if (strlen($module['parm1']) > 0) 
						$outer->init($this->m_dir.$module['parm1']);
					else {
						$this->logMessage(__FUNCTION__,sprintf('default force to /'),1);
						header("Location: /");
					}
				}
				else
					$outer->addFormError('Invalid Username or password');
			}
			else
				$outer->addFormError('Invalid Username or password');
		}
		return $outer->show();
	}

    /**
     * @return false|string
     * @throws phpmailerException
     */
    function getModuleInfo() {
		return parent::getModuleList(array('login','signup','accountInfo','contactUs','forgotPassword','generic','forgotPassword','configParam','searchResults','userTesting'));
	}
	
}

?>
