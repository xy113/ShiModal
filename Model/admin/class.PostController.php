<?php
namespace Admin;
class PostController extends BaseController{
	public function index(){
		$this->item_list();
	}
	
	/**
	 * 文章列表
	 */
	public function item_list(){
		global $G,$lang;
		if ($this->checkFormSubmit()){
			$articleids = $_GET['id'];
			if (is_array($articleids) && !empty($articleids)) {
				$articleids = $articleids ? implode(',', $articleids) : 0;
				if($_GET['option'] == 'delete'){
					post_delete_item(array('id'=>array('IN', $articleids)));
					$this->showSuccess('delete_succeed');
				}
				
				if ($_GET['option'] == 'move'){
					$categoryoptions = post_get_category_options(0,0,1);
					include template('post_move');
					exit();
				}
				
				if ($_GET['option'] == 'audit') {
					post_update_item(array('id'=>array('IN', $articleids)), array('status'=>0));
					$this->showSuccess('update_succeed');
				}
				
				if ($_GET['option'] == 'unaudit'){
					post_update_item(array('id'=>array('IN', $articleids)), array('status'=>-1));
					$this->showSuccess('update_succeed');
				}
			}else{
				$this->showError('no_select');
			}
		}else {
						
			$condition = array();
			$catid     = intval($_GET['catid']);
			$keyword   = trim($_GET['keyword']);
			if ($catid) $condition['catid'] = array('IN',$catid);
			if ($keyword) $condition['title'] = array('LIKE',$keyword);
			
			$pagesize  = 20;
			$totalnum  = post_get_item_num($condition);
			$pagecount = $totalnum < $pagesize ? 1 : ceil($totalnum/$pagesize);
			$postlist = post_get_item_page($condition, min(array($G['page'], $pagecount)), $pagesize, 'id DESC');
			$pages = $this->showPages($G['page'], $pagecount, $totalnum,"catid=$catid&keyword=$keyword",1);
			
			$categorylist = post_get_category_list();
			$categoryoptions = post_get_category_options(0, $catid);
			if ($postlist) {
				$datalist = array();
				foreach ($postlist as $item){
					$item['type_name'] = $lang['post_types'][$item['type']];
					$item['status_name'] = $lang['post_status'][$item['status']];
					$item['cat_name'] = $categorylist[$item['catid']]['name'];
					$item['pubtime'] = @date('Y-m-d H:i', $item['pubtime']);
					$datalist[$item['id']] = $item;
				}
				$postlist = $datalist;
			}
			unset($datalist, $item);
			include template('post_list');
		}
	}
	
	/**
	 * 移动文章
	 */
	public function moveto(){
		if ($this->checkFormSubmit()){
			$articleids = trim($_GET['articleids']);
			$target = intval($_GET['target']);
			post_update_item(array('id'=>array('IN', $articleids)), array('catid'=>$target));
			$this->showSuccess('update_succeed', U('a=item_list&catid='.$target));
		}
	}
	
	/**
	 * 发布文章
	 */
	public function publish(){
		if ($this->checkFormSubmit()){
			$this->save();
		}else {
			global $G,$lang,$config;
			$catid = isset($_GET['catid']) ? intval($_GET['catid']) : 0;
			$categoryoptions = post_get_category_options(0, $catid,1);
			$type = in_array($_GET['type'], array('image','video')) ? $_GET['type'] : 'article';
			$article['from']    = setting('sitename');
			$article['fromurl'] = setting('siteurl');
			$article['author']  = $this->username;
			include template('post_form');
		}
	}
	
