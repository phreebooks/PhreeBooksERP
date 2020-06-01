<?php
/*
 * Functions related to inventory pricing for customers and vendors
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
 * @copyright  2008-2020, PhreeSoft, Inc.
 * @license    http://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 * @version    4.x Last Update: 2020-05-27
 * @filesource /lib/controller/module/inventory/prices.php
 */

namespace bizuno;

class inventoryPrices
{
    public $moduleID = 'inventory';

    function __construct()
    {
        $this->lang     = getLang($this->moduleID);
        $this->type     = clean('type', ['format'=>'char', 'default'=>'c'], 'get');
        $this->helpIndex= $this->type=='v' ? "vendor-prices" : "customer-prices";
        $this->qtySource= ['1'=>lang('direct_entry'), '2'=>lang('inventory_item_cost'), '3'=>lang('inventory_full_price'), '4'=>lang('price_level_1')];
        $this->qtyAdj   = ['0'=>lang('none'), '1'=>lang('decrease_by_amount'), '2'=>lang('decrease_by_percent'), '3'=>lang('increase_by_amount'), '4'=>lang('increase_by_percent')];
        $this->qtyRnd   = ['0'=>lang('none'), '1'=>lang('next_integer'), '2'=>lang('next_fraction'), '3'=>lang('next_increment')];
    }

    /**
     * Entry point for the prices manager
     * @param array $layout - structure for the main inventory prices page
     * @return modified $layout
     */
    public function manager(&$layout=[])
    {
        if (!$security = validateSecurity('inventory', 'prices_'.$this->type, 1)) { return; }
        $mID  = clean('mID','alpha_num','get');
        $cID  = clean('cID','integer',  'get');
        $iID  = clean('iID','integer',  'get');
        $mod  = clean('mod','text',     'get');
        $title= sprintf(lang('tbd_prices'), lang('contacts_type_'.$this->type));
        $data = ['type'=>'page','title'=>$title,
            'divs'     => [
                'prices' => ['order'=>50,'type'=>'accordion','key'=>'accPrices']],
            'accordion'=> ['accPrices'=>['divs'=>[
                'divPricesMgr' =>['order'=>30,'label'=>$title,'type'=>'datagrid','key'=>'dgPricesMgr'],
                'divPricesSet' =>['order'=>50,'label'=>lang('settings'),'type'=>'html',    'html'=>"<p>".$this->lang['msg_no_price_sheets']."</p>"]]]],
            'datagrid' => ['dgPricesMgr' => $this->dgPrices('dgPricesMgr', $this->type, $security, $mID, $cID, $iID, $mod)]];
        if ($mod) { // if mod then in a tab of some sort
            $data['type'] = 'divHTML'; // just the div html
            $layout = array_replace_recursive($layout, $data);
            return;
        }
        $layout = array_replace_recursive($layout, viewMain(), $data);
    }

    /**
     * This method pulls the data from the database to populate the datagrid
     * @param $layout - Structure coming in
     * @return array datagrid structure to load data from database
     */
    public function managerRows(&$layout=[])
    {
        $mID  = clean('mID','alpha_num','get');
        $cID  = clean('cID','integer',  'get');
        $iID  = clean('iID','integer',  'get');
        if (!$security = validateSecurity('inventory', 'prices_'.$this->type, 1)) { return; }
        $_POST['search'] = getSearch('search');
        msgDebug("\n ready to build prices datagrid, security = $security");
        $structure = $this->dgPrices('dgPricesMgr', $this->type, $security, $mID, $cID, $iID);
        $layout = array_replace_recursive($layout, ['type'=>'datagrid','key'=>'dgPricesMgr','datagrid'=>['dgPricesMgr'=>$structure]]);
    }

    /**
     * Stores the users preferences for filters
     */
    private function managerSettings()
    {
        $data = ['path'=>'invPrices'.$this->type, 'values'=>  [
            ['index'=>'rows',  'clean'=>'integer','default'=>getModuleCache('bizuno', 'settings', 'general', 'max_rows')],
            ['index'=>'page',  'clean'=>'integer','default'=>'1'],
            ['index'=>'sort',  'clean'=>'text',   'default'=>'method'],
            ['index'=>'order', 'clean'=>'text',   'default'=>'ASC'],
            ['index'=>'search','clean'=>'text',   'default'=>'']]];
        $this->defaults = updateSelection($data);
    }

