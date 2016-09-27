<?php
/**
 * 显示登录界面
 */
function member_show_login(){
	global $G,$lang;
	$G['m'] = 'member';
	$G['title'] = $lang['login'];
	include template('login','member');
	exit();
}

/**
 * 显示AJAX登录界面
 */
function member_show_ajax_login(){
	global $G,$lang;
	$G['m'] = 'member';
	$G['title'] = $lang['login'];
	include template('ajaxlogin','member');
	exit();
}

/**
 * 显示微信版登录界面
 */
function member_show_weixin_login(){
	global $G,$lang;
	$G['m'] = 'weixin';
	$G['title'] = $lang['login'];
	include template('login','weixin');
	exit();
}

/**
 * 显示注册页面
 */
function member_show_register(){
	global $G,$lang;
	$G['m'] = 'member';
	$G['title'] = $lang['register'];
	include template('register','member');
	exit();
}

/**
 * 显示AJAX注册页面
 */
function member_show_ajax_register(){
	global $G,$lang;
	$G['m'] = 'member';
	$G['title'] = $lang['register'];
	include template('ajaxregister','member');
	exit();
}

/**
 * 显示微信版注册页面
 */
function member_show_weixin_register(){
	global $G,$lang;
	$G['m'] = 'weixin';
	$G['title'] = $lang['register'];
	include template('register','weixin');
	exit();
}

/**
 * 
 * @param string $password
 * @return string|string
 */
function member_encrypt_password($password){
	if (!$password) {
		return $password;
	}else {
		return sha1(md5($password));
	}
}

/**
 * 更新cookie
 * @param int $uid
 * @return boolean
 */
function member_update_cookie($uid){
	$condition = array('uid'=>$uid);
	$member    = member_get_data($condition);
	$status    = member_get_status($condition);
	$userinfo  = member_get_info($condition);
	$group     = usergroup_get_data(array('gid'=>$member['gid']));
	unset($member['password']);
	cookie('user_member', authcode(serialize($member)));
	cookie('user_status', authcode(serialize($status)));
	cookie('user_info',   authcode(serialize($userinfo)));
	cookie('user_group',  authcode(serialize($group)));
	return true;
}

/**
 * 用户登录
 * @param string $username
 * @param string $password
 * @param string $field
 */
function member_login($username, $password, $field='username'){
	if (!$username) {
		return array(
				'errno'=>1,
				'error'=>'account_incorrect'
		);
	}
	if (!$password) {
		return array(
				'errno'=>2,
				'error'=>'password_incorrect'
		);
	}
	
	$field = in_array($field, array('uid','email','mobile')) ? $field : 'username';
	$member = M('member')->where(array($field=>$username))->getOne();
	if (!$member){
		return array(
				'errno'=>3,
				'error'=>'account_invalid'
		);
	}else  {
		if ($member['password'] !== getPassword($password)){
			return array(
					'errno'=>4,
					'error'=>'password_incorrect'
			);
		}elseif ($member['status'] == '-1'){
			return array(
					'errno'=>5,
					'error'=>'login_be_forbidden'
			);
		}elseif ($member['status'] == '-2'){
			return array(
					'errno'=>6,
					'error'=>'account_unauthorized'
			);
		}else {
			member_update_status(array('uid'=>$member['uid']), array(
					'lastvisit'=>TIMESTAMP,
					'lastvisitip'=>getIp()
			));
			member_update_cookie($member['uid']);
			return array(
					'errno'=>0,
					'error'=>'success',
					'userinfo'=>array(
							'uid'=>$member['uid'],
							'gid'=>$member['gid'],
							'username'=>$member['username'],
							'email'=>$member['email'],
							'mobile'=>$member['mobile']
					)
			);
		}
	}
}

/**
 * 退出登录
 */
