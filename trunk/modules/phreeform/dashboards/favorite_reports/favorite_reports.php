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
//  Path: /modules/phreeform/dashboards/favorite_reports.php
//
// Revision history
// 2011-07-01 - Added version number for revision control
namespace phreeform\dashboards\favorite_reports;

require_once(DIR_FS_MODULES . 'phreeform/functions/phreeform.php');

class favorite_reports extends \core\classes\ctl_panel {
	public $description	 		= CP_FAVORITE_REPORTS_DESCRIPTION;
	public $security_id  		= SECURITY_ID_PHREEFORM;
	public $text		 		= CP_FAVORITE_REPORTS_TITLE;
	public $version      		= '4.0';

	function output() {
		global $admin;
		$contents = '';
		$control  = '';
		// load the report list
		$data_array = array(array('id' => '', 'text' => TEXT_PLEASE_SELECT));
		$sql = $admin->DataBase->prepare("SELECT id, security, doc_title FROM " . TABLE_PHREEFORM . " WHERE doc_ext IN ('rpt','frm') ORDER BY doc_title");
		$sql->execute();
		while ($result = $sql->fetch(\PDO::FETCH_LAZY)){
		  	if (security_check($result['security'])) {
				$data_array[] = array('id' => $result['id'], 'text' => $result['doc_title']);
		  	}
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
		if (is_array($this->params)) {
		  	$index = 1;
		  	foreach ($this->params as $id => $description) {
				$contents .= '<div style="float:right; height:16px;">';
				$contents .= html_icon('phreebooks/dashboard-remove.png', TEXT_REMOVE, 'small', "onclick='return del_index(\"{$this->id}\", $index)'");
				$contents .= '</div>';
				$contents .= '<div style="height:16px;">';
				$contents .= "  <a href='index.php?module=phreeform&amp;page=popup_gen&amp;rID={$id}' target='_blank'>{$description}</a>" . chr(10);
				$contents .= '</div>';
				$index++;
		  	}
		} else {
		  	$contents = TEXT_NO_RESULTS_FOUND;
		}
		return $this->build_div($contents, $control);
	}

	function update() {
		global $admin;
		$report_id   = db_prepare_input($_POST['report_id']);
		$result      = $admin->DataBase->query("SELECT doc_title FROM " . TABLE_PHREEFORM . " WHERE id = '$report_id'");
		$description = $result['doc_title'];
		$remove_id   = db_prepare_input($_POST['favorite_reports_rId']);
		// do nothing if no title or url entered
		if (!$remove_id && $report_id == '') return;
		// fetch the current params
		$result = $admin->DataBase->query("SELECT params FROM " . TABLE_USERS_PROFILES . "
		  WHERE user_id = {$_SESSION['admin_id']} and menu_id = '{$this->menu_id}' and dashboard_id = '{$this->id}'");
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
		$admin->DataBase->exec("UPDATE " . TABLE_USERS_PROFILES . " SET params = '" . serialize($this->params) . "'
		  WHERE user_id = {$_SESSION['admin_id']} and menu_id = '{$this->menu_id}' and dashboard_id = '{$this->id}'");
	}
}
?>