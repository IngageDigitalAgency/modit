<?php

class Forms extends Common {
	
	private $m_data = array();
	private $m_fields = array();
	protected $m_html;
	private $m_name = false;
	private $m_specialOptions = array("checked"=>"checked","type"=>"type", "lookup"=>"lookup", "sql"=>"sql", "validation"=>"validation","field"=>"field","database"=>"database","values"=>"values","datakey"=>"datakey");
	//private $m_options;
	private $m_requirements = array();
	private $m_status = array();
	private $m_error = array();
	private $m_module = array();
	private $m_rawValidation = array();

	public function __construct() {
		parent::__construct ();
		$this->setOption('method','post');
		$this->setOption('action','');
		$this->addElement('random', 'tag',array('name'=>'random','value'=>rand()));
		$this->addElement('FULLDOMAIN', 'tag', array('value'=>HOSTNAME));
		$this->addElement('TODAY_DAY','tag',array('value'=>date('d')));
		$this->addElement('TODAY_MONTH','tag',array('value'=>date('M')));
		$this->addElement('TODAY_YEAR','tag',array('value'=>date('Y')));
		$this->addElement('SITENAME', 'tag', array('value'=>SITENAME));
		$this->addElement('FRONTEND','tag',array('value'=>DEFINED('FRONTEND')?FRONTEND:0));
		$a = array_key_exists("",$_SERVER) ? strtolower($_SERVER["HTTP_USER_AGENT"]) : "";
		$this->addElement("USER_AGENT",'tag',array('value'=>$a));
		$this->addElement("IS_ANDROID",'tag',array('value'=>strpos($a,"android")!==FALSE));
		$this->addElement("IS_WINDOWS",'tag',array('value'=>strpos($a,"windows")!==FALSE));
		$this->addElement("IS_MAC",'tag',array('value'=>strpos($a,"mac")!==FALSE));
	}
	
	function __destruct() {
	
	}

	public function reset() {
		$this->m_data = array();
		$this->m_status = array();
		$this->m_error = array();
		foreach($this->m_fields as $key=>$fld) {
			if (get_class($fld) == 'errorTag')
				unset($this->m_fields[$key]);
		}
		$this->addElement('random', 'tag',array('name'=>'random','value'=>rand()));
	}

	public function setModule($module = array()) {
		$this->m_module = $module;
	}

	public function getModule() {
		return $this->m_module;
	}

	public function addFormSuccess($msg) {
		$this->m_status[] = $msg;
	}
	
	public function addFormError($msg) {
		$this->m_error[] = $msg;
	}

	public function getFormErrors() {
		return $this->m_error;
	}

	public function getValidation() {
		return $this->m_rawValidation;
	}

	public function setValidation($msgs) {
		$this->m_rawValidation = $msgs;
	}

	function init($formname, $options = array()) {
		if (strlen($formname) > 0) {
			if (is_dir($formname)) {
				return;
			}
			$formname = realpath($formname);
			if (!file_exists($formname)) {
				$this->logMessage('init', sprintf("Template does not exist [%s]", $formname), 1, true);
				return;		
			}
			if (!$fh = fopen($formname,"r")) {
				$this->logMessage('init', sprintf("Error reading template [%s]", $formname), 1, true);
				return;
			}
			if (filesize($formname) > 0)
				$this->m_html = fread($fh,filesize($formname));
			fclose($fh);
			$this->m_html = file_get_contents($formname);
		}
		$this->setOptions($options);
	}
	
	function show($recursive=true) {
		$tmpHtml = $this->m_html;
		if ($this->hasOption('formDelimiter')) 
			$delims = explode('|',$this->getOption('formDelimiter'));
		else
			$delims = array(0=>'%%',1=>'%%');
		preg_match_all(sprintf('#%s(.*?)%s#',$delims[0],$delims[1]), $tmpHtml, $matches);
		$status = implode('<br/>',array_merge($this->m_status,$GLOBALS['globals']->getMessages()));
		$errors = implode('<br/>',array_merge($this->m_error,$GLOBALS['globals']->getErrors()));
		$msgTmp = "";
		if (strlen($status) > 0) {
			$msgTmp = sprintf('<div class="alert alert-success">%s</div>',$status,false);
		}
		if (strlen($errors) > 0) {
			$msgTmp .= sprintf('<div class="alert alert-error alert-danger">%s</div>',$errors,false);
		}
		if (strlen($msgTmp) > 0)
			$this->addTag('errorMessage',$msgTmp,false);
		$this->m_status = array();
		$this->m_error = array();
		while (count($matches[0]) > 0) {
			$tmp = array_keys($matches[1]);
			$key = $tmp[0];
			$tag = $matches[0][$key];
			$fld = $matches[1][$key];
			$value = '';
			//
			//	adding a show a||b [show a (if exists) else b]
			//
			$tmp = explode('||',$fld);
			$value = '';
			foreach($tmp as $subkey=>$subvar) {
				if (strlen($value) == 0)
					$value = $this->buildField($subvar);
			}
			if (is_array($value))
				$tmpHtml = str_replace($tag,implode('',$value),$tmpHtml);
			else
				$tmpHtml = str_replace($tag,$value,$tmpHtml);
			if ($recursive)
				preg_match_all(sprintf('#%s(.*?)%s#', $delims[0], $delims[1]), $tmpHtml, $matches);
			else {
				unset($matches[0][$key]);
				unset($matches[1][$key]);
			}
		}
		$tmpHtml = $this->processConditionals($tmpHtml);
		$this->logMessage('show', sprintf("Return value: [%s]", $tmpHtml), 4);
		$this->logMessage('show', sprintf("Form: [%s]", print_r($this,true)), 5);
		return $tmpHtml;
	}

