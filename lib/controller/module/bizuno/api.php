<?php
/*
 * Functions to support API operations through Bizuno
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
 * @version    3.x Last Update: 2019-03-05
 * @filesource /lib/controller/module/bizuno/api.php
 */

namespace bizuno;

class bizunoApi
{
    public $moduleID = 'bizuno';

    function __construct()
    {
        $this->lang = getLang('bizuno'); // needs to be hardcoded as this is extended by extensions
    }

    /**
     * Handles incoming requests through the API, parses and calls appropriate method
     * User is validated before we reach this point
     *
     * @param type $layout
     * @return type
     */
    public function processRcv(&$layout=[])
    {
        $action = clean('Action', 'cmd', 'post'); // determine the operation to be performed
        if (!$action) { return msgAdd("No API function, returning!"); }
        switch ($action) {
            case 'journal':
                // wordpress API to wordpress host is adding slashes to Order?? strip them, others?
                if (substr($_POST['Order'], 1, 1) == "\\") { $_POST['Order'] = stripslashes($_POST['Order']); }
                $order = clean('Order', 'json', 'post');
                return $this->apiJournalEntry($layout, $order);
            case 'products':
                return msgAdd("Processing product, nothing to see here! This is not the code you are looking for.");
            default: // do nothing, maybe mods
        }
    }

    /**
     * API method to process incoming orders
     * @param array $layout - structure coming in
     * @param array $order - order data passed through the API
     * @param integer $jID [default 0] - used to preset the journal being written to
     * @return array modified $layout structure
     */
    protected function apiJournalEntry(&$layout, $order=[], $jID=0)
    {
        msgDebug("\nWorking with submitted order = ".print_r($order, true));
        if (!dbConnected()) { return msgAdd('There was an issue connecting to your account! Please check your credentials.'); }
        $this->itemTotal = 0;
        $this->main = $this->items = $map = [];
        $this->defaultAR      = getModuleCache('bizuno','settings','bizuno_api','gl_receivables', getModuleCache('phreebooks','settings','customers','gl_receivables'));
        $this->defaultGlSales = getModuleCache('bizuno','settings','bizuno_api','gl_sales',       getModuleCache('phreebooks','settings','customers','gl_sales'));
        $this->inStock        = true;
        require(BIZUNO_LIB."controller/module/bizuno/apiMaps/journal.php"); // loads the journal map file structure
        $this->setJournalMain($map, $order);
        $this->setJournalItem($map, $order);
        $this->setJournalFreight($order);
        $this->setJournalTax($order);
        $this->setJournalDiscount($order); // must be last, also does unbalance correction for discrepancies between order total and item total
        $this->setJournalTotal($order);
        // determine what journal to post to
        switch ($jID) {
            case 10: $jID = 10; break; // Sales Order
            case 12: $jID = 12; break; // Sale
            default: $jID = $this->getStockLevels($this->items); // Auto detect
        }
        // test for duplicate invoice #'s
        if ($jID == 10) {
            $test = "journal_id=10 AND invoice_num='{$this->main['invoice_num']}'";
        } else { // jID=12
            $this->main['purch_order_id']= $this->main['invoice_num'];
            $this->main['invoice_num']   = '';  // force new invoice #
            $test = "journal_id=12 AND purch_order_id='{$this->main['purch_order_id']}'";
        }
        $dup = dbGetValue(BIZUNO_DB_PREFIX.'journal_main', 'id', $test);
        if ($dup) { return msgAdd(sprintf(lang('err_gl_invoice_num_dup'), lang('reference'), $jID==10 ? $this->main['invoice_num'] : $this->main['purch_order_id'])); }
        // Set some other main values to post, fill in defaults
        $this->main['waiting'] = '1'; // force unshipped
        if ( empty($this->main['gl_acct_id'])) { $this->main['gl_acct_id'] = $this->defaultAR; }
        if (!empty($this->main['store_id']))   { // try to find the store record ID from the short_name
            $sID = dbGetValue(BIZUNO_DB_PREFIX.'contacts', 'id', "type='b' AND short_name='{$this->main['store_id']}'");
            if ($sID) { $this->main['store_id'] = $sID; }
        }
        $this->main['method_code'] = $this->guessShipMethod($this->main['method_code']); // try to map shipping method
        // Post order
        bizAutoLoad(BIZUNO_LIB."controller/module/phreebooks/journal.php", 'journal');
        // ***************************** START TRANSACTION *******************************
        dbTransactionStart();
        $journal = new journal(0, $jID, $this->main['post_date']);
        $journal->main = array_replace($journal->main, $this->main);
        $journal->items = $this->items;
        // guess Sales Tax
        $journal->main['tax_rate_id'] = $this->taxGuess($order['General'], $order['General']['OrderTotal']); // try to determine tax rate at the order level
        // Check to see if customer already exists in db
        $cID = dbGetContact($this->main, '_b');
        unset($this->main['short_name_b'], $journal->main['short_name_b']); // causes post errors as field is not in journal_main
        if ($cID) { // found match
            $journal->main['contact_id_b'] = $cID['contact_id'];
            $journal->main['address_id_b'] = $cID['address_id'];
        } else { // force the contact record to be created, set $_POST so contact info can be saved
            foreach ($journal->main as $key => $value) { $_POST[$key] = $value; } // temp until contacts module can be re-written to accept ->main and process
            $_POST['tax_rate_id_b'] = $journal->main['tax_rate_id'];
            $journal->updateContact_b = true;
        }
        msgDebug("\nReady to post order, main = ".print_r($journal->main, true));
        msgDebug("\nitems = ".print_r($journal->items, true));
        if (!$journal->Post()) { return; }
        $this->setJournalPayment($map, $order, $journal->main['id']);
        // ***************************** END TRANSACTION *******************************
        dbTransactionCommit();
        $invoiceRef= pullTableLabel('journal_main', 'invoice_num', $journal->main['journal_id']);
        $billName  = isset($journal->main['primary_name_b']) ? $journal->main['primary_name_b'] : $journal->main['description'];
        msgAdd(sprintf(lang('msg_gl_post_success'), $invoiceRef, $journal->main['invoice_num']), 'success');
        msgLog('Bizuno API -'.lang('save')." $invoiceRef ".$journal->main['invoice_num']." - $billName (rID={$journal->main['id']}) ".lang('total').": ".viewFormat($journal->main['total_amount'], 'currency'));
        $layout    = ['content'=>['resultCode'=>0]];
    }

