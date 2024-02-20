<?php
namespace com\mikebevz\xsd2php;

require_once 'Xsd2Php.php';

/**
 * LegkoXML class
 */
class LegkoXML {

    public $version = "0.0.4";
    
    /**
     * 
     * @var Xsd2Php
     */
    private $xsd2php;
    
    private $xml2php;
    
    private $php2wsdl;

    /**
     * Constructor
     */
    public function __construct() {
          
    
    }

    /**
     * @param $schema
     * @param $destination
     * @return void
     * @throws \Exception
     */
    public function compileSchema($schema, $destination) {
        $this->xsd2php = new Xsd2Php($schema);  
        $this->xsd2php->saveClasses($destination, true);
    }

    /**
     * @param $class
     * @return void
     */
    public function generateWsdl($class) {
        
    }

    /**
     * @return string
     */
    public function getVersion()
    {
        return $this->version;
    }
}