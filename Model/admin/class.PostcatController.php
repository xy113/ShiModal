<?php
namespace Admin;
class PostcatController extends BaseController{
	public function index(){
		$this->item_list();
	}
	
	/**
	 * 分类列表
	 */
	public function item_list(){
		global $G,$lang;
	
		if ($this->checkFormSubmit()){
			$itemlist = $_GET['itemlist'];
			if ($itemlist && is_array($itemlist)) {
				$delete = $_GET['delete'];
				if ($delete && is_array($delete)){
					post_delete_category(array('catid'=>array('IN', implodeids($delete))));
				}
				$pinyin = new \Core\Pinyin();
				foreach ($itemlist as $catid=>$item){
					if ($item['name']){
						if (!$item['identifer']){
							$item['identifer'] = $pinyin->getPinyin($item['name']);
						}
						if ($catid > 0){
							post_update_category(array('catid'=>$catid), $item);
						}else {
							post_add_category($item);
						}
					}
				}
			}
			post_update_category_cache();
			$this->showSuccess('update_succeed');
		}else {
				
			$itemlist = array();
			$categorylist = post_get_category_list(0, 1);
			foreach ($categorylist as $category){
				$itemlist[$category['fid']][$category['catid']] = $category;
			}
			unset($categorylist, $category);
				
			include template('post_cat_list');
		}
	}
	
	/**
	 * 
	 */
	public function edit(){
		global $G,$lang;
		$catid = intval($_GET['catid']);
		if ($this->checkFormSubmit()){
			$category = $_GET['category'];
			if ($category && is_array($category)){
				if (!$category['identifer']) {
					$pinyin = new \Core\Pinyin();
					$category['identifer'] = $pinyin->getPinyin($category['name']);
				}
				post_update_category(array('catid'=>$catid), $category);
				post_update_category_cache();
				$this->showSuccess('update_succeed');
			}
		}else {
				
			$category = post_get_category(array('catid'=>$catid));
			$datalist = array();
			$categorylist = post_get_category_list(0,1);
			foreach ($categorylist as $list){
				$datalist[$list['fid']][$list['catid']] = $list;
			}
			$categorylist = $datalist;
			unset($datalist, $list);
			include template('post_cat_form');
		}
	}
	
	/**
	 * 合并文章分类
	 */
	public function merge(){
		global $G,$lang;
		if ($this->checkFormSubmit()){
			$source = $_GET['source'];
			$target = intval($_GET['target']);
			if ($source && is_array($source)){
				foreach ($source as $k=>$v){
					if ($v == $target){
						unset($source[$k]);
					}
				}
			}
			$source = $source ? implodeids($source) : 0;
			if ($source){
				post_update_item(array('catid'=>array('IN', $source)), array('catid'=>$target));
				post_delete_category(array('catid'=>array('IN', $source)));
				post_update_category_cache();
			}
			$this->showSuccess('update_succeed');

		}else {
			$categoryoptions = post_get_category_options(0,0,1);
			include template('post_cat_merge');
		}
	}
	
	public function setimage(){
		$catid = intval($_GET['catid']);
		$image = htmlspecialchars($_GET['image']);
		post_update_category(array('catid'=>$catid), array('image'=>$image));
		post_update_category_cache();
		$this->showAjaxReturn(0);
	}
}