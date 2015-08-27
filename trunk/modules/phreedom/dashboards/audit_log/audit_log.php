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
// |                                                                 |
// | The license that is bundled with this package is located in the |
// | file: /doc/manual/ch01-Introduction/license.html.               |
// | If not, see http://www.gnu.org/licenses/                        |
// +-----------------------------------------------------------------+
//  Path: /modules/phreebooks/dashboards/audit_log/audit_log.php
//
namespace phreedom\dashboards\audit_log;
class audit_log extends \core\classes\ctl_panel {
	public $description	 		= CP_AUDIT_LOG_DESCRIPTION;
	public $max_length   		= 50;
	public $security_id  		= SECURITY_ID_CONFIGURATION;
	public $text		 		= CP_AUDIT_LOG_TITLE;
	public $version      		= '4.0';
	public $default_params 		= array('num_rows'=> 50, 'today_minus' => '0');

	function install($column_id = 1, $row_id = 0) {
		$this->params['num_rows']    = $this->max_length;	// defaults to 20 rows
	    $this->params['today_minus'] = '0';         		// defaults to today
		parent::install($column_id, $row_id);
	}

	function output() {
		global $admin;
		if(count($this->params) != count($this->default_params)) { //upgrading
			$this->params = $this->upgrade($this->params);
		}
		$list_length = array();
		$contents = '';
		$control  = '';
		for ($i = 0; $i <= $this->max_length; $i++) $list_length[] = array('id' => $i, 'text' => $i);

// Build control box form data1
        $control = '<div class="row">';
        $control .= '<div style="white-space:nowrap">' . CP_AUDIT_LOG_DISPLAY;
        $control .= '<select name="today_minus" onchange="">';
        for ($i = 0; $i <= 365; $i++) {
        	$control .= "<option value='$i'" . ($this->params['today_minus'] == $i ? ' selected="selected"' : '') . ">$i</option>";
        }
        $control .= '</select>' . CP_AUDIT_LOG_DISPLAY2 . '&nbsp;&nbsp;&nbsp;&nbsp;';
        $control .= '</div></div>';

// Build control box form data2
        $control .= '<div class="row">';
        $control .= '<div style="white-space:nowrap">' . TEXT_SHOW . TEXT_SHOW_NO_LIMIT;
        $control .= html_pull_down_menu('audit_log_num_rows', $list_length, $this->params['num_rows']);
        $control .= html_submit_field('sub_audit_log', TEXT_SAVE);
        $control .= '</div></div>';

// Build content box
        $temp = "SELECT a.action_date, a.action, a.reference_id, a.amount, u.display_name FROM ".TABLE_AUDIT_LOG." as a, ".TABLE_USERS." as u
          WHERE a.user_id = u.admin_id and a.action_date >= '" . date('Y-m-d', strtotime('-' . $this->params['today_minus'] . ' day')) . "'
          and a.action_date <= '" . date('Y-m-d', strtotime('-' . $this->params['today_minus'] + 1 . ' day')) . "' ORDER BY a.action_date desc";

       	if ($this->params['num_rows']) $temp .= " LIMIT " . $this->params['num_rows'];
       	$sql = $admin->DataBase->prepare($temp);
		$sql->execute();
		if ($sql->fetch(\PDO::FETCH_NUM) < 1) {
			$contents = TEXT_NO_RESULTS_FOUND;
		} else {
			while ($result = $sql->fetch(\PDO::FETCH_LAZY)){
            	$contents .= '<div style="float:right">' . $admin->currencies->format_full($result['amount'], true, DEFAULT_CURRENCY, 1, 'fpdf') . '</div>';
                $contents .= '<div>';
                $contents .= $result['display_name'] . '-->' . $result['action'] . '-->' . $result['reference_id'];
                $contents .= '</div>' . chr(10);
          	}
        }
        $this->text = CP_AUDIT_LOG_TITLE . " " . date('Y-m-d', strtotime('-' . $this->params['today_minus'] . ' day'));
       	return $this->build_div( $contents, $control);
 	}

 	function update() {
 		if(count($this->params) == 0){
        	$this->params['num_rows'] 		= db_prepare_input($_POST['audit_log_num_rows']);
        	$this->params['today_minus'] 	= db_prepare_input($_POST['today_minus']);
 		}
		parent::update();
  	}

}

?>
