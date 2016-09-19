<?php
/**
 * Copy Right IJH.CC
 * Each engineer has a duty to keep the code elegant
 * Author @shzhrui<Anhuike@gmail.com>
 * $Id: module.mdl.php 2108 2013-12-11 11:21:31Z youyi $
 */

Import::M('developer/base');
class Mdl_Developer_Module extends Mdl_Developer_Base
{   
    
    protected static $__allow = array(
        'acts' => array('items', 'so', 'detail', 'create', 'edit', 'doaudit', 'delete', 'update', 'save'),
        'input' => array('text', 'file', 'textarea', 'checkbox', 'radio', 'select','boolean', 'date', 'unixtime', 'dateline', 'editor', 'photo', 'attach', 'int', 'number', 'clientip', 'mail', 'phone', 'mobile','city', 'area', 'audit', 'closed', 'qq','company','member','shop','home','desginer','gz')
        );

    public function create_ctl($ctl, $mdl, $schema)
    {
        $content = $this->loadtmpl('ctl/ctl');
        $mctl = ($ctl['module'] ? ucfirst($ctl['module']).'_' : '').ucfirst($ctl['ctl']);
        $data = array('#fileid#'=>base64_decode('JElkJA=='),'#module#_#ctl#'=>$mctl);
        $data['#module#/#mdl#'] = $mdl['module'].'/'.$mdl['mdl'];
        $data['#module#/#mdl#'] = $mdl['module'].'/'.$mdl['mdl'];
        $data['#module#'] = $ctl['module']; 
        $data['#ctl#'] = $ctl['ctl'];
        $data['#view#'] = $ctl['view'];
        $pk = 'ID';
        $upload = $so = '';
        $filter = array();
        foreach($schema as $k=>$v){
            if($v['pk']){
                $pk = $k;
            }
            if(in_array($v['type'], array('photo', 'attach', 'file'))){
                $upload = true;
            }
            if($ctl['act']['so'] && $v['SO']){
                $_so = strtoupper($v['SO']);
                switch($_so){
                    case '>': case '<': case '<>': case '>=': case '<=':
                        $filter[] = 'if($SO[\''.$k.'\']){$filter[\''.$k.'\'] = "'.$_so.':".$SO[\''.$k.'\'];}';
                        break;
                    case '|': case '&': case '^':
                        $filter[] = 'if($SO[\''.$k.'\']){$filter[\''.$k.'\'] = "'.$_so.':".$SO[\''.$k.'\'];}';
                        break;
                    case 'IN': case 'NOIN':
                        $filter[] = 'if($SO[\''.$k.'\'] && is_array($SO[\''.$k.'\'])){$filter[\''.$k.'\'] = $SO[\''.$k.'\'];}else if(K::M("verify/check")->ids($SO[\''.$k.'\'])){$filter[\''.$k.'\'] = "'.$_so.':".$SO[\''.$k.'\'];}';
                        break;
                    case 'LIKE':
                        $filter[] = 'if($SO[\''.$k.'\']){$filter[\''.$k.'\'] = "'.$_so.':%".$SO[\''.$k.'\']."%";}';
                        break;
                    case 'BETWEEN':
                        $filter[] = 'if(is_array($SO[\''.$k.'\'])){$a = intval($SO[\''.$k.'\'][0]);$b=intval($SO[\''.$k.'\'][1]);if($a && $b){$filter[\''.$k.'\'] = $a."~".$b;}}';
                        break;
                    case 'BETWEENDATE':
                        $filter[] = 'if(is_array($SO[\''.$k.'\'])){if($SO[\''.$k.'\'][0] && $SO[\''.$k.'\'][1]){$a = strtotime($SO[\''.$k.'\'][0]); $b = strtotime($SO[\''.$k.'\'][1])+86400;$filter[\''.$k.'\'] = $a."~".$b;}}';
                        break;
                    default:
                        $filter[] = 'if($SO[\''.$k.'\']){$filter[\''.$k.'\'] = $SO[\''.$k.'\'];}';
                        break;
                }
            }
        }
        $data['#pk#'] = $pk;
        if($upload){
            $data['#upload#'] = $this->__tmpl('ctl/upload', $data);
        }else{
            $data['#upload#'] = '';
        }
        if($ctl['act']['so']){
            $data['#so-filter#'] = $filter ? implode("\n", $filter) : '';
            $data['#index-filter#'] = $this->__tmpl('ctl/so-filter', $data);
        }else{
            $data['#so-filter#'] = '';
            $data['#index-filter#'] = '';
        }
        foreach(self::$__allow['acts'] as $act){
            if($ctl['act'][$act]){
                $tmpl = $act;
                if($ctl['city'] && in_array($act, array('items', 'index', 'create', 'edit', 'delete', 'detail'))){
                    $tmpl = 'city_'.$act;
                }
                $data['#'.$act.'#'] = $this->__tmpl('ctl/'.$tmpl, $data);
            }else{
                $data['#'.$act.'#'] = '';
            }
        }
        $content = $this->__tmpl('ctl/ctl', $data);
        $file = __APP_DIR.'controllers'.DIRECTORY_SEPARATOR.$ctl['module'].DIRECTORY_SEPARATOR.$ctl['ctl'].'.ctl.php';
        return $this->__save($file, $content);
    }

