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
//  Path: /modules/phreebooks/dashboards/todays_sales/todays_sales.php
//
// Revision history
// 2011-07-01 - Added version number for revision control
namespace phreebooks\dashboards\todays_sales;
class todays_sales extends \core\classes\ctl_panel {
	public $description	 		= CP_TODAYS_SALES_DESCRIPTION;
	public $security_id  		= SECURITY_ID_SALES_INVOICE;
	public $text		 		= CP_TODAYS_SALES_TITLE;
	public $version      		= '4.0';
	public $default_params 		= array('num_rows'=> 0);

	function output() {
		global $admin;
		if(count($this->params) != count($this->default_params)) { //upgrading
			$this->params = $this->upgrade($this->params);
		}
		$list_length = array();
		$contents = '';
		$control  = '';
		for ($i = 0; $i <= $this->max_length; $i++) $list_length[] = array('id' => $i, 'text' => $i);
		// Build control box form data
		$control  = '<div class="row">';
		$control .= '<div style="white-space:nowrap">' . TEXT_SHOW . TEXT_SHOW_NO_LIMIT;
		$control .= html_pull_down_menu('todays_sales_field_0', $list_length, $this->params['num_rows']);
		$control .= html_submit_field('sub_todays_sales', TEXT_SAVE);
		$control .= '</div></div>';

		// Build content box
		$total = 0;
		$temp = "SELECT id, purchase_invoice_id, total_amount, bill_primary_name, currencies_code, currencies_value
		  FROM " . TABLE_JOURNAL_MAIN . "
		  WHERE journal_id = 12 and post_date = '" . date('Y-m-d', time()) . "' ORDER BY purchase_invoice_id";
		if ($this->params['num_rows']) $temp .= " LIMIT " . $this->params['num_rows'];
		$sql = $admin->DataBase->prepare($temp);
		$sql->execute();
		if ($sql->fetch(\PDO::FETCH_NUM) < 1) {
			$contents = TEXT_NO_RESULTS_FOUND;
		} else {
			while ($result = $sql->fetch(\PDO::FETCH_LAZY)){
			 	$total += $result['total_amount'];
				$contents .= '<div style="float:right">' . $admin->currencies->format_full($result['total_amount'], true, $result['currencies_code'], $result['currencies_value']) . '</div>';
				$contents .= '<div>';
				$contents .= '<a href="' . html_href_link(FILENAME_DEFAULT, "module=phreebooks&amp;page=orders&amp;oID={$result['id']}&amp;jID=12&amp;action=edit", 'SSL') . '">';
				$contents .= $result['purchase_invoice_id'] . ' - ' . htmlspecialchars(gen_trim_string($result['bill_primary_name'], 20, true));
				$contents .= '</a></div>' . chr(10);
			}
		}
		if (!$this->params['num_rows'] && $sql->fetch(\PDO::FETCH_NUM) > 0) {
		  	$contents .= '<div style="float:right">' . $admin->currencies->format_full($total, true, DEFAULT_CURRENCY, 1) . '</div>';
		  	$contents .= '<div><b>' . TEXT_TOTAL . '</b></div>' . chr(10);
		}
		return $this->build_div($contents, $control);
	}

	function update() {
		if(count($this->params) == 0){
			$this->params['num_rows'] = db_prepare_input($_POST['todays_sales_field_0']);
		}
		parent::update();
	}
}
?>