/*
 * Javascipt functions to handle operations specific to the PhreeBooks module

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
 * @copyright  2008-2019, PhreeSoft, Inc.
 * @license    http://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 * @version    3.x Last Update: 2019-04-09
 * @filesource /lib/controller/module/phreebooks/phreebooks.js
 */

var cogs_types  = ['si','sr','ms','mi','ma','sa'];
var inventory   = new Array();
var arrPmtMethod= new Array();
var curIndex    = undefined;
var deleteRow   = false;
var rowAutoAdd  = true;
var discountType= 'amt';
var feeType     = 'amt';
var no_recurse  = false;

//*******************  Orders  ************************************
function formValidate() { // check form
    var error  = false;
    var message= "";
    var notes  = '';
    // With edit of order and recur, ask if roll through future entries or only this entry
    if (jq('#id').val() > 0 && parseInt(jq('#recur_id').val()) > 0) {
        if (confirm(jq.i18n('PB_RECUR_EDIT'))) { jq('#recur_frequency').val('1'); }
        else { jq('#recur_frequency').val('0'); }
    }
    switch (bizDefaults.phreebooks.journalID) {
        case  2:
            var balance = cleanCurrency(jq('#total_balance').val());
            if (balance) {
                error = true;
                message += jq.i18n('PB_DBT_CRT_NOT_ZERO')+"\n";
            }
            break;
        case  6:
        case  7: // Check for invoice_num exists with a recurring entry
            if (!jq('#invoice_num').val() && jq('#recur_id').val()>0) {
                message += jq.i18n('PB_INVOICE_RQD')+"\n";
                error = true;
            }
            // validate that for purchases, either the waiting box needs to be checked or an invoice number needs to be entered
            if (!jq('#invoice_num').val() && !jq('#waiting').is(':checked')) {
                message += jq.i18n('PB_INVOICE_WAITING')+"\n";
                error = true;
            }
            break;
        case  9:
        case 10:
        case 12: //validate item status (inactive, out of stock [SO] etc.)
            var rowData = jq('#dgJournalItem').edatagrid('getData');
            for (var rowIndex=0; rowIndex<rowData.total; rowIndex++) {
                var sku   = parseFloat(rowData.rows[rowIndex].sku);
                var qty   = parseFloat(rowData.rows[rowIndex].qty);
                var stock = parseFloat(rowData.rows[rowIndex].qty_stock);
                var track = jq.inArray(rowData.rows[rowIndex].inventory_type, cogs_types);
                if (rowData.rows[rowIndex].sku != '' && qty>stock && track>-1) {
                    notes+= jq.i18n('PB_NEG_STOCK')+"\n";
                    notes = notes.replace(/%s/g, rowData.rows[rowIndex].sku);
                    notes = notes.replace(/%i/g, stock);
                }
            }
            break;
        case  3:
        case  4:
        case 13:
        case 18:
        case 20:
        default:
    }
    if (error) { alert(message);    return false; }
    if (notes) if (!confirm(notes)) return false;
    if (!jq('#frmJournal').form('validate')) return false;
    jq('body').addClass('loading');
    return true;
}

/**
 * Sets the referrer to apply a credit from the manager
 * @param {integer} jID - Journal ID of the row
 * @param {integer} cID - Contact ID of the row
 * @param {integer} iID - Record ID of the row
 * @returns NULL
 */
function setCrJournal(jID, cID, iID) {
//    alert('received jID = '+jID+' and cID = '+cID+' and iID = '+iID);
    switch (jID) {
        case  6: jDest = 7;  break;
        default:
        case 12: jDest = 13; break;
    }
    journalEdit(jDest, 0, cID, 'inv', 'journal:'+jID, iID);
}

/**
 * Sets the referrer to apply a payment from the manager
 * @param {integer} jID - Journal ID of the row
 * @param {integer} cID - Contact ID of the row
 * @param {integer} iID - Record ID of the row
 * @returns NULL
 */

function setPmtJournal(jID, cID, iID) {
//    alert('received jID = '+jID+' and cID = '+cID+' and iID = '+iID);
    switch (jID) {
        case  6: jDest = 20; break;
        case  7: jDest = 17; break;
        default:
        case 12: jDest = 18; break;
        case 13: jDest = 22; break;
    }
    journalEdit(jDest, 0, cID, 'inv', 'journal:'+jID, iID);
}

function journalEdit(jID, rID, cID, action, xAction, iID) {
    if (typeof cID    == 'undefined') cID    = 0;
    if (typeof iID    == 'undefined') iID    = 0;
    if (typeof action == 'undefined') action = '';
    if (typeof xAction== 'undefined') xAction= '';
//alert('jID = '+jID+' and rID = '+rID+'and cID = '+cID+' and action = '+action+' and xAction = '+xAction);
    var xVars = '&jID='+jID+'&rID='+rID;
    if (cID) xVars += '&cID='+cID;
    if (iID) xVars += '&iID='+iID;
    if (action) xVars  += '&bizAction='+action;
    if (xAction) xVars += '&xAction='+xAction;
    var title = jq('#j'+jID+'_mgr').text();
    document.title = title;
    var p = jq('#accJournal').accordion('getPanel', 1);
    if (p) {
        p.panel('setTitle',title);
        jq('#dgPhreeBooks').datagrid('loaded');
        jq('#divJournalDetail').panel({href:bizunoAjax+'&p=phreebooks/main/edit'+xVars});
        jq('#accJournal').accordion('select', title);
    }
}

function phreebooksSelectAll() {
    var rowData= jq('#dgJournalItem').datagrid('getData');
    for (var rowIndex=0; rowIndex<rowData.total; rowIndex++) {
        var val  = parseFloat(rowData.rows[rowIndex].bal);
        var price= parseFloat(rowData.rows[rowIndex].price);
        if (isNaN(val)) {
            rowData.rows[rowIndex].qty = '';
        } else {
            rowData.rows[rowIndex].qty = val;
            rowData.rows[rowIndex].total = val * price;
        }
    }
    jq('#dgJournalItem').datagrid('loadData', rowData);
}

