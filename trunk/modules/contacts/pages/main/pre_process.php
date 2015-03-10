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
//  Path: /modules/contacts/pages/main/pre_process.php
//
/**************   page specific initialization  *************************/
$contact_js  = '';
$js_pmt_array= '';
$js_actions  = '';
$criteria    = array();
if ($_POST['crm_date']) $_POST['crm_date'] = gen_db_date($_POST['crm_date']);
if ($_POST['due_date']) $_POST['due_date'] = gen_db_date($_POST['due_date']);
$type        = isset($_GET['type']) ? $_GET['type'] : 'c'; // default to customer

history_filter('contacts'.$type, $defaults = array('sf'=>'', 'so'=>'asc')); // load the filters
$default_f0 = defined('CONTACTS_F0_'.strtoupper($type)) ? constant('CONTACTS_F0_'.strtoupper($type)) : DEFAULT_F0_SETTING;
$_SESSION['f0'] = (isset($_SESSION['f0'])) ? $_SESSION['f0'] : $default_f0;
if($_SERVER['REQUEST_METHOD'] == 'POST') $_SESSION['f0'] = (isset($_POST['f0'])) ? $_REQUEST['f0'] : false; // show inactive checkbox
$temp = '\contacts\classes\type\\'.$type;
$cInfo = new $temp;
/**************   Check user security   *****************************/

/***************   hook for custom security  ***************************/
$custom_path = DIR_FS_WORKING . 'custom/contacts/main/extra_security.php';
if (file_exists($custom_path)) { include($custom_path); }
$security_level = \core\classes\user::validate($cInfo->security_token); // in this case it must be done after the class is defined for
/**************  include page specific files    *********************/
require_once(DIR_FS_WORKING . 'defaults.php');
require_once(DIR_FS_MODULES . 'phreedom/functions/phreedom.php');
require_once(DIR_FS_MODULES . 'phreebooks/functions/phreebooks.php');
require_once(DIR_FS_WORKING . 'functions/contacts.php');
$fields = new \contacts\classes\fields();
/***************   hook for custom actions  ***************************/
$custom_path = DIR_FS_WORKING . 'custom/pages/main/extra_actions.php';
if (file_exists($custom_path)) { include($custom_path); }
/***************   Act on the action request   *************************/

