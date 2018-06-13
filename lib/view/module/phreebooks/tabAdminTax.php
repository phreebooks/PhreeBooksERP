<?php
/*
 * View for PhreeBooks settings - Purchase / Sales tax
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
 * @version    2.x Last Update: 2017-04-14
 * @filesource /lib/view/module/phreebooks/tabAdminTax.php
 */

namespace bizuno;

$type = isset($prop['settings']) ? $prop['settings']['type'] : 'c';
htmlToolbar($output, $viewData, 'tbTax');
$output['body'] .= "
".html5('frmTax'.$type,$viewData['forms']['frmTax'.$type]).'
	'.html5('id'.$type,$viewData['fields']['id'])  .'
	'.html5('settings'.$type, ['attr'=> ['type'=>'hidden']]).'
			<table>
		<tbody>
			<tr>
				<td>'.html5('title'     .$type, $viewData['fields']['title'])     .'</td>
                <td>'.html5('inactive'  .$type, $viewData['fields']['inactive'])  .'</td>
            </tr><tr>
				<td>'.html5('start_date'.$type, $viewData['fields']['start_date']).'</td>
				<td>'.html5('end_date'  .$type, $viewData['fields']['end_date'])  .'</td>
			</tr>
		</tbody>
	</table>';
htmlDatagrid($output, $viewData, 'dgTaxVendors');
$output['body'] .= '</form>';

$output['jsBody'][] = "ajaxForm('frmTax$type');
function taxTotal$type(newVal) {
	var total = 0;
	if (typeof curIndex == 'undefined') return;
	jq('#dgTaxVendors$type').datagrid('getRows')[curIndex]['rate'] = newVal;
	var items = jq('#dgTaxVendors$type').datagrid('getData');
	for (var i=0; i<items['rows'].length; i++) {
		total += parseFloat(items['rows'][i]['rate']);
	}
	var footer= jq('#dgTaxVendors$type').datagrid('getFooterRows');
	footer[0]['rate'] = formatNumber(total);
	jq('#dgTaxVendors$type').datagrid('reloadFooter');
}
function taxPreSubmit{$type}(type) {
	jq('#dgTaxVendors$type').edatagrid('saveRow');
	var items = jq('#dgTaxVendors$type').datagrid('getData');
	var serializedItems = JSON.stringify(items);
	jq('#settings'+type).val(serializedItems);
}";
