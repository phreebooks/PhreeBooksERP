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
//  Path: /modules/contacts/pages/popup_accts/template_main.php
//
echo html_form('popup_accts', FILENAME_DEFAULT, gen_get_all_get_params(array('action', 'list'))) . chr(10);
// include hidden fields
echo html_hidden_field('action', '')   . chr(10);
echo html_hidden_field('rowSeq', '') . chr(10);
// customize the toolbar actions
$toolbar->icon_list['cancel']['params'] = 'onclick="self.close()"';
$toolbar->icon_list['open']['show']     = false;
$toolbar->icon_list['save']['show']     = false;
$toolbar->icon_list['delete']['show']   = false;
$toolbar->icon_list['print']['show']    = false;
// pull in extra toolbar overrides and additions
if (count($extra_toolbar_buttons) > 0) foreach ($extra_toolbar_buttons as $key => $value) $toolbar->icon_list[$key] = $value;
// add the help file index and build the toolbar
switch ($account_type) {
  case 'c': $toolbar->add_help('07.02.02'); break;
  case 'v': $toolbar->add_help('07.03.02'); break;
}
echo $toolbar->build_toolbar($add_search = true); 
// Build the page
?>
<h1><?php echo GEN_HEADING_PLEASE_SELECT; ?></h1>
<div style="height:19px"><?php echo $query_split->display_count(TEXT_DISPLAY_NUMBER . constant('ACT_' . strtoupper($account_type) . '_TYPE_NAME')); ?>
<div style="float:right"><?php echo $query_split->display_links(); ?></div>
</div>
<table class="ui-widget" style="border-collapse:collapse;width:100%;">
 <thead class="ui-widget-header">
  <tr><?php echo $list_header; ?></tr>
 </thead>
 <tbody class="ui-widget-content">
  <?php
  $pointer = 0;
  $odd     = true;
  while (!$query_result->EOF) {
    $cancel_single_result_exit = false;	// if there is only one search result but has pull down window choices
	$acct_id = $query_result->fields['id'];
	$bkgnd   = ($query_result->fields['inactive']) ? ' style="background-color:pink"' : '';
?>
  <tr class="<?php echo $odd?'odd':'even'; ?>" style="cursor:pointer">
	<td<?php echo $bkgnd; ?> onclick="<?php echo 'setReturnAccount(' . $acct_id . ')'; ?>"><?php echo htmlspecialchars($query_result->fields['primary_name']); ?></td>
	<td onclick="<?php echo 'setReturnAccount(' . $acct_id . ')'; ?>"><?php echo htmlspecialchars($query_result->fields['address1']); ?></td>
	<td onclick="<?php echo 'setReturnAccount(' . $acct_id . ')'; ?>"><?php echo htmlspecialchars($query_result->fields['city_town']); ?></td>
	<td onclick="<?php echo 'setReturnAccount(' . $acct_id . ')'; ?>"><?php echo htmlspecialchars($query_result->fields['state_province']); ?></td>
	<td onclick="<?php echo 'setReturnAccount(' . $acct_id . ')'; ?>"><?php echo htmlspecialchars($query_result->fields['postal_code']); ?></td>
	<td onclick="<?php echo 'setReturnAccount(' . $acct_id . ')'; ?>"><?php echo htmlspecialchars($query_result->fields['telephone1']); ?></td>
	<?php switch (JOURNAL_ID) {
		case  6:
		case  7:
		case 12:
		case 13:
			switch (JOURNAL_ID) {
				case  6: $search_journal = 4;  break;
				case  7: $search_journal = 6;  break;
				case 12: $search_journal = 10; break;
				case 13: $search_journal = 12; break;
			}
			$open_order_array = $cInfo->load_open_orders($acct_id, $search_journal);
			if ($open_order_array) {
				$selection = html_pull_down_menu('open_order_' . $pointer, $open_order_array, '', 'onchange="setReturnOrder(' . $pointer . ')"');
				$cancel_single_result_exit = true;
			} else {
				$selection = html_hidden_field('open_order_' . $pointer, '');
			}
			break;
		default:
			$selection = html_hidden_field('open_order_' . $pointer, '') . '&nbsp;';
	} 
	if ($cancel_single_result_exit) { ?>
	  <td ><?php echo $selection; ?></td>
	<?php } else { ?>
	  <td onclick="<?php echo 'setReturnAccount(' . $acct_id . ')'; ?>"><?php echo $selection; ?></td>
	<?php } ?>
  </tr>
<?php
	  $pointer++;
	  $query_result->MoveNext();
	  $odd = !$odd;
	} 
?>
 </tbody>
</table>
<div style="float:right"><?php echo $query_split->display_links(); ?></div>
<div><?php echo $query_split->display_count(TEXT_DISPLAY_NUMBER . constant('ACT_' . strtoupper($account_type) . '_TYPE_NAME')); ?></div>
</form>

<?php 
if (($query_result->RecordCount() == 1) && ($_POST['page'] == 1) && (!$cancel_single_result_exit)) { // then only one entry return with it
  echo '<script type="text/javascript">' . chr(10);
  echo 'setReturnAccount(' . $acct_id . ');' . chr(10);
  echo '</script>' . chr(10);
}
?>