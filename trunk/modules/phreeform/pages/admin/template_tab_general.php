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
//  Path: /modules/phreeform/pages/admin/template_tab_general.php
//

?>
<div title="<?php echo TEXT_GENERAL;?>" id="tab_general">
<table class="ui-widget" style="border-style:none;width:100%">
 <thead class="ui-widget-header">
	  <tr><th colspan="2"><?php echo PF_ADMIN_CONFIG_INFO; ?></th></tr> 
 </thead>
 <tbody class="ui-widget-content">
  <tr>
	<td><?php echo PF_DEFAULT_COLUMN_WIDTH_TEXT; ?></td>
	<td><?php echo html_input_field('pf_default_column_width', $_POST['pf_default_column_width'] ? $_POST['pf_default_column_width'] : PF_DEFAULT_COLUMN_WIDTH, 'size="4" maxlength="4"'); ?></td>
  </tr>
  <tr>
	<td><?php echo PF_DEFAULT_MARGIN_TEXT; ?></td>
	<td><?php echo html_input_field('pf_default_margin', $_POST['pf_default_margin'] ? $_POST['pf_default_margin'] : PF_DEFAULT_MARGIN, 'size="4" maxlength="4"'); ?></td>
  </tr>
  <tr>
	<td><?php echo PF_DEFAULT_TITLE1_TEXT; ?></td>
	<td><?php echo html_input_field('pf_default_title1', $_POST['pf_default_title1'] ? $_POST['pf_default_title1'] : PF_DEFAULT_TITLE1, ''); ?></td>
  </tr>
  <tr>
	<td><?php echo PF_DEFAULT_TITLE2_TEXT; ?></td>
	<td><?php echo html_input_field('pf_default_title2', $_POST['pf_default_title2'] ? $_POST['pf_default_title2'] : PF_DEFAULT_TITLE2, 'size="40"'); ?></td>
  </tr>
  <tr>
	<td><?php echo PF_DEFAULT_PAPERSIZE_TEXT; ?></td>
	<td><?php echo html_pull_down_menu('pf_default_papersize', gen_build_pull_down($PaperSizes), $_POST['pf_default_papersize'] ? $_POST['pf_default_papersize'] : PF_DEFAULT_PAPERSIZE, ''); ?></td>
  </tr>
  <tr>
	<td><?php echo PF_DEFAULT_ORIENTATION_TEXT; ?></td>
	<td><?php echo html_pull_down_menu('pf_default_orientation', gen_build_pull_down($PaperOrientation), $_POST['pf_default_orientation'] ? $_POST['pf_default_orientation'] : PF_DEFAULT_ORIENTATION, ''); ?></td>
  </tr>
  <tr>
	<td><?php echo PF_DEFAULT_TRIM_LENGTH_TEXT; ?></td>
	<td><?php echo html_input_field('pf_default_trim_length', $_POST['pf_default_trim_length'] ? $_POST['pf_default_trim_length'] : PF_DEFAULT_TRIM_LENGTH, 'size="3"'); ?></td>
  </tr>
  <tr>
	<td><?php echo PF_DEFAULT_ROWSPACE_TEXT; ?></td>
	<td><?php echo html_input_field('pf_default_rowspace', $_POST['pf_default_rowspace'] ? $_POST['pf_default_rowspace'] : PF_DEFAULT_ROWSPACE, 'size="3" maxlength="2"'); ?></td>
  </tr>
  <tr>
	<td><?php echo PDF_APP_TEXT; ?></td>
	<td><?php echo html_pull_down_menu('pdf_app', $pdf_choices, $_POST['pdf_app'] ? $_POST['pdf_app'] : PDF_APP, ''); ?></td>
  </tr>
 </tbody>
</table>
</div>
