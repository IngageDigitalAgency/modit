<?php
namespace classes;

require_once (ADMIN.'classes/common.php');

use classes\common;

/**
 * Class for common debugging related funtionality for backend and frontend
 */
class Debug extends Common {

    /**
     * @param $db_level
     * @param $log_path
     */
    public function __construct($db_level, $log_path) {
		parent::__construct ();
	}

    /**
     * Destructor
     */
	function __destruct() {
	
	}
}

?>