<?php
/*
 * View for Banking cash receipts and disbursements
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
 * @version    2.x Last Update: 2018-05-14
 * @filesource /lib/view/module/phreebooks/accBankDetail.php
 */

namespace bizuno;

if ((isset($data['bulk']) && $data['bulk']) || (isset($data['journal_main']['contact_id_b']['attr']['value']) && $data['journal_main']['contact_id_b']['attr']['value'] > 0)) {
	htmlToolbar($output, $data, 'tbPhreeBooks');
	$output['body'] .= "   ".html5('frmJournal', $data['form']['frmJournal'])."\n";
	$divWidth = in_array(JOURNAL_ID, [17,18]) ? '25' : '33'; // 3 or 4 sections depending on receipt/payment
	// Hidden fields
	$output['body'] .= html5('id',              $data['journal_main']['id']);
	$output['body'] .= html5('journal_id',      $data['journal_main']['journal_id']);
	$output['body'] .= html5('so_po_ref_id',    $data['journal_main']['so_po_ref_id'])."<br />";
	$output['body'] .= html5('terms',           $data['journal_main']['terms']);
	$output['body'] .= html5('override_user',   $data['override_user']);
	$output['body'] .= html5('override_pass',   $data['override_pass']);
	$output['body'] .= html5('recur_id',        $data['journal_main']['recur_id']);
	$output['body'] .= html5('recur_frequency', $data['recur_frequency']);
	$output['body'] .= html5('item_array',      $data['item_array']);
	$output['body'] .= html5('xChild',          ['attr'=>  ['type'=>'hidden']]);
	$output['body'] .= html5('xAction',         ['attr'=>  ['type'=>'hidden']]);
	// payment choices
	if (in_array(JOURNAL_ID, [17, 18, 19])) {
		$output['body'] .= '<div style="float:right;width:'.$divWidth.'%">'."\n";
		require(BIZUNO_LIB."view/module/payment/accPmtDetail.php");
		$output['body'] .= "</div>\n";
        $output['jsReady'][] = "var selectedMethod=jq('#method_code>option:selected').val(); window['payment_'+selectedMethod]();";
	}
	// Totals
	$output['body'] .= '<div style="float:right;width:'.$divWidth.'%">'."\n";
	foreach ($data['totals_methods'] as $methID) {
		require_once(BIZUNO_LIB."controller/module/phreebooks/totals/$methID/$methID.php");
        $totSet = getModuleCache('phreebooks','totals',$methID,'settings');
        $fqcn   = "\\bizuno\\$methID";
        $totals = new $fqcn($totSet);
        $content= $totals->render($output, $data);
	}
	$output['body'] .= "</div>\n";
	
	// Order properties
	$output['body'] .= '<div style="float:right;width:'.$divWidth.'%">'."\n";
	$output['body'] .= html5('invoice_num',   $data['journal_main']['invoice_num'])."<br />\n";
	$output['body'] .= html5('post_date',     $data['journal_main']['post_date'])."<br />\n";
	$output['body'] .= html5('purch_order_id',$data['journal_main']['purch_order_id'])."<br />\n";
	$output['body'] .= html5('store_id',      $data['journal_main']['store_id'])."\n";
	$output['body'] .= html5('rep_id',        $data['journal_main']['rep_id'])."<br />\n";
	$output['body'] .= html5('terms_text',    $data['terms_text']).' '.html5('terms_edit', $data['terms_edit'])."<br />\n";
	$output['body'] .= html5('currency',      $data['journal_main']['currency']);
	$output['body'] .= html5('closed',        $data['journal_main']['closed'])."\n";
	$output['body'] .= html5('waiting',       $data['journal_main']['waiting'])."\n";
	$output['body'] .= "</div>\n";
	// Billing Address or search box
    $output['body'] .= '<div style="width:'.$divWidth.'%">'.lang('ship_to')."<br />\n";
    $addValues = $data['journal_main'];
    $settings['attr'] = ['suffix'=>'_b','search'=>true,'update'=>true,'validate'=>true];
    require (BIZUNO_LIB."view/module/contacts/divAddressShort.php");
	$output['body'] .= "</div>\n";
	$output['body'] .= '<div style="clear:both">'."\n";
	htmlDatagrid($output, $data, 'item');
	$output['body'] .= '</div>'."\n";
    
	$output['jsBody'][] = "
function preSubmit() {
	var items = new Array();	
	var dgData = jq('#dgJournalItem').datagrid('getData');
	for (var i=0; i<dgData.rows.length; i++) if (dgData.rows[i]['checked']) items.push(dgData.rows[i]);
	var serializedItems = JSON.stringify(items);
	jq('#item_array').val(serializedItems);
	if (!formValidate()) return false;
	return true;
}
ajaxForm('frmJournal');";
} else { // show the pull down to select a customer/vendor
	$output['body'] .= html5('contactSel', ['label'=>lang('search')])."\n";
	$output['jsBody'][] = "
	jq('#contactSel').combogrid({
		width:     120,
		panelWidth:500,
		delay:     500,
		idField:   'contact_id_b',
		textField: 'primary_name_b',
		mode:      'remote',
		url:       '".BIZUNO_AJAX."&p=phreebooks/main/managerRowsBank&jID=".JOURNAL_ID."', 
		onBeforeLoad:function (param) { var newValue = jq('#contactSel').combogrid('getValue'); if (newValue.length < 2) return false; },
		onClickRow:function (idx, row) { journalEdit(".JOURNAL_ID.", 0, row.contact_id_b); },
		columns:[[
			{field:'contact_id_b',  hidden:true},
			{field:'primary_name_b',title:'".jsLang('address_book_primary_name')."', width:200},
			{field:'city_b',        title:'".jsLang('address_book_city')."', width:100},
			{field:'state_b',       title:'".jsLang('address_book_state')."', width: 50},
			{field:'total_amount',  title:'".jsLang('total')."', width:100, align:'right', formatter:function (value) {return formatCurrency(value);} }
		]]
	});
	if (jq('#contactSel').length) jq('#contactSel').next().find('input').focus();";
}