    public function create_mdl($mdl, $schema)
    {
        if(!$table = $mdl['table']){
            $this->err->add('未指定关联数据表', 451);  
            return false;
        }else if(!$fields = $this->db->GetFields(Mdl_Table::table($table))){
            $this->err->add('关联表不存在，请新建数据表', 451);
            return false;
        }
        $cols = $pk = array();
        foreach($fields as $k=>$v){
            $cols[] = $k;
            if($v['Key'] === 'PRI'){
                $pk[] = $k;
            }
        }
        $cols = implode(',', $cols);
        $pk = implode(',', $pk);
        $dir = __CFG::DIR.'models'.DIRECTORY_SEPARATOR.strtolower($mdl['module']).DIRECTORY_SEPARATOR;
        $mmdl = ucfirst($mdl['module']).'_'.ucfirst($mdl['mdl']);
        $data = array('#fileid#'=>base64_decode('JElkJA=='),'#module#_#mdl#'=>$mmdl, '#module#'=>$mdl['module'], '#mdl#'=>$mdl['mdl'], '#table#'=>$table, '#pk#'=>$pk, '#cols#'=>$cols);
        if(strtoupper($mdl['type']) === 'SIGN'){//单文件
            $file = $dir.strtolower($mdl['mdl']).'.mdl.php';
            $content = $this->__tmpl('mdl/mdl', $data);
            $this->__save($file, $content);
        }else{//多文件
            $basefile = $dir.'base.mdl.php';
            $viewfile = $dir.'view.mdl.php';
            $handlerfile = $dir.'handler.mdl.php';
            //if(file_exists($basefile) || file_exists($viewfile) || file_exists($handlerfile)){
            //    return false;
            //}
            $data['Mdl_#module#'] = 'Mdl_'.ucfirst($v['module']);
            $base = $this->__tmpl('mdl/base', $data);
            $view = $this->__tmpl('mdl/view', $data);
            $handler = $this->__tmpl('mdl/handler', $data);
            $this->__save($basefile, $base);
            $this->__save($viewfile, $view);
            $this->__save($handlerfile, $handler);
        }
    }

