<?php
/**
 * Copy Right IJH.CC
 * Each engineer has a duty to keep the code elegant
 * $Id: comment.mdl.php 10307 2015-05-18 01:19:09Z wanglei $
 */

if(!defined('__CORE_DIR')){
    exit("Access Denied");
}

class Mdl_Company_Comment extends Mdl_Table
{   
  
    protected $_table = 'company_comment';
    protected $_pk = 'comment_id';
    protected $_cols = 'comment_id,city_id,company_id,uid,score1,score2,score3,score4,score5,content,dateline,clientip,audit,reply,replyip,replytime,closed';
    protected $_orderby = array('comment_id'=>'DESC');

    protected $_hot_orderby = array('replytime'=>'DESC','id'=>'DESC');
    protected $_hot_filter = array('audit'=>'1');
    protected $_new_orderby = array('comment_id'=>'DESC');
    protected $_new_filter = array('audit'=>'1');
    
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

    public function comment_count($val)
    {
         $count = 0;
        if(!$ids = K::M('verify/check')->ids($val)){
            return false;
        }
        $counts = array_fill_keys(explode(',', $ids), 0);
        $sql = "SELECT company_id, COUNT(1) c FROM ".$this->table($this->_table)." WHERE ". self::field('company_id', $val)." and closed = 0 GROUP BY company_id";
        if($rs = $this->db->Execute($sql)){
            while($row = $rs->fetch()){
                $counts[$row['company_id']] = $row['c'];
            }
            foreach($counts as $k=>$v){
                K::M('company/company')->update($k, array('comments'=>$v), true);
                $count ++;
            }            
        }
        return $count;
    }

	public function comment($data)
	{
		if($company = K::M('company/company')->detail($data['company_id'])){
			
			if($data['score1']>0){
				$update['score1'] = $company['score1']+$data['score1'];
				$update['score'] = round(($update['score1'])/1);
			}
			if($data['score2']>0){
				$update['score2'] = $company['score2']+$data['score2'];
				$update['score'] = round(($update['score1']+$update['score2'])/2);
			}
			if($data['score3']>0){
				$update['score3'] = $company['score3']+$data['score3'];
				$update['score'] = round(($update['score1']+$update['score2']+$update['score3'])/3);
			}
			if($data['score4']>0){
				$update['score4'] = $company['score4']+$data['score4'];
				$update['score'] = round(($update['score1']+$update['score2']+$update['score3']+$update['score4'])/4);
			}
			if($data['score5']>0){
				$update['score5'] = $company['score5']+$data['score5'];
				$update['score'] = round(($update['score1']+$update['score2']+$update['score3']+$update['score4']+$update['score5'])/5);
			}
			
			K::M('company/company')->update($data['company_id'], $update, true);
		}
	}

    public function items_by_company($company_id, $p=1, $l=50, &$count=0)
    {
        if(!$company_id = (int)$company_id){
            return false;
        }
        return $this->items(array('company_id'=>$company_id, 'closed'=>0), null, $p, $l, $count);
    }

    public function reply($id, $reply)
    {
        if(!$id = (int)$id){
            return false;
        }else if(empty($reply)){
            return false;
        }
        $reply = K::M('content/html')->encode($reply);
        return $this->update($id, array('reply'=>$reply,'replyip'=>__IP,'replytime'=>__TIME), true);
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
        for($i=1; $i<=5; $i++){
            $k = 'score'.$i;
            if(!$comment_id || isset($data[$k])){
                $data[$k] = (int)$data[$k];
                if($data[$k]<1 || $data[$k]>5){
                    $data[$k] = 3;
                }
            }            
        }
        if(isset($data['company_id'])){
            $data['company_id'] = (int)$data['company_id'];
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