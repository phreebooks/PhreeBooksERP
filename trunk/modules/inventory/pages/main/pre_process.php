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
//  Path: /modules/inventory/pages/main/pre_process.php
//
$security_level = \core\classes\user::validate(SECURITY_ID_MAINTAIN_INVENTORY);
/**************  include page specific files    *********************/
require_once(DIR_FS_WORKING . 'defaults.php');
require_once(DIR_FS_MODULES . 'phreebooks/functions/phreebooks.php');
require_once(DIR_FS_WORKING . 'functions/inventory.php');
/**************   page specific initialization  *************************/
gen_pull_language('inventory','filter');
$processed   = false;
$criteria    = array();
$fields		 = new \inventory\classes\fields();
$type        = isset($_REQUEST['inventory_type']) ? $_REQUEST['inventory_type'] : null; // default to stock item
history_filter('inventory');
$first_entry = isset($_GET['add']) ? true : false;
// load the filters
$f0 = $_GET['f0'] = isset($_POST['action']) ? (isset($_POST['f0']) ? '1' : '0') : $_GET['f0']; // show inactive checkbox
$f1 = $_GET['f1'] = isset($_POST['f1']) ? $_POST['f1'] : $_GET['f1']; // inventory_type dropdown
$id = isset($_POST['rowSeq']) ? db_prepare_input($_POST['rowSeq']) : db_prepare_input($_GET['cID']);
// getting the right inventory type.
if (!isset($_REQUEST['inventory_type'])){
	if(isset($_REQUEST['cID'])) $result = $db->Execute("SELECT inventory_type FROM ".TABLE_INVENTORY." WHERE id='{$_REQUEST['cID']}'");
	else if (isset($_REQUEST['rowSeq'])) $result = $db->Execute("SELECT inventory_type FROM ".TABLE_INVENTORY." WHERE id='{$_REQUEST['rowSeq']}'");
	else $result = $db->Execute("SELECT inventory_type FROM ".TABLE_INVENTORY." WHERE sku='{$_REQUEST['sku']}'");
	if ($result->RecordCount()>0) $type = $result->fields['inventory_type'];
	else $type ='si';
} 
$temp = '\inventory\classes\type\\'. $type;
$cInfo = new $temp;
/***************   hook for custom actions  ***************************/
$custom_path = DIR_FS_WORKING . 'custom/pages/main/extra_actions.php';
if (file_exists($custom_path)) { include($custom_path); }
/***************   Act on the action request   *************************/
switch ($_REQUEST['action']) {
  case 'create':
  	try{
		\core\classes\user::validate_security($security_level, 2); // security check
		$cInfo->check_create_new();
		$_REQUEST['action'] = 'edit';
  	}catch (Exception $e){
  		$messageStack->add($e->getMessage());
  		$_REQUEST['action'] = 'new';
  	}
	break;
	
  case 'save':
  	try{
		\core\classes\user::validate_security($security_level, 2); // security check
		$cInfo->save();
  	}catch(Exception $e){
  		$messageStack->add($e->getMessage());
		$_REQUEST['action'] = 'edit';
  	}
	break;

  case 'delete':
	\core\classes\user::validate_security($security_level, 4); // security check
	$id = db_prepare_input($_GET['cID']);
	$cInfo->check_remove($id);
	break;

  case 'copy': 	// Pictures are not copied over...
	\core\classes\user::validate_security($security_level, 2); // security check
	$id  = db_prepare_input($_GET['cID']);
	$sku = db_prepare_input($_GET['sku']);
	$cInfo->copy($id, $sku);
	$_REQUEST['action'] = 'edit';
	break;
	
  case 'edit':
  case 'properties':
	if($id != ''){
		$cInfo->get_item_by_id($id);
	}else if(isset($_REQUEST['sku'])){
		$cInfo->get_item_by_sku($_REQUEST['sku']);
	}
	break;

  case 'rename':
	\core\classes\user::validate_security($security_level, 4); // security check
	$id  = db_prepare_input($_GET['cID']);
	$sku = db_prepare_input($_GET['sku']);
	$cInfo->rename($id, $sku);
	break;
  case 'download':
   	    $cID   = db_prepare_input($_POST['id']);
  	    $imgID = db_prepare_input($_POST['rowSeq']);
	    $filename = 'inventory_'.$cID.'_'.$imgID.'.zip';
	    if (file_exists(INVENTORY_DIR_ATTACHMENTS . $filename)) {
	       $backup = new \phreedom\classes\backup();
	       $backup->download(INVENTORY_DIR_ATTACHMENTS, $filename, true);
	    }
	    ob_end_flush();
  		session_write_close();
        die;
  case 'dn_attach': // download from list, assume the first document only
        $cID   = db_prepare_input($_POST['rowSeq']);
  	    $result = $db->Execute("select attachments from ".TABLE_INVENTORY." where id = $cID");
  	    $attachments = unserialize($result->fields['attachments']);
  	    foreach ($attachments as $key => $value) {
		   	$filename = 'inventory_'.$cID.'_'.$key.'.zip';
		   	if (file_exists(INVENTORY_DIR_ATTACHMENTS . $filename)) {
		      	require_once(DIR_FS_MODULES . 'phreedom/classes/backup.php');
		      	$backup = new \phreedom\classes\backup();
		      	$backup->download(INVENTORY_DIR_ATTACHMENTS, $filename, true);
		      	ob_end_flush();
  				session_write_close();
		      	die;
		   	}
  	    }
  case 'reset':
  		$_SESSION['filter_field']	 = null; 
  		$_REQUEST['filter_field']	 = null;
  		$_SESSION['filter_criteria'] = null; 
  		$_REQUEST['filter_criteria'] = null;
  		$_SESSION['filter_value'] 	 = null; 
  		$_REQUEST['filter_value'] 	 = null;
		break;
  case 'go_first':    $_REQUEST['list'] = 1;       break;
  case 'go_previous': $_REQUEST['list'] = max($_REQUEST['list']-1, 1); break;
  case 'go_next':     $_REQUEST['list']++;         break;
  case 'go_last':     $_REQUEST['list'] = 99999;   break;
  case 'search':
  case 'search_reset':
  case 'go_page':
  case 'new':
  default:
}

