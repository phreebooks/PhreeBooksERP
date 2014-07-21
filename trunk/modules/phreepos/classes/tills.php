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
//  Path: /modules/phreepos/classes/tills.php
//

class tills {
	public $code                    = 'tills';
    public $db_table     			= TABLE_PHREEPOS_TILLS;
    public $help_path   			= '';
    public $error       			= false;
    public $currencies_code 		= DEFAULT_CURRENCY;
    public $printer_name    		= '';
    public $printer_starting_line 	= '';
	public $printer_closing_line  	= '';
    public $printer_open_drawer   	= '';
    
    public function __construct(){
         $this->security_id           = $_SESSION['admin_security'][SECURITY_ID_CONFIGURATION];
         foreach ($_POST as $key => $value) $this->$key = db_prepare_input($value);
         $this->id = isset($_POST['sID'])? $_POST['sID'] : $_GET['sID'];
         $this->store_ids = gen_get_store_ids();
         if($_REQUEST['page'] == 'main') $this->showDropDown();
    }

  function btn_save($id = '') {
  	global $db, $messageStack, $currencies;
	validate_security($this->security_id, 2);
	if ($this->gl_acct_id == ''){
		$messageStack->add(GL_SELECT_STD_CHART,'error');
		return false;
	}
	$sql_data_array = array(
		'description' 		    => $this->description,
		'store_id'    		    => $this->store_id,
		'gl_acct_id'  		    => $this->gl_acct_id,
		'dif_gl_acct_id'		=> $this->dif_gl_acct_id,
		'rounding_gl_acct_id'   => $this->rounding_gl_acct_id,
		'currencies_code'   	=> $this->currencies_code,
		'restrict_currency'	 	=> ENABLE_MULTI_CURRENCY ? $this->restrict_currency : 1,
		'printer_name'			=> $this->printer_name,
		'printer_starting_line' => $this->printer_starting_line,
		'printer_closing_line' 	=> $this->printer_closing_line,
		'printer_open_drawer' 	=> $this->printer_open_drawer,
		'balance' 				=> $currencies->clean_value($this->balance, $this->currencies_code),
		'max_discount'		    => $this->max_discount,
		'tax_id'				=> $this->tax_id,
	);
	
    if ($id) {
	  db_perform($this->db_table, $sql_data_array, 'update', "till_id = '" . $id . "'");
	  gen_add_audit_log(SETUP_TAX_AUTHS_LOG . TEXT_UPDATE, $this->description);
	} else  {
      db_perform($this->db_table, $sql_data_array);
	  gen_add_audit_log(SETUP_TAX_AUTHS_LOG . TEXT_ADD, $this->description);
	}
	return true;
  }

  function btn_delete($id = 0) {
  	global $db, $messageStack;
	validate_security($this->security_id, 4);
	// Don't allow delete if there is account activity for this account
	$sql = "select max(debit_amount) as debit, max(credit_amount) as credit, max(beginning_balance) as beg_bal 
		from " . TABLE_CHART_OF_ACCOUNTS_HISTORY . " where account_id = '" . $this->gl_acct_id . "'";
	$result = $db->Execute($sql);
	if ($result->fields['debit'] <> 0 || $result->fields['credit'] <> 0 || $result->fields['beg_bal'] <> 0) {
	  $messageStack->add(GL_ERROR_CANT_DELETE, 'error');
	  return false;
	}
	// OK to delete
	$result = $db->Execute("select description from " . $this->db_table . " where till_id = '" . $id . "'");
	$db->Execute("delete from " . $this->db_table . " where till_id = '" . $id . "'");
	gen_add_audit_log(SETUP_TAX_AUTHS_LOG . TEXT_DELETE, $result->fields['description']);
	return true;
  }

