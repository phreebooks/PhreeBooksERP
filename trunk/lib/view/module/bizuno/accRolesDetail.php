<?php
/*
 * View for Roles detail accordion
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
 * @version    2.x Last Update: 2018-01-08
 * @filesource /lib/view/module/bizuno/accRolesDetail.php
 */

namespace bizuno;

htmlToolbar($output, $data, 'tbRoles');
$output['body'] .= "
  ".html5('frmRoles',$data['form']['frmRoles'])."
  ".html5('id',      $data['roles']['id'])."
  ".html5('title',   $data['roles']['title'])."
  ".html5('inactive',$data['roles']['inactive'])."<br />
  ".html5('selFill', $data['fields']['selFill'])."<br />
  ".html5('restrict',$data['roles']['restrict'])."\n";
htmlTabs($output, $data, 'tabRoles');
$output['body'] .= "</form>";
$output['jsBody'][] = "
function autoFill() {
	var setting = jq('#selFill').val();
	jq('#frmRoles :input').each(function() { if (jq(this).attr('id').substr(0, 4) == 'sID:') jq(this).val(setting); });
}
ajaxForm('frmRoles');";
