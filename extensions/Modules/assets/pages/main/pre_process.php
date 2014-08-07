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
//  Path: /modules/assets/pages/main/pre_process.php
//
$security_level = validate_user(SECURITY_ASSETS_MGT);
/**************  include page specific files    *********************/
require_once(DIR_FS_MODULES . 'phreebooks/functions/phreebooks.php');
require_once(DIR_FS_MODULES . 'inventory/functions/inventory.php');
require_once(DIR_FS_WORKING . 'defaults.php');
require_once(DIR_FS_WORKING . 'classes/assets_fields.php');
/**************   page specific initialization  *************************/
$error       = false;
$processed   = false;
$fields		 = new assets_fields();
$acquisition_date = isset($_POST['acquisition_date']) ? gen_db_date($_POST['acquisition_date']) : '';
$maintenance_date = isset($_POST['maintenance_date']) ? gen_db_date($_POST['maintenance_date']) : '';
$terminal_date    = isset($_POST['terminal_date'])    ? gen_db_date($_POST['terminal_date'])    : '';
history_filter('assets');
/***************   hook for custom actions  ***************************/
$custom_path = DIR_FS_WORKING . 'custom/pages/main/extra_actions.php';
if (file_exists($custom_path)) { include($custom_path); }
/***************   Act on the action request   *************************/
switch ($_REQUEST['action']) {
  case 'new':
	$asset = '';
	$cInfo = '';
	break;
  case 'create':
	if ($security_level < 2) {
		$messageStack->add(ERROR_NO_PERMISSION, 'error');
		gen_redirect(html_href_link(FILENAME_DEFAULT, gen_get_all_get_params(array('action')), 'SSL'));
		break;
	}
	$asset_id   = db_prepare_input($_POST['asset_id']);
	$asset_type = db_prepare_input($_POST['asset_type']);
	if (!$asset_id) {
		$messageStack->add(ASSETS_ERROR_SKU_BLANK, 'error');
		$_REQUEST['action'] = 'new';
		break;
	}
	$result = $db->Execute("select id from " . TABLE_ASSETS . " where asset_id = '$asset_id'");
	if ($result->RecordCount() <> 0) {
		$messageStack->add(ASSETS_ERROR_DUPLICATE_SKU, 'error');
		$_REQUEST['action'] = 'new';
		break;
	}
	$sql_data_array = array(
		'asset_id'         => $asset_id,
		'asset_type'       => $asset_type,
		'acquisition_date' => 'now()');
	switch ($asset_type) {
	  case 'vh': $_REQUEST['search_text'] = TEXT_VEHICLE;   break;
	  case 'bd': $_REQUEST['search_text'] = TEXT_BUILDING;  break;
	  case 'fn': $_REQUEST['search_text'] = TEXT_FURNITURE; break;
	  case 'pc': $_REQUEST['search_text'] = TEXT_COMPUTER;  break;
	  case 'ld': $_REQUEST['search_text'] = TEXT_LAND;      break;
	  case 'sw': $_REQUEST['search_text'] = TEXT_SOFTWARE;  break;
	}
	$sql_data_array['account_asset']        = ''; // best_acct_guess(8,$_REQUEST['search_text'],'');
	$sql_data_array['account_depreciation'] = ''; // best_acct_guess(10,$_REQUEST['search_text'],'');
	$sql_data_array['account_maintenance']  = ''; // best_acct_guess(34,$_REQUEST['search_text'],'');
	db_perform(TABLE_ASSETS, $sql_data_array, 'insert');
	$id = db_insert_id();
	gen_add_audit_log(AESSETS_LOG_ASSETS . TEXT_ADD, 'Type: ' . $asset_type . ' - ' . $asset_id);
	gen_redirect(html_href_link(FILENAME_DEFAULT, gen_get_all_get_params(array('cID', 'action')) . '&cID=' . $id . '&action=edit', 'SSL'));
	break;
  case 'delete':
	if ($security_level < 4) {
		$messageStack->add(ERROR_NO_PERMISSION,'error');
		gen_redirect(html_href_link(FILENAME_DEFAULT, gen_get_all_get_params(array('action')), 'SSL'));
		break;
	}
	$id         = db_prepare_input($_GET['cID']);
	$result     = $db->Execute("select asset_id, asset_type, image_with_path from " . TABLE_ASSETS . " where id = " . $id);
	$asset_id   = $result->fields['asset_id'];
	$asset_type = $result->fields['asset_type'];

	$db->Execute("delete from " . TABLE_ASSETS . " where id = " . $id);
	if ($image_with_path) { // delete image
	  $file_path = DIR_FS_MY_FILES . $_SESSION['company'] . '/assets/images/';
	  if (file_exists($file_path . $result->fields['image_with_path'])) unlink ($file_path . $result->fields['image_with_path']);
	}
	foreach (glob(ASSETS_DIR_ATTACHMENTS.'assets_'.$id.'_*.zip') as $filename) unlink($filename); // remove attachments
	gen_add_audit_log(AESSETS_LOG_ASSETS . TEXT_DELETE, $asset_id);
	gen_redirect(html_href_link(FILENAME_DEFAULT, gen_get_all_get_params(array('cID', 'action')), 'SSL'));
	break;
  case 'save':
	if ($security_level < 3) {
		$messageStack->add(ERROR_NO_PERMISSION,'error');
		gen_redirect(html_href_link(FILENAME_DEFAULT, gen_get_all_get_params(array('action')), 'SSL'));
		break;
	}
	$id              = db_prepare_input($_POST['id']);
	$asset_id        = db_prepare_input($_POST['asset_id']);
	$image_with_path = db_prepare_input($_POST['image_with_path']); // the current image name with path relative from my_files/company_db/asset/images directory
	$asset_path      = db_prepare_input($_POST['asset_path']);
	if (substr($asset_path, 0, 1) == '/') $asset_path = substr($asset_path, 1); // remove leading '/' if there
	if (substr($asset_path, -1, 1) == '/') $asset_path = substr($asset_path, 0, strlen($asset_path)-1); // remove trailing '/' if there
	$asset_type = db_prepare_input($_POST['asset_type']);
	$sql_data_array = array();
	$asset_fields = $db->Execute("select field_name, entry_type from " . TABLE_EXTRA_FIELDS . " where module_id = 'assets'");
	while (!$asset_fields->EOF) {
		$field_name = $asset_fields->fields['field_name'];
		if (!isset($_POST[$field_name]) && $asset_fields->fields['entry_type'] == 'check_box') {
			$sql_data_array[$field_name] = '0'; // special case for unchecked check boxes
		} elseif (isset($_POST[$field_name]) && $field_name <> 'id') {
			$sql_data_array[$field_name] = db_prepare_input($_POST[$field_name]);
		}
		if ($asset_fields->fields['entry_type'] == 'date_time') {
			$sql_data_array[$field_name] = ($sql_data_array[$field_name]) ? gen_db_date($sql_data_array[$field_name]) : '';
		}
		$asset_fields->MoveNext();
	}
	// special cases for checkboxes of system fields (don't return a POST value if unchecked)
	$remove_image = $_POST['remove_image'] == '1' ? true : false;
	unset($sql_data_array['remove_image']); // this is not a db field, just an action
	$sql_data_array['inactive']         = ($sql_data_array['inactive'] == '1' ? '1' : '0');
	$sql_data_array['purch_cond']       = db_prepare_input($_POST['purch_cond']);
	$sql_data_array['acquisition_date'] = $acquisition_date;
	$sql_data_array['maintenance_date'] = $maintenance_date;
	$sql_data_array['terminal_date']    = $terminal_date;
	// special cases for monetary values in system fields
	$sql_data_array['full_price']       = $currencies->clean_value($sql_data_array['full_price']);
	$sql_data_array['asset_cost']       = $currencies->clean_value($sql_data_array['asset_cost']);
	// Check attachments
	$result = $db->Execute("select attachments from " . TABLE_ASSETS . " where id = " . $id);
	$attachments = $result->fields['attachments'] ? unserialize($result->fields['attachments']) : array();
	$image_id = 0;
	while ($image_id < 100) { // up to 100 images
	  if (isset($_POST['rm_attach_'.$image_id])) {
		@unlink(ASSETS_DIR_ATTACHMENTS . 'assets_'.$id.'_'.$image_id.'.zip');
		unset($attachments[$image_id]);
	  }
	  $image_id++;
	}
	if (is_uploaded_file($_FILES['file_name']['tmp_name'])) {
	  // find an image slot to use
	  $image_id = 0;
	  while (true) {
		if (!file_exists(ASSETS_DIR_ATTACHMENTS . 'assets_'.$id.'_'.$image_id.'.zip')) break;
		$image_id++;
	  }
	  saveUploadZip('file_name', ASSETS_DIR_ATTACHMENTS, 'assets_'.$id.'_'.$image_id.'.zip');
	  $attachments[$image_id] = $_FILES['file_name']['name'];
	}
	$sql_data_array['attachments'] = sizeof($attachments)>0 ? serialize($attachments) : '';
	
	if ($remove_image) { // update the image with relative path
		$_POST['image_with_path'] = '';
		$sql_data_array['image_with_path'] = ''; 
	}
	if (!$error && is_uploaded_file($_FILES['asset_image']['tmp_name'])) {
		$file_path = DIR_FS_MY_FILES . $_SESSION['company'] . '/assets/images';
        $asset_path = str_replace('\\', '/', $asset_path);
		// strip beginning and trailing slashes if present
		if (substr($asset_path, -1, 1) == '/') $asset_path = substr($asset_path, 0, -1);
		if (substr($asset_path, 0, 1) == '/') $asset_path = substr($asset_path, 1);
		if ($asset_path) $file_path .= '/' . $asset_path;

		$temp_file_name = $_FILES['asset_image']['tmp_name'];
		$file_name = $_FILES['asset_image']['name'];
		if (!validate_path($file_path)) {
			$messageStack->add(ASSETS_IMAGE_PATH_ERROR, 'error');
			$error = true;
		} elseif (!validate_upload('asset_image', 'image', 'jpg')) {
			$messageStack->add(ASSETS_IMAGE_FILE_TYPE_ERROR, 'error');
			$error = true;
		} else { // passed all test, write file
			if (!copy($temp_file_name, $file_path . '/' . $file_name)) {
				$messageStack->add(ASSETS_IMAGE_FILE_WRITE_ERROR, 'error');
				$error = true;
			} else {
				$image_with_path = ($asset_path ? ($asset_path . '/') : '') . $file_name;
				$_POST['image_with_path'] = $image_with_path;
				$sql_data_array['image_with_path'] = $image_with_path; // update the image with relative path
			}
		}
	}
	// Ready to write update
	if (!$error) {
		db_perform(TABLE_ASSETS, $sql_data_array, 'update', "id = " . $id);
		gen_add_audit_log(AESSETS_LOG_ASSETS . TEXT_UPDATE, $asset_id . ' - ' . $sql_data_array['description_short']);
	} else if ($error == true) {
		$tab_list = $db->Execute("select id, tab_name, description 
		  from " . TABLE_EXTRA_TABS . " where module_id='assets' order by sort_order");
		$_POST['id'] = $id;
		$cInfo = new objectInfo($_POST);
		$processed = true;
	}
	break;
  case 'copy':
	if ($security_level < 2) {
		$messageStack->add(ERROR_NO_PERMISSION,'error');
		gen_redirect(html_href_link(FILENAME_DEFAULT, gen_get_all_get_params(array('action')), 'SSL'));
		break;
	}
	$id 		  = db_prepare_input($_GET['cID']);
	$new_asset_id = db_prepare_input($_GET['asset_id']);
	// check for duplicate skus
	$result = $db->Execute("select id from " . TABLE_ASSETS . " where asset_id = '" . $new_asset_id . "'");
	if ($result->Recordcount() > 0) {	// error and reload
		$messageStack->add(ASSETS_ERROR_DUPLICATE_SKU, 'error');
		break;
	}
	$result = $db->Execute("select * from " . TABLE_ASSETS . " where id = " . $id);
	$old_asset_key = $result->fields['asset_id'];
	// clean up the fields (especially the system fields, retain the custom fields)
	$output_array = array();
	foreach ($result->fields as $key => $value) {
		switch ($key) {
			case 'id':	// Remove from write list fields
			case 'maintenance_date':
			case 'terminal_date':
				break;
			case 'asset_id': // set the new asset_id
				$output_array[$key] = $new_asset_id;
				break;
			case 'acquisition_date':
				$output_array[$key] = date('Y-m-d H:i:s');
				break;
			default:
				$output_array[$key] = $value;
		}
	}
	db_perform(TABLE_ASSETS, $output_array, 'insert');
	$new_id = db_insert_id();
	// Pictures are not copied over...
	// now continue with newly copied item by editing it
	gen_add_audit_log(AESSETS_LOG_ASSETS . TEXT_COPY, $old_asset_key . ' => ' . $new_asset_id);
	$_POST['rowSeq'] = $new_id;	// set item pointer to new record
	$_REQUEST['action'] = 'edit'; // fall through to edit case
  case 'edit':
    $id = db_prepare_input(isset($_POST['rowSeq']) ? $_POST['rowSeq'] : $_GET['cID']);
	$sql = "select * from " . TABLE_ASSETS . " 
		where id = " . (int)$id . " order by asset_id";
	$asset = $db->Execute($sql);
	// load attachments
	$attachments = $asset->fields['attachments'] ? unserialize($asset->fields['attachments']) : array();
	$cInfo = new objectInfo($asset->fields);
	break;
  case 'rename':
	if ($security_level < 4) {
		$messageStack->add(ERROR_NO_PERMISSION,'error');
		gen_redirect(html_href_link(FILENAME_DEFAULT, gen_get_all_get_params(array('action')), 'SSL'));
		break;
	}
	$id = db_prepare_input($_GET['cID']);
	$asset_id = db_prepare_input($_GET['asset_id']);
	// check for duplicate skus
	$result = $db->Execute("select id from " . TABLE_ASSETS . " where asset_id = '" . $asset_id . "'");
	if ($result->Recordcount() > 0) {	// error and reload
		$messageStack->add(ASSETS_ERROR_DUPLICATE_SKU, 'error');
		break;
	}
	$result = $db->Execute("select asset_id, asset_type from " . TABLE_ASSETS . " where id = " . $id);
	$orig_sku = $result->fields['asset_id'];
	$asset_type = $result->fields['asset_type'];
	$sku_list = array($orig_sku);
	if ($asset_type == 'ms') { // build list of asset_id's to rename (without changing contents)
		$result = $db->Execute("select asset_id from " . TABLE_ASSETS ." where asset_id like '". $orig_sku . "-%'");
		while(!$result->EOF) {
			$sku_list[] = $result->fields['asset_id'];
			$result->MoveNext();
		}
	}
	// start transaction (needs to all work or reset to avoid unsyncing tables)
	$db->transStart();
	// rename the afffected tables
	for($i = 0; $i < count($sku_list); $i++) {
		$new_sku = str_replace($orig_sku, $asset_id, $sku_list[$i], $count = 1);
		$result = $db->Execute("update " . TABLE_ASSETS . " set asset_id = '" . $new_sku . "' where asset_id = '" . $sku_list[$i] . "'");
	}
	$db->transCommit();	// finished successfully
	break;
  case 'download':
	$cID   = db_prepare_input($_POST['id']);
	$imgID = db_prepare_input($_POST['rowSeq']);
	$filename = 'assets_'.$cID.'_'.$imgID.'.zip';
	if (file_exists(ASSETS_DIR_ATTACHMENTS . $filename)) {
		require_once(DIR_FS_MODULES . 'phreedom/classes/backup.php');
		$backup = new backup();
		$backup->download(ASSETS_DIR_ATTACHMENTS, $filename, true);
	}
	die;
  case 'dn_attach': // download from list, assume the first document only
	$cID   = db_prepare_input($_POST['rowSeq']);
	$result = $db->Execute("select attachments from " . TABLE_ASSETS . " where id = " . $cID);
	$attachments = unserialize($result->fields['attachments']);
	foreach ($attachments as $key => $value) {
	  $filename = 'assets_'.$cID.'_'.$key.'.zip';
	  if (file_exists(ASSETS_DIR_ATTACHMENTS . $filename)) {
		require_once(DIR_FS_MODULES . 'phreedom/classes/backup.php');
		$backup = new backup();
		$backup->download(ASSETS_DIR_ATTACHMENTS, $filename, true);
		die;
	  }
	}
	break;

  case 'go_first':    $_REQUEST['list'] = 1;       break;
  case 'go_previous': $_REQUEST['list'] = max($_REQUEST['list']-1, 1); break;
  case 'go_next':     $_REQUEST['list']++;         break;
  case 'go_last':     $_REQUEST['list'] = 99999;   break;
  case 'search':
  case 'search_reset':
  case 'go_page':
  default:
}

/*****************   prepare to display templates  *************************/
$gl_array_list = gen_coa_pull_down();
// load purchase condition array
$purch_cond_array = array(
	array('id' => 'n', 'text' => TEXT_NEW),
	array('id' => 'u', 'text' => TEXT_USED),
);
$include_header   = true;
$include_footer   = true;
switch ($_REQUEST['action']) {
  case 'new':
    define('PAGE_TITLE', BOX_ASSET_MODULE);
    $include_template = 'template_id.php';
    break;
  case 'edit':
	// load calendar parameters
	$cal_date1 = array(
	  'name'      => 'date1',
	  'form'      => 'assets',
	  'fieldname' => 'acquisition_date',
	  'imagename' => 'btn_date_1',
	  'default'   => isset($cInfo->acquisition_date) ? gen_locale_date($cInfo->acquisition_date) : '',
	  'params'    => array('align' => 'left'),
	);
	$cal_date2 = array(
	  'name'      => 'date2',
	  'form'      => 'assets',
	  'fieldname' => 'maintenance_date',
	  'imagename' => 'btn_date_2',
	  'default'   => isset($cInfo->maintenance_date) ? gen_locale_date($cInfo->maintenance_date) : '',
	  'params'    => array('align' => 'left'),
	);
	$cal_date3 = array(
	  'name'      => 'date3',
	  'form'      => 'assets',
	  'fieldname' => 'terminal_date',
	  'imagename' => 'btn_date_3',
	  'default'   => isset($cInfo->terminal_date) ? gen_locale_date($cInfo->terminal_date) : '',
	  'params'    => array('align' => 'left'),
	);
    define('PAGE_TITLE', BOX_ASSET_MODULE);
    $include_template = 'template_detail.php';
    break;
  default:
    // build the list header
	$heading_array = array(
	  'asset_id'          => TEXT_ASSET_ID,
	  'asset_type'        => ASSETS_ENTRY_ASSETS_TYPE,
	  'purch_cond'        => TEXT_CONDITION,
	  'serial_number'     => ASSETS_ENTRY_ASSETS_SERIALIZE,
	  'description_short' => TEXT_DESCRIPTION,
	  'acquisition_date'  => TEXT_ACQ_DATE,
	  'terminal_date'     => TEXT_RETIRE_DATE,
	);
	$result      = html_heading_bar($heading_array);
	$list_header = $result['html_code'];
	$disp_order  = $result['disp_order'];
	// build the list for the page selected
    if (isset($_REQUEST['search_text']) && $_REQUEST['search_text'] <> '') {
      $search_fields = array('asset_id', 'serial_number', 'description_short', 'description_long');
	  // hook for inserting new search fields to the query criteria.
	  if (is_array($extra_search_fields)) $search_fields = array_merge($search_fields, $extra_search_fields);
	  $search = ' where ' . implode(' like \'%' . $_REQUEST['search_text'] . '%\' or ', $search_fields) . ' like \'%' . $_REQUEST['search_text'] . '%\'';
    } else {
	  $search = '';
	}
	$field_list = array('id', 'asset_id', 'asset_type', 'purch_cond', 'inactive', 'serial_number', 
		'description_short', 'acquisition_date', 'terminal_date', 'attachments');
	// hook to add new fields to the query return results
	if (is_array($extra_query_list_fields) > 0) $field_list = array_merge($field_list, $extra_query_list_fields);

    $query_raw    = "select SQL_CALC_FOUND_ROWS ".implode(', ', $field_list)." from ".TABLE_ASSETS." $search order by $disp_order, asset_id";
    $query_result = $db->Execute($query_raw, (MAX_DISPLAY_SEARCH_RESULTS * ($_REQUEST['list'] - 1)).", ".  MAX_DISPLAY_SEARCH_RESULTS);
    $query_split  = new splitPageResults($_REQUEST['list'], '');
    if ($query_split->current_page_number <> $_REQUEST['list']) { // if here, go last was selected, now we know # pages, requery to get results
    	$_REQUEST['list'] = $query_split->current_page_number;
	    $query_result = $db->Execute($query_raw, (MAX_DISPLAY_SEARCH_RESULTS * ($_REQUEST['list'] - 1)).", ".  MAX_DISPLAY_SEARCH_RESULTS);
	    $query_split  = new splitPageResults($_REQUEST['list'], '');
    }
    history_save('assets');
    
	define('PAGE_TITLE', BOX_ASSET_MODULE);
    $include_template = 'template_main.php';
	break;
}

?>