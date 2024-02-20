<?php

function metricSort( $a, $b ) {
	//
	//	arrays [idx]=>array(0=>'m1',1=>'m2'...,x=>'count')
	//
	$c = new Common();
	if ($a[count($a)-1] == $b[count($b)-1])
		return 0;
	else
	 	return $a[count($a)-1] < $b[count($b)-1] ? 1:-1;
}

class analytics extends Backend {

	private $m_api = null;
	private $m_startdate = null;
	private $m_enddate = null;
	private $m_account = null;

	public function __construct() {
		$this->M_DIR = 'backend/modules/analytics/';
		$this->setTemplates(
			array(
				'main'=>$this->M_DIR.'analytics.html',
				'dashboard'=>$this->M_DIR.'forms/dashboard.html',
				'demographics'=>$this->M_DIR.'forms/demographics.html'
			)
		);
		$this->setFields(array(
			'main' => array(),
			'dashboard'=>array(
				'startdate'=>array('type'=>'datepicker'),
				'enddate'=>array('type'=>'datepicker'),
				'analyticsform'=>array('type'=>'hidden','value'=>1),
				'movetype'=>array('type'=>'hidden','value'=>'M'),
				'movedirection'=>array('type'=>'hidden'),
				'submit'=>array('type'=>'submitbutton','value'=>'Change Dates')
			)
		));
		parent::__construct ();
		$parms = $GLOBALS["google_api"];
		$client_id = $parms["clientId"];
		$service_account_name = $parms["serviceAccount"];
		$keyfile = ADMIN.$parms["keyFile"];
		$redirect_url = sprintf('http://%s/',HOSTNAME);
		try {
			$client = new Google_Client();
			$client->setApplicationName("api-test");
			$client->setClassConfig('Google_Cache_File', 'directory', '../files');
			$client->setRedirectUri($redirect_url);
			//$client->setClientSecret($client_secret);
			$client->setAssertionCredentials(
				new Google_Auth_AssertionCredentials(
					$service_account_name,
					array('https://www.googleapis.com/auth/analytics'),
					file_get_contents($keyfile)
				)
			);
		}
		catch(Exception $err) {
			$this->logMessage(__FUNCTION__,sprintf("login failed parms [%s] err [%s]",print_r($parms,true),print_r($err,true)),1,true);
			header("Location: /moditcms");
		}
		$this->m_api = new Google_Service_Analytics($client);
		$this->m_account = sprintf('ga:%s',$parms["accountId"]);
		$this->m_enddate = date('Y-m-d');
		$this->m_startdate = date('Y-m-d', strtotime('-1 month'));
	}

	function __destruct() {
	
	}

	function show($injector = null) {
		$form = new Forms();
		$form->init($this->getTemplate('main'),array('name'=>'adminMenu'));
		$frmFields = $form->buildForm($this->getFields('main'));
		if ($injector == null || strlen($injector) == 0) {
			$injector = $this->getAnalytics(true);
		}
		$form->addTag('injector', $injector, false);
		return $form->show();
	}

	function getAnalytics($fromMain = 0) {
		$form = new Forms();
		$form->init($this->getTemplate('dashboard'));
		$flds = $form->buildForm($this->getFields('dashboard'));
		$form->addData(array('startdate'=>$this->m_startdate,'enddate'=>$this->m_enddate));
		if (count($_REQUEST) > 0 && array_key_exists('analyticsform',$_REQUEST)) {
			if (array_key_exists('movedirection',$_REQUEST)) {
				$_REQUEST['startdate'] = date('Y-m-d',strtotime(sprintf('%s %d month',$_REQUEST['startdate'],$_REQUEST['movedirection'])));
				$_REQUEST['enddate'] = date('Y-m-d',strtotime(sprintf('%s %d month',$_REQUEST['enddate'],$_REQUEST['movedirection'])));
			}
			$form->addData($_REQUEST);
			$this->m_startdate = $_REQUEST['startdate'];
			$this->m_enddate = $_REQUEST['enddate'];
		}
		$optParams = array(
			'dimensions' => 'ga:date',
			'sort' => 'ga:date');
		$visits = $this->m_api->data_ga->get($this->m_account, $this->m_startdate, $this->m_enddate,'ga:visits',$optParams);
		$views = $this->m_api->data_ga->get($this->m_account, $this->m_startdate, $this->m_enddate,'ga:pageviews',$optParams);
		$flot_data_visits = array();
		$flot_data_views = array();
		foreach ($visits->getRows() as $row) {
			$date = $row[0];
			$visit = $row[1];
			$year = substr($date, 0, 4);
			$month = substr($date, 4, 2);
			$day = substr($date, 6, 2);
			$utc = mktime(date('h') + 1, NULL, NULL, $month, $day, $year) * 1000;
			$flot_datas_visits[] = '[' . $utc . ',' . $visit . ']';
		}
		$flot_data_visits = '[' . implode(',', $flot_datas_visits) . ']';
		foreach ($views->getRows() as $row) {
			$date = $row[0];
			$visit = $row[1];
			$year = substr($date, 0, 4);
			$month = substr($date, 4, 2);
			$day = substr($date, 6, 2);
			$utc = mktime(date('h') + 1, NULL, NULL, $month, $day, $year) * 1000;
			$flot_datas_views[] = '[' . $utc . ',' . $visit . ']';
		}
		$flot_data_views = '[' . implode(',', $flot_datas_views) . ']';

		$data = array("visits"=>0,"visitors"=>0,"unique"=>0,"newvisitors"=>0,"percentnewvisitors"=>0,"pageviews"=>0,"pagespervisit"=>0,"averagelength"=>0,"bouncerate"=>0);
		$data['analytic_visits'] = $flot_data_visits;
		$data['analytic_views'] = $flot_data_views;
		$rawData = $this->m_api->data_ga->get($this->m_account, $this->m_startdate, $this->m_enddate,'ga:visits,ga:visitors,ga:newVisits,ga:percentNewVisits,ga:pageviews,ga:pageviewsPerVisit,ga:avgSessionDuration,ga:bounceRate,ga:uniquePageViews',array());
		if (is_array($rawData->getRows())) {
			foreach($rawData->getRows() as $row) {
				$data["visits"] = $row[0];
				$data["visitors"] = $row[1];
				$data["newvisitors"] = $row[2];
				$data["percentnewvisitors"] = sprintf("%.1f",$row[3]);
				$data["pageviews"] = $row[4];
				$data["pagespervisit"] = sprintf("%.1f",$row[5]);
				$h = (int)($row[6]/3600);
				$row[6] -= $h*3600;
				$m = (int)($row[6]/60);
				$row[6] -= $m*60;
				$s = $row[6];
				$data["averagelength"] = sprintf("%02d:%02d:%02d",$h,$m,$s);
				$data["bouncerate"] = sprintf("%.1f",$row[7]);
				$data["unique"] = $row[8];
			}
			$form->addData($data);
		}
		if ($fromMain)
			return $form->show();
		else
			return $this->ajaxReturn(array('status'=>true,'html'=>$form->show()));
	}

