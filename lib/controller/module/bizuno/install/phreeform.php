<?php
/*
 * Phreeform installation structure
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
 * @version    3.x Last Update: 2019-01-23
 * @filesource /lib/controller/module/bizuno/install/phreeform.php
 */

$phreeform = [
    'misc' => ['title'=>'misc',      'folders'=>[
        'misc:rpt' => ['type'=>'dir', 'title'=>'reports'],
        'misc:misc'=> ['type'=>'dir', 'title'=>'forms']]],
    'bnk'  => ['title'=>'banking',   'folders'=>[
        'bnk:rpt'  => ['type'=>'dir', 'title'=>'reports'],
        'bnk:j18'  => ['type'=>'dir', 'title'=>'bank_deposit'],
        'bnk:j20'  => ['type'=>'dir', 'title'=>'bank_check']]],
    'cust' => ['title'=>'customers', 'folders'=>[
        'cust:rpt' => ['type'=>'dir', 'title'=>'reports'],
        'cust:j9'  => ['type'=>'dir', 'title'=>'journal_main_journal_id_9'],
        'cust:j10' => ['type'=>'dir', 'title'=>'journal_main_journal_id_10'],
        'cust:j12' => ['type'=>'dir', 'title'=>'journal_main_journal_id_12'],
        'cust:j13' => ['type'=>'dir', 'title'=>'journal_main_journal_id_13'],
        'cust:j19' => ['type'=>'dir', 'title'=>'sales_receipt'],
        'cust:lblc'=> ['type'=>'dir', 'title'=>'label'],
        'cust:ltr' => ['type'=>'dir', 'title'=>'letter'],
        'cust:stmt'=> ['type'=>'dir', 'title'=>'statement']]],
    'gl'   => ['title'=>'general_ledger', 'folders'=>[
        'gl:rpt'   => ['type'=>'dir', 'title'=>'reports', 'type'=>'dir']]],
    'hr'   => ['title'=>'employees', 'folders'=>[
        'hr:rpt'   => ['type'=>'dir', 'title'=>'reports']]],
    'inv'  => ['title'=>'inventory', 'folders'=>[
        'inv:rpt'  => ['type'=>'dir', 'title'=>'reports']]],
    'vend' => ['title'=>'vendors',   'folders'=>[
        'vend:rpt' => ['type'=>'dir', 'title'=>'reports'],
        'vend:j3'  => ['type'=>'dir', 'title'=>'journal_main_journal_id_3'],
        'vend:j4'  => ['type'=>'dir', 'title'=>'journal_main_journal_id_4'],
        'vend:j6'  => ['type'=>'dir', 'title'=>'journal_main_journal_id_6'],
        'vend:j7'  => ['type'=>'dir', 'title'=>'journal_main_journal_id_7'],
        'vend:lblv'=> ['type'=>'dir', 'title'=>'label'],
        'vend:stmt'=> ['type'=>'dir', 'title'=>'statement']]]];
