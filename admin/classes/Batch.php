<?php

define('SUCCESS',0);
define('WARNING',1);
define('ERROR',2);

class Batch extends Common {

	private $m_processing = 0;
	private $m_errors = 0;
	private $m_processed = 0;
	private $m_warnings = 0;

    /**
     * @throws Exception
     */
	function __construct() {
		if (!DEFINED('DEBUG')) 
			$debug = 1;
		else
			$debug = DEBUG > 0 ? DEBUG : 1;
		parent::__construct($debug,false,sprintf('%s',date('Y-m-d')));
	}

    /**
     * @param $id
     * @param $pmtInfo
     * @param $newId
     * @return bool
     * @throws phpmailerException
     */
    function copyOrder($id, $pmtInfo, &$newId) {
		$this->beginTransaction();
		$hdr = $this->fetchSingle(sprintf('select * from orders where id = %d',$id));
		$dtls = $this->fetchAll(sprintf('select * from order_lines where order_id = %d and deleted = 0 order by id',$id));
		$dtl_ids = $this->fetchScalarAll(sprintf('select line_id from order_lines where order_id = %d and deleted = 0',$id));
		$taxes = $this->fetchAll(sprintf('select * from order_taxes where order_id = %d and (line_id = 0 or line_id in (%s)) order by id',$id,implode(",",$dtl_ids)));
		$addresses = $this->fetchAll(sprintf('select * from addresses where ownertype = "order" and ownerid = %d order by id',$id));
		unset($hdr['id']);
		$hdr['order_date'] = date(DATE_ATOM);
		$hdr['created'] = date(DATE_ATOM);
		$hdr['authorization_amount'] = $pmtInfo['AMT'];
		$hdr['authorization_code'] = array_key_exists('AUTHCODE',$pmtInfo) ? $pmtInfo['AUTHCODE'] : $pmtInfo['PPREF'];
		$hdr['authorization_transaction'] = $pmtInfo['PNREF'];
		$hdr['authorization_info'] = print_r($pmtInfo,true);
		$hdr['authorization_type'] = $hdr["authorization_type"];	//array_key_exists('AUTHCODE',$pmtInfo) ? 'PayFlow' : 'PayPal via PayFlow';
		$hdr['random'] = random_int(1,100000);
		$hdr['order_status'] = STATUS_PROCESSING;
		$stmt = $this->prepare(sprintf('insert into orders(%s) values(%s?)',
				implode(', ',array_keys($hdr)), str_repeat('?, ',count($hdr)-1)));
		$stmt->bindParams(array_merge(array(str_repeat('s',count($hdr))),array_values($hdr)));
		$valid = true;
		if ($valid = $stmt->execute()) {
			$newId = $this->insertId();
			foreach($dtls as $key=>$line) {
				unset($line['id']);
				$line['order_id'] = $newId;
				$stmt = $this->prepare(sprintf('insert into order_lines(%s) values(%s?)',
					implode(', ',array_keys($line)), str_repeat('?, ',count($line)-1)));
				$stmt->bindParams(array_merge(array(str_repeat('s',count($line))),array_values($line)));
				$valid = $valid && $stmt->execute();
			}
			foreach($taxes as $key=>$line) {
				unset($line['id']);
				$line['order_id'] = $newId;
				$stmt = $this->prepare(sprintf('insert into order_taxes(%s) values(%s?)',
					implode(', ',array_keys($line)), str_repeat('?, ',count($line)-1)));
				$stmt->bindParams(array_merge(array(str_repeat('s',count($line))),array_values($line)));
				$valid = $valid && $stmt->execute();
			}
			foreach($addresses as $key=>$line) {
				unset($line['id']);
				$line['ownerid'] = $newId;
				$stmt = $this->prepare(sprintf('insert into addresses(%s) values(%s?)',
					implode(', ',array_keys($line)), str_repeat('?, ',count($line)-1)));
				$stmt->bindParams(array_merge(array(str_repeat('s',count($line))),array_values($line)));
				$valid = $valid && $stmt->execute();
			}
		}
		if ($valid) {
			$this->commitTransaction();
		}
		else {
			$this->rollbackTransaction();
			$this->logResult($id,ERROR,sprintf('An error occurred copying order #%d',$id));
			$this->logMessage(__FUNCTION__,sprintf('Nightly Processing: An error occurred copying order #%d',$id),1,true);
		}
		return $valid;
	}

