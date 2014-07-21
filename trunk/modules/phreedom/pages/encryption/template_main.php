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
//  Path: /modules/phreedom/pages/encryption/template_main.php
//
echo html_form('encrypt', FILENAME_DEFAULT, gen_get_all_get_params(array('action'))) . chr(10);
// include hidden fields
echo html_hidden_field('action', '') . chr(10);
// customize the toolbar actions
$toolbar->icon_list['cancel']['params'] = 'onclick="location.href = \'' . html_href_link(FILENAME_DEFAULT, '', 'SSL') . '\'"';
$toolbar->icon_list['open']['show']     = false;
$toolbar->icon_list['save']['show']     = false;
$toolbar->icon_list['delete']['show']   = false;
$toolbar->icon_list['print']['show']    = false;
if (count($extra_toolbar_buttons) > 0) foreach ($extra_toolbar_buttons as $key => $value) $toolbar->icon_list[$key] = $value;
$toolbar->add_help('');
echo $toolbar->build_toolbar(); 
// Build the page
?>
<h1><?php echo BOX_HEADING_ENCRYPTION; ?></h1>
<fieldset>
<legend><?php echo GEN_ADM_TOOLS_SET_ENCRYPTION_KEY; ?></legend>
<p><?php echo GEN_ENCRYPTION_GEN_INFO; ?></p>
<table class="ui-widget" style="border-style:none;margin-left:auto;margin-right:auto">
 <thead class="ui-widget-header">
  <tr><th colspan="2"><?php echo GEN_ENCRYPTION_COMP_TYPE; ?></th></tr>
 </thead>
 <tbody class="ui-widget-content">
  <tr>
	<td align="center"><?php echo GEN_ENCRYPTION_KEY . html_password_field('enc_key', ''); ?></td>
  </tr>
  <tr>
	<td align="center"><?php echo GEN_ENCRYPTION_KEY_CONFIRM . html_password_field('enc_key_confirm', ''); ?></td>
  </tr>
  <tr>
	<td colspan="2" align="right"><?php echo html_button_field('encrypt_key', GEN_ADM_TOOLS_BTN_SAVE, 'onclick="submitToDo(\'save\')"'); ?></td>
  </tr>
 </tbody>
</table>
</fieldset>
<?php if ($security_level > 2) { ?>
<fieldset>
<legend><?php echo GEN_ADM_TOOLS_SET_ENCRYPTION_PW; ?></legend>
<p><?php echo GEN_ADM_TOOLS_SET_ENCRYPTION_PW_DESC; ?></p>
<table class="ui-widget" style="border-style:none;margin-left:auto;margin-right:auto">
 <tbody class="ui-widget-content">
    <tr>
	  <td><?php  echo GEN_ADM_TOOLS_ENCRYPT_OLD_PW; ?></td>
	  <td><?php  echo html_password_field('old_encrypt_key'); ?></td>
	</tr>
    <tr>
	  <td><?php echo GEN_ADM_TOOLS_ENCRYPT_PW; ?></td>
	  <td><?php echo html_password_field('new_encrypt_key'); ?></td>
	</tr>
    <tr>
	  <td><?php echo GEN_ADM_TOOLS_ENCRYPT_PW_CONFIRM; ?></td>
	  <td><?php echo html_password_field('new_encrypt_confirm'); ?></td>
	</tr>
    <tr>
	  <td colspan="2" align="right"><?php echo html_button_field('encrypt_key', GEN_ADM_TOOLS_BTN_SAVE, 'onclick="submitToDo(\'encrypt_key\')"'); ?></td>
	</tr>
 </tbody>
</table>
</fieldset>
<?php } ?>
</form>