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
//  Path: /modules/contacts/pages/admin/template_tab_add_book.php
//

?>
<div title="<?php echo TEXT_ADDRESS_BOOK;?>" id="tab_add_book">
  <fieldset>
    <table>
	  <tr><th colspan="2"><?php echo TEXT_BILLING_PREFS; ?></th></tr>
	  <tr>
	    <td><?php echo sprintf(CONTACT_BILL_FIELD_REQ, GEN_CONTACT); ?></td>
	    <td><?php echo html_pull_down_menu('address_book_contact_required', $sel_yes_no, $_POST['address_book_contact_required'] ? $_POST['address_book_contact_required'] : ADDRESS_BOOK_CONTACT_REQUIRED, ''); ?></td>
	  </tr>
	  <tr>
	    <td><?php echo sprintf(CONTACT_BILL_FIELD_REQ, GEN_ADDRESS1); ?></td>
	    <td><?php echo html_pull_down_menu('address_book_address1_required', $sel_yes_no, $_POST['address_book_address1_required'] ? $_POST['address_book_address1_required'] : ADDRESS_BOOK_ADDRESS1_REQUIRED, ''); ?></td>
	  </tr>
	  <tr>
	    <td><?php echo sprintf(CONTACT_BILL_FIELD_REQ, GEN_ADDRESS2); ?></td>
	    <td><?php echo html_pull_down_menu('address_book_address2_required', $sel_yes_no, $_POST['address_book_address2_required'] ? $_POST['address_book_address2_required'] : ADDRESS_BOOK_ADDRESS2_REQUIRED, ''); ?></td>
	  </tr>
	  <tr>
	    <td><?php echo sprintf(CONTACT_BILL_FIELD_REQ, GEN_CITY_TOWN); ?></td>
	    <td><?php echo html_pull_down_menu('address_book_city_town_required', $sel_yes_no, $_POST['address_book_city_town_required'] ? $_POST['address_book_city_town_required'] : ADDRESS_BOOK_CITY_TOWN_REQUIRED, ''); ?></td>
	  </tr>
	  <tr>
	    <td><?php echo sprintf(CONTACT_BILL_FIELD_REQ, GEN_STATE_PROVINCE); ?></td>
	    <td><?php echo html_pull_down_menu('address_book_state_province_required', $sel_yes_no, $_POST['address_book_state_province_required'] ? $_POST['address_book_state_province_required'] : ADDRESS_BOOK_STATE_PROVINCE_REQUIRED, ''); ?></td>
	  </tr>
	  <tr>
	    <td><?php echo sprintf(CONTACT_BILL_FIELD_REQ, GEN_POSTAL_CODE); ?></td>
	    <td><?php echo html_pull_down_menu('address_book_postal_code_required', $sel_yes_no, $_POST['address_book_postal_code_required'] ? $_POST['address_book_postal_code_required'] : ADDRESS_BOOK_POSTAL_CODE_REQUIRED, ''); ?></td>
	  </tr>
	  <tr>
	    <td><?php echo sprintf(CONTACT_BILL_FIELD_REQ, GEN_TELEPHONE1); ?></td>
	    <td><?php echo html_pull_down_menu('address_book_telephone1_required', $sel_yes_no, $_POST['address_book_telephone1_required'] ? $_POST['address_book_telephone1_required'] : ADDRESS_BOOK_TELEPHONE1_REQUIRED, ''); ?></td>
	  </tr>
	  <tr>
	    <td><?php echo sprintf(CONTACT_BILL_FIELD_REQ, GEN_EMAIL); ?></td>
	    <td><?php echo html_pull_down_menu('address_book_email_required', $sel_yes_no, $_POST['address_book_email_required'] ? $_POST['address_book_email_required'] : ADDRESS_BOOK_EMAIL_REQUIRED, ''); ?></td>
	  </tr>
	</table>
  </fieldset>
</div>
