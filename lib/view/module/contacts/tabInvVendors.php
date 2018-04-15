<?php
/*
 * View for Vendors fieldset on inventory details - General tab
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
 * @version    2.x Last Update: 2016-03-24

 * @filesource /lib/view/module/contacts/tabInvVendors.php
 */

namespace bizuno;

$inventory_type = $data['fields']['inventory_type']['attr']['value'];
$output['body'] .= '
<fieldset><legend>'.lang('details').' ('.lang('vendors').')</legend>
<table style="border-style:none;width:100%">
	<tbody>
		<tr>
			<td colspan="2">'.html5('description_purchase', $data['fields']['description_purchase'])."</td>
		</tr>\n";
if (validateSecurity('inventory', 'prices_v', 1, false)) {
    $output['body'] .= "      <tr><td>".html5('item_cost',     $data['fields']['item_cost']).(sizeof(getModuleCache('phreebooks', 'currency', 'iso'))>1 ? ' ('.getUserCache('profile', 'currency', false, 'USD').')' : '');
    if (isset($data['fields']['id']['attr']['value']) && $data['fields']['id']['attr']['value']) { 
        $output['body'] .= html5('show_prices_v', $data['show_prices_v']);   
    }
    if (isset($data['fields']['id']['attr']['value']) && $data['fields']['id']['attr']['value'] && in_array($inventory_type, array('ma', 'sa'))) {
        $output['body'] .= html5('assy_cost', $data['assy_cost']);
    }
    $output['body'] .= "</td><td>".html5('tax_rate_id_v', $data['fields']['tax_rate_id_v'])."</td></tr>";
}
if (validateSecurity('inventory', 'prices_v', 1, false)) {
    $output['body'] .= "<tr><td>".(sizeof(getModuleCache('inventory', 'prices')) ? html5('price_sheet_v', $data['fields']['price_sheet_v']) : '&nbsp;')."</td>";
} else {
    $output['body'] .= "<tr><td>&nbsp;</td>";
}
$output['body'] .= "<td>".html5('vendor_id', $data['fields']['vendor_id'])."</td></tr>
	</tbody>
</table>
</fieldset>\n";
