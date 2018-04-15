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
foreach ($data['structure']['selDate'] as $value) {
	$cbHTML = $data['fields']['DateList'];
	$cbHTML['label']         = $value['text'];
	$cbHTML['attr']['value'] = $value['id'];
	if (strpos($data['fields']['DateList']['attr']['value'], $value['id']) !== false) {
        $cbHTML['attr']['checked'] = 'checked';
    }
    $dateList .= '<td>'.html5('datelist[]', $cbHTML).'</td>';
	$cnt++;
if ($cnt > 2) { $cnt=0; $dateList .= "</tr><tr>\n"; } // set for 3 columns
}
$dateList .= "</tr>\n";
$output['body'] .= '<table style="border-style:none;width:100%">'."\n";
$output['body'] .= '  <thead class="panel-header"><tr><th colspan="3">'.$data['lang']['phreeform_date_info']."</th></tr></thead>\n";
$output['body'] .= '  <tbody>'."\n";
$data['fields']['DatePeriod']['attr']['value'] = 'p';
if ($data['fields']['DateList']['attr']['value'] == 'z') {
	$data['fields']['DatePeriod']['attr']['checked'] = 'checked';
} else {
	unset($data['fields']['DatePeriod']['attr']['checked']);
}
$output['body'] .= '	<tr><td colspan="3">'.html5('DatePeriod', $data['fields']['DatePeriod']).' '.$data['lang']['use_periods']."</td></tr>\n";
$output['body'] .= '	<tr><td colspan="3">'."<hr></td></tr>\n";
$data['fields']['DatePeriod']['attr']['value'] = 'd';
if ($data['fields']['DateList']['attr']['value'] != 'z') {
	$data['fields']['DatePeriod']['attr']['checked'] = 'checked';
} else {
	unset($data['fields']['DatePeriod']['attr']['checked']);
}
$output['body'] .= '	<tr><td colspan="3">'.html5('DatePeriod', $data['fields']['DatePeriod']).' '.$data['lang']['phreeform_date_list']."</td></tr>\n";
$output['body'] .= $dateList."\n";
$output['body'] .= '	<tr><td colspan="2">'.html5('datedefault', $data['fields']['DateDefault'])."</td>\n";
$output['body'] .= "	    <td>".html5('datefield', $data['fields']['DateField'])."</td></tr>\n";
$output['body'] .= "  </tbody>\n";
$output['body'] .= "</table>\n";
if (isset($data['notes'])) $output['body'] .= '<u><b>'.lang('notes').'</b></u>'.$data['notes'];
if ($data['reportType'] == 'rpt') {
	$output['body'] .= "  <p>&nbsp;</p>\n";
	htmlDatagrid($output, $data, 'groups');
}
$output['body'] .= "  <p>&nbsp;</p>\n";
htmlDatagrid($output, $data, 'sort');
$output['body'] .= "  <p>&nbsp;</p>\n";
htmlDatagrid($output, $data, 'filters');
