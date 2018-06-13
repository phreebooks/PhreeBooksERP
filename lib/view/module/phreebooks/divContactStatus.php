<?php
/*
 * View for contact account status
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
 * @version    2.x Last Update: 2017-01-27
 * @filesource /lib/view/module/phreebooks/divContactStatus.php
 */

namespace bizuno;

if (isset($viewData['text']['alertMsg']) && $viewData['text']['alertMsg']) {
	$output['body'] .= '<div style="font-weight:bold;text-align:center;background-color:'.$viewData['text']['alertBg'].'">'.$viewData['text']['alertMsg']."</div>\n";
}
$output['body'] .= '
 <table style="border-collapse:collapse;width:100%">
    <thead class="panel-header"><tr><th>'.pullTableLabel(BIZUNO_DB_PREFIX."contacts", 'terms')."</th></tr></thead>
    <tbody>
        <tr><td>".$viewData['values']['text_terms']."</td></tr>\n";
if (isset($viewData['text']['past_due']) && $viewData['text']['past_due'] > 0) {
	$output['body'] .= '       <tr><td style="background-color:yellow;">'.sprintf($viewData['lang']['msg_contact_past_due_amount'], viewFormat($viewData['text']['past_due'], 'currency'))."</td></tr>\n";
}
$output['body'] .= "
    </tbody>
</table>".'
<table style="border-collapse:collapse;width:100%;">
    <thead>
        <tr>
            <td colspan="2" style="text-align:center" class="panel-header">'.lang('history').'</td>
            <td>&nbsp;</td>
            <td colspan="2" style="text-align:center" class="panel-header">'.lang('account').'</td>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>'.$viewData['lang']['status_orders_invoice']."</td>
            <td>".(isset($viewData['fields']['inv_orders'])? html5('inv_orders', $viewData['fields']['inv_orders']): lang('none'))."</td>
            <td>&nbsp;</td>
            <td>".$viewData['text']['age_1'].'</td>
            <td style="text-align:right">'.$viewData['values']['bal_1']."</td>
        </tr>
        <tr>
            <td>".$viewData['lang']['status_open_j9']."</td>
            <td>".(isset($viewData['fields']['open_quotes'])? html5('open_quotes', $viewData['fields']['open_quotes']): lang('none'))."</td>
            <td>&nbsp;</td>
            <td >".$viewData['text']['age_2'].'</td>
            <td style="text-align:right">'.$viewData['values']['bal_2']."</td>
        </tr>
        <tr>
            <td>".$viewData['lang']['status_open_j10']."</td>
            <td>".(isset($viewData['fields']['open_orders'])? html5('open_orders', $viewData['fields']['open_orders']): lang('none'))."</td>
            <td>&nbsp;</td>
            <td>".$viewData['text']['age_3'].'</td>
            <td style="text-align:right">'.$viewData['values']['bal_3']."</td>
        </tr>
        <tr>
            <td>".$viewData['lang']['status_open_j12']."</td>
            <td>".(isset($viewData['fields']['unpaid_inv']) ? html5('unpaid_inv', $viewData['fields']['unpaid_inv']) : lang('none'))."</td>
            <td>&nbsp;</td>
            <td>".$viewData['text']['age_4'].'</td>
            <td style="text-align:right">'.$viewData['values']['bal_4']."</td>
        </tr>
        </tr>
            <td>".$viewData['lang']['status_open_j13']."</td>
            <td>".(isset($viewData['fields']['unpaid_crd']) ? html5('unpaid_crd', $viewData['fields']['unpaid_crd']) : lang('none'))."</td>
            <td>&nbsp;</td>
            <td>".lang('total').'</td>
            <td style="text-align:right">'.$viewData['values']['total'].'</td>
        <tr>
    </tbody>
</table>
<table style="border-collapse:collapse;width:100%;">
    <thead class="panel-header"><tr><th colspan="5">'.lang('notes').'</th></tr></thead>
    <tbody><tr><td colspan="5">'.$viewData['values']['notes']."</td></tr></tbody>
</table>\n";