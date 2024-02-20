<?php

class HtmlElement extends Common {
	
	protected $m_attributes = array();
	protected $m_value = null;
	protected $m_format;
	protected $m_reformatting = true;
	protected $m_to_be_removed = array('required'=>true,'sql'=>true,'reformatting'=>true,'lookup'=>true,'AMPM'=>true,'unknown'=>true,'searchfield'=>true,'nonename'=>true,'idlookup'=>true,'sequence'=>true,'addcontentclass'=>true,'prettyname'=>true,'template'=>true);

    /**
     * @param bool $reformatting
     * @throws Exception
     */
    function __construct($reformatting = true) {
		parent::__construct ();
		$this->m_reformatting = $reformatting;
	}

    /**
     * Destructor
     */
	function __destruct() {
		parent::__destruct();
	}

    /**
     * @param $name
     * @param $value
     * @return void
     */
    public function addAttribute($name, $value) {
		if ($name == 'reformatting')
			$this->m_reformatting = $value;
		else
			$this->m_attributes[strtolower($name)] = $value;
	}

    /**
     * @param $attributes
     * @return void
     */
    public function addAttributes($attributes) {
		foreach($attributes as $key=>$value)
			$this->addAttribute($key,$value);
	}

    /**
     * @param $name
     * @return void
     */
    public function removeAttribute($name) {
		if (array_key_exists(strtolower($name),$this->m_attributes))
			unset($this->m_attributes[strtolower($name)]);
	}

    /**
     * @param $attributes
     * @return void
     */
    public function removeAttributes($attributes) {
		foreach($attributes as $value)
			$this->removeAttribute($value);
	}

    /**
     * @return string
     */
    protected function buildAttributes() {
		$return = array();
		foreach($this->m_attributes as $key=>$value) {
			if (!array_key_exists($key,$this->m_to_be_removed))
				if (!is_array($value))
					$return[] = sprintf('%s="%s"',$key,htmlspecialchars($value));
		}
		return implode(" ",$return);
	}

    /**
     * @param $data
     * @return string
     * @throws phpmailerException
     */
    public function show($data = null) {
		//$this->removeAttribute('required');
		if (is_array($data)) {
			$this->logMessage('show',sprintf('unexpected data found [%s] this [%s]',print_r($data,true),print_r($this,true)),1);
			$tmp = array();
			foreach($data as $k=>$v) {
				if (strlen($v) > 0)
					$tmp[] = $this->show($v);
			}
			$this->logMessage(__FUNCTION__,sprintf("returning [%s] from [%s]", print_r($tmp,true), print_r($this,true)), 1);
			return implode("",$tmp);
		}
		else {
			if ((strlen($data) == 0 || is_null($data)) && $this->hasAttribute('prompt')) {
				$data = $this->getAttribute('prompt');
				if ($this->hasAttribute('value'))
					$this->m_attributes['value'] = $data;
			}
			$tmp = sprintf($this->GetFormat(), $this->buildAttributes(), $this->m_reformatting ? htmlspecialchars($data) : $data);
			return $tmp;
		}
	}

    /**
     * @param $value
     * @return void
     */
    public function setReformatting($value) {
		$this->m_reformatting = $value;
	}

    /**
     * @param $name
     * @return bool
     */
    public function hasAttribute($name) {
		return array_key_exists(strtolower($name),$this->m_attributes);
	}

    /**
     * @param $name
     * @return mixed|void
     */
    public function getAttribute($name) {
		if (array_key_exists(strtolower($name),$this->m_attributes))
			return $this->m_attributes[strtolower($name)];
	}

    /**
     * @return mixed
     */
    public function getFormat() {
		return $this->m_format;
	}

    /**
     * @param $str
     * @return void
     */
    public function setFormat($str) {
		$this->m_format = $str;
	}
}

class tag extends HtmlElement {

    /**
     * @param bool $reformatting
     * @throws Exception
     */
    function __construct($reformatting = true) {
		parent::__construct ($reformatting);
		$this->m_format = "%s";
		$this->m_reformatting = $reformatting;
	}

    /**
     * @param $data
     * @return string
     * @throws phpmailerException
     */
    public function show($data = null) {
		if (is_null($data)) $data = $this->getAttribute('value');
		if (is_array($data)) {
			$this->logMessage('show',sprintf('found an unexpected array [%s] this [%s]',print_r($data,true),print_r($this,true)),1);
			$tmp = '';
		}
		else
			$tmp = sprintf($this->GetFormat(), $this->m_reformatting ? htmlspecialchars($data) : $data);
		return $tmp;
	}
	
}

class tagField extends tag {
    /**
     * @param $reformatting
     */
    function __construct($reformatting = true) {
		parent::__construct($reformatting);
	}

    /**
     * @param $data
     * @return string
     * @throws phpmailerException
     */
    public function show($data = null) {
		if (is_null($data)) {
			if ($this->hasAttribute("sql")) {
				$data = $this->fetchScalar($this->getAttribute('sql'));
				$this->removeAttribute("sql");
			}
			else {
			 	$this->logMessage(__FUNCTION__,sprintf("no sql to pull data from [%s]",print_r($this,true)),1,true);
			}
		}
		$tmp = sprintf($this->GetFormat(), $this->m_reformatting ? htmlspecialchars($data) : $data);
		return $tmp;
	}
}

class errorTag extends tag {
    /**
     * @param $reformatting
     */
    function __construct($reformatting = true) {
		parent::__construct($reformatting);
	}
}

