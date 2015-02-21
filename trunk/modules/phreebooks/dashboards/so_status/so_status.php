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
//  Path: /modules/phreebooks/dashboards/so_status/so_status.php
//
// Revision history
// 2011-07-01 - Added version number for revision control
namespace phreebooks\dashboards\so_status;
class so_status extends \core\classes\ctl_panel {
	public $description	 		= CP_SO_STATUS_DESCRIPTION;
	public $security_id  		= SECURITY_ID_SALES_ORDER;
	public $text		 		= CP_SO_STATUS_TITLE;
	public $version      		= '4.0';
	public $default_params 		= array('num_rows'=> 0, 'order'   => 'asc', 'limit'   => 1);

	function output() {
		global $admin;
		if(count($this->params) != count($this->default_params)) { //upgrading
			$this->params = $this->upgrade($this->params);
		}
		$contents = '';
		$control  = '';
		$list_length = array();
		for ($i = 0; $i <= $this->max_length; $i++) $list_length[] = array('id' => $i, 'text' => $i);
		$list_order = array(
		  	array('id'=>'asc', 'text'=>TEXT_ASC),
		  	array('id'=>'desc','text'=>TEXT_DESCENDING_SHORT),
		);
		$list_limit = array(
		  	array('id'=>'0', 'text'=>TEXT_NO),
		  	array('id'=>'1', 'text'=>TEXT_YES),
		);
		// Build control box form data
		$control  = '<div class="row">';
		$control .= '  <div style="white-space:nowrap">';
		$control .= TEXT_SHOW.TEXT_SHOW_NO_LIMIT.'&nbsp'.html_pull_down_menu('so_status_field_0', $list_length,$this->params['num_rows']).'<br />';
		$control .= CP_SO_STATUS_SORT_ORDER     .'&nbsp'.html_pull_down_menu('so_status_field_1', $list_order, $this->params['order']).'<br />';
		$control .= CP_SO_STATUS_HIDE_FUTURE    .'&nbsp'.html_pull_down_menu('so_status_field_2', $list_limit, $this->params['limit']);
		$control .= html_submit_field('sub_so_status', TEXT_SAVE);
		$control .= '  </div>';
		$control .= '</div>';
		// Build content box
		$temp = "SELECT id, post_date, purchase_invoice_id, bill_primary_name, total_amount, currencies_code, currencies_value
		  FROM " . TABLE_JOURNAL_MAIN . " WHERE journal_id = 10 and closed = '0'";
		if ($this->params['limit']=='1')    $temp .= " and post_date <= '".date('Y-m-d')."'";
		if ($this->params['order']=='desc') $temp .= " ORDER BY post_date desc";
		if ($this->params['num_rows'])      $temp .= " LIMIT " . $this->params['num_rows'];
		$sql = $admin->DataBase->prepare($temp);
		$sql->execute();
		if ($sql->rowCount() < 1) {
			$contents = TEXT_NO_RESULTS_FOUND;
		} else {
			while ($result = $sql->fetch(\PDO::FETCH_LAZY)){
			  	$contents .= '<div style="float:right">' ;
			  	$contents .= html_button_field('invoice_' . $result['id'], TEXT_INVOICE, 'onclick="window.open(\'' . html_href_link(FILENAME_DEFAULT, "module=phreebooks&amp;page=orders&amp;oID={$result['id']}&amp;jID=12&amp;action=prc_so", 'SSL') . '\',\'_blank\')"') . "  ";
				$contents .= $admin->currencies->format_full($result['total_amount'], true, $result['currencies_code'], $result['currencies_value']);
				$contents .= '</div>';
				$contents .= '<div>';
				$contents .= '<a href="' . html_href_link(FILENAME_DEFAULT, "module=phreebooks&amp;page=orders&amp;oID={$result['id']}&amp;jID=10&amp;action=edit", 'SSL') . '">';
				$contents .= $result['purchase_invoice_id'] . ' - ' . gen_locale_date($result['post_date']);
				$name      = gen_trim_string($result['bill_primary_name'], 20, true);
				$contents .= ' ' . htmlspecialchars($name);
				$contents .= '</a></div>' . chr(10);
			}
	  	}
		return $this->build_div($contents, $control);
	}

	function update() {
		if(count($this->params) == 0){
			$this->params = array(
			  'num_rows'=> db_prepare_input($_POST['so_status_field_0']),
			  'order'   => db_prepare_input($_POST['so_status_field_1']),
			  'limit'   => db_prepare_input($_POST['so_status_field_2']),
			);
		}
		parent::update();
	}
}
?>