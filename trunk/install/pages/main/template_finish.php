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
//  Path: /install/pages/main/template_finish.php
//
?>
<form name="install" id="install"
	action="index.php?action=open_company<?php echo $lang ? '&amp;lang='.$lang : ''; ?>"
	method="post">
	<table class="ui-widget"
		style="margin-left: auto; margin-right: auto; width: 800px">
		<thead class="ui-widget-header">
			<tr>
				<th align="right"><img
					src="../modules/phreedom/images/phreesoft_logo.png"
					alt="Phreedom Small Business Toolkit" height="50" /></th>
			</tr>
		</thead>
		<tbody class="ui-widget-content">
			<tr>
				<td><?php echo INTRO_FINISHED; ?></td>
			</tr>
			<tr>
				<td align="right"><?php echo html_button_field('submit_form', TEXT_GO_TO_COMPANY, 'onclick="document.forms[0].submit()"'); ?>
				</td>
			</tr>
		</tbody>
	</table>
</form>
