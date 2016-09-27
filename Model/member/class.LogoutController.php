<?php
namespace Member;
use Core\Controller;
class LogoutController extends Controller{	
	public function index(){
		member_logout();
		$contiue = trim($_GET['continue']);
		$contiue = $contiue ? $contiue : $_SERVER['HTTP_REFERER'];
		if ($contiue !== curPageURL()){
			$this->redirect($contiue);
		}else {
			$this->redirect(U('m=member&c=login'));
		}
	}
}