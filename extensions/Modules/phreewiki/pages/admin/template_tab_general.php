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
// |                                                                 |
// | The license that is bundled with this package is located in the |
// | file: /doc/manual/ch01-Introduction/license.html.               |
// | If not, see http://www.gnu.org/licenses/                        |
// +-----------------------------------------------------------------+
//  Path: /modules/phreewiki/pages/admin/template_tab_general.php
//

?>
<div title="<?php echo TEXT_GENERAL;?>" id="general">
  <h2 class="tabset_label"><?php echo TEXT_PHREEWIKI_SETTINGS; ?></h2>
  <fieldset class="formAreaTitle">
    <table border="0" width="100%">
	  <tr><th colspan="5"><?php echo MODULE_PHREEWIKI_CONFIG_INFO; ?></th></tr>
	  <tr>
	    <td colspan="4"><?php echo PHREEWIKI_CONFIG_REGEXPSEARCH; ?></td>
	    <td><?php echo html_pull_down_menu('phreewiki_reg_exp_search', $sel_yes_no, $_POST['phreewiki_reg_exp_search'] ? $_POST['phreewiki_reg_exp_search'] : PHREEWIKI_REG_EXP_SEARCH, ''); ?></td>
	  </tr>
	  <tr>
	    <td colspan="4"><?php echo PHREEWIKI_CONFIG_CASE_SENSITIVE_SEARCH; ?></td>
	    <td><?php echo html_pull_down_menu('phreewiki_case_sensitive_search', $sel_yes_no, $_POST['phreewiki_case_sensitive_search'] ? $_POST['phreewiki_case_sensitive_search'] : PHREEWIKI_CASE_SENSITIVE_SEARCH, ''); ?></td>
	  </tr>
	  <tr>
	    <td colspan="4"><?php echo PHREEWIKI_CONFIG_ANIMATE; ?></td>
	    <td><?php echo html_pull_down_menu('phreewiki_animate', $sel_yes_no, $_POST['phreewiki_animate'] ? $_POST['phreewiki_animate'] : PHREEWIKI_ANIMATE, ''); ?></td>
	  </tr>
	  <tr>
	    <td colspan="4"><?php echo PHREEWIKI_CONFIG_GENERATE_RSS; ?></td>
	    <td><?php echo html_pull_down_menu('phreewiki_generate_rss', $sel_yes_no, $_POST['phreewiki_generate_rss'] ? $_POST['phreewiki_generate_rss'] : PHREEWIKI_GENERATE_RSS, ''); ?></td>
	  </tr>
	  <tr>
	    <td colspan="4"><?php echo PHREEWIKI_CONFIG_LINKS_NEW_WINDOW; ?></td>
	    <td><?php echo html_pull_down_menu('phreewiki_open_new_window', $sel_yes_no, $_POST['phreewiki_open_new_window'] ? $_POST['phreewiki_open_new_window'] : PHREEWIKI_OPEN_NEW_WINDOW, ''); ?></td>
	  </tr>
	  <tr>
	    <td colspan="4"><?php echo PHREEWIKI_CONFIG_TROGGLE_LINKS; ?></td>
	    <td><?php echo html_pull_down_menu('phreewiki_troggle_links', $sel_yes_no, $_POST['phreewiki_troggle_links'] ? $_POST['phreewiki_troggle_links'] : PHREEWIKI_TROGGLE_LINKS, ''); ?></td>
	  </tr>
	  <tr>
	    <td colspan="4"><?php echo PHREEWIKI_CONFIG_CONFIRM_DELETE; ?></td>
	    <td><?php echo html_pull_down_menu('phreewiki_confirm_delete', $sel_yes_no, $_POST['phreewiki_confirm_delete'] ? $_POST['phreewiki_confirm_delete'] : PHREEWIKI_CONFIRM_DELETE, ''); ?></td>
	  </tr>
	  <tr>
	    <td colspan="4"><?php echo PHREEWIKI_CONFIG_INSERT_TABS; ?></td>
	    <td><?php echo html_pull_down_menu('phreewiki_insert_tabs', $sel_yes_no, $_POST['phreewiki_insert_tabs'] ? $_POST['phreewiki_insert_tabs'] : PHREEWIKI_INSERT_TABS, ''); ?></td>
	  </tr>
	  <tr>
	    <td colspan="4"><?php echo PHREEWIKI_CONFIG_MAX_EDIT_ROWS; ?></td>
	    <td><?php echo html_input_field('phreewiki_max_edit_rows', $_POST['phreewiki_max_edit_rows'] ? $_POST['phreewiki_max_edit_rows'] : PHREEWIKI_MAX_EDIT_ROWS, 'size="64"'); ?></td>
	  </tr>
	</table>
  </fieldset>
</div>
