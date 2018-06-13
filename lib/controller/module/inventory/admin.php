<?php
/*
 * Module inventory - Installation, Initialization and Settings
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
 * @version    2.x Last Update: 2018-02-09
 * @filesource /lib/controller/module/inventory/admin.php
 */

namespace bizuno;

class inventoryAdmin 
{
	public $moduleID = 'inventory';

	function __construct() 
    {
		$this->lang      = getLang($this->moduleID);
		$this->invMethods= ['byContact', 'bySKU', 'quantity']; // for install, pre-select some pricing methods to install
		$this->defaults  = [
            'sales'   => getModuleCache('phreebooks', 'chart', 'defaults', getUserCache('profile', 'currency', false, 'USD'))[30],
			'stock'   => getModuleCache('phreebooks', 'chart', 'defaults', getUserCache('profile', 'currency', false, 'USD'))[4],
			'nonstock'=> getModuleCache('phreebooks', 'chart', 'defaults', getUserCache('profile', 'currency', false, 'USD'))[34],
			'cogs'    => getModuleCache('phreebooks', 'chart', 'defaults', getUserCache('profile', 'currency', false, 'USD'))[32],
			'method'  => 'f',
            ];
		$this->settings = array_replace_recursive(getStructureValues($this->settingsStructure()), getModuleCache($this->moduleID, 'settings', false, false, []));
		$this->structure = [
            'url'          => BIZUNO_URL."controller/module/$this->moduleID/",
            'version'      => MODULE_BIZUNO_VERSION,
			'category'     => 'bizuno',
			'required'     => '1',
			'dirMethods'   => 'prices',
			'attachPath'   => 'data/inventory/uploads/',
			'menuBar' => ['child'=>[
                'inventory'=> ['order'=>30,   'label'=>lang('inventory'),'group'=>'inv','icon'=>'inventory','events'=>['onClick'=>"hrefClick('bizuno/main/bizunoHome&menuID=inventory');"],'child'=>[
                    'inv_mgr' => ['order'=>20,'label'=>lang('gl_acct_type_4_mgr'), 'icon'=>'inventory',  'events'=>['onClick'=>"hrefClick('inventory/main/manager');"]],
                    'rpt_inv' => ['order'=>99,'label'=>lang('reports'),            'icon'=>'mimeDoc',    'events'=>['onClick'=>"hrefClick('phreeform/main/manager&gID=inv');"]]]],
                'customers' => ['child'=>[
                    'prices_c'=> ['order'=>70,'label'=>lang('contacts_type_c_prc'),'icon'=>'price',     'events'=>['onClick'=>"hrefClick('inventory/prices/manager&type=c');"]]]],
                'vendors' => ['child'=>[
                    'prices_v'=> ['order'=>70,'label'=>lang('contacts_type_v_prc'),'icon'=>'price',     'events'=>['onClick'=>"hrefClick('inventory/prices/manager&type=v');"]]]]]],
			'hooks' => ['phreebooks'=>['tools'=>[
                'fyCloseHome'=>['page'=>'tools', 'class'=>'inventoryTools', 'order'=>50],
                'fyClose'    =>['page'=>'tools', 'class'=>'inventoryTools', 'order'=>50]]]],
			'api' => ['path'=>'inventory/api/inventoryAPI']];
		$this->phreeformProcessing = [
            'inv_sku'   => ['text'=>lang('sku'),  'group'=>$this->lang['title'],'module'=>'bizuno','function'=>'viewFormat'],
			'inv_image' => ['text'=>lang('image'),'group'=>$this->lang['title'],'module'=>'bizuno','function'=>'viewFormat'],
            'inv_mvmnt' => ['text'=>lang('annual_sales').' (sku)','group'=>$this->lang['title'],'module'=>'bizuno','function'=>'viewFormat']];
        $this->notes = [$this->lang['note_inventory_install_1']];
	}

