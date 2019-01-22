<?php
/*
 * Bizuno dashboard - List reminders on a given recurring schedule, works with profile add-on
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
 * @version    3.x Last Update: 2018-10-10
 * @filesource /lib/controller/module/bizuno/dashboards/reminder/reminder.php
 */

namespace bizuno;

define('DASHBOARD_REMINDER_VERSION','3.1');

class reminder
{
    public $moduleID = 'bizuno';
    public $methodDir= 'dashboards';
    public $code     = 'reminder';
    public $category = 'general';
    
    function __construct($settings=[])
    {
        $this->security= 4;
        $this->lang    = getMethLang($this->moduleID, $this->methodDir, $this->code);
        $this->settings= $settings;
    }

    /**
     * Installs the dashboard on the specified menu
     * @param string $moduleID - Module ID where the method is stored
     * @param string $menu_id - Menu ID where the dashboard will be placed
     */
    public function install($moduleID='', $menu_id='')
    {
        $exists = dbGetRow(BIZUNO_DB_PREFIX."users_profiles", "user_id=".getUserCache('profile', 'admin_id', false, 0)." AND dashboard_id='reminder'");
        if ($exists) {
            $settings = clean($exists['settings'], 'json');
            if (!$settings) { $settings = []; }
        } else {
            $settings = [];
        }
        $sql_data = [
            'user_id'     => getUserCache('profile', 'admin_id', false, 0),
            'menu_id'     => $menu_id,
            'module_id'   => $moduleID,
            'dashboard_id'=> $this->code,
            'settings'    => json_encode($settings)];
        dbWrite(BIZUNO_DB_PREFIX."users_profiles", $sql_data);
    }

    /**
     * Removes the dashboard from the users menu, will not allow removal if only 1 dashboard and reminder list is not empty
     * @param string $menu_id - Menu to remove dashboard
     */
    public function remove($menu_id='')
    {
        $onlyDash = $srcExist = false;
        $dashboards = dbGetMulti(BIZUNO_DB_PREFIX."users_profiles", "user_id=".getUserCache('profile', 'admin_id', false, 0)." AND dashboard_id='reminder'");
        msgDebug("\nNumber of dashboards = ".sizeof($dashboards));
        if (sizeof($dashboards) == 1) { $onlyDash = true; }
        $oneDash = array_shift($dashboards);
        $settings= clean($oneDash['settings'], 'json');
        msgDebug("\nSettings = ".sizeof($dashboards));
        if ($onlyDash && (!isset($settings['source']) || sizeof($settings['source']) > 0)) { $srcExist = true; }
        if (!$onlyDash && !$srcExist) {
            dbGetResult("DELETE FROM ".BIZUNO_DB_PREFIX."users_profiles"." WHERE user_id=".getUserCache('profile', 'admin_id', false, 0)." AND menu_id='$menu_id' AND dashboard_id='$this->code'");
        } else {
            msgAdd($this->lang['err_dash_reminder_delete']);
        }
    }

    /**
     * Renders the dashboard contents in HTML
     * @param array $settings - dashboard settings field (already json decoded)
     * @return string - formatted html ready for display
     */
    public function render()
    {
        // see if any new reminders need to be added to list
        $result = dbGetRow(BIZUNO_DB_PREFIX."users_profiles", "user_id=".getUserCache('profile', 'admin_id', false, 0)." AND dashboard_id='$this->code'");
        $this->settings = clean($result['settings'], 'json');
        $today = date('Y-m-d');
        $needsUpdate = false;
        if (!empty($this->settings['source'])) {
            foreach ($this->settings['source'] as $idx => $entry) {
                if ($entry['dateNext'] <= $today) {
                    $this->settings['current'][] = ['title'=>$entry['title'], 'date'=>$entry['dateNext']];
                    $this->settings['source'][$idx]['dateNext'] = $this->setDateNext($entry['dateNext'], $entry['recur']);
                    $needsUpdate = true;
                }
            }
        }
        if ($needsUpdate) {
            dbWrite(BIZUNO_DB_PREFIX."users_profiles", ['settings'=>json_encode($this->settings)], 'update', "user_id=".getUserCache('profile', 'admin_id', false, 0)." AND dashboard_id='$this->code'");
        }
        $index = 1;
        $html  = '<div>';
        $html .= '  <div id="'.$this->code.'_attr" style="display:none"><p>'.$this->lang['msg_settings_info'].'</p></div>';
        $html .= html5('', ['classes'=>['easyui-datalist'],'attr'=>['type'=>'ul']])."\n";
          if (!empty($this->settings['current'])) {
            foreach ($this->settings['current'] as $entry) {
                $html .= html5('', ['attr'=>['type'=>'li']]).'<span style="float:left">';
                $html .= viewFormat($entry['date'], 'date').' - '.$entry['title'].'</span><span style="float:right">'.html5('', ['icon'=>'trash','size'=>'small','events'=>['onClick'=>"if (confirm('".jsLang('msg_confirm_delete')."')) dashboardAttr('$this->moduleID:$this->code', $index);"]]).'</span></li>';
                $index++;
            }
        } else {
            $html .= '<li><span>'.lang('no_results')."</span></li>";
        }
        $html .= '</ul></div>';
        return $html;
    }

    /**
     * Deletes a reminder from the list
     * @return integer - index of the deleted reminder list item
     */
    public function save()
    {
        if (!$idx= clean('rID', 'integer', 'get')) { return; }
        $result  = dbGetRow(BIZUNO_DB_PREFIX."users_profiles", "user_id=".getUserCache('profile', 'admin_id', false, 0)." AND dashboard_id='$this->code'");
        $settings= clean($result['settings'], 'json');
        unset($settings['current'][($idx-1)]);
        $settings['current'] = array_values($settings['current']);
        dbWrite(BIZUNO_DB_PREFIX."users_profiles", ['settings'=>json_encode($settings)], 'update', "user_id=".getUserCache('profile', 'admin_id', false, 0)." AND dashboard_id='$this->code'");
        return $result['id'];
    }
    
    /**
     * Calculates the next date the reminder should be triggered, will skip any dates prior to today.
     * @param sting $dateStart - date to start the calculation, format Y-m-d
     * @param string $recur - recurrence frequency choices are d,w,m,q,y
     * @return string - Next date to set the reminder
     */
    public function setDateNext($dateStart, $recur='y')
    {
        switch ($recur) {
            case 'd': $offD = 1; $offM = 0; $offY = 0; break;
            case 'w': $offD = 7; $offM = 0; $offY = 0; break;
            case 'm': $offD = 0; $offM = 1; $offY = 0; break;
            case 'q': $offD = 0; $offM = 3; $offY = 0; break;
            case 'y': $offD = 0; $offM = 0; $offY = 1; break;
        }
        $dateNext = localeCalculateDate($dateStart, $offD, $offM, $offY);
        if ($dateNext < date('Y-m-d')) { $this->setDateNext($dateNext, $recur); }
        return $dateNext;
    }
}
