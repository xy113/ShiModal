<?php
namespace Page;
class DetailController extends BaseController{
	public function index(){
		global $G,$lang;
		$pageid = intval($_GET['pageid']);
		$pagedata = page_get_data(array('pageid'=>$pageid));
		
		$G['title'] = $pagedata['title'];
		include template('detail');
	}
}