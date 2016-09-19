<?php
/**
 * Copy Right Anuike.com
 * Each engineer has a duty to keep the code elegant
 * Author @shzhrui<Anhuike@gmail.com>
 * $Id: widget.php 5605 2014-06-23 11:04:22Z youyi $
 */

class Widget_Product extends Model
{

    public function filter(&$params)
    {
        $cate = $top_cate = $cates = $parents = $childrens = $brands = $attrs = array();
        $brand_id = (int)$params['brand_id'];
        if($cat_id = (int)$params['cat_id']){
            if($cate = K::M('shop/cate')->cate($cat_id)){
                $top_cate = K::M('shop/cate')->top_cate($cat_id);
                $attrs = K::M('shop/attr')->attrs_by_cat($cat_id);                
            }
        }
        if(!$cate || $cate['brands'][$brand_id]){
           $brand_id = 0;
        }
        $cate_list = K::M('shop/cate')->fetch_all();  
        $attr_ids = array();
        if($attrs){
            if($attr_values = (array)$params['attr_values']){
                $a = implode('-', $attr_values);
                foreach($attrs as $k=>$v){
                    foreach($a as $vv){
                        if(in_array($vv, $v['values'])){
                            $attr_ids[$k] = $vv;
                        }else{
                            $attr_ids[$k] = 0;
                        }
                    }
                }
            }
        }
        if($attr_ids){
            $attr_ids = implode('-', $attr_ids);
        }else{
            $attr_ids = '0';
        }
        $order = (int)$params['order'];
        $oLink = K::M('helper/link');
        $ctl = $params['ctl'] ? $params['ctl'] : 'mall/product';
        if($cate){
            foreach($cate_list as $k=>$v){
                if(empty($v['parent_id'])){
                    $v['link'] = $oLink->mklink($ctl, array($k, 0, 0, $order, 1), null, true);
                    $parents[$k] = $v;
                }else if($top_cate['cat_id'] == $v['parent_id']){
                    $v['link'] = $oLink->mklink($ctl, array($k, $brand_id, $attr_ids, $order, 1), null, true);
                    $cates[$k] = $v;
                }else if($v['parent_id'] == $cate['parent_id'] || $v['parent_id'] == $cate['cat_id'] || $v['cat_id'] == $cate['cat_id']){
                    $v['link'] = $oLink->mklink($ctl, array($k, $brand_id, $attr_ids, $order, 1), null, true);
                    $childrens[$k] = $v;
                }
            }
        }else{
            foreach($cate_list as $k=>$v){
                if(empty($v['parent_id'])){
                    $v['link'] = $oLink->mklink($ctl, array($k, 0, 0, $order, 1), null, true);
                    $parents[$k] = $v;
                }
            }
        }
        $cate_all_link = $oLink->mklink($ctl, array($top_cate['cat_id'], $brand_id, $attr_ids, $order, 1), null, true);
        if($brand_ids = $cate['brand_ids']){
            if($brands = K::M('shop/brand')->items_by_ids($brand_ids)){
                foreach($brands as $k=>$v){
                    $v['link'] = $oLink->mklink($ctl, array($cat_id, $k, $attr_ids, $order, 1), null, true);
                    $brands[$k] = $v;
                }
                $brand_all_link = $oLink->mklink($ctl, array($cat_id, 0, $attr_ids, $order, 1), null, true);
            }
        }  
        if($attrs = K::M('shop/attr')->attrs_by_cat($cat_id)){
            $values = array();
            foreach($attrs as $k=>$v){
                foreach($attr_values as $kk=>$vv){
                    if($v['values'][$vv]){
                        $values[$k] = $vv;
                    }
                }
            }
            $attr_values = $values;
            foreach($attrs as $k=>$v){
                $checked = false;
                foreach((array)$v['values'] as $kk=>$vv){
                    if(in_array($vv['attr_value_id'], $attr_values)){
                        $vv['checked'] = $checked = true;
                    }else{
                        $a = $attr_values;
                        $a[$vv['attr_id']] = $vv['attr_value_id'];
						$vv['link'] = $oLink->mklink($ctl, array($cat_id, $brand_id, implode('-', $a), $order, 1), null, true);
                    }
                    $v['checked'] = $checked;
                    $a = $attr_values;
                    unset($a[$k]);
					$v['link'] = $oLink->mklink($ctl, array($cat_id, $brand_id, implode('-', $a), $order, 1), null, true);
                    $v['values'][$kk] = $vv;
                }
                $attrs[$k] = $v;
            }            
        }
        $data = array('cate'=>$cate,'top_cate'=>$top_cate, 'parents'=>$parents, 'cates'=>$cates, 'childrens'=>$childrens, 'brands'=>$brands, 'attrs'=>$attrs);
        $data['cat_id'] = $cat_id;
        $data['brand_id'] = $brand_id;
        $data['cate_all_link'] = $cate_all_link;
        $data['brand_all_link'] = $brand_all_link;
        $data['attr_values'] = (array)$attr_values;
        $data['order'] = $order;
        return $data;
    }
}