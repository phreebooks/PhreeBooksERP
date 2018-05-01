<?php
/*
 * View for PhreeBooks general journal detail page
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
 * @version    2.x Last Update: 2018-04-27
 * @filesource /lib/view/module/phreebooks/divJournalDetail.php
 */

namespace bizuno;

$output['body'] .= "<!-- BOF: divJournalDetail -->\n";
$output['body'] .= html5('id',             $data['journal_main']['id']);
$output['body'] .= html5('journal_id',     $data['journal_main']['journal_id']);
$output['body'] .= html5('terminal_date',  ['attr'=>  ['type'=>'hidden']]);
$output['body'] .= html5('currency',       $data['journal_main']['currency']);
$output['body'] .= html5('currency_rate',  $data['journal_main']['currency_rate']);
$output['body'] .= html5('recur_id',       $data['journal_main']['recur_id']);
$output['body'] .= html5('recur_frequency',$data['recur_frequency']);
$output['body'] .= html5('item_array',     $data['item_array']);
$output['body'] .= html5('followup',       ['attr'=>  ['type'=>'hidden']]);
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
// Billing Address
$output['body'] .= '<div style="float:right;width:33%">'.lang('bill_to')."<br />\n";
$addValues = $data['journal_main'];
$settings['attr'] = ['type'=>'cve','suffix'=>'_b','search'=>true,'update'=>true,'validate'=>true];
require (BIZUNO_LIB."view/module/contacts/divAddressShort.php");
$output['body'] .= "</div>\n";
$output['body'] .= "</div>\n";
// Journal properties
$output['body'] .= '<div style="width:33%">'."\n";
$output['body'] .= html5('invoice_num',$data['journal_main']['invoice_num'])."<br />\n";
$output['body'] .= html5('post_date',  $data['journal_main']['post_date'])."<br />\n";
$output['body'] .= html5('store_id',   $data['journal_main']['store_id'])."\n";
$output['body'] .= "</div>\n";