  function build_main_html() {
  	global $db, $messageStack ,$currencies;
    $content = array();
	$content['thead'] = array(
	  'value' => array(TEXT_DESCRIPTION, GEN_STORE_ID, TEXT_GL_ACCOUNT, TEXT_ACTION, TEXT_BALANCE),
	  'params'=> 'width="100%" cellspacing="0" cellpadding="1"',
	);
    $result = $db->Execute("select * from " . $this->db_table );
    $rowCnt = 0;
	while (!$result->EOF) {
	  $actions = '';
	  if ($this->security_id > 1) $actions .= html_icon('actions/edit-find-replace.png', TEXT_EDIT, 'small', 'onclick="loadPopUp(\''.$this->code.'_edit\', ' . $result->fields['till_id'] . ')"') . chr(10);
	  if ($this->security_id > 3) $actions .= html_icon('emblems/emblem-unreadable.png', TEXT_DELETE, 'small', 'onclick="if (confirm(\'' . SETUP_TILL_DELETE_INTRO . '\')) subjectDelete(\''.$this->code.'\', ' . $result->fields['till_id'] . ')"') . chr(10);
	  $content['tbody'][$rowCnt] = array(
	    array('value' => htmlspecialchars($result->fields['description']),
			  'params'=> 'style="cursor:pointer" onclick="loadPopUp(\''.$this->code.'_edit\',\''.$result->fields['till_id'].'\')"'),
		array('value' => htmlspecialchars($result->fields['store_id']), 
			  'params'=> 'style="cursor:pointer" onclick="loadPopUp(\''.$this->code.'_edit\',\''.$result->fields['till_id'].'\')"'),
		array('value' => gen_get_type_description(TABLE_CHART_OF_ACCOUNTS, $result->fields['gl_acct_id']),
			  'params'=> 'style="cursor:pointer" onclick="loadPopUp(\''.$this->code.'_edit\',\''.$result->fields['till_id'].'\')"'),
		array('value' => $currencies->format($result->fields['balance'], true, $result->fields['currencies_code'] ),
			  'params'=> 'style="cursor:pointer" onclick="loadPopUp(\''.$this->code.'_edit\',\''.$result->fields['till_id'].'\')"'),
		array('value' => $actions,
			  'params'=> 'align="right"'),
	  );
      $result->MoveNext();
	  $rowCnt++;
    }
    return html_datatable(''.$this->code.'_table', $content);
  }