class stripped extends tag {

    /**
     * @param $reformatting
     */
    function __construct($reformatting = true) {
		parent::__construct($reformatting);
	}

    /**
     * @param $data
     * @return string
     * @throws phpmailerException
     */
    function show($data = null) {
		if (is_null($data)) $data = $this->getAttribute('value');
		$data = nl2br(strip_tags($data));
		$ct = (int)$this->getAttribute('count');
		if ($ct <= 0) $ct = 15;
		$data = $this->subwords($data,$ct);
		return parent::show($data);
	}
}

class boolean extends tag {

    /**
     * @param $reformatting
     */
    function __construct($reformatting = true) {
		parent::__construct($reformatting);
	}

    /**
     * @param $data
     * @return mixed
     */
    function show($data = null) {
		if (is_null($data)) $data = $this->getAttribute('value');
		if ($this->hasAttribute('true_false')) {
			if (is_null($data))
				$data = 'false';
			else
				$data = $data == 1 ? 'true':'false';
		}
		else {
			if (is_null($data))
				$data = 'No';
			else
				$data = $data == 1 ? 'Yes':'No';
		}
		return parent::show($data);
	}
}

class booleanIcon extends tag {

    /**
     * @param $reformatting
     */
    function __construct($reformatting = false) {
		parent::__construct($reformatting);
	}

    /**
     * @param $data
     * @return string
     * @throws phpmailerException
     */
    function show($data = null) {
		if (is_null($data)) $data = $this->getAttribute('value');
		if (is_null($data) || $data == 0)
			$data = '<i class="icon-remove glyphicon glyphicon-remove"></i>';
		else
			$data = '<em class="icon-check glyphicon glyphicon-ok"></em>';
		return parent::show($data);
	}
}

class currency extends tag {
    /**
     * @param $reformatting
     */
    function __construct($reformatting = true) {
		parent::__construct($reformatting);
	}

    /**
     * @param $data
     * @return string
     * @throws phpmailerException
     */
    function show($data = null) {
		if (is_null($data)) $data = $this->getAttribute('value');
		if ($this->m_reformatting != false) {
			if ($this->hasAttribute('mask'))
				$mask = $this->getAttribute('mask');
			else
				$mask = GLOBAL_DEFAULT_CURRENCY_FORMAT;
			$data = $this->my_money_format((float)$data, $mask);
		}
		return parent::show($data);
	}
}

class datestamp extends tag {
    /**
     * @param $reformatting
     */
    function __construct($reformatting = true) {
		$this->addAttribute('suppressNull',true);
		parent::__construct($reformatting);
	}

    /**
     * @param $data
     * @return string
     * @throws phpmailerException
     */
    function show($data = null) {
		//$this->removeAttribute('required');
		if (is_null($data)) $data = $this->getAttribute('value');
		if (($data == '0000-00-00' || $data == '0000-00-00 00:00:00') && $this->hasAttribute('suppressNull') && $this->getAttribute('suppressNull'))
			$data = null;
		if ($data != null && $this->m_reformatting != false) {
			$tmp = date_parse($data);
			if ($tmp['error_count'] == 0) {
				if ($this->hasAttribute('mask'))
					$mask = $this->getAttribute('mask');
				else
					$mask = GLOBAL_DEFAULT_DATE_FORMAT;
				$data = date($mask,strtotime($data));
			}
		}
		return parent::show($data);
	}
}

class timestamp extends datestamp {
    /**
     * @param $reformatting
     */
    function __construct($reformatting = true) {
		parent::__construct($reformatting);
		$this->addAttribute('mask',GLOBAL_DEFAULT_TIME_FORMAT);
	}

    /**
     * @param $data
     * @return string
     */
    function show($data = null) {
		return parent::show($data);
	}
}

class datetimestamp extends datestamp {
    /**
     * @param $reformatting
     */
    function __construct($reformatting = true) {
		parent::__construct($reformatting);
		$this->addAttribute('mask',GLOBAL_DEFAULT_DATETIME_FORMAT);
	}

    /**
     * @param $data
     * @return string
     */
    function show($data = null) {
		return parent::show($data);
	}
}

class span extends HtmlElement {

    /**
     * @param bool $reformatting
     * @throws Exception
     */
    function __construct($reformatting = true) {
		parent::__construct ($reformatting);
		$this->m_format = "<span %s>%s</span>"; 
	}

    /**
     * @param $data
     * @return string
     * @throws phpmailerException
     */
    public function show($data = null) {
		return parent::show($data);
	}

    /**
     * @return string
     */
    public function getFormat() {
		return $this->m_format;
	}
}

class input extends HtmlElement {

    /**
     * @param bool $reformatting
     * @throws Exception
     */
    function __construct($reformatting = true) {
		parent::__construct ($reformatting);
		$this->m_format = "<input %s />";
		$this->addAttribute('class','def_field_input');
	}

    /**
     * @param $data
     * @return string
     * @throws phpmailerException
     */
    public function show($data = null) {
		if (!is_null($data)) 
			$this->addAttribute('value',$data);
		if ($this->hasAttribute('prompt')) {
			$this->addAttribute('onfocus','setText(this,true);');
			$this->addAttribute('onblur','setText(this,false);');
			if (!$this->hasAttribute('value'))
				$this->addAttribute('value',$this->getAttribute('prompt'));
		}
		return parent::show($data);
	}

    /**
     * @return string
     */
    public function getFormat() {
		return $this->m_format;
	}

}

