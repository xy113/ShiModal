<?php
namespace Admin;
class MemberController extends BaseController{
	public function index(){
		$this->member_list();
	}
	
	/**
	 * 显示会员列表
	 */
	public function member_list(){
		global $G,$lang;
		if ($this->checkFormSubmit()){
			$uids = $_GET['uid'];
			if ($uids && is_array($uids)){
				$uids = implode(',', $uids);
				$condition = array('uid'=>array('IN', $uids));
				switch ($_GET['option']){
					case 'delete':
						member_delete_data($condition);
						member_delete_profile($condition);
						member_delete_status($condition);
						member_delete_count($condition);
						break;
						
					case 'move':
						$usergrouplist = usergroup_get_list(0);
						include template('member_move');
						exit();
						break;
						
					case 'normal':
						member_update_data($condition, array('status'=>0));
						break;
						
					case 'nologin':
						member_update_data($condition, array('status'=>-1));
						break;
					
					case 'nopost':
						member_update_data($condition, array('status'=>-2));
						break;
					default:;
				}
				$this->showSuccess('update_succeed');
			}else {
				$this->showError('no_select');
			}
		}else {
			
			$pagesize = 20;
			$condition = array();
			
			$field   = isset($_GET['field']) ? trim($_GET['field']) : '';
			$keyword = isset($_GET['keyword']) ? trim($_GET['keyword']) : '';
			if ($field && $keyword){
				switch ($field) {
					case 'uid': $condition['uid'] = $keyword;
					break;
					
					case 'username': $condition['username'] = array('LIKE', $keyword);
					break;
					
					case 'mobile' : $condition['mobile'] = array('LIKE', $keyword);
					break;
					
					case 'email' : $condition['email'] = array('LIKE', $keyword);
					break;
					 default: $condition['username'] = array('LIKE', $keyword);
				}
			}
			
			$totalnum   = member_get_num($condition);
			$pagecount  = $totalnum < $pagesize ? 1 : ceil($totalnum/$pagesize);
			$memberlist = member_get_page($condition, $G['page'], $pagesize, 'uid ASC');
			$usergrouplist = usergroup_get_list(0);
			
			$uids = array_keys($memberlist);
			$uids = !empty($uids) ? implode(',', $uids) : 0;
			$memberstatuslist = $this->t('member_status')->where("uid IN($uids)")->select();
			if ($memberstatuslist) {
				foreach ($memberstatuslist as $status){
					$memberlist[$status['uid']]['regdate'] = @date('Y-m-d H:i', $status['regdate']);
					$memberlist[$status['uid']]['regip'] = $status['regip'];
					$memberlist[$status['uid']]['lastvisit'] = @date('Y-m-d H:i', $status['lastvisit']);
					$memberlist[$status['uid']]['lastvisitip'] = $status['lastvisitip'];
				}
			}
			unset($memberstatuslist, $status);
			$pages = $this->showPages($G['page'], $pagecount, $totalnum, "field=$field&keyword=$keyword", 1);
			include template('member_list');
		}
	}
	
	/**
	 * 添加用户
	 */
	public function add(){
		global $G,$lang;
		if ($this->checkFormSubmit()) {
			$errno = 0;
			$membernew = $_GET['membernew'];
			cookie('membernew',serialize($membernew),600);
			if ($membernew['username'] && $membernew['password']) {
				$returns = member_register($membernew);
				if ($returns['errno']) {
					$this->showError($returns['error']);
				}else {
					$this->showSuccess('member_add_succeed');
				}
			}else {
				$this->showError('undefined_action');
			}
		}else {
			
			$grouplist = usergroup_get_list(0);
			$member = unserialize(cookie('membernew'));
			include template('member_form');
		}
	}
	
	/**
	 * 编辑用户
	 */
	public function edit(){
		$uid = intval($_GET['uid']);
		if ($this->checkFormSubmit()) {
			
			$membernew = $_GET['membernew'];
			unset($membernew['username']);
			if ($membernew['password']) {
				$membernew['password'] = getPassword($membernew['password']);
			}else {
				unset($membernew['password']);
			}
			
			if ($membernew['email']) {
				if (member_get_num(array('email'=>$membernew['email'])) > 1){
					$this->showError('email_be_occupied');
				}
			}
			
			if ($membernew['mobile']) {
				if (member_get_num(array('mobile'=>$membernew['mobile'])) > 1){
					$this->showError('mobile_be_occupied');
				}
			}
			
			member_update_data(array('uid'=>$uid), $membernew);
			$this->showSuccess('update_succeed');
		}else {
			global $G,$lang;
			$member = member_get_data(array('uid'=>$uid));
			$grouplist  = usergroup_get_list(0);
			include template('member_form');
		}
	}
	
	/**
	 * 移动到分组
	 */
	public function moveto(){
		$uids = trim($_GET['uids']);
		$target = intval($_GET['target']);
		member_update_data(array('uid'=>array('IN', $uids)), array('gid'=>$target));
		$this->showSuccess('update_succeed', U('a=member_list&gid='.$target));
	}
}