    /**
     * @param $processingRecord
     * @return void
     * @throws phpmailerException
     */
    function setProcessing($processingRecord) {
		$this->m_processing = $processingRecord;
		$this->logMessage(__FUNCTION__,sprintf('init with record #%d',$processingRecord['id']),2);
		$this->logMessage(__FUNCTION__,sprintf('init with record #%s',print_r($processingRecord,true)),2);
	}

    /**
     * @return void
     * @throws \Twilio\Exceptions\ConfigurationException
     * @throws phpmailerException
     */
	function processOrders() {
		$c = new custom(0,array());
		$bDate = $this->m_processing['bill_date'];
		$sDate = $this->m_processing['start_date'];
		$eDate = $this->m_processing['end_date'];
		$this->logMessage(__FUNCTION__,sprintf('starting processing bDate [%s] sDate [%s] eDate [%s]',$bDate,$sDate,$eDate),1);
		//
		//	grab all orders to be billed in this period, or orders that should have been billed already [could have failed the authorization but ok now]
		//
		$recs = $this->fetchAll(sprintf('select b.*, o1.total, o2.authorization_transaction, o1.recurring_period, o1.recurring_type, o1.member_id, o2.baid, o2.ba_authorization_transaction, o2.authorization_type, o1.authorization_transaction as ref_transaction from order_billing b, orders o1, orders o2 where b.billed = 0 and b.billing_date <= "%s" and o1.id = b.original_id and o1.order_status & %d = %d and o1.order_status & %d = 0 and o2.order_status & %d = 0 and o2.id = o1.authorization_transaction group by original_id',
				$eDate, STATUS_PROCESSING | STATUS_RECURRING, STATUS_PROCESSING | STATUS_RECURRING, STATUS_CREDIT_HOLD, STATUS_CREDIT_HOLD));
		$this->logMessage(__FUNCTION__,sprintf('Found %d records to check',count($recs)),1);
		$snoopy = new Snoopy();
		if (array_key_exists('payflow',$GLOBALS)) {
			$parms = $GLOBALS['payflow'];
			$query = array(
				'TRXTYPE'=>'S',
				'TENDER'=>'C',
				'PARTNER'=>$parms['partner'],
				'VENDOR'=>$parms['vendor'],
				'USER'=>$parms['user'],
				'PWD'=>$parms['pwd'],
				'ORIGID'=>'',
				'AMT'=>'0.00',
				'CURRENCY'=>$parms["currency"]
			);
			$snoopy->host = $parms['auth'];
			$snoopy->port = 443;
			$snoopy->httpmethod = 'POST';
			$snoopy->curl_path = $parms['curl_path'];
		}
		else {
			$parms = array();
		}
		$this->beginTransaction();
		foreach($recs as $key=>$order) {
			$billingType = $c->getBillingType($order["original_id"]);
			$this->logMessage(__FUNCTION__,sprintf("auth type [%s] baid [%s] billingType [%s]",$order["authorization_type"],$order["baid"], $billingType),1);
			switch($billingType) {
				case "PayPal":
				case "PayFlow":
					if (strpos($order["authorization_type"],"PayPal") !== false && $order["baid"] == "") {
						$o_order = $this->fetchSingle(sprintf('select * from orders where id = %d',$order['ref_transaction']));
						if ($o_order["nags"] > 3) {
							$this->execute(sprintf("update orders set order_status = %d where id = %d", $o_order["order_status"] | STATUS_CREDIT_HOLD, $o_order["id"]));
							//$this->execute(sprintf("update orders set order_status = %d where id = %d", $o_order["order_status"] | STATUS_CREDIT_HOLD, $order["id"]));
							$this->logResult($o_order["id"],ERROR,"Order put on Credit Hold - no BA");
						}
						else {
							$this->execute(sprintf("update orders set nags = nags+1 where id = %d", $o_order["id"]));
							$this->sendEmail('order-no-ba',$o_order,true,'Error Processing Your Order');
							$this->logResult($order['original_id'],ERROR,'No Billing Agreement');
						}
						continue 2;
					}
					$valid = true;
					$query['ORIGID'] = $order['authorization_transaction'];
					if (strlen($order['baid']) > 0) {
						$query['BAID'] = $order['baid'];
						$query['TENDER'] = 'P';
						$query['ACTION'] = 'D';
						unset($query['ORIGID']);
						//$query['ORIGID'] = $order['ba_authorization_transaction'];
					}
					else
						if (array_key_exists('BAID',$query)) unset($query['BAID']);
					$query['AMT'] = $order['total'];
					$snoopy->submit($parms['auth'],$query);
					$this->logMessage(__FUNCTION__,sprintf('payflow snoopy [%s] result [%s]',print_r($query,true),print_r($snoopy->results,true)),2);
					$result = $this->depairOptions(urldecode($snoopy->results),array('&','='));
					if ($result['RESULT'] != 0) {
						$order['attempts'] += 1;
						$valid = false;
						$upd = array(
							'attempts'=>$order['attempts'],
							'authorization_info'=>print_r($result,true),
							'authorization_message'=>$result['RESPMSG'],
							'billed_on'=>date(DATE_ATOM)
						);
						$stmt = $this->prepare(sprintf('update order_billing set %s=? where id = %d', implode('=?, ',array_keys($upd)),$order['id']));
						$stmt->bindParams(array_merge(array(str_repeat('s',count($upd))),array_values($upd)));
						$stmt->execute();
						$this->logResult($order['original_id'],ERROR,'Transaction was declined');
						//$this->logMessage(__FUNCTION__,sprintf('payflow snoopy [%s] result [%s]',print_r($query,true),print_r($snoopy->results,true)),1,true,false);
						if ($order['attempts'] >= 5) {
							$this->execute(sprintf('update orders set order_status = order_status | %d where id = %d',STATUS_CREDIT_HOLD,$order['original_id']));
							$this->logResult($order['original_id'],ERROR,'Order put on Credit Hold');
							$this->sendEmail('order-cancelled',$this->fetchSingle(sprintf('select * from orders where id = %d',$order['original_id'])),true,'Credit Card Declined');
							$c = new Custom(0,array());
							$c->postRecurringCancelled($order);
						}
					}
					$authType = array_key_exists('AUTHCODE',$result) ? 'PayFlow' : 'PayPal via PayFlow';
					break;
				case "Other":
				case "onAccount":
					$result = array('authorization_type'=>'Other','authorization_code'=>sprintf('From Order #%d',$order["original_id"]),'authorization_transaction'=>'','authorization_amount'=>0,'RESPMSG'=>'On Account','PNREF'=>'','PPREF'=>'');
					$authType = 'Other';
					$query['AMT'] = 0;
					$valid = true;
				break;
				default:
					$valid = false;
					break;
			}
			if ($valid) {
				$this->execute(sprintf("update order_billing set billed=1 where id = %d",$order["id"]));
				$this->m_processed += 1;
				$result['AMT'] = $query['AMT'];
				if ($this->copyOrder($order['original_id'],$result,$newId)) {
					$upd = array(
						'order_id'=>$newId,
						'billed'=>1,
						'billed_on'=>date(DATE_ATOM),
						'authorization_info'=>print_r($result,true),
						'authorization_transaction'=>$result['PNREF'],
						'authorization_code'=>array_key_exists('AUTHCODE',$result) ? $result['AUTHCODE'] : $result['PPREF'],
						'authorization_amount'=>$query['AMT'],
						'authorization_message'=>$result['RESPMSG'],
						'authorization_type'=>$authType
					);
					$stmt = $this->prepare(sprintf('update order_billing set %s=? where id = %d', implode('=?, ',array_keys($upd)),$order['id']));
					$stmt->bindParams(array_merge(array(str_repeat('s',count($upd))),array_values($upd)));
					$stmt->execute();
					$code = $this->fetchSingle(sprintf('select * from code_lookups where type="recurrenceType" and code = "%s"',$order['recurring_type']));
					$terms = explode("|",$code["extra"]);
					if ($terms[2] == "1") {
						//
						//	an unending renewal
						//
						$b = $this->fetchSingle(sprintf('select * from order_billing where original_id = %d order by billing_date desc limit 1',$order['original_id']));
						$b_new = array("original_id"=>$order["original_id"],"period_number"=>$b["period_number"]+1);
						switch($order["recurring_type"]) {
						case "Daily":
							$dt = date("Y-m-d", strtotime(sprintf("%s + %d days", $b["billing_date"],$order["recurring_period"])));
							break;
						case "Weekly":
							$dt = date("Y-m-d", strtotime(sprintf("%s + %d weeks", $b["billing_date"],$order["recurring_period"])));
							break;
						case "Monthly":
							$dt = date("Y-m-d", strtotime(sprintf("%s + %d months", $b["billing_date"],$order["recurring_period"])));
							break;
						default:
						}
						$b_new["billing_date"] = $c->calcPickup($dt,$this->fetchSingle(sprintf("select * from order_lines where order_id = %d and custom_package = 'S'", $order["original_id"])));
						$stmt = $this->prepare(sprintf('insert into order_billing(%s) values(?%s)', implode(', ',array_keys($b_new)),str_repeat(", ?",count($b_new)-1)));
						$stmt->bindParams(array_merge(array(str_repeat('s',count($b_new))),array_values($b_new)));
						$stmt->execute();
					}
					$this->logMessage(__FILE__,sprintf("testing [%s] vs [%s]",$order["billing_date"],date("Y-m-d")),1);
					//
					//	reschedule the remaining shipments based on the recurring period
					//
					$period = explode("|",$this->fetchScalar(sprintf('select * from code_lookups where type="recurrenceType" and code = "%s"',$order["recurring_type"])));
					$next_dt = date("Y-m-d");
					//
					//	Only reschedule 1 order - weekends can shift 1 delivery but shouldn't affect subsequent
					//
					$to_bill = $this->fetchAll(sprintf("select * from order_billing where original_id = %d and billed = 0 order by period_number limit 1",$order["original_id"]));
					foreach($to_bill as $k1=>$v1) {
						switch($order["recurring_type"]) {
						case "Daily":
							$dt = date("Y-m-d", strtotime(sprintf("%s + %d days", $next_dt, $order["recurring_period"])));
							break;
						case "Weekly":
							$dt = date("Y-m-d", strtotime(sprintf("%s + %d weeks", $next_dt, $order["recurring_period"])));
							break;
						case "Monthly":
							$dt = date("Y-m-d", strtotime(sprintf("%s + %d months", $next_dt, $order["recurring_period"])));
							break;
						default:
							break;
						}
						$next_dt = $c->calcPickup($dt,$this->fetchSingle(sprintf("select * from order_lines where order_id = %d and custom_package = 'S'", $order["original_id"])));
						$this->execute(sprintf("update order_billing set billing_date = '%s' where id = %d",$next_dt,$v1["id"]));
					}
					$_REQUEST = array('o_id'=>$newId,'m_id'=>$order['member_id']);
					$this->logMessage(__FUNCTION__,sprintf("print receipt request [%s] order [%s] this [%s]",print_r($_REQUEST,true),print_r($order,true),print_r($this,true)),1);
					$this->preRecurringOrder($order,$newId);
					$c->postSaleProcessing($newId,true,$this);
					$this->postRecurringOrder($order, $newId);
					$this->logResult($order['original_id'],SUCCESS,sprintf('New Order <a href="http://%s/modit/orders/showOrder?o_id=%d" target="new">%d</a> has been placed for processing',HOSTNAME,$newId,$newId));
				}
				else {
					$this->logResult($order['original_id'],ERROR,sprintf('An error occurred creating order'));
				}
			}
		}
		//$this->rollbackTransaction();
		$this->commitTransaction();
		$this->logMessage(__FUNCTION__,sprintf('finished processing'),1);
	}