	function buildField($fld) {
		$reformat = null;
		$value = '';
		if (strpos($fld,',') !== false) {
			$tmp1 = explode(",",$fld);
			$fld = $tmp1[0];
			if ($tmp1[1] == 'true') $reformat = true;
			if ($tmp1[1] == 'false') $reformat = false;
		}
		if ($this->fieldExists($fld)) {
			$value = $this->showField($fld,$reformat);
		}
		else {
			if (($fpos = strpos($fld,':')) !== false) {
				$type = substr($fld,0,$fpos); 
				$fld = substr($fld,$fpos+1);
				switch(strtolower($type)) {
					case 'get':
						$tmp = $_GET;
						$value = $this->checkArray($fld,$tmp);
						break;
					case 'request':
						$tmp = $_REQUEST;
						$value = $this->checkArray($fld,$tmp);
						break;
					case 'post':
						$tmp = $_POST;
						$value = $this->checkArray($fld,$tmp);
						break;
					case 'session':
						$tmp = $_SESSION;
						$value = $this->checkArray($fld,$tmp);
						break;
					case 'tooltip':
						if ($rec = $this->fetchSingle(sprintf('select * from tooltips where tipname = "%s" and internal = 1',$fld))) {
							$value = sprintf('<a class="popovers" data-content="%s" rel="popover" href="#" data-original-title="%s"><img style="vertical-align: middle;" src="/admin/images/help.png" width="16" height="16" alt="Help" border="0" /></a>',$rec['text'],$rec['title']);
						}
						else $value = '';
						break;
					case 'form':
						if (array_key_exists($fld,$this->m_options))
							$value = $this->m_options[$fld];
						break;
					case 'module':
						$tmp = explode(':',$fld);
						if (is_array($module = $this->getModule()) && count($module) > 0)
							$value = $this->checkArray(implode(':',$tmp),$module);
						else $this->logMessage(__FUNCTION__,sprintf("attempt to get module configuration that hasn't been set yet this [%s]",print_r($this,true)),1,true);
						break;
					case 'global':
						if (strpos($fld,':') === false)
							$value = $this->getGlobalVar($fld);
						else {
							$this->logMessage('show',sprintf('multi level global [%s]',$fld),3);
							$tmp = explode(':',$fld);
							$tmp1 = $this->getGlobalVar($tmp[0]);
							if (is_array($tmp1) && array_key_exists($tmp[1],$tmp1))
								$value = $tmp1[$tmp[1]];
						}
						break;
					case 'config':
						if (strpos($fld,':') === false)
							$value = $this->getConfigVar($fld);
						else {
							$tmp = explode(':',$fld);
							if ($tmpData = $this->getConfigData($tmp[0])) {
								switch($tmpData['field_type']) {
								case 'paired_list':
									$tmpData['payload'] = $this->depairOptions($tmpData['value']);
									break;
								case 'address':
									$addrId = $tmpData['value'];
									$tmpData = $this->fetchSingle(sprintf('select * from addresses where id = %d',$addrId));
									$tmpData = Address::formatData($tmpData);
									break;
								default:
									$tmpData = $this->getConfigVar($tmp[0]);
									break;
								}
								unset($tmp[0]);
								$value = $this->checkArray(implode(':',$tmp),$tmpData);
							}
						}
						break;
					case 'custom':
						$c = new Custom(0);
						$d = explode(":",$fld);
						if (method_exists($c, $d[0])) {
							$value = $c->{$d[0]}($d, $this);
						}
						break;
					case 'device':
					/*
						if ($wurfl = $GLOBALS['globals']->getWurfl())
							$value = $wurfl->getCapability($fld);
					*/
						$this->logMessage(__FUNCTION__,sprintf("module still uses wurfl [%s]", print_r($this,true)),1,true);
						$value="";
						break;
					case 'page':
						switch($fld) {
						case 'url':
							$value = $this->getPageName();
							break;
						default:
							break;
						}
						break;
					case 'field':
						$tmp = explode("^",$fld);
						if (class_exists($tmp[0])) {
							$v = new $tmp[0];
							$value = $v->show($this->getData($tmp[1]));
						}
						else {
							$value = "";
						}
						break;
					case "inline":
						$value = $fld;
					default:
						if (array_key_exists($type,$this->m_data) && is_array($this->m_data[$type])) {
							$this->logMessage('show',sprintf('its an array fld [%s]',$fld),5);
							$tmp = explode(':',$fld);
							if (array_key_exists($tmp[0],$this->m_data[$type])) {
								$loc = $this->m_data[$type];
								$fldName = "";
								foreach($tmp as $xx=>$yy) {
									if (!is_array($loc)) {
										if (DEFINED("DEV") && DEV==TRUE)
											$this->logMessage(__FUNCTION__,sprintf("non-array requested in tmp [%s] loc [%s] form [%s]", print_r($tmp,true), print_r($loc,true), print_r($this,true)),3,true);
									}
									else {
										if (array_key_exists($yy,$loc)) {
											$fldName = $yy;
											$loc = $loc[$yy];
										}
										else {
											if (DEFINED("DEV"))
												$this->logMessage(__FUNCTION__, sprintf("not an array field yy [%s] loc [%s] tmp [%s]", print_r($yy,true), print_r($loc,true), print_r($tmp,true)), 3);
											$fldName = $yy;
											$loc = "";
										}
									}
								}
								if (array_key_exists($fldName,$this->m_fields)) {
									$obj = $this->m_fields[$fldName];
								}
								else {
									$obj = new tag(is_null($reformat) ? true : $reformat);
								}
								$value = $obj->show($loc);
							}
						}
						break;
				}
			}
			else {
				$obj = new tag($reformat);
				$value = $obj->show($this->getData($fld));
				//$value = $this->getData($fld);
			}
		}
		return $value;
	}

