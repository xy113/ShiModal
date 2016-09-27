<?php
namespace Member;
class RegisterController extends BaseController{
	function __construct(){
		parent::__construct();
		if ($this->uid) $this->redirect('/?m=home');
	}
	
	public function index(){
		if ($this->checkFormSubmit()){
			$this->save();
		}else {
			member_show_register();
		}
	}
	
	/**
	 * 保存注册信息
	 */
	function save(){
		$username = htmlspecialchars(trim($_GET['username_'.FORMHASH]));
		$password = trim($_GET['password_'.FORMHASH]);
		$email    = trim($_GET['email_'.FORMHASH]);
		$mobile   = trim($_GET['mobile_'.FORMHASH]);
		$captchacode = trim($_GET['captchacode']);
		$this->checkCaptchacode($captchacode);
		
		$data = array(
				'username'=>$username,
				'password'=>$password,
				'email'=>$email,
				'mobile'=>$mobile
		);
		
		$returns = member_register($data, 1);
		if ($returns['errno'] == 0 && $returns['userinfo']) {
			$this->showSuccess('register_success', U('m=account'), array(), '', true);
		}else {
			$this->showError($returns['error']);
		}
	}
}