    /**
     * @param $parms
     * @return array
     * @throws phpmailerException
     */
    function getToken($parms) {
			$s = new Snoopy();
			//Sonarcloud report - Variables should be initialized before use
			if (!isset($formattedCart)) {
				$formattedCart = array();
			}
			$securetoken = substr(SHA1(date(DATE_ATOM).' '.$parms['partner']),0,32);
			$_SESSION['cart']['header']['securetoken'] = $securetoken;
			$formvars = array(
				'PARTNER'=>$parms['partner'],
				'VENDOR'=>$parms['vendor'],
				'PWD'=>$parms['pwd'],
				'USER'=>$parms['user'],
				'TRXTYPE'=>$parms['trxtype'],
				'AMT'=>number_format($formattedCart['header']['total'],2),
				'CREATESECURETOKEN'=>'Y',
				'SECURETOKENID'=>$securetoken
			);
			$s->host = $parms['auth'];
			$s->port = 443;
			$s->httpmethod = 'POST';
			$s->curl_path = $parms['curl_path'];
			$s->submit($parms['auth'],$formvars);
			$this->logMessage(__FUNCTION__,sprintf('payflow snoopy [%s] formvars [%s] result [%s]',print_r($s,true),print_r($formvars,true),print_r($s->results,true)),2);
			$result = $this->depairOptions(urldecode($s->results),array('&','='));
			return $result;
	}

