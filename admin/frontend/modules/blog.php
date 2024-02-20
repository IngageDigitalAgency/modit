<?php

/**
 * @param $a
 * @param $b
 * @return int
 */
function blog_keyword_sort($a, $b) {
	if (strlen($a) == strlen($b)) return 0;
	return strlen($a) < strlen($b) ? -1 : 1;
}

/**
 * Blog feature in the frontend section
 */
class blog extends Frontend {

	private $m_dir = '';
	protected $module;
	private $m_blog_id;

    /**
     * @param $id
     * @param $module
     * @throws phpmailerException
     */
    public function __construct($id, $module = array()) {
		parent::__construct();
		$this->m_dir = ADMIN.'frontend/forms/blog/';
		$this->m_moduleId = $id;
		$this->m_module = $module;
		$this->m_blog_id = array_key_exists('blog_id',$_REQUEST) ? $_REQUEST['blog_id'] : 0;
		$this->logMessage("__construct",sprintf("($id,[%s])",print_r($module,true)),2);
	}

    /**
     * @param $module
     * @param $addLimit
     * @return string
     * @throws phpmailerException
     */
    private function buildSql($module, $addLimit = false) {
		if ($module['folder_id'] > 0) {
			if ($module['include_subfolders'] != 0) {
				$sql = sprintf('select n.id from blog_folders n, blog_folders n1 where n1.id = %d and n.left_id >= n1.left_id and n.right_id <= n1.right_id and n.enabled = 1',$module['folder_id']);
				$tmp = $this->fetchScalarAllTest($sql);
				$this->logMessage('buildSql',sprintf('sub folder sql [%s]',$sql),3);
				$sql = sprintf("select n.*, j.sequence, f.left_id, j.folder_id from blog n, blog_by_folder j, blog_folders f where f.id = j.folder_id and deleted = 0 and n.enabled = 1 and published = 1 and (expires = '0000-00-00' or expires >= curdate()) and n.id = j.blog_id and j.folder_id in (%s)",implode(',',$tmp));
			}
			else
				$sql = sprintf("select n.*, j.sequence, 0 as left_id, folder_id from blog n, blog_by_folder j, blog_folders f where deleted = 0 and n.enabled = 1 and published = 1 and (expires = '0000-00-00' or expires >= curdate()) and j.folder_id = %d and n.id = j.blog_id and f.id = j.folder_id",$module['folder_id']);
		}
		else
			$sql = "select n.*, 0 as sequence, 0 as left_id, 0 as folder_id from blog n where deleted = 0 and n.enabled = 1 and published = 1 and (expires = '0000-00-00' or expires >= curdate())";
		if ($module['featured'])
			$sql .= " and featured = 1 ";
		if (array_key_exists('search_group',$module) && $module['search_group'] > 0) {
			$sql .= sprintf(" and n.id in (select blog_id from blog_by_search_group where folder_id = %d)",$module['search_group']);
		}
		if (array_key_exists('blog_list',$module) && count($module['blog_list']) > 0) {
			$sql .= sprintf(" and n.id in (%s)",implode(',',$module['blog_list']));
		}

		if ($this->hasOption("unique")) {
			$pos = strpos($sql,'from');
			$tmp = sprintf('select min(concat(left_id,"|",n.id)) %s group by n.id',substr($sql,$pos-1));
			$test = $this->fetchScalarAllTest($tmp);
			$this->logMessage(__FUNCTION__,sprintf('unique code produced [%s] with [%s] pos [%s] sql [%s]',print_r($test,true),$tmp,$pos,$sql),2);
			$sql .= sprintf(' and concat(left_id,"|",n.id) in ("%s")',implode('","', $test));
		}

		if (strlen($module['where_clause']) > 0)
			$sql .= " and ".$module['where_clause'];
		if (strlen($module['sort_by']) > 0)
			$sql .= " order by ".$module['sort_by'];
		else
			$sql .= " order by sequence";
		if ($addLimit) {
			if (strlen($module['records']) > 0) {
				$tmp = explode(',',$module['records']);
				if (count($tmp) > 1)
					$total = $tmp[0]*$tmp[1];
				else
					$total = $tmp[0];
				$sql .= " limit ".$total;
			}
		}
		return $sql;
	}