	function getField($name) {
		if (array_key_exists($name,$this->m_fields))
			return $this->m_fields[$name];
		else return null;
	}

	function showField($fld,$reformat) {
		$element = $this->getField($fld);
		$value = $this->getData($element->hasAttribute("datakey")?$element->getAttribute('datakey'):$element->getAttribute('name'));
		if (!is_null($reformat)) {
			$element->addAttribute('reformatting',$reformat);
		}
		if (get_class($element) == 'radiobutton'){
			//
			//	radio buttons we have to loop through to find the 'checked' one
			//
			$name = $element->getAttribute('name');
			foreach($this->m_fields as $obj) {
				if (get_class($obj) == 'radiobutton' && $obj->getAttribute('name') == $name) {
					if ($obj->getAttribute('value') == $value)
						$obj->addAttribute('checked','checked');
					else $obj->removeAttribute('checked');
				}
			}
		}
		if (get_class($element) == 'checkbox'){
			$this->logMessage("showField",sprintf("checkbox test value [%s] data [%s] field [%s]",$value,print_r($this->m_data,true),print_r($element,true)),5);
		}
		if (get_class($element) == 'select' || get_class($element) == 'tagField'){
			if ($element->hasAttribute('sql')) {
				$sql = $element->getAttribute('sql');

				$t = new Forms();
				$t->addData($this->getAllData());
				$t->setModule($this->getModule());
				$t->setHTML($sql);
				$s = $t->show();
				$sql = $s;
				$element->addAttribute('sql',$sql);
			}
		}
		/*
		 *	New code dealing with recursive values in the field definition ("value"=>"%%some-other-data-field%%")
		 *	So far hasn't blown up but has me nervous - exclude checkboxes
		 *	Only kicks in if the value from the form data is null/empty
		 */
		if (get_class($element) != 'checkbox'){
			$typ = gettype($value);
			if (	(!is_array($value)) &&
						(($typ == "string" && strlen($value) == 0) || ($typ == "integer" && $value == 0) || ($typ == "double" && $value == 0)) &&
						$element->hasAttribute("value")) {
				$value = $element->getAttribute("value");
				$delims = $this->getDelimiters();
				if (preg_match_all(sprintf('#%s(.*?)%s#',$delims[0],$delims[1]), $value, $matches) > 0) {
					foreach($matches[0] as $tmpkey=>$tmpvalue) {
						$tag = $matches[0][$tmpkey];
						$fld = $matches[1][$tmpkey];
						$tmp = $this->getData($fld);
						$value = str_replace($tag,$tmp,$value);
					}
				}
			}
		}
		return $element->show($value);
	}

	function addData($data = array()) {
		if (!is_array($data) || is_null($data)) {
			if (DEV)
				$this->logMessage('addData',sprintf('invalid data passed [%s] this [%s]',print_r($data,true),print_r($this,true)),1,true);
			return;
		}
		$this->m_data = array_merge($this->m_data,$data);
		//
		//	special case of country/province
		//
		if ($this->fieldExists('country_id') && $this->fieldExists('province_id')) {
			$ctry = $this->getData($this->getField('country_id')->getAttribute('name'));
			if ($ctry == "" || (is_int($ctry) && $ctry == 0)) {	// usually an int, but could be the name for display purposes ["Canada"]
				$this->setData('country_id',$this->fetchScalar('select id from countries where deleted = 0 order by sort limit 1'));
				$ctry = $this->getData('country_id');
				$this->logMessage('addData',sprintf('set default country to %s data [%s]',$ctry,print_r($this->m_data,true)),3);
			}
			$this->m_fields['province_id']->addAttribute("country_id",$ctry);
			$this->m_fields['province_id']->addAttribute('sql',sprintf('select id, province from provinces where country_id = %d and deleted = 0 order by sort',$ctry));
			$this->logMessage("addData",sprintf("added country_id attribute to province_id [%d] [%s]",$ctry,print_r($this->m_fields["province_id"],true)),3);
		}
		foreach($this->m_fields as $fldName=>$field) {
			if (get_class($field) == 'checkbox') {
				$name = $this->getDataName($fldName);
				if (!(array_key_exists($name,$data))) {
					//
					//	if this is a post, unchecked checkboxes do not get passed back to us
					//	note: a data value found of 0 also falls in here but no damsge [0 == null in php]
					//
					$this->logMessage('addData',sprintf('overriding missing checkbox [%s] [%s]',$name,print_r($this->getAllData(),true)),4);
					$this->m_data[$name] = 0;
				}
			}
		}
	}

