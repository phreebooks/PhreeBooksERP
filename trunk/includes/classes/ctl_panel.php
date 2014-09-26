<?php
// +-----------------------------------------------------------------+
// |                   PhreeBooks Open Source ERP                    |
// +-----------------------------------------------------------------+
// | Copyright(c) 2008-2014 PhreeSoft      (www.PhreeSoft.com)       |
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
//  Path: /includes/classes/ctl_panel.php
//
namespace core\classes;
class ctl_panel {
	public $id 					= '';
	public $default_num_rows 	= 20;
	public $description	 		= '';
	public $max_length   		= 20;
	public $menu_id				= 'index';
	public $module_id 			= '';
	public $params				= '';
	private $security_id  		= '';
	private $security_level		= 0;
	public $text		 		= '';
	public $version      		= 1;
	public $valid_user			= false;
	public $size_params			= 0;
	public $default_params 		= array();
	public $row_started			= false;

  	function __construct() {
  		\core\classes\messageStack::debug_log("executing ".__METHOD__ );
  		$this->security_level = \core\classes\user::security_level($this->security_id); // security check
  		$this->user_id = $_SESSION['admin_id'];
  		if (!is_array($this->params)) $this->params = unserialize($this->params);
  	}

  	function pre_install($odd, $my_profile){
  		\core\classes\messageStack::debug_log("executing ".__METHOD__ );
  		$this->valid_user = in_array($this->id, $my_profile);
		$output  = '<tr class="'.($odd?'odd':'even').'"><td align="center">';
		$checked = (in_array($this->id, $my_profile)) ? ' selected' : '';
		$output .=  html_checkbox_field($this->id, '1', $checked, '', $parameters = '');
		$output .=' </td><td>' . $this->text . '</td><td>' . $this->description . '</td></tr>';
		return $output;
	}

