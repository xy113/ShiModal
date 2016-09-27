<?php
namespace Account;
class IndexController extends BaseController{
	public function index(){
		global $G,$lang;
		include template('index');
	}
}