<?php
/*
 * Template for Inventory prices - byContact
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

 * @filesource lib/view/base/inventory/divByContact.php
 */

namespace bizuno;

$code = $data['values']['pricesCode'];
$type = $data['fields']['contact_type']['attr']['value'];

$output['body'] .= "<h2>".$data['lang']['title']."</h2><p>";
$output['body'] .= html5('item'.$code, ['attr'=>['type'=>'hidden']]);
$output['body'] .= html5('id'  .$code, $data['fields']['id']);
if ($data['values']['inContacts']) { // we're in the contact form, hide contact_id field and set to current form value
    $output['body'] .= html5('contact_id'.$code, ['attr'=>  ['type'=>'hidden']]);
    $output['jsBody'][] = "jq('#contact_id$code').val(jq('#id').val());";
} else {
    $output['body'] .= html5('contact_id'.$code, $data['fields']['contact_id'])."<br />";
    $output['jsBody'][]  = "
var cID = jq('#contact_id$code').val();
jq('#contact_id$code').combogrid({ width:250, panelWidth:425, delay:500, idField:'id', textField:'primary_name', mode:'remote',
	url:     '".BIZUNO_AJAX."&p=contacts/main/managerRows&type=$type&clr=1&rID='+cID,
	columns: [[{field:'id',hidden:true},{field:'primary_name',title:'".jsLang('address_book_primary_name')."',width:250},{field:'telephone1',title:'".jsLang('telephone')."',width:150}]]
});";
}
$output['body'] .= html5('inventory_id'.$code, $data['fields']['inventory_id'])."<br />";
$output['body'] .= html5('ref_id'      .$code, $data['fields']['ref_id']);
$output['body'] .= html5('currency'    .$code, $data['fields']['currency']);
$output['body'] .= "</p>";

$output['jsBody'][]  = "
var dgPricesSetData = ".json_encode($data['values']['prices']).";
var qtySource = ".json_encode(viewKeyDropdown($data['values']['qtySource'])).";
var qtyAdj    = ".json_encode(viewKeyDropdown($data['values']['qtyAdj'])).";
var qtyRnd    = ".json_encode(viewKeyDropdown($data['values']['qtyRnd'])).";
var rID = jq('#inventory_id$code').val();
jq('#inventory_id$code').combogrid({ width:250, panelWidth:350, delay:500, idField:'id', textField:'description_short', mode:'remote',
	url:        '".BIZUNO_AJAX."&p=inventory/main/managerRows&clr=1&rID='+rID,
	onClickRow: function (id, data) { jq('#item_cost').val(data.item_cost); jq('#full_price').val(data.full_price); },
	columns:    [[{field:'id',hidden:true},{field:'sku',title:'".jsLang('sku')."',width:100},{field:'description_short',title:'".jsLang('description')."',width:200}]]
});
function preSubmitPrices() {
	jq('#dgPricesSet').edatagrid('saveRow');
	var items = jq('#dgPricesSet').datagrid('getData');
	var serializedItems = JSON.stringify(items);
	jq('#item$code').val(serializedItems);
	return true;
}";
htmlDatagrid($output, $data, 'dgPricesSet');
