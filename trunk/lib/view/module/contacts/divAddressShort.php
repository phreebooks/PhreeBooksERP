<?php
/*
 * View template for address blocks, short form
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

 * @filesource /lib/view/module/contacts/divAddressShort.php
 */

namespace bizuno;

if (!defined('CONTACT_TYPE')) { define('CONTACT_TYPE', 'c'); }

$defaults = [
    'suffix'  => '',
    'search'  => true,
    'props'   => true,
    'clear'   => true,
    'copy'    => false,
    'update'  => false,
    'validate'=> false,
    'required'=> false,
    'store'   => true,
    'drop'    => false,
    'fill'    => 'none',
];
$attr      = isset($settings['attr']) ? array_replace($defaults, $settings['attr']) : $defaults;
$structure = $data['fields']['address_book'];
unset($structure['country']['label']);
$structure['email']['attr']['size'] = 32; // keep this from overlapping with other divs
// merge values if present
if (isset($addValues)) { foreach ($structure as $field => $value) {
	if (isset($addValues[$field.$attr['suffix']]['attr']['value']))	{
		$structure[$field]['attr']['value'] = $addValues[$field.$attr['suffix']]['attr']['value'];
	}
} }
if ($attr['required']) { foreach ($structure as $field => $props) {
    if (getModuleCache('contacts', 'settings', 'address_book', $field)) {
        $structure[$field]['attr']['required'] = 1;
    }
} }
if (!$attr['search']) {
    $structure['contactSel'] = ['attr'=>['type'=>'hidden']];
} else {
    $structure['contactSel'] = ['label'=>lang('search'),'classes'=>['easyui-combogrid'],'attr'=>  ['data-options'=>"
        width:130, panelWidth:750, delay:900, idField:'id', textField:'primary_name', mode: 'remote',
        url:         '".BIZUNO_AJAX."&p=contacts/main/managerRows&clr=1&type=".($attr['drop']?'c':CONTACT_TYPE)."&store=".($attr['store']?'1':'0')."',
        onBeforeLoad:function (param) { var newValue = jq('#contactSel{$attr['suffix']}').combogrid('getValue'); if (newValue.length < 3) return false; },
        selectOnNavigation:false,
        onClickRow:  function (idx, row){ contactsDetail(row.id, '{$attr['suffix']}', '{$attr['fill']}'); },
        columns: [[{field:'id', hidden:true},
            {field:'short_name',  title:'".jsLang('contacts_short_name')."', width:100},
            {field:'primary_name',title:'".jsLang('address_book_primary_name')."', width:200},
            {field:'address1',    title:'".jsLang('address_book_address1')."', width:100},
            {field:'city',        title:'".jsLang('address_book_city')."', width:100},
            {field:'state',       title:'".jsLang('address_book_state')."', width: 50},
            {field:'postal_code', title:'".jsLang('address_book_postal_code')."', width:100},
            {field:'telephone1',  title:'".jsLang('address_book_telephone1')."', width:100}]]"]];
}
// build pull down selection
$output['body'] .= '<div id="address'.$attr['suffix'].'">'."\n";
if (isset($attr['label'])) { $output['body'] .= "<label>".$attr['label']."</label>"; }
if ($attr['clear']) { $output['body'] .= ' '.html5('', ['icon'=>'clear','size'=>'small','events'=>['onClick'=>"addressClear('{$attr['suffix']}')"]])."\n"; }
if ($attr['validate'] && getModuleCache('extShipping', 'properties', 'status')) {
    $output['body'] .= ' '.html5('', ['icon'=>'truck','size'=>'small','label'=>lang('validate_address'),'events'=>  ['onClick'=>"shippingValidate('{$attr['suffix']}');"]])."\n";
}
if ($attr['copy']) { $output['body'] .= ' '.html5('',['icon'=>'copy','size'=>'small','events'=>['onClick'=>"addressCopy('_b', '_s')"]])."\n"; }
$output['body'] .= "<br />\n";

$output['body'] .= '<div id="contactDiv'.$attr['suffix'].'"'.($attr['drop']?' style="display:none"':'').'>'."\n";
$output['body'] .= html5('contactSel'.$attr['suffix'], $structure['contactSel']);
$output['body'] .= '<span id="spanContactProps'.$attr['suffix'].'" style="display:none">&nbsp;';
if ($attr['props']) { $output['body'] .= html5('contactProps'.$attr['suffix'], ['icon'=>'settings', 'size'=>'small',
    'events' => ['onClick'=>"windowEdit('contacts/main/properties&rID='+jq('#contact_id{$attr['suffix']}').val(), 'winContactProps', '".jsLang('details')."', 1000, 600);"]]);
}
$output['body'] .= '</span></div>';

// Address select (hidden by default)
$output['body'] .= '  <div id="addressDiv'.$attr['suffix'].'" style="display:none">'.html5('addressSel'.$attr['suffix'], array('label'=>''))."</div>\n";
// Options Bar
if ($attr['update']) { $output['body'] .= html5('AddUpdate'.$attr['suffix'], ['label'=>lang('add_update'),'attr'=>  ['type'=>'checkbox']])."\n"; }
if ($attr['drop']) {
	$drop_attr = ['type'=>'checkbox'];
    if (isset($addressValues['drop_ship'.$attr['suffix']]['attr']['checked'])) { $drop_attr['checked'] = 'checked'; }
	$output['body'] .= html5('drop_ship'.$attr['suffix'], ['label'=>lang('drop_ship'), 'attr'=>$drop_attr,
        'events' => ['onClick'=>"jq('#contactDiv{$attr['suffix']}').toggle();"]])."\n";
}
$output['body'] .= "<br />\n";

// Address fields
$output['body'] .= '  <div class="inner-labels">'."\n";
if (isset($structure['contact_id'])) { $output['body'] .= html5('contact_id'.$attr['suffix'], $structure['contact_id'])."\n"; }
$output['body'] .= html5('address_id'.$attr['suffix'],  $structure['address_id'])."\n";
$output['body'] .= html5('primary_name'.$attr['suffix'],$structure['primary_name'])."<br />\n";
$output['body'] .= html5('contact'.$attr['suffix'],     $structure['contact'])."<br />\n";
$output['body'] .= html5('address1'.$attr['suffix'],    $structure['address1'])."<br />\n";
$output['body'] .= html5('address2'.$attr['suffix'],    $structure['address2'])."<br />\n";
$output['body'] .= html5('city'.$attr['suffix'],        $structure['city'])."<br />\n";
$output['body'] .= html5('state'.$attr['suffix'],       $structure['state'])."<br />\n";
if (isset($structure['postal_code'])) { $output['body'] .= html5('postal_code'.$attr['suffix'], $structure['postal_code'])."<br />\n"; }
$output['body'] .= htmlComboCountry('country'.$attr['suffix'], $structure['country']['attr']['value'])."<br />\n";
if (isset($structure['telephone1']))  { $output['body'] .= html5('telephone1'.$attr['suffix'],  $structure['telephone1'])."<br />\n"; }
if (isset($structure['telephone4']))  { $output['body'] .= html5('telephone4'.$attr['suffix'],  $structure['telephone4'])."<br />\n"; }
$output['body'] .= html5('email'.$attr['suffix'],       $structure['email'])."<br />\n";
$output['body'] .= "  </div>\n";
$output['body'] .= "</div>\n";

$output['jsBody'][] = "
var addressVals{$attr['suffix']} = ".(isset($data['address'][$attr['suffix']]) ? $data['address'][$attr['suffix']] : "[]").";
jq('#addressSel{$attr['suffix']}').combogrid({
	width:     150,
	panelWidth:750,
    data:      addressVals{$attr['suffix']},
	idField:   'id',
	textField: 'primary_name',
    onSelect:  function (id, data){ addressFill(data, '{$attr['suffix']}'); },
	columns:   [[
		{field:'address_id', hidden:true},
		{field:'primary_name',title:'".jsLang('address_book_primary_name')."', width:200},
		{field:'address1',    title:'".jsLang('address_book_address1')."', width:100},
		{field:'city',        title:'".jsLang('address_book_city')."', width:100},
		{field:'state',       title:'".jsLang('address_book_state')."', width: 50},
		{field:'postal_code', title:'".jsLang('address_book_postal_code')."', width:100},
		{field:'telephone1',  title:'".jsLang('address_book_telephone1')."', width:100}
	]]
});";
// show the address drop down if values are present
if (isset($data['address'][$attr['suffix']])) { 
    $output['jsReady'][] = "jq('#addressDiv{$attr['suffix']}').show();";
}
$output['jsReady'][] = "setInnerLabels(addressFields, '".$attr['suffix']."');";
