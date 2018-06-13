<?php
/*
 * View for PhreeBooks Settings -> Journal Tools
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
 * @version    2.x Last Update: 2017-10-09
 * @filesource /lib/view/module/phreebooks/tabToolsFY.php
 */

namespace bizuno;

$output['body'] .= "
<fieldset><legend>".$viewData['lang']['phreebooks_fiscal_years']."</legend>
	<p>".html5('btnNewFy', $viewData['fields']['btnNewFy'])."</p>\n<p>".html5('btnCloseFy', $viewData['fields']['btnCloseFy'])."</p>
</fieldset>
<fieldset><legend>".$viewData['lang']['phreebooks_journal_periods']."</legend>
	<p>".$viewData['lang']['msg_gl_fiscal_year_edit'].'</p>
	<div id="fyCal" style="text-align:center">'.html5('fy', $viewData['fields']['fy']).html5('btnSaveFy', $viewData['fields']['btnSaveFy']).'
	<table style="border-style:none;margin-left:auto;margin-right:auto;">
		<thead class="panel-header">
			<tr><th width="33%">'.lang('period').'</th><th width="33%">'.lang('start').'</th><th width="33%">'.lang('end')."</th></tr>
		</thead>
		<tbody>\n";
foreach ($viewData['values']['periods'] as $period => $value) {
	$output['body'] .= '    <tr><td style="text-align:center">'.$period."</td>";
	if ($period > $viewData['values']['max_posted']) { // only allow changes if nothing has been posted above this period
		$output['body'] .= '<td>'.html5("pStart[$period]",['attr'=>['type'=>'date','value'=>$value['start']]])."</td>"; // new Date(2012, 6, 1)
		$output['jsBody'][] = "jq('#pStart_$period').datebox({required:true, onSelect:function(date){ var nDate = new Date(date.getFullYear(), date.getMonth(), date.getDate()-1); jq('#pEnd_".($period-1)."').datebox('setValue', nDate); } });\n";
		$output['body'] .= '<td>'.html5("pEnd[$period]",  ['attr'=>['type'=>'date','value'=>$value['end']]])."</td>\n";
		$output['jsBody'][] = "jq('#pEnd_$period').datebox({  required:true, onSelect:function(date){ var nDate = new Date(date.getFullYear(), date.getMonth(), date.getDate()+1); jq('#pStart_".($period+1)."').datebox('setValue', nDate); } });\n";
	} else {
		$output['body'] .= '<td style="text-align:center">'.viewDate($value['start'])."</td>";
		$output['body'] .= '<td style="text-align:center">'.viewDate($value['end'])."</td>\n";
	}
	$output['body'] .= "    </tr>\n";
}		  
$output['body'] .= "        </tbody>\n  </table>\n  </div>\n</fieldset>";
