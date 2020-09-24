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
 * @copyright  2008-2020, PhreeSoft, Inc.
 * @license    http://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 * @version    4.x Last Update: 2020-08-12
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
            'required'  => 1,
            'attachPath'=> 'data/contacts/uploads/',
            'api'       => ['path'=>'contacts/api/contactsAPI'],
            'quickBar'  => ['child'=>['settings'=>['child'=>[
                'mgr_e' => ['order'=>45,'label'=>lang('employees'),'icon'=>'employee','events'=>['onClick'=>"hrefClick('contacts/main/manager&type=e');"]]]]]],
            'menuBar'   => ['child'=>[
                'customers'=> ['order'=>10,'label'=>lang('customers'),'group'=>'cust','icon'=>'sales', 'events'=>['onClick'=>"hrefClick('bizuno/main/bizunoHome&menuID=customers');"],'child'=>[
                    'mgr_c'=> ['order'=>10,'label'=>lang('contacts_type_c_mgr'),'icon'=>'users',       'events'=>['onClick'=>"hrefClick('contacts/main/manager&type=c');"]],
                    'rpt_c'=> ['order'=>99,'label'=>lang('reports'),            'icon'=>'mimeDoc',     'events'=>['onClick'=>"hrefClick('phreeform/main/manager&gID=cust');"]]]],
                'vendors'  => ['order'=>20,'label'=>lang('vendors'),'group'=>'vend','icon'=>'purchase','events'=>['onClick'=>"hrefClick('bizuno/main/bizunoHome&menuID=vendors');"],'child'=>[
                    'mgr_v'=> ['order'=>20,'label'=>lang('contacts_type_v_mgr'),'icon'=>'users',       'events'=>['onClick'=>"hrefClick('contacts/main/manager&type=v');"]],
                    'rpt_v'=> ['order'=>99,'label'=>lang('reports'),            'icon'=>'mimeDoc',     'events'=>['onClick'=>"hrefClick('phreeform/main/manager&gID=vend');"]]]]]],
            'hooks'     => ['phreebooks'=>['tools'=>[
                'fyCloseHome'=> ['page'=>'tools', 'class'=>'contactsTools', 'order'=>50],
                'fyClose'    => ['page'=>'tools', 'class'=>'contactsTools', 'order'=>50]]]]];
        $this->phreeformProcessing = [
            'qtrNeg0'    => ['text'=>lang('dates_quarter').' (contact_id_b)'],
            'qtrNeg1'    => ['text'=>lang('dates_lqtr')   .' (contact_id_b)'],
            'qtrNeg2'    => ['text'=>lang('quarter_neg2') .' (contact_id_b)'],
            'qtrNeg3'    => ['text'=>lang('quarter_neg3') .' (contact_id_b)'],
            'qtrNeg4'    => ['text'=>lang('quarter_neg4') .' (contact_id_b)'],
            'qtrNeg5'    => ['text'=>lang('quarter_neg5') .' (contact_id_b)'],
            'contactID'  => ['text'=>lang('contacts_short_name'),      'module'=>'bizuno','function'=>'viewFormat'],
            'contactName'=> ['text'=>lang('address_book_primary_name'),'module'=>'bizuno','function'=>'viewFormat'],
            'contactGID' => ['text'=>lang('contacts_gov_id_number')." (".lang('id').")",'group'=>$this->lang['title'],'module'=>'bizuno','function'=>'viewFormat']];
        setProcessingDefaults($this->phreeformProcessing, $this->moduleID, $this->lang['title']);
        $this->crm_actions = [
            'new' =>$this->lang['contacts_crm_new_call'], 'ret' =>$this->lang['contacts_crm_call_back'], 'flw' =>$this->lang['contacts_crm_follow_up'],
            'lead'=>$this->lang['contacts_crm_new_lead'], 'inac'=>lang('inactive')];
    }

    /**
     * Sets the structure of the user settings for the contacts module
     * @return array - user settings
     */
    public function settingsStructure()
    {
        $meths= ['auto'=>lang('default'), 'email'=>lang('email'), 'tele'=>lang('telephone')];
        $data = [
            'general'     => ['order'=>10,'label'=>lang('general'),'fields'=>[
                'short_name_c'=> ['values'=>viewKeyDropdown($meths),'attr'=>['type'=>'select','value'=>'auto']],
                'short_name_v'=> ['values'=>viewKeyDropdown($meths),'attr'=>['type'=>'select','value'=>'auto']]]],
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
        asort($this->crm_actions);
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
        $clnDefault = localeCalculateDate(date('Y-m-d'), 0, -1);
        $fields = [
            'j9CloseDesc'  => ['order'=>10,'html' =>$this->lang['close_j9_desc'],'attr'=>['type'=>'raw']],
            'dateJ9Close'  => ['order'=>20,'label'=>$this->lang['close_j9_label'],'attr'  =>['type'=>'date','value'=>$clnDefault]],
            'btnJ9Close'   => ['order'=>30,'events'=>['onClick'=>"jq('body').addClass('loading'); jsonAction('contacts/tools/j9Close', 0, jq('#dateJ9Close').datebox('getValue'));"],
                'attr' => ['type'=>'button','value'=>lang('start')]],
            'syncAtchDesc' => ['order'=>10,'html'=>$this->lang['sync_attach_desc'],'attr'=>['type'=>'raw']],
            'btnSyncAttach'=> ['order'=>20,'events'=>['onClick' => "jq('body').addClass('loading'); jsonAction('contacts/tools/syncAttachments&verbose=1');"],
                'attr' => ['type'=>'button','value'=>lang('go')]]];
        $data  = [
            'tabs'    => ['tabAdmin'=>['divs'=>[
                'fields'=> ['order'=>40,'label'=>lang('extra_fields'),'type'=>'html','html'=>'','options'=>["href"=>"'".BIZUNO_AJAX."&bizRt=bizuno/fields/manager&module=$this->moduleID&table=contacts'"]],
                'tools' => ['order'=>80,'label'=>lang('tools'),'type'=>'divs','divs'=>[
                    'general' => ['order'=>20,'type'=>'divs','classes'=>['areaView'],'divs'=>[
                        'closeJ9' => ['order'=>30,'type'=>'panel','classes'=>['block33'],'key'=>'closeJ9'],
                        'syncAtch'=> ['order'=>40,'type'=>'panel','classes'=>['block33'],'key'=>'syncAtch']]]]]]]],
            'panels'  => [
                'closeJ9' => ['label'=>$this->lang['close_j9_title'],   'type'=>'fields','keys'=>['j9CloseDesc','dateJ9Close','btnJ9Close']],
                'syncAtch'=> ['label'=>$this->lang['sync_attach_title'],'type'=>'fields','keys'=>['syncAtchDesc','btnSyncAttach']]],
            'fields'  => $fields];
        $layout = array_replace_recursive($layout, adminStructure($this->moduleID, $this->settingsStructure(), $this->lang), $data);
    }

    /**
     * Saves the users settings
     */
    public function adminSave()
    {
        readModuleSettings($this->moduleID, $this->settingsStructure());
    }
}
