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
//  Path: /modules/canadascooters/dashboards/company_to_do/company_to_do.php
//
// Revision history
// 2011-12-01 - Initial release

class company_to_do extends ctl_panel {
	public $dashboard_id 		= 'company_to_do';
	public $description	 		= CP_COMPANY_TO_DO_DESCRIPTION;
	public $security_id  		= SECURITY_ID_PHREEFORM;
	public $title		 		= CP_COMPANY_TO_DO_TITLE;
	public $version      		= 3.5; 
	
  	function Install($column_id = 1, $row_id = 0) {
		global $db;		
		// fetch the pages params to copy to new install
		$result = $db->Execute("select params from " . TABLE_USERS_PROFILES . "
	  	  where menu_id = '".$this->menu_id."' and dashboard_id = '".$this->dashboard_id."'"); // just need one
		$this->default_params = unserialize($result->fields['params']);
		parent::Install($column_id, $row_id);
  	}

  	function Output($params) {
		global $db;
		$contents = '';
		$control  = '';
		// Build control box form data
		$control  = '  <div class="row">' . chr(10);
		$control .= '    <div style="white-space:nowrap">';
		$control .= TEXT_NOTE . '&nbsp;' . html_input_field('company_to_do_field_0', '', 'size="50"') . '<br />';
		$control .= '&nbsp;&nbsp;&nbsp;&nbsp;';
		$control .= html_submit_field('sub_to_do', TEXT_ADD);
		$control .= html_hidden_field('company_to_do_rId', '');
		$control .= '    </div>' . chr(10);
		$control .= '  </div>' . chr(10);
		// Build content box
		$contents = '';
		if (is_array($params)) {
		  	$index = 1;
		  	foreach ($params as $to_do) {
		    	$contents .= '  <div>';
				$contents .= '    <div style="float:right; height:16px;">';
				$contents .= html_icon('phreebooks/dashboard-remove.png', TEXT_REMOVE, 'small', 'onclick="return del_index(\'' . $this->dashboard_id . '\', ' . $index . ')"');
				$contents .= '    </div>' . chr(10);
				$contents .= '    <div style="min-height:16px;">&#9679; '. $to_do . '</div>' . chr(10);
		    	$contents .= '  </div>' . chr(10);
				$index++;
		  	}
		} else {
		  	$contents = ACT_NO_RESULTS;
		}
		return $this->build_div('', $contents, $control);
	}

  	function Update() {
		global $db;
		$add_to_do = db_prepare_input($_POST['company_to_do_field_0']);
		$remove_id = db_prepare_input($_POST['company_to_do_rId']);
		// do nothing if no title or url entered
		if (!$remove_id && $add_to_do == '') return;
		// fetch the current params
		$result = $db->Execute("select params from " . TABLE_USERS_PROFILES . "
	  	  where user_id = " . $_SESSION['admin_id'] . " and menu_id = '" . $this->menu_id . "' 
		  and dashboard_id = '" . $this->dashboard_id . "'");
		if ($remove_id) { // remove element
			$this->params	= unserialize($result->fields['params']);
			$first_part 	= array_slice($this->params, 0, $remove_id - 1);
			$last_part  	= array_slice($this->params, $remove_id);
			$this->params   = array_merge($first_part, $last_part);
		} elseif ($result->fields['params']) { // append new note and sort
		  	$this->params     = unserialize($result->fields['params']);
		  	$this->params[]   = $add_to_do;
		} else { // first entry
		  	$this->params[]   = $add_to_do;
		}
		ksort($this->params);
		db_perform(TABLE_USERS_PROFILES, array('params' => serialize($this->params)), "update", "menu_id = '".$this->menu_id."' and dashboard_id = '".$this->dashboard_id."'");
	}

}
?>