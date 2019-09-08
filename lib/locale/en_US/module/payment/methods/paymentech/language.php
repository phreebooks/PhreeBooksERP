<?php
/*
 * Language translation for payment extension - method Chase Paymentech
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
 * @copyright  2008-2019, PhreeSoft, Inc.
 * @license    http://opensource.org/licenses/OSL-3.0  Open Software License (OSL 3.0)
 * @version    3.x Last Update: 2019-07-13
 * @filesource /locale/en_US/module/payment/methods/paymentech/language.php
 */

$lang     = [
    'title'       => 'Chase Paymentech',
    'description' => 'Accept credit card payments through the paymentech payment gateway.',
    'at_paymentech' => '@Paymentech',
    'merchant_bin'=> 'Merchant Bin (provided by Chase)',
    'merchant_id' => 'Merchant ID (provided by Chase)',
    'username'    => 'User Name (set at the Paymentech portal)',
    'password'    => 'User Password (set at the Paymentech portal)',
    'auth_type'   => 'Authorization Type',
    'allow_refund'=> 'Allow Void/Refunds? This must be enabled by Paymentech for your merchant account or refunds will not be allowed.',
    'msg_approval_success' => '%s - Approval code: %s --> CVV2 results: %s',
    'err_process_decline' => 'Decline Code #%s: %s',
    'err_process_failed' => 'The credit card did not process, the response from Paymentech:',
   ];
