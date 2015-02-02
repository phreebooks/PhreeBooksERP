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
// +-----------------------------------------------------------------+
//  Path: /modules/doc_ctl/pages/main/template_main.php
//

echo html_form('doc_ctl', FILENAME_DEFAULT, gen_get_all_get_params(array('action')), 'post', 'enctype="multipart/form-data"') . chr(10);
echo html_hidden_field('action', '')   . chr(10);
echo html_hidden_field('rowSeq', '') . chr(10);
// customize the toolbar actions
$toolbar->icon_list['cancel']['params'] = 'onclick="location.href = \'' . html_href_link(FILENAME_DEFAULT, '', 'SSL') . '\'"';
$toolbar->icon_list['open']['show']     = false;
$toolbar->icon_list['save']['show']     = false;
$toolbar->icon_list['delete']['show']   = false;
$toolbar->icon_list['print']['show']    = false;
$toolbar->icon_list['home'] = array(
	'show'   => true,
	'icon'   => 'actions/go-home.png',
	'params' => 'onclick="fetch_home()"',
	'text'   => TEXT_HOME,
	'order'  => '35',
);
$toolbar->add_help();
echo $toolbar->build();
?>
<h1><?php echo TEXT_DOCUMENT_CONTROL; ?></h1>
<table class="ui-widget" style="border-style:none;width:100%">
 <tbody class="ui-widget-content">
  <tr>
    <td width="30%" valign="top">
	  <fieldset>
        <legend><?php echo TEXT_DOCUMENT_LIST; ?></legend>
		<div id="description">
		  <div id="mmenu" style="height:50px; overflow:auto;">
		    <?php echo html_icon('actions/folder-new.png', sprintf(TEXT_NEW_ARGS, TEXT_FOLDER), 'medium', '', NULL, NULL, 'add_folder') . chr(10); ?>
		  	<?php echo html_icon('actions/document-new.png', sprintf(TEXT_NEW_ARGS, TEXT_DOCUMENT), 'medium', '', NULL, NULL, 'add_default') . chr(10); ?>
		  	<?php echo html_icon('apps/accessories-text-editor.png', TEXT_RENAME, 'medium', '', NULL, NULL, 'rename') . chr(10); ?>
		  	<?php echo html_input_field('text', '', '') . chr(10); ?>
		  	<?php echo html_icon('actions/system-search.png', TEXT_SEARCH, 'medium', '', NULL, NULL, 'search') . chr(10); ?>
		  	<?php echo html_icon('actions/view-refresh.png', TEXT_CLEAR,  'medium', '', NULL, NULL, 'clear_search') . chr(10); ?>
		  </div>
		  <!-- the tree container -->
		  <div id="demo" class="demo"></div>
		</div>
		<div style="clear:all">NOTE: To add a document or folder, select a folder to insert into and press the icon.</div>
	  </fieldset>
	</td>
	<td width="70%" valign="top">
      <fieldset>
        <legend><?php echo TEXT_DETAILS; ?></legend>
	    <div id="rightColumn">
			<?php if (file_exists($div_template)) {
				include ($div_template);
				echo $fieldset_content;
			} ?>
		</div>
	  </fieldset>
	</td>
  </tr>
 </tbody>
</table>
</form>
