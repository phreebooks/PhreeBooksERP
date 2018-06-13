<?php
/*
 * View for Journanl Tools tab in PhreeBooks Settings
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
 * @version    2.x Last Update: 2017-06-01
 * @filesource /lib/view/module/phreebooks/tabJournalTools.php
 */

namespace bizuno;

$output['body'] .= '<div id="glRepost"><fieldset><legend>'.$viewData['lang']['phreebooks_repost_title']."</legend>\n";
$output['body'] .= " <p>".$viewData['lang']['msg_gl_repost_journals_confirm']."</p>\n";
$output['body'] .= ' <table style="border-style:none;margin-left:auto;margin-right:auto;">'."\n";
$output['body'] .= "  <tbody>\n";
$output['body'] .= '   <tr class="panel-header">'."\n";
$output['body'] .= "    <th>".lang('gl_acct_type_2')."</th>\n";
$output['body'] .= "    <th>".lang('gl_acct_type_20')."</th>\n";
$output['body'] .= "    <th>".lang('gl_acct_type_0')."</th>\n";
$output['body'] .= "    <th>".lang('gl_acct_type_4')."</th>\n";
$output['body'] .= "    <th>&nbsp;</th>\n";
$output['body'] .= "   </tr>\n";
$output['body'] .= "   <tr>\n";
$viewData['fields']['repost']['label'] = lang('journal_main_journal_id_9');
$output['body'] .= "    <td>".html5('jID[9]',  $viewData['fields']['repost'])."</td>\n";
$viewData['fields']['repost']['label'] = lang('journal_main_journal_id_3');
$output['body'] .= "    <td>".html5('jID[3]',  $viewData['fields']['repost'])."</td>\n";
$viewData['fields']['repost']['label'] = lang('journal_main_journal_id_2');
$output['body'] .= "    <td>".html5('jID[2]',  $viewData['fields']['repost'])."</td>\n";
$viewData['fields']['repost']['label'] = lang('journal_main_journal_id_14');
$output['body'] .= "    <td>".html5('jID[14]', $viewData['fields']['repost'])."</td>\n";
$output['body'] .= "    <td>&nbsp;</td>\n";
$output['body'] .= "   </tr>\n";
$output['body'] .= "   <tr>\n";
$viewData['fields']['repost']['label'] = lang('journal_main_journal_id_10');
$output['body'] .= "    <td>".html5('jID[10]', $viewData['fields']['repost'])."</td>\n";
$viewData['fields']['repost']['label'] = lang('journal_main_journal_id_4');
$output['body'] .= "    <td>".html5('jID[4]',  $viewData['fields']['repost'])."</td>\n";
$viewData['fields']['repost']['label'] = lang('journal_main_journal_id_18');
$output['body'] .= "    <td>".html5('jID[18]',  $viewData['fields']['repost'])."</td>\n";
$viewData['fields']['repost']['label'] = lang('journal_main_journal_id_16');
$output['body'] .= "    <td>".html5('jID[16]', $viewData['fields']['repost'])."</td>\n";
$output['body'] .= "    <td>&nbsp;</td>\n";
$output['body'] .= "   </tr>\n";
$output['body'] .= "   <tr>\n";
$viewData['fields']['repost']['label'] = lang('journal_main_journal_id_12');
$output['body'] .= "    <td>".html5('jID[12]', $viewData['fields']['repost'])."</td>\n";
$viewData['fields']['repost']['label'] = lang('journal_main_journal_id_6');
$output['body'] .= "    <td>".html5('jID[6]',  $viewData['fields']['repost'])."</td>\n";
$viewData['fields']['repost']['label'] = lang('journal_main_journal_id_20');
$output['body'] .= "    <td>".html5('jID[20]', $viewData['fields']['repost'])."</td>\n";
$output['body'] .= '    <td style="text-align:right">'.lang('start')."</td>\n";
$output['body'] .= "    <td>".html5('repost_begin',  $viewData['fields']['repost_begin'])."</td>\n";
$output['jsBody'][]  = "jq('#repost_begin').datebox({ required:true });";
$output['body'] .= "   </tr>\n";
$output['body'] .= "   <tr>\n";
$viewData['fields']['repost']['label'] = lang('journal_main_journal_id_13');
$output['body'] .= "    <td>".html5('jID[13]', $viewData['fields']['repost'])."</td>\n";
$viewData['fields']['repost']['label'] = lang('journal_main_journal_id_7');
$output['body'] .= "    <td>".html5('jID[7]',  $viewData['fields']['repost'])."</td>\n";
$viewData['fields']['repost']['label'] = lang('journal_main_journal_id_17');
$output['body'] .= "    <td>".html5('jID[17]', $viewData['fields']['repost'])."</td>\n";
$output['body'] .= '    <td style="text-align:right">'.lang('end')."</td>\n";
$output['body'] .= "    <td>".html5('repost_end', $viewData['fields']['repost_end'])."</td>\n";
$output['jsBody'][]  = "jq('#repost_end').datebox({ required:true });";
$output['body'] .= "   </tr>\n";
$output['body'] .= "   <tr>\n";
$viewData['fields']['repost']['label'] = lang('journal_main_journal_id_19');
$output['body'] .= "    <td>".html5('jID[19]', $viewData['fields']['repost'])."</td>\n";
$viewData['fields']['repost']['label'] = lang('journal_main_journal_id_21');
$output['body'] .= "    <td>".html5('jID[21]', $viewData['fields']['repost'])."</td>\n";
$viewData['fields']['repost']['label'] = lang('journal_main_journal_id_22');
$output['body'] .= "    <td>".html5('jID[22]', $viewData['fields']['repost'])."</td>\n";
$output['body'] .= "    <td>&nbsp;</td>\n";
$output['body'] .= '    <td rowspan="2" style="text-align:right">'.html5('btn_repost', $viewData['fields']['btn_repost'])."</td>\n";
$output['body'] .= "   </tr>\n";
$output['body'] .= "  </tbody>\n";
$output['body'] .= " </table>\n";
$output['body'] .= "</fieldset></div>";
// GL Test and Repair
$output['body'] .= "
<fieldset><legend>".$viewData['lang']['title_gl_test']."</legend>
    <p>".$viewData['lang']['pbtools_gl_test_desc'].'</p>
    <p>'.html5('btnRepairGL', $viewData['fields']['btnRepairGL'])."</p>
