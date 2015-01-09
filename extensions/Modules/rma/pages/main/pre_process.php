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
//  Path: /modules/rma/pages/main/pre_process.php
//
$security_level = \core\classes\user::validate(SECURITY_RMA_MGT);
/**************  include page specific files    *********************/
require_once(DIR_FS_WORKING . 'defaults.php');
require_once(DIR_FS_MODULES . 'inventory/defaults.php');
/**************   page specific initialization  *************************/
if(!isset($_REQUEST['list'])) $_REQUEST['list'] = 1;
$processed = false;
$cInfo     = new \core\classes\objectInfo(array());
$creation_date = isset($_POST['creation_date']) ? gen_db_date($_POST['creation_date']) : date('Y-m-d');
$receive_date  = isset($_POST['receive_date'])  ? gen_db_date($_POST['receive_date'])  : '';
$closed_date   = isset($_POST['closed_date'])   ? gen_db_date($_POST['closed_date'])   : '';
$invoice_date  = isset($_POST['invoice_date'])  ? gen_db_date($_POST['invoice_date'])  : '';
history_filter();
/***************   hook for custom actions  ***************************/
$custom_path = DIR_FS_WORKING . 'custom/pages/main/extra_actions.php';
if (file_exists($custom_path)) { include($custom_path); }
/***************   Act on the action request   *************************/
switch ($_REQUEST['action']) {
  case 'save':
  	try{
	  	\core\classes\user::validate_security($security_level, 2); // security check
		$id                  = db_prepare_input($_POST['id']);
		$rma_num             = db_prepare_input($_POST['rma_num']);
		$caller_name         = db_prepare_input($_POST['caller_name']);
		$caller_telephone1   = db_prepare_input($_POST['caller_telephone1']);
		$caller_email        = db_prepare_input($_POST['caller_email']);
		$contact_id          = db_prepare_input($_POST['contact_id']);
		$contact_name        = db_prepare_input($_POST['contact_name']);
		$entered_by          = db_prepare_input($_POST['entered_by']);
		$status              = db_prepare_input($_POST['status']);
		$purchase_invoice_id = db_prepare_input($_POST['purchase_invoice_id']);
		$purch_order_id      = db_prepare_input($_POST['purch_order_id']);
		$return_code         = db_prepare_input($_POST['return_code']);
		$caller_notes        = db_prepare_input($_POST['caller_notes']);
		$received_by         = db_prepare_input($_POST['received_by']);
		$receive_carrier     = db_prepare_input($_POST['receive_carrier']);
		$receive_tracking    = db_prepare_input($_POST['receive_tracking']);
		$receive_notes       = db_prepare_input($_POST['receive_notes']);
		$receive_details     = array();
		$close_notes         = db_prepare_input($_POST['close_notes']);
		$close_details       = array();
		if (is_array($_POST['rcv_sku'])) foreach ($_POST['rcv_sku'] as $key => $value) {
			$receive_details[] = array(
			  'qty'   => $_POST['rcv_qty'][$key],
			  'sku'   => $_POST['rcv_sku'][$key],
			  'desc'  => $_POST['rcv_desc'][$key],
			  'mfg'   => $_POST['rcv_mfg'][$key],
			  'wrnty' => $_POST['rcv_wrnty'][$key],
			);
		}

		if (is_array($_POST['dis_sku'])) foreach ($_POST['dis_sku'] as $key => $value) {
			$close_details[] = array(
			  'qty'    => $_POST['dis_qty'][$key],
			  'sku'    => $_POST['dis_sku'][$key],
			  'notes'  => $_POST['dis_notes'][$key],
			  'action' => $_POST['dis_action'][$key],
			);
		}
		$admin->DataBase->transStart();
		// Check attachments
		$result = $admin->DataBase->query("select attachments from " . TABLE_RMA . " where id = '$id'");
		$attachments = $result->fields['attachments'] ? unserialize($result->fields['attachments']) : array();
		$image_id = 0;
		while ($image_id < 100) { // up to 100 images
		  	if (isset($_POST['rm_attach_'.$image_id])) {
				@unlink(RMA_DIR_ATTACHMENTS . "rma_{$id}_{$image_id}.zip");
				unset($attachments[$image_id]);
		  	}
		  	$image_id++;
		}
		if (is_uploaded_file($_FILES['file_name']['tmp_name'])) {
		  	// find an image slot to use
		  	$image_id = 0;
		  	while (true) {
				if (!file_exists(RMA_DIR_ATTACHMENTS . 'rma_'.$id.'_'.$image_id.'.zip')) break;
				$image_id++;
		  	}
		  	saveUploadZip('file_name', RMA_DIR_ATTACHMENTS, 'rma_'.$id.'_'.$image_id.'.zip');
		  	$attachments[$image_id] = $_FILES['file_name']['name'];
		}
		// check for errors, process
		if ($status == 99 && $closed_date == '') $closed_date = date('Y-m-d');

		// write the data

		$sql_data_array = array(
		  'status'              => $status,
		  'entered_by'          => $entered_by,
		  'caller_name'         => $caller_name,
		  'caller_telephone1'   => $caller_telephone1,
		  'caller_email'        => $caller_email,
		  'contact_id'          => $contact_id,
		  'contact_name'        => $contact_name,
		  'purchase_invoice_id' => $purchase_invoice_id,
		  'purch_order_id'      => $purch_order_id,
		  'return_code'         => $return_code,
		  'caller_notes'        => $caller_notes,
		  'received_by'         => $received_by,
		  'receive_carrier'     => $receive_carrier,
		  'receive_tracking'    => $receive_tracking,
		  'receive_notes'       => $receive_notes,
		  'receive_details'	  	=> serialize($receive_details),
		  'close_notes'         => $close_notes,
		  'close_details'	    => serialize($close_details),
		  'creation_date'       => $creation_date,
		  'invoice_date'        => $invoice_date,
		  'closed_date'         => $closed_date,
		  'receive_date'        => $receive_date,
		  'attachments'         => sizeof($attachments)>0 ? serialize($attachments) : '',
		);
		if ($id) {
		    db_perform(TABLE_RMA, $sql_data_array, 'update', 'id = ' . $id);
		} else {
		    // fetch the RMA number
			$result = $admin->DataBase->query("select next_rma_num from " . TABLE_CURRENT_STATUS);
			$rma_num = $result->fields['next_rma_num'];
			$sql_data_array['rma_num'] = $rma_num;
		    $success = db_perform(TABLE_RMA, $sql_data_array, 'insert');
			if (!db_perform(TABLE_RMA, $sql_data_array, 'insert')) throw new \core\classes\userException( "unable to add to database");
			$id = db_insert_id();
			$next_num = string_increment($sql_data_array['rma_num']);
			$admin->DataBase->query("update " . TABLE_CURRENT_STATUS . " set next_rma_num = '" . $next_num . "'");
		}
		$admin->DataBase->transCommit();
		$messageStack->add(sprintf(TEXT_SUCCESSFULLY_ARGS, ($_POST['id'] ? TEXT_UPDATED : TEXT_ADDED ), TEXT_RMA , $rma_num), 'success');
		gen_add_audit_log(sprintf(TEXT_SUCCESSFULLY_ARGS, ($_POST['id'] ? TEXT_UPDATED : TEXT_ADDED ), TEXT_RMA , $rma_num));
  	}catch(Exception $e){
  		$admin->DataBase->transRollback();
  		$messageStack->add($e->getMessage());
  	}
	break;
  case 'edit':
	$id = db_prepare_input($_POST['rowSeq']);
	$result = $admin->DataBase->query("select * from " . TABLE_RMA . " where id = " . $id);
	$attachments     = $result->fields['attachments']     ? unserialize($result->fields['attachments'])     : array();
	$receive_details = $result->fields['receive_details'] ? unserialize($result->fields['receive_details']) : array();
	$close_details   = $result->fields['close_details']   ? unserialize($result->fields['close_details'])   : array();
	$cInfo = new \core\classes\objectInfo($result->fields);
	break;

  case 'delete':
  	\core\classes\user::validate_security($security_level, 4); // security check
	$id     = db_prepare_input($_GET['cID']);
	$result = $admin->DataBase->query("select rma_num from " . TABLE_RMA . " where id = " . $id);
	if ($result->rowCount() > 0) {
	  $admin->DataBase->query("delete from " . TABLE_RMA . " where id = " . $id);
	  foreach (glob(RMA_DIR_ATTACHMENTS."ram_$id_*.zip") as $filename) unlink($filename); // remove attachments
	  gen_add_audit_log(sprintf( TEXT_SUCCESSFULLY_ARGS, TEXT_DELETED, TEXT_RMA, $result->fields['rma_num']));
	  gen_redirect(html_href_link(FILENAME_DEFAULT, gen_get_all_get_params(array('cID', 'action')), 'SSL'));
	} else {
	  $messageStack->add(TEXT_THERE_WAS_AN_ERROR_DELETING_THE_RMA, 'error');
	}
	break;
  case 'download':
	$cID   = db_prepare_input($_POST['id']);
	$imgID = db_prepare_input($_POST['rowSeq']);
	$filename = 'rma_'.$cID.'_'.$imgID.'.zip';
	if (file_exists(RMA_DIR_ATTACHMENTS . $filename)) {
	  $backup = new \phreedom\classes\backup();
	  $backup->download(RMA_DIR_ATTACHMENTS, $filename, true);
	}
	die;
  case 'dn_attach': // download from list, assume the first document only
	$cID   = db_prepare_input($_POST['rowSeq']);
	$result = $admin->DataBase->query("select attachments from " . TABLE_RMA . " where id = " . $cID);
	$attachments = unserialize($result->fields['attachments']);
	foreach ($attachments as $key => $value) {
	  $filename = 'rma_'.$cID.'_'.$key.'.zip';
	  if (file_exists(RMA_DIR_ATTACHMENTS . $filename)) {
		$backup = new \phreedom\classes\backup();
		$backup->download(RMA_DIR_ATTACHMENTS, $filename, true);
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
$user_choices = gen_get_pull_down(TABLE_USERS, true, '1', 'admin_id', 'display_name');
// build disposition drop-dwn javascript
$js_disp_code  = 'js_disp_code = new Array(' . sizeof($action_codes) . ');' . chr(10);
$js_disp_value = 'js_disp_value = new Array(' . sizeof($action_codes) . ');' . chr(10);
$i = 0;
foreach ($action_codes as $key => $value) {
  $js_disp_code  .= 'js_disp_code[' . $i . '] = "' . $key . '";' . chr(10);
  $js_disp_value .= 'js_disp_value[' . $i . '] = "' . gen_js_encode($value) . '";' . chr(10);
	$i++;
}

$cal_create = array(
  'name'      => 'createDate',
  'form'      => 'rma',
  'fieldname' => 'creation_date',
  'imagename' => 'btn_date_1',
  'default'   => isset($cInfo->creation_date) ? gen_locale_date($cInfo->creation_date) : gen_locale_date($creation_date),
  'params'    => array('align' => 'left'),
);
$cal_rcv = array(
  'name'      => 'receiveDate',
  'form'      => 'rma',
  'fieldname' => 'receive_date',
  'imagename' => 'btn_date_1',
  'default'   => isset($cInfo->receive_date) ? gen_locale_date($cInfo->receive_date) : gen_locale_date($receive_date),
  'params'    => array('align' => 'left'),
);
$cal_close = array(
  'name'      => 'closedDate',
  'form'      => 'rma',
  'fieldname' => 'closed_date',
  'imagename' => 'btn_date_1',
  'default'   => isset($cInfo->closed_date) ? gen_locale_date($cInfo->closed_date) : gen_locale_date($closed_date),
  'params'    => array('align' => 'left'),
);
$cal_invoice = array(
  'name'      => 'invoiceDate',
  'form'      => 'rma',
  'fieldname' => 'invoice_date',
  'imagename' => 'btn_date_1',
  'default'   => isset($cInfo->invoice_date) ? gen_locale_date($cInfo->invoice_date) : gen_locale_date($invoice_date),
  'params'    => array('align' => 'left'),
);

$include_header   = true;
$include_footer   = true;

switch ($_REQUEST['action']) {
  case 'new':
    // set some defaults
    $cInfo->creation_date = date('Y-m-d');
    if (isset($_GET['name'])) { // defaults wre passed
	  $cInfo->caller_name         = urldecode($_GET['name']);
	  $cInfo->purchase_invoice_id = urldecode($_GET['invoice']);
	  $cInfo->caller_telephone1   = urldecode($_GET['phone']);
	  $cInfo->caller_email        = urldecode($_GET['email']);
	}
	$cInfo->status = '1';
  case 'edit':
  	define('PAGE_TITLE', TEXT_RETURN_MATERIAL_AUTHORIZATIONS);
    $include_template = 'template_detail.php';
    break;
  default:
    // build the list header
	$heading_array = array(
	  'rma_num'             => TEXT_RMA_ID,
	  'creation_date'       => TEXT_CREATION_DATE,
	  'caller_name'         => TEXT_CALLER_NAME,
	  'purchase_invoice_id' => TEXT_INVOICE,
	  'status'              => TEXT_STATUS,
	  'closed_date'         => TEXT_CLOSED,
	);
	$result      = html_heading_bar($heading_array);
	$list_header = $result['html_code'];
	$disp_order  = $result['disp_order'];
	// build the list for the page selected
    if (isset($_REQUEST['search_text']) && $_REQUEST['search_text'] <> '') {
      $search_fields = array('rma_num', 'purchase_invoice_id', 'caller_name', 'caller_telephone1');
	  // hook for inserting new search fields to the query criteria.
	  if (is_array($extra_search_fields)) $search_fields = array_merge($search_fields, $extra_search_fields);
	  $search = " where " . implode(" like %{$_REQUEST['search_text']}%' or ", $search_fields) . " like '%{$_REQUEST['search_text']}%";
    } else {
	  $search = '';
	}
	$field_list = array('id', 'rma_num', 'purchase_invoice_id', 'status', 'caller_name', 'creation_date', 'closed_date', 'attachments');
	// hook to add new fields to the query return results
	if (is_array($extra_query_list_fields) > 0) $field_list = array_merge($field_list, $extra_query_list_fields);
    $query_raw = "select SQL_CALC_FOUND_ROWS " . implode(', ', $field_list)  . " from " . TABLE_RMA . $search . " order by $disp_order, rma_num";
    $query_result = $admin->DataBase->query($query_raw, (MAX_DISPLAY_SEARCH_RESULTS * ($_REQUEST['list'] - 1)).", ".  MAX_DISPLAY_SEARCH_RESULTS);
    $query_split  = new \core\classes\splitPageResults($_REQUEST['list'], '');
	history_save();
    define('PAGE_TITLE', TEXT_RETURN_MATERIAL_AUTHORIZATIONS);
    $include_template = 'template_main.php';
	break;
}

?>