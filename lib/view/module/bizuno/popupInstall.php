<?php
/*
 * View for the installation popup form
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
 * @version    2.x Last Update: 2018-03-29
 * @filesource /lib/view/module/bizuno/popupInstall.php
 */

namespace bizuno;

$output['body'] .= '
<p>'.$data['text']['intro'].'</p>
<div id="divInstall">
<table style="border-collapse:collapse;width:100%">
	<thead class="panel-header"><tr><th colspan="2">&nbsp;</th></tr></thead>
	<tbody>
		<tr><td>'.$data['text']['biz_title']   .'</td><td>'.html5('biz_title',   $data['fields']['biz_title']).'</td></tr>
		<tr><td>'.$data['text']['biz_lang']    .'</td><td>'.html5('biz_lang',    $data['fields']['biz_lang']).'</td></tr>
		<tr><td>'.$data['text']['biz_currency'].'</td><td>'.html5('biz_currency',$data['fields']['biz_currency']).'</td></tr>
		<tr><td>'.$data['text']['biz_chart']   .'</td><td>'.html5('biz_chart',   $data['fields']['biz_chart']).'</td></tr>
		<tr><td>'.$data['text']['biz_fy']      .'</td><td>'.html5('biz_fy',      $data['fields']['biz_fy']).'</td></tr>
	</tbody>
</table>
</div>';
$output['jsBody'][] = "
function installSave(bizID) {
    jq('#instNext').remove();
    divData = jq('#divInstall :input').serializeObject();
    jq.ajax({
        url:     '".BIZUNO_AJAX."&p=bizuno/admin/installBizuno&bID='+bizID,
        type:    'post',
        data:    divData,
        async:   false,
        success: function (data) { processJson(data); }
    });
    jq('#bizInstall').window('destroy');
}";
