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
//  Path: /includes/classes/tabs.php
//
namespace core\classes;
class tabs {
    public  $help_path   = '';
    public  $module      = '';
    public  $extra_buttons = '';
    public  $title       = '';

    public function __construct (){
    	foreach ($_POST as $key => $value) $this->$key = db_prepare_input($value);
    	$this->id = isset($_POST['sID'])? $_POST['sID'] : $_GET['sID'];
    	$this->security_id = \core\classes\user::security_level(SECURITY_ID_CONFIGURATION);
    }

    public function btn_save($id = '') {
	  	global $admin;
	  	\core\classes\user::validate_security($this->security_id, 2); // security check
		$sql_data_array = array(
		  'module_id'   => $this->module,
		  'tab_name'    => $this->tab_name,
		  'description' => $this->description,
		  'sort_order'  => $this->sort_order,
		);
		
		$admin->DataBase->prepare("INSERT INTO ".TEXT_TABS." (id, module_id, tab_name, description, sort_order) VALUES ({$this->id},{$this->module},{$this->tab_name},{$this->description},{$this->sort_order}) ON DUPLICATE KEY UPDATE module_id ='{$this->module}', tab_name='{$this->tab_name}', description='{$this->description}', sort_order='{$this->sort_order}';");
		gen_add_audit_log($this->module .' '. TEXT_TABS . ' - '.  TEXT_UPDATE , $this->tab_name);
	    return true;
	}

    public function btn_delete($id = 0) {
	  	global $admin;
		\core\classes\user::validate_security($this->security_id, 4); // security check
		$result = $admin->DataBase->query("SELECT field_name FROM ".TABLE_EXTRA_FIELDS." WHERE tab_id='$id'");
		if ($result->fetch(\PDO::FETCH_NUM) > 0) throw new \core\classes\userException(INV_CATEGORY_CANNOT_DELETE . $result['field_name']);
		$result = $admin->DataBase->query("SELECT tab_name FROM ".TABLE_EXTRA_TABS." WHERE id='$id'");
		$admin->DataBase->exec("DELETE FROM ".TABLE_EXTRA_TABS." WHERE id=$id");
		gen_add_audit_log($this->module .' '. TEXT_TABS . ' - '. TEXT_DELETE, $result['tab_name']);
		return true;
	}

    public function build_main_html() {
	   global $admin;
	   $content = array();
	   $content['thead'] = array(
		  'value' => array(TEXT_TITLE, TEXT_DESCRIPTION, TEXT_SORT_ORDER, TEXT_ACTION),
		  'params'=> 'width="100%" cellspacing="0" cellpadding="1"',
	   );
	   	$sql = $admin->DataBase->prepare("select id, tab_name, description, sort_order from " . TABLE_EXTRA_TABS . " where module_id='" . $this->module . "'");
	   	$rowCnt = 0;
	   	$sql->execute();
		while ($result = $sql->fetch(\PDO::FETCH_LAZY)){
		  $actions = '';
		  if ($this->security_id > 1) $actions .= html_icon('actions/edit-find-replace.png', TEXT_EDIT,   'small', 'onclick="loadPopUp(\'tabs_edit\', ' . $result['id'] . ')"') . chr(10);
		  if ($this->security_id > 3) $actions .= html_icon('emblems/emblem-unreadable.png', TEXT_DELETE, 'small', 'onclick="if (confirm(\'' . EXTRA_TABS_DELETE_INTRO . '\')) subjectDelete(\'tabs\', ' . $result['id'] . ')"') . chr(10);
		  $content['tbody'][$rowCnt] = array(
		    array('value' => htmlspecialchars($result['tab_name']),
				  'params'=> 'style="cursor:pointer" onclick="loadPopUp(\'tabs_edit\',\''.$result['id'].'\')"'),
			array('value' => htmlspecialchars($result['description']),
				  'params'=> 'style="cursor:pointer" onclick="loadPopUp(\'tabs_edit\',\''.$result['id'].'\')"'),
			array('value' => $result['sort_order'],
				  'params'=> 'style="cursor:pointer" onclick="loadPopUp(\'tabs_edit\',\''.$result['id'].'\')"'),
		    array('value' => $actions,
                  'params'=> 'align="right"'),
		   );
	       $rowCnt++;
	   }
	   return html_datatable('tab_table', $content);
	}

    public function build_form_html($action, $id = '') {
	   global $admin;
	   if ($action <> 'new') $this = $admin->DataBase->query("SELECT * FROM " . TABLE_EXTRA_TABS . " WHERE id = " . $this->id);
	   $output  = '<table style="border-collapse:collapse;margin-left:auto; margin-right:auto;">' . chr(10);
	   $output .= '  <thead class="ui-widget-header">' . "\n";
	   $output .= '  <tr>' . chr(10);
	   $output .= '    <th colspan="2">' . ($action=='new' ? sprintf(TEXT_NEW_ARGS, TEXT_TAB) : sprintf(TEXT_EDIT_ARGS, TEXT_TAB)) . '</th>' . chr(10);
	   $output .= '  </tr>' . chr(10);
	   $output .= '  </thead>' . "\n";
	   $output .= '  <tbody class="ui-widget-content">' . "\n";
	   $output .= '  <tr>' . chr(10);
	   $output .= '    <td colspan="2">' . ($action=='new' ? TEXT_TAB_INSERT_INTRO : TEXT_PLEASE_MAKE_ANY_NECESSARY_CHANGES) . '</td>' . chr(10);
	   $output .= '  </tr>' . chr(10);
	   $output .= '  <tr>' . chr(10);
	   $output .= '    <td>' . TEXT_TAB_TITLE . '</td>' . chr(10);
	   $output .= '    <td>' . html_input_field('tab_name', $this->tab_name) . '</td>' . chr(10);
	   $output .= '  </tr>' . chr(10);
	   $output .= '  <tr>' . chr(10);
	   $output .= '    <td>' . TEXT_DESCRIPTION . '</td>' . chr(10);
	   $output .= '    <td>' . html_textarea_field('description', 30, 10, $this->description) . '</td>' . chr(10);
	   $output .= '  </tr>' . chr(10);
	   $output .= '  <tr>' . chr(10);
	   $output .= '    <td>' . TEXT_SORT_ORDER . '</td>' . chr(10);
	   $output .= '    <td>' . html_input_field('sort_order', $this->sort_order) . '</td>' . chr(10);
	   $output .= '  </tr>' . chr(10);
	   $output .= '  </tbody>' . "\n";
	   $output .= '</table>' . chr(10);
	   return $output;
	}

}

?>