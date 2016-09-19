<?php


class Mdl_System_Integral extends Mdl_Table
{   
    
    protected $_CFG = array();
    
    public function __construct(&$system)
    {
        parent::__construct($system);
        $this->_CFG = K::M('system/config')->get('integral');
    }
 
    /*
     * 因为单例 考虑到其他的地方可以复用
     *@params $member 用户信息, $auth 积分模块  $title 扣除积分的对应中文描述 $type true 扣除用户金币 false 只做检测用
     *@return true or false  false 表示 账户余额不足 不足以支付！true 表示不受积分影响
     */
    public function check($auth,$member,$title=null,$type=false)
    {
        
        if(empty($member)) return false;      
        
        $num = isset($this->_CFG[$auth]) ? (int)$this->_CFG[$auth] : 0;   
        
        if( $member['gold'] + $num < 0 ) return false;
        
        if($type === true && $num !== 0 ){            
            if(!K::M('member/gold')->update($member['uid'], $num, $title ? $title : '积分系统增扣')) return false;            
        }
        
        return true;
    }
    
    /**
     * @params $member 用户信息, $auth 积分模块  $title 扣除积分的对应中文描述 
     * @return true or false 
     */
    public function commit($auth,$member,$title = null)
    {
        
        return $this->check($auth, $member,$title,true);
        
    }

    public function integral($ident)
    {
        $num = isset($this->_CFG[$ident]) ? (int)$this->_CFG[$ident] : 0;
        return $num;
    }
    
}