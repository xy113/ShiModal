<?php
/**
 * 添加文章信息
 * @param array $data
 * @param boolean $return
 */
function post_add_item($data,$return=FALSE){
	$id = M('post_title')->insert($data, true);
	if ($return){
		return post_get_item(array('id'=>$id));
	}else {
		return $id;
	}
}

/**
 * 删除文章信息
 * @param mixed $condition
 * @return boolean
 */
function post_delete_item($condition){
	if ($condition) {
		return M('post_title')->where($condition)->delete();
	}else {
		return false;
	}
}

/**
 * 更新文章信息
 * @param mixed $condition
 * @param array $data
 */
function post_update_item($condition,$data){
	return M('post_title')->where($condition)->update($data);
}

/**
 * 获取文章信息
 * @param mixed $condition
 */
function post_get_item($condition){
	$data = M('post_title')->where($condition)->getOne();
	if ($data) {
		return $data;
	}else {
		return array();
	}
}

/**
 * 获取文章数量
 * @param mixed $condition
 */
function post_get_item_num($condition, $field='*'){
	return M('post_title')->where($condition)->count($field);
}

/**
 * 获取文章列表
 * @param mixed $condition
 * @param number $num
 * @param number $limit
 * @param string $order
 */
function post_get_item_list($condition,$num=20,$limit=0,$order=''){
	$limit = $num ? "$limit,$num" : ($limit ? $limit : '');
	!$order && $order = 'id DESC';
	$postlist = M('post_title')->where($condition)->limit($limit)->order($order)->select();
	if ($postlist){
		return $postlist;
	}else {
		return array();
	}
}

/**
 * 获取文章分页列表
 * @param mixed $condition
 * @param number $page
 * @param number $pagesize
 * @param string $order
 */
function post_get_item_page($condition,$page=1,$pagesize=20,$order=''){
	$limit = ($page - 1)*$pagesize;
	return post_get_item_list($condition, $pagesize, $limit, $order);
}

/**
 * 添加文章内容
 * @param array $data
 */
function post_add_content($data){
	return M('post_content')->insert($data);
}

/**
 * 删除文章内容
 * @param mixed $condition
 */
function post_delete_content($condition){
	if ($condition) {
		return M('post_content')->where($condition)->delete();
	}else {
		return false;
	}
}

/**
 * 更新文章内容
 * @param mixed $condition
 * @param array $data
 * @return boolean|number
 */
function post_update_content($condition, $data){
	return M('post_content')->where($condition)->update($data);
}

/**
 * 获取文章内容
 * @param mixed $condition
 * @return string
 */
function post_get_content($condition){
	$content = '';
	$contentlist = post_get_contents($condition);
	if ($contentlist) {
		foreach ($contentlist as $item){
			$content.= $item['content'];
		}
	}
	return $content;
}

/**
 * 获取文章内容列表
 * @param mixed $condition
 */
function post_get_contents($condition){
	$contentlist = M('post_content')->where($condition)->order('pageorder','ASC')->select();
	if ($contentlist) {
		return $contentlist;
	}else {
		return array();
	}
}

/**
 * 添加文章分类
 * @param array $data
 * @param number $return
 * @return mixed
 */
function post_add_category($data, $return=0){
	$catid = M('post_cat')->insert($data, true);
	if ($return) {
		return post_get_category(array('catid'=>$catid));
	}else {
		return $catid;
	}
}

/**
 * 删除商品分类
 * @param mixed $condition
 */
function post_delete_category($condition){
	if ($condition) {
		return M('post_cat')->where($condition)->delete();
	}else {
		return false;
	}
}

/**
 * 更新商品分类
 * @param mixed $condition
 * @param array $data
 */
function post_update_category($condition, $data){
	return M('post_cat')->where($condition)->update($data);
}

/**
 * 获取分类信息
 * @param mixed $condition
 * @return array
 */
function post_get_category($condition){
	$data = M('post_cat')->where($condition)->getOne();
	if ($data) {
		return $data;
	}else {
		return array();
	}
}

/**
 * 获取商品分类列表
 * @param string $fromcache
 * @param string $all
 * @return boolean|unknown
 */
function post_get_category_list($fromcache=true, $all=false){
	if ($fromcache) {
		return cache('post_category');
	}else {
		$condition = $all ? '' : array('available'=>1);
		$categorylist = M('post_cat')->where($condition)->order('displayorder ASC,catid ASC')->select();
		if ($categorylist) {
			$datalist = array();
			foreach ($categorylist as $category){
				$datalist[$category['catid']] = $category;
			}
			return $datalist;
		}else {
			return array();
		}
	}
}

/**
 * 获取分类选项
 * @param number $fid
 * @param number $selected
 * @param number $showdisable
 * @param number $fromcache
 * @param number $all
 * @return string
 */
function post_get_category_options($fid=0, $selected=0, $showdisable=0, $fromcache=1, $all=0){
	static $separater;
	static $categorylist;
	if (!$categorylist) $categorylist = post_get_category_list($fromcache, $all);
	
	$options = '';
	$separater2 = $separater;
	if ($categorylist && is_array($categorylist)) {
		
		foreach ($categorylist as $category){
			if ($category['fid'] == $fid){
				$a = $showdisable ? ($category['enable'] ? '' : 'disabled="disabled"') : '';
				$s = $selected == $category['catid'] ? 'selected="selected"' : '';
				$options.='<option value="'.$category['catid'].'"'.$a.$s.'>'.$separater.$category['name'].'</option>';
				$separater.= '|--';
				$options.= post_get_category_options($category['catid'], $selected, $showdisable, $fromcache, $all);
				$separater = $separater2;
			}
		}
	}
	return $options;
}

/**
 * 更新商品分类缓存
 * @return boolean
 */
function post_update_category_cache(){
	$categorylist = post_get_category_list(0, 1);
	return cache('post_category', $categorylist);
}