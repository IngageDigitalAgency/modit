<?php
namespace classes;

require_once (ADMIN.'classes/common.php');

use classes\common;

class Debug extends Common {
	
	public function __construct($db_level, $log_path) {
		parent::__construct ();
	}
	
	function __destruct() {
	
	}
}

?>