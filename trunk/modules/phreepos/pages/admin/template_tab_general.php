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
//  Path: /modules/phreepos/pages/admin/template_tab_general.php
//

?>
<div id="general" class="tabset_content">
  <h2 class="tabset_label"><?php echo TEXT_PHREEPOS_SETTINGS; ?></h2>
  <fieldset class="formAreaTitle">
	<table align="center">
	  <tr><th colspan="2"><?php echo MENU_HEADING_CONFIG; ?></th></tr> 
	  <tr>
		<td><?php echo PHREEPOS_REQUIRE_ADDRESS_DESC; ?></td>
		<td><?php echo html_pull_down_menu('phreepos_require_address', $sel_yes_no, $_POST['phreepos_require_address'] ? $_POST['phreepos_require_address'] : PHREEPOS_REQUIRE_ADDRESS, ''); ?></td>
	  </tr>
	  <tr>
		<td><?php echo PHREEPOS_DISPLAY_WITH_TAX_DESC ?></td>
		<td><?php echo html_pull_down_menu('phreepos_display_with_tax', $sel_yes_no, $_POST['phreepos_display_with_tax'] ? $_POST['phreepos_display_with_tax'] : PHREEPOS_DISPLAY_WITH_TAX, '1'); ?></td>
	  </tr>
	  <tr>
		<td><?php echo PHREEPOS_DISCOUNT_OF_DESC ?></td>
		<td><?php echo html_pull_down_menu('phreepos_discount_of', $sel_yes_no, $_POST['phreepos_discount_of'] ? $_POST['phreepos_discount_of'] : PHREEPOS_DISCOUNT_OF, '0'); ?></td>
	  </tr>
	  <tr>
		<td><?php echo PHREEPOS_ROUNDING_DESC ?></td>
		<td><?php echo html_pull_down_menu('phreepos_rounding',    $sel_rounding, $_POST['phreepos_rounding'] ? $_POST['phreepos_rounding'] : PHREEPOS_ROUNDING, '0'); ?></td>
	  </tr>
	  <tr>
		<td><?php echo PHREEPOS_ENABLE_DIRECT_PRINTING_DESC ?></td>
		<td><?php echo html_pull_down_menu('phreepos_enable_direct_printing', $sel_yes_no, $_POST['phreepos_enable_direct_printing'] ? $_POST['phreepos_enable_direct_printing'] : PHREEPOS_ENABLE_DIRECT_PRINTING, '1'); ?></td>
	  </tr>
	</table>
  </fieldset>
</div>