	function getData($name) {
		$return = null;
		$name = str_replace("[]","",$name);	//array inputs are format name[]
		if (array_key_exists($name,$this->m_data)) {
			$return = $this->m_data[$name];
		}
		else {
			$this->logMessage('getData',sprintf('checking for array data [%s] strpos[%s]',$name,strpos($name,":")),4);
			//
			//	is this an html input array?
			//
			if (strpos($name,":") !== FALSE) {
				$this->logMessage(__FUNCTION__,sprintf("found an array reference [%s] [%s]",$name,strpos($name,":")),2);
				return $this->checkArray($name,$this->m_data);
			}
			if (preg_match('/[a-z]*\[[a-z]*\]$/',$name) !== false) {
				//
				//	name is expected as arrayname[name][] or arrayname[name]
				//
				$arr = explode('[',$name);
				$tmp = '';
				$fld = '';
				$delims = $this->getDelimiters();
				foreach($arr as $key=>$value) {
					$arr[$key] = str_replace(']','',$value);
					if (strpos($arr[$key],$delims[0]) === false) {
						if ($tmp == '') 
							$tmp = $arr[$key];
						else
							if ($fld == '') 
								$fld = $arr[$key];
					}
				}
				if (array_key_exists($tmp,$this->m_data)) {
					if (!is_array($this->m_data[$tmp])) {
						if (array_key_exists($tmp,$this->m_data)) {
							$return = $this->m_data[$tmp];
						}
						else
							if ($fld != '' && strpos($fld,$delims[0]) === false) {
								//
								//	bastardized input array of some kind
								//
								$this->logMessage('getData',sprintf('invalid data detected this [%s] looking for [%s] tmp [%s]',print_r($this->m_data,true),$fld,$tmp),2,false);
								$return = null;
							}
						//else $return = array_key_exists($tmp,$this->m_data)?$this->m_data[$tmp]:'';
					}
					else {
						if ($fld != "" and array_key_exists($fld,$this->m_data[$tmp]))
							$return = $this->m_data[$tmp][$fld];
						else
							$return = "";	//$this->m_data[$tmp];
					}
				}
				else {
					if (array_key_exists($fld,$this->m_data))
						$return = $this->m_data[$fld];
				}
			}
			else {
				$this->logMessage(__FUNCTION__,sprintf('looking for [%s] in [%s]',$name,print_r($this->m_data,true)),1);
				$return = $this->checkArray($name,$this->m_data);
			}
		}
		$this->logMessage('getData',sprintf('return [%s]',print_r($return,true)),4);
		return $return;
	}
	
	function getAllData() {
		return $this->m_data;
	}

	function setData($name,$value) {
		$this->m_data[$name] = $value;
	}

	function fieldExists($name) {
		return array_key_exists($name,$this->m_fields);
	}