    private function setJournalMain($map, $order=[])
    {
        foreach ($map['General'] as $idx => $value) {
            if (isset($order['General'][$idx])) { $this->main[$value['field']]      = clean($order['General'][$idx], $value['format']); }
        }
        foreach ($map['Contact'] as $idx => $value) {
            if (isset($order['Billing'][$idx])) { $this->main[$value['field'].'_b'] = clean($order['Billing'][$idx], $value['format']); }
        }
        foreach ($map['Contact']as $idx => $value) {
            if (isset($order['Shipping'][$idx])){ $this->main[$value['field'].'_s'] = clean($order['Shipping'][$idx], $value['format']); }
        }
//      unset($this->main['short_name_b']); // commenting this out causes only an address check for cID, ignores test of the cID based on Contact ID
        unset($this->main['short_name_s']);
    }

    private function setJournalItem($map, $order=[])
    {
        $itmCnt = 1;
        foreach ($order['Item'] as $item) {
            $temp = ['item_cnt'=>$itmCnt,'gl_type'=>'itm','post_date'=>$this->main['post_date']];
            foreach ($map['Item'] as $idx => $value) {
                if (isset($item[$idx])) { $temp[$value['field']] = clean($item[$idx], $value['format']); }
            }
            if (empty($temp['gl_account'])) { $temp['gl_account'] = $this->defaultGlSales; }
            // the tax guess at item levels has been commented out as it is about impossible to guaranty accuracy and results in out of balance errors.
            $temp['tax_rate_id'] = $this->taxGuess($item, $temp['credit_amount']);
            $this->itemTotal+= $temp['credit_amount'];
            $this->items[]   = $temp;
            $itmCnt++;
        }
        msgDebug("\nFinished checking stock, inStock = ".($this->inStock?'TRUE':'FALSE'));
        // process any notes, this is after map so need to use table field names to inject no-sku item
        if (!empty($order['General']['OrderNotes'])) {
            $this->items[] = ['item_cnt'=>$itmCnt,'gl_type'=>'itm','qty'=>'1','sku'=>'','description'=>$order['General']['OrderNotes'], 'gl_account'=>$this->defaultGlSales,'post_date'=>$this->main['post_date']];
        }
    }

