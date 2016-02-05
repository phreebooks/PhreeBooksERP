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
//  Path: /install/pages/main/template_inspect.php
//
?>
<form name="install" id="install"
	action="index.php?action=inspect<?php echo $lang ? '&amp;lang='.$lang : ''; ?>"
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
			
			
		<?php if ($error || $caution) { ?>
			<tr>
				<td><?php echo MSG_INSPECT_ERRORS; ?></td>
			</tr>
			
			
			
			
<?php } ?>
   <tr>
    <td align="right">
<?php echo html_submit_field('btn_recheck', TEXT_RECHECK); ?>
<?php echo html_submit_field('btn_install', TEXT_INSTALL, $error ? 'disabled="disabled"' : ''); ?>
    </td>
   </tr>
  </tbody>
	</table>
</form>
