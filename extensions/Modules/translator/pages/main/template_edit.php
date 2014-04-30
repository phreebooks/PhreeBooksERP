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
//  Path: /modules/translator/pages/main/template_edit.php
//
echo html_form('translator', FILENAME_DEFAULT, gen_get_all_get_params(array('action'))) . chr(10);
echo html_hidden_field('action', '')   . chr(10);
echo html_hidden_field('mod',  $mod) . chr(10);
echo html_hidden_field('lang', $lang). chr(10);
echo html_hidden_field('ver',  $ver) . chr(10);
$toolbar->icon_list['cancel']['params'] = 'onclick="location.href = \'' . html_href_link(FILENAME_DEFAULT, gen_get_all_get_params(array('action', 'f0', 'f1', 'f2', 'f3')), 'SSL') . '\'"';
$toolbar->icon_list['open']['show']     = false;
$toolbar->icon_list['delete']['show']   = false;
$toolbar->icon_list['save']['params']   = 'onclick="submitToDo(\'save\')"';
$toolbar->icon_list['print']['show']    = false;
echo $toolbar->build_toolbar();
?>
<h1><?php echo TEXT_TRANSLATOR_ASS; ?></h1>
<div id="filter_bar">
<table class="ui-widget" style="border-collapse:collapse;width:100%">
 <tbody class="ui-widget-content">
  <tr>
    <td><?php echo html_button_field('export', TEXT_CHECK_ALL, 'onclick="checkAllBoxes()"'); ?></td>
    <td>&nbsp;&nbsp;</td>
	<td><?php echo TEXT_FILTERS. " : " . '&nbsp;' . TEXT_MODULE . html_pull_down_menu('f0', $sel_modules,    $mod); ?></td>
	<td><?php echo '&nbsp;' . TEXT_LANGUAGE .   '&nbsp;' . html_pull_down_menu('f1', $sel_language,   $lang); ?></td>
	<td><?php echo '&nbsp;' . TEXT_VERSION  .   '&nbsp;' . html_pull_down_menu('f2', $sel_version,    $ver); ?></td>
	<td><?php echo '&nbsp;' . TEXT_TRANSLATED . '&nbsp;' . html_pull_down_menu('f3', $sel_translated, $f3); ?></td>
	<td><?php echo '&nbsp;' . html_button_field('apply', TEXT_APPLY, 'onclick="submitToDo(\'filter\')"'); ?></td>
  </tr>
 </tbody>
</table>
</div>
<table class="ui-widget" style="border-collapse:collapse;width:100%">
 <thead class="ui-widget-header">
  <tr><?php echo $list_header; ?></tr>
 </thead>
 <tbody class="ui-widget-content">
<?php
  $i = 0;
  $odd = true;
  while (!$query_result->EOF) {
    $const = $query_result->fields['defined_constant'];
	$cID   = $query_result->fields['id'] . ':' . $query_result->fields['defined_constant'];
	if (!defined($const)) define($const, $const);
	$box_size = strlen($query_result->fields['translation']);
?>
  <tr class="<?php echo $odd?'odd':'even'; ?>" style="cursor:pointer">
	<td align="center">
	  <?php echo html_icon('mimetypes/x-office-address-book.png', $query_result->fields['pathtofile'], 'small', '') . chr(10); ?>
	  <?php echo html_checkbox_field('d:'.$cID, '1', $query_result->fields['translated'] ? true : false); ?>
	</td>
<?php if ($box_size < 64) { ?>
	<td><?php echo html_input_field('t:'.$cID, htmlspecialchars($query_result->fields['translation']), 'size="64"'); ?></td>
	<td><?php echo html_input_field('x:'.$i,   htmlspecialchars(constant($const)), 'readonly="readonly" size="64"'); ?></td>
<?php } else { ?>
	<td><?php echo html_textarea_field('t:'.$cID, 46, 3, htmlspecialchars($query_result->fields['translation'])); ?></td>
	<td><?php echo html_textarea_field('x:'.$i,   46, 3, htmlspecialchars(constant($const)), 'readonly="readonly"'); ?></td>
<?php } ?>
	<td><?php echo html_input_field('c:'.$i, $const, 'readonly="readonly" size="48"'); ?></td>
  </tr>
<?php
	  $i++;
      $query_result->MoveNext();
      $odd = !$odd;
    }
?>
 </tbody>
</table>
</form>
