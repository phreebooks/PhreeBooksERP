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
//  Path: /install/pages/main/template_install.php
//
?>
<div id="please_wait" style="display: hidden">
	<p>

	<?php echo html_icon('phreebooks/please_wait.gif', '', 'large'); ?></p>
</div>
<form name="install" id="install"
	action="index.php?action=install<?php echo $lang ? '&amp;lang='.$lang : ''; ?>"
	method="post">
	<table class="ui-widget"
		style="margin-left: auto; margin-right: auto; width: 800px">
		<thead class="ui-widget-header">
			<tr>
				<th colspan="2" align="right"><img
					src="../modules/phreedom/images/phreesoft_logo.png"
					alt="Phreedom Small Business Toolkit" height="50" /></th>
			</tr>
		</thead>
		<tbody class="ui-widget-content">


		<?php if ($error || $caution) { ?>
			<tr>
				<td colspan="2"><?php echo MSG_INSPECT_ERRORS; ?></td>
			</tr>
			<tr>
				<td colspan="2"><?php echo $messageStack->output(); ?></td>
			</tr>




<?php } ?>
   <tr><td colspan="2"><?php echo MSG_INSTALL_INTRO; ?></td></tr>
   <tr class="ui-widget-header"><th colspan="2"><?php echo TEXT_COMPANY_INFO; ?></th></tr>
   <tr>
	<td><?php echo TEXT_COMPANY_NAME; ?></td>
	<td><?php echo html_input_field('company_name', $_POST['company_name']); ?></td>
   </tr>
   <tr>
	<td><?php echo TEXT_INSTALL_DEMO_DATA; ?></td>
	<td><?php echo html_pull_down_menu('company_demo', $sel_yes_no, $_POST['company_demo'] ? $_POST['company_demo'] : 0); ?></td>
   </tr>

   <tr class="ui-widget-header"><th colspan="2"><?php echo TEXT_ADMIN_INFO; ?></th></tr>
   <tr>
	<td><?php echo TEXT_USERNAME; ?></td>
	<td><?php echo html_input_field('user_username', $_POST['user_username']); ?></td>
   </tr>
   <tr>
	<td><?php echo TEXT_USER_PASSWORD; ?></td>
	<td><?php echo html_password_field('user_password'); ?></td>
   </tr>
   <tr>
	<td><?php echo TEXT_UER_PW_CONFIRM; ?></td>
	<td><?php echo html_password_field('user_pw_confirm'); ?></td>
   </tr>
   <tr>
	<td><?php echo TEXT_USER_EMAIL; ?></td>
	<td><?php echo html_input_field('user_email', $_POST['user_email']); ?></td>
   </tr>

   <tr class="ui-widget-header"><th colspan="2"><?php echo TEXT_SRVR_INFO; ?></th></tr>
   <tr>
	<td><?php echo TEXT_HTTP_SRVR; ?></td>
	<td><?php echo html_input_field('srvr_http', $_POST['srvr_http'] ? $_POST['srvr_http'] : $srvr_http); ?></td>
   </tr>
   <tr>
	<td><?php echo TEXT_USE_SSL; ?></td>
	<td><?php echo html_pull_down_menu('use_ssl', $sel_yes_no, $_POST['use_ssl'] ? $_POST['use_ssl'] : 0); ?></td>
   </tr>
   <tr>
	<td><?php echo TEXT_HTTPS_SRVR; ?></td>
	<td><?php echo html_input_field('srvr_https', $_POST['srvr_https'] ? $_POST['srvr_https'] : $srvr_https); ?></td>
   </tr>

   <tr class="ui-widget-header"><th colspan="2"><?php echo TEXT_DB_INFO; ?></th></tr>
   <tr>
	<td><?php echo TEXT_DB_HOST; ?></td>
	<td><?php echo html_input_field('db_host', $_POST['db_host'] ? $_POST['db_host'] : 'localhost'); ?></td>
   </tr>
   <tr>
	<td><?php echo TEXT_DB_NAME; ?></td>
	<td><?php echo html_input_field('db_name', $_POST['db_name']); ?></td>
   </tr>
   <tr>
	<td><?php echo TEXT_DB_PREFIX; ?></td>
	<td><?php echo html_input_field('db_prefix', $_POST['db_prefix']); ?></td>
   </tr>
   <tr>
	<td><?php echo TEXT_DB_USER; ?></td>
	<td><?php echo html_input_field('db_username', $_POST['db_username']); ?></td>
   </tr>
   <tr>
	<td><?php echo TEXT_DB_PASSWORD; ?></td>
	<td><?php echo html_password_field('db_password'); ?></td>
   </tr>

   <tr class="ui-widget-header"><th colspan="2"><?php echo TEXT_FISCAL_INFO; ?></th></tr>
   <tr>
	<td><?php echo TEXT_FY_MONTH_INFO; ?></td>
	<td><?php echo html_pull_down_menu('fy_month', $sel_fy_month, $_POST['fy_month'] ? $_POST['fy_month'] : '01'); ?></td>
   </tr>
   <tr>
	<td><?php echo TEXT_FY_YEAR_INFO; ?></td>
	<td><?php echo html_pull_down_menu('fy_year', $sel_fy_year, $_POST['fy_year'] ? $_POST['fy_year'] : date('Y')); ?></td>
   </tr>

   <tr>
	<td colspan="2" align="right"><?php echo html_submit_field('btn_install', TEXT_CONTINUE, 'onclick="showLoading()"'); ?></td>
   </tr>
  </tbody>
	</table>
</form>
