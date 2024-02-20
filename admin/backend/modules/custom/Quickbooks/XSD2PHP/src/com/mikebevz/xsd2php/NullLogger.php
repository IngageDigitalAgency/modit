<?php
namespace com\mikebevz\xsd2php;

/**
 * NullLogger class
 */
class NullLogger {

    /**
     * @param $code
     * @param $message
     * @return void
     */
    public function __call($code, $message) {
        
    }
    
}