/**
 * This function either makes a copy of an existing SO/Invoice to the quote journal OR
 * saves to a journal other than the one the current form is set to.
 */
function saveAction(action, newJID) {
    var jID = jq('#journal_id').val();
    var partialInProgress = false;
    if (jq('#id').val()) { // if partially filled, deny save
        var rowData = jq('#dgJournalItem').edatagrid('getData');
        for (var rowIndex=0; rowIndex<rowData.total; rowIndex++) {
            var bal = parseFloat(rowData.rows[rowIndex].bal);
            if (bal) partialInProgress = true;
            if (action == 'saveAs') {
                jq('#dgJournalItem').edatagrid('getRows')[rowIndex]['id'] = 0;
                jq('#dgJournalItem').edatagrid('getRows')[rowIndex]['reconciled'] = 0;
            } // need to create new record
        }
    }
    if (partialInProgress) return alert(jq.i18n('PB_SAVE_AS_LINKED'));
    if (parseFloat(jq('#so_po_ref_id').val()) || parseFloat(jq('#recur_id').val('')) || parseFloat(jq('#recur_frequency').val(''))) {
        return alert(jq.i18n('PB_SAVE_AS_LINKED'));
    }
    if (bizCheckBoxGet('closed')) return alert(jq.i18n('PB_SAVE_AS_CLOSED'));
    if ((jID=='3' || jID=='4' || jID=='6') && (newJID=='3' || newJID=='4' || newJID=='6')) {
        jq('#journal_id').val(newJID);
        bizTextSet('invoice_num', ''); // force the next ref ID from current_status for the journal saved/moved to
    } else if ((jID=='9' || jID=='10' || jID=='12') && (newJID=='9' || newJID=='10' || newJID=='12')) {
        if (newJID=='12') { jq('#waiting').val('1'); } // force the unshipped flag to be set
        jq('#journal_id').val(newJID);
        bizTextSet('invoice_num', ''); // force the next ref ID from current_status for the journal saved/moved to
    } else if (newJID=='2') {
        jq('#journal_id').val(newJID);
    } else alert('Invalid call to Save As...!');
    if (action == 'saveAs') {
        jq('#id').val('0'); // make sure this is posted as a new record
        for (var i=0; i < totalsMethods.length; i++) { // clear the id field for each total method
            var tName = totalsMethods[i];
            jq('#totals_'+tName+'_id').val('0');
        }
    }
    // clear the waiting flag for the following:
    if (newJID=='2' || newJID=='3' || newJID=='4' || newJID=='9' || newJID=='10') { jq('#waiting').val('0'); }
    jq('#frmJournal').submit();
}

/************************** general ledger ********************************************************/
function setPointer(glAcct, debit, credit) {
    var found = false;
    var arrow = '';
    for (var i=0; i < bizDefaults.glAccounts.rows.length; i++) {
        if (bizDefaults.glAccounts.rows[i]['id'] == glAcct) {
            found = true;
            if (debit  &&  bizDefaults.glAccounts.rows[i]['asset']) arrow = 'inc';
            if (debit  && !bizDefaults.glAccounts.rows[i]['asset']) arrow = 'dec';
            if (credit &&  bizDefaults.glAccounts.rows[i]['asset']) arrow = 'dec';
            if (credit && !bizDefaults.glAccounts.rows[i]['asset']) arrow = 'inc';
            break;
        }
    }
    incdec = '';
    if (found && arrow=='inc')      { incdec = String.fromCharCode(8679)+' '+jq.i18n('PB_GL_ASSET_INC'); }
    else if (found && arrow=='dec') { incdec = String.fromCharCode(8681)+' '+jq.i18n('PB_GL_ASSET_DEC'); }
    var notesEditor = jq('#dgJournalItem').datagrid('getEditor', {index:curIndex,field:'notes'});
    jq(notesEditor.target).val(incdec);
}

function glEditing(rowIndex) {
    curIndex = rowIndex;
    jq('#dgJournalItem').edatagrid('getRows')[rowIndex]['qty'] = 1;
    var glEditor = jq('#dgJournalItem').datagrid('getEditor', {index:rowIndex,field:'gl_account'});
    jq(glEditor.target).combogrid('attachEvent', { event: 'onSelect', handler: function(idx,row){ glCalc('gl', row.id); } });
}

