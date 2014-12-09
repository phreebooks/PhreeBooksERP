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
//  Path: /modules/phreedom/dashboards/my_notes/my_notes.php
//
// Revision history
// 2011-07-01 - Added version number for revision control
namespace phreedom\dashboards\my_notes;
class my_notes extends \core\classes\ctl_panel {
	public $description	 		= CP_MY_NOTES_DESCRIPTION;
	public $security_id  		= SECURITY_ID_MY_PROFILE;
	public $text		 		= CP_MY_NOTES_TITLE;
	public $version      		= '4.0';

	function output() {
		global $admin;
		$contents = '';
		$control  = '';
		// Build control box form data
		$control  = '  <div class="row">' . chr(10);
		$control .= '    <div style="white-space:nowrap">';
		$control .= TEXT_NOTE . ': &nbsp;' . html_input_field('my_notes_field_0', '', 'size="64"') . '<br />';
		$control .= '&nbsp;&nbsp;&nbsp;&nbsp;';
		$control .= html_submit_field('sub_my_notes', TEXT_ADD);
		$control .= html_hidden_field('my_notes_rId', '');
		$control .= '    </div>' . chr(10);
		$control .= '  </div>' . chr(10);
		// Build content box
		$contents = '';
		if (is_array($this->params)) {
			$index = 1;
		  	foreach ($this->params as $my_note) {
		    	$contents .= '  <div>';
				$contents .= '    <div style="float:right; height:16px;">';
				$contents .= html_icon('phreebooks/dashboard-remove.png', TEXT_REMOVE, 'small', 'onclick="return del_index(\'' . $this->id . '\', ' . $index . ')"');
				$contents .= '    </div>' . chr(10);
				$contents .= '    <div style="min-height:16px;">&#9679; '. $my_note . '</div>' . chr(10);
		    	$contents .= '  </div>' . chr(10);
				$index++;
		  	}
		} else {
		  	$contents = TEXT_NO_RESULTS_FOUND;
		}
		return $this->build_div($contents, $control);
	}

	function update() {
		global $admin;
		$my_note   = db_prepare_input($_POST['my_notes_field_0']);
		$remove_id = db_prepare_input($_POST['my_notes_rId']);
		// do nothing if no title or url entered
		if (!$remove_id && $my_note == '') return;
		// fetch the current params
		$result = $admin->DataBase->query("SELECT params FROM " . TABLE_USERS_PROFILES . "
			WHERE user_id = {$_SESSION['admin_id']} and menu_id = '{$this->menu_id}' and dashboard_id = '" . get_class($this) . "'");
		if ($remove_id) { // remove element
		  	$this->params	= unserialize($result['params']);
		  	$first_part 	= array_slice($this->params, 0, $remove_id - 1);
		  	$last_part  	= array_slice($this->params, $remove_id);
		  	$this->params	= array_merge($first_part, $last_part);
		} elseif ($result['params']) { // append new note and sort
		  	$this->params   = unserialize($result['params']);
		  	$this->params[] = $my_note;
		} else { // first entry
		  	$this->params[] = $my_note;
		}
		ksort($this->params);
		db_perform(TABLE_USERS_PROFILES, array('params' => serialize($this->params)), "update", "user_id = {$_SESSION['admin_id']} and menu_id = '{$this->menu_id}' and dashboard_id = '" . get_class($this). "'");
	}

}
?>