    /**
     * @param $data
     * @param $folder
     * @return mixed
     * @throws phpmailerException
     */
    function formatData($data, $folder = array()) {
		$tmp = new image();
		if (array_key_exists('image1',$data) && $data['image1'] != '') {
			$tmp->addAttributes(array('src'=>$data['image1'],'alt'=>htmlentities(strip_tags($data['title']))));
			$data['img_image1'] = $tmp->show();
		}
		if (array_key_exists('image2',$data) && $data['image2'] != '') {
			$tmp->addAttributes(array('src'=>$data['image2'],'alt'=>htmlentities(strip_tags($data['title']))));
			$data['img_image2'] = $tmp->show();
		}
		if (array_key_exists('id',$folder) && $folder['id'] > 0) {
			if (!array_key_exists('folder_id',$data))
				$data['folder_id'] = $folder['id'];
			elseif ($data['folder_id'] == 0)
				$data['folder_id'] = $folder['id'];
		}
		$data['url'] = $this->getUrl('blog',$data['id'],$data);
		if ($video = $this->fetchSingleTest('select * from videos where owner_type="blog" and owner_id = %d', $data['id']))
			$data['video'] = $this->formatVideo($video);
		$tmp = $this->fetchSingleTest('select sum(rating) as rating, count(0) as ct from blog_comments where approved = 1 and blog_id = %d group by blog_id',$data['id']);
		$data['comment_count'] = (int)$tmp['ct'];
		if ($data['comment_count']>0) $data['rating'] = (int)(2*$tmp['rating']/$data['comment_count']);
		$data['comment_plural'] = $data['comment_count'] == 1 ? '':'s';
		$data['age'] = $this->age($data['created']);
		$data["author"] = $this->fetchSingleTest("select * from users where id = %d",$data["author"]);
		$this->logMessage(__FUNCTION__,sprintf('data [%s] folder [%s]',print_r($data,true),print_r($folder,true)),3);
		return $data;
	}

    /**
     * @param $data
     * @return mixed
     * @throws phpmailerException
     */
    function formatFolder($data) {
		$tmp = new image();
		if (array_key_exists('image',$data) && $data['image'] != '') {
			$tmp->addAttributes(array('src'=>$data['image'],'alt'=>htmlentities(strip_tags($data['title']))));
			$data['img_image'] = $tmp->show();
		}
		if (array_key_exists('rollover_image',$data) && $data['rollover_image'] != '') {
			$tmp->addAttributes(array('src'=>$data['rollover_image'],'alt'=>htmlentities(strip_tags($data['title']))));
			$data['img_rollover_image'] = $tmp->show();
		}
		$data['url'] = $this->getUrl('blogcat',$data['id'],$data);
		$data["blog_count"] = $this->fetchScalarTest("select count(0) from blog b, blog_by_folder bf where bf.folder_id = %d and b.id = bf.blog_id and b.enabled = 1 and b.published = 1 and b.deleted = 0 and (b.expires = '0000-00-00' or b.expires > curdate())",$data["id"]);
		$data['active'] = array_key_exists("blogcat",$_REQUEST) && $_REQUEST["blogcat"] == $data["id"] ? "active" : "";
		$this->logMessage(__FUNCTION__,sprintf('return [%s]',print_r($data,true)),2);
		return $data;
	}

    /**
     * @param $data
     * @return mixed
     */
    function formatComment($data) {
		$data['formattedCreated'] = date(GLOBAL_DEFAULT_DATETIME_FORMAT,strtotime($data['created']));
		return $data;
	}

    /**
     * @param $data
     * @return mixed
     */
    public function formatVideo($data) {
		return $data;
	}

