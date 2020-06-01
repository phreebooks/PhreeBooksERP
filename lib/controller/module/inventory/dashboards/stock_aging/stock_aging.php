<?php
/*
 * Inventory dashboard - list aging stock that needs attention
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
 * @version    4.x Last Update: 2019-12-03
 * @filesource /lib/controller/module/inventory/dashboards/stock_aging/stock_aging.php
 */

namespace bizuno;

class stock_aging
{
    public  $moduleID = 'inventory';
    public  $methodDir= 'dashboards';
    public  $code     = 'stock_aging';
    public  $category = 'inventory';

    function __construct($settings=[])
    {
        $this->security= getUserCache('security', 'inv_mgr', false, 0);
        $defaults      = ['users'=>'-1','roles'=>'-1','defAge'=>4];
        $this->settings= array_replace_recursive($defaults, $settings);
        $this->lang    = getMethLang($this->moduleID, $this->methodDir, $this->code);
    }

    public function settingsStructure()
    {
        $weeks = [1,2,3,4,6,8,13,26,39,52,104];
        foreach ($weeks as $week) { $ages[] = ['id'=>$week, 'text'=>$week]; }
        return [
            'users' => ['label'=>lang('users'), 'position'=>'after','values'=>listUsers(),'attr'=>['type'=>'select','value'=>$this->settings['users'],'size'=>10,'multiple'=>'multiple']],
            'roles' => ['label'=>lang('groups'),'position'=>'after','values'=>listRoles(),'attr'=>['type'=>'select','value'=>$this->settings['roles'],'size'=>10,'multiple'=>'multiple']],
            'defAge'=> ['label'=>$this->lang['age_default'],'position'=>'after','values'=>$ages,'attr'=>['type'=>'select','value'=>$this->settings['defAge']]]];
    }

    public function render(&$layout=[])
    {
        $ttlQty      = $ttlCost = $value = 0;
        $this->struc = $this->settingsStructure();
        $this->ageFld= dbFieldExists(BIZUNO_DB_PREFIX.'inventory', 'shelf_life') ? true : false;
        $iconExp     = ['attr'=>['type'=>'button','value'=>lang('download')],'events'=>['onClick'=>"jq('#form{$this->code}').submit();"]];
        $action      = BIZUNO_AJAX."&p=$this->category/tools/stockAging";
        $js          = "jq.cachedScript('".BIZUNO_URL."../apps/jquery-file-download.js?ver=".MODULE_BIZUNO_VERSION."');
ajaxDownload('form{$this->code}');
function chart{$this->code}() {
    var data = new google.visualization.DataTable();
    data.addColumn('string', '".jsLang('post_date')."');
    data.addColumn('string', '".jsLang('sku')."');
    data.addColumn('number','" .jsLang('remaining')."');
    data.addColumn('number', '".jsLang('value')."');
    data.addRows([";
        $rows = dbGetMulti(BIZUNO_DB_PREFIX.'inventory_history', "remaining>0", 'post_date', ['sku', 'post_date', 'remaining', 'unit_cost']);
        foreach ($rows as $row) {
            $ageDate = $this->getAgingValue($row['sku']);
            msgDebug("\nsku {$row['sku']} comparing ageDate: $ageDate with post date: {$row['post_date']}");
            if ($row['post_date'] >= $ageDate) { continue; }
            $ttlQty += $row['remaining'];
            $value   = $row['unit_cost'] * $row['remaining'];
            $ttlCost+= $value;
            $js     .= "['".viewFormat($row['post_date'], 'date')."','".$row['sku']."',{v: ".intval($row['remaining'])."},{v:$value, f:'".viewFormat($value,'currency')."'}],";
        }
        $js .= "['".jslang('total')."',' ',{v: ".intval($ttlQty)."},{v: $value, f: '".viewFormat($ttlCost,'currency')."'}]]);
    data.setColumnProperties(0, {style:'font-style:bold;font-size:22px;text-align:center'});
    var table = new google.visualization.Table(document.getElementById('{$this->code}_chart'));
    table.draw(data, {showRowNumber:false, width:'90%', height:'100%'});
}
google.charts.load('current', {'packages':['table']});
google.charts.setOnLoadCallback(chart{$this->code});\n";
        $layout = array_merge_recursive($layout, [
            'divs'  => [
                'admin' =>['divs' =>['body'=>['order'=>50,'type'=>'fields','keys'=>[$this->code.'defAge', $this->code.'_btn']]]],
                'body'  =>['order'=>40,'type'=>'html','html'=>'<div style="width:100%" id="'.$this->code.'_chart"></div>'],
                'export'=>['order'=>80,'type'=>'html','html'=>'<form id="form'.$this->code.'" action="'.$action.'">'.html5('', $iconExp).'</form>']],
            'fields'=> [
                $this->code.'defAge'=> array_merge($this->struc['defAge'],['order'=>10,'break'=>true]),
                $this->code.'_btn'  => ['order'=>90,'attr'=>['type'=>'button','value'=>lang('save')],'events'=>['onClick'=>"dashboardAttr('$this->moduleID:$this->code', 0);"]]],
            'jsHead'=> ['init'=>$js]]);
    }

    /**
     * Retrieves the aging date based on the SKU provided
     * @param string $sku - sku to search
     * @return string - aged date to compare for filter
     */
    private function getAgingValue($sku)
    {
        if (!empty($this->skuDates[$sku])) { return $this->skuDates[$sku]; }
        $numWeeks = $this->ageFld ? dbGetValue(BIZUNO_DB_PREFIX.'inventory', 'shelf_life', "sku='$sku'") : $this->struc['defAge']['attr']['value'];
        $this->skuDates[$sku] = localeCalculateDate(date('Y-m-d'), -($numWeeks * 7));
        msgDebug("\n num weeks = $numWeeks and calculated date = {$this->skuDates[$sku]}");
        return $this->skuDates[$sku];
    }

    /**
     * Saves users preferences
     */
    public function save()
    {
        $menu_id  = clean('menuID', 'text', 'get');
        $settings['defAge']= clean($this->code.'defAge','integer','post');
        dbWrite(BIZUNO_DB_PREFIX."users_profiles", ['settings'=>json_encode($settings)], 'update', "user_id=".getUserCache('profile', 'admin_id', false, 0)." AND dashboard_id='$this->code' AND menu_id='$menu_id'");
    }
}