function glCalc(action, glAcct) {
    var glEditor    = jq('#dgJournalItem').datagrid('getEditor', {index:curIndex,field:'gl_account'});
    var descEditor  = jq('#dgJournalItem').datagrid('getEditor', {index:curIndex,field:'description'});
    var debitEditor = jq('#dgJournalItem').datagrid('getEditor', {index:curIndex,field:'debit_amount'});
    var creditEditor= jq('#dgJournalItem').datagrid('getEditor', {index:curIndex,field:'credit_amount'});
    if (!glEditor || !debitEditor || !creditEditor) return; // all editors are not active
    if (typeof glAcct != 'undefined') {
        if (glAcct != jq('#dgJournalItem').edatagrid('getRows')[curIndex]['gl_account']) {
            jq('#dgJournalItem').edatagrid('getRows')[curIndex]['gl_account'] = glAcct;
            jq(glEditor.target).combogrid('setValue', glAcct);
        }
    } else {
        glAcct  = jq(glEditor.target).combogrid('getValue');
    }
    var newDesc = jq(descEditor.target).val();
    var newDebit= debitEditor.target.val();
    if (isNaN(newDebit))  newDebit = 0;
    var newCredit= creditEditor.target.val();
    if (isNaN(newCredit)) newCredit = 0;
//  alert('glCalc action = '+action+' and glAcct = '+glAcct+' and newDebit = '+newDebit+' and newCredit = '+newCredit);
    if (!glAcct && !newDebit && !newCredit) return; // empty row
    switch (action) {
    default:
        case 'gl': return setPointer(glAcct, newDebit, newCredit);
        case 'debit':
            setDgEdVal(curIndex, 'debit_amount',  newDebit);
            if (newDebit != 0) {
                newCredit = 0;
                setDgEdVal(curIndex, 'credit_amount', 0);
            }
            break;
        case 'credit':
            setDgEdVal(curIndex, 'credit_amount', newCredit);
            if (newCredit != 0) {
                newDebit = 0;
                setDgEdVal(curIndex, 'debit_amount', 0);
            }
            break;
    }
    setPointer(glAcct, newDebit, newCredit);
    totalUpdate();
    if (rowAutoAdd && jq('#dgJournalItem').edatagrid('getRows').length == (curIndex+1)) { // auto add new row
        rowAutoAdd = false; // disable auto add to prevent infinite loop
        jq('#dgJournalItem').edatagrid('addRow');
        setDgEdVal(curIndex, 'debit_amount',  newCredit);
        setDgEdVal(curIndex, 'credit_amount', newDebit);
        var descEditor  = jq('#dgJournalItem').datagrid('getEditor', {index:curIndex,field:'description'});
        jq(descEditor.target).val(newDesc);
    }
}

function totalsCurrency(newISO, oldISO) {
    bizNumSet('currency_rate', bizDefaults.currency.currencies[newISO].value);
    var len = parseInt(bizDefaults.currency.currencies[newISO].dec_len);
    var sep = bizDefaults.currency.currencies[newISO].sep;
    var dec = bizDefaults.currency.currencies[newISO].dec_pt;
    var rate= bizDefaults.currency.currencies[newISO].value / bizDefaults.currency.currencies[oldISO].value;
    var pfx = bizDefaults.currency.currencies[newISO].prefix ? bizDefaults.currency.currencies[newISO].prefix+' ' : '';
    var sfx = bizDefaults.currency.currencies[newISO].suffix ? ' '+bizDefaults.currency.currencies[newISO].suffix : '';
    // convert the totals fields
    var fldsTotals = ['totals_subtotal','totals_debit','totals_credit','total_balance','totals_balanceBeg','totals_balanceEnd',
        'totals_discount','totals_tax_other','totals_tax_order','totals_tax_item','totals_fee_order','freight','total_amount'];
    for (var i=0; i<fldsTotals.length; i++) {
        if (jq('#'+fldsTotals[i])) {
            jq('#'+fldsTotals[i]).numberbox({decimalSeparator:dec,groupSeparator:sep,precision:len,prefix:pfx,suffix:sfx});
            bizNumSet(fldsTotals[i], jq('#'+fldsTotals[i]).val() * rate);
        }
    }
    // Fix the item table
    dgFields = ['amount','price','discount','total','debit_amount','credit_amount'];
    for (var i=0; i<dgFields.length; i++) { setDgEdCur(dgFields[i], newISO); }
    var rowData = jq('#dgJournalItem').edatagrid('getData');
    for (var rowIndex=0; rowIndex<rowData.total; rowIndex++) {
        for (var i=0; i < dgFields.length; i++) {
            newVal = rowData.rows[rowIndex][dgFields[i]] * rate;
            if (isNaN(newVal)) newVal = 0;
            jq('#dgJournalItem').edatagrid('getRows')[rowIndex][dgFields[i]] = newVal;
            var ed = jq('#dgJournalItem').datagrid('getEditor', {index:rowIndex,field:dgFields[i]});
            if (ed) {
                jq(ed.target).numberbox( {decimalSeparator:dec,groupSeparator:sep,precision:len,prefix:pfx,suffix:sfx});
                setDgEdVal(curIndex, dgFields[i], newVal);
            }
        }
        jq('#dgJournalItem').datagrid('refreshRow', rowIndex);
    }
}

/**************************** datagrid support **************************************/
function setDgEdCur(column, newISO) {
    var opts = jq('#dgJournalItem').datagrid('getColumnOption', column);
    if (opts == null || typeof opts.editor == 'undefined') { return; }
    if (!opts.editor) { return; }
    opts.editor.options.decimalSeparator = bizDefaults.currency.currencies[newISO].dec_pt;
    opts.editor.options.groupSeparator = bizDefaults.currency.currencies[newISO].sep;
    opts.editor.options.prefix = bizDefaults.currency.currencies[newISO].prefix ? bizDefaults.currency.currencies[newISO].prefix+' ' : '';
    opts.editor.options.suffix = bizDefaults.currency.currencies[newISO].suffix ? ' '+bizDefaults.currency.currencies[newISO].suffix : '';
}

function setDgEdVal(idx, fld, amount) {
    if (isNaN(amount)) { return; }
    var ed = jq('#dgJournalItem').edatagrid('getEditor', {index:idx,field:fld});
    if (ed) { jq(ed.target).numberbox('setValue', amount); }
    jq('#dgJournalItem').edatagrid('getRows')[idx][fld] = amount; // needs to set iregardless of editor (i.e. when editors are hidden)
}

function setDgEdTxt(idx, fld, txt) {
    var ed = jq('#dgJournalItem').edatagrid('getEditor', {index:idx,field:fld});
    if (ed) { ed.target.val(txt); }
    jq('#dgJournalItem').edatagrid('getRows')[idx][fld] = txt; // needs to set iregardless of editor (i.e. when editors are hidden)
}

