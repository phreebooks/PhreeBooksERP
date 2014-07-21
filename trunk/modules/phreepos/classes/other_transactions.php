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
//  Path: /modules/phreepos/classes/other_transactions.php
//

class other_transactions {
	public $code        	= 'other_transactions'; // needs to match class name
    public $db_table     	= TABLE_PHREEPOS_OTHER_TRANSACTIONS;
    public $help_path   	= '';
    public $error       	= false;
    
    public function __construct(){
         $this->security_id           = $_SESSION['admin_security'][SECURITY_ID_CONFIGURATION];
         foreach ($_POST as $key => $value) $this->$key = db_prepare_input($value);
         $this->id = isset($_POST['sID'])? $_POST['sID'] : $_GET['sID'];
         $this->store_ids = gen_get_store_ids();
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
			'till_id'    		    => $this->till_id,
			'gl_acct_id'  		    => $this->gl_acct_id,
			'type'    			    => $this->type,
			'use_tax'				=> isset($this->use_tax)? 1:0,
			'taxable'				=> $this->taxable,
		);
    	if ($id) {
			db_perform($this->db_table, $sql_data_array, 'update', "ot_id = '" . $id . "'");
			gen_add_audit_log(SETUP_TAX_AUTHS_LOG . TEXT_UPDATE, $this->description);
		} else {
      		db_perform($this->db_table, $sql_data_array);
			gen_add_audit_log(SETUP_TAX_AUTHS_LOG . TEXT_ADD, $this->description);
		}
		return true;
  	}

  	function btn_delete($id = 0) {
  		global $db, $messageStack;
  		validate_security($this->security_id, 4);
		// 	OK to delete
		$result = $db->Execute("select description from " . $this->db_table . " where ot_id = '" . $id . "'");
		$db->Execute("delete from " . $this->db_table . " where ot_id = '" . $id . "'");
		gen_add_audit_log(SETUP_TAX_AUTHS_LOG . TEXT_DELETE, $result->fields['description']);
		return true;
  	}

  	function build_main_html() {
  		global $db, $messageStack, $currencies;
  		require_once(DIR_FS_MODULES . 'phreepos/defaults.php');
    	$content = array();
		$content['thead'] = array(
	  		'value' => array(TEXT_DESCRIPTION, GEN_STORE_ID, TEXT_GL_ACCOUNT, TEXT_ACTION),
	  		'params'=> 'width="100%" cellspacing="0" cellpadding="1"',
		);
    	$result = $db->Execute("select * from " . $this->db_table );
    	$rowCnt = 0;
		while (!$result->EOF) {
	  		$actions = '';
	  		if ($this->security_id > 1) $actions .= html_icon('actions/edit-find-replace.png', TEXT_EDIT, 'small', 'onclick="loadPopUp(\''.$this->code.'_edit\', ' . $result->fields['ot_id'] . ')"') . chr(10);
	  		if ($this->security_id > 3) $actions .= html_icon('emblems/emblem-unreadable.png', TEXT_DELETE, 'small', 'onclick="if (confirm(\'' . SETUP_OT_DELETE_INTRO . '\')) subjectDelete(\''.$this->code.'\', ' . $result->fields['ot_id'] . ')"') . chr(10);
	  		$content['tbody'][$rowCnt] = array(
	    		array('value' => htmlspecialchars($result->fields['description']),
			  		  'params'=> 'style="cursor:pointer" onclick="loadPopUp(\''.$this->code.'_edit\',\''.$result->fields['ot_id'].'\')"'),
				array('value' => htmlspecialchars($result->fields['store_id']), 
					  'params'=> 'style="cursor:pointer" onclick="loadPopUp(\''.$this->code.'_edit\',\''.$result->fields['ot_id'].'\')"'),
				array('value' => gen_get_type_description(TABLE_CHART_OF_ACCOUNTS, $result->fields['gl_acct_id']),
			  		  'params'=> 'style="cursor:pointer" onclick="loadPopUp(\''.$this->code.'_edit\',\''.$result->fields['ot_id'].'\')"'),
				array('value' => $ot_options[$result->fields['type']],
			  		  'params'=> 'style="cursor:pointer" onclick="loadPopUp(\''.$this->code.'_edit\',\''.$result->fields['ot_id'].'\')"'),
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
    	require_once(DIR_FS_MODULES . 'phreepos/classes/tills.php');
    	require_once(DIR_FS_MODULES . 'phreepos/defaults.php');
    	if ($action <> 'new' && $this->error == false) {
        	$sql = "select * from " . $this->db_table . " where ot_id = " . $id;
        	$result = $db->Execute($sql);
        	foreach ($result->fields as $key => $value) $this->$key = $value;
		}
		$tills = new tills();
		$output = "<script type='text/javascript'>
						$(document).ready(function(){
							changeOfType();
						});
		
						function changeOfType(){
							var elt = document.getElementById('type');
							if(elt.options[elt.selectedIndex].value == 'expenses'){
								$('#use_tax_row').show();
								$('#tax_row').show();
							}else{
								$('#use_tax_row').hide();
								$('#tax_row').hide();
							}
						}
					</script>";
		$output .= '<table style="border-collapse:collapse;margin-left:auto; margin-right:auto;">' . chr(10);
		$output .= '  <thead class="ui-widget-header">' . "\n";
		$output .= '  <tr>' . chr(10);
		$output .= '    <th colspan="2">' . ($action=='new' ? TEXT_ENTER_NEW_OTHER_TRANSACTION : TEXT_EDIT_OTHER_TRANSACTION) . '</th>' . chr(10);
    	$output .= '  </tr>' . chr(10);
		$output .= '  </thead>' . "\n";
		$output .= '  <tbody class="ui-widget-content">' . "\n";
		$output .= '  <tr>' . chr(10);
		$output .= '    <td>' . TEXT_DESCRIPTION . '</td>' . chr(10);
		$output .= '    <td>' . html_input_field('description', $this->description, 'size="16" maxlength="15"') . '</td>' . chr(10);
    	$output .= '  </tr>' . chr(10);
    	if($tills->showDropDown()){
			$output .= '  <tr>' . chr(10);
			$output .= '    <td>' . TEXT_TILLS . '</td>' . chr(10);
			$output .= '    <td>' . html_pull_down_menu('till_id', $tills->till_array() , $this->till_id ? $this->till_id : $tills->default_till()) . '</td>' . chr(10);
    		$output .= '  </tr>' . chr(10);
  		}else{ 
			$output .=	html_hidden_field('till_id', $tills->default_till()); 
		}
	    //type change cash or expences.
    	$output .= '  <tr>' . chr(10);
		$output .= '    <td>' . TEXT_PHREEPOS_TRANSACTION_TYPE . '</td>' . chr(10);
		$output .= '    <td>' . html_pull_down_menu('type', gen_build_pull_down($ot_options), $this->type, 'onchange="changeOfType();"') . '</td>' . chr(10);
    	$output .= '  </tr>' . chr(10);
	    $output .= '  <tr>' . chr(10);
		//gl account
		$output .= '  <tr>' . chr(10);
		$output .= '    <td>' . TEXT_GL_ACCOUNT . '</td>' . chr(10);
		$output .= '    <td>' . html_pull_down_menu('gl_acct_id', gen_coa_pull_down(SHOW_FULL_GL_NAMES, true, true, false, false), $this->gl_acct_id) . '</td>' . chr(10);
    	$output .= '  </tr>' . chr(10);
	    $output .= '  <tr>' . chr(10);
		//Only show this when it are expenses
		//use show tax
    	$output .= '  <tr id="use_tax_row">' . chr(10);
		$output .= '    <td>' . TEXT_USE_TAX . '</td>'  . chr(10);
		$output .= '    <td>' . html_checkbox_field('use_tax', '0', $this->use_tax) . '</td>'  . chr(10);
    	$output .= '  </tr>' . chr(10);
	    //default tax
	    $output .= '  <tr id="tax_row">' . chr(10);
		$output .= '    <td>' . TEXT_TAX . '</td>' . chr(10);
		$output .= '    <td>' . html_pull_down_menu('taxable', inv_calculate_tax_drop_down('v',false), $this->taxable) . '</td>' . chr(10);
	    $output .= '  </tr>' . chr(10);
    	$output .= '  <tr>' . chr(10);
	
		$output .= '  </tbody>' . chr(10);
    	$output .= '</table>' . chr(10);
    	return $output;
  	}
  
  	
  /* 
   * returns a string that will be a array in javascript.
   */
  
	function javascript_array(){
  		global $db;
	  	$sql = "select * from " . $this->db_table ;
    	$result = $db->Execute($sql);    
	  	$js_tills  = 'var ot_options  = new Array();' . chr(10);
  		$i = 0;
		while (!$result->EOF){
			$js_tills .= 'ot_options[' . $i. '] = new ot_option("' . $result->fields['till_id'] . '", "' . $result->fields['ot_id'] . '", "' . $result->fields['type'] . '", "' . $result->fields['use_tax'] . '", "' . $result->fields['taxable'] . '", "' . $result->fields['description'] . '");' . chr(10);
			$result->MoveNext();
			$i++;
		}
		return $js_tills;
  	}
  	
  	function get_transaction_info($id){
  		global $db;
  		$sql = "select * from " . $this->db_table . " where ot_id = " . $id;
        $result = $db->Execute($sql);
        foreach ($result->fields as $key => $value) $this->$key = $value;
  	}
  	
  	function __destruct(){
  		//print_r($this);
  	}
  
}
?>