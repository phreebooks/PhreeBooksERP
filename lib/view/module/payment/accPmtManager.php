<?php
/*
 * View for Payment Manager
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
 * @version    2.x Last Update: 2016-09-10

 * @filesource /lib/view/module/payment/accPmtManager.php
 */

namespace bizuno;

if (getUserCache('profile', 'admin_encrypt')) {
    htmlDatagrid($output, $data, 'dgPayment');
    $output['body'] .= '<div id="frmPayment" style="width:50%">'."\n";
    $output['body'] .= html5('payment_id', $data['fields']['payment_id']);
    $output['body'] .= " <fieldset><legend>".lang('payment_new')."</legend>\n";
    htmlToolbar($output, $data, 'tbPayment');
    $output['body'] .= '    <table style="border-style:none;margin-left:auto;margin-right:auto;">'."\n";
    $output['body'] .= '     <tbody>'."\n";
    $output['body'] .= "	  <tr><td>".html5('payment_name',$data['fields']['payment_name'])."</td></tr>\n";
    $output['body'] .= "	  <tr><td>".html5('payment_num', $data['fields']['payment_num'])."</td></tr>\n";
    $output['body'] .= "	  <tr><td>".html5('payment_mon', $data['fields']['payment_mon']).html5('payment_year',$data['fields']['payment_year'])."</td></tr>\n";
    $output['body'] .= "	  <tr><td>".html5('payment_cvv', $data['fields']['payment_cvv'])."</td></tr>\n";
    $output['body'] .= "	 </tbody>\n";
    $output['body'] .= "    </table>\n";
    $output['body'] .= " </fieldset>\n";
    $output['body'] .= "</div>\n";
} else {
    $output['body'] .= "The encryption key has not been set! Results will not be shown until the encryption key has been entered.";
}