function setFields(rowIndex) {
    var qtyEditor = jq('#dgJournalItem').datagrid('getEditor', {index:rowIndex,field:'qty'});
    if (qtyEditor) jq(qtyEditor.target).numberbox('setValue',1);
    var glEditor = jq('#dgJournalItem').datagrid('getEditor', {index:rowIndex,field:'gl_account'});
    if (glEditor) jq(glEditor.target).combogrid('setValue', def_contact_gl_acct);
    var taxEditor = jq('#dgJournalItem').datagrid('getEditor', {index:rowIndex,field:'tax_rate_id'});
    if (taxEditor) jq(taxEditor.target).combogrid('setValue', def_contact_tax_id);
}

/**************************** orders ******************************************************/
function contactsDetail(rID, suffix, fill) {
    jq.ajax({
        url:     bizunoAjax+'&p=contacts/main/details&rID='+rID+'&suffix='+suffix+'&fill='+fill,
        success: function(json) {
            processJson(json);
            if (suffix=='_b') {
                jq('#terms').val(json.contact.terms);
                bizTextSet('terms_text', json.contact.terms_text);
                if (bizDefaults.phreebooks.journalID == 6) {
                    bizDateSet('terminal_date', formatDate(json.contact.terminal_date));
                }
                jq('#spanContactProps'+suffix).show();
                if (json.contact.rep_id != 0) { bizSelSet('rep_id', json.contact.rep_id); }
                def_contact_gl_acct = json.contact.gl_account;
                def_contact_tax_id  = json.contact.tax_rate_id < 0 ? 0 : json.contact.tax_rate_id;
                var glEditor = jq('#dgJournalItem').datagrid('getEditor', {index:curIndex,field:'gl_account'});
                if (glEditor) jq(glEditor.target).combogrid('setValue', def_contact_gl_acct);
                var taxEditor= jq('#dgJournalItem').datagrid('getEditor', {index:curIndex,field:'tax_rate_id'});
                if (taxEditor) jq(taxEditor.target).combogrid('setValue', def_contact_tax_id);
            }
            for (var i = 0; i < json.address.length; i++) { // pull the main address record
                if (json.address[i].type == 'm') addressFill(json.address[i], json.suffix);
            }
            var tmp = new Array();
            jq.each(json.address, function () { if (this.type=='m' || this.type=='b') tmp.push(this); });
            jq('#addressSel'+suffix).combogrid({ data: tmp });
            jq('#addressDiv'+suffix).show();
            bizUncheckBox('AddUpdate'+suffix);
            if (fill == 'both' || suffix=='_s') {
                var tmp = new Array();
                jq.each(json.address, function () {
                    if (this.type=='m') this.address_id = ''; // prevents overriding billing address if selected and add/update checked
                    if (this.type=='m' || this.type=='s') tmp.push(this);
                });
                jq('#addressSel_s').combogrid({ data: tmp });
                jq('#addressDiv_s').show();
            }
            if (suffix=='_b' && json.showStatus=='1') jsonAction('phreebooks/main/detailStatus', json.contact.id);
        }
    });
}

function orderFill(data, type) {
    var gl_account= '';
    var qtyEditor = jq('#dgJournalItem').datagrid('getEditor', {index:curIndex,field:'qty'});
    var skuEditor = jq('#dgJournalItem').datagrid('getEditor', {index:curIndex,field:'sku'});
    var descEditor= jq('#dgJournalItem').datagrid('getEditor', {index:curIndex,field:'description'});
    var glEditor  = jq('#dgJournalItem').datagrid('getEditor', {index:curIndex,field:'gl_account'});
    var taxEditor = jq('#dgJournalItem').datagrid('getEditor', {index:curIndex,field:'tax_rate_id'});
    var qty       = jq(qtyEditor.target).numberbox('getValue'); //handles formatted values
    if (!qty) qty = 1;
    switch (bizDefaults.phreebooks.journalID) {
        case  3:
        case  4:
        case  6:
        case  7: gl_account = data.gl_inv;  break;
        default: gl_account = data.gl_sales;break;
    }
    var def_tax_id = type=='v' ? data.tax_rate_id_v : data.tax_rate_id_c;
    if (def_tax_id == '-1') def_tax_id = def_contact_tax_id;
    var adjDesc  = type=='v' ? data.description_purchase : data.description_sales;
    // adjust for invVendors extension
    if (typeof(data.invVendors) != 'undefined' && data.invVendors) {
        var cID = jq('#contact_id_b').val();
        if (cID) {
            invVendors = JSON.parse(data.invVendors);
            for (var i=0; i<invVendors.length; i++) {
                if (invVendors[i].id == cID) {
                    if (qty < parseFloat(invVendors[i].qty_pkg)) qty = parseFloat(invVendors[i].qty_pkg);
                    adjDesc  = invVendors[i].desc;
                    def_tax_id = def_contact_tax_id;
                }
            }
        }
    }
    // set the datagrid source data
    jq('#dgJournalItem').edatagrid('getRows')[curIndex]['qty']           = qty;
    jq('#dgJournalItem').edatagrid('getRows')[curIndex]['sku']           = data.sku;
    jq('#dgJournalItem').edatagrid('getRows')[curIndex]['description']   = adjDesc;
    jq('#dgJournalItem').edatagrid('getRows')[curIndex]['gl_account']    = gl_account;
    jq('#dgJournalItem').edatagrid('getRows')[curIndex]['tax_rate_id']   = def_tax_id;
    jq('#dgJournalItem').edatagrid('getRows')[curIndex]['pkg_length']    = data.length;
    jq('#dgJournalItem').edatagrid('getRows')[curIndex]['pkg_width']     = data.width;
    jq('#dgJournalItem').edatagrid('getRows')[curIndex]['pkg_height']    = data.height;
    jq('#dgJournalItem').edatagrid('getRows')[curIndex]['inventory_type']= data.inventory_type;
    jq('#dgJournalItem').edatagrid('getRows')[curIndex]['item_weight']   = data.item_weight;
    jq('#dgJournalItem').edatagrid('getRows')[curIndex]['qty_stock']     = data.qty_stock;
    jq('#dgJournalItem').edatagrid('getRows')[curIndex]['full_price']    = data.full_price;
    // set the editor values
    jq(qtyEditor.target).numberbox('setValue', qty);
    descEditor.target.val(adjDesc);
    if (glEditor)  jq(glEditor.target).combogrid( 'setValue', gl_account);
    if (taxEditor) jq(taxEditor.target).combogrid('setValue', def_tax_id);
    if (skuEditor) jq(skuEditor.target).combogrid('setValue', data.sku);
    var targetDate = new Date();
    targetDate.setDate(targetDate.getDate() + parseInt(data.lead_time));
    jq('#dgJournalItem').edatagrid('getRows')[curIndex]['date_1'] = formatDate(targetDate);
//    alert('calculating price, curIndex='+curIndex+' and sku = '+data.sku+' and qty = '+qty+' and type = '+type);
    ordersPricing(curIndex, data.sku, qty, type);
}

