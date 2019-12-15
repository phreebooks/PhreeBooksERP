<?php
/*
 * PhreeBooks support functions
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
 * @version    3.x Last Update: 2019-11-05
 * @filesource /lib/controller/module/contacts/functions.php
 */

namespace bizuno;

/**
 * Processes a value by format, used in PhreeForm
 * @global array $report - report structure
 * @param mixed $value - value to process
 * @param type $format - what to do with the value
 * @return mixed, returns $value if no formats match otherwise the formatted value
 */
function contactsProcess($value, $format = '')
{
    switch ($format) {
        default:
        case 'qtrNeg0': $range='q0'; break;
        case 'qtrNeg1': $range='q1'; break;
        case 'qtrNeg2': $range='q2'; break;
        case 'qtrNeg3': $range='q3'; break;
        case 'qtrNeg4': $range='q4'; break;
        case 'qtrNeg5': $range='q5'; break;
    }
    return viewContactSales($value, $range);
}

/**
 * Pulls the average sales over the past 12 months of the specified SKU, with cache for multiple hits
 * @param type integer - number of sales, zero if not found or none
 */
function viewContactSales($cID='',$range='q0')
{
    global $report;
    if (empty($GLOBALS['contactSales'])) {
        bizAutoLoad(BIZUNO_LIB."controller/module/phreebooks/functions.php", 'calculatePeriod', 'function');
        $parts  = explode(":", $report->datedefault); // encoded dates, type:start:end
        $period = calculatePeriod($parts[2], false);
        $QtrStrt= $period - (($period - 1) % 3);
        $temp0  = dbGetFiscalDates($QtrStrt);
        $dates['start_date']= $temp0['start_date'];
        $temp1  = dbGetFiscalDates($QtrStrt + 2);
        $dates['end_date']  = localeCalculateDate($temp1['end_date'], 1);
        $qtrNeg0= $dates['start_date'];
        $qtrNeg1= localeCalculateDate($qtrNeg0, 0,  -3);
        $qtrNeg2= localeCalculateDate($qtrNeg1, 0,  -3);
        $qtrNeg3= localeCalculateDate($qtrNeg2, 0,  -3);
        $qtrNeg4= localeCalculateDate($qtrNeg3, 0,  -3);
        $qtrNeg5= localeCalculateDate($qtrNeg4, 0,  -3);
        $fields = ['post_date', 'journal_id', 'total_amount', 'contact_id_b'];
        $filter = "post_date >= '$qtrNeg5' AND post_date < '{$dates['end_date']}' AND journal_id IN (12,13)";
        $rows   = dbGetMulti(BIZUNO_DB_PREFIX.'journal_main', $filter, 'contact_id_b', $fields);
        foreach ($rows as $row) {
            if (empty($GLOBALS['contactSales'][$row['contact_id_b']])) { $GLOBALS['contactSales'][$row['contact_id_b']] = ['q0'=>0,'q1'=>0,'q2'=>0,'q3'=>0,'q4'=>0,'q5'=>0]; }
            if ($row['journal_id']==13)            { $row['total_amount'] = -$row['total_amount']; }

            msgDebug("\ncontact id = {$row['contact_id_b']} and journal_id = {$row['journal_id']} and post_date = {$row['post_date']} and amount = {$row['total_amount']}");

            if     ($row['post_date'] >= $qtrNeg0) { msgDebug(" ... adding qtrNeg0"); $GLOBALS['contactSales'][$row['contact_id_b']]['q0'] += $row['total_amount']; }
            elseif ($row['post_date'] >= $qtrNeg1) { msgDebug(" ... adding qtrNeg1"); $GLOBALS['contactSales'][$row['contact_id_b']]['q1'] += $row['total_amount']; }
            elseif ($row['post_date'] >= $qtrNeg2) { msgDebug(" ... adding qtrNeg2"); $GLOBALS['contactSales'][$row['contact_id_b']]['q2'] += $row['total_amount']; }
            elseif ($row['post_date'] >= $qtrNeg3) { msgDebug(" ... adding qtrNeg3"); $GLOBALS['contactSales'][$row['contact_id_b']]['q3'] += $row['total_amount']; }
            elseif ($row['post_date'] >= $qtrNeg4) { msgDebug(" ... adding qtrNeg4"); $GLOBALS['contactSales'][$row['contact_id_b']]['q4'] += $row['total_amount']; }
            else                                   { msgDebug(" ... adding qtrNeg5"); $GLOBALS['contactSales'][$row['contact_id_b']]['q5'] += $row['total_amount']; }
        }
    }
    return !empty($GLOBALS['contactSales'][$cID][$range]) ? $GLOBALS['contactSales'][$cID][$range] : 0;
}
