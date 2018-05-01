<?php
/*
 * View for Contacts - Address Long format
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
 * @filesource /lib/view/module/contacts/divAddressFull.php
 */

namespace bizuno;

$defaults = ['suffix'=>'', 'clear'=>true, 'validate'=>true, 'required'=>false];
$attr     = isset($prop['settings']) ? array_replace($defaults, $prop['settings']) : $defaults;
$entries  = [];
$structure= $data['fields']['address_book'];
if (!$structure['country']['attr']['value']) { $structure['country']['attr']['value'] = getModuleCache('bizuno', 'settings', 'company', 'country'); }
if (isset($data['values']['address_book'])) {
    foreach ($data['values']['address_book'] as $address) { if ($attr['suffix'] == $address['type']) { $entries[] = $address; } }
    if (sizeof($entries) > 0 && $attr['suffix'] == 'm') { foreach ($address as $field => $value) { $structure[$field]['attr']['value'] = $entries[0][$field]; } }
}
if ($attr['required']) { foreach ($structure as $field => $props) {
        if (getModuleCache('contacts', 'settings', 'address_book', $field)) {
            $structure[$field]['attr']['required'] = 1;
        }
} }
// Show a datagrid if entries are present
if (isset($data['datagrid']['dgAddress'.$attr['suffix']]) && $attr['suffix'] <> 'm') {
    htmlDatagrid($output, $data, 'dgAddress'.$attr['suffix']);
}
// Show a toolbar if present
if (isset($data['toolbar']['tbAddress'.$attr['suffix']]) && $attr['suffix'] <> 'm') { 
    htmlToolbar($output, $data, 'tbAddress'.$attr['suffix']);
}
// Start the div to submit
$output['body'] .= '<div id="address'.$attr['suffix'].'">'."\n";
// Show additional form fields
if (isset($data['xFields']['address'.$attr['suffix']])) {
    $output['body'] .= $data['xFields']['address'.$attr['suffix']];
}
// Start the address block
$output['body'] .= '
<fieldset><legend>'.lang('address_book_type', $attr['suffix']).'</legend>
    <table id="formAddress'.$attr['suffix'].'" style="border-collapse:collapse;width:100%;">
        <tr><td><table style="border-collapse:collapse;width:100%;">
        <tr>
            <td>'.html5('address_id'.$attr['suffix'],  $structure['address_id']).
                  html5('type'.$attr['suffix'],        $structure['type']).
                  html5('', ['icon'=>'clear', 'size'=>'small', 'label'=>lang('clear'), 'hidden'=>$attr['clear'] ? '0' : '1',
                    'events'=>  ['onClick'=>"clearAddress('{$attr['suffix']}')"]]);
if ($attr['validate'] && getModuleCache('extShipping', 'properties', 'status')) {
    $output['body'] .= ' '.html5('', ['icon'=>'truck','size'=>'small','label'=>lang('validate_address'),'events'=>['onClick'=>"shippingValidate('{$attr['suffix']}');"]])."\n";
}
$output['body'] .= ' '.html5('primary_name'.$attr['suffix'],$structure['primary_name'])."
            </td>
            <td>".html5('telephone1'.$attr['suffix'],  $structure['telephone1'])."</td>
        </tr>
        <tr>
            <td>".html5('contact'.$attr['suffix'],     $structure['contact'])."</td>
            <td>".html5('telephone2'.$attr['suffix'],  $structure['telephone2'])."</td>
        </tr>
        <tr>
            <td>".html5('address1'.$attr['suffix'],    $structure['address1'])."</td>
            <td>".html5('telephone3'.$attr['suffix'],  $structure['telephone3'])."</td>
        </tr>
        <tr>
            <td>".html5('address2'.$attr['suffix'],    $structure['address2'])."</td>
            <td>".html5('telephone4'.$attr['suffix'],  $structure['telephone4'])."</td>
        </tr>
        <tr>
            <td>".html5('city'.$attr['suffix'],        $structure['city'])."</td>
            <td>".html5('email'.$attr['suffix'],       $structure['email'])."</td>
        </tr>
        <tr>
            <td>".html5('state'.$attr['suffix'],       $structure['state'])."</td>
            <td>".html5('website'.$attr['suffix'],     $structure['website'])."</td>
        </tr>
        <tr>
            <td>".html5('postal_code'.$attr['suffix'], $structure['postal_code'])."</td>
            <td>".htmlComboCountry('country'.$attr['suffix'], $structure['country']['attr']['value'])."</td>
        </tr>
       ".($attr['suffix']<>'m' ? '<tr><td colspan="2">'.html5('notes'.$attr['suffix'], $structure['notes']).'</td></tr>' : '')."
        </table></td></tr>
    </table>
</fieldset></div>\n";
