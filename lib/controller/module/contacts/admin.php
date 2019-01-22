<?php
/*
 * Administration methods for the contacts module
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
 * @version    3.x Last Update: 2018-12-10
 * @filesource /lib/controller/module/contacts/admin.php
 */

namespace bizuno;

class contactsAdmin
{
    public $moduleID = 'contacts';

    function __construct()
    {
        $this->lang     = getLang($this->moduleID);
        $this->settings = array_replace_recursive(getStructureValues($this->settingsStructure()), getModuleCache($this->moduleID, 'settings', false, false, []));
        $this->structure= [
            'url'       => BIZUNO_URL."controller/module/$this->moduleID/",
            'version'   => MODULE_BIZUNO_VERSION,
            'category'  => 'bizuno',
            'required'  => '1',
            'attachPath'=> 'data/contacts/uploads/',
            'api'       => ['path'=>'contacts/api/contactsAPI'],
            'quickBar'  => ['child'=>['home'=>['child'=>[
                'mgr_e' => ['order'=>45,'label'=>lang('employees'),'icon'=>'employee','events'=>['onClick'=>"hrefClick('contacts/main/manager&type=e');"]]]]]],
            'menuBar'   => ['child'=>[
                'customers'=> ['order'=>10,'label'=>lang('customers'),'group'=>'cust','icon'=>'sales', 'events'=>['onClick'=>"hrefClick('bizuno/main/bizunoHome&menuID=customers');"],'child'=>[
                    'mgr_c'=> ['order'=>10,'label'=>lang('contacts_type_c_mgr'),'icon'=>'users',       'events'=>['onClick'=>"hrefClick('contacts/main/manager&type=c');"]],
                    'mgr_i'=> ['order'=>20,'label'=>lang('contacts_type_i_mgr'),'icon'=>'chat',        'events'=>['onClick'=>"hrefClick('contacts/main/manager&type=i');"]],
                    'rpt_c'=> ['order'=>99,'label'=>lang('reports'),            'icon'=>'mimeDoc',     'events'=>['onClick'=>"hrefClick('phreeform/main/manager&gID=cust');"]]]],
                'vendors'  => ['order'=>20,'label'=>lang('vendors'),'group'=>'vend','icon'=>'purchase','events'=>['onClick'=>"hrefClick('bizuno/main/bizunoHome&menuID=vendors');"],'child'=>[
                    'mgr_v'=> ['order'=>20,'label'=>lang('contacts_type_v_mgr'),'icon'=>'users',       'events'=>['onClick'=>"hrefClick('contacts/main/manager&type=v');"]],
                    'rpt_v'=> ['order'=>99,'label'=>lang('reports'),            'icon'=>'mimeDoc',     'events'=>['onClick'=>"hrefClick('phreeform/main/manager&gID=vend');"]]]]]],
            'hooks'     => ['phreebooks'=>['tools'=>[
                'fyCloseHome'=> ['page'=>'tools', 'class'=>'contactsTools', 'order'=>50],
                'fyClose'    => ['page'=>'tools', 'class'=>'contactsTools', 'order'=>50]]]]];
        $this->phreeformProcessing = [
            'contactID'  => ['text'=>lang('contacts_short_name'),      'group'=>$this->lang['title'],'module'=>'bizuno','function'=>'viewFormat'],
            'contactName'=> ['text'=>lang('address_book_primary_name'),'group'=>$this->lang['title'],'module'=>'bizuno','function'=>'viewFormat']];
        $this->crm_actions = [
            'new' =>$this->lang['contacts_crm_new_call'],
            'ret' =>$this->lang['contacts_crm_call_back'],
            'flw' =>$this->lang['contacts_crm_follow_up'],
            'lead'=>$this->lang['contacts_crm_new_lead'],
            'inac'=>lang('inactive')];
    }

    /**
     * Sets the structure of the user settings for the contacts module
     * @return array - user settings 
     */
    public function settingsStructure()
    {
        $data = [
            'address_book'=> ['order'=>20,'label'=>lang('address_book'),'fields'=>[
                'primary_name'=> ['attr'=>['type'=>'selNoYes', 'value'=>'1']],
                'address1'    => ['attr'=>['type'=>'selNoYes', 'value'=>'0']],
                'city'        => ['attr'=>['type'=>'selNoYes', 'value'=>'0']],
                'state'       => ['attr'=>['type'=>'selNoYes', 'value'=>'0']],
                'postal_code' => ['attr'=>['type'=>'selNoYes', 'value'=>'0']],
                'telephone1'  => ['attr'=>['type'=>'selNoYes', 'value'=>'0']],
                'email'       => ['attr'=>['type'=>'selNoYes', 'value'=>'0']]]]];
        settingsFill($data, $this->moduleID);
        return $data;
    }

    public function initialize()
    {
        setModuleCache('contacts', 'crm_actions', false, $this->crm_actions);
        return true;
    }
    
    /**
     * Builds the home menu for settings of the contacts module
     * @param array $layout - current working structure
     * @return modified $layout
     */
    public function adminHome(&$layout=[])
    {
        if (!$security = validateSecurity('bizuno', 'admin', 1)) { return; }
        $data = ['tabs'=>['tabAdmin'=>['divs'=>[
            'fields'  => ['order'=>50,'label'=>lang('extra_fields'),'type'=>'html','html'=>'','options'=>["href"=>"'".BIZUNO_AJAX."&p=bizuno/fields/manager&module=$this->moduleID&table=contacts'"]],
            'tabDBs'  => ['order'=>70,'label'=>lang('dashboards'),'attr'=>['module'=>$this->moduleID,'path'=>'dashboards'],'src'=>BIZUNO_LIB."view/tabAdminMethods.php"],
            'tools'   => ['order'=>80,'label'=>lang('tools'),'type'=>'html','html'=>$this->setToolsTab()]]]]];
        $layout = array_replace_recursive($layout, adminStructure($this->moduleID, $this->settingsStructure(), $this->lang), $data);
    }

    private function setToolsTab()
    {
        $clnDefault = viewFormat(localeCalculateDate(date('Y-m-d'), 0, -1), 'date');
        $fields = [
            'dateJ9Close'  => ['classes'=>['easyui-datebox'],'options'=>['value'=>"'$clnDefault'"]],
            'btnJ9Close'   => ['events' =>['onClick'=>"jq('body').addClass('loading'); jsonAction('contacts/tools/j9Close', 0, jq('#dateJ9Close').datebox('getValue'));"],
                'attr' => ['type'=>'button','value'=>lang('start')]],
            'btnSyncAttach'=> ['events'=> ['onClick' => "jq('body').addClass('loading'); jsonAction('contacts/tools/syncAttachments&verbose=1');"],
                'attr'=>  ['type'=>'button','value'=>lang('go')]]];
         return "<fieldset><legend>".$this->lang['close_j9_title']."</legend>
    <p>".$this->lang['close_j9_desc']."</p>
    <p>".$this->lang['close_j9_label'].' '.html5('dateJ9Close', $fields['dateJ9Close']).html5('btnJ9Close', $fields['btnJ9Close']).'</p>
</fieldset>
<fieldset><legend>'.$this->lang['sync_attach_title']."</legend>
    <p>".$this->lang['sync_attach_desc']."</p>
    <p>".html5('btnSyncAttach', $fields['btnSyncAttach']).'</p>
</fieldset>';
    }

    /**
     * Saves the users settings 
     */
    public function adminSave()
    {
        readModuleSettings($this->moduleID, $this->settingsStructure());
    }
}