  	function install($column_id = 1, $row_id = 0) {
		global $admin;
		\core\classes\messageStack::debug_log("executing ".__METHOD__ );
		if (!$row_id) $row_id 		= $this->get_next_row();
		//$this->params['num_rows']   = $this->default_num_rows;	// defaults to unlimited rows
		$admin->DataBase->exec("insert into " . TABLE_USERS_PROFILES . " set
		  user_id = {$this->user_id}, menu_id = '{$this->menu_id}',
		  dashboard_id = '" . addcslashes('\\'.get_class($this), '\\') . "', column_id = $column_id, row_id = $row_id,
		  params = '"       . serialize($this->default_params) . "'");
  	}

  	/**
  	 * this will be called when a user unchecks the show on page check box.
  	 */
  	function remove() {
		global $admin;
		\core\classes\messageStack::debug_log("executing ".__METHOD__ );
		$result = $admin->DataBase->exec("delete from " . TABLE_USERS_PROFILES . " where user_id = {$this->user_id} and menu_id = '{$this->menu_id}' and dashboard_id = '" . get_class($this) . "'");
  	}

  	/**
  	 * this function will be called when a module is removed.
  	 */

  	function delete(){
		global $admin;
		\core\classes\messageStack::debug_log("executing ".__METHOD__ );
		$result = $admin->DataBase->exec("delete from " . TABLE_USERS_PROFILES . " where dashboard_id = '" . addcslashes('\\'.get_class($this), '\\') . "' and module_id = '{$this->module_id}'");
		foreach ($this->keys as $key) remove_configure($key['key']); // remove all of the keys from the configuration table
		return true;
  	}

  	function update() {
  		global $admin;
  		\core\classes\messageStack::debug_log("executing ".__METHOD__ );
  		$admin->DataBase->exec("update " . TABLE_USERS_PROFILES . " set params = '" . serialize($this->params) . "'
	  		where user_id = {$this->user_id} and menu_id = '{$this->menu_id}'
	    	and dashboard_id = '" . addcslashes('\\'.get_class($this), '\\') . "'");
  	}

  	function build_div($title, $contents, $controls) {
  		\core\classes\messageStack::debug_log("executing ".__METHOD__ );
	  	$output = '';
		$output .= "<!--// start: {$this->id} //-->" . chr(10);
		$output .= '<div id="'.$this->id.'" style="position:relative;" class="easyui-panel" title="'.$this->text.'" data-options="collapsible:true,tools:\'#'.$this->id.'_tt\'">' . chr(10);
		// heading text
		$output .= "<div id='{$this->id}_tt>" . chr(10);
		if ($this->column_id > 1) 				$output .= '	<a href="javascript:void(0)" class="icon-go_previous"	onclick="return move_box(\'' . $this->id . '\', \'move_left\')"></a>' . chr(10);
		if ($this->column_id < MAX_CP_COLUMNS)	$output .= '	<a href="javascript:void(0)" class="icon-go_next"		onclick="return move_box(\'' . $this->id . '\', \'move_right\')"></a>' . chr(10);
		if ($this->row_started == false)		$output .= '	<a href="javascript:void(0)" class="icon-go_up"    onclick="return move_box(\'' . $this->id . '\', \'move_up\')"></a>' . chr(10);
		if ($this->row_id < $this->get_next_row($this->column_id) - 1)
												$output .= '	<a href="javascript:void(0)" class="icon-go_down"    onclick="return move_box(\'' . $this->id . '\', \'move_down\')"></a>' . chr(10);
		$output .= '	<a id="'.$this->id.'_add" href="javascript:void(0)" class="icon-edit"    onclick="return box_edit(\''.$this->id.'\')"></a>' . chr(10);
		$output .= '	<a id="'.$this->id.'_can" href="javascript:void(0)" class="icon-undo"    onclick="return box_cancel(\'' . $this->id . '\')" style="display:none"></a>' . chr(10);
		$output .= '	<a id="'.$this->id.'_del" href="javascript:void(0)" class="icon-cancel"  onclick="return del_box(\'' . $this->id . '\')"></a>' . chr(10);
		//$output .= '	<a href="javascript:void(0)" class="icon-help" onclick="javascript:alert(help)"></a>' . chr(10);
		$output .= '</div>' . chr(10);
		$output .= '<table style="border-collapse:collapse;width:100%">'. chr(10);
		// properties contents
		$output .= '<tbody class="ui-widget-content">' . chr(10);
		$output .= '<tr id="' . $this->id . '_prop" style="display:none"><td colspan="4">' . chr(10);
		$output .= html_form($this->id . '_frm', FILENAME_DEFAULT, gen_get_all_get_params(array('action'))) . chr(10);
		$output .= $controls . chr(10);
		$output .= "<input type='hidden' name='dashboard_id' value='{$this->id}' />" . chr(10);
		$output .= "<input type='hidden' name='column_id' value='{$this->column_id}' />" . chr(10);
		$output .= "<input type= 'hidden' name='row_id' value='{$this->row_id}' />" . chr(10);
		$output .= '</form></td></tr>' . chr(10);
		$output .= "<tr id='{$this->id}_hr' style='display:none'><td colspan='4'><hr /></td></tr>" . chr(10);
		// box contents
		$output .= '<tr><td colspan="4">' . chr(10);
		$output .= "<div id='{$this->id}_body'>" . chr(10);
		$output .= $contents;
		$output .= '</div>';
		$output .= '</td></tr></tbody></table>' . chr(10);
		// finish it up
		$output .= '</div>' . chr(10);
		$output .= "<!--// end: {$this->id} //--><br />" . chr(10) . chr(10);
		return $output;
  	}

	function get_next_row($column_id = 1) {
		global $admin;
		\core\classes\messageStack::debug_log("executing ".__METHOD__ );
		$result = $admin->DataBase->query("select max(row_id) as max_row from " . TABLE_USERS_PROFILES . "
		  where user_id = {$this->user_id} and menu_id = '{$this->menu_id}' and column_id = $column_id");
		return ($result->fields['max_row'] + 1);
	}

	function upgrade($params){
		\core\classes\messageStack::debug_log("executing ".__METHOD__ );
		foreach ($this->default_params as $key => $value){
			if(in_array($key, $params, false)){
				$this->params[$key] =  $params[$key];
			}else{
				$this->params[$key] =  $value;
			}
		}
		$this->update();
		return $this->params;
	}
}