	function buildForm($fieldList) {
		if (!is_array($fieldList)) {
			$this->logMessage(__FUNCTION__,sprintf("invalid array received [%s]",print_r($fieldList,true)),1,true,true);
			return;
		}
		if (array_key_exists('options',$fieldList)) {
		}
		foreach($fieldList as $fldName=>$field) {
			if ($fldName == 'options') {
				if (!is_array($field)) {
					$this->logMessage('buildForm',sprintf('invalid options passed in [%s] list [%s]',print_r($field,true),print_r($fieldList,true)),1,true);
				}
				else {
					$this->setOptions($field);
				}
			} else {
				$options = array();
				if (!array_key_exists('name',$field)) {
					$field['name'] = $fldName;
					$fieldList[$fldName]['name'] = $fldName;
				}
				foreach($field as $key=>$value) {
					if (!array_key_exists($key,$this->m_specialOptions)) {
						$options[$key] = $value;
					}
				}
				if (array_key_exists('checked',$field) && $field['checked'] != '') $options['checked'] = 'checked';
				try {
					if (!array_key_exists('type',$field)) {
						$this->logMessage(__FUNCTION__,sprintf("missing field type in [%s]", print_r($field,true)),1,true,true);
						$field['type'] = 'tag';
					}
					$newField = $this->addElement($fldName,$field['type'],$options);
				}
				catch(Exception $e) {
					$this->logMessage('buildForm',sprintf('invalid class type [%s] field [%s]',$field['type'],print_r($field,true)),1,true);
				}
				if ($field["type"] == "tagField")
					if (array_key_exists("sql",$field))
						$newField->addAttribute("sql",$field["sql"]);
				if ($field['type'] == 'select') {
					if (array_key_exists('values',$field) && count($field['values']) > 0) {
						$newField->addOptions($field['values']);
					} else {
						if (array_key_exists("options",$field)) {
							$newField->addoptions($field["options"]);
						} elseif (array_key_exists('lookup',$field)) {
							$sql = sprintf("SELECT code as id, value as name FROM code_lookups WHERE type = '%s' ORDER BY sort, value ASC", $field['lookup']);
							$newField->addAttribute('sql', $sql);
						}
						elseif (array_key_exists('sql',$field)) {
							$newField->addAttribute('sql',$field['sql']);
						}
						// moved to the show routine
					}
				}
				if ((array_key_exists('required',$field) && $field['required']) || 
					(array_key_exists('validation',$field) && strlen($field['validation']) > 0)) {
					$validation = array_key_exists('validation',$field) ? $field['validation'] : 'string';
					$message = array_key_exists('errormsg',$field) ? $field['errormsg'] : '*';
					$required = array_key_exists('required',$field) ? $field['required'] : false;
					$this->addRequirement($fldName,$newField,$validation,$message,$required);
				}
				if ($field['type'] == 'datetimepicker' || $field['type'] == 'datepicker') {
					if ($field['type'] == 'datetimepicker')
						$validation = array_key_exists('validation',$field) ? $field['validation'] : 'datetime';
					else
						$validation = array_key_exists('validation',$field) ? $field['validation'] : 'date';
					$message = array_key_exists('errormsg',$field) ? $field['errormsg'] : '*';
					$required = array_key_exists('required',$field) ? $field['required'] : false;
					$this->addRequirement($fldName,$newField,$validation,$message,$required);
				}
				if ($field['type'] == 'timepicker') {
					$validation = array_key_exists('validation',$field) ? $field['validation'] : 'time';
					$message = array_key_exists('errormsg',$field) ? $field['errormsg'] : '*';
					$required = array_key_exists('required',$field) ? $field['required'] : false;
					$this->addRequirement($fldName,$newField,$validation,$message,$required);
				}
			}
		}
		return $fieldList;
	}
	
	function addTag($name, $value, $reformatting = true) {
		$this->setData($name,$value);
		return $this->addElement($name,'tag',array('value'=>$value, 'reformatting'=>$reformatting,'name'=>$name));
	}

	function addElement($name, $type, $attributes = array()) {
		if (class_exists($type)) {
			if (array_key_exists('reformatting',$attributes)) {
				$tmp = new $type($attributes['reformatting']);
				unset($attributes['reformatting']);
			}
			else
				$tmp = new $type();
			$tmp->addAttributes($attributes);
			$this->m_fields[$name] = $tmp; 
		}
		else {
			$this->logMessage('addElement', sprintf('Invalid class type [%s] passed, name [%s], attributes [%s]', $type, $name, print_r($attributes,1)), 1, true);
			$tmp = new tag();
		}
		return $tmp;
	}
	
	function deleteElement($name) {
		if ($this->fieldExists($name)) {
			unset($this->m_fields[$name]);
			if (array_key_exists($name,$this->m_data))
				unset($this->m_data[$name]);
		}
		else $this->logMessage('deleteElement',sprintf('deleting non-existant element [%s]',$name),2);
	}

	function addRequirement($name, $field, $type, $message, $required = true) {
		$this->logMessage('addRequirement',sprintf('name [%s] field [%s] type [%s] message [%s] required [%d]',
			$name, print_r($field,true), $type, $message, $required),4);
		$this->m_requirements[$name] = new Requirements($field, $type, $message, $required); 
	}