    /**
     * @return void
     * @throws phpmailerException
     */
	function checkOrders() {
		$bDate = $this->m_processing['bill_date'];
		$sDate = $this->m_processing['start_date'];
		$eDate = $this->m_processing['end_date'];
		$this->logMessage(__FUNCTION__,sprintf('starting processing bDate [%s] sDate [%s] eDate [%s]',$bDate,$sDate,$eDate),1);
		$cutoff = date('Y-m-d',strtotime('today - 10 months'));
		$cc_cutoff = date('Y-m',strtotime('today+30 days'));
		$sql = sprintf('select o.* from orders o where order_status & %d = %d and exists (select 1 from order_billing b where b.original_id = o.id and b.billed = 0) order by o.id', STATUS_RECURRING | STATUS_PROCESSING, STATUS_RECURRING | STATUS_PROCESSING);
		$recs = $this->fetchAll($sql);
		$this->logMessage(__FUNCTION__,sprintf('sql [%s] returned [%d] orders to check, authorization cutoff [%s], expiry cutoff [%s]', $sql, count($recs), $cutoff, $cc_cutoff),2);
		foreach($recs as $key=>$order) {
			if (date('Y-m-d',strtotime($order['order_date'])) <= $cutoff) {
				$this->logResult($order['id'],WARNING,sprintf('Authorization is expiring %s',date("d-M-Y",strtotime($order['order_date']." + 1 year"))));
				$this->execute(sprintf('update orders set order_status = order_status | %d where id = %d',STATUS_EXPIRING,$order['id']));
				$this->sendEmail('expiring-authorization',$order);
			}
			if (strlen($order['cc_expiry']) > 0 && $order['cc_expiry'] <= $cc_cutoff) {
				$this->logResult($order['id'],WARNING,sprintf('Credit card is expiring %s',date("M-Y",strtotime($order['cc_expiry'].'-01'))));
				$this->execute(sprintf('update orders set order_status = order_status | %d where id = %d',STATUS_EXPIRING,$order['id']));
				$this->sendEmail('expiring-cc',$order);
			}
		}
		$this->logMessage(__FUNCTION__,sprintf('ending processing bDate [%s] sDate [%s] eDate [%s]',$bDate,$sDate,$eDate),1);
	}

