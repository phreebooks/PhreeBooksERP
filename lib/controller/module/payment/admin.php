<?php
/*
 * module Payment - Installation, initialization, and settings
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
 * @version    3.x Last Update: 2018-12-19
 * @filesource /lib/controller/module/payment/admin.php
 */

namespace bizuno;

class paymentAdmin
{
    public $moduleID  = 'payment';

    public function __construct()
    {
        $this->lang      = getLang($this->moduleID);
        $this->pmtMethods= ['cod', 'moneyorder']; // pre-select a couple of payment methods to install
        $this->defaults  = [
            'gl_payment_c'  => getModuleCache('phreebooks', 'chart', 'defaults', getUserCache('profile', 'currency', false, 'USD'))[0],
            'gl_discount_c' => getModuleCache('phreebooks', 'chart', 'defaults', getUserCache('profile', 'currency', false, 'USD'))[0],
            'gl_payment_v'  => getModuleCache('phreebooks', 'chart', 'defaults', getUserCache('profile', 'currency', false, 'USD'))[0],
            'gl_discount_v' => getModuleCache('phreebooks', 'chart', 'defaults', getUserCache('profile', 'currency', false, 'USD'))[0]];
        $this->settings  = array_replace_recursive(getStructureValues($this->settingsStructure()), getModuleCache($this->moduleID, 'settings', false, false, []));
        $this->structure = [
            'url'       => BIZUNO_URL."controller/module/$this->moduleID/",
            'version'   => MODULE_BIZUNO_VERSION,
            'category'  => 'bizuno',
            'required'  => '1',
            'dirMethods'=> 'methods'];
    }

    /**
     * Defines the structure of user configurable setting values
     * @return array - structure of user configurable settings
     */
    public function settingsStructure()
    {
        $data = ['general'=>['order'=>10,'label'=>lang('general'),'fields'=>[
            'gl_payment_c' =>['attr'=>['type'=>'ledger','id'=>'general_gl_payment_c', 'value'=>$this->defaults['gl_payment_c']]],
            'gl_discount_c'=>['attr'=>['type'=>'ledger','id'=>'general_gl_discount_c','value'=>$this->defaults['gl_discount_c']]],
            'gl_payment_v' =>['attr'=>['type'=>'ledger','id'=>'general_gl_payment_v', 'value'=>$this->defaults['gl_payment_v']]],
            'gl_discount_v'=>['attr'=>['type'=>'ledger','id'=>'general_gl_discount_v','value'=>$this->defaults['gl_discount_v']]],
            'prefix'       =>['attr'=>['value'=>'DP']]]]];
        settingsFill($data, $this->moduleID);
        return $data;
    }

    /**
     * Sets the structure for the home page of payment user defined settings
     * @param array $layout - Home page for user settings for this module
     * @return modified $layout
     */
    public function adminHome(&$layout)
    {
        if (!$security = validateSecurity('bizuno', 'admin', 1)) { return; }
        $data = adminStructure($this->moduleID, $this->settingsStructure(), $this->lang);
        $data['tabs']['tabAdmin']['divs']['methods'] = ['order'=>5,'label'=>lang('payment_methods'),'attr'=>['module'=>$this->moduleID,'path'=>$this->structure['dirMethods']],
            'src'=>BIZUNO_LIB."view/tabAdminMethods.php"];
        $layout = array_replace_recursive($layout, $data);
    }

    /**
     * Saves the updated settings as requested by the user
     */
    public function adminSave()
    {
        readModuleSettings($this->moduleID, $this->settingsStructure());
    }

    /**
     * Installs some methods so the user can collect a payment
     * @param array $layout - structure coming in
     */
    public function install(&$layout=[])
    {
        $bAdmin = new bizunoSettings();
        foreach ($this->pmtMethods as $method) {
            $bAdmin->methodInstall($layout, ['module'=>'payment', 'path'=>'methods', 'method'=>$method], false);
        }
    }
}
