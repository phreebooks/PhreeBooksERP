<?php
/*
 * View editing the General Ledger chart of accounts
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
 * @version    2.x Last Update: 2017-11-09
 * @filesource /lib/view/module/phreebooks/accGLEdit.php
 */

namespace bizuno;

htmlToolbar($output, $data, 'tbGL');
$output['body'] .= '
'.html5('frmGLEdit', $data['form']['frmGLEdit']).'
  '.html5('gl_account', $data['fields']['gl_account']).'
  '.html5('gl_inactive',$data['fields']['gl_inactive']).'
  '.html5('gl_previous',$data['fields']['gl_previous']).'<br />
  '.html5('gl_desc',    $data['fields']['gl_desc']).'<br />
  '.html5('gl_type',    $data['fields']['gl_type']).'<br />
  '.html5('gl_cur',     $data['fields']['gl_cur']).'<br />
  '.html5('gl_header',  $data['fields']['gl_header']).'
  '.html5('gl_parent',  $data['fields']['gl_parent']).'
</form>';
$output['jsBody'][] = "ajaxForm('frmGLEdit');";