    /**
     * Sets the shipping item record
     * @param type $order
     */
    private function setJournalFreight($order=[])
    {
        if (empty($order['General']['ShippingTotal'])) { return; }
        $this->items[] = [
            'qty'          => 1,
            'sku'          => '',
            'description'  => "title:".lang('shipping'),
            'gl_type'      => 'frt',
            'credit_amount'=> $order['General']['ShippingTotal'],
            'gl_account'   => getModuleCache('extShipping','settings','general','gl_shipping_c',getModuleCache('phreebooks','settings','customers','gl_sales')),
            'post_date'    => $this->main['post_date']];
        $this->itemTotal += $order['General']['ShippingTotal'];
    }

    /**
     * Creates a tax item record making the assumption that the tax has been properly calculated at the cart
     * @param type $order
     */
    private function setJournalTax($order)
    {
        if (empty($order['General']['SalesTaxAmount'])) { return; }
        $this->main['sales_tax']  = $order['General']['SalesTaxAmount'];
        $this->main['tax_rate_id']= getModuleCache('bizuno','settings','bizuno_api','tax_rate_id',0);
        $this->items[] = [
            'qty'          => 1,
            'sku'          => '',
            'description'  => "title:".lang('inventory_tax_rate_id_c'),
            'gl_type'      => 'tax',
            'credit_amount'=> $order['General']['SalesTaxAmount'],
            'gl_account'   => getModuleCache('bizuno','settings','bizuno_api','gl_tax'),
            'post_date'    => $this->main['post_date']];
        $this->itemTotal  += $order['General']['SalesTaxAmount'];
    }

    /**
     * check item total to order total, any difference should be made into a discount record
     * @param type $order
     */
    private function setJournalDiscount($order)
    {
        $balanceCheck = $this->main['total_amount'] - $this->itemTotal;
        if ($balanceCheck == 0) { return; }
        $this->items[] = [
            'qty'          => 1,
            'sku'          => '',
            'description'  => "title:".lang('discount'),
            'gl_type'      => 'dsc',
            'credit_amount'=> $balanceCheck,
            'gl_account'   => getModuleCache('bizuno','settings','bizuno_api','gl_discount'),
            'post_date'    => $this->main['post_date']];
    }

    /**
     * Creates the total item record
     * @param type $order
     */
    private function setJournalTotal($order)
    {
        $this->items[] = [
            'qty'          => 1,
            'sku'          => '',
            'description'  => "title:".lang('total'),
            'gl_type'      => 'ttl',
            'debit_amount' => $order['General']['OrderTotal'],
            'gl_account'   => $this->defaultAR,
            'post_date'    => $this->main['post_date']];
    }

    /**
     * Set the payment status of an order
     */
    private function setJournalPayment($map, $order=[], $rID=0)
    {
        $pmtInfo = [];
        foreach ($map['Payment'] as $idx => $value) {
            if (isset($order['Payment'][$idx])) { $pmtInfo[$value['field']] = clean($order['Payment'][$idx], $value['format']); }
            else                                { $pmtInfo[$value['field']] = ''; }
        }
        msgDebug("\nWorking with payment array = ".print_r($pmtInfo, true));
        if (!$rID) { return; }
        $iID = dbGetValue(BIZUNO_DB_PREFIX."journal_item", ['id','description'], "ref_id=$rID AND gl_type='ttl'");
        $iID['description'] .= ";method:{$pmtInfo['method_code']};title:{$pmtInfo['title']};status:{$pmtInfo['status']};hint:{$pmtInfo['hint']}";
        switch ($pmtInfo['status']) {
            case 'auth':
                if (empty($pmtInfo['transaction_id'])) { $pmtInfo['transaction_id'] .= $pmtInfo['auth_code']; }
                if (empty($pmtInfo['transaction_id'])) {
                    msgAdd("The order has been authorized but the Authorization Code is not present, the payment for this order must be completed in Bizuno AND at the merchant website.", 'caution');
                }
                break;
            case 'cap':
                // This can be written but needs to know the payment method, fetch the order record
                // check to make sure it was posted successfully
                // make sure it was journal 12 NOT 10, if 10 flag as payment received but product not available???
                // build the save $this->main array, try to map the merchant to get gl_account and reference_id no need to cURL merchant
                // post it, close it as it is now paid
                msgAdd("The order has been paid at the cart, the payment for this order must be completed manually in Bizuno.", 'caution');
            case 'unpaid':
            default:
        }
        dbWrite(BIZUNO_DB_PREFIX."journal_item", ['description'=>$iID['description'],'trans_code'=>$pmtInfo['transaction_id']], 'update', "id={$iID['id']}");
    }

