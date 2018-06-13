<?php
/*
 * PhreeForm report/form Open popup window
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
 * @version    2.x Last Update: 2018-06-04
 * @filesource /lib/view/module/phreeform/popupRptOpen.php
 */

namespace bizuno;

$output['body'] .= '<div id="winOpen">'."\n";
$output['body'] .= html5('frmPhreeform', $viewData['forms']['frmPhreeform'])."\n";
$output['body'] .= html5('fmt', ['attr'=>  ['type'=>'hidden', 'value'=>'pdf']])."\n";
if (isset($viewData['report']) && $viewData['report']->datelist == 'a') {
	$output['body'] .= html5('critDateSel', ['attr'=>  ['type'=>'hidden', 'value'=>'a']]);
	$output['body'] .= html5('critDateMin', ['attr'=>  ['type'=>'hidden', 'value'=>'']]);
	$output['body'] .= html5('critDateMax', ['attr'=>  ['type'=>'hidden', 'value'=>'']]);
}
$output['body'] .= '<div style="text-align:center">'."\n";
$output['body'] .= html5('delivery', ['label'=>lang('browser'), 'attr'=>['type'=>'radio','value'=>'I', 'checked'=>'checked'],'events'=>['onClick'=>"jq('#rpt_email').hide('slow')"]])."\n";
$output['body'] .= html5('delivery', ['label'=>lang('download'),'attr'=>['type'=>'radio','value'=>'D'],'events'=>['onClick'=>"jq('#rpt_email').hide('slow')"]])."\n";
$output['body'] .= html5('delivery', ['label'=>lang('email'),   'attr'=>['type'=>'radio','value'=>'S'],'events'=>['onClick'=>"jq('#rpt_email').show('slow')"]])."\n";
$output['body'] .= "</div>\n";
$output['body'] .= '<div id="rpt_email" style="display:none">'."\n";
$output['body'] .= '  <table style="border-style:none;width:100%">'."\n";
$output['body'] .= '   <thead><tr><th colspan="3">'.lang('delivery_method')."</th></tr></thead>\n";
$output['body'] .= '   <tbody>'."\n";
$output['body'] .= '    <tr>'."\n";
$output['body'] .= '	  <td>'.html5('fromName',  $viewData['fields']['fromName']) ."</td>\n";
$output['body'] .= '	  <td>'.html5('fromEmail', $viewData['fields']['fromEmail'])."</td>\n";
$output['body'] .= '    </tr>'."\n";
$output['body'] .= '    <tr>'."\n";
$output['body'] .= '	  <td>'.html5('toName',  $viewData['fields']['toName']) ."</td>";
$output['body'] .= '	  <td>'.html5('toEmail', $viewData['fields']['toEmail'])."</td>\n";
$output['body'] .= '    </tr>'."\n";
$output['body'] .= '    <tr>'."\n";
$output['body'] .= '      <td>'.html5('CCName',  $viewData['fields']['CCName']) ."</td>\n";
$output['body'] .= '      <td>'.html5('CCEmail', $viewData['fields']['CCEmail'])."</td>\n";
$output['body'] .= '    </tr>'."\n";
$output['body'] .= '    <tr><td colspan="2">'.html5('msgSubject', $viewData['fields']['msgSubject']).'</td></tr>'."\n";
// convert the body text to \n form <br />
if (isset($viewData['fields']['msgBody']['attr']['value'])) {
	$viewData['fields']['msgBody']['attr']['value'] = str_replace(['<br />','<br>'], "\n", $viewData['fields']['msgBody']['attr']['value']);
}
$output['body'] .= '    <tr><td colspan="2">'.html5('msgBody',    $viewData['fields']['msgBody'])   .'</td></tr>'."\n";
$output['body'] .= '   </tbody>'."\n";
$output['body'] .= '  </table>'."\n";
$output['body'] .= '</div>'."\n";

