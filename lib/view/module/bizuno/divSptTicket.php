<?php
/*
 * View for Support Ticket form
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
 * @version    2.x Last Update: 2018-01-12
 * @filesource /lib/view/module/bizuno/divSptTicket.php
 */

namespace bizuno;

$output['body'] .= '
<!-- ticketMain -->
<p>&nbsp;</p>
<p>&nbsp;</p>
'.html5('frmTicket', $data['form']['frmTicket']).'
'.html5('ticketDate',$data['fields']['ticketDate']).'
'.html5('ticketURL', $data['fields']['ticketURL']).'
    <table style="width:600px;border-collapse:collapse;margin-left:auto;margin-right:auto;">
        <thead class="panel-header"><tr><th colspan="2" style="width:100px">'.lang('support').'</th></tr></thead>
        <tbody>
            <tr><td colspan="2">'.$data['lang']['ticket_desc'].'</td></tr>
            <tr><td>'.lang('reason')                   ."</td><td>".html5('selReason',  $data['fields']['selReason'])  .'</td></tr>
            <tr><td>'.lang('Machine')                  ."</td><td>".html5('selMachine', $data['fields']['selMachine']) .'</td></tr>
            <tr><td>'.lang('OS')                       ."</td><td>".html5('selOS',      $data['fields']['selOS'])      .'</td></tr>
            <tr><td>'.lang('address_book_primary_name')."</td><td>".html5('ticketUser', $data['fields']['ticketUser']) .'</td></tr>
            <tr><td>'.lang('email')                    ."</td><td>".html5('ticketEmail',$data['fields']['ticketEmail']).'</td></tr>
            <tr><td>'.lang('telephone')                ."</td><td>".html5('ticketPhone',$data['fields']['ticketPhone']).'</td></tr>
            <tr><td>'.lang('description')              ."</td><td>".html5('ticketDesc', $data['fields']['ticketDesc']) .'</td></tr>
            <tr><td colspan="2">'.$data['lang']['ticket_attachment'].'</td></tr>
            <tr><td>'.lang('attachment')               .'</td><td>'.html5('ticketFile', $data['fields']['ticketFile']) .'</td></tr>
            <tr><td colspan="2" style="text-align:center">'.html5('btnSubmit', $data['fields']['btnSubmit']).'</td></tr>
        </tbody>
    </table>
</form>';
