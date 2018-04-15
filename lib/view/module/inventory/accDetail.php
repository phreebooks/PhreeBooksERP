<?php
/*
 * Template for Inventory details with tabs
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
 * @version    2.x Last Update: 2018-02-13
 * @filesource /lib/view/module/inventory/accDetail.php
 */

namespace bizuno;

if (!empty($data['toolbar'])) { htmlToolbar($output, $data, 'tbInventory'); }
$output['body'] .= "<h1>".$data['title']."</h1>\n";
if (isset($data['form'])) {
	$output['body'] .= html5('frmInventory', $data['form']['frmInventory'])."\n";
	$output['body'] .= html5('dg_assy', ['attr'=> ['type'=>'hidden']])."\n";
}
$output['body'] .= html5('id', $data['fields']['id']);
htmlTabs($output, $data, 'tabInventory');
if (isset($data['form'])) {
    $output['body'] .= "    </form>";
	$output['jsBody'][] = "
icnAction= '';
curIndex = 0;
function preSubmit() {
    if (jq('#dgAssembly').length) {
        jq('#dgAssembly').edatagrid('saveRow');
        var items = jq('#dgAssembly').datagrid('getData');
        var serializedItems = JSON.stringify(items);
        jq('#dg_assy').val(serializedItems);
    }
    if (jq('#dgVendors').length) {
        jq('#dgVendors').edatagrid('saveRow');
        var dgVal = jq('#dgVendors').datagrid('getData');
        var invVendors = JSON.stringify(dgVal['rows'])
        jq('#invVendors').val(invVendors);
    }
    return true;
}
ajaxForm('frmInventory');
jq('.products ul li:nth-child(3n+3)').addClass('last');";
}
