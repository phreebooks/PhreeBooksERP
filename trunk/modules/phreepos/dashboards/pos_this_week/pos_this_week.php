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
//  Path: /modules/phreepos/dashboards/pos_this_week/pos_this_week.php
//

class pos_this_week extends ctl_panel {
	public $dashboard_id 		= 'pos_this_week';
	public $description	 		= CP_POS_THIS_WEEK_DESCRIPTION;
	public $security_id  		= SECURITY_ID_POS_MGR;
	public $title		 		= CP_POS_THIS_WEEK_TITLE;
	public $version      		= 3.5;

	function Output($params) {
		global $db, $currencies;
		$contents = '';
		$control  = '';
		for($i=0; $i<=7; $i++){
			if ('Mon'== strftime("%a", time()-($i * 24 * 60 * 60)) ){
				$a = $i;
			} 
		}
		// Build content box
		$total = 0;
		$sql = "select SUM(total_amount) as day_total, currencies_code, currencies_value, post_date 
		  from " . TABLE_JOURNAL_MAIN . " 
		  where journal_id = 19 and post_date >= '" . date('Y-m-d', time()-($a * 24 * 60 * 60)) . "' GROUP BY post_date ORDER BY post_date";
		$result = $db->Execute($sql);
		if ($result->RecordCount() < 1) {
			$contents = ACT_NO_RESULTS;
		} else {
			$week = array();
		  	while (!$result->EOF) {
			  	$total += $result->fields['day_total'];
				$contents .= '<div style="float:right">' . $currencies->format_full($result->fields['day_total'], true, $result->fields['currencies_code'], $result->fields['currencies_value']) . '</div>';
				$contents .= '<div>';
				$contents .= gen_locale_date($result->fields['post_date']) ;
				$contents .= '</a></div>' . chr(10);
				$result->MoveNext();
		  	}
		}
		if (!$params['num_rows'] && $result->RecordCount() > 0) {
		  	$contents .= '<div style="float:right"><b>' . $currencies->format_full($total, true, $result->fields['currencies_code'], $result->fields['currencies_value']) . '</b></div>';
		  	$contents .= '<div><b>' . TEXT_TOTAL . '</b></div>' . chr(10);
		}
		return $this->build_div('', $contents, $control);
	}

}
?>