<?php
/*
 * View for entering terminal dates
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

 * @filesource /lib/view/module/phreebooks/winDelivery.php
 */

namespace bizuno;

$output['body'] .= '
<table style="border-collapse:collapse;width:100%;">
	<thead><tr class="panel-header"><th>'.lang('qty')."</th><th>".lang('sku')."</th><th>".lang('description')."</th><th>".lang('date')."</th></tr></thead>
	<tbody>";
	foreach ($data['fields']['items'] as $row) {
		$output['body'] .= "
		<tr>
			<td>".$row['qty']."</td><td>".$row['sku']."</td><td>".$row['description']."</td>
			<td>".html5("rID_{$row['id']}", ['classes'=> ['easyui-datebox'], 'attr'=> ['value'=>viewFormat($row['date_1'], 'date')]])."</td>
		</tr>";
	}
$output['body'] .= '
	</tbody>
	<tfooter><tr><td colspan="4" style="text-align:right">'.html5('delSave', $data['fields']['delSave'])."</td></tr></tfooter>
</table>\n";