class fileupload extends input  {

    /**
     * @param $reformatting
     */
    function __construct($reformatting = true) {
		parent::__construct ($reformatting);
		$this->addAttributes(array('type'=>'file','class'=>'def_field_fileupload'));
	}

    /**
     * @param $data
     * @return string
     */
    function show($data = null) {	// compatibility only
		//
		//	we have the info in atttributes but the browser won't let us output it
		//
		$tmp = sprintf($this->GetFormat(), $this->buildAttributes());
		return $tmp;
	}

}

class hidden extends input  {

    /**
     * @param $reformatting
     */
    function __construct($reformatting = true) {
		parent::__construct ($reformatting);
		$this->addAttribute('type','hidden');
	}
}

class textfield extends input  {

    /**
     * @param $reformatting
     */
    function __construct($reformatting = true) {
		parent::__construct ($reformatting);
		$this->addAttribute('type','text');
	}
}

class number extends input  {

    /**
     * @param $reformatting
     */
    function __construct($reformatting = true) {
		parent::__construct ($reformatting);
		$this->addAttribute('type','number');
	}
}

class password extends input  {

    /**
     * @param $reformatting
     */
    function __construct($reformatting = true) {
		parent::__construct ($reformatting);
		$this->addAttribute('type','password');
	}
}

class button extends input  {

    /**
     * @param $reformatting
     */
    function __construct($reformatting = true) {
		parent::__construct ($reformatting);
		$this->addAttributes(array('type'=>'button','class'=>'def_field_button'));
	}
}

class submitbutton extends input  {

    /**
     * @param $reformatting
     */
    function __construct($reformatting = true) {
		parent::__construct ($reformatting);
		$this->addAttributes(array('type'=>'submit','class'=>'def_field_submit'));
	}
}

class resetbutton extends input  {

    /**
     * @param $reformatting
     */
    function __construct($reformatting = true) {
		parent::__construct ($reformatting);
		$this->addAttributes(array('type'=>'reset','class'=>'def_field_reset'));
	}
}

class email extends input  {

    /**
     * @param $reformatting
     */
    function __construct($reformatting = true) {
		parent::__construct ($reformatting);
		$this->addAttributes(array('type'=>'email','class'=>'def_field_email'));
	}
}

class textarea extends input  {

    /**
     * @param $reformatting
     */
    function __construct($reformatting = true) {
		parent::__construct ($reformatting);
		$this->m_format = '<textarea %s>%s</textarea>';
		$this->addAttributes(array('type'=>'textarea','class'=>'def_field_textarea'));
	}

    /**
     * @param $data
     * @return string
     */
    function show($data = null) {
		if (is_null($data))
			$data = $this->getAttribute('value');
		$reformat = $this->m_reformatting;
		$data = $reformat?htmlspecialchars($data):$data;
		return sprintf($this->m_format,$this->buildAttributes($this->m_attributes),$data);
	}
}

class checkbox extends input  {

    /**
     * @param $reformatting
     */
    function __construct($reformatting = true) {
		parent::__construct ($reformatting);
		$this->addAttributes(array('type'=>'checkbox','class'=>'def_field_checkbox'));
	}

    /**
     * @param $data
     * @return string
     * @throws phpmailerException
     */
    function show($data = null) {
		$this->logMessage('show',sprintf('data value [%s]',$data),4);
		if (!is_null($data)) {
			//
			//	used for arrays of deletes[], usually value is set to 1
			//	if no value set, don't assume any value is checked
			//	also, don't auto uncheck
			//
//			if ($data == $this->getAttribute('value'))
//				$this->addAttribute('checked','checked');

			if ($data == 0 || strtolower($data) == 'off')
				$this->removeAttribute('checked');
			else {
				if (!($this->hasAttribute('autoCheck') && $this->getAttribute('autoCheck') == 0))
					$this->addAttribute('checked','checked');
			}
		}
		if ($this->hasAttribute('enabled')) {
			if (!($this->getAttribute('enabled') == 'enabled' || $this->getAttribute('enabled') == 'true')) {
				$this->removeAttribute('enabled');
				$this->addAttribute('disabled','disabled');
			}
		}
		$this->logMessage("show",sprintf("this [%s] data [%s]", print_r($this,true),$data),4);
		return parent::show(null);	// don't let the parent override the data value
	}

}

class imagebutton extends input  {

    /**
     * @param $reformatting
     */
    function __construct($reformatting = true) {
		parent::__construct ($reformatting);
		$this->addAttribute('type','image');
	}
}

class radiobutton extends input  {

    /**
     * @param $reformatting
     */
    function __construct($reformatting = true) {
		parent::__construct ($reformatting);
		$this->addAttributes(array('type'=>'radio','class'=>'def_field_radiobutton'));
	}

    /**
     * @param $data
     * @return string
     */
    function show($data = null) {
		if (!is_null($data)) {
			if ($this->getAttribute('value') == $data)
				$this->addAttribute('checked','checked');
		}
		return parent::show(null);	// don't let the parent override the data value
	}
}

class option extends HtmlElement {

    /**
     * @param bool $reformatting
     * @throws Exception
     */
    function __construct($reformatting = true) {
		parent::__construct ($reformatting);
		$this->m_format = '<option %s>%s</option>';
	}

    /**
     * @return string
     */
    public function getFormat() {
		return $this->m_format;
	}
}

class select extends input  {

	protected $m_values = array();

