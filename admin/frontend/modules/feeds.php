<?php

require_once(ADMIN."classes/Facebook/FB.php");

class feeds extends Frontend {

	public function __construct($id,$module = array()) {
		parent::__construct();
		$this->m_dir = ADMIN.'frontend/forms/feeds/';
		$this->m_moduleId = $id;
		$this->m_module = $module;
		$this->logMessage("__construct",sprintf("($id,[%s])",print_r($module,true)),2);
	}

	private function checkFeed($feed,$delay = 30) {
		$this->logMessage("checkFeed","($feed,$delay)",2);
		$sql = sprintf("select * from feeds where source = '%s' order by created desc limit 1",$feed);
		if ($rec = $this->fetchSingle($sql)) {
			$dt = $rec['created'];
			$age = strtotime(date('r')) - strtotime($dt);
			$this->logMessage('checkFeed',sprintf('time check created [%s] delay [%s] age [%s]',$rec['created'],$delay,$age),1);
			if ($age > $delay*60) {
				$this->getFeed($feed);
			}
		}
		else
			$this->getFeed($feed);
	}

	private function getFeed($feed) {
		$this->logMessage("getFeed","($feed)",2);
		$s = new Snoopy();
		$s->fetch($feed);
		$this->logMessage("getFeed","snoopy [".print_r($s,true)."]",3);
		if ($s) {
			$txt = "";
			foreach($s->headers as $hdr) {
				$txt .= $hdr;
			}
			$stmt = $this->prepare(sprintf("insert into feeds(source, value, headers, created) values(?,?,?,?)"));
			$stmt->bindParams(array('ssss',$feed,$s->results,$txt,date(DATE_ATOM)));
			$stmt->execute();
			//
			//	delete feeds more than 24 hrs old
			//
			$sql = sprintf("DELETE FROM `feeds` WHERE source = '%s' and created < now() - 3600*24",$feed);
			$this->execute($sql);
		}
		else
			$this->logMessage("RSS Feed failed",sprintf('%s - results [%s]',$feed,print_r($s,true)),1,true);
	}

	private function parseFeed($data) {
		//$this->logMessage('parseFeed',sprintf('input [%s]',print_r($data,true)),3);
		$return = array();
		if (is_array($data) && strlen($data["value"]) > 0)  {
			if ($root = simplexml_load_string($data["value"])) {
				$items = get_object_vars($root);
				$items = $root->xpath('/rss/channel/item');
				$this->logMessage('parseFeed',sprintf('root [%s] items [%s]',print_r($root,true),print_r($items,true)),3);
				$i = 0;
				foreach($items as $item) {
					$tmp = array();
					$this->logMessage('parseFeed',sprintf('item [%s]',print_r($item,true)),3);
					foreach($item as $key=>$value) {
						//$this->logMessage('parseFeed',sprintf('item key [%s] value [%s] (string)[%s]',print_r($key,true),print_r($value,true),print_r((string)$value,true)),3);
						$tmp[$key] = (string)$value[0];
					}
					if (!$this->hasOption('noHyperlinks'))
						$tmp["description"] = $this->hyperlink($tmp['description'],$item->guid);
					if (!is_null($item->pubDate))
						$tmp['age'] = $this->age($item->pubDate);
					$this->logMessage('parseFeed',sprintf('item [%s]',print_r($tmp,true)),3);
					$return[] = $tmp;
				}
			}
		}
		$this->logMessage('parseFeed',sprintf('return [%s]',print_r($return,true)),3);
		return $return;
	}

	private function hyperlink($data,$guid) {
		if (preg_match("((mailto\:|(news|(ht|f)tp(s?))\://){1}\S+)",$data,$results))
			$data = str_replace($results[0],"<a target='_blank' href='".$results[0]."'>".$results[0]."</a>",$data);
		return $data;
	}
	