    /**
     * @param $form
     * @param $order
     * @param $override
     * @param $title
     * @return void
     * @throws phpmailerException
     */
    function sendEmail($form, $order, $override = false, $title = 'Expiring Authorization') {
		if (($order['nags'] % 14) != 0 && !$override) {
			$this->logMessage(__FUNCTION__,sprintf("not nagging order #%d [%d]",$order['id'],$order['nags']),1);
			$this->execute(sprintf("update orders set nags = nags+1 where id = %d",$order['id']));
			return;
		}
		$this->execute(sprintf("update orders set nags = nags+1 where id = %d",$order['id']));
		$emails = $this->configEmails("ecommerce");
		if (count($emails) == 0)
			$emails = $this->configEmails("contact");
		$mailer = new myMailer();
		$mailer->Subject = sprintf("%s - %s", $title, SITENAME);
		$member = $this->fetchSingle(sprintf('select * from members where id = %d',$order['member_id']));
		$body = new Forms();
		$body->setHTML($this->getHtmlForm($form,'product'));
		$order['formattedExpiry'] = date("M-Y",strtotime($order['cc_expiry']."-01"));
		$body->addData(array("order"=>$this->formatOrder($order)));
		$body->addData(array("member"=>$member));
		$body->setOption('formDelimiter','{{|}}');
		$mailer->From = $emails[0]['email'];
		$mailer->FromName = $emails[0]['name'];
		$mailer->Body = $body->show();
		$mailer->IsHTML(true);
		$mailer->addAddress($member['email'],$member['firstname'].' '.$member['lastname']);
		foreach($emails as $key=>$value) {
			$mailer->addBCC($value['email'],$value['name']);
		}
		if (!$mailer->Send()) {
			$this->logMessage(__FUNCTION__,sprintf("Email send failed [%s]",print_r($mailer,true)),1,true);
		}
	}

