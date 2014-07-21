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
//  Path: /modules/phreedom/pages/import_export/template_modules.php
//
echo html_form('import_export', FILENAME_DEFAULT, gen_get_all_get_params(array('action')), 'post', 'enctype="multipart/form-data"', true) . chr(10);
// include hidden fields
echo html_hidden_field('action', '') . chr(10);
echo html_hidden_field('subject', $subject) . chr(10);
// customize the toolbar actions
$toolbar->icon_list['cancel']['params'] = 'onclick="location.href = \'' . html_href_link(FILENAME_DEFAULT, 'module=phreedom&amp;page=import_export', 'SSL') . '\'"';
$toolbar->icon_list['open']['show']     = false;
$toolbar->icon_list['save']['show']     = false;
$toolbar->icon_list['delete']['show']   = false;
$toolbar->icon_list['print']['show']    = false;
if (count($extra_toolbar_buttons) > 0) foreach ($extra_toolbar_buttons as $key => $value) $toolbar->icon_list[$key] = $value;
$toolbar->add_help('10');
echo $toolbar->build_toolbar(); 
// Build the page
?>
<h1><?php echo PAGE_TITLE; ?></h1>
<fieldset>
<legend><?php echo TEXT_IMPORT_EXPORT_INFO; ?></legend>
  <table class="ui-widget" style="border-style:none;width:100%">
	<tr><td><?php echo GEN_IMPORT_EXPORT_MESSAGE; ?></td></tr>
	<tr><td>
	<table class="ui-widget" style="border-collapse:collapse;width:100%">
	 <thead class="ui-widget-header">
	    <tr><th colspan="4"><?php echo GEN_TABLES_AVAILABLE . TEXT_IMPORT . '/' . TEXT_EXPORT; ?></th></tr>
	 </thead>
	 <tbody class="ui-widget-content">
<?php 
foreach ($page_list as $mod => $params) { 
  if ($subject <> $mod) continue; // only this module
  $structure = $params['structure'];
  if (!$structure->Module->Table) continue; // no tables to import
  foreach ($structure->Module->Table as $table) {
?>
	    <tr>
		  <td><?php echo TEXT_MODULE . ' - ' . $mod; ?></td>
		  <td><?php echo TEXT_TABLE  . ' - ' . $table->Name; ?></td>
	      <td>
		    <?php echo html_button_field('sample_xml_' . $table->Name, SAMPLE_XML, 'onclick="submitToDo(\'sample_xml_' . $table->Name . '\')"') . ' '; ?>
		    <?php echo html_button_field('sample_csv_' . $table->Name, SAMPLE_CSV, 'onclick="submitToDo(\'sample_csv_' . $table->Name . '\')"'); ?>
	      </td>
		</tr>
<?php
  }
} 
?>
	  </tbody>
	 </table>
	</td></tr>
  </table>
</fieldset>

<fieldset>
<legend><?php echo TEXT_IMPORT; ?></legend>
  <table class="ui-widget" style="border-style:none;width:100%">
	<tr><td><?php echo GEN_IMPORT_MESSAGE; ?></td></tr>
	<tr><td>
	<table class="ui-widget" style="border-collapse:collapse;width:100%">
	 <thead class="ui-widget-header">
	    <tr><th colspan="4"><?php echo GEN_TABLES_AVAILABLE . TEXT_IMPORT; ?></th></tr>
	 </thead>
	 <tbody class="ui-widget-content">
<?php 
foreach ($page_list as $mod => $params) { 
  if ($subject <> $mod) continue; // only this module
  $structure = $params['structure'];
  if (!$structure->Module->Table) continue; // no tables to import
  if (!is_array($structure->Module->Table)) $structure->Module->Table = array($structure->Module->Table);
  foreach ($structure->Module->Table as $table) {
    if ($table->CanImport) {
?>
	    <tr>
		  <td><?php echo TEXT_MODULE . ' - ' . $mod; ?></td>
		  <td><?php echo TEXT_TABLE  . ' - ' . $table->Name; ?></td>
	      <td>
			<?php echo html_radio_field ('import_format_' . $table->Name, 'xml', true, '', '') . ' ' . TEXT_XML . ' '; ?>
			<?php echo html_radio_field ('import_format_' . $table->Name, 'csv', false, '', '') . ' ' . TEXT_CSV . ' '; ?>
			<?php echo html_file_field  ('file_name_'     . $table->Name) . ' '; ?>
			<?php echo html_button_field('import_table_'  . $table->Name, TEXT_IMPORT . ' ' . $table->Name, 'onclick="submitToDo(\'import_table_' . $table->Name . '\')"'); ?>
	      </td>
		</tr>
<?php
    }
  }
} 
?>
	  </tbody>
	 </table>
	</td></tr>
  </table>
</fieldset>

<fieldset>
<legend><?php echo TEXT_EXPORT; ?></legend>
  <table class="ui-widget" style="border-style:none;width:100%">
	<tr><td><?php echo GEN_EXPORT_MESSAGE; ?></td></tr>
	<tr><td>
	<table class="ui-widget" style="border-collapse:collapse;width:100%">
	 <thead class="ui-widget-header">
	    <tr><th colspan="4"><?php echo GEN_TABLES_AVAILABLE . TEXT_EXPORT; ?></th></tr>
	 </thead>
	 <tbody class="ui-widget-content">
<?php 
foreach ($page_list as $mod => $params) { 
  if ($subject <> $mod) continue; // only this module
  $structure = $params['structure'];
  if (!$structure->Module->Table) continue; // no tables to import
  if (!is_array($structure->Module->Table)) $structure->Module->Table = array($structure->Module->Table);
  foreach ($structure->Module->Table as $table) {
?>
	    <tr>
		  <td><?php echo TEXT_MODULE . ' - ' . $mod; ?></td>
		  <td><?php echo TEXT_TABLE  . ' - ' . $table->Name; ?></td>
	      <td>
			<?php echo html_radio_field ('export_format_' . $table->Name, 'xml', true, '', '') . ' ' . TEXT_XML . ' '; ?>
			<?php echo html_radio_field ('export_format_' . $table->Name, 'csv', false, '', '') . ' ' . TEXT_CSV . ' '; ?>
			<?php echo html_button_field('export_table_'  . $table->Name, TEXT_EXPORT . ' ' . $table->Name, 'onclick="submitToDo(\'export_table_' . $table->Name . '\')"'); ?>
	      </td>
		</tr>
<?php
  }
} 
?>
	  </tbody>
	 </table>
	</td></tr>
  </table>
</fieldset>
</form>