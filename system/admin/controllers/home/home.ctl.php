<?php
/**
 * Copy Right IJH.CC
 * Each engineer has a duty to keep the code elegant
 * $Id$
 */

if(!defined('__CORE_DIR')){
    exit("Access Denied");
}

class Ctl_Home_Home extends Ctl
{
    
    public function index($page=1)
    {
    	$filter = $pager = array();
    	$pager['page'] = max(intval($page), 1);
    	$pager['limit'] = $limit = 50;
        if($SO = $this->GP('SO')){
            $pager['SO'] = $SO;
            if($SO['home_id']){$filter['home_id'] = $SO['home_id'];}
            if($SO['city_id']){$filter['city_id'] = $SO['city_id'];}
            if($name = trim($SO['name'])){
                $filter[':OR'] = array('title'=>"LIKE:%".$name."%", 'name'=>"LIKE:%".$name."%");
            }
            if(is_numeric($SO['audit'])){$filter['audit'] = $SO['audit'];}
            if(is_array($SO['dateline'])){if($SO['dateline'][0] && $SO['dateline'][1]){$a = strtotime($SO['dateline'][0]); $b = strtotime($SO['dateline'][1])+86400;$filter['dateline'] = $a."~".$b;}}
        }
        $filter['closed'] = 0;
        if($items = K::M('home/home')->items($filter, null, $page, $limit, $count)){
        	$pager['count'] = $count;
        	$pager['pagebar'] = $this->mkpage($count, $limit, $page, $this->mklink(null, array('{page}')), array('SO'=>$SO));
        }
		$this->pagedata['cityList'] = K::M("data/city")->fetch_all();
		$this->pagedata['areaList'] = K::M("data/area")->fetch_all();
        $this->pagedata['items'] = $items;
        $this->pagedata['pager'] = $pager;
        $this->tmpl = 'admin:home/home/items.html';
    }

    public function so($target=null, $multi=null)
    {
		if($target){
            $pager['multi'] = $multi == 'Y' ? 'Y' : 'N';
            $pager['target'] = $target;
        }
        $this->pagedata['pager'] = $pager;  
        $this->tmpl = 'admin:home/home/so.html';
    }

	public function dialog($page=1)
    {
        $filter = $pager = array();
        $pager['page'] = max(intval($page), 1);
        $pager['limit'] = $limit = 10;
        $pager['multi'] = $multi = ($this->GP('multi') == 'Y' ? 'Y' : 'N');
        if($SO = $this->GP('SO')){
            $pager['SO'] = $SO;
            if($SO['id']){$filter['id'] = $SO['id'];}
            if($SO['city_id']){$filter['city_id'] = $SO['city_id'];}
            if($SO['area_id']){$filter['area_id'] = $SO['area_id'];}
            if($SO['name']){$filter['name'] = "LIKE:%".$SO['name']."%";}
            if($SO['tel']){$filter['tel'] = "LIKE:%".$SO['tel']."%";}
            if($SO['kf']){$filter['kf'] = "LIKE:%".$SO['kf']."%";}
            if(is_array($SO['lng'])){$a = intval($SO['lng'][0]);$b=intval($SO['lng'][1]);if($a && $b){$filter['lng'] = $a."~".$b;}}
            if(is_array($SO['lat'])){$a = intval($SO['lat'][0]);$b=intval($SO['lat'][1]);if($a && $b){$filter['lat'] = $a."~".$b;}}
        }
        $filter['closed'] = 0;
		if(CITY_ID){
            $filter['city_id'] = CITY_ID;
        }
        if($items = K::M('home/home')->items($filter, null, $page, $limit, $count)){
            $pager['count'] = $count;
            $pager['pagebar'] = $this->mkpage($count, $limit, $page, $this->mklink(null, array('{page}')), array('SO'=>$SO, 'multi'=>$multi));
        }
        $this->pagedata['items'] = $items;
        $this->pagedata['pager'] = $pager;
        $this->pagedata['cityList'] = K::M("data/city")->fetch_all();
        $this->pagedata['areaList'] = K::M("data/area")->fetch_all();        
        $this->tmpl = 'admin:home/home/dialog.html';           
    }

