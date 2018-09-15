<?php
/*
 * Javascript core language file - all locales as pulled from language files
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
 * @copyright  2008-2018, PhreeSoft, Inc.
 * @license    http://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 * @version    3.x Last Update: 2018-03-30
 * @filesource /locale/i18nLang.php
 */

namespace bizuno;

require_once("cleaner.php");
$cleaner= new cleaner;

$isoLang = clean('lang', ['format'=>'cmd', 'default'=>'en_US'], 'get'); // get the locale ISO code
require("en_US/language.php"); // get the core language file
$arrLang = $langCore;
if (file_exists("$isoLang/language.php")) {
    require("$isoLang/language.php");
    $arrLang = array_replace($arrLang, $langCore);
}
require("en_US/module/phreebooks/language.php"); // need PhreeBooks lang as well
$pbLang = $lang;
if ($isoLang <> 'en_US') { 
    if (file_exists("$isoLang/module/phreebooks/language.php")) {
        require("$isoLang/module/phreebooks/language.php");
        $pbLang = array_replace($pbLang, $lang);
    }
}
// the map
$dictionary = [
	'ACCOUNT'            => $arrLang['account'],
	'CITY'               => $arrLang['address_book_city'],
	'CONTACT_ID'         => $arrLang['contacts_short_name'],
    'EDIT'               => $arrLang['edit'],
    'FINISHED'           => $arrLang['finished'],
    'INFORMATION'        => $arrLang['information'],
    'MESSAGE'            => $arrLang['message'],
	'NAME'               => $arrLang['address_book_primary_name'],
    'PB_INVOICE_RQD'     => $pbLang['msg_invoice_rqd'],
	'PB_INVOICE_WAITING' => $pbLang['msg_inv_waiting'],
	'PB_NEG_STOCK'       => $pbLang['msg_negative_stock'],
	'PB_RECUR_EDIT'      => $pbLang['msg_recur_edit'],
	'PB_SAVE_AS_CLOSED'  => $pbLang['msg_save_as_closed'],
	'PB_SAVE_AS_LINKED'  => $pbLang['msg_save_as_linked'],
	'PB_GL_ASSET_INC'    => $pbLang['bal_increase'],
	'PB_GL_ASSET_DEC'    => $pbLang['bal_decrease'],
    'PB_DBT_CRT_NOT_ZERO'=> $pbLang['err_debits_credits_not_zero'],
    'PLEASE_WAIT'        => $arrLang['please_wait'],
    'SETTINGS'           => $arrLang['settings'],
	'SHIPPING_ESTIMATOR' => $arrLang['shipping_estimator'],
	'STATE'              => $arrLang['address_book_state'],
	'TITLE'              => $arrLang['title'],
	'TOTAL'              => $arrLang['total'],
    'TRASH'              => $arrLang['trash'],
	'TYPE'               => $arrLang['type'],
    'VIEW'               => $arrLang['view']
];
// send it
$output = "var dictionary=".json_encode($dictionary).";\n";
header("Content-type: application/javascript");
header("Content-Length: ".strlen($output));
echo $output;
die;