    /**
     * @return array|mixed|string|string[]
     * @throws phpmailerException
     */
    function details() {
		if (!$module = $this->getModule())
			return "";
		$outer = new Forms();
		$outer->init($this->m_dir.$module['outer_html']);
		if (!$rec = $this->fetchSingleTest('select * from blog where id = %d',$this->m_blog_id)) {
			$this->addError("We couldn't locate that entry");
			return $outer->show();
		}
		if ($module['folder_id'] > 0) {
			$folder = $this->fetchSingleTest('select * from blog_folders where id = %d',$module['folder_id']);
		}
		else {
			$folder = $this->fetchSingleTest('select f.* from blog_folders f, blog_by_folder bf where bf.blog_id = %d and f.id = bf.folder_id order by random() limit 1',$rec['id']);
		}
		$outer->addData($this->formatFolder($folder));
		$inner = new Forms();
		$inner->init($this->m_dir.$module['inner_html']);
		$flds = $inner->buildForm($this->config->getFields($module['configuration']));
		$inner->addData($this->formatData($rec,$folder));
		$subdata = $this->subForms($this->m_module['fetemplate_id'],null,array('folder_id'=>$folder['id']),'inner');
		$this->logMessage(__FUNCTION__,sprintf('subforms [%s]',print_r($subdata,true)),3);
		foreach($subdata as $key=>$value) {
			$inner->addTag($key,$value,false);
		}
		$outer->addTag('entries',$inner->show(),false);
		$subdata = $this->subForms($this->m_module['fetemplate_id'],null,array('folder_id'=>$folder['id']),'outer');
		$this->logMessage(__FUNCTION__,sprintf('subforms [%s]',print_r($subdata,true)),3);
		foreach($subdata as $key=>$value) {
			$outer->addTag($key,$value,false);
		}
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
		$sql = $this->buildSql($module);
		if ($module['folder_id'] != 0)
			$root = $this->fetchSingleTest('select * from blog_folders where id = %d',$module['folder_id']);
		else
			$root = array('left_id'=>0,'right_id'=>999999,'level'=>0,'id'=>0);
		if ($this->hasOption('typeSelect')) {
			$folders = $this->fetchAllTest('select * from blog_folders where left_id > %d and right_id < %d and level > %d order by left_id',$root['left_id'],$root['right_id'],$root['level']);
			$form = new Forms();
			$form->init($this->m_dir.$module['inner_html']);
			$result = array();
			foreach($folders as $key=>$folder) {
				$form->addData($this->formatFolder($folder));
				$form->addTag('selected',array_key_exists('pf_id',$_REQUEST) && $folder['id'] == $_REQUEST['pf_id'] ? 'selected':'');
				$result[] = $form->show();
			}
			$menu = implode("",$result);
		}
		else {
			$menu = sprintf('<ul class="level_0 %s" %s>%s</ul>',
				$this->hasOption('ul_class') ? $this->getOption('ul_class'):'',
				$this->hasOption('ul_id') ? sprintf('id="%s"',$this->getOption('ul_id')):'',
				$this->buildUL($root,$module,0));
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
		$level = $this->fetchAllTest('select * from blog_folders where level = %d and left_id > %d and right_id < %d and enabled = 1 order by left_id',$root['level']+1,$root['left_id'],$root['right_id']);
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
			$ct = $this->fetchScalarTest("select count(0) from blog b, blog_by_folder bf where bf.folder_id = %d and b.id = bf.blog_id and b.enabled = 1 and b.published = 1 and (expires = '0000-00-00' or expires >= curdate())",$item['id']);
			$form->addTag('count',$ct);
			$tmp = $form->show();
			if (($subMenu = $this->buildUL($item,$module,$root_level+1)) != '')
				$tmp .= sprintf('<ul class="level_%d submenu">%s</ul>',$root_level+1,$subMenu);
			if ($this->hasOption('delim') && $seq < count($level)) $tmp .= sprintf('<span class="delim">%s</span>',$this->getOption('delim'));
			$menu[] = sprintf('<li class="sequence_%d">%s</li>',$seq,$tmp);
		}
		$this->logMessage(__FUNCTION__,sprintf("return [%s]",print_r($menu,true)),3);
		return implode("",$menu);
	}

