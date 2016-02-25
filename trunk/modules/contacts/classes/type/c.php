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
//  Path: /modules/contacts/classes/type/c.php
//  customers
namespace contacts\classes\type;
class c extends \contacts\classes\contacts{
	public $terms_type		= 'AR';
	public $credit_limit     = AR_CREDIT_LIMIT_AMOUNT;
	public $discount_percent = AR_PREPAYMENT_DISCOUNT_PERCENT;
	public $discount_days    = AR_PREPAYMENT_DISCOUNT_DAYS;
	public $num_days_due     = AR_NUM_DAYS_DUE;
	public $security_token	= SECURITY_ID_MAINTAIN_CUSTOMERS;
	public $auto_type		= AUTO_INC_CUST_ID;
	public $auto_field		= 'next_cust_id_num';
	public $journals		= '12,13,19';
	public $help			= '07.03.02.02';
	public $help_terms		= '07.03.02.04';
	public $address_types	= array('cm', 'cs', 'cb');
	public $type			= 'c';
	public $title			= TEXT_CUSTOMER;
	public $contact_level	= 'r';

	public function __construct(){
		$this->tab_list[] = array('file'=>'template_payment',	'tag'=>'payment',  'order'=>30, 'text'=>TEXT_PAYMENT);
		$this->tab_list[] = array('file'=>'template_addbook',	'tag'=>'addbook',  'order'=>20, 'text'=>TEXT_ADDRESS_BOOK);
		$this->tab_list[] = array('file'=>'template_contacts',	'tag'=>'contacts', 'order'=> 5, 'text'=>TEXT_CONTACTS);
		$this->tab_list[] = array('file'=>'template_history',	'tag'=>'history',  'order'=>10, 'text'=>TEXT_HISTORY);
		$this->tab_list[] = array('file'=>'template_notes',		'tag'=>'notes',    'order'=>40, 'text'=>TEXT_NOTES);
		$this->tab_list[] = array('file'=>'template_general',	'tag'=>'general',  'order'=> 1, 'text'=>TEXT_GENERAL);
		parent::__construct();
		$this->contacts_levels = array(
			'r' => array('id' => 'r', 'text'=> TEXT_RETAIL),
			'd' => array('id' => 'd', 'text'=> TEXT_DEALER),
			'w' => array('id' => 'w', 'text'=> TEXT_WHOLESALE),
		);
	}

	/**
	 * this method outputs a line on the template page.
	 */
	function list_row ($js_function = "submitSeq") {
		\core\classes\messageStack::debug_log("executing ".__METHOD__ ." of class ". get_class($admin_class));
		$security_level = \core\classes\user::validate($this->security_token); // in this case it must be done after the class is defined for
		$bkgnd          = ($this->inactive) ? ' style="background-color:pink"' : '';
		$attach_exists  = $this->attachments ? true : false;
		echo "<td $bkgnd onclick='$js_function( $this->id, \"editContact\")'>". htmlspecialchars($this->short_name) 	."</td>";
		echo "<td $bkgnd onclick='$js_function( $this->id, \"editContact\")'>". htmlspecialchars($this->primary_name)	. "</td>";
		echo "<td $bkgnd onclick='$js_function( $this->id, \"editContact\")'>". htmlspecialchars($this->contact_level)	. "</td>";
		echo "<td    {$this->inactive}    onclick='$js_function( $this->id, \"editContact\")'>". htmlspecialchars($this->address1) 	."</td>";
		echo "<td        onclick='$js_function( $this->id, \"editContact\")'>". htmlspecialchars($this->city_town)	."</td>";
		echo "<td        onclick='$js_function( $this->id, \"editContact\")'>". htmlspecialchars($this->state_province)."</td>";
		echo "<td        onclick='$js_function( $this->id, \"editContact\")'>". htmlspecialchars($this->postal_code)	."</td>";
		echo "<td 	     onclick='$js_function( $this->id, \"editContact\")'>". htmlspecialchars($this->telephone1)	."</td>";
		echo "<td align='right'>";
		if ($js_function == "submitSeq") {
			// 	build the action toolbar
			if ($security_level > 1) echo html_icon('mimetypes/x-office-presentation.png', TEXT_SALES, 'small', 	"onclick='contactChart(\"annual_sales\", $this->id)'") . chr(10);
			if ($security_level > 1) echo html_icon('actions/edit-find-replace.png', TEXT_EDIT, 'small', 			"onclick='window.open(\"" . html_href_link(FILENAME_DEFAULT, "cID={$this->id}&amp;action=editContact", 'SSL')."\",\"_blank\")'"). chr(10);
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