if (isset($viewData['report'])) {
	$output['body'] .= '<table style="border-collapse:collapse;margin-left:auto;margin-right:auto;">'."\n";
	$output['body'] .= '  <thead class="panel-header">'."\n";
	$output['body'] .= '	<tr><th colspan="4">'.lang('criteria').'</th></tr>'."\n";
	$output['body'] .= "  </thead>\n";
	$output['body'] .= "  <tbody>\n";
	$output['body'] .= '	<tr class="panel-header"><th colspan="2">&nbsp;</th><th>'.lang('from')."</th><th>".lang('to')."</th></tr>\n";
	if ($viewData['report']->datelist != '') { 
		if ($viewData['report']->datelist == 'z') { // special case for period pull-down
			$output['body'] .= '<tr><td colspan="4">'.html5('period', ['label'=>lang('period'), 'values'=>dbPeriodDropDown(), 'attr'=>  ['type'=>'select', 'value'=>getModuleCache('phreebooks', 'fy', 'period')]])."</td></tr>\n";
		} elseif ($viewData['report']->datelist != 'a') {
			$dateArray = explode(':', $viewData['report']->datedefault);
			$output['body'] .= "<tr>\n";
			$output['body'] .= " <td>".lang('date')."</td>\n";
			$output['body'] .= " <td>".html5('critDateSel', ['values'=>$viewData['dateChoices'], 'attr'=>  ['type'=>'select', 'value'=>isset($dateArray[0])?$dateArray[0]:'']])."</td>\n";
			$output['body'] .= " <td>".html5('critDateMin', ['attr'=>  ['value'=>isset($dateArray[1])?$dateArray[1]:'']])."</td>\n";
			$output['body'] .= " <td>".html5('critDateMax', ['attr'=>  ['value'=>isset($dateArray[2])?$dateArray[2]:'']])."</td>\n";
			$output['body'] .= "</tr>\n";
		} 
	}

	if ($viewData['report']->reporttype == 'rpt' && isset($viewData['report']->grouplist) && $viewData['report']->grouplist <> '') {
		$i = 1;
		$group_list    = [['id' => '0', 'text' => lang('none')]];
		$group_default = '';
		if (is_array($viewData['report']->grouplist)) foreach ($viewData['report']->grouplist as $group) {
            if (!empty($group->default))   { $group_default = $i; }
            if (!empty($group->page_break)){ $group_break   = true; }
		    $group_list[] = ['id'=>$i, 'text'=>$group->title];
		    $i++;
		}
		$output['body'] .= "	<tr>\n";
		$output['body'] .= "	  <td>".$viewData['lang']['phreeform_groups']."</td>\n";
		$output['body'] .= '	  <td colspan="3">'.html5('critGrpSel', ['values'=>$group_list, 'attr'=>  ['type'=>'select', 'value'=>$group_default]]).' ';
		$output['body'] .= html5('critGrpChk', ['label'=>$viewData['lang']['phreeform_page_break'], 'attr'=>  ['type'=>'checkbox']])."</td>\n";
		$output['body'] .= "	</tr>\n";
	}
	if (isset($viewData['report']->sortlist) && sizeof($viewData['report']->sortlist) > 0) {
		$i = 1;
		$sort_list   = [['id' => '0', 'text' => lang('none')]];
		$sort_default = '';
		if (is_array($viewData['report']->sortlist)) foreach ($viewData['report']->sortlist as $sortitem) {
            if (!empty($sortitem->default)) { $sort_default = $i; }
		    $sort_list[] = ['id' => $i, 'text' => !empty($sortitem->title) ? $sortitem->title : ''];
		    $i++;
		}
		$output['body'] .= "	<tr>\n";
		$output['body'] .= "	  <td>".$viewData['lang']['phreeform_sorts']."</td>\n";
		$output['body'] .= '	  <td colspan="3">'.html5('critSortSel', ['values'=>$sort_list, 'attr'=>  ['type'=>'select', 'value'=>$sort_default]]);
		$output['body'] .= "	</tr>\n";
		}
	if ($viewData['report']->reporttype == 'rpt') {
		$props = ['attr'=>  ['type'=>'checkbox', 'value'=>'1']];
        if (!empty($viewData['report']->truncate)) { $props['attr']['checked'] = 'checked'; }
		$output['body'] .= "	<tr>\n";
		$output['body'] .= "	  <td>".$viewData['lang']['truncate_fit']."</td>\n";
		$output['body'] .= '	  <td colspan="3">'.html5('critTruncate', $props);
		$output['body'] .= "	</tr>\n";
	}
	if (isset($viewData['report']->filterlist) && $viewData['report']->filterlist <> '') {
		foreach ($viewData['report']->filterlist as $key => $LineItem) { // retrieve the dropdown based on the params field (dropdown type)
			$CritBlocks = explode(':', $viewData['critChoices'][$LineItem->type]);
			$numInputs = array_shift($CritBlocks); // will be 0, 1 or 2
			$choices = [];
            foreach ($CritBlocks as $value) { $choices[] = ['id'=>$value, 'text'=>lang($value)]; }
			if (!empty($LineItem->visible)) {
				$field_0 = html5('critFltrSel'.$key, ['values'=>$choices, 'attr'=>  ['type'=>'select', 'value'=>isset($LineItem->default)?$LineItem->default:'']]);
				$field_1 = html5('fromvalue'  .$key, ['attr'=>  ['value'=>isset($LineItem->min)?$LineItem->min:'', 'size'=>"21", 'maxlength'=>"20"]]);
				$field_2 = html5('tovalue'    .$key, ['attr'=>  ['value'=>isset($LineItem->max)?$LineItem->max:'', 'size'=>"21", 'maxlength'=>"20"]]);
				$output['body'] .= "	<tr".($LineItem->visible ? '' : ' style="display:none"').">\n";
				$output['body'] .= "	  <td>".(empty($LineItem->title) ? '' : $LineItem->title)."</td>\n";
				$output['body'] .= "	  <td>".$field_0."</td>\n";
				$output['body'] .= "	  <td>".($numInputs >= 1 ? $field_1 : '&nbsp;')."</td>\n";
				$output['body'] .= "	  <td>".($numInputs == 2 ? $field_2 : '&nbsp;')."</td>\n";
				$output['body'] .= "	</tr>\n";
			}
		}
	}
	$output['body'] .= '  </tbody>'."\n";
	$output['body'] .= '</table>'."\n";
}

