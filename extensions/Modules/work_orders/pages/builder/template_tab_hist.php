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
//  Path: /modules/work_orders/pages/builder/template_tab_hist.php
//
if ($id) {
$result = $db->Execute("select wo_num, qty, post_date, close_date from " . TABLE_WO_JOURNAL_MAIN . " 
  where wo_id = " . $id . " order by post_date desc limit 20");
}
?>
<div title="<?php echo TEXT_HISTORY;?>" id="tab_history">
  <fieldset>
    <legend><?php echo TEXT_HISTORY; ?></legend>
	<table class="ui-widget" style="border-collapse:collapse;width:400px;margin-left:auto;margin-right:auto">
	 <thead class="ui-widget-header">
	  <tr><th colspan="5"><?php echo sprintf(TEXT_WO_HISTORY, LIMIT_HISTORY_RESULTS); ?></th></tr>
	  <tr>
	    <th><?php echo TEXT_WO_ID ; ?></th>
	    <th><?php echo TEXT_QUANTITY; ?></th>
	    <th><?php echo TEXT_POST_DATE; ?></th>
	    <th><?php echo TEXT_CLOSE_DATE; ?></th>
	  </tr>
	 </thead>
	 <tbody class="ui-widget-content">
<?php
  if ($result->fields) {
	$odd = true;
    while (!$result->EOF) {
	  echo '<tr class="'.($odd?"odd":"even").'">' . chr(10);
	  echo '  <td align="center">' . $result->fields['wo_num'] . '</td>' . chr(10);
	  echo '  <td align="center">' . $result->fields['qty'] . '</td>' . chr(10);
	  echo '  <td align="center">' . gen_locale_date($result->fields['post_date']) . '</td>' . chr(10);
	  echo '  <td align="center">' . gen_locale_date($result->fields['close_date']) . '</td>' . chr(10);
	  echo '</tr>' . chr(10);
	  $odd = !$odd;
	  $result->MoveNext();
    }
  } else {
	echo '<tr><td align="center" colspan="4">' . ACT_NO_RESULTS . '</td></tr>';
  }
?>
	</tbody>
	</table>
  </fieldset>
</div>
