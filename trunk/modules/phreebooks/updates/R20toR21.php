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
//  Path: /modules/phreebooks/updates/R19toR20.php
//

// This script updates Release 2.0 to Release 2.1, it is included as part of the updater script

// *************************** IMPORTANT UPDATE INFORMATION *********************************//

//********************************* END OF IMPORTANT ****************************************//

if (!db_field_exists(TABLE_DEPARTMENTS, 'description_short')) {
  $db->Execute("ALTER TABLE " . TABLE_DEPARTMENTS . " ADD description_short VARCHAR(30) NULL AFTER id");
  $db->Execute("UPDATE " . TABLE_DEPARTMENTS . " set description_short = id");
  $db->Execute("ALTER TABLE " . TABLE_DEPARTMENTS . " CHANGE id id INT(11) NOT NULL AUTO_INCREMENT");
  $result = $db->Execute("select id, description_short from " . TABLE_DEPARTMENTS);
  while(!$result->EOF) {
	$db->Execute("UPDATE " . TABLE_DEPARTMENTS . " set primary_dept_id = " . $result->fields['id'] . "
	    where primary_dept_id = '" . $result->fields['description_short'] . "'");
	$result->MoveNext();
  }
  $db->Execute("ALTER TABLE " . TABLE_DEPARTMENTS . " CHANGE primary_dept_id primary_dept_id INT(11) NOT NULL DEFAULT '0'");
  // fix the reports that may have department security set
  $result = $db->Execute("select id, description_short from " . TABLE_DEPARTMENTS);
  $depts = array();
  while (!$result->EOF) {
    $depts[$result->fields['description_short']] = $result->fields['id'];
    $result->MoveNext();
  }
  $result = $db->Execute("select id, params from " . TABLE_REPORT_FIELDS . " where entrytype = 'security'");
  while (!$result->EOF) {
    if ($result->fields['params']) {
	  $settings = explode(';', $result->fields['params']);
	  if (is_array($settings)) {
	    $rebuild = array();
	    foreach ($settings as $value) {
		  if (substr($value, 0, 1) == 'd') {
		    $value = substr($value, 2);
			if ($value) {
			  $output = array();
			  $temp = explode(':', $value);
			  foreach ($temp as $dept_id) $output[] = (isset($depts[$dept_id])) ? $depts[$dept_id] : $dept_id;
			} elseif ($value == '') {
			  $output = array('');
			} else {
			  $output = array('0');
			}
			$rebuild[] = 'd:' . implode(':', $output);
		  } else {
		    $rebuild[] = $value;
		  }
		}
		$db->Execute("update " . TABLE_REPORT_FIELDS . " set params = '" . implode(';', $rebuild) . "'
		  where id = " . $result->fields['id']);
	  }
	}
    $result->MoveNext();
  }
}

// some other cleanup
$db->Execute("ALTER TABLE " . TABLE_SHIPPING_LOG . " CHANGE ship_date    ship_date    DATETIME    NOT NULL DEFAULT '0000-00-00 00:00:00'");
$db->Execute("ALTER TABLE " . TABLE_SHIPPING_LOG . " CHANGE deliver_date deliver_date DATETIME    NOT NULL DEFAULT '0000-00-00 00:00:00'");
$db->Execute("ALTER TABLE " . TABLE_SHIPPING_LOG . " CHANGE actual_date  actual_date  DATETIME    NOT NULL DEFAULT '0000-00-00 00:00:00'");
$db->Execute("ALTER TABLE " . TABLE_SHIPPING_LOG . " CHANGE ref_id       ref_id       VARCHAR(16) NOT NULL DEFAULT '0'");
$db->Execute("ALTER TABLE " . TABLE_JOURNAL_ITEM . " CHANGE reconciled   reconciled   SMALLINT(4) NOT NULL DEFAULT '0'");

if (!defined('AR_DEF_DEP_LIAB_ACCT')) {
  $db->Execute("INSERT INTO " .  TABLE_CONFIGURATION . "
    ( configuration_title , configuration_key , configuration_value , configuration_description , configuration_group_id , sort_order , last_modified , date_added , use_function , set_function )
    VALUES ( 'CD_02_07_TITLE', 'AR_DEF_DEP_LIAB_ACCT', '', 'CD_02_07_DESC', '2', '7', NULL , now(), NULL , 'cfg_pull_down_gl_acct_list(' );");
  $db->Execute("INSERT INTO " .  TABLE_CONFIGURATION . "
    ( configuration_title , configuration_key , configuration_value , configuration_description , configuration_group_id , sort_order , last_modified , date_added , use_function , set_function )
    VALUES ( 'CD_03_07_TITLE', 'AP_DEF_DEP_LIAB_ACCT', '', 'CD_03_07_DESC', '3', '7', NULL , now(), NULL , 'cfg_pull_down_gl_acct_list(' );");
}

if (!db_field_exists(TABLE_JOURNAL_MAIN, 'discount')) $db->Execute("ALTER TABLE " . TABLE_JOURNAL_MAIN . " ADD discount DOUBLE NOT NULL DEFAULT '0' AFTER freight");
$result = $db->Execute("select ref_id, debit_amount from " . TABLE_JOURNAL_ITEM . " where gl_type = 'dsc'");
while(!$result->EOF) {
  $db->Execute("update " . TABLE_JOURNAL_MAIN . " set discount = " . $result->fields['debit_amount'] . " where id = " . $result->fields['ref_id']);
  $result->MoveNext();
}

// set reconciled for past closed journal_mains
$result = $db->Execute("select * from " . TABLE_RECONCILIATION);
while (!$result->EOF) {
  $period        = $result->fields['period'];
  $gl_account    = $result->fields['gl_account'];
  $cleared_items = unserialize($result->fields['cleared_items']);
  if (sizeof($cleared_items) > 0) {
    $one_period = $db->Execute("select id from " . TABLE_JOURNAL_MAIN . "
      where closed = '1' and journal_id in (2, 18, 20) and id in (" . implode(',', $cleared_items) . ")");
	$update_ids = array();
	while (!$one_period->EOF) {
	  $update_ids[] = $one_period->fields['id'];
	  $one_period->MoveNext();
	}
	$db->Execute("update " . TABLE_JOURNAL_ITEM . " set reconciled = '" . $period . "'
	  where gl_account = '" . $gl_account . "' and ref_id in (" . implode(',', $update_ids) . ")");
  }
  $result->MoveNext();
}

?>