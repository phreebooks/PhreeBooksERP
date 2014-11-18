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
//  Path: /modules/phreebooks/dashboards/to_receive_inv/to_receive_inv.php
//
// Revision history
// 2012-07-01 - new
namespace phreebooks\dashboards\to_receive_inv;
require_once(DIR_FS_MODULES . 'phreebooks/functions/phreebooks.php');
class to_receive_inv extends \core\classes\ctl_panel {
	public $id					= 'to_receive_inv';
	public $description	 		= CP_TO_RECEIVE_INV_DESCRIPTION;
	public $security_id  		= SECURITY_ID_PURCHASE_INVENTORY;
	public $text		 		= CP_TO_RECEIVE_INV_TITLE;
	public $version      		= '4.0';
	public $default_params 		= array('num_rows'=> 0);

	function output() {
		global $admin, $currencies;
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
		$control .= html_pull_down_menu('to_receive_inv_field_0', $list_length, $this->params['num_rows']);
		$control .= html_submit_field('sub_to_receive_inv', TEXT_SAVE);
		$control .= '</div></div>';
		// Build content box
		$total = 0;
		$temp = "SELECT id, purchase_invoice_id, total_amount, bill_primary_name, currencies_code, currencies_value, post_date, journal_id
		  FROM " . TABLE_JOURNAL_MAIN . "
		  WHERE journal_id in (6,7) and waiting = '1' ORDER BY post_date DESC, purchase_invoice_id DESC";
		if ($this->params['num_rows']) $temp .= " LIMIT " . $this->params['num_rows'];
		$sql = $admin->DataBase->prepare($temp);
		$sql->execute();
		if ($sql->rowCount() < 1) {
			$contents = TEXT_NO_RESULTS_FOUND;
		} else {
			while ($result = $sql->fetch(\PDO::FETCH_LAZY)){
			  	$inv_balance = $result['total_amount'] - fetch_partially_paid($result['id']);
			  	if ($result['journal_id'] == 7) $inv_balance = -$inv_balance;
			 	$total += $inv_balance;
				$contents .= '<div style="float:right">' . $currencies->format_full($inv_balance, true, $result['currencies_code'], $result['currencies_value']) . '</div>';
				$contents .= '<div>';
				$contents .= '<a href="' . html_href_link(FILENAME_DEFAULT, "module=phreebooks&amp;page=orders&amp;oID={$result['id']}&amp;jID={$result['journal_id']}&amp;action=edit", 'SSL') . '">';
				$contents .= gen_locale_date($result['post_date']) . ' - ';
				if ($result['purchase_invoice_id'] != '') $contents .= $result['purchase_invoice_id'] . ' - ';
				$contents .= htmlspecialchars($result['bill_primary_name']);
				$contents .= '</a></div>' . chr(10);
			}
		}
		if (!$this->params['num_rows'] && $sql->rowCount() > 0) {
		  	$contents .= '<div style="float:right">' . $currencies->format_full($total, true, DEFAULT_CURRENCY, 1) . '</div>';
			$contents .= '<div><b>' . TEXT_TOTAL . '</b></div>' . chr(10);
		}
		return $this->build_div($contents, $control);
	}

	function update() {
		if(count($this->params) == 0){
			$this->params['num_rows'] = db_prepare_input($_POST['to_receive_inv_field_0']);
		}
		parent::update();
	}
}
?>