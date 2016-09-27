<?php
namespace Account;

class WalletController extends BaseController{
	public function index(){
		global $G,$lang;
		
		include template('wallet_index');
	}
}