<?php

/**
 * Calendar feature in the frontend section
 */
class calendar extends Frontend {

    /**
     * @param $id
     * @param $module
     * @throws phpmailerException
     */
    public function __construct($id, $module = array()) {
		parent::__construct();
		$this->m_dir = ADMIN.'frontend/forms/calendar/';
		$this->m_moduleId = $id;
		$module['m_dir'] = $this->m_dir;
		$this->m_module = $module;
		$this->m_eventId = array_key_exists('event_id',$_REQUEST) ? $_REQUEST['event_id'] : 0;
		$this->logMessage("__construct",sprintf("($id,[%s])",print_r($module,true)),2);
	}

    /**
     * @return false|mixed|string
     * @throws phpmailerException
     */
    public function setDates() {
		if ($this->hasOption('ignoreDates')) {
			$mth = date('m');
			$day = date('d');
			$yr = date('Y');
		}
		else {
			if (array_key_exists('month',$_REQUEST)) 
				$mth = $_REQUEST['month'];
			else $mth = date('m');
			if (array_key_exists('year',$_REQUEST)) 
				$yr = $_REQUEST['year'];
			else $yr = date('Y');
			if (array_key_exists('day',$_REQUEST)) 
				$day = $_REQUEST['day'];
			else {
				//if (array_key_exists('year',$_REQUEST) || array_key_exists('month',$_REQUEST))
					$day = '01';
				//else
				//	$day = date('d');
			}
		}
		if (array_key_exists('sd',$this->m_module)) {
			$sd = $this->m_module['sd'];
		}
		else $sd = sprintf('%04d-%02d-%02d',$yr,$mth,$day);
		if ($this->hasOption('relativeDate')) {
			$tmp = explode('|',$this->getOption('relativeDate'));
			$adjust = '';
			if ($tmp[0] != 0) $adjust = sprintf(' + %d years',$tmp[0]);
			if ($tmp[1] != 0) $adjust = sprintf(' + %d months',$tmp[1]);
			if ($tmp[2] != 0) $adjust = sprintf(' + %d days',$tmp[2]);
			$this->logMessage(__FUNCTION__,sprintf('adjusting sd [%s] by [%s]',$sd,$adjust),2);
			$sd = date('Y-m-d',strtotime(sprintf('%s %s',$sd,$adjust)));
		}
		if ($this->hasOption('limitDates')) {
			$limits = $this->getOption('limitDates');
			$limit = explode('|',$limits);
			$cd = date('Y-m').'-01';
			if ($sd < date('Y-m-d',strtotime(sprintf('%s - %d months',$cd,$limit[0])))) {
				$this->logMessage(__FUNCTION__,sprintf('limiting start date [%s] limits [%s]',$sd,$limits),2);
				$sd = date('Y-m',strtotime(sprintf('%s - %d months',$cd,$limit[0]))).'-01';
			}
			if ($sd >= date('Y-m-d',strtotime(sprintf('%s + %d months',$cd,$limit[1]-1)))) {
				$this->logMessage(__FUNCTION__,sprintf('limiting end date [%s] limits [%s]',$sd,$limits),2);
				$sd = date('Y-m',strtotime(sprintf('%s + %d months',$cd,$limit[1]-1))).'-01';
			}
		}
		$this->logMessage(__FUNCTION__,sprintf('sd [%s]',$sd),2);
		return $sd;
	}

    /**
     * @param $module
     * @param $addLimit
     * @param $sd
     * @param $ed
     * @return string
     * @throws phpmailerException
     */
    public function buildSql($module, $addLimit = true, $sd = null, $ed = null) {
		if ($sd == null && array_key_exists('sd',$this->m_module))
			$sd = $this->m_module['sd'];
		if ($ed == null && array_key_exists('ed',$this->m_module))
			$ed = $this->m_module['ed'];
		if ($module['folder_id'] > 0) {
			if ($module['include_subfolders'] != 0) {
				$sql = sprintf('select e.id from members_folders e, members_folders e1 where e1.id = %d and e.left_id >= e1.left_id and e.right_id <= e1.right_id and e.enabled = 1',$module['folder_id']);
				if (!($tmp = $this->fetchScalarAllTest($sql))) $tmp = array("id"=>0);
				$this->logMessage(__FUNCTION__,sprintf('sub folder sql [%s]',$sql),3);
				$sql = sprintf("select e.* from events e, events_by_folder j, members_folders f, event_dates ed where f.id = j.folder_id and e.deleted = 0 and e.enabled = 1 and e.published = 1 and j.folder_id in (%s) and e.id = j.event_id",implode(',',$tmp));
			}
			else {
				if ($this->hasOption('showAll'))
					$sql = sprintf("select e.* from events e, events_by_folder j, event_dates ed where e.deleted = 0 and j.folder_id = %d and e.id = j.event_id",$module['folder_id']);
				else
					$sql = sprintf("select e.* from events e, events_by_folder j, event_dates ed where e.deleted = 0 and e.enabled = 1 and e.published = 1 and j.folder_id = %d and e.id = j.event_id",$module['folder_id']);
			}
		}
		else
			if ($this->hasOption('showAll'))
				$sql = "select e.* from events e, event_dates ed where e.deleted = 0 ";
			else
				$sql = "select e.* from events e, event_dates ed where e.deleted = 0 and e.enabled = 1 and e.published = 1";
		if (array_key_exists('search_group',$module) && $module['search_group'] > 0) {
			$sql .= sprintf(" and e.id in (select event_id from event_by_search_group where folder_id = %d)",$module['search_group']);
		}
		if (array_key_exists('event_list',$module) && count($module['event_list']) > 0) {
				$sql .= sprintf(" and e.id in (%s) ",implode(",",$module['event_list']));
		}
		if (strlen($module['where_clause']) > 0)
			$sql .= " and ".$module['where_clause'];
		if ($module['featured'])
			$sql .= " and featured = 1";
		if (!is_null($sd)) 
			$tmp = sprintf("ed.event_date >= '%s'",$sd);
		else
			$tmp = "ed.event_date >= curdate()";
		if (!is_null($ed)) $tmp .= sprintf(" and ed.event_date <= '%s'",$ed);
		$sql .= sprintf(" and ed.event_id = e.id and %s ",$tmp);
		if (strlen($module['sort_by']) > 0)
			$sql .= " order by ".$module['sort_by'];
		else
			$sql .= " group by e.id order by min(ed.event_date), start_time ";
		if ($addLimit && strlen($module['records']) > 0) {
			$tmp = explode(',',$module['records']);
			if (count($tmp) > 1)
				$total = $tmp[0]*$tmp[1];
			else
				$total = $tmp[0];
			if ($total > 0)
				$sql .= " limit ".$total;
		}
		$this->logMessage(__FUNCTION__,sprintf('sql [%s]',$sql),3);
		return $sql;
	}