    /**
     * Determines if all products are in stock for auto journal determination
     * @param array $items - List of items in the order
     * @return integer - 12 (sales journal) if order can be filled, 10 (Sales Order journal) if not
     */
    private function getStockLevels($items=[])
    {
        $jID = 12;
        foreach ($items as $item) {
            if ($item['gl_type'] <> 'itm' || $item['sku'] == '') { continue; }
            $inv = dbGetValue(BIZUNO_DB_PREFIX."inventory", ['inventory_type', 'qty_stock'], "sku='{$item['sku']}'");
            msgDebug("\nChecking stock levels, SKU = {$item['sku']} and qty = {$item['qty']} and stock level = {$inv['qty_stock']}");
            msgDebug("\nCOG_ITEM_TYPES = ".COG_ITEM_TYPES." and inventory_type = {$inv['inventory_type']}");
            if (strpos(COG_ITEM_TYPES, $inv['inventory_type']) !== false && $inv['qty_stock'] < $item['qty']) { $jID = 10; }
        }
        return $jID;
    }

    /**
     * Tries to determine the tax rate based on the order data supplied
     * @param array $data - order data after being mapped to Bizuno API format
     * @param float $itemTotal = order total
     * @return int
     */
    private function taxGuess($data=[], $itemTotal=0, $cID=0)
    {
        msgDebug("\nGuessing tax with itemTotal = $itemTotal and contactID = $cID");
        if (empty($this->taxRates)) {
            $this->taxRates = dbGetMulti(BIZUNO_DB_PREFIX.'tax_rates', "type='c'"); // load the tax rates, Not used as not reliable enough
        }
        // use indexes SalesTaxRate (percent), SalesTaxTitle (text), SalesTaxAmount (float) to try to match tax rate
        $taxShipping = getModuleCache('phreebooks', 'settings', 'general', 'shipping_taxed', 0);
        if ($taxShipping && $cID) { return dbGetValue(BIZUNO_DB_PREFIX.'contacts', 'tax_rate_id', "id=$cID"); }
        if       (isset($data['SalesTaxTitle'])  && $data['SalesTaxTitle']) {
            foreach ($this->taxRates as $row) {
                if ($row['title'] == $data['SalesTaxTitle']) {
                    msgDebug(" ... and returning with title found and id = {$row['id']}");
                    return $row['id'];
                }
            }
        } elseif (isset($data['SalesTaxPercent'])&& $data['SalesTaxPercent'] > 0) {
            foreach ($this->taxRates as $row) {
                if ($row['tax_rate'] == $data['SalesTaxPercent']) {
                    msgDebug(" ... and returning with percent found and id = {$row['id']}");
                    return $row['id'];
                }
            }
        } elseif (isset($data['SalesTaxAmount'])  && $data['SalesTaxAmount'] > 0) {
            $itemTotal -= !empty($data['SalesTaxAmount']) ? $data['SalesTaxAmount'] : 0; // subtract out the sales tax amount from the submitted total
            $percentTotal = round((($data['SalesTaxAmount'] / $itemTotal) * 100), getModuleCache('bizuno', 'settings', 'locale', 'number_precision', 2));
            msgDebug(" ... now working with calculated percent $percentTotal");
            foreach ($this->taxRates as $row) {
                $percentRate = round($row['tax_rate'], getModuleCache('bizuno', 'settings', 'locale', 'number_precision', 2));
                msgDebug(" ... id = {$row['id']} and percentRate = $percentRate");
                if (abs($percentTotal-$percentRate) < 0.02) {
                    msgDebug(" ... and returning with amount found and id = {$row['id']}");
                    return $row['id'];
                }
            }
        }
        msgDebug(" ... and returning with no tax id found!");
        return 0; // no tax data found
    }