    /**
     * @return array|mixed|string|string[]
     * @throws phpmailerException
     */
    function entries() {
		if (!$module = parent::getModule())
			return "";
		$this->parseOptions($module['misc_options']);
		$this->logMessage(__FUNCTION__,sprintf("module [%s]",print_r($module,true)),2);
		$sql = $this->buildSql($module,false);
		$pagination = $this->getPagination($sql,$module,$recordcount);
		$records = $this->fetchAllTest($sql);
		$this->logMessage(__FUNCTION__,sprintf("sql [%s] count [%d]",$sql,count($records)),2);
		$entries = array();
		$frm = new Forms();
		$frm->init($this->m_dir.$module['inner_html']);
		$ct = 0;
		$ids = array(0);
		if ($this->hasOption('grpPrefix')) $entries[] = $this->getOption('grpPrefix');
		foreach($records as $rec) {
			$frm->reset();
			$ct += 1;
			if ($module['rows'] > 0 && $ct > $module['columns']) {
				$ct = 1;
				if ($this->hasOption('grpSuffix')) $ads[] = $this->getOption('grpSuffix');
				if ($this->hasOption('grpPrefix')) 
					$entries[] = $this->getOption('grpPrefix');
				else
					$entries[] = '<div class="clearfix"></div>';
			}
			$frm->reset();
			if ($module['folder_id'] != 0)
				$fldr = $this->fetchSingleTest('select * from blog_folders where id = %d',$module['folder_id']);
			else {
				$sql = sprintf('select * from blog_folders where id in (select folder_id from blog_by_folder where blog_id =%d order by rand()) limit 1',$rec['id']);
				$fldr = $this->fetchSingleTest($sql);
			}
			$tmp = $this->formatData($rec,$fldr);
			$frm->addData($tmp);
			$frm->addTag('sequence',$ct);
			$subdata = $this->subForms($this->m_module['fetemplate_id'],null,array('folder_id'=>$module['folder_id'],'blog_id'=>$rec['id']),'inner');
			$this->logMessage(__FUNCTION__,sprintf('subforms [%s]',print_r($subdata,true)),3);
			foreach($subdata as $key=>$value) {
				$frm->addTag($key,$value,false);
			}
			$entries[] = $frm->show();
			$ids[] = $rec['id'];
		}
		if ($this->hasOption('grpSuffix')) $entries[] = $this->getOption('grpSuffix');
		$outer = new Forms();
		if ($module['folder_id'] != 0) {
			if ($fldr = $this->fetchSingleTest('select * from blog_folders where id = %d',$module['folder_id']))
				$outer->addData($this->formatFolder($fldr));
		}
		else {
			if ($fldr = $this->fetchSingleTest('select * from blog_folders where id in (select folder_id from blog_by_folder where blog_id in (%s)) order by rand() limit 1',implode(',',$ids)))
				$outer->addData($this->formatFolder($fldr));
		}
		$subdata = $this->subForms($this->m_module['fetemplate_id'],null,array(),'outer');
		$this->logMessage(__FUNCTION__,sprintf('subforms [%s]',print_r($subdata,true)),3);
		foreach($subdata as $key=>$value) {
			$outer->addTag($key,$value,false);
		}
		$outer->init($this->m_dir.$module['outer_html']);
		$outer->addTag('entries',implode('',$entries),false);
		$outer->addTag('pagination',$pagination,false);
		$tmp = $outer->show();
		return $tmp;
	}

    /**
     * @return array|mixed|string|string[]
     * @throws phpmailerException
     */
    function comments() {
		if (!$module = $this->getModule())
			return "";
		$outer = new Forms();
		$outer->init($this->m_dir.$module['outer_html']);
		if (array_key_exists('blog_list',$module) && count($module['blog_list']) > 0)
			$sql = sprintf('select bc.*, m.firstname, m.lastname, m.email from blog_comments bc, members m where bc.blog_id in (%s) and bc.approved = 1 and m.id = bc.author_id order by bc.created desc', implode(',',$module['blog_list']));
		else
			$sql = sprintf('select bc.*, m.firstname, m.lastname, m.email from blog_comments bc, members m where bc.blog_id = %d and bc.approved = 1 and m.id = bc.author_id order by bc.created desc',$this->m_blog_id);
		$pagination = $this->getPagination($sql,$module,$recordcount);
		$outer->addTag('comment_count',$recordcount);
		$outer->addTag('comment_plural',$recordcount == 1 ? '' : 's');
		$records = $this->fetchAllTest($sql);
		$this->logMessage(__FUNCTION__,sprintf('[%s] found [%d] records',$sql,count($records)),2);
		$inner = new Forms();
		$flds = $inner->buildForm($this->config->getFields($module['configuration']));
		$inner->init($this->m_dir.$module['inner_html']);
		$comments = array();
		foreach($records as $key=>$rec) {
			$inner->reset();
			$inner->addData($this->formatComment($rec));
			$comments[] = $inner->show();
		}
		if (array_key_exists('blog_list',$module) && count($module['blog_list']) > 0) {
			if ($blog = $this->fetchSingleTest('select * from blog where published = 1 and enabled = 1 and id in (%s) order by rand() limit 1',implode(",",$module['blog_list'])))
				$outer->addData($this->formatData($blog));
		}
		else
			if ($blog = $this->fetchSingleTest('select * from blog where published = 1 and enabled = 1 and id = %d',$this->m_blog_id))
				$outer->addData($this->formatData($blog));
		$outer->addTag('comments',implode('',$comments),false);
		$outer->addTag('pagination',$pagination,false);
		$subdata = $this->subForms($this->m_module['fetemplate_id'],null,array(),'outer');
		$this->logMessage(__FUNCTION__,sprintf('subforms [%s]',print_r($subdata,true)),3);
		foreach($subdata as $key=>$value) {
			$outer->addTag($key,$value,false);
		}
		return $outer->show();
	}

