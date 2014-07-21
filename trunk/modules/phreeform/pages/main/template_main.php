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
//  Path: /modules/phreeform/pages/main/template_main.php
//
echo html_form('phreeform', FILENAME_DEFAULT, gen_get_all_get_params(array('action', 'list'))) . chr(10);
echo html_hidden_field('id',     $id) . chr(10);
echo html_hidden_field('action',    '') . chr(10);
echo html_hidden_field('rowSeq',  '') . chr(10);
echo html_hidden_field('newName', '') . chr(10);
// customize the toolbar actions
$toolbar->icon_list['cancel']['params'] = 'onclick="location.href = \'' . html_href_link(FILENAME_DEFAULT, '', 'SSL') . '\'"';
$toolbar->icon_list['open']['show']     = false;
$toolbar->icon_list['save']['show']     = false;
$toolbar->icon_list['delete']['show']   = false;
$toolbar->icon_list['print']['show']    = false;
if ($security_level > 1) $toolbar->add_icon('import', 'onclick="ReportPopup(\'import\')"', $order = 50);
$toolbar->icon_list['home'] = array(
	'show'   => true, 
	'icon'   => 'actions/go-home.png',
	'params' => 'onclick="location.href = \'' . html_href_link(FILENAME_DEFAULT, gen_get_all_get_params(array('action')), 'SSL') . '\'"', 
	'text'   => TEXT_HOME, 
	'order'  => '25',
);
if ($security_level > 1) {
  $toolbar->icon_list['new_rpt'] = array(
	'show'   => true, 
	'icon'   => 'mimetypes/text-x-generic.png',
	'params' => 'onclick="ReportPopup(\'new_rpt\')"', 
	'text'   => TEXT_NEW_REPORT, 
	'order'  => '30',
  );
  $toolbar->icon_list['new_frm'] = array(
	'show'   => true, 
	'icon'   => 'mimetypes/text-html.png',
	'params' => 'onclick="ReportPopup(\'new_frm\')"', 
	'text'   => TEXT_NEW_FORM, 
	'order'  => '35',
  );
}
$toolbar->add_help();
echo $toolbar->build_toolbar($add_search = true);

?>
<h1><?php echo TEXT_REPORTS; ?></h1>
<table class="ui-widget" style="border-style:none;width:100%">
  <tr>
    <td width="30%" valign="top">
      <fieldset>
        <legend><?php echo TEXT_DOCUMENTS; ?></legend>
		<?php echo '<a href="javascript:Expand(\'' . 'dc_' . '\');">' . TEXT_EXPAND_ALL . '</a> - <a href="javascript:Collapse(\'' . 'dc_' . '\');">' . TEXT_COLLAPSE_ALL . '</a><br />' . chr(10); ?>
	    <?php echo build_dir_html('dir_tree', $toc_array); ?>
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
</table>
</form>
