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
//  Path: /modules/phreebooks/classes/tax_auths.php
//

class tax_auths {
	public $code        = 'tax_auths'; // needs to match class name
    public $db_table    = TABLE_TAX_AUTH;
    public $help_path   = '07.08.03.01';
    public $type        = 'c'; // choices are c for customers and v for vendors
    public $error       = false;
    
    public function __construct(){
        $this->security_id = $_SESSION['admin_security'][SECURITY_ID_CONFIGURATION];
        foreach ($_POST as $key => $value) $this->$key = db_prepare_input($value);
        $this->id = isset($_POST['sID'])? $_POST['sID'] : $_GET['sID'];
    }

  function btn_save($id = '') {
  	global $db, $messageStack;
	if ($this->security_id < 2) {
		$messageStack->add(ERROR_NO_PERMISSION,'error');
		return false;
	}
	$sql_data_array = array(
		'type'              => $this->type,
		'description_short' => $this->description_short,
		'description_long'  => $this->description_long,
		'account_id'        => $this->account_id,
		'vendor_id'         => $this->vendor_id,
		'tax_rate'          => $this->tax_rate,
	);
    if ($id) {
	  db_perform($this->db_table, $sql_data_array, 'update', "tax_auth_id = '" . $id . "'");
	  gen_add_audit_log(SETUP_TAX_AUTHS_LOG . TEXT_UPDATE, $this->description_short);
	} else  {
      db_perform($this->db_table, $sql_data_array);
	  gen_add_audit_log(SETUP_TAX_AUTHS_LOG . TEXT_ADD, $this->description_short);
	}
	return true;
  }

