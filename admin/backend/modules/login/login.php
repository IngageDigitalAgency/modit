<?php

class Login extends Backend {

	function __construct() {
		$this->setTemplates(array('main'=>'backend/modules/login/forms/login.html'));
		$this->setFields(array(
			'main' => array(
				'email'=>array('type'=>'textfield','required'=>true,'validation'=>'email'),
				'password'=>array('type'=>'password','required'=>true,'validation'=>'string'),
				'agreement'=>array('type'=>'checkbox','value'=>1),
				'submit'=>array('type'=>'submitbutton','value'=>'Login'),
				'options'=>array('persist'=>false,'method'=>'post')
			)
		));
		parent::__construct();
	}
	
	function __destruct() {

	}
	
	function show($injector = null) {
		$form = new Forms();
		$form->init($this->getTemplate('main'),array('name'=>'adminLogin'));
		$frmFields = $this->getFields('main');
		$frmFields['logo'] = array('type'=>'image','src'=>$this->getConfigVar('logo'),'alt'=>'Corporate Logo');
		$form->buildForm($frmFields);
		if (count($_POST) > 0) {
			$form->addData($_POST);
			if ($form->validate()) {
				if (!$this->logUserIn($_POST['email'],$_POST['password'],$form)) {
					$this->addError('Login Failed');
					//nothing
				}
				else {
					if (array_key_exists('REQUEST_URI',$_SERVER) && strlen($_SERVER['REQUEST_URI']) > 0) {
						header(sprintf('Location: %s',$_SERVER['REQUEST_URI']));
						exit;
					}
					else
						$this->redirect('menu');
				}
			}
			else $this->addError('Form Validation Failed');
		}
		return $form->show();
	}
	
	function getTitle() {
		return 'EckoCS Administration Login';
	}
	
	function logUserIn($email, $password, $form) {
		$return = parent::Login($email,$password); 
		if ($return) {
			if (!$_SESSION['administrator']['user']['terms_accepted']) {
				if (count($_POST) > 0 && $form->getData('agreement') == 1) {
					$this->execute(sprintf('update users set terms_accepted = true where id = %d', $_SESSION['administrator']['user']['id']));
				}
				else {
					$this->addError('You have not accepted the licensing agreement yet.');
					unset($_SESSION['administrator']);
					$return = false;
				}
			}
		}
		if ($return) {
			$this->logLogin($_SESSION['administrator']['user']['id'],true);
		}
		return $return;
	}
}

?>