    /**
     * Structure to add a new price sheet
     * @param array $layout - Structure coming in
     * @return modified $layout
     */
    public function add(&$layout=[])
    {
        $mod  = clean('mod', 'text', 'get');
        $meths= [];
        if (!sizeof(getModuleCache('inventory', 'prices'))) {
            msgAdd("Please add some price methods first, My Business -> Settings -> Inventory Module -> Prices tab");
            $html = '&nbsp';
        } else {
            foreach (getModuleCache('inventory', 'prices') as $mID => $settings) {
                if (!$settings['status']) { continue; }
                $fqcn = "\\bizuno\\$mID";
                bizAutoLoad(getModuleCache('inventory','prices')[$mID]['path']."$mID.php", $fqcn);
                $priceSet = getModuleCache('inventory','prices',$mID,'settings');
                $tmp = new $fqcn($priceSet);
                if (strlen($mod) == 0 || (isset($tmp->structure['hooks']) && array_key_exists($mod, $tmp->structure['hooks']))) {
                    if ($settings['status']) { $meths[] = ['id'=>$mID, 'text'=>$settings['title']]; }
                }
            }
            $html  = '<p>'.lang('desc_new_price_sheets')."</p>";
            $html .= html5('methodID',['values'=>$meths,'attr'=>['type'=>'select']]);
            $html .= html5('iconGO',  ['icon'=>'next','events'=>['onClick'=>"accordionEdit('accPrices','dgPricesMgr','divPricesSet','".jsLang('settings')."','inventory/prices/edit&type=$this->type&mod=$mod&mID='+bizSelGet('methodID'),0); bizWindowClose('winNewPrice');"]]);
        }
        $layout = array_replace_recursive($layout, ['type'=>'divHTML','divs'=>['winNewPrice'=>['order'=>50,'type'=>'html','html'=>$html]]]);
    }

    /**
     * This method is a wrapper to set up the edit structure, it requires the specific method to populate the form
     * @param string $layout - typically the $_GET variables containing the necessary variables
     * @return array - structure to render the detail editor HTML
     */
    public function edit(&$layout=[])
    {
        if (!$security = validateSecurity('inventory', 'prices_'.$this->type, 1)) { return; }
        $rID = clean('rID', 'integer', 'get');
        $mID = clean('mID', ['format'=>'text','default'=>'quantity'], 'request');
        $structure = dbLoadStructure(BIZUNO_DB_PREFIX."inventory_prices");
        if ($rID) {
            $row     = dbGetRow(BIZUNO_DB_PREFIX."inventory_prices", "id=$rID");
            $settings= json_decode($row['settings'], true);
            $mID     = $row['method'];
        } else { // set the defaults
            $row     = ['id'=>0, 'method'=>$mID, 'contact_type'=>$this->type];
            $settings= ['attr'=>'', 'title'=>''];
        }
        $row['currency'] = getDefaultCurrency(); // force currency to be the users default
        unset($structure['settings']);
        $structure['contact_id']['label']   = lang('contacts_short_name');
        $structure['inventory_id']['label'] = lang('sku');
        $structure['ref_id']['attr']['type']= 'select';
        $structure['ref_id']['label']       = $this->lang['price_sheet_to_override'];
        $structure['ref_id']['values']      = $this->quantityList();

        dbStructureFill($structure, $row);
        $data = ['type'=>'divHTML',
            'divs'    => ['tbPrices'=>['order'=> 1,'type'=>'toolbar','key'=>'tbPrices']],
            'toolbars'=> ['tbPrices'=>['icons'=>[
                'save' => ['order'=>40,'hidden'=>$security>1?false:true,'events'=>['onClick'=>"if (preSubmitPrices()) divSubmit('inventory/prices/save&type=$this->type&mID=$mID', 'divPricesSet');"]]]]],
            'fields'  => $structure,
            'values'  => ['qtySource'=>$this->qtySource, 'qtyAdj'=>$this->qtyAdj, 'qtyRnd'=>$this->qtyRnd]];
        $fqcn = "\\bizuno\\$mID";
        bizAutoLoad(getModuleCache('inventory', 'prices')[$mID]['path']."$mID.php", $fqcn);
        $priceSet = getModuleCache('inventory','prices',$mID,'settings');
        $meth = new $fqcn($priceSet);
        msgDebug("\nSettings = ".print_r($settings, true));
        $meth->priceRender($data, $settings);
        $layout = array_replace_recursive($layout, $data);
    }

    /**
     * Method to save a new/edited price sheet
     * @param type $layout
     * @return type
     */
    public function save(&$layout=[])
    {
        $mID = clean('mID', 'text', 'get');
        $rID = clean('id'.$mID, 'text', 'post');
        $_POST['contact_type'.$mID] = $this->type;
        if (!$mID) { return msgAdd('Cannot save, no method passed!'); }
        if (!$security = validateSecurity('inventory', 'prices_'.$this->type, $rID?3:2)) { return; }
        $fqcn = "\\bizuno\\$mID";
        bizAutoLoad(getModuleCache('inventory', 'prices')[$mID]['path']."$mID.php", $fqcn);
        $priceSet = getModuleCache('inventory','prices',$mID,'settings');
        $meth = new $fqcn($priceSet);
        if ($meth->priceSave()) { msgAdd(lang('msg_record_saved'), 'success'); }
        $layout = array_replace_recursive($layout, ['content'=>  ['action'=>'eval','actionData'=>"jq('#accPrices').accordion('select', 0); bizGridReload('dgPricesMgr'); jq('#divPricesSet').html('&nbsp;');"]]);
    }

