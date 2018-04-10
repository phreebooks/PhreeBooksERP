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
 * @copyright  2008-2018, PhreeSoft
 * @license    http://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 * @version    2.x Last Update: 2018-03-17
 * @filesource /lib/controller/module/inventory/prices.php
 */

namespace bizuno;

class inventoryPrices
{
	public $moduleID = 'inventory';

	function __construct()
    {
        $this->lang = getLang($this->moduleID);
		// set some defaults
		$this->type     = clean('type', ['format'=>'char', 'default'=>'c'], 'get');
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
        if     (!$mod && $this->type == 'c') { $submenu = viewSubMenu('customers'); }
        elseif (!$mod && $this->type == 'v') { $submenu = viewSubMenu('vendors'); }
        else   { $submenu = ''; } 
		$data = [
            'pageTitle'=> $title,
			'divs'     => [
                'submenu'=> ['order'=>10,'type'=>'html','html'=>$submenu],
                'prices' => ['order'=>50,'type'=>'accordion','key'=>'accPrices']],
			'accordion'=> ['accPrices' => ['divs' => [
                'divPricesMgr' => ['order'=>30,'label'=>$title,'type'=>'datagrid','key'=>'dgPricesMgr'],
				'divPricesSet' => ['order'=>50,'label'=>lang('settings'),'type'=>'html',    'html'=>"<p>".$this->lang['msg_no_price_sheets']."</p>"]]]],
			'datagrid' =>  ['dgPricesMgr' => $this->dgPrices('dgPricesMgr', $this->type, $security, $mID, $cID, $iID, $mod)]];
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
		$_POST['search'] = getSearch('priceSearch');
		msgDebug("\n ready to build prices datagrid, security = $security");
		$structure = $this->dgPrices('dgPricesMgr', $this->type, $security, $mID, $cID, $iID);
		$layout = array_replace_recursive($layout, ['type'=>'datagrid', 'structure'=>$structure]);
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
        $data = [
            'id'     => $name,
			'rows'   => $this->defaults['rows'],
			'page'   => $this->defaults['page'],
			'attr'   => [
                'url'     => BIZUNO_AJAX."&p=inventory/prices/managerRows&type=$type".($mID?"&mID=$mID":'').($cID?"&cID=$cID":'').($iID?"&iID=$iID":''),
				'toolbar' => '#'.$name.'Toolbar',
				'pageSize'=> getModuleCache('bizuno', 'settings', 'general', 'max_rows'),
				'idField' => 'id'],
			'events' => [
                'onDblClickRow'=> "function(rowIndex, rowData) { accordionEdit('accPrices','dgPricesMgr','divPricesSet','".jsLang('settings')."','inventory/prices/edit&type=$type".($mod?"&mod=$mod":'')."',rowData.id); }",
				'rowStyler'    => "function(index, row) { if (row.inactive==1) { return {class:'row-inactive'}; } if (row.default==1) { return {class:'row-default'}; } }"],
			'source' => [
                'tables' => [
                    'prices'  => ['table'=>BIZUNO_DB_PREFIX."inventory_prices"],
                    'contacts'=> ['table'=>BIZUNO_DB_PREFIX."contacts",'join'=>'LEFT JOIN','links'=>BIZUNO_DB_PREFIX."inventory_prices.contact_id=".BIZUNO_DB_PREFIX."contacts.id"]],
				'search' => ['settings', 'method', 'currency'],
				'actions'=> [
                    'newPrices'  => ['order'=>10,'html'=>['icon'=>'new',  'events'=>['onClick'=>"windowEdit('inventory/prices/add&type=$type".($mod?"&mod=$mod":'').($cID?"&cID=$cID":'').($iID?"&iID=$iID":'')."','winNewPrice','".jsLang('inventory_prices_method')."',400,200);"]]],
					'clrPrices'  => ['order'=>50,'html'=>['icon'=>'clear','events'=>['onClick'=>"jq('#priceSearch').val(''); ".$name."Reload();"]]]],
				'filters'=> [
                    'priceSearch'=> ['order'=>90,'html'=>['label'=>lang('search'),'attr' => ['value'=>$this->defaults['search']]]],
					'priceType'  => ['order'=>99,'hidden'=>true, 'sql'=>BIZUNO_DB_PREFIX."inventory_prices.contact_type='$type'"]],
				'sort' => ['s0'=>  ['order'=>10, 'field'=>($this->defaults['sort'].' '.$this->defaults['order'])]]],
			'footnotes'=> ['codes'=>lang('color_codes').': <span class="row-default">'.lang('default').'</span>'],
			'columns'  => [
                'id'      => ['order'=>0, 'field'=>BIZUNO_DB_PREFIX.'inventory_prices.id',      'attr'=>['hidden'=>true]],
				'inactive'=> ['order'=>0, 'field'=>BIZUNO_DB_PREFIX.'inventory_prices.inactive','attr'=>['hidden'=>true]],
				'default' => ['order'=>0, 'field'=>BIZUNO_DB_PREFIX.'inventory_prices.settings','attr'=>['hidden'=>true], 'format'=>'setng:default'],
				'action'  => ['order'=>1, 'label'=>lang('action'), 'attr'=>  ['width'=>50],
					'events' => ['formatter'=>"function(value,row,index){ return ".$name."Formatter(value,row,index); }"],
					'actions'=> [
                        'edit' => ['icon'=>'edit',  'size'=>'small', 'order'=>30, 'hidden'=>$security>2?false:true,
							'events'=> ['onClick' => "accordionEdit('accPrices','dgPricesMgr','divPricesSet','".jsLang('settings')."','inventory/prices/edit&type=$type',idTBD);"]],
						'copy' => ['icon'=>'copy',  'size'=>'small', 'order'=>30, 'hidden'=>$security>1?false:true,
							'events'=> ['onClick'=>"var title=prompt('".lang('msg_entry_copy')."'); if (title!=null) jsonAction('inventory/prices/copy', idTBD, title);"]],
						'trash'=> ['icon'=>'trash', 'size'=>'small', 'order'=>90, 'hidden'=>$security>3?false:true,
							'events'=> ['onClick' => "if (confirm('".jsLang('msg_confirm_delete')."')) jsonAction('inventory/prices/delete', idTBD);"]]]],
				'title'      => ['order'=>10, 'field'=>BIZUNO_DB_PREFIX.'inventory_prices.settings',     'label'=>lang("title"),    'format'=>'setng:title',
					'attr'=>  ['width'=>80, 'sortable'=>true, 'resizable'=>true]],
				'method'     => ['order'=>20, 'field'=>BIZUNO_DB_PREFIX.'inventory_prices.method',       'label'=>lang("method"),
					'attr'=>  ['width'=>80, 'sortable'=>true, 'resizable'=>true]],
				'ref_id'     => ['order'=>30, 'field'=>BIZUNO_DB_PREFIX.'inventory_prices.ref_id',       'label'=>lang("reference"),'format'=>'dbVal;'.BIZUNO_DB_PREFIX.'inventory_prices;settings:title;id',
					'attr'=>  ['width'=>80, 'sortable'=>true, 'resizable'=>true]],
				'contact_id' => ['order'=>40, 'field'=>BIZUNO_DB_PREFIX.'inventory_prices.contact_id',   'label'=>lang("address_book_primary_name"),'format'=>'contactName',
					'attr'=>  ['width'=>175, 'sortable'=>true, 'resizable'=>true]],
				'inventory_id'=> ['order'=>50, 'field'=>BIZUNO_DB_PREFIX.'inventory_prices.inventory_id','label'=>lang("sku"),      'format'=>'dbVal;inventory;description_short;id',
					'attr'=>  ['width'=>175, 'sortable'=>true, 'resizable'=>true]],
				'currency'   => ['order'=>60, 'field'=>BIZUNO_DB_PREFIX.'inventory_prices.currency',     'label'=>lang("currency"),
					'attr'=>  ['width'=>60, 'sortable'=>true, 'resizable'=>true]],
				'last_update'=> ['order'=>70, 'field'=>BIZUNO_DB_PREFIX.'inventory_prices.settings',     'label'=>lang("last_update"),'format'=>'setng:last_update',
					'attr'=>  ['width'=>100, 'sortable'=>true, 'resizable'=>true]]]];
        $cList  = $iList = [];
        $search = addslashes($this->defaults['search']);
        if ($mID) { 
            $data['source']['filters']['mID'] = ['order'=>99, 'hidden'=>true, 'sql'=>"method='$mID'"];
        }
        if ($cID) { 
            $data['source']['filters']['cID'] = ['order'=>99, 'hidden'=>true, 'sql'=>"contact_id=$cID"];
        } elseif ($this->defaults['search']) { // see if searching within contact
            $contacts = dbGetMulti(BIZUNO_DB_PREFIX."address_book", "primary_name LIKE '%$search%'", "primary_name {$this->defaults['order']}", ['ref_id']);
            if (sizeof($contacts)) { foreach ($contacts as $cID) { $cList[] = $cID['ref_id']; } }
        }
        if ($iID) { 
            $data['source']['filters']['iID'] = ['order'=>99, 'hidden'=>true, 'sql'=>"inventory_id=$iID"];
        } elseif ($this->defaults['search']) {
            $inventory = dbGetMulti(BIZUNO_DB_PREFIX."inventory", "description_short LIKE '%$search%'", "description_short {$this->defaults['order']}", ['id']);
            if (sizeof($inventory)) { foreach ($inventory as $iID) { $iList[] = $iID['id']; } }
        }
        if (sizeof($cList) && sizeof($iList)) {
            $data['source']['filters']['addSrch'] = ['order'=>99,'hidden'=>true, 'sql'=>BIZUNO_DB_PREFIX."inventory_prices.contact_id IN (".implode(',',$cList).") OR inventory_prices.inventory_id IN (".implode(',',$iList).")"];
        } elseif (sizeof($cList)) {
            $data['source']['filters']['addSrch'] = ['order'=>99,'hidden'=>true, 'sql'=>BIZUNO_DB_PREFIX."inventory_prices.contact_id IN (".implode(',',$cList).")"];
        } elseif (sizeof($iList)) {
            $data['source']['filters']['addSrch'] = ['order'=>99,'hidden'=>true, 'sql'=>BIZUNO_DB_PREFIX."inventory_prices.inventory_id IN (".implode(',',$iList).")"];
        } elseif ($search) {
            $data['source']['filters']['addSrch'] = ['order'=>99,'hidden'=>true, 'sql'=>BIZUNO_DB_PREFIX."inventory_prices.contact_id IN () OR inventory_prices.inventory_id IN ()"];            
        }
		return $data;
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
                require_once(getModuleCache('inventory', 'prices')[$mID]['path']."$mID.php");
                $priceSet = getModuleCache('inventory','prices',$mID,'settings');
                $fqcn = "\\bizuno\\$mID";
                $tmp = new $fqcn($priceSet);
                if (strlen($mod) == 0 || (isset($tmp->structure['hooks']) && array_key_exists($mod, $tmp->structure['hooks']))) {
                    if (getModuleCache('inventory', 'prices')[$mID]['status']) { $meths[] = ['id'=>$mID, 'text'=>$settings['title']]; }
                }
            }
            $html  = '<p>'.lang('desc_new_price_sheets')."</p>";
            $html .= html5('methodID',['values'=>$meths,'attr'=>['type'=>'select']]);
            $html .= html5('iconGO',  ['icon'=>'next','events'=>['onClick'=>"accordionEdit('accPrices','dgPricesMgr','divPricesSet','".jsLang('settings')."','inventory/prices/edit&type=$this->type&mod=$mod&mID='+jq('#methodID').val(),0); jq('#winNewPrice').window('close');"]]);
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
        $mID = clean('mID', ['format'=>'text','default'=>'quantity'],'request');
		if ($rID) {
			$row = dbGetRow(BIZUNO_DB_PREFIX."inventory_prices", "id=$rID");
			$settings = json_decode($row['settings'], true);
			$mID = $row['method'];
		} else { // set the defaults
			$row = ['id'=>0, 'method'=>$mID, 'contact_type'=>$this->type, 'currency'=>getUserCache('profile', 'currency', false, 'USD')];
            $settings = ['attr'=>'', 'title'=>''];
		}
		$data = ['type'=>'divHTML',
			'divs'   => ['tbPrices'=>['order'=> 1, 'type'=>'toolbar','key'=>'tbPrices']],
			'toolbar'=> ['tbPrices'=>['icons'=>[
                'save' => ['order'=>40,'hidden'=>$security>1?false:true,
					'events'=>['onClick'=>"if (preSubmitPrices()) divSubmit('inventory/prices/save&type=$this->type&mID=$mID', 'divPricesSet');"]]]]],
			'fields' => dbLoadStructure(BIZUNO_DB_PREFIX."inventory_prices"),
            'values' => [
                'qtySource' => $this->qtySource,
                'qtyAdj' => $this->qtyAdj,
                'qtyRnd' => $this->qtyRnd]];
        unset($data['fields']['settings']);
        $data['fields']['contact_id']['label'] = lang('contacts_short_name');
        $data['fields']['ref_id']['label'] = $this->lang['price_sheet_to_override'];
        $data['fields']['ref_id']['attr']['type'] = 'select';
        $data['fields']['ref_id']['values'] = $this->quantityList();
        $data['fields']['inventory_id']['label'] = lang('sku');
		if (sizeof(getModuleCache('phreebooks', 'currency', 'iso')) > 1) {
			$data['fields']['currency']['attr']['type'] = 'select';
			$data['fields']['currency']['values'] = viewDropdown(getModuleCache('phreebooks', 'currency', 'iso'), "code", "title");
			unset($data['fields']['currency']['attr']['size']);
		} else {
			$data['fields']['currency']['attr']['type'] = 'hidden';
		}
		dbStructureFill($data['fields'], $row);
		require_once(getModuleCache('inventory', 'prices')[$mID]['path']."$mID.php");
        $priceSet = getModuleCache('inventory','prices',$mID,'settings');
        $fqcn = "\\bizuno\\$mID";
        $meth = new $fqcn($priceSet);
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
		require_once(getModuleCache('inventory', 'prices')[$mID]['path']."$mID.php");
        $priceSet = getModuleCache('inventory','prices',$mID,'settings');
        $fqcn = "\\bizuno\\$mID";
        $meth = new $fqcn($priceSet);
        if ($meth->priceSave()) { msgAdd(lang('msg_record_saved'), 'success'); }
		$layout = array_replace_recursive($layout, ['content'=>  ['action'=>'eval','actionData'=>"jq('#accPrices').accordion('select', 0); jq('#dgPricesMgr').datagrid('reload'); jq('#divPricesSet').html('&nbsp;');"]]);
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
            'content' => ['action'=>'eval','actionData'=>"jq('#accPrices').accordion('select', 0); jq('#dgPricesMgr').datagrid('reload'); jq('#divPricesSet').html('&nbsp;');"],
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
		$sku = clean('sku',       'text',   'get');
		$rID = clean('rID',       'integer','get');
        if     ($rID) { $inv = dbGetRow(BIZUNO_DB_PREFIX."inventory", "id=$rID"); }
        elseif ($sku) { $inv = dbGetRow(BIZUNO_DB_PREFIX."inventory", "sku='$sku'"); }
        else   { return msgAdd("Bad SKU sent!"); }
        $cost= clean('itemCost', ['format'=>'float','default'=>0],'get');
        $full= clean('fullPrice',['format'=>'float','default'=>0],'get');
        if (!$cost) { $cost = $inv['item_cost']; }
        if (!$full) { $full = $inv['full_price']; }
		$this->quote($layout, $cost, $full);
		$data = [
            'divs'   => ['winStatus'=> ['order'=>50, 'src'=>BIZUNO_LIB."view/module/inventory/winPrices.php"]],
			'values' => [
                'price' => isset($layout['content']['price']) ? $layout['content']['price'] : 0,
				'cost'  => $cost,
				'full'  => $full,
				'sheets'=> isset($layout['content']['sheets']) ? $layout['content']['sheets'] : []],
			'content' => ['action'=>'window','id'=>'winPrices','title'=>lang('inventory_prices',$this->type),'width'=>275]];
		$layout = array_replace_recursive($layout, $data);
	}

	/**
     * Retrieves the best price for a given customer/sku using available price sheets
     * @param array $layout - structure coming in
     * @return array - modified $layout
     */
    public function quote(&$layout=[], $cost=0, $full=0)
    {
        $cID = clean('cID', 'integer','get'); // contact ID
        $iID = clean('rID', 'integer','get'); // inventory id
        $sku = clean('sku', 'text',   'get');
        $UPC = clean('upc', 'text',   'get');  // inventory UPC Code
        $qty = clean('qty', ['format'=>'float', 'default'=>1], 'get'); // quantity purchased, assume 1
        if (!$security = validateSecurity('inventory', 'prices_'.$this->type, 1)) { return; }
		if ($cID) {
			$contact = dbGetValue(BIZUNO_DB_PREFIX.'contacts', ['type', 'price_sheet'], "id=$cID");
		} else {
			$contact = ['type'=>$this->type, 'price_sheet'=>''];
		}
		if (!$iID) {
            if ($sku)    { $inv = dbGetValue(BIZUNO_DB_PREFIX.'inventory', ['id', 'item_cost', 'full_price', 'price_sheet_c', 'price_sheet_v'], "sku='$sku'"); }
			elseif ($UPC){ $inv = dbGetValue(BIZUNO_DB_PREFIX.'inventory', ['id', 'item_cost', 'full_price', 'price_sheet_c', 'price_sheet_v'], "upc='$UPC'"); }
		} else {
			$inv = dbGetValue(BIZUNO_DB_PREFIX.'inventory', ['id', 'item_cost', 'full_price', 'price_sheet_c', 'price_sheet_v'], "id=$iID");
		}
        if (!isset($inv['id']) || !$inv['id']) { return; }
        if ($cost) { $inv['item_cost'] = $cost; }
        if ($full) { $inv['full_price'] = $full; }
		if (!$inv['price_sheet_c']) { // if no customer price sheet, see if a default is set
			$result = dbGetMulti(BIZUNO_DB_PREFIX."inventory_prices", "method='quantity'");
			foreach ($result as $row) {
				$settings = json_decode($row['settings'], true);
                if (isset($settings['default']) && $settings['default']) { $inv['price_sheet_c'] = $row['id']; }
			}
		}
        if (!$contact['price_sheet']) { $contact['price_sheet'] = $inv['price_sheet_c']; } // if not set, set to inventory default
		$values = [
            'iID'=>$inv['id'],'iSheetc'=>$inv['price_sheet_c'], 'iSheetv'=>$inv['price_sheet_v'], 'iCost'=>$inv['item_cost'], 'iList'=>$inv['full_price'],
			'cID'=>$cID,      'cSheet'=>$contact['price_sheet'],'cType'=>$contact['type'],  
			'qty'=>abs($qty)]; // to properly handle negative sales/purchases and still get pricing based on method
		msgDebug("\nFinding pricing with qty = $qty and values = ".print_r($values, true));
		$prices = [];
		$this->pricesLevels($prices, $values);
  		msgDebug("\nPrice return array = ".print_r($prices, true));
		$layout = array_replace_recursive($layout, ['source'=>$values, 'content'=>$prices]);
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
                    require_once($settings['path']."$meth.php");
                    $priceSet = getModuleCache('inventory','prices',$meth,'settings');
                    $fqcn = "\\bizuno\\$meth";
                    $est = new $fqcn($priceSet);
                    if (method_exists($est, 'priceQuote')) { $est->priceQuote($prices, $values); }
                }
            }
        }
        if (!isset($prices['price'])) { $prices['price'] = $values['cType']=='v'?$values['iCost']:$values['iList']; }
		// put the default price sheet first
		if (isset($prices['sheets']) && is_array($prices['sheets'])) {
            foreach ($prices['sheets'] as $key => $sheet) {
                if (isset($sheet['default']) && $sheet['default']) { // relocate the default to the first in the array
                    unset($prices['sheets'][$key]);
                    array_unshift($prices['sheets'], $sheet);
                }
            }
        }
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
     * @return array - calcualted price after applying price sheet and pricing levels
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
				case 1: 			           break; // Direct Entry
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
		return ['price'=>$price, 'levels'=>$prices];
	}

	/**
     * Datagrid structure for quantity based pricing
     * @param string $name - DOM field name
     * @return array - datagrid structure
     */
    protected function datagridQuantity($name) {
		$data = [
            'id'   => $name,
			'type' => 'edatagrid',
			'attr' => [
                'toolbar'     => '#'.$name.'Toolbar',
				'rownumbers'  => true,
                ],
			'events' => [
                'data'         => $name.'Data',
				'onLoadSuccess'=> "function(row) { var rows=jq('#$name').edatagrid('getData'); if (rows.total == 0) jq('#$name').edatagrid('addRow'); }",
				'onClickRow'   => "function(rowIndex) { jq('#$name').edatagrid('editRow', rowIndex); }",
                ],
			'source' => [
                'actions' => ['new'=>  ['order'=>10, 'html'=>  ['icon'=>'add','size'=>'large','events'=>  ['onClick'=>"jq('#$name').edatagrid('addRow');"]]]],
                ],
			'columns'=> [
                'action'  => ['order'=>1, 'label'=>lang('action'), 'attr'=>  ['width'=>80],
					'actions'=> ['trash'=>  ['icon'=>'trash','order'=>20,'size'=>'small','events'=>  ['onClick'=>"jq('#$name').edatagrid('destroyRow');"]]],
					'events' => ['formatter'=>"function(value,row,index){ return ".$name."Formatter(value,row,index); }"]],
				'qty'     => ['order'=>10, 'label'=>lang('qty'), 'attr'=>  ['width'=>80, 'align'=>'right'],
					'events'=>  ['editor'=>"{type:'numberbox',options:{formatter:function(value){return formatPrecise(value);}}}"]],
				'source'  => ['order'=>20, 'label'=>lang('source'), 'attr'=>  ['width'=>150, 'sortable'=>true, 'resizable'=>true, 'align'=>'center'],
					'events'=>  ['formatter'=>"function(value){ return getTextValue(qtySource, value); }",
						'editor'=>"{type:'combobox',options:{valueField:'id',textField:'text',data:qtySource,value:'1'}}"]],
				'adjType' => ['order'=>30, 'label'=>lang('adjustment'),'attr' => ['width'=>150],
					'events'=>  ['formatter'=>"function(value){ return getTextValue(qtyAdj, value); }",
						'editor'=>"{type:'combobox',options:{valueField:'id',textField:'text',data:qtyAdj}}"]],
				'adjValue'=> ['order'=>40, 'label'=>$this->lang['adj_value'], 'attr'=>  ['width'=>100, 'align'=>'center', 'size'=>'10'],
					'events'=>  ['editor'=>"{type:'numberbox',options:{formatter:function(value){return formatPrecise(value);}}}"]],
				'rndType' => ['order'=>50, 'label'=>lang('rounding'),'attr' => ['width'=>150],
					'events'=>  ['formatter'=>"function(value){ return getTextValue(qtyRnd, value); }",
						'editor'=>"{type:'combobox',options:{valueField:'id',textField:'text',data:qtyRnd}}"]],
				'rndValue'=> ['order'=>60, 'label'=>$this->lang['rnd_value'], 'attr'=>  ['width'=>100, 'align'=>'center', 'size'=>'10'],
					'events'=>  ['editor'=>"{type:'numberbox',options:{formatter:function(value){return formatPrecise(value);}}}"]],
				'price'   => ['order'=>70, 'label'=>lang('price'), 'attr'=>  ['hidden'=>true, 'width'=>100, 'align'=>'right', 'size'=>'10'],
					'events'=>  ['formatter'=>"function(value,row){ return formatCurrency(value); }",
						'editor'=>"{type:'numberbox',options:{formatter:function(value){return formatPrecise(value);}}}"]],
				'margin'  => ['order'=>80, 'label'=>lang('margin'),'attr'=>  ['hidden'=>true, 'width'=>100, 'align'=>'right', 'size'=>'10']],
                ],
            ];
		return $data;
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
