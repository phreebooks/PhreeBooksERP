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
//  Path: /modules/shipping/methods/endicia/label_mgr/pre_process.php
//
$shipping_module = 'endicia';
/**************  include page specific files    *********************/
load_specific_method('shipping', $shipping_module);
require_once(DIR_FS_WORKING . 'defaults.php');
require_once(DIR_FS_WORKING . 'functions/shipping.php');
require_once(DIR_FS_WORKING . 'classes/shipping.php');
/**************   page specific initialization  *************************/
$error      = false;
$auto_print = false;
$label_data = NULL;
$pdf_list   = array();
$sInfo      = new shipment();
$shipment   = new $shipping_module;
// override shipping package types
$shipping_defaults['package_type'] = $shipment->mailPieceShape;
/***************   Act on the action request   *************************/
switch ($_REQUEST['action']) {
  case 'label':
	// overwrite the defaults with data from the form
	reset($_POST);
	while (list($key, $value) = each($_POST)) $sInfo->$key = db_prepare_input($value);
	// generate ISO2 codes for countries (needed by Endicia and others)
	$sInfo->ship_country_code     = gen_get_country_iso_2_from_3($sInfo->ship_country_code);
	$sInfo->ship_date             = date('Y-m-d', strtotime($sInfo->ship_date));
	// read checkboxes
	$sInfo->delivery_confirmation = isset($_POST['delivery_confirmation']) ? '1' : '0';
	$sInfo->cod                   = isset($_POST['cod'])                   ? '1' : '0';
	// load package information
	$date = $sInfo->ship_date;
	$i    = 0;
	$sInfo->package = array();
	// error check
	$sInfo->package = array(
	  'weight' => $_POST['wt_1'],
	  'length' => $_POST['len_1'] ? $_POST['len_1'] : '', // SHIPPING_DEFAULT_LENGTH
	  'width'  => $_POST['wid_1'] ? $_POST['wid_1'] : '', // SHIPPING_DEFAULT_WIDTH
	  'height' => $_POST['hgt_1'] ? $_POST['hgt_1'] : '', // SHIPPING_DEFAULT_HEIGHT
	  'value'  => $_POST['ins_1'],
	);
	if (!$result = $shipment->retrieveLabel($sInfo)) $error = true;

	if (!$error) {
	  $temp = $db->Execute("select next_shipment_num from ".TABLE_CURRENT_STATUS);
	  $shipment_num = $temp->fields['next_shipment_num'];
	  $labels_array = array();
	  foreach ($result as $shipment) {
		$sql_array = array(
		  'ref_id'       => $shipment['ref_id'],
		  'shipment_id'  => $shipment_num,
		  'carrier'      => $shipping_module,
		  'method'       => $sInfo->ship_method,
		  'ship_date'    => $sInfo->ship_date . ' ' . date('h:i:s'),
		  'deliver_date' => $shipment['delivery_date'],
		  'tracking_id'  => $shipment['tracking'],
		  'cost'         => $shipment['net_cost'],
		);
		db_perform(TABLE_SHIPPING_LOG, $sql_array, 'insert');
		$labels_array[] = $shipment['tracking'];
	  }
	  $db->Execute("update ".TABLE_CURRENT_STATUS." set next_shipment_num = next_shipment_num + 1");
	  gen_add_audit_log(SHIPPING_LOG_LABEL_PRINTED, $shipment_num . '-' . $sInfo->purchase_invoice_id);
	  $file_path = SHIPPING_DEFAULT_LABEL_DIR . $shipping_module . '/' . str_replace('-', '/', $date) . '/';
	  // fetch the tracking labels
	  foreach ($labels_array as $tracking_num) {
	    foreach (glob($file_path . $tracking_num . '*.*') as $filename) {
	      if (substr($filename, -3) == 'lpt') { // it's a thermal label
		    if (!$handle = fopen($filename, 'r')) $error = $messageStack->add('Cannot open file (' . $filename . ')','error');
		    $label_data .= base64_encode(fread($handle, filesize($filename)));
		    fclose($handle);
		    if (!$error) $auto_print = true;
	      } elseif (substr($filename, -3) == 'pdf') { // it's a pdf image label
		    $pdf_list[] = $tracking_num; // it's a pdf image label
	      }
	    }
	    if (!$auto_print) { // just pdf, go there now
	      gen_redirect(html_href_link(FILENAME_DEFAULT, 'module=shipping&page=popup_label_viewer&method=' . $shipping_module . '&date=' . $sInfo->ship_date . '&labels=' . implode(':', $labels_array), 'SSL'));	
	    }
	  }
//	  $label_data = str_replace("\r", "", addslashes($label_data)); // for javascript multi-line
//	  $label_data = str_replace("\n", "\\n", $label_data);
	} else {
	  $messageStack->add(SHIPPING_NO_PACKAGES,'error');
	  $sInfo->ship_country_code = gen_get_country_iso_3_from_2($sInfo->ship_country_code);
	}
	break;

  case 'view':
	$date         = $_GET['date'];
	$labels       = $_GET['labels'];
	$labels_array = explode(':', $labels);
	if (count($labels_array) == 0) die('No labels were passed to label_viewer.php!');
	$file_path = SHIPPING_DEFAULT_LABEL_DIR . $shipping_module . '/' . str_replace('-', '/', $date) . '/';
	// fetch the tracking labels
	foreach ($labels_array as $tracking_num) {
	  foreach (glob($file_path . $tracking_num . '*.*') as $filename) {
	    if (substr($filename, -3) == 'lpt') { // it's a thermal label
		  if (!$handle = fopen($filename, 'r')) $error = $messageStack->add('Cannot open file (' . $filename . ')','error');
		  $label_data .= base64_encode(fread($handle, filesize($filename)));
		  fclose($handle);
		  $auto_print = true;
	    } elseif (substr($filename, -3) == 'pdf') { // it's a pdf image label
		  $pdf_list[] = $tracking_num;
	    }
	  }
	}
//	$label_data = str_replace("\r", "", addslashes($label_data)); // for javascript multi-line
//	$label_data = str_replace("\n", "\\n", $label_data);
	if (!$auto_print) { // just pdf, go there now
	  gen_redirect(html_href_link(FILENAME_DEFAULT, 'module=shipping&page=popup_label_viewer&method=' . $shipping_module . '&date=' . $date . '&labels=' . $labels, 'SSL'));	
	}
    break;

  case 'delete':
	$shipment_id = db_prepare_input($_GET['sID']);
	$shipments   = $db->Execute("select method, ship_date, tracking_id from " . TABLE_SHIPPING_LOG . " where shipment_id = " . (int)$shipment_id);
	$ship_method = $shipments->fields['method'];
	if ($shipments->RecordCount() == 0 || !$ship_method) {
	  $error = $messageStack->add(SHIPPING_DELETE_ERROR,'error');
	  break;
	}
	if ($shipments->fields['ship_date'] < date('Y-m-d')) { // only allow delete if shipped today or in future
	  $error = $messageStack->add(SHIPPING_CANNOT_DELETE,'error');
	  break;
	}
	while (!$shipments->EOF) {
	  $tracking_number = $shipments->fields['tracking_id'];
	  $shipment->deleteLabel($tracking_number);
	  // delete the label file
	  $date = explode('-', substr($shipments->fields['ship_date'], 0, 10));
	  $file_path = SHIPPING_DEFAULT_LABEL_DIR.$shipping_module.'/'.$date[0].'/'.$date[1].'/'.$date[2].'/';
	  $cnt = 0;
	  while(true) {
		$filename = $file_path . $tracking_number . ($cnt > 0 ? '-'.$cnt : '') . '.lpt';
		if   (is_file($filename)) {
		  if (!unlink($filename)) $messageStack->add('Trouble removing label file (' . $filename . ')','caution');
		} else {
		  $filename = $file_path . $tracking_number . ($cnt > 0 ? '-'.$cnt : '') . '.pdf';
		  if (is_file($filename)) {
		    if (!unlink($filename)) $messageStack->add('Trouble removing label file (' . $filename . ')','caution');
		  } else {
		    break; // file does not exist, exit loop
		  }
		}
		$cnt++;
	  }
	  $shipments->MoveNext();
	}
	// delete log since deleting label from FedEx is just a courtesy
	$db->Execute("delete from " . TABLE_SHIPPING_LOG . " where shipment_id = " . $shipment_id);
	gen_add_audit_log(SHIPPING_LABEL_DELETED, $shipment_id);
	break;

  default:
	$oID = db_prepare_input($_GET['oID']);
	$sql = "select shipper_code, ship_primary_name, ship_contact, ship_address1, ship_address2, 
		ship_city_town, ship_state_province, ship_postal_code, ship_country_code, ship_telephone1, 
		ship_email, purchase_invoice_id, purch_order_id, total_amount  
		from " . TABLE_JOURNAL_MAIN . " where id = '" . (int)$oID . "'";
	$result = $db->Execute($sql);
	if (is_array($result->fields)) {
	  while (list($key, $value) = each($result->fields)) $sInfo->$key = $value;
	  $temp = explode(':', $result->fields['shipper_code']);
	  $sInfo->ship_method = $temp[1];
	}
}

/*****************   prepare to display templates  *************************/
// translate shipping terms in the carriers language, style
$shipping_methods = array();
foreach ($shipping_defaults['service_levels'] as $key => $value) {
  if (defined($shipping_module . '_' . $key)) $shipping_methods[$key] = constant($shipping_module . '_' . $key);
}

$include_header   = false;
$include_footer   = false;
$include_template = 'template_main.php';
define('PAGE_TITLE', SHIPPING_TEXT_PRINT_LABEL);

?>