  function btn_delete($id = 0) {
  	global $db, $messageStack;
	if ($this->security_id < 4) {
		$messageStack->add(ERROR_NO_PERMISSION,'error');
		return false;
	}
	// Check for this authority being used in a tax rate calculation, if so do not delete
	$result = $db->Execute("select tax_auths from " . TABLE_JOURNAL_MAIN . " 
		where tax_auths like '%" . $id . "%'");
	while (!$result->EOF) {
	  $auth_ids = explode(':', $result->fields['tax_auths']);
	  for ($i = 0; $i < count($auth_ids); $i++) {
		if ($id == $auth_ids[$i]) {
		  $messageStack->add(SETUP_TAX_AUTHS_DELETE_ERROR,'error');
		  return false;
		}
	  }
	  $result->MoveNext();
	}

	// OK to delete
	$result = $db->Execute("select description_short from " . $this->db_table . " where tax_auth_id = '" . $id . "'");
	$db->Execute("delete from " . $this->db_table . " where tax_auth_id = '" . $id . "'");
	gen_add_audit_log(SETUP_TAX_AUTHS_LOG . TEXT_DELETE, $result->fields['description_short']);
	return true;
  }

  function build_main_html() {
  	global $db, $messageStack;
    $content = array();
	$content['thead'] = array(
	  'value' => array(SETUP_TAX_DESC_SHORT, TEXT_DESCRIPTION, SETUP_TAX_GL_ACCT, SETUP_TAX_RATE, TEXT_ACTION),
	  'params'=> 'width="100%" cellspacing="0" cellpadding="1"',
	);
    $result = $db->Execute("select tax_auth_id, description_short, description_long, account_id, tax_rate 
	  from " . $this->db_table . " where type = '" . $this->type . "'");
    $rowCnt = 0;
	while (!$result->EOF) {
	  $actions = '';
	  if ($this->security_id > 1) $actions .= html_icon('actions/edit-find-replace.png', TEXT_EDIT, 'small', 'onclick="loadPopUp(\''.$this->code.'_edit\', ' . $result->fields['tax_auth_id'] . ')"') . chr(10);
	  if ($this->security_id > 3) $actions .= html_icon('emblems/emblem-unreadable.png', TEXT_DELETE, 'small', 'onclick="if (confirm(\'' . SETUP_TAX_AUTH_DELETE_INTRO . '\')) subjectDelete(\''.$this->code.'\', ' . $result->fields['tax_auth_id'] . ')"') . chr(10);
	  $content['tbody'][$rowCnt] = array(
	    array('value' => htmlspecialchars($result->fields['description_short']),
			  'params'=> 'style="cursor:pointer" onclick="loadPopUp(\''.$this->code.'_edit\',\''.$result->fields['tax_auth_id'].'\')"'),
		array('value' => htmlspecialchars($result->fields['description_long']), 
			  'params'=> 'style="cursor:pointer" onclick="loadPopUp(\''.$this->code.'_edit\',\''.$result->fields['tax_auth_id'].'\')"'),
		array('value' => gen_get_type_description(TABLE_CHART_OF_ACCOUNTS, $result->fields['account_id']),
			  'params'=> 'style="cursor:pointer" onclick="loadPopUp(\''.$this->code.'_edit\',\''.$result->fields['tax_auth_id'].'\')"'),
		array('value' => $result->fields['tax_rate'],
			  'params'=> 'style="cursor:pointer" onclick="loadPopUp(\''.$this->code.'_edit\',\''.$result->fields['tax_auth_id'].'\')"'),
		array('value' => $actions,
			  'params'=> 'style="cursor:pointer" align="right"'),
	  );
      $result->MoveNext();
	  $rowCnt++;
    }
    return html_datatable(''.$this->code.'_'.$this->type.'_table', $content);
  }

  function build_form_html($action, $id = '') {
    global $db;
    if ($action <> 'new' && $this->error == false) {
        $sql = "select description_short, description_long, account_id, vendor_id, tax_rate 
	       from " . $this->db_table . " where tax_auth_id = " . $id;
        $result = $db->Execute($sql);
        foreach ($result->fields as $key => $value) $this->$key = $value;
	}
	$output  = '<table style="border-collapse:collapse;margin-left:auto; margin-right:auto;">' . chr(10);
	$output .= '  <thead class="ui-widget-header">' . "\n";
	$output .= '  <tr>' . chr(10);
	$output .= '    <th colspan="2">' . ($action=='new' ? SETUP_INFO_HEADING_NEW_TAX_AUTH : SETUP_INFO_HEADING_EDIT_TAX_AUTH) . '</th>' . chr(10);
    $output .= '  </tr>' . chr(10);
	$output .= '  </thead>' . "\n";
	$output .= '  <tbody class="ui-widget-content">' . "\n";
	$output .= '  <tr>' . chr(10);
	$output .= '    <td colspan="2">' . ($action=='new' ? SETUP_TAX_AUTH_INSERT_INTRO : SETUP_TAX_AUTH_EDIT_INTRO) . '</td>' . chr(10);
    $output .= '  </tr>' . chr(10);
	$output .= '  <tr>' . chr(10);
	$output .= '    <td>' . SETUP_INFO_DESC_SHORT . '</td>' . chr(10);
	$output .= '    <td>' . html_input_field('description_short', $this->description_short, 'size="16" maxlength="15"') . '</td>' . chr(10);
    $output .= '  </tr>' . chr(10);
	$output .= '  <tr>' . chr(10);
	$output .= '    <td>' . SETUP_INFO_DESC_LONG . '</td>' . chr(10);
	$output .= '    <td>' . html_input_field('description_long', $this->description_long, 'size="33" maxlength="64"') . '</td>' . chr(10);
    $output .= '  </tr>' . chr(10);
	$output .= '  <tr>' . chr(10);
	$output .= '    <td>' . SETUP_INFO_GL_ACCOUNT . '</td>' . chr(10);
	$output .= '    <td>' . html_pull_down_menu('account_id', gen_coa_pull_down(), $this->account_id) . '</td>' . chr(10);
    $output .= '  </tr>' . chr(10);
	$output .= '  <tr>' . chr(10);
	$output .= '    <td>' . SETUP_INFO_VENDOR_ID . '</td>' . chr(10);
	$output .= '    <td>' . html_pull_down_menu('vendor_id', gen_get_contact_array_by_type('v'), $this->vendor_id) . '</td>' . chr(10);
    $output .= '  </tr>' . chr(10);
	$output .= '  <tr>' . chr(10);
	$output .= '    <td>' . SETUP_INFO_TAX_RATE . '</td>' . chr(10);
	$output .= '    <td>' . html_input_field('tax_rate', $this->tax_rate) . '</td>' . chr(10);
    $output .= '  </tr>' . chr(10);
	$output .= '  </tbody>' . "\n";
    $output .= '</table>' . chr(10);
    return $output;
  }
}
?>