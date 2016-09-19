<?php
/**
 * Copy Right IJH.CC
 * Each engineer has a duty to keep the code elegant
 * Author @shzhrui<Anhuike@gmail.com>
 * $Id: block.ctl.php 6074 2014-08-12 17:10:33Z youyi $
 */

if(!defined('__CORE_DIR')){
    exit("Access Denied");
}

class Ctl_Block_Block extends Ctl
{
    
    public function index($page=1)
    {
    	$filter = $pager = array();
    	$pager['page'] = $page = max(intval($page), 1);
    	$pager['limit'] = $limit = 50;
        if($items = K::M('block/block')->items($filter, null, $page, $limit,$count)){
        	$pager['count'] = count($items);
            $pager['pagebar'] = $this->mkpage($count, $limit, $page, $this->mklink(null, array('{page}')));
            $this->pagedata['items'] = $items;
            $this->pagedata['page_list'] = K::M('block/page')->fetch_all();           
        }
        $this->pagedata['from_list'] = K::M('block/block')->from_list();
        $this->pagedata['pager'] = $pager;
        $this->tmpl = 'admin:block/block/items.html';
    }

    public function detail($block_id, $page=1)
    {
        if(!$block_id = (int)$block_id){
            $this->err->add('未定要管理的推荐位ID', 211);
        }else if(!$block = K::M('block/block')->detail($block_id)){
            $this->err->add('推荐位不存在或已经删除', 212);
        }else{
            $pager['page'] = $page = max(intval($page), 1);
            $pager['limit'] = $limit = 50;            
            $this->pagedata['block'] = $block;
        	if($items = K::M('block/item')->items_by_block($block_id,null, $page, $limit, $count)){
                $pager['count'] = $count;
                $pager['pagebar'] = $this->mkpage($count, $limit, $page, $this->mklink(null, array($block_id,'{page}')));
                $this->pagedata['items'] = $items;
            }
            $this->pagedata['pager'] = $pager;
        	$this->tmpl = 'admin:block/block/detail.html';
        }
    }

    public function config($block_id)
    {
	  if($this->checksubmit()){
		$block_id = $this->GP('block_id');
		if(!$block_id = (int)$block_id){
			$this->err->add('未定要管理的推荐位ID', 211);
		}else if(!$block = K::M('block/block')->detail($block_id)){
			$this->err->add('推荐位不存在或已经删除', 212);
		}else if(!$data = $this->GP('data')){
			$this->err->add('推荐位内容不存在', 213);
		}else{
			K::M('block/block')->update($block_id, array('tmpl'=>$data['tmpl']));
			$this->err->add("修改推荐位模板成功");
			$this->err->set_data('forward', '?block/block-index.html');
		}
	  }else{
			if(!$block_id = (int)$block_id){
				$this->err->add('未定要管理的推荐位ID', 211);
			}else if(!$block = K::M('block/block')->detail($block_id)){
				$this->err->add('推荐位不存在或已经删除', 212);
			}else{
				$this->pagedata['block'] = $block;
				$this->tmpl = 'admin:block/block/config.html';
			}
		}
    }

    public function code($block_id)
    {
        if(!$block_id = (int)$block_id){
            $this->err->add('未定要管理的推荐位ID', 211);
        }else if(!$block = K::M('block/block')->detail($block_id)){
            $this->err->add('推荐位不存在或已经删除', 212);
        }else{
            $this->pagedata['detail'] = $block;
            $code['widget'] = '<{KT id="'.$block_id.'" name="'.$block['title'].'" city_id=$request.city_id}>代码段<{/KT}>';
            $code['js'] = '<script src="'.$site['url'].'/index.php?market-block-'.$block_id.'"></script>';
            $code['widget'] = K::M('content/html')->encode($code['widget']);
            $code['js'] = K::M('content/html')->encode($code['js']);
            $this->pagedata['code'] = $code;
            $this->tmpl = 'admin:block/block/code.html';
        }
    }

    public function create()
    {
        if($this->checksubmit()){
            if(!$data = $this->GP('data')){
                $this->err->add('非法的数据提交', 201);
            }else{
                if($block_id = K::M('block/block')->create($data)){
                    $this->err->add('添加内容成功');
                    $this->err->set_data('forward', '?block/block-index.html');
                }
            } 
        }else{
           $this->tmpl = 'admin:block/block/create.html';
        }
    }

    public function edit($block_id=null)
    {
        if(!($block_id = (int)$block_id) && !($block_id = (int)$this->GP('block_id'))){
            $this->err->add('非法的数据提交', 211);
        }else if(!$detail = K::M('block/block')->detail($block_id)){
            $this->err->add('推荐位不存在或已经删除', 212);
        }else if($data = $this->checksubmit('data')){
            $zhanwei = $this->GP('zhanwei');
            if($data['type'] == 'zhanwei'){                
                if($attach = $_FILES['zhanwei_thumb']){
                   if ($attach['error'] == UPLOAD_ERR_OK) {
                        if ($a = K::M('magic/upload')->upload($attach, 'block')) {
                            $zhanwei['thumb'] = $a['photo'];
                        }
                    }
                }
            }
            $data['config']['zhanwei'] = $zhanwei;
            if(K::M('block/block')->update($block_id, $data)){
                $this->err->add('修改推荐配置成功');
                $this->err->set_data('forward', '?block/block-index.html');
            }  
        }else{
            $this->pagedata['detail'] = $detail;
            $this->tmpl = 'admin:block/block/edit.html';
        }
    }
    
    public function config2($block_id=null)
    {
        if(!($block_id = (int)$block_id) && !($block_id = (int)$this->GP('block_id'))){
            $this->err->add('非法的数据提交', 211);
        }else if($detail = K::M('block/block')->detail($block_id)){
            $this->err->add('推荐位不存在或已经删除', 212);
        }else if($data = $this->checksubmit('data')){
            if(K::M('block/block')->update($block_id, $data)){
                $this->err->add('修改推荐配置成功');
                $this->err->set_data('forward', '?block/block-index.html');
            }  
        }else{
            $this->pagedata['detail'] = $detail;
            $this->tmpl = 'admin:block/block/edit.html';
        }        
    }	

    public function delete($pk=null)
    {
        if(!empty($pk)){
            if(K::M('block/block')->delete($pk)){
                $this->err->add('删除成功');
            }
        }else if($pks = $this->GP('block_id')){
            if(K::M('block/block')->delete($pks)){
                $this->err->add('批量删除成功');
            }
        }else{
            $this->err->add('未指定要删除的内容ID', 401);
        }
    }

}