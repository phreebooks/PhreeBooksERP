<?php
/*
 * View for Users details - General tab
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
 * @version    2.x Last Update: 2018-02-18
 * @filesource /lib/view/module/bizuno/tabUsersGeneral.php
 */

namespace bizuno;

$output['body'] .= "
<fieldset><legend>".lang('general')."</legend>".
	html5('admin_id',      $data['fields']['admin_id']).
	html5('email',         $data['fields']['email']).
	html5('inactive',      $data['fields']['inactive'])."<br />".
	html5('title',         $data['fields']['title'])."<br />".
	html5('role_id',       $data['fields']['role_id'])."<br />".
	html5('contact_id',    $data['fields']['contact_id'])."<br />".
	html5('store_id',      $data['fields']['store_id'])."<br />".
	html5('restrict_store',$data['fields']['restrict_store'])."<br />".
    html5('restrict_user' ,$data['fields']['restrict_user']).
"</fieldset>
<fieldset><legend>".lang('profile')."</legend>\n".
	html5('theme', $data['fields']['theme']) ."<br />".
	html5('colors',$data['fields']['colors'])."<br />".
	html5('menu',  $data['fields']['menu'])  ."<br />".
	html5('cols',  $data['fields']['cols'])."
</fieldset>
";
include (BIZUNO_LIB."view/module/bizuno/divAttach.php");

$output['jsBody'][] = "
jq('#contact_id').combogrid({
	value:      '{$data['fields']['contact_id']['attr']['value']}',
    width:       130,
	panelWidth:  322,
	delay:       900,
	idField:     'id',
	textField:   'primary_name',
	mode:        'remote',
	url:         '".BIZUNO_AJAX."&p=contacts/main/managerRows&type=e&rID={$data['fields']['contact_id']['attr']['value']}',
	onBeforeLoad:function (param) { var newValue=jq('#contact_id').combogrid('getValue'); if (newValue.length<1 || newValue == '0') return false; },
	columns:     [[
		{field:'id',          hidden:true},
		{field:'short_name',  title:'".jsLang('id')."', width:100},
		{field:'primary_name',title:'".jsLang('address_book_primary_name')."', width:200}]]
});";