	public function settingsStructure()
    {
		$weights  = [['id'=>'LB','text'=>lang('pounds')], ['id'=>'KG', 'text'=>lang('kilograms')]];
		$dims     = [
            ['id'=>'IN','text'=>lang('inches')],
			['id'=>'FT','text'=>lang('feet')],
			['id'=>'MM','text'=>lang('millimeters')],
			['id'=>'CM','text'=>lang('centimeters')],
			['id'=>'M', 'text'=>lang('meters')]];
		$taxes_c  = viewSalesTaxDropdown('c', 'contacts');
		$taxes_v  = viewSalesTaxDropdown('v', 'contacts');
		$noYes    = [['id'=>'0','text'=>lang('no')], ['id'=>'1', 'text'=>lang('yes')]];
		$autoCosts= [['id'=>'0','text'=>lang('none')],  ['id'=>'PO', 'text'=>lang('journal_main_journal_id_4')], ['id'=>'PR', 'text'=>lang('journal_main_journal_id_6')]];
		$invCosts = [['id'=>'f','text'=>lang('inventory_cost_method_f')], ['id'=>'l', 'text'=>lang('inventory_cost_method_l')], ['id'=>'a', 'text'=>lang('inventory_cost_method_a')]];
		$data = [
            'general'=> [
                'weight_uom'     => ['values'=>$weights,  'attr'=>  ['type'=>'select', 'value'=>'LB']],
				'dim_uom'        => ['values'=>$dims,     'attr'=>  ['type'=>'select', 'value'=>'IN']],
				'tax_rate_id_v'  => ['values'=>$taxes_v,  'attr'=>  ['type'=>'select', 'value'=>'0']],
				'tax_rate_id_c'  => ['values'=>$taxes_c,  'attr'=>  ['type'=>'select', 'value'=>'0']],
				'auto_add'       => ['values'=>$noYes,    'attr'=>  ['type'=>'select', 'value'=>'0']],
				'auto_cost'      => ['values'=>$autoCosts,'attr'=>  ['type'=>'select', 'value'=>'0']],
				'allow_neg_stock'=> ['values'=>$noYes,    'attr'=>  ['type'=>'select', 'value'=>'1']],
				'stock_usage'    => ['values'=>$noYes,    'attr'=>  ['type'=>'select', 'value'=>'1']],
				'barcode_length' => ['attr'=>  ['value'=>'12']]], // 0 to turn off
			'phreebooks'=> [
                'sales_si'  => ['label'=>$this->lang['set_inv_sales_'].lang('inventory_inventory_type_si'),'jsBody'=>htmlComboGL('phreebooks_sales_si'),'attr'=>  ['value'=>$this->defaults['sales']]],
				'inv_si'    => ['label'=>$this->lang['set_inv_inv_']  .lang('inventory_inventory_type_si'),'jsBody'=>htmlComboGL('phreebooks_inv_si'),  'attr'=>  ['value'=>$this->defaults['stock']]],
				'cogs_si'   => ['label'=>$this->lang['set_inv_cogs_'] .lang('inventory_inventory_type_si'),'jsBody'=>htmlComboGL('phreebooks_cogs_si'), 'attr'=>  ['value'=>$this->defaults['cogs']]],
				'method_si' => ['label'=>$this->lang['set_inv_meth_'] .lang('inventory_inventory_type_si'),'values'=>$invCosts, 'attr'=>  ['type'=>'select', 'value'=>$this->defaults['method']]],
				'sales_ms'  => ['label'=>$this->lang['set_inv_sales_'].lang('inventory_inventory_type_ms'),'jsBody'=>htmlComboGL('phreebooks_sales_ms'),'attr'=>  ['value'=>$this->defaults['sales']]],
				'inv_ms'    => ['label'=>$this->lang['set_inv_inv_']  .lang('inventory_inventory_type_ms'),'jsBody'=>htmlComboGL('phreebooks_inv_ms'),  'attr'=>  ['value'=>$this->defaults['stock']]],
				'cogs_ms'   => ['label'=>$this->lang['set_inv_cogs_'] .lang('inventory_inventory_type_ms'),'jsBody'=>htmlComboGL('phreebooks_cogs_ms'), 'attr'=>  ['value'=>$this->defaults['cogs']]],
				'method_ms' => ['label'=>$this->lang['set_inv_meth_'] .lang('inventory_inventory_type_ms'),'values'=>$invCosts, 'attr'=>  ['type'=>'select', 'value'=>$this->defaults['method']]],
				'sales_ma'  => ['label'=>$this->lang['set_inv_sales_'].lang('inventory_inventory_type_ma'),'jsBody'=>htmlComboGL('phreebooks_sales_ma'),'attr'=>  ['value'=>$this->defaults['sales']]],
				'inv_ma'    => ['label'=>$this->lang['set_inv_inv_']  .lang('inventory_inventory_type_ma'),'jsBody'=>htmlComboGL('phreebooks_inv_ma'),  'attr'=>  ['value'=>$this->defaults['stock']]],
				'cogs_ma'   => ['label'=>$this->lang['set_inv_cogs_'] .lang('inventory_inventory_type_ma'),'jsBody'=>htmlComboGL('phreebooks_cogs_ma'), 'attr'=>  ['value'=>$this->defaults['cogs']]],
				'method_ma' => ['label'=>$this->lang['set_inv_meth_'] .lang('inventory_inventory_type_ma'),'values'=>$invCosts, 'attr'=>  ['type'=>'select', 'value'=>$this->defaults['method']]],
				'sales_sr'  => ['label'=>$this->lang['set_inv_sales_'].lang('inventory_inventory_type_sr'),'jsBody'=>htmlComboGL('phreebooks_sales_sr'),'attr'=>  ['value'=>$this->defaults['sales']]],
				'inv_sr'    => ['label'=>$this->lang['set_inv_inv_']  .lang('inventory_inventory_type_sr'),'jsBody'=>htmlComboGL('phreebooks_inv_sr'),  'attr'=>  ['value'=>$this->defaults['stock']]],
				'cogs_sr'   => ['label'=>$this->lang['set_inv_cogs_'] .lang('inventory_inventory_type_sr'),'jsBody'=>htmlComboGL('phreebooks_cogs_sr'), 'attr'=>  ['value'=>$this->defaults['cogs']]],
				'method_sr' => ['label'=>$this->lang['set_inv_meth_'] .lang('inventory_inventory_type_sr'),'values'=>$invCosts, 'attr'=>  ['type'=>'select', 'value'=>$this->defaults['method']]],
				'sales_sa'  => ['label'=>$this->lang['set_inv_sales_'].lang('inventory_inventory_type_sa'),'jsBody'=>htmlComboGL('phreebooks_sales_sa'),'attr'=>  ['value'=>$this->defaults['sales']]],
				'inv_sa'    => ['label'=>$this->lang['set_inv_inv_']  .lang('inventory_inventory_type_sa'),'jsBody'=>htmlComboGL('phreebooks_inv_sa'),  'attr'=>  ['value'=>$this->defaults['stock']]],
				'cogs_sa'   => ['label'=>$this->lang['set_inv_cogs_'] .lang('inventory_inventory_type_sa'),'jsBody'=>htmlComboGL('phreebooks_cogs_sa'), 'attr'=>  ['value'=>$this->defaults['cogs']]],
				'method_sa' => ['label'=>$this->lang['set_inv_meth_'] .lang('inventory_inventory_type_sa'),'values'=>$invCosts, 'attr'=>  ['type'=>'select', 'value'=>$this->defaults['method']]],
				'sales_ns'  => ['label'=>$this->lang['set_inv_sales_'].lang('inventory_inventory_type_ns'),'jsBody'=>htmlComboGL('phreebooks_sales_ns'),'attr'=>  ['value'=>$this->defaults['sales']]],
				'inv_ns'    => ['label'=>$this->lang['set_inv_inv_']  .lang('inventory_inventory_type_ns'),'jsBody'=>htmlComboGL('phreebooks_inv_ns'),  'attr'=>  ['value'=>$this->defaults['nonstock']]],
				'cogs_ns'   => ['label'=>$this->lang['set_inv_cogs_'] .lang('inventory_inventory_type_ns'),'jsBody'=>htmlComboGL('phreebooks_cogs_ns'), 'attr'=>  ['value'=>$this->defaults['cogs']]],
				'sales_sv'  => ['label'=>$this->lang['set_inv_sales_'].lang('inventory_inventory_type_sv'),'jsBody'=>htmlComboGL('phreebooks_sales_sv'),'attr'=>  ['value'=>$this->defaults['sales']]],
				'inv_sv'    => ['label'=>$this->lang['set_inv_inv_']  .lang('inventory_inventory_type_sv'),'jsBody'=>htmlComboGL('phreebooks_inv_sv'),  'attr'=>  ['value'=>$this->defaults['nonstock']]],
				'cogs_sv'   => ['label'=>$this->lang['set_inv_cogs_'] .lang('inventory_inventory_type_sv'),'jsBody'=>htmlComboGL('phreebooks_cogs_sv'), 'attr'=>  ['value'=>$this->defaults['cogs']]],
				'sales_lb'  => ['label'=>$this->lang['set_inv_sales_'].lang('inventory_inventory_type_lb'),'jsBody'=>htmlComboGL('phreebooks_sales_lb'),'attr'=>  ['value'=>$this->defaults['sales']]],
				'inv_lb'    => ['label'=>$this->lang['set_inv_inv_']  .lang('inventory_inventory_type_lb'),'jsBody'=>htmlComboGL('phreebooks_inv_lb'),  'attr'=>  ['value'=>$this->defaults['nonstock']]],
				'cogs_lb'   => ['label'=>$this->lang['set_inv_cogs_'] .lang('inventory_inventory_type_lb'),'jsBody'=>htmlComboGL('phreebooks_cogs_lb'), 'attr'=>  ['value'=>$this->defaults['cogs']]],
				'sales_ai'  => ['label'=>$this->lang['set_inv_sales_'].lang('inventory_inventory_type_ai'),'jsBody'=>htmlComboGL('phreebooks_sales_ai'),'attr'=>  ['value'=>$this->defaults['sales']]],
				'sales_ci'  => ['label'=>$this->lang['set_inv_sales_'].lang('inventory_inventory_type_ci'),'jsBody'=>htmlComboGL('phreebooks_sales_ci'),'attr'=>  ['value'=>$this->defaults['sales']]]]];
		settingsFill($data, $this->moduleID);
		return $data;
	}

