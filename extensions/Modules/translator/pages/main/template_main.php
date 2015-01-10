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
//  Path: /modules/translator/pages/main/template_main.php
//
echo html_form('translator', FILENAME_DEFAULT, gen_get_all_get_params(array('action', 'list'))) . chr(10);
echo html_hidden_field('action',   '') . chr(10);
echo html_hidden_field('rowSeq', '') . chr(10);
// set some defaults for the toolbar
$toolbar->icon_list['cancel']['params'] = 'onclick="location.href = \'' . html_href_link(FILENAME_DEFAULT, '', 'SSL') . '\'"';
$toolbar->icon_list['open']['show']     = false;
$toolbar->icon_list['delete']['show']   = false;
$toolbar->icon_list['save']['show']     = false;
$toolbar->icon_list['print']['show']    = false;
if ($security_level > 1) {
  $toolbar->add_icon('new', 'onclick="submitToDo(\'new\')"', $order = 10);
  $toolbar->add_icon('import', 'onclick="submitToDo(\'import\')"', $order = 13);
  $toolbar->add_icon('export', 'onclick="submitToDo(\'export_all\')"', $order = 16);
  $toolbar->icon_list['import']['text'] = TEXT_IMPORT_CURRENT_LANGUAGE;
  $toolbar->icon_list['export']['text'] = TEXT_EXPORT_CURRENT_LANGUAGE;
  $toolbar->icon_list['upload'] = array(
    'show'   => true,
    'icon'   => 'actions/document-save.png',
    'params' => 'onclick="submitToDo(\'upload\')"',
    'text'   => TEXT_UPLOAD_FROM_ZIPPED_FILE,
    'order'  => '20',
  );
}
echo $toolbar->build_toolbar(true);
?>
<h1><?php echo PAGE_TITLE; ?></h1>
<div id="filter_bar">
<div style="height:19px"><?php echo $query_split->display_count(TEXT_DISPLAY_NUMBER . TEXT_TRANSLATIONS); ?>
<div style="float:right"><?php echo $query_split->display_links(); ?></div>
</div>
<table class="ui-widget" style="border-collapse:collapse;width:100%">
 <tbody class="ui-widget-content">
  <tr>
	<td><?php echo TEXT_FILTERS. " : " . '&nbsp;' . TEXT_MODULE . html_pull_down_menu('f0', $sel_modules,  $mod); ?></td>
	<td><?php echo '&nbsp;' . TEXT_LANGUAGE .   '&nbsp;' . html_pull_down_menu('f1', $sel_language, $lang); ?></td>
	<td><?php echo '&nbsp;' . TEXT_VERSION  .   '&nbsp;' . html_pull_down_menu('f2', $sel_version,  $ver); ?></td>
	<td><?php echo '&nbsp;' . html_button_field('apply', TEXT_APPLY, 'onclick="submitToDo(\'filter_main\')"'); ?></td>
  </tr>
 </tbody>
</table>
</div>
<table class="ui-widget" style="border-collapse:collapse;width:100%">
 <thead class="ui-widget-header">
  <tr><?php echo $list_header; ?></tr>
 </thead>
 <tbody class="ui-widget-content">
<?php
  $odd = true;
  while (!$query_result->EOF) {
    $mod   = $query_result->fields['module'];
    $lang  = $query_result->fields['language'];
    $ver   = $query_result->fields['version'];
    $id    = $mod . ':' . $lang . ':' . $ver;
	$t     = $translator->fetch_stats($mod, $lang, $ver);
	$pct   = number_format(100*$t['trans']/$t['total'], 0);
?>
  <tr class="<?php echo $odd?'odd':'even'; ?>" style="cursor:pointer">
	<td align="center" onclick="submitSeq('<?php echo $id; ?>', 'edit')"><?php echo $mod; ?></td>
	<td align="center" onclick="submitSeq('<?php echo $id; ?>', 'edit')"><?php echo $query_result->fields['language']; ?></td>
	<td align="center" onclick="submitSeq('<?php echo $id; ?>', 'edit')"><?php echo $query_result->fields['version']; ?></td>
	<td align="center" onclick="submitSeq('<?php echo $id; ?>', 'edit')"><?php echo sprintf(TEXT_STATS_VALUES, $t['trans'], $t['total'], $pct); ?></td>
	<td align="right" >
<?php
	  if ($mod <> 'all' && $security_level > 2) echo html_icon('actions/edit-find-replace.png',    TEXT_EDIT,   'small', 'onclick="submitSeq(\'' . $id . '\', \'edit\')"') . chr(10);
	  if ($mod <> 'all' && $security_level > 2) echo html_icon('emblems/emblem-symbolic-link.png', TEXT_EXPORT, 'small', 'onclick="submitSeq(\'' . $id . '\', \'export\', true)"') . chr(10);
	  if ($security_level > 3) echo html_icon('emblems/emblem-unreadable.png', TEXT_DELETE, 'small', 'onclick="if (confirm(\'' . MESSAGE_DELETE_TRANSLATION . '\')) submitSeq(\'' . $id . '\', \'delete\')"') . chr(10);
?>
	</td>
  </tr>
<?php
      $query_result->MoveNext();
      $odd = !$odd;
    }
?>
 </tbody>
</table>
<div style="float:right"><?php echo $query_split->display_links(); ?></div>
<div><?php echo $query_split->display_count(TEXT_DISPLAY_NUMBER . TEXT_TRANSLATIONS); ?></div>
</form>
