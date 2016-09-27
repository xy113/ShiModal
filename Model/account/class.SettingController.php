<?php
namespace Account;
class SettingController extends BaseController{
	public function index(){
		$this->userinfo();
	}
	
	/**
	 * 用户基本信息
	 */
	public function userinfo(){
		global $G,$lang;
		
		$userinfo = member_get_data(array('uid'=>$this->uid));
		if ($userinfo['mobile']) {
			$userinfo['mobile'] = substr($userinfo['mobile'], 0, 3).'****'.substr($userinfo['mobile'], -4, strlen($userinfo['mobile']));
		}
		
		if ($userinfo['email']) {
			$userinfo['email'] = substr($userinfo['email'], 0, 3).'****'.substr($userinfo['email'], strrpos($userinfo['email'], '@'));
		}
		$G['title'] = $lang['title_userinfo'];
		include template('setting_userinfo');
	}
	
	/**
	 * 绑定手机号
	 */
	public function bindmobile(){
		$mobile = htmlspecialchars($_GET['mobile']);
		$captchacode = trim($_GET['captchacode']);
		
		$this->checkCaptchacode($captchacode, 1);
		if (!$this->checkFormSubmit()) {
			$this->showAjaxError(301, L('undefined_action'));
		}
		
		if (ismobile($mobile)) {
			if (member_get_num(array('mobile'=>$mobile))){
				$this->showAjaxError(102, L('mobile_be_occupied'));
			}else {
				member_update_data(array('uid'=>$this->uid), array('mobile'=>$mobile));
				member_update_cookie($this->uid);
				$this->showAjaxReturn(0);
			}
		}else {
			$this->showAjaxError(101, L('mobile_incorrect'));
		}
	}
	
	/**
	 * 绑定邮箱
	 */
	public function bindemail(){
		$email = htmlspecialchars($_GET['email']);
		$captchacode = trim($_GET['captchacode']);
		
		$this->checkCaptchacode($captchacode, 1);
		if (!$this->checkFormSubmit()) {
			$this->showAjaxError(301, L('undefined_action'));
		}
		
		if (isemail($email)) {
			if (member_get_num(array('email'=>$email))){
				$this->showAjaxError(202, L('email_be_occupied'));
			}else {
				member_update_data(array('uid'=>$this->uid), array('email'=>$email));
				member_update_cookie($this->uid);
				$this->showAjaxReturn(0);
			}
		}else {
			$this->showAjaxError(201, L('email_incorrect'));
		}
	}
	
	/**
	 * 更新密码
	 */
	public function password(){
		$password = trim($_GET['password']);
		$newpassword = trim($_GET['newpassword']);
		$captchacode = trim($_GET['captchacode']);
		
		$this->checkCaptchacode($captchacode, 1);
		if (!$this->checkFormSubmit()) {
			$this->showAjaxError(301, L('undefined_action'));
		}
		
		$userinfo = member_get_data(array('uid'=>$this->uid));
		if ($userinfo['password'] != member_encrypt_password($password)){
			$this->showAjaxError(302, L('password_incorrect'));
		}else {
			member_update_data(array('uid'=>$this->uid), array('password'=>member_encrypt_password($newpassword)));
			$this->showAjaxReturn(0);
		}
	}
	
	/**
	 * 修改资料
	 */
	public function modiinfo(){
		global $G,$lang;
		if ($this->checkFormSubmit()) {
			$userinfo = $_GET['userinfo'];
			if ($userinfo && is_array($userinfo)) {
				$userinfo['modified'] = TIMESTAMP;
				member_update_info(array('uid'=>$this->uid), $userinfo);
				$this->showAjaxReturn(0);
			}else {
				$this->showAjaxError(101, L('invalid_parameter'));
			}
		}else {
			$userinfo = member_get_info(array('uid'=>$this->uid));
			
			$G['title'] = $lang['title_modiinfo'];
			include template('setting_modiinfo');
		}
	}
	
	/**
	 * 上传头像
	 */
	public function uploadavatar(){
		if ($filedata = photo_upload_data()){
		
			$src = C('ATTACHDIR').$filedata['image'];
			$avatardir    = C('AVATARDIR').$this->uid;
			$avatarsmall  = $this->uid.'_avatar_small.jpg';
			$avatarmiddle = $this->uid.'_avatar_middle.jpg';
			$avatarbig    = $this->uid.'_avatar_big.jpg';
				
			@mkdir($avatardir,0777,true);
			$image = new \Core\Image($src);
			if ($image->width() > $image->height()){
				$x = ($image->width() - $image->height())/2;
				$y = 0;
				$w = $h = $image->height();
			}else {
				$x = 0;
				$y = ($image->height() - $image->width())/2;
				$w = $h = $image->width();
			}
			$image->crop($w, $h,$x,$y,320,320);
			$image->save($avatardir.'/'.$avatarbig);
			$image->thumb(160, 160);
			$image->save($avatardir.'/'.$avatarmiddle);
			$image->thumb(100, 100);
			$image->save($avatardir.'/'.$avatarsmall);
			member_update_data(array('uid'=>$this->uid), array('avatarstatus'=>1));
			$this->showAjaxReturn(array('avatar'=>avatar($this->uid)));
		}
	}
	
	public function security(){
		global $G,$lang;
		
		$G['title'] = $lang['title_security'];
		include template('setting_security');
	}
	
	public function address(){
		global $G,$lang;
		
		$addresslist = address_get_list(array('uid'=>$this->uid));
		
		$G['title'] = $lang['title_address'];
		include template('setting_address');
	}
	
	/**
	 * 添加收货地址
	 */
	public function saveaddress(){
		$address = $_GET['address'];
		$id = intval($_GET['id']);
		if ($id) {
			address_update_data(array('uid'=>$this->uid,'id'=>$id), $address);
			$this->showAjaxReturn(0);
		}else {
			$address['uid'] = $this->uid;
			address_add_data($address);
			$this->showAjaxReturn(0);
		}
	}
	
	/**
	 * 删除收货地址
	 */
	public function removeaddress(){
		$id = intval($_GET['id']);
		address_delete_data(array('uid'=>$this->uid, 'id'=>$id));
		$this->showAjaxReturn(0);
	}
	
	/**
	 * 获取地址信息
	 */
	public function getaddress(){
		$id = intval($_GET['id']);
		$address = address_get_data(array('uid'=>$this->uid, 'id'=>$id));
		if ($address) {
			$this->showAjaxReturn($address);
		}else {
			$this->showAjaxError(101);
		}
	}
}