	function showFeed() {
		if (!$module = parent::getModule())
			return "";	
		$outer = new Forms();
		$outer->init($this->m_dir.$module['outer_html']);
		$feed = $this->getConfigVar($module['parm1']);
		if ($module['parm2'] > 0)
			$this->checkFeed($feed,$module['parm2']);
		else
			$this->checkFeed($feed);
		$return = array();
		$sql = sprintf('select * from feeds where source = "%s" and value like "<?xml version=\"1.0\"%%" order by created desc limit 1',$this->getConfigVar($module['parm1']));
		if ($rec = $this->fetchSingle($sql)) {
			$data = $this->parseFeed($rec);
			$x = 0;
			$inner = new Forms();
			$inner->init($this->m_dir.$module['inner_html']);
			foreach($data as $key=>$value) {
				if ($x < $module['limit']) {
					if ($this->hasOption('customParse')) {
						$value = $this->config->{$this->getOption('customParse')}($value);
					}
					$inner->addData($value);
					$return[] = $inner->show();
				}
				$x += 1;
			}
			$outer->addTag('data',implode('',$return),false);
			return $outer->show();
		} else {
			$this->logMessage('showFeed',sprintf('fetch failed [%s]',$sql),2);
		}
		return "";
	}

	function facebookPhotos() {
		if (!$module = parent::getModule())
			return "";	
		$outer = new Forms();
		$outer->init($this->m_dir.$module['outer_html']);
		$albumId = $this->getOption('fbAlbum');
		$appId = $this->getOption('fbAppId');
		$secret = $this->getOption('fbSecret');
		$userPage = $this->getOption('fbPageId');
		$return = "";
		$status = false;
		try {
			$fb = new FB(array('appId'=>$appId,'secret'=>$secret));
			$info = $fb->getAlbums($userPage);
			$inner = new Forms();
			$inner->init($this->m_dir.$module['inner_html']);
			$results = array();
			foreach($info['data'] as $key=>$album) {
				$this->logMessage('facebookPhotos',sprintf('album info [%s]',print_r($album,true)),1);
				if ($album['id'] == $albumId) {
					$photos = $fb->getPhotos($album['id']);
					$this->logMessage('facebookPhotos',sprintf('raw photos [%s]',print_r($photos,true)),1);
					foreach($photos['data'] as $subkey=>$photo) {
						$inner->reset();
						$inner->addTag('image',$photo['source']);
						$inner->addTag('img',sprintf('<img src="%s" />',$photo['source']),false);
						$this->logMessage('facebookPhotos',sprintf('inner form [%s] photo [%s]',print_r($inner,true),print_r($photo,true)),1);
						$results[] = $inner->show();
					}
					$this->logMessage('facebookPhotos',sprintf('inner results [%s]',implode('',$results)),1);
					$outer->addTag('photos',implode('',$results),false);
					$outer->addTag('name',$album['name']);
					$outer->addTag('description',$album['description']);
					$status = true;
				}
			}
			$return = $outer->show();
			$status = true;
		}
		catch(Exception $err) {
			$this->logMessage('facebookPhotos',sprintf('An error occurred: [%s]', print_r($err,true)),1,true);
		}
		if ($this->isAjax())
			return $this->ajaxResult(array('status'=>$status,'html'=>$return));
		else return $return;
	}