</fieldset>
<fieldset><legend>".$viewData['lang']['pb_prune_cogs_title']."</legend>
    <p>".$viewData['lang']['pb_prune_cogs_desc'].'</p>
    <p>'.html5('btnPruneCogs', $viewData['fields']['btnPruneCogs'])."</p>
</fieldset>

<fieldset><legend>".$viewData['lang']['pb_attach_clean_title']."</legend>
    <p>".$viewData['lang']['pb_attach_clean_desc'].'</p>
    <table class="ui-widget" style="border-style:none;margin-left:auto;margin-right:auto;">
        <tbody>
            <tr>
                <td>'.html5('dateAtchCln',$viewData['fields']['dateAtchCln'])."</td>
                <td>".html5('btnAtchCln', $viewData['fields']['btnAtchCln']) ."</td>
            </tr>
        </tbody>
    </table>
</fieldset>\n";
if ($viewData['security'] == 4) { // GL Purge
    $output['body'] .= "
<fieldset><legend>".$viewData['lang']['msg_gl_db_purge'].'</legend>
	<table class="ui-widget" style="border-style:none;margin-left:auto;margin-right:auto;">'."
		<tbody>
			<tr>
				<td>".$viewData['lang']['msg_gl_db_purge_confirm']."</td>
				<td>".html5('purge_db', $viewData['fields']['purge_db']).' '.html5('btn_purge', $viewData['fields']['btn_purge'])."</td>
			</tr>
		</tbody>
	</table>
</fieldset>";
}
