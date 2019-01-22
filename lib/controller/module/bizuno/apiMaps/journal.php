<?php
/*
 * Template for Bizuno API to handle imported orders
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
 * @copyright  2008-2019, PhreeSoft, Inc.
 * @license    http://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 * @version    3.x Last Update: 2018-03-08
 * @filesource /lib/controller/module/bizuno/apiMaps/journal.php
 */

$map = [
    'General' => [
        'StoreID'             => ['field'=>'store_id','format'=>'text'],
        'ReceivablesGLAccount'=> ['field'=>'gl_acct_id','format'=>'text'],
        'OrderID'             => ['field'=>'invoice_num','format'=>'text'], //customer specified, force next invoice sequence 
        'PurchaseOrderID'     => ['field'=>'purch_order_id','format'=>'text'], //cart order number with prefix
        'OrderDate'           => ['field'=>'post_date','format'=>''], // already in db format
        'OrderTotal'          => ['field'=>'total_amount','format'=>'float'], // total includes all costs
        'TaxOrder'            => ['field'=>'sales_tax','format'=>'float'],   // order level sales tax
        'SalesTaxPercent'     => ['field'=>'tax_rate_id','format'=>'float'], //     if order level sales tax, this is priority 1
        'SalesTaxTitle'       => ['field'=>'tax_rate_id','format'=>'text'], // or, if order level sales tax, this is priority 2
        'SalesTaxAmount'      => ['field'=>'tax_rate_id','format'=>'float'], // or, if order level sales tax, this is priority 3
        'ShippingTotal'       => ['field'=>'freight','format'=>'float'],
        'ShippingCarrier'     => ['field'=>'method_code','format'=>'text'],
        'SalesRepID'          => ['field'=>'rep_id','format'=>'text']],
    'Contact' => [
        'CustomerID'          => ['field'=>'short_name','format'=>'text'],
        'CompanyName'         => ['field'=>'primary_name','format'=>'text'],
        'Contact'             => ['field'=>'contact','format'=>'text'],
        'Address1'            => ['field'=>'address1','format'=>'text'],
        'Address2'            => ['field'=>'address2','format'=>'text'],
        'City'                => ['field'=>'city','format'=>'text'],
        'State'               => ['field'=>'state','format'=>'text'],
        'PostalCode'          => ['field'=>'postal_code','format'=>'text'],
        'Country'             => ['field'=>'country','format'=>'country','option'=>'ISO3'],
        'Telephone'           => ['field'=>'telephone1','format'=>'text'],
        'Email'               => ['field'=>'email','format'=>'text']],
    'Item' => [
        'ItemID'              => ['field'=>'sku','format'=>'text'],
        'Description'         => ['field'=>'description','format'=>'text'],
        'Quantity'            => ['field'=>'qty','format'=>'float'],
        'SalesGLAccount'      => ['field'=>'gl_account','format'=>'text'],
        'SalesTaxTitle'       => ['field'=>'tax_rate_id','format'=>'text'], // or, if item level sales tax, this is priority 1
        'SalesTaxPercent'     => ['field'=>'tax_rate_id','format'=>'float'], //     if item level sales tax, this is priority 2
        'SalesTaxAmount'      => ['field'=>'tax_rate_id','format'=>'float'], // or, if item level sales tax, this is priority 3, total tax for all items in this row (qty * item_unit_tax)
        'TotalPrice'          => ['field'=>'credit_amount','format'=>'float']],
    'Payment' => [
        'Method'              => ['field'=>'method_code','format'=>'text'],
        'Title'               => ['field'=>'title','format'=>'text'],
        'Status'              => ['field'=>'status','format'=>'text'], // possible values are unpaid, auth, and cap [default: unpaid]
        'Authorization'       => ['field'=>'auth_code','format'=>'text'], // Authorization code from credit cards that need to be captured to complete the sale
        'TransactionID'       => ['field'=>'transaction_id','format'=>'text'], // Transaction from credit cards that need to be captured to complete the sale
        'Hint'                => ['field'=>'hint','format'=>'text']], // format nnnn********nnnn (i.e. 4321********9876 )
    ];