    /**
     * @param $data
     * @return mixed
     * @throws phpmailerException
     */
    public function formatData($data) {
		if (!array_key_exists('address_id',$data)) {
			if ($this->hasOption('addressType'))
				$addressType = $this->getOption('addressType');
			else $addressType = $this->fetchScalarTest('select id from code_lookups where type = "storeAddressTypes" and code = "mailingaddress"');
			$this->logMessage(__FUNCTION__,sprintf('addrType = '.$addressType),1);
			if ($address = $this->fetchSingleTest('select a.*, p.province_code as provinceCode, p.province, c.country_code as countryCode, c.country as countryName from addresses a left join provinces p on p.id = a.province_id left join countries c on c.id = a.country_id where a.ownertype="store" and a.ownerid = %d and a.addresstype = %d',$data['id'],$addressType)) {
				$address['address_id'] = $address['id'];
				$address['formattedAddress'] = $address['line1'];
				if (strlen($address['line2']) > 0) $address['formattedAddress'] .= '<br/>'.$address['line2'];
				if (strlen($address['city']) > 0 || strlen($address['provinceCode']) > 0 || $address['postalcode']) {
					$address['formattedAddress'] .= '<br/>'.$address['city'].' '.$address['postalcode'].' '.$address['province'];
				}
				unset($address['id']);
				$data = array_merge($data,$address);
			}
		}
		for ($x = 1; $x < 5; $x++) {
			if (strlen($data['image'.$x]) > 0) {
				$tmp = new image();
				$tmp->addAttributes(array('src'=>$data['image'.$x],'alt'=>htmlentities(strip_tags($data['name']))));
				$data['img_image'.$x] = $tmp->show();
			}
		}
		if ($data['recurring_event'] != 0) {
			$td = $this->fetchScalarTest('select min(event_date) from event_dates where event_id = %d and event_date >= curdate()',$data['id']);
			if ($td != "") $data ['start_date'] = $td;
			$td = $this->fetchScalarTest('select max(event_date) from event_dates where event_id = %d',$data['id']);
			if ($td != "") $data['end_date'] = $td;
		}
		$data['start_date_month_short'] = date('M',strtotime($data['start_date']));
		$data['start_date_month_long'] = date('F',strtotime($data['start_date']));
		$data['start_date_day_0d'] = date('d',strtotime($data['start_date']));
		$data['start_date_day_d'] = date('j',strtotime($data['start_date']));
		$data['start_date_dow'] = date('l',strtotime($data['start_date']));
		if ($data['end_date'] == '1969-12-31') $data['end_date'] = '0000-00-00';
		if ($data['end_date'] != '0000-00-00' and $data['end_date'] != $data['start_date']) {
			$sd = date_parse($data['start_date']);
			$ed = date_parse($data['end_date']);
			if ($sd['year'] != $ed['year']) {
				$data['start_date_range'] = sprintf('%s - %s',date('d M, Y',strtotime($data['start_date'])),date('d M, Y',strtotime($data['end_date'])));
			}
			elseif ($sd['month'] != $ed['month']) {
				$data['start_date_range'] = sprintf('%s-%s %s',date('d M',strtotime($data['start_date'])), date('d M',strtotime($data['end_date'])), $ed['year']);
			}
			else {
				$data['start_date_range'] = sprintf('%s-%s %s',$sd['day'],$ed['day'],date('M, Y',strtotime($data['end_date'])));
			}
		}
		else $data['start_date_range'] = date('d M, Y',strtotime($data['start_date']));
		if ($this->hasOption('dateFormat')) {
			$data['created_fmt'] = date($this->getOption('dateFormat'),strtotime($data['created']));
			$data['start_date_fmt'] = date($this->getOption('dateFormat'),strtotime($data['start_date']));
			$data['end_date_fmt'] = date($this->getOption('dateFormat'),strtotime($data['end_date']));
		}
		$data['url'] = $this->getUrl('event',$data['id'],$data);
		$data['href'] = sprintf('<a href="%s">',$data['url']);
		$data['href_end'] = '</a>';
		if ($data['start_time'] == '') 
			$data['start_time'] = '';
		else {
			$data['start_time'] = date("h:i A", strtotime($data['start_time']));
		}
		if ($data['end_time'] == '') 
			$data['end_time'] = '';
		else {
			$data['end_time'] = date("h:i A", strtotime($data['end_time']));
		}
		$data['formattedTime'] = sprintf('%s %s %s',$data['start_time'],strlen($data['end_time']) > 0 ? '-':'',$data['end_time']);
		if (strlen($data['start_time']) > 0 && strlen($data['end_time']) > 0) $data['timeConnector'] = '-';
		if ($address = $this->fetchSingleTest('select * from addresses where ownertype="event" and ownerid = %d and deleted = 0',$data['id'])) {
			$data['address'] = Address::formatAddress($address['id']);
			$data['mapAddress'] = Address::formatAddress($address['id'],'map');
			$data['encodedAddress'] = htmlentities($data['mapAddress']);
			$data['viewMap'] = sprintf('<a title="View Map" onclick="map_popup(\'http://maps.google.ca/maps?f=q&amp;q=%s\')" href="#">VIEW MAP</a>',urlencode($data['mapAddress']));
			if (strlen($address['email']) > 0) {
				$data['contact'] = sprintf('<a title="Contact" href="mailto:%s">CONTACT</a>',$address['email']);
			}
			$data['addressName'] = $address['addressname'];
			$data['phone1'] = $address['phone1'];
			$data['email'] = $address['email'];
		}
		$addresses = $this->fetchAllTest('select a.*,c.code as addressType from addresses a, code_lookups c where c.id = a.addresstype and a.ownertype="event" and a.ownerid = %d and a.deleted = 0',$data['id']);
		foreach($addresses as $key=>$value) {
			$data['addresses'][$value['addressType']] = Address::formatData($value);
		}
		if (strlen($data['website']) > 0)
			$data['websiteLink'] = sprintf('<a title="Visit Website (External Site)" target="_blank" href="%s">Visit Website</a>',$data['website']);
		if ($data['start_date'] < date('Y-m-d')) {
			$data['relative'] = 'past';
		}
		if ($data['start_date'] >= date('Y-m-d')) {
			$data['relative'] = 'future';
		}
		$this->logMessage(__FUNCTION__,sprintf('return data [%s]',print_r($data,true)),4);
		return $data;
	}

    /**
     * @param $data
     * @return mixed
     * @throws phpmailerException
     */
    public function formatFolder($data) {
		if (array_key_exists("image",$data) && strlen($data['image']) > 0) {
			$tmp = new image();
			$tmp->addAttributes(array('src'=>$data['image'],'alt'=>htmlentities(strip_tags($data['title']))));
			$data['img_image'] = $tmp->show();
		}
		if (array_key_exists("rollover_image",$data) && strlen($data['rollover_image']) > 0) {
			$tmp = new image();
			$tmp->addAttributes(array('src'=>$data['rollover_image'],'alt'=>htmlentities(strip_tags($data['title']))));
			$data['img_rollover_image'] = $tmp->show();
		}
		$data["url"] = $this->getUrl('calendar',$data['id'],$data);
		$this->logMessage(__FUNCTION__,sprintf('return data [%s]',print_r($data,true)),3);
		return $data;
	}

    /**
     * @param $data
     * @return mixed
     * @throws phpmailerException
     */
    function formatiCal($data) {
		$data = $this->formatData($data);
		$sd = $data['start_date'];
		$st = $data['start_time'];
		$ed = $data['end_date'];
		$et = $data['end_time'];
		if ($ed == '0000-00-00') $ed = $sd;
		if ($et == '00:00:00') $et = $st;
		$zulu = new DateTimeZone('UTC');
		$local = new DateTimeZone(TZ);
		$dt = new DateTime();
		$dt->setTimeZone($zulu);
		$data['now_tz'] = $dt->format('Ymd\THis\Z');
		$dt->setTimeZone($local);
		$dt = DateTime::createFromFormat('Y-m-d h:i A',$sd.' '.$st,$local);
		$dt->setTimeZone($zulu);
		$data['start_date_tz'] = $dt->format('Ymd\THis\Z');
		$dt->setTimeZone($local);
		$dt = DateTime::createFromFormat('Y-m-d h:i A',$ed.' '.$et,$local);
		$dt->setTimeZone($zulu);
		$data['end_date_tz'] = $dt->format('Ymd\THis\Z');
		if (array_key_exists('addressName',$data)) {
			$data['LOCATION'] = 'LOCATION:'.$data['addressName'];
		}
		if (array_key_exists('mapAddress',$data)) {
			array_key_exists('LOCATION',$data) ? $data['LOCATION'] .= ', ' : $data['LOCATION'] = 'LOCATION:';
			if (array_key_exists('mapAddress',$data)) {
				$data['LOCATION'] .= sprintf("%s",$data['mapAddress']);
			}
		}
		if (array_key_exists('LOCATION',$data)) {
			$data['LOCATION'] = $this->icsFormat($data['LOCATION']);
		}
		$data['uid'] = md5(uniqid(random_int(1, 1000000), true)) . "@".HOSTNAME;
		$tmp = strip_tags(str_replace("</p>",'\\n',$data['description']));
		$tmp = str_replace("\r\n","",$tmp);
		$tmp = str_replace("\n","",$tmp);
		$data['DESCRIPTION'] = $this->icsFormat(sprintf('DESCRIPTION:%s',$tmp));
		$this->logMessage(__FUNCTION__,sprintf('return data [%s]',print_r($data,true)),4);
		return $data;
	}

    /**
     * @param $str
     * @return string
     */
    function icsFormat($str) {
		$str = str_replace(",","\\,",$str);
		$tmp = '';
		while(strlen($str) > 75) {
			$tmp .= substr($str,0,75)."\r\n ";
			$str = substr($str,75);
		}
		$tmp .= $str;
		return $tmp;
	}

