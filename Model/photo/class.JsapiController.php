<?php
namespace Photo;
class JsapiController extends BaseController{
	function __construct(){
		parent::__construct();
		if (G('a') == 'uploadimage' && $_GET['from'] == 'swfupload'){
			$uid = intval($_GET['uid']);
			$username = trim($_GET['username']);
			$token = sha1($uid.$username.formhash());
			if ($uid && $username && $token === $_GET['token']){
				$this->uid = $uid;
				$this->username = $username;
				G('uid', $this->uid);
				G('username', $this->username);
			}
		}
		if (!$this->checkLogin()){
			$this->showAjaxReturn(100, 'LOGIN EXPIRED');			
		}
	}
	
	public function index(){
		
	}
	
	/**
	 * 上传图片
	 */
	public function uploadimage(){
		$albumid = intval($_GET['albumid']);
		if ($filedata = photo_upload_data()){
			if ($albumid) photo_update_data(array('photoid'=>$filedata['photoid']), array('albumid'=>$albumid));
			$this->showAjaxReturn($filedata);
		}else {
			$this->showAjaxError(101, 'UPLOAD FAILED');
		}
	}
	
	public function pickimage(){
		global $G,$lang;
	
		$albumid = intval($_GET['albumid']);
		$condition = array('uid'=>$this->uid);
		if ($albumid) $condition['albumid'] = $albumid;
		
		$pagesize  = 20;
		$totalnum  = photo_get_num($condition);
		$pagecount = $totalnum < $pagesize ? 1 : ceil($totalnum/$pagesize);
		$photolist = photo_get_page($condition, $G['page'], $pagesize);
		$pages = $this->showPages($G['page'], $pagecount, $totalnum, "albumid=$albumid&inajax=1");

		$albumlist = album_get_list(array('uid'=>$this->uid));
		if (!$albumlist) {
			$album = album_add_data(array('uid'=>$this->uid,'title'=>$lang['default_album'], 'dateline'=>TIMESTAMP), true);
			$albumlist = array($album);
			unset($album);
		}
		include template('ajax_pickimage');
	}
	
	public function create(){
		global $G,$lang;
		if ($this->checkFormSubmit()){
			$title = htmlspecialchars($_GET['title']);
			$isopen = intval($_GET['isopen']);
			$password = trim($_GET['password']);
			if(!$isopen){
				$password = sha1(md5($password));
			}
			if ($title) {
				$alnum = album_add_data(array(
						'uid'=>$this->uid,
						'title'=>$title,
						'isopen'=>$isopen,
						'password'=>$password,
						'dateline'=>TIMESTAMP
				), 1);
				$this->showAjaxReturn($alnum);
			}else {
				$this->showAjaxError(100, 'PARAMETER INCORRECT');
			}
		}else {
			include template('ajax_create_album');
		}
	}
}