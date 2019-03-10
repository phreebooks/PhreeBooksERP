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
 * @copyright  2008-2019, PhreeSoft, Inc.
 * @license    http://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 * @version    3.x Last Update: 2019-03-05
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
            'method'  => 'f'];
        $this->settings = array_replace_recursive(getStructureValues($this->settingsStructure()), getModuleCache($this->moduleID, 'settings', false, false, []));
        $this->structure = [
            'url'       => BIZUNO_URL."controller/module/$this->moduleID/",
            'version'   => MODULE_BIZUNO_VERSION,
            'category'  => 'bizuno',
            'required'  => '1',
            'dirMethods'=> 'prices',
            'attachPath'=> 'data/inventory/uploads/',
            'api'       => ['path'=>'inventory/api/inventoryAPI'],
            'menuBar'   => ['child'=>[
                'inventory'=> ['order'=>30,   'label'=>lang('inventory'),'group'=>'inv','icon'=>'inventory','events'=>['onClick'=>"hrefClick('bizuno/main/bizunoHome&menuID=inventory');"],'child'=>[
                    'inv_mgr' => ['order'=>20,'label'=>lang('gl_acct_type_4_mgr'), 'icon'=>'inventory','events'=>['onClick'=>"hrefClick('inventory/main/manager');"]],
                    'rpt_inv' => ['order'=>99,'label'=>lang('reports'),            'icon'=>'mimeDoc',  'events'=>['onClick'=>"hrefClick('phreeform/main/manager&gID=inv');"]]]],
                'customers' => ['child'=>[
                    'prices_c'=> ['order'=>70,'label'=>lang('contacts_type_c_prc'),'icon'=>'price',    'events'=>['onClick'=>"hrefClick('inventory/prices/manager&type=c');"]]]],
                'vendors' => ['child'=>[
                    'prices_v'=> ['order'=>70,'label'=>lang('contacts_type_v_prc'),'icon'=>'price',    'events'=>['onClick'=>"hrefClick('inventory/prices/manager&type=v');"]]]]]],
            'hooks' => ['phreebooks'=>['tools'=>[
                'fyCloseHome'=>['page'=>'tools', 'class'=>'inventoryTools', 'order'=>50],
                'fyClose'    =>['page'=>'tools', 'class'=>'inventoryTools', 'order'=>50]]]]];
        $this->phreeformProcessing = [
            'inv_sku'  => ['text'=>lang('sku'),  'group'=>$this->lang['title'],'module'=>'bizuno','function'=>'viewFormat'],
            'inv_image'=> ['text'=>lang('image'),'group'=>$this->lang['title'],'module'=>'bizuno','function'=>'viewFormat'],
            'inv_mv0'  => ['text'=>lang('current_sales')    .' (sku)','group'=>$this->lang['title'],'module'=>'bizuno','function'=>'viewFormat'],
            'inv_mv1'  => ['text'=>lang('last_1month_sales').' (sku)','group'=>$this->lang['title'],'module'=>'bizuno','function'=>'viewFormat'],
            'inv_mv3'  => ['text'=>lang('last_3month_sales').' (sku)','group'=>$this->lang['title'],'module'=>'bizuno','function'=>'viewFormat'],
            'inv_mv6'  => ['text'=>lang('last_6month_sales').' (sku)','group'=>$this->lang['title'],'module'=>'bizuno','function'=>'viewFormat'],
            'inv_mv12' => ['text'=>lang('annual_sales')     .' (sku)','group'=>$this->lang['title'],'module'=>'bizuno','function'=>'viewFormat'],
            'inv_stk'  => ['text'=>lang('inventory_qty_min').' (sku)','group'=>$this->lang['title'],'module'=>'bizuno','function'=>'viewFormat']];
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
        $autoCosts= [['id'=>'0','text'=>lang('none')],  ['id'=>'PO', 'text'=>lang('journal_main_journal_id_4')], ['id'=>'PR', 'text'=>lang('journal_main_journal_id_6')]];
        $invCosts = [['id'=>'f','text'=>lang('inventory_cost_method_f')], ['id'=>'l', 'text'=>lang('inventory_cost_method_l')], ['id'=>'a', 'text'=>lang('inventory_cost_method_a')]];
        $si = lang('inventory_inventory_type_si');
        $ms = lang('inventory_inventory_type_ms');
        $ma = lang('inventory_inventory_type_ma');
        $sr = lang('inventory_inventory_type_sr');
        $sa = lang('inventory_inventory_type_sa');
        $ns = lang('inventory_inventory_type_ns');
        $sv = lang('inventory_inventory_type_sv');
        $lb = lang('inventory_inventory_type_lb');
        $ai = lang('inventory_inventory_type_ai');
        $ci = lang('inventory_inventory_type_ci');
        $data = [
            'general'=> ['order'=>10,'label'=>lang('general'),'fields'=>[
                'weight_uom'     => ['values'=>$weights,  'attr'=>  ['type'=>'select', 'value'=>'LB']],
                'dim_uom'        => ['values'=>$dims,     'attr'=>  ['type'=>'select', 'value'=>'IN']],
                'tax_rate_id_v'  => ['values'=>$taxes_v,  'attr'=>  ['type'=>'select', 'value'=>0]],
                'tax_rate_id_c'  => ['values'=>$taxes_c,  'attr'=>  ['type'=>'select', 'value'=>0]],
                'auto_add'       => ['attr'=>  ['type'=>'selNoYes', 'value'=>0]],
                'auto_cost'      => ['values'=>$autoCosts,'attr'=>  ['type'=>'select', 'value'=>0]],
                'allow_neg_stock'=> ['attr'=>  ['type'=>'selNoYes', 'value'=>1]],
                'stock_usage'    => ['attr'=>  ['type'=>'selNoYes', 'value'=>1]]]],
            'phreebooks'=> ['order'=>20,'label'=>getModuleCache('phreebooks', 'properties', 'title'),'fields'=>[
                'sales_si'  => ['label'=>$this->lang['inv_sales_lbl'].$si,'tip'=>$this->lang['inv_sales_'].lang('inventory_inventory_type_si'),'attr'=>['type'=>'ledger','id'=>'phreebooks_sales_si','value'=>$this->defaults['sales']]],
                'inv_si'    => ['label'=>$this->lang['inv_inv_lbl'].$si,  'tip'=>$this->lang['inv_inv_']  .$si,'attr'=>['type'=>'ledger','id'=>'phreebooks_inv_si',  'value'=>$this->defaults['stock']]],
                'cogs_si'   => ['label'=>$this->lang['inv_cogs_lbl'].$si, 'tip'=>$this->lang['inv_cogs_'] .$si,'attr'=>['type'=>'ledger','id'=>'phreebooks_cogs_si', 'value'=>$this->defaults['cogs']]],
                'method_si' => ['label'=>$this->lang['inv_meth_lbl'].$si, 'tip'=>$this->lang['inv_meth_'] .$si,'values'=>$invCosts,'attr'=>['type'=>'select',        'value'=>$this->defaults['method']]],
                'sales_ms'  => ['label'=>$this->lang['inv_sales_lbl'].$ms,'tip'=>$this->lang['inv_sales_'].$ms,'attr'=>['type'=>'ledger','id'=>'phreebooks_sales_ms','value'=>$this->defaults['sales']]],
                'inv_ms'    => ['label'=>$this->lang['inv_inv_lbl'].$ms,  'tip'=>$this->lang['inv_inv_']  .$ms,'attr'=>['type'=>'ledger','id'=>'phreebooks_inv_ms',  'value'=>$this->defaults['stock']]],
                'cogs_ms'   => ['label'=>$this->lang['inv_cogs_lbl'].$ms, 'tip'=>$this->lang['inv_cogs_'] .$ms,'attr'=>['type'=>'ledger','id'=>'phreebooks_cogs_ms', 'value'=>$this->defaults['cogs']]],
                'method_ms' => ['label'=>$this->lang['inv_meth_lbl'].$ms, 'tip'=>$this->lang['inv_meth_'] .$ms,'values'=>$invCosts,'attr'=>['type'=>'select',        'value'=>$this->defaults['method']]],
                'sales_ma'  => ['label'=>$this->lang['inv_sales_lbl'].$ma,'tip'=>$this->lang['inv_sales_'].$ma,'attr'=>['type'=>'ledger','id'=>'phreebooks_sales_ma','value'=>$this->defaults['sales']]],
                'inv_ma'    => ['label'=>$this->lang['inv_inv_lbl'].$ma,  'tip'=>$this->lang['inv_inv_']  .$ma,'attr'=>['type'=>'ledger','id'=>'phreebooks_inv_ma',  'value'=>$this->defaults['stock']]],
                'cogs_ma'   => ['label'=>$this->lang['inv_cogs_lbl'].$ma, 'tip'=>$this->lang['inv_cogs_'] .$ma,'attr'=>['type'=>'ledger','id'=>'phreebooks_cogs_ma', 'value'=>$this->defaults['cogs']]],
                'method_ma' => ['label'=>$this->lang['inv_meth_lbl'].$ma, 'tip'=>$this->lang['inv_meth_'] .$ma,'values'=>$invCosts,'attr'=>['type'=>'select',        'value'=>$this->defaults['method']]],
                'sales_sr'  => ['label'=>$this->lang['inv_sales_lbl'].$sr,'tip'=>$this->lang['inv_sales_'].$sr,'attr'=>['type'=>'ledger','id'=>'phreebooks_sales_sr','value'=>$this->defaults['sales']]],
                'inv_sr'    => ['label'=>$this->lang['inv_inv_lbl'].$sr,  'tip'=>$this->lang['inv_inv_']  .$sr,'attr'=>['type'=>'ledger','id'=>'phreebooks_inv_sr',  'value'=>$this->defaults['stock']]],
                'cogs_sr'   => ['label'=>$this->lang['inv_cogs_lbl'].$sr, 'tip'=>$this->lang['inv_cogs_'] .$sr,'attr'=>['type'=>'ledger','id'=>'phreebooks_cogs_sr', 'value'=>$this->defaults['cogs']]],
                'method_sr' => ['label'=>$this->lang['inv_meth_lbl'].$sr, 'tip'=>$this->lang['inv_meth_'] .$sr,'values'=>$invCosts,'attr'=>['type'=>'select',        'value'=>$this->defaults['method']]],
                'sales_sa'  => ['label'=>$this->lang['inv_sales_lbl'].$sa,'tip'=>$this->lang['inv_sales_'].$sa,'attr'=>['type'=>'ledger','id'=>'phreebooks_sales_sa','value'=>$this->defaults['sales']]],
                'inv_sa'    => ['label'=>$this->lang['inv_inv_lbl'].$sa,  'tip'=>$this->lang['inv_inv_']  .$sa,'attr'=>['type'=>'ledger','id'=>'phreebooks_inv_sa',  'value'=>$this->defaults['stock']]],
                'cogs_sa'   => ['label'=>$this->lang['inv_cogs_lbl'].$sa, 'tip'=>$this->lang['inv_cogs_'] .$sa,'attr'=>['type'=>'ledger','id'=>'phreebooks_cogs_sa', 'value'=>$this->defaults['cogs']]],
                'method_sa' => ['label'=>$this->lang['inv_meth_lbl'].$sa, 'tip'=>$this->lang['inv_meth_'] .$sa,'values'=>$invCosts,'attr'=>['type'=>'select',        'value'=>$this->defaults['method']]],
                'sales_ns'  => ['label'=>$this->lang['inv_sales_lbl'].$ns,'tip'=>$this->lang['inv_sales_'].$ns,'attr'=>['type'=>'ledger','id'=>'phreebooks_sales_ns','value'=>$this->defaults['sales']]],
                'inv_ns'    => ['label'=>$this->lang['inv_inv_lbl'].$ns,  'tip'=>$this->lang['inv_inv_']  .$ns,'attr'=>['type'=>'ledger','id'=>'phreebooks_inv_ns',  'value'=>$this->defaults['nonstock']]],
                'cogs_ns'   => ['label'=>$this->lang['inv_cogs_lbl'].$ns, 'tip'=>$this->lang['inv_cogs_'] .$ns,'attr'=>['type'=>'ledger','id'=>'phreebooks_cogs_ns', 'value'=>$this->defaults['cogs']]],
                'sales_sv'  => ['label'=>$this->lang['inv_sales_lbl'].$sv,'tip'=>$this->lang['inv_sales_'].$sv,'attr'=>['type'=>'ledger','id'=>'phreebooks_sales_sv','value'=>$this->defaults['sales']]],
                'inv_sv'    => ['label'=>$this->lang['inv_inv_lbl'].$sv,  'tip'=>$this->lang['inv_inv_']  .$sv,'attr'=>['type'=>'ledger','id'=>'phreebooks_inv_sv',  'value'=>$this->defaults['nonstock']]],
                'cogs_sv'   => ['label'=>$this->lang['inv_cogs_lbl'].$sv, 'tip'=>$this->lang['inv_cogs_'] .$sv,'attr'=>['type'=>'ledger','id'=>'phreebooks_cogs_sv', 'value'=>$this->defaults['cogs']]],
                'sales_lb'  => ['label'=>$this->lang['inv_sales_lbl'].$lb,'tip'=>$this->lang['inv_sales_'].$lb,'attr'=>['type'=>'ledger','id'=>'phreebooks_sales_lb','value'=>$this->defaults['sales']]],
                'inv_lb'    => ['label'=>$this->lang['inv_inv_lbl'].$lb,  'tip'=>$this->lang['inv_inv_']  .$lb,'attr'=>['type'=>'ledger','id'=>'phreebooks_inv_lb',  'value'=>$this->defaults['nonstock']]],
                'cogs_lb'   => ['label'=>$this->lang['inv_cogs_lbl'].$lb, 'tip'=>$this->lang['inv_cogs_'] .$lb,'attr'=>['type'=>'ledger','id'=>'phreebooks_cogs_lb', 'value'=>$this->defaults['cogs']]],
                'sales_ai'  => ['label'=>$this->lang['inv_sales_lbl'].$ai,'tip'=>$this->lang['inv_sales_'].$ai,'attr'=>['type'=>'ledger','id'=>'phreebooks_sales_ai','value'=>$this->defaults['sales']]],
                'sales_ci'  => ['label'=>$this->lang['inv_sales_lbl'].$ci,'tip'=>$this->lang['inv_sales_'].$ci,'attr'=>['type'=>'ledger','id'=>'phreebooks_sales_ci','value'=>$this->defaults['sales']]]]]];
        settingsFill($data, $this->moduleID);
        return $data;
    }

    public function adminHome(&$layout=[])
    {
        if (!$security = validateSecurity('bizuno', 'admin', 1)) { return; }
        $data = [
            'tabs' => ['tabAdmin'=> ['divs'=>[
                'prices' => ['order'=>10,'label'=>lang('prices'), 'attr'=>['module'=>$this->moduleID,'path'=>$this->structure['dirMethods']],
                    'src'=>BIZUNO_LIB."view/tabAdminMethods.php"],
                'fields' => ['order'=>60,'label'=>lang('extra_fields'),'type'=>'html', 'html'=>'',
                    'options'=> ['href'=>"'".BIZUNO_AJAX."&p=bizuno/fields/manager&module=$this->moduleID&table=inventory'"]],
                'tabDBs' => ['order'=>70,'label'=>lang('dashboards'), 'attr' => ['module' => $this->moduleID, 'type' => 'dashboards'], 'src' => BIZUNO_LIB . "view/tabAdminMethods.php"],
                'tools'  => ['order'=>90,'label'=>lang('tools'),'type'=>'divs','divs'=>$this->getViewTools()]]]],
            'fields' => [
                'btnMethodAdd' => ['attr'=>  ['type'=>'button', 'value'=>lang('install')],'hidden'=>$security> 1?false:true],
                'btnMethodDel' => ['attr'=>  ['type'=>'button', 'value'=>lang('remove')], 'hidden'=>$security==4?false:true],
                'btnMethodProp'=> ['icon'=>'settings','size'=>'medium'],
                'settingSave'  => ['icon'=>'save',    'size'=>'large']],
            'lang' => $this->lang];
        $layout = array_replace_recursive($layout, adminStructure($this->moduleID, $this->settingsStructure(), $this->lang), $data);
    }

    private function getViewTools()
    {
        $btnHistTest  = ['label'=>$this->lang['inv_tools_repair_test'], 'attr'=>['type'=>'button','value'=>$this->lang['inv_tools_btn_test']],
            'events' => ['onClick'=>"jsonAction('inventory/tools/historyTestRepair', 0, 'test');"]];
        $btnHistFix   = ['label'=>$this->lang['inv_tools_repair_fix'], 'attr'=>['type'=>'button', 'value'=>$this->lang['inv_tools_btn_repair']],
            'events' => ['onClick'=>"jsonAction('inventory/tools/historyTestRepair', 0, 'fix');"]];
        $btnAllocFix  = ['label'=>'', 'attr'=>['type'=>'button', 'value'=>$this->lang['inv_tools_qty_alloc_label']],
            'events' => ['onClick'=>"jq('body').addClass('loading'); jsonAction('inventory/tools/qtyAllocRepair');"]];
        $btnJournalFix= ['label'=>'', 'attr'=>['type'=>'button', 'value'=>$this->lang['inv_tools_btn_so_po_fix']],
            'events' => ['onClick'=>"jq('body').addClass('loading'); jsonAction('inventory/tools/onOrderRepair');"]];
        $btnPriceAssy = ['label'=>'', 'attr'=>['type'=>'button', 'value'=>lang('go')],
            'events' => ['onClick'=>"jq('body').addClass('loading'); jsonAction('inventory/tools/priceAssy');"]];
        return [
                'invVal'  => ['order'=>10,'label'=>$this->lang['inv_tools_val_inv'],'type'=>'divs','divs'=>[
                    'desc'  => ['order'=>10,'type'=>'html','html'=>"<p>".$this->lang['inv_tools_val_inv_desc']."</p>"],
                    'btnGo1'=> ['order'=>20,'type'=>'html','html'=>"<p>".html5('', $btnHistTest)."</p>"],
                    'btnGo2'=> ['order'=>30,'type'=>'html','html'=>"<p>".html5('', $btnHistFix)."</p>"]]],
                'invAlloc'=> ['order'=>20,'label'=>$this->lang['inv_tools_qty_alloc'],'type'=>'divs','divs'=>[
                    'desc'  => ['order'=>20,'type'=>'html','html'=>"<p>".$this->lang['inv_tools_qty_alloc_desc']."</p>"],
                    'btnGo' => ['order'=>50,'type'=>'html','html'=>"<p>".html5('', $btnAllocFix)."</p>"]]],
                'invSoPo' => ['order'=>30,'label'=>$this->lang['inv_tools_repair_so_po'],'type'=>'divs','divs'=>[
                    'desc'  => ['order'=>20,'type'=>'html','html'=>"<p>".$this->lang['inv_tools_validate_so_po_desc']."</p>"],
                    'btnGo' => ['order'=>50,'type'=>'html','html'=>"<p>".html5('', $btnJournalFix)."</p>"]]],
                'invPrice'=> ['order'=>40,'label'=>$this->lang['inv_tools_price_assy'],'type'=>'divs','divs'=>[
                    'desc'  => ['order'=>20,'type'=>'html','html'=>"<p>".$this->lang['inv_tools_price_assy_desc']."</p>"],
                    'btnGo' => ['order'=>50,'type'=>'html','html'=>"<p>".html5('', $btnPriceAssy)."</p>"]]]];
    }

    public function adminSave()
    {
        readModuleSettings($this->moduleID, $this->settingsStructure());
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
