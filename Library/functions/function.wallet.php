<?php
/**
 * 获取钱包数据
 * @param int $uid
 * @return array
 */
function wallet_get_data($uid){
	$data = M('wallet')->where(array('uid'=>$uid))->getOne();
	if (!$data) {
		M('wallet')->insert(array('uid'=>$uid,'balance'=>0), 0 ,1);
		return wallet_get_data($uid);
	}else {
		return $data;
	}
}

/**
 * 删除钱包
 * @param mixed $condition
 * @return boolean
 */
function wallet_delete_data($condition){
	if ($condition) {
		return M('wallet')->where($condition)->delete();
	}else {
		return false;
	}
}

/**
 * 更新钱包
 * @param mixed $condition
 * @param array $data
 * @return boolean
 */
function wallet_update_data($condition, $data){
	return M('wallet')->where($condition)->update($data);
}

/**
 * 获取钱包列表
 * @param mixed $condition
 * @param number $num
 * @param number $limit
 * @param string $order
 * @return array
 */
function wallet_get_list($condition, $num=20, $limit=0, $order=null){
	$limit = $num ? "$limit,$num" : ($limit ? $limit : '');
	!$order && $order = 'uid ASC';
	$itemlist = M('wallet')->where($condition)->limit($limit)->order($order)->select();
	if ($itemlist) {
		return $itemlist;
	}else {
		return array();
	}
}

/**
 * 付款
 * @param integer $uid
 * @param float $amount
 * @return boolean
 */
function wallet_pay($uid, $amount){
	if (!$uid || !$amount){
		return false;
	}else {
		$amount = floatval($amount);
		return wallet_update_data(array('uid'=>$uid), "balance=balance-$amount");
	}
}

/**
 * 收入
 * @param int $uid
 * @param float $amount
 * @return boolean
 */
function wallet_income($uid, $amount){
	if (!$uid || !$amount){
		return false;
	}else {
		$amount = floatval($amount);
		return wallet_update_data(array('uid'=>$uid), "balance=balance+$amount");
	}
}

/**
 * 生成交易单号
 * @param string $pre
 * @return string
 */
function trade_create_no($pre=''){
	return $pre.date('YmdHis').rand(1000,9999).rand(1000,9999);
}

/**
 * 添加交易信息
 * @param array $data
 * @param number $return
 * @return void|boolean|unknown
 */
function trade_add_data($data, $return=0){
	$trade_id = M('trade')->insert($data, true);
	if ($return) {
		return trade_get_data(array('trade_id'=>$trade_id));
	}else {
		return $trade_id;
	}
}

/**
 * 删除交易信息
 * @param mixed $condition
 * @return boolean
 */
function trade_delete_data($condition){
	if ($condition) {
		return M('trade')->where($condition)->delete();
	}else {
		return false;
	}
}

/**
 * 更新交易信息
 * @param mixed $condition
 * @param array $data
 * @return boolean
 */
function trade_update_data($condition, $data){
	return M('trade')->where($condition)->update($data);
}

/**
 * 获取交易信息
 * @param mixed $condition
 * @return array
 */
function trade_get_data($condition){
	$data = M('trade')->where($condition)->getOne();
	if ($data) {
		return $data;
	}else {
		return array();
	}
}

/**
 * 获取交易记录数目
 * @param mixed $condition
 * @param string $field
 */
function trade_get_num($condition, $field='*'){
	return M('trade')->where($condition)->count($field);
}

function trade_get_list($condition, $num=20, $limit=0, $order=''){
	$limit = $num ? "$limit, $num" : ($limit ? $limit : '');
	!$order && $order = 'trade_id DESC';
	$tradelist = M('trade')->where($condition)->limit($limit)->order($order)->select();
	if ($tradelist) {
		return $tradelist;
	}else {
		return array();
	}
}

/**
 * 获取交易记录分页列表
 * @param mixed $condition
 * @param number $page
 * @param number $pagesize
 * @param string $order
 * @return array
 */
function trade_get_page($condition, $page=1, $pagesize=20, $order=''){
	$limit = ($page - 1) * $pagesize;
	return trade_get_list($condition, $pagesize, $limit, $order);
}

/**
 * 获取支付提交表单
 * @param int $trade_id
 * @param array $params
 */
function trade_get_form($trade_id, $params=array()){
	$shtml = '<html><head><title>payment</title></head><body>';
	$shtml.= '<form method="post" id="paymentForm" name="paymentForm" action="/?m=payment">';
	$shtml.= '<input type="hidden" name="trade_id" value="'.$trade_id.'">';
	if ($params) {
		foreach ($params as $k=>$v){
			$shtml.= '<input type="hidden" name="'.$k.'" value="'.$v.'">';
		}
	}
	$shtml.= '<input type="submit" value="" style="display:none;"></form>';
	$shtml.= '<script>document.forms[\'paymentForm\'].submit();</script>';
	$shtml.= '</body></html>';
	return $shtml;
}