	/**
	 * 保存文章
	 */
	private function save(){
		global $G;
		
		$newpost = $_GET['newpost'];
		$content = $_GET['content'];
		if (is_array ($newpost)) {
			$newpost['uid'] = $this->uid;
			$newpost['pubtime']  = TIMESTAMP;
 			$newpost['modified'] = TIMESTAMP;
			$newpost['author']   = $newpost['author'] ? $newpost['author'] : $this->username;
			$newpost['from']     = isset($newpost['from']) ? $newpost['from'] : setting('sitename');
			$newpost['fromurl']  = isset($newpost['fromurl']) ? $newpost['fromurl'] : setting('siteurl');
			$newpost['tags'] = $newpost['tags'] ? $newpost['tags'] : '';
			$newpost['allowcomment'] = intval($newpost['allowcomment']);
			if (!$newpost['summary']) {
				$newpost['summary'] = cutstr(stripHtml($content), 300);
			}
			$newpost['summary'] = str_replace('&amp;', '&', $newpost['summary']);
			$newpost['summary'] = str_replace('&nbsp;', '', $newpost['summary']);
			$newpost['summary'] = str_replace('　', '', $newpost['summary']);
			$newpost['summary'] = preg_replace('/\s/', '', $newpost['summary']);
			
			$id = post_add_item($newpost);
			if ($newpost['type'] == 'article') {
				if ($content) {
					$contentlist = preg_split('/<hr class=\"ke-pagebreak\" style=\"page-break-after: always;\">/', $content);
					foreach ($contentlist as $key=>$value){
						$data = array(
								'aid'=>$id,
								'uid'=>$this->uid,
								'content'=>$value,
								'pageorder'=>$key+1
						);
						post_add_content($data);
					}
					post_update_item(array('id'=>$id), array('contents'=>count($contentlist)));
				}
			}
			
			if ($newpost['type'] == 'image'){
				$piclist = $_GET['piclist'];
				if ($piclist) {
					foreach ($piclist as $key=>$pic){
						$pic['dataid'] = $id;
						$pic['datatype'] = 'article';
						$pic['isremote'] = 0;
						image_add_data($pic);
					}
					if (!$newpost['image']) {
						$piclist = array_values($piclist);
						post_update_item(array('id'=>$id), array('image'=>$piclist[0]['image']));
					}
				}
			}
			
			if ($newpost['type'] == 'video'){
				$videourl = trim($_GET['videourl']);
				if ($videourl) {
					$videodata = \Core\ParseVideoUrl::ParseUrl ($videourl);
					if ($videodata) {
						media_add_data(array(
								'dataid'=>$id,
								'datatype'=>'article',
								'image'=>$videodata['img'],
								'source'=>$videodata['source'],
								'original_url'=>$videodata['url']
						));
						if (!$newpost['image']) post_update_item(array('id'=>$id), array('image'=>$videodata['img']));
					}
				}
			}
			
			$links = array (
					array (
							'text' => 'continue_publish',
							'url' => U('&a=publish&type='.$newpost['type'].'&catid='.$newpost['catid'])
					),
					array (
							'text'=>'view',
							'url'=>U('m=post&c=detail&id='.$id),
							'target'=>'_blank'
					),
					array(
							'text'=>'back_list',
							'url'=>U('a=item_list')
					)
			);
			$this->showSuccess('publish_succeed', null, $links, null,true);
		} else {
			$this->showError('undefined_error');
		}
	}
	
	/**
	 * 编辑文章
	 */
	public function edit(){
		if ($this->checkFormSubmit()){
			$this->update();
		}else {
			global $G,$lang;
			$id = intval($_GET['id']);
			$article = post_get_item(array('id'=>$id));
			if (in_array($article['type'], array('image','video'))){
				$type = $article['type'];
			}else {
				$type = 'article';
			}
			
			if ($article['type'] == 'article'){
				$content = post_get_content(array('aid'=>$id));
			}
			
			if ($article['type'] == 'image'){
				$piclist = image_get_list(array('dataid'=>$id, 'datatype'=>'article'), 0);
			}
			
			if ($article['type'] == 'video'){
				$videodata = media_get_data(array('dataid'=>$id, 'datatype'=>'article'));
			}
			$categoryoptions = post_get_category_options(0, $article['catid'],1);
			include template('post_form');
		}
	}
	
