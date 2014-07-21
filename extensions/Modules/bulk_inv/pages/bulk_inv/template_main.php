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
//  Path: /modules/bulk_inv/pages/bulk_inv/template_main.php
//
echo html_form('bulk_inventory', FILENAME_DEFAULT, gen_get_all_get_params(array('action', 'list'))) . chr(10);
// include hidden fields
echo html_hidden_field('action',   '')     . chr(10);
// customize the toolbar actions
$toolbar->icon_list['cancel']['params']   = 'onclick="location.href = \'' . html_href_link(FILENAME_DEFAULT, '', 'SSL') . '\'"';
$toolbar->icon_list['open']['show']       = false;
if ($security_level > 1) {
	$toolbar->icon_list['save']['params'] = 'onclick="submitToDo(\'save\')"';
} else {
	$toolbar->icon_list['save']['show']   = false;
}
$toolbar->icon_list['delete']['show']     = false;
$toolbar->icon_list['print']['show']      = false;
// add the help file index and build the toolbar
echo $toolbar->build_toolbar($add_search = true);
// Build the page
?>
<h1><?php echo PAGE_TITLE; ?></h1>
<div style="height:19px"><?php echo $query_split->display_count(TEXT_DISPLAY_NUMBER . TEXT_ITEMS); ?>
<div style="float:right"><?php echo $query_split->display_links(); ?></div>
</div>
<table class="ui-widget" style="border-collapse:collapse;width:100%">
 <thead class="ui-widget-header">
  <tr>
	<th><?php echo '# Fields'; ?></th>
	<th><?php echo TEXT_DESCRIPTION; ?></th>
	<?php for ($i = 0; $i < $field_cnt; $i++) echo "<th>Field ".$i."</th>\n"; ?>
	<th><?php echo TEXT_ACTION; ?></th>
  </tr>
  <tr>
	<td><?php echo html_input_field('field_cnt', $field_cnt, 'onchange="submitToDo(\'reload\')"'); ?></td>
	<td>&nbsp;</td>
	<?php for ($i = 0; $i < $field_cnt; $i++) echo "<td>".html_pull_down_menu('field'.$i, $fields, $field[$i], 'onchange="submitToDo(\'reload\')"') . "</td>\n"; ?>
	<td align="right"><?php echo html_button_field('filter', 'Filter', 'onclick="submitToDo(\'filter\')"'); ?></td>
  </tr>
 </thead>
 <tbody class="ui-widget-content">
  <?php
	$row   = 0;
	$odd = true;
    while (!$query_result->EOF) {
?>
  <tr class="<?php echo $odd?'odd':'even'; ?>">
	<td><?php echo $query_result->fields['sku'] . html_hidden_field('id_'.$row, $query_result->fields['id']); ?></td>
	<td><?php echo $query_result->fields['description_short']; ?></td>
	<?php for ($i = 0; $i < $field_cnt; $i++) echo "<td>".html_input_field('f'.$i.'_'.$row, $query_result->fields['f'.$i])."</td>\n"; ?>
	<td align="right"><?php echo '&nbsp;'; ?></td>
  </tr>
<?php
		$row++;
		$query_result->MoveNext();
		$odd = !$odd;
    }
?>
 </tbody>
</table>
<div style="float:right"><?php echo $query_split->display_links(); ?></div>
<div><?php echo $query_split->display_count(TEXT_DISPLAY_NUMBER . TEXT_ITEMS); ?></div>
</form>
