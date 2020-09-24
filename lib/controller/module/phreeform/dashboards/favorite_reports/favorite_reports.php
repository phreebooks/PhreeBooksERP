<?php
/*
 * Phreeform dashboard - Favorite Reports
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
 * @version    4.x Last Update: 2020-07-22
 * @filesource /controller/module/phreeform/dashboards/favorite_reports/favorite_reports.php
 */

namespace bizuno;

bizAutoLoad(BIZUNO_LIB."controller/module/phreeform/functions.php", 'phreeformSecurity', 'function');

class favorite_reports
{
    public $moduleID = 'phreeform';
    public $methodDir= 'dashboards';
    public $code     = 'favorite_reports';
    public $category = 'bizuno';

    function __construct($settings)
    {
        $this->security= getUserCache('security', 'profile', false, 0);
        $this->lang    = getMethLang($this->moduleID, $this->methodDir, $this->code);
        $this->settings= $settings;
    }

    /**
     * Creates the HTML code to render this dashboard
     * @param array $settings - dashboard user settings
     * @return string - HTML dashboard
     */
    public function render(&$layout=[])
    {
        $result = dbGetMulti(BIZUNO_DB_PREFIX."phreeform", "mime_type IN ('rpt','frm')", "title"); // load the report list
        $data_array = [['id'=>'', 'text'=>lang('select')]];
        foreach ($result as $row) { if (phreeformSecurity($row['security'])) { $data_array[] = ['id'=>$row['id'], 'text'=>$row['title']]; } }
        if ( empty($this->settings['data'])) { $rows[] = "<span>".lang('no_results')."</span>"; }
        else { foreach ($this->settings['data'] as $id => $title) {
            $mime   = dbGetValue(BIZUNO_DB_PREFIX."phreeform", 'mime_type', "id=$id");
            $content= html5('', ['icon'=>viewMimeIcon($mime),'events'=>['onClick'=>"winOpen('phreeform', 'phreeform/render/open&rID=$id');"]]).viewText($title);
            $trash  = html5('', ['icon'=>'trash','size'=>'small','events'=>['onClick'=>"if (confirm('".jsLang('msg_confirm_delete')."')) { dashSubmit('$this->moduleID:$this->code', $id); }"]]);
            $rows[] = viewDashList($content, $trash);
        } }
        $layout = array_merge_recursive($layout, [
            'divs'  => [
                'admin'=>['divs'=>['body'=>['order'=>50,'type'=>'fields','keys'=>[$this->code.'_0', $this->code.'_btn']]]],
                'body' =>['order'=>50,'type'=>'list','key'=>$this->code]],
            'fields'=> [
                $this->code.'_0'  => ['order'=>10,'break'=>true,'label'=>lang('select'),'values'=>$data_array,'attr'=>['type'=>'select']],
                $this->code.'_btn'=> ['order'=>90,'attr'=>['type'=>'button','value'=>lang('add')],'events'=>['onClick'=>"dashSubmit('$this->moduleID:$this->code', 0);"]]],
            'lists' => [$this->code=>$rows]]);
    }

    /**
     * Saves the user preferences in the database
     * @return database record id of insert/update
     */
    public function save()
    {
        $menu_id  = clean('menuID', 'cmd', 'get');
        $rmID     = clean('rID', 'integer', 'get');
        $report_id= clean($this->code.'_0', 'text', 'post');
        $title    = dbGetValue(BIZUNO_DB_PREFIX."phreeform", 'title', "id='$report_id'");
        if (!$rmID && $report_id == '') { return; }// do nothing if no title or url entered
        // fetch the current settings
        $result = dbGetRow(BIZUNO_DB_PREFIX."users_profiles", "user_id=".getUserCache('profile', 'admin_id', false, 0)." AND menu_id='$menu_id' AND dashboard_id='$this->code'");
        $settings = json_decode($result['settings'], true);
        if ($rmID) { unset($settings['data'][$rmID]); }
        else { $settings['data'][$report_id] = $title; asort($settings['data']); }
        dbWrite(BIZUNO_DB_PREFIX."users_profiles", ['settings'=>json_encode($settings)], 'update', "user_id=".getUserCache('profile', 'admin_id', false, 0)." AND dashboard_id='$this->code' AND menu_id='$menu_id'");
        return $result['id'];
    }
}
