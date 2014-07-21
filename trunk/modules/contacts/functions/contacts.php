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
//  Path: /modules/contacts/functions/contacts.php
//
// Draw address table elements
function draw_address_fields($entries, $add_type, $reset_button = false, $hide_list = false, $short = false) {
	$field = '';
	$method = substr($add_type, 1, 1);
//echo 'entries = '; print_r($entries); echo '<br>';
	if (!$hide_list && sizeof($entries->address_book[$method]) > 0) {
		$field .= '<tr><td><table class="ui-widget" style="border-collapse:collapse;width:100%;">';
		$field .= '<thead class="ui-widget-header">' . chr(10);
		$field .= '<tr>' . chr(10);
		$field .= '  <th>' . GEN_PRIMARY_NAME .   '</th>' . chr(10);
		$field .= '  <th>' . GEN_CONTACT .        '</th>' . chr(10);
		$field .= '  <th>' . GEN_ADDRESS1 .       '</th>' . chr(10);
		$field .= '  <th>' . GEN_CITY_TOWN .      '</th>' . chr(10);
		$field .= '  <th>' . GEN_STATE_PROVINCE . '</th>' . chr(10);
		$field .= '  <th>' . GEN_POSTAL_CODE .    '</th>' . chr(10);
		$field .= '  <th>' . GEN_COUNTRY .        '</th>' . chr(10);
		// add some special fields
		if ($method == 'p') $field .= '  <th>' . ACT_PAYMENT_REF . '</th>' . chr(10);
		$field .= '  <th align="center">' . TEXT_ACTION . '</th>' . chr(10);
		$field .= '</tr>' . chr(10) . chr(10);
		$field .= '</thead>' . chr(10) . chr(10);
		$field .= '<tbody class="ui-widget-content">' . chr(10);
		
		$odd = true;
		foreach ($entries->address_book[$method] as $address) {
			$field .= '<tr id="tr_add_'.$address->address_id.'" class="'.($odd?'odd':'even').'" style="cursor:pointer">';
			$field .= '  <td onclick="getAddress('.$address->address_id.', \''.$add_type.'\')">' . $address->primary_name . '</td>' . chr(10);
			$field .= '  <td onclick="getAddress('.$address->address_id.', \''.$add_type.'\')">' . $address->contact . '</td>' . chr(10);
			$field .= '  <td onclick="getAddress('.$address->address_id.', \''.$add_type.'\')">' . $address->address1 . '</td>' . chr(10);
			$field .= '  <td onclick="getAddress('.$address->address_id.', \''.$add_type.'\')">' . $address->city_town . '</td>' . chr(10);
			$field .= '  <td onclick="getAddress('.$address->address_id.', \''.$add_type.'\')">' . $address->state_province . '</td>' . chr(10);
			$field .= '  <td onclick="getAddress('.$address->address_id.', \''.$add_type.'\')">' . $address->postal_code . '</td>' . chr(10);
			// add special fields
			$field .= '  <td onclick="getAddress('.$address->address_id.', \''.$add_type.'\')">' . $address->country_code . '</td>' . chr(10);
			if ($method == 'p') $field .= '  <td onclick="getAddress('.$address->address_id.', \''.$add_type.'\')">' . ($address['hint'] ? $address['hint'] : '&nbsp;') . '</td>' . chr(10);
			$field .= '  <td align="center">';
			$field .= html_icon('actions/edit-find-replace.png', TEXT_EDIT, 'small', 'onclick="getAddress('.$address->address_id.', \''.$add_type.'\')"') . chr(10);
			$field .= '&nbsp;' . html_icon('emblems/emblem-unreadable.png', TEXT_DELETE, 'small', 'onclick="if (confirm(\'' . ACT_WARN_DELETE_ADDRESS . '\')) deleteAddress(' .$address->address_id . ');"') . chr(10);
			$field .= '  </td>' . chr(10);
			$field .= '</tr>' . chr(10);
			$odd = !$odd;
		}
		$field .= '</tbody>' . chr(10) . chr(10);
		$field .= '</table></td></tr>';
	}

    $field .= '<tr><td><table class="ui-widget" style="border-collapse:collapse;width:100%;">' . chr(10);
	if (!$short) {
		$field .= '<tr>';
		$field .= '  <td align="right">' . GEN_PRIMARY_NAME . '</td>' . chr(10);
		$field .= '  <td>' . html_input_field("address[$add_type][primary_name]", $entries->address[$add_type]['primary_name'], 'size="49" maxlength="48"', true) . '</td>' . chr(10);
		$field .= '  <td align="right">' . GEN_TELEPHONE1 . '</td>' . chr(10);
		$field .= '  <td>' . html_input_field("address[$add_type][telephone1]", $entries->address[$add_type]['telephone1'], 'size="21" maxlength="20"', ADDRESS_BOOK_TELEPHONE1_REQUIRED) . '</td>' . chr(10);
		$field .= '</tr>';
	}
	$field .= '<tr>';
	$field .= '  <td align="right">' . GEN_CONTACT . html_hidden_field("address[$add_type][address_id]", $entries->address[$add_type]['address_id']) . '</td>' . chr(10);
	$field .= '  <td>' . html_input_field("address[$add_type][contact]", $entries->address[$add_type]['contact'], 'size="33" maxlength="32"', ADDRESS_BOOK_CONTACT_REQUIRED) . '</td>' . chr(10);
	$field .= '  <td align="right">' . GEN_TELEPHONE2 . '</td>' . chr(10);
	$field .= '  <td>' . html_input_field("address[$add_type][telephone2]", $entries->address[$add_type]['telephone2'], 'size="21" maxlength="20"') . '</td>' . chr(10);
	$field .= '</tr>';

	$field .= '<tr>';
	$field .= '  <td align="right">' . GEN_ADDRESS1 . '</td>' . chr(10);
	$field .= '  <td>' . html_input_field("address[$add_type][address1]" , $entries->address[$add_type]['address1'], 'size="33" maxlength="32"', ADDRESS_BOOK_ADDRESS1_REQUIRED) . '</td>' . chr(10);
	$field .= '  <td align="right">' . GEN_FAX . '</td>' . chr(10);
	$field .= '  <td>' . html_input_field("address[$add_type][telephone3]", $entries->address[$add_type]['telephone3'], 'size="21" maxlength="20"') . '</td>' . chr(10);
	$field .= '</tr>';

	$field .= '<tr>';
	$field .= '  <td align="right">' . GEN_ADDRESS2 . '</td>' . chr(10);
	$field .= '  <td>' . html_input_field("address[$add_type][address2]", $entries->address[$add_type]['address2'], 'size="33" maxlength="32"', ADDRESS_BOOK_ADDRESS2_REQUIRED) . '</td>' . chr(10);
	$field .= '  <td align="right">' . GEN_TELEPHONE4 . '</td>' . chr(10);
	$field .= '  <td>' . html_input_field("address[$add_type][telephone4]", $entries->address[$add_type]['telephone4'], 'size="21" maxlength="20"') . '</td>' . chr(10);
	$field .= '</tr>';

	$field .= '<tr>';
	$field .= '  <td align="right">' . GEN_CITY_TOWN . '</td>' . chr(10);
	$field .= '  <td>' . html_input_field("address[$add_type][city_town]", $entries->address[$add_type]['city_town'], 'size="25" maxlength="24"', ADDRESS_BOOK_CITY_TOWN_REQUIRED) . '</td>' . chr(10);
	$field .= '  <td align="right">' . GEN_EMAIL . '</td>' . chr(10);
	$field .= '  <td>' . html_input_field("address[$add_type][email]", $entries->address[$add_type]['email'], 'size="51" maxlength="50"') . '</td>' . chr(10);
	$field .= '</tr>';

	$field .= '<tr>';
	$field .= '  <td align="right">' . GEN_STATE_PROVINCE . '</td>' . chr(10);
	$field .= '  <td>' . html_input_field("address[$add_type][state_province]", $entries->address[$add_type]['state_province'], 'size="25" maxlength="24"', ADDRESS_BOOK_STATE_PROVINCE_REQUIRED) . '</td>' . chr(10);
	$field .= '  <td align="right">' . GEN_WEBSITE . '</td>' . chr(10);
	$field .= '  <td>' . html_input_field("address[$add_type][website]", $entries->address[$add_type]['website'], 'size="51" maxlength="50"') . '</td>' . chr(10);
	$field .= '</tr>';

	$field .= '<tr>';
	$field .= '  <td align="right">' . GEN_POSTAL_CODE . '</td>' . chr(10);
	$field .= '  <td>' . html_input_field("address[$add_type][postal_code]", $entries->address[$add_type]['postal_code'], 'size="11" maxlength="10"', ADDRESS_BOOK_POSTAL_CODE_REQUIRED) . '</td>' . chr(10);
	$field .= '  <td align="right">' . GEN_COUNTRY . '</td>' . chr(10);
	$field .= '  <td>' . html_pull_down_menu("address[$add_type][country_code]", gen_get_countries(), $entries->address[$add_type]['country_code'] ? $entries->address[$add_type]['country_code'] : COMPANY_COUNTRY) . '</td>' . chr(10);
	$field .= '</tr>';

	if ($method <> 'm' || ($add_type == 'im' && substr($add_type, 0, 1) <> 'i')) {
	  $field .= '<tr>' . chr(10);
	  $field .= '  <td align="right">' . TEXT_NOTES . '</td>' . chr(10);
      $field .= '  <td colspan="3">' . html_textarea_field("address[$add_type][notes]", 80, 3, $entries->address[$add_type]['notes']) . chr(10);
	  if ($reset_button) $field .= html_icon('actions/view-refresh.png', TEXT_RESET, 'small', 'onclick="clearAddress(\''.$add_type.'\')"') . chr(10);
	  $field .= '  </td>' . chr(10);
	  $field .= '</tr>' . chr(10);
	}
	$field .= '</table></td></tr>' . chr(10) . chr(10);
	return $field;
}

function get_chart_data($operation, $data) {
  global $db, $currencies;
  $output = array(
    'type'  => 'pie',
    'width' => '600',
    'height'=> '400',
  );
  switch ($operation) {
  	case 'annual_sales':
  		$output['type']       = 'column';
  		$output['title']      = CONTACTS_CHART_SALES_TITLE;
  		$output['label_text'] = TEXT_DATE;
  		$output['value_text'] = TEXT_TOTAL;
  		$id = $data[0];
  		if (!$id) return false;
  		$dates = gen_get_dates(gen_specific_date(date(Y-m-d), 0, 0, -1));
  		$result = $db->Execute("SELECT month(post_date) as month, year(post_date) as year,
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