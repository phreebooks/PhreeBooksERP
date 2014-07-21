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
//  Path: /modules/phreebooks/classes/chart_of_accounts.php
//

require_once(DIR_FS_MODULES . 'phreebooks/defaults.php');
require_once(DIR_FS_MODULES . 'phreebooks/functions/phreebooks.php');

class chart_of_accounts {
	public $db_table      = TABLE_CHART_OF_ACCOUNTS;
    public $title         = GL_POPUP_WINDOW_TITLE;
    public $extra_buttons = false;
    public $help_path     = '07.06.01';
    public $error         = false;
    
    public function __construct(){
        foreach ($_POST as $key => $value) $this->$key = db_prepare_input($value);
        if(!isset($this->id))$this->id = isset($_GET['sID'])?$_GET['sID']:$_POST['rowSeq'];
        $this->security_id   = $_SESSION['admin_security'][SECURITY_ID_CONFIGURATION];
    }

  function btn_save($id = '') {
  	global $db, $messageStack, $coa_types_list;
	if ($this->security_id < 2) {
	    $messageStack->add(ERROR_NO_PERMISSION,'error');
	    return false;
	}   
	$this->heading_only     = $this->heading_only == 1 ? '1' : '0';
	$this->account_inactive = $this->account_inactive == 1 ? '1' : '0';
	if ($this->account_type == '') {
	    $messageStack->add(ERROR_ACCT_TYPE_REQ,'error');
	    $this->error = true;
	    return false;
	}
	if (!$this->primary_acct_id == ''){
	    $result = $db->Execute("select account_type from " . $this->db_table . " where id = '" . $this->primary_acct_id . "'");
        if( $result->fields['account_type'] <> $this->account_type){
            $messageStack->add('set account_type to '. $coa_types_list[$result->fields['account_type']]['text']. ' this is the same as the parent','error');
            $this->error = true;
            return false;
        }
	}	
	if ($this->heading_only == 1 && $this->rowSeq <> 0) { // see if this was a non-heading account converted to a heading account
	   $sql = "select max(debit_amount) as debit, max(credit_amount) as credit, max(beginning_balance) as beg_bal 
		from " . TABLE_CHART_OF_ACCOUNTS_HISTORY . " where account_id = '" . $this->id . "'";
	   $result = $db->Execute($sql);
	   if ($result->fields['debit'] <> 0 || $result->fields['credit'] <> 0 || $result->fields['beg_bal'] <> 0) {
		  $messageStack->add(GL_ERROR_CANT_MAKE_HEADING, 'error');
		  $this->error = true;
		  return false;
	   }
	}
	$sql_data_array = array(
	  'description'      => $this->description,
	  'heading_only'     => $this->heading_only,
	  'primary_acct_id'  => $this->primary_acct_id,
	  'account_type'     => $this->account_type,
	  'account_inactive' => $this->account_inactive,
	);
    if ($this->rowSeq <> 0) {
	  db_perform($this->db_table, $sql_data_array, 'update', "id = '" . $this->id . "'");
      gen_add_audit_log(GL_LOG_CHART_OF_ACCOUNTS . TEXT_UPDATE, $this->id);
	} else  { 
	  $sql_data_array['id'] = $this->id;
      db_perform($this->db_table, $sql_data_array);
	  gen_add_audit_log(GL_LOG_CHART_OF_ACCOUNTS . TEXT_ADD, $this->id);
	}
	build_and_check_account_history_records(); // add/modify account to history table
	return true;
  }

  function btn_delete($id = 0) {
  	global $db, $messageStack;
	if ($this->security_id < 4) {
	  $messageStack->add(ERROR_NO_PERMISSION,'error');
	  return false;
	}
	// Don't allow delete if there is account activity for this account
	$sql = "select max(debit_amount) as debit, max(credit_amount) as credit, max(beginning_balance) as beg_bal 
		from " . TABLE_CHART_OF_ACCOUNTS_HISTORY . " where account_id = '" . $id . "'";
	$result = $db->Execute($sql);
	if ($result->fields['debit'] <> 0 || $result->fields['credit'] <> 0 || $result->fields['beg_bal'] <> 0) {
	  $messageStack->add(GL_ERROR_CANT_DELETE, 'error');
	  return false;
	}
	// OK to delete
	$db->Execute("delete from " . $this->db_table . " where id = '" . $id . "'");
	modify_account_history_records($id, $add_acct = false);
	gen_add_audit_log(GL_LOG_CHART_OF_ACCOUNTS . TEXT_DELETE, $id);
	return true;
  }