    /**
     * @return array|mixed|string|string[]
     * @throws phpmailerException
     */
    function events() {
		if (!$module = parent::getModule())
			return "";
		$this->parseOptions($module['misc_options']);
		$this->logMessage(__FUNCTION__,sprintf("module [%s]",print_r($module,true)),2);
		$sd = $this->setDates();
		if ($this->hasOption('numberOfDays')) {
			$tmp = sprintf('%s + %d days',$sd,$this->getOption('numberOfDays')-1);
			$this->logMessage(__FUNCTION__,sprintf('trying to set date [%s] sd [%s] # [%s] result [%s]',$tmp,$sd,$this->getOption('numberOfDays'),date($tmp)),1);
			$ed = date('Y-m-d',strtotime(sprintf('%s + %d days',$sd,$this->getOption('numberOfDays')-1)));
		}
		else
			$ed = null;
		$sql = $this->buildSql($module,true,$sd,$ed);
		$events = $this->fetchAllTest($sql);
		$this->logMessage(__FUNCTION__,sprintf('sql [%s] count [%d]',$sql,count($events)),2);
		$return = array();
		$frm = new Forms();
		$frm->init($this->m_dir.$module['inner_html']);
		$sequence = 0;
		$fldr = 0;
		$outer = new Forms();
		$outer->init($this->m_dir.$module['outer_html']);
		if ($this->m_module['folder_id'] != 0) {
			$fldr = $this->fetchSingleTest('select * from members_folders where id = %d',$this->m_module['folder_id']);
			$outer->addData($this->formatFolder($fldr));
		}
		$outer->setModule($this->m_module);
		foreach($events as $key=>$event) {
			if (count($fldr) > 0) $event["folder_id"] = $fldr["id"];
			$sequence += 1;
			$frm->addData($this->formatData($event));
			$frm->addTag('sequence',$sequence);
			$subdata = $this->subForms($this->m_module['fetemplate_id'],'',array('folder_id'=>$this->m_module['folder_id'],'sd'=>$sd,'ed'=>$sd,'e_id'=>$event['id']),'inner');
			$this->logMessage(__FUNCTION__,sprintf('inner subforms [%s]',print_r($subdata,true)),3);
			foreach($subdata as $key=>$value) {
				$frm->addTag($key,$value,false);
			}
			$return[] = $frm->show();
		}
		if ($this->m_module['folder_id'] != 0) {
			$fldr = $this->fetchSingleTest('select * from members_folders where id = %d',$this->m_module['folder_id']);
			$outer->addData($this->formatFolder($fldr));
		}
		$outer->addTag('events',implode('',$return),false);
		$outer->addTag('month_short',date('M',strtotime($sd)));
		$outer->addTag('month',date('m',strtotime($sd)));
		$outer->addTag('day_0d',date('d',strtotime($sd)));
		$outer->addTag('day',date('j',strtotime($sd)));
		$outer->addTag('year',date('Y',strtotime($sd)));
		$outer->addTag('month_long',date('F',strtotime($sd)));
		$outer->addTag("count",count($return));
		$tmp = $outer->show();
		$this->logMessage(__FUNCTION__,sprintf('return [%s]',$tmp),2);
		return $tmp;
	}

