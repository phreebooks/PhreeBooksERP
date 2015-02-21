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
//  Path: /modules/contacts/functions/contacts.php
//

function get_chart_data($operation, $data) {
  global $admin;
  $output = array(
    'type'  => 'pie',
    'width' => '600',
    'height'=> '400',
  );
  switch ($operation) {
  	case 'annual_sales':
  		$output['type']       = 'column';
  		$output['title']      = TEXT_MONTHLY_SALES;
  		$output['label_text'] = TEXT_DATE;
  		$output['value_text'] = TEXT_TOTAL;
  		$id = $data[0];
  		if (!$id) return false;
  		$dates = gen_get_dates(gen_specific_date(date(Y-m-d), 0, 0, -1));
  		$result = $admin->DataBase->query("SELECT month(post_date) as month, year(post_date) as year,
          sum(total_amount) as total from ".TABLE_JOURNAL_MAIN."
  		  where bill_acct_id = $id and journal_id in (12,13) and post_date >= '".$dates['ThisYear'].'-'.$dates['ThisMonth']."-01'
  		  group by year, month limit 12");
  		for ($i=0; $i<12; $i++) {
  			if ($result->fields['year'] == $dates['ThisYear'] && $result->fields['month'] == $dates['ThisMonth']) {
  			  $value = $result->fields['total'];
  			  $result->MoveNext();
  			} else {
  			  $value = 0;
  			}
  			$output['data'][] = array(
  			  'label' => $dates['ThisYear'].'-'.$dates['ThisMonth'],
  			  'value' => $value,
  			);
  			$dates['ThisMonth']++;
  			if ($dates['ThisMonth'] == '13') {
  				$dates['ThisYear']++;
  				$dates['ThisMonth'] = '01';
  			}
  		}
  		break;
  	default:
  		return false;
  }
  return $output;
}

?>