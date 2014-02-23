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
//  Path: /modules/phreedom/pages/popup_setup/template_main.php
//
echo html_form('popup_setup', FILENAME_DEFAULT, gen_get_all_get_params(array('action')));
// include hidden fields
echo html_hidden_field('action', '') . chr(10);
echo html_hidden_field('rowSeq', $sID) . chr(10);
// customize the toolbar actions
$toolbar->icon_list['cancel']['params'] = 'onclick="self.close()"';
$toolbar->icon_list['open']['show']     = false;
$toolbar->icon_list['save']['params']   = 'onclick="submitToDo(\'save\')"';
$toolbar->icon_list['delete']['show']   = false;
$toolbar->icon_list['print']['show']    = false;
if (count($extra_toolbar_buttons) > 0) foreach ($extra_toolbar_buttons as $key => $value) $toolbar->icon_list[$key] = $value;
if($subject_module->help_path <> '') $toolbar->add_help($subject_module->help_path);
echo $toolbar->build_toolbar(); 
// Build the page
?>
<h1><?php echo $subject_module->title; ?></h1>
<?php
try{
	echo $subject_module->build_form_html($_REQUEST['action'], $sID); 
}catch(Exception $e){
	$messageStack->add($e->getMessage(), $e->getCode);
}
	?>
</form>
