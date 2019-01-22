<?php
/*
 * Method to handle custom tabs for the tables that allow them
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
 * @version    3.x Last Update: 2018-02-13
 * @filesource /lib/controller/module/bizuno/tabs.php
 */

namespace bizuno;

class bizunoTabs
{
    public $moduleID = 'bizuno';

    function __construct()
    {
        $this->lang = getLang($this->moduleID);
    }
    
    /**
     * manager to handle tab - main entry point
     * @param array $layout - structure coming in
     * @return modified structure
     */
    public function manager(&$layout=[])
    {
        if (!$security = validateSecurity('bizuno', 'admin', 1)) { return; }
        $data = ['type'=>'divHTML',
            'divs' => ['divTab' => ['order'=>50,'type'=>'accordion','key'=>'accTabs']],
            'accordion' => ['accTabs'=>  ['divs'=>  [
                'divTabManager'=> ['order'=>30,'label'=>lang('extra_tabs'),'type'=>'datagrid','key'=>'dgTabs'],
                'divTabDetail' => ['order'=>70,'label'=>lang('details'),'type'=>'html','html'=>'&nbsp;']]]],
            'datagrid'=>  ['dgTabs'=>$this->dgTabs('dgTabs', $security)]];
        $layout = array_replace_recursive($layout, $data);
    }

    /**
     * Lists custom tabs entered by the user
     * @param array $layout - structure coming in
     * @return modified structure
     */
    public function managerRows(&$layout=[])
    {
        global $bizunoMod;
        if (!$security = validateSecurity('bizuno', 'admin', 2)) { return; }
        $output = [];
        foreach ($bizunoMod as $mID => $settings) { if (isset($settings['tabs'])) {
            foreach ($settings['tabs'] as $id => $row) { $output[] = ['id'=>$id,'module_id'=>$mID,'table_id'=>$row['table_id'],'title'=>$row['title'],'sort_order'=>$row['sort_order']]; }
        } }
        $total = sizeof($output);
        $page = clean('page', ['format'=>'integer','default'=>1], 'post');
        $rows = clean('rows', ['format'=>'integer','default'=>getModuleCache('bizuno', 'settings', 'general', 'max_rows')], 'post');
        $sort = clean('sort', ['format'=>'text',   'default'=>'title'], 'post');
        $order= clean('order',['format'=>'text',   'default'=>'asc'], 'post');
        $temp = [];
        foreach ($output as $key => $value) { $temp[$key] = $value[$sort]; }
        array_multisort($temp, $order=='desc'?SORT_DESC:SORT_ASC, $output);
        $parts = array_slice($output, ($page-1)*$rows, $rows);
        $data = ['type'=>'raw', 'content'=>json_encode(['total'=>$total, 'rows'=>$parts])];
        $layout = array_replace_recursive($layout, $data);
    }

    /**
     * Saves the user selections in cache for page reload
     */
    private function managerSettings()
    {
        $data = ['path'=>'xTabs', 'values' => [
            ['index'=>'rows',  'clean'=>'integer','default'=>getModuleCache('bizuno', 'settings', 'general', 'max_rows')],
            ['index'=>'page',  'clean'=>'integer','default'=>'1'],
            ['index'=>'sort',  'clean'=>'text',   'default'=>"title"],
            ['index'=>'order', 'clean'=>'text',   'default'=>'ASC'],
            ['index'=>'search','clean'=>'text',   'default'=>'']]];
        $this->defaults = updateSelection($data);
    }

