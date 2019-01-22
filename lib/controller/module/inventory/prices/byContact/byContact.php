<?php
/*
 * Inventory - Prices by contact method
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
 * @version    3.x Last Update: 2018-09-11
 * @filesource /lib/controller/module/inventory/prices/byContact.php
 */

namespace bizuno;

bizAutoLoad(BIZUNO_LIB."controller/module/inventory/prices.php", 'inventoryPrices');

class byContact extends inventoryPrices 
{
    public $moduleID = 'inventory';
    public $methodDir= 'prices';
    public $code     = 'byContact';

    public function __construct()
    {
        parent::__construct();
        $this->lang    = array_merge($this->lang, getMethLang($this->moduleID, $this->methodDir, $this->code));
        $this->settings= ['order'=>20];
        $usrSettings   = getModuleCache($this->moduleID, $this->methodDir, $this->code, 'settings', []);
        settingsReplace($this->settings, $usrSettings, $this->settingsStructure());
        $this->structure= ['hooks'=>['contacts'=>['main'=>[
            'edit'  => ['order'=>51,'page'=>$this->code,'class'=>$this->code],
            'delete'=> ['order'=>71,'page'=>$this->code,'class'=>$this->code]]]]];
    }

    public function settingsStructure()
    {
        return ['order'=>['label'=>lang('order'),'position'=>'after','attr'=>['type'=>'integer','size'=>'3','value'=>$this->settings['order']]]];
    }

    /**
     * Extends /contacts/main/edit
     * @param array $layout - Structure coming in
     * @return modified $layout
     */
    public function edit(&$layout=[]) 
    {
        $type = clean('type',['format'=>'char','default'=>'c'], 'get');
        $cID  = clean('rID', 'integer', 'get');
        if (!$security = validateSecurity('inventory', 'prices_'.$type, 3, false)) { return; }
        if (!$cID) { return; }// cannot add prices until the contact has been saved and exists as prices are added asyncronously
        $layout['tabs']['tabContacts']['divs'][$this->code] = ['order'=>35, 'label'=>$this->lang['tab_label'], 'type'=>'html', 'html'=>'',
            'options'=>['href'=>"'".BIZUNO_AJAX."&p=inventory/prices/manager&type=$type&security=$security&mID=$this->code&cID=$cID&mod={$GLOBALS['bizunoModule']}'"]];
    }
    
    /**
     * Extends /contacts/main/delete
     * @param array $layout - Structure coming in
     * @return modified $layout
     */
    public function delete(&$layout=[]) 
    {
        $rID  = clean('rID', 'integer', 'get');
        $type = dbGetValue(BIZUNO_DB_PREFIX.'contacts', 'type', "id=$rID");
        if (!$security = validateSecurity('inventory', 'prices_'.$type, 4, false)) { return; }
        if ($rID && !empty($layout['dbAction'])) { 
            $layout['dbAction']['price_byContact'] = "DELETE FROM ".BIZUNO_DB_PREFIX."inventory_prices WHERE contact_id=$rID";
        }
    }

    /**
     * This function renders the HTML form to build the pricing strategy
     * @param array $layout - source data to build the form
     * @param array $settings - attributes containing the prices and level information
     * @return modified $layout
     */
    public function priceRender(&$layout=[], $settings=[])
    {
        msgDebug("\nentering byContact with settings= ".print_r($settings, true));
        $mod    = clean('mod', 'text', 'request'); // in specific module, can be either post or get
        $inContacts = $mod=='contacts' ? true : false;
        $type   = $layout['fields']['contact_type']['attr']['value'];
        $prices = isset($settings['attr']) ? $settings['attr'] : '';
        $layout['values']['prices']  = $this->getPrices($prices);
        $jsHead = "
var dgPricesSetData = ".json_encode($layout['values']['prices']).";
var qtySource = "      .json_encode(viewKeyDropdown($layout['values']['qtySource'])).";
var qtyAdj    = "      .json_encode(viewKeyDropdown($layout['values']['qtyAdj'])).";
var qtyRnd    = "      .json_encode(viewKeyDropdown($layout['values']['qtyRnd'])).";
var rID = jq('#inventory_id$this->code').val();
function preSubmitPrices() {
    jq('#dgPricesSet').edatagrid('saveRow');
    var items = jq('#dgPricesSet').datagrid('getData');
    var serializedItems = JSON.stringify(items);
    jq('#item$this->code').val(serializedItems);
    return true;
}";
            $iID = $layout['fields']['inventory_id']['attr']['value'];
            if ($iID) {
                $name = dbGetValue(BIZUNO_DB_PREFIX.'inventory', 'description_short', "id=$iID");
                $layout['fields']['inventory_id']['defaults']['data'] = "iData$this->code";
                $jsHead .= "\nvar iData$this->code = ".json_encode([['id'=>$iID,'description_short'=>$name]]).";";
            }
            $layout['fields']['inventory_id']['attr']['id']  = "inventory_id$this->code";
            $layout['fields']['inventory_id']['attr']['type']= 'inventory';
        if ($inContacts) { // we're in the contact form, hide contact_id field and set to current form value
            $layout['jsReady'][$this->code] = "jq('#contact_id$this->code').val(jq('#id').val());";
        } else {
            $cID = $layout['fields']['contact_id']['attr']['value'];
            if ($cID) {
                $name = dbGetValue(BIZUNO_DB_PREFIX.'address_book', 'primary_name', "ref_id=$cID AND type='m'");
                $layout['fields']['contact_id']['defaults']['data'] = "cData$this->code";
                $jsHead .= "\nvar cData$this->code = ".json_encode([['id'=>$cID,'primary_name'=>$name]]).";";
            }
            $layout['fields']['contact_id']['defaults']['suffix']= $this->code;
            $layout['fields']['contact_id']['defaults']['type']  = $type;
            $layout['fields']['contact_id']['attr']['id']        = "contact_id$this->code";
            $layout['fields']['contact_id']['attr']['type']      = 'contact';
        }
        $layout['divs']['divPrices'] = ['order'=>10,'type'=>'divs','divs'=>[
            'byCBody' => ['order'=>20,'type'=>'fields','label'=>$this->lang['title'],'fields'=>$this->getView($layout['fields'], $inContacts)],
            'byCdg'   => ['order'=>50,'type'=>'datagrid','key'=>'dgPricesSet']]];
        $layout['jsHead'][$this->code] = $jsHead;
        $layout['datagrid']['dgPricesSet'] = $this->dgQuantity('dgPricesSet');
        $layout['datagrid']['dgPricesSet']['columns']['price']['attr']['hidden']  = false;
        $layout['datagrid']['dgPricesSet']['columns']['margin']['attr']['hidden'] = false;
    }