    /**
     * Copies a price sheet to a newly named price sheet with all settings
     * @param array $layout - structure coming in
     * @return array - modified $layout
     */
    public function copy(&$layout=[])
    {
        if (!$security = validateSecurity('inventory', 'prices_'.$this->type, 2)) { return; }
        $rID     = clean('rID', 'integer','get');
        $newTitle= clean('data','text',   'get');
        $sheet   = dbGetRow(BIZUNO_DB_PREFIX."inventory_prices", "id=$rID");
        $settings= json_decode($sheet['settings'], true);
        $oldTitle= isset($settings['title']) ? $settings['title'] : '';
        $dup     = dbGetMulti(BIZUNO_DB_PREFIX."inventory_prices", 'settings', "id<>$rID AND contact_type='$this->type'");
        foreach ($dup as $row) {
            $props = json_decode($row['settings'], true);
            if ($props['title'] == $settings['title']) { return msgAdd(lang('duplicate_title')); }
        }
        unset($sheet['id']);
        foreach ($settings as $key => $value) {
          switch ($key) {
            case 'title':       $settings[$key] = $newTitle;     break;
            case 'last_update': $settings[$key] = date('Y-m-d'); break;
            default: // leave them alone
          }
        }
        $sheet['settings'] = json_encode($settings);
        $nID = $_GET['nID'] = dbWrite(BIZUNO_DB_PREFIX."inventory_prices", $sheet);
        msgLog(lang('prices').' '.lang('copy')." - $oldTitle => $newTitle");
        $layout = array_replace_recursive($layout, ['content' => ['action'=>'eval','actionData'=>"accordionEdit('accPrices', 'dgPricesMgr', 'divPricesSet', '".lang('settings')."', 'inventory/prices/edit', $nID);"]]);
    }

    /**
     * Deletes a price sheet from the database
     * @param array $layout - structure coming in
     * @return array - modified $layout
     */
    public function delete(&$layout=[])
    {
        if (!$security = validateSecurity('inventory', 'prices_'.$this->type, 4)) { return; }
        $rID = clean('rID', 'integer', 'get');
        if (!$rID) { return msgAdd('The record was not deleted, the proper id was not passed!'); }
        $result   = dbGetRow(BIZUNO_DB_PREFIX."inventory_prices", "id=$rID");
        $settings = json_decode($result['settings'], true);
        msgLog(lang('prices').' '.lang('delete')." - Title: ".(isset($settings['title']) ? $settings['title'] : '-')." (iID=".$result['inventory_id']."; cID=".$result['contact_id']."; rID=$rID)");
        $layout = array_replace_recursive($layout, [
            'content' => ['action'=>'eval','actionData'=>"jq('#accPrices').accordion('select', 0); bizGridReload('dgPricesMgr'); jq('#divPricesSet').html('&nbsp;');"],
            'dbAction'=> ["inventory_prices"=>"DELETE FROM ".BIZUNO_DB_PREFIX."inventory_prices WHERE id=$rID OR ref_id=$rID"]]);
    }

    /**
     * retrieves the price sheet details for a given SKU to create a pop up window
     * @param array $layout - structure coming in
     * @return array - modified $layout
     */
    public function details(&$layout=[])
    {
        if (!$security = validateSecurity('inventory', 'inv_mgr', 1)) { return; }
        $rID   = clean('rID', 'integer','get');
        $sku   = clean('sku', 'text',   'get');
        $cID   = clean('cID', 'text',   'get');
        if     ($rID) { $inv = dbGetRow(BIZUNO_DB_PREFIX."inventory", "id=$rID"); }
        elseif ($sku) { $inv = dbGetRow(BIZUNO_DB_PREFIX."inventory", "sku='$sku'"); }
        else   { return msgAdd("Bad SKU sent!"); }
        $cost  = clean('itemCost', ['format'=>'float','default'=>$inv['item_cost']], 'get');
        $full  = clean('fullPrice',['format'=>'float','default'=>$inv['full_price']],'get');
        $prices= [];
        $this->quote($prices, $cost, $full, $sku, $cID);
        $rows[]= ['group'=>lang('general'),'text'=>"<div style='float:right'>".viewFormat($prices['content']['price'], 'currency').'</div><div>'.lang('price')."</div>"];
        $rows[]= ['group'=>lang('general'),'text'=>"<div style='float:right'>".viewFormat($full, 'currency').'</div><div>'.lang('inventory_full_price')."</div>"];
        if (validateSecurity('phreebooks', 'j6_mgr', 1, false)) {
            $rows[] = ['group'=>lang('general'),'text'=>"<div style='float:right'>".viewFormat($cost, 'currency').'</div><div>'.lang('inventory_item_cost')."</div>"];
        }
        if (!empty($prices['content']['sheets'])) { foreach ($prices['content']['sheets'] as $level) {
            $rows[] = ['group'=>$level['title'],'text'=>"<div style='float:right'>".lang('price').'</div><div>'.lang('qty')."</div>"];
            foreach ($level['levels'] as $entry) {
                $rows[] = ['group'=>$level['title'],'text'=>"<div style='float:right'>".viewFormat($entry['price'], 'currency').'</div><div>'.(float)$entry['qty']."</div>"];
            }
        } }
        $data = ['type'=>'popup', 'title'=>lang('inventory_prices', $this->type), 'attr'=>['id'=>'winPrices','width'=>300,'height'=>700],
            'divs'  => ['winStatus'=>['order'=>50,'options'=>['groupField'=>"'group'",'data'=>"pricesData"],'type'=>'list','key' =>'lstPrices']],
            'lists' => ['lstPrices'=>[]], // handled as JavaScript data
            'jsHead'=> ['init'=>"var pricesData = ".json_encode($rows).";"]];
        $layout = array_merge_recursive($layout, $data);
    }