    /**
     * @param $id
     * @param $status
     * @param $msg
     * @return void
     * @throws phpmailerException
     */
    function logResult($id, $status, $msg) {
		$stmt = $this->prepare(sprintf('insert into order_processing_details(processing_id,order_id,processing_status,comments) values(?,?,?,?);'));
		$stmt->bindParams(array('ddds',$this->m_processing['id'],$id,$status,$msg));
		$stmt->execute();
		if ($status == ERROR) $this->m_errors += 1;
		if ($status == WARNING) $this->m_warnings += 1;
		$this->logMessage(__FUNCTION__,sprintf('Order: [%d], Status: [%s] Message: [%s]', $id, $status, $msg), 1);
	}

    /**
     * @param $dt
     * @return false|string
     * @throws phpmailerException
     */
    function parseDate($dt) {
		$mth = substr($dt,0,2);
		$dy = substr($dt,2,2);
		$yr = substr($dt,4,4);
		$rdt = date('Y-m-d',strtotime(sprintf('%s-%s-%s',$yr,$mth,$dy)));
		$this->logMessage(__FUNCTION__,sprintf('yr [%s] mn [%s] dy [%s] dt [%s] from [%s]',$yr,$mth,$dy,$rdt,$dt),2);
		return $rdt;
	}

    /**
     * @param $ts
     * @return false|string
     * @throws phpmailerException
     */
    function parseDateTime($ts) {
		$tmp = explode('  ',$ts);
		$dt = explode('-',$tmp[0]);
		$this->logMessage(__FUNCTION__,sprintf('yr [%s] mn [%s] dy [%s] time [%s]',$dt[2],$dt[1],$dt[0],$tmp[1]),1);
		$dt = date('Y-m-d h:i:s',strtotime(sprintf('%s-%s-%s %s',(int)$dt[2]+2000,$dt[1],$dt[0],$tmp[1])));
		return $dt;
	}