  function build_form_html($action, $id = '') {
    global $db, $currencies;
    if ($action <> 'new' && $this->error == false) {
        $sql = "select * from " . $this->db_table . " where till_id = " . $id;
        $result = $db->Execute($sql);
        foreach ($result->fields as $key => $value) $this->$key = $value;
	}
	$tax_rates = inv_calculate_tax_drop_down('c');
	$output  = '<table style="border-collapse:collapse;margin-left:auto; margin-right:auto;">' . chr(10);
	$output .= '  <thead class="ui-widget-header">' . "\n";
	$output .= '  <tr>' . chr(10);
	$output .= '    <th colspan="2">' . ($action=='new' ? TEXT_ENTER_NEW_TILL : TEXT_EDIT_TILL) . '</th>' . chr(10);
    $output .= '  </tr>' . chr(10);
	$output .= '  </thead>' . "\n";
	$output .= '  <tbody class="ui-widget-content">' . "\n";
	$output .= '  <tr>' . chr(10);
	$output .= '    <td>' . TEXT_DESCRIPTION . '</td>' . chr(10);
	$output .= '    <td>' . html_input_field('description', $this->description, 'size="16" maxlength="15"') . '</td>' . chr(10);
    $output .= '  </tr>' . chr(10);
	$output .= '  <tr>' . chr(10);
	$output .= '    <td>' . GEN_STORE_ID . '</td>' . chr(10);
	$output .= '    <td>' . html_pull_down_menu('store_id', $this->store_ids, $this->store_id) . '</td>' . chr(10);
    $output .= '  </tr>' . chr(10);
	$output .= '  <tr>' . chr(10);
	$output .= '    <td>' . TEXT_GL_ACCOUNT . '</td>' . chr(10);
	$output .= '    <td>' . html_pull_down_menu('gl_acct_id', gen_coa_pull_down(SHOW_FULL_GL_NAMES, true, true, false, $restrict_types = array(0)), $this->gl_acct_id) . '</td>' . chr(10);
    $output .= '  </tr>' . chr(10);
    $output .= '  <tr>' . chr(10);
	$output .= '    <td>' . TEXT_GL_ACCOUNT_ROUNDING . '</td>' . chr(10);
	$output .= '    <td>' . html_pull_down_menu('rounding_gl_acct_id', gen_coa_pull_down(SHOW_FULL_GL_NAMES, true, true, false, $restrict_types = array(30)), $this->rounding_gl_acct_id) . '</td>' . chr(10);
    $output .= '  </tr>' . chr(10);
	$output .= '  <tr>' . chr(10);
	$output .= '    <td>' . TEXT_DIF_GL_ACCOUNT . '</td>' . chr(10);
	$output .= '    <td>' . html_pull_down_menu('dif_gl_acct_id', gen_coa_pull_down(SHOW_FULL_GL_NAMES, true, true, false, $restrict_types = array(30)), $this->dif_gl_acct_id) . '</td>' . chr(10);
    $output .= '  </tr>' . chr(10);
    $output .= '  <tr>' . chr(10);
	$output .= '    <td>' . INV_ENTRY_ITEM_TAXABLE . '</td>' . chr(10);
	$output .= '    <td>' . html_pull_down_menu('tax_id', $tax_rates, $this->tax_id) . '</td>' . chr(10);
    $output .= '  </tr>' . chr(10);
    $output .= '  <tr>' . chr(10);
	$output .= '    <td>' . TEXT_BALANCE . '</td>' . chr(10);
	$output .= '    <td>' . html_input_field('balance', $currencies->format($this->balance, true, $this->currencies_code )) . '</td>' . chr(10);
    $output .= '  </tr>' . chr(10);
    $output .= '  <tr>' . chr(10);
	$output .= '    <td>' . TEXT_MAX_DISCOUNT . '</td>' . chr(10);
	$output .= '    <td>' . html_input_field('max_discount', $this->max_discount, 'size="16" maxlength="15"') . '</td>' . chr(10);
    $output .= '  </tr>' . chr(10);
    //default currency
    if (ENABLE_MULTI_CURRENCY) {	// show currency slection pulldown
    	$output .= '  <tr>' . chr(10);
		$output .= '    <td>' . TEXT_CURRENCY . '</td>' . chr(10);
		$output .= '    <td>' . html_pull_down_menu('currencies_code', gen_get_pull_down(TABLE_CURRENCIES, false, false, 'code', 'title'), $this->currencies_code) . '</td>' . chr(10);
    	$output .= '  </tr>' . chr(10);
    	//restrict to this currency
    	$output .= '  <tr>' . chr(10);
		$output .= '    <td>' . TEXT_RESTRICT_CURRENCY . '</td>'  . chr(10);
		$output .= '    <td>' . html_checkbox_field('restrict_currency', '1', $this->restrict_currency) . '</td>'  . chr(10);
    	$output .= '  </tr>' . chr(10);
    }
    //end default currency
    //printer information
	$output .= '  <tr>' . chr(10);
	$output .= '    <td>' . PHREEPOS_RECEIPT_PRINTER_NAME_DESC  . '<br><br></td>' . chr(10);
	$output .= '    <td>' . html_input_field('printer_name', $this->printer_name) . '</td>' . chr(10);
	$output .= '  </tr>' . chr(10);
	$output .= '  <tr>' . chr(10);
	$output .= '    <td>' . PHREEPOS_RECEIPT_PRINTER_STARTING_LINE_DESC  . '<br><br></td>' . chr(10);
	$output .= '    <td>' . html_input_field('printer_starting_line', $this->printer_starting_line) . '</td>' . chr(10);
	$output .= '  </tr>' . chr(10);
	$output .= '  <tr>' . chr(10);
	$output .= '    <td>' . PHREEPOS_RECEIPT_PRINTER_CLOSING_LINE_DESC . '<a href="' . DIR_WS_ADMIN.'modules/phreepos/printer_codes.htm">'. TEXT_DRAWER_CODES . '</a> <br><br></td>' . chr(10);
	$output .= '    <td>' . html_input_field('printer_closing_line', $this->printer_closing_line) . '</td>' . chr(10);
	$output .= '  </tr>' . chr(10);
	$output .= '  <tr>' . chr(10);
	$output .= '    <td>' . PHREEPOS_RECEIPT_PRINTER_OPEN_DRAWER_DESC  . '<br><br></td>' . chr(10);
	$output .= '    <td>' . html_input_field('printer_open_drawer', $this->printer_open_drawer) . '</td>' . chr(10);
	$output .= '  </tr>' . chr(10);
    //end printer information
    $output .= '  </tbody>' . chr(10);
    $output .= '</table>' . chr(10);
    return $output;
  }
  
// functions for template main  
  function showDropDown(){
  	global $db, $messageStack;
  	foreach ($this->store_ids as $store){
  		$temp[]= $store['id'];
  	}
  	$sql = "select till_id, description from " . $this->db_table . " where store_id in (" . implode(',', $temp) . ")";
    $result = $db->Execute($sql);
    if ($result->RecordCount()== 0){// trigger_error("Before continuing set a till for this store. This will contain default values to allow this page to work", E_USER_ERROR);// there should always be a till because of defaults values.
    	$messageStack->add("Before continuing set a till for this store.<br> This will contain default values to allow this page to work", 'error');
    	gen_redirect(html_href_link(FILENAME_DEFAULT, '', 'SSL')); 
    }
    if (defined('ENABLE_ENCRYPTION') && ENABLE_ENCRYPTION == true && (!isset($_SESSION['admin_encrypt']) || $_SESSION['admin_encrypt'] == '')){
    	$messageStack->add("Error - Encryption key not set! The encryption key must be set to use the POS module.", 'error');
    	gen_redirect(html_href_link(FILENAME_DEFAULT, '', 'SSL'));
    }
    if ($result->RecordCount()== 1) {
    	return false;
    }else{
    	return true;
    }
  }
  
