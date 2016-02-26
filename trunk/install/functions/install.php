<?php
// +-----------------------------------------------------------------+
// |                   PhreeBooks Open Source ERP                    |
// +-----------------------------------------------------------------+
// | Copyright(c) 2008-2015 PhreeSoft      (www.PhreeSoft.com)       |
// +-----------------------------------------------------------------+
// | This program is free software: you can redistribute it and/or   |
// | modify it under the terms of the GNU General Public License as  |
// | published by the Free Software Foundation, either version 3 of  |
// | the License, or any later version.                              |
// |                                                                 |
// | This program is distributed in the hope that it will be useful, |
// | but WITHOUT ANY WARRANTY; without even the implied warranty of  |
// | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the   |
// | GNU General Public License for more details.                    |
// +-----------------------------------------------------------------+
//  Path: /install/functions/install.php
//

function load_full_access_security() {
	global $mainmenu;
	$securitys = null;
	foreach($mainmenu as $menu_item){
		$securitys .= create_id($menu_item);
	}
	if ($securitys == null) return '1:4,';
	else return $securitys;
}

function create_id($array){
	$securitys = '';
	if(isset($array['submenu'])) foreach($array['submenu'] as $menu_item){
		$securitys .= create_id($menu_item);
	}else{
		if(isset($array['security_id'])) $securitys = $array['security_id'] . ':4,';
	}
	return $securitys;
}
?>