    private function guessShipMethod($method='')
    {
        $defCarrier = false;
        $defMethod  = false;
        $carriers = getModuleCache('extShipping', 'carriers');
        foreach ($carriers as $id => $carrier) {
            msgDebug("\nCarrier $id");
            if (!$carrier['status']) { continue; }
            if (!$defCarrier) { $defCarrier = $id; }
            bizAutoLoad($carrier['path']."$id.php");
            $fqdn = "\\bizuno\\$id";
            $cAdmin = new $fqdn();
            if (empty($cAdmin->rateCodes)) { continue; }
            $codes = array_keys($cAdmin->rateCodes);
            foreach ($codes as $code) {
//                msgDebug("\nMethod $id with method code $code comparing with $method");
                if (!$defMethod) { $defMethod = $cAdmin->rateCodes[$code]; }
                if (strpos(strtoupper($method), strtoupper($code)) !== false) {
                    $output = "$id:{$cAdmin->rateCodes[$code]}";
                    msgDebug("\nFOUND MATCH, EXITING with code: $output");
                    return $output;
                }
            }
        }
        return $defCarrier ? "$defCarrier:$defMethod" : '';
    }

    /**
     * Fetches the list of SKUs to upload
     * @param unknown $result
     * @return multitype:multitype:multitype:unknown
     */
    public function apiInvCount(&$layout=[], $result=[])
    {
        $output = [];
        foreach ($result as $row) { $output[] = $row['id']; }
        $layout = array_replace_recursive($layout, ['content' => ['items' => $output]]);
    }

    /**
     *
     * @param array $product - array of filtered product data indexed by the product Tag name
     * @param string $url - remote site to send data
     * @param string $user - remote site username
     * @param string $pass - remote site password
     * @return array $data - structure of what to do next
     */
    protected function apiInventory(&$layout, $product, $url, $user, $pass, $quiet=0)
    {
        $rID = clean($product['RecordID'], 'integer');
        if (!$rID) { return msgDebug("\nBad ID passed. Needs to be the inventory field id tag name (RecordID)."); }
        // Extract the sales tax
        if (isset($product['TaxRateIDCustomer']) && $product['TaxRateIDCustomer'] > 0) {
            $rate = dbGetRow(BIZUNO_DB_PREFIX."tax_rates", "id={$product['TaxRateIDCustomer']}");
            $product['TaxRateTitle']   = $rate['title'];
            $product['TaxRatePercent'] = $rate['tax_rate'];
        } // else leave blank and use cart default tax setting
        unset($product['TaxRateIDCustomer']);
        unset($product['TaxRateIDVendor']);
        // load prices
        bizAutoLoad(BIZUNO_LIB."controller/module/inventory/prices.php", 'inventoryPrices');
        $prices = new inventoryPrices();
        $_GET['rID'] = $rID;
        $pDetails = [];
        $prices->quote($pDetails);
//      msgDebug("\nRetrieved price data in api = ".print_r($pDetails, true));
        $product['Price'] = $pDetails['content']['price'];
        if (!empty($pDetails['content']['regular_price'])){ $product['RegularPrice']= $pDetails['content']['regular_price']; }
        if (!empty($pDetails['content']['sale_price']))   { $product['SalePrice']   = $pDetails['content']['sale_price']; }
        if (isset($pDetails['content']['sheets']) && sizeof($pDetails['content']['sheets']) > 0) { $product['PriceLevels'] = $pDetails['content']['sheets']; }
        $product['WeightUOM']   = getModuleCache('inventory', 'settings', 'general', 'weight_uom', 'LB');
        $product['DimensionUOM']= getModuleCache('inventory', 'settings', 'general', 'dim_uom', 'IN');
        if (!empty($product['sendImage'])) { $this->setImage($product); }
        else                               { unset($product['Image']); }
        $this->setAccessories($product);
        $this->setAttributes ($product);
        $request = [
            'UserID'=> $user,
            'UserPW'=> $pass,
            'Language'=> getUserCache('profile', 'language', false, 'en_US'),
            'Version' => "1.0",
            'Function'=> "",
            'Action'  => "uploadProduct",
            'Product' => json_encode($product)];
        msgDebug("\nSending Product: ".print_r($product, true));
        $data = [
            'product'     => $product, // keep for mods
            'curlAction'  => ['url'=>$url,'mode'=>'post','data'=>$request,'quiet'=>$quiet,'opts'=>['useragent'=>true]],
            'curlResponse'=> ['module'=>'bizuno','page'=>'api','method'=>'apiInventoryResp']];
        $layout = array_replace_recursive($layout, $data);
    }

