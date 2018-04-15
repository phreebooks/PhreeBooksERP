<?php
/*
 * View for Contacts history tab
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
 * @version    2.x Last Update: 2017-08-11

 * @filesource /lib/view/module/contacts/tabHistory.php
 */

namespace bizuno;

$output['body'] .= html5('first_date', $data['fields']['first_date'])." ";
$output['body'] .= html5('last_update', $data['fields']['last_update'])."<br />";
$output['body'] .= '  <div style="float:right;width:50%;">'."\n";
htmlDatagrid($output, $data, 'inv');
$output['body'] .= "  </div>\n";
$output['body'] .= '  <div style="width:49%;">'."\n";
htmlDatagrid($output, $data, 'po_so');
$output['body'] .= "  </div>\n";

