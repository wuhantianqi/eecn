<?php
/**
 * Copy Right IJH.CC
 * Each engineer has a duty to keep the code elegant
 * $Id$
 */

if(!defined('__CORE_DIR')){
    exit("Access Denied");
}

class Ctl_Activity_Lanmu extends Ctl
{
    

    public function activity($activity_id=null)
    {
        if(!($activity_id = (int)$activity_id) && !($activity_id = (int)$this->GP('activity_id'))){
            $this->err->add('未指定活动ID', 211);
        }else if(!$activity = K::M('activity/activity')->detail($activity_id)){
            $this->err->add('活动不存在或已经删除', 212);
        }else if(!$this->check_city($activity['city_id'])){
            $this->err->add('不可越权查看', 403);
        }else{
            $this->pagedata['items'] = K::M('activity/lanmu')->items_by_activity($activity_id);
            $this->pagedata['activity'] = $activity;
            $this->tmpl = 'admin:activity/lanmu/activity.html';
        }
    }

    public function create($activity_id=null)
    {
        if(!($activity_id = (int)$activity_id) && !($activity_id = (int)$this->GP('activity_id'))){
            $this->err->add('未指定活动ID', 211);
        }else if(!$activity = K::M('activity/activity')->detail($activity_id)){
            $this->err->add('活动不存在或已经删除', 212);
        }else if(!$this->check_city($activity['city_id'])){
            $this->err->add('不可越权操作', 403);
        }else if($this->checksubmit()){
            if(!$data = $this->GP('data')){
                $this->err->add('非法的数据提交', 201);
            }else{
                $data['activity_id'] = $activity_id;
                if($lanmu_id = K::M('activity/lanmu')->create($data)){
                    $this->err->add('添加内容成功');
                    $this->err->set_data('forward', '?activity/lanmu-activity-'.$activity_id.'.html');
                }
            } 
        }else{
            $this->pagedata['activity'] = $activity;
            $this->tmpl = 'admin:activity/lanmu/create.html';
        }
    }

    public function edit($lanmu_id=null)
    {
        if(!($lanmu_id = (int)$lanmu_id) && !($lanmu_id = $this->GP('lanmu_id'))){
            $this->err->add('未指定要修改的内容ID', 211);
        }else if(!$detail = K::M('activity/lanmu')->detail($lanmu_id)){
            $this->err->add('您要修改的内容不存在或已经删除', 212);
        }else if(!$activity = K::M('activity/activity')->detail($detail['activity_id'])){
            $this->err->add('该活动不存在或已经删除', 212);
        }else if(!$this->check_city($activity['city_id'])){
            $this->err->add('不可越权操作', 403);
        }else if($this->checksubmit('data')){
            if(!$data = $this->GP('data')){
                $this->err->add('非法的数据提交', 201);
            }else{
				unset($data['lanmu_id']);
                if(K::M('activity/lanmu')->update($lanmu_id, $data)){
                    $this->err->add('修改内容成功');
					$this->err->set_data('forward', '?activity/lanmu-activity-'.$detail['activity_id'].'.html');
                }  
            } 
        }else{
            if($activity_id = $detail['activity_id']){
                $this->pagedata['activity'] = K::M('activity/activity')->detail($activity_id);
            }
        	$this->pagedata['detail'] = $detail;
        	$this->tmpl = 'admin:activity/lanmu/edit.html';
        }
    }



    public function delete($lanmu_id=null)
    {
		if($lanmu_id = (int)$lanmu_id){
            if($item = K::M('activity/lanmu')->detail($lanmu_id)){
				if(!$activity = K::M('activity/activity')->detail($item['activity_id'])){
					 $this->err->add('该活动不存在或已经删除', 403);
				}else if(!$this->check_city($activity['city_id'])){
					 $this->err->add('不可越权操作', 403);
				}else if(K::M('activity/lanmu')->delete($lanmu_id)){
                    $this->err->add('删除成功');
                }
            }
        }else if($ids = $this->GP('lanmu_id')){
            if($items = K::M('activity/lanmu')->items_by_ids($ids)){
                $aids =  array();
                foreach($items as $k => $v){
                    if($activity_id = $v['activity_id']){
                        break;
                    }
                }
                if(!$activity = K::M('activity/activity')->detail($activity_id)){
                    $this->err->add('该活动不存在或已经删除', 403);
                }else if(!$this->check_city($activity['city_id'])){
                     $this->err->add('不可越权操作', 403);
                }else{
                    foreach($items as $val){
                        if($val['activity_id'] == $activity_id){
                            $aids[$val['lanmu_id']] = $val['lanmu_id'];
                        }
                    }
                    if($aids && K::M('activity/lanmu')->delete($aids)){
                        $this->err->add('批量删除成功');
                    }
                }                
            }
        }else{
            $this->err->add('未指定要删除的内容ID', 401);
        }
    }

}