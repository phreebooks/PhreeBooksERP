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
 * @copyright  2008-2018, PhreeSoft
 * @license    http://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 * @version    2.x Last Update: 2017-01-15

 * @filesource /lib/view/module/bizuno/divBackup.php
 * 
 */

namespace bizuno;

$output['body'] .= '<div style="float:right;width:50%">'."\n";
htmlDatagrid($output, $data, 'backup');
$output['body'] .= "</div>\n";
// company data backup
$output['body'] .= '
<div style="width:50%">
    <fieldset><legend>'.lang('bizuno_backup').'</legend>
    '.html5('frmBackup', $data['form']['frmBackup']).'
        <p>'.$data['lang']['desc_backup'].'</p>
        '.html5('incFiles', $data['fields']['incFiles']).'
        <p><div style="text-align:right">'.html5('btnBackup', $data['fields']['btnBackup']).'</div></p>
    </form>
    </fieldset>
</div>
<div style="width:50%">
    <fieldset><legend>'.$data['lang']['audit_log_backup'].'</legend>
    <p>'.$data['lang']['audit_log_backup_desc'].'</p>
    '.html5('btnAudit', $data['fields']['btnAudit']).'
    <hr />
    '.html5('frmAudit', $data['form']['frmAudit']).'
        <p>'.$data['lang']['desc_audit_log_clean'].'</p>
        '.html5('dateClean', $data['fields']['dateClean']).html5('btnClean', $data['fields']['btnClean']).'
    </form>
    </fieldset>
</div>';
$output['jsBody'][] = "ajaxForm('frmBackup'); ajaxForm('frmAudit');";