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
// |                                                                 |
// | The license that is bundled with this package is located in the |
// | file: /doc/manual/ch01-Introduction/license.html.               |
// | If not, see http://www.gnu.org/licenses/                        |
// +-----------------------------------------------------------------+
//  Path: /modules/import_bank/pages/main/template_main.php
//

// start the form
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
// pull in extra toolbar overrides and additions
if (count($extra_toolbar_buttons) > 0) {
  foreach ($extra_toolbar_buttons as $key => $value) $toolbar->icon_list[$key] = $value;
}
// add the help file index and build the toolbar
$toolbar->add_help('10');
echo $toolbar->build();
// Build the page
?>
<h1><?php echo PAGE_TITLE; ?></h1>

<fieldset>
<legend><?php echo TEXT_IMPORT; ?></legend>
  <table width="100%" border="0">
	<tr>
	  <td><?php echo GEN_BANK_IMPORT_MESSAGE; ?></td>
	</tr>
	<tr><td>
	  <table border="1">

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
	      <td>
	        <?php echo html_combo_box('bank_acct', $cash_chart, '', ''); ?>
	      </td>
	      <td>
			<?php echo html_file_field  ('file_name') . ' '; ?>
			<?php echo html_button_field('import_table', TEXT_IMPORT , 'onclick="submitToDo(\'import_csv\')"'); ?>
	      </td>
	       <td>
		    <?php echo html_button_field('sample_csv', TEXT_SAMPLE_CSV, 'onclick="submitToDo(\'sample_csv\')"'); ?>
	      </td>
		</tr>
<?php
    }
  }
}
?>
	  </table>
	</td></tr>
  </table>
</fieldset>

</form>