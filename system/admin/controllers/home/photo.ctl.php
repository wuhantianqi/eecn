<?php
/**
 * Copy Right IJH.CC
 * Each engineer has a duty to keep the code elegant
 * $Id: photo.ctl.php 5610 2014-06-23 16:47:52Z youyi $
 */

if(!defined('__CORE_DIR')){
    exit("Access Denied");
}

class Ctl_Home_Photo extends Ctl
{
    
    public function index($home_id=0,$type=0)
    {   
        $home_id = (int)$home_id;
        if(!$detail = K::M('home/home')->detail($home_id)){
            $this->err->add('该小区不存在或已经删除', 212);
        }else if(!$this->check_city($detail['city_id'])){
            $this->err->add('不可越权查看', 403);
        }
        $this->pagedata['detail']  = $detail;
        $this->pagedata['home_id'] = $filter['home_id'] = $home_id;
        $this->pagedata['type'] = $filter['type'] = (int)$type;
        $this->pagedata['typeCfg'] = K::M('home/photo')->get_type();
        $this->pagedata['items'] =  K::M('home/photo')->items($filter, null, 1, 50, $count);
        $this->tmpl = 'admin:home/photo/items.html';
    }

    public function dialog($home_id=0,$page=1)
    {
        $home_id = (int)$home_id;
        if(!$detail = K::M('home/home')->detail($home_id)){
            $this->err->add('该小区不存在或已经删除', 212);
        }else if(!$this->check_city($detail['city_id'])){
            $this->err->add('不可越权查看', 403);
        }
        $filter = $pager = array();
        $pager['page'] = max(intval($page), 1);
        $pager['limit'] = $limit = 10;
        $pager['multi'] = $multi = ($this->GP('multi') == 'Y' ? 'Y' : 'N');
        if($SO = $this->GP('SO')){
            $pager['SO'] = $SO;
            if($SO['id']){$filter['id'] = $SO['id'];}
            if($SO['city_id']){$filter['city_id'] = $SO['city_id'];}
            if($SO['area_id']){$filter['area_id'] = $SO['area_id'];}
            if($SO['home_id']){$filter['home_id'] = $SO['home_id'];}
        }      
        if($home_id ){
            $filter['home_id'] = (int)$home_id;
        }
        $filter['type'] = '1';//户型图
        $filter['closed'] = 0;
        if($items = K::M('home/photo')->items($filter, null, $page, $limit, $count)){
            $pager['count'] = $count;
            $pager['pagebar'] = $this->mkpage($count, $limit, $page, $this->mklink(null, array($home_id ,'{page}')), array('SO'=>$SO, 'multi'=>$multi));
        }
        $this->pagedata['items'] = $items;
        $this->pagedata['pager'] = $pager;    
        $this->tmpl = 'admin:home/photo/dialog.html';         
    }
    
    public function upload()
    {   
        //越权判断在哪里 add by shzhrui 2014-10-31 00:34:20
        if(!$home_id = $this->GP('home_id')){
            $this->err->add('非法的参数请求', 201);
        }else if(!$home = K::M('home/home')->detail($home_id)){
            $this->err->add('小区不存在或已经删除', 202);
        }else if(!$this->check_city($home['city_id'])){
            $this->err->add('不可越权查看', 403);
        }else if(!$type = $this->GP('type')){
             $this->err->add('非法的参数请求', 201);
        }else if(!$attach = $_FILES['Filedata']){
            $this->err->add('上传图片失败', 401);
        }else if(UPLOAD_ERR_OK != $attach['error']){
            $this->err->add('上传图片失败', 402);
        }else{
            
            if($a = K::M('home/photo')->upload($home_id,$type, $attach)){               
                $cfg = $this->system->config->get('attach');
                $this->err->set_data('thumb', $cfg['attachurl'].'/'.$a['photo']);
                $this->err->add('上传图片成功');                
            }
        }
        $this->err->json();
    }
    
    
    public function update()
    {   
        if($this->checksubmit()){
            $titles = $this->GP('title');
            $orderbys = $this->GP('orderby');
            $home_id = 0;
            $pids = array();
            foreach($titles as $k=>$v){
                $pids[$k] = $k;
            }
            if($items = K::M('home/photo')->items_by_ids($pids)){
                foreach($items as $v){
                    if($home_id = $v['home_id']){
                        break;
                    }
                }
                if(!$home = K::M('home/home')->detail($home_id)){
                    $this->err->add('该小区不存在或已经删除', 403);
                }else if(!$this->check_city($home['city_id'])){
                    $this->err->add('不可越权操作', 403);
                }else{
                    $count = 0;
                    foreach($items as $v){
                        if($v['home_id'] == $home_id){
                            if($v['title'] != $titles[$v['photo_id']] || $v['orderby'] != $orderbys[$v['photo_id']]){
                                K::M('home/photo')->update($v['photo_id'], array('title'=>$titles[$v['photo_id']], 'orderby'=>$orderbys[$v['photo_id']]));
                                $count ++;
                            }
                        }
                    }
                    $this->err->add('更新数据成功');
                }
            }
        }else{
            $this->err->add('非法的数据提交', 211);
        }
        
    }

    public function delete($photo_id=0)
    {
        if($photo_id = (int)$photo_id){
            if($photo = K::M('home/photo')->detail($photo_id)){
                if(!$home = K::M('home/home')->detail($photo['home_id'])){
                     $this->err->add('该小区不存在或已经删除', 403);
                }else if(!$this->check_city($home['city_id'])){
                     $this->err->add('不可越权操作', 403);
                }else if(K::M('home/photo')->delete($photo_id)){
                    $this->err->add('删除成功');
                }
            }
        }else if($ids = $this->GP('photo_id')){
            if($items = K::M('home/photo')->items_by_ids($ids)){
                $aids = $home =  array();
                foreach($items as $k => $v){
                    if($home_id = $v['home_id']){
                        break;
                    }
                }
                if(!$home = K::M('home/home')->detail($home_id)){
                    $this->err->add('该小区不存在或已经删除', 403);
                }else if(!$this->check_city($home['city_id'])){
                     $this->err->add('不可越权操作', 403);
                }else{
                    foreach($items as $val){
                        if($val['home_id'] == $home_id){
                            $aids[$val['photo_id']] = $val['photo_id'];
                        }
                    }
                    if($aids && K::M('home/photo')->delete($aids)){
                        $this->err->add('批量删除成功');
                    }
                }                
            }
        }else{
            $this->err->add('未指定要删除的内容ID', 401);
        }
    }

}