    public function create_tmpl($schema, $ctl, $mdl, $force=false)
    {
        $data = array('#javascript#'=>'', '#module#'=>$ctl['module'], '#ctl#'=>$ctl['ctl'], '#table#'=>$mdl['table'], '#topbutton#'=>'');
        //$footer = $this->loadtmpl('view/footer');
        $form = $formentype = '';
        $dir = __APP_DIR.'view'.DIRECTORY_SEPARATOR.$ctl['view'].DIRECTORY_SEPARATOR;
        if($ctl['act']['create']){
            foreach($schema as $k=>$v){
                if($v['pk']){
                    $pk = $v['field'];
                }  
				if(empty($v['empty'])){
					$data['#label#'] = '<span class="red">*</span>'.$v['label'];
				}else{
					$data['#label#'] = $v['label'];
				}
                $data['#field#'] = $v['field'];
                $data['#default#'] = $v['default'];
                $data['#comment#'] = $v['comment'] ? '<span class="tip-comment">'.$v['comment'].'</span>' : '';
                $type = strtolower($v['type']);
                if($v['add']){
                    switch($type){
                        case 'text': case 'checkbox': case 'radio': case 'select': case 'textarea':
                            $form .= $this->__tmpl('form/'.$type, $data); break;
                        case 'boolean': case 'date':  case 'number': case 'int' :
                            $form .= $this->__tmpl('form/'.$type, $data); break;
                        case 'audit' : case 'closed':
                            $form .= $this->__tmpl('form/'.$type.'_create', $data); break;
                        case 'member' : case 'desginer': case 'home': case 'shop': case 'company': case 'gz':
                            $form .= $this->__tmpl('form/'.$type.'_select', $data); break;
                        case  'city': case 'area':
                            $form .= $this->__tmpl('form/'.$type, $data); break;
                        case 'photo': case 'attach': case 'file':
                            $upload = true;
                            $form .= $this->__tmpl('form/file', $data); break;
                        case 'editor':
                            $kindeditor = true;
                            $form .= $this->__tmpl('form/editor', $data); break;
                        default :
                            $form .= $this->__tmpl('form/text', $data); break;
                    }
                }
            }
            $script = '';
            if($kindeditor){
                $script = $this->loadtmpl('script/kindeditor');
            }
            if($upload){
                $formentype = 'ENCTYPE="multipart/form-data"';
            }
            $data['#javascript#'] = $script;
            $data['#formentype#'] = $formentype;
            $data['#form-action#'] = '?'.$ctl['module'].'/'.$ctl['ctl'].'-create.html';
            $form = $this->__tmpl('form/header', $data) . $form .$this->__tmpl('form/footer', $data);
            $data['#topbutton#'] = '<{link ctl="'.$ctl['module'].'/'.$ctl['ctl'].':index" priv="hidden" class="button"}>';
            $header = $this->__tmpl('view/header', $data);
            $footer = $this->__tmpl('view/footer', $data);
            $content = $header. $form . $footer;
            $file = "{$dir}create.html";
            $this->__save($file, $content);
        }
        if($ctl['act']['edit']){
            $form = $formentype = '';
            foreach($schema as $k=>$v){
                if($v['pk']){
                    $pk = $v['field'];
                    $data['#field#'] = $v['field'];
                    $form .= $this->__tmpl('form/hidden', $data);
                }
				if(empty($v['empty'])){
					$data['#label#'] = '<span class="red">*</span>'.$v['label'];
				}else{
					$data['#label#'] = $v['label'];
				}
                $data['#field#'] = $v['field'];
                $data['#comment#'] = $v['comment'] ? '<span class="tip-comment">'.$v['comment'].'</span>' : '';
                $type = strtolower($v['type']);
                if($v['edit']){
                    switch($type){
                        case 'text': case 'checkbox': case 'radio': case 'select': case 'textarea':
                            $form .= $this->__tmpl('form/'.$type, $data); break;
                        case 'boolean': case 'date':  case 'number': case 'int' : case 'audit' : case 'closed':
                            $form .= $this->__tmpl('form/'.$type, $data); break;
                        case 'member' : case 'desginer': case 'home': case 'shop': case 'company':
                            $form .= $this->__tmpl('form/'.$type.'_select', $data); break;                            
                        case  'city': case 'area':
                            $cityarea = true;
                            $form .= $this->__tmpl('form/'.$type, $data); break;
                        case 'photo': case 'attach': case 'file':
                            $upload = true;
                            $form .= $this->__tmpl('form/file', $data); break;
                        case 'editor':
                            $kindeditor = true;
                            $form .= $this->__tmpl('form/editor', $data); break;
                        default :
                            $form .= $this->__tmpl('form/text', $data); break;
                    }
                }
            }
            if($upload){
                $formentype = 'ENCTYPE="multipart/form-data"';
            }  
            $script = '';
            if($kindeditor){
                $script = $this->loadtmpl('script/kindeditor');
            }
            if($cityarea){
                $script .= $this->loadtmpl('script/cityarea');                
            }

            $data['#javascript#'] = $script;      
            $data['#formentype#'] = $formentype;
            $data['#form-action#'] = '?'.$ctl['module'].'/'.$ctl['ctl'].'-edit.html';
            $form = $this->__tmpl('form/header', $data) . $form .$this->__tmpl('form/footer', $data);
            $data['#topbutton#'] = '<{link ctl="'.$ctl['module'].'/'.$ctl['ctl'].':index" priv="hidden" class="button"}>';
            $header = $this->__tmpl('view/header', $data);
            $footer = $this->__tmpl('view/footer', $data);             
            $content = $header. $form . $footer;
            $file = "{$dir}edit.html";
            $this->__save($file, $content);
        }
        if($ctl['act']['detail']){
            $table = '<table width="100%" border="0" cellspacing="0" class="table-data form">'."\n";
            foreach($schema as $k=>$v){
                if($v['pk']){
                    $pk = $v['field'];
                }                
                $data['#label#'] = $v['label'];
                $data['#field#'] = $v['field'];
                $data['#comment#'] = $v['comment'] ? '<span class="tip-comment">'.$v['comment'].'</span>' : '';
                if($v['show']){
                    if('photo' == $v['type']){
                        $value = '<img src="<{$pager.img}>/<{$detail.'.$v['field'].'}>" class="wh-200" />';
                    }else if('dateline' == $v['type']){
                        $value = '<{$detail.'.$v['field'].'|format}>';
                    }else{
                        $value = '<{$detail.'.$v['field'].'}>';
                    }
                    $table .= '<tr><th>'.$v['label'].'：</th><td>'.$value.'</td></tr>'."\n";
                }
            }
            $table .= '</table>';
            $data['#javascript#'] = '';
            $data['#topbutton#'] = '<{link ctl="'.$ctl['module'].'/'.$ctl['ctl'].':index" priv="hidden" class="button"}>';
            $header = $this->__tmpl('view/header', $data);
            $footer = $this->__tmpl('view/footer', $data);              
            $content = $header. $table . $footer;
            $file = "{$dir}detail.html";
            $this->__save($file, $content);
        }
        if($ctl['act']['so']){
            $form_title = $this->__tmpl('so/header', $data);
            $form = array();
            foreach($schema as $k=>$v){
                $data['#label#'] = $v['label'];
                $data['#field#'] = $v['field'];
                $data['#comment#'] = $v['comment'];
                if($v['SO']){
                    if($v['SO'] == 'between'){
                        $form[] = $this->__tmpl('so/betweeen', $data);
                    }else if($v['SO'] == 'betweendate'){
                        $form[] = $this->__tmpl('so/betweendate', $data);
                    }elseif($v['SO'] == 'boolean'){
                        $form[] = $this->__tmpl('so/boolean', $data);
                    }else if($v['type'] == 'date'){
                        $form[] = $this->__tmpl('so/date', $data);
                    }else if($v['type'] == 'number'){
                        $form[] = $this->__tmpl('so/number', $data);
                    }else{
                        $form[] = $this->__tmpl('so/text', $data);
                    }
                }
            }
            $data['#javascript#'] = '';
            $form_footer = $this->__tmpl('so/footer', $data);
            $data['#topbutton#'] = '<{link ctl="'.$ctl['module'].'/'.$ctl['ctl'].':index" priv="hidden" class="button"}>';
            $header = $this->__tmpl('view/header', $data);
            $footer = $this->__tmpl('view/footer', $data);         
            $content = $header. $form_title. implode("\n    ",$form). $form_footer . $footer;
            $file = "{$dir}so.html";
            $this->__save($file, $content);
        }
        if($ctl['act']['items']){
            $itemth = $itemtd = $itemopt = array();
            $batchbtns = $topbtns = array();
            foreach($schema as $k=>$v){
                if($v['pk']){
                    $pk = $v['field'];
                }                
                if($v['list']){
                    if($v['pk']){
                        $itemth[] = '<th class="w-100">'.$v['label'].'</th>';
                    }else if(in_array($v['type'], array('closed', 'audit', 'boolean', 'int', 'number'))){
                        $itemth[] = '<th class="w-50">'.$v['label'].'</th>';
                    }else if(in_array($v['type'], array('unixtime','dateline'))){
                        $itemth[] = '<th class="w-100">'.$v['label'].'</th>';
                    }else{
                        $itemth[] = '<th>'.$v['label'].'</th>';
                    }
                    if($v['pk']){
                        $itemtd[] = '<label><input type="checkbox" value="<{$item.'.$v['field'].'}>" name="'.$v['field'].'[]" CK="PRI"/><{$item.'.$v['field'].'}><label>';
                    }else if($v['type'] == 'dateline'){
                        $itemtd[] = '<{$item.'.$v['field'].'|format}>';
                    }else if($v['type'] == 'audit'){
                        $itemtd[] = '<{if $item.'.$v['field'].'}>正常<{else}><b class="red">待审</b><{/if}>';
                    }else if($v['type'] == 'closed'){
                        $itemtd[] = '<{if $item.'.$v['field'].'}><b class="red">删除</b><{else}>正常<{/if}>';
                    }else if($v['type'] == 'boolean'){
                        $itemtd[] = '<{if $item.'.$v['field'].'}><b class="blue">是</b><{else}>否<{/if}>';
                    }else if($v['type'] == 'photo'){
                        $itemtd[] = '<img src="<{$pager.img}>/<{$item.'.$v['field'].'}>" class="wh-50" />';
                    }else{
                        $itemtd[] = '<{$item.'.$v['field'].'}>';
                    }
                }
            }
            $itemth[] = '<th class="w-150">操作</th>';
            if($ctl['act']['detail']){
                $itemopt[] = '<{link ctl="'.$ctl['module'].'/'.$ctl['ctl'].':detail" args=$item.'.$pk.' class="button" title="查看"}>';
            }            
            if($ctl['act']['edit']){
                $itemopt[] = '<{link ctl="'.$ctl['module'].'/'.$ctl['ctl'].':edit" args=$item.'.$pk.' title="修改" class="button"}>';
            }
            if($ctl['act']['delete']){
                $itemopt[] = '<{link ctl="'.$ctl['module'].'/'.$ctl['ctl'].':delete" args=$item.'.$pk.' act="mini:删除" confirm="mini:确定要删除吗？" title="删除" class="button"}>';
                $batchbtns[] = '<{link ctl="'.$ctl['module'].'/'.$ctl['ctl'].':delete" type="button" submit="mini:#items-form" confirm="mini:确定要批量删除选中的内容吗?" priv="hide" value="批量删除"}>';
            }
            if($ctl['act']['doaudit']){
                $batchbtns[] = '<{link ctl="'.$ctl['module'].'/'.$ctl['ctl'].':doaudit" type="button" submit="mini:#items-form" confirm="mini:确定要批量审核选中的内容吗?" priv="hide" value="批量审核"}>';
            }            
            if($ctl['act']['create']){
                $topbtns[] = '<{link ctl="'.$ctl['module'].'/'.$ctl['ctl'].':create" class="button" title="添加"}>';
            }
            if($ctl['act']['so']){
                $topbtns[] = '<{link ctl="'.$ctl['module'].'/'.$ctl['ctl'].':so" load="mini:搜索内容" width="mini:500" class="button" title="搜索"}>';
            }
            $itemtd[] = @implode('', $itemopt);
            $data['#itemth#'] = @implode('', $itemth);
            $data['#itemtd#'] = '<td>'.@implode("</td>\n<td>", $itemtd).'</td>';
            $data['#batchbtns#'] = @implode('&nbsp;&nbsp;&nbsp;', $batchbtns);
            $data['#topbtns#'] = @implode('&nbsp;&nbsp;&nbsp;', $topbtns);;
            $content = $this->__tmpl('view/items', $data);
            $file = "{$dir}items.html";
            $this->__save($file, $content);
        }
        return true;
    }