/**
 * Ajax fetch and fill pricing for a line item, typically called after a user selects an item from the SKU list
 * @param string idx - DOM id
 * @param string sku - line item SKU
 * @param float qty - line item Quantity
 * @param char type - options are c for customers or v for vendors to pull from the proper price sheet
 * @returns filled datagrid values with adjustments for users currency selected
 */
function ordersPricing(idx, sku, qty, type) {
    var cID = jq('#contact_id_b').val();
    if (typeof sku == 'undefined' || sku == '') { return; }
//  alert('idx = '+idx+' and sku = '+sku+' and qty = '+qty+' and type = '+type);
    jq.ajax({
        url: bizunoAjax+'&p=inventory/prices/quote&type='+type+'&cID='+cID+'&sku='+sku+'&qty='+qty,
        success: function (data) {
            processJson(data);
            iso  = bizSelGet('currency');
            xRate= iso != bizDefaults.currency.defaultCur ? bizDefaults.currency.currencies[iso].value : 1;
            setDgEdVal(idx, 'price', data.price * parseFloat(xRate));
            setDgEdVal(idx, 'total', data.price * qty * parseFloat(xRate));
            if (jq('#dgJournalItem').edatagrid('getRows').length == (idx+1)) jq('#dgJournalItem').edatagrid('addRow'); // auto add new row
        }
    });
}

function ordersEditing(rowIndex) {
    curIndex = rowIndex;
    var sku  = jq('#dgJournalItem').edatagrid('getRows')[rowIndex]['sku'];
    var desc = jq('#dgJournalItem').edatagrid('getRows')[rowIndex]['description'];
    if (!sku && !desc) { // blank row, set the defaults
        var glEditor = jq('#dgJournalItem').datagrid('getEditor', {index:rowIndex,field:'gl_account'});
        if (glEditor) {
            jq(glEditor.target).combogrid('setValue',def_contact_gl_acct);
        } else {
            jq('#dgJournalItem').edatagrid('getRows')[rowIndex]['gl_account'] = def_contact_gl_acct;
        }
        var taxEditor = jq('#dgJournalItem').datagrid('getEditor', {index:rowIndex,field:'tax_rate_id'});
        if (taxEditor) jq(taxEditor.target).combogrid('setValue',def_contact_tax_id);
    }
    var skuEditor = jq('#dgJournalItem').datagrid('getEditor', {index:rowIndex,field:'sku'});
    switch (bizDefaults.phreebooks.journalID) { // disable sku editor if linked to SO/PO or at least part of line has been filled
            case  3:
            case  4:
            case  9:
            case 10:
                var bal = jq('#dgJournalItem').edatagrid('getRows')[rowIndex]['bal'];
                if (typeof bal !== 'undefined' && bal > 0) {
                    if (skuEditor) jq(skuEditor.target).combogrid({readonly:true}).combogrid('setValue',sku).combogrid('setText',sku);
                }
                break;
            default:
                var item_ref_id= jq('#dgJournalItem').edatagrid('getRows')[rowIndex]['item_ref_id'];
                if (typeof item_ref_id !== 'undefined' && item_ref_id > 0) {
                    if (skuEditor) jq(skuEditor.target).combogrid({readonly:true}).combogrid('setValue',sku).combogrid('setText',sku);
                }
                break;
    }
}

