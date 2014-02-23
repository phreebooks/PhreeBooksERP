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
//  Path: /modules/contacts/pages/main/template_contacts.php
//
?>
<div title="<?php echo TEXT_CONTACTS;?>" id="tab_contacts">
<?php 
  if (is_array($cInfo->contacts)) { 
	$heading_array  = array(); // don't sort
	$non_sort_array = array(GEN_LAST_NAME, GEN_FIRST_NAME, TEXT_TITLE, GEN_TELEPHONE1, GEN_TELEPHONE4, GEN_EMAIL, TEXT_ACTION);
	$crm_headings   = html_heading_bar($heading_array, $non_sort_array);
?>
  <fieldset>
    <legend><?php echo TEXT_CONTACTS; ?></legend>
	 <table class="ui-widget" style="border-collapse:collapse;width:100%;">
	  <thead class="ui-widget-header"><?php echo $crm_headings['html_code']; ?></thead>
	  <tbody class="ui-widget-content">
<?php
  $odd = true;
  foreach ($cInfo->contacts as $entry) {
    $bkgnd = ($entry->inactive) ? 'class="ui-state-highlight"' : '';
?>
	  <tr id="tr_add_<?php echo $entry->id; ?>" class="<?php echo $odd?'odd':'even'; ?>" style="cursor:pointer">
		<td onclick="getAddress(<?php echo $entry->address['m'][0]->address_id; ?>, 'im')"<?php echo $bkgnd; ?>><?php echo $entry->contact_last; ?></td>
		<td onclick="getAddress(<?php echo $entry->address['m'][0]->address_id; ?>, 'im')"<?php echo $bkgnd; ?>><?php echo $entry->contact_first; ?></td>
		<td onclick="getAddress(<?php echo $entry->address['m'][0]->address_id; ?>, 'im')"><?php echo $entry->contact_middle; ?></td>
		<td onclick="getAddress(<?php echo $entry->address['m'][0]->address_id; ?>, 'im')"><?php echo $entry->address['m'][0]->telephone1; ?></td>
		<td onclick="getAddress(<?php echo $entry->address['m'][0]->address_id; ?>, 'im')"><?php echo $entry->address['m'][0]->telephone4; ?></td>
		<td onclick="getAddress(<?php echo $entry->address['m'][0]->address_id; ?>, 'im')"><?php echo $entry->address['m'][0]->email; ?></td>
		<td align="right">
<?php // build the action toolbar
  if ($security_level > 1) echo html_icon('actions/edit-find-replace.png', TEXT_EDIT,   'small', 'onclick="getAddress(' . $entry->address['m'][0]->address_id . ', \'im\')"') . chr(10);
  if ($security_level > 3) echo html_icon('emblems/emblem-unreadable.png', TEXT_DELETE, 'small', 'onclick="if (confirm(\'' . ACT_WARN_DELETE_ACCOUNT . '\')) deleteAddress(' .$entry->address['m'][0]->address_id . ')"') . chr(10);
?>
		</td>
	  </tr>
<?php
    $odd = !$odd;
  }
?>
	  </tbody>
	</table>
  </fieldset>
  <?php } ?>
  <?php // *********************** Mailing/Main Address (only one allowed) ****************************** ?>
  <fieldset>
    <legend><?php echo ACT_CATEGORY_I_ADDRESS; ?></legend>
      <table class="ui-widget" style="border-collapse:collapse;width:100%;">
      <tr>
       <td>
<?php // build a secondary toolbar for the contact form
	$ctoolbar = new \core\classes\toolbar('i');
	$ctoolbar->icon_list['cancel']['show'] = false;
	$ctoolbar->icon_list['open']['show']   = false;
	$ctoolbar->icon_list['save']['show']   = false;
	$ctoolbar->icon_list['delete']['show'] = false;
	$ctoolbar->icon_list['print']['show']  = false;
	$ctoolbar->add_icon('new', 'onclick="clearAddress(\'im\')"', $order = 10);
	$ctoolbar->icon_list['new']['icon']    = 'actions/contact-new.png';
	$ctoolbar->icon_list['new']['text']    = TEXT_NEW_CONTACT;
	$ctoolbar->add_icon('copy', 'onclick="copyContactAddress(\'' . $type . '\')"', 20);
	$ctoolbar->icon_list['copy']['text']   = TEXT_COPY_ADDRESS;
	echo $output;
	echo $ctoolbar->build_toolbar();
?>
    </td></tr>
    </table>
    <table class="ui-widget" style="border-collapse:collapse;width:100%;">
      <tr>
       <td align="right"><?php echo ACT_SHORT_NAME . html_hidden_field('i_id', ''); ?></td>
       <td><?php echo html_input_field('i_short_name', $cInfo->i_short_name, 'size="21" maxlength="20"', true); ?></td>
       <td align="right"><?php echo TEXT_TITLE; ?></td>
       <td><?php echo html_input_field('i_contact_middle', $cInfo->i_contact_middle, 'size="33" maxlength="32"', false); ?></td>
      </tr>
      <tr>
        <td align="right"><?php echo GEN_FIRST_NAME; ?></td>
        <td><?php echo html_input_field('i_contact_first', $cInfo->i_contact_first, 'size="33" maxlength="32"', false); ?></td>
        <td align="right"><?php echo GEN_LAST_NAME; ?></td>
        <td><?php echo html_input_field('i_contact_last', $cInfo->i_contact_last, 'size="33" maxlength="32"', false); ?></td>
      </tr>
      <tr>
        <td align="right"><?php echo ACT_ACCOUNT_NUMBER; ?></td>
        <td><?php echo html_input_field('i_account_number', $cInfo->i_account_number, 'size="17" maxlength="16"'); ?></td>
        <td align="right"><?php echo ACT_ID_NUMBER; ?></td>
        <td><?php echo html_input_field('i_gov_id_number', $cInfo->i_gov_id_number, 'size="17" maxlength="16"'); ?></td>
      </tr>
    </table>
    <table id="im_address_form" class="ui-widget" style="border-collapse:collapse;width:100%;">
      <?php echo draw_address_fields($cInfo->contact, 'im', false, false, false); ?>
    </table>
  </fieldset>
</div>