    /**
     * Datagrid structure for custom tabs
     * @param string $name - DOM name of the datagrid
     * @param integer $security - users given security level.
     * @return array - datagrid structure
     */
    private function dgTabs($name, $security=0)
    {
        $this->managerSettings();
        return ['id'=>$name, 'rows'=>$this->defaults['rows'], 'page'=>$this->defaults['page'],
            'attr'   => ['toolbar'=>"#{$name}Toolbar", 'idField'=>'id','url'=>BIZUNO_AJAX."&p=bizuno/tabs/managerRows"],
            'events' => ['onDblClickRow'=> "function(rowIndex, rowData) { accordionEdit('accTabs', 'dgTabs', 'divTabDetail', '".lang('details')."', 'bizuno/tabs/edit', rowData.id); }"],
            'source' => [
                'actions'=> ['newTab' => ['order'=>10,'icon'=>'new','events'=>  ['onClick'=>"windowEdit('bizuno/tabs/add','winNewTab','".$this->lang['new_tab']."',400,200);"]]],
                'filters'=> ['search' => ['order'=>90,'label' => lang('search'),'attr'=>  ['value'=>$this->defaults['search']]]],
                'sort' => ['s0' => ['order'=>10, 'field' => ("{$this->defaults['sort']} {$this->defaults['order']}")]],
                ],
            'columns'=> [
                'id'     => ['order'=>0, 'attr'=>  ['hidden'=>true]],
                'action' => ['order'=>1, 'label'=>lang('action'),
                    'events' => ['formatter'=>"function(value,row,index){ return {$name}Formatter(value,row,index); }"],
                    'actions'=> [
                        'tabTrash' => ['icon'=>'trash','size'=>'small', 'order'=>90, 'hidden'=>$security>3?false:true,
                            'events'=> ['onClick'=>"if (confirm('".jsLang('msg_confirm_delete')."')) jsonAction('bizuno/tabs/delete', idTBD);"]]]],
                'module_id' =>  ['order'=>10,'label'=>lang('module'),'format'=>'modTitle','attr'=>  ['width'=>160,'sortable'=>true,'resizable'=>true]],
                'table_id'  =>  ['order'=>20,'label'=>lang('table'), 'attr'=>  ['width'=>160,'sortable'=>true,'resizable'=>true]],
                'title'     =>  ['order'=>30,'label'=>lang('title'), 'attr'=>  ['width'=>160,'sortable'=>true,'resizable'=>true]],
                'sort_order'=>  ['order'=>40,'label'=>lang('order'), 'attr'=>  ['width'=>100,'sortable'=>true,'resizable'=>true]]]];
    }

    /**
     * Adds a tab to a specified database table
     * @param array $layout - structure coming in
     * @return modified structure
     */
    public function add(&$layout=[])
    {
        global $bizunoMod;
        $tables = []; // pull in the bizuno tables and search
        require(BIZUNO_LIB."controller/module/bizuno/install/tables.php"); // pulls in core tables
        foreach ($tables as $tID => $value) { if (isset($value['extra_tabs']) && $value['extra_tabs']) {
            $output[] = ['id'=>$value['module'], 'title'=>lang($value['module']), 'table'=>$tID];
        } }
        foreach ($bizunoMod as $module => $props) { if ($props['properties']['status'] && $props['properties']['path']) {
            $fqcn = "\\bizuno\\{$props['properties']['id']}Admin";
            bizAutoLoad($props['properties']['path']."/admin.php", $fqcn);
            $admin = new $fqcn();
            if (isset($admin->tables)) { foreach ($admin->tables as $tID => $value) { if (isset($value['extra_tabs']) && $value['extra_tabs']) {
                $output[] = ['id'=>$admin->moduleID, 'title'=>$admin->lang['title'], 'table'=>$tID];
            } } }
        } }
        $values = [];
        foreach ($output as $props) { 
            $values[] = ['id'=>"{$props['id']}.{$props['table']}", 'text'=>"{$props['title']}/{$props['table']}"];
        }
        $html  = '<p>'.$this->lang['new_tab_desc']."</p>";
        $html .= html5('frmNewTab',['attr' => ['type'=>'form','action'=>BIZUNO_AJAX."&p=bizuno/tabs/edit&bizAction=save"]]);
        $html .= html5('code',     ['values'=>$values, 'attr'=>  ['type'=>'select']]);
        $html .= html5('iconGO',   ['icon'=>'next', 'events'=>  ['onClick'=>"jq('#frmNewTab').submit();"]]);
        $html .= '</form>';
        $html .= htmlJS("ajaxForm('frmNewTab');");
        $layout = array_replace_recursive($layout, ['type'=>'divHTML','divs'=>['divNewTab'=>['order'=>50,'type'=>'html','html'=>$html]]]);
    }