    /**
     * Retrieves the best price for a given customer/SKU using available price sheets
     * @param array $layout - structure coming in
     * @return array - modified $layout
     */
    public function quote(&$layout=[], $cost=0, $full=0, $sku='', $cID=0)
    {
        $iSec = validateSecurity('inventory', 'prices_'.$this->type, 1, false);
        $pSec = $this->type=='v' ? validateSecurity('phreebooks', 'j6_mgr', 1, false) : validateSecurity('phreebooks', 'j12_mgr', 1, false);
        if (!$security = max($iSec, $pSec)) { return msgAdd(lang('err_no_permission')." [".'prices_'.$this->type." OR jX_mgr]"); }
        if (empty($cID)) { $cID = clean('cID', 'integer','get'); } // contact record ID
        if (empty($sku)) { $sku = clean('sku', 'text',   'get'); } // inventory SKU
        $iID = clean('rID', 'integer','get'); // inventory record ID
        $UPC = clean('upc', 'text',   'get'); // inventory UPC Code
        $qty = clean('qty', ['format'=>'float', 'default'=>1], 'get'); // quantity purchased, assume 1
        if (!empty($cID)) {
            $contact = dbGetValue(BIZUNO_DB_PREFIX.'contacts', ['type', 'price_sheet'], "id=$cID");
        } else {
            $contact = ['type'=>$this->type, 'price_sheet'=>''];
        }
        if     (empty($iID) && !empty($sku)) { $filter = "sku='$sku'"; }
        elseif (empty($iID) && !empty($UPC)) { $filter = "upc='$UPC'"; }
        else                                 { $filter = "id = $iID";  }
        $inv = dbGetValue(BIZUNO_DB_PREFIX.'inventory', ['id', 'item_cost', 'full_price', 'price_sheet_c', 'price_sheet_v'], $filter);
        if (empty($inv['id'])) { return; }
        if ($cost) { $inv['item_cost'] = $cost; }
        if ($full) { $inv['full_price']= $full; }
        if (!$inv['price_sheet_c']) { // if no customer price sheet, see if a default is set
            $result = dbGetMulti(BIZUNO_DB_PREFIX."inventory_prices", "method='quantity'");
            foreach ($result as $row) {
                $settings = json_decode($row['settings'], true);
                if (isset($settings['default']) && $settings['default']) { $inv['price_sheet_c'] = $row['id']; }
            }
        }
        if (!$contact['price_sheet']) { $contact['price_sheet'] = $inv['price_sheet_c']; } // if not set, set to inventory default
        $this->values = [
            'iID'=>$inv['id'],'iSheetc'=>$inv['price_sheet_c'],  'iSheetv'=>$inv['price_sheet_v'], 'iCost'=>$inv['item_cost'], 'iList'=>$inv['full_price'],
            'cID'=>$cID,      'cSheet' =>$contact['price_sheet'],'cType'  =>$contact['type'],
            'qty'=>abs($qty)]; // to properly handle negative sales/purchases and still get pricing based on method
        msgDebug("\nFinding pricing with qty = $qty and values = ".print_r($this->values, true));
        $this->prices = [];
        $this->pricesLevels($this->prices, $this->values);
      msgDebug("\nPrice return array = ".print_r($this->prices, true));
        $layout = array_replace_recursive($layout, ['source'=>$this->values, 'content'=>$this->prices]);
    }

    /**
     * Determines the price matrix for a given SKU and customer
     * @param array $layout - structure coming in
     * @param string $sku [default: ''] - inventory item SKU
     * @param integer $cID [default: 0] - Contact ID, can be customer or vendor
     * @return array - price matrix for the given customer and SKU, default price sheet or special pricing applied
     */
    public function quoteLevels(&$layout=[], $sku='', $cID=0)
    {
        msgDebug("\nEntering quoteLevels with sku = $sku and cID = $cID");
        $this->quote($layout, 0, 0, $sku, $cID);
        // extract the default price tier
        if (!empty($this->prices['sheets']) && is_array($this->prices['sheets'])) {
            $sheets   = array_shift($this->prices['sheets']);
            $skuPrices= $sheets['levels']; // first sheet is the default from the quote method
        } else {
            $skuPrices= [['qty'=>1, 'price'=>$this->prices['price']]];
        }
        return $skuPrices;
    }