    /**
     * @return array|mixed|string|string[]
     * @throws phpmailerException
     */
    function addComment() {
		if (!$module = $this->getModule())
			return "";
		$outer = new Forms();
		$outer->init($this->m_dir.$module['outer_html']);
		$inner = new Forms();
		$inner->init($this->m_dir.$module['inner_html'],array('name'=>'blogCommentInner'));
		$flds = $inner->buildForm($this->config->getFields($module['configuration']));
		if ($this->isUserLoggedIn()) {
			$inner->addData($_SESSION['user']['info']);
		}
		if (count($_POST) > 0 && array_key_exists('addComment',$_POST)) {
			$inner->addData($_POST);
			$valid = $inner->validate();
			if ($valid) {
				if ($inner->getData('r_id') != $_SESSION['forms'][$inner->getOption('name')]['r_secret']) {
					$valid = false;
					$this->addError('An internal error occurred');
					$this->logMessage(__FUNCTION__,sprintf('possible spam [%s]',print_r($_POST,true)),1,true);
				}
			}
			//
			//	stop reposts from a refresh
			//
			$inner->setData('r_secret',random_int(1, 1000000));
			$_SESSION['forms'][$inner->getOption('name')]['r_secret'] = $inner->getData('r_secret');
			if ($valid) {
				if (!$this->isUserLoggedIn()) {
					if (!$member = $this->fetchSingleTest('select * from members where email = "%s" and deleted = 0',$inner->getData('email'))) {
						$tmp = array(
							'email'=>$inner->getData('email'),
							'firstname'=>$inner->getData('firstname'),
							'lastname'=>$inner->getData('lastname'),
							'deleted'=>0,
							'enabled'=>1,
							'created'=>date(DATE_ATOM)
						);
						$stmt = $this->prepare(sprintf('insert into members(%s) values(%s?)',implode(',',array_keys($tmp)),str_repeat('?, ',count($tmp)-1)));
						$stmt->bindParams(array_merge(array(str_repeat('s',count($tmp))),array_values($tmp)));
						$stmt->execute();
						$memberId = $this->insertId();
					}
					else $memberId = $member['id'];
				}
				else $memberId = $this->getUserInfo('id');
				$tmp = array(
					'blog_id'=>$this->m_blog_id,
					'created'=>date(DATE_ATOM),
					'author_id'=>$memberId,
					'post_parms'=>print_r($_SERVER,true),
					'rand'=>random_int(0,1000000)
				);
				foreach($flds as $key=>$value) {
					if(!(array_key_exists('database',$value) && $value['database'] == false)) {
						$tmp[$key] = $inner->getData($key);
					}
				}
				$tmp['content'] = sprintf("<p>%s</p>",nl2br(htmlentities($tmp['content'])));
				$stmt = $this->prepare(sprintf('insert into blog_comments(%s) values(%s?)',implode(',',array_keys($tmp)),str_repeat('?, ',count($tmp)-1)));
				$stmt->bindParams(array_merge(array(str_repeat('s',count($tmp))),array_values($tmp)));
				if ($valid = $stmt->execute()) {
					$c_id = $this->insertId();
					if (strlen($module['parm1']) > 0) $inner->init($this->m_dir.$module['parm1']);
					$inner->addFormSuccess('Your Comment has been accepted for approval');
					$this->logMessage(__FUNCTION__,sprintf('checking this for options [%s]',print_r($this,true)),1);
					if ($this->hasOption('email')) {
						$emails = $this->configEmails("blog");
						if (count($emails) == 0) $emails = $this->configEmails("contact");
						$body = new Forms();
						$mailer = new MyMailer();
						$mailer->Subject = sprintf("Blog Comment - %s", SITENAME);
						$body = new Forms();
						$sql = sprintf('select * from htmlForms where class = %d and type = "%s"',$this->getClassId('blog'),$this->getOption('email'));
						$html = $this->fetchSingleTest($sql);
						$body->setHTML($html['html']);
						$blog = $this->fetchSingleTest('select * from blog where id = %d',$this->m_blog_id);
						$body->addData($blog);
						$body->addData($inner->getAllData());
						$body->addData($tmp);
						$body->setData('comment',nl2br($inner->getData('comment')));
						$body->addTag('c_id',$c_id);
						$body->setOption('formDelimiter','{{|}}');
						$mailer->Body = $body->show();
						$mailer->From = 'noreply@'.HOSTNAME;	//$inner->getData('email');
						$mailer->FromName = $inner->getData('firstname').' '.$inner->getData('lastname');
						$mailer->AddReplyTo($inner->getData('email'),$inner->getData('firstname').' '.$inner->getData('lastname'));
						$mailer->IsHTML(true);
						foreach($emails as $key=>$value) {
							$mailer->addAddress($value['email'],$value['name']);
						}
						if (!$mailer->Send()) {
							$this->logMessage(__FUNCTION__,sprintf("Email send failed [%s]",print_r($mailer,true)),1,true);
						}
					}
				}
				else {
					$this->addError('An Error Occurred');
					$inner->setData('r_id',$_SESSION['forms'][$inner->getOption('name')]['r_secret']);
				}
			}
			else {
				$inner->addFormError('An Error Occurred');
				$inner->setData('r_id',$_SESSION['forms'][$inner->getOption('name')]['r_secret']);
			}
		}
		else {
			//
			//	every non-post change the random id
			//
			$_SESSION['forms'][$inner->getOption('name')]['r_secret'] = $flds['r_id']['value'];
		}
		$inner->addTag("blog_id",$this->m_blog_id);
		$outer->addTag('form',$inner->show(),false);
		$subdata = $this->subForms($this->m_module['fetemplate_id'],null,array(),'outer');
		$this->logMessage(__FUNCTION__,sprintf('subforms [%s]',print_r($subdata,true)),3);
		foreach($subdata as $key=>$value) {
			$outer->addTag($key,$value,false);
		}
		return $outer->show();
	}

