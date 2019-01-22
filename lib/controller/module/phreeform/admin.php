<?php
/*
 * Module PhreeForm main functions
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
 * @version    3.x Last Update: 2018-12-18
 * @filesource /controller/module/phreeform/admin.php
 */

namespace bizuno;

bizAutoLoad(BIZUNO_LIB."controller/module/phreeform/functions.php", 'phreeformSecurity', 'function');

class phreeformAdmin
{
    public $moduleID = 'phreeform';

    function __construct()
    {
        $this->lang     = getLang($this->moduleID);
        $this->settings = array_replace_recursive(getStructureValues($this->settingsStructure()), getModuleCache($this->moduleID, 'settings', false, false, []));
        $this->structure= [
            'url'     => BIZUNO_URL."controller/module/$this->moduleID/",
            'version' => MODULE_BIZUNO_VERSION,
            'category'=> 'bizuno',
            'required'=> '1',
            'menuBar' => ['child'=>['tools'=>['child'=>['phreeform'=>['order'=>50,'label'=>lang('phreeform_manager'),'icon'=>'mimeDoc','events'=>['onClick'=>"hrefClick('phreeform/main/manager');"]]]]]]];
    }

    /**
     * Sets the structure for the user definable settings for module PhreeForm
     * @return array - structure ready to render in PhreeForm settings
     */
    public function settingsStructure()
    {
        $data = ['general' => ['order'=>10,'label'=>lang('general'),'fields'=>[
            'default_font'=> ['values'=>phreeformFonts(false), 'attr'=>  ['type'=>'select', 'value'=>'helvetica']],
            'column_width'=> ['attr'=>['value'=>25]],
            'margin'      => ['attr'=>['value'=>8]],
            'title1'      => ['attr'=>['value'=>'%reportname%']],
            'title2'      => ['attr'=>['value'=>$this->lang['phreeform_heading_2']]], // 'Report Generated %date%'
            'paper_size'  => ['values'=>phreeformPages($this->lang), 'attr'=>  ['type'=>'select', 'value'=>'Letter:216:282']],
            'orientation' => ['values'=>phreeformOrientation($this->lang),'attr'=>  ['type'=>'select', 'value'=>'P']],
            'truncate_len'=> ['attr'=>['value'=>'25']]]]];
        settingsFill($data, $this->moduleID);
        return $data;
    }

    /**
     * Sets the structure for the settings home page for module PhreeForm
     * @param array $layout - Structure coming in
     * @return modified $layout
     */
    public function adminHome(&$layout=[])
    {
        if (!$security = validateSecurity('bizuno', 'admin', 1)) { return; }
        $data = adminStructure($this->moduleID, $this->settingsStructure(), $this->lang);
        $data['tabs']['tabAdmin']['divs']['tabDBs'] = ['order'=>70,'label'=>lang('dashboards'),'attr'=>['module'=>$this->moduleID,'path'=>'dashboards'],'src'=>BIZUNO_LIB."view/tabAdminMethods.php"];
        $layout = array_replace_recursive($layout, $data);
    }

    /**
     * Saves the user defined settings in the cache and database
     */
    public function adminSave()
    {
        readModuleSettings($this->moduleID, $this->settingsStructure());
    }
}
