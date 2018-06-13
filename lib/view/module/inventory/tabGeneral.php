<?php
/*
 * View for inventory details - general tab, first section and customers fieldset
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
 * @version    2.x Last Update: 2016-12-30
 * @filesource /lib/view/module/inventory/tabGeneral.php
 */

namespace bizuno;

$inventory_type = $viewData['fields']['inventory_type']['attr']['value'];
if ($viewData['fields']['inventory_type']['values'][$inventory_type]['gl_inv'] === false) {
	$viewData['fields']['gl_inv']['attr']['type'] = 'hidden';
}
if ($viewData['fields']['inventory_type']['values'][$inventory_type]['gl_cogs'] === false) {
	$viewData['fields']['gl_cogs']['attr']['type'] = 'hidden';
}
if (!isset($viewData['fields']['image_with_path']['attr']['value'])) { $viewData['fields']['image_with_path']['attr']['value'] = ''; }

if (sizeof(getModuleCache('bizuno', 'stores')) > 1) {
	$divStores = '<td rowspan="9" style="vertical-align:top;"><table style="border-style:none;"><thead class="tabs-header"><tr><th>'.lang('contacts_type_b')."</th><th>".lang('inventory_qty_stock').'</th></tr></thead><tbody>'."\n";
    foreach (getModuleCache('bizuno', 'stores') as $store) {
        if (!isset($viewData['stores'][$store['id']]['stock'])) { $viewData['stores'][$store['id']]['stock'] = 0; }
        $divStores .= "    <tr><td>{$store['text']}</td><td style=\"text-align:center\">{$viewData['stores'][$store['id']]['stock']}</td></tr>\n";
    }
	$divStores .= "    </tbody></table></td>\n";
} else { $divStores= ''; }

$output['body'] .= html5('id',      $viewData['fields']['id']);
$output['body'] .= html5('dg_assy', ['attr'=> ['type'=>'hidden']])."\n";
$output['body'] .= "
<table>
	<tr>
		<td>".html5('sku',      $viewData['fields']['sku']).html5('inactive', $viewData['fields']['inactive'])."</td>
		<td>".html5('qty_stock',$viewData['fields']['qty_stock'])."</td>
        <td>".html5('store_id', $viewData['fields']['store_id']) .'</td>
		<td rowspan="5">'.html5('image_with_path', $viewData['fields']['image_with_path'])."</td>
	</tr>
	<tr>
		<td>".html5('description_short',$viewData['fields']['description_short']).html5('where_used', $viewData['where_used'])."</td>
		<td>".html5('qty_po',           $viewData['fields']['qty_po'])."</td>".
        $divStores."
	</tr>
	<tr>
		<td>".html5('qty_min',          $viewData['fields']['qty_min'])."</td>
		<td>".html5('qty_alloc',        $viewData['fields']['qty_alloc'])."</td>
	</tr>
	<tr>
		<td>".html5('qty_restock',      $viewData['fields']['qty_restock'])."</td>
		<td>".html5('qty_so',           $viewData['fields']['qty_so'])."</td>
	</tr>
	<tr>
		<td>".html5('lead_time',        $viewData['fields']['lead_time'])."</td>
		<td>".html5('item_weight',      $viewData['fields']['item_weight']).' ('.getModuleCache('inventory', 'settings', 'general', 'weight_uom').')</td>
	</tr>
</table>
<fieldset><legend>'.lang('details').' ('.lang('customers').')</legend>
<table style="border-style:none;width:100%">
	<tbody>
		<tr><td colspan="2">'.html5('description_sales', $viewData['fields']['description_sales'])."</td></tr>
		<tr>
			<td>".html5('full_price', $viewData['fields']['full_price']).(sizeof(getModuleCache('phreebooks', 'currency', 'iso'))>1 ? ' ('.getUserCache('profile', 'currency', false, 'USD').')' : '');
if (isset($viewData['fields']['id']['attr']['value']) && $viewData['fields']['id']['attr']['value']) { 
    $output['body'] .= html5('show_prices_c', $viewData['show_prices_c']);
}
$output['body'] .= "
			</td>
			<td>".html5('tax_rate_id_c',  $viewData['fields']['tax_rate_id_c'])."</td>
		</tr>
		<tr>
			<td>".(getModuleCache('inventory', 'prices') ? html5('price_sheet_c', $viewData['fields']['price_sheet_c']) : '&nbsp;')."</td>
		</tr>
	</tbody>
</table>
</fieldset>\n";

$imgSrc = $viewData['fields']['image_with_path']['attr']['value'];
$imgDir = dirname($viewData['fields']['image_with_path']['attr']['value']).'/';
$output['jsBody'][] = "imgManagerInit('image_with_path', '$imgSrc', '$imgDir', 'images/');";