	function validate() {
		$errors = array();
		$messages = array();
		foreach($this->m_fields as $key=>$fld) {
			if (get_class($fld) == 'checkbox') {
				if (is_null($this->getData($fld->getAttribute('name')))) {
					if ($fld->getAttribute('value') == 1)
						$this->setData($fld->getAttribute('name'),0);
					else
						$this->setData($fld->getAttribute('name'),'off');
				}
			}
			if (get_class($fld) == 'datepicker') { 
				if (($data = $this->getData($fld->getAttribute('name'))) != null) {
					$tmp = date_parse($data);
					if ($tmp['error_count'] == 0)
						$this->setData($fld->getAttribute('name'),sprintf('%04d-%02d-%02d',$tmp['year'],$tmp['month'],$tmp['day']));
				}
			}
			if (get_class($fld) == 'datetimepicker') { 
				if (($data = $this->getData($fld->getAttribute('name'))) != null) {
					$tmp = date_parse($data);
					if ($tmp['error_count'] == 0)
						$this->setData($fld->getAttribute('name'),sprintf('%04d-%02d-%02d',$tmp['year'],$tmp['month'],$tmp['day']));
				}
			}
		}
		foreach($this->m_requirements as $dataKey=>$req) {
			$typ = $req->getType();
			$key = $this->getDataName($dataKey,false);	//usually the same, but for arrays is different
			$this->logMessage('validate',sprintf('getDataName [%s] key [%s] req [%s] type [%s]',$key,$dataKey,print_r($req,true),$typ),4);
			switch($typ) {
				case 'captcha':
					$valid = $this->hasData('g-recaptcha-response');
					if ($valid) $this->setData($key,$this->getData('g-recaptcha-response'));
					break;
				case 'time':
					$valid = $this->hasData($key.'_hh') && $this->hasData($key.'_mm');
					$tmp = array();
					$tmp[] = strlen($this->getData($key.'_hh')) > 0 ? $this->getData($key.'_hh') : '';
					$tmp[] = strlen($this->getData($key.'_mm')) > 0 ? $this->getData($key.'_mm') : '';
					$tmp[] = strlen($this->getData($key.'_ss')) > 0 ? $this->getData($key.'_ss') : '';
					$this->setData($key,sprintf('%02d:%02d:%02d',$tmp[0],$tmp[1],$tmp[2]));
					break;
				case 'fileupload':
					if (array_key_exists($key,$_FILES)) {
						$this->setData($key,$_FILES[$key]);
					}
					break;
				default:
					break;
			}
			if (strpos($typ,":") !== false) {
				$tmp = explode(":",$typ);
				if ($tmp[0] == "custom") {
					if (method_exists($GLOBALS['globals']->getConfig(),$tmp[1])) {
						$valid = $GLOBALS['globals']->getConfig()->{$tmp[1]}($this->getAllData(),$this,$dataKey,$req,$errors,$messages);
					}
					else $this->logMessage(__FUNCTION__,sprintf("missing custom validation [%s]",$tmp[1]),1,true);
				}
			}
			if ($req->isRequired()) {
				$tmp = $req->getField();
				if (get_class($tmp) == 'provinceSelect' && !$tmp->hasOptions()) {
					$this->logMessage(__FUNCTION__,sprintf('skipping province validation since no provinces exist [%s]',print_r($tmp,true)),2);
					continue;
				}
				if (!$this->hasData($key) || ($req->getAttribute('type') == 'checkbox' && $req->hasAttribute('mandatory') && $this->getData($key) == 0)) {
					$this->logMessage('validate',sprintf('missing data for field %s [%s]',$key,print_r($req,true)),5);
					$errors[$dataKey] = $req->getMessage();
					$messages[] = array('isRequired'=>$req->getPrettyName($this->getAllData()));
					continue;
				}
			}
			$values = $this->getData($key);
			if (!is_array($values)) $values = array($values);
			if ($req->getType() == 'fileupload') {
				if (array_key_exists('error',$values) && $values['error'] > 0 && $values['size'] > 0) {
					$errors[$key] = sprintf('File upload %s failed',$values['name']);
					$messages[] = array('fileupload'=>$req->getPrettyName($this->getAllData()));
				}
				$values = array();
			}
			foreach($values as $value) {
				$this->logMessage('validate',sprintf('key [%s] value [%s] req [%s]',print_r($key,true),print_r($value,true),print_r($req,true)),5);
				$typ = $req->getType();
				if ($value != null) {
					$tmp = explode(":",$typ);
					switch($tmp[0]) {
						case 'postalcode':
							$tmp = $this->getData("country_id");
							if ($tmp == 0) $tmp = $this->getData("address[country_id]");
							switch($tmp) {
								case 1:
									$pc = strtoupper($this->getData($key));
									$valid = preg_match("/^[ABCEGHJKLMNPRSTVXY]{1}\d{1}[A-Z]{1}\ {0,1}\d{1}[A-Z]{1}\d{1}$/",$pc,$matches) == 1;
									if ($valid) $this->setData($key,$pc);
									break;
								case 2:
									$pc = $this->getData($key);
									//$valid = preg_match("/\d{5}([\-]?\d{4}){0,1}$/",$pc,$matches) == 1;
									$valid = preg_match("/\A\d{5}([\-]?\d{4})?$/",$pc,$matches) == 1;
									break;
								default:
									$valid = true;
									break;
							}
							if (!$valid) {
								$errors[$dataKey] = $req->getMessage();
								$messages[] = array('postalcode'=>$req->getPrettyName($this->getAllData()));
							}
							break;
						case "invisibleCaptcha":
						case 'captcha':
							if ($valid = array_key_exists('g-recaptcha-response',$_REQUEST)) {
								//require_once(ADMIN.'classes/recaptchalib.php');
								$keys = $this->depairOptions($this->getConfigVar('captcha'));
								$s = new Snoopy();
								$s->host = $GLOBALS["recaptcha"]["url"];
								$s->port = 443;
								$s->httpmethod='GET';
								$vars = array(
									'secret'=>$keys["private"],
									'response'=>array_key_exists("g-recaptcha-response",$_REQUEST) ? $_REQUEST["g-recaptcha-response"] : "",
									'remoteip'=>$_SERVER["REMOTE_ADDR"]
								);
								//$s->curl_path = $GLOBALS['curl_path'];
								$s->submit($GLOBALS["recaptcha"]["url"],$vars);
								$r = json_decode($s->results,true);
								if (!(array_key_exists("success",$r) && $r["success"])) {
								  $this->logMessage(__FUNCTION__,sprintf('captcha response [%s] from [%s]',print_r($r,true),print_r($s,true)),1,true);
									$errors[$key] = $req->getMessage();
									$messages[] = array('captcha'=>$req->getPrettyName($this->getAllData()));
									$valid = false;
								}
							}
							break;
						case 'time':
							$hr = $this->getData($key.'_hh');
							$mn = $this->getData($key.'_mm');
							$sec = $this->getData($key.'_ss');
							$ampm = $this->getData($key.'_ampm');
							$valid = true;
							if ($req->isRequired()) {
								if (strpos($req->getAttribute('display'),'h') === false) $hr = '00';
								if (strpos($req->getAttribute('display'),'m') === false) $mn = '00';
								if (strpos($req->getAttribute('display'),'s') === false) $sec = '00';
								$valid = $hr != '' && $mn != '' && $sec != '';
							}
							else {
								if (strpos($req->getAttribute('display'),'h') === false) $hr = $mn == '' && $sec == '' ? '' : '00';
								if (strpos($req->getAttribute('display'),'m') === false) $mn = $hr == '' && $sec == '' ? '' : '00';
								if (strpos($req->getAttribute('display'),'s') === false) $sec = $hr == '' && $mn == '' ? '' : '00';
								$valid = ($hr == '' && $mn == '' && $sec == '') || ($hr != '' && $mn != '' && $sec != '');
							}
							if ($hr != "" && $hr >= 0 && $hr <= 23 && $mn != "" && $mn >= 0 && $mn <= 59) {
								if ($ampm == 'PM' && $hr < '12') {
									$hr += 12;
									$this->setData($key.'_hh',$hr);
								}
								$time = sprintf('%02d:%02d:%02d',$hr,$mn,$sec);
								$this->setData($key,$time);
							}
							else {
								$this->logMessage('validate',sprintf('setting %s to error [%s] [%s]',$key,$hr,$mn),4);
								if (!$valid) {
									$errors[$key] = $req->getMessage();
									$messages[] = array('time'=>$req->getPrettyName($this->getAllData()));
								}
								else
									$this->setData($key,'');
							}
							break;
						case 'datetime':
							//
							//	have to build up the real timestamp from the pieces
							//
							$parts = explode(' ',$value);
							if (count($parts) == 1) {
								//
								//	we haven't gathered the time part yet
								//
								$ampm = $this->getData($key.'_ampm');
								if (strlen($ampm) > 0) {
									$hr = $this->getData($key.'_hh');
									if ($ampm == 'PM') $hr = sprintf('%02d',$hr+12);
									$time = sprintf('%02d:%02d:%02d',$hr,$this->getData($key.'_mm'),$this->getData($key.'_ss'));
								}
								else {
									$time = sprintf('%02d:%02d:%02d',$this->getData($key.'_hh'),$this->getData($key.'_mm'),$this->getData($key.'_ss'));
								}
								$parts[1] = $time;
							}
							$parts[0] = date('Y-m-d',strtotime($parts[0]));	// date may have already been validated & converted from m/d/y to y-m-d
							if ($parts[0] == '1969-12-31' || !(preg_match('#\d{4}-\d{2}-\d{2}#',$parts[0]) && preg_match('#\d{2}:\d{2}:\d{2}#',$parts[1]))) {
								$errors[$key] = $req->getMessage();
								$messages[] = array('dateTime'=>$req->getPrettyName($this->getAllData()));
							}
							else $this->setData($key,implode(' ',$parts));
							break;
						case 'username':
							if (!(preg_match(GLOBAL_USERNAME_REGEX,$value))) {
								$errors[$key] = $req->getMessage();
								$messages[] = array('usernameComplexity'=>$req->getPrettyName($this->getAllData()));
							}
							break;
						case 'password':
							if (!(preg_match(GLOBAL_PASSWORD_REGEX,$value))) {
								$errors[$key] = $req->getMessage();
								$messages[] = array('passwordComplexity'=>$req->getPrettyName($this->getAllData()));
							}
							break;
						case 'date':
							if (!(preg_match('#\d{4}-\d{2}-\d{2}#',$value))) {
								$errors[$key] = $req->getMessage();
								$messages[] = array('date'=>$req->getPrettyName($this->getAllData()));
							}
							break;
						case 'email':
							if (!preg_match('/^\S+@[\w\d.-]{2,}\.[\w]{2,6}$/iU', $value)) {
								$errors[$key] = $req->getMessage();
								$messages[] = array('email'=>$req->getPrettyName($this->getAllData()));
							}
							break;
						case 'string':
							if (strlen($value) <= 0) {
								$errors[$key] = $req->getMessage();
								$messages[] = array('isRequired'=>$req->getPrettyName($this->getAllData()));
							}
							break;
						case 'number':
							if (!preg_match('/^-?\d{0,10}\.?\d*$/', $value)) {
								$errors[$key] = $req->getMessage();
								$messages[] = array('numeric'=>$req->getPrettyName($this->getAllData()));
							}
							break;
						case 'fileupload':
							$this->logMessage('validate',sprintf('validating fileupload value [%s]',print_r($value,true)),5);
							break;
						case "custom":
							break;
						default:
							if (substr($typ,0,1) == '/') {
								if (!preg_match($typ, $value)) {
									$errors[$key] = $req->getMessage();
									$messages[] = array('genericFail'=>$req->getPrettyName($this->getAllData()));
								}
							} else {
								$this->logMessage('validate', sprintf('Unknown validation type [%s]',$req->getType()), 1, true);
								$errors[$key] = $req->getMessage();
								$messages[] = array('unknownValidation'=>$req->getPrettyName($this->getAllData()).' ['.$req->getType().'] ');
						}
					}
				}
			}
		}
		foreach($errors as $key=>$value) {
			$this->logMessage('validate',sprintf('Error [%s] value [%s]',$key,$value),2);
			$this->addElement($key.'Error', 'errorTag', array('value'=>$value));
		}
		$this->setValidation($messages);
		foreach($messages as $key=>$value) {
			$this->logMessage('validate',sprintf('validation message [%s]',print_r($value,true)),3);
			foreach($value as $subkey=>$subvalue) {
				if (!($msg = $this->fetchScalar(sprintf('select value from code_lookups where type="validationError" and code = "%s"',$subkey))))
					$this->logMessage('validate',sprintf('Missing validation error message [%s]',$subkey),1,true);
				else
					$this->addFormError(sprintf('%s %s',$subvalue,$msg));
			}
		}
		$this->logMessage('validate',sprintf('post-validate [%s]',print_r($this,true)),4);
		return count($errors) == 0;
	}

