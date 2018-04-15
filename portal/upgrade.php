<?php
/*
 * PhreeBooks 5 - Bizuno DB Upgrade Script from any version to current release
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
 * @copyright  2008-2018, PhreeSoft Inc.
 * @license    http://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 * @version    2.x Last Update: 2018-03-28
 * @filesource /portal/upgrade.php
 */

namespace bizuno;

ini_set("max_execution_time", 1000000000);

/**
 * Handles the db upgrade for all versions of Bizuno to the current release level
 * @param string $dbVersion - current Bizuno db version 
 */
function bizunoUpgrade($dbVersion='2.0.0')
{
    switch ($dbVersion) {
//        case '1.0.0':
        default:
	}
}
