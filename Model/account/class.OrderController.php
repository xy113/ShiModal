<?php
namespace Account;
class OrderController extends BaseController{
	public function index(){
		$this->item_list();
	}
	
	public function item_list(){
		global $G,$lang;
		
		include template('order_list');
	}
}