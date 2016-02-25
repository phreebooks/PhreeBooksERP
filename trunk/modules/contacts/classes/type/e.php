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
    public $dept_rep_id 	= EMP_DEFAULT_DEPARTMENT; //will overwrite if exists in database.

	public function __construct(){
		$this->tab_list[] = array('file'=>'template_e_history',	'tag'=>'history',  'order'=>10, 'text'=>TEXT_HISTORY);
		$this->tab_list[] = array('file'=>'template_notes',		'tag'=>'notes',    'order'=>40, 'text'=>TEXT_NOTES);
		$this->tab_list[] = array('file'=>'template_e_general',	'tag'=>'general',  'order'=> 1, 'text'=>TEXT_GENERAL);
		$this->employee_types = array(
			'e' => TEXT_EMPLOYEE,
			's' => TEXT_SALES_REP,
			'b' => TEXT_BUYER,
		);
		parent::__construct();
	}

	public function getContact() {
		global $admin;
		if ($this->id == '' && !$this->aid == ''){
			$sql = $admin->DataBase->prepare("SELECT * FROM ".TABLE_ADDRESS_BOOK." WHERE address_id = {$this->aid}");
			$sql->execute();
			$result = $sql->fetch(\PDO::FETCH_LAZY);
			// Load contact info, including custom fields
			$sql = $admin->DataBase->prepare("SELECT * FROM ".TABLE_CONTACTS." WHERE id = {$result['ref_id']}");
			$sql->execute();
			$this[] = $sql->fetch(\PDO::FETCH_LAZY);
		}
		// expand attachments
		$this->attachments = $result['attachments'] ? unserialize($result['attachments']) : array();
		// Load the address book
		$sql = $admin->DataBase->query("SELECT * FROM ".TABLE_ADDRESS_BOOK." WHERE ref_id = {$this->id} ORDER BY primary_name");
		$sql->execute();
		$this->address = array();
		while ($result = $sql->fetch(\PDO::FETCH_LAZY)) {
			$i = sizeof($this->address[$result['type']]);
			$this->address[$result['type']][$i] = get_object_vars ($result);
		}
		// load payment info
		if ($_SESSION['admin_encrypt'] && ENABLE_ENCRYPTION) {
			$sql = $admin->DataBase->prepare("SELECT id, hint, enc_value FROM ".TABLE_DATA_SECURITY." WHERE module='contacts' and ref_1={$this->id}");
			$sql->execute();
			while ($result = $sql->fetch(\PDO::FETCH_LAZY)) {
				$val = explode(':', \core\classes\encryption::decrypt($_SESSION['admin_encrypt'], $result['enc_value']));
				$this->payment_data[] = array(
						'id'   => $result['id'],
						'name' => $val[0],
						'hint' => $result['hint'],
						'exp'  => $val[2] . '/' . $val[3],
				);
			}
		}
		$sql = $admin->DataBase->prepare("SELECT * FROM ".TABLE_CONTACTS_LOG." WHERE contact_id = {$this->id} ORDER BY log_date DESC");
		$sql->execute();
		while ($result = $sql->fetch(\PDO::FETCH_LAZY)) {
			$i = sizeof($this->crm_log);
			foreach ($result as $key => $value) $this->crm_log[$i] = get_object_vars ($result);
			if ( $this->contact_first != '' || $this->contact_last != '') {
				$this->crm_log[$i]['with'] = $this->contact_first . ' ' . $this->contact_last;
			} else {
				$this->crm_log[$i]['with'] = $this->short_name . ' ' . $this->address["{$this->type}m"][0]['primary_name'];
			}
		}
		// load contacts info
		$sql = $admin->DataBase->prepare("SELECT * FROM ".TABLE_DEPARTMENTS." WHERE id={$this->id}");
		$sql->execute();
		$this->department = $sql->fetchAll() ;
		// load sales reps
		$this->sales_rep_array = gen_get_rep_ids($basis->cInfo->contact->type);
	}

  	function delete() {
	  	global $admin;
	  	if ( $this->id == '' ) $this->id = $id;
  		$result = $admin->DataBase->query("SELECT admin_id FROM ".TABLE_USERS." WHERE account_id =". $this->id);
		if ($result->fetch(\PDO::FETCH_NUM) != 0) throw new \core\classes\userException(ACT_ERROR_CANNOT_DELETE_EMPLOYEE);
	  	parent::delete();
  	}

  	function list_row ($js_function = "submitSeq") {
  		\core\classes\messageStack::debug_log("executing ".__METHOD__ ." of class ". get_class($admin_class));
  		$security_level = \core\classes\user::validate($this->security_token); // in this case it must be done after the class is defined for
  		$bkgnd          = ($this->inactive) ? ' style="background-color:pink"' : '';
  		$attach_exists  = $this->attachments ? true : false;
  		echo "<td $bkgnd onclick='$js_function( $this->id, \"editContact\")'>". htmlspecialchars($this->short_name) ."</td>";
  		echo "<td $bkgnd onclick='$js_function( $this->id, \"editContact\")'>". htmlspecialchars($this->contact_first . ' ' . $this->contact_last). "</td>";
  		echo "<td        onclick='$js_function( $this->id, \"editContact\")'>". htmlspecialchars($this->address1) ."</td>";
  		echo "<td        onclick='$js_function( $this->id, \"editContact\")'>". htmlspecialchars($this->city_town)."</td>";
  		echo "<td        onclick='$js_function( $this->id, \"editContact\")'>". htmlspecialchars($this->state_province)."</td>";
  		echo "<td        onclick='$js_function( $this->id, \"editContact\")'>". htmlspecialchars($this->postal_code)."</td>";
  		echo "<td 	     onclick='$js_function( $this->id, \"editContact\")'>". htmlspecialchars($this->telephone1)."</td>";
  		echo "<td align='right'>";
  		if ($js_function == "submitSeq") {
	  		// build the action toolbar
  			if ($security_level > 1) echo html_icon('mimetypes/x-office-presentation.png', TEXT_SALES, 'small', 	"onclick='contactChart(\"annual_sales\", $this->id)'") . chr(10);
  			if ($security_level > 1) echo html_icon('actions/edit-find-replace.png', TEXT_EDIT, 'small', 			"onclick='submitSeq($this->id, \"editContact\")'") . chr(10);
	  		if ($attach_exists) 	 echo html_icon('status/mail-attachment.png', TEXT_DOWNLOAD_ATTACHMENT,'small', "onclick='submitSeq($this->id, \"ContactAttachmentDownloadFirst\", true)'") . chr(10);
  			if ($security_level > 3) echo html_icon('emblems/emblem-unreadable.png', TEXT_DELETE, 'small', 			"onclick='if (confirm(\"" . ACT_WARN_DELETE_ACCOUNT . "\")) submitSeq($this->id, \"DeleteContact\")'") . chr(10);
  		}else if ($js_function == "setReturnAccount"){
  			switch ($this->journal_id) {
  				case  6:
  				case  7:
  				case 12:
  				case 13:
  					switch ($this->journal_id) {
  						case  6: $search_journal = 4;  break;
  						case  7: $search_journal = 6;  break;
  						case 12: $search_journal = 10; break;
  						case 13: $search_journal = 12; break;
  					}
  					$open_order_array = $this->load_orders($search_journal);
  					if ($open_order_array) {
  						echo html_pull_down_menu('open_order_' . $this->id, $open_order_array, '', "onchange='setReturnOrder(\"{$this->id}\")'");
  					}
  			}
  		}
  		echo "</td>";
  	 }
}
?>