    /**
     * @return array|mixed|string|string[]
     * @throws phpmailerException
     */
    function approveComment() {
		if (!$module = $this->getModule())
			return "";
		$outer = new Forms();
		$outer->init($this->m_dir.$module['outer_html']);
		$inner = new Forms();
		$inner->init($this->m_dir.$module['inner_html'],array('name'=>'blogCommentInner'));
		$flds = $inner->buildForm($this->config->getFields($module['configuration']));
		if (count($_REQUEST) > 0 && array_key_exists('c_approval',$_REQUEST) && array_key_exists('rand',$_REQUEST)) {
			$this->execute(sprintf('update blog_comments set approved = 1 where id = %d and rand = %d', $_REQUEST['c_id'], $_REQUEST['rand']));
			$this->addMessage('The comment has been approved');
		}
		$outer->addTag('result',$inner->show(),false);
		return $outer->show();
	}

    /**
     * @return string|void
     * @throws phpmailerException
     */
    function itemRelations() {
		if (!$module = $this->getModule())
			return "";
		$this->logMessage("itemRelations",sprintf("module [%s]",print_r($module,true)),2);
		if (!$this->hasOption('templateId')) {
			$this->logMessage('itemRelations',sprintf('attempt to implement cross class functionality with no config [%s] this [%s] request [%s]',print_r($module,true),print_r($this,true),print_r($_REQUEST,true)),1,true);
			return "";
		}
		$fn = $this->fetchSingleTest('select t.id as fetemplate_id, t.module_function, m.classname from fetemplates t, modules m where t.id = %d and m.id = t.module_id',$this->getOption('templateId'));
		$tmp = $this->m_module;
		$tmp['classname'] = $fn['classname'];
		$tmp['fetemplate_id'] = $fn['fetemplate_id'];
		$tmp['module_function'] = $fn['module_function'];
		switch($fn['classname']) {
			case 'calendar':
				$module['id'] = $this->getOption('templateId');
				$html = $this->calendar($fn);
				break;
			case 'members':
				$module['id'] = $this->getOption('templateId');
				$html = $this->members($fn);
				break;
			case 'gallery':
				$module['id'] = $this->getOption('templateId');
				$html = $this->gallery($fn);
				break;
			case 'coupons':
				$module['id'] = $this->getOption('templateId');
				$html = $this->coupons($fn);
				break;
			case 'news':
				$module['id'] = $this->getOption('templateId');
				$html = $this->news($fn);
				break;
			case 'product':
				$module['id'] = $this->getOption('templateId');
				$html = $this->product($fn);
				break;
			default:
				$html = '';
		}
		return $html;
	}