	function facebookPosts() {
		if (!$module = parent::getModule())
			return "";
		$outer = new Forms();
		$outer->init($this->m_dir.$module['outer_html']);
		//$appId = $this->getOption('fbAppId');
		//$secret = $this->getOption('fbSecret');
		//$userPage = $this->getOption('fbPageId');
		$opts = $GLOBALS['facebook'];
		$appId = $opts['appId'];
		$secret = $opts['secret'];
		$userPage = array_key_exists("pageId",$opts) ? $opts['pageId'] : $opts["user_id"];
		$return = "";
		$status = false;
		$outer->addData($module);
		try {
			$info = $this->fetchSingle(sprintf("select * from feeds where source='%s' order by created desc limit 1",$module["parm1"]));
			if (!$info || date("Y-m-d H:i:s",strtotime(sprintf("%s + %d minutes",$info["created"],$module["parm2"]))) < date("Y-m-d H:i:s")) {
				$fb = new FB(array('appId'=>$appId,'secret'=>$secret));
				if ($this->hasOption("posts"))
					$info = $fb->getPosts($userPage, $this->hasOption("fields") ? explode(",",$this->getOption("fields")) : array());
				else
					$info = $fb->getFeed($userPage, $this->hasOption("fields") ? explode(",",$this->getOption("fields")) : array());
				if (!is_array($info)) {
					$this->logMessage(__FUNCTION__,sprintf("get failed [%s]", print_r($info,true)),1, true);
					$info = array();
					$info["data"] = json_decode($this->fetchScalar(sprintf("select value from feeds where source='%s' order by created desc limit 1",$module["parm1"])),true);
				}
				else {
					$s = $this->prepare("insert into feeds(source,created,value) values(?,now(),?)");
					$s->bindParams(array("ss",$module["parm1"],json_encode($info["data"])));
					$s->execute();
				}
			}
			else $info["data"] = json_decode($info["value"],true);
			$inner = new Forms();
			$inner->init($this->m_dir.$module['inner_html']);
			$results = array();
			$ct = 0;
			if (is_array($info)) {
				if ($this->hasOption("filter")) {
					foreach($info["data"] as $key=>$value) {
						if (!array_key_exists($this->getOption("filter"),$value)) unset($info["data"][$key]);
					}
				}
				$slice = array_slice($info['data'],0,$module['records']);
				$this->logMessage('fbPosts',sprintf('info after slice [%s] slice [%s]',print_r($info,true),print_r($slice,true)),3);
				foreach($slice as $key=>$value) {
					$ct += 1;
					if ($module['rows'] > 0 && $ct > $module['columns']) {
						$ct = 1;
						$news[] = '<div class="clearfix"></div>';
					}
					$this->logMessage('facebookPosts',sprintf('info [%s]',print_r($value,true)),4);
					$inner->reset();
					if (array_key_exists('message',$value))
						$value['message_formatted'] = nl2br($value['message']);
					if (array_key_exists('actions',$value)) {
						foreach($value['actions'] as $key1=>$value1) {
							$value[sprintf('action_%s_link',$value1['name'])] = $value1['link'];
						}
					}
					$value['age'] = $this->age(array_key_exists("updated_time",$value) ? $value["updated_time"] : $value["created_time"]);
					$inner->addData($value);
					$results[] = $inner->show();
				}
				$outer->addTag('posts',implode('',$results),false);
			}
			$status = true;
			$return = $outer->show();
			$status = true;
		}
		catch(Exception $err) {
			$this->logMessage('facebookPosts',sprintf('An error occurred: [%s]', print_r($err,true)),1,true);
		}
		if ($this->isAjax())
			return $this->ajaxResult(array('status'=>$status,'html'=>$return));
		else return $return;
	}

	function tweets() {
		if (!$module = parent::getModule())
			return "";
		$outer = new Forms();
		$outer->init($this->m_dir.$module['outer_html']);
		$params = $GLOBALS['twitter'];
		$options = $this->depairOptions($module['parm1']);
		$this->checkTweets($module);
		$return = array();
		$sql = sprintf('select * from feeds where source = "%s" order by created desc limit 1',$options['screen_name']);
		if ($rec = $this->fetchSingle($sql)) {
			$data = json_decode($rec['value'],true);
			if (array_key_exists("error",$data)) {
				$this->logMessage(__FUNCTION__,sprintf("twitter error found [%s] from [%s]",print_r($data,true),print_r($rec,true)),1,true);
			}
			$x = 0;
			$inner = new Forms();
			$inner->init($this->m_dir.$module['inner_html']);
			foreach($data as $key=>$value) {
				if ($x < $module['limit']) {
					if ($this->hasOption('customParse')) {
						$value = $this->config->{$this->getOption('customParse')}($value);
					}
					$value['age'] = $this->age($value['created_at']);
					$this->logMessage(__FUNCTION__,sprintf("tweet [%s]", print_r($value,true)),3);
					$inner->addData($this->formatTweet($value));
					$return[] = $inner->show();
				}
				$x += 1;
			}
			$outer->addTag('tweets',implode('',$return),false);
			return $outer->show();
		} else {
			$this->logMessage('showFeed',sprintf('fetch failed [%s]',$sql),2);
		}
		return "";
	}

	private function checkTweets($module) {
		$parms = $this->depairOptions($module['parm1']);
		$feed = $parms['screen_name'];
		$delay = (int)$module['parm2'] > 0 ? (int)$module['parm2'] : 30;
		$sql = sprintf("select * from feeds where source = '%s' order by created desc limit 1",$feed);
		if ($rec = $this->fetchSingle($sql)) {
			$dt = $rec['created'];
			$age = strtotime(date('r')) - strtotime($dt);
			$this->logMessage('checkFeed',sprintf('time check created [%s] delay [%s] age [%s]',$rec['created'],$delay,$age),1);
			if ($age > $delay*60) {
				$this->getTweets($module);
			}
		}
		else
			$this->getTweets($module);
	}