    /**
     * @param $reformatting
     */
    function __construct($reformatting = true) {
		parent::__construct ($reformatting);
		$this->m_format = '<select %s>%s</select>';
		$this->m_reformatting = $reformatting;
		$this->addAttribute('class','def_field_ddl');
	}

    /**
     * @param $key
     * @param $value
     * @return void
     */
    function addOption($key, $value) {
		$this->m_options[$key] = $value;
	}

    /**
     * @param $options
     * @return void
     */
    function addOptions($options) {
		if (is_array($options)) {
			foreach($options as $key=>$value) {
				$this->m_options[$key] = $value;
			}
		}
	}

    /**
     * @param $name
     * @param $value
     * @param $append
     * @return void
     */
    function addAttribute($name, $value, $append = true) {
		if (strtolower($name) == 'value') {
			if ($append)
				$this->m_values[] = $value;
			else
				$this->m_values = array(0=>$value);
		}
		else parent::addAttribute($name,$value);
	}

    /**
     * @param $options
     * @return void
     */
    function addAttributes($options) {
		if (array_key_exists('value',$options)) {
			$this->m_values[] = $options['value'];
			unset($options['value']);
		}
		parent::addAttributes($options);
	}

    /**
     * @return array
     */
    function getOptions() {
		return $this->m_options;
	}

    /**
     * @param $opts
     * @param $init
     * @return void
     */
    function setOptions($opts, $init = false) {	// compatability only
		$this->m_options = array();
		if (is_array($opts))
			$this->m_options = $opts;
	}

    /**
     * @return bool
     */
    function hasOptions() {
		return count($this->m_options) > 0;
	}

    /**
     * @param $data
     * @return string
     * @throws phpmailerException
     */
    function show($data = null) {
		$this->logMessage(__FUNCTION__,sprintf("(%s)",print_r($data,true)),3);
		if ($this->hasAttribute('options')) {
			$this->addOptions($this->getAttribute('options'));
			$this->removeAttribute('options');
		}
		$classes = array();
		if (count($this->m_options) == 0) {
			if ($this->hasAttribute("defaultvalue"))
				$defValue = $this->getAttribute("defaultvalue");
			else
				$defValue = $this->hasAttribute('idlookup') || $this->hasAttribute('sequence') ? 0 : '';
			if (!($this->hasAttribute('required') && $this->getAttribute('required') == true)) {
				if ($this->hasAttribute('nonename'))
					$this->addOption($defValue,$this->getAttribute('nonename'));
				else {
					$this->addOption($defValue,'-None-');
				}
				if ($this->hasAttribute('placeholder'))
					$this->addOption($defValue,$this->getAttribute('placeholder'));
				else {
					$this->addOption($defValue,'-None-');
				}
			}
			if ($this->hasAttribute('shownone')) {
				if ($this->hasAttribute('nonename'))
					$this->addOption($defValue,$this->getAttribute('nonename'));
				else
					$this->addOption($defValue,'-None-');
			}
			if ($this->hasAttribute('options')) {
				$this->m_options = $this->getAttribute('options');
				$this->removeAttribute('options');
			}
			if ($this->hasAttribute('lookup')) {
				$sql = sprintf("SELECT code as id, value as name FROM code_lookups WHERE type = '%s' ORDER BY sort, value ASC", $this->getAttribute('lookup'));
				$this->addAttribute('sql', $sql);
			}
			if ($this->hasAttribute('idlookup')) {
				$sql = sprintf("SELECT id, value as name FROM code_lookups WHERE type = '%s' ORDER BY sort, value ASC", $this->getAttribute('idlookup'));
				$this->addAttribute('sql', $sql);
			}
			if ($this->hasAttribute('sequence')) {
				$tmp = explode('|',$this->getAttribute('sequence'));
				for($x = $tmp[0]; $x <= $tmp[1]; $x++) {
					$this->addOption($x,$x);
				}
			}
			if ($this->hasAttribute('sql')) {
				$tmp = $this->fetchOptions($this->getAttribute('sql'));
				foreach($tmp as $key=>$value) {
					$this->addOption($key,$value);
				}
			}
			if ($this->hasAttribute('optionslist')) {
				$tmp = $this->getAttribute('optionslist');
				$nodes = $this->nodeSelect( $tmp['root'], $tmp['table'], $tmp['indent'], $this->getAttribute('required'), $tmp['inclusive']);
				$this->addOptions($nodes);
				if ($tmp['table'] == 'content' && $this->hasAttribute('addContentClass')) {
					foreach($nodes as $key=>$value) {
						$classes[$key] = $this->fetchScalar(sprintf('select type from %s where id = %d',$tmp['table'],$key));
					}
				}
				$this->removeAttribute('optionslist');
				$this->m_reformatting = false;
			}
		}
		$options = array();
		if (!is_null($data)) {
			if (!$this->hasAttribute('multiple')) {
				$this->m_values = array($data);
			}
			else {
				if (!is_array($data))
					$this->m_values = array($data);
				else
					$this->m_values = $data;
			}
		}
		if ($this->hasAttribute('multiple')) {
			$name = $this->getAttribute('name');
			if (strpos($name,'[]') === false)
				$this->addAttribute('name',$name.'[]');
		}
		$tmp = implode('~',$this->m_values);
		$values = sprintf('~%s~',implode('~',$this->m_values));
		$first = false;
		foreach($this->m_options as $key=>$value) {
			$opt = new option($this->m_reformatting);
			if (count($classes) > 0 && array_key_exists($key,$classes)) {
				$opt->addAttribute('class',$classes[$key]);
			}
			if (is_array($value)) {
				$opt->addAttributes($value);
				$opt->removeAttribute($key);
				$value = $opt->getAttribute('value');
				$opt->removeAttribute('value');
			}
			$opt->addAttribute('value',$key);
			if ($this->hasAttribute('multiple')) {
				if (strpos($values,'~'.$key.'~') !== false && ($key != ''))
					$opt->addAttribute('selected','selected');
			}
			else {
				if ((!$first) && count($this->m_values) > 0) {
					$tmp = $this->m_values[0];
					if (strlen($key) > 0 && strlen($tmp) > 0 && $key == $tmp) {
						$opt->addAttribute('selected','selected');
						$first = true;
					}
				}
			}
			$options[] = $opt->show($value);
		}
		$tmp = sprintf($this->GetFormat(), $this->buildAttributes(), implode('',$options));
		$this->logMessage('show',sprintf('this [%s] options [%s]',print_r($this,true),print_r($options,true)),5);
		return $tmp;
	}

