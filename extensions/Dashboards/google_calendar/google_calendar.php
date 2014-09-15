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
// |                                                                 |
// | The license that is bundled with this package is located in the |
// | file: /doc/manual/ch01-Introduction/license.html.               |
// | If not, see http://www.gnu.org/licenses/                        |
// +-----------------------------------------------------------------+
//  Path: /modules/phreedom/dashboards/google_calendar/google_calendar.php
//
//  V1.0 CREATED BY MAINFRAME COMPUTERS (C) 2011  http://www.mainframecomps.com

class google_calendar extends ctl_panel {

  function google_calendar() {
  }

  function Install($column_id = 1, $row_id = 0) {
	global $admin;
	if (!$row_id) $row_id = $this->get_next_row();
	$db->Execute("insert into " . TABLE_USERS_PROFILES . " set
	  user_id = "       . $_SESSION['admin_id'] . ",
	  menu_id = '"      . $this->menu_id . "',
	  module_id = '"    . $this->module_id . "',
	  dashboard_id = '" . $this->dashboard_id . "',
	  column_id = "     . $column_id . ",
	  row_id = "        . $row_id . ",
	  params = ''");
  }

  function Remove() {
	global $admin;
	$result = $db->Execute("delete from " . TABLE_USERS_PROFILES . "
	  where user_id = " . $_SESSION['admin_id'] . " and menu_id = '" . $this->menu_id . "'
	    and dashboard_id = '" . $this->dashboard_id . "'");
  }

  function Output($params) {
	global $admin;
	// Build content box
	$contents = '';
	$contents .= 'PASTE GOOGLE IFRAME CODE HERE, I MADE MY CALENDAR 450 X 400';

	return $this->build_div(CP_GOOGLE_CALENDAR_TITLE, $contents, $control);
  }

}
?>