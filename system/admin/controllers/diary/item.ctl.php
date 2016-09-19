<?php
/**
 * Copy Right IJH.CC
 * Each engineer has a duty to keep the code elegant
 * $Id$
 */

if(!defined('__CORE_DIR')){
    exit("Access Denied");
}

class Ctl_Diary_Item extends Ctl
{
    
    public function index($diary_id =null,$page=1)
	{	
    	 if (!($diary_id = (int) $diary_id) && !($diary_id = $this->GP('diary_id'))) {
            $this->err->add('未指定要修改的内容ID', 211);
        } else if (!$detail = K::M('diary/diary')->detail($diary_id)) {
            $this->err->add('您要修改的内容不存在或已经删除', 212);
        } else if(!$this->check_city($detail['city_id'])){
            $this->err->add('不可越权查看', 403);
        }else {
            $filter = $pager = array();
            $pager['page'] = max(intval($page), 1);
            $pager['limit'] = $limit = 50;
            $filter['diary_id'] = $diary_id;
			
            if($items = K::M('diary/item')->items($filter, null, $page, $limit, $count)){
                $pager['count'] = $count;
                $pager['pagebar'] = $this->mkpage($count, $limit, $page, $this->mklink(null, array($diary_id, '{page}')), array('SO'=>$SO));
            }
            $this->pagedata['items'] = $items;
            $this->pagedata['pager'] = $pager;
            $this->pagedata['diary_id'] = $diary_id;
            $this->pagedata['diary'] = $detail;
            $this->pagedata['status'] = K::M('home/site')->get_status();
			$this->tmpl = 'admin:diary/item/items.html';
		}
    }

    public function create($diary_id = null)
    {
        if (!($diary_id = (int) $diary_id) && !($diary_id = $this->GP('diary_id'))) {
            $this->err->add('未指定要修改的内容ID', 211);
        } else if (!$detail = K::M('diary/diary')->detail($diary_id)) {
            $this->err->add('您要修改的内容不存在或已经删除', 212);
        } else if(!$this->check_city($detail['city_id'])){
            $this->err->add('不可越权添加', 403);
        }else if($this->checksubmit()){
            if(!$data = $this->GP('data')){
                $this->err->add('非法的数据提交', 201);
            }else{
                $data['diary_id'] = $diary_id;
                if($detail_id = K::M('diary/item')->create($data)){
					if($data['status']<$detail['status']){
						K::M('diary/diary')->update($diary_id, array('content_num'=>$detail['content_num']+1));
					}else{
						K::M('diary/diary')->update($diary_id, array('status' => $data['status'],'content_num'=>$detail['content_num']+1));
					}
                    $this->err->add('添加内容成功');
                    $this->err->set_data('forward', '?diary/item-index-'.$diary_id.'.html');
                }
            } 
        }else{
           $this->pagedata['diary_id'] = $diary_id;
           $this->pagedata['diary'] = $detail;
           $this->pagedata['status'] = K::M('home/site')->get_status();
           $this->tmpl = 'admin:diary/item/create.html';
        }
    }

  
	
	 public function edit($item_id=null)
    {
        if(!($item_id = (int)$item_id) && !($item_id = $this->GP('item_id'))){
            $this->err->add('未指定要修改的内容ID', 211);
        }else if(!$detail = K::M('diary/item')->detail($item_id)){
            $this->err->add('您要修改的内容不存在或已经删除', 212);
        } else if(!$diary = K::M('diary/diary')->detail($detail['diary_id'])){
            $this->err->add('该日记不存在或已经删除', 403);
        }else if(!$this->check_city($diary['city_id'])){
            $this->err->add('不可越权修改', 403);
        }else if($this->checksubmit('data')){
            if(!$data = $this->GP('data')){
                $this->err->add('非法的数据提交', 201);
            }else{
				unset($data['item_id'],$data['diary_id']);
                if(K::M('diary/item')->update($item_id, $data)){
                    K::M('diary/diary')->update($item_id,array('status'=>$data['status']));
                    $this->err->add('修改内容成功');
					$this->err->set_data('forward', '?diary/item-index-'.$detail['diary_id'].'.html');
                }  
            } 
        }else{
            $this->pagedata['status'] = K::M('home/site')->get_status();
        	$this->pagedata['detail'] = $detail;
			$this->pagedata['diary'] = K::M('diary/diary')->detail($detail['diary_id']);
        	$this->tmpl = 'admin:diary/item/edit.html';
        }
    }



    public function delete($item_id=null)
    {
		if($item_id = (int)$item_id){
            if($item = K::M('diary/item')->detail($item_id)){
				if(!$diary = K::M('diary/diary')->detail($item['diary_id'])){
					 $this->err->add('该日记不存在或已经删除', 403);
				}else if(!$this->check_city($diary['city_id'])){
					 $this->err->add('不可越权操作', 403);
				}else if(K::M('diary/item')->delete($item_id)){
					K::M('diary/item')->item_count($diary['diary_id']);
                    $this->err->add('删除成功');
                }
            }
        }else if($ids = $this->GP('item_id')){
           if($items = K::M('diary/item')->items_by_ids($ids)){
                $aids = $diarys_id =  array();
                foreach($items as $k => $v){
                    if($diary_id = $v['diary_id']){
                        break;
                    }
                }
                if(!$diary = K::M('diary/diary')->detail($diary_id)){
                    $this->err->add('该日记不存在或已经删除', 403);
                }else if(!$this->check_city($diary['city_id'])){
                     $this->err->add('不可越权操作', 403);
                }else{
                    foreach($items as $val){
                        if($val['diary_id'] == $diary_id){
                            $aids[$val['item_id']] = $val['item_id'];
							$diarys_id[$val['diary_id']] = $val['diary_id'];
                        }
                    }
                    if($aids && K::M('diary/item')->delete($aids)){
						K::M('diary/item')->item_count($diary['diary_id']);
                        $this->err->add('批量删除成功');
                    }
                }                
            }
        }else{
            $this->err->add('未指定要删除的内容ID', 401);
        }
    }

}