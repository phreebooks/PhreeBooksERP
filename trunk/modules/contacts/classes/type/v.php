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
//  Path: /modules/contacts/classes/type/v.php
//
namespace contacts\classes\type;
class v extends \contacts\classes\contacts{	
	public $security_token = SECURITY_ID_MAINTAIN_VENDORS;
	public $auto_type      = AUTO_INC_VEND_ID;
	public $auto_field     = 'next_vend_id_num';
	public $journals	   = '6,7,21';
	public $help		   = '07.02.02.02';
	public $address_types  = array('vm', 'vs', 'vb');
	public $type           = 'v';
	
	public function __construct(){
		$this->page_title_new = sprintf(BOX_TEXT_NEW_TITLE, TEXT_VENDOR);
		$this->tab_list[] = array('file'=>'template_addbook',	'tag'=>'addbook',  'order'=>20, 'text'=>TEXT_ADDRESS_BOOK);
		$this->tab_list[] = array('file'=>'template_contacts',	'tag'=>'contacts', 'order'=> 5, 'text'=>TEXT_CONTACTS);
		$this->tab_list[] = array('file'=>'template_history',	'tag'=>'history',  'order'=>10, 'text'=>TEXT_HISTORY);
		$this->tab_list[] = array('file'=>'template_notes',		'tag'=>'notes',    'order'=>40, 'text'=>TEXT_NOTES);
		$this->tab_list[] = array('file'=>'template_general',	'tag'=>'general',  'order'=> 1, 'text'=>TEXT_GENERAL);
		parent::__construct();
	}
}
?>