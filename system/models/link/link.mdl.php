<?php
/**
 * Copy Right IJH.CC
 * Each engineer has a duty to keep the code elegant
 * Author @shzhrui<Anhuike@gmail.com>
 * $Id: link.mdl.php 2034 2013-12-07 03:08:33Z $
 */

class Mdl_Link_Link extends Mdl_Table
{   
  
    protected $_table = 'links';
    protected $_pk = 'link_id';
    protected $_cols = 'link_id,title,link,logo,closed,dateline';
    protected $_orderby = array('link_id'=>'DESC');
}