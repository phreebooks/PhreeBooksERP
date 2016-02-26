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
//  Path: /install/pages/main/template_welcome.php
//
?>
<form name="install" id="install"
	action="index.php?action=inspect<?php echo $lang ? '&amp;lang='.$lang : ''; ?>"
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
			<tr>
				<td colspan="2"><?php echo LANGUAGE_TEXT . html_pull_down_menu('lang', $this->language->languages, $this->language->language_code, 'onchange="langSelect()"'); ?>
				</td>
			</tr>
			<tr>
				<td colspan="2"><?php echo INTRO_WELCOME; ?></td>
			</tr>
			<tr>
				<td colspan="2" align="center"><iframe
						src="<?php echo $license_path; ?>"
						style="width: 750px; height: 250px; border: 1px solid #666;"></iframe>
				</td>
			</tr>
			<tr>
				<td width="80%"><?php echo DESC_AGREE; ?></td>
				<td><?php echo html_radio_field('license', 'agree',    false). ' ' . TEXT_AGREE; ?>
				</td>
			</tr>
			<tr>
				<td width="80%"><?php echo DESC_DISAGREE; ?></td>
				<td><?php echo html_radio_field('license', 'disagree', true) . ' ' . TEXT_DISAGREE; ?>
				</td>
			</tr>
			<tr>
				<td colspan="2" align="right"><?php echo html_button_field('submit_form', TEXT_CONTINUE, 'onclick="document.forms[0].submit()"'); ?>
				</td>
			</tr>
		</tbody>
	</table>
</form>
