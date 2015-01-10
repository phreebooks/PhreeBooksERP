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
//  Path: /modules/phreedom/pages/users/template_detail.php
//

echo html_form('users', FILENAME_DEFAULT, gen_get_all_get_params(array('action'))) . chr(10);
// include hidden fields
echo html_hidden_field('action',   '')        . chr(10);
echo html_hidden_field('rowSeq', $admin_id) . chr(10);
// customize the toolbar actions
$toolbar->icon_list['cancel']['params'] = 'onclick="location.href = \'' . html_href_link(FILENAME_DEFAULT, gen_get_all_get_params(array('action')), 'SSL') . '\'"';
$toolbar->icon_list['open']['show']     = false;
$toolbar->icon_list['delete']['show']   = false;
$toolbar->icon_list['print']['show']    = false;
if ($security_level > 2) {
  $toolbar->icon_list['save']['params'] = 'onclick="submitToDo(\'save\')"';
} else {
  $toolbar->icon_list['save']['show']   = false;
}
if (count($extra_toolbar_buttons) > 0) foreach ($extra_toolbar_buttons as $key => $value) $toolbar->icon_list[$key] = $value;
$toolbar->add_help('07.08.07');
echo $toolbar->build_toolbar();
// Build the page
?>
<h1><?php echo PAGE_TITLE; ?></h1>
  <fieldset>
  <legend><?php echo TEXT_GENERAL; ?></legend>
  <table class="ui-widget" style="border-style:none;margin-left:auto;margin-right:auto;">
   <tbody class="ui-widget-content">
   <tr>
    <td align="right"><?php echo TEXT_USERNAME . ' '  . html_input_field('admin_name', $uInfo->admin_name, 'size="25"'); ?></td>
    <td><?php echo TEXT_INACTIVE . ' ' . html_checkbox_field('inactive', '1', ($uInfo->inactive ? true : false)); ?></td>
    <td><?php echo TEXT_DISPLAY_NAME . ' ' . html_input_field('display_name', $uInfo->display_name, 'size="25"'); ?></td>
   </tr>
   <tr>
    <td align="right"><?php echo TEXT_PASSWORD . ' ' . html_password_field('password_new', ''); ?></td>
    <td><?php echo '&nbsp;'; ?></td>
    <td><?php echo TEXT_EMAIL . ' ' . html_input_field('admin_email', $uInfo->admin_email, 'size="33"'); ?></td>
   </tr>
   <tr>
    <td align="right"><?php echo TEXT_CONFIRM_PASSWORD . ' ' . html_password_field('password_conf', ''); ?></td>
    <td><?php echo '&nbsp;'; ?></td>
    <td><?php echo TEXT_LINK_TO_EMPLOYEE_ACCOUNT . ' ' . html_pull_down_menu('account_id', gen_get_contact_array_by_type('e'), $uInfo->account_id, ''); ?></td>
   </tr>
   <tr>
    <td><?php echo TEXT_SELECT_ROLE . ' ' . html_pull_down_menu('fill_role', $fill_all_roles, $uInfo->role, 'onchange="submitToDo(\'fill_role\')"'); ?></td>
   </tr>
   </tbody>
  </table>
  </fieldset>

  <fieldset>
  <legend><?php echo TEXT_PROFILE; ?></legend>
  <table>
  <tr>
    <td><?php echo TEXT_DEFAULT_STORE . ' ' . html_pull_down_menu('def_store_id',  gen_get_store_ids(), isset($_POST['def_store_id']) ? $_POST['def_store_id'] : $uInfo->def_store_id, ''); ?></td>
    <td><?php echo TEXT_DEFAULT_CASH_ACCOUNT . ' ' . html_pull_down_menu('def_cash_acct', gen_coa_pull_down(), isset($_POST['def_cash_acct']) ? $_POST['def_cash_acct'] : $uInfo->def_cash_acct, ''); ?></td>
    <td><?php echo '&nbsp;'; ?></td>
  </tr>
  <tr>
    <td><?php echo TEXT_RESTRICT_STORE . ' ' . html_checkbox_field('restrict_store', '1', ((isset($_POST['restrict_store']) && $_POST['restrict_store']) || $uInfo->restrict_store) ? true : false); ?></td>
    <td><?php echo TEXT_DEFAULT_AR_ACCOUNT    . ' ' . html_pull_down_menu('def_ar_acct', gen_coa_pull_down(), isset($_POST['def_ar_acct']) ? $_POST['def_ar_acct'] : $uInfo->def_ar_acct, ''); ?></td>
    <td><?php echo '&nbsp;'; ?></td>
  </tr>
  <tr>
    <td><?php echo TEXT_RESTRICT_PERIOD . ' ' . html_checkbox_field('restrict_period', '1', ((isset($_POST['restrict_period']) && $_POST['restrict_period']) || $uInfo->restrict_period) ? true : false); ?></td>
    <td><?php echo TEXT_DEFAULT_PAYABLES_ACCOUNT     . ' ' . html_pull_down_menu('def_ap_acct', gen_coa_pull_down(), isset($_POST['def_ap_acct']) ? $_POST['def_ap_acct'] : $uInfo->def_ap_acct, ''); ?></td>
    <td><?php echo '&nbsp;'; ?></td>
  </tr>
  </table>
  </fieldset>

  <fieldset>
  <legend><?php echo TEXT_SECURITY_SETTINGS; ?></legend>
    <div><?php echo TEXT_FILL_ALL_TO_LEVEL . ' ' . html_pull_down_menu('fill_all', $fill_all_values, '-1', 'onchange="submitToDo(\'fill_all\')"'); ?></div>
	<div class="easyui-tabs" id="accesstabs">
