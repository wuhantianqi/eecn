<?php
/**
 * Copy Right IJH.CC
 * Each engineer has a duty to keep the code elegant
 * Author @shzhrui<Anhuike@gmail.com>
 * $Id: photo.ctl.php 2034 2013-12-07 03:08:33Z $
 */

if(!defined('__CORE_DIR')){
    exit("Access Denied");
}

class Ctl_Case_Photo extends Ctl
{
    
    public function upload()
    {
        if(!$case_id = (int)$this->GP('case_id')){
            $this->err->add('非法的参数请求', 201);
        }else if(!$case = K::M('case/case')->detail($case_id)){
            $this->err->add('案例不存在或已经删除', 202);
        }else if(!$this->check_city($case['city_id'])){
            $this->err->add('不可越权操作', 403);
        }else if(!$attach = $_FILES['Filedata']){
            $this->err->add('上传图片失败', 401);
        }else if(UPLOAD_ERR_OK != $attach['error']){
            $this->err->add('上传图片失败', 402);
        }else{
            if($data = K::M('case/photo')->upload($case_id, $attach)){
                $cfg = $this->system->config->get('attach');
                $this->err->set_data('photo', $cfg['attachurl'].'/'.$data['photo']);
				K::M('case/photo')->phone_count($case['case_id']);
                $this->err->add('上传图片成功');
            }
        }
        $this->err->json();
    }    

    public function delete($photo_id=null)
    {
		if($photo_id = (int)$photo_id){
            if($photo = K::M('case/photo')->detail($photo_id)){
				if(!$case = K::M('case/case')->detail($photo['case_id'])){
					 $this->err->add('该案例不存在或已经删除', 403);
				}else if(!$this->check_city($case['city_id'])){
					 $this->err->add('不可越权操作', 403);
				}else if(K::M('case/photo')->delete($photo_id)){
					K::M('case/photo')->phone_count($case['case_id']);
                    $this->err->add('删除成功');
                }
            }
        }else if($ids = $this->GP('photo_id')){
           if($items = K::M('case/photo')->items_by_ids($ids)){
                $aids  =  array();
                foreach($items as $k => $v){
                    if($case_id = $v['case_id']){
                        break;
                    }
                }
                if(!$case = K::M('case/case')->detail($case_id)){
                    $this->err->add('该案例不存在或已经删除', 403);
                }else if(!$this->check_city($case['city_id'])){
                     $this->err->add('不可越权操作', 403);
                }else{
                    foreach($items as $val){
                        if($val['case_id'] == $case_id){
                            $aids[$val['photo_id']] = $val['photo_id'];
							$case_ids[$v['case_id']] = $v['case_id'];
                        }
                    }
                    if($aids && K::M('case/photo')->delete($aids)){
						K::M('case/photo')->phone_count($case_ids);
                        $this->err->add('批量删除成功');
                    }
                }                
            }
        }else{
            $this->err->add('未指定要删除的内容ID', 401);
        }
    }

    public function update($case_id)
    {
        if($this->checksubmit('data')){
            if($data = $this->GP('data')){
				$detail = K::M('case/case')->detail($case_id);
                $obj = K::M('case/photo');
                foreach($data as $k=>$v){
                    $obj->update($k, array('title'=>$v['title'],'city_id' =>$detail['city_id'] , 'orderby'=>(int)$v['orderby']));
                }
            }
            $this->err->add('更新数据成功');
        }

		
    }

}