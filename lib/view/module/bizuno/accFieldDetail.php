<?php
/*
 * View for Field details in PhreeForm designer
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
 * @filesource /lib/view/module/bizuno/accFieldDetail.php
 */

namespace bizuno;

$type = $viewData['fields']['type']['attr']['value'];

$output['body'] .= html5('frmField',$viewData['forms']['frmField']);
$output['body'] .= html5('',        $viewData['fields']['icnSave']);
$output['body'] .= html5('module',  $viewData['fields']['module']);
$output['body'] .= html5('table',   $viewData['fields']['table']);
$output['body'] .= html5('id',      $viewData['fields']['id']);
$output['body'] .= '<table>
 <tbody>
  <tr><td colspan="2">'.$viewData['lang']['xf_lbl_field_tip']       .'</td></tr>
  <tr><td colspan="2">'.html5('field', $viewData['fields']['field']).'</td></tr>
  <tr><td colspan="2">'.html5('label', $viewData['fields']['label']).'</td></tr>
  <tr><td colspan="2">'.html5('tag',   $viewData['fields']['tag'])  .'</td></tr>
  <tr><td colspan="2">'.html5('tab',   $viewData['fields']['tab'])  .'</td></tr>
  <tr><td colspan="2">'.html5('group', $viewData['fields']['group']).'</td></tr>
  <tr><td colspan="2">'.html5('order', $viewData['fields']['order'])."</td></tr>";

if (isset($viewData['options']) && is_array($viewData['options'])){
	$output['body'] .= '  <tr class="panel-header"><th colspan="2">'.lang('options')."</th></tr>";
	$output['body'] .= '  <tr><td colspan="2">'.$viewData['options']['description']."</td></tr>";
	foreach ($viewData['options']['values'] as $key => $settings) {
		$output['body'] .= "  <tr><td>".html5($key, $settings)."</td></tr>";
	}
}

$output['body'] .= '  <tr class="panel-header"><th colspan="2">'.lang('attributes')."</th></tr>";

$output['body'] .= "  <tr><td>";
$viewData['fields']['type']['label'] = $viewData['lang']['xf_lbl_text'];
$viewData['fields']['type']['attr']['checked'] = $type=='text' ? 'checked' : false;
$viewData['fields']['type']['attr']['value'] = 'text'; // radio button type
$output['body'] .= html5('type', $viewData['fields']['type'])."<br />";
$viewData['fields']['type']['label'] = $viewData['lang']['xf_lbl_html'];
$viewData['fields']['type']['attr']['checked'] = $type=='html' ? 'checked' : false;
$viewData['fields']['type']['attr']['value'] = 'html';
$output['body'] .= html5('type', $viewData['fields']['type'])."<br />";
$output['body'] .= "  </td><td>";
$output['body'] .= html5('text_length', $viewData['fields']['text_length'])."<br />";
$output['body'] .= html5('text_default', $viewData['fields']['text_default']);
$output['body'] .= '  </td></tr><tr><td colspan="2"><hr /></td></tr>';

$output['body'] .= "  <tr><td>";
$viewData['fields']['type']['label'] = $viewData['lang']['xf_lbl_link_url'];
$viewData['fields']['type']['attr']['checked'] = $type=='link_url' ? 'checked' : false;
$viewData['fields']['type']['attr']['value'] = 'link_url';
$output['body'] .= html5('type', $viewData['fields']['type'])."<br />";
$viewData['fields']['type']['label'] = $viewData['lang']['xf_lbl_link_image'];
$viewData['fields']['type']['attr']['checked'] = $type=='link_image' ? 'checked' : false;
$viewData['fields']['type']['attr']['value'] = 'link_image';
$output['body'] .= html5('type', $viewData['fields']['type'])."<br />";
$viewData['fields']['type']['label'] = $viewData['lang']['xf_lbl_link_inventory'];
$viewData['fields']['type']['attr']['checked'] = $type=='link_inventory' ? 'checked' : false;
$viewData['fields']['type']['attr']['value'] = 'link_inventory';
$output['body'] .= html5('type', $viewData['fields']['type']);
$output['body'] .= "  </td><td>";
$output['body'] .= html5('link_default', $viewData['fields']['link_default']);
$output['body'] .= '  </td></tr><tr><td colspan="2"><hr /></td></tr>';

$output['body'] .= "  <tr><td>";
$viewData['fields']['type']['label'] = $viewData['lang']['xf_lbl_int'];
$viewData['fields']['type']['attr']['checked'] = $type=='integer' ? 'checked' : false;
$viewData['fields']['type']['attr']['value'] = 'integer';
$output['body'] .= html5('type', $viewData['fields']['type']);
$output['body'] .= "  </td><td>";
$output['body'] .= html5('int_select',  $viewData['fields']['int_select'])."<br />";
$output['body'] .= html5('int_default', $viewData['fields']['int_default']);
$output['body'] .= '  </td></tr><tr><td colspan="2"><hr /></td></tr>';