    /**
     * Retrieves the price levels for a given price sheet, sets the new low price if needed
     * @param array $prices - current working array with pricing values
     * @param array $values - contains information to retrieve proper price for a given SKU
     */
    public function pricesLevels(&$prices, $values)
    {
        if (sizeof(getModuleCache('inventory', 'prices'))) {
            foreach (getModuleCache('inventory', 'prices') as $meth => $settings) { // start with the sorted methods
                msgDebug("\nlooking at method = $meth with settings: ".print_r($settings, true));
                if (isset($settings['path'])) {
                    $fqcn = "\\bizuno\\$meth";
                    bizAutoLoad($settings['path']."$meth.php", $fqcn);
                    $priceSet = getModuleCache('inventory','prices',$meth,'settings');
                    $est = new $fqcn($priceSet);
                    if (method_exists($est, 'priceQuote')) { $est->priceQuote($prices, $values); }
                    msgDebug("\nprices after $meth = ".print_r($prices, true));
                }
            }
        }
        if (!isset($prices['price'])) { $prices['price'] = $values['cType']=='v' ? $values['iCost'] : $values['iList']; }
        $this->setDefaultSheet($prices, $values); // need to set default price saheet
    }

    /**
     * Check which sheet to set as default based on priority, contact, sku, quantity
     * @param array $prices - working pricing structure
     * @param array $values - settings for the quote request
     * @return type
     */
    private function setDefaultSheet(&$prices, $values)
    {
        if (empty($prices['sheets'])) { return; }
        $cSheet = $iSheet = $qSheet = 0;
        foreach ($prices['sheets'] as $key => $sheet) {
            if     ($values['cSheet']==$key)                                 { $cSheet = $key; }
            elseif (in_array($key, [$values['iSheetc'],$values['iSheetv']])) { $iSheet = $key; }
            elseif (!empty($sheet['default']))                               { $qSheet = $key; }
            $prices['sheets'][$key]['default'] = 0; // clear it no matter what
        }
        $idx = $cSheet ? $cSheet : ($iSheet ? $iSheet : ($qSheet ? $qSheet : 0));
        msgDebug("\nfinsihed sorting and cSheet = $cSheet, iSheet = $iSheet, qSheet = $qSheet and winner is: $idx");
        if ($idx) {
            $prices['sheets'][$idx]['default'] = 1; // set default
            $sheet = $prices['sheets'][$idx];
            unset($prices['sheets'][$idx]);
            array_unshift($prices['sheets'], $sheet); // reset default to top of array which forces a re-key
        } else { $prices['sheets'] = array_values($prices['sheets']); }   // re-key array as is
    }

    /**
     * Creates a list of available price sheets to use in a view drop down
     * @param char $type - contact type, choices are c and v
     * @param boolean $addNull - [default false] set to true to create a None option at the first position
     * @return array - list of method:quantity price sheets ready for render
     */
    public function quantityList($type='c', $addNull=false)
    {
        $output = [];
        $result = dbGetMulti(BIZUNO_DB_PREFIX."inventory_prices", "method='quantity' AND contact_type='$type'");
        foreach ($result as $row) {
            $settings = json_decode($row['settings'], true);
            $output[] = ['id'=>$row['id'], 'text'=>$settings['title']];
        }
        $temp = [];
        foreach ($output as $key => $value) { $temp[$key] = $value['text']; }
        array_multisort($temp, SORT_ASC, $output);
        if ($addNull) { array_unshift($output, ['id'=>0, 'text'=>lang('none')]); }
        return $output;
    }

    /**
     * Decodes a price sheet setting and returns lowest price and array of values
     * @param float $cost - Item cost as retrieved from inventory database table
     * @param float $full - Full price as retrieved from inventory database table
     * @param float $quan - Number of units to price
     * @param string $encoded_levels - Encoded price levels to build pricing array
     * @return array - calculated price after applying price sheet and pricing levels
     */
    protected function decodeQuantity($cost, $full, $quan, $encoded_levels)
    { // quantity level pricing
        $price_levels = explode(';', $encoded_levels);
        $prices       = [];
        $first_price  = 0;
        for ($i=0, $j=1; $i < sizeof($price_levels); $i++, $j++) {
            $level_info = explode(':', $price_levels[$i]);
            $price      = isset($level_info[0]) ? $level_info[0] : ($i==0 ? $full : 0);
            $qty        = isset($level_info[1]) ? $level_info[1] : $j;
            $src        = isset($level_info[2]) ? $level_info[2] : 0;
            $adj        = isset($level_info[3]) ? $level_info[3] : 0;
            $adj_val    = isset($level_info[4]) ? $level_info[4] : 0;
            $rnd        = isset($level_info[5]) ? $level_info[5] : 0;
            $rnd_val    = isset($level_info[6]) ? $level_info[6] : 0;
            switch ($src) {
                case 0: $price = 0;            break; // Not Used
                case 1:                        break; // Direct Entry
                case 2: $price = $cost;        break; // Last Cost
                case 3: $price = $full;        break; // Retail Price
                case 4: $price = $first_price; break; // Price Level 1
            }
            switch ($adj) {
                case 0:                                      break; // None
                case 1: $price -= $adj_val;                  break; // Decrease by Amount
                case 2: $price -= $price * ($adj_val / 100); break; // Decrease by Percent
                case 3: $price += $adj_val;                  break; // Increase by Amount
                case 4: $price += $price * ($adj_val / 100); break; // Increase by Percent
            }
            switch ($rnd) {
                case 0: // None
                    break;
                case 1: // Next Integer (whole dollar)
                    $price = ceil($price);
                    break;
                case 2: // Constant remainder (cents)
                    $remainder = $rnd_val;
                    if ($remainder < 0) { $remainder = 0; } // don't allow less than zero adjustments
                    // convert to fraction if greater than 1 (user left out decimal point)
                    if ($remainder >= 1) { $remainder = '.' . $rnd_val; }
                    $price = floor($price) + $remainder;
                    break;
                case 3: // Next Increment (round to next value)
                    $remainder = $rnd_val;
                    if ($remainder <= 0) { // don't allow less than zero adjustments, assume zero
                        $price = ceil($price);
                    } else {
                        $price = ceil($price / $remainder) * $remainder;
                    }
            }
            if ($j == 1) { $first_price = $price; } // save level 1 pricing
            if ($src) { $prices[$i] = ['qty' => $qty, 'price' => $price]; }
        }
        $price = 0;
        if (is_array($prices)) { foreach ($prices as $value) { if ($quan >= $value['qty']) { $price = $value['price']; } } }
        msgDebug("\nlooking at cost = $cost, full price = $full and quantity = $quan and encoded levels: $encoded_levels and ended price = $price");
        return ['price'=>$price, 'levels'=>$price ? $prices : []];
    }

