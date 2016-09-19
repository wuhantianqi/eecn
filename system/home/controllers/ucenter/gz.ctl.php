<?php
/**
 * Copy Right IJH.CC
 * Each engineer has a duty to keep the code elegant
 * $Id$
 */

if(!defined('__CORE_DIR')){
    exit("Access Denied");
}

class Ctl_Ucenter_Gz extends Ctl_Ucenter 
{
    protected $_allow_fields = 'city_id,name,group_id,area_id,qq,about,mobile,slogan';

    public function index()
    {
        $this->info();
    } 

	public function refresh()
    {
		$gz = $this->ucenter_gz();
		$integral = K::$system->config->get('integral');
		$counts = K::M('member/flush')->flushs($this->uid);
		$is_gold = abs($integral['gold']);
		if($counts >= $gz["group"]["priv"]["day_free_count"]){
			$this->pagedata['gold'] = $is_gold;
		}
		$this->pagedata['is_refresh'] = $counts;
		$this->pagedata['counts'] = $gz["group"]["priv"]["day_free_count"];
		if($this->GP('fromid')){
			$isrefresh = true;
			if($counts >= $gz["group"]["priv"]["day_free_count"]){
				if($this->MEMBER['gold']<$is_gold){
					$isrefresh = false;
					$this->err->add('您的金币余额不足，请先充值', 215);
				}
			}
			$data['gold'] = '0';
			if($isrefresh && $counts >= $gz["group"]["priv"]["day_free_count"]){
				$data['gold'] = $is_gold;
				if($is_gold > 0){
                    if(!K::M('member/gold')->update($this->uid, -$is_gold, "刷新工长")){
						$isrefresh = false;
                        $this->err->add('扣费失败', 201)->response();
                    }
                }
			}
			$data['uid'] = $this->uid;$data['from'] = 'gz';$data['itemId'] = $gz['uid'];
			if($isrefresh && K::M('member/flush')->create($data)){
				K::M('gz/gz')->update($gz['uid'], array('flushtime'=>__TIME));
				$this->err->add('刷新成功');
			}

		}else{
			$this->pagedata['fromid'] = $gz['uid'];		
			$this->tmpl = 'ucenter/gz/refresh/look.html';
		}
	}

	public function info()
    {
        $gz = $this->ucenter_gz();
		if($data = $this->checksubmit('data')){
			if(!$data = $this->check_fields($data, $this->_allow_fields)){
				$this->err->add('非法的数据提交', 211);
			}else{
				if(!$gz['gz_id']){
					$data['uid'] = $this->uid;
					if($group = K::M('member/group')->default_group('gz')){
						$data['group_id'] = $group['group_id'];
					}
					K::M('gz/gz')->create($data);
					K::M('member/member')->update($gz['uid'],array('group_id'=>$data['group_id']));
                }else{
					K::M('gz/gz')->update($this->uid, $data);
				}
				$this->err->add('工长资料设置成功');
			}
		}else{
			$this->tmpl = 'ucenter/gz/info.html';
		}        
    }

	public function attr()
    {
        $designer = $this->ucenter_gz();
        if($attr = $this->checksubmit('attr')){
            K::M('gz/attr')->update($this->uid, $attr);
            $this->err->add('工长属性设置成功');
        }else{
            $this->pagedata['attr'] = K::M('gz/attr')->attrs_ids_by_gz($this->uid);
            $this->tmpl = 'ucenter/gz/attr.html';
        }         
    }
}