function ordersCalc(action) {
    var qtyEditor   = jq('#dgJournalItem').datagrid('getEditor', {index:curIndex,field:'qty'});
    var priceEditor = jq('#dgJournalItem').datagrid('getEditor', {index:curIndex,field:'price'});
    var totalEditor = jq('#dgJournalItem').datagrid('getEditor', {index:curIndex,field:'total'});
    var oldQty  = jq('#dgJournalItem').edatagrid('getRows')[curIndex]['qty'];
    var newQty  = qtyEditor ? qtyEditor.target.val() : oldQty;
    if (isNaN(newQty))   newQty   = 0;
    var newPrice= priceEditor ? (priceEditor.target.val()) : jq('#dgJournalItem').edatagrid('getRows')[curIndex]['price'];
    if (isNaN(newPrice)) newPrice = jq('#dgJournalItem').edatagrid('getRows')[curIndex]['price'];
    if (isNaN(newPrice)) newPrice = 0;
    var newTotal= totalEditor ? (totalEditor.target.val()) : jq('#dgJournalItem').edatagrid('getRows')[curIndex]['total'];
    if (isNaN(newTotal)) newTotal = jq('#dgJournalItem').edatagrid('getRows')[curIndex]['total'];
    if (isNaN(newTotal)) newTotal = 0;
//    alert('ordersCalc action = '+action+' and dg qty = '+oldQty+' and editor qty = '+newQty+' and read price = '+newPrice+' and read total = '+newTotal);
    switch (action) {
        case 'qty':
            // when uncommented, this prevents qty_so problems when editing (may have been fixed with journal re-design)
            // when commented, automatically opens SO/PO when closed, user may not observe that it was re-opened and when saved, SO/PO is re-opened.
//          if (oldQty !== newQty) jq('#closed').attr('checked', false);
            jq('#dgJournalItem').edatagrid('getRows')[curIndex]['qty'] = newQty;
            var hasSOorPO = parseInt(jq('#so_po_ref_id').val()); // string "0" evaluates to true!
            setDgEdVal(curIndex, 'total', newPrice*newQty); // set the price now for speedy updaters if they click another row before new price is retrieved from server
            if (!hasSOorPO && oldQty !== newQty) { // fetch a new price based on the qty change, only if not refered by a SO or Po
                var sku = jq('#dgJournalItem').edatagrid('getRows')[curIndex]['sku'];
                ordersPricing(curIndex, sku, newQty, bizDefaults.phreebooks.type);
            }
            break;
        case 'price':
            jq('#dgJournalItem').edatagrid('getRows')[curIndex]['price'] = newPrice;
            setDgEdVal(curIndex, 'total',  (newPrice*newQty));
            break;
        case 'total':
            if (newQty == 0) { newTotal = 0; }
            else             { newPrice = newTotal / newQty; }
            var tmp1 = bizRoundNumber(newPrice); // check for rounding circular logic
            var tmp2 = bizRoundNumber(jq('#dgJournalItem').edatagrid('getRows')[curIndex]['price']);
//alert('newPrice = '+newPrice+' and tmp1 = '+tmp1+' and tmp2 = '+tmp2);
            if (tmp1 != tmp2) { setDgEdVal(curIndex, 'price', newPrice); }
            setDgEdVal(curIndex, 'total', newTotal);
            totalUpdate('ordersCalc');
            break;
    }
}

/**************************** Banking ******************************************************/
function bankingCalc(action) {
    var discEditor  = jq('#dgJournalItem').datagrid('getEditor', {index:curIndex,field:'discount'});
    var totalEditor = jq('#dgJournalItem').datagrid('getEditor', {index:curIndex,field:'total'});
    if (!discEditor || !totalEditor) return; // editor is not active
    var newDisc = discEditor.target.val();
    if (isNaN(newDisc)) newDisc = 0;
    var newTotal= totalEditor.target.val();
    if (isNaN(newTotal)) newTotal = 0;
//  alert('bankingCalc action = '+action+' and newDisc = '+newDisc+' and newTotal = '+newTotal);
    switch (action) {
        case 'disc':
            var amount  = jq('#dgJournalItem').edatagrid('getRows')[curIndex]['amount'];
            jq('#dgJournalItem').edatagrid('getRows')[curIndex]['discount']= newDisc;
            setDgEdVal(curIndex, 'total', amount - newDisc);
            break;
        case 'direct':
            setDgEdVal(curIndex, 'total', newTotal);
            totalUpdate('bankingCalc');
            break;
    }
}

function bankingEdit(rowIndex) {
    curIndex = rowIndex;
}

/**************************** Order Support Functions ******************************************************/
function inventoryGetPrice(rowIndex, type) {
    var cID    = jq('#contact_id_b').val();
    var rowData= jq('#dgJournalItem').datagrid('getData');
    if (typeof rowData.rows[rowIndex] == 'undefined') return;
    var sku    = rowData.rows[rowIndex].sku;
    if (!sku) return;
    jsonAction('inventory/prices/details&cID='+cID+'&sku='+sku+'&type='+type);
}

function inventoryProperties(rowIndex) {
    var rowData= jq('#dgJournalItem').datagrid('getData');
    if (typeof rowData.rows[rowIndex] == 'undefined') return;
    var sku = rowData.rows[rowIndex].sku;
    if (!sku) return;
    windowEdit('inventory/main/properties&data='+sku, 'winInvProps', jq.i18n('SETTINGS'), 800, 600);
    // add event to window to restart editing to fix bug killing event handler of current row
    jq('#winInvProps').window({onClose:function() { ordersEditing(rowIndex); } });
}

function shippingEstimate(jID) {
    var data = { bill:{}, ship:{}, item:[], totals:{} };
    jq("#address_b input").each(function() { if (jq(this).val()) data.bill[jq(this).attr("name")] = jq(this).val(); });
    jq("#address_s input").each(function() { if (jq(this).val()) data.ship[jq(this).attr("name")] = jq(this).val(); });
    var resi   = jq('#totals_shipping_resi').is(':checked') ? '1' : '0';
    jq('#dgJournalItem').edatagrid('saveRow', curIndex);
    var rowData= jq('#dgJournalItem').edatagrid('getData');
    for (var rowIndex=0; rowIndex<rowData.total; rowIndex++) {
        var tmp = {};
        tmp['qty'] = parseFloat(rowData.rows[rowIndex].qty);
        if (isNaN(tmp['qty'])) tmp['qty'] = 0;
        tmp['sku'] = rowData.rows[rowIndex].sku;
        data.item.push(tmp);
    }
    data.totals['total_amount'] = cleanCurrency(jq('#total_amount').val()) - cleanCurrency(jq('#freight').val());
    var content = encodeURIComponent(JSON.stringify(data));
    var url = bizunoAjax+'&p=extShipping/ship/rateMain&jID='+jID+'&resi='+resi+'&data='+content;
    jq('#shippingEst').window({ title:jq.i18n('SHIPPING_ESTIMATOR'), width:1000, height:600, modal:true }).window('refresh', url);
}

function selPayment(value) {
    if (value == '') return;
    jq("#method_code>option").map(function() {
        var value = jq(this).val();
        jq("#div_"+value).hide('slow');
    });
    jq("#div_"+value).show('slow');
    window['payment_'+value]();
}