    /**
     * @return string
     */
    public function getFormat() {
		return $this->m_format;
	}
	
}

class pairedlist extends select {

    /**
     * @param $reformatting
     */
    function __construct($reformatting = true) {
		parent::__construct ($reformatting);
	}

    /**
     * @param $data
     * @return string
     * @throws phpmailerException
     */
    function show($data = null) {
		if (count($this->m_options) == 0) {
			$ids = $this->configEmails($this->getAttribute('src'));
			foreach($ids as $key=>$name) {
				$this->m_options[$key] = $name['name'];
			}
		}
		return parent::show($data);
	}
}

class multiselect extends select {

    /**
     * @param $reformatting
     */
    function __construct($reformatting = true) {
		parent::__construct ($reformatting);
		$this->addAttribute('multiple','multiple');
	}

}

class countrySelect extends select {
    /**
     * @param $reformatting
     */
    function __construct($reformatting = true) {
		parent::__construct ($reformatting);
	}
	
	//private $m_options = array();

    /**
     * @param $data
     * @return string
     * @throws phpmailerException
     */
    function show($data = null) {
		if (count($this->m_options) == 0) {
			if ($this->hasAttribute('sql')) {
				$this->addOptions($this->fetchOptions($this->getAttribute('sql')));
			}
			else {
				if (is_null($data)) {
					$data = $this->fetchScalar('select id from countries where deleted = 0 order by sort limit 1');
				}
				$this->addOptions($this->fetchOptions(sprintf('select id,country from countries where deleted = 0 order by sort')));
			}
		}
		return parent::show($data);
	}
}

class provinceSelect extends select {

	//private $m_options = array();

    /**
     * @param $reformatting
     */
    function __construct($reformatting = true) {
		parent::__construct ($reformatting);
		$this->addAttribute('class','def_field_ddl');
	}

    /**
     * @param $data
     * @return string
     * @throws phpmailerException
     */
    function show($data = null) {
		if (count($this->m_options) == 0) {
			if (!$this->hasAttribute('country_id')) {
				$this->addAttribute('country_id',$this->fetchScalar('select id from countries where deleted = 0 order by sort limit 1'));
				$this->addAttribute("sql",sprintf('select id, province from provinces where deleted = 0 and country_id = %d order by sort',$this->getAttribute('country_id')));
				$this->logMessage("show",sprintf("no country defined - grabbing default [%s]",print_r($this,true)),2);
			}
			if ($this->hasAttribute('required') && $this->getAttribute('required')==false) {
				$this->addOption('0','');
			}
			if ($this->hasAttribute('sql')) {
				$this->addOptions($this->fetchOptions($this->getAttribute('sql')));
			}
			else {
				$sql = sprintf('select id, province from provinces where deleted = 0 and country_id = %d order by sort',$this->getAttribute('country_id'));
				$this->addOptions($this->fetchOptions($sql));
			}
		}
		$this->removeAttribute('country_id');
		if (count($this->m_options) == 0) {
			$this->addOptions(array(0=>"n/a"));
		}
		return parent::show($data);
	}
}

class label extends HtmlElement {

    /**
     * @param bool $reformatting
     * @throws Exception
     */
    function __construct($reformatting = true) {
		parent::__construct ($reformatting);
		$this->m_format = '<label %s>%s</label>';
		$this->addAttribute('classs','def_field_label');
	}

    /**
     * @return string
     */
    public function getFormat() {
		return $this->m_format;
	}

}

class div extends HtmlElement {

    /**
     * @param bool $reformatting
     * @throws Exception
     */
    function __construct($reformatting = true) {
		parent::__construct ($reformatting);
		$this->m_format = '<div %s>%s</div>';
	}

    /**
     * @return string
     */
    public function getFormat() {
		return $this->m_format;
	}

}

class datefield extends textfield {

    /**
     * @param $reformatting
     */
    function __construct($reformatting = true) {
		parent::__construct ($reformatting);
		$this->addAttribute('class','def_date');
		$this->addAttribute('type','date');
	}

    /**
     * @param $data
     * @return string
     */
    function show($data = null) {
		if (is_null($data) && $this->hasAttribute('value')) $data = $this->getAttribute('value');
		if ($data == "0000-00-00" || $data == "1969-12-31")
			$data = "";	
//		if (preg_match("/[0-9][0-9][0-9][0-9][-][0-9][0-9][-][0-9][0-9]/",$data))
//			$data = date("Y-m-d",strtotime($data));
		$this->addAttribute('value',$data);
		if (!$this->hasAttribute('title'))
			$this->addAttribute('title','click to edit');
		$tmp = parent::show($data);
		return $tmp;
	}

}