function member_logout(){
	member_update_status(array('uid'=>G('uid')), array('lastactive'=>TIMESTAMP));
	cookie('user_member', null);
	cookie('user_status', null);
	cookie('user_info', null);
	cookie('user_group', null);
}

/**
 * 用户注册
 * @param array $data
 * @param number $login
 */
function member_register($data, $login=0){	
	$newmember = array();
	if (!$data['username'] && !$data['email'] && !$data['mobile']) {
		return array(
				'errno'=>1,
				'error'=>'invalid_parameter'
		);
	}else {
		if ($data['username']) {
			$newmember['username'] = $data['username'];
			if (member_get_num(array('username'=>$data['username']))) {
				//用户名已被人使用
				return array(
						'errno'=>2,
						'error'=>'username_be_occupied'
				);
			}
		}
			
		if ($data['mobile']) {
			if (!ismobile($data['mobile'])) {
				//手机号不合法
				return array(
						'errno'=>3,
						'error'=>'mobile_incorrect'
				);
			}
			
			if (member_get_num(array('mobile'=>$data['mobile']))) {
				//手机号已被使用
				return array(
						'errno'=>4,
						'error'=>'mobile_be_occupied'
				);
			}
			$newmember['mobile'] = $data['mobile'];
		}
			
		if ($data['email']) {
			if (!isemail($data['email'])) {
				//邮箱格式不合法
				return array(
						'errno'=>5,
						'error'=>'email_be_occupied'
				);
			}
			
			if (member_get_num(array('email'=>$data['email']))) {
				//邮箱已被人使用
				return array(
						'errno'=>6,
						'error'=>'email_be_occupied'
				);
			}
			$newmember['email'] = $data['email'];
		}
	}
	
	if (!$data['password'] || strlen($data['password']) < 6) {
		return array(
				'errno'=>7,
				'error'=>'password_incorrect'
		);
	}else {
		$newmember['password'] = getPassword($data['password']);
	}
	
	if ($data['gid']) {
		$newmember['gid'] = $data['gid'];
	}else {
		$group = M('member_group')->where("type='member' AND creditslower>=0")
		->order('creditslower','ASC')->getOne();
		$newmember['gid'] = $group['gid'];
	}
	
	$uid = member_add_data($newmember);
	M('member_profile')->insert(array('uid'=>$uid), false, true);
	M('member_status')->insert(array(
			'uid'=>$uid,
			'regdate'=>time(),
			'regip'=>getIp(),
			'lastvisit'=>time(),
			'lastvisitip'=>getIp()
	));
	
	M('member_count')->insert(array('uid'=>$uid), false, true);
	if (!$newmember['username']) {
		$newmember['username'] = 'DSX'.$uid;
		member_update_data(array('uid'=>$uid), array('username'=>$newmember['username']));
	}
	if ($login) {
		return member_login($uid, $data['password'], 'uid');
	}else {
		$member = member_get_data(array('uid'=>$uid));
		return array(
				'errno'=>0,
				'error'=>'success',
				'userinfo'=>array(
						'uid'=>$member['uid'],
						'gid'=>$member['gid'],
						'username'=>$member['username'],
						'email'=>$member['email'],
						'mobile'=>$member['mobile']
				)
		);
	}
}

/**
 * 添加会员信息
 * @param array $data
 * @param boolean $return
 * @return unknown
 */
function member_add_data($data,$return=FALSE){
	$uid = M('member')->insert($data, true);
	if ($return) {
		return member_get_data(array('uid'=>$uid));
	}else {
		return $uid;
	}
}

/**
 * 删除会员信息
 * @param mixed $condition
 */
function member_delete_data($condition){
	if ($condition){
		return M('member')->where($condition)->delete();
	}else {
		return false;
	}
}

/**
 * 更新用户信息
 * @param mixed $condition
 * @param array $data
 * @return boolean
 */
function member_update_data($condition, $data){
	return M('member')->where($condition)->update($data);
}

/**
 * 获取用户信息
 * @param mixed $condition
 * @return array
 */
