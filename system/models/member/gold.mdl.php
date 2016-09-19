<?php
/**
 * Copy Right IJH.CC
 * Each engineer has a duty to keep the code elegant
 * Author @shzhrui<Anhuike@gmail.com>
 * $Id: gold.mdl.php 5610 2014-06-23 16:47:52Z youyi $
 */

Import::M('member/member');
class Mdl_Member_Gold extends Mdl_Member_Member
{   
    

    public function update($uids, $gold, $log='')
    {

        if(!$gold = (int)$gold){
            $this->err->add('更变的金币值非法', 411);
        }else if(empty($log)){
            $this->err->add('未指定金币充值日志', 412);
        }else{
            if($uids = K::M('verify/check')->ids($uids)){
                foreach(explode(',', $uids) as $uid){
                    $sql = "UPDATE ".$this->table($this->_table)." SET `gold`=`gold`+{$gold} WHERE uid='$uid'";
                    if($this->db->Execute($sql)){
                        K::M('member/log')->log($uid, 'gold', $gold, $log);                        
                    }
                }
                return true;
            }          
        }
        return false;
    }
}