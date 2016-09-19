<?php
/**
 * Copy Right IJH.CC
 * Each engineer has a duty to keep the code elegant
 * $Id$
 */

if(!defined('__CORE_DIR')){
    exit("Access Denied");
}

class Ctl_Weixin_Scratch extends Ctl_Weixin
{
	public function index()
    {
        exit();
    }
	
	function show($scratch_id) {

		if(!($scratch_id = (int)$scratch_id) && !($scratch_id = $this->GP('scratch_id'))){
            $this->err->add('没有指定刮刮乐ID', 211);
        }else if(!$detail = K::M('weixin/scratch')->detail($scratch_id)){
            $this->err->add('该刮刮乐不存在或已经删除', 212);
        }else{
			if(empty($openid)){
				$openid = $this->access_openid();
			}
			$client = $this->wechat_client();
			$wx_info = $client->getUserInfoById($openid);

			$member =  K::M('member/weixin')->detail_by_openid($openid);

			$filter['scratch_id'] = $scratch_id;

			if($prizes = K::M('weixin/prize')->items($filter, null, $page, $limit, $count)){
				$this->pagedata['prizes'] = $prizes;
			}
			//$filter['openid'] = $openid;
			

			$all_prizes = K::M('weixin/scratchsn')->items(array('scratch_id'=>$scratch_id),array('sn_id'=>'DESC'));

			foreach ( $all_prizes as $all ) {
				if ($all ['prize_id'] > 0) {
					$has [$all ['prize_id']] += 1; // 每个奖项已经中过的次数
					$new_scratch [] = $all; // 最新中奖记录
					if($all ['openid'] == $openid){
						$scratchsn [] = $all;
					} // 我的中奖记录
				} else {
					$no_count += 1; // 没有中奖的次数
				}
				
				// 记录我已抽奖的次数
				if($all ['openid'] == $openid){
					$my_count += 1;
				} 
			}
			$detail['count'] = $detail['max_num'] - $my_count;
			$error = '';
			if ($detail ['ltime'] <= time ()) {
				$error = '活动已结束';
			} else if($detail ['stime'] >= time ()){
				$error = '活动还未开始';
			}else if ($detail['count']<=0) {
				$error = '您的刮卡机会已用完啦';
			} else if ($detail['follower_condtion'] && $wx_info['subscribe'] == 0) {
				//
				switch ($detail ['follower_condtion']) {
					case 1 :
						$error = '关注后才能领取';
						break;
				}
			}else if ($detail ['member_condtion'] == 1 && !$member['uname']) {
				//
				$error = '用户注册后才能领取';
			} 

			$this->pagedata['error'] = $error;
			
			// 抽奖算法
			if(!$error){
				 $this->_lottery ( $detail, $prizes, $new_scratch, $my_count, $has, $no_count );
			}
			$this->pagedata['scratchsn'] = $scratchsn;
			$this->pagedata['new_scratch'] = $new_scratch;
			$this->pagedata['detail'] = $detail;
			$this->tmpl = 'weixin/scratch/show.html';
		}
		
	}

	// 抽奖算法 中奖概率 = 奖品总数/(预估活动人数*每人抽奖次数)
	function _lottery($data, $prizes, $new_prizes, $my_count = 0, $has = array(), $no_count = 0) {
		$max_num = empty ( $data ['max_num'] ) ? 1 : $data ['max_num'];
		$count = $data ['predict_num'] * $max_num; // 总基数
		                                                    // 获取已经中过的奖
		foreach ( $prizes as $p ) {
			$prizesArr [$p ['id']] = $p;
			
			$prize_num = $p ['num'] - $has [$p ['id']];
			for($i = 0; $i < $prize_num; $i ++) {
				$rand [] = $p ['id']; // 中奖的记录，同时通过ID可以知道中的是哪个奖
			}
		}
		
		if ($data ['predict_num'] != 1) {
			$remain = $count - count ( $rand ) - $no_count;
			$remain > 5000 && $remain = 5000; // 防止数组过大导致内存溢出
			for($i = 0; $i < $remain; $i ++) {
				$rand [] = 0; // 不中奖的记录
			}
		}
		if (empty ( $rand )) {
			$rand [] = - 1;
		}
		
		shuffle ( $rand ); // 所有记录随机排序
		$prize_id = $rand [0]; // 第一个记录作为当前用户的中奖记录
		$prize = array ();
		
		if ($prize_id > 0) {
			$prize = $prizesArr [$prize_id];
		} elseif ($prize_id == - 1) {
			$prize ['id'] = 0;
			$prize ['title'] = '奖品已抽完';
		} else {
			$prize ['id'] = 0;
			$prize ['title'] = '谢谢参与';
		}

		// 获取我的抽奖机会
		if (empty ( $data ['max_num'] )) {
			$prize ['count'] = 1;
		} else {
			$prize ['count'] = $max_num - $my_count - 1;
			$prize ['count'] < 0 && $prize ['count'] = 0;
		}
		$this->pagedata['prize'] = $prize;
	}

	function set_sn_code() {
		if(empty($openid)){
			$openid = $this->access_openid();
		}
		$client = $this->wechat_client();
		$wx_info = $client->getUserInfoById($openid);
		$member =  K::M('member/weixin')->detail_by_openid($openid);

		if(!$_POST['id'] || !$_POST['prize_id']){
			 $this->err->add('数据出错', 212);
		}else{
			$data ['sn'] = uniqid ();
			$data ['uid'] = $member['uid'];
			$data['scratch_id'] = $_POST['id'];
			$data['openid'] = $openid;
			$data['nickname'] = $wx_info['nickname'];
			$data ['prize_id'] = $_POST['prize_id'];
			if (! empty ( $data ['scratch_id'] )) {
				$scratch = K::M('weixin/scratch')->detail($data ['scratch_id']);
				$data['wx_id'] = $scratch['wx_id'];
			}
			$title = '';
			if (! empty ( $data ['prize_id'] )) {
				$items = K::M('weixin/prize')->detail($data ['prize_id']);
			}
			$data ['prize_title'] = $items['title'];
			K::M('weixin/scratchsn')->create($data);
			echo $res;
		}
	}
}