$output['body'] .= "  <tr><td>";
$viewData['fields']['type']['label'] = $viewData['lang']['xf_lbl_float'];
$viewData['fields']['type']['attr']['checked'] = $type=='float' ? 'checked' : false;
$viewData['fields']['type']['attr']['value'] = 'float';
$output['body'] .= html5('type', $viewData['fields']['type']);
$output['body'] .= "  </td><td>";
$output['body'] .= html5('float_select',  $viewData['fields']['float_select'])."<br />";
//$output['body'] .= html5('float_format',  $viewData['fields']['float_format'])."<br />"; // for decimal type
$output['body'] .= html5('float_default', $viewData['fields']['float_default']);
$output['body'] .= '  </td></tr><tr><td colspan="2"><hr /></td></tr>';

$output['body'] .= "  <tr><td>";
$viewData['fields']['type']['label'] = $viewData['lang']['xf_lbl_checkbox_multi'];
$viewData['fields']['type']['attr']['checked'] = $type=='checkbox_multi' ? 'checked' : false;
$viewData['fields']['type']['attr']['value'] = 'checkbox_multi';
$output['body'] .= html5('type', $viewData['fields']['type'])."<br />";
$viewData['fields']['type']['label'] = $viewData['lang']['xf_lbl_select'];
$viewData['fields']['type']['attr']['checked'] = $type=='select' ? 'checked' : false;
$viewData['fields']['type']['attr']['value'] = 'select';
$output['body'] .= html5('type', $viewData['fields']['type'])."<br />";
$viewData['fields']['type']['label'] = $viewData['lang']['xf_lbl_radio'];
$viewData['fields']['type']['attr']['checked'] = $type=='radio' ? 'checked' : false;
$viewData['fields']['type']['attr']['value'] = 'radio';
$output['body'] .= html5('type', $viewData['fields']['type'])."</td>";
$output['body'] .= "  </td><td>";
$output['body'] .= html5('radio_default', $viewData['fields']['radio_default']);
$output['body'] .= '  </td></tr><tr><td colspan="2"><hr /></td></tr>';

$output['body'] .= "  <tr><td>";
$viewData['fields']['type']['label'] = $viewData['lang']['xf_lbl_checkbox'];
$viewData['fields']['type']['attr']['checked'] = $type=='checkbox' ? 'checked' : false;
$viewData['fields']['type']['attr']['value'] = 'checkbox';
$output['body'] .= html5('type', $viewData['fields']['type']);
$output['body'] .= "  </td><td>";
$output['body'] .= html5('checkbox_default', $viewData['fields']['checkbox_default']);
$output['body'] .= '  </td></tr><tr><td colspan="2"><hr /></td></tr>';

$output['body'] .= "  <tr><td>";
$viewData['fields']['type']['label'] = lang('date');
$viewData['fields']['type']['attr']['checked'] = $type=='date' ? 'checked' : false;
$viewData['fields']['type']['attr']['value'] = 'date';
$output['body'] .= html5('type', $viewData['fields']['type'])."<br />";
$viewData['fields']['type']['label'] = lang('time');
$viewData['fields']['type']['attr']['checked'] = $type=='time' ? 'checked' : false;
$viewData['fields']['type']['attr']['value'] = 'time';
$output['body'] .= html5('type', $viewData['fields']['type'])."<br />";
$viewData['fields']['type']['label'] = $viewData['lang']['xf_lbl_datetime'];
$viewData['fields']['type']['attr']['checked'] = $type=='datetime' ? 'checked' : false;
$viewData['fields']['type']['attr']['value'] = 'datetime';
$output['body'] .= html5('type', $viewData['fields']['type'])."<br />";
$viewData['fields']['type']['label'] = $viewData['lang']['xf_lbl_timestamp'];
$viewData['fields']['type']['attr']['checked'] = $type=='timestamp' ? 'checked' : false;
$viewData['fields']['type']['attr']['value'] = 'timestamp';
$output['body'] .= html5('type', $viewData['fields']['type']);
$output['body'] .= '  </td></tr>';

$output['body'] .= " </tbody>";
$output['body'] .= "</table></form>";
$output['jsBody'][] = "jq('#group').combobox({ 
    data:grpData, valueField:'id', textField:'id', width:100, delay:1000,
    onChange: function (newVal) {
        var datas = jq('#group').combobox('options').data;
        datas.push({ id:newVal });
        jq('#group').combobox('loadData', datas);
        jq('#group').combobox('setValue', newVal);
    }
});
ajaxForm('frmField');";