    /**
     * Sets the image path and populates image data, if present
     * @param type $product
     */
    private function setImage(&$product)
    {
        $product['ProductImageData'] = $product['ProductImageDirectory'] = $product['ProductImageFilename'] = '';
        if (isset($product['Image']) && $product['Image']) { // image file
            if (strpos($product['Image'], '/') !== false) { // path exists, extract
                $product['ProductImageDirectory'] = substr($product['Image'], 0, strrpos($product['Image'], '/'));
                $product['ProductImageFilename']  = substr($product['Image'], strrpos($product['Image'], '/')+1);
            } else {
                $product['ProductImageDirectory'] = '';
                $product['ProductImageFilename']  = $product['Image'];
            }
            $io = new \bizuno\io();
            $image = $io->fileRead("images/{$product['Image']}");
            if ($image) { $product['ProductImageData'] = base64_encode($image['data']); }
        }
    }

    /**
     * Sets the accessory fields used when uploading inventory
     * @param array $product - working product data
     * @return array - modified $product
     */
    private function setAccessories(&$product)
    {
        if (isset($product['invAccessory'])) {
            $vals = json_decode($product['invAccessory'], true);
            if (!is_array($vals)) { return; }
            unset($product['invAccessory']);
            foreach ($vals as $rID) {
                $product['invAccessory'][] = dbGetValue(BIZUNO_DB_PREFIX."inventory", 'sku', "id=$rID");
            }
        }
    }

    /**
     * Sets the product attributes
     * @param array $product - working product data
     * @return array - modified $product
     */
    private function setAttributes(&$product)
    {
        if (!isset($product['invAttrCat'])) { return; }
        $cat= clean($product['invAttrCat'], ['format'=>'text', 'default'=>'']);
        $i  = 0;
        $attributes = getModuleCache('inventory', 'attr');
        if (!$cat || !sizeof($attributes)) { return; }
        foreach ($attributes as $title => $values) {
            if ($cat != $title) { continue; }
            while (true) {
                $tag = 'invAttr'.substr('0'.$i, -2);
                if (isset($values[$i]) && isset($product[$tag])) {
                    $product['Attributes'][] = ['title'=>$values[$i], 'value'=>$product[$tag]];
                    unset($product[$tag]);
                    $i++;
                } else {
                    break;
                }
            }
        }
    }

    /**
     * This handles the response of the cart for apiInventory.
     * @param array $layout - working structure
     */
    public function apiInventoryResp(&$layout)
    {
        global $msgStack;
        $this->apiResp($layout);
        if (isset($layout['curlAction']['quiet']) && $layout['curlAction']['quiet']) { unset($msgStack->error['success']); } // hide the success message (for bulk upload)
    }

    /**
     * Method to sync product listings in Bizuno with shopping carts
     * @param array $layout -  current working structure
     * @param string $url - URL to send request to
     * @param string $user - user name at the cart
     * @param string $pass - password at the cart
     * @param string $field - field in Bizuno inventory table to look for cart enabled products
     * @param boolean $match - [default false] flag to delete at the cart, if present at cart and no flagged in Bizuno to sell
     */
    protected function apiSync(&$layout, $url, $user, $pass, $field='cartname_sync', $match=false)
    {
        $output = ['syncDelete' => $match?'1':'0'];
        $result = dbGetMulti(BIZUNO_DB_PREFIX."inventory", "`$field`='1' AND inactive='0'");
        foreach ($result as $row) { $output['Product'][] = $row['sku']; }
        msgDebug("Sent aipSync content = ".print_r($output, true));
        $request = [
            'UserID'=> $user,
            'UserPW'=> $pass,
            'Language'=> getUserCache('profile', 'language', false, 'en_US'),
            'Version' => "1.0",
            'Function'=> "",
            'Action'  => "syncProduct",
            'SKUs'    => json_encode($output),
            ];
        $data = [
            'skus'        => $output, // keep for mods
            'curlAction'  => ['url'=>$url,'mode'=>'post','data'=>$request],
            'curlResponse'=> ['module'=>'bizuno','page'=>'api','method'=>'apiResp']];
        $layout = array_replace_recursive($layout, $data);
    }

