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
//  Path: /modules/phreebooks/pages/admin/template_tab_general.php
//

?>
<div title="<?php echo TEXT_GENERAL;?>" id="tab_general">
  <fieldset>
    <table>
	  <tr><th colspan="2"><?php echo TEXT_OPTIONS; ?></th></tr>
	  <tr>
	    <td><?php echo CD_13_01_DESC; ?></td>
	    <td><?php echo html_pull_down_menu('auto_update_period', $sel_yes_no, $_POST['auto_update_period'] ? $_POST['auto_update_period'] : AUTO_UPDATE_PERIOD, ''); ?></td>
	  </tr>
	  <tr>
	    <td><?php echo CD_13_05_DESC; ?></td>
	    <td><?php echo html_pull_down_menu('show_full_gl_names', $sel_gl_desc, $_POST['show_full_gl_names'] ? $_POST['show_full_gl_names'] : SHOW_FULL_GL_NAMES, ''); ?></td>
	  </tr>
	  <tr>
	    <td><?php echo CD_01_52_DESC; ?></td>
	    <td><?php echo html_pull_down_menu('round_tax_by_auth', $sel_yes_no, $_POST['round_tax_by_auth'] ? $_POST['round_tax_by_auth'] : ROUND_TAX_BY_AUTH, ''); ?></td>
	  </tr>
	  <tr>
	    <td><?php echo CD_01_55_DESC; ?></td>
	    <td><?php echo html_pull_down_menu('enable_bar_code_readers', $sel_yes_no, $_POST['enable_bar_code_readers'] ? $_POST['enable_bar_code_readers'] : ENABLE_BAR_CODE_READERS, ''); ?></td>
	  </tr>
	  <tr>
	    <td><?php echo CD_01_75_DESC; ?></td>
	    <td><?php echo html_pull_down_menu('single_line_order_screen', $sel_order_lines, $_POST['single_line_order_screen'] ? $_POST['single_line_order_screen'] : SINGLE_LINE_ORDER_SCREEN, ''); ?></td>
	  </tr>
	  <tr>
	    <td><?php echo CD_01_50_DESC; ?></td>
	    <td><?php echo html_pull_down_menu('enable_order_discount', $sel_yes_no, $_POST['enable_order_discount'] ? $_POST['enable_order_discount'] : ENABLE_ORDER_DISCOUNT, ''); ?></td>
	  </tr>
	  <tr>
	    <td><?php echo ALLOW_NEGATIVE_INVENTORY_DESC; ?></td>
	    <td><?php echo html_pull_down_menu('allow_negative_inventory', $sel_yes_no, $_POST['allow_negative_inventory'] ? $_POST['allow_negative_inventory'] : ALLOW_NEGATIVE_INVENTORY, ''); ?></td>
	  </tr>
	</table>
  </fieldset>
</div>