function member_get_data($condition){
	$data = M('member')->where($condition)->getOne();
	if ($data) {
		return $data;
	}else {
		return array();
	}
}

/**
 * 获取会员数量
 * @param mixed $condition
 */
function member_get_num($condition){
	return M('member')->where($condition)->count();
}

/**
 * 获取用户列表
 * @param mixed $condition
 * @param number $num
 * @param number $limit
 * @param string $order
 */
function member_get_list($condition,$num=20,$limit=0,$order=''){
	global $lang;
	$limit = $num ? "$limit,$num" : '';
	$order = $order ? $order : 'uid ASC';
	$memberlist = M('member')->where($condition)->limit($limit)->order($order)->select();
	if ($memberlist){
		$datalist = array();
		foreach ($memberlist as $list){
			unset($list['password']);
			$list['avatar'] = array(
					'big'=>avatar($list['uid'], 'big'),
					'middle'=>avatar($list['uid'], 'middle'),
					'small'=>avatar($list['uid'], 'small')
			);
			$list['status_name'] = $lang['member_status'][$list['status']];
			$datalist[$list['uid']] = $list;
		}
		return $datalist;
	}else {
		return array();
	}
}

/**
 * 获取用户分页列表
 * @param mixed $condition
 * @param number $page
 * @param number $pagesize
 * @param string $order
 * @return array
 */
function member_get_page($condition,$page=1, $pagesize=20, $order=''){
	$limit = ($page - 1)*$pagesize;
	return member_get_list($condition, $pagesize, $limit, $order);
}

/**
 * 添加用户资料
 * @param array $data
 * @return boolean|unknown
 */
function member_add_info($data){
	return M('member_info')->insert($data, false, true);
}

/**
 * 删除用户资料
 * @param mixed $condition
 * @return boolean
 */
function member_delete_info($condition){
	if ($condition) {
		return M('member_info')->where($condition)->delete();
	}else {
		return false;
	}
}

/**
 * 更新用户资料
 * @param mixed $condition
 * @param mixed $data
 * @return boolean
 */
function member_update_info($condition, $data){
	return M('member_info')->where($condition)->update($data);
}

/**
 * 获取用户资料
 * @param mixed $condition
 * @return array
 */
function member_get_info($condition){
	$data = M('member_info')->where($condition)->getOne();
	if ($data) {
		return $data;
	}else {
		return array();
	}
}

/**
 * 获取用户资料列表
 * @param mixed $condition
 * @param number $num
 * @return array
 */
function member_get_info_list($condition, $num=20){
	$datalist = M('member_info')->where($condition)->limit(0, $num)->select();
	if ($datalist) {
		return $datalist;
	}else {
		return array();
	}
}

/**
 * 添加用户统计
 * @param array $data
 * @return boolean|unknown
 */
function member_add_stat($data){
	return M('member_stat')->insert($data, false, true);
}

/**
 * 删除用户统计数据
 * @param mixed $condition
 * @return boolean
 */
function member_delete_stat($condition){
	if ($condition) {
		return M('member_stat')->where($condition)->delete();
	}else {
		return false;
	}
}

/**
 * 更新用户统计
 * @param mixed $condition
 * @param array $data
 * @return boolean
 */
function member_update_stat($condition, $data){
	return M('member_stat')->where($condition)->update($data);
}

/**
 * 获取用户统计数据
 * @param mixed $condition
 * @return array
 */
function member_get_stat($condition){
	$data = M('member_stat')->where($condition)->getOne();
	if ($data) {
		return $data;
	}else {
		return array();
	}
}

/**
 * 获取用户统计列表
 * @param mixed $condition
 * @param number $num
 * @return unknown
 */
function member_get_stat_list($condition, $num=20){
	$datalist = M('member_stat')->where($condition)->limit(0, $num)->select();
	if ($datalist) {
		return $datalist;
	}else {
		return array();
	}
}

