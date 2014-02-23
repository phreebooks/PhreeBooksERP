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
//  Path: /modules/phreedom/pages/admin/template_tab_manager.php
//

?>
<div title="<?php echo BOX_COMPANY_MANAGER;?>" id="tab_manager">
  <fieldset>
  <legend><?php echo SETUP_CO_MGR_COPY_CO; ?></legend>
    <table>
	  <tr>
	    <td colspan="2" align="right"><?php echo html_button_field('add_button', TEXT_COPY, 'onclick="submitToDo(\'copy_co\')"'); ?></td>
	  </tr>
	  <tr>
	    <td colspan="2"><?php echo SETUP_CO_MGR_COPY_HDR; ?></td>
	  </tr>
	  <tr>
		<td><?php echo SETUP_CO_MGR_SRVR_NAME; ?></td>
		<td><?php echo html_input_field('db_server', $db_server ? $db_server : 'localhost', 'size="40"', true); ?></td>
	  </tr>
	  <tr>
		<td><?php echo SETUP_CO_MGR_DB_NAME; ?></td>
		<td><?php echo html_input_field('db_name', $db_name, '', true); ?></td>
	  </tr>
	  <tr>
		<td><?php echo SETUP_CO_MGR_DB_USER; ?></td>
		<td><?php echo html_input_field('db_user', $db_user, '', true); ?></td>
	  </tr>
	  <tr>
		<td><?php echo SETUP_CO_MGR_DB_PW; ?></td>
		<td><?php echo html_password_field('db_pw', '', true); ?></td>
	  </tr>
	  <tr>
		<td><?php echo SETUP_CO_MGR_CO_NAME; ?></td>
		<td><?php echo html_input_field('co_name', $co_name, 'size="50"', true); ?></td>
	  </tr>
	</table>
    <table>
	  <tr>
	    <th colspan="4"><?php echo SETUP_CO_MGR_MOD_SELECT; ?></th>
	  </tr>
	  <tr>
	    <th width="40%"><?php echo TEXT_MODULE;    ?></th>
	    <th width="20%"><?php echo TEXT_DEF_DATA;  ?></th>
	    <th width="20%"><?php echo TEXT_DEMO_DATA; ?></th>
	    <th width="20%"><?php echo TEXT_ALL_DATA;  ?></th>
	  </tr>
<?php foreach ($loaded_modules as $mod) { // load modules and query for copy 
		if ($mod == 'phreedom') continue;
?>
	  <tr>
		<td><?php echo html_checkbox_field($mod, '1', (!in_array($mod, $core_modules) && $error && !isset($_POST[$mod])) ? false : true, '', in_array($mod, $core_modules) ? 'disabled="disabled"' : '') . '&nbsp;' . TEXT_MODULE . ': ' . $mod; ?></td>
		<td align="center"><?php echo html_radio_field($mod . '_action', 'core', $_POST[$mod.'_action']=='core' ? true : false, '', $parameters = ''); ?></td>
		<td align="center"><?php echo html_radio_field($mod . '_action', 'demo', $_POST[$mod.'_action']=='demo' ? true : false, '', $parameters = ''); ?></td>
		<td align="center"><?php echo html_radio_field($mod . '_action', 'data', (!isset($_POST[$mod.'_action'])||$_POST[$mod.'_action']=='data') ? true : false,  '', $parameters = ''); ?></td>
	  </tr>
<?php } ?>
	</table>
  </fieldset>
  <fieldset>
  <legend><?php echo SETUP_CO_MGR_DEL_CO; ?></legend>
    <table>
	  <tr>
	    <td colspan="3" style="color:red"><?php echo SETUP_CO_MGR_DELETE_CONFIRM; ?></td>
	  </tr>
	  <tr>
	    <td colspan="3"><?php echo SETUP_CO_MGR_SELECT_DELETE . html_pull_down_menu('del_company', load_company_dropdown(true)); ?></td>
	    <td align="right"><?php echo html_button_field('del_button', TEXT_DELETE, 'onclick="if (confirm(\'' . SETUP_CO_MGR_JS_DELETE_CONFIRM . '\')) submitToDo(\'delete_co\')"'); ?></td>
	  </tr>
	 </table>
  </fieldset>
</div>
