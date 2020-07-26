<?php
/*
 * Loads the demo data to get started if requested by user
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
 * @copyright  2008-2020, PhreeSoft, Inc.
 * @license    http://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 * @version    4.x Last Update: 2020-04-29
 * @filesource /lib/controller/module/bizuno/install/demodata.php
 */
namespace bizuno;

/**
 * data to generate:
 * Customers, Vendors, Employees, Inventory (various types), price sheets (c and v), reminders
 */
$demodata = [
    ['table0'=> 'contacts', // prefix added in script
     'keys'  => ['key1','key2','etc...'],
     'data'  => [
        ['val0','val1','etc...']]],
    ['table1'=> 'address_book',
     'keys'  => ['key1','key2','etc...'],
     'data'  => [
        ['val0','val1','etc...']]],
];