/**
 * 生成订单号
 * @param string $pre
 * @return string
 */
function order_create_no($pre=''){
	return $pre.date('YmdHis').rand(1000,9999).rand(1000,9999);
}

/**
 * 添加订单信息
 * @param array $data
 * @param number $return
 */
function order_add_item($data, $return=0){
	$order_id = M('order_item')->insert($data, true);
	if ($return) {
		return order_get_item(array('order_id'=>$order_id));
	}else {
		return $order_id;
	}
}

/**
 * 删除订单信息
 * @param mixed $condition
 */
function order_delete_item($condition){
	if ($condition) {
		return M('order_item')->where($condition)->delete();
	}else {
		return false;
	}
}

/**
 * 更新订单信息
 * @param mixed $condition
 * @param array $data
 */
function order_update_item($condition, $data){
	return M('order_item')->where($condition)->update($data);
}

/**
 * 获取订单信息
 * @param mixed $condition
 */
function order_get_item($condition){
	$data = M('order_item')->where($condition)->getOne();
	if ($data) {
		return $data;
	}else {
		return array();
	}
}

/**
 * 获取订单数目
 * @param mixed $condition
 * @param string $field
 */
function order_get_item_num($condition, $field='*'){
	return M('order_item')->where($condition)->count($field);
}

/**
 * 后去订单数据列表
 * @param mixed $condition
 * @param number $num
 * @param number $limit
 * @param string $order
 * @return array
 */
function order_get_item_list($condition, $num=20, $limit=0, $order=''){
	$limit = $num ? "$limit, $num" : ($limit ? $limit : '');
	!$order && $order = 'order_id DESC';

	$itemlist = M('order_item')->where($condition)->limit($limit)->order($order)->select();
	if ($itemlist){
		return $itemlist;
	}else {
		return array();
	}
}

/**
 * 添加订单商品
 * @param array $data
 * @return boolean|unknown
 */
function order_add_goods($data){
	return M('order_goods')->insert($data, true);
}

/**
 * 删除订单商品
 * @param mixed $condition
 * @return boolean
 */
function order_delete_goods($condition){
	if ($condition) {
		return M('order_goods')->where($condition)->delete();
	}else {
		return false;
	}
}

/**
 * 更新订单商品
 * @param mixed $condition
 * @param array $data
 * @return boolean
 */
function order_update_goods($condition, $data){
	return M('order_goods')->where($condition)->update($data);
}

/**
 * 获取订单商品信息
 * @param mixed $condition
 * @return array
 */
function order_get_goods($condition){
	$data = M('order_goods')->where($condition)->getOne();
	if ($data) {
		return $data;
	}else {
		return array();
	}
}

/**
 * 获取订单商品列表
 * @param mixed $condition
 * @return array
 */
function order_get_goods_list($condition){
	$goodslist = M('order_goods')->where($condition)->select();
	if ($goodslist) {
		return $goodslist;
	}else {
		return array();
	}
}

/**
 * 添加物流信息
 * @param array $data
 * @return boolean|unknown
 */
function wuliu_add_data($data){
	return M('wuliu')->insert($data, true, true);
}

/**
 * 删除物流信息
 * @param mixed $condition
 * @return boolean
 */
function wuliu_delete_data($condition){
	if ($condition){
		return M('wuliu')->where($condition)->delete();
	}else {
		return false;
	}
}

/**
 * 更新物流信息
 * @param mixed $condition
 * @param array $data
 * @return boolean
 */
function wuliu_update_data($condition,$data){
	return M('wuliu')->where($condition)->update($data);
}

/**
 * 获取物流信息
 * @param mixed $condition
 * @return array
 */
function wuliu_get_data($condition){
	$data = M('wuliu')->where($condition)->getOne();
	if ($data) {
		return $data;
	}else {
		return  array();
	}
}

/**
 * 获取物流信息
 * @param mixed $condition
 */
function wuliu_get_num($condition){
	return M('wuliu')->where($condition)->count();
}

/**
 * 获取物流列表
 * @param mixed $condition
 * @param number $num
 * @param number $limit
 * @param string $order
 * @return array
 */
function wuliu_get_list($condition, $num=20, $limit=0, $order=''){
	$limit = $num ? "$limit,$num" : ($limit ? $limit : '');
	!$order && $order = 'id DESC';
	$wuliulist = M('wuliu')->where($condition)->limit($limit)->order($order)->select();
	if ($wuliulist) {
		return $wuliulist;
	}else {
		return array();
	}
}