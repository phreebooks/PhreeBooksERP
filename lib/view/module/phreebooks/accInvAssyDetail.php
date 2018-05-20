<?php
/*
 * View for Inventory Assemblies
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
 * @version    2.x Last Update: 2018-05-15
 * @filesource /lib/view/module/phreebooks/accInvAssyDetail.php
 */

namespace bizuno;

htmlToolbar($output, $data, 'tbPhreeBooks');
$output['body'] .= "   ".html5('frmJournal', $data['form']['frmJournal'])."\n";
$output['body'] .= "<!-- BOF: accInvAssyDetail -->\n";
$output['body'] .= "<div>\n";
$output['body'] .= '<div style="width:50%;float:right">'."\n";
htmlDatagrid($output, $data, 'item');
$output['body'] .= "</div>\n";
$output['body'] .= html5('id',         $data['journal_main']['id'])."\n";
$output['body'] .= html5('journal_id', $data['journal_main']['journal_id']);
$output['body'] .= html5('gl_account', $data['journal_item']['gl_account']);
$output['body'] .= html5('gl_acct_id', $data['journal_main']['gl_acct_id']);
$output['body'] .= html5('recur_id',   $data['journal_main']['recur_id']);
$output['body'] .= html5('item_array', $data['item_array']);
$output['body'] .= html5('store_id',   $data['journal_main']['store_id'])."\n";
if ($data['journal_main']['store_id']['attr']['type'] <> 'hidden') { $output['body'] .= "<br />\n"; }
$output['body'] .= html5('sku',        $data['journal_item']['sku'])."\n";
$output['body'] .= html5('post_date',  $data['journal_main']['post_date'])."<br />\n";
$output['body'] .= html5('trans_code', $data['journal_item']['trans_code'])."<br />\n";
$output['body'] .= html5('description',$data['journal_item']['description'])."<br />\n";
$output['body'] .= html5('invoice_num',$data['journal_main']['invoice_num'])."<br />\n";
$output['body'] .= html5('qty_stock',  $data['qty_stock'])."<br />\n";
$output['body'] .= html5('qty',        $data['journal_item']['qty'])."<br />\n";
$output['body'] .= html5('balance',    $data['balance'])."<br /><br />\n";
$output['body'] .= "</div>\n";

if (isset($data['javascript']['datagridData'])) { $output['jsBody'][] = $data['javascript']['datagridData']; }
$output['jsBody'][]  = "
function preSubmit() {
	var item = {sku:jq('#sku').combogrid('getValue'),qty:jq('#qty').val(),description:jq('#description').val(),total:0,gl_account:jq('#gl_account').val()};
	var items = {total:1,rows:[item]};
	var serializedItems = JSON.stringify(items);
	jq('#item_array').val(serializedItems);
	if (!formValidate()) return false;
	return true;
}
ajaxForm('frmJournal');
jq('#sku').next().find('input').focus();";
