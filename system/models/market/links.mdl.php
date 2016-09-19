<?php
/**
 * Copy Right IJH.CC
 * Each engineer has a duty to keep the code elegant
 * Author @shzhrui<Anhuike@gmail.com>
 * $Id: links.mdl.php 2034 2013-12-07 03:08:33Z $
 */

if(!defined('__CORE_DIR')){
    exit("Access Denied");
}

class Mdl_Market_Links extends Mdl_Table
{   
  
    protected $_table = 'links';
    protected $_pk = 'link_id';
    protected $_cols = 'link_id,title,link,logo,desc,city_id,audit,closed,dateline';
    protected $_orderby = array('link_id'=>'ASC');
    protected $_pre_cache_key = 'market-links-list';
    
    public function create($data, $checked=false)
    {
        if(!$checked && !$data = $this->_check($data)){
            return false;
        }
        $data['dateline'] = __CFG::TIME;
        return $this->db->insert($this->_table, $data, true);
    }

    public function update($pk, $data, $checked=false)
    {
        $this->_checkpk();
        if(!$checked && !$data = $this->_check($data,  $pk)){
            return false;
        }
        return $this->db->update($this->_table, $data, $this->field($this->_pk, $pk));
    }

    protected function _check($data, $link_id=null)
    {
        $oHtml = K::M('content/html');
        if(!$link_id || isset($data['title'])){
            if(empty($data['title'])){
                $this->err->add('友情连接标题不能为空', 451);
                return false;
            }
            $data['title'] = $oHtml->encode($data['title']);
        }
        if(!$link_id || isset($data['link'])){
            if(empty($data['link'])){
                $this->err->add('友情连接不能为空', 451);
                return false;
            }
            $data['link'] = $oHtml->encode($data['link']);
        }
        if(isset($data['logo'])){
            $data['logo'] = $oHtml->encode($data['logo']);
        }
        if(isset($data['desc'])){
            $data['desc'] = $oHtml->encode($data['desc']);
        }
        if(isset($data['city_ids'])){
            if(is_array($data['city_ids'])){
                $data['city_ids'] = implode(',', $data['city_ids']);
            }
        }
        return parent::_check($data);  

    } 
}