  function default_till(){
  	global $db;
  	$sql = "select till_id from " . $this->db_table . " where store_id = '" . $_SESSION['admin_prefs']['def_store_id']."'";
    $result = $db->Execute($sql);
    return $result->fields['till_id'];
  }
  
  function till_array($inc_select = false){
  	global $db;
  	foreach ($this->store_ids as $store){
  		$temp[]= $store['id'];
  	}
  	$sql = "select till_id, description from " . $this->db_table . " where store_id in (" . implode(',', $temp) . ")";
    $result = $db->Execute($sql);
    if ($inc_select) $result_array[] = array('id' => '0', 'text' => GEN_HEADING_PLEASE_SELECT);
    while(!$result->EOF){
    	$result_array[] = array('id' => $result->fields['till_id'], 'text' => $result->fields['description']);
    	$result->MoveNext();
    }
    return $result_array;
  }
  
  function get_till_info($till_id){
  	global $db;
  	$sql = "select * from " . $this->db_table . " where till_id = " . $till_id;
    $result = $db->Execute($sql);
    foreach ($result->fields as $key => $value) $this->$key = $value;
  }
  
  function get_default_till_info(){
  	global $db;
  	$sql = "select * from " . $this->db_table . " where store_id = '" . $_SESSION['admin_prefs']['def_store_id']."'";
    $result = $db->Execute($sql);
    foreach ($result->fields as $key => $value) $this->$key = $value;
  }
  /* 
   * returns a string that will be a array in javascript.
   */
  
  function javascript_array(){
  	global $db;
  	foreach ($this->store_ids as $store){
  		$temp[]= $store['id'];
  	}
  	$sql = "select * from " . $this->db_table . " where store_id in (" . implode(',', $temp) . ")";
    $result = $db->Execute($sql);    
  	$js_tills  = 'var tills  = new Array();' . chr(10);
	while (!$result->EOF){
		$startingline = '';
		$closingline  = '';
		$opendrawer   = '';
		foreach(explode(",",$result->fields['printer_starting_line']) as $key=>$line) {
			foreach( explode( ":" , $line) as $key => $char) $startingline .= chr($char) ;
			$startingline .= '","';
		}
		foreach(explode(",",$result->fields['printer_closing_line']) as $key=>$line) {
			foreach( explode( ":" , $line) as $key => $char) $closingline  .= chr($char) ;
			$closingline .= '","';
		}
		foreach(explode(",",$result->fields['printer_open_drawer']) as $key=>$line) {
			foreach( explode( ":" , $line) as $key => $char) $opendrawer   .= chr($char) ;
			$opendrawer  .=  '","';
		}
		$startingline = rtrim($startingline, '","');
		$closingline  = rtrim($closingline , '","');
		$opendrawer   = rtrim($opendrawer  , '","');
		$js_tills .= 'tills["' . $result->fields['till_id'] . '"] = new till("' . $result->fields['till_id'] . '", "' . $result->fields['restrict_currency'] . '", "' . $result->fields['currencies_code'] . '", "' . $result->fields['printer_name'] . '", Array("' . $startingline . '"), Array("' . $closingline . '"), Array("' . $opendrawer . '" ), "' . $result->fields['tax_id'] . '", "' . $result->fields['store_id'] . '");' . chr(10);
		$result->MoveNext();
	}
	return $js_tills;
  }
  
  function adjust_balance($amount){
  	global $db, $messageStack;
  	$sql_data_array = array('balance' => $this->balance + $amount);
  	$messageStack->debug("\n\n\n updating till balance  balance was = ".$this->balance . " adding = ". $amount." new balance = ". $this->balance + $amount."\n\n\n");
  	db_perform($this->db_table, $sql_data_array, 'update', "till_id = '" . $this->till_id . "'");
  }
  
  function new_balance($amount){
  	global $db, $messageStack;
  	$sql_data_array = array('balance' => $amount);
  	$messageStack->debug("\n\n\n updating till balance  balance was = ".$this->balance . " new balance = ". $amount ."\n\n\n");
  	db_perform($this->db_table, $sql_data_array, 'update', "till_id = '" . $this->till_id . "'");
  }
  
  function __destruct(){
  	//print_r($this);
  }
  
}
?>