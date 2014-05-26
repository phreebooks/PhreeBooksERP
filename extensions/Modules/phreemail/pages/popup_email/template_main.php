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
// |                                                                 |
// | The license that is bundled with this package is located in the |
// | file: /doc/manual/ch01-Introduction/license.html.               |
// | If not, see http://www.gnu.org/licenses/                        |
// +-----------------------------------------------------------------+
//  Path: /modules/phreemail/pages/popup_email/template_main.php
//

// start the form
echo html_form('popup_email', FILENAME_DEFAULT, gen_get_all_get_params(array('action'))) . chr(10);

// include hidden fields
echo html_hidden_field('action', '')   . chr(10);
// customize the toolbar actions
$toolbar->icon_list['cancel']['params'] = 'onclick="self.close()"';
$toolbar->icon_list['open']['show']     = false;
$toolbar->icon_list['save']['show']     = false;
$toolbar->icon_list['print']['show']     = false;
$toolbar->add_icon('email','onclick="submitToDo(\'send\')"');
$toolbar->icon_list['delete']['show']   = false;

// pull in extra toolbar overrides and additions
if (count($extra_toolbar_buttons) > 0) {
	foreach ($extra_toolbar_buttons as $key => $value) $toolbar->icon_list[$key] = $value;
}

// add the help file index and build the toolbar
$toolbar->add_help('11.02');
echo $toolbar->build_toolbar();

// Build the page
?>
<!--possible senders-->
<datalist id="senderlist">
	<?php foreach($senderlist as $key => $value ) echo "<option value='$value'>";?>
</datalist>
<datalist id="sendermaillist">
	<?php foreach($sendermaillist as $key => $value ) echo "<option value='$value'>";?>
</datalist>
<!--possible receivers-->
<datalist id="receiverlist">
	<?php foreach($receiverlist as $key => $value ) echo "<option value='$value'>";?>
	<?php foreach($senderlist as $key => $value ) echo "<option value='$value'>";?>
</datalist>
<datalist id="receivermaillist">
	<?php foreach($receivermaillist as $key => $value ) echo "<option value='$value'>";?>
	<?php foreach($sendermaillist as $key => $value ) echo "<option value='$value'>";?>
</datalist>
<table border="0">
  <tr>
	<td align="right"><?php echo TEXT_SENDER_NAME . ': '; ?></td>
	<td><?php echo html_input_field('sender_name', $sender_name,'list="senderlist"') . ' ' . TEXT_EMAIL . html_input_field('sender_email', $sender_email, 'size="40" list="sendermaillist"'); ?></td>
  </tr>
  <tr>
	<td align="right"><?php echo TEXT_RECEPIENT_NAME; ?></td>
	<td><?php echo html_input_field('recpt_name', $recpt_name,'list="receiverlist"') . ' ' . TEXT_EMAIL . html_input_field('recpt_email', $recpt_email, 'size="40" list="receivermaillist"'); ?></td>
  </tr>
  <tr>
	<td align="right"><?php echo TEXT_CC_NAME; ?></td>
	<td><?php echo html_input_field('cc_name', $cc_name,'list="receiverlist"') . ' ' . TEXT_EMAIL . html_input_field('cc_email', $cc_email, 'size="40" list="receivermaillist"'); ?></td>
  </tr>
  <tr>
	<td align="right"><?php echo TEXT_MESSAGE_SUBJECT . ': '; ?></td>
	<td><?php echo html_input_field('message_subject', $subject, 'size="75"'); ?></td>
  </tr>
  <tr>
	<td align="right" valign="top"><?php echo TEXT_MESSAGE_BODY . ': '; ?></td>
	<td><?php echo html_textarea_field('message_body', '60', '8', $message); ?></td>
  </tr>
</table>
</form>