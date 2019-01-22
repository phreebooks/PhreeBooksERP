<?php
/*
 * Language translation for payment extension - method Authorize.net 
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
 * @version    3.x Last Update: 2018-09-05
 * @filesource /locale/en_US/module/payment/methods/authorizenet/language.php
 */

$lang = [
    'title'       => 'Authorize.net',
    'description' => 'Accept credit card payments through the Authorize.net payment gateway.',
    'at_authorizenet' => '@Authorize.net',
//    'merchant_id' => 'Merchant ID (provided by Authorize.net)',
    'user_id'     => 'User ID (provided by Authorize.net)',
    'txn_key'     => 'Transaction Key',
    'auth_type'   => 'Authorization Type',
    'prefix_amex' => 'Prefix to use for American Express credit cards. (These cards are processed and reconciled through American Express)',
    'allow_refund'=> 'Allow Void/Refunds? This must be enabled by Authorize.net for your merchant account or refunds will not be allowed.',
    'msg_website' => 'This must be done manually at the Authorize.net website.',
    'msg_capture_manual' => 'The payment was not processed through the Authorize.net gateway.',
    'msg_approval_success' => '%s - Approval code: %s --> CVV2 results: %s',
    'msg_address_result' => 'Address verification results: %s',
    'err_process_decline' => 'Decline Code #%s: %s',
    'err_process_failed' => 'The credit card did not process, the response from Authorize.net:',
   ];
