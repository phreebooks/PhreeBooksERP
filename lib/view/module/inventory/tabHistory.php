<?php
/*
 * 2008-2018 PhreeSoft
 *
 * 
 *
 * PHP version 5

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
 * @version    2.0 Last Update: 2017-09-06
 * @filesource /lib/view/module/inventory/tabHistory.php
 */

namespace bizuno;

$hide_cost = validateSecurity('phreebooks', "j6_mgr", 1, false) ? false : true;

$output['body'] .= "
<fieldset><legend>".lang('history')."</legend>
    <p>".html5('', $data['history']['create']) ."<br />"
        .html5('', $data['history']['update']) ."<br />"
        .html5('', $data['history']['journal']).'
    </p>
</fieldset>
<table style="border-collapse:collapse;width:100%">
    <tr>
        <td valign="top" width="50%">
            <table id="dgPurchaseOrders"></table>
            <table id="dgSalesOrders"></table>
            <table style="width:100%">
                <thead class="panel-header"><tr><th>&nbsp;</th><th>'.lang('journal_main_journal_id_6')."</th><th>".lang('journal_main_journal_id_12')."</th></tr></thead>
                <tbody>
                    <tr><td>".$data['lang']['01month'].'</td><td style="text-align:center;">'.$data['history']['01purch'].'</td><td style="text-align:center;">'.$data['history']['01sales']."</td></tr>
                    <tr><td>".$data['lang']['03month'].'</td><td style="text-align:center;">'.$data['history']['03purch'].'</td><td style="text-align:center;">'.$data['history']['03sales']."</td></tr>
                    <tr><td>".$data['lang']['06month'].'</td><td style="text-align:center;">'.$data['history']['06purch'].'</td><td style="text-align:center;">'.$data['history']['06sales']."</td></tr>
                    <tr><td>".$data['lang']['12month'].'</td><td style="text-align:center;">'.$data['history']['12purch'].'</td><td style="text-align:center;">'.$data['history']['12sales'].'</td></tr>
                </tbody>
            </table>
        </td>
        <td valign="top" width="25%">
            <table id="dgPurchases"></table>
        </td>
        <td valign="top" width="25%">
            <table id="dgSales"></table>
        </td>
    </tr>
</table>';
if ($data['history']['id']) { $output['body'] .= lang('id').': '.$data['history']['id']; }
$output['body'] .= "<!-- EOF tab ".lang('history')." -->\n";

$js = "
    var dataPO = " .json_encode($data['history']['open_po']).";
    var dataSO = " .json_encode($data['history']['open_so']).";
    var dataJ6 = " .json_encode($data['history']['purchases']).";
    var dataJ12 = ".json_encode($data['history']['sales']).";
    function dgPurchaseOrdersFormatter(value, row, index) {
      var text = '<span type=\"span\" title=\"Edit\" onClick=\"tabOpen(\'_blank\', \'phreebooks/main/manager&rID=idTBD\');\" class=\"icon-edit\" style=\"border:0;display:inline-block;vertical-align:middle;height:16px;min-width:16px;cursor:pointer\"></span>&nbsp;';
      text += '<span type=\"span\" title=\"Fill\" onClick=\"tabOpen(\'_blank\', \'phreebooks/main/manager&rID=idTBD&jID=6&bizAction=inv\');\" class=\"icon-purchase\" style=\"border:0;display:inline-block;vertical-align:middle;height:16px;min-width:16px;cursor:pointer\"></span>&nbsp;';
      text  = text.replace(/idTBD/g, row.id);
      return text;
    }
    jq('#dgPurchaseOrders').datagrid({
        title:'".lang('open_journal_4')."',
        data:dataPO,
        pagination:false,
        columns:[[
            {field:'id',hidden:true},
            {field:'action',     hidden:".($hide_cost?'true':'false').",width:50,formatter:function(value,row,index){ return dgPurchaseOrdersFormatter(value,row,index); } },
            {field:'invoice_num',title:'".jsLang('journal_main_invoice_num_4')."',width:100},";
if (sizeof(getModuleCache('extStores', 'properties'))) {
    $js .= "\n            {field:'store_id',   title:'".jsLang('contacts_short_name_b')."',width:100},";
}
$js .= "
            {field:'post_date',  title:'".jsLang('post_date')."',width:100},
            {field:'qty',        title:'".jsLang('balance')."',width:100, align:'center'},
            {field:'date_1',     title:'".jsLang('journal_item_date_1',4)."',width:100}
        ]]
    });
    function dgSalesOrdersFormatter(value, row, index) {
      var text = '<span type=\"span\" title=\"Edit\" onClick=\"tabOpen(\'_blank\', \'phreebooks/main/manager&rID=idTBD\');\" class=\"icon-edit\" style=\"border:0;display:inline-block;vertical-align:middle;height:16px;min-width:16px;cursor:pointer\"></span>&nbsp;';
      text += '<span type=\"span\" title=\"Fill\" onClick=\"tabOpen(\'_blank\', \'phreebooks/main/manager&rID=idTBD&jID=12&bizAction=inv\');\" class=\"icon-sales\" style=\"border:0;display:inline-block;vertical-align:middle;height:16px;min-width:16px;cursor:pointer\"></span>&nbsp;';
      text  = text.replace(/idTBD/g, row.id);
      return text;
    }
    jq('#dgSalesOrders').datagrid({
        title:'".lang('open_journal_10')."',
        data:dataSO,
        pagination:false,
        columns:[[
            {field:'action',     width:50,formatter:function(value,row,index){ return dgSalesOrdersFormatter(value,row,index); } },
            {field:'invoice_num',title:'".jsLang('journal_main_invoice_num_10')."',width:100},";
if (sizeof(getModuleCache('extStores', 'properties'))) {
    $js .= "\n            {field:'store_id',   title:'".jsLang('contacts_short_name_b')."',width:100},";
}
$js .= "
            {field:'post_date',  title:'".jsLang('post_date')."',width:100},
            {field:'qty',        title:'".jsLang('balance')."',width:100, align:'center'},
            {field:'date_1',     title:'".jsLang('journal_item_date_1',10)."',width:100}
        ]]
    });
    jq('#dgPurchases').datagrid({
        title:'".sprintf(lang('tbd_history'), lang('journal_main_journal_id', '6'))."',
        data:dataJ6,
        pagination:false,
        columns:[[
            {field:'year', title:'".jsLang('year')."', width:80, align:'center'},
            {field:'month',title:'".jsLang('month')."',width:80, align:'center'},
            {field:'qty',  title:'".jsLang('qty')."',  width:80, align:'center'},
            {field:'total',title:'".jsLang('cost')."', width:120,align:'right',hidden:".($hide_cost?'true':'false').",formatter:function(value) { return formatCurrency(value); } }
        ]]
    });
    jq('#dgSales').datagrid({
        title:'".sprintf(lang('tbd_history'), lang('journal_main_journal_id', '12'))."',
        data:dataJ12,
        pagination:false,
        columns:[[
            {field:'year', title:'".jsLang('year')."', width:80, align:'center'},
            {field:'month',title:'".jsLang('month')."',width:80, align:'center'},
            {field:'qty',  title:'".jsLang('qty')."',  width:80, align:'center'},
            {field:'total',title:'".jsLang('sales')."',width:120,align:'right',formatter:function(value) { return formatCurrency(value); } }
        ]]
    });";
$output['jsBody'][] = $js;