class datepicker extends textfield {

    /**
     * @param $reformatting
     */
    function __construct($reformatting = true) {
		parent::__construct ($reformatting);
		$this->addAttribute('class','def_field_datepicker');
		$this->addAttribute('readonly','readonly');
	}

    /**
     * @param $data
     * @return string
     */
    function show($data = null) {
		//$this->removeAttribute('required');
		if (is_null($data) && $this->hasAttribute('value')) $data = $this->getAttribute('value');
		if ($data == "0000-00-00" || $data == "1969-12-31")
			$data = "";	
		if (preg_match("/[0-9][0-9][0-9][0-9][-][0-9][0-9][-][0-9][0-9]/",$data))
			$data = date("m/d/Y",strtotime($data));
		$this->addAttribute('value',$data);
		if (!$this->hasAttribute('title'))
			$this->addAttribute('title','click to edit');
		/*
		if ($this->hasAttribute('id')) {
			$result = sprintf('<a class="datePicker" href="#" onclick="displayDatePicker(\'%s\',$(\'#%s\')[0]); return false;">%s</a>', $this->getAttribute('name'), $this->getAttribute("id"), $this->hasAttribute("prompt") ? $this->getAttribute('prompt') : 'Pick Date');
			$this->addAttribute('onclick',sprintf("displayDatePicker('%s',$('#%s')[0]); return false;",$this->getAttribute('name'), $this->getAttribute("id")));
		}
		else {
			$result = sprintf('<a class="datePicker" href="#" onclick="displayDatePicker(\'%s\'); return false;">%s</a>', $this->getAttribute('name'), $this->hasAttribute("prompt") ? $this->getAttribute('prompt') : 'Pick Date');
			$this->addAttribute('onclick',sprintf("displayDatePicker('%s'); return false;",$this->getAttribute('name')));
		}
		*/
		$tmp = parent::show($data);	//.$result;
		return $tmp;
	}
}

class time extends input {
    /**
     * @param $reformatting
     */
    function __construct($reformatting = true) {
		parent::__construct ($reformatting);
		$this->addAttribute('type','time');
		$this->addAttribute('class','def_field_time');
	}

    /**
     * @param $data
     * @return string
     */
    function show($data = null) {
		return parent::show($data);
	}

}

class timepicker extends input {
    /**
     * @param $reformatting
     */
    function __construct($reformatting = true) {
		parent::__construct ($reformatting);
		if (!$this->hasAttribute('display')) $this->addAttribute('display','hm');
		$this->addAttribute('class','def_field_timepicker');
	}

    /**
     * @param $data
     * @return string
     * @throws phpmailerException
     */
    function show($data = null) {
		//$this->removeAttribute('required');
		if (is_null($data) && $this->hasAttribute('value')) $data = $this->getAttribute('value');
		//
		//	worst case should be 00:00:00 for null
		//
		$fmt = $this->getAttribute('display');
		$tmp = explode(':',$data);
		if (count($tmp) < 2) {
			$parts = array('h'=>'','m'=>'','s'=>'');
		}
		else {
			$parts = array('h'=>array_key_exists(0,$tmp)?$tmp[0]:'','m'=>array_key_exists(1,$tmp)?$tmp[1]:'','s'=>array_key_exists(2,$tmp)?$tmp[2]:'');
		}
		if (strpos($fmt,'h') !== false)
			$hr = new select();
		else
			$hr = new hidden();
		if (strpos($fmt,'m') !== false)
			$mn = new select();
		else
			$mn = new hidden();
		if (strpos($fmt,'s') !== false)
			$sec = new select();
		else
			$sec = new hidden();
		$hr->addAttributes(array('name'=>$this->getAttribute('name').'_hh','lookup'=>'hours','required'=>$this->getAttribute('required'),'value'=>$parts['h'],'class'=>$this->getAttribute('class')));
		$mn->addAttributes(array('name'=>$this->getAttribute('name').'_mm','lookup'=>'minutes','required'=>$this->getAttribute('required'),'value'=>$parts['m'],'class'=>$this->getAttribute('class')));
		$sec->addAttributes(array('name'=>$this->getAttribute('name').'_ss','lookup'=>'seconds','required'=>$this->getAttribute('required'),'value'=>$parts['s'],'class'=>$this->getAttribute('class')));
		if ($this->hasAttribute('AMPM')) {
			$ampm = new select();
			$ampm->addAttributes(array('name'=>$this->getAttribute('name').'_ampm','options'=>array('AM'=>'AM','PM'=>'PM'),'required'=>true,'value'=>$parts['h']< '12'?'AM':'PM','class'=>$this->getAttribute('class')));
			$hr->addAttributes(array('lookup'=>'hours_ampm'));
			if ($parts['h'] > 11) {
				$hr->addAttribute('value',sprintf('%02d',$parts['h']-12),false);
			}
			//else
			//	$hr->addAttributes(array('name'=>$this->getAttribute('name').'_hh','lookup'=>'hours_ampm','required'=>$this->getAttribute('required'),'value'=>$parts['h'],'class'=>$this->getAttribute('class')));
			$tmp = sprintf('%s%s%s%s%s %s',
				$hr->show(),get_class($hr) == 'select' && get_class($mn) == 'select' ? ':' : '',
				$mn->show(),get_class($mn) == 'select' && get_class($sec) == 'select' ? ':' : '',
				$sec->show(),$ampm->show());
			//$this->removeAttribute('AMPM');
		}
		else {
			$hr->addAttributes(array('name'=>$this->getAttribute('name').'_hh','lookup'=>'hours','required'=>$this->getAttribute('required'),'value'=>$parts['h'],'class'=>$this->getAttribute('class')));
			$tmp = sprintf('%s%s%s%s%s',
				$hr->show(),get_class($hr) == 'select' && get_class($mn) == 'select' ? ':' : '',
				$mn->show(),get_class($mn) == 'select' && get_class($sec) == 'select' ? ':' : '',
				$sec->show());
		}
		return $tmp;
	}

}

