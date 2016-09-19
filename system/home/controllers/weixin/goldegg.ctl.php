<?php
/**
 * Copy Right IJH.CC
 * Each engineer has a duty to keep the code elegant
 * $Id$
 */

if(!defined('__CORE_DIR')){
    exit("Access Denied");
}

class Ctl_Weixin_Goldegg extends Ctl_Weixin
{
	public function index()
    {
        exit();
    }
	
	function show($goldegg_id) {

		if(!($goldegg_id = (int)$goldegg_id) && !($goldegg_id = $this->GP('goldegg_id'))){
            $this->err->add('没有指定砸金蛋ID', 211);
        }else if(!$detail = K::M('weixin/goldegg')->detail($goldegg_id)){
            $this->err->add('该砸金蛋不存在或已经删除', 212);
        }else{
			if(empty($openid)){
				$openid = $this->access_openid();
			}
			$client = $this->wechat_client();
			$wx_info = $client->getUserInfoById($openid);

			$member =  K::M('member/weixin')->detail_by_openid($openid);
			
			$filter['goldegg_id'] = $goldegg_id;
			$filter['openid'] = $openid;

			$now = time();
			$year=date('Y',$now);
			$month=date('m',$now);
			$day=date('d',$now);
			$firstSecond=mktime(0,0,0,$month,$day,$year);
			$lastSecond=mktime(23,59,59,$month,$day,$year);
			$filter['dateline']= $firstSecond.'~'.$lastSecond;

			if($record = K::M('weixin/goldeggsn')->items($filter, null, $page, $limit, $count)){
				$this->pagedata['record'] = $record;
			}
			
			$data=$detail;

			// 1. 中过奖金	
			if ($record['isegg'] == 1) {				
				$data['end'] = 5;
				$data['sn']	 	 = $record['sn'];
				$data['uname']	 = $record['wx_id'];
				$data['prize']	 = $record['prize'];
				$data['openid'] 	 = $record['openid'];	
			}

			if (!empty ( $detail ['stime'] ) && $detail ['stime'] >= time ()) {
				$error = '该活动还没开始';
			}else if (!empty ( $detail ['ltime'] ) && $detail ['ltime'] <= time ()) {
				$error = '您来晚啦';
			}else if ($detail ['max_count'] > 0 && $detail ['max_count'] <= count($list)) {
				$error = '您的领取名额已用完啦';
			}else if ($detail ['follower_condtion'] == 1 && $wx_info['subscribe'] == 0) {
				switch ($detail ['follower_condtion']) {
					case 1 :
						$error = '关注后才能领取';
						break;
				}
			}else if ($detail ['member_condtion'] == 1 && !$member['uname']) {
				$error = '用户注册后才能领取';
			}
			$this->pagedata['error'] = $error;
			$data['On'] 		= 1;
			$data['wx_id']	    = $detail['wx_id'];		
			$data['lid']		= $detail['id'];
			$data['rid']		= intval($record['sn_id']);
			$data['usenums'] 	= $count;
			$data['info']=str_replace('&lt;br&gt;','<br>',$data['info']);
			$data['endinfo']=str_replace('&lt;br&gt;','<br>',$data['end_tips']);
			$record['id']=intval($record['sn_id']);
			$this->pagedata['Dazpan'] = $data;
			$this->pagedata['record'] = $record;
			$this->tmpl = 'weixin/goldegg/show.html';
		}
		
	}

	public function getajax()
	{	
		
		$wx_id	=	$this->GP('wx_id');
		$id 		=	$this->GP('id');
		$rid 		= 	$this->GP('rid');
		if(empty($openid)){
			$openid = $this->access_openid();
		}
		$client = $this->wechat_client();
		$wx_info = $client->getUserInfoById($openid);

		$member =  K::M('member/weixin')->detail_by_openid($openid);
		$uid = $member['uid'];

		$Lottery= K::M('weixin/goldegg')->detail($id);
		if ($Lottery['stime']>time()){
			echo '{"error":1,"msg":"活动还没开始，感谢关注"}';
			exit;
		}
		if ($Lottery['ltime']<time()){
			echo '{"error":1,"msg":"活动已经结束，感谢关注"}';
			exit;
		}

		if ($Lottery ['follower_condtion'] == 1 && $wx_info['subscribe'] == 0) {
			echo '{"error":1,"msg":"关注后才能领取"}';
			exit;
		}
		if ($detail ['follower_condtion'] == 1 && !$member['uname']) {
			echo '{"error":1,"msg":"用户注册后才能领取"}';
			exit;
		}
		$data=$this->prizeHandle($uid,$wx_id,$Lottery);
		if ($data['end']==3){
			$sn	 	 = $data['sn'];
			$prize	 = $data['prize'];
			$tel 	 = $data['phone'];
			$msg = "您已经中过了";
			echo '{"error":1,"msg":"'.$msg.'"}';
			exit;
		}
		if ($data['end']==-1){
			$msg = $data['winprize'];
			echo '{"error":1,"msg":"'.$msg.'"}';
			exit;
		}
		if ($data['end']==-2){
			$msg = $data['winprize'];
			echo '{"error":1,"msg":"'.$msg.'"}';
			exit;
		}
		
		if ($data['prizetype'] >= 1 && $data['prizetype'] <= 6) {
			echo '{"success":1,"sn":"'.$data['sncode'].'","prizetype":"'.$data['prizetype'].'","usenums":"'.$data['usenums'].'"}';
		}else{
			echo '{"success":0,"prizetype":"","usenums":"'.$data['usenums'].'"}';
		}
		exit();
	}

