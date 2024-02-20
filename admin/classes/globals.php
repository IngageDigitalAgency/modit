<?php

/**
 * Manages global properties and methods
 */
class Globals {

	private $m_connection = null;
	private $m_fhandle = null;
	private $m_debug = null;
	private $m_errors = array();
	private $m_messages = array();
	private $m_ecom_errors = array();
	private $m_ecom_messages = array();
	private $m_config = null;
	private $m_relations = array();
	public $m_pageName;
	private $m_wurfl = null;

    /**
     * @param int $debug
     * @param bool $wurfl
     * @param string $debugLog
     * @throws phpmailerException
     */
    function __construct($debug = 0, $wurfl = true, $debugLog = '') {
		//if ($debug > 0) {
			if (strlen($debugLog) > 0)
				$this->m_fhandle = fopen(sprintf("%s%s.log",DEBUGLOG,$debugLog),'a+');
			else
				$this->m_fhandle = fopen(sprintf("%s%s.log",DEBUGLOG,DBNAME),'a+');
		//}
		$this->m_debug = $debug;
		$this->m_connection = new mysqli();
		if (!$this->m_connection->real_connect(DBHOST, DBUSER, DBPASSWD, DBNAME )) {
			$tmp = mysqli_error();
			$this->logMessage("__construct", sprintf("Database connect error: %s", $tmp),1, true);
			exit('An error occured: '.$tmp);
		}
		else {
			$this->m_connection->set_charset("utf8");
			$c = new Common(false);
			$tmp = $c->fetchAll('select m1.name as relatedFrom, m2.name as relatedTo, cf.enabled 
from cross_functionality cf, modules m1, modules m2
where m1.id = cf.related_from and m2.id = cf.related_to and cf.enabled = 1',$this->m_connection);
			foreach($tmp as $key=>$value) {
				$this->m_relations[sprintf('%s:%s',$value['relatedFrom'],$value['relatedTo'])] = $value['enabled'];
			}
		}
		$this->m_wurfl = null;
	}

    /**
     * Destructor
     */
	function __destruct() {
		if ($this->m_fhandle != null) {
			fclose($this->m_fhandle);
			$this->m_handle = null;
		}
		if ($this->m_connection != null) {
			$this->m_connection->close();
			$this->m_connection = null;
		}
	}

    /**
     * @param $message
     * @return void
     */
    function addEcomMessage($message) {
		$this->m_ecom_messages[] = $message;
	}

    /**
     * @param $message
     * @return void
     */
    function addEcomError($message) {
		$this->m_ecom_errors[] = $message;
	}

    /**
     * @return array|string|string[]
     * @throws phpmailerException
     */
    function showEcomMessages() {
		$messages = array();
		foreach($this->m_ecom_messages as $msg) {
			$messages[] = $msg;
		}
		$fld = new div();
		$fld->addAttribute('class', 'alert alert-success');
		$this->m_ecom_messages = array();
		return count($messages) > 0 ? str_replace('~','<br/>',$fld->show(implode('~',$messages))) : "";
	}

    /**
     * @return array|string|string[]
     * @throws phpmailerException
     */
    function showEcomErrors() {
		$messages = array();
		foreach($this->m_ecom_errors as $msg) {
			$messages[] = $msg;
		}
		$fld = new div(false);
		$fld->addAttribute('class', 'alert alert-error alert-danger');
		$msg = $fld->show(implode('[<br/>]',$messages));
		$this->m_ecom_errors = array();
		return count($messages) > 0 ? str_replace('~','<br/>',$fld->show(implode('~',$messages))) : "";
	}

    /**
     * @param $message
     * @return void
     */
    function addMessage($message) {
		$this->m_messages[] = $message;
	}

    /**
     * @param $message
     * @return void
     */
    function addError($message) {
		$this->m_errors[] = $message;
	}

    /**
     * @return array|string|string[]
     * @throws phpmailerException
     */
    function showMessages() {
		$messages = array();
		foreach($this->m_messages as $msg) {
			$messages[] = $msg;
		}
		$fld = new div();
		$fld->addAttribute('class', 'alert alert-success');
		$this->m_messages = array();
		return count($messages) > 0 ? str_replace('~','<br/>',$fld->show(implode('~',$messages))) : "";
	}

    /**
     * @return array|string|string[]
     * @throws phpmailerException
     */
    function showErrors() {
		$messages = array();
		foreach($this->m_errors as $msg) {
			$messages[] = $msg;
		}
		$fld = new div(false);
		$fld->addAttribute('class', 'alert alert-error alert-danger');
		$msg = $fld->show(implode('[<br/>]',$messages));
		$this->m_errors = array();
		return count($messages) > 0 ? str_replace('~','<br/>',$fld->show(implode('~',$messages))) : "";
	}

    /**
     * @return mysqli
     */
    function getConnection() {
		return $this->m_connection;
	}

    /**
     * @return false|resource
     */
    function getHandle() {
		return $this->m_fhandle;
	}

    /**
     * @param $m_handle
     * @return void
     */
    function setHandle($m_handle) {
		$this->m_fhandle = $m_handle;
	}

    /**
     * @return int
     */
    function getSeverity() {
		return $this->m_debug;
	}

    /**
     * @param $level
     * @return void
     */
    function setSeverity($level) {
		$this->m_debug = $level;
	}

    /**
     * @param $obj
     * @return void
     */
    function setConfig($obj) {
		$this->m_config = $obj;
	}

    /**
     * @return null
     */
    function getConfig() {
		return $this->m_config;
	}

    /**
     * @return null
     */
    function getWurfl() {
		return $this->m_wurfl;
	}

    /**
     * @return array
     */
    function getMessages() {
		$tmp = $this->m_messages;
		$this->m_messages = array();
		return $tmp;
	}

    /**
     * @return array
     */
    function getErrors() {
		$tmp = $this->m_errors;
		$this->m_errors= array();
		return $tmp;
	}

    /**
     * @return array
     */
    function getRelations() {
		return $this->m_relations;
	}

    /**
     * @return int
     */
    function getDebug() {
		return $this->m_debug;
	}

    /**
     * @param $level
     * @return void
     */
    function setDebug($level) {
		$this->m_debug = $level;
	}
}

?>