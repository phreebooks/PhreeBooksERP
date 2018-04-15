<?php
/*
 * View for Users details accordion
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
 * @version    2.x Last Update: 2017-12-07
 * @filesource /lib/view/module/bizuno/accUsersDetail.php
 */

namespace bizuno;

htmlToolbar($output, $data, 'tbUsers');
$output['body'] .= "<h1>{$data['pageTitle']}</h1>\n";
$output['body'] .= "   ".html5('frmUsers', $data['form']['frmUsers']);
htmlTabs($output, $data, 'tabUsers');
$output['body'] .= "   </form>\n";
$output['jsBody'][] = "ajaxForm('frmUsers');";