    public function create()
    {
        if($this->checksubmit()){
            if(!$data = $this->GP('data')){
                $this->err->add('非法的数据提交', 201);
            }else{
				if($_FILES['data']){
					foreach($_FILES['data'] as $k=>$v){
						foreach($v as $kk=>$vv){
							$attachs[$kk][$k] = $vv;
						}
					}
					$upload = K::M('magic/upload');
					foreach($attachs as $k=>$attach){
						if($attach['error'] == UPLOAD_ERR_OK){
							if($a = $upload->upload($attach, 'home')){
								$data[$k] = $a['photo'];
							}
						}
					}
				}
				if(CITY_ID){
                    $data['city_id'] = CITY_ID;
                }
				$data['lat'] = trim($data['lat']);
                if($home_id = K::M('home/home')->create($data)){
                    if($attr=  $this->GP('attr')){
                        K::M('home/attr')->update($home_id,$attr);  
						
                    }
                    $this->err->add('添加内容成功');
                    $this->err->set_data('forward', '?home/home-index.html');
                }
            } 
        }else{
           $this->tmpl = 'admin:home/home/create.html';
        }
    }

    public function edit($home_id=null)
    {
        if(!($home_id = (int)$home_id) && !($home_id = $this->GP('home_id'))){
            $this->err->add('未指定要修改的内容ID', 211);
        }else if(!$detail = K::M('home/home')->detail($home_id)){
            $this->err->add('您要修改的内容不存在或已经删除', 212);
        }else if(!$this->check_city($detail['city_id'])){
            $this->err->add('不可越权操作', 403);
        }else if($this->checksubmit('data')){
            if(!$data = $this->GP('data')){
                $this->err->add('非法的数据提交', 201);
            }else{
                if($_FILES['data']){
                    foreach($_FILES['data'] as $k=>$v){
                        foreach($v as $kk=>$vv){
                            $attachs[$kk][$k] = $vv;
                        }
                    }
                    $upload = K::M('magic/upload');
                    foreach($attachs as $k=>$attach){
                        if($attach['error'] == UPLOAD_ERR_OK){
                            if($a = $upload->upload($attach, 'home')){
                                $data[$k] = $a['photo'];
                            }
                        }
                    }
                }
				unset($data['city_id']);
				$data['lat'] = trim($data['lat']);
                if(K::M('home/home')->update($home_id, $data)){
					if($attr =  $this->GP('attr')){
                        K::M('home/attr')->update($home_id,$attr);       
                    }
                    $this->err->add('修改内容成功');
                }  
            } 
        }else{
            $this->pagedata['attr'] = K::M('home/attr')->attrs_ids_by_home($home_id);
        	$this->pagedata['detail'] = $detail;
        	$this->tmpl = 'admin:home/home/edit.html';
        }
    }

    public function doaudit($home_id=null)
    {
        if($home_id = (int)$home_id){
			if(!$home = K::M('home/home')->detail($home_id)){
				$this->err->add('小区不存在或已经删除', 211);
			}else if(!$this->check_city($home['city_id'])){
                $this->err->add('不可越权操作', 403);
            }else if(K::M('home/home')->batch($home_id, array('audit'=>1))){
                $this->err->add('审核内容成功');
            }
        }else if($ids = $this->GP('home_id')){

			if($items = K::M('home/home')->items_by_ids($ids)){
                $aids = $home_ids = array();
                foreach($items as $v){
                    if(CITY_ID && CITY_ID != $v['city_id']){
                        continue;
                    }
                    $aids[$v['home_id']] = $v['home_id'];
                }
                if($aids && K::M('home/home')->batch($aids, array('audit'=>1))){
                    $this->err->add('批量审核内容成功');
                }
            }
        }else{
            $this->err->add('未指定要审核的内容', 401);
        }
    }

    public function delete($home_id=null)
    {
		if($home_id = (int)$home_id){
            if($home = K::M('home/home')->detail($home_id)){
                if(!$this->check_city($home['city_id'])){
                    $this->err->add('不可越权操作', 403);
                }else if(K::M('home/home')->delete($home_id)){
                    $this->err->add('删除小区成功');
                }
            }
        }else if($ids = $this->GP('home_id')){
            if($items = K::M('home/home')->items_by_ids($ids)){
                $aids = $home_ids = array();
                foreach($items as $v){
                    if(CITY_ID && CITY_ID != $v['city_id']){
                        continue;
                    }
                    $aids[$v['home_id']] = $v['home_id'];
                }
                if($aids && K::M('home/home')->delete($aids)){
                    $this->err->add('批量删除成功');
                }
            }
        }else{
            $this->err->add('未指定要删除的内容ID', 401);
        }
    }

}