// *******************  Assemblies  ************************************
function assyUpdateBalance() {
    var onHand = parseFloat(bizNumGet('qty_stock'));
    if (isNaN(onHand)) {
        bizNumSet('qty_stock', 0);
        onHand = 0;
    }
    var qty = parseFloat(bizNumGet('qty'));
    if (isNaN(qty)) {
        bizNumSet('qty', 1);
        qty = 1;
    }
    var rowData= jq('#dgJournalItem').datagrid('getData');
    var total  = 0;
    for (var rowIndex=0; rowIndex<rowData.total; rowIndex++) {
        var unitQty = parseFloat(rowData.rows[rowIndex].qty);
        rowData.rows[rowIndex].qty_required = formatNumber(qty * unitQty);
        total += qty * unitQty;
    }
    jq('#dgJournalItem').datagrid('loadData', rowData);
    jq('#dgJournalItem').datagrid('reloadFooter', [{description: jq.i18n('TOTAL'), qty_required: formatNumber(total)}]);
    var bal = onHand+qty;
//    alert('on hand = '+onHand+' and qty = '+qty+' and bal = '+bal);
    bizNumSet('balance', bal);
}

//*******************  Adjustments  ************************************
function adjFill(data) {
    var jID = jq('#journal_id').val();
    var qtyEditor  = jq('#dgJournalItem').datagrid('getEditor', {index:curIndex,field:'qty'});
    var qty = qtyEditor.target.val() ? parseFloat(qtyEditor.target.val()) : 1;
    setDgEdVal(curIndex, 'qty', qty);
    setDgEdVal(curIndex, 'qty_stock', parseFloat(data.qty_stock));
    setDgEdTxt(curIndex, 'description', data.description_short);
    if (jID=='15') { setDgEdVal(curIndex, 'balance', parseFloat(data.qty_stock) - qty); }
    else           { setDgEdVal(curIndex, 'balance', parseFloat(data.qty_stock) + qty); }
    setDgEdVal(curIndex, 'total', (data.item_cost) * qty);
    jq('#dgJournalItem').edatagrid('getRows')[curIndex]['unit_cost'] = parseFloat(data.item_cost);
    jq('#dgJournalItem').edatagrid('getRows')[curIndex]['gl_account']= data.gl_inv;
    totalUpdate();
}

function adjCalc(action) {
    var jID = jq('#journal_id').val();
    var qtyEditor   = jq('#dgJournalItem').datagrid('getEditor', {index:curIndex,field:'qty'});
    var totalEditor = jq('#dgJournalItem').datagrid('getEditor', {index:curIndex,field:'total'});
    var stockEditor = jq('#dgJournalItem').datagrid('getEditor', {index:curIndex,field:'qty_stock'});
    if (!qtyEditor || !totalEditor || !stockEditor) return; // all editors are not active
    var newQty = cleanNumber(qtyEditor.target.val());
    if (isNaN(newQty)) newQty = 0;
    var newTotal= totalEditor.target.val();
    if (isNaN(newTotal)) newTotal = jq('#dgJournalItem').edatagrid('getRows')[curIndex]['total'];
    if (isNaN(newTotal)) newTotal = 0;
    switch (action) {
        case 'qty':
            var newQty = cleanNumber(qtyEditor.target.val());
            var onHand = cleanNumber(stockEditor.target.val());
            if (jID=='16') { setDgEdVal(curIndex, 'balance', onHand + newQty); }
            else           { setDgEdVal(curIndex, 'balance', onHand - newQty); }
            if (newQty < 0) { // disable total and set to null
                setDgEdVal(curIndex, 'total', 0);
                jq(totalEditor.target).numberbox('disable');
            } else {
                var unitCost = jq('#dgJournalItem').edatagrid('getRows')[curIndex]['unit_cost'];
                if (isNaN(unitCost)) unitCost = 0;
                setDgEdVal(curIndex, 'total', unitCost * newQty);
                if (jID=='16') jq(totalEditor.target).numberbox('enable');
            }
            break;
    }
    totalUpdate();
}

//*******************  Reconciliation  ************************************
lastIndex = -1;
var pauseTotal = true;

function reconInit(row, data) {
    var stmtBal = formatCurrency(data.footer[0].total);
    bizNumSet('stmt_balance', stmtBal);
    pauseTotal = true;
    for (var i=0; i<data.rows.length; i++) {
        if (data.rows[i]['rowChk'] > 0) {
            jq('#tgReconcile').treegrid('checkRow', data.rows[i].id);
            reconCheck(data.rows[i]);
        } else {
            jq('#tgReconcile').treegrid('uncheckRow', data.rows[i].id); // this slows down load but necesary to clear parents during period or acct change
//            reconUncheck(data.rows[i]); // this causes EXTREMELY SLOW page loads, should not be necessary
        }
    }
    pauseTotal = false;
    reconTotal();
}

function reconCheck(row) {
    jq('#tgReconcile').treegrid('update',{ id:row.id, row:{rowChk: true} });
    if (row.id.substr(0, 4) == 'pID_') {
        var node = jq('#tgReconcile').treegrid('getChildren', row.id);
        for (var j=0; j<node.length; j++) {
            jq('#tgReconcile').treegrid('update',{ id:node[j].id, row:{rowChk: true} });
            jq('#tgReconcile').treegrid('checkRow', node[j].id);
        }
    } else if (typeof row._parentId !== 'undefined') {
        reconCheckChild(row._parentId);
    }
}

function reconCheckChild(parentID) {
    var node = jq('#tgReconcile').treegrid('getChildren', parentID);
    var allChecked = true;
    for (var j=0; j<node.length; j++) if (!node[j].rowChk) { allChecked = false; }
    if (allChecked) jq('#tgReconcile').treegrid('update',{ id:parentID, row:{rowChk: true} });
}

