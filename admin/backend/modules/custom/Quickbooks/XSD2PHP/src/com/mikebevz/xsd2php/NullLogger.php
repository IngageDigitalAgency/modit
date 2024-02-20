<?php
namespace com\mikebevz\xsd2php;

class NullLogger {

    /**
     * @param $code
     * @param $message
     * @return void
     */
    public function __call($code, $message) {
        
    }
    
}