    /**
     * Sends to ship confirmation status from Bizuno to the cart to update order status
     * @param array $layout - working structure
     * @param string $url - URL of the cart to send request
     * @param string $user - username at the cart to log in
     * @param string $pass - password at the cart to log in
     * @param boolean $confirmDate - date used to set confirmation of shipments
     */
    protected function apiConfirm(&$layout, $url, $user='', $pass='', $confirmDate=false)
    {
        if (!$confirmDate) { $confirmDate = date('Y-m-d'); }
        $output = [];
        $this->confirmShip   ($output, $confirmDate);
//        $this->confirmPartial($output, $confirmDate);
//        $this->confirmCancel ($output, $confirmDate);
//        $this->confirmReturn ($output, $confirmDate);
        if (!sizeof($output)) { return msgAdd('No shipments could be found for this date!', 'success'); }
        $request = [
            'UserID'=> $user,
            'UserPW'=> $pass,
            'Language'=> getUserCache('profile', 'language', false, 'en_US'),
            'Version' => "1.0",
            'Function'=> "",
            'Action'  => "shipConfirm",
            'Order'   => json_encode($output)];
        msgDebug("Ready to send, sizeof request = ".sizeof($request));
        $data = ['sales'  => $output, // keep for mods
            'curlAction'  => ['url'=>$url,'mode'=>'post','data'=>$request],
            'curlResponse'=> ['module'=>'bizuno','page'=>'api','method'=>'apiResp']];
        $layout = array_replace_recursive($layout, $data);
    }

    /**
     * Test to see if Bizuno can connect to the cart
     * @param array $layout
     * @param string $url - URL to send request to
     * @param string $user - username to use to log in at the cart
     * @param string $pass - password to use to log in at the cart
     * @return modified $layout
     */
    public function apiTestCon(&$layout, $url='', $user='', $pass='')
    {
        if ($url == '') {
            $encoded = clean('data', 'text', 'get');
            $request_info = explode(' ', $encoded, 3);
            $url =  $request_info[0];
            $user = $request_info[1];
            $pass = $request_info[2];
        }
        if (!is_string($url) && (preg_match('/[^https:\/\/,^http:\/\/]/', $url) !== 1)) { return msgAdd("Bad url: $url"); }
        $request = [
            'UserID'  => $user,
            'UserPW'  => $pass,
            'bizunoLang'=> getUserCache('profile', 'language', false, 'en_US'),
            'Version' => "1.0",
            'Action'  => "testCon"];
        $data = [
            'curlAction'  => ['url'=>$url,'mode'=>'post','data'=>$request],
            'curlResponse'=> ['module'=>'bizuno','page'=>'api','method'=>'apiResp']];
        $layout = array_replace_recursive($layout, $data);
    }

    /**
     * Handles the response back from curl and puts all messages into the messages stack class
     * @param $layout - working structure
     */
    public function apiResp(&$layout)
    {
        global $msgStack;
        msgDebug("\nReceived back from API: ".print_r($layout['cURLresp'], true));
        $results = json_decode($layout['cURLresp'], true);
        if ($results == '' || !is_array($results)) {
            if (isset($this->moduleID) && getModuleCache($this->moduleID, 'settings', 'general', 'mode')) {
                $mode = getModuleCache($this->moduleID, 'settings', 'general', 'mode');
                $url  = getModuleCache($this->moduleID, 'settings', $mode, 'url');
                return msgAdd("Check your website url for errors: $url, can not connect.", 'trap');
            }
            return msgAdd("Check your website url for errors, can not connect.", 'trap');
        }
        // if any results are sent back, process them here
        $msgStack->error = array_merge($msgStack->error, $results); // cURL messages to messageStack
    }