	private function getRequirement($name) {
		if (array_key_exists($name, $this->m_requirements))
			return $this->m_requirements[$name];
	}

	function hasData($name) {
		$tmp = $this->getData($name);
		return (!(is_null($tmp)) && ((is_array($tmp) && count($tmp) > 0) || (!is_array($tmp) && strlen($tmp) > 0)));
		if (strpos($name, '[]') !== false) {
			//
			//	name is expected as arrayname[name][]
			//
			$arr = explode('[',$name);
			$tmp = str_replace(']', '', $arr[count($arr)-2]);
			$this->logMessage('hasData',sprintf('checking for array passed name [%s] tmp [%s]', $name, $tmp ),5);
			$name = $tmp;
		}
		if (array_key_exists($name,$this->m_data) && !is_null($this->m_data[$name])) {
			if (is_array($this->m_data[$name])) {
				if (count($this->m_data[$name]) > 0) 
					return true;
				else return false;
			}
			if (!is_null($this->m_data[$name]) && strlen($this->m_data[$name]) > 0) return true;
		}
		return false;
	}
	
	function setHTML($html) {
		$this->m_html = $html;
	}

	function getHTML() {
		return $this->m_html;
	}

	function getDataName($fldName,$strip_array = true) {
		$name = $this->getField($fldName)->getAttribute('name');
		$this->logMessage(__FUNCTION__,sprintf("fldName [%s] data name [%s]",$fldName,$name),5);
		if (strpos($name, '[]') !== false && $strip_array) {
			//
			//	name is expected as arrayname[name][]
			//
			$arr = explode('[',$name);
			$tmp = str_replace(']', '', $arr[count($arr)-2]);
			$this->logMessage(__FUNCTION__,sprintf('checking for array passed name [%s] tmp [%s]', $name, $tmp ),5);
			$name = $tmp;
		}
		return $name;
	}

