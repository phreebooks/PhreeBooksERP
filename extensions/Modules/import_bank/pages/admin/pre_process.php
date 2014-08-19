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
// |                                                                 |
// | The license that is bundled with this package is located in the |
// | file: /doc/manual/ch01-Introduction/license.html.               |
// | If not, see http://www.gnu.org/licenses/                        |
// +-----------------------------------------------------------------+
//  Path: /modules/import_bank/pages/admin/pre_process.php
//

/**************   Check user security   *****************************/
$security_level = \core\classes\user::validate(SECURITY_ID_CONFIGURATION);

/**************  include page specific files    *********************/
gen_pull_language($module);
gen_pull_language($module, 'admin');
/**************   page specific initialization  *************************/
$kt      = new \import_bank\classes\known_transactions();
/***************   Act on the action request   *************************/
switch ($_REQUEST['action']) {
  case 'save':
	\core\classes\user::validate_security($security_level, 4);
  	// save general tab
  	if($_POST['number_of_bank_accounts'] < NUMBER_OF_BANK_ACCOUNTS){
  		throw new \core\classes\userException(IMPORT_BANK_CAN_NOT_DECREASE_NUMBER_OF_BANK_ACCOUNTS);
  		$_POST['number_of_bank_accounts'] = NUMBER_OF_BANK_ACCOUNTS;
  	}else if($_POST['number_of_bank_accounts'] > NUMBER_OF_BANK_ACCOUNTS){
  		for($x=NUMBER_OF_BANK_ACCOUNTS; $x<=$_POST['number_of_bank_accounts']; $x++){
  			$result = $db->Execute("select id from " . TABLE_EXTRA_TABS . " where module_id='contacts' and tab_name = 'import_banking'");
			if ( $result->RecordCount() == 0 ){
				$entry = array(	'module_id'	=> 'contacts',
				 				'tab_name'	=> 'import_banking',
								'sort_order'=> '100' );
				db_perform(TABLE_EXTRA_TABS, $entry, 'insert');
				$tab_id = $db->insert_ID();
			}else {
				$tab_id = $result->fields['id'];
			}
			$entry = array(	'module_id'	  => 'contacts',
				 			'tab_id'	  => $tab_id,
							'entry_type'  => 'text',
							'field_name'  => 'bank_account_'.$x,
							'description' => 'Bank Account',
							'params'	  => 'a:4:{s:4:"type";s:4:"text";s:12:"contact_type";s:16:"customer:vendor:";s:6:"length";i:32;s:7:"default";s:0:"";}');
			db_perform(TABLE_EXTRA_FIELDS, $entry, 'insert');
			$db->Execute("ALTER TABLE ".TABLE_CONTACTS." ADD bank_account_".$x." varchar(32) default NULL");

  		}
  	}
	foreach ($admin_classes['import_bank']->keys as $key => $default) {
	  $field = strtolower($key);
      if (isset($_POST[$field])) write_configure($key, $_POST[$field]);
    }
	gen_redirect(html_href_link(FILENAME_DEFAULT, gen_get_all_get_params(array('action')), 'SSL'));
	$messageStack->add(TEXT_SAVED_IMPORT_BANK_CONFIG, 'success');
    break;
  case 'delete':
	\core\classes\user::validate_security($security_level, 4); // security check
    $subject = $_POST['subject'];
    $id      = $_POST['rowSeq'];
	if (!$subject || !$id) break;
    $$subject->btn_delete($id);
	break;
  default:
}

/*****************   prepare to display templates  *************************/
// build some general pull down arrays
$all_chart = gen_coa_pull_down(2, false, true, false);
$include_header   = true;
$include_footer   = true;
$include_template = 'template_main.php';
define('PAGE_TITLE', TEXT_BANK_IMPORT_SETTINGS);

?>