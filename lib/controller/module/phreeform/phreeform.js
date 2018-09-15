/*
 * JavaScript functions specific to PhreeForm module
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
 * @copyright  2008-2018, PhreeSoft, Inc.
 * @license    http://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 * @version    3.x Last Update: 2018-09-05
 * @filesource /controller/module/phreeform/phreeform-2.0.4.js
 */

var fieldEvent = ''; // tracks index clicked to set field properties

function sessionTables() {
    var table  = '';
    var rowData= jq('#dgTables').edatagrid('getData');
    for (var rowIndex=0; rowIndex<rowData.total; rowIndex++) table += rowData.rows[rowIndex].tablename + ':';
    jsonAction('phreeform/design/getTablesSession', 0, table);
}

function phreeformBody() {
    var rID = jq('input[name=rID]:checked', '#frmPhreeform').val();
    var vars = [], hash;
    var q = document.URL.split('?')[1];
    if (q != undefined) {
        q = q.split('&');
        for (var i = 0; i < q.length; i++) {
            hash = q[i].split('=');
            vars.push(hash[1]);
            vars[hash[0]] = hash[1];
        }
    }
    var params = '&rID='+rID;
    if (vars['xfld']) params += '&xfld='+vars['xfld'];
    if (vars['xcr'])  params += '&xcr=' +vars['xcr'];
    if (vars['xmin']) params += '&xmin='+vars['xmin'];
    if (vars['xmax']) params += '&xmax='+vars['xmax'];
    jq.ajax({ // pull the report body
        url:     bizunoAjax+'&p=phreeform/render/phreeformBody'+params,
        success: function(json) {
            processJson(json);
            if (json.msgBody) {
                bizTextSet('fromName',  json.fromName);
                bizTextSet('fromEmail', json.fromEmail);
                bizTextSet('msgSubject',json.msgSubject);
                jq('#msgBody').val(json.msgBody.replace(/<br \/>/g, "\n"));
            }
        }
    });
}

function importSearch() {
	alert('The feature is not yet available. Please fill out a support ticket to check on development status.');
}
