<?php
/*
 * View for contact terms popup
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
 * @filesource /lib/view/module/contacts/divTerms.php
 */

namespace bizuno;

$output['body'] .= "<h1>".$data['title']."</h1>\n";
$output['body'] .= '<table style="border-collapse:collapse;width:100%">'."\n";
$output['body'] .= ' <thead class="panel-header">'."\n";
$output['body'] .= '  <tr><th colspan="2">'.lang('terms').' '."<br /></th></tr>\n";
$output['body'] .= " </thead>\n";
$output['body'] .= ' <tbody>'."\n";
$temp = $data['terms_type'];
if ($temp['attr']['value'] == 0) { $temp['attr']['checked'] = 'checked'; }
$temp['attr']['value'] = '0';
$output['body'] .= "  <tr>\n";
$output['body'] .= "	<td>".html5('terms_type', $temp).' '.lang('contacts_terms_default')."<br /></td>\n"; // 0-Default
$output['body'] .= "	<td>".viewTerms('0', false, $data['terms_type']['attr']['value'])."</td>\n";
$output['body'] .= "  </tr>\n";
$temp = $data['terms_type'];
if ($temp['attr']['value'] == 3) { $temp['attr']['checked'] = 'checked'; }
$temp['attr']['value'] = '3';
$output['body'] .= "  <tr>\n";
$output['body'] .= "	<td>".html5('terms_type', $temp).' '.lang('contacts_terms_custom')."</td>\n"; // 3-Special early
$output['body'] .= "	<td>".sprintf(lang('contacts_terms_discount'), html5('terms_disc', $data['terms_disc']), html5('terms_early', $data['terms_early'])).' '.sprintf(lang('contacts_terms_net'), html5('terms_net',$data['terms_net']))."</td>\n";
$output['body'] .= "  </tr>\n";
$temp = $data['terms_type'];
if ($temp['attr']['value'] == 6) { $temp['attr']['checked'] = 'checked'; }
$temp['attr']['value'] = '6';
$output['body'] .= '	<tr><td colspan="2">'.html5('terms_type', $temp).' '.lang('contacts_terms_now')."</td></tr>\n"; // 6-Due upon receipt
$temp = $data['terms_type'];
if ($temp['attr']['value'] == 2) { $temp['attr']['checked'] = 'checked'; }
$temp['attr']['value'] = '2';
$output['body'] .= '	<tr><td colspan="2">'.html5('terms_type', $temp).' '.lang('contacts_terms_prepaid')."</td></tr>\n"; // 2-Prepaid
$temp = $data['terms_type'];
if ($temp['attr']['value'] == 1) { $temp['attr']['checked'] = 'checked'; }
$temp['attr']['value'] = '1';
$output['body'] .= '	<tr><td colspan="2">'.html5('terms_type', $temp).' '.lang('contacts_terms_cod')."</td></tr>\n"; // 1-COD
$temp = $data['terms_type'];
if ($temp['attr']['value'] == 4) { $temp['attr']['checked'] = 'checked'; }
$temp['attr']['value'] = '4';
$output['body'] .= "  <tr>\n";
$output['body'] .= "	<td>".html5('terms_type', $temp).' '.lang('contacts_terms_dom')."</td>\n"; // 4-Day of month
$output['body'] .= "	<td>".html5('terms_date', $data['terms_date'])."</td>\n";
$output['body'] .= "  </tr>\n";
$temp = $data['terms_type'];
if ($temp['attr']['value'] == 5) { $temp['attr']['checked'] = 'checked'; }
$temp['attr']['value'] = '5';
$output['body'] .= '	<tr><td colspan="2">'.html5('terms_type', $temp).' '.lang('contacts_terms_eom')."</td></tr>\n"; // 5-End of month
$output['body'] .= '  <tr><td colspan="2"><hr /><td></tr>'."\n";
$output['body'] .= "  <tr>\n";
$output['body'] .= "	<td>".lang('contacts_terms_credit_limit')."</td>\n";
$output['body'] .= "	<td>".html5('credit_limit', $data['credit_limit'])."</td>\n";
$output['body'] .= "  </tr>\n";
$output['body'] .= " </tbody>\n";
$output['body'] .= "</table>\n";

$callBack = isset($data['call_back']) && $data['call_back'] ? $data['call_back'] : 'terms';
$output['jsBody'][] = "
function termsSave() {
    var type  = jq('#terms_type:checked').val();
    var enc   = type+':'+jq('#terms_disc').val()+':'+jq('#terms_early').val()+':';
    enc += (type=='4' ? jq('#terms_date').val() : jq('#terms_net').val())+':'+cleanCurrency(jq('#credit_limit').val());
    jq('#$callBack').val(enc);
    jq.ajax({
        url:     '".BIZUNO_AJAX."&p=contacts/main/termsText&enc='+enc,
        success: function(json) { processJson(json); if (json.text) jq('#{$callBack}_text').val(json.text); }
    });
    jq('#winTerms').window('close');
}";
