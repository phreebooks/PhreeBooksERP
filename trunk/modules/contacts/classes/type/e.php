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
//  Path: /modules/contacts/classes/type/e.php
//  employees
namespace contacts\classes\type;
class e extends \contacts\classes\contacts{	
	public $security_token = SECURITY_ID_MAINTAIN_EMPLOYEES;
	public $help		   = '07.07.01.02';
	public $address_types  = array('em', 'es', 'eb', 'im');
    public $type           = 'e';
	
	public function __construct(){
		$this->page_title_new = sprintf(BOX_TEXT_NEW_TITLE, TEXT_EMPLOYEE);
		$this->tab_list[] = array('file'=>'template_e_history',	'tag'=>'history',  'order'=>10, 'text'=>TEXT_HISTORY);
		$this->tab_list[] = array('file'=>'template_notes',		'tag'=>'notes',    'order'=>40, 'text'=>TEXT_NOTES);
		$this->tab_list[] = array('file'=>'template_e_general',	'tag'=>'general',  'order'=> 1, 'text'=>TEXT_GENERAL);
		parent::__construct();
	}
	
  	function delete($id) {
	  	global $db; 
	  	if ( $this->id == '' ) $this->id = $id;
  		$result = $db->Execute("select admin_id from ".TABLE_USERS." where account_id =". $this->id);
		if ($result->RecordCount() == 0) {
	  		return $this->do_delete();
		}
		return ACT_ERROR_CANNOT_DELETE_EMPLOYEE;
  	}
}
?>