class datetimepicker extends datepicker {

    /**
     * @param $reformatting
     */
    function __construct($reformatting = true) {
		parent::__construct ($reformatting);
		$this->addAttribute('class','def_field_datetimepicker');
	}

    /**
     * @param $data
     * @return array|mixed|string|string[]
     * @throws phpmailerException
     */
    function show($data = null) {
		//$this->removeAttribute('required');
		if (is_null($data) && $this->hasAttribute('value')) $data = $this->getAttribute('value');
		//
		//	worst case should be 0000-00-00 00:00:00 for null
		//	"T" indicates a DATE_ATOM was used
		//
		if (strpos($data,"T"))
			$parts = explode(' ',date("Y-m-d H:i:s", strtotime($data)));
		else
			$parts = explode(' ',$data);
		if (count($parts) < 2) {
			$parts[1] = '00:00:00';
		}
		$date = $parts[0];
		$time = explode(':',$parts[1]);
		if ($date == "0000-00-00" || $date == "1969-12-31" || $date == '') {
			$date = "";	
			$parts[1] = '';
			$this->addAttribute('value','');
		}
		else {
			if (preg_match("/[0-9][0-9][0-9][0-9][-][0-9][0-9][-][0-9][0-9]/",$date))
				$date = date("m/d/Y",strtotime($date));
			$this->addAttribute('value',sprintf('%s %s',$date,$parts[1]));
		}
		$tmp = parent::show($date);
		$tm = new timepicker($this->m_reformatting);
		if ($this->hasAttribute('AMPM')) {
			$tm->addAttribute('AMPM',$this->getAttribute('AMPM'));
			//$this->removeAttribute('AMPM');
		}
		$tm->addAttributes(array('name'=>$this->getAttribute('name'),'required'=>true, 'value'=>$parts[1]));
		if ($this->hasAttribute("template")) {
			$f = new Forms();
			$f->setHTML($this->getAttribute("template"));
			$f->setData("date",$tmp);
			$f->setData("time",$tm->show());
			$tmp = $f->show();
			$this->logMessage(__FUNCTION__,sprintf("found a template [%s]", $this->getAttribute("template")),1);
		}
		else
			$tmp = sprintf('<div class="datetimepicker"><div class="date">%s</div> <div class="time">%s</div></div>',$tmp,$tm->show());
		return $tmp;
	}

}

class image extends HtmlElement {

    /**
     * @param bool $reformatting
     * @throws Exception
     */
    function __construct($reformatting = true) {
		parent::__construct ($reformatting);
		$this->m_format = "<img %s />";
		$this->m_reformatting = $reformatting;
		$this->addAttribute('class','def_field_image');
	}

    /**
     * @param $data
     * @return string
     * @throws phpmailerException
     */
    function show($data = null) {
		if (!is_null($data))
			$this->addAttribute('src', $data);
		$src = $this->getAttribute('src');
		if ($this->hasAttribute('unknown')) {
			if (is_null($src) || strlen($src) == 0) {
				$src = '/images/unknown.jpg';
				$this->addAttribute('src','/images/unknown.jpg');
			}
		} else {
			if (is_null($src) || strlen($src) == 0 || strpos($src,'unknown.jpg') !== false)
				return '';	// missing or unknown image
		}
		if (!$this->hasAttribute('alt'))
			$this->addAttribute('alt', sprintf('Image %s',$this->getAttribute('src')));
		if (array_key_exists("secureLink",$GLOBALS) && $GLOBALS['secureLink']) {
			if (strpos($src,'http://') === false && strpos($src,'https://') === false)
				$this->addAttribute('src',sprintf('https://%s%s',HOSTNAME,$src));
		}
		else {
			if (strpos($src,'http://') === false)
				$this->addAttribute('src',sprintf('http://%s%s',HOSTNAME,$src));
		}
		return parent::show();
	}
}

class li extends option {

    /**
     * @param $reformatting
     */
    function __construct($reformatting = true) {
		parent::__construct ($reformatting);
		$this->m_format = "<li %s>%s</li>";
		$this->m_reformatting = $reformatting;
		$this->addAttribute('class','def_field_li');
	}

    /**
     * @param $data
     * @return string
     * @throws phpmailerException
     */
    function show($data = null) {
		if (is_null($data)) {
			if ($this->hasAttribute('value')) {
				$data = $this->getAttribute('value');
				$this->removeAttribute('value');
			}
		}
		return parent::show($data);
	}
}

class ul extends select {

    /**
     * @param $reformatting
     */
    function __construct($reformatting = true) {
		parent::__construct ($reformatting);
		$this->m_format = "<ul %s>%s</ul>";
		$this->m_reformatting = $reformatting;
		$this->addAttribute('class','def_field_ul');
	}

