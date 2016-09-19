<?php
/**
 * Copy Right IJH.CC
 * Each engineer has a duty to keep the code elegant
 * $Id: comment.mdl.php 2657 2013-12-31 09:48:37Z youyi $
 */

if(!defined('__CORE_DIR')){
    exit("Access Denied");
}

class Mdl_Product_Comment extends Mdl_Table
{   
  
    protected $_table = 'product_comment';
    protected $_pk = 'comment_id';
    protected $_cols = 'comment_id,shop_id,city_id,product_id,uid,score,content,reply,replyip,replytime,audit,closed,clientip,dateline';
    protected $_orderby = array('comment_id'=>'DESC');

    
    public function create($data, $checked=false)
    {
        if(!$checked && !$data = $this->_check_schema($data)){
            return false;
        }
        if($comment_id = $this->db->insert($this->_table, $data, true)){
            $score = (int)$data['score'];
            K::M('product/product')->update_score($data['product_id'], $score, 1);
        }
        return $comment_id;
    }

    public function update($pk, $data, $checked=false)
    {
        $this->_checkpk();
        if(!$checked && !$data = $this->_check_schema($data,  $pk)){
            return false;
        }
        return $this->db->update($this->_table, $data, $this->field($this->_pk, $pk));
    }

    public function reply($comment_id, $reply)
    {
        if(!$comment_id = (int)$comment_id){
            return false;
        }else if(empty($reply)){
            return false;
        }
        $reply = K::M('content/html')->encode($reply);
        return $this->update($comment_id, array('reply'=>$reply,'replyip'=>__IP,'replytime'=>__CFG::TIME), true);
    }

    protected function _check($data, $comment_id=null)
    {
        if(empty($comment_id) || isset($data['score'])){
            $data['score'] = (int)$data['score'];
            if($data['score'] > 5 || $data['score'] < 1){
                $this->err->add('评分范围1~5', 451);
                return false;
            }
        }
        return parent::_check($data);
    }
}