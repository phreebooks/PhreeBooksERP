<?php
/*
 * Module Inventory main functions
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
 * @version    2.x Last Update: 2018-06-12
 * @filesource /lib/controller/module/inventory/main.php
 */

namespace bizuno;

class inventoryMain
{
	public $moduleID = 'inventory';

	function __construct()
    {
		$this->lang = getLang($this->moduleID);
		$this->helpIndex     = '';
		$this->percent_diff  = 0.10; // the percentage differnece from current value to notify for adjustment
		$this->months_of_data= 12;   // valid values are 1, 3, 6, or 12
		$this->med_avg_diff  = 0.25; // the maximum percentage difference from the median and average, for large swings
		$defaults = [
            'sales'   => getModuleCache('phreebooks', 'chart', 'defaults', getUserCache('profile', 'currency', false, 'USD'))[30],
			'stock'   => getModuleCache('phreebooks', 'chart', 'defaults', getUserCache('profile', 'currency', false, 'USD'))[4],
			'nonstock'=> getModuleCache('phreebooks', 'chart', 'defaults', getUserCache('profile', 'currency', false, 'USD'))[34],
			'cogs'    => getModuleCache('phreebooks', 'chart', 'defaults', getUserCache('profile', 'currency', false, 'USD'))[32],
			'method'  => 'f'];
		// Inventory Types
		$this->inventoryTypes = [
            'si' => ['id'=>'si','text'=>lang('inventory_inventory_type_si'),'hidden'=>0,'tracked'=>1,'order'=>10,'gl_sales'=>$defaults['sales'],'gl_inv'=>$defaults['stock'],   'gl_cogs'=>$defaults['cogs'],'method'=>$defaults['method']], // Stock Item
			'sr' => ['id'=>'sr','text'=>lang('inventory_inventory_type_sr'),'hidden'=>0,'tracked'=>1,'order'=>15,'gl_sales'=>$defaults['sales'],'gl_inv'=>$defaults['stock'],   'gl_cogs'=>$defaults['cogs'],'method'=>$defaults['method']], // Serialized
			'ma' => ['id'=>'ma','text'=>lang('inventory_inventory_type_ma'),'hidden'=>0,'tracked'=>1,'order'=>25,'gl_sales'=>$defaults['sales'],'gl_inv'=>$defaults['stock'],   'gl_cogs'=>$defaults['cogs'],'method'=>$defaults['method']], // Assembly
			'sa' => ['id'=>'sa','text'=>lang('inventory_inventory_type_sa'),'hidden'=>0,'tracked'=>1,'order'=>30,'gl_sales'=>$defaults['sales'],'gl_inv'=>$defaults['stock'],   'gl_cogs'=>$defaults['cogs'],'method'=>$defaults['method']], // Serialized Assembly
			'ns' => ['id'=>'ns','text'=>lang('inventory_inventory_type_ns'),'hidden'=>0,'tracked'=>0,'order'=>35,'gl_sales'=>$defaults['sales'],'gl_inv'=>$defaults['nonstock'],'gl_cogs'=>false,'method'=>false], // Non-stock
			'lb' => ['id'=>'lb','text'=>lang('inventory_inventory_type_lb'),'hidden'=>0,'tracked'=>0,'order'=>40,'gl_sales'=>$defaults['sales'],'gl_inv'=>$defaults['nonstock'],'gl_cogs'=>false,'method'=>false], // Labor
			'sv' => ['id'=>'sv','text'=>lang('inventory_inventory_type_sv'),'hidden'=>0,'tracked'=>0,'order'=>45,'gl_sales'=>$defaults['sales'],'gl_inv'=>$defaults['nonstock'],'gl_cogs'=>false,'method'=>false], // Service
			'sf' => ['id'=>'sf','text'=>lang('inventory_inventory_type_sf'),'hidden'=>0,'tracked'=>0,'order'=>50,'gl_sales'=>$defaults['sales'],'gl_inv'=>$defaults['nonstock'],'gl_cogs'=>false,'method'=>false], // Flat Rate Service
			'ci' => ['id'=>'ci','text'=>lang('inventory_inventory_type_ci'),'hidden'=>0,'tracked'=>0,'order'=>55,'gl_sales'=>$defaults['sales'],'gl_inv'=>$defaults['nonstock'],'gl_cogs'=>false,'method'=>false], // Charge
			'ai' => ['id'=>'ai','text'=>lang('inventory_inventory_type_ai'),'hidden'=>0,'tracked'=>0,'order'=>60,'gl_sales'=>$defaults['sales'],'gl_inv'=>$defaults['nonstock'],'gl_cogs'=>false,'method'=>false], // Activity
			'ds' => ['id'=>'ds','text'=>lang('inventory_inventory_type_ds'),'hidden'=>0,'tracked'=>0,'order'=>65,'gl_sales'=>$defaults['sales'],'gl_inv'=>$defaults['nonstock'],'gl_cogs'=>false,'method'=>false], // Description
			'ia' => ['id'=>'ia','text'=>lang('inventory_inventory_type_ia'),'hidden'=>1,'tracked'=>1,'order'=>99,'gl_sales'=>$defaults['sales'],'gl_inv'=>$defaults['stock'],   'gl_cogs'=>false,'method'=>false], // Assembly Part
			'mi' => ['id'=>'mi','text'=>lang('inventory_inventory_type_mi'),'hidden'=>1,'tracked'=>1,'order'=>99,'gl_sales'=>$defaults['sales'],'gl_inv'=>$defaults['stock'],   'gl_cogs'=>false,'method'=>false], // Master Stock Sub Item
            ];
		$this->inventoryTypes = array_merge_recursive($this->inventoryTypes, getModuleCache('inventory', 'phreebooks'));
	}

	/**
     * Main entry point for inventory module
     * @param array $layout - structure coming in
     * @return modified structure
     */
    public function manager(&$layout=[])
    {
        if (!$security = validateSecurity('inventory', 'inv_mgr', 1)) { return; }
		$title = sprintf(lang('tbd_manager'),lang('gl_acct_type_4'));
		$layout = array_replace_recursive($layout, viewMain(), [
            'pageTitle' => $title,
			'divs'      => [
                'submenu'=> ['order'=>10, 'type'=>'html','html'=>viewSubMenu('inventory')],
                'invMgr'=> ['order'=>50, 'type'=>'accordion','key' =>'accInventory']],
			'accordion' => ['accInventory'=>['divs'=>[
                'divInventoryManager'=> ['order'=>30,'label'=>$title,'type'=>'datagrid','key'=>'manager'],
				'divInventoryDetail' => ['order'=>70,'label'=>lang('details'),'type'=>'html','html'=>'&nbsp;']]]],
			'datagrid'  => ['manager'=>$this->dgInventory('dgInventory', 'none', $security)]]);
	}

	/**
     * Lists inventory rows for the manager datagrid filtered by users request
     * @param array $layout - structure coming in
     * @return modified structure
     */
    public function managerRows(&$layout=[])
    {
        if (!$security = validateSecurity('inventory', 'inv_mgr', 1)) { return; }
        $rID   = clean('rID',   'integer', 'get');
        $filter= clean('filter',['format'=>'text', 'default'=>'none'], 'get');
        $_POST['search']= getSearch();
	    $_POST['rows']  = clean('rows', ['format'=>'integer','default'=>getModuleCache('bizuno', 'settings', 'general', 'max_rows')], 'get');
		msgDebug("\n ready to build inventory datagrid, security = $security");
		$structure = $this->dgInventory('dgInventory', $filter, $security);
        if ($rID) { $structure['source']['filters']['rID'] = ['order'=>99, 'hidden'=>true, 'sql'=>BIZUNO_DB_PREFIX."inventory.id=$rID"]; }
		$layout = array_replace_recursive($layout, ['type'=>'datagrid', 'structure'=>$structure]);
	}