	public function adminHome(&$layout=[])
    {
        if (!$security = validateSecurity('bizuno', 'admin', 1)) { return; }
        $data = [
            'tabs' => ['tabAdmin'=> ['divs'=>  [
                'prices' => ['order'=>10,'label'=>lang('prices'), 'attr'=>['module'=>$this->moduleID,'path'=>$this->structure['dirMethods']],
                    'src'=>BIZUNO_LIB."view/module/bizuno/tabAdminMethods.php"],
                'settings'=> ['order'=>20,'label'=>lang('settings'),'src'=>BIZUNO_LIB."view/module/bizuno/tabAdminSettings.php"],
				'fields'  => ['order'=>60,'label'=>lang('extra_fields'),'type'=>'html', 'html'=>'',
                    'attr'=> ["data-options"=>"href:'".BIZUNO_AJAX."&p=bizuno/fields/manager&module=$this->moduleID&table=inventory'"]],
                'tabDBs'  => ['order'=>70, 'label' => lang('dashboards'), 'attr' => ['module' => $this->moduleID, 'type' => 'dashboards'], 'src' => BIZUNO_LIB . "view/module/bizuno/tabAdminMethods.php"],
				'tools'   => ['order'=>90,'label'=>lang('tools'),   'src'=>BIZUNO_LIB."view/module/$this->moduleID/tabAdmTools.php"]]]],
			'fields' => [
                'btnMethodAdd' => ['attr'=>  ['type'=>'button', 'value'=>lang('install')],'hidden'=>$security> 1?false:true],
                'btnMethodDel' => ['attr'=>  ['type'=>'button', 'value'=>lang('remove')], 'hidden'=>$security==4?false:true],
                'btnMethodProp'=> ['icon'=>'settings','size'=>'medium'],
                'settingSave'  => ['icon'=>'save',    'size'=>'large'],
                'btnHistTest'  => ['label'=>'', 'attr'=>  ['type'=>'button', 'value'=>$this->lang['inv_tools_btn_test']],
                    'events'=> ['onClick'=>"jsonAction('inventory/tools/historyTestRepair', 0, 'test');"]],
                'btnHistFix'   => ['label'=>'', 'attr'=>  ['type'=>'button', 'value'=>$this->lang['inv_tools_btn_repair']],
                    'events'=> ['onClick'=>"jsonAction('inventory/tools/historyTestRepair', 0, 'fix');"]],
                'btnAllocFix'  => ['label'=>'', 'attr'=>  ['type'=>'button', 'value'=>$this->lang['inv_tools_qty_alloc_label']],
                    'events'=> ['onClick'=>"jq('body').addClass('loading'); jsonAction('inventory/tools/qtyAllocRepair');"]],
                'btnJournalFix'=> ['label'=>'', 'attr'=>  ['type'=>'button', 'value'=>$this->lang['inv_tools_btn_so_po_fix']],
                    'events'=> ['onClick'=>"jq('body').addClass('loading'); jsonAction('inventory/tools/onOrderRepair');"]],
                'btnPriceAssy' => ['label'=>'', 'attr'=>  ['type'=>'button', 'value'=>lang('go')],
                    'events'=> ['onClick'=>"jq('body').addClass('loading'); jsonAction('inventory/tools/priceAssy');"]]],
            'lang' => $this->lang];
		$layout = array_replace_recursive($layout, adminStructure($this->moduleID, $this->settingsStructure(), $this->lang), $data);
	}

