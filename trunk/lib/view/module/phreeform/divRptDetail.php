<?php
/*
 * View for report/form details
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
 * @version    2.x Last Update: 2017-06-01
 * @filesource /lib/view/module/phreeform/divRptDetail.php
 */

namespace bizuno;

htmlToolbar($output, $data['toolbar']['tbDetail']);
$output['body'] .= "
  <h1>".$data['report']['title']."</h1>
  <div>
    ".lang('id').": {$data['report']['id']}<br />
    ".lang('type').": {$data['report']['mime_type']}<br />
    ".lang('create_date').": ".viewFormat($data['report']['create_date'], 'date')."<br />
    ".lang('last_update').': '.viewFormat($data['report']['last_update'], 'date')."<br />
  </div>\n";
