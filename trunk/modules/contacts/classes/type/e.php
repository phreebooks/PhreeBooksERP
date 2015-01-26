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
//  Path: /modules/contacts/classes/type/e.php
//  employees
namespace contacts\classes\type;
class e extends \contacts\classes\contacts{
	public $security_token	= SECURITY_ID_MAINTAIN_EMPLOYEES;
	public $help			= '07.07.01.02';
	public $address_types	= array('em', 'es', 'eb', 'im');
    public $type			= 'e';
    public $title			= TEXT_EMPLOYEE;

	public function __construct(){
		$this->tab_list[] = array('file'=>'template_e_history',	'tag'=>'history',  'order'=>10, 'text'=>TEXT_HISTORY);
		$this->tab_list[] = array('file'=>'template_notes',		'tag'=>'notes',    'order'=>40, 'text'=>TEXT_NOTES);
		$this->tab_list[] = array('file'=>'template_e_general',	'tag'=>'general',  'order'=> 1, 'text'=>TEXT_GENERAL);
		parent::__construct();
	}

  	function delete($id) {
	  	global $admin;
	  	if ( $this->id == '' ) $this->id = $id;
  		$result = $admin->DataBase->query("select admin_id from ".TABLE_USERS." where account_id =". $this->id);
		if ($result->rowCount() == 0) {
	  		return $this->do_delete();
		}
		return ACT_ERROR_CANNOT_DELETE_EMPLOYEE;
  	}

  	function contant_list_row () {
  		\core\classes\messageStack::debug_log("executing ".__METHOD__ ." of class ". get_class($admin_class));
  		$security_level = \core\classes\user::validate($this->security_token); // in this case it must be done after the class is defined for
  		$bkgnd          = ($this->inactive) ? ' style="background-color:pink"' : '';
  		$attach_exists  = $this->attachments ? true : false;
  		echo "<td $bkgnd onclick='submitSeq( $this->id, \"edit\")'>". htmlspecialchars($this->short_name) ."</td>";
  		echo "<td $bkgnd onclick='submitSeq( $this->id, \"edit\")'>". htmlspecialchars($this->contact_first . ' ' . $this->contact_last). "</td>";
  		echo "<td        onclick='submitSeq( $this->id, \"edit\")'>". htmlspecialchars($this->address1) ."</td>";
  		echo "<td        onclick='submitSeq( $this->id, \"edit\")'>". htmlspecialchars($this->city_town)."</td>";
  		echo "<td        onclick='submitSeq( $this->id, \"edit\")'>". htmlspecialchars($this->state_province)."</td>";
  		echo "<td        onclick='submitSeq( $this->id, \"edit\")'>". htmlspecialchars($this->postal_code)."</td>";
  		echo "<td 	     onclick='submitSeq( $this->id, \"edit\")'>". htmlspecialchars($this->telephone1)."</td>";
  		echo "<td align='right'>";
  		// build the action toolbar
  		if ($security_level > 1) echo html_icon('mimetypes/x-office-presentation.png', TEXT_SALES, 'small', 	"onclick='contactChart(\"annual_sales\", $this->id)'") . chr(10);
  		if ($security_level > 1) echo html_icon('actions/edit-find-replace.png', TEXT_EDIT, 'small', 			"onclick='submitSeq($this->id, \"edit\")'") . chr(10);
  		if ($attach_exists) 	 echo html_icon('status/mail-attachment.png', TEXT_DOWNLOAD_ATTACHMENT,'small', "onclick='submitSeq($this->id, \"dn_attach\", true)'") . chr(10);
  		if ($security_level > 3) echo html_icon('emblems/emblem-unreadable.png', TEXT_DELETE, 'small', 			"onclick='if (confirm(\"" . ACT_WARN_DELETE_ACCOUNT . "\")) submitSeq($this->id, \"delete\")'") . chr(10);
  		echo "</td>";
  	 }
}
?>