	public function adminSave()
    {
		readModuleSettings($this->moduleID, $this->settings);
	}

	public function install(&$layout=[])
    {
		$bAdmin = new bizunoSettings();
        foreach ($this->invMethods as $method) {
            $bAdmin->methodInstall($layout, ['module'=>'inventory', 'path'=>'prices', 'method'=>$method], false);
        }
	}

	/**
	 * This method adds standard definition physical fields to the inventory table
	 */
	public function installPhysicalFields()
    {
		$id = validateTab($module_id='inventory', 'inventory', lang('physical'), 80);
        if (!dbFieldExists(BIZUNO_DB_PREFIX."inventory", 'length')) { dbGetResult("ALTER TABLE ".BIZUNO_DB_PREFIX."inventory ADD length FLOAT NOT NULL DEFAULT '0' COMMENT 'tag:ProductLength;tab:$id;order:20'"); }
        if (!dbFieldExists(BIZUNO_DB_PREFIX."inventory", 'width'))  { dbGetResult("ALTER TABLE ".BIZUNO_DB_PREFIX."inventory ADD width  FLOAT NOT NULL DEFAULT '0' COMMENT 'tag:ProductWidth;tab:$id;order:30'"); }
        if (!dbFieldExists(BIZUNO_DB_PREFIX."inventory", 'height')) { dbGetResult("ALTER TABLE ".BIZUNO_DB_PREFIX."inventory ADD height FLOAT NOT NULL DEFAULT '0' COMMENT 'tag:ProductHeight;tab:$id;order:40'"); }
	}
}
