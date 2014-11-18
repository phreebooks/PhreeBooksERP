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
//  Path: /modules/phreebooks/dashboards/todays_s_quotes/todays_s_quotes.php
//
namespace phreebooks\dashboards\todays_s_quotes;
require_once(DIR_FS_MODULES  . 'phreebooks/config.php');
class todays_s_quotes extends \core\classes\ctl_panel {
	public $id			 		= 'todays_s_quotes';
	public $description	 		= CP_TODAYS_S_QUOTES_DESCRIPTION;
	public $security_id  		= SECURITY_ID_SALES_QUOTE;
	public $text		 		= CP_TODAYS_S_QUOTES_TITLE;
	public $version      		= '4.0';
	public $default_params 		= array('num_rows'=> 0);

	function output() {
		global $admin, $currencies;
		$list_length = array();
		if(count($this->params) != count($this->default_params)) { //upgrading
			$this->params = $this->upgrade($this->params);
		}
		$contents = '';
		$control  = '';
		for ($i = 0; $i <= $this->max_length; $i++) $list_length[] = array('id' => $i, 'text' => $i);
		// Build control box form data
		$control  = '<div class="row">';
		$control .= '<div style="white-space:nowrap">' . TEXT_SHOW . TEXT_SHOW_NO_LIMIT;
		$control .= html_pull_down_menu('todays_s_quotes_field_0', $list_length, $this->params['num_rows']);
		$control .= html_submit_field('sub_todays_s_quotes', TEXT_SAVE);
		$control .= '</div></div>';
		// Build content box
		$total = 0;
		$temp = "SELECT id, purchase_invoice_id, total_amount, bill_primary_name, currencies_code, currencies_value
		  FROM " . TABLE_JOURNAL_MAIN . " WHERE journal_id = 9 and post_date = '" . date('Y-m-d') . "' ORDER BY purchase_invoice_id";
		if ($this->params['num_rows']) $temp .= " LIMIT " . $this->params['num_rows'];
		$sql = $admin->DataBase->prepare($temp);
		$sql->execute();
		if ($sql->rowCount() < 1) {
			$contents = TEXT_NO_RESULTS_FOUND;
		} else {
			while ($result = $sql->fetch(\PDO::FETCH_LAZY)){
			 	$total += $result['total_amount'];
				$contents .= '<div style="float:right">' . $currencies->format_full($result['total_amount'], true, $result['currencies_code'], $result['currencies_value']) . '</div>';
				$contents .= '<div>';
	            $contents .= '<a href="' . html_href_link(FILENAME_DEFAULT, "module=phreebooks&amp;page=orders&amp;oID={$result['id']}&amp;jID=9&amp;action=edit", 'SSL') . '">';
				$contents .= $result['purchase_invoice_id'] . ' - ';
				$contents .= htmlspecialchars(gen_trim_string($result['bill_primary_name'], 20, true));
				$contents .= '</a></div>' . chr(10);
			}
	  	}
		if (!$this->params['num_rows'] && $sql->rowCount() != 0) {
		  	$contents .= '<div style="float:right">' . $currencies->format_full($total, true, $result['currencies_code'], $result['currencies_value']) . '</div>';
		  	$contents .= '<div><b>' . TEXT_TOTAL . '</b></div>' . chr(10);
		}
		return $this->build_div($contents, $control);
	}
}
?>
