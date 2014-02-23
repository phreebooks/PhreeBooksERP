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
// |                                                                 |
// | The license that is bundled with this package is located in the |
// | file: /doc/manual/ch01-Introduction/license.html.               |
// | If not, see http://www.gnu.org/licenses/                        |
// +-----------------------------------------------------------------+
//  Path: /modules/bank_import/pages/admin/template_tab_general.php
//

?>
<div title="<?php echo TEXT_GENERAL;?>" id="tab_general">
  <h2 class="tabset_label"><?php echo TEXT_BANK_IMPORT_SETTINGS; ?></h2>
  <fieldset class="formAreaTitle">
    <table border="0" width="100%" cellspacing="1" cellpadding="1">
	  <tr><th colspan="5"><?php echo MODULE_BANK_IMPORT_CONFIG_INFO; ?></th></tr>
	  <tr>
	    <td colspan="4"><?php echo BANK_IMPORT_QUESTION_POSTS; ?></td>
	    <td nowrap="nowrap"><?php echo html_combo_box('question_posts', $all_chart, $_POST['question_posts'] ? $_POST['question_posts'] : QUESTION_POSTS, ''); ?></td>
	  </tr>
	  <tr>
	    <td colspan="4"><?php echo BANK_IMPORT_DEBIT_CREDIT; ?></td>
	    <td><?php echo html_input_field('debit_credit_description', $_POST['debit_credit_description'] ? $_POST['debit_credit_description'] : DEBIT_CREDIT_DESCRIPTION, 'size="64"'); ?></td>
	  </tr>
	</table>
  </fieldset>
</div>
