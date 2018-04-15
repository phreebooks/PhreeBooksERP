<?php
/*
 * Template for Inventory prices - Quantity
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
 * @version    2.x Last Update: 2017-02-23

 * @filesource lib/view/base/inventory/divQuantity.php
 */

namespace bizuno;

$code = $data['values']['pricesCode'];
$defAttr= ['label'=>lang('default'), 'attr'=>  ['type'=>'checkbox', 'value'=>1]];
if (isset($data['values']['settings']['default']) && $data['values']['settings']['default']) { $defAttr['attr']['checked'] = 'checked'; }

$output['body'] .= "<h2>".$data['lang']['title']."</h2><p>";
$output['body'] .= html5('id'      .$code, $data['fields']['id']);
$output['body'] .= html5('item'    .$code, ['attr' =>  ['type'=>'hidden']]);
$output['body'] .= html5('title'   .$code, $data['fields']['title']);
$output['body'] .= html5('currency'.$code, $data['fields']['currency']);
$output['body'] .= html5('default' .$code, $defAttr);
$output['body'] .= "</p>";

$output['jsBody'][] = "
var dgPricesSetData = ".json_encode($data['values']['prices']).";
var qtySource= ".json_encode(viewKeyDropdown($data['values']['qtySource'])).";
var qtyAdj   = ".json_encode(viewKeyDropdown($data['values']['qtyAdj'])).";
var qtyRnd   = ".json_encode(viewKeyDropdown($data['values']['qtyRnd'])).";
function preSubmitPrices() {
	jq('#dgPricesSet').edatagrid('saveRow');
	var items = jq('#dgPricesSet').datagrid('getData');
	var serializedItems = JSON.stringify(items);
	jq('#item$code').val(serializedItems);
	return true;
}";
htmlDatagrid($output, $data, 'dgPricesSet');
