<?php
namespace Common;
class CaptchaController extends BaseController{
	public function index(){
		$captcha = new \Core\Captcha();
	}
}