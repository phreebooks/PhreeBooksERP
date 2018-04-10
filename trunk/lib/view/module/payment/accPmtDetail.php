<?php
/*
 * View for main payment screen
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
 * @version    2.x Last Update: 2017-05-14

 * @filesource /lib/view/module/payment/accPmtDetail.php
 */

namespace bizuno;

$output['body'] .= " <fieldset>\n";
$output['body'] .= "  <legend>".lang('payment_method')."</legend>\n";
// if we have data, see what the stored values are to set defaults
$dataValues= [];
$dispFirst = $data['fields']['selMethod']['attr']['value'] = $data['journal_main']['method_code']['attr']['value'];

if (isset($data['items'])) { foreach ($data['items'] as $row) { // fill in the data if available
	$props = explode(";", $row['description']);
	foreach ($props as $val) {
		$tmp = explode(":", $val);
		$dataValues[$tmp[0]] = isset($tmp[1]) ? $tmp[1] : '';
	}
	if (!isset($row['id']) || !$row['id']) { // insert
		$txID = dbGetValue(BIZUNO_DB_PREFIX."journal_item", array('description','trans_code'), "ref_id='{$data['items'][0]['item_ref_id']}' AND gl_type='ttl'");
		$props = !empty($txID['description']) ? explode(";", $txID['description']) : [];
		foreach ($props as $val) {
			$tmp = explode(":", $val);
			$dataValues[$tmp[0]] = isset($tmp[1]) ? $tmp[1] : '';
		}
		$dataValues['id']        = $row['item_ref_id'];
		$dataValues['trans_code']= !empty($txID['trans_code']) ? $txID['trans_code'] : '';
		$dataValues['total']     = !empty($row['total']) ? $row['total'] : 0;
	}
} }
// set the pull down for which method, onChange execute javascript function to load defaults
$output['body'] .= html5('method_code', $data['fields']['selMethod']);
$methods = sortOrder(getModuleCache('payment', 'methods'));
foreach ($methods as $method => $settings) {
    if (isset($settings['status']) && $settings['status']) { // load the div for each method
        if (!is_file($settings['path']."$method.php")) {
            msgAdd("I cannot find the method $method to load! Skipping.");
            continue;
        }
        if (!$dispFirst) { $dispFirst = $method; }
        $style = $dispFirst == $method ? '' : ' style="display:none;"'; 
        $output['body'] .= '<div id="div_'.$method.'" class="layout-expand-over"'.$style.'>'."\n";
        require_once($settings['path']."$method.php");
        $pmtSet = getModuleCache('payment','methods',$method,'settings');
        $fqcn = "\\bizuno\\$method";
        $temp = new $fqcn($pmtSet);
        $temp->render($output, $data, $dataValues, $dispFirst);
        $output['body'] .= "</div>\n";
    }
}
$output['body'] .= " </fieldset>\n";