/*****************   prepare to display templates  *************************/
// build the type filter list
$type_select_list = array( // add some extra options
  array('id' => '0',   'text' => TEXT_ALL),
  array('id' => 'cog', 'text' => TEXT_INV_MANAGED),
);

foreach ($inventory_types_plus as $key => $value) $type_select_list[] = array('id' => $key,  'text' => $value);
// generate the vendors and fill js arrays for dynamic pull downs
$vendors = gen_get_contact_array_by_type('v');
$js_vendor_array = 'var js_vendor_array = new Array();' . chr(10);
for ($i = 0; $i < count($vendors); $i++) {
  $js_vendor_array .= 'js_vendor_array[' . $i . '] = new dropDownData("' . $vendors[$i]['id'] . '", "' . $vendors[$i]['text'] . '");' . chr(10);
}
// generate the pricesheets and fill js arrays for dynamic pull downs
$pur_pricesheets = get_price_sheet_data('v');
$js_pricesheet_array = 'var js_pricesheet_array = new Array();' . chr(10);
for ($i = 0; $i < count($pur_pricesheets); $i++) {
  $js_pricesheet_array .= 'js_pricesheet_array[' . $i . '] = new dropDownData("' . $pur_pricesheets[$i]['id'] . '", "' . $pur_pricesheets[$i]['text'] . '");' . chr(10);
}

