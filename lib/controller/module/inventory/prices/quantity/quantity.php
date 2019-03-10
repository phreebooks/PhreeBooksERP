<?php
/*
 * Inventory - Prices by quantity method
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
 * @filesource /lib/controller/module/inventory/prices/quantity.php
 */

namespace bizuno;

bizAutoLoad(BIZUNO_LIB."controller/module/inventory/prices.php", 'inventoryPrices');

class quantity extends inventoryPrices
{
    public $moduleID = 'inventory';
    public $methodDir= 'prices';
    public $code     = 'quantity';
    public $required = true;

    public function __construct()
    {
        parent::__construct();
        $this->lang    = array_merge($this->lang, getMethLang($this->moduleID, $this->methodDir, $this->code));
        $this->settings= ['order'=>10];
        $usrSettings   = getModuleCache($this->moduleID, $this->methodDir, $this->code, 'settings', []);
        settingsReplace($this->settings, $usrSettings, $this->settingsStructure());
    }

    public function settingsStructure()
    {
        return ['order'=>['label'=>lang('order'),'position'=>'after','attr'=>['type'=>'integer','size'=>'3','value'=>$this->settings['order']]]];
    }

    /**
     * This function renders the HTML form to build the pricing strategy
     * @param array $layout - source data to build the form
     * @param array $settings - attributes containing the prices and level information
     * @return modified $layout
     */
    public function priceRender(&$layout=[], $settings=[])
    {
        $prices = isset($settings['attr']) ? $settings['attr'] : '';
        $layout['values']['prices'] = $this->getPrices($prices);
        $jsHead = "
var dgPricesSetData = ".json_encode($layout['values']['prices']).";
var qtySource = "      .json_encode(viewKeyDropdown($layout['values']['qtySource'])).";
var qtyAdj    = "      .json_encode(viewKeyDropdown($layout['values']['qtyAdj'])).";
var qtyRnd    = "      .json_encode(viewKeyDropdown($layout['values']['qtyRnd'])).";
function preSubmitPrices() {
    jq('#dgPricesSet').edatagrid('saveRow');
    var items = jq('#dgPricesSet').datagrid('getData');
    var serializedItems = JSON.stringify(items);
    jq('#item$this->code').val(serializedItems);
    return true;
}";
        $layout['divs']['divPrices'] = ['order'=>10,'label'=>lang('general'),'type'=>'divs','divs'=>[
            'byCBody' => ['order'=>20,'type'=>'fields','label'=>$this->lang['title'],'fields'=>$this->getView($layout['fields'], $settings)],
            'byCdg'   => ['order'=>50,'type'=>'datagrid','key'=>'dgPricesSet']]];
        $layout['jsHead'][$this->code] = $jsHead;
        $layout['datagrid']['dgPricesSet'] = $this->dgQuantity('dgPricesSet');
        $layout['datagrid']['dgPricesSet']['columns']['price']['attr']['hidden']  = false;
        $layout['datagrid']['dgPricesSet']['columns']['margin']['attr']['hidden'] = false;
    }

    private function getView($structure, $settings)
    {
        $defAttr= ['break'=>true,'label'=>lang('default'),'attr'=>['type'=>'selNoYes']];
        if (!empty($settings['default'])) { $defAttr['attr']['checked'] = true; }
        return [
            'id'      .$this->code => $structure['id'], // hidden
            'item'    .$this->code => ['attr'=>['type'=>'hidden']],
            'title'   .$this->code => ['order'=>10,'label'=>lang('title'),'break'=>true,'attr'=>['value'=>$settings['title']]],
            'default' .$this->code => $defAttr,
            'currency'.$this->code => array_merge($structure['currency'],['order'=>70,'break'=>true])];
    }

    /**
     * This method saves the form contents for quantity pricing into the database, it is called from method: inventoryPrices:save
     * @param string $layout
     * @return true if successful, NULL and messageStack with error message if failed
     */
    public function priceSave(&$layout=[])
    {
        $rID    = clean('id'.$this->code, 'integer', 'post');
        $data   = clean('item'.$this->code,  'json', 'post');
        $title  = clean('title'.$this->code, 'text', 'post');
        $default=clean('default'.$this->code,'char', 'post');
        if (!$security = validateSecurity('inventory', 'prices_'.$this->type, $rID?3:2)) { return; }
        $values = requestData(dbLoadStructure(BIZUNO_DB_PREFIX."inventory_prices"), $this->code);
        $values['method'] = $this->code;
        // check for duplicate title's
        if ($title) {
            $dup = dbGetMulti(BIZUNO_DB_PREFIX."inventory_prices", "id<>$rID AND method='$this->code' AND contact_type='$this->type'");
            foreach ($dup as $row) {
                $props = json_decode($row['settings'], true);
                if (isset($props['title']) && $props['title'] == $title) { return msgAdd(lang('duplicate_title')); }
            }
        }
        msgDebug("\ndecoded data = ".print_r($data, true));
        if (!isset($data['total']) || !isset($data['rows'])) { return; }
        $levels = $data['rows'];
        if ($data['total'] == 0) { return; }
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
        $settings = [
            'title'      => $title,
            'last_update'=> date('Y-m-d'),
            'attr'       => implode(';', $prices),
            'default'    => $default];
        $values['settings'] = json_encode($settings);
        $result = dbWrite(BIZUNO_DB_PREFIX."inventory_prices", $values, $rID?'update':'insert', "id=$rID");
        if (!$rID) { $rID = $_POST['id'] = $result; } // for customization
        msgLog(lang('prices').'-'.lang('save')." - $this->code; $title (rID=$rID)");
        return true;
    }

    /**
     * This function determines the price for a given sku and returns the entries for prices drop down
     * @param array &$prices - current pricing array to be added to
     * @param array $values - details needed to calculate proper price
     * @return array $prices by reference
     */
    public function priceQuote(&$prices, $values)
    {
        $sheets = dbGetMulti(BIZUNO_DB_PREFIX."inventory_prices", "method='$this->code' AND contact_type='{$values['cType']}'");
        if (!$sheets) { return; }
        $default_price = 0;
        foreach ($sheets as $row) {
            $settings = json_decode($row['settings'], true);
            $levels = $this->decodeQuantity($values['iCost'], $values['iList'], $values['qty'], $settings['attr']);
//          msgDebug("\nMethod = $this->code with attr = {$settings['attr']} returned levels: ".print_r($levels, true));
            if ($values['cSheet']==$row['id']) {
                if (!isset($prices['price'])) { $prices['price'] = $levels['price']; }
            } elseif (!empty($settings['default'])) {
                $default_price = $levels['price'];
            }
            if (!empty($settings['default']) && empty($prices['regular_price'])) {
                $prices['regular_price'] = $levels['price'];
            }
            if (!isset($prices['sheets'][$row['id']]) && $levels['price']) { // only add price sheet if a price was returned
                $prices['sheets'][$row['id']] = [
                    'title'  => $settings['title'],
                    'default'=> $values['cSheet']==$row['id'] || (isset($settings['default']) && $settings['default']) ? 1 : 0,
                    'levels' => $levels['levels']];
            }
        }
        if (!isset($prices['price']) && $default_price) { $prices['price'] = $default_price; }
//      msgDebug("\nLeaving $this->code with price = ".(isset($prices['price']) ? $prices['price'] : 'undefined'));
    }
}
