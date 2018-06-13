<?php
/*
 * View for PhreeForm -> Design -> Filters tab
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

 * @filesource /lib/view/module/phreeform/tabFilters.php
 * 
 */

namespace bizuno;

// build the date checkboxes
$dateList = '<tr>';
$cnt = 0;
foreach ($viewData['structure']['selDate'] as $value) {
	$cbHTML = $viewData['fields']['DateList'];
	$cbHTML['label']         = $value['text'];
	$cbHTML['attr']['value'] = $value['id'];
	if (strpos($viewData['fields']['DateList']['attr']['value'], $value['id']) !== false) {
        $cbHTML['attr']['checked'] = 'checked';
    }
    $dateList .= '<td>'.html5('datelist[]', $cbHTML).'</td>';
	$cnt++;
if ($cnt > 2) { $cnt=0; $dateList .= "</tr><tr>\n"; } // set for 3 columns
}
$dateList .= "</tr>\n";
$output['body'] .= '<table style="border-style:none;width:100%">'."\n";
$output['body'] .= '  <thead class="panel-header"><tr><th colspan="3">'.$viewData['lang']['phreeform_date_info']."</th></tr></thead>\n";
$output['body'] .= '  <tbody>'."\n";
$viewData['fields']['DatePeriod']['attr']['value'] = 'p';
if ($viewData['fields']['DateList']['attr']['value'] == 'z') {
	$viewData['fields']['DatePeriod']['attr']['checked'] = 'checked';
} else {
	unset($viewData['fields']['DatePeriod']['attr']['checked']);
}
$output['body'] .= '	<tr><td colspan="3">'.html5('DatePeriod', $viewData['fields']['DatePeriod']).' '.$viewData['lang']['use_periods']."</td></tr>\n";
$output['body'] .= '	<tr><td colspan="3">'."<hr></td></tr>\n";
$viewData['fields']['DatePeriod']['attr']['value'] = 'd';
if ($viewData['fields']['DateList']['attr']['value'] != 'z') {
	$viewData['fields']['DatePeriod']['attr']['checked'] = 'checked';
} else {
	unset($viewData['fields']['DatePeriod']['attr']['checked']);
}
$output['body'] .= '	<tr><td colspan="3">'.html5('DatePeriod', $viewData['fields']['DatePeriod']).' '.$viewData['lang']['phreeform_date_list']."</td></tr>\n";
$output['body'] .= $dateList."\n";
$output['body'] .= '	<tr><td colspan="2">'.html5('datedefault', $viewData['fields']['DateDefault'])."</td>\n";
$output['body'] .= "	    <td>".html5('datefield', $viewData['fields']['DateField'])."</td></tr>\n";
$output['body'] .= "  </tbody>\n";
$output['body'] .= "</table>\n";
if (isset($viewData['notes'])) $output['body'] .= '<u><b>'.lang('notes').'</b></u>'.$viewData['notes'];
if ($viewData['reportType'] == 'rpt') {
	$output['body'] .= "  <p>&nbsp;</p>\n";
	htmlDatagrid($output, $viewData, 'groups');
}
$output['body'] .= "  <p>&nbsp;</p>\n";
htmlDatagrid($output, $viewData, 'sort');
$output['body'] .= "  <p>&nbsp;</p>\n";
htmlDatagrid($output, $viewData, 'filters');
