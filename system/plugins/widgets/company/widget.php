<?php
/**
 * Copy Right IJH.CC
 * Each engineer has a duty to keep the code elegant
 * Author @shzhrui<Anhuike@gmail.com>
 * $Id: widget.php 5457 2014-06-11 08:53:34Z $
 */

class Widget_Company extends Model
{

    public function index()
    {
		
    }

	public function group()
	{
		$data = K::M('member/group')->items_by_from('company');
		return $data;
	}

	public function companyindex()
	{
		$data = K::M('data/attr')->attrs_by_from('zx:company', true);
		return $data;
	}
    
}