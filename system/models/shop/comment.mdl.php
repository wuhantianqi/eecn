<?php
/**
 * Copy Right IJH.CC
 * Each engineer has a duty to keep the code elegant
 * $Id: comment.mdl.php 2657 2013-12-31 09:48:37Z youyi $
 */

if(!defined('__CORE_DIR')){
    exit("Access Denied");
}

class Mdl_Shop_Comment extends Mdl_Table
{   
  
    protected $_table = 'shop_comment';
    protected $_pk = 'comment_id';
    protected $_cols = 'comment_id,city_id,shop_id,uid,score,content,reply,replyip,replytime,audit,closed,clientip,dateline';
    protected $_orderby = array('comment_id'=>'DESC');
    
    public function create($data, $checked=false)
    {
        if(!$checked && !$data = $this->_check($data)){
            return false;
        }
        unset($data['reply'], $data['replyip'], $data['replytime']); 
        $data['clientip'] = $data['clientip'] ? $data['clientip'] : __IP; 
        $data['dateline'] = $data['dateline'] ? $data['dateline'] : __TIME; 
        if($comment_id = $this->db->insert($this->_table, $data, true)){
            $score = (int)$data['score'];
            K::M('shop/shop')->update_score($data['shop_id'], $score, 1);
        }
        return $comment_id;
    }

    public function update($comment_id, $data, $checked=false)
    {
        if(!$checked && !$data = $this->_check($data, $comment_id)){
            return false;
        }
        return $this->db->update($this->_table, $data, $this->field($this->_pk, $comment_id));
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

    public function comment_count($val)
    {
         $count = 0;
        if(!$ids = K::M('verify/check')->ids($val)){
            return false;
        }
        $counts = array_fill_keys(explode(',', $ids), 0);
        $sql = "SELECT shop_id, COUNT(1) c FROM ".$this->table($this->_table)." WHERE ". self::field('shop_id', $val)." and closed=0 GROUP BY shop_id";
        if($rs = $this->db->Execute($sql)){
            while($row = $rs->fetch()){
                $counts[$row['shop_id']] = $row['c'];
            }
            foreach($counts as $k=>$v){
                K::M('shop/shop')->update($k, array('comments'=>$v), true);
                $count ++;
            }            
        }
        return $count;
    }

    protected function _format_row($row)
    {
        //if($city = K::M('data/city')->city($row['city_id'])){
        //    $row['city_name'] = $city['city_name'];
        //}
        return $row;
    }

    protected function _check($data, $comment_id=null)
    {
        unset($data['comment_id'], $data['clientip'], $data['dateline']);
        if(!$comment_id || isset($data['content'])){
            if(empty($data['content'])){
                $this->err->add('留言内容不能为空', 411);
                return false;
            }else if(!K::M('verify/check')->len(K::M("content/text")->strlen($data['content']), 5, 512)){
                $this->err->add('留言内容长度为5~512个字', 411);
                return false;               
            }
            $data['content'] = K::M('content/html')->encode($data['content']);
        }
        if(!$comment_id || isset($data['score'])){
            $data['score'] = (int)$data['score'];
            if($data['score']<1 || $data['score']>5){
                $data['score'] = 3;
            }
        }
        if(isset($data['shop_id'])){
            $data['shop_id'] = (int)$data['shop_id'];
        }
        if(isset($data['uid'])){
            $data['uid'] = (int)$data['uid'];
        }
        if(isset($data['reply'])){
            if($data['reply'] = K::M('content/html')->encode($data['reply'])){
                $data['replytime'] = $data['replytime'] ? $data['replytime'] : __CFG::TIME;
            }
        }
        if(isset($data['audit'])){
            $data['audit'] = $data['audit'] ? 1 : 0;
        }
        return parent::_check($data);
    }    
}