// load the tax rates
$tax_rates        = ord_calculate_tax_drop_down('c');
$purch_tax_rates  = inv_calculate_tax_drop_down('v',false);
// generate a rate array parallel to the drop down for javascript
$js_tax_rates = 'var tax_rates = new Array(' . count($tax_rates) . ');' . chr(10);
for ($i = 0; $i < count($tax_rates); $i++) {
  $js_tax_rates .= 'tax_rates[' . $i . '] = new tax("' . $tax_rates[$i]['id'] . '", "' . $tax_rates[$i]['text'] . '", "' . $tax_rates[$i]['rate'] . '");' . chr(10);
}

// load gl accounts
$gl_array_list    = gen_coa_pull_down();
$include_header   = true;
$include_footer   = true;

switch ($_REQUEST['action']) {
  case 'new':
    define('PAGE_TITLE', BOX_INV_NEW);
    $include_template = 'template_id.php';
	break;
  case 'create':
  case 'edit':
    define('PAGE_TITLE', BOX_INV_MAINTAIN);
    $include_template = 'template_detail.php';
    break;
  case 'properties':
    define('PAGE_TITLE', BOX_INV_MAINTAIN);
	$include_header   = false;
	$include_footer   = false;
    $include_template = 'template_detail.php';
    break;
  default:
  	//building filter criteria
  	$_SESSION['filter_field'] 	 = isset( $_REQUEST['filter_field']) 	?  $_REQUEST['filter_field'] : $_SESSION['filter_field'];
  	$_SESSION['filter_criteria'] = isset( $_REQUEST['filter_criteria']) ?  $_REQUEST['filter_criteria'] : $_SESSION['filter_criteria'];
  	$_SESSION['filter_value'] 	 = isset( $_REQUEST['filter_value']) 	?  $_REQUEST['filter_value'] : $_SESSION['filter_value'];
  	$filter_criteria = Array(" = "," != "," LIKE "," NOT LIKE "," > "," < ");
	$x = 0;
	while (isset($_SESSION['filter_field'][$x])) {
		if(      $filter_criteria[$_SESSION['filter_criteria'][$x]] == " LIKE " || $_SESSION['filter_criteria'][$x] == FILTER_CONTAINS){
			if ( $_SESSION['filter_value'][$x] <> '' ) $criteria[] = $_SESSION['filter_field'][$x] . ' Like "%'    . $_SESSION['filter_value'][$x] . '%" ';
			
		}elseif( $filter_criteria[$_SESSION['filter_criteria'][$x]] == " NOT LIKE "){
			if ( $_SESSION['filter_value'][$x] <> '' ) $criteria[] = $_SESSION['filter_field'][$x] . ' Not Like "%' . $_SESSION['filter_value'][$x] . '%" ';
			
		}elseif( $filter_criteria[$_SESSION['filter_criteria'][$x]] == " = "  && $_SESSION['filter_value'][$x] == ''){
			if ( $_SESSION['filter_field'][$x] == 'a.sku' && $_SESSION['filter_value'][$x] == '' ) { $x++; continue; }
			$criteria[] = '(' . $_SESSION['filter_field'][$x] . $filter_criteria[$_SESSION['filter_criteria'][$x]] . ' "' . $_SESSION['filter_value'][$x] . '" or ' . $_SESSION['filter_field'][$x] . ' IS NULL ) ';
			
		}elseif( $filter_criteria[$_SESSION['filter_criteria'][$x]] == " != " && $_SESSION['filter_value'][$x] == ''){
			$criteria[] = '(' . $_SESSION['filter_field'][$x] . $filter_criteria[$_SESSION['filter_criteria'][$x]] . ' "' . $_SESSION['filter_value'][$x] . '" or ' . $_SESSION['filter_field'][$x] . ' IS NOT NULL ) ';
			
		}else{	
			$criteria[] = $_SESSION['filter_field'][$x] . $filter_criteria[$_SESSION['filter_criteria'][$x]]. ' "' . $_SESSION['filter_value'][$x] . '" ';
		}		
		$x++;
	}	
  	
    // build the list header
	$heading_array = array(
	  'a.sku'                     => TEXT_SKU,
	  'a.inactive'                => TEXT_INACTIVE,
	  'a.description_short'       => TEXT_DESCRIPTION,
	  'a.quantity_on_hand'        => INV_HEADING_QTY_ON_HAND,
	  'a.quantity_on_sales_order' => INV_HEADING_QTY_ON_SO,
	  'a.quantity_on_allocation'  => INV_HEADING_QTY_ON_ALLOC,
	  'a.quantity_on_order'       => INV_HEADING_QTY_ON_ORDER,
	);
	$result      = html_heading_bar($heading_array);
	$list_header = $result['html_code'];
	$disp_order  = $result['disp_order'];
//	if ($disp_order == 'a.sku ASC') $disp_order ='LPAD(a.sku,'.MAX_INVENTORY_SKU_LENGTH.',0) ASC';
//	if ($disp_order == 'a.sku DESC')$disp_order ='LPAD(a.sku,'.MAX_INVENTORY_SKU_LENGTH.',0) DESC';
	// build the list for the page selected
    if (isset($_REQUEST['search_text']) && $_REQUEST['search_text'] <> '') {
      $search_fields = array('a.sku', 'a.description_short', 'a.description_sales', 'p.description_purchase');
	  // hook for inserting new search fields to the query criteria.
	  if (is_array($extra_search_fields)) $search_fields = array_merge($search_fields, $extra_search_fields);
  	  $criteria[] = '(' . implode(' like \'%' . $_REQUEST['search_text'] . '%\' or ', $search_fields) . ' like \'%' . $_REQUEST['search_text'] . '%\')';
	}
	// build search filter string
	$search = (sizeof($criteria) > 0) ? (' where ' . implode(' and ', $criteria)) : '';
	$field_list = array('a.id as id', 'a.sku as sku', 'inactive', 'inventory_type', 'description_short', 'full_price', 
			'quantity_on_hand', 'quantity_on_order', 'quantity_on_sales_order', 'quantity_on_allocation', 'last_journal_date');
	// hook to add new fields to the query return results
	if (is_array($extra_query_list_fields) > 0) $field_list = array_merge($field_list, $extra_query_list_fields);
    $query_raw    = "SELECT SQL_CALC_FOUND_ROWS DISTINCT " . implode(', ', $field_list)  . " from " . TABLE_INVENTORY ." a LEFT JOIN " . TABLE_INVENTORY_PURCHASE . " p on a.sku = p.sku ". $search . " order by $disp_order ";
    $query_result = $db->Execute($query_raw, (MAX_DISPLAY_SEARCH_RESULTS * ($_REQUEST['list'] - 1)).", ".  MAX_DISPLAY_SEARCH_RESULTS);
    // the splitPageResults should be run directly after the query that contains SQL_CALC_FOUND_ROWS
    $query_split  = new \core\classes\splitPageResults($_REQUEST['list'], '');
	history_save('inventory');
    //building array's for filter dropdown selection
	$i=0;
	$result = $db->Execute("SELECT * FROM " . TABLE_EXTRA_FIELDS ." WHERE module_id = 'inventory' AND use_in_inventory_filter = '1' ORDER BY description ASC");
	$FirstValue 		= 'var FirstValue = new Array();' 		. chr(10);
	$FirstId 			= 'var FirstId = new Array();' 			. chr(10);
	$SecondField 		= 'var SecondField = new Array();' 		. chr(10);
	$SecondFieldValue	= 'var SecondFieldValue = new Array();'	. chr(10);
	$SecondFieldId		= 'var SecondFieldId = new Array();' 	. chr(10);
	while (!$result->EOF) {
		if(in_array($result->fields['field_name'], array('vendor_id','description_purchase','item_cost','purch_package_quantity','purch_taxable','price_sheet_v')) ){
			$append 	= 'p.';
		}else{
			$append 	= 'a.';
		}
		$FirstValue .= 'FirstValue[' . $i . '] = "' . $result->fields['description'] . '";' . chr(10);
		$FirstId 	.= 'FirstId[' 	 . $i . '] = "' . $append . $result->fields['field_name'] 	. '";' . chr(10);
		Switch($result->fields['field_name']){
			case 'vendor_id':
				$contacts = gen_get_contact_array_by_type('v');
				$tempValue  ='Array("'  ;
				$tempId 	='Array("' ;
				while ($contact = array_shift($contacts)) {
						$tempValue .= $contact['id'].'","';
						$tempId    .= str_replace( array("/","'",chr(34),) , ' ', $contact['text']).'","';
				}
				$tempValue  .='")' ;
				$tempId 	.='")' ;
				$SecondField		.= 'SecondField["'		. $append . $result->fields['field_name'] 	. '"] = "drop_down";' . chr(10);
				$SecondFieldValue	.= 'SecondFieldValue["'	. $append . $result->fields['field_name'] 	. '"] = '. $tempValue . ';' . chr(10);
				$SecondFieldId		.= 'SecondFieldId["'  	. $append . $result->fields['field_name']	. '"] = '. $tempId . ';' . chr(10);
				break;
			case'inventory_type':

				$tempValue 	='Array("'  ;
				$tempId 	='Array("' ;
				foreach ($inventory_types_plus as $key => $value){
					$tempValue .= $key.'","';
					$tempId    .= $value.'","';
				}
				$tempValue 	.='")' ;
				$tempId 	.='")' ;
				$SecondField		.= 'SecondField["' 		. $append . $result->fields['field_name'] . '"] = "drop_down";' . chr(10);
				$SecondFieldValue	.= 'SecondFieldValue["'	. $append . $result->fields['field_name'] . '"] = '. $tempValue . ';' . chr(10);
				$SecondFieldId		.= 'SecondFieldId["' 	. $append . $result->fields['field_name'] . '"] = '. $tempId . ';' . chr(10);
				break;
			case'cost_method':
				$tempValue 	='Array("'  ;
				$tempId 	='Array("' ;
				foreach ($cost_methods as $key => $value){
					$tempValue .= $key.'","';
					$tempId    .= $value.'","';
				}
				$tempValue .='")' ;
				$tempId .='")' ;
				$SecondField		.= 'SecondField["' 		. $append . $result->fields['field_name'] . '"] = "drop_down";' . chr(10);
				$SecondFieldValue	.= 'SecondFieldValue["'	. $append . $result->fields['field_name'] . '"] = '. $tempValue . ';' . chr(10);
				$SecondFieldId		.= 'SecondFieldId["' 	. $append . $result->fields['field_name'] . '"] = '. $tempId . ';' . chr(10);
				break;	
			default:
				$SecondField.= 'SecondField["' . $append . $result->fields['field_name'] . '"] ="'. $result->fields['entry_type'] . '";' . chr(10);
				if(in_array($result->fields['entry_type'], array('drop_down','radio','multi_check_box'))){
					$tempValue 	='Array("';
					$tempId 	='Array("' ;
					//explode params and splits value form id
					$params  = unserialize($result->fields['params']);
					$choices = explode(',',$params['default']);
					while ($choice = array_shift($choices)) {
							$values 	 = explode(':',$choice);
							$tempValue	.= $values[0].'","';
							$tempId		.= $values[1].'","';
					}
					$tempValue 	.='")' ;
					$tempId 	.='")' ;
					$SecondFieldValue	.= 'SecondFieldValue["' . $append . $result->fields['field_name'] . '"] ='. $tempValue . ';' . chr(10);
					$SecondFieldId		.= 'SecondFieldId["' 	. $append . $result->fields['field_name'] . '"] ='. $tempId . ';' . chr(10);
				}
		}	
		$i++;
	   	$result->MoveNext();
	}
    //end building array's for filter dropdown selection
	define('PAGE_TITLE', BOX_INV_MAINTAIN);
    $include_template = 'template_main.php';
	break;
}
?>