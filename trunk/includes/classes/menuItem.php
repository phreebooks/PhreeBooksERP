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
//  Path: /includes/classes/menuItem.php
//
namespace core\classes;
class menuItem {
	public $order;
	public $text;
	public $security_id = 0;
	public $link;
	public $show_in_users_settings = true;
	public $params;
	public $submenu;
	public $required_module;
	public $icon;
	
	public function __construct($order, $text, $action, $security_id, $constant){
		$this->order = $order;
		$this->text  = $text;
		$this->security_id = $security_id;
		$this->link = $action;
		$this->required_module = $constant;
	}
	
	function output(){
		if ($this->show() == false) return ;
		if (is_array($this->submenu)){
			usort($this->submenu, array($this,'sortByOrder'));
			$show = false; 
			foreach($this->submenu as $menu_item) if ($menu_item->show()) $show = true;
			if ($show){
				echo "  <li><a href='".html_href_link(FILENAME_DEFAULT, $this->link, 'SSL')."' {$this->params}> $this->icon $this->text</a>";
				echo '    <ul>';
				foreach($this->submenu as $menu_item) $menu_item->output();
				echo '    </ul>';
				echo '  </li>';
			}
		}else{
			echo "  <li><a href='".html_href_link(FILENAME_DEFAULT, $this->link, 'SSL')."' {$this->params}>";
			if ($this->text == TEXT_HOME && ENABLE_ENCRYPTION && strlen($_SESSION['admin_encrypt']) > 0) echo html_icon('emblems/emblem-readonly.png', TEXT_ENCRYPTION_KEY_IS_SET, 'small');
			echo "$this->icon $this->text</a>  </li>".chr(10);
		}
	}
	
	function sortByOrder($a, $b) {
		if (is_integer($a->order) && is_integer($b->order)) return $a->order - $b->order;
		return strcmp($a->order, $b->order);
	}
	
	function show(){
		if ($this->required_module != ''){
			if (is_array($this->required_module)) {
				$temp = false;
				foreach ($this->required_module as $key) if (defined($key)) $temp = true;
				if ($temp == false ) return false;
			} else{
				if(!defined($this->required_module)) return false;
			}
		}
		if ($this->security_id > 0 && \core\classes\user::security_level($this->security_id) != 0 ) return false;
		return true;
	}
	
	function appendsubmenu(\core\classes\menuItem $menuitems){
		foreach ($menuitems as $key => $menuitem){ 
			$this->submenu[$key] = $menuitem;
			if (is_array($menuitem->submenu)) {
				print_r($menuitem);
//				$key = key($menuitems);
//				$this->submenu[$key]->appendsubmenu($menuitem->submenu);
			}
//			$this->submenu[$key]->appendsubmenu($menuitem->submenu);
		}
	}
	
}

?>