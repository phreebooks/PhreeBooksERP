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
//  Path: /modules/phreebooks/pages/search/template_main.php
//
echo html_form('site_search', FILENAME_DEFAULT, gen_get_all_get_params(array('action', 'list'))) . chr(10);
// include hidden fields
echo html_hidden_field('action', '') . chr(10);
// customize the toolbar actions
$toolbar->icon_list['cancel']['params'] = 'onclick="location.href = \'' . html_href_link(FILENAME_DEFAULT, '', 'SSL') . '\'"';
$toolbar->icon_list['open']['show']     = false;
$toolbar->icon_list['delete']['show']   = false;
$toolbar->icon_list['save']['show']     = false;
$toolbar->icon_list['print']['show']    = false;
if (count($extra_toolbar_buttons) > 0) foreach ($extra_toolbar_buttons as $key => $value) $toolbar->icon_list[$key] = $value;
$toolbar->add_help('');
echo $toolbar->build_toolbar(); 
// Build the page
?>
<h1><?php echo HEADING_TITLE_SEARCH_INFORMATION; ?></h1>
<table class="ui-widget" style="border-style:none;margin-left:auto;margin-right:auto">
 <thead class="ui-widget-header">
  <tr>
    <th><?php echo TEXT_FILTER; ?></th>
    <th><?php echo TEXT_TYPE; ?></th>
    <th><?php echo TEXT_FROM; ?></th>
    <th><?php echo TEXT_TO; ?></th>
  </tr>
 </thead>
 <tbody class="ui-widget-content">
  <tr>
    <td><?php echo TEXT_TRANSACTION_TYPE; ?></td>
    <td colspan="3"><?php echo html_pull_down_menu('search_journal_id', gen_build_pull_down($journal_choices), $_SESSION['search_journal_id']); ?></td>
	<td rowspan="3" align="right"><?php echo html_icon('actions/view-refresh.png', TEXT_RESET, 'large', 'style="cursor:pointer;" onclick="submitToDo(\'reset\')"');
    echo '&nbsp;' . html_icon('actions/system-search.png', TEXT_SEARCH, 'large', 'style="cursor:pointer;" onclick="submitToDo(\'search\')"'); ?></td>
  </tr>
  <tr>
    <td><?php echo TEXT_TRANSACTION_DATE; ?></td>
    <td><?php echo html_pull_down_menu('search_date_id', gen_build_pull_down($DateChoices), $_SESSION['search_date_id'], $params = ''); ?></td>
    <td><?php echo html_calendar_field($cal_from); ?></td>
    <td><?php echo html_calendar_field($cal_to); ?></td>
  </tr>
  <tr>
    <td><?php echo TEXT_REFERENCE_NUMBER; ?></td>
    <td><?php echo html_pull_down_menu('search_ref_id', gen_build_pull_down($choices), $_SESSION['search_ref_id'], $params = ''); ?></td>
    <td><?php echo html_input_field('search_ref_from', $_SESSION['search_ref_from'], $params = ''); ?></td>
    <td><?php echo html_input_field('search_ref_to', $_SESSION['search_ref_to'], $params = ''); ?></td>
  </tr>
  <tr>
    <td><?php echo TEXT_CUST_VEND_ACCT; ?></td>
    <td><?php echo html_pull_down_menu('search_account_id', gen_build_pull_down($choices), $_SESSION['search_account_id'], $params = ''); ?></td>
    <td><?php echo html_input_field('search_account_from', $_SESSION['search_account_from'], $params = ''); ?></td>
    <td><?php echo html_input_field('search_account_to', $_SESSION['search_account_to'], $params = ''); ?></td>
  </tr>
  <tr>
    <td><?php echo TEXT_INVENTORY_ITEM; ?></td>
    <td><?php echo html_pull_down_menu('search_sku_id', gen_build_pull_down($choices), $_SESSION['search_sku_id'], $params = ''); ?></td>
    <td><?php echo html_input_field('search_sku_from', $_SESSION['search_sku_from'], $params = ''); ?></td>
    <td><?php echo html_input_field('search_sku_to', $_SESSION['search_sku_to'], $params = ''); ?></td>
  </tr>
  <tr>
    <td><?php echo TEXT_TRANSACTION_AMOUNT; ?></td>
    <td><?php echo html_pull_down_menu('search_amount_id', gen_build_pull_down($choices), $_SESSION['search_amount_id'], $params = ''); ?></td>
    <td><?php echo html_input_field('search_amount_from',($_SESSION['search_amount_from'])? $currencies->precise($_SESSION['search_amount_from']):'', $params = ''); ?></td>
    <td><?php echo html_input_field('search_amount_to', ($_SESSION['search_amount_to']) ? $currencies->precise($_SESSION['search_amount_to']):'', $params = ''); ?></td>
  </tr>
  <tr>
    <td><?php echo TEXT_GENERAL_LEDGER_ACCOUNT; ?></td>
    <td><?php echo html_pull_down_menu('search_gl_acct_id', gen_build_pull_down($choices), $_SESSION['search_gl_acct_id'], $params = ''); ?></td>
    <td><?php echo html_pull_down_menu('search_gl_acct_from', $gl_array_list, $_SESSION['search_gl_acct_from']); ?></td>
    <td><?php echo html_pull_down_menu('search_gl_acct_to', $gl_array_list, $_SESSION['search_gl_acct_to']); ?></td>
  </tr>
  <tr>
    <td><?php echo TEXT_JOURNAL_RECORD_ID; ?></td>
    <td><?php echo html_pull_down_menu('search_main_id', gen_build_pull_down($choices), $_SESSION['search_main_id'], $params = ''); ?></td>
    <td><?php echo html_input_field('search_main_from', $_SESSION['search_main_from'], $params = ''); ?></td>
    <td><?php echo html_input_field('search_main_to', $_SESSION['search_main_to'], $params = ''); ?></td>
  </tr>
 </tbody>
