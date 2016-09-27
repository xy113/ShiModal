<?php
/**
 * 添加模特
 * @param array $data
 * @param number $return
 * @return mixed
 */
function model_add_item($data, $return=0){
	$id = M('model')->insert($data, true);
	if ($return) {
		return model_get_item(array('id'=>$id));
	}else {
		return $id;
	}
}

/**
 * 删除模特
 * @param mixed $condition
 * @return boolean|number|boolean
 */
function model_delete_item($condition){
	if ($condition) {
		return M('model')->where($condition)->delete();
	}else {
		return false;
	}
}

/**
 * 更新模特信息
 * @param mixed $condition
 * @param array $data
 * @return boolean|number
 */
function model_update_item($condition, $data){
	return M('model')->where($condition)->update($data);
}

/**
 * 获取模特信息
 * @param mixed $condition
 */
function model_get_item($condition){
	$data = M('model')->where($condition)->getOne();
	if ($data) {
		return $data;
	}else {
		return array();
	}
}

/**
 * 获取模特数量
 * @param mixed $condition
 * @return mixed
 */
function model_get_item_num($condition){
	return M('model')->where($condition)->count();
}

/**
 * 获取模特信息
 * @param mixed $condition
 * @param number $num
 * @param number $limit
 * @param string $order
 */
function model_get_item_list($condition, $num=20, $limit=0, $order=''){
	$limit = $num ? "$limit,$num" : ($limit ? $limit : '');
	!$order && $order = 'id DESC';
	$itemlist = M('model')->where($condition)->order($order)->limit($limit)->select();
	if ($itemlist) {
		return $itemlist;
	}else {
		return array();
	}
}