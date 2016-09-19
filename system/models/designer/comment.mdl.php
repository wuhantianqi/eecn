<?php
/**
 * Copy Right IJH.CC
 * Each engineer has a duty to keep the code elegant
 * $Id$
 */

if(!defined('__CORE_DIR')){
    exit("Access Denied");
}

class Mdl_Designer_Comment extends Mdl_Table
{   
  
    protected $_table = 'designer_comment';
    protected $_pk = 'comment_id';
    protected $_cols = 'comment_id,city_id,uid,designer_id,score1,score2,score3,content,reply,replyip,replytime,audit,closed,clientip,dateline';

    
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
        $sql = "SELECT designer_id, COUNT(1) c FROM ".$this->table($this->_table)." WHERE ". self::field('designer_id', $val)." GROUP BY designer_id";
        if($rs = $this->db->Execute($sql)){
            while($row = $rs->fetch()){
                K::M('designer/designer')->update($row['designer_id'], array('comments'=>$row['c']), true);
                $count ++;
            }
        }
        return $count;
    }

    protected function _format_row($row)
    {
        if($city_id = $row['city_id']){
            if($city = K::M('data/city')->city($city_id)){
                $row['city_name'] = $city['city_name'];
            }
        }
        return $row;
    }

	public function comment($data)
	{
		if($detail = K::M('designer/designer')->detail($data['designer_id'])){
			$update['comments'] = $detail['comments']+1;
			$update['score1'] = $detail['score1']+$data['score1'];
			$update['score2'] = $detail['score2']+$data['score2'];
			$update['score3'] = $detail['score3']+$data['score3'];
			$update['score'] = round(($update['score1']+$update['score2']+$update['score3'])/3);
			K::M('designer/designer')->update($data['designer_id'], $update, true);
		}
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
        for($i=1; $i<4; $i++){
            $k = 'score'.$i;
            if(!$comment_id || isset($data[$k])){
                $data[$k] = (int)$data[$k];
                if($data[$k]<1 || $data[$k]>5){
                    $data[$k] = 3;
                }
            }            
        }
        return parent::_check($data);
    }      
}