</table>
<div style="height:19px"><?php echo $query_split->display_count(TEXT_DISPLAY_NUMBER . TEXT_RESULTS); ?>
<div style="float:right"><?php echo $query_split->display_links(); ?></div>
</div>
<table class="ui-widget" style="border-collapse:collapse;width:100%">
 <thead class="ui-widget-header">
  <tr><?php echo $list_header; ?></tr>
 </thead>
 <tbody class="ui-widget-content">
	<?php
	$odd = true;
	while (!$query_result->EOF) {
	  $jID = (int)$query_result->fields['journal_id'];
	  switch ($jID) {
	    case  2: 
		  $security_level = $_SESSION['admin_security'][SECURITY_ID_JOURNAL_ENTRY]; 
		  $module = 'phreebooks'; 
		  $mod = 'journal'; 
		  break;
		case  3: 
		  $security_level = $_SESSION['admin_security'][SECURITY_ID_PURCHASE_QUOTE]; 
		  $module = 'phreebooks'; 
		  $mod = 'orders'; 
		  break;
		case  4: 
		  $security_level = $_SESSION['admin_security'][SECURITY_ID_PURCHASE_ORDER]; 
		  $module = 'phreebooks'; 
		  $mod = 'orders'; 
		  break;
		case  6: 
		  $security_level = $_SESSION['admin_security'][SECURITY_ID_PURCHASE_INVENTORY]; 
		  $module = 'phreebooks'; 
		  $mod = 'orders'; 
		  break;
		case  7: 
		  $security_level = $_SESSION['admin_security'][SECURITY_ID_PURCHASE_CREDIT]; 
		  $module = 'phreebooks'; 
		  $mod = 'orders'; 
		  break;
		case  9: 
		  $security_level = $_SESSION['admin_security'][SECURITY_ID_SALES_QUOTE]; 
		  $module = 'orders'; 
		  $mod = 'orders'; 
		  break;
		case 10: 
		  $security_level = $_SESSION['admin_security'][SECURITY_ID_SALES_ORDER]; 
		  $module = 'phreebooks'; 
		  $mod = 'orders'; 
		  break;
		case 12: 
		  $security_level = $_SESSION['admin_security'][SECURITY_ID_SALES_INVOICE]; 
		  $module = 'phreebooks'; 
		  $mod = 'orders'; 
		  break;
		case 13: 
		  $security_level = $_SESSION['admin_security'][SECURITY_ID_SALES_CREDIT]; 
		  $module = 'phreebooks'; 
		  $mod = 'orders'; 
		  break;
	    case 14: 
		  $security_level = $_SESSION['admin_security'][SECURITY_ID_ASSEMBLE_INVENTORY]; 
		  $module = 'inventory'; 
		  $mod = 'assemblies'; 
		  break;
	    case 16: 
		  $security_level = $_SESSION['admin_security'][SECURITY_ID_ADJUST_INVENTORY]; 
		  $module = 'inventory'; 
		  $mod = 'adjustments'; 
		  break;
	    case 18:
		  $security_level = $_SESSION['admin_security'][SECURITY_ID_CUSTOMER_RECEIPTS]; 
		  $module = 'phreebooks'; 
		  $mod = 'bills'; 
		  $type = gen_get_contact_type($query_result->fields['bill_acct_id']);
		  break;
	    case 19: 
		  $security_level = $_SESSION['admin_security'][SECURITY_ID_POS_MGR]; 
		  $module = 'phreepos'; $mod = 'pos_mgr'; 
		  break;
	    case 20: 
		  $security_level = $_SESSION['admin_security'][SECURITY_ID_PAY_BILLS]; 
		  $module = 'phreebooks'; 
		  $mod = 'bills'; 
		  $type = gen_get_contact_type($query_result->fields['bill_acct_id']);
		  break;
	    case 21: 
		  $security_level = $_SESSION['admin_security'][SECURITY_ID_POS_MGR]; 
		  $module = 'phreepos'; $mod = 'pos_mgr'; 
		  break;
		default: 
		  $security_level = $_SESSION['admin_security'][SECURITY_ID_SEARCH]; 
		  $module = 'phreebooks'; 
		  $mod = 'orders'; 
		  break;
	  }
	  if ($security_level > 0) {
	  	$params = 'module=' . $module . '&amp;page=' . $mod . '&amp;oID=' . $query_result->fields['id'] . '&amp;jID=' . $jID . '&amp;action=edit';
		if ($type) $params .= '&amp;type=' . $type;
	?>
  <tr class="<?php echo $odd?'odd':'even'; ?>" style="cursor:pointer">
	<td onclick="window.open('<?php echo html_href_link(FILENAME_DEFAULT, $params, 'SSL'); ?>','_blank')"><?php echo $query_result->fields['id']; ?></td>
	<td onclick="window.open('<?php echo html_href_link(FILENAME_DEFAULT, $params, 'SSL'); ?>','_blank')"><?php echo $query_result->fields['description']; ?></td>
	<td onclick="window.open('<?php echo html_href_link(FILENAME_DEFAULT, $params, 'SSL'); ?>','_blank')"><?php echo $query_result->fields['bill_primary_name']; ?></td>
	<td onclick="window.open('<?php echo html_href_link(FILENAME_DEFAULT, $params, 'SSL'); ?>','_blank')"><?php echo gen_locale_date($query_result->fields['post_date']); ?></td>
	<td onclick="window.open('<?php echo html_href_link(FILENAME_DEFAULT, $params, 'SSL'); ?>','_blank')"><?php echo $query_result->fields['purchase_invoice_id']; ?></td>
	<td align="right" onclick="window.open('<?php echo html_href_link(FILENAME_DEFAULT, $params, 'SSL'); ?>','_blank')"><?php echo $currencies->format($query_result->fields['total_amount']); ?></td>
	<td align="right">
<?php // build the action toolbar
	  if (function_exists('add_extra_action_bar_buttons')) echo add_extra_action_bar_buttons($query_result->fields);
	  echo html_icon('actions/edit-find-replace.png', TEXT_EDIT, 'small', 'onclick="window.open(\'' . html_href_link(FILENAME_DEFAULT, $params, 'SSL') . '\',\'_blank\')"') . chr(10);
?>
	</td>
  </tr>
<?php } else { // no permission ?>
  <tr><td colspan="7"><?php echo $query_result->fields['description'] . ' - ' . ERROR_NO_SEARCH_PERMISSION; ?></td></tr>
<?php	  
	  }
	  $query_result->MoveNext();
	  $odd = !$odd;
	}
?>
 </tbody>
</table>
<div style="float:right"><?php echo $query_split->display_links(); ?></div>
<div><?php echo $query_split->display_count(TEXT_DISPLAY_NUMBER . TEXT_RESULTS); ?></div>
</form>