function reconUncheck(row) {
    jq('#tgReconcile').treegrid('update',{ id:row.id, row:{rowChk: false} });
    if (row.id.substr(0, 4) == 'pID_') {
        var node = jq('#tgReconcile').treegrid('getChildren', row.id);
        for (var j=0; j<node.length; j++) {
            jq('#tgReconcile').treegrid('update',{ id:node[j].id, row:{rowChk: false} });
            jq('#tgReconcile').treegrid('uncheckRow', node[j].id);
        }
    } else if (typeof row._parentId !== 'undefined') {
        jq('#tgReconcile').treegrid('update',{ id:row._parentId, row:{rowChk: false} });
    }
}

function reconTotal() {
    if (pauseTotal) { return; }
    var openTotal  = 0;
    var closedTotal= 0;
    var items = jq('#tgReconcile').treegrid('getData');
    for (var i=0; i<items.length; i++) {
        if (isNaN(items[i]['total'])) alert('error in total = '+items[i]['total']);
        if (items[i]['id'].substr(0, 4) == 'pID_') {
            var node = jq('#tgReconcile').treegrid('getChildren', items[i]['id']);
            for (var j=0; j<node.length; j++) {
                ttl = parseFloat(node[j]['deposit']) - parseFloat(node[j]['withdrawal']);
                if (node[j]['rowChk']) { closedTotal += ttl; }
                else                    { openTotal += ttl; }
            }
        } else {
            if (items[i]['rowChk']) { closedTotal += parseFloat(items[i]['total']); }
            else                    { openTotal   += parseFloat(items[i]['total']); }
        }
    }
    var stmt  = cleanCurrency(jq('#stmt_balance').val());
    var footer= jq('#tgReconcile').treegrid('getFooterRows');
    var gl    = parseFloat(footer[3]['total']);
    footer[0]['total'] = stmt;
    footer[1]['total'] = closedTotal;
    footer[2]['total'] = openTotal;
    footer[4]['total'] = stmt + openTotal - gl;
    jq('#tgReconcile').datagrid('reloadFooter');
}

function reconcileShowDetails(ref) {
  if(document.all) { // IE browsers
    if (document.getElementById('disp_'+ref).innerText == textHide) {
      document.getElementById('detail_'+ref).style.display = 'none';
      document.getElementById('disp_'+ref).innerText = textShow;
    } else {
      document.getElementById('detail_'+ref).style.display = '';
      document.getElementById('disp_'+ref).innerText = textHide;
    }
  } else {
    if (document.getElementById('disp_'+ref).textContent == textHide) {
      document.getElementById('detail_'+ref).style.display = 'none';
      document.getElementById('disp_'+ref).textContent = textShow;
    } else {
      document.getElementById('detail_'+ref).style.display = '';
      document.getElementById('disp_'+ref).textContent = textHide;
    }
  }
}

function reconcileUpdateSummary(ref) {
  var cnt = 0;
  var rowRef = 'disp_'+ref+'_';
  var checked = document.getElementById('sum_'+ref).checked;
  document.getElementById('disp_'+ref).style.backgroundColor = '';
  while(true) {
    if (!document.getElementById(rowRef+cnt)) break;
    document.getElementById('chk_'+ref).checked = (checked) ? true : false;
    cnt++;
    ref++;
  }
  updateBalance();
}

function reconcileUpdateDetail(ref) {
  var numDetail  = 0;
  var numChecked = 0;
  var rowRef     = 'disp_'+ref+'_';
  var cnt        = 0;
  var origRef    = ref;
  while (true) {
    if (!document.getElementById(rowRef+cnt)) break;
    if (document.getElementById('chk_'+ref).checked) numChecked++;
    numDetail++;
    cnt++;
    ref++;
  }
  if (numChecked == 0) { // none checked
      document.getElementById('disp_'+origRef).style.backgroundColor = '';
    document.getElementById('sum_'+origRef).checked = false;
  } else if (numChecked == numDetail) { // all checked
      document.getElementById('disp_'+origRef).style.backgroundColor = '';
    document.getElementById('sum_'+origRef).checked = true;
  } else { // partial checked
      document.getElementById('disp_'+origRef).style.backgroundColor = 'yellow';
    document.getElementById('sum_'+origRef).checked = true;
  }
  reconcileUpdateBalance();
}

function reconcileUpdateBalance() {
  var value;
  var start_balance = cleanCurrency(document.getElementById('start_balance').value);
  var open_checks   = 0;
  var open_deposits = 0;
  var gl_balance = cleanCurrency(document.getElementById('gl_balance').value);
  for (var i=0; i<totalCnt; i++) {
    if (!document.getElementById('chk_'+i).checked) {
      value = parseFloat(document.getElementById('pmt_'+i).value);
      if (value < 0) {
        if (!isNaN(value)) open_checks -= value;
      } else {
        if (!isNaN(value)) open_deposits += value;
      }
    }
  }
  var sb = new String(start_balance);
  document.getElementById('start_balance').value = formatCurrency(sb);
  var dt = new String(open_checks);
  document.getElementById('open_checks').value = formatCurrency(dt);
  var ct = new String(open_deposits);
  document.getElementById('open_deposits').value = formatCurrency(ct);

  var balance = start_balance - open_checks + open_deposits - gl_balance;
  var tot = new String(balance);
  document.getElementById('balance').value = formatCurrency(tot);
  var numExpr = Math.round(eval(balance) * Math.pow(10, bizDefaults.currency.currencies[bizDefaults.currency.defaultCur].dec_len));
  if (numExpr == 0) {
      document.getElementById('balance').style.color = '';
  } else {
      document.getElementById('balance').style.color = 'red';
  }
}
