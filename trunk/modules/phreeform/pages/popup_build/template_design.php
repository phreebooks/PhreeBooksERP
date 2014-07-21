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
//  Path: /modules/phreeform/pages/popup_build/template_design.php
//
echo html_form('popup_build', FILENAME_DEFAULT, gen_get_all_get_params(array('action')), 'post', 'enctype="multipart/form-data"') . chr(10);
// include hidden fields
echo html_hidden_field('action',       '') . chr(10);
echo html_hidden_field('rID',        $rID) . chr(10);
echo html_hidden_field('reporttype', $report->reporttype) . chr(10);
// customize the toolbar actions
$toolbar->icon_list['cancel']['params'] = 'onclick="self.close();"';
$toolbar->icon_list['open']['show']     = false;
$toolbar->icon_list['save']['params']   = 'onclick="submitToDo(\'save\')"';
$toolbar->icon_list['print']['show']    = false;
$toolbar->icon_list['delete']['show']   = false;
$toolbar->icon_list['preview'] = array(
  'show'   => true, 
  'icon'   => 'actions/edit-find.png',
  'params' => 'onclick="submitToDo(\'preview\')"', 
  'text'   => TEXT_SAVE_PREVIEW, 
  'order'  => '20',
);
$toolbar->add_help('11.01.01');
echo $toolbar->build_toolbar(); 

// Build the page
?>
<h2 align="center"><?php echo PAGE_TITLE . ' - ' . ($report->title ? $report->title : TEXT_NEW); ?></h2>
<div id="buildtabs">
<ul>
<?php 
  echo add_tab_list('tab_page',  TEXT_PAGE_SETUP);
  echo add_tab_list('tab_db',    TEXT_DATABASE_SETUP);
  echo add_tab_list('tab_field', TEXT_FIELD_SETUP);
  echo add_tab_list('tab_crit',  TEXT_CRITERIA);
  echo add_tab_list('tab_prop',  TEXT_PROPERTIES);
  // pull in additional custom tabs
  if (isset($extra_designer_tabs) && is_array($extra_designer_tabs)) {
	foreach ($extra_designer_tabs as $tabs) echo add_tab_list('tab_'.$tabs['tab_id'], $tabs['tab_title']);
  }
?>
</ul>
<?php
require (DIR_FS_WORKING . 'pages/popup_build/tab_page_setup.php');
require (DIR_FS_WORKING . 'pages/popup_build/tab_db_setup.php');
if ($report->reporttype == 'frm' ) {
	require (DIR_FS_WORKING . 'pages/popup_build/tab_frm_field_setup.php');
} else {
	require (DIR_FS_WORKING . 'pages/popup_build/tab_rpt_field_setup.php');
}
require (DIR_FS_WORKING . 'pages/popup_build/tab_crit_setup.php');
require (DIR_FS_WORKING . 'pages/popup_build/tab_prop_setup.php');

// pull in additional custom tabs
if (isset($extra_designer_tabs) && is_array($extra_designer_tabs)) {
  foreach ($extra_designer_tabs as $tabs) {
    $file_path = DIR_FS_WORKING . 'custom/phreeform/main/' . $tabs['tab_filename'] . '.php';
    if (file_exists($file_path)) { require($file_path); }
  }
}
?>
</div>
</form>
