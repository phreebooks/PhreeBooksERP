<?php
/*
 * View for Inventory general tab - phreebooks portion
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
 * @version    2.x Last Update: 2017-08-12

 * @filesource /lib/view/module/phreebooks/tabInvPhreebooks.php
 */

namespace bizuno;

$output['body'] .= '
<fieldset><legend>'.lang('details').' ('.getModuleCache('phreebooks', 'properties', 'title').')</legend>
<table style="border-style:none;width:100%">
	<tbody>
		<tr><td>'.html5('inventory_type',$data['fields']['inventory_type'])."</td><td>".html5('gl_sales',$data['fields']['gl_sales'])."</td></tr>
		<tr><td>".html5('upc_code',      $data['fields']['upc_code'])."</td><td>"      .html5('gl_inv',  $data['fields']['gl_inv'])."</td></tr>
		<tr><td>".html5('cost_method',   $data['fields']['cost_method'])."</td><td>"   .html5('gl_cogs', $data['fields']['gl_cogs'])."</td></tr>
	</tbody>
</table>
</fieldset>\n";
