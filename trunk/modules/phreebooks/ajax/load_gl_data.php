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
//  Path: /modules/phreebooks/ajax/load_gl_data.php
//

/**************   Check user security   *****************************/
$xml = NULL;
$security_level = validate_ajax_user();
/**************   include page specific files   *********************/
/**************   page specific initialization  *********************/
$gl_acct = db_prepare_input($_GET['glAcct']);
$fy      = db_prepare_input($_GET['fy']);
$error   = false;
$result  = $db->Execute("select period, start_date, end_date from " . TABLE_ACCOUNTING_PERIODS . " 
	where fiscal_year = '" . ($fy - 1) . "' order by period");
if ($result->RecordCount() == 0) { // no earlier data found
  echo createXmlHeader() . xmlEntry('error', ERROR_NO_GL_ACCT_INFO) . createXmlFooter();
  die;
}
$periods = array();
while (!$result->EOF) {
  $periods[] = $result->fields['period'];
  $result->MoveNext();
}
$result = $db->Execute("select debit_amount - credit_amount as balance from " . TABLE_CHART_OF_ACCOUNTS_HISTORY . " 
	where account_id = '" . $gl_acct . "' and period in (" . implode(',', $periods) . ")");
while (!$result->EOF) {
  $xml .= "\t<items>\n";
  $xml .= "\t\t" . xmlEntry('balance', $currencies->format($result->fields['balance']));
  $xml .= "\t</items>\n";
  $result->MoveNext();
}

echo createXmlHeader() . $xml . createXmlFooter();
die;
?>