    /**
     * @param $obj
     * @param $value
     * @return void
     */
    function addLI($obj, $value) {
		$this->addOption($obj,$value);
	}

    /**
     * @param $data
     * @return string
     * @throws phpmailerException
     */
    function show($data = null) {
		if (is_null($data)) $data = $this->getAttribute('value');
		if ($this->hasAttribute('options')) {
			$this->addOptions($this->getAttribute('options'));
		}
		if (count($this->getOptions()) == 0) {
			if ($this->hasAttribute('sql')) {
				$tmp = $this->fetchOptions($this->getAttribute('sql'));
				foreach($tmp as $key=>$value) {
					$this->addOption($key,$value);
				}
				//$this->removeAttribute('sql');
			}
			if ($this->hasAttribute('optionslist')) {
				$tmp = $this->getAttribute('optionslist');
				$nodes = $this->nodeSelect( $tmp['root'], $tmp['table'], $tmp['indent'], $this->getAttribute('required'), $tmp['inclusive']);
				$this->addOptions($nodes);
				$this->removeAttribute('optionslist');
				$this->m_reformatting = false;
			}
		}
		$options = array();
		foreach($this->getOptions() as $key=>$values) {
			$opt = new li($this->m_reformatting);
			$opt->addAttributes($values);
			if ($data == $opt->getAttribute('value')) {
				$class = $opt->getAttribute('class');
				$opt->addAttribute('class', rtrim(sprintf('%s %s',$class,'active'),' '));
			}
			$options[] = $opt->show();
		}
		$this->removeAttributes(array(0=>'sql',1=>'required',2=>'value',3=>'options'));
		$tmp = sprintf($this->GetFormat(), $this->buildAttributes(), implode('',$options));
		return $tmp;
	}

}

class ol extends ul {
    /**
     * @param $reformatting
     */
    function __construct($reformatting = true) {
		parent::__construct ($reformatting);
		$this->m_format = "<ol %s>%s</ol>";
		$this->m_reformatting = $reformatting;
		$this->addAttribute('class','def_field_ul');
	}
}

class captcha extends HtmlElement {

    /**
     * @param bool $reformatting
     * @throws Exception
     */
    function __construct($reformatting = true) {
		parent::__construct ($reformatting);
		$this->m_format = "";
		$this->m_reformatting = $reformatting;
	}

    /**
     * @param $data
     * @return mixed|string
     * @throws phpmailerException
     */
    function show($data = null) {
		$result = Ajax::captcha();
		if ($result['status'])
			return $result['html'];
		else {
			$this->logMessage('show',sprintf('captcha failed, return [%s]',print_r($result,true)),1,true);
			return "";
		}
	}
}

class link extends HtmlElement {
    /**
     * @param bool $reformatting
     * @throws Exception
     */
    function __construct($reformatting = true) {
		parent::__construct ($reformatting);
		$this->m_format = "<a %s>%s</a>";
		$this->m_reformatting = $reformatting;
		$this->addAttribute('class','def_field_link');
		$this->m_reformatting = $reformatting;
	}

    /**
     * @param $data
     * @return string
     */
    function show($data = null) {
		if (is_null($data)) $data = $this->getAttribute('value');
		$this->removeAttributes(array(2=>'value'));
		$tmp = sprintf($this->GetFormat(), $this->buildAttributes(), $data);
		return $tmp;
	}
}

class productOptGroup extends HtmlElement {
    /**
     * @param bool $reformatting
     * @throws Exception
     */
    function __construct($reformatting = true) {
		parent::__construct ($reformatting);
		$this->m_format = '<select %s>%s</select>';
		$this->m_reformatting = $reformatting;
		$this->addAttribute('class','def_field_ddl');
	}

    /**
     * @param $data
     * @return string
     * @throws phpmailerException
     */
    function show($data = null) {
		$this->logMessage(__FUNCTION__,sprintf("this [%s] data [%s]", print_r($this,true), print_r($data,true)), 1);
		$grps = $this->fetchAll(sprintf("select * from product_folders order by left_id"));
		$list = array();
		if (!($this->hasAttribute('required') && $this->getAttribute('required') == true)) $list[] = sprintf("<option value='0'>-none-</option>");
		foreach($grps as $k=>$v) {
			$list[] = $this->subOptGroup($v, $data);
		}
		$tmp = sprintf($this->GetFormat(), $this->buildAttributes(), implode('',$list));
		return $tmp;
	}

    /**
     * @param $fldr
     * @param $data
     * @return string
     * @throws phpmailerException
     */
    function subOptGroup($fldr, $data) {
		$grp[] = sprintf("<optgroup label='%s'>", $fldr["title"]);
		if ($this->hasAttribute("where")) {
			$prods = $this->fetchAll(sprintf("select p.* from product p, product_by_folder pf where pf.folder_id = %d and p.id = pf.product_id and %s", $fldr["id"], $this->getAttribute("where")));
		}
		else {
			$prods = $this->fetchAll(sprintf("select p.* from product p, product_by_folder pf where pf.folder_id = %d and p.id = pf.product_id", $fldr["id"]));
		}
		foreach($prods as $k=>$v) {
			$grp[] = sprintf("<option value='%d' %s>%s</option>", $v["id"], $data == $v["id"] ? "selected='selected'" : "", $v["name"]);
		}
		$grp[] = "</optgroup>";
		return implode("",$grp);
	}
}
?>