	function getDemographics() {
		if (array_key_exists('type',$_REQUEST)) $type = $_REQUEST['type'];
		$this->logMessage("getDemographics",sprintf("type [$type]"),2);
		$form = new Forms();
		$form->init($this->getTemplate('demographics'));
		$form->addTag($type.'active','active');
		switch($type) {
			case 'language':
				$rawData = $this->getGeneric('ga:language','ga:language');
				$header = array('Language');
				break;
			case 'country':
				$rawData = $this->getGeneric('ga:country','ga:country');
				$header = array('Country');
				break;
			case 'city':
				$rawData = $this->getGeneric('ga:city','ga:city');
				$header = array('City');
				break;
			case 'browser';
				$rawData = $this->getGeneric('ga:browser,ga:browserVersion','ga:browser,ga:browserVersion');
				$header = array('Browser','Version');
				break;
			case 'operatingSystem';
				$rawData = $this->getGeneric('ga:operatingSystem,ga:operatingSystemVersion','ga:operatingSystem,ga:operatingSystemVersion');
				$header = array('OS','Version');
				break;
			case 'networkLocation';
				$rawData = $this->getGeneric('ga:networkLocation','ga:networkLocation');
				$header = array('ISP');
				break;
			case 'screenResolution';
				$rawData = $this->getGeneric('ga:screenResolution','ga:screenResolution');
				$header = array('Resolution');
				break;
			case 'pagePath';
				$rawData = $this->getGeneric('ga:pagePath','ga:pagePath');
				$header = array('Page');
				break;
			case 'keyword';
				$rawData = $this->getGeneric('ga:keyword','ga:keyword');
				$header = array('Keyword');
				break;
			case 'referralPath';
				$rawData = $this->getGeneric('ga:source,ga:referralPath','ga:source,ga:referralPath');
				$header = array('Source','Link');
				break;
			case 'deviceCategory';
				$rawData = $this->getGeneric('ga:deviceCategory','ga:deviceCategory','ga:newUsers,ga:sessions');
				$header = array('Device','New Users');
				break;
			default:
				$this->addMessage(sprintf('Unknown demographic requested [%s]',$type));
				return $this->ajaxReturn(array('status'=>'false'));
				break;
		}
		$total = 0;
		$row = '<tr><th></th>';
		foreach($header as $key=>$value) {
			$row .= sprintf('<th>%s</th>',$value);
		}
		$result[] = $row.'<th>Visits</th><th>% of Visits</th></tr>';
		$idx = 0;
		if (is_array($rawData)) {
			foreach($rawData as $key=>$value) {
				$total += $value[count($value)-1];
			}
			$graph = array();
			foreach($rawData as $key=>$rec) {
				$row = sprintf('<tr><td class="a-right">%d</td>',$key+1);
				$title = array();
				foreach($rec as $skey=>$value) {
					if ($skey < count($rec)-1) {
						$row .= sprintf('<td%s>%s</td>',is_numeric($value) || is_float($value) ? " class='a-right'" : "", $value);
						$title[] = $value;
					}
				}
				$result[] = sprintf('%s<td><div  class="a-right">%d</div></td><td><div  class="a-right">%3.1f</div></td></tr>',$row,$rec[count($rec)-1],round(($rec[count($rec)-1]/$total)*100,1));
			}
			usort($rawData,"metricSort");
			foreach($rawData as $key=>$rec) {
				if ($key < 10) {
					$title = array();
					foreach($rec as $skey=>$value) {
						if ($skey < count($rec)-1) {
							$title[] = $value;
						}
					}
					$graph[] = sprintf('["%s", %d]',implode(', ',$title),$rec[count($rec)-1]);
				}
			}
			$form->addTag('title',implode(', ',$header));
			$form->addTag('metric',$total);
			$form->addTag('rows',implode(',',$graph),false);
			$form->addTag('result',implode('',$result),false);
		}
		return $this->ajaxReturn(array('status'=>'true','html'=>$form->show()));
	}

	function getGeneric($flds,$sort,$metric = 'ga:sessions') {
		$optParams = array(
			'dimensions' => $flds,
			'sort' => $sort);
		$data = $this->m_api->data_ga->get($this->m_account, $this->m_startdate, $this->m_enddate,$metric,$optParams);
		$this->logMessage(__FUNCTION__,sprintf("return [%s] from [%s/%s/%s]",print_r($data,true),$flds,$sort,$metric),1);
		return $data->getRows();
	}
}

?>