    /**
     * @return array|mixed|string|string[]
     * @throws phpmailerException
     */
    function collapsible() {
		if (!$module = parent::getModule())
			return "";
		$this->parseOptions($module['misc_options']);
		$this->logMessage(__FUNCTION__,sprintf("module [%s]",print_r($module,true)),2);
		$year_ct = $module['parm1'];
		$toggler = $module['parm2'];
		if ($this->m_module['folder_id'] != 0) {
			$folderList = sprintf('and d.event_id in (select f.event_id from events_by_folder f where f.folder_id = %d)',$this->m_module['folder_id']);
		}
		else $folderList = '';
		$sql = sprintf('
select tmp.year,count(tmp.id) as ct from (
	select distinct date_format(event_date,"%%Y") as year,e.id
	from event_dates d, events e
	where d.event_date >= curdate() and d.event_date < str_to_date("1 Jan %d","%%d %%M %%y")
	and e.id = d.event_id
	and e.enabled = true
	and e.published = true
	%s) as tmp
group by year order by year',date('Y')+$year_ct,$folderList);
		$years = $this->fetchAllTest($sql);
		$this->logMessage(__FUNCTION__,sprintf('years sql [%s] data [%s]',$sql,print_r($years,true)),3);
		$list = array();
		$frm = new Forms();
		$frm->init($this->m_dir.$module['inner_html']);
		foreach($years as $key=>$year) {
			$sql = sprintf('
select tmp.m_num,tmp.mth,count(tmp.id) as ct from (
	select distinct date_format(event_date,"%%m") as m_num, date_format(event_date,"%%b") as mth,e.id
	from event_dates d, events e
	where date_format(event_date,"%%Y") = %d
	and d.event_date >= curdate()
	and e.id = d.event_id
	and e.enabled = true
	and e.published = true
	%s) as tmp
group by m_num order by m_num',$year['year'],$folderList);
			$months = $this->fetchAllTest($sql);
			$this->logMessage(__FUNCTION__,sprintf('month sql [%s] count [%d]',$sql,count($months)),2);
			$mthList = array();
			foreach($months as $key=>$month) {
				$sql = sprintf('
select distinct e.*
from event_dates d, events e
where date_format(event_date,"%%Y-%%m") = "%s-%s"
and d.event_date >= curdate()
and e.id = d.event_id
and e.enabled = true
and e.published = true
%s
order by d.event_date', $year['year'], $month['m_num'], $folderList);
				$records = $this->fetchAllTest($sql);
				$this->logMessage(__FUNCTION__,sprintf('month sql [%s] count [%d]',$sql,count($records)),2);

				$events = array();
				foreach($records as $rec) {
					$frm->addData($this->formatData($rec));
					$events[] = $frm->show();
				}
				$mthList[] = sprintf('<li class="month collapsed">%s<span class="month">%s (%d)</span><span class="value">%d</span> <ul class="events">%s</ul></li>',$toggler,$month['mth'],$month['ct'],$month['m_num'],implode('',$events));
			}

			$list[] = sprintf('<li class="year collapsed">%s<span class="year">%d (%d)</span><span class="value">%d</span><ul class="year">%s</ul></li>',$toggler,$year['year'],$year['ct'],$year['year'],implode('',$mthList));
		}
		$outer = new Forms();
		if ($this->m_module['folder_id'] != 0) {
			$fldr = $this->fetchSingleTest('select * from members_folders where id = %d',$this->m_module['folder_id']);
			$outer->addData($this->formatFolder($fldr));
		}
		$outer->init($this->m_dir.$module['outer_html']);
		$outer->addTag('listing','<ul class="listing">'.implode('',$list).'</ul>',false);
		$tmp = $outer->show();
		$this->logMessage(__FUNCTION__,sprintf("return [%s]",$tmp),3);
		return $tmp;
	}

    /**
     * @return array|mixed|string|string[]
     * @throws phpmailerException
     */
    function monthView() {
		if (!$module = parent::getModule())
			return "";
		$this->parseOptions($module['misc_options']);
		if (array_key_exists('month',$_REQUEST)) 
			$mth = $_REQUEST['month'];
		else $mth = date('m');
		if (array_key_exists('day',$_REQUEST)) 
			$day = $_REQUEST['day'];
		else $day = date('d');
		if (array_key_exists('year',$_REQUEST)) 
			$yr = $_REQUEST['year'];
		else $yr = date('Y');
		$sd = sprintf('%04d-%02d-01',$yr,$mth);
		$sd = $this->setDates($sd);
		$ed = date('Y-m-d',strtotime($sd." + 1 month"));
		$tmp = explode("-",$sd);
		$yr = $tmp[0];
		$mth = $tmp[1];
		$day = $tmp[2];
		$module['sd'] = $sd;
		$module['ed'] = $ed;
		$prevMonth = $mth-1;
		if ($prevMonth <= 0) {
			$prevMonth = 12;
			$prevYear = $yr-1;
		}
		else $prevYear = $yr;
		$nextMonth = $mth+1;
		if ($nextMonth > 12 ) {
			$nextMonth = 1;
			$nextYear = $yr+1;
		}
		else $nextYear = $yr;
		if ($this->hasOption('limitDates')) {
			$limits = $this->getOption('limitDates');
			$limit = explode('|',$limits);
			$cd = date('Y-m').'-01';
			if ($sd < date('Y-m-d',strtotime(sprintf('%s - %d months',$cd,$limit[0])))) {
				$this->logMessage(__FUNCTION__,sprintf('limiting start date [%s] limits [%s]',$sd,$limits),2);
				$sd = date('Y-m',strtotime(sprintf('%s - %d months',$cd,$limit[0]))).'-01';
				$ed = date('Y-m-d',strtotime($sd." + 1 month"));
				$yr = date('Y',strtotime($sd));
				$mth = date('m',strtotime($sd));
				$prevYear = $yr;
				$prevMonth = $mth;
			}
		}
		$this->logMessage(__FUNCTION__,sprintf("sd[%s] ed [%s]",$sd,$ed),2);
		$outer = new Forms();
		$sd_time = strtotime($sd);
		$outer->init($this->m_dir.$module['outer_html']);
		$outer->addTag('month',strftime('%m',$sd_time));
		$outer->addTag('fullMonth',strftime('%B',$sd_time));
		$outer->addTag('year',strftime('%Y',$sd_time));
		$outer->addTag('folder_id',$module['folder_id']);
		$outer->addTag('prevMonth',$prevMonth);
		$outer->addTag('prevYear',$prevYear);
		$outer->addTag('nextMonth',$nextMonth);
		$outer->addTag('nextYear',$nextYear);
		$outer->setModule($this->m_module);
		//
		//	inner = week view, day view = options:daily, day content = subtemplates
		//
		$return = array();
		$weekly = new Forms();
		$daily = new Forms();
		$weekly->init($this->m_dir.$module['inner_html']);
		if (strlen($module['parm1']) > 0) {
			$daily->init($this->m_dir.$module['parm1']);
		}
		else {
			$this->logMessage(__FUNCTION__,sprintf('daily view not in config [%s]',print_r($module,true)),1,true);
			return "";
		}
		$wk = date('w',strtotime($sd));
		$week = array();
		$sanity = 0;	// failsafe for infinite loop
		for ($i = 0; $i < $wk; $i++) {
			//
			//	build empty days prior to the start of the month
			//
			$daily->addTag('noday','noday');
			$week[] = $daily->show();
		}
		$daily->addTag('noday','');
		$wk = strftime('%U',strtotime($sd));
		$weeks = array();
		$eventList = array();
		while ($sd < $ed  && $sanity < 35) {
			$daily = new Forms();
			$daily->init($this->m_dir.$module['parm1']);
			$sd_time = strtotime($sd);
			$currWk = strftime('%U',strtotime($sd));
			if ($currWk != $wk) {
				$weekly->addTag('days',implode('',$week),false);
				$weeks[] = $weekly->show();
				$week = array();
			}
			$events = $this->fetchAllTest($this->buildSql($module,true,$sd,$sd));
			$this->logMessage(__FUNCTION__,sprintf('events [%d] sd[%s] ed[%s]',count($events),$sd,$sd),2);
			if (count($events) > 0) {
				foreach($events as $key=>$event) {
					$eventList[$event['id']] = $event['id'];
				}
				$subdata = $this->subForms($this->m_module['fetemplate_id'],'',array('folder_id'=>$this->m_module['folder_id'],'sd'=>$sd,'ed'=>$sd),'inner');
				$this->logMessage(__FUNCTION__,sprintf('inner subforms [%s]',print_r($subdata,true)),3);
				foreach($subdata as $key=>$value) {
					$daily->addTag($key,$value,false);
				}
				$daily->addTag('eventday','eventday');
				$tmp = sprintf('%s: %d event%s',strftime('%b %e, %Y',$sd_time),count($events),count($events) > 1 ? 's':'');
				if ($this->hasOption('onclick')) {
					$dayTmp = new Forms();
					$dayTmp->init($this->m_dir.$this->getOption('onclick'));
					$dayTmp->setOption('formDelimiter','{{|}}');
					$dayTmp->addTag('day',date('d',$sd_time));
					$dayTmp->addTag('month',date('m',$sd_time));
					$dayTmp->addTag('year',date('Y',$sd_time));
					$dayTmp->addTag('ct',count($events));
					$click=sprintf('onclick="%s"',$dayTmp->show());
				}
				else
					$click='';
				if ($this->hasOption('dayUrl')) {
					$dayTmp = new Forms();
					$dayTmp->init($this->m_dir.$this->getOption('dayUrl'));
					$dayTmp->setOption('formDelimiter','{{|}}');
					$dayTmp->addTag('day',date('d',$sd_time));
					$dayTmp->addTag('month',date('m',$sd_time));
					$dayTmp->addTag('year',date('Y',$sd_time));
					$daily->addTag('day',sprintf('<a class="caltips" href="%s" title="%s" %s>%s</a>',$dayTmp->show(),$tmp,$click,strftime('%e',$sd_time)),false);
				}
				else
					$daily->addTag('day',sprintf('<a class="caltips" href="%s" title="%s" %s>%s</a>',$this->getUrl('eventday',0,array('date'=>$sd)),$tmp,$click,strftime('%e',$sd_time)),false);
				$daily->addTag('eventCount',count($events));
				$daily->addTag('eventPlural',count($events) > 1 ? 's':'');
			}
			else {
				$daily->addTag('day',strftime('%e',$sd_time));
			}
			$daily->addTag('today',$sd == date('Y-m-d')?'today':'');
			$week[] = $daily->show();
			$sd = date('Y-m-d',strtotime(sprintf('%s + 1 day',$sd)));
			$sanity += 1;
			$this->logMessage(__FUNCTION__,sprintf('sd [%s] week [%s] wk[%s] currWk [%s]',$sd,print_r($week,true),$wk,$currWk),4);
			$wk = $currWk;
		}
		if ($sanity >= 35) {
			$this->logMessage(__FUNCTION__,sprintf('oops - something happened in the month loop sd [%s] ed [%s] weeks [%s]',$sd,$ed,print_r($weeks,true)),1,true);
		}
		$weekly->addTag('days',implode('',$week),false);
		$weeks[] = $weekly->show();
		if ($this->m_module['folder_id'] != 0) {
			$fldr = $this->fetchSingleTest('select * from members_folders where id = %d',$this->m_module['folder_id']);
			$outer->addData($this->formatFolder($fldr));
		}
		$outer->addTag('weeks',implode('',$weeks),false);
		$subdata = $this->subForms($this->m_module['fetemplate_id'],'',array('folder_id'=>$this->m_module['folder_id'],'eventList'=>$eventList,'sd'=>$module['sd'],'ed'=>$module['ed']),'outer');
		$this->logMessage(__FUNCTION__,sprintf('outer subforms [%s]',print_r($subdata,true)),3);
		foreach($subdata as $key=>$value) {
			$outer->addTag($key,$value,false);
		}
		$tmp = $outer->show();
		$this->logMessage(__FUNCTION__,sprintf('return [%s] weeks [%s]',$tmp,print_r($weeks,true)),2);
		return $tmp;
	}

    /**
     * @return array|mixed|string|string[]
     * @throws phpmailerException
     */
    function upcomingEvents() {
		if (!$this->m_module = parent::getModule())
			return "";
		$this->parseOptions($this->m_module['misc_options']);
		$sd = $this->setDates();
		if ($this->hasOption('monthCount')) {
			$ed = date('Y-m-d',strtotime(sprintf($sd." + %d month - 1 day",$this->getOption('monthCount'))));
		}
		else {
			$ed = date('Y-m-d',strtotime($sd." + 1 month - 1 day"));
		}
		$this->logMessage(__FUNCTION__,sprintf("sd[%s] ed [%s]",$sd,$ed),2);
		$outer = new Forms();
		$outer->setModule($this->m_module);
		$flds = $this->config->getFields($this->m_module['configuration']);
		$sd_time = strtotime($sd);
		$outer->init($this->m_dir.$this->m_module['outer_html']);
		$flds = $outer->buildForm($flds);
		$outer->addTag('month',strftime('%b',$sd_time));
		$outer->addTag('monthNumeric',strftime('%m',$sd_time));
		$outer->addTag('fullMonth',strftime('%B',$sd_time));
		$outer->addTag('year',strftime('%Y',$sd_time));
		$outer->addTag('folder_id',$this->m_module['folder_id']);
		if ($fldr = $this->fetchSingleTest('select * from members_folders where id = %d',$this->m_module['folder_id'])) {
			$outer->addData($this->formatFolder($fldr));
		}
		if (count($_POST) > 0 && array_key_exists('upcomingEvents',$_POST)) {
			$outer->addData($_POST);
			if ($outer->validate()) {
				if (array_key_exists('event_type',$_POST) && count($_POST['event_type']) > 0) {
					if (is_array($_POST['event_type']) && count($_POST['event_type']) > 0) {
						if (strlen($this->m_module['where_clause']) > 0)
							$this->m_module['where_clause'] .= ' and ';
						$this->m_module['where_clause'] .= sprintf('e.id in (select event_id from event_types where event_type in (%s))',is_array($_POST['event_type']) ? implode(',',$_POST['event_type']) : $_POST['event_type']);
					}
					else {
						if (!is_array($_POST['event_type']) && $_POST['event_type'] > 0) {
							if (strlen($this->m_module['where_clause']) > 0)
								$this->m_module['where_clause'] .= ' and ';
							$this->m_module['where_clause'] .= sprintf('e.id in (select event_id from event_types where event_type = %d)', $_POST['event_type']);
						}
					}
				}
			}
		}
		$sql = $this->buildSql($this->m_module,true,$sd,$ed);
		if (!$this->hasOption('byMonth')) {
			$sql = $this->buildSql($this->m_module,false,$sd,$ed);
			$pagination = $this->getPagination($sql,$this->m_module,$recordcount);
			$outer->addTag('pagination',$pagination,false);
			$events = $this->fetchAllTest($sql);
			$this->logMessage(__FUNCTION__,sprintf('sql [%s] count[%d]',$sql,count($events)),2);
			$evt = array();
			$sequence = 0;
			$inner = new Forms();
			$inner->init($this->m_dir.$this->m_module['inner_html']);
			$mth = 0;
			$ct = 0;
			if ($this->hasOption('grpPrefix')) $evt[] = $this->getOption('grpPrefix');
			foreach($events as $key=>$event) {
				$ct += 1;
				if ($this->m_module['rows'] > 0 && $ct > $this->m_module['columns']) {
					$ct = 1;
					if ($this->hasOption('grpSuffix')) $evt[] = $this->getOption('grpSuffix');
					if ($this->hasOption('grpPrefix')) 
						$evt[] = $this->getOption('grpPrefix');
					else
						$evt[] = '<div class="clearfix"></div>';
				}
				$inner->reset();
				if ($sequence == 0) $inner->addTag("first","first");
				if ($sequence == (count($events) - 1)) $inner->addTag("last","last");
				$inner->addTag('folder_id',$this->m_module['folder_id']);
				$sequence += 1;
				if (is_array($fldr) && array_key_exists("id",$fldr)) $event["folder_id"] = $fldr["id"];
				$inner->addData($this->formatData($event));
				$inner->addTag('sequence',$sequence);

				$subdata = $this->subForms($this->m_module['fetemplate_id'],'',array('folder_id'=>$this->m_module['folder_id'],'sd'=>$sd,'ed'=>$sd,'e_id'=>$event['id']),'inner');
				$this->logMessage(__FUNCTION__,sprintf('inner subforms [%s]',print_r($subdata,true)),3);
				foreach($subdata as $key=>$value) {
					$inner->addTag($key,$value,false);
				}

				$evt[] = $inner->show();
			}
			if ($this->hasOption('grpSuffix')) $evt[] = $this->getOption('grpSuffix');
		}
		else {
			$mthCount = $this->hasOption('monthCount') ? $this->getOption('monthCount') : 1;
			$mthForm = new Forms();
			$mthForm->init($this->m_dir.$this->getOption('byMonth'));
			$inner = new Forms();
			$inner->init($this->m_dir.$this->m_module['inner_html']);
			$evt= array();
			for($mth = 0; $mth < $mthCount; $mth++) {
				$ed = date('Y-m-d',strtotime(sprintf('%s + 1 month - 1 day',$sd)));
				$sd_time = strtotime($sd);
				$events = $this->fetchAllTest($this->buildSql($this->m_module,true,$sd,$ed));
				if (count($events) > 0) {
					$mthForm->addTag('month',date('M',$sd_time));
					$mthForm->addTag('fullMonth',date('F',$sd_time));
					$mthForm->addTag('monthNumeric',date('n',$sd_time));
					$mthForm->addTag('year',strftime('%Y',$sd_time));
					$mthForm->addTag('folder_id',$this->m_module['folder_id']);
					$evt[] = $mthForm->show();
					$this->logMessage(__FUNCTION__,sprintf('mthForm [%s] template [%s]',print_r($mthForm,true),$this->m_dir.$this->getOption('byMonth')),1);
					$ct = 0;
					if ($this->hasOption('grpPrefix')) $evt[] = $this->getOption('grpPrefix');
					foreach($events as $key=>$event) {
						$ct += 1;
						if ($this->m_module['rows'] > 0 && $ct > $this->m_module['columns']) {
							$ct = 1;
							if ($this->hasOption('grpSuffix')) $evt[] = $this->getOption('grpSuffix');
							if ($this->hasOption('grpPrefix')) 
								$evt[] = $this->getOption('grpPrefix');
							else
								$evt[] = '<div class="clearfix"></div>';
						}
						if (is_array($fldr) && array_key_exists("id",$fldr)) $event["folder_id"] = $fldr["id"];
						$inner->addData($this->formatData($event));
						$evt[] = $inner->show();
					}
					if ($this->hasOption('grpSuffix')) $evt[] = $this->getOption('grpSuffix');
				}
				$sd = date('Y-m-d',strtotime(sprintf('%s + 1 month',$sd)));
			}
		}
		$outer->addTag('events',implode('',$evt),false);
		$subdata = $this->subForms($this->m_module['fetemplate_id'],'',array('folder_id'=>$this->m_module['folder_id'],'sd'=>$sd,'ed'=>$sd),'outer');
		$this->logMessage(__FUNCTION__,sprintf('inner subforms [%s]',print_r($subdata,true)),3);
		foreach($subdata as $key=>$value) {
			$outer->addTag($key,$value,false);
		}
		$tmp = $outer->show();
		$this->logMessage(__FUNCTION__,sprintf('return [%s]',$tmp),2);
		return $tmp;
	}

    /**
     * @return array|mixed|string|string[]
     * @throws phpmailerException
     */
    function details() {
		if (!$module = parent::getModule())
			return "";
		$this->parseOptions($module['misc_options']);
		$outer = new Forms();
		$outer->init($this->m_dir.$module['outer_html']);
		if (array_key_exists('event_id',$_REQUEST)) {
			$this->logMessage(__FUNCTION__,sprintf('loading override event %d',$_REQUEST['event_id']),2);
			if (!($events = $this->fetchAllTest('select * from events where id = %d and deleted = 0 and published = 1 and enabled = 1',$_REQUEST['event_id'])))
				$events = array(0=>array('name'=>'We could not locate that event'));
		}
		else {
			$sd = $this->setDates();
			$sql = $this->buildSql($module,true,$sd);
			$events = $this->fetchAllTest($sql);
		}
		$inner = new Forms();
		$inner->init($this->m_dir.$module['inner_html']);
		$result = array();
		if ($fldr = $this->fetchSingleTest('select * from members_folders where id = %d',$this->m_module['folder_id'])) {
			$outer->addData($this->formatFolder($fldr));
		}
		foreach($events as $event) {
			if (is_array($fldr) && array_key_exists("id",$fldr)) $event["folder_id"] = $fldr["id"];
			$inner->addData($this->formatData($event));
			$subdata = $this->subForms($this->m_module['fetemplate_id'],'',array('folder_id'=>$this->m_module['folder_id'],'e_id'=>$event['id']),'inner');
			$this->logMessage(__FUNCTION__,sprintf('inner subforms [%s]',print_r($subdata,true)),3);
			foreach($subdata as $key=>$value) {
				$inner->addTag($key,$value,false);
			}
			$result[] = $inner->show();
		}
		$outer->addTag('events',implode('',$result),false);
		if ($module['folder_id'] != 0) {
			$folder = $this->fetchSingleTest('select * from members_folders where id = %d',$module['folder_id']);
			$outer->addData($this->formatFolder($folder));
		}
		$subdata = $this->subForms($this->m_module['fetemplate_id'],'',array('folder_id'=>$this->m_module['folder_id'],'e_id'=>array_key_exists('event_id',$_REQUEST) ? $_REQUEST['event_id']:0),'outer');
		$this->logMessage(__FUNCTION__,sprintf('outer subforms [%s]',print_r($subdata,true)),3);
		foreach($subdata as $key=>$value) {
			$outer->addTag($key,$value,false);
		}

		$tmp = $outer->show();
		$this->logMessage(__FUNCTION__,sprintf('return [%s]',$tmp),2);
		return $tmp;
	}

    /**
     * @return array|mixed|string|string[]
     * @throws phpmailerException
     */
    function createAnEvent() {
		if (!$module = parent::getModule())
			return "";
		$this->parseOptions($module['misc_options']);
		$outer = new Forms();
		$inner = new Forms();
		$inner->init($this->m_dir.$module['inner_html']);
		$flds = $inner->buildForm($this->config->getFields($module['configuration']));
		$outer->init($this->m_dir.$module['outer_html']);
		if ($module['folder_id'] != 0) {
			$fldr = $this->fetchSingleTest('select * from members_folders where id = %d',$module['folder_id']);
			$outer->addData($this->formatFolder($fldr));
		}
		if (array_key_exists('folder_id',$flds)) {
			$inner->setData('folder_id',$module['folder_id']);
		}
		if (array_key_exists('e_id',$_REQUEST) && $evt = $this->fetchSingleTest('select * from events where id = %d and deleted = 0',$_REQUEST['e_id'])) {
			$inner->addData($evt);
			if ($addr = $this->fetchSingleTest('select * from addresses where ownertype = "event" and ownerid = %d',$_REQUEST['e_id']))
				$inner->addData($addr);
		}
		else $evt = array('id'=>0);
		if (count($_POST) > 0 && array_key_exists('deleteAnEvent',$_POST)) {
			if (array_key_exists('delete',$_POST)) {
				$deletes = $_POST['delete'];
				foreach($deletes as $key=>$value) {
					$this->logMessage(__FUNCTION__,sprintf('deleting event %d',$value),2);
					$this->execute(sprintf('delete from events where id = %d',$value));
					$this->execute(sprintf('delete from event_dates where id = %d',$value));
				}
			}
		}
		if (count($_POST) > 0 && array_key_exists('createAnEvent',$_POST)) {
			if (array_key_exists('recurring_weekdays',$_POST) && is_array($_POST['recurring_weekdays'])) {
				$tmp = 0;
				foreach($_POST['recurring_weekdays'] as $key=>$value) {
					$tmp |= $value;
				}
				$_POST['recurring_weekdays'] = $tmp;
			}
			$inner->addData($_POST);
			if ($status = $inner->validate()) {
				$images = array();
				$msgs = array();
				$status = $this->processUploadedFiles(array('Image'),$images,$messages);
				if (!$status) {
					foreach($messages as $key=>$value) {
						$inner->addFormError($value);
					}
				}
				else {
					foreach($images as $key=>$value) {
						$inner->setData($key,$value);
					}
				}
			}
			if ($status) {
				$address = $_POST['address'];
				$values = array();
				$addr = array();
				foreach($flds as $key=>$value) {
					if (!array_key_exists('database',$value)) {
						if (strpos($value['name'],'[') === false) {
							$values[$inner->getField($key)->getAttribute('name')] = $inner->getData($key);
						}
						else {
							$addr[$key] = $address[$key];
						}
					}
				}
				$values['created'] = date('c');
				if ($evt['id'] > 0)
					$stmt = $this->prepare(sprintf('update events set %s=? where id = %d',
						implode('=?, ',array_keys($values)),$_POST['e_id']));
				else
					$stmt = $this->prepare(sprintf('insert into events(%s) value(%s)',
						implode(',',array_keys($values)),str_repeat('?,',count($values)-1).'?'));
				$stmt->bindParams(array_merge(array(str_repeat('s',count($values))),array_values($values)));
				$this->beginTransaction();
				if ($valid = $stmt->execute()) {
					if ($evt['id'] == 0) {
						$id = $this->insertId();
						$fldr = $this->prepare('insert into events_by_folder(event_id,folder_id) values(?,?)');
						$fldr->bindParams(array('dd',$id,$module['folder_id']));
						$valid = $valid && $fldr->execute();
						$addr['ownerid'] = $id;
						$addr['ownertype'] = 'event';
						$addr['addresstype'] = $this->fetchScalarTest('select id from code_lookups where type="eventAddressTypes" order by sort limit 1');
						$stmt = $this->prepare(sprintf('insert into addresses(%s) values(%s)',
							implode(',',array_keys($addr)),str_repeat('?,',count($addr)-1).'?'));
						$stmt->bindParams(array_merge(array(str_repeat('s',count($addr))),array_values($addr)));
						$valid = $valid && $stmt->execute();
					}
					else {
						$stmt = $this->prepare(sprintf('update addresses set %s=? where id = %d',
							implode('=?, ',array_keys($addr)),$_POST['a_id']));
						$stmt->bindParams(array_merge(array(str_repeat('s',count($addr))),array_values($addr)));
						$valid = $valid && $stmt->execute();
						$stmt->execute(sprintf('delete from event_dates where event_id = %d',$evt['id']));
						$id = $evt['id'];
					}
					$sd = $inner->getData('start_date');
					$ed = $inner->getData('end_date');
					if ($ed == '0000-00-00' || $ed == '') $ed = $sd;
					$stmt = $this->prepare('insert into event_dates(event_id,event_date) values(?,?)');
					while ($sd <= $ed && $valid) {
						$stmt->bindParams(array('ds',$id,$sd));
						$valid = $valid && $stmt->execute();
						$sd = date('Y-m-d',strtotime(sprintf('%s + 1 day',$sd)));
					}
				}
				if (!$valid) {
					$inner->addFormError('An Error Occurred. The Web Master has been notified');
					$this->rollbackTransaction();
				}
				else {
					$this->commitTransaction();
					$email = new Forms();
					$email->setHTML($this->getHtmlForm($module['parm1']));
					$email->addData($inner->getAllData());
					$email->addData($_POST['address']);
					$email->addTag('province',$this->fetchScalarTest('select province from provinces where id = %d',$_POST['address']['province_id']));
					$email->addTag('country',$this->fetchScalarTest('select country from countries where id = %d',$_POST['address']['country_id']));
					$mailer = new MyMailer();
					$mailer->Subject = sprintf("Event Submission - %s", SITENAME);
					$email->setOption('formDelimiter','{{|}}');
					$mailer->Body = $email->show();
					$emails = $this->configEmails("contact");
					foreach($emails as $key=>$info) {
						$mailer->AddAddress($info['email'],$info['name']);
					}
					$mailer->From= $emails[0]['email'];
					$mailer->FromName = $emails[0]['name'];
					$mailer->IsHTML();
					if (!$mailer->Send()) {
						Common::logMessage(__FUNCTION__,sprintf("Email send failed [%s]",print_r($mailer,true)),1,false);
						$inner->addFormError('An error occurred submitting your event. The Web Master has been notified');
					}
					else {
						$inner = new Forms();
						if (strlen($module['parm2']) > 0) {
							$inner->init($this->m_dir.$module['parm2']);
							$inner->addData($_POST);
						}
						$outer->addFormError('The Event has been submitted');
					}
				}
			}
			else {
				$inner->addFormError('Form validation failed');
				$this->logMessage(__FUNCTION__,sprintf('form to check address [%s]',print_r($inner,true)),1);
			}
		}
		$outer->addTag('form',$inner->show(),false);
		return $outer->show();		
	}

    /**
     * @return array|mixed|string|string[]
     * @throws phpmailerException
     */
    function listing() {
		if (!$module = $this->getModule())
			return "";
		$this->parseOptions($module['misc_options']);
		$this->logMessage(__FUNCTION__,sprintf("module [%s]",print_r($module,true)),2);
		$sql = $this->buildSql($module,true);
		if ($module['folder_id'] != 0)
			$root = $this->fetchSingleTest('select * from members_folders where id = %d',$module['folder_id']);
		else
			$root = array('left_id'=>0,'right_id'=>999999,'level'=>0,'id'=>0,'title'=>'','image'=>'','rollover_image'=>'');
		if ($this->hasOption('typeSelect')) {
			$folders = $this->fetchAllTest('select * from members_folders where left_id > %d and right_id < %d and level > %d order by left_id',$root['left_id'],$root['right_id'],$root['level']);
			$form = new Forms();
			$form->init($this->m_dir.$module['inner_html']);
			$result = array();
			$ct = 0;
			if ($this->hasOption('grpPrefix')) $result[] = $this->getOption('grpPrefix');
			foreach($folders as $key=>$folder) {
				$ct += 1;
				if ($module['rows'] > 0 && $ct > $module['columns']) {
					$ct = 1;
					if ($this->hasOption('grpSuffix')) $result[] = $this->getOption('grpSuffix');
					if ($this->hasOption('grpPrefix')) 
						$result[] = $this->getOption('grpPrefix');
					else
						$result[] = '<div class="clearfix"></div>';
				}
				$level = $folder['level'] - $root['level'];
				$spacer = str_repeat('&nbsp;',$level*2);
				$form->addData($this->formatFolder($folder));
				$form->addTag('level',$level);
				$form->addTag('spacer',$spacer,false);
				$form->addTag('sequence',$ct);
				if (array_key_exists('f_id',$_REQUEST))
					$form->addTag('selected', $folder['id'] == $_REQUEST['f_id'] ? 'selected':'');
				elseif (array_key_exists('cat_id',$_REQUEST))
					$form->addTag('selected', $folder['id'] == $_REQUEST['cat_id'] ? 'selected':'');
				$result[] = $form->show();
			}
			if ($this->hasOption('grpSuffix')) $result[] = $this->getOption('grpSuffix');
			$menu = implode("",$result);
		}
		else {
			$menu = sprintf('<ul class="level_0">%s</ul>',$this->buildUL($root,$module,0));
		}
		$outer = new Forms();
		$outer->init($this->m_dir.$module['outer_html']);
		$outer->addTag('listing',$menu,false);
		$outer->addData($this->formatFolder($root));
		$tmp = $outer->show();
		return $tmp;
	}

    /**
     * @param $root
     * @param $module
     * @param $root_level
     * @return string
     * @throws phpmailerException
     */
    private function buildUL($root, $module, $root_level) {
		$this->logMessage(__FUNCTION__,sprintf("root [%d] root_level [%d]",$root['id'],$root_level),2);
		$level = $this->fetchAllTest('select * from members_folders where level = %d and left_id > %d and right_id < %d and enabled = 1 order by left_id',$root['level']+1,$root['left_id'],$root['right_id']);
		$form = new Forms();
		$form->init($this->m_dir.$module['inner_html']);
		$menu = array();
		$seq = 0;
		foreach($level as $key=>$item) {
			$seq += 1;
			$form->addData($this->formatFolder($item));
			$form->addTag('sequence',$seq);
			$form->addTag('level',$root_level+1);
			$subdata = $this->subForms($this->m_module['fetemplate_id'],null,array('folder_id'=>$item['id']),'inner');
			$this->logMessage(__FUNCTION__,sprintf('subforms [%s]',print_r($subdata,true)),3);
			foreach($subdata as $key=>$value) {
				$form->addTag($key,$value,false);
			}
			$tmp = $form->show();
			$hasSubmenu = false;
			if (($subMenu = $this->buildUL($item,$module,$root_level+1)) != '') {
				$hasSubmenu = true;
				$form->addTag("hasSubmenu","hasSubmenu");
				$tmp = sprintf('%s<ul class="level_%d submenu">%s</ul>',$form->show(),$root_level+1,$subMenu);
			}
			if ($this->hasOption('delim') && $seq < count($level)) $tmp .= sprintf('<span class="delim">%s</span>',$this->getOption('delim'));
			$menu[] = sprintf('<li class="sequence_%d %s">%s</li>',$seq,$hasSubmenu ? 'hasSubmenu':'',$tmp);
		}
		$this->logMessage(__FUNCTION__,sprintf("return [%s]",print_r($menu,true)),3);
		return implode("",$menu);
	}

    /**
     * @return false|string|void
     * @throws phpmailerException
     */
    function ical() {
		if (!$module = $this->getModule())
			return "";
		if (array_key_exists('event_id',$_REQUEST) && $evt = $this->fetchSingleTest('select * from events where id = %d and deleted = 0 and enabled = 1 and published = 1',$_REQUEST['event_id'])) {
			$outer = new Forms();
			$outer->init($this->m_dir.$module['outer_html']);
			$outer->addData($this->formatiCal($evt));
			ob_clean();
			header('Content-type: text/calendar; charset=utf-8');
			header('Content-Disposition: inline; filename=calendar.ics');
			echo $outer->show();
			ob_flush();
			exit();
		}
		return $this->ajaxReturn(array('status'=>false));
	}

    /**
     * @return array|mixed|string|string[]
     * @throws phpmailerException
     */
    function eventTypes() {
		if (!$module = $this->getModule())
			return "";
		$outer = new Forms();
		$outer->init($this->m_dir.$module['outer_html']);
		if (array_key_exists('eventList',$module) && count($module['eventList']) > 0)
			$sql = sprintf('select id, id as event_type, value from code_lookups where type="eventType" and id in (select event_type from event_types where event_id in (%s)) order by sort, value',implode(',',$module['eventList']));
		else
			$sql = sprintf('select id, id as event_type, value from code_lookups where type="eventType" order by sort, value');
		$recs = $this->fetchAllTest($sql);
		$this->logMessage(__FUNCTION__,sprintf('sql [%s] recs [%d]',$sql,count($recs)),3);
		$result = array();
		$inner = new Forms();
		$inner->init($this->m_dir.$module['inner_html']);
		$flds = $inner->buildForm($this->config->getFields($module['configuration']));
		if (array_key_exists('event_type',$flds) && $this->hasOption('checkByDefault'))
			if ($this->getOption('checkByDefault'))
				$inner->getField('event_type')->addAttribute('checked','checked');
			else {
				$inner->getField('event_type')->removeAttribute('checked');
				$this->logMessage(__FUNCTION__,sprintf('removing checked attribute [%s]',print_r($inner->getField('event_type'),true)),1);
			}
		$ct = 0;
		if ($this->hasOption('grpPrefix')) $result[] = $this->getOption('grpPrefix');
		foreach($recs as $key=>$rec) {
			$ct += 1;
			if ($module['rows'] > 0 && $ct > $module['columns']) {
				$ct = 1;
				if ($this->hasOption('grpSuffix')) $result[] = $this->getOption('grpSuffix');
				if ($this->hasOption('grpPrefix')) 
					$result[] = $this->getOption('grpPrefix');
				else
					$result[] = '<div class="clearfix"></div>';
			}
			$inner->reset();
			$inner->addData($rec);
			$result[] = $inner->show();
		}
		if ($this->hasOption('grpSuffix')) $result[] = $this->getOption('grpSuffix');
		$outer->addData($module);
		if (array_key_exists('sd',$module)) {
			$tmp = explode('-',$module['sd']);
			$outer->setData('month',$tmp[1]);
			$outer->setData('year',$tmp[0]);
		}
		$flds = $outer->buildForm($flds);
		$outer->addTag('types',implode('',$result),false);
		return $outer->show();
	}

    /**
     * @return string|void
     * @throws phpmailerException
     */
    function itemRelations() {
		if (!$module = $this->getModule())
			return "";
		$this->logMessage(__FUNCTION__,sprintf("module [%s]",print_r($module,true)),2);
		if (!$this->hasOption('templateId')) {
			$this->logMessage(__FUNCTION__,sprintf('attempt to implement cross class functionality with no config [%s] this [%s] request [%s]',print_r($module,true),print_r($this,true),print_r($_REQUEST,true)),1,true);
			return "";
		}
		$fn = $this->fetchSingleTest('select t.id as fetemplate_id, t.module_function, m.classname from fetemplates t, modules m where t.id = %d and m.id = t.module_id',$this->getOption('templateId'));
		$tmp = $this->m_module;
		$tmp['classname'] = $fn['classname'];
		$tmp['fetemplate_id'] = $fn['fetemplate_id'];
		$tmp['module_function'] = $fn['module_function'];
		switch($fn['classname']) {
			case 'stores':
				$module['id'] = $this->getOption('templateId');
				$html = $this->stores($fn,$module);
				break;
			case 'product':
				$module['id'] = $this->getOption('templateId');
				$html = $this->product($fn,$module);
				break;
			case 'news':
				$module['id'] = $this->getOption('templateId');
				$html = $this->news($fn,$module);
				break;
			default:
				$html = '';
		}
		return $html;
	}

    /**
     * @param $module
     * @param $caller
     * @return void
     * @throws phpmailerException
     */
    function stores($module, $caller) {
		$this->logMessage(__FUNCTION__,sprintf('parms module [%s] caller [%s]',print_r($module,true),print_r($caller,true)),2);
		$evt = 0;
		if ($this->m_eventId > 0)
			$evt = $this->m_eventId;
		if (array_key_exists('event_id',$caller) && $caller['event_id'] > 0)
			$evt = $caller['event_id'];
		if (array_key_exists('e_id',$caller) && $caller['e_id'] > 0)
			$evt = $caller['e_id'];
		if (array_key_exists('event_id',$module) && $module['event_id'] > 0)
			$evt = $module['event_id'];
		if (array_key_exists('e_id',$module) && $module['e_id'] > 0)
			$evt = $module['e_id'];
		if ($stores = $this->fetchScalarAllTest('select owner_id from relations where owner_type = "store" and related_id = %d and related_type = "event"',$evt)) {
			$module['store_list'] = $stores;
			$module['folder_id'] = 0;
			$obj = new stores($module['fetemplate_id'],$module);
			if (method_exists('stores',$module['module_function'])) {
				$this->logMessage(__FUNCTION__,sprintf('invoking class with [%s]',print_r($module,true)),2);
				$html = $obj->{$module['module_function']}();
				return $html;
			}
			else {
				$this->logMessage(__FUNCTION__,sprintf('bail - no function [%s] in class for event %d',$module['module_function'],$this->m_eventId),1,true);
			}
		}
	}

    /**
     * @param $module
     * @return void
     * @throws phpmailerException
     */
    function product($module) {
		$this->logMessage(__FUNCTION__,sprintf('parms module [%s]',print_r($module,true)),2);
		$evt = 0;
		if ($this->m_eventId > 0)
			$evt = $this->m_eventId;
		if (array_key_exists('event_id',$module) && $module['event_id'] > 0)
			$evt = $module['event_id'];
		if (array_key_exists('e_id',$module) && $module['e_id'] > 0)
			$evt = $module['e_id'];
		$this->logMessage(__FUNCTION__,sprintf('select related_id from relations where owner_type = "event" and owner_id = %d and related_type = "product"',$evt),1);
		if ($products = $this->fetchScalarAllTest('select related_id from relations where owner_type = "event" and owner_id = %d and related_type = "product"',$evt)) {
			$module['product_list'] = $products;
			$module['folder_id'] = 0;
			$obj = new product($module['fetemplate_id'],$module);
			if (method_exists('product',$module['module_function'])) {
				$this->logMessage(__FUNCTION__,sprintf('invoking class with [%s]',print_r($module,true)),2);
				$html = $obj->{$module['module_function']}();
				return $html;
			}
			else {
				$this->logMessage(__FUNCTION__,sprintf('bail - no function [%s] in class for event %d',$module['module_function'],$this->m_eventId),1,true);
			}
		}
	}

    /**
     * @param $module
     * @return void
     * @throws phpmailerException
     */
    function news($module) {
		$this->logMessage(__FUNCTION__,sprintf('parms module [%s]',print_r($module,true)),2);
		$evt = 0;
		if ($this->m_eventId > 0)
			$evt = $this->m_eventId;
		if (array_key_exists('event_id',$module) && $module['event_id'] > 0)
			$evt = $module['event_id'];
		if (array_key_exists('e_id',$module) && $module['e_id'] > 0)
			$evt = $module['e_id'];
		$this->logMessage(__FUNCTION__,sprintf('select owner_id from relations where owner_type = "news" and related_id = %d and related_type = "event"',$evt),1);
		if ($news = $this->fetchScalarAllTest('select owner_id from relations where owner_type = "news" and related_id = %d and related_type = "event"',$evt)) {
			$module['news_list'] = $news;
			$module['folder_id'] = 0;
			$obj = new news($module['fetemplate_id'],$module);
			if (method_exists('news',$module['module_function'])) {
				$this->logMessage(__FUNCTION__,sprintf('invoking class with [%s]',print_r($module,true)),2);
				$html = $obj->{$module['module_function']}();
				return $html;
			}
			else {
				$this->logMessage(__FUNCTION__,sprintf('bail - no function [%s] in class for event %d',$module['module_function'],$this->m_eventId),1,true);
			}
		}
	}

    /**
     * @return string|void
     * @throws phpmailerException
     */
    function folderRelations() {
		if (!$module = $this->getModule())
			return "";
		$this->logMessage(__FUNCTION__,sprintf("module [%s]",print_r($module,true)),2);
		if ($module['folder_id'] == 0) {
			$this->logMessage(__FUNCTION__,'bail - no default folder',1);
			return '';
		}
		if (!$this->hasOption('templateId')) {
			$this->logMessage(__FUNCTION__,sprintf('attempt to implement cross class functionality with no config [%s] this [%s] request [%s]',print_r($module,true),print_r($this,true),print_r($_REQUEST,true)),1,true);
			return "";
		}
		$fn = $this->fetchSingleTest('select t.id as fetemplate_id, t.module_function, m.classname from fetemplates t, modules m where t.id = %d and m.id = t.module_id',$this->getOption('templateId'));
		$tmp = $this->m_module;
		$this->logMessage(__FUNCTION__,sprintf('m_module: [%s]',print_r($tmp,true)),1);
		$tmp['classname'] = $fn['classname'];
		$tmp['fetemplate_id'] = $fn['fetemplate_id'];
		$tmp['module_function'] = $fn['module_function'];
		$html='';
		switch($fn['classname']) {
		case 'gallery':
			$module['id'] = $this->getOption('templateId');
			$html = $this->galleryFolder($fn,$module['folder_id']);
			break;
		case 'news':
			$module['id'] = $this->getOption('templateId');
			$html = $this->newsFolder($fn,$module['folder_id']);
			break;
		}
		return $html;
	}

    /**
     * @param $module
     * @param $folder
     * @return string|void
     * @throws phpmailerException
     */
    private function galleryFolder($module, $folder) {
		$this->logMessage(__FUNCTION__,sprintf('parms [%s]',print_r($module,true)),2);
		if ($folders = $this->fetchSingleTest('select * from relations where owner_type = "galleryfolder" and related_id = %d and related_type = "eventfolder"',$folder)) {
			$module['folder_id'] = $folders['related_id'];
		}
		else {
			$folders = array();
			$this->logMessage(__FUNCTION__,sprintf('no gallery for eventFolder %d',$folder),1);
			return "";
		}
		$obj = new gallery($module['fetemplate_id'],$module);
		if (method_exists('gallery',$module['module_function'])) {
			$this->logMessage(__FUNCTION__,sprintf('invoking gallery with [%s]',print_r($module,true)),2);
			$html = $obj->{$module['module_function']}();
			return $html;
		}
		else {
			$this->logMessage(__FUNCTION__,sprintf('bail - no function [%s] in gallery for productFolder %d',$module['module_function'],$folder),1,true);
		}
	}

    /**
     * @param $module
     * @param $folder
     * @return string|void
     * @throws phpmailerException
     */
    private function newsFolder($module, $folder) {
		$this->logMessage(__FUNCTION__,sprintf('parms [%s]',print_r($module,true)),2);
		if ($folders = $this->fetchSingleTest('select * from relations where owner_type = "newsfolder" and related_id = %d and related_type = "eventfolder"',$folder)) {
			$module['folder_id'] = $folders['owner_id'];
		}
		else {
			$folders = array();
			$this->logMessage(__FUNCTION__,sprintf('no news for eventFolder %d',$folder),1);
			return "";
		}
		$obj = new news($module['fetemplate_id'],$module);
		if (method_exists('news',$module['module_function'])) {
			$this->logMessage(__FUNCTION__,sprintf('invoking news with [%s]',print_r($module,true)),2);
			$html = $obj->{$module['module_function']}();
			return $html;
		}
		else {
			$this->logMessage(__FUNCTION__,sprintf('bail - no function [%s] in news for event Folder %d',$module['module_function'],$folder),1,true);
		}
	}

    /**
     * @return array|mixed|string|string[]
     * @throws phpmailerException
     */
    function calendarSearchForm() {
		if (!$module = parent::getModule())
			return "";
		$this->parseOptions($module['misc_options']);
		$outer = new Forms();
		$inner = new Forms();
		$outer->setModule($module);
		$outer->init($this->m_dir.$module['outer_html']);
		$inner->init($this->m_dir.$module['inner_html']);
		$flds = $inner->buildForm($this->config->getFields($module['configuration']));
		if ($module["folder_id"] > 0) {
			$fldr = $this->fetchSingleTest("select * from members_folders where id = %d", $module["folder_id"]);
			$outer->addData($this->formatFolder($fldr));
		}
		if (array_key_exists(__FUNCTION__,$_REQUEST) && $_REQUEST[__FUNCTION__] > 0) {
			$inner->addData($_REQUEST);
			$inner->validate();
		}
		$outer->addTag("form",$inner->show(),false);
		return $outer->show();
	}

    /**
     * @return array|mixed|string|string[]
     * @throws phpmailerException
     */
    function calendarSearchResults() {
		if (!$module = parent::getModule())
			return "";
		$this->parseOptions($module['misc_options']);
		$sd = $this->setDates();
		if ($this->hasOption('monthCount')) {
			$ed = date('Y-m-d',strtotime(sprintf($sd." + %d month - 1 day",$this->getOption('monthCount'))));
		}
		else {
			$ed = date('Y-m-d',strtotime($sd." + 1 month - 1 day"));
		}
		//Sonarcloud report - Variables should be initialized before use
        if (!isset($ct)) {
            $ct = 0;
        }
		$outer = new Forms();
		$inner = new Forms();
		$outer->setModule($module);
		$flds = $inner->buildForm($this->config->getFields($module['configuration']));
		$outer->init($this->m_dir.$module['outer_html']);
		$inner->init($this->m_dir.$module['inner_html']);
		$outer->addTag("form",$inner->show(),false);
		$outer->addTag("count",0);
		if ($module["folder_id"] > 0) {
			$fldr = $this->fetchSingleTest("select * from members_folders where id = %d", $module["folder_id"]);
			$outer->addData($this->formatFolder($fldr));
		}
		else $fldr = array("id"=>0, "left_id"=>0, "right_id"=>99999);
		if ($module["include_subfolders"] != 0) {
			$fldrList = $this->fetchScalarAllTest("select id from members_folders where left_id >= %d and right_id <= %d and enabled = 1", $fldr["left_id"], $fldr["right_id"]);
		}
		else {
			$fldrList = array($fldr["id"]);
		}
		if (array_key_exists(__FUNCTION__,$_REQUEST) && $_REQUEST[__FUNCTION__] > 0) {
			$searchTerms = $_REQUEST['searchText'];
			$searchPhrase = explode(' ',$searchTerms);
			$events = array();
			for($x = 0; $x < count($searchPhrase); $x++) {
				$events[$x] = $this->fetchAllTest('select * from events where enabled = 1 and published = 1 and (name like "%%%s%%" or teaser like "%%%s%%" or description like "%%%s%%") and id in (select event_id from event_dates where event_date >= "%s" and event_date <= "%s")',
					$searchPhrase[$x],$searchPhrase[$x],$searchPhrase[$x], $sd, $ed);				
				foreach($events[$x] as $key=>$article) {
					$t = strip_tags($article["teaser"]);
					$b = strip_tags($article["description"]);
					if (stripos($article["name"],$searchPhrase[$x]) === false && stripos($t,$searchPhrase[$x]) === false && stripos($b,$searchPhrase[$x]) === false) {
						$this->logMessage(__FUNCTION__,sprintf("dropping article [%d] after stripping", $article["id"]),1);
						unset($events[$x][$key]);
					}
					else
						$events[$x][$key] = $article;
				}
			}
			$weighted = array();
			for($x = 0; $x < count($searchPhrase); $x++) {
				foreach($events[$x] as $item) {
					if (!array_key_exists($item['id'],$weighted))
						$weighted[$item['id']] = array('ct'=>0,'created'=>$item['created']);
					$weighted[$item['id']]['ct'] += 1;
				}
			}
			$sorted = array();
			foreach($weighted as $key=>$item) {
				$sorted[$item['ct']][] = array('type'=>$key,'sort'=>$item['created']);
			}
			$merged = array();
			for($x = count($searchPhrase); $x > 0; $x--) {
				if (array_key_exists($x,$sorted)) {
					usort($sorted[$x],"sortSearchItems");
					$merged = array_merge($merged,$sorted[$x]);
				}
			}
			$outer->addTag('searchCount',count($merged));
			$ids = array(0);
			foreach($merged as $key=>$item) { $ids[] = $item["type"]; }
			$sql = sprintf("select * from events where id in (%s) and id in (select event_id from events_by_folder f where f.folder_id in (%s)) order by instr('|%s|',concat('|',id,'|')) ", 
					implode(",",array_values($ids)), implode(",", $fldrList), implode("|",array_values($ids)));
			$pagenum = 0;
			$ct;
			$pagination = $this->getPagination($sql,$module,$ct,$pagenum);
			$this->logMessage(__FUNCTION__,sprintf('weighted [%s] sorted [%s] merged [%s]', print_r($weighted,true), print_r($sorted,true), print_r($merged,true)),1);
			$outer->addTag("pagination",$pagination,false);
			$this->logMessage(__FUNCTION__,sprintf("pagination [%s] from [%s] page [%s]", $pagination, $sql, $pagenum),1);
			$results = array();
			$recs = $this->fetchAllTest($sql);
			foreach($recs as $key=>$rec) {
				$inner->addData($this->formatData($rec));
				$results[] = $inner->show();
			}
			$outer->addTag("items",implode("",$results),false);
			$outer->addTag("count",count($results));
		}
		return $outer->show();
	}

    /**
     * @return false|string
     * @throws phpmailerException
     */
    function getModuleInfo() {
		return parent::getModuleList(array('collapsible','events','monthView','upcomingEvents','details','createAnEvent','listing','ical','eventTypes','itemRelations','folderRelations','calendarSearchForm','calendarSearchResults'));
	}

}

?>
