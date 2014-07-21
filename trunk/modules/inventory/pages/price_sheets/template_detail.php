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
//  Path: /modules/inventory/pages/price_sheets/template_detail.php
//
echo html_form('pricesheet', FILENAME_DEFAULT, gen_get_all_get_params(array('action'))) . chr(10);
$hidden_fields = NULL;
// include hidden fields
echo html_hidden_field('id', $id);
echo html_hidden_field('action', '') . chr(10);
// customize the toolbar actions
$toolbar->icon_list['cancel']['params'] = 'onclick="location.href = \'' . html_href_link(FILENAME_DEFAULT, gen_get_all_get_params(array('action')), 'SSL') . '\'"';
$toolbar->icon_list['open']['show']     = false;
if ($security_level > 1) {
  $toolbar->icon_list['save']['params'] = 'onclick="submitToDo(\'' . (($_REQUEST['action'] == 'new') ? 'save' : 'update') . '\')"';
} else {
  $toolbar->icon_list['save']['show']   = false;
}
$toolbar->icon_list['delete']['show']   = false;
$toolbar->icon_list['print']['show']    = false;
if (count($extra_toolbar_buttons) > 0) foreach ($extra_toolbar_buttons as $key => $value) $toolbar->icon_list[$key] = $value;
$toolbar->add_help('07.04.06');
echo $toolbar->build_toolbar(); 
// Build the page
?>
<h1><?php echo PAGE_TITLE . $sheet_name . ' (' . TEXT_REVISION . ' ' . $revision . ')'; ?></h1>
<table class="ui-widget" style="border-style:none;margin-left:auto;margin-right:auto">
 <tbody class="ui-widget-content">
  <tr>
    <td><?php echo PRICE_SHEET_NAME; ?></td>
    <td><?php echo html_input_field('sheet_name', $sheet_name, '', false); ?></td>
    <td align="right"><?php echo TEXT_INACTIVE . html_checkbox_field('inactive', '1', ($inactive) ? ' checked' : ''); ?></td>
    <td align="right"><?php echo TEXT_REVISION . html_input_field('revision', $revision, 'readonly="readonly" size="5"', false); ?></td>
  </tr>
  <tr>
    <td><?php echo TEXT_EFFECTIVE_DATE; ?></td>
    <td><?php echo html_calendar_field($cal_ps); ?></td>
    <td align="right"><?php echo TEXT_USE_AS_DEFAULT; ?></td>
    <td><?php echo html_checkbox_field('default_sheet', '1', ($default_sheet) ? ' checked' : ''); ?></td>
  </tr>
<?php if (ENABLE_MULTI_CURRENCY) echo '<tr><td colspan="4" class="fieldRequired" align="center"> ' . sprintf(GEN_PRICE_SHEET_CURRENCY_NOTE, $currencies->currencies[DEFAULT_CURRENCY]['title']) . '</td></tr>'; ?>
  <tr>
    <td colspan="4">
	 <table class="ui-widget" style="border-collapse:collapse;width:100%">
	  <thead class="ui-widget-header">
	   <tr>
        <th><?php echo TEXT_LEVEL;      ?></th>
        <th><?php echo TEXT_QUANTITY;   ?></th>
        <th><?php echo TEXT_SOURCE;     ?></th>
        <th><?php echo TEXT_ADJUSTMENT; ?></th>
        <th><?php echo INV_ADJ_VALUE;   ?></th>
        <th><?php echo INV_ROUNDING;    ?></th>
        <th><?php echo INV_RND_VALUE;   ?></th>
        <th><?php echo TEXT_PRICE;      ?></th>
      </tr>
	  </thead>
	  <tbody class="ui-widget-content">
      <?php
	$price_levels = explode(';', $default_levels);
	// remove the last element from the price level source array (Level 1 price source)
	$first_source_list = $price_mgr_sources;
	array_shift($first_source_list);
	array_pop($first_source_list);
	for ($i = 0, $j = 1; $i < MAX_NUM_PRICE_LEVELS; $i++, $j++) {
		$level_info = explode(':', $price_levels[$i]);
		$price = $currencies->precise($level_info[0] ? $level_info[0] : (($i == 0) ? $full_price : 0));
		$qty     = $level_info[1] ? $level_info[1] : $j;
		$src     = $level_info[2] ? $level_info[2] : 0;
		$adj     = $level_info[3] ? $level_info[3] : 0;
		$adj_val = $level_info[4] ? $level_info[4] :'0';
		$rnd     = $level_info[5] ? $level_info[5] : 0;
		$rnd_val = $level_info[6] ? $level_info[6] :'0';
		echo '<tr>' . chr(10);
		echo '<td align="center">' . $j . '</td>' . chr(10);
		echo '<td>' . html_input_field('qty_' .     $j, $qty, 'size="10" onchange="updatePrice()" style="text-align:right"') . '</td>' . chr(10);
		echo '<td>' . html_pull_down_menu('src_' .  $j, gen_build_pull_down(($i==0) ? $first_source_list : $price_mgr_sources), $src, 'onchange="updatePrice()"') . '</td>' . chr(10);
		echo '<td>' . html_pull_down_menu('adj_' .  $j, gen_build_pull_down($price_mgr_adjustments), $adj, 'disabled="disabled" onchange="updatePrice()"') . '</td>' . chr(10);
		echo '<td>' . html_input_field('adj_val_' . $j, $currencies->format($adj_val), 'disabled="disabled" size="10" onchange="updatePrice()" style="text-align:right"') . '</td>' . chr(10);
		echo '<td>' . html_pull_down_menu('rnd_' .  $j, gen_build_pull_down($price_mgr_rounding), $rnd, 'disabled="disabled" onchange="updatePrice()"') . '</td>' . chr(10);
		echo '<td>' . html_input_field('rnd_val_' . $j, $currencies->precise($rnd_val), 'disabled="disabled" size="10" onchange="updatePrice()" style="text-align:right"') . '</td>' . chr(10);
		echo '<td>' . html_input_field('price_' .   $j, $currencies->format($price), 'onchange="updatePrice()" style="text-align:right"') . '</td>' . chr(10);
		echo '</tr>' . chr(10);
	}
	$hidden_fields = '<script type="text/javascript">initEditForm()</script>' . chr(10);
?>
    </tbody></table></td>
  </tr>
</tbody>
</table>
<?php // display the hidden fields that are not used in this rendition of the form
echo $hidden_fields;
?>
</form>