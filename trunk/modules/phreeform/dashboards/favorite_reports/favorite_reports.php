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
//  Path: /modules/phreeform/dashboards/favorite_reports.php
//
// Revision history
// 2011-07-01 - Added version number for revision control
namespace phreeform\dashboards\favorite_reports;

require_once(DIR_FS_MODULES . 'phreeform/functions/phreeform.php');

class favorite_reports extends \core\classes\ctl_panel {
	public $id			 		= 'favorite_reports';
	public $description	 		= CP_FAVORITE_REPORTS_DESCRIPTION;
	public $security_id  		= SECURITY_ID_PHREEFORM;
	public $text		 		= CP_FAVORITE_REPORTS_TITLE;
	public $version      		= '3.5';
	public $module_id 			= 'phreeform';

	function output($params) {
		global $admin;
		$contents = '';
		$control  = '';
		// load the report list
		$result = $admin->DataBase->query("select id, security, doc_title from " . TABLE_PHREEFORM . "
		  where doc_ext in ('rpt','frm') order by doc_title");
		$data_array = array(array('id' => '', 'text' => TEXT_PLEASE_SELECT));
		$type_array = array();
		while(!$result->EOF) {
		  	if (security_check($result->fields['security'])) {
				$data_array[] = array('id' => $result->fields['id'], 'text' => $result->fields['doc_title']);
		  	}
		  	$result->MoveNext();
		}
		// Build control box form data
		$control  = '<div class="row">';
		$control .= '<div style="white-space:nowrap">';
		$control .= TEXT_REPORT . '&nbsp;' . html_pull_down_menu('report_id', $data_array);
		$control .= '&nbsp;&nbsp;&nbsp;&nbsp;';
		$control .= html_submit_field('sub_favorite_reports', TEXT_ADD);
		$control .= html_hidden_field('favorite_reports_rId', '');
		$control .= '</div></div>';

		// Build content box
		$contents = '';
		if (is_array($params)) {
		  	$index = 1;
		  	foreach ($params as $id => $description) {
				$contents .= '<div style="float:right; height:16px;">';
				$contents .= html_icon('phreebooks/dashboard-remove.png', TEXT_REMOVE, 'small', 'onclick="return del_index(\'' . $this->id . '\', ' . $index . ')"');
				$contents .= '</div>';
				$contents .= '<div style="height:16px;">';
				$contents .= '  <a href="index.php?module=phreeform&amp;page=popup_gen&amp;rID=' . $id . '" target="_blank">' . $description . '</a>' . chr(10);
				$contents .= '</div>';
				$index++;
		  	}
		} else {
		  	$contents = TEXT_NO_RESULTS_FOUND;
		}
		return $this->build_div('', $contents, $control);
	}

	function update() {
		global $admin;
		$report_id   = db_prepare_input($_POST['report_id']);
		$result      = $admin->DataBase->query("select doc_title from " . TABLE_PHREEFORM . " where id = '" . $report_id . "'");
		$description = $result->fields['doc_title'];
		$remove_id   = db_prepare_input($_POST['favorite_reports_rId']);
		// do nothing if no title or url entered
		if (!$remove_id && $report_id == '') return;
		// fetch the current params
		$result = $admin->DataBase->query("select params from " . TABLE_USERS_PROFILES . "
		  where user_id = " . $_SESSION['admin_id'] . " and menu_id = '" . $this->menu_id . "'
		  and dashboard_id = '" . $this->id . "'");
		if ($remove_id) { // remove element
		  	$this->params = unserialize($result->fields['params']);
		  	$temp   = array();
		  	$index  = 1;
		  	foreach ($this->params as $key => $value) {
				if ($index <> $remove_id) $temp[$key] = $value;
				$index++;
		  	}
		  	$this->params = $temp;
		} elseif ($result->fields['params']) { // append new url and sort
		  	$this->params = unserialize($result->fields['params']);
		  	$this->params[$report_id] = $description;
		} else { // first entry
			$this->params = array($report_id => $description);
		}
		asort($this->params);
		$admin->DataBase->query("update " . TABLE_USERS_PROFILES . " set params = '" . serialize($this->params) . "'
		  where user_id = " . $_SESSION['admin_id'] . " and menu_id = '" . $this->menu_id . "'
		  and dashboard_id = '" . $this->id . "'");
	}
}
?>