    /**
     * Structure to edit a specific custom tab
     * @param array $layout - structure coming in
     * @return modified structure
     */
    public function edit(&$layout=[])
    {
        global $bizunoMod;
        if (!$security = validateSecurity('bizuno', 'admin', 1)) { return; }
        $rID   = clean('rID',      'integer','get');
        $action= clean('bizAction','text',   'get');
        $code  = clean('code',     'text',   'post');
        $values= [];
        if ($action == 'save') { // sent here from popup to select module/table pair
            if ($code) {
                $tmp = explode('.', $code);
                $rID = validateTab($tmp[0], $tmp[1], 'New Tab', 50);
            }
        }
        if ($rID) {
            foreach ($bizunoMod as $mID => $settings) { if (isset($settings['tabs'][$rID])) {
                $values = $settings['tabs'][$rID];
                $values['module_id'] = $mID;
                msgDebug("\nFound existing tab, values = ".print_r($values, true));
            } }
        }
        if ($action=='save') {
            $data = ['content'=>  ['action'=>'eval','actionData'=>"accordionEdit('accTabs', 'dgTabs', 'divTabDetail', '".lang('details')."', 'bizuno/tabs/edit', $rID); bizWindowClose('winNewTab');"]];
        } else {
            $html  = html5('frmTabs', ['attr'=>  ['type'=>'form','action'=>BIZUNO_AJAX."&p=bizuno/tabs/save"]]);
            $html .= html5('id', ['attr'=>  ['type'=>'hidden', 'value'=>$rID]]);
            $html .= html5('module_id', ['attr'=>  ['type'=>'hidden', 'value'=>isset($values['module_id'])? $values['module_id']: '']]);
            $html .= html5('table_id', ['attr'=>  ['type'=>'hidden', 'value'=>isset($values['table_id']) ? $values['table_id'] : '']]);
            $html .= html5('title', ['label'=>lang('title'), 'attr'=>  ['value'=>isset($values['title']) ? $values['title'] : '']])."<br />";
            $html .= html5('sort_order',  ['label'=>lang('sort_order'), 'attr'=>  ['value'=>isset($values['sort_order']) ? $values['sort_order'] : '']]);
            $html .= html5('', ['icon'=>'save','events'=>  ['onClick'=>"jq('#frmTabs').submit();"]]);
            $html .= '</form>';
            $data = ['type'=>'divHTML',
                'divs'=>  ['tabDetail'=>  ['order'=>50,'type'=>'html','html'=>$html]],
                'jsReady'=> ['tabsNew'=>"ajaxForm('frmTabs');"]];
        }
        $layout = array_replace_recursive($layout, $data);
    }

    /**
     * Structure to save a custom tab to a given db table
     * @param array $layout - structure coming in
     * @return modified structure
     */
    public function save(&$layout=[])
    {
        $id   = clean('id', 'integer', 'post');
        $mID  = clean('module_id', 'text', 'post');
        $tID  = clean('table_id', 'text', 'post');
        $title= clean('title', 'text', 'post');
        $order= clean('sort_order', ['format'=>'integer', 'default'=>50], 'post');
        if (!$security = validateSecurity('bizuno', 'admin', $id?3:2)) { return; }
        $tabs = getModuleCache($mID, 'tabs');
        if (!$id) {
            $id = validateTab($mID, $tID, $title, $order);
        } else {
            $tabs[$id] = ['table_id'=>$tID, 'title'=>$title, 'sort_order'=>$order];
            setModuleCache($mID, 'tabs', false, $tabs);
        }
        msgAdd(lang('tab').": $title - ".lang('msg_settings_saved'), 'success');
        msgLog(lang('tab').": $title (Module:$mID, Table:$tID) - ".($id?lang('update'):lang('add')));
        $data = ['content'=>  ['action'=>'eval','actionData'=>"jq('#accTabs').accordion('select', 0); jq('#dgTabs').datagrid('reload'); jq('#divTabsDetail').html('&nbsp;');"]];
        $layout = array_replace_recursive($layout, $data);
    }

    /**
     * Structure to delete a tab from a specific table
     * @param array $layout - structure coming in
     * @return modified structure
     */
    public function delete(&$layout=[])
    {
        global $bizunoMod;
        if (!$security = validateSecurity('bizuno', 'admin', 4)) { return; }
        $rID = clean('rID', 'integer', 'get');
        // find the module and table from the id
        $mID = 0;
        foreach ($bizunoMod as $module_id => $settings) { if (isset($settings['tabs'])) {
            foreach ($settings['tabs'] as $id => $values) { if ($id == $rID) {
                $mID = $module_id;
                $tID = $values['table_id'];
            } }
        } }
        if (!$rID || !$mID || !$tID) { return msgAdd("Bad ID!"); }
        // Error check, can't delete tab if it has fields
        $struc  = dbLoadStructure(BIZUNO_DB_PREFIX.$tID);
        $usage  = [];
        foreach ($struc as $settings) {
            if ($settings['tab'] == $rID) { $usage[] = $settings['label']; }
        }
        if (sizeof($usage) > 0) { return msgAdd(lang('err_tab_in_use').implode(", ", $usage)); }
        $tabs = getModuleCache($mID, 'tabs');
        $title  = $tabs[$rID]['title'];
        unset($tabs[$rID]);
        setModuleCache($mID, 'tabs', false, $tabs);
        msgLog(lang('tab').": $title (Module:$mID) - ".lang('delete'));
        $data = ['content'=>  ['action'=>'eval','actionData'=>"jq('#dgTabs').datagrid('reload');"]];
        $layout = array_replace_recursive($layout, $data);
    }
}
