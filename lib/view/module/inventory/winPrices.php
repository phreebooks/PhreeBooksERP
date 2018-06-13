<?php
/*
 * View for the price popup
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
 * @version    2.x Last Update: 2017-06-03
 * @filesource /lib/view/module/inventory/winPrices.php
 */

namespace bizuno;

$output['body'] .= '
<table style="border-collapse:collapse;">
    <thead class="panel-header"><tr><th colspan="2">'.lang('general').'</th></tr></thead>
    <tbody>
        <tr class="panel-header"><td style="width:125px;">'.lang('qty').'</td><td style="width:125px;">'.lang('price').'</td></tr>
        <tr><td style="width:125px;">'.pullTableLabel(BIZUNO_DB_PREFIX."inventory", 'price').'</td>
            <td style="width:125px;">'.viewFormat($viewData['values']['price'], 'currency').'</td></tr>
        <tr><td style="width:125px;">'.pullTableLabel(BIZUNO_DB_PREFIX."inventory", 'full_price').'</td>
            <td style="width:125px;">'.viewFormat($viewData['values']['full'], 'currency') .'</td></tr>';
if (validateSecurity('phreebooks', 'j6_mgr', 1, false)) {
    $output['body'] .= '        <tr><td style="width:125px;">'.pullTableLabel(BIZUNO_DB_PREFIX."inventory", 'item_cost') .'</td>
            <td style="width:125px;">'.viewFormat($viewData['values']['cost'], 'currency') .'</td></tr>';
}
$output['body'] .= '    </tbody>
</table>'."\n";
if (isset($viewData['values']['sheets']) && is_array($viewData['values']['sheets']) && sizeof($viewData['values']['sheets'])) {
	$output['body'] .= '<table style="border-collapse:collapse;">'."\n";
	$output['body'] .= ' <thead class="panel-header"><tr><th colspan="2">'.lang('inventory_prices')."</th></tr></thead>\n";
	$output['body'] .= " <tbody>\n";
	foreach ($viewData['values']['sheets'] as $level) {
		$output['body'] .= '  <tr class="panel-header"><td colspan="2" style="text-align:center;">'.$level['title']."</td></tr>\n";
		$output['body'] .= '  <tr class="panel-header"><td style="width:125px;">'.lang('qty').'</td><td style="width:125px;">'.lang('price')."</td></tr>\n";
        foreach ($level['levels'] as $entry) { 
            $output['body'] .= '  <tr><td style="width:125px;">'.$entry['qty'].'</td><td style="width:125px;">'.viewFormat($entry['price'], 'currency')."</td></tr>\n";
        }
	}
	$output['body'] .= " </tbody>\n";
	$output['body'] .= "</table>\n";
}
