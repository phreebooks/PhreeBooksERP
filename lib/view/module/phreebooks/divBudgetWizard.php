<?php
/*
 * View for Budget Wizard 
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
 * @filesource /lib/view/module/phreebooks/divBudgetWizard.php
 */

namespace bizuno;

$output['body'] .= "
<fieldset>
	<legend>".lang('wizard')."</legend>".html5('frmWizard',$viewData['forms']['frmWizard'])."
	<p>".$viewData['lang']['phreebooks_budget_wizard_desc']."</p>
	<p>".$viewData['lang']['budget_dest_fy']    .html5('destFY',    $viewData['fields']['destFY'])
		.$viewData['lang']['budget_src_fy']     .html5('srcFY',     $viewData['fields']['srcFY'])
		.$viewData['lang']['budget_using']      .html5('srcData',   $viewData['fields']['srcData'])
		.$viewData['lang']['budget_adjust']     .html5('adjVal',    $viewData['fields']['adjVal'])
		.lang('percent').'<br />'  .html5('avgVal',    $viewData['fields']['avgVal'])
		.$viewData['lang']['budget_average']    .html5('btnSaveWiz',$viewData['fields']['btnSaveWiz'])."</p>
	<hr>
	<p>".$viewData['lang']['build_next_fy_desc'].html5('btnNextFY', $viewData['fields']['btnNextFY'])."</p>
	</form>
</fieldset>\n";
$output['jsBody'][] = "
	function budgetSave() {
		jq('#dgBudget').edatagrid('saveRow');
		var data = {gl:jq('#glAcct').val(), fy:jq('#fy').val(), dg:jq('#dgBudget').datagrid('getData')};
		jsonAction('phreebooks/budget/save', 0, JSON.stringify(data));
	}
	function budgetTotal() {
		var rowData = jq('#dgBudget').edatagrid('getData');
		var total = 0;
		for (var rowIndex=0; rowIndex<12; rowIndex++) total += parseFloat(rowData.rows[rowIndex].cur_bud);
		rowData.rows[12].cur_bud = total;
		jq('#dgBudget').edatagrid('loadData', rowData);
	}
	function budgetClear() {
		jq('#dgBudget').edatagrid('saveRow');
		var rowData = jq('#dgBudget').edatagrid('getData');
		for (var rowIndex=0; rowIndex<13; rowIndex++) rowData.rows[rowIndex].cur_bud = 0;
		jq('#dgBudget').edatagrid('loadData', rowData);
	}
	function budgetCopy() {
		jq('#dgBudget').edatagrid('saveRow');
		var rowData = jq('#dgBudget').edatagrid('getData');
		for (var rowIndex=0; rowIndex<13; rowIndex++) rowData.rows[rowIndex].cur_bud = rowData.rows[rowIndex].last_act;
		jq('#dgBudget').edatagrid('loadData', rowData);
	}
		function budgetAverage() {
		jq('#dgBudget').edatagrid('saveRow');
		var rowData = jq('#dgBudget').edatagrid('getData');
		var total = 0;
		for (var rowIndex=0; rowIndex<12; rowIndex++) total += parseFloat(rowData.rows[rowIndex].cur_bud);
		var avg = total / 12;
		for (var rowIndex=0; rowIndex<12; rowIndex++) rowData.rows[rowIndex].cur_bud = avg;
		rowData.rows[12].cur_bud = total;
		jq('#dgBudget').edatagrid('loadData', rowData);
	}
	function budgetDistribute() {
		jq('#dgBudget').edatagrid('saveRow');
		var rowData = jq('#dgBudget').edatagrid('getData');
		var total = rowData.rows[12].cur_bud;
		var avg = total / 12;
		for (var rowIndex=0; rowIndex<12; rowIndex++) rowData.rows[rowIndex].cur_bud = avg;
		jq('#dgBudget').edatagrid('loadData', rowData);
	}
	ajaxForm('frmWizard');";