    /**
     * @param $data
     * @return array
     * @throws phpmailerException
     */
    function parsePayments($data) {
		$pmts = array();
		for($i = 1; $i < 999; $i++) {
			if (!array_key_exists(sprintf('P_PNREF%d',$i),$data)) {
				break;
			}
			$pmts[$i] = array(
				'PNREF'=>$data[sprintf('P_PNREF%d',$i)],
				'TRANSTIME'=>$this->parseDateTime($data[sprintf('P_TRANSTIME%d',$i)]),
				'RESULT'=>$data[sprintf('P_RESULT%d',$i)],
				'AMT'=>$data[sprintf('P_AMT%d',$i)],
				'TRANSTATE'=>$data[sprintf('P_TRANSTATE%d',$i)]
			);
		}
		$this->logMessage(__FUNCTION__,sprintf('return [%s]',print_r($pmts,true)),3);
		return $pmts;
	}

    /**
     * @param $order_id
     * @return void
     * @throws phpmailerException
     */
    function test($order_id) {
		$parms = $GLOBALS['payflow'];
		$snoopy = new Snoopy();
		$query = array(
			'TRXTYPE'=>'S',
			'TENDER'=>'C',
			'PARTNER'=>$parms['partner'],
			'VENDOR'=>$parms['vendor'],
			'USER'=>$parms['user'],
			'PWD'=>$parms['pwd']
		);
		$snoopy->host = $parms['auth'];
		$snoopy->port = 443;
		$snoopy->httpmethod = 'POST';
		$snoopy->curl_path = $parms['curl_path'];
		$order = $this->fetchSingle(sprintf('select * from orders where id = %d',$order_id));
		$query['ORIGID'] = $order['authorization_transaction'];
		$query['AMT'] = $order['total'];
		$snoopy->submit($parms['auth'],$query);
		$this->logMessage(__FUNCTION__,sprintf('payflow snoopy [%s] formvars [%s] result [%s]',print_r($snoopy,true),print_r($query,true),print_r($snoopy->results,true)),2);
		$result = $this->depairOptions(urldecode($snoopy->results),array('&','='));
		echo print_r($result,true);
	}

    /**
     * @return int
     * @throws phpmailerException
     */
    function getProcessed() {
		$this->logMessage(__FUNCTION__,sprintf('returning %d',$this->m_processed),1);
		return $this->m_processed;
	}

    /**
     * @return int
     * @throws phpmailerException
     */
    function getErrors() {
		$this->logMessage(__FUNCTION__,sprintf('returning %d',$this->m_errors),1);
		return $this->m_errors;
	}

    /**
     * @return int
     * @throws phpmailerException
     */
    function getWarnings() {
		$this->logMessage(__FUNCTION__,sprintf('returning %d',$this->m_warnings),1);
		return $this->m_warnings;
	}

    /**
     * @return void
     * @throws phpmailerException
     */
	function sendProcessingReport() {
		$alerts = $this->fetchAll(sprintf("select * from order_processing_details where processing_id = %d",$this->m_processing["id"]));
		$module = $this->fetchSingle(sprintf("select * from fetemplates where id = %d",$this->getOption("adminLog")));
		$dtl = new Forms();
		$dtl->init(sprintf("./frontend/forms/custom/%s",$module['inner_html']));
		$dtl->setOption('formDelimiter','{{|}}');
		$results = array();
		foreach($alerts as $key=>$value) {
			switch($value['processing_status']) {
			case SUCCESS:
				$value['processing_status'] = "Information";
				break;
			case WARNING:
				$value['processing_status'] = "Warning";
				break;
			case ERROR:
				$value['processing_status'] = "Error";
				break;
			default:
				$value['processing_status'] = sprintf("Unknown - %d",$value["processing_status"]);
				break;
			}
			$dtl->addData($value);
			$results[] = $dtl->show();
		}
		$email = new Forms();
		$email->init(sprintf("./frontend/forms/custom/%s",$module['outer_html']));
		$email->addTag("results",implode("",$results),false);
		$emails = $this->configEmails("ecommerce");
		$email->setOption('formDelimiter','{{|}}');
		if (count($emails) == 0)
			$emails = $this->configEmails("contact");
		$mailer = new MyMailer();
		$mailer->Subject = sprintf("Nightly Processing - %s", SITENAME);
		$mailer->Body = $email->show();
		$mailer->From = sprintf("noreply@%s",HOSTNAME);	//$emails[0]['email'];
		$mailer->FromName = sprintf("noreply@%s",HOSTNAME);	//$emails[0]['name'];
		$mailer->IsHTML(true);
		foreach($emails as $key=>$value) {
			$mailer->addAddress($value['email'],$value['name']);
		}
		if (!$mailer->Send()) {
			$this->logMessage(__FUNCTION__,sprintf("email send failed [%s]",print_r($mailer,true)),1,true);
		}
	}

