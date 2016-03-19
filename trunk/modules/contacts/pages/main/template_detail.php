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
//  Path: /modules/contacts/pages/main/template_detail.php
//
//echo html_form('contacts', FILENAME_DEFAULT, gen_get_all_get_params(array('action')), 'post', 'enctype="multipart/form-data"', true) . chr(10);
echo "<form id='contacts' class=\"easyui-form\" style='padding:10px 20px 10px 40px;' method='post' enctype='multipart/form-data'>";
// include hidden fields
echo html_hidden_field('action',        '') . chr(10);
echo html_hidden_field('id',  $basis->cInfo->contact->id) . chr(10);
echo html_hidden_field('del_crm_note','') . chr(10);
echo html_hidden_field('payment_id',  '') . chr(10);

$basis->cInfo->contact->fields->display($basis->cInfo->contact);
// Build the page

$custom_path = DIR_FS_MODULES . 'contacts/custom/pages/main/extra_tabs.php';
if (file_exists($custom_path)) { include($custom_path); }

function tab_sort($a, $b) {
	if ($a['order'] == $b['order']) return 0;
	return ($a['order'] > $b['order']) ? 1 : -1;
}
usort($basis->cInfo->contact->tab_list, 'tab_sort');
?>
<h1><?php echo $basis->page_title; ?></h1>
<div class="easyui-tabs" id="detailtabs">
<?php
foreach ($basis->cInfo->contact->tab_list as $value) {
  if (file_exists(DIR_FS_MODULES . "contacts/custom/pages/main/{$value['file']}.php")) {
	include(DIR_FS_MODULES . "contacts/custom/pages/main/{$value['file']}.php");
  } else {
	include(DIR_FS_MODULES . "contacts/pages/main/{$value['file']}.php");
  }
}
// pull in additional custom tabs
if (isset($extra_contact_tabs) && is_array($extra_contact_tabs)) {
  foreach ($extra_contact_tabs as $tabs) {
    $file_path = DIR_FS_MODULES . "contacts/custom/pages/main/{$tabs['tab_filename']}.php";
    if (file_exists($file_path)) { require($file_path);	}
  }
}
echo $basis->cInfo->contact->fields->extra_tab_html;// user added extra tabs
?>
</div>
</form>

<div id ='contactsWindow' class="easyui-window">
	<form id='contactDetails' class="easyui-form">
	    <legend><?php echo TEXT_ADD_UPDATE .' ' . TEXT_CONTACT; ?></legend>
	    <table class="ui-widget" style="border-collapse:collapse;width:100%;">
	      <tr>
	       <td align="right"><?php echo TEXT_CONTACT_ID . html_hidden_field('i_id', ''); ?></td>
	       <td><?php echo html_input_field('i_short_name', $basis->cInfo->contact->i_short_name, 'size="21" maxlength="20"', true); ?></td>
	       <td align="right"><?php echo TEXT_TITLE; ?></td>
	       <td><?php echo html_input_field('i_contact_middle', $basis->cInfo->contact->i_contact_middle, 'size="33" maxlength="32"', false); ?></td>
	      </tr>
	      <tr>
	        <td align="right"><?php echo TEXT_FIRST_NAME; ?></td>
	        <td><?php echo html_input_field('i_contact_first', $basis->cInfo->contact->i_contact_first, 'size="33" maxlength="32"', false); ?></td>
	        <td align="right"><?php echo TEXT_LAST_NAME; ?></td>
	        <td><?php echo html_input_field('i_contact_last', $basis->cInfo->contact->i_contact_last, 'size="33" maxlength="32"', false); ?></td>
	      </tr>
	      <tr>
	        <td align="right"><?php echo TEXT_FACEBOOK_ID; ?></td>
	        <td><?php echo html_input_field('i_account_number', $basis->cInfo->contact->i_account_number, 'size="17" maxlength="16"'); ?></td>
	        <td align="right"><?php echo TEXT_TWITTER_ID; ?></td>
	        <td><?php echo html_input_field('i_gov_id_number', $basis->cInfo->contact->i_gov_id_number, 'size="17" maxlength="16"'); ?></td>
	      </tr>
	    </table>
	    <table id="im_address_form" class="ui-widget" style="border-collapse:collapse;width:100%;">
	      <?php $basis->cInfo->contact->draw_address_fields('im', false, true, false, false); ?>
	    </table>
	</form>
</div>