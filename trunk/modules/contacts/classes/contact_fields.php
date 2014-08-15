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
//  Path: /modules/contacts/classes/contact_fields.php
//
require_once(DIR_FS_MODULES . 'phreedom/classes/fields.php');

class contact_fields extends fields{
	public  $help_path   = '';
	public  $title       = '';
	public  $module      = 'contacts';
	public  $db_table    = TABLE_CONTACTS;
    public  $type_desc   = TEXT_CONTACT_TYPE;
	public  $type_params = 'contact_type';
	public  $extra_buttons = '';
  
  public function __construct($sync = true){
	$this->type_array[] = array('id' => 'c', 'text' => TEXT_CUSTOMER);
    $this->type_array[] = array('id' => 'v', 'text' => TEXT_VENDOR);
    $this->type_array[] = array('id' => 'e', 'text' => TEXT_EMPLOYEE);
    $this->type_array[] = array('id' => 'b', 'text' => TEXT_BRANCH);
    parent::__construct($sync);    
  }

}
?>