    /**
     * @param $orig_order
     * @param $o_id
     * @return void
     * @throws phpmailerException
     */
    function preRecurringOrder($orig_order, $o_id) {
		$this->logMessage(__FUNCTION__,sprintf("orig_order [%s] o_id [%s]", print_r($orig_order,true),print_r($o_id,true)),1);
		$_SESSION["cart"]["products"] = $this->fetchAll(sprintf("select p.*, ol.price, ol.quantity, ol.value, ol.custom_package from order_lines ol, product p where ol.order_id = %d and p.id = ol.product_id", $o_id));
		//$pu = $this->fetchSingle(sprintf("select * from custom_delivery where order_id = %d and service_type='P'", $orig_order["ref_transaction"]));
		$order = $this->fetchSingle(sprintf("select * from orders where id = %d", $orig_order["original_id"]));

		if ($order["custom_recurring_pu_time"]=="00:00:00") 
			$order["custom_recurring_pu_time"] = $this->fetchScalar(sprintf("select scheduled_date from custom_delivery where order_id = %d and service_type='P'",$orig_order["ref_transaction"]));

		$_SESSION["cart"]["header"] = array(
			"pickup_datetime" => date("Y-m-d H:i:s", strtotime(sprintf("%s", date("H:i:s",strtotime($order["custom_recurring_pu_time"]))))),
			"custom_weight_code"=>$order["custom_weight_code"],
			"custom_placed_by"=>$order["custom_placed_by"],
			"custom_dimension_code"=>$order["custom_dimension_code"],
			"custom_declared_value"=>$order["custom_declared_value"],
			"custom_reference_number"=>$order["custom_reference_number"],
			"custom_signature_required"=>$order["custom_signature_required"]
		);
		$_SESSION["quote"] = array(
			"custom_recurring_pu_driver"=>$order["custom_recurring_pu_driver"] > 0 ? $this->fetchScalar(sprintf("select d.id from drivers d, members m where m.id = d.member_id and m.deleted = 0 and m.enabled = 1 and d.deleted = 0 and d.enabled = 1 and d.id = %d",$order["custom_recurring_pu_driver"])) : 0,
			"custom_recurring_del_driver"=>$order["custom_recurring_del_driver"] > 0 ? $this->fetchScalar(sprintf("select d.id from drivers d, members m where m.id = d.member_id and m.deleted = 0 and m.enabled = 1 and d.deleted = 0 and d.enabled = 1 and d.id = %d",$order["custom_recurring_del_driver"])) : 0,
			"pickupInstructions"=>$this->fetchScalar(sprintf("select instructions from custom_delivery where order_id = %d and service_type='P'", $orig_order["ref_transaction"])),
			"deliveryInstructions"=>$this->fetchScalar(sprintf("select instructions from custom_delivery where order_id = %d and service_type='D'", $orig_order["ref_transaction"]))
		);
		$this->logMessage(__FUNCTION__,sprintf("session is now [%s]", print_r($_SESSION,true)),1);
	}

    /**
     * @param $orig_order
     * @param $o_id
     * @return void
     * @throws phpmailerException
     */
    function postRecurringOrder($orig_order, $o_id) {

		$this->recalcDowntown($o_id);
		$this->calc_driver_allocations($o_id);
		$this->calculateCommissions($o_id);

	}

}

?>