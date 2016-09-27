<?php
namespace Photo;
class AlbumController extends BaseController{
	public function index(){
		
	}
	
	/**
	 * 创建相册
	 */
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