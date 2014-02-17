<?php
// +-----------------------------------------------------------------+
// |                   PhreeBooks Open Source ERP                    |
// +-----------------------------------------------------------------+
// | Copyright(c) 2008-2013 PhreeSoft, LLC (www.PhreeSoft.com)       |
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
//  Path: /includes/common_functions.php
//

// General functions used across modules. Divided into the following sections:
// Section 1. General Functions
// Section 2. Database Functions
// Section 3. HTML Functions
// Section 4. localization Functions
// Section 5. Extra Fields Functions
// Section 6. Validation Functions
// Section 7. Password Functions
// Section 8. Conversion Functions
// Section 9. Error Handling Functions

/**************************************************************************************************************/
// Section 1. General Functions
/**************************************************************************************************************/
// Redirect to another page or site
  function gen_redirect($url) {
    global $messageStack;
	// clean up URL before executing it
    while (strstr($url, '&&'))    $url = str_replace('&&', '&', $url);
    // header locates should not have the &amp; in the address it breaks things
    while (strstr($url, '&amp;')) $url = str_replace('&amp;', '&', $url);
    header('Location: ' . $url);
    exit;
  }

  function gen_pull_language($page, $file = 'language') {
  	if (!is_dir(DIR_FS_MODULES . $page)) return;
  	if       (file_exists(DIR_FS_MODULES . "$page/custom/language/".$_SESSION['language']."/$file.php")) {
      include_once       (DIR_FS_MODULES . "$page/custom/language/".$_SESSION['language']."/$file.php");
	} elseif (file_exists(DIR_FS_MODULES . "$page/custom/language/en_us/$file.php")) {
      include_once       (DIR_FS_MODULES . "$page/custom/language/en_us/$file.php");
	}
    if       (file_exists(DIR_FS_MODULES . "$page/language/".$_SESSION['language']."/$file.php")) {
      include_once       (DIR_FS_MODULES . "$page/language/".$_SESSION['language']."/$file.php");
	} elseif (file_exists(DIR_FS_MODULES . "$page/language/en_us/$file.php")) {
      include_once       (DIR_FS_MODULES . "$page/language/en_us/$file.php");
	}
  }

  function load_method_language($path, $file = '') {
  	if (!is_dir($path . $file)) return;
    if (file_exists($path . "$file/language/".$_SESSION['language'].'/language.php')) {
      include_once ($path . "$file/language/".$_SESSION['language'].'/language.php');
    } elseif (file_exists($path . "$file/language/en_us/language.php")) {
      include_once       ($path . "$file/language/en_us/language.php");
    }
  }

  function load_all_methods($module, $active_only = true, $inc_select = false) {
    $choices     = array();
	if (!$module) return $choices;
    $method_dir  = DIR_FS_MODULES . "$module/methods/";
	if ($inc_select) $choices[] = array('id' => '0', 'text' => GEN_HEADING_PLEASE_SELECT);
    if ($methods = @scandir($method_dir)) foreach ($methods as $method) {
	  if ($method == '.' || $method == '..' || !is_dir($method_dir . $method)) continue;
	  if ($active_only && !defined('MODULE_' . strtoupper($module) . '_' . strtoupper($method) . '_STATUS')) continue;
	  load_method_language($method_dir, $method);
	  include_once($method_dir . $method . '/' . $method . '.php');
	  $choices[constant('MODULE_' . strtoupper($module) . '_' . strtoupper($method) . '_SORT_ORDER')] = array(
	    'id'   => $method, 
		'text' => constant('MODULE_' . strtoupper($module) . '_' . strtoupper($method) . '_TEXT_TITLE'),
	  );
    }
    ksort($choices);
    return $choices;
  }

  function load_specific_method($module, $method) {
    $method_dir  = DIR_FS_MODULES . $module . '/methods/';
    load_method_language($method_dir, $method);
    if (file_exists($method_dir . $method . '/' . $method . '.php')) {
	  require_once ($method_dir . $method . '/' . $method . '.php');
    }
	return $method;
  }

  function write_configure($constant, $value = '') {
    global $db;
	if (!$constant) return false;
	$result = $db->Execute("select configuration_value from " . TABLE_CONFIGURATION . " where configuration_key = '" . $constant . "'");
	if ($result->RecordCount() == 0) {
	  $sql_array = array('configuration_key'  => $constant, 'configuration_value'=> $value);
	  db_perform(TABLE_CONFIGURATION,  $sql_array);
	  define($constant, $value);
	} elseif ($result->fields['configuration_value'] <> $value) {
	  db_perform(TABLE_CONFIGURATION, array('configuration_value'=>$value), 'update', "configuration_key = '".$constant."'");
	}
	return true;
  }

  function remove_configure($constant){
    global $db;
	if (!$constant) return false;
	$db->Execute("delete from " . TABLE_CONFIGURATION . " where configuration_key = '" . $constant . "'");
	return true;
  }

  function gen_not_null($value) {
    return (!is_null($value) || strlen(trim($value)) > 0) ? true : false;
  }

  function strip_alphanumeric($value) {
    return preg_replace("/[^a-zA-Z0-9\s]/", "", $value);
  }

  function remove_special_chars($value) {
    $value = str_replace('&', '-', $value);
    return $value;
  }

  function gen_js_encode($str) {
  	$str = str_replace('"', '\"', $str);
	$str = str_replace(chr(10), '\n', $str);
	$str = str_replace(chr(13), '', $str);
	return $str;
  }

  function gen_trim_string($string, $length = 20, $add_dots = false) {
    return mb_strimwidth($string, 0, $length, $add_dots ? '...' : '');
  }

  function gen_array_key_merge($arr1, $arr2) {
  	if (!is_array($arr1)) $arr1 = array();
    if (is_array($arr2) && sizeof($arr2) > 0) {
	  foreach($arr2 as $key => $value) if (!array_key_exists($key, $arr1)) $arr1[$key] = $value;
	}
    return $arr1;
  }
 
  function gen_null_pull_down() {
    $null_array = array('id' => '0', 'text' => TEXT_ENTER_NEW);
    return $null_array;
  }

  function gen_build_pull_down($keyed_array) {
	$values = array();
	if (is_array($keyed_array)) {
	  foreach($keyed_array as $key => $value) {
		$values[] = array('id' => $key, 'text' => $value);
	  }
	}
	return $values;
  }

  function gen_get_pull_down($db_name, $first_none = false, $show_id = '0', $id = 'id', $description = 'description') {
    global $db;
    $type_format_values = $db->Execute("select " . $id . " as id, " . $description . " as description
      from " . $db_name . " order by '" . $id . "'");
    $type_format_array = array();
    if ($first_none) $type_format_array[] = array('id' => '', 'text' => TEXT_NONE);
    while (!$type_format_values->EOF) {
	  switch ($show_id) {
	    case '1': // description only
		  $text_value = $type_format_values->fields['description'];
		  break;
		case '2': // Both id and description
		  $text_value = $type_format_values->fields['id'] . ' : ' . $type_format_values->fields['description'];
		  break;
		case '0': // id only
		default:
	  	  $text_value = $type_format_values->fields['id'];
	  }
      $type_format_array[] = array(
	    'id'   => $type_format_values->fields['id'],
        'text' => $text_value,
	  );
      $type_format_values->MoveNext();
    }
    return $type_format_array;
  }

  function gen_calculate_period($post_date, $hide_error = false) {
	global $db, $messageStack;
	$post_time_stamp = strtotime($post_date);
	$period_start_time_stamp = strtotime(CURRENT_ACCOUNTING_PERIOD_START);
	$period_end_time_stamp = strtotime(CURRENT_ACCOUNTING_PERIOD_END);

	if (($post_time_stamp >= $period_start_time_stamp) && ($post_time_stamp <= $period_end_time_stamp)) {
		return CURRENT_ACCOUNTING_PERIOD;
	} else {
		$result = $db->Execute("select period from " . TABLE_ACCOUNTING_PERIODS . " 
			where start_date <= '" . $post_date . "' and end_date >= '" . $post_date . "'");
		if ($result->RecordCount() <> 1) { // post_date is out of range of defined accounting periods
			if (!$hide_error) $messageStack->add(ERROR_MSG_POST_DATE_NOT_IN_FISCAL_YEAR,'error');
			return false;
		}
		if (!$hide_error) $messageStack->add(ERROR_MSG_BAD_POST_DATE,'caution');
		return $result->fields['period'];
	}
  }

  function gen_get_period_pull_down($include_all = true) {
    global $db;
    $period_values = $db->Execute("select period, start_date, end_date from " . TABLE_ACCOUNTING_PERIODS . " order by period");
    $period_array = array();
    if ($include_all) $period_array[] = array('id' => 'all', 'text' => TEXT_ALL);
    while (!$period_values->EOF) {
	  $text_value = TEXT_PERIOD . ' ' . $period_values->fields['period'] . ' : ' . gen_locale_date($period_values->fields['start_date']) . ' - ' . gen_locale_date($period_values->fields['end_date']);
      $period_array[] = array('id' => $period_values->fields['period'], 'text' => $text_value);
      $period_values->MoveNext();
    }
    return $period_array;
  }

  function gen_coa_pull_down($show_id = SHOW_FULL_GL_NAMES, $first_none = true, $hide_inactive = true, $show_all = false, $restrict_types = false) {
    global $db;
	$params = array();
    $output = array();
	$sql    = "select id, description, account_type from " . TABLE_CHART_OF_ACCOUNTS;
	if ($hide_inactive)  $params[] = "account_inactive = '0'";
	if (!$show_all)      $params[] = "heading_only = '0'";
	if ($restrict_types) $params[] = "account_type in (" . implode(',', $restrict_types) . ")";
	$sql .= (sizeof($params) == 0) ? '' : ' where ' . implode(' and ', $params); 
	$sql .= " order by id";
    $result = $db->Execute($sql);
    if ($first_none) $output[] = array('id' => '', 'text' => GEN_HEADING_PLEASE_SELECT);
    while (!$result->EOF) {
	  switch ($show_id) {
		default:
		case '0': $text_value = $result->fields['id']; break;
	    case '1': $text_value = $result->fields['description']; break;
		case '2': $text_value = $result->fields['id'].' : '.$result->fields['description']; break;
	  }
      $output[] = array('id' => $result->fields['id'], 'text' => $text_value, 'type' => $result->fields['account_type']);
      $result->MoveNext();
    }
    return $output;
  }

  function gen_get_type_description($db_name, $id, $full = true) {
    global $db;
    $type_name = $db->Execute("select description from " . $db_name . " where id = '" . $id . "'");
    if ($type_name->RecordCount() < 1) {
      return $id;
    } else {
	  if ($full) {
		return $id . ':' . $type_name->fields['description'];
	  } else {
		return $type_name->fields['description'];
	  }
    }
  }

  function gen_get_contact_type($id) {
    global $db;
    $vendor_type = $db->Execute("select type from " . TABLE_CONTACTS . " where id = '" . $id . "'");
    return ($vendor_type->RecordCount() == 1) ? $vendor_type->fields['type'] : false;
  }

  function gen_get_contact_name($id) {
    global $db;
    $vendor_name = $db->Execute("select short_name from " . TABLE_CONTACTS . " where id = '" . $id . "'");
    if ($vendor_name->RecordCount() < 1) {
      return false;
    } else {
      return $vendor_name->fields['short_name'];
    }
  }

  function gen_get_contact_array_by_type($type = 'v') {
    global $db;
    $accounts = $db->Execute("select c.id, a.primary_name from " . TABLE_CONTACTS . " c left join " . TABLE_ADDRESS_BOOK . " a on c.id = a.ref_id 
	  where c.inactive <> '1' and a.type='" . $type . "m' order by a.primary_name");
    $accounts_array = array();
    $accounts_array[] = array('id' => '', 'text' => TEXT_NONE);
    while (!$accounts->EOF) {
      $accounts_array[] = array(
	    'id'   => $accounts->fields['id'],
		'text' => $accounts->fields['primary_name'],
	  );
      $accounts->MoveNext();
    }
    return $accounts_array;
  }

  function gen_get_rep_ids($type = 'c') {
	global $db;
	// map the type to the employee types
	switch ($type) {
	  default:
	  case 'c': $emp_type = 's'; break;
	  case 'v': $emp_type = 'b'; break;
	}
    $result_array = array();
    $result_array[] = array('id' => '0', 'text' => TEXT_NONE);
	$result = $db->Execute("select id, contact_first, contact_last, gl_type_account from " . TABLE_CONTACTS . " where type = 'e' and inactive <> '1'");
	while(!$result->EOF) {
	  if (strpos($result->fields['gl_type_account'], $emp_type) !== false) {
 	    $result_array[] = array('id' => $result->fields['id'], 'text' => $result->fields['contact_first'] . ' ' . $result->fields['contact_last']);
	  }
	  $result->MoveNext();
	}
    return $result_array;
  }

  function gen_get_store_ids() {
	global $db;
    $result_array = array();
	$result = $db->Execute("select id, short_name from " . TABLE_CONTACTS . " where type = 'b'");
	if (($_SESSION['admin_prefs']['restrict_store'] && $_SESSION['admin_prefs']['def_store_id'] == 0)
	  || !$_SESSION['admin_prefs']['restrict_store']) {
        $result_array[0] = array('id' => '0', 'text' => COMPANY_ID); // main branch id
	}
	while(!$result->EOF) {
	  if (($_SESSION['admin_prefs']['restrict_store'] && $_SESSION['admin_prefs']['def_store_id'] == $result->fields['id'])
	    || !$_SESSION['admin_prefs']['restrict_store']) {
 	      $result_array[$result->fields['id']] = array('id' => $result->fields['id'], 'text' => $result->fields['short_name']);
	  }
	  $result->MoveNext();
	}
    return $result_array;
  }

  function inv_calculate_tax_drop_down($type = 'c',$contactForm = true) {
    global $db;
    $tax_rates = $db->Execute("select tax_rate_id, description_short 
		from " . TABLE_TAX_RATES . " where type = '" . $type . "'");
    $tax_rate_drop_down = array();
    if ($contactForm)$tax_rate_drop_down[] = array('id' => '-1', 'text' => TEXT_PRODUCT_DEFAULT);
    $tax_rate_drop_down[] = array('id' => '0', 'text' => TEXT_NONE);
	while (!$tax_rates->EOF) {
	  $tax_rate_drop_down[] = array(
	    'id'   => $tax_rates->fields['tax_rate_id'],
		'text' => $tax_rates->fields['description_short'],
	  );
	  $tax_rates->MoveNext();
	}
	return $tax_rate_drop_down;
  }

  function gen_terms_to_language($terms_encoded, $short = true, $type = 'AR') {
	gen_pull_language('contacts'); // required for calculating terms
	$type   = strtoupper($type);
	$terms  = explode(':', $terms_encoded);
	$result = array();
	switch ($terms[0]) {
	  default:
	  case '0': // Default terms
		if ((int)constant($type . '_PREPAYMENT_DISCOUNT_PERCENT') <> 0) {
		  $result['long']  = ACT_DISCOUNT . constant($type . '_PREPAYMENT_DISCOUNT_PERCENT') . ACT_EARLY_DISCOUNT . ACT_DUE_IN . constant($type . '_PREPAYMENT_DISCOUNT_DAYS') . ACT_TERMS_EARLY_DAYS;
		  $result['short'] = constant($type . '_PREPAYMENT_DISCOUNT_PERCENT') . ACT_EARLY_DISCOUNT_SHORT . constant($type . '_PREPAYMENT_DISCOUNT_DAYS') . ', ';
		}
		$result['long']  .= ACT_TERMS_NET . constant($type . '_NUM_DAYS_DUE') . ACT_TERMS_STANDARD_DAYS;
		$result['short'] .= ACT_TERMS_NET . constant($type . '_NUM_DAYS_DUE');
		break;
	  case '1': // Cash on Delivery (COD)
		$result['long']  = ACT_COD_LONG;
		$result['short'] = ACT_COD_SHORT;
		break;
	  case '2': // Prepaid
		$result['long']  = ACT_PREPAID;
		$result['short'] = ACT_PREPAID;
		break;
	  case '3': // Special terms
		if ($terms[1] <> 0) {
		  $result['long']  = ACT_DISCOUNT . $terms[1] . ACT_EARLY_DISCOUNT . ACT_DUE_IN . $terms[2] . ACT_TERMS_EARLY_DAYS;
		  $result['short'] = $terms[1] . ACT_EARLY_DISCOUNT_SHORT . $terms[2] . ', ';
		}
		$result['long']  .= ACT_TERMS_NET . $terms[3] . ACT_TERMS_STANDARD_DAYS;
		$result['short'] .=  ACT_TERMS_NET . $terms[3];
		break;
	  case '4': // Due on day of next month
		if ($terms[1] <> 0) {
		  $result['long']  = ACT_DISCOUNT . $terms[1] . ACT_EARLY_DISCOUNT . ACT_DUE_IN . $terms[2] . ACT_TERMS_EARLY_DAYS;
		  $result['short'] = $terms[1] . ACT_EARLY_DISCOUNT_SHORT . $terms[2] . ', ';
		}
		$result['long']  .= ACT_DUE_ON . $terms[3];
		$result['short'] .=  ACT_DUE_ON . $terms[3];
		break;
	  case '5': // Due at end of month
		if ($terms[1] <> 0) {
		} else {
		  $result['long']  = ACT_DISCOUNT . $terms[1] . ACT_EARLY_DISCOUNT . ACT_DUE_IN . $terms[2] . ACT_TERMS_EARLY_DAYS;
		  $result['short'] = $terms[1] . ACT_EARLY_DISCOUNT_SHORT . $terms[2] . ', ';
		}
		$result['long']  .= ACT_END_OF_MONTH;
		$result['short'] .=  ACT_END_OF_MONTH;
	}
	if ($short) return $result['short']; 
	return $result['long'];
  }

  function get_price_sheet_data($type = 'c') {
    global $db;
    $sql = "select distinct sheet_name, default_sheet from " . TABLE_PRICE_SHEETS . " 
		where inactive = '0' and type = '" . $type . "' order by sheet_name";
    $result = $db->Execute($sql);
    $sheets = array();
	$default = '';
    $sheets[] = array('id' => '', 'text' => TEXT_NONE);
    while (!$result->EOF) {
	  if ($result->fields['default_sheet']) $default = $result->fields['sheet_name'];
      $sheets[] = array('id' => $result->fields['sheet_name'], 'text' => $result->fields['sheet_name']);
      $result->MoveNext();
    }
    return $sheets;
  }

  function gen_build_company_arrays() {
  	$acct_array = array();
	$acct_array['fields'] = array('primary_name', 'contact', 'address1', 'address2', 'city_town', 'state_province', 'postal_code', 'country_code', 'telephone1', 'email');
	$acct_array['company'] = array(
	  gen_js_encode(COMPANY_NAME),
	  gen_js_encode(AP_CONTACT_NAME),
	  gen_js_encode(COMPANY_ADDRESS1),
	  gen_js_encode(COMPANY_ADDRESS2),
	  gen_js_encode(COMPANY_CITY_TOWN),
	  gen_js_encode(COMPANY_ZONE),
	  gen_js_encode(COMPANY_POSTAL_CODE), 
	  gen_js_encode(COMPANY_COUNTRY), 
	  gen_js_encode(COMPANY_TELEPHONE1), 
	  gen_js_encode(COMPANY_EMAIL),
	);
	$acct_array['text'] = array();
	foreach ($acct_array['fields'] as $value) $acct_array['text'][] = constant('GEN_' . strtoupper($value));
	return $acct_array;
  }

  function gen_validate_sku($sku) {
  	global $db;
	$result = $db->Execute("select id from " . TABLE_INVENTORY . " where sku = '" . $sku . "'");
	return ($result->RecordCount() <> 0) ? true : false;
  }

  function gen_parse_permissions($imploded_permissions) {
	$result = array();
	$temp = explode(',', $imploded_permissions);
	if (is_array($temp)) {
	  foreach ($temp as $imploded_entry) {
		$entry = explode(':', $imploded_entry);
		$result[$entry[0]] = $entry[1];
	  }
	}
	return $result;
  }

  function gen_add_audit_log($action, $ref_id = '', $amount = '') {
	global $db;
  	if ($action == '' || !isset($action)) die ('Error, call to audit log with no description');
  	$stats = (int)(1000 * (microtime(true) - PAGE_EXECUTION_START_TIME))."ms, ".$db->count_queries."q ".(int)($db->total_query_time * 1000)."ms";
	$fields = array(
	  'user_id'   => $_SESSION['admin_id'] ? $_SESSION['admin_id'] : '1',
	  'action'    => substr($action, 0, 64), // limit to field length
	  'ip_address'=> $_SERVER['REMOTE_ADDR'],
	  'stats'     => $stats,
	);
	if ($ref_id) $fields['reference_id'] = substr($ref_id, 0, 32); // limit to field length
	if ($amount) $fields['amount']       = (real)$amount;
	db_perform(TABLE_AUDIT_LOG, $fields, 'insert');
  }

  function gen_get_all_get_params($exclude_array = '') {
    global $_GET;
    if ($exclude_array == '') $exclude_array = array();
    $get_url = '';
    reset($_GET);
    $output = array();
    while (list($key, $value) = each($_GET)) {
      if (($key != session_name()) && ($key != 'error') && (!in_array($key, $exclude_array)) && $key != 'search_text') {
      	if (strlen($_REQUEST[$key]) > 0) $output[] = "$key=".$_REQUEST[$key];
      }
    }
    if(isset($_REQUEST['search_text']) && $_REQUEST['search_text'] != '' && in_array('search_text', $exclude_array) == false) $output[] = "search_text=".$_REQUEST['search_text'];
    return implode('&amp;', $output);
  }

  function js_get_all_get_params($exclude_array = '') { // for use within javascript language validator
    global $_GET;
    if ($exclude_array == '') $exclude_array = array();
    $get_url = '';
    reset($_GET);
    while (list($key, $value) = each($_GET)) {
      if (($key != session_name()) && ($key != 'error') && (!in_array($key, $exclude_array))) $get_url .= $key . '=' . $_REQUEST[$key] . '&';
    }
    return $get_url;
  }

function saveUploadZip($file_field, $dest_dir, $dest_name) {
	global $messageStack;
	if ($_FILES[$file_field]['error']) { // php error uploading file
		$messageStack->add(TEXT_IMP_ERMSG5 . $_FILES[$file_field]['error'], 'error');
	} elseif ($_FILES[$file_field]['size'] > 0) {
		require_once(DIR_FS_MODULES . 'phreedom/classes/backup.php');
		$backup              = new backup();
		$backup->source_dir  = $_FILES[$file_field]['tmp_name'];
		$backup->source_file = '';
		$backup->dest_dir    = $dest_dir;
		$backup->dest_file   = $dest_name;
		if (file_exists($dest_dir . $dest_name)) @unlink($dest_dir . $dest_name);
		$backup->make_zip('file', $_FILES[$file_field]['name']);
		@unlink($backup->source_dir);
	}
}

  function dircopy($src_dir, $dst_dir, $verbose = false, $use_cached_dir_trees = false) {    
	static $cached_src_dir;
	static $src_tree; 
	static $dst_tree;
	$num = 0;

	if (($slash = substr($src_dir, -1)) == "\\" || $slash == "/") $src_dir = substr($src_dir, 0, strlen($src_dir) - 1); 
	if (($slash = substr($dst_dir, -1)) == "\\" || $slash == "/") $dst_dir = substr($dst_dir, 0, strlen($dst_dir) - 1);  

	if (!$use_cached_dir_trees || !isset($src_tree) || $cached_src_dir != $src_dir) {
		$src_tree = get_dir_tree($src_dir);
		$cached_src_dir = $src_dir;
		$src_changed = true;  
	}
	if (!$use_cached_dir_trees || !isset($dst_tree) || $src_changed) $dst_tree = get_dir_tree($dst_dir);
	if (!is_dir($dst_dir)) mkdir($dst_dir, 0777, true);  

	foreach ($src_tree as $file => $src_mtime) {
		if (!isset($dst_tree[$file]) && $src_mtime === false) mkdir("$dst_dir/$file"); 
		elseif (!isset($dst_tree[$file]) && $src_mtime || isset($dst_tree[$file]) && $src_mtime > $dst_tree[$file]) {
			if (copy("$src_dir/$file", "$dst_dir/$file")) {
				if($verbose) echo "Copied '$src_dir/$file' to '$dst_dir/$file'<br />\r\n";
				touch("$dst_dir/$file", $src_mtime); 
				$num++; 
			} else echo "<font color='red'>File '$src_dir/$file' could not be copied!</font><br />\r\n";
		}        
	}

	return $num; 
  }

  function get_dir_tree($dir, $root = true)  {
	static $tree;
	static $base_dir_length; 
	if ($root) { 
	  $tree = array();  
	  $base_dir_length = strlen($dir) + 1;  
	}
	if (is_file($dir)) {
	  $tree[substr($dir, $base_dir_length)] = filemtime($dir); 
	} elseif (is_dir($dir) && $di = dir($dir)) { 
	  if (!$root) $tree[substr($dir, $base_dir_length)] = false;  
	  while (($file = $di->read()) !== false) 
		if ($file != "." && $file != "..")
		  get_dir_tree("$dir/$file", false);  
	  $di->close(); 
	}
	if ($root) return $tree;     
  }

/*************** Date Functions *******************************/
  // builds sql date string and description string based on passed criteria
  // function requires as input an associative array with two entries:
  // df = database fieldname for the sql date search
  // date_prefs = imploded (:) string with three entries
  //    entry 1 => date range specfication for switch statement
  //    entry 2 => start date value db format
  //    entry 3 => end date value db format
  function gen_build_sql_date($date_prefs, $df) {
  	global $db;
	$dates = gen_get_dates();
	$DateArray = explode(':', $date_prefs);
	$t = time();
	$ds = '0000-00-00';
	$de = '2199-00-00';
	switch ($DateArray[0]) {
	  default:
	  case "a": // All, skip the date addition to the where statement, all dates in db
		$d = '';
		$fildesc = '';
		break;
	  case "b": // Date Range
		$d = '';
		$fildesc = RW_RPT_DATERANGE;
		if ($DateArray[1] <> '') {
		  $ds = gen_db_date($DateArray[1]);
		  $d .= $df . " >= '" . $ds . "'";
		  $fildesc .= ' ' . TEXT_FROM . ' ' . $DateArray[1];
		}
		if ($DateArray[2] <> '') { // a value entered, check
		  if (strlen($d) > 0) $d .= ' and ';
		  $de = gen_specific_date(gen_db_date($DateArray[2]), 1);
		  $d .= $df . " < '" . $de . "'";
		  $fildesc .= ' ' . TEXT_TO . ' ' . $DateArray[2];
		}
		$fildesc .= '; ';			
		break;
	  case "c": // Today (specify range for datetime type fields to match for time parts)
		$ds = $dates['Today'];
		$de = gen_specific_date($dates['Today'], 1);
		$d  = $df . " >= '" . $ds . "' and " . $df . " < '" . $de . "'";
		$fildesc = RW_RPT_DATERANGE . ' = ' . gen_locale_date($dates['Today']) . '; ';
		break;
	  case "d": // This Week
		$ds = date('Y-m-d', mktime(0, 0, 0, $dates['ThisMonth'], date('j', $t) - date('w', $t), $dates['ThisYear']));
		$de = gen_specific_date(date('Y-m-d', mktime(0, 0, 0, $dates['ThisMonth'], date('j', $t) - date('w', $t)+6, $dates['ThisYear'])), 1);
		$d  = $df . " >= '" . $ds . "' and " . $df . " < '" . $de . "'";
		$fildesc = RW_RPT_DATERANGE . ' ' . TEXT_FROM . ' ' . gen_locale_date($ds) . ' ' . TEXT_TO . ' ' . gen_locale_date(gen_specific_date($de, -1)) . '; ';
		break;
	  case "e": // This Week to Date
		$ds = date('Y-m-d', mktime(0, 0, 0, $dates['ThisMonth'], date('j', $t)-date('w', $t), $dates['ThisYear']));
		$de = gen_specific_date($dates['Today'], 1);
		$d  = $df . " >= '" . $ds . "' and " . $df . " < '" . $de . "'";
		$fildesc = RW_RPT_DATERANGE . ' ' . TEXT_FROM . ' ' . gen_locale_date($ds) . ' ' . TEXT_TO . ' ' . gen_locale_date($dates['Today']) . '; ';
		break;
	  case "f": // This Month
		$ds = date('Y-m-d', mktime(0, 0, 0, $dates['ThisMonth'], 1, $dates['ThisYear']));
		$de = gen_specific_date(date('Y-m-d', mktime(0, 0, 0, $dates['ThisMonth'], $dates['TotalDays'], $dates['ThisYear'])), 1);
		$d  = $df . " >= '" . $ds . "' and " . $df . " < '" . $de . "'";
		$fildesc = RW_RPT_DATERANGE . ' ' . TEXT_FROM . ' ' . gen_locale_date($ds) . ' ' . TEXT_TO . ' ' . gen_locale_date(gen_specific_date($de, -1)) . '; ';
		break;
	  case "g": // This Month to Date
		$ds = date('Y-m-d', mktime(0, 0, 0, $dates['ThisMonth'], 1, $dates['ThisYear']));
		$de = gen_specific_date($dates['Today'], 1);
		$d  = $df . " >= '" . $ds . "' and " . $df . " < '" . $de . "'";
		$fildesc = RW_RPT_DATERANGE . ' ' . TEXT_FROM . ' ' . gen_locale_date($ds) . ' ' . TEXT_TO . ' ' . gen_locale_date($dates['Today']) . '; ';
		break;
	  case "h": // This Quarter
		$QtrStrt = CURRENT_ACCOUNTING_PERIOD - ((CURRENT_ACCOUNTING_PERIOD - 1) % 3);
		$temp = gen_calculate_fiscal_dates($QtrStrt);
		$ds = $temp['start_date'];
		$temp = gen_calculate_fiscal_dates($QtrStrt + 2);
		$de = gen_specific_date($temp['end_date'], 1);
		$d  = $df . " >= '" . $ds . "' and " . $df . " < '" . $de . "'";
		$fildesc = RW_RPT_DATERANGE . ' ' . TEXT_FROM . ' ' . gen_locale_date($ds) . ' ' . TEXT_TO . ' ' . gen_locale_date($temp['end_date']) . '; ';
		break;
	  case "i": // Quarter to Date
		$QtrStrt = CURRENT_ACCOUNTING_PERIOD - ((CURRENT_ACCOUNTING_PERIOD - 1) % 3);
		$temp = gen_calculate_fiscal_dates($QtrStrt);
		$ds = $temp['start_date'];
		$de = gen_specific_date($dates['Today'], 1);
		$d  = $df . " >= '" . $ds . "' and " . $df . " < '" . $de . "'";
		$fildesc = RW_RPT_DATERANGE . ' ' . TEXT_FROM . ' ' . gen_locale_date($ds) . ' ' . TEXT_TO . ' ' . gen_locale_date($dates['Today']) . '; ';
		break;
	  case "j": // This Year
		$YrStrt = CURRENT_ACCOUNTING_PERIOD - ((CURRENT_ACCOUNTING_PERIOD - 1) % 12);
		$temp = gen_calculate_fiscal_dates($YrStrt);
		$ds = $temp['start_date'];
		$temp = gen_calculate_fiscal_dates($YrStrt + 11);
		$de = gen_specific_date($temp['end_date'], 1);
		$d  = $df . " >= '" . $ds . "' and " . $df . " < '" . $de . "'";
		$fildesc = RW_RPT_DATERANGE . ' ' . TEXT_FROM . ' ' . gen_locale_date($ds) . ' ' . TEXT_TO . ' ' . gen_locale_date($temp['end_date']) . '; ';
		break;
	  case "k": // Year to Date
		$YrStrt = CURRENT_ACCOUNTING_PERIOD - ((CURRENT_ACCOUNTING_PERIOD - 1) % 12);
		$temp = gen_calculate_fiscal_dates($YrStrt);
		$ds = $temp['start_date'];
		$de = gen_specific_date($dates['Today'], 1);
		$d  = $df . " >= '" . $ds . "' and " . $df . " < '" . $de . "'";
		$fildesc = RW_RPT_DATERANGE . ' ' . TEXT_FROM . ' ' . gen_locale_date($ds) . ' ' . TEXT_TO . ' ' . gen_locale_date($dates['Today']) . '; ';
		break;
	  case "l": // This Period
		$ds = CURRENT_ACCOUNTING_PERIOD_START;
		$de = gen_specific_date(CURRENT_ACCOUNTING_PERIOD_END, 1);
		$d  = $df . " >= '" . $ds . "' and " . $df . " < '" . $de . "'";
		$fildesc = TEXT_PERIOD . ' ' . CURRENT_ACCOUNTING_PERIOD . ' (' . gen_locale_date(CURRENT_ACCOUNTING_PERIOD_START) . ' ' . TEXT_TO . ' ' . gen_locale_date(CURRENT_ACCOUNTING_PERIOD_END) . '); ';
		break;
	  case "z": // date by period
		$temp = gen_calculate_fiscal_dates($DateArray[1]);
		$ds = $temp['start_date'];
		$de = $temp['end_date'];
		$d  = 'period = ' . $DateArray[1];
		$fildesc = TEXT_PERIOD . ' ' . $DateArray[1] . ' (' . gen_locale_date($ds) . ' ' . TEXT_TO . ' ' . gen_locale_date($de) . '); ';
		break;
	}
	$dates = array(
	  'sql'         => $d, 
	  'description' => $fildesc,
	  'start_date'  => $ds,
	  'end_date'    => $de,
	);
	return $dates;
  }

function gen_db_date($raw_date = '', $separator = '/') { 
  global $messageStack;
  if (!$raw_date) return '';
  // handles periods (.), dashes (-), and slashes (/) as date separators
  $error = false;
  $second_separator = $separator;
  if (strpos(DATE_FORMAT, '.') !== false) $separator = '.';
  if (strpos(DATE_FORMAT, '-') !== false) $separator = '-';
  $date_vals = explode($separator, DATE_FORMAT);
  if (strpos($raw_date, '.') !== false) $second_separator = '.';
  if (strpos($raw_date, '-') !== false) $second_separator = '-';
  $parts     = explode($second_separator, $raw_date);
  foreach ($date_vals as $key => $position) {
    switch ($position) {
      case 'Y': $year  = substr('20' . $parts[$key], -4, 4); break;
      case 'm': $month = substr('0'  . $parts[$key], -2, 2); break;
      case 'd': $day   = substr('0'  . $parts[$key], -2, 2); break;
    }
  }
  if ($month < 1    || $month > 12)   $error = true;
  if ($day   < 1    || $day   > 31)   $error = true;
  if ($year  < 1900 || $year  > 2099) $error = true;
  if ($error) {
    $messageStack->add(sprintf(GEN_CALENDAR_FORMAT_ERROR, $raw_date, DATE_FORMAT, DATE_FORMAT_CALENDAR), 'error');
    return date('Y-m-d');
  }
  return $year . '-' . $month . '-' . $day;
}

  function gen_locale_date($raw_date, $long = false) { // from db to display format
    if ($raw_date == '0000-00-00' || $raw_date == '0000-00-00 00:00:00' || !$raw_date) return '';
  	global $messageStack;
	$error  = false;
    $year   = substr($raw_date,  0, 4);
    $month  = substr($raw_date,  5, 2);
    $day    = substr($raw_date,  8, 2);
    $hour   = $long ? substr($raw_date, 11, 2) : 0;
    $minute = $long ? substr($raw_date, 14, 2) : 0;
    $second = $long ? substr($raw_date, 17, 2) : 0;
	if ($month < 1   || $month > 12)  $error = true;
	if ($day < 1     || $day > 31)    $error = true;
	if ($year < 1900 || $year > 2099) $error = true;
	if ($error) {
	  $messageStack->add(sprintf(GEN_CALENDAR_FORMAT_ERROR, $raw_date, DATE_FORMAT, DATE_FORMAT_CALENDAR),'error');
	  $date_time = time();
	} else {
	  $date_time = mktime($hour, $minute, $second, $month, $day, $year);
	}
	$format = $long ? DATE_TIME_FORMAT : DATE_FORMAT;
    return date($format, $date_time);
  }

  function gen_get_dates($this_date = '') { // this_date format YYYY-MM-DD
  	if (!$this_date) $this_date = date('Y-m-d');
	$result = array();
	$result['Today']     = ($this_date) ? substr(trim($this_date), 0, 10) : date('Y-m-d');
	$result['ThisDay']   = (int)substr($result['Today'], 8, 2);
	$result['ThisMonth'] = (int)substr($result['Today'], 5, 2);
	$result['ThisYear']  = (int)substr($result['Today'], 0, 4);
	$result['TotalDays'] = date('t', mktime( 0, 0, 0, $result['ThisMonth'], $result['ThisDay'], $result['ThisYear']));
	switch($result['ThisMonth']){
		case 1:		$result['MonthName'] = TEXT_JAN;	break;
		case 2:		$result['MonthName'] = TEXT_FEB;	break;
		case 3:		$result['MonthName'] = TEXT_MAR;	break;
		case 4:		$result['MonthName'] = TEXT_APR;	break;
		case 5:		$result['MonthName'] = TEXT_MAY;	break;
		case 6:		$result['MonthName'] = TEXT_JUN;	break;
		case 7:		$result['MonthName'] = TEXT_JUL;	break;
		case 8:		$result['MonthName'] = TEXT_AUG;	break;
		case 9:		$result['MonthName'] = TEXT_SEP;	break;
		case 10:	$result['MonthName'] = TEXT_OCT;	break;
		case 11:	$result['MonthName'] = TEXT_NOV;	break;
		case 12:	$result['MonthName'] = TEXT_DEC;	break;
	}
	return $result;
  }

  function gen_specific_date($start_date, $day_offset = 0, $month_offset = 0, $year_offset = 0) {
	global $messageStack;
	$date_details = gen_get_dates($start_date);
    if ($date_details['ThisYear'] > '1900' && $date_details['ThisYear'] < '2099') {
	  // check for current day greater than the month will allow (for recurs)
	  $days_in_month = date('t', mktime(0, 0, 0, $date_details['ThisMonth'] + $month_offset, 1, $date_details['ThisYear'] + $year_offset));
	  $mod_this_day  = min($days_in_month, $date_details['ThisDay']);
      return date('Y-m-d', mktime(0, 0, 0, $date_details['ThisMonth'] + $month_offset, $mod_this_day + $day_offset, $date_details['ThisYear'] + $year_offset));
    } else {
	  $messageStack->add(sprintf(GEN_CALENDAR_FORMAT_ERROR, $raw_date, DATE_FORMAT, DATE_FORMAT_CALENDAR), 'error');
      return date('Y-m-d');
    }
  }

  function gen_calculate_fiscal_dates($period) {
	global $db, $messageStack;
	$result = $db->Execute("select fiscal_year, start_date, end_date from " . TABLE_ACCOUNTING_PERIODS . " 
	  where period = " . $period);
	if ($result->RecordCount() <> 1) { // post_date is out of range of defined accounting periods
	  $messageStack->add(ERROR_MSG_POST_DATE_NOT_IN_FISCAL_YEAR,'error');
	  return false;
	}
	return $result->fields;
  }

/*************** Country Functions *******************************/
  function gen_pull_countries() {
    if (file_exists(DIR_FS_MODULES . 'phreedom/language/' . $_SESSION['language'] . '/locales.xml')) {
      $xmlStr = file_get_contents(DIR_FS_MODULES . 'phreedom/language/' . $_SESSION['language'] . '/locales.xml');
    } else {
      $xmlStr = file_get_contents(DIR_FS_MODULES . 'phreedom/language/en_us/locales.xml');
    }
	$locales =  xml_to_object($xmlStr);
    if (isset($locales->data)) $locales = $locales->data;
	return $locales;
  }

  function gen_get_country_iso_2_from_3($iso3 = COMPANY_COUNTRY, $countries = false) {
    if (!$countries) $countries = gen_pull_countries();
	foreach ($countries->country as $value) if ($value->iso3 == $iso3) return $value->iso2;
    return $iso3; // not found
  }

  function gen_get_country_iso_3_from_2($iso2, $countries = false) {
    if (!$countries) $countries = gen_pull_countries();
	foreach ($countries->country as $value) if ($value->iso2 == $iso2) return $value->iso3;
    return $iso2; // not found
  }

  function gen_get_countries($choose = false, $countries = false) {
	$temp   = array();
    $output = array();
    if (!$countries) $countries = gen_pull_countries();
    foreach ($countries->country as $value) $temp[$value->iso3] = $value->name;
    asort($temp); // for language translations, sort to alphabetical
    if ($choose) $output[] = array('id' => '0', 'text' => GEN_HEADING_PLEASE_SELECT);
    foreach ($temp as $iso3 => $country) $output[] = array('id' => $iso3, 'text' => $country);
    return $output;
  }

/*************** Other Functions *******************************/
  function gen_get_top_level_domain($url) {
    if (strpos($url, '://')) {
      $url = parse_url($url);
      $url = $url['host'];
    }
    $domain_array = explode('.', $url);
    $domain_size = sizeof($domain_array);
    if ($domain_size > 1) {
      if (is_numeric($domain_array[$domain_size-2]) && is_numeric($domain_array[$domain_size-1])) {
        return false;
      } else {
        if ($domain_size > 3) {
          return $domain_array[$domain_size-3] . '.' . $domain_array[$domain_size-2] . '.' . $domain_array[$domain_size-1];
        } else {
          return $domain_array[$domain_size-2] . '.' . $domain_array[$domain_size-1];
        }
      }
    } else {
      return false;
    }
  }

  function get_ip_address() {
    if (isset($_SERVER)) {
      if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
      } elseif (isset($_SERVER['HTTP_CLIENT_IP'])) {
        $ip = $_SERVER['HTTP_CLIENT_IP'];
      } else {
        $ip = $_SERVER['REMOTE_ADDR'];
      }
    } else {
      if (getenv('HTTP_X_FORWARDED_FOR')) {
        $ip = getenv('HTTP_X_FORWARDED_FOR');
      } elseif (getenv('HTTP_CLIENT_IP')) {
        $ip = getenv('HTTP_CLIENT_IP');
      } else {
        $ip = getenv('REMOTE_ADDR');
      }
    }
    return $ip;
  }

// Return a random value
  function general_rand($min = null, $max = null) {
    static $seeded;
    if (!$seeded) {
      mt_srand((double)microtime()*1000000);
      $seeded = true;
    }
    if (isset($min) && isset($max)) {
      if ($min >= $max) {
        return $min;
      } else {
        return mt_rand($min, $max);
      }
    } else {
      return mt_rand();
    }
  }

  function arr2string($arr) {
    if (!is_array($arr)) return $arr;
    $output = "Array (";
	if (sizeof($arr) > 0) {
	  foreach ($arr as $key => $val) {
	    if (is_array($val)) {
	  	  $output .= ' [' . $key . '] => ' . arr2string($val);
		} else {
	  	  $output .= ' [' . $key . '] => ' . $val;
		}
	  }
	}
	$output .= ' )';
	return $output;
  }

  function string_increment($string, $increment = 1) {
	$string++; // just use the built in PHP operation
	return $string;
  }

  function install_blank_webpage($filename) {
    global $messageStack;
  	$blank_web = '<html>
  <head>
    <title></title>
    <meta content="">
    <style></style>
  </head>
  <body>&nbsp;</body>
</html>';
	if (!$handle = @fopen($filename, 'w')) {
	  $messageStack->add('Cannot open file (' . $filename . ') for writing check your permissions.', 'error');
	  return false;
	}
	fwrite($handle, $blank_web);
	fclose($handle);
	return true;
  }

/**************************************************************************************************************/
// Section 2. Database Functions
/**************************************************************************************************************/
  function db_perform($table, $data, $action = 'insert', $parameters = '') {
    global $db;
    if (!is_array($data)) return false;
    reset($data);
    if ($action == 'insert') {
      $query = 'insert into ' . $table . ' (';
      while (list($columns, ) = each($data)) {
        $query .= $columns . ', ';
      }
      $query = substr($query, 0, -2) . ') values (';
      reset($data);
      while (list(, $value) = each($data)) {
        switch ((string)$value) {
          case 'now()': $query .= 'now(), '; break;
          case 'null':  $query .= 'null, ';  break;
          default:      $query .= '\'' . db_input($value) . '\', '; break;
        }
      }
      $query = substr($query, 0, -2) . ')';
    } elseif ($action == 'update') {
      $query = 'update ' . $table . ' set ';
      while (list($columns, $value) = each($data)) {
        switch ((string)$value) {
          case 'now()': $query .= $columns . ' = now(), '; break;
          case 'null':  $query .= $columns .= ' = null, '; break;
          default:      $query .= $columns . ' = \'' . db_input($value) . '\', '; break;
        }
      }
      $query = substr($query, 0, -2) . ' where ' . $parameters;
    }
    return $db->Execute($query);
  }

  function db_insert_id() {
    global $db;
    return $db->insert_ID();
  }

  function db_input($string) {
    return addslashes($string);
  }

  function db_prepare_input($string, $required = false) {
    if (is_string($string)) {
      $temp = trim(stripslashes($string));
	  if ($required && (strlen($temp) == 0)) {
	  	return false;
	  } else {
	    return $temp;
	  }
    } elseif (is_array($string)) {
      reset($string);
      while (list($key, $value) = each($string)) $string[$key] = db_prepare_input($value);
      return $string;
    } else {
      return $string;
    }
  }

  function db_table_exists($table_name) {
    global $db;
    $tables = $db->Execute("SHOW TABLES like '" . $table_name . "'");
    return ($tables->RecordCount() > 0) ? true : false;
  }

  function db_field_exists($table_name, $field_name) {
    global $db;
    $result = $db->Execute("show fields from " . $table_name);
    while (!$result->EOF) {
      if  ($result->fields['Field'] == $field_name) return true; 
      $result->MoveNext();
    }
    return false;
  }

/**************************************************************************************************************/
// Section 3. HTML Functions
/**************************************************************************************************************/
  function html_href_link($page = '', $parameters = '', $connection = 'NONSSL', $add_session_id = false) {
    global $request_type, $session_started, $http_domain, $https_domain;
    if ($page == '') {
      die('Unable to determine the page link!<br />Function used:<br />html_href_link(\'' . $page . '\', \'' . $parameters . '\', \'' . $connection . '\')');
    }
    if ($connection == 'SSL') {
      $link = DIR_WS_FULL_PATH;
    } else {
      $link = HTTP_SERVER . DIR_WS_ADMIN;
    }
    if (!strstr($page, '.php')) $page .= '.php';
    if ($parameters == '') {
      $link = $link . $page;
      $separator = '?';
    } else {
      $link = $link . $page . '?' . $parameters;
      $separator = '&amp;';
    }
    while ( (substr($link, -1) == '&') || (substr($link, -1) == '?') ) $link = substr($link, 0, -1);
	// Add the session ID when moving from different HTTP and HTTPS servers, or when SID is defined
    if ( ($add_session_id == true) && ($session_started == true) ) {
      if (defined('SID') && gen_not_null(SID)) {
        $sid = SID;
      } elseif ( ( ($request_type == 'NONSSL') && ($connection == 'SSL') && (ENABLE_SSL_ADMIN == 'true') ) || ( ($request_type == 'SSL') && ($connection == 'NONSSL') ) ) {
        if ($http_domain != $https_domain) {
          $sid = session_name() . '=' . session_id();
        }
      }
    }
    if (isset($sid)) $link .= $separator . $sid;
    return $link;
  }

  function html_image($src, $alt = '', $width = '', $height = '', $params = '') {
    $image = '<img src="' . $src . '" alt="' . $alt . '" style="border:none"';
    if (gen_not_null($alt))    $image .= ' title="' . $alt . '"';
    if ($width > 0)            $image .= ' width="' . $width . '"';
    if ($height > 0)           $image .= ' height="' . $height . '"';
    if (gen_not_null($params)) $image .= ' ' . $params;
    $image .= ' />';
    return $image;
  }

  function html_icon($image, $alt = '', $size = 'small', $params = NULL, $width = NULL, $height = NULL, $id = NULL) {
  	switch ($size) {
		default:
		case 'small':  $subdir = '16x16/'; $height='16'; break;
		case 'medium': $subdir = '22x22/'; $height='22'; break;
		case 'large':  $subdir = '32x32/'; $height='32'; break;
		case 'svg' :   $subdir = 'scalable/';            break;
	}
    $image_html = '<img src="' . DIR_WS_ICONS . $subdir . $image . '" alt="' . $alt . '" style="border:none;"';
    if (gen_not_null($alt))    $image_html .= ' title="'  . $alt    . '"';
    if (gen_not_null($id))     $image_html .= ' id="'     . $id     . '"';
    if ($width > 0)            $image_html .= ' width="'  . $width  . '"';
    if ($height > 0)           $image_html .= ' height="' . $height . '"';
    if (gen_not_null($params)) $image_html .= ' ' . $params;
    $image_html .= ' />';
    return $image_html;
  }

  function html_form($name, $action, $parameters = '', $method = 'post', $params = '', $usessl = true) {
    $form = '<form name="' . $name . '" id="' . $name . '" action="';
    if (gen_not_null($parameters)) {
        $form .= html_href_link($action, $parameters, (($usessl) ? 'SSL' : 'NONSSL'));
    } else {
        $form .= html_href_link($action, '', (($usessl) ? 'SSL' : 'NONSSL'));
    }
    $form .= '" method="' . $method . '"';
    if (gen_not_null($params)) $form .= ' ' . $params;
    $form .= '>';
    return $form;
  }

  function html_input_field($name, $value = '', $parameters = '', $required = false, $type = 'text') {
	if (strpos($name, '[]')) { // don't show id attribute if generic array
	  $id = false;
	} else {
	  $id = str_replace('[', '_', $name); // clean up for array inputs causing html errors
	  $id = str_replace(']', '',  $id);
    }
    $field = '<input type="' . $type . '" name="' . $name . '"';
	if ($id)                       $field .= ' id="'    . $id    . '"';
    if (gen_not_null($value))      $field .= ' value="' . str_replace('"', '&quot;', $value) . '"';
    if (gen_not_null($parameters)) $field .= ' ' . $parameters;
    $field .= ' />';
    if ($required == true) $field .= TEXT_FIELD_REQUIRED;
    return $field;
  }

  function html_hidden_field($name, $value = '', $parameters = '') {
    return html_input_field($name, $value, $parameters, false, 'hidden', false);
  }

  function html_password_field($name, $value = '', $required = false, $parameters = '') {
    return html_input_field($name, $value, 'maxlength="40" ' . $parameters, $required, 'password', false);
  }

  function html_file_field($name, $required = false) {
    return html_input_field($name, '', '', $required, 'file', false);
  }

  function html_submit_field($name, $value, $parameters = '') {
  	return html_input_field($name, $value, 'style="cursor:pointer" ' . $parameters, false, 'submit', false);
  }

  function html_button_field($name, $value, $parameters = '') {
  	return '<a href="#" id="'.$name.'" class="ui-state-default ui-corner-all" '.$parameters.'>'.$value.'</a>';
  }

  function html_selection_field($name, $type, $value = '', $checked = false, $compare = '', $parameters = '') {
	if (strpos($name, '[]')) { // don't show id attribute if generic array
	  $id = false;
	} else {
	  $id = str_replace('[','_', $name); // clean up for array inputs causing html errors
	  $id = str_replace(']','',  $id);
    }
	$selection = '<input type="' . $type . '" name="' . $name . '"';
	if ($id) $selection .= ' id="' . $id . '"';
    if (gen_not_null($value)) $selection .= ' value="' . $value . '"';
    if (($checked == true) || (gen_not_null($value) && gen_not_null($compare) && ($value == $compare))) {
      $selection .= ' checked="checked"';
    }
    if (gen_not_null($parameters)) $selection .= ' ' . $parameters;
    $selection .= ' />';
    return $selection;
  }

  function html_checkbox_field($name, $value = '', $checked = false, $compare = '', $parameters = '') {
    return html_selection_field($name, 'checkbox', $value, $checked, $compare, $parameters);
  }

  function html_radio_field($name, $value = '', $checked = false, $compare = '', $parameters = '') {
    $selection  = '<input type="radio" name="' . $name . '" id="' . $name . '_' . $value . '"';
    $selection .= ' value="' . $value . '"';
    if (($checked == true) || (gen_not_null($value) && gen_not_null($compare) && ($value == $compare)) ) {
      $selection .= ' checked="checked"';
    }
    if (gen_not_null($parameters)) $selection .= ' ' . $parameters;
    $selection .= ' />';
    return $selection;
  }

  function html_textarea_field($name, $width, $height, $text = '', $parameters = '') {
  	if (strpos($name, '[]')) { // don't show id attribute if generic array
	  $id = false;
	} else {
	  $id = str_replace('[','_', $name); // clean up for array inputs causing html errors
	  $id = str_replace(']','',  $id);
    }
  	$field = '<textarea name="' . $name . '" id="' . $id . '" cols="' . $width . '" rows="' . $height . '"';
    if ($parameters) $field .= ' ' . $parameters;
    $field .= '>';
    if ($text) $field .= $text;
    $field .= '</textarea>';
    return $field;
  }

  function html_pull_down_menu($name, $values, $default = '', $parameters = '', $required = false) {
	if (strpos($name, '[]')) { // don't show id attribute if generic array
	  $id = false;
	} else {
	  $id = str_replace('[','_', $name); // clean up for array inputs causing html errors
	  $id = str_replace(']','',  $id);
    }
    $field = '<select name="' . $name . '"';
	if ($id) $field .= ' id="' . $id . '"';
    if (gen_not_null($parameters)) $field .= ' ' . $parameters;
    $field .= '>';
    if (empty($default) && isset($GLOBALS[$name])) $default = stripslashes($GLOBALS[$name]);
	if (is_array($values) > 0) {
	  foreach ($values as $choice) if (isset($choice['id'])) {
	    $field .= '<option value="' . $choice['id'] . '"';
	    if (is_array($default)) { // handles pull down with size and multiple parameters set
	      if (in_array($choice['id'], $default)) $field .= ' selected="selected"';
	    } else {
		  if ($default == $choice['id']) $field .= ' selected="selected"';
		}
	    $field .= '>' . htmlspecialchars($choice['text']) . '</option>';
	  }
	}
    $field .= '</select>';
    if ($required == true) $field .= TEXT_FIELD_REQUIRED;
    return $field;
  }

  function html_combo_box($name, $values, $default = '', $parameters = '', $width = '220px', $onchange = '', $id = false) {
	if (!$id) {
	  if (strpos($name, '[]')) { // don't show id attribute if generic array
	    $id = str_replace('[]', '', $name);
	  } else {
	    $id = str_replace('[', '_', $name); // clean up for array inputs causing html errors
	    $id = str_replace(']', '', $id);
      }
	}
	$field  = '<input type="text" name="' . $name . '"';
	if (gen_not_null($id)) $field .= ' id="' . $id . '"';
	$field .= ' value="' . $default . '" ' . $parameters . ' />';
	$field .= '<img name="imgName' . $id . '" id="imgName' . $id . '" alt="" src="' . DIR_WS_ICONS . '16x16/phreebooks/pull_down_inactive.gif" height="16" width="16" align="top" style="border:none;" onmouseover="handleOver(\'imgName' . $id . '\'); return true;" onmouseout="handleOut(\'imgName' . $id . '\'); return true;" onclick="JavaScript:cbMmenuActivate(\'' . $id . '\', \'combodiv' . $id . '\', \'combosel' . $id . '\', \'imgName' . $id . '\')" />';
	$field .= '<div id="combodiv' . $id . '" style="position:absolute; display:none; top:0px; left:0px; z-index:10000" onmouseover="javascript:oOverMenu=\'combodiv' . $id . '\';" onmouseout="javascript:oOverMenu=false;">';
	$field .= '<select size="10" id="combosel' . $id . '" style="width:' . $width . '; border-style:none" onchange="JavaScript:textSet(\'' . $id . '\', this.value); ' . $onchange . ';" onkeypress="JavaScript:comboKey(\'' . $id . '\', this, event);">';
    for ($i = 0; $i < sizeof($values); $i++) {
      $field .= '<option value="' . $values[$i]['id'] . '"';
      if ($default == $values[$i]['id']) $field .= ' selected="selected"';
      $field .= '>' . htmlspecialchars($values[$i]['text']) . '</option>';
    }
	$field .= '</select></div>';
	return $field;
  }

  function history_filter($key=false, $defaults = array()) {
  	if (!$key) $key = $_GET['module'];
  	if (!isset($_REQUEST['sf']))   $_REQUEST['sf']   = isset($_SESSION[$key]['sf'])   ? $_SESSION[$key]['sf']   : $defaults['sf'];
  	if (!isset($_REQUEST['so']))   $_REQUEST['so']   = isset($_SESSION[$key]['so'])   ? $_SESSION[$key]['so']   : $defaults['so'];
  	if (!isset($_REQUEST['list'])) $_REQUEST['list'] = isset($_SESSION[$key]['list']) ? $_SESSION[$key]['list'] : 1;
  	$_REQUEST['list'] = max(1, $_REQUEST['list']);
  	if (!isset($_REQUEST['search_text'])) $_REQUEST['search_text'] = isset($_SESSION[$key]['search']) ? $_SESSION[$key]['search'] : '';
  	if ( $_REQUEST['search_text'] == TEXT_SEARCH) $_REQUEST['search_text'] = '';
  	if (!$_REQUEST['action'] && $_REQUEST['search_text'] <> '') $_REQUEST['action'] = 'search'; // if enter key pressed and search not blank
  	if ( $_REQUEST['search_text'] <> '' && $_REQUEST['search_text'] <> $_SESSION[$key]['search']) $_REQUEST['list'] = 1;
  	if (!isset($_REQUEST['search_period'])) $_REQUEST['search_period']= isset($_SESSION[$key]['period'])? $_SESSION[$key]['period']: CURRENT_ACCOUNTING_PERIOD;
  	if (!isset($_REQUEST['search_date']))   $_REQUEST['search_date']  = isset($_SESSION[$key]['date'])  ? $_SESSION[$key]['date']  : '';
  	if ($_REQUEST['reset']) {
  		$_REQUEST['sf']    = $defaults['sf'];
  		$_REQUEST['so']    = $defaults['so'];
  		$_REQUEST['list']  = 1;
  		$_REQUEST['search_text']  = '';
  		$_REQUEST['search_period']= CURRENT_ACCOUNTING_PERIOD;
  		$_REQUEST['search_date']  = '';
  		unset($_GET['reset']);
  	}
  }

  function history_save($key=false) {
  	if (!$key) $key = $_GET['module'];
  	$_SESSION[$key]['sf']    = $_REQUEST['sf'];
  	$_SESSION[$key]['so']    = $_REQUEST['so'];
    $_SESSION[$key]['list']  = $_REQUEST['list'];
    $_SESSION[$key]['search']= $_REQUEST['search_text'];
    $_SESSION[$key]['period']= $_REQUEST['search_period'];
    $_SESSION[$key]['date']  = $_REQUEST['search_date'];
  }

/**
 * this function creates a heading for a table that will be able to sort 
 * @param array $heading_array the fields of the table
 * @param array $extra_headings extra columns that do not have the abillety to sort
 * @return 'html_code' this is the table heading
 * @return 'disp_order' this is the field + display order for the sql statement_builder
 */
	
  function html_heading_bar($heading_array, $extra_headings = array(TEXT_ACTION)) {
	global $PHP_SELF; 
	$result = array();
	$output .= html_hidden_field('sf', $_REQUEST['sf']) . chr(10);
    $output .= html_hidden_field('so', ($_REQUEST['so'] == 'desc' ? 'desc' : 'asc') ) . chr(10);
	foreach ($heading_array as $key => $value) {
	  if (!isset($result['disp_order'])) $result['disp_order'] = $key; // set the first key to the default
      $image_asc  = 'sort_asc_disabled.png';
      $image_desc = 'sort_desc_disabled.png';
	  if ($value == $_REQUEST['sf'] || ($result['disp_order'] == $key && $_REQUEST['sf'] == '') ){
	       if ($_REQUEST['so'] == 'desc'){
	           $result['disp_order'] = $key . ' DESC';
               $image_desc = 'sort_desc.png';
	       }else{
	           $result['disp_order'] = $key . ' ASC';
               $image_asc = 'sort_asc.png';
	       }
	  }
	  $output .= '<th nowrap="nowrap">' . chr(10);
	  if ($value) $output .= html_image(DIR_WS_IMAGES . $image_asc  , TEXT_ASC,  '', '', 'onclick="submitSortOrder(\''.$value.'\',\'asc\')"'). chr(10);
	  $output .= $value;
	  if ($value) $output .= html_image(DIR_WS_IMAGES . $image_desc , TEXT_DESC, '', '', 'onclick="submitSortOrder(\''.$value.'\',\'desc\')"'). chr(10);
	  $output .= '</th>' . chr(10);
	}
	if (sizeof($extra_headings) > 0) foreach ($extra_headings as $value) {
	  $output .= '<th nowrap="nowrap">' . $value . '</th>' . chr(10);
	}
	$result['html_code'] = $output; 
	return $result;
  }

  function html_datatable($id, $content = NULL) {
	$head_bar  = '   <tr>'."\n";
	foreach ($content['thead']['value'] as $heading) $head_bar .= '    <th nowrap="nowrap">'.htmlspecialchars($heading).'</th>'."\n";
	$head_bar .= '   </tr>'."\n";
	$output    = '<table class="ui-widget" id="'.$id.'" '.$content['thead']['params'].'>'."\n";
	$output   .= '  <thead class="ui-widget-header">'."\n".$head_bar.'  </thead>'."\n";
	$output   .= '  <tbody class="ui-widget-content">'."\n";
    if (is_array($content['tbody'])) {
	  foreach ($content['tbody'] as $row) {
	    $output .= '  <tr>'."\n";
	    foreach ($row as $element) $output .= '    <td nowrap="nowrap" '.$element['params'].'>'.$element['value'].'</td>'."\n";
        $output .= '  </tr>'."\n";
	  }
	} else {
	  $output .= '  <tr>'."\n";
	  $output .= '    <td nowrap="nowrap">'.TEXT_NO_DATA.'</td>'."\n";
	  for ($i = 1; $i < sizeof($content['thead']['value']); $i++) $output .= '    <td>&nbsp;</td>'."\n";
	  $output .= '  </tr>'."\n";
	}
 	$output .= '  </tbody>'."\n";
 	$output .= '  <tfoot class="ui-widget-header">'."\n".$head_bar.'  </tfoot>'."\n";
 	$output .= '</table>'."\n";
    return $output;
  }

  function add_tab_list($name, $text, $active = false) {
	return '<li><a href="#' . $name . '">' . $text . '</a></li>' . chr(10);
  }

  function build_dir_html($name, $full_array) {
	$entry_string  = NULL;
//	$entry_string  .= '<table id="' . $name . '" cellpadding="0" cellspacing="0">' . chr(10);
	$entry_string .= build_dir_tree($name, $full_array, $index = -1, $level = 0, $cont_level = array());
//	$entry_string .= '</table>' . chr(10);
	return $entry_string;
  }

  function build_dir_tree($name, $full_array, $index = -1, $level = 0, $cont_level = array()) {
	$entry_string = '';
	for ($j = 0; $j < sizeof($full_array[$index]); $j++) {
	  $new_ref   = $index . '_' . $full_array[$index][$j]['id'];
	  $cont_temp = array_keys($cont_level);
	  $entry_string .= '<div style="height:16px;">' . chr(10);
//	  $entry_string .= '<table cellpadding="0" cellspacing="0">' . chr(10);
//	  $entry_string .= '<tr><td nowrap="nowrap">' . chr(10);
	  for ($i = 0; $i < $level; $i++) {
	    if (false) {
	    } elseif ($i == $level-1 && $j < sizeof($full_array[$index])-1) {
		  $entry_string .= html_icon('phreebooks/cont-end.gif', '', 'small');
		} elseif ($i == $level-1 && $j == sizeof($full_array[$index])-1) {
		  $entry_string .= html_icon('phreebooks/end-end.gif', '', 'small');
		} elseif (in_array($i, $cont_temp)) {
		  $entry_string .= html_icon('phreebooks/cont.gif', '', 'small');
		} elseif ($i < $level-1) {
		  $entry_string .= html_icon('phreebooks/blank.gif', '', 'small');
		}
	  }
	  // change title to language if constant is defined
	  if (defined($full_array[$index][$j]['doc_title'])) $full_array[$index][$j]['doc_title'] = constant($full_array[$index][$j]['doc_title']); 
	  if ($full_array[$index][$j]['doc_type'] == '0') {  // folder
		$entry_string .= '<a id="imgdc_' . $new_ref . '" href="javascript:Toggle(\'dc_' . $new_ref . '\');">' . html_icon('places/folder.png', TEXT_OPEN, 'small', '', '', '', 'icndc_' . $new_ref) . '</a>';
	  } else {
		$entry_string .= html_icon('mimetypes/text-x-generic.png', $full_array[$index][$j]['doc_title'], 'small');
	  }
//	  $entry_string .= '</td>' . chr(10);
//	  $entry_string .= '<td>';
	  $short_title   = (strlen($full_array[$index][$j]['doc_title']) <= PF_DEFAULT_TRIM_LENGTH) ? $full_array[$index][$j]['doc_title'] : substr($full_array[$index][$j]['doc_title'], 0, PF_DEFAULT_TRIM_LENGTH) . '...';
	  $entry_string .= '&nbsp;<a href="javascript:fetch_doc(' . $full_array[$index][$j]['id'] . ');">' . htmlspecialchars($short_title) . '</a>' . chr(10);
//	  $entry_string .= '</td></tr>' . chr(10);
//	  $entry_string .= '</table>' . chr(10);
	  $entry_string .= '</div>' . chr(10);
	  if ($j < sizeof($full_array[$index])-1) {
		$cont_level[$level-1] = true;
	  } else {
		unset($cont_level[$level-1]);
	  }
	  if (isset($full_array[$full_array[$index][$j]['id']])) {
		$display_none = ($level == 0 || $full_array[$index][$j]['show']) ? '' : 'display:none; ';
		$entry_string .= '<div id="dc_' . $new_ref . '" style="' . $display_none . 'margin-left:0px;">' . chr(10);
		$entry_string .= build_dir_tree($name, $full_array, $full_array[$index][$j]['id'], $level+1, $cont_level) . chr(10);
		$entry_string .= '</div>' . chr(10);
	  }
	}
	return $entry_string;
  }

/**************************************************************************************************************/
// Section 4. Localization Functions
/**************************************************************************************************************/
function charConv($string, $in, $out) {
	$str = NULL;
	// make them both lowercase
	$in = strtolower($in);
	$out = strtolower($out);
	// sanity checking
	if (!$in || !$out) return $string;
	if ($in == $out) return $string;
	// return string if we don't have this function
	if (!function_exists("iconv")) return $string;
	// this tells php to ignore characters it doesn't know
	$out .= "//IGNORE";
	return iconv($in, $out, $string);
}

  function strtolower_utf8($string){
    $convert_from = array(
      "A", "B", "C", "D", "E", "F", "G", "H", "I", "J", "K", "L", "M", "N", "O", "P", "Q", "R", "S", "T", "U",
      "V", "W", "X", "Y", "Z", "ÃƒÆ’Ã¢â€šÂ¬", "ÃƒÆ’Ã¯Â¿Â½", "ÃƒÆ’Ã¢â‚¬Å¡", "ÃƒÆ’Ã†â€™", "ÃƒÆ’Ã¢â‚¬Å¾", "ÃƒÆ’Ã¢â‚¬Â¦", "ÃƒÆ’Ã¢â‚¬Â ", "ÃƒÆ’Ã¢â‚¬Â¡", "ÃƒÆ’Ã‹â€ ", "ÃƒÆ’Ã¢â‚¬Â°", "ÃƒÆ’Ã…Â ", "ÃƒÆ’Ã¢â‚¬Â¹", "ÃƒÆ’Ã…â€™", "ÃƒÆ’Ã¯Â¿Â½", "ÃƒÆ’Ã…Â½", "ÃƒÆ’Ã¯Â¿Â½",
      "ÃƒÆ’Ã¯Â¿Â½", "ÃƒÆ’Ã¢â‚¬Ëœ", "ÃƒÆ’Ã¢â‚¬â„¢", "ÃƒÆ’Ã¢â‚¬Å“", "ÃƒÆ’Ã¢â‚¬ï¿½", "ÃƒÆ’Ã¢â‚¬Â¢", "ÃƒÆ’Ã¢â‚¬â€œ", "ÃƒÆ’Ã‹Å“", "ÃƒÆ’Ã¢â€žÂ¢", "ÃƒÆ’Ã…Â¡", "ÃƒÆ’Ã¢â‚¬Âº", "ÃƒÆ’Ã…â€œ", "ÃƒÆ’Ã¯Â¿Â½", "Ãƒï¿½Ã¯Â¿Â½", "Ãƒï¿½Ã¢â‚¬Ëœ", "Ãƒï¿½Ã¢â‚¬â„¢", "Ãƒï¿½Ã¢â‚¬Å“", "Ãƒï¿½Ã¢â‚¬ï¿½", "Ãƒï¿½Ã¢â‚¬Â¢", "Ãƒï¿½Ã¯Â¿Â½", "Ãƒï¿½Ã¢â‚¬â€œ",
      "Ãƒï¿½Ã¢â‚¬â€�", "Ãƒï¿½Ã‹Å“", "Ãƒï¿½Ã¢â€žÂ¢", "Ãƒï¿½Ã…Â¡", "Ãƒï¿½Ã¢â‚¬Âº", "Ãƒï¿½Ã…â€œ", "Ãƒï¿½Ã¯Â¿Â½", "Ãƒï¿½Ã…Â¾", "Ãƒï¿½Ã…Â¸", "Ãƒï¿½Ã‚Â ", "Ãƒï¿½Ã‚Â¡", "Ãƒï¿½Ã‚Â¢", "Ãƒï¿½Ã‚Â£", "Ãƒï¿½Ã‚Â¤", "Ãƒï¿½Ã‚Â¥", "Ãƒï¿½Ã‚Â¦", "Ãƒï¿½Ã‚Â§", "Ãƒï¿½Ã‚Â¨", "Ãƒï¿½Ã‚Â©", "Ãƒï¿½Ã‚Âª", "Ãƒï¿½Ã‚Âª",
      "Ãƒï¿½Ã‚Â¬", "Ãƒï¿½Ã‚Â­", "Ãƒï¿½Ã‚Â®", "Ãƒï¿½Ã‚Â¯"
    );
    $convert_to = array(
      "a", "b", "c", "d", "e", "f", "g", "h", "i", "j", "k", "l", "m", "n", "o", "p", "q", "r", "s", "t", "u",
      "v", "w", "x", "y", "z", "ÃƒÆ’Ã‚Â ", "ÃƒÆ’Ã‚Â¡", "ÃƒÆ’Ã‚Â¢", "ÃƒÆ’Ã‚Â£", "ÃƒÆ’Ã‚Â¤", "ÃƒÆ’Ã‚Â¥", "ÃƒÆ’Ã‚Â¦", "ÃƒÆ’Ã‚Â§", "ÃƒÆ’Ã‚Â¨", "ÃƒÆ’Ã‚Â©", "ÃƒÆ’Ã‚Âª", "ÃƒÆ’Ã‚Â«", "ÃƒÆ’Ã‚Â¬", "ÃƒÆ’Ã‚Â­", "ÃƒÆ’Ã‚Â®", "ÃƒÆ’Ã‚Â¯",
      "ÃƒÆ’Ã‚Â°", "ÃƒÆ’Ã‚Â±", "ÃƒÆ’Ã‚Â²", "ÃƒÆ’Ã‚Â³", "ÃƒÆ’Ã‚Â´", "ÃƒÆ’Ã‚Âµ", "ÃƒÆ’Ã‚Â¶", "ÃƒÆ’Ã‚Â¸", "ÃƒÆ’Ã‚Â¹", "ÃƒÆ’Ã‚Âº", "ÃƒÆ’Ã‚Â»", "ÃƒÆ’Ã‚Â¼", "ÃƒÆ’Ã‚Â½", "Ãƒï¿½Ã‚Â°", "Ãƒï¿½Ã‚Â±", "Ãƒï¿½Ã‚Â²", "Ãƒï¿½Ã‚Â³", "Ãƒï¿½Ã‚Â´", "Ãƒï¿½Ã‚Âµ", "Ãƒâ€˜Ã¢â‚¬Ëœ", "Ãƒï¿½Ã‚Â¶",
      "Ãƒï¿½Ã‚Â·", "Ãƒï¿½Ã‚Â¸", "Ãƒï¿½Ã‚Â¹", "Ãƒï¿½Ã‚Âº", "Ãƒï¿½Ã‚Â»", "Ãƒï¿½Ã‚Â¼", "Ãƒï¿½Ã‚Â½", "Ãƒï¿½Ã‚Â¾", "Ãƒï¿½Ã‚Â¿", "Ãƒâ€˜Ã¢â€šÂ¬", "Ãƒâ€˜Ã¯Â¿Â½", "Ãƒâ€˜Ã¢â‚¬Å¡", "Ãƒâ€˜Ã†â€™", "Ãƒâ€˜Ã¢â‚¬Å¾", "Ãƒâ€˜Ã¢â‚¬Â¦", "Ãƒâ€˜Ã¢â‚¬Â ", "Ãƒâ€˜Ã¢â‚¬Â¡", "Ãƒâ€˜Ã‹â€ ", "Ãƒâ€˜Ã¢â‚¬Â°", "Ãƒâ€˜Ã…Â ", "Ãƒâ€˜Ã¢â‚¬Â¹",
      "Ãƒâ€˜Ã…â€™", "Ãƒâ€˜Ã¯Â¿Â½", "Ãƒâ€˜Ã…Â½", "Ãƒâ€˜Ã¯Â¿Â½"
    );
    return str_replace($convert_from, $convert_to, $string);
  }

  function strtoupper_utf8($string){
    $convert_from = array(
      "a", "b", "c", "d", "e", "f", "g", "h", "i", "j", "k", "l", "m", "n", "o", "p", "q", "r", "s", "t", "u",
      "v", "w", "x", "y", "z", "ÃƒÆ’Ã‚Â ", "ÃƒÆ’Ã‚Â¡", "ÃƒÆ’Ã‚Â¢", "ÃƒÆ’Ã‚Â£", "ÃƒÆ’Ã‚Â¤", "ÃƒÆ’Ã‚Â¥", "ÃƒÆ’Ã‚Â¦", "ÃƒÆ’Ã‚Â§", "ÃƒÆ’Ã‚Â¨", "ÃƒÆ’Ã‚Â©", "ÃƒÆ’Ã‚Âª", "ÃƒÆ’Ã‚Â«", "ÃƒÆ’Ã‚Â¬", "ÃƒÆ’Ã‚Â­", "ÃƒÆ’Ã‚Â®", "ÃƒÆ’Ã‚Â¯",
      "ÃƒÆ’Ã‚Â°", "ÃƒÆ’Ã‚Â±", "ÃƒÆ’Ã‚Â²", "ÃƒÆ’Ã‚Â³", "ÃƒÆ’Ã‚Â´", "ÃƒÆ’Ã‚Âµ", "ÃƒÆ’Ã‚Â¶", "ÃƒÆ’Ã‚Â¸", "ÃƒÆ’Ã‚Â¹", "ÃƒÆ’Ã‚Âº", "ÃƒÆ’Ã‚Â»", "ÃƒÆ’Ã‚Â¼", "ÃƒÆ’Ã‚Â½", "Ãƒï¿½Ã‚Â°", "Ãƒï¿½Ã‚Â±", "Ãƒï¿½Ã‚Â²", "Ãƒï¿½Ã‚Â³", "Ãƒï¿½Ã‚Â´", "Ãƒï¿½Ã‚Âµ", "Ãƒâ€˜Ã¢â‚¬Ëœ", "Ãƒï¿½Ã‚Â¶",
      "Ãƒï¿½Ã‚Â·", "Ãƒï¿½Ã‚Â¸", "Ãƒï¿½Ã‚Â¹", "Ãƒï¿½Ã‚Âº", "Ãƒï¿½Ã‚Â»", "Ãƒï¿½Ã‚Â¼", "Ãƒï¿½Ã‚Â½", "Ãƒï¿½Ã‚Â¾", "Ãƒï¿½Ã‚Â¿", "Ãƒâ€˜Ã¢â€šÂ¬", "Ãƒâ€˜Ã¯Â¿Â½", "Ãƒâ€˜Ã¢â‚¬Å¡", "Ãƒâ€˜Ã†â€™", "Ãƒâ€˜Ã¢â‚¬Å¾", "Ãƒâ€˜Ã¢â‚¬Â¦", "Ãƒâ€˜Ã¢â‚¬Â ", "Ãƒâ€˜Ã¢â‚¬Â¡", "Ãƒâ€˜Ã‹â€ ", "Ãƒâ€˜Ã¢â‚¬Â°", "Ãƒâ€˜Ã…Â ", "Ãƒâ€˜Ã¢â‚¬Â¹",
      "Ãƒâ€˜Ã…â€™", "Ãƒâ€˜Ã¯Â¿Â½", "Ãƒâ€˜Ã…Â½", "Ãƒâ€˜Ã¯Â¿Â½"
    );
    $convert_to = array(
      "A", "B", "C", "D", "E", "F", "G", "H", "I", "J", "K", "L", "M", "N", "O", "P", "Q", "R", "S", "T", "U",
      "V", "W", "X", "Y", "Z", "ÃƒÆ’Ã¢â€šÂ¬", "ÃƒÆ’Ã¯Â¿Â½", "ÃƒÆ’Ã¢â‚¬Å¡", "ÃƒÆ’Ã†â€™", "ÃƒÆ’Ã¢â‚¬Å¾", "ÃƒÆ’Ã¢â‚¬Â¦", "ÃƒÆ’Ã¢â‚¬Â ", "ÃƒÆ’Ã¢â‚¬Â¡", "ÃƒÆ’Ã‹â€ ", "ÃƒÆ’Ã¢â‚¬Â°", "ÃƒÆ’Ã…Â ", "ÃƒÆ’Ã¢â‚¬Â¹", "ÃƒÆ’Ã…â€™", "ÃƒÆ’Ã¯Â¿Â½", "ÃƒÆ’Ã…Â½", "ÃƒÆ’Ã¯Â¿Â½",
      "ÃƒÆ’Ã¯Â¿Â½", "ÃƒÆ’Ã¢â‚¬Ëœ", "ÃƒÆ’Ã¢â‚¬â„¢", "ÃƒÆ’Ã¢â‚¬Å“", "ÃƒÆ’Ã¢â‚¬ï¿½", "ÃƒÆ’Ã¢â‚¬Â¢", "ÃƒÆ’Ã¢â‚¬â€œ", "ÃƒÆ’Ã‹Å“", "ÃƒÆ’Ã¢â€žÂ¢", "ÃƒÆ’Ã…Â¡", "ÃƒÆ’Ã¢â‚¬Âº", "ÃƒÆ’Ã…â€œ", "ÃƒÆ’Ã¯Â¿Â½", "Ãƒï¿½Ã¯Â¿Â½", "Ãƒï¿½Ã¢â‚¬Ëœ", "Ãƒï¿½Ã¢â‚¬â„¢", "Ãƒï¿½Ã¢â‚¬Å“", "Ãƒï¿½Ã¢â‚¬ï¿½", "Ãƒï¿½Ã¢â‚¬Â¢", "Ãƒï¿½Ã¯Â¿Â½", "Ãƒï¿½Ã¢â‚¬â€œ",
      "Ãƒï¿½Ã¢â‚¬â€�", "Ãƒï¿½Ã‹Å“", "Ãƒï¿½Ã¢â€žÂ¢", "Ãƒï¿½Ã…Â¡", "Ãƒï¿½Ã¢â‚¬Âº", "Ãƒï¿½Ã…â€œ", "Ãƒï¿½Ã¯Â¿Â½", "Ãƒï¿½Ã…Â¾", "Ãƒï¿½Ã…Â¸", "Ãƒï¿½Ã‚Â ", "Ãƒï¿½Ã‚Â¡", "Ãƒï¿½Ã‚Â¢", "Ãƒï¿½Ã‚Â£", "Ãƒï¿½Ã‚Â¤", "Ãƒï¿½Ã‚Â¥", "Ãƒï¿½Ã‚Â¦", "Ãƒï¿½Ã‚Â§", "Ãƒï¿½Ã‚Â¨", "Ãƒï¿½Ã‚Â©", "Ãƒï¿½Ã‚Âª", "Ãƒï¿½Ã‚Âª",
      "Ãƒï¿½Ã‚Â¬", "Ãƒï¿½Ã‚Â­", "Ãƒï¿½Ã‚Â®", "Ãƒï¿½Ã‚Â¯"
    );
    return str_replace($convert_from, $convert_to, $string);
  }

/**************************************************************************************************************/
// Section 5. Extra Fields Functions
/**************************************************************************************************************/
  function xtra_field_build_entry($param_array, $cInfo) {
	$output = '<tr><td>' . $param_array['description'] . '</td>';
	$params = unserialize($param_array['params']);
	switch ($params['type']) {
		case 'text':
		case 'html':
			if ($params['length'] < 256) {
				$length = ($params['length'] > 120) ? 'size="120"' : ('size="' . $params['length'] . '"');
				$output .= '<td>' . html_input_field($param_array['field_name'], $cInfo->$param_array['field_name'], $length) . '</td></tr>';
			} else {
				$output .= '<td>' . html_textarea_field($param_array['field_name'], DEFAULT_INPUT_FIELD_LENGTH, 4, $cInfo->$param_array['field_name']) . '</td></tr>';
			}
			break;
		case 'hyperlink':
		case 'image_link':
		case 'inventory_link':
			$output .= '<td>' . html_input_field($param_array['field_name'], $cInfo->$param_array['field_name'], 'size="' . DEFAULT_INPUT_FIELD_LENGTH . '"') . '</td></tr>';
			break;
		case 'integer':
		case 'decimal':
			$output .= '<td>' . html_input_field($param_array['field_name'], $cInfo->$param_array['field_name'], 'size="13" maxlength="12" style="text-align:right"') . '</td></tr>';
			break;
		case 'date':
		case 'time':
		case 'date_time':
			$output .= '<td>' . html_input_field($param_array['field_name'], $cInfo->$param_array['field_name'], 'size="21" maxlength="20"') . '</td></tr>';
			break;
		case 'drop_down':
		case 'enum':
			$choices = explode(',',$params['default']);
			$pull_down_selection = array();
			$default_selection = '';
			while ($choice = array_shift($choices)) {
				$values = explode(':',$choice);
				$pull_down_selection[] = array('id' => $values[0], 'text' => $values[1]);
				if ($cInfo->$param_array['field_name'] == $values[0]) $default_selection = $values[0];
			}
			$output .= '<td>' . html_pull_down_menu($param_array['field_name'], $pull_down_selection, $default_selection) . '</td></tr>';
			break;
		case 'radio':
			$output .= '<td>';
			$choices = explode(',',$params['default']);
			while ($choice = array_shift($choices)) {
				$values = explode(':',$choice);
				$output .= html_radio_field($param_array['field_name'], $values[0], ($cInfo->$param_array['field_name']==$values[0]) ? true : false);
				$output .= '<label for="' . $param_array['field_name']. '_' . $values[0] . '"> ' . $values[1] . '</label>';
			}
			$output .= '</td></tr>';
			break;
		case 'multi_check_box':	
			$output  .= '<td>';
			$output  .= '<table frame="border"><tr>';
			$choices  = explode(',',$params['default']);
			$selected = explode(',',$cInfo->$param_array['field_name']);
			$i = 1;
			while ($choice = array_shift($choices)) {
				$values = explode(':', $choice);
				$output .= '<td>';
				$output .= html_checkbox_field($param_array['field_name'] . $values[0] , $values[0], in_array($values[0], $selected) ? true : false);
				$output .= '<label for="' . $param_array['field_name'] . $values[0] . '"> ' . $values[1] . '</label>';
				$output .= '</td>';
				if ($i == 4){
					$output .= '</tr><tr>';
					$i=0;
				}
				$i++;
			}
			$output .= '</tr></table>';
			$output .= '</td></tr>';
			break;	
		case 'check_box':
			$output .= '<td>' . html_checkbox_field($param_array['field_name'], '1', ($cInfo->$param_array['field_name']==1) ? true : false) . '</td></tr>';
			break;
		case 'time_stamp':
		default:
			$output = '';
	}
	return $output;
  }

/**************************************************************************************************************/
// Section 6. Validation Functions
/**************************************************************************************************************/
function validate_user($token = 0, $user_active = false) {
  global $messageStack;
  $security_level = $_SESSION['admin_security'][$token];
  if (!in_array($security_level, array(1,2,3,4)) && !$user_active) { // not suppose to be here, bail
    $messageStack->add(ERROR_NO_PERMISSION, 'error');
    gen_redirect(html_href_link(FILENAME_DEFAULT, '', 'SSL'));
  }
  return $user_active ? 1 : $security_level;
}

function validate_security($security_level = 0, $required_level = 1) {
  global $messageStack;
  if ($security_level < $required_level) {
	$messageStack->add(ERROR_NO_PERMISSION, 'error');
	gen_redirect(html_href_link(FILENAME_DEFAULT, gen_get_all_get_params(array('action')), 'SSL'));
  }
  return true;
}

function validate_ajax_user($token = 0) {
  $security_level = $token ? $_SESSION['admin_security'][$token] : (int)$_SESSION['admin_id'];
  if (!$security_level) { // not suppose to be here
    echo createXmlHeader() . xmlEntry('error', ERROR_NO_PERMISSION) . createXmlFooter();
    die;
  }
  return $token ? $security_level : 1;
}

  function validate_email($email) {
    $isValid = true;
    $atIndex = strrpos($email, "@");
    if (is_bool($atIndex) && !$atIndex) {
      $isValid = false;
    } else {
      $domain    = substr($email, $atIndex+1);
      $local     = substr($email, 0, $atIndex);
      $localLen  = strlen($local);
      $domainLen = strlen($domain);
      if ($localLen < 1 || $localLen > 64) { // local part length exceeded
         $isValid = false;
      } else if ($domainLen < 1 || $domainLen > 255) { // domain part length exceeded
         $isValid = false;
      } else if ($local[0] == '.' || $local[$localLen-1] == '.') { // local part starts or ends with '.'
         $isValid = false;
      } else if (preg_match('/\\.\\./', $local)) { // local part has two consecutive dots
         $isValid = false;
      } else if (!preg_match('/^[A-Za-z0-9\\-\\.]+$/', $domain)) { // character not valid in domain part
         $isValid = false;
      } else if (preg_match('/\\.\\./', $domain)) { // domain part has two consecutive dots
         $isValid = false;
      } else if (!preg_match('/^(\\\\.|[A-Za-z0-9!#%&`_=\\/$\'*+?^{}|~.-])+$/', str_replace("\\\\","",$local))) {
         // character not valid in local part unless local part is quoted
         if (!preg_match('/^"(\\\\"|[^"])+"$/', str_replace("\\\\","",$local))) $isValid = false;
      }
    }
    return $isValid;
  }

  function validate_upload($filename, $file_type = 'text', $extension = 'txt') {
  	global $messageStack;
	if ($_FILES[$filename]['error']) { // php error uploading file
		switch ($_FILES[$filename]['error']) {
			case '1': $messageStack->add(TEXT_IMP_ERMSG1, 'error'); break;
			case '2': $messageStack->add(TEXT_IMP_ERMSG2, 'error'); break;
			case '3': $messageStack->add(TEXT_IMP_ERMSG3, 'error'); break;
			case '4': $messageStack->add(TEXT_IMP_ERMSG4, 'error'); break;
			default:  $messageStack->add(TEXT_IMP_ERMSG5 . $_FILES[$filename]['error'] . '.', 'error');
		}
		return false;
	} elseif (!is_uploaded_file($_FILES[$filename]['tmp_name'])) { // file uploaded
		$messageStack->add(TEXT_IMP_ERMSG13, 'error');
		return false;
	} elseif ($_FILES[$filename]['size'] == 0) { // report contains no data, error
		$messageStack->add(TEXT_IMP_ERMSG7, 'error');
		return false;
	}
	$ext = strtolower(substr($_FILES[$filename]['name'], -3, 3));
	$textfile = (strpos($_FILES[$filename]['type'], $file_type) === false) ? false : true;
	if (!is_array($extension)) $extension = array($extension);
	if ((!$textfile && in_array($ext, $extension)) || $textfile) { // allow file_type and extensions
		return true;
	}
	$messageStack->add(TEXT_IMP_ERMSG6, 'error');
	return false;
  }

  function validate_path($file_path) {
	if (!is_dir($file_path)) mkdir($file_path, 0777, true);
	return true;
  }

  function validate_db_date($date) {
    $y = (int)substr($date, 0, 4); 
	if ($y < 1900 || $y > 2099) return false;
    $m = (int)substr($date, 5, 2); 
	if ($m < 1 || $m > 12) return false;
    $d = (int)substr($date, 8, 2); 
	if ($d < 1 || $d > 31) return false;
	return true;
  }

  function validate_send_mail($to_name, $to_address, $email_subject, $email_text, $from_email_name, $from_email_address, $block = array(), $attachments_list = '' ) {
    global $db, $messageStack;
    // check for injection attempts. If new-line characters found in header fields, simply fail to send the message
    foreach(array($from_email_address, $to_address, $from_email_name, $to_name, $email_subject) as $key => $value) {
      if (!$value) continue;
	  if (strpos("\r", $value) !== false || strpos("\n", $value) !== false) return false;
    }
    // if no text or html-msg supplied, exit
    if (!gen_not_null($email_text) && !gen_not_null($block['EMAIL_MESSAGE_HTML'])) return false;
    // if email name is same as email address, use the Store Name as the senders 'Name'
    if ($from_email_name == $from_email_address) $from_email_name = COMPANY_NAME;
    // loop thru multiple email recipients if more than one listed  --- (esp for the admin's "Extra" emails)...
    foreach(explode(',', $to_address) as $key => $to_email_address) {
      //define some additional html message blocks available to templates, then build the html portion.
      if ($block['EMAIL_TO_NAME'] == '')      $block['EMAIL_TO_NAME']      = $to_name;
      if ($block['EMAIL_TO_ADDRESS'] == '')   $block['EMAIL_TO_ADDRESS']   = $to_email_address;
      if ($block['EMAIL_SUBJECT'] == '')      $block['EMAIL_SUBJECT']      = $email_subject;
      if ($block['EMAIL_FROM_NAME'] == '')    $block['EMAIL_FROM_NAME']    = $from_email_name;
      if ($block['EMAIL_FROM_ADDRESS'] == '') $block['EMAIL_FROM_ADDRESS'] = $from_email_address;
      $email_html = $email_text;
      //  if ($attachments_list == '') $attachments_list= array();
      // clean up &amp; and && from email text
      while (strstr($email_text, '&amp;&amp;')) $email_text = str_replace('&amp;&amp;', '&amp;', $email_text);
      while (strstr($email_text, '&amp;'))      $email_text = str_replace('&amp;', '&', $email_text);
      while (strstr($email_text, '&&'))         $email_text = str_replace('&&', '&', $email_text);
      // clean up currencies for text emails
      $fix_currencies = explode(":", CURRENCIES_TRANSLATIONS);
      $size = sizeof($fix_currencies);
      for ($i=0, $n=$size; $i<$n; $i+=2) {
        $fix_current = $fix_currencies[$i];
        $fix_replace = $fix_currencies[$i+1];
        if (strlen($fix_current)>0) {
          while (strpos($email_text, $fix_current)) $email_text = str_replace($fix_current, $fix_replace, $email_text);
        }
      }
      // fix double quotes
      while (strstr($email_text, '&quot;')) $email_text = str_replace('&quot;', '"', $email_text);
      // fix slashes
      $email_text = stripslashes($email_text);
      $email_html = stripslashes($email_html);
      // Build the email based on whether customer has selected HTML or TEXT, and whether we have supplied HTML or TEXT-only components
      if (!gen_not_null($email_text)) {
        $text = str_replace('<br[[:space:]]*/?[[:space:]]*>', "\n", $block['EMAIL_MESSAGE_HTML']);
        $text = str_replace('</p>', "</p>\n", $text);
        $text = htmlspecialchars(stripslashes(strip_tags($text)));
      } else {
        $text = strip_tags($email_text);
      }
      // now lets build the mail object with the phpmailer class
	  require_once(DIR_FS_MODULES . 'phreedom/includes/PHPMailer/class.phpmailer.php');
      $mail = new PHPMailer();
      $mail->SetLanguage();
      $mail->CharSet =  (defined('CHARSET')) ? CHARSET : "iso-8859-1";
      if ($debug_mode=='on') $mail->SMTPDebug = true;
      if (EMAIL_TRANSPORT=='smtp' || EMAIL_TRANSPORT=='smtpauth') {
        $mail->IsSMTP();                           // set mailer to use SMTP
        $mail->Host = EMAIL_SMTPAUTH_MAIL_SERVER;
        if (EMAIL_SMTPAUTH_MAIL_SERVER_PORT != '25' && EMAIL_SMTPAUTH_MAIL_SERVER_PORT != '') $mail->Port = EMAIL_SMTPAUTH_MAIL_SERVER_PORT;
        if (EMAIL_TRANSPORT=='smtpauth') {
          $mail->SMTPAuth = true;     // turn on SMTP authentication
          $mail->Username = (gen_not_null(EMAIL_SMTPAUTH_MAILBOX)) ? EMAIL_SMTPAUTH_MAILBOX : EMAIL_FROM;  // SMTP username
          $mail->Password = EMAIL_SMTPAUTH_PASSWORD; // SMTP password
        }
      }
      $mail->Subject  = $email_subject;
      $mail->From     = $from_email_address;
      $mail->FromName = $from_email_name;
      $mail->AddAddress($to_email_address, $to_name);
      $mail->AddReplyTo($from_email_address, $from_email_name);
	  if (isset($block['EMAIL_CC_ADDRESS'])) $mail->AddCC($block['EMAIL_CC_ADDRESS'], $block['EMAIL_CC_NAME']);
      // set proper line-endings based on switch ... important for windows vs linux hosts:
      $mail->LE = (EMAIL_LINEFEED == 'CRLF') ? "\r\n" : "\n";
      $mail->WordWrap = 76;    // set word wrap to 76 characters
      // if mailserver requires that all outgoing mail must go "from" an email address matching domain on server, set it to store address
      if (EMAIL_TRANSPORT=='sendmail-f' || EMAIL_TRANSPORT=='sendmail') {
	    $mail->Mailer = 'sendmail';
        $mail->Sender = $mail->From;
      }
      // process attachments
      // Note: $attachments_list array requires that the 'file' portion contains the full path to the file to be attached
      if (EMAIL_ATTACHMENTS_ENABLED && gen_not_null($attachments_list) ) {
        $mail->AddAttachment($attachments_list['file']);          // add attachments
      } //endif attachments
      if (EMAIL_USE_HTML && trim($email_html) != '' && ADMIN_EXTRA_EMAIL_FORMAT == 'HTML') {
        $mail->IsHTML(true);           // set email format to HTML
        $mail->Body    = $email_html;  // HTML-content of message
        $mail->AltBody = $text;        // text-only content of message
      }  else {                        // use only text portion if not HTML-formatted
        $mail->Body    = $text;        // text-only content of message
      }
      if (!$mail->Send()) {
        $messageStack->add(sprintf(EMAIL_SEND_FAILED . '&nbsp;'. $mail->ErrorInfo, $to_name, $to_email_address, $email_subject),'error');
        return false;
	  }else{
	  	$temp = $db->Execute("select address_id, ref_id from " . TABLE_ADDRESS_BOOK . " where email ='".$to_email_address."' and ref_id <> 0");
		$sql_data_array['address_id_from'] 	= $temp->fields['address_id'];
		$ref_id = $temp->fields['ref_id'];
		$temp = $db->Execute("select address_id, ref_id from " . TABLE_ADDRESS_BOOK . " where email ='".$from_email_address."'");
		$sql_data_array['address_id_to'] 	= $temp->fields['address_id'];
		$sql_data_array['Message'] 		= $text;
		$sql_data_array['Message_html']	= $email_html;
		//$sql_data_array['IDEmail'] 		= $email['message_id'];?? Rene Unknown
		$sql_data_array['EmailFrom']	= $from_email_address;
		$sql_data_array['EmailFromP']	= $from_email_name;
		$sql_data_array['EmailTo']		= $to_name;
		$sql_data_array['Account']		= $from_email_address;
		$sql_data_array['DateE']		= date("Y-m-d H:i:s");
		$sql_data_array['DateDb'] 		= date("Y-m-d H:i:s");
		$sql_data_array['Subject']		= $email_subject;
		//$sql_data_array['MsgSize'] 		= $email["SIZE"];?? Rene Unknown
// 		if(db_table_exists(TABLE_PHREEMAIL)) db_perform(TABLE_PHREEMAIL, $sql_data_array, 'insert');  		
  		// save in crm_notes
		$temp = $db->Execute("select account_id from " . TABLE_USERS . " where admin_email = '" . $from_email_address . "'");
		$sql_array['contact_id'] = $ref_id;
		$sql_array['log_date']   = $sql_data_array['DateE'];
		$sql_array['entered_by'] = $temp->fields['account_id'];
		$sql_array['action']     = 'mail_out';
		$sql_array['notes']      = $email_subject;
		db_perform(TABLE_CONTACTS_LOG, $sql_array, 'insert');
	  }
    } // end foreach loop thru possible multiple email addresses
    return true;
  }  // end function

  function web_connected($silent = true) {
    global $messageStack;
    $web_enabled = false; 
    $connected = @fsockopen('www.google.com', 80, $errno, $errstr, 20);
    if ($connected) { 
      $web_enabled = true; 
      fclose($connected); 
    } else {
	  if (!$silent) $messageStack->add('You are not connected to the internet. Error:' . $errno . ' - ' . $errstr, 'error');
	}
    return $web_enabled;   
  }

/**************************************************************************************************************/
// Section 7. Password Functions
/**************************************************************************************************************/
function pw_validate_password($plain, $encrypted) {
  if (gen_not_null($plain) && gen_not_null($encrypted)) {
// split apart the hash / salt
    $stack = explode(':', $encrypted);
    if (sizeof($stack) != 2) return false;
    if (md5($stack[1] . $plain) == $stack[0]) {
      return true;
    }
  }
  return false;
}

function pw_validate_encrypt($plain) {
  global $db;
  if (gen_not_null($plain)) {
    $sql = "select configuration_value from " . TABLE_CONFIGURATION . " where configuration_key = 'ENCRYPTION_VALUE'";
    $result = $db->Execute($sql);
    $encrypted = $result->fields['configuration_value'];
    $stack = explode(':', $encrypted);
    if (sizeof($stack) != 2) return false;
    if (md5($stack[1] . $plain) == $stack[0]) return true;
  }
  return false;
}

function pw_encrypt_password($plain) {
  $password = '';
  for ($i=0; $i<10; $i++) {
    $password .= general_rand();
  }
  $salt = substr(md5($password), 0, 2);
  $password = md5($salt . $plain) . ':' . $salt;
  return $password;
}

function pw_create_random_value($length = 12) {
  $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz1234567890';
  $chars_length = (strlen($chars) - 1);
  $string = $chars{rand(0, $chars_length)};
  for ($i = 1; $i < $length; $i = strlen($string)) {
	$r = $chars{rand(0, $chars_length)};
	if ($r != $string{$i - 1}) $string .=  $r;
  }
  return $string;
}

/**************************************************************************************************************/
// Section 8. Conversion Functions
/**************************************************************************************************************/
function createXmlHeader($type = '') {
	header("Content-Type: text/xml");
	if (!defined("CHARSET")) define("CHARSET", "UTF-8");
	$str = "<?xml version=\"1.0\" encoding=\"" . CHARSET . "\" standalone=\"yes\"?>\n";
	$str .= "<data>\n";
	return $str;
}

function createXmlFooter() {
	return "</data>\n";
}

//encases the data in its xml tags and CDATA declaration
function xmlEntry($key, $data, $ignore = NULL) {
	$str = "\t<" . $key . ">";
	if ($data != NULL) {
		//convert our db data to the proper encoding if able
		if (defined("DB_CHARSET") && defined("CHARSET")) $data = charConv($data, DB_CHARSET, CHARSET);
		if ($ignore) $str .= $data;
		else $str .= "<![CDATA[" . $data . "]]>";
	}
	$str .= "</" . $key . ">\n";
	return $str;
}

function xml_to_object($xml = '') {
  global $messageStack;
  $xml     = trim($xml);
  if ($xml == '') return '';
  $output  = new objectInfo();
  $runaway = 0;
  while (strlen($xml) > 0) {
	if (strpos($xml, '<?xml') === 0) { // header xml, ignore
	  $xml = trim(substr($xml, strpos($xml, '>') + 1));
	} elseif (strpos($xml, '</') === 0) { // ending tag, should not happen
	  $xml = trim(substr($xml, strpos($xml, '>') + 1));
	} elseif (substr($xml, 0, 3) == '<![') { // it's data, clean up and return
	  return substr($xml, strpos($xml, '[CDATA[') + 7, strrpos($xml, ']]') - strpos($xml, '[CDATA[') - 7);
	} elseif (substr($xml, 0, 1) == '<') { // beginning tag, process
	  $tag = substr($xml, 1, strpos($xml, '>') - 1);
	  $attr = array();
	  if (substr($tag, -1) == '/') { // the tag is self closing
	    $selfclose = true;
		$tag       = substr($xml, 1, strpos($xml, '>') - 2);
		$end_tag   = '<' . $tag . '/>';
		$taglen    = strlen($tag) + 3;
	  } else {
	    $selfclose = false;
	    $end_tag   = '</' . $tag . '>';
	    $taglen    = strlen($tag) + 2;
	  }
	  if (strpos($tag, ' ') !== false) { // there are tag properites
		$new_tag = substr($tag, 0, strpos($tag, ' '));
		$end_tag = $selfclose ? ('<' . $tag . '/>') : '</' . $new_tag . '>';
		$temp = explode(' ', $tag);
		$tag = array_shift($temp);
		if (sizeof($temp) > 0) {
		  foreach ($temp as $prop) {
		    if ($prop) {
		      $oneval = explode('=', $prop);
		      $attr[$oneval[0]] = $onveal[1]; 
		    }
		  }
		}
	  }
	  // TBD, the attr array is set but how to add to output?
	  if (!$selfclose && strpos($xml, $end_tag) === false) {
	    $messageStack->add('PhreeBooks XML parse error looking for end tag: ' . $tag . ' but could not find it!','error');
	    return false;
	  }
	  while(true) {
		$runaway++;
		if ($runaway > 10000) return $messageStack->add('Runaway counter 1 reached. There is an error in the xml entry!','error');	
		$data = $selfclose ? '' : trim(substr($xml, $taglen, strpos($xml, $end_tag) - $taglen));
		if (isset($output->$tag)) {
		  if (!is_array($output->$tag)) $output->$tag = array($output->$tag);
		  array_push($output->$tag, xml_to_object($data));
		} else {
		  $output->$tag = xml_to_object($data);
		}
		$xml = trim(substr($xml, strpos($xml, $end_tag) + strlen($end_tag)));
		$next_tag = substr($xml, 1, strpos($xml, '>') - 1);
		if ($next_tag <> $tag) break;
	  }
	} else { // it's probably just plain data, return with it
	  return $xml;
	}
	$runaway++;
	if ($runaway > 10000) $messageStack->add('Runaway counter 2 reached. There is an error in the xml entry!','error');	
  }
  return $output;
}

function object_to_xml($params, $multiple = false, $multiple_key = '', $level = 0) {
  $output = NULL;
  if (!is_array($params) && !is_object($params)) return;
  foreach ($params as $key => $value) {
	$xml_key = $multiple ? $multiple_key : $key;
    if       (is_array($value)) {
	  $output .= object_to_xml($value, true, $key, $level);
    } elseif (is_object($value)) {
	  for ($i=0; $i<$level; $i++) $output .= "\t";
	  $output .= "<" . $xml_key . ">\n";
	  $output .= object_to_xml($value, '', '', $level+1);
	  for ($i=0; $i<$level; $i++) $output .= "\t";
	  $output .= "</" . $xml_key . ">\n";
	} else {
	  if ($value <> '') {
	    for ($i=0; $i<$level-1; $i++) $output .= "\t";
	    $output .= xmlEntry($xml_key, $value);
	  }
	}
  }
  return $output;
}

function array_to_object($arr = array()) {
  if (!is_array($arr)) return $arr;
  $output = new objectInfo();
  foreach ($arr as $key => $value) {
    if (is_array($value)) {
	  $output->$key = array_to_object($value);
	} else {
	  $output->$key = $value;
	}
  }
  return $output;
}

function csv_string_to_array($str = '') {
  $results = preg_split("/,(?=(?:[^\"]*\"[^\"]*\")*(?![^\"]*\"))/", trim($str));
  return preg_replace("/^\"(.*)\"$/", "$1", $results);
}

/**************************************************************************************************************/
// Section 9. Error Handling Functions
/**************************************************************************************************************/

function PhreebooksErrorHandler($errno, $errstr, $errfile, $errline, $errcontext) {
	global $messageStack;
    if (!(error_reporting() & $errno)) {
        // This error code is not included in error_reporting
        return;
    }
    $temp = '';
	if(isset($_SESSION['admin_id'])) $temp = " User: " . $_SESSION['admin_id'];
	if(isset($_SESSION['company'])) $temp .= " Company: " . $_SESSION['company'];
    switch ($errno) {
    	case E_ERROR: //1
    		$text  = date('Y-m-d H:i:s') . $temp;
    		$text .= " FATAL RUN-TIME ERROR: '$errstr' Fatal error on line $errline in file $errfile, PHP " . PHP_VERSION . " (" . PHP_OS . ") Aborting...";
    		//error_log($text, 1, "operator@example.com");
    		error_log($text . PHP_EOL, 3, DIR_FS_MY_FILES."/errors.log");
    		if ($_POST['page'] == 'ajax' || $_GET['page'] == 'ajax'){
                echo createXmlHeader() . xmlEntry('error', "Sorry! FATAL RUN-TIME ERROR. We encounterd the following error: $errstr.  and had to cancel the script") . createXmlFooter();
                die();
                break;  
            }
    		header('HTTP/1.1 500 Internal Server Error'); 
    		die("<h1>Sorry! 1 FATAL RUN-TIME ERROR</h1> <p>We encounterd the following error:<br/> $errstr. <br/><br/> and had to cancel the script.</p>");
	        break;
    	case E_WARNING: //2
    		$text  = date('Y-m-d H:i:s') . $temp;
    		$text .= " RUN-TIME WARNING: '$errstr' line $errline in file $errfile";
    		error_log($text . PHP_EOL, 3, DIR_FS_MY_FILES."/errors.log");
        	break;
    	case E_PARSE: //4
        	$text  = date('Y-m-d H:i:s') . $temp;
    		$text .= " COMPILE-TIME PARSE ERROR: '$errstr' error on line $errline in file $errfile";
    		error_log($text . PHP_EOL, 3, DIR_FS_MY_FILES."/errors.log");
        	break;
        case E_NOTICE: //8
        	$text  = date('Y-m-d H:i:s') . $temp;
    		$text .= " RUN-TIME NOTICE:  '$errstr' line $errline in file $errfile";
    		if(!defined('DEBUG') || DEBUG == true) error_log($text . PHP_EOL, 3, DIR_FS_MY_FILES."/errors.log");
        	break;
        case E_CORE_ERROR: //16
        	$text  = date('Y-m-d H:i:s') . $temp;
    		$text .= " FATAL ERROR THAT OCCURED DURING PHP's INITIAL STARTUP: '$errstr' Fatal error on line $errline in file $errfile, PHP " . PHP_VERSION . " (" . PHP_OS . ") Aborting...";
    		//error_log($text, 1, "operator@example.com");
    		error_log($text . PHP_EOL, 3, DIR_FS_MY_FILES."/errors.log");
    		header('HTTP/1.1 500 Internal Server Error');
    		die("<h1>Sorry! 16 FATAL ERROR THAT OCCURED DURING PHP's INITIAL STARTUP</h1> <p>We encounterd the following error:<br/> $errstr. <br/><br/> and had to cancel the script.</p>");
	        break;
        case E_CORE_WARNING: //32
        	$text  = date('Y-m-d H:i:s') . $temp;
    		$text .= " WARNING THAT OCCURED DURING PHP's INITIAL STARTUP: '$errstr' line $errline in file $errfile";
    		error_log($text . PHP_EOL, 3, DIR_FS_MY_FILES."/errors.log");
        	break;
        case E_COMPILE_ERROR://64
        	$text  = date('Y-m-d H:i:s') . $temp;
    		$text .= " FATAL COMPILE-TIME ERROR: '$errstr' Fatal error on line $errline in file $errfile, PHP " . PHP_VERSION . " (" . PHP_OS . ") Aborting...";
    		//error_log($text, 1, "operator@example.com");
    		error_log($text . PHP_EOL, 3, DIR_FS_MY_FILES."/errors.log");
    		if ($_POST['page'] == 'ajax' || $_GET['page'] == 'ajax'){
                echo createXmlHeader() . xmlEntry('error', "Sorry! FATAL COMPILE-TIME ERROR. We encounterd the following error: $errstr.  and had to cancel the script") . createXmlFooter();
                die();
                break;  
            }
    		header('HTTP/1.1 500 Internal Server Error');
    		die("<h1>Sorry! 64 FATAL COMPILE-TIME ERROR</h1> <p>We encounterd the following error:<br/> $errstr. <br/><br/> and had to cancel the script.</p>");
	        break;
        case E_COMPILE_WARNING: //128
        	$text  = date('Y-m-d H:i:s') . $temp;
    		$text .= " COMPILE-TIME WARNING: '$errstr' line $errline in file $errfile";
    		error_log($text . PHP_EOL, 3, DIR_FS_MY_FILES."/errors.log");
        	break;
    	case E_USER_ERROR: //256
    		$text  = date('Y-m-d H:i:s') . $temp;
    		$text .= " USER ERROR: '$errstr' Fatal error on line $errline in file $errfile, PHP " . PHP_VERSION . " (" . PHP_OS . ") Aborting...";
    		error_log($text . PHP_EOL, 3, DIR_FS_MY_FILES."/errors.log");
    		//error_log($text, 1, "operator@example.com");
    		if ($_POST['page'] == 'ajax' || $_GET['page'] == 'ajax'){
                echo createXmlHeader() . xmlEntry('error', "Sorry! User Error. We encounterd the following error: $errstr.  and had to cancel the script") . createXmlFooter();
                die();
                break;  
            }
    		$messageStack->add($errstr, 'error');
    		//header('HTTP/1.1 500 Internal Server Error');
    		//die("<h1>Sorry! 256 User Error</h1> <p>We encounterd the following error:<br/> $errstr. <br/><br/> and had to cancel the script.</p>");
	        break;
    	case E_USER_WARNING: //512
    		$text  = date('Y-m-d H:i:s') . $temp;
    		$text .= " USER WARNING: '$errstr' line $errline in file $errfile";
    		error_log($text . PHP_EOL, 3, DIR_FS_MY_FILES."/errors.log");
        	break;
    	case E_USER_NOTICE: //1024
    		$text  = date('Y-m-d H:i:s') . $temp;
    		$text .= " USER NOTICE:  '$errstr' line $errline in file $errfile";
    		error_log($text . PHP_EOL, 3, DIR_FS_MY_FILES."/errors.log");
        	break;
    	case E_RECOVERABLE_ERROR : //4096
    		$text  = date('Y-m-d H:i:s') . $temp;
    		$text .= " RECOVERABLE ERROR:  '$errstr' error on line $errline in file $errfile";
    		error_log($text . PHP_EOL, 3, DIR_FS_MY_FILES."/errors.log");
        	break;
        case E_DEPRECATED : //4096
    		$text  = "PLEASE REPORT THIS TO THE DEV TEAM ".date('Y-m-d H:i:s') . $temp;
    		$text .= " DEPRECATED FUNCTION:  '$errstr' line $errline in file $errfile";
    		error_log($text . PHP_EOL, 3, DIR_FS_MY_FILES."/errors.log");
        	break;
        case E_USER_DEPRECATED : //16384 	
    		$text  = "PLEASE REPORT THIS TO THE DEV TEAM ".date('Y-m-d H:i:s') . $temp;
    		$text .= " USER DEPRECATED FUNCTION:  '$errstr' line $errline in file $errfile";
    		error_log($text . PHP_EOL, 3, DIR_FS_MY_FILES."/errors.log");
        	break; 	
        default:
	    	$text  = date('Y-m-d H:i:s') . $temp;
	    	$text .=  " Unknown error type: [$errno] '$errstr' error on line $errline in file $errfile";
	    	error_log($text . PHP_EOL, 3, DIR_FS_MY_FILES."/errors.log");
        	break;
    }
    /* Don't execute PHP internal error handler */
    return true;
}

function PhreebooksExceptionHandler($exception) {
	global $messageStack;
	if ($_POST['page'] == 'ajax' || $_GET['page'] == 'ajax'){
    	echo createXmlHeader() . xmlEntry('error', "Exception: " . $exception->getMessage()) . createXmlFooter();
        die();
    }
    $messageStack->add($exception->getMessage(), 'error');
  	$text  = date('Y-m-d H:i:s') . " User: " . $_SESSION['admin_id'] . " Company: " . $_SESSION['company'] ;
    $text .= " Exception: '" . $exception->getMessage() . "' line " . $exception->getLine() . " in file " . $exception->getFile();
    if(DEBUG) error_log($text . PHP_EOL, 3, DIR_FS_MY_FILES."/errors.log");
}

function Phreebooks_autoloader($class){
	if(class_exists($class)) print("class is geladen $class");
	$class = str_replace("\\", "/", $class);
	$path = explode("/", $class, 2);
	if($path[0] == 'core'){
		print(DIR_FS_ADMIN."includes/classes/$path[1].php<br/>");
		if (file_exists(DIR_FS_ADMIN."includes/classes/$path[1].php"))
		require_once DIR_FS_ADMIN."includes/classes/$path[1].php";	
	}else{
		if (file_exists(DIR_FS_ADMIN."modules/$path[0]/custom/classes/$path[1].php")){
			print((DIR_FS_ADMIN."modules/$path[0]/custom/classes/$path[1].php<br/>"));
			require_once DIR_FS_ADMIN."modules/$path[0]/custom/classes/$path[1].php";
		}else{
			print("$class<br/>");
			print((DIR_FS_ADMIN."modules/$path[0]/classes/$path[1].php<br/>"));
			require_once DIR_FS_ADMIN."modules/$path[0]/classes/$path[1].php";
		}
	}
	
}
?>