/**
 * 添加用户状态
 * @param array $data
 * @return boolean|unknown
 */
function member_add_status($data){
	return M('member_status')->insert($data, false, true);
}

/**
 * 删除用户状态
 * @param mixed $condition
 * @return boolean
 */
function member_delete_status($condition){
	if ($condition) {
		return M('member_status')->where($condition)->delete();
	}else {
		return false;
	}
}

/**
 * 更新用户状态
 * @param mixed $condition
 * @param array $data
 * @return boolean
 */
function member_update_status($condition, $data){
	return M('member_status')->where($condition)->update($data);
}

/**
 * 获取用户状态
 * @param mixed $condition
 */
function member_get_status($condition){
	return M('member_status')->where($condition)->getOne();
}

/**
 * 获取用户统计列表
 * @param mixed $condition
 * @param int $num
 * @return array
 */
function member_get_status_list($condition, $num){
	$datalist = M('member_status')->where($condition)->limit(0,$num)->select();
	if ($datalist) {
		return $datalist;
	}else {
		return array();
	}
}

/**
 * 添加第三方登录记录
 * @param array $data
 * @return boolean|unknown
 */
function member_add_login($data){
	return M('member_login')->insert($data, false, true);
}

/**
 * 删除第三方登录记录
 * @param mixed $condition
 * @return boolean
 */
function member_delete_login($condition){
	if ($condition) {
		return M('member_login')->where($condition)->delete();
	}else {
		return false;
	}
}

/**
 * 更新第三方登录记录
 * @param mixed $condition
 * @param array $data
 * @return boolean
 */
function member_update_login($condition,$data){
	return M('member_login')->where($condition)->update($data);
}

/**
 * 获取第三方登录记录
 * @param mixed $condition
 */
function member_get_login($condition){
	return M('member_login')->where($condition)->getOne();
}

/**
 * 获取第三方登录记录数目
 * @param mixed $condition
 * @param string $field
 */
function member_get_login_num($condition, $field='*'){
	return M('member_login')->where($condition)->count($field);
}

/**
 * 获取第三方登录记录列表
 * @param mixed $condition
 * @param number $num
 * @param number $limit
 * @param string $order
 * @return array
 */
function member_get_login_list($condition, $num=20, $limit=0, $order=''){
	$limit = $num ? "$limit,$num" : ($limit ? $limit : '');
	!$order && $order = 'id DESC';
	$datalist = M('member_login')->where($condition)->limit($limit)->order($order)->select();
	if ($datalist) {
		return $datalist;
	}else {
		return array();
	}
}

/**
 * 添加会员分组
 * @param array $data
 * @param string $return
 */
function usergroup_add_data($data, $return=FALSE){
	$gid = M('member_group')->insert($data, true);
	if ($return){
		return usergroup_get_data(array('gid'=>$gid));
	}else {
		return $gid;
	}
}

/**
 * 删除用户组
 * @param mixed $condition
 */
function usergroup_delete_data($condition){
	if (!$condition) {
		return false;
	}else {
		return M('member_group')->where($condition)->delete();
	}
}

/**
 * 更新用户组信息
 * @param mixed $condition
 * @param array $data
 */
function usergroup_update_data($condition,$data){
	return M('member_group')->where($condition)->update($data);
}

/**
 * 获取用户组信息
 * @param mixed $condition
 */
function usergroup_get_data($condition){
	return M('member_group')->where($condition)->getOne();
}

/**
 * 获取用户分组列表
 * @param mixed $condition
 * @param string $order
 */
function usergroup_get_list($condition,$order=''){
	!$order && $order = 'gid ASC';
	$grouplist = M('member_group')->where($condition)->order($order)->select();
	if ($grouplist) {
		$datalist = array();
		foreach ($grouplist as $list){
			$datalist[$list['gid']] = $list;
		}
		return $datalist;
	}else {
		return array();
	}
}