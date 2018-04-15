<?php
/*
 * View template for PhreeBooks order screens
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
 * @version    2.x Last Update: 2017-10-28
 * @filesource /lib/view/module/phreebooks/divOrdersDetail.php
 * 
 */

namespace bizuno;

$shipStatus = true; //getModuleCache('extShipping', 'properties', 'status') ? true : false;
$jID = $data['journal_main']['journal_id']['attr']['value'];
$type= in_array($jID, [3,4,6,7]) ? 'v' : 'c';
$default_gl     = $type=='v' ? getModuleCache('phreebooks', 'settings', 'vendors', 'gl_purchases') : getModuleCache('phreebooks', 'settings', 'customers', 'gl_sales');
$default_tax_id = 0;

$divWidth = $shipStatus ? '25' : '33'; // 3 or 4 sections depending on shipping enabled
// Hidden fields
$output['body'] .= "<!-- BOF: divOrdersDetail -->\n";
$output['body'] .= html5('id',              $data['journal_main']['id']);
$output['body'] .= html5('journal_id',      $data['journal_main']['journal_id']);
$output['body'] .= html5('so_po_ref_id',    $data['journal_main']['so_po_ref_id']);
$output['body'] .= html5('terms',           $data['journal_main']['terms']);
$output['body'] .= html5('override_user',   $data['override_user']);
$output['body'] .= html5('override_pass',   $data['override_pass']);
$output['body'] .= html5('recur_id',        $data['journal_main']['recur_id']);
$output['body'] .= html5('recur_frequency', $data['recur_frequency']);
$output['body'] .= html5('item_array',      $data['item_array']);
$output['body'] .= html5('xChild',          ['attr'=>  ['type'=>'hidden']]);
$output['body'] .= html5('xAction',         ['attr'=>  ['type'=>'hidden']]);
$output['body'] .= '<div id="shippingEst"></div>'."\n";
$output['body'] .= '<div id="shippingVal"></div>'."\n";
// Totals
$output['body'] .= '<div style="float:right;width:'.$divWidth.'%">'."\n";
foreach ($data['totals_methods'] as $methID) {
	require_once(BIZUNO_LIB."controller/module/phreebooks/totals/$methID/$methID.php");
    $totSet = getModuleCache('phreebooks','totals',$methID,'settings');
    $fqcn = "\\bizuno\\$methID";
	$totals = new $fqcn($totSet);
    $content = $totals->render($output, $data);
}
$output['body'] .= "</div>\n";

// Order properties
$output['body'] .= '<div style="float:right;width:'.$divWidth.'%">'."\n";
$output['body'] .= html5('purch_order_id',$data['journal_main']['purch_order_id'])."<br />";
$output['body'] .= html5('terms_text',    $data['terms_text']).' '.html5('terms_edit', $data['terms_edit'])."<br />";
$output['body'] .= html5('invoice_num',   $data['journal_main']['invoice_num'])."<br />";
$output['body'] .= html5('waiting',       $data['journal_main']['waiting']);
$output['body'] .= html5('post_date',     $data['journal_main']['post_date'])."<br />";
$output['body'] .= html5('terminal_date', $data['journal_main']['terminal_date'])."<br />";
$output['body'] .= html5('store_id',      $data['journal_main']['store_id']);
if ($jID==12) { $output['body'] .= html5('sales_order_num',$data['journal_main']['sales_order_num'])."<br />"; }
$output['body'] .= html5('rep_id',        $data['journal_main']['rep_id'])."<br />";
$output['body'] .= html5('currency',      $data['journal_main']['currency'])."";
$output['body'] .= html5('currency_rate', $data['journal_main']['currency_rate'])."";
$output['body'] .= html5('closed',        $data['journal_main']['closed']);
$output['body'] .= $data['journal_msg'];
$output['body'] .= "</div>\n";

// Shipping address
if ($shipStatus) {
    $output['body'] .= '<div style="float:right;width:'.$divWidth.'%">'.lang('ship_to')."<br />\n";
	$addValues = $data['journal_main'];
    if (isset($data['journal_main']['drop_ship']['attr']['checked'])) { $data['fields']['address_book']['drop_ship_s']['attr']['checked'] = 'checked'; }
    $settings['attr'] = ['suffix'=>'_s','update'=>true,'validate'=>true,'drop'=>true];
    if (in_array($jID, [3,4,6,7])) { $settings['attr']['update'] = false; }
    require (BIZUNO_LIB."view/module/contacts/divAddressShort.php");
    $output['body'] .= "</div>\n";
}

// Billing Address
$output['body'] .= '<div style="width:'.$divWidth.'%">'.lang('bill_to')."<br />\n";
$addValues = $data['journal_main'];
$settings['attr'] = ['suffix'=>'_b','search'=>true,'update'=>true,'validate'=>true,'fill'=>'both','required'=>true,'store'=>false];
if ($shipStatus) { $settings['attr']['copy'] = true; }
require (BIZUNO_LIB."view/module/contacts/divAddressShort.php");
$output['body'] .= "</div>\n";

if (!isset($data['journal_main']['contact_id_b']['attr']['value']) || !$data['journal_main']['contact_id_b']['attr']['value']) { // new order
	$output['jsBody'][] = "
  jq('#AddUpdate_b').prop('checked', true);
  var def_contact_gl_acct = '$default_gl';
  var def_contact_tax_id  = ".($default_tax_id ? $default_tax_id : 0).";";
} else {
	$cID  = $data['journal_main']['contact_id_b']['attr']['value'];
	$defs = dbGetValue(BIZUNO_DB_PREFIX.'contacts', ['gl_account','tax_rate_id'], "id='$cID'");
	$output['jsBody'][] = "
  var def_contact_gl_acct = '{$defs['gl_account']}';
  var def_contact_tax_id  = ".($defs['tax_rate_id'] < 0 ? 0 : $defs['tax_rate_id']).";";
}
if ($data['journal_main']['currency']['attr']['type'] <> 'hidden') {
	$output['jsBody'][] = "jq('#currency').combobox({editable:false, onChange:function(newVal, oldVal){ ordersCurrency(newVal, oldVal); } });";
}
