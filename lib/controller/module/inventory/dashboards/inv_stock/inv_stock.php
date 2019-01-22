<?php
/*
 * Inventory dashboard - Stock reorder by Vendor column chart
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
 * @version    3.x Last Update: 2018-09-04
 * @filesource /lib/controller/module/phreebooks/dashboards/inv_stock/inv_stock.php
 */

namespace bizuno;

class inv_stock
{
    public $moduleID = 'inventory';
    public $methodDir= 'dashboards';
    public $code     = 'inv_stock';
    public $category = 'inventory';
    
    function __construct($settings)
    {
        $this->security= getUserCache('security', 'inv_mgr', false, 0);
        $defaults      = ['users'=>'-1','roles'=>'-1','reps'=>'0'];
        $this->settings= array_replace_recursive($defaults, $settings);
        $this->lang    = getMethLang($this->moduleID, $this->methodDir, $this->code);
    }

    public function settingsStructure()
    {
        return [
            'users' => ['label'=>lang('users'),    'position'=>'after','values'=>listUsers(),'attr'=>['type'=>'select','value'=>$this->settings['users'],'size'=>10,'multiple'=>'multiple']],
            'roles' => ['label'=>lang('groups'),   'position'=>'after','values'=>listRoles(),'attr'=>['type'=>'select','value'=>$this->settings['roles'],'size'=>10,'multiple'=>'multiple']],
            'reps'  => ['label'=>lang('just_reps'),'position'=>'after','attr'=>['type'=>'selNoYes','value'=>$this->settings['reps']]]];
    }

    public function render()
    {
/*        $cData[]= [lang('vendor'), lang('total')]; // headings
        $rows = dbGetMulti(BIZUNO_DB_PREFIX.'inventory', "qty_stock<(qty_min+qty_so+qty_alloc-qty_po)", '', ['sku','vendor_id','(qty_min+qty_so+qty_alloc-qty_po-qty_stock) AS balance']);
        $vendors = [];
        foreach ($rows as $row) { $vendors[$row['vendor_id']][] = ['sku'=>$row['sku'],'balance'=>$row['balance']]; }
        foreach ($vendors as $id => $skus) {
            $vName = viewFormat($id, 'contactName');
            $total = 0;
            foreach ($skus as $sku) {
                $cost = dbGetValue(BIZUNO_DB_PREFIX.'inventory', 'unit_cost', "sku={$sku['sku']}");
                $total += $sku['balance'] * $cost;
            }
            $cData = [$vName, $total];
        }
        $output = ['divID'=>$this->code."_chart",'type'=>'column','data'=>$cData];
        $js = "var data_{$this->code} = ".json_encode($output).";\n";
        $js.= "function chart{$this->code}() { drawBizunoChart(data_{$this->code}); };\n";
        $js.= "google.charts.load('current', {'packages':['corechart']});\n";
        $js.= "google.charts.setOnLoadCallback(chart{$this->code});\n";
*/
        return 'dashboard not written';
    }
}