    /**
     * @param $module
     * @return string|void
     * @throws phpmailerException
     */
    private function product($module) {
		if (array_key_exists('blog_id',$this->m_module))
			$p_id = $this->m_module['blog_id'];
		else
			$p_id = $this->m_blog_id;
		if ($folders = $this->fetchSingleTest('select * from relations where owner_type = "blogfolder" and owner_id = %d and related_type = "product"',$p_id)) {
			$module['folder_id'] = $folders['owner_id'];
		}
		else {
			$folders = array();
			$module['folder_id'] = 0;
			$this->logMessage(__FUNCTION__,sprintf('no blog folder for product %d',$p_id),2);
		}
		if ($items = $this->fetchScalarAllTest('select related_id as product_id from relations where owner_type = "blog" and owner_id = %d and related_type = "product"',$p_id))
			$module['product_list'] = $items;
		else {
			$items = array();
			$this->logMessage(__FUNCTION__,sprintf('no product for blog %d',$p_id),2);
		}
		if (count($folders) == 0 && count($items) == 0) {
			$this->logMessage(__FUNCTION__,sprintf('bail - no items or folders for product %d',$p_id),1);
			return "";
		}
		$obj = new product($module['fetemplate_id'],$module);
		if (method_exists('product',$module['module_function'])) {
			$html = $obj->{$module['module_function']}();
			return $html;
		}
		else {
			$this->logMessage(__FUNCTION__,sprintf('bail - no function [%s] in product for blog %d',$module['module_function'],$p_id),1,true);
		}
	}

    /**
     * @return array|mixed|string|string[]
     * @throws phpmailerException
     */
    function memberOf() {
		if (!$module = $this->getModule())
			return "";
		$outer = new Forms();
		$outer->init($this->m_dir.$module['outer_html']);
		if (!$rec = $this->fetchSingleTest('select * from blog where id = %d',$this->m_blog_id)) {
			return "";
		}
		if ($module["folder_id"] > 0) {
			if ($module["include_subfolders"] != 0) {
				$f = $this->fetchScalarAllTest("select n.id from blog_folders n, blog_folders n1 where n1.id = %d and n.left_id >= n1.left_id and n.right_id <= n1.right_id",$module["folder_id"]);
			}
			else {
				$f = array($module["folder_id"]);
			}
			$folders = $this->fetchAllTest("select f.* from blog_folders f, blog_by_folder bf where bf.blog_id = %d and f.id = bf.folder_id and f.enabled = 1 and bf.folder_id in (%s) order by sequence",$this->m_blog_id,implode(",",$f));
		}
		else
			$folders = $this->fetchAllTest("select f.* from blog_folders f, blog_by_folder bf where bf.blog_id = %d and f.id = bf.folder_id and f.enabled = 1 order by sequence",$this->m_blog_id);
		$inner = new Forms();
		$inner->init($this->m_dir.$module['inner_html']);
		$flds = $inner->buildForm($this->config->getFields($module['configuration']));
		$return = array();
		foreach($folders as $key=>$value) {
			$inner->addData($this->formatFolder($value));
			$subdata = $this->subForms($this->m_module['fetemplate_id'],null,array('folder_id'=>$value['id']),'inner');
			$this->logMessage(__FUNCTION__,sprintf('subforms [%s]',print_r($subdata,true)),3);
			foreach($subdata as $key=>$value) {
				$inner->addTag($key,$value,false);
			}
			$return[] = $inner->show();
		}
		$outer->addTag('entries',implode("",$return),false);
		$subdata = $this->subForms($this->m_module['fetemplate_id'],null,array(),'outer');
		$this->logMessage(__FUNCTION__,sprintf('subforms [%s]',print_r($subdata,true)),3);
		foreach($subdata as $key=>$value) {
			$outer->addTag($key,$value,false);
		}
		return $outer->show();
	}