	function getTweets($module) {
		require_once(sprintf('./%s/classes/Twitter/twitteroauth.php',ADMIN));
		if (!array_key_exists('twitter',$GLOBALS)) {
			$this->logMessage(__FUNCTION__,sprintf('attempt to get tweets with no configuration'),1,true);
			return;
		}
		$params = $GLOBALS['twitter']['settings'];
		$options = $this->depairOptions($module['parm1']);
		$this->logMessage(__FUNCTION__,sprintf('options [%s]',print_r($options,true)),1);
		$connection = new TwitterOAuth($params['consumer_key'], $params['consumer_secret'], $params['oauth_access_token'], $params['oauth_access_token_secret']);
		$content = $connection->get('account/verify_credentials');
		$status = (array)$connection;
		if ($status['http_code'] != 200) {
			$this->resetTweetTiming($module,$params);
			$this->logMessage(__FUNCTION__,sprintf('twitter authorization failed [%s]',print_r($status,true)),1,true);
			return;
		}
		$connection->decode_json = false;
		$feed = $connection->get('statuses/user_timeline', $options);
		$r = json_decode($feed,true);
		$this->logMessage(__FUNCTION__,sprintf('returned feed is [%s] [%s]',$feed,print_r($r,true)),1);
		if (array_key_exists("errors",$r) && $r["errors"][0]["code"] != 0) {
			$this->resetTweetTiming($module,$params);
			$this->logMessage(__FUNCTION__,sprintf("found errors [%s] from [%s]",print_r($r,true),print_r($module,true)),1,true);
			return;
		}
		$stmt = $this->prepare(sprintf("insert into feeds(source, value, headers, created) values(?,?,?,?)"));
		$stmt->bindParams(array('ssss',$options['screen_name'],$feed,'',date(DATE_ATOM)));
		$stmt->execute();
		//
		//	delete feeds more than x days old
		//
		if ($module['parm3'] > 0) {
			$sql = sprintf("DELETE FROM `feeds` WHERE source = '%s' and created < now() - 3600*24*%d",$options['screen_name'],$module['parm3']);
			$this->execute($sql);
		}
		return;
	}

	function resetTweetTiming($module,$params) {
		$options = $this->depairOptions($module['parm1']);
		$delay = (int)$module['parm2'] > 0 ? (int)$module['parm2'] : 30;
		$this->execute(sprintf("update feeds set created = date_add(now(),interval %d minute) where source = '%s' order by created desc limit 1", $delay, $options["screen_name"]));
		$this->logMessage(__FUNCTION__,sprintf("reset next fetch for feed [%s]", $options["screen_name"]),1,true);
	}

	function formatTweet($data) {
		$this->logMessage(__FUNCTION__,sprintf('incoming [%s]',print_r($data,true)),4);
		$data['hyperlinked_text'] = $data['text'];
		if (array_key_exists('urls',$data['entities'])) {
			foreach($data['entities']['urls'] as $subkey=>$link) {
				$data['hyperlinked_text'] = str_replace($link['url'],sprintf('<a href="%s" target="new">%s</a>',$link['expanded_url'],$link['url']),$data['hyperlinked_text']);
			}
		}
		if (array_key_exists('media',$data['entities'])) {
			foreach($data['entities']['media'] as $subkey=>$link) {
				$data['hyperlinked_text'] = str_replace($link['url'],sprintf('<a href="%s" target="new">%s</a>',$link['expanded_url'],$link['url']),$data['hyperlinked_text']);
			}
		}
		if (array_key_exists('hashtags',$data['entities'])) {
			foreach($data['entities']['hashtags'] as $subkey=>$link) {
				$data['hyperlinked_text'] = str_replace($link['text'],sprintf('<a href="https://twitter.com/search?q=%%23%s&src=hash" target="new">%s</a>',$link['text'],$link['text']),$data['hyperlinked_text']);
			}
		}
		
		$this->logMessage(__FUNCTION__,sprintf('outgoing [%s]',print_r($data,true)),3);
		return $data;
	}

