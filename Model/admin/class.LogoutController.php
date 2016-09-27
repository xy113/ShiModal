<?php
namespace Admin;
class LogoutController extends BaseController{
	public function index(){
		member_logout();
		cookie('adminlogined',null);
		$this->redirect($_SERVER['HTTP_REFERER']);
	}
}