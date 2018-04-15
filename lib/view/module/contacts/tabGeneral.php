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
 * @version    2.x Last Update: 2017-01-27
 * @filesource /lib/view/module/contacts/tabGeneral.php
 */

namespace bizuno;

$output['body'] .= html5('id',  $data['fields']['contacts']['id']);
$output['body'] .= html5('type',$data['fields']['contacts']['type']);
$output['body'] .= "  <fieldset>\n";
$output['body'] .= "  <legend>".lang('general')."</legend>\n";
$output['body'] .= '<div style="float:right">Record ID: '.$data['fields']['contacts']['id']['attr']['value']."</div>"; // displays the contact idin the lower left corner of the general tab
switch ($data['fields']['contacts']['type']['attr']['value']) {
	case 'b':
		$output['body'] .= "
<table>
	<tr>
		<td>".html5('short_name',    $data['fields']['contacts']['short_name']).' '.html5('inactive', $data['fields']['contacts']['inactive'])."</td>
		<td>".html5('rep_id',        $data['fields']['contacts']['rep_id'])   ."</td>
	</tr>
	<tr>
		<td>".html5('gl_account',    $data['fields']['contacts']['gl_account'])    ."</td>
		<td>".(sizeof(getModuleCache('inventory', 'prices'))?html5('price_sheet',$data['fields']['contacts']['price_sheet']):'&nbsp;')."</td>
	</tr>
	<tr>
		<td>".html5('flex_field_1',  $data['fields']['contacts']['flex_field_1'])  ."</td>
		<td>".html5('tax_rate_id',   $data['fields']['contacts']['tax_rate_id'])   ."</td>
	</tr>
	<tr>
		<td>".html5('account_number',$data['fields']['contacts']['account_number'])."</td>
	</tr>
	<tr>
		<td>".html5('gov_id_number', $data['fields']['contacts']['gov_id_number']) ."</td>
		<td>".html5('terms',         $data['fields']['contacts']['terms']).html5('terms_text', $data['terms_text']).html5('terms_edit', $data['terms_edit'])."</td>
	</tr>
</table>\n";
		break;

	case 'e':
		$output['body'] .= "
<table>
	<tr>
		<td>".html5('short_name',    $data['fields']['contacts']['short_name'])    ."</td>
		<td>".html5('inactive',      $data['fields']['contacts']['inactive'])      ."</td>
		<td>".html5('gov_id_number', $data['fields']['contacts']['gov_id_number']) ."</td>
	</tr>
	<tr>
		<td>".html5('contact_first', $data['fields']['contacts']['contact_first']) ."</td>
		<td>".html5('flex_field_1',  $data['fields']['contacts']['flex_field_1'])  ."</td>
		<td>".html5('contact_last',  $data['fields']['contacts']['contact_last'])  ."</td>
	</tr>
	<tr>
		<td>&nbsp;</td>
		<td>&nbsp;</td>
		<td>".html5('store_id',      $data['fields']['contacts']['store_id'])."</td>
	</tr>
</table>\n";
		break;

	case 'i':
		$output['body'] .= "
<table>
	<tr>
		<td>".html5('short_name',    $data['fields']['contacts']['short_name'])    ."</td>
		<td>".html5('inactive',      $data['fields']['contacts']['inactive'])      ."</td>
	</tr>
	<tr>
		<td>".html5('contact_first', $data['fields']['contacts']['contact_first']) ."</td>
		<td>".html5('flex_field_1',  $data['fields']['contacts']['flex_field_1'])."</td>
	</tr>
	<tr>
		<td>".html5('contact_last',  $data['fields']['contacts']['contact_last'])  ."</td>
		<td>".html5('rep_id',        $data['fields']['contacts']['rep_id'])   ."</td>
		<td>".html5('store_id',      $data['fields']['contacts']['store_id'])."</td>
	</tr>
</table>\n";
		break;

	default:
		$output['body'] .= "
<table>
	<tr>
		<td>".html5('short_name',    $data['fields']['contacts']['short_name'])    ."</td>
		<td>".html5('inactive',      $data['fields']['contacts']['inactive'])      ."</td>
		<td>".html5('rep_id',        $data['fields']['contacts']['rep_id'])   ."</td>
	</tr>
	<tr>
		<td>".html5('contact_first', $data['fields']['contacts']['contact_first']) ."</td>
		<td>".html5('account_number',$data['fields']['contacts']['account_number'])."</td>
		<td>".(sizeof(getModuleCache('inventory', 'prices'))?html5('price_sheet',$data['fields']['contacts']['price_sheet']):'&nbsp;')."</td>
	</tr>
	<tr>
		<td>".html5('contact_last',  $data['fields']['contacts']['contact_last'])  ."</td>
		<td>".html5('gov_id_number', $data['fields']['contacts']['gov_id_number']) ."</td>
		<td>".html5('tax_rate_id',   $data['fields']['contacts']['tax_rate_id'])   ."</td>
	</tr>
	<tr>
		<td>".html5('flex_field_1',  $data['fields']['contacts']['flex_field_1'])  ."</td>
		<td>".html5('gl_account',    $data['fields']['contacts']['gl_account'])    ."</td>
		<td>".html5('terms',         $data['fields']['contacts']['terms']).html5('terms_text', $data['terms_text']).html5('terms_edit', $data['terms_edit'])."</td>
	</tr>
	<tr>
		<td>&nbsp;</td>
		<td>&nbsp;</td>
		<td>".html5('store_id',      $data['fields']['contacts']['store_id'])."</td>
	</tr>
</table>\n";
	break;
}
$output['body'] .= "  </fieldset>\n";
if ($data['fields']['contacts']['type']['attr']['value'] == 'i') { // show the combogrid for contact link
	$linkID = isset($data['fields']['contacts']['rep_id']['attr']['value']) ? $data['fields']['contacts']['rep_id']['attr']['value'] : 0;
	$output['jsBody'][] = "
jq('#rep_id').combogrid({width:225,panelWidth:825,delay:700,idField:'id',textField:'primary_name',mode:'remote',
	url:    '".BIZUNO_AJAX."&p=contacts/main/managerRows&rID=$linkID&type=cv',
	columns:[[{field:'id',hidden:true},
		{field:'short_name',  title:'".pullTableLabel(BIZUNO_DB_PREFIX."contacts",    'short_name')."',  width:100},
		{field:'type',        title:'".pullTableLabel(BIZUNO_DB_PREFIX."contacts",    'type')."',        width:100},
		{field:'primary_name',title:'".pullTableLabel(BIZUNO_DB_PREFIX."address_book",'primary_name')."',width:200},
		{field:'address1',    title:'".pullTableLabel(BIZUNO_DB_PREFIX."address_book",'address1')."',    width:200},
		{field:'city',        title:'".pullTableLabel(BIZUNO_DB_PREFIX."address_book",'city')."',        width:100},
		{field:'postal_code', title:'".pullTableLabel(BIZUNO_DB_PREFIX."address_book",'postal_code')."', width:100}
	]]
});";
}
