<?php
/*
 * View tab for PhreeBooks user settings
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
 * @version    2.x Last Update: 2016-12-15

 * @filesource /lib/view/module/phreebooks/tabUsersSettings.php
 */

namespace bizuno;

$output['body'] .= html5('restrict_period',$data['fields']['restrict_period'])."<br />";
$output['body'] .= html5('cash_acct',      $data['fields']['cash_acct'])."<br />";
$output['body'] .= html5('ar_acct',        $data['fields']['ar_acct'])  ."<br />";
$output['body'] .= html5('ap_acct',        $data['fields']['ap_acct']);