	function hasTag($name) {
		$tmpHtml = $this->m_html;
		$delims = $this->getDelimiters();
		return preg_match(sprintf("/%s%s(.*)%s/",$delims[0],$name,$delims[1]),$tmpHtml);
	}

	function getDelimiters() {
		if ($this->hasOption('formDelimiter')) 
			$delims = explode('|',$this->getOption('formDelimiter'));
		else
			$delims = array(0=>'%%',1=>'%%');
		return $delims;
	}

	function getErrors() {
		return $this->m_error;
	}

	function getSuccess() {
		return $this->m_status;
	}

}

class Requirements extends Forms {
	
	private $m_field;
	private $m_validationType;
	private $m_errMessage;
	private $m_required;
	
	function __construct( $field, $validationType, $errMessage, $required =true ) {
		$this->m_field = $field;
		$this->m_validationType = $validationType;
		$this->m_errMessage = $errMessage;
		$this->m_required = $required;
	}

	function getField($name = null) {	// compatibility only
		return $this->m_field;
	}

	function __destruct() {
	}

	function hasValidation() {
		return $this->m_validationType != null && strlen($this->m_validationType) > 0;
	}

	function isRequired() {
		return $this->m_required;
	}

	function getMessage() {
		return $this->m_errMessage;
	}
	
	function getType() {
		return $this->m_validationType;
	}

	function getPrettyName($data) {
		if ($this->m_field->hasAttribute('prettyName')) {
			$tmp = new Forms();
			$tmp->addData($data);
			$tmp->setHTML($this->m_field->getAttribute('prettyName'));
			return $tmp->show();
		}
		else if ($this->m_field->hasAttribute('placeholder')) {
			return str_replace(":","",$this->m_field->getAttribute('placeholder'));
		}
		else {
			$tmp = str_replace("[]","",$this->m_field->getAttribute('name'));
			$tmp = ucwords(str_replace("_"," ",$tmp));
			return $tmp;
		}
	}

	function getAttribute($name) {
		return $this->getField()->getAttribute($name);
	}

	function hasAttribute($name) {
		return $this->getField()->hasAttribute($name);
	}
}
?>