    /**
     * @return array|mixed|string|string[]
     * @throws phpmailerException
     */
    function search() {
		if (!$module = $this->getModule())
			return "";
		$outer = new Forms();
		$outer->init($this->m_dir.$module['outer_html']);
		$flds = $this->config->getFields($module['configuration']);

		$inner = new Forms();
		$inner->init($this->m_dir.$module['inner_html']);

		$flds = $outer->buildForm($flds);

		if (array_key_exists('blogSearchForm',$_REQUEST) && array_key_exists('blogSearchText',$_REQUEST) && strlen($module['inner_html']) > 0) {
			$outer->addData($_REQUEST);
			$this->logMessage(__FUNCTION__,"validating",1);
			$status = $outer->validate();
			if ($status) {
				$sql = $this->buildSql($module,false);
				$tmp = $this->fetchAllTest($sql);
				$ids = array();
				foreach($tmp as $key=>$value) {
					$ids[] = $value["id"];
				}
				$searchTerms = $_REQUEST['blogSearchText'];
				$searchPhrase = explode(' ',$searchTerms);
				usort($searchPhrase,"blog_keyword_sort");
				$weighted = array();
				for($x = 0; $x < count($searchPhrase); $x++) {
						$blogs = $this->fetchAllTest('select b.* from blog b where b.id in (%s) and (b.title like "%%%s%%" or b.teaser like "%%%s%%" or b.body like "%%%s%%") and b.enabled = 1 and b.published = 1 and b.deleted = 0',
							implode(",",$ids),$searchPhrase[$x],$searchPhrase[$x],$searchPhrase[$x]);
					foreach($blogs as $key=>$value) {
						$t = strip_tags($value["teaser"]);
						$b = strip_tags($value["body"]);
						if (stripos($value["title"],$searchPhrase[$x]) == false && stripos($t,$searchPhrase[$x]) == false && stripos($b,$searchPhrase[$x]) == false) {
							$this->logMessage(__FUNCTION__,sprintf("dropping blog [%d] after stripping", $value["id"]),1);
						}
						else
							$weighted[$value["id"]] = array_key_exists($value["id"],$weighted) ? $weighted[$value["id"]] + $x+1 : $x+1;
					}
				}
				arsort($weighted);
				$this->logMessage(__FUNCTION__,sprintf("weighted blogs [%s]",print_r($weighted,true)),2);
				$pg = array_key_exists("pagenum",$_REQUEST) ? $_REQUEST["pagenum"] : 1;
				$items = array_splice(array_keys($weighted),($pg-1)*$module["records"],$module["records"]);
				$sql = sprintf("select id from blog where id in (%s)",implode(",",array_merge(array(0),array_keys($weighted))));
				$pagination = $this->getPagination($sql,$module,$recordcount);
				$outer->addTag("pagination",$pagination,false);
				$this->logMessage(__FUNCTION__,sprintf("sliced array [%s]",print_r($items,true)),2);
				$outer->addTag('count',count($weighted));
				$outer->addTag('plural',count($weighted) == 1 ? '' : 's');
				$posts = array();
				foreach($items as $key) {
					$inner->reset();
					$blog = $this->fetchSingleTest("select * from blog where id = %d",$key);
					$inner->addData($this->formatData($blog));
					$posts[] = $inner->show();
				}
				$outer->addTag("entries",implode("",$posts),false);
			}
		}
		else {
			$outer->addTag('count',0);
			$outer->addTag('plural','s');
		}
		if (array_key_exists('blogSearchForm',$_REQUEST) && array_key_exists('blogSearchText',$_REQUEST)) {
			$outer->addData($_REQUEST);
		}
		return $outer->show();
	}

    /**
     * @return false|string
     * @throws phpmailerException
     */
    function getModuleInfo() {
		return parent::getModuleList(array('details','listing','entries','comments','addComment','approveComment','itemRelations','memberOf','search'));
	}

}

?>
