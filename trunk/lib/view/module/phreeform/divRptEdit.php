<?php
/*
 * 
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

 * @filesource /lib/view/module/phreeform/divRptEdit.php
 */

namespace bizuno;

$output['body'] .= html5('frmPhreeform',$data['form']['frmPhreeform'])
.html5('id',          $data['fields']['id'])
.html5('reporttype',  $data['fields']['rptType'])
.html5('tables',      ['attr'=>  ['type'=>'hidden']])
.html5('fieldlist',   ['attr'=>  ['type'=>'hidden']])
.html5('grouplist',   ['attr'=>  ['type'=>'hidden']])
.html5('sortlist',    ['attr'=>  ['type'=>'hidden']])
.html5('filterlist',  ['attr'=>  ['type'=>'hidden']])
.html5('xChild',      ['attr'=>  ['type'=>'hidden']]);
htmlTabs($output, $data, 'tabPhreeForm'); // htmlTabs must stand alone as it modifies $data
$output['body'] .= '</form>';
$output['jsBody'][] = "
function preSubmit() {
	jq('#dgTables').edatagrid('saveRow');
	if (jq('#dgTables').length)  jq('#tables').val(JSON.stringify(jq('#dgTables').datagrid('getData')));
	jq('#dgFields').edatagrid('saveRow');
	if (jq('#dgFields').length)  jq('#fieldlist').val(JSON.stringify(jq('#dgFields').datagrid('getData')));
	jq('#dgGroups').edatagrid('saveRow');
	if (jq('#dgGroups').length)  jq('#grouplist').val(JSON.stringify(jq('#dgGroups').datagrid('getData')));
	jq('#dgSort').edatagrid('saveRow');
	if (jq('#dgSort').length)    jq('#sortlist').val(JSON.stringify(jq('#dgSort').datagrid('getData')));
	jq('#dgFilters').edatagrid('saveRow');
	if (jq('#dgFilters').length) jq('#filterlist').val(JSON.stringify(jq('#dgFilters').datagrid('getData')));
	return true;
}
ajaxForm('frmPhreeform');";
