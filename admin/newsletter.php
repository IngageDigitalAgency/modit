<?php

require_once("config.php");
require_once("classes/globals.php");
require_once("classes/PHPMailer/PHPMailerAutoload.php");
require_once("classes/common.php");
require_once("classes/smtp.php");
require_once("classes/Forms.php");
require_once("classes/HtmlElement.php");
require_once("classes/Snoopy.php");
//require_once("classes/wurfl.php");

class Newsletter extends Common {

	function __construct() {
		//error_reporting(E_ALL);
		parent::__construct(true,false);
		if (DEBUG == 0) {
			if ((int)$this->getConfigVar("debug") > 0) {
				$GLOBALS['globals'] = new Globals($this->getConfigVar("debug"),false);
			}
		}
	}

    /**
     * @return void
     * @throws phpmailerException
     */
    function process() {
		$this->logMessage('process','start processing',1,true,true);
echo "test";
		$safety = 0;
		while ($safety < 2 && $batches = $this->fetchSingle('select b.*, n.page_id, n.title, n.from_name, n.from_email, n.customized, n.teaser, n.description, c.search_title, 1 as isMailing from newsletter_batch b, newsletter n, content c where b.newsletter_id = n.id and c.id = n.page_id and started != "0000-00-00 00:00:00" and completed = "0000-00-00 00:00:00" and aborted = "0000-00-00 00:00:00" order by id')) {
			echo 'start processing'.PHP_EOL;
			$safety++;
			$this->logMessage('process',sprintf('starting batch #%d, newsletter %d',$batches['id'],$batches['newsletter_id']),1);
			$userList = $this->fetchScalarAll(sprintf('select subscribers_id from newsletter_by_subscribers where newsletter_id = %d',$batches['newsletter_id']));
			if (count($userList) == 0)
				$this->execute(sprintf('insert into newsletter_batch_subscriber(batch_id,subscriber_id,random_id) (select %d,id,cast(rand()*10000 as unsigned) from subscriber where (testing = 1 or %d = 0) and enabled = 1 and deleted = 0 and id not in (select subscriber_id from newsletter_batch_subscriber where batch_id = %d))',$batches['id'],$batches['testing'],$batches['id']));
			else
				$this->execute(sprintf('insert into newsletter_batch_subscriber(batch_id,subscriber_id,random_id) (select %d,id,cast(rand()*10000 as unsigned) from subscriber where (testing = 1 or %d = 0) and enabled = 1 and deleted = 0 and id not in (select subscriber_id from newsletter_batch_subscriber where batch_id = %d) and id in (select subscriber_id from subscriber_by_folder where folder_id in (%s)))',$batches['id'],$batches['testing'],$batches['id'],implode(',',$userList)));
			$page = $this->fetchSingle(sprintf('select * from content where id = %d',$batches['page_id']));
			$link = sprintf('http://%s%s',HOSTNAME,$this->getUrl('menu',$page['id'],$page));
			$this->logMessage('process',sprintf('fetching page [%s]',$link),2);
			$snoopy = new Snoopy();
			$snoopy->fetch($link);
			$this->logMessage('process',sprintf('snoopy return is [%s]',print_r($snoopy,true)),3);
			if ($snoopy->status == 200) {
				$stmt = $this->prepare(sprintf('update newsletter set html = ? where id = %d',$batches['newsletter_id']));
				$stmt->bindParams(array('s',$snoopy->results));
				$stmt->execute();
				$killIt = false;
				$mailer = new MyMailer();	
				$mailer->isHTML();
				$mailer->Subject = sprintf('%s%s',$batches['title'],$batches['testing'] > 0 ? ' ** TEST**':'');
				$mailer->From= $batches['from_email'];
				$mailer->FromName = $batches['from_name'];
				$form = new Forms();
				$form->setOption('formDelimiter','{{|}}');
				$text = $snoopy->results;
				echo 'get group'.PHP_EOL;
				while ((!$killIt) && $list = $this->fetchAll(sprintf('select b.*, s.firstname, s.lastname, s.email from newsletter_batch_subscriber b, subscriber s where batch_id = %d and sent = "0000-00-00 00:00:00" and s.id = b.subscriber_id order by random_id limit 50',$batches['id']))) {
					$this->logMessage('process',sprintf('next batch count = %d',count($list)),2);
					foreach($list as $subscriber) {
						if ($batches['customized']) {
							//
							//	build the page for every email - enables random items on the page
							//
							$snoopy->fetch($link);
							echo 'refetching page'.PHP_EOL;
							if ($snoopy->status == 200)
								$text = $snoopy->results;
						}
						$text = str_replace('<!--{{','{{',$text);
						$text = str_replace('}}-->','}}',$text);
						$subscriber['tracker'] = sprintf('<img class="nlv" src="http://%s/nlv/%d/%d/%d" />',HOSTNAME,$subscriber['batch_id'],$subscriber['subscriber_id'],$subscriber['random_id']);
						$subscriber['unsubscribe'] = sprintf('http://%s/nlu/%d/%d/%d',HOSTNAME,$subscriber['batch_id'],$subscriber['subscriber_id'],$subscriber['random_id']);
						$subscriber['url'] = $link;
						$form->setHtml($text);
						$form->addData($batches);
						$form->addData($subscriber);
						$mailer->Body = $form->show();
						$mailer->clearAddresses();
						$mailer->AddAddress($subscriber['email'],sprintf('%s %s',$subscriber['firstname'],$subscriber['lastname']));
						echo 'send to '.$subscriber['email'].PHP_EOL;
						$status = true;
						if ($status = $mailer->Send()) {
							$this->logMessage('process',sprintf('mailed to %s %s <%s>',$subscriber['firstname'],$subscriber['lastname'],$subscriber['email']),3,true);
						}
						else {
							$this->logMessage('process',sprintf('mail failed to %s %s <%s> mailer [%s]',$subscriber['firstname'],$subscriber['lastname'],$subscriber['email'], print_r($mailer,true)),3,true,true);
						}
						$this->execute(sprintf('update newsletter_batch_subscriber set sent=now(), success=%d where id=%s',$status?1:0,$subscriber['id']));
						$aborted = $this->fetchScalar(sprintf('select aborted from newsletter_batch where id = %d',$batches['id']));
						$killIt = $aborted != '0000-00-00 00:00:00';
						if ($killIt) {
							$this->logMessage('process','killing job - aborted from gui',1);
							break;
						}
					}
					echo 'next batch wait'.PHP_EOL;
					sleep(60);
				}
			}
			else 
				$this->logMessage('process',sprintf('Newsletter failed to retreive the requested page [%s]',print_r($snoopy,true)),1,true);
			$this->execute(sprintf('update newsletter_batch set completed=now() where id = %d',$batches['id']));
		}
		$this->logMessage('process','finished processing',1);
	}
}

date_default_timezone_set(TZ);
setlocale(LC_MONETARY,CURRENCY);
$_SESSION = array();
$nl = new Newsletter();
$nl->process();

?>