if (sizeof($viewData['fields']['reports']) > 1) {
  $output['body'] .= '	<div id="frm_select">'."\n";
  $output['body'] .= "	<br /><p>".$viewData['lang']['phreeform_form_select']."</p>\n";
  foreach ($viewData['fields']['reports'] as $value) {
  	$output['body'] .= "	<div>".html5('rID', ['events'=>  ['onChange'=>"phreeformBody();"], 'attr'=>  ['type'=>'radio', 'value'=>$value['id']]]).'&nbsp;'.$value['text']."</div>\n";
  }
  $output['body'] .= "	</div>\n";
} elseif (!isset($viewData['fields']['id']['attr']['value'])) {
    $output['body'] .= lang('msg_no_documents');
} else {
    $output['body'] .= html5('rID', $viewData['fields']['id']);
}
$output['body'] .= "</form>\n";
$output['body'] .= "</div>\n";

$output['body'] .= '<script type="text/javascript" src="'.BIZUNO_URL.'../apps/jquery-file-download.js"></script>'."\n";
$output['jsBody'][]  = "
jq('#critDateMin').datebox();
jq('#critDateMax').datebox();
jq('#frmPhreeform').submit(function (e) {
	var delivery = jq('input:radio[name=delivery]:checked').val();
	var format = jq('#fmt').val();
	if (delivery == 'D' || format == 'csv' || format == 'xml') { // download
		jq.fileDownload(jq(this).attr('action'), {
			failCallback: function (response, url) { processJson(JSON.parse(response)); },
			httpMethod: 'POST',
			data: jq(this).serialize()
		});
		e.preventDefault(); //otherwise a normal form submit would occur
	} else if (delivery == 'S' || format == 'html') { // email
		jq('body').addClass('loading');
		jq.ajax({
			type:    'post',
			url:     jq('#frmPhreeform').attr('action'),
			data:    jq('#frmPhreeform').serialize(),
			success: function (data) { processJson(data); }
		});
		return false;
	} else {
		jq('body').addClass('loading');
	}
});";
