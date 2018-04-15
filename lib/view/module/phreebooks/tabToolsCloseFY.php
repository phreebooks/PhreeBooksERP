<?php
/*
 * View for closing Fiscal Year page
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

 * @filesource /lib/view/module/bizuno/tabToolsCloseFY.php
 * 
 */

namespace bizuno;

// define the tab for PhreeBooks
$html = '
<h2><u>What will happen when a Fiscal Year is Closed</u></h2>
<p>The following is a summary of the tasks performed while closing a fiscal year. The fiscal year being closed is indicated above.</p>
<h3>Pre-flight Check</h3>
<p>All journal entries will be tested to make sure they are in a completed state. You have an option to skip this test and remove them unconditionally by checking the box below. 
If any journal entries are not in a closed state, this process will terminate. There may be other modules that will terminate the close process, the conditions for other modules 
is described in the tab of the module.</p>
<b>Close Process</b><br />
<p>The close process will remove all general journal records for the closing fiscal year. Fiscal calendar periods that are vacated during this process will be removed and 
the fiscal calendar will be re-sequenced starting with period 1 being the first period of the first remaining fiscal year.</p>
The following is a summary of the PhreeBooks module closing task list:
<ul>
<li>Delete all journal entries for the closing fiscal year, tables journal_main and journal_item and associated attachments</li>
<li>Delete table journal_history records for the closing fiscal year</li>
<li>Clean up COGS owed table for closing fiscal year</li>
<li>Clean up journal_cogs_usage table for closing fiscal year</li>
<li>Delete journal_periods for fiscal year</li>
<li>Re-sequence journal periods in journal_history table</li>
<li>Delete all gl chart of accounts if inactive no journal entries exits against the account</li>
<li>Delete tax_rates that have end date within the closing fiscal year</li>
<li>Delete bank reconciliation records within the range of closed fiscal year, re-sequence periods periods</li>
</ul>
<h3>Post-close Clean-up</h3><br />
<p>Following the journal deletion and other PhreeBooks module close tasks discussed above, each module will clean orphaned table records. See the instructions 
within each module tab for details on what is performed.</p>
<p>The PhreeBooks post close process will be to re-run the journal tools to validate the journal balance and history table are in sync. 
Other tools are also run to removed orphaned transactions, attachments and other general maintenance activities. 
Most of these are available in the Journal Tools tab in the PhreeBooks module settings.</p>';
$html .= "<p>"."To prevent the pre-flight test from halting the close process, check the box below."."</p>";
$html .= html5('phreebooks_skip', ['label' => 'Do not perform the pre-flight check, I understand that this may affect my financial statements and inventory balances', 'position'=>'after','attr'=>['type'=>'checkbox','value'=>'1']]);

// add the PhreeBooks tab  
$title = getModuleCache('phreebooks', 'properties', 'title');
$layout['tabs']['tabFyClose']['divs']['phreebooks'] = ['order'=>50,'label'=>$title,'type'=>'html','html'=>$html];
$data['tabs']['tabFyClose']['divs'][$title] = ['order'=>0,'label'=>$title,'type'=>'html','html'=>$html];
// build the page
htmlToolbar($output, $data, 'tbFyClose');
$output['body'] .= '<div id="divCloseFY"><p>'.$data['lang']['fy_del_instr']."</p>\n";
htmlTabs($output, $data, 'tabFyClose');
$output['body'] .= '</div>';
