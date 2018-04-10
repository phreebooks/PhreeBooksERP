<?php
/*
 * View for PhreeForm home page detail div
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
 * @version    2.x Last Update: 2017-02-14

 * @filesource /lib/view/module/phreeform/divHomeDetail.php
 * 
 */

namespace bizuno;

$output['body'] .= '<div style="float:right;width:50%"><h3>'.$data['lang']['msg_recently_added_docs']."</h3>";
foreach ($data['values']['recent'] as $report) {
    $output['body'] .= '<div><a href="#" onClick="jsonAction(\'phreeform/main/detailReport\', '.$report['id'].');">';
    $output['body'] .= html5('', ['icon'=>viewMimeIcon($report['mime_type']), 'size'=>'small', 'label'=>$report['title']]);
    $output['body'] .= ' '.$report['title']."</a></div>\n";
}
if (sizeof($data['values']['recent']) == 0) { $output['body'] .= lang('msg_no_documents'); }
$output['body'] .= '</div><div style="width:50%"><h3>'.lang('my_documents')."</h3>";
foreach ($data['values']['mine'] as $report) {
    $output['body'] .= '<div><a href="#" onClick="jsonAction(\'phreeform/main/detailReport\', '.$report['id'].');">';
    $output['body'] .= html5('', ['icon'=>viewMimeIcon($report['mime_type']), 'size'=>'small', 'label'=>$report['title']]);
    $output['body'] .= ' '.$report['title']."</a></div>\n";
}
if (sizeof($data['values']['mine']) == 0) { $output['body'] .= lang('msg_no_documents'); }
$output['body'] .= "</div>\n";
