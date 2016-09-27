<?php
namespace Home;
class IndexController extends BaseController{
	public function index(){
		global $G,$lang;
		include template('index');
	}
}