    private function getView($structure, $inContacts)
    {
        $output = [
            'id'          .$this->code => $structure['id'], // hidden
            'item'        .$this->code => ['attr'=>['type'=>'hidden']],
            'contact_id'  .$this->code => $inContacts ? ['attr'=>['type'=>'hidden']] : array_merge($structure['contact_id'],['break'=>true]),
            'inventory_id'.$this->code => array_merge($structure['inventory_id'],['break'=>true]),
            'ref_id'      .$this->code => array_merge($structure['ref_id'],      ['break'=>true]),
            'currency'    .$this->code => array_merge($structure['currency'],    ['break'=>true])];
        return $output;
    }
    
    /**
     * This method saves the form contents for quantity pricing into the database, it is called from method: inventoryPrices:save 
     * @param string $request
     * @return true if successful, NULL and messageStack with error message if failed
     */
    public function priceSave()
    {
        $rID  = clean('id'.$this->code, 'integer', 'post');
        $data = clean('item'.$this->code, 'json', 'post');
        if (!$security = validateSecurity('inventory', 'prices_'.$this->type, $rID?3:2)) { return; }
        $values = requestData(dbLoadStructure(BIZUNO_DB_PREFIX."inventory_prices"), $this->code);
        $values['method'] = $this->code;
        msgDebug("decoded data = ".print_r($data, true));
        $levels = $data['rows'];
        $prices = [];
        foreach ($levels as $level) {
            if ($level['source'] && $level['qty']) {
                $temp = [];
                $temp[] = $level['price'];
                $temp[] = $level['qty'];
                $temp[] = $level['source'];
                $temp[] = $level['adjType'];
                $temp[] = $level['adjValue'];
                $temp[] = $level['rndType'];
                $temp[] = $level['rndValue'];
                $prices[] = implode(':', $temp);
            }
        }
        $settings = ['last_update'=> date('Y-m-d'), 'attr'=>implode(';', $prices)];
        $values['settings'] = json_encode($settings);
        $result = dbWrite(BIZUNO_DB_PREFIX."inventory_prices", $values, $rID?'update':'insert', "id=$rID");
        if (!$rID) { $rID = $_POST['id'] = $result; } // for customization
        msgLog(lang('prices').'-'.lang('save')." - $this->code; contact: ".$values['contact_id']."; SKU: ".$values['inventory_id']." (rID=$rID)");
        return true;
    }

    /**
     * This function determines the price for a given sku and returns the entries for prices dropdown
     * @param array $prices - current pricing array to be added to
     * @param array $values - details needed to calculate proper price
     * @return array $prices by reference
     */
    public function priceQuote(&$prices, $values)
    {
        if (!isset($values['cID']) || !$values['cID']) { return; }
        $sheets = dbGetMulti(BIZUNO_DB_PREFIX."inventory_prices", "method='$this->code' AND inventory_id='{$values['iID']}' AND contact_id='{$values['cID']}'");
        if (!$sheets) { return; }
        foreach ($sheets as $row) {
            $settings = json_decode($row['settings'], true);
            $levels = $this->decodeQuantity($values['iCost'], $values['iList'], $values['qty'], $settings['attr']);
            msgDebug("\nMethod = $this->code with attr = ".$settings['attr']." returned levels: ".print_r($levels, true));
            $prices['price'] = isset($prices['price']) ? min($prices['price'], $levels['price']) : $levels['price'];
            if (!isset($prices['sheets'][$row['ref_id']]) && $levels['price']) { // only add price sheet if a price was returned
                $prices['sheets'][$row['ref_id']] = ['title'=>$this->lang['title'], 'default'=>1, 'levels'=>$levels['levels']];
            }
        }
        msgDebug("\nLeaving $this->code with price = {$prices['price']}"); 
    }
}
