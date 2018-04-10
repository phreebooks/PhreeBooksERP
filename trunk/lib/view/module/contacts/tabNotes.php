<?php
/*
 * View for Contact Notes tab
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

 * @filesource /lib/view/module/contacts/tabNotes.php
 */

namespace bizuno;

// pull the value
$suffix = 'm';
$structure = $data['fields']['address_book'];
$entries = [];
if (isset($data['values']['address_book'])) {
    foreach ($data['values']['address_book'] as $address) { if ($suffix == $address['type']) { $entries[] = $address; } }
    if (sizeof($entries) > 0 && $suffix == 'm') { foreach ($address as $field => $value) { $structure[$field]['attr']['value'] = $entries[0][$field]; } }
}
// fix the notes width
$structure['notes']['attr']['cols'] = 60;
$structure['notes']['attr']['rows'] = 20;
unset($structure['notes']['label']);
$output['body'] .= '
<div style="float:right;width:50%">
    <table style="border-collapse:collapse;width:100%">
        <thead  class="panel-header">
            <tr><th colspan="3">'.lang('contacts_log').'</th></tr>
            <tr><th>'.lang('contacts_log_entered_by').'</th><th>'.lang('date').'</th><th>'.lang('action').'</th></tr>
        </thead>
        <tbody>
            <tr>
                <td align="center">'.html5('crm_rep_id', $data['crm_rep_id']).'</td>
                <td align="center">'.html5('crm_date',   $data['crm_date'])  .'</td>
                <td align="center">'.html5('crm_action', $data['crm_action']).'</td>
            </tr>
            <tr>
                <td colspan="3" align="center">'.html5('crm_note', $data['crm_note']).'</td>
            </tr>
        </tbody>
    </table>'."\n";
htmlDatagrid($output, $data, 'log');
$output['body'] .= '
</div>
<div style="width:50%">'.html5('notes'.$suffix, $structure['notes']).'</div>';
