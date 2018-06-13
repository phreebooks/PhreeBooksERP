<?php
/*
 * View for Bizuno backup page
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
 * @version    2.x Last Update: 2018-06-12
 * @filesource /lib/view/module/bizuno/divBackup.php
 */

namespace bizuno;

$output['body'] .= '<div style="float:right;width:50%">'."\n";
htmlDatagrid($output, $viewData, 'backup');
$output['body'] .= "</div>\n";
// company data backup
$output['body'] .= '
<div style="width:50%">
    <fieldset><legend>'.lang('bizuno_backup').'</legend>
    '.html5('frmBackup', $viewData['forms']['frmBackup']).'
        <p>'.$viewData['lang']['desc_backup'].'</p>
        '.html5('incFiles', $viewData['fields']['incFiles']).'
        <p><div style="text-align:right">'.html5('btnBackup', $viewData['fields']['btnBackup']).'</div></p>
    </form>
    </fieldset>
</div>
<div style="width:50%">
    <fieldset><legend>'.$viewData['lang']['audit_log_backup'].'</legend>
    <p>'.$viewData['lang']['audit_log_backup_desc'].'</p>
    '.html5('btnAudit', $viewData['fields']['btnAudit']).'
    <hr />
    '.html5('frmAudit', $viewData['forms']['frmAudit']).'
        <p>'.$viewData['lang']['desc_audit_log_clean'].'</p>
        '.html5('dateClean', $viewData['fields']['dateClean']).html5('btnClean', $viewData['fields']['btnClean']).'
    </form>
    </fieldset>
</div>';
$output['jsBody'][] = "ajaxForm('frmBackup'); ajaxForm('frmAudit');";