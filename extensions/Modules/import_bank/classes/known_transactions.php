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
//  Path: /modules/import_bank/classes/known_transactions.php
//
namespace import_bank\classes;
class known_transactions {
	public $code                    = 'known_transactions';
    public $db_table     			= TABLE_IMPORT_BANK;
    public $help_path   			= '';

    public function __construct(){
         $this->security_id           = \core\classes\user::validate(SECURITY_ID_CONFIGURATION);
         foreach ($_POST as $key => $value) $this->$key = db_prepare_input($value);
         $this->id = isset($_POST['sID'])? $_POST['sID'] : $_GET['sID'];
    }

  function btn_save($id = '') {
  	global $db, $currencies;
	\core\classes\user::validate_security($this->security_id, 2);
	if ($this->gl_acct_id == '') throw new \core\classes\userException(GL_SELECT_STD_CHART);
	$sql_data_array = array(
		'description' 		    => $this->description,
		'gl_acct_id'  		    => $this->gl_acct_id,
		'bank_account' 		  	=> $this->bank_account,
	);

    if ($id) {
	  db_perform($this->db_table, $sql_data_array, 'update', "kt_id = '$id'");
	  gen_add_audit_log(TEXT_TRANSACTION_TEMPLATE ." - " . TEXT_UPDATE, $this->description);
	} else  {
      db_perform($this->db_table, $sql_data_array);
	  gen_add_audit_log(TEXT_TRANSACTION_TEMPLATE ." - " . TEXT_ADD, $this->description);
	}
	return true;
  }

  function btn_delete($id = 0) {
  	global $db;
	\core\classes\user::validate_security($this->security_id, 4);
	// OK to delete
	$result = $db->Execute("select description from " . $this->db_table . " where kt_id = '$id'");
	$db->Execute("delete from " . $this->db_table . " where kt_id = '$id'");
	gen_add_audit_log(TEXT_TRANSACTION_TEMPLATE ." - " . TEXT_DELETE, $result->fields['description']);
	return true;
  }

  function build_main_html() {
  	global $db, $currencies;
    $content = array();
	$content['thead'] = array(
	  'value' => array(TEXT_DESCRIPTION, TEXT_GL_ACCOUNT,  TEXT_BANK_ACCOUNT,TEXT_ACTION),
	  'params'=> 'width="100%" cellspacing="0" cellpadding="1"',
	);
    $result = $db->Execute("select * from " . $this->db_table );
    $rowCnt = 0;
	while (!$result->EOF) {
	  $actions = '';
	  if ($this->security_id > 1) $actions .= html_icon('actions/edit-find-replace.png', TEXT_EDIT, 'small', 'onclick="loadPopUp(\''.$this->code.'_edit\', ' . $result->fields['kt_id'] . ')"') . chr(10);
	  if ($this->security_id > 3) $actions .= html_icon('emblems/emblem-unreadable.png', TEXT_DELETE, 'small', 'onclick="if (confirm(\'' . SETUP_TILL_DELETE_INTRO . '\')) subjectDelete(\''.$this->code.'\', ' . $result->fields['kt_id'] . ')"') . chr(10);
	  $content['tbody'][$rowCnt] = array(
	    array('value' => htmlspecialchars($result->fields['description']),
			  'params'=> 'style="cursor:pointer" onclick="loadPopUp(\''.$this->code.'_edit\',\''.$result->fields['kt_id'].'\')"'),
		array('value' => gen_get_type_description(TABLE_CHART_OF_ACCOUNTS, $result->fields['gl_acct_id']),
			  'params'=> 'style="cursor:pointer" onclick="loadPopUp(\''.$this->code.'_edit\',\''.$result->fields['kt_id'].'\')"'),
		array('value' => $result->fields['bank_account'],
			  'params'=> 'style="cursor:pointer" onclick="loadPopUp(\''.$this->code.'_edit\',\''.$result->fields['kt_id'].'\')"'),
		array('value' => $actions,
			  'params'=> 'align="right"'),
	  );
      $result->MoveNext();
	  $rowCnt++;
    }
    return html_datatable(''.$this->code.'_table', $content);
  }

  function build_form_html($action, $id = '') {
    global $db;
    if ($action <> 'new') {
        $sql = "select * from " . $this->db_table . " where kt_id = " . $id;
        $result = $db->Execute($sql);
        foreach ($result->fields as $key => $value) $this->$key = $value;
	}
	$output  = '<table style="border-collapse:collapse;margin-left:auto; margin-right:auto;">' . chr(10);
	$output .= '  <thead class="ui-widget-header">' . "\n";
	$output .= '  <tr>' . chr(10);
	$output .= '    <th colspan="2">' . ($action=='new' ? sprintf(TEXT_NEW_ARGS, TEXT_TRANSACTION_TEMPLATE) : sprintf(TEXT_EDIT_ARGS, TEXT_TRANSACTION_TEMPLATE)) . '</th>' . chr(10);
    $output .= '  </tr>' . chr(10);
	$output .= '  </thead>' . "\n";
	$output .= '  <tbody class="ui-widget-content">' . "\n";
	$output .= '  <tr>' . chr(10);
	$output .= '    <td>' . TEXT_BANK_ACCOUNT_OR_IBAN . '</td>' . chr(10);
	$output .= '    <td>' . html_input_field('bank_account', $this->bank_account) . '</td>' . chr(10);
    $output .= '  </tr>' . chr(10);
	$output .= '  <tr>' . chr(10);
	$output .= '    <td>' . TEXT_DESCRIPTION . '</td>' . chr(10);
	$output .= '    <td>' . html_input_field('description', $this->description, 'size="16" maxlength="15"') . '</td>' . chr(10);
    $output .= '  </tr>' . chr(10);
	$output .= '  <tr>' . chr(10);
	$output .= '    <td>' . TEXT_GL_ACCOUNT . '</td>' . chr(10);
	$output .= '    <td>' . html_pull_down_menu('gl_acct_id', gen_coa_pull_down(SHOW_FULL_GL_NAMES, true, true, false, false), $this->gl_acct_id) . '</td>' . chr(10);
    $output .= '  </tr>' . chr(10);
    $output .= '  </tbody>' . chr(10);
    $output .= '</table>' . chr(10);
    return $output;
  }

  function __destruct(){
  	//print_r($this);
  }

}
?>