    /**
     *
     * @param string $name - REQUIRED - datagrid ID
     * @param string $type - contact type, acceptable values are c or v
     * @param number $security - access control
     * @param text $mID - Method ID, if present will restrict output to specified method
     * @param integer $cID - Contact ID, if present will restrict output to specified contact
     * @param integer $iID - Inventory ID, if present will restrict output to specified inventory item
     * @return array structure
     */
    private function dgPrices($name, $type='c', $security=0, $mID='', $cID=0, $iID=0, $mod='')
    {
        $this->managerSettings();
        $data = ['id'=>$name, 'rows'=>$this->defaults['rows'], 'page'=>$this->defaults['page'],
            'attr'   => ['toolbar'=>"#{$name}Toolbar",'idField'=>'id', 'url'=>BIZUNO_AJAX."&p=inventory/prices/managerRows&type=$type".($mID?"&mID=$mID":'').($cID?"&cID=$cID":'').($iID?"&iID=$iID":'')],
            'events' => [
                'onDblClickRow'=> "function(rowIndex, rowData) { accordionEdit('accPrices','dgPricesMgr','divPricesSet','".jsLang('settings')."','inventory/prices/edit&type=$type".($mod?"&mod=$mod":'')."',rowData.id); }",
                'rowStyler'    => "function(index, row) { if (row.inactive==1) { return {class:'row-inactive'}; } if (row.default==1) { return {class:'row-default'}; } }"],
            'source' => [
                'tables' => [
                    'prices'   => ['table'=>BIZUNO_DB_PREFIX."inventory_prices"],
                    'inventory'=> ['table'=>BIZUNO_DB_PREFIX."inventory",'join'=>'JOIN','links'=>BIZUNO_DB_PREFIX."inventory_prices.inventory_id=".BIZUNO_DB_PREFIX."inventory.id"]],
                'search' => ['settings', 'method', 'sku', 'description_short', 'description_purchase', 'description_sales'],
                'actions'=> [
                    'newPrices'=> ['order'=>10,'icon'=>'new',  'events'=>['onClick'=>"windowEdit('inventory/prices/add&type=$type".($mod?"&mod=$mod":'').($cID?"&cID=$cID":'').($iID?"&iID=$iID":'')."','winNewPrice','".jsLang('inventory_prices_method')."',400,200);"]],
                    'clrPrices'=> ['order'=>50,'icon'=>'clear','events'=>['onClick'=>"bizTextSet('search', ''); ".$name."Reload();"]],
                    'help'     => ['order'=>99,'icon'=>'help', 'label' =>lang('help'),'align'=>'right','hideLabel'=>true,'index'=>$this->helpIndex]],
                'filters'=> [
                    'search'   => ['order'=>90,'html'  =>['attr'=>['id'=>'search','value'=>$this->defaults['search']]]],
                    'typePrice'=> ['order'=>99,'hidden'=>true,'sql'=>BIZUNO_DB_PREFIX."inventory_prices.contact_type='$type'"]],
                'sort' => ['s0' =>['order'=>10,'field' =>($this->defaults['sort'].' '.$this->defaults['order'])]]],
            'footnotes'=> ['codes'=>lang('color_codes').': <span class="row-default">'.lang('default').'</span>'],
            'columns'  => [
                'id'      => ['order'=>0,'field'=>BIZUNO_DB_PREFIX.'inventory_prices.id',      'attr'=>['hidden'=>true]],
                'inactive'=> ['order'=>0,'field'=>BIZUNO_DB_PREFIX.'inventory_prices.inactive','attr'=>['hidden'=>true]],
//              'currency'=> ['order'=>0,'field'=>BIZUNO_DB_PREFIX.'inventory_prices.currency','attr'=>['hidden'=>true]],
                'default' => ['order'=>0,'field'=>'settings:default','attr'=>['hidden'=>true]],
                'action'  => ['order'=>1,'label'=>lang('action'),'events'=>['formatter'=>"function(value,row,index){ return ".$name."Formatter(value,row,index); }"],
                    'actions'=> [
                        'edit' => ['order'=>30,'icon'=>'edit', 'hidden'=>$security>2?false:true,'events'=>['onClick'=>"accordionEdit('accPrices','dgPricesMgr','divPricesSet','".jsLang('settings')."','inventory/prices/edit&type=$type',idTBD);"]],
                        'copy' => ['order'=>30,'icon'=>'copy', 'hidden'=>$security>1?false:true,'events'=>['onClick'=>"var title=prompt('".lang('msg_entry_copy')."'); if (title!=null) jsonAction('inventory/prices/copy', idTBD, title);"]],
                        'trash'=> ['order'=>90,'icon'=>'trash','hidden'=>$security>3?false:true,'events'=>['onClick'=>"if (confirm('".jsLang('msg_confirm_delete')."')) jsonAction('inventory/prices/delete', idTBD);"]]]],
                'title'      => ['order'=>10, 'field'=>'settings:title',   'label'=>lang('title'),
                    'attr'=>  ['width'=>60, 'sortable'=>true, 'resizable'=>true]],
                'method'     => ['order'=>20, 'field'=>BIZUNO_DB_PREFIX.'inventory_prices.method',     'label'=>lang('method'),
                    'attr'=>  ['width'=>60, 'sortable'=>true, 'resizable'=>true]],
                'ref_id'     => ['order'=>30, 'field'=>BIZUNO_DB_PREFIX.'inventory_prices.ref_id',     'label'=>lang('reference'),'format'=>'dbVal;'.BIZUNO_DB_PREFIX.'inventory_prices;settings:title;id',
                    'attr'=>  ['width'=>80, 'sortable'=>true, 'resizable'=>true]],
                'contact_id' => ['order'=>40, 'field'=>BIZUNO_DB_PREFIX.'inventory_prices.contact_id', 'label'=>lang('address_book_primary_name'),'format'=>'contactName',
                    'attr'=>  ['sortable'=>true, 'resizable'=>true]],
                'inventory_id'=> ['order'=>50, 'field'=>BIZUNO_DB_PREFIX.'inventory.description_short','label'=>lang('description'),
                    'attr'=>  ['sortable'=>true, 'resizable'=>true]],
                'last_update'=> ['order'=>70, 'field'=>'settings:last_update',   'label'=>lang('last_update'),'format'=>'date',
                    'attr'=>  ['width'=>70, 'sortable'=>true, 'resizable'=>true]]]];
        $cList  = $iList = [];
        $search = addslashes($this->defaults['search']);
        if ($mID) { $data['source']['filters']['mID'] = ['order'=>99, 'hidden'=>true, 'sql'=>"method='$mID'"]; }
        if ($cID) { $data['source']['filters']['cID'] = ['order'=>99, 'hidden'=>true, 'sql'=>"contact_id=$cID"]; }
        elseif ($this->defaults['search']) { // see if searching within contact
            $contacts = dbGetMulti(BIZUNO_DB_PREFIX."address_book", "primary_name LIKE '%$search%'", "primary_name {$this->defaults['order']}", ['ref_id']);
            foreach ($contacts as $cID) { $cList[] = $cID['ref_id']; }
        }
        if ($iID) {
            $data['source']['filters']['iID'] = ['order'=>99, 'hidden'=>true, 'sql'=>"inventory_id=$iID"];
        } elseif ($this->defaults['search']) {
            $inventory = dbGetMulti(BIZUNO_DB_PREFIX."inventory", "description_short LIKE '%$search%'", "description_short {$this->defaults['order']}", ['id']);
            foreach ($inventory as $iID) { $iList[] = $iID['id']; }
        }
        if (sizeof($cList) && sizeof($iList)) {
            $data['source']['filters']['addSrch'] = ['order'=>99,'hidden'=>true, 'sql'=>BIZUNO_DB_PREFIX."inventory_prices.contact_id IN (".implode(',',$cList).") OR inventory_prices.inventory_id IN (".implode(',',$iList).")"];
        } elseif (sizeof($cList)) {
            $data['source']['filters']['addSrch'] = ['order'=>99,'hidden'=>true, 'sql'=>BIZUNO_DB_PREFIX."inventory_prices.contact_id IN (".implode(',',$cList).")"];
        } elseif (sizeof($iList)) {
            $data['source']['filters']['addSrch'] = ['order'=>99,'hidden'=>true, 'sql'=>BIZUNO_DB_PREFIX."inventory_prices.inventory_id IN (".implode(',',$iList).")"];
        } elseif (!$search) { // not in contacts or inventory managers, must be prices manager by contact type and not searching (or method quantity goes away)
            unset($data['source']['tables']['inventory']);
            $data['source']['search'] = ['settings', 'method', 'sku', 'description_short', 'description_purchase', 'description_sales'];
            $data['columns']['inventory_id']['field'] = BIZUNO_DB_PREFIX.'inventory_prices.inventory_id';
            $data['columns']['inventory_id']['format']= 'dbVal;inventory;description_short;id';
        }
        if (in_array($GLOBALS['myDevice'], ['mobile','tablet'])) {
            $data['columns']['title']['attr']['hidden'] = true;
            $data['columns']['method']['attr']['hidden'] = true;
            $data['columns']['ref_id']['attr']['hidden'] = true;
            $data['columns']['last_update']['attr']['hidden'] = true;
        }
        return $data;
    }

