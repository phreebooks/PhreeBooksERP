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
//  Path: /modules/phreebooks/pages/popup_status/pre_process.php
//
$security_level = validate_user(0, true);
/**************  include page specific files    *********************/
gen_pull_language('contacts');
require_once(DIR_FS_WORKING . 'functions/phreebooks.php');
/**************   page specific initialization  *************************/
$id = (int)$_GET['id'];
/***************   Act on the action request   *************************/
// Load the customer status
$customer = $db->Execute("select c.type, c.inactive, c.special_terms, a.notes 
  from " . TABLE_CONTACTS . " c inner join " . TABLE_ADDRESS_BOOK . " a on c.id = a.ref_id 
  where c.id = " . $id . " and a.type like '%m'");
$notes    = $customer->fields['notes'] ? str_replace(chr(10), "<br />", $customer->fields['notes']) : '&nbsp;';
$type     = $customer->fields['type'] == 'v' ? 'AP' : 'AR';
$new_data = calculate_aging($id, $customer->fields['type'], $customer->fields['special_terms']);
// set the customer/vendor status in order of importance
if ($customer->fields['inactive']) {
  $inactive_flag = 'class="ui-state-error"';
  $status_text = TEXT_INACTIVE;
} elseif ($new_data['past_due'] > 0) {
  $inactive_flag = 'class="ui-state-highlight"';
  $status_text = ACT_HAS_PAST_DUE_AMOUNT;
} elseif ($new_data['total'] > $new_data['credit_limit']) {
  $inactive_flag = 'class="ui-state-highlight"';
  $status_text = ACT_OVER_CREDIT_LIMIT;
} else {
  $inactive_flag = 'class="ui-state-active"';
  $status_text = ACT_GOOD_STANDING;
}
/*****************   prepare to display templates  *************************/
$include_header   = false;
$include_footer   = false;
$include_template = 'template_main.php'; // include display template (required)
define('PAGE_TITLE', constant($type . '_CONTACT_STATUS'));
?>