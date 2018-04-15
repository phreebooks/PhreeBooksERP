<?php
/*
 * View for Tools -> Import/Export -> Beginning Balance tab
 *
 * NOTICE OF LICENSE
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.TXT.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/OSL-3.0
 *
 * DISCLAIMER
 * Do not edit or add to this file if you wish to upgrade Bizuno to newer
 * versions in the future. If you wish to customize Bizuno for your
 * needs please refer to http://www.phreesoft.com for more information.
 *
 * @name       Bizuno ERP
 * @author     Dave Premo, PhreeSoft <support@phreesoft.com>
 * @copyright  2008-2018, PhreeSoft
 * @license    http://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 * @version    2.x Last Update: 2016-12-23

 * @filesource /lib/view/module/phreebooks/tabToolsBegBal.php
 */

namespace bizuno;

$output['body'] .= html5('frmBegBal', $data['form']['frmBegBal'])."\n";
$output['body'] .= '
<table style="border-style:none;margin-left:auto;margin-right:auto;">
 <thead class="panel-header">
  <tr>
   <th>'.lang('journal_main_gl_acct_id').'</th>
   <th nowrap="nowrap">'.lang('description')               .'</th>
   <th nowrap="nowrap">'.lang('journal_item_gl_type')      .'</th>
   <th nowrap="nowrap">'.lang('journal_item_debit_amount') .'</th>
   <th nowrap="nowrap">'.lang('journal_item_credit_amount').'</th>
  </tr>
 </thead>
 <tbody>'."\n";
foreach ($data['values']['beg_bal'] as $glAcct => $values) {
	$output ['body'] .= "  <tr>\n";
	$output ['body'] .= '   <td align="center">'.$glAcct."</td>\n";
	$output ['body'] .= "   <td>".$values['desc']."</td>\n";
	$output ['body'] .= "   <td>".$values['desc_type']."</td>\n";
	$data['fields']['bb_value']['attr']['value'] = $values['value'];
	if ($values['asset']) {
		$output ['body'] .= '<td style="text-align:center">'.html5("debits[$glAcct]", $data['fields']['bb_value'])."</td>\n";
		$output ['body'] .= '<td style="background-color:#CCCCCC">&nbsp;</td>'."\n";
	} else { // credit
		$output ['body'] .= '<td style="background-color:#CCCCCC">&nbsp;</td>'."\n";
		$output ['body'] .= '<td style="text-align:center">'.html5("credits[$glAcct]", $data['fields']['bb_value'])."</td>\n";
	}
	$output ['body'] .= "</tr>\n";
}
$output ['body'] .= '
 </tbody>
 <tfoot class="panel-header">
  <tr>
   <td colspan="3" align="right">'.lang('total').'</td>
   <td style="text-align:right">'.html5('bb_debit_total',  $data['fields']['bb_debit_total']) .'</td>
   <td style="text-align:right">'.html5('bb_credit_total', $data['fields']['bb_credit_total']).'</td>
  </tr>
  <tr>
   <td colspan="4" style="text-align:right">'.lang('balance').'</td>
   <td style="text-align:right">'.html5('bb_balance_total', $data['fields']['bb_balance_total']).'</td>
   <td colspan="4" style="text-align:right">'.html5('btnSaveBegBal', $data['fields']['btnSaveBegBal']).'</td>
  </tr>
 </tfoot>
</table>'."\n";
$output['body'] .= "</form>\n";
$output['jsBody'][]  = "
function begBalTotal() {
	var debits = 0;
	var credits= 0;
	var balance= 0;
	jq('input[name^=debits]').each(function() { debits += cleanCurrency(jq(this).val()); });
	jq('input[name^=credits]').each(function(){ credits+= cleanCurrency(jq(this).val()); });
	balance = debits - credits;
	jq('#bb_debit_total').val(formatCurrency(debits));
	jq('#bb_credit_total').val(formatCurrency(credits));
	jq('#bb_balance_total').val(formatCurrency(balance));
	if (balance == 0) jq('#bb_balance_total').css({color:'#000000'});
	else jq('#bb_balance_total').css({color:'red'});
}
ajaxForm('frmBegBal');";
