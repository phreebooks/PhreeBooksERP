<?php
/*
 * View fo PhreeBooks inventory adjustments
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
 * @version    2.x Last Update: 2017-04-02
 * @filesource /lib/view/module/phreebooks/accInvAdjDetail.php
 */

namespace bizuno;

$output['body'] .= '
<div style="float:right;width:30%">';
foreach ($viewData['totals_methods'] as $methID) {
    require_once(BIZUNO_LIB."controller/module/phreebooks/totals/$methID/$methID.php");
    $totSet = getModuleCache('phreebooks','totals',$methID,'settings');
    $fqcn = "\\bizuno\\$methID";
    $totals = new $fqcn($totSet);
    $content = $totals->render($output, $viewData);
}
$output['body'] .= "</div>";
// Hidden fields
$output['body'] .= html5('id',             $viewData['fields']['main']['id'])."\n";
$output['body'] .= html5('journal_id',     $viewData['fields']['main']['journal_id']);
$output['body'] .= html5('item_array',     $viewData['item_array']);
$output['body'] .= html5('recur_id',       $viewData['fields']['main']['recur_id']);
$output['body'] .= html5('recur_frequency',$viewData['recur_frequency']);
// Displayed fields
$output['body'] .= html5('invoice_num',    $viewData['fields']['main']['invoice_num'])."\n";
$output['body'] .= html5('store_id',       $viewData['fields']['main']['store_id'])."\n";
$output['body'] .= html5('post_date',      $viewData['fields']['main']['post_date'])."\n";
