<?php
/**
 * Copy Right IJH.CC
 * Each engineer has a duty to keep the code elegant
 * $Id$
 */

if(!defined('__CORE_DIR')){
    exit("Access Denied");
}

class Mdl_Designer_article extends Mdl_Table
{   
  
    protected $_table = 'designer_article';
    protected $_pk = 'article_id';
    protected $_cols = 'article_id,city_id,uid,title,content,is_top,views,audit,clientip,dateline';
    protected $_orderby = array('is_top'=>'DESC', 'article_id'=>'DESC');
    
    public function create($data, $checked=false)
    {
        if(!$checked && !$data = $this->_check_schema($data)){
            return false;
        }
        return $this->db->insert($this->_table, $data, true);
    }

    public function update($pk, $data, $checked=false)
    {
        $this->_checkpk();
        if(!$checked && !$data = $this->_check_schema($data,  $pk)){
            return false;
        }
        return $this->db->update($this->_table, $data, $this->field($this->_pk, $pk));
    }

    public function item_prev($article_id, $uid=0)
    {
        if(!$article_id = (int)$article_id){
            return false;
        }
        $where = "article_id<$article_id";
        if($uid = (int)$uid){
            $where .= " AND uid=$uid";
        }
        return $this->db->GetOne("SELECT * FROM ".$this->table($this->_table)." WHERE $where ORDER BY article_id DESC");
    }

    public function item_next($article_id, $uid=0)
    {
        if(!$article_id = (int)$article_id){
            return false;
        }
        $where = "article_id>$article_id";
        if($uid = (int)$uid){
            $where .= " AND uid=$uid";
        }
        return $this->db->GetOne("SELECT * FROM ".$this->table($this->_table)." WHERE $where ORDER BY article_id ASC");
    }
}