	/**
     * Saves the users filter settings in cache 
     */
    private function managerSettings()
    {
        $data = ['path'=>'inventory', 'values'=>  [
            ['index'=>'rows',  'clean'=>'integer','default'=>getModuleCache('bizuno', 'settings', 'general', 'max_rows'), 'method'=>'request'],
            ['index'=>'page',  'clean'=>'integer','default'=>'1'],
            ['index'=>'sort',  'clean'=>'text',   'default'=>BIZUNO_DB_PREFIX."inventory.sku"],
            ['index'=>'order', 'clean'=>'text',   'default'=>'ASC'],
            ['index'=>'f0',    'clean'=>'char',   'method'=>'request','default'=>'y'],
//          ['index'=>'f1',    'clean'=>'integer','method'=>'request','default'=>'0'],
            ['index'=>'search','clean'=>'text',   'default'=>''],
        ]];
        if (clean('clr', 'boolean', 'get')) { clearUserCache($data['path']); }
		$this->defaults = updateSelection($data);
	}

	/**
     * Generates the datagrid structure for managing bills of materials
     * @param array $layout - structure coming in
     * @return modified structure
     */
    public function managerBOM(&$layout=[])
	{
        if (!$security = validateSecurity('inventory', 'inv_mgr', 1, false)) { return; }
		$rID  = clean('rID', 'integer', 'get');
        if ($rID) {
			$sku    = dbGetValue(BIZUNO_DB_PREFIX."inventory", 'sku', "id=$rID");
			$asyData= dbGetMulti(BIZUNO_DB_PREFIX."inventory_assy_list", "ref_id=$rID");
            foreach ($asyData as $idx => $row) {
                $asyData[$idx]['qty_stock'] = dbGetValue(BIZUNO_DB_PREFIX."inventory", 'qty_stock', "sku='{$row['sku']}'");
            }
			$assemblyData = formatDatagrid($asyData, 'assyData');
			$locked = dbGetValue(BIZUNO_DB_PREFIX."journal_item", 'id', "sku='$sku'");
		} else {
            $assemblyData = "var assyData = ".json_encode(['total'=>0,'rows'=>  []]).";";
            $locked = false;
        }
		$layout = array_replace_recursive($layout, ['type'=>'divHTML',
			'divs'    => ['divVendGrid'=> ['order'=>30,'type'=>'datagrid','key'=>'dgAssembly']],
			'datagrid'=> ['dgAssembly' => $this->dgAssembly('dgAssembly', $locked)],
            'jsHead'  => ['mgrBOMdata' => $assemblyData]]);
        if (!$locked) { $layout['jsReady']['mgrBOM'] = "jq('#dgAssembly').edatagrid('addRow');"; }
	}

	/**
     * Lists the rows of a bill of materials
     * @param array $layout - structure coming in
     * @return modified structure
     */
    public function managerBOMList(&$layout=[])
    {
        if (!$security = validateSecurity('inventory', 'inv_mgr', 1)) { return; }
		$skuID = clean('rID', 'integer', 'get');
        if (!$skuID) { return msgAdd("Cannot process assy list, no SKU ID provided!"); }
		$result= dbGetMulti(BIZUNO_DB_PREFIX."inventory_assy_list", "ref_id=$skuID");
		$total = 0;
		foreach ($result as $key => $row) {
			$result[$key]['qty_stock']   = viewFormat(dbGetValue(BIZUNO_DB_PREFIX."inventory", 'qty_stock', "sku='{$row['sku']}'"), 'precise');
			$result[$key]['qty_required']= viewFormat($row['qty'], 'precise');
			$total += $row['qty'];
		}
		$footer = [['description'=>lang('total'), 'qty_required'=>viewFormat($total, 'precise')]];
		$layout = array_replace_recursive($layout, ['content'=>['total'=>sizeof($result),'rows'=>$result,'footer'=>$footer]]);
	}