    public function create_schema($table, $schema, $create=true)
    {
        $file = __CFG::DIR.'schemas'.DIRECTORY_SEPARATOR.$table.'.php';
        foreach((array)$schema as $k=>$v){
            $a = array('field'=>$k);
            $a['label'] = $v['label'] ? $v['label'] : $k; //显示名称
            $a['pk'] = $v['pk'] ? true : false;   
            $a['add'] = $v['add'] ? true : false; //是否可以添加
            $a['edit'] = $v['edit'] ? true : false; //是否可以修改
            $a['html'] = $v['html'] ? true : false; //是否支持html
            $a['empty'] = $v['empty'] ? true : false; //是否可以为空
            $a['show'] = $v['show'] ? true : false; //是否显示
            $a['list'] = $v['list'] ? true : false; //是否在列表显示
            $a['type'] = in_array($v['type'], self::$__allow['input']) ? $v['type'] : 'text';
            $a['comment'] = $v['comment'] ? $v['comment'] : '';
            $a['default'] = $v['default'] ? $v['default'] : '';
            $a['SO'] = $v['SO'] ? $v['SO'] : false;
            $schema[$k] = $a;
        }
        if($create){
            $content = $this->__tmpl('mdl/schema', array('#fileid#'=>base64_decode('JElkJA=='),'#schema#'=>var_export($schema, true)));
            $this->__save($file, $content);
        }
        return $schema;
    }

    public function table_fields($table)
    {
        return $this->db->GetFields(Mdl_Table::table($table), true);
    }
}