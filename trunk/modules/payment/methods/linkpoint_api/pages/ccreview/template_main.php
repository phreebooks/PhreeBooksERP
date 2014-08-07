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
//  Initially Written By: Harry Lu @ 2009/08/01
//  Path: /modules/payment/methods/linkpoint/pages/ccreview/template_main.php
//
	
// start the form
echo html_form('cc_view', FILENAME_DEFAULT, gen_get_all_get_params(array('action'))) . chr(10);

// include hidden fields
echo html_hidden_field('action', '') . chr(10);	

// customize the toolbar actions
$toolbar->icon_list['cancel']['params'] = 'onclick="location.href = \'' . html_href_link(FILENAME_DEFAULT, '', 'SSL') . '\'"';
$toolbar->icon_list['open']['show']     = false;
$toolbar->icon_list['save']['show']     = false;
$toolbar->icon_list['delete']['show']   = false;
$toolbar->icon_list['print']['show']    = false;

// pull in extra toolbar overrides and additions
if (count($extra_toolbar_buttons) > 0) {
	foreach ($extra_toolbar_buttons as $key => $value) $toolbar->icon_list[$key] = $value;
}

// add the help file index and build the toolbar	
$toolbar->search_period = $acct_period;
echo $toolbar->build_toolbar($add_search = true, $add_periods = true); 

// Build the page
?>
<h1><?php echo BOX_BANKING_LINK_POINT_CC_REVIEW; ?></h1>
<div style="height:19px"><?php echo $query_split->display_count(TEXT_DISPLAY_NUMBER . TEXT_ITEMS); ?>
<div style="float:right"><?php echo $query_split->display_links(); ?></div>
</div>
<table>
  <tr valign="top"><th><?php echo $list_header; ?></th></tr>
<?php 
  $odd = true;
  while (!$customers->EOF) { ?>
         <tr class="<?php echo $odd?'odd':'even'; ?>">
                <td align="right"><?php echo $customers->fields['id']; ?></td>
                <td align="right"><?php echo $customers->fields['short_name']; ?></td>
                <td><?php echo $customers->fields['contact_last']; ?></td>
                <td><?php echo $customers->fields['contact_first']; ?></td>
                <td><?php echo $customers->fields['entry_company']; ?></td>

                <td>
                  <?php echo 'Credit Card Server Time: <strong>' . ($customers->fields['transaction_response_time'] == '' ? 'Not Connected' : $customers->fields['transaction_response_time']) . '</strong>'; ?>
                </td>
                <td>
                  <?php echo 'This Server Time: <strong>' . $customers->fields['date_added'] . '</strong>'; ?>
                </td>
                <td align="right" style="color:red;">
                  <?php echo $currencies->format($customers->fields['chargetotal']); ?>
                </td>
                <td align="center">
                  <?php echo $customers->fields['first_date']; ?>
                </td>
                <td align="right">
                </td>
              </tr>
              <tr>
                <td colspan="4">
                  <?php echo
                    ($customers->fields['transaction_result'] != 'APPROVED' ? '<b>' . $customers->fields['transaction_result'] . '</b>' : $customers->fields['transaction_result']) . '<br />' .
                    $customers->fields['cc_number'] . '<br />' .
                    'Expires: ' . $customers->fields['cc_expire'] . '<br />' .
                    $customers->fields['lp_trans_num'] . '<br />' .
                    $customers->fields['transaction_reference_number'] . '<br />' .
                    ($customers->fields['avs_response'] != 'YYYM' ? '<b>' . $customers->fields['avs_response'] . '</b>' : $customers->fields['avs_response']) . ' ' . ($customers->fields['r_error'] != '' ? '<b>' . $customers->fields['r_error'] . '</b>' : '') . '<br />' .
                    $customers->fields['transaction_time'];
                  ?>
                </td>
                <td colspan="6"><?php echo str_replace(array('PREAUTH','SALE'),array('<span style="color:orange;"><strong>PREAUTH</strong></span>','<span style="color:green;"><strong>SALE</strong></span>'), $customers->fields['cust_info']) . '<br /><br />'; ?></td>
              </tr>
              <tr>
                <td colspan="10"><hr /></td>
              </tr>
<?php
      $customers->MoveNext();
      $odd = !$odd;
    }
?>
</table>
<div style="float:right"><?php echo $query_split->display_links(); ?></div>
<div><?php echo $query_split->display_count(TEXT_DISPLAY_NUMBER . TEXT_ITEMS); ?></div>

