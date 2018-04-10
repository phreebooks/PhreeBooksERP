<?php
/*
 * View for Contacts main page
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
 * @version    2.x Last Update: 2017-08-06
 * @filesource /lib/view/module/contacts/accDetail.php
 */

namespace bizuno;

htmlToolbar($output, $data, 'tbContacts');
$output['body'] .= "<h1>".$data['title']."</h1>\n";
if (isset($data['form']['frmContact'])) { $output['body'] .= "   ".html5('frmContact', $data['form']['frmContact'])."\n"; }
htmlTabs($output, $data, 'tabContacts');
if (isset($data['form']['frmContact'])) { $output['body'] .= "   </form>\n"; }
$output['jsReady'][] = "ajaxForm('frmContact');";
