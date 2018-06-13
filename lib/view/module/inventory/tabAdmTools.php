<?php
/*
 * View for Inventory tools tab
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
 * @version    2.x Last Update: 2018-01-24
 * @filesource /lib/view/module/inventory/tabAdmTools.php
 */

namespace bizuno;

$output['body'] .= "
<fieldset><legend>".$viewData['lang']['inv_tools_val_inv']."</legend>
	<p>".$viewData['lang']['inv_tools_val_inv_desc']."</p>
	<table>
		<thead><tr><th>".$viewData['lang']['inv_tools_repair_test']."</th><th>".$viewData['lang']['inv_tools_repair_fix']."</th></tr></thead>
		<tbody><tr><td>".html5('', $viewData['fields']['btnHistTest'])."</td><td>".html5('', $viewData['fields']['btnHistFix'])."</td></tr></tbody>
	</table>
</fieldset>
<fieldset><legend>".$viewData['lang']['inv_tools_qty_alloc']."</legend>
	<p>".$viewData['lang']['inv_tools_qty_alloc_desc']."</p>
	<p>".html5('', $viewData['fields']['btnAllocFix'])."</p>
</fieldset>
<fieldset><legend>".$viewData['lang']['inv_tools_repair_so_po']."</legend>
	<p>".$viewData['lang']['inv_tools_validate_so_po_desc']."</p>
	<p>".html5('', $viewData['fields']['btnJournalFix'])."</p>
</fieldset>
<fieldset><legend>".$viewData['lang']['inv_tools_price_assy']."</legend>
	<p>".$viewData['lang']['inv_tools_price_assy_desc']."</p>
	<p>".html5('', $viewData['fields']['btnPriceAssy'])."</p>
</fieldset>\n";
