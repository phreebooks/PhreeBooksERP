<?php
/*
 * Functions to convert currency to words
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
 * @license    http://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 * @version    3.x Last Update: 2018-09-05
 * @filesource /locale/en_US/functions.php
 */

namespace bizuno;

// The below functions are used to convert a number to language for USD (primarily for checks)
function viewCurrencyToWords($number)
{
    $number   = round($number, 2);
    $position = array('', ' Thousand', ' Million', ' Billion');
    $dollars  = intval($number);
    $cents    = round(($number - $dollars) * 100);
    if (strlen($cents) == 1) { $cents = '0'.$cents; }
    if ($dollars < 1) { $output = 'Zero'; }
      else { $output = build_1000_words($dollars, $position); }
    return strtoupper("$output ".lang('dollars').' '.lang('and')." $cents/100");
}

function build_1000_words($number, $position)
{
    $output   = '';
    $suffix   = array_shift($position);
    $tens     = $number % 100;
    $number   = intval($number / 100);
    $hundreds = $number % 10;
    $number   = intval($number / 10);
    if ($number >= 1) { $output = build_1000_words($number, $position); }
    switch ($hundreds) {
        case 1: $output .= ' One Hundred';   break;
        case 2: $output .= ' Two Hundred';   break;
        case 3: $output .= ' Three Hundred'; break;
        case 4: $output .= ' Four Hundred';  break;
        case 5: $output .= ' Five Hundred';  break;
        case 6: $output .= ' Six Hundred';   break;
        case 7: $output .= ' Seven Hundred'; break;
        case 8: $output .= ' Eight Hundred'; break;
        case 9: $output .= ' Nine Hundred';  break;
    }
    $output .= build_100_words($tens);
    return $output . $suffix;
}

function build_100_words($number)
{
    if ($number > 9 && $number < 20) {
        switch ($number) {
            case 10: return ' Ten';
            case 11: return ' Eleven';
            case 12: return ' Twelve';
            case 13: return ' Thirteen';
            case 14: return ' Fourteen';
            case 15: return ' Fifteen';
            case 16: return ' Sixteen';
            case 17: return ' Seventeen';
            case 18: return ' Eighteen';
            case 19: return ' Ninteen';
        }
    }
    $output = '';
    $tens = intval($number / 10);
    switch ($tens) {
        case 2: $output .= ' Twenty'; break;
        case 3: $output .= ' Thirty'; break;
        case 4: $output .= ' Forty'; break;
        case 5: $output .= ' Fifty'; break;
        case 6: $output .= ' Sixty'; break;
        case 7: $output .= ' Seventy'; break;
        case 8: $output .= ' Eighty'; break;
        case 9: $output .= ' Ninety'; break;
    }
    $ones = $number % 10;
    switch ($ones) {
        case 1: $output .= ($output ? '-' : ' ') . 'One';   break;
        case 2: $output .= ($output ? '-' : ' ') . 'Two';   break;
        case 3: $output .= ($output ? '-' : ' ') . 'Three'; break;
        case 4: $output .= ($output ? '-' : ' ') . 'Four';  break;
        case 5: $output .= ($output ? '-' : ' ') . 'Five';  break;
        case 6: $output .= ($output ? '-' : ' ') . 'Six';   break;
        case 7: $output .= ($output ? '-' : ' ') . 'Seven'; break;
        case 8: $output .= ($output ? '-' : ' ') . 'Eight'; break;
        case 9: $output .= ($output ? '-' : ' ') . 'Nine';  break;
    }
    return $output;
}