	/**
	 * 更新文章
	 */
	public function update(){
		global $G;

		$id = intval($_GET['id']);
		$newpost = $_GET['newpost'];
		$content = $_GET['content'];
		if (is_array($newpost)) {
			$newpost['modified'] = TIMESTAMP;
			$newpost['author']   = $newpost['author'] ? $newpost['author'] : $this->username;
			$newpost['from']     = isset($newpost['from']) ? $newpost['from'] : setting('sitename');
			$newpost['fromurl']  = isset($newpost['fromurl']) ? $newpost['fromurl'] : setting('siteurl');
			$newpost['tags'] = $newpost['tags'] ? $newpost['tags'] : '';
			$newpost['allowcomment'] = intval($newpost['allowcomment']);
			if (!$newpost['summary']) {
				$newpost['summary'] = cutstr(stripHtml($content), 300);
			}
			$newpost['summary'] = str_replace('&amp;', '&', $newpost['summary']);
			$newpost['summary'] = str_replace('&nbsp;', '', $newpost['summary']);
			$newpost['summary'] = str_replace('　', '', $newpost['summary']);
			$newpost['summary'] = preg_replace('/\s/', '', $newpost['summary']);
			//$newpost['summary'] = preg_replace('/[\n|\r]/', '', $newpost['summary']);
			
			post_update_item(array('id'=>$id), $newpost);
			if ($newpost['type'] == 'article') {
				if ($content) {
					post_delete_content(array('aid'=>$id));
					$contentlist = preg_split('/<hr class=\"ke-pagebreak\" style=\"page-break-after: always;\">/', $content);
					foreach ($contentlist as $key=>$value){
						$data = array(
								'aid'=>$id,
								'uid'=>$this->uid,
								'content'=>$value,
								'pageorder'=>$key+1
						);
						post_add_content($data);
					}
				}
			}
			
			if ($newpost['type'] == 'image'){
				image_delete_data(array('dataid'=>$id, 'datatype'=>'article'));
				$piclist = $_GET['piclist'];
				if ($piclist) {
					foreach ($piclist as $pic){
						$pic['dataid'] = $id;
						$pic['datatype'] = 'article';
						$pic['isremote'] = 0;
						image_add_data($pic);
					}
					if (!$newpost['image']) {
						$piclist = array_values($piclist);
						post_update_item(array('id'=>$id), array('image'=>$piclist[0]['image']));
					}
				}
			}
			
			if ($newpost['type'] == 'video'){
				$videourl = trim($_GET['videourl']);
				if ($videourl) {
					$videodata = \Core\ParseVideoUrl::ParseUrl ($videourl);
					if ($videodata) {
						media_update_data(
								array('dataid'=>$id, 'datatype'=>'article'), 
								array(
										'image'=>$videodata['img'],
										'source'=>$videodata['source'],
										'original_url'=>$videodata['url']
								)
						);
						if (!$newpost['image']) post_update_item(array('id'=>$id), array('image'=>$videodata['img']));
					}
				}
			}
		
			$links = array (
					array (
							'text' => 'reedit',
							'url' => U('a=edit&id='.$id)
					),
					array (
							'text' => 'view',
							'url' => U('m=post&c=detail&id=' . $id),
							'target' => '_blank'
					),
					array(
							'text'=>'back_list',
							'url'=>U('a=item_list')
					)
			);
			$this->showSuccess('modi_succeed',null, $links,null,true);
		} else {
			$this->showError('undefined_error');
		}
	}
	
	/**
	 * 设置文章图片
	 */
	public function setimage(){
		$id = intval($_GET['id']);
		$image = htmlspecialchars($_GET['image']);
		if ($id && $image){
			post_update_item(array('id'=>$id), array('image'=>$image));
			$this->showAjaxReturn(0);
		}else {
			$this->showAjaxError(100, 'INVALID PARAMETER');
		}
	}
}