	public function getPrizeName($Lottery,$prize)
	{
		$array = array('1'=>'frist','2'=>'second','3'=>'third','4'=>'four','5'=>'five','6'=>'six');
		return $Lottery[$array[$prize]];
	}

	public function prizeHandle($uid,$wx_id,$Lottery)
	{	
		if(empty($openid)){
			$openid = $this->access_openid();
		}
		$client = $this->wechat_client();
		$wx_info = $client->getUserInfoById($openid);

		$member =  K::M('member/weixin')->detail_by_openid($openid);
		

		$now=time();
		$id 		= $Lottery['id'];
		$isday = 0;
		
		$where 		= array('openid'=>$openid);
		if($record 	= K::M('weixin/goldeggsn')->items($where,null,null,null,$count)){
			$record['usenums'] = $count;
		}else{
			$record['usenums'] = 0;
		}
		
			if ($Lottery['ltime'] < $now) { //过期
				$data['end'] = 2;
				$data['end_tips'] = $Lottery['end_tips'];
				$data['end_photo']  = empty($Lottery['end_photo']) ? 1 : $Lottery['end_photo'];
			}else{
				//最多抽奖次数
				$LotteryedRecordWhere=array('wx_id'=>$wx_id,'egg_id'=>$id,'isegg'=>1);
				K::M('weixin/goldeggsn')->items($LotteryedRecordWhere,null,null,null,$prizedCount);
				if ($prizedCount >=1){
					$data['end'] = 3;
					$data['msg'] = "您已经中过奖了，不能再领取了，谢谢";
					$data['wxname']=$record['wx_id'];
					$data['wecha_name']=$record['nickname'];
					$data['sn']  = $record['sn'];
					$data['myprize'] 	= $this->getPrizeName($Lottery,$record['prize']);
					$data['prize'] 	= $record['prize'];
				}else {
					//是否已经够次数
					if ($record['usenums'] >= $Lottery['max_num'] ) {
						$data['end'] = -1;
						$data['prizetype'] = 4; //啥都没了
						$data['zjl']	  = 0;
						$data['usenums']  = $record['usenums'];
						$data['winprize']	   = "抽奖次数已经用完";
						//exit;
					}else{
						//当天的次数
						$year=date('Y',$now);
						$month=date('m',$now);
						$day=date('d',$now);
						$firstSecond=mktime(0,0,0,$month,$day,$year);
						$lastSecond=mktime(23,59,59,$month,$day,$year);
						foreach($record as $k => $v){
							if($v['dateline']>=$firstSecond && $v['dateline']<=$lastSecond){
								$thisDayNums++;
							}
						}
						if ($thisDayNums>=$Lottery['daynums']){
							$data['end'] = -2; //
							$data['zjl']	  = 0;
							$data['winprize']	   = "今天已经抽了".$thisDayNums."次了，没名额了，明天再来吧";
						}else {
							//3.没有领过,次数没达到,开始随机发放优惠券
							if($Lottery['fistlucknums']  == $Lottery['fistnums'] &&
							$Lottery['secondlucknums'] == $Lottery['secondnums'] &&
							$Lottery['thirdlucknums']  == $Lottery['thirdnums'] &&
							$Lottery['fourlucknums']  == $Lottery['fournums'] &&
							$Lottery['fivelucknums']  == $Lottery['fivenums'] &&
							$Lottery['sixlucknums']  == $Lottery['sixnums']
							){
								$prizeType=7;
							}else{
								$prizeType=intval($this->get_prize($Lottery));
							}
							//排除没有设置的优惠券
							//奖品数 != 已经领取该奖品数 => 还有奖品

							switch ($prizeType){
								default:
									$data['prizetype'] = 7; //啥都没了
									$data['zjl']	  = 0;
									$data['winprize']	   = "谢谢参与";
									$isegg=0;
									$data['sncode']    = '';
									break;
								case 1:
									$data['prizetype'] = 1;
									$data['sncode'] = uniqid();
									$data['winprize']	   = $Lottery['fist'];
									$data['zjl']	   = 1;
									K::M('weixin/goldegg')->update_count($id, 'fistlucknums', 1);
									$isegg=1;
									break;
								case 2:
									$data['prizetype'] = 2;
									$data['winprize']  = $Lottery['second'];
									$data['zjl']	   = 1;
									$data['sncode']    = uniqid();
									K::M('weixin/goldegg')->update_count($id, 'secondlucknums', 1);
									$isegg=1;
									break;
								case 3:
									$data['prizetype'] = 3;
									$data['winprize']	   = $Lottery['third'];
									$data['zjl']	   = 1;
									$data['sncode'] = uniqid();
									K::M('weixin/goldegg')->update_count($id, 'thirdlucknums', 1);
									$isegg=1;
									break;
								case 4:
									$data['prizetype'] = 4;
									$data['winprize']	   = $Lottery['four'];
									$data['zjl']	   = 1;
									$data['sncode'] = uniqid();
									K::M('weixin/goldegg')->update_count($id, 'fourlucknums', 1);
									$isegg=1;
									break;
								case 5:
									$data['prizetype'] = 5;
									$data['winprize']	   = $Lottery['five'];
									$data['zjl']	   = 1;
									$data['sncode'] = uniqid();
									K::M('weixin/goldegg')->update_count($id, 'fivelucknums', 1);
									$isegg=1;
									break;
								case 6:
									$data['prizetype'] = 6;
									$data['winprize']	   = $Lottery['six'];
									$data['zjl']	   = 1;
									$data['sncode'] = uniqid();
									K::M('weixin/goldegg')->update_count($id, 'sixlucknums', 1);
									$isegg=1;
									break;
							}
							//
							$data_sn = array('uid'=>$uid,'openid'=>$openid,'wx_id'=>$wx_id,'egg_id'=>$Lottery['id'],'nickname'=>$wx_info['nickname'],'isegg'=>$isegg,'sn'=>$data['sncode'],'prize'=>intval($data['prizetype']));
							
							$sn_id = K::M('weixin/goldeggsn')->create($data_sn);
							
							//
							//$this->lottery_record_db->where(array('lid'=>$id,'wecha_id'=>$wecha_id))->setInc('usenums');
							//$record['usenums']=intval($record['usenums'])+1;
						}

					}//以上没领过
				}
			}
		//}
		//
		$record 	=  K::M('weixin/goldeggsn')->detail($sn_id);
		K::M('weixin/goldeggsn')->items(array('openid'=>$openid),null,null,null,$count);
		
		$data['rid']		= intval($record['sn_id']);
		$data['sn']		= $record['sn'];
		$data['dateline']	= $record['dateline'];
		$data['usenums']	= $count;
		return $data;
	}