    /**
     * This method handles orders that have shipped on the specified date
     * @param array $output - working list of orders and shipping information
     * @param string $confirmDate - date to use as the ship date of the order
     */
    private function confirmShip(&$output, $confirmDate=false)
    {
        if (!$confirmDate) { return; }
        if (!dbTableExists(BIZUNO_DB_PREFIX."extShipping")) {
            return msgAdd("The shipping extension is required for this operation.");
        }
        $result = dbGetMulti(BIZUNO_DB_PREFIX."extShipping", "ship_date LIKE '$confirmDate%'");
        if (sizeof($result) == 0) { return msgAdd($this->lang['msg_no_shipments_found'], 'caution'); }
        // process the ship list
        foreach ($result as $value) {
            $invoice_num = strpos($value['ref_id'], '-') ? substr($value['ref_id'], 0, strpos($value['ref_id'], '-')) : $value['ref_id'];
            $value['order_num'] = dbGetValue(BIZUNO_DB_PREFIX."journal_main", 'purch_order_id', "invoice_num='$invoice_num'");
            $order_num = $value['order_num'] ? $value['order_num'] : $invoice_num;
            $method_text = viewProcess($value['method_code'], 'shipInfo');
            if (isset($output[$order_num]['tracking_id'])) {
                $output[$order_num]['Tracking'] .= ', '.$value['tracking_id'];
            } else {
                $output[$order_num]['Tracking'] = 'Your order shipped '.viewDate($confirmDate).' via '.$method_text.', tracking number(s): '.$value['tracking_id'];
            }
            if (isset($output[$order_num]['notes'])) {
                $output[$order_num]['Notes'] .= '. '.$value['notes'];
            } else {
                if ($value['notes']) { $output[$order_num]['Notes'] = lang('notes').': '.$value['notes']; }
            }
            $output[$order_num]['Method']   = $method_text;
            $output[$order_num]['TrackID']  = $value['tracking_id'];
            $output[$order_num]['Status']   = 'orderShipped';
            $output[$order_num]['ShipDate'] = $confirmDate;
        }
    }

    /**
     * This method handles orders that have partially shipped on the specified date
     */
    private function confirmPartial()
    {
    }

    /**
     * This method handles orders that have been canceled prior to being shipped
     */
    private function confirmCancel()
    {
    }

    /**
     * This method handles orders from the origin that were refunded or returned
     */
    private function confirmReturn()
    {
    }

    /**
     * Install common fields into inventory db table shared amongst all the interfaces
     */
    protected function installStoreFields()
    {
        $id1 = validateTab($module_id='inventory', 'inventory', lang('details'), 60);
        if (!dbFieldExists(BIZUNO_DB_PREFIX."inventory", 'description_long')){ dbGetResult("ALTER TABLE ".BIZUNO_DB_PREFIX."inventory ADD description_long TEXT COMMENT 'type:textarea;label:Long Description;tag:DescriptionLong;tab:$id1;order:10'"); }
        $id = validateTab($module_id='inventory', 'inventory', lang('estore'), 80);
        if (!dbFieldExists(BIZUNO_DB_PREFIX."inventory", 'manufacturer'))    { dbGetResult("ALTER TABLE ".BIZUNO_DB_PREFIX."inventory ADD manufacturer VARCHAR(24) NOT NULL DEFAULT '' COMMENT 'label:Manufacturer;tag:Manufacturer;tab:$id;order:40'"); }
        if (!dbFieldExists(BIZUNO_DB_PREFIX."inventory", 'model'))           { dbGetResult("ALTER TABLE ".BIZUNO_DB_PREFIX."inventory ADD model VARCHAR(24) NOT NULL DEFAULT '' COMMENT 'label:Model;tag:Model;tab:$id;order:41'"); }
        if (!dbFieldExists(BIZUNO_DB_PREFIX."inventory", 'msrp'))            { dbGetResult("ALTER TABLE ".BIZUNO_DB_PREFIX."inventory ADD msrp DOUBLE NOT NULL DEFAULT '0' COMMENT 'label:Mfg Suggested Retail Price;tag:MSRPrice;tab:$id;order:42'"); }
        if (!dbFieldExists(BIZUNO_DB_PREFIX."inventory", 'meta_keywords'))   { dbGetResult("ALTER TABLE ".BIZUNO_DB_PREFIX."inventory ADD meta_keywords VARCHAR(255) NOT NULL DEFAULT '' COMMENT 'label:Meta Keywords;tag:MetaKeywords;tab:$id;o rder:90;group:General'"); }
         if (!dbFieldExists(BIZUNO_DB_PREFIX."inventory", 'meta_description')){ dbGetResult("ALTER TABLE ".BIZUNO_DB_PREFIX."inventory ADD meta_description VARCHAR(255) NOT NULL DEFAULT '' COMMENT 'label:Meta Description;tag:MetaDescription;tab:$id;order:91;group:General'"); }
        bizAutoLoad(BIZUNO_LIB."controller/module/inventory/admin.php", 'inventoryAdmin');
        $inv = new inventoryAdmin();
        $inv->installPhysicalFields();
    }

    /**
     * Removes common fields from the inventory db table
     *
     * NOTE: No fields are removed as they may be used by other modules
     */
    protected function removeStoreFields()
    {
        // DO not remove any fields as they may be used by another cart.
    }
}
