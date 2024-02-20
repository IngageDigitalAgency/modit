<?php

class Conversion extends Common {

	public function __construct($db,$user,$passwd) {
		parent::__construct(true);
		echo sprintf('this [%s]',print_r($this,true)).PHP_EOL;
		$debug = $GLOBALS['globals'.$this->getPrivateId()];
		echo sprintf('debug [%s]',print_r($debug,true)).PHP_EOL;
		$debug->getConnection()->real_connect('localhost', $user, $passwd, $db);
		if ($debug->getSeverity() > 0) {
			$debug->setHandle(fopen(sprintf("%sconversion.log",DEBUGLOG,DBNAME),'a+'));
		}
		$this->m_debug = $debug;
	}

}