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
 * @copyright  2008-2018, PhreeSoft
 * @license    http://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 * @version    2.x Last Update: 2018-04-17
 * @filesource /lib/controller/module/inventory/prices/quantity.php
 */

namespace bizuno;

require_once(BIZUNO_LIB."controller/module/inventory/prices.php");

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
        $layout['divs']['divPrices']= ['order'=>50,'src'=>BIZUNO_LIB."view/module/inventory/divQuantity.php"];
        $layout['fields']['title']  = ['label'=>lang('title'),'attr'=>['value'=>$settings['title']]];
        $prices = isset($settings['attr']) ? $settings['attr'] : '';
        $layout['values']['prices'] = $this->getPrices($prices);
        $layout['values']['pricesCode'] = $this->code;
        $layout['lang']['title'] = $this->lang['title'];
        $layout['datagrid']['dgPricesSet'] = $this->datagridQuantity('dgPricesSet');
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
        if (!$sheets) { return;  }
		$default_price = 0;
		foreach ($sheets as $row) {
			$settings = json_decode($row['settings'], true);
			$levels = $this->decodeQuantity($values['iCost'], $values['iList'], $values['qty'], $settings['attr']);
//			msgDebug("\nMethod = $this->code with attr = {$settings['attr']} returned levels: ".print_r($levels, true));
			if ($values['cSheet']==$row['id']) {
                if (!isset($prices['price'])) { $prices['price'] = $levels['price']; }
			} elseif (isset($settings['default']) && $settings['default']) {
				$default_price = $levels['price'];
			}
			if (!isset($prices['sheets'][$row['id']]) && $levels['price']) { // only add price sheet if a price was returned
                $prices['sheets'][$row['id']] = [
                    'title'  => $settings['title'],
                    'default'=> $values['cSheet']==$row['id'] || (isset($settings['default']) && $settings['default']) ? 1 : 0,
                    'levels' => $levels['levels']];
            }
		}
        if (!isset($prices['price']) && $default_price) { $prices['price'] = $default_price; }
//		msgDebug("\nLeaving $this->code with price = ".(isset($prices['price']) ? $prices['price'] : 'undefined'));
	}
}
