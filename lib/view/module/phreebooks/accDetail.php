<?php
/*
 * View template for PhreeBooks Orders main layout, will pull in detail template and datagrid for specific journal
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
 * @version    2.x Last Update: 2017-10-04
 * @filesource /lib/view/module/phreebooks/accDetail.php
 */

namespace bizuno;

htmlToolbar($output, $data, 'tbPhreeBooks');
$output['body'] .= "   ".html5('frmJournal', $data['form']['frmJournal'])."\n";
if (isset($data['itemDGSrc'])) { require ($data['itemDGSrc']); }
if (isset($data['datagrid']['item'])) {
	$output['body'] .= '<div style="clear:both">'."\n";
	htmlDatagrid($output, $data, 'item');
	$output['body'] .= '</div>'."\n";
}

$output['jsBody'][]  = "function preSubmit() {
    jq('#dgJournalItem').edatagrid('saveRow');
    totalUpdate();
    var items = jq('#dgJournalItem').datagrid('getData');
    jq('#item_array').val(JSON.stringify(items));
    if (!formValidate()) return false;
    return true;
}
ajaxForm('frmJournal');\n";
if (isset($data['journal_main']['id']['attr']['value']) && $data['journal_main']['id']['attr']['value']) { // edit
	$output['jsBody'][] = "jq('#spanContactProps_b').show();";
}
$output['jsBody'][] = "jq(document).ready(function() { jq('#dgJournalItem').edatagrid('addRow'); jq('#contactSel_b').next().find('input').focus(); });";