	function instagram() {
		if (!$module = parent::getModule())
			return "";	
		$outer = new Forms();
		$outer->init($this->m_dir.$module['outer_html']);
		$inner = new Forms();
		$inner->init($this->m_dir.$module['inner_html']);
		$parms = $GLOBALS['instagram'];
		$feed = $this->checkInstagramFeed($module,$parms);
		if (!is_array($feed)) {
			$this->logMessage(__FUNCTION__,sprintf("bail - no feed found"),1);
			return "";
		}
		$data = json_decode($feed['value'],true);
		$this->logMessage(__FUNCTION__,sprintf("data [%s] rec [%s]",print_r($data,true),print_r($feed,true)),1);
		$recs = array();
		$x = 0;
		if (is_array($data["data"])) {
			foreach($data['data'] as $key=>$value) {
				if ($x < $module['limit']) {
					if ($this->hasOption('customParse')) {
						$value = $this->config->{$this->getOption('customParse')}($value);
					}
					$inner->addData($this->formatInstagram($value));
					$recs[] = $inner->show();
				}
				$x += 1;
			}
		}
		$outer->addTag('posts',implode('',$recs),false);
		return $outer->show();
	}

	private function checkInstagramFeed($module,$parms) {
		$sql = sprintf("select * from feeds where source = '%s' order by created desc limit 1",$this->getOption("feedname"));
		if ($rec = $this->fetchSingle($sql)) {
			$dt = $rec['created'];
			$age = strtotime(date('r')) - strtotime($dt);
			$delay = $this->hasOption("delay") ? $this->getOption("delay") : 60;
			$this->logMessage(__FUNCTION__,sprintf('time check created [%s] delay [%s] age [%s]',$rec['created'],$delay,$age),1);
			if ($age > $delay*60) {
				$this->getInstagram($this->getOption("feedname"),$module,$parms);
			}
		}
		else
			$this->getInstagram($this->getOption("feedname"),$module,$parms);
		return $this->fetchSingle(sprintf("select * from feeds where source='%s' order by created desc limit 1",$this->getOption("feedname")));
	}

	private function getInstagram($feed, $module, $parms) {
		$host = new Forms();
		$host->setHTML($parms[$this->getOption("post_type")."_url"]);
		$host->addData($module);
		$host->addData($parms);
		$signature = hash_hmac('sha256',$parms['source_ip'],$parms['consumer_secret'],false);
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $host->show());
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_TIMEOUT, 10);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array(
			"Accept: image/gif, image/x-xbitmap, image/jpeg, image/pjpeg, */*",
			sprintf("X-Insta-Forwarded-For: %s|%s",$parms['source_ip'],$signature)));
		$result = curl_exec($ch);
		curl_close($ch);
		if ($result == "") {
			$this->logMessage(__FUNCTION__,sprintf("get failed for parms [%s]",print_r($parms,true)),1,true);
			return;
		}
		$r = json_decode($result,true);
		if (is_null($r)) {
			$this->logMessage(__FUNCTION__,sprintf("decode failed [%s] for parms [%s]",$result,print_r($parms,true)),1,true);
			return;
		}
		$this->logMessage(__FUNCTION__,sprintf("curl result is [%s]",print_r($r,true)),3);
		if (array_key_exists("meta",$r) && array_key_exists("code",$r["meta"]) && $r["meta"]["code"] != 200) {
			$this->logMessage(__FUNCTION__,sprintf("authorization failed [%s] for parms [%s]",print_r($r,true),print_r($parms,true)),1,true);
			return;
		}
		$stmt = $this->prepare(sprintf("insert into feeds(source, value, created, headers) values(?,?,?,?)"));
		$stmt->bindParams(array('ssss',$feed,$result,date(DATE_ATOM),''));
		$stmt->execute();
		return;
	}

	function formatInstagram($data) {
		$data['age'] = $this->age(date("Y-m-d h:i:s",$data['created_time']));
		$this->logMessage(__FUNCTION__,sprintf("post [%s]", print_r($data,true)),3);
		return $data;
	}

	function getModuleInfo() {
		return parent::getModuleList(array('facebookPhotos','facebookPosts','tweets','instagram'));
	}

}

?>