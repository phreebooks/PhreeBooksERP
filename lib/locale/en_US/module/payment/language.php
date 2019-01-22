<?php
/*
 * Language translation for Payment module
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
 * @version    3.x Last Update: 2018-12-19
 * @filesource /locale/en_US/module/payment/language.php
 */

$lang = [
    'title' => 'Payment',
    'description' => 'The payment module is a wrapper for user configurable payment methods. Some methods are included with the core package and others are available for download from the PhreeSoft website. <b>NOTE: This is a core module and cannot be removed!</b>',
    'payment_settings_discount_gl' => 'Default GL Discount Account to use for this payment method.',
    'payment_settings_deposit_prefix' => 'Default prefix to use for deposit slips.',
    'stored' => 'Stored',
    // Settings
    'gl_payment_c_lbl' => 'AR Cash GL Account',
    'gl_payment_c_tip'  => 'Default GL account to use for payments received from customers. Typically a Cash type account.',
    'gl_discount_c_lbl' => 'AR Disc GL Account',
    'gl_discount_c_tip' => 'Default GL account to use for payment discounts from customers. Typically a Sales/Income type account.',
    'gl_payment_v_lbl' => 'AP Cash GL Account',
    'gl_payment_v_tip'  => 'Default GL account to use for payments to vendors (bills). Typically a Cash type account.',
    'gl_discount_v_lbl' => 'AP Disc GL Account',
    'gl_discount_v_tip' => 'Default GL account to use for payment discounts to vendors. Typically a Cost of Goods Sold type account.',
    'prefix_lbl' => 'Reference Prefix',
    'prefix_tip' => 'Default prefix for deposits. Deposits with the same ID are grouped together and simplify bank account reconciliation.',
    // Error Messages
    'err_payment_dup' => 'This payment has already been processed, resubmission to the payment gateway has been skipped!',
    'err_cvv_mismatch' => 'CAUTION! The CCV code was returned but did not match at the processor. Your processor responded with: %s. This may be an indication of a stolen credit card!',
    'err_avs_mismatch' => 'CAUTION! The Address Verification Response code was returned but did not match at your processor. Your processor responded with: %s. This may be an indication of a stolen credit card!',
    // AVS Codes
    'AVS_A' => 'Address matches - Postal Code does not match.',
    'AVS_B' => 'Street address match, Postal code in wrong format. (International issuer)',
    'AVS_C' => 'Street address and postal code in wrong formats.',
    'AVS_D' => 'Street address and postal code match. (international issuer)',
    'AVS_E' => 'AVS Error.',
    'AVS_G' => 'Service not supported by non-US issuer.',
    'AVS_I' => 'Address information not verified by international issuer.',
    'AVS_M' => 'Street address and Postal code match. (international issuer)',
    'AVS_N' => 'No match on address (street) or postal code.',
    'AVS_O' => 'No response sent.',
    'AVS_P' => 'Postal code matches, street address not verified due to incompatible formats.',
    'AVS_R' => 'Retry, system unavailable or timed out.',
    'AVS_S' => 'Service not supported by issuer.',
    'AVS_U' => 'Address information is unavailable.',
    'AVS_W' => '9 digit postal code matches, address (street) does not match.',
    'AVS_X' => 'Exact AVS match.',
    'AVS_Y' => 'Address (street) and 5 digit postal code match.',
    'AVS_Z' => '5 digit postal code matches, address (street) does not match.',
    // CCV Codes
    'CVV_M' => 'CVV2 matches',
    'CVV_N' => 'CVV2 does not match',
    'CVV_P' => 'Not Processed',
    'CVV_S' => 'Issuer indicates that CVV2 data should be present on the card, but the merchant has indicated that the CVV2 data is not present on the card.',
    'CVV_U' => 'Issuer has not certified for CVV2 or issuer has not provided Visa with the CVV2 encryption keys.'];