	/**
     * Inventory datagrid structure
     * @param string $name - DOM field name
     * @param string $filter - control to limit filtering by inventory type
     * @param integer $security - users security level
     * @return string - datagrid structure
     */
    private function dgInventory($name, $filter='none', $security=0)
    {
		$this->managerSettings();
        $yes_no_choices = [['id'=>'a','text'=>lang('all')],['id'=>'y','text'=>lang('active')],['id'=>'n','text'=>lang('inactive')]];
		switch ($this->defaults['f0']) { // clean up the filter
			default:
			case 'a': $f0_value = ""; break;
			case 'y': $f0_value = "inactive='0'"; break;
			case 'n': $f0_value = "inactive='1'"; break;
		}
		$data = [
            'id'     => $name,
			'rows'   => $this->defaults['rows'],
			'page'   => $this->defaults['page'],
			'attr'   => [
                'url'     => BIZUNO_AJAX."&p=inventory/main/managerRows",
				'toolbar' => '#'.$name.'Toolbar',
				'pageSize'=> getModuleCache('bizuno', 'settings', 'general', 'max_rows'),
				'idField' => 'id'],
			'events' => [
                'onLoadSuccess'=> "function(data) {
					jq('#search').focus();
					jq('#{$name}Toolbar input').keypress(function (e) { if (e.keyCode == 13) { {$name}Reload(); } });
				}",
				'onDblClickRow'=> "function(rowIndex, rowData){ accordionEdit('accInventory', 'dgInventory', 'divInventoryDetail', '".jsLang('details')."', 'inventory/main/edit', rowData.id); }",
				'rowStyler'    => "function(index, row) { if (row.inactive==1) { return {class:'row-inactive'}; }}"],
			'footnotes' => ['codes'=>jsLang('color_codes').': <span class="row-inactive">'.jsLang('inactive').'</span>'],
			'source' => [
                'tables' => ['inventory' => ['table'=>BIZUNO_DB_PREFIX."inventory"]],
				'search' => [BIZUNO_DB_PREFIX.'inventory.sku', 'description_short', 'description_purchase', 'description_sales', 'upc_code'],
				'actions' => [
                    'newInventory'=>['order'=>10, 'html'=>['icon'=>'new',  'events'=>['onClick'=>"accordionEdit('accInventory', 'dgInventory', 'divInventoryDetail', '".lang('details')."', 'inventory/main/edit', 0);"]]],
					'clrSearch'   =>['order'=>50, 'html'=>['icon'=>'clear','events'=>['onClick'=>"jq('#f0').val('y'); jq('#search').val(''); ".$name."Reload();"]]]],
				'filters'=> [
                    'f0' => ['order'=>10, 'sql'=>$f0_value,
						'html'  => ['label'=>lang('status'), 'values'=> $yes_no_choices, 'attr' => ['type'=>'select', 'value'=>$this->defaults['f0']]]],
					'search' => ['order'=>90,'html'=>['attr'=>['value'=>$this->defaults['search']]]]],
				'sort' => ['s0'=>  ['order'=>10, 'field'=>($this->defaults['sort'].' '.$this->defaults['order'])]]],
			'columns' => [
                'id'            => ['order'=>0, 'field'=>'inventory.id',      'attr'=>['hidden'=>true]],
				'inactive'      => ['order'=>0, 'field'=>'inventory.inactive','attr'=>['hidden'=>true]],
				'attach'        => ['order'=>0, 'field'=>'attach',            'attr'=>['hidden'=>true]],
				'inventory_type'=> ['order'=>0, 'field'=>'inventory_type',    'attr'=>['hidden'=>true]],
				'action' => ['order'=>1, 'label'=>lang('action'),'attr'=>['width'=>125],
					'events' => ['formatter'=>"function(value,row,index){ return ".$name."Formatter(value,row,index); }"],
					'actions'=> [
                        'prices' => ['icon'=>'price', 'size'=>'small', 'order'=>20,
							'events'=> ['onClick' => "jsonAction('inventory/prices/details&type=c', idTBD);"]],
						'edit' => ['icon'=>'edit', 'size'=>'small', 'order'=>30,
							'events'=> ['onClick' => "accordionEdit('accInventory', 'dgInventory', 'divInventoryDetail', '".lang('details')."', 'inventory/main/edit', idTBD);"]],
						'rename'=> ['icon'=>'rename', 'size'=>'small', 'order'=>40,
							'events' => ['onClick'=>"var title=prompt('".$this->lang['msg_sku_entry_rename']."'); if (title!=null) jsonAction('inventory/main/rename', idTBD, title);"]],
						'copy' => ['icon'=>'copy', 'size'=>'small', 'order'=>50,
							'events' => ['onClick'=>"var title=prompt('".$this->lang['msg_sku_entry_copy']."'); if (title!=null) jsonAction('inventory/main/copy', idTBD, title);"]],
                        'chart' => ['icon'=>'mimePpt', 'size'=>'small', 'order'=>60, 'label'=>lang('sales'),
							'events'=> ['onClick' => "windowEdit('inventory/tools/chartSales&rID=idTBD', 'myInvChart', '&nbsp;', 600, 500);"]],
						'trash' => ['icon'=>'trash', 'size'=>'small', 'order'=>90, 'hidden'=> $security > 3 ? false : true,
							'events'=> ['onClick' => "if (confirm('".jsLang('msg_confirm_delete')."')) jsonAction('inventory/main/delete', idTBD);"]],
						'attach' => ['order'=>95,'icon'=>'attachment','size'=>'small','display'=>"row.attach=='1'"]]],
				'sku'              => ['order'=>10,'field'=>BIZUNO_DB_PREFIX.'inventory.sku','label'=>pullTableLabel("inventory", 'sku'), 'attr'=>['width'=> 80,'sortable'=>true,'resizable'=>true]],
				'description_short'=> ['order'=>20,'field'=>'description_short','label'=>pullTableLabel("inventory", 'description_short'),'attr'=>['width'=>240,'sortable'=>true,'resizable'=>true]],
				'qty_stock'        => ['order'=>30,'field'=>'qty_stock',        'label'=>pullTableLabel("inventory", 'qty_stock'),        'attr'=>['width'=> 80,'sortable'=>true,'resizable'=>true]],
				'qty_po'           => ['order'=>40,'field'=>'qty_po',           'label'=>pullTableLabel("inventory", 'qty_po'),           'attr'=>['width'=> 80,'sortable'=>true,'resizable'=>true]],
				'qty_so'           => ['order'=>50,'field'=>'qty_so',           'label'=>pullTableLabel("inventory", 'qty_so'),           'attr'=>['width'=> 80,'sortable'=>true,'resizable'=>true]],
				'qty_alloc'        => ['order'=>60,'field'=>'qty_alloc',        'label'=>pullTableLabel("inventory", 'qty_alloc'),        'attr'=>['width'=> 80,'sortable'=>true,'resizable'=>true]]]];
		switch ($filter) {
			case 'stock': $data['source']['filters']['restrict'] = ['order'=>99, 'sql'=>"inventory_type in ('si','sr','ms','mi','ma')"]; break;
			case 'assy':  $data['source']['filters']['restrict'] = ['order'=>99, 'sql'=>"inventory_type in ('ma')"]; break;
			default:
		}
		return $data;
	}

	/**
     * Lists the details of a given inventory item from the database table
     * @param array $layout - structure coming in
     * @return modified structure
     */
    public function detailsType(&$layout=[])
    {
        if (!$security = validateSecurity('inventory', 'inv_mgr', 2)) { return; }
		$type = clean('data', 'text', 'get');
        if (!$type) { msgAdd("No Type passed!"); }
		msgDebug("\n Loading defaults for type = $type");
		$settings = getModuleCache('inventory', 'phreebooks');
		$data = [
            'sales' => isset($settings['sales_'.$type]) ? $settings['sales_'.$type]  : '',
			'inv'   => isset($settings['inv_'.$type])   ? $settings['inv_'.$type]    : '',
			'cogs'  => isset($settings['cog_'.$type])   ? $settings['cog_'.$type]    : '',
			'method'=> isset($settings['method_'.$type])? $settings['method_'.$type] : 'f'];
		$html  = "jq('#gl_sales').val('".$data['sales']."');";
		$html .= "jq('#gl_inv').val('".$data['inv']."');";
		$html .= "jq('#gl_cogs').val('".$data['cogs']."');";
		$html .= "jq('#cost_method').val('".$data['method']."');";
		$layout = array_replace_recursive($layout,['content'=>['action'=>'eval', 'actionData'=>$html]]);
	}

	/**
     * Generates the inventory item edit structure
     * @param array $layout - structure coming in
     * @return modified structure
     */
    public function edit(&$layout=[])
    {
        $security = validateSecurity('inventory', 'inv_mgr', 1);
		$rID = clean('rID', 'integer', 'get');
		$cost_methods = [
            ['id'=>'f', 'text'=>lang('inventory_cost_method_f')],
			['id'=>'l', 'text'=>lang('inventory_cost_method_l')],
			['id'=>'a', 'text'=>lang('inventory_cost_method_a')]];
		$data = ['type'=>'divHTML',
            'title'  => lang('new'),
			'divs'    => [
                'toolbar'=> ['order'=>10,'type'=>'toolbar','key'=>'tbInventory'],
                'heading'=> ['order'=>15,'type'=>'html','html'=>"<h1>".lang('new')."</h1>"],
                'formBOF'=> ['order'=>20,'type'=>'form','key'=>'frmInventory'],
                'tabs'   => ['order'=>50,'type'=>'tabs','key'=>'tabInventory'],
                'formEOF'=> ['order'=>99,'type'=>'html', 'html'=>'</form>']],
			'toolbars' => ['tbInventory'=>['icons'=>[
				'save' => ['order'=>20,'hidden'=>$security >1?false:true,'events'=>  ['onClick'=>"jq('#frmInventory').submit();"]],
                'new'  => ['order'=>40,'hidden'=>$security >1?false:true,'events'=>  ['onClick'=>"accordionEdit('accInventory', 'dgInventory', 'divInventoryDetail', '".lang('details')."', 'inventory/main/edit', 0);"]],
				'trash'=> ['order'=>80,'hidden'=>$rID && $security==4?false:true,'events'=>  ['onClick'=>"if (confirm('".jsLang('msg_confirm_delete')."')) jsonAction('inventory/main/delete', $rID);"]],
				'help' => ['order'=>99,'index' =>$this->helpIndex]]]],
			'tabs'   => ['tabInventory'=> ['divs'=>[
                'general'=> ['order'=>10,'label'=>lang('general'), 'type'=>'divs', 'divs'=>[
                    'genMain'  => ['order'=>20, 'src'=>BIZUNO_LIB."view/module/inventory/tabGeneral.php"],
                    'genPB'    => ['order'=>60, 'src'=>BIZUNO_LIB."view/module/phreebooks/tabInvPhreebooks.php"],
                    'getAttach'=> ['order'=>80, 'src'=>BIZUNO_LIB."view/module/bizuno/divAttach.php",'attr'=>['delPath'=>"$this->moduleID/main/deleteAttach"]]]],
				'history'=> ['order'=>30, 'label'=>lang('history'),'hidden'=>$rID?false:true,'type'=>'html', 'html'=>'',
					'attr'=>  ["data-options"=>"href:'".BIZUNO_AJAX."&p=inventory/main/history&rID=$rID'"]]]]],
			'forms'  => ['frmInventory'=>['attr'=>['type'=>'form','action'=>BIZUNO_AJAX."&p=inventory/main/save"]]],
			'fields' => dbLoadStructure(BIZUNO_DB_PREFIX."inventory"),
			'jsHead' => ['invHead' => "var curIndex = undefined;
var invTypeMsg = [];
icnAction= '';
curIndex = 0;
function preSubmit() {
    if (jq('#dgAssembly').length) {
        jq('#dgAssembly').edatagrid('saveRow');
        var items = jq('#dgAssembly').datagrid('getData');
        var serializedItems = JSON.stringify(items);
        jq('#dg_assy').val(serializedItems);
    }
    if (jq('#dgVendors').length) {
        jq('#dgVendors').edatagrid('saveRow');
        var dgVal = jq('#dgVendors').datagrid('getData');
        var invVendors = JSON.stringify(dgVal['rows'])
        jq('#invVendors').val(invVendors);
    }
    return true;
}"],
            'jsReady' => ['invReady' => "ajaxForm('frmInventory');\njq('.products ul li:nth-child(3n+3)').addClass('last');"]];
		// merge data with structure
		$dbData = dbGetRow(BIZUNO_DB_PREFIX."inventory", "id='$rID'");
        dbStructureFill($data['fields'], $dbData);
		// check to see if some fields should be locked
		if ($rID) {
			$sku = $data['fields']['sku']['attr']['value'];
			$locked = dbGetValue(BIZUNO_DB_PREFIX."journal_item", 'id', "sku='$sku'"); // was inventory_history but if a SO exists will not lock sku field and can change
            $data['divs']['heading']['html'] = "<h1>".$data['fields']['sku']['attr']['value'].' - '.$data['fields']['description_short']['attr']['value']."</h1>";
        } else {
            $sku = '';
            $locked = false;
        }
		// add custom tabs
		customTabs($data, $data['fields'], 'inventory', 'tabInventory');
        // set some special cases
        if (validateSecurity('phreebooks', "j6_mgr", 1, false)) {
            $data['tabs']['tabInventory']['divs']['general']['divs']['genVend'] = ['order'=>40, 'src'=>BIZUNO_LIB."view/module/contacts/tabInvVendors.php"];
        }
		$data['fields']['inventory_type']['values']            = $this->inventoryTypes;
		$data['fields']['inventory_type']['events']['onChange']= "var type=jq('#inventory_type').val(); if (invTypeMsg[type]) alert(invTypeMsg[type])";
		$data['fields']['cost_method']['values']               = $cost_methods;
		$data['fields']['description_sales']['attr']['type']   = 'textarea';
		$data['fields']['description_sales']['attr']['cols']   = '60';
		$data['fields']['description_purchase']['attr']['type']= 'textarea';
		$data['fields']['description_purchase']['attr']['cols']= '60';
		$data['fields']['image_with_path']['attr']['type']     = 'hidden';
        if (isset($data['fields']['image_with_path']['attr']['value'])) {
            $cleanPath = clean($data['fields']['image_with_path']['attr']['value'], 'path_rel');
            if (!file_exists(BIZUNO_DATA."images/$cleanPath")) { $cleanPath = 'images/'; }
            $data['fields']['image_with_path']['attr']['value'] = $cleanPath;
        }
		$data['fields']['qty_stock']['attr']['readonly']= 'readonly';
		$data['fields']['qty_po']['attr']['readonly']   = 'readonly';
		$data['fields']['qty_so']['attr']['readonly']   = 'readonly';
		$data['fields']['qty_alloc']['attr']['readonly']= 'readonly';
		$data['fields']['gl_sales']['js']= htmlComboGL('gl_sales');
		$data['fields']['gl_inv']['js']  = htmlComboGL('gl_inv');
		$data['fields']['gl_cogs']['js'] = htmlComboGL('gl_cogs');
		$data['fields']['item_cost']['classes'] = ['easyui-numberbox'];
		$data['fields']['full_price']['classes'] = ['easyui-numberbox'];
		if ($locked) {
			$data['fields']['sku']['attr']['readonly']           = 'readonly';
			$data['fields']['inventory_type']['attr']['disabled']= 'disabled';
			$data['fields']['cost_method']['attr']['disabled']   = 'disabled';
		}
		if (!$rID) { // set some defaults
			$data['fields']['inventory_type']['attr']['value']= 'si'; // default to stock item
			$data['fields']['inventory_type']['events']       = ['onChange'=>"jsonAction('inventory/main/detailsType', 0, this.value);"];
			$data['fields']['gl_sales']['attr']['value']      = getModuleCache('inventory', 'settings', 'phreebooks', 'sales_si');
			$data['fields']['gl_inv']['attr']['value']        = getModuleCache('inventory', 'settings', 'phreebooks', 'inv_si');
			$data['fields']['gl_cogs']['attr']['value']       = getModuleCache('inventory', 'settings', 'phreebooks', 'cogs_si');
			$data['fields']['cost_method']['attr']['value']   = getModuleCache('inventory', 'settings', 'phreebooks', 'method_si');
			$data['fields']['tax_rate_id_v']['attr']['value'] = getModuleCache('inventory', 'settings', 'general', 'tax_rate_id_v');
			$data['fields']['tax_rate_id_c']['attr']['value'] = getModuleCache('inventory', 'settings', 'general', 'tax_rate_id_c');
		}
		if (sizeof(getModuleCache('inventory', 'prices'))) {
			require_once(BIZUNO_LIB."controller/module/inventory/prices.php");
			$tmp = new inventoryPrices();
			$data['fields']['price_sheet_c']['values'] = $tmp->quantityList('c', true);
			$data['fields']['price_sheet_v']['values'] = $tmp->quantityList('v', true);
		}
		$data['fields']['tax_rate_id_v']['attr']['type'] = 'select';
		$data['fields']['tax_rate_id_v']['values']= viewSalesTaxDropdown('v', $opts='contacts');
		$data['fields']['tax_rate_id_c']['attr']['type'] = 'select';
		$data['fields']['tax_rate_id_c']['values'] = viewSalesTaxDropdown('c', $opts='contacts');
		$data['fields']['vendor_id']['attr']['type'] = 'select';
		$data['fields']['vendor_id']['values']    = dbBuildDropdown(BIZUNO_DB_PREFIX."contacts", "id", "short_name", "type='v' AND inactive<>'1' ORDER BY short_name", lang('none'));
		$data['where_used'] = ['icon'=>'tools', 'size'=>'small', 'label'=>lang('inventory_where_used'), 'hidden'=>$rID?false:true,
			'events'=> ['onClick' => "jsonAction('inventory/main/usage', $rID);"]];
        $block_c = validateSecurity('inventory', 'prices_c', 1, false) ? false : true;
		$data['show_prices_c'] = ['icon'=>'price', 'size'=>'small', 'label'=>lang('prices'), 'hidden'=>$block_c,
			'events'=> ['onClick' => "jsonAction('inventory/prices/details&type=c&itemCost='+jq('#item_cost').numberbox('getValue')+'&fullPrice='+jq('#full_price').numberbox('getValue'), $rID);"]];
        $block_v = validateSecurity('inventory', 'prices_v', 1, false) ? false : true;
		$data['show_prices_v'] = ['icon'=>'price', 'size'=>'small', 'label'=>lang('prices'), 'hidden'=>$block_v,
			'events'=> ['onClick' => "jsonAction('inventory/prices/details&type=v', $rID);"]];
		$data['assy_cost'] = ['icon'=>'payment', 'size'=>'small', 'label'=>lang('inventory_assy_cost'),
			'events'=> ['onClick' => "jsonAction('inventory/main/getCostAssy', $rID);"]];
		// build the attachment structure
		$data['attachPath']   = getModuleCache('inventory', 'properties', 'attachPath');
		$data['attachPrefix'] = "rID_{$rID}_";
		// assembly
		if (in_array($data['fields']['inventory_type']['attr']['value'], ['ma','sa'])) {
			$data['tabs']['tabInventory']['divs']['bom'] = ['order'=>20, 'label'=>lang('inventory_assy_list'),'type'=>'html', 'html'=>'',
					'attr'=>  ["data-options"=>"href:'".BIZUNO_AJAX."&p=inventory/main/managerBOM&rID=$rID'"]];
		}
		$layout = array_replace_recursive($layout, $data);
	}

	/**
     * Generates the structure for inventory properties pop up used in PhreeBooks
     * @param array $layout - structure coming in
     * @return modified structure
     */
    public function properties(&$layout=[])
    {
		$sku = clean('data', 'text', 'get');
        if (!$sku) { return msgAdd("Bad sku passed!"); }
		$_GET['rID'] = dbGetValue(BIZUNO_DB_PREFIX."inventory", 'id', "sku='$sku'");
        compose('inventory', 'main', 'edit', $layout);
        unset($layout['tabs']['tabInventory']['divs']['general']['divs']['getAttach']);
        unset($layout['divs']['toolbar']);
        unset($layout['divs']['formBOF']);
        unset($layout['divs']['formEOF']);
        unset($layout['toolbars']);
		unset($layout['forms']);
        unset($layout['jsHead']);
        unset($layout['jsReady']);
	}
	
	/**
     * Generates the inventory item save structure for recording user updates
     * @param array $layout - structure coming in
     * @param boolean $makeTransaction - [default true] set to false if the save is already a part of another transaction
     * @return modified structure
     */
    public function save(&$layout=[], $makeTransaction=true)
    {
        $type   = clean('inventory_type', ['format'=>'text','default'=>'si'], 'post');
		$values = requestData(dbLoadStructure(BIZUNO_DB_PREFIX."inventory"));
        $values['image_with_path'] = clean('image_with_path', 'path_rel', 'post');
        if (!$security = validateSecurity('inventory', 'inv_mgr', isset($values['id']) && $values['id']?3:2)) { return; }
		$rID = isset($values['id']) && $values['id'] ? $values['id'] : 0;
		$dup = dbGetValue(BIZUNO_DB_PREFIX."inventory", 'sku', "sku='{$values['sku']}' AND id<>$rID"); // check for duplicate sku's
        if ($dup) { return msgAdd(lang('error_duplicate_id')); }
        if (!$values['sku']) { return msgAdd($this->lang['err_inv_sku_blank']); }
		$readonlys = ['qty_stock','qty_po','qty_so','qty_alloc','creation_date','last_update','last_journal_date']; // some special processing
        foreach ($readonlys as $field) { unset($values[$field]); }
        if (!$rID) { $values['creation_date']= date('Y-m-d h:i:s'); }
        else	   { $values['last_update']  = date('Y-m-d h:i:s'); }
        if ($makeTransaction) { dbTransactionStart(); } // START TRANSACTION (needs to be here as we need the id to create links
		$result = dbWrite(BIZUNO_DB_PREFIX."inventory", $values, $rID?'update':'insert', "id=$rID");
        if (!$rID) { $rID = $_POST['id'] = $result; }
        $dgAssy = clean('dg_assy', 'json', 'post');
        if ($dgAssy) { $this->saveBOM($rID, $type, $values['sku'], $dgAssy); } // handle assemblies
        if ($makeTransaction) { dbTransactionCommit(); }
        $io = new \bizuno\io();
        if ($io->uploadSave('file_attach', getModuleCache('inventory', 'properties', 'attachPath')."rID_{$rID}_")) {
            dbWrite(BIZUNO_DB_PREFIX.'inventory', ['attach'=>'1'], 'update', "id=$rID");
        }
		msgAdd(lang('msg_database_write'), 'success');
		msgLog(lang('inventory').'-'.lang('save')." - ".$values['sku']." (rID=$rID)");
		$layout = array_replace_recursive($layout, ['content'=>['action'=>'eval','actionData'=>"jq('#accInventory').accordion('select', 0); jq('#dgInventory').datagrid('reload'); jq('#divInventoryDetail').html('&nbsp;');"]]);
	}

    /**
     * Saves a bill of materials for inventory type AS, MA
     * @param integer $rID - inventory database record id
     * @param string $type - inventory type
     * @param type $sku - item SKU
     * @param type $dgData - JSON encoded list of inventory items that make up the BOM 
     * @return boolean null, BOM is not generated in inventory type is not equal to ma or as
     */
    private function saveBOM($rID, $type, $sku, $dgData)
    {
        if (!in_array($type, ['ma', 'sa'])) { return; }
        if (dbGetValue(BIZUNO_DB_PREFIX."journal_item", 'id', "sku='$sku'")) { return; } // journal entry present , not ok to save
        if (is_array($dgData) && sizeof($dgData) > 0) {
            dbGetResult("DELETE FROM ".BIZUNO_DB_PREFIX."inventory_assy_list WHERE ref_id=$rID");
            foreach ($dgData['rows'] as $row) {
                $bom_array = ['ref_id'=>$rID, 'sku'=>$row['sku'], 'description'=>$row['description'], 'qty'=>$row['qty']];
                dbWrite(BIZUNO_DB_PREFIX."inventory_assy_list", $bom_array);
            }
        }
    }

    /**
     * Structure for renaming inventory items
     * @param array $layout - structure coming in
     * @return modified structure
     */
    public function rename(&$layout=[])
    {
        if (!$security = validateSecurity('inventory', 'inv_mgr', 3)) { return; }
		$rID    = clean('rID', 'integer','get');
		$newSKU = clean('data','text',   'get');
		$sku    = dbGetRow(BIZUNO_DB_PREFIX."inventory", "id=$rID");
		$oldSKU = $sku['sku'];
		// make sure new SKU is not null
        if (strlen($newSKU) < 1) { return msgAdd($this->lang['err_inv_sku_blank']); }
		// check for duplicate skus
		$found = dbGetValue(BIZUNO_DB_PREFIX."inventory", 'id', "sku = '$newSKU'");
        if ($found) { return msgAdd(lang('error_duplicate_id')); }
		$data = [
            'content' => ['action'=>'eval', 'actionData'=> "jq('#dgInventory').datagrid('reload');"],
			'dbAction'=> [
                "inventory"          => "UPDATE ".BIZUNO_DB_PREFIX."inventory SET sku='$newSKU' WHERE id='$rID'",
				"inventory_assy_list"=> "UPDATE ".BIZUNO_DB_PREFIX."inventory_assy_list SET sku='$newSKU' WHERE sku='$oldSKU'",
				"inventory_history"  => "UPDATE ".BIZUNO_DB_PREFIX."inventory_history SET sku='$newSKU' WHERE sku='$oldSKU'",
				"journal_cogs_owed"  => "UPDATE ".BIZUNO_DB_PREFIX."journal_cogs_owed SET sku='$newSKU' WHERE sku='$oldSKU'",
				"journal_item"       => "UPDATE ".BIZUNO_DB_PREFIX."journal_item SET sku='$newSKU' WHERE sku='$oldSKU'",
                ],
            ];
		msgLog(lang('inventory').' '.lang('rename')." - $oldSKU ($rID) -> $newSKU");
		$layout = array_replace_recursive($layout, $data);
	}

	/**
     * Structure for copying inventory items
     * @param array $layout - structure coming in
     * @return modified structure
     */
    public function copy(&$layout=[])
    {
        if (!$security = validateSecurity('inventory', 'inv_mgr', 2)) { return; }
		$rID    = clean('rID', 'integer', 'get');
		$newSKU = clean('data','text', 'get'); // new sku
        if (!$newSKU) { return msgAdd($this->lang['err_inv_sku_blank']); }
		$sku    = dbGetRow(BIZUNO_DB_PREFIX."inventory", "id=$rID");
		$oldSKU = $sku['sku'];
		// check for duplicate skus
		$found = dbGetValue(BIZUNO_DB_PREFIX."inventory", 'id', "sku='$newSKU'");
        if ($found) { return msgAdd(lang('error_duplicate_id')); }
		// clean up the fields (especially the system fields, retain the custom fields)
		foreach ($sku as $key => $value) {
			switch ($key) {
				case 'sku':          $sku[$key] = $newSKU; break; // set the new sku
				case 'creation_date':
				case 'last_update':  $sku[$key] = date('Y-m-d H:i:s'); break;
				case 'id':	// Remove from write list fields
				case 'last_journal_date':
				case 'item_cost':
				case 'upc_code':
				case 'image_with_path':
				case 'qty_stock':
				case 'qty_po':
				case 'qty_so': 
				case 'qty_alloc': unset($sku[$key]); break;
				default:
			}
		}
		$nID = dbWrite(BIZUNO_DB_PREFIX."inventory", $sku);
		if ($sku['inventory_type'] == 'ma' || $sku['inventory_type'] == 'sa') { // copy assembly list if it's an assembly
			$result = dbGetMulti(BIZUNO_DB_PREFIX."inventory_assy_list", "ref_id = '$rID'");
			foreach ($result as $value) {
				$sqlData = [
                    'ref_id'      => $nID,
					'sku'         => $value['sku'],
					'description' => $value['description'],
					'qty'         => $value['qty'],
                    ];
				dbWrite(BIZUNO_DB_PREFIX."inventory_assy_list", $sqlData);
			}
		}
		$result = dbGetMulti(BIZUNO_DB_PREFIX."inventory_prices", "inventory_id=$rID AND contact_id=0");
		foreach ($result as $value) { // just copy over the price sheets by SKU, skip byContact and others
			unset($value['id']);
			$value['inventory_id'] = $nID;
			dbWrite(BIZUNO_DB_PREFIX."inventory_prices", $value);
		}
		msgLog(lang('inventory').'-'.lang('copy')." - $oldSKU => $newSKU");
		$layout = array_replace_recursive($layout, ['content' => ['action'=>'eval','actionData'=>"jq('#dgInventory').datagrid('reload'); accordionEdit('accInventory', 'dgInventory', 'divInventoryDetail', '".lang('details')."', 'inventory/main/edit', $nID);"],
            ]);
	}

	/**
     * Structure for deleting inventory items
     * @param array $layout - structure coming in
     * @return modified structure
     */
    public function delete(&$layout=[])
    {
        if (!$security = validateSecurity('inventory', 'inv_mgr', 4)) { return; }
		$rID = clean('rID', 'integer', 'get');
        if (!$rID) { return msgAdd('Bad Record ID!'); }
		$item = dbGetRow(BIZUNO_DB_PREFIX."inventory", "id=$rID");
		$sku    = clean($item['sku'], 'text');
		// Check to see if this item is part of an assembly
		$block0 = dbGetValue(BIZUNO_DB_PREFIX."inventory_assy_list", 'id', "sku='$sku'");
        if ($block0) { return msgAdd($this->lang['err_inv_delete_assy']); }
		$block1 = dbGetValue(BIZUNO_DB_PREFIX."journal_item", 'id', "sku='$sku'");
        if ($sku && $block1 && strpos(COG_ITEM_TYPES, $item['inventory_type']) !== false) { return msgAdd($this->lang['err_inv_delete_gl_entry']); }
		$data = ['content' => ['action'=>'eval','actionData'=>"jq('#accInventory').accordion('select', 0); jq('#dgInventory').datagrid('reload'); jq('#divInventoryDetail').html('&nbsp;');"],
			'dbAction'=> [
                "inventory"          => "DELETE FROM ".BIZUNO_DB_PREFIX."inventory WHERE id='$rID'",
				"inventory_prices"   => "DELETE FROM ".BIZUNO_DB_PREFIX."inventory_prices WHERE inventory_id='$rID'",
				"inventory_assy_list"=> "DELETE FROM ".BIZUNO_DB_PREFIX."inventory_assy_list WHERE ref_id='$rID'"]];
		$files = glob(getModuleCache('inventory', 'properties', 'attachPath')."rID_{$rID}_*.*");
        if (is_array($files)) { foreach ($files as $filename) { @unlink($filename); } } // remove attachments
		msgLog(lang('inventory').' '.lang('delete')." - $sku ($rID)");
		$layout = array_replace_recursive($layout, $data);
	}

    /**
     * Deletes an attachment from an inventory item
     * @param array $layout - structure coming in
     * @return modified structure
     */
    public function deleteAttach(&$layout=[])
    {
        $io = new io;
        $io->attachDelete($layout, $this->moduleID, $pfxID='rID_');
    }
    
	/**
     * Datagrid structure for assembly material lists
     * @param string $name - DOM field name
     * @param boolean $locked - [default true] leave unlocked if no journal activity has been entered for this sku
     * @return string - datagrid structure
     */
    private function dgAssembly($name, $locked=true)
    {
		$data = [
            'id'  => $name,
			'type'=> 'edatagrid',
			'attr'=> [
                'width'       => $locked ? 660 : 740,
				'pagination'  => false,
				'rownumbers'  => true,
				'singleSelect'=> true,
				'toolbar'     => "#{$name}Toolbar",
				'idField'     => 'id'],
			'events' => [
                'data'       => "assyData",
				'onClickRow' => "function(rowIndex, row) { curIndex = rowIndex; }",
				'onBeginEdit'=> "function(rowIndex, row) { curIndex = rowIndex; }",
				'onDestroy'  => "function(rowIndex, row) { curIndex = undefined; }",
				'onAdd'      => "function(rowIndex, row) { curIndex = rowIndex; }"],
			'source' => ['actions'=>['newAssyItem'=>['order'=>10,'html'=>['icon'=>'add','size'=>'large','events'=>['onClick'=>"jq('#$name').edatagrid('addRow');"]]]]],
			'columns' => [
                'id'      => ['order'=>0, 'attr'=>['hidden'=>true]],
				'action'  => ['order'=>1, 'label'=>lang('action'), 'attr'=>['width'=>80],
					'events' => ['formatter'=>"function(value,row,index){ return {$name}Formatter(value,row,index); }"],
					'actions'=> [
                        'trash'=>  ['icon'=>'trash','order'=>20,'size'=>'small','events'=>['onClick'=>"if (confirm('".jsLang('msg_confirm_delete')."')) jq('#$name').edatagrid('deleteRow', curIndex);"]]]],
				'sku'=> ['order'=>30, 'label'=>lang('sku'),
					'attr'  => ['width'=>150, 'sortable'=>true, 'resizable'=>true, 'align'=>'center'],
					'events'=> ['editor'=>"{type:'combogrid',options:{ url:'".BIZUNO_AJAX."&p=inventory/main/managerRows&clr=1',
						width:150, panelWidth:320, delay:500, idField:'sku', textField:'sku', mode:'remote',
						onClickRow: function (idx, data) { 
							var descEditor= jq('#$name').datagrid('getEditor', {index:curIndex,field:'description'});
							descEditor.target.val(data.description_short);
							var qtyEditor = jq('#$name').datagrid('getEditor', {index:curIndex,field:'qty'});
							jq(qtyEditor.target).numberbox('setValue',1); },
						columns:[[{field:'sku',              title:'".lang('sku')."',        width:100},
								  {field:'description_short',title:'".lang('description')."',width:200}]]
					}}"]],
				'description'=> ['order'=>40, 'label'=>lang('description'),
					'attr' => ['width'=>280, 'editor'=>'text', 'sortable'=>true, 'resizable'=>true]],
				'qty'      => ['order'=>60, 'label'=>lang('qty_needed'), 'attr'=>['value'=>1,'width'=>90,'resizable'=>true,'align'=>'center'],
					'events'=> ['editor'=>"{type:'numberbox'}"]],
				'qty_stock' => ['order'=>90,'label'=>pullTableLabel("inventory", 'qty_stock'),'attr'=>['width'=>90,'resizable'=>true,'align'=>'center']]]];
		if ($locked) {
			unset($data['columns']['action']);
			unset($data['columns']['sku']['events']['editor']);
			unset($data['columns']['description']['attr']['editor']);
			unset($data['columns']['qty']['events']['editor']);
			unset($data['source']);
		}
		return $data;
	}

	/**
     * calculates the cost of building an assembly
     * @return entry is made in the message queue with current assembly cost
     */
    public function getCostAssy($rID=0)
    {
        if (!$rID) { $rID = clean('rID', 'integer', 'get'); }
        $cost = dbGetInvAssyCost($rID);
        msgAdd(sprintf($this->lang['msg_inventory_assy_cost'], viewFormat($cost, 'currency')), 'caution');
	}

	/**
     * Generates lists of historical values for an inventory item for the past 12 months
     * @param array $layout - structure coming in
     * @return modified structure
     */
    public function history(&$layout=[])
    {
        if (!$security = validateSecurity('inventory', 'inv_mgr', 1)) { return; }
		$rID = clean('rID', 'integer', 'get');
        if (!$rID) { return ("This SKU does not have any history!"); }
		$skuInfo = dbGetRow(BIZUNO_DB_PREFIX."inventory", "id='$rID'");
        $sku = $skuInfo['sku'];
		msgDebug("\nEntering inventory history with rID = $rID and sku = $sku");
		$history = ['id'=>$rID,'open_po'=>[],'open_so'=>[],'purchases'=>[],'sales'=>[]];
        $history['create'] = ['label'=>lang('inventory_creation_date'),    'attr'=>['type'=>'date','readonly'=>'readonly','value'=>$skuInfo['creation_date']]];
        $history['update'] = ['label'=>lang('last_update'),                'attr'=>['type'=>'date','readonly'=>'readonly','value'=>$skuInfo['last_update']]];
        $history['journal']= ['label'=>lang('inventory_last_journal_date'),'attr'=>['type'=>'date','readonly'=>'readonly','value'=>$skuInfo['last_journal_date']]];
		// load the SO's and PO's and get order, expected del date
		$sql = "SELECT m.id, m.journal_id, m.store_id, m.invoice_num, i.qty, i.post_date, i.date_1,
			i.id AS item_id FROM ".BIZUNO_DB_PREFIX."journal_main m JOIN ".BIZUNO_DB_PREFIX."journal_item i ON m.id=i.ref_id
			WHERE m.journal_id IN (4, 10) AND i.sku='$sku' AND m.closed='0' ORDER BY i.date_1";
        if (!$stmt  = dbGetResult($sql)) { return; }
		$result= $stmt->fetchAll(\PDO::FETCH_ASSOC);
		msgDebug("\nReturned number of open SO/PO rows = ".sizeof($result));
		foreach ($result as $row) {
			switch ($row['journal_id']) {
				case  4: $hist_type = 'open_po'; break;
				case 10: $hist_type = 'open_so'; break;
			}
            $adj = dbGetValue(BIZUNO_DB_PREFIX."journal_item", "SUM(qty) AS qty", "gl_type='itm' AND item_ref_id={$row['item_id']}", false);
            msgDebug("\nadj = $adj and row = ".print_r($row, true));
			if ($row['qty'] > $adj) {
				$history[$hist_type][] = [
                    'id'         => $row['id'],
					'store_id'   => viewFormat($row['store_id'], 'storeID'),
					'invoice_num'=> $row['invoice_num'],
					'post_date'  => viewDate($row['post_date']),
					'qty'        => $row['qty'] - $adj,
					'date_1'     => viewDate($row['date_1'])];
			}
		}
		// load the units received and sold, assembled and adjusted
		$dates = localeGetDates();
		$cur_month = $dates['ThisYear'].'-'.substr('0'.$dates['ThisMonth'], -2).'-01';
		for ($i = 0; $i < 13; $i++) {
			$index = substr($cur_month, 0, 7);
			$month = substr($index, 5, 2);
			$year  = substr($index, 0, 4);
			$history['purchases'][$index]= ['year'=>$year, 'month'=>lang('month_'.$month), 'qty'=>0, 'total'=>0, 'usage'=>0];
			$history['sales'][$index]    = ['year'=>$year, 'month'=>lang('month_'.$month), 'qty'=>0, 'total'=>0, 'usage'=>0];
			$cur_month = localeCalculateDate($cur_month, 0, -1, 0);
		}
		$next_month = localeCalculateDate($dates['ThisYear'].'-'.substr('0'.$dates['ThisMonth'], -2).'-01', 0, 1, 0);
		$last_year = ($dates['ThisYear']-1).'-'.substr('0'.$dates['ThisMonth'], -2).'-01';
		$sql = "SELECT m.journal_id, m.post_date, i.qty, i.gl_type, i.credit_amount, i.debit_amount
			FROM ".BIZUNO_DB_PREFIX."journal_main m JOIN ".BIZUNO_DB_PREFIX."journal_item i ON m.id=i.ref_id
			WHERE m.journal_id IN (6,7,12,13,14,16,19,21) AND i.sku='$sku' AND m.post_date>='$last_year' AND m.post_date<'$next_month'
			ORDER BY m.post_date DESC";
		$stmt  = dbGetResult($sql);
		$result= $stmt->fetchAll(\PDO::FETCH_ASSOC);
		msgDebug("\nReturned monthly sales/purchases rows = ".sizeof($result));
		foreach ($result as $row) {
			$index = substr($row['post_date'], 0, 7);
			switch ($row['journal_id']) {
				case  6:
				case 21: $history['purchases'][$index]['qty']   += $row['qty'];
						 $history['purchases'][$index]['usage'] += $row['qty'];
						 $history['purchases'][$index]['total'] += $row['debit_amount']; break;
				case  7: $history['purchases'][$index]['qty']   -= $row['qty'];
						 $history['purchases'][$index]['usage'] -= $row['qty'];
						 $history['purchases'][$index]['total'] -= $row['debit_amount']; break;
				case 12:
				case 19: $history['sales'][$index]['qty']       += $row['qty'];
						 $history['sales'][$index]['usage']     += $row['qty'];
						 $history['sales'][$index]['total']     += $row['credit_amount']; break;
				case 13: $history['sales'][$index]['qty']       -= $row['qty'];
						 $history['sales'][$index]['usage']     -= $row['qty'];
						 $history['sales'][$index]['total']     -= $row['debit_amount']; break;
                case 14: if ($row['gl_type'] == 'asi') { $history['sales'][$index]['usage'] -= $row['qty']; } break;
				case 16: $history['sales'][$index]['usage']     += $row['qty']; break;
			}
		}
		// calculate average usage
		$history['01purch']  = 0;
		$history['03purch']  = 0;
		$history['06purch']  = 0;
		$history['12purch']  = 0;
		$cnt = 0;
		$history['purchases']= array_values($history['purchases']);
		foreach ($history['purchases'] as $key => $value) {
			if ($cnt == 0) { $cnt++; continue; } // skip current month since we probably don't have the full months worth
			$history['12purch']               += $value['usage'];
            if ($cnt < 7) { $history['06purch'] += $value['usage']; }
            if ($cnt < 4) { $history['03purch'] += $value['usage']; }
            if ($cnt < 2) { $history['01purch'] += $value['usage']; }
			$cnt++;
		}
		$history['12purch'] = round($history['12purch'] / 12);
		$history['06purch'] = round($history['06purch'] /  6);
		$history['03purch'] = round($history['03purch'] /  3);
		
		$history['01sales'] = 0;
		$history['03sales'] = 0;
		$history['06sales'] = 0;
		$history['12sales'] = 0;
		$cnt   = 0;
		$sales = [];
		$history['sales']   = array_values($history['sales']);
		foreach ($history['sales'] as $key => $value) {
			if ($cnt == 0) { $cnt++; continue; }
			$history['12sales']               += $value['usage'];
            if ($cnt < 7) { $history['06sales'] += $value['usage']; }
            if ($cnt < 4) { $history['03sales'] += $value['usage']; }
            if ($cnt < 2) { $history['01sales'] += $value['usage']; }
            if ($cnt <= $this->months_of_data) { $sales[] = $value['usage']; }
			$cnt++;
		}
		$history['12sales'] = round($history['12sales'] / 12);
		$history['06sales'] = round($history['06sales'] /  6);
		$history['03sales'] = round($history['03sales'] /  3);
		// find the restock levels that need adjustment
		if (getModuleCache('inventory', 'settings', 'general', 'stock_usage') && validateSecurity('phreebooks', 'j6_mgr', 3, false)) {
			$inv = dbGetValue(BIZUNO_DB_PREFIX."inventory", ['qty_min', 'lead_time'], "sku='$sku'");
			sort($sales);
			$months = substr('0'.$this->months_of_data, -2);
			$idx           = ceil(count($sales) / 2);
			$median_sales  = $sales[$idx];
			$average_sales = ceil($history[$months.'sales']);
			$new_min_stock = ceil($inv['lead_time'] / 30) * $average_sales;
			$high_band     = $inv['qty_min'] *(1 + $this->percent_diff);
			$low_band      = $inv['qty_min'] *(1 - $this->percent_diff);
			$high_avg      = $average_sales * (1 + $this->med_avg_diff);
			$low_avg       = $average_sales * (1 - $this->med_avg_diff);
			if ($new_min_stock > $high_band || $new_min_stock < $low_band) {
				msgAdd(sprintf($this->lang['msg_inv_qty_min'], $new_min_stock), 'caution');
			}
			if ($median_sales > $high_avg || $median_sales < $low_avg) {
				msgAdd(sprintf($this->lang['msg_inv_median'], $median_sales, $average_sales), 'caution');
			}
		}
		msgDebug("\nReturning from inventory history with array = ".print_r($history, true));
		$layout = array_replace_recursive($layout, ['type'=>'divHTML',
			'divs'   => ['history' => ['order'=>30, 'src'=>BIZUNO_LIB."view/module/inventory/tabHistory.php"]],
            'lang'   => $this->lang,
			'history'=> $history]);
	}

	/**
     * Generates the Where Used? pop up window displaying where a sku is used in other sku's
     * @return usage statistics added to message queue
     */
    public function usage()
    {
        if (!$security = validateSecurity('inventory', 'inv_mgr', 1)) { return; }
		$rID = clean('rID', 'integer', 'get');
		$sku = dbGetValue(BIZUNO_DB_PREFIX."inventory", 'sku', "id=$rID");
        if (!$sku) { return msgAdd("Cannot find sku!"); }
		$result = dbGetMulti(BIZUNO_DB_PREFIX."inventory_assy_list", "sku='$sku'", 'sku');
        if (sizeof($result)==0) { return msgAdd("Cannot find any usage!"); }
		$output = [];
		foreach ($result as $row) {
			$inv = dbGetValue(BIZUNO_DB_PREFIX."inventory", ['sku', 'description_short'], "id={$row['ref_id']}");
			$output[] = ['qty'=>$row['qty'], 'sku'=>$inv['sku'], 'desc'=>$inv['description_short']];
		}
		$temp = [];
        foreach ($output as $key => $value) { $temp[$key] = $value['sku']; }
		array_multisort($temp, SORT_ASC, $output);
		msgAdd("This SKU is used in the following assemblies:", 'caution');
        foreach ($output as $row) { msgAdd("Qty: {$row['qty']} SKU: {$row['sku']} - {$row['desc']}", 'caution'); }
	}

    /**
     * Generates a list of stock available to build a given number of assemblies to determine if enough product is on hand
     * @return status message is added to user message queue
     */
    public function getStockAssy()
    {
		$sID = clean('rID', 'integer', 'get');
		$qty = clean('qty', ['format'=>'float','default'=>1], 'get');
        if (!$sID) { return msgAdd("Bad record ID!"); }
		$result = dbGetMulti(BIZUNO_DB_PREFIX."inventory_assy_list", "ref_id=$sID");
        if (sizeof($result) == 0) { return msgAdd($this->lang['err_inv_assy_error']); }
		$shortages = [sprintf($this->lang['err_inv_assy_low_stock'], $qty)];
		foreach ($result as $row) {
			$stock = dbGetValue(BIZUNO_DB_PREFIX."inventory", "qty_stock", "sku='{$row['sku']}'");
            if ($row['qty']*$qty > $stock) { $shortages[] = sprintf($this->lang['err_inv_assy_low_list'], $row['sku'], $row['description'], $stock, $row['qty']*$qty); }
		}
        if (sizeof($shortages) > 1) { msgAdd(implode("<br />", $shortages), 'caution'); }
        else { msgAdd($this->lang['msg_inv_assy_stock_good'], 'success'); }
	}
}
