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

$type = $data['fields']['type']['attr']['value'];

$output['body'] .= html5('frmField',$data['form']['frmField']);
$output['body'] .= html5('',        $data['fields']['icnSave']);
$output['body'] .= html5('module',  $data['fields']['module']);
$output['body'] .= html5('table',   $data['fields']['table']);
$output['body'] .= html5('id',      $data['fields']['id']);
$output['body'] .= '<table>
 <tbody>
  <tr><td colspan="2">'.$data['lang']['xf_lbl_field_tip']       .'</td></tr>
  <tr><td colspan="2">'.html5('field', $data['fields']['field']).'</td></tr>
  <tr><td colspan="2">'.html5('label', $data['fields']['label']).'</td></tr>
  <tr><td colspan="2">'.html5('tag',   $data['fields']['tag'])  .'</td></tr>
  <tr><td colspan="2">'.html5('tab',   $data['fields']['tab'])  .'</td></tr>
  <tr><td colspan="2">'.html5('group', $data['fields']['group']).'</td></tr>
  <tr><td colspan="2">'.html5('order', $data['fields']['order'])."</td></tr>";

if (isset($data['options']) && is_array($data['options'])){
	$output['body'] .= '  <tr class="panel-header"><th colspan="2">'.lang('options')."</th></tr>";
	$output['body'] .= '  <tr><td colspan="2">'.$data['options']['description']."</td></tr>";
	foreach ($data['options']['values'] as $key => $settings) {
		$output['body'] .= "  <tr><td>".html5($key, $settings)."</td></tr>";
	}
}

$output['body'] .= '  <tr class="panel-header"><th colspan="2">'.lang('attributes')."</th></tr>";

$output['body'] .= "  <tr><td>";
$data['fields']['type']['label'] = $data['lang']['xf_lbl_text'];
$data['fields']['type']['attr']['checked'] = $type=='text' ? 'checked' : false;
$data['fields']['type']['attr']['value'] = 'text'; // radio button type
$output['body'] .= html5('type', $data['fields']['type'])."<br />";
$data['fields']['type']['label'] = $data['lang']['xf_lbl_html'];
$data['fields']['type']['attr']['checked'] = $type=='html' ? 'checked' : false;
$data['fields']['type']['attr']['value'] = 'html';
$output['body'] .= html5('type', $data['fields']['type'])."<br />";
$output['body'] .= "  </td><td>";
$output['body'] .= html5('text_length', $data['fields']['text_length'])."<br />";
$output['body'] .= html5('text_default', $data['fields']['text_default']);
$output['body'] .= '  </td></tr><tr><td colspan="2"><hr /></td></tr>';

$output['body'] .= "  <tr><td>";
$data['fields']['type']['label'] = $data['lang']['xf_lbl_link_url'];
$data['fields']['type']['attr']['checked'] = $type=='link_url' ? 'checked' : false;
$data['fields']['type']['attr']['value'] = 'link_url';
$output['body'] .= html5('type', $data['fields']['type'])."<br />";
$data['fields']['type']['label'] = $data['lang']['xf_lbl_link_image'];
$data['fields']['type']['attr']['checked'] = $type=='link_image' ? 'checked' : false;
$data['fields']['type']['attr']['value'] = 'link_image';
$output['body'] .= html5('type', $data['fields']['type'])."<br />";
$data['fields']['type']['label'] = $data['lang']['xf_lbl_link_inventory'];
$data['fields']['type']['attr']['checked'] = $type=='link_inventory' ? 'checked' : false;
$data['fields']['type']['attr']['value'] = 'link_inventory';
$output['body'] .= html5('type', $data['fields']['type']);
$output['body'] .= "  </td><td>";
$output['body'] .= html5('link_default', $data['fields']['link_default']);
$output['body'] .= '  </td></tr><tr><td colspan="2"><hr /></td></tr>';

