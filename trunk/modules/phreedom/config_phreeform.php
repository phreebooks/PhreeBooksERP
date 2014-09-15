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
//  Path: /modules/phreedom/config_phreeform.php
//

$FormProcessing['rnd_dec']    = TEXT_ROUND_DECIMAL;
$FormProcessing['rnd_pre']    = TEXT_ROUND_PRECISE;
$FormProcessing['def_cur']    = TEXT_DEFAULT_CURRENCY;
$FormProcessing['null_dcur']  = TEXT_NULL_DEFAULT_CURRENCY;
$FormProcessing['posted_cur'] = TEXT_POSTED_CURRENCY;
$FormProcessing['null_pcur']  = TEXT_NULL_POSTED_CURRENCY;
$FormProcessing['rep_id']     = TEXT_USERNAME;
// Extra form processing operations
function pf_process_phreedom($strData, $Process) {
  global $currencies, $posted_currencies;
  switch ($Process) {
	case "rnd_dec":   if (!is_numeric($strData)) return $strData;
	                  return $currencies->format($strData);
	case "rnd_pre":   if (!is_numeric($strData)) return $strData;
	                  return $currencies->precise($strData);
	case "def_cur":   if (!is_numeric($strData)) return $strData;
	                  return $currencies->format_full($strData, true, DEFAULT_CURRENCY, 1);
	case "null_dcur": if (!is_numeric($strData)) return $strData;
	                  return (real)$strData == 0 ? '' : $currencies->format_full($strData, true, DEFAULT_CURRENCY, 1);
	case "posted_cur":if (!is_numeric($strData)) return $strData;
	                  return $currencies->format_full($strData, true, $posted_currencies['currencies_code'], $posted_currencies['currencies_value']);
	case "null_pcur": if (!is_numeric($strData)) return $strData;
	                  return (real)$strData == 0 ? '' : $currencies->format_full($strData, true, $posted_currencies['currencies_code'], $posted_currencies['currencies_value']);
	case "rep_id":    return pb_get_user_name($strData);
	default: // Do nothing
  }
  return $strData; // No Process recognized, return original value
}

function pb_get_user_name($id) {
  global $admin;
  if (!$id) return '';
  $result = $db->Execute("select display_name from " . TABLE_USERS . " where admin_id = " . (int)$id);
  return $result->RecordCount()==0 ? '' : $result->fields['display_name'];
}

?>