<?php //@todo tables do not show in ff
$settings     = \core\classes\user::parse_permissions($uInfo->admin_security);
$column_break = true;
// array pb_headings is defined in /includes/header_navigation.php
foreach ($mainmenu as $key => $menu_heading) {
  if ($menu_heading['text'] == TEXT_HOME || $menu_heading['text'] == TEXT_LOG_OUT) continue;
	echo '		<div title="'.$menu_heading['text'].'" id="tab_' . $key . '">' . chr(10);
	echo '			<table  class="ui-widget" style="border-collapse:collapse;margin-left:auto;margin-right:auto;">' . chr(10);
	echo '				<thead class="ui-widget-header">' . chr(10);
	echo '					<tr>' . chr(10);
	echo '						<th width="50%">&nbsp;</th>' . chr(10);
	echo '						<th width="10%" nowrap="nowrap">' . TEXT_FULL      . '</th>' . chr(10);
	echo '						<th width="10%" nowrap="nowrap">' . TEXT_EDIT      . '</th>' . chr(10);
	echo '						<th width="10%" nowrap="nowrap">' . TEXT_ADD       . '</th>' . chr(10);
	echo '						<th width="10%" nowrap="nowrap">' . TEXT_READ_ONLY . '</th>' . chr(10);
	echo '						<th width="10%" nowrap="nowrap">' . TEXT_NONE      . '</th>' . chr(10);
	echo '					</tr>' . chr(10);
	echo '				</thead><tbody class="ui-widget-content">' . chr(10);
	$odd = true;
	foreach($menu_heading['submenu'] as $menu_item){
		create_row($menu_item);
	}
	echo '				</tbody>' . chr(10);
	echo '			</table>' . chr(10);
	echo '		</div>' . chr(10);
} ?>
    </div>
  </fieldset>
</form>


<?php
function create_row($array){
	global $odd, $settings;
	if(!empty($array['submenu']) && $array['text'] <> TEXT_MODULE_ADMINISTRATION) foreach($array['submenu'] as $menu_item){
		create_row($menu_item);
	}else{
		if ($array['security_id'] == '') return;// && $item['heading'] <> TEXT_TOOLS) continue;  // special case for reports listings not in Tools menu
		$checked = array();
		if ($array['show_in_users_settings'] === false) {
			return; // skip if menu only item
		} elseif (isset($_POST['sID_' . $array['security_id']])) {
			$checked[0] = false;
			$checked[$_POST['sID_' . $array['security_id']]] = true;
		} elseif (isset($settings[$array['security_id']])) {
			$checked[0] = false;
			$checked[$settings[$array['security_id']]] = true;
		} else {
			$checked[0] = true;	// default to no access
		}
		echo '					<tr valign="top" class="' . ($odd?'odd':'even') . '">'. chr(10);
		echo '						<td>' . $array['text'] . '</td>' . chr(10);
		echo '						<td align="center">' . html_radio_field('sID_' . $array['security_id'], '4', $checked[4]) . '</td>' . chr(10);
		echo '						<td align="center">' . html_radio_field('sID_' . $array['security_id'], '3', $checked[3]) . '</td>' . chr(10);
		echo '						<td align="center">' . html_radio_field('sID_' . $array['security_id'], '2', $checked[2]) . '</td>' . chr(10);
		echo '						<td align="center">' . html_radio_field('sID_' . $array['security_id'], '1', $checked[1]) . '</td>' . chr(10);
		echo '						<td align="center">' . html_radio_field('sID_' . $array['security_id'], '0', $checked[0]) . '</td>' . chr(10);
		echo '					</tr>' . chr(10);
		$odd =!$odd;
	}
}
?>