    /**
     * Datagrid structure for quantity based pricing
     * @param string $name - DOM field name
     * @return array - datagrid structure
     */
    protected function dgQuantity($name) {
        return ['id'=>$name, 'type'=>'edatagrid',
            'attr'     => ['toolbar'=>"#{$name}Toolbar", 'rownumbers'=>true],
            'events'   => ['data'=> $name.'Data',
                'onLoadSuccess'=> "function(row) { var rows=jq('#$name').edatagrid('getData'); if (rows.total == 0) jq('#$name').edatagrid('addRow'); }",
                'onClickRow'   => "function(rowIndex) { jq('#$name').edatagrid('editRow', rowIndex); }"],
            'source'   => ['actions'=>['new'=>['order'=>10,'icon'=>'add','size'=>'large','events'=>['onClick'=>"jq('#$name').edatagrid('addRow');"]]]],
            'footnotes'=> ['currency'=>lang('msg_default_currency_assumed')],
            'columns'  => [
                'action'  => ['order'=> 1,'label'=>lang('action'), 'attr'=>['width'=>80],
                    'actions'=> ['trash'=>  ['icon'=>'trash','order'=>20,'events'=>['onClick'=>"jq('#$name').edatagrid('destroyRow');"]]],
                    'events' => ['formatter'=>"function(value,row,index){ return ".$name."Formatter(value,row,index); }"]],
                'qty'     => ['order'=>10,'label'=>lang('qty'), 'attr'=>['width'=>80, 'align'=>'right'],
                    'events'=>  ['editor'=>"{type:'numberbox',options:{formatter:function(value){return formatPrecise(value);}}}"]],
                'source'  => ['order'=>20,'label'=>lang('source'), 'attr'=>['width'=>150, 'sortable'=>true, 'resizable'=>true, 'align'=>'center'],
                    'events' => ['formatter'=>"function(value){ return getTextValue(qtySource, value); }",
                        'editor'=>"{type:'combobox',options:{valueField:'id',textField:'text',data:qtySource,value:'1'}}"]],
                'adjType' => ['order'=>30,'label'=>lang('adjustment'),'attr' =>['width'=>150],
                    'events' => ['formatter'=>"function(value){ return getTextValue(qtyAdj, value); }",
                        'editor'=>"{type:'combobox',options:{valueField:'id',textField:'text',data:qtyAdj}}"]],
                'adjValue'=> ['order'=>40,'label'=>$this->lang['adj_value'], 'attr'=>['width'=>100, 'align'=>'center', 'size'=>'10'],
                    'events' => ['editor'=>"{type:'numberbox'}",'formatter'=>"function(value,row){ return formatNumber(value); }"]],
                'rndType' => ['order'=>50,'label'=>lang('rounding'),'attr' =>['width'=>150],
                    'events' => ['formatter'=>"function(value){ return getTextValue(qtyRnd, value); }",'editor'=>"{type:'combobox',options:{valueField:'id',textField:'text',data:qtyRnd}}"]],
                'rndValue'=> ['order'=>60,'label'=>$this->lang['rnd_value'], 'attr'=>['width'=>100, 'align'=>'center', 'size'=>'10'],
                    'events' => ['editor'=>"{type:'numberbox'}",'formatter'=>"function(value,row){ return formatNumber(value); }"]],
                'price'   => ['order'=>70,'label'=>lang('price'), 'attr'=>['hidden'=>true, 'width'=>100, 'align'=>'right', 'size'=>'10'],
                    'events' => ['editor'=>"{type:'numberbox'}",'formatter'=>"function(value,row){ return formatNumber(value); }"]],
                'margin'  => ['order'=>80,'label'=>lang('margin'),'attr'=>['hidden'=>true, 'width'=>100, 'align'=>'right', 'size'=>'10']]]];
    }

    /**
     * Decodes the price sheet settings for quantity based pricing and returns array of values for datagrid display
     * @param string $prices - encoded price value
     * @return array - ready to display in datagrid
     */
    protected function getPrices($prices='')
    {
        msgDebug("\nWorking with price string: $prices");
        $price_levels = explode(';', $prices);
        $arrData = [];
        for ($i=0; $i<sizeof($price_levels); $i++) {
            $level_info = explode(':', $price_levels[$i]);
            $arrData[] = [
                'price'   => isset($level_info[0]) ? $level_info[0] : 0,
                'qty'     => isset($level_info[1]) ? $level_info[1] : ($i+1),
                'source'  => isset($level_info[2]) ? $level_info[2] : '1',
                'adjType' => isset($level_info[3]) ? $level_info[3] : '',
                'adjValue'=> isset($level_info[4]) ? $level_info[4] : 0,
                'rndType' => isset($level_info[5]) ? $level_info[5] : '',
                'rndValue'=> isset($level_info[6]) ? $level_info[6] : 0];
        }
        return $arrData;
    }
}
