<?php
/**
 * Copy Right IJH.CC
 * $Id$
 */

class Mdl_Helper_Debug extends Model
{
    
    protected $__DU = array('__DEBUG__'=> 'test');
    
    public function set($k,$v)
    {
        $this->__DU[$k] = $v;
    }
    
    /**
     * $level 调试级别{data:仅调试数据,sys系统}
     */
    public function output($level='data', $return=false)
    {
        if(!$level){
            return false;
        }
        if($return){
            $content = ob_get_contents();
            ob_end_clean();
            ob_start();
        }
        //以后添加调试转出模板
        echo '<div id="__SYSTEMDEBUG__" style="background-color:#FFFFFF;border:2px dashed #FF3300;padding:5px;overflow-x:auto;overflow-y:scroll;height:400px;display:none;position: fixed;right:0px;bottom:10px;z-index:100;">';
        echo "<h4>江湖信息科技系统框架调试信息</h4><hr />\n";        
        echo "<pre>\n+------------------------------------------------------------------------+\n";
        //echo '系统级运行时间:'.xdebug_time_index()."秒\n";
        echo '框架级运行时间:'.$this->runtime()."秒\n";
        //echo 'Smarty处理时间:'.K::$system->smarty_execute_time."秒\n";
        echo 'Smarty处理时间:'.(microtime(true) - K::$system->smarty_start_time)."秒\n";
        echo '内存使用情况:'.K::M('helper/format')->size(memory_get_usage())."\n";
        echo '内存使用峰值:'.K::M('helper/format')->size(memory_get_peak_usage())."\n";
        echo "\n+------------------------------------------------------------------------+\n";
        echo "用户调试信息:\n";
        if($this->__DU){
            foreach((array)$this->__DU as $k=>$v){
                echo "$k:\n";
                if(is_array($v)){
                    print_r($v);
                }else if(is_string($v)){
                    echo "$v\n";
                }else{
                    var_dump($v);
                }
            }
        }
        if($level == 'system'){
            echo "\n+------------------------------------------------------------------------+\n";
            $QSQL = $this->db->SQLLOG();
            $c = count($QSQL['QSQL']);
            echo "系统执行的SQL语句:共({$c})个\n";
            //var_dump($this->db->_QSQL);
            echo "SQL执行时间:{$QSQL['Time']}秒\n";
            print_r($QSQL);
            echo "\n+------------------------------------------------------------------------+\n";
            $c = count(Import::$_GFILES);
            echo "系统加载的MDL,CTL文件:共({$c})个\n";
			foreach(Import::$_GFILES as $k=>$v){
				echo "{$k}:{$v}\n";
			}
            echo "\n+------------------------------------------------------------------------+\n";
            $FS = get_included_files();
            $c = count($FS);
            echo "系统所有引入的文件:共({$c})个\n";
			foreach($FS as $k=>$v){
				echo "{$k}:{$v}\n";
			}
            echo "\n+------------------------------------------------------------------------+\n";
            echo "系统运行信息:\n";
            //var_dump(self::$system);
        }
        echo "\n+------------------------------------------------------------------------+\n";
        echo '</pre></div><a id="__SYSTEMDEBUG_BUTTON__" style="width:100px;height:36px;display:block;position: fixed;right:0px;bottom:10px;z-index:1000;background-image: url(\'data:image/jpg;base64,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\');"></a>';
echo '
<script type="text/javascript">
(function(K, $){
$("#__SYSTEMDEBUG_BUTTON__").toggle(function(){$("#__SYSTEMDEBUG__").show();},function(){$("#__SYSTEMDEBUG__").hide();});
$("#__SYSTEMDEBUG__").width($(window).width()-20);
})(window.KT, window.jQuery);
</script>';
        if($return){
            $info = ob_get_contents();
            ob_end_clean();
            echo $content;
            return $info;
        }
        return true;
    }
    
    //返回系统运行时间
    public function runtime()
    {
        return microtime(true) - self::$system->starttime;
    }
}