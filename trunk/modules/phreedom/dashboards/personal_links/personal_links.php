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
//  Path: /modules/phreedom/dashboards/personal_links/personal_links.php
//
// Revision history
// 2011-07-01 - Added version number for revision control

class personal_links extends ctl_panel {
	public $dashboard_id 		= 'personal_links';
	public $description	 		= CP_PERSONAL_LINKS_DESCRIPTION;
	public $security_id  		= SECURITY_ID_MY_PROFILE;
	public $title		 		= CP_PERSONAL_LINKS_TITLE;
	public $version      		= 3.5;

	function Output($params) {
		global $db;
		$contents = '';
		$control  = '';
		// Build control box form data
		$control  = '<div class="row">';
		$control .= '<div style="white-space:nowrap">';
		$control .= TEXT_TITLE . '&nbsp;' . html_input_field('personal_links_field_0', '', 'size="40"') . '<br />';
		$control .= TEXT_URL   . '&nbsp;' . html_input_field('personal_links_field_1', '', 'size="64"');
		$control .= '&nbsp;&nbsp;&nbsp;&nbsp;';
		$control .= html_submit_field('sub_personal_links', TEXT_ADD);
		$control .= html_hidden_field('personal_links_rId', '');
		$control .= '</div></div>';
		// Build content box
		$contents = '';
		if (is_array($params)) {
			$index = 1;
			foreach ($params as $title => $hyperlink) {
				$contents .= '<div style="float:right; height:16px;">';
				$contents .= html_icon('phreebooks/dashboard-remove.png', TEXT_REMOVE, 'small', 'onclick="return del_index(\'' . $this->dashboard_id . '\', ' . $index . ')"');
				$contents .= '</div>';
				$contents .= '<div style="min-height:16px;">';
				$contents .= '<a href="' . $hyperlink . '" target="_blank">' . $title . '</a>' . chr(10);
				$contents .= '</div>';
				$index++;
			}
		} else {
			$contents = ACT_NO_RESULTS;
		}
		return $this->build_div('', $contents, $control);
	}

	function Update() {
		global $db;
		$my_title  = db_prepare_input($_POST['personal_links_field_0']);
		$my_url    = db_prepare_input($_POST['personal_links_field_1']);
		$remove_id = db_prepare_input($_POST['personal_links_rId']);
		// do nothing if no title or url entered
		if (!$remove_id && ($my_title == '' || $my_url == '')) return; 
		// fetch the current params
		$result = $db->Execute("select params from " . TABLE_USERS_PROFILES . "
		  where user_id = " . $_SESSION['admin_id'] . " and menu_id = '" . $this->menu_id . "' 
		  and dashboard_id = '" . $this->dashboard_id . "'");
		if ($remove_id) { // remove element
		  	$this->params	= unserialize($result->fields['params']);
		  	$first_part 	= array_slice($this->params, 0, $remove_id - 1);
		  	$last_part  	= array_slice($this->params, $remove_id);
		  	$this->params	= array_merge($first_part, $last_part);
		} elseif ($result->fields['params']) { // append new note and sort
		  	$this->params   = unserialize($result->fields['params']);
		  	$this->params[$my_title] = $my_url;
		} else { // first entry
		  	$this->params[$my_title] = $my_url;
		}
		ksort($this->params);	
		db_perform(TABLE_USERS_PROFILES, array('params' => serialize($this->params)), "update", "user_id = ".$_SESSION['admin_id']." and menu_id = '".$this->menu_id."' and dashboard_id = '".$this->dashboard_id."'");
	}
}
?>