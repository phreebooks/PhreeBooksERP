<?php
/*
 * View for Contacts - Edit page - General tab
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
 * @version    2.x Last Update: 2018-04-27
 * @filesource /lib/view/module/contacts/tabGeneral.php
 */

namespace bizuno;

$output['body'] .= html5('id',  $viewData['fields']['contacts']['id']);
$output['body'] .= html5('type',$viewData['fields']['contacts']['type']);
$output['body'] .= "  <fieldset>\n";
$output['body'] .= "  <legend>".lang('general')."</legend>\n";
$output['body'] .= '<div style="float:right">Record ID: '.$viewData['fields']['contacts']['id']['attr']['value']."</div>"; // displays the contact id in the lower left corner of the general tab
switch ($viewData['fields']['contacts']['type']['attr']['value']) {
	case 'b':
		$output['body'] .= "
<table>
	<tr>
		<td>".html5('short_name',    $viewData['fields']['contacts']['short_name']).' '.html5('inactive', $viewData['fields']['contacts']['inactive'])."</td>
		<td>".html5('rep_id',        $viewData['fields']['contacts']['rep_id'])   ."</td>
	</tr>
	<tr>
		<td>".html5('gl_account',    $viewData['fields']['contacts']['gl_account'])    ."</td>
		<td>".(sizeof(getModuleCache('inventory', 'prices'))?html5('price_sheet',$viewData['fields']['contacts']['price_sheet']):'&nbsp;')."</td>
	</tr>
	<tr>
		<td>".html5('flex_field_1',  $viewData['fields']['contacts']['flex_field_1'])  ."</td>
		<td>".html5('tax_rate_id',   $viewData['fields']['contacts']['tax_rate_id'])   ."</td>
	</tr>
	<tr>
		<td>".html5('account_number',$viewData['fields']['contacts']['account_number'])."</td>
	</tr>
	<tr>
		<td>".html5('gov_id_number', $viewData['fields']['contacts']['gov_id_number']) ."</td>
		<td>".html5('terms',         $viewData['fields']['contacts']['terms']).html5('terms_text', $viewData['terms_text']).html5('terms_edit', $viewData['terms_edit'])."</td>
	</tr>
</table>\n";
		break;

	case 'e':
		$output['body'] .= "
<table>
	<tr>
		<td>".html5('short_name',    $viewData['fields']['contacts']['short_name'])    ."</td>
		<td>".html5('inactive',      $viewData['fields']['contacts']['inactive'])      ."</td>
		<td>".html5('gov_id_number', $viewData['fields']['contacts']['gov_id_number']) ."</td>
	</tr>
	<tr>
		<td>".html5('contact_first', $viewData['fields']['contacts']['contact_first']) ."</td>
		<td>".html5('flex_field_1',  $viewData['fields']['contacts']['flex_field_1'])  ."</td>
		<td>".html5('contact_last',  $viewData['fields']['contacts']['contact_last'])  ."</td>
	</tr>
	<tr>
		<td>&nbsp;</td>
		<td>&nbsp;</td>
		<td>".html5('store_id',      $viewData['fields']['contacts']['store_id'])."</td>
	</tr>
</table>\n";
		break;

	case 'i':
		$output['body'] .= "
<table>
	<tr>
		<td>".html5('short_name',    $viewData['fields']['contacts']['short_name'])    ."</td>
		<td>".html5('inactive',      $viewData['fields']['contacts']['inactive'])      ."</td>
	</tr>
	<tr>
		<td>".html5('contact_first', $viewData['fields']['contacts']['contact_first']) ."</td>
		<td>".html5('flex_field_1',  $viewData['fields']['contacts']['flex_field_1'])."</td>
	</tr>
	<tr>
		<td>".html5('contact_last',  $viewData['fields']['contacts']['contact_last'])  ."</td>
		<td>".html5('rep_id',        $viewData['fields']['contacts']['rep_id'])   ."</td>
		<td>".html5('store_id',      $viewData['fields']['contacts']['store_id'])."</td>
	</tr>
</table>\n";
		break;

	default:
		$output['body'] .= "
<table>
	<tr>
		<td>".html5('short_name',    $viewData['fields']['contacts']['short_name'])    ."</td>
		<td>".html5('inactive',      $viewData['fields']['contacts']['inactive'])      ."</td>
		<td>".html5('rep_id',        $viewData['fields']['contacts']['rep_id'])   ."</td>
	</tr>
	<tr>
		<td>".html5('contact_first', $viewData['fields']['contacts']['contact_first']) ."</td>
		<td>".html5('account_number',$viewData['fields']['contacts']['account_number'])."</td>
		<td>".(sizeof(getModuleCache('inventory', 'prices'))?html5('price_sheet',$viewData['fields']['contacts']['price_sheet']):'&nbsp;')."</td>
	</tr>
	<tr>
		<td>".html5('contact_last',  $viewData['fields']['contacts']['contact_last'])  ."</td>
		<td>".html5('gov_id_number', $viewData['fields']['contacts']['gov_id_number']) ."</td>
		<td>".html5('tax_rate_id',   $viewData['fields']['contacts']['tax_rate_id'])   ."</td>
	</tr>
	<tr>
		<td>".html5('flex_field_1',  $viewData['fields']['contacts']['flex_field_1'])  ."</td>
		<td>".html5('gl_account',    $viewData['fields']['contacts']['gl_account'])    ."</td>
		<td>".html5('terms',         $viewData['fields']['contacts']['terms']).html5('terms_text', $viewData['terms_text']).html5('terms_edit', $viewData['terms_edit'])."</td>
	</tr>
	<tr>
		<td>&nbsp;</td>
		<td>&nbsp;</td>
		<td>".html5('store_id',      $viewData['fields']['contacts']['store_id'])."</td>
	</tr>
</table>\n";
	break;
}
$output['body'] .= "  </fieldset>\n";
if ($viewData['fields']['contacts']['type']['attr']['value'] == 'i') { // show the combogrid for contact link
	$linkID = isset($viewData['fields']['contacts']['rep_id']['attr']['value']) ? $viewData['fields']['contacts']['rep_id']['attr']['value'] : 0;
    if ($linkID) {
        $primary_name = dbGetValue(BIZUNO_DB_PREFIX.'address_book', 'primary_name', "ref_id=$linkID AND type='m'"); }
    else { $primary_name = ''; }
	$output['jsBody'][] = "
jq('#rep_id').combogrid({width:225,panelWidth:825,delay:700,idField:'id',textField:'primary_name',mode:'remote',
    url:    '".BIZUNO_AJAX."&p=contacts/main/managerRows&type=cv&store=0',
    onBeforeLoad:function (param) { var newValue = jq('#rep_id').combogrid('getValue'); if (newValue.length < 3) return false; },
    selectOnNavigation:false,
	columns:[[{field:'id',hidden:true},
		{field:'short_name',  title:'".pullTableLabel(BIZUNO_DB_PREFIX."contacts",    'short_name')."',  width:100},
		{field:'type',        title:'".pullTableLabel(BIZUNO_DB_PREFIX."contacts",    'type')."',        width:100},
		{field:'primary_name',title:'".pullTableLabel(BIZUNO_DB_PREFIX."address_book",'primary_name')."',width:200},
		{field:'address1',    title:'".pullTableLabel(BIZUNO_DB_PREFIX."address_book",'address1')."',    width:200},
		{field:'city',        title:'".pullTableLabel(BIZUNO_DB_PREFIX."address_book",'city')."',        width:100},
		{field:'postal_code', title:'".pullTableLabel(BIZUNO_DB_PREFIX."address_book",'postal_code')."', width:100}
	]]
}).combogrid('setValue', {id:'$linkID',primary_name:'$primary_name'});";
}
