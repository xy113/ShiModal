<?php
namespace Admin;
class ModelController extends BaseController{
	public function index(){
		$this->item_list();
	}
	
	public function item_list(){
		global $G,$lang;
		if ($this->checkFormSubmit()){
			$modelids = $_GET['id'];
			if ($modelids && is_array($modelids)){
				$modelids = implodeids($modelids);
				switch ($_GET['option']) {
					case 'delete':
						model_delete_item(array('id'=>array('IN', $modelids)));
						image_delete_data(array('datatype'=>'model', 'dataid'=>array('IN', $modelids)));
						$this->showSuccess('delete_succeed');
						break;
					default:;
				}
			}
		}else {
			$condition = array();
			$keyword = trim($_GET['keyword']);
			if ($keyword) $condition = "nickname LIKE '%$keyword%' OR fullname LIKE '%$keyword%'";
			
			$pagesize  = 20;
			$totalnum  = model_get_item_num($condition);
			$pagecount = $totalnum < $pagesize ? 1 : ceil($totalnum/$pagesize);
			$itemlist  = model_get_item_list($condition, $pagesize, ($G['page']-1)*$pagesize);
			$pages = $this->showPages($G['page'], $pagecount, $totalnum, "keyword=$keyword", 1);
			include template('model_list');
		}
	}
	
	public function add(){
		global $G,$lang;
		if ($this->checkFormSubmit()){
			$model = $_GET['model'];
			if ($model && $model['nickname']){
				$model['uid'] = $this->uid;
				$model['dateline'] = TIMESTAMP;
				$id = model_add_item($model);
				
				$piclist = $_GET['piclist'];
				if ($piclist && is_array($piclist)){
					foreach ($piclist as $pic){
						$pic['dataid'] = $id;
						$pic['datatype'] = 'model';
						image_add_data($pic);
					}
				}
				$this->showSuccess('save_succeed');
			}else {
				$this->showError('undefined_action');
			}
		}else {
			
			include template('model_form');
		}
	}
	
	public function edit(){
		global $G,$lang;
		$id = intval($_GET['id']);
		if ($this->checkFormSubmit()){
			$model = $_GET['model'];
			if ($model && $model['nickname']){
				model_update_item(array('id'=>$id), $model);
				
				image_delete_data(array('dataid'=>$id, 'datatype'=>'model'));
				$piclist = $_GET['piclist'];
				if ($piclist && is_array($piclist)){
					foreach ($piclist as $pic){
						$pic['dataid'] = $id;
						$pic['datatype'] = 'model';
						image_add_data($pic);
					}
				}
				$this->showSuccess('update_succeed');
			}else {
				$this->showError('undefined_action');
			}
		}else {
			
			$model = model_get_item(array('id'=>$id));
			$piclist = image_get_list(array('dataid'=>$id, 'datatype'=>'model'), 0);
			include template('model_form');
		}
	}
}