$output['body'] .= "  <tr><td>";
$data['fields']['type']['label'] = $data['lang']['xf_lbl_int'];
$data['fields']['type']['attr']['checked'] = $type=='integer' ? 'checked' : false;
$data['fields']['type']['attr']['value'] = 'integer';
$output['body'] .= html5('type', $data['fields']['type']);
$output['body'] .= "  </td><td>";
$output['body'] .= html5('int_select',  $data['fields']['int_select'])."<br />";
$output['body'] .= html5('int_default', $data['fields']['int_default']);
$output['body'] .= '  </td></tr><tr><td colspan="2"><hr /></td></tr>';

$output['body'] .= "  <tr><td>";
$data['fields']['type']['label'] = $data['lang']['xf_lbl_float'];
$data['fields']['type']['attr']['checked'] = $type=='float' ? 'checked' : false;
$data['fields']['type']['attr']['value'] = 'float';
$output['body'] .= html5('type', $data['fields']['type']);
$output['body'] .= "  </td><td>";
$output['body'] .= html5('float_select',  $data['fields']['float_select'])."<br />";
//$output['body'] .= html5('float_format',  $data['fields']['float_format'])."<br />"; // for decimal type
$output['body'] .= html5('float_default', $data['fields']['float_default']);
$output['body'] .= '  </td></tr><tr><td colspan="2"><hr /></td></tr>';

$output['body'] .= "  <tr><td>";
$data['fields']['type']['label'] = $data['lang']['xf_lbl_checkbox_multi'];
$data['fields']['type']['attr']['checked'] = $type=='checkbox_multi' ? 'checked' : false;
$data['fields']['type']['attr']['value'] = 'checkbox_multi';
$output['body'] .= html5('type', $data['fields']['type'])."<br />";
$data['fields']['type']['label'] = $data['lang']['xf_lbl_select'];
$data['fields']['type']['attr']['checked'] = $type=='select' ? 'checked' : false;
$data['fields']['type']['attr']['value'] = 'select';
$output['body'] .= html5('type', $data['fields']['type'])."<br />";
$data['fields']['type']['label'] = $data['lang']['xf_lbl_radio'];
$data['fields']['type']['attr']['checked'] = $type=='radio' ? 'checked' : false;
$data['fields']['type']['attr']['value'] = 'radio';
$output['body'] .= html5('type', $data['fields']['type'])."</td>";
$output['body'] .= "  </td><td>";
$output['body'] .= html5('radio_default', $data['fields']['radio_default']);
$output['body'] .= '  </td></tr><tr><td colspan="2"><hr /></td></tr>';

$output['body'] .= "  <tr><td>";
$data['fields']['type']['label'] = $data['lang']['xf_lbl_checkbox'];
$data['fields']['type']['attr']['checked'] = $type=='checkbox' ? 'checked' : false;
$data['fields']['type']['attr']['value'] = 'checkbox';
$output['body'] .= html5('type', $data['fields']['type']);
$output['body'] .= "  </td><td>";
$output['body'] .= html5('checkbox_default', $data['fields']['checkbox_default']);
$output['body'] .= '  </td></tr><tr><td colspan="2"><hr /></td></tr>';

$output['body'] .= "  <tr><td>";
$data['fields']['type']['label'] = lang('date');
$data['fields']['type']['attr']['checked'] = $type=='date' ? 'checked' : false;
$data['fields']['type']['attr']['value'] = 'date';
$output['body'] .= html5('type', $data['fields']['type'])."<br />";
$data['fields']['type']['label'] = lang('time');
$data['fields']['type']['attr']['checked'] = $type=='time' ? 'checked' : false;
$data['fields']['type']['attr']['value'] = 'time';
$output['body'] .= html5('type', $data['fields']['type'])."<br />";
$data['fields']['type']['label'] = $data['lang']['xf_lbl_datetime'];
$data['fields']['type']['attr']['checked'] = $type=='datetime' ? 'checked' : false;
$data['fields']['type']['attr']['value'] = 'datetime';
$output['body'] .= html5('type', $data['fields']['type'])."<br />";
$data['fields']['type']['label'] = $data['lang']['xf_lbl_timestamp'];
$data['fields']['type']['attr']['checked'] = $type=='timestamp' ? 'checked' : false;
$data['fields']['type']['attr']['value'] = 'timestamp';
$output['body'] .= html5('type', $data['fields']['type']);
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