  function build_main_html() {
  	global $db, $messageStack;
    $content = array();
	$content['thead'] = array(
	  'value'  => array(GL_HEADING_ACCOUNT_NAME, TEXT_ACCT_DESCRIPTION, GL_INFO_ACCOUNT_TYPE, GL_HEADING_SUBACCOUNT, TEXT_ACTION),
	  'params' => 'width="100%" cellspacing="0" cellpadding="1"',
	);
    $result = $db->Execute("select id, description, heading_only, primary_acct_id, account_type, account_inactive from " . $this->db_table);
    $rowCnt = 0;
	while (!$result->EOF) {
	  $bkgnd = ($result->fields['account_inactive']) ? 'class="ui-state-error" ' : '';
	  $account_type_desc = constant('COA_' . str_pad($result->fields['account_type'], 2, "0", STR_PAD_LEFT) . '_DESC');
      if ($result->fields['heading_only']) {
	    $account_type_desc = TEXT_HEADING;
		$bkgnd = 'class="ui-state-active" ';
	  }
	  $actions = '';
	  if ($this->security_id > 1) $actions .= html_icon('actions/edit-find-replace.png', TEXT_EDIT, 'small', 'onclick="loadPopUp(\'chart_of_accounts_edit\', \'' . $result->fields['id'] . '\')"') . "\n";
	  if ($this->security_id > 3) $actions .= html_icon('emblems/emblem-unreadable.png', TEXT_DELETE, 'small', 'onclick="if (confirm(\'' . GL_INFO_DELETE_INTRO . '\')) subjectDelete(\'chart_of_accounts\', ' . $result->fields['id'] . ')"') . "\n";
	  $content['tbody'][$rowCnt] = array(
	    array('value' => htmlspecialchars($result->fields['id']),
			  'params'=> $bkgnd.'style="cursor:pointer" onclick="loadPopUp(\'chart_of_accounts_edit\',\''.$result->fields['id'].'\')"'),
		array('value' => htmlspecialchars($result->fields['description']), 
			  'params'=> $bkgnd.'style="cursor:pointer" onclick="loadPopUp(\'chart_of_accounts_edit\',\''.$result->fields['id'].'\')"'),
		array('value' => htmlspecialchars($account_type_desc),
			  'params'=> $bkgnd.'style="cursor:pointer" onclick="loadPopUp(\'chart_of_accounts_edit\',\''.$result->fields['id'].'\')"'),
		array('value' => $result->fields['primary_acct_id'] ? TEXT_YES . ' - ' . htmlspecialchars($result->fields['primary_acct_id']) : TEXT_NO,
			  'params'=> $bkgnd.'style="cursor:pointer" onclick="loadPopUp(\'chart_of_accounts_edit\',\''.$result->fields['id'].'\')"'),
		array('value' => $actions,
			  'params'=> $bkgnd.'align="right"'),
	  );
      $result->MoveNext();
	  $rowCnt++;
    }
    return html_datatable('coa_table', $content);
  }

  function build_form_html($action, $id = '') {
    global $db, $coa_types_list;
    if ($action <> 'new' && $this->error == false) {
        $sql = "select * from " . $this->db_table . " where id = '" . $this->id . "'";
        $result = $db->Execute($sql);
        foreach ($result->fields as $key => $value) $this->$key = $value;
    }
    $output  = ($action == 'new' ? html_hidden_field('new', 1 ) :'')  . chr(10);
	$output .= '<table style="border-collapse:collapse;margin-left:auto; margin-right:auto;">' . "\n";
	$output .= '  <thead class="ui-widget-header">' . "\n";
	$output .= '  <tr>' . "\n";
	$output .= '    <th colspan="2">' . ($action == 'new' ? GL_INFO_NEW_ACCOUNT : GL_INFO_EDIT_ACCOUNT) . '</th>' . "\n";
    $output .= '  </tr>' . "\n";
	$output .= '  </thead>' . "\n";
	$output .= '  <tbody class="ui-widget-content">' . "\n";
	$output .= '  <tr>' . "\n";
	$output .= '    <td colspan="2">' . ($action == 'new' ? GL_INFO_INSERT_INTRO : GL_EDIT_INTRO) . '</td>' . "\n";
    $output .= '  </tr>' . "\n";
	$output .= '  <tr>' . "\n";
	$output .= '    <td>' . TEXT_GL_ACCOUNT . '</td>' . "\n";
	$output .= '    <td>' . ($action == 'new' ? html_input_field('id', $this->id) : $this->id) . '</td>' . "\n";
    $output .= '  </tr>' . "\n";
	$output .= '  <tr>' . "\n";
	$output .= '    <td>' . TEXT_ACCT_DESCRIPTION . '</td>' . "\n";
	$output .= '    <td>' . html_input_field('description', $this->description, 'size="48" maxlength="64"') . '</td>' . "\n";
    $output .= '  </tr>' . "\n";
	$output .= '  <tr>' . "\n";
	$output .= '    <td>' . GL_INFO_HEADING_ONLY . '</td>' . "\n";
	$output .= '    <td>' . html_checkbox_field('heading_only', '1', $this->heading_only) . '</td>' . "\n";
    $output .= '  </tr>' . "\n";
	$output .= '  <tr>' . "\n";
	$output .= '    <td>' . GL_INFO_PRIMARY_ACCT_ID . '</td>' . "\n";
	$output .= '    <td>' . html_pull_down_menu('primary_acct_id', gen_coa_pull_down(SHOW_FULL_GL_NAMES, true, true, true), $this->primary_acct_id) . '</td>' . "\n";
    $output .= '  </tr>' . "\n";
    if ($this->primary_acct_id == '' || $this->error == true){
	    $output .= '  <tr>' . "\n";
	    $output .= '    <td>' . GL_INFO_ACCOUNT_TYPE . '</td>' . "\n";
	    $output .= '    <td>' . html_pull_down_menu('account_type', $coa_types_list, $this->account_type) . '</td>' . "\n";
        $output .= '  </tr>' . "\n";
    }else{
    	$sql = "select account_type from " . $this->db_table . " where id = '" . $this->primary_acct_id . "'";
    	$result = $db->Execute($sql);
        $output .= html_hidden_field('account_type', $result->fields['account_type'] )   . chr(10);	
    }
	$output .= '  <tr>' . "\n";
	$output .= '    <td>' . GL_INFO_ACCOUNT_INACTIVE . '</td>' . "\n";
	$output .= '    <td>' . html_checkbox_field('account_inactive', '1', $this->account_inactive == 1 ? true : false) . '</td>' . "\n";
    $output .= '  </tr>'  . "\n";
	$output .= '  </tbody>' . "\n";
    $output .= '</table>' . "\n";
    return $output;
  }
  public function __destruct(){
  	//print_r($this);
  }
}
?>
