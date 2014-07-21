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
//  Path: /modules/contacts/pages/popup_terms/template_main.php
//
echo html_form('popup_terms', FILENAME_DEFAULT, gen_get_all_get_params(array('action'))) . chr(10);
// include hidden fields
echo html_hidden_field('action', '')   . chr(10);
echo html_hidden_field('rowSeq', '') . chr(10);
// customize the toolbar actions
$toolbar->icon_list['cancel']['params'] = 'onclick="self.close()"';
$toolbar->icon_list['open']['show']     = false;
$toolbar->icon_list['save']['params']   = 'onclick="setReturnTerms()"';
$toolbar->icon_list['delete']['show']   = false;
$toolbar->icon_list['print']['show']    = false;
if (count($extra_toolbar_buttons) > 0) foreach ($extra_toolbar_buttons as $key => $value) $toolbar->icon_list[$key] = $value;
switch ($account_type) {
  case 'c': $toolbar->add_help('07.03.02.04'); break;
  case 'v': $toolbar->add_help('07.02.02.04'); break;
}
echo $toolbar->build_toolbar(); 
// Build the page
?>
<h1><?php echo ACT_POPUP_TERMS_WINDOW_TITLE; ?></h1>
<table class="ui-widget" style="border-collapse:collapse;width:100%">
 <tbody class="ui-widget-content">
  <tr>
	<td colspan="2"><?php echo ACT_TERMS_DEFAULT . ' ' . gen_terms_to_language('0', false, $terms_type) . '<br />' . chr(10); ?></td>
  </tr>
  <tr>
	<td>
<?php
if ($_GET['val']) {
	$terms = explode(':', $_GET['val']);
} else {
	$terms = array('0' => 0);
}
//echo 'terms='; print_r($terms); echo '<br />';
echo html_radio_field('special_terms', 0, ($terms[0] == '0' ? true : false), '', 'onclick="changeOptions()"') . ACT_TERMS_USE_DEFAULTS . '<br />' . chr(10);
echo html_radio_field('special_terms', 1, ($terms[0] == '1' ? true : false), '', 'onclick="changeOptions()"') . ACT_COD_SHORT . '<br />' . chr(10);
echo html_radio_field('special_terms', 2, ($terms[0] == '2' ? true : false), '', 'onclick="changeOptions()"') . ACT_PREPAID . '<br />' . chr(10);
echo html_radio_field('special_terms', 3, ($terms[0] == '3' ? true : false), '', 'onclick="changeOptions()"') . ACT_SPECIAL_TERMS . '<br />' . chr(10);
echo html_radio_field('special_terms', 4, ($terms[0] == '4' ? true : false), '', 'onclick="changeOptions()"') . ACT_DAY_NEXT_MONTH . '<br />' . chr(10);
echo html_radio_field('special_terms', 5, ($terms[0] == '5' ? true : false), '', 'onclick="changeOptions()"') . ACT_END_OF_MONTH . chr(10);
?>
	</td>
	<td valign="top">
<?php
echo ACT_DISCOUNT . html_input_field('early_percent', (isset($terms[1]) ? $terms[1] : $discount_percent), 'size="4"') . ACT_EARLY_DISCOUNT . '<br />' . chr(10);
echo ACT_DUE_IN . html_input_field('early_days', (isset($terms[2]) ? $terms[2] : $discount_days), 'size="3"') . ACT_TERMS_EARLY_DAYS . '<br />' . chr(10);
if ($terms[0] == '0' || $terms[0] == '1' || $terms[0] == '2' || $terms[0] == '3') {
	$field_value = isset($terms[3]) ? $terms[3] : $num_days_due;
} else {
	$field_value = '';
}
echo ACT_TERMS_NET . html_input_field('standard_days', $field_value, 'size="3"') . ACT_TERMS_STANDARD_DAYS . '<br />' . chr(10);
echo '<br /><br />'.html_calendar_field($cal_terms);
if ($terms[0] == '4' || $terms[0] == '5') {
	echo '<script type="text/javascript">';
	echo "document.popup_terms.elements['due_date'].value = '" . $terms[3] . "';";
	echo '</script>';
}
?>
	</td>
  </tr>
  <tr><td colspan="2"><?php echo ACT_TERMS_CREDIT_LIMIT.' '.html_input_field('credit_limit', (isset($terms[4]) ? $terms[4] : $credit_limit)) . chr(10); ?></td></tr>
 </tbody>
</table>
</form>