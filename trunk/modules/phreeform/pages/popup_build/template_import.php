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
// +-----------------------------------------------------------------+
//  Path: /modules/phreeform/pages/popup_build/template_import.php
//
echo html_form('popup_build', FILENAME_DEFAULT, gen_get_all_get_params(array('action')), 'post', 'enctype="multipart/form-data"') . chr(10);
// include hidden fields
echo html_hidden_field('action',        '') . chr(10);
echo html_hidden_field('id',          $id) . chr(10);
echo html_hidden_field('parent_id',   $parent_id) . chr(10);
echo html_hidden_field('doc_title',   '') . chr(10); // placeholder
echo html_hidden_field('folder_path', '/');
// customize the toolbar actions
$toolbar->icon_list['cancel']['params'] = 'onclick="self.close();"';
$toolbar->icon_list['open']['show']     = false;
$toolbar->icon_list['save']['show']     = false;
$toolbar->icon_list['print']['show']    = false;
$toolbar->icon_list['delete']['show']   = false;
$toolbar->add_help('11.01.01');
echo $toolbar->build_toolbar(); 
// Build the page
?>
<h1><?php echo PAGE_TITLE; ?></h1>
<table class="ui-widget" style="border-style:none;margin-left:auto;margin-right:auto;">
 <tbody class="ui-widget-content">
    <tr>
      <td colspan="2">
	    <?php echo TEXT_ENTER_SEARCH_PARAMS_A; ?>
		<?php echo html_pull_down_menu('mod',  $sel_modules,  $def_module); ?>
	    <?php echo TEXT_ENTER_SEARCH_PARAMS_B; ?>
		<?php echo html_pull_down_menu('lang', $sel_language, $def_lang); ?>
	    <?php echo TEXT_ENTER_SEARCH_PARAMS_C; ?>
		<?php echo html_button_field('refresh_dir', TEXT_SEARCH, 'onclick="submitToDo(\'refresh_dir\')"'); ?>
	  </td>
    </tr>
	<tr  class="ui-widget-header">
      <th colspan="2"><?php echo PHREEFORM_DEFIMP; ?></th>
    </tr>
    <tr>
      <td><?php echo ReadDefReports('RptFileName', $import_path); ?></td>
    </tr>
    <tr>
      <td>
	    <?php echo PHREEFORM_RPTBROWSE . ' ' . html_file_field('reportfile'); ?>
	  </td>
    </tr>
    <tr>
      <td>
	    <?php echo PHREEFORM_RPTENTER . '<br />' . PHREEFORM_RPTNOENTER; ?>
	    <?php echo html_input_field('reportname', $reportname, 'size="32" maxlength="30"'); ?>
	  </td>
	  <td align="right">
		<?php echo html_button_field('import_one', TEXT_IMPORT_ONE, 'onclick="submitToDo(\'import_one\')"') . chr(10); ?>
	  </td>
	</tr>
    <tr class="ui-widget-header">
      <th colspan="2"><?php echo '&nbsp;'; ?></th>
    </tr>
	<tr>
	  <td><?php echo TEXT_IMPORT_ALL_DESC . ' ' ; ?></td>
	  <td align="right">
		<?php echo html_button_field('import_all', TEXT_IMPORT_ALL, 'onclick="submitToDo(\'import_all\')"') . chr(10); ?>
	  </td>
    </tr>
  </tbody>
</table>
</form>
