<?php
/*
 * View for Banking - Pay bills in bulk
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
 * @version    2.x Last Update: 2017-05-14
 * @filesource /lib/view/module/phreebooks/accBankBulk.php
 */

namespace bizuno;

if (!isset($dispFirst)) { $dispFirst = 'cod'; }
htmlToolbar($output, $data, 'tbPhreeBooks');
$output['body'] .= html5('frmJournal', $data['form']['frmJournal'])."\n";
// Hidden fields
$output['body'] .= html5('id',         $data['journal_main']['id']);
$output['body'] .= html5('journal_id', $data['journal_main']['journal_id']);
$output['body'] .= html5('item_array', $data['item_array']);
$output['body'] .= html5('xChild',     ['attr'=>  ['type'=>'hidden']]);
$output['body'] .= html5('xAction',    ['attr'=>  ['type'=>'hidden']]);
// Totals
$output['body'] .= '<div style="float:right;width:33%">'."\n";
foreach ($data['totals_methods'] as $methID) {
	require_once(BIZUNO_LIB."controller/module/phreebooks/totals/$methID/$methID.php");
    $totSet = getModuleCache('phreebooks','totals',$methID,'settings');
    $fqcn = "\\bizuno\\$methID";
    $totals = new $fqcn($totSet);
    $content = $totals->render($output, $data);
}
$output['body'] .= "</div>\n";
// Properties
$output['body'] .= '<div style="float:right;width:34%">'."\n";
// Needed to add a suffix as easyui datagrid filter passes the filter fields as post variables and these get overwritten
$output['body'] .= html5('invoice_num',   $data['journal_main']['invoice_num'])."<br />\n";
$output['body'] .= html5('post_date',     $data['journal_main']['post_date'])."<br />\n";
$output['body'] .= html5('purch_order_id',$data['journal_main']['purch_order_id'])."<br />\n";
$output['body'] .= html5('rep_id',        $data['journal_main']['rep_id'])."<br />\n";
$output['body'] .= "</div>\n";

$output['body'] .= '<div style="clear:both">'."\n";
htmlDatagrid($output, $data, 'item');
$output['body'] .= "</div>\n</form>\n";
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
ajaxForm('frmJournal');
jq('#dgJournalItem').datagrid('enableFilter', [{ field:'date_1', type:'datebox', options:{precision:1}, op:['lessorequal'] }] );\n";