	protected function get_prize($Lottery){
		$id=intval($Lottery['id']);
		
		$firstNum=intval($Lottery['fistnums'])-intval($Lottery['fistlucknums']);
		$secondNum=intval($Lottery['secondnums'])-intval($Lottery['secondlucknums']);
		$thirdNum=intval($Lottery['thirdnums'])-intval($Lottery['thirdlucknums']);
		$fourthNum=intval($Lottery['fournums'])-intval($Lottery['fourlucknums']);
		$fifthNum=intval($Lottery['fivenums'])-intval($Lottery['fivelucknums']);
		$sixthNum=intval($Lottery['sixnums'])-intval($Lottery['sixlucknums']);
		$multi=intval($Lottery['max_num']);//最多抽奖次数
		$prize_arr = array(
			'0' => array('id'=>1,'prize'=>'一等奖','v'=>$firstNum,'start'=>0,'end'=>$firstNum), 
			'1' => array('id'=>2,'prize'=>'二等奖','v'=>$secondNum,'start'=>$firstNum,'end'=>$firstNum+$secondNum), 
			'2' => array('id'=>3,'prize'=>'三等奖','v'=>$thirdNum,'start'=>$firstNum+$secondNum,'end'=>$firstNum+$secondNum+$thirdNum),
			'3' => array('id'=>4,'prize'=>'四等奖','v'=>$fourthNum,'start'=>$firstNum+$secondNum+$thirdNum,'end'=>$firstNum+$secondNum+$thirdNum+$fourthNum),
			'4' => array('id'=>5,'prize'=>'五等奖','v'=>$fifthNum,'start'=>$firstNum+$secondNum+$thirdNum+$fourthNum,'end'=>$firstNum+$secondNum+$thirdNum+$fourthNum+$fifthNum),
			'5' => array('id'=>6,'prize'=>'六等奖','v'=>$sixthNum,'start'=>$firstNum+$secondNum+$thirdNum+$fourthNum+$fifthNum,'end'=>$firstNum+$secondNum+$thirdNum+$fourthNum+$fifthNum+$sixthNum),
			'6' => array('id'=>7,'prize'=>'谢谢参与','v'=>(intval($Lottery['predict_num']))*$multi-($firstNum+$secondNum+$thirdNum+$fourthNum+$fifthNum+$sixthNum),'start'=>$firstNum+$secondNum+$thirdNum+$fourthNum+$fifthNum+$sixthNum,'end'=>intval($Lottery['predict_num'])*$multi)
		);
		//
		foreach ($prize_arr as $key => $val) { 
			$arr[$val['id']] = $val; 
		} 
		//-------------------------------	 
		//随机抽奖[如果预计活动的人数为1为各个奖项100%中奖]
		//-------------------------------	 
		if ($Lottery['predict_num'] == 1) {
	 
			if ($Lottery['fistlucknums'] <= $Lottery['fistnums']) {
				$prizetype = 1;	
			}else{
				$prizetype = 7;	
			}			
			 
		}else{
			$prizetype = $this->get_rand($arr,(intval($Lottery['predict_num'])*$multi)); 
		}
		 
		//$winprize = $prize_arr[$rid-1]['prize'];
		switch($prizetype){
			case 1:
					 
				if ($Lottery['fistlucknums'] >= $Lottery['fistnums']) {
					 $prizetype = ''; 
					 //$winprize = '谢谢参与'; 
				}else{
					 
					$prizetype = 1; 					
				    //$lottery_db->where(array('id'=>$id))->setInc('fistlucknums');
				}
			break;
				
			case 2:
				if ($Lottery['secondlucknums'] >= $Lottery['secondnums']) {
						$prizetype = ''; 
						//$winprize = '谢谢参与';
				}else{
					//判断是否设置了2等奖&&数量
					if(empty($Lottery['second']) && empty($Lottery['secondnums'])){
						$prizetype = ''; 
						//$winprize = '谢谢参与';
					}else{ //输出中了二等奖
						$prizetype = 2; 					
						//$lottery_db->where(array('id'=>$id))->setInc('secondlucknums');
					}	 
					
				}
				break;
							
			case 3:
				if ($Lottery['thirdlucknums'] >= $Lottery['thirdnums']) {
					 $prizetype = ''; 
					// $winprize = '谢谢参与';
				}else{
					if(empty($Lottery['third']) && empty($Lottery['thirdnums'])){
						 $prizetype = ''; 
						// $winprize = '谢谢参与';
					}else{
						$prizetype = 3; 					
						//$lottery_db->where(array('id'=>$id))->setInc('thirdlucknums');
					} 
					
				}
				break;
						
			case 4:
				if ($Lottery['fourlucknums'] >= $Lottery['fournums']) {
					  $prizetype =  ''; 
					// $winprize = '谢谢参与';
				}else{
					 if(empty($Lottery['four']) && empty($Lottery['fournums'])){
					   	$prizetype =  ''; 
					 	//$winprize = '谢谢参与';
					 }else{
					 	$prizetype = 4; 					
						//$lottery_db->where(array('id'=>$id))->setInc('fourlucknums');
					 }					
				}
			break;
			
			case 5:
				if ($Lottery['fivelucknums'] >= $Lottery['fivenums']) {
					 $prizetype =  ''; 
					 //$winprize = '谢谢参与';
				}else{
					if(empty($Lottery['five']) && empty($Lottery['fivenums'])){
						$prizetype =  ''; 
					 	//$winprize = '谢谢参与';
					}else{
						$prizetype = 5; 					
						//$lottery_db->where(array('id'=>$id))->setInc('fivelucknums');
					} 
				}
			break;
			
			case 6:
				if ($Lottery['sixlucknums'] >= $Lottery['sixnums']) {
					 $prizetype =  ''; 
					// $winprize = '谢谢参与';
				}else{
					 if(empty($Lottery['six']) && empty($Lottery['sixnums'])){
					 	$prizetype =  ''; 
					 	//$winprize = '谢谢参与';
					 }else{
					 	$prizetype = 6; 					
						//$lottery_db->where(array('id'=>$id))->setInc('sixlucknums');
					 }
					
				}
			break;
							
			default:
					$prizetype =  ''; 
					//$winprize = '谢谢参与';
					
					break;
		}
		if (intval($prizetype)&&$prizetype<7){
			//M('Lottery_record')->where(array('lid'=> $id,'wecha_id'=>$this->_get('wecha_id')))->save(array('isegg'=>1));
		}
		
		return $prizetype;
	}

	protected function get_rand($proArr,$total) {

		    $result = 7; 
		    $randNum = mt_rand(1, $total); 
		    foreach ($proArr as $k => $v) {
		    	
		    	if ($v['v']>0){//奖项存在或者奖项之外
		    		if ($randNum>$v['start']&&$randNum<=$v['end']){
		    			$result=$k;
		    			break;
		    		}
		    	}
		    }

		    return $result; 
	}

	
}