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
'.html5('frmTicket', $viewData['forms']['frmTicket']).'
'.html5('ticketDate',$viewData['fields']['ticketDate']).'
'.html5('ticketURL', $viewData['fields']['ticketURL']).'
    <table style="width:600px;border-collapse:collapse;margin-left:auto;margin-right:auto;">
        <thead class="panel-header"><tr><th colspan="2" style="width:100px">'.lang('support').'</th></tr></thead>
        <tbody>
            <tr><td colspan="2">'.$viewData['lang']['ticket_desc'].'</td></tr>
            <tr><td>'.lang('reason')                   ."</td><td>".html5('selReason',  $viewData['fields']['selReason'])  .'</td></tr>
            <tr><td>'.lang('Machine')                  ."</td><td>".html5('selMachine', $viewData['fields']['selMachine']) .'</td></tr>
            <tr><td>'.lang('OS')                       ."</td><td>".html5('selOS',      $viewData['fields']['selOS'])      .'</td></tr>
            <tr><td>'.lang('address_book_primary_name')."</td><td>".html5('ticketUser', $viewData['fields']['ticketUser']) .'</td></tr>
            <tr><td>'.lang('email')                    ."</td><td>".html5('ticketEmail',$viewData['fields']['ticketEmail']).'</td></tr>
            <tr><td>'.lang('telephone')                ."</td><td>".html5('ticketPhone',$viewData['fields']['ticketPhone']).'</td></tr>
            <tr><td>'.lang('description')              ."</td><td>".html5('ticketDesc', $viewData['fields']['ticketDesc']) .'</td></tr>
            <tr><td colspan="2">'.$viewData['lang']['ticket_attachment'].'</td></tr>
            <tr><td>'.lang('attachment')               .'</td><td>'.html5('ticketFile', $viewData['fields']['ticketFile']) .'</td></tr>
            <tr><td colspan="2" style="text-align:center">'.html5('btnSubmit', $viewData['fields']['btnSubmit']).'</td></tr>
        </tbody>
    </table>
</form>';