switch ($_REQUEST['action']) {
    case 'save':
    	try{
			$id = (int)db_prepare_input($_POST['id']);  // if present, then its an edit
		    $id ? \core\classes\user::validate_security($security_level, 3) : \core\classes\user::validate_security($security_level, 2);
			// error check
			$cInfo->data_complete();
			// start saving data
		  	$cInfo->save_contact();
		  	$cInfo->save_addres();
		  	if ($type <> 'i' && ($_POST['i_short_name'] || $_POST['address']['im']['primary_name'])) { // is null
		  		$crmInfo = new \contacts\classes\type\i;
	        	$crmInfo->auto_field  = $cInfo->type=='v' ? 'next_vend_id_num' : 'next_cust_id_num';
	        	$crmInfo->dept_rep_id = $cInfo->id;
		  		// error check contact
				$crmInfo->data_complete();
	        	$crmInfo->save_contact();
	      		$crmInfo->save_addres();
		  	}
		  	// payment fields
		  	if (ENABLE_ENCRYPTION && $_POST['payment_cc_name'] && $_POST['payment_cc_number']) { // save payment info
				$cc_info = array(
				  'name'    => db_prepare_input($_POST['payment_cc_name']),
				  'number'  => db_prepare_input($_POST['payment_cc_number']),
				  'exp_mon' => db_prepare_input($_POST['payment_exp_month']),
				  'exp_year'=> db_prepare_input($_POST['payment_exp_year']),
				  'cvv2'    => db_prepare_input($_POST['payment_cc_cvv2']),
				);
				$enc_value = \core\classes\encryption::encrypt_cc($cc_info);
				$payment_array = array(
				  'hint'      => $enc_value['hint'],
				  'module'    => 'contacts',
				  'enc_value' => $enc_value['encoded'],
				  'ref_1'     => $cInfo->id,
				  'ref_2'     => $cInfo->address[$type.'m']['address_id'],
				  'exp_date'  => $enc_value['exp_date'],
				);
				db_perform(TABLE_DATA_SECURITY, $payment_array, $_POST['payment_id'] ? 'update' : 'insert', 'id = '.$_POST['payment_id']);
		  	}
		  	// Check attachments
		  	$result = $admin->DataBase->query("select attachments from ".TABLE_CONTACTS." where id = $id");
		  	$attachments = $result->fields['attachments'] ? unserialize($result->fields['attachments']) : array();
		  	$image_id = 0;
		  	while ($image_id < 100) { // up to 100 images
		    	if (isset($_POST['rm_attach_'.$image_id])) {
				  	@unlink(CONTACTS_DIR_ATTACHMENTS . 'contacts_'.$cInfo->id.'_'.$image_id.'.zip');//@todo replace with $this->dir_attachments
				  	unset($attachments[$image_id]);
		    	}
		    	$image_id++;
		  	}
		  	if (is_uploaded_file($_FILES['file_name']['tmp_name'])) { // find an image slot to use
		    	$image_id = 0;
		    	while (true) {
				    if (!file_exists(CONTACTS_DIR_ATTACHMENTS.'contacts_'.$cInfo->id.'_'.$image_id.'.zip')) break;//@todo replace with $this->dir_attachments
				    $image_id++;
			    }
		    	saveUploadZip('file_name', CONTACTS_DIR_ATTACHMENTS, 'contacts_'.$cInfo->id.'_'.$image_id.'.zip');//@todo replace with $this->dir_attachments
			    $attachments[$image_id] = $_FILES['file_name']['name'];
			}
		  	$sql_data_array = array('attachments' => sizeof($attachments)>0 ? serialize($attachments) : '');
		  	db_perform(TABLE_CONTACTS, $sql_data_array, 'update', 'id = '.$cInfo->id);
		  	// check for crm notes
		  	if ($_POST['crm_action'] <> '' || $_POST['crm_note'] <> '') {
				$sql_data_array = array(
				  'contact_id' => $cInfo->id,
				  'log_date'   => $_POST['crm_date'],
				  'entered_by' => $_POST['crm_rep_id'],
				  'action'     => $_POST['crm_action'],
				  'notes'      => db_prepare_input($_POST['crm_note']),
				);
				db_perform(TABLE_CONTACTS_LOG, $sql_data_array, 'insert');
		  	}
		  	gen_redirect(html_href_link(FILENAME_DEFAULT, gen_get_all_get_params(array('action')), 'SSL'));
		}catch(Exception $e){
			$messageStack->add($e->getMessage());
			$_REQUEST['action'] = 'edit';
		}

		break;

    case 'download':
   	    $cID   = db_prepare_input($_POST['id']);
  	    $imgID = db_prepare_input($_POST['rowSeq']);
	    $filename = 'contacts_'.$cID.'_'.$imgID.'.zip';
	    if (file_exists(CONTACTS_DIR_ATTACHMENTS . $filename)) {//@todo replace with $this->dir_attachments
	       $backup = new \phreedom\classes\backup();
	       $backup->download(CONTACTS_DIR_ATTACHMENTS, $filename, true);//@todo replace with $this->dir_attachments
	    }
	    ob_end_flush();
  		session_write_close();
        die;

    case 'dn_attach': // download from list, assume the first document only
        $cID   = db_prepare_input($_POST['rowSeq']);
  	    $result = $admin->DataBase->query("select attachments from ".TABLE_CONTACTS." where id = $cID");
  	    $attachments = unserialize($result->fields['attachments']);
  	    foreach ($attachments as $key => $value) {
		   	$filename = 'contacts_'.$cID.'_'.$key.'.zip';
		   	if (file_exists(CONTACTS_DIR_ATTACHMENTS . $filename)) {//@todo replace with $this->dir_attachments
		      	$backup = new \phreedom\classes\backup();
		      	$backup->download(CONTACTS_DIR_ATTACHMENTS, $filename, true);//@todo replace with $this->dir_attachments
		      	ob_end_flush();
  				session_write_close();
		      	die;
		   	}
  	    }
 	case 'reset':
 		$_SESSION['f0'] = $default_f0;
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
?>