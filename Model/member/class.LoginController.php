<?php
namespace Member;
use Core\Controller;
class LoginController extends Controller{	
	public function index(){
		if ($this->uid && $this->username) $this->redirect('/');
		if ($this->checkFormSubmit()){
			$this->chklogin();
		}else {
			member_show_login();
		}
	}
	
	/**
	 *AJAX获取登录状态
	 */
	public function getstatus(){
		if ($this->uid && $this->username){
			$this->showAjaxReturn(array('status'=>'LOGGED'));
		}else {
			$this->showAjaxError(100, 'UNLOGGED');
		}
	}
	
	/**
	 * 登录验证
	 */
	private function chklogin(){
		$account  = htmlspecialchars(trim($_GET['account_'.FORMHASH]));
		$password = trim($_GET['password_'.FORMHASH]);
		$captchacode = trim($_GET['captchacode']);
		$this->checkCaptchacode($captchacode, G('inajax'));
		
		if (isemail($account)){
			$returns = member_login($account, $password, 'email');
		}elseif (ismobile($account)){
			$returns = member_login($account, $password, 'mobile');
		}else {
			$returns = member_login($account, $password, 'username');
		}

		if ($returns['errno'] == 0 && $returns['userinfo']){
			if (G('inajax')) {
				$this->showAjaxReturn('SUCCESS');
			}else {
				$continue = $_GET['continue'] ? $_GET['continue'] : $_SERVER['HTTP_REFERER'];
				if ($continue !== curPageURL()){
					$this->redirect($continue);
				}else {
					$this->redirect(U(array('m'=>'account')));
				}
			}
		}else {	
			if (G('inajax')) {
				$this->showAjaxError($returns['errno'], L($returns['error']));
			}else {
				$this->showError($returns['error']);
			}
		}
	}
	
	public function ajaxlogin(){
		member_show_ajax_login();
	}
}